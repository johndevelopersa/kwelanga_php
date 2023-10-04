<?php

/* * ********************************************************************************************
 * *
 * *  This job can run as many times per day as is necessary according to job scheduler.
 * *
 * *  It executes notifications that occur throughout the day by triggers
 * *
 * ******************************************************************************************** */

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "functional/extracts/daily/extractController.php");


//static method handler.
class LeechemInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class LeechemInvoicedInit extends extractController {


  private $principalUid = 95; //uid of principal extract.
  private $filename = 'INV00[@FSEQ].csv';  //main controller will build full filename with seq. for us.


  public function generateOutput(){

    global $ROOT, $PHPFOLDER;


    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];
    $folder = $this->principalUid . '_' . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.


    //use the receipients listed in the notification table instead of hard coding them!!!
    //expecting only one row loaded per principal extract
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_CUSTOM);
    if (count($reArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Export failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }

    $recipientUId = $reArr[0]['uid'];

    // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
    }

    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($this->principalUid, $recipientUId);


    /*  SUCCESS POINT - 1  */
    //nothing to do...
    if(count($seDocs)==0){
      echo "Successfully Completed Extract : ".get_class($this)." - No entries!<br>";
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
      return $this->errorTO;
    }


    //group array
    $grpDocs = array();
    $psms=array();
    foreach($seDocs as $k=>$r){
      $grpDocs[$r['dm_uid']][] = $r; //sends file per depot.
      $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 83, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }


    //depot translate
    $depotTrans = array(
      2 =>   'UJ', //UJ
      3 =>   'UD', //UD
      5 =>   'UC', //UC
      6 =>   'FP', //FP
      7 =>   'TE'  //TE
    );
    $errorSEUIdArr = array();
    $successSEUIdArr = array();
    $dataArr = array();

    foreach($grpDocs as $hr){

      if(empty($sfvals[$hr[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...

        $errorSEUIdArr[] = $hr[0]['se_uid']; //list of smart event errors

      } else {


        $successSEUIdArr[] = $hr[0]['se_uid']; //list of smart event success

        //skip invoices which has zero invoice qty.
        $hasInvoiceLines = false;
        foreach($hr as $d){
          if(abs($d['document_qty'])>0){  //invoice qty greater then zero.
            $hasInvoiceLines = true;
            break;
          }
        }
        if($hasInvoiceLines===false){
          continue; //this invoice was sent but don't export it.
        }

        $region = (isset($depotTrans[$hr[0]['depot_uid']])) ? $depotTrans[$hr[0]['depot_uid']] : '  ';
        $docNo = (!empty($hr[0]['invoice_number'])) ? abs($hr[0]['invoice_number']) : abs($hr[0]['document_number']);
        $sysproAcc = trim($sfvals[$hr[0]["principal_store_uid"]]["value"]);


        /*-------------------------------------------------*/
        /* START BUILDING OUTPUT
         */
        $dataH = array();
        $dataH[] = '00'; //header
        $dataH[] = $docNo; //invoice no.
        $dataH[] = $sysproAcc;
        $dataH[] = trim($hr[0]['customer_order_number']);
        $dataH[] = date("Ymd", strtotime($hr[0]["invoice_date"]));  //DATE (DD/MM/YYYY)
        $dataH[] = ''; //blank
        $dataH[] = ''; //blank
        $dataH[] = str_replace(array(',','"',"'"), array('','',''), trim($hr[0]['deliver_name']));
        $dataH[] = str_replace(array(',','"',"'"), array('','',''), trim($hr[0]['deliver_add1']));
        $dataH[] = str_replace(array(',','"',"'"), array('','',''), trim($hr[0]['deliver_add2']));
        $dataH[] = str_replace(array(',','"',"'"), array('','',''), trim($hr[0]['deliver_add3']));
        $dataH[] = ''; //blank
        $dataH[] = ''; //blank
        $dataH[] = ''; //blank
        $dataH[] = ''; //blank
        $dataH[] = ' ' . abs($hr[0]['document_number']);
        $dataArr[] = join(',', $dataH);


        //detail rows
        $lineNo = 1;
        foreach($hr as $d){ //detail rows.

          if(abs($d['document_qty'])>0){  //invoice qty greater then zero.

            $dataD = array();
            $dataD[] = '01'; //detail
            $dataD[] = $docNo; //invoice no.
            $dataD[] = str_replace(array("\t","\r"),array("",""), trim($d['product_code']));
            $dataD[] = $region;
            $dataD[] = abs($d['document_qty']);
            $dataD[] = 'EA';
            $dataD[] = number_format(round($d['net_price'], 2), 2, '.', '');
            $dataD[] = '0.00';  //hard coded : DISPER  := "0.00"   //  ALLTRIM(STR(T_ORDDET->OD_DISC))
            $dataArr[] = join(',', $dataD);

          }
        } //eo detail
      } //eo special field check
    } //eo all documents
    $data = join("\r\n",$dataArr);


    //create file only if there are successful items.
    $filePath = false;
    if(count($successSEUIdArr)>0){

      $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid, false, 3);
      if($seqFilename==false){
        BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
        return $this->errorTO;
      }

      //write physical file
      $filePath = $this->createFile($folder, $seqFilename, $data);  //places file in correct folder
      if($filePath == false){
        BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in CapeHerbInvoiced on file creation";
        return $this->errorTO;
      }
    }


    // SETUP DISTRIBUTION
    $postingDistributionTO = new PostingDistributionTO;
    $postingDistributionTO->DMLType = "INSERT";
    $postingDistributionTO->deliveryType = BT_EMAIL;
    $postingDistributionTO->subject = $this->getTemplateInvoiceSubject(); //might have no filename if all errors therefore don't display on subject line...
    $postingDistributionTO->body = $this->getTemplateBody($principalName, count($successSEUIdArr), count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
    if($filePath!=false){
      $postingDistributionTO->attachmentFile = $filePath;
    }

    $recipientList = explode(",", $reArr[0]["user_uid_list"]); //if blank, sizeof() will still be 1
    $recipientsCheckCount = 0;  //check if atleast one distribution item was posted, incase of invalid recipients

    foreach($recipientList as $re){

      $mfC = $this->miscDAO->getContactItem($this->principalUid, "", $re);
      if (sizeof($mfC)==0) {
        BroadcastingUtils::sendAlertEmail("System Error",get_class($this)." Extract for nr.UID {$recipientUId} has an invalid Recipient/Contact: '{$re}'.","Y", true);
        continue;
      }

      $postingDistributionTO->destinationAddr = $mfC[0]["email_addr"];
      $dResult = $this->postDistributionDAO->postQueueDistribution($postingDistributionTO);
      if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = get_class($this)." Extract distribution queue failed for for nr.UID {$recipientUId} and Recipient/Contact: '{$re}'.";
        BroadcastingUtils::sendAlertEmail("System Error", $this->errorTO->description, "Y", true);
        return $this->errorTO;
      } else {
        $recipientsCheckCount++;  //successful
      }

    }


    if ($recipientsCheckCount==0) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed in ".get_class($this)." extract no valid Recipient/Contact found, no outgoing mail generated!";
      BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
      return $this->errorTO;
    }


    /*
     *  UPDATE SMART EVENT in BULK
     */
    //SUCCESSFUL ITEMS
    if (sizeof($successSEUIdArr) > 0) {
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), basename($filePath), "");
      if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
        return $this->errorTO;
      }
    }
    //ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
    if (sizeof($errorSEUIdArr) > 0) {
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "83", "", FLAG_ERRORTO_ERROR);
      if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
        return $this->errorTO;
      }
    }
    /*-------------------------------------------------*/


    echo "Successfully Completed Extract : ".get_class($this)."<br>";

    /*  SUCCESS POINT - 2  */
    $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
    $this->errorTO->description = "Successful";
    return $this->errorTO;

  }

}


//direct run!
if ($runMe) {
  directRunExtract(__FILE__);
}


?>
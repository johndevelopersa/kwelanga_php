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
class Cyndara2Invoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}



class Cyndara2InvoicedInit extends extractController {


  private $principalUid = 62; //uid of principal extract.
  private $filename = 'newPA05[@FSEQ].csv';  //main controller will build full filename with seq. for us.

  /***************************
   * NOTE !
   * This uses NT_DAILY_EXTRACT_ALTCUSTOM and not NT_DAILY_EXTRACT_CUSTOM as we needed to run in parallel to an existing extraction
   ***************************/


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
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_ALTCUSTOM1);
    if (count($reArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
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
      $grpDocs[$r['dm_uid']][] = $r;
      $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 34, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }

    $dataArr = array();
    $errorSEUIdArr = array();
    $successSEUIdArr = array();

    foreach($grpDocs as $ord){

      //depot translate (clipper program)
//      $depotTrans = array(
//          2 =>   50, //UJ
//          3 =>   51, //UD
//          5 =>   52, //UC
//          6 =>   54, //FP
//          7 =>   55 //TE
//      );

      /*-------------------------------------------------*/
      /*            START BUILDING OUTPUT
      /*-------------------------------------------------*/

      if(empty($sfvals[$ord[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...

        $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors

      } else {

        $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

//        $regionArr = (isset($depotTrans[$ord[0]['depot_uid']])) ? $depotTrans[$ord[0]['depot_uid']] : '  ';
        $storeAcc = trim($sfvals[$ord[0]["principal_store_uid"]]['value']);

        /* PASTEL HEADER */
        //array containing list of row values
        $rowArr = array();
        $rowArr[] = '1'; // Header Row
        $rowArr[] = '1'; // 0=customer invoice; 1= suplier inv
        $rowArr[] = $ord[0]['document_number'];
        $rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["invoice_date"])).'"';  //DATE (DD/MM/YYYY)
        $rowArr[] = '"'.$storeAcc.'"';  //CUSTOMER CODE - Pastel Account.
        $rowArr[] = '"'.trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"])).'"'; // order no
        $rowArr[] = '""'; // reference
        $rowArr[] = $ord[0]["exclusive_total"]; // sub tot
        $rowArr[] = $ord[0]["vat_total"]; // vat
        $rowArr[] = $ord[0]["invoice_total"]; // total

        $dataArr[] = join(',',$rowArr);

        $locationUId = "";
        switch ($ord[0]["depot_uid"]) {
          case 2:
            $locationUId = "14";
            break;
          case 3:
            $locationUId = "15";
            break;
          case 5:
              $locationUId = "17";
              break;
          case 6:
            $locationUId = "16";
            break;
          case 7:
            $locationUId = "20";
            break;
          default:
            $locationUId = "NOT CONFIGURED";
        }

        foreach($ord as $d){ //detail rows.

         // if(abs($d['document_qty'])>0){  //include zero qty.

            $detArr = array();
            $detArr[] = '0'; // Detail Row
            $detArr[] = '"' . trim(str_replace(array('"'),array(''),$d['alt_code'])) . '"';
            $detArr[] = $locationUId; // stock location id
            $detArr[] = ((floatval($d['vat_amount'])==0 && $d['net_price']>0)?0:1); // vat code
            $detArr[] = '"' . trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description'])) . '"'; // description
            $detArr[] = abs($d['document_qty']);
            $detArr[] = number_format(round($d['net_price'], 2), 2, '.', ''); // Unit Price
            $detArr[] = number_format(round($d['extended_price'], 2), 2, '.', ''); // Item Total

            $dataArr[] = join(',',$detArr);

         // }
        } //eo detail
      } //eo special field check
    } //eo documents

    $data = join("\r\n",$dataArr);


    //create file only if there are successful items.
    $filePath = false;
    if(count($successSEUIdArr)>0){

      //determine seq.
      $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
      if($seqFilename==false){
        BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
        return $this->errorTO;
      }

      //write physical file
      $filePath = $this->createFile($folder, $seqFilename, $data);  //places file in correct folder
      if($filePath == false){
        BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
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
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "34", "", FLAG_ERRORTO_ERROR);
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
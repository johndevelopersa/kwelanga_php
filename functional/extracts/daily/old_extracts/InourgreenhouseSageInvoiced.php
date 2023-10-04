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
class InourgreenhouseSageInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class InourgreenhouseSageInvoicedInit extends extractController {

  private $principalUid = 217; //uid of principal extract.
  private $filename = 'SAGE217[@FSEQ].csv';  //main controller will build full filename with seq. for us.
 // private $crnFilename = 'CRN217[@FSEQ].csv';  //credit note filename

  public function generateOutput(){

    global $ROOT, $PHPFOLDER;

    $depotMapping = array("2"=>"02","5"=>"03");

    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];
    $folder = $this->principalUid . '_in';  //folder replaced with principal id + first WORD of principal.


    //use the receipients listed in the notification table instead of hard coding them!!!
    //expecting only one row loaded per principal extract
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_CUSTOM);
    if (count($reArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];

    if (!$this->skipInsert) {
      // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }


//      //credits and debit notes
//      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, $recipientUId, array(DT_CREDITNOTE));  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
//      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
//        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
//      } else {
//        $this->dbConn->dbinsQuery("commit;");
//      }
    }


    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($this->principalUid, $recipientUId);

    /*  SUCCESS POINT - 1  */
    //nothing to do...
    if(count($seDocs) == 0){
      echo "Successfully Completed Extract : ".get_class($this)." - No entries!<br>";
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
      return $this->errorTO;
    }

    //group array
    $grpDocs = array();
    $psms=$dmUIds=array();
    foreach($seDocs as $k=>$r){

      $type = 'i';
      if ($r['document_type_uid'] == DT_CREDITNOTE){
        $type = 'c';
      }

      $grpDocs[$type][$r['dm_uid']][] = $r;
      $psms[$r["principal_store_uid"]] = $r["principal_store_uid"];
      $dmUIds[] = $r["dm_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals_PA = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 244, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      $sfvals_SA = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 246, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }

    $dataArr = array();
    $row1[] = '"RECTYPE"';
    $row1[] = '"SHIUNIQ"';
    $row1[] = '"SHINUMBER"';
    $row1[] = '"CUSTOMER"';
    $row1[] = '"PRICELIST"';
    $row1[] = '"PONUMBER"';
    $row1[] = '"SHIDATE"';
    $row1[] = '"LOCATION"';
    $row1[] = '"COMPLETE"';
    $row1[] = '"SHIPTRACK"';
    $dataArr[] = join(',',$row1);
    $row2[] = '"RECTYPE"';
    $row2[] = '"SHIUNIQ"';
    $row2[] = '"LINENUM"';
    $row2[] = '"ITEM"';
    $row2[] = '"DESC"';
    $row2[] = '"QTYSHIPPED"';
    $row2[] = '"SHIUNIT"';
    $row2[] = '"PRIUNTPRC"';
    $dataArr[] = join(',',$row2);    
    $row3[] = '"RECTYPE"';
    $row3[] = '"SHIUNIQ"';
    $row3[] = '"LINENUM"';
    $row3[] = '"SERIALNUMF"';
    $dataArr[] = join(',',$row3);   
    $row4[] = '"RECTYPE"';
    $row4[] = '"SHIUNIQ"';
    $row4[] = '"LINENUM"';
    $row4[] = '"LOTNUMF"';
    $dataArr[] = join(',',$row4);
    $row5[] = '"RECTYPE"';
    $row5[] = '"SHIUNIQ"';
    $row5[] = '"UNIQUIFIER"';
    $dataArr[] = join(',',$row5);
    $row6[]= '"RECTYPE"';
    $row6[]= '"SHIUNIQ"';
    $row6[]= '"OPTFIELD"';
    $dataArr[] = join(',',$row6);
    $row7[] = '"RECTYPE"';
    $row7[] = '"SHIUNIQ"';
    $row7[] = '"LINENUM"';
    $row7[] = '"OPTFIELD"';
    $dataArr[] = join(',',$row7);

    foreach($grpDocs as $type => $orders){

    $errorSEUIdArr = array();
    $successSEUIdArr = array();
    $successCount = 0;
    $i = 1;
    $pd = array();
    foreach($orders as $doc){


        /*-------------------------------------------------*/
        /*            START BUILDING OUTPUT
        /*-------------------------------------------------*/

      $accpac =((isset($sfvals_PA[$doc[0]["principal_store_uid"]]))?$sfvals_PA[$doc[0]["principal_store_uid"]]['value']:"");
      $pricelist =((isset($sfvals_SA[$doc[0]["principal_store_uid"]]))?$sfvals_SA[$doc[0]["principal_store_uid"]]['value']:"");
      $invNum = substr(str_pad(((trim($doc[0]["invoice_number"])=="")?$doc[0]["document_number"]:$doc[0]["invoice_number"]),8,"0",STR_PAD_LEFT),2); // always 6
      $dptCode =((isset($depotMapping[$doc[0]["depot_uid"]]))?$depotMapping[$doc[0]["depot_uid"]]:"?{$doc[0]["depot_uid"]}?");
      
        if((empty($sfvals_PA[$doc[0]["principal_store_uid"]]['value'])) || empty($sfvals_SA[$doc[0]["principal_store_uid"]]['value'])) {  //has no special field and/or blank...
          $errorSEUIdArr[] = $doc[0]['se_uid']; //list of smart event errors

      } else {

        $successCount++;
        $successSEUIdArr[] = $doc[0]['se_uid']; //list of smart event success
             
        //RECTYPE 1
        $rowArr = array();
        $rowArr[] = '"1"';
        $rowArr[] = $i;
        $rowArr[] = '"'."RTT"."$invNum".'"';
        $rowArr[] = '"'.$accpac.'"';
        $rowArr[] = '"'.$pricelist.'"';
        $rowArr[] = '"'.$doc[0]["customer_order_number"].'"';
        $rowArr[] = str_replace("-","",$doc[0]["invoice_date"]);
        $rowArr[] = '"'.$dptCode.'"';
        $rowArr[] = '3';
        $rowArr[] = '""';
        $dataArr[] = join(',',$rowArr);

        foreach($doc as $dtl){ //RECTYPE 2
        if(abs($dtl['document_qty'])>0){
          $detArr = array();
          $ln = substr($dtl["line_no"],1);
          $discPerc =(($dtl["selling_price"]>0)?(round($dtl["discount_value"]/$dtl["selling_price"]*100,2)):"0");
          $qty =($dtl["document_qty"]);
          $packing =($dtl['packing']);
                    
          $detArr[] = '"2"';
          $detArr[] = $i;
          $detArr[] = $ln;
          $detArr[] = '"'.$dtl["product_code"].'"';
          $detArr[] = '"'.$dtl["product_description"].'"';
          $detArr[] = $qty;
          $detArr[] = '"'.$packing.'"';
          $detArr[] = round($dtl["selling_price"],2);
          $dataArr[] = join(',',$detArr);
          
            }  
          } //eo detail
          ++$i;
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
         //create actual file to local smollan folder.
        $fp = file_put_contents(DIR_DATA_SURESERVER_NON_FTP_FROM . 'greenhouse/' . $seqFilename, $data);
        if($fp != strlen($data)){
          BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on actual file creation";
          return $this->errorTO;
        } 
      }


      // SETUP DISTRIBUTION
      $postingDistributionTO = new PostingDistributionTO;
      $postingDistributionTO->DMLType = "INSERT";
      $postingDistributionTO->deliveryType = BT_EMAIL;
      $postingDistributionTO->subject = (($type=='i')?$this->getTemplateInvoiceSubject():$this->getTemplateCreditSubject()); //might have no filename if all errors therefore don't display on subject line...
      $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
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
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "289", "", FLAG_ERRORTO_ERROR);
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
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
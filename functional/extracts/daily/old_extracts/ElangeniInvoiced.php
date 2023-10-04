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
class ElangeniInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}



class ElangeniInvoicedInit extends extractController {


  private $principalUid = 5; //uid of principal extract.
  private $invFilename = 'INV03[@FSEQ].txt';
  private $ctrlFilename = 'CTL03[@FSEQ].txt';

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
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];

    // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = true);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
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
    foreach($seDocs as $k=>$r){
      $grpDocs[$r['dm_uid']][] = $r;
    }

    $dataInv = '';
    $dataCtl = '';
    $successSEUIdArr = array();
    $errorSEUIdArr = array();


    foreach($grpDocs as $ord){

      /*-------------------------------------------------*/
      /*            START BUILDING OUTPUT
      /*-------------------------------------------------*/
      $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

      $dataCtl .= 'P' . str_pad(substr($ord[0]['document_number'],-5), 5, 0, STR_PAD_LEFT) . str_pad(substr(abs($ord[0]['invoice_number']),-6), 6, 0, STR_PAD_LEFT) . "\r\n";

        foreach($ord as $d){ //detail rows.

          if(abs($d['document_qty'])>0){

            $dataInv .= 'D';
            $dataInv .= date("ymd", strtotime($ord[0]["invoice_date"]));
            $dataInv .= str_pad(substr($ord[0]['old_account'],-6), 7,  ' ', STR_PAD_RIGHT);
            $dataInv .= 'P' . str_pad(substr($ord[0]['document_number'],-5), 5, 0, STR_PAD_LEFT);
            $dataInv .= str_pad(trim(str_replace(array('"'),array(''),$d['product_code'])), 10,  ' ', STR_PAD_RIGHT);
            $dataInv .= str_pad('', 27, ' ');
            $dataInv .= str_pad(abs($d['document_qty']), 6,  ' ', STR_PAD_LEFT);
            $dataInv .= " ";
            $dataInv .= str_pad($d['client_line_no'], 4,  ' ', STR_PAD_LEFT);
            $dataInv .= str_pad('', 38, ' ');
            $dataInv .= "8";
            $dataInv .= "\r\n";

          }
        } //eo detail
     #} //eo special field check
    } //eo documents

    //create file only if there are successful items.
    $filePath1 = false;
    if(count($successSEUIdArr)>0){

        //create files for each row - total files have same seq....
        //determine seq.
        $seqFilename = $this->setFilenameFSEQ("NOFILENAME", $this->principalUid, false, 3);
        if($seqFilename==false){
          BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
          return $this->errorTO;
        }

        $invFilename = str_replace('[@FSEQ]', $this->fileSequence, $this->invFilename);
        $ctrlFilename = str_replace('[@FSEQ]', $this->fileSequence, $this->ctrlFilename);  //replace file key in total file.


        //write physical file
        $filePath1 = $this->createFile($folder, $invFilename, $dataInv);  //places file in correct folder
        if($filePath1 == false){
          BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
          return $this->errorTO;
        }
        //write physical file
        $filePath2 = $this->createFile($folder, $ctrlFilename, $dataCtl);  //places file in correct folder
        if($filePath2 == false){
          BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
          return $this->errorTO;
        }
    }



    //FILE 1
    //SETUP DISTRIBUTION
    $postingDistributionTO = new PostingDistributionTO;
    $postingDistributionTO->DMLType = "INSERT";
    $postingDistributionTO->deliveryType = BT_EMAIL;
    $postingDistributionTO->subject = $this->getTemplateInvoiceSubject(); //might have no filename if all errors therefore don't display on subject line...
    $postingDistributionTO->body = $this->getTemplateBody($principalName, count($successSEUIdArr), count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
    if($filePath1!=false){
      $postingDistributionTO->attachmentFile = $filePath1;
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


    //FILE 2
    //SETUP DISTRIBUTION
    $postingDistributionTO = new PostingDistributionTO;
    $postingDistributionTO->DMLType = "INSERT";
    $postingDistributionTO->deliveryType = BT_EMAIL;
    $postingDistributionTO->subject = $this->getTemplateInvoiceSubject(); //might have no filename if all errors therefore don't display on subject line...
    $postingDistributionTO->body = $this->getTemplateBody($principalName, count($successSEUIdArr), count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
    if($filePath2!=false){
      $postingDistributionTO->attachmentFile = $filePath2;
    }

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
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), basename($filePath1), basename($filePath2));
      if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
        return $this->errorTO;
      }
    }

//    //ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
//    if (sizeof($errorSEUIdArr) > 0) {
//      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "172", "", FLAG_ERRORTO_ERROR);
//      if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
//        $this->errorTO->type = FLAG_ERRORTO_ERROR;
//        $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
//        BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
//        return $this->errorTO;
//      }
//    }
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
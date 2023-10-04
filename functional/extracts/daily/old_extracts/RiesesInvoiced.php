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
class RiesesInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}



class RiesesInvoicedInit extends extractController {


  private $principalUid = 9; //uid of principal extract.
  private $invFilename = 'INV18[@FSEQ].edi';  //main controller will build full filename with seq. for us.
  private $creFilename = 'CRN12[@FSEQ].edi';  //main controller will build full filename with seq. for us.


  public function generateOutput(){

    global $ROOT, $PHPFOLDER;
    include_once($ROOT.$PHPFOLDER.'DAO/DocumentUpdateDAO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
    $documentUpdateDAO = new DocumentUpdateDAO($this->dbConn);
    $transDAO = new TransactionDAO($this->dbConn);

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
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, $recipientUId);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
    }

    //will return all queued items... invoices, credits, debits.
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
    $grpDocs = $successSEUIdArr = $errorSEUIdArr = array();
    foreach($seDocs as $k=>$r){
        if($r['document_type_uid'] == DT_CREDITNOTE){
          $grpDocs['c'][$r['dm_uid']][] = $r;
        } else if($r['document_type_uid'] == DT_DEBITNOTE){
          //don't send debits
        } else {
          $grpDocs['i'][$r['dm_uid']][] = $r;
        }

    }


    $successCount = 0;
    $dataArr = array();
    foreach($grpDocs as $type => $docArr){

      /*-------------------------------------------------*/
      /*            INVOICES
      /*-------------------------------------------------*/
      if($type == 'i'){

        $filename = $this->invFilename;
        $sequenceType = 1;
        $dataArr = array();
        foreach($docArr as $ord){

          $successCount++;

          foreach($ord as $d){

            if(abs($d['document_qty'])>0){

              $dArr = array();
              $dArr[] = date("Ymd", strtotime($ord[0]["invoice_date"]));
              $dArr[] = abs($ord[0]["document_number"]);
              $dArr[] = abs($d['document_qty']);
              $dArr[] = $d['client_line_no'];
              $dataArr[] = str_pad(join('|',$dArr),501,' ', STR_PAD_RIGHT);

            }
          }

          $successSEUIdArr[$ord[0]['se_uid']] = $ord[0]['se_uid'];

        }
        $data = join("\r\n",$dataArr);


      /*-------------------------------------------------*/
      /*            CREDITS
      /*-------------------------------------------------*/
      } elseif($type == 'c'){

        $filename = $this->creFilename;
        $sequenceType = 4;
        $dataArr = array();
        foreach($docArr as $ord){

          $successCount++;
          $fetchDoc = $documentUpdateDAO->getDocumentbyPrincipalDepotDocumentNo($this->principalUid, $ord[0]["depot_uid"], $ord[0]["source_document_number"]);
          if (!isset($fetchDoc[0]['dmUId']) || !isset($ord[0])) {
            echo "Rieses Extract returned no document - Depot:{$ord[0]["depot_uid"]} Source Doc:{$ord[0]["source_document_number"]} DMUId:{$ord[0]["dm_uid"]}.";
            print_r($ord);
            $errorSEUIdArr[$ord[0]['se_uid']] = $ord[0]['se_uid'];
            continue;
          }
          $docUID = $fetchDoc[0]['dmUId'];
          $orgDocDetailArr = $transDAO->getDocumentDetails($docUID);

          foreach($ord as $d){

            if(abs($d['document_qty'])>0){

              $dArr = array();
              $dArr[] = date("Ymd", strtotime($ord[0]["order_date"]));
              $dArr[] = abs($ord[0]["source_document_number"]);
              $dArr[] = abs($d['document_qty']);

              foreach($orgDocDetailArr as $orgDet){
                if($orgDet['product_uid'] == $d['product_uid']){
                  $dArr[] = $orgDet['client_line_no'];
                  break;
                }
              }
              $dArr[] = (($ord[0]['reason_uid']>0)?($ord[0]['reason_uid']):('99'));
              $dataArr[] = str_pad(join('|',$dArr),501,' ', STR_PAD_RIGHT);

            }
          }

          $successSEUIdArr[$ord[0]['se_uid']] = $ord[0]['se_uid'];

        }
        $data = join("\r\n",$dataArr);

      }

      $data = '' . join("\r\n",$dataArr);


      //create file only if there are successful items.
      $filePath = false;
      if(count($successSEUIdArr)>0){

        //determine seq.
        $seqFilename = $this->setFilenameFSEQ($filename, $this->principalUid, $sequenceType, 3, self::setFilenameFSEQ_LenType_PAD);
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
      if($sequenceType == 4){
        $postingDistributionTO->subject = $this->getTemplateCreditSubject(); //might have no filename if all errors therefore don't display on subject line...
      } else {
        $postingDistributionTO->subject = $this->getTemplateInvoiceSubject(); //might have no filename if all errors therefore don't display on subject line...
      }
      $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, 0, $this->getManagementURL($this->principalUid));
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
    // ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
    if (sizeof($errorSEUIdArr) > 0) {
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "Source Doc does not exist", "", FLAG_ERRORTO_ERROR);
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
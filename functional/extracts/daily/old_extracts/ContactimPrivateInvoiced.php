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
class ContactimPrivateInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class ContactimPrivateInvoicedInit extends extractController {


  private $principalUid = 152; //uid of principal extract.
  private $invFilename = 'IN[@REGION]49[@FSEQ].TXT';  //main controller will build full filename with seq. for us.
  private $crnFilename = 'CR[@REGION]49[@FSEQ].TXT';  //main controller will build full filename with seq. for us.
  private $debFilename = 'DN[@REGION]49[@FSEQ].TXT';  //main controller will build full filename with seq. for us.


  public function generateOutput(){

    global $ROOT, $PHPFOLDER;


    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];
    $folder = $this->principalUid . '_contactimprivate'; // . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.


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

      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, $recipientUId);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
    }

    //will return all queued items...
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
      $index = 'i';
      if($r['document_type_uid'] == DT_CREDITNOTE){
        $index = 'c';
      } else if ($r['document_type_uid'] == DT_DEBITNOTE){
        $index = 'd';
      }
      $grpDocs[$index][$r['depot_uid']][$r['dm_uid']][] = $r;
    }

    // no special field values -> use old account

    //depot translate
    $depotTrans = array(
      2 =>   'J', //UJ
      3 =>   'D', //UD
      7 =>   'T'  //TE
    );


    foreach($grpDocs as $type => $depotOrders){ //each type has its own loop

      foreach($depotOrders as $depotId => $orders){  //within each type is each depot -> create file per depot.


        $data = '';
        $errorSEUIdArr = array(); //no errors can really happen as we use the old account number.
        $successSEUIdArr = array();

        $fSeqDocumentType = false;
        $filename = $this->invFilename;
        if($type == 'c'){
          $filename = $this->crnFilename;
          $fSeqDocumentType = 4;
        } elseif ($type == 'd'){
          $filename = $this->debFilename;
          $fSeqDocumentType = 8;
        }

        $regionCode = (isset($depotTrans[$depotId])) ? $depotTrans[$depotId] : false;
        $filename = str_replace('[@REGION]',$regionCode,$filename);

        //skip over any other depot then ones in the depotTrans list.
        if($regionCode === false){

          foreach($orders as $ord){ //document loop
            $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success
          }

        } else {

          /*-------------------------------------------------*/
          /* START BUILDING OUTPUT
           */

          foreach($orders as $ord){ //document loop

            if(empty($ord[0]['old_account'])){

              $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors

            } else {

              $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success


              foreach($ord as $d){ //detail rows => rows per order.

                $productCode = str_replace(array("\t","\r","'",'"'),array('','','',''), trim($d['product_code']));

                $data .= str_pad(substr(abs($ord[0]['document_number']),-6), 6, '0', STR_PAD_LEFT); //grv number for credits/debits.
                $data .= str_pad(substr($productCode,0,6), 6, ' ', STR_PAD_RIGHT);
                $data .= str_pad($ord[0]['old_account'], 9, ' ', STR_PAD_RIGHT);
                $data .= strtoupper($type); //I, C, D
                $data .= date("Y-m-d", strtotime($ord[0]["invoice_date"]));  //DATE
                $data .= date("Y-m-d", strtotime($ord[0]["order_date"]));  //DATE
                $data .= str_pad(substr($productCode, 0, 12), 12, ' ', STR_PAD_RIGHT);
                $data .= str_pad(abs($d['document_qty']), 6, '0', STR_PAD_LEFT);
                $data .= str_pad(number_format(round(abs($d['selling_price']), 2), 2, '', ''), 7, 0, STR_PAD_LEFT);  //5,2 decimal.
                $data .= str_pad(number_format(round(abs($d['discount_value']), 2), 2, '', ''), 5, 0, STR_PAD_LEFT);  //3,2 decimal.
                $data .= str_pad(number_format(round(abs($d['vat_amount']), 2), 2, '', ''), 8, 0, STR_PAD_LEFT);  //6,2 decimal.
                $data .= str_pad(number_format(round(abs($d['total']), 2), 2, '', ''), 10, 0, STR_PAD_LEFT);  //6,2 decimal.
                $data .= str_pad(substr($ord[0]['customer_order_number'],0,15), 15, ' ', STR_PAD_RIGHT);
                if($type != 'i'){
                  $data .= str_pad(substr(abs($ord[0]['source_document_number']),-6), 6, '0', STR_PAD_LEFT); //grv number for credits/debits.
                }
                $data .= str_pad('', 395, ' ') . "\r\n";

              } //eo detail
            } //eo special field check
          } //eo document
          $data .= chr(26);
        }


        if($regionCode !== false){


          /*-----------------------------------------------------------------------*/
          //create file only if there are successful items.
          $filePath = false;
          if(count($successSEUIdArr)>0){

            $seqFilename = $this->setFilenameFSEQ($filename, $this->principalUid, $fSeqDocumentType, 3, self::setFilenameFSEQ_LenType_PAD, $depotId);
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
          if($type == 'i'){
            $postingDistributionTO->subject = $this->getTemplateInvoiceSubject(basename($filePath)); //might have no filename if all errors therefore don't display on subject line...
          } else {
            $postingDistributionTO->subject = $this->getTemplateCreditSubject(basename($filePath)); //might have no filename if all errors therefore don't display on subject line...
          }
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
          $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "19", "", FLAG_ERRORTO_ERROR);
          if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
            BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
            return $this->errorTO;
          }
        }
        /*-----------------------------------------------------------------------*/


      } //eo depot array -> depot/file.
    } //eo group array -> type


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
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
class ChillBeveragesInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class ChillBeveragesInvoicedInit extends extractController {


  private $principalUid = 232; //uid of principal extract.
  private $invFilename = 'INV232[@FSEQ].csv';  //invoice filename
  private $crnFilename = 'CRN232[@FSEQ].csv';  //credit & debit note filename


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
    if (count($reArr)==0){
      BroadcastingUtils::sendAlertEmail("System Error", "Export failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }

    $recipientUId = $reArr[0]['uid'];


    // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
      //invoices and cancelled items
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
    $psms=array();
    foreach($seDocs as $k=>$r){

        //place into which type...
        $index = 'i';
        if($r['document_type_uid'] == DT_CREDITNOTE){
          $index = 'c';
        } else if($r['document_type_uid'] == DT_DEBITNOTE){
          $index = 'd';
        }
        $grpDocs[$index][$r['dm_uid']][] = $r;
        $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 281, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }


    $depotArr = array(
                        2 => 'ULL', //UJ
                        5 => 'ULL', //UC
                        6 => 'ULL', //FP/PE
                        7 => 'ULL', //TE
                        3 => 'ULL' //UD
                     );

    //BUILD FILE FOR EACH TYPE
    foreach($grpDocs as $type => $docArr){


      $errorSEUIdArr = array(); //update errors at the end.
      $dataArr = array();
      $successSEUIdArr = array();
      foreach($docArr as $doc){

        if(empty($sfvals[$doc[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...

          $errorSEUIdArr[] = $doc[0]['se_uid']; //list of smart event errors

        } else {

          $successSEUIdArr[] = $doc[0]['se_uid']; //list of smart event success

          switch ($type){
            case 'i': //invoices
              $typeID = 4;
              $filename = $this->invFilename;
              break;
            case 'c': //credits
              $typeID = 1;
              $filename = $this->crnFilename;
              break;
            case 'd': //debits
              $typeID = 4;
              $filename = $this->crnFilename;
              break;
          }


          foreach($doc as $i => $d){ //detail rows.

            //start building output
            $rowArr = array();
            $rowArr[] = $typeID;
            $rowArr[] = '0';
            $rowArr[] = '0';
            if($type == 'i'){
              $rowArr[] = 'UL' . abs($doc[0]['document_number']);  //document_number
            } else {
              $rowArr[] = 'UL' . abs($doc[0]['alternate_document_number']);  //alternate document_number
            }
            $rowArr[] = $sfvals[$doc[0]["principal_store_uid"]]['value'];
            $rowArr[] = trim($doc[0]['deliver_name']);
            if($type != 'i'){
              $rowArr[] = 'UL'.abs($doc[0]['source_document_number']);
            } else {
              $rowArr[] = '';
            }
            $rowArr[] = date('d/m/Y', strtotime($doc[0]['invoice_date']));  //14/01/2013
            $rowArr[] = '0';
            $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_name']));
            $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_add1']));
            $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_add2']));
            $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_add3']));
            $rowArr[] = '';
            $rowArr[] = '';
            $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_name']));
            $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_add1']));
            $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_add2']));
            $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_add3']));
            $rowArr[] = '';
            $rowArr[] = '';
            $rowArr[] = ''; //P_REPCD
            $rowArr[] = ''; //P_PROJC
            $rowArr[] = abs($doc[0]['document_number']) . " - " . str_replace(array('"',"'",','), array('','',''), trim($doc[0]['customer_order_number']));
            $rowArr[] = '';
            $rowArr[] = '0';
            $rowArr[] = '0';
            $rowArr[] = '0';
            $rowArr[] = '0';
            $rowArr[] = (isset($depotArr[$doc[0]['depot_uid']])) ? $depotArr[$doc[0]['depot_uid']] : '';
            $rowArr[] = trim($doc[$i]['product_code']);
            $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[$i]['product_description']));
            $rowArr[] = abs($doc[$i]['document_qty']); //use abs as we store credit notes negatively.
            $rowArr[] = number_format(round($doc[$i]['selling_price'], 2), 2, '.', ''); //SELLING PRICE - NUM
            $rowArr[] = ($doc[$i]['discount_value'] > 0) ? number_format(round($doc[$i]['discount_value'] / $doc[$i]['selling_price'] * 100, 2), 2, '.', '') : '0.00';
            $rowArr[] = '1';
            $rowArr[] = '';
            $rowArr[] = '';
            $rowArr[] = '0';
            $rowArr[] = '';
            $rowArr[] = '0';
            $rowArr[] = '';
            $rowArr[] = '';
            $rowArr[] = '';
            $rowArr[] = '1';
            $rowArr[] = '0';
            $rowArr[] = '0';

            if(abs($doc[$i]['document_qty']) > 0){ //skip if qty is zero.
              $dataArr[] = join(',', $rowArr);
            }

          } //eof detail
        } //has spf value.
      } //eof documents in type


      /*------------------ SUCCESS : START ---------------*/
      //success must be 1 for us to queue the ftp item.
      $filePath = false;
      if(count($successSEUIdArr)>0){

        //determine seq.
        $seqFilename = $this->setFilenameFSEQ($filename, $this->principalUid);
        if($seqFilename==false){
          BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
          return $this->errorTO;
        }

        //write physical file - placed in extracts folder
        $filePath = $this->createFile($folder, $seqFilename, join("\r\n", $dataArr));  //places file in correct folder
        if($filePath == false){
          BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on backup file creation";
          return $this->errorTO;
        }
      }


      // SETUP DISTRIBUTION
      $postingDistributionTO = new PostingDistributionTO;
      $postingDistributionTO->DMLType = "INSERT";
      $postingDistributionTO->deliveryType = BT_EMAIL;
      if($type == 'i'){
        $postingDistributionTO->subject = $this->getTemplateInvoiceSubject();
      } else {
        $postingDistributionTO->subject = $this->getTemplateCreditSubject(); //might have no filename if all errors therefore don't display on subject line...
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

      if ($recipientsCheckCount==0) { //MAKE SURE ATLEAST ONE RECEIPENT PASSED
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in ".get_class($this)." extract no valid Recipient/Contact found, no outgoing error mail generated!";
        BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
        return $this->errorTO;
      }


      //UPDATE ANY SMART EVENTS.
      if (sizeof($successSEUIdArr) > 0) {
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), basename($filePath), "");
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
      }

      if (sizeof($errorSEUIdArr) > 0) {
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "281", "", FLAG_ERRORTO_ERROR);
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
      }

    } //eof all documents


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
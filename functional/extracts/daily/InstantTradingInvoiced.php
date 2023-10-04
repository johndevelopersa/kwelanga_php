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
class InstantTradingInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}



class InstantTradingInvoicedInit extends extractController {

  private $principalUid = 198; //uid of principal extract.
  private $filename = 'IO[DATETIME].txt';
  private $crnFilename = 'CRN198[DATETIME].csv';  //credit note filename

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

    if (!$this->skipInsert) {
      // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = false, $p_dtArr = false, $p_wDSArr = false, $fromInvDate=false, $toInvDate=false, $chainUIdIn=false, $dataSource=false, $capturedBy=false, $depotUId = 195);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }

      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, $recipientUId, array(DT_CREDITNOTE));  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
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

      $type = 'i';
      if ($r['document_type_uid'] == DT_CREDITNOTE){
        $type = 'c';
      }
      $grpDocs[$type][$r['dm_uid']][] = $r;
      $psms[$r["principal_store_uid"]] = $r["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals_EN = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 229, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }



    foreach($grpDocs as $type => $orders){

    $dataArr = array();
    $errorSEUIdArr = array();
    $successSEUIdArr = array();
    $successCount = 0;

      foreach($orders as $ord){

        $locationCode = "";
        switch ($ord[0]["depot_uid"]) {
          case 195:
            $locationCode = "DEPOT09";
            break;
          default:
            $locationCode = "NOT CONFIGURED";
        }


        /*-------------------------------------------------*/
        /*            START BUILDING OUTPUT
        /*-------------------------------------------------*/
        if(empty($sfvals_EN[$ord[0]["principal_store_uid"]]['value'])) {  //has no special field and/or blank...

          $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors

        } elseif ($ord[0]["depot_uid"] == 234 ) {
        	// Do Nothing        	
        } else {

          $successCount++;
          $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success
          $storeAcc = trim($sfvals_EN[$ord[0]["principal_store_uid"]]['value']);

          //array containing list of row values
          $rowArr = array();

          if($type == 'i'){
            $rowArr[] = '"Header"';

            $rowArr[] = '"' . str_pad($ord[0]['invoice_number'], 8, 0, STR_PAD_LEFT) . '"';
            $rowArr[] = '';
            $rowArr[] = '';
            $rowArr[] = '"'.$storeAcc.'"';
            $rowArr[] = '';
            $rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["invoice_date"])).'"';  //DATE (DD/MM/YYYY)
            $rowArr[] = '"SO' . str_pad(substr($ord[0]['document_number'],-6), 6, 0, STR_PAD_LEFT) . '"';

            $rowArr[] = '';
            $rowArr[] = '';
            $rowArr[] = '';
            $rowArr[] = '';
            $rowArr[] = '';

            $rowArr[] = '"'.trim(str_replace(array('"',"'"),array('',''),$ord[0]["deliver_add1"])).'"';
            $rowArr[] = '"'.trim(str_replace(array('"',"'"),array('',''),$ord[0]["deliver_add2"])).'"';

            $rowArr[] = '"0"';

            $dataArr[] = join(',',$rowArr);
          }


          foreach($ord as $d){ //detail rows.

            $detArr = array();
            if($type == 'i'){
              $detArr[] = '"Detail"';
              $detArr[] = '0';

              $detArr[] = abs($d['document_qty']);
              $detArr[] = number_format(abs(round($d['net_price'], 2)), 2, '.', ''); //SELLING PRICE - NUM
              $detArr[] = abs($d['client_line_no']);
              $detArr[] = '';

              $detArr[] = $d['vat_rate'];
              $detArr[] = '""';
              $detArr[] = "0.0";
              $detArr[] = '"' . trim(str_replace(array('"'),array(''),$d['product_code'])) . '"';
              $detArr[] = '"' . trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description'])) . '"';
              $detArr[] = "009";

              $dataArr[] = join(',',$detArr);
            } else {
              $detArr[] = substr(str_pad($locationCode,9," ",STR_PAD_RIGHT),0,9);
              $detArr[] = str_pad($ord[0]['invoice_number'],8,"0",STR_PAD_LEFT);
              $detArr[] = "       ";
              $detArr[] = str_pad(abs($d['client_line_no']),12," ",STR_PAD_RIGHT);
              $detArr[] = str_pad(trim(str_replace(array('"'),array(''),$d['product_code'])),15," ",STR_PAD_RIGHT);
              $detArr[] = str_pad(date("d/m/Y", strtotime($ord[0]["invoice_date"])),11," ",STR_PAD_RIGHT);
              $detArr[] = str_pad(abs($d['document_qty']),10," ",STR_PAD_RIGHT);

              $dataArr[] = join('',$detArr);
            }

          } //eo detail
        } //eo special field check
      } //eo documents

      $data = join("\r\n",$dataArr);


      //create file only if there are successful items.
      $filePath = false;
      if(count($successSEUIdArr)>0){


        //determine seq.
        $this->filename = str_replace("[DATETIME]",date("YmdHis"), (($type=='i')?$this->filename:$this->crnFilename));

        //write physical file
        $filePath = $this->createFile($folder, $this->filename, $data);  //places file in correct folder
        if($filePath == false){
          BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
          return $this->errorTO;
        }

        // copy to ftp folder
        $copy = copy($ROOT.$filePath, DIR_DATA_NON_FTP_FROM."ftp/instant/invoices/".basename($this->filename));
        if(!$copy){
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="failed copying file from archives {$filePath} to ftp dir in instantTradingInvoiced.php";
          BroadcastingUtils::sendAlertEmail("Error in Extract Adaptor InstantTradingInvoiced", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }


      }

      /*
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
*/

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
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "229", "", FLAG_ERRORTO_ERROR);
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
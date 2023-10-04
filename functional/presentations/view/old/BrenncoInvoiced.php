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
class BrenncoInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class BrenncoInvoicedInit extends extractController {


  private $principalUid = 4; //uid of principal extract.
  private $invFilename = 'INV47[@FSEQ].csv';  //main controller will build full filename with seq. for us.
  private $creFilename = 'RTN47[@FSEQ].csv';  //main controller will build full filename with seq. for us.
  private $clcFilename = 'CLM47[@FSEQ].csv';  //main controller will build full filename with seq. for us.
  private $folder = 'brennco';


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

      //buyer originated claims
      $rTO = $this->postExtractDAO->queueAllClaims($this->principalUid, $recipientUId);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllClaims in ".get_class($this)." " . $rTO->description, "Y");
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
      $index = 'i';
      if($r['document_type_uid'] == DT_CREDITNOTE){
        $index = 'c';
      } else if($r['document_type_uid'] == DT_DEBITNOTE){
        $index = 'd';
      } else if($r['document_type_uid'] == DT_BUYER_ORIGINATED_CREDIT_CLAIM){
        $index = 'clc';
      }
      $grpDocs[$index][$r['dm_uid']][] = $r;
      $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }


    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfBrenncoRegion = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 6, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      $sfBrenncoAcc = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 9, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      $sfNNBAcc = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 8, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      $sfNNB2Acc = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 196, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }


    foreach($grpDocs as $type => $docArr){

      $errorSEUIdArr = array();
      $successSEUIdArr = array();
      $dataArr = array();

      if($type == 'd'){
        foreach($docArr as $hr){
          //simple mark the debits as done.
          $successSEUIdArr[] = $hr[0]['se_uid']; //list of smart event success
        }
      } else {

        foreach($docArr as $hr){

          /*-------------------------------------------------*/
          //    calculate which sp field to use
          /*-------------------------------------------------*/
          $buyerRef = abs($hr[0]['buyer_account_reference']);

          // the first 3 accounts below are the old codes included to maintain overlap problems, can be removed in due course
          if($buyerRef == '40009124' || $buyerRef == '30006184' || $buyerRef == '90006095' || $buyerRef == '1000002810'){
            //PICK N PAY BRAND PET FOOD     1000002810  NNB Account number 40-100…
            $chosenSFVal = $sfNNBAcc;
          } else if($buyerRef == '1000001066'){
            //PICK N PAY BRAND BEANS                           1000001066                                         NNB2 Account number 40-2100…
            $chosenSFVal = $sfNNB2Acc;
          } else {

            //IF CAPTURED AND PRODUCT CODES BEGIN WITH 9 AND NOT 980 THEN USE NNB1 ACC.
            if(substr(trim($hr[0]['product_code']),0,1) == '9' && substr($hr[0]['product_code'],0,3)!='980'){
              $chosenSFVal = $sfNNBAcc;
            } else {
              //DEFAULT TO BRENNCO ACOCUNT : CAPTURE AND GPH
              $chosenSFVal = $sfBrenncoAcc;
            }
          }
          /*-------------------------------------------------*/
          if(
              empty($sfBrenncoRegion[$hr[0]["principal_store_uid"]]['value']) ||  //$sfBrenncoRegion must always be set.
              //empty($sfBrenncoAcc[$hr[0]["principal_store_uid"]]['value']) ||
              empty($chosenSFVal[$hr[0]["principal_store_uid"]]['value']) //might be the brennco account again... depending on above logic.
            ){

            $errorSEUIdArr[] = $hr[0]['se_uid']; //list of smart event errors

          } else {  	

            if ($hr[0]['depot_uid'] == 3) {
                   $regionCode = '401';
            } elseif ($hr[0]['depot_uid'] == 5) {
                   $regionCode = '403';
            } elseif ($hr[0]['depot_uid'] == 6) {
                   $regionCode = '405';
            } else {
                   $regionCode = trim($sfBrenncoRegion[$hr[0]["principal_store_uid"]]['value']);
            }  

            $successSEUIdArr[] = $hr[0]['se_uid']; //list of smart event success
            $storeAccount = trim($chosenSFVal[$hr[0]["principal_store_uid"]]['value']);

            /*-------------------------------------------------*/
            /* START BUILDING OUTPUT
             */

            $dataH = array();
            $dataH[] = 'H'; //header
            if($type == 'c'){
              $dataH[] = abs($hr[0]['source_document_number']); //doc number for inv and cre.
            } else {
              $dataH[] = abs($hr[0]['document_number']); //doc number for inv and cre.
            }
            $dataH[] = $storeAccount;

            if($type == 'c'){
              $dataH[] = date("dmy", strtotime($hr[0]["invoice_date"]));  //DATE
            } else {
              $dataH[] = date("dmy", strtotime($hr[0]["order_date"]));  //DATE
            }

            $dataH[] = '40-' . $regionCode; //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            $dataH[] = str_replace(array(',','"',"'"), array('','',''), trim($hr[0]['customer_order_number']));
            if($type == 'c'){
              $dataH[] = abs($hr[0]['source_document_number']); //doc number for inv and cre.
            } else {
              $dataH[] = abs($hr[0]['document_number']); //doc number for inv and cre.
            }
            $altcode = '';
            if(trim($hr[0]['additional_type']) == '11_RETURNS_DAMAGE')  {
            	$altcode = 'RETURN';
            }
            if($type == 'c'){
              $dataH[] = (!empty($hr[0]['reason_description'])) ? trim($hr[0]['reason_description']) : "Unknown Reason"; //blank
            } else if($type == 'clc'){
              $dataH[] = (!empty($hr[0]['additional_type'])) ? trim($hr[0]['additional_type']) : "Unknown Reason"; //blank
            } else {
              $dataH[] = ' '; //blank
            }
            $dataH[] = trim($hr[0]['branch_code']); //blank
            if($type == 'c'){
              $dataH[] = ' '; //blank
              $dataH[] = abs($hr[0]['alternate_document_number']);
            } else {
              $dataH[] = ' '; //blank
              $dataH[] = $regionCode . '-' . (!empty($hr[0]['invoice_number']) ? str_pad(substr(abs($hr[0]['invoice_number']),-6), 6, '0', STR_PAD_LEFT) : str_pad(substr(abs($hr[0]['document_number']),-6), 6, '0', STR_PAD_LEFT)); //blank : " + BRENREG + "-" +DDNO
            }
            $dataArr[] = str_pad(join(',', $dataH),500 , ' ', STR_PAD_RIGHT);

            //detail rows
            foreach($hr as $d){ //detail rows.
              if(abs($d['document_qty'])>0){  //invoice qty greater then zero.
                $dataD = array();
                $dataD[] = 'D'; //detail
                if($altcode == 'RETURN') {
                   $dataD[] = str_replace(array("\t","\r","'",'"',','),array('','','','',''), trim($d['alt_code']));
                } else {
                   $dataD[] = str_replace(array("\t","\r","'",'"',','),array('','','','',''), trim($d['product_code']));
                }
                $dataD[] = abs($d['document_qty']);
                $dataD[] = number_format(round($d['net_price'], 2), 2, '.', '');
                $dataArr[] = str_pad(join(',', $dataD),500 , ' ', STR_PAD_RIGHT);
              }
            } //eo detail
          }
        }
        $data = join("\r\n",$dataArr);  //build file.


        if ($type == 'i') {
          $filename = $this->invFilename;
          $dtSeq = false;
        } else if ($type == 'clc') {
          $filename = $this->clcFilename;
          $dtSeq = DT_BUYER_ORIGINATED_CREDIT_CLAIM;
        } else {
          $filename = $this->creFilename;
          $dtSeq = DT_CREDITNOTE;
        }

        /*-------------------------------------------------*/
        /*                  EMAIL FILE
        /*-------------------------------------------------*/
        //create file only if there are successful items.
        $filePath = false;
        if(count($successSEUIdArr)>0){

          $seqFilename = $this->setFilenameFSEQ($filename, $this->principalUid, $dtSeq, 3, self::setFilenameFSEQ_LenType_PAD);
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
          $postingDistributionTO->subject = $this->getTemplateInvoiceSubject();
        } else if($type == 'clc'){
          $postingDistributionTO->subject = $this->getTemplateClaimSubject();
        } else {
          $postingDistributionTO->subject = $this->getTemplateCreditSubject();
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

      } //eo invoices and credit else.


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
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "6,9,8,196", "", FLAG_ERRORTO_ERROR);
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
      }
      /*-------------------------------------------------*/


    } //eo groups


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
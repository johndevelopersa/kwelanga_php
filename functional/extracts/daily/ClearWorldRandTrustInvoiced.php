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
class ClearWorldRandTrustInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class ClearWorldRandTrustInvoicedInit extends extractController {
	
  private $principalUid = 216; //uid of principal extract.
  private $filename = 'RAND_TRUST_INVOICES_[@FSEQ].csv'; //main controller will build full filename with seq. for us.
  private $crnFilename = 'RAND_TRUST_CREDITS_[@FSEQ].csv';  //credit note filename
  
 

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
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_ALTCUSTOM2);
    if (count($reArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];

    if (!$this->skipInsert) {
      // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = false , $dtarr = array(DT_ORDINV, DT_ORDINV_ZERO_PRICE));  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
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
      $sfvals_PA = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 324, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }

    foreach($grpDocs as $type => $orders){

    $dataArr = array();
    $errorSEUIdArr = array();
    $successSEUIdArr = array();
    $successCount = 0;    
    
    $fmtArr    = array();
    $fmtArr[]  = 'FMT';
    if ($type == 'c') {
    	$fmtArr[]  = 'CrNoteSchedule';
    } else {
    	$fmtArr[]  = 'InvoiceSchedule';
    	} 
    $dataArr[] = join(',',$fmtArr);    
    
    $fmtArr    = array();    
    $fmtArr[]  = 'SET';
    $fmtArr[]  = 'ClientName=Clear World Supplies';
    $dataArr[] = join(',',$fmtArr);  
    
    $fmtArr    = array();    
    $fmtArr[]  = 'SET';
    $fmtArr[]  = 'ClientCode=649';
    $dataArr[] = join(',',$fmtArr);  

    $fmtArr    = array();    
    $fmtArr[]  = 'SET';
    $fmtArr[]  = 'BatchDate='.date('d/m/y');
    $dataArr[] = join(',',$fmtArr);  

    $fmtArr    = array();	
    $fmtArr[]  = 'REM';
    $fmtArr[]  = 'Rand Trust Financiers';
    $dataArr[] = join(',',$fmtArr);  

    $fmtArr    = array();
    $fmtArr[]  = 'REM';	
    $fmtArr[]  = 'Pursuant to the current Memorandum of Agreement in force between the undermentioned seller and Rand Trust Financiers (Pty) Ltd';
    $dataArr[] = join(',',$fmtArr);  

    $fmtArr    = array();
    $fmtArr[]  = 'REM';
    $fmtArr[]  = 'this schedule of debts is delivered by way of an offer for sale thereof';
    $dataArr[] = join(',',$fmtArr); 
    
    $fmtArr    = array();	
    $fmtArr[]  = 'HDR';
    $fmtArr[]  = 'AcNo';
    $fmtArr[]  = 'CustName';
    $fmtArr[]  = 'TrnAmount';
    if ($type == 'i') {
      $fmtArr[]  = 'InvNo';
    } else {
      $fmtArr[]  = 'CrNoteNo';
      } 
    if ($type == 'i') {
      $fmtArr[]  = 'OrderNo';
    } else {
      $fmtArr[]  = 'InvNo';
      }
    if ($type == 'i') { 	
    $fmtArr[]  = 'ProofOfDel';
    $fmtArr[]  = 'AssetAc';
  }
    $fmtArr[]  = 'CustBranch';
    $fmtArr[]  = 'TrnDate';

    $dataArr[] = join(',',$fmtArr); 
    
    
      foreach($orders as $ord){

        /*-------------------------------------------------*/
        /*            START BUILDING OUTPUT
        /*-------------------------------------------------*/
        
    if(empty($sfvals_PA[$ord[0]["principal_store_uid"]]['value'])) {  //has special field and not blank...
          $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors
    } else {
             $successCount++;
             $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

              $detArr = array();
              $detArr[] = '';
              $detArr[] = $sfvals_PA[$ord[0]["principal_store_uid"]]['value'];
              $detArr[] = trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$ord[0]['deliver_name']));
              $invtot=0;
              foreach($ord as $d){ //detail rows.
                 $invtot+=$d['extended_price'] ;
              }  
              $gtotal = round($invtot * VAL_VAT_RATE_ADD ,4)	;
              
              if(strpos($gtotal, '.') == 0) {
                  $gtotal = $gtotal . '.00';
              } 
                            
              $detArr[] =substr($gtotal, 0, ((strpos($gtotal, '.')+1)+2)); 
              
              if ($type == 'i') { 
                 $detArr[] = substr($ord[0]['document_number'],2,6);
              } else {
                 $detArr[] = substr($ord[0]['alternate_document_number'],2,6);
              }   
                 
              if ($type == 'i') { 
                 $detArr[] = $ord[0]['customer_order_number'];
              } else {
                 $detArr[] = substr($ord[0]['source_document_number'],2,6);
              }   
              if ($type == 'i') { 
                 $detArr[] = 'NO'; 
                 $detArr[] = 'NON ASSET';
              }   
              $detArr[] = $ord[0]['branch_code'];
              $detArr[] = $ord[0]['invoice_date'];     
              $dataArr[] = join(',',$detArr);
        } 
      } //eo documents
      $data = join("\r\n",$dataArr);

      //create file only if there are successful items.
      $filePath = false;
      if(count($successSEUIdArr)>0){

        //determine seq.
        $seqFilename = $this->setFilenameFSEQ((($type=='i')?$this->filename:$this->crnFilename), $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
        if($seqFilename==false){
          BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
          return $this->errorTO;
        }

        //write physical file
        $filePath = $this->createFile($folder, str_replace('[@DTE]',date('d-m-y'),$seqFilename), $data);  //places file in correct folder
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
      $postingDistributionTO->subject = (($type=='i')?$this->getTemplateRandInvoiceSubject():$this->getTemplateRandCreditSubject()); //might have no filename if all errors therefore don't display on subject line...
      $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
      if($filePath!=false){
		//$postingDistributionTO->attachmentFile = $filePath;
		$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, str_replace('/bkup/','/',$filePath));
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
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "323", "", FLAG_ERRORTO_NOT_EXTRACTED);
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
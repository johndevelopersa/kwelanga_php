<?php

/* * ********************************************************************************************
 * *
 * *  This job can run as many times per day as is necessary according to job scheduler.
 * *
 * *  It executes notifications that occur throughout the day by triggers
 * *
 Revalation Account ,Reference Number ,Customer PO Number ,Inclusive Total,Invoice Date
7700987,DI50034,1103293868,1122.4,20220630
7731398,DI50038,1101375917,1099.4,20220630
7639948,DI50039,1101530554,24028.56,20220630
7731411,DI50047,1102406733,1781.35,20220630
8236097,DI50059,1103364450,40246.59,20220630

 
 
 
 
 * ******************************************************************************************** */

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "functional/extracts/daily/extractController.php");


//static method handler.
class HermesConesInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class HermesConesInvoicedInit extends extractController {

     private $principalUid = 418; //uid of principal extract.
  
     private $filename    = 'INV418[@FSEQ].csv';  //main controller will build full filename with seq. for us.
     private $crnFilename = 'CRN409[@FSEQ].csv';  //credit note filename
     
     

     public function generateOutput() {

          global $ROOT, $PHPFOLDER;

          //name in email and folder to place bkup files.
          $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
          if (count($pArr)==0) {
               BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
               return $this->errorTO;
          }
          $principalName = $pArr[0]['principal_name'];
          $folder = $this->principalUid . '_' . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.

          $recipParm = $this->extractDAO->getJobExecutionParms($this->principalUid);

          //use the receipients listed in the notification table instead of hard coding them!!!
          //expecting only one row loaded per principal extract
          $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_CUSTOM);
          if (count($reArr)==0) {
                BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
                return $this->errorTO;
          }
          $recipientUId = $reArr[0]['uid'];

          // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    
          $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, 
                                                         $recipientUId, 
                                                         $inclCancelled = false, 
                                                         $documentTypeArr = false, 
                                                         $documentStatusArr = false, 
                                                         $fromInvDate='2022-09-01',
                                                         $toInvDate=false,
                                                         $chainUIdIn=false,
                                                         $dataSource=false,
                                                         $capturedBy=false,
                                                         $depotUId=false,
                                                         $altChainUIdIn=false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
    
          if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
               BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
          } else {
               $this->dbConn->dbinsQuery("commit;");
          }
    
          /* credits and debit notes
          $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, 
                                                               $recipientUId, 
                                                               $documentTypeArr=false,  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
                                                               $documentStatusArr,
                                                               $fromInvDate='2022-09-01',
                                                               $toInvDate=false,
                                                               $dataSource=false,
                                                               $capturedBy=false,
                                                               $depotUId=false,
                                                               $altChainUIdIn=false,
                                                               $chainUIdIn=$chainArray);
                                                               
          if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
            BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
          } else {
            $this->dbConn->dbinsQuery("commit;");
          }
          */    
          $seDocs = $this->extractDAO->getDailyExtractInvoicedHeaders($this->principalUid, $recipientUId, '1');
    
          /*  SUCCESS POINT - 1  */
          //nothing to do...
          if(count($seDocs)==0){
               echo "Successfully Completed Extract : ".get_class($this)." - No entries!<br>";
               $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
               $this->errorTO->description = "Successful";
               return $this->errorTO;
          }
          $dataArr = array();
          $errorSEUIdArr = array();
          $successSEUIdArr = array();
          $successCount = 0;
        
          $headerArr = array();
          $headerArr[] = 'Revalation Account';
          $headerArr[] = 'Reference Number'; 
          $headerArr[] = 'Customer PO Number';
          $headerArr[] = 'Inclusive Total';
          $headerArr[] = 'Invoice Date';
          $headerArr[] = 'VAT Code';
          $dataArr[] = join(',',$headerArr);    
          
          foreach($seDocs as $orders) {
          	
               if(trim($orders['buyer_account_reference']) == '1000011232')  {
                             	
                    $sfvals_SA = $this->miscDAO->getSpecialFieldValues(579, $orders["principal_store_uid"]);
                    if(!empty($sfvals_SA)) {  //has no special field and/or blank...
                         $revAccount = trim($sfvals_SA[0]['value']);
                     } else {
                          $errorSEUIdArr[] = $orders['se_uid']; //list of smart event errors
                           $revAccount = '';      
                     }       
               } else {
                             	
                    $sfvals_PA = $this->miscDAO->getSpecialFieldValues(580, $orders["principal_store_uid"]);

                    if(!empty($sfvals_PA)) {  //has no special field and/or blank...
                           $revAccount = trim($sfvals_PA[0]['value']);
                   } else {
                   $errorSEUIdArr[] = $orders['se_uid']; //list of smart event errors
                           $revAccount = '';      
                   }         	
               }
                             
               if(trim($revAccount) <> '') {
                     $successCount++;
                     $successSEUIdArr[] = $orders['se_uid']; //list of smart event success
                     $storeAcc = trim($revAccount);
                     
                     // OUTPUT LINE 
                     //array containing list of row values
                     $rowArr = array();
                     $rowArr[] = '"' . $storeAcc . '"';
                     $rowArr[] = '"' . substr($orders["document_number"],3,5) . '"';
                     $rowArr[] = '"' . trim(str_replace(array('"',"'"),array('',''),$orders["customer_order_number"])) .'"';
                     $rowArr[] = '"' . $orders['Total'] . '"';
                     $rowArr[] = '"' . date("Y/m/d", strtotime($orders["invoice_date"])).'"';  //DATE (YYY/MM/DD)
                     $rowArr[] = '"01"';
                     
                     $dataArr[] = join(',',$rowArr);
                     
                } 
          }

          $data = join("\r\n",$dataArr);
          
          //create file only if there are successful items.
          $filePath = false;
          if(count($successSEUIdArr)>0){
    
              //determine seq.
              $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid, false, 5, self::setFilenameFSEQ_LenType_PAD);
                 if($seqFilename==false) {
                      BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
                      return $this->errorTO;
                 }    	       	
            }
    
            //write physical file
            $filePath = $this->createFile($folder, $seqFilename, $data);  //places file in correct folder
            if($filePath == false){
              BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
              $this->errorTO->type = FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
              return $this->errorTO;
            }
          
          // SETUP DISTRIBUTION
          $postingDistributionTO = new PostingDistributionTO;
          $postingDistributionTO->DMLType = "INSERT";
          $postingDistributionTO->deliveryType = BT_EMAIL;
          $postingDistributionTO->subject = ($this->getTemplateInvoiceSubject()); //might have no filename if all errors therefore don't display on subject line...
          $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
          if($filePath!=false){
               //$postingDistributionTO->attachmentFile = $filePath;
               $postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, str_replace('/bkup/','/',$filePath));
          }
    
          $recipientList = explode(",", $reArr[0]["user_uid_list"]); //if blank, sizeof() will still be 1
          $recipientsCheckCount = 0;  //check if atleast one distribution item was posted, incase of invalid recipients
    
          foreach($recipientList as $re) {
    
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
                $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "551", "", FLAG_ERRORTO_ERROR);
                if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                   $this->errorTO->type = FLAG_ERRORTO_ERROR;
                   $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
                   BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
                   return $this->errorTO;
                } 
          }
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
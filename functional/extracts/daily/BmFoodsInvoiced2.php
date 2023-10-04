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
class BmFoodsInvoiced2 {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class BmFoodsInvoiced2Init extends extractController {


  private $principalUid = 290; //uid of principal extract.
  private $invFilename = 'SINV290[@FSEQ].TXT';  //invoice filename
  private $crnFilename = 'SCRN290[@FSEQ].TXT';  //credit & debit note filename


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
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_ALTCUSTOM3);
    if (count($reArr)==0){
      BroadcastingUtils::sendAlertEmail("System Error", "Export failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];


    //invoices and cancelled items
    if (!$this->skipInsert) {
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, 
                                                     $recipientUId, 
                                                     $inclCancelled = true,  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
                                                     $p_dtArr = false,
                                                     $p_wDSArr = false,
                                                     $fromInvDate=false,
                                                     $toInvDate=false,
                                                     $chainUIdIn=false,
                                                     $dataSource=false,
                                                     $capturedBy='BMSAGE',
                                                     $depotUId = false );
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }

      //credits and debit notes
      // $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, $recipientUId);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      //  if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
      //  BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
      // } else {
      //  $this->dbConn->dbinsQuery("commit;");
      //}
    }

    //will return all queued items... invoices, credits, debits, cancelled
    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($this->principalUid, $recipientUId);


    /*  SUCCESS POINT - 1  */
    //nothing to do...
    if(count($seDocs)==0){
      echo "Successfully Completed Extract : ".get_class($this)." - No entries!<br>";
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
      return $this->errorTO;
    }

    $grpDocs = array();
    foreach($seDocs as $k=>$r){
      $type = 'i';
      if ($r['document_type_uid'] == DT_CREDITNOTE){
        $type = 'c';
      }
      $grpDocs[$type][$r['dm_uid']][] = $r;
      $psms[$r["principal_store_uid"]] = $r["principal_store_uid"];
    }

    //no special fields -> old account is the special store account!


    /*-----------------------------------------------------------------------*/
    /*    OUTPUT
    /*-----------------------------------------------------------------------*/

    foreach($grpDocs as $type => $docArr){
    	
       $errorSEUIdArr = array(); //update errors at the end.
       $successSEUIdArr = array();

       if($type == 'd'){
         foreach($docArr as $doc){
            $successSEUIdArr[] = $doc[0]['se_uid'];
         }
         continue;
       }

       $data = '';
       foreach($docArr as $doc) {
           $successSEUIdArr[] = $doc[0]['se_uid'];
          
       //    print_r($doc);

           if($type == 'c'){
               $docNo = str_pad(abs($doc[0]['source_document_number']), 7, '0', STR_PAD_LEFT);
               $filename = $this->crnFilename;
               $fSeq = 4;
           } else {
               $docNo = $doc[0]['client_document_number'];
               $filename = $this->invFilename;
               $fSeq = false;
           }

           $data .= "HDR01"  . "," .
                     $docNo  . "," .
                     $doc[0]["invoice_date"] . "\r\n";

           $pageNo = 1;
           $lineNo = 1;
           foreach($doc as $i => $d){ //detail rows.
           	
           	    $dashpos = stripos(trim($d['product_code']), "-");

                $data   .= 'DTL01' . "," .
                $docNo  . "," . 
                trim($d['client_line_no']) . "," .                
                substr(trim($d['product_code']),0,$dashpos)   . "," .
                trim(substr(trim($d['product_code']),$dashpos+1,5))   . "," .
                
                $doc[$i]['document_qty']   . "\r\n";

                $lineNo++;
                if($lineNo>10) {
                    $lineNo = 1;
                    $pageNo++;
                }
           }
       }  //eo-documents

      /*-------------------------------------------------*/
      /*      CREATE FILE AND SEND!
      /*-------------------------------------------------*/

      //create file only if there are successful items.
       $filePath = false;
       if(count($successSEUIdArr)>0){

           //determine seq.
           $seqFilename = $this->setFilenameFSEQ($filename, $this->principalUid, $fSeq, 6);
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
           //create actual file to local FTP folder.
           /*   Starred out because no FTP file required
           echo DIR_DATA_NON_FTP_FROM . '/ftp/bmfoods/invoices/';
           $fp = file_put_contents(DIR_DATA_NON_FTP_FROM . '/ftp/bmfoods/invoices/' . $seqFilename, $data);
           if($fp != strlen($data)){
               BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
               $this->errorTO->type = FLAG_ERRORTO_ERROR;
               $this->errorTO->description = "Failed in ".get_class($this)." on actual file creation";
               return $this->errorTO;
           }
           */

           $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), basename($filePath), "");
           if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
                BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
                return $this->errorTO;
           }
       }

       // SETUP DISTRIBUTION
       $postingDistributionTO = new PostingDistributionTO;
       $postingDistributionTO->DMLType = "INSERT";
       $postingDistributionTO->deliveryType = BT_EMAIL;
       if($type == 'c'){
            $postingDistributionTO->subject = $this->getTemplateCreditSubject(); //might have no filename if all errors therefore don't display on subject line...
       } else {
            $postingDistributionTO->subject = $this->getTemplateInvoiceSubject(); //might have no filename if all errors therefore don't display on subject line...
       }
            $postingDistributionTO->body = $this->getTemplateBodyError($principalName, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));

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
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "436", "", FLAG_ERRORTO_ERROR);
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
      }
        /*-------------------------------------------------*/

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
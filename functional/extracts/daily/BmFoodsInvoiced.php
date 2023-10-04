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
class BmFoodsInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class BmFoodsInvoicedInit extends extractController {


  private $principalUid = 290; //uid of principal extract.
  private $invFilename = 'INV290[@FSEQ].TXT';  //invoice filename
  private $crnFilename = 'CRN290[@FSEQ].TXT';  //credit & debit note filename


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
                                                     $capturedBy='TUNA',
                                                     $depotUId = false );
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
      //place into groups
      $index = $r['depot_uid']; //group invoices by depot.
      if($r['document_type_uid'] == DT_CREDITNOTE){
        $index = 'c'; //credits
      } else if($r['document_type_uid'] == DT_DEBITNOTE){
        $index = 'd'; //credits
      }
      $grpDocs[$index][$r['dm_uid']][] = $r;  //group by index.
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
      foreach($docArr as $doc){
          switch ($doc[0]['depot_uid']){
              case '188': 
                $iprefix = 'L0';
                break;
              case '7': 
                $iprefix = 'T0';
                break;
              case '8':
                $iprefix = 'M0';
                break;
              case '195':
                $iprefix = 'D0';
                break;
              case '119':
                $iprefix = 'K0';
                break;
              case '103':
                $iprefix = 'S0';
                break;
              case '190':
                $iprefix = 'D0';
                break;
              default:
                $iprefix = 'U0';
                break;
            }
        $successSEUIdArr[] = $doc[0]['se_uid'];

        if($type == 'c'){
          $docNo = str_pad(abs($doc[0]['source_document_number']), 7, '0', STR_PAD_LEFT);
          $filename = $this->crnFilename;
          $fSeq = 4;
        } else {
          $docNo = str_pad(abs($doc[0]['document_number']), 6, '0', STR_PAD_LEFT);
          $filename = $this->invFilename;
          $fSeq = false;
        }

        $data .= "OHDR121" .
                 $docNo .
                 "00000" .
                 str_pad($doc[0]['old_account'], 8, ' ', STR_PAD_RIGHT) .
                 str_pad('', 14, ' ');

        if($type == 'c'){
          $data .= str_pad('', 8, ' ');
        }

        if($type == 'c'){
          $data .= date('Ymd', strtotime($doc[0]['order_date'])) .
                 date('Ymd', strtotime($doc[0]['order_date']));
        } else {
          $data .= date('Ymd', strtotime($doc[0]['order_date'])) .
                 date('Ymd', strtotime($doc[0]['invoice_date']));
        }

        if($type == 'c'){
          $data .= $iprefix . str_pad(substr(abs($doc[0]['invoice_number']),-6), 6, '0', STR_PAD_LEFT) .
                   str_pad(substr($doc[0]['grv_number'],0,13), 13, ' ', STR_PAD_RIGHT) .
                   (($doc[0]['reason_uid']>0 && $doc[0]['reason_uid']<100)?str_pad($doc[0]['reason_uid'], 2, ' ', STR_PAD_LEFT):'  ') .
                   str_pad(abs($doc[0]['alternate_document_number']), 6, '0', STR_PAD_LEFT) .
                   ((empty($doc[0]['claim_number'])) ? (str_pad(abs($doc[0]['invoice_number']), 6, '0', STR_PAD_LEFT).str_pad('', 7, ' ')) : (str_pad(abs($doc[0]['claim_number']), 13, ' ', STR_PAD_LEFT)));

        } else {
          $data .= str_pad('', 16, ' ');
          if(!empty($doc[0]['invoice_number'])){
            $data .= $iprefix . str_pad(substr(abs($doc[0]['invoice_number']),-6), 6, '0', STR_PAD_LEFT);
          } else {
            $data .= str_pad('', 6, ' ');
          }
          $data .= (($doc[0]['reason_uid']>0 && $doc[0]['reason_uid']<100) ? str_pad($doc[0]['reason_uid'], 2, ' ', STR_PAD_LEFT) : str_pad('', 11, ' '));
        }

        $data .= str_pad('', 300, ' ') . "\r\n";


        $pageNo = 1;
        $lineNo = 1;
        foreach($doc as $i => $d){ //detail rows.

          $data .= 'ODTL121' .
                   $docNo .
                   str_pad(substr($d['client_line_no'],0,1), 2, ' ', STR_PAD_LEFT) .
                   str_pad(trim(substr($d['client_line_no'],1,2)), 2, ' ', STR_PAD_LEFT) .
                   str_pad(trim($d['product_code']), 11, ' ', STR_PAD_RIGHT) .
                   str_pad('', 7, ' ') .
                   str_pad(abs($doc[$i]['document_qty']), 8, ' ', STR_PAD_LEFT) . //cancelled show up as 0.
                   str_pad('', 8, ' ') .
                   str_pad(number_format(round(abs($d['extended_price']), 2), 2, '.', ''), 9, ' ', STR_PAD_LEFT) .
                   str_pad(number_format(round(abs($d['vat_amount']), 2), 2, '.', ''), 9, ' ', STR_PAD_LEFT) .
                   str_pad(number_format(round(abs($d['total']), 2), 2, '.', ''), 9, ' ', STR_PAD_LEFT) .
                   str_pad('', 300, ' ') . "\r\n";

          $lineNo++;
          if($lineNo>10){
            $lineNo = 1;
            $pageNo++;
          }
        }

      } //eo-documents


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
        echo DIR_DATA_NON_FTP_FROM . '/ftp/bmfoods/invoices/';
        $fp = file_put_contents(DIR_DATA_NON_FTP_FROM . '/ftp/bmfoods/invoices/' . $seqFilename, $data);
        if($fp != strlen($data)){
          BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on actual file creation";
          return $this->errorTO;
        }


        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), basename($filePath), "");
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }

      }


      if (sizeof($errorSEUIdArr) > 0) {

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


        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "", "", FLAG_ERRORTO_ERROR);
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
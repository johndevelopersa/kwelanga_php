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
class BiscottiInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}



class BiscottiInvoicedInit extends extractController {


  private $principalUid = 50; //uid of principal extract.
  private $filename = 'I[@REGION]05[@FSEQ].doc';  //main controller will build full filename with seq. for us.


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
      $grpDocs[$r['depot_uid']][$r['dm_uid']][] = $r;
    }


    //depot translate (clipper program)
    $depotTrans = array(
        2 => "J", //UJ
        5 => "C", //UC
        3 => "D", //UD
        6 => "P", //FP
        7 => "E", //FP
     );

    $errorSEUIdArr = array(); //no error on invoice summary
    $successSEUIdArr = array();


    foreach($grpDocs as $depotId => $ordersArr){

      $dataArr = array();
      $regionCode = (isset($depotTrans[$depotId])) ? $depotTrans[$depotId] : '';
      $filename = str_replace('[@REGION]', $regionCode, $this->filename);

      foreach($ordersArr as $ord){


        $successSEUIdArr[] = $ord[0]['se_uid'];

        /*-------------------------------------------------*/
        /*            START BUILDING OUTPUT
        /*-------------------------------------------------*/
        $orderData = "\r\n";
        $orderData .= "Retail Trading Technologies (Pty) Ltd                     Invoice Summary\r\n";
        $orderData .= $principalName . "  -  " . $ord[0]['depot_name'] . "\r\n";
        $orderData .= str_pad('',73,'-') . "\r\n";
        $orderData .= "\r\n";

        //cancelled indicator
        $reasonString = '';
        if($ord[0]['document_status_uid'] == DST_CANCELLED){
          $orderData .= str_pad("- CANCELLED - ", 73, ' ', STR_PAD_BOTH) . "\r\n";
          $orderData .= "\r\n";
          $reasonString = str_pad(strtoupper(substr($ord[0]['reason_description'],0,40)), 38, ' ', STR_PAD_LEFT);
        }

        $orderData .= str_pad("Invoice No.:    " . abs($ord[0]['document_number']) . " / " . abs($ord[0]['invoice_number']), 35, ' ', STR_PAD_RIGHT) . $reasonString . "\r\n";
        $orderData .= str_pad("Customer No.:   " . trim($ord[0]['old_account']), 35, ' ', STR_PAD_RIGHT) . "Order Date:" . str_pad(date("d/m/Y", strtotime($ord[0]["order_date"])), 27, ' ', STR_PAD_LEFT) . "\r\n";
        $orderData .= str_pad("Account No.:    ", 35, ' ', STR_PAD_RIGHT) . "Customer Order No.:" . str_pad(trim(substr($ord[0]['customer_order_number'],0,15)), 19, ' ', STR_PAD_LEFT) . "\r\n";
        $orderData .= str_pad("Customer Name:  " . trim($ord[0]["deliver_name"]), 35, ' ', STR_PAD_RIGHT) . "\r\n";
        $orderData .= str_pad("                " . trim($ord[0]["deliver_add1"]), 35, ' ', STR_PAD_RIGHT) . "\r\n";
        $orderData .= str_pad("                " . trim($ord[0]["deliver_add2"]), 35, ' ', STR_PAD_RIGHT) . "\r\n";
        $orderData .= str_pad("                " . trim($ord[0]["deliver_add3"]), 35, ' ', STR_PAD_RIGHT) . "\r\n";
        $orderData .= "\r\n";
        $orderData .= "Code        Description             Ord  Inv   Taxable      Tax     Total\r\n";
        $orderData .= str_pad('',73,'-') . "\r\n";

        $totalOrdQty = 0;
        $totalInvQty = 0;
        foreach($ord as $d){ //detail rows.

          $totalOrdQty += abs($d['ordered_qty']);
          $totalInvQty += abs($d['document_qty']);

          $orderData .= str_pad(substr(trim(str_replace(array('"'),array(''),$d['product_code'])), 0, 10), 12, ' ', STR_PAD_RIGHT) .
                        str_pad(substr(trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description'])), 0, 22), 24, ' ', STR_PAD_RIGHT) .
                        str_pad(abs($d['ordered_qty']), 3, ' ', STR_PAD_LEFT) .
                        str_pad(abs($d['document_qty']), 5, ' ', STR_PAD_LEFT) .
                        str_pad(number_format(round($d['extended_price'], 2), 2, '.', ''), 10, ' ', STR_PAD_LEFT) .
                        str_pad(number_format(round($d['vat_amount'], 2), 2, '.', ''), 9, ' ', STR_PAD_LEFT) .
                        str_pad(number_format(round($d['total'], 2), 2, '.', ''), 10, ' ', STR_PAD_LEFT) .
                        "\r\n";

        } //eo detail

        $orderData .= str_pad('',73,'-') . "\r\n";
        $orderData .= "Totals                              " .
                str_pad($totalOrdQty, 3, ' ', STR_PAD_LEFT) .
                str_pad($totalInvQty, 5, ' ', STR_PAD_LEFT) .
                str_pad(number_format(round($ord[0]['exclusive_total'], 2), 2, '.', ''), 10, ' ', STR_PAD_LEFT) .
                str_pad(number_format(round(($ord[0]['invoice_total']-$ord[0]['exclusive_total']), 2), 2, '.', ''), 9, ' ', STR_PAD_LEFT) .
                str_pad(number_format(round($ord[0]['invoice_total'], 2), 2, '.', ''), 10, ' ', STR_PAD_LEFT) .
                "\r\n";

        //add to file group
        $dataArr[] = $orderData;

      } //eo documents in group


      $data = join("\f\r\n",$dataArr);


      //determine seq.
      $seqFilename = $this->setFilenameFSEQ($filename, $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
      if($seqFilename==false){
        BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
        return $this->errorTO;
      }

      //write physical file
      $filePath = $this->createFile($folder, $seqFilename, str_replace('?', '', mb_convert_encoding ($data, 'ASCII')));  //places file in correct folder
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
      $postingDistributionTO->subject = $this->getTemplateInvoiceSubject(); //might have no filename if all errors therefore don't display on subject line...
      $postingDistributionTO->body = $this->getTemplateBody($principalName, count($successSEUIdArr), count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
      $postingDistributionTO->attachmentFile = $filePath;


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
//      if (sizeof($errorSEUIdArr) > 0) {
//       $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "201,202", "", FLAG_ERRORTO_ERROR);
//       if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
//         $this->errorTO->type = FLAG_ERRORTO_ERROR;
//         $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
//         BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
//         return $this->errorTO;
//       }
//      }

    } //eof depot type


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
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
class PaarmanInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class PaarmanInvoicedInit extends extractController {


  private $principalUid = 11; //uid of principal extract.
  private $filename = 'I270[@FSEQ].TXT';  //main controller will build full filename with seq. for us.
                                          // NOTE: we prefix with 0 instead of padding the seq to 5 because this seq needs to be 4 chars as it forms part of the detail lines too


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
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
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
    $psms=array();
    foreach($seDocs as $k=>$r){
      $grpDocs[$r['depot_uid']][$r['dm_uid']][] = $r; //sends file per depot.
      $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 19, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }


    //depot translate
    $depotTrans = array(
      2 =>   array('doc'=>"G0", 'region'=>"7000"), //UJ
      3 =>   array('doc'=>"M0", 'region'=>"5000"), //UD
      5 =>   array('doc'=>"E0", 'region'=>"8500"), //UC
      6 =>   array('doc'=>"00", 'region'=>"8000"), //FP
      7 =>   array('doc'=>"00", 'region'=>"8100")  //TE
    );



    //BUILD FILE FOR EACH DEPOT, treat each depot as an extract.
    foreach($grpDocs as $depotId => $hr){

      //has 1 ok order... -> file will be generated, police to stop ghost file seqs as we need to seq before building file contents.
      $okOrders = false;
      foreach($hr as $ord){ //document loop
        if(!empty($sfvals[$ord[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...
          $okOrders = true;
          break;
        }
      }

      if($okOrders){
        //determine seq.
        $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid, false, 4, self::setFilenameFSEQ_LenType_PAD);
        if($seqFilename==false){
          BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
          return $this->errorTO;
        }
      }


      $errorSEUIdArr = array();
      $successSEUIdArr = array();
      $regionArr = (isset($depotTrans[$depotId])) ? $depotTrans[$depotId] : array('doc'=>"00",  'region'=>"0000");
      $totFileAmt = 0;
      $totFileTax = 0;
      $totFileDue = 0;


      /*-------------------------------------------------*/
      /* START BUILDING OUTPUT
       */
      $this->fileSequence = str_pad($this->fileSequence,4,"0",STR_PAD_LEFT);  //ie: 0[410] - seq is 4 long.
      $data = $this->fileSequence . "00        0000".str_pad('', 482, ' ')."\r\n"; //file header.


      foreach($hr as $ord){ //document loop

        if(empty($sfvals[$ord[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...

          $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors

        } else {

          $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

          if($ord[0]['data_source']!=DS_CAPTURE){
            $docNum = $regionArr['doc'] . str_pad(substr(abs($ord[0]['document_number']),-6), 6, '0', STR_PAD_LEFT);
          } else {
            $docNum = '00' . str_pad(substr(abs($ord[0]['document_number']),-6), 6, '0', STR_PAD_LEFT);
          }

          $data .= $this->fileSequence; //file seq
          $data .= '01'; //header record
          $data .= $docNum;
          $data .= '0000';
          $data .= $docNum;
          $data .= str_pad(trim($sfvals[$ord[0]["principal_store_uid"]]["value"]), 8, ' ', STR_PAD_RIGHT);
          $data .= str_pad('', 8, ' ');  //space(8)
          $data .= date("dmY", strtotime($ord[0]["invoice_date"]));  //DATE (DD/MM/YYYY)
          $data .= str_pad(substr($ord[0]['customer_order_number'],0,13), 13, ' ', STR_PAD_RIGHT);
          $data .= str_pad('', 39, ' ');  //space(39)
          $data .= $regionArr['region'];
          $data .= str_pad('', 4, ' ');  //space(4)
          $data .= date("dmY", strtotime($ord[0]["invoice_date"]));  //DATE (DD/MM/YYYY)
          $data .= 'Y1';
          $data .= str_pad('', 2, ' ');  //space(4)


          //HEADER TOTALS
          $totHeadAmt = 0;
          $totHeadTax = 0;
          $totHeadDue = 0;
          $lineNo = 1;

          foreach($ord as $d){ //detail rows.
            if(abs($d['document_qty'])>0){
              $totHeadAmt += round($d['extended_price'], 2);
              $totHeadTax += round($d['vat_amount'], 2);
              $totHeadDue += round($d['total'], 2);
              $lineNo++;
            }
          }

          $data .= str_pad($lineNo, 4, '0', STR_PAD_LEFT);  //count of rows + 1 for header.
          $data .= '+';
          $data .= str_pad(number_format($totHeadAmt, 5, '.', ''), 11, 0, STR_PAD_LEFT);  //5,5 decimal.
          $data .= '+';
          $data .= str_pad(number_format($totHeadTax, 5, '.', ''), 11, 0, STR_PAD_LEFT);  //5,5 decimal.
          $data .= '+';
          $data .= str_pad(number_format($totHeadDue, 5, '.', ''), 11, 0, STR_PAD_LEFT);  //5,5 decimal.
          $data .= str_pad('', 338, ' ');
          $data .= "\r\n";
          //eof header...


          //File totals
          $totFileAmt += $totHeadAmt;
          $totFileTax += $totHeadTax;
          $totFileDue += $totHeadDue;


          //detail rows
          $lineNo = 1;
          foreach($ord as $d){ //detail rows.

            if(abs($d['document_qty'])>0){  //invoice qty greater then zero.

              $data .= $this->fileSequence; //file seq
              $data .= '02'; //header record
              $data .= $docNum;
              $data .= str_pad($lineNo, 4, '0', STR_PAD_LEFT);
              $data .= str_pad(str_replace(array("\t","\r"),array("",""), trim($d['product_code'])), 18, ' ', STR_PAD_RIGHT);
              $data .= $regionArr['region'];
              $data .= str_pad('', 4, ' ');  //space(4)
              $data .= '+';
              $data .= str_pad(abs($d['document_qty']), 9, '0', STR_PAD_LEFT);
              $data .= 'CS';
              $data .= '+';
              $data .= str_pad(number_format(round($d['selling_price'], 2), 5, '.', ''), 11, 0, STR_PAD_LEFT);  //5,5 decimal.
              $data .= '+';

                $discount = ($d['selling_price']>0) ? round($d['discount_value'] / $d['selling_price'] * 100, 2) : 0; //DISCOUNT %  //stored value : discount_value backwards calculation.
              $data .= str_pad(number_format(substr($discount ,1), 4, '.', ''), 7, 0, STR_PAD_LEFT);  //5,5 decimal.
              $data .= '+';
              $data .= str_pad(number_format(round($d['net_price'], 2), 5, '.', ''), 11, 0, STR_PAD_LEFT);  //5,5 decimal.
              $data .= '+';
              $data .= str_pad(number_format(round($d['extended_price'], 2), 5, '.', ''), 11, 0, STR_PAD_LEFT);  //5,5 decimal.
              $data .= 'Y';
              $data .= str_pad('', 3, ' ');  //space(3)
              $data .= 'N';
              $data .= str_pad('', 395, ' ');
              $data .= "\r\n";


              $lineNo++;

            }
          } //eo detail
        } //eo special field check
      } //eo document



      //create file only if there are successful items.
      $filePath = false;
      if(count($successSEUIdArr)>0){

        //file footer.
        $data .= $this->fileSequence; //file seq
        $data .= '99'; //header record
        $data .= str_pad('', 8, ' ');  //space(8)
        $data .= str_pad(count($successSEUIdArr), 10, '0', STR_PAD_LEFT);
        $data .= '+';
        $data .= str_pad(number_format($totFileAmt, 2, '.', ''), 11, 0, STR_PAD_LEFT);
        $data .= '+';
        $data .= str_pad(number_format($totFileTax, 2, '.', ''), 11, 0, STR_PAD_LEFT);
        $data .= '+';
        $data .= str_pad(number_format($totFileDue, 2, '.', ''), 11, 0, STR_PAD_LEFT);
        $data .= str_pad('', 440, ' ');
        $data .= "\r\n";
        $data .= chr(26);

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
      $postingDistributionTO->subject = $this->getTemplateInvoiceSubject('Region:'.$regionArr['region']); //might have no filename if all errors therefore don't display on subject line...
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
      /*-------------------------------------------------*/


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
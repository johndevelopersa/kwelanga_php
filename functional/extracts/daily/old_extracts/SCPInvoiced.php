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
class SCPInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}



class SCPInvoicedInit extends extractController {


  private $principalUid = 21; //uid of principal extract.
  private $filename = 'SOR35[@FSEQ].csv';  //main controller will build full filename with seq. for us.


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
      $grpDocs[$r['dm_uid']][] = $r;
      $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 26, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }


    //invoice location prefix translate
    $invoiceTrans = array(
      2 => 'FSJHB', //UJ
      3 => 'FSDBN', //UD
      5 => 'FSCPT', //UC
    );
    //depot translate
    $depotTrans = array(
      2 => 'ULLJHB', //UJ
      3 => 'ULLDBN', //UD
      5 => 'ULLCPT', //UC
    );

    $dataArr = $errorSEUIdArr = $successSEUIdArr = array();
    foreach($grpDocs as $ord){

      /*-------------------------------------------------*/
      /*            START BUILDING OUTPUT
      /*-------------------------------------------------*/
      if(empty($sfvals[$ord[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...

        $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors

      } else {

        $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success
        $storeAcc = trim($sfvals[$ord[0]["principal_store_uid"]]['value']);
        $invPrepend = (isset($invoiceTrans[$ord[0]['depot_uid']])) ? $invoiceTrans[$ord[0]['depot_uid']] : '';
        $invoiceNumber = $invPrepend . str_pad(substr($ord[0]['invoice_number'],-6), 6, 0, STR_PAD_LEFT);
        $location = (isset($depotTrans[$ord[0]['depot_uid']])) ? $depotTrans[$ord[0]['depot_uid']] : ' ';


        //OA :=    CHR(34)  + "1" + CHR(34)        + "," + ;
        //	 ORDUNIQ                         + "," + ;
        //         CHR(34)  + ORDNUMBER  + CHR(34) + "," + ;
        //         CHR(34)  + CUSTOMER   + CHR(34) + "," + ;
        //         CHR(34)  + CUSTGROUP  + CHR(34) + "," + ;
        //         CHR(34)  + BILNAME    + CHR(34) + "," + ;
        //         CHR(34)  + BILADDR1   + CHR(34) + "," + ;
        //         CHR(34)  + BILADDR2   + CHR(34) + "," + ;
        //         CHR(34)  + BILADDR3   + CHR(34) + "," + ;
        //         CHR(34)  + BILADDR4   + CHR(34) + "," + ;
        //         CHR(34)  + BILCITY    + CHR(34) + "," + ;
        //         CHR(34)  + BILZIP     + CHR(34) + "," + ;
        //         CHR(34)  + SHPNAME    + CHR(34) + "," + ;
        //         CHR(34)  + SHPADDR1   + CHR(34) + "," + ;
        //         CHR(34)  + SHPADDR2   + CHR(34) + "," + ;
        //         CHR(34)  + SHPADDR3   + CHR(34) + "," + ;
        //         CHR(34)  + SHPCITY    + CHR(34) + "," + ;
        //         CHR(34)  + SHPZIP     + CHR(34) + "," + ;
        //         CHR(34)  + SHPCONTACT + CHR(34) + "," + ;
        //         CHR(34)  + PRICELIST  + CHR(34) + "," + ;
        //         CHR(34)  + PONUMBER   + CHR(34) + "," + ;
        //         CHR(34)  + REFERENCE  + CHR(34) + ","
        //
        //
        //OB :=    TYPE                            + "," + ;
        //         ORDDATE                         + "," + ;
        //         EXPDATE                         + "," + ;
        //         CHR(34)  + ORDFISCYR  + CHR(34) + "," + ;
        //         ORDFISCPER                      + "," + ;
        //         CHR(34)  + LOCATION   + CHR(34) + "," + ;
        //         INVPRODUCE                      + "," + ;
        //         CHR(34)  + INVNUMBER  + CHR(34) + "," + ;
        //         CHR(34)  + NUMINVOICE + CHR(34) + "," + ;
        //         CHR(34)  + PRINTSTAT  + CHR(34) + "," + ;
        //         CHR(34)  + LASTPOST   + CHR(34) + "," + ;
        //         CHR(34)  + ORDTOTAL   + CHR(34) + "," + ;
        //         CHR(34)  + ORDLINES   + CHR(34) + "," + ;
        //         TBASE1                          + "," + ;
        //         TEAMOUNT1                       + "," + ;
        //         TIAMOUNT1                       + "," + ;
        //         COMPLETE                        + "," + ;
        //         COMPDATE                        + "," + ;
        //         SHIPDATE                        + "," + ;
        //         INVDATE                         + "," + ;
        //         CHR(34)  + INVFISCYR  + CHR(34) + "," + ;
        //         INVFISCPER                      + ","
        //
        //OC :=    CHR(34) + POSTINV     + CHR(34) + "," + ;
        //         NOSHIPLINE                      + "," + ;
        //         INVNETNOTX                      + "," + ;
        //         INVITAXTOT                      + "," + ;
        //         INVITMTOT                       + "," + ;
        //         INVSUBTOT                       + "," + ;
        //         INVETAXTOT                      + "," + ;
        //         INVNETWTX                       + "," + ;
        //         NVAMTDUE                        + "," + ;
        //         TAMOUNT1                        + "," + ;
        //         CHR(34) + SHINUMBER   + CHR(34) + "," + ;
        //         NUMSHPMENT                      + "," + ;
        //         SHIDATE                         + "," + ;
        //         OECOMMAND                       + "," + ;
        //         PROCESSCMD                      + "," + ;
        //         CHR(34)  + GOCHKCRDT  + CHR(34) + "," + ;
        //         CHR(34)  + SWCHKLIMC  + CHR(34) + "," + ;
        //         CHR(34)  + SWOVERLIMC + CHR(34) + "," + ;
        //         CHR(34)  + SWCHKLIMA  + CHR(34) + "," + ;
        //         CHR(34)  + SWOVERLIMA + CHR(34) + "," + ;
        //         CHR(34)  + ONHOLD     + CHR(34) + "," + ;
        //         CHR(34)  + AMTOVERA  + CHR(34)
        //
        //? OA + OB + OC


        //array containing list of row values
        $rowArr = array();
        $rowArr[] = '"1"';
        $rowArr[] = abs($ord[0]['document_number']);
        $rowArr[] = '"' . $invoiceNumber . '"';
        $rowArr[] = '"'.$storeAcc.'"';
        $rowArr[] = '""'; //cust2 empty
        $rowArr[] = '"' . str_replace(array(',','"',"'"), array('','',''), trim($ord[0]['bill_name'])) . '"';
        $rowArr[] = '"' . str_replace(array(',','"',"'"), array('','',''), trim($ord[0]['bill_add1'])) . '"';
        $rowArr[] = '"' . str_replace(array(',','"',"'"), array('','',''), trim($ord[0]['bill_add2'])) . '"';
        $rowArr[] = '"' . str_replace(array(',','"',"'"), array('','',''), trim($ord[0]['bill_add3'])) . '"';
        $rowArr[] = '" "';
        $rowArr[] = '" "';
        $rowArr[] = '" "';
        $rowArr[] = '"' . str_replace(array(',','"',"'"), array('','',''), trim($ord[0]['deliver_name'])) . '"';
        $rowArr[] = '"' . str_replace(array(',','"',"'"), array('','',''), trim($ord[0]['deliver_add1'])) . '"';
        $rowArr[] = '"' . str_replace(array(',','"',"'"), array('','',''), trim($ord[0]['deliver_add2'])) . '"';
        $rowArr[] = '"' . str_replace(array(',','"',"'"), array('','',''), trim($ord[0]['deliver_add3'])) . '"';
        $rowArr[] = '" "';
        $rowArr[] = '" "';
        $rowArr[] = '" "';
        $rowArr[] = '"SCP"';
        $rowArr[] = '"' . trim($ord[0]['customer_order_number']) . '"';
        $rowArr[] = '" "';
        $rowArr[] = '1';
        $rowArr[] = date("Ymd", strtotime($ord[0]["order_date"]));
        $rowArr[] = date("Ymd", strtotime($ord[0]["order_date"]));
        $rowArr[] = '"' . (date("Y", strtotime($ord[0]["order_date"])) + 1) . '"';  //copied from clipper ???
        $rowArr[] = '' . (date("m", strtotime($ord[0]["order_date"])) - 2) . '';  //copied from clipper ???
        $rowArr[] = '"'.$location.'"';
        $rowArr[] = 'TRUE';
        $rowArr[] = '"' . $invoiceNumber . '"';
        $rowArr[] = '"0"';
        $rowArr[] = '"1"';
        $rowArr[] = '"' . date("Ymd", strtotime($ord[0]["order_date"])) . '"';
        $rowArr[] = '"' . number_format(round($ord[0]['exclusive_total'], 2), 2, '.', '') . '"';
        $rowArr[] = '"0"';
        $rowArr[] = number_format(round($ord[0]['exclusive_total'], 2), 2, '.', '');
        $rowArr[] = number_format(round(($ord[0]['invoice_total'] - $ord[0]['exclusive_total']), 2), 2, '.', '');
        $rowArr[] = '0';
        $rowArr[] = '3';
        $rowArr[] = date("Ymd", strtotime($ord[0]["invoice_date"]));
        $rowArr[] = date("Ymd", strtotime($ord[0]["invoice_date"]));
        $rowArr[] = date("Ymd", strtotime($ord[0]["invoice_date"]));
        $rowArr[] = '"' . (date("Y", strtotime($ord[0]["invoice_date"])) + 1) . '"';  //copied from clipper ???
        $rowArr[] = '' . (date("m", strtotime($ord[0]["invoice_date"])) - 2) . '';  //copied from clipper ???
        $rowArr[] = '"FALSE"';
        $rowArr[] = '0';
        $rowArr[] = number_format(round($ord[0]['exclusive_total'], 2), 2, '.', '');
        $rowArr[] = number_format(round(($ord[0]['invoice_total'] - $ord[0]['exclusive_total']), 2), 2, '.', '');
        $rowArr[] = number_format(round($ord[0]['exclusive_total'], 2), 2, '.', '');
        $rowArr[] = number_format(round($ord[0]['exclusive_total'], 2), 2, '.', '');
        $rowArr[] = number_format(round($ord[0]['exclusive_total'], 2), 2, '.', '');
        $rowArr[] = number_format(round($ord[0]['invoice_total'], 2), 2, '.', '');
        $rowArr[] = number_format(round($ord[0]['invoice_total'], 2), 2, '.', '');
        $rowArr[] = number_format(round(($ord[0]['invoice_total'] - $ord[0]['exclusive_total']), 2), 2, '.', '');
        $rowArr[] = '"' . $invoiceNumber . '"';
        $rowArr[] = '0';
        $rowArr[] = date("Ymd", strtotime($ord[0]["invoice_date"]));
        $rowArr[] = '0';
        $rowArr[] = '0';
        $rowArr[] = '"FALSE"';
        $rowArr[] = '"FALSE"';
        $rowArr[] = '"FALSE"';
        $rowArr[] = '"FALSE"';
        $rowArr[] = '"FALSE"';
        $rowArr[] = '"FALSE"';
        $rowArr[] = '"FALSE"';

        $dataArr[] = join(',',$rowArr);


        $lineCount = 1;
        foreach($ord as $d){ //detail rows.

          if(abs($d['document_qty'])>0){


            //? "2," + ORDUNIQ     + "," + ;
            //         LINENUM     + "," + ;
            //         LINETYPE    + "," + ;
            //         CHR(34)     + ITEM       + CHR(34) + "," + ;
            //         CHR(34)     + DESC       + CHR(34) + "," + ;
            //         CHR(34)     + ACCTSET    + CHR(34) + "," + ;
            //         CHR(34)     + PRICELIST  + CHR(34) + "," + ;
            //         CHR(34)     + LOCATION   + CHR(34) + "," + ;
            //         EXPDATE                            + "," + ;
            //         QTYORDERED                         + "," + ;
            //         OQTYSHIPPED                        + "," + ;
            //         QTYBACKORD                         + "," + ;
            //         QTYSHPTODT                         + "," + ;
            //         ORIGQTY                            + "," + ;
            //         CHR(34)     + ORDUNIT    + CHR(34) + "," + ;
            //         UNITPRICE                          + "," + ;
            //         CHR(34)     + PRICEUNIT  + CHR(34) + "," + ;
            //         PRIUNTPRC                          + "," + ;
            //         CHR(34)     + BASEUNIT   + CHR(34) + "," + ;
            //         PRIBASPRC                          + "," + ;
            //         EXTOPRICE                          + "," + ;
            //         COMPLETE                           + "," + ;
            //         CHR(34)     + TINCLUDED1 + CHR(34) + "," + ;
            //         TBASE1                             + "," + ;
            //         TAMOUNT1                           + "," + ;
            //         LINENUM                            + "," + ;
            //         QTYCOMMIT                          + "," + ;
            //         DDTLTYPE                           + "," + ;
            //         CHR(34)     + DDTLNO     + CHR(34) + "," + ;
            //         EXTNETPRI                          + "," + ;
            //         DISORDMISC                         + "," + ;
            //         TAXTOTAL

            $detArr = array();
            $detArr[] = '2';
            $detArr[] = abs($ord[0]['document_number']);
            $detArr[] = str_pad($lineCount, 4, '0', STR_PAD_LEFT);
            $detArr[] = '1';
            $detArr[] = '"' . trim(str_replace(array('"',','),array('',''),$d['product_code'])) . '"';
            $detArr[] = '"' . trim(str_replace(array('"',','),array('',''),$d['product_code'])) . ' ' . trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description'])) . '"';
            $detArr[] = '"FG"';
            $detArr[] = '"SCP"';
            $detArr[] = '"'.$location.'"';
            $detArr[] = date("Ymd", strtotime($ord[0]["order_date"]));
            $detArr[] = abs($d['ordered_qty']);
            $detArr[] = abs($d['ordered_qty']);
            $detArr[] = '0';
            $detArr[] = abs($d['document_qty']);
            $detArr[] = abs($d['ordered_qty']);
            $detArr[] = '"CASE"';
            $detArr[] = number_format(round($d['selling_price'], 2), 2, '.', '');
            $detArr[] = '"CASE"';
            $detArr[] = number_format(round($d['selling_price'], 2), 2, '.', '');
            $detArr[] = '"CASE"';
            $detArr[] = number_format(round($d['selling_price'], 2), 2, '.', '');
            $detArr[] = number_format(round($d['extended_price'], 2), 2, '.', '');
            $detArr[] = '2';
            $detArr[] = '"FALSE"';
            $detArr[] = number_format(round($d['extended_price'], 2), 2, '.', '');
            $detArr[] = number_format(round($d['vat_amount'], 2), 2, '.', '');
            $detArr[] = str_pad($lineCount, 4, '0', STR_PAD_LEFT);
            $detArr[] = abs($d['document_qty']);
            $detArr[] = '2';
            $detArr[] = '""';
            $detArr[] = number_format(round($d['extended_price'], 2), 2, '.', '');
            $detArr[] = '0';
            $detArr[] = number_format(round($d['total'], 2), 2, '.', '');

            $dataArr[] = join(',',$detArr);

            $lineCount++;
          }
        } //eo detail
      } //eo special field check
    } //eo documents


    $data = '"RECTYPE","ORDUNIQ","ORDNUMBER","CUSTOMER","CUSTGROUP","BILNAME","BILADDR1","BILADDR2","BILADDR3","BILADDR4","BILCITY","BILZIP","SHPNAME","SHPADDR1","SHPADDR2","SHPADDR3","SHPCITY","SHPZIP","SHPCONTACT","PRICELIST","PONUMBER","REFERENCE","TYPE","ORDDATE","EXPDATE","ORDFISCYR","ORDFISCPER","LOCATION","INVPRODUCE","INVNUMBER","NUMINVOICE","PRINTSTAT","LASTPOST","ORDTOTAL","ORDLINES","TBASE1","TEAMOUNT1","TIAMOUNT1","COMPLETE","COMPDATE","SHIPDATE","INVDATE","INVFISCYR","INVFISCPER","POSTINV","NOSHIPLINE","INVNETNOTX","INVITAXTOT","INVITMTOT","INVSUBTOT","INVETAXTOT","INVNETWTX","NVAMTDUE","TAMOUNT1","SHINUMBER","NUMSHPMENT","SHIDATE","OECOMMAND","PROCESSCMD","GOCHKCRDT","SWCHKLIMC","SWOVERLIMC","SWCHKLIMA","SWOVERLIMA","ONHOLD","AMTOVERA"' . "\r\n" .
             '"RECTYPE","ORDUNIQ","LINENUM","LINETYPE","ITEM","DESC","ACCTSET","PRICELIST","LOCATION","EXPDATE","QTYORDERED","QTYSHIPPED","QTYBACKORD","QTYSHPTODT","ORIGQTY","ORDUNIT","UNITPRICE","PRICEUNIT","PRIUNTPRC","BASEUNIT","PRIBASPRC","EXTOPRICE","COMPLETE","TINCLUDED1","TBASE1","TAMOUNT1","DETAILNUM","QTYCOMMIT","DDTLTYPE","DDTLNO","EXTNETPRI","DISORDMISC","TAXTOTAL"' . "\r\n" .
             '"RECTYPE","ORDUNIQ","LINENUM","SERIALNUMF"' . "\r\n" .
             '"RECTYPE","ORDUNIQ","LINENUM","LOTNUMF"' . "\r\n" .
             '"RECTYPE","ORDUNIQ","PAYMENT"' . "\r\n" .
             '"RECTYPE","ORDUNIQ","UNIQUIFIER"' . "\r\n" .
             '"RECTYPE","ORDUNIQ","OPTFIELD"' . "\r\n" .
             join("\r\n",$dataArr);


    //create file only if there are successful items.
    $filePath = false;
    if(count($successSEUIdArr)>0){

      //determine seq.
      $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
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
    $postingDistributionTO->subject = $this->getTemplateInvoiceSubject(); //might have no filename if all errors therefore don't display on subject line...
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
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "26", "", FLAG_ERRORTO_ERROR);
      if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
        return $this->errorTO;
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
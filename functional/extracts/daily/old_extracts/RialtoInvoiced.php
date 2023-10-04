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
class RialtoInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class RialtoInvoicedInit extends extractController {


  private $principalUid = 153; //uid of principal extract.
  private $filename = 'I153[@FSEQ].xml';  //invoice filename


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
    }

    //will return all queued items... invoices, credits, debits.
    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($this->principalUid, $recipientUId);


    //group array
    $grpDocs = array();
    $psms=array();
    foreach($seDocs as $k=>$r){
        $grpDocs[$r['dm_uid']][] = $r;
        $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }


    if (sizeof($psms)>0) {  // get special field values for all stores in above docs
      $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, "208", implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }


    $successSEUIdArr = array();
    $errorSEUIdArr = array(); //update errors at the end.

    //depot translate (clipper program)
    $depotTrans = array(
        2 => 'UG', //UJ
        3 => 'UK', //FP
        5 => 'UC', //UC
        6 => 'UP', //PE
        7 => 'UT', //TE
    );

    $data = "\r\n<OrderDownload>\r\n".
            "        <Header>\r\n".
            "            <User>S53730_001</User>\r\n".
            "            <UserLevel></UserLevel>\r\n".
            "            <ErrorMessage></ErrorMessage>\r\n".
            "            <GenerationDate>" . date('Ymd') . "</GenerationDate>\r\n".
            "           <GenerationTime>" . date('H:i:s') . "</GenerationTime>\r\n".
            "        </Header>\r\n";


    foreach($grpDocs as $doc){

      if(empty($sfvals[$doc[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...

        $errorSEUIdArr[] = $doc[0]['se_uid']; //list of smart event errors

      } else {

        $successSEUIdArr[] = $doc[0]['se_uid']; //list of smart event success
        $regionCode = (isset($depotTrans[$doc[0]['depot_uid']])) ? $depotTrans[$doc[0]['depot_uid']] : '  ';

        $data .=  "    <Order>\r\n".
                  "       <Header>\r\n".
                  "           <OrderID>" . str_pad(abs($doc[0]['document_number']), 6, '0', STR_PAD_LEFT) . "</OrderID>\r\n".
                  "           <OrderDate>"  . date('Ymd', strtotime($doc[0]['order_date'])) . "</OrderDate>\r\n".
                  "           <DistributeFrom>" . $regionCode . "</DistributeFrom>\r\n".
                  "           <IntoStore>"  . date('Ymd', strtotime($doc[0]['invoice_date'])) . "</IntoStore>\r\n".
                  "           <IntoStoreDayOfWeek></IntoStoreDayOfWeek>\r\n".
                  "           <DepartmentName></DepartmentName>\r\n".
                  "           <DepartmentID>" . htmlentities(trim($doc[0]['customer_order_number'])) . "</DepartmentID>\r\n".
                  "           <DepartmentFax></DepartmentFax>\r\n".
                  "           <SupplierName>RIALTO FOODS</SupplierName>\r\n".
                  "           <SupplierID>53730</SupplierID>\r\n".
                  "           <SupplierFax></SupplierFax>\r\n".
                  "           <DeliveryName>" . str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_name'])) . "</DeliveryName>\r\n".
                  "           <DeliveryID>" . htmlentities(trim($sfvals[$doc[0]["principal_store_uid"]]['value'])) . "</DeliveryID>\r\n".
                  "           <DeliveryFax></DeliveryFax>\r\n".
                  "           <DeliveryDate>"  . date('Ymd', strtotime($doc[0]['invoice_date'])) . "</DeliveryDate>\r\n".
                  "           <DeliveryDayOfWeek></DeliveryDayOfWeek>\r\n".
                  "       </Header>\r\n".
                  "       <Detail>\r\n";

        $lineCount = 2;
        foreach($doc as $i => $d){ //detail rows.

          //skip zero qty lines.
          if(!abs($d['document_qty']) > 0){ //skip if qty is zero.
            continue;
          }

          $data .="          <Line>\r\n".
                  "              <LineNumber>" . $lineCount . "</LineNumber>\r\n".
                  "              <SKU></SKU>\r\n".
                  "              <LineCanceled>N</LineCanceled>\r\n".
                  "              <ProductCode>" . trim($doc[$i]['product_code']) . "</ProductCode>\r\n".
                  "              <ProductDescription>" . htmlentities(str_replace(array('"',"'",','),array('','',''), strtoupper(trim($doc[$i]['product_description'])))) . "</ProductDescription>\r\n".
                  "              <MUQuantity>" . ($doc[$i]['document_qty']) . "</MUQuantity>\r\n".
                  "              <MUUnitMass></MUUnitMass>\r\n".
                  "              <MUUnits></MUUnits>\r\n".
                  "              <UnitCostPrice></UnitCostPrice>\r\n".
                  "              <UnitSellingPrice>" . number_format(round($doc[$i]['selling_price'], 2), 2, '.', '') . "</UnitSellingPrice>\r\n".
                  "              <MUCostPrice></MUCostPrice>\r\n".
                  "              <SellByDate>"  . date('Ymd', strtotime($doc[0]['invoice_date'])) . "</SellByDate>\r\n".
                  "          </Line>\r\n";

          $lineCount++;

        } //eof detail

        //order footer.
        $data .= "       </Detail>\r\n".
                 "    </Order>\r\n";

      } //has spf value.
    } //eof documents in type

    $data .= '</OrderDownload>';



    /*------------------ SUCCESS : START ---------------*/
    $filePath = false;
    if(count($successSEUIdArr)>0){

      //determine seq.
      $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid, false, 4, self::setFilenameFSEQ_LenType_PAD);
      if($seqFilename==false){
        BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
        return $this->errorTO;
      }

      //write physical file - placed in extracts folder
      $filePath = $this->createFile($folder, $seqFilename, $data);  //places file in correct folder
      if($filePath == false){
        BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in ".get_class($this)." on backup file creation";
        return $this->errorTO;
      }

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
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "208", "", FLAG_ERRORTO_ERROR);
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
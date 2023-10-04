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
class PopzInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class PopzInvoicedInit extends extractController {


  private $principalUid = 22; //uid of principal extract.
  private $invFilename = 'INV34[@FSEQ]i.XML';  //invoice filename
  private $crnFilename = 'INV34[@FSEQ]c.XML';  //credit & debit note filename

  //special field uids
  private $spfUId1 = 15; //shipto
  private $spfUId2 = 16; //billto

  //SGX SETTINGS
  private $recipient = '6002657000017';
  private $vendorNo = '6006677000347';
  private $vendorName= 'POPZ SOLLAR SA (PTY) LTD';
  private $vendorAddr1= 'P.O.BOX 51125';
  private $vendorAddr2= 'RAEDENE';
  private $vendorAddr3= '0';
  private $vendorAddr4= '';
  private $vendorCompanyRegNo = '';
  private $vendorVatNo = '9155437172';
  private $prefix = 'PH';
  private $site = 'aphar';


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

      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, $recipientUId);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
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

        //place into which type...
        $index = 'i';
        if(in_array($r['document_type_uid'], array(DT_CREDITNOTE, DT_DEBITNOTE))){
          $index = 'c';
        }
        $grpDocs[$index][$r['dm_uid']][] = $r;
        $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }


    if (sizeof($psms)>0) {  // get special field values for all stores in above docs
      $sfvalsShipTO = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, $this->spfUId1, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      $sfvalsBillTO = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, $this->spfUId2, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }


    $reasonArr = array(
                        3 => 100,
                        1 => 102,
                        4 => 103,
                        7 => 104,
                       72 => 113,
                       10 => 155,
                       76 => 201
                       );

    $isBlankFile = false;
    //enable blank file sending.
    if(count($grpDocs)==0){
      $grpDocs = array('i' => array());
      $isBlankFile = true;
    }

    $errorSEUIdArr = array(); //update errors at the end.


    //BUILD FILE FOR EACH TYPE invoices and credits namely.
    foreach($grpDocs as $type => $docArr){


      $successSEUIdArr = array();
      $filename = ($type == 'c') ? $this->crnFilename : $this->invFilename;


      $data = "<order_file>\r\n".
                "    <recipient>{$this->recipient}</recipient>\r\n".
                "    <vendor>\r\n".
                "         <vendor_no>{$this->vendorNo}</vendor_no>\r\n".
                "         <vendor_name>{$this->vendorName}</vendor_name>\r\n".
                "         <vendor_addr1>{$this->vendorAddr1}</vendor_addr1>\r\n".
                "         <vendor_addr2>{$this->vendorAddr2}</vendor_addr2>\r\n".
                "         <vendor_addr3>{$this->vendorAddr3}</vendor_addr3>\r\n".
                "         <vendor_addr4>{$this->vendorAddr4}</vendor_addr4>\r\n".
                "         <vendor_company_reg_no>{$this->vendorCompanyRegNo}</vendor_company_reg_no>\r\n".
                "         <vendor_vat_no>{$this->vendorVatNo}</vendor_vat_no>\r\n".
                "    </vendor>\r\n".
                "    <process_date>".date('Y-m-d')."</process_date>\r\n";


      foreach($docArr as $doc){

        if(empty($sfvalsShipTO[$doc[0]["principal_store_uid"]]['value']) || empty($sfvalsBillTO[$doc[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...

          $errorSEUIdArr[] = $doc[0]['se_uid']; //list of smart event errors

        } else {

          $successSEUIdArr[] = $doc[0]['se_uid']; //list of smart event success


          //order header.
          $data .="    <order>\r\n".
                  "        <order_hdr>\r\n".
                  "             <buyer>" . trim($sfvalsBillTO[$doc[0]["principal_store_uid"]]['value']) . "</buyer>\r\n". //SGX Bill to <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                  "             <buyer_name>" . str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_name'])) . "</buyer_name>\r\n".
                  "             <buyer_addr1>" . str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_add1'])) . "</buyer_addr1>\r\n".
                  "             <buyer_addr2>" . str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_add2'])) . "</buyer_addr2>\r\n".
                  "             <buyer_addr3>" . str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_add3'])) . "</buyer_addr3>\r\n".
                  "             <buyer_postcode></buyer_postcode>\r\n";
          if($type == 'i'){
            $data .= "             <order_no>" . $this->prefix . str_pad(abs($doc[0]['document_number']), 6, '0', STR_PAD_LEFT) . "</order_no>\r\n";
          } else {
            $data .= "             <order_no>" . $this->prefix . str_pad(abs($doc[0]['alternate_document_number']), 6, '0', STR_PAD_LEFT) . "</order_no>\r\n";
          }
          if($type == 'c'){
            $data .="             <original_order_no>" . str_pad(abs($doc[0]['source_document_number']), 6, '0', STR_PAD_LEFT) . "</original_order_no>\r\n"; //invoice (source document number)
            $data .="             <customer_pono>" . str_pad(abs($doc[0]['source_document_number']), 6, '0', STR_PAD_LEFT) . "</customer_pono>\r\n";
          } else {
            $data .="             <customer_pono>" . trim($doc[0]['customer_order_number']) . "</customer_pono>\r\n";
          }

          $data .="             <order_date>" . date('Y-m-d', strtotime($doc[0]['invoice_date'])) . "</order_date>\r\n".
                  "             <required_date>" . date('Y-m-d', strtotime($doc[0]['invoice_date'])) . "</required_date>\r\n".
                  "             <order_site>{$this->site}</order_site>\r\n".
                  "             <status>G</status>\r\n".
                  "             <branch>\r\n".
                  "                 <branch_no>" . trim($sfvalsShipTO[$doc[0]["principal_store_uid"]]['value']) . "</branch_no>\r\n".  //SGX Ship to <<<<<<<<<<<<<<<<<<<<<<<<<<<<
                  "                 <branch_name>" . str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_name'])) . "</branch_name>\r\n".
                  "                 <branch_addr1>" . str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_add1'])) . "</branch_addr1>\r\n".
                  "                 <branch_addr2>" . str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_add2'])) . "</branch_addr2>\r\n".
                  "                 <branch_addr3>" . str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_add3'])) . "</branch_addr3>\r\n".
                  "                 <branch_postcode></branch_postcode>\r\n".
                  "             </branch>\r\n".
                  "             <comment1></comment1>\r\n".
                  "             <comment2></comment2>\r\n";
          if($type == 'c'){
            $data .= "             <reason_code>" . ((isset($reasonArr[$doc[0]['reason_uid']]) ? ($reasonArr[$doc[0]['reason_uid']]) : (111))) . "</reason_code>\r\n";  //reason code. translate.
          }
          $data .= "        </order_hdr>\r\n";


          $totalCases = 0;
          $totalTaxable = 0;
          $totalTax = 0;
          $totalDue = 0;
          $lineCount = 1;

          foreach($doc as $i => $d){ //detail rows.


            //skip zero qty lines.
            if(!abs($doc[$i]['document_qty']) > 0){ //skip if qty is zero.
              continue;
            }

            $totalCases += ($doc[$i]['document_qty']);
            $totalTaxable += ($doc[$i]['extended_price']);
            $totalTax += ($doc[$i]['vat_amount']);
            $totalDue += ($doc[$i]['total']);


            $data .="       <order_det>\r\n".
                    "            <product>\r\n".
                    "                <line_no>" . str_pad($lineCount, 3 , '0', STR_PAD_LEFT) . "</line_no>\r\n".
                    "                <ean_no></ean_no>\r\n".
                    "                <vendor_product_code>" . trim($doc[$i]['product_code']) . "</vendor_product_code>\r\n".
                    "                <product_description>" . str_replace(array('"',"'",','),array('','',''), trim($doc[$i]['product_description'])) . "</product_description>\r\n".
                    "                <pack_size>\r\n".
                    "                <unit_pack></unit_pack>\r\n".
                    "                <unit_size></unit_size>\r\n".
                    "                </pack_size>\r\n".
                    "                <case_pack>1</case_pack>\r\n".
                    "                <order_quantity>" . ($doc[$i]['document_qty']) . "</order_quantity>\r\n".
                    "                <customer_quantity>" . ($doc[$i]['document_qty']) . "</customer_quantity>\r\n".
                    "                <unit_price_excl_vat>" . number_format(round($doc[$i]['selling_price'], 2), 2, '.', '') . "</unit_price_excl_vat>\r\n".
                    "                <deal_discounts>\r\n".
                    "                    <deal_discount_1></deal_discount_1>\r\n".
                    "                    <deal_value_1>" . number_format(round($doc[$i]['discount_value'], 2), 2, '.', '') . "</deal_value_1>\r\n".
                    "                    <deal_discount_2></deal_discount_2>\r\n".
                    "                    <deal_value_2></deal_value_2>\r\n".
                    "                </deal_discounts>\r\n".
                    "                <unit_gross_excl_vat>" . number_format(round($doc[$i]['net_price'], 2), 2, '.', '') . "</unit_gross_excl_vat>\r\n".
                    "                <extended_value_excl_vat>" . number_format(round($doc[$i]['extended_price'], 2), 2, '.', '') . "</extended_value_excl_vat>\r\n".
                    "                <vat_percentage>" . number_format(round($doc[$i]['vat_rate'], 2), 2, '.', '') . "</vat_percentage>\r\n".
                    "                <extended_vat_value>" . number_format(round($doc[$i]['vat_amount'], 2), 2, '.', '') . "</extended_vat_value>\r\n".
                    "                <extended_value_incl_vat>" . number_format(round($doc[$i]['total'], 2), 2, '.', '') . "</extended_value_incl_vat>\r\n".
                    "            </product>\r\n".
                    "       </order_det>\r\n";

            $lineCount++;

          } //eof detail

          //order footer.
          $data .= "       <order_mass></order_mass>\r\n".
                   "       <order_total_qty>{$totalCases}</order_total_qty>\r\n".
                   "       <order_customer_qty>{$totalCases}</order_customer_qty>\r\n".
                   "       <order_total_excl_vat>" . number_format(round($totalTaxable, 2), 2, '.', '') . "</order_total_excl_vat>\r\n".
                   "       <order_vat>" . number_format(round($totalTax, 2), 2, '.', '') . "</order_vat>\r\n".
                   "       <order_total_incl_vat>" . number_format(round($totalDue, 2), 2, '.', '') . "</order_total_incl_vat>\r\n".
                   "    </order>\r\n";


        } //has spf value.
      } //eof documents in type

      $data .= '</order_file>';



      /*------------------ SUCCESS : START ---------------*/
      $filePath = false;
      if(count($successSEUIdArr)>0 || $isBlankFile){

        //determine seq.
        $seqFilename = $this->setFilenameFSEQ($filename, $this->principalUid);
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


        //create actual file to local sgx folder.
        $fp = file_put_contents(DIR_DATA_SURESERVER_NON_FTP_FROM . 'sgx/tosgx/' . $seqFilename, $data);
        if($fp != strlen($data)){
          BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on actual file creation";
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

    } //eof all documents

      /*------------------ ERRORS : START ---------------*/
      //ERRORS - bulk action and email out...
      if (sizeof($errorSEUIdArr) > 0) {


          // SETUP DISTRIBUTION
          $postingDistributionTO = new PostingDistributionTO;
          $postingDistributionTO->DMLType = "INSERT";
          $postingDistributionTO->deliveryType = BT_EMAIL;
          $postingDistributionTO->subject = $this->getTemplateErrorSubject(); //might have no filename if all errors therefore don't display on subject line...
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
            $this->errorTO->description = "Failed in ".get_class($this)." extract no valid Recipient/Contact found, no outgoing error mail generated!";
            BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
            return $this->errorTO;
          }

          //MARK SE AS "E", for extract errors display screen.
          $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), $this->spfUId1 . "," . $this->spfUId2, "", FLAG_ERRORTO_ERROR);
          if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
            BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
            return $this->errorTO;
          }
      }
      /*------------------ ERRORS : END ---------------*/


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
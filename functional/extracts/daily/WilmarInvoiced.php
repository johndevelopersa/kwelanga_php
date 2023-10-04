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
class WilmarInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class WilmarInvoicedInit extends extractController {


  private $principalUid = 351; //uid of principal extract.
  private $filename      = 'KOSXX[@FSEQ].dat';  //main controller will build full filename with seq. for us.
  private $crnFilename   = 'KOSXX[@FSEQ].dat';  //credit note filename
  private $stockFilename = 'KOSXX[@FSEQ].dat';  //Stock Movement filename

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
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, 
                                                     $recipientUId, 
                                                     $inclCancelled = false,
                                                     $p_dtArr = false,
                                                     $p_wDSArr = false,
                                                     $fromInvDate=false,
                                                     $toInvDate=false,
                                                     $chainUIdIn=false,
                                                     $dataSource=false,
                                                     $capturedBy=false,
                                                     $depotUId = false);
       //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }

      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, 
                                                             $recipientUId, 
                                                             array(DT_CREDITNOTE, DT_ARRIVAL, DT_STOCKADJUST_POS, DT_STOCKADJUST_NEG),
                                                             $depotUId = false,
                                                             $fromInvDate=false,
                                                             $toInvDate=false,
                                                             $dataSource=false,
                                                             $capturedBy=false,
                                                             $depotUId = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***

      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
    }

    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($this->principalUid, $recipientUId);
    /*  SUCCESS POINT - 1  */
    //nothing to do...
    
    echo "<pre>";
    print_r($seDocs);
    echo "<br>";
    
    
    
    
    
    
    
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
        //place into groups
        $type = 'i'; //invoices
                if ($r['document_type_uid'] == DT_CREDITNOTE){
           $type = 'c';
        } elseif ($r['document_type_uid'] == DT_ARRIVAL){
           $type = 'a';         
        } elseif ($r['document_type_uid'] == DT_STOCKADJUST_POS) {
           $type = 'si';
        } elseif ($r['document_type_uid'] == DT_STOCKADJUST_NEG) {
           $type = 'sr';
        }
        $grpDocs[$type][$r['dm_uid']][] = $r;  //group by index.
        $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }
    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
         $sfvals  = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 415, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");  //Account - S/Sure
         $sfvalsc = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 419, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");  //Account - S/Sure
    }
    
    $depotTrans = array(195  => '10', // Loginet
                        202  => 'N01', // TK
                        384  => '91',
                        190  => '90'
                        );

    foreach($grpDocs as $type => $hr){
         /*-------------------------------------------------*/
         /* START BUILDING OUTPUT
         /*-------------------------------------------------*/
    
         $errorSEUIdArr    = array();
         $successSEUIdArr  = array();
         $dataArr          = array();
         $filecount = 0; 
         $successCount = 0;
       	

         foreach($hr as $ord){ //document loop

              if((empty($sfvals[$ord[0]["principal_store_uid"]]['value'])) || (empty($sfvalsc[$ord[0]["principal_store_uid"]]['value'])) ) {  //has no special field and/or blank...
                  $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors
              } else {
                  $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success
                  $storeAcc  = $sfvals[$ord[0]["principal_store_uid"]]['value'];
                  $storeCode = $sfvalsc[$ord[0]["principal_store_uid"]]['value'];
                  //array containing list of row values
                  $ordCount = 0;
                  $successCount++;
                  $dataH = array();
                                      
                  $regionArr = (isset($depotTrans[$ord[0]['depot_uid']])) ? $depotTrans[$ord[0]['depot_uid']] : false;                    
                  
                  if($type == 'i') { 
                      $dataH[] = '040';                                                                             // Line type
                      $dataH[] = '1';                                                                               // Version
                      $dataH[] = $storeAcc;                                                                         // PrincipalsStoreCode
                      $dataH[] = trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"]));   // SenderOrderNr
                      $dataH[] = trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"]));   // Customer Order Number
                      $dataH[] = str_pad(substr($ord[0]['document_number'],-6), 6, 0, STR_PAD_LEFT);                // OrderNr
                      $dataH[] = str_pad(substr($ord[0]['document_number'],-6), 6, 0, STR_PAD_LEFT);                // InvoiceNr                  
                      $dataH[] = date("Ymd", strtotime($ord[0]["invoice_date"]));                                   // InvoiceDate
                      $dataH[] = '';                                                                                // RepCode 
                      $dataH[] = $regionArr;                                                                                // WH Cde 
                      $dataH[] = date("Ymd", strtotime($ord[0]["invoice_date"]));                                   // OrderDate
                      $dataH[] = trim(str_replace(array('"',"'"),array('',''),$ord[0]["branch_code"]));             // StoreBranchCode
                      $dataH[] = '';                                                                                // PrincipalXtraData
                      $dataH[] = $storeCode;                                                                        // StoreCode
                      $dataH[] = '';                                                                                // StoreGuid
                      $dataH[] = $storeAcc;                                                                         // AccountCode
//                    $dataH[] = '';                                                                                // AccountGuid
//                    $dataH[] = '';                                                                                // VatRegNumber
//                    $dataH[] = '';                                                                                // AccountVatRegNr
//                    $dataH[] = '';
                      $fileType = 'B';
                  } elseif ($type == 'c') {
                      $dataH[] = '050';                                                                             // RecordType	x	"050"
                      $dataH[] = '1';                                                                               // Version
                      $dataH[] = 'RET';                                                                             // DocumentType	x	"RET" (pod return), "SAR" (non-pod return)
                      $dataH[] = $storeAcc;                                                                         // PrincipalStoreCode	x	
                      $dataH[] = str_pad(substr($ord[0]['alternate_document_number'],-6), 6, 0, STR_PAD_LEFT);      // CreditNoteNr	x	 
                      $dataH[] = ' ';                                                                               // RepCode	x	
                      $dataH[] = ' ';                                                                               // SalesDcCode	x	Sales were updated in this Dc
                      $dataH[] = date("Ymd", strtotime($ord[0]["invoice_date"]));                                   // CreditNoteDate	d	
                      $dataH[] = str_pad(substr($ord[0]['source_document_number'],-6), 6, 0, STR_PAD_LEFT);         // OriginalInvoiceNr	x	
                      $dataH[] = ' ';                                                                               // PickupAdviceNr	x	SAR only
                      $dataH[] = $regionArr;                                                                        // StockDcCode	x	Stock was updated in this Dc
                      $dataH[] = ' ';                                                                               // Reference	x	usually the claim#
                      $dataH[] = $storeAcc;                                                                         // StoreCode	x	
                      $dataH[] = ' ';                                                                               // StoreGuid	x	 
                      $dataH[] = '1';                                                                               // AccountCode	x	
                      $dataH[] = '';                                                                                // AccountGuid	x	
//                    $dataH[] = '1';                                                                               // VatRegNumber	x	
//                    $dataH[] = '1';                                                                               // AccountVatRegNr	x	
//                    $dataH[] = '1';                                                                               // PrincipalReturnAuthorisation#	x	RET only
//                    $dataH[] = '1';                                                                               // StoreBranchCode	x	Stores own branch code
//                    $dataH[] = '1';
                      $fileType = 'B';                                                                              		
                  } elseif ($type == 'a') {
                      $dataH[] = '080';                                                                             // RecordType	x	"080"
                      $dataH[] = '1';                                                                               // Version	x	"1"
                      $dataH[] = str_pad(substr($ord[0]['document_number'],-6), 6, 0, STR_PAD_LEFT);                // GrnNr	x	Goods Received Note
                      $dataH[] = trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"]));   // SdnNr	x	Supplier Delivery Note
                      $dataH[] = $regionArr;                                                                        // DcCode	x	
                      $dataH[] = 'P226';                                                                               // SupplierCode	x	Pxxx=principal, Dxx=dc   P226
                      $dataH[] = date("Ymd", strtotime($ord[0]["invoice_date"]));                                   // GrnDate	d	
                      $dataH[] = ' ';                                                                               // <unused>	 	
                      $dataH[] = ' ';                                                                               // Reference	x	
//                    $dataH[] = ' '; 
                      $fileType = 'A';                                                        // <do not delete>	 	
                  } elseif ($type == 'si' || $type == 'sr') {
                  	    // No Header - Do nothing
                  	    $fileType = 'A';
                  } 
                  
                  if(count($successSEUIdArr)==1){
                  	  if($type=='i') {
                  	  	   $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid, false, 6);
                  	  } elseif($type=='c') {
                  	      $seqFilename = $this->setFilenameFSEQ($this->crnFilename, $this->principalUid, false, 6);
                  	  } elseif($type=='a' || $type=='si' || $type=='sr') {
                          $seqFilename = $this->setFilenameFSEQ($this->stockFilename, $this->principalUid, false, 6);
                      }                      
                      if($seqFilename==false){
                          BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
                          return $this->errorTO;
                      }
                      $fileHeader = array();
                      $fileHeader[] = '000';                                                        // RecordType
                      $fileHeader[] = '1';                                                          // Version
                      $fileHeader[] = '6001651048339';                                              // PartnerGUID x 13 m 
                      $fileHeader[] = '6009668780006';                                              // CompanyGUID x 13 m 
                      $fileHeader[] = date("Ymd") ;                                                 // DateOfPreparation d  m 
                      $fileHeader[] = date("Hi");                                                   // TimeOfPreparation t  m 
                      $fileHeader[] = substr($seqFilename,5,4);                                     // PartnerTransmissionNumber n  m sequential and unique per partner
                      $fileHeader[] = substr($seqFilename,5,4);                                     // SwitchTransmissionNumber n  m sequential and unique per switch
                      $fileHeader[] = date("d") ;   //FinancialDay#	n
                      $fileHeader[] = date("m") ;   //FinancialMonth#	n
                      $fileHeader[] = date("Y") ;   //FinancialYear#
                      $fileHeader[] = '';                                                           // <do not delete>    
                      $filecount++; 
                      $dataArr[] = join('|', $fileHeader);
                  }
                  $linecount = 0;
                  if ($type <> 'si' && $type <> 'sr') {
                       $ordCount++;
                       $filecount++; 
                       $dataArr[] = join('|', $dataH);
                  }
                  foreach($ord as $d) { //detail rows.
//                	print_r($d);                      	
                       if(abs($d['document_qty']) > 0) {
                       	   $linecount++;
                           $detArr = array();
                           if($type == 'i') { 
                                $detArr[] = '041';                                                      // RecordType	x	"041"
                                $detArr[] = $linecount;                                                 // OrderLineSequenceNr	n	As supplied by the sales agent on the order
                                $detArr[] = trim(str_replace(array('"'),array(''),$d['product_code'])); // PrincipalStockCode	x	 
                                $detArr[] = abs($d['document_qty']);                                    // QuantitySupplied	n	sale units
                                $detArr[] = '';                                                         // LotNr	x	 
                                $detArr[] = number_format(round($d['extended_price'], 2), 2, '.', '');  // ExclLineAmount	m.2	Full value of the line (excluding VAT)
                                $detArr[] = number_format(round($d['total'], 2), 2, '.', '');           // InclLineAmount	m.2	Full value of the line (including VAT)
                                $detArr[] = number_format(round($d['vat_rate'], 2), 2, '.', '');        // Vat%	m.2	 
                                $detArr[] = '';                                                         // DealNr	x	 
                                $detArr[] = '';                                                         // ExpiryDate	d	
                                $detArr[] = $d['sku_gtin'];                                             // SingleGuid	x	 
                                $detArr[] = trim($d['items_per_case']);                                 // SinglesPerSaleUnit	n	
                                $detArr[] = trim(str_replace(array('"'),array(''),$d['product_code'])); // StockCode	x	
                                $detArr[] = $d['sku_gtin'];                                             // SaleUnitGuid	x	
                                $detArr[] = 'CASE';                                                     // SaleUnitLevel	x	“SINGLE”,”SHRINK”,”CASE”
//                              $detArr[] = ' ';                                                        // PrincipalXtraLineData	x	
//                              $detArr[] = ' ';                                                        // SunitGrossPrice	m.5	Exclusive of VAT (as quoted on the deal)
//                              $detArr[] = ' ';                                                        // SaleUnitListPrice	m.2	Exclusive of VAT (as stored on the stock record)
//                              $detArr[] = ' ';                                                        // PermanentDisc%	m.2	
//                              $detArr[] = ' ';                                                        // PromotionDisc%	m.2	
//                              $detArr[] = ' '; 
                           } elseif ($type == 'c') {
                                $detArr[] = '051';                                                      // RecordType	x	"051"
                                $detArr[] = trim(str_replace(array('"'),array(''),$d['product_code'])); // PrincipalStockCode	x	 
                                $detArr[] = trim($d['document_qty']);                                   // Quantity	n	sale units
                                $detArr[] = '';                                                         // ReasonCode	x	 
                                $detArr[] = number_format(round($d['extended_price'], 2), 2, '.', '');  // ExclLineAmount	m.2	Full value of the line (excluding VAT)
                                $detArr[] = number_format(round($d['total'], 2), 2, '.', '');           // InclLineAmount	m.2	Full value of the line (including VAT)
                                $detArr[] = number_format(round($d['vat_rate'], 2), 2, '.', '');           // Vat%	m.2	 
                                $detArr[] = '';                                                         // DealNr	x	
                                $detArr[] = '';                                                         // LotNr	x	 
                                $detArr[] = '';                                                         // ExpiryDate	d	 
                                $detArr[] = trim($d['items_per_case']);                                 // SinglesPerSaleUnit	n	
                                $detArr[] = trim(str_replace(array('"'),array(''),$d['product_code'])); // StockCode	x	
                                $detArr[] = $d['sku_gtin'];                                             // SaleUnitGuid	x	
                                $detArr[] = 'CASE';                                                     // SaleUnitLevel	x	“SINGLE”,”SHRINK”,”CASE”
//                              $detArr[] = '';                                                         // PrincipalsReturnAuthorisationLineReference	n	As supplied by the principal (RET only)
//                              $detArr[] = '';                                                         // SunitGrossPrice	m.5	Exclusive of VAT (as quoted on the deal)
//                              $detArr[] = '';                                                         // SaleUnitListPrice	m.5	Exclusive of VAT (as stored on the stock record)
//                              $detArr[] = '';                                                         // PermanentDisc%	m.2	
//                              $detArr[] = '';                                                         // PromotionDisc%	m.2	
//                              SingleGuid	x	 
//                              <do not delete                         	
                           } elseif ($type == 'a') {
                                $detArr[] = '081';                                                       //RecordType	x	"081"
                                $detArr[] = trim(str_replace(array('"'),array(''),$d['product_code']));  // PrincipalStockCode	x	 
                                $detArr[] = abs($d['document_qty']);                                     // Quantity	n	sale units
                                $detArr[] = '';                                                          // LotNr	x	 
                                $detArr[] = $linecount;                                                  // LineSequence#	n	unique and ascending within the SDN
                                $detArr[] = '';                                                          // ExpiryDate	d	
                                $detArr[] = trim($d['items_per_case']);                                  // SinglesPerSaleUnit	n	
                                $detArr[] = trim(str_replace(array('"'),array(''),$d['product_code']));  // StockCode	x	226..
                                $detArr[] = '';                                                          // SaleUnitGuid	x	
                                $detArr[] = 'CASE';                                                      // SaleUnitLevel	x	“SINGLE”,”SHRINK”,”CASE”
                                $detArr[] = '';                                                          // <do not delete>	 	
                           } elseif ($type == 'si' || $type == 'sr') {
                                $detArr[] = '100';                                                                              // RecordType	x	"100"
                                $detArr[] = '1';                                                                                // Version	x	"1"
                                $detArr[] = trim(str_replace(array('"'),array(''),$d['product_code']));                         // PrincipalStockCode	x	 
                                $detArr[] = $regionArr;                                                                                 // DcCode	x	 
                                $detArr[] = trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"]));    // Reference	x	
                                $detArr[] = '';                                                                                 // ReasonCode	x	 
                                if($type == 'si') {
                                    $detArr[] = trim($d['document_qty']);                                                       // Quantity	n	sale units	
                                } elseif ($type == 'sr') { 
                                    $detArr[] = 0-trim($d['document_qty']);                                                     // Quantity	n	sale units
                                }
                                $detArr[] = date("Ymd", strtotime($ord[0]["invoice_date"]));                                    // AdjustmentDate	d	
                                $detArr[] = str_pad(substr($ord[0]['document_number'],-6), 6, 0, STR_PAD_LEFT);                 // AdjustmentNr	n	
                                $detArr[] = '';                                                                                 // LotNr	x	 
                                $detArr[] = '';                                                                                 // 11	<unused>		
                                $detArr[] = '';                                                                                 // ExpiryDate	d	
                                $detArr[] = trim($d['items_per_case']);                                                         // SinglesPerSaleUnit	n	
                                $detArr[] = trim(str_replace(array('"'),array(''),$d['product_code']));                         // stockCode	x	226…
                                $detArr[] = '';                                                                                 // 15	SaleUnitGuid	x	
                                $detArr[] = 'CASE';                                                                             // SaleUnitLevel	x	“SINGLE”,”SHRINK”,”CASE”
                                $detArr[] = '';                                                                                 //  <do not delete>	 	
                           }
                           $ordCount++;
                           $filecount++;
                           $dataArr[] = join('|',$detArr);
                       }                       
                  } //eo detail
                  $otArr = array();
                  $ordCount++;
                  if ($type == 'i') {
                      $otArr[] = '049';
                      $filecount++;
                      $otArr[] = trim($ordCount);
                      $dataArr[] = join('|',$otArr);
                  } elseif ($type == 'c') {      
                      $otArr[] = '059';
                      $filecount++;
                      $otArr[] = trim($ordCount);
                      $dataArr[] = join('|',$otArr);
                  } elseif ($type == 'a') {      
                      $otArr[] = '089';
                      $filecount++;
                      $otArr[] = trim($ordCount);
                      $dataArr[] = join('|',$otArr);
                  } 
               } //eo special field check
         } //eo document
         $filecount++;
         $fileTrl = array();
         $fileTrl[] = '009';                                                        // RecordType
         $fileTrl[] = trim($filecount);
         $dataArr[] = join('|',$fileTrl); 
 
         //create file only if there are successful items.
                  
         if(count($successSEUIdArr)>0){
         	   $data = join("\r\n",$dataArr);
             //write physical file
             
             $filePath = $this->createFile($folder, str_replace('XX',$fileType,$seqFilename), $data);  //places file in correct folder
           
             if($filePath == false){
                 BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
                 $this->errorTO->type = FLAG_ERRORTO_ERROR;
                 $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
                 return $this->errorTO;
             }
              //create actual file to local FTP folder.
              // echo DIR_DATA_NON_FTP_FROM . 'ftp/willmar/invoices/';
             $fp = file_put_contents(DIR_DATA_NON_FTP_FROM . 'ftp/willmar/invoices/' . str_replace('XX',$fileType,$seqFilename), $data);
         }
         // SETUP DISTRIBUTION
         $postingDistributionTO = new PostingDistributionTO;
         $postingDistributionTO->DMLType = "INSERT";
         $postingDistributionTO->deliveryType = BT_EMAIL;
         if($type=='i') { $postingDistributionTO->subject = $this->getTemplateInvoiceSubject(); } 
         elseif ($type=='c') { $postingDistributionTO->subject = $this->getTemplateCreditSubject(); }
         elseif ($type=='a') { $postingDistributionTO->subject = $this->getTemplateArrivalSubject(); }
         elseif ($type == 'si' || $type == 'sr') {{ $postingDistributionTO->subject = $this->getTemplateAdjustmentSubject(); }}
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
          $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "225,227", "", FLAG_ERRORTO_ERROR);
          if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
            BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
            return $this->errorTO;
          }
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
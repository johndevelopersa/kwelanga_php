<?php

/* * ********************************************************************************************
 * *
 * *  LIVE Omni - SALES ORDER PUSH TO OMNI RESTAPI API
 * *
 * *****************ss*************************************************************************** */

require_once __DIR__ . "/../../../libs/api/omni/OmniRestAPI.php";
require_once __DIR__ . "/../../../properties/RhodesConstants21.php";

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/BIDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/messagingDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");


set_time_limit(15*60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    
}

echo "<pre style='font-size:14px;'>";
echo str_repeat("-", 75) . "\n";
echo "OMNI LIVE SALES ORDERS SYNC PROCESSOR\n";
echo str_repeat("-", 75) . "\n";


/*-------------------------------------------------*/
/*  FETCH Notification Recipients ??
/*-------------------------------------------------*/
//use the receipients listed in the notification table instead of hard coding them!!!
//expecting only one row loaded per principal extract
$reArr = (new BIDAO($dbConn))->getNotificationRecipients(RhodesConstants21::PrincipalID, NT_DAILY_EXTRACT_CUSTOM);
if (count($reArr) == 0) {
    BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in " . __FILE__, "Y");
    exit;
}
$recipientUId = $reArr[0]['uid'];

/*-------------------------------------------------*/
/*  QUEUE DOCUMENTS IN SMART EVENTS
/*-------------------------------------------------*/
// Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
$documentTypeArr = [
    DT_DELIVERYNOTE,
    DT_ORDINV,
    DT_ORDINV_ZERO_PRICE,
];
$documentStatusArr = [
    $importStatus
];

$rTO = (new PostExtractDAO($dbConn))->queueAllInvoiced(RhodesConstants21::PrincipalID,
    $recipientUId,
    $inclCancelled = false,
    $documentTypeArr,
    $documentStatusArr,
    $fromInvDate=RhodesConstants21::RhodesStartDate,
    $toInvDate=false,
    $chainUIdIn= false,
    $dataSource=false,
    $capturedBy=false,
    $depotUId = $importWarehouse ,
    $altChainUIdIn=false);  
    
    //use the loaded receipientUID and not the notification type... *** same as document confirmations***

if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
    BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in " . __FILE__ . " ERROR:" . $rTO->description, "Y");
} else {
    $dbConn->dbinsQuery("commit;");
}

/*-------------------------------------------------*/
/*  FETCH ORDERS THAT NEED TO BE SENT.
/*-------------------------------------------------*/
echo "Fetching getDailyExtractInvoicedOrders\n";
$seDocs = (new ExtractDAO($dbConn))->getDailyExtractInvoicedOrdersWithParms(RhodesConstants21::PrincipalID, $recipientUId, '', '');

if (!is_array($seDocs) || is_array($seDocs) && count($seDocs) == 0) {
    echo "Successful --> No outstanding orders found!<br>";
    $errorTO = new ErrorTO();
    $errorTO->type = FLAG_ERRORTO_SUCCESS;  //preset.
    $errorTO->object = array();
/*    Update Status' not sue if I need this here    
    
    $setOmniImportAll = new PostBIDAO($dbConn);
    $mTResult = $setOmniImportAll->setDocumentConfirm(RhodesConstants21::PrincipalID, RhodesConstants21::ConfirmStartStatus, RhodesConstants21::ConfirmEndStatus);
    
*/    
    return($errorTO);
}

echo "Found: " . count($seDocs) . " order lines\n";


// Reade Extract Array and do one order at a time

$orderStore = '';

foreach($seDocs as $row) {

//       echo "<br>";
//       echo "<pre>";	
//       print_r($row);	
//       echo "<br>";
       
       if($orderStore <> $row['dm_uid']) {   // Set Up Header
              $storeSpecialArr = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesMultEntities(RhodesConstants21::PrincipalID, 514, $row['principal_store_uid'], CT_STORE_SHORTCODE, $arrayIndex = FALSE);

              $depotSpecialArr = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesMultEntities(RhodesConstants21::PrincipalID, 516, $row['depot_uid'], CT_DEPOT_SHORTCODE, $arrayIndex = FALSE);
              if (empty($storeSpecialArr[0]['value'])) {  //has no special field and/or blank...
                   echo "missing special field for store:" . $row['principal_store_uid'] . "\n<br>";
                   $updateSEstatus = FLAG_STATUS_ERROR;
                   $updateGeneral1 = '';
                   $general2       = '';
                   $statusMsg  = "[KOS] Missing Omni Account for store:   <br>";                   
                   $setOmniImportAll = new PostBIDAO($dbConn);
                   $mTResult = $setOmniImportAll->setSmartEventStatusIndividual($row['se_uid'], $updateGeneral1, $general2 , $statusMsg , $updateSEstatus );
                   $processErrors = 'Y';
                   continue;
              }
              $storeSpecialValue = $storeSpecialArr[0]['value'];

              if (empty($depotSpecialArr[0]['value'])) {  //has no special field and/or blank...
                   echo "missing special field for depot:" .$row['depot_uid'] . "<br>";
                   $updateSEstatus = FLAG_STATUS_ERROR;
                   $statusMsg  = "[KOS] Missing Omni Depot:   <br>"; 
                   $updateGeneral1 = ''; 
                   $general2       = '';
                   $setOmniImportAll = new PostBIDAO($dbConn);
                   $mTResult = $setOmniImportAll->setSmartEventStatusIndividual($row['se_uid'], $updateGeneral1, $general2 ,  $statusMsg , $updateSEstatus );
                   $processErrors = 'Y';
                   continue;
              }
              $depotSpecialValue = trim($depotSpecialArr[0]['value']);

              if($orderStore <> '') {           // First Loop Don't Send and update
              	    break;
                    WriteToOmni($omniOrder, $documentNumber, $orderStore, $setypeStore, $dhStatus='',$seUID) ; 
                    
              }
       	      $processErrors = 'N';
       	      $hasDetail     = 'N';
       	      $orderStore    = $row['dm_uid'];
       	      $setypeStore   = $row['type_uid'];
       	      $seUID         = $row['se_uid']; 
       	      
       	      // sleep(10);
              /*-------------------------------------------------*/
              /*  LOOK UP SPECIAL FIELDS
              /*-------------------------------------------------*/	
              // Get special field values for all stores in above docs


              if($processErrors == 'N') {
                  echo "Posting. " . $row['dm_uid'] . ":- Doc:" . $row['document_number'] . " Cust Ord:" . $row['customer_order_number'] . " Depot:" . $row['depot_name'] .'/'. $depotSpecialValue . ' Store:' . $row['deliver_name'] .'/'. $storeSpecialValue . '\n<br>';
 
                   $documentNumber =  ltrim($row['document_number']);
                   $clientDocNumber = ltrim($row['client_document_number']); $customerOrderNumber = $row['customer_order_number'];
                   $branchCode = $row['branch_code'];   
                   $documentStatus = $row['document_status_uid'];
                   $orderDate = $row['customer_order_number'];
                   $orderDate = $orderDate != "0000-00-00" ? $orderDate : null;
                   $invoiceDate = $row['invoice_date'];
                   $invoiceDate = $invoiceDate != "0000-00-00" ? $invoiceDate : null;
                   $deliveryDay = $row['delivery_date'];
                   $deliveryDay = $deliveryDay != "0000-00-00" ? $deliveryDay : null;
                   $dueDeliveryDate = $row['requested_delivery_date'];
                   $dueDeliveryDate = $dueDeliveryDate != "0000-00-00" ? $dueDeliveryDate : null;
                   $dataSource = $row['data_source'];
                   $incomingFile = substr($row['incoming_file'],0,30);
                   $sourceDocumentNumber = $row['source_document_number'];
                   $storeVatNumber = ''; // $row['vat_number'];
                   if(trim($row['delivery_instructions']) == "" || $row['delivery_instructions'] == NULL) {
                          $deliveryDetails = '..'; 	
                   } else {
                          $deliveryDetails = substr($row['delivery_instructions'],0,59);                    	
                   }
                   $deliverName = substr($row["deliver_name"],0,30);
                   $deliverAdd1 = substr($row["deliver_add1"],0,30);
                   $deliverAdd2 = substr($row["deliver_add2"],0,30);
                   $deliverAdd3 = substr($row["deliver_add3"],0,30);
                   $billName = substr($row["bill_name"],0,30);
                   $billAdd1 = substr($row["bill_add1"],0,30);
                   $billAdd2 = substr($row["bill_add2"],0,30);
                   $billAdd3 = substr($row["bill_add3"],0,30);
                   $buyeraccountreference = trim($row["buyer_account_reference"]);
              }

              /*-------------------------------------------------------------          
               *      CREATE ORDER OBJECT                                              
               *-----------------------------------------------------------*/          
              $omniOrder = (new OmniSalesOrderObj)                                     
                           ->setAnalysis4($storeSpecialValue)	//###                            
                         //->setAreaCode()                                                    
                           ->setBranchCode($branchCode)                                         
                         //->setCapturedBy()                                                  
                           ->setCustomerAccountCode($storeSpecialValue)                         
                           ->setCustomerOrderNo($customerOrderNumber)                           
                           ->setDeliveryDay($deliveryDay)                                       
                           ->setDeliveryDetails($deliveryDetails)  //  *************            
                         //->setDeliveryRoute()                                               
                           ->setDocumentDate($invoiceDate)                                      
                           ->setDueDate($dueDeliveryDate)                                       
                           ->setExtraInfo3($incomingFile)                                       
                         //->setExtraInfo6()                                                  
                         //->setExtraInfo7()                                                  
                         //->setJobNo()                                                       
                         //->setMemo()                                                        
                         //->setOverallDiscountCode()                                         
                         //->setPhysicalAddress1($deliverName)                                
                         //->setPhysicalAddress2($deliverAdd1)                                
                         //->setPhysicalAddress3($deliverAdd2)                                
                         //->setPhysicalAddress4($deliverAdd3)                                
                         //->setPhysicalAddress5()                                            
                         //->setPostalAddress1($billName)                                     
                         //->setPostalAddress2($billAdd1)                                     
                         //->setPostalAddress3($billAdd2)                                     
                         //->setPostalAddress4($billAdd3)                                     
                         //->setPostalAddress5()                                              
                         //->setPostCode()                                                    
                           ->setPrincipalinv($clientDocNumber)  //  ************                
                         //->setPrinciple("KOS")                                             
                         //->setRepCode("")                                                  
                           ->setRevenueAccCode(RhodesConstants21::RevenueAccount)                 
                         //->setSourceReference($sourceDocumentNumber)                          
                         //->setSourceType($dataSource)                                        
                         //->setStoreName($deliverName)                                      
                         //->setVatRegistrationNo($buyeraccountreference)                    
                           ->setWarehouseCode($depotSpecialValue);                        
                         //->setAnalysis4();
       }
       if($orderStore == $row['dm_uid'] && $processErrors == 'N') { 
       	
                if (abs($row['ordered_qty']) == 0) {
                    continue;
                }

                //set and build required fields here
                if($documentStatus == DST_INVOICED) {
                     $documentQty = abs($row['document_qty']);	
                } else {
                     $documentQty = abs($row['ordered_qty']);
                }
                if($row['alt_code'] == NULL) {
                     $productCode = trim(str_replace(['"'], [''], $row['product_code']));
                } else {
                     $productCode = trim(str_replace(['"'], [''], $row['alt_code']));
                }        
                $productDescription = substr(trim(str_replace(['"', '\\', "\t", "\n", "\r"], ['', '', '', '', ''], $row['product_description'])),0,30);
 
                if ($row['vat_rate'] == '0.00') {
                       $vatCode = '2';
                } else {
                       $vatCode = '1';	        	
                }
        
                $ExtDiscountValue = NULL;
                $ExtPrice         = NULL;
                $ExtPriceExcl     = NULL;
                $ExtPriceIncl     = NULL;
        
                $vatRate          = NULL;
                $vatAmt           = NULL;
        
                //        $ExtDiscountValue = (number_format($documentQty * $row['discount_value'], 2, '.', ''));
                //        $ExtPrice         = (number_format($row['extended_price'], 2, '', ''));
                //        $ExtPriceExcl     = (number_format($row['extended_price'], 2, '.', ''));
                //        $ExtPriceIncl     = (number_format($row['total'], 2, '.', ''));
        
                $sendWithPrice = 'N';       // Price fields will be as above else price not sent at all
                /*-------------------------------------------------------------   
                 *      CREATE ORDER LINES                                        
                 *-----------------------------------------------------------*/   
                 $orderLineItem = (new OmniSalesOrderLineItemObj)                 
                                                                               
                //->setBackOrders()                                            
                //->setBarCode()                                               
                //->setBinLocationCode()                                       
                //->setBinLocationDescription()                                
                //->setCapturedBy()                                            
                //->setCostPrice()                                             
                //->setCostPricePer()                                          
                //->setCredited()                                              
                //->setDealGroupNo()                                           
                //->setDealId()                                                
                //->setDealNarrative()                                         
                //->setDelivered()                                             
                //->setDescription()                                           
                //->setDiscount()                                              
                //->setDiscountType()                                          
                //->setDueDate()                                               
                //->setExtCostPrice()
                //->setExtDiscountValue()                     
                //->setExtPrice()                                     
                //->setExtPriceExcl()                             
                //->setExtPriceIncl()              
                //->setExtraInfo1()                                            
                //->setExtraInfo2()                                            
                //->setExtraInfo3()                                            
                //->setExtraInfo4()                                            
                //->setExtraInfo5()                                            
                //->setExtraInfo6()                                            
                //->setExtraInfo7()                                            
                //->setExtraInfo8()                                            
                //->setExtVolume()                                             
                //->setExtWeight()                                             
                //->setGoodStock()                                             
                //->setGpPercent()                                             
                //->setGrossProfit()                                           
                //->setId()                                                    
                //->setInvoiced(0)  //OMNI to invoice document                 
                //->setJobNo()                                                 
                ->setLineNo($row['line_no'])                                     
                //->setLineType()                                              
                //->setLocalVatValue()                                         
                //->setMarkupPercent()                                         
                //->setMeasure()                                               
                //->setMeasureDescription()                                    
                //->setMinLevel()                                              
                //->setNoDiscount()                                            
                //->setOnOrder()                                               
                ->setOrdered($documentQty)                                     
                //->setPack()                                                  
                //->setPrincipalInv()                                          
                //->setProductGroupCode()                                      
                //->setProductGroupDescription()                               
                //->setPromotionName()                                         
                //->setQtyShort()                                              
                ->setQuantity($documentQty)                                    
                //->setRedundant()                                             
                //->setReference()                                             
                //->setReferenceNo()                                           
                ->setRevenueAccCode(RhodesConstants21::RevenueAccount)           
                //->setRevenueAccDescription()                                 
                //->setSellingPrice(number_format($row['net_price'], 2, '.', ''))
                //->setSellingPricePer(1)                                      
                //->setSequenceNo()                                            
                //->setSerialNo()                                              
                //->setSourceReferenceNo()                                     
                //->setSourceType()                                            
                //->setStockCategoryCode()                                     
                //->setStockCategoryDescription()                              
                ->setStockCode($productCode)                                   
                //setStockDescription($productDescription)                     
                //->setStockLevel()                                            
                //->setStockType()                                             
                //->setTaxType('Exclusive')                                    
                //->setToCredit()                                              
                //->setToDeliver()                                             
                //->setToInvoice()                                             
                //->setTrackingNo4()                                           
                //->setUnitCostPrice()                                         
                //->setUnitPrice()                                             
                //->setUnitVolume()                                            
                //->setUnitWeight()                                            
                //->setUseGoodStock()                                          
                //->setUseRedundant()                                          
                //->setUseSerialNo()                                           
                //->setUseTrackingNo4()                                        
                //->setVatCode($vatCode)                                       
                ->setVatRate($vatRate)                                         
                //->setVatValue(number_format($row['vat_amount'], 2, '.', ''))     
                //->setWarehouseDescription()                                  
                //->setWarrantyPeriod()                                        
                 ;
          
                //append to order
                $hasDetail     = 'Y';
                $omniOrder->addOrderLine($orderLineItem);
       }
}

if($processErrors == 'N') {
    WriteToOmni($omniOrder, $documentNumber, $orderStore, $setypeStore, $dhStatus='',$seUID) ;
}
OmniErrorReporting(RhodesConstants21::PrincipalID, $row['type_uid'], CTD_EDI);

OmniErrorReporting(RhodesConstants21::PrincipalID, $row['type_uid'], CTD_ADMIN_CLERK);

echo "<br>";
echo "<br>";
echo "Fin Loop";

// ******************************************************************************************************************************
 function WriteToOmni($omniOrder, $documentNumber, $DocUid, $setype, $updateSEstatus="", $seUID) {
 
     global $ROOT; global $PHPFOLDER;
 	
     require_once __DIR__ . "/../../../libs/api/omni/OmniRestAPI.php";
     require_once __DIR__ . "/../../../properties/RhodesConstants21.php";
 	
     include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");
     include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');

     if (!isset($dbConn)) {
        $dbConn = new dbConnect();
        $dbConn->dbConnection();
     }
 	   
 	   $omniApi = new OMNIRestAPI(RhodesConstants21::OmniHostname,
                           RhodesConstants21::OmniUsername,
                           RhodesConstants21::OmniPassword,
                           RhodesConstants21::OmniLiveCompany);
     // debug request
     // print_r($omniOrder->getJSON());
     $response = $omniApi->CreateSalesOrder($omniOrder, $documentNumber, $DocUid);
     $responseOrderNo = $response->getBody();  //only the order number comes back and not in JSON format!
    
    if (!$response->getSuccess() || $response->getSuccess() && $responseOrderNo != $documentNumber) {

         $errMessage = $response->getErrorMessage();
         echo '<br>';
         echo '<pre>';
         echo 'Error Msg';
         echo '<br>';
         print_r($errMessage);
         echo '<br>';         
         
         
         if(strpos(strtolower($errMessage), "already exists") !== false){
             echo "[OMNI] Duplicate Order, Ignoring error: " . $errMessage . "\n<br>";
             $updateSEstatus = FLAG_STATUS_CLOSED;
             $statusMsg = "";
             $updateGeneral1 = "SUCCESS";
             $general2       = "[OMNI] Duplicate Order, Ignoring error: \n <br>";
         } else {
             $errorKey = "[OMNI API]: " .$errMessage;
             echo "[OMNI] Error Creating Order: " . $errorKey . "\n <br>";
             $updateSEstatus = FLAG_STATUS_ERROR;
             $statusMsg = $errorKey;
             $updateGeneral1 = "[OMNI] Order Rejected - Omni Error: ";
             // dump full response here...
//       var_dump($response);	
         }
    } else {
        echo "[OMNI] Successfully Created Order:" . $responseOrderNo;
        
        $updateSEstatus = FLAG_STATUS_CLOSED;
        $statusMsg = "[OMNI] Successfully Created Order:" . $responseOrderNo;;
        $updateGeneral1 = "SUCCESS";
        
        echo "<h1>Fetching Order: {$documentNumber}</h1>";
        // Verify Order 
/*        
        $createdOrder = $omniApi->GetSalesOrder($documentNumber);

        if($createdOrder->getResponse()->getSuccess()){

            if($omniOrder->getDocumentDate() != $createdOrder->getDocumentDate()){
                $general2   = 'WARNINGS';
                $statusMsg  = "Document Date Mismatch (" . $omniOrder->getDocumentDate() . "/" . $createdOrder->getDocumentDate() . ")";
            }

            if($omniOrder->getCustomerAccountCode() != $createdOrder->getCustomerAccountCode()){
                $general2   = 'WARNINGS';
                $statusMsg  = "Customer Code Mismatch (" . $omniOrder->getCustomerAccountCode() . "/" . $createdOrder->getCustomerAccountCode() . ")";
            }

            //check every line
            foreach($omniOrder->getOrderLines() as  $k => $line){

                $createdLine = $createdOrder->getOrderLines()[$k] ?? null;
                if(!$createdLine){
                    $general2   = 'WARNINGS';
                    $statusMsg  = "Missing Line: " . $k+1 . " - " . $line->getLineNo();
                    continue;
                }
                if($line->getExtPriceIncl() != $createdLine->getExtPriceIncl()){
                    $general2   = 'WARNINGS';
                    $statusMsg  = "ExtPriceIncl Mismatch (" . $line->getExtPriceIncl() . "/" . $createdLine->getExtPriceIncl() . ")";

                }
            }
        } else {
            $warnings[] = $createdOrder->getResponse()->getErrorMessage();
        }
*/        
    }
    
    // Update Smart event and Document Details
    
    $setOmniImportAll = new PostBIDAO($dbConn);
    $mTResult = $setOmniImportAll->setSmartEventStatusIndividual($seUID, $updateGeneral1, $general2 = "", $statusMsg , $updateSEstatus);
    
    if($updateSEstatus == FLAG_STATUS_CLOSED ) {
           $setOmniImportAll = new PostBIDAO($dbConn);
           $mTResult = $setOmniImportAll->setOmniUpdateTrans($DocUid, $setype, RhodesConstants21::ConfirmEndStatus);
    }
    
    
}
// ******************************************************************************************************************************
 function OmniErrorReporting($principalUId, $seType, $nType) {
 	
     global $ROOT; global $PHPFOLDER;
     
     include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
     include_once($ROOT . $PHPFOLDER . "DAO/messagingDAO.php");
     include_once($ROOT . $PHPFOLDER . "DAO/postDistributionDAO.php");
     include_once($ROOT . $PHPFOLDER . "TO/PostingDistributionTO.php");
     include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");
     
     if (!isset($dbConn)) {
        $dbConn = new dbConnect();
        $dbConn->dbConnection();
     }	
 	
     $messagingDAO = new messagingDAO($dbConn);
     $errLST = $messagingDAO->getOmniErrorNotificationRecipients($principalUId, $seType, $nType) ;
     
 // print_r($errLST);
     
     if (count($errLST) > 0) {
    	
          $storeString = '';
          $bodyString = '';
    
          $c= 0;

          foreach($errLST as $elRow) {    	    	
              if($storeString <> trim($elRow['email_addr'])  ) {
                   if($storeString <> '') {
                         $messagingDAO = new messagingDAO($dbConn);
                         $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorend($elRow['Principal']);              	    	
                         $postingDistributionTO->body = $bodyString;
                         $postDistributionDAO = new postDistributionDAO($dbConn);
                         $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
                   }              	
                    // Set up new distribution TO
                    $postingDistributionTO = new PostingDistributionTO;
                    $postingDistributionTO->DMLType = "INSERT";
                    $postingDistributionTO->deliveryType = BT_EMAIL;
                    
                    $messagingDAO = new messagingDAO($dbConn);
                    $postingDistributionTO->subject = $messagingDAO->getTemplateOmniImportErrorSubject(trim($elRow['Principal'])); 
                    $postingDistributionTO->destinationAddr =  trim($elRow["email_addr"]); 
                    $storeString = trim($elRow['email_addr']);
                   
                    $messagingDAO = new messagingDAO($dbConn);
                    $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorHeader($elRow['Principal']);
              }    	  	 
              $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorBody($elRow['document_number'], 
                                                                                  $elRow['invoice_date'], 
                                                                                  trim($elRow['WhAbr']) . ' - ' .$elRow['deliver_name'], 
                                                                                  $elRow['status_msg'], 
                                                                                  $elRow['dataUid'], 
                                                                                  $elRow['psm.uid'], 
                                                                                  $elRow['type']);
                                                                                  
              // Update error count here 
              
              $setSeErrorCount = new PostBIDAO($dbConn);
              $mTResult = $setSeErrorCount->setSeErrorCount($elRow['se_uid']);
              
          }
          $messagingDAO = new messagingDAO($dbConn);
          $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorend($elRow['Principal']);
          $postingDistributionTO->body = $bodyString;
          $postDistributionDAO = new postDistributionDAO($dbConn);
          $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
     }
     $dbConn->dbinsQuery("commit;");
      
 }

 
// ******************************************************************************************************************************

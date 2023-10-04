<?php
// Naomi 0637772326
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/dearsystem/post_royalsalt_sales_invoice.php
// Information on DEAR API: 
// https://support.dearsystems.com/support/solutions/1000084084

/* * ********************************************************************************************
 * *
 * *  Example - Create Sale, Order, Invoice, Credit Note
 * *
 * *****************ss*************************************************************************** */

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
include_once($ROOT . $PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER."DAO/DearSystemDAO.php");
include_once($ROOT . $PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER.'DAO/ExtractDAO.php');	
include_once($ROOT . $PHPFOLDER. "DAO/BIDAO.php");
include_once($ROOT . $PHPFOLDER. "DAO/postBIDAO.php");
include_once($ROOT . $PHPFOLDER.'DAO/MiscellaneousDAO.php'); 
include_once($ROOT . $PHPFOLDER.'properties/Constants.php'); 

    
require_once __DIR__ . "/../../../libs/api/dearsystems/DearRestAPI.php";

require_once __DIR__ . "/../../../properties/RoyalSaltConstants.php";

//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;

$apiBaseUri     = RoyalSaltConstants::DearHostname;
$accountId      = RoyalSaltConstants::DearUsername;
$applicationKey = RoyalSaltConstants::DearPassword;
$PrincipalID    = RoyalSaltConstants::PrincipalID;
$NotifyId       = RoyalSaltConstants::extractType;
/*-------------------------------------------------*/
/*  FETCH Notification Recipients ??
/*-------------------------------------------------*/
//use the receipients listed in the notification table instead of hard coding them!!!
//expecting only one row loaded per principal extract
$reArr = (new BIDAO($dbConn))->getNotificationRecipients(RoyalSaltConstants::PrincipalID, NT_DAILY_EXTRACT_CUSTOM);
if (count($reArr) == 0) {
    BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in " . __FILE__, "Y");
    exit;
}
$recipientUId = $reArr[0]['uid'];

/*-------------------------------------------------*/
/*  FETCH ORDERS THAT NEED TO BE SENT.
/*-------------------------------------------------*/
echo "Fetching getDailyExtractInvoicedOrders";
echo "<br>"; 

$seDocs = (new DearSystemDAO($dbConn))->getOrdersForDear($PrincipalID, $NotifyId, '');

if (!is_array($seDocs) || is_array($seDocs) && count($seDocs) == 0) {
    echo "Successful --> No outstanding orders found!";
    $errorTO = new ErrorTO();
    $errorTO->type = FLAG_ERRORTO_SUCCESS;  //preset.
    $errorTO->object = array();
    return($errorTO);
}

echo "Found: " . count($seDocs) . " order lines";
echo "<br>"; 

$orderStore = '';
echo "<h1>Create Sale</h1>";
echo "<br>";

$header = 'T';
$processErrors = 'N';
$createSale = 'Y';

/*--------------------------------------------
 *  STEP 1 : CREATE A SALE
 *-----------------------------------------*/ 

     foreach($seDocs as $orow) {
       
           if($orderStore <> '' && $orderStore <> $orow['document_number']) {
               echo "Break end of order";
               echo "<br>"; 
               break;
           }
           // Process Header
           if($header == 'T') {
               $storeSpecialArr       = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesMultEntities(RoyalSaltConstants::PrincipalID, 555, $orow['psmUid'], CT_STORE_SHORTCODE, $arrayIndex = "entity_uid");
               $royalBranchSpecialArr = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesMultEntities(RoyalSaltConstants::PrincipalID, 556, $orow['psmUid'], CT_STORE_SHORTCODE, $arrayIndex = "entity_uid");
	  	
               if (empty($storeSpecialArr[$orow['psmUid']]['value'])) {  //has no special field and/or blank...
                       echo "Missing Special Field for Store:" . $orow['psmUid'] . $row['psmUid'] . '-' . $orow['smartUid'] . "\n<br>";
                       $updateSEstatus = FLAG_STATUS_ERROR;
                       $updateGeneral1 = '555';
                       $general2       = '';
                       $statusMsg  = "[KOS] Missing RoyalSalt SPF for store:   <br>";                   
                       $setOmniImportAll = new PostBIDAO($dbConn);
                       $mTResult = $setOmniImportAll->setSmartEventStatusIndividual($orow['smartUid'], $updateGeneral1, $general2 , $statusMsg , $updateSEstatus );
                       $processErrors = 'Y';
                       echo "Return Account Error";
                       echo "<br>";
               }
               $storeSpecialValue = $storeSpecialArr[$orow['psmUid']]['value'];
    
               if (empty($royalBranchSpecialArr[$orow['psmUid']]['value'])) {  //has no special field and/or blank...
                       echo "Missing Special Field for Location:" . $row['psmUid'] . '-' . $orow['smartUid'] ."\n<br>";
                       $updateSEstatus = FLAG_STATUS_ERROR;
                       $updateGeneral1 = '556';
                       $general2       = '';
                       $statusMsg  = "[KOS] Missing RoyalSalt spf for location:   <br>";                   
                       $setOmniImportAll = new PostBIDAO($dbConn);
                       $mTResult = $setOmniImportAll->setSmartEventStatusIndividual($orow['smartUid'], $updateGeneral1, $general2 , $statusMsg , $updateSEstatus );
                       $processErrors = 'Y';
                       echo "Return Account Error";
                       echo "<br>"; 
               }
               $storeBranchSpecialValue = $royalBranchSpecialArr[$orow['psmUid']]['value'];         
               // Build Output Object
               
               if($processErrors == 'Y') {
                   echo "Send Special Field Errors";
                   echo "<br>";
                   exit;
               }
               
               // Check if Sale document already exists
               
               $docUid = trim($orow['dm_uid']);
               $saleExists = (new DearSystemDAO($dbConn))->checkForExistingSale(trim($orow['dm_uid']), '');
               
               if(count($saleExists) == 0) {
               
                    $documentNumber = ltrim($orow['document_number'],'0');
                    $invoiceNumber = ltrim($orow['invoice_number'],'0');
                    
                    $customerOrderNumber = $orow['customer_order_number'];
                    $invoiceDate = $orow['invoice_date'];
                    $invoiceDate = $invoiceDate != "0000-00-00" ? $invoiceDate : null;
    
                    $deliverName = substr($orow["deliver_name"],0,30);
                    $deliverAdd1 = substr($orow["deliver_add1"],0,30);
                    $deliverAdd2 = substr($orow["deliver_add2"],0,30);
                    $deliverAdd3 = substr($orow["deliver_add3"],0,30);
                    $billName    = substr($orow["bill_name"],0,30);
                    $billAdd1 = substr($orow["bill_add1"],0,30);
                    $billAdd2 = substr($orow["bill_add2"],0,30);
                    $billAdd3 = substr($orow["bill_add3"],0,30);
                    $buyeraccountreference = trim($orow["buyer_account_reference"]);
                    $depot    = trim($orow["depot_name"]);
                    
                    if ($orow['vat_rate'] == '0.00') {
                         $vatCode = 'Standard Rate Sales';
                    } else {
                         $vatCode = 'Standard Rate Sales';	        	
                    }
             
                    //construct the object for a new order
                    
                    echo "Location Value " . $storeBranchSpecialValue;
                    echo "<br>";
              
                    $sale = (new DearSaleObj());
                    $sale->setAdditionalAttributes(["AdditionalAttribute10" => $invoiceNumber]);       // Invoice Number
                    // $sale->setAutoPickPackShipMode();
                    $sale->setBillingAddress(
                                             (new DearSaleBillingAddressObj())
                                              ->setLine1($deliverName)
                                              ->setLine2($billAdd1)
                                              ->setCity($billAdd3 )
                                              ->setPostcode("")
                                              ->setState("")
                                              ->setCountry("ZAF")
                                             );
                    // $sale->setCarrier();
                    // $sale->setContact();
                    $sale->setCurrencyRate(1);
                    $sale->setCustomer($storeSpecialValue);
                    // $sale->setCustomerID();
                    $sale->setCustomerReference($customerOrderNumber);     //PO Number
                    // $sale->setDefaultAccount();
                    // $sale->setEmail();
                    $sale->setLocation($storeBranchSpecialValue); //also looked up!
                    // $sale->setNote();
                    // $sale->setPhone();
                    // $sale->setPriceTier();
                    // $sale->setSaleOrderDate();
                    $sale->setSalesRepresentative($depot);
                    // $sale->setShipBy();
                    $sale->setShippingAddress(
                                              (new DearSaleShippingAddressObj())
                                               ->setLine1($deliverName)
                                               ->setLine2($billAdd1)
                                               ->setCity($billAdd2)
                                               ->setState($billAdd3)
                                               ->setCountry("ZAF")
                                               ->setContact("")
                                               ->setPostcode("")
                                               // ->setCompany()
                                              ->setShipToOther(true)
                                             )     ;
          
                    // $sale->setShippingNotes();
                    // $sale->setSkipQuote();
                    // $sale->setTaxInclusive();
                    $sale->setTaxRule($vatCode);
                    // $sale->setTerms();
                    $header = 'F';
               } else {
                   $createSale = 'N';
                   echo "<br>";
                   echo "Create Sale Skipped";
                   echo "<br>";
                   break;          	
               } 
           }     
           $orderStore = $orow['document_number'];
     }
     // After Loop Call APIDAO
     if($createSale == 'Y') {
           echo "<br>";	
           echo "<pre>"; 
           'debug request';
            var_dump(json_encode($sale->getArray(), JSON_PRETTY_PRINT));
           
           //construct api client
           $api = new DEARRestAPI($apiBaseUri, $accountId, $applicationKey);
         
           //submit to api
           $saleResponse = $api->CreateSale($sale);         
           if (!$saleResponse->getSuccess()) {
                echo "Create Sale Error!";
                var_dump($saleResponse->getBody());          
                echo "API Error   " . $orow['invoice_number'] . "\n<br>";
                $updateSEstatus = FLAG_STATUS_ERROR;
                $updateGeneral1 = 'API Error - Create Sale';
                $general2       = '';
                $statusMsg  = $eArray['0']['Exception'];             
                $setOmniImportAll = new PostBIDAO($dbConn);
                $mTResult = $setOmniImportAll->setSmartEventStatusIndividual($orow['smartUid'], $updateGeneral1, $general2 , $statusMsg , $updateSEstatus );
                $processErrors = 'Y';
                echo "Return API Error";
                echo "<br>"; 
                exit;
           } else {
                $saleId = $saleResponse->getBody()['ID'];
                $saleOrderNumber = $saleResponse->getBody()['Order']['SaleOrderNumber'];
                $saleInvoiceTaskID = $saleResponse->getBody()['Invoices'][0]['TaskID'];
                $saleInvoiceNumber = $saleResponse->getBody()['Invoices'][0]['InvoiceNumber'];
         
                //var_dump($saleResponse->getBody());
           
                // Insert into dear_systems_sales
           
                $errorTO = (new DearSystemDAO($dbConn))->insertSale($docUid, 
                                                                    $saleId, 
                                                                    $saleOrderNumber, 
                                                                    $saleInvoiceTaskID,
                                                                    $saleInvoiceNumber,
                                                                    SALECREATED);
         
                print_r("saleId: " . $saleId . PHP_EOL);
                print_r("saleOrderNumber: " . $saleOrderNumber . PHP_EOL);
           }     
     } 
/*--------------------------------------------
 *  END OF STEP 1 : CREATE A SALE
 *-----------------------------------------*/           

/*--------------------------------------------
 *  STEP 2 : CREATE A SALES ORDER
 *-----------------------------------------*/
 
    echo "<br>"; 
    echo "<h1>Create Order</h1>";
    echo "<br>";
    
    $orDocs = (new DearSystemDAO($dbConn))->getOrdersForDear($PrincipalID, $NotifyId, $docUid); 

    $orderStoreC = '';
    $createOrder = 'Y';
    $header = 'T';
    foreach($orDocs as $orow) {	
        if($orderStoreC <> '' && $orderStoreC <> $orow['document_number']) {
	            echo "Break end of order";
              echo "<br>"; 
              break;
        }
     	  // Get Sale Details
         
        $saleOrder = (new DearSystemDAO($dbConn))->checkForExistingSale($docUid, SALECREATED);
          
        if(count($saleOrder) == 1) {

              // Process Header
              if($header == 'T') {
            	
            	    //construct the object for a new order
                  $order = (new DearSalesOrderObj())
                            ->setSaleID($saleOrder[0]['sale_id'])
                            ->setSaleOrderNumber($saleOrder[0]['sale_order_number'])
                            //->setMemo()
                            ->setStatus("AUTHORISED");
                            //->setTax()
                            //->setTotal()
                            //->setTotalBeforeTax();
                  
                  $header = 'F';
               }
               if($orow['document_status_uid'] == 47) {
                    $documentQty = 1;
                    $nettPrice   = (number_format($orow['net_price'], 2, '.', ''));
                    $ExtPrice    = (number_format(($documentQty*$nettPrice), 2, '.', ''));		               	
               } else {
                   $documentQty = abs($orow['document_qty']);
                   $nettPrice   = (number_format($orow['net_price'], 2, '.', ''));
                   $ExtPrice    = (number_format($orow['extended_price'], 2, '.', ''));
               } 		
               $productGuid = $orow['product_guid'];
               $productCode = trim(str_replace(['"'], [''], $orow['product_code']));
               $productDescription = trim($orow['product_description']);
      
               if ($orow['vat_rate'] == '0.00') {
                    $vatCode = 'Standard Rate Sales';
               } else {
                    $vatCode = 'Standard Rate Sales';	        	
               }
               
               if($documentQty <> 0) {
                          //add multi lines to the order here
                          $line1 = (new DearSalesOrderLineItemObj())
                                   ->setProductID($productGuid)
                                   ->setName($productDescription)
                                  ->setSKU($productCode)
                                  ->setQuantity($documentQty)
                                  ->setPrice($nettPrice)
                                  ->setDiscount(0)
                                  ->setTax(15.00)
                                  ->setTaxRule($vatCode)
                                  ->setComment("")
                                  ->setTotal($ExtPrice);
     
                                  //add the line to the order object
                                  $order->addLine($line1);
                                  $order->setTotal($ExtPrice);
               	
               } else {
               	        continue;
               }

        } else {
             echo "<br>";
             echo "Create Order Skipped - ";
             echo "<br>";
             $createOrder = 'N';
             break;
        }
        $orderStoreC = $orow['document_number'];
    }          
    if($createOrder == 'Y') {
         //debug request
         var_dump(json_encode($order->getArray(), JSON_PRETTY_PRINT));
         
         $api = new DEARRestAPI($apiBaseUri, $accountId, $applicationKey);
         
         $orderResponse = $api->CreateSalesOrder($order);
     
         if (!$orderResponse->getSuccess()) {
              echo "Create Sale Order Error!";
              var_dump($orderResponse->getBody());
              echo "API Error   " . $orow['invoice_number'] . "\n<br>";
              $updateSEstatus = FLAG_STATUS_ERROR;
              $updateGeneral1 = 'API Error Create Order';
              $general2       = '';
              $statusMsg  = $eArray['0']['Exception'];             
              $setOmniImportAll = new PostBIDAO($dbConn);
              $mTResult = $setOmniImportAll->setSmartEventStatusIndividual($orow['smartUid'], $updateGeneral1, $general2 , $statusMsg , $updateSEstatus );
              $processErrors = 'Y';
              echo "Return API Error";
              echo "<br>"; 
              exit;
        } else {
             var_dump($orderResponse->getSuccess());
             //var_dump($orderResponse->getBody());
     
             // Update dear_systems_sales
             $errorTO = (new DearSystemDAO($dbConn))->updateSale($saleOrder[0]['uid'], SALEORDERCREATED);     	
        }
    }   
/*--------------------------------------------
 *  STEP 2 : END OF CREATE A SALES ORDER
 *-----------------------------------------*/     

/*--------------------------------------------
 *  STEP 3 : CREATE A SALES INVOICE
 *-----------------------------------------*/

     echo "<br>"; 
     echo "<h1>Create Invoice</h1>";
     echo "<br>";
     
     $inDocs = (new DearSystemDAO($dbConn))->getOrdersForDear($PrincipalID, $NotifyId, $docUid); 
     
     $header = 'T';
     $orderStoreI = '';
     $nextInvoice = '';
      
     foreach($inDocs as $orow) {
     
         	if($orderStoreI <> '' && $orderStoreI <> $orow['document_number']) {
         		
               echo "Break end of Invoice";
               echo "<br>"; 
               break;
     	    }
     	
          $saleInv = (new DearSystemDAO($dbConn))->checkForExistingSale($docUid, SALEORDERCREATED);
          
          if(count($saleInv) == 1) {     	
                  // Process Header
                 if($header == 'T') {
                 	
                      $invoiceDate = $orow['invoice_date'];
     			
                      //construct the object for a new order
                      $invoice = (new DearSalesInvoiceObj())
                                  ->setSaleID($saleInv[0]['sale_id'])
                                  ->setTaskID($saleInv[0]['sale_invoice_task_id'])
                                  ->setInvoiceDate($invoiceDate)
                                  ->setInvoiceDueDate($invoiceDate)
                                  ->setStatus("AUTHORISED")
                                  //->setBillingAddressLine1()
                                  //->setBillingAddressLine2()
                                  //->setCombineAdditionalCharges()
                                  ->setCurrencyConversionRate(1)
                                  //->setLinkedFulfillmentNumber(1)
                                  //->setMemo() 
                                  ;		
                                  $header = 'F';
                 }
                 
                 $orderStoreI = $orow['document_number'];
                 
                 if($orow['document_status_uid'] == 47) {
                    $documentQty = 1;
                    $nettPrice   = (number_format($orow['net_price'], 2, '.', ''));
                    $ExtPrice    = (number_format(($documentQty*$nettPrice), 2, '.', ''));		               	
                 } else {
                   $documentQty = abs($orow['document_qty']);
                   $nettPrice   = (number_format($orow['net_price'], 2, '.', ''));
                   $ExtPrice    = (number_format($orow['extended_price'], 2, '.', ''));
                 }		
                 $productGuid = $orow['product_guid'];
                 $productCode = trim(str_replace(['"'], [''], $orow['product_code']));
                 $productDescription = trim($orow['product_description']);

            
                 $revAcc      = trim($orow['revenue_account']);  
         
                 if ($orow['vat_rate'] == '0.00') {
                      $vatCode = 'Standard Rate Sales';
                 } else {
                      $vatCode = 'Standard Rate Sales';	        	
                 }
                 //add multi lines to the order here
                 if($documentQty <> 0) {
                      $line1 = (new DearSalesInvoiceLineItemObj())
                                ->setProductID($productGuid)
                                ->setName($productDescription)
                                ->setSKU($productCode)
                                ->setQuantity($documentQty)
                                ->setPrice($nettPrice)
                                ->setDiscount(0)
                                ->setTax(15.00)
                                ->setTaxRule($vatCode)
                                ->setComment("")
                                ->setAccount($revAcc) //<--- another thing we need to store or lookup
                                ->setTotal($ExtPrice);
                                //add the line to the order object
                                $invoice->addLine($line1);
                 } else {
                 	    continue;
                 }               
          } else { 
          	    echo "<br>";
                echo "Sale Order  not found - Problem ";
                echo "<br>";
                exit;
          }
     }                      
         
     //debug request
     echo "<br>";
     echo "<pre>";
     echo "<h1>Created Invoice -  " . $orow['invoice_number'] . "  " . $orow['deliver_name'] . "</h1>";
     echo "<br>";
//     var_dump(json_encode($invoice->getArray(), JSON_PRETTY_PRINT));
     
     file_put_contents($ROOT.$PHPFOLDER.'log/rsjson' . date("ymd") . '.txt', json_encode($invoice->getArray(), JSON_PRETTY_PRINT) , FILE_APPEND);   
 
     $api = new DEARRestAPI($apiBaseUri, $accountId, $applicationKey);      
     $invoiceResponse = $api->CreateSalesInvoice($invoice);
     
     if (!$invoiceResponse->getSuccess()) {
     	      echo "Create Sale Invoice Error!";
           // var_dump($invoiceResponse->getBody());
           
           $eArray = $invoiceResponse->getBody();
           if(strpos($eArray[0]['Exception'],'AUTHORISED',0) <> FALSE) {
                 $updateSEstatus = FLAG_STATUS_CLOSED;
                 $updateGeneral1 = 'Success - Duplicate';
                 $general2       = '';
                 $statusMsg  ='';
                 $setOmniImportAll = new PostBIDAO($dbConn);
                 $mTResult = $setOmniImportAll->setSmartEventStatusIndividual($orow['smartUid'], $updateGeneral1, $general2 , $statusMsg , $updateSEstatus );
                 // Update dear_systems_sales
                 $errorTO = (new DearSystemDAO($dbConn))->updateSale($saleInv[0]['uid'], SALEINVOICED);
                 echo "<br>";
                 echo "Create Sale Invoice Duplicate!";
                 echo "<br>";
           } else {
                 echo "<br>";
                 echo $eArray[0]['Exception'];     
                 echo "<br>";
                 echo "API Error   " . $orow['invoice_number'] . "\n<br>";
                 $updateSEstatus = FLAG_STATUS_ERROR;
                 $updateGeneral1 = 'API Error Create Invoice';
                 $general2       = '';
                 $statusMsg  = $eArray[0]['Exception'];          
                 $setOmniImportAll = new PostBIDAO($dbConn);
                 $mTResult = $setOmniImportAll->setSmartEventStatusIndividual($orow['smartUid'], $updateGeneral1, $general2 , $statusMsg , $updateSEstatus );
                 $processErrors = 'Y';
                 echo "Return API Error";
                 echo "<br>";
           }      
     } else {
           echo "Create Sale Invoice Created!";
           echo "<br>";
           // var_dump($invoiceResponse->getBody());
           $updateSEstatus = FLAG_STATUS_CLOSED;
           $updateGeneral1 = 'Success';
           $general2       = '';
           $statusMsg  ='';             
           $setOmniImportAll = new PostBIDAO($dbConn);
           $mTResult = $setOmniImportAll->setSmartEventStatusIndividual($orow['smartUid'], $updateGeneral1, $general2 , $statusMsg , $updateSEstatus );
     
           // Update dear_systems_sales
           $errorTO = (new DearSystemDAO($dbConn))->updateSale($saleInv[0]['uid'], SALEINVOICED); 
     }

/*--------------------------------------------
 *  STEP 3 : END OF CREATE A SALES INVOICE
 *-----------------------------------------*/


/*--------------------------------------------
 *  STEP 3 : Get Next Invoice Number
 *-----------------------------------------*/

$xtDocs = (new DearSystemDAO($dbConn))->getOrdersForDear($PrincipalID, $NotifyId, '');

foreach($xtDocs as $orow) {

          echo "<br>";
          echo "Next Invoice to be extracted - " .  $orow['invoice_number'] . "  " . $orow['deliver_name'] . "  " . $orow['document_status_uid'];		
          echo "<br>";
          echo "<br>"; 
          break;
}

echo "<br>";
echo "<br>";
echo "<h1>EOS</h1>";
echo "<br>";
echo "<br>"; 


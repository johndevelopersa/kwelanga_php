<?php

/* * ********************************************************************************************
 * *
 * *  LIVE Omni - SALES ORDER PUSH TO OMNI RESTAPI API
 * *
 * *********************************************************************************************** */
 
require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/MiscellaneousDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/OmniExtractDAO.php");

// $principal_uid = isset($_GET["PRINCIPALID"])?$_GET["PRINCIPALID"]:"";

if(trim($principal_uid == '')) {
        echo "<br>";
        echo "[KOS] No Principal Set up for this extract to run";
        echo "<br>";
        echo "Cannot Continue";	
        exit;	
}

require_once($ROOT . $PHPFOLDER . "properties/" . "Omni_Constants_" . $principal_uid . ".php");

require_once __DIR__ . "/../../../libs/api/omni/OmniRestAPI.php";

$constantsClass = "Omni_Constants_" . $principal_uid ;

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

/*---------------------------------------------------------------------------------------------------------------------------*/
/*  FETCH ORDERS THAT NEED TO BE SENT. // Oldest in smart event without without errors first
/*---------------------------------------------------------------------------------------------------------------------------*/

echo "Fetching getDailyExtractInvoicedOrders\n";
$seDocs = (new OmniExtractDAO($dbConn))->getOrdersForOmni($constantsClass::PrincipalID, 
                                                          $constantsClass::extractType,
                                                          '', 
                                                          $constantsClass::activeDepot);

if (!is_array($seDocs) || is_array($seDocs) && count($seDocs) == 0) {
    echo "Successful --> No outstanding orders found!";
    $errorTO = new ErrorTO();
    $errorTO->type = FLAG_ERRORTO_SUCCESS;  //preset.
    $errorTO->object = array();
    return;
}    
    
echo "Found: " . count($seDocs) . " order lines\n";

// Process orders until end of loop

$firstOrderLine = '';
$batchSize      = 1;

$orderSourceArr = explode(',', $constantsClass::orderSource);

$kosError = 'F';

// echo "<pre>";
// print_r($seDocs);

foreach ($seDocs as $serow) {
	     // Collect special fields by order
      if($firstOrderLine <> $serow['dm_uid']) {
   	       if($firstOrderLine <> '') {
   	       	    echo '<br>';
   	       	    echo 'Next Order';
   	       	    echo '<br>';
   	            include_once($ROOT . $PHPFOLDER . 'functional/jobs/omnimports/importProcessOmni.php');
               
                if($constantsClass::finalStatus == DST_INVOICED) {
                       $setIs = (new OmniExtractDAO($dbConn))->setDocumentConfirmationStatus($constantsClass::PrincipalID, DST_ACCEPTED, DST_INVOICED);
                } 
   	            $programComplete = 'Y';
   	            return;
           }
	     	
           // Set up Order Header info
           $specialFieldValue = new MiscellaneousDAO($dbConn);
           $storeSpecialArr   = $specialFieldValue->getSpecialFieldValues($constantsClass::customerSpF,
	                                                                        $serow['psmUid']);	                                                                  
	         if (empty($storeSpecialArr[0]['value'])) {  //has no special field and/or blank...

	              // Update Smart event direcly with error
	              $general1 = "No Store Omni Account - " . ltrim($serow['document_number']);
	              // Update SE - Quit and loop
	              $updateSmartE = new OmniExtractDAO($dbConn);
                $uSE = $updateSmartE->updateSmartEventDirectly($serow['smartUid'], $general1, $general2 = "", $statusFlag = FLAG_ERRORTO_ERROR) ;
                $kosError = 'T';
	              break;  
                
           }	else {
           	    $storeSpecialValue  = $storeSpecialArr[0]['value'] ;
           }     
      
           $specialFieldValue = new MiscellaneousDAO($dbConn);
           $depotSpecialArr   = $specialFieldValue->getSpecialFieldValues($constantsClass::depotSpf,
	                                                                        $serow['depotUid']);
           if (empty($depotSpecialArr[0]['value'])) {  //has no special field and/or blank...
	              $general1 = "Omni Warehouse Unknown - " . ltrim($serow['document_number']);
	              // Update SE - Quit and loop
	              $updateSmartE = new OmniExtractDAO($dbConn);
                $uSE = $updateSmartE->updateSmartEventDirectly($serow['smartUid'], $general1, $general2 = "", $statusFlag = FLAG_ERRORTO_ERROR);
	              $kosError = 'T';
	              break;   
           }	else {
           	    $depotSpecialValue = $depotSpecialArr[0]['value'];
           	    echo "<br>";
	              echo $depotSpecialValue;
	              echo "<br>";  
           }
           if(in_array($serow["alt_principal_chain_uid"], $orderSourceArr)) {
           	
           	     if($serow["alt_principal_chain_uid"] == '3233') {
                     $documentNumber =  $constantsClass::docNoPrefix . ltrim($serow['document_number'],'0');
                     if(ltrim($serow['client_document_number']) == '') {
                          $clientDocNumber = $constantsClass::docNoPrefix . ltrim($serow['document_number'],'0');
                     } else {
                 	        $clientDocNumber = $constantsClass::docNoPrefix . ltrim($serow['client_document_number'],'0');
                     }    	
           	     } else {
                       $documentNumber =  ltrim($serow['document_number']);
                       if(ltrim($serow['client_document_number']) == '') {
                           $clientDocNumber = ltrim($serow['document_number']);
                       } else {
                           $clientDocNumber = ltrim($serow['client_document_number']);
                       }
                 }                          
           } else {
                 $documentNumber =  $constantsClass::docNoPrefix . ltrim($serow['document_number'],'0');
                 if(ltrim($serow['client_document_number']) == '') {
                      $clientDocNumber = $constantsClass::docNoPrefix . ltrim($serow['document_number'],'0');
                 } else {
                 	    $clientDocNumber = $constantsClass::docNoPrefix . ltrim($serow['client_document_number'],'0');
                 }                         
           }
           
           echo $serow["alt_principal_chain_uid"];
           echo "<br>";
           
           if(trim($serow['invoice_number']) == '') {
                   if(in_array($serow["alt_principal_chain_uid"], $orderSourceArr)) {
                   	   if($serow["alt_principal_chain_uid"] == '3233') {
                   	        $invoiceNumber = $constantsClass::docNoPrefix . ltrim($serow['document_number'],'0');	
                   	   } else {
                   	        $invoiceNumber =  ltrim($serow['document_number']);	
                   	   }
                   } else {
                       $invoiceNumber = $constantsClass::docNoPrefix . ltrim($serow['document_number'],'0');
                   }    
           } else {
                  if(in_array($serow["alt_principal_chain_uid"], $orderSourceArr)) {
                   	   if($serow["alt_principal_chain_uid"] == '3233') {
                   	        $invoiceNumber = $constantsClass::docNoPrefix . ltrim($serow['document_number'],'0');	
                   	   } else {
                   	        $invoiceNumber =  ltrim($serow['document_number']);	
                   	   }
                  } else {
                       $invoiceNumber = $constantsClass::docNoPrefix . ltrim($serow['document_number'],'0');
                  }
           }
           
           if($constantsClass::documentDate == 1){
                $docDate = $serow['processed_date']; 		
           } elseif($constantsClass::documentDate == 2) {
           	    $docDate = $serow['order_date']; 	
           } elseif($constantsClass::documentDate == 3) {
                $docDate = date('Y-m-d');
           } else {
                $docDate = $serow['invoice_date']; 	
           }
           
           if($constantsClass::analysis_4 == 'CAPTUREDBY'){
                $analysis4 = trim($serow['username']) .' - ' . trim($serow['captured_by']); 
           } elseif($constantsClass::analysis_4 == 'XX') {
           	    $analysis4 = $storeSpecialValue; 	
           } else {
                $analysis4 = $storeSpecialValue; 	
           }
           
           $branchCode = $serow['branch_code'];   
           $customerOrderNumber = $serow['customer_order_number'];
           $deliveryDay = $serow['delivery_date'];
           $deliveryDay = $deliveryDay != "0000-00-00" ? $deliveryDay : null;
           $deliveryDetails = $serow['invoice_number'];    
           $invoiceDate = $docDate;
           $invoiceDate = $invoiceDate != "0000-00-00" ? $invoiceDate : null;
           $dueDeliveryDate = $serow['requested_delivery_date'];
           $dueDeliveryDate = $dueDeliveryDate != "0000-00-00" ? $dueDeliveryDate : null;
           $incomingFile = substr($serow['incoming_file'],0,30);
           $deliverName = substr($serow["deliver_name"],0,30);
           $buyeraccountreference = trim($serow["buyer_account_reference"]);
           $documentStatus = $serow['document_status_uid'];
           
           $gdsPrin = $constantsClass::revenueAccount;
           $RevenueAccount = $constantsClass::gdsPrincipal;
           
           $firstOrderLine = $serow['dm_uid'];
           $seOrderLineStore = $serow['smartUid'];
           
           $batchSize++;
           
           echo "Posting. "       . $serow["dm_uid"] . ":- Doc:" . $documentNumber . "<br>" . 
                "Cust Order No: " . $serow["customer_order_number"] . "<br>" .
                "Depot:"          . $serow["depot_name"]   ." / ". $depotSpecialValue . "<br>" .
                "Store:"          . $serow["deliver_name"] ." / ". $storeSpecialValue . "\n<br>" .
                "End of Parms";
                
          /*-------------------------------------------------------------
           *      CREATE ORDER OBJECT
           *-----------------------------------------------------------*/
           $omniOrder = (new OmniSalesOrderObj)
                  ->setAnalysis4($analysis4)	//###                  
                  //->setAreaCode()
                  ->setBranchCode($branchCode)
                  //->setCapturedBy()
                  ->setCustomerAccountCode($storeSpecialValue)
                  ->setCustomerOrderNo($customerOrderNumber)
                  ->setDeliveryDay($deliveryDay)
                  ->setDeliveryDetails($documentNumber)  //  *************
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
                  ->setPrinciple("KOS")
                  ->setRepCode("")
                  ->setRevenueAccCode($constantsClass::revenueAccount)
                  //->setSourceReference($sourceDocumentNumber)
                  //->setSourceType($dataSource)
                  //->setStoreName($deliverName)
                  //->setVatRegistrationNo($buyeraccountreference)
                  ->setWarehouseCode($depotSpecialValue);
                  //->setAnalysis4();
          /*-------------------------------------------------------------
           *      ORDER
           *------------------------------------------------------------ */

      }  // eo Order Header
       
      if($documentStatus == DST_INVOICED) {
            $documentQty = abs($serow['document_qty']);	
      } else {
            $documentQty = abs($serow['ordered_qty']);
      }
      if($serow['alt_code'] == NULL) {
            $productCode = trim(str_replace(['"'], [''], $serow['product_code']));
      } else {
            $productCode = trim(str_replace(['"'], [''], $serow['alt_code']));
      }        
      $productDescription = substr(trim(str_replace(['"', '\\', "\t", "\n", "\r"], ['', '', '', '', ''], $serow['product_description'])),0,30);
 
      if ($serow['vat_rate'] == '0.00') {
           $vatCode = '2';
      } else {
           $vatCode = '1';	        	
      }

        if(in_array($serow["alt_principal_chain_uid"], $orderSourceArr)) {
               $ExtDiscountValue = (number_format($documentQty * $serow['discount_value'], 2, '.', ''));
               $ExtPrice         = (number_format($serow['extended_price'], 2, '', ''));
               $ExtPriceExcl     = (number_format($serow['extended_price'], 2, '.', ''));
               $ExtPriceIncl     = (number_format($serow['total'], 2, '.', ''));            	
        } else {
               $ExtDiscountValue = NULL;
               $ExtPrice         = NULL;
               $ExtPriceExcl     = NULL;
               $ExtPriceIncl     = NULL;   	
        }

      
      if(in_array($serow["alt_principal_chain_uid"], $orderSourceArr)) {
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
              ->setExtDiscountValue($ExtDiscountValue)
              ->setExtPrice($ExtPrice)
              ->setExtPriceExcl($ExtPriceExcl)
              ->setExtPriceIncl($ExtPriceIncl)            	
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
            ->setLineNo($serow['line_no'])
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
            ->setRevenueAccCode($constantsClass::revenueAccount)
            //->setRevenueAccDescription()
            ->setSellingPrice(number_format($serow['net_price'], 2, '.', ''))
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
            //->setVatRate(number_format($d['vat_rate'], 2, '.', ''))
            //->setVatValue(number_format($d['vat_amount'], 2, '.', ''))
            //->setWarehouse()
            //->setWarehouseDescription()
            //->setWarrantyPeriod()
             ;
          
            //append to order
            $omniOrder->addOrderLine($orderLineItem);
       } else {
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
            ->setLineNo($serow['line_no'])
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
            ->setRevenueAccCode($constantsClass::revenueAccount)
            //->setRevenueAccDescription()
            //->setSellingPrice(number_format($d['net_price'], 2, '.', ''))
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
            //->setVatRate(number_format($d['vat_rate'], 2, '.', ''))
            //->setVatValue(number_format($d['vat_amount'], 2, '.', ''))
            //->setWarehouse()
            //->setWarehouseDescription()
            //->setWarrantyPeriod()
             ;
          
            //append to order
            $omniOrder->addOrderLine($orderLineItem);       	
       }
       

}

if($kosError == 'F') {
      include_once($ROOT . $PHPFOLDER . 'functional/jobs/omnimports/importProcessOmni.php');
      
      if($constantsClass::finalStatus == DST_INVOICED) {
          $setIs = (new OmniExtractDAO($dbConn))->setDocumentConfirmationStatus($constantsClass::PrincipalID, DST_ACCEPTED, DST_INVOICED);
      } 
} else {
        echo "<br>";
        echo "[KOS] Unsuccessful - Order Not Created - " . $general1;
        echo "<br>";
        echo "End of Process";
}

$programComplete = 'Y';

/*   
Notes By principal on Set up

Rolling Chicken

Order source

1. Capture 

      Start Status - Unaccepted
      After Omni Import - Accepted
      
      Sent with no pricing
      
      Invoice printed from Omni
      
2. Pastel Imported Orders

      Start Status - Unaccepted
      After Omni Import - Accepted
      
      Sent with pricing
      
      Set document Status to Invoiced after successful import
      
      Invoice mailed from Kwelanga


*/ 
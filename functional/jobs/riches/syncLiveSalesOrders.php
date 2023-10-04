<?php

/* * ********************************************************************************************
 * *
 * *  LIVE RICHES - SALES ORDER PUSH TO OMNI RESTAPI API
 * *
 * *****************ss*************************************************************************** */

require_once __DIR__ . "/../../../libs/api/omni/OmniRestAPI.php";
require_once __DIR__ . "/RichesConstants.php";

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php";
require_once $ROOT . $PHPFOLDER . 'properties/Constants.php';
require_once $ROOT . $PHPFOLDER . 'DAO/ExtractDAO.php';
include_once($ROOT . $PHPFOLDER . 'DAO/BIDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostBIDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostExtractDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/MiscellaneousDAO.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");

set_time_limit(15*60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}

echo "<pre style='font-size:14px;'>";
echo str_repeat("-", 75) . "\n";
echo "RICHES LIVE SALES ORDERS SYNC PROCESSOR\n";
echo str_repeat("-", 75) . "\n";


/*-------------------------------------------------*/
/*  FETCH Notification Recipients ??
/*-------------------------------------------------*/
//use the receipients listed in the notification table instead of hard coding them!!!
//expecting only one row loaded per principal extract
$reArr = (new BIDAO($dbConn))->getNotificationRecipients(RichesConstants::PrincipalID, NT_DAILY_EXTRACT_CUSTOM);
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
    DT_ORDINV,
    DT_ORDINV_ZERO_PRICE,
];
$documentStatusArr = [
    DST_UNACCEPTED
];

$rTO = (new PostExtractDAO($dbConn))->queueAllInvoiced(RichesConstants::PrincipalID,
    $recipientUId,
    $inclCancelled = false,
    $documentTypeArr,
    $documentStatusArr,
    $fromInvDate='2019-12-05',
    $toInvDate=false,
    $chainUIdIn=2728,
    $dataSource=false,
    $capturedBy=false,
    $depotUId = 343,
    $altChainUIdIn=9999);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
    BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in " . __FILE__ . " ERROR:" . $rTO->description, "Y");
} else {
    $dbConn->dbinsQuery("commit;");
}


/*-------------------------------------------------*/
/*  FETCH ORDERS THAT NEED TO BE SENT.
/*-------------------------------------------------*/
echo "Fetching getDailyExtractInvoicedOrders\n";
$seDocs = (new ExtractDAO($dbConn))->getDailyExtractInvoicedOrders(RichesConstants::PrincipalID, $recipientUId);

if (!is_array($seDocs) || is_array($seDocs) && count($seDocs) == 0) {
    echo "Successful --> No outstanding orders found!";
    exit;
}

echo "Found: " . count($seDocs) . " orders\n";


/*-------------------------------------------------*/
/*  COLLECT SPECIAL FIELDS
/*-------------------------------------------------*/
$grpDocs = [];
$storeIdArr = [];	//store, unqiue id list
$storeSpecialArr = []; //store list of special fields
$depotIdArr = []; //depot, unqiue id list
$depotSpecialArr = []; //depot list of special fields

foreach ($seDocs as $k => $r) {
    $grpDocs[$r['dm_uid']][] = $r;
    $storeIdArr[$r["principal_store_uid"]] = $r["principal_store_uid"];
    $depotIdArr[$r['depot_uid']] = $r['depot_uid'];
}

// get special field values for all stores in above docs
if (sizeof($storeIdArr) > 0) {
    $storeSpecialArr = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesMultEntities(RichesConstants::PrincipalID, 434, implode(",", $storeIdArr), CT_STORE_SHORTCODE, $arrayIndex = "entity_uid");
}
if (sizeof($depotIdArr) > 0) {
    $depotSpecialArr = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesMultEntities(RichesConstants::PrincipalID, 433, implode(",", $depotIdArr), CT_DEPOT_SHORTCODE, $arrayIndex = "entity_uid");
}

$errorSEUIdArr["no store special field"] = [];
$errorSEUIdArr["no depot special field"] = [];
$successSEUIdArr = [];
$successCount = 0;

$omniApi = new OMNIRestAPI(
    RichesConstants::OmniHostname,
    RichesConstants::OmniUsername,
    RichesConstants::OmniPassword,
    RichesConstants::OmniLiveCompany
);

echo "Starting to Post Orders";

foreach($grpDocs as $ord) :

    #var_dump($ord);

    /*-------------------------------------------------------------
     *      get fields / special as required
     *------------------------------------------------------------
     */

    //test
    if (empty($storeSpecialArr[$ord[0]["principal_store_uid"]]['value'])) {  //has no special field and/or blank...
        echo "missing special field for store:" . $ord[0]["principal_store_uid"] . "\n<br>";
        $errorSEUIdArr["no store special field"][] = $ord[0]['se_uid']; //list of smart event errors
        continue;
    }
    $storeSpecialValue = $storeSpecialArr[$ord[0]["principal_store_uid"]]['value'];

    if (empty($depotSpecialArr[$ord[0]["depot_uid"]]['value'])) {  //has no special field and/or blank...
        echo "missing special field for depot:" . $ord[0]["depot_uid"] . "\n<br>";
        $errorSEUIdArr["no depot special field"][] = $ord[0]['se_uid']; //list of smart event errors
        continue;
    }
    $depotSpecialValue = $depotSpecialArr[$ord[0]["depot_uid"]]['value'];

    echo "Posting. " . $ord[0]["dm_uid"] . ":- Doc:" . $ord[0]["document_number"] . " Cust Ord:" . $ord[0]["customer_order_number"] . " Depot:" . $ord[0]["depot_name"] ."/". $depotSpecialValue . " Store:" . $ord[0]["deliver_name"] ."/". $storeSpecialValue . "\n<br>";


    $documentNumber = $ord[0]['document_number'];
    if(trim($ord[0]['invoice_number']) == '') {
    	  $invoiceNumber = $ord[0]['document_number'];
    } else {
    	  $invoiceNumber = $ord[0]['invoice_number'];
    }   
    $customerOrderNumber = $ord[0]['customer_order_number'];
    $branchCode = $ord[0]['branch_code'];   
    $documentStatus = $ord[0]['document_status_uid'];
    $orderDate = $ord[0]['customer_order_number'];
    $orderDate = $orderDate != "0000-00-00" ? $orderDate : null;
    $invoiceDate = $ord[0]['invoice_date'];
    $invoiceDate = $invoiceDate != "0000-00-00" ? $invoiceDate : null;
    $deliveryDay = $ord[0]['delivery_date'];
    $deliveryDay = $deliveryDay != "0000-00-00" ? $deliveryDay : null;
    $dueDeliveryDate = $ord[0]['requested_delivery_date'];
    $dueDeliveryDate = $dueDeliveryDate != "0000-00-00" ? $dueDeliveryDate : null;

    $dataSource = $ord[0]['data_source'];
    $incomingFile = substr($ord[0]['incoming_file'],0,30);
    $sourceDocumentNumber = $ord[0]['source_document_number'];
    $storeVatNumber = ''; // $ord[0]['vat_number'];
    $deliveryDetails = $ord[0]['delivery_instructions'];

    $deliverName = ''; // substr($ord[0]["deliver_name"],0,30);
    $deliverAdd1 = ''; // substr($ord[0]["deliver_add1"],0,30);
    $deliverAdd2 = ''; // substr($ord[0]["deliver_add2"],0,30);
    $deliverAdd3 = ''; // substr($ord[0]["deliver_add3"],0,30);
    $billName = ''; // substr($ord[0]["bill_name"],0,30);
    $billAdd1 = ''; // substr($ord[0]["bill_add1"],0,30);
    $billAdd2 = ''; // substr($ord[0]["bill_add2"],0,30);
    $billAdd3 = ''; // substr($ord[0]["bill_add3"],0,30);

    /*-------------------------------------------------------------
     *      CREATE ORDER OBJECT
     *-----------------------------------------------------------*/
    $omniOrder = (new OmniSalesOrderObj)
        //->setAnalysis4()	//###
        //->setAreaCode()
        ->setBranchCode($branchCode)
        //->setCapturedBy()
        ->setCustomerAccountCode($storeSpecialValue)
        ->setCustomerOrderNo($customerOrderNumber)
        ->setDeliveryDay($deliveryDay)
        ->setDeliveryDetails($deliveryDetails)
        //->setDeliveryRoute()
        ->setDocumentDate($invoiceDate)
        ->setDueDate($dueDeliveryDate)
        ->setExtraInfo3($incomingFile)
        //->setExtraInfo6()
        //->setExtraInfo7()
        //->setJobNo()
        //->setMemo()
        //->setOverallDiscountCode()
        ->setPhysicalAddress1($deliverName)
        ->setPhysicalAddress2($deliverAdd1)
        ->setPhysicalAddress3($deliverAdd2)
        ->setPhysicalAddress4($deliverAdd3)
        //->setPhysicalAddress5()
        ->setPostalAddress1($billName)
        ->setPostalAddress2($billAdd1)
        ->setPostalAddress3($billAdd2)
        ->setPostalAddress4($billAdd3)
        //->setPostalAddress5()
        //->setPostCode()
        ->setPrincipalinv($invoiceNumber)	//
        ->setPrinciple("RP001")
        //->setRepCode()
        ->setRevenueAccCode('100144')
        ->setSourceReference($sourceDocumentNumber)
        ->setSourceType($dataSource)
        //->setStoreName()
        ->setVatRegistrationNo($storeVatNumber)
        ->setWarehouseCode($depotSpecialValue);

    /*-------------------------------------------------------------
     *      ORDER
     *------------------------------------------------------------
     */
    foreach ($ord as $d) :

        if (abs($d['ordered_qty']) == 0) {
            continue;
        }

        //set and build required fields here
        if($documentStatus == DST_INVOICED) {
            $documentQty = abs($d['document_qty']);	
        } else {
        	  $documentQty = abs($d['ordered_qty']);
        }
        if($d['alt_code'] == NULL) {
        	  $productCode = trim(str_replace(['"'], [''], $d['product_code']));
        } else {
        	  $productCode = trim(str_replace(['"'], [''], $d['alt_code']));
        }        
        $productDescription = substr(trim(str_replace(['"', '\\', "\t", "\n", "\r"], ['', '', '', '', ''], $d['product_description'])),0,30);

        /*-------------------------------------------------------------
          *      CREATE ORDER LINES
          *-----------------------------------------------------------*/
        $orderLineItem = (new OmniSalesOrderLineItemObj)
            //->setAnalysis4()
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
            ->setExtDiscountValue(number_format($d['discount_value'], 2, '.', ''))
            //->setExtPrice(number_format($d['extended_price'], 2, '', ''))
            ->setExtPriceExcl(number_format($d['extended_price'], 2, '.', ''))
            ->setExtPriceIncl(number_format($d['total'], 2, '.', ''))
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
            ->setLineNo($d['line_no'])
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
            ->setRevenueAccCode('100144')
            //->setRevenueAccDescription()
            ->setSellingPrice(number_format($d['net_price'], 2, '.', ''))
            ->setSellingPricePer(1)
            //->setSequenceNo()
            //->setSerialNo()
            //->setSourceReferenceNo()
            //->setSourceType()
            //->setStockCategoryCode()
            //->setStockCategoryDescription()
            ->setStockCode($productCode)
            ->setStockDescription($productDescription)
            //->setStockLevel()
            //->setStockType()
            //->setTaxType()
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
            //->setVatCode()
            ->setVatRate(number_format($d['vat_rate'], 2, '.', ''))
            ->setVatValue(number_format($d['vat_amount'], 2, '.', ''))
            //->setWarehouse()
            //->setWarehouseDescription()
            //->setWarrantyPeriod()
        ;

        //append to order
        $omniOrder->addOrderLine($orderLineItem);

    endforeach; //eo detail

    /*-------------------------------------------------------------
     *   POST TO OMNI
     *------------------------------------------------------------
     */

    //debug request
    print_r($omniOrder->getJSON());


    $response = $omniApi->CreateSalesOrder($omniOrder, $documentNumber);
    $responseOrderNo = $response->getBody();  //only the order number comes back and not in JSON format!
    
    if (!$response->getSuccess() || $response->getSuccess() && $responseOrderNo != $documentNumber) {

        $errorKey = "[OMNI API]: " .$response->getErrorMessage();
        if(!isset($errorSEUIdArr[$errorKey])){
            $errorSEUIdArr[$errorKey] = [];
        }
        $errorSEUIdArr[$errorKey][] = $ord[0]['se_uid']; //list of smart event success

        echo "[OMNI] Error Creating Order: " . $errorKey . "\n<br>";
        //dump full response here...
        //var_dump($response);

    } else {

        $successCount++;
        $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

        echo "[OMNI] Successfully Created Order:" . $responseOrderNo;

        /*-------------------------------------------------------------
         *      GET POSTED ORDER AND VERIFY -- ALL OK!
         *------------------------------------------------------------
         */
        echo "<h1>Fetching Order: {$documentNumber}</h1>";
        $createdOrder = $omniApi->GetSalesOrder($documentNumber);
        $warnings = [];

        var_dump($createdOrder->getResponse()->getBody());

        if($createdOrder->getResponse()->getSuccess()){

            if($omniOrder->getDocumentDate() != $createdOrder->getDocumentDate()){
                $warnings[] = "Document Date Mismatch (" . $omniOrder->getDocumentDate() . "/" . $createdOrder->getDocumentDate() . ")";

            }

            if($omniOrder->getCustomerAccountCode() != $createdOrder->getCustomerAccountCode()){
                $warnings[] = "Customer Code Mismatch (" . $omniOrder->getCustomerAccountCode() . "/" . $createdOrder->getCustomerAccountCode() . ")";
            }

            //check every line
            foreach($omniOrder->getOrderLines() as  $k => $line){

                $createdLine = $createdOrder->getOrderLines()[$k] ?? null;
                if(!$createdLine){
                    $warnings[] = "Missing Line: " . $k+1 . " - " . $line->getLineNo();
                    continue;
                }

                if($line->getExtPriceIncl() != $createdLine->getExtPriceIncl()){
                    $warnings[] = "ExtPriceIncl Mismatch (" . $line->getExtPriceIncl() . "/" . $createdLine->getExtPriceIncl() . ")";

                }
                //if($line->getInvoiced() != $createdLine->getInvoiced()){
                //    $warnings[] = "Invoiced Mismatch (" . $line->getInvoiced() . "/" . $createdLine->getInvoiced() . ")";
                //}
            }


        } else {
            $warnings[] = $createdOrder->getResponse()->getErrorMessage();
        }

        if(count($warnings)){

            echo "[WARNINGS] " . $ord[0]['se_uid'] . " ====> " . implode(";", $warnings);

            $bIResult = (new PostBIDAO($dbConn))->setSmartEventStatusBulk($ord[0]['se_uid'], "WARNING", implode(";", $warnings));
            if ($bIResult->type == FLAG_ERRORTO_SUCCESS) {
                $dbConn->dbinsQuery("commit;");
            } else {
                echo "failed query!";
                $dbConn->dbinsQuery("rollback;");
            }
        }

    }

endforeach; //eo documents


/*-------------------------------------------------------------
 *   UPDATE SMART EVENT in BULK
 *------------------------------------------------------------
 */
if (count($successSEUIdArr) > 0) {
    $bIResult = (new PostBIDAO($dbConn))->setSmartEventStatusBulk(implode(",", $successSEUIdArr), "SUCCESS", "");
    if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $error = "Failed in " . get_class($this) . " extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in " . get_class($this) . " extract", $error, "Y", false);
        exit;
    }
    $mTResult = (new PostBIDAO($dbConn))->setOmniImport(implode(",", $successSEUIdArr));
    if ($mTResult->type != FLAG_ERRORTO_SUCCESS) {
        $error = "Failed in " . get_class($this) . " extract on setting success setOmniImport with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in " . get_class($this) . " extract", $error, "Y", false);
        exit;
    }   
}

/*-------------------------------------------------------------
 *   ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
 *------------------------------------------------------------
 */
if (sizeof($errorSEUIdArr) > 0) {
    //set these per the error messages...
    foreach ($errorSEUIdArr as $errorMessage => $UidArr) {
        if (count($UidArr) > 0) {
            $bIResult = (new PostBIDAO($dbConn))->setSmartEventStatusBulk(implode(",", $UidArr), "ERROR", $errorMessage, FLAG_ERRORTO_ERROR);
            if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                $error = "Failed in " . get_class($this) . " on setting errror setSmartEventStatusBulk with {$bIResult->description}";
                BroadcastingUtils::sendAlertEmail("Error in " . get_class($this) . " extract", $error, "Y", false);
                exit;
            }
        }
    }
}

$dbConn->dbinsQuery("commit;");

echo "Successfully Completed RICHES ORDERS SYNC\n";

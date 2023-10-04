<?php

/* * ********************************************************************************************
 * *
 * *  LIVE Omni - SALES ORDER PUSH TO OMNI RESTAPI API
 * *
 * *****************ss*************************************************************************** */

require_once __DIR__ . "/../../../libs/api/omni/OmniRestAPI.php";
require_once __DIR__ . "/../../../properties/Omni_Constants_291.php";

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/OmniExtractDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/postExtractDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/MiscellaneousDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/BIDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/messagingDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/postDistributionDAO.php");
include_once($ROOT . $PHPFOLDER . 'TO/PostingDistributionTO.php');

set_time_limit(15 * 60); // 15 mins
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

$constantsClass = "Omni_Constants_291";

/*-------------------------------------------------*/
/*  FETCH Notification Recipients ??
/*-------------------------------------------------*/
//use the receipients listed in the notification table instead of hard coding them!!!
//expecting only one row loaded per principal extract
$reArr = (new BIDAO($dbConn))->getNotificationRecipients($constantsClass::PrincipalID, NT_DAILY_EXTRACT_ALTCUSTOM3);
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

$statusArr = ([$constantsClass::StartStatusInvoices]);

$rTO = (new PostExtractDAO($dbConn))->queueAllInvoiced($constantsClass::PrincipalID,
    $recipientUId,
    $inclCancelled = false,
    $documentTypeArr,
    $statusArr,
    $fromInvDate = '2023-01-01',
    $toInvDate = false,
    $chainUIdIn = false,
    $dataSource = false,
    $capturedBy = false,
    $depotUId = false,
    $altChainUIdIn = false);

//use the loaded receipientUID and not the notification type... *** same as document confirmations***
if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
    BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in " . __FILE__ . " ERROR:" . $rTO->description, "Y");
} else {
    $dbConn->dbinsQuery("commit;");
}
echo "Fetching getDailyExtractInvoicedOrders\n";
$seDocs = (new OmniExtractDAO($dbConn))->getOrdersForOmni($constantsClass::PrincipalID,
    $constantsClass::extractTypeInvoices,
    '',
    $constantsClass::ActiveDepotInvoices);

if (!is_array($seDocs) || is_array($seDocs) && count($seDocs) == 0) {
    echo "Successful --> No outstanding Invoices found!";
    $errorTO = new ErrorTO();
    $errorTO->type = FLAG_ERRORTO_SUCCESS;  //preset.
    $errorTO->object = [];
    echo "<br><br>";
    echo "[OMNI] End Of Script";
    echo '[***EOS***]';

    return ($errorTO);
}

echo "Found: " . count($seDocs) . " order lines\n";

/*-------------------------------------------------*/
/*  COLLECT SPECIAL FIELDS
/*-------------------------------------------------*/
$grpDocs = [];
$storeIdArr = [];    //store, unqiue id list
$storeSpecialArr = []; //store list of special fields
$depotIdArr = []; //depot, unqiue id list
$depotSpecialArr = []; //depot list of special fields

foreach ($seDocs as $k => $r) {
    $grpDocs[$r['dm_uid']][] = $r;
    $storeIdArr[$r["psmUid"]] = $r["psmUid"];
    $depotIdArr[$r['depotUid']] = $r['depotUid'];
}

// get special field values for all stores in above docs
if (sizeof($storeIdArr) > 0) {
    $storeSpecialArr = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesMultEntities($constantsClass::PrincipalID, 332, implode(",", $storeIdArr), CT_STORE_SHORTCODE, $arrayIndex = "entity_uid");
    $omniBranchSpecialArr = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesMultEntities($constantsClass::PrincipalID, 335, implode(",", $storeIdArr), CT_STORE_SHORTCODE, $arrayIndex = "entity_uid");
}

$errorSEUIdArr = [];
$successSEUIdArr = [];
$successCount = 0;

$omniApi = new OMNIRestAPI(
    $constantsClass::OmniHostname,
    $constantsClass::OmniUsername,
    $constantsClass::OmniPassword,
//    $constantsClass::OmniTestCompany
    $constantsClass::OmniLiveCompany
);

echo "Starting to Post Orders";

foreach ($grpDocs as $ord) {

    #var_dump($ord);

    /*-------------------------------------------------------------
     *      get fields / special as required
     *------------------------------------------------------------
     */

    //test
    if (empty($storeSpecialArr[$ord[0]["psmUid"]]['value']) || empty($omniBranchSpecialArr[$ord[0]["psmUid"]]['value'])) {  //has no special field and/or blank...

        $errorSEUIdArr["no store special field"] = [];
        $errorSEUIdArr["no Store Branch special field"] = [];

        echo "missing special field for store:" . $ord[0]["psmUid"] . "\n<br>";

        $errorSEUIdArr["no store special field"][] = $ord[0]['smartUid']; //list of smart event errors

        $updateSEstatus = FLAG_STATUS_ERROR;
        $updateGeneral1 = '';
        $general2 = '';
        $statusMsg = "[KOS] Missing Omni Account for store:   <br>";
        $setOmniImportAll = new PostBIDAO($dbConn);
        $mTResult = $setOmniImportAll->setSmartEventStatusIndividual($ord[0]['smartUid'], $updateGeneral1, $general2, $statusMsg, $updateSEstatus);
        $processErrors = 'Y';
        continue;
    }
    $storeSpecialValue = $storeSpecialArr[$ord[0]["psmUid"]]['value'];

    $branchSpecialValue = $omniBranchSpecialArr[$ord[0]["psmUid"]]['value'];

    $specialFieldValue = new MiscellaneousDAO($dbConn);
    $depotSpecialArr = $specialFieldValue->getSpecialFieldValues($constantsClass::depotSpf,
        $ord[0]['depotUid']);
    if (empty($depotSpecialArr[0]['value'])) {  //has no special field and/or blank...
        $general1 = "Omni Warehouse Unknown - " . ltrim($ord[0]['document_number']);
        // Update SE - Quit and loop
        $updateSmartE = new OmniExtractDAO($dbConn);
        $uSE = $updateSmartE->updateSmartEventDirectly($ord[0]['smartUid'], $general1, $general2 = "", $statusFlag = FLAG_ERRORTO_ERROR);
        $kosError = 'T';
        break;
    } else {
        $depotSpecialValue = $depotSpecialArr[0]['value'];
    }

    echo "Posting. " . $ord[0]["dm_uid"] . ":- Doc:" . $ord[0]["document_number"] . " Cust Ord:" . $ord[0]["customer_order_number"] . " Depot:" . $ord[0]["depot_name"] . "/" . '' . " Store:" . $ord[0]["deliver_name"] . "/" . $storeSpecialValue . "\n<br>";

    $documentNumber = ltrim($ord[0]['document_number'], '0');
    $clientDocNumber = ltrim($ord[0]['client_document_number'], '0');
    if (trim($ord[0]['invoice_number']) == '') {
        $invoiceNumber = ltrim($ord[0]['document_number'], '0');
    } else {
        $invoiceNumber = ltrim($ord[0]['invoice_number'], '0');
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
    $incomingFile = substr($ord[0]['incoming_file'], 0, 30);
    $storeVatNumber = ''; // $ord[0]['vat_number'];
    $deliveryDetails = $ord[0]['delivery_instructions'];

    $deliverName = substr($ord[0]["deliver_name"], 0, 30);
//    $deliverAdd1 = substr($ord[0]["deliver_add1"],0,30);
//    $deliverAdd2 = substr($ord[0]["deliver_add2"],0,30);
//    $deliverAdd3 = substr($ord[0]["deliver_add3"],0,30);
//    $billName = substr($ord[0]["bill_name"],0,30);
//    $billAdd1 = substr($ord[0]["bill_add1"],0,30);
//    $billAdd2 = substr($ord[0]["bill_add2"],0,30);
//    $billAdd3 = substr($ord[0]["bill_add3"],0,30);
    $buyeraccountreference = trim($ord[0]["buyer_account_reference"]);

    /*-------------------------------------------------------------
     *      CREATE ORDER OBJECT
     *-----------------------------------------------------------*/
    $omniOrder = (new OmniInvoiceObj)
        ->setAnalysis4($storeSpecialValue)    //###
        //->setAreaCode()
        ->setBranchCode($branchCode)
        //->setCapturedBy()
        ->setCustomerAccountCode($storeSpecialValue)
        ->setCustomerBranchCode($branchSpecialValue)
        ->setCustomerOrderNo($customerOrderNumber)
        ->setDeliveryDay($deliveryDay)
        ->setDeliveryDetails($documentNumber)  //  *************
        //->setDeliveryRoute()
        ->setDocumentDate($invoiceDate)
        ->setDueDate($dueDeliveryDate)
        //->setExtraInfo3($incomingFile)
        //->setExtraInfo6()
        //->setExtraInfo7()
        //->setJobNo()
        //->setMemo()
        //->setOverallDiscountCode()
        ->setPhysicalAddress1($deliverName)
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
        //->setPrincipalinv()  //  ************
        //->setPrinciple("")
        //->setRepCode("")
        ->setRevenueAccCode('100100')
        //->setSourceReference()
        //->setSourceType($dataSource)
        ->setStoreName($deliverName)
        ->setVatRegistrationNo($buyeraccountreference)
        ->setWarehouseCode($depotSpecialValue);
    //->setAnalysis4();
    /*-------------------------------------------------------------
     *      ORDER
     *------------------------------------------------------------
     */
    foreach ($ord as $d) {
        $DocUid = $d['dm_uid'];

        if (abs($d['document_qty']) == 0) {
            continue;
        }
        $documentQty = abs($d['document_qty']);
//      $prodRevenueCode = $d['prod_group_code'];

        $productCode = trim(str_replace(['"'], [''], $d['product_code']));

        $productDescription = substr(trim(str_replace(['"', '\\', "\t", "\n", "\r"], ['', '', '', '', ''], $d['product_description'])), 0, 30);

        if ($d['vat_rate'] == '0.00') {
            $vatCode = '2';
        } else {
            $vatCode = '1';
        }
        echo $productCode;

        // Get Batch Numbers

        $getBatchNo = new OmniExtractDAO($dbConn);
        $batArr = $getBatchNo->getAvailableBatches($constantsClass::PrincipalID,
            $ord[0]['depotUid'],
            $d['pp.uid']);
        $loadInvoice = 'T';
        foreach ($batArr as $batRow) {
            print_r($batRow);

            if ($batRow['quantity'] - $batRow['reduced_quantity'] >= $documentQty) {
                $tBatch_no = $batRow['batch'];
                $tQty = $documentQty;
                // Update Batch log

                $updBatchLog = new OmniExtractDAO($dbConn);
                $errorTO = $updBatchLog->updateBatchLog($batRow['batUid'],
                    $d['detailUid'],
                    $d['pp.uid'],
                    $batRow['batch'],
                    $documentQty,
                    $DocUid);

                if ($errorTO->type != FLAG_ERRORTO_SUCCESS) {
                    echo "<br>";
                    echo "Batch Log Update failed";
                    echo "<br>";
                    die();
                }
                break;
            } else {
                // Don't Load Invoice - Batch Failure
                $loadInvoice = 'T';
                $tBatch_no = "Unknown";
                $tQty = 0;
            }
        }

//      echo "<pre>";
//      print_r($batArr);

        /*-------------------------------------------------------------
          *      CREATE ORDER LINES
          *-----------------------------------------------------------*/
        $orderLineItem = (new OmniInvoiceLineItemObj)

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
            ->setDiscount(0)
            //->setDiscountType()
            //->setDueDate()
            //->setExtCostPrice()
            ->setExtDiscountValue(0)
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
            //->setRevenueAccCode('')
            //->setRevenueAccDescription()
            ->setSellingPrice(number_format($d['net_price'], 2, '.', ''))
            //->setSellingPricePer(1)
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
            ->setTaxType('Exclusive')
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
            ->setVatCode($vatCode)
            ->setVatRate(number_format($d['vat_rate'], 2, '.', ''))
            ->setVatValue(number_format($d['vat_amount'], 2, '.', ''));
        //->setWarehouse()
        //->setWarehouseDescription()
        //->setWarrantyPeriod() ;
        $orderLineItem->addTrackingNumbers($tSerial_no = 'NA',
            $tBatch_no,
            $tTracking_no_3 = "NA",
            $tTracking_no_4 = "NA",
            $tQty);

        //append to order
        $omniOrder->addOrderLine($orderLineItem);


    }

    /*-------------------------------------------------------------
     *   POST TO OMNI
     *------------------------------------------------------------
     */

// debug request
//print_r($omniOrder->getJSON());

    if ($loadInvoice == 'T') {

        $response = $omniApi->CreateInvoice($omniOrder, $documentNumber, $DocUid);

        // VAR_DUMP($response);

        $responseOrderNo = $response->getBody();  //only the order number comes back and not in JSON format!

        if (!$response->getSuccess()) {

            $errMessage = $response->getErrorMessage();

            if (strpos(strtolower($errMessage), "already exists") !== false) {

                echo "[OMNI] Duplicate Order, ignoring: " . $errMessage . "\n<br>";
                $successCount++;
                $successSEUIdArr[] = $ord[0]['smartUid']; //list of smart event success
                //minor hack as the duplicate error message does not return the order number in JSON!!!
                $responseOrderNo = trim(str_replace(['Sales', 'Invoice', 'already', 'exists.'], '', $responseOrderNo));

            } else {

                $errorKey = "[OMNI API]: " . $errMessage;
                if (!isset($errorSEUIdArr[$errorKey])) {
                    $errorSEUIdArr[$errorKey] = [];
                }
                $errorSEUIdArr[$errorKey][] = $ord[0]['smartUid']; //list of smart event success

                echo "[OMNI] Error Creating Order: " . $errorKey . "\n<br>";
                //dump full response here...

            }
        } else {

            $successCount++;
            $successSEUIdArr[] = $ord[0]['smartUid']; //list of smart event success

            echo "<h1>[OMNI] Successfully Created Invoice:" . $responseOrderNo . "  -  " . $documentNumber . "</h1>";

        }
        break;
    }
}

/*-------------------------------------------------------------
 *   UPDATE SMART EVENT in BULK
 *------------------------------------------------------------
 */

// print_r($successSEUIdArr);

if (count($successSEUIdArr) > 0) {
    $bIResult = (new PostBIDAO($dbConn))->setSmartEventStatusBulk((implode(",", $successSEUIdArr)), "SUCCESS", "");
    if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $error = "Failed in " . get_class($this) . " extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in " . get_class($this) . " extract", $error, "Y", false);
        exit;
    }

    $updateImportStat = new OmniExtractDAO($dbConn);
    $uSE = $updateImportStat->setOmniInvoiceStatus(implode(",", $successSEUIdArr), $constantsClass::extractTypeInvoices, $responseOrderNo);
}

/*-------------------------------------------------------------
 *   ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
 *------------------------------------------------------------
 */

if (sizeof($errorSEUIdArr) > 0) {
    echo "generating errors...\n";
    //set these per the error messages...

    $sp = '';
    $eList = '';
    foreach ($errorSEUIdArr as $errorMessage => $UidArr) {

        if (count($UidArr) > 0) {
            $bIResult = (new PostBIDAO($dbConn))->setSmartEventStatusBulk(implode(",", $UidArr), "ERROR", $errorMessage, FLAG_ERRORTO_ERROR);
            if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                $error = "Failed in " . get_class($this) . " on setting errror setSmartEventStatusBulk with {$bIResult->description}";
                BroadcastingUtils::sendAlertEmail("Error in " . get_class($this) . " extract", $error, "Y", false);
                exit;
            }
            foreach ($UidArr as $erow) {
                $eList = $eList . $sp . $erow;
                $sp = ",";
            }

        }

    }

    OmniErrorReporting($constantsClass::PrincipalID, $seDocs[0]['type_uid'], CTD_EDI);
}
$dbConn->dbinsQuery("commit;");

$errorTO = new ErrorTO();
$errorTO->type = FLAG_ERRORTO_SUCCESS;  //preset.
$errorTO->object = [];

echo "<br><br>";
echo "[OMNI] End Of Script";
echo '[***EOS***]';

return ($errorTO);

// ******************************************************************************************************************************
function OmniErrorReporting($principalUId, $seType, $nType)
{

    global $ROOT;
    global $PHPFOLDER;

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
    $errLST = $messagingDAO->getOmniErrorNotificationRecipients($principalUId, $seType, $nType);

    if (count($errLST) > 0) {

        $storeString = '';
        $bodyString = '';

        $c = 0;

        foreach ($errLST as $elRow) {
            if ($storeString <> trim($elRow['email_addr'])) {
                // Set up new distribution TO
                $postingDistributionTO = new PostingDistributionTO($dbConn);
                $postingDistributionTO->DMLType = "INSERT";
                $postingDistributionTO->deliveryType = BT_EMAIL;

                $messagingDAO = new messagingDAO($dbConn);
                $postingDistributionTO->subject = $messagingDAO->getTemplateOmniImportErrorSubject(trim($elRow['Principal']) . ' - Invoices');
                $postingDistributionTO->destinationAddr = trim($elRow["email_addr"]);
                $storeString = trim($elRow['email_addr']);

                $messagingDAO = new messagingDAO($dbConn);
                $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorHeader($elRow['Principal']);
            }

            $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorBody($elRow['document_number'],
                    $elRow['invoice_date'],
                    trim($elRow['WhAbr']) . ' - ' . $elRow['deliver_name'],
                    trim(preg_replace("/\r|\n/", "", $elRow['general_reference_2'])),
                    $elRow['dataUid'],
                    $elRow['psm.uid'],
                    $elRow['type']);
        }

        $messagingDAO = new messagingDAO($dbConn);
        $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorend($elRow['Principal']);

        $postDistributionDAO = new postDistributionDAO($dbConn);
        $postingDistributionTO->body = $bodyString;
        $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
    }
    $dbConn->dbinsQuery("commit;");

}


// ******************************************************************************************************************************

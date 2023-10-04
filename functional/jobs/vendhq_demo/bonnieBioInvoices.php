<?php
/*
 https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/vendhq_demo/bonnieBioInvoices.php";

 * ------------------------------------------------------
 * PROCESSOR TO PUSH INVOICES TO XERO.COM FOR PENTACORP CATCH
 * ------------------------------------------------------
 */
/*-----------------------------------
	INTEGRATION PARAMETERS
-----------------------------------*/
$integrationTYPE = "LightSpeed";
$principalId = 458;
$principalStoreSpecialFieldID = 640;    /* BonnieBio Vend Contact ID!! */
/*-----------------------------------*/

error_reporting(-1);
ini_set("display_errors", 1);

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'libs/common.php');
include_once($ROOT . $PHPFOLDER . "libs/GUICommonUtils.php");
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . "DAO/BIDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PostExtractDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/ExtractDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/MiscellaneousDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");
include_once($ROOT . $PHPFOLDER . 'DAO/PostDistributionDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingDistributionTO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/MiscellaneousDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . "DAO/OmniExtractDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/api/vendhq/client.php');

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();
$errorTO = new ErrorTO;


//TO DO: store in SSM

$apiKey = "lsxs_pt_dhCVfmsY4VodPazeQKPFmKHmI570ArgO";
$domainPrefix = "bonniebio";

$client = new VendHQClient($apiKey, $domainPrefix);

echo "<pre>";

/*-------------------------------------------------*/
/*  Fetch Notification Recipient ID
/*-------------------------------------------------*/

$reArr = (new BIDAO($dbConn))->getNotificationRecipients($principalId, NT_DAILY_EXTRACT_CUSTOM);
if (count($reArr) == 0) {
    BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in " . __FILE__, "Y");
    return;
}
$recipientUId = $reArr[0]['uid'];
#var_dump($reArr);
echo "Using recipient: {$recipientUId}\n";

/*-------------------------------------------------*/
/*  Create Consolidated Transactions
/*-------------------------------------------------*/
/*-------------------------------------------------*/
/*  QUEUE DOCUMENTS IN SMART EVENTS
/*-------------------------------------------------*/
$rTO = (new PostExtractDAO($dbConn))->queueAllInvoiced($principalId, $recipientUId,
    $inclCancelled = false,  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
    $p_dtArr = false,
    $p_wDSArr = false,
    $fromInvDate = '2023-08-01',
    $toInvDate = false,
    $chainUIdIn = false,
    $dataSource = false,
    $capturedBy = false,
    $depotUId = false
);

//use the loaded receipientUID and not the notification type... *** same as document confirmations***
if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
    BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in " . __FILE__ . " ERROR:" . $rTO->description, "Y");
    return;
} else {
    $dbConn->dbinsQuery("commit;");
}

/*-------------------------------------------------*/
/*  FETCH ORDERS THAT NEED TO BE SENT.
/*-------------------------------------------------*/
echo "Fetching getDailyExtractInvoicedOrders\n";
$seDocs = (new ExtractDAO($dbConn))->getDailyExtractInvoicedOrders($principalId,
    $recipientUId,
    $orderBy = "se.status DESC, a.uid DESC, c.uid");

// print_r($seDocs);

if (!is_array($seDocs) || is_array($seDocs) && count($seDocs) == 0) {
    echo "Successful --> No outstanding orders found!";
    return;
}
echo "Found: " . count($seDocs) . " order lines\n";

//group orders
$bucketArr = [];
$storeArr = [];
foreach ($seDocs as $row) {
    $docId = $row['dm_uid'];
    $storeArr[$row['principal_store_uid']] = $row['principal_store_uid'];    //list of principal stores

    if (!isset($bucketArr[$docId])) {
        $bucketArr[$docId] = [];
    }
    $bucketArr[$docId][] = $row;
}

/*-------------------------------------------------*/
/*  COLLECT SPECIAL FIELDS
/*-------------------------------------------------*/
//FETCH CHAIN SPECIAL FIELDS XERO ID
$storeSpecialArr = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesMultEntities($principalId, $principalStoreSpecialFieldID, implode(",", $storeArr), CT_STORE_SHORTCODE, $arrayIndex = "entity_uid");

$errorSEUIdArr = [];
$successSEUIdArr = [];
$successCount = 0;

// loop through each CHAIN ID...
foreach ($bucketArr as $lineArr) {

    // BUILD UP DATA HERE FOR VEND INVOICE

    $storeId = $lineArr[0]['principal_store_uid'];
    $storeName = $lineArr[0]['deliver_name'];

    // print_r($lineArr);

    // DO WE HAVE A FILLED IN VEND SPECIAL FIELD?
    if (!isset($storeSpecialArr[$storeId]) ||
        empty($storeSpecialArr[$storeId]['value']) ||
        strlen(trim($storeSpecialArr[$storeId]['value'])) != 36)    //xero ids are 36 chars
    {

        echo "Empty Vend Id for Store: {$storeId} - {$storeName} !\n";

        //generate error for this chain item!
        if (!isset($errorSEUIdArr["no store special field"])) {
            $errorSEUIdArr["no store special field"] = [];
        }
        $errorSEUIdArr["no store special field"][] = $lineArr[0]['se_uid'];

        continue;
    }

    //set the contact id value, this is used as the customer identify on xero's end!
    $storeVendContactID = trim($storeSpecialArr[$storeId]['value']);

    $sale = (new VendHQSale())
        ->setRegisterId("0afa8de1-1450-11e9-edec-3ad777df825e")
        ->setUserId("616e9cdc-8f2f-4928-bff2-6be104504311")
        ->setSaleDate($lineArr[0]['invoice_date'])
        ->setCustomerId($storeVendContactID)
        ->setInvoiceNumber($lineArr[0]['document_number'])
        ->setStatus('ONACCOUNT')   //https://docs.vendhq.com/docs/sales_statuses
        ->setNote(trim($lineArr[0]['customer_order_number']));

    $totalPrice = 0;
    foreach ($lineArr as $lineRow) {

        $productCode = $lineRow['product_guid'];
        $productUnitAmount = (float)$lineRow['net_price'];    //this could vary across invoices (times and sessions)
        $productTotalTax = (float)$lineRow['vat_amount'];
        $productTotalQty = (int)$lineRow['document_qty'];



        // Create Vend Invoice Lines
        $productLine = (new VendHQProduct())
            ->setProductId($productCode)
            ->setQuantity((int)$productTotalQty)
            ->setPrice((float)$productUnitAmount)
            ->setTax((float)$productUnitAmount * VAL_VAT_RATE);

        $sale->addProduct($productLine);
    }
    // var_dump($sale);

    $saleResponse = $client->createRegisterSale($sale);

    if (!$saleResponse->getSuccess()) {
        echo 'Error: ' . $saleResponse->getErrorMessage();
        var_dump($saleResponse);
        die();
    }
  
    echo "<br>";
    echo str_repeat('-',45) . "\n";
    echo "<br>";
    $successCount++;
    $successSEUIdArr[] = $lineArr[0]['se_uid']; //list of smart event success

    echo "<h1>[VEND] Successfully Created Invoice:" . $lineArr[0]['document_number'] ."</h1>";
    echo "<br>";
    echo str_repeat('-',45) . "\n";
    echo "<br>";
   // $saleArr = $saleResponse->getBody();
    if($successCount > 5) {
        break;	
    }
}

/*-------------------------------------------------------------
 *   UPDATE SMART EVENT in BULK
 *-----------------------------------------------------------*/
//print_r($successSEUIdArr);

//echo count($successSEUIdArr);

if(count($successSEUIdArr) > 0) {
	
    $bIResult = (new PostBIDAO($dbConn))->setSmartEventStatusBulk((implode(",", $successSEUIdArr)), "SUCCESS", "");
    if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $error = 'Failed in ' . get_class($this) . " extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in " . get_class($this) . " extract", $error, "Y", false);
        exit;
    }
}
$dbConn->dbinsQuery("commit;");

/*-------------------------------------------------------------
 *   ERROR EXTRACTS
 *   MARK SE AS "E", for extract errors display screen.
 *------------------------------------------------------------*/
if (count($errorSEUIdArr) > 0) {
    //errors are grouped by error message
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

echo "<br>End of Bonnie Bio Invoices<br>";
echo "[***EOS***]<br>";

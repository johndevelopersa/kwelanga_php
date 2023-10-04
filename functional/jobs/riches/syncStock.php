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
include_once($ROOT . $PHPFOLDER . 'DAO/DepotDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ProductDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostProductDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostStockDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingProductTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingStockTO.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}

//session
if (!isset($_SESSION)) session_start();
$_SESSION['user_id'] = "000";
$_SESSION['principal_id'] = $postingStoreTO->principal;

//display errors.
set_time_limit(30 * 60); //30 mins
error_reporting(-1);
ini_set('display_errors', 1);


echo "<pre style='font-size:14px;'>";
echo str_repeat("-", 75) . "\n";
echo "RICHES LIVE STOCK SYNC PROCESSOR\n";
echo str_repeat("-", 75) . "\n";


/*-------------------------------------------------*/
/*  FETCH List of Depots, where special field is set and not blank
/*-------------------------------------------------*/
$sql = "select   
        d.uid as depot_uid, 
        d.code as depot_code, 
        d.name AS depot_name, 
        sff.uid as special_field_uid, 
        sff.name as special_field_name, 
        sfd.value as special_field_value
    from   depot d,
           special_field_fields sff,
           special_field_details sfd
    WHERE  " . RichesConstants::PrincipalID . " = sff.principal_uid
        and    d.uid = sfd.entity_uid
        and    sff.type = 'D'
        and    sff.uid = 433    -- omni special field
        and    sff.uid = sfd.field_uid
        AND    LENGTH(sfd.value) > 0";

$depotArr = $dbConn->dbGetAll($sql);

if (count($depotArr) == 0) {
    BroadcastingUtils::sendAlertEmail("System Error", "Riches Stock Synced failure - empty depot array in script: " . __FILE__, "Y");
    exit;
}

/*-------------------------------------------------*/
/*  Get a list of products for Riches, index by product code
/*-------------------------------------------------*/
$productsArr = (new ProductDAO($dbConn))->getPrincipalProductsArray(RichesConstants::PrincipalID, "product_code");


/*-------------------------------------------------*/
/*  LOOP Through each depot and grab stock items...
/*-------------------------------------------------*/

$omniApi = new OMNIRestAPI(
    RichesConstants::OmniHostname,
    RichesConstants::OmniUsername,
    RichesConstants::OmniPassword,
    RichesConstants::OmniLiveCompany
);

foreach ($depotArr as $depot) :

    echo "DEPOT: {$depot['depot_name']} ({$depot['special_field_value']}) \n<br>";

    //pull stock per warehouse!
    $stockObj = $omniApi->GetStock(['IWAREHOUSECODE' => $depot['special_field_value']]);

    if ($stockObj->getResponse()->getSuccess() && is_array($stockObj->getAutostocklevels()) && count($stockObj->getAutostocklevels()) > 0) {

        //TODO: we might want to blank all stock items for this warehouse, but only when we have a response.
        // --set available = 0

        $stockBulkArr = [];

        foreach ($stockObj->getAutostocklevels() as $stockItem) :

            $productCode = $stockItem->getStockCode();
            $productDesc = $stockItem->getStockDescription();
            $availableStock = $stockItem->getAvailable();

            //ignore products that don't exist
            if (isset($productsArr[$productCode])) {
                $productUid = $productsArr[$productCode]['uid'];

                //assign stock to
                $PostingStockTO = new PostingStockTO();
                $PostingStockTO->stkUid = $productUid;
                $PostingStockTO->principalId = RichesConstants::PrincipalID;
                $PostingStockTO->depotId = $depot['depot_uid'];
                $PostingStockTO->stockCode = substr($productCode,0,45);
                $PostingStockTO->stockDescription = substr($productDesc,0,60);
                $PostingStockTO->available = $availableStock;
                $PostingStockTO->dataGeneratedDate = date("Y-m-d H:i:s");

                //append to bulk insert.
                $stockBulkArr[] = $PostingStockTO;
            }


        endforeach; //eof each stock line


        if(count($stockBulkArr)) {
            /*-------------------------------------------------*/
            /*  Update stock items in Bulk
            /*-------------------------------------------------*/
            $response = (new PostStockDAO($dbConn))->postStockBulk($stockBulkArr);
            if ($response->type != FLAG_ERRORTO_SUCCESS) {
                BroadcastingUtils::sendAlertEmail("System Error", "Riches BulkStock insert failure:" . $response->description, "Y");
                //continue
            } else {
                $dbConn->dbinsQuery("commit;");
                echo "Loaded " . count($stockBulkArr) . " Stock Items for Depot: {$depot['depot_name']}\n<br>";
            }
        }

    } else {
        //no stock found for this depot, possible error - rise alarm!
        BroadcastingUtils::sendAlertEmail("System Error", "Riches Stock Synced failure - no stock list found in script: " . __FILE__ . " API Response:" . $stockObj->getResponse()->getErrorMessage(), "Y");
        exit;
    }

endforeach; //eof each depot

echo "Successfully Completed RICHES STOCK SYNC!\n";

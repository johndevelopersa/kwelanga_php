<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . "DAO/PostImportDAO.php");
include_once($ROOT . $PHPFOLDER . "properties/Constants.php");
include_once($ROOT . $PHPFOLDER . "libs/CommonUtils.php");
include_once($ROOT . $PHPFOLDER . "libs/BroadcastingUtils.php");

set_time_limit(5 * 60);
error_reporting(-1);
ini_set('display_errors', 1);

echo "Starting orders holding purge queries...\n";

$age = (int)($_GET['age'] ?? 120);

$dbConn = new dbConnect();
$dbConn->dbConnection();

$rTO = (new PostImportDAO($dbConn))->purgeProcessedOrdersHolding($age);
if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
    BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing",
        "An Error occurred calling purgeProcessedOrdersHolding:" . $rTO->description,
        "Y",
        $quietMode = false);

    var_dump($rTO);
    die("Query Error");
}

echo "\n" . print_r($rTO->description, true) . "\n";

echo "ordersHoldingPurge Completed\n";
echo "[***EOS***]";

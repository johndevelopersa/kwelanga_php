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
echo "OMNI LIVE REPORTS PROCESSOR\n";
echo str_repeat("-", 75) . "\n";

$omniApi = new OMNIRestAPI(
    RichesConstants::OmniHostname,
    RichesConstants::OmniUsername,
    RichesConstants::OmniPassword,
    RichesConstants::OmniLiveCompany
);
// RICHSDAILYSALESREPORT

// RICHSSalesAnalysis


$stockObj = $omniApi->GetReport($reportName = "TEST_KWL_GS.rep");

 	
    	echo "<br>";
    	print_r($stockObj);

echo "Successfully Completed RICHES REPORTS FETCH!\n";

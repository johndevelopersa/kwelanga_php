<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/omniReports/syncOmniReports.php?REPORTTYPE=omniGlacialReportProcessing

/* * ********************************************************************************************
 * *
 * *  LIVE PULL BISCO REPORTS
 * *
 * **********************************************************************************************/

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php";
require_once $ROOT . $PHPFOLDER . 'properties/Constants.php';
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/OmniExtractDAO.php");

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    $errorTO = new ErrorTO;
}

$reportType = ((isset($_GET["REPORTTYPE"]))?$_GET["REPORTTYPE"]:"");

// Get Onmi Report to fetch


$getReportExecution = new OmniExtractDAO($dbConn);
$omniReport = $getReportExecution->getOmniReportExecution($reportType);


require_once __DIR__ . "/../../../libs/api/omni/OmniRestAPI.php";
require_once __DIR__ . "/../../../properties/Omni_Constants_". $omniReport[0]['principal_uid'] .".php";

$constantsClass = "Omni_Constants_". $omniReport[0]['principal_uid'] ;

$prinUid  = $omniReport[0]['principal_uid'] ;
$depotUid = trim($omniReport[0]['comment']) ;

//display errors.
set_time_limit(30 * 60); //30 mins
error_reporting(-1);
ini_set('display_errors', 1);

echo str_repeat("-", 45) . "\n";
echo "OMNI LIVE REPORTS PROCESSOR\n";
echo str_repeat("-", 45) . "\n";

$omniApi = new OMNIRestAPI(
    $constantsClass::OmniHostname,
    $constantsClass::OmniUsername,
    $constantsClass::OmniPassword,
    //  $constantsClass::OmniTestCompany
    $constantsClass::OmniLiveCompany
);

foreach($omniReport as $row) {
	
      $reportObj = $omniApi->GetReport($reportName = trim($row['page_params']));

     // check the API response was correct (HTTP OK 200)
     if(!$reportObj->getSuccess()){
          print_r($reportObj);
          die("REPORT ERROR");
     }

     // response was OK, get decoded body
     $reportArr = $reportObj->getBody();
     
     #print_r($reportArr);
     
     include_once($ROOT.$PHPFOLDER."functional/jobs/omniReports/{$row['script_name']}.php");

     echo "Successfully Processed " . trim($row['page_params']) . "\n";     
	
}

echo "Successfully Completed REPORTS PROCESS!\n";
echo "[***EOS***]";

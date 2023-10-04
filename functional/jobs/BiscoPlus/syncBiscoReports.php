<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/BiscoPlus/syncBiscoReports.php

/* * ********************************************************************************************
 * *
 * *  LIVE PULL BISCO REPORTS
 * *
 * *****************ss*************************************************************************** */

require_once __DIR__ . "/../../../libs/api/omni/OmniRestAPI.php";
require_once __DIR__ . "/../../../properties/Omni_Constants_291.php";

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
}

$getReportExecution = new OmniExtractDAO($dbConn);
$omniReport = $getReportExecution->getOmniReportExecution('omniReportProcessing');

foreach($omniReport as $row) {
   echo "<pre>";	
   print_r($row);
	
}

die();


$constantsClass = "Omni_Constants_291" ;

// Get Onmi Report to fetch



//display errors.
set_time_limit(30 * 60); //30 mins
error_reporting(-1);
ini_set('display_errors', 1);


echo "<pre style='font-size:14px;'>";
echo str_repeat("-", 75) . "\n";
echo "OMNI LIVE REPORTS PROCESSOR\n";
echo str_repeat("-", 75) . "\n";

$omniApi = new OMNIRestAPI(
    $constantsClass::OmniHostname,
    $constantsClass::OmniUsername,
    $constantsClass::OmniPassword,
//  $constantsClass::OmniTestCompany
    $constantsClass::OmniLiveCompany);
// RICHSDAILYSALESREPORT

// RICHSSalesAnalysis
// Stock Available by Tracking Number - NEL1(FS)
// RPA - Kwelanga Daily Invoice report

$stockObj = $omniApi->GetReport($reportName = "RPA - Stock Available by Tracking Number - NEL1(FS)");

// check the API response was correct (HTTP OK 200)
if(!$stockObj->getSuccess()){
    print_r($stockObj);
    die("REPORT ERROR");
}

// response was OK, get decoded body
$reportArr = $stockObj->getBody();

foreach($reportArr as $row) {
	  foreach($row as $prodrow) {
      
      $startDash = strpos($prodrow['batch_no'],'-',0);
	  	$firstDash = strpos($prodrow['batch_no'],'-',3);
	  	$secondDash = strpos($prodrow['batch_no'],'-',$firstDash+1);

	  	$yy = str_pad(trim(substr($prodrow['batch_no'], $secondDash + 1 ,2)),4,"20",STR_PAD_LEFT);
      $mm = str_pad(trim(substr($prodrow['batch_no'], $firstDash + 1 ,$secondDash - $firstDash - 1)),2,"0",STR_PAD_LEFT);
	  	$dd = str_pad(trim(substr($prodrow['batch_no'], $startDash + 1 ,$firstDash - $startDash - 1)),2,"0",STR_PAD_LEFT);
	  	
	  	echo ($prodrow['stock_code']  . ' ' . 
	  	      $prodrow['batch_no'] . ' ' . 
	  	      $yy . $mm . $dd . ' ' .
	  	      $prodrow['level']) ;
	  	echo "<br>";
	  	
	  	// prepareBatchlist
	  	
	  	
	  	
	  }
	
}

echo "Successfully Completed Bisco REPORTS FETCH!\n";

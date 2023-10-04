<?php
/*
 *
 * THIS LAYER PROVIDES BASIC SCREENING AND CONVERTS INPUT INTO PROPER TO BEFORE CALL IS MADE TO PROCESS
 *
 */
include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/ValidationCommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');


$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback


if (!isset($_SESSION)) session_start;
// override the principal Id for now.
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION['user_id'];


$dbConn = new dbConnect();  //Create new database object
$dbConn->dbConnection();  //Database connection method


$postDMLTYPE = (isset($_POST['DMLTYPE'])) ? (htmlspecialchars($_POST['DMLTYPE'])) : ("");
$postUID = (isset($_POST['UID'])) ? (htmlspecialchars($_POST['UID'])) : ("");
$postDPS = (isset($_POST['FORM_DPS'])) ? (htmlspecialchars($_POST['FORM_DPS'])) : ("");
$postTTDAYGAP = (isset($_POST['TTDAYGAP'])) ? (htmlspecialchars($_POST['TTDAYGAP'])) : ("");
$postTTCOLUMNS = (isset($_POST['TTCOLUMNS'])) ? (htmlspecialchars($_POST['TTCOLUMNS'])) : ("");
$postREPORTOUTSET = (isset($_POST['REPORTOUTSET'])) ? ($_POST['REPORTOUTSET']) : ("");
$postNOTIFYEXCEPTIONTAG = (isset($_POST['NOTIFYEXCEPTIONTAG'])) ? (htmlspecialchars($_POST['NOTIFYEXCEPTIONTAG'])) : ("N");
$postNOTIFYDEPOTORDERTAG = (isset($_POST['NOTIFYDEPOTORDERTAG'])) ? (htmlspecialchars($_POST['NOTIFYDEPOTORDERTAG'])) : ("N");
$postCAPTUREVALIDATION =  (isset($_POST['CAPTUREVALIDATION'])) ? ($_POST['CAPTUREVALIDATION']) : ("N");
$postPRODUCTSORTBY =  (isset($_POST['PRODUCTSORTBY'])) ? ($_POST['PRODUCTSORTBY']) : ("");
$postDISPLAYACCESSLOG =  (isset($_POST['DISPLAYACCESSLOG'])) ? ($_POST['DISPLAYACCESSLOG']) : ("");

// start of superficial checks. Main checks done in adminPost....php

if (($postDMLTYPE!="UPDATE") && ($postDMLTYPE!="INSERT")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if ($postTTCOLUMNS == "" || count(explode(',',$postTTCOLUMNS)) < 4) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Please select atleast 4 columns to display.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};


// Do the Actual Posting
include_once($ROOT.$PHPFOLDER."DAO/PostAdminUserDAO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingUserPreferencesTO.php");
$postAdminUserDAO = new PostAdminUserDAO($dbConn);
$postingUserPreferencesTO = new PostingUserPreferencesTO();

$postingUserPreferencesTO->DMLType = $postDMLTYPE;
$postingUserPreferencesTO->UId = $postUID;
$postingUserPreferencesTO->pageSizeDefault = $postDPS;
$postingUserPreferencesTO->trackingTransactionDayGap = $postTTDAYGAP;
$postingUserPreferencesTO->trackingTransactionColumns = $postTTCOLUMNS;
$postingUserPreferencesTO->notifyExceptionTag = $postNOTIFYEXCEPTIONTAG;
$postingUserPreferencesTO->notifyDepotOrderTag = $postNOTIFYDEPOTORDERTAG;
$postingUserPreferencesTO->userReportOutputSetting = $postREPORTOUTSET;
$postingUserPreferencesTO->capturePreValidationFlag = $postCAPTUREVALIDATION;
$postingUserPreferencesTO->sortProductDropdown = $postPRODUCTSORTBY;
$postingUserPreferencesTO->displayAccessLog = $postDISPLAYACCESSLOG;
$postingUserPreferencesTO->userUId = $userId;


$result=$postAdminUserDAO->postUserPreference($postingUserPreferencesTO);

if ($result->type==FLAG_ERRORTO_SUCCESS) {

  $dbConn->dbinsQuery("commit;");

  $_SESSION["up_dps"] = $postingUserPreferencesTO->pageSizeDefault;
  $_SESSION["up_pSortBy"] = $postingUserPreferencesTO->sortProductDropdown;
  $_SESSION["up_cPreValid"] = $postingUserPreferencesTO->capturePreValidationFlag;

} else $dbConn->dbinsQuery("rollback;");

$dbConn->dbClose();

// check return values
if (sizeof($result)> 0) {
	print(CommonUtils::getJavaScriptMsg($result));
	return;
} else if (sizeof($result)== 0) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Problem occurred during posting : Post returned 0 arraysize. Please inform RetailTrading Management.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
  }

print(CommonUtils::getJavaScriptMsg($result));
return;

?>

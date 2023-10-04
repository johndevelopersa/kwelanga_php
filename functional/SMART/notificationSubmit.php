<?php
/*
 * 
 * THIS LAYER PROVIDES BASIC SCREENING AND CONVERTS INPUT INTO PROPER TO BEFORE CALL IS MADE TO PROCESS
 * 
 */
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/ValidationCommonUtils.php');

$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback

if (!isset($_SESSION)) session_start;
// override the principal Id for now.
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION['user_id'];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
if (isset($_POST['DMLTYPE'])) $postDMLTYPE = mysqli_real_escape_string($dbConn->connection, $_POST['DMLTYPE']); else $postDMLTYPE="";
if (isset($_POST['UID'])) $postUID = mysqli_real_escape_string($dbConn->connection, $_POST['UID']); else $postUID="";
if (isset($_POST['NOTIFICATIONUID'])) $postNOTIFICATIONUID = mysqli_real_escape_string($dbConn->connection, $_POST['NOTIFICATIONUID']); else $postNOTIFICATIONUID="";
if (isset($_POST['USERUIDLIST'])) $postUSERUIDLIST = mysqli_real_escape_string($dbConn->connection, $_POST['USERUIDLIST']); else $postUSERUIDLIST="";
if (isset($_POST['VALUE'])) $postVALUE = mysqli_real_escape_string($dbConn->connection, $_POST['VALUE']); else $postVALUE="";
if (isset($_POST['OUTPUTTYPE'])) $postOUTPUTTYPE = mysqli_real_escape_string($dbConn->connection, $_POST['OUTPUTTYPE']); else $postOUTPUTTYPE="";
if (isset($_POST['DELIVERYTYPE'])) $postDELIVERYTYPE = mysqli_real_escape_string($dbConn->connection, $_POST['DELIVERYTYPE']); else $postDELIVERYTYPE="";
if (isset($_POST['APS'])) $postAPS = mysqli_real_escape_string($dbConn->connection, urldecode($_POST['APS'])); else $postAPS="";

// start of superficial checks. Main checks done in adminPost....php

if (($postDMLTYPE!="UPDATE") && ($postDMLTYPE!="INSERT") && ($postDMLTYPE!="DELETE")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

// Do the Actual Posting
include_once($ROOT.$PHPFOLDER."DAO/PostBIDAO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingNotificationRecipientsTO.php");
$postBIDAO = new PostBIDAO($dbConn);
$postingNotificationRecipientsTO = new PostingNotificationRecipientsTO();

$postingNotificationRecipientsTO->DMLType = $postDMLTYPE;
$postingNotificationRecipientsTO->UId = $postUID;
$postingNotificationRecipientsTO->notificationUId = $postNOTIFICATIONUID;
$postingNotificationRecipientsTO->principalUId = $principalId;
$postingNotificationRecipientsTO->userUIdList = $postUSERUIDLIST;
$postingNotificationRecipientsTO->value = $postVALUE;
$postingNotificationRecipientsTO->outputType = $postOUTPUTTYPE;
$postingNotificationRecipientsTO->deliveryType = $postDELIVERYTYPE;
$postingNotificationRecipientsTO->additionalParameterString = $postAPS;

$result=$postBIDAO->postNotification($postingNotificationRecipientsTO);
if ($result->type==FLAG_ERRORTO_SUCCESS) {
	$dbConn->dbinsQuery("commit;");
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

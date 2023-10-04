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
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');

if (!isset($_SESSION)) session_start;

$returnMessage;

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
$postDMLTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DMLTYPE']));
$postUSERID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['USERID']));
$postUN=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['UN']));
//$postPWD=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PWD']));
$postFN=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['FN']));
$postEMAIL=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['EMAIL']));
$postTEL=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['TEL']));
$postCELL=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['CELL']));
$postSUSPENDED=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['SUSPENDED']));
$postSELFREGISTERED=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['SELFREGISTERED']));
$postDELETED=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DELETED']));
$postCATEGORY=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['CATEGORY']));
$postORGNAME=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['ORGNAME']));
$postADMINUSER = (isset($_POST['ADMINUSER'])) ? ($_POST['ADMINUSER']) : ('N');
$postSTAFFUSER = (isset($_POST['STAFFUSER'])) ? ($_POST['STAFFUSER']) : ('N');

// start of superficial checks. Main checks done in adminPost.php

if (($postDMLTYPE!="UPDATE") && ($postDMLTYPE!="INSERT")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if(($postUSERID=="") && ($postDMLTYPE=="UPDATE")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="UserId not supplied.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if(!preg_match(GUI_PHP_EMAIL_REGEX,$postEMAIL)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Email must be of recognisable structure.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if((strlen($postUN) < 6) && ($postDMLTYPE=="INSERT")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="UserName must be atleast 6 characters.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

/*
if(strlen($postPWD) < 6) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Password must be atleast 6 characters.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};
*/

if(!ValidationCommonUtils::checkFieldYesNoSimple($postSUSPENDED)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Suspended must be either Yes or No.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if(!ValidationCommonUtils::checkFieldYesNoSimple($postDELETED)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Deleted must be either Yes or No.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if(!ValidationCommonUtils::checkFieldYesNoSimple($postSELFREGISTERED)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="SelfRegistered must be either Yes or No.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if (($postCATEGORY!=FLAG_PRINCIPAL_USER) && ($postCATEGORY!=FLAG_DEPOT_USER) && ($postCATEGORY!=FLAG_SALESAGENT_USER) && ($postCATEGORY!=FLAG_TRUCKDRIVER_USER)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Category Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

include_once($ROOT.$PHPFOLDER."TO/PostingUserTO.php");

$postingUserTO = new PostingUserTO;
$postingUserTO->DMLType=$postDMLTYPE;
$postingUserTO->userId=$postUSERID;
$postingUserTO->userName=$postUN;
$postingUserTO->password="";//$postPWD;
$postingUserTO->fullName=$postFN;
$postingUserTO->userEmail=$postEMAIL;
$postingUserTO->userTel=$postTEL;
$postingUserTO->userCell=$postCELL;
$postingUserTO->suspended=ValidationCommonUtils::translateYNtoBoolean($postSUSPENDED);
$postingUserTO->selfRegistered=ValidationCommonUtils::translateYNtoBoolean($postSELFREGISTERED);
$postingUserTO->deleted=ValidationCommonUtils::translateYNtoBoolean($postDELETED);
$postingUserTO->category=$postCATEGORY;
$postingUserTO->organisationName=$postORGNAME;
$postingUserTO->adminUser = $postADMINUSER;
$postingUserTO->staffUser = $postSTAFFUSER;


if (!isset($_SESSION)) session_start();
$userId = isset($_SESSION['user_id'])?$_SESSION['user_id']:"";

// Do the Actual Posting
include_once($ROOT.$PHPFOLDER."DAO/PostAdminUserDAO.php");
$postAdminUserDAO = new PostAdminUserDAO($dbConn);
$result=$postAdminUserDAO->postUser($postingUserTO,$userId);

if ($result->type==FLAG_ERRORTO_SUCCESS) {
	$result2=mysqli_query($dbConn->connection, "commit");
	if ($postingUserTO->DMLType=="INSERT") {
		$sendResultETO=BroadcastingUtils::sendEmailNewUser($result->identifier,$dbConn);
		$result->description .= $sendResultETO->description;
	}
}


//$dbConn->dbFree();
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

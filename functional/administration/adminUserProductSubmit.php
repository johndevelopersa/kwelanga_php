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
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');

if (!isset($_SESSION)) session_start();

$returnMessage;

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

$postDMLTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DMLTYPE']));
$postUSERID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['USERID']));
$postPRINCIPALID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PRINCIPALID']));
$postPRODCTUID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PRODUCTUID']));

// start of superficial checks. Main checks done in adminPost...php

if (($postDMLTYPE!="DELETE") && ($postDMLTYPE!="INSERT")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

if((!preg_match(GUI_PHP_INTEGER_REGEX,$postUSERID)) || ($postPRODCTUID=="")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid User, or Product ID type - or fields not supplied! Please make sure you have filled in all required fields.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

include_once($ROOT.$PHPFOLDER."TO/PostingUserProductTO.php");
$postingUserProductTO = new PostingUserProductTO;
$postingUserProductTO->DMLType=$postDMLTYPE;
$postingUserProductTO->userId=$postUSERID;
$postingUserProductTO->principalId=$postPRINCIPALID;

$userId = $_SESSION['user_id'];

// Do the Actual Posting
include_once($ROOT.$PHPFOLDER."DAO/PostAdminUserDAO.php");
$postAdminUserDAO = new PostAdminUserDAO($dbConn);
$productArr=explode(",",$postPRODCTUID);
$result = new ErrorTO();
// with multiple processing, we always set to success
$result->type=FLAG_ERRORTO_SUCCESS;
foreach ($productArr as $val) {
	$postingUserProductTO->principalProductUId=$val;
	$result2=$postAdminUserDAO->postUserProduct($postingUserProductTO);
	$result->description .= $result2->type." - ".$val." - ".$result2->description."<br>";
	if ($result2->type==FLAG_ERRORTO_SUCCESS) $result2=mysqli_query($dbConn->connection, "commit");
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
	$returnMessages->description="Problem occurred during posting : Post returned 0 arraysize. Please inform Kwelanga Management.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
  }

print(CommonUtils::getJavaScriptMsg($result));
return; 

?>

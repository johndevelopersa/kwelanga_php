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
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

$returnMessage;

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();


$postDMLTYPE = htmlspecialchars($_POST['DMLTYPE']);
$postUID = (isset($_POST['UID'])) ? (htmlspecialchars($_POST['UID'])) : ("");
$postDESCRIPTION = htmlspecialchars($_POST['DESCRIPTION']);

// start of superficial checks. Main checks done in adminPost....php

if (($postDMLTYPE!="UPDATE") && ($postDMLTYPE!="INSERT")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if (($postDMLTYPE=="UPDATE") && ($postUID=="")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="UPDATE Requires a UID";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

include_once($ROOT.$PHPFOLDER."TO/PostingAreaTO.php");
$postingAreaTO = new PostingAreaTO;
$postingAreaTO->DMLType=$postDMLTYPE;
$postingAreaTO->uId=$postUID;
$postingAreaTO->principalUId=$principalId;
$postingAreaTO->description=trim($postDESCRIPTION);

// Do the Actual Posting of principal chain
include_once($ROOT.$PHPFOLDER."DAO/PostStoreDAO.php");
$postStoreDAO = new PostStoreDAO($dbConn);
$result=$postStoreDAO->postArea($postingAreaTO);

if ($result->type==FLAG_ERRORTO_SUCCESS) {
	$seqPS = $result->identifier;
	$dbConn->dbinsQuery("commit;");
} else {
  $dbConn->dbinsQuery("rollback;");
}

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

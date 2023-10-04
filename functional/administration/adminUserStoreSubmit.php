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

if (!isset($_SESSION)) session_start();


$returnMessage;

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

$postDMLTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DMLTYPE']));
$postUSERID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['USERID'])); // the user you are adding TO
if (isset($_POST['PRINCIPALSTOREUID'])) $postPRINCIPALSTOREUID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PRINCIPALSTOREUID'])); else $postPRINCIPALSTOREUID="";
if (isset($_POST['PRINCIPALID'])) $postPRINCIPALID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PRINCIPALID'])); else $postPRINCIPALID="";
if (isset($_POST['BATCHTYPE'])) $postBATCHTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['BATCHTYPE'])); else $postBATCHTYPE="";
if (isset($_POST['BATCHUSER'])) $postBATCHUSER=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['BATCHUSER'])); else $postBATCHUSER=""; // The user you are modelling on
if (isset($_POST['BATCHCHAIN'])) $postBATCHCHAIN=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['BATCHCHAIN'])); else $postBATCHCHAIN="";

$pSArr=explode(",",$postPRINCIPALSTOREUID);

// start of superficial checks. Main checks done in adminPost...php

if (($postDMLTYPE!="DELETE") && ($postDMLTYPE!="INSERT")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

if ($postPRINCIPALID=="") {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description=" Principal ID type - or fields not supplied! Please make sure you have filled in all required fields.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

if((!preg_match(GUI_PHP_INTEGER_REGEX,$postUSERID)) || (($postPRINCIPALSTOREUID=="") && ($postBATCHTYPE!="USER") && ($postBATCHTYPE!="CHAIN"))) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid User, or PrincipalStore ID type - or fields not supplied! Please make sure you have filled in all required fields.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

if (($postBATCHTYPE!="") && ($postBATCHTYPE!="CHAIN") && ($postBATCHTYPE!="USER")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Batch Type passed";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

if (($postBATCHTYPE=="CHAIN") && ($postBATCHCHAIN=="")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Chain must be selected if you wish to add stores by chain.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

if (($postBATCHTYPE=="USER") && ($postBATCHUSER=="")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Batch User must be selected if you wish to add stores by Batch User.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

$userId = $_SESSION['user_id'];
include_once($ROOT.$PHPFOLDER."DAO/PostAdminUserDAO.php");
$postAdminUserDAO = new PostAdminUserDAO($dbConn);
include_once($ROOT.$PHPFOLDER."TO/PostingUserPrincipalStoreTO.php");
$postingUserPrincipalStoreTO = new PostingUserPrincipalStoreTO;
$postingUserPrincipalStoreTO->DMLType=$postDMLTYPE;
$postingUserPrincipalStoreTO->userId=$postUSERID;
$postingUserPrincipalStoreTO->principalId=$postPRINCIPALID;

// if batch chain/user add, then override the $psArr
if ($postBATCHTYPE=="CHAIN") {
	$pSArr=array();
	include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
	$storeDAO=new StoreDAO($dbConn);
	$principalChainId=$postBATCHCHAIN;
	if ($principalChainId=="") {
		$returnMessages=new ErrorTO;
		$returnMessages->type=FLAG_ERRORTO_ERROR;
		$returnMessages->description="Chain Id not supplied.";
		print(CommonUtils::getJavaScriptMsg($returnMessages));
		return; 
	}
	$mfPC=$storeDAO->getAllStoresByPrincipalChainExclusive($postPRINCIPALID,$principalChainId,$postUSERID);
	foreach ($mfPC as $row) {
		if ($row["status"]==FLAG_STATUS_ACTIVE) $pSArr[]=$row['store_uid'];
	}
} else if ($postBATCHTYPE=="USER") {
		$pSArr=array();
		include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
		$storeDAO=new StoreDAO($dbConn);
		$mfPS=$storeDAO->getUserPrincipalStoreArray($postBATCHUSER,$postPRINCIPALID,"");
		foreach ($mfPS as $row) {
			$pSArr[]=$row['psm_uid'];
		}
	}

$resType=FLAG_ERRORTO_INFO;
$resDesc="The results of the multiple store additions :<BR><BR>";
if (sizeof($pSArr)==0) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="This user already has all the stores for this principal/chain/depot<BR><BR>";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
}
$i=0;
set_time_limit(0); // prevent timeout if large chain/user
foreach ($pSArr as $pUId) {
	$i++;
	$postingUserPrincipalStoreTO->principalStoreUId=$pUId;
	// Do the Actual Posting
	$result=$postAdminUserDAO->postUserPrincipalStore($postingUserPrincipalStoreTO,$userId);
	if ($result->type==FLAG_ERRORTO_SUCCESS) $result2=mysqli_query("commit", $dbConn->connection);
	
	if ($i>2000) {
		//
	} else {
		$resDesc.=$result->type." - "."Store ".$postingUserPrincipalStoreTO->principalStoreUId." - ".$result->description."<BR>";
		if ($i>=2000) $resDesc.="<BR>...*list shortened...*";
	  }
}

if ($postBATCHTYPE=="CHAIN") {
	$resDesc.="<BR><BR>NOTE: only stores linked to the chosen chain/user where YOU have priviledges for were considered.";
}

$result->type=$resType;
$result->description="<div style='overflow:auto; max-height:250px;'>".$resDesc."</div>";
$result->identifier="";


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

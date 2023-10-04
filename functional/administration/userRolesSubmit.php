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
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER."TO/PostingUserRolesTO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostAdminUserDAO.php");


if (!isset($_SESSION)) session_start();
$userId = isset($_SESSION['user_id'])?$_SESSION['user_id']:"";
$principalId = $_SESSION['principal_id'];

$returnMessage;

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;



$postDMLTYPE=(htmlspecialchars($_POST['DMLTYPE']));
$postUSERID=(htmlspecialchars($_POST['USERID']));
$postPRINCIPALID=(htmlspecialchars($_POST['PRINCIPALID']));


$postROLEID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['ROLEID']));

// start of superficial checks. Main checks done in adminPost.php

if (($postDMLTYPE!="DELETE") && ($postDMLTYPE!="INSERT")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if (!preg_match(GUI_PHP_INTEGER_REGEX,$postUSERID)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid User ID type - or fields not supplied! Please make sure you have filled in all required fields.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if (!preg_match(GUI_PHP_INTEGER_REGEX,$postPRINCIPALID)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Principal ID type - or fields not supplied! Please make sure you have filled in all required fields.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if ($postROLEID=="") {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Role ID type - or fields not supplied! Please make sure you have filled in all required fields.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};



$resType=FLAG_ERRORTO_INFO;
$resDesc="The results of the role processing :<BR><BR>";
$i=0;
$pSArr=explode(",",$postROLEID);
foreach ($pSArr as $pUId) {
	$i++;
	$postingUserRolesTO = new PostingUserRolesTO;
	$postingUserRolesTO->DMLType=$postDMLTYPE;
	$postingUserRolesTO->userId=$postUSERID;
	$postingUserRolesTO->principalId=$postPRINCIPALID;
	$postingUserRolesTO->roleId=$pUId;
	// Do the Actual Posting
	$postAdminUserDAO = new PostAdminUserDAO($dbConn);
	$result=$postAdminUserDAO->postUserRole($postingUserRolesTO,$userId);
	if ($result->type==FLAG_ERRORTO_SUCCESS) $result2=mysqli_query($dbConn->connection, "commit");

	if ($i>15) {
		//
	} else {
		$resDesc.=$result->type." - "."Role ".$postingUserRolesTO->roleId." - ".$result->description."<BR>";
		if ($i>=15) $resDesc.="<BR>...*list shortened...*";
	  }
}

$result->type=$resType;
$result->description=$resDesc;
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
	$returnMessages->description="Problem occurred during posting : Post returned 0 arraysize. Please inform Kwelanga Solutions.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
  }

print(CommonUtils::getJavaScriptMsg($result));
return;

?>

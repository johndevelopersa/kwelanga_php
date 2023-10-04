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
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER."TO/PostingUserChainTO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostAdminUserDAO.php");


if (!isset($_SESSION)) session_start();

$returnMessage;


//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();


$postDMLTYPE = (htmlspecialchars($_POST['DMLTYPE']));
$postUSERIDARR = (explode(',',$_POST['USERIDARR']));
$postCHAINUID = (htmlspecialchars($_POST['CHAINUID']));


// start of superficial checks. Main checks done in adminPost...php

if (($postDMLTYPE!="DELETE") && ($postDMLTYPE!="INSERT")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};


if(!count($postUSERIDARR) > 0 || ($postCHAINUID=="")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid User, or Chain ID type - or fields not supplied! Please make sure you have filled in all required fields.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};


 // Do the Actual Posting


// with multiple processing, we always set to success
$result = new ErrorTO();
$result->type = FLAG_ERRORTO_SUCCESS;
$result->description = '<div style="overflow:auto; max-height:250px;">';

$postingUserChainTO = new PostingUserChainTO;
$postAdminUserDAO = new PostAdminUserDAO($dbConn);
$chainArr = explode(",",$postCHAINUID);

foreach($postUSERIDARR as $postUSERID){


  $postingUserChainTO->DMLType = $postDMLTYPE;
  $postingUserChainTO->userId = $postUSERID;
  $userId = $_SESSION['user_id'];


  foreach ($chainArr as $val) {
  	$postingUserChainTO->principalChainUId = $val;
  	$result2 = $postAdminUserDAO->postUserChain($postingUserChainTO,$userId);
  	$result->description .= 'Uid: '.$postUSERID ." : ".$result2->type." - ".$val." - ".$result2->description."<br>";
  	if ($result2->type==FLAG_ERRORTO_SUCCESS){
  	  $result2 = mysqli_query( $dbConn->connection, "commit");
  	} else {
  	  $result->type = FLAG_ERRORTO_ERROR;
  	  $dbConn->dbinsQuery("rollback;");
  	  break;
  	}
  }

}

$result->description .= '</div>';


$dbConn->dbClose();


// check return values
if (sizeof($result)> 0) {
	print(CommonUtils::getJavaScriptMsg($result));
	return;
} else if (sizeof($result)== 0) {
	$returnMessages = new ErrorTO;
	$returnMessages->type = FLAG_ERRORTO_ERROR;
	$returnMessages->description="Problem occurred during posting : Post returned 0 arraysize. Please inform Kwelanga Management.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
  }

print(CommonUtils::getJavaScriptMsg($result));
return;

?>

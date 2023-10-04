<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

$returnMessages = new ErrorTO;

$postPRODUCTID = isset($_POST['PRODUCTID']) ? (htmlspecialchars($_POST['PRODUCTID'])) : false;
$postSTOREID = isset($_POST['STOREID']) ? (htmlspecialchars($_POST['STOREID'])) : false;
$postDOCTYPE = isset($_POST['DOCTYPE']) ? (htmlspecialchars($_POST['DOCTYPE'])) : false;

if (($postPRODUCTID===false) || ($postSTOREID===false)) {
	$returnMessages->type=FLAG_ERRORTO_SUCCESS;
	$returnMessages->description="Value Pass Error.";
	$returnMessages->identifier="Value Pass Error.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
}


//SET THE ID2 => PRODUCT UID TO RETURN TO SCREEN, CROSSED AJAX FIX
$returnMessages->identifier2 = $postPRODUCTID;

$adminDAO = new AdministrationDAO($dbConn);
$hasRolePricing = $adminDAO->hasRole($userId,$principalAliasId,ROLE_VIEW_PRICE);
if (!($hasRolePricing===true)) {
	$returnMessages->type=FLAG_ERRORTO_SUCCESS; // so that screen dispalys description without alarm.
	$returnMessages->description="Price Found. No Permission.";
	$returnMessages->identifier="Price Found.No Permission.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
}

// no need to check for product permissions
$productDAO=new ProductDAO($dbConn);
$mfP=$productDAO->getActivePricesForProduct($principalAliasId,$postSTOREID,$postPRODUCTID);
if (sizeof($mfP)==0) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="No Pricing";
	$returnMessages->identifier="";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
}

$returnMessages->type=FLAG_ERRORTO_SUCCESS;
$returnMessages->description="Successfully Retrieved Price.";
if ($postDOCTYPE === DT_MCREDIT_OTHER){
	 $returnMessages->identifier=round(($mfP[0]['price'] / $mfP[0]['items_per_case']),2);
} else {
   $returnMessages->identifier=($mfP[0]['price']);	
}
print(CommonUtils::getJavaScriptMsg($returnMessages));

$dbConn->dbClose();
?>

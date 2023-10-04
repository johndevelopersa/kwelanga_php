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

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

$postPRODUCTID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PRODUCTID']));
$postPRODUCTUIDTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PRICETYPE']));

$returnMessages = new ErrorTO;

$adminDAO = new AdministrationDAO($dbConn);
$hasRolePricing = $adminDAO->hasRole($userId,$principalId,ROLE_VIEW_PRICE);
if (!($hasRolePricing===true)) {
	$returnMessages->type=FLAG_ERRORTO_SUCCESS; // so that screen dispalys description without alarm.
	$returnMessages->description="Price Found. No Permission.";
	$returnMessages->identifier="Price Found.No Permission.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
}

// no need to check for product permissions
$productDAO=new ProductDAO($dbConn);
$mfP=$productDAO->getGenericChainDefaultPrice($principalId, $postPRODUCTID,$postPRODUCTUIDTYPE);
if (sizeof($mfP)==0) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="No Prices";
	$returnMessages->identifier="";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
}

$returnMessages->type=FLAG_ERRORTO_SUCCESS;
$returnMessages->description="Successfully Retrieved Price.";
$returnMessages->identifier=$mfP[0]['price'];
print(CommonUtils::getJavaScriptMsg($returnMessages));

$dbConn->dbClose();
?>

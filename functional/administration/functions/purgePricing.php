<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php"); 
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostProductDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

$postPERIOD=mysql_real_escape_string(htmlspecialchars($_POST['PERIOD']));

$adminDAO=new AdministrationDAO($dbConn);

$errorTO = new ErrorTO;

// check roles
$hasRole = $adminDAO->hasRole($userId,$principalId,ROLE_MODIFY_PRICE);
if (!$hasRole) {
	$errorTO->type = FLAG_ERRORTO_ERROR;
	$errorTO->description = 'You do not have permissions to run this routine.';
	print(CommonUtils::getJavaScriptMsg($errorTO));
	$dbConn->dbClose();
	return;
}

$postProductDAO = new PostProductDAO($dbConn);
$eTO=$postProductDAO->purgePricing($postPERIOD);
if ($eTO->type==FLAG_ERRORTO_SUCCESS) $dbConn->dbinsQuery("commit");
print(CommonUtils::getJavaScriptMsg($eTO));

$dbConn->dbClose();
?>

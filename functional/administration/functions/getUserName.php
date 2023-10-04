<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php"); 
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

$postFULLNAME=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['FULLNAME']));

$name=trim($postFULLNAME);
$after=preg_replace('/\s+/','',$name);
$after=preg_replace('/[^a-zA-Z0-9]/', '', $after);
$after=strtolower($after);

if (strlen($after)>6) $after=substr($after,0,6);

$returnMessages=new ErrorTO;
if (strlen($after)<5) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="FullName needs atleast 5 alpha-numeric characters to form a proper UserName.";
	$returnMessages->identifier=$after;
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
}

$administrationDAO=new AdministrationDAO($dbConn);
// assign the number and check for uniqueness
$i=0;
do {
	$i++;
	$tempName=$after.$i;
	$result=$administrationDAO->getUsersByUserNameArray($tempName);
} while (sizeof($result)>0);

$returnMessages->type=FLAG_ERRORTO_SUCCESS;
$returnMessages->description="Successful.";
$returnMessages->identifier=$tempName;
print(CommonUtils::getJavaScriptMsg($returnMessages));

$dbConn->dbClose();
?>

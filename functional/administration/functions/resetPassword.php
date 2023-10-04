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

$postUserId=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['USERID']));

$adminDAO=new AdministrationDAO($dbConn);
$eTO=$adminDAO->resetPassword($postUserId, "Y");
if ($eTO->type==FLAG_ERRORTO_SUCCESS) $dbConn->dbinsQuery("commit");
print(CommonUtils::getJavaScriptMsg($eTO));

$dbConn->dbClose();
?>

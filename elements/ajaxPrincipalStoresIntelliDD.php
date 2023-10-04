<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."elements/intelliDDElement.php");

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

if (!isset($_SESSION)) session_start() ;
$principalId =  $_SESSION['principal_id'] ;
$userId     =  $_SESSION['user_id'];


if (isset($_POST['PRINCIPALSTOREID'])) $postPRINCIPALSTOREID=mysql_real_escape_string(htmlspecialchars($_POST['PRINCIPALSTOREID'])); else $postPRINCIPALSTOREID="";
if (isset($_POST['TAGID'])) $postTAGID=mysql_real_escape_string(htmlspecialchars($_POST['TAGID'])); else $postTAGID="";
if (isset($_POST['ONCHANGE'])) $postONCHANGE=mysql_real_escape_string(htmlspecialchars($_POST['ONCHANGE'])); else $postONCHANGE="";
if (isset($_POST['CALLBACK'])) $postCALLBACK=mysql_real_escape_string(htmlspecialchars($_POST['CALLBACK'])); else $postCALLBACK="";

IntelliDDElement::displayStoreIDD($postTAGID,$postPRINCIPALSTOREID,"N","N",$postONCHANGE,null,null,$dbConn,$principalId,$userId,$postCALLBACK);

?>

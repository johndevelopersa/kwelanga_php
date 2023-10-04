<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

if (isset($_POST['USERID'])) $postUSERID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['USERID'])); else $postUSERID="";
if (isset($_POST['PRINCIPALID'])) $postPRINCIPALID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PRINCIPALID'])); else $postPRINCIPALID="";
if (isset($_POST['TAGID'])) $postTAGID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['TAGID'])); else $postTAGID="";
if (isset($_POST['ONCHANGE'])) $postONCHANGE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['ONCHANGE'])); else $postONCHANGE="";

BasicSelectElement::getUserPrincipalDD($postTAGID,$postPRINCIPALID,"N","N",$postONCHANGE,null,null,$dbConn,$postUSERID);

?>

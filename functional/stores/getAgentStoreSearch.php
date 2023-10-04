<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
ob_start(); //Turn on output buffering
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

if (!isset($_SESSION)) session_start();
$userId=$_SESSION["user_id"];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

$postSEARCHCRITERIA=mysql_real_escape_string(htmlspecialchars($_POST['SEARCHCRITERIA']));

$sCArr=explode(",",strtolower($postSEARCHCRITERIA));

if ($_SESSION["category"]!=FLAG_SALESAGENT_USER) {
	echo "Sorry, this functionality is limited to Sales Agents only";
	return;
}
if (trim($postSEARCHCRITERIA)=="") {
	echo "Search Criteria cannot be blank.";
	return;
}
$storeDAO = new StoreDAO($dbConn);
$mfSAS = $storeDAO->getUserAgentPrincipalStoreArray($userId, $sCArr);
echo "<TABLE style='text-align:left;'>";
echo "<TR>
		  <TH>Store Name</TH>
		  <TH>Delivery Addr 1</TH>
		  <TH>Depot Name</TH>
		  <TH>Chain Name</TH>
		  <TH>Principal</TH>
		  <TH>Delivery Day</TH>
		  </TR>";
foreach ($mfSAS as $s) {
	echo "<TR>
		  <TD nowrap>{$s["store_name"]}</TD>
		  <TD nowrap>{$s["deliver_add1"]}</TD>
		  <TD nowrap>{$s["depot_name"]}</TD>
		  <TD nowrap>{$s["chain_name"]}</TD>
		  <TD nowrap>{$s["principal"]}</TD>
		  <TD nowrap>{$s["delivery_day"]}</TD>
		  </TR>";
}
echo "</TABLE>";

$dbConn->dbClose();

$htmlBody = ob_get_clean();
$htmlBody = gzencode($htmlBody, 9, FORCE_GZIP);
header ("Content-Encoding: gzip");
header ('Content-Length: '.strlen($htmlBody));
echo $htmlBody;
?>
<?php
/* NB:
 * This should only be accessible by a depot user from a depot principal
 */
 
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."elements/basicInputElement.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");

if (!isset($_SESSION)) session_start() ;
$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

if (isset($_GET['DOCMASTID'])) $postDOCMASTID=mysql_real_escape_string(htmlspecialchars($_GET['DOCMASTID']));
else if (isset($_POST['DOCMASTID'])) $postDOCMASTID=mysql_real_escape_string(htmlspecialchars($_POST['DOCMASTID']));
else $postDOCMASTID="";

// don't do security checks on principal because not document details are revealed, and on submit, the security is then validated
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
$transactionDAO = new TransactionDAO($dbConn);
$mfT = $transactionDAO->getDocumentDepotAuditLog($userId, $postDOCMASTID); // principal security check done inside 

if (sizeof($mfT)==0) {
	echo "No Audit Log found.";
	return;
}


echo "<html>
			<head>
				<LINK href='".$ROOT.$PHPFOLDER."css/default.css' rel='stylesheet' type='text/css'>
			</head>
			<body>";
			
echo "<h3><i>Event Audit Log for Document...</i></h3>
			<table class='tableReset'>
			<tr><th>Event Date</th><th>By Person</th><th>Comment</th></tr>";
foreach ($mfT as $r) {
  echo "<tr><td>{$r["activity_date"]}</td><td>{$r["full_name"]}</td><td>{$r["comment"]}</td></tr>";
}
echo "</table>";

echo "</body>
			</html>";
?>
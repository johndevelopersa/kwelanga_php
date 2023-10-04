<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

if (!isset($_SESSION)) session_start();

// session sometimes times out, so put this in
if (!isset($_SESSION["user_id"])) {
  echo "N";
  return;  
}

$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();

$transactionDAO = new TransactionDAO($dbConn);

$cnt = $transactionDAO->getUnacceptedDepotOrdersCount($userId);

if (intval($cnt)>0) {
	echo "Y";
} else {
	echo "N";
}

return;

?>

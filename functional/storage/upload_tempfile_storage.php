<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

//LIBRARIES
require_once $ROOT.$PHPFOLDER."libs/storage/StorageClass.php" ;
require_once $ROOT.$PHPFOLDER."properties/Constants.php";

include_once($ROOT.$PHPFOLDER."DAO/TaskManDAO.php");

if (!isset($_SESSION)) session_start() ;
$userUId     = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;

//Create new database object
$dbConn  = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;

	

echo "creating storage class for multiple uses\n";
$store = (new StorageClass);

// Get Taskman user to log onto FTP

      $TaskManDAO = new TaskManDAO($dbConn);
      $taskftp = $TaskManDAO->getUserFTPlogin($userUId);

echo "<pre>";

print_r($taskftp);

echo "Connecting to storage FTP port...";
$conn_id = ftp_connect($taskftp[0]['host'], $taskftp[0]['port']); 

echo "<br>";
echo $conn_id;

echo "<br>";
echo "\tSuccessfully Connected!\n";


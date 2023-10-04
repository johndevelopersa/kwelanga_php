<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/payments/MatchAllInvoiceCredits.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/PaymentsDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostPaymentsDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

$errorTO = new ErrorTO;

if (!isset($_SESSION)) session_start();
$_SESSION['user_id'] = SESSION_ADMIN_USERID;

//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();

$PostPaymentsDAO   = new PostPaymentsDAO($dbConn);
$errorTO           = $PostPaymentsDAO->AutoMatchCredits('207');

echo $errorTO->description;

echo '<br>';
echo "End";
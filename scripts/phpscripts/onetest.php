
<?php
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/onetest.php

// $start_date = new DateTime(date('Y-m-d H:i:s'));

$start_date = new DateTime('2023-04-24 06:30:59');

$since_start = $start_date->diff(new DateTime(date('Y-m-d H:i:s')));
echo $since_start->i.' minutes total<br>';

if($since_start->i < 60) {
	echo "Run<br>";
} else {
	echo "Don't Run <br>";
}
?>                  

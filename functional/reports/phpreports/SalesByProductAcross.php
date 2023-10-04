<?php
// http://www.kwelangasolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/SalesByProductAcross.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

global $paramsArr, $principalId;

$dbConn = new dbConnect();
$dbConn->dbConnection();

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

$StartDate = $paramsArr['p1'];
$EndDate   = $paramsArr['p2'];
$ChainList = $paramsArr['p3'];
$DocStatus = $paramsArr['p4'];
$ProdList  = $paramsArr['p5'];

$TransactionDAO = new TransactionDAO($dbConn);
$gSBP = $TransactionDAO->getSalesByProduct($principalId, $StartDate, $EndDate, $ChainList, $DocStatus, $ProdList  );

// Create product field array for temporary table

$prodcode = array();

$bldsql = "DROP TABLE IF EXISTS temp_sales_" . $userId . ";   ";

$dtresult = $dbConn->dbQuery($bldsql);

$bldsql = "CREATE TABLE temp_sales_" . $userId . " (`Store-Product_Code` VARCHAR(60) NULL,";

foreach($gSBP as $row) {
        if (!in_array($row['product_code'], $prodcode)) {
           $bldsql = $bldsql . ' `' . $row['product_code'] . "` VARCHAR(60) NULL,";
           array_push($prodcode,$row['product_code']);
        }
}

$bldsql =  $bldsql. " Report_Start_Date DATE NULL,";
$bldsql =  $bldsql. " Report_End_Date DATE NULL )";
// $sql = str_replace("NULL,XX","NULL ", $bldsql);
// $sql = $sql
$btresult = $dbConn->dbQuery($bldsql);
$isql='';
$ststore = '';

$isql = "INSERT INTO temp_sales_" . $userId . " (`Store-Product_Code`) VALUES (substr('Product Description',1,50));";
$utresult = $dbConn->dbQuery($isql);


foreach($gSBP as $row1) {
	 	   $isql = "UPDATE temp_sales_" . $userId . " SET `" . $row1['product_code'] ."`='".  $row1['product_description'] ."' WHERE  `Store-Product_Code` = 'Product Description';";
       $utresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");
	 }

foreach($gSBP as $row1) {
	 if ($ststore <> $row1['storeUid']) {
       $isql = "INSERT INTO temp_sales_" . $userId . " (`Store-Product_Code`, `" . $row1['product_code'] ."`, `Report_Start_Date`, `Report_End_Date`) VALUES (' ". $row1['deliver_name'] ."', '".  $row1['quantity'] ."', '". $StartDate ."', '". $EndDate . "');";
       $itresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");
       $ststore = $row1['storeUid'] ; 
   } else {
	 	   $isql = "UPDATE temp_sales_" . $userId . " SET `" . $row1['product_code'] ."`='".  $row1['quantity'] ."' WHERE  `Store-Product_Code` = '". $row1['deliver_name'] ."';";
       $utresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");
	 }
	 
}

// Temp Table SQL

$csv_export = '';

$tsql = "select * from temp_sales_" . $userId . " where 1";
$utresult = $dbConn->dbGetAll($tsql);

if (count($utresult) == 0) {
	   ?>
     <script type='text/javascript' >parent.showMsgBoxError("No Rows Selected<BR><BR>")</script> 
	   <?php
	   return;	
}

foreach (array_keys($utresult[0]) as $arow) {
	$csv_export.= $arow . ',';
}
  $csv_export.= "\n";

foreach ($utresult as $brow) {
	$csv_export.= implode(',',$brow) . "\n";
}
$fileName = "Sales_Summary_By_Product.csv";

  header("Content-Description: File Transfer");
  header("Content-Disposition: attachment; filename=\"".$fileName."\"");
  header("Content-Type: application/force-download");
  echo $csv_export;

$bldsql = "DROP TABLE IF EXISTS temp_sales_" . $userId . ";   ";

$dtresult = $dbConn->dbQuery($bldsql);

  
?>
<?php
// http://www.kwelangasolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/SalesByProductAcross.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/ReportsDAO.php");

global $paramsArr, $principalId;

$dbConn = new dbConnect();
$dbConn->dbConnection();

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

$StartDate     = $paramsArr['p2'];
$EndDate       = $paramsArr['p3'];
$WarehouseList = $paramsArr['p4'];
$statusList    = $paramsArr['p5'];
$PrincipalList = $paramsArr['p1'];
$ReportBy      = $paramsArr['p6'];

$ReportsDAO = new ReportsDAO($dbConn);
$tSales = $ReportsDAO->getTransporterSales("", $PrincipalList, $statusList, $StartDate, $EndDate, $WarehouseList, $ReportBy);

$exclusiveTotal = 0;
$totArr = array();

foreach ($tSales as $row) {
	
      $totArr += [$row['whUid'] => round($row['Exclusive Total'])];
      $exclusiveTotal = $exclusiveTotal + round($row['Exclusive Total']);
}

//echo "<pre>";
//print_r($totArr);
//die();
$ReportsDAO = new ReportsDAO($dbConn);
$sSales = $ReportsDAO->getTransporterSales("T", $PrincipalList, $statusList, $StartDate, $EndDate, $WarehouseList, $ReportBy);

// Create product field array for temporary table

$bldsql = "DROP TABLE IF EXISTS temp_sales_" . $userId . ";   ";

$dtresult = $dbConn->dbQuery($bldsql);

$bldsql = "CREATE TABLE temp_sales_" . $userId . " (`Region`             VARCHAR(60) NULL,
                                                    `Exclusive Total`    VARCHAR(20) NULL,	
                                                    `Vat Total`          VARCHAR(20) NULL,	
                                                    `Invoice Total`      VARCHAR(20) NULL,	
                                                    `Start Date` VARCHAR(20) NULL,	
                                                    `End Date`   VARCHAR(20) NULL,	
                                                    `Transporter`        VARCHAR(60) NULL,
                                                    `Contribution`       VARCHAR(20) NULL)";

$utresult = $dbConn->dbQuery($bldsql);
$dbConn->dbQuery("commit");

// Insert Headings
/* $isql = "INSERT INTO temp_sales_" . $userId . " (`Region`,
                                                 `Exclusive Total`,
                                                 `Vat Total`,
                                                 `Invoice Total`,
                                                 `Start Date`,
                                                 `End Date`,
                                                 `Transporter`,
                                                 `Contribution`) 
        VALUES ('Region',
                'Exclusive Total',
                'Vat Total',
                'Invoice Total',
                'Report: Start Date',
                'Report: End Date',
                'Transporter',
                'Contribution')";                                                  

$utresult = $dbConn->dbQuery($isql);
$dbConn->dbQuery("commit");
*/

$storeDep = '';

foreach($sSales as $row) {
	
	     if($storeDep <> $row['whUid'] && $storeDep <> '') {
	     	
            $isql = "INSERT INTO temp_sales_" . $userId . " (`Region`,
                                                        `Exclusive Total`) 
                     VALUES ('Warehouse Total ',
                             ' " . $totArr[$storeDep] . "')";        
            $utresult = $dbConn->dbQuery($isql);
            $dbConn->dbQuery("commit");       	     	

            $isql = "INSERT INTO temp_sales_" . $userId . " (`Region`,
                                                        `Exclusive Total`) 
                     VALUES (' ',
                             ' ')";        
            $utresult = $dbConn->dbQuery($isql);
            $dbConn->dbQuery("commit");
	     }
       $storeDep = $row['whUid'];
       $isql = "INSERT INTO temp_sales_" . $userId . " (`Region`,
                                                 `Exclusive Total`,
                                                 `Vat Total`,
                                                 `Invoice Total`,
                                                 `Start Date`,
                                                 `End Date`,
                                                 `Transporter`,
                                                 `Contribution`) 
                VALUES ('" . $row['Warehouse']        . "',
                        '" . $row['Exclusive Total']  . "',
                        '" . $row['VAT Total']        . "',
                        '" . $row['Invoice Total']    . "',
                        '" . $StartDate                     ."',
                        '" . $EndDate                       ."',
                        '" . $row['Transporter'] . "',
                        '" . round($row['Exclusive Total'] / $totArr[$row['whUid']] * 100,2) ."')"; 
 
       $utresult = $dbConn->dbQuery($isql);
       
       $dbConn->dbQuery("commit");
       
       $isql = "INSERT INTO temp_sales_" . $userId . " (`Region`,
                                                        `Exclusive Total`) 
                VALUES (' ',
                        ' ')";        
       $utresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");       

}

       $isql = "INSERT INTO temp_sales_" . $userId . " (`Region`,
                                                   `Exclusive Total`) 
                VALUES ('Warehouse Total ',
                        ' " . $totArr[$storeDep] . "')";        
       $utresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");       	     	

       $isql = "INSERT INTO temp_sales_" . $userId . " (`Region`,
                                                   `Exclusive Total`) 
                VALUES (' ',
                        ' ')";        
       $utresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");



       $isql = "INSERT INTO temp_sales_" . $userId . " (`Region`,
                                                 `Exclusive Total`,
                                                 `Vat Total`,
                                                 `Invoice Total`,
                                                 `Start Date`,
                                                 `End Date`,
                                                 `Transporter`,
                                                 `Contribution`) 
                VALUES ('TOTAL',
                        '" . round($exclusiveTotal,0)       . "',
                        ' ',
                        ' ',
                        ' ',
                        ' ',
                        ' ',
                        ' ')"; 
 
       $utresult = $dbConn->dbQuery($isql);

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
$fileName = "Nellwyn Transporter Sales.csv";

  header("Content-Description: File Transfer");
  header("Content-Disposition: attachment; filename=\"".$fileName."\"");
  header("Content-Type: application/force-download");
  echo $csv_export;

$bldsql = "DROP TABLE IF EXISTS temp_sales_" . $userId . ";   ";

$dtresult = $dbConn->dbQuery($bldsql);

  
?>
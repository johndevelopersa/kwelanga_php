<?php
// http://www.kwelangasolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/reports/phpreports/DetailedCashSummaryReport.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');

global $paramsArr, $principalId, $outputType;

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();
//  0123456789012345678901234567890
//  2018022018-04-012018-04-30



$StartDate   = $paramsArr['p1'];
$EndDate     = $paramsArr['p2'];
$chain       = $paramsArr['p3'];
$wh          = $paramsArr['p4'];


// echo $StartDate;

// Create temporary table user specfic

$ClosingBal = $CashOut = $CashIn = 0;

$bldsql = "DROP TABLE IF EXISTS store_orders_temp_" . $userId . ";   ";

$dtresult = $dbConn->dbQuery($bldsql);
$dbConn->dbQuery("commit");

$bldsql = "CREATE TABLE store_orders_temp_" . $userId . " (`FLD1` INT(10) NULL,
                                     `FLD2` VARCHAR(10) NULL,
                                     `FLD3` VARCHAR(15) NULL,
                                     `FLD4` VARCHAR(15) NULL);";
                                     
$dtresult = $dbConn->dbQuery($bldsql);
$dbConn->dbQuery("commit");


$isql = "INSERT INTO store_orders_temp_" . $userId . " (store_orders_temp_" . $userId . ".FLD1, 
                                                        store_orders_temp_" . $userId . ".FLD2, 
                                                        store_orders_temp_" . $userId . ".FLD3, 
                                                        store_orders_temp_" . $userId . ".FLD4)
                          (SELECT dh.principal_store_uid, 
                                  dh.order_date, 
                                  dh.cases, 
                                  dh.exclusive_total
                           FROM document_master dm, 
                                document_header dh
                           where dm.uid = dh.document_master_uid
                           AND   dm.principal_uid = " . $principalId . "
                           AND   dm.document_type_uid in (" .DT_ORDINV. ", " .DT_ORDINV_ZERO_PRICE. " )
                           AND   dh.order_date BETWEEN '" . $StartDate . "' AND  '" . $EndDate . "')";
                           

$itresult = $dbConn->dbQuery($isql);
$dbConn->dbQuery("commit");


$isql = "SELECT pcm.description AS 'Chain', 
                d.name AS 'Warehouse',
                psm.deliver_name AS 'Customer', 
                t.FLD2 AS 'Order Date',
                t.FLD3 AS 'Cases',
                round(t.FLD4,2) AS 'Excl Value',
                '" . $StartDate . "' AS 'Report Start',
                '" . $EndDate   . "' AS 'Report End'
         FROM .principal_store_master psm
         LEFT JOIN store_orders_temp_" . $userId . " t ON t.FLD1 = psm.uid
         LEFT JOIN .principal_chain_master pcm on pcm.uid = psm.principal_chain_uid
         LEFT JOIN .depot d ON d.uid = psm.depot_uid
         WHERE psm.principal_uid = " . $principalId . "
         AND   psm.alt_principal_chain_uid in (" . $chain . ")
         AND   psm.depot_uid in (" . $wh . ")
         AND   psm.`status` = 'A'
         ORDER BY pcm.description, psm.deliver_name, t.FLD2 ";

$utresult = $dbConn->dbGetAll($isql);

// print_r($utresult);

if (count($utresult) == 0) {
	   ?>
     <script type='text/javascript' >parent.showMsgBoxError("No Rows Selected<BR><BR>")</script> 
	   <?php
	   return;	
}


foreach (array_keys($utresult[0]) as $arow) {
  if (substr($arow,0,5) <> 'Field' && substr($arow,0,4) <> 'sort' ) {
     $csv_export.= $arow . ',';
  }

}
  $csv_export.= "\n";

foreach ($utresult as $brow) {
	$csv_export.= implode(',',$brow) . "\n";
}
$fileName = "Store Orders Report.csv";

  header("Content-Description: File Transfer");
  header("Content-Disposition: attachment; filename=\"".$fileName."\"");
  header("Content-Type: application/force-download");
  echo $csv_export;
  


?>
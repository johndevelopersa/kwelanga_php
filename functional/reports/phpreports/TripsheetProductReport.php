<?php
// http://www.kwelangasolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/reports/phpreports/TripsheetProductReport.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');

global $paramsArr, $principalId, $outputType;

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();

echo "<pre>";

$isql = "SELECT th.tripsheet_number,
                th.tripsheet_date,
                p.name AS 'Principal',
                d.name AS 'Depot',
                pp.product_code,
                pp.product_description,
                sum(dd.document_qty) as 'Qty',
                t.name AS 'Transporter'
          FROM .tripsheet_header th
          LEFT JOIN .tripsheet_detail td ON th.uid = td.tripsheet_master_uid
          LEFT JOIN .document_master dm on dm.uid  = td.document_master_uid
          LEFT JOIN .document_header dh ON dm.uid = dh.document_master_uid
          LEFT JOIN .document_detail dd ON dm.uid = dd.document_master_uid
          LEFT JOIN .principal p ON p.uid = dm.principal_uid
          LEFT JOIN .depot d ON d.uid = dm.depot_uid
          LEFT JOIN .principal_product pp ON pp.uid = dd.product_uid
          LEFT JOIN .transporter t ON t.uid = th.transporter_id
          WHERE th.depot_uid = 393
          AND   th.tripsheet_date BETWEEN '2023-01-15' AND '2023-01-17'
          GROUP BY th.tripsheet_number, dm.principal_uid, pp.product_code";

          $utresult = $dbConn->dbGetAll($isql);

if (count($utresult) == 0) {
	   ?>
     <script type='text/javascript' >parent.showMsgBoxError("No Rows Selected<BR><BR>")</script> 
	   <?php
	   return;	
}
$headingLine = array();
$detailLine  = array();
$newPrincipal = '';
$csv_export = '';

foreach($utresult as $row) {
	    if($newPrincipal != $row['Principal']) {
	    	    if($newPrincipal != '') {
	    	    	echo "<pre>";
	    	    	echo "<br>";
	    	    	//print_r($headingLine);
	    	    	echo "<br>";
	    	    	//print_r($detailLine);
	    	    	
	    	    	
	    	    	$csv_export.= implode(',',$headingLine) . "\n";
	    	    	$csv_export.= implode(',',$detailLine) . "\n";
	    	    	
	    	    	$headingLine = array();
	    	    	$detailLine  = array();
	    	    	
	    	    }
	    	    array_push($headingLine,$row['tripsheet_number']);
	    	    array_push($headingLine,$row['tripsheet_date']);
	    	    array_push($headingLine,$row['Transporter']);
	    	    array_push($headingLine,$row['Principal']);
	    	    array_push($detailLine,'Totals');
	    	    array_push($detailLine,'');
	    	    array_push($detailLine,'');
	    	    array_push($detailLine,'');
	    	    $newPrincipal = $row['Principal'];
	    }
	    array_push($headingLine,$row['product_description']);
	    array_push($detailLine,$row['Qty']);
	
}

$csv_export.= implode(',',$headingLine) . "\n";
$csv_export.= implode(',',$detailLine) . "\n";

echo "<br>";
echo "<br>";
echo $csv_export;




?>
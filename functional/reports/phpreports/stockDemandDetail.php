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
$principalId  = $_SESSION['principal_id'];

$WarehouseList = $paramsArr['p1'];
$PrincipalList = $paramsArr['p2'];

if(trim($PrincipalList) == '') {
    $PrincipalList = $principalId;	
}

$ReportsDAO = new ReportsDAO($dbConn);
$tSales = $ReportsDAO->getStockDemand($PrincipalList, $WarehouseList);

if (count($tSales) == 0) {
	   ?>
     <script type='text/javascript' >parent.showMsgBoxError("No Rows Selected<BR><BR>")</script> 
	   <?php
	   return;	
}

$headingArray = array('Principal',
                 'Warehouse',
                 'Document Number',
                 'Store',
                 'Product Code',
                 'Product',
                 'Ordered Qty',
                 'Days',
                 'Closing Stock',
                 'Value');
                 
$spaceArray   = array('',
                 '',
                 '',
                 '',
                 '',
                 '',
                 '',
                 '',
                 '',                 
                 '');

$csv_export = '';

$csv_export.= implode(',',$headingArray) . "\n";

$csv_export.= "\n";

$storeRow = '';
$storeStoc = $storeNp = $totalQty = 0;

foreach ($tSales as $row) {
	
        if($storeRow <> $row['product_code'] && $storeRow <>'' ) {
              $totalArray = array('Product Total ' . $storeRow,
                            '',
                            '',
                            '',
                            '',
                            '',
                            $totalQty,
                            '',
                            $storeStoc,
                            round($totalQty * trim($storeNp),2));
              
              $storeStoc = $storeNp = $totalQty = 0;
              
              $csv_export.= implode(',',$spaceArray) . "\n";
              
              $csv_export.= implode(',',$totalArray) . "\n";
              
              $csv_export.= implode(',',$spaceArray) . "\n";
              
        }
        
        $storeRow  = $row['product_code'];        
        $storeStoc = $row['ClosingStock'];        
        $storeNp   = $row['NetPrice'];
        
        $totalQty = $totalQty + $row['Qty'];
	
        $detailArray = array($row['Principal'],
                        $row['Warehouse'],
                        $row['Document_Number'],
                        $row['deliver_name'],
                        $row['product_code'],
                        $row['product_description'],
                        $row['Qty'],
                        $row['Days']);
	
        $csv_export.= implode(',',$detailArray) . "\n";
}

$totalArray = array('Product Total ' . $row['product_code'],
                            '',
                            '',
                            '',
                            '',
                            '',
                            $totalQty,
                            '',
                            $storeStoc,
                            round($totalQty * trim($storeNp),2));
              
              $storeStoc = $storeNp = $totalQty = 0;
              
              $csv_export.= implode(',',$spaceArray) . "\n";
              
              $csv_export.= implode(',',$totalArray) . "\n";
              
              $csv_export.= implode(',',$spaceArray) . "\n";

$fileName = "Detail_Stock_Demand_Report.csv";

  header("Content-Description: File Transfer");
  header("Content-Disposition: attachment; filename=\"".$fileName."\"");
  header("Content-Type: application/force-download");

  echo $csv_export;
  
?>
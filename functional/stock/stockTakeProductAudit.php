<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'DAO/StockDAO.php');

include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");


if (!isset($_SESSION)) session_start;
$principalId = $_SESSION['principal_id'];
$principalName = $_SESSION['principal_name'];
$depotId = $_SESSION['depot_id'];
$depotName = $_SESSION['depot_name'];
$postPRODUCTID = (isset($_GET['PRODUCTID']))?$_GET['PRODUCTID']:FALSE;

if($postPRODUCTID === false){
  echo 'Error: invalid/empty supplied product!';
  return;
}


$dbConn = new dbConnect();
$dbConn->dbConnection();

$productDAO = new ProductDAO($dbConn);
$pArr = $productDAO->getPrincipalProductItem($principalId, $postPRODUCTID);

$stockDAO = new StockDAO($dbConn);
$lastStockTake = $stockDAO->getPreviousStockTakeDate($principalId, $depotId);

$tranDAO = new TransactionDAO($dbConn);
$tArr = $tranDAO->getStockItemMovement($principalId, $depotId, $postPRODUCTID, $lastStockTake);

echo "<script type='text/javascript' language='javascript' src='". $ROOT.$PHPFOLDER ."js/jquery.js'></script>";
echo '<LINK href="'. $DHTMLROOT.$PHPFOLDER .'css/default.css" rel="stylesheet type="text/css">';
echo '<h2>Stock Item Movement</h2>';
echo '<u><strong>Information</strong></u><br>';
echo 'Product : <strong>' . $pArr[0]['product_code'] . ' -- ' . $pArr[0]['product_description'] . ' (' . $pArr[0]['uid'] . ')' . '</strong><br>';
echo 'From last stock take of : <strong>'.(($lastStockTake==false)?'*unknown':$lastStockTake).'</strong><br>';
echo 'at depot : <strong>'.$depotName.'</strong><br>';
echo 'for : <strong>'.$principalName.'</strong><br><br>';

echo '<script type="text/javascript">';
echo '$(document).ready(function(){
      $(".report_table tbody tr").hover(
            function () {$(this).children("td").css("background-color","#F3F781");},
            function () {$(this).children("td").css("background-color","");}
            );
      });';
echo '</script>';

echo '<u><strong>Movement</strong></u><br>';
if(count($tArr)==0){
  echo 'no movements found for this item!';
} else {

  include_once($ROOT.$PHPFOLDER.'DAO/ReportDAO.php');
  $reportDAO = new ReportDAO($dbConn);
  echo $reportDAO->reportSQL_arrayToHTML($tArr);

}

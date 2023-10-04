<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/Update stock AllocatedInpick.php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once ($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

$principalList = ((isset($_GET["PRINCIPALLIST"]))?$_GET["PRINCIPALLIST"]:"");
$wareHouseList = ((isset($_GET["WAREHOUSLIST"]))?$_GET["WAREHOUSLIST"]:"");
$dInterval     = ((isset($_GET["DINTERVAL"]))?$_GET["DINTERVAL"]:"");

if($principalList == 'Select All Principals') {
   $principalsToCheck = "";	
} else {
	 $principalsToCheck = $principalList;	
}
if($wareHouseList == 'Select All Warehouses') {
   $depotList = "";	
} else {
	 $depotList = $wareHouseList;	
}
$statusLoop        = Array("allocations", "in_pick");
$interval          = $dInterval;

$updatedRow        = "";


foreach($statusLoop as $loopRow) {	
	    if($loopRow =="allocations" ) {	    	
	   	     $statusList  = array(DST_UNACCEPTED, DST_ACCEPTED);
	   	     $docTypeList = array(DT_ORDINV,DT_DELIVERYNOTE,DT_ORDINV_ZERO_PRICE);
      } elseif ($loopRow == "in_pick") {
	   	     $statusList  = array(DST_INPICK);
	   	     $docTypeList = array(DT_ORDINV, DT_DELIVERYNOTE, DT_ORDINV_ZERO_PRICE);
	    }	    
      $MaintenanceDAO = new MaintenanceDAO($dbConn);
      $aSr = $MaintenanceDAO->clearExistingBalances($principalsToCheck, $depotList, $loopRow);

      $MaintenanceDAO = new MaintenanceDAO($dbConn);
      $aSr = $MaintenanceDAO->getAllStockRecords($principalsToCheck, $depotList, $interval, implode(",",$statusList), implode(",",$docTypeList) );

      $firstPrinDep = '';
      foreach($aSr as $row) {
    	    if($row['principal_uid'] . $row['depot_uid'] <> $firstPrinDep  )	{
             $firstPrinDep = $row['principal_uid'] . $row['depot_uid'];
             $updatedRow = $updatedRow . "Prin " . $row['principal_uid'] . "  Warehouse " . $row['depot_uid'] . "  Type - " . $loopRow . "<br>";
          }
        	
        	$MaintenanceDAO = new MaintenanceDAO($dbConn);
          $aSr = $MaintenanceDAO->updateBalances($row['principal_uid'], $row['depot_uid'], $row['Quantity'], $row['product_uid'], $loopRow);
          if($aSr <> 'S') {
          	echo "Balance Update Failed" ;
          }
      }	
      $updatedRow = $updatedRow . "Prin " . $row['principal_uid'] . "  Warehouse " . $row['depot_uid'] . "  Type - " . $loopRow . "<br>"; 
       
}

$MaintenanceDAO = new MaintenanceDAO($dbConn);
$aSr = $MaintenanceDAO->recalcalculateStockBalance();

echo $updatedRow;

//***************************************************************************************************************************************************************************************************
    echo "[***EOS***]";

?>
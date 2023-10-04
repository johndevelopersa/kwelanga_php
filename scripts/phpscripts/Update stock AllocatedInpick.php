<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/Update stock AllocatedInpick.php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . "properties/Constants.php");
include_once($ROOT . $PHPFOLDER . "DAO/MaintenanceDAO.php");

set_time_limit(15*60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

$startTime = microtime(true);
$principalId = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:"");
$depotList = "";
$statusLoop = array("allocations", "in_pick");
$interval = 45;
$queryCounter = 0;
$maintenanceDAO = new MaintenanceDAO($dbConn);

foreach ($statusLoop as $loopRow) {
    if ($loopRow == "allocations") {
        $statusList = [DST_UNACCEPTED, DST_ACCEPTED];
        $docTypeList = [DT_ORDINV, DT_DELIVERYNOTE, DT_ORDINV_ZERO_PRICE];
    } elseif ($loopRow == "in_pick") {
        $statusList = [DST_INPICK];
        $docTypeList = [DT_ORDINV, DT_DELIVERYNOTE, DT_ORDINV_ZERO_PRICE];
    }

    $queryCounter++;
    $aSr = $maintenanceDAO->getAllStockRecords($principalId, $depotList, $interval, implode(",", $statusList), implode(",", $docTypeList));

    $firstPrincipalDepot = '';
    foreach ($aSr as $row) {
        if ($row['principal_uid'] . $row['depot_uid'] <> $firstPrincipalDepot) {
            echo 'Principal ' . $row['principal_uid'] . '  Warehouse ' . $row['depot_uid'] . '  Type - ' . $loopRow . "\n";

            $firstPrincipalDepot = $row['principal_uid'] . $row['depot_uid'];

            $result = $maintenanceDAO->clearExistingBalances($row['principal_uid'], $row['depot_uid'], $loopRow, false);
            if($result->isError()){
                echo "clearExistingBalances Error: " . $result->getDescription() . "\n";
                exit;
            }
            $queryCounter++;
        }

        $queryCounter++;
        $result = $maintenanceDAO->updateBalances($row['principal_uid'], $row['depot_uid'], $row['Quantity'], $row['product_uid'], $loopRow, false);
        if($result->isError()){
            echo "Balance Update Error: " . $result->getDescription() . "\n";
            exit;
        }
    }
    echo "Principal " . $row['principal_uid'] . "  Warehouse " . $row['depot_uid'] . "  Type - " . $loopRow . "\n";
    $dbConn->dbQuery("commit");

}

$recalculateStockStartTime = microtime(true);

$queryCounter++;
$aSr = $maintenanceDAO->recalcalculateStockBalance();

echo "Recalculate Stock Took: " . round((microtime(true) - $recalculateStockStartTime),4) . " secs\n";
echo "Total Queries Executed: " . $queryCounter . "\n";
echo "Total Time Taken: " . round((microtime(true) - $startTime),4) . " secs\n";
echo "[***EOS***]";
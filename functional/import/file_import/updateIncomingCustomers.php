<?php

// "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/import/file_import/updateIncomingCustomers.php?PRIN=450";

/* * ********************************************************************************************
 * *
 * *  Import Customer from PPB
 * *
 * ********************************************************************************************** */

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/ppbCustomersDAO.php");
include_once($ROOT . $PHPFOLDER . 'libs/newrelic.php');

$prin = ((isset($_GET["PRIN"])) ? $_GET["PRIN"] : "");
$max_files_per_run = 100;

set_time_limit(15 * 60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}

$storage = new Storage(S3_ACCESS_ID, S3_SECRET_KEY, true, S3_ENDPOINT, S3_REGION);

echo "<pre style='font-size:14px;'>";
echo str_repeat("-", 75) . "\n";
echo "Import PPB Customers \n";
echo str_repeat("-", 75) . "\n";

$results = [];

$path = $ROOT . 'ftp/primaCustomers/';

$files = scandir($path);
$cont2 = "N";

// print_r($files);
// Get warhouse mapping
$ppbCustomersDAO = new PPBCUSTOMERSDAO($dbConn);
$wareHouseList = $ppbCustomersDAO->getWareHouseMapping();

$depotTrans = [];
foreach ($wareHouseList as $wRow) {
    $depotTrans[$wRow['value']] = $wRow['entity_uid'];

}
//print_r($depotTrans);
$ppbCustomersDAO = new PPBCUSTOMERSDAO($dbConn);
$chainList = $ppbCustomersDAO->getPrincipalChainMapping();

$chainTrans = [];
foreach ($chainList as $cRow) {
    $chainTrans[$cRow['value']] = $cRow['entity_uid'];

}

$multCount = $totCount = $insCount = $updCount = 0;

if (count($files) > 3) {
    foreach ($files as $key => $value) {

        if (strpos($value, 'customerUpdate') == 0) {
            echo $value . "<br>";
            if ($value === '.' || $value === '..' || $value === 'new') continue;

            $jsonContents = '';
            // Read entire file into string
            $jsonContents = file_get_contents($path . $value);
            // Convert into associative array
            $newArr = json_decode($jsonContents, true);
            foreach ($newArr as $stRowKey => $stRow) {
                if ($stRowKey == 'customerAccount') {
                    $ppbCustomersDAO = new PPBCUSTOMERSDAO($dbConn);
                    $result = $ppbCustomersDAO->getCustomerFromAccount($prin, trim($stRow));

                    if (count($result) == 0) {
                        echo "No Kwelanga Customer - Add";
                        $ppbAccount = trim($stRow);
                        $updateType = "INSERT";
                        $insCount++;
                        $totCount++;
                    } elseif (count($result) == 1) {
                        echo "Update Kwelanga Customer - Update";
                        $updateType = "UPDATE";
                        $kosUid = $result[0]['psmUid'];
                        $ppbAccount = trim($stRow);
                        $updCount++;
                        $totCount++;
                    } elseif (count($result) > 1) {
                        echo "Duplicate Kwelanga Customer found - Update";
                        $updateType = "MULTIUPDATE";
                        $kosUid = $result[0]['psmUid'];
                        $ppbAccount = trim($stRow);
                        $multCount++;
                        $totCount++;
                    }
                }
                if ($stRowKey == 'DeliverName') {
                    $ppbDelName = trim($stRow);
                }
                if ($stRowKey == 'DeliverAddress1') {
                    $ppbDelAdd1 = trim($stRow);
                }
                if ($stRowKey == 'DeliverAddress2') {
                    $ppbDelAdd2 = trim($stRow);
                }
                if ($stRowKey == 'DeliverAddress3') {
                    $ppbDelAdd3 = trim($stRow);
                }
                if ($stRowKey == 'InvoiceName') {
                    $ppbInvName = trim($stRow);
                }
                if ($stRowKey == 'InvoiceAddress1') {
                    $ppbInvAdd1 = trim($stRow);
                }
                if ($stRowKey == 'InvoiceAddress2') {
                    $ppbInvAdd2 = trim($stRow);
                }
                if ($stRowKey == 'InvoiceAddress3') {
                    $ppbInvAdd3 = trim($stRow);
                }
                if ($stRowKey == 'postCode') {
                    $ppbPost = trim($stRow);
                }
                if ($stRowKey == 'vatNumber') {
                    $ppbVatNo = trim($stRow);
                }
                if ($stRowKey == 'branch') {
                    $ppbBranch = trim($stRow);
                }
                if ($stRowKey == 'defaultWarehouse') {
                    if (trim($stRow) == '') {
                        $kosDepotUid = 485;
                    } else {
                        $kosDepotUid = $depotTrans[trim($stRow)];
                    }

                }
                if ($stRowKey == 'priceList1') {
                    $kosChainUid = $chainTrans[trim($stRow)];
                }
                if ($stRowKey == 'priceList2') {
                    if (trim($stRow) != '') {
                        $kosAltChainUid = $chainTrans[trim($stRow)];
                    } else {
                        $kosAltChainUid = $kosChainUid;
                    }
                }
                if ($stRowKey == 'creditLimit') {
                    $ppbCreditLimit = abs(trim($stRow));
                }
                if ($stRowKey == 'onHold') {
                    if (trim($stRow) == 'Y') {
                        $ppbHold = 1;
                    } else {
                        $ppbHold = 0;
                    }
                }
                if ($stRowKey == 'customerBalance') {
                    $ppbCustBal = abs(trim(substr($stRow, -8)));
                }
                if ($stRowKey == 'DepotName') {

                    if ($updateType == 'UPDATE') {

                        $ppbCustomersDAO = new PPBCUSTOMERSDAO($dbConn);
                        $dbConn->errorTO = $ppbCustomersDAO->updatePpbSores($kosUid,
                            $ppbDelName,
                            $ppbDelAdd1,
                            $ppbDelAdd2,
                            $ppbDelAdd3,
                            $ppbInvName,
                            $ppbInvAdd1,
                            $ppbInvAdd2,
                            $ppbInvAdd3,
                            $ppbVatNo,
                            (int)$ppbCreditLimit,
                            (float)$ppbCustBal,
                            $ppbHold,
                            $ppbBranch,
                            $ppbAccount,
                            $kosDepotUid);

                        echo "<br>" . $dbConn->errorTO->description . "<br>";
                    } elseif ($updateType == 'INSERT') {

                        $ppbCustomersDAO = new PPBCUSTOMERSDAO($dbConn);
                        $dbConn->errorTO = $ppbCustomersDAO->insertPpbSores($prin,
                            $ppbDelName,
                            $ppbDelAdd1,
                            $ppbDelAdd2,
                            $ppbDelAdd3,
                            $ppbInvName,
                            $ppbInvAdd1,
                            $ppbInvAdd2,
                            $ppbInvAdd3,
                            $ppbVatNo,
                            $kosDepotUid,
                            $kosChainUid,
                            $kosAltChainUid,
                            (int)$ppbCreditLimit,
                            (float)$ppbCustBal,
                            $ppbHold,
                            $ppbBranch,
                            $ppbAccount);
                        echo "<br>" . $dbConn->errorTO->description . "<br>";
                    }
                    /*echo "Do all Updates Here" ;

                    echo "<br>" . $updateType;


                    echo "<br>" . $ppbAccount;
                    echo "<br>" . $kosUid;
                    echo "<br>" . $updateType;
                    echo "<br>" . $ppbDelName;
                    echo "<br>" . $ppbDelAdd1;
                    echo "<br>" . $ppbDelAdd2;
                    echo "<br>" . $ppbDelAdd3;
                    echo "<br>" . $ppbInvName;
                    echo "<br>" . $ppbInvAdd1;
                    echo "<br>" . $ppbInvAdd2;
                    echo "<br>" . $ppbInvAdd3;
                    echo "<br>" . $ppbPost;
                    echo "<br>" . $ppbVatNo;
                    echo "<br>" . $ppbBranch;
                    echo "<br>" . $kosDepotUid;
                    echo "<br>" . $kosChainUid;
                    echo "<br>" . $kosAltChainUid;
                    echo "<br>" . $ppbCreditLimit;
                    echo "<br>" . $ppbHold;
                    echo "<br>" . $ppbCustBal;
                    echo "<br>";*/
                }
            }

            NewRelic::logEvent(
                $logType = "ppb-customers",
                $script = basename(__FILE__),
                $msg = 'Successfully Processed Customer Update',
                $attr = [
                    'principal_uid' => (int)$prin,
                    'filename' => basename($value),
                    'filepath' => $path . $value,
                    'psm_uid' => $result[0]['psmUid'],
                    'ppb_account' => trim($ppbAccount),
                    'timestamp' => gmdate('YmdHis'),
                    'customerUpdate' => $newArr,
                ]
            );

            $deletefile = unlink($path . $value);
            if ($deletefile) {
                echo "File deleted.. <br>";
            } else {
                echo "Unable to Delete the File.. <br>";
            }
        }
        if ($totCount >= $max_files_per_run) {
            echo 'max files reached, stopping processing!';
            break;
        }
    }
} else {
    echo "<br>";
    echo "No Customer Updates Found..";
    echo "<br>";

}
echo "<br>";
echo "Updated  - " . $updCount;
echo "<br>";
echo "Inserted - " . $insCount;
echo "<br>";
echo "Multi    - " . $multCount;
echo "<br>";
echo "Total    - " . $totCount;
echo "<br>";

echo "<br>End of PPB Customer Update<br>";
echo "[***EOS***]<br>";

?>

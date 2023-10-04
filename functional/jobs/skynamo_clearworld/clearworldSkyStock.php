<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/skynamo_clearworld/clearworldSkyStock.php?DEPOT=195&PRIN=216

require_once 'ROOT.php';
include_once $ROOT . 'PHPINI.php';
include_once $ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php";
include_once $ROOT . $PHPFOLDER . "DAO/SkymanoApiDAO.php";
require_once __DIR__ . "/../../../libs/api/skynamoclient/vendor/autoload.php";

require_once __DIR__ . '/syknamo_clearworld_config.php';

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}

if (isset($_GET["DEPOT"])) {
    $selDep = mysqli_real_escape_string($dbConn->connection, $_GET["DEPOT"]);
} else {
    $selDep = $importWarehouse;
}
$selPrin = 216;

$config = SkynamoClientAPI\Configuration::getDefaultConfiguration();
$config->setApiKey('x-api-key', SkynamoClearworld::getAPIKey());

$apiInstance = new SkynamoClientAPI\Api\StockLevelsApi(new GuzzleHttp\Client(), $config);

$skymanoApiDAO = new SkymanoApiDAO($dbConn);
$prodList = $skymanoApiDAO->getWarehouseClosing($selPrin, $selDep);

echo "Products to update: " . count($prodList) . "\n";

if (!count($prodList)) {
    echo "<br>Nothing to Update<br>";
    return;
}


$uCount = $successCount = $failCount = 0;
$successCount++;

//group products into 10 products of 5 batches
$batchArr = [];
$idx = 0;

//total batches
for ($i = 0; $i < 5; $i++) {
    if (!isset($batchArr[$i])) {
        $batchArr[$i] = [];
    }

    //size of products per batch
    for ($k = 0; $k < 10; $k++) {
        if(isset($prodList[$idx])) {
            $batchArr[$i][$k] = $prodList[$idx];
            $idx++;
        }
    }
}

foreach ($batchArr as $prodList) {

    //UPDATE STOCK EXAMPLE
    $updateArr = [];
    foreach($prodList as $row) {
        $s = new SkynamoClientAPI\Model\StockLevelPost();
        $s->setLevel($row['available']);
        $s->setProductCode($row['product_code']);
        $s->setOrderUnitId(trim($row['spare']));
        $s->setWarehouseId($row['value']);
        $updateArr[] = $s;

        echo $row['wh'] . ' - ' . $row['product_code'] . ' - ' . $row['available'] . "\n";

        $uCount++;
    }

    try {

        $result = $apiInstance->stocklevelsPost(SkynamoClearworld::getClientName(), $updateArr);

        //the above api call with throw an exception if there is an error, the catch is where you roll back!
        //var_dump($result->getMessage());    //Updated successfully

        echo "<br>";
        $successCount++;

        $skymanoApiDAO = new SkymanoApiDAO($dbConn);

        foreach($prodList as $row) {
            $errorTO = $skymanoApiDAO->updateSkyTime($row['whUid'], $row['ppUid'], $row['available']);
        }

    } catch (Exception $result) {
        //echo "<br>";
        //echo "<pre>";
        //var_dump($result);
        //echo "<br>";
        $failCount++;

        echo "Exception when calling apiInstance->stocklevelsPost: \n", $result->getMessage(), PHP_EOL;
        echo "<br>";
    }



}


echo "<br>";
echo $successCount . "  - Successful Batches Updated";
echo "<br>";
echo $failCount . "  - Batches Not Updated";
echo "<br>";
echo $uCount . "  - Records Processed";
echo "<br>";









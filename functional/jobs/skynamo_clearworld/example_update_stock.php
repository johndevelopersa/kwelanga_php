<?php

use SkynamoClientAPI\Model\StockLevel;
use SkynamoClientAPI\Model\StockLevelPost;

require_once 'ROOT.php';
include_once $ROOT . 'PHPINI.php';
include_once $ROOT . $PHPFOLDER."DAO/db_Connection_Class.php";
require_once __DIR__ . "/../../../libs/api/skynamoclient/vendor/autoload.php";

require_once __DIR__ . '/syknamo_clearworld_config.php';

$config = SkynamoClientAPI\Configuration::getDefaultConfiguration();
$config->setApiKey('x-api-key', SkynamoClearworld::getAPIKey());

$apiInstance = new SkynamoClientAPI\Api\StockLevelsApi(new GuzzleHttp\Client(), $config);

try {
    $result = $apiInstance->stocklevelsGet(SkynamoClearworld::getClientName(), 1, 25, $flags = null);

    if ($result->getPage()->getTotalItemCount() == 0) {
        echo "no products found!";
        return;
    }

    foreach ($result->getData() as $stock) {

        //print_r($stock);

        print(str_repeat("-", 45).  "\n");

        //product
        print("product id:" . $stock->getProductId(). "\n");
        print("product code:" . $stock->getProductCode(). "\n");
        print("product name:" . $stock->getProductName(). "\n");

        //warehouse
        print("warehouse id:" . $stock->getWarehouseId(). "\n");
        print("warehouse name:" . $stock->getWarehouseName(). "\n");

        //order??
        print("order unit id:" . $stock->getOrderUnitId(). "\n");
        print("order unit name:" . $stock->getOrderUnitName(). "\n");

        print("level:" . $stock->getLevel(). "\n"); //<----- APPEARS TO BE QTY

        //last modified in DateTime format.
        print("Last Modified:" . $stock->getLastModifiedTime()->format(DATE_RFC3339) . "\n");
    }


    //UPDATE STOCK EXAMPLE
    $s = new SkynamoClientAPI\Model\StockLevelPost();
    $s->setLevel(12345678);
    $s->setProductCode("12176718");
    $s->setOrderUnitId(2);  // <--------- WHAT IS THIS???
    $s->setWarehouseId(1);    //id:1 looks like the item must be at the warehouse?

    try {
        $result = $apiInstance->stocklevelsPost(SkynamoClearworld::getClientName(), [$s]);

        //the above api call with throw an exception if there is an error, the catch is where you roll back!
        var_dump($result->getMessage());    //Updated successfully

    } catch (Exception $e) {
        echo "Exception when calling apiInstance->stocklevelsPost: \n", $e->getMessage(), PHP_EOL;
    }

} catch (Exception $e) {
    echo "Exception when calling apiInstance->stocklevelsGet: \n", $e->getMessage(), PHP_EOL;
}

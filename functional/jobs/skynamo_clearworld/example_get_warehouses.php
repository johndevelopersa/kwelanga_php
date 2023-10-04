<?php
require_once 'ROOT.php';
include_once $ROOT . 'PHPINI.php';
include_once $ROOT . $PHPFOLDER."DAO/db_Connection_Class.php";
require_once __DIR__ . "/../../../libs/api/skynamoclient/vendor/autoload.php";

require_once __DIR__ . '/syknamo_clearworld_config.php';

$config = SkynamoClientAPI\Configuration::getDefaultConfiguration();
$config->setApiKey('x-api-key', SkynamoClearworld::getAPIKey());

$apiInstance = new SkynamoClientAPI\Api\WarehousesApi(new GuzzleHttp\Client(), $config);

echo "<pre>";

try {
    $result = $apiInstance->warehousesGet(SkynamoClearworld::getClientName(), 1, 25, $flags = null);

    if ($result->getPage()->getTotalItemCount() == 0) {
        echo "no warehouses found!";
        return;
    }

    foreach ($result->getData() as $warehouse) {

        //print_r($product);
        print(str_repeat("-", 45).  "\n");
        print("id:" . $warehouse->getId() . "\n");
        print("name:" . $warehouse->getName() . "\n");

    }

} catch (Exception $e) {
    echo "Exception when calling apiInstance->warehousesGet: \n", $e->getMessage(), PHP_EOL;
}

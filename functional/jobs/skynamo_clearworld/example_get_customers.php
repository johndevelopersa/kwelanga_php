<?php
require_once 'ROOT.php';
include_once $ROOT . 'PHPINI.php';
include_once $ROOT . $PHPFOLDER."DAO/db_Connection_Class.php";
require_once __DIR__ . "/../../../libs/api/skynamoclient/vendor/autoload.php";

require_once __DIR__ . '/syknamo_clearworld_config.php';

$config = SkynamoClientAPI\Configuration::getDefaultConfiguration();
$config->setApiKey('x-api-key', SkynamoClearworld::getAPIKey());

$apiInstance = new SkynamoClientAPI\Api\CustomersApi(new GuzzleHttp\Client(), $config);

try {
    $result = $apiInstance->customersGet(SkynamoClearworld::getClientName(), 1, 25, $flags = null);

    if ($result->getPage()->getTotalItemCount() == 0) {
        echo "no customers found!";
        return;
    }

    foreach ($result->getData() as $cust) {

        //print_r($product);
        print(str_repeat("-", 45).  "\n");
        print("id:" . $cust->getId() . "\n");
        print("code:" . $cust->getCode() . "\n");
        print("name:" . $cust->getName() . "\n");

        //custom fields contain additional codes
        foreach ($cust->getCustomFields() as $field) {
            print("\tFIELD " . $field->getId() . ". " . $field->getName() . " = '" . $field->getValue() . "'\n");
        }
        //$product->getActive();

    }

} catch (Exception $e) {
    echo "Exception when calling apiInstance->productsGet: \n", $e->getMessage(), PHP_EOL;
}

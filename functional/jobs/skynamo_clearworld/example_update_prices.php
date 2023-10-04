<?php

require_once 'ROOT.php';
include_once $ROOT . 'PHPINI.php';
include_once $ROOT . $PHPFOLDER."DAO/db_Connection_Class.php";
require_once __DIR__ . "/../../../libs/api/skynamoclient/vendor/autoload.php";
require_once __DIR__ . '/syknamo_clearworld_config.php';

$config = SkynamoClientAPI\Configuration::getDefaultConfiguration();
$config->setApiKey('x-api-key', SkynamoClearworld::getAPIKey());
$pricesAPI = new SkynamoClientAPI\Api\PricesApi(new GuzzleHttp\Client(), $config);

echo "<PRE>";

try {

    /*-----------------------------*/
    /*         FETCH PRICES        */
    /*-----------------------------*/
    $prices = $pricesAPI->pricesGet(SkynamoClearworld::getClientName(), $pageNo =1, $pageSize = 5, $flags = null);
    if($prices->getPage()->getPageSize()){


        foreach($prices->getData() as $priceRecord){

            print(str_repeat("-", 20). " PRODUCT PRICES " . str_repeat("-", 20) . "\n");

            // the product
            print("ProductName: " . $priceRecord->getProductName() . "\n");
            print("ProductId: " . $priceRecord->getProductId() . "\n");
            print("ProductCode: " . $priceRecord->getProductCode() . "\n");

            //price
            print("Price: " . var_export($priceRecord->getPrice(), true) . "\n");
            print("TaxRateId: " . var_export($priceRecord->getTaxRateId(), true) . "\n");
            print("OrderUnitName: " . $priceRecord->getOrderUnitName() . "\n");

            //pricelist?
            print("PriceListId: " . $priceRecord->getPriceListId() . "\n");
            print("PriceListName: " . $priceRecord->getPriceListName() . "\n");

            //modified
            print("LastModifiedTime: " . $priceRecord->getLastModifiedTime()->format(DATE_RFC3339) . "\n");
        }

    }


    /*-----------------------------*/
    /*         UPDATE PRICES       */
    /*-----------------------------*/

    //example update array
    $updateProducts = [
        //product code ==> new price
        "12500913" => 99.89,
        "12176718" => 9.50,
        "12261941" => 1234.56
    ];

    //build up an array of OrderUnitPricePost to forward to the SkynamoAPI
    $priceUpdates = [];
    foreach ($updateProducts as $productCode => $newPrice) {

        $priceUpdateModel = new SkynamoClientAPI\Model\OrderUnitPricePost();
        $priceUpdateModel->setProductCode($productCode);
        $priceUpdateModel->setPrice($newPrice);
        $priceUpdateModel->setOrderUnitName("Unit");
        $priceUpdateModel->setPriceListName("HOT");     //you might want an example to get a list.

        //add to updates array
        $priceUpdates[] = $priceUpdateModel;
    }

    print("\n\n\n" . str_repeat("-", 20). " PRICES UPDATE " . str_repeat("-", 20) . "\n");

    //send all the updates to the SkynamoAPI
    try {
        $result = $pricesAPI->pricesPost(SkynamoClearworld::getClientName(), $priceUpdates);

        //the above api call with throw an exception if there is an error, the catch is where you roll back!
        var_dump($result->getMessage());    //Updated successfully

    } catch (Exception $e) {
        echo "Exception when calling $pricesAPI->pricesPost: \n", $e->getMessage(), PHP_EOL;
    }

} catch (Exception $e) {
    echo "Exception when calling apiInstance->stocklevelsGet: \n", $e->getMessage(), PHP_EOL;
}

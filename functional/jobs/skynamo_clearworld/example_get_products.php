<?php

//https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/skynamo_clearworld/example_get_products.php

require_once 'ROOT.php';
include_once $ROOT . 'PHPINI.php';
include_once $ROOT . $PHPFOLDER."DAO/db_Connection_Class.php";
include_once $ROOT . $PHPFOLDER."DAO/SkymanoApiDAO.php";
require_once __DIR__ . "/../../../libs/api/skynamoclient/vendor/autoload.php";

require_once __DIR__ . '/syknamo_clearworld_config.php';

$config = SkynamoClientAPI\Configuration::getDefaultConfiguration();
$config->setApiKey('x-api-key', SkynamoClearworld::getAPIKey());

$apiInstance = new SkynamoClientAPI\Api\ProductsApi(new GuzzleHttp\Client(), $config);


// Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();
      
$errorTO = new ErrorTO;

try {
    $result = $apiInstance->productsGet(SkynamoClearworld::getClientName(),5, 200, $flags = null);

    if ($result->getPage()->getTotalItemCount() == 0) {
        echo "no products found!";
        return;
    }

    echo "<pre>";
    foreach ($result->getData() as $product) {

        if(!isset($product->getOrderUnits()[0])) {
            //log error here
            echo "ERROR: unable to determine OrderUnits->id";
            print_r($product);
            continue;
        }

        echo str_repeat("-", 50) . "\n";
        echo "PRODUCT_ID: " . $product->getId() . "\n";
        echo "PRODUCT_ORDER_ID: " . $product->getOrderUnits()[0]->getId() . "\n";
        echo "PRODUCT_CODE: " . $product->getCode() . "\n";
        echo "PRODUCT_NAME: " . $product->getName() . "\n";
    }

//    die("stopping before update");

    foreach ($result->getData() as $product) {

        $updProd = new SkymanoApiDAO($dbConn);
        $errorTO = $updProd->updateSkyProductIds($product->getCode(), $product->getOrderUnits()[0]->getId());   
        
        //var_dump($product);
        
        echo $product->getId() ." - ". $product->getOrderUnits()[0]->getId() ." - ". $product->getCode() ." - ".  $product->getName() ." - ". $errorTO->description . "<BR>";
        


    }

} catch (Exception $e) {
    echo "Exception when calling apiInstance->productsGet: \n", $e->getMessage(), PHP_EOL;
}

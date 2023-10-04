<?php


//THIS TESTS CONNECTION, CREDS AND FETCHING (KEYS/RIGHTS)

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . 'libs/api/woocommerce-api/woocommerce-api.php';


//WooCommerceStore and API Details
class ItalianDeliConstants
{
    const WooCommerceStoreURI = "https://italiandelionline.co.za";
    const WooCommerceConsumerKey = "ck_7be7e1b530ad4bd37cefd20d0e0aa60c00c64b18";
    const WooCommerceConsumerSecret = "cs_95c693a957f57e8d8d7b16f005ab252b00d385e5";
}

try {

    $options = [
        'debug' => false,
        'return_as_array' => true,
        'validate_url' => false,
        'timeout' => 30,
        'ssl_verify' => false,
    ];

    //Create new Client
    $client = new WC_API_Client(ItalianDeliConstants::WooCommerceStoreURI, ItalianDeliConstants::WooCommerceConsumerKey, ItalianDeliConstants::WooCommerceConsumerSecret, $options);

    //get a SINGLE order
    $orderId = "237999";
    $orderArr = $client->orders->get($orderId);

    if (!is_array($orderArr)) {
        http_response_code(500);
        echo "response is not an array!";
        return;
    }

    if (is_array($orderArr) && $orderArr['id'] != $orderId) {
        http_response_code(500);
        echo "array has no id key!";
        return;
    }

    if (is_array($orderArr) && empty($orderArr['billing'])) {
        http_response_code(500);
        echo "array has no billing key!";
        return;
    }

    echo "<pre>";
    echo "<h4>Fetched ORDER {$orderId}.... OK!</h4><hr>";
    print_r($orderArr);

} catch (Exception $e) {

    http_response_code(500);
    echo "<pre>";
    echo $e->getMessage() . PHP_EOL;
    echo $e->getCode() . PHP_EOL;

    if ($e instanceof WC_API_Client_HTTP_Exception) {

        print_r($e->get_request());
        print_r($e->get_response());
    }
}

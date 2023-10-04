<?php

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . 'libs/api/woocommerce-api/woocommerce-api.php';

include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();


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

    //get all orders by a status, for the past few days
    //  status. Options:
    //      any, pending, processing, on-hold, completed, cancelled, refunded, failed and trash. Default is any.
    $query = [
        'status'   => 'any',
        'per_page' => 100,  //max
        'orderby'  => "date", "order" => "desc", //gives us the newest first
        'after'    => (new DateTime())->sub(new DateInterval("P14D"))->format(DateTime::ATOM),   //limit to 15D = 15 Days
    ];

    //var_dump($query);
    $orders = $client->orders->get(null, $query);

    echo "<pre>";
    echo count($orders);
    
    include_once($ROOT.$PHPFOLDER."functional/import/adaptor/api/ApiAdaptorOH.php");

    foreach ($orders as $order) {
echo "<br>";
echo "<pre>";

print_r($order);    	
    	  $newOrder = new ApiAdaptorOH($dbConn);
    	  $newOrder->adaptorAPI_216($order) ;



        //send to TOH

        //on success
        //$updatedOrder = $client->orders->update_status($order['id'], 'processing');
    }

die();
//    //get a SINGLE order
//    $orderId = "237999";
//
//    $updatedOrder = $client->orders->update_status($orderId, 'processing');
//
//    $orderArr = $client->orders->get($orderId);
//    $currentStatus = $orderArr['status'];
//    var_dump("current status:" . $currentStatus);
//
//    //change to completed!
//    $updatedOrder = $client->orders->update_status($orderId, 'completed');
//    var_dump("changed to:" . $updatedOrder['status']);
//
//    //set it back
//    $updatedToOriginal = $client->orders->update_status($orderId, $currentStatus);
//    var_dump("return to original status:" . $updatedToOriginal['status']);


} catch (Exception $e) {

    echo $e->getMessage() . PHP_EOL;
    echo $e->getCode() . PHP_EOL;

    if ($e instanceof WC_API_Client_HTTP_Exception) {

        print_r($e->get_request());
        print_r($e->get_response());
    }
}

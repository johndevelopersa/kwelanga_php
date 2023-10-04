<?php

require_once 'ROOT.php';
include_once $ROOT . 'PHPINI.php';
include_once $ROOT . $PHPFOLDER."DAO/db_Connection_Class.php";

require_once __DIR__ . "/../../../libs/api/skynamoclient/vendor/autoload.php";

require_once __DIR__ . '/syknamo_clearworld_config.php';


$config = SkynamoClientAPI\Configuration::getDefaultConfiguration();
$config->setApiKey('x-api-key', SkynamoClearworld::getAPIKey());

$api = new SkynamoClientAPI\Api\OrdersApi(new GuzzleHttp\Client(), $config);

echo "<PRE>";

try {
    $result = $api->ordersGet(SkynamoClearworld::getClientName(), $pageNo = 1, $pageSize = 10, $flags = null);

    if ($result->getPage()->getTotalItemCount() == 0) {
        echo "no products found!";
        return;
    }

    foreach ($result->getData() as $order) {

        print(str_repeat("-", 20). " ORDER " . str_repeat("-", 20) . "\n");

        print("OrderID: " . $order->getId() . "\n");
        print("Reference: " . $order->getReference() . "\n");

        print("CustomerCode: " . $order->getCustomerCode() . "\n");
        print("CustomerName: " . $order->getCustomerName() . "\n");

        print("WarehouseId: " . $order->getWarehouseId() . "\n");
        print("WarehouseName: " . $order->getWarehouseName() . "\n");

        print("Discount: " . $order->getDiscount() . "\n");
        print("DiscountAmount: " . $order->getDiscountAmount() . "\n");
        print("InteractionId: " . $order->getInteractionId() . "\n");
        print("TotalAmount: " . $order->getTotalAmount() . "\n");
        print("PricesIncludeVat: " . print_r($order->getPricesIncludeVat(),true) . "\n");

        print("Date: " . $order->getDate()->format(DATE_RFC3339) . "\n");
        print("LastModifiedTime: " . $order->getLastModifiedTime()->format(DATE_RFC3339) . "\n");

        print(str_repeat("-", 20). " LINES " . str_repeat("-", 20) . "\n");

        //custom fields contain additional codes
        foreach ($order->getItems() as $k => $item) {

            print(str_repeat("-", 20). " LINE:" . ($k+1) . " " . str_repeat("-", 20) . "\n");

            print("ProductCode: " . $item->getProductCode() . "\n");
            print("ProductName: " . $item->getProductName() . "\n");

            print("Quantity: " . $item->getQuantity() . "\n");

            print("OrderUnitName: " . $item->getOrderUnitName() . "\n");

            print("Cost: " . $item->getCost() . "\n");
            print("ListPrice: " . $item->getListPrice() . "\n");
            print("UnitPrice: " . print_r($item->getUnitPrice(),true) . "\n");

            print("TaxRateId: " . $item->getTaxRateId() . "\n");
            print("TaxRateValue: " . $item->getTaxRateValue() . "\n");

        }

        print("\n");
    }

} catch (Exception $e) {
    echo "Exception when calling api->ordersGet: \n", $e->getMessage(), PHP_EOL;
}

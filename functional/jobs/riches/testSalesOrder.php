<?php

require_once __DIR__ . "/../../../libs/api/omni/OmniRestAPI.php";
require_once __DIR__ . "/RichesConstants.php";



# TEST A SINGLE SALES ORDER : CREATE, SELECT, UPDATE (POST, GET, PUT)

$omniApi = new OMNIRestAPI(
    RichesConstants::OmniHostname,
    RichesConstants::OmniUsername,
    RichesConstants::OmniPassword,
    RichesConstants::OmniTestCompany
);

echo "test";
echo "<pre style='font-size:14px;'>";

/*-------------------------------------------------------------
 *      CREATE ORDER
 *------------------------------------------------------------
 */
$order = (new OmniSalesOrderObj)
    ->setCustomerAccountCode("CHK001")
    ->setWarehouseCode("CWCS");

for ($i = 1; $i <= 3; $i++) {

    //create line item
    $lineItem = (new OmniSalesOrderLineItemObj)
        ->setStockCode("08507")
        ->setQuantity((int)6);

    //append to order
    $order->addOrderLine($lineItem);
}


$orderNumber = "TEST0" . rand(0, 1000);  //omni api allows you set to the ordernumber or let it be auto generated.

//send to OMNI API...

print_r($order->getJSON());
echo "</pre>";

$response = $omniApi->CreateSalesOrder($order, $orderNumber);

//only the order number comes back and not in JSON format!
$responseOrderNo = $response->getBody();

var_dump($responseOrderNo);


//this won't work if the order number is auto-generated
if (!$response->getSuccess() || $response->getSuccess() && $responseOrderNo != $orderNumber) {

    echo "<h1>Error Creating Order</h1>";
    var_dump($response->getErrorMessage());
    echo "<hr>";
   // var_dump($response);

} else {

    echo "<h1>Successfully Created Order</h1>";
    echo "Order No:" . $responseOrderNo;
    echo "URL:" . $response->getRequestURL();

    /*-------------------------------------------------------------
     *      GET ORDER
     *------------------------------------------------------------
     */
    echo "<h1>Fetching Order: {$orderNumber}</h1>";
    $createdOrder = $omniApi->GetSalesOrder($orderNumber);

    var_dump($createdOrder->getResponse()->getSuccess());
    echo "<pre>";
    print_r($createdOrder);

    /*-------------------------------------------------------------
     *      UPDATE ORDER
     *------------------------------------------------------------
     */
    echo "<h1>Updating Order: {$orderNumber}</h1>";

    //response was not an order number?
    // $response->setSuccess(false);

    $updatedOrderHeader = $createdOrder->setAnalysis4("TESTXYZ");

    $updatedResponse = $omniApi->UpdateSalesOrder($createdOrder, $orderNumber);
    var_dump($updatedResponse->getSuccess());
    echo "<pre>";
    var_dump("UPDATED ORDER NO.:",$updatedResponse->getBody());

   var_dump( $updatedResponse->getXHRResponse());

}


// loop PER DOCUMENT
//post to omni
//response OK
//get sales order -- verify?
//date, customer,
// total qty
// total value...
// difference?
// insert different into smart events...

// insert into smart events.... status = C

// ------
// post to status cake... for


/*
//GDS services...
//LOCAL INSTALL --- REPORT DESIGNS
//API ETC


//LIST OF CATEGORIES
//Lookups/Customer Category?

//Customer Listing-&-Customer%20Category=

//---
//CAR
//PRR
//


//Auto Stock --- Category
//ISTOCKCATEGORY




# POST SALES ORDER --
# GET DELIVERY/INVOICE INFORMATION BASED ON ORDER -- LINK TO ORDER
# GET DELIVERY CONFIRMATION / CREDIT NOTE

*/

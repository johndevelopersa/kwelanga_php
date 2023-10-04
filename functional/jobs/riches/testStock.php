<?php

include_once __DIR__ . "../../libraries/api/omni/OmniRestAPI.php";
include_once __DIR__ . "/RichesCredentials.php";

$omniApi = new OMNIRestAPI(
    RichesConstants::Hostname,
    RichesConstants::Username,
    RichesConstants::Password,
    RichesConstants::Company
);




# RETRIEVE ALL STOCK ITEMS AND LEVELS

$response = $omniApi->GetStock();

var_dump($response);


/*
 * remote.glacialds.co.za:54920



Username : WEB

Password : web123@



Report name for stock levels : AutoStockLevels

Report name for Customers : AutoCust



Kind regards,
 */


# RETRIEVE ALL STOCK ITEMS AND LEVELS
# POST SALES ORDER --
# GET DELIVERY/INVOICE INFORMATION BASED ON ORDER -- LINK TO ORDER
# GET DELIVERY CONFIRMATION / CREDIT NOTE

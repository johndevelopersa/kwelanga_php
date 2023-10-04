<?php

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER."DAO/db_Connection_Class.php";
require_once $ROOT . $PHPFOLDER . "libs/api/sqlrest-client/SQLRestClient.php";
require_once $ROOT . $PHPFOLDER . "libs/Config.php";

error_reporting(-1);
ini_set('display_errors', 1);

# configuration - stored in cloud.
$api_key = Config::GetSecret('/berkley/api_secret_key')->AsString();
$uri = Config::GetParam('/berkley/uri_slug')->AsString();
$endpoint = Config::GetParam('/endpoints/api_base_url')->AsString();

# MSSQL to REST Client library
$client = new SQLRestClient($endpoint, $uri, $api_key);

### ALL AVAILABLE KWE PROCEDURES.
$query = "SELECT
              SCHEMA_NAME(schema_id) AS [Schema],
              name
            FROM CustomData.sys.objects
            WHERE type = 'P' and name like '%KWE%';";

$procListResult = $client->Query($query);
if($procListResult->IsSuccess()) {
    var_dump($procListResult->Data());
} else {
    echo "ERROR!";
    var_dump($procListResult);
    die();
}

# GET STOCK
$result = $client->Procedure("CustomData.dbo.p_KWE_StockItems", ['Db_ID' => 426]);
if($result->IsSuccess()) {
    var_dump($result->Data()[0]);   //first product
}

# GET Sales Orders
$result = $client->Procedure("CustomData.dbo.p_KWE_SalesOrders", ['Db_ID' => 426]);
if($result->IsSuccess()) {
    var_dump($result->Data()[0]);   //first product
}

# GET STOCK
$result = $client->Procedure("CustomData.dbo.p_KWE_DeliveryAddress", ['Db_ID' => 426]);
if($result->IsSuccess()) {
    var_dump($result->Data()[0]);   //first product
}

# GET STOCK
$result = $client->Procedure("CustomData.dbo.p_KWE_CustomerMaster", ['Db_ID' => 426]);
if($result->IsSuccess()) {
    var_dump($result->Data()[0]);   //first product
}


<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/berkley_sync/getBerkleyGlenmor.php?QTYPE=O&PUID=426

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php";
require_once $ROOT . $PHPFOLDER . "libs/api/sqlrest-client/SQLRestClient.php";
require_once $ROOT . $PHPFOLDER . "libs/Config.php";
include_once($ROOT . $PHPFOLDER . 'DAO/BerkGlenDAO.php');


//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();
$errorTO = new ErrorTO;

error_reporting(-1);
ini_set('display_errors', 1);

if (isset($_POST["QTYPE"])) $postQTYPE = mysqli_real_escape_string($dbConn->connection, $_POST["QTYPE"]); else if (isset($_GET["QTYPE"])) $postQTYPE = mysqli_real_escape_string($dbConn->connection, $_GET["QTYPE"]);
if (isset($_POST["PUID"])) $postPrincipalUID = mysqli_real_escape_string($dbConn->connection, $_POST["PUID"]); else if (isset($_GET["PUID"])) $postPrincipalUID = mysqli_real_escape_string($dbConn->connection, $_GET["PUID"]);

# configuration - stored in cloud.
$api_key = Config::GetSecret('/berkley/api_secret_key')->AsString();
$uri = Config::GetParam('/berkley/uri_slug')->AsString();
$endpoint = Config::GetParam('/endpoints/api_base_url')->AsString();

# MSSQL to REST Client library
$client = new SQLRestClient($endpoint, $uri, $api_key);

### ALL AVAILABLE KWE PROCEDURES.
$query = "SELECT SCHEMA_NAME(schema_id) AS [Schema],
                name
          FROM CustomData.sys.objects
          WHERE type = 'P' and name like '%KWE%';";

$procListResult = $client->Query($query);
if ($procListResult->IsSuccess()) {
    // var_dump($procListResult->Data());
} else {
    echo "ERROR!";
    var_dump($procListResult);
    die();
}

if ($postQTYPE == "P") {

    # GET PRODUCTS
    $result = $client->Procedure("CustomData.dbo.p_KWE_StockItems", ['Db_ID' => $postPrincipalUID]);
    if ($result->IsSuccess()) {
        var_dump($result->Data()[0]);   //first product
    }

} elseif ($postQTYPE == "O") {
    # GET Sales Orders
    $result = $client->Procedure("CustomData.dbo.p_KWE_SalesOrders", ['Db_ID' => $postPrincipalUID]);
    if ($result->IsSuccess()) {
        //print_r($result->Data());   //first product

        $resultArray = $result->Data();

        //sort resulting data as invoices are not ordered from the underlying SP query.
        function berkleySalesOrdersSort($a, $b): int
        {
            if ($a['AutoIndex'] == $b['AutoIndex']) {
                //if same invoice, sort by lineID.
                return ($a['iLineID'] < $b['iLineID']) ? -1 : 1;
            }
            return ($a['AutoIndex'] < $b['AutoIndex']) ? -1 : 1;
        };

        uasort($resultArray, 'berkleySalesOrdersSort');

        //echo count($resultArray);
        //echo "<br>";
        $header = 'F';

        if ($postPrincipalUID == 426) {
            $fileName = 'Glenmor' . date('z') . date('H') . date('i') . date('s') . count($resultArray) . '.txt';
        } else {
            $fileName = 'BerkOrders' . date('z') . date('H') . date('i') . date('s') . count($resultArray) . '.txt';
        }

        foreach ($resultArray as $rRow) {
            if ($header == 'F') {
                //echo implode("|",array_keys($rRow));
                //echo "<br>";
                $header = 'T';
                file_put_contents($ROOT . "ftp/berkley/" . $fileName, implode("|", array_keys($rRow)) . "\r\n");
            }
            file_put_contents($ROOT . "ftp/berkley/" . $fileName, implode("|", $rRow) . "\r\n", FILE_APPEND);
        }
    }

} elseif ($postQTYPE == "D") {

    # GET ADDRESSES
    $result = $client->Procedure("CustomData.dbo.p_KWE_DeliveryAddress", ['Db_ID' => $postPrincipalUID]);
    if ($result->IsSuccess()) {
        var_dump($result->Data()[0]);   //first product
    }

} elseif ($postQTYPE == "C") {

    # GET CUSTOMERS
    $result = $client->Procedure("CustomData.dbo.p_KWE_CustomerMaster", ['Db_ID' => $postPrincipalUID]);
    if ($result->IsSuccess()) {
        // print_r($result->Data());

        $resultArray = $result->Data();
        $header = 'F';

        if ($postPrincipalUID == 426) {
            $fileName = 'Stores_Glenmor' . date('z') . date('H') . date('i') . date('s') . count($resultArray) . '.txt';
        } else {
            $fileName = 'Stores_Berkley' . date('z') . date('H') . date('i') . date('s') . count($resultArray) . '.txt';
        }

        foreach ($resultArray as $rRow) {
            if ($header == 'F') {
                //echo implode("|",array_keys($rRow));
                //echo "<br>";
                $header = 'T';
                file_put_contents($ROOT . "ftp/berkley/" . $fileName, implode("|", array_keys($rRow)) . "\r\n");
            }
            file_put_contents($ROOT . "ftp/berkley/" . $fileName, implode("|", $rRow) . "\r\n", FILE_APPEND);
        }

        $processStores = new BerkGlenDAO($dbConn);
        $errorTO = $processStores->uploadProcessStores($postPrincipalUID, $ROOT . "ftp/berkley/" . $fileName);

        if ($errorTO->type == FLAG_ERRORTO_ERROR) {
            echo "<BR><BR>";
            echo "Loading Stores Bomb Out<BR>";
            echo "<PRE>";
            print_r($errorTO);

        }

    }
}
echo "<BR><BR>";
echo "Fetching Berkley/Glenmor Orders/Stores<BR>";
echo "Successful <BR>";

echo "<BR><BR>[***EOS***]";


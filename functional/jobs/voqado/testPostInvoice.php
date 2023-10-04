<?php

include_once __DIR__ . "/../../../libs/api/voqado-api/VoqadoRestAPI.php";
include_once __DIR__ . "/VoqadoConstants.php";

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php";
require_once $ROOT . $PHPFOLDER . 'properties/Constants.php';
include_once($ROOT . $PHPFOLDER . 'DAO/DepotDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ProductDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostProductDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostStockDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingProductTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingStockTO.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');

//setup header
echo "<pre style='font-size:14px;'>";
echo str_repeat("-", 75) . "\n";
echo "Voqado REST API - Post Invoices\n";
echo str_repeat("-", 75) . "\n";


//php settings
set_time_limit(15 * 60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

//connect to database
//if (!isset($dbConn)) {
//    $dbConn = new dbConnect();
//    $dbConn->dbConnection();
//}

//setup api class
$voqadoApi = new VoqadoRestAPI(VoqadoConstants::ApiUri, VoqadoConstants::ApiUsername, VoqadoConstants::ApiPassword);


//query orders/invoices
//< INSERT HERE >//


//sample for sending ONE invoice

$invoiceData = [
    's_coyid' => 'KSSPD',
    's_code' => 'TEST',
    's_ref' => 'IN021',
    's_type' => 'IN',
    's_desc' => 'Invoice',
    's_tyear' => '2020',
    's_period' => '9',
    's_custono' => '12345',
    's_exclamount' => 100,
    's_vamt' => 15,
    's_amount' => 115,
    's_userid' => 'TS',
    's_transactionstatus' => 'A',
    'smsadata' => [
        ['s_code' => 'TEST'],
        //add lines here
    ]
];

//display the sent data!
echo "sending to voqado!\n<br>";
print_r($invoiceData);

$response = $voqadoApi->Request("POST", "vqdebtortransactions/upddrtn", $invoiceData);


if ($response->getSuccess()) {

    //everything went OK!
    echo "SUCCESS!\n<br>";
    print_r($response->getBody());

} else {
    echo "ERROR!\n<br>";
    echo $response->getErrorMessage();
}


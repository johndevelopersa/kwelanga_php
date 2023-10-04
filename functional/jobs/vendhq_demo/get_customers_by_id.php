<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/vendhq_demo/get_customers_by_id.php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . "libs/CommonUtils.php");
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/api/vendhq/client.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");

//TODO: store in SSM
$apiKey = "lsxs_pt_dhCVfmsY4VodPazeQKPFmKHmI570ArgO";
$domainPrefix = "bonniebio";

$client = new VendHQClient($apiKey, $domainPrefix);

$customerID = $_GET['id'] ?? false;
if(!$customerID){
    die("missing query parameter: id");
}

$customers = $client->getCustomerByID($customerID);

if(!$customers->getSuccess()){
    echo "Error: " . $customers->getErrorMessage();
    die();
}

$customerArr = $customers->getBody();
echo "<pre>";

print_r($customerArr['data']);

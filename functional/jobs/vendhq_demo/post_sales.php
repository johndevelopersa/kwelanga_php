<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/api/vendhq/client.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PrincipalDAO.php');

//TO DO: store in SSM
$apiKey = 'lsxs_pt_dhCVfmsY4VodPazeQKPFmKHmI570ArgO';
$domainPrefix = 'bonniebio';

$client = new VendHQClient($apiKey, $domainPrefix);

// reference: https://docs.vendhq.com/docs/sales_101

$sale = (new VendHQSale())
    ->setRegisterId('0afa8de1-1450-11e9-edec-3ad777df825e')
    ->setUserId('616e9cdc-8f2f-4928-bff2-6be104504311')
    ->setSaleDate('2016-05-05 23:35:34')
    ->setCustomerId('0653b8f0-f9bb-11ee-ec2f-2d3d38a20306')
    ->setInvoiceNumber('INV-0002')
    ->setStatus('CLOSED')   //https://docs.vendhq.com/docs/sales_statuses
    ->setNote('TEST SALE');

$productLine = (new VendHQProduct())
    ->setProductId('5c8fd19b-b7ae-0a34-520d-eb16bc389700')
    ->setQuantity(1)
    ->setPrice(12.44)
    ->setTax(1.5);

$sale->addProduct($productLine);

echo '<pre>';
echo ($sale->asJSON());

$saleResponse = $client->createRegisterSale($sale);

if (!$saleResponse->getSuccess()) {
    echo 'Error: ' . $saleResponse->getErrorMessage();
    var_dump($saleResponse);
    die();
}

echo "SUCCESS!!!";
$saleArr = $saleResponse->getBody();

var_dump($saleArr);


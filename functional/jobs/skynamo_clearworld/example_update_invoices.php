<?php
require_once 'ROOT.php';
include_once $ROOT . 'PHPINI.php';
include_once $ROOT . $PHPFOLDER."DAO/db_Connection_Class.php";
require_once __DIR__ . "/../../../libs/api/skynamoclient/vendor/autoload.php";

require_once __DIR__ . '/syknamo_clearworld_config.php';

//local testing
//require_once __DIR__ . '/../skynamoclient/vendor/autoload.php';
//require_once __DIR__ . '/syknamo_clearworld_config.php';


$config = SkynamoClientAPI\Configuration::getDefaultConfiguration();
$config->setApiKey('x-api-key', SkynamoClearworld::getAPIKey());

$apiInstance = new SkynamoClientAPI\Api\InvoicesApi(new GuzzleHttp\Client(), $config);

try {





    //products/items
    $invoiceItems = [];

    //loop

    $item = new \SkynamoClientAPI\Model\InvoiceItem();
    $item->setQuantity(99);
    $item->setProductCode(12176718);

    $invoiceItems[] = $item;


    $invoice = new SkynamoClientAPI\Model\InvoicePost();
    $invoice->setItems($invoiceItems);
    $invoice->setDate(new DateTime("2023-02-01"));
    $invoice->setDueDate(new DateTime("2023-02-01"));
    $invoice->setTotal(1020.20);
    $invoice->setTax(200.10);

    //SET THE TAX INCLUSION: INCLU OR EXCLU.
    //SkynamoClientAPI\Model\InvoicePost::TAX_INCLUSION_INCLUDED,
    //SkynamoClientAPI\Model\InvoicePost::TAX_INCLUSION_EXCLUDED,
    $invoice->setTaxInclusion(SkynamoClientAPI\Model\InvoicePost::TAX_INCLUSION_EXCLUDED);

    //$invoice->setExternalId("TEST-12345");  //appears like a customer invoice no.?
    $invoice->setReference("TEST-12345");

    // need a list of customers: see example_get_customers.php
    /*
     * id:25
       code:179351
       name:GC56 PNP - VAAL MALL
     */
     $invoice->setCustomerId(25);

     /* invoice statuses:
        STATUS_PAID = 'Paid';
        STATUS_NOT_SPECIFIED = 'NotSpecified';
        STATUS_OUT_STANDING = 'OutStanding';
        STATUS_DELETED = 'Deleted';
        STATUS_VOID = 'Void';
        TAX_INCLUSION_INCLUDED = 'Included';
        TAX_INCLUSION_EXCLUDED = 'Excluded';
      */
    $invoice->setStatus(SkynamoClientAPI\Model\InvoicePost::STATUS_PAID);

    //post to API
    $createdResult = $apiInstance->invoicesPost(SkynamoClearworld::getClientName(), [$invoice]);

    if(is_array($createdResult->getData()) && count($createdResult->getData())) {
        $invoiceId = $createdResult->getData()[0]->getId();

        $invoiceResult = $apiInstance->invoicesIdGet(SkynamoClearworld::getClientName(), $invoiceId);

        print_r($invoiceResult);
    }

    //print_r($result->getData());

    //get all invoices.
    /*
    $result = $apiInstance->invoicesGet(SkynamoClearworld::getClientName(), 1, 25, $flags = null);

    if($result->getPage()->getTotalItemCount() == 0) {
        echo "no invoices found!";
        return;
    }
    print_r($result->getData());
    */

} catch (Exception $e) {
    echo "Exception when calling apiInstance->invoicesGet: \n", $e->getMessage(), PHP_EOL;
}

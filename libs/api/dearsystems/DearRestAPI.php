<?php

//just include all objects
foreach (glob(__DIR__ . "/objects/*.php") as $objectFilename) {
    require_once $objectFilename;
}


class DEARRestAPI
{

    protected $accountID;
    protected $applicationKey;
    protected $baseURL;

    public function __construct($baseURL, $accountID, $applicationKey)
    {
        $this->baseURL = trim($baseURL);
        $this->accountID = trim($accountID);
        $this->applicationKey = trim($applicationKey);
    }

    private function request($method, $action, $body = [], $queryParams = [], $timeout = 300): DearHTTPResponseObj
    {
        $requestRaw = null;

        $url = $this->baseURL . '/' . trim($action, '/') . '?' . http_build_query($queryParams);

        $headersArr = [
            'Content-Type: application/json',
            "api-auth-accountid: $this->accountID",
            "api-auth-applicationkey: $this->applicationKey",
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($method != "GET") {
            $requestRaw = json_encode($body, JSON_PRETTY_PRINT);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestRaw);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersArr);

        $rawResponse = curl_exec($ch);
        $curlDebug = curl_getinfo($ch);

        return new DearHTTPResponseObj($url, $method, $requestRaw, $rawResponse, $curlDebug);
    }

    //GetProductById - Product?ID=E6E8163F-6911-40e9-B740-90E5A0A3A996 - returns details of a particular product;
    public function GetProductById(string $productUid): DearProductResponseObj
    {
        $response = $this->request("GET", "Product", [], ["ID" => $productUid]);
        return (new DearProductResponseObj($response->getBody()))->setResponse($response);
    }

    public function GetProducts($page = 1, $limit = 500): DearProductResponseObj
    {
        $response = $this->request("GET", "Product?page=$page&limit=$limit");
        return (new DearProductResponseObj($response->getBody()))->setResponse($response);
    }

    public function CreateSale(DearSaleObj $sale): DearHTTPResponseObj
    {
        return $this->request("POST", "sale", $sale->getArray());
    }

    public function GetSaleById($saleUid): DearHTTPResponseObj
    {
        return $this->request("GET", "sale", [], ['ID' => $saleUid]);
    }

    public function CreateSalesOrder(DearSalesOrderObj $order): DearHTTPResponseObj
    {
        return $this->request("POST", "sale/order", $order->getArray());
    }

    public function CreateSalesInvoice(DearSalesInvoiceObj $invoice): DearHTTPResponseObj
    {
        return $this->request("POST", "sale/invoice", $invoice->getArray());
    }

    public function CreateSalesCreditNote(DearSalesCreditNoteObj $credit): DearHTTPResponseObj
{
    return $this->request("POST", "sale/creditnote", $credit->getArray());
}

    public function UpdateSaleFulfilment($type, $taskID, $status, $lines): DearHTTPResponseObj
    {
        return $this->request("POST", "sale/fulfilment/$type", ["TaskID" => $taskID, "Status" => $status, 'Lines' => $lines], []);
    }


}

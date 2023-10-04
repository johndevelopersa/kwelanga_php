<?php

require_once 'response.php';
require_once 'sale.php';
require_once 'product.php';

class VendHQClient
{
    private $apiKey;
    private $domainPrefix;
    private $baseURL = 'https://[DOMAIN].vendhq.com/';

    /**
     * @param string $apiKey e.g. "lsxs_pt_XXXXXXXXXXXX"
     * @param string $domainPrefix e.g. "bonniebio from [domain].vendhq.com"
     */
    function __construct(string $apiKey, string $domainPrefix)
    {
        $this->apiKey = $apiKey;
        $this->domainPrefix = $domainPrefix;
        $this->baseURL = str_replace('[DOMAIN]', $domainPrefix, $this->baseURL);
    }

    function getCustomers(int $pageSize = 1000, bool $includeDeleted = false): VendHQHTTPResponseObj
    {
        $data = [
            'page_size' => $pageSize,
            'deleted' => $includeDeleted ? 'true' : 'false',
        ];
        return $this->sendRequest("GET", "api/2.0/customers", $data);
    }

    function sendRequest($method, $url, $params = [], $data = false, $timeout = 120): VendHQHTTPResponseObj
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
        }

        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);

        $paramUri = '';
        if (count($params)) {
            $paramUri = '?' . http_build_query($params);
        }

        curl_setopt($curl, CURLOPT_URL, $this->baseURL . trim($url, '/') . $paramUri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $rawResponse = curl_exec($curl);
        $curlDebug = curl_getinfo($curl);
        curl_close($curl);

        return new VendHQHTTPResponseObj($url, $method, $data, $rawResponse, $curlDebug);
    }

    public function getProducts(int $page = 1, int $pageSize = 1000): VendHQHTTPResponseObj
    {
        return $this->sendRequest("GET", "api/2.0/products", ['page' => $page, 'page_size' => $pageSize]);
    }

    public function getUsers(int $pageSize = 100): VendHQHTTPResponseObj
    {
        return $this->sendRequest("GET", "api/2.0/users", ['page_size' => $pageSize]);
    }

    public function getSales(): VendHQHTTPResponseObj
    {
        return $this->sendRequest("GET", "api/2.0/sales", ['page_size' => 100]);
    }

    public function getRegisters(): VendHQHTTPResponseObj
    {
        return $this->sendRequest("GET", "api/2.0/registers", ['page_size' => 100]);
    }

    public function createRegisterSale(VendHQSale $saleObj): VendHQHTTPResponseObj
    {
        return $this->sendRequest('POST', 'api/register_sales', [], $saleObj->asJSON());
    }

    public function getPaymentTypes(int $pageSize = 1000): VendHQHTTPResponseObj
    {
        return $this->sendRequest('GET', 'api/payment_types', ['page_size' => $pageSize]);
    }

    public function getCustomerByID(string $id): VendHQHTTPResponseObj
    {
        return $this->sendRequest('GET', '/api/2.0/customers/' . $id);
    }

}
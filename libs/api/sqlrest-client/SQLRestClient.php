<?php

class SQLRestClient {

    private $baseURI;
    private $apiKey;

    /**
     * @throws Exception
     */
    function __construct($endpoint, $uri, $apiKey){
        $this->baseURI = $endpoint . $uri;
        $this->apiKey = $apiKey;
        $result = $this->Connect();
        if(!$result->IsSuccess()) {
            throw new Exception("error connecting to SQLRest API: {$this->baseURI}");
        }
    }

    function Ping() : SQLRestResponse {
        return $this->httpRequest("GET", "/ping");
    }

    function Connect() : SQLRestResponse{
        return $this->httpRequest("GET", "/connect");
    }

    function Query(string $query) : SQLRestResponse{
        $body = json_encode(["query" => $query]);
        // var_dump($body);
        return $this->httpRequest("POST", "/v1/query", $body);
    }

    function Procedure(string $name, $params = [], $executeOnly = false) : SQLRestResponse{
        $proc = [
            "name" => $name,
            "parameters" => $params,
            "executeOnly" => $executeOnly
        ];
        $body = json_encode($proc);
        return $this->httpRequest("POST", "/v1/procedure", $body);
    }

    private function httpRequest($method, $operation, $body = null) : SQLRestResponse {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL,  $this->baseURI . $operation);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . $this->getHMACHeader($body)
        ]);

        $body = curl_exec($ch);
        $debug = curl_getinfo($ch);
        curl_close($ch);

        return new SQLRestResponse($body, $debug);
    }

    private function getHMACHeader($body = ''): string
    {
        $hmac = "";
        if(!empty($body)){
            $hmac = hash_hmac('sha256', $body, $this->apiKey, false);
        }

        $realm = "kwelanga";
        $timestamp = (string)(new DateTime('now', new DateTimeZone('GMT')))->getTimestamp() * 1000;
        $nonce = uniqid("nouce");

        return join(":", [$realm, $hmac, $nonce, $timestamp]);
    }

}

class SQLRestResponse {

    private $debug;
    private $body;

    function __construct($body, $debug)
    {
        $this->debug = $debug;
        if(strpos($this->debug['content_type'] ?? '', 'application/json' ) !== false){
            $arr = json_decode($body, true);
            if(isset($arr['Data'])){
                $data = [];
                $columns = $arr['Columns'];
                foreach($arr['Data'] as $row){
                    $data[] = array_combine($columns, $row);
                }
                $arr = $data;
            }
            $this->body = $arr;
        } else {
            $this->body = $body;
        }
    }

    function IsSuccess() : bool {
        return $this->StatusCode() >= 200 && $this->StatusCode() <= 299;
    }

    function StatusCode() : int {
        return $this->debug['http_code'] ?? 0;
    }

    function Debug() : array {
        return $this->debug;
    }

    function Data() {
        return $this->body;
    }
}

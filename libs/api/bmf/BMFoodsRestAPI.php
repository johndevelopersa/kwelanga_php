<?php

class BMFoodsRestAPI
{

    protected $hostPort;
    protected $userName;
    protected $password;

    public function __construct($HostPort, $UserName, $Password)
    {
        $this->hostPort = trim(trim($HostPort), '/');
        $this->userName = trim($UserName);
        $this->password = trim($Password);
    }

    public function Request($method, $action, $data = [], $timeout = 60, $downloadFile = '')
    {

        //URI Format: http://<Host>:<Port>/Customer/<Account Code>/<Branch Code>?UserName=<User Name>&Password=<Password>&CompanyName=<Company Name>
        $url = $this->hostPort . '/' . trim($action," /");

        $requestRaw = null;

        $ch = curl_init();

        //basic auh
        curl_setopt($ch, CURLOPT_USERPWD, $this->userName . ":" . $this->password);
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($method != "GET") {
            $requestRaw = $data;
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestRaw);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $rawResponse = curl_exec($ch);
        $curlDebug = curl_getinfo($ch);
        
        return $rawResponse;
    
    
    }

}
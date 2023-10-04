<?php

require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "libs/aws/aws-autoloader.php";
require_once $ROOT . $PHPFOLDER . "TO/ErrorTO.php";
require_once $ROOT . $PHPFOLDER . 'properties/Constants.php';

use Aws\Ssm\SsmClient;

class Config
{

    private static function getClient(): SsmClient
    {
        return new SsmClient([
            'region' => DEFAULT_SSM_REGION,
            'version' => 'latest',
            'credentials' => [
                'key' => SSM_ACCESS_ID,
                'secret' => SSM_SECRET_KEY,
            ],
        ]);
    }

    public static function GetParam($key): ConfigParam
    {
        return self::getParameter($key, false);
    }

    public static function GetSecret($key): ConfigParam
    {
        return self::getParameter($key, true);
    }

    private static function getParameter($key, $decrypt = false): ConfigParam
    {
        return new ConfigParam(self::getClient()->getParameter([
            'Name' => $key,
            'WithDecryption' => $decrypt
        ]));
    }
}

class ConfigParam
{
    private $type;
    private $dataType;
    private $lastModifiedDate;
    private $value;

    function __construct($arr)
    {
        if(isset($arr['Parameter'])){
            $this->dataType = $arr['Parameter']['DataType'] ?? '';
            $this->lastModifiedDate = $arr['Parameter']['LastModifiedDate'] ?? '';
            $this->type = $arr['Parameter']['Type'] ?? '';
            $this->value = $arr['Parameter']['Value'] ?? '';
        }
    }

    function GetDataType(){
        return $this->dataType;
    }

    function AsString() : string {
        return strval($this->value);
    }

    function AsJSONString() : array {
        return json_decode(strval($this->value), true);
    }

}


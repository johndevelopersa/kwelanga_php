<?php

class NewRelic
{

    const FLUENT_ENDPOINT = "localhost";
    const FLUENT_PORT = 5170;

    public static function logEvent($logType, $script, $message, $attributes = []) : bool {

        // construct request
        $data = [
            'timestamp' => (new DateTime)->getTimestamp(),
            'script' => $script,
            'logtype' => $logType,
            'message' => $message,
            'attr' => $attributes,
        ];

        //post log to new relic via FLUENT
        return self::postLog($data);
    }

    private static function postLog($data = [], $timeout = 15)
    {
        $fp = fsockopen(SELF::FLUENT_ENDPOINT, SELF::FLUENT_PORT, $errno, $errstr, 30);
        if (!$fp) {
            //echo "$errstr ($errno)<br />\n";
            return false;
        } else {
            fwrite($fp, json_encode($data));
            fclose($fp);
        }
        return true;
    }

}
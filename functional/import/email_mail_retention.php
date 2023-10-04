<?php

/*---------------------------------------------------------------
 * MAIL retention cleanup script
 *---------------------------------------------------------------*/

require_once('ROOT.php');
require_once($ROOT . 'PHPINI.php');
require_once($ROOT . $PHPFOLDER . 'libs/pop3.php');
require_once($ROOT . $PHPFOLDER . "libs/CommonUtils.php");
require_once $ROOT . $PHPFOLDER . "libs/Config.php";

error_reporting(-1);
ini_set('display_errors', 1);

echo 'started: ' . CommonUtils::getGMTime(0) . "\n";

//get config
$mailBoxesArr = Config::GetSecret('mail_retention_json')->AsJSONString();
if (!$mailBoxesArr || !count($mailBoxesArr)) {
    die("No mailboxes found");
}

foreach ($mailBoxesArr as $boxArr) {

    echo str_repeat("-", 45) . "\n";

    //parameters
    $USERNAME = $boxArr['username'];
    $PASSWORD = $boxArr['password'];
    $SERVER = $boxArr['server'];
    $SERVER_PORT = (int)$boxArr['port'];
    $RETENTION_DAYS = (int)($_GET['days'] ?? 5);

    $st = microtime(true);
    set_time_limit(5 * 60);
    $jobCount = 0;

    $pop3 = new pop3_class;
    $pop3->hostname = $SERVER;
    $pop3->port = $SERVER_PORT;
    $pop3->tls = 0;
    $pop3->realm = "";
    $pop3->workstation = "";
    $pop3->authentication_mechanism = "USER";
    $pop3->debug = 0;
    $pop3->html_debug = 0;
    $pop3->join_continuation_header_lines = 1;


    //connect
    $err = $pop3->Open();
    if ($err != "") {
        die("ERROR: connecting to pop3 mail: {$err}");
    }
    echo "Connected to POP3 Server '" . $pop3->hostname . "'... \tOK\n";


    //login
    $err = $pop3->Login($USERNAME, $PASSWORD);
    if ($err != "") {
        die("ERROR: login failed: {$err}");
    }
    echo "User <{$USERNAME}> logged in... \tOK\n";


    //GET STATS
    $messages = null;
    $err = $pop3->Statistics($messages, $size);
    if ($err != "") {
        die("ERROR: statistics call failed: {$err}");
    }
    echo "#$messages messages in the mail box. ($size bytes)\n";


    //get messages
    $result = $pop3->ListMessages("", 0);
    if (GetType($result) != "array") {
        die("ERROR: result not  array: " . GetType($result));
    }

    if (!$messages) {
        echo "No Messages \n[***EOS***]";
        die();
    }


    //LOOP THROUGH MESSAGES
    for ($i = 1; $i <= $messages; $i++) {

        //just fetch the headers per email.
        $err = $pop3->RetrieveMessage($i, $headers, $ignore, 0);
        if ($err != "") {
            echo "Error opening message {$i}: {$err}\n";
            continue;
        }

        $deliveryDate = false;
        foreach ($headers as $header) {
            $headerLower = ltrim(strtolower($header));
            if (substr($headerLower, 0, 14) == "delivery-date:") {
                $deliveryDate = ltrim(substr($header, 14));
            }
        }

        $deliveryDateObj = DateTime::createFromFormat(DateTime::RFC1123, $deliveryDate);
        $dateObj = (new DateTime);
        if (!$deliveryDateObj) {
            echo "Error parsing message 'Delivery Date'\n";
            continue;
        }

        $ageDays = $dateObj->diff($deliveryDateObj)->days;
        if ($ageDays > $RETENTION_DAYS) {
            echo "Deleting old message: {$i} - age: {$ageDays}\n";
            $err = $pop3->DeleteMessage($i);
            $jobCount++;
            if ($err != "") {
                echo "ERROR: deleting message from POP3 server: {$err}\n";
            }
        } else {
            echo "Retaining message: {$i} - age: {$ageDays}\n";
        }
    }

    $err = $pop3->Close();
    if ($err != "") {
        die("ERROR: disconnecting from POP3 server: {$err}");
    }

    echo "Disconnected from POP3 Server.\n";

}

echo "\n-------------------------------------------\n";
echo '[@>>>JOBS:' . $jobCount . ';TT:', round(microtime(true) - $st, 4), '@]' . "\n";
echo '[***EOS***]';

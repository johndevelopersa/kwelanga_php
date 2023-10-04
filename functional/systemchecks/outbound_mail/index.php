<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingDistributionTO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostDistributionDAO.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
require_once $ROOT . $PHPFOLDER . "libs/Config.php";

error_reporting(-1);
ini_set('display_errors', 1);

$divider = str_repeat("-", 45) . "\n";
$statST = microtime(true);
$apiKey = Config::GetSecret("mailslurp_api_key")->AsString();

echo "started: " . (CommonUtils::getGMTime(0)) . "\n";

echo $divider;
echo "\tOutbound Mail System-Check \n";
echo $divider;

$dbConn = new dbConnect();
$dbConn->dbConnection();

echo "creating email...\n";

$postingDistributionTO = new PostingDistributionTO;
$postingDistributionTO->DMLType = "INSERT";
$postingDistributionTO->deliveryType = BT_EMAIL;
$postingDistributionTO->subject = "SYSTEM CHECK TEST EMAIL " . date("Y-m-d H:i:s");
$postingDistributionTO->body = 'BODY - ' . date("Y-m-d H:i:s");
//$postingDistributionTO->attachmentFile = '/ftp/rvl/UpliftDocuments/R-'.trim($mDetails[0]['deliver_name']) .' - ' . ltrim($mDetails[0]['document_number'],'0') .'.pdf';
$postingDistributionTO->destinationAddr = '1db79895-d143-4bc0-a68f-569805d954b4@mailslurp.com'; //$_GET['to'] ?? "onyx@gouws.co";
$postingDistributionTO->attachmentFile = 's3://kos.storage.cpt/archives/reports/2023/06/22/Stock_Movement_Report_.20230622041147.2.4.2259.csv,s3://kos.storage.cpt/archives/reports/2023/06/22/New_Daily_Sales_P216A.20230622041140.11.216.9945.csv';
//$postingDistributionTO->destinationAddr = "onyx@example.com";
$postDistributionDAO = new PostDistributionDAO($dbConn);

echo "sending to... {$postingDistributionTO->destinationAddr}\n";

$st = microtime(true);
$dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
if ($dResult->isSuccess()) {
    echo "successfully queued\n";
} else {
    echo "failed to queue email!\n";
    var_dump($dResult);
    die();
}

$dbConn->dbinsQuery("commit;");

echo "purging receiving inbox...\n";
$response = mailslurp_request($apiKey, "DELETE", "emails");

echo "waiting for email to arrive...\n";

//max is 30 seconds...
sleep(30);

$response = mailslurp_request($apiKey, "GET", "emails");
if (!count($response) || !isset($response['content'][0]['from'])) {
    echo "error no emails found!\n";
    die();
}

echo "email found!\n";
$emailArr = $response['content'][0];

echo "verifying fields...\n";

if (strcmp($emailArr['from'], DEFAULT_FROM_EMAIL) !== 0) {
    echo "error from field mismatch!\n";
    echo "expected: " . DEFAULT_FROM_EMAIL . "\n";
    echo "got: {$emailArr['from']}\n";
    die();
}
if (strcmp($emailArr['subject'], $postingDistributionTO->subject) !== 0) {
    echo "error subject field mismatch!\n";
    echo "expected: {$postingDistributionTO->subject}\n";
    echo "got: {$emailArr['subject']}\n";
    die();
}
//TODO: the sending script appends a view online HTML portion to the body, so the MD5 hash will never match. We need to include the view online HTML on the sending script!
//if (strcmp($emailArr['bodyMD5Hash'], md5($postingDistributionTO->body)) !== 0) {
//    echo "error body MD5 field mismatch!\n";
//    echo "expected: ".md5($postingDistributionTO->body)."\n";
//    echo "got: {$emailArr['bodyMD5Hash']}\n";
//    die();
//}
if (count($emailArr['attachments']) != 2) {
    echo "error attachments count doesn't match!\n";
    echo "got: " . count($emailArr['attachments']) . "\n";
    die();
}

echo "successfully verified\n";

$statET = microtime(true);
$statTT = round($statET - $statST, 4);
echo $divider;
echo "[@>>>JOBS:TT:" . $statTT . "@]\n";  //stat line.
echo '[***EOS***]';

function mailslurp_request($apiKey, $method, $action)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.mailslurp.com/$action");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "x-api-key: $apiKey"
    ));
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        return json_decode($result, true);
    }
    curl_close($ch);
}
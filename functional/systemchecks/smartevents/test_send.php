<?php

//This manual tests the SmartQueue library and that we can createQueues, sendMessages and receiveMessages successfully

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "libs/smartqueue/SmartQueue.php";
require_once $ROOT . $PHPFOLDER . 'libs/CommonUtils.php';

error_reporting(-1);
ini_set('display_errors', 1);

$testQueueName = 'QUEUE_URL.fifo';
$statST = microtime(true);

echo "Check Started: " . (CommonUtils::getGMTime(0)) . "\n";
echo str_repeat("-", 50) . "\n";

echo "Creating Queue...\t";

//$result = SmartQueue::purgeQueue($testQueueName);
//var_dump($result);

$result = SmartQueue::getQueue($testQueueName);

if (!is_array($result)) {
    var_dump($result);
    die("error!");
}

var_dump($result);

echo "{$result['ApproximateNumberOfMessages']}\n";


//$result = SmartQueue::createQueue(
//    $queueName = $testQueueName,
//    $delaySeconds = null,
//    $maximumMessageSize = null,
//    $messageRetentionPeriod = strtotime('1 day', 0),
//    $visibilityTimeout = 30,
//    $receiveMessageWaitTimeout = null
//);
//if ($result->isError()) {
//    echo "Error creating queue:\n";
//    echo $result->getDescription();
//    die();
//}

echo "Messages in Queue...\t";

$result = SmartQueue::getQueue($testQueueName);

echo "{$result['ApproximateNumberOfMessages']}\n";

sleep(1);

echo "Sending message...\t";

$sendTime = microtime(true);

$dataTest = uniqid();
$msg = (new SmartEventTO(SE_NOTIFICATION))
    ->setGeneralReference1($dataTest);

$result = SmartQueue::Publish($testQueueName, $msg);

if ($result->isError()) {
    echo "Error sending message!";
    var_dump($result->getDescription());
    die();
}

echo "Success - took (" . (microtime(true) - $sendTime) ."\n";
echo "Sequence No:\t\t" . $result->identifier . "\n";
echo "MessageID:\t\t" . $result->identifier2 . "\n";

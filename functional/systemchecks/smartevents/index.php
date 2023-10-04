<?php

//This manual tests the SmartQueue library and that we can createQueues, sendMessages and receiveMessages successfully

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "libs/smartqueue/SmartQueue.php";
require_once $ROOT . $PHPFOLDER . 'libs/CommonUtils.php';

error_reporting(-1);
ini_set('display_errors', 1);

$testQueueName = 'TEST_QUEUE.fifo';
$statST = microtime(true);

echo "Check Started: " . (CommonUtils::getGMTime(0)) . "\n";
echo str_repeat("-", 50) . "\n";

echo "Creating Queue...\t";

//$result = SmartQueue::purgeQueue($testQueueName);
//var_dump($result);

$result = SmartQueue::createQueue(
    $queueName = $testQueueName,
    $delaySeconds = null,
    $maximumMessageSize = null,
    $messageRetentionPeriod = strtotime('1 day', 0),
    $visibilityTimeout = 30,
    $receiveMessageWaitTimeout = null
);
if ($result->isError()) {
    echo "Error creating queue:\n";
    echo $result->getDescription();
    die();
}

echo "Success\n";
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

$endTime = microtime(true);

echo "Success (" . round($endTime-$sendTime, 8) . ")\n";
echo "Sequence No:\t\t" . $result->identifier . "\n";
echo "MessageID:\t\t" . $result->identifier2 . "\n";

//-----------------------------------------------------------------

sleep(1);

echo "Receiving message...\t";


$result = SmartQueue::Receive($testQueueName, 5);

if ($result->hasError()) {
    echo "error receiving messages!";
    echo $result->getErrorTo()->getDescription();
    die();
}

if (!$result->hasMessages()) {
    echo "error, expected 1 message, got zero!";
    var_dump($result);
    die();
}

echo "Success\n";

foreach ($result->getMessages() as $message) {

    $sentDateTime = $message->getSentTimestamp()
        ->setTimezone(new DateTimeZone('Africa/Johannesburg'))
        ->format(DATE_RFC3339);
    $sentAgeSeconds = (new DateTime)->getTimestamp() - $message->getSentTimestamp()->getTimestamp();

    echo "MessageID:\t\t{$message->getMessageID()}\n";
    echo "MessageGroupID:\t\t{$message->getMessageGroupID()}\n";
    echo "SequenceNumber:\t\t{$message->getSequenceNumber()}\n";
    echo "SentTimestamp:\t\t{$sentDateTime} ({$sentAgeSeconds}s ago)\n";

    if ($message->getSmartEvent()->generalReference1 !== $dataTest) {
        echo "error on testing sent and received data!";
        var_dump($dataTest);
        var_dump($message->getSmartEvent());

        //commit this bad message out of the queue!
        $commitResult = $message->commitMessage();

        die();
    }

    echo "Committing message...\t";

    $commitResult = $message->commitMessage();

    if ($commitResult->isError()) {
        echo $commitResult->getDescription();
        var_dump($commitResult);
        die();
    }

    echo "Success\n";

}

echo "Checking messages...\t";

$result = SmartQueue::Receive($testQueueName);

if ($result->hasMessages()) {
    die("expected no messages");
}

echo "Success (no more messages)\n";


$statET = microtime(true);
$statTT = round($statET - $statST, 4);
echo str_repeat("-", 50) . "\n";
echo "[@>>>JOBS:TT:" . $statTT . "@]\n";  //stat line.
echo '[***EOS***]';
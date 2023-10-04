<?php

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "libs/smartqueue/SmartQueue.php";

$result = SmartQueue::createQueue(
    $queueName = 'TEST_QUEUE.fifo',
    $delaySeconds = null,
    $maximumMessageSize = null,
    $messageRetentionPeriod = strtotime('1 day', 0),
    $visibilityTimeout = 30,
    $receiveMessageWaitTimeout = null
);

var_dump($result);

if($result->isError()){
    echo $result->getDescription();
    die();
}




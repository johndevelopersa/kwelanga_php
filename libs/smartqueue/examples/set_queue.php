<?php

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "libs/smartqueue/SmartQueue.php";

$result = SmartQueue::getQueue('POOO_QUEUE.fifo');

var_dump($result);

$result = SmartQueue::setQueueAttr(
    $queueName = 'POOO_QUEUE.fifo',
    $delaySeconds = null,
    $maximumMessageSize = null,
    $messageRetentionPeriod = strtotime('7 day', 0),
    $visibilityTimeout = 30,
    $receiveMessageWaitTimeout = null
);

if($result->isError()){
    echo $result->getDescription();
    die();
}

var_dump($result->getObject()['new_attributes']);


<?php

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "libs/smartqueue/SmartQueue.php";


$st = microtime(true);

$msg = (new SmartEventTO(SE_NOTIFICATION))
    ->setDataUid(3232)
    ->setGeneralReference1("pooo")
    ->setMetaArr(['file' => 'dsds']);

$result = SmartQueue::Publish('QUEUE_URL.fifo', $msg);

if ($result->isSuccess()) {
    echo "Successfully sent test Smart Event message...\n";
    echo "Seq No:" . $result->identifier . "\n";
    echo "MessageID:" . $result->identifier2 . "\n";
} else {
    echo "ERROR SENDING MESSAGE....";
    var_dump($result);
}

$et = microtime(true);

echo "Time:" . ($et - $st) . "\n";
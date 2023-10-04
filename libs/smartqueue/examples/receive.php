<?php

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "libs/smartqueue/SmartQueue.php";


$queueUrl = "POOO_QUEUE.fifo";

$result = SmartQueue::Receive($queueUrl);

if($result->hasError()){

    //echo poo
    echo $result->getErrorTo()->getDescription();
    die();
}

if($result->hasMessages()){

    foreach($result->getMessages() as $message){
        $event = $message->getSmartEvent();

        echo "Type:" . $event->getType() . "\n";
        echo "Data UID:" . $event->getDataUid() . "\n";

        echo "MessageID:" . $message->getMessageID() . "\n";
        echo "essageGroupID:" . $message->getMessageGroupID() . "\n";
        echo "SequenceNumber:" . $message->getSequenceNumber() . "\n";
        echo "ApproxReceiveCount:" . $message->getApproxReceiveCount() . "\n";
        echo "SentTimestamp:" . print_r($message->getSentTimestamp(), true) . "\n";

        //var_dump($message->getAttr());

        $commitResult = $message->commitMessage();
        if($commitResult->isError()){
            echo $commitResult->getDescription();
            var_dump($commitResult);
        }

        //var_dump($commitResult);

        die();
    }

} else {
    echo "No messages in queue. \n";
}





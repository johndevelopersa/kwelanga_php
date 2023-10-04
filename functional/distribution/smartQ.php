<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . 'libs/smartqueue/SmartQueue.php');

$entityBody = file_get_contents('php://input');
if (empty($entityBody)) {
    echo 'empty request!';
    return;
}
if(!isset($_GET['type'])){
    echo 'missing smart type';
    return;
}
if(!isset($_GET['queueName'])){
    echo 'missing queueName';
    return;
}

$metaArr = json_decode($entityBody, true);

$id = 0;
if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
}
if(isset($metaArr['uid'])){
    $id = (int)$metaArr['uid'];
}

//Publish event to SQS...
$msg = (new SmartEventTO((string)$_GET['type']))
    ->setDataUid($id)
    ->setMetaArr($metaArr);

$publishResult = SmartQueue::Publish((string)$_GET['queueName'], $msg);

if ($publishResult->isError()) {
    //throw new Exception("SmartQueue failed: {$publishResult->getDescription()}");
    http_response_code(500);
    echo "SmartQueue failed: {$publishResult->getDescription()}";
} else {
    header("Content-Type: application/json");
    $response = [
        "Status" => "OK",
        "MessageId" => $publishResult->getIdentifier2(),
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
}

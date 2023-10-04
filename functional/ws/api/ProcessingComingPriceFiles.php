<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/ProcessingComingPriceFiles.php 

include_once 'ROOT.php';
include_once $ROOT . 'PHPINI.php';
include_once $ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php";
include_once $ROOT . $PHPFOLDER . 'DAO/PostDistributionDAO.php';
include_once $ROOT . $PHPFOLDER . 'DAO/ApiDAO.php';
include_once $ROOT . $PHPFOLDER . 'properties/Constants.php';
include_once $ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php';
include_once $ROOT . $PHPFOLDER . 'libs/CommonUtils.php';
include_once $ROOT . $PHPFOLDER . 'libs/storage/Storage.php';
include_once $ROOT . $PHPFOLDER . "libs/smartqueue/SmartQueue.php";
include_once $ROOT . $PHPFOLDER . 'libs/EncryptionClass.php';

set_time_limit(300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}

$newApiDAO = new APIDAO($dbConn);

new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);

$statST = microtime(true);
$apiRequestQueueName = "API-RequestAsyncPrices.fifo";    //make to constant
$maxBatchesPerRun = $GET['maxBatchesPerRun'] ?? 10;
$maxMessagesPerBatch = $GET['maxMessagesPerBatch'] ?? 5;
$waitTimeoutSec = 10;           //long polling: total script time : $waitTimeoutSec * $maxBatchesPerRun
$priceUpdateBatch = 200;
$visibilityTimeoutSec = 120;    //all messages need to be processed by this timeout.
$batchNo = 1;
$msgCount = 0;

echo "<pre>start: \t" . CommonUtils::getGMTime(0) . "<BR>";

while ($batchNo <= $maxBatchesPerRun) :

    echo "BATCH.{$batchNo}] long-polling for {$waitTimeoutSec} seconds for a maximum {$maxMessagesPerBatch} messages\n";

    $result = SmartQueue::Receive($apiRequestQueueName, $maxMessagesPerBatch, $visibilityTimeoutSec, $waitTimeoutSec);
    if ($result->hasError()) {
        echo "BATCH.{$batchNo}] error receiving messages\n";
        echo $result->getErrorTo()->getDescription();
        return; //stop batches
    }
    if (!$result->hasMessages()) {
        echo "BATCH.{$batchNo}] no more messages\n";
        echo "time: \t" . round(microtime(true) - $statST, 4) . "s\n";
        echo "[***EOS***]";
        return; //stop batches
    }

    echo "BATCH.{$batchNo}] messages retrieved... " . count($result->getMessages()) . "\n";

    if ($batchNo == 1) {
        $queueStats = SmartQueue::getQueue($apiRequestQueueName);
        echo "[STATS] total in queue:\t" . ($queueStats['ApproximateNumberOfMessages'] ?? '??') . "\n";
    }
    $stopBatches = false;

    //process messages
    foreach ($result->getMessages() as $messageNo => $message) :

        $messageStart = microtime(true);
        $msgCount++;

        $sentDateTime = $message->getSentTimestamp()
            ->setTimezone(new DateTimeZone('Africa/Johannesburg'))
            ->format(DATE_RFC3339);

        $ageSeconds = (new DateTime)->getTimestamp() - $message->getSentTimestamp()->getTimestamp();

        $messageId = $message->getMessageID();
        $eventType = $message->getSmartEvent()->getType();
        $fileSeq = $message->getSmartEvent()->getTypeUid();

        echo str_repeat("-", 45) . "\n";
        echo "BATCH.{$batchNo} MSG.$messageNo: eventType... \t{$eventType}\n";
        echo "BATCH.{$batchNo} MSG.$messageNo: messageId... \t{$messageId}\n";
        echo "BATCH.{$batchNo} MSG.$messageNo: queueTime: \t{$sentDateTime} ({$ageSeconds}s ago)\n";
        echo "BATCH.{$batchNo} MSG.$messageNo: fileSeq: \t$fileSeq\n";
        if ($ageSeconds > 600) {
            echo "WARNING: possible stuck message\n";
        }

        if ($eventType == "API_ASYNC_PRICE_REQUEST") {

            //THIS HANDLES THE ASYNC API REQUEST FOR A BULK PRICE UPDATE.

            //decode the incoming message array
            $data = $message->getSmartEvent()->getMetaArr();

            $principalUid = $data['principalUid'];
            $uri = $data['uri'];

            echo "BATCH.{$batchNo} MSG.$messageNo: creating bulk price update... \n";
            echo "BATCH.{$batchNo} MSG.$messageNo: principal: \t$principalUid\n";
            echo "BATCH.{$batchNo} MSG.$messageNo: request payload: \t$uri\n";

            //validate the uri of the s3 file
            $s3FilePartsArr = parse_url($uri);
            if (!isset($s3FilePartsArr['scheme']) || !$s3FilePartsArr['scheme'] === "s3") {
                BroadcastingUtils::sendAlertEmail("System Error:" . __FILE__, "URI invalid - Message:\n<br><pre>" . print_r($message, true) . "</pre>", "Y");
                $commitResult = $message->commitMessage();  //remove bad message
            }

            //fetch the price update file
            $priceUpdateFile = Storage::getObject($s3FilePartsArr['host'], $s3FilePartsArr['path']);
            if (!$priceUpdateFile) {
                BroadcastingUtils::sendAlertEmail("System Error:" . __FILE__, "Missing price file - {$uri} Message:\n<br><pre>" . print_r($message, true) . "</pre>", "Y");
                $commitResult = $message->commitMessage();  //remove bad message
            }

            $priceUpdateArr = json_decode($priceUpdateFile->body, true);
            if (!isset($priceUpdateArr['principalUid']) || !isset($priceUpdateArr['priceReference']) || !isset($priceUpdateArr['priceList'])) {
                BroadcastingUtils::sendAlertEmail("System Error:" . __FILE__, "Invalid price payload (missing a critical key) - {$uri} Message:\n<br><pre>" . print_r($message, true) . "</pre>", "Y");
                $commitResult = $message->commitMessage();  //remove bad message
            }

            //split the updates into batches
            $priceUpdateChunksArr = array_chunk($priceUpdateArr['priceList'], $priceUpdateBatch, false);

            $priceUpdateHeaderArr = [
                'principalUid' => $priceUpdateArr['principalUid'],
                'priceReference' => $priceUpdateArr['priceReference'],
                'totalBatches' => count($priceUpdateChunksArr),
                'original_file' => $uri,
                'priceListBatch' => []  //placeholder for the batch
            ];

            $priceUpdateBatchNo = 1;
            foreach ($priceUpdateChunksArr as $priceListBatch) :

                $priceUpdateHeaderArr['priceListBatch'] = $priceListBatch;

                $msg = (new SmartEventTO("API_ASYNC_PRICE_REQUEST_BATCH"))
                    ->setTypeUid($fileSeq)
                    ->setDataUid($priceUpdateBatchNo)
                    ->setMetaArr($priceUpdateHeaderArr);

                $batchPublishResult = SmartQueue::Publish("API-RequestAsyncPrices.fifo", $msg);
                if ($batchPublishResult->isError()) {
                    echo "SmartQueue failed: {$batchPublishResult->getDescription()}";
                    BroadcastingUtils::sendAlertEmail("System Error:" . __FILE__, "Price update batch publish error:" . $batchPublishResult->getDescription(), "Y");
                    exit;
                }

                echo "BATCH.{$batchNo} MSG.$messageNo: published " . $batchPublishResult->identifier2 . "\n";

                $priceUpdateBatchNo++;

            endforeach;


            echo "BATCH.{$batchNo} MSG.$messageNo: created batches:\t " . count($priceUpdateChunksArr) . "\n";

            //commit the price batch update message
            $commitResult = $message->commitMessage();

            //immediately stop doing additional batches.
            $stopBatches = true;

        } else if ($eventType == "API_ASYNC_PRICE_REQUEST_BATCH") {

            //THIS PROCESSES A SUBSET/BATCH OF THE PRICE UPDATE
            $data = $message->getSmartEvent()->getMetaArr();

            $principalUid = $data['principalUid'];
            $priceReference = $data['priceReference'];
            $originalFile = $data['original_file'];
            $totalBatches = $data['totalBatches'];
            $currentBatch = $message->getSmartEvent()->getDataUid();
            $priceListBatch = $data['priceListBatch'];

            $clearOldPrices = 'Y';     //TODO this should be a principal setting
            $recCnt = 0;
            if ($priceReference == 'DEALS') {
                $updateType = 'S';
            } else {
                $updateType = 'C';
            }

            echo "BATCH.{$batchNo} MSG.$messageNo\n";
            echo "\tprocessing price update batch... \n";
            echo "\tprincipal: \t$principalUid\n";
            echo "\tupdateType: \t$updateType\n";
            echo "\tbatch: \t$currentBatch/$totalBatches\n";
            echo "\toriginal price payload: \t$originalFile\n";

            //process the price update batch
            foreach ($priceListBatch as $detRow) {

                foreach ($detRow as $fKey => $fRow) {
                    //print_r($fKey);
                    //echo "<br>";
                    if ($updateType == 'S') {
                        if ($fKey == 'customerNumber') {
                            $chainStor = $fRow;
                        }
                    } else {
                        if ($fKey == 'customerGroup') {
                            $chainStor = $fRow;
                        }
                    }
                    if ($fKey == 'ProdCode') {
                        $prdCde = $fRow;
                    }
                    if ($fKey == 'discountType') {
                        $dtype = $fRow;
                    }
                    if ($fKey == 'listPrice') {
                        $listP = $fRow;
                    }
                    if ($fKey == 'startDate') {
                        $sDate = $fRow;
                    }
                    if ($fKey == 'EndDate') {

                        $eDate = $fRow;

                        $errorTO = $newApiDAO->loadPrices($principalUid, $chainStor, $prdCde, $listP, $dtype, $sDate, $eDate, $updateType);
                        if ($errorTO->type = FLAG_ERRORTO_SUCCESS) {
                            $recCnt++;
                            //echo "\t" . $errorTO->description . "\n";
                        } else {
                           echo "BATCH.{$batchNo}] error: \t" . $errorTO->message . "\n";
                           break;
                        }
                    }
                }
            }

            //commit this bulk price batch
            $dbConn->dbQuery("commit");

            //commit this price batch
            $commitResult = $message->commitMessage();
        } else {
            //unknown type
            $errMsg = "unknown message type: " . $eventType . " (message: $messageId)";
            BroadcastingUtils::sendAlertEmail("System Error:" . __FILE__, $errMsg, "Y");
            $commitResult = $message->commitMessage();
        }

        echo "BATCH.{$batchNo} MSG.$messageNo: took: " . round(microtime(true) - $messageStart, 6) . "s\n";

    endforeach; //end of message batch

    $batchNo++;

    if($stopBatches){
        break;
    }

endwhile;


echo str_repeat("-", 45) . "\n";
echo "completed batches: \t" . $batchNo . "\n";
echo "completed messages: \t" . $msgCount . "\n";
echo "end: \t" . CommonUtils::getGMTime(0) . "\n";
echo "time: \t" . round(microtime(true) - $statST, 4) . "s\n";
echo '[***EOS***]';

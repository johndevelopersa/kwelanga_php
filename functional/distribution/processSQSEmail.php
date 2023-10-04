<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostDistributionDAO.php');
include_once $ROOT . $PHPFOLDER . 'libs/storage/Storage.php';
require_once $ROOT . $PHPFOLDER . "libs/smartqueue/SmartQueue.php";
include_once($ROOT . $PHPFOLDER . 'libs/EncryptionClass.php');

set_time_limit(300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$statST = microtime(true);
$emailQueueName = 'distribution_email.fifo';    //move to constant
$maxMessages = 10;
$maxErrorCount = 30;
$waitTimeoutSec = 15;
$visibilityTimeoutSec = 120;    //all ten messages need to be processed by this timeout.
echo "start: \t" . CommonUtils::getGMTime(0) . "<BR>";

echo "long-polling for {$waitTimeoutSec} seconds for a maximum {$maxMessages} messages\n";

$result = SmartQueue::Receive($emailQueueName, $maxMessages, $visibilityTimeoutSec, $waitTimeoutSec);
if ($result->hasError()) {
    echo "error receiving messages\n";
    echo $result->getErrorTo()->getDescription();
    die();
}
if (!$result->hasMessages()) {
    echo "no more messages\n";
    echo "time: \t" . round(microtime(true) - $statST, 4) . "s\n";
    echo "[***EOS***]";
    die();
}

echo "message retrieved\n";

//first loop
$queueStats = SmartQueue::getQueue($emailQueueName);

echo "total in queue:\t" . ($queueStats['ApproximateNumberOfMessages'] ?? '??') . "\n";

// only connect to db when there "are" actual messages....
new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}

$eC = new EncryptionClass();


foreach ($result->getMessages() as $message) {

    $sendResult = new ErrorTO();
    $sentDateTime = $message->getSentTimestamp()
        ->setTimezone(new DateTimeZone('Africa/Johannesburg'))
        ->format(DATE_RFC3339);

    $ageSeconds = (new DateTime)->getTimestamp() - $message->getSentTimestamp()->getTimestamp();

    $d = $message->getSmartEvent()->getMetaArr();
    if (isset($d['message_id'])) {
        $emailEventArr = $message->getSmartEvent()->getMetaArr();
        //revert to legacy format.
        $d = [
            'destinationAddr' => $d['to'],
            'messageId' => $d['message_id'],
            'attachmentFile' => '',
            'destinationUserUId' => 0,
            'subject' => $d['subject'],
            'body' => $d['body'],
            'plainBody' => $d['plain_body'],
            'error_cnt' => $d['error_cnt'] ?? 0,
        ];

        if (isset($emailEventArr['attachments'])) {
            $attachments = [];
            foreach ($emailEventArr['attachments'] as $attach) {
                if ($attach != "NULL") {
                    $attachments[] = trim($attach, "'");
                }
            }
            $d['attachmentFile'] = join(',', $attachments);
        }
    }

    //format error
    if (!isset($d['destinationAddr'])) {
        echo "FORMAT ERROR: " . print_r($d, true);
        var_dump($message);

        //$dResult = (new PostDistributionDAO($dbConn))->setDistributionResult($d["uid"], "SQS Format Error", FLAG_ERRORTO_ERROR);
        $commitResult = $message->commitMessage();  //commit this bad message
        continue;
    }

    $d["uid"] = $message->getSmartEvent()->getDataUid();
    $d['error_cnt'] = $d['error_cnt'] ?? 0;

    $messageId = $d['messageId'] ?? $message->getSmartEvent()->getDataUid();

    echo str_repeat("-", 45) . "\n";
    echo "messageId... \t{$messageId} ({$d["uid"]})\n";
    echo "queueTime: \t{$sentDateTime} ({$ageSeconds}s ago)\n";
    echo "address: \t{$d['destinationAddr']}\n";

    if ($ageSeconds > 600) {
        echo "WARNING: possible stuck message\n";
    }
    if($d['error_cnt'] > 0){
        echo "RETRYING MESSAGE: error count: {$d['error_cnt']}\n";
    }

    //online version linking
    $dUId = $eC->encryptUIDValue($d["uid"], 2, 8);
    $online = '<center><div style="font-family:Arial,verdana,tahoma;font-size:10px;" align="center">Is this email not displaying correctly? <a href="http://' . $_SERVER['SERVER_NAME'] . '/systems/kwelanga_system/r/?e=' . $dUId . '" target="_blank">View it in your browser</a></div></center><br>';

    if (($d["destinationUserUId"] != "") && ($d["attachmentFile"] == "")) {

        // use the user id instead for certain email outputs
        $sendResult = BroadcastingUtils::sendEmailHTMLUser($d["destinationUserUId"], $dbConn, $d["body"], $d["subject"], [], $messageId);

    } else if ($d["destinationAddr"] != "") {

        // use the supplied raw email address directly as an array
        if (!preg_match(GUI_PHP_EMAIL_REGEX, $d["destinationAddr"])) {

            $errMsg = "Invalid email address for {$d["destinationAddr"]}";
            $dResult = (new PostDistributionDAO($dbConn))->setDistributionResult($d["uid"], addSlashes($errMsg), FLAG_ERRORTO_ERROR);

            //commit database updates
            $dbConn->dbinsQuery("commit;");

            //commit message out of SQS
            $commitResult = $message->commitMessage();
            if ($commitResult->isError()) {
                echo "error committing sqs message: {$commitResult->getDescription()}\n";
                die();
            }

            BroadcastingUtils::sendAlertEmail("System Error: Permanent Email Failure", "$errMsg\n\n<br><br>" . print_r($d), "Y");
            continue;
        }

        //EMAIL OUTPUT TYPE : HANDLING
        //csv OR html (look for a txt file for plain text)
        if (strpos(basename($d["attachmentFile"]), '.html') === false) {

            $sendResult = BroadcastingUtils::sendEmailWithAttachment($d["subject"], $online . $d["body"], $d["plainBody"], array($d["destinationAddr"]), $d["attachmentFile"], [], true, $messageId);

        } else {

            //HTML SENDING.
            $s3FilePartsArr = parse_url(trim($d["attachmentFile"], "'"));
            if (isset($s3FilePartsArr['scheme']) && $s3FilePartsArr['scheme'] === "s3") {

                $bodyHtml = Storage::getObject($s3FilePartsArr['host'], $s3FilePartsArr['path']);
                $bodyPlain = Storage::getObject($s3FilePartsArr['host'], str_replace('.html', '.txt', $s3FilePartsArr['path']));

                if ($bodyHtml) {
                    $sendResult = BroadcastingUtils::sendEmailHTMLEmbedded($d["subject"], array($d["destinationAddr"]), $online . $bodyHtml->body, $bodyPlain->body, [], $messageId);
                } else {
                    echo "error: invalid/blank body html file:" . print_r($d, true);
                    $dResult = (new PostDistributionDAO($dbConn))->setDistributionResult($d["uid"], addSlashes("error: invalid/blank S3 body html file"), FLAG_ERRORTO_ERROR);
                    continue;
                }

            } else {
                $dResult = (new PostDistributionDAO($dbConn))->setDistributionResult($d["uid"], addSlashes("Could not find HTML/TXT files required from embedded HTML email."), FLAG_ERRORTO_ERROR);
                continue;
            }

        }

    } else {
        $dResult = (new PostDistributionDAO($dbConn))->setDistributionResult($d["uid"], addSlashes("Uncatered for email type for uid {$d["uid"]}. Please contact Kwelanga Solutions Support"), FLAG_ERRORTO_ERROR);
        $commitResult = $message->commitMessage();  //commit this bad message
        continue;
    }

    if ($sendResult->type != FLAG_ERRORTO_SUCCESS) {

        echo "error sending: \t{$sendResult->description}\n";

        $dResult = (new PostDistributionDAO($dbConn))->setDistributionResult($d["uid"], addSlashes($sendResult->description), FLAG_STATUS_ERROR);
        if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
            echo "Error occurred setting Distribution Result.";
            BroadcastingUtils::sendAlertEmail("System Error", "Error occurred setting Distribution Result.<BR>UID: " . $d["uid"], "Y");
            return;
        }

        //lets re-publish this message with an error count
        if ($d["error_cnt"] < $maxErrorCount) {

            $metaArr = $message->getSmartEvent()->getMetaArr();
            $metaArr['error_cnt'] = $d["error_cnt"] + 1;

            $msg = (new SmartEventTO($message->getSmartEvent()->getType()))
                ->setDataUid($message->getSmartEvent()->getDataUid())
                ->setMetaArr($metaArr);

            echo "re-publishing with error count: {$metaArr['error_cnt']}\n";

            $result = SmartQueue::Publish($emailQueueName, $msg);
        } else {

            // only send error message to RT if it is not a once-off error
            BroadcastingUtils::sendAlertEmail("System Error", "Could not send EMAIL to User {$d["destinationUserUId"]}/{$d["destinationAddr"]}.<BR>UID: " . $d["uid"] . "<BR>ERROR CNT:" . ($d["error_cnt"] + 1) . "<BR>", "N");

            echo "error count exceeded ({$d["error_cnt"]}) halting retries\n";
        }

    } else {

        echo "emailing...\tsent!\n";

        $dResult = (new PostDistributionDAO($dbConn))->setDistributionResult($d["uid"], "", FLAG_STATUS_CLOSED);
        if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
            BroadcastingUtils::sendAlertEmail("System Error", "Error occurred setting Distribution Result.<BR>UID: " . $d["uid"], "Y");
        }
    }

    echo "event...\t";

    //commit database updates
    $dbConn->dbinsQuery("commit;");

    //commit message out of SQS
    $commitResult = $message->commitMessage();
    if ($commitResult->isError()) {
        echo "error committing sqs message: {$commitResult->getDescription()}\n";
        die();
    }

    echo "committed!\n";
}

echo str_repeat("-", 45) . "\n";
echo "end: \t" . CommonUtils::getGMTime(0) . "\n";
echo "time: \t" . round(microtime(true) - $statST, 4) . "s\n";
echo "[***EOS***]";
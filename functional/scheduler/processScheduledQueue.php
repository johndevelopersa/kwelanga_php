<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'DAO/SchedulerDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ReportDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostSchedulerDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingSchedulerJobTO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostDistributionDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingDistributionTO.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PrincipalDAO.php');
include_once($ROOT . $PHPFOLDER . 'libs/xmlClass.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/storage/Storage.php');
include_once($ROOT . $PHPFOLDER . 'libs/smartqueue/SmartQueue.php');

//TODO: once on a job server we can remove this!
set_time_limit(15 * 60);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$statST = microtime(true);
$maxMessages = $_GET['max_messages'] ?? 1;   //start low
$maxErrorCount = 30;
$waitTimeoutSec = 15;
$visibilityTimeoutSec = 300;    //all messages need to be processed by this timeout.

echo "start: \t" . CommonUtils::getGMTime(0) . "\n";
echo "long-polling for {$waitTimeoutSec} seconds for a maximum of {$maxMessages} messages\n";

$result = SmartQueue::Receive(QueueName::SchedulerJob, $maxMessages, $visibilityTimeoutSec, $waitTimeoutSec);
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
$queueStats = SmartQueue::getQueue(QueueName::SchedulerJob);

echo "total in queue:\t" . ($queueStats['ApproximateNumberOfMessages'] ?? '??') . "\n";

// only connect to db when there "are" actual messages....
new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}


$postDistributionDAO = new PostDistributionDAO($dbConn);
$adminDAO = new AdministrationDAO($dbConn);
$reportDAO = new ReportDAO($dbConn);
$postSchedulerDAO = new PostSchedulerDAO($dbConn);

// process each schedule
foreach ($result->getMessages() as $message) :

    $sentDateTime = $message->getSentTimestamp()
        ->setTimezone(new DateTimeZone('Africa/Johannesburg'))
        ->format(DATE_RFC3339);

    $ageSeconds = (new DateTime)->getTimestamp() - $message->getSentTimestamp()->getTimestamp();

    $s = $message->getSmartEvent()->getMetaArr();
    if (!isset($s["uid"]) || !isset($s["report_name"]) || !isset($s["principal_uid"])) {
        echo "critical error: SmartEvent invalid/blank: uid, report_name or principal_uid\n";
        echo print_r($s, true);
        continue;
    }

    $schedulerJobUId = $message->getSmartEvent()->getTypeUid();
    if (!$schedulerJobUId > 0) {
        echo "critical error: SmartEvent invalid/blank typeUid->schedulerJobUId: $schedulerJobUId\n";
        continue;
    }

    echo str_repeat("-", 45) . "\n";
    echo "schedulerID: \t{$s["uid"]}\n";
    echo "principalUID: \t{$s["principal_uid"]}\n";
    echo "historyID: \t$schedulerJobUId\n";
    echo "report: \t{$s["report_name"]}\n";
    echo "queueTime: \t{$sentDateTime} ({$ageSeconds}s ago)\n";

    //fetch the history and confirm it is queued
    $jobArr = (new SchedulerDAO($dbConn))->getSchedulerJobItem($schedulerJobUId);
    if(!isset($jobArr['run_result']) || $jobArr['run_result'] != FLAG_STATUS_QUEUED) {
        echo "critical error: job not in QUEUED status\n";
        echo print_r($jobArr, true);

        //remove message from queue
        $commitResult = $message->commitMessage();
        if ($commitResult->isError()) {
            echo "error committing sqs message: {$commitResult->getDescription()}\n";
            echo print_r($commitResult, true);
            exit;
        }

        continue;
    }

    // convert params to array
    $outputType = $s['output_type'];
    $paramArr = [];
    $emailList = "";

    if ($s["parameter_list"] != '') {
        $plArr = explode("&", $s["parameter_list"]);
        foreach ($plArr as $param) {
            list($name, $value) = explode("=", $param);
            $paramArr[$name] = $value;
            $emailList .= "\n" . $name . "=" . GUICommonUtils::translateDateRangeValue($value);
        }
    }

    // set up session var(s) which may be used inside the sql
    // don't do this anymore as it sometimes causes sql's to hand on the "sending data" phase $dbConn->dbQuery("SET @SCHEDULERUID:={$s["uid"]}");
    $GLOBALS["SCHEDULERUID"] = $s["uid"];
    $GLOBALS["SCRIPTORIGIN"] = "SCHEDULER"; // might not be needed, but couldnt fully understand how downloadBase/parameterBase was called as it had scheduler checks inside
    $GLOBALS["SE_RUNONCE_TYPE"] = "S" . $s["uid"];
    $GLOBALS["SE_RUNONCE_TYPE_UID"] = $s["object_id"];

    echo "Generating report...\n";
    $resultTO = $reportDAO->reportSQL_getReportSQL($s["object_id"], $s["user_uid"], $s["principal_uid"], $s["principal_code"], $paramArr);
    if ($resultTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "<BR>" . $resultTO->description, "Y");
        $postSchedulerDAO->setScheduleJobResult($schedulerJobUId, FLAG_ERRORTO_ERROR, $resultTO->description);
        continue;
    }

    $reportSQLTO = $resultTO->object;
    $sql = $resultTO->object->sql;
    $fileName = $resultTO->object->fileName;
    $xmlSchema = $resultTO->object->xmlSchema;
    $Cformater = (!empty($resultTO->object->columnFormat)) ? $resultTO->object->columnFormat : false;

    // returns only the result set.
    $runOnceParams = [
        "runOnceFieldName" => $resultTO->object->runOnceFieldName,
        "type" => $GLOBALS["SE_RUNONCE_TYPE"],
        "typeUId" => $GLOBALS["SE_RUNONCE_TYPE_UID"],
    ];

    // this not may also do an update (run once code) which we will need to rollback if error so commit now !
    // There must NOT be any other rollback in this script besides the one directly after this call due to its rollback
    $dbConn->dbinsQuery("commit");

    echo "Running report...\n";
    $resultTO = $reportDAO->reportSQL_runReportSQL($sql, $resultTO->object->database, $runOnceParams, $hiddenColList = $resultTO->object->hiddenColList);
    if ($resultTO->type != FLAG_ERRORTO_SUCCESS) {
        // rollback only any run-once updates to smart event. There is no other rollback in this script, so ok to rollback here and commit earlier
        $dbConn->dbinsQuery("rollback");

        BroadcastingUtils::sendAlertEmail("System Error: Scheduled Report ({$s["uid"]})", "<BR>" . $resultTO->description, "Y");
        $postSchedulerDAO->setScheduleJobResult($schedulerJobUId, FLAG_ERRORTO_ERROR, $resultTO->description);

        //commit the job result
        $dbConn->dbinsQuery("commit");

        //commit message out of SQS
        $commitResult = $message->commitMessage();
        if ($commitResult->isError()) {
            echo "error committing sqs message: {$commitResult->getDescription()}\n";
            echo print_r($commitResult, true);
            exit;
        }

        echo "committed!\n";

        continue;
    }

    // Add Control Break Total Lines if necessary
    echo "Adding totals...\n";
    $rTO = $reportDAO->reportSQL_addBreakTotals($reportSQLTO, $resultTO->object->data);
    if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        $dbConn->dbinsQuery("rollback");
        BroadcastingUtils::sendAlertEmail("System Error", "<BR>" . $rTO->description, "Y");
        $postSchedulerDAO->setScheduleJobResult($schedulerJobUId, FLAG_ERRORTO_ERROR, $rTO->description);
    }
    unset($resultTO->object->data);
    $resultTO->object->data = $rTO->object;

    echo "Creating output file...\n";
    $reportOutputDefault = '';
    $reportOutputSecondary = '';
    $mfU = $adminDAO->getUserItem($s["user_uid"]);
    $userName = $mfU[0]["full_name"] ?? "SYSTEM";
    if ($s['principal_uid'] != '') {
        $pdA = (new PrincipalDAO($dbConn))->getPrincipalItem($s['principal_uid']);
        $principalName = $pdA[0]['principal_name'];
    } else {
        $principalName = 'SYSTEM REPORT';
    }

    $systemLogo = 'images/kwelanga_colour_logo_70.png';
    $logoHTML = '<div align="right"><img src="' . HOST_SURESERVER_AS_USER . $PHPFOLDER . $systemLogo . '" alt="" ></div><hr><br><br>';
    $bodyHeader = $logoHTML . addSlashes("Dear User,<br><br>\n\nPlease find the <B>{$s["report_name"]}</B> that was scheduled by {$userName} for you to receive.<br>\n\nRegards,<br>\nKwelanga Online Solutions<br><br>\n\n");
    $bodyFooter = addSlashes("Parameters specified:<br>\n====================<br>\n<small>{$emailList}</small><br><br>\n\nPlease do not reply to this message. This is an automated emailbox and contents are not checked for communications.");

    //FORMAT RESULT SET DETERMINED BY OUTPUT TYPE
    if ($outputType == SCD_OT_CSV) {

        if ($resultTO->object->data == "" || !count($resultTO->object->data) > 0) {
            $reportOutputDefault = "No Rows Found.";
        } else {
            $reportOutput = $reportDAO->reportSQL_arrayToCSV($resultTO->object->data, TRUE);
            $reportOutputDefault = $reportOutput;
        }

    } else if ($outputType == SCD_OT_XML) {

        if ($resultTO->object->data == "" || !count($resultTO->object->data) > 0) {
            $reportOutputDefault = "No Rows Found.";
        } else {

            //WRITE XML
            $xmlResult = new arrayToXMLschema($xmlSchema, $resultTO->object->data);
            if ($xmlResult->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                $reportOutputDefault = "No Rows Found." . $xmlResult->errorTO->description;
            } else {
                $reportOutputDefault = $xmlResult->resultXML;
            }
        }

    } else {

        if ($resultTO->object->data == "" || !count($resultTO->object->data) > 0) {

            $reportOutputDefault = str_replace("\n", '<br>', $bodyHeader) . "No Rows Found.<br><br>" . str_replace("\n", '<br>', $bodyFooter);
            $reportOutputSecondary = $bodyHeader . "No Rows Found." . "\n\n" . $bodyFooter;

        } else {

            //WRITE HTML
            $reportOutputDefault = str_replace("\n", '<br>', $bodyHeader) .
                $reportDAO->reportSQL_arrayToHTML($resultTO->object->data, true, $Cformater) .
                "<br><br>" . str_replace("\n", '<br>', $bodyFooter);

            //WRITE PLAIN TEXT
            $reportOutputSecondary = $bodyHeader .
                $reportDAO->reportSQL_arrayToPLAINTEXT($resultTO->object->data) .
                "\n\n" . $bodyFooter;
        }
    }

    //replace spaces for URL friendly link - for future upgrades.
    //make name unique as same information can occur within same exec.
    //substr last 4 char as based on microtime - seconds not safe enough.
    $fileName = str_replace(" ", "_", $fileName) . '.' . CommonUtils::getGMTimeCompressed(0) . "." . $s["user_uid"] . "." . $s["principal_uid"] . "." . substr(uniqid(), 9, 4);
    $bkupFolder = FILE_ARCHIVE_REPORTS_PATH . date("Y") . "/" . date("m") . "/" . date("d") . "/";
    $reportExt = SchedulerDAO::getReportExtensionFromType($outputType);
    $attachFile = $bkupFolder . $fileName . "." . $reportExt;
    $attachFileSecondary = $bkupFolder . $fileName . ".txt";    //plain text emails

    echo "Uploading report to S3...\n";
    $s3Result = Storage::putObject(S3_BUCKET_NAME, $attachFile, $reportOutputDefault);
    if (!$s3Result) {
        echo "storage error: " . $s3Result . "\n";
        $postSchedulerDAO->setScheduleJobResult($schedulerJobUId, FLAG_ERRORTO_ERROR, "Error uploading S3 files: " . $s3Result);
        continue;
    }

    //secondary plain text email file!
    if ($outputType === SCD_OT_HTML) {
        $s3Result = Storage::putObject(S3_BUCKET_NAME, $attachFileSecondary, $reportOutputSecondary);
        if (!$s3Result) {
            echo "storage error: " . $s3Result . "\n";
            $postSchedulerDAO->setScheduleJobResult($schedulerJobUId, FLAG_ERRORTO_ERROR, "Error uploading S3 files: " . $s3Result);
            continue;
        }
    }

    $postingSchedulerJobTO = new PostingSchedulerJobTO;
    $postingSchedulerJobTO->DMLType = "UPDATE";
    $postingSchedulerJobTO->UId = $schedulerJobUId;
    $postingSchedulerJobTO->runResult = FLAG_ERRORTO_SUCCESS;
    $postingSchedulerJobTO->runDate = CommonUtils::getGMTime();
    $postingSchedulerJobTO->attachmentFile = $attachFile;
    $resultTO = $postSchedulerDAO->postScheduleJob($postingSchedulerJobTO);
    if ($resultTO->isError()) {
        echo "Error postScheduleJob: {$resultTO->getDescription()}\n";
        echo print_r($resultTO, true);
        continue;
    }

    //proceed only when email destination
    if ($s["destination_type"] != SCD_DT_EMAIL) {
        continue;
    }

    // process each output type for each recipient. for time being only cater for emails from scheduler
    $distributionSourceIdentifier = strtolower(trim(CommonUtils::getGUID(), "{}")); // linked to created distributions for retrieval on query screen
    $now = CommonUtils::getGMTime();
    $recipientList = array_filter(explode(",", $s["alt_recipient_list"]));

    if ($s["send_to_self"] == "Y") {
        if(!isset($mfU) || !isset($mfU[0]["user_email"])){
            $mfU = $adminDAO->getUserItem($s["user_uid"]);
        }
        if (isset($mfU[0]["user_email"])) {
            $recipientList[] = $mfU[0]["user_email"];
        }
    }

    if (!count($recipientList)) {
        $postSchedulerDAO->setScheduleJobResult($schedulerJobUId, FLAG_ERRORTO_ERROR, "No recipients found for distribution type email");
        continue;
    }

    // validate each email
    foreach ($recipientList as $r) {
        if (!preg_match(GUI_PHP_EMAIL_REGEX, $r)) {
            $postSchedulerDAO->setScheduleJobResult($schedulerJobUId, FLAG_ERRORTO_ERROR, "Incorrect email address format:" . $r);
            continue 2; // don't send to any of users if any single one has a problem
        }
    }

    foreach ($recipientList as $r) {

        // queue for distribution
        $postingDistributionTO = new PostingDistributionTO;
        $postingDistributionTO->DMLType = "INSERT";
        $postingDistributionTO->deliveryType = BT_EMAIL;
        $postingDistributionTO->sourceIdentifier = $distributionSourceIdentifier;
        $postingDistributionTO->destinationAddr = $r;
        $postingDistributionTO->subject = addSlashes($s["report_name"] . " for {$principalName} is attached ({$now} GMT) - Scheduled ");
        $postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, $postingSchedulerJobTO->attachmentFile);
        $postingDistributionTO->body = ($outputType == SCD_OT_CSV || $outputType == SCD_OT_XML) ? ($bodyHeader . $bodyFooter) : ('');

        $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);

        if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
            BroadcastingUtils::sendAlertEmail("System Error", "Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$schedulerJobUId}.", "Y");
            $postSchedulerDAO->setScheduleJobResult($schedulerJobUId, FLAG_ERRORTO_ERROR, addSlashes($dResult->description));
        }

        // reset the distribution uid back - it is important to update it regardless of successfully queueing distribution as you don't want it to point to old entry
        $sjdResult = $postSchedulerDAO->setScheduleJobDistribution($schedulerJobUId, $distributionSourceIdentifier);
        if ($sjdResult->type != FLAG_ERRORTO_SUCCESS) {
            BroadcastingUtils::sendAlertEmail("System Error", "Could not set the distribution id pass back.", "Y");
        }
    }
    echo "Generated: " . preg_replace("/error/i", "@error", $fileName) . " (EMAIL)\n";

    $dbConn->dbinsQuery("commit");

    //commit message out of SQS
    $commitResult = $message->commitMessage();
    if ($commitResult->isError()) {
        echo "error committing sqs message: {$commitResult->getDescription()}\n";
        echo print_r($commitResult, true);
        exit;
    }

    echo "committed!\n";

endforeach;


echo str_repeat("-", 45) . "\n";
echo "end: \t" . CommonUtils::getGMTime(0) . "\n";
echo "time: \t" . round(microtime(true) - $statST, 4) . "s\n";
echo "[***EOS***]";
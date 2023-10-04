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
include_once $ROOT . $PHPFOLDER . 'libs/storage/Storage.php';

//setup S3 storage class.
new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);

set_time_limit(60 * 45); // the 3am slot that runs 5 reports per principal (ML,RP,RI,RU,RO) may need this much time in future
error_reporting(-1);
ini_set('display_errors', 1);

echo "START: " . CommonUtils::getGMTime(0) . "<BR>";

// calling program may already have set this in JobExecution
if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}

$postingSchedulerJobTO = new PostingSchedulerJobTO;
$postDistributionDAO = new PostDistributionDAO($dbConn);
$adminDAO = new AdministrationDAO($dbConn);
$reportDAO = new ReportDAO($dbConn);
$postSchedulerDAO = new PostSchedulerDAO($dbConn);

/**
 * @param PostingSchedulerJobTO $postingSchedulerJobTO
 * @param string $attachFile
 * @param PostSchedulerDAO $postSchedulerDAO
 * @return ErrorTO
 */
function updateScheduledJob(PostingSchedulerJobTO $postingSchedulerJobTO, string $attachFile, PostSchedulerDAO $postSchedulerDAO): ErrorTO
{
    $postingSchedulerJobTO->DMLType = "UPDATE";
    $postingSchedulerJobTO->runResult = FLAG_ERRORTO_SUCCESS;
    $postingSchedulerJobTO->runDate = CommonUtils::getGMTime();
    $postingSchedulerJobTO->attachmentFile = $attachFile;
    $postingSchedulerJobTO->distributionStatus = FLAG_STATUS_QUEUED;
    return $postSchedulerDAO->postScheduleJob($postingSchedulerJobTO);
}

$mfS = (new SchedulerDAO($dbConn))->getActiveSchedulesDueNow();
foreach ($mfS as $s) :

    echo "Starting scheduledId: {$s["uid"]}\n";

    $postSchedulerDAO->setScheduleResult($s["uid"], CommonUtils::getGMTime());

    $postingSchedulerJobTO->DMLType = "INSERT";
    $postingSchedulerJobTO->schedulerUId = $s["uid"];
    $postingSchedulerJobTO->runDate = CommonUtils::getGMTime();
    $postingSchedulerJobTO->queuedDate = CommonUtils::getGMTime();
    $postingSchedulerJobTO->runResult = FLAG_STATUS_QUEUED;
    $postingSchedulerJobTO->attachmentFile = "";
    $resultTO = $postSchedulerDAO->postScheduleJob($postingSchedulerJobTO); // create the job entry for this run

    $postingSchedulerJobTO->UId = $resultTO->identifier;

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
    // dont do this anymore as it sometimes causes sql's to hand on the "sending data" phase $dbConn->dbQuery("SET @SCHEDULERUID:={$s["uid"]}");
    $GLOBALS["SCHEDULERUID"] = $s["uid"];
    $GLOBALS["SCRIPTORIGIN"] = "SCHEDULER"; // might not be needed, but couldnt fully understand how downloadBase/parameterBase was called as it had scheduler checks inside
    $GLOBALS["SE_RUNONCE_TYPE"] = "S" . $s["uid"];
    $GLOBALS["SE_RUNONCE_TYPE_UID"] = $s["object_id"];

    $resultTO = $reportDAO->reportSQL_getReportSQL($s["object_id"], $s["user_uid"], $s["principal_uid"], $s["principal_code"], $paramArr);
    if ($resultTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "<BR>" . $resultTO->description, "Y");
        $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, $resultTO->description);
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

    $resultTO = $reportDAO->reportSQL_runReportSQL($sql, $resultTO->object->database, $runOnceParams, $hiddenColList = $resultTO->object->hiddenColList);
    if ($resultTO->type != FLAG_ERRORTO_SUCCESS) {
        $dbConn->dbinsQuery("rollback"); // rollback only any run-once updates to smart event. There is no other rollback in this script, so ok to rollback here and commit earlier
        BroadcastingUtils::sendAlertEmail("System Error", "<BR>" . $resultTO->description, "Y");
        $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, $resultTO->description);
        continue;
    }

    // Add Control Break Total Lines if necessary
    $rTO = $reportDAO->reportSQL_addBreakTotals($reportSQLTO, $resultTO->object->data);
    if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        $dbConn->dbinsQuery("rollback");
        BroadcastingUtils::sendAlertEmail("System Error", "<BR>" . $rTO->description, "Y");
        $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, $rTO->description);
    }
    unset($resultTO->object->data);
    $resultTO->object->data = $rTO->object;

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

    //upload to storage
    $s3Result = Storage::putObject(S3_BUCKET_NAME, $attachFile, $reportOutputDefault);
    if (!$s3Result) {
        echo "storage error: " . $s3Result . "\n";
        $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, "Error uploading S3 files: " . $s3Result);
        continue;
    }

    //secondary plain text email file!
    if ($outputType === SCD_OT_HTML) {
        $s3Result = Storage::putObject(S3_BUCKET_NAME, $attachFileSecondary, $reportOutputSecondary);
        if (!$s3Result) {
            echo "storage error: " . $s3Result . "\n";
            $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, "Error uploading S3 files: " . $s3Result);
            continue;
        }
    }

    $resultTO = updateScheduledJob($postingSchedulerJobTO, $attachFile, $postSchedulerDAO);

    //proceed only when email destination
    if ($s["destination_type"] != SCD_DT_EMAIL) {
        continue;
    }

    // process each output type for each recipient. for time being only cater for emails from scheduler
    $distributionSourceIdentifier = CommonUtils::getRandomInteger(); // linked to created distributions for retrieval on query screen
    $now = CommonUtils::getGMTime();
    $recipientList = array_filter(explode(",", $s["alt_recipient_list"]));

    if ($s["send_to_self"] == "Y") {
        $mfU = $adminDAO->getUserItem($s["user_uid"]);
        if (isset($mfU[0]["user_email"])) {
            $recipientList[] = $mfU[0]["user_email"];
        }
    }

    if (!count($recipientList)) {
        $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, "No recipients found for distribution type email");
        continue;
    }

    // validate each email
    foreach ($recipientList as $r) {
        if (!preg_match(GUI_PHP_EMAIL_REGEX, $r)) {
            $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, "Incorrect email address format:" . $r);
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
            BroadcastingUtils::sendAlertEmail("System Error", "Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$postingSchedulerJobTO->UId}.", "Y");
            $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, addSlashes($dResult->description));
        }

        // reset the distribution uid back - it is important to update it regardless of successfully queueing distribution as you don't want it to point to old entry
        $sjdResult = $postSchedulerDAO->setScheduleJobDistribution($postingSchedulerJobTO->UId, $distributionSourceIdentifier);
        if ($sjdResult->type != FLAG_ERRORTO_SUCCESS) {
            BroadcastingUtils::sendAlertEmail("System Error", "Could not set the distribution id pass back.", "Y");
        }
    }
    echo "Generated: " . preg_replace("/error/i", "@error", $fileName) . " (EMAIL)\n";

    $dbConn->dbinsQuery("commit");

endforeach;

echo "\n[@>>>JOBS:" . count($mfS) . ";@]\n";  //stat line.
echo "END: " . CommonUtils::getGMTime(0) . "<BR>[***EOS***]";
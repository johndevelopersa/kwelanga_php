<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'DAO/SchedulerDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostSchedulerDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingSchedulerJobTO.php');
include_once($ROOT . $PHPFOLDER . 'libs/smartqueue/SmartQueue.php');

set_time_limit(5 * 60);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$scheduleType = "SCHEDULED_JOB";

echo "START: " . CommonUtils::getGMTime(0) . "<BR>";

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}

// get all active schedules that are due now
$mfS = (new SchedulerDAO($dbConn))->getActiveSchedulesDueNow();

// publish each schedule to SmartQueue
foreach ($mfS as $s) :

    echo "Schedule: {$s["uid"]} --- {$s["report_name"]}\n";

    //set the schedule result to now so that it is not picked up again!
    (new PostSchedulerDAO($dbConn))->setScheduleResult($s["uid"], CommonUtils::getGMTime());

    // add a queued job history entry for this run
    $postingSchedulerJobTO = new PostingSchedulerJobTO();
    $postingSchedulerJobTO->DMLType = "INSERT";
    $postingSchedulerJobTO->schedulerUId = $s["uid"];
    $postingSchedulerJobTO->runDate = CommonUtils::getGMTime();
    $postingSchedulerJobTO->queuedDate = CommonUtils::getGMTime();
    $postingSchedulerJobTO->runResult = FLAG_STATUS_QUEUED;
    $historyResultTO = (new PostSchedulerDAO($dbConn))->postScheduleJob($postingSchedulerJobTO); // create the job entry for this run

    echo "Created Job History: $historyResultTO->identifier\n";

    //Publish event to SQS...
    $msg = (new SmartEventTO($scheduleType))
        ->setTypeUid((int)$historyResultTO->identifier)    //pass the history id
        ->setMetaArr($s);

    echo "Publishing...";

    $publishResult = SmartQueue::Publish(QueueName::SchedulerJob, $msg);

    if ($publishResult->isError()) {
        echo "Error publishing to SmartQueue: {$publishResult->getDescription()}\n";
        echo print_r($publishResult, true);
        continue;
    }

    echo "Success!\n";
    $dbConn->dbinsQuery("commit");

endforeach;

echo "[@>>>JOBS:" . count($mfS) . ";@]\n";  //stat line.
echo "END: " . CommonUtils::getGMTime(0) . "\n";
echo "[***EOS***]";
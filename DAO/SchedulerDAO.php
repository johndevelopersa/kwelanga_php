<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');

// remember to pass the PHP BACKEND DATABASE as DBCONNECT and not LIVE
class SchedulerDAO
{
    private $dbConn;

    function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    // NB: also returns reports not allocated to any user at all
    public function getAllSchedulesForUser($userId, $principalId, $applySchedulerScope = true)
    {
        $sql = "select a.*, b.report_name, b.scheduler_scope
				from   scheduler a
					 	LEFT JOIN reports b ON a.object_id = b.uid
						 	LEFT JOIN (select distinct user_id, entity_uid, role_id from user_role) c
							 			on c.user_id=if(b.scheduler_scope='P',{$userId},a.user_uid) and
											(c.entity_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "' or c.entity_uid is null) and
											 b.role_id=c.role_id
				where  (user_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "' or user_uid is null" . (($applySchedulerScope) ? " or b.scheduler_scope='P'" : "") . ")
				and    ((b.role_id is null) or (b.role_id is not null and c.role_id is not null))
				and ifnull(a.principal_uid,'{$principalId}') = '{$principalId}'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    // NB: also returns reports not allocated to any user at all
    public function getScheduleItemForUser($userId, $principalId, $sId, $applySchedulerScope = true)
    {
        $sql = "select a.*, 
		      b.report_name, 
		      b.scheduler_scope
  				from   scheduler a
  				LEFT JOIN reports b ON a.object_id = b.uid
  				LEFT JOIN (select distinct user_id, entity_uid, role_id from user_role) c
          					 on c.user_id=if(b.scheduler_scope='P',{$userId},a.user_uid) and
          					 (c.entity_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "' or c.entity_uid is null) and
          											 b.role_id=c.role_id
  				where  (user_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "' or user_uid is null" . (($applySchedulerScope) ? " or b.scheduler_scope='P'" : "") . ")
  				and    ((b.role_id is null) or (b.role_id is not null and c.role_id is not null))
			    and    a.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $sId) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    public function getSchedulerJobItem($jobId)
    {
        $sql = "select
                    uid,
                    scheduler_uid,
                    run_date,
                    queued_date,
                    run_result,
                    run_msg,
                    attachment_file,
                    distribution_source_identifier
                from scheduler_job 
                where uid = '" . mysqli_real_escape_string($this->dbConn->connection, $jobId) . "'";

        $this->dbConn->dbQuery($sql);

        if ($this->dbConn->dbQueryResultRows > 0) {
            return mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC);
        }
        return [];
    }


    public function getScheduleItem($sId)
    {
        $sql = "select a.*, b.report_name
				from   scheduler a
					 	LEFT JOIN reports b ON a.object_id = b.uid
			    where a.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $sId) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    public function getAllJobsForUserSchedule($userId, $principalId, $sId, $days)
    {
        $sql = "select d.uid, d.scheduler_uid, d.run_date, d.queued_date, d.run_result, d.run_msg, d.attachment_file, d.distribution_source_identifier
				from   scheduler a
					 	LEFT JOIN reports b ON a.object_id = b.uid
						 	LEFT JOIN (select distinct user_id, entity_uid, role_id from user_role) c
							 			on c.user_id=a.user_uid and
											(c.entity_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "' or c.entity_uid is null) and
											 b.role_id=c.role_id,
						scheduler_job d
				where  (user_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "' or user_uid is null)
				and    ((b.role_id is null) or (b.role_id is not null and c.role_id is not null))
				and    d.scheduler_uid = a.uid
				and    a.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $sId) . "'
				and    run_date >= date_sub(CURDATE(),INTERVAL " . $days . " DAY)
				order  by run_date desc";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    public function getScheduleJobItemForUser($userId, $principalId, $sjId)
    {
        $sql = "select d.uid, d.scheduler_uid, d.run_date, d.queued_date, d.run_result, d.run_msg, d.attachment_file, d.distribution_source_identifier
				from   scheduler a
					 	LEFT JOIN reports b ON a.object_id = b.uid
						 	LEFT JOIN (select distinct user_id, entity_uid, role_id from user_role) c
							 			on c.user_id=a.user_uid and
											(c.entity_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "' or c.entity_uid is null) and
											 b.role_id=c.role_id,
						scheduler_job d
				where  (user_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "' or user_uid is null)
				and    ((b.role_id is null) or (b.role_id is not null and c.role_id is not null))
				and    d.scheduler_uid = a.uid
				and    d.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $sjId) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    public function getActiveSchedules()
    {
        $sql = "select a.*, b.report_name
				from   scheduler a
						LEFT JOIN reports b ON a.object_id = b.uid and a.job_type in ('" . SCD_JT_REPORT . "','" . SCD_JT_SYSTEM_REPORT . "')";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    public function getActiveSchedulesDueNow(): array
    {
        $scheduleReportsArr = $this->getActiveSchedules();
        $responseArr = [];
        foreach($scheduleReportsArr as $item){
            if($this->scheduledReportDueNow($item)) {
                $responseArr[] = $item;
            }
        }
        return $responseArr;
    }

    /**
     * @param $scheduledItem
     * @return bool
     */
    private function scheduledReportDueNow($scheduledItem): bool
    {
        if ($scheduledItem["run_day"] != "") $day = explode(",", $scheduledItem["run_day"]); else $day = array();
        sort($day);
        if ($scheduledItem["run_week"] != "") $week = explode(",", $scheduledItem["run_week"]); else $week = array();
        rsort($week);
        $time = explode(",", $scheduledItem["run_time"]);
        sort($time); // always assumed to be in 24hr format

        if ($scheduledItem["regenerate"] == "Y") {
            return true;
        }

        $lastMaxTime = 0;
        $nowJustDate = gmdate(GUI_PHP_DATE_FORMAT);
        $now = CommonUtils::getGMTime();
        $hrNow = gmdate("G"); // no leading zeros
        $mthNow = gmdate("m");
        $yrNow = gmdate("Y");
        $dayNow = gmdate("j"); // no leading zeros
        $weekDayNow = gmdate("w");

        // get the last schedule run date
        foreach ($time as $t) {

            //move time backwards to GMT timezone
            //IE: 6 AM (GMT+2) = 4 AM (GMT+0)
            $t -= 2;

            if (sizeof($day) > 0) {
                foreach ($day as $d) {
                    // assume all days are in past to get the last time it should have run.
                    // same month - cant do it same as for weeks because of differing days per month and leap year
                    if ($d <= $dayNow) {
                        if (checkdate(intval($mthNow), intval($d), intval($yrNow))) $calcTime = strtotime($yrNow . "-" . $mthNow . "-" . $d . " " . $t . ":00:00"); // check month has that many days
                        else $calcTime = 0;
                    } // previous month
                    else {
                        if (intval($mthNow) == 1) {
                            if (checkdate(intval("12"), intval($d), intval($yrNow))) $calcTime = strtotime((intval($yrNow) - 1) . "-12-" . $d . " " . $t . ":00:00"); // rollover
                            else $calcTime = 0;
                        } else {
                            if (checkdate((intval($mthNow) - 1), $d, $yrNow)) $calcTime = strtotime($yrNow . "-" . (intval($mthNow) - 1) . "-" . $d . " " . $t . ":00:00");
                            else $calcTime = 0;
                        }
                    }
                    if (($calcTime > $lastMaxTime) && ($calcTime <= strtotime($now))) $lastMaxTime = $calcTime; // remember it only if in past
                }
            } else if (sizeof($week) > 0) {
                foreach ($week as $w) {
                    // assume all weeks are in past to get the last time it should have run.
                    if (intval($w) <= intval($weekDayNow)) $calcTime = strtotime($nowJustDate . " " . $t . ":00:00") - (($weekDayNow - $w) * 60 * 60 * 24);
                    else $calcTime = strtotime($nowJustDate . " " . $t . ":00:00") - ((7 - ($w - $weekDayNow)) * 60 * 60 * 24);
                    if (($calcTime > $lastMaxTime) && ($calcTime <= strtotime($now))) $lastMaxTime = $calcTime; // remember it only if in past
                }
            }

        }

        if ($scheduledItem["last_run_date"] == "" && strtotime($scheduledItem["created_date"]) < $lastMaxTime) {
            return true;
        }

        if (strtotime($scheduledItem["last_run_date"]) < $lastMaxTime) {
            return true;
        }

        return false;
    }

    /**
     * @param $outputType
     * @return string
     */
    public static function getReportExtensionFromType($outputType): string{
        switch ($outputType) {
            case SCD_OT_CSV:
                return "csv";
            case SCD_OT_XML:
                return "xml";
            default:
                return "html";
        }
    }

}

<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');

class DistributionDAO
{
    private $dbConn;

    function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function getQueuedDistributions()
    {
        $sql = "select *
                    from   distribution
                    where  status IN ('" . FLAG_STATUS_QUEUED . "','" . FLAG_ERRORTO_ERROR . "')";

        return $this->dbConn->dbGetAll($sql);
    }


    public function getQueuedDistributionsByType($typeArr, $limit = false)
    {
        $sql = "select *
                from   distribution
                where  delivery_type IN (" . join(',', $typeArr) . ")
                and  status IN ('" . FLAG_STATUS_QUEUED . "','" . FLAG_ERRORTO_ERROR . "')
                " . (($limit !== false) ? (" LIMIT " . $limit . " ") : (''));

        return $this->dbConn->dbGetAll($sql);
    }

    public function getDistributions($sourceIdentifier)
    {
        if ($sourceIdentifier == "") return array();

        $sql = "select d.*, if(delivery_type=" . BT_EMAIL . ",if(u.uid is null,d.destination_addr,u.user_email),destination_addr) addr
                    from   distribution d
                                          left join users u on d.destination_user_uid = u.uid
                    where  d.source_identifier = '{$sourceIdentifier}'";

        return $this->dbConn->dbGetAll($sql);
    }


    public function getDistributionsForNDaysByEmail($destinationAddr, $days)
    {
        $sql = "select
                      `uid`, `run_date`, `queued_date`, `run_msg`, `attachment_file`, `status`, `subject`, `body`, `destination_addr`
                    from   distribution d
                    where status = '" . FLAG_STATUS_CLOSED . "'
                      AND DATE(queued_date) BETWEEN CURDATE() - INTERVAL " . $days . " DAY AND CURDATE()
                      AND destination_addr = '" . $destinationAddr . "' ORDER BY uid DESC";

        return $this->dbConn->dbGetAll($sql);
    }

    public function getDistributionItem($uid)
    {
        $sql = "select
                      *
                    from   distribution d
                    where uid = {$uid}";

        return $this->dbConn->dbGetAll($sql);
    }
}

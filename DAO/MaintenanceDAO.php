<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . "properties/Constants.php");
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');

class MaintenanceDAO
{
    private $dbConn;

    function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
        $this->errorTO = new ErrorTO;
    }

    // ****************************************************************************************************************************************************

    public function getAllStockRecords($principalList, $depotList, $interval, $statusList, $docTypeList)
    {

        if (mysqli_real_escape_string($this->dbConn->connection, $principalList) <> "") {
            $prinVar = "and dm.principal_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $principalList) . ")";
        } else {
            $prinVar = "";
        }
        if (mysqli_real_escape_string($this->dbConn->connection, $depotList) <> '' > 0) {
            $warehouseVar = "and dm.depot_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $depotList) . ")";
        } else {
            $warehouseVar = "";
        }

        $sql = "select dm.principal_uid, 
                    dm.depot_uid, 
                    dd.product_uid, 
                    sum(dd.ordered_qty) as 'Quantity',
                    s.stock_item
             from   document_master dm
             inner join document_detail dd on dm.uid = dd.document_master_uid
             inner join document_header dh on dm.uid = dh.document_master_uid
             left  join stock s on s.principal_id = dm.principal_uid 
                                and s.depot_id = dm.depot_uid 
                                and s.principal_product_uid = dd.product_uid
             inner join .depot d on d.uid = dm.depot_uid 
             where dm.processed_date > curdate() - interval " . mysqli_real_escape_string($this->dbConn->connection, $interval) . " day
             " . $prinVar . "
             " . $warehouseVar . "
             and   dm.document_type_uid in   (" . mysqli_real_escape_string($this->dbConn->connection, $docTypeList) . ")
             and   dh.document_status_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $statusList) . ")
             and   d.wms = 'Y'
             group by dm.principal_uid, dm.depot_uid, dd.product_uid;";


        $aSR = $this->dbConn->dbGetAll($sql);
        return $aSR;
    }


    public function clearExistingBalances($principalList, $depotList, $stockcol, $autoCommit = true)
    {
        $prinVar = "";
        if (!empty($principalList)) {
            $prinVar = "and s.principal_id in (" . mysqli_real_escape_string($this->dbConn->connection, $principalList) . ")";
        }

        $warehouseVar = "";
        if (mysqli_real_escape_string($this->dbConn->connection, $depotList) <> '' > 0) {
            $warehouseVar = "and s.depot_id in (" . mysqli_real_escape_string($this->dbConn->connection, $depotList) . ")";
        }

        $sql = "update stock s set s." . mysqli_real_escape_string($this->dbConn->connection, $stockcol) . " = 0 
             where 1 " .
            $prinVar . " " .
            $warehouseVar . " ;";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if($autoCommit) {
            $this->dbConn->dbQuery("commit");

            if ($this->errorTO->type == 'S') {
                return $this->errorTO->type;
            } else {
                return "F";
            }
        } else {
            return $this->errorTO;
        }

    }


    public function updateBalances($principal, $warehouse, $quantity, $stockUid, $stockcol, $autoCommit = true)
    {

        $sql = "update stock s set s." . mysqli_real_escape_string($this->dbConn->connection, $stockcol) . " = 0 - " . mysqli_real_escape_string($this->dbConn->connection, $quantity) . "
             where s.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . "
             and   s.depot_id     = " . mysqli_real_escape_string($this->dbConn->connection, $warehouse) . " 
             and   s.principal_product_uid  = " . mysqli_real_escape_string($this->dbConn->connection, $stockUid) . ";";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if($autoCommit) {
            $this->dbConn->dbQuery("commit");


            if ($this->errorTO->type == 'S') {
                return $this->errorTO->type;
            } else {
                return "F";
            }

        } else{
            return $this->errorTO;
        }
    }

// ****************************************************************************************************************************************************
    public function recalcalculateStockBalance()
    {

        // Recalcalculate stock Balance

        $sql = "update stock a set  a.closing = (a.opening + 
                            a.arrivals + 
                            a.returns_cancel + 
                            a.returns_nc + 
                            a.delivered + 
                            a.adjustment),

                            a.available = (a.opening  + 
                            a.arrivals + 
                            a.returns_cancel + 
                            a.returns_nc + 
                            a.delivered + 
                            a.adjustment +
                            a.allocations +
                            a.in_pick)
              where 1;";

        $this->errorTO = $this->dbConn->processPosting($sql, "");
        $this->dbConn->dbQuery("commit");

        if ($this->errorTO->type == 'S') {
            return $this->errorTO->type;
        } else {
            return $this->errorTO;
        }
    }

// ****************************************************************************************************************************************************
    public function recalcalculateStockBalancePrinDepot($prin, $wh)
    {

        // Recalcalculate stock Balance

        $sql = "update stock a set  a.closing = (a.opening + 
                            a.arrivals + 
                            a.returns_cancel + 
                            a.returns_nc + 
                            a.delivered + 
                            a.adjustment),

                            a.available = (a.opening  + 
                            a.arrivals + 
                            a.returns_cancel + 
                            a.returns_nc + 
                            a.delivered + 
                            a.adjustment +
                            a.allocations +
                            a.in_pick)
              WHERE a.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $prin) . "
              AND   a.depot_id = " . mysqli_real_escape_string($this->dbConn->connection, $wh) . ";";

        $this->errorTO = $this->dbConn->processPosting($sql, "");
        $this->dbConn->dbQuery("commit");

        if ($this->errorTO->type == 'S') {
            return $this->errorTO->type;
        } else {
            return "F";
        }
    }

// ****************************************************************************************************************************************************
    public function activeWarehousesPrincipal()
    {

        $sql = "select distinct(dm.depot_uid), d.name
              from   document_master dm
              inner join document_header dh on dm.uid = dh.document_master_uid
              inner join stock s on s.principal_id = dm.principal_uid and s.depot_id = dm.depot_uid 
              inner join depot d on d.uid = dm.depot_uid 
              inner join principal p on p.uid = dm.principal_uid
              where dm.processed_date > curdate() - interval 45 day
              and   dm.document_type_uid in (" . DT_ORDINV . "," . DT_DELIVERYNOTE . "," . DT_ORDINV_ZERO_PRICE . ")
              and   dh.document_status_uid in ( " . DST_INVOICED . "," . DST_DELIVERED_POD_OK . "," . DST_DIRTY_POD . "," . DST_ACCEPTED . ")
              and   d.wms = 'Y'
              order by d.name;";

        $aWP = $this->dbConn->dbGetAll($sql);
        return $aWP;
    }

// ****************************************************************************************************************************************************
    public function activePrincipalWarehouses()
    {

        $sql = "select distinct(dm.principal_uid), p.name
              from   document_master dm
              inner join document_header dh on dm.uid = dh.document_master_uid
              inner join stock s on s.principal_id = dm.principal_uid and s.depot_id = dm.depot_uid 
              inner join depot d on d.uid = dm.depot_uid 
              inner join principal p on p.uid = dm.principal_uid
              where dm.processed_date > curdate() - interval 45 day
              and   dm.document_type_uid in (" . DT_ORDINV . "," . DT_DELIVERYNOTE . "," . DT_ORDINV_ZERO_PRICE . ")
              and   dh.document_status_uid in ( " . DST_INVOICED . "," . DST_DELIVERED_POD_OK . "," . DST_DIRTY_POD . ")
              and   d.wms = 'Y'
              order by p.name;";

        $aPW = $this->dbConn->dbGetAll($sql);
        return $aPW;
    }

    // ****************************************************************************************************************************************************
    public function dailyExtractPrincipalList($userUId, $principalId)
    {

        // check for Staff user

        $sql = "SELECT *
             FROM .users u
             WHERE u.staff_user = 'Y'
             AND   u.deleted = 0
             AND   u.category = 'P'
             AND   u.uid = " . mysqli_real_escape_string($this->dbConn->connection, $userUId) . " ";

        $sU = $this->dbConn->dbGetAll($sql);

        if (count($sU) > 0) {
            $plist = '';
        } else {
            $plist = 'AND   js.principal_uid = ' . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ' ';
        }

        $psql = 'SELECT js.uid AS "js.Uid", 
                      p.uid  AS "principal_uid", 
                      p.name AS "principal",
                      js.type
                FROM       job_execution js
                LEFT JOIN  principal p ON p.uid = js.principal_uid
                WHERE js.name IN ("dailyExtracts", "weeklyExtracts")  
                ' . $plist . '
                ORDER BY p.name;';

        $aPW = $this->dbConn->dbGetAll($psql);
        return $aPW;
    }

    // ****************************************************************************************************************************************************
    public function getReQuePrincipalList($userUId, $principalId)
    {

        // check for Staff user

        $sql = "SELECT *
             FROM .users u
             WHERE u.staff_user = 'Y'
             AND   u.deleted = 0
             AND   u.category = 'P'
             AND   u.uid = " . mysqli_real_escape_string($this->dbConn->connection, $userUId) . " ";

        $sU = $this->dbConn->dbGetAll($sql);

        if (count($sU) > 0) {
            $plist = '';
        } else {
            $plist = 'AND   p.principal_uid = ' . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ' ';
        }

        $psql = "SELECT DISTINCT(upd.principal_id),
                               p.name AS 'principal'
               FROM .user_principal_depot upd
               INNER JOIN .principal p ON upd.principal_id = p.uid AND p.`status` = 'A'
               WHERE upd.user_id = " . mysqli_real_escape_string($this->dbConn->connection, $userUId) . "  
               " . $plist . " 
               ORDER BY p.name;";

        $aPW = $this->dbConn->dbGetAll($psql);
        return $aPW;
    }

    // ****************************************************************************************************************************************************
    public function getReQueWarehouseList($userUId, $principalId)
    {

        $dsql = "SELECT DISTINCT(d.uid) AS 'warehouse_uid',
                       d.name AS 'warehouse'
                FROM  user_principal_depot upd
                INNER JOIN .depot d ON upd.depot_id = d.uid
                WHERE upd.user_id = " . mysqli_real_escape_string($this->dbConn->connection, $userUId) . "
                AND   upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ";";


        $depl = $this->dbConn->dbGetAll($dsql);
        return $depl;

    }

    // ****************************************************************************************************************************************************
    public function updateJobExceutiontime($principalId)
    {

        $sql = "update job_execution js SET js.last_run = concat(SUBSTR(js.last_run, 1,11),SUBTIME(js.time_to_run, '00:15:00'))
               WHERE js.name =  'dailyExtracts'
               AND   js.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . " ";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == 'S') {
            $this->dbConn->dbQuery("commit");
            return $this->errorTO->type;
        } else {
            return "F";
        }
    }

    // ****************************************************************************************************************************************************
    public function checkHeaderTotals($principalId, $fdate, $docNo)
    {

        // Get documents to check
        if (trim($docNo) == '') {
            $oneDoc = "AND    dm.processed_date > '" . mysqli_real_escape_string($this->dbConn->connection, $fdate) . "' ";
        } else {
            $oneDoc = "AND dm.document_number like '%" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . "%' ";
        }

        $sql = "SELECT distinct(dm.uid) , dh.document_status_uid
               FROM   document_master dm, document_header dh, .document_detail dd 
               WHERE  dm.uid = dh.document_master_uid 
               AND    dm.uid   = dd.document_master_uid 
               AND    dm.principal_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ")
               " . $oneDoc . ";";

        $docs = $this->dbConn->dbGetAll($sql);

        $success = $failed = $changed = 0;

        if (count($docs) > 0) {
            $docstatus = array(DST_UNACCEPTED, DST_ACCEPTED, DST_INPICK);

            foreach ($docs as $doc1) {

                if (in_array($doc1['document_status_uid'], $docstatus)) {
                    $usql = "update document_header dh set dh.cases           =  (select sum(dd.ordered_qty)
                                                                              from .document_detail dd
                                                                              where dd.document_master_uid = " . $doc1['uid'] . "),
                                                dh.exclusive_total        =  (select sum(dd.extended_price)
                                                                              from .document_detail dd
                                                                              where dd.document_master_uid = " . $doc1['uid'] . "),
                                                dh.vat_total              =  (select sum(dd.vat_amount)
                                                                              from .document_detail dd
                                                                              where dd.document_master_uid = " . $doc1['uid'] . "),
                                                dh.invoice_total          =  (select sum(dd.total)
                                                                              from .document_detail dd
                                                                              where dd.document_master_uid = " . $doc1['uid'] . ")
                         where dh.document_master_uid = " . $doc1['uid'] . " ;";
                } else {
                    $usql = "update document_header dh set dh.cases           =  (select sum(dd.document_qty)
                                                                              from .document_detail dd
                                                                              where dd.document_master_uid = " . $doc1['uid'] . "),
                                                dh.exclusive_total        =  (select sum(dd.extended_price)
                                                                              from .document_detail dd
                                                                              where dd.document_master_uid = " . $doc1['uid'] . "),
                                                dh.vat_total              =  (select sum(dd.vat_amount)
                                                                              from .document_detail dd
                                                                              where dd.document_master_uid = " . $doc1['uid'] . "),
                                                dh.invoice_total          =  (select sum(dd.total)
                                                                              from .document_detail dd
                                                                              where dd.document_master_uid = " . $doc1['uid'] . ")
                          where dh.document_master_uid = " . $doc1['uid'] . " ;";
                }
                $this->errorTO = $this->dbConn->processPosting($usql, "");

                if ($this->errorTO->type == 'S') {
                    $this->dbConn->dbQuery("commit");
                    $success++;
                    $changed = $changed + $this->errorTO->object['changed'];
                } else {
                    $failed++;
                    return $this->errorTO;
                }
            }
        } else {
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "No Documents found to Update";
            return $this->errorTO;
        }

        if ($success == 0 && $failed == 0) {
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "No Documents to Update - " . $success . " ";
            return $this->errorTO;
        } elseif ($success > 0 && $failed == 0) {
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "Number of Documents Updated - " . $success . " Changed - " . $changed . "  ";
            return $this->errorTO;
        } else {
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "Should never get here - Big Problem ";
            return $this->errorTO;
        }
    }

    // ****************************************************************************************************************************************************
    public function detailRecordAdjust($principalId, $docNo)
    {

        $sql = "SELECT dm.uid AS 'docUid',
                        dm.document_number,
                        dh.document_status_uid 
                 FROM .document_master dm, .document_header dh
                 WHERE dm.uid = dh.document_master_uid
                 AND   dm.principal_uid   = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                 AND   dm.document_number = " . mysqli_real_escape_string($this->dbConn->connection, $docNo) . ";";

        $docs = $this->dbConn->dbGetAll($sql);

        if (count($docs) == 1) {
            $docstatus = array(DST_UNACCEPTED, DST_ACCEPTED, DST_INPICK);
            foreach ($docs as $doc1) {
                if (in_array($doc1['document_status_uid'], $docstatus)) {
                    $docQty = "dd.ordered_qty";
                } else
                    $docQty = "dd.document_qty";
            }
            $qsql = "update document_detail dd set dd.extended_price = " . $docQty . " * dd.net_price,
                             dd.vat_amount     =  " . $docQty . " * dd.net_price  * (dd.vat_rate /100),
                             dd.total          = (" . $docQty . " * dd.net_price) + (" . $docQty . " * dd.net_price * (dd.vat_rate /100))
                   where dd.document_master_uid IN (" . $doc1['docUid'] . ");";

            $this->errorTO = $this->dbConn->processPosting($qsql, "");

            if ($this->errorTO->type == 'S') {
                $this->dbConn->dbQuery("commit");
                return $this->errorTO;
            } else {
                $this->errorTO->description = "Big Problem ";
                return $this->errorTO;
            }
        } else {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Document Not Found or More than one Document Found - Try again";
            return $this->errorTO;
        }
    }

    // ****************************************************************************************************************************************************
    public function omniExtractDepotList($userUId, $principalId)
    {

        $sql = "SELECT upd.depot_id AS 'depot_uid', 
                     d.name       AS 'depot_name'
              FROM .user_principal_depot upd 
              LEFT JOIN .depot d ON upd.depot_id = d.uid
              WHERE upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
              AND   upd.user_id      = " . mysqli_real_escape_string($this->dbConn->connection, $userUId) . ";";

        $aDL = $this->dbConn->dbGetAll($sql);
        return $aDL;

    }

    // ****************************************************************************************************************************************************
    public function omniExtractChainList($principalId)
    {

        $sql = "SELECT pcm.uid         AS 'chain_uid',
                     pcm.description AS 'chain'
              FROM .principal_chain_master pcm
              WHERE pcm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
              AND   pcm.alternate_chain = 1 ;";

        $aCH = $this->dbConn->dbGetAll($sql);
        return $aCH;

    }

    // ****************************************************************************************************************************************************
    public function getJobExecutionEntry($principalId, $depotId, $chainId)
    {

        $sql = "SELECT *
              FROM .job_execution a
              WHERE a.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
              AND   substr(a.page_params,1,3) = " . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "
              AND   substr(a.page_params,5,4) = " . mysqli_real_escape_string($this->dbConn->connection, $chainId) . "
              AND   a.name = 'OmniImports' ;";

        $jeEE = $this->dbConn->dbGetAll($sql);
        return $jeEE;

    }

    // ****************************************************************************************************************************************************
    public function getDailyExecutionEntry($principalId, $eType = NULL)
    {

        if ($eType <> NULL) {
            $pType = "AND   je.type     = '" . mysqli_real_escape_string($this->dbConn->connection, $eType) . "' ";
        } else {
            $pType = '';
        }
        $sql = "SELECT DISTINCT (je.script_name), je.principal_uid ,je.name
              FROM .job_execution je
              WHERE je .principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
              AND   je.name = 'dailyExtracts'
              " . $pType . " ;";

        $jeEE = $this->dbConn->dbGetAll($sql);
        return $jeEE;
    }

    // ****************************************************************************************************************************************************
    public function getScriptRunPassword($userId)
    {

        $sql = "SELECT u.uid, u.taskman_account
               FROM .users u
               WHERE u.uid = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . " ;";

        $rPW = $this->dbConn->dbGetAll($sql);
        return $rPW;
    }

    // ****************************************************************************************************************************************************
    public function runSelectScript($postquerySelect)
    {

        $sql = mysqli_real_escape_string($this->dbConn->connection, $postquerySelect) . " ;";
        $sql1 = preg_replace('#(\\\r|\\\n)#', ' ', $sql);
        $sql2 = trim(preg_replace('#(\\\)#', '', $sql1));

        echo "<br>";
        echo $sql2;
        echo "<br>";

        $result = $this->dbConn->dbGetAll($sql2);

        print_r($result);

        if (substr($result, 0, 14) == 'Error in query') {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = 'Error in query rr';
            $this->errorTO->object = $sql2;
            return $this->errorTO;
        } else {
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = 'Query Successful';
            $this->errorTO->object = $result;
            return $this->errorTO;
        }
    }

    // ****************************************************************************************************************************************************
    public function runInsertScript($posttableName, $ufld1, $ufld2, $ufld3, $ufld4, $ufld5, $ufld6, $ufld7, $ufld8, $ufld9, $ufld10,
                                    $tControl1, $tControl2, $tControl3, $octlVar1, $octlVar2, $octlVar3,
                                    $oVar1, $oVar2, $oVar3, $oVar4, $oVar5, $oVar6, $oVar7, $oVar8, $oVar9, $oVar10)
    {
        $stableName = mysqli_real_escape_string($this->dbConn->connection, $posttableName);

        if (trim(mysqli_real_escape_string($this->dbConn->connection, $octlVar1)) <> '') {
            $soctlVar1 = "'" . mysqli_real_escape_string($this->dbConn->connection, $octlVar1) . "'";
        } else {
            $soctlVar1 = '';
        }
        if (trim(mysqli_real_escape_string($this->dbConn->connection, $octlVar2)) <> '') {
            $soctlVar2 = ",'" . mysqli_real_escape_string($this->dbConn->connection, $octlVar2) . "'";
        } else {
            $soctlVar2 = '';
        }
        if (trim(mysqli_real_escape_string($this->dbConn->connection, $octlVar3)) <> '') {
            $soctlVar3 = ",'" . mysqli_real_escape_string($this->dbConn->connection, $octlVar3) . "'";
        } else {
            $soctlVar3 = '';
        }
        if (trim(mysqli_real_escape_string($this->dbConn->connection, $tControl1)) <> '') {
            $sControl1 = $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $tControl1);
        } else {
            $sControl1 = '';
        }
        if (trim(mysqli_real_escape_string($this->dbConn->connection, $tControl2)) <> '') {
            $sControl2 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $tControl2);
        } else {
            $sControl2 = '';
        }
        if (trim(mysqli_real_escape_string($this->dbConn->connection, $tControl3)) <> '') {
            $sControl3 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $tControl3);
        } else {
            $sControl3 = '';
        }

        $sFLD1 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $ufld1);
        $sVAR1 = ",'" . mysqli_real_escape_string($this->dbConn->connection, $oVar1) . "'";

        if (trim($ufld2) <> '') {
            $sFLD2 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $ufld2);
            $sVAR2 = ",'" . mysqli_real_escape_string($this->dbConn->connection, $oVar2) . "'";
        } else {
            $sFLD2 = '';
            $sVAR2 = '';
        }

        if (trim($ufld3) <> '') {
            $sFLD3 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $ufld3);
            $sVAR3 = ",'" . mysqli_real_escape_string($this->dbConn->connection, $oVar3) . "'";
        } else {
            $sFLD3 = '';
            $sVAR3 = '';
        }
        if (trim($ufld4) <> '') {
            $sFLD4 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $ufld4);
            $sVAR4 = ",'" . mysqli_real_escape_string($this->dbConn->connection, $oVar4) . "'";
        } else {
            $sFLD4 = '';
            $sVAR4 = '';
        }
        if (trim($ufld5) <> '') {
            $sFLD5 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $ufld5);
            $sVAR5 = ",'" . mysqli_real_escape_string($this->dbConn->connection, $oVar5) . "'";
        } else {
            $sFLD5 = '';
            $sVAR5 = '';
        }
        if (trim($ufld6) <> '') {
            $sFLD6 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $ufld6);
            $sVAR6 = ",'" . mysqli_real_escape_string($this->dbConn->connection, $oVar6) . "'";
        } else {
            $sFLD6 = '';
            $sVAR6 = '';
        }
        if (trim($ufld7) <> '') {
            $sFLD7 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $ufld7);
            $sVAR7 = ",'" . mysqli_real_escape_string($this->dbConn->connection, $oVar7) . "'";
        } else {
            $sFLD7 = '';
            $sVAR7 = '';
        }
        if (trim($ufld8) <> '') {
            $sFLD8 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $ufld8);
            $sVAR8 = ",'" . mysqli_real_escape_string($this->dbConn->connection, $oVar8) . "'";
        } else {
            $sFLD8 = '';
            $sVAR8 = '';
        }
        if (trim($ufld9) <> '') {
            $sFLD9 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $ufld9);
            $sVAR9 = ",'" . mysqli_real_escape_string($this->dbConn->connection, $oVar9) . "'";
        } else {
            $sFLD9 = '';
            $sVAR9 = '';
        }
        if (trim($ufld10) <> '') {
            $sFLD10 = "," . $stableName . "." . mysqli_real_escape_string($this->dbConn->connection, $ufld10);
            $sVAR10 = "'," . mysqli_real_escape_string($this->dbConn->connection, $oVar10) . "'";
        } else {
            $sFLD10 = '';
            $sVAR10 = '';
        }

        $iSql = "INSERT INTO " . $stableName . " (" . $sControl1 . "
                                                 " . $sControl2 . "
                                                 " . $sControl3 . "
                                                 " . $sFLD1 . " 
                                                 " . $sFLD2 . " 
                                                 " . $sFLD3 . " 
                                                 " . $sFLD4 . " 
                                                 " . $sFLD5 . " 
                                                 " . $sFLD6 . " 
                                                 " . $sFLD7 . " 
                                                 " . $sFLD8 . "                                                 
                                                 " . $sFLD9 . " 
                                                 " . $sFLD10 . ")
                VALUES (" . $soctlVar1 . "
                        " . $soctlVar2 . "
                        " . $soctlVar3 . "
                        " . $sVAR1 . "
                        " . $sVAR2 . "
                        " . $sVAR3 . "
                        " . $sVAR4 . " 
                        " . $sVAR5 . "
                        " . $sVAR6 . "
                        " . $sVAR7 . "
                        " . $sVAR8 . "
                        " . $sVAR9 . "
                        " . $sVAR10 . ");";

        $this->errorTO = $this->dbConn->processPosting($iSql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {


            $this->dbConn->dbQuery("commit");
            return $this->errorTO;
        } else {
            echo "<br>";
            echo $iSql;
            echo "<br>";
            return "F";
        }

    }

    // ****************************************************************************************************************************************************
    public function runScreenValidationScript($postquerySelect, $tblName, $tControl1, $ctlVar1, $ufld1, $uVar1)
    {

        if (strtoupper(substr($postquerySelect, 0, 6)) <> "SELECT") {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Select Query incorrect - Try again";
            return $this->errorTO;

        }
        if (trim($tblName) == '') {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Table Name cannot be blank - Try again";
            return $this->errorTO;
        }

        if (trim($tControl1) == '' || trim($ctlVar1) == '') {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Table Control  and / or Value 1 cannot be empty - Try again";
            return $this->errorTO;
        }
        if (trim($ufld1) == '' || trim($uVar1) == '') {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "One Update field is required - Try again";
            return $this->errorTO;
        }

        $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        $this->errorTO->description = "Validation OK";
        return $this->errorTO;

    }

    // ****************************************************************************************************************************************************
    public function checkRecordExists($tblName, $tControl1, $tControl2, $tControl3, $ctlVar1, $ctlVar2, $ctlVar3)
    {

        if (mysqli_real_escape_string($this->dbConn->connection, $tControl2) <> '') {
            $ctVar2 = "AND t. " . mysqli_real_escape_string($this->dbConn->connection, $tControl2) . " = " . $ctlVar2 . " ";
        } else {
            $ctVar2 = '';
        }
        if (mysqli_real_escape_string($this->dbConn->connection, $tControl3) <> '') {
            $ctVar3 = "AND t. " . mysqli_real_escape_string($this->dbConn->connection, $tControl3) . " = " . $ctlVar3 . " ";
        } else {
            $ctVar3 = '';
        }

        $sql = "SELECT * 
               FROM " . mysqli_real_escape_string($this->dbConn->connection, $tblName) . " t 
               WHERE  t. " . mysqli_real_escape_string($this->dbConn->connection, $tControl1) . " = '" . $ctlVar1 . "' 
               '" . $ctVar2 . "'
               '" . $ctVar3 . "';";

        $rEx = $this->dbConn->dbGetAll($sql);
        return $rEx;

    }

    // ****************************************************************************************************************************************************
    public function runUpdateScript($tblName, $ufld1, $ufld2, $ufld3, $ufld4, $ufld5, $ufld6, $ufld7, $ufld8, $ufld9, $ufld10,
                                    $tControl1, $tControl2, $tControl3, $octlVar1, $octlVar2, $octlVar3,
                                    $oVar1, $oVar2, $oVar3, $oVar4, $oVar5, $oVar6, $oVar7, $oVar8, $oVar9, $oVar10)
    {

        if (mysqli_real_escape_string($this->dbConn->connection, $tControl2) <> '') {
            $ctVar2 = "AND t. " . mysqli_real_escape_string($this->dbConn->connection, $tControl2) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $octlVar2) . "' ";
        } else {
            $ctVar2 = '';
        }
        if (mysqli_real_escape_string($this->dbConn->connection, $tControl3) <> '') {
            $ctVar3 = "AND t. " . mysqli_real_escape_string($this->dbConn->connection, $tControl3) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $octlVar3) . "' ";
        } else {
            $ctVar3 = '';
        }

        $stableName = mysqli_real_escape_string($this->dbConn->connection, $tblName);

        $uf1 = "t. " . mysqli_real_escape_string($this->dbConn->connection, $ufld1) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $oVar1) . "'";

        if (trim($ufld2) <> '') {
            $uf2 = ", t." . mysqli_real_escape_string($this->dbConn->connection, $ufld2) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $oVar2) . "'";
        } else {
            $uf2 = '';
        }

        if (trim($ufld3) <> '') {
            $uf3 = ", t." . mysqli_real_escape_string($this->dbConn->connection, $ufld3) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $oVar3) . "'";
        } else {
            $uf3 = '';
        }
        if (trim($ufld4) <> '') {
            $uf4 = ", t." . mysqli_real_escape_string($this->dbConn->connection, $ufld4) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $oVar4) . "'";
        } else {
            $uf4 = '';
        }
        if (trim($ufld5) <> '') {
            $uf5 = ", t." . mysqli_real_escape_string($this->dbConn->connection, $ufld5) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $oVar5) . "'";
        } else {
            $uf5 = '';
        }
        if (trim($ufld6) <> '') {
            $uf6 = ", y." . mysqli_real_escape_string($this->dbConn->connection, $ufld6) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $oVar6) . "'";
        } else {
            $uf6 = '';
        }
        if (trim($ufld7) <> '') {
            $uf7 = ", t." . mysqli_real_escape_string($this->dbConn->connection, $ufld7) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $oVar7) . "'";
        } else {
            $uf7 = '';
        }
        if (trim($ufld8) <> '') {
            $uf8 = ", t." . mysqli_real_escape_string($this->dbConn->connection, $ufld8) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $oVar8) . "'";
        } else {
            $uf8 = '';
        }
        if (trim($ufld9) <> '') {
            $uf9 = ", t." . mysqli_real_escape_string($this->dbConn->connection, $ufld9) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $oVar9) . "'";
        } else {
            $uf9 = '';
        }
        if (trim($ufld10) <> '') {
            $uf10 = ", t." . mysqli_real_escape_string($this->dbConn->connection, $ufld10) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $oVar10) . "'";
        } else {
            $uf10 = '';
        }

        $iSql = "UPDATE " . $stableName . " t SET " . $uf1 . " 
                                                 " . $uf2 . "
                                                 " . $uf3 . "
                                                 " . $uf4 . " 
                                                 " . $uf5 . " 
                                                 " . $uf6 . " 
                                                 " . $uf7 . " 
                                                 " . $uf8 . "
                                                 " . $uf9 . "  
                                                 " . $uf10 . " 
                WHERE  t. " . mysqli_real_escape_string($this->dbConn->connection, $tControl1) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $octlVar1) . "'  
               " . $ctVar2 . "
               " . $ctVar3 . ";";

        $this->errorTO = $this->dbConn->processPosting($iSql, "");


        echo "<br>";
        echo $iSql;
        echo "<br>";

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");
            return $this->errorTO;
        } else {

            echo "<br>";
            echo $iSql;
            echo "<br>";
            return "F";
        }

    }

    // ****************************************************************************************************************************************************
    public function getProductDetails($principalId, $prodcode)
    {

        $sql = "SELECT pp.uid, pp.product_code, pp.product_description
               FROM  principal_product pp
               WHERE pp.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               AND   pp.product_code = '" . mysqli_real_escape_string($this->dbConn->connection, $prodcode) . "';";

        $prDet = $this->dbConn->dbGetAll($sql);
        return $prDet;

    }

    // ****************************************************************************************************************************************************
    public function updateProductDetails($prodUid, $postNewProd, $postOldProd, $userUId)
    {


        $usql = "UPDATE `kwelanga_live`.`principal_product` SET `product_code`='" . mysqli_real_escape_string($this->dbConn->connection, $postNewProd) . "' ,
  	                                                            `old_code`    ='" . mysqli_real_escape_string($this->dbConn->connection, $postOldProd) . "' , 
  	                                                            `changed_by`  ='" . mysqli_real_escape_string($this->dbConn->connection, $userUId) . "' , 
  	                                                            `change_date` = curdate()
  	              WHERE  `uid`= " . mysqli_real_escape_string($this->dbConn->connection, $prodUid) . ";";

        $this->errorTO = $this->dbConn->processPosting($usql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {

            $this->dbConn->dbQuery("commit");

            $ssql = "SELECT *
        	            FROM stock s 
        	            where s.principal_product_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prodUid) . ";";

            $sprDet = $this->dbConn->dbGetAll($ssql);

            if (count($sprDet) > 0) {

                $ssql = "UPDATE stock s SET s.stock_item = '" . mysqli_real_escape_string($this->dbConn->connection, $postNewProd) . "' 
        	                  WHERE s.principal_product_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prodUid) . ";";

                $this->errorTO = $this->dbConn->processPosting($ssql, "");

                if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                    $this->dbConn->dbQuery("commit");
                } else {
                    return $this->errorTO;
                }
            }
            $this->dbConn->dbQuery("commit");
            return $this->errorTO;
        } else {
            return $this->errorTO;
        }
    }

    // ****************************************************************************************************************************************************
    public function smartEventQuery($postPrincipal, $postFROMDATE, $postTODATE, $postDOCTYPE, $postPSMNME, $postDOCNO, $postINVNO, $postCREDNO, $postEXTTYPE, $postWAREHOUSE, $postCAPBY, $postLIMITREC)
    {

        $doctype = $credNo = $docNo = $invNo = $storeName = $retailer = $warehouse = '';

        if (trim($postDOCTYPE) == "2") {
            $doctype = "AND dm.document_type_uid in (4,31,32)";
        } else {
            $doctype = "AND dm.document_type_uid in (1,6,13,27)";
        }

        if (trim($postPSMNME) <> "") {
            $storeName = "AND psm.deliver_name  LIKE '%" . trim(mysqli_real_escape_string($this->dbConn->connection, $postPSMNME)) . "%' ";
        }

        if (trim($postDOCNO) <> "") {
            $docNo = "AND dm.document_number in (" . trim(mysqli_real_escape_string($this->dbConn->connection, $postDOCNO)) . ") ";
        }

        if (trim($postINVNO) <> "") {
            $invNo = "AND dh.invoice_number LIKE '%" . trim(mysqli_real_escape_string($this->dbConn->connection, $postINVNO)) . "%' ";
        }

        if (trim($postCREDNO) <> "") {
            $credNo = "AND dh.invoice_number LIKE '%" . trim(mysqli_real_escape_string($this->dbConn->connection, $postCREDNO)) . "%' ";
        }

        if (trim($postCAPBY) <> "") {
            $retailer = "AND dh.captured_by LIKE  '%" . trim(mysqli_real_escape_string($this->dbConn->connection, $postCAPBY)) . "%' ";
        }
        echo "<br>";
        echo $postWAREHOUSE;
        echo "ll<br>";
        if (trim($postWAREHOUSE) <> "Select a Warehouse") {
            $warehouse = "AND dm.depot_uid = " . trim(mysqli_real_escape_string($this->dbConn->connection, $postWAREHOUSE));
        }
        if (trim($postLIMITREC) == '1') {
            $limit = "LIMIT 20";
        } elseif (trim($postLIMITREC) == '2') {
            $limit = "LIMIT 100";
        } else {
            $limit = "";
        }
        if (trim($postEXTTYPE) == "1") {
            $vsql = "SELECT vep.notification_uid
                   FROM .voqado_extract_parameters vep
                   WHERE vep.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $postPrincipal);

            $voqType = $this->dbConn->dbGetAll($vsql);

            if (count($voqType) <> 0) {
                $extType = "AND se.type_uid = " . trim(mysqli_real_escape_string($this->dbConn->connection, $voqType[0]['notification_uid']));
            } else {
                $sEventRecs = "Error This is not a Voqado Principal";
                return $sEventRecs;
            }
        } elseif (trim($postEXTTYPE) == "2") {
            $extType = "AND se.type = 'EXT'";
        } elseif (trim($postEXTTYPE) == "4") {
            if (!in_array($postCAPBY, array('CHICKEN'))) {
                $sEventRecs = "Error - GDS Notification - Captured by must be `CHICKEN`  ...";
                return $sEventRecs;
            } else {
                $extType = "AND se.type = 'N'";
            }
        } else {
            if (!in_array($postCAPBY, array('PNP', 'CHECKERS', 'INSTANT'))) {

                $sEventRecs = "Error - Retail Invoice - Captured by must be `PNP` or `CHECKERS` ...";
                return $sEventRecs;
            } else {
                $extType = "AND se.type = 'RTINV'";
            }
        }

        $sql = "SELECT p.name as 'Principal',
                      p.uid  as 'PrinId',
                      dt.description as 'Document_Type',
                      dm.document_number as 'Document Number',
                      dh.invoice_number,
                      dm.uid as 'Document_Uid', 
                      psm.deliver_name as 'Store',
                      se.`status` as 'Status',
                      se.status_msg,
                      if(dm.document_type_uid in (1,6,13), 'Invoice','Credit') as 'Document Type',
                      se.type_uid as 'Type', 
                      dh.invoice_date as 'Invoice Date', 
                      se.general_reference_1 as 'New File',
                      se.general_reference_2,
                      se.`comment` as 'Old File',
                      se.uid as 'DataId',
                      se.`type`
               FROM      document_master dm
               LEFT JOIN smart_event se on se.data_uid = dm.uid " . $extType . "
               left JOIN document_type dt on dt.uid = dm.document_type_uid,
                         document_header dh, 
                         principal_store_master psm, 
               principal p
               WHERE     dm.uid = dh.document_master_uid
               AND       dh.principal_store_uid = psm.uid
               AND       dm.principal_uid = p.uid
               AND       dm.principal_uid =       " . mysqli_real_escape_string($this->dbConn->connection, $postPrincipal) . "
               AND       dh.invoice_date BETWEEN '" . mysqli_real_escape_string($this->dbConn->connection, $postFROMDATE) . "'  
                                         AND     '" . mysqli_real_escape_string($this->dbConn->connection, $postTODATE) . "' 
               AND       dh.document_status_uid IN (76,77,78,81,73)
               " . $doctype . " 
               " . $credNo . " 
               " . $storeName . "
               " . $docNo . "
               " . $invNo . "
               " . $retailer . "
               " . $warehouse . "
               order by  dm.document_type_uid, dh.invoice_date
               " . $limit . ";";
        $sEventRecs = $this->dbConn->dbGetAll($sql);

        return $sEventRecs;
    }


    // ****************************************************************************************************************************************************
    public function smartEventUpdate($dataUid)
    {

        $use = "UPDATE `smart_event` SET `status`='Q',
  	                                  `status_msg`= NULL,        
  	                                  `general_reference_1`= NULL 
  	         WHERE  `uid` IN  (" . mysqli_real_escape_string($this->dbConn->connection, $dataUid) . ");";

        $this->errorTO = $this->dbConn->processPosting($use, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");

        }
        $seupdate = $this->errorTO->type;
        return $seupdate;
    }

    // ****************************************************************************************************************************************************
    public function smartEventInsert($prinId, $dataUid)
    {

        $jsql = "SELECT j.notification
             FROM   job_execution j
             WHERE  j.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
             AND   j.name LIKE 'dailyExtracts';";

        $jeN = $this->dbConn->dbGetAll($jsql);

        $ise = "INSERT INTO smart_event (created_date, type, type_uid, processed_date, status, status_msg, data_uid) VALUES 
  	                                 (NOW(), 
  	                                 'EXT',
  	                                 " . $jeN[0]['notification'] . ", 
  	                                  NULL, 
  	                                 'Q', 
  	                                 '', 
  	                                 " . mysqli_real_escape_string($this->dbConn->connection, $dataUid) . ");";

        $this->errorTO = $this->dbConn->processPosting($ise, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");

        }

        $seInsert = $this->errorTO->type;
        return $seInsert;
    }

    // ****************************************************************************************************************************************************
    public function checkForDuplicates($prinId, $docNo)
    {

        $sql = "SELECT *
                FROM .import_document_control idc
                WHERE idc.Principal_uid   =  " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . " 
                AND   idc.document_number = '" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . "';";
        echo $sql;
        echo "<br>";
        $seDup = $this->dbConn->dbGetAll($sql);

        return $seDup;

    }

    // ****************************************************************************************************************************************************
    public function addToDocControl($prinId, $docNo, $file_name)
    {

        $isql = "INSERT INTO import_document_control (import_document_control.principal_uid,
  	                                                 import_document_control.document_number,
  	                                                 import_document_control.tod,
  	                                                 import_document_control.file_name)
  	            VALUES ( " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . ",
  	                     '" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . "',
  	                     NOW(),
                         '" . mysqli_real_escape_string($this->dbConn->connection, $file_name) . "');";

        $this->errorTO = $this->dbConn->processPosting($isql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");
        }

        $seInsert = $this->errorTO->type;
        return $seInsert;
    }

    // ****************************************************************************************************************************************************
    public function checkNotificationParams()
    {

        $sql = "SELECT *
                FROM .notification_recipients nr
                WHERE nr.additional_parameter_string <> ''";

        $seDup = $this->dbConn->dbGetAll($sql);

        return $seDup;
    }

    // ****************************************************************************************************************************************************
    public function updateNotificationParams($updateUid, $p1, $p2, $p3, $p4, $p5, $p6, $p7)
    {

        $sql = "UPDATE notification_recipients nr set nr.p1 = '" . mysqli_real_escape_string($this->dbConn->connection, $p1) . "',
                                                      nr.p2 = '" . mysqli_real_escape_string($this->dbConn->connection, $p2) . "',
                                                      nr.p3 = '" . mysqli_real_escape_string($this->dbConn->connection, $p3) . "',
                                                      nr.p4 = '" . mysqli_real_escape_string($this->dbConn->connection, $p4) . "',
                                                      nr.p5 = '" . mysqli_real_escape_string($this->dbConn->connection, $p5) . "',
                                                      nr.p6 = '" . mysqli_real_escape_string($this->dbConn->connection, $p6) . "',
                                                      nr.p7 = '" . mysqli_real_escape_string($this->dbConn->connection, $p7) . "'
                WHERE nr.uid = " . mysqli_real_escape_string($this->dbConn->connection, $updateUid) . ";";
        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");
        }

        $seupdate = $this->errorTO->type;
        return $seupdate;

    }

    // ****************************************************************************************************************************************************
    public function getUseOrganisation($userId)
    {
        $sql = "SELECT u.organisation_name
                FROM users u
                WHERE u.uid = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . ";";

        $uOrg = $this->dbConn->dbGetAll($sql);

        return $uOrg;
    }

    // ****************************************************************************************************************************************************
    public function getUseOrganisationUsers($prinId, $userId)
    {

        $sql = "SELECT DISTINCT(upd.user_id), 
                       u.username, 
                       u.full_name, 
                       u.user_email, 
                       u.deleted, 
                       u.staff_user
                FROM .user_principal_depot upd
                LEFT JOIN .users u ON upd.user_id = u.uid
                WHERE upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
                and u.deleted <> 1
                AND u.staff_user = 'N'
                AND u.organisation_name = '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "';";

        $mfUser = $this->dbConn->dbGetAll($sql);

        return $mfUser;
    }

    // ****************************************************************************************************************************************************
    public function getUserRoles($prinId, $userId)
    {
        $sql = "SELECT role.uid AS 'roleId', 
                       role.description, 
                       ur.user_id,
                       pdt.description,
                       dt.description,
                       if(pdt.uid IS NOT NULL,pdt.description,if(dt.uid IS NOT NULL,dt.description, role.description)) AS 'RoleDesc',
                       role.`group`
                  
                FROM role
                LEFT JOIN user_role ur ON ur.role_id = role.uid 
                                       AND ur.user_id = '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "' 
                                       AND ur.entity_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
                LEFT JOIN .document_type dt ON dt.role_id = role.uid                      
                LEFT JOIN .principal_document_type pdt ON pdt.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
                                                       AND pdt.document_type_uid = dt.uid
                WHERE role.allow_admin = 'Y'
                ORDER BY 'RoleDesc';";

        $uOrg = $this->dbConn->dbGetAll($sql);

        return $uOrg;
    }

    // ****************************************************************************************************************************************************
    public function clearUserRoles($prinId, $userId, $roleList)
    {

        $sql = "DELETE FROM user_role
               WHERE entity_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
               AND   user_id    = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . "
               AND   role_id IN ( " . mysqli_real_escape_string($this->dbConn->connection, $roleList) . ");";

//       	  echo "<br>";
//       	  echo $sql;


        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");
        } else {
            echo "<br>";
            echo $sql;
        }
        return $this->errorTO;

    }

    // ****************************************************************************************************************************************************
    public function addUserRoles($prinId, $userId, $roleId)
    {

        $sql = "INSERT IGNORE INTO user_role (user_role.user_id,
                                      user_role.role_id,
                                      user_role.entity_uid)
               VALUES (" . mysqli_real_escape_string($this->dbConn->connection, $userId) . ", 
                       " . mysqli_real_escape_string($this->dbConn->connection, $roleId) . ",
                       " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . ");";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

//       echo $sql;

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");
        }
        return $this->errorTO;
    }

    // ****************************************************************************************************************************************************
    public function getInvoiceDetailsToAmend($prinId, $docNum)
    {

        $sql = "SELECT dm.uid AS 'dUid', 
                      dm.document_number,
                      dm.document_type_uid,
                      dh.principal_store_uid,
                      dh.invoice_date,
                      dh.customer_order_number,
                      dh.document_status_uid,
                      s.description,
                      psm.deliver_name,
                      dd.product_uid,
                      pp.product_code,
                      pp.product_description,
                      dd.ordered_qty,
                      dd.document_qty,
                      dd.delivered_qty,
                      dt.tripsheet_number,
                      dt.i_dispatched,
                      dt.t_dispatched, 
                      se.status
               FROM .document_master dm
               INNER JOIN .document_header dh ON dm.uid = dh.document_master_uid
               INNER JOIN .document_detail dd ON dd.document_master_uid = dm.uid
               INNER JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
               INNER JOIN .principal_product pp ON pp.uid = dd.product_uid
               INNER JOIN .`status` s ON dh.document_status_uid = s.uid 
               LEFT JOIN  .document_tripsheet dt ON dm.uid = dt.document_master_uid
               LEFT JOIN  .smart_event se on se.data_uid = dm.uid AND se.type = 'EXT' 
               WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
               AND dm.document_number = '" . mysqli_real_escape_string($this->dbConn->connection, $docNum) . "';";

        echo $sql;

        $aCuDet = $this->dbConn->dbGetAll($sql);

        return $aCuDet;
    }

    // ****************************************************************************************************************************************************
    public function resetInvoicedTransaction($docUid)
    {

        $sql = "UPDATE document_master dm
                      INNER JOIN document_header dh ON dm.uid = dh.document_master_uid
                      INNER JOIN document_detail dd ON dm.uid = dd.document_master_uid
                      INNER JOIN depot d on d.uid = dm.depot_uid
                      LEFT  JOIN stock s ON s.principal_id = dm.principal_uid 
                                          AND s.depot_id    = dm.depot_uid
                                          AND s.principal_product_uid = dd.product_uid 
                                                SET s.delivered          = if((s.delivered + dd.document_qty) >= 0,0,s.delivered + dd.document_qty),
                                                    s.`allocations`      = s.`allocations` - dd.document_qty,
                                                    s.`pending_dispatch` = if(d.`allow_pending_dispatch` = 'Y',  s.`pending_dispatch`- dd.document_qty, s.`pending_dispatch`),
                                                    dd.document_qty = 0,
                                                    dd.delivered_qty = 0,
                                                    dh.document_status_uid = " . DST_ACCEPTED . ",
                                                    dh.invoice_date = dh.order_date,
                                                    dh.pending_dispatch = 'N'
                      WHERE dm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . ";";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");
        } else {
            echo "<br>" . $sql . "<br>";
            return $this->errorTO;
        }

        $sql = "UPDATE document_detail dd set dd.extended_price =  dd.ordered_qty * dd.net_price,
                                                    dd.vat_amount     =  dd.ordered_qty * dd.net_price  * (dd.vat_rate /100),
                                                    dd.total          = (dd.ordered_qty * dd.net_price) + (dd.ordered_qty * dd.net_price * (dd.vat_rate /100))
                      WHERE dd.document_master_uid =  " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . ";";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");
        } else {
            echo "<br>" . $sql . "<br>";
            return $this->errorTO;
        }

        $sql = "UPDATE document_header dh set dh.cases   =  (SELECT sum(dd.ordered_qty)
                                                                   FROM   document_detail dd
                                                                   WHERE  dd.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . "),
                                                 
                                             dh.exclusive_total =  (SELECT sum(dd.extended_price)
                                                                    FROM   document_detail dd
                                                                    WHERE  dd.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . "),
                             
                                             dh.vat_total       =  (SELECT sum(dd.vat_amount)
                                                                    FROM   document_detail dd
                                                                    WHERE  dd.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . "),
                                                 
                                             dh.invoice_total   =  (SELECT sum(dd.total)
                                                                    FROM   document_detail dd
                                                                    WHERE  dd.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . ")
                                                 
                     WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . ";";


        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");
            return $this->errorTO;
        } else {
            echo "<br>" . $sql . "<br>";
            return $this->errorTO;
        }

    }

    // ****************************************************************************************************************************************************
    public function getUserTel($uUid)
    {

        $sql = "SELECT u.user_tel, u.reset_auth
               FROM   users u
               WHERE  u.uid = " . mysqli_real_escape_string($this->dbConn->connection, $uUid) . ";";

//               echo $sql;

        $uNum = $this->dbConn->dbGetAll($sql);

        return $uNum;
    }

    // ****************************************************************************************************************************************************
    public function getUidsToCancel($prinUid, $depotList, $dayInterval)
    {

        $sql = "SELECT dm.uid
                 FROM .document_master dm
                 INNER JOIN .document_header dh ON dm.uid = dh.document_master_uid
                 INNER JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
                 WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinUid) . "
                 AND   dm.document_type_uid IN (" . DT_ORDINV . ")
                 AND   dh.document_status_uid in ( " . DST_UNACCEPTED . "," . DST_ACCEPTED . "," . DST_INPICK . ")
                 AND   dh.order_date < CURDATE() - INTERVAL " . mysqli_real_escape_string($this->dbConn->connection, $dayInterval) . " day
                 AND   dm.depot_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $depotList) . ")";

        // echo "<br>" . $sql . "<br>" ;

        $cList = $this->dbConn->dbGetAll($sql);

        return $cList;

    }

    // ****************************************************************************************************************************************************
    public function cancelSelectedOrders($dmUid)
    {

        $sql = "UPDATE document_master dm,
                        document_header dh,
                        document_detail dd set dh.document_status_uid = " . DST_CANCELLED . ",
                                                                        dh.invoice_date = '" . date('Y-m-d') . "',
                                                                        dh.cases           = 0,
                                                                        dh.exclusive_total = 0,
                                                                        dh.vat_total       = 0,
                                                                        dh.invoice_total   = 0,
                                                                        dd.delivered_qty   = 0,
                                                                        dd.extended_price  = 0,
                                                                        dd.vat_amount      = 0,
                                                                        dd.total           = 0,
                                                                        dh.pod_reason_uid  = 23
                 WHERE dm.uid = dh.document_master_uid
                 AND   dm.uid = dd.document_master_uid
                 AND   dm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $dmUid) . ";";

        // echo "<br>" . $sql . "<br>";
        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");
            return $this->errorTO;
        } else {
            echo "<br>" . $sql . "<br>";
            return $this->errorTO;
        }

    }

    // ****************************************************************************************************************************************************
    public function copyHoneyfieldsWalkingstock()
    {

        $sql = "UPDATE document_master dm
                      INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid
                      INNER JOIN .document_detail dd ON dd.document_master_uid = dm.uid
                      INNER JOIN .principal_product pp ON pp.uid = dd.product_uid
                      INNER  JOIN .stock s ON s.depot_id = 230 AND s.principal_product_uid = dd.product_uid 
                             SET s.delivered = if(dm.document_type_uid = 1, s.delivered - dd.document_qty, s.delivered),
                                 s.returns_cancel = if(dm.document_type_uid = 4, s.returns_cancel + dd.document_qty, s.returns_cancel),
                                 dd.bbq_updated = 'Y'
                      WHERE dm.principal_uid = 305
                      AND   dm.depot_uid = 376
                      AND   dh.invoice_date > '2021-10-11'
                      AND   dh.document_status_uid IN (76,77,78,81)
                      AND   dm.document_type_uid IN (1,4)
                      AND   pp.non_stock_item = 'N'
                      AND   dd.bbq_updated = 'N'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");
            return $this->errorTO;
        } else {
            echo "<br>" . $sql . "<br>";
            return $this->errorTO;
        }
    }

    // ****************************************************************************************************************************************************
    public function getOrderDetailsToManage($docUid)
    {

        $sql = "SELECT dm.uid,
                             dm.document_number, 
                             dm.depot_uid, 
                             d.name AS 'wh',
                             psm.uid AS 'storeUid',
                             psm.deliver_name AS 'store',
                             dh.document_status_uid,
                             s.description AS 'status',
                             dh.customer_order_number
                     FROM document_master dm
                     INNER JOIN document_header dh ON  dm.uid = dh.document_master_uid
                     INNER JOIN  principal_store_master psm  ON psm.uid = dh.principal_store_uid
                     INNER JOIN  depot d ON d.uid = dm.depot_uid
                     INNER JOIN `status` s ON s.uid = dh.document_status_uid
                     WHERE dm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . ";";

        // echo "<br>" . $sql . "<br>" ;

        $cList = $this->dbConn->dbGetAll($sql);

        return $cList;

    }

    // ****************************************************************************************************************************************************
    public function lookForPickNPayStore($type, $brgln)
    {

        if ($type == 'GETSTOREGLN') {
            $sType = "WHERE a.GLN = '" . mysqli_real_escape_string($this->dbConn->connection, $brgln) . "'";
        } else {
            $sType = "WHERE a.Branch = '" . mysqli_real_escape_string($this->dbConn->connection, $brgln) . "'";
        }

        $sql = "SELECT *
                   FROM .pnp_store_list a
                   " . $sType;

        $cList = $this->dbConn->dbGetAll($sql);

        return $cList;
    }

    // ****************************************************************************************************************************************************
    public function lookForCheckersStore($type, $brgln)
    {

        if ($type == 'GETSTOREGLN') {
            $sType = "WHERE a.GLN = '" . mysqli_real_escape_string($this->dbConn->connection, $brgln) . "'";
        } else {
            $sType = "WHERE trim(LEADING '0' FROM a.StoreNumber) = trim(LEADING '0' FROM " . mysqli_real_escape_string($this->dbConn->connection, $brgln) . ")";
        }

        $sql = "SELECT *
                   FROM .checkers_store_master a
                   " . $sType;

        $cList = $this->dbConn->dbGetAll($sql);

        return $cList;
    }

    // ****************************************************************************************************************************************************
    public function lookForStoreUid($type, $uid)
    {

        $sql = "SELECT *
                   FROM .principal_store_master psm
                   WHERE psm.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $uid) . "';";

        $cList = $this->dbConn->dbGetAll($sql);

        return $cList;
    }

    // ****************************************************************************************************************************************************
    public function getPrincipalChainList($principal)
    {

        $sql = "SELECT *
                   FROM .principal_chain_master a
                   WHERE a.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . "
                   AND   a.`status` = 'A';";

        $cList = $this->dbConn->dbGetAll($sql);

        return $cList;
    }

    // ****************************************************************************************************************************************************
    public function checkForExistingGln($principal, $gln)
    {

        $sql = "SELECT *
                   FROM .principal_store_master psm
                   WHERE psm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . "
                   AND   psm.ean_code      = '" . mysqli_real_escape_string($this->dbConn->connection, $gln) . "';";
        $cList = $this->dbConn->dbGetAll($sql);

        return $cList;
    }

    // ****************************************************************************************************************************************************
    public function insertNewStore($LoadGlnStoreTO)
    {

        $sql = "INSERT INTO `principal_store_master` (`ean_code`,
                                                            `principal_uid`,
                                                            `last_change_by_userid`, 
                                                            `deliver_name`, 
                                                            `deliver_add1`, 
                                                            `deliver_add2`, 
                                                            `deliver_add3`, 
                                                            `bill_name`, 
                                                            `bill_add1`, 
                                                            `bill_add2`, 
                                                            `bill_add3`, 												   
                                                            `vat_number`, 
                                                            `depot_uid`, 
                                                            `principal_chain_uid`,
                                                            `alt_principal_chain_uid`,
                                                            `branch_code`, 
                                                            `old_account`,
                                                            `retailer`) 
                                                             VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->gln) . "', 
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->Principal) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->UserId) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->Name) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->add1) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->add2) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->add3) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->BillName) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->Billadd1) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->Billadd2) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->Billadd3) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->Vat) . "', 
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->wareHouse) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->chain) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->chain) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->Branch) . "',
                                                                    '" . substr(mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->gln, -6)) . "',
                                                                    '" . mysqli_real_escape_string($this->dbConn->connection, $LoadGlnStoreTO->retailer) . "');";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->dbConn->dbQuery("commit");
            return $this->errorTO;
        } else {
            echo "<br>" . $sql . "<br>";
            return $this->errorTO;
        }
    }
    // ****************************************************************************************************************************************************

}

?>
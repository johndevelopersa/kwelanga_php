<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "properties/dbSettings.inc");
include_once($ROOT . $PHPFOLDER . "libs/CommonUtils.php");
// NB !!!
// DO NOT USE mysql_pconnect because persistent connections have problems with locking if script exits in middle.
// BUT MORE IMPORTANTLY, the 4th parameter (new_link) does not work ! This 4th param is needed if you open up 2 diff dbConnect objects in same script
// and point them to 2 diff dbs, they will end up both pointing to same db if 4th param not set to true !
// Besides - apache pools mysql connections regardless of pconnect !
class dbConnect
{

    var $dbQueryResult;
    var $dbQueryResultRows;
    var $connection;

    function getconnectionstring()
    {
        return iHOSTNAME."|".iUSERNAME."|".iPASSWORD."|".iDATABASE;
    }

    function dbConnection()
    {
        $this->connection = mysqli_connect(iHOSTNAME, iUSERNAME, iPASSWORD, iDATABASE) or die ("Cannot connect to database");
        $db = mysqli_select_db($this->connection, iDATABASE) or die ("Couldn't select database");
        $this->dbQuery("set autocommit=0;");
    }

    function dbConnectionAuditor()
    {
        $this->connection = mysqli_connect(iHOSTNAME, iUSERNAME, iPASSWORD, true) or die ("Cannot connect to database");
        $db = mysqli_select_db(DATABASE_AUDITOR, $this->connection) or die ("Couldn't select database");
        $this->dbQuery("set autocommit=0;");
    }

    function dbConnectionArchive()
    {
        $this->connection = mysqli_connect(iHOSTNAME, iUSERNAME, iPASSWORD, true) or die ("Cannot connect to database");
        $db = mysqli_select_db(DATABASE_ARCHIVE, $this->connection) or die ("Couldn't select database");
        $this->dbQuery("set autocommit=0;");
    }

    function dbQuery($query)
    {
        $result = mysqli_query($this->connection, $query) or die ("Error in query " . ((DEBUG_MODE == "Y") ? $query : "") . " : " . mysqli_error($this->connection) . " " . mysqli_errno($this->connection));
        $this->dbQueryResult = $result;

        @$resultrows = mysqli_num_rows($result);
        $this->dbQueryResultRows = $resultrows;
    }

    function dbinsQuery($query)
    {
        $result = mysqli_query($this->connection, $query);
        $this->dbQueryResult = $result;

        $resultrows = mysqli_affected_rows($this->connection);
        $this->dbQueryResultRows = $resultrows;
    }

    function dbFree()
    {
        // free result set memory
        mysqli_free_result($this->dbQueryResult);
    }

    function dbClose()
    {
        $closeConn = mysqli_close($this->connection);
    }

    function dbGetLastInsertId()
    {
        $result = mysqli_query($this->connection, "select LAST_INSERT_ID() uid");
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $uid = $row['uid'];
        return $uid;
    }

    public function processPosting($sql, $refForErrorTO)
    {
        global $ROOT;
        global $PHPFOLDER;
        include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
        $errorTO = new ErrorTO;

        $result = mysqli_query($this->connection, "set autocommit=0;"); // this is VITAL for operations such as "FOR UPDATE", rollbacks etc.
        $result = mysqli_query($this->connection, $sql);

        if ((!$result) || (mysqli_errno($this->connection) != "")) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Error executing mysqli_query in processPosting: " . ((DEBUG_MODE == "Y") ? $sql : "") . " : " . mysqli_error($this->connection);
            return $errorTO;
        }

        $this->dbQueryResultRows = mysqli_affected_rows($this->connection);

        $infoArr = CommonUtils::getmysqlInfo(mysqli_info($this->connection));
        $errorTO->object = $infoArr;
        $errorTO->sql = $sql;
        if (($infoArr['rows_matched'] > 0) && ($infoArr['changed'] == "0")) $updatedButNoChanges = true; else $updatedButNoChanges = false;
        if ($updatedButNoChanges) {
            $errorTO->type = FLAG_ERRORTO_SUCCESS;
            $errorTO->description = "Record(s) were not saved because no field details were changed.<BR>" . mysqli_info($this->connection);

            return $errorTO;
        } else {
            $errorTO->type = FLAG_ERRORTO_SUCCESS;
            $errorTO->description = "Request Successful.<BR>" . mysqli_info($this->connection);

            return $errorTO;
        }

        return $errorTO;
    }

    function dbGetOne($sql)
    {
        $result = mysqli_query($this->connection, $sql) or die ("Error in query  " . ((DEBUG_MODE == "Y") ? $sql : "") . " : " . mysqli_error($this->connection) . " " . mysqli_errno($this->connection));
        $this->dbQueryResult = $result;

        $resultrows = mysqli_num_rows($result);
        $this->dbQueryResultRows = $resultrows;

        if ($this->dbQueryResultRows > 0) {
            return mysqli_fetch_array($this->dbQueryResult, MYSQLI_ASSOC);
        }
        return [];
    }

    function dbGetAll($sql)
    {
        $result = mysqli_query($this->connection, $sql) or die ("Error in query  " . ((DEBUG_MODE == "Y") ? $sql : "") . " : " . mysqli_error($this->connection) . " " . mysqli_errno($this->connection));
        $this->dbQueryResult = $result;

        $resultrows = mysqli_num_rows($result);
        $this->dbQueryResultRows = $resultrows;

        $arr = array();
        if ($this->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        mysqli_free_result($this->dbQueryResult);

        return $arr;
    }

    function mysqli_result($result, $row)
    {
        $resrow = array();
        mysqli_data_seek($result, $row);
        $resrow = mysqli_fetch_assoc($result);
        return $resrow;
    }

    function dbGetAllNoDie($sql)
    {
        $result = mysqli_query($this->connection, $sql);
        if (!$result) {
            $errResult = ("Error in query  " . ((DEBUG_MODE == "Y") ? $sql : "") . " : " . mysqli_error($this->connection) . " " . mysqli_errno($this->connection));
            return $errResult;
        } else {
            $this->dbQueryResult = $result;
            $resultrows = mysqli_num_rows($result);
            $this->dbQueryResultRows = $resultrows;

            $arr = array();
            if ($this->dbQueryResultRows > 0) {
                while ($row = mysqli_fetch_array($this->dbQueryResult, MYSQLI_ASSOC)) {
                    $arr[] = $row;
                }
                mysqli_free_result($this->dbQueryResult);
                return $arr;
            }
        }
    }


}

?>
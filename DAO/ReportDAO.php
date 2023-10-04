<?php


include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostBIDAO.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/SmartEventTO.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');


class ReportSQLTO
{

    public $sql;
    public $fileName;
    public $database;
    public $columnFormat;
    public $xmlSchema;
    public $reportLevel;
    public $postBIDAO;
    public $runOnceFieldName;
    public $hiddenColList;
    public $totalBreakColList;
    public $totalSumColList;
    public $pdfScriptPath;

}


class RunReportTO
{
    public $data;
    public $hiddenCols;  // hidden cols are removed in data, and instead put in hiddenCols which is an array with fieldnames as key. Will always be same number of elements as main data array
    public $needsCommit = false;
}

class ReportDAO
{

    private $dbConn;
    public $errorTO;


    function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
        $this->errorTO = new ErrorTO;
        $this->postBIDAO = new PostBIDAO($dbConn);
    }

    /*
     * Report SQL Generation Procs
     */
    function reportSQL_isRequiredParameter($paramInt, &$report)
    {
        $listReq = explode(",", $report[0]['required_fields']);
        if (in_array($paramInt, $listReq)) return true;
        else return false;
    }

    // run the actual SQL - NO LONGER HANDLES FORMATTING...
    /*
     * @params runOnceParams is an array of :
     *          runOnceFieldName
     *          type ~ S, U (Scheduler or User) - USE STATIC vars declared RUNREPORTSQL_*
     *          typeUId ~ the scheduler UId or the UserUId
     * @params hiddenColList ~ from the reports table as-is
     */
    function reportSQL_runReportSQL($sql, $database, $runOnceParams, $hiddenColList)
    {
        global $ROOT, $PHPFOLDER;
        include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");

        /* @var $dbC dbConnect */
        $dbC = $this->dbConn;
        $dbC->dbinsQuery($sql);

        if (!$dbC->dbQueryResult) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Error in query " . ((DEBUG_MODE == "Y") ? $sql : "") . " : " . mysqli_error($dbC->connection) . " " . mysqli_errno($dbC->connection);
            return $this->errorTO;
        }

        $dataArr = array();
        $hiddenCols = array();
        $runOnceValues = array();
        $hiddenFieldNameArray = explode(",", $hiddenColList);
        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {

            // swap out any php_functions with the result if specified
            foreach ($row as &$fld) {
                if (strpos($fld, "php_obj") !== false) {
                    $constArr = explode("|", $fld); // php_obj: | php_func: | php_params:
                    $set = explode(":", $constArr[0]);
                    $className = trim($set[1]);
                    $set = explode(":", $constArr[1]);
                    $methodName = trim($set[1]);
                    $set = explode(":", $constArr[2]);
                    $paramVals = explode(",", trim($set[1]));

                    include_once($ROOT . $PHPFOLDER . "DAO/{$className}.php");
                    // call the specific adaptor (which may vary)
                    $class = new $className($this->dbConn);
                    $fld = call_user_func_array(array($class, $methodName), $paramVals); // call with array of params
                }
            }
            unset($fld);

            // handle run once col values
            if ((isset($runOnceParams["runOnceFieldName"])) && ($runOnceParams["runOnceFieldName"] != "") && ($row[$runOnceParams["runOnceFieldName"]] != "")) {
                $runOnceValues[$row[$runOnceParams["runOnceFieldName"]]] = $row[$runOnceParams["runOnceFieldName"]];
            }
            // handle hidden cols
            if ($hiddenColList != "") {
                foreach ($hiddenFieldNameArray as $hc) {
                    $hiddenCols[$hc][] = $row[$hc]; // do not specify 2nd key as there must be one for every main data row 1:1
                    unset($row[$hc]); // delete the column so it will not affect any processing downstream
                }
            }
            $dataArr[] = $row;
        }

        if ($dbC->dbQueryResultRows == 0) {
            $dataArr = array();
        }

        $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        $this->errorTO->description = "Successful";
        $this->errorTO->object = new RunReportTO;
        $this->errorTO->object->data = $dataArr;
        $this->errorTO->object->hiddenCols = $hiddenCols;

        // handle the run-once requirements if specified
        if (sizeof($runOnceValues) > 0) {

            if (
                (!isset($runOnceParams["type"])) ||
                (!isset($runOnceParams["typeUId"]))
            ) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Failed in reportSQL_runReportSQL : required runOnceParams value(s) not supplied";
                return $this->errorTO;
            }

            $seTO = new SmartEventTO;
            $seTO->type = $runOnceParams["type"];
            $seTO->typeUid = $runOnceParams["typeUId"];
            $seTO->dataUid = $runOnceValues;
            $seTO->status = FLAG_STATUS_CLOSED;
            $seTO->statusMsg = "Run Once record only";

            $rTO = $this->postBIDAO->postSmartEventBulk($seTO);

            if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Failed in reportSQL_runReportSQL : " . $rTO->description;
                return $this->errorTO;
            }

            $this->errorTO->object->needsCommit = true;

        }

        return $this->errorTO;
    }


    function reportSQL_arrayToCSV($array, $displayHeaderRow = true, $userReportOutputSetting = false)
    {

        $content = '';

        if (strtoupper($userReportOutputSetting) == 'SEP') {
            $content .= "sep=,\n";
        }
        //add more user output settings here...

        $rowCnt = 0;

        foreach ($array as $row) {

            $i = 0;

            // output headers
            if ($rowCnt == 0 && $displayHeaderRow === true) {
                foreach ($row as $key => $value) {
                    if ($i == 0) $content .= DELIMITER_REP_FIELD . str_replace(DELIMITER_REP_FIELD, "", $key) . DELIMITER_REP_FIELD;
                    else $content .= DELIMITER_REP_COLUMN . DELIMITER_REP_FIELD . str_replace(DELIMITER_REP_FIELD, "", $key) . DELIMITER_REP_FIELD;
                    $i++;
                }
                $content .= "\n";
                $i = 0;
            }

            // output detail
            foreach ($row as $key => $value) {

                $ZEROFIX = (!empty($value) && is_numeric($value) && (strval(substr($value, 0, 1)) == "0")) ? ('=') : ('');
                if ($i == 0) {
                    $content .= $ZEROFIX . DELIMITER_REP_FIELD . str_replace(DELIMITER_REP_FIELD, "", $value) . DELIMITER_REP_FIELD;
                } else {
                    $content .= DELIMITER_REP_COLUMN . $ZEROFIX . DELIMITER_REP_FIELD . str_replace(DELIMITER_REP_FIELD, "", $value) . DELIMITER_REP_FIELD;
                }

                $i++;
            }
            $content .= "\n";
            $rowCnt++;

        }

        return $content;

    }


    function reportSQL_arrayToHTML($array, $displayHeaderRow = true, $columnFormatString = false)
    {

        $rowCnt = 0;


        $crlf = "\r\n";

        //colors and styles - needs to be hard coded for email - stylesheets are striped in some email browsers

        $css_odd_row = '';
        $css_even_row = 'class="even"';

        $content = $crlf . '<style type="text/css">' . $crlf;
        $content .= ".report_table {border-collapse:collapse;border:1px solid #047;font: 12px Helvetica,Verdana,Arial,sans-serif;text-align:left} " . $crlf;
        $content .= ".report_table th {padding:0px 5px;background:lightskyBlue;height:30px;color:#047;border:1px solid #047}" . $crlf;
        $content .= ".report_table td {border:1px solid lightskyblue}" . $crlf;
        $content .= ".report_table .even {background:aliceblue;}";
        $content .= '</style>' . $crlf;
        $content .= '<div style="padding:15px;">' . $crlf;
        $content .= '<table cellpadding="3" cellspacing="0" border="0" class="report_table">' . $crlf;


        //COLUMN FORMATER -
        if ($columnFormatString !== false) {
            $fIa = explode(';', $columnFormatString);
            $fFunctArr = array();
            $fColArr = array();
            foreach ($fIa as $k => $format) {
                $formatA = explode('=', $format);
                $fCa = explode(',', trim(strtoupper($formatA[0])));
                foreach ($fCa as $fColName) {
                    $fColArr[$fColName] = $k;
                }
                unset($formatA[0]);
                $fFunctArr[] = trim(implode('=', $formatA));
            }
        }

        //CALCULATE COLUMN WIDTHS.
        $n = 0;
        $arrayLen = array();

        foreach ($array as $k => $row) {
            foreach ($row as $key => $dataStr) {
                if ($n == 0) {
                    $arrayLen[$key] = (strlen($dataStr) < strlen($key)) ? strlen($key) : strlen($dataStr);
                } else {
                    if ($arrayLen[$key] < strlen($dataStr)) {
                        $arrayLen[$key] = strlen($dataStr);
                    }
                }
            }
            $n++;
        }


        foreach ($array as $k => $row) {

            $i = 0;
            // output headers
            if ($rowCnt == 0) {

                $content .= '<THEAD><TR>';
                foreach ($row as $key => $value) {
                    if ($i == 0) {
                        $content .= '<TH width="' . round($arrayLen[$key] * 8.7) . '" >' . strtoupper($key) . '</TH>' . $crlf;
                    } else {
                        $content .= '<TH width="' . round($arrayLen[$key] * 8.7) . '" >' . strtoupper($key) . '</TH>' . $crlf;
                    }
                    $i++;
                }
                $content .= '</TR></THEAD><TBODY>';
            }

            $i = 0;

            // output detail
            $eo = ($k % 2) ? ($css_even_row) : ($css_odd_row);
            $content .= '<TR ' . $eo . '>';
            foreach ($row as $key => $value) {

                // convert links
                if (substr(strtolower($value), 0, 7) == "http://" && !isset($fColArr[strtoupper($key)])) {  //dont convert links that are set in the column formatter.
                    $value = '<a href="' . $value . '">' . $value . '</a>';
                }

                //align numbers right
                $a = (is_numeric($value)) ? (' align="right"') : ('');

                //format column if setup
                if ($columnFormatString !== false && isset($fColArr[strtoupper($key)])) {
                    $fC = $fFunctArr[$fColArr[strtoupper($key)]];
                    $fC = str_replace('#', '"' . $value . '"', $fC);
                    $value = $value . $fC;
                    $result = @eval('$value = ' . $fC . ';');  //if fails no changed to value will occur - hide errors
                }
                $value = (trim($value) != '') ? ($value) : ('&nbsp;');

                if ($k == count($array) - 1) {
                    $content .= '<TD ' . $a . '>' . $value . '</TD>' . $crlf;
                } else {
                    $content .= '<TD ' . $a . '>' . $value . '</TD>' . $crlf;
                }
                $i++;
            }
            $content .= '</TR>';
            $rowCnt++;

        }

        $content .= '</TBODY></TABLE>' . $crlf;
        $content .= '</div><BR><BR><BR>' . $crlf;

        return $content;

    }


    function reportSQL_arrayToPLAINTEXT($array, $displayHeaderRow = true)
    {


        $content = '';
        $rowCnt = 0;


        //CALCULATE COLUMN WIDTHS.
        $n = 0;
        $arrayLen = array();

        foreach ($array as $k => $row) {
            foreach ($row as $key => $dataStr) {
                if ($n == 0) {
                    $arrayLen[$key] = (strlen($dataStr) < strlen(html_entity_decode($key))) ? strlen(html_entity_decode($key)) : strlen(html_entity_decode($dataStr));
                } else {
                    if ($arrayLen[$key] < strlen(html_entity_decode($dataStr))) {
                        $arrayLen[$key] = strlen(html_entity_decode($dataStr));
                    }
                }
            }
            $n++;
        }

        $totl = (count($arrayLen) - 1);
        foreach ($arrayLen as $v) {
            $totl += $v + 2;
        }

        foreach ($array as $row) {

            $i = 0;

            // output headers
            if ($rowCnt == 0) {
                $content .= '|' . str_pad('', $totl, '-') . "|\n";
                foreach ($row as $key => $value) {
                    if ($i == 0) $content .= '| ' . str_pad(strtoupper($key), $arrayLen[$key], ' ', STR_PAD_RIGHT) . ' |';
                    else $content .= ' ' . str_pad(strtoupper($key), $arrayLen[$key], ' ', STR_PAD_RIGHT) . ' |';
                    $i++;
                }
                $content .= "\n" . '|' . str_pad('', $totl, '-') . "|\n";
                $i = 0;
            }

            // output detail
            foreach ($row as $key => $value) {

                if ($i == 0) {
                    $content .= '| ' . str_pad(html_entity_decode($value), $arrayLen[$key], ' ', STR_PAD_RIGHT) . ' |';
                } else {
                    $content .= ' ' . str_pad(html_entity_decode($value), $arrayLen[$key], ' ', STR_PAD_RIGHT) . ' |';
                }

                $i++;
            }
            if ($rowCnt == count($array) - 1) {
                $content .= "\n" . '|' . str_pad('', $totl, '-') . "|\n";
            }
            $content .= "\n";
            $rowCnt++;

        }

        return $content;

    }


    // swap out each parameter.
    function reportSQL_setParamValues(&$sql, $mfR, &$database, $userId, $principalId, $principalCode, $paramsArr)
    {

        //DATE RANGES
        //Rebuild constant into actual date param for sql.
        foreach ($paramsArr as $key => $val) {

            //if an actual date ie: 2012-10-23 or a none date constant - nothing will happen to param!
            $paramsArr[$key] = GUICommonUtils::translateDateRangeValue($val);

        }

        preg_match_all("/[&][0-9]+/", $sql, $params);
        foreach ($params[0] as $val) {
            $pInt = substr($val, 1);
            if (is_numeric($pInt)) {
                if (isset($paramsArr['p' . $pInt])) $pVal = $paramsArr['p' . $pInt]; else $pVal = "";
                // check if required
                if ($this->reportSQL_isRequiredParameter($pInt, $mfR) && ($pVal == "")) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "A required parameter p{$pInt} was not supplied with a value!";
                    return false;
                }
                // if destination is an IN () statement, then wrap in quotes
                if (!(strpos($sql, "(&" . $pInt . ")") === false) && ($pVal != "") && ($pVal != "*")) {
                    $pVal = '"' . str_replace(",", '","', $pVal) . '"';
                }
                $sql = str_replace("&" . $pInt, $pVal, $sql);
            }
        }
        // swap out special values the userId, principalId, reportId
        $sql = str_replace("&userId", $userId, $sql);
        $sql = str_replace("&reportId", $mfR[0]['reports_uid'], $sql);
        $sql = str_replace("&principalId", $principalId, $sql);

        // swap out vars that are controlled by calling program (scheduler) and not the parameter screen - these @VARS used to be session vars
        // set by the calling program but they sometimes cause the sql to hand on the "sending data" phase
        if ((isset($GLOBALS["SCHEDULERUID"])) && ($GLOBALS["SCHEDULERUID"] != "")) $sql = str_replace("@SCHEDULERUID", $GLOBALS["SCHEDULERUID"], $sql);
        else $sql = str_replace("@SCHEDULERUID", "", $sql); // you need to do this as if the @var is in quotes then it will not resolve !

        return true;
    }

    public function reportSQL_prepareStatement(&$sql, $mfR, $database, $reportId, $userId, $principalId, $principalCode, $paramsArr)
    {
        // swap out each parameter.
        if (!$this->reportSQL_setParamValues($sql, $mfR, $database, $userId, $principalId, $principalCode, $paramsArr)) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = " An error occurred setting Param Values for report {$reportId}. " . $this->errorTO->description;
            return $this->errorTO;
        }

        $sql = preg_replace("/[ ]*[a-zA-Z0-9_.`]*[ ]*(IN|in)[ ]*[(][*][)]/", " 1=1 ", $sql); // get rid of empty "IN (*)" (wildcard because came from scheduler)
        $sql = preg_replace("/[ ]*[a-zA-Z0-9_.`]*[ ]*(IN|in)[ ]*[(][)]/", " 1=1 ", $sql); // get rid of empty "IN ()" (is empty because parameter not required)
        // swap out the constants...
        preg_match_all("/&CONSTANT_.*?&/", $sql, $constants);
        $constants = array_unique($constants[0]);
        foreach ($constants as $c) {
            $val = constant(substr($c, 10, strlen($c) - 10 - 1));
            $sql = preg_replace("/" . $c . "/", '"' . $val . '"', $sql);
        }
    }

    public function reportSQL_getReportSQL($reportId, $userId, $principalId, $principalCode, $paramsArr)
    {

        global $ROOT, $PHPFOLDER;
        include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');

        $database = "";

        // get report details (includes role check within sql
        $mfR = $this->getReportItemForUser($userId, $principalId, $reportId);
        if (sizeof($mfR) == 0) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Report not found or you do not have permissions to run this report (Interface:2,repId:{$reportId},prin:{$principalId},user:{$userId})!";
            return $this->errorTO;
        }

        $sql = $mfR[0]['sql'];
        $database = $mfR[0]['database'];

        $this->reportSQL_prepareStatement($sql, $mfR, $database, $reportId, $userId, $principalId, $principalCode, $paramsArr);

        //check run_as_secondary_sql?
        if (isset($mfR[0]['run_as_secondary_sql']) && $mfR[0]['run_as_secondary_sql'] == 'Y') {

            $result = $this->dbConn->dbQuery($sql);
            $row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_NUM);
            if ($this->dbConn->dbQueryResultRows != 1) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "An Error occurred generating Report in Secondary SQL";
                return $this->errorTO;
            }
            $sql = $row[0];
        }

        $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        $this->errorTO->description = "Successful";
        $this->errorTO->object = new ReportSQLTO;
        $this->errorTO->object->sql = $sql;
        $this->errorTO->object->fileName = $mfR[0]['report_name']; //make filename url friendly.
        $this->errorTO->object->database = $database;
        $this->errorTO->object->xmlSchema = $mfR[0]['xml_schema'];
        $this->errorTO->object->columnFormat = (isset($mfR[0]['column_format'])) ? $mfR[0]['column_format'] : '';
        $this->errorTO->object->reportLevel = $mfR[0]['report_level'];
        $this->errorTO->object->runOnceFieldName = $mfR[0]['run_once_fieldname'];
        $this->errorTO->object->hiddenColList = $mfR[0]['hidden_col_list'];
        $this->errorTO->object->totalBreakColList = $mfR[0]['total_break_col_list'];
        $this->errorTO->object->totalSumColList = $mfR[0]['total_sum_col_list'];
        $this->errorTO->object->pdfScriptPath = trim($mfR[0]['pdf_script_path']);
        $this->errorTO->object->reportRow = $mfR;

        return $this->errorTO;
    }


    // NB !!
    // If you use this to modify the original data set, then the hidden fields will no longer be in synch !
    // Rather on return put it in a separate result object to hold
    public function reportSQL_addBreakTotals($reportSQLTO, $data)
    {
        global $ROOT;
        global $PHPFOLDER;

        $errorTO = new ErrorTO(); // must make new as otherwise overwrite of calling object will occur

        // Add Control Break Total Lines if necessary
        if (($reportSQLTO->totalBreakColList != "") && (sizeof($data) > 0)) {

            $tbclArr = array_reverse(explode(",", $reportSQLTO->totalBreakColList));
            $tsclArr = explode(",", $reportSQLTO->totalSumColList);
            if (trim($reportSQLTO->totalSumColList) == "") {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "Error in reportSQL_addBreakTotals : You need to specify total_sum_col_list if break totals are used !";
                return $errorTO;
            }
            $totalFlds = array();
            $grandTotalFlds = array();

            $ctrlRow = $data[0];
            $tempArr = array();

            // initialise each field - required as it falls over if 2nd level doesnt exist
            foreach ($tbclArr as $breakFld) {
                $totalFlds[$breakFld] = array();
                foreach ($tsclArr as $sumFld) {
                    $totalFlds[$breakFld][$sumFld] = 0;
                    $grandTotalFlds[$sumFld] = 0;
                }
            }

            // each row
            foreach ($data as $row) {

                // check control fields
                foreach ($tbclArr as $breakFld) {

                    if ($row[$breakFld] != $ctrlRow[$breakFld]) {
                        // insert the total line
                        $i = 0;
                        $tLine = array();
                        foreach ($row as $key => $dFld) {
                            if ($i == 0) {
                                $tLine[$key] = "---SUBTOTAL ({$ctrlRow[$breakFld]})---"; // assumes that first column is NOT A SUM total !!!
                            } else if (in_array($key, $tsclArr)) {
                                $tLine[$key] = $totalFlds[$breakFld][$key];
                            } else {
                                $tLine[$key] = "";
                            }
                            $i++;
                        }
                        $tempArr[] = $tLine;

                        // zero the break field
                        foreach ($tsclArr as $sumFld) {
                            $totalFlds[$breakFld][$sumFld] = 0;
                        }

                    }

                }

                // sum each field
                $i = 0;
                foreach ($tbclArr as $breakFld) {
                    foreach ($tsclArr as $sumFld) {
                        $totalFlds[$breakFld][$sumFld] += $row[$sumFld];
                        if ($i == 0) $grandTotalFlds[$sumFld] += $row[$sumFld];
                    }
                    $i++;
                }

                $ctrlRow = $row;
                $tempArr[] = $row;

            }

            // add the last sub total line
            foreach ($tbclArr as $breakFld) {
                $i = 0;
                $tLine = array();
                foreach ($ctrlRow as $key => $dFld) {
                    if ($i == 0) {
                        $tLine[$key] = "---SUBTOTAL ({$ctrlRow[$breakFld]})---"; // assumes that first column is NOT A SUM total !!!
                    } else if (in_array($key, $tsclArr)) {
                        $tLine[$key] = $totalFlds[$breakFld][$key];
                    } else {
                        $tLine[$key] = "";
                    }
                    $i++;
                }
                $tempArr[] = $tLine;
            }

            // add the grand total line
            $i = 0;
            $tLine = array();
            foreach ($ctrlRow as $key => $dFld) {
                if ($i == 0) {
                    $tLine[$key] = "---TOTAL---"; // assumes that first column is NOT A SUM total !!!
                } else if (in_array($key, $tsclArr)) {
                    $tLine[$key] = $grandTotalFlds[$key];
                } else {
                    $tLine[$key] = "";
                }
                $i++;
            }
            $tempArr[] = $tLine;

        }

        $errorTO->type = FLAG_ERRORTO_SUCCESS;
        if (!isset($tempArr) || (sizeof($tempArr) == 0)) $errorTO->object = $data;
        else $errorTO->object = $tempArr;
        $errorTO->description = "Successful";

        return $errorTO;
    }

    /*
     * END : Report SQL Generation Procs
     */

    public function getAllReportsForUser($userId, $principalId)
    {
        $sql = "select a.uid, 
		             a.report_name, 
		             a.report_description, 
		             a.parameter_fields, 
		             a.required_fields, 
		             a.override_field_labels, 
		             a.initial_values, 
		             a.field_lengths,
		             a.sql, 
		             a.role_id, 
		             b.role_id, a
		             .database
			     from   reports a
           inner join system_reports r on a.uid = r.report_uid and r.system_uid = {$_SESSION['system_id']}
				   left join (select distinct user_id, entity_uid, role_id from user_role) b on b.user_id='" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "' 
				   and (b.entity_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "' or b.entity_uid is null) 
				   and  a.role_id=b.role_id
			where  ((a.role_id is null) or (a.role_id is not null and b.role_id is not null))
			and    a.user_enabled='Y'
			order by a.report_name";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    public function getReportItemForUser($userId, $principalId, $reportId)
    {

        //REPORT @ PRINCIPAL LEVEL - includes role checking
        //report_name, r.report_description taken from top level
        //role checking done from top level role_id
        $sql = "select
      			a.uid, r.report_name, r.report_description, a.parameter_fields, a.required_fields,
      			a.override_field_labels, a.initial_values, a.field_lengths, a.sql, r.role_id,
      			b.role_id, a.database, a.column_format, a.run_as_secondary_sql, a.xml_schema, a.csv_separator,
      			'Principal' as 'report_level', '2' as 'report_level_code', r.uid reports_uid, r.run_once_fieldname, r.hidden_col_list,
            r.total_break_col_list, r.total_sum_col_list, r.pdf_script_path, a.php_script, r.stopphpscript
      		from   principal_reports a
       		inner join reports r on a.report_uid = r.uid
      	    left join (select distinct user_id, entity_uid, role_id from user_role) b
      	    	on b.user_id = '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "' and
      			  (b.entity_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "' or b.entity_uid is null) and
      			   r.role_id=b.role_id
      		where  a.report_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $reportId) . "'
      			and a.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
      			and ((r.role_id is null) or (r.role_id is not null
      			and b.role_id is not null))";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        if (!count($arr) > 0) {

            //REPORT @ GLOBAL LEVEL - includes role checking
            $sql = "select a.uid, a.report_name, a.report_description, a.parameter_fields, a.required_fields, a.override_field_labels, a.initial_values, a.field_lengths,
    			 	 a.sql, a.role_id, b.role_id, a.database, a.column_format, a.run_as_secondary_sql, a.xml_schema, a.csv_separator,
    			 	 'Global' as 'report_level', '1' as 'report_level_code', a.uid reports_uid, a.run_once_fieldname, a.hidden_col_list,
             a.total_break_col_list, a.total_sum_col_list, a.pdf_script_path, a.php_script, a.stopphpscript
    		from   reports a
    			    left join (select distinct user_id, entity_uid, role_id from user_role) b
    			    	on b.user_id='" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "' and
    					  (b.entity_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "' or b.entity_uid is null) and
    					   a.role_id=b.role_id
    		where  uid = '" . mysqli_real_escape_string($this->dbConn->connection, $reportId) . "'
    		and    ((a.role_id is null) or (a.role_id is not null and b.role_id is not null))";

            $this->dbConn->dbQuery($sql);

            $arr = array();
            if ($this->dbConn->dbQueryResultRows > 0) {
                while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                    $arr[] = $row;
                }
            }
        }

        return $arr;
    }


    //*** DANGER DAO ***
    //USE getReportItemForUser instead.
    /*
    public function getReportItem($reportId) {
        $sql="select a.*
                from   reports a
                where  uid = '".mysqli_real_escape_string($this->dbConn->connection, $reportId)."'";

        $this->dbConn->dbQuery($sql);

        $arr=array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
                $arr[] = $row;
            }
        }

        return $arr;
    }
    */

}

?>

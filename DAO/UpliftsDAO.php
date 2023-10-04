<?php
//22/09/2023 jb
//this is a test program for familization, r&d and trying ideas purposes
//for select statements only
//uses paramerized queries
//returns either an individual value or mysqli_result

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
require_once($ROOT . $PHPFOLDER . "functional/main/access_control.php");
include_once($ROOT . $PHPFOLDER . "properties/dbSettings.inc");
include_once($ROOT . $PHPFOLDER . "libs/CommonUtils.php");

//include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . "properties/Constants.php");
//include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
//include_once($ROOT.$PHPFOLDER.'DAO/PostStockDAO.php');

class dbFunctions
{
    private $dbConn;
    function __construct()
    {
        //$this->dbConn = $dbConn;
        //$this->errorTO = new ErrorTO;
    }

    public function getTablevalue($sql, $ptypes, $pvalues, $fields)
    {
        $conn = new dbConnect();
        $conn->dbConnection();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $resultValue = "";
        //$resultRows = [];
        try
        {

            $str = $conn->getconnectionstring();
            $elements = explode('|', $str);
            $host = $elements[0];
            $username = $elements[1];
            $password = $elements[2];
            $dbname = 'kwelanga_dev';
            $conn = mysqli_connect($host, $username, $password, $dbname);

            if (!$conn)
            {
                die("Connection failed: " . mysqli_connect_error());
            }
            //if (mb_strlen($sql) == 0)
            //{
            //$sql = "SELECT " . $fields . " FROM $tablename WHERE " . $fields . "= ?";
            //}
            $stmt = $conn->prepare($sql);
            if (!$stmt)
            {
                die("Statement preparation failed: " . $conn->error);
            }
            if (mb_strlen($sql) == 0)
            {
                $stmt->bind_param("s", $values);
            }
            else
            {
                $params[] = $ptypes;
                foreach ($pvalues as $key => $value)
                {
                    $params[] = &$pvalues[$key];
                }
                call_user_func_array([$stmt, 'bind_param'], $params);
            }
            $stmt->execute();
            //$stmt->bind_param("s", $values);  // 's' indicates a string
            //if (!$stmt->execute())
            //{
            //die("Execution failed: " . $stmt->error);
            //}
            if (mb_strlen($fields) > 0)
            {
                $result = $stmt->get_result();
                if ($result->num_rows > 0)
                {
                    $row = $result->fetch_assoc();
                    if (isset($row[$fields]))
                    {
                        $resultValue = $row[$fields];
                        //echo "Value ".$resultValue;
                    }
                    else
                    {
                        $resultValue = "Error|field '{$fields}' not found in the result.";
                    }
                }
                else
                {
                    $resultValue = "Error|no records found";
                    //.$str."|".$sql."|".$values;
                }
            }
            else
            {
                //$resultRows = $stmt->get_result();
            }
        }

        catch (mysqli_sql_exception $e)
        {
            $resultValue = "Error|" . $e->getMessage();
        }
        //$stmt->close();
        if (mb_strlen($fields) > 0)
        {
            return $resultValue;
        }
        else
        {
            //echo $sql;
            return $stmt->get_result();
        }
    }
}

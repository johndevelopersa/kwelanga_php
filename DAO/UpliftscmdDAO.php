<?php
//22/09/2023 jb
//this is a test program for familization, r&d and trying ideas purposes
//for updates & inserts, can be multiple
//uses paramerized queries and transactions

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

class dbFunctions1
{
    private $dbConn;
    function __construct()
    {
        //$this->dbConn = $dbConn;
        //$this->errorTO = new ErrorTO;
    }

    public function cmdexecutetn($sql, $ptypes, $pvalues)
    {
        $result="";
        $conn = new dbConnect();
        $conn->dbConnection();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $resultValue = "";

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

        $conn->begin_transaction();
        try
        {
            for ($i = 0; $i < count($sql); $i++)
            {
                $stmt = $conn->prepare($sql[$i]);
                if (!$stmt)
                {
                    die("Statement preparation failed: " . $conn->error);
                }

                $params = [];
                $params[] = $ptypes[$i];
                foreach ($pvalues[$i] as $key => $value)
        {
            $params[] = &$pvalues[$i][$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $params);
                $stmt->execute();
                $stmt->close();
            }
            $conn->commit();
            $result="success";
        }
        catch (Exception $e)
        {
            $conn->rollback();
            $result="failed";
        }
        return $result;
    }
}

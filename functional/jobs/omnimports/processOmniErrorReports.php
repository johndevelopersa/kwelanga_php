<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/OmniExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

if (!isset($_SESSION)) session_start();

//totals
$statST = microtime(true);
$statJOBcnt = 0;

echo "<BR>Job Started: ".(CommonUtils::getGMTime(0))."<BR>";

set_time_limit(20*60); // keep at 20 mins as jobs cant be spaced closer than 15 mins to avoid overlapping runs

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO();

// $jobName = "OmniImportsNew";

if (isset($_POST["JOBNAME"])) $postJOBNAME=mysqli_real_escape_string($dbConn->connection, $_POST["JOBNAME"]); else if (isset($_GET["JOBNAME"])) $postJOBNAME=mysqli_real_escape_string($dbConn->connection, $_GET["JOBNAME"]);
if (isset($_POST["JOBID"]))   $postJOBID=mysqli_real_escape_string($dbConn->connection,$_POST["JOBID"]); else if (isset($_GET["JOBID"])) $postJOBID=mysqli_real_escape_string($dbConn->connection, $_GET["JOBID"]);

$jeOmniExtractDAO = new OmniExtractDAO($dbConn);
$mfJE = $jeOmniExtractDAO->getJobExecutionEntries($postJOBNAME, $postJOBID, "last_run ASC"); // must be own name because included script could overwrite it and this commit wont work then

echo $mfJE[0]['active_status'] . "<br>";

// print_r($mfJE);

echo "job uid:" . ($mfJE[0]['jeUid'] ?? "?") . "<br>";
echo "page_params:" . ($mfJE[0]['page_params'] ?? "?") . "<br>";
echo "principal_uid:" . ($mfJE[0]['principal_uid']??"?") . "<br>";
echo "uid_list:" . ($mfJE[0]['uid_list']??"?") . "<br>";
      

$principal_uid  = $mfJE[0]['principal_uid'];
$importStatus   = trim($mfJE[0]["page_params"]);

// echo $mfJE[0]["script_name"];
      
include_once($ROOT.$PHPFOLDER."functional/jobs/omnimports/{$mfJE[0]["script_name"]}.php");

$jeOmniExtractDAO = new OmniExtractDAO($dbConn);
$lkJe = $jeOmniExtractDAO->jeStatusFlag($mfJE[0]['jeUid'], "N");	
          
echo "<BR>Job Completed: ".(CommonUtils::getGMTime(0))."<BR>";

$statET = microtime(true);
$statTT = round($statET - $statST,4);
echo "[@>>>JOBS:".($statJOBcnt).";TT:".$statTT."@]<BR>";  //stat line.
echo '[***EOS***]';

?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/BIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostBIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

if (!isset($_SESSION)) session_start();

//totals
$statST = microtime(true);
$statJOBcnt = 0;


echo "<BR>Job Started: ".(CommonUtils::getGMTime(0))."<BR>";

set_time_limit(15*60); // keep at 15 mins as jobs cant be spaced closer than 15 mins to avoid overlapping runs

$dbConn = new dbConnect();
$dbConn->dbConnection();

$dbConnMain = new dbConnect(); // must have different dbConn name because DAO gets created with it and if it is redefined later in included script, the reference is no longer valid and cant commit
$dbConnMain->dbConnection();

$jobName = "dailyExtracts";

if (isset($_POST["JOBNAME"])) $postJOBNAME=mysqli_real_escape_string($dbConn->connection, $_POST["JOBNAME"]); else if (isset($_GET["JOBNAME"])) $postJOBNAME=mysqli_real_escape_string($dbConn->connection, $_GET["JOBNAME"]);
if (isset($_POST["JOBID"])) $postJOBID=mysqli_real_escape_string($dbConn->connection, $_POST["JOBID"]); else if (isset($_GET["JOBID"])) $postJOBID=mysqli_real_escape_string($dbConn->connection, $_GET["JOBID"]);

$postDistributionDAO = new PostDistributionDAO($dbConn);
$biDAO = new BIDAO($dbConn);
$postBIDAO = new PostBIDAO($dbConn);
$postExtractDAO = new PostExtractDAO($dbConn);
$extractDAO = new ExtractDAO($dbConn);
$miscDAO = new MiscellaneousDAO($dbConn);
$jEMiscellaneousDAO = new MiscellaneousDAO($dbConnMain);
$mfJE = $jEMiscellaneousDAO->getJobExecution($jobName, $postJOBID); // must be own name because included script could overwrite it and this commit wont work then

if ((isset($mfJE[0])) && ($mfJE[0]["script_name"]!="")) {
  foreach ($mfJE as $je) {
    include_once($ROOT.$PHPFOLDER."functional/extracts/daily/{$je["script_name"]}.php");

    // each processor must control its own rollbacks !
    $clTO=call_user_func(array($je["script_name"], "generateOutput"));
    if ($clTO->type!=FLAG_ERRORTO_SUCCESS) {
      $dbConn->dbinsQuery("rollback");

    } else {
      $dbConn->dbinsQuery("commit"); // commit any successfuly
      // only set last run date if successful !
      $jeResultTO=$jEMiscellaneousDAO->setJobExecution($jobName,$je["uid_list"]);
      if ($jeResultTO->type!=FLAG_ERRORTO_SUCCESS) echo "FAILED to setJobExecution in processExtractsBatch.php - notifications will run again repeatedly !";
      $dbConnMain->dbinsQuery("commit;");

    }
    $statJOBcnt++;
  }
}

echo "<BR>Job Completed: ".(CommonUtils::getGMTime(0))."<BR>";

$statET = microtime(true);
$statTT = round($statET - $statST,4);
echo "[@>>>JOBS:".($statJOBcnt).";TT:".$statTT."@]<BR>";  //stat line.
echo '[***EOS***]';

?>
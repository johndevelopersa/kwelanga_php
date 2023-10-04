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
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

if (!isset($_SESSION)) session_start();

//totals
$statST = microtime(true);
$statJOBcnt = 0;

echo "<BR>Job Started: ".(CommonUtils::getGMTime(0))."<BR>";

set_time_limit(20*60); // keep at 20 mins as jobs cant be spaced closer than 15 mins to avoid overlapping runs

$dbConn = new dbConnect();
$dbConn->dbConnection();

$dbConnMain = new dbConnect(); // must have different dbConn name because DAO gets created with it and if it is redefined later in included script, the reference is no longer valid and cant commit
$dbConnMain->dbConnection();

$jobName = "dailyUpdates";

if (isset($_POST["JOBNAME"])) $postJOBNAME=mysqli_real_escape_string($dbConn->connection, $_POST["JOBNAME"]); else if (isset($_GET["JOBNAME"])) $postJOBNAME=mysqli_real_escape_string($dbConn->connection, $_GET["JOBNAME"]);
if (isset($_POST["JOBID"]))   $postJOBID=mysqli_real_escape_string($dbConn->connection,$_POST["JOBID"]); else if (isset($_GET["JOBID"])) $postJOBID=mysqli_real_escape_string($dbConn->connection, $_GET["JOBID"]);

$miscDAO = new MiscellaneousDAO($dbConn);
$jEMiscellaneousDAO = new MiscellaneousDAO($dbConnMain);

$mfJE = $jEMiscellaneousDAO->getJobExecution($jobName, $postJOBID); // must be own name because included script could overwrite it and this commit wont work then


if ((isset($mfJE[0])) && ($mfJE[0]["script_name"]!="")) {
  foreach ($mfJE as $je) {
       $errorTO = new ErrorTO;
       if(trim(substr($mfJE[0]["page_params"],2,1)) =='' ){
           $updateBatch  = NULL; 	
       } else {
           $updateBatch  = substr($mfJE[0]["page_params"],2,1);
       }
       
       if(trim(substr($mfJE[0]["page_params"],0,1)) =='' ){
           $updateType  = 1; 	
       } else {
           $updateType   = substr($mfJE[0]["page_params"],0,1);
       }	   
  	   
  	   $principalUid = $mfJE[0]["principal_uid"];
  	     	
       include_once($ROOT.$PHPFOLDER."functional/payments/{$je["script_name"]}.php");
       
       if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
         // only set last run date if successful !
         $jeResultTO=$jEMiscellaneousDAO->setJobExecution($jobName,$je["uid_list"]);
         if ($jeResultTO->type!=FLAG_ERRORTO_SUCCESS) echo "FAILED to setJobExecution in DailyUpdates.php - notifications will run again repeatedly !";
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
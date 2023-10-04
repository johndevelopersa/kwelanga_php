<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

set_time_limit(15*60); // keep at 15 mins as jobs cant be spaced closer than 15 mins to avoid overlapping runs

if (!isset($_SESSION)) session_start();

echo "<BR>Job Started: ".(CommonUtils::getGMTime(0))."<BR>";

//Create new database object
$dbConnMain = new dbConnect(); // must have different dbConn name because DAO gets created with it and if it is redefined later in included script, the reference is no longer valid and cant commit

//Database connection method
$dbConnMain->dbConnection();

if (isset($_POST["JOBNAME"])) $postJOBNAME=mysqli_real_escape_string($dbConnMain->connection, ($_POST["JOBNAME"])); else if (isset($_GET["JOBNAME"])) $postJOBNAME=mysqli_real_escape_string($dbConnMain->connection, ($_GET["JOBNAME"]));

$jEMiscellaneousDAO = new MiscellaneousDAO($dbConnMain);
$mfJE = $jEMiscellaneousDAO->getJobExecution($postJOBNAME); // must be own name because included script could overwrite it and this commit wont work then

#var_dump($postJOBNAME, $mfJE);

if ((isset($mfJE[0])) && ($mfJE[0]["script_name"]!="")) {
  if ($mfJE[0]["page_params"]!="") {
    $params = explode(",",$mfJE[0]["page_params"]);
    foreach($params as $p) {
      $pair = explode("=",$p);
      $_GET[$pair[0]]=$pair[1];
    }
  }
  
	#var_dump($_GET);
	#var_dump($mfJE);
	echo "Running: {$mfJE[0]["script_name"]}\n<br>";
	
	include_once($ROOT.$PHPFOLDER.$mfJE[0]["script_name"]);

	$jeResultTO=$jEMiscellaneousDAO->setJobExecution($postJOBNAME);
	if ($jeResultTO->type!=FLAG_ERRORTO_SUCCESS) echo "FAILED to setJobExecution - notifications will run again repeatedly !";
	$dbConnMain->dbinsQuery("commit;");
}

echo "<BR>Job Completed: ".(CommonUtils::getGMTime(0))."<BR>";
?>



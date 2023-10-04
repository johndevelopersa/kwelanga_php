<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once ($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");	

if (!isset($_SESSION)) session_start();

//totals
$statST = microtime(true);
$statJOBcnt = 0;
echo "<BR>Job Started: ".(CommonUtils::getGMTime(0))."<BR>";

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO();

$dbConnMain = new dbConnect(); // must have different dbConn name because DAO gets created with it and if it is redefined later in included script, the reference is no longer valid and cant commit
$dbConnMain->dbConnection();

$jobName = "OmniImports";

$MaintenanceDAO = new MaintenanceDAO($dbConn);
$mfJE = $MaintenanceDAO->getJobExecutionEntry($principalId, $postDepot, $postChain);

if ((isset($mfJE[0])) && ($mfJE[0]["script_name"]!="")) {

       $errorTO = new ErrorTO;
       
       $whEnd = strpos($mfJE[0]["page_params"],"#");
       $chEnd = strpos($mfJE[0]["page_params"],"+");
       
       if(trim(substr($mfJE[0]["page_params"],0,$whEnd)) =='' ){
           $importWarehouse  = NULL; 	
       } else {
           $importWarehouse  = substr($mfJE[0]["page_params"],0,$whEnd);
       }
       
       if(trim(substr($mfJE[0]["page_params"],$whEnd+1,($chEnd-$whEnd-1))) =='' ){
           $importChain  = NULL; 	
       } else {
           $importChain   = substr($mfJE[0]["page_params"],$whEnd+1,($chEnd-$whEnd-1));
       }	   
       if(trim(substr($mfJE[0]["page_params"],$chEnd+1,50)) =='' ){
           $importStatus  = NULL; 	
       } else {
           $importStatus   = substr($mfJE[0]["page_params"],$chEnd+1,50);
       }
  	   $principalUid = $mfJE[0]["principal_uid"];

       include_once($ROOT.$PHPFOLDER."functional/jobs/omnimports/{$mfJE[0]["script_name"]}.php");
       
       if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {         
         echo "<BR>Successfully Completed Omni ORDERS SYNC\n";
       }
       $statJOBcnt++;
}

echo "<BR>Job Completed: ".(CommonUtils::getGMTime(0))."<BR>";

$statET = microtime(true);
$statTT = round($statET - $statST,4);
echo "[@>>>JOBS:".($statJOBcnt).";TT:".$statTT."@]<BR>";  //stat line.
echo '[***EOS***]';

?>
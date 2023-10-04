<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/reports/phpreports/voqadoKwelangaRecon.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/ReportsDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

global $paramsArr, $principalId, $outputType;

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

    $ReportsDAO = new ReportsDAO($dbConn);
    $pParm = $ReportsDAO->getprincipalParams($principalId);
    
    $fileseqnumber = $userId . date("is");
    $dirPath  = $ROOT . 'temp/';
    $fileName = 'temp'. $fileseqnumber . '.csv' ;   
    $tempfile = $dirPath . $fileName;

    include_once($ROOT.$PHPFOLDER.'functional/reports/api/voqadoFetchReportsNew.php');

    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->deleteReportsTempTable($fileseqnumber);
    
    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->createReportsTempTable($fileseqnumber);

    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->loadReportsTempTable(trim($dirPath), trim($fileName), $fileseqnumber);

    $totalTime = (microtime(true) - $startTime);
    $totalFileTime += $totalTime;
    
    $tsql = "SELECT *
             FROM .voqado_reports_temp_" . $fileseqnumber . "
             WHERE 1";
    $utresult = $dbConn->dbGetAll($tsql);
    
    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->deleteReportsTempTable($fileseqnumber);
    
    if(!strpos($paramsArr['p3'],'age')) {$repName = "_Statement_"; } else {$repName = "_Age_Analysis_";}
    
    $dFileName = $paramsArr['p1'] . $repName . date('Y-m-d') . "_" . $fileseqnumber . ".csv";
  
    if (count($utresult) == 0) { 
         file_put_contents($dirPath . $dFileName, 'No Rows Selected ...!');
    } else {
         foreach ($utresult as $brow) {
            $record = $brow['record'] . "\n";
            file_put_contents($dirPath . $dFileName, $record , FILE_APPEND);
         }
    }

    $manualRun = "N";
    if($manualRun == "N") {
       
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"". $dFileName."\"");
        header("Content-Type: application/force-download");
        readfile($dirPath . $dFileName);

     }

    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->deleteReportsTempTable($fileseqnumber);

    if(file_exists($tempfile)) {
        unlink($tempfile);
    }

    // EO Fetch
   echo "<br>";
   echo "<br>Kwelanga Report Extract Completed..[***EOS***]";
 ?>
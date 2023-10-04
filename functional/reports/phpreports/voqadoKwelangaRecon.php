<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/reports/phpreports/voqadoKwelangaRecon.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/ReportsDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

global $paramsArr, $principalId, $outputType;

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

$manualRun = "N";

if(count($paramsArr) == 0) {
      $StartDate = date('Y')  . '-' . date('m', strtotime("-1 Months")) . '-01'; 
      $EndDate   = date('Y')  . '-' . date('m') . '-' . date('d', strtotime("-1 Day"));
      $manualRun = "Y";
      
      echo $startDate;
} else {
    $StartDate   = $paramsArr['p1'];
    $EndDate     = $paramsArr['p2'];
}
if(date('d') == 01) {
    $previousMth =  date('m', strtotime("-1 Months"));
    
    if (date('m') == 01 ) {
         $lastyear = date('Y') -1;
         $StartDate = $lastyear . '-' . $previousMth . '-01';
         $EndDate   = $lastyear . '-' . $previousMth . '-31';
    } else {
        $StartDate = date('Y') . '-' . $previousMth . '-01';
        $EndDate   = date('Y') . '-' . $previousMth . '-' . date('t', strtotime("-1 Months")); 
    }
}
//  Create Sequence number for Voqado Output file

    $sequenceDAO = new SequenceDAO($dbConn);
    $sequenceTO = new SequenceTO;
    $errorTO = new ErrorTO;
    $sequenceTO->sequenceKey=LITERAL_SEQ_VOQADO;
    $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
    
    if ($result->type!=FLAG_ERRORTO_SUCCESS) {
       return $result;
    }
    $fileseqnumber  = $seqVal;
    
    $fileName = "Voqado" . str_pad($fileseqnumber,5,"0",STR_PAD_LEFT) . ".csv";
    
    $dirPath = 'C:/inetpub/wwwroot/systems/kwelanga_system/archives/voqado/';
    
    // Create Backup folders
    // Path and backup folder creation.
    @mkdir($dirPath, 0777, true);
    $bkupFolder = CommonUtils::createBkupDirs($dirPath, 1);
    $bkupFolder = CommonUtils::createDailyBackup($bkupFolder);  

    $errBkupFolder = CommonUtils::createBkupDirs($dirPath, 2);
    $errBkupFolder = CommonUtils::createDailyBackup($errBkupFolder);  
    
    // Fetch Voqado report goes here

    include_once($ROOT.$PHPFOLDER.'functional/reports/api/voqadoFetchReports.php');

    // EO Fetch
 
    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->deleteVoqadoTempTable($fileseqnumber);
    
    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->createVoqadoTempTable($fileseqnumber);

    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->loadVoqadoTempTable(trim($dirPath), trim($fileName), $fileseqnumber);

    $fsuccess = rename ( $dirPath . $fileName  , $bkupFolder . '/' . $fileName );

    $ReportsDAO = new ReportsDAO($dbConn);
    $result = $ReportsDAO->voqadoReconReport($StartDate, $EndDate, "voqado_temp_" . $fileseqnumber );
 
    $totalTime = (microtime(true) - $startTime);
    $totalFileTime += $totalTime;
    
//    $ReportsDAO = new ReportsDAO($dbConn);
//    $errorTO = $ReportsDAO->deleteVoqadoTempTable($fileseqnumber);

    
    if (count($result) == 0) { 
    	  $csv_export.= 'No Rows Selected!..';
    } else {
        foreach (array_keys($result[0]) as $arow) {
           $csv_export.= $arow . ',';
        }    	
    }
    $csv_export.= "\n";

    foreach ($result as $brow) {
         $csv_export.= implode(',',$brow) . "\n";
    }
    
    if($manualRun == "N") {
        $fileName = "Voqado - Kwelanga Recon Report " . date('Y-m-d') . ".csv";
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"".$fileName."\"");
        header("Content-Type: application/force-download");
        echo $csv_export;
    } else {
       file_put_contents($ROOT. '/archives/emaildocs/' . $fileName, $csv_export);
       

       // Hard coded for this staff report
       
       $emailAddresses = array('alan@kwelangasolutions.co.za', 'graeme@kwelangasolutions.co.za', 'melany@kwelangasolutions.co.za');
       
       foreach($emailAddresses as $erow) {
             $ReportsDAO = new ReportsDAO($dbConn);
             $result = $ReportsDAO->SetUpReportDistribution("Voqado - Kwelanga Recon Report " . date('Y-m-d'), 'archives/emaildocs/' . $fileName, $erow );
             echo "<br>";
             echo "Mail sent to - " . $result;
       }
    }
    
    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->deleteVoqadoTempTable($fileseqnumber);
    
   echo "<br>Voqado Kwelanga Recon Completed..[***EOS***]";
 ?>
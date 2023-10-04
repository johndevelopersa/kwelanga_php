<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/ReportsDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

global $paramsArr, $principalId, $outputType;

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

$manualRun = "N";;

if(count($paramsArr) == 0) {
      $StartDate = date('Y')  . '-' . date('m', strtotime("-1 Months")) . '-01'; 
      $EndDate   = date('Y')  . '-' . date('m') . '-' . date('d', strtotime("-1 Day"));
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

     $whIdArr = explode(",",$paramsArr['p3']);     
     $whId     = $paramsArr['p5'];
     $noTrans  = $paramsArr['p4'];
     
     $sequenceDAO = new SequenceDAO($dbConn);
     $sequenceTO = new SequenceTO;
     $errorTO = new ErrorTO;
     $sequenceTO->sequenceKey=LITERAL_SEQ_REPORTS;
     $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
    
     if ($result->type!=FLAG_ERRORTO_SUCCESS) {
        return $result;
     }
    $fileseqnumber  = $seqVal;
 
    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->deleteOwnerReportTempTable($fileseqnumber-1);
    
    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->createOwnerReportTempTable($fileseqnumber);
    
    if(count($whIdArr) > 0 ) {
        foreach($whIdArr as $row) {
            $ownStr = substr($row,strpos($row, '-') +1, strlen($row));
            $ReportsDAO = new ReportsDAO($dbConn);
            $errorTO = $ReportsDAO->insertTransporterMTDSales($fileseqnumber, $whId, $StartDate, $EndDate, $ownStr);        
        } 

        if($noTrans == 'Yes' ) {
         $ReportsDAO = new ReportsDAO($dbConn);
         $errorTO = $ReportsDAO->insertTransporterMTDSales($fileseqnumber, $whId, $StartDate, $EndDate, "");    	
        }	
    }
    
    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->insertTransporterMTDCredits($fileseqnumber, $whId, $StartDate, $EndDate, $ownStr);

    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->insertOwnerTotals($fileseqnumber, "O");

    $ReportsDAO = new ReportsDAO($dbConn);
    $errorTO = $ReportsDAO->insertOwnerTotals($fileseqnumber, "G");

    $ReportsDAO = new ReportsDAO($dbConn);
    $result = $ReportsDAO->extractReport($fileseqnumber)  ;
    
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
    
//    if($manualRun == "N") {
        $fileName = "transport Owner Report" . date('Y-m-d') . ".csv";
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"".$fileName."\"");
        header("Content-Type: application/force-download");
        echo $csv_export;
//    }
      
    
    
    echo "End";




/*    
 else {
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

//    $ReportsDAO = new ReportsDAO($dbConn);
//    $errorTO = $ReportsDAO->deleteReportTempTable($fileseqnumber);

    
   echo "<br>Voqado Kwelanga Recon Completed..[***EOS***]";
 
 */
 ?>
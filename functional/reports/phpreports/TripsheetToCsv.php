<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/TripSheetDAO.php");
include_once $ROOT . $PHPFOLDER . 'libs/storage/Storage.php';


set_time_limit(15*60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

//setup S3 storage class.
new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);


if (!isset($_SESSION)) session_start() ;
$depotId = $_SESSION['depot_id'] ;
$userUId = $_SESSION['user_id'] ;

// $tripNo = ((isset($_GET["TRIPNO"]))?$_GET["TRIPNO"]:"");
echo "PP";
$dbConn = new dbConnect();
$dbConn->dbConnection();
 
 
$TripSheetDAO = new TripSheetDAO($dbConn);
$loadd = $TripSheetDAO->getLoadSheetDetails($depotId, $tripNo, "dm.principal_uid, dm.document_number");

// Create tripsheet file name

$tfileName = 'archives/emaildocs/' . date("Y") . "/" . date("m") . '/tripsheet_' . $tripNo . '.csv';
$tfileData = "";

$hString = '';
$fstDetail = '';

// print_r($loadd);
 
foreach($loadd as $row) {
      $dString = '';
      If($hString == '') {
      	      
      	      $hString = "Trip Sheet \r\n";
      	      $hString = $hString . $row['Warehouse'] . ",DATE," . $row['tripsheet_date'] . "\r\n";
      	      $hString = $hString . 'Transporter,' . $row['Transporter'] . "\r\n";
      	      $hString = $hString . 'Principal,Document No,Delivery Point,Cases,Exclusive Value,Comment' ."\r\n";
      	      
      	      $tfileData .= $hString;
      }
      if($fstDetail <> $row['document_number']) {
                $dString = $dString . $row['Principal']       . ',' . 
                                      $row['document_number'] . ',' . 
                                      $row['deliver_name']    . ',' . 
                                      $row['cases']           . ',' .
                                      round($row['exclusive_total'],2) . ',' .
                                      ''  . "\r\n";
				$tfileData .= $dString;
                $fstDetail = $row['document_number'];
      } 

}

$tfileData .= " "   . "\r\n";
$tfileData .= "End of Report"   . "\r\n";

$storageUploadResult = Storage::putObject(S3_BUCKET_NAME, $tfileName, $tfileData);
if(!$storageUploadResult){
	echo "storage error: " . $storageUploadResult . "\n";
	return false;
}

header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=\"". basename($tfileName) ."\"");
header("Content-Type: application/force-download");
ob_clean();
flush();
echo $tfileData;
die();

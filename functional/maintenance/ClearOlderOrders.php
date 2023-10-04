<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/maintenance/ClearOlderOrders.php?PRINCIPALID=390&DEPOTARR=392,393,396,397,400,401&DAYINT=14


$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

$principalId = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:"");
$depotArray  = ((isset($_GET["DEPOTARR"]))?$_GET["DEPOTARR"]:"");
$daysInt     = ((isset($_GET["DAYINT"]))?$_GET["DAYINT"]:"");

$MaintenanceDAO = new MaintenanceDAO($dbConn);     
$cList = $MaintenanceDAO->getUidsToCancel($principalId, $depotArray, $daysInt);

// print_r($cList);

$cc = 0;

$canBomb = 'N';

echo count($cList);
echo "<br>";

if(count($cList) > 0 ) {
	
     foreach($cList as $uRow) {
          $MaintenanceDAO = new MaintenanceDAO($dbConn);     
          $errorTO = $MaintenanceDAO->cancelSelectedOrders($uRow['uid']);
       
          //print_r($errorTO);

          if($errorTO->type == FLAG_ERRORTO_SUCCESS) {
               $canBomb = 'N';
               $cc++;
          } else {
              echo "Error - Order cancel Failed";	
              $canBomb = 'Y';
              break;
          }
      }	
      if($canBomb == 'N') {
          $smsMessage = "Nellwyn weekly order cleanup completed Successfully " .
          Date('Y-m-d h:i:s A') . "    " .
          $cc . " Orders Cancelled";	
      } else {
          $smsMessage = "Nellwyn weekly order cleanup Failed
          Action Required
          " . Date('Y-m-d h:i:s A');
      }	
} else {
         $smsMessage = "Nellwyn weekly order cleanup No Orders to cancel " .
         Date('Y-m-d h:i:s A') . "    ";
}

$smsNumber = '+27832920417';

include_once($ROOT.$PHPFOLDER.'functional/ws/bulk_sms/bulkSms.php');


echo "<br>Job Completed: " . Date('Y-m-d h:i:s A') . "<br><br>[***EOS***]";

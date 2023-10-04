<?php
// http://www.kwelangasolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/reports/phpreports/DetailedCashSummaryReport.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/ReportsDAO.php");

global $paramsArr, $principalId, $outputType;

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();
//  0123456789012345678901234567890
//  2018022018-04-012018-04-30

$EndDate     = $paramsArr['p2'];
$CustomerUid = trim(substr($paramsArr['p3'],1,10));
$PaymentBy   = substr($paramsArr['p3'],0,1);

if(substr($paramsArr['p2'],8,2) <= 10) {
     $StartDate   = (substr($paramsArr['p2'],0,4)) . "-" . str_pad(substr($paramsArr['p2'],5,2)-1,2,"0",STR_PAD_LEFT) . "-01";	
} else {
	   $StartDate   = substr($paramsArr['p2'],0,7) . '-01';	
}

//   $StartDate = '2020-09-01';

// Create temporary table user specfic

$ClosingBal = $CashOut = $CashIn = 0;

$bldsql = "DROP TABLE IF EXISTS temp_ledger_" . $userId . ";   ";

$dtresult = $dbConn->dbQuery($bldsql);
$dbConn->dbQuery("commit");

$bldsql = "CREATE TABLE temp_ledger_" . $userId . " (`Field1` VARCHAR(60) NULL,
                                     `Field2` VARCHAR(60) NULL,
                                     `Field3` VARCHAR(60) NULL,
                                     `Field4` VARCHAR(60) NULL,
                                     `Field5` VARCHAR(60) NULL,
                                     `Field6` VARCHAR(60) NULL,
                                     `Field7` VARCHAR(60) NULL,
                                     `Sort` TINYINT(1) NULL);";

$dtresult = $dbConn->dbQuery($bldsql);
$dbConn->dbQuery("commit");

$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getPrincipalName($principalId);

foreach($gSBP as $row1) {
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field6`, `Sort` ) VALUES ('" .$row1['Principal'] ."', ' ". date("Y-m-d H:i") ."', '1');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
}
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '3');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
     
     if($PaymentBy==PAYMENT_BY_GROUP) {
         $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Field4`, `Field5`, `Field6`, `Field7`, `Sort` ) VALUES ('Name', 'Date','Document Number', 'Reference 1 ', 'Reference 2','Debit','Credit', '4');";
     } else {
         $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Field4`, `Field5`, `Field6`, `Sort` ) VALUES ('Date', 'Type','Reference ','Source Reference ','Debit','Credit', '4');";
     }    
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '5');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getCustomerName($CustomerUid, $PaymentBy);

foreach($gSBP as $row1) {
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Sort` ) VALUES (' ". $row1['Customer'] . "', '6');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
}
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '6.1');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
     
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field5`, `Sort`) VALUES ('Statement as at ','" . $EndDate . "','', '6.2');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '7');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getCustomerOpeningBalance($CustomerUid, $PaymentBy, $StartDate);

foreach($gSBP as $row1) {
	
	   if($row1['Total'] <= 0) {
           $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field6`,`Sort`) VALUES ('Opening Balance as at ','" . $StartDate . "','". number_format($row1['Total'],2,'.', ' ') . "', '8');";
           $CashOut    = $CashOut    - $row1['Total'];  
	   } else {
           $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field7`,`Sort`) VALUES ('Opening Balance as at ','" . $StartDate . "','". number_format($row1['Total'],2,'.', ' ') . "', '8');";
           $CashIn     = $CashIn     + $row1['Total'];  
	   }
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

}

$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getCashOutDocuments($principalId, $CustomerUid, $StartDate, $EndDate, $PaymentBy);

foreach($gSBP as $row1) {
      $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, 
                                                          `Field2`, 
                                                          `Field3`, 
                                                          `Field4`,
                                                          `Field5`, 
                                                          `Field6`,
                                                          `Field7`,  
                                                          `Sort` ) 
                                                          
                VALUES ('". $row1['deliver_name']     . "',
                        '". $row1['invoice_date'] . "', 
                        '". $row1['document_number']    . "',
                        '". trim($row1['delivery_instructions']) ."',
                        '". trim($row1['customer_order_number']) . "',
                        '". round($row1['invoice_total'],2) . "',
                        '',
                        '9');";
      $itresult = $dbConn->dbQuery($isql);
      $dbConn->dbQuery("commit");
      
      $CashOut    = $CashOut    + $row1['invoice_total'];
}
//  ***************************************************************
$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getSummaryPaymentDocuments($principalId, $StartDate, $EndDate);

foreach($gSBP as $row1) {
      $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, 
                                                          `Field2`, 
                                                          `Field3`, 
                                                          `Field4`, 
                                                          `Field5`, 
                                                          `Field7`, 
                                                          `Sort` ) 
               VALUES ('". $row1['deliver_name']    . "',
                       '". $row1['payment_date']     . "',
                       '". $row1['payment_number'] . "',
                       '',
                       '',
                       '". abs(round($row1['payment_amount'],2)) . "',
                       '9');";

         $itresult = $dbConn->dbQuery($isql);
         $dbConn->dbQuery("commit");
         $CashIn     = $CashIn     - $row1['payment_amount'];
}
//  ************************************************************
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '10');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '11');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field6`, `Field7`, `Sort` ) VALUES ('Totals'," . $CashOut. "," . $CashIn . ", '12');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '13');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $ClosingBal = $CashIn-$CashOut;

	   if($ClosingBal <= 0) {  
         $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field6`, `Sort`) VALUES ('Closing Balance ',' ". round(0-$ClosingBal,2) . "', '14');";
	   } else {
         $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field7`, `Sort`) VALUES ('Closing Balance ',' ". round($ClosingBal,2) . "', '14');";
     }
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '15');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

      $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '16');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '17');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");     

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES ('**End** ', ' ', ' ', '18');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");     
// Temp Table SQL

$csv_export = '';

$tsql = "select `Field1`,
                `Field2`,
                `Field3`,
                `Field4`,
                `Field5`,
                `Field6`,
                `Field7`
         from temp_ledger_" . $userId . " where 1
         order by sort, Field2;";
$utresult = $dbConn->dbGetAll($tsql);

// print_r($utresult);

if (count($utresult) == 0) {
	   ?>
     <script type='text/javascript' >parent.showMsgBoxError("No Rows Selected<BR><BR>")</script> 
	   <?php
	   return;	
}


foreach (array_keys($utresult[0]) as $arow) {
  if (substr($arow,0,5) <> 'Field' && substr($arow,0,4) <> 'sort' ) {
     $csv_export.= $arow . ',';
  }

}
  $csv_export.= "\n";

foreach ($utresult as $brow) {
	$csv_export.= implode(',',$brow) . "\n";
}
$fileName = "Detailed Ledger.csv";

  header("Content-Description: File Transfer");
  header("Content-Disposition: attachment; filename=\"".$fileName."\"");
  header("Content-Type: application/force-download");
  echo $csv_export;
  


?>
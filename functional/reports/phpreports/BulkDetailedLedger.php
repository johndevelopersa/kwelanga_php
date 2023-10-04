<?php
// http://www.kwelangasolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/reports/phpreports/BulkDetailedLedger.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/ReportsDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PaymentsDAO.php");
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

global $paramsArr, $principalId, $outputType;

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();
//  0123456789012345678901234567890
//  2018022018-04-012018-04-30

if (isset($paramsArr['p1']) && isset($paramsArr['p2'])) {
	    if(strlen(trim($paramsArr['p2'])) == 10) {
         $EndDate       = $paramsArr['p2'];
	    } else {
	       $EndDate       = substr($paramsArr['p2'],16,10);
	    }
	    $StartDate     = substr($paramsArr['p1'],6,10); 
	    
}	else {
	
			echo 'Oh Shit';
	 
}
// Create temporary table user specfic

$ClosingBal = 0;

$bldsql = "DROP TABLE IF EXISTS temp_ledger_bulk_" . $userId . ";   ";

$dtresult = $dbConn->dbQuery($bldsql);
$dbConn->dbQuery("commit");

$bldsql = "CREATE TABLE temp_ledger_bulk_" . $userId . " (`Field1` VARCHAR(60) NULL,
                                     `Field2`  VARCHAR(60) NULL,
                                     `Field3`  VARCHAR(60) NULL,
                                     `Field4`  VARCHAR(60) NULL,
                                     `Field5`  VARCHAR(60) NULL,
                                     `Field6`  VARCHAR(60) NULL,
                                     `Field7`  VARCHAR(60) NULL,
                                     `Field8`  VARCHAR(3) NULL,
                                     `Field11` VARCHAR(60) NULL,
                                     `Field12` VARCHAR(60) NULL,
                                     `Sort` TINYINT(1) NULL);";

$dtresult = $dbConn->dbQuery($bldsql);
$dbConn->dbQuery("commit");

foreach($custlist as $key => $crow) {
	
	//*************************************************************************************************************************
	
      $ReportsDAO = new ReportsDAO($dbConn);
      $gSBP = $ReportsDAO->getPrincipalName($principalId);

      $ReportsDAO = new ReportsDAO($dbConn);
      $gCust = $ReportsDAO->getCustomerName($crow, "1");
      
      foreach($gCust as $row1) {
            $custName = trim($row1['Customer']);
      }
      
      foreach($gSBP as $row1) {
	
              $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field6`, `Field8`, `Sort`, `Field12` ) VALUES ('STATEMENT', ''," . $principalId . ", '1', " . $custName ." );";
              $itresult = $dbConn->dbQuery($isql);
              $dbConn->dbQuery("commit");	

              $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field8`, `Sort`, `Field12` ) VALUES (' ', ' ', ' ', " . $principalId . ", '2', " . $custName ." );";
              $itresult = $dbConn->dbQuery($isql);
              $dbConn->dbQuery("commit");
	
              $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field6`, `Field8`, `Sort`, `Field12` ) VALUES ('" .$row1['Principal'] ."', ' ". date("d-m-Y H:i") ."', " . $principalId . ", '3', " . $custName ." );";
              $itresult = $dbConn->dbQuery($isql);
              $dbConn->dbQuery("commit");
      }
      $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field8`, `Sort`, `Field12` ) VALUES (' ', ' ', ' ', " . $principalId . ", '4'," . $custName ." );";
      $itresult = $dbConn->dbQuery($isql);
      $dbConn->dbQuery("commit");
     
      $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field4`, `Field5`, `Field6`, `Field8`, `Sort`, `Field12` ) VALUES ('Date', 'Type','Ref. ','Source Ref. ','Debit','Credit', " . $principalId . ", '8'" . $custName ." );";
    
      $itresult = $dbConn->dbQuery($isql);
      $dbConn->dbQuery("commit");

      foreach($gCust as $row1) {
              $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field8`, `Sort`, `Field12` ) VALUES (' ". $row1['Customer'] . "', " . $principalId . ", '4' " . $custName ." );";
              $itresult = $dbConn->dbQuery($isql);
              $dbConn->dbQuery("commit");
              $custName = trim($row1['Customer']);
      }
      $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field8`, `Sort`, `Field12` ) VALUES (' ', ' ', ' ', " . $principalId . ", '5' " . $custName ." );";
      $itresult = $dbConn->dbQuery($isql);
      $dbConn->dbQuery("commit");
     
      $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field5`, `Field8`, `Sort`) VALUES ('Statement as at ','" . substr($EndDate,8,2) . '-' . substr($EndDate,5,2). '-' . substr($EndDate,0,4)  . "','', " . $principalId . ", '6' " . $custName ." );";
      $itresult = $dbConn->dbQuery($isql);
      $dbConn->dbQuery("commit");

      $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field8`, `Sort`, `Field12` ) VALUES (' ', ' ', ' ', " . $principalId . ", '7' " . $custName ." );";
      $itresult = $dbConn->dbQuery($isql);
      $dbConn->dbQuery("commit");

      $ReportsDAO = new ReportsDAO($dbConn);
      $gSBP = $ReportsDAO->getCustomerOpeningBalance($crow, "1", $StartDate);

      foreach($gSBP as $row1) {
	
              if($row1['Total'] <= 0) {
                   $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field6`, `Field8`, `Sort`) VALUES ('Opening Balance as at ','" . substr($StartDate,8,2) . '-' . substr($StartDate,5,2). '-' . substr($StartDate,0,4)  . "','". ($row1['Total']) . "', " . $principalId . ", '9' " . $custName ." );";
              } else {
                   $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field5`, `Field8`, `Sort`) VALUES ('Opening Balance as at ','" . substr($StartDate,8,2) . '-' . substr($StartDate,5,2). '-' . substr($StartDate,0,4) . "','". $row1['Total'] . "', " . $principalId . ", '9' " . $custName ." );";
	            }
              $itresult = $dbConn->dbQuery($isql);
              $dbConn->dbQuery("commit");
     
              $ClosingBal = $ClosingBal + $row1['Total'];
      }

      $ReportsDAO = new ReportsDAO($dbConn);
      $gSBP = $ReportsDAO->getCustomerSalesDocuments($principalId, $CustomerUid, $StartDate, $EndDate, $PaymentBy);

      foreach($gSBP as $row1) {
	   	     $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, 
                                                               `Field2`, 
                                                               `Field3`, 
                                                               `Field4`, 
                                                               `Field6`,
                                                               `Field5`,
                                                               `Field8`,   
                                                               `Sort`, `Field12` ) 
                    VALUES ('". substr($row1['invoice_date'],0,4) . '-' . substr($row1['invoice_date'],5,2). '-' . substr($row1['invoice_date'],8,2)    . "',
                            '". $row1['description']     . "',
                            '". substr($row1['document_number'],2,6) . "',
                            if('". $row1['document_type_uid'] . "' = '4', '" . $row1['source_document_number'] ."' ,''),
                            if('" .$row1['document_type_uid'] . "' in ('4','31')     , '". abs(round($row1['invoice_total'],2)) . "',''),
                            if('" .$row1['document_type_uid'] . "' not in ('4','31') , '". abs(round($row1['invoice_total'],2)) . "',''),
                            " . $principalId . ", 
                            '10' " . $custName ." );";     	
          $itresult = $dbConn->dbQuery($isql);
          $dbConn->dbQuery("commit");
     
          $ClosingBal = $ClosingBal + $row1['invoice_total'];
      }
//  ***************************************************************
      $ReportsDAO = new ReportsDAO($dbConn);
      $gSBP = $ReportsDAO->getCustomerPaymentDocuments($principalId, $CustomerUid, $StartDate, $EndDate);

       foreach($gSBP as $row1) {
              $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, 
                                                                  `Field2`, 
                                                                  `Field3`, 
                                                                  `Field4`, 
                                                                  `Field6`,
                                                                  `Field8`,
                                                                  `Sort`, `Field12` ) 
                       VALUES ('". substr($row1['payment_date'],0,4) . '-' . substr($row1['payment_date'],5,2). '-' . substr($row1['payment_date'],8,2)    . "',
                               '". $row1['pay_type']     . "',
                               '". $row1['payment_number'] . "',
                               '" .substr($row1['document_number'],2,6) ."',
                               '". abs(round($row1['payment_amount'],2)) . "',
                               " . $principalId . ", 
                               '10' " . $custName ." );";	       	
              $itresult = $dbConn->dbQuery($isql);
              $dbConn->dbQuery("commit");
     
              $ClosingBal = $ClosingBal + $row1['payment_amount'];
       }
//  ************************************************************
       $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field8`, `Sort`, `Field12` ) VALUES (' ', ' ', ' ', " . $principalId . ", '11' " . $custName ." );";
       $itresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");
	     if($ClosingBal <= 0) {  
           $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field6`, `Field8`, `Sort`) VALUES ('Closing Balance ',' ". round(0-$ClosingBal,2) . "', " . $principalId . ", '12' " . $custName ." );";
	     } else {
           $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field5`, `Field8`, `Sort`) VALUES ('Total Due ','". round($ClosingBal,2) . "', " . $principalId . ", '12' " . $custName ." );";
       }
       $itresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");

       $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field8`, `Sort`, `Field12` ) VALUES (' ', ' ', ' ', " . $principalId . ", '13' " . $custName ." );";
       $itresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");

       $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field8`, `Sort`, `Field12` ) VALUES ('Age Analysis', ' ', ' ', " . $principalId . ", '14' " . $custName ." );";
       $itresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");

       $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field8`, `Sort`, `Field12` ) VALUES (' ', ' ', ' ', " . $principalId . ", '15' " . $custName ." );";
       $itresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");
     
       $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field4`, `Field5`, `Field6`, `Field8`, `Sort`, `Field12` ) VALUES ('Over90 Days', '90 Days', '60 Days', '30 Days', 'Current', 'Total Due'," . $principalId . ", '16' " . $custName ." );";
       $itresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");     

       $ReportsDAO = new ReportsDAO($dbConn);
       
       $gSAG = $ReportsDAO->getCustomerAgeing($CustomerUid, $PaymentBy, date("Y-m-d", strtotime($EndDate)))  ;

       if(count($gSAG) > 0) {

             foreach($gSAG as $row1) {

                       $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, 
                                                                           `Field2`, 
                                                                           `Field3`, 
                                                                           `Field4`, 
                                                                           `Field5`, 
                                                                           `Field6`,
                                                                           `Field8`,  
                                                                           `Sort`, `Field12` ) 
                                VALUES ('". $row1['Over90 Days']    . "', 
                                        '". $row1['90 Days']        . "',
                                        '". $row1['60 Days']        . "',
                                        '". $row1['30 Days']        . "',
                                        '". $row1['Current']        . "',
                                        '". $row1['Total Due']      . "',
                                        " . $principalId . ", 
                                        '17' " . $custName ." );";
                     $dbConn->dbQuery("commit");     
             }
       }  else {
                    $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field6`, 
                                                              `Field8`, 
                                                              `Sort`, `Field12` ) 
                             VALUES ('". round($ClosingBal,2)      . "',
                                     " . $principalId . ", 
                                         '17' " . $custName ." );";
 
       }     
       $itresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");     	

       $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field8`, `Sort`, `Field12` ) VALUES (' ', ' ', ' ', " . $principalId . ", '18' " . $custName ." );";
       $itresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");

       $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field8`, `Sort`, `Field12` ) VALUES (' ', ' ', ' ', " . $principalId . ", '19' " . $custName ." );";
       $itresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");     

       $isql = "INSERT INTO `temp_ledger_bulk_". $userId . "` (`Field1`, `Field2`, `Field3`, `Field8`, `Sort`, `Field12` ) VALUES ('**End** ', ' ', ' ', " . $principalId . ", '20' " . $custName ." );";
       $itresult = $dbConn->dbQuery($isql);
       $dbConn->dbQuery("commit");     
	
       echo ($crow);
       echo "<br>";
}






?>
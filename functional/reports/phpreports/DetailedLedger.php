<?php
// http://www.kwelangasolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/reports/phpreports/DetailedLedger.php

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

if (isset($paramsArr['p4'])) {
      $StartDate     = substr($paramsArr['p4'],4,10);    	
      $EndDate       = substr($paramsArr['p4'],14,10);   	
} elseif (isset($paramsArr['p1']) && isset($paramsArr['p2'])) {
	    if(strlen(trim($paramsArr['p2'])) == 10) {
         $EndDate       = $paramsArr['p2'];
	    } else {
	       $EndDate       = substr($paramsArr['p2'],16,10);
	    }
	    $StartDate     = substr($paramsArr['p1'],6,10); 
	    
}	

$CustomerUid   = trim(substr($paramsArr['p3'],1,10));
$PaymentBy     = substr($paramsArr['p3'],0,1);

// Create temporary table user specfic

$ClosingBal = 0;

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
	
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field6`, `Sort` ) VALUES ('STATEMENT', '', '1');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");	

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '2');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
	
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field6`, `Sort` ) VALUES ('" .$row1['Principal'] ."', ' ". date("Y-m-d H:i") ."', '3');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
}
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '4');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
     
     if($PaymentBy==PAYMENT_BY_GROUP) {
         $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Field4`, `Field5`, `Field6`, `Field7`, `Sort` ) VALUES ('Date', 'Type','Ref. ','Name' ,'Source Ref. ','Debit','Credit', '8');";
     } else {
         $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Field4`, `Field5`, `Field6`, `Sort` ) VALUES ('Date', 'Type','Ref. ','Source Ref. ','Debit','Credit', '8');";
     }    
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getCustomerName($CustomerUid, $PaymentBy);

foreach($gSBP as $row1) {
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Sort` ) VALUES (' ". $row1['Customer'] . "', '4');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
     $custName = trim($row1['Customer']);
}
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '5');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
     
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field5`, `Sort`) VALUES ('Statement as at ','" . $EndDate . "','', '6');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '7');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getCustomerOpeningBalance($CustomerUid, $PaymentBy, $StartDate);

foreach($gSBP as $row1) {
	
	   if($row1['Total'] <= 0) {
           $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field6`,`Sort`) VALUES ('Opening Balance as at ','" . $StartDate . "','". ($row1['Total']) . "', '9');";
	   } else {
           $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field5`,`Sort`) VALUES ('Opening Balance as at ','" . $StartDate . "','". $row1['Total'] . "', '9');";
	   }
	   
//	   echo $isql;
//	   echo "<br>";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
     
     $ClosingBal = $ClosingBal + $row1['Total'];
     
}

$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getCustomerSalesDocuments($principalId, $CustomerUid, $StartDate, $EndDate, $PaymentBy);

foreach($gSBP as $row1) {
	   if($PaymentBy==PAYMENT_BY_GROUP) {
	   	     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, 
                                                               `Field2`, 
                                                               `Field3`, 
                                                               `Field4`,
                                                               `Field5`, 
                                                               `Field7`,
                                                               `Field6`,  
                                                               `Sort` ) 
                    VALUES ('". $row1['invoice_date']    . "',
                            '". $row1['description']     . "',
                            '". ltrim($row1['document_number'],"0") . "',
                            '". $row1['CustomerName']         . "',
                            if('". $row1['document_type_uid'] . "' = '4', '" . substr($row1['source_document_number'],2,6) ."' ,''),
                            if('" .$row1['document_type_uid'] . "' in ('4','31')     , '". round($row1['invoice_total'],2) . "',''),
                            if('" .$row1['document_type_uid'] . "' not in ('4','31') , '". round($row1['invoice_total'],2) . "',''),
                            
                            '10');";
     } else {
	   	     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, 
                                                               `Field2`, 
                                                               `Field3`, 
                                                               `Field4`, 
                                                               `Field6`,
                                                               `Field5`,  
                                                               `Sort` ) 
                    VALUES ('". $row1['invoice_date']    . "',
                            '". $row1['description']     . "',
                            '". ltrim($row1['document_number'],"0") . "',
                            if('". $row1['document_type_uid'] . "' = '4', '" . $row1['source_document_number'] ."' ,''),
                            if('" .$row1['document_type_uid'] . "' in ('4','31')     , '". abs(round($row1['invoice_total'],2)) . "',''),
                            if('" .$row1['document_type_uid'] . "' not in ('4','31') , '". abs(round($row1['invoice_total'],2)) . "',''),
                            '10');";     	
     	
     	
     }                       
                            
                            
                            
                            
                            
           $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
     
     $ClosingBal = $ClosingBal + $row1['invoice_total'];
}
//  ***************************************************************
$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getCustomerPaymentDocuments($principalId, $CustomerUid, $StartDate, $EndDate);

foreach($gSBP as $row1) {
	       if($PaymentBy==PAYMENT_BY_GROUP) {
	           $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, 
                                         `Field2`, 
                                         `Field3`, 
                                         `Field4`, 
                                         `Field7`, 
                                         `Sort` ) 
                      VALUES ('". $row1['payment_date']    . "',
                              '". $row1['pay_type']     . "',
                              '". $row1['payment_number'] . "',
                              '" .substr($row1['document_number'],2,6) ."',
                              '". abs(round($row1['payment_amount'],2)) . "',
                              '10');";
	       } else {
              $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, 
                                                                  `Field2`, 
                                                                  `Field3`, 
                                                                  `Field4`, 
                                                                  `Field6`, 
                                                                  `Sort` ) 
                       VALUES ('". $row1['payment_date']    . "',
                               '". $row1['pay_type']     . "',
                               '". $row1['payment_number'] . "',
                               '" .substr($row1['document_number'],2,6) ."',
                               '". abs(round($row1['payment_amount'],2)) . "',
                               '10');";	       	
	       }
         $itresult = $dbConn->dbQuery($isql);
         $dbConn->dbQuery("commit");
     
         $ClosingBal = $ClosingBal + $row1['payment_amount'];
}
//  ************************************************************
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '11');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
	   if($ClosingBal <= 0) {  
         $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field6`, `Sort`) VALUES ('Closing Balance ',' ". round(0-$ClosingBal,2) . "', '12');";
	   } else {
         $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field5`, `Sort`) VALUES ('Total Due ','". round($ClosingBal,2) . "', '12');";
     }
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '13');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");



     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '18');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '19');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");     

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES ('**End** ', ' ', ' ', '20');";
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
         order by sort, Field1;";
$utresult = $dbConn->dbGetAll($tsql);

// print_r($utresult);

if (count($utresult) == 0) {
	   ?>
     <script type='text/javascript' >parent.showMsgBoxError("No Rows Selected<BR><BR>")</script> 
	   <?php
	   return;	
}

if($outputType==1) {
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
} else {
?>
	    <script type='text/javascript'>
	    	
//	          parent.showMsgBoxInfo('Printing Completed');
            window.open("<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=DETAILEDLEDGER&PRINCIPALID=<?php echo $principalId;?>&CUSTNAME=<?php echo $custName ;?>&PSMUID=<?php echo $CustomerUid; ?>" , "_blank", "toolbar=no,scrollbars=yes,resizable=yes,width=750,height=600" );</script>
      </script> 

<?php
}
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/reports/parameterBase.php?REPORTID=163


?>
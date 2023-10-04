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

$bldsql = "CREATE TABLE temp_ledger_" . $userId . " (`Field1` VARCHAR(80) NULL,
                                     `Field2` VARCHAR(80) NULL,
                                     `Field3` VARCHAR(80) NULL,
                                     `Field4` VARCHAR(80) NULL,
                                     `Field5` VARCHAR(80) NULL,
                                     `Field6` VARCHAR(80) NULL,
                                     `Field7` VARCHAR(80) NULL,
                                     `Field8` VARCHAR(80) NULL,
                                     `Field9` VARCHAR(80) NULL,
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
	
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field6`, `Sort` ) VALUES ('" .$row1['Principal'] ."', ' ". date("d-m-Y H:i") ."', '3');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
}
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '4');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
     
     if($PaymentBy==PAYMENT_BY_GROUP) {
         $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Field4`, `Field5`, `Field6`, `Field7`, `Sort` ) VALUES ('Date', 'Type','Ref. ','Name' ,'Source Ref. ','Debit','Credit', '8');";
     } else {
         $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Field4`, `Field5`, `Field6`, `Field7`, `Sort` ) VALUES ('Date', 'Type','Ref. ','Source Ref. ','Debit','Credit,Balance', 'Balance', '8');";
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
     
     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field5`, `Sort`) VALUES ('Statement as at ','" . substr($EndDate,8,2) . '-' . substr($EndDate,5,2). '-' . substr($EndDate,0,4) . "','', '6');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field3`, `Sort` ) VALUES (' ', ' ', ' ', '7');";
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");

$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getCustomerOpeningBalance($CustomerUid, $PaymentBy, $StartDate);

foreach($gSBP as $row1) {
	
	   if($row1['Total'] <= 0) {
           $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field6`,`Field9`,`Sort`) VALUES ('Opening Balance as at ','" . substr($StartDate,8,2) . '-' . substr($StartDate,5,2). '-' . substr($StartDate,0,4) . "','". ($row1['Total']) . "', '". ($row1['Total']) . "', '9');";
     } else {
           $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, `Field2`, `Field5`, `Field9`, `Sort`) VALUES ('Opening Balance as at ','" . substr($StartDate,8,2) . '-' . substr($StartDate,5,2). '-' . substr($StartDate,0,4) . "','". $row1['Total'] . "', '". ($row1['Total']) . "', '9');";
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
     $acBalance = $acBalance + $row1['invoice_total'];
	   if($PaymentBy==PAYMENT_BY_GROUP) {
	   	     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, 
                                                               `Field2`, 
                                                               `Field3`, 
                                                               `Field4`,
                                                               `Field5`, 
                                                               `Field7`,
                                                               `Field6`,
                                                               `Field9`,   
                                                               `Sort` ) 
                    VALUES ('". $row1['invoice_date']    . "',
                            '". $row1['description']     . "',
                            '". substr($row1['document_number'],2,6) . "',
                            '". $row1['CustomerName']         . "',
                            if('". $row1['document_type_uid'] . "' in ('4','31','32'), '" . substr($row1['source_document_number'],0,8) ."' ,''),
                            if('" .$row1['document_type_uid'] . "' in ('4','31', 32)     , '". round($row1['invoice_total'],2) . "',''),
                            if('" .$row1['document_type_uid'] . "' not in ('4','31', 32) , '". round($row1['invoice_total'],2) . "',''),
                            " . $row1['invoice_total'] . ",
                            '10');";
     } else {
	   	     $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, 
                                                               `Field2`, 
                                                               `Field3`, 
                                                               `Field4`, 
                                                               `Field6`,
                                                               `Field5`,
                                                               `Field9`,  
                                                               `Sort` ) 
                    VALUES ('". substr($row1['invoice_date'],0,4) . '-' . substr($row1['invoice_date'],5,2). '-' . substr($row1['invoice_date'],8,2)    . "',
                            '". $row1['description']     . "',
                            '". substr($row1['document_number'],2,6) . "',
                            if('". $row1['document_type_uid'] . "' in ('4','31','32'), '" . $row1['source_document_number'] ."' ,''),
                            if('" .$row1['document_type_uid'] . "' in ('4','31','32')     , '". abs(round($row1['invoice_total'],2)) . "',''),
                            if('" .$row1['document_type_uid'] . "' not in ('4','31','32') , '". abs(round($row1['invoice_total'],2)) . "',''),
                            " . round($row1['invoice_total'],2) . ",
                            '10');";     	
     	
     	
     }
     $itresult = $dbConn->dbQuery($isql);
     $dbConn->dbQuery("commit");
     
     $ClosingBal = $ClosingBal + round($row1['invoice_total'],2);
}
//  ***************************************************************
$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getCustomerPaymentTotals($principalId, $CustomerUid, $StartDate, $EndDate);

foreach($gSBP as $row1) {
	       $acBalance = $acBalance + $row1['payment_amount'];
	
	       if($PaymentBy==PAYMENT_BY_GROUP) {
	           $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, 
                                         `Field2`, 
                                         `Field3`, 
                                         `Field4`, 
                                         `Field7`,
                                         `Field9`, 
                                         `Sort` ) 
                      VALUES ('". substr($row1['payment_date'],0,4) . '-' . substr($row1['payment_date'],5,2). '-' . substr($row1['payment_date'],8,2)    . "',
                              '". $row1['pay_type']     . "',
                              '". $row1['payment_number'] . "',
                              '" .substr($row1['document_number'],2,6) ."',
                              '". abs(round($row1['payment_amount'],2)) . "',
                              " . round($row1['payment_amount'],2) . ",
                              '10');";
	       } else {
              $isql = "INSERT INTO `temp_ledger_" . $userId . "` (`Field1`, 
                                                                  `Field2`, 
                                                                  `Field3`, 
                                                                  `Field4`, 
                                                                  `Field6`,
                                                                  `Field9`, 
                                                                  `Sort` ) 
                       VALUES ('". substr($row1['payment_date'],0,4) . '-' . substr($row1['payment_date'],5,2). '-' . substr($row1['payment_date'],8,2)   . "',
                               '". $row1['pay_type']     . "',
                               '". $row1['payment_number'] . "',
                               '" .substr($row1['document_number'],2,6) ."',
                               '". abs(round($row1['payment_amount'],2)) . "',
                               " . round($row1['payment_amount'],2) . ",
                               '10');";	       	
	       }
         $itresult = $dbConn->dbQuery($isql);
         $dbConn->dbQuery("commit");
     
         $ClosingBal = $ClosingBal + round($row1['payment_amount'],2);
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
                `Field7`,
                `Field9`
                
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

$acBalance  = 0;

if($outputType==1) {
    foreach (array_keys($utresult[0]) as $arow) {
          if (substr($arow,0,5) <> 'Field' && substr($arow,0,4) <> 'sort' ) {
              $csv_export.= $arow . ',';
          }
    }
    $csv_export.= "\n";

     foreach ($utresult as $brow) {
     	
         if(trim($brow['Field9']) <> '' ) {
             if(substr($brow['Field9'],0,1) == '-') {
                 $acBalance = $acBalance - trim(substr($brow['Field9'],1,10));
             } else {
                 $acBalance = $acBalance + trim($brow['Field9']);
             }
                 $balarr = array('Field10'=>round($acBalance,2));
         } else {
                $balarr = array();
         }
         $oarr = array();
         foreach(array_merge($brow,$balarr) as $key=>$frow) {
             if($key <> 'Field9' && $key <> 'Field8' && $key <> 'Field7') {
                  array_push($oarr, $frow);
             }
         }
         $csv_export.= implode(',', $oarr) . "\n";
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


?>
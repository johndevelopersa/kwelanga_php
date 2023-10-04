<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/checkDecimalTotalsDAO.php');
  
$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;
echo "<br>";
echo 'Checking Decimals..';
echo "<br>";    
$checkDecimalTotalsDAO = new checkDecimalTotalsDAO($dbConn);
$gBBQ = $checkDecimalTotalsDAO->getDocsToUpdate(216);
$checkDocument = '';
$updateDecHeader = $updateDec = 'N';
$noActionCount = 0;
$actionCount   = 0;
$csTot = $exTot = $vTot = $totTot = 0;            
foreach($gBBQ as $row) {
  	   if($checkDocument <> $row['dmUid'] && $checkDocument <> '') {
            if($updateDec == 'N') {
                 $checkDecimalTotalsDAO = new checkDecimalTotalsDAO($dbConn);
                 $gBBQ = $checkDecimalTotalsDAO->updateDocsNoAction($checkDocument);
                 $updateDec = 'N';
                 $noActionCount++;
            } else {
                 $checkDecimalTotalsDAO = new checkDecimalTotalsDAO($dbConn);
                 $gBBQ = $checkDecimalTotalsDAO->updateDocsAction($checkDocument, 
                                                                  $updateDecHeader,
                                                                  $csTot,
                                                                  $exTot,
                                                                  $totTot);
                 $updateDec = 'N';
                 $updateDecHeader = 'N';
                 $csTot = $exTot = $vTot = $totTot = 0; 
                 $actionCount++;         	
            	
            }
  	   }
  	   $checkDocument = $row['dmUid'];  	   
  	   if($row['allow_decimal'] <> 'Y') {
  	   	    if(in_array($row['document_status_uid'], array(74,75,87))) {
  	   	          $csTot = $csTot + $row['ordered_qty']  * 100 ;	
  	   	    } else {
  	   	          $csTot = $csTot + $row['document_qty'] * 100 ;
  	   	    }
  	   	    $exTot  = $exTot  + $row['extended_price']  * 100 ;
  	   	    $totTot = $totTot + $row['total']           * 100 ;
   	   } else {
  	        if(in_array($row['document_status_uid'], array(74,75,87))) {
  	   	          $csTot = $csTot + $row['ordered_qty'];
  	   	          $updateDecHeader = 'P';
  	   	    } else {
  	   	          $csTot = $csTot + $row['document_qty'];
  	   	          $updateDecHeader = 'Y';
  	   	    }
  	   	    $updateDec = 'Y';
  	   	    $exTot  = $exTot  + $row['extended_price'];
  	   	    $totTot = $totTot + $row['total']; 	
  	   }
}
echo "<br>";
echo 'No Action Count Updated - ' . $noActionCount;
echo "<br>";
echo 'To Update Count Updated - ' . $actionCount;
echo "<br>";
echo "Decimal Check Completed..<br>[***EOS***]";
echo "<br>";
?>     
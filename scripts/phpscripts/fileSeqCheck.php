
<?php
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/fileSeqCheck.php

// get Shopify files for last 14 days

$sql = "SELECT dh.customer_order_number
        FROM .document_master dm
        INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid
        AND dm.principal_uid = 216
        AND dm.incoming_file LIKE 'order%'
        AND dm.processed_date > NOW() -  INTERVAL 14 day
        ORDER BY dh.customer_order_number";
echo "<pre>";
 $orderNumbers = $dbConn->dbGetAll($sql);
 
 print_r($orderNumbers);
  $fileTot = count($orderNumbers) ;
  $firstNumber = trim(substr($orderNumbers[0]['customer_order_number'],1,6));
echo $firstNumber;
 for ($x = $firstNumber; $x <= ($firstNumber +$fileTot); $x++) {
  echo "The number is: " . $x . "<br>";
  if(in_array('#'. trim(substr($orderNumbers[0]['customer_order_number'],1,6)),$orderNumbers)) {
  	echo "yes";
  } else {
  	echo "no";
  }
  echo "ll";
}
 
 

 $fseq = 0;
   
 foreach($orderNumbers AS $oRow) {
 	  if($fseq == 0) {
 	  	    $fseq = trim(substr($oRow['customer_order_number'],1,6)); 
 	  }
 	  echo trim(substr($oRow['customer_order_number'],1,6));
 	  
 	  echo "PP" . "  " . $fseq;
 	  
 	  if(trim(substr($oRow['customer_order_number'],1,6)) == $fseq) {
         $stat = $fseq;
 	  } else {
 	       $stat = "Missing";	
 	  }
 	  $fseq++;  
 	  echo "<pre>";
 	  echo($oRow['customer_order_number']) . " - " . $stat;
 	  
 	  echo "<br>"; 	
 }
?>                  

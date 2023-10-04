
<?php
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/sgxUpload.php

// $start_date = new DateTime(date('Y-m-d H:i:s'));


$sql = "SELECT *
        FROM file_upload_temp a
        WHERE 1";
        
$xmlList = $dbConn->dbGetAll($sql); 



foreach($xmlList as $row) { 
	
	      if($row['FLD1'] == 'invoice_no') {
	      	echo $row['FLD2'];
	      	echo "<br>";
	      }
	      
	      if($row['FLD1'] == 'invoice_date') {
	      	echo $row['FLD2'];
	      	echo "<br>";
	      }
        if($row['FLD1'] == 'comment1') {
	      	echo $row['FLD2'];
	      	echo "<br>";
	      }
	      if($row['FLD1'] == 'supplier_product_code') {
	      	echo $row['FLD2'];
	      	echo "<br>";
	      }



	      if($row['FLD1'] == 'quantity') {
	      	echo $row['FLD2'];
	      	echo "<br>";
	      }
	      
/*      echo "<br>";
	      echo $row['FLD1'] . '   ' . $row['FLD2'];
	      echo "<br>";
*/		      

	


	
}
       
        
        
?>                  

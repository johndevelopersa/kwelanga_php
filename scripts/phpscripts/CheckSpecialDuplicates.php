<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/CheckSpecialDuplicates.php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

$sql = "SELECT t.FLD1
        FROM file_upload_temp t
        INNER JOIN document_tripsheet dt on dt.document_master_uid = trim(FLD1)
        WHERE trim(t.FLD2) <> 'DUP'
        ORDER BY FLD1;";
            
        $docs = $dbConn->dbGetAll($sql);
        $count = 0;
        $dupCount = 0;        
//        print_r($docs);
        echo "<br>";
        echo "PP";
        echo "<br>";
        
        $storeDoc = '';

foreach ($docs as $doc1) {
	
	     if($storeDoc == trim($doc1['FLD1']) && trim($doc1['FLD2']) <> 'DUP' ) {
             $sql = "UPDATE file_upload_temp SET FLD2 = 'DUP'
                     WHERE trim(FLD1) = ". trim($doc1['FLD1']) .";";

         	           $errorTO = $dbConn->processPosting($sql,"");
      
                     if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                          $errorTO->description="Insert Failed : ".$errorTO->description;
                          echo($errorTO->description); ;         	                  
                     } 
                     $dbConn->dbQuery("commit");
                  
                     $count++;
         } 
         
         $storeDoc = trim($doc1['FLD1']);
}
    echo "<br>";
    echo $count;
    echo "<br>";
    echo "End";

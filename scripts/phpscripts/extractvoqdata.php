<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once ($ROOT.$PHPFOLDER."DAO/ExtractDataDAO.php");
    
    global $paramsArr;

    if (!isset($_SESSION)) session_start() ;
    $userUId     = $_SESSION['user_id'] ;
      
    if (isset($_POST["INVOICE"])) $postINVOICE=test_input($_POST["INVOICE"]); else $postINVOICE = ''; 
      
    $dbConn = new dbConnect();
    $dbConn->dbConnection();

    $errorTO = new ErrorTO;
 
    $dirPath = 'C:/inetpub/wwwroot/systems/kwelanga_system/ftp/voqado/';
    
    echo substr($paramsArr['p1'],-4);
     echo "<br>";
    echo $dirPath . "    " .is_file($dirPath . $paramsArr['p1']);
    echo "<br>";
    echo $paramsArr['p1'];
    echo "<br>";
    if(!is_file($dirPath . $paramsArr['p1']) || substr($paramsArr['p1'],-4) <> '.csv' ) {
    	   echo "<br>";
    	   echo "<br>";
    	   echo "<br>";
         echo "****************** " . $paramsArr['p1'] . " ----- File Missing or Name incorrect  ****************** <br>";
         echo "<br>";
         echo "<br>";
         return;
    }
    
    $delimiters = array( ',' => 0, ';' => 0, "\t" => 0, '|' => 0, ); 
    $firstLine = ''; 
    $handle = fopen($dirPath . $paramsArr['p1'], 'r'); 
    if ($handle) { $firstLine = fgets($handle); 
    	fclose($handle); } 
    	
    	if ($firstLine) { 
    		foreach ($delimiters as $delimiter => &$count) { 
    			$count = count(str_getcsv($firstLine, $delimiter)); }
          
          $delim = array_search(max($delimiters), $delimiters); 
          
          
   		}
      else { key($delimiters); } 
    // Path and backup folder creation.
	  @mkdir($dirPath, 0777, true);
	  $bkupFolder = CommonUtils::createBkupDirs($dirPath, 1);
	
    $bldsql = "DROP TABLE IF EXISTS temp_voqado"; 

    $dtresult = $dbConn->dbQuery($bldsql);
    $dbConn->dbQuery("commit");

    $bldsql = "CREATE TABLE temp_voqado  (`principal_uid`   INT(10) NULL,
                                          `invoice_number` INT(10) NULL,
                                          `voqado_account`  VARCHAR(10) NULL,
                                          `document_number` INT(10) NULL,
                                          `document_type`   VARCHAR(1) NULL);";

    $dtresult = $dbConn->dbQuery($bldsql);
    $dbConn->dbQuery("commit");	
	
	  $sql='LOAD DATA LOCAL INFILE "' . $dirPath . $paramsArr['p1'] . '" INTO TABLE temp_voqado
				  CHARACTER SET latin1
	                  FIELDS TERMINATED BY "' . $delim . '"
	                  OPTIONALLY ENCLOSED BY "\""
	                  ESCAPED BY "\\\"
	                  LINES TERMINATED BY "\\r\\n" 
	                  IGNORE 1 ROWS;';	

         echo "<br>" . str_repeat("-",50) . "<br>";
         
         $rTO = $dbConn->processPosting($sql,"");
         
         if($rTO->type == "S"){ 
		         echo "Query: OK <br>";
		         $dbConn->dbQuery("commit");
		         
		         $fsuccess = rename ( $dirPath . $paramsArr['p1']  , $bkupFolder . '/' . $paramsArr['p1']);
		         
		         if($fsucess) {
		         	  echo " -- File Move Successful -- ";
		         	  
		         	  echo "<br>" . str_repeat("-",50) . "<br>";		         	
		         }
         } else {
    	       var_dump($rTO->type, $rTO->description);
    	       #var_dump($rTO);
    	       var_dump(mysqli_get_warnings($dbConn->connection));	
		         $dbConn->dbQuery("rollback");
         }
         
         $ExtractData = new ExtractDataDAO($dbConn);
         $gSBP =  $ExtractData->getDocumentList();
         
         foreach ($gSBP as $row) {
         	      // Get Voq Parameters
         	    if(trim($row['principal_uid']) <> '') {
                   $ExtractData = new ExtractDataDAO($dbConn);
                   $gSBP =  $ExtractData->getVoqadoParms($row['principal_uid']);
                 
                   // Insert into Smart Event                
                   $ExtractData = new ExtractDataDAO($dbConn);
                   $iSE =  $ExtractData->insertToSmartEvent($row['principal_uid'], $gSBP[0]['notification_uid'], $row['document_number'], $row['document_type']);
                
                   $ExtractData = new ExtractDataDAO($dbConn);
                   $iSE =  $ExtractData->unFlagSmartEvent($row['principal_uid'], $gSBP[0]['notification_uid'], $row['document_number'], $row['document_type']); 
                
                   $ExtractData = new ExtractDataDAO($dbConn);
                   $gSBP =  $ExtractData->checkSpecialFields($row['principal_uid'], $gSBP[0]['voqado_account_field'], $row['voqado_account'],  $row['document_number'], $row['document_type']);

                   $ExtractData = new ExtractDataDAO($dbConn);
                   $gSBP =  $ExtractData->setJeToRun($row['principal_uid']);
             }        
         } 
    echo "<br>";
    echo "<br>";
    echo "End";
    echo "<br>";

?>
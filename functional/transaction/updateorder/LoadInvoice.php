<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . "DAO/NewTransactionDAO.php");
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

$dirPath = 'C:/inetpub/wwwroot/systems/kwelanga_system/ftp/richs/in/';

// Sort in ascending order - this is default
$a = scandir($dirPath); 

// Create Temp Tables

$newtransactionDAO = new NewTransactionDAO($dbConn);
$eTO = $newtransactionDAO->deleteTempSo($userId);
$eTO = $newtransactionDAO->createTempSos($userId);

// Sort in ascending order - this is default
$a = scandir($dirPath);
$totalTime = 0;
$totalFileTime = 0;

foreach($a as $row) {
	
	$startTime = microtime(true);
	
    if(!is_file($dirPath . $row) || substr($row,0,3) <> 'RPC' ){
        echo "Excluding invalid file or folders: {$row}<br>";
        continue;
    } else {
         if($loop == 0) {
	           // Create Backup folders
	           // Path and backup folder creation.
	           @mkdir($dirPath, 0777, true);
	           $bkupFolder = CommonUtils::createBkupDirs($dirPath, 1);
	           $bkupFolder = CommonUtils::createDailyBackup($bkupFolder);  

	           $errBkupFolder = CommonUtils::createBkupDirs($dirPath, 2);
	           $errBkupFolder = CommonUtils::createDailyBackup($errBkupFolder);  
         
	       }
	       $sql='LOAD DATA LOCAL INFILE "' . $dirPath . $row . '" INTO TABLE kwelanga_live.rich_temp_11
				 CHARACTER SET latin1
	             FIELDS TERMINATED BY ";"
	             OPTIONALLY ENCLOSED BY "\""
	             ESCAPED BY "\\\"
	             LINES TERMINATED BY "\\r\\n" 
               IGNORE 1 LINES';	

         $loop++;
         echo "<br>" . str_repeat("-",50) . "<br>";
         
         echo "<h4>file:".$row."</h4>";
         
         $rTO = $dbConn->processPosting($sql,"");

         if($rTO->type == "S"){
			 
				 $totalTime = (microtime(true) - $startTime);
				 $totalFileTime += $totalTime;
				 
		         echo "Query: OK {$totalTime}<br>";
		         $dbConn->dbQuery("commit");
		         
		         $fsuccess = rename ( $dirPath . $row  , $bkupFolder . '/' . $row );
		         
		         if($fsucess) {
		         	  echo " -- File Move Successful -- ";
		         	  
		         	  echo "<br>" . str_repeat("-",50) . "<br>";		         	
		         }
		         
		
         } else {
    	       var_dump($rTO->type, $rTO->description);
    	       #var_dump($rTO);
    	       var_dump(mysqli_get_warnings($dbConn->connection));	
		         $dbConn->dbQuery("rollback");
		         
		         rename ( $dirPath . $row  , $errBkupFolder . '/' . $row );
         }

         #die();
    }
}
echo "Total File Time:" . $totalFileTime . "<br>";


// Checks file for SO Numbers

/* emptySoTable */
$startTime = microtime(true);
$eTO = $newtransactionDAO->emptySoTable($userId);
echo "emptySoTable Time: " . (microtime(true) - $startTime) . "<br>";


/* loadSoTable */
$startTime = microtime(true);
$eTO = $newtransactionDAO->loadSoTable($userId);
echo "loadSoTable Time: " . (microtime(true) - $startTime) . "<br>";


/* updateRichTable */
$startTime = microtime(true);
$eTO = $newtransactionDAO->updateRichTable($userId);
echo "updateRichTable Time: " . (microtime(true) - $startTime) . "<br>";


/* checkDoubledItemNos */
$startTime = microtime(true);
$eTO = $newtransactionDAO->checkDoubledItemNos($userId);
echo "checkDoubledItemNos Time: " . (microtime(true) - $startTime) . "<br>";


/* updateRichInvoicedTransactions */
$startTime = microtime(true);
$eTO = $newtransactionDAO->updateRichInvoicedTransactions($userId);
echo "updateRichInvoicedTransactions Time: " . (microtime(true) - $startTime) . "<br>";


/* checkInvoicedTransactionsTotals */
$startTime = microtime(true);
$eTO = $newtransactionDAO->checkInvoicedTransactionsTotals($userId);
echo "checkInvoicedTransactionsTotals Time: " . (microtime(true) - $startTime) . "<br>";

echo "<br>";
echo "End<br>[***EOS***]";
// ************************************************************************************************************************************


?>

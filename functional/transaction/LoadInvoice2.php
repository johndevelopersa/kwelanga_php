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

$dirPath = 'C:/inetpub/wwwroot/systems/kwelanga_system/ftp/richs/test/';

// Sort in ascending order - this is default
$a = scandir($dirPath); 

// Create Temp Tables

$newtransactionDAO = new NewTransactionDAO($dbConn);
$eTO = $newtransactionDAO->deleteTempSo($userId);

$newtransactionDAO = new NewTransactionDAO($dbConn);
$eTO = $newtransactionDAO->createTempSos($userId);

$newtransactionDAO = new NewTransactionDAO($dbConn);
$eTO = $newtransactionDAO->deleteTempRich($userId);

$newtransactionDAO = new NewTransactionDAO($dbConn);
$eTO = $newtransactionDAO->createTempRich($userId);

// Sort in ascending order - this is default
$a = scandir($dirPath);

foreach($a as $row) {
    if(!is_file($dirPath . $row) && substr($dirPath . $row,0,3) <> 'RPC' ){
        echo "excluding invalid file: {$row}<br>";
        continue;
    }
    if($loop == 0) {
	       // Create Backup folders
	       // Path and backup folder creation.
         @mkdir($dirPath, 0777, true);
         $bkupFolder = CommonUtils::createBkupDirs($dirPath, 1);
         $bkupFolder = CommonUtils::createDailyBackup($bkupFolder);  

         $errBkupFolder = CommonUtils::createBkupDirs($dirPath, 2);
         $errBkupFolder = CommonUtils::createDailyBackup($errBkupFolder);  
         
    } else {

         $sql='LOAD DATA LOCAL INFILE "' . $dirPath . $row . '" INTO TABLE kwelanga_live.rich_temp_' . $userId .'
               FIELDS TERMINATED BY ";"
               OPTIONALLY ENCLOSED BY "\""
               ESCAPED BY "\\\"
               LINES TERMINATED BY "\\r\\n" 
               IGNORE 1 LINES';		
    }
    echo ($dirPath . $row);
    
    $loop++;
    echo "<br>" . str_repeat("-",50) . "<br>";

    echo "<h4>file:".$row."</h4>";
    echo "<br>" . str_repeat("-",50) . "<br>";
    $rTO = $dbConn->processPosting($sql,"");

    if($rTO->type == "S"){
		echo "Query: OK<br>";
		$dbConn->dbQuery("commit");
		
		echo "SS";
		
    } else {
    	var_dump($rTO->type, $rTO->description);
    	print_r($rTO);
    	echo "LL";
    	var_dump(mysqli_get_warnings($dbConn->connection));	
		$dbConn->dbQuery("rollback");
    }

    #die();

    echo "<br>" . str_repeat("-",50) . "<br>";

    echo "<br>";

}



echo "End";
// ************************************************************************************************************************************


?>

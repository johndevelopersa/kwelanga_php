<?php

include('ROOT.php'); include($ROOT.'PHPINI.php');
require_once $ROOT.$PHPFOLDER.'properties/Constants.php' ;
require_once $ROOT.$PHPFOLDER.'libs/storage/Storage.php';
require_once $ROOT.$PHPFOLDER.'DAO/ScansDAO.php';
require_once $ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php';

/*------------------------------------------------*/

set_time_limit(15*60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

//DB conn
if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();  
}

//S3 storage init.
$storage = new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);

/*------------------------------------------------*/

//scans folder and regex pattern
$OUTGOING_SCANS_DIR = $ROOT . "ftp/scans/outgoing/";
$ERROR_SCANS_DIR = $ROOT . "ftp/scans/errors/";
$OUTGOING_REGEX = "/.*\.pdf/";

$statST = microtime(true);
echo "processScans Started: ".(CommonUtils::getGMTime(0))."\n";

//Recursively find all *.pdf files
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($OUTGOING_SCANS_DIR));
$files = new RegexIterator($iterator, $OUTGOING_REGEX, RegexIterator::GET_MATCH);

foreach($files as $pdfFile) :
	
	echo str_repeat("-", 75) . "\n";
	
	//regex match.
	if(!isset($pdfFile[0])){	
		continue;
	}
	$pdfFile = $pdfFile[0];
	
	//do we have a valid file.
	if(!is_file($pdfFile)){
		echo "error file '$pdfFile' is not a file!\n";
		failScanFile($pdfFile);			
		continue;
	}
	
	//ignore empty files
	if(filesize($pdfFile) == 0){
		echo "error file '$pdfFile' is a zero file!\n";		
		continue;
	}
		
	echo "Processing file: {$pdfFile}\n";
	
	$filename = pathinfo($pdfFile, PATHINFO_FILENAME);	//just get the file name, excludes path and extension.
	
	echo "Filename: \t{$filename}\n";
	
	if(substr(basename($filename),0,6) == 'SI22BM') {
         $principalUid = '290';
         $scannedDocumentNo = trim(substr(basename($filename),6,20));	
	} else {
         $filePartsArr = explode(" - ",basename($filename));

         //file must have two parts: eg: 396 - 00147860.pdf ==> {PRINCIPAL} - {DOCNO}.pdf
         if(count($filePartsArr)!= 2){
              echo "filename '$filename' does not have 2 parts, discarding!\n";
              //TODO these need to be manually fixed.
              failScanFile($pdfFile);		
              continue;
         }
	       //pull from the exploded filename parts.
         $principalUid = (int)$filePartsArr[0];
         $scannedDocumentNo = $filePartsArr[1];	
	}
    
	if(!$principalUid > 0){
		echo "error while parsing filename '$filename' invalid principal int: $principalUid\n";
		failScanFile($pdfFile);
		continue;
	}
	
	//set the storage path
	$storagePath = 'public/scans/' . $principalUid . "/" . date("Y/m") . "/" . basename($pdfFile);
	
	//display
	echo "Principal: \t{$principalUid}\n";
	echo "Doc No.: \t{$scannedDocumentNo}\n";
	echo "Storing path: \t{$storagePath}\n";
	
	echo "uploading to storage...\t\t\t";
	
		//upload pdf to storage
		$storageUploadResult = Storage::putObject(S3_BUCKET_NAME, $storagePath, file_get_contents($pdfFile));	
		if(!$storageUploadResult){
			echo "error uploading file '$pdfFile'\n";
			print_r($storageUploadResult);
			continue;
		}
		
	echo "SUCCESS\n";
	
	echo "inserting into database...\t\t";
	
		//everything checks out... 
		//insert into the database
		$result = (new ScansDAO($dbConn))->insertDocumentScanEntry(
			$documentType = DT_DELIVERYNOTE, 			
			$storagePath, 
			$principalUid, 
			$scannedDocumentNo,
			$fileSize = filesize($pdfFile), 
			$md5Check = md5_file($pdfFile)
		);
		
		if($result->type != FLAG_ERRORTO_SUCCESS){
			echo "error insertDocumentScanEntry:\n";
			print_r($result);
			mysqli_query($dbConn->connection, "rollback");
			continue;
		}	
		
		//commit and continue	
		$dbConn->dbinsQuery("commit;");
		
	echo "SUCCESS\n";
	
	//finally we can delete the local file!	
	//uncomment when ready to go live!
	unlink($pdfFile);
	
	echo "Completed file: {$pdfFile}\n";
	
	//end of single pdf file.
endforeach;



/*------------------------------------------------*/

echo "Completed: ".(CommonUtils::getGMTime(0))."\n";

$statET = microtime(true);
$statTT = round($statET - $statST,4);
echo "[@>>>JOBS:TT:".$statTT."@]\n";  //stat line.
echo '[***EOS***]';


function failScanFile($file){
	global $ERROR_SCANS_DIR;

    $dirDate = $ERROR_SCANS_DIR . "/" .  date("Y") . "/" . date("m") . '/';
    if(!is_dir($dirDate)){
        mkdir($dirDate, 0777, true);
    }

    $result = rename($file, $dirDate . basename($file));
    if($result===false){
        echo "error moving file";
    }
}
<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/uploads/upload_tempfile_storage.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	 
//LIBRARIES
require_once $ROOT.$PHPFOLDER."properties/Constants.php";
require_once $ROOT.$PHPFOLDER.'libs/storage/Storage.php';
include_once $ROOT.$PHPFOLDER."DAO/TaskManDAO.php";

$userUId     = "11";

//Create new database object
$dbConn  = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;

$storage = new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);

$statST = microtime(false);

echo "Transfer to Storage Session Started: ".(CommonUtils::getGMTime(0))."\n\n";	

// Get list of file to transfer to storage

$TaskManDAO = new TaskManDAO($dbConn);
$filesToLoad = $TaskManDAO->getFilesToTransfer();

// echo "<pre>";

// print_r($filesToLoad);

$fcount = $fcountError = 0;

if (count($filesToLoad) > 0) {
	
	      foreach($filesToLoad as $row) {
	      	      	    
	      	    $principalDirectory = '/archives/debtor_admin/' . trim($row['principal_uid']) . "_" . trim($row['Principal']) . "/" . trim($row['tYear']) . "/" . trim(str_pad($row['tMonth'],3,"0",STR_PAD_LEFT)) ;
	      	    	      	    
	      	    $localFile = $ROOT . 'ftp/storagedocuments/'. $row['file_name'];
	      	    $remoteFile = $principalDirectory .'/'. $row['file_name'];   	    
	      	    
	      	    $result = $storage::putObjectFile(S3_BUCKET_NAME, $localFile, $remoteFile);
              if ($result) {
              	   // echo "<br>";
              	   // echo "file uploaded successfully\n";
              	   $TaskManDAO = new TaskManDAO($dbConn);
                   $filesToLoad = $TaskManDAO->updateFileTransfer($row['fuiUid'], 'Y', ''); 
                   
                   // delete local file 
              
                   unlink($localFile);
                   
                   $fcount++;
                   
              } else {
              	    echo "<br>";
                    echo "failed to upload file:\n";
					print_r($result);
              	    $TaskManDAO = new TaskManDAO($dbConn);
                    $filesToLoad = $TaskManDAO->updateFileTransfer($row['fuiUid'], 'E', $result); 
                    
                    $fcountError++;

              }
	      }
	      
	      $statFN = microtime(false);
	      $tTaken = $statFM - $statST;
	      echo "<br>";
	      echo "Files Transfer Complete ";
	      echo "<br>";
	      echo "Succesful ". trim($fcount);
	      echo "<br>";
	      echo "Error " . trim($fcountError);
	      echo "<br>";
	      echo "Time Taken " . $tTaken  . "<br>";
	      echo "[***EOS***]";
	      return;
} else {
	  echo "<br>";
    echo "No Files to Transfer : ".(CommonUtils::getGMTime(0))."\n\n";
    echo "[***EOS***]";	
    return;
}

<pre>
<?php

include('ROOT.php'); include($ROOT.'PHPINI.php');
require_once $ROOT.$PHPFOLDER.'properties/Constants.php' ;
require_once $ROOT.$PHPFOLDER.'libs/FTPSClient.php';
require_once $ROOT.$PHPFOLDER.'libs/CommonUtils.php';

error_reporting(-1);
ini_set('display_errors', 1);

// var FTPSClient $ftpClient
$ftpClient = null;
$testDirectory = "testftp";
$statST = microtime(true);

echo "Check Started: ".(CommonUtils::getGMTime(0))."\n";

echo str_repeat("-",66) . "\n";
echo "\tFile Server System-Check \n";
echo str_repeat("-",66) . "\n";

echo "Creating a new FTPS Client...\t\t\t";

try {
    $ftpClient = new FTPSClient(FILE_SERVER_HOSTNAME, FILE_SERVER_FTPS_PORT, FILE_SERVER_USERNAME, FILE_SERVER_PASSWORD);
} catch (Exception $e) {
    echo $e->getMessage();
    die();
}

echo "OK\n";

//-----------------------------------------------------------------

$remoteFile = "test_file.dat";
$sampleData = str_repeat(uniqid("data"), 1000);
				
echo "Uploading '$remoteFile' (" . strlen($sampleData) . " bytes)...\t";

$uploadResult = $ftpClient->UploadFile($testDirectory . "/" . $remoteFile, $sampleData);
if(!$uploadResult){
    die("UPLOAD FAILED!\n");
}

echo "OK\n";

//-----------------------------------------------------------------

echo "Listing directory...\t\t\t\t";

$filesArr = $ftpClient->GetDirectoryList($testDirectory);
if(!is_array($filesArr) || count($filesArr) == 0){
    die("Failed to list directory: $testDirectory");
}

//var FTPFile $uploadedFile
$uploadedFile = null;
foreach($filesArr as $file){
    if($file->isFile() && $file->name == $remoteFile && $file->size == strlen($sampleData)){
        $uploadedFile = $file;
    }
}

if(!$uploadedFile){
    die("error locating uploaded file: $remoteFile");
}

echo "$remoteFile FOUND!\n";

//-----------------------------------------------------------------

echo "Verifying '$remoteFile'...\t\t\t";

$result = $ftpClient->GetFile($testDirectory . "/" . $remoteFile);
if(strcmp($result, $sampleData) !== 0){
    die("failed verifying file data!");
}

echo "OK\n";

//-----------------------------------------------------------------

echo "Cleaning up test file...\t\t\t";

$result = $ftpClient->DeleteFile($testDirectory . "/" . $remoteFile);
if(!$result){
    die("failed removing test file!");
}

echo "OK\n";

echo str_repeat("-",66) . "\n";
	echo "File Server Test Completed\n";
echo str_repeat("-",66) . "\n";

echo "Completed: ".(CommonUtils::getGMTime(0))."\n";

$statET = microtime(true);
$statTT = round($statET - $statST,4);
echo "[@>>>JOBS:TT:".$statTT."@]\n";  //stat line.
echo '[***EOS***]';
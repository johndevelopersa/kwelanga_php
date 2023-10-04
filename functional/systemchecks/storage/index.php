<?php

include('ROOT.php'); include($ROOT.'PHPINI.php');
require_once $ROOT.$PHPFOLDER.'properties/Constants.php' ;
require_once $ROOT.$PHPFOLDER.'libs/storage/Storage.php';
require_once $ROOT.$PHPFOLDER.'libs/CommonUtils.php';

$statST = microtime(true);

echo "Check Started: ".(CommonUtils::getGMTime(0))."\n";

echo str_repeat("-",45) . "\n";
echo "\tStorage System-Check \n";
echo str_repeat("-",45) . "\n";


echo "Creating new storage class...\t\t\t\t\t";

	$storage = new Storage(S3_ACCESS_ID, S3_SECRET_KEY, true, S3_ENDPOINT, S3_REGION);
    Storage::setSSL(true, false);
	
echo "OK\n";

//-----------------------------------------------------------------

echo "Listing buckets...\t\t\t\t\t\t";

	$bucketsArr = Storage::listBuckets(true);
	if(!isset($bucketsArr['buckets']) || !is_array($bucketsArr['buckets'])){
		die("FAILED - missing buckets key or not array!\n");
	}
	
echo "OK - ". count($bucketsArr['buckets']) ." buckets found!\n";

//-----------------------------------------------------------------

echo "Confirming bucket '".S3_BUCKET_NAME."' exists ...\t\t\t";
	$uploadBucketExists = false;
	foreach($bucketsArr['buckets'] as $bucket){
		if($bucket['name'] === S3_BUCKET_NAME){
			$uploadBucketExists = true;
			break;
		}
	}
	if(!$uploadBucketExists){
		die("FAILED - bucket missing: " . S3_BUCKET_NAME . "\n");
	}
echo "OK\n";

//-----------------------------------------------------------------

$remoteFile = "/storage.test/file.xml";
$sampleData = '<?xml version="1.0" encoding="UTF-8"?>
				<note>
				  <to>Tove '.rand(0,10000).'</to>
				  <from>Jani</from>
				  <heading>Reminder - '.date('d-m-y h:i:s').'</heading>
				  <body>Dont forget me this weekend!</body>
				</note>';
				
echo "Uploading '$remoteFile'...\t\t\t\t";		

	if(!Storage::putObject(S3_BUCKET_NAME, $remoteFile, $sampleData)){
		die("UPLOAD FAILED!\n");
	}

echo "OK\n";

//-----------------------------------------------------------------

echo "Verifying '$remoteFile'...\t\t\t\t";	

	$result = Storage::getObject(S3_BUCKET_NAME, $remoteFile);
	if(!$result || $result->error){
		echo "FETCH FAILED!\n";	
		print_r($result);
		die();
	}
	if(strcmp($result->body, $sampleData)!==0){
		die("Contents DO NOT MATCH!");	
	}

echo "OK\n";

//-----------------------------------------------------------------


echo "Cleaning up test file...\t\t\t\t\t";

	if(!Storage::deleteObject(S3_BUCKET_NAME, $remoteFile)){
		die("DELETE FAILED!\n");
	}

echo "OK\n";


//-----------------------------------------------------------------

$storageTestPublicFile = "/public/storage.test/test_".uniqid().".html";
	$fileData = uniqid("TEST-");
	
echo "Uploading public file '$storageTestPublicFile'...\t";

	if(!Storage::putObject(S3_BUCKET_NAME, $storageTestPublicFile, $fileData)){
		die("UPLOAD FAILED!\n");
	}

echo "OK\n";

echo "Fetching via HTTP...\t\t\t\t\t\t";

	$publicUrl = Storage::getPublicURL(STORAGE_DOMAIN, $storageTestPublicFile);
	echo $publicUrl . "\n";
	if(!$publicUrl){
		die("URL FAILED!\n");
	}
	
	$fetch = httpRequest($publicUrl);
	if(!isset($fetch['status']) || $fetch['status'] != 200){
		die("HTTP STATUS NOT OK - " . ($fetch['status']??"NA") . "\n");
	}

	if(!isset($fetch['data']) || strcmp($fetch['data'], $fileData) !== 0){
		die("DATA VERIFICATION FAILED!\n");
	}

echo "OK\n";

//-----------------------------------------------------------------

echo "Cleaning up public test file...\t\t\t\t\t";

	if(!Storage::deleteObject(S3_BUCKET_NAME, $storageTestPublicFile)){
		die("DELETE FAILED!\n");
	}

echo "OK\n";

//-----------------------------------------------------------------

//StatusCake Storage PUSH - monitors that this script completed atleast every 15min.
echo "Pinging StatusCake...\t\t\t\t\t\t";
	httpRequest("https://push.statuscake.com/?PK=ffd463f5d227a93&TestID=5817494&time=0");
echo "OK\n";

echo str_repeat("-",45) . "\n";
	echo "Storage and Public Test Completed\n";
echo str_repeat("-",45) . "\n";

echo "Completed: ".(CommonUtils::getGMTime(0))."\n";

$statET = microtime(true);
$statTT = round($statET - $statST,4);
echo "[@>>>JOBS:TT:".$statTT."@]\n";  //stat line.
echo '[***EOS***]';


function httpRequest($url)
{
  $ch = curl_init();
    
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch, CURLOPT_TIMEOUT,60);

  $response = curl_exec($ch);
  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  
  $error_message = false;
  if($errno = curl_errno($ch)) {
	$error_message = curl_strerror($errno);	
  }

  curl_close($ch); // Close the connection
	
  return [
	'data' => $response,
	'status' => $httpcode,
	'msg' => $error_message,
  ];
	
}
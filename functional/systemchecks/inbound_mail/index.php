<?php

# ---------------------------------------------------------------------------
# This system check ensures that inbound emails are correctly being processed
# ---------------------------------------------------------------------------
# it does this by checking for files on s3://kos.mail.cpt/inbound_* and
# that none are older than a specific date, which would indicate a Lambda processing failure
#

include('ROOT.php'); include($ROOT.'PHPINI.php');
require_once $ROOT.$PHPFOLDER.'properties/Constants.php' ;
require_once $ROOT.$PHPFOLDER.'libs/storage/Storage.php';
require_once $ROOT.$PHPFOLDER.'libs/CommonUtils.php';

$divider = str_repeat("-",45) . "\n";
$statST = microtime(true);

echo "Check Started: ".(CommonUtils::getGMTime(0))."\n";

echo $divider;
echo "\tInbound Mail System-Check \n";
echo $divider;

//-----------------------------------------------------------------

echo "Creating new storage class...\t\t\t\t\t";

	$storage = new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_MAIL_ENDPOINT, S3_MAIL_REGION);
	
echo "OK\n";

//-----------------------------------------------------------------

echo "Listing inbound_ objects...\t\t\t\t\t\t";

$objects = Storage::getBucket(S3_MAIL_BUCKET_NAME, "inbound/");

echo "OK\n";

//-----------------------------------------------------------------

if($objects){
    echo "Found objects, verifying date stamps...\n";
    echo $divider;

    foreach($objects as $fileArr){

        if($fileArr['size'] == 0){
            continue;
        }

        $created = gmdate("Y-m-d\TH:i:s\Z", $fileArr['time']);
        $age =(new DateTime)->format("U") - $fileArr['time'];

        echo "File: {$fileArr['name']}\n  Created: {$created}\n  Age: {$age}\n";

        # retry again!
        if($age > 60) { //60sec old, rename triggering lambda again!
            echo "ERROR - UNPROCESSED INBOUND EMAIL: {$fileArr['name']}\n";

            $fileUUID = $fileArr['name'];
            if(strpos($fileUUID, "___retry") !== false){
                $parts = explode("___retry", $fileUUID);
                $fileUUID = array_pop($parts);
            }
            $retryKey = $fileUUID . '___retry-' . uniqid();

            echo "Retry key: {$retryKey}\n";

            Storage::copyObject(S3_MAIL_BUCKET_NAME, $fileArr['name'],S3_MAIL_BUCKET_NAME, $retryKey);
            Storage::deleteObject(S3_MAIL_BUCKET_NAME, $fileArr['name']);
        }

        echo $divider;
    }
} else {
    echo "NO inbound_ prefix objects, today is a good day!\n";
}

//-----------------------------------------------------------------

echo $divider;
echo "Inbound Mail Completed\n";
echo $divider;

echo "Completed: ".(CommonUtils::getGMTime(0))."\n";

$statET = microtime(true);
$statTT = round($statET - $statST,4);
echo "[@>>>JOBS:TT:".$statTT."@]\n";  //stat line.
echo '[***EOS***]';
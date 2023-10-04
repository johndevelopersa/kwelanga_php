<?php

//LIBRARIES
require_once __DIR__ . "/../storage/StorageClass.php";
require_once __DIR__ . "/../storage/constants.php";

//this example uploads a local file to the public directory and serves a url that you can store or serve to a user
echo "creating storage class for multiple uses\n";
$store = (new StorageClass);

//STEP 1.
//create the public file
$localFile = __DIR__ . "/sample_local_file.png";
$remoteFile = "/public/my_test_logo.png";

echo "Uploading local file $localFile to -> $remoteFile\n";

$result = $store->putFromLocal($remoteFile, $localFile);
if ($result) {
    echo "file uploaded successfully\n";
} else {
    echo "failed to upload file:" . $store->getError() . "\n";
}

//this methods helps us to verify that the file exists and is inside a public folder
if($store->isPublic($remoteFile)){
    echo "PUBLIC URL of $remoteFile ---> " . $store->getUrl($remoteFile) . "<---\n";
} else {
    echo "failed to verify as a public file\n";
}
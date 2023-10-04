<?php

//LIBRARIES
require_once __DIR__ . "/../storage/StorageClass.php";
require_once __DIR__ . "/../storage/constants.php";


echo "creating storage class for multiple uses\n";
$store = (new StorageClass);

//PUT file!
$localFile = __DIR__ . "/sample_local_file.png";
$remoteFile = "/orders/demo/" . basename($localFile);   //basename uses the same local filename, you can change that if you need.

echo "Uploading local file $localFile to -> $remoteFile\n";

$result = $store->putFromLocal($remoteFile, $localFile);
if ($result) {
    echo "file uploaded successfully\n";
} else {
    echo "failed to upload file:" . $store->getError() . "\n";
}


//rename the local file.
$newRemoteFile = "/orders/demo/logo.png";
$renameFile = $store->rename($remoteFile, $newRemoteFile);
if($renameFile){
    echo "file $remoteFile renamed to $newRemoteFile --> OK!\n";
} else {
    echo "failed to rename file $remoteFile to $newRemoteFile:" . $store->getError() . "\n";
}

$deletedFile = $store->delete($newRemoteFile);
if ($deletedFile) {
    echo "file deleted successfully\n";
} else {
    echo "failed to delete file:" . $store->getError() . "\n";
}

/// TEST error catching, folder doesn't exist.
$localFile = __DIR__ . "/sample_local_file.png";
$remoteFile = "/invalidRemoteFolder/" . basename($localFile);


echo "Uploading local file $localFile to -> $remoteFile\n";

//PUT file!
$result = $store->putFromLocal($remoteFile, $localFile);
if ($result) {
    echo "file uploaded successfully\n";
} else {
    echo "failed to upload file:" . $store->getError() . "\n";
}



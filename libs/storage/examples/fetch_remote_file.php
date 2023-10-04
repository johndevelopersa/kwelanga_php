<?php

//LIBRARIES
require_once __DIR__ . "/../storage/StorageClass.php";
require_once __DIR__ . "/../storage/constants.php";


echo "creating storage class for multiple uses\n";
$store = (new StorageClass);

//upload file!
$localFile = __DIR__ . "/sample_local_file.png";
$remoteFile = "/orders/demo/sample.png";

echo "Uploading local file $localFile to -> $remoteFile\n";

$result = $store->putFromLocal($remoteFile, $localFile);
if ($result) {
    echo "file uploaded successfully\n";
} else {
    echo "failed to upload file:" . $store->getError() . "\n";
}

//download file.
$logoData = $store->get($remoteFile);
if($logoData !== false){
    echo "file data fetched successfully --> " . strlen($logoData) . " raw bytes\n";
} else {
    echo "failed to fetch file data:" . $store->getError() . "\n";
}


$downloadLocal = __DIR__ . "/local_file.png";
$result = $store->getToLocal($remoteFile, $downloadLocal);
if ($result) {
    echo "file downloaded successfully\n";

    //clean up test!
    if(is_file($downloadLocal)){
        unlink($downloadLocal);
    }

} else {
    echo "failed to download file:" . $store->getError() . "\n";
}

<?php

//LIBRARIES
require_once __DIR__ . "/../storage/StorageClass.php";
require_once __DIR__ . "/../storage/constants.php";


echo "creating storage class for multiple uses\n";
$store = (new StorageClass);


//SAMPLE LOCAL RAW DATA
$rawData = '<?xml version="1.0" encoding="UTF-8"?>
            <note>
              <to>Tove</to>
              <from>Jani</from>
              <heading>Reminder</heading>
              <body>Dont forget me this weekend!</body>
            </note>';

$filename = "test-" . time() . ".xml";  //FILENAME
$path = "/orders/demo/";    //PATH ON STORAGE

echo "Uploading file to -> $path.$filename\n";

//PUT file!
$result = $store->put($path . $filename, $rawData);
if ($result) {
    echo "file uploaded successfully\n";
} else {
    echo "failed to upload file:" . $store->getError() . "\n";
}


/// TEST error catching, folder doesn't exist.
$filename = "test-" . time() . ".xml";  //FILENAME
$path = "/invalidFolder/";    //PATH ON STORAGE

echo "Uploading file to -> $path.$filename\n";

//PUT file!
$result = $store->put($path . $filename, $rawData);
if ($result) {
    echo "file uploaded successfully\n";
} else {
    echo "failed to upload file:" . $store->getError() . "\n";
}



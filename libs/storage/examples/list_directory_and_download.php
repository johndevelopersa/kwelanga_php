<?php

//LIBRARIES
require_once __DIR__ . "/../storage/StorageClass.php";
require_once __DIR__ . "/../storage/constants.php";


echo "creating storage class for multiple uses\n";
$store = (new StorageClass);

$directory = "/public/";

//check directory exists
$directoryExists = $store->isDir($directory);
if($directoryExists){
    echo "directory $directory exists!\n";
}

//create a directory
$exampleDir = "/test99/example_dir";
$directoryExists = $store->isDir($exampleDir);
if(!$directoryExists){
    echo "directory $directory exists!\n";

    $createdDir = $store->makeDir($exampleDir, true);
    if($createdDir){
        echo "directory $exampleDir created OK!\n";
    } else {
        echo "failed to created directory $exampleDir:" . $store->getError() . "\n";
    }
}

//rename the directory
$newExampleDir = "/test99/NEW_example_dir";
$renameDir = $store->rename($exampleDir, $newExampleDir);
if($renameDir){
    echo "directory $exampleDir renamed to $newExampleDir --> OK!\n";
} else {
    echo "failed to rename directory $exampleDir to $newExampleDir:" . $store->getError() . "\n";
}

//delete a directory, directory must be empty!
$deleteDir = "/test99/";
$directoryExists = $store->isDir($deleteDir);
if($directoryExists){
    $delDir = $store->removeDir($newExampleDir);    //directory must be empty!
    $delDir = $store->removeDir($deleteDir);
    if($delDir){
        echo "directory $deleteDir deleted OK!\n";
    } else {
        echo "failed to delete directory $deleteDir:" . $store->getError() . "\n";
    }
}

//list a directory
$dir = $store->listDir($directory);

print_r($dir);
/*

returns an array of files --

            [size] => 5380757           <--- bytes of file
            [uid] => 0
            [gid] => 0
            [permissions] => 33184
            [mode] => 33184
            [type] => 1                 <---- 1:FILE, 2:DIRECTORY
            [atime] => 1606688504
            [mtime] => 1606688504
            [filename] => 43320_14-5_Cogta.pdf

creating storage class for multiple uses
Array
(
    [43320_14-5_Cogta.pdf] => Array
        (
            [size] => 5380757
            [uid] => 0
            [gid] => 0
            [permissions] => 33184
            [mode] => 33184
            [type] => 1
            [atime] => 1606688504
            [mtime] => 1606688504
            [filename] => 43320_14-5_Cogta.pdf
        )

    [demo] => Array
        (
            [size] => 0
            [uid] => 0
            [gid] => 0
            [permissions] => 16877
            [mode] => 16877
            [type] => 2
            [atime] => 1608615662
            [mtime] => 1608615662
            [filename] => demo
        )

    [index.htm] => Array
        (
            [size] => 67
            [uid] => 0
            [gid] => 0
            [permissions] => 33188
            [mode] => 33188
            [type] => 1
            [atime] => 1610542460
            [mtime] => 1610542460
            [filename] => index.htm
        )

)


 */
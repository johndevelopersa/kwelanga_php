<?php

include('ROOT.php');
include($ROOT . 'PHPINI.php');
require_once $ROOT . $PHPFOLDER . 'properties/Constants.php';
require_once $ROOT . $PHPFOLDER . 'libs/FTPSClient.php';
require_once $ROOT . $PHPFOLDER . 'libs/CommonUtils.php';

error_reporting(-1);
ini_set('display_errors', 1);
$statST = microtime(true);

if(ENVIRONMENT != 'PRODUCTION'){
    die('This script is not allowed to run in non-production environments');
}

$directory = '/' . trim($_GET['directory'] ?? 'clearworld', '/');
$target = DIR_DATA_FTP_FROM . ('/' . trim($_GET['target'] ?? 'clearworld/ordersify', '/') . '/');
$minAge = 60 * 3; // 3 minutes

echo "Connecting to FileServer...\t\t\t";

try {
    $client = new FTPSClient(FILE_SERVER_HOSTNAME, FILE_SERVER_FTPS_PORT, FILE_SERVER_USERNAME, FILE_SERVER_PASSWORD);
} catch (Exception $error) {
    echo $error->getMessage();
    die();
}

echo "OK\n";

echo "Directory listing {$directory}...\t";

$file_listing = $client->GetDirectoryList($directory);
if (!empty($file_listing) && count($file_listing)) {

    echo count($file_listing) . " file/s\n";
    echo str_repeat('-', 45) . "\n";

    $tz = new DateTimeZone('+0000');
    $unix_timestamp = (new DateTime())->setTimezone($tz)->getTimestamp();

    foreach ($file_listing as $file) {
        if ($file->isFile()) {

            $file_age = $unix_timestamp - $file->modified->setTimezone($tz)->getTimestamp();

            echo "file:\t\t $file->name\n";
            echo "size:\t\t $file->size bytes\n";
            echo "mod:\t\t {$file->modified->format(GUI_PHP_DATETIME_FORMAT)} ({$file_age}s ago)\n";

            if ($file_age < $minAge) {
                echo "file is too young, wait for it to get older (+{$minAge}s)\n";
                continue;
            }

            $remote_file_path = $directory . '/' . $file->name;
            $local_file_path = $target . $file->name;

            $result = $client->GetFile($remote_file_path);
            if (strlen($result) == 0) {
                echo "[WARN] file is empty\n";
                continue;
            }
            if (file_put_contents($local_file_path, $result) === false) {
                echo "[ERROR] failed to write file: $local_file_path\n";
                continue;
            }

            if (!$client->DeleteFile($remote_file_path)) {
                echo "[ERROR] failed to delete remote file: $remote_file_path\n";
                unlink($local_file_path);
            }

            echo "Successfully saved: $local_file_path\n";
        }
    }
}

$statET = microtime(true);
$statTT = round($statET - $statST, 4);
echo '[@>>>JOBS:TT:' . $statTT . "@]\n";
echo '[***EOS***]';
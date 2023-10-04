<?php

include('ROOT.php'); include($ROOT.'PHPINI.php');
require_once $ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php';

error_reporting(-1);
ini_set('display_errors', 1);

$statST = microtime(true);

echo str_repeat("-", 50) . "\n";

//MYSQL SLAVE DETAILS
//user can only select and run replication queries
$servername = "13.245.51.137";
$username = "replica_monitor";
$password = "X91MxypJaR7lQWBPe5niDmUtvMLzXcRl";
$dbname = "kwelanga_live";
$port = 33060;

/*---------------------------------------------------------------------*/

echo "Creating to MySQL Replica... \t\t";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname, $port);
if (!$conn) {
    FatalError("Connection failed: " . mysqli_connect_error());
}

echo "OK\n";

/*---------------------------------------------------------------------*/

echo "Checking BACKUP MODE... \t\t";

// Query state from the sync.state table
$result = mysqli_query($conn, "SELECT `state`, `timestamp` FROM `sync`.`state`");
if (!$result) {
    FatalError("sync.state table query error: " . mysqli_error($conn));
}
if (mysqli_num_rows($result) == 0) {
    FatalError("ERROR: unknown state, no records returned from sync.state table");
}

$row = mysqli_fetch_assoc($result);

$currentState = $row["state"];
$stateChanged = $row["timestamp"];
$tz = new DateTimeZone("+0000");
$stateChangeTS = (new DateTime($stateChanged, $tz))->getTimestamp();
$diffSeconds = (new DateTime)->setTimezone($tz)->getTimestamp() - $stateChangeTS;

echo "$currentState\n";

//check state
if($currentState == "BACKUP_STARTED"){
    // how long ago did the backup start
    // backup takes around 10min, lets give them 30min to complete.
    if($diffSeconds > (30 * 60)){
        FatalError("ERROR: SLAVE appears to be struck in backup mode... started " . secondsToHumanTime($diffSeconds) . " ago... \t($stateChanged)");
    }

    echo "SLAVE IN BACKUP MODE - OK\n";
    echo "Started " . secondsToHumanTime($diffSeconds) . " ago... \t$stateChanged\n";
    echo '[***EOS***]';
    die();
}

/*---------------------------------------------------------------------*/

echo "Checking SLAVE status... \n";

// Query slave status
$result = mysqli_query($conn, "SHOW SLAVE STATUS");

if (mysqli_num_rows($result) == 0) {
    FatalError("ERROR: SHOW SLAVE STATUS returned no records");
}

$slave_status = mysqli_fetch_assoc($result);
$io_thread = $slave_status["Slave_IO_Running"];
$sql_thread = $slave_status["Slave_SQL_Running"];
$delay = $slave_status["Seconds_Behind_Master"];

$notRunningTracker = __DIR__ . '/slave_not_running.log';

// Check if IO and SQL threads are running
if ($io_thread === "Yes" && $sql_thread === "Yes") {
    echo "\tIO and SQL threads are running\n";

    if($diffSeconds < 60) {
        echo "\tBackup completed within 1 minute ago, provide space for slave to catch up ({$diffSeconds}s ago)\n";
    } else {

        echo "\tBackup completed over a 1 minute ago, checking slave delay: current {$delay}s\n";

        // Check if replication delay is within 5 minutes
        if ($delay <= 300) {
            echo "\tReplication delay is within 5 minutes\n";
        } else {
            FatalError("\tERROR: Replication delay is greater than 5 minutes\n");
        }

    }
} else {

    //track this using a local file
    $tz = new DateTimeZone("+0000");
    $unixNow = (new DateTime)->setTimezone($tz)->getTimestamp();
    if(!is_file($notRunningTracker)) {
        file_put_contents($notRunningTracker, (string)$unixNow);
    }

    $slave_not_running_first_unix = (int)file_get_contents($notRunningTracker);
    $slave_not_running_age_seconds = $unixNow - (new DateTime)->setTimestamp($slave_not_running_first_unix)->getTimestamp();

    $slave_not_running_first = (int)file_get_contents($notRunningTracker);
    if($slave_not_running_age_seconds > (60 * 15)) {
        FatalError("\tERROR: IO and/or SQL thread is not running for more than ".secondsToHumanTime($slave_not_running_age_seconds)."\n");
    }

    echo "\tERROR: IO and/or SQL thread is not running for ".secondsToHumanTime($slave_not_running_age_seconds)."\n";
    return;
}


if(is_file($notRunningTracker)) {
    echo "\tRemoved old slave_not_running.log IO and SQL file\n";
    unlink($notRunningTracker);
}

// Close connection
mysqli_close($conn);

$statET = microtime(true);
$statTT = round($statET - $statST, 4);
echo str_repeat("-", 50) . "\n";
echo "[@>>>JOBS:TT:" . $statTT . "@]\n";  //stat line.
echo '[***EOS***]';


function secondsToHumanTime($seconds)
{
    $intervals = [
        'year' => 31536000,
        'month' => 2592000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1,
    ];

    $time = '';
    foreach ($intervals as $unit => $interval) {
        if ($seconds >= $interval) {
            $time .= floor($seconds / $interval) . " $unit" . (floor($seconds / $interval) > 1 ? 's' : '') . ' ';
            $seconds = $seconds % $interval;
        }
    }

    return trim($time);
}

function FatalError($errMsg){
    BroadcastingUtils::sendAlertEmail("Replication Error",$errMsg . "\n Script: " . __FILE__ . "\n Timestamp: " . date("Y-m-d H:i:s"),"Y");
    die();
}
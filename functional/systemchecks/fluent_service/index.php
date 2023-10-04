<pre>
<?php

# ---------------------------------------------------------------------------
# system check that the logger daemon fluentbit is running and accepting application logs
# ---------------------------------------------------------------------------

include('ROOT.php'); include($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
require_once $ROOT.$PHPFOLDER.'libs/newrelic.php';

$statST = microtime(true);

echo "Check Started: ".(CommonUtils::getGMTime(0))."\n";

$result = NewRelic::logEvent("fluent-checkin", basename(__FILE__), "checkin");
if($result){
    echo "Log event successful!\n";
} else {
    echo "Log event error!\n";
}

$statET = microtime(true);
$statTT = round($statET - $statST,4);
echo "[@>>>JOBS:TT:".$statTT."@]\n";  //stat line.
echo '[***EOS***]';
<?php

$st = microtime(true);

include('ROOT.php'); include($ROOT.'PHPINI.php');
include($ROOT.$PHPFOLDER.'properties/Constants.php');
include($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');

$postTYPE = ((isset($_REQUEST["TYPE"]))?$_REQUEST["TYPE"]:false);
$postP1 = ((isset($_REQUEST["P1"]))?$_REQUEST["P1"]:false);

// BroadcastingUtils::sendAlertEmail($subject, $body, $outputEcho, $quietMode = false);

switch ($postTYPE) {
  case "1":
//    $resultTO = BroadcastingUtils::sendTextEmail("DASHBOARD WARNING - ClockIn Expired", "Warning - the dashboard has not been clocked into within the time frame allowed ! \n\nPlease check the Jobs !", array("admin@kwelangasolutions.co.za"));
    break;
  case "2":
    $resultTO = BroadcastingUtils::sendTextEmail("DASHBOARD ERROR - Failed Script(s)", "WARNING !!\n\nThe Dashboard auto-scanner detects a possible script fallover in {$postP1}.", array("admin@kwelangasolutions.co.za"));
    break;
  default :
    echo "INVALID TYPE";
    return;
}


?>
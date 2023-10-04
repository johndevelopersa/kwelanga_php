<?php

/* * ********************************************************************************************
 * *
 * *  This job can run as many times per day as is necessary according to job scheduler.
 * *
 * *  It executes notifications that occur throughout the day by triggers
 * *
 * ******************************************************************************************** */

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once __DIR__ . "/../../../libs/api/voqado-api/VoqadoRestAPI.php";
include_once $ROOT . $PHPFOLDER . "functional/jobs/voqado/VoqadoConstants.php";

#echo __DIR__ . "/../../../libs/api/voqado-api/VoqadoRestAPI.php";

global $ROOT, $PHPFOLDER, $prinId;
//setup api class
$voqadoApi = new VoqadoRestAPI(VoqadoConstants::ApiUri, VoqadoConstants::ApiUsername, VoqadoConstants::ApiPassword);

$reportData = [
               's_coyid'         => 'KSSPD',
               's_userid'        => 'ALAN',
               'oreportfilename' => 'kws_batchlisting2.txt',
               'osqlcmd'         => 'substr(s_systemdatetime,1,10) between "' . $StartDate . '" and "' . $EndDate . '"'
              ];
                          
$response = $voqadoApi->Request("POST", "vqtxt/getreporttxt", $reportData); 

file_put_contents('aa1.txt', print_r($response->getBody(), TRUE));

echo "End Of Report";

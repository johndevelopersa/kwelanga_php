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

if($paramsArr['p4'] == '') {$codeTO = $paramsArr['p1'] ;} else { $codeTO = $paramsArr['p4'];}

$date = $paramsArr['p2'];
$mp = 'period_month_'   . date("m", strtotime($date));
$my = 'fin_year_factor' . date("m", strtotime($date));

$fyear = date("Y", strtotime($date)) + $pParm[0][$my];
$fperiod = $pParm[0][$mp];

$voqadoApi = new VoqadoRestAPI(VoqadoConstants::ApiUri, VoqadoConstants::ApiUsername, VoqadoConstants::ApiPassword);

$selectArray = Array();

if($paramsArr['p3'] == 'kws_drledger_xls') {

     $selectArray[] = array('s_fieldname' => 'codefrom', 's_value' => $paramsArr['p1']);
     $selectArray[] = array('s_fieldname' => 'codeto'  , 's_value' => $codeTO);
     $selectArray[] = array('s_fieldname' => '1',        's_value' => '2021');
     $selectArray[] = array('s_fieldname' => '2',        's_value' => '9');
     $selectArray[] = array('s_fieldname' => 'docdate' , 's_value' => $paramsArr['p2']);

     $reportData  = array('s_coyid'     => 'KSHW',
                          's_reportfilename'   => $paramsArr['p3'],
                          's_filename'         => 'temp.csv',
                          's_batchnumber'      => '1',
                          's_userid'           => 'JB',
                          'selectioncriteria'  => $selectArray);
	} else {

     $selectArray[] = array('s_fieldname' => 'codefrom', 's_value' => $paramsArr['p1']);
     $selectArray[] = array('s_fieldname' => 'codeto'  , 's_value' => $codeTO);
     $selectArray[] = array('s_fieldname' => 'fyear'   , 's_value' => '2021');
     $selectArray[] = array('s_fieldname' => 'fperiod' , 's_value' => '9');
     $selectArray[] = array('s_fieldname' => 'docdate' , 's_value' => $paramsArr['p2']);
     
     $reportData  = array('s_coyid'     => 'KSHW',
                    's_reportfilename'   => $paramsArr['p3'],
                    's_filename'         => 'temp.csv',
                    's_batchnumber'      => '1',
                    's_userid'           => 'JB',
                    'selectioncriteria'  => $selectArray);

}
               
if(file_exists($tempfile)) {
     unlink($tempfile);
}
$response = $voqadoApi->Request("POST", "/vqrwxls/runreport", $reportData, "", "temp.csv"); 

file_put_contents($tempfile, print_r($response->getBody(), TRUE));
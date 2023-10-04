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

$selectArray = Array();

$selectArray[] = array(array('s_fieldname'       => 'codefrom' ,
                       's_value'           => 'PNP001')  ,
                 array('s_fieldname'       => 'codeto',
                       's_value'           => 'PNP001'),
                 array('s_fieldname'       => 'fyear'    ,
                       's_value'           => '2021'),
                 array('s_fieldname'       => 'fperiod'  ,
                       's_value'           => '9'),
                 array('s_fieldname'       => 'docdate'  ,
                       's_value'           => '15/11/2020'));

print_r($selectArray);




$reportData  = array('s_coyid'     => 'KSHW',
               's_reportfilename'   => 'statement_ks_hw_xls',
               's_filename'         => 'temp.xlxs',
               's_batchnumber'      => '1',
               's_userid'           => 'JB',
               'selectioncriteria'  => $selectArray);
               
echo "<br>";
echo "<pre>";          
print_r($reportData);             
echo "</pre>";  
echo "<br>"; 

echo json_encode($reportData);

echo "<br>"; 
                          
$response = $voqadoApi->Request("POST", "/vqrwxls/runreport", $reportData); 

file_put_contents('aaNew.txt', print_r($response, TRUE));

echo "This is the End";

/*
[{"s_coyid":"KSHW","s_reportfilename":"statement_ks_hw_xls","s_filename":"temp.xlxs","s_batchnumber":"1","s_userid":"JB","selectioncriteria":[[{"s_fieldname":"codefrom","s_value":"PNP001"},{"s_fieldname":"codeto","s_value":"PNP001"},{"s_fieldname":"fyear","s_value":"2021"},{"s_fieldname":"fperiod","s_value":"9"},{"s_fieldname":"docdate","s_value":"15\/11\/2020"}]]}]



{"s_coyid":"KSHW",
"s_reportfilename":
"statement_ks_hw_xls",
"s_filename":"temp.csv",
"s_batchnumber":"1",
"s_userid":"JB",
"selectioncriteria":{"s_fieldname":"codefrom",
	                   "s_value":"PNP001"
	                  },{"s_fieldname":"codeto",
	                  	 "s_value":"PNP001"
	                  },{"s_fieldname":"fyear","s_value":"2021"
	                  },{"s_fieldname":"fperiod",
	                  	 "s_value":"9"
	                  },{"s_fieldname":"docdate",
	                  	 "s_value":"15\/11\/2020"}
}

{
    "s_coyid": "KSHW",
    "s_reportfilename": "statement_ks_hw_xls",
    "s_filename": "temp.xlsx",
    "s_batchnumber": "1",
    "s_userid": "JB",
    "selectioncriteria": [{
        "s_fieldname": "codefrom",
        "s_value": "PNP001"
    }, {
        "s_fieldname": "codeto",
        "s_value": "PNP001"
    }, {
        "s_fieldname": "fyear",
        "s_value": "2021"
    }, {
        "s_fieldname": "fperiod",
        "s_value": "9"
    }, {
        "s_fieldname": "docdate",
        "s_value": "15/11/2020"
    }]
}





*/
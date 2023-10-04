<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/vendhq_demo/get_customers.php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . "libs/CommonUtils.php");
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/api/vendhq/client.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");

//TODO: store in SSM
$apiKey = "lsxs_pt_dhCVfmsY4VodPazeQKPFmKHmI570ArgO";
$domainPrefix = "bonniebio";

$client = new VendHQClient($apiKey, $domainPrefix);

$customers = $client->getCustomers($pageSize = 5000);

if (!$customers->getSuccess()) {
    echo "Error: " . $customers->getErrorMessage();
    die();
}

$customerArr = $customers->getBody();

 echo "<pre>";
 print_r($customerArr);

foreach ($customerArr['data'] as $customer) {
    $customer_code = $customer['company_name'];

    //echo $customer_code. '<br>';
    // var_dump($customer_code);
    // die();
    //echo "$customer_code - ...<br>";
    if (strpos(strtolower($customer_code), 'pick') !== false) {
        
        $firstDash = strpos($customer_code, "-");
        $sndDash = strpos($customer_code, "-", $firstDash + 1);
        echo strpos($customer_code, "-", $firstDash + 1);        
        if(!strpos($customer_code, "-", $firstDash + 1)) {
             $brnLen = 6;	
        } else {
             $brnLen = $sndDash - $firstDash + 1;	
        }
        $branch = str_replace('-', '', substr($customer_code, strpos($customer_code, "-"), $brnLen));
        echo $customer['id'] . "," . $customer_code . "," . $branch;
        echo "<br>";
    } elseif (strpos(strtolower($customer_code), 'checker') !== false) {
        //echo "$customer_code - ...";
        $firstDash = strpos($customer_code, "-");
        $sndDash = strpos($customer_code, "-", $firstDash + 1);
        if(strpos($customer_code, "-", $firstDash + 1) == FALSE) {
             $brnLen = 6;	
        } else {
             $brnLen = $sndDash - $firstDash + 1;	
        }
        
        $branch = str_replace('-', '', substr($customer_code, strpos($customer_code, "-"), $brnLen));
        echo $customer['id'] . "," . $customer_code . "," . $branch;
        echo "<br>";        
    } elseif (strpos(strtolower($customer_code), 'shoprite') !== false) {
        //echo "$customer_code - ...";
        $firstDash = strpos($customer_code, "-");
        $sndDash = strpos($customer_code, "-", $firstDash + 1);
        if(strpos($customer_code, "-", $firstDash + 1) == FALSE) {
             $brnLen = 6;	
        } else {
             $brnLen = $sndDash - $firstDash + 1;	
        }
        
        $branch = str_replace('-', '', substr($customer_code, strpos($customer_code, "-"), $brnLen));
        echo $customer['id'] . "," . $customer_code . "," . $branch;
        echo "<br>"; 
    }
}


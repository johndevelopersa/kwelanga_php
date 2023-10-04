<?php

// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apiCallAdjustmentTest.php";


    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');




$data = array(
    'username'        => 'dadmin',
    'password'        => ')2QztI!P<+K89i(2aO]O',
    'requireddata'    => 'postStockAdjIncrease',
    'principalId'     => '390',
    'referenceNumber' => 'abs',
    'arrivalDateTime' => date('Y-m-d H:i:s'),
    'detailLines' => Array(),
);
                                            
                                            
$payload = json_encode($data);

echo "<h4>Payload</h4>";
echo $payload;
echo "<br>";
echo "<br>";

// print_r($data);
// echo "<br>";
// echo "<hr>";
// echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/v.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);

$curlDebug = curl_getinfo($ch);

// echo "<br><pre>";
// print_r(curl_getinfo($ch));

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    print_r($error_msg);
}

//$errorTO = new ErrorTO();
//$errorTO->type = FLAG_ERRORTO_ERROR;  //Preset!
if($result){
    $errorTO->type = FLAG_ERRORTO_SUCCESS;
    $errorTO->description = "test";
    $errorTO->identifier2 = $result;
//    echo CommonUtils::getJavaScriptMsg($errorTO);
} else {
    $errorTO->type = FLAG_ERRORTO_ERROR;
    $errorTO->description = "test";
    $errorTO->identifier2 = json_encode($result);
//    echo CommonUtils::getJavaScriptMsg($errorTO);
}

// close cURL resource, and free up system resources
curl_close($ch);


print_r($result);




?>
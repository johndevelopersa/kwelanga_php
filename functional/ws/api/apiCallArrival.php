<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// $callresult = "http://test-kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apiCallArrival.php";

$data = array(

              'username'        => 'joseph2',
              'password'        => 'bigbird1',
              'requireddata'    => 'postArrival',
              'principalId'     => '401',
              'referenceNumber' => '67007298',
              'orderDate'       => '27/06/2023 8:39:43 AM',
              'detailLines' => Array(Array
                                         (
                                          'productUid'           => '144697',
                                          'quantity'             => '1138'
                                          )
                                     )
             );
                                            
 
echo "<h4>Data Array</h4>";
echo "<pre>";
print_r($data);
echo "<br>";
echo "</pre>";
echo "<hr>";
 
                                            
$payload = json_encode($data);

echo "<h4>Payload</h4>";
echo $payload;
echo "<br>";
echo "<br>";

echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/v.php");
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

print_r($result);

// close cURL resource, and free up system resources
curl_close($ch);







?>
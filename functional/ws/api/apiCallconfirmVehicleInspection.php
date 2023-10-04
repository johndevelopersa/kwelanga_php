<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apiCallconfirmVehicleInspection.php";

$data = array(
    'username'         => 'routeMe1',
    'password'         => 'yF!+KssJr-Ca8yM=NX',
    'requireddata'     => 'confirmVehicleInspection',
    'vehicle_reg'      => 'HX123654',
    'question_details' => array(
                                'vehicle_reg'      => 'HX123654',
                                'question'         => 'current kms',
                                'answer'           => '123456',
                                'date_time'        => '2023-03-09 11:28:21'),
                          array(     
                                'vehicle_reg'      => 'HX123654',
                                'question'         => 'Reg Number',
                                'answer'           => 'HX123654',
                                'date_time'        => '2023-03-09 11:28:21'),
                         array(          
                               'vehicle_reg'      => 'HX123654',
                               'question'         => 'Cleanliness',
                               'answer'           => 'POOR',
                               'date_time'        => '2023-03-09 11:28:21'),
                         array(
                               'vehicle_reg'      => 'HX123654',
                               'question'         => 'Windows',
                               'answer'           => 'GOOD',
                               'date_time'        => '2023-03-09 11:28:21'));

$payload = json_encode($data);

echo "<h4>Data Array</h4>";
echo "<pre>";
print_r($data);
echo "<br>";
echo "</pre>";
echo "<hr>";

echo "<h4>Json Payload</h4>";
echo $payload;
echo "<br>";
echo "<br>";

echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/e/kKre3CSva3E/char.php");
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

// echo "<h4>Result</h4>";
print_r($result);

// close cURL resource, and free up system resources
curl_close($ch);


?>
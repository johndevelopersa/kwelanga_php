<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apiCallPostDocumentConfirm.php";

$data = array(
              'username'     => 'PrimaPasta',
              'password'     => 'yF!+KssJr-Ca8yM=NX',
              'requireddata' => 'postDocumentConfirm',
              'principalUid' => '450',
              'userEmail'    => 'apiTest@kwelangasolutions.co.za',
              'dateTime'     => date('Y-m-d H:i:s'),   
              'documentList' => Array(Array(
                                            'type'           => 'CREDIT',
                                            'documentNumber' => 'PPBKC700000',
                                            'Status'         =>  'S'),
                                      Array(
                                            'type'           => 'CREDIT',
                                            'documentNumber' => 'PPBKC700001',
                                            'Status'         =>  'S'),
                                      Array(
                                            'type'           => 'INVOICE',
                                            'documentNumber' => 'PPBKI500003',
                                            'Status'         =>  'S'),
                                      Array(
                                            'type'           => 'INVOICE',
                                            'documentNumber' => 'PPBKI500004',
                                            'Status'         =>  'S'),
                                      Array(
                                            'type'           => 'INVOICE',
                                            'documentNumber' => 'PPBKI500005',
                                            'Status'         =>  'S') 
                                      )
             );

// *****************************************************************************************


$payload = json_encode($data, true);

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
echo "<hr>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/e/kKre3CSva3E/chariii.php");
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

//echo "<br><pre>";
//print_r(curl_getinfo($ch));

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    print_r($error_msg);
}

echo "<br>";
echo "<h4>Result</h4>";
print_r($result);
echo "<br><br><hr><br><br>";

//print_r(json_decode($result, true ));

// close cURL resource, and free up system resources
curl_close($ch);


?>
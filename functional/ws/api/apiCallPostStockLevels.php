<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apiCallPostStockLevels.php";

$data = array(
              'username'     => 'APITest',
              'password'     => 'yF!+KssJr-Ca8yM=NX',
              'requireddata' => 'postStockLevels',
              'principalUid' => '271',
              'principal'    => 'Test Principal',
              'userEmail'    => 'apiTest@kwelangasolutions.co.za',
              'wareHouseCode'=> '179',
              'warehouse'    => 'Kwelanga Warehouse',
              'DateTime'     => date('Y-m-d H:i:s'),    
              'productLines' => Array(Array('ProdCode'       => 'SQCAS',
                                            'Product'        => 'CARAMEL 1X30',
                                            'AvailableLevel' => '29'),
                                      Array('ProdCode'       => 'SQCHS',
                                            'Product'        => 'CHOCOLATE 1X30',
                                            'AvailableLevel' => '29'),
                                      Array('ProdCode'       => 'SQSTS',
                                            'Product'        => 'STRAWBERRY 1X30',
                                            'AvailableLevel' => '29'))
         );    

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

// echo "<br><pre>";
// print_r(curl_getinfo($ch));

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    print_r($error_msg);
}

echo "<br>";
echo "<h4>Result</h4>";
print_r($result);
echo "<br><br><hr><br><br>";

// print_r(json_decode($result, true ));

// close cURL resource, and free up system resources
curl_close($ch);


?>
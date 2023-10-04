<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apiCallKosOrderSit.php";


$data = Array
(
    'username'             => 'SIT',
    'password'             => 'yF!+KssJr-Ca8yM=NX',
    'requireddata'         => 'postKosOrder',
    'principalUid'         => '304',
    'userEmail'            => 'sedick.isaacs@ashtech.co.za',
    'capturedBy'           => 'zareenam@stepintime.co.za',
    'captureByLocation'    => '-34.01601349347342, 18.50630025389674',
    'OrderReference'       => 'C006',
    'storeId'              => '891261637',
    'storeName'            => 'Rebel Fruit & Veg - Benoni',
    'purchaseOrderNumber'  => '',
    'captureDateTime'      => '2023-06-23 10:11:11',
    'orderDate'            => '2023-06-23',
    'requiredDate'         => '2023-06-26',
    'deliveryInstructions' => '',
    'type'                 => 'ORDER',
    'photoUrl'             => '',
    'signitureUrl'         => '',
    'detailLines'          => Array    
                                   (Array
                                         (
                                          'prodCode'           => 'OF4',
                                          'productDescription' => 'SQEEZY CARAMEL 1X30',
                                          'orderQuantity'      => '25.00',
                                          'sellingPrice'       => '195.20'
                                          )
                                 )
);
 
$payload = json_encode($data);

echo "<h4>Payload</h4>";
echo $payload;
echo "<br>";
echo "<br>";

print_r($data);
echo "<br>";
echo "<hr>";
echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";

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

print_r($result);

// close cURL resource, and free up system resources
curl_close($ch);







?>
<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apiCallKosOrder.php";


$data = Array
(
    'username'             => 'MAX',
    'password'             => 'yF!+KssJr-Ca8yM=NX',
    'requireddata'         => 'postKosOrder',
    'principalUid'         => '361',
    'userEmail'            => 'bruwer.melany@gmail.com',
    'capturedBy'           => 'bruwer.melany@gmail.com',
    'captureByLocation'    => '-26.0022605, 28.1124739',
    'OrderReference'       => 'eb127488-2d40-4fcf-8177-74b67b74c3mm',
    'storeId'              => '891305132',
    'storeName'            => 'Rebel Fruit & Veg - Benoni',
    'purchaseOrderNumber'  => 'jjj',
    'captureDateTime'      => '2023-06-28 16:23:19',
    'orderDate'            => '2023-06-28',
    'requiredDate'         => '',
    'deliveryInstructions' => '',
    'type'                 => 'ORDER',
    'photoUrl'             => 'https://kwelanga.appco.online/uploads/form_orders/14d8b647ecea0d430106bcf5bde3a7307c8f119702a4.jpg',
    'signitureUrl'         => 'https://kwelanga.appco.online/signiture/23456.jpg',
    'detailLines'          => Array    
                                   (Array
                                         (
                                          'prodCode'           => 'EEB330',
                                          'productDescription' => 'SQEEZY CARAMEL 1X30',
                                          'orderQuantity'      => '500',
                                          'sellingPrice'       => '250'
                                          )
                                   ));
 
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
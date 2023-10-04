<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/callPostNewOrder.php";


$data = Array
              (
               'username'             => 'APITest',
               'password'             => 'yF!+KssJr-Ca8yM=NX',
               'requireddata'         => 'postNewOrder',
               'principalUid'         => '271',
               'userEmail'            => 'apitest@kwelangasolutions.co.za',
               'OrderReference'       => 'TESTP30022585',
               'customerAccountCode'  => 'BMAR002',
               'customerName'         => 'OK Airport Manhattan Mini',
               'physicalAddress1'     =>  '' ,
               'physicalAddress2'     =>  '',
               'physicalAddress3'     =>  'Cape Town',
               'physicalAddress4'     =>    '7441',
               'region'               =>    'Gauteng',
               'shipping'             => '0',
               'orderDiscountType'    => 'Amount Off',
               'orderDiscountAmount'  => '300',
               'emailAddress'         => 'kwelanga@kewlangasolutions.co.za',
               'contactNumber'        => '011 969 2405',
               'purchaseOrderNumber'  => '12052023',
               'orderDate'            => '2023-05-12',
               'requiredDate'         => '2023-05-12',
               'deliveryInstructions' => '',
               'documentType'         => 'ORDER',
               'detail_lines'         => Array(Array
                                                    (
                                                     'product_code' => 'SQCAS',
                                                     'product_description' => 'CARAMEL 1X30',
                                                     'order_quantity' => '6',
                                                     'selling_price' => '83.00',
                                                     'line_discount_type' => 'Amount Off',
                                                     'line_discount_amount' => '100',
                                                     'line_nett_price' => '0.00',
                                                     'vat_rate' => '15',
                                                     'warehouse' => '179',
                                                     'items_per_case' => 'EA',
                                                     ),
                                                Array
                                                     (
                                                      'product_code' => 'SQCHS',
                                                      'product_description' => 'CHOCOLATE 1X30',
                                                      'order_quantity' => '3',
                                                      'selling_price' => '210.00',
                                                      'line_discount_type' => 'Amount Off',
                                                      'line_discount_amount' => '100',
                                                      'line_nett_price' => '0.00',
                                                      'vat_rate' => '15',
                                                      'warehouse' => '179',
                                                      'items_per_case' => 'EA',
                                                      ),
                                                Array
                                                     (
                                                     'product_code' => 'SQSTS',
                                                     'product_description' => 'STRAWBERRY 1X30',
                                                     'order_quantity' => '3',
                                                     'selling_price' => '175.00',
                                                     'line_discount_type' => 'Amount Off',
                                                     'line_discount_amount' => '100',
                                                     'line_nett_price' => '0.00',
                                                     'vat_rate' => '15',
                                                     'warehouse' => '179',
                                                     'items_per_case' => 'EA',
                                                     )
                                               )
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
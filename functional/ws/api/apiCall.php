<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apicall.php";

$data = array(
    'username'     => 'APITest',
    'password'     => 'yF!+KssJr-Ca8yM=NX',
    'requireddata' => 'postOrder',
    'principalId'  => '999',
    'reference_number'=>'ab123456',
    'customer_account_code'=> 'SHOP001',
    'customer_name'=>'abcdefg' , 
    'physical_address_1'=>'SHOPRITE BOOYSEN PARK   8631',
    'physical_address_2'=>'BOOYSEN PARK',
    'physical_address_4'=>'Port Elizabeth',
    'physical_address_5'=>'NULL',
    'shipping'=>'40.00',
    'order_discount_type'=>'percentage',
    'order_discount_amount'=>'5',
    'email_address'=>'alan@kos.co.za'	,
    'contact_number'=>'0830000000',
    'purchase_order_number'=>'abc123',
    'order_date'=>'2022-01-01',
    'required_date'=>'2022-01-01',
    'delivery-instructions'=>'Deliver after 2 at Main Gate',
    'detail_lines'=>[
                     'product_code'=>' 12369550',
                     'product_description'=>'Organic Lemon Tea 24X250ML',
                     'order_quantity'=>'1',
                     'selling_price'=>'100.00',
                     'line_discount_type'=>'percentage',
                     'line_discount_amount'=>'5',
                     'line_nett_price'=>'95.00',
                     'vat_rate'=>'15.00'
                     ],
                     [
                     'product_code'=>' 12369555',
                     'product_description'=>'Organic Ginja Tea 24X250ML',
                     'order_quantity'=>'1',
                     'selling_price'=>'100.00',
                     'line_discount_type'=>'percentage',
                     'line_discount_amount'=>'5',
                     'line_nett_price'=>'95.00',
                     'vat_rate'=>'15.00'
                     ]);

$payload = json_encode($data);
echo "v1.2";
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

echo "<h4>Result</h4>";
print_r($result);

// close cURL resource, and free up system resources
curl_close($ch);


?>
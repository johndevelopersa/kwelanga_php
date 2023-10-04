<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apicallorder.php";


$data = Array
(
    'username' => 'BMF',
    'password' => 'yF!+KssJr-Ca8yM=NX',
    'requireddata' => 'postOrder',
    'principalId' => '290',
    'reference_number' => 'SO23BM30022585',
    'customer_account_code' => 'BMAR002',
    'customer_name' => 'OK Airport Manhattan Mini',
    'physical_address_1' =>'' ,
    'physical_address_2' =>'',
    'physical_address_4' => 'Cape Town',
    'physical_address_5' => '7441',
    'shipping' => '0',
    'order_discount_type' => 'Amount Off',
    'order_discount_amount' => '300',
    'email_address' => 'kwelanga@bmfoods.co.za',
    'contact_number' => '021 551 3733',
    'purchase_order_number' => '12052023',
    'order_date' => '2023-05-12',
    'required_date' => '2023-05-12',
    'deliveryinstructions' => '',
    'document_type' => 'ORDER',
    'detail_lines' => Array
        (Array
                (
                    'product_code' => 'FCPI022',
                    'product_description' => 'Pie Place - Pepper Steak Bite 120X45g',
                    'order_quantity' => '6',
                    'selling_price' => '83.00',
                    'line_discount_type' => 'Amount Off',
                    'line_discount_amount' => '100',
                    'line_nett_price' => '0.00',
                    'vat_rate' => '15',
                    'warehouse' => 'BM309',
                    'items_per_case' => 'EA',
                ),
         Array
                (
                    'product_code' => 'FCPK100',
                    'product_description' => 'Pie Place - Mini Roast Chicken Pies Frozen 90x45g',
                    'order_quantity' => '3',
                    'selling_price' => '210.00',
                    'line_discount_type' => 'Amount Off',
                    'line_discount_amount' => '100',
                    'line_nett_price' => '0.00',
                    'vat_rate' => '15',
                    'warehouse' => 'BM309',
                    'items_per_case' => 'EA',
                ),

         Array
                (
                    'product_code' => 'FCPK103',
                    'product_description' => 'Pie Place - Mini Mince Beef & Cheese Pies Frozen 90x45g',
                    'order_quantity' => '3',
                    'selling_price' => '175.00',
                    'line_discount_type' => 'Amount Off',
                    'line_discount_amount' => '100',
                    'line_nett_price' => '0.00',
                    'vat_rate' => '15',
                    'warehouse' => 'BM309',
                    'items_per_case' => 'EA',
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

print_r($result);

// close cURL resource, and free up system resources
curl_close($ch);







?>
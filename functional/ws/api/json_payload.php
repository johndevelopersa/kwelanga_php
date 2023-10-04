<?php

// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/json_payload.php";

$data = array(
              'username'=>'Voqado',                                     // Mandatory (All)
              'password'=>'T%rk3y345',                                  // Mandatory (All)
              'requireddata'=>'postOrder',                              // Mandatory (postOrder, getStockLevel)
              'userEmail'=>'alan@kwelangasolutions.co.za',              // Optional
              'userPassword'=>'T5rK3y6478',                             // Optional
              'principalUid'=>'271',                                    // Mandatory (All  Etosha - 64)
              'principalName'=>'SANLOUW PRODUCT DISTRIBUTION CC',       // Optional
              'orderReference'=>'12345678',                             // Mandatory (postOrder - Tranacation reference on request )
              'customerID'=>'891239301',                                // Optional  (postOrder)
              'customerName'=>'Dis-Chem Pharmacy. Kalahari Mall',       // Mandatory (postOrder)
              'customerDelAdd1'=>'1 Jones Road ',                        // Mandatory (postOrder)
              'customerDelAdd2'=>'1 Jones Road ',                        // Mandatory (postOrder)
              'customerDelAdd3'=>'1 Jones Road ',                        // Mandatory (postOrder)
              'customerDelPost'=>'1514',                                 // Mandatory (postOrder)
              'customerVat'=>'44444444',                                 // Optional  (postOrder) 
              'customerEmail '=>'1514',                                  // Mandatory (postOrder) 
              'customerContactNo'=>'0119692406',                         // Mandatory (postOrder) 
              'orderDate'=>'2021-01-22',                                // Mandatory (postOrder) 
              'captureDate'=>'2021-01-22',                              // Mandatory (postOrder, getStockLevel) 
              'customerReference'=>'Test PO Number',                    // Mandatory (postOrder) 
              'specialInstructions'=>'Deliver Friday before 9',         // Mandatory (postOrder) 
              'paymentConfirmed',                                        // Mandatory (postOrder - Y/N) 
              'order_lines'=>array(                                     // Mandatory (postOrder, getStockLevel) 
                         'orderReference'=>'12345678',                  // Mandatory (postOrder, getStockLevel Same as above orderReference )
                         'orderLineNo'=>'1',                            // Mandatory (postOrder, getStockLevel Number of product lines )
                         'ProdCode'=>'CHEP',                            // Mandatory (postOrder, getStockLevel)
                         'Product'=>'CHEP Blue Pallet',                 // Mandatory (postOrder, getStockLevel)
                         'quantity'=>'2',                               // Mandatory (postOrder, getStockLevel)
                         'netPrice'=>''),                               // Mandatory (postOrder)
                          array(                                        // Mandatory (postOrder, getStockLevel)                               
                         'orderReference'=>'12345678',                  // Mandatory (postOrder, getStockLevel Same as above orderReference ) 
                         'orderLineNo'=>'2',                            // Mandatory (postOrder, getStockLevel Number of product lines )      
                         'ProdCode'=>'RBALC',                           // Mandatory (postOrder, getStockLevel)                               
                         'Product'=>'Rim Block Alpine',                 // Mandatory (postOrder, getStockLevel)                               
                         'orderQuantity'=>'2',                          // Mandatory (postOrder, getStockLevel)                               
                         'netPrice'=>''),                               // Mandatory (postOrder)                               
                          array(                                        // Mandatory (postOrder, getStockLevel)                               
                         'orderReference'=>'12345678',                  // Mandatory (postOrder, getStockLevel Same as above orderReference ) 
                         'orderLineNo'=>'3',                            // Mandatory (postOrder, getStockLevel Number of product lines )      
                         'ProdCode'=>'SAN1',                            // Mandatory (postOrder, getStockLevel)                               
                         'Product'=>'Sanitizer',                        // Mandatory (postOrder, getStockLevel)                               
                         'orderQuantity'=>'2',                          // Mandatory (postOrder, getStockLevel)                               
                         'netPrice'=>'')                                // Mandatory (postOrder)                               
);
echo "<br>";
echo "<pre>";
print_r($data);
echo "</pre>";

$payload = json_encode($data);

echo "<br>";
echo "<h4>Payload</h4>";
echo "<br>";
echo $payload;
echo "<br>";

$response = array(
                  'resultStatus'=>"E",                                  // Mandatory "E" or "S" 
                  'resultMessage'=>"See included List",                 // Mandatory "E" or "S"
                  'orderReference'=>'From Request',                     // Mandatory From Above
                  'processedReference'=>'111111111' ,                    // Mandatory Supplied by Kwelanga
                  'detail_lines'=>array(                                // Mandatory (postOrder, getStockLevel) 
                                        'orderReference'=>'12345678',   // Mandatory (postOrder, getStockLevel Same as above orderReference )
                                        'orderLineNo'=>'1',             // Mandatory (postOrder, getStockLevel Number of product lines )
                                        'ProdCode'=>'CHEP',             // Mandatory (postOrder, getStockLevel)   
                                        'AvailableStock'=>'100'         // Mandatory (postOrder, getStockLevel)    
                  ));

echo "<br>";
echo "<pre>";
print_r(response);
echo "</pre>";

$response_j = json_encode($response);

echo "<br>";
echo "<h4>Response</h4>";
echo "<br>";
echo $response_j;
echo "<br>";

/* Error Messages
	Invalid Login Credentials
	Password Incorrect – Login Error
	No Customer Name
              No Customer Address 1
              No Customer Address 2
	No Post Code
	Email Address Blank or invalid
		Valid Email address 
			Contains @
			At Least one ‘.’
               No contact number 
		Minimum 10 Characters 
	No Product selected
	Invalid Quantity (Must be above 0)
	No Price Supplied.
	Insufficient Stock available

*/

?>
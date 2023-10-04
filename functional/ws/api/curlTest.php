<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// $callresult = "";
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/curlTest.php
/*


 
•	API URl
o	https://cloudplatform.iconnix.co.za/WMSApi/WS/WMSService.asmx?wsdl
•	Help Page
o	https://cloudplatform.iconnix.co.za/WMSApi/Help/Api/POST-api-Order

https://cloudplatform.iconnix.co.za/WMSApi/api/Order

{
  "OrderNr": "sample string 1",
  "Warehouse": "sample string 2",
  "CustomerName": "sample string 3",
  "CustomerContact": "sample string 4",
  "CustomerEmail": "sample string 5",
  "CustomerStreet1": "sample string 6",
  "CustomerStreet2": "sample string 7",
  "CustomerTown": "sample string 8",
  "CustomerCountry": "sample string 9",
  "CustomerPostalCode": "sample string 10",
  "CustomerCity": "sample string 11",
  "CustomerBuildingName": "sample string 12",
  "OrderDate": "sample string 13",
  "DeliveryType": "sample string 14",
  "Reference1": "sample string 15",
  "Reference2": "sample string 16",
  "Reference3": "sample string 17",
  "Reference4": "sample string 18",
  "Reference5": "sample string 19",
  "Note": "sample string 20",
  "OrderDetails": [
    {
      "Principle": "sample string 1",
      "ItemCode": "sample string 2",
      "Quantity": "sample string 3"
    },
    {
      "Principle": "sample string 1",
      "ItemCode": "sample string 2",
      "Quantity": "sample string 3"
    }
  ]
}

*/
$data = array('OrderNr'              => '00357900',
              'Warehouse'            => 'Demo',
              'CustomerName'         => 'PNP FAMILY WAVERLY NF08',      // sample string 3",
              'CustomerContact'      => '',      // sample string 4",
              'CustomerEmail'        => '',      // sample string 5",
              'CustomerStreet1'      => '',      // sample string 6",
              'CustomerStreet2'      => '',      // sample string 7",
              'CustomerTown'         => '',      // sample string 8",
              'CustomerCountry'      => '',      // sample string 9",
              'CustomerPostalCode'   => '',      // sample string 10",
              'CustomerCity'         => '',      // sample string 11",
              'CustomerBuildingName' => '',      // sample string 12",
              'OrderDate'            => '2023-03-09',      // sample string 13",
              'DeliveryType'         => '',      // sample string 14",
              'Reference1'           => '',      // sample string 15",
              'Reference2'           => '',      // sample string 16",
              'Reference3'           => '',      // sample string 17",
              'Reference4'           => '',      // sample string 18",
              'Reference5'           => '',      // sample string 19",
              'Note'                 => '',      // sample string 20",
              'OrderDetails'         => array(array(
                                              'Principle' => 'Heartland Foods',   //  sample string 1",
                                              'ItemCode'  => 'HF038',   //  sample string 2",
                                              'Quantity'  => '1'))
           )  ;
           
echo "<br>";

$username  = "concargowms";
$password  = 'u2)O}t|SO';
$Groupname = 'ConCargoWMS';
echo "<pre>";
print_r($data);
echo "<br>";
echo "<br>";
$payload =json_encode($data) ;

echo $payload;


echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://cloudplatform.iconnix.co.za/WMSconcargoApi/api/Order");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);

$curlDebug = curl_getinfo($ch);

 echo "<br><pre>";
 print_r(curl_getinfo($ch));

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    print_r($error_msg);
}

// echo "<h4>Result</h4>";
print_r($result);

// close cURL resource, and free up system resources
curl_close($ch);

/*

POST api/Order
Request Information
URI Parameters
None.

Body Parameters
Order
Name	Description	Type	Additional information
OrderNr	
string	
None.

Warehouse	
string	
None.

CustomerName	
string	
None.

CustomerContact	
string	
None.

CustomerEmail	
string	
None.

CustomerStreet1	
string	
None.

CustomerStreet2	
string	
None.

CustomerTown	
string	
None.

CustomerCountry	
string	
None.

CustomerPostalCode	
string	
None.

CustomerCity	
string	
None.

CustomerBuildingName	
string	
None.

OrderDate	
string	
None.

DeliveryType	
string	
None.

Reference1	
string	
None.

Reference2	
string	
None.

Reference3	
string	
None.

Reference4	
string	
None.

Reference5	
string	
None.

Note	
string	
None.

OrderDetails	
Collection of Detail	
None.

Request Formats
application/json, text/json
Sample:
{
  "OrderNr": "sample string 1",
  "Warehouse": "sample string 2",
  "CustomerName": "sample string 3",
  "CustomerContact": "sample string 4",
  "CustomerEmail": "sample string 5",
  "CustomerStreet1": "sample string 6",
  "CustomerStreet2": "sample string 7",
  "CustomerTown": "sample string 8",
  "CustomerCountry": "sample string 9",
  "CustomerPostalCode": "sample string 10",
  "CustomerCity": "sample string 11",
  "CustomerBuildingName": "sample string 12",
  "OrderDate": "sample string 13",
  "DeliveryType": "sample string 14",
  "Reference1": "sample string 15",
  "Reference2": "sample string 16",
  "Reference3": "sample string 17",
  "Reference4": "sample string 18",
  "Reference5": "sample string 19",
  "Note": "sample string 20",
  "OrderDetails": [
    {
      "Principle": "sample string 1",
      "ItemCode": "sample string 2",
      "Quantity": "sample string 3"
    },
    {
      "Principle": "sample string 1",
      "ItemCode": "sample string 2",
      "Quantity": "sample string 3"
    }
  ]
}
application/xml, text/xml
Sample:
<Order xmlns:i="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://schemas.datacontract.org/2004/07/WMSAPI.Models">
  <CustomerBuildingName>sample string 12</CustomerBuildingName>
  <CustomerCity>sample string 11</CustomerCity>
  <CustomerContact>sample string 4</CustomerContact>
  <CustomerCountry>sample string 9</CustomerCountry>
  <CustomerEmail>sample string 5</CustomerEmail>
  <CustomerName>sample string 3</CustomerName>
  <CustomerPostalCode>sample string 10</CustomerPostalCode>
  <CustomerStreet1>sample string 6</CustomerStreet1>
  <CustomerStreet2>sample string 7</CustomerStreet2>
  <CustomerTown>sample string 8</CustomerTown>
  <DeliveryType>sample string 14</DeliveryType>
  <Note>sample string 20</Note>
  <OrderDate>sample string 13</OrderDate>
  <OrderDetails>
    <Detail>
      <ItemCode>sample string 2</ItemCode>
      <Principle>sample string 1</Principle>
      <Quantity>sample string 3</Quantity>
    </Detail>
    <Detail>
      <ItemCode>sample string 2</ItemCode>
      <Principle>sample string 1</Principle>
      <Quantity>sample string 3</Quantity>
    </Detail>
  </OrderDetails>
  <OrderNr>sample string 1</OrderNr>
  <Reference1>sample string 15</Reference1>
  <Reference2>sample string 16</Reference2>
  <Reference3>sample string 17</Reference3>
  <Reference4>sample string 18</Reference4>
  <Reference5>sample string 19</Reference5>
  <Warehouse>sample string 2</Warehouse>
</Order>
application/x-www-form-urlencoded
Sample:
Sample not available.

Response Information
Resource Description
HttpResponseMessage
Name	Description	Type	Additional information
Version	
Version	
None.

Content	
HttpContent	
None.

StatusCode	
HttpStatusCode	
None.

ReasonPhrase	
string	
None.

Headers	
Collection of Object	
None.

RequestMessage	
HttpRequestMessage	
None.

IsSuccessStatusCode	
boolean	
None.

© 2023 - WMS.NET API*/
?>
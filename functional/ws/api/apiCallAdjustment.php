<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// get the passed PARAMS
$json = json_decode(file_get_contents('php://input'), true);

// build up the payload
$payload = json_encode(array(
    'username'        => $json["USERNAME"],
    'password'        => $json["PASSWORD"],
    'requireddata'    => $json["REQUIREDDATA"],
    'principalId'     => $json["PRINCIPLEID"],
    'referenceNumber' => $json["REFERENCENUMBER"],
    'arrivalDateTime' => date('Y-m-d H:i:s'),
    'detailLines' => $json["DETAILLINES"],
));

file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/adj.txt', $payload, FILE_APPEND);
// make the request to make the stock adjustment
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

// setup the response data
$response = curl_exec($ch);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    print_r($error_msg);
}

// return the response to the client
return $response;

// close cURL resource, and free up system resources
curl_close($ch);







?>
<?php
/*
 *
 * SOAP TEST Client : Dated 01.09.11
 *
 */

require_once('lib/nusoap.php');	//include nusoap lib

//connection param
//$client = new nusoap_client("http://www1.retailtrading.net/ws/rttsoap.php?ws=ORDERSTEST", false); // IP address does NOT work !!
$client = new nusoap_client("http://127.0.0.1/RetailTradingTest/ws/rttsoap.php?ws=GIORDERS", false);
//$client = new nusoap_client("http://live.retailtrading.net/ws/rttsoap.php?ws=ORDERS", false);
//$client = new nusoap_client("http://test.retailtrading.net/ws/rttsoap.php?ws=ORDERS", false);
$err = $client->getError();
$client->xml_encoding = 'UTF-8';


/*
//test using RPC call
$test = $client->call('echoString',array('string'=>'hello world'),'http://soapinterop.org/');

//test using SEND data
$test = $client->send('<?xml version="1.0" encoding="UTF-8"?><SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/"><SOAP:Header/><SOAP:Body> xmlns:ns6037="http://soapinterop.org/"><string xsi:type="xsd:string" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">hello world</string></ns6037:echoString></SOAP:Body></SOAP:Envelope>');
*/

//data expected to receive.
//include('sendingdata.php');
$sendXML = trim(file_get_contents("templates/PnpGoodsInwardExample.xml"));
$client->setCredentials("PnPWS","ws012011");
// DO NOT FORGET that this requires an .htaccess rewrite rule : RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
$test = $client->send($sendXML);


if ($client->fault) {
	echo '<h2>Fault</h2><pre>'; print_r($test); echo '</pre>';
} else {
	$err = $client->getError();
	if ($err) {
		echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
		echo '<h2>Result</h2><pre>'; print_r($test);
		echo '</pre>';
	}
}

echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';


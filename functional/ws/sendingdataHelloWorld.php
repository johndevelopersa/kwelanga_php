<?php

$sendXML = '<?xml version="1.0"?>
<soap:Envelope
xmlns:soap="http://www.w3.org/2001/12/soap-envelope"
soap:encodingStyle="http://www.w3.org/2001/12/soap-encoding">
<soap:Body xmlns:m="http://www.example.org/stock">
  <m:echoString>
    <m:StockName>Hello World !</m:StockName>
  </m:echoString>
</soap:Body>
</soap:Envelope>';


?>
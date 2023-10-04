<?php
/*
 *
 * RTT SOAP Server : Dated 01.09.11
 *
 */
 
file_put_contents("wslog/fulldump.".date("Y.m.d").".log", 
									date("Y-m-d H:i:s")."|".var_export($_REQUEST,true)."|".var_export(getallheaders(),true)."|".file_get_contents("php://input")."\r\n", 
									FILE_APPEND); 
 
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once('ws_logger.php');

require_once('lib/nusoap.php');	//include nusoap lib

$dbConn = new dbConnect();	//create db connection
$dbConn->dbConnection();

$miscDAO = new MiscellaneousDAO($dbConn);


/*SOAP SERVER
 *
 * START
 *
 */
$rtt_ws = new soap_server();
$rtt_ws->configureWSDL('RTTSOAP','urn:ean.ucc:order:2');
$rtt_ws->xml_encoding = 'UTF-8';	//Receiving encoding


//Generic Server Echo
$rtt_ws->register('echoString',
                  array('string'=>'xsd:string'),
                  array('response'=>'xsd:string'),
                  '',
                  'echoString'
                  );

$rtt_ws->register('StandardBusinessDocument',
                  array('string'=>'xsd:string'),
                  array(''=>'xsd:string'),
                  '',
                  'sh:StandardBusinessDocument',
                  false,
                  'literal'
                  );

$rtt_ws->register('UpdateDeliveryNotice',
                  array('string'=>'xsd:string'),
                  array('UpdateDeliveryNoticeResult'=>'xsd:string'),
                  '',
                  'UpdateDeliveryNotice',
                  false,
                  'literal'
                  );

// testing only for checkers
$rtt_ws->register('getNewOrders',
                  array('str'=>'xsd:string'),
                  array('getNewOrdersResponse'=>'xsd:string'),
                  '',
                  'm:getNewOrdersResponse',
                  false,
                  'literal'
                  );
// testing only for checkers
$rtt_ws->register('ackOrders',
                  array('str'=>'xsd:string'),
                  array('ackOrdersResponse'=>'xsd:string'),
                  '',
                  'm:ackOrders',
                  false,
                  'literal'
                  );


if (isset($_REQUEST["WS"])) {
  $pWS = $_REQUEST["WS"];
} else if (isset($_REQUEST["ws"])) {
  $pWS = $_REQUEST["ws"];
} else {
  $pWS = false;
}

$isTest=(in_array($pWS,array("ORDERSTEST","GIORDERSTEST"))?true:false);

/*
$output = (isset($HTTP_RAW_POST_DATA)) ? $HTTP_RAW_POST_DATA : 'ERROR';
if (($output=='ERROR') && (!isset($_REQUEST["WSDL"]))) {
	WSlogger::wsServerLog('ERROR','INVALID SOAP STRUCTURE RECEIVED','','','','');
}
*/
$output = $HTTP_RAW_POST_DATA = file_get_contents("php://input");

// check if username / password was sent as HTTP header instead
// PHP CGI needs a rewrite command RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L] to create HTTP_AUTHORIZATION which populates PHP_AUTH*
// localhost also prefixes var name with "REDIRECT_"
$authStr=(isset($_SERVER['HTTP_AUTHORIZATION']) && ($_SERVER['HTTP_AUTHORIZATION']!=""))?$_SERVER['HTTP_AUTHORIZATION']:((isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && ($_SERVER['REDIRECT_HTTP_AUTHORIZATION']!=""))?$_SERVER['REDIRECT_HTTP_AUTHORIZATION']:"");
if (isset($_SERVER['PHP_AUTH_USER']) && ($_SERVER['PHP_AUTH_USER']!="")) {
	$userName=(isset($_SERVER['PHP_AUTH_USER']))?$_SERVER['PHP_AUTH_USER']:"";
	$passWord=(isset($_SERVER['PHP_AUTH_PW']))?$_SERVER['PHP_AUTH_PW']:"";
} else {
  $pArr=explode(':',base64_decode(substr($authStr,6)));
  if ((isset($pArr[0])) && (isset($pArr[1]))) {
	 list($userName,$passWord)=$pArr;
  } else {
    $userName="";
    $passWord="";
  }
}

// debugging
if (($pWS=="GIORDERS") || ($pWS=="GIORDERSTEST")) {
  WSlogger::wsDebugReceivedLog($authStr."-".$userName.":".$passWord." ".$HTTP_RAW_POST_DATA); // only used when problems occur.
}

$output = preg_replace("/[\n]?[\r]?[<]StandardBusinessDocuments[>][\n]?[\r]?/", "", $output);
$output = preg_replace("/[\n]?[\r]?[<][\/]StandardBusinessDocuments[>][\n]?[\r]?/", "", $output);
//echo "xxx".$output."xxx";
$rtt_ws->service($output);


/*
 * END
 */


/*SOAP SERVER
 *
 * FUNCTIONS
 *
 */

function validArrayStructure($object, $tagPath) {
	preg_match_all("/\[.*?\]/",$tagPath,$keys);
	$prevArr=$object;
	foreach ($keys[0] as $k) {
		$k=preg_replace('/[\[\]\'\"]/', '', $k);
		if ((!is_array($prevArr)) || (!isset($prevArr["{$k}"]))) {
			return false;
		}
		$prevArr=$prevArr["{$k}"];
	}
	return true;
}

//Generic Echo Function
function echoString($str = ''){
	if(!empty($str)){
		return 'You said "'.$str.'"';
	} else {
		return 'You said nothing!';
	}
}

function getNewOrders($str = ''){
  /*
  $responseXML = '<?xml version="1.0"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
	SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
	<SOAP-ENV:Header></SOAP-ENV:Header>
	<SOAP-ENV:Body>
		<m:getNewOrdersResponse xmlns:m="https://www.shoprite.co.za/b2b/soap/soap:getNewOrdersResponse/">
			<Doc>
			<Orders>
      <Order>
						...
						...
			</Doc>
		</m:getNewOrdersResponse>
	</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';

  header('Content-Type: text/html');
  //header('Content-Length: '.strlen($responseXML));
  //header('Content-Type: text/xml; charset=UTF-8');
  header('Set-Cookie: ASPSESSIONIDCCAARSRQ=JFJDNOFCODJOGONNHGFIEGFB; path=/');
  header('Cache-control: private');
  // header('Transfer-Encoding: chunked');

  echo $responseXML;
  die();
*/
}

function ackOrders($str = ''){

  $responseXML = '<?xml version="1.0"?>
    <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
SOAP-ENV:encodingstyle="http://schemas.xmlsoap.org/soap/encoding/">
       <SOAP-ENV:Header/>
       <SOAP-ENV:Body>
        <m:ackOrdersResponse xmlns:m= "urn:https://www.shoprite.co.za/b2b/soap/soap:ackOrdersResponse/">4256066017,4109055593,4364062314,5144019008,642214
              </m:ackOrdersResponse>
</SOAP-ENV:Body>
   </SOAP-ENV:Envelope>';

  header('Content-Length: '.strlen($responseXML));
  header('Content-Type: text/html; charset=UTF-8');

  echo $responseXML;
  die();

}


// vendor must be setup so authenticate must be called from within main processing function such as StandardBusinessDocument()
function authenticate($adaptorGS1){

	global $rtt_ws, $miscDAO, $userName, $passWord;

	// skip if username/password was sent as HTTP header
	if ($userName=="") {
		if ((!isset($rtt_ws->requestArray[1])) ||
			(!validArrayStructure($rtt_ws->requestArray[1],"['result']['BasicAuth']['Name']")) ||
			(!validArrayStructure($rtt_ws->requestArray[1],"['result']['BasicAuth']['Password']"))
		) {
			$adaptorGS1->errorDescription = 'Invalid Authentication Structure in header';
			$adaptorGS1->errorParty = 'SOAP: Client';
			$adaptorGS1->errorStatus = 1;
			return false;
		}

		$userName=$rtt_ws->requestArray[1]['result']['BasicAuth']['Name'];
		$passWord=$rtt_ws->requestArray[1]['result']['BasicAuth']['Password'];
	}

	$venderResult = $miscDAO->getVenderLoginUID($userName, $passWord, $adaptorGS1->vendorGln);

	if(isset($venderResult[0]['uid'])){	//isset for mysql query failures
	  if (isset($adaptorGS1->vendorUid)) {
	    $adaptorGS1->vendorUid = $adaptorGS1->postingOrdersHoldingTO->vendorUid = $venderResult[0]['uid'];
	  } else {
	    $adaptorGS1->vendorUId = $adaptorGS1->postingRemittanceTO->vendorUId = $venderResult[0]['uid'];
	  }
		return true;
	} else {
		$adaptorGS1->errorDescription = 'Vendor Login Failure';
		$adaptorGS1->errorParty = 'SOAP: Client';
		$adaptorGS1->errorStatus = 1;
		return false;
	}
}


function StandardBusinessDocument(){
	global $rtt_ws, $ROOT, $PHPFOLDER, $dbConn, $HTTP_RAW_POST_DATA, $isTest, $pWS;

	/*****GS1 Adaptor*****
	 *
	 * Validation, and response is built into the adaptor
	 * Pass the dbconnection and the requestArray nusoap builds for us.
	 *
	 */

	// depending on if headers were passed or not, it will change which node to pass
	// - if the XML does not contain a <SOAP:Header/> element, it changes it from [6] to a [2]
	if (validArrayStructure($rtt_ws->requestArray[1],"['result']['BasicAuth']")) {
	  $payload = $rtt_ws->requestArray[6];
	} else if ((validArrayStructure($rtt_ws->requestArray[2],"['result']['StandardBusinessDocumentHeader']")) &&
	           (validArrayStructure($rtt_ws->requestArray[2],"['result']['message']"))) {
	  $payload = $rtt_ws->requestArray[2];
	} else {
	  $payload = $rtt_ws->requestArray[3];
	}
/*
	echo "<pre>xxx";
	print_r($rtt_ws->requestArray); //2
	echo "<pre>xxx";
*/
	if (($pWS=="GIORDERS") || ($pWS=="GIORDERSTEST")) {
	  include_once ($ROOT.$PHPFOLDER.'functional/import/adaptor/AdaptorGS1GIOrder.php');
	  $adaptorGS1 = new AdaptorGS1GIOrder($dbConn, $payload);
	}
	else if (($pWS=="ORDERS") || ($pWS=="ORDERSTEST")) {
	  include_once ($ROOT.$PHPFOLDER.'functional/import/adaptor/AdaptorGS1TOrder.php');
	  $adaptorGS1 = new AdaptorGS1TOrder($dbConn, $payload);
	}
	else if (($pWS=="REMITTANCE") || ($pWS=="REMITTANCETEST")) {
	  include_once ($ROOT.$PHPFOLDER.'functional/import/adaptor/AdaptorGS1Remittance.php');
	  $adaptorGS1 = new AdaptorGS1Remittance($dbConn, $payload);
	}
	else return new soap_fault(99,"SOAP: Client","Unrecognised WS parameter passed in URL endpoint");

	$authenticated = authenticate($adaptorGS1); // must come here after adaptor setup due to vendor needing to be set first
	if ($authenticated===false) {
	}

	if (($authenticated) && ($adaptorGS1!==false) && ($adaptorGS1->errorStatus===false)) {

			// process the order

	    if ($isTest) {
        $eTO=new ErrorTO();
        // do not store the order
        $eTO->type=FLAG_ERRORTO_SUCCESS;
        $eTO->description="SUCCESSFUL TEST";
			} else {

			  if (($pWS=="REMITTANCE") || ($pWS=="REMITTANCETEST")) {

			    include_once ($ROOT.$PHPFOLDER.'functional/import/processor/ProcessorRemittance.php');
			    $processorClass = new ProcessorRemittance($dbConn);
			    $eTO=$processorClass->postRemittance($adaptorGS1->postingRemittanceTO);

			    if ($eTO->type != FLAG_ERRORTO_SUCCESS) {

			      $subject = 'RTTSOAP DML ERROR';
			      $body = 'DML error has occurred on the posting of a remittance:'."\r\n".
			          "\r\n".
			          'GLN: '.$adaptorGS1->postingRemittanceTO->principalGLN."\r\n".
			          'Principal UID: '.$adaptorGS1->postingRemittanceTO->principalUId."\r\n".
			          'Vendor UID: '.$adaptorGS1->postingRemittanceTO->vendorUId."\r\n".
			          'Document No: '.$adaptorGS1->postingRemittanceTO->documentNumber."\r\n".
			          'Reference: '.$adaptorGS1->postingRemittanceTO->reference."\r\n".
			          'Date: '.CommonUtils::getGMTime(0)."\r\n\r\n".
			          $eTO->description;

			      BroadcastingUtils::sendAlertEmail($subject, $body, NULL, true);
			      $adaptorGS1->errorDescription = 'Could not store the Order. RT system error.'.$eTO->description;
			      $adaptorGS1->errorParty = 'SOAP: Client';
			      $adaptorGS1->errorStatus = 1;

			    } else {
//			      mysql_query("commit", $dbConn->connection);
			      mysqli_commit($dbConn->connection);
			    }

			  } else {

			    include_once ($ROOT.$PHPFOLDER.'functional/import/processor/ProcessorTOH.php');
			    $processorClass = new ProcessorTOH($dbConn);
			    $eTO=$processorClass->postTOH($adaptorGS1->postingOrdersHoldingTO);

			    if ($eTO->type != FLAG_ERRORTO_SUCCESS) {

			      $subject = 'RTTSOAP DML ERROR';
			      $body = 'DML error has occurred on the posting of a order:'."\r\n".
			          "\r\n".
			          'GLN: '.$adaptorGS1->postingOrdersHoldingTO->principalGLN."\r\n".
			          'Principal UID: '.$adaptorGS1->postingOrdersHoldingTO->principalUid."\r\n".
			          'Vendor UID: '.$adaptorGS1->postingOrdersHoldingTO->vendorUid."\r\n".
			          'Document No: '.$adaptorGS1->postingOrdersHoldingTO->documentNo."\r\n".
			          'Reference: '.$adaptorGS1->postingOrdersHoldingTO->reference."\r\n".
			          'Date: '.CommonUtils::getGMTime(0)."\r\n\r\n".
			          $eTO->description;

			      BroadcastingUtils::sendAlertEmail($subject, $body, NULL, true);
			      $adaptorGS1->errorDescription = 'Could not store the Order. RT system error.'.$eTO->description;
			      $adaptorGS1->errorParty = 'SOAP: Client';
			      $adaptorGS1->errorStatus = 1;

			    } else {
			      mysqli_query($dbConn->connection, "commit");
			    }


			  }
      }

	}
	$responseXML = $adaptorGS1->getResponse();

	if($responseXML != false){
	  //log result | check the type of response
	  if($adaptorGS1->errorStatus == false){
	    WSlogger::wsServerLog('SUCCESS',
	                          'DONE',
	                          ((isset($adaptorGS1->orderNo))?$adaptorGS1->orderNo:$adaptorGS1->postingRemittanceTO->wsUniqueCreatorId),
	                          ((isset($adaptorGS1->principalUid))?$adaptorGS1->principalUid:$adaptorGS1->principalUId),
	                          ((isset($adaptorGS1->vendorUid))?$adaptorGS1->vendorUid:$adaptorGS1->vendorUId),
	                          $HTTP_RAW_POST_DATA);
	  } else {
	    WSlogger::wsServerLog('ERROR',
	                          'CODE: '.$adaptorGS1->errorStatus.' '.$adaptorGS1->errorParty . ', ' .$adaptorGS1->errorDescription,
	                          ((isset($adaptorGS1->orderNo))?$adaptorGS1->orderNo:$adaptorGS1->postingRemittanceTO->wsUniqueCreatorId),
	                          ((isset($adaptorGS1->principalUid))?$adaptorGS1->principalUid:$adaptorGS1->principalUId),
	                          ((isset($adaptorGS1->vendorUid))?$adaptorGS1->vendorUid:$adaptorGS1->vendorUId),
	                          $HTTP_RAW_POST_DATA);
	  }

		return $responseXML;  //return
	} else {
    	//logger built into nusoap fault.
		return new soap_fault($adaptorGS1->errorStatus,$adaptorGS1->errorParty,$adaptorGS1->errorDescription);
	}
}




//Generic Echo Function
function UpdateDeliveryNotice($str){

  global $ROOT, $PHPFOLDER, $dbConn, $rtt_ws;

  include_once ($ROOT.$PHPFOLDER.'functional/import/adaptor/AdaptorSOAP.php');

  $adaptorSoap = new AdaptorSOAP($dbConn);
  $adaptorSoap->EPODUpdateDeliveryNotice($rtt_ws->requestArray);  //internal response.

}

?>
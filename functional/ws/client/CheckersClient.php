<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER.'functional/ws/lib/nusoap.php');  //soap lib
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingDetailTO.php');
include_once ($ROOT.$PHPFOLDER.'functional/import/processor/ProcessorTOH.php');
require_once $ROOT . $PHPFOLDER . 'libs/newrelic.php';


class CheckersClient {


  public $jobCount = 0; //counter for script output

  private $dbConn;
  private $transactionDAO;
  private $postBIDAO;
  private $client = false;
  private $currentEndPoint = false;


  public function __construct($dbConn) {

    $this->dbConn = $dbConn;

    $this->transactionDAO = new TransactionDAO($this->dbConn);
    $this->postBIDAO = new PostBIDAO($this->dbConn);

  }

  // Checkers will ignore a max val > 20
  public function runProcess_getOrders($principalUId, $numOfOrders="20", $vendorAccount, $username, $password){

      // get orders
      $callResult = $this->sendRequestForOrders(
                                          $username,
                                          $password,
                                          $numOfOrders,
                                          $options = array(
                                                            'type'=>'RequestForOrders'
                                                          )
                                        );

      if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
        if ($callResult->identifier=="305") {
          echo "<p>Code 305 Received - No Rows to Download<p>";
          return false;
        } else {
          echo "<p><span style='color:red'>ERROR in sendRequestForOrders CheckersClient.runProcess_getOrders(): </span>".$callResult->description."</p>";
          return false;
        }
      }

      $orders=$callResult->object;

      // Convert to OH TO
      $callResult = $this->setupTO_orders($principalUId, $vendorAccount, $orders);

      if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
        echo "<p><span style='color:red'>ERROR in CheckersClient.setupTO(): </span>".$callResult->description."</p>";
        return false;
      }

      $ordersTOH = $callResult->object;

      // Store OH
      if (count($ordersTOH)>0) {

        $processorTOH = new ProcessorTOH($this->dbConn);
        $callResult=$processorTOH->postTOH($ordersTOH);

        if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
          echo "<p><span style='color:red'>ERROR in CheckersClient.setupTO(): </span>".$callResult->description."</p>";
          $this->dbConn->dbinsQuery("rollback");
          return false;
        }

        // we commit here before the confirmation, as if the next stage fails then we just receive the order again and dup check will protect the client
        // also, we dont want this to roll back on error if we confirm the document successfully as we would then never receive the doc again.
        $this->dbConn->dbinsQuery("commit");


        // send confirmation back
        $sendConfirmation = true;
        if ($sendConfirmation) {
          // get unique list of successfull
          $orderList=array();
          foreach ($ordersTOH as $doc) {
            $orderList[]=$doc->reference;
          }

          $callResult = $this->sendOrderConfirmation(
                                              $username,
                                              $password,
                                              implode(",",$orderList),
                                              $options = array(
                                                                'type'=>'OrderConfirmation'
                                                              )
                                            );

          if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
              echo "<p><span style='color:red'>ERROR in CheckersClient.sendOrderConfirmation: </span>".$callResult->description."</p>";
              return false;
          } else if (trim($callResult->object)=="") {
              echo "<p><span style='color:red'>ERROR in CheckersClient.sendOrderConfirmation, no OrdersList Index in resultset found from response</p>";
              return false;
          }

          $result=$this->compareConfirmationList($submittedArr=$orderList, $returnedListStr=$callResult->object);
          echo "<p style='color:green'>Successfully Confirmed Orders:".implode(",",$result["good"])."</p>";
          if (count($result["bad"])>0) echo "<p style='color:red;'>Failed Confirmed Orders:".implode(",",$result["bad"])."</p>";
        }

      } // end > 0 array

      // if NOT the full complement of max orders was returned then stop the loop and move onto next principal
      if (count($orders["Doc"]["Orders"]["Order"])<$numOfOrders) {
        return false;
      }

      return true; // execute next loop for same principal, or exit controlled by that loop

  }

  // Checkers will ignore a max val > 20
  public function runProcess_getClaims($principalUId, $numOfOrders="20", $vendorAccount, $username, $password){

    // get orders
    $callResult = $this->sendRequestForClaims(
        $username,
        $password,
        $numOfOrders,
        $options = array(
        'type'=>'RequestForClaims'
        )
    );

    if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
      if ($callResult->identifier=="305") {
        echo "<p>Code 305 Received - No Rows to Download<p>";
        return false;
      } else {
        echo "<p><span style='color:red'>ERROR in sendRequestForClaims CheckersClient.runProcess_getClaims(): </span>".$callResult->description."</p>";
        return false;
      }
    }

     // echo "<pre>";
     // print_r($callResult->object);

    $claims=$callResult->object;

    // Convert to OH TO
    $callResult = $this->setupTO_claims($principalUId, $vendorAccount, $claims);

    if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
      echo "<p><span style='color:red'>ERROR in CheckersClient.setupTO() for Claims: </span>".$callResult->description."</p>";
      return false;
    }

    $claimsTOH = $callResult->object;

    //echo "<pre>";
    //print_r($callResult->object);

    // Store OH
    if (count($claimsTOH)>0) {

      $processorTOH = new ProcessorTOH($this->dbConn);
      $callResult=$processorTOH->postTOH($claimsTOH);

      if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
        // echo "<pre>"; print_r($claims);
        echo "<p><span style='color:red'>ERROR in CheckersClient.setupTO_claims(): </span>".$callResult->description."</p>";
        $this->dbConn->dbinsQuery("rollback");
        return false;
      }

      // we commit here before the confirmation, as if the next stage fails then we just receive the order again and dup check will protect the client
      // also, we dont want this to roll back on error if we confirm the document successfully as we would then never receive the doc again.
      $this->dbConn->dbinsQuery("commit");

      // send confirmation back
      // * it is also possible to "reset" claims (see documentation), but this is not done here
      $sendConfirmation = true;
      if ($sendConfirmation) {
        // get unique list of successfull
        $claimList=array();
        foreach ($claimsTOH as $doc) {
          $claimList[]=$doc->wsUniqueCreatorId;
        }

        $callResult = $this->sendClaimConfirmation(
            $username,
            $password,
            implode(",",$claimList),
            $options = array(
                'type'=>'ClaimConfirmation'
            )
        );

        if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
          echo "<p><span style='color:red'>ERROR in CheckersClient.sendClaimConfirmation: </span>".$callResult->description."</p>";
          return false;
        } else if (trim($callResult->object)=="") {
          echo "<pre>"; print_r($callResult);
          echo "<p><span style='color:red'>ERROR in CheckersClient.sendClaimConfirmation, no ClaimsList Index in resultset found from response</p>";
          return false;
        }

        $result=$this->compareConfirmationList($submittedArr=$claimList, $returnedListStr=$callResult->object);
        echo "<p style='color:green'>Successfully Confirmed Claims:".implode(",",$result["good"])."</p>";
        if (count($result["bad"])>0) echo "<p style='color:red;'>Failed Confirmed Claims:".implode(",",$result["bad"])."</p>";
      }

    } // end > 0 array

    // if NOT the full complement of max orders was returned then stop the loop and move onto next principal
    if (count($claims["StandardBusinessDocument"]["message"]["documentCommand"]["documentCommandOperand"]["debitCreditAdvice"])<$numOfOrders) {
      return false;
    }

    return true; // execute next loop for same principal, or exit controlled by that loop

  }


    public function sendRequestForOrders($username,$password,$numOfOrders="20",$addOptions = array()){
      global $ROOT, $PHPFOLDER;

      $errorTO = new ErrorTO();

      // replace values
      $sendXML = '<?xml version="1.0"?>
                  <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" SOAP-ENV:encodingstyle="http://schemas.xmlsoap.org/soap/encoding/">
                    <SOAP-ENV:Header>
                      <username>'.$username.'</username>
                      <password>'.$password.'</password>
                      <MaxOrders>'.$numOfOrders.'</MaxOrders>
                      <XMLVers>2.0.2</XMLVers>
                    </SOAP-ENV:Header>
                    <SOAP-ENV:Body>
                         <m:getNewOrders xmlns:m= "urn:https://www.shoprite.co.za/b2b/soap/soap:getNewOrders/">
                         </m:getNewOrders>
                    </SOAP-ENV:Body>
                  </SOAP-ENV:Envelope>';

      return $this->WebServiceCall($sendXML, $addOptions, $logFolder="checkers", $endPoint=WS_URL_CHECKERS_ORDERS);

    }

    public function sendRequestForClaims($username,$password,$numOfClaims="20",$addOptions = array()){
      global $ROOT, $PHPFOLDER;

      $errorTO = new ErrorTO();

      // replace values
      $sendXML = '<?xml version="1.0"?>
                  <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" SOAP-ENV:encodingstyle="http://schemas.xmlsoap.org/soap/encoding/">
                    <SOAP-ENV:Header>
                      <username>'.$username.'</username>
                      <password>'.$password.'</password>
                      <maxClaims>'.$numOfClaims.'</maxClaims>
                      <xmlVers>2.0.2</xmlVers>
                    </SOAP-ENV:Header>
                    <SOAP-ENV:Body>
                         <m:getClaims xmlns:m= "urn:https://www.shoprite.co.za/b2b/soap/soap:getClaims/">
                         </m:getClaims>
                    </SOAP-ENV:Body>
                  </SOAP-ENV:Envelope>';

      return $this->WebServiceCall($sendXML, $addOptions, $logFolder="checkers", $endPoint=WS_URL_CHECKERS_CLAIMS);

    }

    public function sendOrderConfirmation($username,$password,$orderList,$addOptions = array()){
      global $ROOT, $PHPFOLDER;

      $errorTO = new ErrorTO();

      // replace values
      $sendXML = '<?xml version="1.0"?>
                  <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
                   SOAP-ENV:encodingstyle="http://schemas.xmlsoap.org/soap/encoding/">
                    <SOAP-ENV:Header>
                      <username>'.$username.'</username>
                      <password>'.$password.'</password>
                    </SOAP-ENV:Header>
                    <SOAP-ENV:Body>
                      <m:ackOrders xmlns:m= "urn:https://www.shoprite.co.za/b2b/soap/soap:ackOrders/">
                        <OrdersList>'.$orderList.'</OrdersList>
                      </m:ackOrders>
                    </SOAP-ENV:Body>
                  </SOAP-ENV:Envelope>';

      return $this->WebServiceCall($sendXML, $addOptions, $logFolder="checkers", $endPoint=WS_URL_CHECKERS_ORDERS);

    }

    public function sendClaimConfirmation($username,$password,$claimList,$addOptions = array()){
      global $ROOT, $PHPFOLDER;

      $errorTO = new ErrorTO();

      // replace values
      $sendXML = '<?xml version="1.0"?>
                  <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
                   SOAP-ENV:encodingstyle="http://schemas.xmlsoap.org/soap/encoding/">
                    <SOAP-ENV:Header>
                      <username>'.$username.'</username>
                      <password>'.$password.'</password>
                    </SOAP-ENV:Header>
                    <SOAP-ENV:Body>
                      <m:ackClaims xmlns:m= "urn:https://www.shoprite.co.za/b2b/soap/soap:ackClaims/">
                        <ClaimsList>'.$claimList.'</ClaimsList>
                      </m:ackClaims>
                    </SOAP-ENV:Body>
                  </SOAP-ENV:Envelope>';

      return $this->WebServiceCall($sendXML, $addOptions, $logFolder="checkers", $endPoint=WS_URL_CHECKERS_CLAIMS);

    }


    private function WebServiceCall($sendContent, $addOptions, $logFolder, $endPoint){
      global $ROOT;

      $errorTO = new ErrorTO();
      // you also need to do this is calling a diff soap_claims.asp/soap_orders.asp otherwise you will get a "Method not found" error
      if ((!$this->client) || ($this->currentEndPoint!=$endPoint)) {
        $this->currentEndPoint = $endPoint;
        $this->client = new nusoap_client($endPoint,false);

        // $client->soap_defencoding = 'utf-8';
        $err = $this->client->getError();
        $this->client->xml_encoding = 'UTF-8';
        $this->client->persistentConnection = true;
        // $this->client->setHTTPEncoding('deflate, gzip');

        /*
          $client->setCredentials($username,$password);
        */

        if ($err) {
          echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
          echo '<h2>Debug</h2><pre>' . htmlspecialchars($this->client->getDebug(), ENT_QUOTES) . '</pre>';
          $errorTO->type = FLAG_ERRORTO_ERROR;
          $errorTO->description = htmlspecialchars($this->client->getDebug(), ENT_QUOTES);
          return $errorTO;
        }
      }

      $result = $this->client->send($sendContent);

      /*
      echo "<pre>var_dump(result) | START<br>";
      var_dump($result);
      echo "<br>var_dump(result) | END</pre>";
      */

      //DEBUG
      $debug = false;
      if($debug){
        if ($this->client->fault) {
                echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
        } else {
          $err = $this->client->getError();
          if ($err) {
            echo '<h2>Error</h2><pre>' . $err . '</pre>';
          } else {
            echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
          }
        }

        echo '<h2>Request</h2><pre>' . htmlspecialchars(str_replace("><",">\n<",$this->client->request), ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' . htmlspecialchars($this->client->response, ENT_QUOTES) . '</pre>';
        // echo '<h2>Response</h2><pre>' , var_dump($client->return) , '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($this->client->getDebug(), ENT_QUOTES) . '</pre>';
      }


      //LOGGING
      $xmlLog = true;
      if($xmlLog){

          //id to link the two log items
          $traceId = uniqid();

          //log to newrelic and s3
          $logResult = NewRelic::logEvent(
              $logType = $logFolder,
              $script = basename(__FILE__),
              $msg = substr($this->client->request, strpos($this->client->request, '<?'), strlen($this->client->request)),
              $attr = [
                  'requesttype' => $addOptions['type'],
                  'timestamp' => gmdate('YmdHis'),
                  'flow' => 'REQUEST',
                  'traceid' => $traceId,
              ]
          );
          if (!$logResult) {
              echo "Failed to create log event for REQUEST or options not passed";
          }

          $logResult = NewRelic::logEvent(
              $logType = $logFolder,
              $script = basename(__FILE__),
              $msg = $this->client->response,
              $attr = [
                  'requesttype' => $addOptions['type'],
                  'timestamp' => gmdate('YmdHis'),
                  'flow' => 'RESPONSE',
                  'traceid' => $traceId,
              ]
          );
          if (!$logResult) {
              echo "Failed to create log event for RESPONSE or options not passed";
          }
      }

      $err = $this->client->getError();

      if($this->client->fault){
        $errorTO->type = FLAG_ERRORTO_ERROR;
        $errorTO->description = '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>' . $result . $err . '</pre>';
      } else {
          if ($err) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>' . $result . $err . '</pre>';
          } if (!preg_match("/^HTTP.+ 2[0-9][0-9] /msU",$this->client->response)) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = 'Server issued a non-200 response code.' . $result . $err . '</pre>';
          } else {

            // Checkers send the wrong content-type back as text/html instead of text/xml
            // This stops the parseResponse from converting the XML data into a response array.
            // So we artificially force the parser to work by doing this...
            if ((strstr($this->client->incoming_headers['content-type'],"text/html")) &&
                (
                  (preg_match("/<Doc>/msU",$this->client->response)) ||
                  (preg_match("/StandardBusinessDocument>/msU",$this->client->response)) ||
                  (preg_match("/ackOrdersResponse/msU",$this->client->response)) ||
                  (preg_match("/ackClaimsResponse/msU",$this->client->response)) ||
                  (preg_match("/<SOAP-ENV:Body>/msU",$this->client->response))
                )
            ) {
              $this->client->incoming_headers['content-type'] = "text/xml";
              $result = $this->client->parseResponse($this->client->incoming_headers, $this->client->responseData);
            }


            // StandardBusinessDocument = getClaims response
            if ((isset($result["Doc"])) || (isset($result["StandardBusinessDocument"]))) {
                $errorTO->type = FLAG_ERRORTO_SUCCESS;
                $errorTO->description = "Successfully Fetched Orders";
                $errorTO->object = $result;
            } else if ((preg_match("/ackOrdersResponse/msU",$this->client->response)) || (preg_match("/ackClaimsResponse/msU",$this->client->response))) {
                $errorTO->type = FLAG_ERRORTO_SUCCESS;
                $errorTO->description = "Successfully Confirmed Orders";
                $errorTO->object = $result;
            } else {
                if (isset($result["faultcode"])) {
                  $errorTO->type = FLAG_ERRORTO_ERROR;
                  $errorTO->description = "Rejected by Checkers with : ".$result["faultstring"];
                  $errorTO->identifier = $result["faultcode"];
                  $errorTO->object = $result;
                } else {
                  $errStr = "";
                  if ( (isset($this->client->persistentConnection)) && (isset($this->client->persistentConnection->error_str)) ) {
                    $errStr = $this->client->persistentConnection->error_str;
                  }
                  $errorTO->type = FLAG_ERRORTO_ERROR;
                  $errorTO->description = "Rejected by Checkers : ".$errStr;
                  $errorTO->object = $result;
                }
            }
          }
      }


      return $errorTO;
    }


    function setupTO_orders($principalUId, $vendorAccount, &$orders) {
      $eTO = new ErrorTO();

      // Checkers have a problem which they seem unable to solve - they split the docs up into batches and duplicate the parent group erroneously !
      /* as in :
       *
       * <Doc>
				<Orders>
					<Order>
						...
					</Order>
					<Order>
						...
					</Order>
				</Orders>
				<Products>
					...
				</Products>
				<PastOrders></PastOrders>
				<Orders>
					<Order>
						...
					</Order>
					<Order>
						...
					</Order>
				</Orders>
				<Products>
					...
				</Products>
				<PastOrders></PastOrders>
			</Doc>
       */

      // correct the mistake for them
      if (isset($orders["Doc"]["Orders"][0])) {
        $temp = array();
        foreach ($orders["Doc"]["Orders"] as $o) {
          if (isset($o["Order"][0])) $temp = array_merge($temp, $o["Order"]);
          else $temp = array_merge($temp, array($o["Order"]));
        }

        unset($orders["Doc"]["Orders"]);
        $orders["Doc"]["Orders"]["Order"] = $temp;
        unset($temp);
      }
      if (isset($orders["Doc"]["Products"][0])) {
        $temp = array();
        foreach ($orders["Doc"]["Products"] as $p) {
          $temp = array_merge($temp, $p["Prod"]);
        }

        unset($orders["Doc"]["Products"]);
        $orders["Doc"]["Products"]["Prod"] = $temp;
        unset($temp);
      }
      // END : Fixup for Checkers


      // Basic Validation first
      if (
          !isset($orders["Doc"]) ||
          !isset($orders["Doc"]["Orders"]["Order"]) ||
          (!isset($orders["Doc"]["Orders"]["Order"][0]["OrderDetails"]) && !isset($orders["Doc"]["Orders"]["Order"]["OrderDetails"]))
         ) {
           $eTO->type = FLAG_ERRORTO_ERROR;
           $eTO->description = "SOAP Order Structural problem in CheckersClient @".date(GUI_PHP_DATETIME_FORMAT);
           $eTO->identifier = ET_SYSTEM;
           return $eTO;
      }

      // if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
      if (!isset($orders["Doc"]["Orders"]["Order"][0])) {
        $temp=$orders["Doc"]["Orders"]["Order"];
        unset($orders["Doc"]["Orders"]["Order"]);
        $orders["Doc"]["Orders"]["Order"][0]=$temp;
      }


      $arrTO = array();

      // NOTE:
      // Because these rows are returned in batch we just skip the current document if there is an error with it as
      // when we send back the confirmation then that document is therefore not confirmed and it will just keep resending
      foreach ($orders["Doc"]["Orders"]["Order"] as &$o) {

        // Checkers allocation send through multiple Destin tags for same order, so we need to convert single into an array.
        // if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
        if (!isset($o["OrderDetails"]["Destins"]["Destin"][0])) {
          $temp=$o["OrderDetails"]["Destins"]["Destin"];
          unset($o["OrderDetails"]["Destins"]["Destin"]);
          $o["OrderDetails"]["Destins"]["Destin"][0]=$temp;
        }

        foreach ($o["OrderDetails"]["Destins"]["Destin"] as &$destin) {

          $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
          $postingOrdersHoldingTO->updateProduct="N";
          $postingOrdersHoldingTO->principalUid=$principalUId;
          $postingOrdersHoldingTO->orderDate = substr($o["OrderDate"],6,4)."-".substr($o["OrderDate"],3,2)."-".substr($o["OrderDate"],0,2);
          $postingOrdersHoldingTO->deliveryDate = ((trim($o["DropDate"])=="")?"":substr($o["DropDate"],6,4)."-".substr($o["DropDate"],3,2)."-".substr($o["DropDate"],0,2));
          $postingOrdersHoldingTO->clientDocumentNo="";
          $postingOrdersHoldingTO->documentNo=$postingOrdersHoldingTO->clientDocumentNo;
          $postingOrdersHoldingTO->vendorUid = V_CHECKERS_VENDOR;
          if (!isset($o["Vendor"]["!ID"]) || !isset($o["Vendor"]["!"])) {
            echo "The [Vendor][!ID] or [Vendor][!] tag is not present in the array conversion in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT);
            continue;
          }
          if (!in_array($o["Vendor"]["!ID"],explode(",",$vendorAccount))) {
            echo "The [Vendor][!ID] ({$o["Vendor"]["!ID"]}) differs from the passed vendor account ({$vendorAccount} - {$o["Vendor"]["!"]}) in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT);
            continue;
          }
          $postingOrdersHoldingTO->vendorReference = $o["Vendor"]["!ID"];

          $postingOrdersHoldingTO->dataSource = DS_WS;
          $postingOrdersHoldingTO->capturedBy = 'CHECKERS';
          $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
          $postingOrdersHoldingTO->chainLookupRef=""; // processing will assign generic chain to it.
          $postingOrdersHoldingTO->storeLookupRef=""; // ???
          $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
          $postingOrdersHoldingTO->principalStoreUId=""; // let the processing script lookup the store
          $postingOrdersHoldingTO->reference=$o["OrderNo"];
          if (trim($postingOrdersHoldingTO->reference)=="") {
            echo "The Order No is blank for vendor account ({$vendorAccount} - {$o["Vendor"]["!"]}) in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT);
            continue;
          }

          $postingOrdersHoldingTO->deliverName = $destin["DestDesc"]." (".$destin["DestID"].")";
          $postingOrdersHoldingTO->shipToName = $postingOrdersHoldingTO->deliverName;
          $postingOrdersHoldingTO->oldAccount = "";
          $postingOrdersHoldingTO->shipToGLN = $destin["DestEAN"];
          if (trim($postingOrdersHoldingTO->shipToGLN)=="") {
            // do not allow to continue - reject the file and ITD will be notified in confirmation
            echo "The EAN / GLN is blank for order ({$postingOrdersHoldingTO->reference}) vendor account ({$vendorAccount} - {$o["Vendor"]["!"]}) in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT);
            continue;
          }

          // if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
          if (!isset($destin["Items"]["Item"][0])) {
            $temp=$destin["Items"]["Item"];
            unset($destin["Items"]["Item"]);
            $destin["Items"]["Item"][0]=$temp;
          }

          foreach ($destin["Items"]["Item"] as $ol) {
            $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

            $postingOrdersHoldingDetailTO->productCode = $ol["ItemNum"]; // to do lookup later to get EAN
            $postingOrdersHoldingDetailTO->quantity = $ol["Qty"];
            // Checkers formula for cost of entire line (extPrice) is ((Qty*ItemPackSize)/CostPer)*GrossCst   ~ where CostPer is the number of items(or cases if=1) that GrossCst applies to
            // To Get it per cases we divide by qty which ultimately cancels out the Qty in initial formula above
            $postingOrdersHoldingDetailTO->listPrice = ($ol["ItemPackSize"]*$ol["GrossCst"])/$ol["CostPer"];
            // As per Checkers Formula. This is the starting value to apply discounts to
            $extPrice = (($postingOrdersHoldingDetailTO->quantity*$ol["ItemPackSize"])/$ol["CostPer"])*$ol["GrossCst"];
            if ($ol["CostPer"]!=$ol["ItemPackSize"]) echo "<p>Unmatching CostPer:ItemPackSize {$postingOrdersHoldingTO->reference}</p>";

            $postingOrdersHoldingDetailTO->discountValue = 0;
            if (isset($ol["Discounts"]["Discount"])) {


              if (!isset($ol["Discounts"]["Discount"][0])) {
                $temp=$ol["Discounts"]["Discount"];
                unset($ol["Discounts"]["Discount"]);
                $ol["Discounts"]["Discount"][0]=$temp;
              }
              foreach ($ol["Discounts"]["Discount"] as $d) {

                if (($d["DiscInd"]=="") && ($d["DiscAmt"]=="")) continue; // empty structure

                $discVal = 0;
                switch ($d["DiscInd"]) {
                  case "R":
                    $discVal = round($d["DiscAmt"] / $postingOrdersHoldingDetailTO->quantity,2);
                    break;
                  case "%":
                    /*
                    if ($d["DiscAmt"]>1) {
                      echo "<p style='color:red'>Discount Percentage expected to be in format of <1 (DiscAmt = {$d["DiscAmt"]}) for order ({$postingOrdersHoldingTO->reference}) vendor account ({$vendorAccount} - {$o["Vendor"]["!"]}) in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT)."</p>";
                      continue 4;
                    }
                    */
                    $discVal = round($extPrice * ($d["DiscAmt"]/100) / $postingOrdersHoldingDetailTO->quantity,2);
                    break;
                  default:
                    echo "Invalid Discount Indicator (DiscInd = {$d["DiscInd"]}) for order ({$postingOrdersHoldingTO->reference}) vendor account ({$vendorAccount} - {$o["Vendor"]["!"]}) in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT);
                    continue 4;
                }
                $extPrice -= $discVal*$postingOrdersHoldingDetailTO->quantity; // compounded, so need to have a running extPrice
                $postingOrdersHoldingDetailTO->discountValue += $discVal;
                $postingOrdersHoldingDetailTO->discountReference=substr($d["DiscDesc"],0,20);
              }

            }

            $postingOrdersHoldingDetailTO->extPrice = $extPrice;
            $postingOrdersHoldingDetailTO->nettPrice += round($extPrice / $postingOrdersHoldingDetailTO->quantity,2);

            // do not apply VAT - vat is assigned during processing as Checkers require us to control this.
            $postingOrdersHoldingDetailTO->vatRate = "";
            $postingOrdersHoldingDetailTO->vatAmount = "";

            $postingOrdersHoldingDetailTO->totalPrice = $postingOrdersHoldingDetailTO->extPrice; // this must be calculated otherwise the COMPUTE check fails in processing

            $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

          }

          $arrTO[]=$postingOrdersHoldingTO;

        } // destins loop

      }
      unset($o);

      // Now Process the PRODUCTS section to allocate the product details

      // Rebuild the array structure
      if (!isset($orders["Doc"]["Products"]["Prod"][0])) {
        // if there is only one item in totallity
        $temp=$orders["Doc"]["Products"]["Prod"];
        unset($orders["Doc"]["Products"]["Prod"]);
        $orders["Doc"]["Products"]["Prod"][0]=$temp;
      }
      if (isset($orders["Doc"]["Products"]["Prod"]["ProdItemNo"])) {
        // if there is one item on its own with another proper array list separately
        $temp = array("ProdItemNo"=>$orders["Doc"]["Products"]["Prod"]["ProdItemNo"],
                      "Barcode"=>$orders["Doc"]["Products"]["Prod"]["Barcode"],
                      "SuppItemNo"=>$orders["Doc"]["Products"]["Prod"]["SuppItemNo"],
                      "ItemDesc"=>$orders["Doc"]["Products"]["Prod"]["ItemDesc"],
                      "WHOrderInd"=>$orders["Doc"]["Products"]["Prod"]["WHOrderInd"]
                      );
        $orders["Doc"]["Products"]["Prod"][]=$temp;

        unset($orders["Doc"]["Products"]["Prod"]["ProdItemNo"]);
        unset($orders["Doc"]["Products"]["Prod"]["Barcode"]);
        unset($orders["Doc"]["Products"]["Prod"]["SuppItemNo"]);
        unset($orders["Doc"]["Products"]["Prod"]["ItemDesc"]);
        unset($orders["Doc"]["Products"]["Prod"]["WHOrderInd"]);
      }

      $productArr=array();
      foreach ($orders["Doc"]["Products"]["Prod"] as $p) {
       if(!isset($p["ProdItemNo"])) {
         echo "Product not found in response
               <pre>";
         print_r($orders["Doc"]["Products"]);
         echo "<pre>";
       }
       if(!isset($productArr[$p["ProdItemNo"]])) echo "Product Item No {$p["ProdItemNo"]} not found in response - adjusting array from 1 dim to multi<br>";
       $productArr[$p["ProdItemNo"]] = array("Barcode"=>$p["Barcode"],
                                             "SuppItemNo"=>$p["SuppItemNo"],
                                             "ItemDesc"=>$p["ItemDesc"]);
      }

      foreach($arrTO as $doc) {
        foreach ($doc->detailArr as &$dtl) {
          if (!isset($productArr[$dtl->productCode])) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Product Code failed on XML lookup (Checkers Item Code {$dtl->productCode}) for order ({$doc->reference}) vendor account ({$vendorAccount}) in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT);
            $eTO->identifier = ET_SYSTEM;
            return $eTO;
          }
          $dtl->productSKUGTIN = preg_replace("/^[0]+/","",$productArr[$dtl->productCode]["Barcode"]); // Checkers use innercasing GTIN
          $dtl->productName = $productArr[$dtl->productCode]["ItemDesc"];
          $dtl->productCode = "";
        }
      }
      unset($dtl);

      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;

    }

    function setupTO_claims($principalUId, $vendorAccount, &$claims) {
      $eTO = new ErrorTO();

      // The main document loop is :
      // ["message"]["documentCommand"]["documentCommandOperand"]["debitCreditAdvice"]

      // Basic Validation first
      if (
        !isset($claims["StandardBusinessDocument"]["message"]) ||
        !isset($claims["StandardBusinessDocument"]["message"]["documentCommand"]["documentCommandOperand"]["debitCreditAdvice"]) ||
        (!isset($claims["StandardBusinessDocument"]["message"]["documentCommand"]["documentCommandOperand"]["debitCreditAdvice"][0]["debitCreditDetail"]) && !isset($claims["StandardBusinessDocument"]["message"]["documentCommand"]["documentCommandOperand"]["debitCreditAdvice"]["debitCreditDetail"]))
      ) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "SOAP Claims Structural problem in CheckersClient @".date(GUI_PHP_DATETIME_FORMAT);
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }

      // if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
      if (!isset($claims["StandardBusinessDocument"]["message"]["documentCommand"]["documentCommandOperand"]["debitCreditAdvice"][0])) {
        $temp=$claims["StandardBusinessDocument"]["message"]["documentCommand"]["documentCommandOperand"]["debitCreditAdvice"];
        unset($claims["StandardBusinessDocument"]["message"]["documentCommand"]["documentCommandOperand"]["debitCreditAdvice"]);
        $claims["StandardBusinessDocument"]["message"]["documentCommand"]["documentCommandOperand"]["debitCreditAdvice"][0]=$temp;
      }

      $arrTO = array();

      // NOTE:
      // Because these rows are returned in batch we just skip the current document if there is an error with it as
      // when we send back the confirmation then that document is therefore not confirmed and it will just keep resending
      foreach ($claims["StandardBusinessDocument"]["message"]["documentCommand"]["documentCommandOperand"]["debitCreditAdvice"] as &$c) {

        // these are also repeated under the detail loop for each line, but we just use the summary
        $netClaimValue = $c["netSummary"]["amount"]["monetaryAmount"]; // "33.76"
        $netClaimIndicator = $c["netSummary"]["debitCreditIndicator"]; // "CREDIT"

        $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
        $postingOrdersHoldingTO->updateProduct="N";
        $postingOrdersHoldingTO->principalUid=$principalUId;
        $postingOrdersHoldingTO->dataSource = DS_WS;
        $postingOrdersHoldingTO->capturedBy = 'CHECKERS';
        $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
        $postingOrdersHoldingTO->chainLookupRef=""; // processing will assign generic chain to it.
        $postingOrdersHoldingTO->storeLookupRef=""; // ???
        $postingOrdersHoldingTO->documentTypeUId = (($netClaimIndicator=="CREDIT")?DT_BUYER_ORIGINATED_CREDIT_CLAIM:DT_BUYER_ORIGINATED_DEBIT_CLAIM);
        $postingOrdersHoldingTO->principalStoreUId=""; // let the processing script lookup the store
        $postingOrdersHoldingTO->shipToGLN = $c["buyerSellerPartyIdentification"]["buyerIdentification"]["gln"];
        if (trim($postingOrdersHoldingTO->shipToGLN)=="") {
          // do not allow to continue - reject the file and ITD will be notified in confirmation
          echo "The EAN / GLN is blank for order ({$postingOrdersHoldingTO->reference}) vendor account ({$vendorAccount} - {$c["Vendor"]["!"]}) in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT);
          continue;
        }

        // NB! This wsUniqueCreatorId is used in the claimsAck response !
        $postingOrdersHoldingTO->wsUniqueCreatorId=$c["debitCreditAdviceIdentification"]["uniqueCreatorIdentification"];
        $postingOrdersHoldingTO->vendorUid = V_CHECKERS_VENDOR;
        /* cannot do this check as CHECKERS are sending through "0000000000000" as the GLN
        if ($c["buyerSellerPartyIdentification"]["sellerIdentification"]["gln"]!=principal gln) {
        echo "The [Vendor][!ID] ({$c["Vendor"]["!ID"]}) differs from the passed vendor account ({$vendorAccount} - {$c["Vendor"]["!"]}) in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT);
        continue;
        }
        */
        $postingOrdersHoldingTO->vendorReference = "";
        foreach ($c["buyerSellerPartyIdentification"]["buyerIdentification"]["additionalPartyIdentification"] as $buyer) {
          $postingOrdersHoldingTO->deliverName .= (($postingOrdersHoldingTO->deliverName=="")?"":"-").$buyer["additionalPartyIdentificationValue"];
        }
        $postingOrdersHoldingTO->shipToName = $postingOrdersHoldingTO->deliverName;
        $postingOrdersHoldingTO->oldAccount = "";

        // if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
        if (!isset($c["debitCreditDetail"][0])) {
          $temp=$c["debitCreditDetail"];
          unset($c["debitCreditDetail"]);
          $c["debitCreditDetail"][0]=$temp;
        }

        // just use first line at header level
        $postingOrdersHoldingTO->additionalType = $c["debitCreditDetail"][0]["adjustmentReason"]["messageReason"]; // "A4_NON_RECEIPT_OF_GOODS"

        // loop thru each claim line
        foreach ($c["debitCreditDetail"] as $dtl) {

          // there should be 3 items for pricing :
          // [0] The original order details
          // [1] what was on the invoice
          // [2] The return / claim (diff between the two)
          //
          // .. and two for others ...
          // [0] The return / claim
          // [1] Some other details such as the truck details

          if (
            !isset($dtl["debitCreditReference"][0])
          ) {
            $temp=$dtl["debitCreditReference"];
            unset($dtl["debitCreditReference"]);
            $dtl["debitCreditReference"][0]=$temp;
          }

          if (count($dtl["debitCreditReference"])==1) {

            $postingOrdersHoldingTO->orderDate = $dtl["debitCreditReference"][0]["creationDate"];
            $postingOrdersHoldingTO->reference=$dtl["debitCreditReference"][0]["reference"]["entityIdentification"]["uniqueCreatorIdentification"];
            $postingOrdersHoldingTO->clientDocumentNo=$dtl["debitCreditReference"][0]["reference"]["entityIdentification"]["uniqueCreatorIdentification"];

          } else if ((count($dtl["debitCreditReference"])==2) || (count($dtl["debitCreditReference"])>2 && !isset($dtl["debitCreditReference"][2]["creationDate"]))) {

            $postingOrdersHoldingTO->orderDate = $dtl["debitCreditReference"][0]["creationDate"];
            $postingOrdersHoldingTO->reference=$dtl["debitCreditReference"][0]["reference"]["entityIdentification"]["uniqueCreatorIdentification"];
            $postingOrdersHoldingTO->clientDocumentNo=$dtl["debitCreditReference"][0]["reference"]["entityIdentification"]["uniqueCreatorIdentification"];
            $postingOrdersHoldingTO->additionalDetails=$dtl["debitCreditReference"][1]["reference"]["entityIdentification"]["uniqueCreatorIdentification"]; // "TRUCK DETAILS"


            if (isset($dtl["debitCreditReference"][1]["reference"]["entityIdentification"]["contentOwner"]["additionalPartyIdentification"])) {
              if (!isset($dtl["debitCreditReference"][1]["reference"]["entityIdentification"]["contentOwner"]["additionalPartyIdentification"][0])) {
                $temp=$dtl["debitCreditReference"][1]["reference"]["entityIdentification"]["contentOwner"]["additionalPartyIdentification"];
                unset($dtl["debitCreditReference"][1]["reference"]["entityIdentification"]["contentOwner"]["additionalPartyIdentification"]);
                $dtl["debitCreditReference"][1]["reference"]["entityIdentification"]["contentOwner"]["additionalPartyIdentification"][0]=$temp;
              }
              foreach ($dtl["debitCreditReference"][1]["reference"]["entityIdentification"]["contentOwner"]["additionalPartyIdentification"] as $p) {
                $postingOrdersHoldingTO->additionalDetails .= ", ".$p["additionalPartyIdentificationValue"];
              }
            }

          } else {
            $postingOrdersHoldingTO->orderDate = $dtl["debitCreditReference"][2]["creationDate"];
            $postingOrdersHoldingTO->reference=$dtl["debitCreditReference"][1]["reference"]["entityIdentification"]["uniqueCreatorIdentification"];
            $postingOrdersHoldingTO->clientDocumentNo=$dtl["debitCreditReference"][2]["reference"]["entityIdentification"]["uniqueCreatorIdentification"];

          }
          $postingOrdersHoldingTO->sourceDocumentNo = $dtl["debitCreditReference"][0]["reference"]["entityIdentification"]["uniqueCreatorIdentification"];
          $postingOrdersHoldingTO->documentNo=$postingOrdersHoldingTO->clientDocumentNo;

          $postingOrdersHoldingTO->deliveryDate = "";

          $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
          $postingOrdersHoldingDetailTO->updateProductVATRate = "N";
          $postingOrdersHoldingDetailTO->productCode = "";
          $postingOrdersHoldingDetailTO->productSKUGTIN = preg_replace("/^0*/","",$dtl["subLineDetail"]["tradeItemIdentification"]["gtin"]);
          foreach ($dtl["subLineDetail"]["tradeItemIdentification"]["additionalTradeItemIdentification"] as $p) {
            $postingOrdersHoldingDetailTO->productName .= (($postingOrdersHoldingDetailTO->productName=="")?"":"-").$p["additionalTradeItemIdentificationValue"];
          }
          $postingOrdersHoldingDetailTO->quantity = $dtl["subLineDetail"]["quantity"]; // can be ZERO if only pricing claim !

          if ($dtl["subLineDetail"]["quantity"]> 0 ) {
             $postingOrdersHoldingDetailTO->listPrice = ($dtl["amount"]["monetaryAmount"] / $dtl["subLineDetail"]["quantity"]); // signage is important - you can get a net credit on total result, but some lines may be debits and others credits
          } else {
             $postingOrdersHoldingDetailTO->listPrice = ($dtl["amount"]["monetaryAmount"]); // signage is important - you can get a net credit on total result, but some lines may be debits and others credits
          }	
          /* claims values are calculated only on processing in ordersHoldingProcessing once the VAT settings are determined
          // interestingly, a zero quantity and a > 0 qty can both be pricing errors ??
          if (intval($postingOrdersHoldingDetailTO->quantity)==0) {
            $postingOrdersHoldingDetailTO->extPrice = $postingOrdersHoldingDetailTO->listPrice;
            $postingOrdersHoldingDetailTO->nettPrice = $postingOrdersHoldingDetailTO->listPrice;
          } else {
            // $postingOrdersHoldingDetailTO->extPrice = abs($dtl["subLineDetail"]["alignedPrice"]["monetaryAmount"] - $dtl["subLineDetail"]["invoicedPrice"]["monetaryAmount"]);
            $postingOrdersHoldingDetailTO->extPrice = $postingOrdersHoldingDetailTO->listPrice * $postingOrdersHoldingDetailTO->quantity;
            $postingOrdersHoldingDetailTO->nettPrice = round($postingOrdersHoldingDetailTO->extPrice / $postingOrdersHoldingDetailTO->quantity,2);
          }
          */


          $postingOrdersHoldingDetailTO->discountValue = 0;
          $postingOrdersHoldingDetailTO->discountReference="";
          // do not apply VAT - vat is assigned during processing as Checkers require us to control this.
          $postingOrdersHoldingDetailTO->vatRate = "";
          $postingOrdersHoldingDetailTO->vatAmount = "";
          // $postingOrdersHoldingDetailTO->totalPrice = $postingOrdersHoldingDetailTO->extPrice; // this must be calculated otherwise the COMPUTE check fails in processing
          $postingOrdersHoldingDetailTO->additionalType = $dtl["adjustmentReason"]["messageReason"]; // "A4_NON_RECEIPT_OF_GOODS"

          $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

        } // detail line loop

        $arrTO[]=$postingOrdersHoldingTO;

      }
      unset($c);

    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = $arrTO;
    return $eTO;

  }

  function compareConfirmationList($submittedArr, $returnedListStr) {

    $returnedList = explode(",",trim($returnedListStr));
    $returnedListIndexed = array();
    foreach ($returnedList as $ola) {
      $returnedListIndexed[$ola]=true;
    }
    $bad=$good=array();
    foreach ($submittedArr as $ola) {
      if (!isset($returnedListIndexed[$ola])) $bad[]=$ola;
      else $good[]=$ola;
    }

    return array("good"=>$good,"bad"=>$bad);
  }

}







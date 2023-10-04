<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER.'functional/ws/lib/nusoap.php');  //soap lib
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingDetailTO.php');
include_once ($ROOT.$PHPFOLDER.'functional/import/processor/ProcessorTOH.php');
include_once ($ROOT.$PHPFOLDER.'libs/FileParser.php');

class CheckersClientREST {


  public $jobCount = 0; //counter for script output

  private $dbConn;
  private $transactionDAO;
  private $postBIDAO;
  private $client = false;
  private $currentEndPoint = false;
  private $contractId = "aa659aa2-4175-471f-8c82-59ca416723cf";


  public function __construct($dbConn) {

    $this->dbConn = $dbConn;

    $this->transactionDAO = new TransactionDAO($this->dbConn);
    $this->postBIDAO = new PostBIDAO($this->dbConn);

  }

  // Checkers will ignore a max val > 20
  public function runProcess_getOrders($principalUId, $vendorAccount, $username, $password){

      // get orders
      $callResult = $this->sendRequestForOrders(
                                          $username,
                                          $password
                                        );

      if (count($callResult->object)==0) return false;
      if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
        if (preg_match("/No downloadable/msUi",$callResult->object)) {
          echo "<p>{$callResult->object}<p>"; // No downloadable Orders for Current User: HastyDownload.
          return false;
        } else {
        echo "<p><span style='color:red'>ERROR in sendRequestForOrders CheckersClientREST.runProcess_getOrders(): </span>".$callResult->description."</p>";
        print_r($callResult);
        return false;
        }
      }

      $orders = $callResult->object;

      // Convert to OH TO
      
      $callResult = $this->setupTO_orders($principalUId, $vendorAccount, $orders);

      if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
        echo "<p><span style='color:red'>ERROR in CheckersClientREST.setupTO(): </span>".$callResult->description."</p>";
        return false;
      }

      $ordersTOH = $callResult->object;

      // Store OH
      if (count($ordersTOH)>0) {

        $processorTOH = new ProcessorTOH($this->dbConn);
        $callResult=$processorTOH->postTOH($ordersTOH);

        if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
          echo "<p><span style='color:red'>ERROR in CheckersClientREST.setupTO(): </span>".$callResult->description."</p>";
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
                                              implode(",",$orderList)
                                            );

          if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
              echo "<p><span style='color:red'>ERROR in CheckersClientREST.sendOrderConfirmation: </span>".$callResult->description."</p>";
              return false;
          }

          $result=$this->compareConfirmationList($submittedArr=$orderList, $returnedListStr=$callResult->object);
          echo "<p style='color:green'>Successfully Confirmed Orders:".$result["good"]."</p>";
          if ($result["bad"]>0) echo "<p style='color:red;'>Failed Confirmed Orders:".$result["bad"]."</p>";
        }

      } // end > 0 array

      /*
      // if NOT the full complement of max orders was returned then stop the loop and move onto next principal
      if (count($callResult->object)<20) {
        return false;
      }
      */

      return true; // execute next loop for same principal, or exit controlled by that loop

  }

  // Checkers will ignore a max val > 20
  public function runProcess_getClaims($principalUId, $vendorAccount, $username, $password){

    // get orders
    $callResult = $this->sendRequestForClaims(
                      $username,
                      $password
                  );

    if (count($callResult->object)==0) return false;
    if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
      if (preg_match("/No downloadable/msUi",$callResult->object)) {
        echo "<p>{$callResult->object}<p>"; // No downloadable Claims for Current User: HastyDownload.
        return false;
      } else {
        echo "<p><span style='color:red'>ERROR in sendRequestForClaims CheckersClient.runProcess_getClaims(): </span>".$callResult->description."</p>";
        return false;
      }
    }

     // echo "<pre>";
     // print_r($callResult->object);

    $claims = $callResult->object;

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
            implode(",",$claimList)
        );


        if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
          echo "<p><span style='color:red'>ERROR in CheckersClient.sendClaimConfirmation: </span>".$callResult->description."</p>";
          return false;
        }

        $result=$this->compareConfirmationList($submittedArr=$claimList, $returnedListStr=$callResult->object);
        echo "<p style='color:green'>Successfully Confirmed Claims:".$result["good"]."</p>";
        if ($result["bad"]>0) echo "<p style='color:red;'>Failed Confirmed Claims:".$result["bad"]."</p>";
      }

    } // end > 0 array

    return true; // execute next loop for same principal, or exit controlled by that loop

  }


    public function sendRequestForOrders($username, $password){
      global $ROOT, $PHPFOLDER;

      return $this->WebServiceCall($logFolder="checkers", $endPoint=WS_URL_CHECKERS_ORDERS_REST."VendorOrder", $username, $password, CURLOPT_HTTPGET);

    }

    public function sendRequestForClaims($username,$password) {
      global $ROOT, $PHPFOLDER;

      return $this->WebServiceCall($logFolder="checkers", $endPoint=WS_URL_CHECKERS_CLAIMS_REST."VendorClaim", $username, $password, CURLOPT_HTTPGET);

    }

    public function sendOrderConfirmation($username,$password,$orderList){
      global $ROOT, $PHPFOLDER;

      // replace values
      $sendJSON = '['.$orderList.']';

      return $this->WebServiceCall($logFolder="checkers",
                                   $endPoint=WS_URL_CHECKERS_ORDERS_REST."VendorOrder?action=Acknowledge",
                                   $username,
                                   $password,
                                   CURLOPT_PUT,
                                   $sendJSON,
                                   "application/json");

    }

    public function sendClaimConfirmation($username, $password, $claimList){
      global $ROOT, $PHPFOLDER;

      $errorTO = new ErrorTO();

      // replace values
      $sendJSON = '['.$claimList.']';

      return $this->WebServiceCall($logFolder="checkers",
                                   $endPoint=WS_URL_CHECKERS_CLAIMS_REST."VendorClaim?action=Acknowledge",
                                   $username,
                                   $password,
                                   CURLOPT_PUT,
                                   $sendJSON,
                                   "application/json");

    }


    private function WebServiceCall($logFolder, $endPoint, $username, $password, $type, $sendPayload="", $contentType="application/xml; charset=utf-8"){
      global $ROOT, $PHPFOLDER;

      $errorTO = new ErrorTO();

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $endPoint);
      if ($type==CURLOPT_PUT) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // otherwise POSTFIELDS conflicts
      else curl_setopt($ch, $type, 1); // GET, POST etc.
      // curl_setopt($ch, CURLOPT_PORT, 9443);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // get text to be returned
      curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
      curl_setopt($ch, CURLOPT_VERBOSE, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
      curl_setopt($ch, CURLOPT_TIMEOUT, 100);
      curl_setopt($ch, CURLOPT_HEADER, false); // we dont want the headers as part of responseData, else $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); $body = substr($response, $header_size);
 
//       	echo($ch);
//       	echo CURLOPT_POSTFIELDS;

      if (!empty($sendPayload)) {

        curl_setopt($ch, CURLOPT_POSTFIELDS, $sendPayload);
      }

      $headers = array();
      $headers[] = 'content-type: '.$contentType; // request content
      $headers[] = 'Accept: text/xml'; // response content
      $headers[] = 'ContractID: '.$this->contractId;
      $headers[] = 'UIUser:'.$username;
      
//      print_r($headers);

      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      
//      echo CURLOPT_HTTPHEADER;

      // response of the POST request
      $responseData = curl_exec($ch);
      $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE); // application/json;charset=UTF-8     

      if ($responseData === false) $responseData = curl_error($ch);
      curl_close($ch);

      if (
          (!preg_match("/success\": false/msUi",$responseData)) &&
          (
            // orders response
            (
              (preg_match("/StandardBusinessDocumentHeader>/msUi",$responseData)) &&
              // (preg_match("/<orderMessage/msU", $responseData)) && // not part of array obj after parsing
              (preg_match("/<order/msU", $responseData))
            ) ||
            // orders acknowledge response
            (
                (preg_match("/Number of Orders Acknowledged/msUi", $responseData))
            ) ||
            // claims response
            (
            (preg_match("/StandardBusinessDocumentHeader>/msUi",$responseData)) &&
            // (preg_match("/<orderMessage/msU", $responseData)) && // not part of array obj after parsing
            (preg_match("/<claimsNotification/msUi", $responseData))
            ) ||
            // claims acknowledge response
            (
                (preg_match("/Number of Claims that were Acknowledged/msUi", $responseData))
            ) ||
            // invoice upload acknowledge response
            (
                (preg_match("/Success/msUi", $responseData)) // 1 record was successfully inserted
            )
          )
         ) {

            // acknowledge returns :
            // Array ( [0] => Number of Orders Acknowledged: 1 )

            try {
            $result = FileParser::xmlToArray($responseData);
            $result["rawClass"] = simplexml_load_string($responseData); // to be able to get the attribute vals (some get lost under array conversion. This class uses "->" to access elements
            } catch (Exception $e) {
              echo '1. Caught exception: ',  $e->getMessage(), "\n";
              print_r($responseData);
            }

            $errorTO->type = FLAG_ERRORTO_SUCCESS;
            $errorTO->description = "Successfully Fetched/Sent Documents";
            $errorTO->object = $result;

      } else {

        $errorTO->type = FLAG_ERRORTO_ERROR;

        if (strpos($contentType, "application/json")!==false) {
          $json = json_decode($responseData, true);
        }
        
        $errorTO->description = "Rejected by Checkers with : ".( (isset($json["fault"]["faultstring"])) ? $json["fault"]["faultstring"] : $responseData).
                                 " ".
                                 ( (isset($json["fault"]["detail"])) ? $json["fault"]["detail"] : "");
        $errorTO->identifier = ( (isset($json["fault"]["faultcode"])) ? $json["fault"]["faultcode"] : "");
        $errorTO->object = $responseData;


        /*
         * for some reason error always gets returned as JSON

         {
         "success": false,
         "fault": { "faultcode": "SH-401",
         "faultstring": "Invalid Contract ID",
         "faultactor": "https://externalservicesqa.shopriteholdings.co.za:9443/b2bservice/api/VendorOrder",
         "detail": "ERROR: Invalid Service ContractID=. Please contact your service provider/administrator for correct contract details"
         }
         }
         */


      }

      return $errorTO;
    }


    function setupTO_orders($principalUId, $vendorAccount, &$orders) {
    	global $ROOT, $PHPFOLDER;
      $eTO = new ErrorTO();
      
      if (
          (isset($orders["order"])) &&
          (!isset($orders["order"][0]))
          ) {

            $temp = $orders["order"];
            unset($orders["order"]);
            $orders["order"][0] = $temp;

      }

      // Basic Validation first
      if (
          !isset($orders["StandardBusinessDocumentHeader"]) ||
          !isset($orders["order"]) ||
          !isset($orders["order"][0]["orderLineItem"])
         ) {
           $eTO->type = FLAG_ERRORTO_ERROR;
           $eTO->description = "Order Structural problem in CheckersClientREST @".date(GUI_PHP_DATETIME_FORMAT);
           $eTO->identifier = ET_SYSTEM;
           return $eTO;
      }

      $arrTO = array();

      // NOTE:
      // Because these rows are returned in batch we just skip the current document if there is an error with it as
      // when we send back the confirmation then that document is therefore not confirmed and it will just keep resending
      foreach ($orders["order"] as &$o) {

          $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
          $postingOrdersHoldingTO->updateProduct="N";
          $postingOrdersHoldingTO->principalUid=$principalUId;
          $postingOrdersHoldingTO->orderDate = substr($o["creationDateTime"],0,10);
          $postingOrdersHoldingTO->deliveryDate = ""; // set later again
          $postingOrdersHoldingTO->clientDocumentNo="";
          $postingOrdersHoldingTO->documentNo=$postingOrdersHoldingTO->clientDocumentNo;
          $postingOrdersHoldingTO->vendorUid = V_CHECKERS_VENDOR;
          $postingOrdersHoldingTO->vendorReference = $vendorAccount; // $o["seller"]["additionalPartyIdentification"][2] ~ additionalPartyIdentificationTypeCode=BUYER_ASSIGNED_VENDOR_ID

          $postingOrdersHoldingTO->dataSource = DS_WS;
          $postingOrdersHoldingTO->capturedBy = 'CHECKERS';
          $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
          $postingOrdersHoldingTO->chainLookupRef=""; // processing will assign generic chain to it.
          $postingOrdersHoldingTO->storeLookupRef=""; // ???
          $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
          $postingOrdersHoldingTO->principalStoreUId=""; // let the processing script lookup the store
          $postingOrdersHoldingTO->reference=$o["orderIdentification"]["entityIdentification"];
          if (trim($postingOrdersHoldingTO->reference)=="") {
            echo "The Order No is blank for vendor account ({$vendorAccount}) in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT);
            continue;
          }

          if (!isset($o["buyer"]["additionalPartyIdentification"][0])) $o["buyer"]["additionalPartyIdentification"] = array($o["buyer"]["additionalPartyIdentification"]);
          $postingOrdersHoldingTO->deliverName = ( (isset($o["buyer"]["additionalPartyIdentification"][0])) ? $o["buyer"]["additionalPartyIdentification"][0] : "" ) . " " . ( (isset($o["buyer"]["additionalPartyIdentification"][1])) ? $o["buyer"]["additionalPartyIdentification"][1] : "" );
          $postingOrdersHoldingTO->shipToName = $postingOrdersHoldingTO->deliverName;

          $postingOrdersHoldingTO->oldAccount = "";
          $postingOrdersHoldingTO->shipToGLN = $o["buyer"]["gln"]; // this is also stored against each line item - hopefully it is the same value
          if (trim($postingOrdersHoldingTO->shipToGLN)=="") {
            // do not allow to continue - reject the file and ITD will be notified in confirmation
            echo "The EAN / GLN is blank for order ({$postingOrdersHoldingTO->reference}) vendor account ({$vendorAccount}) in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT);
            continue;
          }
          if($o["buyer"]["gln"] == '6001001360104') {
          	file_put_contents($ROOT.$PHPFOLDER.'log/chorder.txt', print_r($o, TRUE), FILE_APPEND); 
          }
          
 //        print_r( $o["orderLineItem"]);

          // if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
          if (!isset($o["orderLineItem"][0])) {
            $o["orderLineItem"] = array($o["orderLineItem"]);
          }

          foreach ($o["orderLineItem"] as $ol) {
          	
            if ($ol["orderLineItemDetail"]["orderLogisticalInformation"]["orderLogisticalDateInformation"]["requestedDeliveryDateTime"]["date"]!="")
             $postingOrdersHoldingTO->deliveryDate = $ol["orderLineItemDetail"]["orderLogisticalInformation"]["orderLogisticalDateInformation"]["requestedDeliveryDateTime"]["date"];

            $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

            /* if you need in future to access the attributes to make sure additionalTradeItemIdentificationTypeCode=SUPPLIER_ASSIGNED_ITEMID is always in the
             * first array position, then you will need to call simple_xml_load_string($data) directly and then access each attr by:
             * foreach($x->order[0]->orderLineItem->transactionalTradeItem->additionalTradeItemIdentification[0]->attributes() as $a => $b) {
                echo $a,'="',$b,"\"\n";
              }
             */
            $postingOrdersHoldingDetailTO->productSKUGTIN = preg_replace("/^[0]+/","",$ol["transactionalTradeItem"]["gtin"]); // Checkers use innercasing GTIN
            $postingOrdersHoldingDetailTO->productGTIN   = preg_replace("/^[0]+/","",$ol["transactionalTradeItem"]["gtin"]); // Checkers use innercasing GTIN
            $postingOrdersHoldingDetailTO->productName = $ol["transactionalTradeItem"]["tradeItemDescription"];
            $postingOrdersHoldingDetailTO->productCode = ""; // $ol["transactionalTradeItem"]["additionalTradeItemIdentification"][0];

            if (!isset($ol["transactionalTradeItem"]["additionalTradeItemIdentification"][0])) {
              $ol["transactionalTradeItem"]["additionalTradeItemIdentification"] = array($ol["transactionalTradeItem"]["additionalTradeItemIdentification"]);
            }
            
            if (trim($postingOrdersHoldingTO->deliveryInstructions) =='' && trim($ol["additionalOrderLineInstruction"]) !='') {
            	  $postingOrdersHoldingTO->deliveryInstructions = $ol["additionalOrderLineInstruction"];
            }

            $postingOrdersHoldingDetailTO->quantity = $ol["requestedQuantity"]; // also in orderLineItemDetail struct
            $postingOrdersHoldingDetailTO->listPrice = $ol["monetaryAmountExcludingTaxes"]; // item price, $ol["netAmount"] is order price
            $postingOrdersHoldingDetailTO->nettPrice = $postingOrdersHoldingDetailTO->listPrice; // no discount so same
            $postingOrdersHoldingDetailTO->extPrice = $ol["netAmount"]; // listPrice * quantity

            // do not apply VAT - vat is assigned during processing as Checkers require us to control this.
            $postingOrdersHoldingDetailTO->vatRate = "";
            $postingOrdersHoldingDetailTO->vatAmount = "";

            $postingOrdersHoldingDetailTO->totalPrice = $postingOrdersHoldingDetailTO->extPrice; // this must be calculated otherwise the COMPUTE check fails in processing

            $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

          }

          $arrTO[]=$postingOrdersHoldingTO;

      }

      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;

    }

    function setupTO_claims($principalUId, $vendorAccount, &$claims) {
      $eTO = new ErrorTO();

      // Basic Validation first
      if (
          !isset($claims["claimsNotification"])
      ) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Claims Structural problem in CheckersClientREST @".date(GUI_PHP_DATETIME_FORMAT);
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }

      // if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
      if (!isset($claims["claimsNotification"][0])) {
        $claims["claimsNotification"] = array($claims["claimsNotification"]);
      }

      $arrTO = array();

      // NOTE:
      // Because these rows are returned in batch we just skip the current document if there is an error with it as
      // when we send back the confirmation then that document is therefore not confirmed and it will just keep resending

      // ALSO :
      // 1. sometimes we need to strval() a attribute as if it has an attribute in the tag (@attributeName) and the val is empty then it is a class such as Array ( [@attributes] => Array ( [additionalPartyIdentificationTypeCode] => BUYER_ASSIGNED_DAO_GLN ) )
      foreach ($claims["claimsNotification"] as $key1=>&$c) {

        // these are also repeated under the detail loop for each line, but we just use the summary
        $netClaimIndicator = $claims["rawClass"]->claimsNotification[$key1]->avpList->eComStringAttributeValuePairList->attributes()->attributeName;

        $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
        $postingOrdersHoldingTO->updateProduct="N";
        $postingOrdersHoldingTO->principalUid=$principalUId;
        $postingOrdersHoldingTO->dataSource = DS_WS;
        $postingOrdersHoldingTO->capturedBy = 'CHECKERS';
        $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
        $postingOrdersHoldingTO->chainLookupRef=""; // processing will assign generic chain to it.
        $postingOrdersHoldingTO->storeLookupRef=""; // ???
        $postingOrdersHoldingTO->documentTypeUId = (($netClaimIndicator=="CREDIT_NOTE")?DT_BUYER_ORIGINATED_CREDIT_CLAIM:DT_BUYER_ORIGINATED_DEBIT_CLAIM);
        $postingOrdersHoldingTO->principalStoreUId=""; // let the processing script lookup the store
        $postingOrdersHoldingTO->shipToGLN = $c["buyer"]["gln"];
        if (trim($postingOrdersHoldingTO->shipToGLN)=="") {
          // do not allow to continue - reject the file and ITD will be notified in confirmation
          echo "The EAN / GLN is blank for order ({$postingOrdersHoldingTO->reference}) vendor account ({$vendorAccount}) in CheckersClient for principal {$principalUId} @".date(GUI_PHP_DATETIME_FORMAT);
          continue;
        }

        // NB! This wsUniqueCreatorId is used in the claimsAck response !
        $postingOrdersHoldingTO->wsUniqueCreatorId=$c["claimsNotificationIdentification"]["entityIdentification"];
        $postingOrdersHoldingTO->reference = $c["claimsNotificationIdentification"]["entityIdentification"];
        $postingOrdersHoldingTO->clientDocumentNo =  $c["claimsNotificationIdentification"]["entityIdentification"];
        $postingOrdersHoldingTO->sourceDocumentNo = $c["purchaseOrder"]["entityIdentification"]; // creationDateTime is the date field
        $postingOrdersHoldingTO->documentNo = $postingOrdersHoldingTO->clientDocumentNo;

        $postingOrdersHoldingTO->vendorUid = V_CHECKERS_VENDOR;
        $postingOrdersHoldingTO->vendorReference = "";
        $postingOrdersHoldingTO->additionalDetails = ""; // no truck or any other data
        $postingOrdersHoldingTO->deliveryDate = "";

        if (!isset($c["buyer"]["additionalPartyIdentification"][0])) $c["buyer"]["additionalPartyIdentification"] = array($c["buyer"]["additionalPartyIdentification"]);
        foreach ($c["buyer"]["additionalPartyIdentification"] as $buyer) {
          $postingOrdersHoldingTO->deliverName .= (($postingOrdersHoldingTO->deliverName=="")?"":"-").((is_array($buyer))?"":$buyer);
        }
        $postingOrdersHoldingTO->shipToName = $postingOrdersHoldingTO->deliverName;
        $postingOrdersHoldingTO->oldAccount = "";

        // just use first line at header level
        $postingOrdersHoldingTO->additionalType = strval($c["claimsNotificationTypeCode"])." ".strval($c["supplementalMessageDescription"]); // "DISPUTE" ." ". "overcharge" // pricing, shortage, overcharge
        $postingOrdersHoldingTO->orderDate = substr($c["creationDateTime"],0,10);

        // loop thru each claim line
        if (!isset($c["claimsNotificationDiscrepancyInformation"][0])) $c["claimsNotificationDiscrepancyInformation"] = array($c["claimsNotificationDiscrepancyInformation"]);
        foreach ($c["claimsNotificationDiscrepancyInformation"] as $key2=>$dtl) {

          $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
          $postingOrdersHoldingDetailTO->updateProductVATRate = "N";

          $avpList = [];
          $avpList["claimAmount"] = "claimAmount = ".$dtl["claimAmount"];
          foreach($claims["rawClass"]->claimsNotification[$key1]->claimsNotificationDiscrepancyInformation[$key2]->avpList->eComStringAttributeValuePairList as $field) {
            $avpList[] = strval($field->attributes()->attributeName)." = ".strval($field);
          }

          /*
          claimAmount = 46.85
          CLAIM_INVOICE_NUMBER = 00622090
          CLAIM_INVOICE_DATE = 2016-07-29 12:00:00 AM
          CLAIM_INVOICE_WEIGHT =
          CLAIM_INVOICE_LINE_NUMBER = 1
          CLAIM_VATRATE = 14
          CLAIM_COSTPER = 24
          CLAIM_CONTRACTNUMBER = 2006227890
          CLAIM_UNITOFMEASURE = EA
          CLAIM_ITEM_PACKSIZE = 24
          CLAIM_ASSOCIATEDCLAIMNO =
          TRUCK_DRIVER =
          TRUCK_REGISTRATION =
          CLAIM_COSTEXCL = 349.35
          CLAIM_COSTTAX = 48.91
          CLAIM_VENDOR_COSTEXCL = 390.45
          CLAIM_VENDOR_UNITCOST = 445.11
          CLAIM_ITEM_COSTPRICE = 398.26

          The problem is that print_r(SimpleXML) doesn't show attributes and text on the same element. so array with ["@attributes"] is only created for empty nodes
          */
          $postingOrdersHoldingDetailTO->productCode = $dtl["expectedToReceive"]["transactionalTradeItem"]["additionalTradeItemIdentification"][2]; // vs ["actualReceived"]
          $postingOrdersHoldingDetailTO->productSKUGTIN = preg_replace("/^0*/","",$dtl["expectedToReceive"]["transactionalTradeItem"]["gtin"]); // ["additionalTradeItemIdentification"][1] contains outerGTIN
          $postingOrdersHoldingDetailTO->productName = $dtl["expectedToReceive"]["transactionalTradeItem"]["tradeItemDescription"];
          $postingOrdersHoldingDetailTO->quantity = $dtl["expectedToReceive"]["quantity"];

          // $dtl["expectedToReceive"]["price"] is excl vat and is extPrice
          $postingOrdersHoldingDetailTO->listPrice = ( (abs($postingOrdersHoldingDetailTO->quantity) > 0) ? round($dtl["expectedToReceive"]["price"] / $postingOrdersHoldingDetailTO->quantity,3) : "");
          $postingOrdersHoldingDetailTO->nettPrice = $postingOrdersHoldingDetailTO->listPrice;
          $postingOrdersHoldingDetailTO->extPrice = $dtl["expectedToReceive"]["price"];

          $postingOrdersHoldingDetailTO->discountValue = 0;
          $postingOrdersHoldingDetailTO->discountReference="";
          // do not apply VAT - vat is assigned during processing as Checkers require us to control this.
          $postingOrdersHoldingDetailTO->vatRate = "";
          $postingOrdersHoldingDetailTO->vatAmount = "";
          // $postingOrdersHoldingDetailTO->totalPrice = $postingOrdersHoldingDetailTO->extPrice; // this must be calculated otherwise the COMPUTE check fails in processing
          $postingOrdersHoldingDetailTO->additionalType = $dtl["discrepancyDescription"]."\n".implode("\n", $avpList); // "unknown" mostly

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

    // acknowledge returns :
    // Array ( [0] => Number of Orders Acknowledged: 1 )

    $parts = explode(":", $returnedListStr[0]);
    return array( "good"=>intval($parts[1]), "bad"=>(count($submittedArr)-intval($parts[1])) );

    /*
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
    */
  }



  /* *********************************
   * CLAIMS
   ********************************* */

  public function runProcess_uploadInvoice(){
  	
    //START PROCESS
    $rs = $this->transactionDAO->getCheckersWSInvoiced();

    $this->jobCount = count($rs);

    if(count($rs)==0){

      echo 'No Queued Items<BR>';

    } else {

      // checkers upload supports multiple invoices in one call, but for time being leave as 1 per upload as im not sure what will happen if 1 out of 20 fails
      foreach($rs["header"] as $doc){

        $username = $doc["username"];
        $password = $doc["password"];

        $seTO = new SmartEventTO();

        $seTO->type = SE_INVOICE_UPLOAD;
        $seTO->typeUid = $doc["principal_uid"];
        $seTO->dataUid = array($doc["uid"]);

        // Checkers probably same as PnP where we must not send zero quantity lines or zero value lines / invoice !
        $hasLines=false;
        foreach($rs["detail"][$doc["uid"]] as $key=>$line) {
          if ((floatval($line["total"])>0) && ($line["document_qty"]>0)) {
            $hasLines = true;
          } else {
            unset($rs["detail"][$doc["uid"]][$key]);
          }
        }

        if ($hasLines) {
          $callResult = $this->UploadInvoice(
              $doc,
              $rs["detail"][$doc["uid"]],
              $username,
              $password
          );


          $seTO->status = $callResult->type;
          $seTO->statusMsg = $callResult->description;

        } else {

          $seTO->status = FLAG_ERRORTO_INFO; // use this status so that reporting will show the appropriate message
          $seTO->statusMsg = "Successful - Zero priced Invoice is not uploaded";

        }

        // var_dump($callResult);

        //
        // interpret callResult
        //


        $resultTO = $this->postBIDAO->postSmartEventBulk($seTO); // used instead of single insert method due to field usage

        if ($resultTO->type != FLAG_ERRORTO_SUCCESS) {
          echo "<br><span style='color:red'>ERROR creating SMART EVENT in CheckersClientREST.runProcess(): </span>".$resultTO->description;
          $this->dbConn->dbinsQuery("rollback");
        } else {
          $this->dbConn->dbinsQuery("commit");
        }

        // email RT admin staff for urgent action if error in upload
        // - excl INFO status
        if ($seTO->status == FLAG_ERRORTO_ERROR) {
          BroadcastingUtils::sendSupportEmail("Please Action! Checkers Invoice Upload Error",
                                              "The System atempted to upload an invoice to the Checkers System but failed with the following reason :\n\n{$seTO->statusMsg}\n\nPlease correct the data ASAP as this is a time-critical service",
                                              "Y",
                                              false);
        }

      }

    }

  }

  public function UploadInvoice($header, $detail, $username, $password){
    global $ROOT, $PHPFOLDER;

    $errorTO = new ErrorTO();

    require_once($ROOT.$PHPFOLDER.'functional/ws/client/CheckersClientREST.php');
    //data template to send.
    include($ROOT.$PHPFOLDER.'functional/ws/templates/CheckersInvoiceSendingData.php');
    include($ROOT.$PHPFOLDER.'functional/ws/templates/CheckersInvoiceDetailSendingData.php');

    $uniqueCreatorId = $header["uid"];
    $invoiceNumber = str_pad(trim(((empty($header["invoice_number"]))?$header["document_number"]:$header["invoice_number"])),8,"0",STR_PAD_LEFT);
    $invoiceNumber = preg_replace("/^[0]+/","",$invoiceNumber); // Checkers might have same rule as PnP which is inv number must not have any leading zeros, even if the number is only 4 digits long
    $deliveryDate = ((empty($header["delivery_date"]) || $header["delivery_date"]=="0000-00-00")?$header["order_date"]:$header["delivery_date"]);

    // basic validations for submitted data
    $errors=array();
    if (trim($header["principal_gln"])=="") $errors[]="Principal GLN not supplied";
    if (trim($header["ean_code"])=="") $errors[]="Store GLN not supplied";
    if (trim($header["invoice_date"])=="" || trim($header["invoice_date"])=="0000-00-00") $errors[]="Invoice Date not supplied";
    if (trim($deliveryDate)=="" || trim($deliveryDate)=="0000-00-00") $errors[]="Delivery Date not supplied";
    if (trim($invoiceNumber)=="") $errors[]="Invoice Number not supplied";
    if (trim($header["principal_vat_number"])=="") $errors[]="Principal VAT Number not supplied";
    if (!preg_match("/^[0-9]{0,1}[0-9]{".strlen($header['customer_order_number']) . "}$/",$header["customer_order_number"])) $errors[]="Invalid Order PO Number ({$header["customer_order_number"]}) for {$header["principal_name"]}, Doc {$header["document_number"]}";

    $now = date("c"); // ISO 8601 ~ 2013-01-30T12:42:31+02:00

    // replace values

    $sendXML = str_replace(array("&&var_uploadDateTime&&",
        "&&var_principalGLN&&",
        "&&var_storeGLN&&",
        "&&var_uniqueCreatorId&&",
        "&&var_invoiceDate&&",
        "&&var_invoiceNumber&&",
        "&&var_sellerVATNumber&&",
        "&&var_invoiceTotalInclusive&&",
        "&&var_invoiceTotalExclusive&&",
        "&&var_invoiceTotalVatAmount&&",
        "&&var_purchaseOrderReference&&",
        "&&var_invoiceCount&&"
    ),
        array($now,
            $header["principal_gln"],
            $header["ean_code"],
            $uniqueCreatorId,
            $header["invoice_date"],
            $invoiceNumber,
            $header["principal_vat_number"],
            $header["invoice_total"],
            $header["exclusive_total"],
            $header["vat_total"],
            $header["customer_order_number"],
            1
        ),
            $sendXML);

    $dtlArr = array();
    foreach($detail as $row) {
    	
      // basic validations for submitted data cont...
      if (!preg_match(GUI_PHP_INTEGER_REGEX,$row["line_no"])) $errors[]="Invalid Line Number";
      if (!preg_match("/^[0-9]{8,}$/",$row["sku_gtin"])) $errors[]="Invalid Product ({$row["product_code"]}) GTIN for {$header["principal_name"]}, Doc {$header["document_number"]}, PO {$header["customer_order_number"]}";

      $weight = ""; // ($row["weight"]) Checkers might have same rule as PnP which requires that if we send a weight (ours are all "1" anyway), then they require a proper unitOfMeasure, so we don't send weight
      $dtlArr[] = str_replace(array("&&var_lineNumber&&",
                                    "&&var_productGTIN&&",
                                    "&&var_productCode&&",
                                    "&&var_quantity&&",
                                    "&&var_transferOfOwnershipDate&&",
                                    "&&var_extendedPriceExclusive&&",
                                    "&&var_productDescription&&",
                                    "&&var_sellingPriceExclusive&&",
                                    "&&var_extendedPriceInclusive&&",
                                    "&&var_vatAmount&&",
                                    "&&var_vatRate&&",
                                    "&&var_vatCategory&&",
                                    "&&var_productWeight&&",
                                    "&&var_productWeightUnit&&",
                                    "&&var_productPackSize&&",
                                    "&&var_invoiceNumber&&",
                                    "&&var_invoiceDetailRef&&"
                              ),
                              
                              array($row["line_no"],
                                    $row["outercasing_gtin"],
                                    str_replace("&","",$row["product_code"]),
                                    $row["document_qty"],
                                    $deliveryDate,
                                    $row["net_price"],
                                    str_replace("&","",$row["product_description"]),
                                    $row["net_price"],
                                    $row["net_price"] * (1 + $row["vat_rate"] / 100),
                                    $row["vat_amount"] / $row["document_qty"],
                                    $row["vat_rate"],
                                    (($row["vat_rate"]>0)?"STANDARD":"ZERO"),
                                    $weight,
                                    "",
                                    $row["items_per_case"],
                                    $invoiceNumber,
                                    ""
                              ),
                              $sendXMLDtl);
      }

      if (sizeof($errors)>0) {
        echo "<br>Could not export Checkers Invoice via WS (dm_uid:{$header["uid"]}): ".implode("<br>",$errors);
        $errorTO->type = FLAG_ERRORTO_ERROR;
        $errorTO->description = implode(",",$errors);
        return $errorTO;
      }

echo "LL";

print_R($sendXML);

      $sendXML = str_replace("&&var_invoiceDetailLines&&",implode("",$dtlArr), $sendXML);
      
      file_put_contents($ROOT.$PHPFOLDER."log/log.xml", $sendXML);
      


      
      return $this->WebServiceCall($logFolder="checkers_invoice",
                                   $endPoint=WS_URL_CHECKERS_INVOICE_REST."VendorInvoice",
                                   $username,
                                   $password,
                                   CURLOPT_POST,
                                   $sendPayload=$sendXML);

  }


}


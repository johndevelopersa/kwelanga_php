<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingDetailTO.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');


class AdaptorGS1GIOrder {

	private $dbConn;
	private $orderArr;
	public  $principalUid = '';
	public  $principalGLN;
	public  $orderNo = 0;  //currently only used to pass to the logger.
	// required fields across all adaptors
	public  $userName = '';
	public  $passWord = '';
	public  $vendorUid = 0;
	public  $vendorGln;
	public  $errorStatus = false; //false : NO ERRORS
	public  $errorParty;
	public  $errorDescription;
	public  $postingOrdersHoldingTO;

	public function __construct($dbConn, $orderArr) {

		$this->dbConn = $dbConn;

		// the isset() function will raise a fatal error if the orderArr is not an array, but is an object already as opposed to just unset.
		if (!is_array($orderArr)) {
			$this->errorDescription = 'Incorrect Request XML supplied.';
			$this->errorParty = 'SOAP: Client';
			$this->errorStatus = 1;
			return false;
		} else $this->orderArr = $orderArr;

    //login
    if (!$this->setCredentials()) {
    	$this->errorDescription = 'Vendor Login Failure';
			$this->errorParty = 'SOAP: Client';
			$this->errorStatus = 1;
			return false;
    };

     if (!$this->setPrincipal()) {
    	$this->errorDescription = 'Seller GLN invalid or not Supplied';
			$this->errorParty = 'SOAP: Client';
			$this->errorStatus = 1;
			return false;
    };

		if(!$this->validateGS1()){
			$this->errorDescription = 'Failed Validation';
			$this->errorParty = 'SOAP: Client';
			$this->errorStatus = 3;
			return false;
		}

		if(!$this->buildGIOrder()){
			$this->errorDescription .= ' Failed To Submit Order.';
			$this->errorParty = 'SOAP: Server';
			$this->errorStatus = 5;
			return false;
		}

		return true;
   }

	private function setCredentials(){
		if(!isset($this->orderArr['result']['StandardBusinessDocumentHeader']['Sender']['Identifier']['!'])){
			return false;
		}
		//$this->vendorGln = $this->orderArr['result']['message']['transaction']['entityIdentification']['contentOwner']['gln'];
		// if a tag has an attribute specified, then the tag value gets put into [!
		$this->vendorGln = $this->orderArr['result']['StandardBusinessDocumentHeader']['Sender']['Identifier']['!'];

		return true;

	}

  private function setPrincipal(){

	  	if(!isset($this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['receivingAdvice']['shipper']['gln'])){
	  		return false;
	  	}

	  	$this->principalGLN = $this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['receivingAdvice']['shipper']['gln'];
	  	$this->principalUid = '';

		return true;
  }

  private function validateGS1(){

		//ENTERING... Check Fields
    $orderFields = &$this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['receivingAdvice'];

    // if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
    if (!isset($orderFields['receivingAdviceItemContainmentLineItem'][0])) {
      $temp=$orderFields['receivingAdviceItemContainmentLineItem'];
      unset($orderFields['receivingAdviceItemContainmentLineItem']);
      $orderFields['receivingAdviceItemContainmentLineItem'][0]=$temp;
    }

    //Order Number
    if(empty($orderFields['receivingAdviceItemContainmentLineItem'][0]['purchaseOrder']['documentReference']['uniqueCreatorIdentification'])){
      return false;
    }

    // every row should be the same but we don't check it
    $this->orderNo = $orderFields['receivingAdviceItemContainmentLineItem'][0]['purchaseOrder']['documentReference']['uniqueCreatorIdentification'];


    foreach($orderFields['receivingAdviceItemContainmentLineItem'] as $orderLine){

      //Product GTIN Mandatory
      if(empty($orderLine['containedItemIdentification']['gtin'])){
        return false;
      }

      //Quantity
      if(!isset($orderLine['quantityAccepted']['value']) || !is_numeric($orderLine['quantityAccepted']['value'])){
        return false;
      }
    }


		//Order Creation Date
		if(empty($orderFields['!creationDateTime'])){
			return false;
		}

		return true;

  }

	private function buildGIOrder(){

		  //Build TO for DAO.
		unset($this->postingOrdersHoldingTO);
	    $postingOrdersHoldingTO = new PostingOrdersHoldingTO();

		// if a tag has an attribute specified, then the tag value gets put into [!]
    // The literal GLN is the original "rt" GLn lent to us by PnP before we had ours.
    if(!in_array($this->orderArr['result']['StandardBusinessDocumentHeader']['Receiver']['Identifier']['!'],array(RT_GLN,"6001007802929"))){
			$this->errorDescription = 'Content Receiver not for RT GLN';
			$this->errorParty = 'SOAP: Client';
			$this->errorStatus = 1;
			return false;
		}

		$orderFields = $this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['receivingAdvice'];
		$postingOrdersHoldingTO->onlineFileProcessingUId = "";
    $postingOrdersHoldingTO->principalGLN = $this->principalGLN;
  	$postingOrdersHoldingTO->principalUid = $this->principalUid;
  	$postingOrdersHoldingTO->vendorUid = $this->vendorUid;
  	$postingOrdersHoldingTO->dataSource = DS_WS;
  	$postingOrdersHoldingTO->wsUniqueCreatorId = $orderFields['receivingAdviceItemContainmentLineItem'][0]['purchaseOrder']['documentReference']['uniqueCreatorIdentification'];
  	$postingOrdersHoldingTO->documentNo="";
  	$postingOrdersHoldingTO->clientDocumentNo="";
  	$postingOrdersHoldingTO->reference = $orderFields['receivingAdviceItemContainmentLineItem'][0]['purchaseOrder']['documentReference']['uniqueCreatorIdentification'];
    $postingOrdersHoldingTO->captureDate = $this->GS1dateClean($orderFields['!creationDateTime']);
		$postingOrdersHoldingTO->orderDate = $postingOrdersHoldingTO->captureDate;
    $postingOrdersHoldingTO->deliveryDate = $postingOrdersHoldingTO->orderDate;
    $postingOrdersHoldingTO->capturedBy = CB_PNP_WS;
  	$postingOrdersHoldingTO->deliveryInstructions = $orderFields['!documentStatus'];
  	$postingOrdersHoldingTO->documentType =  $orderFields['receivingAdviceIdentification']['uniqueCreatorIdentification'];
  	$postingOrdersHoldingTO->documentTypeUId = DT_BUYER_GOODS_INWARD;
  	$postingOrdersHoldingTO->vendorReference = ""; // there is no vendor account information
  	$orderLineItem = $orderFields['receivingAdviceItemContainmentLineItem'];

  	if (!$this->setPrincipal()) {
    	$this->errorDescription = 'Seller GLN invalid or not Supplied';
  		$this->errorParty = 'SOAP: Client';
  		$this->errorStatus = 1;
  		return false;
    };

  	$postingOrdersHoldingTO->shipToName = $orderFields['shipTo']['additionalPartyIdentification']['additionalPartyIdentificationValue'];
  	$postingOrdersHoldingTO->deliverName = $postingOrdersHoldingTO->shipToName;

		$postingOrdersHoldingTO->shipToGLN = $orderFields['shipTo']['gln'];
		$postingOrdersHoldingTO->buyerGLN = $orderFields['receivingAdviceIdentification']['contentOwner']['gln'];

		//Build OrderLines Array
  	foreach($orderLineItem as $orderLine){

  		$postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

  		$postingOrdersHoldingDetailTO->quantity = $orderLine['quantityAccepted']['value'];
  		$postingOrdersHoldingDetailTO->rejectedQuantity = intval($orderLine['quantityReceived']['value'] - $postingOrdersHoldingDetailTO->quantity);

  		$postingOrdersHoldingDetailTO->productGTIN = $orderLine['containedItemIdentification']['gtin'];
  		$postingOrdersHoldingDetailTO->productCode = '';
  		$postingOrdersHoldingDetailTO->principalProductUid = "";
  		$postingOrdersHoldingDetailTO->pallets = 0;
  		$postingOrdersHoldingDetailTO->clientLineNo = $orderLine["!number"];  //keys = no of lines, no page number
  		//build product name
  		$productNameArr = array();
  		foreach($orderLine['containedItemIdentification']['additionalTradeItemIdentification'] as $productNameSet){
  			$productNameArr[] = $productNameSet['additionalTradeItemIdentificationValue'];
  		}
  		$postingOrdersHoldingDetailTO->productName = join('|',$productNameArr);

  		$postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
  	}

		$this->postingOrdersHoldingTO = $postingOrdersHoldingTO;

	    return true;

	}

	function getResponse(){

		$responseXML = $this->setResponse();

		if($responseXML == false){
	    	$this->errorDescription = ($this->errorStatus)?$this->errorDescription:'Error building response';
				$this->errorParty = 'SOAP: Server';
				$this->errorStatus = 6;
				return false;
		} else {
			return $responseXML;
		}

	}

  private function setResponse(){

	// error status might have been set elsewhere already
	if ($this->errorStatus) {
		return false;
	}

  	//if a response cannot be built, a fault will be generated.

  	//SenderEAN | Principal EAN
  	if(empty($this->principalGLN)){
  		return false;
  	}

  	//ReceiverEAN : Response SenderEAN (PNP)
 		if(empty($this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['receivingAdvice']['receivingAdviceIdentification']['contentOwner']['gln'])){
  		return false;
  	}

  	//order number, invoice number etc
  	if(empty($this->orderNo)){
  		return false;
  	}
  	$entityCreatorId = $this->orderNo;

  	//InstanceIdentifier
  	if(!isset($this->orderArr['result']['StandardBusinessDocumentHeader']['DocumentIdentification']['InstanceIdentifier'])){
  		return false;
  	}
  	$instanceId = $this->orderArr['result']['StandardBusinessDocumentHeader']['DocumentIdentification']['InstanceIdentifier'];

  	//Status
  	$responseStatus = (!$this->errorStatus) ? ('ACCEPTED') : ('REJECTED');


  	$responseXML = '<sh:StandardBusinessDocumentHeader>
							<sh:HeaderVersion>2.4</sh:HeaderVersion>
                <sh:Sender>
                    <sh:Identifier Authority="EAN.UCC">'.RT_GLN.'</sh:Identifier>
                </sh:Sender>
                <sh:Receiver>
                    <sh:Identifier Authority="EAN.UCC">'.$this->vendorGln.'</sh:Identifier>
                </sh:Receiver>
                <sh:DocumentIdentification>
                    <sh:Standard>EAN.UCC</sh:Standard>
                    <sh:TypeVersion>2.4</sh:TypeVersion>
                    <sh:InstanceIdentifier>'.$instanceId.'</sh:InstanceIdentifier>
                    <sh:Type />
                    <sh:CreationDateAndTime>'.date('Y-m-d').'T'.date('H:i:s').'</sh:CreationDateAndTime>
                </sh:DocumentIdentification>
            </sh:StandardBusinessDocumentHeader>
            <eanucc:message xmlns:eanucc="urn:ean.ucc:2">
                <entityIdentification>
                    <uniqueCreatorIdentification>'.$entityCreatorId.'</uniqueCreatorIdentification>
                    <contentOwner>
                        <gln>'.RT_GLN.'</gln>
                    </contentOwner>
                </entityIdentification>
                <transaction>
                    <entityIdentification>
                        <uniqueCreatorIdentification>'.$entityCreatorId.'</uniqueCreatorIdentification>
                        <contentOwner>
                            <gln>'.RT_GLN.'</gln>
                        </contentOwner>
                    </entityIdentification>
                    <command>
                        <eanucc:documentCommand>
                            <documentCommandHeader type="'.'ADD'.'">
                                <entityIdentification>
                                    <uniqueCreatorIdentification>'.$entityCreatorId.'</uniqueCreatorIdentification>
                                    <contentOwner>
                                        <gln>'.RT_GLN.'</gln>
                                    </contentOwner>
                                </entityIdentification>
                            </documentCommandHeader>
                            <documentCommandOperand>
                                <response responseStatus="'.$responseStatus.'">
                                    <responseIdentification>
                                        <uniqueCreatorIdentification>'.$instanceId.'</uniqueCreatorIdentification>
                                        <contentOwner>
                                            <gln>'.RT_GLN.'</gln>
                                        </contentOwner>';

  						if($responseStatus == 'REJECTED'){
  							$responseXML .= '<orderResponseReasonCode>ERROR:'.$this->errorStatus.' '.$this->errorDescription .' ('.$this->errorParty.')</orderResponseReasonCode>';
  						}

                         $responseXML .= '</responseIdentification>
                                </response>
                            </documentCommandOperand>
                        </eanucc:documentCommand>
                    </command>
                </transaction>
            </eanucc:message>';


  	return $responseXML;
  }

  function GS1dateClean($date){
    return substr($date,0,10).' '.substr($date,11);
  }

}

?>
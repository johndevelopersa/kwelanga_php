<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingDetailTO.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');


class AdaptorGS1TOrder {

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

		if(!$this->buildOrder()){
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

	  	if(!isset($this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderPartyInformation']['seller']['gln'])){
	  		return false;
	  	}

	  	$this->principalGLN = $this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderPartyInformation']['seller']['gln'];
	  	$this->principalUid = '';

		return true;
  }

  private function validateGS1(){

		//ENTERING... Check Fields
		//Order Number
		$orderFields = &$this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order'];
		if(empty($orderFields['orderIdentification']['uniqueCreatorIdentification'])){
			return false;
		}

		$this->orderNo = $orderFields['orderIdentification']['uniqueCreatorIdentification'];

		//Order Creation Date
		if(empty($orderFields['!creationDateTime'])){
			return false;
		}

		//Order Type ie: ROSTER | ALLOCATION | FREE_STOCK | STANDARD
		if(empty($orderFields['orderIdentification']['contentOwner']['additionalPartyIdentification']['additionalPartyIdentificationType'])){
			return false;
		}

		if(empty($orderFields['orderLineItem'])){
			return false;
		} else {
			// if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
	    	if (!isset($orderFields['orderLineItem'][0])) {
	    		$temp=$orderFields['orderLineItem'];
	    		unset($orderFields['orderLineItem']);
	    		$orderFields['orderLineItem'][0]=$temp;
	    	}
			foreach($orderFields['orderLineItem'] as $orderLine){

				//Product GTIN Mandatory
				if(empty($orderLine['tradeItemIdentification']['gtin'])){
					return false;
				}

				//Quantity
				if(!isset($orderLine['requestedQuantity']['value']) && !is_numeric($orderLine['requestedQuantity']['value'])){
					return false;
				}
			}
		}

		return true;

  }

	private function buildOrder(){

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

		$postingOrdersHoldingTO->onlineFileProcessingUId = "";
    $postingOrdersHoldingTO->principalGLN = $this->principalGLN;
  	$postingOrdersHoldingTO->principalUid = $this->principalUid;
  	$postingOrdersHoldingTO->vendorUid = $this->vendorUid;
  	$postingOrdersHoldingTO->dataSource = DS_WS;
  	$postingOrdersHoldingTO->wsUniqueCreatorId = $this->orderArr['result']['message']['entityIdentification']['uniqueCreatorIdentification'];
  	$postingOrdersHoldingTO->documentNo="";
  	$postingOrdersHoldingTO->clientDocumentNo="";
  	$postingOrdersHoldingTO->reference = $this->orderArr['result']['message']['entityIdentification']['uniqueCreatorIdentification'];
    $postingOrdersHoldingTO->captureDate = $this->GS1dateClean($this->orderArr['result']['StandardBusinessDocumentHeader']['DocumentIdentification']['CreationDateAndTime']);
		$postingOrdersHoldingTO->orderDate = $this->GS1dateClean($this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['!creationDateTime']);
		$postingOrdersHoldingTO->expiryDate = $this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderLogisticalInformation']['orderLogisticalDateGroup']['requestedDeliveryDateAtUltimateConsignee']['date'];
    /*-------------------------*/
    #$postingOrdersHoldingTO->deliveryDate = $this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderLogisticalInformation']['orderLogisticalDateGroup']['requestedDeliveryDateAtUltimateConsignee']['date'];
    //change by: onyx
    //dated: 2013-01-08
    //expected delivery date issues with ullmans
    $postingOrdersHoldingTO->requestedDeliveryDate = $postingOrdersHoldingTO->orderDate;
    /*-------------------------*/
    $postingOrdersHoldingTO->capturedBy = CB_PNP_WS;
	  $postingOrdersHoldingTO->deliveryInstructions;
	  $postingOrdersHoldingTO->documentType = $this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderIdentification']['contentOwner']['additionalPartyIdentification']['additionalPartyIdentificationType'];
	  $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
	  $postingOrdersHoldingTO->vendorReference = $this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderPartyInformation']['seller']['additionalPartyIdentification'][0]['additionalPartyIdentificationValue'];
	  $orderLineItem = $this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderLineItem'];

	  	if (!$this->setPrincipal()) {
	    	$this->errorDescription = 'Seller GLN invalid or not Supplied';
			$this->errorParty = 'SOAP: Client';
			$this->errorStatus = 1;
			return false;
	    };

	  	//build shipTo name.
	 	  	$shipToNameArr = array();
	 	  	foreach($this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderLogisticalInformation']['shipToLogistics']['shipTo']['additionalPartyIdentification'] as $shipToNameSet){
	 	   		$shipToNameArr[] = $shipToNameSet['additionalPartyIdentificationValue'];
	 	  	}
	 	  	$postingOrdersHoldingTO->shipToName = join('|',$shipToNameArr);

		$postingOrdersHoldingTO->shipToGLN = $this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderLogisticalInformation']['shipToLogistics']['shipTo']['gln'];
		$postingOrdersHoldingTO->buyerGLN = $this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderPartyInformation']['buyer']['gln'];
		$postingOrdersHoldingTO->deliverName = $this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderLogisticalInformation']['shipToLogistics']['shipTo']['additionalPartyIdentification'][1]['additionalPartyIdentificationValue'];
		//Build OrderLines Array
	  	foreach($orderLineItem as $orderLine){

			$postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

			// PnP only sends through the list price and the total price. All figures in between need to be derived.
			// PnP - Discount Value is always Zero. Therefore Net Price always equals List Price.
			$postingOrdersHoldingDetailTO->quantity = $orderLine['requestedQuantity']['value'];

			$postingOrdersHoldingDetailTO->listPrice = $orderLine['netPrice']['amount']['monetaryAmount'];
			$postingOrdersHoldingDetailTO->totalPrice =$orderLine['netAmount']['amount']['monetaryAmount'];
			$postingOrdersHoldingDetailTO->discountValue = 0;
			$postingOrdersHoldingDetailTO->nettPrice = $postingOrdersHoldingDetailTO->listPrice - $postingOrdersHoldingDetailTO->discountValue; // $postingOrdersHoldingDetailTO->nettPrice = round(($orderLine['netAmount']['amount']['monetaryAmount']/(VAL_VAT_RATE_ADD*$orderLine['requestedQuantity']['value'])),2);
			$postingOrdersHoldingDetailTO->extPrice = round($postingOrdersHoldingDetailTO->nettPrice * $postingOrdersHoldingDetailTO->quantity,2);// round(($orderLine['netAmount']['amount']['monetaryAmount']/VAL_VAT_RATE_ADD),2);
			$postingOrdersHoldingDetailTO->vatRate = ((abs($postingOrdersHoldingDetailTO->extPrice-$postingOrdersHoldingDetailTO->totalPrice)<=($postingOrdersHoldingDetailTO->extPrice*VAL_PRICE_VARIATION_ALLOWED))?0:VAL_VAT_RATE_TBLSTD); // diff must be less that 1%
			$postingOrdersHoldingDetailTO->vatAmount = round($postingOrdersHoldingDetailTO->extPrice*($postingOrdersHoldingDetailTO->vatRate/100),2); // round((($orderLine['netAmount']['amount']['monetaryAmount']/VAL_VAT_RATE_ADD)*VAL_VAT_RATE),2);

			$postingOrdersHoldingDetailTO->productGTIN = $orderLine['tradeItemIdentification']['gtin'];
			$postingOrdersHoldingDetailTO->productCode = '';
			$postingOrdersHoldingDetailTO->principalProductUid = "";
			$postingOrdersHoldingDetailTO->pallets = 0;
			$postingOrdersHoldingDetailTO->clientLineNo = $orderLine["!number"];  //keys = no of lines, no page number
			//build product name
			$productNameArr = array();
			foreach($orderLine['tradeItemIdentification']['additionalTradeItemIdentification'] as $productNameSet){
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
  	if(empty($this->orderArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['order']['orderPartyInformation']['seller']['gln'])){
  		return false;
  	}

  	//ReceiverEAN : Response SenderEAN (PNP)
 		if(empty($this->orderArr['result']['message']['transaction']['entityIdentification']['contentOwner']['gln'])){
  		return false;
  	}

  	//order number, invoice number etc
  	if(!isset($this->orderArr['result']['message']['entityIdentification']['uniqueCreatorIdentification'])){
  		return false;
  	}
  	$entityCreatorId = $this->orderArr['result']['message']['entityIdentification']['uniqueCreatorIdentification'];

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
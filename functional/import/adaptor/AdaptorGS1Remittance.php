<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentRemittanceTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentRemittanceDetailTO.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');


class AdaptorGS1Remittance {

	private $dbConn;
	private $remittanceArr;
	public  $principalUId = '';
	public  $principalGLN;
	public  $remittanceNo = 0;  //currently only used to pass to the logger.
	// required fields across all adaptors
	public  $userName = '';
	public  $passWord = '';
	public  $vendorUId = 0;
	public  $vendorGln;
	public  $errorStatus = false; //false : NO ERRORS
	public  $errorParty;
	public  $errorDescription;
	public  $postingRemittanceTO;

	public function __construct($dbConn, $remittanceArr) {
		$this->dbConn = $dbConn;

		// the isset() function will raise a fatal error if the remittanceArr is not an array, but is an object already as opposed to just unset.
		if (!is_array($remittanceArr)) {
			$this->errorDescription = 'Incorrect Request XML supplied.';
			$this->errorParty = 'SOAP: Client';
			$this->errorStatus = 1;
			return false;
		} else $this->remittanceArr = $remittanceArr;
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
		if(!$this->buildRemittance()){
			$this->errorDescription .= ' Failed To Submit Order.';
			$this->errorParty = 'SOAP: Server';
			$this->errorStatus = 5;
			return false;
		}

		return true;
   }

	private function setCredentials(){
		if(!isset($this->remittanceArr['result']['StandardBusinessDocumentHeader']['Sender']['Identifier']['!'])){
			return false;
		}
		//$this->vendorGln = $this->remittanceArr['result']['message']['transaction']['entityIdentification']['contentOwner']['gln'];
		// if a tag has an attribute specified, then the tag value gets put into [!
		$this->vendorGln = $this->remittanceArr['result']['StandardBusinessDocumentHeader']['Sender']['Identifier']['!'];

		return true;

	}

  private function setPrincipal(){
	  	if(empty($this->remittanceArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['settlement']['payee']['gln'])){
	  		return false;
	  	}

	  	$this->principalGLN = $this->remittanceArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['settlement']['payee']['gln'];
	  	$this->principalUId = '';

		return true;
  }

  private function validateGS1(){
    //Order Type ie: ROSTER | ALLOCATION | FREE_STOCK | STANDARD
    if(empty($this->remittanceArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['settlement'])) {
      return false;
    }

		//ENTERING... Check Fields
		$remittanceFields = &$this->remittanceArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['settlement'];

		//Order Creation Date
		if(empty($remittanceFields['!creationDateTime'])){
			return false;
		}

		return true;

  }

	private function buildRemittance(){

		  //Build TO for DAO.
		unset($this->postingRemittanceTO);
	  $postingRemittanceTO = new PostingDocumentRemittanceTO();

		// if a tag has an attribute specified, then the tag value gets put into [!]
		// The literal GLN is the original "rt" GLn lent to us by PnP before we had ours.
		/* Remittances send the principal gln in the header, different from orders */
		if(!in_array($this->remittanceArr['result']['StandardBusinessDocumentHeader']['Receiver']['Identifier']['!'],array(RT_GLN,"6001007802929"))){
			$this->errorDescription = 'Content Receiver not for RT GLN';
			$this->errorParty = 'SOAP: Client';
			$this->errorStatus = 1;
			return false;
		}


		$remittanceFields = $this->remittanceArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['settlement'];

    $postingRemittanceTO->principalGLN = $this->principalGLN;
  	$postingRemittanceTO->principalUId = $this->principalUId;
  	$postingRemittanceTO->vendorUId = $this->vendorUId;
  	$postingRemittanceTO->dataSource = DS_WS;
  	$postingRemittanceTO->wsUniqueCreatorId = $this->remittanceArr['result']['message']['entityIdentification']['uniqueCreatorIdentification'];
  	$postingRemittanceTO->documentNo="";
  	$postingRemittanceTO->reference = "";
    $postingRemittanceTO->captureDate = substr($this->remittanceArr['result']['StandardBusinessDocumentHeader']['DocumentIdentification']['CreationDateAndTime'],0,10);
		$postingRemittanceTO->paymentEffectiveDate = substr($remittanceFields['paymentEffectiveDate'],0,4)."-".substr($remittanceFields['paymentEffectiveDate'],4,2)."-".substr($remittanceFields['paymentEffectiveDate'],6,2);
		$postingRemittanceTO->totalAmount = $remittanceFields['totalAmount'];
    $postingRemittanceTO->capturedBy = CB_PNP_WS;
	  $postingRemittanceTO->documentType = $remittanceFields["transactionHandlingType"]; // REMITTANCE_ONLY
	  $postingRemittanceTO->documentTypeUId = DT_REMITTANCE;
	  $postingRemittanceTO->vendorReference = $remittanceFields['payee']['additionalPartyIdentification']['additionalPartyIdentificationValue'];
	  $postingRemittanceTO->buyerGLN = $remittanceFields["payer"]["gln"];

	  if (!$this->setPrincipal()) {
	    $this->errorDescription = 'Seller GLN invalid or not Supplied';
	    $this->errorParty = 'SOAP: Client';
	    $this->errorStatus = 1;
	    return false;
	  };

	  $invoiceRef = $documentType = [];

	  //Build OrderLines Array #1
	  $remittanceLineItem = $this->remittanceArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['settlement']['settlementLineItem'];
	  // if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
	  if (!isset($remittanceLineItem[0])) {
	    $temp=$remittanceLineItem;
	    unset($remittanceLineItem);
	    $remittanceLineItem[0]=$temp;
	  }

  	foreach($remittanceLineItem as $remittanceLine){

  	  $postingRemittanceDetailTO = new PostingDocumentRemittanceDetailTO();

  	  $postingRemittanceDetailTO->principalUId = $postingRemittanceTO->principalUId;

  	  $postingRemittanceDetailTO->type = "header";
  	  $postingRemittanceDetailTO->lineNo = $remittanceLine['!number'];
  	  $postingRemittanceDetailTO->amount = $remittanceLine['amountPaid'];
  	  $postingRemittanceDetailTO->originalAmount = $remittanceLine['originalAmount'];
  	  $postingRemittanceDetailTO->invoiceCreationDate = $remittanceLine['invoice']['!creationDateTime'];
  	  $postingRemittanceDetailTO->invoiceReference = $remittanceLine['invoice']['uniqueCreatorIdentification'];
  	  $postingRemittanceDetailTO->documentType = $remittanceLine['invoice']['invoiceType'];

  	  $invoiceRef[$postingRemittanceDetailTO->lineNo] = $postingRemittanceDetailTO->invoiceReference;
  	  $documentType[$postingRemittanceDetailTO->lineNo] = $postingRemittanceDetailTO->documentType;

  	  $postingRemittanceTO->detailArr[] = $postingRemittanceDetailTO;

  	  if (isset($remittanceLine['adjustmentAndDiscount'])) {
  			$adjustmentAndDiscountItems = $remittanceLine['adjustmentAndDiscount'];
  			// if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
  			if (!isset($adjustmentAndDiscountItems[0])) {
  			  $temp=$adjustmentAndDiscountItems;
  			  unset($adjustmentAndDiscountItems);
  			  $adjustmentAndDiscountItems[0]=$temp;
  			}
  			foreach ($adjustmentAndDiscountItems as $adjItems) {
  			  $postingRemittanceDetailTO = new PostingDocumentRemittanceDetailTO();

  			  $postingRemittanceDetailTO->principalUId = $postingRemittanceTO->principalUId;

  			  $postingRemittanceDetailTO->type = "adjustment-and-detail";
  			  $postingRemittanceDetailTO->lineNo = $remittanceLine['!number'];
    			$postingRemittanceDetailTO->adjustmentReason = $adjItems['adjustmentReason']['messageReason'];
    			$postingRemittanceDetailTO->adjustmentReference = $adjItems['alternateAdjustmentReference']['identification'];
    			$postingRemittanceDetailTO->adjustmentAmount = $adjItems['amount'];
    			// carry these 2 lines through all others
    			$postingRemittanceDetailTO->invoiceReference = $remittanceLine['invoice']['uniqueCreatorIdentification'];
    			$postingRemittanceDetailTO->documentType = $remittanceLine['invoice']['invoiceType'];

    			$postingRemittanceTO->detailArr[] = $postingRemittanceDetailTO;
  			}
  	  }

  	}

  	//Build OrderLines Array #2
  	$remittanceLineItem = $this->remittanceArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['settlement']['extension']['settlementLineItem'];
  	// if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
  	if (!isset($remittanceLineItem[0])) {
  	  $temp=$remittanceLineItem;
  	  unset($remittanceLineItem);
  	  $remittanceLineItem[0]=$temp;
  	}

  	foreach($remittanceLineItem as $remittanceLine){

  	  $postingRemittanceDetailTO = new PostingDocumentRemittanceDetailTO();

  	  $postingRemittanceDetailTO->principalUId = $postingRemittanceTO->principalUId;

  	  $postingRemittanceDetailTO->type = "ext-settlement";
  	  $postingRemittanceDetailTO->lineNo = $remittanceLine['!number'];
  	  $postingRemittanceDetailTO->adjustmentReason = $remittanceLine['documentType'];
  	  $postingRemittanceDetailTO->adjustmentReference = $remittanceLine['documentNumber'];
  	  // use invoice ref from prev processed loops. The extension tag is processed outside of the settlementLineItem tag so cant reference it directly.
  	  $postingRemittanceDetailTO->invoiceReference = ((isset($invoiceRef[$postingRemittanceDetailTO->lineNo]))?$invoiceRef[$postingRemittanceDetailTO->lineNo]:"");
  	  $postingRemittanceDetailTO->documentType = ((isset($documentType[$postingRemittanceDetailTO->lineNo]))?$documentType[$postingRemittanceDetailTO->lineNo]:"");

  	  $postingRemittanceTO->detailArr[] = $postingRemittanceDetailTO;

  	}

  	//Build OrderLines Array #3
  	$remittanceLineItem = $this->remittanceArr['result']['message']['transaction']['command']['documentCommand']['documentCommandOperand']['settlement']['extension']['adjustmentAndDiscountSummary']['adjustmentAndDiscount'];
  	// if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
  	if (!isset($remittanceLineItem[0])) {
  	  $temp=$remittanceLineItem;
  	  unset($remittanceLineItem);
  	  $remittanceLineItem[0]=$temp;
  	}

  	foreach($remittanceLineItem as $remittanceLine){

  	  $postingRemittanceDetailTO = new PostingDocumentRemittanceDetailTO();

  	  $postingRemittanceDetailTO->principalUId = $postingRemittanceTO->principalUId;

  	  $postingRemittanceDetailTO->type = "ext-adjustment-and-discount";
  	  $postingRemittanceDetailTO->lineNo = "";
  	  $postingRemittanceDetailTO->amount = ( (isset($remittanceLine['amount'])) ? $remittanceLine['amount'] : "" );
  	  $postingRemittanceDetailTO->adjustmentReason = ( (isset($remittanceLine['adjustmentReason']) && isset($remittanceLine['adjustmentReason']['messageReason'])) ? $remittanceLine['adjustmentReason']['messageReason'] : "" );

  	  $postingRemittanceTO->detailArr[] = $postingRemittanceDetailTO;

  	}

		$this->postingRemittanceTO = $postingRemittanceTO;


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

  	//order number, invoice number etc
  	if(!isset($this->remittanceArr['result']['message']['entityIdentification']['uniqueCreatorIdentification'])){
  		return false;
  	}
  	$entityCreatorId = $this->remittanceArr['result']['message']['entityIdentification']['uniqueCreatorIdentification'];

  	//InstanceIdentifier
  	if(!isset($this->remittanceArr['result']['StandardBusinessDocumentHeader']['DocumentIdentification']['InstanceIdentifier'])){
  		return false;
  	}
  	$instanceId = $this->remittanceArr['result']['StandardBusinessDocumentHeader']['DocumentIdentification']['InstanceIdentifier'];

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
<?php
/* This is an ADAPTOR. As little as possible processing and lookups should happen in here. Leave that to the processing script.
 * Adaptors should be as lightweight as possible
 *
 * STORE CREDIT LIMITS IMPORT
 *
 * Updates the credit limit fields on principal_store_master by using store special fields
 * File Structure : XML
 * Sample :
 * see xsd
  */

/* NB
 * This Adaptor Handles only ASN's and STOCK TRANSFERS !
 * 2) SGX consolidated Depot "summary orders"
 *
 */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostMiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/ValidationCommonUtils.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingStoreTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingSpecialFieldTO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingDetailTO.php');

class AdaptorTOHT {
	private $dbConn;
	private $storeDAO;
	private $postMiscDAO;
  private $importDAO;
  private $postProductDAO;

	function __construct($dbConn) {
		global $storeDAO, $postMiscDAO, $importDAO, $postProductDAO;
		$this->dbConn = $dbConn;
		// re-use above globals what we can from calling program to improve speed
	    $this->postMiscDAO = $postMiscDAO;
	    $this->storeDAO = $storeDAO;
      $this->importDAO = $importDAO;
      $this->postProductDAO = $postProductDAO;

      if (!isset($_SESSION)) session_start();
      $_SESSION['user_id'] = SESSION_ADMIN_USERID;
      $_SESSION['principal_id'] = "";
    }


    // V1 is vendor 1 - SGX not version 1
    // At the moment this only caters for candy tops, but essentially this type of file has all orders in it (1 file for 1 principal) and
    // these orders need to be consolidated and put through as SGX under one product
    function adaptorTOHT_V1($content, $onlineFileProcessItem) {
      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;

    // I prefer to process it using SimpleXML into an Array instead of DOM
    $fileArray = FileParser::xmlToArray($content);

    $principalSGX = "166";

/*
Array looks like this after parsing :

Array
(
    [vendor] => Array
        (
            [vendor_no] => 6001007165000
            [vendor_name] => Continental Biscuits
            [vendor_addr1] => P.O Box 15199
            [vendor_addr2] => Westmead
            [vendor_addr3] =>
            [vendor_addr4] => South Africa
            [vendor_phone] => 031-713 1300
            [vendor_fax] =>
            [vendor_company_reg_no] => 1990/011494/07
            [vendor_vat_no] => 4400128452
        )

    [interface_process_date] => 2013-03-06
    [invoice] => Array
        (
            [0] => Array
                (
                    [invoice_hdr] => Array
                        (
                            [buyer] => 156750
                            [buyer_name] => Shoprite Checkers
                            [buyer_addr1] => PO Box 215
                            [buyer_addr2] => Brackenfell
                            [buyer_addr3] => Cape Town
                            [buyer_postcode] => 7561
                            [invoice_no] => CB76603
                            [buyer_accno] => 156750
                            [sales_order_no] => CB76603
                            [customer_pono] => 4344039040
                            [invoice_date] => 2013-02-18
                            [order_date] => 2013-02-15
                            [required_date] => 2013-02-20
                            [invoice_site] => acojb3
                            [territory] => rfs
                            [branch] => Array
                                (
                                    [branch_no] => cht6967
                                    [branch_name] => Shoprite Sasolburg
                                    [branch_addr1] => MJ Van Der Merwe Street &
                                    [branch_addr2] => Fichardt St
                                    [branch_addr3] => Sasolburg
                                    [branch_postcode] => 1947
                                )

                            [bracket_disc] => 00.0%
                            [comment1] =>
                            [comment2] =>
                        )

                    [invoice_det] => Array
                        (
                            [product] => Array
                                (
                                    [0] => Array
                                        (
                                            [line_no] => 1
                                            [vendor_product_code] => 024302
                                            [supplier_product_code] => 024302
                                            [product_description] => Chelsea Cream Chocolate  24x100g
                                            [pack_size] => Array
                                                (
                                                    [unit_pack] => 24x100g
                                                    [unit_size] => 24x100g
                                                )

                                            [case_pack] => 1
                                            [invoice_quantity] => 2
                                            [customer_quantity] => 48
                                            [unit_price_excl_vat] => 60.00
                                            [deal_discounts] => Array
                                                (
                                                    [deal_discount_1] => R    9.69
                                                    [deal_value_1] => -9.69
                                                    [deal_discount_2] => R    9.69
                                                    [deal_value_2] => -9.69
                                                    ...
                                                )

                                            [unit_value_excl_vat] => 50.31
                                            [extended_value_excl_vat] => 100.62
                                            [extended_vat] => 14.09
                                            [extended_value_incl_vat] => 114.71
                                        )

                                        ...
*/

    // do basic validation in place of XSD, if tag is not repeated, it wont create an array
      if ((!isset($fileArray["vendor"]["vendor_no"])) ||
        ((!isset($fileArray["invoice"]["0"]["invoice_hdr"])) && (!isset($fileArray["invoice"]["invoice_hdr"]))) ||
        ((!isset($fileArray["invoice"]["0"]["invoice_hdr"]["branch"]["branch_no"])) && (!isset($fileArray["invoice"]["invoice_hdr"]["branch"]["branch_no"]))) ||
        (
          (!isset($fileArray["invoice"]["0"]["invoice_det"]["product"]["0"]["invoice_quantity"])) &&
          (!isset($fileArray["invoice"]["0"]["invoice_det"]["product"]["invoice_quantity"])) &&
          (!isset($fileArray["invoice"]["invoice_det"]["product"]["0"]["invoice_quantity"])) &&
          (!isset($fileArray["invoice"]["invoice_det"]["product"]["invoice_quantity"]))
        )) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "File Structural problem in adaptorTOHT_V1 for file ".basename($onlineFileProcessItem["file_being_processed"])." principal SGX";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }

      // get the uid for the consolidated product to use
      $cpTO = $this->getConsolidatedProduct($principalSGX);
      if ($cpTO->type != FLAG_ERRORTO_SUCCESS) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Could not create consolidated product in adaptorTOHT_V1 in file ".basename($onlineFileProcessItem["file_being_processed"]);
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }

      // if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
      if (!isset($fileArray["invoice"][0])) {
        $temp=$fileArray["invoice"];
        unset($fileArray["invoice"]);
        $fileArray["invoice"][0]=$temp;
      }

      // put into common TO
      $arrTO=array();

      foreach ($fileArray["invoice"] as $o) {

        $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
        $postingOrdersHoldingTO->updateProduct="N";
        $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
        $postingOrdersHoldingTO->principalUid=$principalSGX;
        $postingOrdersHoldingTO->wsUniqueCreatorId="";
        $postingOrdersHoldingTO->orderDate = mysql_real_escape_string($o["invoice_hdr"]["invoice_date"]); // not order_date
        $postingOrdersHoldingTO->clientDocumentNo=mysql_real_escape_string(trim($o["invoice_hdr"]["invoice_no"]));
        if ($o["invoice_hdr"]["invoice_no"]!=$o["invoice_hdr"]["sales_order_no"]) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Sales Order No differs from invoice_no in file ".basename($onlineFileProcessItem["file_being_processed"]);
          $eTO->identifier = ET_SYSTEM;
          return $eTO;
        }

        // this is to keep uniqueness across principals
        $prefix = strtoupper(preg_replace("/[^A-Z]/i","",$postingOrdersHoldingTO->clientDocumentNo));
        $docPrefixPrincipalUId = "";
        $docPrefix = "";
        // NB !!
        // because we dont pad the prefix but do pad the doc num part that means we can overlap and create duplicates
        //   - so if we have code 19 then we cannot have code 190 , or if we have 21 we cannot have 210 as :-
        // 19+"001234" = 19001234
        // is the same as 190+"001234" = 19001234
        $prefixMap = array(
                          'IR'=>array('principal_code'=>221,'name'=>'Imana'),
                          'PQ'=>array('principal_code'=>222,'name'=>'Promeal'),
                          'P'=>array('principal_code'=>19,'name'=>'Promasidor'), // formerly code 223
                          'WF'=>array('principal_code'=>224,'name'=>'Floyds'),
                          'TW'=>array('principal_code'=>224,'name'=>'Trumps'),
                          'KN'=>array('principal_code'=>226,'name'=>'Trade Kings'),
                          'BE'=>array('principal_code'=>226,'name'=>'The Blenders'),
                          'CK'=>array('principal_code'=>228,'name'=>'Cotton Kings'),
                          'SN'=>array('principal_code'=>229,'name'=>'Sunkist'),
                          'KL'=>array('principal_code'=>230,'name'=>'Kelpack'),
                          'AD'=>array('principal_code'=>231,'name'=>'Africa Dynamics'),
                          'BB'=>array('principal_code'=>232,'name'=>'BBH Agencies'),
                          'BS'=>array('principal_code'=>233,'name'=>'Big Six'),
                          'BP'=>array('principal_code'=>234,'name'=>'Boland Pulp'),
                          'CG'=>array('principal_code'=>235,'name'=>'Character Group'),
                          'CB'=>array('principal_code'=>236,'name'=>'Continental'),
                          'CH'=>array('principal_code'=>237,'name'=>'Coti'),
                          'AC'=>array('principal_code'=>238,'name'=>'Contraship'),
                          'CR'=>array('principal_code'=>239,'name'=>'Cintron'),
                          'GD'=>array('principal_code'=>240,'name'=>'Elgin Dew'),
                          'VC'=>array('principal_code'=>241,'name'=>'EV Cosmetics'),
                          'FL'=>array('principal_code'=>242,'name'=>'FG La Pasta'),
                          'FP'=>array('principal_code'=>243,'name'=>'Fantastic Plastics'),
                          'GC'=>array('principal_code'=>244,'name'=>'Global Coffee'),
                          'GE'=>array('principal_code'=>245,'name'=>'Gehringer'),
                          'GB'=>array('principal_code'=>246,'name'=>'Good Hope'),
                          'GM'=>array('principal_code'=>247,'name'=>'GMT'),
                          'AS'=>array('principal_code'=>248,'name'=>'Heartlands'),
                          'HX'=>array('principal_code'=>249,'name'=>'Hexagon'),
                          'BR'=>array('principal_code'=>250,'name'=>'Illovo'),
                          'NA'=>array('principal_code'=>251,'name'=>'Nbandi Beverages'),
                          'PF'=>array('principal_code'=>252,'name'=>'Princeware Plastics'),
                          'SO'=>array('principal_code'=>253,'name'=>'Southern Oil'),
                          'BY'=>array('principal_code'=>254,'name'=>'Tasselberry'),
                          'ST'=>array('principal_code'=>255,'name'=>'Tshivase Tea'),
                          'UP'=>array('principal_code'=>256,'name'=>'UPP'),
                          );

        if (!isset($prefixMap[$prefix])) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Unknown document prefix ({$prefix}) in file ".basename($onlineFileProcessItem["file_being_processed"]);
          $eTO->identifier = ET_SYSTEM;
          return $eTO;
        }
        $docPrefix = $prefixMap[$prefix]["principal_code"];
        $docStripped = preg_replace("/[^0-9]/","",$postingOrdersHoldingTO->clientDocumentNo);
        $padLen = 8 - strlen($docPrefix);
        if (strlen($docStripped)+strlen($docPrefix)>8) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Document number ({$docPrefix} + {$docStripped}) longer than allowed 8 digits in file ".basename($onlineFileProcessItem["file_being_processed"]);
          $eTO->identifier = ET_SYSTEM;
          return $eTO;
        }
        $postingOrdersHoldingTO->documentNo=$docPrefix.str_pad($docStripped,$padLen,"0",STR_PAD_LEFT);

        $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
        $postingOrdersHoldingTO->dataSource = DS_EDI;
        $postingOrdersHoldingTO->capturedBy = 'SGX-LP';
        $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
        $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
        $postingOrdersHoldingTO->chainLookupRef=""; // processing will assign generic chain to it.
        $postingOrdersHoldingTO->storeLookupRef=""; // ???
        $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
        $postingOrdersHoldingTO->principalStoreUId=""; // let the processing script lookup the store
        $postingOrdersHoldingTO->reference=$o["invoice_hdr"]["customer_pono"];
        $postingOrdersHoldingTO->deliverName = $o["invoice_hdr"]["branch"]["branch_name"];
        $postingOrdersHoldingTO->oldAccount=mysql_real_escape_string($o["invoice_hdr"]["branch"]["branch_no"]);
        if (trim($postingOrdersHoldingTO->oldAccount)=="") {
          // do not allow to continue - reject the file and ITD will be notified in confirmation
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = htmlspecialchars("A mandatory field (<invoice_hdr><invoice_hdr><branch><branch_no) is empty in file ".basename($onlineFileProcessItem["file_being_processed"]));
          $eTO->identifier = ET_SYSTEM;
          return $eTO;
        }

        $postingOrdersHoldingTO->depotUId = 2; // Ullmanns Johannesburg
        // The next two statements are to protect against forgetting to change this adaptor incase it is no longer just for JHB depot
        $postingOrdersHoldingTO->enforceSameDepot="Y";
        $postingOrdersHoldingTO->updateStoreDepot="N";

        $postingOrdersHoldingTO->skipInvoiceComputationCheck="Y";

        // prepare the store
        // supply the store details in case the processor cant find the store
        $postingStoreTO = new PostingStoreTO;
        $postingStoreTO->DMLType = "INSERT";
        $postingStoreTO->principalStoreUId ="";
        $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
        $postingStoreTO->deliverName = $postingOrdersHoldingTO->deliverName;
        $postingStoreTO->deliverAdd1 = mysql_real_escape_string(trim($o["invoice_hdr"]["branch"]["branch_addr1"]));
        $postingStoreTO->deliverAdd2 = mysql_real_escape_string(trim($o["invoice_hdr"]["branch"]["branch_addr2"]));
        $postingStoreTO->deliverAdd3 = mysql_real_escape_string(trim($o["invoice_hdr"]["branch"]["branch_addr3"]).", ".trim($o["invoice_hdr"]["branch"]["branch_postcode"]));
        $postingStoreTO->billName = $postingStoreTO->deliverName;
        $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
        $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
        $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
        $postingStoreTO->vatNumber = "";
        $postingStoreTO->depot = $postingOrdersHoldingTO->depotUId; // this will be set by the processing script
        $postingStoreTO->deliveryDay = "8";
        $postingStoreTO->noVAT=((floatval($o["invoice_vat_perc"])==floatval(VAL_VAT_RATE_TBLSTD))?0:1); // ITD do send thru garbage here so not reliable. We just pass on values received and dont use the store
        $postingStoreTO->onHold = "0";
        $postingStoreTO->chain = ""; // this will be set by the processing script
        $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
        $postingStoreTO->status=FLAG_STATUS_ACTIVE;
        $postingStoreTO->vendorCreatedByUId=$postingOrdersHoldingTO->vendorUid;
        $postingStoreTO->ownedBy=$postingOrdersHoldingTO->vendorUid;
        $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;
        $postingStoreTO->updatePrincipalStore = 'Y';

        $postingOrdersHoldingTO->postingStoreTO=$postingStoreTO;

        // prepare the single detail line
        $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
        $postingOrdersHoldingDetailTO->productCode = VAL_PRODUCTCODE_CONSOLIDATED;
        $postingOrdersHoldingDetailTO->principalProductUid = $cpTO->object["product_uid"];
        $postingOrdersHoldingDetailTO->productName = "Consolidated Product";
        $postingOrdersHoldingDetailTO->clientLineNo="1";
        $postingOrdersHoldingDetailTO->pallets = 0;

        // SUM the detail

        // if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
        if (!isset($o["invoice_det"]["product"][0])) {
          $temp=$o["invoice_det"]["product"];
          unset($o["invoice_det"]["product"]);
          $o["invoice_det"]["product"][0]=$temp;
        }

        foreach ($o["invoice_det"]["product"] as $ol) {
          if (!isset($ol["unit_price_excl_vat"])) {
            echo "<pre>";
            print_r($o);
          }


          $postingOrdersHoldingDetailTO->listPrice += mysql_real_escape_string($ol["unit_price_excl_vat"]);

          // discounts are a little tricky, as if it is empty, the parser makes it an empty array because the innermost tags are missing
          $dCnt=1;
          while (isset($ol["deal_discounts"]["deal_value_{$dCnt}"])) {
            $postingOrdersHoldingDetailTO->discountValue += $ol["deal_discounts"]["deal_value_{$dCnt}"]*-1; // reverse it so it is compatible with our system ie. discount_value assumed to be +ive which reduces list price
            $dCnt++;
          }

          $postingOrdersHoldingDetailTO->nettPrice += $ol["unit_value_excl_vat"];

        }

        $postingOrdersHoldingDetailTO->quantity = round(mysql_real_escape_string($o["invoice_total_qty"]),0); // not invoice_customer_quantity
        $postingOrdersHoldingDetailTO->extPrice = $o["invoice_total_excl_vat"];
        $postingOrdersHoldingDetailTO->vatAmount += $o["invoice_vat"];
        $postingOrdersHoldingDetailTO->totalPrice +=$o["invoice_total_incl_vat"];
        $postingOrdersHoldingDetailTO->vatRate = $o["invoice_vat_perc"];
        $postingOrdersHoldingDetailTO->mass = $o["invoice_mass"];
        if (isset($o["invoice_volume"])) $postingOrdersHoldingDetailTO->volume = $o["invoice_volume"]; // just until testing is over, ITD havent added tag yet
        $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

        $arrTO[]=$postingOrdersHoldingTO;

      }

      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;

      return $eTO;

    }


    // V10 is vendor 10 - ITD not version 10
    // At the moment this only caters for candy tops
    function adaptorTOHT_V10($content, $onlineFileProcessItem) {
    	// NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
    	global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

    	$eTO = new ErrorTO;

		/* xsd validation is not done for ITD files... only because of lack of time to write the xsd.

    	$eTO = new ErrorTO;
    	$xml = new DOMDocument();

    	// Test the XML File
		libxml_use_internal_errors(true); // needed to parse errors below
		$xml->loadXML($content);
		if (!$xml->schemaValidateSource($xsd)) {
			$errorStr=FileParser::getDOMErrors();
			$eTO->type = FLAG_ERRORTO_ERROR;
    		$eTO->description = "Invalid XML format of file :<br>\n".$errorStr;
    		$eTO->identifier = ET_CUSTOMER;
    		return $eTO;
		}
		*/

		// I prefer to process it using SimpleXML into an Array instead of DOM
		$fileArray = FileParser::xmlToArray($content);

/*
Array looks like this after parsing :

Array
(
    [vendor_no] => 6001914000005
    [creation_date] => 21/06/2012
    [creation_time] => 14:22:24
    [transfer] => Array
        (
            [0] => Array
                (
                    [transfer_hdr] => Array
                        (
                            [transaction_no] =>    1
                            [transaction_key] => TB10678
                            [transfer_no] => TB10678
                            [transfer_date] => 21/06/2012
                            [transfer_from_site] => actel2
                            [transfer_to_site] => actbl1
                        )

                    [transfer_det] => Array
                        (
                            [product] => Array
                                (
                                    [0] => Array
                                        (
                                            [ean_no] => 600NO-BARCODE
                                            [vendor_product_code] => 840070
                                            [supplier_product_code] => 840070
                                            [product_description] => Cowboy Sherbet Retail    24x9
                                            [quantity] => 10
                                        )

                                    [1] => Array
                                        (
                                            [ean_no] => 600NO-BARCODE
                                            [vendor_product_code] => 840070
                                            [supplier_product_code] => 840070
                                            [product_description] => Cowboy Sherbet Retail    24x9
                                            [quantity] => 10
                                        )

                                )

                        )

                )
			...

        )
)
*/

		// do basic validation in place of XSD, if tag is not repeated, it wont create an array
    	if ((!isset($fileArray["vendor_no"])) ||
    		((!isset($fileArray["transfer"]["0"]["transfer_hdr"])) && (!isset($fileArray["transfer"]["transfer_hdr"]))) ||
    		(
    			(!isset($fileArray["transfer"]["0"]["transfer_det"]["product"]["0"]["quantity"])) &&
    			(!isset($fileArray["transfer"]["0"]["transfer_det"]["product"]["quantity"])) &&
    			(!isset($fileArray["transfer"]["transfer_det"]["product"]["0"]["quantity"])) &&
    			(!isset($fileArray["transfer"]["transfer_det"]["product"]["quantity"]))
    		) ||
    		($fileArray["vendor_no"]!="6001914000005") // Candy Tops GLN
    		) {
  			$eTO->type = FLAG_ERRORTO_ERROR;
	   		$eTO->description = "File Structural problem in adaptorTOHT_V10 for file ".basename($onlineFileProcessItem["file_being_processed"])." principal {$onlineFileProcessingMapping["principal_uid"]}";
	   		$eTO->identifier = ET_SYSTEM;
	    	return $eTO;
  		}

    	// put into common TO, ignore all the other extra fields
    	$onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalUId($onlineFileProcessItem["onlineFileProcessingMapping"], $onlineFileProcessItem["principal_uid"]);
    	// this adaptor file is only used for one principal, so treat null or exact principal as same, but must be found !
    	if (empty($onlineFileProcessingMapping)) {
  			$eTO->type = FLAG_ERRORTO_ERROR;
	   		$eTO->description = "Could not retrieve online file principal mappings";
	   		$eTO->identifier = ET_SYSTEM;
	    	return $eTO;
  		}

    	$arrTO=array();
    	// if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
    	if (!isset($fileArray["transfer"][0])) {
    		$temp=$fileArray["transfer"];
    		unset($fileArray["transfer"]);
    		$fileArray["transfer"][0]=$temp;
    	}

    	// put into common TO
    	$arrTO=array();

    	foreach ($fileArray["transfer"] as $o) {

    		$postingOrdersHoldingTO = new PostingOrdersHoldingTO();
        $postingOrdersHoldingTO->updateProduct="Y";
    		$postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
    		$postingOrdersHoldingTO->principalUid=$onlineFileProcessingMapping["principal_uid"];
    		$postingOrdersHoldingTO->wsUniqueCreatorId=$o["transfer_hdr"]["transaction_no"];
    		$postingOrdersHoldingTO->orderDate = mysql_real_escape_string(trim(CommonUtils::formatFromDDsMMsYYYY ($o["transfer_hdr"]["transfer_date"],$fromSeparator="/")));
    		if ($postingOrdersHoldingTO->orderDate===false) {
    			$eTO->type = FLAG_ERRORTO_ERROR;
		   		$eTO->description = "Invalid transfer date format <transfer_date> for transaction_no {$postingOrdersHoldingTO->wsUniqueCreatorId}.";
		   		$eTO->identifier = ET_CUSTOMER;
		    	return $eTO;
    		}
    		$postingOrdersHoldingTO->clientDocumentNo=mysql_real_escape_string(trim($o["transfer_hdr"]["transfer_no"]));
    		$postingOrdersHoldingTO->documentNo=preg_replace("/[^0-9]/","",$postingOrdersHoldingTO->clientDocumentNo);
    		$postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
    		$postingOrdersHoldingTO->dataSource = DS_EDI;
    		$postingOrdersHoldingTO->capturedBy = 'ITD';
    		$postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
    		$postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
   			$postingOrdersHoldingTO->chainLookupRef=""; // ???
   			$postingOrdersHoldingTO->storeLookupRef=""; // ???
		  	$postingOrdersHoldingTO->documentTypeUId = DT_ASN;
		  	$postingOrdersHoldingTO->principalStoreUId=""; // let the processing script lookup the store
		  	$postingOrdersHoldingTO->deliverName = ""; // this might change if not ASN !!
		  	$postingOrdersHoldingTO->oldAccount=mysql_real_escape_string(trim($o["transfer_hdr"]["transfer_to_site"]));
		  	if (trim($postingOrdersHoldingTO->oldAccount)=="") {
		  		// do not allow to continue - reject the file and ITD will be notified in confirmation
		  		$eTO->type = FLAG_ERRORTO_ERROR;
		   		$eTO->description = "A mandatory field (<transfer_hdr><transfer_to_site>) is empty for transaction_no {$postingOrdersHoldingTO->wsUniqueCreatorId}.";
		   		$eTO->identifier = ET_CUSTOMER;
		    	return $eTO;
		  	}


		  	// create the detail
		  	// if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
	    	if (!isset($o["transfer_det"]["product"][0])) {
	    		$temp=$o["transfer_det"]["product"];
	    		unset($o["transfer_det"]["product"]);
	    		$o["transfer_det"]["product"][0]=$temp;
	    	}

		  	foreach ($o["transfer_det"]["product"] as $ol) {
		  		$postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

		    	$postingOrdersHoldingDetailTO->quantity = mysql_real_escape_string($ol["quantity"]); // not customer_quantity
    			$postingOrdersHoldingDetailTO->productCode = mysql_real_escape_string(trim($ol["vendor_product_code"]));
				$postingOrdersHoldingDetailTO->productName = mysql_real_escape_string(trim($ol["product_description"]));
				$postingOrdersHoldingDetailTO->productGTIN = mysql_real_escape_string(trim($ol["ean_no"]));

		  		$postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
		  	}

  			$arrTO[]=$postingOrdersHoldingTO;
    	}

   		$eTO->type = FLAG_ERRORTO_SUCCESS;
   		$eTO->description = "Successful";
   		$eTO->object = $arrTO;

    	return $eTO;

    }


    private function getConsolidatedProduct($principalUId) {
      $eTO = new ErrorTO();

      $pfArr = array();
      // attempt to get system consolidated product
      $pfArr = $this->importDAO->getPrincipalProductByCode($principalUId, VAL_PRODUCTCODE_CONSOLIDATED, "");
      if(count($pfArr)==0){
        // create the system consolidated product
        $rTO = $this->postProductDAO->createPrincipalConsolidatedProduct($principalUId);
        if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Product Code: " . VAL_PRODUCTCODE_CONSOLIDATED . " could not be located nor ".VAL_PRODUCTCODE_CONSOLIDATED." created!";
          return $eTO;
        } else {
          $prUId = $rTO->identifier;
        }
      } else {
        $prUId = $pfArr[0]['uid'];
      }

      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = array("product_uid"=>$prUId,
                           "consol_product_code"=>VAL_PRODUCTCODE_CONSOLIDATED,
                           "rs"=>$pfArr);

      return $eTO;
    }

}



?>
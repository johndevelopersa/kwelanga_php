<?php
/*
 *
 * THIS LAYER PROVIDES BASIC SCREENING AND CONVERTS INPUT INTO PROPER TO BEFORE CALL IS MADE TO PROCESS
 *
 */
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/ValidationCommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'functional/export/adaptor/AdaptorDocumentExport.php');  //depot export adaptors.
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');


$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback

if (!isset($_SESSION)) session_start;
// override the principal Id for now.
$principalId = $_SESSION['principal_id'];
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['user_email'];

$returnMessage;

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

$principalSylko = 3;

include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
$postDMLTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DMLTYPE']));
if (isset($_POST['DOCMASTID'])) $postDOCMASTID = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DOCMASTID'])); else $postDOCMASTID="";
if (isset($_POST['STORE'])) $postSTORE = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['STORE'])); else $postSTORE="";
if (isset($_POST['DOCDATE'])) $postDOCDATE = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DOCDATE'])); else $postDOCDATE="";
if (isset($_POST['DELDATE'])) $postDELDATE = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DELDATE'])); else $postDELDATE="";
if (isset($_POST['DOCTYPE'])) $postDOCTYPE = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DOCTYPE'])); else $postDOCTYPE="";
if (isset($_POST['DELINST'])) $postDELINST = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DELINST'])); else $postDELINST="";
if (isset($_POST['CUSTREF'])) $postCUSTREF = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['CUSTREF'])); else $postCUSTREF="";
if (isset($_POST['DN'])) $postDN = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DN'])); else $postDN="";
if (isset($_POST['CLIENTSOURCEDOCUMENT'])) $postCLIENTSOURCEDOCUMENT = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['CLIENTSOURCEDOCUMENT'])); else $postCLIENTSOURCEDOCUMENT="";
if (isset($_POST['PRODUCT'])) $postPRODUCT = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PRODUCT'])); else $postPRODUCT="";
if (isset($_POST['QTY'])) $postQTY = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['QTY'])); else $postQTY="";
if (isset($_POST['COMMENT'])) $postCOMMENT = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['COMMENT'])); else $postCOMMENT=""; // quotations screen only
if (isset($_POST['PALLETS'])) $postPALLETS = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PALLETS'])); else $postPALLETS="";
if (isset($_POST['OVERRIDEPRICE'])) $postOVERRIDEPRICE = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['OVERRIDEPRICE'])); else $postOVERRIDEPRICE="";
if (isset($_POST['CONFIRMOPTION'])) $postCONFIRMOPTION = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['CONFIRMOPTION'])); else $postCONFIRMOPTION="N";
$postSERVICETYPE = (isset($_POST['SERVICETYPE'])) ? (htmlspecialchars($_POST['SERVICETYPE'])) : "";
$postREPCODE = (isset($_POST['REPCODE'])) ? (htmlspecialchars($_POST['REPCODE'])) : "";

// convert to arrays
$arrPRODUCT=explode(",",$postPRODUCT);
$arrQTY=explode(",",$postQTY);
$arrCOMMENT=(($postCOMMENT=="")?array_fill(0, count($arrQTY), ""):explode(",",$postCOMMENT)); // field not present on normal orderCapture.php
$arrPALLETS=(($postPALLETS=="")?array_fill(0, count($arrQTY), ""):explode(",",$postPALLETS));
$arrOVERRIDEPRICE=explode(",",$postOVERRIDEPRICE);

// start of superficial checks. Main checks done in adminPost....php
$returnMessages=new ErrorTO;
if ($postDMLTYPE!="INSERT") {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};
// check document date
if (preg_match(GUI_PHP_DATE_VALIDATION,$postDOCDATE,$parts)) {
	if(!checkdate($parts[2],$parts[3],$parts[1])) {
		$returnMessages->type=FLAG_ERRORTO_ERROR;
		$returnMessages->description="Invalid Document Date format.";
		print(CommonUtils::getJavaScriptMsg($returnMessages));
		return;
	}
} else {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Document Date format.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
  }

// check delivery date if entered
if ($postDELDATE!="") {
	if (preg_match(GUI_PHP_DATE_VALIDATION,$postDELDATE,$parts)) {
		if(!checkdate($parts[2],$parts[3],$parts[1])) {
			$returnMessages->type=FLAG_ERRORTO_ERROR;
			$returnMessages->description="Invalid Delivery Date format.";
			print(CommonUtils::getJavaScriptMsg($returnMessages));
			return;
		}
	} else {
		$returnMessages->type=FLAG_ERRORTO_ERROR;
		$returnMessages->description="Invalid Delivery Date format.";
		print(CommonUtils::getJavaScriptMsg($returnMessages));
		return;
	  }
}

// invalid store format or missing
if(!preg_match(GUI_PHP_INTEGER_REGEX,$postSTORE)) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Store Value or not entered.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};
// invalid document type format or missing
if(!preg_match(GUI_PHP_INTEGER_REGEX,$postDOCTYPE)) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Document Type or not entered.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

$rowCnt=0;
$arrDuplicateProducts=array();
for($i=0; $i<sizeof($arrPRODUCT); $i++) {
	if ($arrPRODUCT[$i]!="") {
		$rowCnt++;
                if($principalId != $principalSylko) {
                  if (($arrQTY[$i]==0) && ($postDOCTYPE!=DT_CANCELLEDNOTE)) {
                      $returnMessages->type=FLAG_ERRORTO_ERROR;
                      $returnMessages->description="Quantity cannot be zero.";
                      $returnMessages->identifier="Q";
                      $returnMessages->identifier2=$i;
                      print(CommonUtils::getJavaScriptMsg($returnMessages));
                      return;
                  }
                }
		if ($arrQTY[$i]<0) {
			$returnMessages->type=FLAG_ERRORTO_ERROR;
			$returnMessages->description="Quantity cannot be negative.";
			$returnMessages->identifier="Q";
			$returnMessages->identifier2=$i;
			print(CommonUtils::getJavaScriptMsg($returnMessages));
			return;
		}
		// Product UID's where decimal values are allowed
		// Allow decimal Quantities
    $productDAO = new productDAO($dbConn);
    $decQtyAllowed=$productDAO->getAllowDecimalFlag($arrPRODUCT[$i]);
		if($decQtyAllowed['allow_decimal'] == 'Y') {
		    if(($arrQTY[$i]!="") && (!preg_match(GUI_PHP_FLOAT_REGEX,$arrQTY[$i]))) {
			     $returnMessages->type=FLAG_ERRORTO_ERROR;
			     $returnMessages->description="Invalid Quantity (".$arrQTY[$i].") value on line ".($i+1).". Only Numbers allowed";
			     $returnMessages->identifier="Q";
			     $returnMessages->identifier2=$i;
			     print(CommonUtils::getJavaScriptMsg($returnMessages));
			     return;
		    }		
		}  elseif(($arrQTY[$i]!="") && (!preg_match(GUI_PHP_INTEGER_REGEX,$arrQTY[$i]))) {
			$returnMessages->type=FLAG_ERRORTO_ERROR;
			$returnMessages->description="Invalid Quantity (".$arrQTY[$i].") value on line ".($i+1).". Only Integers allowed";
			$returnMessages->identifier="Q";
			$returnMessages->identifier2=$i;
			print(CommonUtils::getJavaScriptMsg($returnMessages));
			return;
		};
		// duplicated products
		/*
		if (isset($arrDuplicateProducts[$arrPRODUCT[$i]])) {
			$returnMessages->type=FLAG_ERRORTO_ERROR;
			$returnMessages->description="Product is duplicated on line ".($i+1);
			$returnMessages->identifier="P";
			$returnMessages->identifier2=$i;
			print(CommonUtils::getJavaScriptMsg($returnMessages));
			return;
		}
		*/
		$arrDuplicateProducts[$arrPRODUCT[$i]]=$arrPRODUCT[$i];
	} else if (($arrPRODUCT[$i]=="") && ($arrQTY[$i]!="")) {
		$returnMessages->type=FLAG_ERRORTO_ERROR;
		$returnMessages->description="You have entered a quantity on a item line but have not chosen a product.";
		$returnMessages->identifier="P";
		$returnMessages->identifier2=$i;
		print(CommonUtils::getJavaScriptMsg($returnMessages));
		return;
	  }
}

// row count check
if($rowCnt==0) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="No Products were found!";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
}


/*
 *
 * PROCESSING SECTION
 *
 */

$finalResult = new ErrorTO;

// determine routing - REMEMBER that Confirm Option does not work with docs routed to orders_holding
$principalDAO = new PrincipalDAO($dbConn);
$mfDOA=$principalDAO->getPrincipalDocumentOriginAction($principalId, DOAT_REQUIRES_APPROVAL_REQ);
// get the depot for the store
$storeDAO = new StoreDAO($dbConn);
$mfS = $storeDAO->getPrincipalStoreItem($postSTORE);
if ((empty($mfS)) || ($mfS[0]["depot_uid"]=="")) {
  $returnMessages->type=FLAG_ERRORTO_ERROR;
  $returnMessages->description="Store Depot cannot be blank - needed to determine routing";
  print(CommonUtils::getJavaScriptMsg($returnMessages));
  return;
}
$approvalReq=$principalDAO->resolvePrincipalDocumentOriginAction($mfDOA, $postDOCTYPE, $mfS[0]["depot_uid"], DS_CAPTURE, $userId); // this also handles empty (false)

// proforma pricing for non- Orders
$mfPDocType = $principalDAO->getPrincipalDocumentTypes($principalId); // document types overrides if any
$pDT_ProformaPricing = array();
foreach ($mfPDocType as $r) {
  if (($r["proforma_pricing"]=="Y") && ($r["document_type_uid"]!=DT_ORDINV))
     $pDT_ProformaPricing[]=$r["document_type_uid"];
}


// post directly
if (!$approvalReq) {

  include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
  $postTransactionDAO = new PostTransactionDAO($dbConn);

  include_once($ROOT.$PHPFOLDER.'TO/PostingOrderTO.php');
  include_once($ROOT.$PHPFOLDER.'TO/PostingOrderDetailTO.php');
  // include_once($ROOT.$PHPFOLDER.'TO/PostingOrderDocumentPricingTO.php');

  // not really needed anymore
  $myGUID = CommonUtils::getGUID();

  $postingOrderTO = new PostingOrderTO;
  // the headers for the TO
  $postingOrderTO->DMLType=$postDMLTYPE;
  $postingOrderTO->dMUId=$postDOCMASTID;
  $postingOrderTO->storeChainUId=$postSTORE;
  $postingOrderTO->principalUId=$principalAliasId;
  $postingOrderTO->orderNumber=$postCUSTREF;
  $postingOrderTO->orderSequenceNo=""; // is not assigned as part of posting
  $postingOrderTO->deliveryInstructions=$postDELINST;
  $postingOrderTO->documentDate=$postDOCDATE;
  $postingOrderTO->requestedDeliveryDate=$postDELDATE;
  $postingOrderTO->deliveryDueDate=$postDELDATE;
  $postingOrderTO->batchGUID=$myGUID;
  $postingOrderTO->captureUserUId=$userId;
  $postingOrderTO->deleted=0;
  $postingOrderTO->ediCreated="N";
  $postingOrderTO->ediFileName="";
  $postingOrderTO->documentType=$postDOCTYPE;
  $postingOrderTO->documentServiceTypeUId = $postSERVICETYPE;
  $postingOrderTO->documentRepCodeUid = $postREPCODE;  
  $postingOrderTO->confirmOption = $postCONFIRMOPTION;
  $postingOrderTO->dataSource = DS_CAPTURE;
  if (in_array($postingOrderTO->documentType,array(DT_QUOTATION,DT_PURCHASE_ORDER))) $postingOrderTO->documentNumber=$postDN;
  $postingOrderTO->clientDocumentNumber=$postDN;
  if (in_array($postingOrderTO->documentType,array(DT_MCREDIT_VALUE,DT_MCREDIT_OTHER))) {
  	  $postingOrderTO->sourceDocumentNumber=$postCUSTREF;
  } else {
      $postingOrderTO->sourceDocumentNumber=$postCLIENTSOURCEDOCUMENT;
  }
  for($i=0; $i<sizeof($arrPRODUCT); $i++) {
    if ($arrPRODUCT[$i]!="") {
      $postingOrderDetailTO = new PostingOrderDetailTO;
      $postingOrderDetailTO->productUId=$arrPRODUCT[$i];
      $postingOrderDetailTO->quantity=$arrQTY[$i];
      $postingOrderDetailTO->pallets=$arrPALLETS[$i];
      $postingOrderDetailTO->priceOverrideValue=$arrOVERRIDEPRICE[$i];
      $postingOrderDetailTO->comment=((isset($arrCOMMENT[$i]))?$arrCOMMENT[$i]:"");

      $postingOrderTO->detailArr[]=$postingOrderDetailTO;
    }
  }
  // Do the Actual Posting. Remember the pricing details are passed back.
  $result=$postTransactionDAO->postOrder($postingOrderTO);
  
//  if($userId == 11) {file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/debug.txt', print_r($postingOrderTO, TRUE) , FILE_APPEND);}
  
  // store date and time
  $capDate=gmdate(GUI_PHP_DATE_FORMAT);
  $capTime=gmdate(GUI_PHP_TIME_FORMAT);

  if ($result->type!=FLAG_ERRORTO_SUCCESS) {
    $finalResult->type=$result->type;
    $finalResult->description="The order could not be saved ! <BR><BR>".$result->description;
    $finalResult->identifier=$result->identifier; // highlighting of rows
    $finalResult->identifier2=$result->identifier2;
    print(CommonUtils::getJavaScriptMsg($finalResult));
    return;
  } else {
    $finalResult->identifier=$postingOrderTO->orderSequenceNo;
    $postingType=$result->identifier2;
  }


  $finalResult->type=FLAG_ERRORTO_SUCCESS;

  if ($finalResult->type==FLAG_ERRORTO_SUCCESS) {
    $result=mysqli_query($dbConn->connection, "commit");
  }

  // create EDI File
  if ($finalResult->type==FLAG_ERRORTO_SUCCESS) {

      //will be deprecated shortly
      // $result = BroadcastingUtils::generateOrdersEDI($postingOrderTO);
      // $ediMSG = ($result) ? ("EDI File : OK") : ("EDI File : FAIL!");

      $dxs = microtime(true);
      $adaptorDocEx = new AdaptorDocumentExport($dbConn);
      $exportResult = $adaptorDocEx->generateExport($postingOrderTO);  //self contained error notifications
      $dxe = microtime(true);
      $dxt = round($dxe - $dxs, 4);
      $ediExportMSG = ($exportResult) ? (" | Depot EDI File : OK ($dxt)") : (""); //might have no export required.
  }

  $finalResult->description = "Order Successfully Processed as ".$postingType."<BR><STRONG><FONT STYLE='font-size:16px;'>DOCUMENT NUMBER: ".$postingOrderTO->documentNumber."</FONT></STRONG><BR><span style='font-size:11px;'>Capture Sequence: ".$finalResult->identifier ." (Previously displayed number)</span><span style='font-size:11px;'>" . $ediExportMSG . "</span><BR>";
  if(in_array($postDOCTYPE, array(DT_ORDINV,DT_DELIVERYNOTE,DT_ARRIVAL,DT_STOCKADJUST_NEG,DT_STOCKADJUST_POS, DT_QUOTATION, DT_MCREDIT_OTHER,DT_GOODS_IN_TRANSIT))){
        $finalResult->description .= "<BR><a href=javascript:; onclick=window.open('functional/presentations/presentationManagement.php?TYPE={$postDOCTYPE}&DSTATUS=Processed&CSOURCE=C&FINDNUMBER=".$finalResult->identifier."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');>[VIEW/PRINT DOCUMENT]</a>";
  } elseif(in_array($postDOCTYPE, array(DT_PAYMENTTO))){
        $finalResult->description .= "<BR><a href=javascript:; onclick=window.open('functional/presentations/presentationManagement.php?TYPE=PAYMENTTOMULT&DSTATUS=Processed&CSOURCE=C&FINDNUMBER=".$finalResult->identifier."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');>[VIEW/PRINT PAYMENT DETAILS]</a>";
  }
javascript:;
  if(!CommonUtils::isDepotUser()){
    if ($postDOCTYPE==DT_DELIVERYNOTE) $finalResult->description.="<BR><BR><SPAN style='color:red;'><B>NOTE:</B> NO Pricing is applicable for Delivery Notes</SPAN><BR><BR>";
  }

} else {

  include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingTO.php');
  include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingDetailTO.php');
  include_once($ROOT.$PHPFOLDER.'functional/import/processor/ProcessorTOH.php');

  $processorTOH = new ProcessorTOH($dbConn);

  $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
  $postingOrdersHoldingTO->principalUid = $principalId;
  $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
  $postingOrdersHoldingTO->dataSource = DS_CAPTURE;
  $postingOrdersHoldingTO->capturedBy = $userId;
  $postingOrdersHoldingTO->documentNo = $postDN;
  $postingOrdersHoldingTO->clientDocumentNo = $postingOrdersHoldingTO->documentNo;
  $postingOrdersHoldingTO->reference = $postCUSTREF;
  $postingOrdersHoldingTO->principalStoreUId = $postSTORE;
  $postingOrdersHoldingTO->deliveryInstructions = $postDELINST;
  $postingOrdersHoldingTO->documentTypeUId = $postDOCTYPE;
  $postingOrdersHoldingTO->orderDate = $postDOCDATE;
  $postingOrdersHoldingTO->deliveryDate = $postDELDATE;
  $postingOrdersHoldingTO->sourceDocumentNo=$postCLIENTSOURCEDOCUMENT;
  $postingOrdersHoldingTO->status = "R.A";
  $postingOrdersHoldingTO->statusMsg = "Requires Approval";
  $postingOrdersHoldingTO->documentOriginQueried = "Y";
  $postingOrdersHoldingTO->exceptionNotified = FLAG_STATUS_QUEUED; // ensure user gets notified as R.A. are excluded from processing

  for($i=0; $i<sizeof($arrPRODUCT); $i++) {
    if ($arrPRODUCT[$i]!="") {
      $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
      $postingOrdersHoldingDetailTO->principalProductUid = $arrPRODUCT[$i];
      $postingOrdersHoldingDetailTO->pallets = $arrPALLETS[$i];
      $postingOrdersHoldingDetailTO->quantity = $arrQTY[$i];
      // don't change this without changing the ProcessorTOH as it checks for these conditions
      if (trim($arrOVERRIDEPRICE[$i])!="") {
        $postingOrdersHoldingDetailTO->overridePriceType = PCA_USE_VENDOR;
        $postingOrdersHoldingDetailTO->nettPrice = $arrOVERRIDEPRICE[$i];
      }

      $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
    }
  }

  $result = $processorTOH->postTOH(array($postingOrdersHoldingTO));
  if ($result->type!=FLAG_ERRORTO_SUCCESS) {
    $finalResult->type=$result->type;
    $finalResult->description="The order could not be saved ! <BR><BR>".$result->description;
    print(CommonUtils::getJavaScriptMsg($finalResult));
    return;
  } else {
    $finalResult->identifier="Order Seq not yet assigned - approval required first";
    $postingType=$result->identifier2;
  }

  $finalResult->description = "Order Successfully submitted for Approval.<BR>";
  $finalResult->type=FLAG_ERRORTO_SUCCESS;
  $result=mysqli_query($dbConn->connection, "commit");

}

print(CommonUtils::getJavaScriptMsg($finalResult));
return;

?>

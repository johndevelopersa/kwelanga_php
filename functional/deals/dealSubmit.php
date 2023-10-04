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

$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback

if (!isset($_SESSION)) session_start;
// override the principal Id for now.
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION['user_id'];

$returnMessage;

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
$postDMLTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DMLTYPE']));
if (isset($_POST['CUSTOMERTYPE'])) $postCUSTOMERTYPE = $_POST['CUSTOMERTYPE']; else $postCUSTOMERTYPE="";
if (isset($_POST['CHAINSTOREUID'])) $postCHAINSTOREUID = $_POST['CHAINSTOREUID']; else $postCHAINSTOREUID="";
if (isset($_POST['PRICETYPE'])) $postPRICETYPE = $_POST['PRICETYPE']; else $postPRICETYPE="";
if (isset($_POST['DEALUID'])) $postDEALUID = $_POST['DEALUID']; else $postDEALUID="";
if (isset($_POST['REFERENCE'])) $postREFERENCE = $_POST['REFERENCE']; else $postREFERENCE="";
if (isset($_POST['PRODUCT'])) $postPRODUCT = $_POST['PRODUCT']; else $postPRODUCT="";
if (isset($_POST['LISTPRICE'])) $postLISTPRICE = $_POST['LISTPRICE']; else $postLISTPRICE="";
if (isset($_POST['DEALTYPE'])) $postDEALTYPE = $_POST['DEALTYPE']; else $postDEALTYPE="";
if (isset($_POST['VALUE'])) $postVALUE = $_POST['VALUE']; else $postVALUE="";
if (isset($_POST['EXCLINCL'])) $postEXCLINCL = $_POST['EXCLINCL']; else $postEXCLINCL="";
if (isset($_POST['STARTDATE'])) $postSTARTDATE = $_POST['STARTDATE']; else $postSTARTDATE="";
if (isset($_POST['ENDDATE'])) $postENDDATE = $_POST['ENDDATE']; else $postENDDATE="";
if (isset($_POST['DELETED'])) $postDELETED = $_POST['DELETED']; else $postDELETED=""; // this is only shown on capture screen if UPDATE. Blanks (unchecked) are preserved.

// convert to arrays
$arrCUSTOMERTYPE=explode(",",$postCUSTOMERTYPE);
$arrCHAINSTOREUID=explode(",",$postCHAINSTOREUID);
$arrPRICETYPE=explode(",",$postPRICETYPE);
$arrDEALUID=explode(",",$postDEALUID);
$arrPRODUCT=explode(",",$postPRODUCT);
$arrLISTPRICE=explode(",",$postLISTPRICE);
$arrDEALTYPE=explode(",",$postDEALTYPE);
$arrREFERENCE=explode(",",$postREFERENCE);
$arrVALUE=explode(",",$postVALUE);
$arrEXCLINCL=explode(",",$postEXCLINCL);
$arrSTARTDATE=explode(",",$postSTARTDATE);
$arrENDDATE=explode(",",$postENDDATE);
$arrDELETED=explode(",",$postDELETED);

// start of superficial checks. Main checks done in adminPost....php
$returnMessages=new ErrorTO;
if (($postDMLTYPE!="UPDATE") && ($postDMLTYPE!="INSERT")) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

if (($postDMLTYPE=="UPDATE") && ($postDEALUID=="")) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="UPDATE Requires a UID";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

$rowCnt=0;
$arrDuplicateProductsDates=array();
for($i=0; $i<sizeof($arrPRODUCT); $i++) {
	if ($arrPRODUCT[$i]!="") {
		$rowCnt++;
		if ($postDMLTYPE=="INSERT") {
			if (($arrVALUE[$i]>0) && (($arrDEALTYPE[$i]!=VAL_DEALTYPE_AMOUNT_OFF) && ($arrDEALTYPE[$i]!=VAL_DEALTYPE_PERCENTAGE))) {
				$returnMessages->type=FLAG_ERRORTO_ERROR;
				$returnMessages->description="Discount Value must be > zero if Deal Type is 'Amount Off' or 'Percentage'.";
				print(CommonUtils::getJavaScriptMsg($returnMessages));
				return;
			}
			// commented out : allow zero prices now as at 12Mar2012 
			/*
			if ($arrLISTPRICE[$i]<=0) {
				$returnMessages->type=FLAG_ERRORTO_ERROR;
				$returnMessages->description="List Price cannot be zero.";
				print(CommonUtils::getJavaScriptMsg($returnMessages));
				return;
			}
			*/
			if ($arrEXCLINCL[$i]!=LITERAL_DEAL_EXCLUSIVE) {
				$returnMessages->type=FLAG_ERRORTO_ERROR;
				$returnMessages->description="Only EXCLUSIVE of VAT is permitted for consistency.";
				print(CommonUtils::getJavaScriptMsg($returnMessages));
				return;
			}
			// incorrect values section
			if(($arrPRODUCT[$i]!="") && (!preg_match(GUI_PHP_INTEGER_REGEX,$arrPRODUCT[$i]))) {
				$returnMessages->type=FLAG_ERRORTO_ERROR;
				$returnMessages->description="Invalid Product value.";
				print(CommonUtils::getJavaScriptMsg($returnMessages));
				return; 
			};
			if(!preg_match(GUI_PHP_INTEGER_REGEX,$arrDEALTYPE[$i])) {
				$returnMessages->type=FLAG_ERRORTO_ERROR;
				$returnMessages->description="Invalid Deal Type value.";
				print(CommonUtils::getJavaScriptMsg($returnMessages));
				return; 
			};
			if(!preg_match(GUI_PHP_FLOAT_REGEX,$arrLISTPRICE[$i])) {
				$returnMessages->type=FLAG_ERRORTO_ERROR;
				$returnMessages->description="Invalid List Price value at row {$rowCnt}. ".$arrPRODUCT[$i]."-".$arrLISTPRICE[$i];
				print(CommonUtils::getJavaScriptMsg($returnMessages));
				return; 
			};
			if(!preg_match(GUI_PHP_FLOAT_REGEX,$arrVALUE[$i])) {
				$returnMessages->type=FLAG_ERRORTO_ERROR;
				$returnMessages->description="Invalid Discount Value.";
				print(CommonUtils::getJavaScriptMsg($returnMessages));
				return; 
			};
			// check start date
			if (preg_match(GUI_PHP_DATE_VALIDATION,$arrSTARTDATE[$i],$parts)) {
				if(!checkdate($parts[2],$parts[3],$parts[1])) {
					$returnMessages->type=FLAG_ERRORTO_ERROR;
					$returnMessages->description="Invalid Start Date format.";
					print(CommonUtils::getJavaScriptMsg($returnMessages));
					return; 
				}
			} else {
				$returnMessages->type=FLAG_ERRORTO_ERROR;
				$returnMessages->description="Invalid Start Date format.";
				print(CommonUtils::getJavaScriptMsg($returnMessages));
				return; 
			  }
		} // end INSERT checks
		// check end date
		if (preg_match(GUI_PHP_DATE_VALIDATION,$arrENDDATE[$i],$parts)) {
			if(!checkdate($parts[2],$parts[3],$parts[1])) {
				$returnMessages->type=FLAG_ERRORTO_ERROR;
				$returnMessages->description="Invalid End Date format.";
				print(CommonUtils::getJavaScriptMsg($returnMessages));
				return; 
			}
		} else {
			$returnMessages->type=FLAG_ERRORTO_ERROR;
			$returnMessages->description="Invalid End Date format.";
			print(CommonUtils::getJavaScriptMsg($returnMessages));
			return; 
		  }
		// check start date not after end date
		if(strtotime($arrSTARTDATE[$i])>strtotime($arrENDDATE[$i])) {
			$returnMessages->type=FLAG_ERRORTO_ERROR;
			$returnMessages->description="Start Date cannot be after End Date.";
			print(CommonUtils::getJavaScriptMsg($returnMessages));
			return; 
		}
		/*
		// check dates not too far in future
		if(((abs(strtotime($arrSTARTDATE[$i])-time()))/(60*60*24))>365) {
			$returnMessages->type=FLAG_ERRORTO_ERROR;
			$returnMessages->description="Dates cannot be more than 1 year in future/past";
			print(CommonUtils::getJavaScriptMsg($returnMessages));
			return; 
		}
		*/
	}
	$i++;
}

// row count check
if($rowCnt==0) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="No Products were found!";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
}

include_once($ROOT.$PHPFOLDER."DAO/PostProductDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/PostingPricingDealTO.php');
$postProductDAO = new PostProductDAO($dbConn);
// not really needed anymore
$myGuid = CommonUtils::getGUID();

$finalResult = new ErrorTO;
$copyP=array();
for($i=0; $i<sizeof($arrPRODUCT); $i++) {
	if ($arrPRODUCT[$i]!="") {
		// have to repeat these fields because of cloning the array
		$postingPricingDealTO = new PostingPricingDealTO();
		$postingPricingDealTO->DMLType=$postDMLTYPE;
		$postingPricingDealTO->customerTypeUid=$arrCUSTOMERTYPE[$i]; // for updates this will end up being blank, but is not a problem because update limits to only certain fields
		$postingPricingDealTO->chainOrStoreUid=$arrCHAINSTOREUID[$i];
		$postingPricingDealTO->user_uid=$userId;
		$postingPricingDealTO->guid=$myGuid;
		if ($postDMLTYPE=="UPDATE") $postingPricingDealTO->pduid=$arrDEALUID[$i]; // is overridden later after posting call
		
		$postingPricingDealTO->principalProdUid=$arrPRODUCT[$i];
		$postingPricingDealTO->priceTypeUId=$arrPRICETYPE[$i]; // default to 1 for time being
		$postingPricingDealTO->principalUid=$principalId;
		$postingPricingDealTO->listPrice=$arrLISTPRICE[$i];
		$postingPricingDealTO->dealTypeID=$arrDEALTYPE[$i];
		$postingPricingDealTO->discountValue=$arrVALUE[$i];
		$postingPricingDealTO->exclInclFlag=substr($arrEXCLINCL[$i],0,1);
		$postingPricingDealTO->activated="1"; // this is active, being live... may have another person capture and have someone else Ok it!
		$postingPricingDealTO->status_uid="1";
		$postingPricingDealTO->startDate=$arrSTARTDATE[$i];
		$postingPricingDealTO->endDate=$arrENDDATE[$i];
		$postingPricingDealTO->reference=$arrREFERENCE[$i];
		if ($postDMLTYPE=="UPDATE")	$postingPricingDealTO->deleted=$arrDELETED[$i];
		else $postingPricingDealTO->deleted=0;
		//$postingPricingDealTO->captureDate= // SET by processing script POSTDAO
		
		// Do the Actual Posting 
		$result=$postProductDAO->postPricing($postingPricingDealTO);
		if ($postingPricingDealTO->DMLType=="INSERT") $postingPricingDealTO->pduid=$result->identifier;
		if ($result->type!=FLAG_ERRORTO_SUCCESS) {
				$finalResult->type=FLAG_ERRORTO_ERROR;
				$finalResult->description="The deal batch could not be saved ! <BR><BR>".$result->description;
				print(CommonUtils::getJavaScriptMsg($finalResult));
				return;
		} else {
			$finalResult->description.=$postingPricingDealTO->principalProdUid." - ".$result->description."<BR>";
	 	  }
	 
	 	// store for edi output
	 	$copyP[]=$postingPricingDealTO;   //might need to use new statement
	}
}

$finalResult->type=FLAG_ERRORTO_SUCCESS;

if ($finalResult->type==FLAG_ERRORTO_SUCCESS) {
	$result=mysqli_query($dbConn->connection, "commit");
}
/*
// create EDI File
if ($finalResult->type==FLAG_ERRORTO_SUCCESS) {
	include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
	include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
	$productDAO = new ProductDAO($dbConn);
	$ediArr=array();
	$temp= array();
	$temp[]="pduid";
 	$temp[]="customerTypeUid";
	$temp[]="chainOrStoreUid";
	$temp[]="user_uid";
	$temp[]="guid";
	$temp[]="principalProdUid";
	$temp[]="priceTypeUId";
	$temp[]="principalUid";
	$temp[]="listPrice";
	$temp[]="dealTypeID";
	$temp[]="discountValue";
	$temp[]="exclInclFlag";
	$temp[]="activated";
	$temp[]="status_uid";
	$temp[]="startDate";
	$temp[]="endDate";
	$temp[]="reference";
	$temp[]="deleted";
	$temp[]="principalProductCode";
	$ediArr[]=$temp;
	foreach ($copyP as $TO) {
	 $mfPr = $productDAO->getPrincipalProductItem($principalId, $TO->principalProdUid);
	 unset($temp);
	 $temp= array();
	 $temp[]=$TO->pduid;
	 $temp[]=$TO->customerTypeUid;
	 $temp[]=$TO->chainOrStoreUid;
	 $temp[]=$TO->user_uid;
	 $temp[]=$TO->guid;
	 $temp[]=$TO->principalProdUid;
	 $temp[]=$TO->priceTypeUId;
	 $temp[]=$TO->principalUid;
	 $temp[]=$TO->listPrice;
	 $temp[]=$TO->dealTypeID;
	 $temp[]=$TO->discountValue;
	 $temp[]=$TO->exclInclFlag;
	 $temp[]=$TO->activated;
	 $temp[]=$TO->status_uid;
	 $temp[]=$TO->startDate;
	 $temp[]=$TO->endDate;
	 $temp[]=$TO->reference;
	 $temp[]=$TO->deleted;
	 if (sizeof($mfPr)==0) {
	 	 if ($TO->priceTypeUId==PRT_PRODUCT) {
		 	$finalResult->description .= "<BR><BR>Could Not Generate EDI file as Principal Product Lookup Failed.";
		 	$temp[]="";
		 } 
	 } else {
	 	$temp[]=$mfPr[0]['product_code'];	
	 }
	 $ediArr[]=$temp;
	}
	$ediResult=BroadcastingUtils::createEDIFile($ediArr,"price ".$postDMLTYPE,FILE_FTP_DOPS_FILETYPE_PRICE,$dbConn);
	$finalResult->description .= "<BR><BR>".$ediResult->description;
}
*/
$dbConn->dbClose();

$finalResult->description="Deal Successfully Processed.<BR><BR>".$finalResult->description;
print(CommonUtils::getJavaScriptMsg($finalResult));
return; 

?>

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
if (isset($_POST['LOCATIONTYPE'])) $postLOCATIONTYPE = $_POST['LOCATIONTYPE']; else $postLOCATIONTYPE="";
if (isset($_POST['PRODUCTLEVEL'])) $postPRODUCTLEVEL = $_POST['PRODUCTLEVEL']; else $postPRODUCTLEVEL="";
if (isset($_POST['LOCATIONLIST'])) $postLOCATIONLIST = $_POST['LOCATIONLIST']; else $postLOCATIONLIST="";
if (isset($_POST['PRODUCTLIST'])) $postPRODUCTLIST = $_POST['PRODUCTLIST']; else $postPRODUCTLIST="";
if (isset($_POST['LISTPRICE'])) $postLISTPRICE = $_POST['LISTPRICE']; else $postLISTPRICE="";
if (isset($_POST['DEALTYPE'])) $postDEALTYPE = $_POST['DEALTYPE']; else $postDEALTYPE="";
if (isset($_POST['DISCOUNTVALUE'])) $postDISCOUNTVALUE = $_POST['DISCOUNTVALUE']; else $postDISCOUNTVALUE="";
if (isset($_POST['STARTDATE'])) $postSTARTDATE = $_POST['STARTDATE']; else $postSTARTDATE="";
if (isset($_POST['ENDDATE'])) $postENDDATE = $_POST['ENDDATE']; else $postENDDATE="";
if (isset($_POST['REFERENCE'])) $postREFERENCE = $_POST['REFERENCE']; else $postREFERENCE="";


// start of superficial checks. Main checks done in adminPost....php
$returnMessages=new ErrorTO;

include_once($ROOT.$PHPFOLDER."DAO/PostProductDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/PostingPricingDealTO.php');
$postProductDAO = new PostProductDAO($dbConn);

// this front end expects prices to be loaded in BULK, so the uids are a comma separated list for products/product groups and stores/chains

$postingPricingDealTO = new PostingPricingDealTO();
$postingPricingDealTO->DMLType="INSERT";
$postingPricingDealTO->customerTypeUid=$postLOCATIONTYPE;
$postingPricingDealTO->chainOrStoreUid=$postLOCATIONLIST;
$postingPricingDealTO->user_uid=$userId;

$postingPricingDealTO->principalProdUid=$postPRODUCTLIST;
$postingPricingDealTO->priceTypeUId=$postPRODUCTLEVEL; 
$postingPricingDealTO->principalUid=$principalId;
$postingPricingDealTO->listPrice=$postLISTPRICE;
$postingPricingDealTO->dealTypeID=$postDEALTYPE;
$postingPricingDealTO->discountValue=$postDISCOUNTVALUE;
$postingPricingDealTO->exclInclFlag="E";
$postingPricingDealTO->activated="1"; // this is active, being live... may have another person capture and have someone else Ok it!
$postingPricingDealTO->status_uid="1";
$postingPricingDealTO->startDate=$postSTARTDATE;
$postingPricingDealTO->endDate=$postENDDATE;
$postingPricingDealTO->reference=$postREFERENCE;
$postingPricingDealTO->deleted=0;
//$postingPricingDealTO->captureDate= // SET by processing script POSTDAO

// Do the Actual Posting 
$finalResult=$postProductDAO->postPricingBulk($postingPricingDealTO);
if ($finalResult->type!=FLAG_ERRORTO_SUCCESS) {
		$finalResult->type=FLAG_ERRORTO_ERROR;
		$finalResult->description="The deal batch could not be saved ! <BR><BR>".$finalResult->description;
		print(CommonUtils::getJavaScriptMsg($finalResult));
		return;
} else {
	$finalResult->description.=$postingPricingDealTO->principalProdUid." - ".$finalResult->description."<BR>";
}
	 

$finalResult->type=FLAG_ERRORTO_SUCCESS;

if ($finalResult->type==FLAG_ERRORTO_SUCCESS) {
	$result=mysqli_query($dbConn->connection, "commit");
}

$dbConn->dbClose();

$finalResult->description="Deal Successfully Processed.<BR><BR>".$finalResult->description;
print(CommonUtils::getJavaScriptMsg($finalResult));
return; 

?>

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
$postDMLTYPE=mysqli_real_escape_string($dbConn->connection,htmlspecialchars($_POST['DMLTYPE']));
if (isset($_POST['CUSTOMERTYPE'])) $postCUSTOMERTYPE = $_POST['CUSTOMERTYPE']; else $postCUSTOMERTYPE="";
if (isset($_POST['STORECHAIN'])) $postSTORECHAIN = $_POST['STORECHAIN']; else $postSTORECHAIN="";
if (isset($_POST['GROUPING'])) $postGROUPING = $_POST['GROUPING']; else $postGROUPING="";
if (isset($_POST['DESCRIPTION'])) $postDESCRIPTION = $_POST['DESCRIPTION']; else $postDESCRIPTION="";
if (isset($_POST['PRODUCTTYPE'])) $postPRODUCTTYPE = $_POST['PRODUCTTYPE']; else $postPRODUCTTYPE="";
if (isset($_POST['PRODUCTTYPELIST'])) $postPRODUCTTYPELIST = $_POST['PRODUCTTYPELIST']; else $postPRODUCTTYPELIST="";
if (isset($_POST['DESCRIPTION'])) $postDESCRIPTION = $_POST['DESCRIPTION']; else $postDESCRIPTION="";
if (isset($_POST['UNITPRICETYPE'])) $postUNITPRICETYPE = $_POST['UNITPRICETYPE']; else $postUNITPRICETYPE="";
if (isset($_POST['QUANTITY'])) $postQUANTITY = $_POST['QUANTITY']; else $postQUANTITY="";
if (isset($_POST['DEALTYPE'])) $postDEALTYPE = $_POST['DEALTYPE']; else $postDEALTYPE="";
if (isset($_POST['VALUE'])) $postVALUE = $_POST['VALUE']; else $postVALUE="";
if (isset($_POST['APPLYLEVEL'])) $postAPPLYLEVEL = $_POST['APPLYLEVEL']; else $postAPPLYLEVEL="";
if (isset($_POST['STARTDATE'])) $postSTARTDATE = $_POST['STARTDATE']; else $postSTARTDATE="";
if (isset($_POST['ENDDATE'])) $postENDDATE = $_POST['ENDDATE']; else $postENDDATE="";
if (isset($_POST['APPLYPERUNIT'])) $postAPPLYPERUNIT = "N"; else $postAPPLYPERUNIT = "N";
if (isset($_POST['CUMULATIVETYPE'])) $postCUMULATIVETYPE = $_POST['CUMULATIVETYPE']; else $postCUMULATIVETYPE="";
if (isset($_POST['STATUS'])) $postSTATUS = $_POST['STATUS']; else $postSTATUS="";
if (isset($_POST['UID'])) $postUID = $_POST['UID']; else $postUID="1";

// start of superficial checks. Main checks done in adminPost....php
$returnMessages=new ErrorTO;
if (($postDMLTYPE!="UPDATE") && ($postDMLTYPE!="INSERT")) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};

if (($postDMLTYPE=="UPDATE") && ($postUID=="")) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="UPDATE Requires a UID";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return; 
};


include_once($ROOT.$PHPFOLDER."DAO/PostProductDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentPricingTO.php');
$postProductDAO = new PostProductDAO($dbConn);

$resultTO = new ErrorTO;
$postingDocumentPricingTO = new PostingDocumentPricingTO();
$postingDocumentPricingTO->DMLType=$postDMLTYPE;
$postingDocumentPricingTO->uid=$postUID; 
$postingDocumentPricingTO->principalUId=$principalId;
$postingDocumentPricingTO->grouping=$postGROUPING;
$postingDocumentPricingTO->description=$postDESCRIPTION;
$postingDocumentPricingTO->customerTypeUId=$postCUSTOMERTYPE;
$postingDocumentPricingTO->storeChainUId=$postSTORECHAIN;
$postingDocumentPricingTO->unitPriceTypeUId=$postUNITPRICETYPE;
$postingDocumentPricingTO->quantity=$postQUANTITY;
$postingDocumentPricingTO->dealTypeUId=$postDEALTYPE;
$postingDocumentPricingTO->value=$postVALUE;
$postingDocumentPricingTO->applyLevel=$postAPPLYLEVEL;
$postingDocumentPricingTO->startDate=$postSTARTDATE;
$postingDocumentPricingTO->endDate=$postENDDATE;
$postingDocumentPricingTO->applyPerUnit=$postAPPLYPERUNIT;
$postingDocumentPricingTO->cumulativeType=$postCUMULATIVETYPE;
$postingDocumentPricingTO->status=$postSTATUS;
$postingDocumentPricingTO->productType=$postPRODUCTTYPE;


if (trim($postPRODUCTTYPELIST)!="") $postingDocumentPricingTO->productArr=explode(",",$postPRODUCTTYPELIST);


// Do the Actual Posting 
$resultTO=$postProductDAO->postDocumentPricing($postingDocumentPricingTO);

if ($resultTO->type!=FLAG_ERRORTO_SUCCESS) {
	$resultTO->type=FLAG_ERRORTO_ERROR;
	$resultTO->description="The document pricing could not be saved ! <BR><BR>".$resultTO->description. print_r($postingDocumentPricingTO);
	print(CommonUtils::getJavaScriptMsg($resultTO));
	return;
} 

if ($resultTO->type==FLAG_ERRORTO_SUCCESS) {
	$result=mysqli_query($dbConn->connection, "commit");
}

$dbConn->dbClose();

$resultTO->description="<BR>Document Pricing Successfully Processed.<BR>";
print(CommonUtils::getJavaScriptMsg($resultTO));
return; 

?>

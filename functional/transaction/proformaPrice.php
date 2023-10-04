<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");

$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback

if (!isset($_SESSION)) session_start;
// override the principal Id for now.
$principalId = $_SESSION['principal_id'];
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$userId = $_SESSION['user_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();
	
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
if (isset($_GET['STORE'])) $postSTORE = mysqli_real_escape_string($dbConn->connection, $_GET['STORE']); else $postSTORE="";
if (isset($_GET['CUSTREF'])) $postCUSTREF = mysqli_real_escape_string($dbConn->connection, $_GET['CUSTREF']); else $postCUSTREF="";
if (isset($_GET['DOCTYPE'])) $postDOCTYPE = mysqli_real_escape_string($dbConn->connection, $_GET['DOCTYPE']); else $postDOCTYPE="";
if (isset($_GET['PRODUCT'])) $postPRODUCT = mysqli_real_escape_string($dbConn->connection, $_GET['PRODUCT']); else $postPRODUCT="";
if (isset($_GET['QTY'])) mysqli_real_escape_string($dbConn->connection, $postQTY = $_GET['QTY']); else $postQTY="";
if (isset($_GET['OVERRIDEPRICE'])) mysqli_real_escape_string($dbConn->connection, $postOVERRIDEPRICE = $_GET['OVERRIDEPRICE']); else $postOVERRIDEPRICE="";

if ($postSTORE=="") {
	echo "No Store Supplied.<br>";
	return;
}

$adminDAO = new AdministrationDAO($dbConn);
$hasRoleVP = $adminDAO->hasRole($userId, $principalAliasId,ROLE_VIEW_PRICE);
if ($hasRoleVP!==true) {
	echo "You do not have permissions to view Pricing";
	return;
}

// convert to arrays
$arrPRODUCT=explode(",",$postPRODUCT);
$arrQTY=explode(",",$postQTY);
$arrOVERRIDEPRICE=explode(",",$postOVERRIDEPRICE);

include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/PostingOrderTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrderDetailTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrderDocumentPricingTO.php');

$storeDAO=new StoreDAO($dbConn);
$productDAO=new ProductDAO($dbConn);
$mfS=$storeDAO->getPrincipalStoreItem($postSTORE);

$postingOrderTO = new PostingOrderTO;
// the headers for the TO, only setup some
$postingOrderTO->storeChainUId=$postSTORE;
$postingOrderTO->principalUId=$principalAliasId;
$postingOrderTO->orderNumber=$postCUSTREF;
$postingOrderTO->captureUserUId=$userId;
$postingOrderTO->documentType=$postDOCTYPE;
for($i=0; $i<sizeof($arrPRODUCT); $i++) {
	if ($arrPRODUCT[$i]!="") {
		$postingOrderDetailTO = new PostingOrderDetailTO;
		$postingOrderDetailTO->productUId=$arrPRODUCT[$i];
		$postingOrderDetailTO->quantity=$arrQTY[$i];
		$postingOrderDetailTO->priceOverrideValue=$arrOVERRIDEPRICE[$i];
		$postingOrderTO->detailArr[]=$postingOrderDetailTO;
	}
}

$documentTotal=0; 
$errorTO = $productDAO->getFinalInvoicePricing($postingOrderTO, $documentTotal, $userId, $principalAliasId);
if ($errorTO->type != FLAG_ERRORTO_SUCCESS) {
	echo "error occurred processing proforma Pricing.<br><br>".$errorTO->description;
	return;
}
echo "<html>
		<head>
			<style>
			body { font-size:12px; }
			table { font-size:12px; border:1; border-style:solid; }
			tr { border:1; border-style:solid; }
			th { border:1; border-right-style:solid; border-bottom-style:solid; border-bottom-width:1; background-color:#CCCCEE;}
			td { border:1; border-right-style:solid; border-bottom-style:solid; border-bottom-width:1;}
			</style>
		</head>
	  <body>";
echo "<table>
	  <tr><th colspan=9 style='font-size:15px; background-color:#dddddd;'>Proforma Pricing for : {$postCUSTREF}</th></tr>
	  <tr>
		<th>Product Code</th>
		<th>Description</th>
		<th>Quantity</th>
		<th>List Price(ea)</th>
		<th>Discount Value(ea)</th>
		<th>Discount ref</th>
		<th>Nett Price</th>
		<th>Ext Price</th>
	 </tr>";
	 
$quantity=0; $totalEP=0;
foreach ($postingOrderTO->detailArr as $row) {
	$mfP=$productDAO->getPrincipalProductItem($principalAliasId,$row->productUId);

	echo "<tr>
			<td nowrap>{$mfP[0]['product_code']}</td>
			<td nowrap>{$mfP[0]['product_description']}</td>
			<td nowrap style='text-align:right;'>{$row->quantity}</td>
			<td nowrap style='text-align:right;'>{$row->listPrice}</td>
			<td nowrap style='text-align:right;'>".number_format($row->discountValue,2)."</td>
			<td nowrap>{$row->discountReference}</td>
			<td nowrap style='text-align:right;'>".number_format($row->nettPrice,2)."</td>
			<td nowrap style='text-align:right;'>".number_format($row->nettPrice*$row->quantity,2)."</td>
		 </tr>";
		 
	$quantity+=$row->quantity;
	$totalEP+=$row->nettPrice*$row->quantity;
}


// inv bulk discounts
$outputHdr=false;
foreach ($postingOrderTO->pricingDocumentArr as $row) {
	if ((!$outputHdr) && ($row->applyLevel==DPL_DOCUMENT)) {
		echo "<tr><th colspan=8 style='text-align:left;'>Bulk Discounts / Charges</th></tr>";
		$outputHdr=true; 
	};
	
	if ($row->applyLevel==DPL_DOCUMENT) {
		echo "<tr>
				<td colspan=3>{$row->description} (&gt;={$row->quantity} ".GUICommonUtils::translateUnitPriceType($row->unitPriceTypeUId).")</td>
				<td>&nbsp;</td>
				<td style='text-align:right;'>".number_format($row->discountValue,2)."</td>
				<td style='text-align:right;'>&nbsp;</td>
				<td style='text-align:right;'>&nbsp;</td>
				<td style='text-align:right;'>".number_format($row->discountValue*-1,2)."</td>
			 </tr>";
			 
		$totalEP+=$row->discountValue*-1;
	}
		
}
  	
// totals
echo "<tr>
		<th colspan=2>TOTALS</th>
		<th>{$quantity}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>".number_format($totalEP,2)."</th>
	 </tr>";
	
echo "</table>
	  <span style='".FONT_UNOBTRUSIVE_INFO."' >* This order has NOT been submitted !</span>";
echo "</body></html>";
$dbConn->dbClose();


?>

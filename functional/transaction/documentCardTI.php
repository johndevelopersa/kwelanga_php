<?php
/*************************************************************
 * TAX INVOICE DOCUMENT CARD
 *************************************************************/

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
if (
	((!isset($_GET["KEYFROMLINK"])) && (!isset($_POST["KEYFROMLINK"]))) ||
	((isset($_GET["KEYFROMLINK"])) && ($_GET["KEYFROMLINK"]=="")) ||
	((isset($_POST["KEYFROMLINK"])) && ($_POST["KEYFROMLINK"]==""))
   ) {
   	require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
} else {
	include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php"); // needed because of access_control commented out
	include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
}
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");

$principalVergezocht="49";
$principalGrowingManufacturers="122";

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

if (isset($_GET['DOCMASTID'])) $postDOCMASTID=mysql_real_escape_string(htmlspecialchars($_GET['DOCMASTID']));
else if (isset($_POST['DOCMASTID'])) $postDOCMASTID=mysql_real_escape_string(htmlspecialchars($_POST['DOCMASTID']));
else $postDOCMASTID="";

if (isset($_GET["KEYFROMLINK"])) $postKEYFROMLINK=$_GET["KEYFROMLINK"]; else $postKEYFROMLINK=""; // the user came to this page from an email link - no userlogin necessary

if (!isset($_SESSION)) session_start() ;

if ($postKEYFROMLINK=="") {
	$userId = $_SESSION['user_id'] ;
	$principalId = $_SESSION['principal_id'] ;
	$principalName = $_SESSION['principal_name'] ;
	$userCategory = $_SESSION['user_category'];
} else {
	$userId="";
	$principalId="";
	$principalName="";
	$userCategory = "";
}

$principalDAO = new PrincipalDAO($dbConn);
$storeDAO = new StoreDAO($dbConn);
$transactionDAO = new TransactionDAO($dbConn);


if (($postKEYFROMLINK!="") || ($userCategory==PT_DEPOT)) {
	//all security is bypassed
	$mfT = $transactionDAO->getDocumentWithDetailIgnorePermissionsItem($postDOCMASTID);
	if (sizeof($mfT)==0) {
		echo "You do not have access to this information, or document master does not exist.(1)";
		return;
	}
	$hasRoleVP = true;


	if (
	    (($userCategory==PT_DEPOT) && ($mfT[0]['depot_wms']!="Y")) ||
	    (($userCategory!=PT_DEPOT) && ((MD5(gmdate('Y-m-d')).base64_encode($postDOCMASTID))!=$postKEYFROMLINK))
	){
	  echo "You do not have access to this information, or document master does not exist.(2)";
	  return;
	}

/*
	if (MD5(gmdate('Y-m-d')).base64_encode($postDOCMASTID)!=$postKEYFROMLINK) {
		echo "You do not have access to this information, or document master does not exist.(2)";
		return;
	}
	*/
} else {

	// this also doubles as the security check because this sql joins on user_principal_depot
	$mfT = $transactionDAO->getDocumentWithDetailItem($userId, $principalId, $postDOCMASTID);
	if (sizeof($mfT)==0) {
		echo "You do not have access to this information, or document master does not exist.(3)";
		return;
	}

	$adminDAO = new AdministrationDAO($dbConn);
	$hasRoleVP = $adminDAO->hasRole($userId, $principalId,ROLE_VIEW_PRICE);
}

$mfP = $principalDAO->getPrincipalItem($mfT[0]["principal_uid"]);
$mfS = $storeDAO->getPrincipalStoreItem($mfT[0]["principal_store_uid"]);

?>
<HTML>
<HEAD>
</HEAD>
<BODY style='width:200px; font-family:Verdana,Arial,Helvetica,sans-serif;'>

<!-- email -->
<p style="text-align:center;"><A style='color:grey; text-align:center; font-size:0.8em;' href='javascript:;' onclick='pdfDoc();'>[view as PDF TAX INVOICE]</A></p>
<h2 style="text-align:center;"><?php echo ((!in_array($mfT[0]["principal_uid"],array($principalVergezocht,$principalGrowingManufacturers)))?"C o p y&nbsp;&nbsp;&nbsp;&nbsp;":""); ?>T A X&nbsp;&nbsp;&nbsp;&nbsp;I N V O I C E</h2>
<hr>
<table style="font-size:11; text-align:right;">
<tr style="width:100%"><td style="width:70%;">&nbsp;</td><td style="width:30%;" nowrap><b><?php echo $mfP[0]['principal_name']; ?></b></td></tr>
<tr><td style="font-size:8; color:#BBBBBB;">Physical Address:</td><td nowrap><?php echo $mfP[0]['physical_add1']; ?></td></tr>
<tr><td>&nbsp;</td><td nowrap><?php echo $mfP[0]['physical_add2']; ?></td></tr>
<tr><td>&nbsp;</td><td nowrap><?php echo $mfP[0]['physical_add3']; ?></td></tr>
<tr><td>&nbsp;</td><td nowrap><?php echo $mfP[0]['physical_add4']; ?></td></tr>
<tr><td style="font-size:8; color:#BBBBBB;">Postal Address:</td><td nowrap><?php echo $mfP[0]['postal_add1']; ?></td></tr>
<tr><td>&nbsp;</td><td nowrap><?php echo $mfP[0]['postal_add2']; ?></td></tr>
<tr><td>&nbsp;</td><td nowrap><?php echo $mfP[0]['postal_add3']; ?></td></tr>
<tr><td>&nbsp;</td><td nowrap><?php echo $mfP[0]['postal_add4']; ?></td></tr>
<tr><td style="font-size:8; color:#BBBBBB;">VAT:</td><td nowrap><?php echo $mfP[0]['vat_num']; ?></td></tr>
<tr><td style="font-size:8; color:#BBBBBB;">Telephone:</td><td nowrap><?php echo $mfP[0]['office_tel']; ?></td></tr>
<tr><td>&nbsp;</td><td></td></tr>
</table>
<BR>
<hr>

<BR>&nbsp;<BR>

<FIELDSET>
<LEGEND style='font-size:9px; color:#999999; padding-left:10px; padding-right:10px;'>I n v o i c e d&nbsp;&nbsp;&nbsp;&nbsp;D o c k e t&nbsp;&nbsp;&nbsp;&nbsp;f o r&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $principalName; ?>, <?php echo $mfT[0]['depot_name']; ?></LEGEND>

<BR>

<TABLE style='border-style:none; width:100%'>

<?php if ($mfT[0]['has_associated_notes']=="1") { ?>
<TR>
	<TD style='font-weight:bold; font-size:0.6em; color:red; text-align:center;' colspan="4">WARNING : This document has associated credits/debits applied to it that are not shown here!</TD>
</TR>
<?php } ?>

<!-- doc dates and ref -->
<TR>
	<TD colspan="2" style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Customer:</SPAN><BR><SPAN style='font-size:1.2em;'><?php echo $mfT[0]['store_name']; ?></SPAN></TD>
	<TD colspan="1" style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Document Number:</SPAN><BR><SPAN style='font-size:1.2em;'><?php echo $mfT[0]['document_number']; ?></SPAN></TD>
</TR>
<TR>
	<TD nowrap style='vertical-align:top;'><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Deliver To:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['deliver_add1']; ?><BR><?php echo $mfT[0]['deliver_add2']; ?><BR><?php echo $mfT[0]['deliver_add3']; ?></SPAN></TD>
	<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Bill To:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfS[0]['bill_name']; ?><BR><?php echo $mfS[0]['bill_add1']; ?><BR><?php echo $mfS[0]['bill_add2']; ?><BR><?php echo $mfS[0]['bill_add3']; ?><BR>VAT: <?php echo $mfS[0]['vat_number']; ?></SPAN></TD>
	<TD nowrap style=''></TD>
</TR>
<TR>
	<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>OrderDate:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['order_date']; ?></SPAN></TD>
	<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Delivery Date:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['delivery_date']; ?></SPAN></TD>
	<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Invoice Date:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['invoice_date']; ?></SPAN></TD>
</TR>
<TR>
	<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Document Type:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['document_type_description']; ?></SPAN></TD>
	<TD colspan="2" nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Invoice No:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['invoice_number']; ?></SPAN></TD>
</TR>

<TR>
	<TD colspan="3">&nbsp;</TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em; color:#999999;' colspan="3">Delivery Details</TD>
</TR>

<!-- other details -->
<TR>
	<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Customer Order No:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['customer_order_number']; ?></SPAN></TD>
	<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Delivery Day:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['delivery_day']; ?></SPAN></TD>
	<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Cases:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['cases']; ?></SPAN></TD>
</TR>
<TR>
	<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Claim No:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['claim_number']; ?></SPAN></TD>
	<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>GRV Number:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['grv_number']; ?></SPAN></TD>
	<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Related Source Docket:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['source_document_number']; ?></SPAN></TD>
</TR>

<TR>
	<TD colspan="3">&nbsp;</TD>
</TR>
</TABLE>

</FIELDSET>

<!-- detail -->
<FIELDSET>
<LEGEND style='font-size:9px; color:#999999; padding-left:10px; padding-right:10px;'>I t e m&nbsp;&nbsp;&nbsp;L i n e&nbsp;&nbsp;&nbsp;D e t a i l s</LEGEND>

<BR>
<STYLE>
th.detail { text-align:right; padding:3; margin:0; border-style:solid; border-width:1; border-color:#CCCCCC; font-weight:bold; font-size:0.7em; white-space:nowrap;}
td.detail { text-align:right; padding:3; margin:0; border-bottom-style:dashed; border-bottom-width:1; border-left-style:dashed; border-left-width:1; border-color:grey; font-weight:normal; font-size:0.7em; white-space:nowrap; }
</STYLE>
<CENTER>
<TABLE style='border-style:solid; border-width:1; border-color:#CCCCCC; padding:0; margin:0;' cellpadding=0; cellspacing=0;>
<TR style='padding:0; margin:0;'>
	<TH width="10%" class='detail' colspan="1">Product</TH>
	<TH width="30%" class='detail' colspan="1">Description</TH>
	<TH width="5%" class='detail' colspan="1">Order<br>Qty</TH>
	<TH width="5%" class='detail' colspan="1">Doc<br>Qty</TH>
	<TH width="5%" class='detail' colspan="1">Del<br>Qty</TH>
	 <!-- REMOVED <TH width="4%" class='detail' colspan="1">Pal<br>Qty</TH>-->
	<TH width="6%" class='detail' colspan="1">Sell<br>Price</TH>
	<TH width="7%" class='detail' colspan="1">Disc<br>Val</TH>
	<TH width="8%" class='detail' colspan="1">Nett<br>Price</TH>
	<TH width="9%" class='detail' colspan="1">Ext<br>Price</TH>
	<TH width="7%" class='detail' colspan="1">VAT<br>Amnt</TH>
	<TH width="8%" class='detail' colspan="1">Total</TH>
</TR>
<?php
$totalPallets=0;
foreach($mfT as $row) {
	$totalPallets+=intval($row['pallets']);
	echo "<TR style='padding:2; margin:0;'>
		  <TD class='detail' style='text-align:left' align='left'>{$row['product_code']}</TD>
		  <TD class='detail' style='text-align:left' align='left'>{$row['product_description']}</TD>
		  <TD class='detail'>{$row['ordered_qty']}</TD>
		  <TD class='detail'>{$row['document_qty']}</TD>
		  <TD class='detail'>{$row['delivered_qty']}</TD>
		 <!-- REMOVED <TD class='detail'>".(($row['pallets']=="")?"-":$row['pallets'])."</TD>-->";
	if (!$hasRoleVP) {
		echo "<TD colspan=\"6\" class='detail'>not authorised to view pricing</TD>";
 	} else {
 		echo "<TD nowrap class='detail'>".number_format($row['selling_price'],2)."</TD>
 			  <TD nowrap class='detail'>".number_format($row['discount_value'],2)."</TD>
 			  <TD nowrap class='detail'>".number_format($row['net_price'],2)."</TD>
 			  <TD nowrap class='detail'>".number_format($row['extended_price'],2)."</TD>
 			  <TD nowrap class='detail'>".number_format($row['vat_amount'],2)."</TD>
 			  <TD nowrap class='detail'>".number_format($row['total'],2)."</TD>";
	}
	echo "</TR>";
	$totalEP=$row['exclusive_total']; // not cumulative !
	$totalVAT=$row['vat_total']; // not cumulative !
	$totalINV=$row['invoice_total']; // not cumulative !
}


if ($mfT[0]["orders_uid"]!="") {
	$mfDP = $transactionDAO->getOrderPricingDocumentItems($mfT[0]["orders_uid"],DPL_DOCUMENT);
	$totalBulkDiscount=0;
	if (sizeof($mfDP)>0) {
		echo "<tr><TD class='detail' colspan=\"11\" nowrap style='text-align:left;'><b>Document Bulk Discounts / Charges</b></TD></tr>";

		include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
		$productDAO = new ProductDAO($dbConn);

		if (!$hasRoleVP) {
			echo "<tr><TD class='detail' colspan=\"11\" nowrap style='text-align:left;'>Bulk Discounts / Charges found, but user not authorised to view pricing</b></TD></tr>";
		}
		foreach ($mfDP as $row) {
			$suffix=($row["deal_type_uid"]==VAL_DEALTYPE_AMOUNT_OFF)?"/On":"";
			$calculatedPrice=$row["discount_value"]*(-1);
			$discountPrice=$row["discount_value"];
			// override some DTs with a suffix
			switch ($row["deal_type_uid"]) {
				case VAL_DEALTYPE_AMOUNT_OFF: {
						$suffixDesc="(&gt;={$row["quantity"]} {$row["unit_price_type_description"]})";
						break;
					}
				case VAL_DEALTYPE_PERCENTAGE: {
						$suffixDesc="({$row["value"]}% {$row["unit_price_type_description"]})";
						break;
				}
				default: {
					$suffixDesc="";
				}
			}
			$totalBulkDiscount+=floatval($calculatedPrice);
			echo "<tr>
					<TD class='detail' colspan=\"4\" nowrap style='text-align:left;'>{$row["description"]}{$suffixDesc}</TD>
					<TD class='detail' nowrap>".(GUICommonUtils::translateDealType($row["deal_type_uid"]).$suffix)."</TD>
					<TD class='detail' nowrap>&nbsp;</TD>
					<TD class='detail' nowrap>".number_format($discountPrice,2)."</TD>
					<TD class='detail' nowrap>&nbsp;</TD>
					<TD class='detail' nowrap>".number_format($calculatedPrice,2)."</TD>
					<TD class='detail' nowrap>".number_format($calculatedPrice*VAL_VAT_RATE,2)."</TD>
					<TD class='detail' nowrap>".number_format($calculatedPrice,2)."</TD>
				  </tr>";
		}
		/* should be part of dh totals
		$totalINV+=$calculatedPrice+$calculatedPrice*VAL_VAT_RATE;
		$totalVAT+=$calculatedPrice*VAL_VAT_RATE;
		$totalEP+=$calculatedPrice;
		*/
	}
}


?>

<!-- total line -->
<TR style='padding:0; margin:0;'>
<TH colspan="4"></TH>
<!-- REMOVED <TH class='detail'><?php echo $totalPallets; ?></TH> -->
<?php if ($hasRoleVP) { ?>
	<TH colspan="4"></TH>
	<TH nowrap class='detail'><?php echo number_format($totalEP,2); ?></TH>
	<TH class='detail'><?php echo number_format($totalVAT,2); ?></TH>
	<TH class='detail'><?php echo number_format($totalINV,2); ?></TH>
<?php } ?>
</TR>
</TABLE>

</CENTER>
</FIELDSET>

<!-- footer -->
<TABLE style='width:100%;'>
<TR>
<TD colspan="2" style='text-align:center; color:grey; font-weight:normal; font-size:0.55em;'><script type="text/javascript">var d = new Date(); document.write("<b>" + d.getDate() + "/" + d.getMonth() + "/" + d.getFullYear() + "&nbsp;&nbsp;" + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds() + "</b>");</script></TD>
</TR>
</TABLE>

</BODY>

</HTML>

<?php
$dbConn->dbClose();
?>

<script type='text/javascript' defer>
function pdfDoc() {
	var params="?USERID=<?php echo $userId; ?>&OBJECTID=<?php echo EO_DOC_CARD_TI; ?>&DOCMASTID=<?php echo $postDOCMASTID; ?>&KEYFROMLINK=<?php echo $postKEYFROMLINK; ?>";
	window.location='<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/pdfUserHTML.php'+params;
}
</script>

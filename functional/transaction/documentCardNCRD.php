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

<FIELDSET>
<LEGEND colspan="2"; style='font-weight:900; font-size:2em; color:black;'>C O P Y&nbsp;&nbsp;&nbsp;&nbsp;C R E D I T&nbsp;&nbsp;&nbsp;&nbsp;N O T E &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $principalName; ?></LEGEND>
<BR>

<TABLE style='border-style:none; width:100%'>

<!-- doc dates and ref -->
<TR>
	<TD colspan="2" style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Invoice To Name :</SPAN><BR><SPAN style='font-size:1.2em;'><?php echo $mfT[0]['bill_name']; ?></SPAN></TD>
	<TD colspan="1" style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Credit Note Number:</SPAN><BR><SPAN style='font-weight:bold; font-size:0.9em;'><?php echo ltrim($mfT[0]['alternate_document_number'],'0'); ?></SPAN></TD>
	<TD colspan="1" style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Document Number:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo ltrim($mfT[0]['document_number'],'0'); ?></SPAN></TD>
</TR>
<TR>
	<TD colspan="2" style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Customer:</SPAN><BR><SPAN style='font-size:0.9em;'><?php echo $mfT[0]['store_name']; ?></SPAN></TD>
</TR>
<TR>
		<TD colspan="3" nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Delivery Address:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['deliver_add1']; ?>, <?php echo $mfT[0]['deliver_add2']; ?>, <?php echo $mfT[0]['deliver_add3']; ?></SPAN></TD>
		<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Document Date</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['invoice_date']; ?></SPAN></TD>
</TR>
	<TR>
		<TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Vat No:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['vat_number']; ?></SPAN></TD>
		<TD nowrap style=''></TD>
	<TD nowrap style=''></TD>
</TR>
  <TR>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Document Type:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['document_type_description']; ?></SPAN></TD>
          <TD colspan="2" nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>&nbsp;</SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Related Source Docket:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo ltrim($mfT[0]['source_document_number'],'0'); ?></SPAN></TD>

</TR>
  <!-- other details -->
  
  <TR>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Customer Reference No:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['customer_order_number']; ?></SPAN></TD>
          <TD colspan="1" nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>&nbsp;</SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Doc Quantity:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['cases']; ?></SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Credit Value:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo number_format($mfT[0]['invoice_total'],2); ?></SPAN></TD>

  </TR>
  <TR>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Claim No:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['claim_number']; ?></SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>GRV Number:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['grv_number']; ?></SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Comments:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['delivery_instructions']; ?></SPAN></TD>

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
th.detail { text-align:center; padding:3; margin:0; border:1px solid #999; font-weight:bold; font-size:0.7em; white-space:nowrap;}
td.detail { margin-right: 100px; text-align:right; padding-right:5px; border:1px solid #999; border-bottom-style:solid #999; font-weight:normal; font-size:0.7em; white-space:nowrap; }
</STYLE>
<CENTER>
<TABLE style='border-style:solid; border-width:1; border-color:#CCCCCC; border-bottom-style:solid #999; padding:0; margin:0;' cellpadding=0; cellspacing=0;>
<TR style='padding:0; margin:0;'>
	<TH width="15%" class='detail' colspan="1">Product</TH>
	<TH width="35%" class='detail' colspan="2">Description</TH>
	<TH width="5%" class='detail' colspan="1">Doc<br>Qty</TH>
	<TH width="6%" class='detail' colspan="1">Sell<br>Price</TH>
	<TH width="7%" class='detail' colspan="1">Disc<br>Val</TH>
	<TH width="8%" class='detail' colspan="1">Nett<br>Price</TH>
	<TH width="9%" class='detail' colspan="1">Ext<br>Price</TH>
	<TH width="7%" class='detail' colspan="1">VAT<br>Amnt</TH>
	<TH width="8%" class='detail' colspan="2">Total</TH>
</TR>
<?php
$totalPallets=0;
foreach($mfT as $row) {
	echo "<TR style='padding:2; margin:0;'>
		  <TD class='detail' style='text-align:left' align='left'>&nbsp;{$row['product_code']}</TD>
		  <TD class='detail' colspan=\"2\" style='text-align:left' align='left'>&nbsp;{$row['product_description']}</TD>
		  <TD class='detail' >{$row['document_qty']}&nbsp;&nbsp;</TD>";
	if (!$hasRoleVP) {
		echo "<TD colspan=\"6\" class='detail'>not authorised to view pricing</TD>";
 	} else {
    echo "<TD nowrap class='detail'  style='margin-right: 30px'>".number_format($row['selling_price'],2)."&nbsp;&nbsp;</TD>
          <TD nowrap class='detail'  style='margin-right: 30px'>".number_format($row['discount_value'],2)."&nbsp;&nbsp;</TD>
          <TD nowrap class='detail'>".number_format($row['net_price'],2)."&nbsp;&nbsp;</TD>
          <TD nowrap class='detail'>".number_format($row['extended_price'],2)."&nbsp;&nbsp;</TD>
          <TD nowrap class='detail'>".number_format($row['vat_amount'],2)."&nbsp;&nbsp;</TD>
          <TD nowrap class='detail' colspan=\"2\">".number_format($row['total'],2)."&nbsp;&nbsp;</TD>";
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
		echo "<tr><TD class='detail' colspan=\"9\" nowrap style='text-align:left;'><b>Document Bulk Discounts / Charges</b></TD></tr>";

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
					<TD class='detail' nowrap colspan=\"2\">".number_format($calculatedPrice,2)."</TD>
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
<TH  colspan="4" style='border-top-style:solid #999; align:right;'></TH>
<?php if ($hasRoleVP) { ?>
	<TH colspan="3" style='border-top-style:solid #999;'></TH>
	<TH nowrap class='detail' style= 'text-align:right;'><?php echo number_format($totalEP,2); ?>&nbsp;&nbsp;</TH>
	<TH class='detail' style='text-align:right;'><?php echo number_format($totalVAT,2); ?>&nbsp;&nbsp;</TH>
	<TH class='detail' colspan="2" style= 'text-align:right;'><?php echo number_format($totalINV,2); ?>&nbsp;&nbsp;</TH>
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

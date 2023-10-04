<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");

if (!isset($_SESSION)) session_start() ;
$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$principalName = $_SESSION['principal_name'] ;
$principalAliasName = (($_SESSION['principal_alias_name']=="")?$principalName:$_SESSION['principal_alias_name']);

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

if (isset($_GET['DOCMASTID'])) $postDOCMASTID=mysql_real_escape_string(htmlspecialchars($_GET['DOCMASTID']));
else if (isset($_POST['DOCMASTID'])) $postDOCMASTID=mysql_real_escape_string(htmlspecialchars($_POST['DOCMASTID']));
else $postDOCMASTID="";

include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
$transactionDAO = new TransactionDAO($dbConn);
// this also doubles as the security check because this sql joins on user_principal_depot
$mfT = $transactionDAO->getOrderWithDetailItem($userId, $principalAliasId, $postDOCMASTID); 

if (sizeof($mfT)==0) {
	echo "You do not have access to this information, or order does not exist.";
	return;
}

$adminDAO = new AdministrationDAO($dbConn);
$hasRoleVP = $adminDAO->hasRole($userId, $principalAliasId,ROLE_VIEW_PRICE);


?>
<HTML>
<HEAD>
</HEAD>
<BODY style='width:200px; font-family:Verdana,Arial,Helvetica,sans-serif;'>

<!-- email -->
<CENTER><A style='color:grey; text-align:center; font-size:0.8em;' href='javascript:;' onclick='emailOrder();'>[email order details to self]</A></CENTER> 

<TABLE style='border-style:none; width:100%'>
<TR>
<TD colspan="4" style='text-align:center; background-color:gray; color:white; font-weight:bold; font-size:0.8em;'>Document Information</TD>
</TR>
<TR>
<TD colspan="4" style='text-align:center; background-color:#BBBBBB; color:white; font-weight:bold; font-size:0.8em;'><?php echo $principalAliasName; ?></TD>
</TR>
<TR>
<TD colspan="4" style='text-align:center; background-color:#BBBBBB; color:white; font-weight:bold; font-size:0.8em;'><?php echo $mfT[0]['depot_name']; ?></TD>
</TR>

<!-- doc dates and ref -->
<TR>
	<TD colspan=2 style='border-style:solid; border-width:1; border-color:grey;'><SPAN style='font-weight:normal; font-size:0.5em;'>Customer:</SPAN><BR><SPAN style='font-weight:bold; font-size:1.2em;'><?php echo $mfT[0]['deliver_name']; ?></SPAN></TD>
	<TD colspan=1 style='border-style:solid; border-width:1; border-color:grey;'><SPAN style='font-weight:normal; font-size:0.5em;'>Document Number:</SPAN><BR><SPAN style='font-weight:bold; font-size:1.2em;'><?php echo $mfT[0]['document_number']; ?></SPAN></TD>
</TR>
<TR>
	<TD nowrap style='border-style:solid; border-width:1; border-color:grey;'><SPAN style='font-weight:normal; font-size:0.5em;'>Order Date:</SPAN><BR><SPAN style='font-weight:bold; font-size:0.8em;'><?php echo $mfT[0]['order_date']; ?></SPAN></TD>
	<TD nowrap style='border-style:solid; border-width:1; border-color:grey;'><SPAN style='font-weight:normal; font-size:0.5em;'>Capture Date (GMT):</SPAN><BR><SPAN style='font-weight:bold; font-size:0.8em;'><?php echo $mfT[0]['capturedate']; ?></SPAN></TD>
	<TD nowrap style='border-style:solid; border-width:1; border-color:grey;'><SPAN style='font-weight:normal; font-size:0.5em;'>Delivery Date:</SPAN><BR><SPAN style='font-weight:bold; font-size:0.8em;'><?php echo $mfT[0]['deliverydate']; ?></SPAN></TD>
</TR>
<TR>
	<TD nowrap style='border-style:solid; border-width:1; border-color:grey;'><SPAN style='font-weight:normal; font-size:0.5em;'>Document Type:</SPAN><BR><SPAN style='font-weight:bold; font-size:0.8em;'><?php echo $mfT[0]['document_type_description']; ?></SPAN></TD>
	<TD nowrap style='border-style:solid; border-width:1; border-color:grey;'><SPAN style='font-weight:normal; font-size:0.5em;'>Internal Order Seq:</SPAN><BR><SPAN style='font-weight:bold; font-size:0.8em;'><?php echo $mfT[0]['order_sequence_no']; ?></SPAN></TD>
	<TD nowrap style='border-style:solid; border-width:1; border-color:grey;'><SPAN style='font-weight:normal; font-size:0.5em;'>Customer Reference:</SPAN><BR><SPAN style='font-weight:bold; font-size:0.8em;'><?php echo $mfT[0]['order_number']; ?></SPAN></TD>
</TR>

<TR>
	<TD colspan=3>&nbsp;</TD>
</TR>

<TR>
	<TD style='font-weight:bold; font-size:0.6em;' colspan=4>Delivery Details</TD>
</TR>

<!-- header -->
<TR>
	<TD colspan=2 nowrap style='border-style:solid; border-width:1; border-color:grey;'><SPAN style='font-weight:normal; font-size:0.5em;'>Delivery Address:</SPAN><BR><SPAN style='font-weight:bold; font-size:0.8em;'><?php echo $mfT[0]['deliver_name']."<br>".$mfT[0]['deliver_add1']."<br>".$mfT[0]['deliver_add2']."<br>".$mfT[0]['deliver_add3']; ?></SPAN></TD>
</TR>
<TR>
	<TD colspan=2 nowrap style='border-style:solid; border-width:1; border-color:grey;'><SPAN style='font-weight:normal; font-size:0.5em;'>Delivery Instructions:</SPAN><BR><SPAN style='font-weight:bold; font-size:0.8em;'><?php echo $mfT[0]['delivery_instructions']; ?></SPAN></TD>
</TR>

<TR>
	<TD colspan=3>&nbsp;</TD>
</TR>
<!-- detail -->
<tr cellpadding=0; cellspacing=0;><td style='font-weight:bold; font-size:0.6em;' colspan=4>Order Items</td></tr>
<tr cellpadding=0; cellspacing=0;><td style='font-weight:bold; font-size:0.6em;' colspan=4>* Price has been Overridden</td></tr>
</TABLE>
<STYLE>
th {
	padding:0; margin:0; border-style:solid; border-width:1; border-color:black; font-weight:bold; font-size:0.7em;
}
td.dC {
	padding:0; margin:0; border-style:solid; border-width:1; border-color:grey; font-weight:normal; font-size:0.7em; text-align:right;
}
</STYLE>
<TABLE style='border-style:solid; border-width:1; border-color:black; padding:0; margin:0;' cellpadding=0; cellspacing=0;>
<TR style='padding:0; margin:0;'>
	<TH nowrap colspan=1>Product:</TH>
	<TH nowrap colspan=1>Description:</TH>
	<TH nowrap colspan=1>Quantity</TH>
	<TH nowrap colspan=1>Pallets</TH>
	<TH nowrap colspan=1>List<BR>Price (ea):</TH>
	<TH nowrap colspan=1>Discount<br>Value (ea):</TH>
	<TH nowrap colspan=1>Discount<br>Ref:</TH>
	<TH nowrap colspan=1>Nett<br>Price (ea):</TH>
	<TH nowrap colspan=1>Ext<br>Price:</TH>
</TR>
<?php 
$totQ=0; $totLP=0; $totDV=0; $totCP=0; $totPal=0; $totNett=0;
foreach($mfT as $row) { 
	$nettCP=0;
	echo "<TR style='padding:0; margin:0;'>
			<TD class='dC' nowrap>{$row['product_code']}</TD>
			<TD class='dC' nowrap>{$row['product_description']}</TD>
			<TD class='dC' nowrap>{$row['quantity']}</TD>
			<TD class='dC' nowrap>{$row['pallets']}</TD>";
	
	$totQ+= $row['quantity'];
	$totPal+=$row['pallets'];
	
	if (!$hasRoleVP) {
		echo "<td nowrap colspan=\"8\">not authorised to view pricing</td>";
	} else { 
		if ($row['price_override']=="Y") {
			echo "<TD class='dC' nowrap>".number_format($row['price_override_value'],2)."*</TD>";
			$totLP+= $row['price_override_value'];
		} else {
		    echo "<TD class='dC' nowrap>".number_format($row['list_price'],2)."</TD>";
		    $totLP+= $row['list_price'];
		}
		echo "<TD class='dC' nowrap>".number_format($row['discount_value'],2)."</TD>";
		echo "<TD class='dC' nowrap>{$row['bulk_description']}</TD>";
		if ($row['price_override']=="Y") {
			echo "<TD class='dC' nowrap>".number_format($row['price_override_value'],2)."*</TD>";
			$totCP+= $row['price_override_value'];
			$nettCP=$row['quantity']*$row['price_override_value'];
		} else {
		    echo "<TD class='dC' nowrap>".number_format($row['nett_price'],2)."</TD>";
		    $totCP+= $row['nett_price'];
		    $nettCP=$row['quantity']*$row['nett_price'];
		}
		echo "<TD class='dC' style='text-align:right;'>".number_format($nettCP,2)."</TD>";
		$totDV+=$row['discount_value'];
		$totNett+=$nettCP;
	} 
	
	echo "</TR>";
}
// output the document pricing
$mfDP = $transactionDAO->getOrderPricingDocumentItems($mfT[0]["uid"],DPL_DOCUMENT);
if (sizeof($mfDP)>0) {
	echo "<tr><TD class='dC' colspan=9 nowrap style='text-align:left;'><b>Document Bulk Discounts / Charges</b></TD></tr>";
	$productDAO = new ProductDAO($dbConn);
}
$totalBulkDiscount=0;
foreach ($mfDP as $row) {
	$suffix=($row["deal_type_uid"]==VAL_DEALTYPE_AMOUNT_OFF)?"/On":"";
	$calculatedPrice=$row["discount_value"]*(-1); 
	$discountPrice=$row["discount_value"]; // discount col assumed to be positive, but discounts returned as neg, so switch
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
	$totalBulkDiscount+=floatval($discountPrice);
	echo "<tr>
			<TD class='dC' colspan=4 nowrap style='text-align:left;'>{$row["description"]}{$suffixDesc}</TD>
			<TD class='dC' nowrap>&nbsp;</TD>
			<TD class='dC' nowrap>".number_format($discountPrice,2)."</TD>
			<TD class='dC' nowrap>&nbsp;</TD>
			<TD class='dC' nowrap>&nbsp;</TD>
			<TD class='dC' nowrap>".number_format($calculatedPrice,2)."</TD>
		  </tr>";
		  
	$totNett+=$calculatedPrice;
}
$totDV+=$totalBulkDiscount;
?>

<!-- total line -->
<TR style='padding:0; margin:0;'>
<th colspan="2"></th>
<th nowrap><?php echo $totQ; ?></th>
<th nowrap><?php echo $totPal; ?></th>
<?php if ($hasRoleVP) { ?>
	<th><?php echo number_format($totLP,2); ?></th>
	<th><?php echo number_format($totDV,2); ?></th>
	<th>&nbsp;</th>
	<th><?php echo number_format($totCP,2); ?></th>
	<th style='text-align:right;'><?php echo number_format($totNett,2); ?></th>
<?php } else { ?>
	<th>&nbsp</th>
	<th>&nbsp</th>
	<th>&nbsp</th>
	<th>&nbsp</th>
	<th>&nbsp</th>
<?php } ?>
</TR>

</TABLE>

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
function emailOrder() {
	var params="?USERID=<?php echo $userId; ?>&OBJECTID=<?php echo EO_ORDER_CARD; ?>&SUBJECT=Order Details as per Request: <?php echo $postDOCMASTID; ?>&DOCMASTID=<?php echo $postDOCMASTID; ?>";
	window.location='<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/emailUserHTML.php'+params;
}						
</script>
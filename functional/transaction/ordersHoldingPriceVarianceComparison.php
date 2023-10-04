<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

if (isset($_POST['PROCESSEDDATE'])) $postPROCESSEDDATE=mysqli_real_escape_string($dbConn->connection, $_POST['PROCESSEDDATE']); else $postPROCESSEDDATE="";

if (!isset($_SESSION)) session_start() ;
$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;

include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
$transactionDAO = new TransactionDAO($dbConn);


$adminDAO = new AdministrationDAO($dbConn);
$hasRoleTT = $adminDAO->hasRole($userId,$principalId,ROLE_TRANSACTION_TRACKING);
if (!$hasRoleTT) {
	echo "Sorry, you do not have permissions to VIEW TRACKING!";
	return;
}
$hasRoleVP = $adminDAO->hasRole($userId, $principalId,ROLE_VIEW_PRICE);
if (!$hasRoleVP) {
	echo "Sorry, you do not have permissions to do a price comparison!";
	return;
}

echo "<html>
		<head>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.js'></script>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js'></script>
		  <meta name='SKYPE_TOOLBAR' content='SKYPE_TOOLBAR_PARSER_COMPATIBLE' />
	      <link href='".$ROOT.$PHPFOLDER."css/default.css' rel='stylesheet' type='text/css'>";
		  DatePickerElement::getDatePickerLibs();
	      
	// autoscroll start
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.autoscroll.js'></script>";
	echo "<scr"."ipt type=\"text/javascript\">";
	echo "\$(document).ready(function(){ document.body.focus(); \$.autoscroll.init({step: 200}); });";
	echo "</scr"."ipt>";
	// autoscroll end
	
	echo "</head><body><center>";

// START : Params
$rowStyle="even";
echo "<form id='pForm' action='".$_SERVER["PHP_SELF"]."' method='post'>
	  <table>
	  <tr class='".GUICommonUtils::styleEO($rowStyle)."'>
		<td>Processed Date:</td>
		<td>";
			DatePickerElement::getDatePicker("PROCESSEDDATE",($postPROCESSEDDATE=="")?CommonUtils::getUserDate(0):$postPROCESSEDDATE);
echo "  </td>
      </tr>
	  <tr class='".GUICommonUtils::styleEO($rowStyle)."'>
		<td colspan=2 style='text-align:center'>
		<input type='submit' class='submit' value='Submit' onclick='submitParams();' />
					<script type='text/javascript' defer>
						function submitParams() {
							document.pForm.submit();
						}
					</script>
		</td>
	  </tr>
	  </table>
	  </form>";
// END : Params


if (empty($postPROCESSEDDATE)) {
	echo "Please supply a processed date";
	return;
}


$docs=$transactionDAO->getPriceVarianceComparisonList($principalId, $postPROCESSEDDATE);
$dbConn->dbClose();

if (sizeof($docs)==0) {
	echo "No Documents found for this Processed Date: <b>{$postPROCESSEDDATE}</b>";
	return;
}


$break="";
$rowStyle="even";
echo "<style>
		th.ohe,td.ohe { font-size:11px; font-family: calibri; white-space:nowrap; }
		.divider { border-left:1px solid;}
		.priceDiff { background: #adb26b;}
		th.ohe { border:1px; border-style:solid; border-color:#DDDDDD; }
	  </style>";

// key
echo "<table class='tblReset' style='border:0;'><tr><td><div class='priceDiff' style='width:30px; height:15px;'></div></td><td>This line has a price difference</td></tr></table><br>";
	  
echo "<table class='ohe' cellspacing=0>
	  <tr class='".GUICommonUtils::styleEO($rowStyle)."'>
		<th class='ohe' colspan=7>Document Details</th>
		<th class='ohe' colspan=7>Pricing as Processed</th>
		<th class='ohe' colspan=7>Pricing supplied by EDI</th>
	  </tr>
	  <tr class='".GUICommonUtils::styleEO($rowStyle)."'>
		<th class='ohe'>Reference</th>
		<th class='ohe'>Document Type</th>
		<th class='ohe'>Processed Date</th>
		<th class='ohe'>Document Number</th>
		<th class='ohe'>Store Name</th>
		<th class='ohe'>Depot Name</th>
		<th class='ohe'>Product Description</th>
		<th class='ohe divider'>List Price</th>
		<th class='ohe'>Discount Value</th>
		<th class='ohe'>Nett Price</th>
		<th class='ohe'>Ext Price</th>
		<th class='ohe'>VAT Rate</th>
		<th class='ohe'>VAT Amount</th>
		<th class='ohe'>Total Price</th>
		<th class='ohe divider'>List Price</th>
		<th class='ohe'>Discount Value</th>
		<th class='ohe'>Nett Price</th>
		<th class='ohe'>Ext Price</th>
		<th class='ohe'>VAT Rate</th>
		<th class='ohe'>VAT Amount</th>
		<th class='ohe'>Total Price</th>
	 </tr>";
$break="";
foreach ($docs as $r) {
	if ($break!=$r["reference"]) {
		$rowStyle=GUICommonUtils::styleEO($rowStyle);
	}
	// detail
	echo "<tr class='".(($r["price_diff_notified"]!="")?" priceDiff ":$rowStyle)."'>";
	if ($break!=$r["reference"]) {
		echo "	<td class='ohe'>{$r["reference"]}</td>
				<td class='ohe'>{$r["document_type"]}</td>
				<td class='ohe'>{$r["processed_date"]}</td>
				<td class='ohe'>{$r["document_number"]}</td>
				<td class='ohe'>{$r["store_name"]}</td>
				<td class='ohe'>{$r["depot_name"]}</td>";
		$break=$r["reference"];
	} else {
		echo "	<td class='ohe' colspan=6></td>";
	}
	echo "	<td class='ohe'>{$r["product_description"]}</td>";
	
	// processed info
	if ($r["list_price"]=="") {
		echo "<td class='ohe divider' colspan=7>[Please wait: Transaction has not yet appeared in Tracking Transaction]</td>";
	} else {
		echo "<td class='ohe divider'>{$r["list_price"]}</td>
			  <td class='ohe'>{$r["discount_value"]}</td>
			  <td class='ohe'>{$r["nett_price"]}</td>
			  <td class='ohe'>{$r["ext_price"]}</td>
			  <td class='ohe'>{$r["vat_rate"]}</td>
			  <td class='ohe'>{$r["vat_amount"]}</td>
			  <td class='ohe'>{$r["total_price"]}</td>";
	}
	
	// edi info
	echo "	<td class='ohe divider'>{$r["edi_list_price"]}</td>
			<td class='ohe'>{$r["edi_discount_value"]}</td>
			<td class='ohe'>{$r["edi_nett_price"]}</td>
			<td class='ohe'>{$r["edi_ext_price"]}</td>
			<td class='ohe'>{$r["edi_vat_rate"]}</td>
			<td class='ohe'>{$r["edi_vat_amount"]}</td>
			<td class='ohe'>{$r["edi_total_price"]}</td>
			<tr>";
}
echo "</table>";


echo "</center></body></html>";
?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();


$postREFERENCE = (isset($_GET['REFERENCE'])) ? trim(htmlspecialchars($_GET['REFERENCE'])) : "";

if (empty($postREFERENCE)) {
	echo "No Reference Passed. Could not retrieve Document";
	return;
}

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


$docs=$transactionDAO->getElectronicExceptions($principalId, $reference=$postREFERENCE);
$dbConn->dbClose();

if (sizeof($docs)==0) {
	echo "No Documents found for this reference: <b>{$postREFERENCE}</b>";
	return;
}

echo "<html>
		<head>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.js'></script>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js'></script>
		  <meta name='SKYPE_TOOLBAR' content='SKYPE_TOOLBAR_PARSER_COMPATIBLE' />
	      <link href='".$ROOT.$PHPFOLDER."css/default.css' rel='stylesheet' type='text/css'>";

	// autoscroll start
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.autoscroll.js'></script>";
	echo "<scr"."ipt type=\"text/javascript\">";
	echo "\$(document).ready(function(){ document.body.focus(); \$.autoscroll.init({step: 200}); });";
	echo "</scr"."ipt>";
	// autoscroll end
echo "<style>
		th.ohe,td.ohe { font-size:12px; white-space:nowrap; }
		th.ohe { border:1px; border-style:solid; border-color:#DDDDDD; }
		.oheExceptionHeader td {border-top: 5px double #87CEFA;}
		.oheExceptionDetail{ background-color:#F7F8E0; }
		.oheExceptionDetail th{ background-color:#F2F5A9; }
		.oheExceptionDetail td, .oheExceptionDetail th {border:1px; border-style:solid; border-color:#F5DA81}
	  </style>";
	echo "</head><body>";


	$cn = array();
	$cDocN = array();
	$refN = array();
	foreach($docs as $r){
	  $cn[$r['oh_uid']] = 1; //array built using oh_uid = for total count of found documents
	  if (strpos(trim(strtoupper($r["client_document_number"])),strtoupper(trim($postREFERENCE)))!==false) {
	    $cDocN[$r['oh_uid']] = 1;
	  }
	  if (strpos(trim(strtoupper($r["reference"])),strtoupper(trim($postREFERENCE)))!==false) {
	    $refN[$r['oh_uid']] = 1;
	  }
	}
	$totalResults = count($cn);
	$totalcDocN = count($cDocN);
	$totalrefN = count($refN);

	echo "<br><h3 style='color:#047;'><i>Your search for '{$postREFERENCE}' returned {$totalResults} result(s).</i></h3><br>Warning: This search implements `partial matching` so check against full value for appropriate reference or document number.";
	echo "<h3>{$totalrefN} - Reference(s) matched</h3>";
	buildCardTable(array_keys($refN));

	echo "<br><h3>{$totalcDocN} - Client Document(s) matched</h3>";

	buildCardTable(array_keys($cDocN));

	echo '<br><br>';


function buildCardTable($UidToDisplay){

  global $docs, $hasRoleVP;

  $break="";
  $rowStyle="even";

echo "<table class='ohe' cellspacing=0>
	  <tr class='".GUICommonUtils::styleEO($rowStyle)."'>
		<th class='ohe'>UId</th>
		<th class='ohe'>Vendor Source</th>
		<th class='ohe'>Created Date</th>
		<th class='ohe'>Capture Date</th>
		<th class='ohe'>Order Date</th>
		<th class='ohe'>Data Source</th>
		<th class='ohe'>Incoming Ref</th>
		<th class='ohe'>Client Document</th>
		<th class='ohe'>Reference</th>
		<th class='ohe'>General Reference 1</th>
		<th class='ohe'>General Reference 2</th>
		<th class='ohe'>Delivery Instructions</th>
		<th class='ohe'>Document Type</th>
		<th class='ohe'>RT Store</th>
		<th class='ohe'>Ship To GLN</th>
		<th class='ohe'>Vendor Store Name</th>
		<th class='ohe'>Debtors Store Identifier</th>
		<th class='ohe'>Sales Agent Store Identifier</th>
		<th class='ohe'>Store Lookup Ref</th>
		<th class='ohe'>Chain Lookup Ref</th>
		<th class='ohe'>Depot Lookup Ref</th>
		<th class='ohe'>Status</th>
	 </tr>";
foreach ($docs as $n => $r) {

    if (!in_array($r["oh_uid"],$UidToDisplay))
      continue;

	// header change
	if ($r["oh_uid"]!=$break) {
          GUICommonUtils::styleEO($rowStyle);

          // get the error status description(s)
          $errorArr=array_unique(explode(",",$r["oh_status"]));
          $errMsg=($r["status_msg"]!="")?"{$r["status_msg"]}<br>":"";
          foreach ($errorArr as $e) {
            if ($e!=""){
              if($e == 'D') $errMsg.= GUICommonUtils::translateOHExceptionStatus($e) . ' - ' . $r['deleted_by_user'] . "<br>";
              else
              $errMsg.=GUICommonUtils::translateOHExceptionStatus($e)."<br>";
            }
          }

        if($n != 0)
          echo "<tr><td colspan='20' style='height:10px;'></td></tr>";

		echo "<tr " , (in_array('D',$errorArr))?("style='background:#F5A9A9'"):('class="odd oheExceptionHeader"') , ">
				<td class='ohe'>{$r["oh_uid"]}</td>
				<td class='ohe'>{$r["vendor_name"]}</td>
				<td class='ohe'>{$r["created_date"]}</td>
				<td class='ohe'>{$r["capture_date"]}</td>
				<td class='ohe'>{$r["order_date"]}</td>
				<td class='ohe'>{$r["data_source"]}</td>
				<td class='ohe'>{$r["incoming_ref"]}</td>
				<td class='ohe'>{$r["client_document_number"]}</td>
				<td class='ohe'>{$r["reference"]}</td>
				<td class='ohe'>{$r["general_reference_1"]}</td>
				<td class='ohe'>{$r["general_reference_2"]}</td>
				<td class='ohe'>{$r["delivery_instructions"]}</td>
				<td class='ohe'>{$r["document_type"]}</td>
				<td class='ohe'>{$r["deliver_name"]}</td>
				<td class='ohe'>{$r["ship_to_gln"]}</td>
				<td class='ohe'>{$r["ship_to_name"]}</td>
				<td class='ohe'>{$r["debtors_store_identifier"]}</td>
				<td class='ohe'>{$r["sales_agent_store_identifier"]}</td>
				<td class='ohe'>{$r["store_lookup_ref"]}</td>
				<td class='ohe'>{$r["chain_lookup_ref"]}</td>
				<td class='ohe'>{$r["depot_lookup_ref"]}</td>
				<td class='ohe'>{$errMsg}</td>
			 </tr>
			 <tr class='tableReset oheExceptionDetail'>
				<th class='ohe'>Client Line No</th>
				<th class='ohe'>Client page No</th>
				<th class='ohe'>RT Product</th>
				<th class='ohe'>Quantity</th>
				<th class='ohe'>List Price</th>
				<th class='ohe'>Discount Value</th>
				<th class='ohe'>Nett Price</th>
				<th class='ohe'>Ext Price</th>
				<th class='ohe'>Vat Rate</th>
				<th class='ohe'>VAT Amount</th>
				<th class='ohe'>Total Price</th>
				<th class='ohe'>Price Variance</th>
				<th class='ohe'>Vendor Product Name</th>
				<th class='ohe'>GTIN</th>
				<th class='ohe'>Product Code</th>
				<th class='ohe' colspan=8>Status</th>
			 </tr>";

		$break=$r["oh_uid"];
	}
	// detail
	echo "<tr class='tableReset oheExceptionDetail' " , ($r["status_dtl"] == 'D')?("style='background:#F5A9A9'"):('') , ">
			<td class='ohe'>{$r["client_line_no"]}</td>
			<td class='ohe'>{$r["client_page_no"]}</td>
			<td class='ohe'>{$r["product_description"]}</td>
			<td class='ohe'>{$r["quantity"]}</td>";
	if ($hasRoleVP===true) {
			echo "<td class='ohe'>{$r["list_price"]}</td>
					<td class='ohe'>{$r["discount_value"]}</td>
					<td class='ohe'>{$r["nett_price"]}</td>
					<td class='ohe'>{$r["ext_price"]}</td>
					<td class='ohe'>{$r["vat_rate"]}</td>
					<td class='ohe'>{$r["vat_amount"]}</td>
					<td class='ohe'>{$r["total_price"]}</td>";
	} else {
			echo "<td class='ohe' colspan='7'>You do not have permissions to view pricing</td>";
	}
	echo "	<td class='ohe'>{$r["price_diff_notified"]}</td>
			<td class='ohe'>{$r["product_name"]}</td>
			<td class='ohe'>{$r["product_gtin"]}</td>
			<td class='ohe'>{$r["product_code"]}</td>";

	$errorArr=array_unique(explode(",",$r["ohd_status"]));
	$errMsg="";
	foreach ($errorArr as $e) {
		if ($e!="") $errMsg.=GUICommonUtils::translateOHExceptionStatus($e)."<br>";
	}

	echo 	"<td class='ohe' colspan=7>{$errMsg}</td>
		 </tr>";

}
echo "</table>";

}

echo "</body></html>";
?>
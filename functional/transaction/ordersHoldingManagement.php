<?php

	include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	require($ROOT.$PHPFOLDER."functional/main/access_control.php");
	include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
	include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
	include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
	include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
	include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
	include_once($ROOT.$PHPFOLDER.'elements/intelliDDElement.php');
	include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
	include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");


	if (!isset($_SESSION)) session_start();
	$principalId  = $_SESSION['principal_id'];
	$userId       = $_SESSION['user_id'];

	$dbConn = new dbConnect();
	$dbConn->dbConnection();

	$transactionDAO = new TransactionDAO($dbConn);
	$adminDAO = new AdministrationDAO($dbConn);
	$hasRole = $adminDAO->hasRole($userId,$principalId,ROLE_ORDERS_HOLDING_EXCEPTIONS);
	if (!$hasRole) {
		echo "Sorry, you do not have permissions to Manage Exceptions!";
		return;
	}

	//if (isset($_GET["FILTERLIST"])) { $postFilterList=$_GET["FILTERLIST"]; $postFilterList=explode(',',$postFilterList); } else $postFilterList="";
	//if (isset($_GET["FROMDATE"])) $postFROMDATE=mysql_real_escape_string(htmlspecialchars($_GET["FROMDATE"])); else $postFROMDATE="";
	//if (isset($_GET["TODATE"])) mysql_real_escape_string(htmlspecialchars($postTODATE=$_GET["TODATE"])); else $postTODATE="";
	//if (isset($_GET["DATETYPE"])) mysql_real_escape_string(htmlspecialchars($postDATETYPE=$_GET["DATETYPE"])); else $postDATETYPE="";

	echo "<html><head>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.js'></script>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js'></script>
		  <meta name='SKYPE_TOOLBAR' content='SKYPE_TOOLBAR_PARSER_COMPATIBLE' />
	      <link href='".$ROOT.$PHPFOLDER."css/1_default.css' rel='stylesheet' type='text/css'>
	      <link href='".$ROOT.$PHPFOLDER."css/uiicon.css' rel='stylesheet' type='text/css'>";








	// autoscroll start
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.autoscroll.js'></script>";
	echo "<scr"."ipt type=\"text/javascript\">";
	echo "\$(document).ready(function(){ document.body.focus(); \$.autoscroll.init({step: 200}); });";
	echo "</scr"."ipt>";
	// autoscroll end

	echo "</head>";
	echo "<body>";

	// search instead
	echo "<div align='center'><div style='display:block;width:80%; padding:5px 20px; background-color:#f8f6d0;text-align:left;'>
			Lookup Successfully Processed documents on Reference & Client Document : <input id='fldReference' size='15' />
			<input type='submit' class='submit' onclick='window.open(\"".$ROOT.$PHPFOLDER."functional/transaction/ordersHoldingCard.php?REFERENCE=\"+document.getElementById(\"fldReference\").value,\"myOH\",\"scrollbars=yes,resizable=yes,width=400,height=550\");' value='Submit'/>
		  </div></div><br>";


	echo "<center>";

	// toolbar
	echo "<table >
		  <tr><th colspan=2 style='text-align:center; color:".COLOR_UNOBTRUSIVE_INFO."'>Use this toolbar to set the values for the exceptions listed below...<br>Clicking on the action button next to each exception will apply this value</th></tr>
		  <tr><td>Store: </td><td align='right'>";


	//----------------------------------------------------
	//STORE SEARCH
	//----------------------------------------------------

	echo "<div style='float:right;display:block;'>";
	echo "<INPUT TYPE='hidden' id=\"oheSTORE\" name=\"oheSTORE\" >";

	//special_field_or & ean_code_or means search on these fields - store name is always enabled for search.
	//ean_code = display column	ean code.
	//Header Name ie: store_name = Store Name  : ucword and remove dashes.
	//columns are displayed in order of below - if uid is set to true will be col 1 by default.
	$columnsArr = array('store_name','depot_name','delivery_day','special_fields','special_field_or');

	IntelliDDElement::selectStoreSearch("oheSTORE", $columnsArr, false, '', "store_name + ' -- ' + depot_name + ((delivery_day!='Not Known')?(\",\"+delivery_day):(''))", true);

    echo "</div>";

	//----------------------------------------------------


	//IntelliDDElement::displayStoreIDD("oheSTORE","","N","N",null,null,null,$dbConn,$principalId,$userId,"");

	echo "<div style='clear:both;'></div></td></tr>
			<tr><td></td></tr>
		  <tr><td>Product: </td><td>";

	IntelliDDElement::displayProductIDD("ohePRODUCT","","N","N",null,null,null,$dbConn,$principalId,$userId,"");

	echo "</td></tr>";

	echo "</table><br><br>";

	// exceptions
	$eArr = $transactionDAO->getElectronicExceptions($principalId);

	$break="";
	$rowStyle="even";
	echo "<style>
			th.ohe,td.ohe { font-size:12px; white-space:nowrap; }
			th.ohe { border:1px; border-style:solid; border-color:lightSkyBlue; }
			.oheException { display:none; }
			.oheExceptionHeader {margin:5px 0px 5px 0px;background-color:#CEF6CE}
			.oheExceptionHeader td.ohe {background:#CEF6CE;}
			.oheExceptionHeader td {border:1px; border-style:solid; border-color:#01DF01;background-color:#E0F8E0;white-space:nowrap;}
			.oheExceptionDetail{ background-color:#F7F8E0; }
			.oheExceptionDetail th{ background-color:#F2F5A9; }
			.oheExceptionDetail td, .oheExceptionDetail th {border:1px; border-style:solid; border-color:#F5DA81}
			.actToolbarH,.actToolbarD{padding:4px 7px 1px 6px;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;background:#fff;}
			.actToolbarH img,.actToolbarD img{border:0px;}
			.actToolbarH a,.actToolbarD a{outline:0px}
			.actToolbarH {border:1px solid lightSkyBlue;}
			.actToolbarD {border:1px solid #FFBF00;}
			.ordheadRow td{border-bottom:1px solid lightSkyBlue;}
			table a {color:blue;}
			table a:hover{color:red;}
		  </style>";
	echo "<table class='ohe' cellspacing=0>
		  <tr class='".GUICommonUtils::styleEO($rowStyle)."'>
			<th class='ohe'></th>
			<th class='ohe'>UId</th>
			<th class='ohe'>Document Type</th>
			<th class='ohe'>Order Date</th>
			<th class='ohe'>Reference</th>
			<th class='ohe'>Client Document</th>
			<th class='ohe'>Incoming Ref</th>
			<th class='ohe'>Vendor<br>Source</th>
			<th class='ohe'>Data<br>Source</th>
			<th class='ohe'>Delivery<br>Instructions</th>
			<th class='ohe'>Status</th>
		 </tr>";

	$no = 0;
	$rowNo = 0;
    $rowStyle = 'even';


	foreach ($eArr as $r) {


		// header change
		if ($r["oh_uid"]!=$break) {

			// get the error status description(s)
			$errorArr=array_unique(explode(",",$r["oh_status"]));
			$errMsg=($r["status_msg"]!="")?"{$r["status_msg"]}<br>":"";
			foreach ($errorArr as $e) {
				if ($e!="") $errMsg.=GUICommonUtils::translateOHExceptionStatus($e)."<br>";
			}

			if($break != ''){
			  echo '<tr class="',GUICommonUtils::styleEO($rowStyle),'"><td colSpan="23"></td></tr>';
			}
			GUICommonUtils::styleEO($rowStyle);

			// NOTE: the onKeyPress does not catch backspace, enter or del key. Use onkeydown.
			echo "<tr class='{$rowStyle} rtorder_{$r["oh_uid"]} ordheadRow'>
					<td class='ohe'>
					<div class='actToolbarH'>
						<a href='javascript:;' onclick='showDetail({$r["oh_uid"]});' title='Show Document Detail'><img id='img{$r["oh_uid"]}' src='{$ROOT}{$PHPFOLDER}images/down.jpg' onclick='javascript:;'></a>&nbsp;
						<a href='javascript:;' onclick='setStore({$r["oh_uid"]});' title='Set store to toolbar value'><img src='{$DHTMLROOT}{$PHPFOLDER}images/s-icon-small.png' alt='Set store to toolbar value'/></a>&nbsp;
						<a href='javascript:;' onclick='forceDocument({$r["oh_uid"]});' title='Bypass the Unique Order Reference check'><img src='{$DHTMLROOT}{$PHPFOLDER}images/force_icon.png' alt='Bypass the Unique Order Reference check'/></a>&nbsp;".
                                                (($r["oh_status"]=="R.A" || $r["oh_status"]=="R.A.MP")?"<A id='approveId_{$r["oh_uid"]}' href='javascript:;' onclick='approveDocument({$r["oh_uid"]});' title='Approve (release) the Document for processing'><img src='{$DHTMLROOT}{$PHPFOLDER}images/approve_16x16.gif' alt='Approve (release) the Document for processing'  /></a>":"")."
						<A href='javascript:;' onclick='deleteDocument({$r["oh_uid"]});' title='Delete whole Document'><img src='{$DHTMLROOT}{$PHPFOLDER}images/trash_16x16.gif' alt='Delete whole Document'  /></a>
						<A href='javascript:;' onclick='window.open(\"".$ROOT.$PHPFOLDER."functional/transaction/ordersHoldingProformaCard.php?OHUID={$r["oh_uid"]}\",\"myOH\",\"scrollbars=yes,resizable=yes,width=1024,height=768\");' title='Print ProForma'><img src='{$DHTMLROOT}{$PHPFOLDER}images/print-icon-16x16.png' alt='Print Proforma'  /></a>
					</div>
					</td>
					<td class='ohe'><small>{$r["oh_uid"]}</small></td>
					<td class='ohe'>{$r["document_type"]}</td>
					<td class='ohe' id='orderdateId_{$r["oh_uid"]}'>{$r["order_date"]}" . ((strtotime($r["order_date"]) < strtotime('-30 days'))?(" <a href='javascript:;' onclick='setOrderDate({$r["oh_uid"]},\"".gmdate('Y-m-d')."\");'>[today]</a>"):(""))."</td>
					<td class='ohe' style='display:inline-block;white-space:nowrap;'><input id='refId_{$r["oh_uid"]}' value='{$r["reference"]}' onkeydown='changeIconState(\"saveicon_{$r["oh_uid"]}\",\"on\");' />&nbsp;<a href='javascript:;' onclick='setReference({$r["oh_uid"]});' title='Save Changes to Reference' style='white-space:nowrap;display:inline-block;' ><span id='saveicon_{$r["oh_uid"]}' class='ICONx24' style='background-image:url(\"{$DHTMLROOT}{$PHPFOLDER}images/save24.png\"); width:24px; height:24px; background-repeat:no-repeat;display:table-cell; white-space:nowrap;'>&nbsp;</span></a></td>
					<td class='ohe'>{$r["client_document_number"]}</td>
					<td class='ohe'>{$r["incoming_ref"]}</td>
					<td class='ohe'>{$r["vendor_name"]}</td>
					<td class='ohe'>{$r["data_source"]}</td>
					<td class='ohe'>{$r["delivery_instructions"]}</td>

					<td class='ohe' style='white-space:normal'><div style='width:400px;color:#DF0101;'>{$errMsg}</div></td>
				 </tr>

				 <tr class='oheException rtorder_{$r["oh_uid"]}'>

				 <td colSpan='23'><div class='oheException oheException{$r["oh_uid"]}'>

				 <table class='tableReset oheExceptionHeader' width='100%' style='font-size: 11px;'>
					<tr>
				 		<td class='ohe'>General Reference 1</td><td>{$r["general_reference_1"]}</td>
				 		<td class='ohe'>RT Store</td><td width='150' id='rtstore_{$r["oh_uid"]}'>" , ($r["psm_uid"]!='') ? ('<a href="'.$DHTMLROOT.$PHPFOLDER.'functional/stores/storeCard.php?PRINCIPALSTOREUID='.$r["psm_uid"].'&MODIFY=1" target="_blank" title="Show Store" style="color:#047">' . $r["deliver_name"] . '</a>') : ($r["deliver_name"]) , "</td>
				 		<td class='ohe'>Store Lookup Ref</td><td width='150'>{$r["store_lookup_ref"]}</td>
				 	</tr><tr>
				 		<td class='ohe'>General Reference 2</td><td>{$r["general_reference_2"]}</td>
				 		<td class='ohe'>Ship To GLN</td><td>" , ($r["ship_to_gln"]!='') ? ('<a href="'.$DHTMLROOT.$PHPFOLDER.'functional/stores/storeCard.php?STOREGLN='.$r["ship_to_gln"].'&MODIFY=1" target="_blank" title="Show Store" style="color:#047">' . $r["ship_to_gln"] . '</a>') : ('') , "</td>
				 		<td class='ohe'>Chain Lookup Ref</td><td>{$r["chain_lookup_ref"]}</td>
				 	</tr><tr>
				 		<td class='ohe'>Created Date</td><td>{$r["created_date"]}</td>
				 		<td class='ohe'>Vendor Store Name</td><td>" , ($r["ship_to_name"]!='') ? ('<a href="'.$DHTMLROOT.$PHPFOLDER.'home.php?m_id=15&tab_id=47&param=DELNAME:'.$r["ship_to_name"].'" target="_blank" title="Show Store" style="color:#047">' . $r["ship_to_name"] . '</a>') : ('') , "</td>
				 		<td class='ohe'>Depot Lookup Ref</td><td>{$r["depot_lookup_ref"]}</td>
				 	</tr><tr>
				 		<td class='ohe'>Capture Date</td><td>{$r["capture_date"]}</td>
						<td class='ohe'>Debtors Store Identifier</td><td>{$r["debtors_store_identifier"]}</td>
						<td class='ohe'>Sales Agent Store Identifier</td><td>{$r["sales_agent_store_identifier"]}</td>
				 	</tr><tr>
                <td class='ohe'>Delivery Date</td><td>{$r["delivery_date"]}</td>
                <td class='ohe'>Delivery Day</td><td>".GUICommonUtils::translateDeliveryDay($r["delivery_day_uid"])."</td>
				 	      <td class='ohe'>Disabled Duplication Check</td><td id='forceId_{$r["oh_uid"]}'>{$r["force_skip_unique_order_no"]}</td>
				 	</tr><tr>
                <td class='ohe'>Depot Override</td><td style='display:inline-block;white-space:nowrap;width:10%;padding-right:20px;'>"; // the td width here is only to stop the browser putting loads of extra spacings after the select element
			          BasicSelectElement::getUserDepotsForPrincipalDD("f_dptOverride_{$r["oh_uid"]}", $r["depot_uid"], "N", "N", "changeIconState('saveiconDpt_{$r["oh_uid"]}','on');", "", "", $dbConn, $userId, $principalId,$returnAsString=false);
echo "          &nbsp;<a href='javascript:;' onclick='setDepot({$r["oh_uid"]});' title='Save Changes to Depot' style='white-space:nowrap;display:inline-block;' ><span id='saveiconDpt_{$r["oh_uid"]}' class='ICONx24' style='background-image:url(\"{$DHTMLROOT}{$PHPFOLDER}images/save24.png\"); width:24px; height:24px; background-repeat:no-repeat;display:table-cell; white-space:nowrap;'>&nbsp;</span></a>
                </td>
                <td class='ohe'>Expiry Date</td><td>{$r["expiry_date"]}</td>
                <td class='ohe' colspan='2'>&#160;</td>
				 	</tr>
				 </table>

				 <table class='tableReset oheExceptionDetail' width='100%'><tr>
					<th class='ohe'></th>
					<th class='ohe'>Override<br>Pricing</th>
					<th class='ohe'>Line<br>No</th>
					<th class='ohe'>Page<br>No</th>
					<th class='ohe'>RT Product</th>
					<th class='ohe'>Stock<br>Available</th>
					<th class='ohe'>SKU Gtin</th>
					<th class='ohe'>Outer/C Gtin</th>
					<th class='ohe'>Quantity</th>
					<th class='ohe'>Amended Quantity</th>
					<th class='ohe'>List<br>Price</th>
					<th class='ohe'>Discount<br>Value</th>
					<th class='ohe'>Nett<br>Price</th>
					<th class='ohe'>Ext<br>Price</th>
					<th class='ohe'>Vat<br>Rate</th>
					<th class='ohe'>VAT<Br>Amount</th>
					<th class='ohe'>Total<br>Price</th>
					<th class='ohe' colspan=8>Status</th>
				 </tr>";

			$break=$r["oh_uid"];
			$no++;
		}
		// detail
		switch ($r["override_price_type"]) {
			case PCA_USE_OWN: {
				$overridePriceTypeDesc="Force RT Pricing";
				break;
			}
			case PCA_USE_VENDOR: {
				$overridePriceTypeDesc="Force EDI Pricing";
				break;
			}
			default: {
				$overridePriceTypeDesc="";
			}
		}
		echo "<tr class='even' id='drow_{$rowNo}'>
				<td class='ohe' width='135'>
				<div class='actToolbarD'>
					<A href='javascript:;' onclick='setProduct({$r["ohd_uid"]},{$rowNo});' title='Set product to toolbar value'><img src='{$DHTMLROOT}{$PHPFOLDER}images/p-icon-small.png' alt='Set product to toolbar value' /></a>
					<A href='javascript:;' onclick='setOverridePriceType({$r["ohd_uid"]},".PCA_USE_OWN.");'  title='Force order to use RT pricing'><img src='{$DHTMLROOT}{$PHPFOLDER}images/price-rt-icon-small.png' alt='Force order to use RT pricing' /></a>
					<A href='javascript:;' onclick='setOverridePriceType({$r["ohd_uid"]},".PCA_USE_VENDOR.");'  title='Force order to use EDI pricing'><img src='{$DHTMLROOT}{$PHPFOLDER}images/price-edi-icon-small.png' alt='Force order to use EDI pricing' /></a>
					<A href='javascript:;' onclick='deleteRow({$r["ohd_uid"]},{$rowNo});'  title='Delete Line'><img src='{$DHTMLROOT}{$PHPFOLDER}images/trash_16x16.gif' alt='Delete Line' /></a>
				</div>
				<a href='".$DHTMLROOT.$PHPFOLDER."functional/deals/dealCard.php?STOREUID=" . $r['psm_uid'] . "&PRODUCTUID=" . $r['product_uid'] . "&DISCLAIMER' target='_blank' title='View Pricing on RT System' style='margin-top:6px;display:block;color:#047;text-align:center;'>View RT Pricing</a>
				</td>
				<td class='ohe'>{$overridePriceTypeDesc}</td>
				<td class='ohe'>{$r["client_line_no"]}</td>
				<td class='ohe'>{$r["client_page_no"]}</td>
				<td class='ohe'>
					<span id='rtproduct_{$rowNo}'>", ($r["product_description"]!='')?($r["product_description"]):('?') , "</span>
					<small><div style='margin:4px 0px;color:#088A08;border-top:1px solid #01DF01;'>
		              {$r["product_name"]} <br> {$r["product_gtin"]} " , ($r["product_code"]!='') ? (' | ' . $r["product_code"]) : ('') , "
		            </div></small>
				</td>
				<td class='ohe'>{$r["stock_available"]}</td>
				<td class='ohe'>{$r["product_sku_gtin"]}</td>
				<td class='ohe'>{$r["product_gtin"]}</td>
				<td class='ohe'>{$r["quantity"]}</td>
				<td class='ohe' style='display:inline-block;white-space:nowrap;width:1%'><input id='f_amendedQtyId_{$r["ohd_uid"]}' size='5' maxlength='5' value='{$r["amended_quantity"]}' onkeydown='changeIconState(\"saveiconAmendedQty_{$r["ohd_uid"]}\",\"on\");' />&nbsp;<a href='javascript:;' onclick='setAmendedQuantity({$r["ohd_uid"]});' title='Save Changes to Amended Quantity' style='white-space:nowrap;display:inline-block;' ><span id='saveiconAmendedQty_{$r["ohd_uid"]}' class='ICONx24' style='background-image:url(\"{$DHTMLROOT}{$PHPFOLDER}images/save24.png\"); width:24px; height:24px; background-repeat:no-repeat;display:table-cell; white-space:nowrap;'>&nbsp;</span></a></td>
				<td class='ohe'>{$r["list_price"]}</td>
				<td class='ohe'>{$r["discount_value"]}</td>
				<td class='ohe'>{$r["nett_price"]}</td>
				<td class='ohe'>{$r["ext_price"]}</td>
				<td class='ohe'>{$r["vat_rate"]}</td>
				<td class='ohe'>{$r["vat_amount"]}</td>
				<td class='ohe'>{$r["total_price"]}</td>";

		$errorArr=array_unique(explode(",",$r["ohd_status"]));
		$errMsg="";
		foreach ($errorArr as $e) {
			if ($e!="") $errMsg.=GUICommonUtils::translateOHExceptionStatus($e)."<br>";
		}

		echo 	"<td class='ohe' colspan=7 style='color:#DF0101;white-space:normal'>{$errMsg}</td>
			</tr>";


		//look into the future...
		if(count($eArr) == ($rowNo+1) || (isset($eArr[$rowNo+1]['oh_uid']) && $break != $eArr[$rowNo+1]['oh_uid'])){
		  echo '</table></div></td></tr>';
        }
        $rowNo++;

	}
	echo "</table>";

	echo "</center></body></html>";
	$dbConn->dbClose();
	echo "<br><br><br><img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable
?>
<SCRIPT type="text/javascript" defer>
adjustMyFrameHeight();
function showDetail(ohUId) {

	$('.oheException'+ohUId).each(function (i,e) {
		if ($(this).css('display')=='none') {

			document.getElementById('img'+ohUId).src=document.getElementById('img'+ohUId).src.replace('down.jpg','up.jpg');
			$(this).parent().parent().removeClass('oheException');
			$(this).toggle(600, function() { adjustMyFrameHeight(); });
		} else {

			document.getElementById('img'+ohUId).src=document.getElementById('img'+ohUId).src.replace('up.jpg','down.jpg');
			$(this).toggle(500, function() { $(this).parent().parent().addClass('oheException'); adjustMyFrameHeight(); });
		}
	});
}
function deleteRow(ohdUId, rowNo) {
	ans=confirm('Are you sure you wish to mark this row as deleted ?\n\nNote: if this is the only detail item on this document, you will also have to mark the header row as deleted.');
	if (ans) {
		submitAction("DR",ohdUId,"","$('#drow_"+rowNo+"').hide();");
	}
}
function deleteDocument(ohUId) {
	ans=confirm('Are you sure you wish to delete this entire document ?');
	if (ans) {
		submitAction("DD",ohUId,"","$('.rtorder_"+ohUId+"').hide();");
	}
}
// blank store values are allowed to force the processing script to relook up the store based on its lookup values
function setStore(ohUId) {
	var s=document.getElementById('oheSTORE').value;
	ans=confirm('Are you sure you wish to set the Store for this document to the toolbar value ?');
	if (ans) {
		submitAction("SS",ohUId,s,'updateStoreField('+ohUId+');');
	}
}
function setOrderDate(ohUId, date) {
	//var s=document.getElementById('oheSTORE').value;
	ans=confirm('Are you sure you wish to set the Order Date to '+date+'?');
	if (ans) {
                submitAction("SOD",ohUId,date,'updateOrderDate('+ohUId+',"'+date+'");');
	}
}
function setReference(ohUId) {
	var r=document.getElementById('refId_'+ohUId).value;
	ans=confirm('Are you sure you wish to set the Reference?');
	if (ans) {
	  submitAction("UR",ohUId,r,"if(msgClass.type=='S') changeIconState('saveicon_"+ohUId+"','off');");
	}
}
function setDepot(ohUId) {
	var r=document.getElementById('f_dptOverride_'+ohUId).value;
	ans=confirm('Are you sure you wish to set the Depot?');
	if (ans) {
	  submitAction("UDPT",ohUId,r,"if(msgClass.type=='S') changeIconState('saveiconDpt_"+ohUId+"','off');");
	}
}
function setAmendedQuantity(ohUId) {
	var r=document.getElementById('f_amendedQtyId_'+ohUId).value;
	ans=confirm('Are you sure you wish to set the Amended Quantity?');
	if (ans) {
	  submitAction("UAQ",ohUId,r,"if(msgClass.type=='S') changeIconState('saveiconAmendedQty_"+ohUId+"','off');");
	}
}
function forceDocument(ohUId) {
	ans=confirm('Are you sure you wish to disable the order number duplication check, forcing the document to be processed?');
	if (ans) {
		submitAction("UFD",ohUId,"Y","$('#forceId_"+ohUId+"').text('Y')");
	}
}
function approveDocument(ohUId) {
  ans=confirm('Are you sure you wish to approve (release) this document for processing ?');
  if (ans) {
    submitAction("UAD",ohUId,"Y","$('#approveId_"+ohUId+"').html('')");
  }
}
function setOverridePriceType(ohdUId,priceType) {
	var type=(priceType==1)?"RT":"EDI";
	ans=confirm('Are you sure you wish to force the pricing to use '+type+' pricing for this line item ?');
	if (ans) {
		submitAction("SPR",ohdUId,priceType,'');
	}
}
// blank product values are allowed to force the processing script to relook up the product based on its lookup values
function setProduct(ohdUId, rowNo) {
	var p=document.getElementById('ohePRODUCT').value;
	ans=confirm('Are you sure you wish to set the Product for this Line Item to the toolbar value ?');
	if (ans) {
		submitAction("SP",ohdUId,p,'updateProductField('+rowNo+');');
	}
}
var alreadySubmitted=false;

function submitAction(actionType,uid,value,postJS){

	if (alreadySubmitted){
		alert('You have already clicked on submit... If you are sure the capture has NOT been stored then you may click submit again after 2 minutes.');
		return;
	}
	alreadySubmitted=true;

	var params='ACTIONTYPE='+actionType+'&UID='+uid;
	params+='&VALUE='+value;
	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/ordersHoldingManagementSubmit.php',
						  'alreadySubmitted=false; if(msgClass.type=="S") '+postJS + ';',
						  'Please wait while request is processed...');
}

function updateStoreField(uid){
  if($("#STORENAME").text()!='' && uid>0){
  	var storeString = $("#STORENAME").text();
  	storeString = storeString.split(' -- ');
  	storeString = storeString[0];
  	var psm_uid = document.getElementById('oheSTORE').value;
  	var link = '<a href="<?php echo $DHTMLROOT.$PHPFOLDER ?>functional/stores/storeCard.php?PRINCIPALSTOREUID='+psm_uid+'&MODIFY=1" target="_blank" title="Show Store" style="color:#047">' + storeString + '</a>';
  	document.getElementById("rtstore_"+uid+"").innerHTML = link;
  }
}

function updateProductField(rowNo){

  var val = $("#ohePRODUCT option:selected").val();
  if(val!=''||val!=0){
  	var productString = $("#ohePRODUCT option:selected").text();
  	productString = productString.split(' -  - ');
  	productString = productString[1];
  	document.getElementById("rtproduct_"+rowNo+"").innerHTML = productString;
  }
}

function updateOrderDate(uid, date){
  $('#orderdateId_'+uid).html(date);
}

function changeIconState(iconId,status) {
  if (status=='on') {
    $('#'+iconId).removeClass('ICONx24').addClass('ICONx0');
  } else {
    $('#'+iconId).removeClass('ICONx0').addClass('ICONx24');
  }
}

</SCRIPT>
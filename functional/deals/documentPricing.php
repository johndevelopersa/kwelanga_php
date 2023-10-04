<?php
	include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	require($ROOT.$PHPFOLDER."functional/main/access_control.php");
ob_start(); //Turn on output buffering
	include_once($ROOT.$PHPFOLDER."elements/intelliDDElement.php");
	include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
	include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
	include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
	include_once($ROOT.$PHPFOLDER."elements/simpleTableElement.php");
	include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
	include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

$dateStr=CommonUtils::getUserDate(0);

$dbConn = new dbConnect();
$dbConn->dbConnection();

if (isset($_POST['action'])) $action=mysqli_real_escape_string($dbConn->connection,$_POST['action']); else $action="VIEW";
if (isset($_POST['PAGETYPE'])) $postPAGETYPE=mysqli_real_escape_string($dbConn->connection,$_POST['PAGETYPE']); else $postPAGETYPE="A";
if (isset($_POST['P_CUSTOMERTYPE'])) $postCUSTOMERTYPE=mysqli_real_escape_string($dbConn->connection,$_POST['P_CUSTOMERTYPE']); else $postCUSTOMERTYPE=CT_CHAIN;
if (isset($_POST['P_STORECHAIN'])) $postSTORECHAIN=mysqli_real_escape_string($dbConn->connection,$_POST['P_STORECHAIN']); else $postSTORECHAIN="";

#--------------------------------------------------------------------------------------------------------------------------


	    /*
	     *
	     * START OF SCREEN
	     *
	     */
echo "<HTML>
  <HEAD>
	<META HTTP-EQUIV=\"CACHE-CONTROL\" CONTENT=\"NO-CACHE, NO-STORE\">
  	<link href='{$ROOT}{$PHPFOLDER}css/1_default.css' rel='stylesheet' type='text/css'>
	<script type='text/javascript' language='javascript' src='{$ROOT}{$PHPFOLDER}js/jquery.js'></script>
	<script type='text/javascript' language='javascript' src='{$ROOT}{$PHPFOLDER}js/dops_global_functions.js'></script>
	<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.autoscroll.js'></script>
	<scr"."ipt type=\"text/javascript\">
		\$(document).ready(function(){
			document.body.focus(); \$.autoscroll.init({step: 200});
			var e=document.getElementsByName('T_PRODUCTTYPE[]');
			for (var i=0; i<e.length; i++) {
				togglePrLstImg(i);
			}
		});

		function getRowIndex(e) {
			var r=$(e).closest('tr')[0].rowIndex-1;
			return r;
		}
		function changeProductType(row) {
			var el=document.getElementsByName('T_PRODUCTTYPELIST[]');
			el[row].value=''; // blank out old values
			togglePrLstImg(row);
		}
		function togglePrLstImg(row) {
			var e=document.getElementsByName('T_PRODUCTTYPE[]'),
				el=document.getElementsByName('T_PRODUCTTYPELIST[]');

			var img=document.getElementsByName('prLstImg')[row];
			if (e[row].value=='') {
				$(img).hide();
			} else {
				$(img).show();
			}
		}

		var sourceRowIndex=null,
			sourceListElement=null;
		function showProductTypeList(rowIndex) {
			sourceRowIndex=rowIndex;
			var e=document.getElementsByName('T_PRODUCTTYPE[]'),
				el=document.getElementsByName('T_PRODUCTTYPELIST[]');

			$('#div_PRODUCTTYPELIST,#div_PTPL,#div_PTPCL').hide();

			if (e[rowIndex].value=='P') {
				$('#div_PTPL').show();
				sourceListElement='T_PRODUCTTYPELIST_P';
			} else if (e[rowIndex].value=='PC') {
				$('#div_PTPCL').show();
				sourceListElement='T_PRODUCTTYPELIST_PC';
			}

			var eSLE=document.getElementsByName(sourceListElement);

			// carry through the values only if not blank to assist in rapid entry (remembering prev row)
			if (el[rowIndex].value!='') {
				var elArray = el[rowIndex].value.split(','); // convert to array
				for (var i=0; i<eSLE.length; i++) {
					if (elArray.findIndex(eSLE[i].value)+'x'!='x') {
						eSLE[i].checked=true;
					} else {
						eSLE[i].checked=false;
					}
				}
			}

			$('#div_PRODUCTTYPELIST').css({'marginTop':f_scrollTop(),'marginLeft':f_scrollLeft()+f_clientWidth()/2-200}).show(700); // show main container with buttons after the contents so animation effect is good
		}

		function acceptChanges() {
			var e=document.getElementsByName('T_PRODUCTTYPELIST[]');
			var list=convertElementToArray(document.getElementsByName(sourceListElement));
			$('#div_PRODUCTTYPELIST').hide();
			e[sourceRowIndex].value=list;
		}
	</scr"."ipt>";
	// autoscroll end";
	DatePickerElement::getDatePickerLibs();
echo "
  </HEAD>
  <BODY style='text-align:center;'><BR>";


if (($postSTORECHAIN=="") || ($postCUSTOMERTYPE=="")) {

	// parameter form
    echo "<form name='P_FORM' action='{$_SERVER["PHP_SELF"]}' method='post'>
		  <TABLE>";
	echo "<TR>";
	echo "<TD>Select Type :</TD>";
	echo "<TD>";
    BasicSelectElement::getCustomerTypesDD("P_CUSTOMERTYPE",$postCUSTOMERTYPE,"N","N","document.P_FORM.P_STORECHAIN.value=''; document.P_FORM.submit();",null,null,$dbConn);
    echo "</TD>
		  <td>";
		if ($postCUSTOMERTYPE==CT_CHAIN) {
		    BasicSelectElement::getUserPrincipalChainsDD("P_STORECHAIN",$postSTORECHAIN,"N","N","refreshPDDetail();",null,null,$dbConn,$userId,$principalId,CHAIN_FILTER_PRICE);
	    } else {

        	echo "<INPUT TYPE='hidden' id=\"P_STORECHAIN\" name=\"P_STORECHAIN\" >";

        	//----------------------------------------------------
        	//STORE SEARCH
        	//----------------------------------------------------
        	//special_field_or & ean_code_or means search on these fields - store name is always enabled for search.
        	//ean_code = display column	ean code.
        	//Header Name ie: store_name = Store Name  : ucword and remove dashes.
        	//columns are displayed in order of below - if uid is set to true will be col 1 by default.
        	$columnsArr = array('store_name','depot_name','delivery_day','special_fields','special_field_or');

        	IntelliDDElement::selectStoreSearch("P_STORECHAIN", $columnsArr, false, 'refreshPDDetail',"store_name + ' - ' + depot_name+', '+((delivery_day!='Not Known')?delivery_day:'')");

        	//----------------------------------------------------

	    }

    echo "</td>
		  </TR>";
    echo "</TABLE>
		  </form>";

	echo "<div id='T_FORM'></div>";
} else {

	// this style is used in other places, not just below
	echo "<style>a,img {text-decoration:none;border:0;}</style>
		  <!--[if IE]>
		  <style>div.prLst { display:inline; }</style>
		  <![endif]-->
		  <!--[if !IE]><!-->
			<style>div.prLst { display:inline-block; }</style>
		  <![endif]-->";

	// the product / product group form
	// Only 1 of these nested divs will be shown at any one time , or none depending on option chosen in product_level DD
	echo "<div id='div_PRODUCTTYPELIST' style='top:0px; left:0px; display:none; position:absolute; width:450px; white-space:nowrap; background-color:#f9f180; opacity:0.9;'>";
			echo "<div style='padding-bottom:5px;padding-top:5px;'>
				  <input type='submit' class='submit' value='Accept Changes' onclick='acceptChanges();' />
				  <input type='submit' class='submit' value='Cancel' onclick='$(\"#div_PRODUCTTYPELIST\").hide(500);' />
				  </div>";
			// NB !!! This calls Product list and not USER product list as if the screen only showed products for their permissions, on submit we would lose the deals against the others !
			//        We will probably have to show all but disable sometime in future, but this is only critical for processes such as order capture.
			echo "<div id='div_PTPL' style='display:none; border:2px; border-style:solid;' >";
			SimpleTableElement::getProductList("T_PRODUCTTYPELIST_P", array(), "checkbox", "450", $dbConn, $principalId, $userId);
			echo "</div>";
			echo "<div id='div_PTPCL' style='display:none; border:2px; border-style:solid;'>";
			SimpleTableElement::getProductGroupList("T_PRODUCTTYPELIST_PC", array(), "checkbox", "450", $dbConn, $principalId, $userId);
			echo "</div>";
	echo "</div>";


	$productDAO = new ProductDAO($dbConn);
	$mfP=$productDAO->getUserPrincipalPricingDocuments($userId, $principalId, $postCUSTOMERTYPE, $postSTORECHAIN);

	echo "<div id='T_FORM'>";
	echo "<table>
		  <tr class='odd'>
			<th>&nbsp;</th>
			<th>Grouping<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"Choose a grouping. The system will select only one Deal from within a group!\")' onmouseout='parent.hideTip();' /></th>
			<th>Description<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"Type in what you would like to call this deal. It will appear as such on the invoiced (printed) document.\")' onmouseout='parent.hideTip();' /></th>
			<th>Product Level<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"Specify whether the specific line deal must be triggered only for specific products.\")' onmouseout='parent.hideTip();' /></th>
			<th>Unit Type</th><th>Quantity (Incl)<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"When the unit type total meets or exceeds this quantity, the deal will be applied to the document total.\")' onmouseout='parent.hideTip();' /></th>
			<th>Deal Type</th><th>Value<br>Adjustment<br>(+/-)<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"Entering a negative number will deduct (discount) from the document total. A positive number will add a charge.\")' onmouseout='parent.hideTip();' /></th>
			<th>Apply Level<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"Choose the level that you want to apply the deal at - <br>Item ~ Deal will be applied per Item<br>Document ~ Deal will be applied only at Invoice total level<br>Document, across Items ~ The document totals will be used to trigger the discount, but will be apportioned across items and not shown as a separate total at bottom.\")' onmouseout='parent.hideTip();' /></th>
			<th>Start Date</th>
			<th>End Date</th>
			<th>Apply per Unit<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"If enabled, and unit type CASES is chosen, then this Adjustment Values is multiplied by number of Cases, otherwise, only the simple value adjustment applies.\")' onmouseout='parent.hideTip();' /></th>
			<th>Cumulative Type<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"How do you want to apply the discount?<br>Nett Price ~ only apply if the deal type for the line is set to Nett Price<br>Discount Zero ~ Only apply if the discounts are zero for the item price<br>Cumulative ~ Add this bulk discount to existing item list price discounts if any.\")' onmouseout='parent.hideTip();' /></th>
			<th>Status</th>
			<th></th></tr>
		  <tbody id='dealTblBody' style=\"font-size: 11px;\">";
	$i=0;
	// all input fields need an id and a name for it to display a value for some reasons ?!?!?
	$j=0;
	foreach ($mfP as $p) {
		// get the product/categ uids
		$productTypeList=array();
		foreach ($p["productArr"] as $pe) {
			$productTypeList[]=$pe["product_entity_uid"];
		}
		$productTypeList=implode(",",$productTypeList);

		echo "<tr id='tr_{$i}' name='tr_{$i}'>
				<input id='T_UID[]' name='T_UID[]' type='hidden' value='{$p["uid"]}' />
				<td><img src='{$DHTMLROOT}{$PHPFOLDER}images/delete-icon-small.png' alt='Delete Row' onclick='deleteRow(this.parentNode.parentNode.rowIndex-1);' /></td>
				<td><select id='T_GROUPING[]' name='T_GROUPING[]' DISABLED>";
				for ($i=1; $i<=10; $i++) {
					echo "<option value='{$i}' ".(($p["grouping"]==$i)?" SELECTED ":"").">{$i}</option>";
				}
		echo "	</select>
				</td>
				<td><input type='text' id='T_DESCRIPTION[]' name='T_DESCRIPTION[]' value='{$p["description"]}' /></td>
				<td>
					<div class='' style='white-space:nowrap;'>
						<div class='prLst'>
							<select name='T_PRODUCTTYPE[]' onchange='changeProductType(getRowIndex(this));' >
							<option value='' ".(($p["product_type"]=="")?"SELECTED":"")." >All Products</option>
							<option value='PC' ".(($p["product_type"]=="PC")?"SELECTED":"")." >Product Category(s)</option>
							<option value='P' ".(($p["product_type"]=="P")?"SELECTED":"")." >Products</option>
							</select>
							<input type='hidden' name='T_PRODUCTTYPELIST[]' value='{$productTypeList}' />
						</div>
						<div class='prLst'>
							<a href='#' onclick='showProductTypeList(getRowIndex(this));'><img name='prLstImg' src='".$DHTMLROOT.$PHPFOLDER."images/modify-icon.png' /></a>
						</div>
					</div>
				</td>
				<td>";
					echo BasicSelectElement::getUnitPriceTypeDD("T_UNITPRICETYPE[]",$p["unit_price_type_uid"],"Y","Y",null,null,null,$dbConn);
		echo "  </td>
				<td><input type='text' id='T_QUANTITY[]' name='T_QUANTITY[]' value='{$p["quantity"]}' size='10' /></td>
				<td>";
					echo BasicSelectElement::getDealTypesDD("T_DEALTYPE[]",$p["deal_type_uid"],"N","N",null,null,null,$dbConn);
		echo " </td>
				<td><input type='text' id='T_VALUE[]' name='T_VALUE[]' value='{$p["value"]}' size='10' /></td>
				<td><select name='T_APPLYLEVEL[]'>
					<option value='".DPL_DOCUMENT."' ".(($p["apply_level"]==DPL_DOCUMENT)?" SELECTED ":"")." DISABLED >".GUICommonUtils::translateDocumentPricingLevel(DPL_DOCUMENT)."</option>
					<option value='".DPL_DOCUMENT_ITEM."' ".(($p["apply_level"]==DPL_DOCUMENT_ITEM)?" SELECTED ":"")." >".GUICommonUtils::translateDocumentPricingLevel(DPL_DOCUMENT_ITEM)."</option>
					<option value='".DPL_ITEM."' ".(($p["apply_level"]==DPL_ITEM)?" SELECTED ":"")." >".GUICommonUtils::translateDocumentPricingLevel(DPL_ITEM)."</option>
				</td>
				<td>";
				DatePickerElement::getDatePicker("T_STARTDATE[]",$p['start_date']);
		echo "	</td>
				<td>";
				DatePickerElement::getDatePicker("T_ENDDATE[]",$p['end_date']);
		echo "  <td><select name='T_APPLYPERUNIT[]'>
					<option value='Y' ".(($p["apply_per_unit"]=="Y")?" SELECTED ":"")." >(off List Price) Value x Units</option>
					<option value='N' ".(($p["apply_per_unit"]=="N")?" SELECTED ":"")." >(off Ext. Price) Value only</option>
				</td>
				<td><select name='T_CUMULATIVETYPE[]'>
					<option value='".DPCT_NETT_PRICE."' ".(($p["cumulative_type"]==DPCT_NETT_PRICE)?" SELECTED ":"")." >".GUICommonUtils::translateCumulativeType(DPCT_NETT_PRICE)."</option>
					<option value='".DPCT_DISCOUNTS_ZERO."' ".(($p["cumulative_type"]==DPCT_DISCOUNTS_ZERO)?" SELECTED ":"")." >".GUICommonUtils::translateCumulativeType(DPCT_DISCOUNTS_ZERO)."</option>
					<option value='".DPCT_DISCOUNTS_CUMULATIVE."' ".(($p["cumulative_type"]==DPCT_DISCOUNTS_CUMULATIVE)?" SELECTED ":"")." >".GUICommonUtils::translateCumulativeType(DPCT_DISCOUNTS_CUMULATIVE)."</option>
					<option value='".DPCT_DISCOUNTS_OVERRIDE."' ".(($p["cumulative_type"]==DPCT_DISCOUNTS_OVERRIDE)?" SELECTED ":"")." >".GUICommonUtils::translateCumulativeType(DPCT_DISCOUNTS_OVERRIDE)."</option>
				</td>
				<td><select name='T_STATUS[]'>
					<option value='".FLAG_STATUS_ACTIVE."' ".(($p["status"]==FLAG_STATUS_ACTIVE)?" SELECTED ":"")." >Active</option>
					<option value='".FLAG_STATUS_DELETED."' ".(($p["status"]==FLAG_STATUS_DELETED)?" SELECTED ":"")." >Deleted</option>
				</td>
				<td><input type=submit class=submit value='Submit this Row' onclick='submitRow(this.parentNode.parentNode.rowIndex-1);' /></td></tr>";
		$i++;
	}
	if (sizeof($mfP)==0) {
		echo "<tr id='tr_0' name='tr_0'>
				<input id='T_UID[]' name='T_UID[]' type='hidden' value='' />
				<td><img src='{$DHTMLROOT}{$PHPFOLDER}images/delete-icon-small.png' alt='Delete Row' onclick='deleteRow(this.parentNode.parentNode.rowIndex-1);' /></td>
				<td><select id='T_GROUPING[]' name='T_GROUPING[]'>";
				for ($i=1; $i<=10; $i++) {
					echo "<option value='{$i}' >{$i}</option>";
				}
		echo "  </select>
				</td>
				<td><input type='text' id='T_DESCRIPTION[]' name='T_DESCRIPTION[]' value='' /></td>
				<td>
					<div class='' style='white-space:nowrap;'>
						<div class='prLst'>
							<select id='T_PRODUCTTYPE[]' name='T_PRODUCTTYPE[]' onchange='changeProductType(getRowIndex(this));' ><option value=''>All Products</option><option value='PC'>Product Category(s)</option><option value='P'>Products</option></select>
							<input id='T_PRODUCTTYPELIST[]' name='T_PRODUCTTYPELIST[]'  type='hidden'>
						</div>
						<div class='prLst'>
							<a href='#' onclick='showProductTypeList(getRowIndex(this));'><img name='prLstImg' src='".$DHTMLROOT.$PHPFOLDER."images/modify-icon.png' /></a>
						</div>
					</div>
				</td>
				<td>";
					echo BasicSelectElement::getUnitPriceTypeDD("T_UNITPRICETYPE[]","","N","N",null,null,null,$dbConn);
		echo "  </td>
				<td><input type='text' id='T_QUANTITY[]' name='T_QUANTITY[]' value='' size='10' /></td>
				<td>";
					echo BasicSelectElement::getDealTypesDD("T_DEALTYPE[]","","N","N",null,null,null,$dbConn);
		echo " </td>
				<td><input type='text' id='T_VALUE[]' name='T_VALUE[]' value='' size='10' /></td>
				<td><select name='T_APPLYLEVEL[]'>
					<option value='".DPL_DOCUMENT."' SELECTED DISABLED >".GUICommonUtils::translateDocumentPricingLevel(DPL_DOCUMENT)."</option>
					<option value='".DPL_DOCUMENT_ITEM."' >".GUICommonUtils::translateDocumentPricingLevel(DPL_DOCUMENT_ITEM)."</option>
					<option value='".DPL_ITEM."' >".GUICommonUtils::translateDocumentPricingLevel(DPL_ITEM)."</option>
				</td>
				<td>";
				DatePickerElement::getDatePicker("T_STARTDATE[]",$dateStr);
		echo "	</td>
				<td>";
				DatePickerElement::getDatePicker("T_ENDDATE[]",$dateStr);
		echo "	</td>
				<td><select name='T_APPLYPERUNIT[]'>
					<option value='Y' >(off List Price) Value x Units</option>
					<option value='N' SELECTED >(off Ext. Price) Value only</option>
				</td>
				<td><select name='T_CUMULATIVETYPE[]'>
					<option value='".DPCT_NETT_PRICE."' SELECTED >".GUICommonUtils::translateCumulativeType(DPCT_NETT_PRICE)."</option>
					<option value='".DPCT_DISCOUNTS_ZERO."' >".GUICommonUtils::translateCumulativeType(DPCT_DISCOUNTS_ZERO)."</option>
					<option value='".DPCT_DISCOUNTS_CUMULATIVE."' >".GUICommonUtils::translateCumulativeType(DPCT_DISCOUNTS_CUMULATIVE)."</option>
					<option value='".DPCT_DISCOUNTS_OVERRIDE."' >".GUICommonUtils::translateCumulativeType(DPCT_DISCOUNTS_OVERRIDE)."</option>
				</td>
				<td><select name='T_STATUS[]'>
					<option value='".FLAG_STATUS_ACTIVE."' SELECTED>Active</option>
					<option value='".FLAG_STATUS_DELETED."'>Deleted</option>
				</td>
				<td><input type=submit class=submit value='Submit this Row' onclick='submitRow(this.parentNode.parentNode.rowIndex-1);' /></td></tr>";
	}
	echo " </tbody><tr class='odd'><td colspan=15 style='text-align:center;'><input type=submit class=submit value='Add Row' onclick='addRow();' /></td></tr></table>";
    echo "</div>";  // main content area

}
?>
<script type="text/javascript" defer>
function addRow() {
	if (!document.getElementsByTagName) { alert('Your browser does not support dynamic row add. Please contact RetailTrading.'); return; }

	var tableBody = document.getElementById('dealTblBody');
	var root=tableBody.getElementsByTagName('tr')[0].parentNode; //the TBODY
	var tableRows=tableBody.getElementsByTagName('tr').length;
	var clone=tableBody.getElementsByTagName('tr')[0].cloneNode(true); //the clone of the first row
	root.appendChild(clone); //appends the clone
	document.getElementsByName("T_GROUPING[]")[tableRows].value="";
	document.getElementsByName("T_GROUPING[]")[tableRows].disabled=false;
	document.getElementsByName("T_DESCRIPTION[]")[tableRows].value="";
	document.getElementsByName("T_PRODUCTTYPELIST[]")[tableRows].value="";
	togglePrLstImg(tableRows);
	document.getElementsByName("T_UNITPRICETYPE[]")[tableRows].value="";
	document.getElementsByName("T_UNITPRICETYPE[]")[tableRows].readOnly=false;
	document.getElementsByName("T_UNITPRICETYPE[]")[tableRows].disabled=false;
	document.getElementsByName("T_QUANTITY[]")[tableRows].value="";
	document.getElementsByName("T_DEALTYPE[]")[tableRows].value="";
	document.getElementsByName("T_VALUE[]")[tableRows].value="";
	document.getElementsByName("T_APPLYLEVEL[]")[tableRows].value="<?php echo DPL_DOCUMENT ?>";
	//document.getElementsByName("T_STARTDATE[]")[tableRows].value="";
	//document.getElementsByName("T_ENDDATE[]")[tableRows].value="";
	document.getElementsByName("T_STATUS[]")[tableRows].value="<?php echo FLAG_STATUS_ACTIVE ?>";
	document.getElementsByName("T_APPLYPERUNIT[]")[tableRows].value="N";
	document.getElementsByName("T_CUMULATIVETYPE[]")[tableRows].value="<?php echo DPCT_NETT_PRICE ?>";
	document.getElementsByName("T_UID[]")[tableRows].value="";
}
var alreadySubmitted=false;
function submitRow(row) {
	if (alreadySubmitted) {
		alert('You have already clicked on submit... If you are sure the capture has NOT been stored then you may click submit again after 2 minutes.');
		return;
	}
	alreadySubmitted=true;
	var grouping=document.getElementsByName('T_GROUPING[]')[row].value,
		customertype='<?php echo $postCUSTOMERTYPE; ?>',
		storechain='<?php echo $postSTORECHAIN; ?>',
		uid=document.getElementsByName('T_UID[]')[row].value,
		description=document.getElementsByName('T_DESCRIPTION[]')[row].value
		producttype=document.getElementsByName('T_PRODUCTTYPE[]')[row].value,
		producttypelist=document.getElementsByName('T_PRODUCTTYPELIST[]')[row].value,
		unitpricetype=document.getElementsByName('T_UNITPRICETYPE[]')[row].value,
		quantity=document.getElementsByName('T_QUANTITY[]')[row].value,
		dealtype=document.getElementsByName('T_DEALTYPE[]')[row].value,
		value=document.getElementsByName('T_VALUE[]')[row].value,
		applylevel=document.getElementsByName('T_APPLYLEVEL[]')[row].value,
		startdate=document.getElementsByName('T_STARTDATE[]')[row].value,
		enddate=document.getElementsByName('T_ENDDATE[]')[row].value,
		applyperunit=document.getElementsByName('T_APPLYPERUNIT[]')[row].value,
		cumulativetype=document.getElementsByName('T_CUMULATIVETYPE[]')[row].value,
		tstatus=document.getElementsByName('T_STATUS[]')[row].value;
	if (uid=="") var params='DMLTYPE=INSERT';
	else var params='DMLTYPE=UPDATE';

	params+='&CUSTOMERTYPE='+customertype;
	params+='&STORECHAIN='+storechain;
	params+='&UID='+uid;
	params+='&GROUPING='+grouping;
	params+='&DESCRIPTION='+description;
	params+='&PRODUCTTYPE='+producttype;
	params+='&PRODUCTTYPELIST='+producttypelist;
	params+='&UNITPRICETYPE='+unitpricetype;
	params+='&QUANTITY='+quantity;
	params+='&DEALTYPE='+dealtype;
	params+='&VALUE='+value;
	params+='&APPLYLEVEL='+applylevel;
	params+='&STARTDATE='+startdate;
	params+='&ENDDATE='+enddate;
	params+='&APPLYPERUNIT='+applyperunit;
	params+='&CUMULATIVETYPE='+cumulativetype;
	params+='&STATUS='+tstatus;
	
//	window.alert(params);
	
	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/deals/documentPricingSubmit.php',
						  'alreadySubmitted=false; if (msgClass.type=="S") postSubmitSuccess(msgClass,'+row+');',
						  'Please wait while request is processed...');
}

function postSubmitSuccess(msgClass,row) {
	if (msgClass.identifier!='') {
		document.getElementsByName('T_UID[]')[row].value=msgClass.identifier;
		document.getElementsByName('T_UID[]')[row].value=msgClass.identifier;
		document.getElementsByName("T_UNITPRICETYPE[]")[row].readOnly=true;
		document.getElementsByName("T_UNITPRICETYPE[]")[row].disabled=true;
		document.getElementsByName("T_GROUPING[]")[row].disabled=true;
	}
}
function deleteRow(row) {
	if (!document.getElementsByTagName) { alert('Your browser does not support dynamic row add. Please contact RetailTrading.'); return; }

	var tableBody = document.getElementById('dealTblBody');
	var root=tableBody.getElementsByTagName('tr')[0].parentNode; //the TBODY
	var lineCnt=tableBody.getElementsByTagName('tr').length;
	if ((lineCnt<=1) || (row==0)) {
		alert('There must be atleast 1 row remaining, and the first row cannot be deleted.');
		return;
	}
	if (document.getElementsByName('T_UID[]')[row].value!='') {
		alert('This delete button does not delete the deal row from the database, it can only be used to delete rows that have not already been submitted. If you need to delete a submitted row, change the status to be DELETED');
		return;
	}
	var tblRow=tableBody.getElementsByTagName('tr')[row];
	root.removeChild(tblRow);
}
function refreshPDDetail() {
	AjaxRefresh("P_CUSTOMERTYPE="+document.P_FORM.P_CUSTOMERTYPE.value+"&P_STORECHAIN="+document.P_FORM.P_STORECHAIN.value,
				"<?php echo $_SERVER['PHP_SELF']; ?>",
			    "T_FORM",
			    "Please wait whilst page is refreshed...",
			    "");
}
</script>
<?php
echo "</BODY></HTML>";
#--------------------------------------------------------------------------------------------------------------------------

$dbConn->dbClose();

$htmlBody = ob_get_clean();
$htmlBody = gzencode($htmlBody, 9, FORCE_GZIP);
header ("Content-Encoding: gzip");
header ('Content-Length: '.strlen($htmlBody));
echo $htmlBody;

?>

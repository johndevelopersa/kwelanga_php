<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'functional/main/access_control.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."TO/PostingOrderTO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
include_once($ROOT.$PHPFOLDER."elements/intelliDDElement.php");
include_once($ROOT.$PHPFOLDER."elements/Messages.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");

CommonUtils::getSystemConventions();


if (!isset($_SESSION)) session_start() ;
$principalId  = $_SESSION['principal_id'] ;
$userId       = $_SESSION['user_id'];
$systemId = $_SESSION["system_id"];
$systemName = $_SESSION['system_name'];

//?
$principalType = $_SESSION['principal_type'];
$principalName = $_SESSION['principal_name'];
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$principalAliasName = (($_SESSION['principal_alias_name']=="")?$principalName:$_SESSION['principal_alias_name']);

$postDOCMASTID = (isset($_GET['DOCMASTID'])) ? $_GET['DOCMASTID'] : false;
$isCOPYDOCUMENT = (isset($_GET['COPYDOCUMENT'])) ? true : false;

$postDOCTYPE = (isset($_GET['DOCTYPE']) && is_numeric($_GET['DOCTYPE'])) ? $_GET['DOCTYPE'] : false;

$dbConn = new dbConnect();
$dbConn->dbConnection();

$administrationDAO = new AdministrationDAO($dbConn);

class Header {
  public $storeUId="";
  public $storeName="";
  public $documentDate="";
  public $deliveryDate="";
  public $deliveryInstruction="";
  public $customerReferenceNo="";
  public $documentNumber="";

  public $detailArr=array();
}
class Detail {
  public $productUId="";
  public $quantity="";
  public $price="";
  public $extPrice="";
  public $comment="";
}
$TO = new Header();


if ($postDOCMASTID!==false) {
  include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
  $transactionDAO = new TransactionDAO($dbConn);

  $mfT = $transactionDAO->getDocumentWithDetailItem($userId, $principalId, $postDOCMASTID);
  if (count($mfT)==0) {
    echo "You do not have permission to amend this quotation, or quotation does not exist";
    return;
  }

  $hasRole = $administrationDAO->hasRole($userId,$principalAliasId,ROLE_MANAGE_QUOTATION);
  if (!$hasRole) {
    echo "Sorry, you do not have permissions to manage quotations!";
    return;
  }

  $TO->storeUId = $mfT[0]["psm_uid"];
  $TO->storeName = $mfT[0]["store_name"];
  $TO->documentDate = $mfT[0]["order_date"];
  $TO->deliveryDate = (($mfT[0]["delivery_date"]=="0000-00-00")?"":$mfT[0]["delivery_date"]);
  $TO->deliveryInstruction = $mfT[0]["delivery_instructions"];
  $TO->customerReferenceNo = $mfT[0]["customer_order_number"];
  $TO->documentNumber = (($isCOPYDOCUMENT)?"":$mfT[0]["document_number"]);

  foreach ($mfT as $r) {
    $temp = new Detail();
    $temp->productUId = $r["product_uid"];
    $temp->quantity = $r["ordered_qty"];
    $temp->price = $r["net_price"];
    $temp->extPrice = $r["extended_price"];
    $temp->comment = $r["comment"];

    $TO->detailArr[] = $temp;
  }

}


#--------------------------------------------------------------------------------------------------------------------------


$postDMLTYPE="INSERT";

$principalAliasChosen = ((isset($_REQUEST["pAliasC"]))?trim(mysql_real_escape_string($_REQUEST["pAliasC"])):"N");

$totalRows=5;
echo "<HTML>";
echo "<HEAD>";
?>

<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
<script type="text/javascript">
  // build the store list from variable arrays
  parent.showMsgBoxSystemFeedback('Building Lists... Please wait...');
  $(document).ready(function(){
		parent.hideMsgBoxSystemFeedback('Building Lists... Please wait...');
		if (typeof(dummy)=="function") {
	  	buildProductList();
	  	setDefaultRows();
      // start the autoSave Session on this trigger
      // document.getElementById("STORE").onchange=startAutoSaveSession; // cant do this because it will overwrite existing onchange event in declaration
      setTimeout("checkForRestore();",1000);
      setDocumentNumberRequired();
      <?php if ($postDOCMASTID!==false) echo "applyAmendFieldValues();" ?>
		}

  });

</script>
<?php DatePickerElement::getDatePickerLibs(); ?>
<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/default.css' rel='stylesheet' type='text/css'>
<!-- the initial zoom is different from the home.php page viewport as this screen needs a little more width as the green btns go off page -->
<meta name="viewport" content="initial-scale=0.8, minimum-scale=0.2, maximum-scale=3, width=device-width, height=device-height, target-densitydpi=device-dpi" />

<?php
echo "</HEAD>";
echo "<BODY style=''><CENTER><BR>";

// check roles
$administrationDAO = new AdministrationDAO($dbConn);

$hasRole = $administrationDAO->hasRole($userId,$principalAliasId,ROLE_QUOTATION_CAPTURE);
if (!$hasRole) {
	echo "Sorry, you do not have permissions to CAPTURE!";
	return;
}
$hasRoleModifyDD = $administrationDAO->hasRole($userId,$principalAliasId,ROLE_OC_CAN_MODIFY_DELDATE);
$hasRolePriceOverride = $administrationDAO->hasRole($userId,$principalAliasId,ROLE_ALLOW_PRICE_OVERRIDE);
// end check roles

// get import preferences
$principalDAO = new PrincipalDAO($dbConn);
$mfPPref = $principalDAO->getPrincipalPreferences($principalAliasId); // importDAO changed to now always return row
$mfASUsers = $principalDAO->getSequenceControlUsers($principalAliasId, "", DS_CAPTURE, "", "");
$mfPDocType = $principalDAO->getPrincipalDocumentTypes($principalAliasId); // document types overrides if any

$pDT_ProformaPricing = array();
foreach ($mfPDocType as $r) {
  if (($r["proforma_pricing"]=="Y") && ($r["document_type_uid"]!=DT_ORDINV))
     $pDT_ProformaPricing[]=$r["document_type_uid"];
}

$orderNumberUnique = (isset($mfPPref[0]["order_number_unique"]) && $mfPPref[0]["order_number_unique"] == 'Y') ? 'Y' : 'N';

// previous Reference saved
echo "<DIV style='font-family:Verdana,Arial,Helvetica,sans-serif; color:grey; font-size:0.85em' id='prevSavedRefMsg' >Previous Saved Reference: </div>";

// autosave bar
echo "<DIV style='font-family:Verdana,Arial,Helvetica,sans-serif; color:grey; font-size:0.85em' id='autoSaveMsg' >AutoSaved last: </div>";


echo "<br>";

$sncStore = SNC::store . ' :';
$dateStr = CommonUtils::getUserDate();


/******************************************************************************************************************
 ****************************************** STORE *********************************************************
 ******************************************************************************************************************/

echo'<table border="0">';

// the order header
echo "<TR>";
echo "<TD>" . $sncStore . "</TD>";
echo "<TD>";


echo "<INPUT TYPE='hidden' id=\"STORE\" name=\"STORE\" >";

//----------------------------------------------------
//STORE SEARCH
//----------------------------------------------------
//special_field_or & ean_code_or means search on these fields - store name is always enabled for search.
//ean_code = display column	ean code.
//Header Name ie: store_name = Store Name  : ucword and remove dashes.
//columns are displayed in order of below - if uid is set to true will be col 1 by default.
$columnsArr = array('store_name','depot_name','delivery_day','rep_name','special_fields','special_field_or');

IntelliDDElement::selectStoreSearch("STORE", $columnsArr, false, 'selectStore',"store_name + ' - ' + depot_name+', '+((delivery_day!='Not Known')?delivery_day:'')",$showVendorStores=false,$urlString="pAlias={$principalAliasId}", $isDesktop=true);

//Function for ONCLICK.
?>
<script type='text/javascript'>
 function selectStore(uid,name,onhold){
     startAutoSaveSession();
     changeStore();
     setDelDay(name);
 }
</script>
<?php

//----------------------------------------------------

echo "</TD>";
echo "</TR>";

/******************************************************************************************************************
 ****************************************** DOCUMENT DATE *********************************************************
 ******************************************************************************************************************/

echo "<TR>";
echo "<TD>Document Date :</TD>";
echo "<TD>";

DatePickerElement::getDatePicker("DOCDATE",$dateStr,false,$isDesktop=true);

if ($isDesktop) {
  echo "</TD>";
  echo "</TR>";
}


/******************************************************************************************************************
 ****************************************** DELIVERY DATE *********************************************************
 ******************************************************************************************************************/

$f = false;

echo "<TR>";
echo "<TD>Delivery Date :</TD>";
echo "<TD>";

if ($hasRoleModifyDD) DatePickerElement::getDatePicker("DELDATE","");
else echo "<input id='DELDATE' name='DELDATE' type='text' size='10' maxlength='10' READONLY DISABLED />";
echo "<div id='feedbackDD' style='color:red;'></div>";

echo "</TD>";
echo "</TR>";


echo "<SCR"."IPT>";
// used for picking the next delivery day based on store chosen
echo "var delDayArr=new Array();";

for ($i=1; $i<=7; $i++) {
  $uDate=CommonUtils::getUserDate($i);
  $dOW=date("w",strtotime($uDate));
  echo 'delDayArr['.$dOW.']="'.$uDate.'";'."\r\n";
}
echo "function setDelDay(val) {
					var list=val.split(','),
					    dday=list[list.length-1];
					var fld=document.getElementById('DELDATE');
					if(fld.value!='') document.getElementById('feedbackDD').innerHTML='<B>WARNING:</B> Delivery Date value may have been changed when you changed the selected Store!';
					switch(dday.toLowerCase().fulltrim()) {
					  case 'sunday':fld.value=delDayArr[0]; break;
					  case 'monday':fld.value=delDayArr[1]; break;
					  case 'tuesday':fld.value=delDayArr[2]; break;
					  case 'wednesday':fld.value=delDayArr[3]; break;
					  case 'thursday':fld.value=delDayArr[4]; break;
					  case 'friday':fld.value=delDayArr[5]; break;
					  case 'saturday':fld.value=delDayArr[6]; break;
					  default:fld.value='';
					}

				}";
echo "</SCRIPT>";


/******************************************************************************************************************
 ****************************************** DOCUMENT TYPE *********************************************************
 ******************************************************************************************************************/


if ($isDesktop) {

 // echo "<TR ".GUICommonUtils::showHideField($scrPref,'DOCTYPE',$f,false).">";
	echo "<TD>Document Type :</TD>";
	echo "<TD>";

} else {

 //  echo "<div ".GUICommonUtils::showHideField($scrPref,'DOCTYPE',$f,false)."><br>
  echo "<span class='mobile-label'>Document Type :</span>";

}

echo "<div class='select'>";
BasicSelectElement::getDocumentTypesAllowedDD("DOCTYPE",$postDOCTYPE,"N","N","changeDocType();",null,null,$dbConn,$userId,$principalAliasId);
echo "</div>";

if ($isDesktop) {
  echo "</TD>";
  echo "</TR>";
} else {
  echo "</div>";
}


/******************************************************************************************************************
 ****************************************** DELIVERY INSTRUCTIONS *************************************************
 ******************************************************************************************************************/

$sncDeliveryInst = 'Remarks';

echo "<TR>";
echo "<TD>" . $sncDeliveryInst . " :</TD>";
echo "<TD>";
echo '<TEXTAREA id="DELINST" name="DELINST" type="text" cols="40" rows="4" value="" ></TEXTAREA>';
echo "</TD>";
echo "</TR>";


/******************************************************************************************************************
 ****************************************** CUSTOMER REFERENCE ****************************************************
 ******************************************************************************************************************/


$sncCustomerRef = 'Customer Reference No';

echo "<TR>";
echo "<TD>" . $sncCustomerRef . " :</TD>
		  <TD>";

echo "<input id=\"CUSTREF\" name='CUSTREF' type='text' style='width:100%' maxlength='25' value=''></TD>";


echo "</TD>
      </TR>";


/******************************************************************************************************************
 ****************************************** DOCUMENT NUMBER ****************************************************
 ******************************************************************************************************************/

echo "<TR>";
echo "<TD>Document Number :</TD>";
echo "<TD>";
echo "<input id=\"DN\" name='DN' type='text' size='8' maxlength='8' value='' >";

echo "<span id='autoSeqMsg' style='color:".COLOR_UNOBTRUSIVE_INFO."'></span>";

echo "</TD>
      </TR>";


/******************************************************************************************************************
 ****************************************** OPTIONAL FIELDS *******************************************************
 ******************************************************************************************************************/

// this is needed as otherwise chrome doesnt "finish off" the bottom of the table (underline border) if optional fields hidden
echo "<tr><td colspan=2></td></tr>";
echo "</table>";

echo "<br><br>";



/******************************************************************************************************************
 ****************************************** PRODUCT / DETAIL SECTION **********************************************
 ******************************************************************************************************************/


echo '<table border="0" id="dealTbl">';

echo "<thead>";
echo "<TR>";
echo "<TH scope=col>&nbsp;</TH>";
echo "<TH scope=col>Product</TH>";
echo "<TH scope=col>Quantity</TH>";
echo "<TH scope=col>Price</TH>";
echo "<TH scope=col>Ext Price<br><span style='font-size:8px;'>*excl Bulk Disc</span></TH>";
echo "<TH scope=col>Comment</TH>";
echo "</TR>";
echo "</thead>";

echo "<tbody id='dealTblBody' style=\"font-size: 11px;\">";

echo "<TR id='tr_0' name='tr_0'>";
echo "<TD><img src='{$DHTMLROOT}{$PHPFOLDER}images/delete-icon-small.png' alt='Delete Row' onclick='deleteRow(this.parentNode.parentNode.rowIndex-1);' /></TD>";
echo "<TD>";

echo "<SELECT id=\"PRODUCT[]\" name=\"PRODUCT[]\" onchange='changeProduct(this.parentNode.parentNode.rowIndex-1);' onKeyUp='showSpecialDD(event,this,\"ddProdSearchInput\");'>
		  <OPTION value='' SELECTED>Not Selected</OPTION>
		  </SELECT>";

echo "</TD>";

echo "<TD id='tdq0' style='white-space:nowrap;' nowrap>
      <table class='tableReset' style='font-size:12px;' cellpadding=0 cellspacing=0><tr><td><input name='PREQTY[]' type='text' size='5' style='display:none;' maxlength='5' value='' DISABLED READONLY ></td>
      <td><input id='q0' name='QTY[]' type='text' size='5' maxlength='5' value='' onchange='chgQty(this.parentNode.parentNode.rowIndex-1);' ></td>
      </tr></table>
			</TD>";

// *** BE CAREFUL. This TD Cell uses ParentNode and it MUST point to the TR to get rowIndex
// can also use alert($(this).closest("tr").prevAll("tr").length + 1)
echo "<TD><INPUT type='text' name='price[]' style='border-style:none; color:grey;' value='' READONLY />
		  <INPUT type='text' name='OVERRIDE_PRICE[]' size='5' maxlength='8' value='' style='display:none;' value='' onchange='chgQty(this.parentNode.parentNode.rowIndex-1);' ".(($hasRolePriceOverride)?"":" READONLY ")."/>
			  <img src='{$DHTMLROOT}{$PHPFOLDER}images/modify-icon.png' alt='Override Price' onclick='showPriceOverride(this.parentNode.parentNode.rowIndex-1);' />
			  <img src='{$DHTMLROOT}{$PHPFOLDER}images/modify-cancel-icon.png' alt='Cancel Override' onclick='hidePriceOverride(this.parentNode.parentNode.rowIndex-1);' /></TD>"; // readonly instead of disabled allows font color to be set to red later


echo "<TD><INPUT style='text-align:right;' type='text' name='extPrice[]' size='7' maxlength='7' style='border-style:none; color:grey;' value='' READONLY /></TD>";
echo "<TD><INPUT style='' type='text' name='COMMENT[]' size='30' maxlength='60' style='' value='' /></TD>";
echo "</TR>";
echo "</tbody>";



// total line
echo "<tbody id='totals' style=\"font-size: 11px;\">";
echo "<TR >";
echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'></TH>";
echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'></TH>";
echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'></TH>";
echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'></TH>";
echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;' ><DIV id='totalEP' style='font-weight:bold;'></DIV></TH>";
echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'></TH>";
echo "</TR>";
echo "</tbody>";

echo "<tfoot>";
echo "<tr>".
    "<td colspan=6>
    <input type='button' name='addmore' id='addmore' class='submit' value='Add Another Row' onclick='addRow(); adjustMyFrameHeight();'/>
    <input type='button' name='delmore' id='delmore' class='submit' value='Delete Last Row' onclick='deleteRow(-1); adjustMyFrameHeight();'/>
    <span><input type='button' name='cEPT' id='rcpallets' class='submit' value='Calculate Extended Price Total' onclick='calculateEPT();'/></span></td>".
      "</tr>";
echo "</tfoot>";

echo "</table>";



echo "<br><br>
      <INPUT class='submit' type=\"button\" onclick='submitContentForm(\"".$postDMLTYPE."\",\"\");' value=\"Submit\" />
      <br><br>
      <INPUT class='submit' type=\"button\" onclick='cancelForm();' value=\"Cancel\" />
      <br><br>";

$ScheduleInnerHTML = "<table class='tableReset' style='font-size:11px;'>
                      <tr>
                      <td width=\"60%\">
                        <span style='font-size:1.2em;letter-spacing:-0.03em'><strong>CAPTURE ASSISTANCE</strong><br></span>
                        Your past documents are show here to the right. You may choose to reuse products from these on a new capture.<br><br>
                        <span style='color:#7c7e7a;'>Please note : </span><br>
                        If you exit the capture screen, the history is lost !<br>
                        If you need to temporarily navigate away from the capture screen, please open up a new tab / browser session by copying the url and pasting it into the new page.
                      </td>
                      <td align='center' style='display:block;padding:6px 10px;margin:0px;margin-top:8px;color:#fff;font-weight:bold;text-align:center;background:#d8dad6'>
                        <style>
                        .captureAssistTbl th {
                          color:white;
                          background:#808080;
                        }
                        .captureAssistTbl td {
                          border-bottom:1px;
                          border-bottom-color:#BBBBBB;
                          border-bottom-style:dotted;
                        }
                        </style>
                        <div id='captureAssist' ></div>
                      </td>
                      </tr>
                      </table>";
echo "<div style='margin-left:10%; margin-right:10%;'>";
GUICommonUtils::outputBlkInobtrusive($ScheduleInnerHTML);
echo "</div>";

echo "</CENTER></BODY>";
echo "</HTML>";

$storeDAO = new StoreDAO($dbConn);
$mfPS = $storeDAO->getUserPrincipalStoreArray($userId,$principalAliasId,"");
$productDAO = new ProductDAO($dbConn);
$mfPP=$productDAO->getUserPrincipalProductsArray($principalAliasId,$userId);

//minor category lables...
$minorLableArr = array();
if(!empty($mfPP[0]['minor_category_lables_list'])){
  $minorLableArr = explode(';', $mfPP[0]['minor_category_lables_list']);
}

$dbConn->dbClose();

?>
<SCRIPT type='text/javascript' >
<?php
echo "	var totalRows=1,
     	    autoSaveStarted=false,
     	    intervalTimer,
          proformaPriceLookup=[".((count($pDT_ProformaPricing)>0)?implode(",",$pDT_ProformaPricing):"")."];";


echo "var psArrDPT=new Object();"; // Depot UID, key is store UId
$jsDPT="";

foreach ($mfPS as $row) {
	if ($jsDPT=="") $jsDPT="psArrDPT={\"".$row['psm_uid']."\":\"".$row['depot_uid']."\""; else $jsDPT.=",\"".$row['psm_uid']."\":\"".$row['depot_uid']."\"";
}
if ($jsDPT!="") $jsDPT.="};";
$combinedJS=$jsDPT;



// create the JS for inteli lookups - PRODUCT
echo "var ppArrPC=new Array();";
echo "var ppArrAC=new Array();"; // alt Code
echo "var ppArrPD=new Array();";
echo "var ppArrUID=new Array();";
echo "var ppArrEPC=new Object();"; // key is product uid
echo "var ppArrUPP=new Object();"; // key is product uid
$jsPC="";
$jsAC="";
$jsPD="";
$jsPUID="";
$jsEPC="";
$jsUPP="";
foreach ($mfPP as $row) {
	if ($jsPUID=="") $jsPUID="ppArrUID=[\"".str_replace('"','',$row['uid'])."\""; else $jsPUID.=",\"".str_replace('"','',$row['uid'])."\"";
	if ($jsPC=="") $jsPC="ppArrPC=[\"".str_replace('"','',$row['product_code'])."\""; else $jsPC.=",\"".str_replace('"','',$row['product_code'])."\"";
	if ($jsAC=="") $jsAC="ppArrAC=[\"".str_replace('"','',$row['alt_code'])."\""; else $jsAC.=",\"".str_replace('"','',$row['alt_code'])."\"";
	if ($jsPD=="") $jsPD="ppArrPD=[\"".str_replace('"','',$row['product_description'])."\""; else $jsPD.=",\"".str_replace('"','',$row['product_description'])."\"";

	if ($jsEPC=="") $jsEPC="ppArrEPC={\"".$row['uid']."\":\"".$row['enforce_pallet_consignment']."\""; else $jsEPC.=",\"".$row['uid']."\":\"".$row['enforce_pallet_consignment']."\"";
	if ($jsUPP=="") $jsUPP="ppArrUPP={\"".$row['uid']."\":\"".$row['units_per_pallet']."\""; else $jsUPP.=",\"".$row['uid']."\":\"".$row['units_per_pallet']."\"";
}
if ($jsPC!="") $jsPC.="];";
if ($jsAC!="") $jsAC.="];";
if ($jsPD!="") $jsPD.="];";
if ($jsPUID!="") $jsPUID.="];";
if ($jsEPC!="") $jsEPC.="};";
if ($jsUPP!="") $jsUPP.="};";
$combinedJS.=$jsPC.$jsAC.$jsPD.$jsPUID.$jsEPC.$jsUPP;
echo $combinedJS;


//product minor categories
$mcPreArr = array();
for($i=0;$i<count($minorLableArr);$i++){$mcPreArr[] = "";}
$mcValArr = array();
foreach($mfPP as $mi){
  $mcArr = explode(';',$mi['minor_category_list']);
  if(count($mcArr) != count($mcPreArr)){
    $mcArr = $mcPreArr;
  }
  $mcValArr[$mi['uid']] = $mcArr;
}
echo 'var ppArrMC = ' . json_encode($mcValArr) . "; \n";


?>

function resetCalculatedFields() {
  var docType=document.getElementById('DOCTYPE').value,
		fldq=document.getElementsByName('QTY[]'),
		fldp=document.getElementsByName('price[]');
    fldComment=document.getElementsByName('COMMENT[]');

	for (i=0; i<fldq.length; i++) {
	  if (proformaPriceLookup.findIndex(docType).toString()=='') {
			fldp[i].value='';
		}
		getPrice(i);
	}
	calculateEPT();
}

function changeStore() {
  resetCalculatedFields();
}

// product doesnt have an id so have to do it this way
function changeProduct(row) {
	if (!isNumeric(row)) {
		alert('ERROR: rowIndex is not supported in this browser. Please notify Retailtrading');
		return;
	}
	var fldp=document.getElementsByName('PRODUCT[]'),
	    fldq=document.getElementsByName('QTY[]');

	getPrice(row);
}

function chgQty(row) {
	if (!isNumeric(row)) {
		alert('ERROR: rowIndex is not supported in this browser. Please notify Retailtrading');
		return;
	}
	calculateEPT();
}

function getPrice(row) {
	var index=row;
	var product=document.getElementsByName('PRODUCT[]')[index].value,
		store=document.getElementById('STORE').value,
		docType=document.getElementById('DOCTYPE').value,
		price=document.getElementsByName('price[]');

	if (product=="") { price[index].value="No Product Entered"; return; }
	else if (store=="") { price[index].value="No Store Entered"; return; }

	price[index].value='Retrieving Price...';

	params='PRODUCTID='+product+'&STOREID='+store;

	parent.showMsgBoxSystemFeedback('Loading Price ...');
	$.ajax({
	  url: "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/getPrice.php?pAlias=<?php echo $principalAliasId ?>",
	  global: false,
	  type: 'POST',
          data: params,
          dataType: 'html',
	  cache: false,
	  success: function(msg){

	  	try {
	  		var innerIndex=index; // not really necessary beause this var is local so ajax will still be able to access same value
	  		eval(msg);

	  		if(msgClass.identifier2 == document.getElementsByName('PRODUCT[]')[innerIndex].value){
	  			if (msgClass.type=="S") { price[innerIndex].value=msgClass.identifier+"ea"; price[innerIndex].style.color="#7F7F7F";}
	  			else { price[innerIndex].value=msgClass.description; price[innerIndex].style.color='#FF0000'; }
	  			calculateEPT();
	  		}

	  	} catch (e) { alert('an unexpected error occurred:'+e.description+msg); }
	  	parent.hideMsgBoxSystemFeedback('Loading Price ...');
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
	  	  var innerIndex=index;
	  	  price[innerIndex].value="Failed to retrieve price";
		  //alert('Could not load price. '+textStatus+' - '+errorThrown);
		  parent.hideMsgBoxSystemFeedback('Loading Price ...');
	  }
  });
}


var prevFld;
function showSpecialDD(event, fld, fldName){
	switch (event.keyCode){
		case 32: {

                    if($('#ddProdSearchContent').attr('id') == undefined){
                    html = '<div style="display:block;font-size:12pt;height:35px;border-top:1px solid lightSkyblue;background:aliceBlue;">'+
                           'Search for Product: <input type="text" style="margin-top:8px;" id="ddProdSearchInput" name="ddProdSearchInput" value="test" onkeyup="suggest(\'ddProdSearchInput\'); showSpecialDD(event,\'ddProdSearchInput\',\'ddProdSearchInput\')" /> {ESC Exits}'+
                           '</div>'+
                           '<div id="ddProdSearchContent" style="border:1px solid lightSkyblue;border-left:0px;border-right:0px;height:295px;background:#fff;overflow:auto;"></div>';

                            showPopUp(html);
                            prevFld=fld;
                            $("#ddProdSearchInput").attr("value","");
                            $("#ddProdSearchInput").focus();
                    }
                            break;
		         }
		case 27: {
                            prevFld.focus();
                            $('#player').remove();
                            break;
		         }
		default: {
                            return;
		         }
	}
}

function selectDDVal(uid) {
        $('#player').remove();
	prevFld.focus();
	prevFld.value=uid;
	// if <0 then it is one of header fields (store)
	if ((prevFld.parentNode.parentNode.rowIndex-1)<0) {
	} else changeProduct(prevFld.parentNode.parentNode.rowIndex-1);
	startAutoSaveSession(); // make sure it has started
}

function suggest(fldName) {
	var list = new String();
	var matchCnt=0;
	var fullName;
	var fld=document.getElementsByName(fldName);
	var val=fld[0].value.toLowerCase();
	if (val.length==0) { parent.hideMsgBoxSystemFeedbackAll(); return; }
	var pattern = new RegExp(val.replace(/[^a-zA-Z0-9]+/g,'')); // leave only alpha chars and digits
	var lengthArr=0;
	switch (fldName) {
			case "ddProdSearchInput": lengthArr=ppArrPC.length;
									  contentFld="ddProdSearchContent";
							 break;
	}
	for (i=0; ((i<lengthArr) && (matchCnt<2000)); i++) {
		switch (fldName) {
			case "ddProdSearchInput": if (pattern.test(ppArrPC[i].replace(/[^a-zA-Z0-9]+/g,'').toLowerCase()+ppArrAC[i].replace(/[^a-zA-Z0-9]+/g,'').toLowerCase()+ppArrPD[i].replace(/[^a-zA-Z0-9]+/g,'').toLowerCase()<?php for($i=0;$i<count($minorLableArr);$i++){ echo '+ppArrMC[ppArrUID[i]]['.$i.'].replace(/[^a-zA-Z0-9]+/g,\'\').toLowerCase()'; } ?>)) {
							  list += "<tr style='border-bottom:1px solid lightskyblue;' onclick='selectDDVal("+ppArrUID[i]+");' onMouseOver='$(this).css(\"background\",\"#FCFFB4\");' onMouseOut='$(this).css(\"background\",\"#FFF\");'>"+
                                                                  "<td class='standardFont' style='padding:3px 5px;'><A href='javascript:' onclick='selectDDVal("+ppArrUID[i]+");' >"+ppArrPC[i]+"</A></td>"+
                                                                  "<td class='standardFont' style='padding:3px 5px;'>"+ppArrAC[i]+"</td>"+
                                                                  "<td class='standardFont' style='padding:3px 5px;'>"+ppArrPD[i]+"</td>"+<?php
                                                                  for($i=0;$i<count($minorLableArr);$i++){
                                                                   echo '"<td class=\'standardFont\' style=\'padding:3px 5px;\'>"+ppArrMC[ppArrUID[i]]['.$i.']+"</td>"+';
                                                                  }
                                                                  ?>"</tr>";

							  matchCnt++;
							  if (matchCnt>(2000-1)) list += "<tr><td colspan=2 class='tableReset standardFont' style='padding:5px;'><I><B>list incomplete...list exceeds 2000.</B></I></td></tr>";
							 }
							 fullName = 'Product(s)';
							 break;
		}
	}
	if (list.length > 0) {
		// user must use scroll wheel on mouse to scroll because otherwise onblur first to hide list
		list = "<table class='tableReset' width='100%' style='font-size:11px;'><tr style='border-bottom:1px solid lightskyblue;background:aliceBlue;'><th>Code</th><th>Alt Code</th><th>Description</th><?php foreach($minorLableArr as $mg){echo '<th>'.$mg.'</th>';} ?></tr>"+list+"</table>";
		document.getElementById(contentFld).innerHTML=list;
	} else {
		document.getElementById(contentFld).innerHTML="";
	}
}

var alreadySubmitted=false;
function submitContentForm(p_type,feedbackObj) {
	if (alreadySubmitted) {
		alert('You have already clicked on submit... If you are sure the capture has NOT been stored then you may click submit again after 2 minutes.');
		return;
	}
	alreadySubmitted=true;

	// perform a quick save incase of troubles / jscript errors later
	saveContent();

	resetErrorStyling();

	var params='DMLTYPE='+p_type;

	var fldOP=document.getElementsByName("OVERRIDE_PRICE[]");
	var hasOP=false;
	for (var i=0; i<fldOP.length; i++) {
		if (fldOP[i].value!='') {
			hasOP=true;
			break;
		}
	}
	if (hasOP) {
		ans=confirm('You have chosen to override some prices. Are you sure you want to submit ?');
		if (!ans) { alreadySubmitted=false; return; }
	}

	params+='&DOCMASTID=<?php echo (($isCOPYDOCUMENT)?"":$postDOCMASTID); ?>'; // only if come from transaction tracking amend
	params+='&STORE='+document.getElementById("STORE").value;
	params+='&DOCDATE='+document.getElementById("DOCDATE").value;
	params+='&DELDATE='+document.getElementById("DELDATE").value;
	params+='&DOCTYPE='+document.getElementById("DOCTYPE").value;
	params+='&DELINST='+document.getElementById("DELINST").value;
	params+='&CUSTREF='+document.getElementById("CUSTREF").value;
	params+='&DN='+document.getElementById("DN").value;
	params+='&PRODUCT='+convertElementToArrayOther(document.getElementsByName("PRODUCT[]"));
	params+='&QTY='+convertElementToArray(document.getElementsByName("QTY[]"));

	var arr = new Array(), fld=document.getElementsByName("COMMENT[]");
	for (var i=0; i<fld.length; i++) { arr.push(encodeURIComponent(fld[i].value)); }
	params+='&COMMENT='+arr;

	params+='&OVERRIDEPRICE='+convertElementToArray(document.getElementsByName("OVERRIDE_PRICE[]"));
	if ((feedbackObj.type=="W")) {
		parent.hideMsgBoxError();
		ans=confirm(feedbackObj.description.replace(/<BR>/g,'\n'));
		if (ans) params+='&CONFIRMOPTION=Y';
		else {
			alreadySubmitted=false;
			return false;
		}
	}

	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/orderSubmit.php?pAlias=<?php echo $principalAliasId ?>',
						  'alreadySubmitted=false; if(msgClass.type=="S") successfullyProcessed(msgClass.identifier); else if(msgClass.type=="E") errorProcessed(msgClass); else if(msgClass.type=="W") submitContentForm("'+p_type+'",msgClass);',
						  'Please wait while request is processed...');
}

function restartForm() {
	//document.getElementById('STORE').value="";

  $('#STORE').val('');
  $('#STORENAME').html('&nbsp;');

	document.getElementById('DELDATE').value=""; document.getElementById('feedbackDD').innerHTML="";

  document.getElementById('DELINST').value="";
	document.getElementById('CUSTREF').value="";
	document.getElementById('DN').value="";
	fld=document.getElementsByName('PRODUCT[]');
	for (i=0; i<fld.length; i++) {
		document.getElementsByName('PRODUCT[]')[i].value="";
		document.getElementsByName('PREQTY[]')[i].value="";
		document.getElementsByName('QTY[]')[i].value="";
		try { document.getElementsByName('COMMENT[]')[i].value=""; } catch(e) {}
		document.getElementsByName('price[]')[i].value="";
		document.getElementsByName("OVERRIDE_PRICE[]")[i].value="";
		document.getElementsByName("extPrice[]")[i].value="";
		hidePriceOverride(i);
	}
	resetErrorStyling();
	restartSession();
	calculateEPT();
	setDocumentNumberRequired();
}

function resetErrorStyling() {
	fld=document.getElementsByName('PRODUCT[]');
	for (i=0; i<fld.length; i++) {
		document.getElementsByName('PRODUCT[]')[i].style.backgroundColor='#FFFFFF';
		document.getElementsByName('QTY[]')[i].style.backgroundColor='#FFFFFF';
	}
}

function cancelForm() {
	var answer = confirm('Are you sure you wish to CONTINUE and CLEAR the saved session and the form? Select OK to clear, or CANCEL to go back.');
	if (answer) restartForm();
}

function successfullyProcessed(val) {
  recordCaptureAssist(val);
	restartForm();
	document.getElementById('prevSavedRefMsg').innerHTML="<A href='javascript:' onclick='printQuotation(\""+val+"\");' ><img src='<?php echo $DHTMLROOT.$PHPFOLDER; ?>images/print-icon.png' style='border-style: none' /></A><BR> Previous Saved Reference: "+val;
}

function errorProcessed(msgClass) {
	if ((msgClass.identifier=="") || (msgClass.identifier2=="")) return;
	if (msgClass.identifier=="P") {
		var fld=document.getElementsByName('PRODUCT[]')[msgClass.identifier2];
	} else if (msgClass.identifier=="Q") {
		var fld=document.getElementsByName('QTY[]')[msgClass.identifier2];
	}
	fld.focus(); // unfortunately when you close the error box, it loses focus
	fld.style.backgroundColor='#FF0000';

}

function printQuotation(val) {
	window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/presentations/presentationManagement.php?TYPE=<?php echo 'Quotation'; ?>&CSOURCE=C&FINDNUMBER='+val,'myOrder','scrollbars=yes,width=850,height=500,resizable=yes');

}

function addRow() {
	if (!document.getElementsByTagName) { alert('Your browser does not support dynamic row add. Please contact RetailTrading.'); return; }

	var tableBody = document.getElementById('dealTblBody');
	var root=tableBody.getElementsByTagName('tr')[0].parentNode; //the TBODY
	var clone=tableBody.getElementsByTagName('tr')[0].cloneNode(true); //the clone of the first row
	root.appendChild(clone); //appends the clone
	document.getElementsByName("PRODUCT[]")[totalRows].value="";
	document.getElementsByName("QTY[]")[totalRows].value="";
	document.getElementsByName("price[]")[totalRows].value="";
	document.getElementsByName("OVERRIDE_PRICE[]")[totalRows].value="";

    totalRows++;
    //adjustMyFrameHeight();  // dont put this here because it freezes the screen as the image positioner moves.
}

function deleteRow(row) {
	if (!document.getElementsByTagName) { alert('Your browser does not support dynamic row add. Please contact RetailTrading.'); return; }

	var tableBody = document.getElementById('dealTblBody'),
	    root=tableBody.getElementsByTagName('tr')[0].parentNode,
	    rowCnt=$('#dealTblBody').children('tr').size();

  if (row==-1) row=rowCnt-1;

  if ((rowCnt<=1) || (row==0)) {
  	alert('There must be atleast 1 row remaining, and the first row cannot be deleted.');
    return;
  }

  // must do it through JQuery so that nested table rows dont get included. Children only takes first level
  var rowPtr= $('#dealTblBody').children('tr')[row];
  root.removeChild(rowPtr); // passed val of -1 removes last row

  totalRows--;
}

function restoreContent() {

	var f0=readCookie("QCPRINCIPAL"); if (f0==null) f0="";
		f1=readCookie("QCSTORE"); if (f1==null) f1="";
	  f2=readCookie("QCDOCDATE"); if (f2==null) f2="";
		f3=readCookie("QCDOCTYPE"); if (f3==null) f3="";
		f4=readCookie("QCDELINST"); if (f4==null) f4=""; else f4=f4.replace(/###/g,'\n');
		f5=readCookie("QCCUSTREF"); if (f5==null) f5="";
		f6=readCookie("QCPRODUCT"); if (f6!=null) f6=f6.split(','); else f6=new Array();
		f7=readCookie("QCQTY"); if (f7!=null) f7=f7.split(','); else f7=new Array();
		f8=readCookie("QCDELDATE"); if (f8==null) f8="";
		f9=readCookie("QCOPRICE"); if (f9!=null) f9=f9.split(','); else f9=new Array();
		f10=readCookie("QDN"); if (f10==null) f10="";

	if (f0!='<?php echo $principalAliasId ?>') {
		alert('The saved session is for a different principal. Cannot restore.');
		return;
	}


	f1 = f1.split('|||');
	if(f1[1] === undefined){
	 alert('Error on Reading Store Data!');
	 return false;
	}

	document.getElementById('STORE').value = f1[0];
	$('#STORENAME').text(f1[1]);


	document.getElementById('DOCDATE').value=f2;
	document.getElementById('DOCTYPE').value=f3;
	document.getElementById('DELINST').value=f4;
	document.getElementById('CUSTREF').value=f5;
	document.getElementById('DN').value=f10;
	document.getElementById('DELDATE').value=f8;
	for (i=0; i<f6.length; i++) {
		if ((i+1)>totalRows) addRow();
		document.getElementsByName('PRODUCT[]')[i].value=f6[i];
		document.getElementsByName('QTY[]')[i].value=f7[i];
		document.getElementsByName('OVERRIDE_PRICE[]')[i].value=f9[i]; if(f9[i]!="") showPriceOverride(i);
	}

	startAutoSaveSession();
	alert('Content has been restored.');
	calculateEPT();
}

function restartSession(){
	createCookie("QCsession","INactive",3);
	autoSaveStarted=false;
	clearInterval(intervalTimer);
	document.getElementById('autoSaveMsg').innerHTML = 'AutoSaved last:';
}

function checkForRestore() {
  if ('<?php echo (($postDOCMASTID===false)?2:1); ?>'=='1') return;
	// check to see if there is a session
	if ((readCookie("QCsession")=="active") && (readCookie("QCPRINCIPAL")=='<?php echo $principalAliasId ?>')) {
		var answer = confirm('There is an active autorecovery session. Would you like to restore to this session ?');
		if (!answer) return "cancelled"; else restoreContent();
	}
}

function saveContent() {

	var f0='<?php echo $principalAliasId ?>';
	var f1=document.getElementById('STORE').value + '|||' + $('#STORENAME').text();
	var f2=document.getElementById('DOCDATE').value;
	var f3=document.getElementById('DOCTYPE').value;
	f4=document.getElementById('DELINST').value;
	f4=f4.replace(/\n+/g,'###').replace(/\r+/g,'');
	f5=document.getElementById('CUSTREF').value;
	f6=convertElementToArrayOther(document.getElementsByName("PRODUCT[]"));
	f7=convertElementToArrayOther(document.getElementsByName("QTY[]"));
	f8=document.getElementById('DELDATE').value;
	f9=convertElementToArrayOther(document.getElementsByName("OVERRIDE_PRICE[]"));
	f10=document.getElementById('DN').value;

	// to block against page change leaving timer still running
	if (f6.length==0) {
		autoSaveStarted=false;
		clearInterval(intervalTimer);
		return;
	}

	createCookie("QCsession","active",3);
	createCookie("QCPRINCIPAL",f0,3);
	createCookie("QCSTORE",f1,3);
	createCookie("QCDOCDATE",f2,3);
	createCookie("QCDOCTYPE",f3,3);
	createCookie("qCDELINST",f4,3);
	createCookie("QCCUSTREF",f5,3);
	createCookie("QCPRODUCT",f6,3);
	createCookie("QCQTY",f7,3);
	createCookie("QCDELDATE",f8,3);
	createCookie("QCOPRICE",f9,3);
	createCookie("QDN",f10,3);

	var date = new Date();
	document.getElementById('autoSaveMsg').innerHTML = 	'AutoSaved last: '+date.toLocaleString();

}

function startAutoSaveSession() {
	if (autoSaveStarted) return;
	autoSaveStarted=true;
	// do an autosave every 15 seconds
	intervalTimer=setInterval ("saveContent();", 15000);
}

function buildProductList() {
	var fld=document.getElementsByName("PRODUCT[]")[0];
  	for (var i=0; i<ppArrPC.length; i++) {
	  	var elOptNew = document.createElement('option');
	  	elOptNew.text = ppArrPC[i]+" - "+ppArrAC[i]+" - "+ppArrPD[i];
	  	elOptNew.value = ppArrUID[i];
	  	try {
		    fld.add(elOptNew, null); // standards compliant; doesn't work in IE
		 } catch(ex) {
		    fld.add(elOptNew); // IE only
		 }
	}
}

function buildStoreList() {

	var fld=document.getElementById("STORE");
  	var dd='';
  	for (var i=0; i<psArrDN.length; i++) {
	  	var elOptNew = document.createElement('option');
	  	if (psArrDD[i]==1) dd='Monday'; else if (psArrDD[i]==2) dd='Tuesday'; else if (psArrDD[i]==3) dd='Wednesday'; else if (psArrDD[i]==4) dd='Thursday'; else if (psArrDD[i]==5) dd='Friday'; else if (psArrDD[i]==6) dd='Saturday'; else if (psArrDD[i]==7) dd='Sunday'; else dd='';
	  	elOptNew.text = psArrSN[i]+" - "+psArrDN[i]+", "+dd;
	  	elOptNew.value = psArrUID[i];
	  	try {
		    fld.add(elOptNew, null); // standards compliant; doesn't work in IE
		 } catch(ex) {
		    fld.add(elOptNew); // IE only
		 }
	}
}

function setDefaultRows() {
	for (i=0; i < <?php echo ($totalRows-1); ?>; i++) {
		addRow();
	}
	adjustMyFrameHeight();
}

function showPriceOverride(row) {
	if (!isNumeric(row)) {
		alert('Pricing Override is not supported in this browser.');
		return;
	}
	var fld=document.getElementsByName('OVERRIDE_PRICE[]');
	var fldOldPrice=document.getElementsByName('price[]');
	fld[row].style.display='block';
	fldOldPrice[row].style.textDecoration='line-through';
	adjustMyFrameHeight();
}

function hidePriceOverride(row) {
	if (!isNumeric(row)) {
		alert('Pricing Override is not supported in this browser.');
		return;
	}
	var fld=document.getElementsByName('OVERRIDE_PRICE[]');
	var fldOldPrice=document.getElementsByName('price[]');
	fld[row].style.display='none';
	fld[row].value='';
	fldOldPrice[row].style.textDecoration='';
}
function calculateEPT() {
	var fP=document.getElementsByName('price[]'),
		fOP=document.getElementsByName('OVERRIDE_PRICE[]'),
		fQ=document.getElementsByName('QTY[]'),
		fEP=document.getElementsByName('extPrice[]'),
		tEP=0,
		tQ=0,
		tot=0;

	for (var i=0; i<fP.length; i++){
		tOP=fOP[i].value.replace(/[^0-9.]+/g,'');
		tQ=fQ[i].value.replace(/[^0-9.]+/g,'');
		tP=fP[i].value.replace(/[^0-9.]+/g,'');
		if ((tOP>0) && (tQ>0)) fEP[i].value=(tOP * tQ).toFixed(2);
		else if ((tP>0) && (tQ>0)) fEP[i].value=(tP * tQ).toFixed(2);
		else fEP[i].value='';

		if (isNumeric(parseFloat(fEP[i].value))) tot+=parseFloat(fEP[i].value);
	}

	document.getElementById('totalEP').innerHTML=tot.toFixed(2);
}

function setDocumentNumberRequired() {
	var dT=document.getElementById('DOCTYPE').value
		store=document.getElementById('STORE').value;

	var depot=psArrDPT[store];
	if (depot=="") {
		alert('The depot has not been specified for this store ! Cannot determine Document Sequence ! Please go to your store masterfiles and update the depot for this store.');
		return;
	} else if (depot=="undefined") {
		return;
	}

	var autoSeq=false, // default set later
			seqArr=new Array();
	<?php
		foreach ($mfASUsers as $r) {
			echo "seqArr.push({rs:'{$r["row_source"]}',type:'{$r["type"]}',dt:'{$r["document_type_uid"]}',dpt:'{$r["depot_uid"]}'});";
		}
	?>

	if (seqArr.length==0) {
	  autoSeq=true; // the default if no rows loaded at all - matched or unmatched
	} else {

			autoSeq=false; // the default because there are rows matched or unmatched - gets overridden in loop below
			for (var i=0; i<seqArr.length; i++) {

				if (
						((seqArr[i].dt.fulltrim()=='') || (seqArr[i].dt.split(',').findIndex(dT).toString()!="")) &&
			   		((seqArr[i].dpt.fulltrim()=='') || (seqArr[i].dpt.split(',').findIndex(depot).toString()!=""))
			   ) {

					// MUST follow same logic as in function principalDAO->usesDocumentNumberAutoSeq !
					if (seqArr[i].type=='E') {
							autoSeq=false;
					} else if (seqArr[i].type=='I') {
							autoSeq=true;
					} else {
							// type = null, or row_source (.rs) = 'NOT MATCHED'
					  	autoSeq=false; // same default as at start ;-) just for security
					}

					break; // only execute loop for 1st value !

				}

			}

	}

	var fld=document.getElementById('DN');
	if (autoSeq) {
		document.getElementById('autoSeqMsg').innerHTML='&nbsp;(Disabled : Allocated by AutoSeq)';
		fld.disabled=true;
		fld.readonly=true;
		fld.value='';
	} else {
		document.getElementById('autoSeqMsg').innerHTML='&nbsp;(Required)';
		fld.disabled=false;
		fld.readonly=false;
	}
}

// used to test whether the script was returned from before functions echoed
function dummy(){}

function createProduct(){
  showPopUp('');
  AjaxRefresh("action=INSERT&fromcapture=1","<?php echo $ROOT.$PHPFOLDER ?>functional/products/productForm.php",'playerPage','<h1>loading...</h1>', '');
}

function showPopUp(html){
  var player = '<div style="position:absolute;width:100%;height:100%;top:0px;left:0px;background:#efefef;" id="player" align="center">'+
                '<div style="width:700px;margin-top:40px;height:380px;overflow:auto;background:#fff;border:3px solid #444">'+
                '<div><input type="button" class="submit" value="Close" onclick="$(\'#player\').remove();" style="margin-top:10px;" ></div>'+
                '<div id="playerPage" style="padding:5px 5px;">'+html+'</div>'+
                '</div>' +
              '</div>';
  $('body').append(player);
}

var caDetail=new Array();
function recordCaptureAssist(dn) {
  var thisDoc=new Array(),
      ref=$("#CUSTREF").val();

  var p=document.getElementsByName('PRODUCT[]'),
      q=document.getElementsByName('QTY[]');

  for (var i=0; i<p.length; i++) {
    if (p[i].value!='') thisDoc.push({p:p[i].value,q:q[i].value});
  }
  caDetail.push({'dn':dn,'ref':ref,detail:thisDoc});

  var html='<table class="tableReset captureAssistTbl" style="font-size:11px;"><tr><th>Document</th><th>Reference</th><th>Action</th></tr>';
  for (var i=0; i<caDetail.length; i++) {
    html+='<tr><td>'+caDetail[i].dn+'</td><td>'+caDetail[i].ref+'</td><td><a href="#" onclick="useCaptureAssistProducts('+i+');">Use Products</a></td></tr>';
  }
  html+='</table>';
  $("#captureAssist").html(html);
}

function useCaptureAssistProducts(ndx) {
   ans=confirm('Are you sure you wish to clear the product list and use these products ?');
   if (!ans) { alreadySubmitted=false; return; }

   var p=document.getElementsByName('PRODUCT[]'),
       q=document.getElementsByName('QTY[]');

   // Delete all Rows. Leave 1 row so start at 1
   var rowCnt=p.length; // save it as if you delete a row it changes p.length
   for (var i=1; i<rowCnt; i++) {
     deleteRow(1); // always delete row 1 as it is like a stack when you remove one, the one above becomes that number
   }

   for (i=0; i<caDetail[ndx].detail.length; i++) {
     if (i>=p.length) addRow();
     p[i].value=caDetail[ndx].detail[i].p;
     q[i].value=caDetail[ndx].detail[i].q;
   }

}

// when coming from transaction tracking screen
function applyAmendFieldValues() {
  $('#STORE').val('<?php echo $TO->storeUId; ?>');
  $('#STORENAME').text('<?php echo $TO->storeName; ?>');
  $('#DOCDATE').val('<?php echo $TO->documentDate; ?>');
  $('#DELDATE').val('<?php echo $TO->deliveryDate; ?>');
  $('#DELINST').val('<?php echo str_replace(array("'","\n"), array("\\'","\\n"),$TO->deliveryInstruction); ?>');
  $('#CUSTREF').val('<?php echo $TO->customerReferenceNo; ?>');
  $('#DN').val('<?php echo $TO->documentNumber; ?>');

  var p=document.getElementsByName('PRODUCT[]'),
      q=document.getElementsByName('QTY[]'),
      pr=document.getElementsByName('OVERRIDE_PRICE[]'),
      epr=document.getElementsByName('extPrice[]'),
      c=document.getElementsByName('COMMENT[]');

  <?php
  foreach ($TO->detailArr as $key=>$d) {
    echo "if ({$key}>=p.length) addRow();
          showPriceOverride(pr[{$key}].parentNode.parentNode.rowIndex-1);
          setTimeout(function() {getPrice($key);},1000);";
    echo "p[{$key}].value={$d->productUId};";
    echo "q[{$key}].value={$d->quantity};";
    echo "pr[{$key}].value={$d->price};";
    echo "epr[{$key}].value={$d->extPrice};";
    echo "c[{$key}].value='{$d->comment}';";
  }
  ?>

}

adjustMyFrameHeight();

</SCRIPT>
<?php

echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />";





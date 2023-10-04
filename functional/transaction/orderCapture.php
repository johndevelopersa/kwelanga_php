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
include_once($ROOT.$PHPFOLDER."DAO/SpecialValidationDAO.php");

CommonUtils::getSystemConventions();


if (!isset($_SESSION)) session_start() ;
$principalId  = $_SESSION['principal_id'] ;
$userId       = $_SESSION['user_id'];
$systemId     = $_SESSION["system_id"];
$systemName   = $_SESSION['system_name'];
$isDesktop    = (($_SESSION["DESKTOP"]=="Y")?true:false);

//?
$principalType = $_SESSION['principal_type'];
$principalName = $_SESSION['principal_name'];
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$principalAliasName = (($_SESSION['principal_alias_name']=="")?$principalName:$_SESSION['principal_alias_name']);

$principalSylko="3";

$delTestBoxPrin = array(342);

if (in_array($principalId, $delTestBoxPrin)) {
   $delInsbox = 'Y';
} else {
   $delInsbox = 'N';
}

$postENABLEPRESELECT = (isset($_GET['ENABLEPRESELECT']) && $_GET['ENABLEPRESELECT'] == 1) ? true : false;
$postDOCTYPE = (isset($_GET['DOCTYPE']) && is_numeric($_GET['DOCTYPE'])) ? $_GET['DOCTYPE'] : false;


$dbConn = new dbConnect();
$dbConn->dbConnection();



#--------------------------------------------------------------------------------------------------------------------------


$postDMLTYPE="INSERT";

$principalAliasChosen = ((isset($_REQUEST["pAliasC"]))?trim(mysql_real_escape_string($_REQUEST["pAliasC"])):"N");

$totalRows=(($isDesktop)?5:2);
echo "<HTML>";
echo "<HEAD>";
if (!$isDesktop) echo "<link href='".$DHTMLROOT .$PHPFOLDER ." css/mobile.css' rel='stylesheet' type='text/css' />";
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
                  setFieldVisibility();
		}

  });

</script>
<?php DatePickerElement::getDatePickerLibs(); ?>
<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>
<!-- the initial zoom is different from the home.php page viewport as this screen needs a little more width as the green btns go off page -->
<meta name="viewport" content="initial-scale=0.8, minimum-scale=0.2, maximum-scale=3, width=device-width, height=device-height, target-densitydpi=device-dpi" />

<?php
echo "</HEAD>";
echo "<BODY style='".(($isDesktop)?"":"background:#eef6fc")."'><CENTER><BR>";

// check roles
$administrationDAO = new AdministrationDAO($dbConn);
$hasRole = $administrationDAO->hasRole($userId,$principalAliasId,ROLE_ORDER_CAPTURE);
if (!$hasRole) {
	echo "Sorry, you do not have permissions to CAPTURE!";
	return;
}
$hasRoleModifyDD = $administrationDAO->hasRole($userId,$principalAliasId,ROLE_OC_CAN_MODIFY_DELDATE);
$hasRolePriceOverride = $administrationDAO->hasRole($userId,$principalAliasId,ROLE_ALLOW_PRICE_OVERRIDE);
// end check roles

//system configure.
$scrPref = $administrationDAO->getAllFieldPreferences($principalId, $systemId, 'CAPTURE', $postDOCTYPE);


// get import preferences
$principalDAO = new PrincipalDAO($dbConn);
$mfPPref = $principalDAO->getPrincipalPreferences($principalAliasId); // importDAO changed to now always return row
$mfPCPref = $principalDAO->getAllPrincipalCapturePreferences($principalAliasId); // capture preferences - only returns a row if exists
$mfASUsers = $principalDAO->getSequenceControlUsers($principalAliasId, "", DS_CAPTURE, "", "");
$mfPDocType = $principalDAO->getPrincipalDocumentTypes($principalAliasId); // document types overrides if any

$pDT_ProformaPricing = array();
foreach ($mfPDocType as $r) {
  if (($r["proforma_pricing"]=="Y") && ($r["document_type_uid"]!=DT_ORDINV))
     $pDT_ProformaPricing[]=$r["document_type_uid"];
}

$preValidation = (isset($_SESSION["up_cPreValid"]) && $_SESSION["up_cPreValid"] == 'Y') ? true : false;
$orderNumberUnique = (isset($mfPPref[0]["order_number_unique"]) && $mfPPref[0]["order_number_unique"] == 'Y') ? 'Y' : 'N';


// START : DEPOT Users Principal Alias Selection
if ($principalType==PT_DEPOT) {
	if ($principalAliasChosen!="Y") {
		echo "<div style='background-color:#fcf99c;
											padding:10px;
											border:2px;
											border-style:solid;
											border-color:#f9ed47;
											display:inline-block;
											width:400px;' >";

					$mfP=BasicSelectElement::getLogonUserPrincipalDD("principal_alias_list",$principalAliasId,"N","N",null,null,null,$dbConn,$userId, $limitType = PT_PRINCIPAL);
					$content='<BR><BR><h3>Principal Alias:</h3><BR>'.$mfP[1].'<BR><BR>
							   <INPUT type="submit" class="submit" value="submit" onclick="submitPrincipalAlias();">
							   <BR><BR><BR>';
					$content=str_replace("\r\n","",$content); // javascript mustnt go over 1 line
					$content=str_replace("\n","",$content); // sureserver needs this
					echo $content;
					echo "<script type='text/javascript' defer>function submitPrincipalAlias() { var pStr=$('#principal_alias_list').val(); var p=pStr.split(','); window.location+='&pAlias='+p[0]+'&pAliasName='+p[1]+'&pAliasC=Y'; }</script>";

		echo "</div>";

		return;

	} else {

	  echo "<div style='background-color:#fcf99c;
											padding:10px;
											border:2px;
											border-style:solid;
											border-color:#f9ed47;
											display:inline-block;
											width:400px;' >
						Your Principal Alias is : {$principalAliasName} <a href='javascript:var l=window.location.toString(); window.location=l.replace(/[&]pAlias.*/g,\"\");'>change?</a>
					</div>";

	}

}
// END : DEPOT Users Principal Alias Selection

// previous Reference saved
echo "<DIV style='font-family:Verdana,Arial,Helvetica,sans-serif; color:grey; font-size:0.85em' id='prevSavedRefMsg' >Previous Saved Reference: </div>";

// autosave bar
echo "<DIV style='font-family:Verdana,Arial,Helvetica,sans-serif; color:grey; font-size:0.85em' id='autoSaveMsg' >AutoSaved last: </div>";


echo "<br>";

/*****************************************************************************************************
 ********************************************** CAPTURE FIELDS ***************************************
 *****************************************************************************************************/

if(isset($postENABLEPRESELECT) && $postENABLEPRESELECT == true && $postDOCTYPE == DT_ARRIVAL){
  $sncStore = 'Depot :';
} else {
  $sncStore = SNC::store . ' :';
}
$dateStr = CommonUtils::getUserDate();


/******************************************************************************************************************
 ****************************************** STORE *********************************************************
 ******************************************************************************************************************/

if ($isDesktop) {
  echo'<table border="0">';

  // the order header
    echo "<TR>";
  	echo "<TD>" . $sncStore . "</TD>";
  	echo "<TD>";
} else {

  echo "<div style='padding:10px;border-radius:8px;border:2px solid white;background:#c3d0da;margin:10px;'>";

  echo "<br><span class='mobile-label'>{$sncStore}</span><br>";

}


if(isset($postENABLEPRESELECT) && $postENABLEPRESELECT == true && $postDOCTYPE == DT_ARRIVAL){

  include_once($ROOT.$PHPFOLDER."DAO/ImportDAO.php");
  $importDAO = new ImportDAO($dbConn);

  if(!isset($_SESSION['depot_id'])){
    die("<strong>Empty/invalid selected depot.</strong><br>");
  } else {
    $depotId = $_SESSION['depot_id'];
    $storeArr = $importDAO->getPrincipalStoreByOldAccount($principalId,(VAL_PSM_OLD_ACCOUNT_PREFIX.$depotId),"");
    if(count($storeArr)==0){
      //create store
      include_once($ROOT.$PHPFOLDER."DAO/PostStoreDAO.php");
      $postStoreDAO = new PostStoreDAO($dbConn);
      $storeArr = $postStoreDAO->createPrincipalDepotStore($principalId, $depotId);

      if($storeArr->type != FLAG_ERRORTO_SUCCESS){
        $dbConn->dbQuery("rollback");
        die("<strong>Error occurred building principal-depot-store</strong><br>" . $storeArr->description);
      } else {
        $dbConn->dbQuery("commit");
        $arStoreName = $storeArr->object['deliverName'];
        $arStoreId = $storeArr->identifier;
      }
    } else {
      //var_dump($storeArr);
      $arStoreName = $storeArr[0]['deliver_name'];
      $arStoreId = $storeArr[0]['uid'];
    }


    //safeguard issues
    if(empty($arStoreId)){
      die("<strong>Empty/invalid depot-store id!</strong>");
    }

    //display hard coded depot-store uid.
    echo "<INPUT TYPE='hidden' id=\"STORE\" name=\"STORE\" value=\"{$arStoreId}\" >";
    echo '<div style="text-align:left;width:400px;font-size:12px;background-color:white;color:#047;border:1px solid gray;padding:1px 4px;float:left;" id="STORENAME">'.$arStoreName.'</div>';
  }

} else {


    echo "<INPUT TYPE='hidden' id=\"STORE\" name=\"STORE\" >";

    //----------------------------------------------------
    //STORE SEARCH
    //----------------------------------------------------
    //special_field_or & ean_code_or means search on these fields - store name is always enabled for search.
    //ean_code = display column	ean code.
    //Header Name ie: store_name = Store Name  : ucword and remove dashes.
    //columns are displayed in order of below - if uid is set to true will be col 1 by default.
      if ($isDesktop) $columnsArr = array('store_name','depot_name','delivery_day','rep_name','special_fields','special_field_or');
      else $columnsArr =  array('store_name','depot_name','delivery_day'); // mimimum for needed fields

    IntelliDDElement::selectStoreSearch("STORE", $columnsArr, false, 'selectStore',"store_name + ' - ' + depot_name+', '+((delivery_day!='Not Known')?delivery_day:'')",$showVendorStores=false,$urlString="pAlias={$principalAliasId}", $isDesktop);

    //Function for ONCLICK.
    ?>
    <script type='text/javascript'>
     function selectStore(uid,name,onhold){
         startAutoSaveSession();
         changeStore();
         setDelDay(name);
         <?php if($preValidation){echo 'PreValidateStoreHold(uid,name,onhold);';} ?>
     }
    </script>
    <?php

    //----------------------------------------------------
}

// only show for sales agents
if ($_SESSION["category"]==FLAG_SALESAGENT_USER) {
	echo "&nbsp;&nbsp;<A href='javascript:' onclick='showAgentPopup();' ><IMG src='{$DHTMLROOT}{$PHPFOLDER}images/agent-icon.png' alt='Search Stores across Principals' style='border-style:none;' /></A>";
	?>
	<script type='text/javascript'>
			function showAgentPopup() {
				var contentDivName='agentSearchDiv';
				var searchFldId='agentSearch';
				content='<BR>Please type store keywords, separated with a comma(s) for each word.<BR>Note:phrases are less accurate as subtle variations exist, and avoid words like AND.<BR><INPUT type=text id='+searchFldId+' value=\'\' />'
						+'<input type=submit class=submit value=\'submit\' onclick=\'content.getAgentStores("'+searchFldId+'","'+contentDivName+'");\' /><HR>'
						+'<DIV id='+contentDivName+'></DIV><BR>';
				parent.showMsgBoxContent(content);
				parent.document.getElementById(searchFldId).focus();
			}
			function getAgentStores(searchFldId,resultDiv) {
				var searchVal=parent.document.getElementById(searchFldId).value;
				AjaxRefreshHTML("SEARCHCRITERIA="+searchVal,
								"<?php echo $ROOT.$PHPFOLDER; ?>functional/stores/getAgentStoreSearch.php?pAlias=<?php echo $principalAliasId ?>",
								resultDiv,
								"Retrieving Store Search List...",
								"");
			}
	</script>
	<?php
}


if ($isDesktop) {
	echo "</TD>";
	echo "</TR>";
}

/******************************************************************************************************************
 ****************************************** DOCUMENT DATE *********************************************************
 ******************************************************************************************************************/

if ($isDesktop) {
  echo "<TR>";
	echo "<TD>Document Date :</TD>";
	echo "<TD>";
} else {

  echo "<br><span class='mobile-label'>Document Date :</span><br>";

}

DatePickerElement::getDatePicker("DOCDATE",$dateStr,false,$isDesktop);

if ($isDesktop) {
  echo "</TD>";
  echo "</TR>";
}


/******************************************************************************************************************
 ****************************************** DELIVERY DATE *********************************************************
 ******************************************************************************************************************/

$f = false;

if ($isDesktop) {

  echo "<TR " . GUICommonUtils::showHideField($scrPref,'DELDATE',$f, false) . ">";
	echo "<TD class='TR_DELDATE' style='display:none;'>Delivery Date :</TD>";
	echo "<TD class='TR_DELDATE' style='display:none;'>";

	if ($hasRoleModifyDD) DatePickerElement::getDatePicker("DELDATE","");
	else echo "<input id='DELDATE' name='DELDATE' type='text' size='10' maxlength='10' READONLY DISABLED />";
	echo "<div id='feedbackDD' style='color:red;'></div>";

	echo "</TD>";
	echo "</TR>";

} else {

  echo "<div " . GUICommonUtils::showHideField($scrPref,'DELDATE',$f, false) . ">";
  echo "<div class='TR_DELDATE' style='display:none;'><br><span class='mobile-label'>Delivery Date :</span><br></div>";
  echo "<div class='TR_DELDATE' style='display:none;'>";

  if ($hasRoleModifyDD) DatePickerElement::getDatePicker("DELDATE","",false,$isDesktop);
  else echo "<input id='DELDATE' name='DELDATE' type='text' size='10' maxlength='10' READONLY DISABLED />";
  echo "<div id='feedbackDD' style='color:red;'></div>";

  echo "</div></div>";

}


echo "<SCR"."IPT>";
// used for picking the next delivery day based on store chosen
echo "var delDayArr=new Array();";

for ($i=1; $i<=7; $i++) {
  $uDate=CommonUtils::getUserDate($i);
  $dOW=date("w",strtotime($uDate));
  echo 'delDayArr['.$dOW.']="'.$uDate.'";'."\r\n";
}
echo "function setDelDay(val) {
					if(!fieldVisibility2) return;

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

  echo "<TR ".GUICommonUtils::showHideField($scrPref,'DOCTYPE',$f,false).">";
	echo "<TD>Document Type :</TD>";
	echo "<TD>";

} else {

  echo "<div ".GUICommonUtils::showHideField($scrPref,'DOCTYPE',$f,false)."><br>
        <span class='mobile-label'>Document Type :</span>";

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
 ****************************************** SERVICE TYPE **********************************************************
 ******************************************************************************************************************/

if ($isDesktop) {

  echo "<TR ".GUICommonUtils::showHideField($scrPref,'SERVICETYPE',$f,false).">";
  echo "<TD>Service Type :</TD>";
  echo "<TD>";

} else {
	

  echo "<div ".GUICommonUtils::showHideField($scrPref,'SERVICETYPE',$f,false)."><br>
        <br><br><span class='mobile-label'>Service Type :</span><br>";

}


echo "<div class='select'>";
BasicSelectElement::getDocumentServiceTypes("SERVICETYPE","","N","N","",null,null,$dbConn,$userId,$principalAliasId);
echo "</div>";

if ($isDesktop) {
  echo "</TD>";
  echo "</TR>";
} else {
  echo "</div>";
}

/******************************************************************************************************************
 ****************************************** REP CODE **********************************************************
 ******************************************************************************************************************/

if ($isDesktop) {

  echo "<TR ".GUICommonUtils::showHideField($scrPref,'REPCODE',$f,false).">";
  echo "<TD>Over Ride Sales Code :</TD>";
  echo "<TD>";

} else {

  echo "<div ".GUICommonUtils::showHideField($scrPref,'REPCODE',$f,false)."><br>
        <br><br><span class='mobile-label'>Over Ride Sales Code :</span><br>";

}

echo "<div class='select'>";
BasicSelectElement::getDocumentRepCodes("REPCODE","","N","N","",null,null,$dbConn,$principalAliasId);
echo "</div>";

if ($isDesktop) {
  echo "</TD>";
  echo "</TR>";
} else {
  echo "</div>";
}

/******************************************************************************************************************
 ****************************************** WAREHOUSE CODE **********************************************************
 ******************************************************************************************************************/

if ($isDesktop) {

  echo "<TR ".GUICommonUtils::showHideField($scrPref,'DEPOTCODE',$f,false).">";
  echo "<TD>Over Ride Sales Code :</TD>";
  echo "<TD>";

} else {

  echo "<div ".GUICommonUtils::showHideField($scrPref,'DEPOTCODE',$f,false)."><br>
        <br><br><span class='mobile-label'>Over Warehouse Code :</span><br>";

}

echo "<div class='select'>";
BasicSelectElement::getUserDepotsForPrincipalDD("DEPOTCODE","","N","N","",null,null,$dbConn,$userId,$principalAliasId);
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

if(isset($postENABLEPRESELECT) && $postENABLEPRESELECT == true && $postDOCTYPE == DT_ARRIVAL){
  $sncDeliveryInst = 'Received From';
} else {
  $sncDeliveryInst = 'Delivery Instruction';
}

if ($isDesktop) {
  echo "<TR>";
	echo "<TD>" . $sncDeliveryInst . " :</TD>";
	echo "<TD>";
	if($delInsbox == 'Y') {
	     echo '<textarea rows="3" cols="50" maxlength="250" id="DELINST" name="DELINST" value="" ></textarea>';	
	} else {
       echo '<input id="DELINST" name="DELINST" type="text" size="50" maxlength="100" value="" >';
  }
	echo "</TD>";
	echo "</TR>";
} else {
  echo "<br><span class='mobile-label'>{$sncDeliveryInst} :</span><br>";
  echo '<input id="DELINST" name="DELINST" type="text" style="width:100%" maxlength="100" value="" >';
}


/******************************************************************************************************************
 ****************************************** CUSTOMER REFERENCE ****************************************************
 ******************************************************************************************************************/


if(isset($postENABLEPRESELECT) && $postENABLEPRESELECT == true && $postDOCTYPE == DT_ARRIVAL){
  $sncCustomerRef = 'Goods Received Note';

} else {
  $sncCustomerRef = 'Reference No';
}

if ($isDesktop) {
  echo "<TR>";
	echo "<TD>" . $sncCustomerRef . " :</TD>
			  <TD>";
} else {
  echo "<br><br><span class='mobile-label'>{$sncCustomerRef} :</span><br>";
}

if($preValidation){
  echo "<input id=\"CUSTREF\" name='CUSTREF' type='text' size='20' maxlength='20' value='' onBlur=\"PreValidateOrderNo(this.value)\" >
        <span id=\"preValidId\"></span></TD>";
} else {
  echo "<input id=\"CUSTREF\" name='CUSTREF' type='text' style='width:100%' maxlength='25' value=''></TD>";
}


if ($isDesktop) {
echo "</TD>
      </TR>";
}



/******************************************************************************************************************
 ****************************************** DOCUMENT NUMBER ****************************************************
 ******************************************************************************************************************/

if ($isDesktop) {
  echo "<TR " . GUICommonUtils::showHideField($scrPref,'DN',$f, false) . ">";
	echo "<TD>Document Number :</TD>";
	echo "<TD>";
	echo "<input id=\"DN\" name='DN' type='text' size='10' maxlength='10' value='' >";
} else {
  echo "<div " . GUICommonUtils::showHideField($scrPref,'DN',$f, false) . "><br><span class='mobile-label'>Document Number :</span><br>";
  echo "<input id=\"DN\" name='DN' type='text' style='width:100%' maxlength='10' value='' >";
}

echo "<span id='autoSeqMsg' style='color:".COLOR_UNOBTRUSIVE_INFO."'></span>";

if ($isDesktop) {
  echo "</TD>
        </TR>";
} else {
  echo "</div>";
}


/******************************************************************************************************************
 ****************************************** OPTIONAL FIELDS *******************************************************
 ******************************************************************************************************************/

// optional fields - display none causes a column misalignment in Chrome if we hide the TR, so put it on the td level. We can't use visibility property because we mustn't preserve the space which visibility property does
if ($isDesktop) {

  echo "<TR>";
	echo "<TD class='TR_CLIENTSOURCEDOCUMENT' style='display:none;'>Source Document Number (cross reference) :</TD>";
	echo "<TD class='TR_CLIENTSOURCEDOCUMENT' style='display:none;'>
        <input id=\"CLIENTSOURCEDOCUMENT\" name='CLIENTSOURCEDOCUMENT' onchange='onChange_clientSourceDocument();' type='text' size='8' maxlength='8' value='' >
        <span id=\"preloadSrcDoc\"></span>
        </TD>";
  echo "</TR>";

} else {

  echo "<br>
        <span class='mobile-label'>Source Document Number (cross reference) :</span>
        <br>
        <input id=\"CLIENTSOURCEDOCUMENT\" name='CLIENTSOURCEDOCUMENT' onchange='onChange_clientSourceDocument();' type='text' style='width:100%' maxlength='8' value='' >
        <span id=\"preloadSrcDoc\"></span>";

}

if ($isDesktop) {

  // this is needed as otherwise chrome doesnt "finish off" the bottom of the table (underline border) if optional fields hidden
  echo "<tr><td colspan=2></td></tr>";
  echo "</table>";

} else {

  echo "</div>"; // padding wrapper div

}

echo "<br><br>";



/******************************************************************************************************************
 ****************************************** PRODUCT / DETAIL SECTION **********************************************
 ******************************************************************************************************************/


if ($isDesktop) {
  echo '<table border="0" id="dealTbl">';

  echo "<thead>";
  echo "<TR>";
  echo "<TH scope=col>&nbsp;</TH>";
  echo "<TH scope=col>Product " . (($postDOCTYPE == DT_ARRIVAL)?("<input type='button' name='addmore' id='addmore' class='submit' value='Create New Product' onclick='createProduct();' style='float:right'/>"):("")) ."</TH>";
  echo "<TH scope=col>Quantity</TH>";
  echo "<TH scope=col ".GUICommonUtils::showHideField($scrPref,'PRICE',$f, false).">Price</TH>";
  echo "<TH scope=col ".GUICommonUtils::showHideField($scrPref,'EXTPRICE',$f, false).">Ext Price<br><span style='font-size:8px;'>*excl Bulk Disc</span></TH>";
  echo "<TH scope=col ".GUICommonUtils::showHideField($scrPref,'PALLET',$f, false)." >Pallets</TH>";
  echo "<TH scope=col ".GUICommonUtils::showHideField($scrPref,'STOCK',$f, false).">Stock<br>Avail.</TH>";
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
        <td><input id='q0' name='QTY[]' type='text' size='6' maxlength='6' value='' onchange='chgQty(this.parentNode.parentNode.rowIndex-1);' ></td>
        </tr></table>
				</TD>";

  // *** BE CAREFUL. This TD Cell uses ParentNode and it MUST point to the TR to get rowIndex
  // can also use alert($(this).closest("tr").prevAll("tr").length + 1)
  echo "<TD ".GUICommonUtils::showHideField($scrPref,'PRICE',$f, false)."><INPUT type='text' name='price[]' style='border-style:none; color:grey;' value='' READONLY />
			  <INPUT type='text' name='OVERRIDE_PRICE[]' size='5' maxlength='8' value='' style='display:none;' value='' onchange='chgQty(this.parentNode.parentNode.rowIndex-1);' ".(($hasRolePriceOverride)?"":" READONLY ")."/>
  			  <img src='{$DHTMLROOT}{$PHPFOLDER}images/modify-icon.png' alt='Override Price' onclick='showPriceOverride(this.parentNode.parentNode.rowIndex-1);' />
  			  <img src='{$DHTMLROOT}{$PHPFOLDER}images/modify-cancel-icon.png' alt='Cancel Override' onclick='hidePriceOverride(this.parentNode.parentNode.rowIndex-1);' /></TD>"; // readonly instead of disabled allows font color to be set to red later


  echo "<TD ".GUICommonUtils::showHideField($scrPref,'EXTPRICE',$f, false)."><INPUT style='text-align:right;' type='text' name='extPrice[]' size='7' maxlength='7' style='border-style:none; color:grey;' value='' READONLY /></TD>";
  echo "<TD ".GUICommonUtils::showHideField($scrPref,'PALLET',$f, false)." ><INPUT type='text' name='pallets[]' size='5' maxlength='5' style='border-style:none; color:grey;' value='' READONLY /></TD>";
  echo "<TD ".GUICommonUtils::showHideField($scrPref,'STOCK',$f, false)."><INPUT type='text' name='stock[]' size='10' maxlength='10' style='border-style:none; color:grey;' value='' READONLY /></TD>";
  echo "</TR>";
  echo "</tbody>";



  // total line
  echo "<tbody id='totals' style=\"font-size: 11px; " . GUICommonUtils::showHideField($scrPref,'TOTAL',$f, true)."\">";
  echo "<TR >";
  echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'></TH>";
  echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'></TH>";
  echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'></TH>";
  echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'></TH>";
  echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;' ><DIV id='totalEP' style='font-weight:bold;'></DIV></TH>";
  echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'><DIV id='totalPallets' style='font-weight:bold;'></DIV></TH>";
  echo "<TH style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'></TH>";
  echo "</TR>";
  echo "</tbody>";

  echo "<tfoot>";
  echo "<tr>".
      "<td colspan=7>
      <input type='button' name='addmore' id='addmore' class='submit' value='Add Another Row' onclick='addRow(); adjustMyFrameHeight();'/>
      <input type='button' name='delmore' id='delmore' class='submit' value='Delete Last Row' onclick='deleteRow(-1); adjustMyFrameHeight();'/>
      <span ".GUICommonUtils::showHideField($scrPref,'PALLET',$f, false)." ><input type='button' name='rcpallets' id='rcpallets' class='submit' value='Recalculate Pallets' onclick='recalcPallets();'/></span>
      <span ".GUICommonUtils::showHideField($scrPref,'PRICE',$f, false)."><input type='button' name='cEPT' id='rcpallets' class='submit' value='Calculate Extended Price Total' onclick='calculateEPT();'/></span>
      <span ".GUICommonUtils::showHideField($scrPref,'PROFORMA',$f, false)." > <input type='button' name='proformaPrice' id='proformaPrice' class='submit' value='View Proforma Invoice' onclick='proformaPrice();'/></span></td>".
        "</tr>";
  echo "</tfoot>";

  echo "</table>";


} else {

  echo "<div style='padding:10px;border-radius:8px;border:2px solid white;background:#c3d0da;margin:10px;'>";

  echo '<table border="0" id="dealTbl" class="tableReset">';

  echo "<tbody id='dealTblBody' style=\"font-size: 11px;\">";

  echo "<tr id='tr_0' name='tr_0' ><td style='border-bottom:1px solid white;'><br>";


      echo "<center><img src='{$DHTMLROOT}{$PHPFOLDER}images/delete-icon.png' style='width:24px;height:24px;' alt='Delete Row' onclick='deleteRow($(this).closest(\"tr\").prevAll(\"tr\").length);' /></center><br><br>";

      //this.parentNode.parentNode.rowIndex-1
      echo "<div class='select'>
    			  <SELECT id=\"PRODUCT[]\" name=\"PRODUCT[]\" onchange='changeProduct($(this).closest(\"tr\").prevAll(\"tr\").length);' onKeyUp='showSpecialDD(event,this,\"ddProdSearchInput\");'>
      		  <OPTION value='' SELECTED>Not Selected</OPTION>
      		  </SELECT>
    			  </div>";

      echo "<br>";

      echo "<div id='tdq0'>
            <input name='PREQTY[]' type='number' style='display:none;width:100%;' maxlength='5' value='' DISABLED READONLY >
            <input id='q0' name='QTY[]' type='number' style='width:100%;' maxlength='5' value='' onchange='chgQty($(this).closest(\"tr\").prevAll(\"tr\").length);' placeholder='Qty'>
    		    </div>
            </br>";

      // *** BE CAREFUL. This TD Cell uses ParentNode and it MUST point to the TR to get rowIndex
      // can also use alert($(this).closest("tr").prevAll("tr").length + 1)
      echo "<div style='".GUICommonUtils::showHideField($scrPref,'PRICE',$f, true)." display:table;width:100%;' >
            <div style='display:table-cell;'><INPUT type='text' name='price[]' style='border-style:none;color:grey;width:100%;' value='' placeholder='Price' READONLY /></div>
    			  <div style='display:table-cell;'><INPUT type='text' name='OVERRIDE_PRICE[]' maxlength='8' value='' style='display:none;width:100%;' value='' onchange='chgQty($(this).closest(\"tr\").prevAll(\"tr\").length);' ".(($hasRolePriceOverride)?"":" READONLY ")." placeholder='Override Price'/>
      			  <img src='{$DHTMLROOT}{$PHPFOLDER}images/modify-icon.png' style='width:24;height:24px;' alt='Override Price' onclick='showPriceOverride($(this).closest(\"tr\").prevAll(\"tr\").length);' />
      			  &#160;&#160;&#160;&#160;&#160;
      			  <img src='{$DHTMLROOT}{$PHPFOLDER}images/modify-cancel-icon.png' style='width:24;height:24px;' alt='Cancel Override' onclick='hidePriceOverride($(this).closest(\"tr\").prevAll(\"tr\").length);' />
      			</div>
      			</div><br>"; // readonly instead of disabled allows font color to be set to red later

      echo "<div style='display:table;width:100%;border-spacing:5px;'>
            <div style='display:table-row;'>
            <div style='display:table-cell'>";
      echo "  <div ".GUICommonUtils::showHideField($scrPref,'EXTPRICE',$f, false)."><INPUT style='text-align:right;width:100%;' type='text' name='extPrice[]' size='7' maxlength='7' style='border-style:none; color:grey;' value='' READONLY placeholder='Ext Price' /></div>";
      echo "</div>
            <div style='display:table-cell'>";
      echo "  <div ".GUICommonUtils::showHideField($scrPref,'PALLET',$f, false)."><INPUT type='text' name='pallets[]' maxlength='5' style='border-style:none;color:grey;width:100%;' value='' READONLY placeholder='Pallets' /></div>";
      echo "</div>
            <div style='display:table-cell'>";
      echo "  <div ".GUICommonUtils::showHideField($scrPref,'STOCK',$f, false)."><INPUT type='text' name='stock[]' maxlength='10' style='border-style:none;color:grey;width:100%;' value='' READONLY placeholder='Stock' /></div>
            </div>
            </div>
            </div>";


  echo "<br><br></td></tr></tbody></table>"; // table+wrapper


  // total line
  echo "<div id='totals' style=\"font-size: 11px; " . GUICommonUtils::showHideField($scrPref,'TOTAL',$f, true)."\">";
  echo " <div style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'><DIV id='totalEP' style='font-weight:bold;'></DIV></div>";
  echo " <div style='border:1; border-top:2; border-color:#87CEFA; border-style:solid; background:#F0F8FF;'><DIV id='totalPallets' style='font-weight:bold;'></DIV></div>";
  echo "</div>";


  echo "</div><BR>"; // panel wrapper

  echo "<br>";

  // action buttons
  echo "<div style='display:table;width:100%;border-spacing:10px;'>

        <div style='display:table-row;'>
        <div style='display:table-cell;width:50%;'>
          <input type='button' name='addmore' id='addmore' class='mobile-opt-button' value='Add Another Row' onclick='addRow(); adjustMyFrameHeight();'/>
        </div>
        <div style='display:table-cell;width:50%;'>
          <input type='button' name='delmore' id='delmore' class='mobile-opt-button' value='Delete Last Row' onclick='deleteRow(-1); adjustMyFrameHeight();'/>
        </div>
        </div>

        <div style='display:table-row;'>
        <div style='display:table-cell;width:50%;'>
          <span ".GUICommonUtils::showHideField($scrPref,'PALLET',$f, false)."><input type='button' name='rcpallets' id='rcpallets' class='mobile-opt-button' value='Recalculate Pallets' onclick='recalcPallets();'/></span>
        </div>
        <div style='display:table-cell;width:50%;'>
          <span ".GUICommonUtils::showHideField($scrPref,'PRICE',$f, false)."><input type='button' name='cEPT' id='rcpallets' class='mobile-opt-button' value='Calculate Extended Price Total' onclick='calculateEPT();'/></span>
        </div>
        </div>

        <div style='display:table-row;'>
        <div style='display:table-cell;width:50%;'>
          <span ".GUICommonUtils::showHideField($scrPref,'PROFORMA',$f, false)."> <input type='button' name='proformaPrice' id='proformaPrice' class='mobile-opt-button' value='View Proforma Invoice' onclick='proformaPrice();'/></span></td>
        </div>
	      </div>

	      </div>";

}

$btnClass = (($isDesktop)?"submit":"mobile-submit-button");

echo "<br><br>
      <INPUT class='{$btnClass}' type=\"button\" onclick='submitContentForm(\"".$postDMLTYPE."\",\"\");' value=\"Submit\" />
      <br><br>
      <INPUT class='{$btnClass}' type=\"button\" onclick='cancelForm();' value=\"Cancel\" />
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

if($userId == 11) {
//     file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/capdebug.txt', "AAAA" , FILE_APPEND);
     $mfPS = $storeDAO->getUserPrincipalStoreArrayNew($userId,$principalAliasId,"");
} else {
//	   file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/capdebug.txt', "BBBB" , FILE_APPEND);
     $mfPS = $storeDAO->getUserPrincipalStoreArrayNew($userId,$principalAliasId,"");
}
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


// create the JS for inteli lookups - STORE
//echo "var psArrDA1=new Array();";  // DELIVER ADD1
//echo "var psArrDN=new Array();";  // DEPOT NAME
//echo "var psArrSN=new Array();";  // STORE NAME
//echo "var psArrDD=new Array();";  // DELDAY UID 1=monday...
//echo "var psArrUID=new Array();";
echo "var psArrDPT=new Object();"; // Depot UID, key is store UId
//$jsDA1="";
//$jsDN="";
//$jsSN="";
//$jsDD="";
//$jsUID="";
$jsDPT="";

foreach ($mfPS as $row) {
	//if ($jsUID=="") $jsUID="psArrUID=[\"".str_replace('"','',$row['psm_uid'])."\""; else $jsUID.=",\"".str_replace('"','',$row['psm_uid'])."\"";
	//if ($jsSN=="") $jsSN="psArrSN=[\"".str_replace('"','',$row['store_name'])."\""; else $jsSN.=",\"".str_replace('"','',$row['store_name'])."\"";
	//if ($jsDN=="") $jsDN="psArrDN=[\"".str_replace('"','',$row['depot_name'])."\""; else $jsDN.=",\"".str_replace('"','',$row['depot_name'])."\"";
	//if ($jsDA1=="") $jsDA1="psArrDA1=[\"".str_replace('"','',$row['deliver_add1'])."\""; else $jsDA1.=",\"".str_replace('"','',$row['deliver_add1'])."\"";
	//if ($jsDD=="") $jsDD="psArrDD=[\"".str_replace('"','',$row['dd_uid'])."\""; else $jsDD.=",\"".str_replace('"','',$row['dd_uid'])."\"";
	if ($jsDPT=="") $jsDPT="psArrDPT={\"".$row['psm_uid']."\":\"".$row['depot_uid']."\""; else $jsDPT.=",\"".$row['psm_uid']."\":\"".$row['depot_uid']."\"";
}
//if ($jsUID!="") $jsUID.="];";
//if ($jsSN!="") $jsSN.="];";
//if ($jsDN!="") $jsDN.="];";
//if ($jsDA1!="") $jsDA1.="];";
//if ($jsDD!="") $jsDD.="];";
if ($jsDPT!="") $jsDPT.="};";
//$combinedJS=$jsUID.$jsSN.$jsDN.$jsDA1.$jsDD.$jsDPT;
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

function PreValidateOrderNo(val){

  var storeId = $('#STORE').val();
  var doctypeId = $('#DOCTYPE').val();

  if(val!='' && storeId!='' && doctypeId!=''){

    $("#preValidId").html('<font color="#666">validating...</font>');

    $.ajax({
      url: "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/checkPreValidation.php?pAlias=<?php echo $principalAliasId ?>",
      global: false,
      type: 'POST',
      data: "CUSTREF="+val+"&STOREID="+storeId+"&DOCTYPE="+doctypeId+"&ORDERUNIQUE=<?php echo $orderNumberUnique ?>",
      dataType: 'html',
      cache: false,
      timeout: 6000,
      success: function(data){
        $("#preValidId").html('');
        try {
            eval(data);
            if(msgClass.identifier2 == $('#CUSTREF').val()){  //might have changed.
              if (msgClass.type=="S"){
               //do nothing on success
              } else if (msgClass.type=="W"){
               parent.popBox("<font color='black'>" + msgClass.description + "</font>",'warn');
              }
            }
        } catch (e) {
          $("#preValidId").html('<font color="red">Error</font>'+data);
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        $("#preValidId").html('<font color="red">Error</font>');
      }
  });
  }
}


function PreValidateStoreHold(uid,name,onhold){
  if((onhold!=undefined) && (onhold == 1)){
    setTimeout(function(){parent.popBox("<font color='black'>Warning: This store is placed ON HOLD:<br>"+name+" ("+uid+")<br><br>You may not capture orders to locations that are on-hold.</font>",'warn');},300);
  }
}

function resetCalculatedFields() {
  var docType=document.getElementById('DOCTYPE').value,
		fldq=document.getElementsByName('QTY[]'),
		fldp=document.getElementsByName('price[]');

	for (i=0; i<fldq.length; i++) {
		setPallets(i);
		if ((docType!=<?php echo DT_ORDINV; ?>) && (proformaPriceLookup.findIndex(docType).toString()=='')) {
			fldp[i].value='';
		} else {
			getPrice(i);
			getStock(i);
		}
	}
	calculateEPT();
  onChange_clientSourceDocument(); // requery the xref doc pre-fetch
}

function changeStore() {
  resetCalculatedFields();
}

function changeDocType() {
	setDocumentNumberRequired();
	setFieldVisibility();
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
	getStock(row);
	if (fldq[row]>0) setPallets(row); // only call if qty>0 to avoid error msg for qty not entered
}

function totalQty() {
	var fldpal=document.getElementsByName('pallets[]'),
	    total=0;

	for (i=0; i<fldpal.length; i++) {
		if (parseInt(fldpal[i].value)) {
			total+=parseInt(fldpal[i].value);
		}
	}
	if (total==0) {
		document.getElementById('totalPallets').innerHTML='-';
	} else {
		document.getElementById('totalPallets').innerHTML=total;
	}

	return true;
}

function chgQty(row) {
	if (!isNumeric(row)) {
		alert('ERROR: rowIndex is not supported in this browser. Please notify Retailtrading');
		return;
	}
	setPallets(row);
	totalQty();
	calculateEPT();
}

function setPallets(row) {
	if (row<0) return;

	var lineIndex=row;
	var quantity=document.getElementsByName('QTY[]')[lineIndex].value,
		docType=document.getElementById('DOCTYPE').value,
		productUId=document.getElementsByName('PRODUCT[]')[lineIndex].value;
	var index=ppArrUID.findIndex(productUId);

	if ((docType!=<?php echo DT_STOCKTRANSFER; ?>) && (docType!=<?php echo DT_DELIVERYNOTE; ?>) &&
		(docType!=<?php echo DT_ORDINV; ?>) && (docType!=<?php echo DT_ORDINV_ZERO_PRICE; ?>)) {
		document.getElementsByName('pallets[]')[lineIndex].value='';
		return true;
	}
	if (ppArrEPC[productUId]=='Y') {
		if (quantity>0) {
			if ((ppArrUPP[productUId]=="0") || (ppArrUPP[productUId]=="")) {
				alert('Product '+ppArrPC[index]+' requires a Pallet Configuration, but has not been specified with a unit value in masterfiles.');
				document.getElementsByName('pallets[]')[lineIndex].value='Invalid!';
				return false;
			}
			if ((quantity/ppArrUPP[productUId])!=Math.round(quantity/ppArrUPP[productUId])) {
				alert('This product requires a whole pallet load or whole multiple thereof. A pallet load for this product is specified at '+ppArrUPP[productUId]+' unit(s).');
				document.getElementsByName('pallets[]')[lineIndex].value='Invalid!';
				return false;
			} else {
				document.getElementsByName('pallets[]')[lineIndex].value=quantity/ppArrUPP[productUId];
				return true;
			}
		} else {
			alert('You have not entered a Quantity against a selected product.');
			document.getElementsByName('pallets[]')[lineIndex].value='';
			return false;
		}
	} else document.getElementsByName('pallets[]')[lineIndex].value='';
	return true;
}

function checkPallets() {
	var fldp=document.getElementsByName('PRODUCT[]'),
	    fldq=document.getElementsByName('QTY[]');

	for (i=0; i<fldp.length; i++) {
		if ((fldp[i].value!='') || (fldq[i].value!='')) {
			if (!setPallets(i)) return false;
		}
	}
	return true;
}

// possible bug on individual tally onChange, so allow user to initiate
function recalcPallets() {
	if (checkPallets()) totalQty();
}

function getPrice(row) {
	var index=row;
	var product=document.getElementsByName('PRODUCT[]')[index].value,
		store=document.getElementById('STORE').value,
		docType=document.getElementById('DOCTYPE').value,
		price=document.getElementsByName('price[]');

	if (
      (docType!=<?php echo DT_ORDINV; ?>) &&
      (proformaPriceLookup.findIndex(docType).toString()=='')
     ) return;

	if (product=="") { price[index].value="No Product Entered"; return; }
	else if (store=="") { price[index].value="No Store Entered"; return; }

	price[index].value='Retrieving Price...';

	params='PRODUCTID='+product+'&STOREID='+store+'&DOCTYPE='+docType;

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


function getStock(row) {
	var index=row;
	var product=document.getElementsByName('PRODUCT[]')[index].value,
		store=document.getElementById('STORE').value,
		docType=document.getElementById('DOCTYPE').value,
		stock=document.getElementsByName('stock[]');

	if (docType==<?php echo DT_UPLIFTS; ?>) return;

	if (product=="") { stock[index].value="No Product Entered"; return; }
	else if (store=="") { stock[index].value="No Store Entered"; return; }

	stock[index].value='Retrieving Stock...';

	params='PRODUCTID='+product+'&DEPOTID='+psArrDPT[store];

	parent.showMsgBoxSystemFeedback('Loading Stock ...');
	$.ajax({
	  url: "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/getStock.php?pAlias=<?php echo $principalAliasId ?>",
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
	  			if (msgClass.type=="S") { stock[innerIndex].value=msgClass.identifier; stock[innerIndex].style.color="#7F7F7F";}
	  			else { stock[innerIndex].value=msgClass.description; stock[innerIndex].style.color='#FF0000'; }
	  		}
	  	} catch (e) { alert('an unexpected error occurred:'+e.description+msg); }
	  	parent.hideMsgBoxSystemFeedback('Loading Stock ...');
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
	  	  var innerIndex=index;
	  	  stock[innerIndex].value="Failed to retrieve stock";
		  parent.hideMsgBoxSystemFeedback('Loading Stock ...');
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
		changeDocType();
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

	// check pallet consignment enforcement
	if (!checkPallets()) {
		alreadySubmitted=false;
		return false;
	}

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

	params+='&STORE='+document.getElementById("STORE").value;
	params+='&DOCDATE='+document.getElementById("DOCDATE").value;
	params+='&DELDATE='+document.getElementById("DELDATE").value;
	params+='&DOCTYPE='+document.getElementById("DOCTYPE").value;
	params+='&DELINST='+document.getElementById("DELINST").value;
  params+='&SERVICETYPE='+document.getElementById("SERVICETYPE").value;
  params+='&REPCODE='+document.getElementById("REPCODE").value;
	params+='&CUSTREF='+document.getElementById("CUSTREF").value;
	params+='&DN='+document.getElementById("DN").value;
	params+='&CLIENTSOURCEDOCUMENT='+document.getElementById("CLIENTSOURCEDOCUMENT").value;
	params+='&PRODUCT='+convertElementToArrayOther(document.getElementsByName("PRODUCT[]"));
	params+='&QTY='+convertElementToArray(document.getElementsByName("QTY[]"));
	params+='&PALLETS='+convertElementToArray(document.getElementsByName("pallets[]"));
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

<?php if(isset($postENABLEPRESELECT) && $postENABLEPRESELECT == true && $postDOCTYPE == DT_ARRIVAL){ ?>
    //nothing to do.
 <?php } else { ?>
   $('#STORE').val('');
   $('#STORENAME').html('&nbsp;');
 <?php } ?>

	//document.getElementById('DOCDATE').value="";
	document.getElementById('DELDATE').value=""; document.getElementById('feedbackDD').innerHTML="";
  document.getElementById('SERVICETYPE').value=0;	//not selected..
  document.getElementById('REPCODE').value=0;	//Default..
  
  <?php if(!$postENABLEPRESELECT){ ?>
      document.getElementById('DOCTYPE').value="";	//not selected..
	    document.getElementById('DOCTYPE').value=1;	//orders, invoice
	<?php } ?>
  document.getElementById('DELINST').value="";
	document.getElementById('CUSTREF').value="";
	document.getElementById('DN').value="";
	document.getElementById('CLIENTSOURCEDOCUMENT').value="";
	fld=document.getElementsByName('PRODUCT[]');
	for (i=0; i<fld.length; i++) {
		document.getElementsByName('PRODUCT[]')[i].value="";
		document.getElementsByName('PREQTY[]')[i].value="";
		document.getElementsByName('QTY[]')[i].value="";
		document.getElementsByName('price[]')[i].value="";
		document.getElementsByName('pallets[]')[i].value="";
		document.getElementsByName("stock[]")[i].value="";
		document.getElementsByName("OVERRIDE_PRICE[]")[i].value="";
		document.getElementsByName("extPrice[]")[i].value="";
		hidePriceOverride(i);
	}
	resetErrorStyling();
	totalQty();
	restartSession();
	calculateEPT();
	setDocumentNumberRequired();
	setFieldVisibility();

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
	document.getElementById('prevSavedRefMsg').innerHTML="<A href='javascript:' onclick='printOrder(\""+val+"\");' ><img src='<?php echo $DHTMLROOT.$PHPFOLDER; ?>images/print-icon.png' style='border-style: none' /></A><BR> Previous Saved Reference: "+val;
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

function printOrder(val) {
	window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/presentations/presentationManagement.php?TYPE=<?php echo ''; ?>&CSOURCE=C&FINDNUMBER='+val,'myOrder','scrollbars=yes,width=850,height=500,resizable=yes');

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
	document.getElementsByName("pallets[]")[totalRows].value="";
	document.getElementsByName("stock[]")[totalRows].value="";

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

	var f0=readCookie("OCPRINCIPAL"); if (f0==null) f0="";
		f1=readCookie("OCSTORE"); if (f1==null) f1="";
	  f2=readCookie("OCDOCDATE"); if (f2==null) f2="";
		f3=readCookie("OCDOCTYPE"); if (f3==null) f3="";
		f4=readCookie("OCDELINST"); if (f4==null) f4=""; else f4=f4.replace(/###/g,'\n');
		f5=readCookie("OCCUSTREF"); if (f5==null) f5="";
		f6=readCookie("OCPRODUCT"); if (f6!=null) f6=f6.split(','); else f6=new Array();
		f7=readCookie("OCQTY"); if (f7!=null) f7=f7.split(','); else f7=new Array();
		f8=readCookie("OCDELDATE"); if (f8==null) f8="";
		f9=readCookie("OCOPRICE"); if (f9!=null) f9=f9.split(','); else f9=new Array();
		f10=readCookie("ODN"); if (f10==null) f10="";
		f11=readCookie("OCSD"); if (f11==null) f11="";

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
	// restore optional / hidden fields
	document.getElementById('CLIENTSOURCEDOCUMENT').value=f11;

	startAutoSaveSession();
	alert('Content has been restored.');
	changeDocType();
	totalQty();
	calculateEPT();
}

function restartSession(){
	createCookie("OCsession","INactive",3);
	autoSaveStarted=false;
	clearInterval(intervalTimer);
	document.getElementById('autoSaveMsg').innerHTML = 'AutoSaved last:';
}

function checkForRestore() {
	// check to see if there is a session
	if ((readCookie("OCsession")=="active") && (readCookie("OCPRINCIPAL")=='<?php echo $principalAliasId ?>')) {
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
	f11=document.getElementById('CLIENTSOURCEDOCUMENT').value;

	// to block against page change leaving timer still running
	if (f6.length==0) {
		autoSaveStarted=false;
		clearInterval(intervalTimer);
		return;
	}

	createCookie("OCsession","active",3);
	createCookie("OCPRINCIPAL",f0,3);
	createCookie("OCSTORE",f1,3);
	createCookie("OCDOCDATE",f2,3);
	createCookie("OCDOCTYPE",f3,3);
	createCookie("OCDELINST",f4,3);
	createCookie("OCCUSTREF",f5,3);
	createCookie("OCPRODUCT",f6,3);
	createCookie("OCQTY",f7,3);
	createCookie("OCDELDATE",f8,3);
	createCookie("OCOPRICE",f9,3);
	createCookie("ODN",f10,3);
	createCookie("OCSD",f11,3);

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
function proformaPrice() {
	var params='CUSTREF='+encodeURIComponent(document.getElementById("CUSTREF").value);
	params+='&STORE='+document.getElementById("STORE").value;
	params+='&DOCTYPE='+document.getElementById("DOCTYPE").value;
	params+='&PRODUCT='+convertElementToArrayOther(document.getElementsByName("PRODUCT[]"));
	params+='&QTY='+convertElementToArray(document.getElementsByName("QTY[]"));
	params+='&OVERRIDEPRICE='+convertElementToArray(document.getElementsByName("OVERRIDE_PRICE[]"));

	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/proformaPrice.php?pAlias=<?php echo $principalAliasId ?>&'+params,'proformaPrice','scrollbars=yes,width=850,height=300,resizable=yes');
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

// just so that the field can be referenced outside without checking CSS
var fieldVisibility1=false,
		fieldVisibility2=false,
		fieldVisibility3=false;
function setFieldVisibility() {
	// 1 = client_source_document ; 2 = delivery date

	var cPArr = new Array(),
			dT=document.getElementById('DOCTYPE').value;
	<?php
		foreach ($mfPCPref as $r) {
			echo "cPArr.push({dt:'{$r["document_type_uid"]}',hf:'{$r["hide_field"]}'});";
		}
	?>

	for (var fieldType=1; fieldType<=3; fieldType++) {

		var show=false,
				foundSpecific=false;

		if (cPArr.length==0) {
		  show=true; // the default if no rows loaded at all - matched or unmatched
		} else {
				for (var i=0; i<cPArr.length; i++) {
					if (
							((cPArr[i].dt.fulltrim()=='') || (cPArr[i].dt==dT))
						) {

					  // rows can only be loaded for specific DT and not Principal
					  // - at most 2 rows should be loaded, blank and non-blank dT otherwise this will malfunction
						if (cPArr[i].dt==dT) {
						  foundSpecific=true; // specific dt overrides null
						  if (cPArr[i].hf.split(',').findIndex(fieldType).toString()!="") { show=false; } else { show=true; }
						} else if ((cPArr[i].dt.fulltrim()=='') && (!foundSpecific)) {
						  if (cPArr[i].hf.split(',').findIndex(fieldType).toString()!="") { show=false; } else show=true;
						}

					}

				}
		}

		if (show) {
			if (fieldType==1) {
				$('.TR_CLIENTSOURCEDOCUMENT').css({'display':'block'});
				fieldVisibility1=true;
			} else if (fieldType==2) {
			  $('.TR_DELDATE').css({'display':'block'});
			  fieldVisibility2=true;
			}
		} else {
			if (fieldType==1) {
				$('.TR_CLIENTSOURCEDOCUMENT').css({'display':'none'});
				$('#CLIENTSOURCEDOCUMENT').val(''); // only do this on hide, as restore session wont work then
				fieldVisibility1=false;
			} else if (fieldType==2) {
			  $('.TR_DELDATE').css({'display':'none'});
				$('#DELDATE').val(''); // only do this on hide, as restore session wont work then
				fieldVisibility2=false;
			}
		}

    // display the PREQTY[] column if XrefLookup
    if (requiresXrefLookup(dT)) {
      $('[name="PREQTY[]"]').css('display','block');
    } else {
      $('[name="PREQTY[]"]').css('display','none');
    }

	} // end fieldType loop

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

function onChange_clientSourceDocument() {
  // if the detailArr is preloaded then that means the capture is limited to these products only.
  // The validation is only enforced on the postOrder to cut down on bloat code and having to maintain two places.
  // Even if this logic fails, it will be safeguarded through validation on postOrder

  var storeId = $('#STORE').val(),
      doctypeId = $('#DOCTYPE').val(),
      srcDoc = $('#CLIENTSOURCEDOCUMENT').val();

  if(storeId=='' || doctypeId=='' || srcDoc==''){
     return;
  }

  $("#preloadSrcDoc").html('<font color="#666">fetching detail ...</font>');

  $.ajax({
    url: "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/getCapturePreloadProducts.php?pAlias=<?php echo $principalAliasId ?>",
    global: false,
    type: 'POST',
    data: "STOREID="+storeId+"&DOCTYPE="+doctypeId+"&SOURCEDOCUMENTNUMBER="+srcDoc,
    dataType: 'html',
    cache: false,
    timeout: 6000,
    success: function(data){
      $("#preloadSrcDoc").html('');
      try {
          eval(data);
          if (msgClass.type=="S"){
             // get the result set of products - returns detailArr[{productUId & orderedQty}]
             eval(msgClass.identifier);
             if (detailArr.length==0) {
               parent.popBox("<font color='black'>No detail Rows found for this Document</font>",'warn');
             } else {

               var p=document.getElementsByName('PRODUCT[]'),
                   q=document.getElementsByName('PREQTY[]');

               // Delete all Rows. Leave 1 row so start at 1
               var rowCnt=p.length; // save it as if you delete a row it changes p.length
               for (var i=1; i<rowCnt; i++) {
                 deleteRow(1); // always delete row 1 as it is like a stack when you remove one, the one above becomes that number
               }

               // add row for each product returned
               for (i=0; i<detailArr.length; i++) {
                 if (i>=p.length) addRow();
                 p[i].value=detailArr[i].productUId;
                 q[i].value=detailArr[i].orderedQty;
               }

             }
          } else if (msgClass.type=="E"){
           parent.popBox("<font color='black'>Failed to fetch Products for the following reason: " + msgClass.description + "</font>",'error');
          }
      } catch (e) {
        $("#preloadSrcDoc").html('<font color="red">Error</font>'+data+' '+e.description);
      }
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      $("#preloadSrcDoc").html('<font color="red">Error: Could not fetch Detail!</font>');
    }
});

}

function requiresXrefLookup(docTypeUId) {
  var result=false;

  if (docTypeUId=="") return false;

  <?php
      $arr=SpecialValidationDAO::orderCapture_getAllRequiresXRefLookup ($principalAliasId); // at the moment there is only one array element
      if (sizeof($arr>0)) {
        echo "var arr=[".(implode(",",$arr))."];";
        echo "if (arr.findIndex(docTypeUId).toString()!='') return true;";
      }
  ?>

  return result;
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

adjustMyFrameHeight();

</SCRIPT>
<?php

echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />";





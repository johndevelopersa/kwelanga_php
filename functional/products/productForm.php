<?php


  include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
  require($ROOT.$PHPFOLDER."functional/main/access_control.php");
  include_once($ROOT.$PHPFOLDER.'libs/common.php');
  include_once($ROOT.$PHPFOLDER.'DAO/PostProductDAO.php');
  include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
  include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
  include_once($ROOT.$PHPFOLDER.'TO/PostingProductTO.php');
  include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
  include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
  include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
  include_once($ROOT . $PHPFOLDER . 'elements/basicSelectElement.php');
  include_once($ROOT . $PHPFOLDER . 'DAO/DepotDAO.php');


  if (!isset($_SESSION)) session_start() ;
  $principalId = $_SESSION['principal_id'] ;
  $userId = $_SESSION["user_id"];
  $systemId = $_SESSION["system_id"];

  //Create new database object
  $dbConn = new dbConnect();
  $dbConn->dbConnection();

  $productDAO = new ProductDAO($dbConn);
  $adminDAO = new AdministrationDAO($dbConn);
  $fldPref = $adminDAO->getAllFieldPreferences($principalId, $systemId, 'PRODUCT');

  if (isset($_POST['action'])) $action=$_POST['action']; else $action="";
  if (isset($_POST['DMLTYPE'])) $postDMLTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DMLTYPE']));
  if ($action!="") $postDMLTYPE=$action; // to cater for direct navigation for Add
  if ($postDMLTYPE=="") $postDMLTYPE="INSERT";
  if (isset($_POST['LOADPRINPRODID'])) $postLOADPRINPRODID=$_POST['LOADPRINPRODID']; else $postLOADPRINPRODID="";
  if (isset($_POST['LOADPRINPRODID'])) $postLOADPRINPRODID=$_POST['LOADPRINPRODID']; else $postLOADPRINPRODID="";
  $postFROMCAPTURE = (isset($_POST['fromcapture']) && $_POST['fromcapture'] == 1) ? true : false;


  $hierarchyArr = array();
  if ($postLOADPRINPRODID=="") {

          if (isset($_POST['PRODCODE'])) $postPRODCODE=$_POST['PRODCODE']; else $postPRODCODE="";
          if (isset($_POST['PRODDESC'])) $postPRODDESC=$_POST['PRODDESC']; else $postPRODDESC="";
          if (isset($_POST['PACKING'])) $postPACKING= mysqli_real_escape_string($dbConn->connection, $_POST['PACKING']); else $postPACKING="";
          if (isset($_POST['PRODSKUGTIN'])) $postPRODSKUGTIN=$_POST['PRODSKUGTIN']; else $postPRODSKUGTIN=array('');
          if (isset($_POST['OCGTIN'])) $postOCGTIN=$_POST['OCGTIN']; else $postOCGTIN=array('');
          if (isset($_POST['PRODWGT'])) $postPRODWGT=$_POST['PRODWGT']; else $postPRODWGT="";
          if (isset($_POST['PRODVAT'])) $postPRODVAT=$_POST['PRODVAT']; else $postPRODVAT=(VAL_VAT_RATE*100);
          if (isset($_POST['AUTHVAT'])) $postAUTHVAT=$_POST['AUTHVAT']; else $postAUTHVAT="N";
          if (isset($_POST['STATUS'])) $postSTATUS = $_POST['STATUS']; else $postSTATUS="A";
          $postPRODEPOTGTIN = (isset($_POST['PRODEPOTGTIN'])) ? ($_POST['PRODEPOTGTIN']) : (array(''));
          if (isset($_POST['EPC'])) $postEPC = $_POST['EPC']; else $postEPC="N"; // enforce pallet consignment
          if (isset($_POST['UPP'])) $postUPP = $_POST['UPP']; else $postUPP="0"; // units per pallet
          if (isset($_POST['NSI'])) $postNSI = $_POST['NSI']; else $postNSI="N"; // Non Stock Item
          if (isset($_POST['WEB'])) $postWEB = $_POST['WEB']; else $postWEB="N"; // Show in shop
          if (isset($_POST['SHOPIFY'])) $postSHOPIFY = $_POST['SHOPIFY']; else $postSHOPIFY="N"; // Show in shop
          if (isset($_POST['ALLOWDECIMALS'])) $postALLOWDECIMALS = $_POST['ALLOWDECIMALS']; else $postALLOWDECIMALS="N"; // Allow Decimals
          if (isset($_POST['ALTCODE'])) $postALTCODE = $_POST['ALTCODE']; else $postALTCODE=""; // alternate code
          if (isset($_POST['PRODCAT1'])) $postPRODCAT1 = $_POST['PRODCAT1']; else $postPRODCAT1=""; // product category
          if (isset($_POST['ITEMSPERCASE'])) $postITEMSPERCASE = $_POST['ITEMSPERCASE']; else $postITEMSPERCASE="1"; // items per case

          $postUNITVALUE = (isset($_POST['UNITVALUE'])) ? ($_POST['UNITVALUE']) : (0);
          $postSIZETYPE = (isset($_POST['SIZETYPE'])) ? ($_POST['SIZETYPE']) : (0);
          $postSIZEWIDTH = (isset($_POST['SIZEWIDTH'])) ? ($_POST['SIZEWIDTH']) : (0);
          $postSIZELENGTH = (isset($_POST['SIZELENGTH'])) ? ($_POST['SIZELENGTH']) : (0);
          $postSIZEHEIGHT = (isset($_POST['SIZEHEIGHT'])) ? ($_POST['SIZEHEIGHT']) : (0);
          $postPRODUID = '0';

  } else {


    $mfP = $productDAO->getUserPrincipalProductItem($principalId, $postLOADPRINPRODID, $userId);

    if (sizeof($mfP)==0) {
      echo "Principal Product does not exist.";
      return;
    }

    $postPRODUID = $mfP[0]['uid'];
    $postPRODCODE = $mfP[0]['product_code'];
    $postPRODDESC = $mfP[0]['product_description'];
    $postPACKING = ($mfP[0]['packing']== "")?(''):($mfP[0]['packing']);
    $postPRODSKUGTIN = explode(',',$mfP[0]['sku_gtin_list']);
    $postPRODEPOTGTIN = explode(',',$mfP[0]['gtin_depot_uid_list']);
    $postOCGTIN = explode(',',$mfP[0]['outer_casing_gtin_list']);
    $postPRODWGT = $mfP[0]['weight'];
    $postPRODVAT = $mfP[0]['vat_rate'];
    $postAUTHVAT = ((trim($mfP[0]['vat_excl_authorised_by'])!="")?"Y":"N");
    $postSTATUS = $mfP[0]['status'];
    $postEPC = $mfP[0]['enforce_pallet_consignment'];
    $postUPP = $mfP[0]['units_per_pallet'];
    $postNSI = $mfP[0]['non_stock_item'];
    $postWEB = $mfP[0]['web_capture'];
    $postSHOPIFY  = $mfP[0]['load_to_shopify'];
    $postDISCOUNTS  = $mfP[0]['no_discount'];
    $postALLOWDECIMALS = $mfP[0]['allow_decimal'];
    $postALTCODE = $mfP[0]['alt_code'];
    $postPRODCAT1 = ($mfP[0]['major_category']==0)?(''):($mfP[0]['major_category']);
    $postITEMSPERCASE = ($mfP[0]['items_per_case']=="")?(1):($mfP[0]['items_per_case']);
    $postUNITVALUE = $mfP[0]['unit_value'];
    $postSIZETYPE = $mfP[0]['size_type'];
    $postSIZEWIDTH = $mfP[0]['size_width'];
    $postSIZELENGTH = $mfP[0]['size_length'];
    $postSIZEHEIGHT = $mfP[0]['size_height'];

  }

$mfPMGL = $productDAO->getProductMinorCategoryLables($principalId, $systemId);
	if ($postPRODVAT=="") $postPRODVAT="0";
  if ($postAUTHVAT=="") $postAUTHVAT="N";
	if ($postPRODWGT=="") $postPRODWGT="0";
	if ($postEPC=="") $postEPC="N";
	if ($postNSI=="") $postNSI="N";	
	if ($postWEB=="") $postWEB="N";	
	if ($postSHOPIFY=="") $postSHOPIFY="N";
	if ($postDISCOUNTS=="") $postDISCOUNTS="N";	
	if ($postALLOWDECIMALS=="") $postALLOWDECIMALS="N";
	if ($postUPP=="") $postUPP="0";


	$hasRoleDel=false;
	switch ($postDMLTYPE) {
		case "INSERT": {
						$hasRole = $adminDAO->hasRole($userId,$principalId,ROLE_ADD_PRODUCT);
						break;
					   }
		case "UPDATE": {
						$hasRole = $adminDAO->hasRole($userId,$principalId,ROLE_MODIFY_PRODUCT);
						$hasRoleDel = $adminDAO->hasRole($userId,$principalId,ROLE_DELETE_PRODUCT);
						break;
					   }
		case "VIEW":   {
						$hasRole = $adminDAO->hasRole($userId,$principalId,ROLE_DELETE_PRODUCT);
						break;
					   }
		default : $hasRole=false;
	}
	if (!$hasRole) {
		echo "You do not have permissions to ".$postDMLTYPE." a Product.";
		return;
	}
    echo "<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>";
    echo '<BR>';


	function drawSelect ($name, $arrName, $arrValue, $selected, $extra="") {
	$return = "";
	if ($extra != "") { $extra = " " . $extra; }
	$return .= "<SELECT name='$name'$extra>";
	$arrNameTotal = count($arrName);
	for ($i=0; $i<$arrNameTotal; $i++) {
		$selected =  ($selected == $arrValue[$i]) ? ('selected') : ('');
		$return .= "<OPTION ".$selected." value='" . $arrValue[$i] . "'>" . $arrName[$i] . "</option>";
	}
	$return .= "</SELECT>";

	return $return;
};


	// function toolbar
	if (($postDMLTYPE=="UPDATE") || ($postDMLTYPE=="VIEW")) {
	    echo '<div align="center"><INPUT type="submit" class="submit" value="Back to Products" onClick="backToProductList()"></DIV><BR>';
		echo "<SPAN style='font-family:Verdana,Arial,Helvetica,sans-serif; font-weight:bold;font-size:0.8em;'>";
		echo "<A style='color:grey;' href=\"javascript:;\" onClick=\"window.open('".$ROOT.$PHPFOLDER."functional/products/productCard.php?PRODUCTUID=".$postLOADPRINPRODID."','myPRODUCT','scrollbars=yes,width=320,height=400');\">view as popup</A>";
		echo " | <A style='color:grey;' href='javascript:;' onclick='emailProduct();'>email product details to self</A>";
		echo "</SPAN>";
	}

	echo "<TABLE style='line-height:20px;' id='productTableID'>";
	echo "<thead><tr>";
	echo "<th colspan=\"3\">{$postDMLTYPE} product".((($postDMLTYPE=="UPDATE") || ($postDMLTYPE=="VIEW")) ? ' <span style="float:right">'.$postPRODUID.'</span>':'')."</th>";
	echo "</tr></thead>";
	echo "<tbody style=\"font-size: 11px;\">";

	$class='odd';
	echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";


        /* ------------------------------*
         *        PRODUCT IMAGE          *
         * ------------------------------*/
        $templatePhoto = $ROOT . $PHPFOLDER . '/images/product_template.gif';
        $photoPath = $ROOT . $PHPFOLDER . 'uploads/products/' . $principalId .'_'. $postPRODUID .'.jpg';
        echo '<td rowSpan="20" valign="top" width="170" align="center" STYLE="border-right: 1px solid lightSkyBlue;"><BR>';
        if($postDMLTYPE != 'INSERT' && is_file($photoPath)){
          echo '<a href="javascript:displayPhoto()" title="Click to enlarge" id="productPhotoLink"><IMG SRC="'.$photoPath . '?' . uniqid() .'" width="150" height="150" id="productPhoto" border="0" style="border:1px solid #047;"></a>';
          echo '<br><a href="javascript:displayPhoto()">[ENLARGE]</a><br>';
          if($postDMLTYPE != 'VIEW'){ echo '<a href="javascript:uploadFile()">[CHANGE IMAGE]</a>'; }
        } else {
          echo '<IMG SRC="'.$templatePhoto.'" width="150" height="150" id="productPhoto" style="border:1px solid #047;">';
          if($postDMLTYPE != 'VIEW'){ echo '<a href="javascript:uploadFile()">[UPLOAD IMAGE]</a>'; }
        }
        echo '<br><div id="photomsg"></div>';
        echo '</td>';
        /* ------------------------------*/


          echo "<td width=\"180\">Product Code",GUICommonUtils::requiredField(),"</td>";
          if ($postDMLTYPE=="INSERT") echo "<td><INPUT type='text' size='30' maxlength='30' id='prod_code' value='" . $postPRODCODE. "' /></td>";
          else echo "<td><INPUT type='text' size='30' maxlength='30' id='prod_code' value='" . $postPRODCODE. "' disabled /></td>";
        echo "</tr>";


	echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
	echo "<td bgcolor=\"#87CEFA\">Alternate Product Code<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"This is an optional field. You can use it to type in another short identifier for the product (such as an item-code). It is not used for any lookups as a key, but will form part of the search in the capture screen.\")' onmouseout='parent.hideTip();' /></td>";
	echo "<td><INPUT type='text' size='30' maxlength='30' id='alt_code' value='" . $postALTCODE. "' /></td>";
	echo "</tr>";

	echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
	echo "<td>Description",GUICommonUtils::requiredField(),"</td>";
	echo "<td><INPUT type='text' size='40' maxlength='50' id='prod_desc' value='" . $postPRODDESC. "' /></td>";
	echo "</tr>";
  
  echo "<tr class=\"".GUICommonUtils::styleEO($class)."\" >";
	echo "<td>Packing<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"Description of stock.\")' onmouseout='parent.hideTip();' /></td>";
	echo "<td><INPUT type='text' size='10' maxlength='10' id='packing' value='" . $postPACKING. "' /></td>";
	echo '</tr>';

  echo "<tr class=\"".GUICommonUtils::styleEO($class)."\" ".GUICommonUtils::showHideField($fldPref,'CATEGORY',$class,false).">";
	echo "<td>Category</td>";
	echo '<td>';
	BasicSelectElement::getPrincipalProductCategoryDD('prod_category', $postPRODCAT1, '', '', '', '', '', $dbConn, $principalId, FLAG_STATUS_ACTIVE);
	echo '</td></tr>';

  echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
  echo "<td valign='top'>Minor Categories</td>";
  echo "<td style='padding:2px 0px;'>";
    BasicSelectElement::getProductMinorCategoryTree('prod_minor_category',$postPRODUID,"N","N",$onChange=null,$onClick=null,$onMouseOver=null,$dbConn,$principalId, $systemId);
    echo "</td></tr>";

    echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
	echo "<td>Weight</td>";
	echo "<td><INPUT type='text' size='15' maxlength='15' id='prod_wgt' value='" . $postPRODWGT. "' /></td>";
    echo "</tr>";

    echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
	echo "<td>Dimensions <IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='showDimensionsTooltip(this)' onmouseout='parent.hideTip();' /></td>";


	echo "<td>
            <table class='tableReset'><tr>
                <td valign='bottom' style='padding:0px 5px 0px 0px;'><br>" , BasicSelectElement::getMeasurementTypes('SIZETYPE',$postSIZETYPE, "N", "N", null, null, null) , "</td>
                <td style='padding:0px 5px; '>width: <br><INPUT type='text' size='7' maxlength='7' id='SIZEWIDTH' value='" . $postSIZEWIDTH. "' /></td>
                <td style='padding:0px 5px;'>length:<br> <INPUT type='text' size='7' maxlength='7' id='SIZELENGTH' value='" . $postSIZELENGTH. "' /></td>
                <td style='padding:0px 5px;'>height: <br><INPUT type='text' size='7' maxlength='7' id='SIZEHEIGHT' value='" . $postSIZEHEIGHT. "' /></td>
                </tr></table>
              </td>";
  echo "</tr>";

  echo "<tr class=\"".GUICommonUtils::styleEO($class)."\" ".GUICommonUtils::showHideField($fldPref,'VAT',$class,false).">";
	echo "<td>VAT Rate",GUICommonUtils::requiredField(),"</td>";
  echo "<td>
        <INPUT type='text' size='5' maxlength='5' id='prod_vatrate' value='" . $postPRODVAT. "' onchange='changeVAT();' onkeyup='changeVAT();'/>";
  $visible=(($postPRODVAT==0)?"block":"none");
  echo "<br><div id='divAuthVAT' style='display:{$visible}; line-height:15px;color:".COLOR_URGENT_TEXT."; font-size:10px'>I understand that VAT will be EXCLUDED<br>
        whenever an order for this product is created :";
  echo "<input type='checkbox' id='AUTHVAT' name='AUTHVAT' value='Y' ".(($postAUTHVAT=="Y")?" CHECKED ":"").">
        </div>";
  echo "</td>";
  echo "</tr>";

  echo "<tr class=\"".GUICommonUtils::styleEO($class)."\" >";
	echo "<td>Unit Value</td>";
	echo "<td><INPUT type='text' size='7' maxlength='7' id='UNITVALUE' value='" . $postUNITVALUE. "' /></td>";
  echo "</tr>";


	//-------------------------------------------------------------//

    echo "<tr>";
    echo "<td colSpan='2' style='padding:0px;background:aliceBlue;".GUICommonUtils::showHideField($fldPref,'GTIN',$class,true)."' >";

    //Build Depot -> DD
    $depotDAO = new DepotDAO($dbConn);
    $depotsArr = $depotDAO->getAllDepotsArray();  //a.uid, a.code, a.name depot_name

    function buildDepotDD($selectedValue = false){
      global $depotsArr;
      $depotLinkedGTIN_DD = '<SELECT style="width:147px;">';
      $depotLinkedGTIN_DD .= '<option value="" >All Depots</option>';
      foreach($depotsArr as $depotItem){
        $selected = ($selectedValue != false && $selectedValue == $depotItem['uid']) ? ('SELECTED') : ('');
        $depotLinkedGTIN_DD .=  '<option value="'.$depotItem['uid'].'" '.$selected.'>'.$depotItem['code'].' - '.$depotItem['depot_name'].'</option>';
      }
      $depotLinkedGTIN_DD .=  '</SELECT>';
      return $depotLinkedGTIN_DD;
    }


$depotDD = buildDepotDD();
echo <<<EOF
<script type="text/javascript">

//add blank input field to cell - id. - values picked up from 'each' cell id => input
function addDepotLnkGTINField(){
  var inputFld = " <INPUT type='text' size='30' maxlength='30' value='' style='width:145px;'/>";
  var depotSel = ' {$depotDD}';
  var cellObjD = $('#prodeplnkdepId');var cellObjE = $('#prodeplnkeanId');var cellObjG = $('#prodeplnkocgtinId');

  //expand table width : user can scroll right.
  var proTbObj = $('#productTableID');
  var curWd = proTbObj.css('width');
  curWd = Number(curWd.replace('px',''));
  proTbObj.css('width',curWd+155);

  //add fields
  cellObjD.append(depotSel);cellObjE.append(inputFld);cellObjG.append(inputFld);

}
</script>
EOF;


    	echo "<TABLE style='line-height:20px;margin:0px;border:0px;font-size:8pt;background:#fff;'>";
    	echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
    	echo "<td width='177' >Depot linked GTIN</td>";
    	echo "<td id='prodeplnkdepId'>";
    	foreach($postPRODEPOTGTIN as $PRODEPOTGTIN){
    	  echo buildDepotDD($PRODEPOTGTIN) . ' ';
    	}
    	echo "</td>";
    	echo '<td rowSpan="3" width="30" valign="middle" style="background:aliceBlue;padding:0px;">';
    	echo '<table style="padding:0px;border:0px;width:20px;" >
    			<tr><td style="padding:0px 5px;border-right:1px solid green;border-top:1px solid green;">&nbsp;</td><td></td></tr>
    			<tr><td colSpan="2" style="padding:2px" valign="center"><A href="javascript:addDepotLnkGTINField()" title="Add Another Set of Depot GTIN Fields"><img src="../../images/add_icon.png" width="24" height="24" border="0" style="display:block;padding:0px;margin:0px;margin-bottom:-2px;"></a></td></tr>
    			<tr><td style="padding:0px 5px;border-right:1px solid green;border-bottom:1px solid green;">&nbsp;</td><td></td></tr>
    		 </table>';
    	echo '</td>';
    	echo "</tr>";

    	echo "<tr class=\"".GUICommonUtils::styleEO($class)."\" style='border-left:0px;'>";
    	echo "<td >SKU GTIN (EAN)</td>";
    	echo "<td id='prodeplnkeanId'>";
    	foreach($postPRODSKUGTIN as $SKUGTIN){
    	  echo "<INPUT type='text' size='30' maxlength='30' value='" , $SKUGTIN , "' style=\"width:145px;\"/> ";
    	}
        echo "</TD>";
    	echo "</tr>";

    	echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
    	echo "<td>Outercasing GTIN</td>";
    	echo "<td id='prodeplnkocgtinId'>";
    	foreach($postOCGTIN as $OCGTIN){
    	  echo "<INPUT type='text' size='30' maxlength='30' value='" , $OCGTIN , "' style=\"width:145px;\"/> ";
    	}
    	echo "</td>";
    	echo "</tr>";
    	echo "</table>";
	echo "</td>";
	echo "</tr>";

    //-------------------------------------------------------------//

  echo "<tr class=\"".GUICommonUtils::styleEO($class)."\" >";
	echo "<td>Items per Case<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"If this product is a case description, then enter a 1, else if this product is an item product then enter the number of items per case if bulk deals are to be applied. Bulk deals are applied at a case level, so therefore the system needs to know how many items are in a case.\")' onmouseout='parent.hideTip();' /></td>";
	echo "<td><INPUT type='text' size='5' maxlength='5' id='itemspercase' value='" . $postITEMSPERCASE. "' /></td>";
	echo '</tr>';
  
  echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'EPC',$class,false)."><TD>Enforce Pallet Consignment<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"If activated, then when capturing an order, the quantity entered must be exactly a full pallet load or a whole multiple thereof. A full pallet load equates to the unit amount entered here on the next field. \")' onmouseout='parent.hideTip();' /></TD><TD>"; BasicInputElement::getGeneralHorizontalRB("EPC","Yes,No","Y,N",$postEPC,"N","N",null,null,null); echo "</TD></TR>";
	echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'UPP',$class,false)."><TD>Unit Qty equalling a pallet load</TD><TD><INPUT type='text' size='5' maxlength='5' id='UPP' value='" . $postUPP. "' /></TD></TR>";

 
  echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'NSI',$class,false)."><TD>Non Stock Item<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"If activated, then Stock report is nor effected by invoices - delivery notes or credits. \")' onmouseout='parent.hideTip();' /></TD><TD>"; BasicInputElement::getGeneralHorizontalRB("NSI","Yes,No","Y,N",$postNSI,"N","N",null,null,null); echo "</TD></TR>";
 
  echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'WEB',$class,false)."><TD>Show in Shop<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"Product will be displayed in online Shop. \")' onmouseout='parent.hideTip();' /></TD><TD>"; BasicInputElement::getGeneralHorizontalRB("WEB","Yes,No","Y,N",$postWEB,"N","N",null,null,null); echo "</TD></TR>";
 
  echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'SHOPIFY',$class,false)."><TD>Extract for Shopify<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"Product will be extracted for Shopify. \")' onmouseout='parent.hideTip();' /></TD><TD>"; BasicInputElement::getGeneralHorizontalRB("SHOPIFY","Yes,No","Y,N",$postSHOPIFY,"N","N",null,null,null); echo "</TD></TR>";

  echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'DISCOUNTS',$class,false)."><TD>Exclude from Discounts<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"Product will not attract any discount. \")' onmouseout='parent.hideTip();' /></TD><TD>"; BasicInputElement::getGeneralHorizontalRB("DISCOUNTS","Yes,No","Y,N",$postDISCOUNTS,"N","N",null,null,null); echo "</TD></TR>";

  echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'ALLOWDECIMALS',$class,false)."><TD>Allow Decimal Quantities<IMG src='".$DHTMLROOT.$PHPFOLDER."images/info-icon-small.png' onmouseover='parent.displayTip(this,5,135,\"Allow Decimal Quantities. \")' onmouseout='parent.hideTip();' /></TD><TD>"; BasicInputElement::getGeneralHorizontalRB("ALLOWDECIMALS","Yes,No","Y,N",$postALLOWDECIMALS,"N","N",null,null,null); echo "</TD></TR>";


	$permis = (($postDMLTYPE=="UPDATE") && (!$hasRoleDel))? true : false;
	echo "<TR class='".GUICommonUtils::styleEO($class)."' style='border-bottom: 5px double lightSkyBlue;'><TD>Status"; GUICommonUtils::requiredField();
        echo "</TD><TD>"; BasicInputElement::getCSS3RadioHorizontal("STATUS","Active,Suspended,Deleted","A,S,D",$postSTATUS,$permis); echo "</TD></TR>";

	echo "</tbody>";
	echo "</TABLE><br />";

	if (($postDMLTYPE=="INSERT") || ($postDMLTYPE=="UPDATE"))
		echo "<INPUT type='button' class='submit' onclick='submitContentProduct(\"".$postDMLTYPE."\");' value='Submit Product' />";

#--------------------------------------------------------------------------------------------------------------------------

$dbConn->dbClose();
?>

<script type='text/javascript' defer>
var alreadySubmitted=false;

function successCallback(robj) {

  if ('<?php echo $postDMLTYPE; ?>'!='INSERT') return;
    var productCode = document.getElementById("prod_code").value;
    document.getElementById("prod_code").value='';
    document.getElementById("alt_code").value='';
    var productDesc = document.getElementById("prod_desc").value
    document.getElementById("prod_desc").value='';
    document.getElementById("packing").value='';
    document.getElementById("prod_wgt").value='0';
    document.getElementById("prod_vatrate").value=<?php echo VAL_VAT_RATE*100 ?>;
    changeVAT(); // reset the authorisation fields
    document.getElementById("prod_category").options[0].selected = true;
    document.getElementById("itemspercase").value='1';
    document.getElementById("UNITVALUE").value='0';
    document.getElementById("SIZEWIDTH").value='';
    document.getElementById("SIZELENGTH").value='';
    document.getElementById("SIZEHEIGHT").value='';
    document.getElementById("SIZETYPE").options[0].selected = true;

    <?php

      if($postFROMCAPTURE){
        echo "
          var fld=document.getElementsByName(\"PRODUCT[]\");
          for (var i=0; i<fld.length; i++) {
            var elOptNew = document.createElement('option');
            elOptNew.text = productCode + '- -' + productDesc;
            elOptNew.value = robj.identifier;
            try {
                fld[i].add(elOptNew, null); // standards compliant; doesn't work in IE
             } catch(ex) {
                fld[i].add(elOptNew); // IE only
             }
          }
          $('#player').remove();return;";
      }

    ?>

    //RESET IMAGE
    $('#productPhoto').attr('src','<?php echo $templatePhoto ?>');
    $('#photomsg').text('');

    //RESET : DEPOT LINKED GTIN FIELDS.
    var prodeplnkdepObj = $('#prodeplnkdepId select');
    for(var i=1;i<(prodeplnkdepObj.length);i++){
      prodeplnkdepObj.eq(i).remove();
    }
    $('#prodeplnkdepId select').eq(0).val(0);

    var prodeplnkeanObj = $('#prodeplnkeanId input');
    for(var i=1;i<(prodeplnkeanObj.length);i++){
            prodeplnkeanObj.eq(i).remove();
    }
    $('#prodeplnkeanId input').eq(0).val('');

    var prodeplnkocgtinObj = $('#prodeplnkocgtinId input');
    for(var i=1;i<(prodeplnkocgtinObj.length);i++){
            prodeplnkocgtinObj.eq(i).remove();
    }
    $('#prodeplnkocgtinId input').eq(0).val('');

    //RESET table width
    $('#productTableID').css('width','');

}

function submitContentProduct(p_type) {
	if (alreadySubmitted) {
		//return;
	}
	alreadySubmitted=true;

	var params='DMLTYPE='+p_type;
	params+='&UID=<?php echo $postLOADPRINPRODID; ?>';
	params+='&PRODCODE='+encodeURIComponent(document.getElementById("prod_code").value.replace(/'/g,'').replace(/"/g,''));
	params+='&PRODDESC='+encodeURIComponent(document.getElementById("prod_desc").value.replace(/'/g,'').replace(/"/g,''));
  params+='&PACKING='+encodeURIComponent(document.getElementById("packing").value);
	params+='&ALTCODE='+encodeURIComponent(document.getElementById("alt_code").value.replace(/'/g,'').replace(/"/g,''));
	params+='&PRODWGT='+document.getElementById("prod_wgt").value;
	params+='&PRODVAT='+document.getElementById("prod_vatrate").value;
  params+='&AUTHVAT='+convertElementToArray(document.getElementsByName("AUTHVAT"));
	params+='&STATUS='+convertElementToArray(document.getElementsByName("STATUS"));
	params+='&EPC='+convertElementToArray(document.getElementsByName("EPC"));
	params+='&NSI='+convertElementToArray(document.getElementsByName("NSI"));
	params+='&WEB='+convertElementToArray(document.getElementsByName("WEB"));
	params+='&UPP='+document.getElementById("UPP").value;
	params+='&ITEMSPERCASE='+document.getElementById("itemspercase").value;
  params+='&UNITVALUE='+document.getElementById("UNITVALUE").value;
  params+='&SIZETYPE='+convertElementToArray(document.getElementsByName("SIZETYPE"));
  params+='&SIZEWIDTH='+document.getElementById("SIZEWIDTH").value;
  params+='&SIZELENGTH='+document.getElementById("SIZELENGTH").value;
  params+='&SIZEHEIGHT='+document.getElementById("SIZEHEIGHT").value;
  params+='&SHOPIFY='+convertElementToArray(document.getElementsByName("SHOPIFY"));
  params+='&DISCOUNTS='+convertElementToArray(document.getElementsByName("DISCOUNTS"));  
  params+='&ALLOWDECIMALS='+convertElementToArray(document.getElementsByName("ALLOWDECIMALS"));  
  
   //supply the URL ONLY when the photo is changed
  photoURL = $('#productPhoto').attr('src');
  if ( photoURL.search(/-TEMP-/gi) !== -1 ){
    params+='&PHOTOURL='+encodeURIComponent(photoURL);
  }

  //DEPOT LINKED GTIN : get each input / select
  var prodeplnkdepObj = $('#prodeplnkdepId select');
  prodeplnkdep = new Array();
  for(var i=0;i<(prodeplnkdepObj.length);i++){
    prodeplnkdep[i] = prodeplnkdepObj.eq(i).val();
  }
  params+='&PRODEPOTGTIN='+encodeURIComponent(prodeplnkdep.join(','));

  var prodeplnkeanObj = $('#prodeplnkeanId input');
  prodeplnkean = new Array();
	for(var i=0;i<(prodeplnkeanObj.length);i++){
    prodeplnkean[i] = prodeplnkeanObj.eq(i).val().replace(/'/g,'').replace(/"/g,'').replace(/,/g,'');
  }
	params+='&PRODSKUGTIN='+encodeURIComponent(prodeplnkean.join(','));

	var prodeplnkocgtinObj = $('#prodeplnkocgtinId input');
	prodeplnkocgtin = new Array();

	for(var i=0;i<(prodeplnkocgtinObj.length);i++){
	  prodeplnkocgtin[i] = prodeplnkocgtinObj.eq(i).val().replace(/'/g,'').replace(/"/g,'').replace(/,/g,'');
  }
	params+='&OCGTIN='+encodeURIComponent(prodeplnkocgtin.join(','));

	var len = document.getElementById("prod_category").length;
	var prod_category = 0;
	for(var j=0;j<len;j++){
		if(document.getElementById("prod_category").options[j].selected == true){
			prod_category = document.getElementById("prod_category").options[j].value;
		}
	}
	params+='&PRODCAT1='+prod_category;

        <?php
        foreach($mfPMGL as $mcat){
          echo 'var PMCATITEM = $(\'select[name="prod_minor_category['.$mcat['uid'].']"] option:selected\').val();' . "\n";
          echo 'PMCATITEM=(PMCATITEM==undefined)?(""):(PMCATITEM);' . "\n";
          echo 'params+="&PMGUIDLIST['.$mcat['uid'].']=" + PMCATITEM' . "\n";
        }
        ?>
	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element

	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/products/productSubmit.php',
						  'alreadySubmitted=false; if (msgClass.type=="S") successCallback(msgClass);',
						  'Please wait while request is processed ...');
}

function emailProduct() {
	var params="USERID=<?php echo $userId; ?>&OBJECTID=<?php echo EO_PRODUCT_CARD; ?>&SUBJECT=Product Details as per Request: "+escape(document.getElementById('prod_desc').value)+"&PRODUCTUID=<?php echo $postLOADPRINPRODID; ?>";
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/emailUserHTML.php',
						  'alreadySubmitted=false;',
						  'Please wait while request is processed...');
}
function showHierarchy() {

  $('#divHierarchy').each(function (i,e) {
    if ($(this).css('display')=='none') {
      document.getElementById('imgHierarchy').src=document.getElementById('imgHierarchy').src.replace('down.jpg','up.jpg');
      $(this).toggle(500, function() { });
    } else {
      document.getElementById('imgHierarchy').src=document.getElementById('imgHierarchy').src.replace('up.jpg','down.jpg');
      $(this).toggle(500, function() { });
    }
  });
}

function showDimensionsTooltip(obj) {
  parent.displayTip(obj,5,135,'<div align="center">width x length x height<BR><IMG src="images/dimensions.jpg" width="220" height="129"></div>');
}

function displayPhoto(){
  parent.popBox('<div align="center"><IMG SRC="<?php echo str_replace('../../','',$photoPath) . '?' . uniqid(); ?>" width="350" height="350" ></div>','general',380);
}

function uploadFile(){
    parent.popBox('<div align="center" id="productUpload" style="color:#444;"></div>','general');
    AjaxRefreshHTML("TYPE=PRODUCT",
                    '<?php echo $ROOT.$PHPFOLDER ?>functional/general/uploadFile.php',
                    'productUpload',
                    'Please wait while request is processed...',
                    '');
}

function changeVAT() {
  var rate=0;
  try {
    rate=parseFloat($('#prod_vatrate').val());
  } catch (e) {
    alert('Invalid Rate - numeric value expected !');
    return;
  }
  if (rate==0) {
    $('#divAuthVAT').css('display','block');
    $('#AUTHVAT')[0].checked=false;
  } else {
    $('#divAuthVAT').css('display','none');
  }
}

</script>
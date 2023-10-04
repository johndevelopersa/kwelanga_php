<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER.'functional/main/access_control.php');

include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
include_once($ROOT.$PHPFOLDER."elements/basicInputElement.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
include_once($ROOT.$PHPFOLDER."elements/simpleTableElement.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");

$totalRows=1;	 

$dbConn = new dbConnect();
$dbConn->dbConnection();

if (isset($_POST['DMLTYPE'])) $postDMLTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DMLTYPE'])); else $postDMLTYPE="INSERT";

if (isset($_POST["TYPE"])) { $postTYPE = strtoupper($_POST["TYPE"]);  } else { 	$postTYPE = ""; }
if (isset($_POST["LEVEL"])) {	$postLEVEL = $_POST["LEVEL"];  } else { $postLEVEL = PRT_PRODUCT; }

if (!isset($_SESSION)) session_start() ;
$principalId =  $_SESSION['principal_id'] ;
$userId     =  $_SESSION['user_id'];

#--------------------------------------------------------------------------------------------------------------------------
	echo "<html>";
	echo "<head>";
    echo "</head>";
    echo "<body><br>";
	
	// check roles
    $administrationDAO = new AdministrationDAO($dbConn);
    $hasMPRole = $administrationDAO->hasRole($userId,$principalId,ROLE_MODIFY_PRICE);
    $hasAPRole = $administrationDAO->hasRole($userId,$principalId,ROLE_ADD_PRICE);

	if ($postDMLTYPE=="INSERT") {
		if (!$hasAPRole) {
			echo "Sorry, you do not have permissions to ADD PRICING!";
			return;
		}
	} else if ($postDMLTYPE=="UPDATE") {
		if (!$hasMPRole) {
			echo "Sorry, you do not have permissions to MODIFY PRICING!";
			return;
		}
	} 
	// end check roles
	

	// START : PARAM FORM 
	
 	if (($postTYPE == "") || ($postLEVEL=="")) {
 		echo "<table>";
		echo "<tr class='odd' style='text-align:center; font-weight:bold;'><td colspan=2>Parameters</td></tr>";
		echo "<tr>";
		echo "<td>Location Level :</td>";
		echo "<td>";
	    BasicInputElement::getGeneralHorizontalRB("pTYPE","Chain,Store",CT_CHAIN.",".CT_STORE,CT_CHAIN,"N","N",null,null,null);
	    echo "</td>
			  </tr>

			  <tr>
			  <td>Product Level: </td>";
		echo "<td>";
	    BasicInputElement::getGeneralHorizontalRB("pLEVEL","Product,Product Group",PRT_PRODUCT.",".PRT_PRODUCT_GROUP,PRT_PRODUCT,"N","N",null,null,null);
	    echo "</td>
			  </tr>";
	    echo "<tr style='text-align:center;'><td colspan=2>
					<input type='submit' class='submit' value='Submit' onclick='submitParams();' />
					<script type='text/javascript' defer>
						function submitParams() {
							AjaxRefresh('TYPE='+convertElementToArray(document.getElementsByName(\"pTYPE\"))+'&LEVEL='+convertElementToArray(document.getElementsByName(\"pLEVEL\")),
										'{$ROOT}{$PHPFOLDER}functional/deals/basketDealForm.php',
										'ajaxTabsContent',
										'Please wait while Data Form is retrieved...',
										'');
						}
					</script>
				  </td></tr>
			  </table>";
	    
	    return;
	
	}
	
	// END : PARAM FORM
	
	echo "Deals will be loaded at the following Level:
		  <table>
			<tr><td>Location Level: </td><td>".(($postTYPE==CT_CHAIN)?"Chain":"Store")."</td></tr>
			<tr><td>Product Level: </td><td>".(($postLEVEL==PRT_PRODUCT)?"Product":"Product Group")."</td></tr>
		  </table>
		  <br>";
	
	
	$dbConn = new dbConnect();
	$dbConn->dbConnection();


/********************************************************************************
 * Start : Location and Product Tabs
 * ******************************************************************************/

echo <<<EOF
<script type="text/javascript">

function nul(){}

function showTab(nr,obj){
  jQuery('#tabBlks > div').hide();	//hide all direct child div's
  jQuery('#tabBlks div#tab'+nr+'').show(); //show tab nr
  jQuery('#tabBut a').css({'background':'lightSkyBlue','line-height':'16px','color':'#1e4272'});	//set all tabbut to std bk
  jQuery(obj).css({'background':'#1e4272','line-height':'20px','color':'#fff'});	//set sel tab to on bk
}

function calSelected(){
	for(var i = 0; i < jQuery('#tabBlks > div').size(); i++){
		var obj = jQuery('#tabBut > a:eq('+i+')');
		var ostr = obj.text();
		var nstr = ostr.replace(/\(\d*\)/,'('+jQuery('#tabBlks div#tab'+(i+1)+' input:checked').size()+')');
		obj.text(nstr);
	}
}

</script>
EOF;
	$tabcss = 'outline:0px;color:#1e4272;display:inline-block;padding:3px 14px;margin:3px 6px 0px 0px;background:lightSkyBlue;line-height:16px;';
	$tabact = 'color:#fff;background:#1e4272;line-height:20px;';
	echo '<table><tr><td>
		  <div id="tabBut">';
	echo ($postTYPE==CT_CHAIN)?'<a href="javascript:nul()"  onClick="showTab(1,this)" style="'.$tabcss.$tabact.'">Chains (0)</a>':
		  					   '<a href="javascript:nul()" onClick="showTab(1,this)" style="'.$tabcss.'">Stores (0)</a>';
	echo ($postLEVEL==PRT_PRODUCT)?'<a href="javascript:nul()" onClick="showTab(2,this)" style="'.$tabcss.'">Products (0)</a>':
		  					   '<a href="javascript:nul()" onClick="showTab(2,this)" style="'.$tabcss.'">Product Groups (0)</a>';
	echo '</div>';

  	echo '<div id="tabBlks" style="border:1px solid #1e4272;" onClick="calSelected()">';

	if ($postTYPE==CT_CHAIN) {
	    echo '<div id="tab1">';
		  SimpleTableElement::getUserChainList("PFORM_LOCATIONS", array(), "checkbox", "140", $dbConn, $principalId, $userId, CHAIN_FILTER_PRICE);
		echo '</div>';
	} else {
		echo '<div id="tab1" style="display:none">';
		  SimpleTableElement::getUserStoreList("PFORM_LOCATIONS", array(), "checkbox", "140", $dbConn, $principalId, $userId);
		echo '</div>';
	}
	
	if ($postLEVEL==PRT_PRODUCT) {
		echo '<div id="tab2" style="display:none">';
	      SimpleTableElement::getUserProductList("PFORM_PRODUCTS", array(), "checkbox", "140", $dbConn, $principalId, $userId);
		echo '</div>';
	} else {
		echo '<div id="tab2" style="display:none">';
	      SimpleTableElement::getProductGroupList("PFORM_PRODUCTS", array(), "checkbox", "140", $dbConn, $principalId, $userId);
		echo '</div>';
	}

  	echo '</div>
		  </td></tr></table>
		  <br><br>';

/********************************************************************************
 * End : Location and Product Tabs
 * ******************************************************************************/
 	
		
	$dateStr    = gmdate("Y-m-d", time()+7200);

	echo "<INPUT name='CUSTOMERTYPE' type='hidden' value='{$postTYPE}' />";
	echo "<INPUT name='PRICETYPE' type='hidden' value='{$postLEVEL}' />";

	$rowStyle="even";
			
	echo "<TABLE id='dealTbl'>";
	echo "<tr class='{$rowStyle}'><td>List Price:</td><td><INPUT name='LISTPRICE' class='txtinputr' type='text' size='9' maxlength='8' value='0' onChange=''></td></tr>";
	GUICommonUtils::styleEO($rowStyle);
	echo "<tr class='{$rowStyle}'><td>Deal Type:</td><td>"; BasicSelectElement::getDealTypesDD("DEALTYPE","1","N","N",null,null,null,$dbConn); echo "</td></tr>";
	GUICommonUtils::styleEO($rowStyle);
	echo "<tr class='{$rowStyle}'><td>Discount Value:</td><td><INPUT name='DISCOUNTVALUE' class='txtinputr' type='text' size='9' maxlength='8' value='0' ></td></tr>";
	GUICommonUtils::styleEO($rowStyle);
	echo "<tr class='{$rowStyle}'><td>VAT:</td><td>Exclusive</td></tr>";
	GUICommonUtils::styleEO($rowStyle);
	echo "<tr class='{$rowStyle}'><td>Start Date:</td><td>"; DatePickerElement::getDatePicker("STARTDATE",$dateStr); echo "</td></tr>";
	GUICommonUtils::styleEO($rowStyle);
	echo "<tr class='{$rowStyle}'><td>End Date:</td><td>"; DatePickerElement::getDatePicker("ENDDATE",$dateStr); echo "</td></tr>";
	GUICommonUtils::styleEO($rowStyle);
	echo "<tr class='{$rowStyle}'><td>Reference:</td><td><INPUT name='REFERENCE' class='txtinputr' type='text' size='50' maxlength='50' value='' onChange=''></td></tr>";
	GUICommonUtils::styleEO($rowStyle);
	echo "<tr class='{$rowStyle}'><td colspan=2 style='text-align:center;'><INPUT class='submit' type=\"button\" onclick='submitContentForm(\"".$postDMLTYPE."\");' value=\"Submit Deal\"></td></tr>
		  </table>";
	  

echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable

echo "</body>";
echo "</html>";

$dbConn->dbClose();

?>
<script type="text/javascript" defer>

var alreadySubmitted=false;
function submitContentForm(p_type) {
	if (alreadySubmitted) {
		return;
	}
	alreadySubmitted=true;
	
	var params='DMLTYPE='+p_type;
	
	params+='&LOCATIONTYPE=<?php echo $postTYPE; ?>';
	params+='&PRODUCTLEVEL=<?php echo $postLEVEL; ?>';
	params+='&LOCATIONLIST='+convertElementToArray(document.getElementsByName("PFORM_LOCATIONS"));
	params+='&PRODUCTLIST='+convertElementToArray(document.getElementsByName("PFORM_PRODUCTS"));
	params+='&LISTPRICE='+convertElementToArray(document.getElementsByName("LISTPRICE"));
	params+='&DEALTYPE='+convertElementToArrayOther(document.getElementsByName("DEALTYPE"));
	params+='&DISCOUNTVALUE='+convertElementToArray(document.getElementsByName("DISCOUNTVALUE"));
	params+='&STARTDATE='+convertElementToArray(document.getElementsByName("STARTDATE"));
	params+='&ENDDATE='+convertElementToArray(document.getElementsByName("ENDDATE"));
	params+='&REFERENCE='+convertElementToArrayOther(document.getElementsByName("REFERENCE"));
	
	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/deals/basketDealSubmit.php',
						  'alreadySubmitted=false; if(msgClass.type=="S") successfullyProcessed();',
						  'Please wait while request is processed...');
}

function successfullyProcessed() {
	// no need to reset any fields
}

</script>

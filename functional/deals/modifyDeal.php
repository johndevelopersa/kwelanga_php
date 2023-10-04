<?php
	include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	require($ROOT.$PHPFOLDER."functional/main/access_control.php");
ob_start(); //Turn on output buffering
	include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
	include_once($ROOT.$PHPFOLDER."elements/simpleTableElement.php");
	include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
	include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
	include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

// fields
$fldChosenDRB = 'ChosenDeal';

$dbConn = new dbConnect();
$dbConn->dbConnection();

$postDAYSPAST = (isset($_POST["DAYSPAST"])) ? ($_POST["DAYSPAST"]) : ('14');
$postDMLTYPE = (isset($_POST['action'])) ? (mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['action']))) : ('VIEW');

$firstTime = (!isset($_POST["PFORM_CBFILTER"])) ? (true) : (false); // includes the show TL option
$postCBFILTER = (isset($_POST["PFORM_CBFILTER"])) ? (mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["PFORM_CBFILTER"]))) : ('');
$postCHAINS = (isset($_POST["PFORM_CHAINS"])) ? (mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["PFORM_CHAINS"]))) : ('');
$postSTORES = (isset($_POST["PFORM_STORES"])) ? (mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["PFORM_STORES"]))) : ('');
$postPRODUCTS = (isset($_POST["PFORM_PRODUCTS"])) ? (mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["PFORM_PRODUCTS"]))) : ('');
$postPROGROUPS = (isset($_POST["PFORM_PROGROUP"])) ? (mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["PFORM_PROGROUP"]))) : ('');

$cbFilter = explode(",",$postCBFILTER);
$cbChains = explode(",",$postCHAINS);
$cbStores = explode(",",$postSTORES);
$cbProducts = explode(",",$postPRODUCTS);
$cbProGroups = explode(",",$postPROGROUPS);

// the ajax divs. refreshed independently
$divAjaxMainContentArea="ajaxMainContentArea";

$JSDeletedArr="var arrDel=new Array();";

#--------------------------------------------------------------------------------------------------------------------------


	    /*
	     *
	     * START OF SCREEN
	     *
	     */
	    echo "<HTML>
			  <HEAD></HEAD>
			  <BODY><BR>";

	    include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');

	    // check roles
	    $hasModifyRole=false; // initialised because it is checked later without knowing DMLTYPE
	    $hasViewRole=false; // initialised because it is checked later without knowing DMLTYPE
	    $administrationDAO = new AdministrationDAO($dbConn);
	    if ($postDMLTYPE=="VIEW") {
	    	$hasViewRole = $administrationDAO->hasRole($userId,$principalId,ROLE_VIEW_PRICE);
	    	if (!$hasViewRole) {
		    	echo "Sorry, you do not have permissions to VIEW PRICING";
		    	return;
	    	}
	    } else {
	    	$hasModifyRole = $administrationDAO->hasRole($userId,$principalId,ROLE_MODIFY_PRICE);
	    	if (!$hasModifyRole) {
		    	echo "Sorry, you do not have permissions to MODIFY PRICING";
		    	return;
		    }
	      }

	    if (($firstTime) || (in_array("ACTIVE",$cbFilter))) $active=true; else $active=false;
	    if (in_array("EXPIRED",$cbFilter)) $expired=true; else $expired=false;
	    if (in_array("FORTHCOMING",$cbFilter)) $forthComing=true; else $forthComing=false;


if ($firstTime) {

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

		// parameter : days passed DD
		echo "<FORM name='params' id='params' action='".$_SERVER['PHP_SELF']."' method='post' style='margin:0; padding:0;'>
			  <INPUT type='hidden' name='action' value='{$postDMLTYPE}' />";

	    // parameters : other
		echo "<TABLE class='tblReset' border='0' width='600'>";
		echo "<TR style='text-align:center' class='odd'>";
			echo "<TD colspan=8><B>Parameters</B></TD>";
		echo '</TR>';

		echo '<TR>';
			if (($firstTime===true) || (in_array("ACTIVE",$cbFilter))) $CHECKED=" CHECKED "; else $CHECKED="";
			echo "<TD width='20' height='35'><input type='checkbox' name='PFORM_CBFILTER' value='ACTIVE'".$CHECKED." ></TD>";
			echo "<TD>Current</TD>";

			$CHECKED=(in_array("FORTHCOMING",$cbFilter))?(" CHECKED "):("");
			echo "<TD width='20'><input type='checkbox' name='PFORM_CBFILTER' value='FORTHCOMING'".$CHECKED." ></TD>";
			echo '<TD>Upcoming</TD>';

		    $CHECKED=(in_array("EXPIRED",$cbFilter))?(" CHECKED "):("");
			echo "<TD width='20'><input type='checkbox' name='PFORM_CBFILTER' value='EXPIRED'".$CHECKED." ></TD>";
			echo '<TD width="180">Expired within: &nbsp;';

	    //updated day select
		echo '<SELECT id="days" name="days" style="width:80px;">';
		$expireDays = array(5,14,30,90);
		foreach($expireDays as $day){ echo '<OPTION value="',$day,'" ',($postDAYSPAST==$day)?('selected'):(''),'>',$day,' days';}
		echo '</SELECT>';

		echo '</TD>';

		   $CHECKED=(in_array("SHOWTL",$cbFilter))?(" CHECKED "):("");
			echo "<TD width='20'><input type='checkbox' name='PFORM_CBFILTER' value='SHOWTL'".$CHECKED." ></TD>";
			echo "<TD>Timeline</TD>";
        echo '</TR>';
        echo '<TR>';

		echo '<tr><td colSpan="8" style="border-top: 1px solid #87CEFA;padding-top:8px;">';

		$tabcss = 'outline:0px;color:#1e4272;display:inline-block;padding:3px 14px;margin:3px 6px 0px 0px;background:lightSkyBlue;line-height:16px;';
		$tabact = 'color:#fff;background:#1e4272;line-height:20px;';
		echo '<div id="tabBut">
				<a href="javascript:nul()"  onClick="showTab(1,this)" style="',$tabcss,$tabact,'">Chains (0)</a>
			  	<a href="javascript:nul()" onClick="showTab(2,this)" style="',$tabcss,'">Stores (0)</a>
			  	<a href="javascript:nul()" onClick="showTab(3,this)" style="',$tabcss,'">Products (0)</a>
			  	<a href="javascript:nul()" onClick="showTab(4,this)" style="',$tabcss,'">Product Groups (0)</a>
			  </div>';

  echo '<div id="tabBlks" style="border:1px solid #1e4272;" onClick="calSelected()">';

    echo '<div id="tab1">';
	  SimpleTableElement::getUserChainList("PFORM_CHAINS", $cbChains, "checkbox", "140", $dbConn, $principalId, $userId,CHAIN_FILTER_PRICE);
	echo '</div>';

	echo '<div id="tab2" style="display:none">';
	  SimpleTableElement::getUserStoreList("PFORM_STORES", $cbStores, "checkbox", "140", $dbConn, $principalId, $userId);
	echo '</div>';

	echo '<div id="tab3" style="display:none">';
      SimpleTableElement::getUserProductList("PFORM_PRODUCTS", $cbProducts, "checkbox", "140", $dbConn, $principalId, $userId);
	echo '</div>';

	echo '<div id="tab4" style="display:none">';
      SimpleTableElement::getProductGroupList("PFORM_PROGROUP", $cbProGroups, "checkbox", "140", $dbConn, $principalId, $userId);
	echo '</div>';

  echo '</div>';

	echo '</td></tr><tr>
			<TD colspan="8" align="center" height="35"><input type="button" class="submit" value="Submit Parameters" onclick="refreshDATA();" /></TD></TR>
		  </TABLE>';
	echo '</FORM>';
	echo '<div id="DATA"></div>';
		?>
		<script type="text/javascript" defer>
		function refreshDATA() {
			AjaxRefresh(getParams(),
						"<?php echo $_SERVER['PHP_SELF']; ?>",
					    "DATA",
					    "Please wait whilst page is refreshed...",
					    "");
		}
		function getParams() {
			var params="PFORM_CBFILTER="+convertElementToArray(document.getElementsByName("PFORM_CBFILTER"));
			params+="&DAYSPAST="+document.getElementById("days").value;
			params+="&PFORM_CHAINS="+convertElementToArray(document.getElementsByName("PFORM_CHAINS"));
			params+="&PFORM_STORES="+convertElementToArray(document.getElementsByName("PFORM_STORES"));
			params+="&PFORM_PRODUCTS="+convertElementToArray(document.getElementsByName("PFORM_PRODUCTS"));
			params+="&PFORM_PROGROUP="+convertElementToArray(document.getElementsByName("PFORM_PROGROUP"));
			params+="&action=<?php echo $postDMLTYPE; ?>";

			return params;
		}

		</script>
		<?php


} else {
		if (($postCHAINS=="") && ($postPRODUCTS=="") && ($postSTORES=="")) { echo "You must choose atleast one 1 product or store or chain from the parameters"; return; }
	    echo "<table class='tableReset'><tr>";
	    if (($hasModifyRole) && ($postDMLTYPE=="UPDATE")) echo "<td style='padding-top:10px;'><input type='button' class='submit' value='Submit Showing Rows for Edit' onclick='submitAllShowing(\"UPDATE\");' /></td>";
	    if (($hasModifyRole) && ($postDMLTYPE=="UPDATE")) echo "<td style='padding-top:10px;'><input type='button' class='submit' value='Purge End Dates > 90days' onclick='purge(90);' /></td>";
	    echo "<td style='padding-top:10px;'><input type='button' class='submit' value='Cancel Filter' onclick='showAllRows();' /></td>";
	    echo "<td style='padding-top:10px;'><input id='btnHideDel' type='button' class='submit' value='Hide Deleted Rows' onclick='toggleShowDeletedRow();' /></td>";
	    echo "</tr></table>";


	    // style for table timeline
	    echo "<STYLE>
				.TLouter {
					font-family:Verdana,Arial,Helvetica,sans-serif;
					font-size:0.8em;
					padding:0;
					margin:0;
				}
				.TLformer {
					background-color:#505050;
				}
				.TLactive {
					background-color:#ffffff;
				}
				.TLdeleted {
					background-color:#faf6cb;
					color:red; text-decoration:line-through;
				}
				.TL {
					white-space:nowrap;
					padding:0;
					margin:0;
				}
				.TLborder {
					border-left-style:solid;
					border-left-width:1px;
					border-left-color:black;
					margin:0px;
					padding:0px;
				}
				.TLtoday {
					padding:0;
					margin:0;
					border-left-style:solid;
					border-left-width:2px;
					border-left-color:#75a3d1;
					border-right-style:solid;
					border-right-width:2px;
					border-right-color:#75a3d1;
				}
			 </STYLE>";


	    //legend
	    echo "<table class='tableReset'><tr>";
	    echo "<td><div style='text-decoration:line-through; width:15px; height:15px; margin-top:5px; color:red; border-style:solid; border-color:grey; border-width:1px;'>text</div></td><td>Deleted Pricing</td>";
	    echo "<td><div style='background-color:#505050; width:15px; height:15px; margin-top:5px;'></div></td><td>Deals expired</td>";
	    echo "<td><div style='background-color:black; width:15px; height:15px; margin-top:5px;'></div></td><td>Deal Duration (days)</td>";
	    echo "<td><div class='TLtoday' style='width:15px; height:15px; margin-top:5px;'></div></td><td>Timeline: Today (1 day)</td>";
	    echo "</tr></table>";


	    /******************************
	     * MAIN CONTENT
	     * ****************************/

	    echo "<table style='font-family:Verdana,Arial,Helvetica,sans-serif; font-size:0.7em;'>";

		echo "<tr class='odd'>";
		echo "<th></th>";
		echo "<th>UID</th>";
		echo "<th>Description<BR>
					<SELECT id='ddENT' onchange='hideEntityRow(this.options[this.selectedIndex].value);'><OPTION value=''>--</OPTION></SELECT>
			  </th>";
		echo "<th>Product Code<BR>
					<SELECT id='ddPC' onchange='hideProductRow(this.options[this.selectedIndex].value);'><OPTION value=''>--</OPTION></SELECT>
			  </th>";
		echo "<th>Product Description<BR>
					<SELECT id='ddPD' onchange='hideProductDescRow(this.value);'><OPTION value=''>--</OPTION></SELECT>
			  </th>";
		echo "<th>Deal<br>Type</th>";
		echo "<th>List<br>Price</th>";
		echo "<th>Discount<br>Value</th>";
		echo "<th>Start Date</th>";
		echo "<th>End Date</th>";
		echo "<th>Entity<br>Type</th>";
		echo "<th>VAT</th>";
		echo "<th>Captured<br>By</th>";
		echo "<th>Time Line</th>";
		echo "<th style='padding:0; margin:0;'></th>"; // green line for today
		echo "<th style='padding:0; margin:0;'></th>"; // space after green line
		echo "<th></th>";
		echo "</tr>";

	    $productDAO = new ProductDAO($dbConn);
	    $mfDeals=$productDAO->getUserPrincipalPricingDeals($userId,$principalId,$postDAYSPAST,$active,$expired,$forthComing,$postCHAINS,$postPRODUCTS,$postSTORES,$postPROGROUPS);

	    $i=0;
	    $futureDays=21; $dayWidth=8; //*5px;
	    foreach ($mfDeals as $row) {
	    	// JS : for hiding rows
	    	$JSDeletedArr.="arrDel[".$i."]=".$row['deleted'].";";
	    	$class="";
	    	if ($row['scope']=="<") $class=" TLformer ";
	    	else if ($row['scope']=="=") $class=" TLactive ";
	    	if (intval($row['deleted'])=="1") $class.=" TLdeleted ";
	    	echo "<tr id='row_".$i."'>";
	    	$i++;
	    	echo "<td class='{$class}' style='width:20px;' nowrap>&nbsp;</td>";
	    	echo "<td class='{$class}' nowrap><A href='javascript:' class='c_uid' onclick='hideUIDRow(this.innerHTML);'>".$row['uid']."</A></td>";
	    	echo "<td class='{$class}' nowrap><A href='javascript:' class='c_entity' onclick='hideEntityRow(this.innerHTML);'>".$row['entity_description']."</A></td>";
	    	echo "<td class='{$class}' nowrap><A href='javascript:' class='c_pcode' onclick='hideProductRow(this.innerHTML);'>".(($row['price_type_uid']==PRT_PRODUCT_GROUP)?"(Product Group)":$row['product_code'])."</A></td>";
	    	echo "<td class='c_pdesc {$class}' nowrap>".$row['product_description']."</td>";
	    	echo "<td class='{$class}' nowrap>".$row['dealtype_description']."</td>";
			echo "<td class='{$class}' nowrap>".$row['list_price']."</td>";
			echo "<td class='{$class}' nowrap>".$row['discount_value']."</td>";
			echo "<td class='{$class}' nowrap><A href='javascript:' class='c_sd'onclick='hideSDRow(this.innerHTML);'>".$row['start_date']."</A></td>";
			echo "<td class='{$class}' nowrap><A href='javascript:' class='c_ed' onclick='hideEDRow(this.innerHTML);'>".$row['end_date']."</A></td>";
			echo "<td class='{$class}' nowrap>".$row['customer_type']."</td>";
			echo "<td class='{$class}' nowrap>".$row['excl_incl']."</td>";
			echo "<td class='{$class}' nowrap>".$row['full_name']."</td>";

			// timeline start
			echo "<td class='{$class} TLborder' nowrap>";
			if (in_array("SHOWTL",$cbFilter)) {
				$tdTL="";

				$TLArr=array();
				$startDaysRelativeToToday=floor((time()-strtotime($row['start_date']))/(60*60*24))*(-1);
				$endDaysRelativeToToday=floor((time()-strtotime($row['end_date']))/(60*60*24))*(-1);
				// calculate where in the window the deal fits in. futureDays counts from and including today therefore stop when "<" and not "<=". Although, interval 0 in array will be today.
				for ($day=($postDAYSPAST*-1);$day<$futureDays; $day++) {
					if (($startDaysRelativeToToday<=$day) && ($endDaysRelativeToToday>=$day)) {
						$TLArr[$day]=true;
					} else $TLArr[$day]=false;
				}
				// now convert into div width intervals of before-middle-after-TODAY-before-middle-after
				$before1=0; $before2=0; $before3=0;
				$today=0;
				$after1=0; $after2=0; $after3=0;
				foreach ($TLArr as $key=>$day) {
					// before today
					if ($key<0) {
						if (($before2==0) && ($day===false)) $before1++;
						else if ($day===true) $before2++;
						else $before3++;
					}
					// today
					else if ($key==0) {
						if ($day===true) $today=1;
					}
					// after today
					else {
						if (($after2==0) && ($day===false)) $after1++;
						else if ($day===true) $after2++;
						else $after3++;
					}
				}
				// there is a before-middle-after-TODAY-before-middle-after where before and after are whitespace to move the bar along!
				//echo $before1."-".$before2."-".$before3."-".$today."-".$after1."-".$after2."-".$after3;
				$tdTL.="<table id='pricingTbl' class='tableReset TLouter'><tr>";
				$tdTL.="<td class='{$class} TL'><div style='height:15px; width:".($before1*$dayWidth)."px; background-color:#eeeeee;'></div></td>";
				$tdTL.="<td class='{$class} TL'><div style='height:15px; color:white; width:".($before2*$dayWidth)."px; background-color:#303030;'></div></td>";
				$tdTL.="<td class='{$class} TL'><div style='height:15px; width:".($before3*$dayWidth)."px; background-color:#eeeeee;'></div></td>";
				// today must be enforced as otherwise columns after become misaligned
				if ($today>0) $tdTL.="<td class='{$class} TLtoday TL'><div style='height:15px; width:".($today*$dayWidth)."px; background-color:#303030;'></div></td>";
				else $tdTL.="<td class='{$class} TLtoday TL'><div style='height:15px; width:".(1*$dayWidth)."px; background-color:#eeeeee;'></div></td>";
				$tdTL.="<td class='{$class} TL'><div style='height:15px; width:".($after1*$dayWidth)."px; background-color:#eeeeee;'></div></td>";
				$tdTL.="<td class='{$class} TL'><div style='height:15px; color:white; width:".($after2*$dayWidth)."px; background-color:#303030;'></div></td>";
				$tdTL.="<td class='{$class} TL'><div style='height:15px; width:".($after3*$dayWidth)."px; background-color:#eeeeee;'></div></td>";
				$tdTL.="</tr></table>";

				echo $tdTL; // i was going to convert < to &lt; to prevent rendering, but changed my mind.
			}
			echo "</td>";
			// timeline end
			echo "<td class='{$class}' style='width:20px;'>&nbsp;</td>";
	    	echo "</tr>";
	    }
	    echo "</table>";

		$dbConn->dbClose();
?>
<SCRIPT type="text/javascript" defer>
adjustMyFrameHeight();
var alreadySubmitted=false;

<?php echo $JSDeletedArr; //echo $JSProdArr; echo $JSEntityArr; echo $JSSDArr; echo $JSEDArr; echo $JSUIDArr; ?>

$(document).ready(function () {
 	postProcesses();
  });

function postProcesses() {
	generatePC_DD();
 	generatePD_DD();
 	generateENT_DD();
	adjustMyFrameHeight();
}

function getColumnArray(p_class) {
	var arr=new Array();
	var i=0;
	$('.'+p_class).each(function() {
		arr[i]=$(this).html();
		i++;
	 })
	 return arr;
}
// ignore hidden, but keep the positioning regardless
function getColumnArrayVisible(p_class) {
	var arr=new Array();
	var i=0;
	$('.'+p_class).each(function() {
		var row = document.getElementById("row_"+i);
		if (row.style.display != '') arr[i]="";
		else arr[i]=$(this).html();
		i++;
	 })
	 return arr;
}
function generatePC_DD() {
	unProd=getColumnArrayVisible('c_pcode');
	unProd=unProd.unique();
	unProd.sort();
	dd=document.getElementById('ddPC');
	dd.options.length = 0;
	dd.options[dd.options.length] = new Option("--", "", false, false);
	for (i=0; i<unProd.length; i++) {
		if (unProd[i]!='') dd.options[dd.options.length] = new Option(unProd[i], unProd[i], false, false);
	}
}
function generatePD_DD() {
	pdArr=getColumnArrayVisible('c_pdesc');
	pdArr=pdArr.unique();
	pdArr.sort();
	dd=document.getElementById('ddPD');
	dd.options.length = 0;
	dd.options[dd.options.length] = new Option("--", "", false, false);
	for (i=0; i<pdArr.length; i++) {
		if (pdArr[i]!='') dd.options[dd.options.length] = new Option(pdArr[i], pdArr[i], false, false);
	}
}
function generateENT_DD() {
	eArr=getColumnArrayVisible('c_entity');
	eArr=eArr.unique();
	eArr.sort();
	dd=document.getElementById('ddENT');
	dd.options.length = 0;
	dd.options[dd.options.length] = new Option("--", "", false, false);
	for (i=0; i<eArr.length; i++) {
		if (eArr[i]!='') dd.options[dd.options.length] = new Option(eArr[i], eArr[i], false, false);
	}
}

function toggleShowDeletedRow(){
	for (i=0; i<arrDel.length; i++) {
		if (arrDel[i]==1) {
			var row = document.getElementById("row_"+i);
			if (row.style.display == '')  row.style.display = 'none';
			else row.style.display = '';
		}
	}

	btn=document.getElementById('btnHideDel');
	if (btn.value=='Hide Deleted Rows') btn.value='Show Deleted Rows';
	else btn.value='Hide Deleted Rows';
	postProcesses();

}
function hideProductRow(val){
	if (val=="") return;
	var arrProd=getColumnArray('c_pcode');
	for (i=0; i<arrProd.length; i++) {
		if (arrProd[i]!=val) {
			var row = document.getElementById("row_"+i);
			row.style.display = 'none';
		}
	}
	postProcesses();
}
function hideProductDescRow(val){
	if (val=="") return;
	var arrPD=getColumnArray('c_pdesc');
	for (i=0; i<arrPD.length; i++) {
		if (arrPD[i]!=val) {
			var row = document.getElementById("row_"+i);
			row.style.display = 'none';
		}
	}
	postProcesses();
}
function hideEntityRow(val){
	var arrEntity=getColumnArray('c_entity');
	for (i=0; i<arrEntity.length; i++) {
		if (arrEntity[i]!=val) {
			var row = document.getElementById("row_"+i);
			row.style.display = 'none';
		}
	}
	postProcesses();
}
function hideSDRow(val){
	var arrSD=getColumnArray('c_sd');
	for (i=0; i<arrSD.length; i++) {
		if (arrSD[i]!=val) {
			var row = document.getElementById("row_"+i);
			row.style.display = 'none';
		}
	}
	postProcesses();
}
function hideEDRow(val){
	var arrED=getColumnArray('c_ed');
	for (i=0; i<arrED.length; i++) {
		if (arrED[i]!=val) {
			var row = document.getElementById("row_"+i);
			row.style.display = 'none';
		}
	}
	postProcesses();
}
function hideUIDRow(val){
	var arrUID=getColumnArray('c_uid');
	for (i=0; i<arrUID.length; i++) {
		if (arrUID[i]!=val) {
			var row = document.getElementById("row_"+i);
			row.style.display = 'none';
		}
	}
	postProcesses();
}
function showAllRows(val){
	var arrProd=getColumnArray('c_pcode');
	for (i=0; i<arrProd.length; i++) {
		var row = document.getElementById("row_"+i);
		row.style.display = '';
	}
	btn=document.getElementById('btnHideDel');
	btn.value='Hide Deleted Rows';
	postProcesses();
}
function submitAllShowing(val){
	var arrList=new Array();
	var j=0;
	var arrUID=getColumnArray('c_uid');
	for (i=0; i<arrUID.length; i++) {
		var row = document.getElementById("row_"+i);
		if ((row.style.display == '') && (arrDel[i]!=1)) {
			arrList[j]=arrUID[i];
			j++;
		}
	}
	if (arrList.length>0) {
			if (alreadySubmitted) {
				return;
			}
			alreadySubmitted=true;
			var params='DMLTYPE='+val;
			params+='&DEALUID='+arrList;

			params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
			getContent("<?php echo $ROOT.$PHPFOLDER; ?>functional/deals/dealForm.php",params); // func is in generalAjaxBase.php

	} else alert('no rows showing. Cannot submit. \n\nOnly non-deleted rows are considered.');
}
function refreshMain() {
	var params=getParams();
	getContent("<?php echo $ROOT.$PHPFOLDER; ?>functional/deals/modifyDeal.php",params); // func is in generalAjaxBase.php
}

function purge(period) {
	var answer = confirm('Are you sure you wish to PURGE all DELETED pricing with End Dates older than 90 days ?');
	if (!answer) return "cancelled";

	//params='PERIOD='+period;
	params='PERIOD=700';
	parent.showMsgBoxSystemFeedback('Purging ...');
	$.ajax({
	  url: "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/purgePricing.php",
	  global: false,
	  type: 'POST',
      data: params,
      dataType: 'html',
	  cache: false,
	  success: function(msg){
	  	try {
	  		eval(msg);
	  		parent.showMsgBoxInfo(msgClass.description);
	  	} catch (e) { alert('an unexpected error occurred:'+e.description+msg); }
	  	parent.hideMsgBoxSystemFeedback('Purging ...');
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
		  alert('Could not purge. '+textStatus+' - '+errorThrown);
		  parent.hideMsgBoxSystemFeedback('Purging ...');
	  }
  });

}

toggleShowDeletedRow();
</SCRIPT>
<?php

} // end data section
echo "</BODY></HTML>";

// content was getting beyond 0.5MB so this is necessary
$htmlBody = ob_get_clean();
echo $htmlBody;
?>
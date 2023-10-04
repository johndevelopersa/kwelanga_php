<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER.'functional/main/access_control.php');

include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
include_once($ROOT.$PHPFOLDER."elements/basicInputElement.php");
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
include_once($ROOT.$PHPFOLDER."elements/intelliDDElement.php");

$totalRows=1;

$dbConn = new dbConnect();
$dbConn->dbConnection();

if (isset($_POST['DMLTYPE'])) $postDMLTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DMLTYPE'])); else $postDMLTYPE="INSERT";
// the lookup value when coming from modify deals
if (isset($_POST['DEALUID'])) $postDEALUID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DEALUID'])); else $postDEALUID="";

if (isset($_POST["TYPE"])) { $postTYPE = strtoupper($_POST["TYPE"]);  } else { 	$postTYPE = ""; }
if (isset($_POST["TYPEUID"])) {	$postTYPEUID = strtoupper($_POST["TYPEUID"]);  } else { $postTYPEUID = ""; }
if (isset($_POST["LEVEL"])) {	$postLEVEL = $_POST["LEVEL"];  } else { $postLEVEL = PRT_PRODUCT; }

// convert to arrays
$arrDEALUID=explode(",",$postDEALUID);

if (!isset($_SESSION)) session_start();
$principalId =  $_SESSION['principal_id'] ;
$userId     =  $_SESSION['user_id'];

#--------------------------------------------------------------------------------------------------------------------------
	echo "<HTML>";
	echo "<HEAD>";
    echo "</HEAD>";
    echo "<BODY><BR>";

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


	function drawSelect ($name, $arrName, $arrValue, $selected, $extra="") {
		$return = "";
		if ($extra != "") { $extra = " " . $extra; }
		$return .= "<SELECT id='".$name."' name='".$name."' $extra>";
		for ($i=0; $i<count($arrName); $i++) {
			$return .= "<OPTION ";
			if ($selected == $arrValue[$i]) {
				$return .= "selected ";
			}
			$return .= "value='" . $arrValue[$i] . "'>" . $arrName[$i];
		}
		$return .= "</SELECT>";

		return $return;
	};


	$dbConn = new dbConnect();
	$dbConn->dbConnection();

	$storeDAO = new StoreDAO($dbConn);
 	if (($postTYPE == "") && ($postDEALUID=="")) {
 		echo "<TABLE>";
		echo "<tr class='odd' style='text-align:center; font-weight:bold;'><td colspan=2>Parameters</td></tr>";
		echo "<TR>";
		echo "<TD>Select Type :</TD>";
		echo "<TD>";
	    echo drawSelect ("TYPE", array("Chain", "Store"), array(CT_CHAIN, CT_STORE), CT_CHAIN, "class='txtinput' onChange='javascript:chooseObject(this.value);'");
	    echo "</TD>
			  </tr>";

		echo "<tr>
			  <td>Choose Item: </td>
			  <TD>";
		echo "<DIV id='ajaxOptionDivChain'></div>";


	echo "<DIV id='ajaxOptionDivStore' style='display:none'>";
	echo "<INPUT TYPE='hidden' id=\"STYPEUID\" name=\"STYPEUID\" >";

	//----------------------------------------------------
	//STORE SEARCH
	//----------------------------------------------------
	//special_field_or & ean_code_or means search on these fields - store name is always enabled for search.
	//ean_code = display column	ean code.
	//Header Name ie: store_name = Store Name  : ucword and remove dashes.
	//columns are displayed in order of below - if uid is set to true will be col 1 by default.
	$columnsArr = array('store_name','depot_name','delivery_day','special_fields','special_field_or');

	IntelliDDElement::selectStoreSearch("STYPEUID", $columnsArr, false, '',"store_name + ' - ' + depot_name+', '+((delivery_day!='Not Known')?delivery_day:'')");

    echo "</div>";

	//----------------------------------------------------


		?>
		<SCRIPT type='text/javascript' defer>

		function chooseObject(val){
			if (val=="<?php echo CT_CHAIN ?>") showPC();
			else if (val=="<?php echo CT_STORE ?>") showPS();
		}

		function showPS(){
			$('#ajaxOptionDivChain').hide();
			$('#ajaxOptionDivStore').show();
		}

		function showPC(){
		  	$('#ajaxOptionDivStore').hide();
			$('#ajaxOptionDivChain').show();
		}

		function loadPC(){
			AjaxRefresh("USERID=<?php echo $userId; ?>&TAGID=CTYPEUID&PRINCIPALCHAINID=&PRINCIPALID=<?php echo $principalId; ?>&ONCHANGE=resetFromParamChange();",
						"<?php echo $ROOT.$PHPFOLDER ?>elements/ajaxPrincipalChainsDD.php",
						"ajaxOptionDivChain",
						"Please wait while list of chains is retrieved... this could take a few moments....",
						"");
		}

		function submitParams(){

			var TYPE = document.getElementById("TYPE").value;
			if (TYPE=="<?php echo CT_CHAIN ?>"){
			  var TYPEUID = document.getElementById("CTYPEUID").value;
			} else if (TYPE=="<?php echo CT_STORE ?>") {
			  var TYPEUID = document.getElementById("STYPEUID").value;
			}

			AjaxRefresh("TYPE="+TYPE+"&TYPEUID="+TYPEUID+"&LEVEL="+convertElementToArray(document.getElementsByName("LEVEL")),
						"<?php echo $ROOT.$PHPFOLDER ?>functional/deals/dealForm.php",
						"dealsdiv",
						"Please wait while Data Form is retrieved...",
						"");
		}

		loadPC();

		var autoSaveStarted=false,
			intervalTimer;
		function restartSession(){
			createCookie("session","INactive",7);
			autoSaveStarted=false;
			clearInterval(intervalTimer);
		}
		function resetFromParamChange() {
			document.getElementById("dealsdiv").innerHTML="Click Submit to Continue";
			clearInterval(intervalTimer);
			restartSession();
		}
		</SCRIPT>

		<?php
	    echo "</TD>
			  </tr>";

	    echo "<tr>
			  <td>Which level would you like to add Prices at ?</td>
			  <td>
				  <input type='radio' name='LEVEL' value='".PRT_PRODUCT."' ".(($postLEVEL==PRT_PRODUCT)?"CHECKED":"")." onclick='resetFromParamChange();'>Product
				  <input type='radio' name='LEVEL' value='".PRT_PRODUCT_GROUP."' ".(($postLEVEL==PRT_PRODUCT_GROUP)?"CHECKED":"")." onclick='resetFromParamChange();'>Product Group
			  </TD>
			  </tr>";
	    echo "<tr style='text-align:center;'><td colspan=2>
					<input type='submit' class='submit' value='Submit' onclick='submitParams();' />
				  </td></tr>
			  </TABLE>";

	    $dbConn->dbClose();

	    echo "<div id='dealsdiv'></div>";

	    return;

	} else {
		if ((($postTYPE=="") || ($postTYPEUID=="") || ($postLEVEL=="")) && ($postDEALUID=="")) {
			echo "Please enter all parameters...";
			return;
		}

		$productDAO = new ProductDAO($dbConn);
     	getDeals($postTYPE,$postTYPEUID);
     	?>
     	<SCRIPT type='text/javascript' defer>
     	var totalRows=<?php echo $totalRows ?>;

		function addRow() {
			if (!document.getElementsByTagName) { alert('Your browser does not support dynamic row add. Please contact RetailTrading.'); return; }

			var tableBody = document.getElementById('dealTblBody');
			var root=tableBody.getElementsByTagName('tr')[0].parentNode; //the TBODY
			var clone=tableBody.getElementsByTagName('tr')[0].cloneNode(true); //the clone of the first row
			root.appendChild(clone); //appends the clone
			document.getElementsByName('DEALTYPE[]')[totalRows].value=document.getElementsByName('DEALTYPE[]')[totalRows-1].value;
	        totalRows++;
	        // adjustMyFrameHeight(); // this causes the screen to hang due to positioner image moving
		}

		function restoreContent() {
			<?php if ($postDMLTYPE!="INSERT") echo "alert('restore not available for UPDATEs, only new prices'); return;"; ?>
			var col0=readCookie("col0"); if (col0!=null) col0=col0.split(','); else col0=new Array();
			    col1=readCookie("col1"); if (col1!=null) col1=col1.split(','); else col1=new Array();
				col2=readCookie("col2"); if (col2!=null) col2=col2.split(','); else col2=new Array();
				col3=readCookie("col3"); if (col3!=null) col3=col3.split(','); else col3=new Array();
				col4=readCookie("col4"); if (col4!=null) col4=col4.split(','); else col4=new Array();
				col5=readCookie("col5"); if (col5!=null) col5=col5.split(','); else col5=new Array();
				col6=readCookie("col6"); if (col6!=null) col6=col6.split(','); else col6=new Array();
				col7=readCookie("col7"); if (col7!=null) col7=col7.split(','); else col7=new Array();
				col8=readCookie("col8");
				col9=readCookie("col9");
				col10=readCookie("col10");
			for (i=0; i<col1.length; i++) {
				if ((i+1)>totalRows) addRow();
				document.getElementsByName('REFERENCE[]')[i].value=col0[i];
				document.getElementsByName('PRODUCT[]')[i].value=col1[i];
				document.getElementsByName('LISTPRICE[]')[i].value=col2[i];
				document.getElementsByName('DEALTYPE[]')[i].value=col3[i];
				document.getElementsByName('VALUE[]')[i].value=col4[i];
				document.getElementsByName('EXCLINCL[]')[i].value=col5[i];
				document.getElementsByName('STARTDATE[]')[i].value=col6[i];
				document.getElementsByName('ENDDATE[]')[i].value=col7[i];
			}
			startAutoSaveSession();
			alert('Content has been restored.');
		}

		function checkForRestore() {
			var TYPE = document.getElementById("TYPE").value;
			// check to see if there is a session
			if (
				// dont check this as it gets reset on param change (readCookie("session")=="active") &&
				(readCookie("col8")==TYPE) &&
				(readCookie("col10")==convertElementToArray(document.getElementsByName("LEVEL")))
			   ) {
  				if((TYPE == "<?php echo CT_CHAIN ?>" && (readCookie("col9")==document.getElementById("CTYPEUID").value)) ||
  				   (TYPE == "<?php echo CT_STORE ?>" && (readCookie("col9")==document.getElementById("STYPEUID").value))
  				){
  					var answer = confirm('There is an active autorecovery session. Would you like to restore to this session ?');
  					if (!answer) return "cancelled"; else restoreContent();
				  }
			}
		}

		// remember that effectively, the save wont be activated for updates as product is disabled and that is the trigger to start it.
		function saveContent() {
			var col0=convertElementToArrayOther(document.getElementsByName("REFERENCE[]")),
			    col1=convertElementToArrayOther(document.getElementsByName("PRODUCT[]")),
				col2=convertElementToArrayOther(document.getElementsByName("LISTPRICE[]")),
				col3=convertElementToArrayOther(document.getElementsByName("DEALTYPE[]")),
				col4=convertElementToArrayOther(document.getElementsByName("VALUE[]")),
				col5=convertElementToArrayOther(document.getElementsByName("EXCLINCL[]")),
				col6=convertElementToArrayOther(document.getElementsByName("STARTDATE[]")),
				col7=convertElementToArrayOther(document.getElementsByName("ENDDATE[]"));
				col8=document.getElementById("TYPE").value;
				if (col8=="<?php echo CT_CHAIN ?>"){
				  col9 = document.getElementById("CTYPEUID").value;
				} else if (col8=="<?php echo CT_STORE ?>"){
				  col9 = document.getElementById("STYPEUID").value;
				}
				col10=convertElementToArray(document.getElementsByName("LEVEL"));

			// to block against page change leaving timer still running
			if (col1.length==0) {
				autoSaveStarted=false;
				clearInterval(intervalTimer);
				return;
			}

			createCookie("session","active",7);
			createCookie("col0",col0,7);
			createCookie("col1",col1,7);
			createCookie("col2",col2,7);
			createCookie("col3",col3,7);
			createCookie("col4",col4,7);
			createCookie("col5",col5,7);
			createCookie("col6",col6,7);
			createCookie("col7",col7,7);
			createCookie("col8",col8,7);
			createCookie("col9",col9,7);
			createCookie("col10",col10,7);

			var date = new Date();
			document.getElementById('autoSaveMsg').innerHTML = 	'AutoSaved last: '+date.toLocaleString();
		}

		function startAutoSaveSession() {
			<?php if ($postDMLTYPE!="INSERT") echo "alert('Auto Recovery not available for UPDATEs, only new prices'); return;"; ?>
			if (autoSaveStarted) return;
			autoSaveStarted=true;
			// do an autosave every 15 seconds
			intervalTimer=setInterval ("saveContent();", 15000);
		}

		<?php
		// create the JS for inteli lookups
		$mfPP=$productDAO->getUserPrincipalProductsArray($principalId,$userId);
		echo "var ppArrPC=new Array();";
		echo "var ppArrPD=new Array();";
		echo "var ppArrUID=new Array();";
		if (sizeof($mfPP)>5000) {
			echo "alert('WARNING: Size of ProductList returned for JS Lookups has exceeded allowable entries. Lookups have been disabled. Please inform RetailTrading Management.')";
		} else {
				$i=0;
				foreach ($mfPP as $row) {
					echo "ppArrUID[".$i."]=\"".str_replace('"','',$row['uid'])."\";";
					echo "ppArrPC[".$i."]=\"".str_replace('"','',$row['product_code'])."\";";
					echo "ppArrPD[".$i."]=\"".str_replace('"','',$row['product_description'])."\";";
					$i++;
				}
		  }
		?>
		var prevFld;
		function showSpecialDD(event, fld) {
			// short circuit this because esc could apply to other things if block not open
			switch (event.keyCode) {
				case 32: {
							$("#ddSearch").css({display:'block',width:300,height:400});
							prevFld=fld;
							document.getElementById('ddSearchInput').focus();
							document.getElementById('ddSearchInput').value="";
							document.getElementById('ddSearchContent').innerHTML="";
							break;
				         }
				case 27: {
							$("#ddSearch").css({display:'none'});
							prevFld.focus();
							break;
				         }
				default: {
						 	return;
				         }
			}
		}

		function selectDDVal(uid) {
			$("#ddSearch").css({display:'none'});
			prevFld.focus();
			prevFld.value=uid;
			var row=prevFld.parentNode.parentNode.rowIndex-tblHdrCnt;
			if (row>=0) getPrice(row);
		}

		function suggest(fldName) {
			var list = new String();
			var matchCnt=0;
			var fullName;
			var fld=document.getElementsByName(fldName);
			var val=fld[0].value.toLowerCase();
			if (val.length==0) { parent.hideMsgBoxSystemFeedbackAll(); return; }
			var pattern = new RegExp(val.replace(/[^a-zA-Z0-9]+/g,'')); // leave only alpha chars and digits
			for (i=0; ((i<ppArrPC.length) && (matchCnt<2000)); i++) {
				switch (fldName) {
					case "ddSearchInput": if (pattern.test(ppArrPC[i].replace(/[^a-zA-Z0-9]+/g,'').toLowerCase()+ppArrPD[i].replace(/[^a-zA-Z0-9]+/g,'').toLowerCase())) {
									  list += "<tr><td class='tableReset standardFont' style='padding:5px;'><A href='javascript:' onclick='selectDDVal("+ppArrUID[i]+");'>"+ppArrPC[i]+"</A></td><td class='tableReset standardFont' style='padding:5px;'>"+ppArrPD[i]+"</td></tr>";
									  matchCnt++;
									  if (matchCnt>(2000-1)) list += "<tr><td colspan=2 class='tableReset standardFont' style='padding:5px;'><I><B>list incomplete...list exceeds 2000.</B></I></td></tr>";
									 }
									 fullName = 'Product Code+Description';
									 break;
				}
			}
			if (list.length > 0) {
				// user must use scroll wheel on mouse to scroll because otherwise onblur first to hide list
				list = "<div style='height:250px;'><B>Principal-Products with Similar "+fullName+":</B><BR><BR><table class='tableReset'>"+list+"</table></div>";
				document.getElementById('ddSearchContent').innerHTML=list;
			} else {
				document.getElementById('ddSearchContent').innerHTML="";
			  }
		}

		<?php if ($postDMLTYPE=="INSERT") { ?>
		setTimeout("checkForRestore();",1000);
		for (i=0; i<5; i++) {
			addRow();
		}
		adjustMyFrameHeight();
		<?php } ?>

		var alreadySubmitted=false;
		function submitContentForm(p_type) {
			if (alreadySubmitted) {
				return;
			}
			alreadySubmitted=true;

			var params='DMLTYPE='+p_type;

			params+='&CUSTOMERTYPE='+convertElementToArray(document.getElementsByName("CUSTOMERTYPE[]"));
			params+='&CHAINSTOREUID='+convertElementToArray(document.getElementsByName("CHAINSTOREUID[]"));
			params+='&PRICETYPE='+convertElementToArray(document.getElementsByName("PRICETYPE[]"));
			params+='&REFERENCE='+convertElementToArrayOther(document.getElementsByName("REFERENCE[]"));
			params+='&PRODUCT='+convertElementToArrayOther(document.getElementsByName("PRODUCT[]"));
			params+='&LISTPRICE='+convertElementToArray(document.getElementsByName("LISTPRICE[]"));
			params+='&DEALTYPE='+convertElementToArrayOther(document.getElementsByName("DEALTYPE[]"));
			params+='&VALUE='+convertElementToArray(document.getElementsByName("VALUE[]"));
			params+='&EXCLINCL='+convertElementToArrayOther(document.getElementsByName("EXCLINCL[]"));
			params+='&STARTDATE='+convertElementToArray(document.getElementsByName("STARTDATE[]"));
			params+='&ENDDATE='+convertElementToArray(document.getElementsByName("ENDDATE[]"));

			params+='&DEALUID=<?php echo $postDEALUID; ?>';
			<?php if ($postDMLTYPE=="UPDATE") { ?>
				params+='&DELETED='+convertElementToArrayEnforceBlankValue(document.getElementsByName("DELETED[]"),"0");
			<?php } ?>

			params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
			AjaxRefreshWithResult(params,
								  '<?php echo $ROOT.$PHPFOLDER ?>functional/deals/dealSubmit.php',
								  'alreadySubmitted=false; if(msgClass.type=="S") successfullyProcessed();',
								  'Please wait while request is processed...');
		}

		function successfullyProcessed() {
			var fld=document.getElementsByName('PRODUCT[]');
			for (var i=0; i<fld.length; i++) {
				document.getElementsByName('REFERENCE[]')[i].value="";
				document.getElementsByName('PRODUCT[]')[i].value="";
				document.getElementsByName('LISTPRICE[]')[i].value="";
				document.getElementsByName('DEALTYPE[]')[i].value="1";
				document.getElementsByName('VALUE[]')[i].value="0";
			}
			if(typeof restartSession == 'function') restartSession(); // if coming from modify screen, it wont exist
		}
		function extendDates() {
			var val=document.getElementById('extDate').value;
			var rows=document.getElementsByName('ENDDATE[]');
			for (var i=0; i<rows.length; i++) {
				rows[i].value=val;
			}
			parent.showMsgBoxInfo('All Dates successfully changed.\n\nPlease NOTE :\nYou must still click SUBMIT to save the changes!');
		}

		function getPrice(row) {
			if (convertElementToArray(document.getElementsByName('defaultIP'))=="F") return;

			var product=document.getElementsByName('PRODUCT[]')[row].value,
				pricetype=convertElementToArray(document.getElementsByName('LEVEL')),
				lp=document.getElementsByName('LISTPRICE[]')[row];

			if (product=="") { return; }
			params='&PRODUCTID='+product+'&PRICETYPE='+pricetype;

			parent.showMsgBoxSystemFeedback('Loading Price ...');
			$.ajax({
			  url: "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/getGenericChainPrice.php",
			  global: false,
			  type: 'POST',
		      data: params,
		      dataType: 'html',
			  cache: false,
			  success: function(msg){
			  	try {
			  		var innerRow=row,
			  			innerLP=lp;
			  		eval(msg);
			  		if ((msgClass.type=="S") && (isNumeric(msgClass.identifier))) {
			  			if ((innerLP.value>0) && (innerLP.value!=msgClass.identifier)){
			  				 alert('The fetched default for row '+(innerRow+1)+' should be : '+msgClass.identifier+'. The existing value was NOT overwritten!');
			  			} else innerLP.value=msgClass.identifier;

			  		}
			  	} catch (e) { alert('an unexpected error occurred:'+e.description+msg); }
			  	parent.hideMsgBoxSystemFeedback('Loading Price ...');
			  },
			  error: function(XMLHttpRequest, textStatus, errorThrown) {
				  parent.hideMsgBoxSystemFeedback('Loading Price ...');
			  }
		  });
		}

		// used to see how many header rows above the first line. Needs this value to return the zero-based index
		var tblHdrCnt=document.getElementById('dealTblBody').getElementsByTagName('tr')[0].rowIndex;

		adjustMyFrameHeight();

		</SCRIPT>
		<?php
	  }


	function getDeals($type,$typeId) {
		global $principalId; global $dbConn; global $totalRows; global $postDMLTYPE; global $arrDEALUID; global $productDAO; global $hasDPRole; global $administrationDAO; global $userId; global $postTYPE;
		global $DHTMLROOT; global $PHPFOLDER; global $postLEVEL; global $postTYPEUID;

		$hasEEDRole = $administrationDAO->hasRole($userId,$principalId,ROLE_EXTEND_ENDDATE_PRICE);
    	$hasDPRole = $administrationDAO->hasRole($userId,$principalId,ROLE_DELETE_PRICE);

		$dateStr    = gmdate("Y-m-d", time()+7200);

		// for the popup DD search
		echo "<div id='ddSearch' style='font-family:Verdana,Arial,Helvetica,sans-serif; font-size:0.8em; display:none; position:absolute; z-index:1000; background-color:#99cc99; border-style:solid; border-width:1px; overflow:auto;'>";
		?>
		 <input id='ddSearchInput' type='text' value='' size=15 maxlength=20 onkeyup='suggest("ddSearchInput"); showSpecialDD(event,"ddSearchInput")' />&nbsp; {ESC Exits}
		 <HR>
		 <div id='ddSearchContent' style='font-family:Verdana,Arial,Helvetica,sans-serif; font-size:0.9em;'></div>
		<?php
		echo "</div>";

		echo "<DIV style='font-family:Verdana,Arial,Helvetica,sans-serif; color:grey; font-size:0.85em' id='autoSaveMsg' >AutoSaved last: </div>";

		 echo "<TABLE id='dealTbl' width=\"100%\">\n";
		 echo "<thead>
			   <tr><th colspan=1>
					Extend End Dates : ";
		DatePickerElement::getDatePicker("extDate",$dateStr);
		echo  "<input type='submit' class='submit' value='Apply this value' onclick='extendDates();' />
				   </th>";
		if ($postDMLTYPE=="INSERT") {
			echo  "<th colspan=8><input type='radio' name='defaultIP' value='T' CHECKED > Default Item Price ON&nbsp;<input type='radio' name='defaultIP' value='F'> Default Item Price OFF</th>";
		}

		echo "</tr>
			   <tr>";
		 echo "<th scope=\"col\">Product<BR>Code</th>"
		      ."<th scope=\"col\">List<BR>Price</th>"
		      ."<th scope=\"col\">Deal<BR>Type</th>"
		      ."<th scope=\"col\">Discount<BR>Value</th>"
		      ."<th scope=\"col\">V.A.T</th>"  //
		      ."<th scope=\"col\">Start<BR>Date</th>"  //
		      ."<th scope=\"col\">End<BR>Date</th>"
		      ."<th scope=\"col\">Reference</th>";
		      if ($postDMLTYPE=="UPDATE") {
		      	echo "<th scope=\"col\">Deleted</th>";
		      }
		 echo "</tr>
			   </thead>";
		 if ($postDMLTYPE=="INSERT") {
			 echo "<tfoot>";
			 echo "<tr>".
			      "<td colspan=8> <input type='button' name='addmore' id='addmore' class='submit' value='Add Another Row' onclick='addRow(); adjustMyFrameHeight();'/></td>".
				  "</tr>".
				  "</tfoot>";
		 }
		 echo "<tbody id='dealTblBody' style=\"font-size: 11px;\">";

				// UPDATE
				$i=0;
				if ($postDMLTYPE=="UPDATE") {
					foreach ($arrDEALUID as $val) {
						$mfDeal=$productDAO->getUserPrincipalPricingItem($userId,$principalId,$val);
						if ($mfDeal[0]['excl_incl']!="E") { echo "ERROR: a row being edited was not set to VAT EXCLUSIVE ! Please notify Retail Trading"; return; };
						if ($i % 2 == 0) { echo "<TR id='tr_".$i."' class='odd'>\n";	} else { echo "<TR id='tr_".$i."' class='even'>"; }
						echo "<TD width=\"150px\">";
						// edits can make these headers fields mixed, so store with each line
						echo "<INPUT name='CUSTOMERTYPE[]' type='hidden' value='{$mfDeal[0]['customer_type_uid']}' />";
						echo "<INPUT name='CHAINSTOREUID[]' type='hidden' value='{$mfDeal[0]['chain_store']}' />";
						echo "<INPUT name='PRICETYPE[]' type='hidden' value='{$mfDeal[0]['price_type_uid']}' />";
						// save time by not calling the proper DD, hardcode single value - not necessary to check for price_type_uid, as the supply function swaps out values accordingly
						echo drawSelect("PRODUCT[]", array($mfDeal[0]['product_code']." - ".$mfDeal[0]['product_description']), array($mfDeal[0]['principal_product_uid']), array($mfDeal[0]['principal_product_uid']), "disabled onChange=''");
						echo "</TD>";
						echo "<TD><INPUT name='LISTPRICE[]' class='txtinputr' type='text' size='9' maxlength='8' value='".$mfDeal[0]['list_price']."' onChange='' disabled></TD>";
						echo "<TD>"; BasicSelectElement::getDealTypesDD("DEALTYPE[]",$mfDeal[0]['dtuid'],"N","Y",null,null,null,$dbConn);  echo "</TD>";
				  		echo "<TD><INPUT name='VALUE[]' class='txtinputr' type='text' size='9' maxlength='8' value='".$mfDeal[0]['discount_value']."' disabled></TD>";
						echo "<TD>" . drawSelect("EXCLINCL[]", array("Exclusive" ), array("EXCL" ), $mfDeal[0]['excl_incl'], "disabled class='txtinput' onChange=''") . "</TD>";
				  		echo "<TD>";
				  		echo "<INPUT name='STARTDATE[]' class='txtinputr' type='text' size='9' maxlength='8' value='".$mfDeal[0]['start_date']."' onChange='' disabled>";
				  		echo "</TD>";
				  		echo "<TD>";
				  		if ($hasEEDRole) DatePickerElement::getDatePicker("ENDDATE[]",$mfDeal[0]['end_date']);
				  		else echo "<INPUT name='ENDDATE[]' class='txtinputr' type='text' size='9' maxlength='8' value='".$mfDeal[0]['end_date']."' onChange='' disabled>";
				  		echo "</TD>";
				  		echo "<TD><INPUT name='REFERENCE[]' class='txtinputr' type='text' size='20' maxlength='50' value='".$mfDeal[0]['reference']."' onChange='' disabled></TD>";
				  		if ($mfDeal[0]['deleted']==1) $checked="checked"; else $checked="";
				  		if ($hasDPRole)	echo "<TD><INPUT name='DELETED[]' type='checkbox' value='1'".$checked."/></TD>";
				  		else echo "<TD><INPUT name='DELETED[]' type='checkbox' value='1'".$checked." disabled /></TD>";
				  		echo "</TR>";
				  		$i++;
					}
				}
				// INSERT
				else if ($postDMLTYPE=="INSERT") {
					for ($i=0; $i<$totalRows; $i++) {
						if ($i % 2 == 0) { echo "<TR id='tr_".$i."' class='odd'>\n";	} else { echo "<TR id='tr_".$i."' class='even'>"; }
						echo "<TD width=\"150px\">";
						// edits can make these headers fields mixed, so store with each line
						echo "<INPUT name='CUSTOMERTYPE[]' type='hidden' value='{$postTYPE}' />";
						echo "<INPUT name='CHAINSTOREUID[]' type='hidden' value='{$postTYPEUID}' />";
						echo "<INPUT name='PRICETYPE[]' type='hidden' value='{$postLEVEL}' />";
						if ($postLEVEL==PRT_PRODUCT) {
							BasicSelectElement::getPrincipalProductsDD("PRODUCT[]","","N","N","startAutoSaveSession(); getPrice(parentNode.parentNode.rowIndex-tblHdrCnt);",null,null,"onKeyUp='showSpecialDD(event,this);'",$dbConn,$principalId, $userId);
						} else {
							BasicSelectElement::getPrincipalProductCategoryDD("PRODUCT[]", "", "N","N", "getPrice(parentNode.parentNode.rowIndex-tblHdrCnt);",null,null, $dbConn, $principalId, FLAG_STATUS_ACTIVE);
						}
						echo "</TD>";
						echo "<TD><INPUT name='LISTPRICE[]' class='txtinputr' type='text' size='9' maxlength='8' value='0' onChange=''></TD>";
						echo "<TD>"; BasicSelectElement::getDealTypesDD("DEALTYPE[]","1","N","N",null,null,null,$dbConn);  echo "</TD>";
				  		echo "<TD><INPUT name='VALUE[]' class='txtinputr' type='text' size='9' maxlength='8' value='0' ></TD>";
						echo "<TD>" . drawSelect("EXCLINCL[]", array("Exclusive" ), array("EXCL" ), "", "class='txtinput' onChange=''") . "</TD>";
				  		echo "<TD>";
				  		DatePickerElement::getDatePicker("STARTDATE[]",$dateStr);
				  		echo "</TD>";
				  		echo "<TD>";
				  		DatePickerElement::getDatePicker("ENDDATE[]",$dateStr);
				  		echo "</TD>";
				  		echo "<TD><INPUT name='REFERENCE[]' class='txtinputr' type='text' size='20' maxlength='50' value='' onChange=''></TD>";
				  		echo "</TR>";
					}
				}

		 echo "</tbody>";
		 echo "</TABLE><BR>";
		 echo "<INPUT class='submit' type=\"button\" onclick='submitContentForm(\"".$postDMLTYPE."\");' value=\"Submit Deal\">";

		 echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable
	}

echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable
echo "</BODY>";
echo "</HTML>";
$dbConn->dbClose();
?>

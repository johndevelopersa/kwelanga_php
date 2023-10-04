<?php

include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'elements/Messages.php'); // ie8 layers z-index weirdly, so popup in this form will appear under parent's modal layer regardless of z-index
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$userCategory = $_SESSION['category'];


$dbConn = new dbConnect();  //Create new database object
$dbConn->dbConnection();  //Database connection method


$administrationDAO = new AdministrationDAO($dbConn);
$mfUP=$administrationDAO->getUserPreferences($userId);

if (sizeof($mfUP)==0) {

    $rsUId="";
    $rsPageSizeDefault = 10;
    $rsTTDAYGAP = 1;
    $rsTTCOLUMNS = false;
    $DMLType='INSERT';
    $rsNotifyException = "N";
    $rsNotifyDepotOrder = "N";
    $rsReportOutputSet = '';
    $rsCapturePreValidation = 'N';
    $rsProductSortBy = 'D';
    $rsDisplayAccessLog = 'Y';

} else {

    $rsUId=$mfUP[0]["uid"];
    $rsPageSizeDefault=$mfUP[0]["page_size_default"];
    $rsTTDAYGAP = $mfUP[0]["tracking_transaction_day_gap"];
    $rsTTCOLUMNS = $mfUP[0]["tracking_transaction_columns"];
    $rsNotifyException = $mfUP[0]["notify_exception_tag"];
    $rsNotifyDepotOrder = $mfUP[0]["notify_depot_order_tag"];
    $rsReportOutputSet = $mfUP[0]["user_report_output_setting"];
    $rsCapturePreValidation = $mfUP[0]["capture_pre_validation_flag"];
    $rsProductSortBy = $mfUP[0]["sort_product_dropdown"];
    $rsDisplayAccessLog = $mfUP[0]["display_access_log"];
    if ($rsPageSizeDefault=="") $rsPageSizeDefault="0";
    $DMLType='UPDATE';
}

echo "<script type=\"text/javascript\" language=\"javascript\" src=\"{$DHTMLROOT}{$PHPFOLDER}js/jquery.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"{$DHTMLROOT}{$PHPFOLDER}js/dops_global_functions.js\"></script>
		<link href='{$DHTMLROOT}{$PHPFOLDER}css/1_default.css' rel='stylesheet' type='text/css'>";
?>
<SCRIPT type="text/javascript">
	var alreadySubmitted=false;
	function submitContentForm() {
		if (alreadySubmitted) {return;}
		alreadySubmitted=true;
		params='DMLTYPE='+document.getElementById('DMLTYPE').value;
		params+='&UID='+document.getElementById('FORM_UID').value;
		params+='&FORM_DPS='+convertElementToArray(document.getElementsByName('FORM_DPS'));
		params+='&TTDAYGAP='+document.getElementById('TTDAYGAP').value;
		params+='&NOTIFYEXCEPTIONTAG='+convertElementToArray(document.getElementsByName('NOTIFYEXCEPTIONTAG'));
		params+='&NOTIFYDEPOTORDERTAG='+convertElementToArray(document.getElementsByName('NOTIFYDEPOTORDERTAG'));
		params+='&REPORTOUTSET=' + document.getElementById('REPORTOUTSET').value;
                params+='&CAPTUREVALIDATION='+convertElementToArray(document.getElementsByName('CAPTUREVALIDATION'));
                params+='&PRODUCTSORTBY='+convertElementToArray(document.getElementsByName('PRODUCTSORTBY'));
                params+='&DISPLAYACCESSLOG='+convertElementToArray(document.getElementsByName('DISPLAYACCESSLOG'));

		var i = 0;
		var transCol = new Array();
	  	jQuery('#transColActive option').each( function() {
	  	  transCol[i] = $(this).val();
	      i++;
	    });

	  	params+='&TTCOLUMNS='+transCol.join(',');	//order saved = order displayed.

		params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
		parent.showSubMsgBoxModal();
		AjaxRefreshWithResult(params,
							  '<?php echo $ROOT.$PHPFOLDER; ?>functional/administration/userPreferencesSubmit.php',
							  'alreadySubmitted=false; parent.hideSubMsgBoxModal(); if (msgClass.type=="S") postSuccessful();',
							  'Please wait while request is processed...');
	}

	function postSuccessful() {
		if (parent.userPreference.notifyExceptionTag==undefined) {
			alert('Could not set userPreference object in parent during userPreference submit.');
			return;
		}
		parent.userPreference.notifyExceptionTag=convertElementToArray(document.getElementsByName('NOTIFYEXCEPTIONTAG'));
	}
</SCRIPT>
<?php

echo '<div align="center"><br>';

echo "<FORM id='preferencesForm' name='preferencesForm' action='{$ROOT}{$PHPFOLDER}functional/administration/userPreferencesSubmit.php' method='post' >";
echo "<INPUT type='hidden' value='{$DMLType}' id='DMLTYPE'>
      <INPUT type='hidden' value='{$rsUId}' id='FORM_UID'>";

echo '<TABLE width="700">';
echo '<THEAD><TR class="'.GUICommonUtils::styleEO($class).'">
		<TH colSpan="2" >Your Preferences</TH>
	 </TR></THEAD>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo '<TD width="300" >Default Page Size (rows)</TD>';
	echo '<TD width="400">';
	  BasicSelectElement::getGeneralDD("FORM_DPS","10,50,100,250,500,1000,Not Limited","10,50,100,250,500,1000,0",$rsPageSizeDefault,"N","N",null,null,null);
	echo '</TD>';
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo '<TD width="300" >Report Output Setting</TD>';
	echo '<TD width="400">';
	  echo '<INPUT TYPE="TEXT" id="REPORTOUTSET" maxlength="5" size="5" value="'.$rsReportOutputSet.'">';
	echo '</TD>';
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo '<TD>Show Alert Tag when I have Exceptions (orders that cannot be processed via EDI)</TD>';
	echo '<TD>';
        BasicInputElement::getCSS3RadioHorizontal("NOTIFYEXCEPTIONTAG", "Enabled,Disabled", "Y,N", $rsNotifyException, false);
	echo '</TD>';
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo '<TD>Show Alert Tag when my depot has Unaccepted Orders (<b>Depot WMS users only</b>)</TD>';
	echo '<TD>';
        BasicInputElement::getCSS3RadioHorizontal("NOTIFYDEPOTORDERTAG", "Enabled,Disabled", "Y,N", $rsNotifyDepotOrder, (($userCategory==FLAG_DEPOT_USER)?false:true));
	echo '</TD>';
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo '<TD>Capture Pre-validation</TD>';
	echo '<TD>';
        BasicInputElement::getCSS3RadioHorizontal("CAPTUREVALIDATION", "Enabled,Disabled", "Y,N", $rsCapturePreValidation, false);
	echo '</TD>';
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo '<TD>Sort Product Lists by</TD>';
	echo '<TD>';
        BasicInputElement::getCSS3RadioHorizontal("PRODUCTSORTBY", "Description,Code", "D,C", $rsProductSortBy, false);
	echo '</TD>';
echo '</TR>';

echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo '<TD>Display Access Log</TD>';
	echo '<TD>';
        BasicInputElement::getCSS3RadioHorizontal("DISPLAYACCESSLOG", "Yes,No", "Y,N", $rsDisplayAccessLog, false);
	echo '</TD>';
echo '</TR>';


echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo '<TD>Transaction Tracking Day Gap</TD>';
	echo '<TD>';
	  BasicSelectElement::getGeneralDD("TTDAYGAP","1 Day,3 Days,7 Days,14 Days,30 Days","1,3,7,14,30",$rsTTDAYGAP,"N","N",null,null,null);
	echo '</TD>';
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo '<TD colSpan="2">Transaction Tracking Columns (order of list matches screen)</TD>';
echo '</TR>';
echo '<TR><TD colSpan="2">';

	$transHeaders = array(
		array("Document Reference*",'documentNumber'),
		array("Order Date",'orderDate'),
		array("Invoice Date",'invoiceDate'),
		array("Delivery Date",'deliveryDate'),
    array('Requested Delivery Date','requestedDeliveryDate'),
    array('Due Delivery Date','dueDeliveryDate'),
		array("Customer",'customer'),
                array('EPOD*','epod'),
		array("Status",'status'),
		array("Cases",'cases'),
		array("Document Type",'documentType'),
		array("Invoice No",'invoiceNumber'),
		array("Delivery Day",'deliveryDay'),
		array("GRV No",'grvNumber'),
		array("Claim No",'claimNumber'),
		array("Customer Order No",'customerOrderNumber'),
		#array("Capture Seq",'orderSequenceNo'),
                array("Alternate Document Number",'alternateDocumentNumber'),
		array("Source Document No",'sourceDocumentNumber'),
		array("Depot",'depotName'),
		array('Incoming File','inFile'),
		array('Processed Date','processDate'),
		array('Processing Detail*','processingDetail'),
		array('Resource Imagery','resourceImagery'),
		array('Special Field(s)','specialFields')
	);


	$jsArrString = 'var ttOptArr = [];'."\n";
	$no = 0;
	foreach($transHeaders as $head){
	  $jsArrString .= 'ttOptArr['.$no.'] = ["'.$head[0].'","' . $head[1] .'"];'."\n";;
	  $no++;
	}

	echo '<br>';

echo '<script>';
echo <<<EOF

{$jsArrString}

function removeCol(){
  var sitem = $('#transColActive option:selected');
  var value = sitem.val();
  var text = sitem.text();
  var notAll = text.search(/\*/);
  if(notAll == -1){
    if(value.length > 1){
    	$('<option value="'+value+'">'+text+'</option>').appendTo('#transColRemoved');
    	sitem.remove();
  	}
  }
}

function addCol(){
  var sitem = $('#transColRemoved option:selected');
  var value = sitem.val();
  var text = sitem.text();
  if(value.length > 1){
  	$('<option value="'+value+'">'+text+'</option>').appendTo('#transColActive');
  sitem.remove();
  }
}

function sort(dir){

  var sitem = $('#transColActive option:selected');
  var svalue = sitem.val();
  var stext = sitem.text();

  var i = 0;
  $('#transColActive option').each( function() {
  i++;
  	if($(this).val() == svalue && $(this).text() == stext){
		var size = $('#transColActive option').size();
		if(dir == '+' && i>1){
  			$(this).remove();
			$('#transColActive option').eq(i-2).before('<option value="'+svalue+'" selected="selected">'+stext+'</option>');
  		} else if (dir == '-' && i < size){
  			$('#transColActive option').eq(i).after('<option value="'+svalue+'" selected="selected">'+stext+'</option>');
  			$(this).remove();
  		}
  	}
   });
}

function resetOpt(){

$('#transColActive').html('');
$('#transColRemoved').html('');

//loop through org array.
if(ttOptArr.length > 1){
var orgOpt = '';
for(var i = 0; i < ttOptArr.length; i++){
orgOpt += '<option value="'+ttOptArr[i][1]+'">'+ttOptArr[i][0]+'</option>';
}
$('#transColActive').html(orgOpt);
}
}


EOF;

echo '</script>';

	echo '<table style="border:0px;font-size:8pt;" align="center"><tr>';

	echo '<td valign="top">';
	    echo 'Current Columns<br>';
    	echo '<select size="12" id="transColActive" style="width:160px;">';

    	if($rsTTCOLUMNS!=false && count(explode(',',$rsTTCOLUMNS)) > 0){

    	  //columns are set and found - output in choosen display order....
    	  //therefore we remove a column from the trans tracking and a user has it saved as a pref - this will remove that column NOT display.
          $setCols = explode(',',$rsTTCOLUMNS);
    	  foreach($setCols as $col){
    	    $transName = false;
    	    foreach($transHeaders as $transCols){
    	      if($transCols[1] == $col){
    	        $transName = $transCols[0];
    	      }
    	    }
    	    if($transName != false){
    	      echo '<option value="'.$col.'">'.$transName.'</option>';
    	    }
    	    }
    	} else {

    	  //no columns set - display in array normal order
    	  foreach($transHeaders as $row){
    	    echo '<option value="'.$row[1].'">'.$row[0].'</option>';
    	  }
    	}

    	echo '</select>';

	echo '</td>';

	echo '<td width="70" align="left" style="padding:0px;margin:0px;">
			<input type="button" class="submit" onClick="sort(\'+\')" value=" &and; ">
			<br>
			<input type="button" class="submit" onClick="sort(\'-\')" value=" &or; ">
		 </td>';

	echo '<td>';
	    echo 'Available Columns<br>';
    	echo '<select size="12" id="transColRemoved" style="width:160px;">';

    	//loop through columns - if NOT in array - display.
    	foreach($transHeaders as $row){
    	  $setCols = explode(',',$rsTTCOLUMNS);
    	  if($rsTTCOLUMNS!=false && count($setCols) > 0 && !in_array($row[1],$setCols)){
    	    echo '<option value="'.$row[1].'">'.$row[0].'</option>';
    	  }
    	}

    	echo '</select>';

	echo '</td></tr>';
	echo '<tr>';
	echo '<td align="center">
			<input type="button" class="submit" onClick="removeCol()" value="Remove Column" style="margin:0px;">
			<input type="button" class="submit" onClick="resetOpt()" value="Reset">
		</td>';
	echo '<td></td>';
	echo '<td align="center"><input type="button" class="submit" onClick="addCol()" value="Add Column"></td>';
	echo '</tr>';
	echo '</table>';
    echo '<br>';

	echo '</TD>';
echo '</TR>';
echo '</TABLE>';

echo '<br><input type="button" class="submit" value="Submit Preferences" onclick="submitContentForm();" />';
echo '</FORM>';
echo '</DIV>';

?>
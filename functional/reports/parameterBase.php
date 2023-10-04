<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/ReportDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'lIbs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');


if (isset($_POST['TYPEID'])) $postTYPEID=$_POST['TYPEID']; else if (isset($_GET['TYPEID'])) $postTYPEID=$_GET['TYPEID']; else $postTYPEID="";
if (isset($_POST['REPORTID'])) $postREPORTID=$_POST['REPORTID']; else if (isset($_GET['REPORTID'])) $postREPORTID=$_GET['REPORTID']; else $postREPORTID=""; // used for reports only
if (isset($_POST['OBJUID'])) $postOBJUID=$_POST['OBJUID']; else if (isset($_GET['OBJUID'])) $postOBJUID=$_GET['OBJUID']; // used for others only
if (isset($_POST['SCHEDULER'])) $postSCHEDULER=$_POST['SCHEDULER']; else if (isset($_GET['SCHEDULER'])) $postSCHEDULER=$_GET['SCHEDULER']; else $postSCHEDULER="N"; // only passed if called from scheduler
if (isset($_POST['GENERALUSAGE'])) $postGENERALUSAGE=$_POST['GENERALUSAGE']; else if (isset($_GET['GENERALUSAGE'])) $postGENERALUSAGE=$_GET['GENERALUSAGE']; else $postGENERALUSAGE="N";

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$systemId = $_SESSION['system_id'];
$depotId = $_SESSION['depot_id'];

$calledFromScheduler=false;

$dbConn = new dbConnect();
$dbConn->dbConnection();

// role check
if ($postGENERALUSAGE=="N") {
	if ($postREPORTID=="") {
		echo "No Report ID supplied";
		return;
	}
	$adminDAO = new AdministrationDAO($dbConn);
	$hasRole=$adminDAO->hasRole($userId, $principalId,ROLE_REPORTS);
	if (!$hasRole) {
		echo "You do not have permissions to run reports!";
		return;
	}
	// get report details (includes role check within sql
	$reportDAO = new ReportDAO($dbConn);
	$mfR = $reportDAO->getReportItemForUser($userId,$principalId,$postREPORTID);

	if (sizeof($mfR)==0) {
		echo "Report not found or you do not have permissions to run this report (Interface:1,repId:{$postREPORTID},prin:{$principalId},user:{$userId})!";
		return;
	}
	$attrParameterFields=$mfR[0]["parameter_fields"];
	$attrRequiredFields=$mfR[0]["required_fields"];
	$attrOverrideFieldLabels=$mfR[0]["override_field_labels"];
	$attrInitialValues=$mfR[0]["initial_values"];
	$attrFieldLengths=$mfR[0]["field_lengths"];
	$attrObjName=$mfR[0]["report_name"];
	$attrObjDescription=$mfR[0]["report_description"];
	$attrLevel = $mfR[0]["report_level"];
} else {
	switch ($postTYPEID) {
		case "NOTIFICATION":
			include_once($ROOT.$PHPFOLDER.'DAO/BIDAO.php');
			$biDAO = new BIDAO($dbConn);
			$mfN=$biDAO->getNotificationItem($postOBJUID);
			if (sizeof($mfN)==0) {
				echo "Object Id not found.";
				return;
			}
			$attrParameterFields=$mfN[0]["additional_parameter_string"];
			$attrRequiredFields=$mfN[0]["additional_parameters_required"];
			$attrOverrideFieldLabels=$mfN[0]["override_field_labels"];
			$attrInitialValues=$mfN[0]["additional_parameter_values"];
			$attrFieldLengths="";
			$attrObjName=$mfN[0]["description"];
			$attrObjDescription="";
			$attrLevel = "";
			break;
		default:
			echo "Error : incorrect typeId passed!";
			return;
	}
}


// remember that these do not enforce element positioning.
$pPFArr=explode(",",$attrParameterFields);
$pRFArr=explode(",",$attrRequiredFields);
$pOFLArr=explode(",",$attrOverrideFieldLabels);
$pIVArr=explode(";",$attrInitialValues);
$pFLArr=explode(",",$attrFieldLengths);

//$paramsJS="var params='';";
$paramsJSFld="";
$paramsJSPop="";

function getDefaults($index) {
	// get default values
	$defaults=array();
	if (isset($_POST["p".$index])) $defaults=explode(",",$_POST["p".$index]);
	if (!isset($defaults[0])) $defaults[0]=""; // just to prevent an error later when checking
	return $defaults;
}

function multiToolbar($name){

  echo '<div style="line-height:22px;padding:0px 10px;border-bottom:1px solid lightSkyblue;">';

    //select all | none
    echo 'Select: ';
    echo '<a href="javascript:;" onClick="selectAll(\''.$name.'\', 1);">All</a> | ';
    echo '<a href="javascript:;" onClick="selectAll(\''.$name.'\', 0);">None</a>';

    //filter
    //echo '<span style="float:right">';
    //echo '<input type="" value="Filter" onChange="alert(\'ok\')">';
    //echo '</span>';

  echo '</div>';

}

// plain text input box
function f_IB($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="General Input Value";
	if ($pLength!="") $length=$pLength; else $length="50";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td><input name='".$pfName."' type='text' size='".$length."' maxlength='".$length."' value='".$pValue."' /></td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// plain textarea input box
function f_TA($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];


	if ($pLabel!="") $label=$pLabel; else $label="General Text Area Value";
	if ($pLength!="") $length=$pLength; else $length="1500";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>".
		 "<textarea name='".$pfName."' type='text' rows='5' cols='60' onchange='if (this.value.length>".$length.") alert(\"WARNING: only a max of ".$length." chars allowed. Entry truncated to fit.\"); this.value=this.value.substr(0,".$length.");' >".$pValue."</textarea>".
		 "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// depot List
function f_DEPOTMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');

	$depotDAO = new DepotDAO($dbConn);
	$mfD = $depotDAO->getAllDepotsForPrincipalArray($userId, $principalId);

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Depot(s)";
	if ($pLength!="") $length=$pLength; else $length="50";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

	//echo "<input type='button' class='submit' value='Select All Depots' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if ((($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfD as $row) {
		$value=$row['uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['depot_name']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// HISTORICAL Region List
function f_HDEPOTMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');
	$depotDAO = new DepotDAO($dbConn);
	$mfD = $depotDAO->getHistoricalRegionDD();

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Region(s)";
	if ($pLength!="") $length=$pLength; else $length="50";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Regions' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfD as $row) {
		$value=$row['id'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$row['id']."' ".$CHECKED." /></td><td>".$row['description']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}


// Allowed principals based on user.
function f_PRINCIPAL($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

    global $ROOT, $PHPFOLDER, $dbConn, $userId, $principalId, $systemId;
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
    include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');

    $adminDAO = new AdministrationDAO($dbConn);
    $prinArr = $adminDAO->getUsersPrincipals($userId, $systemId);

    $defaults=getDefaults($index);
    if ($pValue=="") $pValue=$defaults[0];
    if ($pLabel!="") $label=$pLabel; else $label="Principal";

    if ($pRequired)	{
      echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
    } else {
      echo "<td>".$label.":</td>";
    }

    $valueArr = $lableArr = array();
    foreach($prinArr as $p){
      $valueArr[] = $p['principal_id'];
      $lableArr[] = $p['principal_name'];
    }

    echo "<td style='text-align:top;'>";
      BasicSelectElement::buildGenericDD($pfName, $lableArr, $valueArr, $pValue, "N", "N", null, null, null);
    echo "</td>";

    getJSFld($pfName,$label,$index,$pRequired);
}

// Allowed principals based on user : MULTI
function f_PRINCIPALMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

    global $ROOT, $PHPFOLDER, $dbConn, $userId, $principalId, $systemId, $postSCHEDULER, $postGENERALUSAGE;
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
    include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');

    $defaults = getDefaults($index);
    if ($pLabel!="") $label=$pLabel; else $label="Principal(s)";

    if ($pRequired)	{
            echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
    } else {
            echo "<td>".$label.":</td>";
    }

    $adminDAO = new AdministrationDAO($dbConn);
    $prinArr = $adminDAO->getUsersPrincipals($userId, $systemId);

    echo '<td>';
    echo '<div style="overflow:auto; width:400px; height:100px; font-size:12px;">';

    //echo "<input type='button' class='submit' value='Select All Document Types' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
    multiToolbar($pfName);

    if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
      if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
      echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
      getJSFldAlternateFld("sa".$pfName,$index,"sa");
    }
    echo '<table class="tableReset">';
    foreach ($prinArr as $row) {
      $value = $row['principal_id'];
                      if ((in_array($value, $defaults)) || (in_array("*", $defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
      echo '<tr>';
      echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' " . $CHECKED . " /></td><td>" . $row['principal_name'] . "</td>";
      echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
    echo '</td>';


    getJSFld($pfName,$label,$index,$pRequired);
}


// Single Depot
function f_DEPOT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
    global $ROOT, $PHPFOLDER, $dbConn, $userId, $principalId, $systemId, $postSCHEDULER, $postGENERALUSAGE;
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Depot";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getUserDepotsForPrincipalDD($pfName,$pValue,"N","N",null,null,null,$dbConn,$userId,$principalId); echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// simple Radio Buttons, eg. yes no value pairs
function f_RB($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT, $PHPFOLDER;
	include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');

	$defaults=getDefaults($index);

	// first element is always the main lable, rest are the radio button lables
	$lbl=explode("?",$pLabel);
	$lblStr="";
	$val=explode("?",$pValue);
	$valStr="";

	// will get overridden later
	$label="General Radio Buttons";
	$value="";

	// the labels
	for($i=0; $i<sizeof($lbl); $i++) {
		if ($i==0) $label=$lbl[$i];
		else { if ($lblStr=="") $lblStr=$lbl[$i]; else $lblStr.=",".$lbl[$i]; }
	}
	// the values
	for($i=0; $i<sizeof($val); $i++) {
		if ($i==0) {
			$value=$val[$i];
			if ($defaults[0]!="") $value=$defaults[0]; // NB: normally other way around
		}
		else { if ($valStr=="") $valStr=$val[$i]; else $valStr.=",".$val[$i]; }
	}

	if (strlen($lblStr)==0) {
		echo "Cannot Generate Radio Buttons. Incorrect Label List supplied.";
		return;
	}

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; echo BasicInputElement::getGeneralHorizontalRB($pfName,$lblStr,$valStr,$value,"N","N",null,null,null); echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// date
function F_DATE($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $DHTMLROOT, $PHPFOLDER, $postSCHEDULER;

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="General Date";
	if ($defaults[0]!="") $pValue=$defaults[0]; // override early so that is validated properly
	if ($pValue!="") {
		// if proper date
		if (preg_match(GUI_PHP_DATE_VALIDATION,$pValue)) $value=$pValue;
		else if (preg_match("/^[+-]+[0-9]*$/",$pValue)) $value=CommonUtils::getUserDate($pValue);
		else if ($postSCHEDULER == 'Y') $value = CommonUtils::getUserDate();
		else $value="ERROR";
	} else {
		$value = CommonUtils::getUserDate();
	}

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";


    //DATE RANGES - ONLY FOR SCHEDULER
	if($postSCHEDULER == 'Y'){

	    $pV = explode(':',$pValue);
	    $pV = $pV[0];

	    //show options
    	echo  '<select id="DR_RANGE_'.$pfName.'" onChange="dateSelector(this,\'' . $pfName . '\')">';
        	echo '<option value="'.DR_DATE.'" >Date';  //if date will be the first one.
        	echo '<option value="'.DR_YESTERDAY.'" ' . (($pV==DR_YESTERDAY)?('SELECTED=""'):('')) . '>Minus # Days from Today';
        	echo '<option value="'.DR_CURRENT_WEEK_START.'" ' . (($pV==DR_CURRENT_WEEK_START)?('SELECTED=""'):('')) . '>Start of current week (Monday)';
        	echo '<option value="'.DR_CURRENT_WEEK_END.'" ' . (($pV==DR_CURRENT_WEEK_END)?('SELECTED=""'):('')) . '>End of current week (Sunday)';
        	echo '<option value="'.DR_LAST_WEEK_START.'" ' . (($pV==DR_LAST_WEEK_START)?('SELECTED=""'):('')) . '>Start of previous week (Monday)';
        	echo '<option value="'.DR_LAST_WEEK_END.'" ' . (($pV==DR_LAST_WEEK_END)?('SELECTED=""'):('')) . '>End of previous week (Sunday)';
        	echo '<option value="'.DR_CURRENT_MONTH_START.'" ' . (($pV==DR_CURRENT_MONTH_START)?('SELECTED=""'):('')) . '>Start of current month';
        	echo '<option value="'.DR_CURRENT_MONTH_END.'" ' . (($pV==DR_CURRENT_MONTH_END)?('SELECTED=""'):('')) . '>End of current month';
        	echo '<option value="'.DR_NO_MONTH_START.'" ' . (($pV==DR_NO_MONTH_START)?('SELECTED=""'):('')) . '>Start of # months ago';
        	echo '<option value="'.DR_NO_MONTH_END.'" ' . (($pV==DR_NO_MONTH_END)?('SELECTED=""'):('')) . '>End of # months ago';
        echo '</select>&nbsp; ';


        //handling of hide / show
        $css = 'STYLE="display:none"';
        $rangeParam = 1;
        if($pV == DR_YESTERDAY || $pV == DR_NO_MONTH_START || $pV==DR_NO_MONTH_END){  //dont hide param if this is set.
          $css = '';
          $rangeParam = explode(':',$pValue);
          $rangeParam = (isset($rangeParam[1])) ? $rangeParam[1] : 1;
        }

        //extra param for date range - no validation so converted to selector, NOT FREE TEXT.
        echo '<span id="rparm_'.$pfName.'" '.$css.'>';
        $opt = range(0,20);
        echo '<select id="DR_PARM_'.$pfName.'" >';
        foreach($opt as $o){
          $sel = ($rangeParam == $o) ? ('SELECTED=""') : ('');
          echo '<option value="'.$o.'" '.$sel.'>' . $o;
        }
        echo '</select>';
        echo '</span>';
        //<input type="text" maxlength="2" size="10" value="'.$rangeParam.'" ></span>';

        if(in_array($pV, array(DR_YESTERDAY, DR_CURRENT_WEEK_START, DR_CURRENT_WEEK_END, DR_LAST_WEEK_START, DR_LAST_WEEK_END, DR_CURRENT_MONTH_START, DR_CURRENT_MONTH_END, DR_NO_MONTH_START, DR_NO_MONTH_END))){
          echo "<script>$('#dId_{$pfName}').hide();</script>"; //hide the date selector
        }

	}

	DatePickerElement::getDatePicker($pfName,$value);  //date picker

	echo "</td>";

	if($postSCHEDULER == 'Y'){
	  getJSFldDateRange($pfName,$label,$index,$pRequired);  //we build the date param using a special js function.
    } else {
	  getJSFld($pfName,$label,$index,$pRequired);
    }
}

// general Select DD
function f_GDD($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER;
	include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');

	$defaults=getDefaults($index);

	// will get overridden later
	$label="General Select DD";
	$value="";

	// first element is always the main lable, rest are the radio button lables
	$lblArr=explode("?",$pLabel);
	$valArr=explode("?",$pValue);
	$lblStr="";
	$valStr="";

	// the labels
	for($i=0; $i<sizeof($lblArr); $i++) {
		if ($i==0) $label=$lblArr[$i];
		else { if ($lblStr=="") $lblStr=$lblArr[$i]; else $lblStr.=",".$lblArr[$i]; }
	}
	// the values
	for($i=0; $i<sizeof($valArr); $i++) {
		if ($i==0) {
			$value=$valArr[$i];
			if ($defaults[0]!="") $value=$defaults[0]; // NB: normally other way around
		}
		else { if ($valStr=="") $valStr=$valArr[$i]; else $valStr.=",".$valArr[$i]; }
	}

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>";
	echo "<SELECT name='".$pfName."' >";
	// skip the first one as that should be the field label
	for($i=1; $i<sizeof($lblArr); $i++) {
		if ($valArr[$i]==$value) echo "<OPTION value='".$valArr[$i]."' SELECTED >".$lblArr[$i]."</OPTION>";
		else echo "<OPTION value='".$valArr[$i]."' >".$lblArr[$i]."</OPTION>";
	}
	echo "</SELECT>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

// Period
function f_PERIOD($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Period";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getPeriodDD($pfName,$pValue,"N","N",null,null,null,$dbConn, $principalId); echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

// Period
function f_PERIOD2($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Period";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getPeriodDD2($pfName,$pValue,"N","N",null,null,null,$dbConn, $principalId); echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

// Year
function f_YEAR($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Fin Year";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getFinYearDD($pfName,$pValue,"N","N",null,null,null,$dbConn, $principalId); echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

// Current Year / Previous Year
function f_CPYEAR($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Current Year";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getCPYearDD($pfName,$pValue,"N","N",null,null,null,$dbConn, $principalId); echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&


// delivery day DD
function f_DELDAY($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Delivery Day";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getDaysDD($pfName,$pValue,"N","N",null,null,null,$dbConn); echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// Deal Type DD
function f_DEALTYPE($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Deal Type";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getDealTypesDD($pfName,$pValue,"N","N",null,null,null,$dbConn); echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// Allowed Document Types DD
function f_DOCTYPE($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Document Type";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getReportDocumentTypesAllowedDD($pfName,$pValue,"N","N",null,null,null,$dbConn,$userId,$principalId); echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// Allowed Document Types DD
function f_DOCTYPEMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;

	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
    include_once($ROOT.$PHPFOLDER.'DAO/CommonDAO.php');

	$defaults = getDefaults($index);

  if ($pValue == "") {
	  $pValue = $defaults;
	} else {
	  $pValue = explode(',',$pValue);
	}

	if ($pLabel!="") $label=$pLabel; else $label="Document Type";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	}

	$commonDAO = new CommonDAO($dbConn);
	$mfDT = $commonDAO->getReportDocumentTypesAllowedArray($userId,$principalId);

	echo '<td>';
	echo '<div style="overflow:auto; width:400px; height:100px; font-size:12px;">';

        //echo "<input type='button' class='submit' value='Select All Document Types' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo '<table class="tableReset">';
	foreach ($mfDT as $row) {
		$value = $row['uid'];
				if ((in_array($value, $pValue)) || (in_array("*", $pValue))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo '<tr>';
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' " . $CHECKED . " /></td><td>" . $row['description'] . "</td>";
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
	echo '</td>';


	getJSFld($pfName,$label,$index,$pRequired);
}


// HISTORICAL Allowed Document Types DD
function f_HDOCTYPE($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Document Type";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getHistoricalDocumentTypesAllowedDD($pfName,$pValue,"N","N",null,null,null,$dbConn,$userId,$principalId); echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
//Multiple Captured Date (Remittance)

function f_CAPTUREDATE($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/RemittanceDAO.php');
	$remittanceDAO = new RemittanceDAO($dbConn);
	$mfP = $remittanceDAO->getCaptureDateArray($principalId);

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Capture Dates";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        multiToolbar($pfName);

        if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfP as $row) {
		$value=$row['uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['capture_date'].DELIMITER_OTHER_1.$row['vendor_reference'].DELIMITER_OTHER_1.$row['total_amount']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}


// Multiple Products List
function f_PPRODMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
	$productDAO = new ProductDAO($dbConn);
	$mfP = $productDAO->getUserPrincipalProductsArray($principalId, $userId);

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Product(s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        multiToolbar($pfName);

        if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfP as $row) {
		$value=$row['uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['product_code'].DELIMITER_OTHER_1.$row['alt_code'].DELIMITER_OTHER_1.$row['product_description']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}


//Products List distinct FROM ORDERS work around - fixes deleted products.
function f_PPRODTTMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
	$productDAO = new ProductDAO($dbConn);
	$showOnlyProductsInTT = true;
	$allProducts = true;
	$mfP = $productDAO->getUserPrincipalProductsArray($principalId, $userId, $allProducts, $showOnlyProductsInTT);

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Product(s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Products' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";

        multiToolbar($pfName);

        if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfP as $row) {
		$value=$row['uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['product_code'].DELIMITER_OTHER_1.$row['alt_code'].DELIMITER_OTHER_1.$row['product_description']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// Single Product DD
function f_PPROD($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Product";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getPrincipalProductsDD($pfName,$pValue,"N","N",null,null,null,"",$dbConn,$principalId,$userId); echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// Single Chain DD
function f_CHAIN($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Chain";

	echo "<tr>";
	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getUserPrincipalChainsDD($pfName,$pValue,"N","N",null,null,null,$dbConn,$userId,$principalId,"1"); echo "</td>";
	echo "</tr>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// Multiple Chains List
function f_CHAINMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');

	$defaults=getDefaults($index);

	$storeDAO = new StoreDAO($dbConn);
	$mfC = $storeDAO->getAllPrincipalChainsForUser($userId, $principalId,CHAIN_FILTER_ALL);

	if ($pLabel!="") $label=$pLabel; else $label="Chain(s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Chains' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);


	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfC as $row) {
		$value=$row['principal_chain_uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['chain_name']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

function f_CHAINPMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');

	$defaults=getDefaults($index);

	$storeDAO = new StoreDAO($dbConn);
	$mfC = $storeDAO->getAllPrincipalChainsForUser($userId, $principalId,CHAIN_FILTER_PRICE);

	if ($pLabel!="") $label=$pLabel; else $label="Chain(s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Chains' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);


	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfC as $row) {
		$value=$row['principal_chain_uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['chain_name']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}



function f_ALTCHAINMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');

	$defaults=getDefaults($index);

	$storeDAO = new StoreDAO($dbConn);
	$mfC = $storeDAO->getAllAlternatePrincipalChainsForUser($userId, $principalId);

	if ($pLabel!="") $label=$pLabel; else $label="Chain(s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Chains' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);


	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfC as $row) {
		$value=$row['principal_chain_uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['chain_name']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}


// Multiple Stores List
function f_STOREMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
	$storeDAO = new StoreDAO($dbConn);
	$mfC = $storeDAO->getUserPrincipalStoreArray($userId, $principalId, "");

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Store(s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Stores' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfC as $row) {
		$value=$row['psm_uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['store_name']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// Multiple Stores List, Payment store separated into group mayments and individual payment

function f_STOREPMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/PaymentsDAO.php');
		$PaymentsDAO=new PaymentsDAO($dbConn);
		$mfPSI = $PaymentsDAO->getPaymentCustomers($principalId);
		
//		$PaymentsDAO=new PaymentsDAO($dbConn);
//		$mfPSG = $PaymentsDAO->getPaymentGroups($principalId);		
		
		$mfC  = array_merge($mfPSI);
		
//		print_r($mfC);

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Store(s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:200px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Stores' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfC as $row) {
		$value=$row['payment_by'] . $row['uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['Customer']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}



// Multiple Stores List, but only those stores that are found in tracking transaction to get around the vendor owned store issue ie.cannot return every store in CB list to user.
function f_STORETTMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
	$storeDAO = new StoreDAO($dbConn);
	$mfC = $storeDAO->getUserPrincipalStoreArray($userId, $principalId, "", $showVendorStores=true, $showOnlyStoresInTT=true);

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Store(s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Stores' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);


	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfC as $row) {
		$value=$row['psm_uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['store_name']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}


// Single Chain DD
function f_STORE($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Store";

	echo "<tr>";
	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getPaymentPrincipalStoresDD($pfName,$pValue,"N","N",null,null,null,"",$dbConn,$userId,$principalId); echo "</td>";
	echo "</tr>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// *******************************************************************************************************************************************************************************
function f_DEBTGRPCHAIN($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Store";

	echo "<tr>";
	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getPaymentPGroupsDD($pfName,$pValue,"N","N",null,null,null,"",$dbConn,$userId,$principalId); echo "</td>";
	echo "</tr>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// *******************************************************************************************************************************************************************************
function f_DEBTGRPSTORE($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Store";

	echo "<tr>";
	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getPaymentPrincipalStoresDD($pfName,$pValue,"N","N",null,null,null,"",$dbConn,$userId,$principalId); echo "</td>";
	echo "</tr>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// *******************************************************************************************************************************************************************************
function f_DEBTGRPSTOREMULTI($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	
	include_once($ROOT.$PHPFOLDER.'DAO/PaymentsDAO.php');
   $PaymentsDAO=new PaymentsDAO($dbConn);
   $mfPSI = $PaymentsDAO->getPaymentCustomers($principalId);
		
   $PaymentsDAO=new PaymentsDAO($dbConn);
   $mfPSG = $PaymentsDAO->getPaymentGroups($principalId);		
		
  $mfC  = array_merge($mfPSI,$mfPSG);
  
  $defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Customer";

	echo "<tr>";
	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:200px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Stores' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);


	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfC as $row) {
		$value=$row['payment_by'] . $row['uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>". $row['Customer'] . "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// *******************************************************************************************************************************************************************************

function f_SALESREPMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
	$storeDAO = new StoreDAO($dbConn);
	$mfC = $storeDAO->getPrincipalSalesRepAll($principalId);


	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Areas";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	}

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Stores' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);


	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfC as $row) {
		$value=$row['uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>" . $row['first_name'] . ' ' . $row['surname']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// *******************************************************************************************************************************************************************************
function f_SALESAREAMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
	$storeDAO = new StoreDAO($dbConn);
	$mfC = $storeDAO->getPrincipalArea($principalId);

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Area (s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	}

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Stores' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);


	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfC as $row) {
		$value=$row['uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['description'] . "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// *******************************************************************************************************************************************************************************

// Single SALES REPS DD
function f_SALESREP($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Sales Rep";

	echo "<tr>";
	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getPrincipalSalesRepDD($pfName,$pValue,"N","N",null,null,null,$dbConn,$userId,$principalId); echo "</td>";
	echo "</tr>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// ***********************************************************************************
function f_AREASMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
	$storeDAO = new StoreDAO($dbConn);
	$mfC = $storeDAO->getPrincipalAreas($principalId);

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Area";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	}

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Areas' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);


	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfC as $row) {
		$value=$row['uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['uid'] . ' - ' . $row['description'] . "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// **********************************************************************************************
function f_PAYMENTTYPEPMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/PaymentsDAO.php');
	$PaymentsDAO = new PaymentsDAO($dbConn);
	$mfC = $PaymentsDAO->getPaymentTypes($principalId);

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Sales Rep(s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	}

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Stores' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);


	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($mfC as $row) {
		$value=$row['uid'];
		if ((in_array($value,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row['pay_type']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}



// Document Status DD
function f_DOCSTAT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Document Status";

	echo "<tr>";
	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getDocumentStatusLimitedDD($pfName,$pValue,"N","N",null,null,null,$dbConn); echo "</td>";
	echo "</tr>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// *****************************************************************************************************************************************************
function f_USERMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue == "") {
	  $pValue = $defaults;
	} else {
	  $pValue = explode(',',$pValue);
	}

	if ($pLabel != "") $label = $pLabel; else $label="Document Status";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	}

	include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
	$AdministrationDAO = new AdministrationDAO($dbConn);
	$capUserArr = $AdministrationDAO->getCaptureUsers($principalId);

	echo '<td>';
	echo '<div style="overflow:auto; width:400px; height:100px; font-size:12px;">';

        //echo "<input type='button' class='submit' value='Select All Document Status' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo '<table class="tableReset">';
	foreach ($capUserArr as $row) {
		$value = $row['uid'];
		if ((in_array($value, $pValue)) || (in_array("*", $pValue))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo '<tr>';
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' " . $CHECKED . " /></td><td>" . $row['full_name'] . "</td>";
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
	echo '</td>';

	getJSFld($pfName,$label,$index,$pRequired);
}

// *****************************************************************************************************************************************************
function f_PRINMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue == "") {
	  $pValue = $defaults;
	} else {
	  $pValue = explode(',',$pValue);
	}

	if ($pLabel != "") $label = $pLabel; else $label="Principal";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	}

	include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
	$AdministrationDAO = new AdministrationDAO($dbConn);
	$capUserArr = $AdministrationDAO->getUserPrincipals($userId);

	echo '<td>';
	echo '<div style="overflow:auto; width:400px; height:100px; font-size:12px;">';

        //echo "<input type='button' class='submit' value='Select All Document Status' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo '<table class="tableReset">';
	foreach ($capUserArr as $row) {
		$value = $row['principal_id'];
		if ((in_array($value, $pValue)) || (in_array("*", $pValue))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo '<tr>';
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' " . $CHECKED . " /></td><td>" . $row['Principal'] . "</td>";
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
	echo '</td>';

	getJSFld($pfName,$label,$index,$pRequired);
}

// *****************************************************************************************************************************************************


function f_DOCSTATMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue == "") {
	  $pValue = $defaults;
	} else {
	  $pValue = explode(',',$pValue);
	}

	if ($pLabel != "") $label = $pLabel; else $label="Document Status";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	}

	include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
	$transactionDAO = new TransactionDAO($dbConn);
	$docStatusArr = $transactionDAO->getDocumentStatusArray(true);


	echo '<td>';
	echo '<div style="overflow:auto; width:400px; height:100px; font-size:12px;">';

        //echo "<input type='button' class='submit' value='Select All Document Status' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo '<table class="tableReset">';
	foreach ($docStatusArr as $row) {
		$value = $row['uid'];
		if ((in_array($value, $pValue)) || (in_array("*", $pValue))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo '<tr>';
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' " . $CHECKED . " /></td><td>" . $row['description'] . "</td>";
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
	echo '</td>';


	getJSFld($pfName,$label,$index,$pRequired);
}


function f_RTDOCSTATMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue == "") {
	  $pValue = $defaults;
	} else {
	  $pValue = explode(',',$pValue);
	}

	if ($pLabel != "") $label = $pLabel; else $label="Document Status";

	if ($pRequired)	{
          echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
          echo "<td>".$label.":</td>";
	}

	include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
	$transactionDAO = new TransactionDAO($dbConn);
	$docStatusArr = $transactionDAO->getRTDocumentStatusArray("Y");


	echo '<td>';
	echo '<div style="overflow:auto; width:400px; height:100px; font-size:12px;">';

        //echo "<input type='button' class='submit' value='Select All Document Status' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
	multiToolbar($pfName);

        if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\" />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo '<table class="tableReset">';
	foreach ($docStatusArr as $row) {
		$value = $row['uid'];
		if ((in_array($value, $pValue)) || (in_array("*", $pValue))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo '<tr>';
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' " . $CHECKED . " /></td><td>" . $row['description'] . "</td>";
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
	echo '</td>';


	getJSFld($pfName,$label,$index,$pRequired);
}

function f_PPRODGRPMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

	global $ROOT,$PHPFOLDER, $dbConn, $userId, $principalId, $postSCHEDULER, $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');

	$defaults=getDefaults($index);

	$productDAO = new ProductDAO($dbConn);
	$pC = $productDAO->getPrincipalProductCategoryArray($principalId,FLAG_STATUS_ACTIVE);

	$label = ($pLabel!="")?($pLabel):("Product Category(s)");

    echo "<td>".$label;
    if ($pRequired) GUICommonUtils::requiredField();
    echo ":</td><td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Categories' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\"  />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";

    foreach($pC as $row) {
        $value = $row ['uid'];
        $CHECKED = ((in_array($value, $defaults))||(in_array("*", $defaults))) ? " CHECKED " : "";
        echo "<tr><td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row ['description']."</td></tr>";
    }

	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}

function PPRODGRPMINORMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index, $level) {

	global $ROOT,$PHPFOLDER, $dbConn, $userId, $principalId, $postSCHEDULER, $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');

	$defaults=getDefaults($index);
        $proMinorLevel = $level;
	$productDAO = new ProductDAO($dbConn);
	#$pC = $productDAO->getPrincipalProductCategoryArray($principalId,FLAG_STATUS_ACTIVE);
        $systemId = $_SESSION['system_id'];
        $pCMinorLabels = $productDAO->getProductMinorCategoryLables($principalId, $systemId);
        if(!isset($pCMinorLabels[$proMinorLevel-1]))
          return;
        $labelArr = $pCMinorLabels[$proMinorLevel-1];
        $pC = array();
        $pCat = $productDAO->getAllProductMinorCategory($principalId, $systemId);
        if(isset($pCat[$labelArr['uid']])){
            $pC = $pCat[$labelArr['uid']];
        }

	$label = ($pLabel!="")?($pLabel):('Product '.$labelArr['lable']);

        echo "<td>".$label;
        if ($pRequired) GUICommonUtils::requiredField();
        echo ":</td><td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Categories' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\"  />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
        foreach($pC as $row) {
            $value = $row['uid'];
            $CHECKED = ((in_array($value, $defaults))||(in_array("*", $defaults))) ? " CHECKED " : "";
            echo "<tr><td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>".$row ['value']."</td></tr>";
        }
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
function f_PPRODGRPMINOR1MULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
  PPRODGRPMINORMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index, 1);
}
function f_PPRODGRPMINOR2MULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
  PPRODGRPMINORMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index, 2);
}
function f_PPRODGRPMINOR3MULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
  PPRODGRPMINORMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index, 3);
}
function f_PPRODGRPMINOR4MULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
  PPRODGRPMINORMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index, 4);
}


// Multiple Data Source List
function f_DATASOURCEMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Data Source(s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	$dSArr = array(DS_CAPTURE,DS_EDI,DS_WS);

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Data Sources' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\"  />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
	foreach ($dSArr as $v) {
		if ((in_array($v,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='checkbox' value='".$v."' ".$CHECKED." /></td><td>".$v."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// ******************************************************************************************************
function f_BILLINGPRINCIPALMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Principal";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
	$transactionDAO = new TransactionDAO($dbConn);
	$docStatusArr = $transactionDAO->getBillingPrincipalArray();
	
	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";
        //echo "<input type='button' class='submit' value='Select All Data Sources' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\"  />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
//	print_r($docStatusArr);
	foreach ($docStatusArr as $row) {
// print_r($row);
		$value = $row['uid'];
		if ((in_array($value, $defaults)) || (in_array("*", $defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo '<tr>';
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' " . $CHECKED . " /></td><td>". $row['name'] . "</td>";
		echo '</tr>';
	}


	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// ******************************************************************************************************
// Audit Log Types (hardcoded at times)
function f_ALOGTYPEMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
  global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;

  $defaults=getDefaults($index);

  if ($pLabel!="") $label=$pLabel; else $label="Event Type(s)";

  if ($pRequired) {
    echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
  } else {
    echo "<td>".$label.":</td>";
    }

  $principalMrSweet = "104";
  if ($principalId==$principalMrSweet) $dSArr = array("MRSINVCNF","MRSCRCNF","MRSCANCNF");
  else $dSArr = array("");

  echo "<td>";
  echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Data Sources' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

  if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
    if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
    echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\"  />Always use Select All ";
    getJSFldAlternateFld("sa".$pfName,$index,"sa");
  }
  echo "<table class='tableReset'>";
  foreach ($dSArr as $v) {
    if ((in_array($v,$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
    echo "<tr>";
    echo "<td><input name='".$pfName."' type='checkbox' value='".$v."' ".$CHECKED." /></td><td>".$v."</td>";
    echo "</tr>";
  }
  echo "</table>";
  echo "</div>";
  echo "</td>";

  getJSFld($pfName,$label,$index,$pRequired);
}

// EDI File Definitions
function f_EDIFILEDEF($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/ImportDAO.php');

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="EDI File Definition(s)";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	$importDAO = new ImportDAO($dbConn);
	$rsArr = $importDAO->getPrincipalEDIFileDefinitions($principalId);

	echo "<td>";
	echo "<div style='overflow:auto; width:400px; font-size:12px;'>";
	echo "<table class='tableReset'>";
	foreach ($rsArr as $rs) {
		if ((in_array($rs["uid"],$defaults)) || (in_array("*",$defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<tr>";
		echo "<td><input name='".$pfName."' type='radio' value='".$rs["uid"]."' ".$CHECKED." /></td><td>".$rs["file_path"].$rs["file_wildcard"]."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
function getJSFld($fieldName, $label, $index, $required) {
	global $paramsJSFld; global $paramsJSPop; global $postGENERALUSAGE; global $paramsJSFeedback;

	if ($required) {
		$paramsJSFld.="<INPUT type=\"hidden\" id=\"p".$index."\" name=\"p".$index."\" />";
		$paramsJSPop.="fld=document.getElementsByName('".$fieldName."'); val=convertElementToArray(fld); if (val.toString().trim()=='') { alert('Parameter ".$label." is a required value'); return false; } document.getElementById(\"p".$index."\").value=val;";
	} else {
		$paramsJSFld.="<INPUT type=\"hidden\" id=\"p".$index."\" name=\"p".$index."\" />";
		$paramsJSPop.="fld=document.getElementsByName('".$fieldName."'); val=convertElementToArray(fld); document.getElementById(\"p".$index."\").value=val;";
	}

	if ($postGENERALUSAGE=="Y") {
		if ($paramsJSFeedback=="") $paramsJSFeedback="'p{$index}='+getVal({$index}, '{$fieldName}')";
		else $paramsJSFeedback.="+'&p{$index}='+getVal({$index}, '{$fieldName}')";
	}
}

//Used by Date to set the date range stored value, made of various params determined by selected option.
function getJSFldDateRange($fieldName, $label, $index, $required){
	global $paramsJSFld; global $paramsJSPop; global $postGENERALUSAGE; global $paramsJSFeedback;

	if ($required) {
		$paramsJSFld.="<INPUT type=\"hidden\" id=\"p".$index."\" name=\"p".$index."\" />";
		$paramsJSPop.="val = getDateRange('".$fieldName."'); if (val.toString().trim()=='') { alert('Parameter ".$label." is a required value'); return false; } document.getElementById(\"p".$index."\").value=val;";
	} else {
		$paramsJSFld.="<INPUT type=\"hidden\" id=\"p".$index."\" name=\"p".$index."\" />";
		$paramsJSPop.="val = getDateRange('".$fieldName."'); document.getElementById(\"p".$index."\").value=val;";
	}

}

function getJSFldAlternateFld($fieldName, $index, $iName) {
	global $paramsJSFld; global $paramsJSPop;

	$paramsJSFld.="<INPUT type=\"hidden\" id=\"".$iName.$index."\" name=\"".$iName.$index."\" />";
	$paramsJSPop.="fld=document.getElementsByName('".$fieldName."'); val=convertElementToArray(fld); document.getElementById(\"".$iName.$index."\").value=val; ";
}
//***************************************************************************************************************************************************************/
function f_WAREHOUSEAREASMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Principal";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	include_once($ROOT.$PHPFOLDER.'DAO/TaskManDAO.php');
	$TaskManDAO = new TaskManDAO($dbConn);
	$prinTaskArr = $TaskManDAO->getWarehouseAreas($userId, $principalId);
	
	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";
        //echo "<input type='button' class='submit' value='Select All Data Sources' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\"  />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
//	print_r($docStatusArr);
	foreach ($prinTaskArr as $row) {
// print_r($row);
		$value = $row['waUid'];
		if ((in_array($value, $defaults)) || (in_array("*", $defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo '<tr>';
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' " . $CHECKED . " /></td><td>". $row['wh_area'] . "</td>";
		echo '</tr>';
	}


	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// *******************************************************************************************************************************************************************************

// Single Kwelanga Debtors Accounts
function f_KDEBTACC($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Kwelanga Debtors Account";

	echo "<tr>";
	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getKwelangaDebtorsAccount($pfName,$pValue,"N","N",null,null,null,$dbConn,$principalId); echo "</td>";
	echo "</tr>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// ***********************************************************************************
function f_TRANSOWNERMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

	global $ROOT,$PHPFOLDER, $dbConn, $userId, $principalId, $postSCHEDULER, $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/reportsDAO.php');

	$defaults=getDefaults($index);

	$reportsDAO = new reportsDAO($dbConn);
	$pC = $reportsDAO->getTransporterOwnerList(392,FLAG_STATUS_ACTIVE);

	$label = ($pLabel!="")?($pLabel):("Transport Owner");

    echo "<td>".$label;
    if ($pRequired) GUICommonUtils::requiredField();
    echo ":</td><td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Categories' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\"  />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";

    foreach($pC as $row) {
        $value = $row['uid'] . '-' .  $row['owner'];
        $CHECKED = ((in_array($value, $defaults))||(in_array("*", $defaults))) ? " CHECKED " : "";
        echo "<tr><td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>" . $row ['short_name'] . ' - ' .$row ['owner']."</td></tr>";
    }

	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}





// ***********************************************************************************
function f_EMPLOYEEMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

	global $ROOT,$PHPFOLDER, $dbConn, $userId, $principalId, $depotId, $postSCHEDULER, $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/EmployeeDAO.php');

	$defaults=getDefaults($index);

	$reportsDAO = new EmployeeDAO($dbConn);
	$pC = $reportsDAO->getEmployeeList($depotId);

	$label = ($pLabel!="")?($pLabel):("Employee");

    echo "<td>".$label;
    if ($pRequired) GUICommonUtils::requiredField();
    echo ":</td><td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Categories' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\"  />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";

    foreach($pC as $row) {
        $value = $row['uid'];
        $CHECKED = ((in_array($value, $defaults))||(in_array("*", $defaults))) ? " CHECKED " : "";
        echo "<tr><td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>" . $row ['uid'] . ' - ' .$row ['name']."</td></tr>";
    }

	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}


















// ***********************************************************************************
function f_USERTRANSPORTERMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

	global $ROOT,$PHPFOLDER, $dbConn, $userId, $principalId, $postSCHEDULER, $postGENERALUSAGE;
	include_once($ROOT.$PHPFOLDER.'DAO/reportsDAO.php');

	$defaults=getDefaults($index);

	$reportsDAO = new reportsDAO($dbConn);
	$pC = $reportsDAO->getUserTransporterList(392, $userId, FLAG_STATUS_ACTIVE);

	$label = ($pLabel!="")?($pLabel):("Transporter List");

    echo "<td>".$label;
    if ($pRequired) GUICommonUtils::requiredField();
    echo ":</td><td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Categories' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\"  />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";

    foreach($pC as $row) {
        $value = $row['uid'];
        $CHECKED = ((in_array($value, $defaults))||(in_array("*", $defaults))) ? " CHECKED " : "";
        echo "<tr><td><input name='".$pfName."' type='checkbox' value='".$value."' ".$CHECKED." /></td><td>" . $row ['name'] ."</td></tr>";
    }

	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// ***********************************************************************************
function f_YESNO($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {

	global $ROOT,$PHPFOLDER, $dbConn, $userId, $principalId, $postSCHEDULER, $postGENERALUSAGE;

	$defaults=getDefaults($index);

	$reportsDAO = new reportsDAO($dbConn);
	$pC = array('No','Yes');

	$label = ($pLabel!="")?($pLabel):("YESNO");

    echo "<td>".$label;
    if ($pRequired) GUICommonUtils::requiredField();
    echo ":</td><td>";
	  echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";

        //echo "<input type='button' class='submit' value='Select All Categories' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\"  />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";

    foreach($pC as $row) {
        $value = $row;
        if ($row == 'No') {
            $CHECKED = " CHECKED ";
        } else {
            $CHECKED = "";
        }
//        $CHECKED = ((in_array($value, $defaults))||(in_array("*", $defaults))) ? " CHECKED " : "";
        echo "<tr><td><input name='".$pfName."' type='radio' value='".$value."' ".$CHECKED." /></td><td>". $row ."</td></tr>";
    }

	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}
// ***********************************************************************************
// Single Kwelanga Debtors Accounts
function f_KDEBTREP($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId;
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

	$defaults=getDefaults($index);
	if ($pValue=="") $pValue=$defaults[0];

	if ($pLabel!="") $label=$pLabel; else $label="Report Type";

	echo "<tr>";
	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	echo "<td style='text-align:top;'>"; BasicSelectElement::getKwelangaDebtorsReportType($pfName,$pValue,"N","N",null,null,null,$dbConn,$principalId); echo "</td>";
	echo "</tr>";

	getJSFld($pfName,$label,$index,$pRequired);
}

// ***********************************************************************************

//***************************************************************************************************************************************************************/
/*                                                                                                                                                              */
/*       Task System Report Parameters                                                                                                                                                           */
/*                                                                                                                                                              */
//***************************************************************************************************************************************************************/
function f_TASKPRINCIPALMULT($pfName, $pValue, $pLength, $pLabel, $pRequired, $index) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $userId; global $principalId; global $postSCHEDULER; global $postGENERALUSAGE;

	$defaults=getDefaults($index);

	if ($pLabel!="") $label=$pLabel; else $label="Principal";

	if ($pRequired)	{
		echo "<td>".$label; echo GUICommonUtils::requiredField(); echo ":</td>";
	} else {
		echo "<td>".$label.":</td>";
	  }

	include_once($ROOT.$PHPFOLDER.'DAO/TaskManDAO.php');
	$TaskManDAO = new TaskManDAO($dbConn);
	$prinTaskArr = $TaskManDAO->getDebtorAdminPricipalList();
	
	echo "<td>";
	echo "<div style='overflow:auto; width:400px; height:100px; font-size:12px;'>";
        //echo "<input type='button' class='submit' value='Select All Data Sources' onclick='fld=document.getElementsByName(\"".$pfName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />";
        multiToolbar($pfName);

	if (($postSCHEDULER=="Y") || ($postGENERALUSAGE=="Y")) {
		if (in_array("*",$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<input name='sa".$pfName."' type='checkbox' value='*' ".$CHECKED." onClick=\"((this.checked==true)?(selectAll('{$pfName}', true)):(selectAll('{$pfName}', false)))\"  />Always use Select All ";
		getJSFldAlternateFld("sa".$pfName,$index,"sa");
	}
	echo "<table class='tableReset'>";
//	print_r($docStatusArr);
	foreach ($prinTaskArr as $row) {
// print_r($row);
		$value = $row['principal_uid'];
		if ((in_array($value, $defaults)) || (in_array("*", $defaults))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo '<tr>';
		echo "<td><input name='".$pfName."' type='checkbox' value='".$value."' " . $CHECKED . " /></td><td>". $row['principal'] . "</td>";
		echo '</tr>';
	}


	echo "</table>";
	echo "</div>";
	echo "</td>";

	getJSFld($pfName,$label,$index,$pRequired);
}


//***************************************************************************************************************************************************************/
/*                                                                                                                                                              */
/*       End of Task System Report Parameters                                                                                                                                                           */
/*                                                                                                                                                              */
//***************************************************************************************************************************************************************/




/*************************************************************************************************/
/*
 *          OUTPUT BEGINS....
 */
/*************************************************************************************************/


echo "<HTML>";
echo "<HEAD>";
echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.js'></script>";
echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js'></script>";
DatePickerElement::getDatePickerLibs();
echo "<LINK href='".$ROOT.$PHPFOLDER."css/1_default.css' rel='stylesheet' type='text/css'>" ;
echo "</HEAD>";
echo "<BODY><CENTER>";

echo '<TABLE border="0" style="border:0px; font-size:100%;padding:0px;margin:0px;line-height:normal;" cellpadding="0" cellspacing="0">';

echo '<TR>';
if (($postSCHEDULER!="Y") && ($postGENERALUSAGE!="Y")) {
  echo '<TD valign="top" width="210" style="margin:0px;padding:0px;"></TD>';
}
echo '<TD valign="top" align="left" style="margin:0px;padding:0px;">';
echo '<h2 style="color:#047;margin-top:15px;margin-bottom:0px;font-family: Calibri, Verdana, Ariel, sans-serif;">'.$attrObjName.'</h2>';
//echo "<BR><div style='font-weight:bold;font-size:16px;line-height:28px;'>".$attrObjName."</div>";
echo "<SPAN style='font-weight:normal;font-size:0.8em; '>".$attrObjDescription." (".$attrLevel." Level)</SPAN><BR><BR><BR>";
echo "<SPAN style='font-weight:normal;font-size:0.8em; color:grey;line-height:30px;'>Please choose your parameters </SPAN><BR>";

echo '</TD>';

if (($postSCHEDULER!="Y") && ($postGENERALUSAGE!="Y")) {
  echo '<TD valign="bottom" width="210" style="margin:0px;padding:0px 0px 0px 15px">';
  echo "<SPAN style='font-weight:normal;font-size:0.8em; color:grey;line-height:30px;'>Reporting information </SPAN><BR>";
  echo '</TD>';
}

echo '</TR>';
echo '<TR>';

if (($postSCHEDULER!="Y") && ($postGENERALUSAGE!="Y")) {
  echo '<TD valign="top" width="210" style="margin:0px;padding:0px;"></TD>';
}

echo '<TD valign="top" align="center" style="margin:0px;padding:0px;">';

// main loop
echo "<TABLE>";



$i=0;
foreach ($pPFArr as $val) {
  echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
		//if (($i%2)==0) echo "<TR class='odd'>"; else echo "<TR class='even'>";
		$fieldInt=""; $fieldFunc=""; $required=false; $length=""; $value=""; $label="";

		// get field
		preg_match("/^[0-9]+/",$val,$temp);
    if (sizeof($temp)==0) {
      break;
    }
		$fieldInt=$temp[0];

		// get field function name
		$pos=strpos($val,"=");
		if (!($pos===false) && ($pos>0)) $fieldFunc="f_".substr($val,$pos+1); else $fieldFunc="";

		// get required
		if (is_numeric($fieldInt))
		foreach ($pRFArr as $val2) {
			preg_match("/^[0-9]+/",$val2,$temp);
			if ((isset($temp[0])) && ($temp[0]==$fieldInt)) {
				$required=true;
				break;
			};
		}

		// get fieldlabel
		if (is_numeric($fieldInt))
		foreach ($pOFLArr as $val2) {
			preg_match("/^[0-9]+/",$val2,$temp);
			if ((isset($temp[0])) && ($temp[0]==$fieldInt)) {
				$pos=strpos($val2,"=");
				if (!($pos===false) && ($pos>0)) $label=substr($val2,$pos+1); else $label="";
				break;
			};
		}

		// get initial value
		if (is_numeric($fieldInt))
		foreach ($pIVArr as $val2) {
			preg_match("/^[0-9]+/",$val2,$temp);
			if ((isset($temp[0])) && ($temp[0]==$fieldInt)) {
				$pos=strpos($val2,"=");
				if (!($pos===false) && ($pos>0)) $value=substr($val2,$pos+1); else $value="";
				break;
			};
		}

		// get length
		if (is_numeric($fieldInt))
		foreach ($pFLArr as $val2) {
			preg_match("/^[0-9]+/",$val2,$temp);
			if ((isset($temp[0])) && ($temp[0]==$fieldInt)) {
				$pos=strpos($val2,"=");
				if (!($pos===false) && ($pos>0)) $length=substr($val2,$pos+1); else $length="";
				break;
			};
		}

		if (function_exists($fieldFunc)) {
			// every function must be declared the same because of the single call below, even though some params are not used
			$fieldFunc('f_'.$fieldInt, $value, $length, $label, $required, $fieldInt);
		} else echo "function ".$fieldFunc." does not exist.<br>";

		echo "</TR>";
		$i++;
}


//OUTPUT TYPE
if (($postSCHEDULER!="Y") && ($postGENERALUSAGE!="Y")) {
  echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
    echo '<TD>Output Type: </TD>';
    echo '<TD>';
      BasicSelectElement::getReportOutputTypesDD('f_OUTPUTTYPE', SCD_OT_CSV, "N", "N", null, null, null);
    echo '</TD>';
  echo '</TR>';

  //ADD TO POST VALS
  getJSFld('f_OUTPUTTYPE','Output Type','OUTPUT', true);
}


echo "</TABLE>";
echo "<BR>";



if (($postSCHEDULER!="Y") && ($postGENERALUSAGE!="Y")) {
	echo "<INPUT type='button' class='submit' onclick='submitParams();' value='Run Report' />";
}

echo '</TD>';
if (($postSCHEDULER!="Y") && ($postGENERALUSAGE!="Y")) {
echo '<TD valign="top" width="210" style="margin:0px;padding:0px 0px 0px 15px;">';


echo '<a href="javascript:;" onClick="$(\'#reportschedule\').slideToggle();" id="moreInfo" style="width:100%;"><span>Scheduling</span></a>';

echo '<div id="reportschedule" style="display:none;">';
$ScheduleInnerHTML = "<span style='font-size:1.2em;letter-spacing:-0.03em'><strong>SCHEDULE THIS REPORT!</strong></span>
	  Receive this report via email or other methods daily or monthly at specific times automatically.
	  <div align='center'><a href='../general/generalAjaxBase.php?m_id=109' style='display:block;padding:6px 10px;margin:0px;margin-top:8px;color:#fff;font-weight:bold;text-align:center;background:#1e570a'>Click Here to Schedule</a></div>";
GUICommonUtils::outputBlkGreen($ScheduleInnerHTML);
echo '</div>';


//echo '<BR>';

echo '<a href="javascript:;" onClick="$(\'#reportnotice\').slideToggle();" id="moreInfo" style="width:100%;" ><span>Notice</span></a>';

$NoteHTML = "<span style='font-size:1.2em'><strong>NOTE</strong></span><br />
	  Be careful when running reports for totalling purposes (eg.sales), as ONLY the stores/products which you have specifically been given permissions to see will be included on the report!<br />
	  Your total figures may therefore have excluded stores/products outside of your permissions.<br>
	  You can use the <img src='".$DHTMLROOT.$PHPFOLDER."images/permissions-icon.png' width=15> button on the menu bar, and select <i>summary</i> to further understand what permissions you have.
	  </SPAN>";

echo '<div id="reportnotice" style="display:none;">';
GUICommonUtils::outputBlkRed($NoteHTML);
echo '</div>';

echo '</TD>';
echo '</TR>';
}
echo '</TABLE>';  //close main table.
echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable

$dbConn->dbClose();


//<!-- must be done this way because GET url limited to 2083 chars and if u select all stores = problem -->

?>

<form name="submit_params"
	action="<?php echo $ROOT.$PHPFOLDER; ?>functional/reports/downloadBase.php"
	method="post" autocomplete="off">
	<input name="REPORTID" type="hidden" value="<?php echo $postREPORTID; ?>">
	<input name="SCHEDULER" type="hidden" value="<?php echo $postSCHEDULER; ?>">
    <?php echo $paramsJSFld; ?>
</form>

<SCRIPT type="text/javascript" defer>
	adjustMyFrameHeight();

	function submitParams() {
		// populate and check fields
		if (setparamBaseValues()) {
			document.submit_params.submit(); // returns false if required value not met
		}
	}
	// called also from scheduler
	function setparamBaseValues() {
		<?php echo $paramsJSPop; ?> // automatically returns false if required value not met
		return true;
	}

	function getParamsURL(){
		if (setparamBaseValues()) return <?php echo $paramsJSFeedback; ?>;
		else return "";
	}

	function getVal(index, fieldName){
		if (convertElementToArray(document.getElementsByName('sa'+fieldName))=='*') return '*';
		else return convertElementToArray(document.getElementsByName(fieldName));
	}

	//date ranges
	function getDateRange(id){
	  var dateRange = $('#DR_RANGE_'+id).val();
	  var dateParm = $('#DR_PARM_'+id).val();
	  var date = $('#'+id).val();
	  var param = dateRange;	//present to range constant.
      if(dateRange=="<?php echo DR_DATE ?>"){
        param = date;
      } else if (dateRange=="<?php echo DR_YESTERDAY ?>"){
        param = "<?php echo DR_YESTERDAY ?>:" + dateParm;
      } else if (dateRange=="<?php echo DR_NO_MONTH_START ?>"){
        param = "<?php echo DR_NO_MONTH_START ?>:" + dateParm;
      } else if (dateRange=="<?php echo DR_NO_MONTH_END ?>"){
        param = "<?php echo DR_NO_MONTH_END ?>:" + dateParm;
      }
	  return param;
	}

  	function dateSelector(obj, id){
    	var value = obj.value;
    	//handling of display for input fields
  		$('#dId_'+id).hide();
  		$('#rparm_'+id).hide();
    	if(value=="<?php echo DR_DATE ?>"){
    	  $('#dId_'+id).show();
    	} else if (value=="<?php echo DR_YESTERDAY ?>"){
    	  $('#rparm_'+id).show();
    	} else if (value=="<?php echo DR_NO_MONTH_START ?>" || value=="<?php echo DR_NO_MONTH_END ?>"){
    	  $('#rparm_'+id).show();
    	}
	}
        function selectAll(elementName, flag){
          $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
        }
</SCRIPT>

<BR></CENTER>
</BODY>
</HTML>
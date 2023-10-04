<?php


//LIBS
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
include_once($ROOT.$PHPFOLDER.'DAO/SchedulerDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');


//SESSION
if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];


//DB CONNECT
$dbConn = new dbConnect();
$dbConn->dbConnection();


//POST VALUES
$postDMLTYPE = (isset($_POST['DMLTYPE'])) ? $_POST['DMLTYPE'] : 'INSERT';
$postSCHEDULEID = (isset($_POST["SCHEDULEID"])) ? ($_POST["SCHEDULEID"]) : (false);
$postOBJID = (isset($_POST["REPORTID"])) ? ($_POST["REPORTID"]) : ('');
$postJOBTYPE = SCD_JT_REPORT;
$postREGENERATE = 'N';
$postPARAMETERLIST = '';
$postRUNDAY = '';
$postRUNWEEK = '';
$postRUNTIME = '';
$postOUTPUTTYPE = SCD_OT_HTML;
$postDESTINATIONTYPE = SCD_DT_EMAIL;
$postSENDTOSELF = 'Y';
$postALTRECIPIENTLIST = '';
$postLASTRUNDATE = '';

$postFTPHOST = '';
$postFTPUSR = '';
$postFTPPWD = '';
$postFTPFOLDER = '';
$postFTPPORT = 21;
$postFTPMODE = 1;

//STOP IF NO

//UPDATE EXISTING
if ($postDMLTYPE == 'UPDATE' && ($_POST["SCHEDULEID"] != false || $_POST["SCHEDULEID"] != '')) {

	$schedulerDAO = new SchedulerDAO($dbConn);
	$schedulerArr = $schedulerDAO->getScheduleItemForUser($userId,$principalId, $postSCHEDULEID, $applySchedulerScope=true);

	//Handling of no returned data and than trying to set it afterwards.
	if(!count($schedulerArr)>0){

	  die('ERROR: NO Data found for schedule UID: '.$postSCHEDULEID);
	}

	$schedule = $schedulerArr;
	$postJOBTYPE = $schedule[0]["job_type"];
	$postSCHEDULEID = $schedule[0]["uid"];
    $postREGENERATE = $schedule[0]["regenerate"];
    $postOBJID = $schedule[0]["object_id"];
    $postPARAMETERLIST = $schedule[0]["parameter_list"];
    $postRUNDAY = $schedule[0]["run_day"];
    $postRUNWEEK = $schedule[0]["run_week"];
    $postRUNTIME = $schedule[0]["run_time"];
    $postOUTPUTTYPE = $schedule[0]["output_type"];
    $postDESTINATIONTYPE = $schedule[0]["destination_type"];
    $postSENDTOSELF = $schedule[0]["send_to_self"];
    $postALTRECIPIENTLIST = $schedule[0]["alt_recipient_list"];
    $postLASTRUNDATE = $schedule[0]["last_run_date"];

    if($postDESTINATIONTYPE == SCD_DT_FTP){

      $FTPArr = unserialize($schedule[0]['destination_address']);

      //INCLUDE INVALID SERIAL/ARRAY VALUE HANDLING
      $postFTPHOST = (isset($FTPArr['HOST'])) ? ($FTPArr['HOST']) : ($postFTPHOST);
      $postFTPUSR = (isset($FTPArr['USR'])) ? ($FTPArr['USR']) : ($postFTPUSR);
      $postFTPPWD = (isset($FTPArr['PWD'])) ? ($FTPArr['PWD']) : ($postFTPPWD);
      $postFTPFOLDER = (isset($FTPArr['FOLDER'])) ? ($FTPArr['FOLDER']) : ($postFTPFOLDER);
      $postFTPPORT = (isset($FTPArr['PORT'])) ? ($FTPArr['PORT']) : ($postFTPPORT);
      $postFTPMODE = (isset($FTPArr['MODE'])) ? ($FTPArr['MODE']) : ($postFTPMODE);

    }
}


//------------------------------------------------------------------------------------------------------------
//
//  START OF SCREEN
//
//------------------------------------------------------------------------------------------------------------


echo '<BR>';

if($postDMLTYPE == 'INSERT'){
  echo '<DIV style="' . FONT_UNOBTRUSIVE_INFO . '">Create a schedule to run a report and get the result sent to your email automatically.</DIV><BR>';
}

//??? disabled = true not cross browser.....
$DISABLED = ($postJOBTYPE == SCD_JT_REPORT) ? ("") : (" disabled='true' ");
echo "<div id='sDFOuter' ".$DISABLED." >";

echo "<input id='SCHEDULEID' type='hidden' value='".$postSCHEDULEID."' />"; // for checking posting failure, and submitting for update
echo "<TABLE align='center' style='line-height:20px;' width='750'>";


//Hide Force Re-run for INSERT
if($postDMLTYPE == "UPDATE"){
  echo "<TR class=\"".GUICommonUtils::styleEO($class)."\">";
    echo "<TD colspan='2' style='".FONT_UNOBTRUSIVE_INFO." '>Force this to rerun again at next time interval? <input type='checkbox' name='REGENERATE' value='Y' " , ($postREGENERATE=="Y") ? (" CHECKED ") : ('') , " > (Scheduler runs every 3 hours from 3am to 6pm)</TD>";
  echo "</TR>";
}

echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo "<TD WIDTH='240'>Job Type</TD>";
	echo "<TD>";
    	echo "<select id='JOBTYPE' name='JOBTYPE' style='' onChange='' DISABLED >";
    	echo "<option value='".SCD_JT_REPORT."' " , ($postJOBTYPE==SCD_JT_REPORT) ? (" SELECTED ") : ('') , " >Report</option>";
    	echo "<option value='".SCD_JT_SYSTEM_REPORT."' " , ($postJOBTYPE==SCD_JT_SYSTEM_REPORT) ? (" SELECTED ") : ('') , " >System Report</option>";
    	echo "</select>";
	echo "</TD>";
echo "</TR>";

echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo "<TD>Report to Run</TD>";
	echo "<TD>";
	$DISABLED = ($postDMLTYPE!="INSERT") ? ("Y") : ("N");
	if ($postJOBTYPE==SCD_JT_REPORT){
	  BasicSelectElement::getUserReportsDD("OBJID",$postOBJID,$DISABLED,$DISABLED,"\$('#PARAMETERLIST').val('');",null,null,$dbConn,$userId, $principalId);
	} else {
	  echo "<select id='F_OBJID' name='OBJID' style='' onChange='' DISABLED >";
	  echo "<option value='".$postOBJID."' SELECTED >".$schedule[0]["report_name"].(($schedule[0]["scheduler_scope"]=="P")?" (***PRINCIPAL SCOPE***)":"")."</option>";
	  echo "</select>";
	}
	echo "</TD>";
echo "</TR>";

echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo "<TD>Report Parameters</TD>";
	echo "<TD>";
	if ($postJOBTYPE==SCD_JT_REPORT) echo "<input class='submit' type='submit' value='Specify Params' onclick='refreshParams(document.getElementById(\"OBJID\").value);' style='float:left;margin:0px 10px 0px 2px;'/>";
	echo " <textarea style='float:left;margin:0px;font-size:10pt;' id='PARAMETERLIST' type='text' rows='1' cols='38' DISABLED >" . $postPARAMETERLIST . "</TEXTAREA>";
	echo "</TD>";
echo "</TR>";


//WEEKLY OR MONTHLY
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
  echo "<TD>Choose an Interval</TD>";
  echo "<TD>";
		$SELECTED_W = ((($postRUNDAY=="") && ($postRUNWEEK == "")) || (($postRUNWEEK != "")))	? (" CHECKED ") : ("");
        echo "<input type='radio' name='RUNINTERVAL' value='W' ".$SELECTED_W." onclick=\"showHideID('RUNWEEK_TD','RUNDAY_TD');\" > Weekly ";
        $SELECTED_M = ($postRUNDAY != "") ? (" CHECKED ") : ('');
	    echo "<input type='radio' name='RUNINTERVAL' value='M' ".$SELECTED_M." onclick=\"showHideID('RUNDAY_TD','RUNWEEK_TD');\" > Monthly";
  ECHO '</TD>';
echo "</TR>";

echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
  echo '<TD colspan="2" style="padding:0px;">';

  echo '<TABLE width="100%" >';
    echo '<TR>';
      echo '<TD rowSpan="2" >Days</TD>';
    echo '</TR>';
    echo '<TR>';
      echo "<TD id='RUNWEEK_TD' style='",($SELECTED_W=='')?("display:none;"):(""),"'>";
	    BasicInputElement::getGeneralHorizontalCB("RUNWEEK", GUICommonUtils::translateWeekdayFromString("1,2,3,4,5,6,0"),"1,2,3,4,5,6,0", $postRUNWEEK , "N","N",null,null,null,"80px");
	  echo "</TD>";
	  echo "<TD id='RUNDAY_TD' style='",($SELECTED_M=='')?("display:none;"):(""),"'>";
	    BasicInputElement::getGeneralHorizontalCB("RUNDAY","1,2,3,4,5,6,7,8,9,10","1,2,3,4,5,6,7,8,9,10",$postRUNDAY,"N","N",null,null,null,"40px");
		BasicInputElement::getGeneralHorizontalCB("RUNDAY","11,12,13,14,15,16,17,18,19,20","11,12,13,14,15,16,17,18,19,20",$postRUNDAY,"N","N",null,null,null,"40px");
	    BasicInputElement::getGeneralHorizontalCB("RUNDAY","21,22,23,24,25,26,27,28,29,30,31","21,22,23,24,25,26,27,28,29,30,31",$postRUNDAY,"N","N",null,null,null,"40px");
	  echo "</TD>";
	echo "</TR>";
  echo "</TABLE>";

echo "</TD></TR>";

echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
  echo "<TD nowrap>Run at these time(s) </TD>";
  echo "<TD>";
    BasicInputElement::getGeneralHorizontalCB("RUNTIME","3 AM,6 AM,9 AM,12 PM,3 PM,6 PM","3,6,9,12,15,18",$postRUNTIME,"N","N",null,null,null,"40px");
  echo "</TD>";
echo "</TR>";

//ONLY CSV SETUP - HARDCODED.
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo "<TD>Output Type</TD>";
	echo '<TD>';
	BasicSelectElement::getReportOutputTypesDD('OUTPUTTYPE', $postOUTPUTTYPE, "N", "N", null, null, null);
	echo "</TD>";
echo "</TR>";

echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	echo "<TD >Destination Type</TD>";
	echo "<TD>";
	echo "<select id='DESTINATIONTYPE' style='WIDTH:120px;' onChange='showFTPDetails(this.value)'  >";
	echo "<option value='".SCD_DT_EMAIL."' ",($postDESTINATIONTYPE == SCD_DT_EMAIL)?('SELECTED'):(''),">E-MAIL</option>";
	if(isset($_SESSION['staff_user']) && $_SESSION['staff_user'] == 'Y') echo "<option value='".SCD_DT_FTP."' ",($postDESTINATIONTYPE == SCD_DT_FTP)?('SELECTED'):(''),">FTP</option>";
	echo "</select>";
	echo "</TD>";
echo "</TR>";


//EMAIL DETAIL ROWS
$HIDDEN = ($postDESTINATIONTYPE != SCD_DT_EMAIL) ? ('style="display:none;"') : ('');
$CHECKED = ($postSENDTOSELF=="Y") ? (" CHECKED ") : ('');
echo '<TR class="'.GUICommonUtils::styleEO($class).' EMAILROWS" '.$HIDDEN.'>';
		echo "<TD nowrap>E-mail Output to Self?</TD>";
		echo "<TD >";
		echo "<input type='checkbox' name='SENDTOSELF' value='Y' ".$CHECKED." > " , (isset($_SESSION['user_email'])) ? ($_SESSION['user_email']) : ('');
    if (isset($schedule[0])) echo (($schedule[0]["scheduler_scope"]=="P" && $schedule[0]["user_uid"]!=$userId && $postSENDTOSELF=="Y")?"<br>Warning ! This is a principal-scope report and if you edit it and have send-to-self selected then YOU become the SELF not the original person who created the scheduled item. You can enter the email address into the box below in such case.":"");
		echo "</TD>";
echo "</TR>";

echo '<TR class="'.GUICommonUtils::styleEO($class).' EMAILROWS" '.$HIDDEN.'>';
		echo "<TD nowrap valign='top'>Alternate E-mail Recipient List<BR><span style='".FONT_UNOBTRUSIVE_INFO."'>(comma separated list of email addresses)</span></TD>";
		echo "<TD>";
		echo "<textarea id='ALTRECIPIENTLIST' type='text' rows='4' cols='40'>".$postALTRECIPIENTLIST."</TEXTAREA>";
		echo "</TD>";
echo "</TR>";


//FTP DETAIL ROWS
$HIDDEN = ($postDESTINATIONTYPE != SCD_DT_FTP) ? ('style="display:none;"') : ('');
echo '<TR class="'.GUICommonUtils::styleEO($class).' FTPROWS" '.$HIDDEN.'>';
  echo '<TD>FTP Host/Server '; GUICommonUtils::requiredField(); echo'</TD>';
  echo "<TD><INPUT TYPE='TEXT' id='FTPHOST' value='".$postFTPHOST."' maxlength='30' size='30'> <span style='".FONT_UNOBTRUSIVE_INFO."'>eg: retailtrading.net, ftp.example.com</span></TD>";
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).' FTPROWS" '.$HIDDEN.'>';
   echo '<TD>FTP Username '; GUICommonUtils::requiredField(); echo'</TD>';
   echo "<TD><INPUT TYPE='TEXT' id='FTPUSR' value='".$postFTPUSR."' maxlength='30' size='30'></TD>";
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).' FTPROWS" '.$HIDDEN.'>';
   echo '<TD>FTP Password '; GUICommonUtils::requiredField(); echo'</TD>';
   echo "<TD><INPUT TYPE='TEXT' id='FTPPWD' value='".$postFTPPWD."' maxlength='30' size='30'></TD>";
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).' FTPROWS" '.$HIDDEN.'>';
   echo '<TD>FTP Folder </TD>';
   echo "<TD><INPUT TYPE='TEXT' id='FTPFOLDER' value='".$postFTPFOLDER."' maxlength='30' size='30'> <span style='".FONT_UNOBTRUSIVE_INFO."'>eg: reports/directory</span></TD>";
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).' FTPROWS" '.$HIDDEN.'>';
   echo '<TD>FTP Port '; GUICommonUtils::requiredField(); echo'</TD>';
   echo "<TD><INPUT TYPE='TEXT' id='FTPPORT' value='".$postFTPPORT."'  maxlength='5' size='5'> <span style='".FONT_UNOBTRUSIVE_INFO."'>Default 21</span></TD>";
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).' FTPROWS" '.$HIDDEN.'>';
   echo '<TD>FTP Mode '; GUICommonUtils::requiredField(); echo'</TD>';

   echo "<TD id='F_FTP_MODE'>";
   BasicInputElement::getGeneralHorizontalRB('FTPMODE', 'Passive,Active','1,0', $postFTPMODE, 'N', 'N', NULL, NULL, NULL);
   echo "</TD>";
echo '</TR>';


if($postDMLTYPE == "UPDATE"){
  echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
   	echo "<TD>Last Run Date</TD>";
  	echo "<TD style='color:#6666;'>" . $postLASTRUNDATE . "</TD>";
  echo "</TR>";
}

echo "</TABLE>";

echo '<DIV align="center" style="padding:10px 0px;">';
if ($postJOBTYPE == SCD_JT_REPORT) {
	echo "<input type='submit' class='submit' value='Submit Schedule' onclick='submitContentForm(DMLType);' />";
	if ($postDMLTYPE=="UPDATE") echo "<input type='submit' class='submit' value='Delete Schedule' onclick='submitContentForm(\"DELETE\");' />";
}
echo  '</DIV>';

echo "</DIV>"; //sDFOuter


?>

<BR><BR><BR><BR>

<!-- the popup to choose params -->
<DIV id="paramDull" style='position:absolute;top:0px;left:0px;opacity:0.35;filter:alpha(opacity=35);background:lightskyblue;display:none;height:100%;width:100%;z-index:-10'></DIV>
<DIV id='paramsLayer' style='display:none;z-index=20;position:absolute;top:40px;left:0px;width:100%' align="center">
  	<DIV id='paramsPopup' style='overflow:auto;border:5px solid #1e4272;background-color:white;padding:10px;width:650px;' ></DIV>
</DIV>

<STYLE TYPE="TEXT/CSS">
table table {border:0px;}
td,th{font-family: Verdana, Arial, Helvetica, sans-serif;font-size:11px;}
</STYLE>

<?php DatePickerElement::getDatePickerLibs(); ?>

<script type="text/javascript" >

var DMLType = "<?php echo $postDMLTYPE; ?>"; // must be done this way otherwise if you create then modify same, it keeps adding new
var alreadySubmitted = false;
var paramsAlreadySent = false;

function successCallback(p_type, id) {
	if (DMLType=="INSERT") {

		//CLEAR FORM VALUES HERE
		document.getElementById("JOBTYPE").options[0].selected = true;
		document.getElementById("OBJID").options[0].selected = true;
		document.getElementById("OUTPUTTYPE").options[0].selected = true;
    	document.getElementById("PARAMETERLIST").value = '';
		document.getElementsByName("RUNINTERVAL")[0].checked = true;

		//INTERVALS
		var RUNDAY = document.getElementsByName('RUNDAY');
		for(var i = 0; i < RUNDAY.length; i++) {RUNDAY[i].checked = false;}
		var RUNWEEK = document.getElementsByName('RUNWEEK');
		for(var i = 0; i < RUNWEEK.length; i++) {RUNWEEK[i].checked = false;}
		showHideID('RUNWEEK_TD','RUNDAY_TD');
		var RT = document.getElementsByName('RUNTIME');
		for(var i = 0; i < RT.length; i++) {RT[i].checked = false;}

		document.getElementById("DESTINATIONTYPE").options[0].selected = true;
		//EMAIL SETTINGS
		document.getElementsByName("SENDTOSELF").checked = true;
		document.getElementById("ALTRECIPIENTLIST").value = '';

		//FTP SETTINGS
		document.getElementById("FTPHOST").value = '';
    	document.getElementById("FTPUSR").value = '';
    	document.getElementById("FTPPWD").value = '';
    	document.getElementById("FTPFOLDER").value = '';
    	document.getElementById("FTPPORT").value = 21;
    	document.getElementsByName('FTPMODE')[0].checked = true;
    	showFTPDetails(1);

	} else if (p_type=="DELETE") {
		//RE-CLICK MODIFY TAB
		getContent("<?php echo $ROOT.$PHPFOLDER; ?>functional/scheduler/modifySchedule.php","DMLTYPE=UPDATE");
	}
}

function errorCallback(p_type){}

function submitContentForm(p_type) {

	if (alreadySubmitted) { return; }
	alreadySubmitted = true;

	var params='DMLTYPE='+p_type;
	params+='&SCHEDULEID='+document.getElementById("SCHEDULEID").value;
	params+='&JOBTYPE='+document.getElementById("JOBTYPE").value;
	params+='&OBJECTID='+document.getElementById("OBJID").value;
	if (convertElementToArray(document.getElementsByName('RUNINTERVAL'))=="M")	params+='&RUNDAY='+convertElementToArray(document.getElementsByName("RUNDAY")); else params+='&RUNDAY=';
	if (convertElementToArray(document.getElementsByName('RUNINTERVAL'))=="W") params+='&RUNWEEK='+convertElementToArray(document.getElementsByName("RUNWEEK")); else params+='&RUNWEEK=';
	params+='&RUNTIME='+convertElementToArray(document.getElementsByName("RUNTIME"));
	params+='&REGENERATE='+convertElementToArray(document.getElementsByName("REGENERATE"));
	params+='&ALTRECIPIENTLIST='+document.getElementById("ALTRECIPIENTLIST").value;
	params+='&SENDTOSELF='+convertElementToArray(document.getElementsByName("SENDTOSELF"));
	params+='&PARAMETERLIST='+encodeURIComponent(document.getElementById("PARAMETERLIST").value);
	params+='&OUTPUTTYPE='+document.getElementById("OUTPUTTYPE").value;

	var DESTINATION = document.getElementById("DESTINATIONTYPE").value;
	params+='&DESTINATIONTYPE='+DESTINATION;

	if(DESTINATION == <?php echo SCD_DT_FTP; ?>){
    	//FTP DETAILS IF SELECTED
    	params+='&FTPHOST='+encodeURIComponent(document.getElementById("FTPHOST").value.replace(/'/g,''));
    	params+='&FTPUSR='+encodeURIComponent(document.getElementById("FTPUSR").value.replace(/'/g,''));
    	params+='&FTPPWD='+encodeURIComponent(document.getElementById("FTPPWD").value.replace(/'/g,''));
    	params+='&FTPFOLDER='+encodeURIComponent(document.getElementById("FTPFOLDER").value.replace(/'/g,''));
    	params+='&FTPPORT='+encodeURIComponent(document.getElementById("FTPPORT").value.replace(/'/g,''));
    	params+='&FTPMODE='+convertElementToArray(document.getElementsByName('FTPMODE'));
	}

	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER; ?>functional/scheduler/scheduleSubmit.php',
						  'alreadySubmitted=false; if (msgClass.type=="S") successCallback("'+p_type+'",msgClass.identifier); else errorCallback("'+p_type+'");',
						  'Please wait while request is processed...');
}

function showHideID(showID, hideID){
  $('#'+showID).show();
  $('#'+hideID).hide();
  adjustMyFrameHeight();
}

function showFTPDetails(val){
  if(val == <?php echo SCD_DT_FTP; ?>){
  	$('.EMAILROWS').hide();
  	$('.FTPROWS').show();
  } else {

  	$('.FTPROWS').hide(); $('.EMAILROWS').show();
  }
  adjustMyFrameHeight();
}

function showPopup() {
	$("#paramsLayer").show();
	$("#paramDull").show();
}

function hideParamsPopup() {
	alreadySubmitted = false;
	$("#paramsPopup").children("form[name=submit_params]").remove();
	$("#paramsPopup").html("");
	$("div#paramsLayer").hide();
	$("#paramDull").hide();
}

 // returns false if invalid or missing required field
function setParams() {
	// force garbage collection in IE so no old DOM is left
	if (typeof(CollectGarbage) == "function") CollectGarbage();
	var params="";
	if (!setparamBaseValues()) return false; // function in parameterBase.php - populate the hidden fields with their values
	$("form[name=submit_params]").children("input").each(function (i) {
		if(this.name.substring(0,1)=="p") {
			// check if "always user select all" has been chosen
			selectAll=false;
			$("form[name=submit_params]").children("input[name=sa"+this.name.substring(1)+"]").each(function (i) {
				if (this.value=="*") selectAll=true;
				return false; // jquery exit
			});
			if (selectAll) {
				if (params=="") params=this.name+"=*";
				else params+="&"+this.name+"=*";
			} else {
				if (params=="") params=this.name+"="+this.value;
				else params+="&"+this.name+"="+this.value;
			}
		}
		});

	$('#PARAMETERLIST').val(params);
	hideParamsPopup();
	alreadySubmitted=false;

	return true;
}

function refreshParams(id) {

  if (paramsAlreadySent){return;}

  	if (id=='' || id==undefined){
		parent.popBox('<font color="#000">Invalid Report, Please select a report.</font>','ERROR');
	} else {
		paramsAlreadySent = true;
		alreadySubmitted = true;
		showPopup();
	// NB! you must use jquery HTML to set innerHTML as otherwise memory leaks occur and previous param DOM tree still exists in mem before garbage collector eventually runs. It doesn't help to first set innerhtml to null either.
	AjaxRefreshHTML("REPORTID=" + id + "&SCHEDULER=Y&"+document.getElementById('PARAMETERLIST').value,
				"<?php echo $ROOT.$PHPFOLDER; ?>functional/reports/parameterBase.php",
				"paramsPopup",
				"Retrieving Parameter Options ...",
				"paramsAlreadySent=false;$('#paramsPopup').html(retVal+'<input type=\"submit\" class=\"submit\" value=\"Cancel\" onclick=\"hideParamsPopup();\" />'\
															+'<input type=\"submit\" class=\"submit\" value=\"Submit Parameters\" onclick=\"setParams();\" />');");
	}
}

</script>
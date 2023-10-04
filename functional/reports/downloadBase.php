<?php

include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ReportDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/xmlClass.php');


set_time_limit(60*5);

if (isset($_POST['REPORTID'])) $postREPORTID=$_POST['REPORTID']; else $postREPORTID=$_GET['REPORTID'];

// if scheduler called this, then validate

if (!isset($_SESSION)) session_start();
if (isset($_SESSION['user_id']) && (isset($_SESSION['principal_id']))) {
	$userId = $_SESSION['user_id'];
	$principalId = $_SESSION['principal_id'];
	$principalCode = $_SESSION['principal_code'];
} else {
	// if called by scheduler
	$sysId = "";
	if (isset($_POST[md5(SESSION_SYSTEM_USERID_PARAM_NAME)])) $sysId=$_POST[md5(SESSION_SYSTEM_USERID_PARAM_NAME)]; else if (isset($_GET[md5(SESSION_SYSTEM_USERID_PARAM_NAME)])) $sysId=$_GET[md5(SESSION_SYSTEM_USERID_PARAM_NAME)];
	if (($sysId=="") || ($sysId!=md5(SESSION_ADMIN_USERID))) {
		echo "Illegal Access to downloadBase. You do not have permissions.";
		return;
	}
	if (isset($_POST['USERID'])) $userId=$_POST['USERID']; else if (isset($_GET['USERID'])) $userId=$_GET['USERID'];
	if (isset($_POST['PRINCIPALID'])) $principalId=$_POST['PRINCIPALID']; else if (isset($_GET['PRINCIPALID'])) $principalId=$_GET['PRINCIPALID'];
	if (isset($_POST['PRINCIPALCODE'])) $principalCode=$_POST['PRINCIPALCODE']; else if (isset($_GET['PRINCIPALCODE'])) $principalCode=$_GET['PRINCIPALCODE'];
}

//FILTER SCHEDULE VALUE TO HERE.
$postSCHEDULER = (isset($_POST['SCHEDULER'])) ? ($_POST['SCHEDULER']) : ('N');
$outputType = (isset($_POST['pOUTPUT'])) ? ($_POST['pOUTPUT']) : SCD_OT_CSV;

$dbConn = new dbConnect();
$dbConn->dbConnection();

$reportDAO = new ReportDAO($dbConn);

// check permissions
$adminDAO=new AdministrationDAO($dbConn);
$hasRole=$adminDAO->hasRole($userId,$principalId,ROLE_REPORTS);
if (!$hasRole) {
	echo "You do not have permissions to run reports.";
	$dbConn->dbClose();
	return;
}

$principalDAO = new PrincipalDAO($dbConn);
$mfP = $principalDAO->getPrincipalItem($principalId);
$principalName = $mfP[0]["principal_name"];

//USER SETTINGS
$userReportOutputSetting = false;
$mfUP = $adminDAO->getUserPreferences($userId);
if (!sizeof($mfUP)==0) {
  $userReportOutputSetting = $mfUP[0]["user_report_output_setting"];
}

$database="";
$paramsArr=array();
// there may be gaps in indexes so must do it like this
foreach($_POST as $name=>$value) {
	if (preg_match("/^p[0-9]/",$name)) $paramsArr[$name]=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($value));
}
$resultTO = $reportDAO->reportSQL_getReportSQL($postREPORTID, $userId, $principalId, $principalCode, $paramsArr);

if ($resultTO->type != FLAG_ERRORTO_SUCCESS) {
	echo $resultTO->description;
	$dbConn->dbClose();
	return;
}

$sql = $resultTO->object->sql;
$database = $resultTO->object->database;
$formater = (!empty($resultTO->object->columnFormat)) ? $resultTO->object->columnFormat : false;
$fileName = $resultTO->object->fileName;
$xmlSchema = $resultTO->object->xmlSchema;
$reportLevel = $resultTO->object->reportLevel;
$runOnceFieldName = $resultTO->object->runOnceFieldName;
$hiddenColList = $resultTO->object->hiddenColList;
$totalBreakColList = $resultTO->object->totalBreakColList;
$totalSumColList = $resultTO->object->totalSumColList;
$phpScript = $resultTO->object->reportRow[0]['php_script'];
$StopPhpScript = $resultTO->object->reportRow[0]['stopphpscript'];

$reportSQLTO = $resultTO->object;

// wrap all php here to protect the parent
function phpScript($script) {
  global $ROOT, $PHPFOLDER, $paramsArr, $reportSQLTO, $reportDAO, $postREPORTID, $userId, $principalId, $principalCode; $outputType;
  $path = explode("?",$script);
  if (isset($path[1])) {
    $params = explode("&",$path[1]);
    foreach($params as $p) {
      $param = explode("=",$p);
      $_REQUEST[$param[0]]=$param[1];
    }
  }
  include_once($ROOT.$PHPFOLDER.$path[0]);
}
if ($phpScript!="") {
  phpScript($phpScript);
  return;
}

// set up the Smart Event fields to record the run once
// eg. type ~ S34 where S=Scheduler and 34 is scheduler uid ; else U366 where U = user and 366 is user id
if ((isset($GLOBALS["SCRIPTORIGIN"])) && ($GLOBALS["SCRIPTORIGIN"]=="SCHEDULER")) {
  $runOnceParams=array("runOnceFieldName"=>$runOnceFieldName, "type"=>$GLOBALS["SE_RUNONCE_TYPE"], "typeUId"=>$GLOBALS["SE_RUNONCE_TYPE_UID"]); // just use what was passed by calling program
} else {
  $runOnceParams=array("runOnceFieldName"=>$runOnceFieldName, "type"=>"U".$userId, "typeUId"=>$postREPORTID);
}
$resultTO = $reportDAO->reportSQL_runReportSQL($sql,$database,$runOnceParams,$hiddenColList);



if ($resultTO->type != FLAG_ERRORTO_SUCCESS) {
	echo $resultTO->description;
	$dbConn->dbClose();
	return;
}

if (!count($resultTO->object->data) > 0) {

    echo '<div align="center"><br><br>';
	echo "No Rows found.";
	echo "<br><a href='javascript:history.back(1);' >back</a>";
	echo '</div>';
	return;

}

// Add Control Break Total Lines if necessary
$rTO = $reportDAO->reportSQL_addBreakTotals ($reportSQLTO, $resultTO->object->data);
if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
  echo $rTO->description;
  $dbConn->dbClose();
  return;
}
unset($resultTO->object->data);
$resultTO->object->data = $rTO->object;


//changes output
if($outputType == SCD_OT_CSV){

  $fileName =  str_replace(' ', '_', $fileName . ".csv");

  $reportOutput = $reportDAO->reportSQL_arrayToCSV($resultTO->object->data, true, $userReportOutputSetting);

  header("Content-Description: File Transfer");
  header("Content-Disposition: attachment; filename=\"".$fileName."\"");
  header("Content-Type: application/force-download");
  echo $reportOutput;

} else if ($outputType == SCD_OT_XML){

  $fileName = $fileName . ".xml";

  $xmlResult = new arrayToXMLschema($xmlSchema, $resultTO->object->data);

  if ($xmlResult->errorTO->type != FLAG_ERRORTO_SUCCESS) {
	echo $xmlResult->errorTO->description;
	$dbConn->dbClose();
	return;
  }

  $reportOutput = $xmlResult->resultXML;

  header("Content-Type: application/force-download");
  header("Content-Disposition: filename=\"".$fileName."\"");
  echo $reportOutput;

} else if ($outputType == SCD_OT_HTML){

  //if not from scheduler include header for embed html.
  if($postSCHEDULER == 'N'){

    $reportName = $fileName;  //same as filename -

    echo '<div style="padding:0px 12px;font: 12px Helvetica, Verdana, Arial, sans-serif;">';
    echo '<table cellpadding="4" cellspacing="0" style="font-size:12px">';
    echo '<tr>';
      echo '<td width="90"><a href="javascript:history.back(1);" style="text-align:center;display:block;border:1px solid #ccc;padding:0px 8px;line-height:36px;text-decoration:none;color:#666;font-weight:bold;"><img src="../../images/left.jpg" border="0" alt="" align="left" style="margin:8px 0px;" >Back</a></td>';
      echo '<td width="90"><a href="javascript:window.print();" style="text-align:center;display:block;border:1px solid #ccc;padding:0px 8px;line-height:36px;text-decoration:none;color:#666;font-weight:bold;"><img src="../../images/print-icon.png" border="0" alt="" align="left" style="margin:2px 0px;" > Print</a></td>';
      echo '<td width="90"><a href="javascript:popupToEmail(\'Kwelanga Report: '.$reportName.' (' . $principalId . ')\');" style="text-align:center;display:block;border:1px solid #ccc;padding:0px 8px;line-height:36px;text-decoration:none;color:#666;font-weight:bold;"><img src="../../images/email-icon.png" border="0" alt="" align="left" style="margin:2px 0px;" > Email</a></td>';
      echo '<td></td>';
    echo '</tr>';
    echo '</table>';
    echo '</div>';

    echo '<BR><BR>';

    echo '<div id="emailHTMLArea" >';
      echo '<div style="padding:0px 12px;font: 12px Helvetica, Verdana, Arial, sans-serif;">';  //email section starts
        echo '<h2>'.$reportName.'</h2>';
        echo 'Principal: <b><u>' . $principalName . '</u></b><br>';
        echo 'Date Generated: ' . date('Y-m-d H:i:s') . '<br>';
        echo 'Report Level: ' . $reportLevel . '<br>';
        //display parameters selected.
        $paramStr = array();
        foreach($paramsArr as $k=>$v){
          $paramStr[]= $k.'='.$v;
        }
        //echo 'Parameters: <input type="text" value="' . join('&',$paramStr) . '" name="reportParam" size="30" disabled="disabled" style="border:1px solid #fff">';

      echo '</div>';
  }

  $reportOutput = $reportDAO->reportSQL_arrayToHTML($resultTO->object->data, true, $formater);
  echo $reportOutput;

  if($postSCHEDULER == 'N'){


    echo '</div>';  //email section ends

    //FANCY ROW HIGHLIGHT
    echo '<script type="text/javascript" language="javascript" src="'.$DHTMLROOT.$PHPFOLDER.'js/jquery.js"></script>';
    echo '<script type="text/javascript">';
    echo '$(document).ready(function(){
    	$(".report_table tbody tr").hover(
    		function () {$(this).children("td").css("background-color","#F3F781");},
        	function () {$(this).children("td").css("background-color","");}
  		);
    });';
    echo '
    	function popupToEmail(emailSubject){
    		var data = $(\'#emailHTMLArea\').html();
    		var len = data.length;

    		//DO NOT SEND DATA bigger than 2MB.
    		if(len > 2097152){
    			parent.popBox("<font color=\'black\'>The following report is too Large to be Emailed! ("+len+" b)<br>Please try generating a CSV/Excel version instead.</font>","error");
    		} else {

				parent.popBox("<div align=\'center\' style=\'color:black;\'><br><span style=\'color:red\' id=\'EmailErr\'><br></span><br>Address: <input type=\'text\' value=\'\' id=\'reportEmail\'> <input type=\'hidden\' id=\'emailSubject\' value=\'"+emailSubject+"\' ><input type=\'submit\' class=\'submit\' value=\'Send Email\' onClick=\'content.sendAreaEmail(this)\'><br><br><br></div>","general");
				parent.document.getElementById(\'reportEmail\').focus();
			}
    	}

    	function sendAreaEmail(obj){

    		obj.value = "sending...";
    		obj.disabled = true;

    		var email = parent.document.getElementById(\'reportEmail\').value;

    		params = "HTMLSTR=" + encodeURIComponent($("#emailHTMLArea").html()).replace(/\n/g,"").replace(/\r/g,"");
    		params += "&EMAILADD=" + email;
    		params += "&SUBJECT=" + parent.document.getElementById(\'emailSubject\').value;

         	$.ajax({
        	  url: "'.$DHTMLROOT.$PHPFOLDER.'functional/administration/functions/emailAddressHTML.php",
        	  global: false,
        	  type: "POST",
              data: params,
              dataType: "html",
        	  cache: false,
        	  success: function(data){

        	  	obj.value = "Send Email";
    			obj.disabled = false;

        	  	eval(data);

				if(msgClass.type == "'.FLAG_ERRORTO_SUCCESS.'"){
					parent.popBoxClose();
					parent.popBox("<div align=\'center\' style=\'color:black;\'><br>Email successfully sent to: \'"+ email +"\'<br></div>","success");
				} else {
					parent.$("#EmailErr").html("ERROR: "  + msgClass.description + "<br>");
				}
        	  },
          	  error: function(XMLHttpRequest, textStatus, errorThrown) {
          	  	obj.value = "Send Email";
				obj.disabled = false;
        		alert("AJAX Error: " + XMLHttpRequest.responseText + " - " + textStatus + " : " + errorThrown);
        	  }
          });

    	}
    ';
    echo '</script>';

  }

} else if($outputType == SCD_OT_PDF){

  $scriptPath = __DIR__ . '/' . $reportSQLTO->pdfScriptPath;

  if(!is_file($scriptPath)){
    echo '<div align="center"><br><br>';
    echo "No PDF Template found!";
    echo "<br><a href='javascript:history.back(1);' >back</a>";
    echo '</div>';
    return;
  } else {

    include($scriptPath);

    $pdf = new PDFReport($dbConn);
    $pdf->render($resultTO->object->data, $fileName);
    $pdf->save(str_replace(' ', '_', $fileName . ".pdf"));

    //$fileName =
    #$reportOutput = $reportDAO->reportSQL_arrayToCSV($resultTO->object->data, true, $userReportOutputSetting);

    #header("Content-Description: File Transfer");
    #header("Content-Disposition: attachment; filename=\"".$fileName."\"");
    #header("Content-Type: application/force-download");



  }


}

// Must only commit the run-once updates (if applicable) here in case of code fall over above
// only commit if user running directly
if ((!isset($GLOBALS["SCRIPTORIGIN"])) || ($GLOBALS["SCRIPTORIGIN"]!="SCHEDULER")) {
  $dbConn->dbQuery("commit");
}

$dbConn->dbClose();

?>
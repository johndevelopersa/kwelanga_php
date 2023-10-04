<?php

/*
 *
 * THIS LAYER PROVIDES BASIC SCREENING AND CONVERTS INPUT INTO PROPER TO BEFORE CALL IS MADE TO PROCESS
 *
 */

//LIBS
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/ValidationCommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/PostSchedulerDAO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingSchedulerTO.php");

$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback

if (!isset($_SESSION)) session_start;

// override the principal Id for now.
$principalId = $_SESSION['principal_id'];
$principalCode = $_SESSION['principal_code'];
$userId = $_SESSION['user_id'];


$returnMessage;

//DB OBJ
$dbConn = new dbConnect();
$dbConn->dbConnection();


$postDMLTYPE          = (isset($_POST['DMLTYPE'])) ? ($_POST['DMLTYPE']) : ('');
$postSCHEDULEID       = (isset($_POST['SCHEDULEID'])) ? (htmlspecialchars($_POST['SCHEDULEID'])) : ('');
$postJOBTYPE          = (isset($_POST['JOBTYPE'])) ? (htmlspecialchars($_POST['JOBTYPE'])) : ('');
$postOBJECTID         = (isset($_POST['OBJECTID'])) ? (htmlspecialchars($_POST['OBJECTID'])) : ('');
$postPARAMETERLIST    = (isset($_POST['PARAMETERLIST'])) ? (urldecode(mysqli_real_escape_string($dbConn->connection, $_POST['PARAMETERLIST']))) : ('');
$postRUNDAY           = (isset($_POST['RUNDAY'])) ? (htmlspecialchars($_POST['RUNDAY'])) : ('');
$postRUNWEEK          = (isset($_POST['RUNWEEK'])) ? (htmlspecialchars($_POST['RUNWEEK'])) : ('');
$postRUNTIME          = (isset($_POST['RUNTIME'])) ? (htmlspecialchars($_POST['RUNTIME'])) : ('');
$postREGENERATE       = (isset($_POST['REGENERATE'])) ? (htmlspecialchars($_POST['REGENERATE'])) : ('');
$postOUTPUTTYPE       = (isset($_POST['OUTPUTTYPE'])) ? (htmlspecialchars($_POST['OUTPUTTYPE'])) : ('');
$postDESTINATIONTYPE  = (isset($_POST['DESTINATIONTYPE'])) ? (htmlspecialchars($_POST['DESTINATIONTYPE'])) : ('');
$postSENDTOSELF       = (isset($_POST['SENDTOSELF'])) ? (htmlspecialchars($_POST['SENDTOSELF'])) : ('');
$postALTRECIPIENTLIST = (isset($_POST['ALTRECIPIENTLIST'])) ? (htmlspecialchars($_POST['ALTRECIPIENTLIST'])) : ('');


if ($postDESTINATIONTYPE == SCD_DT_FTP) {

 //Build FTP settings into array for serialize : easy to read out.
 $postFTP = array();
 $postFTP['HOST']   = (isset($_POST['FTPHOST'])) ? ($_POST['FTPHOST']):('');
 $postFTP['USR']    = (isset($_POST['FTPUSR'])) ? ($_POST['FTPUSR']):('');
 $postFTP['PWD']    = (isset($_POST['FTPPWD'])) ? ($_POST['FTPPWD']):('');
 $postFTP['FOLDER'] = (isset($_POST['FTPFOLDER'])) ? ($_POST['FTPFOLDER']):('');
 $postFTP['PORT']   = (isset($_POST['FTPPORT'])) ? ($_POST['FTPPORT']):('21');
 $postFTP['MODE']   = (isset($_POST['FTPMODE'])) ? ($_POST['FTPMODE']):('1');
 $postSENDTOSELF  = '';
 $postALTRECIPIENTLIST  = '';

}

// start of superficial checks. Main checks done in POST DAO
if (($postDMLTYPE!="UPDATE") && ($postDMLTYPE!="INSERT") && ($postDMLTYPE!="DELETE")) {
  return buildErr('Invalid Processing Type!');
}

if (($postDMLTYPE=="UPDATE") && empty($postSCHEDULEID)) {
   return buildErr('UPDATE Requires a UID');
}

if($postJOBTYPE!=SCD_JT_REPORT) {
   return buildErr('Invalid Report Type.');
}


$postingSchedulerTO = new PostingSchedulerTO();
$postingSchedulerTO->DMLType = $postDMLTYPE;
$postingSchedulerTO->uid = $postSCHEDULEID;
$postingSchedulerTO->jobType = $postJOBTYPE;
$postingSchedulerTO->objectId = $postOBJECTID;
$postingSchedulerTO->runDay = $postRUNDAY;
$postingSchedulerTO->runWeek = $postRUNWEEK;
$postingSchedulerTO->runTime = $postRUNTIME;
$postingSchedulerTO->regenerate = ($postREGENERATE=="") ? ("N") : ($postREGENERATE);
$postingSchedulerTO->altRecipientList = $postALTRECIPIENTLIST;
$postingSchedulerTO->sendToSelf = ($postSENDTOSELF=="") ? ("N") : ($postSENDTOSELF);
$postingSchedulerTO->parameterList = $postPARAMETERLIST;
$postingSchedulerTO->principalUId = $principalId;
$postingSchedulerTO->principalCode = $principalCode;
$postingSchedulerTO->userUId = $userId;
$postingSchedulerTO->destinationType = $postDESTINATIONTYPE;
$postingSchedulerTO->destinationAddress = ($postDESTINATIONTYPE == SCD_DT_FTP) ? (serialize($postFTP)) : ('');
$postingSchedulerTO->outputType = $postOUTPUTTYPE;


$postSchedulerDAO = new PostSchedulerDAO($dbConn);
$result = $postSchedulerDAO->postSchedule($postingSchedulerTO);


if ($result->type==FLAG_ERRORTO_SUCCESS) {

  $dbConn->dbinsQuery("commit;");
  echo CommonUtils::getJavaScriptMsg($result);
  return;

} else {

  $dbConn->dbinsQuery("rollback;");
  echo CommonUtils::getJavaScriptMsg($result);
  return;

}


function buildErr($msg){
  	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description=$msg;
	echo CommonUtils::getJavaScriptMsg($returnMessages);
	return;
}


$dbConn->dbClose();

?>
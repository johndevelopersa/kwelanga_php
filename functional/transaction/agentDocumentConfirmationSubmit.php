<?php
/*
 *
 * THIS LAYER PROVIDES BASIC SCREENING AND CONVERTS INPUT INTO PROPER TO BEFORE CALL IS MADE TO PROCESS
 *
 */
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");

$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback

if (!isset($_SESSION)) session_start;
// override the principal Id for now.
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION['user_id'];

$returnMessage;

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
if (isset($_POST['DMUID'])) $postDMUID = mysql_real_escape_string($_POST['DMUID']); else $postDMUID=false;
$postAGENT = ((isset($_POST['AGENT']))?$_POST['AGENT']:false);

if ($postDMUID===false || empty($postDMUID) || $postAGENT===false) {
  $errorTO->type=FLAG_ERRORTO_ERROR;
  $errorTO->description="Missing Parameters";
  print(CommonUtils::getJavaScriptMsg($errorTO));
  return;
}

$adminDAO=new AdministrationDAO($dbConn);
$hasRole=$adminDAO->hasRole($userId,$principalId,ROLE_AGENT_DOCMENT_CONFIRMATION);
if ($hasRole!==true) {
	$errorTO->type=FLAG_ERRORTO_ERROR;
	$errorTO->description="You do not have permissions to Confirm Document Confirmations";
	print(CommonUtils::getJavaScriptMsg($errorTO));
	return;
}

include_once($ROOT.$PHPFOLDER."TO/SmartEventTO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostBIDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/BIDAO.php");
$postBIDAO = new PostBIDAO($dbConn);
$BIDAO = new BIDAO($dbConn);

// check if exists - this is a form of security anyway
// if it exists then the user wants to delete, else insert it
$seRows = $BIDAO->getSmartEventsByTypeData("AGNTVERIFIED", $typeUid = $userId, $dataUid = $postDMUID);
// NB !
// At the moment this only works if only one agent for user exists because the delete will remove all occurrences of data_uid 
// not just for groups the agent is member of !
// If you want it to work, you need to store a separate row in SE for each agent the user is member of, and only delete the row for that general_reference_1
if (empty($seRows)) {
  $seTO = new SmartEventTO();
  $seTO->type = "AGNTVERIFIED";
  $seTO->typeUid = $userId;
  $seTO->dataUid = array($postDMUID);
  $seTO->status = FLAG_STATUS_CLOSED;
  $seTO->statusMsg = "AGENT DOC ACCEPTANCE";
  $seTO->generalReference1 = $postAGENT;
  
  $rTO = $postBIDAO->postSmartEventBulk($seTO);
} else {
  $rTO = $postBIDAO->removeSmartEventsByTypeData("AGNTVERIFIED", $typeUid = $userId, $dataUid = $postDMUID);
}

if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
	$dbConn->dbQuery("rollback");
	$errorTO->type=$rTO->type;
	$errorTO->description="The changes could not be saved ! <BR><BR>".$rTO->description;
	print(CommonUtils::getJavaScriptMsg($errorTO));
	return;
} else {
	$dbConn->dbQuery("commit");
	$errorTO->type=$rTO->type;
	$errorTO->description="Successfully updated Agent Acceptance";
  print(CommonUtils::getJavaScriptMsg($errorTO));
  return;
}

?>

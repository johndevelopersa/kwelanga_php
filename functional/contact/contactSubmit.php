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
include_once($ROOT.$PHPFOLDER.'libs/ValidationCommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER."TO/PostingPrincipalContactTO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostMiscellaneousDAO.php");


if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();
$returnMessages = new ErrorTO;



//GRAB POST VARS
$postDMLTYPE = (isset($_POST['DMLTYPE'])) ? ($_POST['DMLTYPE']) : ('VIEW');
$postCONTACTUID = (isset($_POST['CONTACTUID'])) ? ($_POST['CONTACTUID']) : ('');
$postEMAILADDR = (isset($_POST['EMAILADDR'])) ? (trim($_POST['EMAILADDR'])) : ('');  //REQUIRED
$postMOBILENO = (isset($_POST['MOBILENO'])) ? (trim($_POST['MOBILENO'])) : ('');
$postDEPOT = (isset($_POST['DEPOT'])) ? ($_POST['DEPOT']) : ('');
$postCONTACTTYPE = (!empty($_POST['CONTACTTYPE'])) ? ($_POST['CONTACTTYPE']) : (false);  //REQUIRED
$postFTP = array();  //will be serialize for storage.
$postFTP['HOST']   = (isset($_POST['FTPHOST'])) ? (trim($_POST['FTPHOST'])):('');
$postFTP['USR']    = (isset($_POST['FTPUSR'])) ? (trim($_POST['FTPUSR'])):('');
$postFTP['PWD']    = (isset($_POST['FTPPWD'])) ? (trim($_POST['FTPPWD'])):('');
$postFTP['FOLDER'] = (isset($_POST['FTPFOLDER'])) ? (trim($_POST['FTPFOLDER'])):('');
$postFTP['PORT']   = (isset($_POST['FTPPORT'])) ? (trim($_POST['FTPPORT'])):('21');
$postFTP['MODE']   = (isset($_POST['FTPMODE'])) ? (trim($_POST['FTPMODE'])):('1');


//Checks
if (($postDMLTYPE != "UPDATE") && ($postDMLTYPE != "INSERT") && ($postDMLTYPE != "DELETE")) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if (($postDMLTYPE == "UPDATE") && ($postCONTACTUID == "")) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="UPDATE Requires a UID";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if((($postDMLTYPE == "UPDATE") || ($postDMLTYPE == "DELETE")) && !is_numeric($postCONTACTUID)) {
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Principal Contact Id not valid.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if($postCONTACTTYPE === false) {

	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Principal Contact Type.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};



//POST
$postingContactTO = new PostingPrincipalContactTO();
$postingContactTO->DMLType = $postDMLTYPE;
$postingContactTO->cuid = $postCONTACTUID;
$postingContactTO->principalUid = $principalId;
$postingContactTO->depotUid = $postDEPOT;
$postingContactTO->contactTypeUid = $postCONTACTTYPE;
$postingContactTO->emailAddr = $postEMAILADDR;
$postingContactTO->mobileNumber = $postMOBILENO;
$postingContactTO->ftpAddr = (!empty($postFTP['HOST']))?(serialize($postFTP)):('');

$postMiscDAO = new PostMiscellaneousDAO($dbConn);
$result = $postMiscDAO->postPrincipalContact($postingContactTO);
$seqPS = $result->identifier;

if ($result->type == FLAG_ERRORTO_SUCCESS) {
	$result2 = mysqli_query($dbConn->connection, "commit");
} else {
	$result2 = mysqli_query($dbConn->connection, "rollback");
	$returnMessages->type=FLAG_ERRORTO_ERROR;

}


$dbConn->dbClose();


// check return values
if (sizeof($result)> 0) {
	print(CommonUtils::getJavaScriptMsg($result));
	return;
} else if (sizeof($result)== 0) {

	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Problem occurred during posting : Post returned 0 arraysize. Please inform RetailTrading Management.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
  }

print(CommonUtils::getJavaScriptMsg($result));
return;

?>

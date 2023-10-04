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
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");

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
if (isset($_POST['ACTIONTYPE'])) $postACTIONTYPE = mysql_real_escape_string($_POST['ACTIONTYPE']); else $postACTIONTYPE="";
if (isset($_POST['PSMPARENTUID'])) $postPSMPARENTUID = mysql_real_escape_string($_POST['PSMPARENTUID']); else $postPSMPARENTUID="";
if (isset($_POST['PSMCHILDUID'])) $postPSMCHILDUID = mysql_real_escape_string($_POST['PSMCHILDUID']); else $postPSMCHILDUID="";
if (isset($_POST['DOCMASTID'])) $postDOCMASTID = mysql_real_escape_string($_POST['DOCMASTID']); else $postDOCMASTID="";
if (isset($_POST['ASSOCUID'])) $postASSOCUID = mysql_real_escape_string($_POST['ASSOCUID']); else $postASSOCUID="";

include_once($ROOT.$PHPFOLDER."DAO/PostStoreDAO.php");
$postStoreDAO = new PostStoreDAO($dbConn);

switch ($postACTIONTYPE) {
	case "SETLINK": {
		include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
		$postTransactionDAO = new PostTransactionDAO($dbConn);
		
		$storeDAO = new StoreDAO($dbConn);
		$mfS = $storeDAO->getPrincipalStoreItem($postPSMPARENTUID);
		if (sizeof($mfS)==0) {
			$errorTO->type = FLAG_ERRORTO_ERROR;
			$errorTO->description = "Invalid PSMPARENTUID passed - store not found";
			print(CommonUtils::getJavaScriptMsg($errorTO));
			return;
		}
		
		// create association
		$result=$postStoreDAO->associateStore($principalId, $postPSMPARENTUID, $postPSMCHILDUID);
		if ($result->type!=FLAG_ERRORTO_SUCCESS) {
			$dbConn->dbQuery("rollback");
			print(CommonUtils::getJavaScriptMsg($result));
			return;
		}
		// link document
		$result=$postTransactionDAO->associateDocumentDepotStore($postDOCMASTID, $postPSMPARENTUID);
		if ($result->type!=FLAG_ERRORTO_SUCCESS) {
			$dbConn->dbQuery("rollback");
			print(CommonUtils::getJavaScriptMsg($result));
			return;
		}
		$errorTO->identifier="var msgClassIdentifier={delloc:\"".(str_replace("'","\'",$mfS[0]["store_name"]))."\",".
																								 "delarea:\"".(str_replace("'","\'",$mfS[0]["area_description"]))."\",".
																								 "delday:\"".(str_replace("'","\'",$mfS[0]["delivery_day"]))."\"};"; // is eval() on client side. It is passed to client as '...contents...' so strip out apostrophes here
		break;
	}
	case "USELINK": {
		include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
		$postTransactionDAO = new PostTransactionDAO($dbConn);
		
		$storeDAO = new StoreDAO($dbConn);
		$mfS = $storeDAO->getPrincipalStoreItem($postPSMPARENTUID);
		if (sizeof($mfS)==0) {
			$errorTO->type = FLAG_ERRORTO_ERROR;
			$errorTO->description = "Invalid PSMPARENTUID passed - store not found";
			print(CommonUtils::getJavaScriptMsg($errorTO));
			return;
		}
		
		// link document
		$result=$postTransactionDAO->associateDocumentDepotStore($postDOCMASTID, $postPSMPARENTUID);
		if ($result->type!=FLAG_ERRORTO_SUCCESS) {
			$dbConn->dbQuery("rollback");
			print(CommonUtils::getJavaScriptMsg($result));
			return;
		}
		$errorTO->identifier="var msgClassIdentifier={delloc:\"".(str_replace("'","\'",$mfS[0]["store_name"]))."\",".
																								 "delarea:\"".(str_replace("'","\'",$mfS[0]["area_description"]))."\",".
																								 "delday:\"".(str_replace("'","\'",$mfS[0]["delivery_day"]))."\"};"; // is eval() on client side. It is passed to client as '...contents...' so strip out apostrophes here
		break;
	}
	case "REMOVELINK": {
		$result=$postStoreDAO->deassociateStore($principalId, $postASSOCUID);
		if ($result->type!=FLAG_ERRORTO_SUCCESS) {
			$dbConn->dbQuery("rollback");
			print(CommonUtils::getJavaScriptMsg($result));
			return;
		}
		break;
	}
	default : {
		$errorTO->type=FLAG_ERRORTO_ERROR;
		$errorTO->description="Invalid Action Type passed";
		print(CommonUtils::getJavaScriptMsg($errorTO));
		return;
	}
}

if ($result->type!=FLAG_ERRORTO_SUCCESS) {
	$dbConn->dbQuery("rollback");
	$errorTO->type=$result->type;
	$errorTO->description="The order changes could not be saved ! <BR><BR>".$result->description;
	print(CommonUtils::getJavaScriptMsg($errorTO));
	$dbConn->dbClose();
	return;
} else {
	$dbConn->dbQuery("commit");
	$errorTO->type=$result->type;
	$errorTO->description=$result->description;
}

$dbConn->dbClose();

$errorTO->description="Change(s) successfully saved";
print(CommonUtils::getJavaScriptMsg($errorTO));
return;

?>

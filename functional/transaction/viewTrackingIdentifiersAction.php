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

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/BIDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostBIDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."TO/SmartEventTO.php");

if (isset($_GET['KEYACTION'])) $postKEYACTION = mysqli_real_escape_string($dbConn->connection, $_GET['KEYACTION']); else $postKEYACTION="";
if (isset($_GET['ACTIONTYPE'])) $postACTIONTYPE = mysqli_real_escape_string($dbConn->connection, $_GET['ACTIONTYPE']); else $postACTIONTYPE="";
if (isset($_GET['DOCMASTID'])) $postDOCMASTID = mysqli_real_escape_string($dbConn->connection, $_GET['DOCMASTID']); else $postDOCMASTID="";
if (isset($_GET['NOTIFICATIONID'])) $postNOTIFICATIONID = mysqli_real_escape_string($dbConn->connection, $_GET['NOTIFICATIONID']); else $postNOTIFICATIONID="";

$bIDAO = new BIDAO($dbConn);
$postBIDAO = new PostBIDAO($dbConn);
$transactionDAO = new TransactionDAO($dbConn);

switch ($postACTIONTYPE) {
	case "REQUEUEINVEXT": {
	  
    $reArr = $bIDAO->getNotificationRecipients($principalId,$postNOTIFICATIONID);
    if (count($reArr)==0) {
		  echo "Could not find Invoice Extraction Configuration/Notification";
		  return;
		}

		$seTO = new SmartEventTO();
		$seTO->type = SE_EXTRACT;
		$seTO->typeUid = $reArr[0]['uid'];
		$seTO->dataUid = $postDOCMASTID;

		$mfT = $transactionDAO->getSimpleDocumentByDMUId($postDOCMASTID);
		if ((count($mfT)==0) || ($principalId!=$mfT[0]["principal_uid"])) {
		  echo "Invalid Document link passed or you do not have permissions to access this document";
		  return;
		}

		$KEYACTION=md5($postDOCMASTID.$mfT[0]["principal_uid"].$postACTIONTYPE);
		if ($KEYACTION!=$postKEYACTION) {
		  echo "Invalid Document link passed or you do not have permissions to access this document";
		  return;
		}

		$rTO = $postBIDAO->postSmartEvent($seTO);
		if ($rTO->type == FLAG_ERRORTO_SUCCESS) {
		  $dbConn->dbQuery("commit");
		  echo "Document Successfully Queued for Invoice Extract";
		  return;
		} else {
		  $dbConn->dbQuery("rollback");
		  echo "Failed to Queue this document for Invoice Extract : ".$rTO->description;
		  return;
		}

		break;
	}
  
	default : {
		echo "Unknown Action Type Passed";
		return;
	}
}

$dbConn->dbClose();

?>

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
if (isset($_POST['ACTIONTYPE'])) $postACTIONTYPE = mysqli_real_escape_string($dbConn->connection, $_POST['ACTIONTYPE']); else $postACTIONTYPE="";
if (isset($_POST['UID'])) $postUID = mysqli_real_escape_string($dbConn->connection, $_POST['UID']); else $postUID="";
if (isset($_POST['VALUE'])) $postVALUE = mysqli_real_escape_string($dbConn->connection, $_POST['VALUE']); else $postVALUE="";

$adminDAO=new AdministrationDAO($dbConn);
$hasRole=$adminDAO->hasRole($userId,$principalId,ROLE_ORDERS_HOLDING_EXCEPTIONS);
if ($hasRole!==true) {
	$errorTO->type=FLAG_ERRORTO_ERROR;
	$errorTO->description="You do not have permissions to Manage Order Exceptions";
	print(CommonUtils::getJavaScriptMsg($errorTO));
	return;
}

include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
$postTransactionDAO = new PostTransactionDAO($dbConn);

switch ($postACTIONTYPE) {
	case "DD": {
		$result=$postTransactionDAO->setOrdersHoldingDeleted($postUID, $principalId);
		break;
	}
	case "DR": {
		$result=$postTransactionDAO->setOrdersHoldingDetailDeleted($postUID, $principalId);
		break;
	}
	case "SS": {
		$result=$postTransactionDAO->setOrdersHoldingStore($postUID, $principalId, $postVALUE);
		break;
	}
	case "SP": {
		$result=$postTransactionDAO->setOrdersHoldingProduct($postUID, $principalId, $postVALUE);
		break;
	}
	case "SPR": {
		$result=$postTransactionDAO->setOrdersHoldingOverridePriceType($postUID, $principalId, $postVALUE);
		break;
	}
	case "UR": {
	  $result=$postTransactionDAO->setOrdersHoldingReference($postUID, $principalId, $postVALUE);
	  break;
	}
	case "UFD": {
		$result=$postTransactionDAO->setOrdersHoldingForceUniqueFlag($postUID, $principalId, $postVALUE);
		break;
	}
	case "UAQ": {
	  if (!preg_match(GUI_PHP_INTEGER_REGEX, $postVALUE)) {
	    $errorTO->type=FLAG_ERRORTO_ERROR;
	    $errorTO->description="Invalid Amended Quantity - integer (no decimal) required";
	    print(CommonUtils::getJavaScriptMsg($errorTO));
	    return;
	  }
	  $result=$postTransactionDAO->setOrdersHoldingAmendedQuantity($postUID, $principalId, $postVALUE);
	  break;
	}
	case "UDPT": {
	  $result=$postTransactionDAO->setOrdersHoldingDepot($postUID, $principalId, $postVALUE);
	  break;
	}
  case "SOD": {
		$result=$postTransactionDAO->setOrdersHoldingOrderDate($postUID, $principalId, $postVALUE);
		break;
	}
  case "UAD": {
    $hasRole=$adminDAO->hasRole($userId,$principalId,ROLE_MAY_APPROVE_FOR_RELEASE);
    if ($hasRole!==true) {
      $errorTO->type=FLAG_ERRORTO_ERROR;
      $errorTO->description="You do not have permissions to Approve a document for processing";
      print(CommonUtils::getJavaScriptMsg($errorTO));
      return;
    }
    $result=$postTransactionDAO->setOrdersHoldingApprove($postUID, $principalId);
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
	$errorTO->description="The document changes could not be saved ! <BR><BR>".$result->description;
	print(CommonUtils::getJavaScriptMsg($errorTO));
	$dbConn->dbClose();
	return;
} else {
	$dbConn->dbQuery("commit");
	$errorTO->type=$result->type;
	$errorTO->description=$result->description;
}

$dbConn->dbClose();

$errorTO->description="Document Change(s) successfully saved";
print(CommonUtils::getJavaScriptMsg($errorTO));
return;

?>

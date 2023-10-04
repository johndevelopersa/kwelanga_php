<!DOCTYPE html>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

// NOTE : The params below are only the compulsory ones.
//        Any other parameters above may have been passed but are used in the INCLUDE_ONCE (file) below !
//		  Can be called directly without ajax hence the $_GET
if (isset($_POST["USERID"])) $postUSERID=htmlspecialchars($_POST["USERID"]);
else if (isset($_GET["USERID"])) $postUSERID=htmlspecialchars($_GET["USERID"]);
else $postUSERID="";
if (isset($_POST["SUBJECT"])) $postSUBJECT=htmlspecialchars($_POST["SUBJECT"]);
else if (isset($_GET["SUBJECT"])) $postSUBJECT=htmlspecialchars($_GET["SUBJECT"]);
else $postUSERID="";
if (isset($_POST["OBJECTID"])) $postOBJECTID=htmlspecialchars($_POST["OBJECTID"]);
else if (isset($_GET["OBJECTID"])) $postOBJECTID=htmlspecialchars($_GET["OBJECTID"]);
else $postOBJECTID="";

if (isset($_GET["OBJECTID"])) $directCall=true; else $directCall=false;

$dbConn->dbClose(); // have to close and reopen because storeCard below closes it

switch ($postOBJECTID) {
	case EO_STORE_CARD : {
	  $object="functional/stores/storeCard.php";
	  break;
  }
	case EO_PRODUCT_CARD : {
		$object="functional/products/productCard.php";
		break;
	}
	case EO_DOC_CARD : {
    $_GET['TYPE'] = 'DOCUMENT';
    $_GET['NOTOOLBAR'] = 1;
  	$object="functional/presentations/presentationHandler.php";
  	break;
  }
	case EO_ORDER_CARD : {
		$object="functional/transaction/orderCard.php";
		break;
	}
  case EO_QUOTATION_CARD : {
   $object="functional/transaction/quotationCard.php";
   break;
  }  
 case EO_JOB_CARD : {
   $object="functional/transaction/jobCard.php";
   break;
  }
 case EO_PROFORMAINV_CARD : {
   $object="functional/transaction/proformaInvoiceCard.php";
   break;
  }  
	case EO_DOC_CARD_NCRD : {
		$object="functional/transaction/documentCardNCRD.php";
		break;
	}
	case EO_DOC_CARD_NINV : {
		$object="functional/transaction/documentCardNINV.php";
		break;
	}	
	default : {
	  echo "invalid object ID";
	  return;
  }
}

// get the store card output
ob_start(); //Turn on output buffering

include_once($ROOT.$PHPFOLDER.$object);
//copy current buffer contents into variable and delete current output buffer
$htmlBody = ob_get_clean();

$returnMessages=new ErrorTO;
if ($postUSERID=="") {
	if (!$directCall) {
		$returnMessages->type=FLAG_ERRORTO_ERROR;
		$returnMessages->description="User ID not supplied.";
		print(CommonUtils::getJavaScriptMsg($returnMessages));
		return;
	} else {
		echo "User ID not supplied.";
		return;
	  }
}

$dbConn->dbConnection();

//$returnMessages=BroadcastingUtils::sendEmailHTMLUser($postUSERID,$dbConn,$htmlBody,$postSUBJECT);
$returnMessages = BroadcastingUtils::sendEmailHTMLUserEmbedded($postUSERID,$dbConn,$htmlBody,$postSUBJECT);

if (!$directCall) {
	print(CommonUtils::getJavaScriptMsg($returnMessages));
} else {
	echo $returnMessages->description;
	echo "<BR><BR>";
	echo "<INPUT type='button' class='submit' value='Back' onclick='history.back();' />";
  }

$dbConn->dbClose();
?>

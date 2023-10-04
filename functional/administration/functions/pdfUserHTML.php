<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
if (
	((!isset($_GET["KEYFROMLINK"])) && (!isset($_POST["KEYFROMLINK"]))) ||
	((isset($_GET["KEYFROMLINK"])) && ($_GET["KEYFROMLINK"]=="")) ||
	((isset($_POST["KEYFROMLINK"])) && ($_POST["KEYFROMLINK"]==""))
   ) {
   	require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
} else {
	include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php"); // needed because of access_control commented out
	include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
}
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();


// BECAUSE OF THE ACCESS CONTROL BYPASS ABOVE, THIS PAGE MUST ONLY ACT AS A "FORWARDER" AND MUST NOT CONTAIN ANY SENSITIVE DATA ITSELF DIRECTLY !!!


// NOTE : The params below are only the compulsory ones.
//        Any other parameters above may have been passed but are used in the INCLUDE_ONCE (file) below !
//		  Can be called directly without ajax hence the $_GET
if (isset($_POST["USERID"])) $postUSERID=mysql_real_escape_string(htmlspecialchars($_POST["USERID"]));
else if (isset($_GET["USERID"])) $postUSERID=mysql_real_escape_string(htmlspecialchars($_GET["USERID"]));
else $postUSERID="";
if (isset($_POST["OBJECTID"])) $postOBJECTID=mysql_real_escape_string(htmlspecialchars($_POST["OBJECTID"]));
else if (isset($_GET["OBJECTID"])) $postOBJECTID=mysql_real_escape_string(htmlspecialchars($_GET["OBJECTID"]));
else $postOBJECTID="";

if (isset($_GET["OBJECTID"])) $directCall=true; else $directCall=false;

$dbConn->dbClose(); // have to close and reopen because storeCard below closes it

switch ($postOBJECTID) {
	case EO_DOC_CARD : {
							$object="functional/transaction/documentCard.php";
							break;
						 }
	case EO_DOC_CARD_TI : {
							$object="functional/transaction/documentCardTI.php";
							break;
						 }
    case EO_DOC_CARD_CR : {
							$object="functional/transaction/documentCardCR.php";
							break;
						 }
    case EO_DOC_CARD_NCRD : {
							$object="functional/transaction/documentCardNCRD.php";
							break;
						 }
    case EO_QUOTATION_CARD : {
                            $object="functional/transaction/quotationCard.php";
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

// get the store card output;
ob_start(); //Turn on output buffering
include_once($ROOT.$PHPFOLDER.$object);
//copy current buffer contents into variable and delete current output buffer
$htmlBody = ob_get_clean();

$returnMessages=new ErrorTO;

$dbConn->dbConnection();

$htmlBody=BroadcastingUtils::sanitizePDFHTML($htmlBody);
$pdfBody=BroadcastingUtils::convertHTMLToPDF($htmlBody, "Document {$mfT[0]['document_number']}", $mfT[0]['document_number'], "Document, {$mfT[0]['document_number']}, Invoiced"); // variables are from object called
BroadcastingUtils::outputPDFWithHeaders("Retailtrading D{$mfT[0]['document_number']}.pdf", $pdfBody);

if (!$directCall) {
	print(CommonUtils::getJavaScriptMsg($returnMessages));
} else {
	echo $returnMessages->description;
	echo "<BR><BR>";
	echo "<INPUT type='button' class='submit' value='Back' onclick='history.back();' />";
  }

$dbConn->dbClose();
?>

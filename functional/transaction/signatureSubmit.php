<?php
/*
 *
 * THIS LAYER PROVIDES BASIC SCREENING AND CONVERTS INPUT INTO PROPER TO BEFORE CALL IS MADE TO PROCESS
 *
 */
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ImageTO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostTransactionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback

if (!isset($_SESSION)) session_start;
$userId = $_SESSION["user_id"];
$principalId = $_SESSION["principal_id"];

$imageTO = new ImageTO();

$imageTO->dmUId = mysql_real_escape_string($_POST["DMUID"]);

// If you want to save data that is derived from a Javascript canvas.toDataURL() function, you have to convert
// blanks into plusses. If you do not do that, the decoded data is corrupted:
$imageTO->imageData = mysql_real_escape_string(str_replace(' ','+',$_POST["IMAGEDATA"]));

$imageTO->imageType = IMAGE_TYPE_SIGNATURE;
$imageTO->uploadedByUserUId = $userId;
$imageTO->userAgentString = $_SERVER['HTTP_USER_AGENT']." IP:".$_SERVER['REMOTE_ADDR'];

$transactionDAO = new TransactionDAO($dbConn);
$postTransactionDAO = new PostTransactionDAO($dbConn);

$mfT = $transactionDAO->getDocumentWithDetailItem($userId, $principalId, $imageTO->dmUId);

if (count($mfT)==0) {
  echo "Document not found or your do not have permissions to sign this document";
  return;
}

$mfS = $transactionDAO->getDocumentImage($imageTO->dmUId, IMAGE_TYPE_SIGNATURE);

if (count($mfS)>0) {
  echo "Document has already been signed";
  return;
}

$rTO = $postTransactionDAO->postDocumentImage($imageTO);

if ($rTO->type===FLAG_ERRORTO_SUCCESS) {
  $dbConn->dbinsQuery("commit");
  echo "Successfully Saved Signature against document";
  return;
} else {

  echo "Failed to Save Signature against document : ".$rTO->description;
  return;
}


return;

?>

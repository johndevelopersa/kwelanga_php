<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/TransactionDedicatedDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

/* NOTES:
 * Some principals require the capture screen to be pre-populated with products 
 * EG. Sylko Capture an Uplift against an SOR, and they will type out the SOR WAYBILL number into
 *      the Source Document Number field (cross reference) so the screen needs to retrieve
 *      all products under the SOR
 */

$dbConn = new dbConnect();
$dbConn->dbConnection();

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$postSTOREID = (isset($_POST['STOREID'])) ? (mysql_real_escape_string(trim($_POST['STOREID']))):(false);
$postDOCTYPEID = (isset($_POST['DOCTYPE'])) ? (mysql_real_escape_string(trim($_POST['DOCTYPE']))):(false);
$postSOURCEDOCUMENTNUMBER = (isset($_POST['SOURCEDOCUMENTNUMBER'])) ? (mysql_real_escape_string(trim($_POST['SOURCEDOCUMENTNUMBER']))):(false);

$returnMessages = new ErrorTO;

$transactionDedicatedDAO = new TransactionDedicatedDAO($dbConn);

$principalSylko = "3";
if (($principalId==$principalSylko) && ($postDOCTYPEID==DT_UPLIFTS)) {
  // this is not validated against the user permissions as it should only be used to prepopulate a list
  // DT_FREEFORM_DOCTYPE_1 is an SOR transaction for Sylko
  $mfT = $transactionDedicatedDAO->getCrossReferenceDocumentDetail($principalId, $postSTOREID, DT_FREEFORM_DOCTYPE_1, $postSOURCEDOCUMENTNUMBER);  
} else {
  $returnMessages->type=FLAG_ERRORTO_ERROR;
  $returnMessages->description="Document Type is not a registered cross reference document type";
  echo CommonUtils::getJavaScriptMsg($returnMessages);
  return;
}

$uniqDocs=array();
foreach($mfT as $row) {
  $uniqDocs[$row["dm_uid"]]=$row["dm_uid"];
}
if (sizeof($uniqDocs)>1) {
  $returnMessages->type=FLAG_ERRORTO_ERROR;
  $returnMessages->description="Cross reference document type found more than one document !";
  echo CommonUtils::getJavaScriptMsg($returnMessages);
  return;
}

$returnMessages->type=FLAG_ERRORTO_SUCCESS;
$returnMessages->description="Successfully retrieved document";
$vals=array();
foreach ($mfT as $row) {
  $vals[]="{productUId:{$row["product_uid"]},orderedQty:{$row["ordered_qty"]}}";
}
$returnMessages->identifier="var detailArr=new Array();detailArr=[".implode(",",$vals)."];"; // and array of objects
echo CommonUtils::getJavaScriptMsg($returnMessages);

$dbConn->dbClose();

?>

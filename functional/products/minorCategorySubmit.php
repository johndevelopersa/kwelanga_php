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
include_once($ROOT.$PHPFOLDER.'DAO/PostProductDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingProductMinorCategoryTO.php');


if (!isset($_SESSION)) session_start;
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION['user_id'];
$returnMessages = new ErrorTO();
$returnMessages->type=FLAG_ERRORTO_ERROR; //!preset
$dbConn = new dbConnect();
$dbConn->dbConnection();


$postDMLTYPE = (isset($_POST['DMLTYPE'])) ? (trim($_POST['DMLTYPE'])) : (false);
$postPCUID = (isset($_POST['PCUID'])) ? ($_POST['PCUID']) : false;
$postPCVALUE = (isset($_POST['PCVALUE'])) ? (urldecode($_POST['PCVALUE'])) : false;
$postTYPEUID = (isset($_POST['TYPEUID'])) ? (trim($_POST['TYPEUID'])) : false;
$postSTATUS = (isset($_POST['STATUS'])) ? (trim($_POST['STATUS'])) : FLAG_STATUS_ACTIVE;

// start of superficial checks. Main checks done in adminPost.php
if(!in_array($postDMLTYPE, array("INSERT","UPDATE","DELETE"))) {
  $returnMessages->description="ERROR: Invalid DMLTYPE";
  echo CommonUtils::getJavaScriptMsg($returnMessages);
  return;
}

if(($postDMLTYPE=="UPDATE" || $postDMLTYPE=="DELETE") && (empty($postPCUID))) {
  $returnMessages->description="ERROR : Operation requires a UID";
  echo CommonUtils::getJavaScriptMsg($returnMessages);
  return;
}

$postProductDAO = new PostProductDAO($dbConn);
$postingMinorCategoryTO = new PostingProductMinorCategoryTO();
$postingMinorCategoryTO->DMLType = $postDMLTYPE;
$postingMinorCategoryTO->UId = $postPCUID;
$postingMinorCategoryTO->minorCategoryTypeUid = $postTYPEUID;
$postingMinorCategoryTO->principalUId = $principalId;
$postingMinorCategoryTO->value = $postPCVALUE;
$postingMinorCategoryTO->status = $postSTATUS;
$result = $postProductDAO->postProductMinorCategory($postingMinorCategoryTO);

if ($postDMLTYPE=="INSERT")
  $postingMinorCategoryTO->UId = $result->identifier;

if ($result->type==FLAG_ERRORTO_SUCCESS) {
  mysql_query("commit", $dbConn->connection);
  print(CommonUtils::getJavaScriptMsg($result));
  return;
} else {
  mysql_query("rollback", $dbConn->connection);

  print(CommonUtils::getJavaScriptMsg($result));
  return;
}


  print(CommonUtils::getJavaScriptMsg($returnMessages));
  return;

?>

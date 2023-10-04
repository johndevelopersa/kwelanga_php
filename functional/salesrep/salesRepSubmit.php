<?php

/*
 *
 * THIS LAYER PROVIDES BASIC SCREENING AND CONVERTS INPUT INTO PROPER TO BEFORE CALL IS MADE TO PROCESS
 *
 *
 */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/PostStoreDAO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingSalesRepTO.php");

if (!isset($_SESSION)) session_start;
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION['user_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();
$postStoreDAO = new PostStoreDAO($dbConn);


//preset post vars...
$postDMLTYPE = 'INSERT';
$postREPID = false;
$postREPCODE = '';
$postFIRSTNAME = '';
$postSURNAME = '';
$postIDENTITYNUMBER = '';
$postEMAILADDR = '';
$postMOBILENUMBER = '';
$postALTERNATECONTACTNUMBER = '';
$postSHIPTOADDRESS1 = '';
$postSHIPTOADDRESS2 = '';
$postSHIPTOADDRESS3 = '';
$postSALESTARGET = '';
$postSTATUS = FLAG_STATUS_ACTIVE;
CommonUtils::setPostVars();


#basic validation
if(!in_array($postDMLTYPE, array('INSERT','UPDATE'))){  #this screen only takes these two dml types however the postDAO takes 3...
  return CommonUtils::submitErrorTO(FLAG_ERRORTO_ERROR, "Invalid DMLType supplied!");
}
if($postDMLTYPE == 'UPDATE' && (empty($postREPID) || $postREPID == false)){
  return CommonUtils::submitErrorTO(FLAG_ERRORTO_ERROR, "Invalid ID supplied for update!");
}


$postingSalesRepTO = new PostingSalesRepTO();
$postingSalesRepTO->DMLType = $postDMLTYPE;
$postingSalesRepTO->UId = $postREPID;
$postingSalesRepTO->principalUId = $principalId;
$postingSalesRepTO->repCode = $postREPCODE;
$postingSalesRepTO->firstName = $postFIRSTNAME;
$postingSalesRepTO->surname = $postSURNAME;
$postingSalesRepTO->identityNumber = $postIDENTITYNUMBER;
$postingSalesRepTO->emailAddr = $postEMAILADDR;
$postingSalesRepTO->mobileNumber = $postMOBILENUMBER;
$postingSalesRepTO->alternateContactNumber = $postALTERNATECONTACTNUMBER;
$postingSalesRepTO->shiptoAddress1 = $postSHIPTOADDRESS1;
$postingSalesRepTO->shiptoAddress2 = $postSHIPTOADDRESS2;
$postingSalesRepTO->shiptoAddress3 = $postSHIPTOADDRESS3;
$postingSalesRepTO->salesTarget = $postSALESTARGET;
$postingSalesRepTO->status = $postSTATUS;
$returnTO = $postStoreDAO->postPrincipalSalesRep($postingSalesRepTO);


if($returnTO->type == FLAG_ERRORTO_SUCCESS){
  $result2 = mysqli_query($dbConn->connection, "commit");
  echo CommonUtils::getJavaScriptMsg($returnTO);
} else {
  $result2= mysqli_query($dbConn->connection, "rollback");
  echo CommonUtils::getJavaScriptMsg($returnTO);
}

?>
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

$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

$returnMessage;

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();


$postDMLTYPE = htmlspecialchars($_POST['DMLTYPE']);
$postPRINCIPALCHAINUID = (isset($_POST['PRINCIPALCHAINUID'])) ? (htmlspecialchars($_POST['PRINCIPALCHAINUID'])) : ("");
$postCHAINNAME = htmlspecialchars($_POST['CHAINNAME']);
$postPRINCIPALID = $_SESSION['principal_id']; // override the principalId for now
$postSTATUS = htmlspecialchars($_POST['STATUS']);
$postACGM = htmlspecialchars($_POST['ACGM']); // add chain to global master
$postOLDCODE = htmlspecialchars($_POST['OLDCODE']); // add chain to global master

// start of superficial checks. Main checks done in adminPost....php

if (($postDMLTYPE!="UPDATE") && ($postDMLTYPE!="INSERT")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if (($postDMLTYPE=="UPDATE") && ($postPRINCIPALCHAINUID=="")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="UPDATE Requires a UID";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if(($postCHAINNAME=="") || (strlen($postCHAINNAME)<5)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Chain Name not supplied or too short.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if(!preg_match(GUI_PHP_INTEGER_REGEX,$postPRINCIPALID)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Principal Id not valid.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if(!ValidationCommonUtils::checkStatus($postSTATUS)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Status is not valid.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

include_once($ROOT.$PHPFOLDER."TO/PostingPrincipalChainTO.php");
$postingPrincipalChainTO = new PostingPrincipalChainTO;
$postingPrincipalChainTO->DMLType=$postDMLTYPE;
$postingPrincipalChainTO->chainName=trim($postCHAINNAME);
$postingPrincipalChainTO->principalId=$postPRINCIPALID;
$postingPrincipalChainTO->status=$postSTATUS;
$postingPrincipalChainTO->principalChainUId=$postPRINCIPALCHAINUID;
$postingPrincipalChainTO->oldCode=$postOLDCODE;

// Do the Actual Posting of principal chain
include_once($ROOT.$PHPFOLDER."DAO/PostChainDAO.php");
$postChainDAO = new PostChainDAO($dbConn);
$result=$postChainDAO->postPrincipalChain($postingPrincipalChainTO);
$seqPS = $result->identifier;

if ($result->type==FLAG_ERRORTO_SUCCESS) {

	// add the CUSTOM principal-CHAIN-fields : START
	include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
	include_once($ROOT.$PHPFOLDER."DAO/PostMiscellaneousDAO.php");
	include_once($ROOT.$PHPFOLDER."TO/PostingSpecialFieldTO.php");

	$postingSpecialFieldTO = new PostingSpecialFieldTO;
	$postingSpecialFieldTO->DMLType =$postDMLTYPE;
	$miscDAO = new MiscellaneousDAO($dbConn);
	$postMiscDAO = new PostMiscellaneousDAO($dbConn);
	$mfFlds=$miscDAO->getPrincipalSpecialFields($postingPrincipalChainTO->principalId, "C");


	$specialFieldAllow = 0;  //value to change behaviour of description of special fields.

	foreach ( $mfFlds as $smpfLine ) {

         $postingSpecialFieldTO->principal = $postingPrincipalChainTO->principalId;
		 $postingSpecialFieldTO->deliverName = $postingPrincipalChainTO->chainName;
     	 $postingSpecialFieldTO->fielduid = $smpfLine['uid'];

     	 $postingSpecialFieldTO->value = (isset($_POST[str_replace(' ','',$smpfLine['name'])])) ? ($_POST[str_replace(' ','',$smpfLine['name'])]) : ("");

     	 //multiple field values
     	 $multiValue = explode('#,#', $postingSpecialFieldTO->value);

     	 $postingSpecialFieldTO->value = array();
     	 foreach($multiValue as $value){
     	   $postingSpecialFieldTO->value[] = $value;
     	 }

     	 //if all the values of the special fields are empty the array will be empty.
     	 if(count($postingSpecialFieldTO->value)>0){

         	 if ($postDMLTYPE=="INSERT"){
         	   $postingSpecialFieldTO->entityUId = $seqPS;
         	   $postingSpecialFieldTO->editable = 'Y';
         	 } else {
         	   $postingSpecialFieldTO->entityUId = $postPRINCIPALCHAINUID;
         	   $postingSpecialFieldTO->editable = $smpfLine['editable'];
         	 }

         	 if($postingSpecialFieldTO->editable=='Y'){
             	 $Smpdresult = $postMiscDAO->postSpecialField($postingSpecialFieldTO);

             	if ($Smpdresult->type != FLAG_ERRORTO_SUCCESS) {
        		  $result3=mysqli_query($dbConn->connection, "rollback");

        		  $result->type=FLAG_ERRORTO_ERROR;  // cancel the description of 1st and display error.
        		  $result->description = "The Store could not be added/updated because the Special Field update/insert failed.<BR><BR>".$Smpdresult->description;
        		  break;
        		} else {
        		  $specialFieldAllow++;
        		}
         	 }
     	 }

    }
	if($result->type==FLAG_ERRORTO_SUCCESS && $specialFieldAllow>0){
	  $result->description .= '<br>Special Fields Successfully updated/inserted.<BR><BR>';
	}
	//Custom fields : END


	// add to global store too
	if ($postACGM=="Y") {
		include_once($ROOT.$PHPFOLDER."TO/PostingGlobalChainTO.php");
		$postingGlobalChainTO = new PostingGlobalChainTO;
		$postingGlobalChainTO->DMLType="INSERT";
		$postingGlobalChainTO->chainName=trim($postCHAINNAME);
		$postingGlobalChainTO->status=$postSTATUS;

		// Do the Actual Posting of global chain
		$result2=$postChainDAO->postGlobalChain($postingGlobalChainTO);

		if ($result2->type==FLAG_ERRORTO_SUCCESS) {
			$result3=mysqli_query($dbConn->connection, "commit");
			$result->description .= "<BR>".$result2->description; // join result to first
		} else {
			$result3=mysql_query("rollback", $dbConn->connection);
			// cancel the description of 1st and display error.
			$result->description="The Store could not be added because the Global Store Validation failed when trying to add the Global Chain.<BR><BR>If you would like to store the principal-chain only, untick the global checkbox at bottom.<BR><BR>";
			$result->description .= $result2->description;
		  }
	} else {
		mysqli_query($dbConn->connection, "commit");
	  }
} else $result3=mysqli_query($dbConn->connection, "rollback");

// automatically add the chain to the user who created the above chain.
if (($result->type==FLAG_ERRORTO_SUCCESS) && ($postDMLTYPE=="INSERT")) {
	include_once($ROOT.$PHPFOLDER."TO/PostingUserChainTO.php");
	$postingUserChainTO = new PostingUserChainTO;
	$postingUserChainTO->DMLType=$postDMLTYPE;
	$postingUserChainTO->userId=$userId;
	$postingUserChainTO->principalChainUId=$seqPS;

	// Do the Actual Posting
	include_once($ROOT.$PHPFOLDER."DAO/PostAdminUserDAO.php");
	$postAdminUserDAO = new PostAdminUserDAO($dbConn);
	$result2=$postAdminUserDAO->postUserChain($postingUserChainTO,$userId);
	$result->description .= "<BR>".$result2->description; // join result to first
	// ignore the error status as only the creation of store is important part
	$result2=mysqli_query($dbConn->connection, "commit");
}

//$dbConn->dbFree();
$dbConn->dbClose();

// check return values
if (sizeof($result)> 0) {
	print(CommonUtils::getJavaScriptMsg($result));
	return;
} else if (sizeof($result)== 0) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Problem occurred during posting : Post returned 0 arraysize. Please inform RetailTrading Management.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
  }

print(CommonUtils::getJavaScriptMsg($result));
return;

?>

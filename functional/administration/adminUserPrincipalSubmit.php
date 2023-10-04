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
include_once($ROOT.$PHPFOLDER."DAO/PostAdminUserDAO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingUserPrincipalDepotTO.php");


if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;


$returnMessages=new ErrorTO;
//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();


$postDMLTYPE = htmlspecialchars($_POST['DMLTYPE']);
$postUSERID = (isset($_POST['USERID']) && $_POST['USERID'] != '') ? ($_POST['USERID']) : false;
$postPRINCIPALID = (isset($_POST['PRINCIPALID']) && $_POST['PRINCIPALID'] != '') ? ($_POST['PRINCIPALID']) : false;
if (isset($_POST['DEPOTID'])) $postDEPOTID = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DEPOTID'])); else $postDEPOTID="";


// start of superficial checks. Main checks done in adminPost...php
if (($postDMLTYPE!="DELETE") && ($postDMLTYPE!="INSERT")) {
  $returnMessages->type=FLAG_ERRORTO_ERROR;
  $returnMessages->description="Invalid Processing Type";
  print(CommonUtils::getJavaScriptMsg($returnMessages));
  return;
};

if(!$postPRINCIPALID || !$postUSERID || ($postDEPOTID=="")) {
  $returnMessages->type=FLAG_ERRORTO_ERROR;
  $returnMessages->description="Invalid User, or Depot ID type - or fields not supplied! Please make sure you have filled in all required fields.";
  print(CommonUtils::getJavaScriptMsg($returnMessages));
  return;
};


$postAdminUserDAO = new PostAdminUserDAO($dbConn);
$postingUserPrincipalDepotTO = new PostingUserPrincipalDepotTO;


  $postingUserPrincipalDepotTO->DMLType = $postDMLTYPE;
  $postingUserPrincipalDepotTO->userId = $postUSERID;
  $postingUserPrincipalDepotTO->principalId = $postPRINCIPALID;

  $explodeDepot = explode(",",$postDEPOTID);
  $resType = FLAG_ERRORTO_INFO;
  $resDesc="The results of the principal-depot additions :<BR><BR>";
  if (sizeof($explodeDepot)==0) {
  	$resType = FLAG_ERRORTO_ERROR;
  	$resDesc .= "NO Principal-Depots Selected !<BR><BR>";
  }
  $i=0;
  foreach ($explodeDepot as $id) {
  	$i++;
  	$postingUserPrincipalDepotTO->depotId = $id;
  	// Do the Actual Posting
  	$result=$postAdminUserDAO->postUserPrincipalDepot($postingUserPrincipalDepotTO,$userId);

  	if ($result->type==FLAG_ERRORTO_SUCCESS) $result2=mysqli_query($dbConn->connection, "commit");

  	if ($i>15) {
  		//
  	} else {
  		$resDesc.=$result->type." - "."Depot ".$postingUserPrincipalDepotTO->depotId." - ".$result->description."<BR>";
  		if ($i>=15) $resDesc.="<BR>...*list shortened...*";
  	}
  }

  $result->type=$resType;
  $result->description=$resDesc;
  $result->identifier="";



$dbConn->dbClose();

// check return values
if (count($result)> 0) {
	print(CommonUtils::getJavaScriptMsg($result));
	return;
} else if (sizeof($result)== 0) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Problem occurred during posting : Post returned 0 arraysize. Please inform Kwelanga Management.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
  }

print(CommonUtils::getJavaScriptMsg($result));
return;

?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/StockDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");


if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

$postPRODUCTID = isset($_POST['PRODUCTID']) ? (htmlspecialchars($_POST['PRODUCTID'])) : false;
$postDEPOTID = isset($_POST['DEPOTID']) ? (htmlspecialchars($_POST['DEPOTID'])) : false;

// all stock permissions are in the function call

$returnMessages = new ErrorTO;


if (($postPRODUCTID===false) || ($postDEPOTID===false)) {
  $returnMessages->type=FLAG_ERRORTO_SUCCESS;
  $returnMessages->description="Value Pass Error.";
  $returnMessages->identifier="Value Pass Error.";
  print(CommonUtils::getJavaScriptMsg($returnMessages));
  return;
}


//SET THE ID2 => PRODUCT UID TO RETURN TO SCREEN, CROSSED AJAX FIX
$returnMessages->identifier2 = $postPRODUCTID;

//logic spilt by : onyx @ 2013-01-10
//WE NOW RETURN
//  1.  NO PERMISSION
//  2.  STOCK VALUE
//  3.  ZERO STOCK VALUE

//PERMISSIONS
//PRODUCT
$administrationDAO = new AdministrationDAO($dbConn);
if (!$administrationDAO->hasRole($userId, $principalId, ROLE_BYPASS_USER_PRODUCT_RESTRICTION)){ //bypass role
  if(!$administrationDAO->hasProduct($userId, $postPRODUCTID, $principalAliasId)){ //allocated to user
    //no permisions.
    $returnMessages->type=FLAG_ERRORTO_ERROR;
    $returnMessages->description="No Permissions";
    $returnMessages->identifier="";
    echo CommonUtils::getJavaScriptMsg($returnMessages);
    return;
  }
}
//DEPOT
if (!$administrationDAO->hasDepot($userId, $postDEPOTID, $principalAliasId)){
    //no permisions.
    $returnMessages->type=FLAG_ERRORTO_ERROR;
    $returnMessages->description="No Permissions";
    $returnMessages->identifier="";
    echo CommonUtils::getJavaScriptMsg($returnMessages);
    return;
}

//GET STOCK
$stockDAO=new StockDAO($dbConn);
$mfS=$stockDAO->getUserPrincipalProductStock($userId, $principalAliasId, $postPRODUCTID, $postDEPOTID);
if (sizeof($mfS)==0) {
  //this is no longer for permissions - this is only no stock ie: zero value as the above separate checks do the permission checking.
  $returnMessages->type=FLAG_ERRORTO_SUCCESS;
  $returnMessages->description="";
  $returnMessages->identifier=0;
  print(CommonUtils::getJavaScriptMsg($returnMessages));
  return;
}

$returnMessages->type=FLAG_ERRORTO_SUCCESS;
$returnMessages->description="Successfully Retrieved Stock.";
$returnMessages->identifier=$mfS[0]['available']; // available stock
print(CommonUtils::getJavaScriptMsg($returnMessages));

$dbConn->dbClose();
?>

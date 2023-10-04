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
include_once($ROOT.$PHPFOLDER.'DAO/PostDepotDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDepotTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDepotDeliveryCalendarTO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');


#session and db.
if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];
$principalId  = $_SESSION['principal_id'];
$dbConn = new dbConnect();
$dbConn->dbConnection();
$postDepotDAO = new PostDepotDAO($dbConn);


#values
$postDMLTYPE = false;
$postDDUID = '';
$postSETDAY = false;
$postDEPOTID = false;
$postDAY = '';
$postMONTH = '';
$postYEAR = '';
$postCOMMENT = '';
CommonUtils::setPostVars();


#validation
if(!in_array($postDMLTYPE, array('INSERT','UPDATE'))){  #this screen only takes these two dml types however the postDAO takes 3...
  return CommonUtils::submitErrorTO(FLAG_ERRORTO_ERROR, "Invalid DMLType supplied!");
}
if($postDMLTYPE == 'UPDATE' && empty($postDDUID)){
  return CommonUtils::submitErrorTO(FLAG_ERRORTO_ERROR, "Invalid ID supplied for update!");
}
if(!in_array($postSETDAY, array('DD','ND'))){
  return CommonUtils::submitErrorTO(FLAG_ERRORTO_ERROR, "Invalid Day Type supplied!");
}
if($postDEPOTID===false){
  return CommonUtils::submitErrorTO(FLAG_ERRORTO_ERROR, "Invalid DepotID supplied!");
}
if(empty($postMONTH)||empty($postDAY)||empty($postYEAR)){
  return CommonUtils::submitErrorTO(FLAG_ERRORTO_ERROR, 'Invalid Day/Month/Year supplied!');
}
if(checkdate($postMONTH, $postDAY, $postYEAR)!==true){
  return CommonUtils::submitErrorTO(FLAG_ERRORTO_ERROR, 'Invalid Date supplied!');
}
if($postSETDAY == 'ND' && strlen($postCOMMENT)<8){
  return CommonUtils::submitErrorTO(FLAG_ERRORTO_ERROR, 'Please type in a comment for this Non Delivery Day! (Requires 8 characters)');
}

$postingDepotDeliveryCalendarTO = new  PostingDepotDeliveryCalendarTO();
$postingDepotDeliveryCalendarTO->depotUId = $postDEPOTID;
$postingDepotDeliveryCalendarTO->timestamp = mktime(0,0,0,$postMONTH,$postDAY,$postYEAR);
$postingDepotDeliveryCalendarTO->type = 1;
$postingDepotDeliveryCalendarTO->comment = trim($postCOMMENT);
$postingDepotDeliveryCalendarTO->createdByUserUId = $userId;
$postingDepotDeliveryCalendarTO->calendarDate = $postYEAR."-".$postMONTH."-".$postDAY;


#db post
$returnTO = false;
if($postDMLTYPE == 'INSERT'){

  if($postSETDAY == 'DD'){

    #delivery day and insert dml, so doesn't exist in db - no update or insert required
    return CommonUtils::submitErrorTO(FLAG_ERRORTO_SUCCESS, 'Successfully updated Delivery Day! (1)', $postSETDAY, $postingDepotDeliveryCalendarTO->timestamp);

  } else if($postSETDAY == 'ND'){

    #delete the row
    $postingDepotDeliveryCalendarTO->DMLType = 'INSERT';
    $returnTO = $postDepotDAO->postDepotDeliveryCalendar($postingDepotDeliveryCalendarTO);
  }

} else if ($postDMLTYPE == 'UPDATE'){

  if($postSETDAY == 'DD'){

    #non delivery day and update, row exists in db for us to delete.
    $postingDepotDeliveryCalendarTO->UId = $postDDUID;
    $postingDepotDeliveryCalendarTO->DMLType = 'DELETE';
    $returnTO = $postDepotDAO->postDepotDeliveryCalendar($postingDepotDeliveryCalendarTO);

  } else if($postSETDAY == 'ND'){

    #non delivery day and update, row exists in db for us to UPDATE.
    $postingDepotDeliveryCalendarTO->UId = $postDDUID;
    $postingDepotDeliveryCalendarTO->DMLType = 'UPDATE';
    $returnTO = $postDepotDAO->postDepotDeliveryCalendar($postingDepotDeliveryCalendarTO);

  }
}
if($returnTO==false){
  return CommonUtils::submitErrorTO(FLAG_ERRORTO_ERROR, 'Error updating Delivery Day!');
}

if($returnTO->type == FLAG_ERRORTO_SUCCESS){
  $result2 = mysql_query("commit", $dbConn->connection);
  $returnTO->identifier = $postSETDAY;
  $returnTO->identifier2 = $postingDepotDeliveryCalendarTO->timestamp;
  print(CommonUtils::getJavaScriptMsg($returnTO));
} else {
  $result2=mysql_query("rollback", $dbConn->connection);
  print(CommonUtils::getJavaScriptMsg($returnTO));
}


?>
<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostBIDAO.php');


$dbConn = new dbConnect();
$dbConn->dbConnection();
$postBIDAO = new PostBIDAO($dbConn);
$errorTO = new ErrorTO();
$errorTO->type = FLAG_ERRORTO_ERROR;  //Preset!
$postACTION = false;  //preset expected vars...
$postSMARTID = false;
CommonUtils::setPostVars(); //magic function


if(empty($postACTION)){
  $errorTO->description = "Invalid Action!";
  echo CommonUtils::getJavaScriptMs($errorTO);
  return;
}
if(empty($postSMARTID)){
  $errorTO->description = "Invalid Smart Event!";
  echo CommonUtils::getJavaScriptMsg($errorTO);
  return;
}


if($postACTION == 'CLEAR'){

  $result = $postBIDAO->setSmartEventStatus($postSMARTID, $general1 = "CLEARED ERROR");
  if($result->type == FLAG_ERRORTO_SUCCESS){
    $dbConn->dbQuery("commit");
    $errorTO->type = FLAG_ERRORTO_SUCCESS;  //Preset!
    $errorTO->description = "Successfully cleared!";
    echo CommonUtils::getJavaScriptMsg($errorTO);
    return;
  } else {
    $dbConn->dbQuery("rollback");
    $errorTO->description = 'ERROR: ' . $result->description;
    echo CommonUtils::getJavaScriptMsg($errorTO);
    return;
  }

}



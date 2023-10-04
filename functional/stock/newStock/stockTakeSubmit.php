<?php

/*
 *
 * THIS LAYER PROVIDES BASIC SCREENING AND CONVERTS INPUT INTO PROPER TO BEFORE CALL IS MADE TO PROCESS
 *
 * HANDLES MULTIPLE ACTIONS from stock take.
 *
 */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/StockDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostStockDAO.php");


if (!isset($_SESSION)) session_start;
$principalId = $_SESSION['principal_id'];
$depotId = $_SESSION['depot_id'];
$userId = $_SESSION['user_id'];


$dbConn = new dbConnect();
$dbConn->dbConnection();
$errorTO = new ErrorTO();
$errorTO->type = FLAG_ERRORTO_ERROR;  //Preset!


//preset expected vars...
$postACTION = false;
$postPRINCIPALID = false;
$postDEPOTID = false;
$postCATEGORIES = false;
$postSWITCH = 1;
$postPRODCOUNT = array();
CommonUtils::setPostVars(); //magic function



/*-------------------------------------------
 *          OPERATION VALIDATION
 *-------------------------------------------*/

//user is depot user
if(!CommonUtils::isDepotUser()){
  $errorTO->description = "User type is not allowed to preform this action!";
  echo CommonUtils::getJavaScriptMsg($errorTO);
  return;
}

//compare session vars to supplied
if($postPRINCIPALID != $principalId){
  $errorTO->description = "Principal Id differs from Supplied Id!";
  echo CommonUtils::getJavaScriptMsg($errorTO);
  return;
}
if($postDEPOTID != $depotId){
  $errorTO->description = "Depot Id differs from Supplied Id!";
  echo CommonUtils::getJavaScriptMsg($errorTO);
  return;
}


/*-------------------------------------------
 *                OPERATIONS
 *-------------------------------------------*/

switch ($postACTION) {


  //TURNS STOCK TAKE MODE -> ON .or. OFF
  case 'MODE':

    $postStockDAO = new PostStockDAO($dbConn);
    $result = $postStockDAO->postStockMode($postDEPOTID, $postPRINCIPALID, $userId, $postSWITCH);

    if($result->type == FLAG_ERRORTO_SUCCESS){
      $dbConn->dbQuery("commit");
      $errorTO->type = FLAG_ERRORTO_SUCCESS;
      if($postSWITCH==1){
        $errorTO->description = "Depot - Principal transactions successfully frozen!";
      } else {
        $errorTO->description = "Depot - Principal transactions successfully resumed!";
      }
      echo CommonUtils::getJavaScriptMsg($errorTO);
    } else {
      $dbConn->dbQuery("rollback");
      echo CommonUtils::getJavaScriptMsg($result);
    }

    break;

  //COMPARES COUNT - returns json array of variances
  case 'COUNT':


    $stockDAO = new StockDAO($dbConn);
    $categoryUIds = [];

    // build up the list of category uids
    foreach (json_decode(urldecode($postCATEGORIES), true) as $key => $value) array_push($categoryUIds, $value['uid']);
    
    // get stock count for either 'ALL' products or under categories
    $categoryUIdsString = implode(",", array_map('intval', $categoryUIds));
    $stockArr = $stockDAO->getStockCountProducts($postDEPOTID, $postPRINCIPALID, $categoryUIdsString);

    //submission of the above...
    if(count($postPRODCOUNT)==0){
      $errorTO->description = "Empty/Invalid Product values!";
      echo CommonUtils::getJavaScriptMsg($errorTO);
      return;
    }

    if(count($postPRODCOUNT) != count($stockArr)){
      $errorTO->description = "Screen supplied list of products do not match stock list!";
      echo CommonUtils::getJavaScriptMsg($errorTO);
      return;
    }

    $returnArr = array(); //variance product list
    $variancesExist = false;

    foreach($stockArr as $st){


      $productId = $st['product_uid'];

      if(!isset($postPRODCOUNT[$productId])){
        $varProductArr[] = $st['product_uid'];  //error for this product uid.
        continue;
      }
      $suppliedCount = htmlentities(trim($postPRODCOUNT[$productId]));

      if(!is_numeric($suppliedCount) ||
         ((int)$suppliedCount!=$suppliedCount) ||
         ((int)abs($suppliedCount) != $st['closing'])){  //validation against number...

        $diff = (int)abs($suppliedCount) - $st['closing'];
        $variancesExist = true;
        $returnArr[$productId]['p'] = $suppliedCount; //on product uid
        $returnArr[$productId]['v'] = $diff;  //variance amount
        $returnArr[$productId]['s'] = $st['closing']; //system
        continue;

      } else {
        $returnArr[$productId]['p'] = $suppliedCount;
        $returnArr[$productId]['v'] = "N";
        $returnArr[$productId]['s'] = "0";
      }

    }

    if(!$variancesExist){
      $errorTO->type = FLAG_ERRORTO_SUCCESS;
      $errorTO->description = "No variances have been found in your count!";
      echo CommonUtils::getJavaScriptMsg($errorTO);
      return;
    } else {
      $errorTO->type = FLAG_ERRORTO_ERROR;
      $errorTO->description = "Some variances have been found in your count!";
      //$errorTO->identifier2 = buildProductList($stockArr, $postPRODCOUNT, $varProductArr);
      $errorTO->identifier2 = json_encode($returnArr);
      // print_r($errorTO->description);
      echo CommonUtils::getJavaScriptMsg($errorTO);
      // return;
    }

    break;


  case 'ROLLOVER':  //ROLLOVER STOCK COUNT!

      $postStockDAO = new PostStockDAO($dbConn);
      $categoryUIds = [];

      // build up the list of category uids
      foreach (json_decode(urldecode($postCATEGORIES), true) as $key => $value) array_push($categoryUIds, $value['uid']);
      
      // get stock count for either 'ALL' products or under categories
      $categoryUIdsString = implode(",", array_map('intval', $categoryUIds));
      $result = $postStockDAO->rolloverStock($depotId, $principalId, $userId, $categoryUIdsString);

      if($result->type == FLAG_ERRORTO_SUCCESS){
        $dbConn->dbQuery("commit");
        $errorTO->type = FLAG_ERRORTO_SUCCESS;
        $errorTO->description = "Stock has been successfully rolled over and previous stock figures saved to snapshot log";
        echo CommonUtils::getJavaScriptMsg($errorTO);
      } else {
        $dbConn->dbQuery("rollback");
        echo CommonUtils::getJavaScriptMsg($result);
      }


    break;


  default:

    $errorTO->description = "Invalid Action Supplied";
    echo CommonUtils::getJavaScriptMsg($errorTO);

    break;

}

?>
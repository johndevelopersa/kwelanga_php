<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/ImportDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

/*
 *
 *  CHECK IF ORDER NUMBER IS UNIQUE
 *
 *  returns: success = is unique; failure = duplicate exists.
 *
 */


//vars
if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$postDOCTYPEID = (isset($_POST['DOCTYPE'])) ? (trim($_POST['DOCTYPE'])):(false);
$postSTOREID = (isset($_POST['STOREID'])) ? (trim($_POST['STOREID'])):(false);
$postCUSTREF = (isset($_POST['CUSTREF'])) ? (trim($_POST['CUSTREF'])):(false);
$postORDERUNIQUE = (isset($_POST['ORDERUNIQUE'])) ? (trim($_POST['ORDERUNIQUE'])):('N');
$returnMessages = new ErrorTO;
$returnMessages->identifier2 = $postCUSTREF;  //SET THE ID2 => CUST REF TO RETURN TO SCREEN, FOR CROSSED AJAX CALLS


if(($postDOCTYPEID!=false) && ($postSTOREID!=false) && ($postCUSTREF!=false) && (trim($postCUSTREF)!="")){


  //db
  $dbConn = new dbConnect();
  $dbConn->dbConnection();

  //dao
  $adminDAO = new AdministrationDAO($dbConn);
  $importDAO = new ImportDAO($dbConn);
  $storeDAO = new StoreDAO($dbConn);


  //get store - depot
  $storeArr = $storeDAO->getPrincipalStoreItem($postSTOREID);
  $depotUid = $storeArr[0]['depot_uid'];

  //preset.
  $returnMessages->type = FLAG_ERRORTO_SUCCESS;
  $returnMessages->description = "Order Number is OK.";

  if ($postORDERUNIQUE=="Y") {

    $mfDUPO = $importDAO->getOrdersByON($principalAliasId, $postCUSTREF, $depotUid, $postDOCTYPEID);
    if (sizeof($mfDUPO)>0) {
      $returnMessages->type=FLAG_ERRORTO_ERROR;
      $returnMessages->description="Principal has been configured for unique order references. Order Reference is not unique in Orders.";
    }

    $mfDUPD = $importDAO->getDocumentsByON($principalAliasId, $postCUSTREF, $depotUid, ($postDOCTYPEID==DT_ORDINV_ZERO_PRICE)?DT_ORDINV:$postDOCTYPEID); // clipper converts it
    if (sizeof($mfDUPD)>0){
      $returnMessages->type=FLAG_ERRORTO_ERROR;
      $returnMessages->description="Principal has been configured for unique order references. Order Reference is not unique in Tracking Transaction.";
    }

  } else {

    // use generic : user cannot submit same product within last 5 mins, for themselves (prevents double submits)
    $dbConn->dbQuery("SET time_zone='+0:00'");

    $sql="select order_number, order_sequence_no, '>' date_group
            from orders a
            where principal_uid = ".$principalAliasId."
            and   storechain_uid = ".$postSTOREID."
            and   (a.order_number = '{$postCUSTREF}' and a.order_number is not null and a.order_number!='')
            and   a.document_type = {$postDOCTYPEID}
            and   a.capturedate >= DATE_SUB(now(),INTERVAL 5*60 SECOND)
            and   a.captureuser_uid = '{$userId}'";

    $dbConn->dbinsQuery($sql);

    if (!$dbConn->dbQueryResult) {
      $returnMessages->type=FLAG_ERRORTO_ERROR;
      $returnMessages->description="Error occurred in duplicate check SQL";
    }

    if ($dbConn->dbQueryResultRows > 0) {

      $returnMessages->type=FLAG_ERRORTO_ERROR;
      $returnMessages->description="Order Number is not unique.";

      while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
        // check critical duplicate double submit for any in loop
        if ($row["date_group"]==">") {
          $returnMessages->type=FLAG_ERRORTO_ERROR;
          $returnMessages->description="This Order has already been captured for this reference for this principal-store within the last 5 minutes.";
        }
      }
    }

  } // end screen check double submit

  if ($returnMessages->type==FLAG_ERRORTO_ERROR) {
  	$returnMessages->type=FLAG_ERRORTO_WARNING;
  	$returnMessages->description="WARNING: Pre-validations check on unique customer reference has found a duplication.<br><hr><br>".
  								$returnMessages->description.
  								"<br><br>You may continue, but this order may be blocked on submit depending on your principal settings.";
  }
  echo CommonUtils::getJavaScriptMsg($returnMessages);

  $dbConn->dbClose();

}


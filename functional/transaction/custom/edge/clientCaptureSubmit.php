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
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/StockDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/DepotDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostDistributionDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');



if (!isset($_SESSION)) session_start;
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION['user_id'];
$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback
$returnMessage = new ErrorTO;
$dbConn = new dbConnect();
$dbConn->dbConnection();
$productDAO = new ProductDAO($dbConn);
$stockDAO = new StockDAO($dbConn);
$principalDAO = new PrincipalDAO($dbConn);
$adminDAO = new AdministrationDAO($dbConn);
$depotDAO = new DepotDAO($dbConn);
$postDistributionDAO = new PostDistributionDAO($dbConn);


$postDEPOT = '';
$postDELINSTR = '';
$postDELPOINT = '';
$postDELPCODE = '';
$postDELCONTACT = '';
$postDELCONTACTNO = '';
$postSERVICE = '';
$postPRODUCTID = array();
$postQTY = array();
CommonUtils::setPostVars();



//FULL VALIDATION FOR CUSTOM EDGE CAPTURE SCREEN.
if($postDEPOT==''){
  $returnMessage->type=FLAG_ERRORTO_ERROR;
  $returnMessage->description="Please select a depot!";
  echo CommonUtils::getJavaScriptMsg($returnMessage);
  return;
}
if(strlen($postDELPOINT)<8){
  $returnMessage->type=FLAG_ERRORTO_ERROR;
  $returnMessage->description="Please specify a delivery point, miniumn 8 characters";
  echo CommonUtils::getJavaScriptMsg($returnMessage);
  return;
}
if($postDEPOT==''){
  $returnMessage->type=FLAG_ERRORTO_ERROR;
  $returnMessage->description="Please select a depot!";
  echo CommonUtils::getJavaScriptMsg($returnMessage);
  return;
}
if(count($postPRODUCTID)==0){
  $returnMessage->type=FLAG_ERRORTO_ERROR;
  $returnMessage->description="Please select a product!";
  echo CommonUtils::getJavaScriptMsg($returnMessage);
  return;
}
foreach($postQTY as $k=>$q){
  if(!is_numeric($q)){
    $returnMessage->type=FLAG_ERRORTO_ERROR;
    $returnMessage->description="Invalid quantity specify!";
    echo CommonUtils::getJavaScriptMsg($returnMessage);
    return;
  }
  if(!$q > 0){
    $returnMessage->type=FLAG_ERRORTO_ERROR;
    $returnMessage->description="Please select a Quantity!";
    echo CommonUtils::getJavaScriptMsg($returnMessage);
    return;
  }
}


$dArr = $depotDAO->getDepotItem($postDEPOT);
$prinArr = $principalDAO->getPrincipalItem($principalId);
$uArr = $adminDAO->getUserItem($userId);

if(!isset($dArr[$postDEPOT]['depot_email_list'])){
  $returnMessage->type=FLAG_ERRORTO_ERROR;
  $returnMessage->description="Error - Invalid Depot Supplied!";
  echo CommonUtils::getJavaScriptMsg($returnMessage);
  return;
}

$depotEmailArr = explode(';',$dArr[$postDEPOT]['depot_email_list']);

if(count($depotEmailArr)==0){
  $returnMessage->type=FLAG_ERRORTO_ERROR;
  $returnMessage->description="No depot email contacts setup, please contact the depot!";
  echo CommonUtils::getJavaScriptMsg($returnMessage);
  return;
}


$htmlProductList = '';

foreach($postPRODUCTID as $k => $pUId){

  $proQTY = $postQTY[$k];
  $pArr = $productDAO->getPrincipalProductItem($principalId, $pUId);
  $sArr = $stockDAO->getUserPrincipalProductStock($userId, $principalId, $pUId, $postDEPOT);

  if(!isset($sArr[0]['available'])){
    $returnMessage->type=FLAG_ERRORTO_ERROR;
    $returnMessage->description="You do not have access to a product code : " . $pArr[0]['product_code'] . ' or there is no stock in the selected depot!';
    echo CommonUtils::getJavaScriptMsg($returnMessage);
    return;
  } else {

    if($proQTY > $sArr[0]['available']){
      $returnMessage->type=FLAG_ERRORTO_ERROR;
      $returnMessage->description = "Quantity exceeds stock available for product code : " . $pArr[0]['product_code'] . '!';
      echo CommonUtils::getJavaScriptMsg($returnMessage);
      return;
    }
  }

  $proDesc = (strlen($pArr[0]['product_description'])>30)?(substr($pArr[0]['product_description'],0,27).'...'):($pArr[0]['product_description']);
  $htmlProductList .= str_pad($pArr[0]['product_code'],30," ", STR_PAD_RIGHT) . " | " .
                      str_pad($proDesc, 30, " ", STR_PAD_RIGHT) . " | " .
                       $proQTY . "\n";


}



//validation passed! - get seq

include_once($ROOT.$PHPFOLDER."TO/SequenceTO.php");
include_once($ROOT.$PHPFOLDER."DAO/SequenceDAO.php");
$getSequenceResult = false;
$sequenceTO = new SequenceTO();
$sequenceTO->sequenceKey    = "EDGECLIENT";
$sequenceTO->sequenceStart  = 0;
$sequenceTO->sequenceLen    = 6;
$sequenceDAO = new SequenceDAO($dbConn);
$seqResult = $sequenceDAO->getSequence($sequenceTO, $getSequenceResult);


if($seqResult->type != FLAG_ERRORTO_SUCCESS || $getSequenceResult === false){
  $returnMessage->type=FLAG_ERRORTO_ERROR;
  $returnMessage->description="Error in Sequence Control; invalid key or empty value result!";
  echo CommonUtils::getJavaScriptMsg($returnMessage);
  return;
}

//BODY OUTPUT
$htmlOUT  = '<html><head></head><body style="font-size:10px;">';
$htmlOUT .= '<PRE><h2>Edge Client Order</h2>';
$htmlOUT .= '<hr>';

$htmlOUT .= '<h3>Company Information</h3>';
$htmlOUT .= "Company : \t\t" . $prinArr[0]['principal_name'] ." (". $prinArr[0]['uid'] . ")\n" .
            "User : \t\t\t" . $uArr[0]['full_name'] . ' (' . $uArr[0]['uid']  . ")\n" .
            "Office No. : \t\t" . $uArr[0]['user_tel']  . "\n".
            "Mobile No. : \t\t" .$uArr[0]['user_cell'] . "\n" .
            "E-mail : \t\t" .$uArr[0]['user_email'] . "\n\n";

$htmlOUT .= '<h3>Order Information</h3>';
$htmlOUT .= "From Depot : \t\t".$dArr[$postDEPOT]['depot_name'] . ' (' . $dArr[$postDEPOT]['uid'] . ")\n" .
            "Delivery Point : \t" . implode(', ', explode("\r\n",$postDELPOINT)) . "\n" .
            "Postal Code : \t\t" . $postDELPCODE . "\n" .
            "Delivery Instr. : \t". $postDELINSTR . "\n" .
            "Contact : \t\t". $postDELCONTACT . "\n" .
            "Contact No. : \t\t". $postDELCONTACTNO . "\n" .
            "Service Type : \t\t". $postSERVICE . "\n" .
            "Order No. : \t\t". $getSequenceResult . "\n" .
            "Order Date : \t\t" . date('Y-m-d H:i:s', strtotime(gmdate('Y-m-d H:i:s'))+7200) . "\n";

$htmlOUT .= '<h4>Items</h4>';
$htmlOUT .= str_pad("Product Code",33," ", STR_PAD_RIGHT) .
            str_pad("Description", 33, " ", STR_PAD_RIGHT) .
            "Quantity" . "\n" .
            "--------------------------------------------------------------------------\n";

$htmlOUT .= $htmlProductList;
$htmlOUT .= '</pre></div></body></html>';


$postDistributionTO = new PostingDistributionTO();
$postDistributionTO->DMLType = "INSERT";
$postDistributionTO->subject = "Edge Client Order - " . $prinArr[0]['principal_name'] . ' - ' . $getSequenceResult;
$postDistributionTO->body = $htmlOUT;
$postDistributionTO->deliveryType = BT_EMAIL;
$postDistributionTO->fromAlias = $uArr[0]['full_name'];
$postDistributionTO->fromAddr = $uArr[0]['user_email'];
$postDistributionTO->sourceIdentifier = 'SEQ:'.abs($getSequenceResult).'|USR:'.$userId;


$depotEmailArr[] = $uArr[0]['user_email'];  //send also to user for reference.

foreach($depotEmailArr as $depotEmail){
  $postDistributionTO->destinationAddr = $depotEmail;
  $dResult = $postDistributionDAO->postQueueDistribution($postDistributionTO);
  if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
    $returnMessage->type=FLAG_ERRORTO_ERROR;
    $returnMessage->description = 'Error sending email - ' . $dResult->description;
    echo CommonUtils::getJavaScriptMsg($returnMessage);
    return;
  }
}


if ($dResult->type==FLAG_ERRORTO_SUCCESS) {
  mysql_query("commit", $dbConn->connection);
  $returnMessage->type = FLAG_ERRORTO_SUCCESS;
  $returnMessage->description = "Order successfully created!<br><br><STRONG><FONT STYLE='font-size:16px;'>ORDER NO: " . abs($getSequenceResult) . "</FONT></STRONG>";
  echo CommonUtils::getJavaScriptMsg($returnMessage);
  return;
}


?>
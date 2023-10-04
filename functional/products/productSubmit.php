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
include_once($ROOT.$PHPFOLDER.'DAO/PostProductDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingProductTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingPrincipalProductMinorCategoryTO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');


if (!isset($_SESSION)) session_start;
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION['user_id'];
$returnMessage = new ErrorTO();
$dbConn = new dbConnect();
$dbConn->dbConnection();


$postDMLTYPE = (isset($_POST['DMLTYPE'])) ? $_POST['DMLTYPE'] : false; //rename to uid
if (isset($_POST['UID'])) $postUID=$_POST['UID']; else $postUID="";
if (isset($_POST['PRODCODE'])) $postPRODCODE=urldecode($_POST['PRODCODE']); else $postPRODCODE="";
if (isset($_POST['PRODDESC'])) $postPRODDESC=urldecode($_POST['PRODDESC']); else $postPRODDESC="";
if (isset($_POST['PACKING'])) $postPACKING=urldecode($_POST['PACKING']); else $postPACKING=""; 
if (isset($_POST['PRODWGT'])) $postPRODWGT=$_POST['PRODWGT']; else $postPRODWGT="";
if (isset($_POST['PRODVAT'])) $postPRODVAT=$_POST['PRODVAT']; else $postPRODVAT="";
if (isset($_POST['AUTHVAT'])) $postAUTHVAT=$_POST['AUTHVAT']; else $postAUTHVAT="";
if (isset($_POST['PRODCAT1'])) $postPRODCAT1=$_POST['PRODCAT1']; else $postPRODCAT1="";
if (isset($_POST['STATUS'])) $postSTATUS=$_POST['STATUS']; else $postSTATUS="";
if (isset($_POST['EPC'])) $postEPC = $_POST['EPC']; else $postEPC="N"; // enforce pallet consignment
if (isset($_POST['UPP'])) $postUPP = $_POST['UPP']; else $postUPP="0"; // units per pallet
if (isset($_POST['ALTCODE'])) $postALTCODE = $_POST['ALTCODE']; else $postALTCODE=""; // alternate code
if (isset($_POST['ITEMSPERCASE'])) $postITEMSPERCASE = $_POST['ITEMSPERCASE']; else $postITEMSPERCASE="1"; // items per case
if (isset($_POST['PMGUIDLIST'])) $postPMGUIDLIST = $_POST['PMGUIDLIST']; else $postPMGUIDLIST=array(); // array
if (isset($_POST['NSI'])) $postNSI = $_POST['NSI']; else $postNSI="N"; // Non Stock Item
if (isset($_POST['WEB'])) $postWEB = $_POST['WEB']; else $postWEB="N"; // Show in web shop
if (isset($_POST['SHOPIFY'])) $postSHOPIFY = $_POST['SHOPIFY']; else $postSHOPIFY="N"; // Show in web shop\
if (isset($_POST['DISCOUNTS'])) $postDISCOUNTS = $_POST['DISCOUNTS']; else $postDISCOUNTS="N"; // Show in web shop
if (isset($_POST['ALLOWDECIMALS'])) $postALLOWDECIMALS = $_POST['ALLOWDECIMALS']; else $postALLOWDECIMALS="N"; // Show in web shop




$postUNITVALUE = (isset($_POST['UNITVALUE'])) ? $_POST['UNITVALUE'] : 0;
$postSIZETYPE = (isset($_POST['SIZETYPE'])) ? $_POST['SIZETYPE'] : 0;
$postSIZEWIDTH = (isset($_POST['SIZEWIDTH'])) ? $_POST['SIZEWIDTH'] : 0;
$postSIZELENGTH = (isset($_POST['SIZELENGTH'])) ? $_POST['SIZELENGTH'] : 0;
$postSIZEHEIGHT = (isset($_POST['SIZEHEIGHT'])) ? $_POST['SIZEHEIGHT'] : 0;
$postPHOTOURL = (isset($_POST['PHOTOURL'])) ? $_POST['PHOTOURL'] : false;
$postPRODSKUGTIN = (isset($_POST['PRODSKUGTIN'])) ? (explode(',',urldecode($_POST['PRODSKUGTIN']))) : (array());  //Possible csv string.
$postOCGTIN = (isset($_POST['OCGTIN'])) ? (explode(',',urldecode($_POST['OCGTIN']))) : (array());   //Possible csv string.
$postPRODEPOTGTIN = (isset($_POST['PRODEPOTGTIN'])) ? (explode(',',urldecode($_POST['PRODEPOTGTIN']))) : (array());   //Possible csv string.

// start of superficial checks. Main checks done in adminPost.php
if(($postDMLTYPE!="INSERT") && ($postDMLTYPE!="UPDATE")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="invalid DMLTYPE.";
	echo CommonUtils::getJavaScriptMsg($returnMessages);
	return;
};

if(($postDMLTYPE=="UPDATE") && ($postUID=="")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Update requires a UID";
	echo CommonUtils::getJavaScriptMsg($returnMessages);
	return;
};

//make sure the lengths of the DEPOT LINKED GTIN ARE EQUAL.
if(count($postPRODSKUGTIN) != count($postOCGTIN) || count($postPRODSKUGTIN) != count($postPRODEPOTGTIN) || count($postOCGTIN) != count($postPRODEPOTGTIN)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Error on Depot Linked GTIN codes.";
	echo CommonUtils::getJavaScriptMsg($returnMessages);
	return;
};



// convert the supplied UID into a principal_id and depot_id if INSERT




$bot_error_l="";
if ( trim( $postPRODCODE ) == "" ) {
        $bot_error_l .= "Please enter a product code - may not be blank<BR>";
}
if ( trim( $postPRODDESC ) == "" ) {
        $bot_error_l .= "Please enter a description, may not be blank <BR>";
}
if($bot_error_l!="") {
  $returnMessages=new ErrorTO;
  $returnMessages->type=FLAG_ERRORTO_ERROR;
  $returnMessages->description=$bot_error_l;
  print(CommonUtils::getJavaScriptMsg($returnMessages));
  return;
}


if ($bot_error_l == "") {

  $postProductTO = new PostingProductTO;
  $postProductTO->DMLType = $postDMLTYPE;
  $postProductTO->UId = $postUID;
  $postProductTO->principal = $principalId;
  $postProductTO->productCode = trim($postPRODCODE);
  $postProductTO->productDescription = $postPRODDESC;
  $postProductTO->packing = $postPACKING;
  $postProductTO->skuGTINList = $postPRODSKUGTIN;
  $postProductTO->outerCasingGTINList = $postOCGTIN;
  $postProductTO->gtinDepotUidList = $postPRODEPOTGTIN;
  $postProductTO->weight = $postPRODWGT;
  $postProductTO->majorCategory = $postPRODCAT1;
  $postProductTO->minorCategory = "0"; // just default this for now. not used.
  $postProductTO->productVATRate = $postPRODVAT;
  $postProductTO->vatExclAuthorisedByFlag = $postAUTHVAT;
  //$postProductTO->productString       = addSlashes( $postPSTAD3)        ;
  $postProductTO->status = $postSTATUS;
  $postProductTO->enforcePalletConsignment = $postEPC;
  $postProductTO->unitsPerPallet = $postUPP;
  $postProductTO->altCode = $postALTCODE;
  $postProductTO->itemsPerCase = $postITEMSPERCASE;
  $postProductTO->unitValue = $postUNITVALUE;
  $postProductTO->sizeType = $postSIZETYPE;
  $postProductTO->sizeWidth = $postSIZEWIDTH;
  $postProductTO->sizeLength = $postSIZELENGTH;
  $postProductTO->sizeHeight = $postSIZEHEIGHT;
  $postProductTO->nonStockItem = $postNSI;
  $postProductTO->webCapture = $postWEB;
  $postProductTO->loadToShopify = $postSHOPIFY;  
  $postProductTO->noDiscounts = $postDISCOUNTS;  
   $postProductTO->allowDecimal = $postALLOWDECIMALS;   
  
    
  

  // Product Minor Groups
  if (count($postPMGUIDLIST)>0) {
    foreach ($postPMGUIDLIST as $fieldUid => $r) {
      $postingPrincipalProductMinorCategoryTO = new PostingPrincipalProductMinorCategoryTO();
      $postingPrincipalProductMinorCategoryTO->principalProductUId = $postProductTO->UId;
      $postingPrincipalProductMinorCategoryTO->productMinorCategoryUId = $r;
      $postingPrincipalProductMinorCategoryTO->minorCategoryTypeUId = $fieldUid;
      $postProductTO->principalProductMinorCategoryTOArr[] = $postingPrincipalProductMinorCategoryTO;
    }
  }

  $PostNewProduct = new PostProductDAO($dbConn);
  $productresult = $PostNewProduct->postProduct($postProductTO, $userId);

  if (sizeof($productresult)== 0) {
    $returnMessages=new ErrorTO;
    $returnMessages->type=FLAG_ERRORTO_ERROR;
    $returnMessages->description="Problem occurred during posting : Post returned 0 arraysize. Please inform Kwelanga Solutions Support.";
    print(CommonUtils::getJavaScriptMsg($returnMessages));
    return;
  }

  if ($productresult->type==FLAG_ERRORTO_SUCCESS) {
    $result2=mysqli_query($dbConn->connection, "commit");

    // add the product to the user that has captured the product
    // Note : the main add product has committed, so if this fails, we just show an error as part of success msg
    if($postDMLTYPE=="INSERT"){
            include_once($ROOT.$PHPFOLDER."DAO/PostAdminUserDAO.php");
            $postAdminUserDAO = new PostAdminUserDAO($dbConn);
            include_once($ROOT.$PHPFOLDER."TO/PostingUserProductTO.php");
            $postingUserProductTO = new PostingUserProductTO;
            $postingUserProductTO->DMLType=$postDMLTYPE;
            $postingUserProductTO->userId=$userId;
            $postingUserProductTO->principalProductUId=$postProductTO->UId;
            $postingUserProductTO->principalId=$principalId;
            $postingUserProductTO->packing=$postPACKING;
            // Do the Actual Posting. Don't worry about the return result.
            $result2=$postAdminUserDAO->postUserProduct($postingUserProductTO);
            $productresult->description .= "<BR>User-Product permissions result:".$result2->description; // join result to first
            $result2=mysqli_query($dbConn->connection, "commit");
    }

    //PHOTO HANDLING.
    if($postPHOTOURL!==false){
      if(is_file($postPHOTOURL)){
        $filename = str_replace(basename($postPHOTOURL),'',$postPHOTOURL) . $principalId .'_'.$productresult->identifier.'.jpg';
        @unlink($filename);
        rename($postPHOTOURL, $filename);

        $productresult->description .= '<BR>Photo Changed!';
      }
    }


    print(CommonUtils::getJavaScriptMsg($productresult));
    return;
  } else {
    $result2=mysqli_query($dbConn->connection, "rollback");
    print(CommonUtils::getJavaScriptMsg($productresult));
    return;
    }


            $dbConn->dbClose();
            return;
}
?>

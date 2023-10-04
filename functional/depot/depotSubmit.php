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

if (!isset($_SESSION)) session_start;
$userId         = $_SESSION['user_id'];
$principalId  = $_SESSION['principal_id'] ;

$returnMessage;

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

$postDMLTYPE = (isset($_POST['DMLTYPE'])) ? (htmlspecialchars($_POST['DMLTYPE'])) : ('VIEW');
$postDEPOTCODE = (isset($_POST['DEPOTCODE'])) ? (htmlspecialchars($_POST['DEPOTCODE'])) : ("");
$postDEPOTNAME = (isset($_POST['DEPOTNAME'])) ? (htmlspecialchars($_POST['DEPOTNAME'])) : ("");
$postDEPOTID = (isset($_POST['DEPOTID'])) ? (htmlspecialchars($_POST['DEPOTID'])) : ("");
$postDEPOTEMAILLST = (isset($_POST['DEPOTEMAILLST'])) ? (htmlspecialchars($_POST['DEPOTEMAILLST'])) : ("");
$postDEPOTWMS = (isset($_POST['DEPOTWMS'])) ? (htmlspecialchars($_POST['DEPOTWMS'])) : ("");
$postSKIPINPICKSTAGE = (isset($_POST['SKIPINPICKSTAGE'])) ? (htmlspecialchars($_POST['SKIPINPICKSTAGE'])) : ("");
$postDEPOTCHARGE = (isset($_POST['DEPOTCHARGE'])) ? (htmlspecialchars($_POST['DEPOTCHARGE'])) : ("");
$postDEPOTPAPERCHARGE = (isset($_POST['DEPOTPAPERCHARGE'])) ? (htmlspecialchars($_POST['DEPOTPAPERCHARGE'])) : ("");
$postDELIVERYNOTE = (isset($_POST['DELIVERYNOTE'])) ? (htmlspecialchars($_POST['DELIVERYNOTE'])) : ("");


// start of superficial checks. Main checks done in adminPost.php
if($postDMLTYPE != "INSERT" && $postDMLTYPE != "UPDATE") {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="invalid DMLTYPE. You sent me:".$postDMLTYPE;
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

//make sure the update has a PRINID and is numeric.
if (($postDMLTYPE == "UPDATE") && ($postDEPOTID == "") && ! is_numeric($postDEPOTID)) {
  $returnMessages=new ErrorTO;
  $returnMessages->type=FLAG_ERRORTO_ERROR;
  $returnMessages->description='No Depot Selected';
  echo CommonUtils::getJavaScriptMsg($returnMessages);
  return;
};


$bot_error_l="";
if ( trim( $postDEPOTCODE ) == "" ) {
	$bot_error_l .= "Please enter a valid  Depot code - may not be blank<BR>";
}

if ( trim( $postDEPOTNAME ) == "" ) {
	$bot_error_l .= "Please enter a depot Name, may not be blank <BR>";
}

if($bot_error_l!="") {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description=$bot_error_l;
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};


//VALIDATE EMAIL LIST.
$eA = explode(';', $postDEPOTEMAILLST);
$depotEmailArr = array();
foreach($eA as $e){
  if(!empty($e)){
    if (!preg_match(GUI_PHP_EMAIL_REGEX,trim($e))){
    	$returnMessages=new ErrorTO;
    	$returnMessages->type=FLAG_ERRORTO_ERROR;
    	$returnMessages->description="Invalid Depot Contact E-mail Address for : " . $e;
    	print(CommonUtils::getJavaScriptMsg($returnMessages));
    	return;
    } else {
      $depotEmailArr[] = trim($e);
    }
  }
}

if ( $bot_error_l == "" ) {

    $postDepotTO = new PostingDepotTO;
      $postDepotTO->DMLType   = $postDMLTYPE;
      $postDepotTO->depotCode = $postDEPOTCODE;
      $postDepotTO->depotName = $postDEPOTNAME;
      $postDepotTO->depotUid = $postDEPOTID;
      $postDepotTO->depotEmailList = join(';',$depotEmailArr);
      $postDepotTO->WMS = $postDEPOTWMS;
      $postDepotTO->skipInPickStage = $postSKIPINPICKSTAGE;
      $postDepotTO->depotCharge = $postDEPOTCHARGE;
      $postDepotTO->depotPaperCharge = $postDEPOTPAPERCHARGE;
      $postDepotTO->deliveryNote = $postDELIVERYNOTE;

      $PostNewDepot = new PostDepotDAO($dbConn);
      $result = $PostNewDepot->postNewDepot($postDepotTO, $userId);
      if (sizeof($result)== 0) {
      	$returnMessages=new ErrorTO;
      	$returnMessages->type=FLAG_ERRORTO_ERROR;
      	$returnMessages->description="Problem occurred during posting : Post returned 0 arraysize. Please inform RetailTrading Management.";
      	print(CommonUtils::getJavaScriptMsg($returnMessages));
      	return;
      }

      if ($result->type==FLAG_ERRORTO_SUCCESS) {


          // add the CUSTOM principal-DEPOT-fields : START
          include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
          include_once($ROOT.$PHPFOLDER."DAO/PostMiscellaneousDAO.php");
          include_once($ROOT.$PHPFOLDER."TO/PostingSpecialFieldTO.php");

          $postingSpecialFieldTO = new PostingSpecialFieldTO;
          $postingSpecialFieldTO->DMLType = $postDMLTYPE;
          $miscDAO = new MiscellaneousDAO($dbConn);
          $postMiscDAO = new PostMiscellaneousDAO($dbConn);
          $mfFlds=$miscDAO->getPrincipalSpecialFields($principalId, CT_DEPOT_SHORTCODE);


          $specialFieldAllow = 0;  //value to change behaviour of description of special fields.

          foreach ( $mfFlds as $smpfLine ) {
             $postingSpecialFieldTO->principal = $principalId;
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
           	   $postingSpecialFieldTO->entityUId = $result->identifier;
           	   $postingSpecialFieldTO->editable = 'Y';
           	 } else {
           	   $postingSpecialFieldTO->entityUId = $postDEPOTID;
           	   $postingSpecialFieldTO->editable = $smpfLine['editable'];
           	 }

           	 if($postingSpecialFieldTO->editable=='Y'){
            	 $Smpdresult = $postMiscDAO->postSpecialField($postingSpecialFieldTO);

             	if ($Smpdresult->type != FLAG_ERRORTO_SUCCESS) {
      			  $result3=mysqli_query($dbConn->connection, "rollback");
      			  $result->type=FLAG_ERRORTO_ERROR;  // cancel the description of 1st and display error.
      			  $result->description = "The Depot could not be added/updated because the Special Field update/insert failed.<BR><BR>".$Smpdresult->description;
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

        if (CommonUtils::isDepotUser()) $_SESSION['skip_inpick_stage'] = $postDepotTO->skipInPickStage;

      	$result2=mysqli_query($dbConn->connection, "commit");
      	print(CommonUtils::getJavaScriptMsg($result));
      	return;


      } else {
      	$result2=mysqli_query($dbConn->connection, "rollback");
      	print(CommonUtils::getJavaScriptMsg($result));
      	return;
      }

	$dbConn->dbClose();
	return;
}


?>
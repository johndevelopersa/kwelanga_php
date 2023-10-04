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
include_once($ROOT.$PHPFOLDER."DAO/SequenceDAO.php");
include_once($ROOT.$PHPFOLDER."TO/SequenceTO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingStoreTO.php");

$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback

if (!isset($_SESSION)) session_start;
// override the principal Id for now.
$postPRINCIPAL = $_SESSION['principal_id'];
$userId = $_SESSION['user_id'];

$returnMessage;

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

$postPRINCIPALID = $_SESSION['principal_id']; // override the principalId for now
$postDMLTYPE = htmlspecialchars($_POST['DMLTYPE']);
$postPRINCIPALSTOREUID = (isset($_POST['PRINCIPALSTOREUID'])) ? (($_POST['PRINCIPALSTOREUID'])) : ("");
$postDELNAME = (isset($_POST['DELNAME'])) ? ((urldecode($_POST['DELNAME']))) : ("");
$postBILLNAME = (isset($_POST['BILLNAME'])) ? ((urldecode($_POST['BILLNAME']))) : ("");
$postDELADDR1 = (isset($_POST['DELADDR1'])) ? ((urldecode($_POST['DELADDR1']))) : ("");
$postDELADDR2 = (isset($_POST['DELADDR2'])) ? ((urldecode($_POST['DELADDR2']))) : ("");
$postDELADDR3 = (isset($_POST['DELADDR3'])) ? ((urldecode($_POST['DELADDR3']))) : ("");
$postBILLADDR1 = (isset($_POST['BILLADDR1'])) ? ((urldecode($_POST['BILLADDR1']))) : ("");
$postBILLADDR2 = (isset($_POST['BILLADDR2'])) ? ((urldecode($_POST['BILLADDR2']))) : ("");
$postBILLADDR3 = (isset($_POST['BILLADDR3'])) ? ((urldecode($_POST['BILLADDR3']))) : ("");
$postEAN = (isset($_POST['EAN'])) ? (($_POST['EAN'])) : ("");
$postVATNO = (isset($_POST['VATNO'])) ? (($_POST['VATNO'])) : ("");
$postVATNO2 = (isset($_POST['VATNO2'])) ? (($_POST['VATNO2'])) : ("");
$postTELNO1 = (isset($_POST['TELNO1'])) ? (trim($_POST['TELNO1'])) : ("");
$postTELNO2 = (isset($_POST['TELNO2'])) ? (trim($_POST['TELNO2'])) : ("");
$postEMAILADD = (isset($_POST['EMAILADD'])) ? (trim($_POST['EMAILADD'])) : ("");
$postBRANCHCODE = (isset($_POST['BRANCHCODE'])) ? (($_POST['BRANCHCODE'])) : ("");
$postNOVAT = (isset($_POST['NOVAT'])) ? (($_POST['NOVAT'])) : ("");
$postAUTHVAT = (isset($_POST['AUTHVAT']) && ($_POST['AUTHVAT']!="")) ? (($_POST['AUTHVAT'])) : ("N");
$postCHAIN = (isset($_POST['CHAIN'])) ? (($_POST['CHAIN'])) : ("");
$postALTCHAIN = (isset($_POST['ALTCHAIN'])) ? (($_POST['ALTCHAIN'])) : ("");
$postDEPOT = (isset($_POST['DEPOT'])) ? (($_POST['DEPOT'])) : ("");
$postDELDAY = (isset($_POST['DELDAY'])) ? (($_POST['DELDAY'])) : ("");
$postORDDAY = (isset($_POST['ORDDAY'])) ? (($_POST['ORDDAY'])) : ("");
$postONHOLD = (isset($_POST['ONHOLD'])) ? (($_POST['ONHOLD'])) : ("");
$postNOPRICES = (isset($_POST['NOPRICES'])) ? (($_POST['NOPRICES'])) : ("");
$postOWNEDBY = (isset($_POST['OWNEDBY'])) ? (($_POST['OWNEDBY'])) : ("");
$postOLDACCOUNT = (isset($_POST['OLDACCOUNT'])) ? (($_POST['OLDACCOUNT'])) : (""); // not part of screen. only used in edi export.
$postAGSM = (isset($_POST['AGSM'])) ? (($_POST['AGSM'])) : ("");
$postUSERPERMISSIONS = (isset($_POST['USERPERMISSIONS'])) ? (($_POST['USERPERMISSIONS'])) : ("");
$postLB = (isset($_POST['LB'])) ? (($_POST['LB'])) : ("");
$postLCL = (isset($_POST['LCL'])) ? (($_POST['LCL'])) : ("");
$postSTATUS = (isset($_POST['STATUS'])) ? ($_POST['STATUS']) : ("");
$postAREA = (isset($_POST['AREA'])) ? ($_POST['AREA']) : ("");
$postPSMCLIENTUID = (isset($_POST['PSMCLIENTUID'])) ? ($_POST['PSMCLIENTUID']) : ("");
$postRETAILER = (isset($_POST['RETAILER'])) ? ($_POST['RETAILER']) : ("");
$postBACCOUNT = (isset($_POST['BACCOUNT'])) ? ($_POST['BACCOUNT']) : ("");
$postQRCODE = (isset($_POST['QRCODE'])) ? ($_POST['QRCODE']) : ("");
$postSALESREPID = (isset($_POST['SALESREPID'])) ? ($_POST['SALESREPID']) : (0);
$postEXPORTNUMBERENABLED = (isset($_POST['EXPORTNUMBERENABLED'])) ? ($_POST['EXPORTNUMBERENABLED']) : ("N");
$postDISVAL = (isset($_POST['DISVAL'])) ? ($_POST['DISVAL']) : ("0");
$postWLINK  = (isset($_POST['WLINK'])) ? ($_POST['WLINK']) : ("0");
$postLC = (isset($_POST['LC'])) ? (($_POST['LC'])) : ("Y");
//EPOD fields
$postEPODFLAG = (isset($_POST['EPODFLAG'])) ? (trim($_POST['EPODFLAG'])) : ("N");
$postEPODRSAID = (isset($_POST['EPODRSAID'])) ? (trim($_POST['EPODRSAID'])) : ("");
$postEPODCELLNO = (isset($_POST['EPODCELLNO'])) ? (trim($_POST['EPODCELLNO'])) : ("");
$postAUTOMAILINVOICE = (isset($_POST['AUTOMAILINVOICE'])) ? (trim($_POST['AUTOMAILINVOICE'])) : ("");

// start of superficial checks. Main checks done in adminPost....php
if (($postDMLTYPE!="UPDATE") && ($postDMLTYPE!="INSERT")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Invalid Processing Type";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if (($postDMLTYPE=="UPDATE") && ($postPRINCIPALSTOREUID=="")) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="UPDATE Requires a UID";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if(($postDELNAME=="") || (strlen($postDELNAME)<5)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Delivery Name not supplied or too short.";
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

if(!preg_match(GUI_PHP_INTEGER_REGEX,$postCHAIN)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Chain Id not valid.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};

if(!preg_match(GUI_PHP_INTEGER_REGEX,$postDEPOT)) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Depot Id not valid.";
	print(CommonUtils::getJavaScriptMsg($returnMessages));
	return;
};
$postingStoreTO = new PostingStoreTO;
$postingStoreTO->DMLType =$postDMLTYPE;
$postingStoreTO->principalStoreUId =$postPRINCIPALSTOREUID;
$postingStoreTO->principal = $postPRINCIPAL;
$postingStoreTO->deliverName = str_replace(array('"',"'",";","\\","\r\n","\n"),array('','','',''),$postDELNAME);
$postingStoreTO->deliverAdd1 = str_replace(array('"',"'",";","\\","\r\n","\n"),array('','','',''),$postDELADDR1);
$postingStoreTO->deliverAdd2 = str_replace(array('"',"'",";","\\","\r\n","\n"),array('','','',''),$postDELADDR2);
$postingStoreTO->deliverAdd3 = str_replace(array('"',"'",";","\\","\r\n","\n"),array('','','',''),$postDELADDR3);
$postingStoreTO->billName = str_replace(array('"',"'",";","\\","\r\n","\n"),array('','','',''),$postBILLNAME);
$postingStoreTO->billAdd1 = str_replace(array('"',"'",";","\\","\r\n","\n"),array('','','',''),$postBILLADDR1);
$postingStoreTO->billAdd2 = str_replace(array('"',"'",";","\\","\r\n","\n"),array('','','',''),$postBILLADDR2);
$postingStoreTO->billAdd3 = str_replace(array('"',"'",";","\\","\r\n","\n"),array('','','',''),$postBILLADDR3);
$postingStoreTO->eanCode = $postEAN;
$postingStoreTO->vatNumber = $postVATNO;
$postingStoreTO->vatNumber2 = $postVATNO2;
$postingStoreTO->telNo1 = $postTELNO1;
$postingStoreTO->telNo2 = $postTELNO2;
$postingStoreTO->emailAdd = $postEMAILADD;
$postingStoreTO->depot = $postDEPOT;
$postingStoreTO->deliveryDay = $postDELDAY;
$postingStoreTO->orderDay = $postORDDAY;
$postingStoreTO->noVAT = $postNOVAT;
$postingStoreTO->vatExclAuthorisedByFlag = $postAUTHVAT;
$postingStoreTO->onHold = $postONHOLD;
$postingStoreTO->chain = $postCHAIN;
$postingStoreTO->altPrincipalChainUId = $postALTCHAIN;
$postingStoreTO->branchCode = $postBRANCHCODE;
$postingStoreTO->oldAccount = $postOLDACCOUNT; // if empty will be allocated automatically
$postingStoreTO->allocatePermissionsUserList=$postUSERPERMISSIONS;
$postingStoreTO->ledgerBalance=$postLB;
$postingStoreTO->ledgerCreditLimit=$postLCL;
$postingStoreTO->status = $postSTATUS;
$postingStoreTO->ownedBy = $postOWNEDBY;
$postingStoreTO->areaUId = mysqli_real_escape_string($dbConn->connection, $postAREA);
$postingStoreTO->retailer = mysqli_real_escape_string($dbConn->connection, $postRETAILER);
$postingStoreTO->baccount = mysqli_real_escape_string($dbConn->connection, $postBACCOUNT);
$postingStoreTO->qrcode = mysqli_real_escape_string($dbConn->connection, $postQRCODE);
$postingStoreTO->principalSalesRepresentativeUId = $postSALESREPID;
$postingStoreTO->exportNumberEnabled = $postEXPORTNUMBERENABLED;
$postingStoreTO->disval = $postDISVAL;
$postingStoreTO->wlink  = $postWLINK; 
$postingStoreTO->localCountry = $postLC;
$postingStoreTO->autoMailInvoice = $postAUTOMAILINVOICE; 
$postingStoreTO->noPricesOnInvoice = $postNOPRICES;

//only save these fields if epod is enabled at store level.
if($postEPODFLAG == 'Y'){
  $postingStoreTO->epodStoreFlag = $postEPODFLAG;
  $postingStoreTO->epodRsaId = $postEPODRSAID;
  $postingStoreTO->epodCellphoneNumber = $postEPODCELLNO;
}

// set address if blank
if (
	($postingStoreTO->billName=="") &&
	($postingStoreTO->billAdd1=="")
   ) {
   	$postingStoreTO->billName = $postingStoreTO->deliverName;
   	$postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
   	$postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
   	$postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
   }


// Do the Actual Posting of store
include_once($ROOT.$PHPFOLDER."DAO/PostStoreDAO.php");
$postStoreDAO = new PostStoreDAO($dbConn);
$result=$postStoreDAO->postPrincipalStore($postingStoreTO);
$seqPS=$result->identifier;
$returnJS="";
if ($postDMLTYPE=="INSERT") $postingStoreTO->principalStoreUId =$seqPS;
if ($result->type==FLAG_ERRORTO_SUCCESS) {
	if (($postDMLTYPE=="INSERT") && ($seqPS=="")) {
		$result->type=FLAG_ERRORTO_ERROR;
		$result->description="A problem occurred retrieving the store sequence. Store could not be created !";
		print(CommonUtils::getJavaScriptMsg($result));
		return;
	}

	// link the store if necessary to association
	if (($postDMLTYPE=="INSERT") && ($postPSMCLIENTUID!="")) {
		$result=$postStoreDAO->associateStore($postPRINCIPAL,$seqPS,$postPSMCLIENTUID);
		if ($result->type!=FLAG_ERRORTO_SUCCESS) {
			mysql_query($dbConn->connection, "rollback");
			print(CommonUtils::getJavaScriptMsg($result));
			return;
		} else {
		  $returnJS="var msgClassIdentifier={delloc:\"".(str_replace("'","\'",$postingStoreTO->deliverName))."\",".
																									 "delarea:\"".(str_replace("'","\'",$postingStoreTO->areaDescription))."\",".
																									 "delday:\"".(str_replace("'","\'",$postingStoreTO->deliveryDayDescription))."\"};"; // is eval() on client side. It is passed to client as '...contents...' so strip out apostrophes here
		}
	}

}

if ($result->type==FLAG_ERRORTO_SUCCESS) {
	// add the principal-store-fields
	include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
	include_once($ROOT.$PHPFOLDER."DAO/PostMiscellaneousDAO.php");
	include_once($ROOT.$PHPFOLDER."TO/PostingSpecialFieldTO.php");

	$postingSpecialFieldTO = new PostingSpecialFieldTO;
	$postingSpecialFieldTO->DMLType =$postDMLTYPE;

	$miscDAO = new MiscellaneousDAO($dbConn);
	$postMiscDAO = new PostMiscellaneousDAO($dbConn);
	$mfFlds=$miscDAO->getPrincipalSpecialFields($postingStoreTO->principal, CT_STORE_SHORTCODE);


	$specialFieldAllow = 0;  //value to change behaviour of description of special fields.

	foreach ( $mfFlds as $smpfLine ) {
       $postingSpecialFieldTO->principal = $postingStoreTO->principal;
		   $postingSpecialFieldTO->deliverName = $postingStoreTO->deliverName;
     	 $postingSpecialFieldTO->fielduid = $smpfLine['uid'];
       $postingSpecialFieldTO->depotUId = $postingStoreTO->depot;

     	 if ($postingStoreTO->status==FLAG_STATUS_DELETED) $postingSpecialFieldTO->skipValidation = "Y"; // allow a store missing special fields to be deleted

     	 if ($postDMLTYPE=="INSERT"){
     	   $postingSpecialFieldTO->entityUId = $seqPS;
     	   $postingSpecialFieldTO->editable = 'Y';
     	 } else {
     	   $postingSpecialFieldTO->entityUId = $postPRINCIPALSTOREUID;
     	   $postingSpecialFieldTO->editable = $smpfLine['editable'];
     	 }

     	 if (isset($_POST[str_replace(' ','',$smpfLine['name'])])) $postingSpecialFieldTO->value = urldecode($_POST[str_replace(' ','',$smpfLine['name'])]); else $postingSpecialFieldTO->value = "";

     	 if($postingSpecialFieldTO->editable=='Y'){
         	 $Smpdresult = $postMiscDAO->postSpecialField($postingSpecialFieldTO);

         	 if ($Smpdresult->type!=FLAG_ERRORTO_SUCCESS) {
    			$result3=mysqli_query($dbConn->connection, "rollback");
    			// cancel the description of 1st and display error.
    			$result->type=FLAG_ERRORTO_ERROR;
    			$result->description="The Store could not be added/updated because the Special Field update/insert failed.<BR><BR>".$Smpdresult->description;
    			break;
    		  } else {
    		    $specialFieldAllow++;
    		  }
     	 }
    }

	if($result->type==FLAG_ERRORTO_SUCCESS && $specialFieldAllow>0)
	  $result->description .= "Special Fields Successfully updated/inserted.<BR><BR>";


	if($result->type==FLAG_ERRORTO_SUCCESS) {
	// add to global store too
		if (($postAGSM=="Y") && ($postDMLTYPE=="INSERT")) {
			$result2=$postStoreDAO->postGlobalStore($postingStoreTO);

			if ($result2->type==FLAG_ERRORTO_SUCCESS) {
				$result3=mysqli_query($dbConn->connection, "commit");
				$result->description .= "<BR>".$result2->description; // join result to first
			} else {
				$result3=mysqli_query($dbConn->connection, "rollback");
				// cancel the description of 1st and display error.
				$result->type = FLAG_ERRORTO_ERROR;
				$result->description="The Store could not be added because the Global Store Validation failed when trying to add the Global Store.<BR><BR>If you would like to store the principal-store only, untick the global checkbox at bottom.<BR><BR>";
				$result->description .= $result2->description;
			  }
		} else {
			$result3=mysqli_query($dbConn->connection, "commit");
		  }
	}

	// add the store to the user that has captured the store
	// Note : the main add store has committed, so if this fails, we just show an error as part of success msg
	if(($result->type==FLAG_ERRORTO_SUCCESS) && ($postDMLTYPE=="INSERT")) {
		include_once($ROOT.$PHPFOLDER."DAO/PostAdminUserDAO.php");
		$postAdminUserDAO = new PostAdminUserDAO($dbConn);
		include_once($ROOT.$PHPFOLDER."TO/PostingUserPrincipalStoreTO.php");
		$postingUserPrincipalStoreTO = new PostingUserPrincipalStoreTO;
		$postingUserPrincipalStoreTO->DMLType=$postDMLTYPE;
		$postingUserPrincipalStoreTO->userId=$userId;
		$postingUserPrincipalStoreTO->principalId=$postPRINCIPAL;
		$postingUserPrincipalStoreTO->principalStoreUId=$seqPS;
		// Do the Actual Posting. Don't worry about the return result.
		$result2=$postAdminUserDAO->postUserPrincipalStore($postingUserPrincipalStoreTO,$userId);
		$result->description .= "<BR>".$result2->description; // join result to first
		$result2=mysqli_query($dbConn->connection,"commit");

		// now also give permissions to the sales agents if necessary.
		// NOTE: the sales agents in this list were validated properly during store posting, so all is well!
		if ($postingStoreTO->allocatePermissionsUserList!="") {
			$saArr=explode(",",$postingStoreTO->allocatePermissionsUserList);
			$saSCnt=0; $saECnt=0;
			foreach ($saArr as $sa) {
				$postingUserPrincipalStoreTO->userId=$sa;
				$result2=$postAdminUserDAO->postUserPrincipalStore($postingUserPrincipalStoreTO,$userId);
				if ($result2->type!=FLAG_ERRORTO_SUCCESS) {
					// dont worry too much about this, just report it
					$saECnt++;
				} else $saSCnt++;
				$dbConn->dbinsQuery("commit;");
			}
			$result->description .= "<BR>{$saSCnt} Sales Agent(s) successfully allocated permissions ({$saECnt} Errors)"; // join result to first
		}

	} // end user-store

} else $result3=mysqli_query($dbConn->connection, "rollback");

$dbConn->dbClose();

// check return values
if (sizeof($result)> 0) {
	$result->identifier=$returnJS;
	print(CommonUtils::getJavaScriptMsg($result));
	return;
} else if (sizeof($result)== 0) {
	$returnMessages=new ErrorTO;
	$returnMessages->type=FLAG_ERRORTO_ERROR;
	$returnMessages->description="Problem occurred during posting : Post returned 0 arraysize. Please inform RetailTrading Management.";
	echo CommonUtils::getJavaScriptMsg($returnMessages);
	return;
  }

echo CommonUtils::getJavaScriptMsg($result);
return;

?>

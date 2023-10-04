<?php
/*
 *
 * THIS LAYER PROVIDES BASIC SCREENING AND CONVERTS INPUT INTO PROPER TO BEFORE CALL IS MADE TO PROCESS
 *
 */
include_once ('ROOT.php');
include_once ($ROOT . 'PHPINI.php');
require ($ROOT . $PHPFOLDER . "functional/main/access_control.php");
include_once ($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once ($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once ($ROOT . $PHPFOLDER . 'libs/ValidationCommonUtils.php');
include_once ($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/PostPrincipalDAO.php');
include_once ($ROOT . $PHPFOLDER . 'TO/PrincipalTO.php');
include_once ($ROOT . $PHPFOLDER . 'TO/PostingPrincipalTO.php');

class principalSubmit{

  public $dbConn;
  public $UserID;
  public $returnMsgTO;

  public $postPRINID;
  public $postDMLType;
  public $postPRINNAME;
  public $postPRINCODE;
  public $postALTPRINCODE;
  public $postPHYAD1;
  public $postPHYAD2;
  public $postPHYAD3;
  public $postPHYAD4;
  public $postPSTAD1;
  public $postPSTAD2;
  public $postPSTAD3;
  public $postPSTAD4;
  public $postVATNO;
  public $postPRINGLN;
  public $postRTTACC;
  public $postEMAIL;
  public $postCTCPER;
  public $postOTEL;
  public $postBANKDET;
  public $postPTYPE;
  public $postPRINUPLIFTCODE;
  public $postSTATUS;
  public $postEXPORTNUMBER;
  public $postCHARGE;
  public $postDOCCHARGE;
  public $postPAPERCHARGE;
  public $postCANCELCHARGE;
  public $postDEBTORCHARGE;

  public function __construct() {

    $this->returnMsgTO = new ErrorTO();
    $this->UserID = $_SESSION['user_id'];

    //Create DB link - call before mysql_real...
    $this->dbConn = new dbConnect();
    $this->dbConn->dbConnection();

    //Parse POST values
    $this->postDMLType = (isset($_POST['DMLTYPE'])) ? (trim($_POST['DMLTYPE'])) : ('VIEW');
    $this->postPRINID = (isset($_POST['PRINID']) && count($_POST['PRINID']) > 0) ? (trim($_POST['PRINID'])) : ("");
    $this->postPRINNAME = (isset($_POST['PRINNAME'])) ? (trim($_POST['PRINNAME'])) : ("");
    $this->postPRINCODE = (isset($_POST['PRINCODE'])) ? (trim($_POST['PRINCODE'])) : ("");
    $this->postALTPRINCODE = (isset($_POST['ALTPRINCODE'])) ? (trim($_POST['ALTPRINCODE'])) : ("");
    $this->postPHYAD1 = (isset($_POST['PHYAD1'])) ? (trim($_POST['PHYAD1'])) : ("");
    $this->postPHYAD2 = (isset($_POST['PHYAD2'])) ? (trim($_POST['PHYAD2'])) : ("");
    $this->postPHYAD3 = (isset($_POST['PHYAD3'])) ? (trim($_POST['PHYAD3'])) : ("");
    $this->postPHYAD4 = (isset($_POST['PHYAD4'])) ? (trim($_POST['PHYAD4'])) : ("");
    $this->postPSTAD1 = (isset($_POST['PSTAD1'])) ? (trim($_POST['PSTAD1'])) : ("");
    $this->postPSTAD2 = (isset($_POST['PSTAD2'])) ? (trim($_POST['PSTAD2'])) : ("");
    $this->postPSTAD3 = (isset($_POST['PSTAD3'])) ? (trim($_POST['PSTAD3'])) : ("");
    $this->postPSTAD4 = (isset($_POST['PSTAD4'])) ? (trim($_POST['PSTAD4'])) : ("");
    $this->postVATNO = (isset($_POST['VATNO'])) ? (trim($_POST['VATNO'])) : ("");
    $this->postPRINGLN = (isset($_POST['PRINGLN'])) ? (trim($_POST['PRINGLN'])) : ("");
    $this->postRTTACC = (isset($_POST['RTTACC'])) ? (trim($_POST['RTTACC'])) : ("");
    $this->postEMAIL = (isset($_POST['EMAIL'])) ? (trim($_POST['EMAIL'])) : ("");
    $this->postCTCPER = (isset($_POST['CTCPER'])) ? (trim($_POST['CTCPER'])) : ("");
    $this->postOTEL = (isset($_POST['OTEL'])) ? (trim($_POST['OTEL'])) : ("");
    $this->postBANKDET = (isset($_POST['BANKDET'])) ? (trim($_POST['BANKDET'])) : ("");
    $this->postPTYPE = (isset($_POST['PTYPE'])) ? (trim($_POST['PTYPE'])) : ("");
    $this->postSTATUS = (isset($_POST['STATUS'])) ? (trim($_POST['STATUS'])) : ("");
    $this->postPRINUPLIFTCODE = (isset($_POST['PRINUPLIFTCODE'])) ? (trim($_POST['PRINUPLIFTCODE'])) : ("");
    $this->postEXPORTNUMBER = (isset($_POST['EXPORTNUMBER'])) ? (trim($_POST['EXPORTNUMBER'])) : ("");
    $this->postCHARGE = (isset($_POST['CHARGE'])) ? $_POST['CHARGE'] : ("N");
    $this->postDOCCHARGE = (isset($_POST['DOCCHARGE'])) ? $_POST['DOCCHARGE'] : ("N");
    $this->postPAPERCHARGE = (isset($_POST['PAPERCHARGE'])) ? $_POST['PAPERCHARGE'] : ("");
    $this->postCANCELCHARGE = (isset($_POST['CANCELCHARGE'])) ? $_POST['CANCELCHARGE'] : ("");
    $this->postDEBTORCHARGE = (isset($_POST['DEBTORCHARGE'])) ? $_POST['DEBTORCHARGE'] : ("");
    //Check Soft validation
    if ($this->SoftValidation()) {
      $this->SubmitPrincipalTO();
    }
  }

  //func that builds the return message
  private function returnMessage($aMessage, $aFlag) {
    $this->returnMsgTO->type = $aFlag;
    $this->returnMsgTO->description = $aMessage;
    echo CommonUtils::getJavaScriptMsg($this->returnMsgTO);
  }

  private function SoftValidation() {

    $validationSuccess = true;

    //only allow inserts or updates
    if ($this->postDMLType !=  'INSERT' && $this->postDMLType != 'UPDATE') {
      $this->returnMessage('Invalid DMLTYPE "' . $this->postDMLType . '"', FLAG_ERRORTO_ERROR);
      $validationSuccess = false;
    }

    //make sure the update has a PRINID and is numeric.
    if (($this->postDMLType == "UPDATE") && ($this->postPRINID == "") && ! is_numeric($this->postPRINID)) {
      $this->returnMessage('No Principal Selected', FLAG_ERRORTO_ERROR);
      $validationSuccess = false;
      ;
    }
    return $validationSuccess;
  }

  private function SubmitPrincipalTO() {

    $postingPrincipalTO = new PostingPrincipalTO();

    $postingPrincipalTO->DMLType = $this->postDMLType;
    $postingPrincipalTO->puid = $this->postPRINID;
    $postingPrincipalTO->principal_code = $this->postPRINCODE;
    $postingPrincipalTO->name = $this->postPRINNAME;
    $postingPrincipalTO->physical_add1 = $this->postPHYAD1;
    $postingPrincipalTO->physical_add2 = $this->postPHYAD2;
    $postingPrincipalTO->physical_add3 = $this->postPHYAD3;
    $postingPrincipalTO->physical_add4 = $this->postPHYAD4;
    $postingPrincipalTO->postal_add1 = $this->postPSTAD1;
    $postingPrincipalTO->postal_add2 = $this->postPSTAD2;
    $postingPrincipalTO->postal_add3 = $this->postPSTAD3;
    $postingPrincipalTO->postal_add4 = $this->postPSTAD4;
    $postingPrincipalTO->vat_num = $this->postVATNO;
    $postingPrincipalTO->principalGLN = $this->postPRINGLN;
    $postingPrincipalTO->rt_acc_num = $this->postRTTACC;
    $postingPrincipalTO->office_tel = $this->postOTEL;
    $postingPrincipalTO->email_add = $this->postEMAIL;
    $postingPrincipalTO->contactperson = $this->postCTCPER;
    $postingPrincipalTO->bankingDetails = $this->postBANKDET;
    $postingPrincipalTO->altPrincipalCode = $this->postALTPRINCODE;
    $postingPrincipalTO->principalType = $this->postPTYPE;
    $postingPrincipalTO->status = $this->postSTATUS;
    $postingPrincipalTO->principalUpliftCode = $this->postPRINUPLIFTCODE;
    $postingPrincipalTO->exportNumber = $this->postEXPORTNUMBER;
    $postingPrincipalTO->charge = $this->postCHARGE;
    $postingPrincipalTO->doc_charge = $this->postDOCCHARGE;
    $postingPrincipalTO->paper_charge = $this->postPAPERCHARGE;
    $postingPrincipalTO->cancel_charge = $this->postCANCELCHARGE;
    $postingPrincipalTO->debtor_charge = $this->postDEBTORCHARGE;
    
    $principalPost = new PostPrincipalDAO($this->dbConn);
    $principalResult = $principalPost->postPrincipal($postingPrincipalTO);

    if (empty($principalResult)) {
	  $returnMessages->description='Problem occurred during posting : Post returned 0 arraysize. Please inform RetailTrading Management.';
	  $this->returnMessage($principalResult->description, $principalResult->type);
	  return;
    } else {
      if ($principalResult->type == FLAG_ERRORTO_SUCCESS) {
        $result = mysqli_query($this->dbConn->connection, 'commit');

        if($postingPrincipalTO->DMLType == 'INSERT'){
          $principalResult->description = 'Principal has been successfully created';
        } elseif($postingPrincipalTO->DMLType == 'UPDATE'){
          $principalResult->description = 'Principal has been updated';
        }

        $this->returnMessage($principalResult->description, $principalResult->type);
        return;
      } else {
        $result = mysqli_query($this->dbConn->connection, 'rollback');
        $this->returnMessage($principalResult->description, $principalResult->type);
        return;
      }
    }
  }

}

$principalSubmit = new principalSubmit();
$principalSubmit->dbConn->dbClose();

?>

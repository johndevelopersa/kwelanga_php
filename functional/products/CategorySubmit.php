<?php
/*
 * 
 * THIS LAYER PROVIDES BASIC SCREENING AND CONVERTS INPUT INTO PROPER TO BEFORE CALL IS MADE TO PROCESS
 * 
 */

include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once ($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/common.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostProductDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingProductCategoryTO.php');
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");


class productCategorySubmit{
  
  public $dbConn;
  public $UserID;
  public $principalId;
  public $returnMsgTO;
  
  public $postDMLType;
  public $postUID; 
  public $postPROCATNAME;
  public $postPROCATSTATUS;

  public function __construct() {
    
    $this->returnMsgTO = new ErrorTO();
    $this->UserID = $_SESSION['user_id'];
    $this->principalId = $_SESSION['principal_id'];

    //Create DB link - call before mysql_real...
    $this->dbConn = new dbConnect();
    $this->dbConn->dbConnection();
    
    //Parse POST values
    $this->postDMLType = (isset($_POST['DMLTYPE'])) ? (mysqli_real_escape_string($this->dbConn->connection, $_POST['DMLTYPE'])) : ('VIEW');
    $this->postUID = (isset($_POST['UID']) && count($_POST['UID']) > 0) ? (mysqli_real_escape_string($this->dbConn->connection, $_POST['UID'])) : ('');
    $this->postPROCATNAME = (isset($_POST['PROCATNAME'])) ? (mysqli_real_escape_string($this->dbConn->connection, $_POST['PROCATNAME'])) : ('');
    $this->postPROCATSTATUS = (isset($_POST['PROCATSTATUS'])) ? (mysqli_real_escape_string($this->dbConn->connection, $_POST['PROCATSTATUS'])) : (FLAG_STATUS_ACTIVE);

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
    if (($this->postDMLType == "UPDATE") && ($this->postUID == "") && ! is_numeric($this->postUID)) {
      $this->returnMessage('No Principal Selected', FLAG_ERRORTO_ERROR);
      $validationSuccess = false;      
    }      
    
    return $validationSuccess;
  }

  private function SubmitPrincipalTO() {
    
    $postingProductCategoryTO = new PostingProductCategoryTO();
    
    $postingProductCategoryTO->DMLType = $this->postDMLType;
    $postingProductCategoryTO->uid = $this->postUID;
    $postingProductCategoryTO->principalUId = $this->principalId;
    $postingProductCategoryTO->description = $this->postPROCATNAME;
    $postingProductCategoryTO->status = $this->postPROCATSTATUS;

    $productCatPost = new PostProductDAO($this->dbConn);
    $productCatResult = $productCatPost->postProductCategory($postingProductCategoryTO);
    
    if (empty($productCatResult)) {
	  $productCatResult->description='Problem occurred during posting : Post returned 0 arraysize. Please inform RetailTrading Management.';
	  echo CommonUtils::getJavaScriptMsg($productCatResult);
	  return; 
    } else {
      if ($productCatResult->type == FLAG_ERRORTO_SUCCESS) {
        $result = mysqli_query($this->dbConn->connection, 'commit');
        
        if($postingProductCategoryTO->DMLType == 'INSERT'){
          $productCatResult->description = 'Product Category was successfully created';  
        } elseif($postingProductCategoryTO->DMLType == 'UPDATE'){
          $productCatResult->description = 'Product Category has been updated';
        }         
        
        $this->returnMessage($productCatResult->description, $productCatResult->type);
        return;
      } else {
        $result = mysqli_query($this->dbConn->connection, 'rollback');
        $this->returnMessage($productCatResult->description, $productCatResult->type);
        return;
      }
    }
  }

}

$categorySubmit = new productCategorySubmit();
$categorySubmit->dbConn->dbClose();

?>

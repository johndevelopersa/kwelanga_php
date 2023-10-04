<?php
include_once('ROOT.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/TransactionDedicatedDAO.php');

/* NOTES:
 * This DAO containst static ONLY functions, and is use whenever a deviation from standard functionality
 * is used, where that functionality exceeds the maintainable usage of generic configuration.
 */

class SpecialValidationDAO {

  private static $principalSylko="3";
  private static $principalMisterSweet="104";
  private static $principalZambezi="48";
  public static $VAL_SRC_QTY_OPERATOR_LT="<";
  public static $VAL_SRC_QTY_OPERATOR_LTE="<=";
  public static $VAL_SRC_QTY_OPERATOR_EQ="==";
  public $errorTO;

  function __construct($dbConn) {

    $this->dbConn = $dbConn;
    $this->errorTO = new ErrorTO;
  }

  /* PURPOSE:
   * The capture screen will show / hide various fields / buttons / functionality based on this result.
   *
   * @param $principalUId =
   * @param $parentDocTypeUId = the original being linked to
   * @param $childDocTypeUId = the doc doing the cross referencing
   * @return true / false
   */
	public static function orderCapture_requiresXRefLookup($principalUId, $parentDocTypeUId, $childDocTypeUId) {

    // Sylko SOR : Uplift
    if (($principalUId==self::$principalSylko) && ($parentDocTypeUId==DT_FREEFORM_DOCTYPE_1) && ($childDocTypeUId==DT_UPLIFTS)) {
      return true;
    }

    return false;
	}

  /* PURPOSE:
   * The capture screen will show / hide various fields / buttons / functionality based on this result.
   * An array of doc types is returned for those requiring it.
   * At the moment, the capture screen does not cater for diff doc types for the parent Doc Type.
   *
   * @param $principalUId
   * @return array of doc types array[docType ndx]=docType
   */
  public static function orderCapture_getAllRequiresXRefLookup($principalUId) {

    if ($principalUId==self::$principalSylko) {
      return array(DT_UPLIFTS=>DT_UPLIFTS);
    }

    return array();
  }

  public function exitPrincipalDocumentValidation($postingOrderTO) {
    if ($postingOrderTO->principalUId==self::$principalSylko) {

      if (($postingOrderTO->documentType==DT_UPLIFTS) && (trim($postingOrderTO->sourceDocumentNumber)!="")) {
        $eTO=self::validateDocumentAgainstSource($postingOrderTO,DT_FREEFORM_DOCTYPE_1,$qtyOperator=self::$VAL_SRC_QTY_OPERATOR_LTE);
        if ($eTO->type!=FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->flag=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Failed Custom Validation with : ".$eTO->description;
          return $this->errorTO;
        }
      }

    }

    if ($postingOrderTO->principalUId==self::$principalMisterSweet) {
      // to prevent circular loops, leg 2 must NEVER trigger off leg 1 ! This logic is put into the validation as the depot needs to have been allocated
      if (($postingOrderTO->dataSource==DS_EDI) && ($postingOrderTO->capturedBy=="MSEVO") && ($postingOrderTO->processedDepotUId==105)) {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Mr Sweet leg 2 cannot be processed to Mister Sweet Depot as endless loop will result !";
        return $this->errorTO;
      }
    }

    $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
    $this->errorTO->description="No Custom Validation necessary";

    return $this->errorTO;
  }

  public function validateDocumentAgainstSource($postingOrderTO, $sourceDocumentType, $qtyOperator=false) {
    global $dbConn;
    $transactionDedicatedDAO = new TransactionDedicatedDAO($dbConn);

    if ($qtyOperator===false) $qtyOperator=self::$VAL_SRC_QTY_OPERATOR_EQ;
    if (!in_array($qtyOperator,array(self::$VAL_SRC_QTY_OPERATOR_EQ,self::$VAL_SRC_QTY_OPERATOR_LT,self::$VAL_SRC_QTY_OPERATOR_LTE))) {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description="Invalid operator passed in validateDocumentAgainstSource.";
      return $this->errorTO;
    }

    $mfT = $transactionDedicatedDAO->getCrossReferenceDocumentDetail($postingOrderTO->principalUId, $postingOrderTO->storeChainUId, $sourceDocumentType, $postingOrderTO->sourceDocumentNumber);

    $uniqDocs=array();
    foreach($mfT as $row) {
      $uniqDocs[$row["dm_uid"]]=$row["dm_uid"];
    }
    if (sizeof($uniqDocs)!=1) {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description="Source Document not found, or returned more than 1 row!";
      return $this->errorTO;
    }

    if (sizeof($mfT)!=sizeof($postingOrderTO->detailArr)) {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description="Source Document expected the same number of detail lines, but a variance was found!";
      return $this->errorTO;
    }

    $indexesUsed=array();
    foreach ($postingOrderTO->detailArr as $dTO) {
      $found=false;
      // can't use an array index here for direct lookups as there might be dup products
      foreach ($mfT as $key=>$row) {

        // in order to use eval() securely below, we MUST validate passed values !!
        if (!preg_match(GUI_PHP_INTEGER_REGEX,$dTO->quantity)) {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Quantity not numeric - cannot complete validateDocumentAgainstSource"; // don't let user know which product / qty for Sylko requirements
          return $this->errorTO;
        }

        if (($row["product_uid"]==$dTO->productUId) &&
               // eval("return {$dTO->quantity} {$qtyOperator} {$row["ordered_qty"]};") && //REMOVED ON 2012.12.28 BY ONYX, QUANTITY MAY BE MORE AS CAPTURED BY UNIT. - NEGATIVE CHECKING DONE ON SUBMIT.
                (!isset($indexesUsed[$key]))) {
          $indexesUsed[$key]=$key;
          $found=true;
          break;
        }

      }
      if (!$found ){
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description="The Detail Lines do not match to source document!"; // don't let user know which product / qty for Sylko requirements
        return $this->errorTO;
      }
    }

    $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
    $this->errorTO->description="Successful";

    return $this->errorTO;
  }

  public function exitPrincipalPostDocumentOverrides($postingOrderTO) {
    if ($postingOrderTO->principalUId==self::$principalMisterSweet) {
      if ((in_array($postingOrderTO->documentType,array(DT_ORDINV,DT_ORDINV_ZERO_PRICE,DT_DELIVERYNOTE))) && (($postingOrderTO->dataSource!=DS_EDI) || (!in_array($postingOrderTO->capturedBy,array("MSEVO","ITD"))))) {
        $postingOrderTO->forceDepotUId=105;   // keep a record of deliberate overrides - override depot only if source is not 2nd leg integration
        $postingOrderTO->processedDepotUId=$postingOrderTO->forceDepotUId; // slot it into normal processing to be validated
      }
    }

    if ($postingOrderTO->principalUId==self::$principalZambezi) {
      if ((in_array($postingOrderTO->documentType,array(DT_ORDINV,DT_ORDINV_ZERO_PRICE,DT_DELIVERYNOTE))) && ($postingOrderTO->dataSource==DS_EDI) && ($postingOrderTO->capturedBy=="ZAMNONRTDPT")) {
        $postingOrderTO->forceDepotUId=VAL_NON_RT_DEPOT;   // keep a record of deliberate overrides - override depot only if source is above
        $postingOrderTO->processedDepotUId=$postingOrderTO->forceDepotUId; // slot it into normal processing to be validated
      }
    }

    $this->errorTO->type=FLAG_ERRORTO_SUCCESS;

    return $this->errorTO;
  }

  /*
  // used for MrSweet in AdaptorUploadFileConfirmation to see if Leg2 originated from the RT system or not
  public function principal104_getLeg1Document($documentNumber) {
    $sql = "select 1
            from document_master c
						where c.document_number = '{$documentNumber}'
						and c.principal_uid = ".self::$principalMisterSweet."
						and c.document_type_uid in (".DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DEBITNOTE.")
						and c.depot_uid = 105";

    $arr = $this->dbConn->dbGetAll($sql);

    if (count($arr)>0) {
      return true;
    } else {
      return false;
    }

  }
  */

}
?>

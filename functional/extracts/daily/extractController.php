<?php
/**********************************************************************************************
 **********************************************************************************************
 * *
 * *    MAIN EXTRACT METHODS AND GENERAL HANDLING.
 * *
 **********************************************************************************************
 **********************************************************************************************/
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ImportDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/BIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostBIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
include_once($ROOT.$PHPFOLDER."TO/SequenceTO.php");
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER.'DAO/RemittanceDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/BillingDAO.php');
require_once($ROOT.$PHPFOLDER.'libs/storage/Storage.php');



$runMe = ((isset($_REQUEST["RUNME"]) && $_REQUEST["RUNME"]=="Y")?true:false); // if called directly from url individually
$skipInsert = ((isset($_REQUEST["SKIPINSERT"]) && $_REQUEST["SKIPINSERT"]=="Y")?true:false); // if called directly from url individually

if (!isset($dbConn)) {
  $dbConn = new dbConnect();
  $dbConn->dbConnection();
}

if (!isset($storage)) {
	$storage = new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);
}

class extractController {


  protected $skipInsert;
  public    $errorTO;
  protected $dbConn;
  protected $bIDAO;
  protected $postBIDAO;
  protected $exportDAO;
  protected $importDAO;
  protected $miscDAO;
  protected $postDistributionDAO;
  protected $postExtractDAO;
  protected $extractDAO;
  protected $sequenceDAO;
  protected $principalDAO;
  protected $productDAO;
  protected $RemittanceDAO;
  protected $BillingDAO;
  protected $fileSequence = 0;
  const setFilenameFSEQ_LenType_PAD="PAD";    // the file sequence is padded to required len
  const setFilenameFSEQ_LenType_CHOP="CHOP";  // the file seq is absoluted to remove leading zeros

  function __construct() {

      global $dbConn, $skipInsert;
      $this->dbConn = $dbConn;
      $this->skipInsert = $skipInsert;
      $this->errorTO = new ErrorTO;
      $this->bIDAO = new BIDAO($this->dbConn);
      $this->postBIDAO = new PostBIDAO($this->dbConn);
      $this->depotDAO = new DepotDAO($this->dbConn);
      $this->miscDAO = new MiscellaneousDAO($this->dbConn);
      $this->postDistributionDAO = new PostDistributionDAO($this->dbConn);
      $this->sequenceDAO = new SequenceDAO(null);
      $this->postExtractDAO = new PostExtractDAO($this->dbConn);
      $this->extractDAO = new ExtractDAO($this->dbConn);
      $this->principalDAO = new PrincipalDAO($this->dbConn);
      $this->importDAO = new ImportDAO($this->dbConn);
      $this->productDAO = new ProductDAO($this->dbConn);
      $this->RemittanceDAO = new RemittanceDAO($this->dbConn);
      $this->BillingDAO = new BillingDAO($this->dbConn);
  }


  //standized mail templates
  protected function getTemplateBody($to, $docNo, $errNo, $errURL){

    return "<div style=\"font-family:Arial,sans-serif,verdana;font-size:12px;\">Dear {$to},<br><br>
            Please find attached your 'Daily Orders Extract'.<br><br>
            <div style='color:green;'><strong>Documents:</strong> {$docNo}</div>
            <div style='color:red;'><strong>Errors:</strong> {$errNo} <a href='{$errURL}'>click here to manage</a></div>
            <br>
            Regards,<br>
            The Kwelanga Solutions Team<br><br>
            *** This is an automated email. Please do NOT reply to this email as the sending address mailbox is not monitored
            </div>";
  }

  //standized mail templates
  protected function getTemplateBodyError($to, $errNo, $errURL){

    return "<div style=\"font-family:Arial,sans-serif,verdana;font-size:12px;\">Dear {$to},<br><br>
            Please note there are error(s) against your 'Daily Orders Extract'.<br><br>
            <div style='color:red;'><strong>Errors:</strong> {$errNo} <a href='{$errURL}'>click here to manage</a></div>
            <br>
            Regards,<br>
            The Kwelanga Solutions<br><br>
            *** This is an automated email. Please do NOT reply to this email as the sending address mailbox is not monitored
            </div>";
  }

  protected function getTemplateRandPodInvoiceSubject($additional = ''){
    return "Rand Trust POD Extract " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }
  protected function getTemplateInvoiceSubject($additional = ''){
    return "Invoices Extract " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }
  protected function getTemplateSupplierInvoiceSubject($additional = ''){
    return "Supplier Invoices Extract " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }
  protected function getTemplateRandInvoiceSubject($additional = ''){
    return "Rand Trust Invoices Extract " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }
  protected function getTemplateRandCreditSubject($additional = ''){
    return "Rand Trust Credits Extract " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }
  protected function getTemplateCreditSubject($additional = ''){
    return "Credits Extract " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }
  protected function getTemplateStockBalSubject($additional = ''){
    return "Stock Balances " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }
  protected function getTemplateArrivalSubject($additional = ''){
    return "Stock Receipt Extract " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }
  protected function getTemplateAdjustmentSubject($additional = ''){
    return "Stock Adjustment Extract " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }
  protected function getTemplateDebitSubject($additional = ''){
    return "Debit Notes Extract " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }

  protected function getTemplatePurchaceOrderSubject($additional = ''){
    return "Supplier Invoice Extract " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }

  protected function getTemplateClaimSubject($additional = ''){
    return "Buyer originated Claims Extract " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }

  protected function getTemplateErrorSubject($additional = ''){
    return "Extract Errors " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }

  protected function getManagementURL($principalId){

    global $ROOT,$PHPFOLDER;

    include_once($ROOT.$PHPFOLDER."libs/EncryptionClass.php");
    $encryption = new EncryptionClass();
    $epid = $encryption->encryptUIDValue($principalId, 0, 6);

    return HOST_SURESERVER_AS_USER.'systems/kwelanga_system/r/?eem=' . $epid . '';

  }

  protected function getFileSequence($principal, $documentType = false, $len = 8, $lenType = self::setFilenameFSEQ_LenType_CHOP, $depotUId = false) {
    $getSequenceResult = false;
    $sequenceTO = new SequenceTO();
    $sequenceTO->sequenceKey    = "EXTRACTFILE";
    $sequenceTO->sequenceStart  = 0;
    $sequenceTO->sequenceLen    = $len;
    $sequenceTO->documentTypeUId = $documentType;
    $sequenceTO->principalUId   = $principal;
    $sequenceTO->depotUId = $depotUId;
    $seqResult = $this->sequenceDAO->getSequence($sequenceTO, $getSequenceResult);
    if($getSequenceResult==false){
      return false;
    }
    $getSequenceResult = substr($getSequenceResult,-$len);

    if ($lenType==self::setFilenameFSEQ_LenType_CHOP) $this->fileSequence = abs($getSequenceResult);
    else if ($lenType==self::setFilenameFSEQ_LenType_PAD) $this->fileSequence = $getSequenceResult;
    else return false;

    return $this->fileSequence;
  }
    protected function getRemitFileSequence($principal, $documentType = false, $len = 8, $lenType = self::setFilenameFSEQ_LenType_CHOP, $depotUId = false) {
    $getSequenceResult = false;
    $sequenceTO = new SequenceTO();
    $sequenceTO->sequenceKey    = "EXTRACTRMFILE";
    $sequenceTO->sequenceStart  = 0;
    $sequenceTO->sequenceLen    = $len;
    $sequenceTO->documentTypeUId = $documentType;
    $sequenceTO->principalUId   = $principal;
    $sequenceTO->depotUId = $depotUId;
    $seqResult = $this->sequenceDAO->getSequence($sequenceTO, $getSequenceResult);
    if($getSequenceResult==false){
      return false;
    }
    $getSequenceResult = substr($getSequenceResult,-$len);

    if ($lenType==self::setFilenameFSEQ_LenType_CHOP) $this->fileSequence = abs($getSequenceResult);
    else if ($lenType==self::setFilenameFSEQ_LenType_PAD) $this->fileSequence = $getSequenceResult;
    else return false;

    return $this->fileSequence;
  }

  protected function getBillingFileSequence($principal, $documentType = false, $len = 8, $lenType = self::setFilenameFSEQ_LenType_CHOP, $depotUId = false) {
    $getSequenceResult = false;
    $sequenceTO = new SequenceTO();
    $sequenceTO->sequenceKey    = "DOCNUM";
    $sequenceTO->sequenceStart  = 0;
    $sequenceTO->sequenceLen    = $len;
    $sequenceTO->documentTypeUId = $documentType;
    $sequenceTO->principalUId   = $principal;
    $sequenceTO->depotUId = $depotUId;
    $seqResult = $this->sequenceDAO->getSequence($sequenceTO, $getSequenceResult);

    if($getSequenceResult==false){
      return false;
    }
    $getSequenceResult = substr($getSequenceResult,-$len);

    if ($lenType==self::setFilenameFSEQ_LenType_CHOP) $this->fileSequence = abs($getSequenceResult);
    else if ($lenType==self::setFilenameFSEQ_LenType_PAD) $this->fileSequence = $getSequenceResult;
    else return false;

    return $this->fileSequence;
  }
  protected function getDocumentFileSequence($principal, $documentType = false, $len = 8, $lenType = self::setFilenameFSEQ_LenType_CHOP, $depotUId = false) {
    $getSequenceResult = false;
    $sequenceTO = new SequenceTO();
    $sequenceTO->sequenceKey    = "DOCFILE";
    $sequenceTO->sequenceStart  = 0;
    $sequenceTO->sequenceLen    = $len;
    $sequenceTO->documentTypeUId = $documentType;
    $sequenceTO->principalUId   = $principal;
    $sequenceTO->depotUId = $depotUId;
    $seqResult = $this->sequenceDAO->getSequence($sequenceTO, $getSequenceResult);

    if($getSequenceResult==false){
      return false;
    }
    $getSequenceResult = substr($getSequenceResult,-$len);

    if ($lenType==self::setFilenameFSEQ_LenType_CHOP) $this->fileSequence = abs($getSequenceResult);
    else if ($lenType==self::setFilenameFSEQ_LenType_PAD) $this->fileSequence = $getSequenceResult;
    else return false;

    return $this->fileSequence;
  }  

  protected function setFilenameFSEQ($filename, $principal, $documentType = false, $len = 8, $lenType = self::setFilenameFSEQ_LenType_CHOP, $depotUId = false){

    //GET FILE EXTRACT SEQ.

    $getSequenceResult = $this->getFileSequence($principal,
                                               $documentType,
                                               $len,
                                               $lenType,
                                               $depotUId);

    //SET FILENAME
    if (
        ($lenType==self::setFilenameFSEQ_LenType_CHOP) ||
        ($lenType==self::setFilenameFSEQ_LenType_PAD)
        ) return str_replace('[@FSEQ]', $getSequenceResult, $filename);
    else return false;

  }



  protected function createFile($folder, $filename, $data){

    global $ROOT, $PHPFOLDER;

	//upload to storage
	$storageFilename = FILE_ARCHIVE_EXTRACTS_PATH . trim($folder) . "/" . date("Y") . "/" . date("m") . "/" . $filename;
	$storageUploadResult = Storage::putObject(S3_BUCKET_NAME, $storageFilename, $data);
	if(!$storageUploadResult){
		echo "storage error: " . $storageUploadResult . "\n";
		return false;
	}

	return $storageFilename;

    //path and backup folder creation.
    //$fP = $ROOT . FILE_ARCHIVE_EXTRACTS_PATH . $folder . "/";		
    //@mkdir($fP, 0777, true);
    //$bkupFolder = CommonUtils::createBkupDirs($fP);

    //if(!empty($bkupFolder)){

    //  // create file
    //  $fh = file_put_contents($bkupFolder . $filename, $data);
    //  return ($fh != strlen($data))?(false):(str_replace("../", "", $bkupFolder . $filename));

    //} else {
    //  return false;
    //}

  }


}


/*----------------------------------------------------*/
/*      EXTRACT HELPER FUNCTIONS
/*----------------------------------------------------*/
function directRunExtract($f){
  global $dbConn;
  $className = basename($f,'.php');
  $obj = new $className();
  $r = $obj->generateOutput();
  if($r->type == FLAG_ERRORTO_SUCCESS){
    $dbConn->dbinsQuery("commit");
  }
}

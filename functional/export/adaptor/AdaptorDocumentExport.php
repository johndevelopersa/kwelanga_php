<?php


/* -------------------------------------------------
 *
 *    Adaptor Document Export Export
 *    Handles various depot EDI exports.
 *
 * -------------------------------------------------*/

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExportDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ImportDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostTransactionDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');


class AdaptorDocumentExport {


    private $dbConn;
    private $postingOrderTO;
    private $exportDAO;
    private $importDAO;
    private $exportMappingArr = array();
    private $postTransactionDAO;
    private $storeDAO;
    private $mfStoreArr = array();
    private $productDAO;
    private $principalDAO;
    private $sequenceDAO;
    private $miscellaneousDAO;


    function __construct($dbConn){
      $this->dbConn = $dbConn;
      $this->exportDAO = new ExportDAO($this->dbConn);
      $this->importDAO = new ImportDAO($this->dbConn);
      $this->sequenceDAO = new SequenceDAO(null);
      $this->postTransactionDAO = new PostTransactionDAO($this->dbConn);
    }


    /*  -----------------------------------------
     *  Export Handler - Top level
     *  -----------------------------------------
     *  Determines:
     *  1. If export should be created -> in online_export_mapping table (returns true if not setup)
     *  2. Determines the export adaptor to use, based on settings in online_export_mapping.
     *  3  Updates orders(table) edi_depot* db flag and filename.
     *
     *  Returns bool: true/false.
     *  The calling script does not fail or handle returned value at time of coding.
     *
     */
    public function generateExport($postingOrderTO, $readOnlyStoreDAO = false, $readOnlyProductDAO = false, $readOnlyPrincipalDAO = false){

      //set variables.
      global $ROOT, $PHPFOLDER;
      $this->postingOrderTO = $postingOrderTO;
      include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
      $this->miscellaneousDAO = new MiscellaneousDAO($this->dbConn);

      if (!$readOnlyStoreDAO){
        include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
        $this->storeDAO = new StoreDAO($this->dbConn);
      } else $this->storeDAO = $readOnlyStoreDAO;
      if (!$readOnlyProductDAO){
        include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
        $this->productDAO = new ProductDAO($this->dbConn);
      } else $this->productDAO = $readOnlyProductDAO;
      if (!$readOnlyPrincipalDAO){
        include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
        $this->principalDAO = new PrincipalDAO($this->dbConn);
      } else $this->principalDAO = $readOnlyPrincipalDAO;


      //GET Store for Depot
      $this->mfStoreArr = $this->storeDAO->getPrincipalStoreItem($postingOrderTO->storeChainUId);
      if(sizeof($this->mfStoreArr)==0){
        BroadcastingUtils::sendAlertEmail("Error in AdaptorDocumentExport->generateExport", "The Export EDI file could not be created due to missing store details!", "Y", $quietMode = false);
        return false;
      }

      /* -------------------------------------
       *    FETCH ONLINE EXPORT MAPPING
       * -------------------------------------*/

      //"Depot"(D) Export Type only catered for currently.
      // Add additional handling here for when adding principal depot exports
      $this->exportMappingArr = $this->exportDAO->getOnlineExportMappingbyType('D', $postingOrderTO->processedDepotUId);

      if(sizeof($this->exportMappingArr)==0){

        return true;  //no export setup => Might need to add additional handling here.

      } else {

        //EXCLUDE PRINCIPAL EXTRACTS
        $excludePrincipalsArr = explode(',', trim($this->exportMappingArr[0]['principal_exclude_list']));
        if(in_array($this->postingOrderTO->principalUId, $excludePrincipalsArr)){
          return true;  //fake OK.
        }

        $adaptorName = 'adaptor'.$this->exportMappingArr[0]['adaptor_name'];  //method name that handles adaptor output/format.
        if(!method_exists ($this, $adaptorName)){

          BroadcastingUtils::sendAlertEmail("Error in AdaptorDocumentExport->generateExport", "The Export Adaptor '".$adaptorName."' could not be found or is misspelt, check online_export_mapping table!", "Y", $quietMode = false);
          return false;

        } else {

          $resultTO = $this->$adaptorName();
          if($resultTO->type == FLAG_ERRORTO_INFO){
            // depot extract bypassed through internal logic
            return true;

          } else if($resultTO->type != FLAG_ERRORTO_SUCCESS){

            BroadcastingUtils::sendAlertEmail("Error in AdaptorDocumentExport->".$adaptorName, $resultTO->description, "Y", $quietMode = false);
            return false;

          } else {

            //update edi_depot* fields in orders(table)
            $result = $this->postTransactionDAO->closeDocumentDepotEDI($postingOrderTO->orderSequenceNo, $resultTO->identifier); // updates the flag against order to say file created properly
            if($result->type!=FLAG_ERRORTO_SUCCESS){

              //update failed.
              $this->dbConn->dbinsQuery("rollback;");
              BroadcastingUtils::sendAlertEmail("Error in AdaptorDocumentExport->generateExport", "The orders table could not be updated after EDI Export file was generated!\n". $result->description, "Y", $quietMode = false);
              return false;
            } else {

              /* -------------------------------------
               *    FILE EXPORT SUCCESSFUL
               * -------------------------------------*/

              $this->dbConn->dbinsQuery("commit");
              return true;

              /* -------------------------------------*/

            }
          }
        }
      }

    }

    /* -------------------------------
     *    VITAL DISTRIBUTION GENERIC FORMAT
     * -------------------------------*/
    private function adaptorVITAL1(){  //no param - uses properties of class.


      /* ----------------------
       *  EXAMPLE filenames:
       * ----------------------
       * ORDVJ.086 => VITAL Johannesburg */

      /* ----------------------
       * EXAMPLE format - CSV
       * ----------------------
        Layout for Sales Order Import File

        Principal CODE                  Supplied by Vital
        Order type                      NO or UP  NO = Normal Order : UP = Uplift
        Order date                      20121001  yyyymmdd
        Sales Order number              11207131
        Invoice Number                  Not required if VDS generates the Invoice
        Stores Purchase Order #         4197032464
        Principal's Sold-to Account     SHO018  Account Number to be printed on the Invoice
        Customer Store name             SHOPRITE CHECKERS PTY LTD
        Product Code                    20001
        Description                     Dinu Bathroom Tissue 2 ply
        Quantity ordered                3
        Sales unit                      Case
        Gross price                     258.39
        Discount Percentage             10.00 % Percentage
        Net price                       233.45
        Valuation tax incl.             798.40
        Principal's Bill to Account     SHO000  Account Number to be printed on the Invoice
        Distributors (VDS) Account No.  SHP027  Will be used to load and route the order.
        TRAILER                         T+file line count inclusive

       */


      /*******************
      *     VARIABLES
      *******************/

      //GENERAL VARIABLES
      global $ROOT;
      $eTO = new ErrorTO;
      $newline = "\r\n";
      $errSubject = "Error in AdaptorDocumentExport->adaptorVITAL1";

      //region code ie: KZN, BEN etc.
      // $paramStr = $this->exportMappingArr[0]['additional_parameter_string'];
      //$regionCode = str_pad(substr($regionCode,0,3), 3, " ");

      // principal UIDs
      $principalQualityProducts = "37";

      // DEPOT VARIABLES
      $depotMap = $this->importDAO->getAllDepotPrincipalExportMapping($indexArray=ImportDAO::$PDP_EXP_MAP_INDEX_2);

      $documentTypeArr = array(DT_ORDINV=>"YES",
                               DT_ORDINV_ZERO_PRICE=>"NO",
                               DT_UPLIFTS=>"NO",
                               DT_MCREDIT_OTHER=>"NO");     

     if ($documentTypeArr[$this->postingOrderTO->documentType]=="UP") {
        $eTO->type= FLAG_ERRORTO_SUCCESS;
        $eTO->identifier = 'Uplift / Manual Credit Not Extracted';
        return $eTO;      
     } 
      if ((!isset($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId])) || (trim($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId]["principal_code"])=="")) {
        BroadcastingUtils::sendAlertEmail($errSubject, "Empty Principal-Depot Code! principalUid@" . $this->postingOrderTO->principalUId." depot@{$this->postingOrderTO->processedDepotUId}", "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      } else {
        $prinCode = trim($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId]["principal_code"]);
      }

      if(!isset($documentTypeArr[$this->postingOrderTO->documentType]) || ($documentTypeArr[$this->postingOrderTO->documentType]=="")){
        BroadcastingUtils::sendAlertEmail($errSubject, "Unknown Document Type Code! principalUid@" . $this->postingOrderTO->principalUId, "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      } else {
        $prinDocTypeCode = $documentTypeArr[$this->postingOrderTO->documentType];
      }

      $mfSF = $this->miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($this->postingOrderTO->principalUId, 561, $this->postingOrderTO->storeChainUId, CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      $vitalAccount = $mfSF[$this->postingOrderTO->storeChainUId]['value'];
      //GET STORE SPECIAL FIELDs
      // these cannot be blank - the order would not have been allowed to be saved otherwise
//       $vitalAccount = $this->postingOrderTO->storeChainUId;
//      $mfSF = $this->miscellaneousDAO->getPrincipalSpecialFieldValues($this->postingOrderTO->principalUId, $this->postingOrderTO->storeChainUId, "S", "processing_order", "processing_order");
//      $vitalAccount = (isset($mfSF["3"]['entity_uid']) && (trim($mfSF["3"]['entity_uid']) != "")) ? (trim($mfSF["3"]['entity_uid'])) : (FALSE);
//      $sysproAccount = (isset($mfSF["1"]['entity_uid']) && (trim($mfSF["1"]['entity_uid']) != "")) ? (trim($mfSF["1"]['entity_uid'])) : (FALSE);

      $seq = $this->sequenceDAO->getFTPFileExportSequence();
      $filename = str_replace('[@SEQ]', $seq, $this->exportMappingArr[0]['output_filename']); //replace seq placeholder in filename provided.
      $filename = str_replace('[@DNUM]',$this->postingOrderTO->documentNumber, $filename);
      $filename = str_replace('[@PCDE]',$prinCode, $filename);

      /* ------------------------------------
       *          FILE CONTENTS
       * ------------------------------------*/

      $fLines="";
      foreach($this->postingOrderTO->detailArr as $detailRow){

        //GET Product
        $productArr = $this->productDAO->getPrincipalProductItem($this->postingOrderTO->principalUId,$detailRow->productUId);
        if (sizeof($productArr)==0){
          BroadcastingUtils::sendAlertEmail($errSubject, "The DEPOT Export file could not be created due to missing PRODUCT details! @principalUid:".$this->postingOrderTO->principalUId."; @productUid:".$detailRow->productUId, "Y", $quietMode = false);
          $eTO->type = FLAG_ERRORTO_ERROR;
          return $eTO;
        }

        //detail variables
        //convert the product description to ASCII - we dont want funny chars being sent out that might be stored in our database already.
        $productDesc = mb_convert_encoding(str_replace(array("\t", "\n" ,"\r"), array(" ","",""), $productArr[0]['product_description']), 'ASCII');


        $discPerc=(($detailRow->listPrice>0)?(round($detailRow->discountValue/$detailRow->listPrice*100,2)):"0");
        /*---------- DETAIL ROW ----------*/
        $fLines .=  "VDKZN,".
                    $prinCode.",".
                    $prinDocTypeCode.",".
                    str_replace('-', '', $this->postingOrderTO->documentDate).",".
                    substr($prinCode,1,2) ."-".$this->postingOrderTO->documentNumber.",".
                    $this->postingOrderTO->orderNumber.",".
                    $vitalAccount.",".
                    $this->mfStoreArr[0]['store_name'].",".
                    ",".
                    $productArr[0]['product_code'].",".
                    $productDesc.",".
                    $detailRow->quantity.",".
                    "Case,".
                    round($detailRow->listPrice,2).",".
                    $discPerc.",".
                    round($detailRow->nettPrice,2).",".
                    ",".
                    ",".
                    ",".
                    $newline;
      }
      /*------------------------------------*/


      //PATH AND LOCATION OF FILE
      $localPath = $ROOT . FILE_ARCHIVE_EXPORTS_PATH . $this->exportMappingArr[0]['folder_name'];
      if(!is_dir($localPath)){
        mkdir($localPath, 0777, TRUE);  //create recursive directory based on path.
      }
      
      $bytesWrit = @file_put_contents($localPath . $filename, $fLines);
      if($bytesWrit == strlen($fLines)){
        /*------  SUCCESSFUL ------*/
        $eTO->type= FLAG_ERRORTO_SUCCESS;
        $eTO->identifier = $filename;
        return $eTO;
      } else {
        BroadcastingUtils::sendAlertEmail($errSubject, "The DEPOT Export file could not be created @principalUid:".$this->postingOrderTO->principalUId."; @path:".$localPath . $filename, "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      }


    }

  private function adaptorBRENNERMILLS(){
    global $ROOT;
    $eTO = new ErrorTO;
    $newline = "\r\n";
    $errSubject = "Error in AdaptorDocumentExport->adaptorBRENNERMILLS";

    //region code ie: KZN, BEN etc.
    $paramStr = $this->exportMappingArr[0]['additional_parameter_string'];
    // $regionCode = trim(CommonUtils::getParamValuesFromString($paramStr,"p1"));
    // $regionCode = str_pad(substr($regionCode,0,3), 3, " ");



    //PRINCIPAL VARIABLES.
    $prinArr = $this->principalDAO->getPrincipalItem($this->postingOrderTO->principalUId);
    if(sizeof($prinArr)==0){
      BroadcastingUtils::sendAlertEmail($errSubject, "Missing Principal Details or Query Failure! principalUid@" . $this->postingOrderTO->principalUId, "Y", $quietMode = false);
      $eTO->type = FLAG_ERRORTO_ERROR;
      return $eTO;
    }

    if(empty($prinArr[0]['principal_code'])){ //doubles up as isset.
      BroadcastingUtils::sendAlertEmail($errSubject, "Empty Principal Code! principalUid@" . $this->postingOrderTO->principalUId, "Y", $quietMode = false);
      $eTO->type = FLAG_ERRORTO_ERROR;
      return $eTO;
    } else {
      $prinCode = str_pad($prinArr[0]['principal_code'], 3, "0", STR_PAD_LEFT); //pad to 3 if not already done so.
    }

    $sfBrenncoRegion = $this->miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($this->postingOrderTO->principalUId, 237, $this->postingOrderTO->storeChainUId, CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    $sfBrenncoAcc = $this->miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($this->postingOrderTO->principalUId, 236, $this->postingOrderTO->storeChainUId, CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    // $sfNNBAcc = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->postingOrderTO->principalUId, 8, $this->postingOrderTO->storeChainUId, CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    // $sfNNB2Acc = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->postingOrderTO->principalUId, 196, $this->postingOrderTO->storeChainUId, CT_STORE_SHORTCODE, $arrayIndex="entity_uid");

    /*-------------------------------------------------*/
    //    calculate which sp field to use
    /*-------------------------------------------------*/
    $buyerRef = abs($this->postingOrderTO->buyerAccountReference);

    // the first 3 accounts below are the old codes included to maintain overlap problems, can be removed in due course
    if($buyerRef == '40009124' || $buyerRef == '30006184' || $buyerRef == '90006095' || $buyerRef == '1000002810'){
      //PICK N PAY BRAND PET FOOD     1000002810  NNB Account number 40-100…
      $chosenSFVal = $sfNNBAcc;
    } else if($buyerRef == '1000001066'){
      //PICK N PAY BRAND BEANS                           1000001066                                         NNB2 Account number 40-2100…
      $chosenSFVal = $sfNNB2Acc;
    } else {

      //IF CAPTURED AND PRODUCT CODES BEGIN WITH 9 AND NOT 980 THEN USE NNB1 ACC.
      if(substr(trim($this->postingOrderTO->detailArr[0]->productCode),0,1) == '9' && substr($this->postingOrderTO->detailArr[0]->productCode,0,3)!='980'){
        $chosenSFVal = $sfNNBAcc;
      } else {
        //DEFAULT TO BRENNCO ACOCUNT : CAPTURE AND GPH
        $chosenSFVal = $sfBrenncoAcc;
      }
    }
    /*-------------------------------------------------*/


    if(
        empty($sfBrenncoRegion[$this->postingOrderTO->storeChainUId]['value']) ||  //$sfBrenncoRegion must always be set.
        //empty($sfBrenncoAcc[$this->postingOrderTO->storeChainUId]['value']) ||
        empty($chosenSFVal[$this->postingOrderTO->storeChainUId]['value']) //might be the brennco account again... depending on above logic.
      ){

      // for the time being do nothing as the s/f required configuration should have blocked the order from being processed
      // however, the chosenSFVal above might be blank

    }


    $regionCode = trim($sfBrenncoRegion[$this->postingOrderTO->storeChainUId]['value']);
    $storeAccount = trim($chosenSFVal[$this->postingOrderTO->storeChainUId]['value']);

    /*-------------------------------------------------*/
    /* START BUILDING OUTPUT
     */

    $dataH = array();
    $dataH[] = 'H'; //header
    $dataH[] = abs($this->postingOrderTO->documentNumber); //doc number for inv and cre.
    $dataH[] = $storeAccount;
    $dataH[] = date("dmy", strtotime($this->postingOrderTO->documentDate));  //DATE
    $dataH[] = '40-' . $regionCode;
    $dataH[] = str_replace(array(',','"',"'"), array('','',''), trim($this->postingOrderTO->orderNumber));
    $dataH[] = abs($this->postingOrderTO->documentNumber); //doc number for inv and cre.
    $dataH[] = ' '; //blank
    $dataH[] = trim($this->mfStoreArr[0]["branch_code"]); //blank
    $dataH[] = ' '; //blank
    $dataH[] = $regionCode . '-' . str_pad(substr(abs($this->postingOrderTO->documentNumber),-6), 6, '0', STR_PAD_LEFT); // should be invoice number in extract
    $dataArr[] = str_pad(join(',', $dataH),500 , ' ', STR_PAD_RIGHT);

    //detail rows
    foreach($this->postingOrderTO->detailArr as $d){ //detail rows.
      $dataD = array();
      $dataD[] = 'D'; //detail
      $dataD[] = str_replace(array("\t","\r","'",'"',','),array('','','','',''), trim($d->productCode));
      $dataD[] = abs($d->quantity);
      $dataD[] = number_format(round($d->nettPrice, 2), 2, '.', '');
      $dataArr[] = str_pad(join(',', $dataD),500 , ' ', STR_PAD_RIGHT);
    } //eo detail

    $data = join("\r\n",$dataArr);  //build file.


    /*******************
     *     FILENAME
    *******************/
    $seq = substr($this->sequenceDAO->getFTPFileExportSequence(),-5);  //is 6 char, clip to 5.
    $filename = str_replace('[@SEQ]', $seq, $this->exportMappingArr[0]['output_filename']);       //replace seq placeholder in filename provided.

    //PATH AND LOCATION OF FILE
    $localPath = $ROOT . FILE_ARCHIVE_EXPORTS_PATH . $this->exportMappingArr[0]['folder_name'];
    if(!is_dir($localPath)){
      mkdir($localPath, 0777, TRUE);  //create recursive directory based on path.
    }

    $bytesWrit = @file_put_contents($localPath . $filename, $data);
    if($bytesWrit == strlen($data)){
      /*------  SUCCESSFUL ------*/
      $eTO->type= FLAG_ERRORTO_SUCCESS;
      $eTO->identifier = $filename;
      return $eTO;
    } else {
      BroadcastingUtils::sendAlertEmail($errSubject, "The DEPOT Export file could not be created @principalUid:".$this->postingOrderTO->principalUId."; @path:".$localPath . $filename, "Y", $quietMode = false);
      $eTO->type = FLAG_ERRORTO_ERROR;
      return $eTO;
    }


  }
// *********************************************************************************************************************************************
// *********************************************************************************************************************************************
// *********************************************************************************************************************************************
    private function adaptorIMPERIALLOGISTICS() {  //no param - uses properties of class.

      /*******************
      *     VARIABLES
      *******************/

      //GENERAL VARIABLES
      global $ROOT;
      $eTO = new ErrorTO;
      $newline = "\r\n";
      $errSubject = "Error in AdaptorDocumentExport->adaptorIMPERIALLOGISTICS";

      // DEPOT VARIABLES
      $depotMap = $this->importDAO->getAllDepotPrincipalExportMapping($indexArray=ImportDAO::$PDP_EXP_MAP_INDEX_2);

      $documentTypeArr = array(DT_ORDINV=>"NO",
                               DT_ORDINV_ZERO_PRICE=>"NO",
                               DT_UPLIFTS=>"UP",
                               DT_MCREDIT_OTHER=>"UP",
                               DT_ARRIVAL=>"UP",
                               DT_DELIVERYNOTE=>"UP");
 
      if ($documentTypeArr[$this->postingOrderTO->documentType]=="UP") {
        $eTO->type= FLAG_ERRORTO_SUCCESS;
        $eTO->identifier = 'Uplift Not Extracted';
        return $eTO;      
     } 
      if ((!isset($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId])) || (trim($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId]["principal_code"])=="")) {
        BroadcastingUtils::sendAlertEmail($errSubject, "Empty Principal-Depot Code! principalUid@" . $this->postingOrderTO->principalUId." depot@{$this->postingOrderTO->processedDepotUId}", "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      } else {
        $prinCode = trim($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId]["principal_code"]);
      }

      if(!isset($documentTypeArr[$this->postingOrderTO->documentType]) || ($documentTypeArr[$this->postingOrderTO->documentType]=="")){
        BroadcastingUtils::sendAlertEmail($errSubject, "Unknown Document Type Code! principalUid@" . $this->postingOrderTO->principalUId, "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      } else {
        $prinDocTypeCode = $documentTypeArr[$this->postingOrderTO->documentType];
      }

//    Set up the valus that need to be Hard Coded for an IRL Principal
//    1. Partner GUID
      if ($this->postingOrderTO->principalUId == 74) {
          $partnerguid = 'CAPEHERBSPICE';
          $Companyguid = '6001553000008';
      } elseif ($this->postingOrderTO->principalUId == 293) {
          $partnerguid = 'NaturalHerbs';
          $Companyguid = '6001553000008';
      } elseif ($this->postingOrderTO->principalUId == 305) {
          $partnerguid = 'HoneyFields'; 
          $Companyguid = '6001553000008';
      } else {               
        BroadcastingUtils::sendAlertEmail($errSubject, "Unknown Partner GUID! principalUid@" . $this->postingOrderTO->principalUId, "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
     }
//    2. Special Fields
      if(	$this->postingOrderTO->principalUId == 74) {
          $prinspecialfield = 339;
      } elseif ($this->postingOrderTO->principalUId == 293) {
          $prinspecialfield = 338;
      } elseif ($this->postingOrderTO->principalUId == 305) {
          $prinspecialfield = 343;
      } else {
          BroadcastingUtils::sendAlertEmail($errSubject, "Principal Special Field! principalUid@" . $this->postingOrderTO->principalUId, "Y", $quietMode = false);
          $eTO->type = FLAG_ERRORTO_ERROR;
          return $eTO;
      } 

      $sfchsaccount = $this->miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($this->postingOrderTO->principalUId, $prinspecialfield, $this->postingOrderTO->storeChainUId, CT_STORE_SHORTCODE, $arrayIndex="entity_uid");

// print_r($sfchsaccount);


      $seq = $this->sequenceDAO->getFTPFileExportSequence();
      $filename = str_replace('[@SEQ]', $seq, $this->exportMappingArr[0]['output_filename']); //replace seq placeholder in filename provided.
      $filename = str_replace('[@DNUM]',$this->postingOrderTO->documentNumber, $filename);
      
      $linecount = 0;

      /* ------------------------------------
       *          FILE CONTENTS
       * ------------------------------------*/

    $dataO = array();
    $dataO[] = '000';                                                        // RecordType
    $dataO[] = '1';                                                          // Version
    $dataO[] = $partnerguid;                                                           // PartnerGUID x 13 m 
    $dataO[] = $Companyguid;                                                           // CompanyGUID x 13 m 
    $dataO[] = date("Ymd") ;                                                 // DateOfPreparation d  m 
    $dataO[] = date("Hi");                                                   // TimeOfPreparation t  m 
    $dataO[] = $seq;                                                         // PartnerTransmissionNumber n  m sequential and unique per partner
    $dataO[] = $seq;                                                         // SwitchTransmissionNumber n  m sequential and unique per switch
    $dataO[] = '';                                                           // <do not delete>    
  
    $dataArr[] = join(chr(253), $dataO);
    
    $linecount++;

    if(	$this->postingOrderTO->principalUId == 74) {
          $custAccount=((isset($sfchsaccount[$this->postingOrderTO->storeChainUId]))? $sfchsaccount[$this->postingOrderTO->storeChainUId]['value']:"");
    } elseif ($this->postingOrderTO->principalUId == 293) {
          $custAccount=((isset($sfchsaccount[$this->postingOrderTO->storeChainUId]))? $sfchsaccount[$this->postingOrderTO->storeChainUId]['value']:"");
    } elseif ($this->postingOrderTO->principalUId == 305) {
          $custAccount = $this->postingOrderTO->storeChainUId;
    }

    $dataH = array();
    $dataH[] = '910';                                                        // Line type
    $dataH[] = '1';                                                          // Version
    $dataH[] = $custAccount;                                                 // PrincipalsStoreCode
    $dataH[] = date("Ymd", strtotime($this->postingOrderTO->documentDate));  //DATE
    $dataH[] = '';                                                           // Order #    
    $dataH[] = $this->postingOrderTO->orderNumber;                           // Customer Order Number
    $dataH[] = abs($this->postingOrderTO->documentNumber);                   // PrincipalsOrder#
    $dataH[] = abs($this->postingOrderTO->documentNumber);                   // PrincipalsInvoice#
    $dataH[] = date("Ymd", strtotime($this->postingOrderTO->documentDate));  // Expected Delivery Date
    $dataH[] = '';                                                           // SpecialInstructions1
    $dataH[] = '';                                                           // SpecialInstructions2
    $dataH[] = '';                                                           // DcCode x 2 ps 
    $dataH[] = $prinCode;                                                    // PrincipalCode x 3 m 
    $dataH[] = '';                                                           // PrincipalsXtraData x  o Will be passed back in return messages
    $dataH[] = 'N';                                                          // JabOrder? x 1 o Indicate if the order is a JAB (Y/N)
    $dataH[] = $custAccount;                                                 // PAcctCodeOnSlsCoc x 14 o PrincipalAccountCode to be printed on Sales Document
    $dataH[] = $this->mfStoreArr[0]['store_name']  ;                         //  StoreNameOnSlsDoc

    $dataH[] = '';                                                           // <do not delete>    
    $dataH[] = '';                                                           // StoreCode    <internal use>
    $dataH[] = '';                                                           // <internal use>
    $dataH[] = '';                                                           // ExpectedDeliveryDateGuide    <internal use>
    $dataH[] = '';                                                           // DcCode    <internal use>
    $dataH[] = '';                                                           // InvoiceDcCode    <internal use>
    $dataH[] = '';                                                           // AccountCode    <internal use>
    $dataH[] = '';                                                           // RepCode    <internal use>
    $dataH[] = '';                                                           // InvoicerCode    <internal use>
    $dataH[] = '';                                                           // <do not delete>    
    $dataH[] = '';                                                           // InvoiceNrSource    <internal use>
    $dataH[] = '';                                                           // PriceSource    <internal use>
    $dataH[] = '';                                                           // DcCodeSource    <internal use>
    $dataH[] = '';                                                           // <do not delete>

    $dataArr[] = join(chr(253), $dataH);

    $linecount++;
      foreach($this->postingOrderTO->detailArr as $detailRow){

        //GET Product
        $productArr = $this->productDAO->getPrincipalProductItem($this->postingOrderTO->principalUId,$detailRow->productUId);
        if (sizeof($productArr)==0){
          BroadcastingUtils::sendAlertEmail($errSubject, "The DEPOT Export file could not be created due to missing PRODUCT details! @principalUid:".$this->postingOrderTO->principalUId."; @productUid:".$detailRow->productUId, "Y", $quietMode = false);
          $eTO->type = FLAG_ERRORTO_ERROR;
          return $eTO;
        }

        //detail variables
        //convert the product description to ASCII - we dont want funny chars being sent out that might be stored in our database already.
        $productDesc = mb_convert_encoding(str_replace(array("\t", "\n" ,"\r"), array(" ","",""), $productArr[0]['product_description']), 'ASCII');


        $discPerc=(($detailRow->listPrice>0)?(round($detailRow->discountValue/$detailRow->listPrice*100,2)):"0");
        
        /*---------- DETAIL ROW ----------*/
        
       $dataD = array();
       $dataD[] = '911';                                                  // LineType

       $dataD[] =  str_pad($detailRow->pageNo,2,"0",STR_PAD_LEFT) . str_pad($detailRow->lineNo,2,"0",STR_PAD_LEFT) ;  // LineReference n 4 m unique and ascending within the order
       $dataD[] =  $productArr[0]['product_code'];                        //  PrincipalsStockCode
       $dataD[] =  abs($detailRow->quantity);                                     //  Quantity n 6 m number of selling units
       $dataD[] =  '';                                                    //  SinglesPerSunit n 6 o 
       $dataD[] =  number_format(round($detailRow->listPrice, 2), 2, '.', '');    //  SaleUnitGrossPrice m.5 11 ps Sale unit price before discount (excl of vat)
       $dataD[] =  '';                                                    //  PromotionDeal# x 4 ps NB! Cannot be used with the discount value
       $dataD[] =  '';                                                    //  FreeGoods? ? 1 o
       $dataD[] =  $discPerc;                                             //  PromotionDiscount% m.2 5 o 
       $dataD[] =  '';                                                    //  PermanentDiscount% m.2 5 o 
       $dataD[] =  '';                                                    //  PrincipalsXtraLineData x  o Will be passed back in return messages
       $dataD[] =  '';                                                    //  StockSetCode x 15 o Supplied by the principal and must be accompanied by the SunitsPerSet (Only numeric or alphabetic characters - no spaces)
       $dataD[] =                                                         //  SunitsPerSet n 5 o Required when StockSetCode is populated

       $dataD[] = '';                                                     //  <do not delete>    
       $dataD[] = '';                                                     //  StockCode    <internal use>
       $dataD[] = '';                                                     //  OrderGroupCode    <internal use>
       $dataD[] = '';                                                     //  PickwarehouseCode    <internal use>
       $dataD[] = '';                                                     //  <do not delete>    

       $dataArr[] = join(chr(253), $dataD);
       $linecount++;
      }
      /*------------------------------------*/

    $linecount++;
    
    $dataT2 = array();
    $dataT2[] = '919';                                                  // Line type
    $dataT2[] = str_pad($linecount,3,"0",STR_PAD_LEFT);                 // messsage
    $dataT2[] = '';                                                     //  <do not delete>  

    $dataArr[] = join(chr(253), $dataT2);

    $data = join("\r\n",$dataArr);  //build file.

      //PATH AND LOCATION OF FILE
      $localPath = $ROOT . FILE_ARCHIVE_EXPORTS_PATH . $this->exportMappingArr[0]['folder_name'];
      if(!is_dir($localPath)){
        mkdir($localPath, 0777, TRUE);  //create recursive directory based on path.
      }


      $bytesWrit = @file_put_contents($localPath . $filename, $data);
      if($bytesWrit == strlen($data)){
        /*------  SUCCESSFUL ------*/
        $eTO->type= FLAG_ERRORTO_SUCCESS;
        $eTO->identifier = $filename;
        return $eTO;
      } else {
        BroadcastingUtils::sendAlertEmail($errSubject, "The DEPOT Export file could not be created @principalUid:".$this->postingOrderTO->principalUId."; @path:".$localPath . $filename, "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      }


    }
// *********************************************************************************************************************************************
// *********************************************************************************************************************************************
// *********************************************************************************************************************************************
    private function adaptorSYSTEMEXTRACT() {  //no param - uses properties of class.

      /*******************
      *     VARIABLES
      *******************/

      //GENERAL VARIABLES
      global $ROOT;
      $eTO = new ErrorTO;
      $newline = "\r\n";
      $errSubject = "Error in AdaptorDocumentExport->adaptorSYSTEMEXTRACT";
      
      // DEPOT VARIABLES
      $depotMap = $this->importDAO->getAllDepotPrincipalExportMapping($indexArray=ImportDAO::$PDP_EXP_MAP_INDEX_2);

      $documentTypeArr = array(DT_ORDINV=>"NO",
                               DT_ORDINV_ZERO_PRICE=>"NO",
                               DT_UPLIFTS=>"UP");     
      if ((!isset($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId])) || (trim($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId]["principal_code"])=="")) {
        BroadcastingUtils::sendAlertEmail($errSubject, "Empty Principal-Depot Code! principalUid@" . $this->postingOrderTO->principalUId." depot@{$this->postingOrderTO->processedDepotUId}", "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      } else {
        $prinCode = trim($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId]["principal_code"]);
      }

      if(!isset($documentTypeArr[$this->postingOrderTO->documentType]) || ($documentTypeArr[$this->postingOrderTO->documentType]=="")){
        BroadcastingUtils::sendAlertEmail($errSubject, "Unknown Document Type Code! principalUid@" . $this->postingOrderTO->principalUId, "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      } else {
        $prinDocTypeCode = $documentTypeArr[$this->postingOrderTO->documentType];
      }
      $sfchsaccount = $this->miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($this->postingOrderTO->principalUId, 35, $this->postingOrderTO->storeChainUId, CT_STORE_SHORTCODE, $arrayIndex="entity_uid");

      $seq = $this->sequenceDAO->getFTPFileExportSequence();
      $filename = str_replace('[@SEQ]', $seq, $this->exportMappingArr[0]['output_filename']); //replace seq placeholder in filename provided.
      $filename = str_replace('[@DNUM]',$this->postingOrderTO->documentNumber, $filename);
      
      $linecount = 0;      
      /* ------------------------------------
       *          FILE CONTENTS
       * ------------------------------------*/
       
    $dataH = array();
    $dataH[] = 'H';                                                          // Line type
    $dataH[] = '1';                                                          // Version
    $dataH[] = $this->postingOrderTO->principalUId;
    $dataH[] = date("Ymd", strtotime($this->postingOrderTO->documentDate));  //DATE
    $dataH[] = $this->postingOrderTO->orderNumber;                           // Customer Order Number
    $dataH[] = abs($this->postingOrderTO->documentNumber);                   // PrincipalsOrder#
    $dataH[] = $this->mfStoreArr[0]['store_name']  ;                         //  StoreNameOnSlsDoc
    $dataH[] = $this->mfStoreArr[0]['deliver_add1'] ;
    $dataH[] = $this->mfStoreArr[0]['deliver_add2'] ;
    $dataH[] = $this->mfStoreArr[0]['deliver_add3'] ;
    $dataH[] = $this->mfStoreArr[0]['bill_name'] ;
    $dataH[] = $this->mfStoreArr[0]['bill_add1'] ;
    $dataH[] = $this->mfStoreArr[0]['bill_add2'] ;
    $dataH[] = $this->mfStoreArr[0]['bill_add3'] ;
    $dataH[] = $this->mfStoreArr[0]['depot_uid'] ;
    $dataH[] = $this->mfStoreArr[0]['depot_name'] ;
    $dataH[] = $this->mfStoreArr[0]['principal_chain_uid'] ;
    $dataH[] = $this->mfStoreArr[0]['chain_name'] ;
    $dataH[] = $this->mfStoreArr[0]['ean_code'] ;
    $dataH[] = $this->mfStoreArr[0]['vat_number'];
    $dataH[] = $this->mfStoreArr[0]['branch_code'] ;
    $dataH[] = $this->mfStoreArr[0]['old_account'] ;
    
    $dataArr[] = join(",", $dataH);

    $linecount++;
      foreach($this->postingOrderTO->detailArr as $detailRow){

        //GET Product
        $productArr = $this->productDAO->getPrincipalProductItem($this->postingOrderTO->principalUId,$detailRow->productUId);
        if (sizeof($productArr)==0){
          BroadcastingUtils::sendAlertEmail($errSubject, "The DEPOT Export file could not be created due to missing PRODUCT details! @principalUid:".$this->postingOrderTO->principalUId."; @productUid:".$detailRow->productUId, "Y", $quietMode = false);
          $eTO->type = FLAG_ERRORTO_ERROR;
          return $eTO;
        }

        //detail variables

        /*---------- DETAIL ROW ----------*/
        
       $dataD = array();
       $dataD[] = 'D';
       $dataD[] =  str_pad($detailRow->pageNo,2,"0",STR_PAD_LEFT) . str_pad($detailRow->lineNo,2,"0",STR_PAD_LEFT) ;  // LineReference n 4 m unique and ascending within the order
       $dataD[] =  $productArr[0]['product_code'];                                //  PrincipalsStockCode
       $dataD[] =  $productArr[0]['product_description'];
       $dataD[] =  abs($detailRow->quantity);                                     //  Quantity n 6 m number of selling units
       $dataD[] =  number_format(round($detailRow->listPrice, 2), 2, '.', '');    //  SaleUnitGrossPrice m.5 11 ps Sale unit price before discount (excl of vat)
       $dataD[] =  round($detailRow->discountValue,2);                                             //  PromotionDiscount% m.2 5 o 

       $dataArr[] = join(',', $dataD);
       $linecount++;
      }
      /*------------------------------------*/

        $linecount++;
    
        $dataT2 = array();
        $dataT2[] = 'T';                                                  // Line type
        $dataT2[] = str_pad($linecount,5,"0",STR_PAD_LEFT);                 // messsage

        $dataArr[] = join(',', $dataT2);
        
        $data = join("\r\n",$dataArr);  //build file.

      //PATH AND LOCATION OF FILE
      $localPath = $ROOT . FILE_ARCHIVE_EXPORTS_PATH . $this->exportMappingArr[0]['folder_name'];
      if(!is_dir($localPath)){
        mkdir($localPath, 0777, TRUE);  //create recursive directory based on path.
      }
      $bytesWrit = @file_put_contents($localPath . $filename, $data);
      
//      copy ($localPath . $filename , ('c:/www/live/antel_system/ftp/vergezocht/'. $filename));  
      
      if($bytesWrit == strlen($data)){
        /*------  SUCCESSFUL ------*/
        $eTO->type= FLAG_ERRORTO_SUCCESS;
        $eTO->identifier = $filename;
        return $eTO;
      } else {
        BroadcastingUtils::sendAlertEmail($errSubject, "The DEPOT Export file could not be created @principalUid:".$this->postingOrderTO->principalUId."; @path:".$localPath . $filename, "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      }

    }
// *********************************************************************************************************************************************
// *********************************************************************************************************************************************
// *********************************************************************************************************************************************
    private function adaptorOLD() {  //no param - uses properties of class.

      /*******************
      *     VARIABLES
      *******************/

      //GENERAL VARIABLES
      global $ROOT;
      global $PHPFOLDER;
      $eTO = new ErrorTO;
      $newline = "\r\n";
      $errSubject = "Error in AdaptorDocumentExport->adaptorSYSTEMEXTRACT";
      
      // DEPOT VARIABLES
      $depotMap = $this->importDAO->getAllDepotPrincipalExportMapping($indexArray=ImportDAO::$PDP_EXP_MAP_INDEX_2);

      $documentTypeArr = array(DT_ORDINV=>"NO",
                               DT_ORDINV_ZERO_PRICE=>"NO",
                               DT_UPLIFTS=>"UP");     
      if ((!isset($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId])) || (trim($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId]["principal_code"])=="")) {
        BroadcastingUtils::sendAlertEmail($errSubject, "Empty Principal-Depot Code! principalUid@" . $this->postingOrderTO->principalUId." depot@{$this->postingOrderTO->processedDepotUId}", "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      } else {
        $prinCode = trim($depotMap[$this->postingOrderTO->principalUId][$this->postingOrderTO->processedDepotUId]["principal_code"]);
      }

      if(!isset($documentTypeArr[$this->postingOrderTO->documentType]) || ($documentTypeArr[$this->postingOrderTO->documentType]=="")){
        BroadcastingUtils::sendAlertEmail($errSubject, "Unknown Document Type Code! principalUid@" . $this->postingOrderTO->principalUId, "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      } else {
        $prinDocTypeCode = $documentTypeArr[$this->postingOrderTO->documentType];
      }
      $sfchsaccount = $this->miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($this->postingOrderTO->principalUId, 35, $this->postingOrderTO->storeChainUId, CT_STORE_SHORTCODE, $arrayIndex="entity_uid");

      $seq = $this->sequenceDAO->getFTPFileExportSequenceDepot(259);
      $filename = str_replace('[@SEQ]', $seq, $this->exportMappingArr[0]['output_filename']); //replace seq placeholder in filename provided.
      $filename = str_replace('[@DNUM]',$this->postingOrderTO->documentNumber, $filename);
      
      $linecount = 0;      
      /* ------------------------------------
       *          FILE CONTENTS
       * ------------------------------------*/
       
    file_put_contents($ROOT.$PHPFOLDER.'log/sgx.txt', print_r($this->postingOrderTO, TRUE), FILE_APPEND);   
       

    $dataH = array();
    $dataH[] = date("d/m/Y", strtotime($this->postingOrderTO->documentDate));  //DATE
    $dataH[] = PADD($this->postingOrderTO->orderNumber);                           // Customer Order Number
    $dataH[] = $this->postingOrderTO->documentNumber;                        // PrincipalsOrder#
    $dataH[] = $this->mfStoreArr[0]['store_name']  ;                         //  StoreNameOnSlsDoc
    $dataH[] = $this->mfStoreArr[0]['deliver_add1'] ;
    $dataH[] = $this->mfStoreArr[0]['deliver_add2'] ;
    $dataH[] = $this->mfStoreArr[0]['deliver_add3'] ;
    if($this->postingOrderTO->deliveryDueDate <> '') {
         $dataH[] = date("dmY", strtotime($this->postingOrderTO->deliveryDueDate));  //DATE	
    } else {
    	   $dataH[] = '';
    }
    
    $dataH[] = $this->postingOrderTO->deliveryInstructions; 
    $dataH[] = $this->mfStoreArr[0]['courier_code'] ;
    
    $netPrice = 0;
    
    foreach($this->postingOrderTO->detailArr as $detailRow){
    	   $netPrice = $netPrice + $detailRow->extPrice;
    }
    
    $dataH[] = number_format(round($netPrice, 2), 2, '.', '');   
     
    $dataArr[] = join(",", $dataH);
         
        $data = join("\r\n",$dataArr);  //build file.

      //PATH AND LOCATION OF FILE
      $localPath = $ROOT . FILE_ARCHIVE_EXPORTS_PATH . $this->exportMappingArr[0]['folder_name'];
      if(!is_dir($localPath)){
        mkdir($localPath, 0777, TRUE);  //create recursive directory based on path.
      }
      $bytesWrit = @file_put_contents($localPath . $filename, $data);
      
      copy ($localPath . $filename , ($ROOT . '/ftp/salcolog/in/'. $filename));  
      
      if($bytesWrit == strlen($data)){
        /*------  SUCCESSFUL ------*/
        $eTO->type= FLAG_ERRORTO_SUCCESS;
        $eTO->identifier = $filename;
        return $eTO;
      } else {
        BroadcastingUtils::sendAlertEmail($errSubject, "The DEPOT Export file could not be created @principalUid:".$this->postingOrderTO->principalUId."; @path:".$localPath . $filename, "Y", $quietMode = false);
        $eTO->type = FLAG_ERRORTO_ERROR;
        return $eTO;
      }

    }
// *********************************************************************************************************************************************
// *********************************************************************************************************************************************
// *********************************************************************************************************************************************
    private function adaptorXENONIDOC() {  //no param - uses properties of class.

           /*******************
           *     VARIABLES
           *******************/

           //GENERAL VARIABLES
           global $ROOT;
           global $PHPFOLDER;
           $eTO = new ErrorTO;
           $newline = "\r\n";
           $errSubject = "Error in AdaptorDocumentExport->adaptorSGXOrders";
      
           $seq = $this->sequenceDAO->getFTPFileExportSequenceDepot(188);     //SGX Capetown 
           $filename = str_replace('[@SEQ]', $seq, $this->exportMappingArr[0]['output_filename']); //replace seq placeholder in filename provided.
           $filename = str_replace('[@DATE]',date("YmdHis"), $filename);
           
           // PATH AND LOCATION OF FILE
           $localPath = $ROOT . FILE_ARCHIVE_EXPORTS_PATH . $this->exportMappingArr[0]['folder_name'];
           if(!is_dir($localPath)){
               mkdir($localPath, 0777, TRUE);  //create recursive directory based on path.
           }
          /* ------------------------------------
          *          FILE CONTENTS
          * ------------------------------------*/
       
//          file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/exon.txt', print_r($this->postingOrderTO, TRUE), FILE_APPEND);  
    
          $fcustNum = $this->mfStoreArr[0]['old_account'];
          $fdocNum  = $this->postingOrderTO->documentNumber;
          $fpoNum   = $this->postingOrderTO->orderNumber;
          
          include($ROOT.$PHPFOLDER.'/functional/export/test/XeonIdocOrdersTemplate.php');
          
          $orderHeaderXMla = str_replace(array("&&file_seq_num&&", "&&cust_number&&", "&&po_number&&", "&&doc_number&&"),
                                      array($seq, $fcustNum, $fpoNum, $fdocNum ),
                                      $orderHeaderXMl);
          
          $headerBytesWrit = file_put_contents($localPath . $filename, $orderHeaderXMla . "\r\n");
                    
          foreach($this->postingOrderTO->detailArr as $detailRow){
               
               $netPrice = $detailRow->nettPrice;
               $product  = $detailRow->productCode;	
          	
               $orderDetailXMla = str_replace(array("&&extended_price&&", "&&product_code&&"),
                                      array($netPrice, $product),
                                      $orderDetailXMl);
                                      
               $detailBytes = file_put_contents($localPath . $filename, $orderDetailXMla . "\r\n", FILE_APPEND);
               
               $detailBytesWrit = $detailBytesWrit + $detailBytes;
               
               $lenghWrtten = $lenghWrtten + strlen($orderDetailXMla);
          }

          $trailerBytesWrit = file_put_contents($localPath . $filename,$orderEndXMl . "\r\n", FILE_APPEND);
              
          $totalBytesWrtt =   $detailBytesWrit +  $trailerBytesWrit + $headerBytesWrit;
        
          
         //PATH AND LOCATION OF FILE
         $localPath = $ROOT . FILE_ARCHIVE_EXPORTS_PATH . $this->exportMappingArr[0]['folder_name'];
         if(!is_dir($localPath)){
            mkdir($localPath, 0777, TRUE);  //create recursive directory based on path.
         }
      
         copy ($localPath . $filename , ($ROOT . '/ftp/xenon/in/'. $filename)); 
      
         if($totalBytesWrtt > 1530){
              /*------  SUCCESSFUL ------*/
              $eTO->type= FLAG_ERRORTO_SUCCESS;
              $eTO->identifier = $filename;
              return $eTO;
         } else {
              BroadcastingUtils::sendAlertEmail($errSubject, "The DEPOT Export file could not be created @principalUid:".$this->postingOrderTO->principalUId."; @path:".$localPath . $filename, "Y", $quietMode = false);
              $eTO->type = FLAG_ERRORTO_ERROR;
         return $eTO;
         }
    }
// *********************************************************************************************************************************************
// *********************************************************************************************************************************************
// *********************************************************************************************************************************************


}


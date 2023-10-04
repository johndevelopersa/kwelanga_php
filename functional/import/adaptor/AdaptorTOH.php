<?php
/* This is an ADAPTOR. As little as possible processing and lookups should happen in here. Leave that to the processing script.
 * Adaptors should be as lightweight as possible
 *
 * STORE CREDIT LIMITS IMPORT
 *
 * Updates the credit limit fields on principal_store_master by using store special fields
 * File Structure : XML
 * Sample :
 * see xsd
 *
 *
 *
 * If you get strange chars in front of file after fixing a downloaded file (BOM problem) then either use editor to convert to UTF8
 * or put this code in permanently :
 *
 * //Storing the previous encoding in case you have some other piece
   //of code sensitive to encoding and counting on the default value.
   $previous_encoding = mb_internal_encoding();

   //Set the encoding to UTF-8, so when reading files it ignores the BOM
   mb_internal_encoding('UTF-8');

   //Process the CSS files...

   //Finally, return to the previous encoding
   mb_internal_encoding($previous_encoding);

   //Rest of the code...
 *
 *
 *
 */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostMiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/ValidationCommonUtils.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingStoreTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingSpecialFieldTO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingDetailTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingBillingInstructionsTO.php');

class AdaptorTOH {

    private $dbConn;
    private $storeDAO;
    private $postMiscDAO;


    function __construct($dbConn) {
    global $storeDAO, $postMiscDAO;
    $this->dbConn = $dbConn;
    // re-use above globals what we can from calling program to improve speed
    $this->postMiscDAO = $postMiscDAO;
    $this->storeDAO = $storeDAO;
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------

   //Vendor 6, CONCEPT
   //Vendor 6, Smollan
   function adaptorTOH_CONCEPT($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array(); // put into common TO
      $fileArr = explode("\n",$content);

      if (!strpos($fileArr, "HDR") || !strpos($fileArr, "LN") ) {
         $eTO->type = FLAG_ERRORTO_ERROR;
         $eTO->description = "Missing flags within file - data received: ({$fileArr})";
         $eTO->identifier = ET_CUSTOMER;
         return $eTO;
      }

      foreach ($fileArr as $key=>$line) {

         $cols = str_getcsv($line, ",", '"', "\\");
         $lineType = $cols[0];

         /*******************
          *   ORDER HEAD
         *******************/
         if ($lineType=="HDR") {
               $onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalUId($onlineFileProcessItem["onlineFileProcessingMapping"], 423);
               if (empty($onlineFileProcessingMapping)) {
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "Unknown Principal Type () @line:".($key+1);
                  $eTO->identifier = ET_CUSTOMER;
                  return $eTO;
               }

               $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
               $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
               $postingOrdersHoldingTO->principalUid = 423;
               $postingOrdersHoldingTO->updateProduct="N";
               $postingOrdersHoldingTO->insertProduct="N";
               $postingOrdersHoldingTO->reference = trim($cols[1]);
               $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
               $postingOrdersHoldingTO->dataSource = DS_EDI;
               $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
               $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
               $postingOrdersHoldingTO->requestedDeliveryDate = "";
               $postingOrdersHoldingTO->capturedBy = 'CONCEPT';
               $postingOrdersHoldingTO->deliveryInstructions = "";
               $postingOrdersHoldingTO->offInvoiceDiscount = 0;
               $postingOrdersHoldingTO->offInvoiceDiscountType = '';  
               
               $postingOrdersHoldingTO->salesAgentStoreIdentifier = "";
               $postingOrdersHoldingTO->debtorsStoreIdentifier = "";

               // todo: lookup the generic depot which they can edit
               $postingOrdersHoldingTO->depotLookupRef = (mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,103,15))));

               $postingOrdersHoldingTO->orderDate = $cols[3];
            
               if ($postingOrdersHoldingTO->orderDate === false) {
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "Order date invalid format or empty";
                  $eTO->identifier = ET_CUSTOMER;
                  return $eTO;
               }

               $postingOrdersHoldingTO->shipToName = $cols[7];
               $postingOrdersHoldingTO->deliverName = $cols[7];
               $postingOrdersHoldingTO->clientDocumentNo = ($cols[2] == "")? '' : $cols[2];
               $postingOrdersHoldingTO->documentNo = $postingOrdersHoldingTO->clientDocumentNo;
               $postingOrdersHoldingTO->documentType = $cols[8];
               $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
               $postingOrdersHoldingTO->deliveryInstructions = "";
               $postingOrdersHoldingTO->enforceSameDepot = "N";
               $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';

               $postingOrdersHoldingTO->offInvoiceDiscount = 0;
               $postingOrdersHoldingTO->offInvoiceDiscountType = '';

               /*---------------------------
               *    STORE LOOKUP LOGIC
               *--------------------------*/
               $postingOrdersHoldingTO->oldAccount = $cols[6];

               /*******************
               *   CREATE STORE
               *******************/
               $postingStoreTO = new PostingStoreTO;
               $postingStoreTO->DMLType = "INSERT";
               $postingStoreTO->principalStoreUId ="";
               $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
               $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, $postingOrdersHoldingTO->deliverName);
               $postingStoreTO->deliverAdd1 = "";
               $postingStoreTO->deliverAdd2 = "";
               $postingStoreTO->deliverAdd3 = "";
               $postingStoreTO->billName = $postingStoreTO->deliverName;
               $postingStoreTO->billAdd1 = "";
               $postingStoreTO->billAdd2 = "";
               $postingStoreTO->billAdd3 = "";
               $postingStoreTO->vatNumber = "";
               $postingStoreTO->depot = ""; // this will be set by the processing script
               $postingStoreTO->deliveryDay = "8";
               $postingStoreTO->noVAT="0";
               $postingStoreTO->onHold = "0";
               $postingStoreTO->chain = ""; // this will be set by the processing script
               $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
               $postingStoreTO->status=FLAG_STATUS_ACTIVE;
               $postingStoreTO->vendorCreatedByUId=$postingOrdersHoldingTO->vendorUid;
               $postingStoreTO->ownedBy=$postingOrdersHoldingTO->vendorUid;
               $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;

               $postingOrdersHoldingTO->postingStoreTO=$postingStoreTO;
         }

         /*******************
             *   ORDER DETAILS
            *******************/
         else if ($lineType=="LN") {  // DETAIL rows
            $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

            // should not be necessary to check document type as other types should end up as zeros
            $postingOrdersHoldingDetailTO->listPrice     = $cols[6];
            $postingOrdersHoldingDetailTO->discountValue = "0000.00";
            $postingOrdersHoldingDetailTO->nettPrice     = $cols[6];
            $postingOrdersHoldingDetailTO->nettPrice     = round($postingOrdersHoldingDetailTO->listPrice - $postingOrdersHoldingDetailTO->discountValue, 2); // each
            $postingOrdersHoldingDetailTO->quantity      = $cols[4];
            $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->nettPrice * $postingOrdersHoldingDetailTO->quantity;
            $postingOrdersHoldingDetailTO->vatRate       = "0015.00";
            $postingOrdersHoldingDetailTO->vatAmount     = round($postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100, 2);
            $postingOrdersHoldingDetailTO->totalPrice    = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;

            $postingOrdersHoldingDetailTO->productCode = "";
            $postingOrdersHoldingDetailTO->productGTIN = $cols[2]; //* barcode
            $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
            $postingOrdersHoldingDetailTO->pallets = 0;
            $postingOrdersHoldingDetailTO->productName = mysqli_real_escape_string($this->dbConn->connection, $cols[3]);
            $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
         } else {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Invalid Line Type found, starting with {$lineType}";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
         }

      } // end of file loop

      $arrTO[] = $postingOrdersHoldingTO;


      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;
    }

// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------

    //Vendor 6, Smollan
    function adaptorTOH_V6($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array(); // put into common TO
      $fileArr = explode("\n",$content);

      // check line total
      $totalLineCnt=sizeof($fileArr);
      if ("TRL".str_pad($totalLineCnt,5,'0',STR_PAD_LEFT) != $fileArr[$totalLineCnt-1]) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "File Trailer total lines do not match".$fileArr[$totalLineCnt-1];
        $eTO->identifier = ET_CUSTOMER;
        return $eTO;
      }

      foreach ($fileArr as $key=>$line) {

        $lineType = substr($line,0,5);

        if ($lineType=="HDR01") { //HEADER : 1

          if (isset($postingOrdersHoldingTO)) {
            if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
              $eTO->identifier = ET_CUSTOMER;
              return $eTO;
            }
            $arrTO[] = $postingOrdersHoldingTO;
            unset($postingOrdersHoldingTO);
          }
          $onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalIdentifier($onlineFileProcessItem["onlineFileProcessingMapping"], 279);
          if (empty($onlineFileProcessingMapping)) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Unknown Principal Type (".trim(substr($line,13,30)).") @line:".($key+1);
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }

          /*******************
          *   ORDER HEAD
          *******************/
          $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
          $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
          $postingOrdersHoldingTO->principalUid = 279;
          $postingOrdersHoldingTO->updateProduct="Y";
          $postingOrdersHoldingTO->insertProduct="Y";
          $postingOrdersHoldingTO->reference = trim(str_replace("'",'',substr($line,227,13)));
          $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
          $postingOrdersHoldingTO->dataSource = DS_EDI;
          $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
          $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
          $postingOrdersHoldingTO->requestedDeliveryDate = "";
          $postingOrdersHoldingTO->capturedBy = 'SMOLLAN';
          $postingOrdersHoldingTO->deliveryInstructions = trim(substr($line,278,20));
          $postingOrdersHoldingTO->offInvoiceDiscount = 0;
          $postingOrdersHoldingTO->offInvoiceDiscountType = '';  
          
          $postingOrdersHoldingTO->salesAgentStoreIdentifier = trim(substr($line,262,7)); //sgx bill to
          $postingOrdersHoldingTO->debtorsStoreIdentifier = trim(substr($line,273,8)); // sgx ship to
          $upliftsRef = trim(substr($line,193,8)); // save this for later use in HDR02, for uplifts
          if(Preg_match(GUI_PHP_CHAR_REGEX,$upliftsRef)){
            $upliftsRef = trim(substr($line,206,6));
          }
          $xtraShipToRef = trim(substr($line,206,6));
          $postingOrdersHoldingTO->depotLookupRef = (mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,103,15))));

          
        } else if ($lineType=="HDR02") {//HEADER : 2

          $postingOrdersHoldingTO->orderDate = CommonUtils::formatCompactDate(substr($line,107,8));
          
          if ($postingOrdersHoldingTO->orderDate === false) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Order date invalid format or empty";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }

          $postingOrdersHoldingTO->shipToName = str_replace("'",'',trim(substr($line,13,30)));
          $postingOrdersHoldingTO->deliverName = str_replace("'",'',substr($line,13,30));
          $postingOrdersHoldingTO->clientDocumentNo = "00".substr($line,7,6);
          $postingOrdersHoldingTO->documentNo = $postingOrdersHoldingTO->clientDocumentNo;
          $postingOrdersHoldingTO->documentType = substr($line,115,1);

          switch ($postingOrdersHoldingTO->documentType){
            case "D":
              $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV_ZERO_PRICE;
              $postingOrdersHoldingTO->deliveryInstructions .= "(FREE STOCK)"; // override
              break;
            case "P":
              $postingOrdersHoldingTO->documentTypeUId = DT_UPLIFTS;
               break;
            default:
              $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
          }

          $postingOrdersHoldingTO->deliveryInstructions .= "";
          $postingOrdersHoldingTO->enforceSameDepot = "N";
          $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';

          $postingOrdersHoldingTO->offInvoiceDiscount = 0;
          $postingOrdersHoldingTO->offInvoiceDiscountType = '';  

          /*---------------------------
           *    STORE LOOKUP LOGIC
           *--------------------------*/
          if($postingOrdersHoldingTO->documentTypeUId == DT_UPLIFTS){
            //FOR UPLIFTS MATCH STORE BY STORE NAME.
            $postingOrdersHoldingTO->flagStrippeddelivernameLookupRef = 'Y';
            $postingOrdersHoldingTO->oldAccount = ''; //blank out old account.
          } else {
             $postingOrdersHoldingTO->oldAccount = $upliftsRef;
          }

            /*******************
            *   CREATE STORE
            *******************/
            $postingStoreTO = new PostingStoreTO;
            $postingStoreTO->DMLType = "INSERT";
            $postingStoreTO->principalStoreUId ="";
            $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
            $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, $postingOrdersHoldingTO->deliverName);
            $postingStoreTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim(substr($line,43,20))));
            $postingStoreTO->deliverAdd2 = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,63,20)));
            $postingStoreTO->deliverAdd3 = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,83,20)));
            $postingStoreTO->billName = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim(substr($line,119,20))));
            $postingStoreTO->billAdd1 = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,149,20)));
            $postingStoreTO->billAdd2 = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,169,20)));
            $postingStoreTO->billAdd3 = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,189,20)));
            $postingStoreTO->vatNumber = "";
            $postingStoreTO->depot = ""; // this will be set by the processing script
            $postingStoreTO->deliveryDay = "8";
            $postingStoreTO->noVAT="0";
            $postingStoreTO->onHold = "0";
            $postingStoreTO->chain = ""; // this will be set by the processing script
            $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
            $postingStoreTO->status=FLAG_STATUS_ACTIVE;
            $postingStoreTO->vendorCreatedByUId=$postingOrdersHoldingTO->vendorUid;
            $postingStoreTO->ownedBy=$postingOrdersHoldingTO->vendorUid;
            $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;

            $postingOrdersHoldingTO->postingStoreTO=$postingStoreTO;
            // add lookup special field(s)
        }   else if ($lineType=="DTL01") {  //DETAIL rows

          /*******************
          *   ORDER DETAILS
          *******************/
          $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

          // should not be necessary to check document type as other types should end up as zeros
          $postingOrdersHoldingDetailTO->listPrice     = substr($line,77,5).".".substr($line,83,2);
          $postingOrdersHoldingDetailTO->discountValue = "0000.00";
          $postingOrdersHoldingDetailTO->nettPrice     = substr($line,77,5).".".substr($line,83,2);
          $postingOrdersHoldingDetailTO->nettPrice     = round($postingOrdersHoldingDetailTO->listPrice - $postingOrdersHoldingDetailTO->discountValue,2); // each
          $postingOrdersHoldingDetailTO->quantity      = substr($line,72,5);
          $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->nettPrice*$postingOrdersHoldingDetailTO->quantity;
          $postingOrdersHoldingDetailTO->vatRate       = "0015.00";
          $postingOrdersHoldingDetailTO->vatAmount     = round($postingOrdersHoldingDetailTO->extPrice*$postingOrdersHoldingDetailTO->vatRate/100,2);
          $postingOrdersHoldingDetailTO->totalPrice    =$postingOrdersHoldingDetailTO->extPrice+$postingOrdersHoldingDetailTO->vatAmount;
          if(trim(substr($line,86,5))<>'') {
             $postingOrdersHoldingDetailTO->productCode = trim(substr($line,85,12));
          } else {
             $productstr = trim(str_replace("Kellogg's",'',substr($line,19,40)));
             $productstr = trim(str_replace(" ",'',$productstr));
             $postingOrdersHoldingDetailTO->productCode = $productstr;
          } 
          $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
          $postingOrdersHoldingDetailTO->pallets = 0;
          $postingOrdersHoldingDetailTO->productName = trim(substr($line,19,40));
          $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;


        } else if (substr($line,0,3)=="TRL") {  //FILE TRAILER

          // affix the last processed TO
          if (isset($postingOrdersHoldingTO)) {
            if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
              $eTO->identifier = ET_CUSTOMER;
              return $eTO;
            }
            $arrTO[] = $postingOrdersHoldingTO;
            unset($postingOrdersHoldingTO);
          }

          // skip
          if ($totalLineCnt!=($key+1)) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "End of Line Delimiter reached before end of file";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }

        } else {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid Line Type found, starting with {$lineType}";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
        }

      } // end of file loop


      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------

    //hasty tasty
    function adaptorTOH_V6a($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array(); // put into common TO
      $fileArr = explode("\n",$content);


      // check line total
      $totalLineCnt=sizeof($fileArr);

      if(!isset($fileArr[$totalLineCnt-1]) || substr($fileArr[$totalLineCnt-1],0,5) != 'TRL00') {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "File Trailer total lines do not match".$fileArr[$totalLineCnt-1];
        $eTO->identifier = ET_CUSTOMER;
        return $eTO;
      }


      foreach ($fileArr as $key=>$line) {

        $lineType = substr($line,0,5);

        if ($lineType=="HDR01") { //HEADER : 1

          if (isset($postingOrdersHoldingTO)) {
            if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
              $eTO->identifier = ET_CUSTOMER;
              return $eTO;
            }
            $arrTO[] = $postingOrdersHoldingTO;
            unset($postingOrdersHoldingTO);
          }

          $onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalIdentifier($onlineFileProcessItem["onlineFileProcessingMapping"], 71);

          if (empty($onlineFileProcessingMapping)) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Unknown Principal Type (".trim(substr($line,13,30)).") @line:".($key+1);
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }



          /*******************
          *   ORDER HEAD
          *******************/
          $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
          $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
          $postingOrdersHoldingTO->principalUid = 71;
          $postingOrdersHoldingTO->updateProduct="Y";
          $postingOrdersHoldingTO->insertProduct="Y";
          $postingOrdersHoldingTO->reference = trim(str_replace("'",'',substr($line,227,13)));
          $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
          $postingOrdersHoldingTO->dataSource = DS_EDI;
          $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
          $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
          $postingOrdersHoldingTO->requestedDeliveryDate = "";
          $postingOrdersHoldingTO->capturedBy = 'HASTY';
          //$postingOrdersHoldingTO->salesAgentStoreIdentifier = trim(substr($line,262,7)); //sgx bill to
          $upliftsRef = trim(substr($line,206,8)); // save this for later use in HDR02, for uplifts
          $xtraShipToRef = trim(substr($line,206,8));
          $postingOrdersHoldingTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,73,20)));
          $postingOrdersHoldingTO->deliverAdd2 = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,103,30)));
          $postingOrdersHoldingTO->deliverAdd3 = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,133,20)));


        } else if ($lineType=="HDR02") {  //HEADER : 2


          $postingOrdersHoldingTO->orderDate = CommonUtils::formatCompactDate(substr($line,107,8));
          if ($postingOrdersHoldingTO->orderDate === false) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Order date invalid format or empty";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }

          $postingOrdersHoldingTO->shipToName = trim(substr($line,13,30))."|".trim($postingOrdersHoldingTO->debtorsStoreIdentifier)."|".trim($postingOrdersHoldingTO->salesAgentStoreIdentifier)."|".$xtraShipToRef; // store this incase store creation failed
          $postingOrdersHoldingTO->deliverName = substr($line,13,30);
          if (substr($line,7,2) == '91') {
              $postingOrdersHoldingTO->clientDocumentNo = "9".trim(substr($line,7,6));
          } else {
              $postingOrdersHoldingTO->clientDocumentNo = trim(substr($line,7,6));
          }
          $postingOrdersHoldingTO->documentNo = $postingOrdersHoldingTO->clientDocumentNo;
          $postingOrdersHoldingTO->documentType = substr($line,115,1);

          switch ($postingOrdersHoldingTO->documentType){
            case "D":
              $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV_ZERO_PRICE;
              break;
            case "P":
              {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Cannot process UPLIFTS";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }
              break;
            default:
              $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
          }

          // let system define the sequence according to masterfiles, but populate it incase it needs it, waveside only use own doc num for Uplifts (P)

          //$postingOrdersHoldingTO->deliveryInstructions .= ((strToUpper(substr($line,277,3))=="COD")?" (COD)":"");
          //$postingOrdersHoldingTO->depotLookupRef = substr(strToUpper(basename($postingOrdersHoldingTO->incomingFile)),0,3);
          $postingOrdersHoldingTO->enforceSameDepot = "N";
          $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';


          /*---------------------------
           *    STORE LOOKUP LOGIC
           *--------------------------*/
           //echo $postingOrdersHoldingTO->documentTypeUId;
          if($postingOrdersHoldingTO->documentTypeUId == DT_UPLIFTS){
            //FOR UPLIFTS MATCH STORE BY STORE NAME.
            $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = 'Y';
            $postingOrdersHoldingTO->oldAccount = ''; //blank out old account.
          } else {
               //for all other principals use the first store lookup column
             $postingOrdersHoldingTO->oldAccount = $upliftsRef;
            }
         	  /*******************
            *   CREATE STORE
            *******************/
            $postingStoreTO = new PostingStoreTO;
            $postingStoreTO->DMLType = "INSERT";
            $postingStoreTO->principalStoreUId ="";
            $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
            $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, $postingOrdersHoldingTO->deliverName);
            $postingStoreTO->deliverAdd1 =$postingOrdersHoldingTO->deliverAdd1;
            $postingStoreTO->deliverAdd2 = $postingOrdersHoldingTO->deliverAdd2;
            $postingStoreTO->deliverAdd3 = $postingOrdersHoldingTO->deliverAdd3;
            $postingStoreTO->billName = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,63,20)));
            $postingStoreTO->billAdd1 = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,83,10)));
            $postingStoreTO->billAdd2 = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,95,8)));
            $postingStoreTO->billAdd3 = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,103,4)));
            //$postingStoreTO->vatNumber = mysqli_real_escape_string($this->dbConn->connection, trim(substr($line,265,13)));
            $postingStoreTO->depot = ""; // this will be set by the processing script
            $postingStoreTO->deliveryDay = "8";
            $postingStoreTO->noVAT="0";
            $postingStoreTO->onHold = "0";
            $postingStoreTO->chain = ""; // this will be set by the processing script
            $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
            $postingStoreTO->status=FLAG_STATUS_ACTIVE;
            $postingStoreTO->vendorCreatedByUId=$postingOrdersHoldingTO->vendorUid;
            $postingStoreTO->ownedBy=$postingOrdersHoldingTO->vendorUid;
            $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;

            $postingOrdersHoldingTO->postingStoreTO=$postingStoreTO;

            // add lookup special field(s)
          }

         else if ($lineType=="DTL01") {  //DETAIL rows

          /*******************
          *   ORDER DETAILS
          *******************/
          $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

          // should not be necessary to check document type as other types should end up as zeros
          $postingOrdersHoldingDetailTO->listPrice = substr($line,77,5).".".substr($line,83,2);
          if(substr($line,101,5).".".substr($line,107,2) == '00000.00'){
              $discount = 0;
          } else {	
              $discount = (substr($line,77,5).".".substr($line,83,2)) - (substr($line,101,5).".".substr($line,107,2));
          }
          $postingOrdersHoldingDetailTO->discountValue = $discount; // discount ref (contains blanks) is sent in file but we ignore it.
          $postingOrdersHoldingDetailTO->quantity = substr($line,72,5);

          $postingOrdersHoldingDetailTO->listPrice     = '';
          $postingOrdersHoldingDetailTO->nettPrice     = '';
          $postingOrdersHoldingDetailTO->extPrice      = '';
          $postingOrdersHoldingDetailTO->vatRate       = "0015.00";
          $postingOrdersHoldingDetailTO->vatAmount     = '';
          $postingOrdersHoldingDetailTO->totalPrice    = '';
//          $postingOrdersHoldingDetailTO->nettPrice = (substr($line,77,5).".".substr($line,83,2)) - $discount; // each
//          $postingOrdersHoldingDetailTO->extPrice =$postingOrdersHoldingDetailTO->nettPrice*$postingOrdersHoldingDetailTO->quantity;
//          $postingOrdersHoldingDetailTO->vatRate = "15.00";
//          $postingOrdersHoldingDetailTO->vatAmount = round($postingOrdersHoldingDetailTO->extPrice*$postingOrdersHoldingDetailTO->vatRate/100,2);
//          $postingOrdersHoldingDetailTO->totalPrice =$postingOrdersHoldingDetailTO->extPrice+$postingOrdersHoldingDetailTO->vatAmount;
          $postingOrdersHoldingDetailTO->productCode = trim(substr($line,86,6));
          $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
          $postingOrdersHoldingDetailTO->clientPageNo = trim(substr($line,14,2));
          $postingOrdersHoldingDetailTO->clientLineNo = trim(substr($line,16,3));
          $postingOrdersHoldingDetailTO->pallets = 0;
          $postingOrdersHoldingDetailTO->productName = trim(substr($line,19,40));
          $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;


        } else if (substr($line,0,3)=="TRL") {  //FILE TRAILER

          // affix the last processed TO
          if (isset($postingOrdersHoldingTO)) {
            if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
              $eTO->identifier = ET_CUSTOMER;
              return $eTO;
            }
            $arrTO[] = $postingOrdersHoldingTO;
            unset($postingOrdersHoldingTO);
          }

          // skip
          if ($totalLineCnt!=($key+1)) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "End of Line Delimiter reached before end of file";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }

        } else {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid Line Type found, starting with {$lineType}";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
        }

      } // end of file loop


      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------

    //Vendor 12: Tuna Marine (Single principal)
    function adaptorTOH_V12($content, $onlineFileProcessItem) {

    	// NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
    	global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
    	$eTO = new ErrorTO;
    	if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
    	$fileArr = explode("\n",trim($content));  //LF
    	$totalLineCnt = sizeof($fileArr);  //lines total
    	$arrTO = array();  // put into common TO


        //test integrity of file here.
        $trailer = $fileArr[count($fileArr)-1]; //last row - trim removes trailing blank rows
        if(substr($trailer,0,3) != 'TRL' || !is_numeric(substr($trailer,3)) || substr($trailer,3)!=(count($fileArr))){
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "File Trailer is missing, not numeric or total lines do not match! (" . $trailer . ")";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
        }

        //detail rows must exist for each header(2).
        $hCount = 0;
        foreach($fileArr as $line){
          $type = substr($line, 0, 3);
          if($hCount == 2 && $type!='DTL'){	//expecting detail next
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Detail lines are missing for a provided header row.";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          } elseif($type=='DTL'){
            $hCount = 0;
          }
          if ($type=='HDR'){ $hCount++; }
        }


        //file per single principal - apdator for single principal
        $onlineFileProcessingMapping = $importDAO->getMappingFromOIMByPrincipalUId($onlineFileProcessItem["onlineFileProcessingMapping"], $onlineFileProcessItem["principal_uid"]);
        if (empty($onlineFileProcessingMapping)) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Could not retrieve online file principal mappings";
	  $eTO->identifier = ET_SYSTEM;
          return $eTO;
        }
        //same adaptor is used for processing mulitple unique principal files, so you cannot have null principal_uid
        if ($onlineFileProcessingMapping["principal_uid"]=="") {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Incorrect Configuration : onlineFileProcessingMapping encountered blank principal_uid or not found!";
          $eTO->identifier = ET_SYSTEM;
          return $eTO;
        }


    	foreach ($fileArr as $key => $line){  //file line loop

          $lineType = strtoupper(substr(trim($line),0,3));


          /*******************
          *   ORDER HEAD
          *******************/
          if ($lineType=="HDR"){

            //there are two header rows - treat them independently.
            if(substr(trim($line),3,2)=="01"){  //Header Row - 1

              //build arrTO.
              if (isset($postingOrdersHoldingTO)) {

                if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
                  $eTO->identifier = ET_CUSTOMER;
                  return $eTO;
                }

                $arrTO[] = $postingOrdersHoldingTO;
                unset($postingOrdersHoldingTO);
              }


              $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
              $postingOrdersHoldingTO->updateProduct="Y";
              $postingOrdersHoldingTO->insertProduct="Y";
              $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
              $postingOrdersHoldingTO->principalUid = $onlineFileProcessingMapping["principal_uid"];
              $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
              $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
              $postingOrdersHoldingTO->capturedBy = 'TUNA'; // dont change this as notifications run off it
              $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
              $postingOrdersHoldingTO->dataSource = DS_EDI;
              $postingOrdersHoldingTO->documentNo = trim(substr($line, 7, 6));  //use provided doc no
              $postingOrdersHoldingTO->clientDocumentNo = trim(substr($line, 7, 6));  //same as above.
              $postingOrdersHoldingTO->reference = trim(substr($line, 226, 13));  //PO NUMBER.
              $postingOrdersHoldingTO->vendorReference = "";
              $postingOrdersHoldingTO->oldAccount = trim(substr($line, 193, 10));
              $postingOrdersHoldingTO->debtorsStoreIdentifier = "";
              $postingOrdersHoldingTO->salesAgentStoreIdentifier = $postingOrdersHoldingTO->oldAccount; //same as old acc.
              $postingOrdersHoldingTO->storeLookupRef = '';  //same as old acc.
              $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';  //use generic => EDI pricing is used. no problem, happy days :)
              $Postingordersholdingto->Depotlookupref = Strtoupper(Substr(Trim($Postingordersholdingto->Incomingfile), 0, 3));  //Lookup Code Is From The Filename. Ie: Cmt - Ull Cpt, Ktm - Ull Durb.

            } else if(substr(trim($line),3,2)=="02"){ //Header Row - 2


              //dont trust header 1 as file might be malformed ie: missing a HDR01 thereby skipping too here.
              if (!isset($postingOrdersHoldingTO)){
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "postingOrdersHoldingTO was not set in first header row, check file if malformed. (line:".($key+1).")";
                $eTO->identifier = ET_CUSTOMER;
                return $eTO;
              }

              //This is unique to TUNA.
              $priceIndicator = trim(substr($line, 113, 2));  //whether pricing provided is exclu. or inclu. or VAT.
              if(!in_array($priceIndicator, array('IN', 'EX'))){   //IN or EX values ONLY!!!
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "Pricing Indicator is invalid or malformed! (indicator: " . $priceIndicator . ", line:".($key+1).")";
                $eTO->identifier = ET_CUSTOMER;
                return $eTO;
              } else {
                $postingOrdersHoldingTO->generalReference1 = $priceIndicator;
              }

              $postingOrdersHoldingTO->deliverName = trim(substr($line, 13, 30));
              $postingOrdersHoldingTO->shipToName = $postingOrdersHoldingTO->deliverName;
              $postingOrdersHoldingTO->documentType = trim(substr($line, 115, 1));

              switch ($postingOrdersHoldingTO->documentType){
                case "V":
                  $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                  break;
                case "P":
                  $postingOrdersHoldingTO->documentTypeUId = DT_UPLIFTS;
                  break;
                default:
                  //allow to continue and get flagged by normal processing
                  $postingOrdersHoldingTO->documentTypeUId = "";
              }

              $postingOrdersHoldingTO->orderDate = trim(substr($line, 107, 2)) . '-' . trim(substr($line, 109, 2)) . '-' . trim(substr($line, 111, 2)); //format YY-MM-DD, MySQL will accept this.
              $postingOrdersHoldingTO->requestedDeliveryDate = trim(substr($line, 222, 2)) . '-' . trim(substr($line, 224, 2)) . '-' . trim(substr($line, 226, 2));  //format YY-MM-DD, MySQL will accept this.
              //check order date. must be a valid date and not 1970-01-01
              $ordDate = date('Ymd', strtotime($postingOrdersHoldingTO->orderDate));  //if value malformed will be 1970-01-01 and check below will activate!
              if(($postingOrdersHoldingTO->orderDate===false) || !(checkdate(substr($ordDate, 4,2), substr($ordDate,6,2), substr($ordDate, 0,4))) || ($ordDate == '1970-01-01')){
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "Order date Invalid format or empty";
                $eTO->identifier = ET_CUSTOMER;
                return $eTO;
              }


              /*******************
              *   CREATE STORE
              *******************/
              $postingStoreTO = new PostingStoreTO;
              $postingStoreTO->DMLType = "INSERT";
              $postingStoreTO->principalStoreUId = '';
              $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
              $postingStoreTO->deliverName = $postingOrdersHoldingTO->deliverName;
              $postingStoreTO->deliverAdd1 = trim(substr($line, 43, 20));
              $postingStoreTO->deliverAdd2 = trim(substr($line, 63, 20));
              $postingStoreTO->deliverAdd3 = trim(substr($line, 83, 20));
              $postingStoreTO->billName = trim(substr($line, 119, 30));
              $postingStoreTO->billAdd1 = trim(substr($line, 149, 20));
              $postingStoreTO->billAdd2 = trim(substr($line, 169, 20));
              $postingStoreTO->billAdd3 = trim(substr($line, 189, 20));  //postal code.
              $postingStoreTO->vatNumber = trim(substr($line, 228, 20));
              $postingStoreTO->depot = ''; // this will be set by the processing script
              $postingStoreTO->deliveryDay = "8";
              $postingStoreTO->noVAT = 0;
              $postingStoreTO->onHold = "0";
              $postingStoreTO->chain = ''; // this needs to be assigned by exceptions user.
              $postingStoreTO->altPrincipalChainUId = ''; // let the posting allocate the generic chain
              $postingStoreTO->status = FLAG_STATUS_ACTIVE;
              $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
              $postingStoreTO->ownedBy = '';
              $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;  //NB!!!
              $postingStoreTO->updatePrincipalStore = 'Y';
              $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
              //End Create the StoreTO


            } //end of header row - 2.

          }  elseif ($lineType=="DTL") { //DETAIL ROWS


            /*******************
            *   ORDER DETAILS
            *******************/
            $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();  // should not be necessary to check document type as other types should end up as zeros

            $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
            $postingOrdersHoldingDetailTO->pallets = 0;
            $postingOrdersHoldingDetailTO->clientPageNo = trim(substr($line, 14, 2));
            $postingOrdersHoldingDetailTO->clientLineNo = trim(substr($line, 16, 2));
            $postingOrdersHoldingDetailTO->productCode = trim(substr($line, 58, 10));
            $postingOrdersHoldingDetailTO->productName = trim(substr($line, 18, 40));
            $postingOrdersHoldingDetailTO->quantity = trim(substr($line, 71, 5));
            $postingOrdersHoldingDetailTO->discountValue = (trim(substr($line, 87, 9)) / $postingOrdersHoldingDetailTO->quantity);  //actual value off.
            $postingOrdersHoldingDetailTO->discountReference = trim(substr($line, 96, 10));
            if($priceIndicator == 'EX'){
              $postingOrdersHoldingDetailTO->listPrice = trim(substr($line, 78, 8));
            } else {
              $listPriceInclVAT = trim(substr($line, 78, 8));
              $listPriceOnlyVAT = ($listPriceInclVAT * VAL_VAT_RATE_TBLSTD)/(VAL_VAT_RATE_TBLSTD + 100);
              $postingOrdersHoldingDetailTO->listPrice = round(($listPriceInclVAT - $listPriceOnlyVAT), 2);
            }
            $postingOrdersHoldingDetailTO->nettPrice = round(($postingOrdersHoldingDetailTO->listPrice - $postingOrdersHoldingDetailTO->discountValue), 2);
            $postingOrdersHoldingDetailTO->extPrice = $postingOrdersHoldingDetailTO->nettPrice * $postingOrdersHoldingDetailTO->quantity;
            $postingOrdersHoldingDetailTO->vatRate = trim(substr($line, 128, 5));
            $postingOrdersHoldingDetailTO->vatAmount = round(($postingOrdersHoldingDetailTO->extPrice * ($postingOrdersHoldingDetailTO->vatRate / 100)), 2);
            $postingOrdersHoldingDetailTO->totalPrice = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;

            $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

          //end of detail
          } elseif ($lineType=="TRL"){ //last line in file.

            //final line and append last order to arrTO.
            $arrTO[] = $postingOrdersHoldingTO;

          } else {

            //protect adaptor for bad ass files.
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Encounted malformed line or not catered for line type: ".$lineType." at line:".($key+1);
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }

    	} // end of file loop


   	$eTO->type = FLAG_ERRORTO_SUCCESS;
   	$eTO->description = "Successful";
   	$eTO->object = $arrTO;
    	return $eTO;
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------

    // 198 - Instant Trading / Natural Vinegar
    function adaptorTOH_V26($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);

      $fileArr = explode("\r\n",trim($content)); //LF

      //put file into order array, file has newlines inside header rows therefore group by sections.
      $ordArr = array();

      /*
       * 10ORDERSIO410834      A       CHOP08 130726      RAJESH         Choppies Supermarkets SA (Pty)Choppies Rustenburg                     72 Kerk Str                             Stand No 1895                           Rustenburg                                                                      001       00000 00000 00000             410834
         60      IO410834      0001AMT00W500GRSC                                                 000000001000CS  000000992600 00000 00000 00000                                                       000000000
         60      IO410834      0002AMT00W750GRSC                                                 000000002000CS  000001411200 00000 00000 00000                                                       000000000
       */
      foreach ($fileArr as $key => $line){
        $docNum = substr($line,10,6);
        $recType = substr($line,0,2);
        if($recType == "10") {
          if (isset($ordArr[$docNum])) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Duplicate Document ({$docNum}) found in adaptorTOH_V26 in file ".basename($onlineFileProcessItem["file_being_processed"]);
            $eTO->identifier = ET_SYSTEM;
            return $eTO;
          }
          $ordArr[$docNum]["Header"] = $line;
        } else if($recType == "60") {
          $ordArr[$docNum]["Detail"][] = $line;
        } else {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Unrecognised record type in adaptorTOH_V26 in file ".basename($onlineFileProcessItem["file_being_processed"]) . "  " . print_r($ordArr);
          $eTO->identifier = ET_SYSTEM;
          return $eTO;
        }
      }

      //file per single principal - apdator for single principal
      $onlineFileProcessingMapping = $importDAO->getMappingFromOIMByPrincipalUId($onlineFileProcessItem["onlineFileProcessingMapping"], $onlineFileProcessItem["principal_uid"]);
      if (empty($onlineFileProcessingMapping)) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Could not retrieve online file principal mappings";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }
      //same adaptor is used for processing mulitple unique principal files, so you cannot have null principal_uid
      if ($onlineFileProcessingMapping["principal_uid"]=="") {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Incorrect Configuration : onlineFileProcessingMapping encountered blank principal_uid or not found!";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }


      foreach ($ordArr as $key => $ord){


        /*******************
        *   ORDER HEAD
        *******************/
        $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
        $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
        $postingOrdersHoldingTO->principalUid = $onlineFileProcessingMapping["principal_uid"];
        $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
        $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
        $postingOrdersHoldingTO->capturedBy = 'INSTANT'; // dont change this as notifications run off it
        $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
        $postingOrdersHoldingTO->dataSource = DS_EDI;
        $postingOrdersHoldingTO->documentNo = preg_replace("/[^0-9]/", "", trim(substr($ord["Header"],10,6)));
        $postingOrdersHoldingTO->clientDocumentNo = $postingOrdersHoldingTO->documentNo;  //same as above.
        $postingOrdersHoldingTO->reference = trim(substr($ord["Header"],49,15));
        $postingOrdersHoldingTO->vendorReference = trim(substr($ord["Header"],28,9)); // not unique by store
        $postingOrdersHoldingTO->oldAccount = trim(substr($ord["Header"],28,9));
        $postingOrdersHoldingTO->debtorsStoreIdentifier = "";
        $postingOrdersHoldingTO->salesAgentStoreIdentifier = $postingOrdersHoldingTO->oldAccount; //same as old acc.
        $postingOrdersHoldingTO->storeLookupRef = trim(substr($ord["Header"],28,9));
        $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';  //use generic => EDI pricing is used
        $postingOrdersHoldingTO->depotLookupRef = ''; // GAU is not the depot code - whole file is for industria
        echo substr(basename($onlineFileProcessItem["file_being_processed"]),0,3);
        if (substr(basename($onlineFileProcessItem["file_being_processed"]),0,3) == "Log") {
        	  $postingOrdersHoldingTO->depotUId = 195; // loginet
        } else {
        	  $postingOrdersHoldingTO->depotUId = 234; // Instant Depot
        }
        $postingOrdersHoldingTO->deliverName = trim(substr($ord["Header"],64,30));
        if (strtoupper(substr($postingOrdersHoldingTO->deliverName,0,4)) == "CHOP") $postingOrdersHoldingTO->deliverName = trim(substr($ord["Header"],94,30)); // this is 40 chars but ullmanns only have 30
        if (strtoupper(substr($postingOrdersHoldingTO->deliverName,0,14)) == "SENTRA GAUTENG") $postingOrdersHoldingTO->deliverName = trim(substr($ord["Header"],94,30));
        $postingOrdersHoldingTO->shipToName = $postingOrdersHoldingTO->deliverName;
        $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = "N";
        $postingOrdersHoldingTO->documentType = '';
        $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
        $postingOrdersHoldingTO->enforceSameDepot = "N";
        $postingOrdersHoldingTO->updateStoreDepot = "Y";
        $postingOrdersHoldingTO->updateProduct = "N"; // only insert because no description is supplied in the file
        $postingOrdersHoldingTO->insertProduct = "Y";
        $postingOrdersHoldingTO->orderDate = $postingOrdersHoldingTO->captureDate;


        /*******************
        *   CREATE STORE
        *******************/
        $postingStoreTO = new PostingStoreTO;
        $postingStoreTO->DMLType = "INSERT";
        $postingStoreTO->principalStoreUId = '';
        $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
        $postingStoreTO->deliverName = trim(substr($ord["Header"],64,30));

        $postingStoreTO->deliverAdd1 = trim(substr($ord["Header"],94,40));
        $postingStoreTO->deliverAdd2 = trim(substr($ord["Header"],134,40));
        $addr3 = trim(substr($ord["Header"],174,40));
        $addr4 = trim(substr($ord["Header"],214,40));
        $addr5 = trim(substr($ord["Header"],254,40));

        if (preg_match("/vat:/i",$addr4)) {
          $postingStoreTO->vatNumber = preg_replace("/[^0-9]/", "", $addr4);
          $addr4="";
        } else if (preg_match("/vat:/i",$addr5)) {
          $postingStoreTO->vatNumber = preg_replace("/[^0-9]/", "", $addr5);
          $addr5="";
        }

        if (!empty($addr3)) $postingStoreTO->deliverAdd2 = substr($postingStoreTO->deliverAdd2.",".$addr3,0,60);
        if (!empty($addr4)) $postingStoreTO->deliverAdd3 = $addr4;
        if (!empty($addr5)) $postingStoreTO->deliverAdd3 = substr($postingStoreTO->deliverAdd3.",".$addr5,0,60);

        $postingStoreTO->billName = trim(substr($ord["Header"],64,30));
        $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
        $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
        $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
        $postingStoreTO->depot = $postingOrdersHoldingTO->depotUId; // Ullmanns JHb/Industria
        $postingStoreTO->deliveryDay = "8";
        $postingStoreTO->noVAT = 0;
        $postingStoreTO->onHold = "0";
        $postingStoreTO->chain = ''; // should use the lookup
        $postingStoreTO->altPrincipalChainUId = ''; // let the posting allocate the generic chain
        $postingStoreTO->status = FLAG_STATUS_ACTIVE;
        $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
        $postingStoreTO->ownedBy = '';
        $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;
        $postingStoreTO->updatePrincipalStore="N"; // updates fields if supplied and the store is already inserted
        $postingStoreTO->updateDeliveryDay="N";
        $postingStoreTO->updateNoVAT="N";
        $postingStoreTO->updateStoreStatus="N";
        $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
        //End Create the StoreTO

        // Special Field

        // Extract Number
        $sf = trim(substr($ord["Header"],28,9));
        if ($sf!=''){
          $postingSpecialFieldTO = new PostingSpecialFieldTO;
          $postingSpecialFieldTO->DMLType ="INSERT";
          $postingSpecialFieldTO->principal = $postingStoreTO->principal;
          $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
          $postingSpecialFieldTO->fielduid = '229'; // Extract Number
          $postingSpecialFieldTO->entityUId = "";
          $postingSpecialFieldTO->value = $sf;
          $postingSpecialFieldTO->allowUpdate="N";
          $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;
        }

        /*******************
        *   ORDER DETAILS
        *******************/
         foreach($ord["Detail"] as $k2=>$ordD){

           $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();  // should not be necessary to check document type as other types should end up as zeros
           $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
           $postingOrdersHoldingDetailTO->pallets = 0;
           $postingOrdersHoldingDetailTO->clientPageNo = '';
           $postingOrdersHoldingDetailTO->clientLineNo = trim(substr($ordD,22,4));
           $postingOrdersHoldingDetailTO->productCode = trim(substr($ordD,27,15));
           $postingOrdersHoldingDetailTO->productName = $postingOrdersHoldingDetailTO->productCode; // only insert, don't update
           $postingOrdersHoldingDetailTO->discountValue = '';  //only list price provided.
           $postingOrdersHoldingDetailTO->discountReference = ''; //only list price provided.
           if (substr(basename($onlineFileProcessItem["file_being_processed"]),0,3) == "Log") {
                $postingOrdersHoldingDetailTO->quantity = trim(substr($ordD,88,9));
                $postingOrdersHoldingDetailTO->listPrice = trim(substr($ordD,104,8)).".".trim(substr($ordD,112,2));  //only list price provided, ignore the 3rd decimal
           } else {
                $postingOrdersHoldingDetailTO->quantity = trim(substr($ordD,89,9));
                $postingOrdersHoldingDetailTO->listPrice = trim(substr($ordD,105,8)).".".trim(substr($ordD,113,2));  //only list price provided, ignore the 3rd decimal
           }
           $postingOrdersHoldingDetailTO->nettPrice = $postingOrdersHoldingDetailTO->listPrice;
           $postingOrdersHoldingDetailTO->extPrice = ($postingOrdersHoldingDetailTO->nettPrice * $postingOrdersHoldingDetailTO->quantity);
           $postingOrdersHoldingDetailTO->vatRate = ""; // use RT Masterfiles
           $postingOrdersHoldingDetailTO->vatAmount = "";
           $postingOrdersHoldingDetailTO->totalPrice = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;
           $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

         }

         $arrTO[] = $postingOrdersHoldingTO; //add final order, no need to check if has detail as we do that in the begin.

      }


      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------

    //PASTEL : TSITSIKAMMA SPRING WATER cc
    function adaptorTOH_V31($content, $onlineFileProcessItem) {
      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      $principalTSITSIKAMMA = '73';
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO=new StoreDAO($this->dbConn);

      $fileArr = explode("\n",$content);

      // put into common TO
      $arrTO=array();
      $processingLine=0;
      foreach ($fileArr as $key=>$line) {

        $processingLine++;

        // convert line to CSV
        $lineArr=str_getcsv($line, ",", '"', "\\");
        if (
        (!isset($lineArr[0])) ||
        (
            (($lineArr[0]=="Header") && (sizeof($lineArr)<29)) ||
            (($lineArr[0]=="Detail") && (sizeof($lineArr)<14))
        )
        ){
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Line {$processingLine} of file ".basename($onlineFileProcessItem["file_being_processed"])." could not be converted to an array of 14/29 elements for CSV conversion!";
          $eTO->identifier = ET_SYSTEM;
          return $eTO;
        }

        $lineType=substr($lineArr[0],0,6);

        if(!isset($lineArr[1])){
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "File is malformed!";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
        }

        if ($lineType == "Header") {

          if (isset($postingOrdersHoldingTO)) {
            if (sizeof($postingOrdersHoldingTO->detailArr) == 0) {
              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "No Detail Lines found for Header Line @line:" . ($key + 1);
              $eTO->identifier = ET_CUSTOMER;
              return $eTO;
            }
            $arrTO[] = $postingOrdersHoldingTO;
            unset($postingOrdersHoldingTO);
          }

          $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
          $postingOrdersHoldingTO->updateProduct="Y";
          $postingOrdersHoldingTO->insertProduct="Y";
          $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
          $postingOrdersHoldingTO->principalUid = $onlineFileProcessItem["principal_uid"];

          $onlineFileProcessingMapping = $importDAO->getMappingFromOIMByPrincipalUId($onlineFileProcessItem["onlineFileProcessingMapping"], $postingOrdersHoldingTO->principalUid);
          if(empty($onlineFileProcessingMapping)) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Unknown Principal Type in adaptorTOH_V31 for file " . basename($onlineFileProcessItem["file_being_processed"]);
            $eTO->identifier = ET_SYSTEM;
            return $eTO;
          }

          $postingOrdersHoldingTO->clientDocumentNo = trim($lineArr[1]);
          $postingOrdersHoldingTO->documentNo = preg_replace("/[^0-9]/", "", $postingOrdersHoldingTO->clientDocumentNo); // only use number part
          $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
          $postingOrdersHoldingTO->dataSource = DS_EDI;
          $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
          $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
           if (substr(trim($lineArr[6]),2,1) == '-') {
             $postingOrdersHoldingTO->orderDate = CommonUtils::formatFromDDsMMsYYYY(trim($lineArr[6]), $fromSeparator = "-");
          } elseif (substr(trim($lineArr[6]),2,1) == '/') {
             $postingOrdersHoldingTO->orderDate = CommonUtils::formatFromDDsMMsYYYY(trim($lineArr[6]), $fromSeparator = "/");
          } else {
             $postingOrdersHoldingTO->orderDate = CommonUtils::formatFromDDsMMsYYYY(trim($lineArr[6]), $fromSeparator = " ");
          }
//        $postingOrdersHoldingTO->orderDate = CommonUtils::formatFromDDsMMsYYYY(trim($lineArr[6]), $fromSeparator = "/"); // convert from dd/mm/yyyy into yyyy-mm-dd

          if (ValidationCommonUtils::checkIsDate($postingOrdersHoldingTO->orderDate, "yyyymmdd", $withSeparator = true) === false) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Line {$processingLine} of file " . basename($onlineFileProcessItem["file_being_processed"]) . " has an invalid order date.";
            $eTO->identifier = ET_SYSTEM;
            return $eTO;
          }

          $postingOrdersHoldingTO->capturedBy = 'TSITSIKAMMA'; // careful with changing this as notifications might run off it
          $postingOrdersHoldingTO->reference = substr(trim($lineArr[7]), 0, 20);
          $postingOrdersHoldingTO->deliveryInstructions = $postingOrdersHoldingTO->reference; // same as reference for now
          $postingOrdersHoldingTO->enforceSameDepot = "N";

          $postingOrdersHoldingTO->offInvoiceDiscount = 0;
          $postingOrdersHoldingTO->offInvoiceDiscountType = '';  

          $postingOrdersHoldingTO->salesAgentStoreIdentifier = trim($lineArr[4]);
          $postingOrdersHoldingTO->debtorsStoreIdentifier = trim($lineArr[4]);
          $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
          $postingOrdersHoldingTO->storeLookupRef = ""; // $lineArr[4];
          $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = "Y";
          $postingOrdersHoldingTO->shipToName = trim($lineArr[13]) . "|" . trim($postingOrdersHoldingTO->salesAgentStoreIdentifier); // store this incase store creation failed
          $postingOrdersHoldingTO->deliverName = trim($lineArr[13]);
          $postingOrdersHoldingTO->oldAccount = ""; // Tsitsikamma does not have a unique store identifier so cannot enforce the existing legacy store reusage !

          // Create the StoreTO
          $postingStoreTO = new PostingStoreTO;
          $postingStoreTO->DMLType = "INSERT";
          $postingStoreTO->principalStoreUId = "";
          $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
          $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[13]));
          $postingStoreTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[14]));
          $postingStoreTO->deliverAdd2 = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[15]));
          $postingStoreTO->deliverAdd3 = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[16]) . " " . trim($lineArr[17]));
          $postingStoreTO->billName = $postingStoreTO->deliverName;
          $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
          $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
          $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
          $postingStoreTO->vatNumber = (strpos(strtoupper(trim($lineArr[17])), 'VAT')!==FALSE) ? substr(trim($lineArr[17]),-10) : ('');
          $postingStoreTO->telNo1 = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[22]));
          $postingStoreTO->depot = ""; // this will be set by the processing script
          $postingStoreTO->deliveryDay = "8";
          $postingStoreTO->noVAT = 0;
          $postingStoreTO->onHold = "0";
          $postingStoreTO->chain = ""; // this will be set by the processing script
          $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
          $postingStoreTO->status = FLAG_STATUS_ACTIVE;
          $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
          $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
          $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;

          $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;

          // lookup special field(s) - enforce this specific one
          $postingSpecialFieldTO = new PostingSpecialFieldTO;
          $postingSpecialFieldTO->DMLType = "INSERT";
          $postingSpecialFieldTO->principal = $postingStoreTO->principal;
          $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
          $postingSpecialFieldTO->fielduid = 251; // Pastel Account
          $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
          $postingSpecialFieldTO->value = trim($lineArr[4]);
          $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;

          // End Create the StoreTO

        } else if ($lineType == "Detail") {
          // depot is set at detail level for pastel files, hoping there is consistency across all detail lines !!!
          if (trim($lineArr[13]) != "")
            $postingOrdersHoldingTO->depotLookupRef = trim($lineArr[13]); // mysqli_real_escape_string($this->dbConn->connection, preg_replace("/^[0]+/", "", trim($lineArr[13])));

          $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
          $postingOrdersHoldingDetailTO->listPrice = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[3]));
          $postingOrdersHoldingDetailTO->discountValue = $postingOrdersHoldingDetailTO->listPrice * ((floatval($lineArr[8]) / 100) / 100); // discount is a percentage sent through without decimals so first div by 100
          $postingOrdersHoldingDetailTO->nettPrice = round($postingOrdersHoldingDetailTO->listPrice - $postingOrdersHoldingDetailTO->discountValue,2); // each
          $postingOrdersHoldingDetailTO->quantity = $lineArr[2];
          $postingOrdersHoldingDetailTO->extPrice = $postingOrdersHoldingDetailTO->nettPrice * $postingOrdersHoldingDetailTO->quantity;
          $postingOrdersHoldingDetailTO->vatRate = VAL_VAT_RATE_TBLSTD; // always expect VAT.
          $postingOrdersHoldingDetailTO->vatAmount = round($postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100, 2);
          $postingOrdersHoldingDetailTO->totalPrice = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;
          $postingOrdersHoldingDetailTO->productCode = trim($lineArr[9]);
          $postingOrdersHoldingDetailTO->productName = trim($lineArr[10]);
          $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
          $postingOrdersHoldingDetailTO->pallets = 0;

          // Only add product to document if satisfies rules
          if (
          (
              (($postingOrdersHoldingDetailTO->productCode != "") && ($postingOrdersHoldingDetailTO->productCode != "'")) &&
              (($postingOrdersHoldingDetailTO->productName != "") && ($postingOrdersHoldingDetailTO->productName != "'"))
          ) ||
          ((is_numeric($postingOrdersHoldingDetailTO->listPrice) === true) && (floatval($postingOrdersHoldingDetailTO->listPrice) > 0))
          ) {
            $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
          }
        } else {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid Line Type found, starting with {$lineType}";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
        }
      } // end of file loop

      // process the final loop to db
      if (isset($postingOrdersHoldingTO)) {
        if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
        }
        $arrTO[] = $postingOrdersHoldingTO;
        unset($postingOrdersHoldingTO);
      }


      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------

    // adaptor for vendor 32

    //PASTEL : Trade Model 7
    function adaptorTOH_VTM7($content, $onlineFileProcessItem) {
      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO=new StoreDAO($this->dbConn);

      $fileArr = explode("\n",$content);

      // put into common TO
      $arrTO=array();
      $processingLine=0;
      foreach ($fileArr as $key=>$line) {

          $processingLine++;

          // convert line to CSV
          $lineArr=str_getcsv($line, ",", '"', "\\");

          if (!isset($lineArr[0]))
            {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Line {$processingLine} of file ".basename($onlineFileProcessItem["file_being_processed"])."is empty";
            $eTO->identifier = ET_SYSTEM;
            return $eTO;
          }

          $lineType=substr($lineArr[0],0,6);

          if(!isset($lineArr[1])){
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "File is malformed!";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }

      if ($lineType == "Header") {

          if (isset($postingOrdersHoldingTO)) {
            if (sizeof($postingOrdersHoldingTO->detailArr) == 0) {
              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "No Detail Lines found for Header Line @line:" . ($key + 1);
              $eTO->identifier = ET_CUSTOMER;
              return $eTO;
            }
            $arrTO[] = $postingOrdersHoldingTO;
            unset($postingOrdersHoldingTO);
          }

          $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
          $postingOrdersHoldingTO->updateProduct="Y";
          $postingOrdersHoldingTO->insertProduct="Y";
          $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
          $postingOrdersHoldingTO->principalUid = $onlineFileProcessItem["principal_uid"];

          $onlineFileProcessingMapping = $importDAO->getMappingFromOIMByPrincipalUId($onlineFileProcessItem["onlineFileProcessingMapping"], $postingOrdersHoldingTO->principalUid);
          if(empty($onlineFileProcessingMapping)) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Unknown Principal Type in adaptorTOH_VTM7 for file " . basename($onlineFileProcessItem["file_being_processed"]);
            $eTO->identifier = ET_SYSTEM;
            return $eTO;
          }

          $postingOrdersHoldingTO->clientDocumentNo = trim($lineArr[1]);
          $postingOrdersHoldingTO->documentNo = preg_replace("/[^0-9]/", "", $postingOrdersHoldingTO->clientDocumentNo); // only use number part
          $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
          $postingOrdersHoldingTO->dataSource = DS_EDI;
          $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
          $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
          $postingOrdersHoldingTO->orderDate = CommonUtils::formatFromDDsMMsYYYY(trim($lineArr[6]), $fromSeparator = "/"); // convert from dd/mm/yyyy into yyyy-mm-dd
          $postingOrdersHoldingTO->invoiceDate = CommonUtils::formatFromDDsMMsYYYY(trim($lineArr[6]), $fromSeparator = "/");
          if (ValidationCommonUtils::checkIsDate($postingOrdersHoldingTO->orderDate, "yyyymmdd", $withSeparator = true) === false) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Line {$processingLine} of file " . basename($onlineFileProcessItem["file_being_processed"]) . " has an invalid order date.";
            $eTO->identifier = ET_SYSTEM;
            return $eTO;          }


          $postingOrdersHoldingTO->capturedBy = 'TM7'; // careful with changing this as notifications might run off it
          $postingOrdersHoldingTO->reference = substr(trim($lineArr[7]), 0, 20);
          //$postingOrdersHoldingTO->deliveryInstructions = $postingOrdersHoldingTO->reference; // same as reference for now
          $postingOrdersHoldingTO->enforceSameDepot = "N";
          //$postingOrdersHoldingTO->salesAgentStoreIdentifier = trim($lineArr[4]);
          //$postingOrdersHoldingTO->debtorsStoreIdentifier = trim($lineArr[4]);
          $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
          $postingOrdersHoldingTO->storeLookupRef = ""; // $lineArr[4];
          $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = "N";
          $postingOrdersHoldingTO->shipToName = trim($lineArr[13]) . "|" . trim($postingOrdersHoldingTO->salesAgentStoreIdentifier); // store this incase store creation failed
          $postingOrdersHoldingTO->deliverName = trim($lineArr[13]);
          $postingOrdersHoldingTO->oldAccount = trim($lineArr[4]);
          $postingOrdersHoldingTO->depotUId = 141;


          $postingStoreTO = new PostingStoreTO;
          $postingStoreTO->DMLType = "INSERT";
          $postingStoreTO->principalStoreUId = "";
          $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
          $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[10]));
          $postingStoreTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[13]));
          $postingStoreTO->deliverAdd2 = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[14]));
          $postingStoreTO->deliverAdd3 = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[15]) . " " . trim($lineArr[17]));
          $postingStoreTO->billName = $postingStoreTO->deliverName;
          $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
          $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
          $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
          //$postingStoreTO->vatNumber = (strpos(strtoupper(trim($lineArr[17])), 'VAT')!==FALSE) ? substr(trim($lineArr[17]),-10) : ('');
          $postingStoreTO->telNo1 = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[22]));
          $postingStoreTO->depot = $postingOrdersHoldingTO->depotUId;
          $postingStoreTO->deliveryDay = "8";
          $postingStoreTO->noVAT = 0;
          $postingStoreTO->onHold = "0";
          $postingSt3oreTO->chain = ""; // this will be set by the processing script
          $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
          $postingStoreTO->status = FLAG_STATUS_ACTIVE;
          $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
          $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
          $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;

          $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;

          // lookup special field(s) - enforce this specific one
          $postingSpecialFieldTO = new PostingSpecialFieldTO;
          $postingSpecialFieldTO->DMLType = "INSERT";
          $postingSpecialFieldTO->principal = $postingStoreTO->principal;
          $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
          $postingSpecialFieldTO->fielduid = 258; // Pastel Account
          $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
          $postingSpecialFieldTO->value = $postingOrdersHoldingTO->oldAccount;
          $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;

      } else if ($lineType == "Detail") {
        // depot is set at detail level for pastel files, hoping there is consistency across all detail lines !!!
        if (trim($lineArr[13]) != "")
          $postingOrdersHoldingTO->depotLookupRef = mysqli_real_escape_string($this->dbConn->connection, preg_replace("/^[0]+/", "", trim($lineArr[13])));

        $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
        $postingOrdersHoldingDetailTO->listPrice = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[3]));
        $postingOrdersHoldingDetailTO->discountValue = $postingOrdersHoldingDetailTO->listPrice * ((floatval($lineArr[8]) / 100) / 100); // discount is a percentage sent through without decimals so first div by 100
        $postingOrdersHoldingDetailTO->nettPrice = round($postingOrdersHoldingDetailTO->listPrice - $postingOrdersHoldingDetailTO->discountValue,2); // each
        $postingOrdersHoldingDetailTO->quantity = $lineArr[2];
        $postingOrdersHoldingDetailTO->extPrice = $postingOrdersHoldingDetailTO->nettPrice * $postingOrdersHoldingDetailTO->quantity;
        if (mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[3])) == mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[4]))){
        $postingOrdersHoldingDetailTO->vatRate = 0; // always expect VAT.
        } else {
        $postingOrdersHoldingDetailTO->vatRate = VAL_VAT_RATE_TBLSTD;
        }
        $postingOrdersHoldingDetailTO->vatAmount = round($postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100, 2);
        $postingOrdersHoldingDetailTO->totalPrice = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;
        $postingOrdersHoldingDetailTO->productCode = trim($lineArr[9]);
        $postingOrdersHoldingDetailTO->productName = trim($lineArr[10]);
        $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
        $postingOrdersHoldingDetailTO->pallets = 0;

        // Only add product to document if satisfies rules
        if (
                (
                (($postingOrdersHoldingDetailTO->productCode != "") && ($postingOrdersHoldingDetailTO->productCode != "'")) &&
                (($postingOrdersHoldingDetailTO->productName != "") && ($postingOrdersHoldingDetailTO->productName != "'"))
                ) ||
                ((is_numeric($postingOrdersHoldingDetailTO->listPrice) === true) && (floatval($postingOrdersHoldingDetailTO->listPrice) > 0))
        ) {
          $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
        }
      } else {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Invalid Line Type found, starting with {$lineType}";
        $eTO->identifier = ET_CUSTOMER;
        return $eTO;
      }
    } // end of file loop

      // process the final loop to db
      if (isset($postingOrdersHoldingTO)) {
          if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }
          $arrTO[] = $postingOrdersHoldingTO;
          unset($postingOrdersHoldingTO);
    }


        $eTO->type = FLAG_ERRORTO_SUCCESS;
        $eTO->description = "Successful";
        $eTO->object = $arrTO;
      return $eTO;
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------
    //Vendor 34, Pioneer
    function adaptorTOH_VP($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;

      $fileArr = explode("\n",$content);
      
      foreach ($fileArr as $key=>$line) {

          $processingLine++;

          // convert line to CSV
          $lineArr=str_getcsv($line, ",", '"', "\\");
         
          if ((!isset($lineArr[0])) || ((substr($lineArr[0],6,3)=="201") && (sizeof($lineArr)<10))){
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Line {$processingLine} of file ".basename($onlineFileProcessItem["file_being_processed"])." could not be converted to an array of 14/29 elements for CSV conversion!";
            $eTO->identifier = ET_SYSTEM;
            return $eTO;
          }
          if (isset($postingOrdersHoldingTO)) {
            if (sizeof($postingOrdersHoldingTO->detailArr) == 0) {
              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "No Detail Lines found for Header Line @line:" . ($key + 1);
              $eTO->identifier = ET_CUSTOMER;
              return $eTO;
            }
            $arrTO[] = $postingOrdersHoldingTO;
            unset($postingOrdersHoldingTO);
          }

          $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
          $postingOrdersHoldingTO->updateProduct="Y";
          $postingOrdersHoldingTO->insertProduct="Y";
          $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
          $postingOrdersHoldingTO->principalUid = $onlineFileProcessItem["principal_uid"];

          $onlineFileProcessingMapping = $importDAO->getMappingFromOIMByPrincipalUId($onlineFileProcessItem["onlineFileProcessingMapping"], $postingOrdersHoldingTO->principalUid);
          if(empty($onlineFileProcessingMapping)) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Unknown Principal Type in adaptorTOH_VP for file " . basename($onlineFileProcessItem["file_being_processed"]);
            $eTO->identifier = ET_SYSTEM;
            return $eTO;
          }

          $postingOrdersHoldingTO->clientDocumentNo = '';
          $postingOrdersHoldingTO->documentNo = '';
          $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
          $postingOrdersHoldingTO->dataSource = DS_EDI;
          $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
          $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
          $postingOrdersHoldingTO->orderDate = CommonUtils::formatFromDDsMMsYYYY(trim($lineArr[0]), $fromSeparator = "."); // convert from dd/mm/yyyy into yyyy-mm-dd
          $postingOrdersHoldingTO->invoiceDate = CommonUtils::formatFromDDsMMsYYYY(trim($lineArr[0]), $fromSeparator = ".");
          if (ValidationCommonUtils::checkIsDate($postingOrdersHoldingTO->orderDate, "yyyymmdd", $withSeparator = true) === false) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Line {$processingLine} of file " . basename($onlineFileProcessItem["file_being_processed"]) . " has an invalid order date.";
            $eTO->identifier = ET_SYSTEM;
            return $eTO;          }

          $postingOrdersHoldingTO->capturedBy = 'Pioneer'; // careful with changing this as notifications might run off it
          $postingOrdersHoldingTO->reference = mysqli_real_escape_string($this->dbConn->connection, substr(trim($lineArr[1]), 0, 20));
          $postingOrdersHoldingTO->deliveryInstructions = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[9]));
          $postingOrdersHoldingTO->enforceSameDepot = "N";
          //$postingOrdersHoldingTO->salesAgentStoreIdentifier = trim($lineArr[4]);
          //$postingOrdersHoldingTO->debtorsStoreIdentifier = trim($lineArr[4]);
          $postingOrdersHoldingTO->documentTypeUId = DT_UPLIFTS;
          $postingOrdersHoldingTO->storeLookupRef = ""; // $lineArr[4];
          $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
          $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = "N";
          $postingOrdersHoldingTO->shipToName = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[3])) ;
          $postingOrdersHoldingTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[3]));
          $postingOrdersHoldingTO->oldAccount = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[2]));
          $postingOrdersHoldingTO->depotUId = 205;

          $postingStoreTO = new PostingStoreTO;
          $postingStoreTO->DMLType = "INSERT";
          $postingStoreTO->principalStoreUId = "";
          $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
          $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[3]));
          $postingStoreTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[6]));
          $postingStoreTO->deliverAdd2 = "";
          $postingStoreTO->deliverAdd3 = "";
          $postingStoreTO->billName = $postingStoreTO->deliverName;
          $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
          $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
          $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
          $postingStoreTO->telNo1 = "";
          $postingStoreTO->depot = $postingOrdersHoldingTO->depotUId;
          $postingStoreTO->deliveryDay = "8";
          $postingStoreTO->noVAT = 0;
          $postingStoreTO->onHold = "0";
          $postingStoreTO->Chain = ""; // this will be set by the processing script
          $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
          $postingStoreTO->status = FLAG_STATUS_ACTIVE;
          $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
          $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
          $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;

          $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;

          $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
          
          $postingOrdersHoldingDetailTO->quantity = 1;
          $postingOrdersHoldingDetailTO->productCode = "ANTEL001";
          $postingOrdersHoldingDetailTO->productName = "UPLIFT BAG";
          $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor

          $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
    } // end of file loop

      // process the final loop to db
      if (isset($postingOrdersHoldingTO)) {
          if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }
          $arrTO[] = $postingOrdersHoldingTO;
          unset($postingOrdersHoldingTO);
      }

      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------
    //Vendor 35, Stafford
    function adaptorTOH_VS($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;
      
      $fileArr = explode("\n",$content);

      //test integrity of file here.
      //file has Header AND REMOVE!
      $lineArr = str_getcsv($fileArr[0], ",", '"', "\\");
      print_r($lineArr);     

      if($lineArr[0] != 'ORDER_TYPE'){
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "File Integrity: Header Row is missing/Unknown Header Field at Pos 1";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
      } else {
          array_shift($fileArr);
      }
      
      print_r($fileArr); 
      
      foreach ($fileArr as $key=>$line) {

          $processingLine++;
          // convert line to CSV
          $lineArr=str_getcsv($line, ",", '"', "\\");

          if ((!isset($lineArr[0])) || ((substr($lineArr[0],6,3)=="O") && (sizeof($lineArr)<15))){
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Line {$processingLine} of file ".basename($onlineFileProcessItem["file_being_processed"])." could not be converted to an array of 14/29 elements for CSV conversion!";
            $eTO->identifier = ET_SYSTEM;
            return $eTO;
          }
          
          /*******************
          *   ORDER HEAD
          *******************/
          $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
          $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
          $postingOrdersHoldingTO->principalUid = 289;
          $postingOrdersHoldingTO->updateProduct="Y";
          $postingOrdersHoldingTO->insertProduct="Y";
          $postingOrdersHoldingTO->reference = trim($lineArr[13]);
          $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
          $postingOrdersHoldingTO->dataSource = DS_EDI;
          $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
          $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
          $postingOrdersHoldingTO->requestedDeliveryDate = "";
          $postingOrdersHoldingTO->capturedBy = 'STAFFORD';
          $postingOrdersHoldingTO->depotLookupRef = (mysqli_real_escape_string($this->dbConn->connection, trim($lineArr[18])));
          $postingOrdersHoldingTO->orderDate = CommonUtils::formatCompactDate(trim($lineArr[2]));
          
          if ($postingOrdersHoldingTO->orderDate === false) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Order date invalid format or empty";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }
          $postingOrdersHoldingTO->shipToName = str_replace("'",'',trim($lineArr[7]));
          $postingOrdersHoldingTO->deliverName = str_replace("'",'',trim($lineArr[10]));
          $postingOrdersHoldingTO->clientDocumentNo = "00".trim($lineArr[4]);
          $postingOrdersHoldingTO->documentNo = $postingOrdersHoldingTO->clientDocumentNo;
          $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;

          $postingOrdersHoldingTO->enforceSameDepot = "N";
          $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
          $postingOrdersHoldingTO->oldAccount = trim($lineArr[5]);

          /*---------------------------
           *    STORE LOOKUP LOGIC
           *--------------------------*/
          /*******************
           *   CREATE STORE
           *******************/
            $postingStoreTO = new PostingStoreTO;
            $postingStoreTO->DMLType = "INSERT";
            $postingStoreTO->principalStoreUId ="";
            $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;

            $storeDeliverArr = explode("\r",$lineArr[11]);  //store address has 'Carriage Returns' between values.
            $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim($lineArr[10])));
            $postingStoreTO->deliverAdd1 = (isset($storeDeliverArr[1])) ? (mysqli_real_escape_string($this->dbConn->connection, trim($storeDeliverArr[1]))) : ('');
            $postingStoreTO->deliverAdd2 = (isset($storeDeliverArr[2])) ? (mysqli_real_escape_string($this->dbConn->connection, trim($storeDeliverArr[2]))) : ('');
            $postingStoreTO->deliverAdd3 = (isset($storeDeliverArr[3])) ? (mysqli_real_escape_string($this->dbConn->connection, trim($storeDeliverArr[3]))) : ('');

            $storeBillArr = explode("\r",$lineArr[7]);  //store address has 'Carriage Returns' between values.
            $postingStoreTO->billName = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim($lineArr[6])));
            $postingStoreTO->billAdd1   = (isset($storeBillArr[1])) ? (mysqli_real_escape_string($this->dbConn->connection, trim($storeBillArr[1]))) : ('');
            $postingStoreTO->billAdd2   = (isset($storeBillArr[2])) ? (mysqli_real_escape_string($this->dbConn->connection, trim($storeBillArr[2]))) : ('');
            $postingStoreTO->billAdd3   = (isset($storeBillArr[3])) ? (mysqli_real_escape_string($this->dbConn->connection, trim($storeBillArr[3]))) : ('');
            $postingStoreTO->vatNumber = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim($lineArr[8])));
            $postingStoreTO->depot = ""; // this will be set by the processing script
            $postingStoreTO->deliveryDay = "8";
            $postingStoreTO->noVAT="0";
            $postingStoreTO->onHold = "0";
            $postingStoreTO->chain = ""; // this will be set by the processing script
            $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
            $postingStoreTO->status=FLAG_STATUS_ACTIVE;
            $postingStoreTO->vendorCreatedByUId=$postingOrdersHoldingTO->vendorUid;
            $postingStoreTO->ownedBy=$postingOrdersHoldingTO->vendorUid;
            $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;

            $postingOrdersHoldingTO->postingStoreTO=$postingStoreTO;

            foreach($fileArr as $d){
            	
               $processingLine++;
              // convert line to CSV
               $detArr=str_getcsv($d, ",", '"', "\\");

               if ((!isset($detArr[0])) || ((substr($detArr[0],6,3)=="O") && (sizeof($detArr)<15))){
                 $eTO->type = FLAG_ERRORTO_ERROR;
                 $eTO->description = "Line {$processingLine} of file ".basename($onlineFileProcessItem["file_being_processed"])." could not be converted to an array of 14/29 elements for CSV conversion!";
                 $eTO->identifier = ET_SYSTEM;
                 return $eTO;
               }   	
 
              /******************
              *   ORDER DETAILS
              *******************/
              $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
           
              // should not be necessary to check document type as other types should end up as zeros
              $postingOrdersHoldingDetailTO->listPrice     = trim($detArr[21]);
              $postingOrdersHoldingDetailTO->discountValue = trim($detArr[20]);
              $postingOrdersHoldingDetailTO->nettPrice     = round($postingOrdersHoldingDetailTO->listPrice - $postingOrdersHoldingDetailTO->discountValue,2); // each
              $postingOrdersHoldingDetailTO->quantity      = trim($detArr[14]);
              $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->nettPrice*$postingOrdersHoldingDetailTO->quantity;
              $postingOrdersHoldingDetailTO->vatRate       = "0015.00";
              $postingOrdersHoldingDetailTO->vatAmount     = round($postingOrdersHoldingDetailTO->extPrice*$postingOrdersHoldingDetailTO->vatRate/100,2);
              $postingOrdersHoldingDetailTO->totalPrice    = $postingOrdersHoldingDetailTO->extPrice+$postingOrdersHoldingDetailTO->vatAmount;
              $postingOrdersHoldingDetailTO->productCode   = trim(trim($detArr[16]));
           
              $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
              $postingOrdersHoldingDetailTO->pallets = 0;
              $postingOrdersHoldingDetailTO->productName = trim(trim($detArr[17]));
              $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
            }

    } // end of file loop
    
    print_r($postingOrdersHoldingTO);

      // process the final loop to db
      if (isset($postingOrdersHoldingTO)) {
          if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }
          $arrTO[] = $postingOrdersHoldingTO;
          unset($postingOrdersHoldingTO);
      }

      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;
   } 
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------    
    // V10 is vendor 10 - ITD not version 10

    function adaptorTOH_V10($content, $onlineFileProcessItem) {
    	// NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
    	global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

    	$eTO = new ErrorTO;

		$fileArray = FileParser::xmlToArray($content);
		
		print_r($fileArray);

    // do basic validation in place of XSD, if tag is not repateated, it wont create an array
    	if ((!isset($fileArray["vendor"]["vendor_name"])) ||
    		  (
    		      ($fileArray["vendor"]["vendor_name"] != "Contactim a Division of")
    		  )
    		) {
  			$eTO->type = FLAG_ERRORTO_ERROR;
	   		$eTO->description = "File Structural problem in adaptorTOHT_V10 for file ".basename($onlineFileProcessItem["file_being_processed"]);
	   		$eTO->identifier = ET_SYSTEM;
	    	return $eTO;
  		}

    	// put into common TO, ignore all the other extra fields
    	// NOTE: ITD produce diff files so this is ok, otherwise you can't use this method !
    	$onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalUId($onlineFileProcessItem["onlineFileProcessingMapping"], $onlineFileProcessItem["principal_uid"]);
    	// this adaptor file is only used for one principal, so treat null or exact principal as same, but must be found !
    	if (empty($onlineFileProcessingMapping)) {
  			$eTO->type = FLAG_ERRORTO_ERROR;
	   		$eTO->description = "Could not retrieve online file principal mappings";
	   		$eTO->identifier = ET_SYSTEM;
	    	return $eTO;
  		}

    	$arrTO=array();
    	// if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
    	if (!isset($fileArray["order"][0])) {
    		$temp=$fileArray["order"];
    		unset($fileArray["order"]);
    		$fileArray["order"][0]=$temp;
    	}

    	// put into common TO
    	$arrTO=array();
			$principalContactim="70";

    	//echo "<pre>"; print_r($fileArray["Order"]); echo "</pre>";
    	foreach ($fileArray["order"] as $o) {

        // do basic validation in place of XSD, if tag is not repateated, it wont create an array
        if ((!isset($o["order_hdr"])) ||
            (
              (!isset($o["order_det"]["product"]["0"]["line_no"])) &&
              (!isset($o["order_det"]["product"]["line_no"]))
            )
          ) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "File Structural problem in adaptorTOHT_V10 for file ".basename($onlineFileProcessItem["file_being_processed"])." principal {$onlineFileProcessingMapping["principal_uid"]} for document {$o["order_hdr"]["order_no"]}";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
        }

    		$postingOrdersHoldingTO = new PostingOrdersHoldingTO();
        $postingOrdersHoldingTO->updateProduct="N";
        $postingOrdersHoldingTO->skipInvoiceComputationCheck="Y";
    		$postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
    		$postingOrdersHoldingTO->principalUid=$onlineFileProcessingMapping["principal_uid"];
    		$postingOrdersHoldingTO->wsUniqueCreatorId=$o["order_hdr"]["transaction_no"];
    		$postingOrdersHoldingTO->depotLookupRef=mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["site"]));
				$postingOrdersHoldingTO->updateStoreDepot="N";
    		if (trim($postingOrdersHoldingTO->depotLookupRef)=="") {
		  		// do not allow to continue - reject the file and ITD will be notified in confirmation
		  		$eTO->type = FLAG_ERRORTO_ERROR;
		   		$eTO->description = "A mandatory field (<order_hdr><site>) is empty for transaction_no {$postingOrdersHoldingTO->wsUniqueCreatorId}.";
		   		$eTO->identifier = ET_CUSTOMER;
		    	return $eTO;
		  	}
    		$postingOrdersHoldingTO->reference=mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["customer_pono"])); // or order_no ??
    		$postingOrdersHoldingTO->orderDate = mysqli_real_escape_string($this->dbConn->connection, trim(CommonUtils::formatFromDDsMMsYYYY ($o["order_hdr"]["order_date"],$fromSeparator="/")));
    		if ($postingOrdersHoldingTO->orderDate===false) $postingOrdersHoldingTO->orderDate=gmdate("Y-m-d");
    		$postingOrdersHoldingTO->requestedDeliveryDate = mysqli_real_escape_string($this->dbConn->connection, trim(CommonUtils::formatFromDDsMMsYYYY ($o["order_hdr"]["required_date"],$fromSeparator="/")));
    		if ($postingOrdersHoldingTO->requestedDeliveryDate===false) $postingOrdersHoldingTO->requestedDeliveryDate="";
    		$postingOrdersHoldingTO->deliveryInstructions=mysqli_real_escape_string($this->dbConn->connection, substr(trim($o["order_hdr"]["comment1"])." ".trim($o["order_hdr"]["comment2"]),0,50));
    		$postingOrdersHoldingTO->clientDocumentNo=mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["order_no"]));
    		$postingOrdersHoldingTO->documentNo='';
    		$postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
    		$postingOrdersHoldingTO->dataSource = DS_EDI;
    		$postingOrdersHoldingTO->capturedBy = 'SGX';
    		$postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
    		$postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
   			$postingOrdersHoldingTO->chainLookupRef=""; // ???
   			$postingOrdersHoldingTO->storeLookupRef=""; // ???
		  	$postingOrdersHoldingTO->documentTypeUId = DT_ORDINV; // Transfers as well in future
		  	$postingOrdersHoldingTO->principalStoreUId=""; // let the processing script lookup the store
		  	$postingOrdersHoldingTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["branch"]["branch_name"]));
		  	$postingOrdersHoldingTO->oldAccount=mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["branch"]["branch_no"])); // ship to
		  	if (trim($postingOrdersHoldingTO->oldAccount)=="") {
		  		// do not allow to continue - reject the file and ITD will be notified in confirmation
		  		$eTO->type = FLAG_ERRORTO_ERROR;
		   		$eTO->description = "A mandatory field (<order_hdr><branch><branch_no>) is empty for transaction_no {$postingOrdersHoldingTO->wsUniqueCreatorId}.";
		   		$eTO->identifier = ET_CUSTOMER;
		    	return $eTO;
		  	}

		  	// supply the store details in case the processor cant find the store
		  	$postingStoreTO = new PostingStoreTO;
  			$postingStoreTO->DMLType = "INSERT";
  			$postingStoreTO->principalStoreUId ="";
  			$postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
  			$postingStoreTO->deliverName = $postingOrdersHoldingTO->deliverName;
  			$postingStoreTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["branch"]["branch_addr1"]));
  			$postingStoreTO->deliverAdd2 = mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["branch"]["branch_addr2"]));
  			$postingStoreTO->deliverAdd3 = mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["branch"]["branch_addr3"]).", ".trim($o["order_hdr"]["branch"]["branch_postcode"]));
  			$postingStoreTO->billName = mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["buyer"]["buyer_name"]));
  			$postingStoreTO->billAdd1 = mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["buyer"]["buyer_addr1"]));
  			$postingStoreTO->billAdd2 = mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["buyer"]["buyer_addr2"]));
  			$postingStoreTO->billAdd3 = mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["buyer"]["buyer_addr3"]).", ".trim($o["order_hdr"]["buyer"]["buyer_postcode"]));
  			$postingStoreTO->vatNumber = ((isset($o["order_hdr"]["buyer"]["buyer_vatno"]))?mysqli_real_escape_string($this->dbConn->connection, trim($o["order_hdr"]["buyer"]["buyer_vatno"])):"");
  			$postingStoreTO->depot = ""; // this will be set by the processing script
  			$postingStoreTO->deliveryDay = "8";
  			$postingStoreTO->noVAT=((floatval($o["order_hdr"]["order_vat_perc"])==floatval(VAL_VAT_RATE_TBLSTD))?0:1); // ITD do send thru garbage here so not reliable. We just pass on values received and dont use the store
  			$postingStoreTO->onHold = "0";
  			$postingStoreTO->chain = ""; // this will be set by the processing script
  			$postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
  			$postingStoreTO->status=FLAG_STATUS_ACTIVE;
  			$postingStoreTO->vendorCreatedByUId=$postingOrdersHoldingTO->vendorUid;
  			$postingStoreTO->ownedBy=$postingOrdersHoldingTO->vendorUid;
  			$postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;

        $postingStoreTO->updatePrincipalStore = 'Y';

  			$postingOrdersHoldingTO->postingStoreTO=$postingStoreTO;

 		  	// create the detail
		  	// if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
	    	if (!isset($o["order_det"]["product"][0])) {
	    		$temp=$o["order_det"]["product"];
	    		unset($o["order_det"]["product"]);
	    		$o["order_det"]["product"][0]=$temp;
	    	}

		  	foreach ($o["order_det"]["product"] as $ol) {
		  		$postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

		  		$postingOrdersHoldingDetailTO->clientLineNo=mysqli_real_escape_string($this->dbConn->connection, $ol["line_no"]);
		  		$postingOrdersHoldingDetailTO->pallets = 0;
		  		$postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor

		  		// we do not do discounts this way anymore as ITD are inconsistent between CT and MRS file formats
		  		/*
					// discounts are a little tricky, as if it is empty, the parser makes it an empty array because the innermost tags are missing
					if (!isset($ol["deal_discounts"]["deal_details"]["deal_value"])) {
					  $postingOrdersHoldingDetailTO->discountValue = 0;
					} else if (is_array($ol["deal_discounts"]["deal_details"]["deal_value"])) {
		    		// for the above condition, it is important to include [deal_value] because of the array conversion i mentioned above and we want to differentiate between array() and array(with tags)
		    		$postingOrdersHoldingDetailTO->discountValue = 0;
		    		foreach ($ol["deal_discounts"]["deal_details"]["deal_value"] as $dv) {
		    			$postingOrdersHoldingDetailTO->discountValue+=$dv; // allow signage (-ive decreases list price, +ive incr which is opposite to your discount_value usage which assumes +ive)
		    		}
		    	} else if (isset($ol["deal_discounts"]["deal_details"]["deal_value"])) {
		    		$postingOrdersHoldingDetailTO->discountValue = $ol["deal_discounts"]["deal_details"]["deal_value"];
		    	} else {
		    		$postingOrdersHoldingDetailTO->discountValue = 0;
					}

					$postingOrdersHoldingDetailTO->discountValue = $postingOrdersHoldingDetailTO->discountValue*-1; // reverse it so it is compatible with our system ie. discount_value assumed to be +ive which reduces list price
*/

          $postingOrdersHoldingDetailTO->quantity = mysqli_real_escape_string($this->dbConn->connection, $ol["quantity"]); // not customer_quantity
          
          switch (mysqli_real_escape_string($this->dbConn->connection, trim($ol["supplier_product_code"]))) {
            case '184467':
              $prodc = 'PP2108';
              break;
           case '184467':
              $prodc = 'PP2108';
              break;
           case '136643':
              $prodc = 'PP2109';
              break;
           case '593528':
              $prodc = 'PP2110';
              break;
            case '593955':
              $prodc = 'PP2111';
              break;
           case '113221':
              $prodc = 'PP2207';
              break; 
           case '136959':
              $prodc = 'PP2208';
              break; 
           case '228697':
              $prodc = 'PP2209';
              break; 
           case '130294':
              $prodc = 'PP2402';
              break; 
            case '193054':
              $prodc = 'PP2403';
              break;
            case '259449':
              $prodc = 'PP2421';
              break;
            case '242370':
              $prodc = 'PP2503';
              break;
            case '192408':
              $prodc = 'PP2408';
              break;
            case '195551':
              $prodc = 'PP2701';
              break;
            case '192451':
              $prodc = 'PP2451';
              break;
            case '192452':
              $prodc = 'PP2452';
              break;
            case '193054':
              $prodc = 'PP2403';
              break;                            
              
           default:
              $prodc = (mysqli_real_escape_string($this->dbConn->connection, trim($ol["supplier_product_code"])));
           }
           
          $postingOrdersHoldingDetailTO->productCode = $prodc; // must be the ITD code, no principals
					$postingOrdersHoldingDetailTO->productName = mysqli_real_escape_string($this->dbConn->connection, trim($ol["product_description"]));
					// do not load the GTIN so as to force the usage of productCode for lookups
					// $postingOrdersHoldingDetailTO->productGTIN = mysqli_real_escape_string($this->dbConn->connection, trim($ol["ean_no"]));
					$postingOrdersHoldingDetailTO->listPrice = mysqli_real_escape_string($this->dbConn->connection, $ol["list_price_excl_vat"]);
					$postingOrdersHoldingDetailTO->nettPrice = $ol["unit_value_excl_vat"];
					$postingOrdersHoldingDetailTO->discountValue = round($postingOrdersHoldingDetailTO->listPrice-$postingOrdersHoldingDetailTO->nettPrice,2);
					$postingOrdersHoldingDetailTO->extPrice = $ol["extended_value_excl_vat"];
					$postingOrdersHoldingDetailTO->vatRate = $o["order_hdr"]["order_vat_perc"];
					$postingOrdersHoldingDetailTO->vatAmount = $ol["extended_vat"];
					$postingOrdersHoldingDetailTO->totalPrice =$ol["extended_value_incl_vat"];

		  		$postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
		  	}

  			$arrTO[]=$postingOrdersHoldingTO;
    	}

   		$eTO->type = FLAG_ERRORTO_SUCCESS;
   		$eTO->description = "Successful";
   		$eTO->object = $arrTO;

    	return $eTO;

    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------    
    //Vendor 6, Smollan = Copy File for Antel Processing
    function adaptorTOH_V6B($content, $onlineFileProcessItem) {

      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      $eTO = new ErrorTO;
      
      $arrTO=array();
      
      $tofile = $ROOT. '../antel_system/ftp/smollan/out/' . basename($onlineFileProcessItem["file_being_processed"]);
      
    	copy( $onlineFileProcessItem["file_being_processed"], $tofile);
 
   		$eTO->type = FLAG_ERRORTO_SUCCESS;
   		$eTO->description = "Successful";
   		$eTO->object = $arrTO;

    	return $eTO; 
 }   	
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------
  
    //Vendor 38, System
    function adaptorTOH_VZ($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;

      $fileArr = explode("\n",$content);
      // check line total
      $totalLineCnt=sizeof($fileArr);
      if ("T,".str_pad($totalLineCnt,5,'0',STR_PAD_LEFT) != $fileArr[$totalLineCnt-1]) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "File Trailer total lines do not match".$fileArr[$totalLineCnt-1];
        $eTO->identifier = ET_CUSTOMER;
        return $eTO;
      }
      
      foreach ($fileArr as $key=>$line) {
      	
       // convert line to CSV
        $lineArr=str_getcsv($line, ",", '"', "\\");
        $lineType = trim($lineArr[0]);

        if ($lineType=="H") { //HEADER : 1
        	
        	if (isset($postingOrdersHoldingTO)) {
        	    $arrTO[] = $postingOrdersHoldingTO;
              unset($postingOrdersHoldingTO);
        	}
           /*******************
           *   ORDER HEAD
           *******************/
          $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
          $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
          $postingOrdersHoldingTO->principalUid = $lineArr[2];
          $postingOrdersHoldingTO->updateProduct="Y";
          $postingOrdersHoldingTO->insertProduct="Y";
          $postingOrdersHoldingTO->reference = trim(str_replace("'",'',$lineArr[4]));
          $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
          $postingOrdersHoldingTO->dataSource = DS_EDI;
          $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
          $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
          $postingOrdersHoldingTO->requestedDeliveryDate = "";
          $postingOrdersHoldingTO->capturedBy = 'SYSTEM';
          $postingOrdersHoldingTO->depotLookupRef = (mysqli_real_escape_string($this->dbConn->connection, $lineArr[10]));

          $postingOrdersHoldingTO->orderDate = CommonUtils::formatCompactDate(trim($lineArr[3]));
          echo CommonUtils::formatCompactDate(trim($lineArr[3]));
          if ($postingOrdersHoldingTO->orderDate === false) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Order date invalid format or empty";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }

          
          $postingOrdersHoldingTO->shipToName = str_replace("'",'',trim($lineArr[5]));
          $postingOrdersHoldingTO->deliverName = str_replace("'",'',trim($lineArr[5]));
          $postingOrdersHoldingTO->clientDocumentNo = "00";
          $postingOrdersHoldingTO->documentNo = '';
          $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;

          $postingOrdersHoldingTO->enforceSameDepot = "Y";
          $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
          $postingOrdersHoldingTO->oldAccount = trim($lineArr[9]);
          
          /*---------------------------
           *    STORE LOOKUP LOGIC
           *--------------------------*/

            /*******************
            *   CREATE STORE
            *******************/
            $postingStoreTO = new PostingStoreTO;
            $postingStoreTO->DMLType = "INSERT";
            $postingStoreTO->principalStoreUId ="";
            $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
            $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, $postingOrdersHoldingTO->deliverName);
            $postingStoreTO->deliverAdd1 = str_replace("'",'',trim($lineArr[6]));
            $postingStoreTO->deliverAdd2 = str_replace("'",'',trim($lineArr[7]));
            $postingStoreTO->deliverAdd3 = str_replace("'",'',trim($lineArr[8]));
            $postingStoreTO->billName    = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim($lineArr[4])));
            $postingStoreTO->billAdd1    = '';
            $postingStoreTO->billAdd2    = '';
            $postingStoreTO->billAdd3    = '';
            $postingStoreTO->vatNumber  =  '';
            $postingStoreTO->depot = "";
            $postingStoreTO->deliveryDay = "8";
            $postingStoreTO->noVAT="0";
            $postingStoreTO->onHold = "0";
            $postingStoreTO->chain = ""; // this will be set by the processing script
            $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
            $postingStoreTO->status=FLAG_STATUS_ACTIVE;
            $postingStoreTO->vendorCreatedByUId=$postingOrdersHoldingTO->vendorUid;
            $postingStoreTO->ownedBy=$postingOrdersHoldingTO->vendorUid;
            $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;
            
            $postingOrdersHoldingTO->postingStoreTO=$postingStoreTO;
            
            $SecondDetail = $FirstDetail = 0;
            
        }
        elseif ($lineType=="D") {      

              /******************
              *   ORDER DETAILS
              *******************/

              $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
           
              // should not be necessary to check document type as other types should end up as zeros
              $postingOrdersHoldingDetailTO->listPrice     = trim($lineArr[5]);
              $postingOrdersHoldingDetailTO->quantity      = trim($lineArr[4]);
              $postingOrdersHoldingDetailTO->nettPrice     = round($postingOrdersHoldingDetailTO->listPrice - $postingOrdersHoldingDetailTO->discountValue,2); // each
              $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->nettPrice*$postingOrdersHoldingDetailTO->quantity;
              $postingOrdersHoldingDetailTO->vatRate       = "0015.00";
              $postingOrdersHoldingDetailTO->vatAmount     = round($postingOrdersHoldingDetailTO->extPrice*$postingOrdersHoldingDetailTO->vatRate/100,2);
              $postingOrdersHoldingDetailTO->totalPrice    = $postingOrdersHoldingDetailTO->extPrice+$postingOrdersHoldingDetailTO->vatAmount;
              $postingOrdersHoldingDetailTO->productCode   = trim($lineArr[2]);
              $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
              $postingOrdersHoldingDetailTO->pallets       = 0;
              $postingOrdersHoldingDetailTO->productName   = trim($lineArr[3]);
              $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
              
              $PostingBillingInstructionsTO = new PostingBillingInstructionsTO;
              
              if(trim($lineArr[7]) == '' && $FirstDetail == 0) {
                   $PostingBillingInstructionsTO->StartDate = trim($lineArr[9]);
                   $PostingBillingInstructionsTO->EndDate   = trim($lineArr[10]);
                   $postingOrdersHoldingTO->deliveryInstructions = "Billing Period " . $PostingBillingInstructionsTO->StartDate . " to " . $PostingBillingInstructionsTO->EndDate;
                   $FirstDetail = 1;                 	
              }
              if(trim($lineArr[7]) <> '' && $SecondDetail == 0) {
              	
                   $SecondDetail = 1;
                   $FirstDetail  = 1;

                   $PostingBillingInstructionsTO->StartDate = trim($lineArr[9]);
                   $PostingBillingInstructionsTO->EndDate   = trim($lineArr[10]);
                   $PostingBillingInstructionsTO->ToPercent = trim($lineArr[8]);
                   $PostingBillingInstructionsTO->TurnOver   = trim($lineArr[7]);
                   $postingOrdersHoldingTO->deliveryInstructions = "+Billing Period   " . $PostingBillingInstructionsTO->StartDate .
                                                                   " to " . $PostingBillingInstructionsTO->EndDate .
                                                                   "+Rate %     " . number_format(floatval(trim($PostingBillingInstructionsTO->ToPercent)),2,"."," ")  .
                                                                   "+Turnover  R" . number_format(floatval(trim($PostingBillingInstructionsTO->TurnOver)),0,"."," ") ;             	
             }  
        } elseif ($lineType=="T") {             
            $arrTO[] = $postingOrdersHoldingTO;
            unset($postingOrdersHoldingTO);
        
        } 
    }
      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;

   } 
    
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------           

    //Vendor 40, System
    // Convert a CSV file with orders 
    //Allocation	Created on	Article	Article Description	Site/DC	Site/DC Desc	Unit Qty	Unit Qty UoM	Outer Case/Case Unit	Vendor order	Warehouse Order
    //Principal,Date, Product Code, Product Description, Store Branch, Store, Unit Qty, Unit Qty UoM,Outer Case/Case Unit,Order Number	Warehouse Order
    
    function adaptorTOH_HASH($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;

      $fileArr = explode("\n",$content);
      // check line total
      $totalLineCnt=sizeof($fileArr);
      

      $lineType = '';
      foreach ($fileArr as $key=>$line) {
      	
       // convert line to CSV
        $lineArr=str_getcsv($line, ",", '"', "\\");

  print_r($lineArr);        
        if ($lineType<>trim($lineArr[3])) { //HEADER : 1
        	
        	$lineType = trim($lineArr[3]);
        	
        	if (isset($postingOrdersHoldingTO)) {
        	    $arrTO[] = $postingOrdersHoldingTO;
              unset($postingOrdersHoldingTO);
        	}
           /*******************
           *   ORDER HEAD
           *******************/
          $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
          $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
          $postingOrdersHoldingTO->principalUid = 207;
          $postingOrdersHoldingTO->updateProduct="N";
          $postingOrdersHoldingTO->insertProduct="N";
          $postingOrdersHoldingTO->reference = trim(str_replace("'",'',$lineArr[4]));
          $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
          $postingOrdersHoldingTO->dataSource = DS_EDI;
          $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
          $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
          $postingOrdersHoldingTO->requestedDeliveryDate = "";
          $postingOrdersHoldingTO->capturedBy = 'SYSTEM';
          $postingOrdersHoldingTO->depotLookupRef = "";

          $postingOrdersHoldingTO->orderDate = CommonUtils::formatCompactDate('20170324');
          
          if ($postingOrdersHoldingTO->orderDate === false) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Order date invalid format or empty";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
          }

          
          $postingOrdersHoldingTO->shipToName = str_replace("'",'',trim($lineArr[6]));
          $postingOrdersHoldingTO->deliverName = str_replace("'",'',trim($lineArr[6]));
          $postingOrdersHoldingTO->documentNo = '';
          $postingOrdersHoldingTO->documentTypeUId = DT_QUOTATION;

          $postingOrdersHoldingTO->enforceSameDepot = "N";
          $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
          $postingOrdersHoldingTO->oldAccount = trim($lineArr[21]);
          
 
          /*---------------------------
           *    STORE LOOKUP LOGIC
           *--------------------------*/

            /*******************
            *   CREATE STORE
            *******************/
            $postingStoreTO = new PostingStoreTO;
            $postingStoreTO->DMLType = "INSERT";
            $postingStoreTO->principalStoreUId ="";
            $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
            $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, $postingOrdersHoldingTO->deliverName);
            $postingStoreTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim($lineArr[7])));
            $postingStoreTO->deliverAdd2 = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim($lineArr[8])));
            $postingStoreTO->deliverAdd3 = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim($lineArr[9])));
            $postingStoreTO->billName    = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim($lineArr[6])));
            $postingStoreTO->billAdd1    = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim($lineArr[7])));
            $postingStoreTO->billAdd2    = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim($lineArr[8])));
            $postingStoreTO->billAdd3    = mysqli_real_escape_string($this->dbConn->connection, str_replace("'",'',trim($lineArr[9])));
            $postingStoreTO->vatNumber  =  "";
            $postingStoreTO->depot = "";
            $postingStoreTO->deliveryDay = "8";
            $postingStoreTO->noVAT="0";
            $postingStoreTO->onHold = "0";
            $postingStoreTO->chain = ""; // this will be set by the processing script
            $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
            $postingStoreTO->status=FLAG_STATUS_ACTIVE;
            $postingStoreTO->vendorCreatedByUId=$postingOrdersHoldingTO->vendorUid;
            $postingStoreTO->ownedBy=$postingOrdersHoldingTO->vendorUid;
            $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;

            $postingOrdersHoldingTO->postingStoreTO=$postingStoreTO;
            
        }
              /******************
              *   ORDER DETAILS
              *******************/

              $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
           
              // should not be necessary to check document type as other types should end up as zeros
              $postingOrdersHoldingDetailTO->listPrice     = trim($lineArr[5]);
              $postingOrdersHoldingDetailTO->quantity      = trim($lineArr[3]);
              $postingOrdersHoldingDetailTO->nettPrice     = round($postingOrdersHoldingDetailTO->listPrice - $postingOrdersHoldingDetailTO->discountValue,2); // each
              $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->nettPrice*$postingOrdersHoldingDetailTO->quantity;
              $postingOrdersHoldingDetailTO->vatRate       = "0015.00";
              $postingOrdersHoldingDetailTO->vatAmount     = round($postingOrdersHoldingDetailTO->extPrice*$postingOrdersHoldingDetailTO->vatRate/100,2);
              $postingOrdersHoldingDetailTO->totalPrice    = $postingOrdersHoldingDetailTO->extPrice+$postingOrdersHoldingDetailTO->vatAmount;
              $postingOrdersHoldingDetailTO->productCode   = trim($lineArr[1]);
              $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
              $postingOrdersHoldingDetailTO->pallets       = 0;
              $postingOrdersHoldingDetailTO->productName   = trim($lineArr[2]);
              $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
        } 
                         
        $arrTO[] = $postingOrdersHoldingTO;
        unset($postingOrdersHoldingTO);
 
        $eTO->type = FLAG_ERRORTO_SUCCESS;
        $eTO->description = "Successful";
        $eTO->object = $arrTO;
        return $eTO;

   } 
    
// *********************************************************************************************************************************************
// *********************************************************************************************************************************************
// ----------------------------------------------------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------------------------------------           

    //Vendor 41, Spar
    // Convert a CSV file with orders 
    //Allocation	Created on	Article	Article Description	Site/DC	Site/DC Desc	Unit Qty	Unit Qty UoM	Outer Case/Case Unit	Vendor order	Warehouse Order
    //Principal,Date, Product Code, Product Description, Store Branch, Store, Unit Qty, Unit Qty UoM,Outer Case/Case Unit,Order Number	Warehouse Order
    
    function adaptorTOH_SPAR($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;

      $fileArr = explode("\n",$content);
      // check line total
      $hasheader =  $hasdetail = 'N';
       
 //       echo "<BR>";
 //       echo "<pre>";           
 //       print_r($fileArr);
 //       echo "</pre>";
 //       echo "<br>";
       
      $detailStart = 'F';
      
      // find location of fields - Ensure the the Store old account is in the Store Line 3 cols down from 'STORE'
      $storePos = $storeLookUp = $orderNo = $quantity = $ref = $prodcode = $aa = 99;
      
      foreach ($fileArr as $key=>$line2) {
      	  // convert line to CSV
          $lineArr2=str_getcsv($line2, ",", '"', "\\");
          $alen = count($lineArr2);
          $aloop = 0;
          foreach ($lineArr2 as $la2) {
               if(substr(strtoupper(trim($la2)),0,5) == 'STORE') {
                   $storePos    = $aloop;
                   $storeLookUp = $aloop + 3;	
               }
               if($orderNo == 99 && substr(strtoupper(trim($lineArr2[$aloop])),0,8) == 'ORDER NO' || $orderNo == 99 && substr(strtoupper(trim($lineArr2[$aloop])),0,12) == 'ORDER NUMBER') {
                   $orderNo = $aloop;
          	       $ref     = $aloop + 1;
               }
               if($prodcode == 99 && substr(strtoupper(trim($lineArr2[$aloop])),0,10) == 'STOCK CODE') {
                   $prodcode = $aloop;
               }               
               if($quantity == 99 && $prodcode <> 99 && substr(strtoupper(trim($lineArr2[$aloop])),0,5) == 'ORDER') {
                   $quantity = $aloop;
               }                   
               $aloop++ ;
          }
      }
      if($storePos == 99 || $storeLookUp == 99 || $orderNo == 99 || $quantity == 99 || $ref == 99 || $prodcode == 99) {
        	    $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "Fields cannot be identified ";
              
              echo  " St  " . $storePos;
              echo  "<br>";
              echo  "SLU  " . $storeLookUp;
              echo  "<br>";
              echo  "ON   " . $orderNo;
              echo  "<br>";
              echo  "REF  " . $ref;
              echo  "<br>";
              echo  "PC  "  . $prodcode;
              echo  "<br>";
              echo  "QTY  " . $quantity; 
              echo  "<br>";
              $eTO->identifier = ET_CUSTOMER;
              return $eTO;
      }   
      
              echo  " St  " . $storePos;
              echo  "<br>";
              echo  "SLU  " . $storeLookUp;
              echo  "<br>";
              echo  "ON   " . $orderNo;
              echo  "<br>";
              echo  "REF  " . $ref;
              echo  "<br>";
              echo  "PC  "  . $prodcode;
              echo  "<br>";
              echo  "QTY  " . $quantity; 
              echo  "<br>";
              
      $OrdersArr = explode("\n",$content);
      
      
      foreach ($OrdersArr as $line) {
          $lineArr2=str_getcsv($line, ",", '"', "\\");	
 //         echo $lineArr2[$storePos];
 //         echo $lineArr2[$storeLookUp];
          if(trim(substr($lineArr2[$storePos],0,5)) == 'STORE') {
            	if (isset($postingOrdersHoldingTO)) {
        	        $arrTO[] = $postingOrdersHoldingTO;
                  unset($postingOrdersHoldingTO);
              }
              /*******************
              *   ORDER HEAD
              *******************/
              $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
              $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
              $postingOrdersHoldingTO->principalUid = 74;
              $postingOrdersHoldingTO->updateProduct="N";
              $postingOrdersHoldingTO->insertProduct="N";
              $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
              $postingOrdersHoldingTO->dataSource = DS_EDI;
              $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
              $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
              $postingOrdersHoldingTO->requestedDeliveryDate = "";
              $postingOrdersHoldingTO->capturedBy = 'MANUAL';
              $postingOrdersHoldingTO->depotLookupRef = "";

              $postingOrdersHoldingTO->orderDate = date("Y-m-d");
          
              if ($postingOrdersHoldingTO->orderDate === false) {
                    $eTO->type = FLAG_ERRORTO_ERROR;
                    $eTO->description = "Order date invalid format or empty";
                    $eTO->identifier = ET_CUSTOMER;
                    return $eTO;
              }
              $postingOrdersHoldingTO->shipToName = str_replace("'",'',trim($lineArr2[$storePos]));
              $postingOrdersHoldingTO->deliverName = str_replace("'",'',trim($lineArr2[$storePos]));
              $postingOrdersHoldingTO->documentNo = '';
              $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
              $postingOrdersHoldingTO->storeLookupRef = trim($lineArr2[$storePos]); 
              $postingOrdersHoldingTO->enforceSameDepot = "N";
              $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
              $postingOrdersHoldingTO->oldAccount = trim($lineArr2[$storeLookUp]);
              $postingOrdersHoldingTO->reference = '';
          } elseif(substr(strtoupper(trim($lineArr2[$orderNo])),0,8) == 'ORDER NO' || substr(strtoupper(trim($lineArr2[$orderNo])),0,12) == 'ORDER NUMBER') {
                 $postingOrdersHoldingTO->reference = trim(str_replace("'",'',$lineArr2[$ref]));
          } elseif(substr(strtoupper(trim($lineArr2[$prodcode])),0,10) == 'STOCK CODE') {
                 $detailStart = 'T';                 
          } elseif(trim($lineArr2[$quantity]) > 0 &&  trim($lineArr2[$quantity]) <> 99 && $detailStart == 'T') {    	
                  /******************
                  *   ORDER DETAILS
                  *****************/
                  $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                  // should not be necessary to check document type as other types should end up as zeros
                  $postingOrdersHoldingDetailTO->listPrice     = '';
                  $postingOrdersHoldingDetailTO->quantity      =  trim($lineArr2[$quantity]);
                  $postingOrdersHoldingDetailTO->nettPrice     = '';
                  $postingOrdersHoldingDetailTO->extPrice      = '';
                  $postingOrdersHoldingDetailTO->vatRate       = "0015.00";
                  $postingOrdersHoldingDetailTO->vatAmount     = '';
                  $postingOrdersHoldingDetailTO->totalPrice    = '';
                  if(trim($lineArr2[$prodcode]) == 'RUBTB0000003') {
                         $postingOrdersHoldingDetailTO->productCode = 'RUBTB000003';
                  } elseif(trim($lineArr2[$prodcode]) == 'RUBTB0000004') {
                  	     $postingOrdersHoldingDetailTO->productCode = 'RUBTB000004';
                  } elseif(trim($lineArr2[$prodcode]) == 'SETB000001') {
                      	 $postingOrdersHoldingDetailTO->productCode = 'SETB0000001';
                  } elseif(trim($lineArr2[$prodcode]) == 'RUBTB0000013') {
                      	 $postingOrdersHoldingDetailTO->productCode = 'RUBTB000013';             	 
                  } elseif(trim($lineArr2[$prodcode]) == 'RUBTB0000012') {
                      	 $postingOrdersHoldingDetailTO->productCode = 'RUBTB000012';
                  } elseif(trim($lineArr2[$prodcode]) == 'RUBTB0000010') {
                      	 $postingOrdersHoldingDetailTO->productCode = 'RUBTB000010';
                  } elseif(trim($lineArr2[$prodcode]) == 'RUBTB0000011') {
                      	 $postingOrdersHoldingDetailTO->productCode = 'RUBTB000011';
                  } elseif(trim($lineArr2[$prodcode]) == 'DOYPC0000002') {
                      	 $postingOrdersHoldingDetailTO->productCode = 'DOYPC000002'; 
                  } elseif(trim($lineArr2[$prodcode]) == 'DOYPC0000001') {
                      	 $postingOrdersHoldingDetailTO->productCode = 'DOYPC000001';                       	 
                  } elseif(trim($lineArr2[$prodcode]) == 'DOYPC0000003') {
                      	 $postingOrdersHoldingDetailTO->productCode = 'DOYPC000003';                       	 
                  } elseif(trim($lineArr2[$prodcode]) == 'DOYPC0000004') {
                      	 $postingOrdersHoldingDetailTO->productCode = 'DOYPC000004'; 
                  } else {
                      $postingOrdersHoldingDetailTO->productCode   = trim($lineArr2[$prodcode]); 	
                  }
                  $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                  $postingOrdersHoldingDetailTO->pallets       = 0;
                  $postingOrdersHoldingDetailTO->productName   = trim($prodcode);
                  $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
                  
                  $hasdetail = 'Y';
          } 
      }
      
      $arrTO[] = $postingOrdersHoldingTO;
      unset($postingOrdersHoldingTO);
          
//          echo "<BR>";
//          echo "<pre>";     
//          
//          print_r($arrTO);
//          echo "</pre>"; 
//          echo "<BR>";
     
        
      if($hasdetail =='Y') {
          $eTO->type = FLAG_ERRORTO_SUCCESS;
          $eTO->description = "Successful";
          $eTO->object = $arrTO;
          return $eTO;
      } else {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid File or no Details";
          $eTO->identifier = ET_CUSTOMER;
          
          echo "<BR>";
          echo "<pre>";     
//          
          print_r($arrTO);
          echo "</pre>"; 
          echo "<BR>";
     
          
          
          return $eTO;
      }   
    }
    
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------       
   //Vendor 42, Skynamo
    // Convert a CSV file with orders 
    //Allocation	Created on	Article	Article Description	Site/DC	Site/DC Desc	Unit Qty	Unit Qty UoM	Outer Case/Case Unit	Vendor order	Warehouse Order
    //Principal,Date, Product Code, Product Description, Store Branch, Store, Unit Qty, Unit Qty UoM,Outer Case/Case Unit,Order Number	Warehouse Order
    
    function adaptorTOH_SYKNAMO($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;
      
      $headerarray=array('Comment');

      $fileArr = explode("\n",$content);
      
      $validfile = 0;
//      echo count($fileArr);
      
      if (in_array(trim(substr($fileArr[0],0,7)), $headerarray))   {
         // validate first line of file
         $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");
         for ($x = 0; $x <= 20; $x++) {
         	    if(trim($lineArr[$x])=='Customer code') {
         	       $Customer_code_offset = $x;
         	       $validfile++;
              } elseif(trim($lineArr[$x])=='ID') {
                 $id = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Comment') {
                 $Comment = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Date') {
                 $order_date = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Product code') {
                 $product_code = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Principle') {
                 $PrincipalId = $x;
                 $validfile++;                  
              } elseif(trim($lineArr[$x])=='Quantity') {
                 $quantity = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Discount') {
                 $discount = $x;
                 $validfile++;                
              } elseif(trim($lineArr[$x])=='Reference') {
                 $reference = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='User') {
                 $skyuser = $x;
                 $validfile++;
              }   
//            echo $lineArr[$x];
//            echo "<br>";
         }
//       echo "<br>";
//       echo $validfile;
//       echo "<br>";
         if($validfile > 9 && $validfile < 10 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Check file - Order fields missing!";
            return $eTO;
         }          
         if(count($lineArr) <= 17 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Invalid Column Count - expecting 19 columns Found " . count($lineArr);
            return $eTO;
         }          
         unset($fileArr[0]);
      } else {
         $eTO->type = FLAG_ERRORTO_ERROR;
         $eTO->description = "First line expected to be a header!";
         return $eTO;
      }
      
      $hasheader = 'N';
      $hasdetail = 'N';
      
      foreach ($fileArr as $key=>$line) {
      	
           // convert line to CSV
           $lineArr=str_getcsv($line, ",", '"', "\\");
           
//           print_r($lineArr);

           if (  $hasheader == 'N') {
               // Determine Principal code from first line of order      	
               if(strtoupper($lineArr[$PrincipalId]) == 'UBER') {
                   $currPrincipal = 317;	
               } elseif (strtoupper($lineArr[$PrincipalId]) == 'CLEAR WORLD') {
                   $currPrincipal = 216;	
               } elseif (trim(strtoupper($lineArr[$PrincipalId])) == '') {
                   $currPrincipal = 216;	
               } elseif (strtoupper($lineArr[$PrincipalId]) == 'NOVA CHOCOLATE') {
                   $currPrincipal = 315;	
               } else {
                   $currPrincipal = 216;	
               }
               /*******************
               *   ORDER HEAD
               *******************/
               $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
               $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
               $postingOrdersHoldingTO->principalUid = $currPrincipal;
               $postingOrdersHoldingTO->updateProduct="N";
               $postingOrdersHoldingTO->insertProduct="N";
               $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
               $postingOrdersHoldingTO->dataSource = DS_EDI;
               $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
               $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
               $postingOrdersHoldingTO->requestedDeliveryDate = "";
               $postingOrdersHoldingTO->capturedBy = substr(trim($lineArr[$skyuser]),0,10);
               $postingOrdersHoldingTO->deliveryInstructions = trim($lineArr[$Comment]);
               $postingOrdersHoldingTO->depotLookupRef = "";
               $postingOrdersHoldingTO->offInvoiceDiscount = 0;
               $postingOrdersHoldingTO->offInvoiceDiscountType = '';  

               $postingOrdersHoldingTO->orderDate =  $lineArr[$order_date];
              
               if ($postingOrdersHoldingTO->orderDate === false) {
                        $eTO->type = FLAG_ERRORTO_ERROR;
                        $eTO->description = "Order date invalid format or empty";
                        $eTO->identifier = ET_CUSTOMER;
                        return $eTO;
               }
               $postingOrdersHoldingTO->shipToName = '';
               $postingOrdersHoldingTO->deliverName = '';
               $postingOrdersHoldingTO->clientDocumentNo = trim($lineArr[$id]);
               $postingOrdersHoldingTO->documentNo = '';
               if($lineArr[$discount]=='100') {
                   $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV_ZERO_PRICE;
               } else {
                   $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV; 	
               }
               $postingOrdersHoldingTO->storeLookupRef = $lineArr[$Customer_code_offset];
               $postingOrdersHoldingTO->enforceSameDepot = "N";
               $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
               $postingOrdersHoldingTO->oldAccount = trim($lineArr[$Customer_code_offset]);
               $postingOrdersHoldingTO->reference = '';
               $postingOrdersHoldingTO->reference = trim($lineArr[$reference]);
               
               $hasheader = 'Y';
               
                $arrTO[] = $postingOrdersHoldingTO;
                        }       
           /******************
           *   ORDER DETAILS
           *****************/
          
           $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

           $postingOrdersHoldingDetailTO->listPrice     = '';
           $postingOrdersHoldingDetailTO->quantity      = $lineArr[$quantity];
           $postingOrdersHoldingDetailTO->nettPrice     = '';
           $postingOrdersHoldingDetailTO->extPrice      = '';
           $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
           $postingOrdersHoldingDetailTO->vatAmount     = '';
           $postingOrdersHoldingDetailTO->totalPrice    = '';
           if($currPrincipal == 317 && $lineArr[$product_code] == '2301') {
                 $postingOrdersHoldingDetailTO->productCode    = '2501' ;          	
           } elseif($currPrincipal == 317 && $lineArr[$product_code] == '2302') {
                 $postingOrdersHoldingDetailTO->productCode   = '2502';           	
           } elseif($currPrincipal == 317 && $lineArr[$product_code] == '2303') {
                 $postingOrdersHoldingDetailTO->productCode   = '2503';           	
           } elseif($currPrincipal == 317 && $lineArr[$product_code] == '2304') {
                 $postingOrdersHoldingDetailTO->productCode   = '2504';           	
           } else {
           	     $postingOrdersHoldingDetailTO->productCode   = $lineArr[$product_code];
           }
           $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
           $postingOrdersHoldingDetailTO->pallets       = 0;
           $postingOrdersHoldingDetailTO->productName   = '';
           $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
           $hasdetail = 'Y';
      }
      if($hasdetail =='Y') {
           $eTO->type = FLAG_ERRORTO_SUCCESS;
           $eTO->description = "Successful";
           $eTO->object = $arrTO;
           return $eTO;
      } else {
     	    $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid File or no Details";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
      }       
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------       
// --------------------------------------------------------------------------------------------------------------------------------     

    function adaptorTOH_NATDISCHEM($content, $onlineFileProcessItem) {

         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
         global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
   
         $eTO = new ErrorTO;
         if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
         $arrTO=array();
         $processingLine=0;
         
         $headerarray=array('Comment');
   
         $fileArr = explode("\n",$content);
         unset($fileArr[0]);
         unset($fileArr[2]);
               
         $productCodeArr=str_getcsv($fileArr[1], ",", '"', "\\");
         
//         print_r($productCodeArr);
         
//         echo "<br>";
   
         unset($fileArr[3]);
         unset($fileArr[4]);  
         
         foreach ($fileArr as $key=>$line) {
         	
             $lineArr=str_getcsv($line, ",", '"', "\\");
//             echo "<br>";
//         	  print_r($lineArr);
 //            echo "<br>";
 //            echo "<br>";
             
             if(substr(trim($lineArr[0]),0,1) == 'S') {
             	   for ($x = 0; $x <= 65; $x++) {
             	   	   if ($x == 0) {
             	   	         /*******************
                            *   ORDER HEAD
                            *******************/
                            $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                            $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                            $postingOrdersHoldingTO->principalUid = 293;
                            $postingOrdersHoldingTO->updateProduct="N";
                            $postingOrdersHoldingTO->insertProduct="N";
                            $postingOrdersHoldingTO->vendorUid = 43;
                            $postingOrdersHoldingTO->dataSource = DS_EDI;
                            $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                            $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                            $postingOrdersHoldingTO->requestedDeliveryDate = "";
                            $postingOrdersHoldingTO->capturedBy = '11';
                            $postingOrdersHoldingTO->deliveryInstructions = '';
                            $postingOrdersHoldingTO->depotLookupRef = "";

                            $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                            $postingOrdersHoldingTO->offInvoiceDiscountType = '';  
                            $postingOrdersHoldingTO->orderDate = date("Y-m-d");
                 
                            if ($postingOrdersHoldingTO->orderDate === false) {
                                $eTO->type = FLAG_ERRORTO_ERROR;
                                $eTO->description = "Order date invalid format or empty";
                                $eTO->identifier = ET_CUSTOMER;
                                return $eTO;
                            }
                            $postingOrdersHoldingTO->shipToName = '';
                            $postingOrdersHoldingTO->deliverName = '';
                            $postingOrdersHoldingTO->clientDocumentNo = '';
                            $postingOrdersHoldingTO->documentNo = '';
                            $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                            $postingOrdersHoldingTO->storeLookupRef = '';
                            $postingOrdersHoldingTO->enforceSameDepot = "N";
                            $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                            $postingOrdersHoldingTO->oldAccount = trim($lineArr[0]);
                            $postingOrdersHoldingTO->reference  = trim($lineArr[3]);
                  
                            $arrTO[] = $postingOrdersHoldingTO;
                     }      
                        
             	   	   if ($x >= 4) {
             	   	        if (array_key_exists($x,$lineArr)) {
             	   	             /******************
                               *   ORDER DETAILS
                               *****************/
                               
                                 if($lineArr[$x] > 0 && $lineArr[$x] <> '') {
             
                                     $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
   
                                     $postingOrdersHoldingDetailTO->listPrice     = '';
                                     $postingOrdersHoldingDetailTO->quantity      = $lineArr[$x];
                                     $postingOrdersHoldingDetailTO->nettPrice     = '';
                                     $postingOrdersHoldingDetailTO->extPrice      = '';
                                     $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                                     $postingOrdersHoldingDetailTO->vatAmount     = '';
                                     $postingOrdersHoldingDetailTO->totalPrice    = '';
                                     
                                     echo trim($productCodeArr[$x]);
                                     
                                     $postingOrdersHoldingDetailTO->productCode    = str_replace(" ",'', trim($productCodeArr[$x]));        	
                                     $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                                     $postingOrdersHoldingDetailTO->pallets       = 0;
                                     $postingOrdersHoldingDetailTO->productName   = '';
                                     $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
                                 }
                          }
                     }
             	   }
             }	   
         }
         $eTO->type = FLAG_ERRORTO_SUCCESS;
         $eTO->description = "Successful";
         $eTO->object = $arrTO;
         return $eTO;
    }

// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------     

    function adaptorTOH_HTBUILDER($content, $onlineFileProcessItem) {

         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
         global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
   
         $eTO = new ErrorTO;
   
         $fileArr = explode("\n",$content);
         
///      print_r($fileArr);
         
         unset($fileArr[0]);
         
         $header    = "N";
         
         $hasdetail = 'N';
       
         foreach ($fileArr as $key=>$line) {
         	
         	    $lineArr=str_getcsv($line, ",", '"', "\\");
                   
        	    if($header == "N") {  
        	    	
        	         echo basename($onlineFileProcessItem["file_being_processed"]);                 
                   
                   /*******************
                    *   ORDER HEAD
                   *******************/
                   $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                   $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                   $postingOrdersHoldingTO->principalUid = 328;
                   $postingOrdersHoldingTO->updateProduct="N";
                   $postingOrdersHoldingTO->insertProduct="N";
                   $postingOrdersHoldingTO->vendorUid = 43;
                   $postingOrdersHoldingTO->dataSource = BW_CSV;
                   $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                   $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                   $postingOrdersHoldingTO->requestedDeliveryDate = "";
                   $postingOrdersHoldingTO->capturedBy = '11';
                   $postingOrdersHoldingTO->deliveryInstructions = '';
                   $postingOrdersHoldingTO->depotLookupRef = "";

                   $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                   $postingOrdersHoldingTO->offInvoiceDiscountType = '';  

                   $postingOrdersHoldingTO->orderDate = date("Y-m-d");
                      
                   if ($postingOrdersHoldingTO->orderDate === false) {
                         $eTO->type = FLAG_ERRORTO_ERROR;
                         $eTO->description = "Order date invalid format or empty";
                         $eTO->identifier = ET_CUSTOMER;
                         return $eTO;
                   }
                   $postingOrdersHoldingTO->shipToName =  trim($lineArr[5]);
                   $postingOrdersHoldingTO->deliverName = trim($lineArr[5]);
                   $postingOrdersHoldingTO->clientDocumentNo = '';
                   $postingOrdersHoldingTO->documentNo = '';
                   $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                   $postingOrdersHoldingTO->storeLookupRef = '';
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                   $postingOrdersHoldingTO->oldAccount = trim($lineArr[4]);
                   $postingOrdersHoldingTO->reference  = trim($lineArr[0]);  
                   
                   $header = "Y"; 
                       
                   $arrTO[] = $postingOrdersHoldingTO;
              } 
              
              /******************
              *   ORDER DETAILS
              *****************/
              
              if(trim($lineArr[9]) <> '') {
              	
                   $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
              
                   $postingOrdersHoldingDetailTO->listPrice     = $lineArr[15];
                   $postingOrdersHoldingDetailTO->quantity      = $lineArr[10];
                   $postingOrdersHoldingDetailTO->nettPrice     = $lineArr[15];
                   $postingOrdersHoldingDetailTO->extPrice      = $lineArr[17];
                   $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                   $postingOrdersHoldingDetailTO->vatAmount     = $lineArr[17] * VAL_VAT_RATE_TBLSTD /100 ;
                   $postingOrdersHoldingDetailTO->totalPrice    = $lineArr[17] + $lineArr[17] * VAL_VAT_RATE_TBLSTD /100;
                                          
                   $postingOrdersHoldingDetailTO->productCode    = $lineArr[9];        	
                   $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                   $postingOrdersHoldingDetailTO->pallets       = 0;
                   $postingOrdersHoldingDetailTO->productName   = $lineArr[7]; 
                   
                   $hasdetail = 'Y';
                   
                   $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;              	
              }
         }
   
         if($hasdetail =='Y') {
             $eTO->type = FLAG_ERRORTO_SUCCESS;
             $eTO->description = "Successful";
             $eTO->object = $arrTO;
             return $eTO;
         } else {
         	  unset($postingOrdersHoldingTO);
       	    $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "File Ignored Details";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
         }           
    }

// *********************************************************************************************************************************************
// *********************************************************************************************************************************************
// ----------------------------------------------------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------------------------------------           

    //Vendor 41,DIS
    // Convert a CSV file with orders 
    //Allocation	Created on	Article	Article Description	Site/DC	Site/DC Desc	Unit Qty	Unit Qty UoM	Outer Case/Case Unit	Vendor order	Warehouse Order
    //Principal,Date, Product Code, Product Description, Store Branch, Store, Unit Qty, Unit Qty UoM,Outer Case/Case Unit,Order Number	Warehouse Order
    
    function adaptorTOH_DIS($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      
      $fileArr = explode("\n",$content);
      
       foreach ($fileArr as $key=>$line) {
     	
           $lineArr=str_getcsv($line, ",", '"', "\\");

            if($key == 0) {
                 /*******************
                  *   ORDER HEAD
                 *******************/
                 $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                 $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                 $postingOrdersHoldingTO->principalUid = 293;
                 $postingOrdersHoldingTO->updateProduct="N";
                 $postingOrdersHoldingTO->insertProduct="N";
                 $postingOrdersHoldingTO->vendorUid = 43;
                 $postingOrdersHoldingTO->dataSource = DS_EDI;
                 $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                 $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                 $postingOrdersHoldingTO->requestedDeliveryDate = "";
                 $postingOrdersHoldingTO->capturedBy = '11';
                 $postingOrdersHoldingTO->deliveryInstructions = '';
                 $postingOrdersHoldingTO->depotLookupRef = "";
                 
                 $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                 $postingOrdersHoldingTO->offInvoiceDiscountType = '';  
                 
                 $postingOrdersHoldingTO->orderDate = date("Y-m-d");
                      
                  if ($postingOrdersHoldingTO->orderDate === false) {
                        $eTO->type = FLAG_ERRORTO_ERROR;
                        $eTO->description = "Order date invalid format or empty";
                        $eTO->identifier = ET_CUSTOMER;
                        return $eTO;
                  }
                  $postingOrdersHoldingTO->shipToName =  '';
                  $postingOrdersHoldingTO->deliverName = '';
                  $postingOrdersHoldingTO->clientDocumentNo = '';
                  $postingOrdersHoldingTO->documentNo = '';
                  $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                  $postingOrdersHoldingTO->storeLookupRef = '';
                  $postingOrdersHoldingTO->enforceSameDepot = "N";
                  $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                  $postingOrdersHoldingTO->oldAccount = trim($lineArr[3]);
                  
            }      
            if($key == 2) {                  
                  $postingOrdersHoldingTO->reference  = trim($lineArr[4]);  
                  
                  $arrTO[] = $postingOrdersHoldingTO;
            } 
            	
            if($key >= 4 && trim($lineArr[0]) <>'') {  
                  /******************
                   *   ORDER DETAILS
                   *****************/
              	
                   $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
              
                   $postingOrdersHoldingDetailTO->listPrice     = $lineArr[5];
                   $postingOrdersHoldingDetailTO->quantity      = $lineArr[4];
                   $postingOrdersHoldingDetailTO->nettPrice     = $lineArr[5];
                   $postingOrdersHoldingDetailTO->extPrice      = $lineArr[4] * $lineArr[5];
                   $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                   $postingOrdersHoldingDetailTO->vatAmount     = $lineArr[4] * $lineArr[5] * VAL_VAT_RATE_TBLSTD /100 ;
                   $postingOrdersHoldingDetailTO->totalPrice    = $lineArr[4] * $lineArr[5] * VAL_VAT_RATE_TBLSTD /100;
                                          
                   $postingOrdersHoldingDetailTO->productCode    = $lineArr[0];        	
                   $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                   $postingOrdersHoldingDetailTO->pallets       = 0;
                   $postingOrdersHoldingDetailTO->productName   = ''; 
                   
                   $hasdetail = 'Y';
                   
                   $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;              	
            }     	
            	
       }
       
           echo "<BR>";      	
           print_r($postingOrdersHoldingTO);
           echo "LL";
           echo "<BR>";           
       
             
        $eTO->type = FLAG_ERRORTO_SUCCESS;
        $eTO->description = "Successful";
        $eTO->object = $arrTO;
        return $eTO; 
        
    }  
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------     

    function adaptorTOH_HTMAKRO($content, $onlineFileProcessItem) {

         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
         global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
   
         $eTO = new ErrorTO;
   
         $fileArr = explode("\n",$content);
         
///      print_r($fileArr);
         
         unset($fileArr[0]);
 
         $newStore  = '';
       
         foreach ($fileArr as $key=>$line) {
             $lineArr=str_getcsv($line, ",", '"', "\\");
         	   if  ($newStore <> trim($lineArr[4])) {
   	             echo basename($onlineFileProcessItem["file_being_processed"]);                 
                 
                 /*******************
                  *   ORDER HEAD
                 *******************/
                 $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                 $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                 $postingOrdersHoldingTO->principalUid = 328;
                 $postingOrdersHoldingTO->updateProduct="N";
                 $postingOrdersHoldingTO->insertProduct="N";
                 $postingOrdersHoldingTO->vendorUid = 43;
                 $postingOrdersHoldingTO->dataSource = DS_EDI;
                 $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                 $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                 $postingOrdersHoldingTO->requestedDeliveryDate = "";
                 $postingOrdersHoldingTO->capturedBy = '11';
                 $postingOrdersHoldingTO->deliveryInstructions = '';
                 $postingOrdersHoldingTO->depotLookupRef = "";

                 $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                  $postingOrdersHoldingTO->offInvoiceDiscountType = '';  
                 $postingOrdersHoldingTO->orderDate = date("Y-m-d");
                    
                 if ($postingOrdersHoldingTO->orderDate === false) {
                       $eTO->type = FLAG_ERRORTO_ERROR;
                       $eTO->description = "Order date invalid format or empty";
                       $eTO->identifier = ET_CUSTOMER;
                       return $eTO;
                 }
                 $postingOrdersHoldingTO->shipToName =  trim($lineArr[5]);
                 $postingOrdersHoldingTO->deliverName = trim($lineArr[5]);
                 $postingOrdersHoldingTO->clientDocumentNo = '';
                 $postingOrdersHoldingTO->documentNo = '';
                 $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                 $postingOrdersHoldingTO->storeLookupRef = '';
                 $postingOrdersHoldingTO->enforceSameDepot = "N";
                 $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                 $postingOrdersHoldingTO->oldAccount = trim($lineArr[4]);
                 $postingOrdersHoldingTO->reference  = trim($lineArr[0]);  
                                  
                 $newStore = trim($lineArr[4]) ;
                 
                 $hasdetail = 'N';                     
                 $arrTO[] = $postingOrdersHoldingTO;
             }    

             /******************
             *   ORDER DETAILS
              *****************/
              
              if(trim($lineArr[9]) <> '') {
              	
                   $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
              
                   $postingOrdersHoldingDetailTO->listPrice     = $lineArr[15];
                   $postingOrdersHoldingDetailTO->quantity      = $lineArr[10];
                   $postingOrdersHoldingDetailTO->nettPrice     = $lineArr[15];
                   $postingOrdersHoldingDetailTO->extPrice      = $lineArr[17];
                   $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                   $postingOrdersHoldingDetailTO->vatAmount     = $lineArr[17] * VAL_VAT_RATE_TBLSTD /100 ;
                   $postingOrdersHoldingDetailTO->totalPrice    = $lineArr[17] + $lineArr[17] * VAL_VAT_RATE_TBLSTD /100;
                                          
                   $postingOrdersHoldingDetailTO->productCode    = $lineArr[9];        	
                   $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                   $postingOrdersHoldingDetailTO->pallets       = 0;
                   $postingOrdersHoldingDetailTO->productName   = $lineArr[7]; 
                   
                   $hasdetail = 'Y';
                   
                   $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;              	
              } 
         }
   
         if($hasdetail =='Y') {
             $eTO->type = FLAG_ERRORTO_SUCCESS;
             $eTO->description = "Successful";
             $eTO->object = $arrTO;
             return $eTO;
         } else {
         	  unset($postingOrdersHoldingTO);
       	    $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "File Ignored Details";
            $eTO->identifier = ET_CUSTOMER;
            return $eTO;
         }           
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------       
    function adaptorTOH_OUTERJOIN($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;
      
      $headerarray=array('FormID');

      $fileArr = explode("\n",$content);
      
      $validfile = 0;
      
      // FormID,PONumber,StoreCode,Order Date,SKUCode,OrderQTY,VendorName,Rep Email,Store Name
      
      if (in_array(trim(substr($fileArr[0],0,6)), $headerarray))   {
         // validate first line of file
         $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");
         for ($x = 0; $x <= 9; $x++) {
         	    if(trim($lineArr[$x])=='StoreCode') {
         	       $Customer_code_offset = $x;
         	       $validfile++;
              } elseif(trim($lineArr[$x])=='FormID') {
                 $id = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Order Date') {
                 $order_date = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='SKUCode') {
                 $product_code = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='VendorName') {
                 $PrincipalId = $x;
                 $validfile++;                  
              } elseif(trim($lineArr[$x])=='OrderQTY') {
                 $quantity = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='PONumber') {
                 $reference = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Rep Email') {
                 $skyuser = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Store Name') {
                 $StoreName = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='OrderType') {
                 $OrderType = $x;
                 $validfile++;
              }   
              echo $lineArr[$x];
              echo "<br>";
         }
         echo "<br>";
         echo $validfile;
         echo "<br>";
         if($validfile <> 10 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Check file - Order fields missing!";
            return $eTO;
         }          
         if(count($lineArr) <= 8 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Invalid Column Count - expecting 19 columns Found " . count($lineArr);
            return $eTO;
         }          
         unset($fileArr[0]);
      } else {
         $eTO->type = FLAG_ERRORTO_ERROR;
         $eTO->description = "First line expected to be a header!";
         return $eTO;
      }
      
      $hasheader = 'N';
      $hasdetail = 'N';
      $newStore  = '';
      
      foreach ($fileArr as $key=>$line) {
      	
           // convert line to CSV
           $lineArr=str_getcsv($line, ",", '"', "\\");
           
           print_r($lineArr);
           
           if ($newStore <> trim($lineArr[$id]) . trim($lineArr[$FormID])) {

              // Determine Principal code from first line of order    
                  
               echo   $lineArr[$PrincipalId];	
               if(strtoupper($lineArr[$PrincipalId]) == 'NOVA CHOCOLATE') {
                   $currPrincipal = 326;	
               } elseif (strtoupper($lineArr[$PrincipalId]) == 'CARTOON CANDY') {
                   $currPrincipal = 337;	
               } elseif (strtoupper($lineArr[$PrincipalId]) == 'ONE JUICE') {
                   $currPrincipal = 338;	
               } elseif (strtoupper($lineArr[$PrincipalId]) == 'CLEARWORLD') {
                   $currPrincipal = 216;	
               } elseif (strtoupper($lineArr[$PrincipalId]) == 'ADAMS INTERNATIONAL') {
                   $currPrincipal = 339;	                   
               } elseif (strtoupper($lineArr[$PrincipalId]) == 'UBER') {
                   $currPrincipal = 317;	                   
               } elseif (strtoupper($lineArr[$PrincipalId]) == 'PEELS') {
                   $currPrincipal = 315;
               } elseif (strtoupper($lineArr[$PrincipalId]) == 'KLEIN RIVER CHEESE') {
                   $currPrincipal = 348;
               } elseif (strtoupper($lineArr[$PrincipalId]) == 'LIFE TREK') {
                   $currPrincipal = 352;
               } elseif (strtoupper($lineArr[$PrincipalId]) == "GLENS CHICKPEAS") {
                   $currPrincipal = 367;
               } elseif (strtoupper($lineArr[$PrincipalId]) == "DESERV") {
                   $currPrincipal = 382;
               } elseif (strtoupper($lineArr[$PrincipalId]) == "SKYRULE DRINKS") {
                   $currPrincipal = 384;
               } elseif (strtoupper($lineArr[$PrincipalId]) == "HONEY FIELDS") {
                   $currPrincipal = 305;
               } else {
                   $eTO->type = FLAG_ERRORTO_ERROR;
                   $eTO->description = "Cannnot Determine the Principal!";
                  return $eTO;
               }
 
          	   $specialField = array(216 => "379",
                                     317 => "380",
                                     326 => "378",
                                     337 => "381",
                                     338 => "382",
                                     315 => "408",
                                     339 => "383",
                                     348 => "411",
                                     352 => "422",
                                     367 => "447",
                                     382 => "488",
                                     305 => "501",
                                     384 => "493");
                                     
               $spField = isset($specialField[$currPrincipal]) ? $specialField[$currPrincipal] : "  ";     
               
               $chainSpecFld = array(337 => "386",
                                     338 => "398",
                                     339 => "399",
                                     315 => "406",
                                     348 => "409",
                                     352 => "420",
                                     367 => "445",
                                     382 => "490",
                                     384 => "491");
                                     
               $chainSpField = isset($chainSpecFld[$currPrincipal]) ? $chainSpecFld[$currPrincipal] : "  ";   
               
               $DepotSpField = array(337 => "388",
                                     338 => "396",
                                     339 => "397",
                                     315 => "407",
                                     348 => "410",
                                     352 => "421",
                                     367 => "446",
                                     382 => "489",
                                     384 => "492");
                                     
               $depSpField = isset($DepotSpField[$currPrincipal]) ? $DepotSpField[$currPrincipal] : "  ";
         
               /*******************
               *   ORDER HEAD
               *******************/
               $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
               $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
               $postingOrdersHoldingTO->principalUid = $currPrincipal;
               $postingOrdersHoldingTO->updateProduct="N";
               $postingOrdersHoldingTO->insertProduct="N";
               $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
               $postingOrdersHoldingTO->dataSource = DS_EDI;
               $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
               $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
               $postingOrdersHoldingTO->requestedDeliveryDate = "";
               $postingOrdersHoldingTO->capturedBy = "Iram";
               $postingOrdersHoldingTO->deliveryInstructions = '';
               $postingOrdersHoldingTO->additionalDetails = substr(trim($lineArr[$skyuser]),0,10);
               $postingOrdersHoldingTO->offInvoiceDiscount = 0;
               $postingOrdersHoldingTO->offInvoiceDiscountType = '';
               $postingOrdersHoldingTO->depotLookupRef = "IRM";
          
               $postingOrdersHoldingTO->orderDate =  $lineArr[$order_date];
               
               if ($postingOrdersHoldingTO->orderDate === false) {
                        $eTO->type = FLAG_ERRORTO_ERROR;
                        $eTO->description = "Order date invalid format or empty";
                        $eTO->identifier = ET_CUSTOMER;
                        return $eTO;
               }
               $postingOrdersHoldingTO->shipToName = trim($lineArr[$StoreName]);
               $postingOrdersHoldingTO->deliverName = trim($lineArr[$StoreName]);
               $postingOrdersHoldingTO->clientDocumentNo = trim($lineArr[$id]);
               $postingOrdersHoldingTO->documentNo = '';
               if(trim($lineArr[$OrderType]=='F')) {
                   $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV_ZERO_PRICE;
               } else {
               	   $delNoteArray = array(348,xxx) ;
               	   if(in_array($currPrincipal,$delNoteArray)) {
               	      $postingOrdersHoldingTO->documentTypeUId = DT_DELIVERYNOTE;   	
               	   } else {
               	      $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV; 	
               	   }
               }
               $postingOrdersHoldingTO->storeLookupRef = $lineArr[$Customer_code_offset];  // special field UID comes from array above and not OLFPM
               $postingOrdersHoldingTO->enforceSameDepot = "N";
               $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
               $postingOrdersHoldingTO->oldAccount = '';
               $postingOrdersHoldingTO->salesAgentStoreIdentifier = "SP" . $spField . "CH" . $chainSpField . "WH" . $depSpField;
               $postingOrdersHoldingTO->reference = substr(trim($lineArr[$reference]),0,19);
               
               $newStore = trim($lineArr[$id]) . trim($lineArr[$FormID]);
              
               $arrTO[] = $postingOrdersHoldingTO;
               
               // If only mailing the orders just create the store
               
               $prinCreateStore = array(337,338,339,315,348, 352, 367, 382, 384);
               
               if(in_array($currPrincipal,$prinCreateStore)) {
               
                   // Create the StoreTO
                   $postingStoreTO = new PostingStoreTO;
                   $postingStoreTO->DMLType = "INSERT";
                   $postingStoreTO->principalStoreUId = "";
                   $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                   $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[$StoreName]));
                   $postingStoreTO->deliverAdd1 = '';
                   $postingStoreTO->deliverAdd2 = '';
                   $postingStoreTO->deliverAdd3 = '';
                   $postingStoreTO->billName = $postingStoreTO->deliverName;
                   $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
                   $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
                   $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
                   $postingStoreTO->vatNumber = ''; 
                   $postingStoreTO->telNo1    = ''; 
                   $postingStoreTO->depot = ""; // this will be set by the processing script
                   $postingStoreTO->deliveryDay = "8";
                   $postingStoreTO->noVAT = 0;
                   $postingStoreTO->onHold = "0";
                   $postingStoreTO->chain = ""; // this will be set by the processing script
                   $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                   $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                   $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->oldAccount = $lineArr[$Customer_code_offset];

                   $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;

                   // lookup special field(s) - enforce this specific one
                   $postingSpecialFieldTO = new PostingSpecialFieldTO;
                   $postingSpecialFieldTO->DMLType = "INSERT";
                   $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                   $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                   $postingSpecialFieldTO->fielduid = $spField; // Special Field
                   $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                   $postingSpecialFieldTO->value = $lineArr[$Customer_code_offset];
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;

                   // End Create the StoreTO
               }
           }       
           /******************
           *   ORDER DETAILS
           *****************/
          
           $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

           $postingOrdersHoldingDetailTO->listPrice     = '';
           $postingOrdersHoldingDetailTO->quantity      = $lineArr[$quantity];
           $postingOrdersHoldingDetailTO->nettPrice     = '';
           $postingOrdersHoldingDetailTO->extPrice      = '';
           $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
           $postingOrdersHoldingDetailTO->vatAmount     = '';
           $postingOrdersHoldingDetailTO->totalPrice    = '';
           $postingOrdersHoldingDetailTO->productCode   = $lineArr[$product_code];
           $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
           $postingOrdersHoldingDetailTO->pallets       = 0;
           $postingOrdersHoldingDetailTO->productName   = '';
           $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
           $hasdetail = 'Y';

      }
      if($hasdetail =='Y') {
           $eTO->type = FLAG_ERRORTO_SUCCESS;
           $eTO->description = "Successful";
           $eTO->object = $arrTO;
           return $eTO;
      } else {
     	    $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid File or no Details";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
      }       
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------       
    function adaptorTOH_IRAMUPLIFTS($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;
      
      $headerarray=array('SITE');

      $fileArr = explode("\n",$content);
      
      $validfile = 0;
            
      // Site,Channel,Region,Part Number,Barcode,Category,Vendor Number,Vendor Name,Site Name,Article,Material Description,Cost,Aged Stock,Stock Found,Display Stock,Damaged Stock,Stock Uplifted,SITE_FILTER
      
      if (in_array(strtoupper(trim(substr($fileArr[0],0,4))), $headerarray) || in_array(strtoupper(trim(substr($fileArr[0],3,4))), $headerarray) )   {

         // validate first line of file
         $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");
         for ($x = 0; $x <= 20; $x++) {
         	
         	//echo $x;
         	
//         	preg_replace('/[\xEF\xBB\xBF]/', '', $lineArr[$x]);
         	
         	    if(preg_replace('/[\xEF\xBB\xBF]/', '', strtoupper($lineArr[$x]))=='SITE') {
         	       $Site = $x;
         	       $validfile++;
              } elseif(trim(strtoupper($lineArr[$x]))=='CHANNEL') {
                 $Channel = $x;
                 $validfile++;
              } elseif(trim(strtoupper($lineArr[$x]))=='REGION') {
                 $Region = $x;
                 $validfile++;
              } elseif(trim(strtoupper($lineArr[$x]))=='PART NUMBER') {
                 $Part_Number = $x;
                 $validfile++;
              } elseif(trim(strtoupper($lineArr[$x]))=='BARCODE') {
                 $Barcode = $x;
                 $validfile++;                  
              } elseif(trim(strtoupper($lineArr[$x]))=='VENDOR NUMBER') {
                 $Vendor_Number = $x;
                 $validfile++;              
              } elseif(trim(strtoupper($lineArr[$x]))=='VENDOR NAME') {
                 $Vendor_Name = $x;
                 $validfile++;
              } elseif(trim(strtoupper($lineArr[$x]))=='SITE NAME') {
                 $Site_Name = $x;
                 $validfile++;
              } elseif(trim(strtoupper($lineArr[$x]))=='ARTICLE') {
                 $Article = $x;
                 $validfile++;
              } elseif(trim(strtoupper($lineArr[$x]))=='MATERIAL DESCRIPTION') {
                 $Material_Description = $x;
                 $validfile++;
              } elseif(trim(strtoupper($lineArr[$x]))=='COST') {
                 $Cost = $x;
                 $validfile++;
              } elseif(trim(strtoupper($lineArr[$x]))=='AGED STOCK') {
                 $Aged_Stock = $x;
                 $validfile++;
               } elseif(trim(strtoupper($lineArr[$x]))=='UPLIFTMENT VALUE') {
                 $value = $x;
                 $validfile++;                                  
              }   
              //echo $lineArr[$x];
              //echo "<br>";
         }
         
         //echo "<br>";
      	 //echo $Channel . '   ' . trim($lineArr[0]) . "    Site";
      	
      	
         //echo "<br>";
         //echo trim($lineArr[1]) . "    Site";
         //echo "<br>";
         
         echo "<br>";
         echo $validfile;
         echo "<br>";
         if($validfile < 10 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Check file - Order fields missing!";
            return $eTO;
         }          
         if(count($lineArr) < 11 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Invalid Column Count - expecting 19 columns Found " . count($lineArr);
            return $eTO;
         }          
         unset($fileArr[0]);
      } else {
         $eTO->type = FLAG_ERRORTO_ERROR;
         $eTO->description = "First line expected to be a header!";
         return $eTO;
      }
      
      $hasheader = 'N';
      $hasdetail = 'N';
      $newStore  = '';


      foreach ($fileArr as $key=>$line) {
      	
           // convert line to CSV
           $lineArr=str_getcsv($line, ",", '"', "\\");
           // echo "<pre>";
           // print_r($lineArr);
           // echo "<br>";
           
           // echo $Site;
           // echo "<br>";
           // echo trim($lineArr[$Site]);
           // echo "<br>";
           // echo $Vendor_Name . '  -  trim($lineArr[$Site])';
           // echo "<br>";
           
           if(trim($lineArr[$Barcode]) == '' || trim($lineArr[$Barcode]) == '0' || trim($lineArr[$Barcode]) == 'B') {
      	   	   continue;      	   	
      	   }
           
           
           if ($newStore <> trim($lineArr[$Site]) . trim($lineArr[$Vendor_Number])) {
               // Determine Principal code from first line of order    

               if(strtoupper($lineArr[$Vendor_Name]) == 'MAJOR TECH') {
                   $currPrincipal = '376';	                    
               } elseif (strtoupper(substr($lineArr[$Vendor_Name],0,6)) == 'RVL TEST') {
                   $currPrincipal = '336';	             // PROTON PREPACK (PTY) LTD
               } elseif (strtoupper(substr($lineArr[$Vendor_Name],0,5)) == 'BOSCH') {
                   $currPrincipal = '346';	
               } elseif (strtoupper($lineArr[$Vendor_Name]) == 'QUALICHEM GENKEM') {
                   $currPrincipal = '415';	
               } elseif (strtoupper($lineArr[$Vendor_Name]) == 'ROVIC LEERS') {
                   $currPrincipal = '404';	                   
               } elseif (strtoupper($lineArr[$Vendor_Name]) == 'POSITEC') {
                   $currPrincipal = '444';	                   
               } elseif (strtoupper($lineArr[$Vendor_Name]) == 'ANDROWARE') {
                   $currPrincipal = '453';
               } elseif (strtoupper($lineArr[$Vendor_Name]) == 'TOPLINE') {
                   $currPrincipal = '460';
               } else {
                   $eTO->type = FLAG_ERRORTO_ERROR;
                   $eTO->description = "Cannnot Determine the Principal!"; 
                   echo "error = p1<br>";   
                   return $eTO;
                  
               }
               $specialField = array(336 => "387",
                                     346 => "402",
                                     444 => "614",
                                     404 => "540",
                                     376 => "472",
                                     415 => "563",
                                     460 => "638",
                                     453 => "626");
                                     
               $spField = isset($specialField[$currPrincipal]) ? $specialField[$currPrincipal] : "  ";                      

               $chainSpecFld = array(336 => "389",
                                     346 => "400",
                                     444 => "613",
                                     376 => "476",
                                     404 => "541",
                                     415 => "567",
                                     460 => "635",
                                     453 => "623");
                                     
               $chainSpField = isset($chainSpecFld[$currPrincipal]) ? $chainSpecFld[$currPrincipal] : "  ";   
               
               $DepotSpField = array(336 => "388",
                                     346 => "401",
                                     444 => "615",
                                     376 => "474",
                                     404 => "542",
                                     415 => "568",
                                     415 => "636",
                                     453 => "642");
                                     
               $depSpField = isset($DepotSpField[$currPrincipal]) ? $DepotSpField[$currPrincipal] : "  ";
               
               /*******************
               *   ORDER HEAD
               *******************/
               $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
               $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
               $postingOrdersHoldingTO->principalUid = $currPrincipal;
               $postingOrdersHoldingTO->updateProduct="Y";
               $postingOrdersHoldingTO->insertProduct="Y";
               $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
               $postingOrdersHoldingTO->dataSource = DS_EDI;
               $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
               $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
               $postingOrdersHoldingTO->requestedDeliveryDate = "";
               $postingOrdersHoldingTO->capturedBy = 'iRam';
               $postingOrdersHoldingTO->deliveryInstructions = trim($lineArr[$Vendor_Name]) . ' - ' . trim($lineArr[$Vendor_Number]);;
               
               $postingOrdersHoldingTO->offInvoiceDiscount = 0;
               $postingOrdersHoldingTO->offInvoiceDiscountType = '';  
               
               $postingOrdersHoldingTO->depotLookupRef = trim($lineArr[$Region]);
               $postingOrdersHoldingTO->documentStatusUId = DST_UNACCEPTED;
          
               $postingOrdersHoldingTO->orderDate = date("Y-m-d");
               
               if ($postingOrdersHoldingTO->orderDate === false) {
                        $eTO->type = FLAG_ERRORTO_ERROR;
                        $eTO->description = "Order date invalid format or empty";
                        $eTO->identifier = ET_CUSTOMER;
                        return $eTO;
               }
               
               if($lineArr[$Channel] == 'MAKRO') {
                    $prodPre = 'M';
               } else {
                    $prodPre = 'B' 	;
               }
               $postingOrdersHoldingTO->shipToName = trim($lineArr[$Site_Name]);
               $postingOrdersHoldingTO->deliverName = trim($lineArr[$Site_Name]);
               $postingOrdersHoldingTO->clientDocumentNo = '';
               $postingOrdersHoldingTO->documentNo = '';
               $postingOrdersHoldingTO->documentTypeUId = DT_UPLIFTS; 
               $postingOrdersHoldingTO->storeLookupRef = $lineArr[$Site];  // special field UID comes from arry above and not OLFPM
               $postingOrdersHoldingTO->enforceSameDepot = "N";
               $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
               $postingOrdersHoldingTO->oldAccount = '';
               $postingOrdersHoldingTO->salesAgentStoreIdentifier = "SP" . $spField . "CH" . $chainSpField . "WH" . $depSpField;
               $postingOrdersHoldingTO->reference = '';
               
               $newStore = trim($lineArr[$Site]) . trim($lineArr[$Vendor_Number]);
              
               $arrTO[] = $postingOrdersHoldingTO;
               
               // If only mailing the orders just create the store
               
               
               $prinCreateStore = array(999, 999, 999);
         
               if(in_array($currPrincipal,$prinCreateStore)) {
               
                   // Create the StoreTO
                   $postingStoreTO = new PostingStoreTO;
                   $postingStoreTO->DMLType = "INSERT";
                   $postingStoreTO->principalStoreUId = "";
                   $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                   $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[$Site_Name]));
                   $postingStoreTO->deliverAdd1 = '';
                   $postingStoreTO->deliverAdd2 = '';
                   $postingStoreTO->deliverAdd3 = '';
                   $postingStoreTO->billName = $postingStoreTO->deliverName;
                   $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
                   $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
                   $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
                   $postingStoreTO->vatNumber = ''; 
                   $postingStoreTO->telNo1    = ''; 
                   $postingStoreTO->depot = ""; // this will be set by the processing script
                   $postingStoreTO->deliveryDay = "8";
                   $postingStoreTO->noVAT = 0;
                   $postingStoreTO->onHold = "0";
                   $postingStoreTO->chain = ""; // this will be set by the processing script
                   $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                   $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                   $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->oldAccount = $lineArr[$Site];

                   $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;

                   // lookup special field(s) - enforce this specific one
                   $postingSpecialFieldTO = new PostingSpecialFieldTO;
                   $postingSpecialFieldTO->DMLType = "INSERT";
                   $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                   $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                   $postingSpecialFieldTO->fielduid = $spField; // Special Field
                   $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                   $postingSpecialFieldTO->value = $lineArr[$Site];
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;

                   // End Create the StoreTO
               }
           }       
           /******************
           *   ORDER DETAILS
           *****************/
          
           $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

           $postingOrdersHoldingDetailTO->listPrice      = $lineArr[$value];      
           $postingOrdersHoldingDetailTO->quantity       = $lineArr[$Aged_Stock];
           $postingOrdersHoldingDetailTO->nettPrice      = $lineArr[$value];
           $postingOrdersHoldingDetailTO->extPrice       = '';
           $postingOrdersHoldingDetailTO->vatRate        = VAL_VAT_RATE_TBLSTD;
           $postingOrdersHoldingDetailTO->vatAmount      = '';
           $postingOrdersHoldingDetailTO->totalPrice     = ''; 
           
           $postingOrdersHoldingDetailTO->additionalType = $lineArr[$value];  
/*           if(in_array(substr($lineArr[$Article],0,1), array('B','M'))) {
                 $postingOrdersHoldingDetailTO->productCode   = $lineArr[$Article];
           } else {
           	     $postingOrdersHoldingDetailTO->productCode   = $prodPre . $lineArr[$Article];
            //     $postingOrdersHoldingDetailTO->productCode   = $lineArr[$Part_Number];
           } */
           if(substr($lineArr[$Barcode],0,2) == 'BB') {
                $postingOrdersHoldingDetailTO->productCode   = 'B'. $lineArr[$Barcode];  
           } elseif(substr($lineArr[$Barcode],0,1) == 'B') {
                $postingOrdersHoldingDetailTO->productCode   = $lineArr[$Barcode]; 
           } else {
                $postingOrdersHoldingDetailTO->productCode   = 'B'. $lineArr[$Barcode];  	
           }
           $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
           $postingOrdersHoldingDetailTO->pallets       = 0;
           $postingOrdersHoldingDetailTO->productName   = '';
           $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
           $hasdetail = 'Y';
      }



      if($hasdetail =='Y') {
           $eTO->type = FLAG_ERRORTO_SUCCESS;
           $eTO->description = "Successful";
           $eTO->object = $arrTO;
           return $eTO;
      } else {
     	    $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid File or no Details";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
      }       


      
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------       
    function adaptorTOH_JUNBOBRANDS($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;
      
      $headerarray=array('Reference');


      $fileArr = explode("\n",$content);
      
      $validfile = 0;
       
      // "Reference","Document Date","Customer Account","Customer Branch","Customer Name","Postal Address 1","Postal Address 2","Postal Address 3","Postal Address 4","Postal Address 5","Postal Code","Physical Address 1","Physical Address 2","Physical Address 3","Physical Address 4","Physical Address 5","VAT Registration No","Customer Order No","Warehouse Code","Line Number","Bar Code","Stock Code","Line Item Description","Ordered Qty","Selling Price (Excl)","Line Disc %","Selling Price (Incl)","VAT Rate"

      if (in_array(substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $fileArr[0])),0,9), $headerarray))   {
         // validate first line of file
         $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");

         for ($x = 0; $x <= 30; $x++) {
         	    $lineValue = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$x]));
           	    
         	    if(trim($lineValue)=='Reference') {
         	       $docno = $x;
         	       $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  1";
                 echo "<br>";
              } elseif(trim($lineValue)=='Document Date') {
                 $order_date = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  2";
                 echo "<br>";
              } elseif(trim($lineValue)=='Customer Account') {
                 $account = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  3";
                 echo "<br>";
              } elseif(trim($lineValue)=='Customer Branch') {
                 $branch = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  4";
                 echo "<br>";
              } elseif(trim($lineValue)=='Customer Name') {
                 $StoreName = $x;
                 $validfile++;                  
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  5";
                 echo "<br>";
              } elseif(trim($lineValue)=='Postal Address 1') {
                 $posAdd1 = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  6";
                 echo "<br>";
              } elseif(trim($lineValue)=='Postal Address 2') {
                 $posAdd2 = $x;
                 $validfile++;                               
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  7";
                 echo "<br>";
              } elseif(trim($lineValue)=='Postal Address 3') {
                 $posAdd3 = $x;
                 $validfile++;   
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  8";
                 echo "<br>";
              } elseif(trim($lineValue)=='Postal Code') {
                 $posCode = $x;
                  $validfile++; 
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  9";
                 echo "<br>";
              } elseif(trim($lineValue)=='Physical Address 1') {
                 $add1 = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  10";
                 echo "<br>";
              } elseif(trim($lineValue)=='Physical Address 2') {
                 $add2 = $x;
                 $validfile++;                               
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  11";
                 echo "<br>";
              } elseif(trim($lineValue)=='Physical Address 3') {
                 $add3 = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  12";
                 echo "<br>";
              } elseif(trim($lineValue)=='VAT Registration No') {
                 $vatno = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  13";
                 echo "<br>";
              } elseif(trim($lineValue)=='Customer Order No') {
                 $reference = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  14";
                 echo "<br>";
              } elseif(trim($lineValue)=='Warehouse Code') {
                 $wh = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  15";
                 echo "<br>";
              } elseif(trim($lineValue)=='Line Number') {
                 $lineno = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  16";
                 echo "<br>";                 
              } elseif(trim($lineValue)=='Bar Code') {
                 $bc = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  17";
                 echo "<br>";
              } elseif(trim($lineValue)=='Stock Code') {
                 $product = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  17";
                 echo "<br>";
               } elseif(trim($lineValue)=='Line Item Description') {
                 $productDisc = $x;
                 $validfile++;
                  echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  18";
                 echo "<br>";
              } elseif(trim($lineValue)=='Ordered Qty') {
                 $qty = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  19";
                 echo "<br>";
              } elseif(trim($lineValue)=='Selling Price (Excl)') {
                 $sp = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  20";
                 echo "<br>";
              } elseif(trim($lineValue)=='Line Disc %') {
                 $discount = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  21";
                 echo "<br>";
 
              } elseif(trim($lineValue)=='VAT Rate') {
                 $vatR = $x;
                 $validfile++;
                 echo "<br>";         	    
                 echo trim($lineValue);
                 echo "  22";
                 echo "<br>";
              }
         }      
         if($validfile < 22 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Check file - Order fields missing!";
            return $eTO;
         }          
         if(count($lineArr) <> 28 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Invalid Column Count - expecting 28 columns Found " . count($lineArr);
            return $eTO;
         }          
         unset($fileArr[0]);
      } else {
         $eTO->type = FLAG_ERRORTO_ERROR;
         $eTO->description = "First line expected to be a header!";
         return $eTO;
      }
      
      $hasheader = 'N';
      $hasdetail = 'N';
      $newOrder  = '';
      
      foreach ($fileArr as $key=>$line) {
      	
           // convert line to CSV
           $lineArr=str_getcsv($line, ",", '"', "\\");
           
           if(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$docno]))<>'') {
               if ($newOrder <> trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$docno]))) {
                  /*******************
                   *   ORDER HEAD
                   *******************/
                   $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                   $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                   $postingOrdersHoldingTO->principalUid = 347;
                   $postingOrdersHoldingTO->updateProduct="Y";
                   $postingOrdersHoldingTO->insertProduct="Y";
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->updateStoreDepot = "Y";
                   $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                   $postingOrdersHoldingTO->dataSource = DS_EDI;
                   $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                   $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                   $postingOrdersHoldingTO->requestedDeliveryDate = "";
                   $postingOrdersHoldingTO->capturedBy = "JB";
                   $postingOrdersHoldingTO->deliveryInstructions = '';
                   $postingOrdersHoldingTO->additionalDetails = '';
                   $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                   $postingOrdersHoldingTO->offInvoiceDiscountType = '';
                   $postingOrdersHoldingTO->depotLookupRef = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$wh]));
          
                   $postingOrdersHoldingTO->orderDate =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$order_date]));
               
                   if ($postingOrdersHoldingTO->orderDate === false) {
                        $eTO->type = FLAG_ERRORTO_ERROR;
                        $eTO->description = "Order date invalid format or empty";
                        $eTO->identifier = ET_CUSTOMER;
                        return $eTO;
                   }
                   $postingOrdersHoldingTO->shipToName = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$StoreName]));
                   $postingOrdersHoldingTO->deliverName = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$StoreName]));
                   $postingOrdersHoldingTO->clientDocumentNo = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$docno]));
                   $postingOrdersHoldingTO->documentNo = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$docno])),2,8);
                   $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                   $postingOrdersHoldingTO->storeLookupRef = '';
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = 'Y';
                   $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                   $postingOrdersHoldingTO->oldAccount = '';
                   $postingOrdersHoldingTO->salesAgentStoreIdentifier = '';
                   $postingOrdersHoldingTO->reference = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$reference])),0,19);
               
                   $newOrder = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$docno]));
              
                   $arrTO[] = $postingOrdersHoldingTO;
               
                   // Create the StoreTO
                   $postingStoreTO = new PostingStoreTO;
                   $postingStoreTO->DMLType = "INSERT";
                   $postingStoreTO->principalStoreUId = "";
                   $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                   $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$StoreName])));
                   $postingStoreTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$add1])));
                   $postingStoreTO->deliverAdd2 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$add2])));
                   $postingStoreTO->deliverAdd3 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$add3])));
                   $postingStoreTO->billName = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$toreName])));
                   $postingStoreTO->billAdd1 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$posAdd1])));
                   $postingStoreTO->billAdd2 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$posAdd2])));
                   $postingStoreTO->billAdd3 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$posAdd3])));
                   $postingStoreTO->vatNumber = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$vatno])));    
                   $postingStoreTO->telNo1    = ''; 
                   $postingStoreTO->depot = ""; // this will be set by the processing script
                   $postingStoreTO->deliveryDay = "8";
                   $postingStoreTO->noVAT = 0;
                   $postingStoreTO->onHold = "0";
                   $postingStoreTO->chain = ""; // this will be set by the processing script
                   $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                   $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                   $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->oldAccount ='';

                   $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
                                                                           
                   // lookup special field(s) - enforce this specific one
                   $postingSpecialFieldTO = new PostingSpecialFieldTO;
                   $postingSpecialFieldTO->DMLType = "INSERT";
                   $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                   $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                   $postingSpecialFieldTO->fielduid = 403; // Special Field
                   $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                   $postingSpecialFieldTO->value = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$account]));
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;

               }       
               /******************
                *   ORDER DETAILS
                *****************/
          
                $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

                $postingOrdersHoldingDetailTO->clientLineNo =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$lineno]));
                $postingOrdersHoldingDetailTO->listPrice     = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$sp]));
                $postingOrdersHoldingDetailTO->quantity      = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$qty]));
                $postingOrdersHoldingDetailTO->discountValue = listPrice - ($postingOrdersHoldingDetailTO->listPrice) * (trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x25]/', '',$lineArr[$discount])) /100);
                $postingOrdersHoldingDetailTO->nettPrice     = $postingOrdersHoldingDetailTO->listPrice - ($postingOrdersHoldingDetailTO->listPrice) * (trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x25]/', '',$lineArr[$discount])) /100);
                $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->quantity * $postingOrdersHoldingDetailTO->nettPrice ;
                $postingOrdersHoldingDetailTO->vatRate       = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$vatR])),0,2) . '.00';
                $postingOrdersHoldingDetailTO->vatAmount     = $postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100 ;
                $postingOrdersHoldingDetailTO->totalPrice    = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;
                $postingOrdersHoldingDetailTO->productCode   = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$bc]));
                $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                $postingOrdersHoldingDetailTO->pallets       = 0;
                $postingOrdersHoldingDetailTO->productName   = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$productDisc]));;

                $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
                $hasdetail = 'Y';
           }
      }
      if($hasdetail =='Y') {
           $eTO->type = FLAG_ERRORTO_SUCCESS;
           $eTO->description = "Successful";
           $eTO->object = $arrTO;
           return $eTO;
      } else {
     	    $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid File or no Details";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
      }       
      }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------       
    function adaptorTOH_RICHSORDERS($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;
      
      $headerarray=array('Sales Doc.', 'Sales Order Number');

      $fileArr = explode("\n",$content);
      
      $validfile = 0;
      
//    Sales Order Number,Creation Date,Sales Document Type,Doc Currency,Sales Organisation,Distribution Channel,Division,Sales Group,Sales Office,Requested Delivery Date,PO Number,Sold-To Party,Sold-To Party Description,Ship-To Party,Delivery Address,,,,,Credit Account,Item No,Material No,Description,Order Qty,Sales Unit,Gross Weight,Nett Weight,Weight Unit,Volume,Volume Unit,Plant,Storage Location,Unit Price,Value Exclusive,VAT,Value Inclusive
//    ,,,,,,,,,,,,,,Name ,Name 2,Street,City,Postal Code,,,,,,,,,,,,,,,,,

      
      if (in_array(trim(substr($fileArr[0],0,10)), $headerarray))   {
      	

      	
         // validate first line of file
         $lineArr=str_getcsv($fileArr[0], ";", '"', "\\");

         for ($x = 0; $x <= 36; $x++) {
        	    if(trim($lineArr[$x])=='Sales Doc.') {
         	       $soNumber = $x;
         	       $validfile++;
              } elseif(trim($lineArr[$x])=='Created On') {
                 $creationDate = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='SalesDocTy') {
                 $SalesDocTy = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Currency') {
                 $docCur = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Sales Org.') {
                 $alesOrganisation = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Distr. Chl') {
                 $distributionChannel = $x;
                 $validfile++;                  
              } elseif(trim($lineArr[$x])=='Division') {
                 $division = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Sales Grp') {
                 $salesGroup = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Sales Off') {
                 $salesOffice = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Req.dlv.dt') {
                 $requestedDeliveryDate = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='PO Number') {
                 $PONumber = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Name 1') {
                 $soldToPartyDesc = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Sold to Party') {
                 $SoldToParty = $x;
                 $validfile++;     
              } elseif(trim($lineArr[$x])=='Name') {
                 $deliveryName = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Name 2') {
                 $Branch = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Street') {
                 $deliveryAdd1 = $x;
                 $validfile++;                  
              } elseif(trim($lineArr[$x])=='City') {
                 $deliveryAdd2 = $x;
                 $validfile++;                   
              } elseif(trim($lineArr[$x])=='Postl Code') {
                 $deliveryAdd3 = $x;
                 $validfile++;  
              } elseif(trim($lineArr[$x])=='Cred. Acct') {
                 $Cred_Acc = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Item') {
                 $Item_no = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Material') {
                 $Material_No = $x;
                 $validfile++;                  
              } elseif(trim($lineArr[$x])=='Description') {
                 $Description = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Order Qty') {
                 $Order_Qty = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Unit') {
                 $Sales_Unit = $x;
                 $validfile++;                              
              } elseif(trim($lineArr[$x])=='Gross Weight') {
                 $Gross_Weight = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='WeightUnit') {
                 $Nett_Weight = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Weight Unit') {
                 $Weight_Unit = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Volume') {
                 $Volume = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='VolumeUnit') {
                 $Volume_Unit = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Plant') {
                 $Plant = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Stor. Loc.') {
                 $Storage_Location = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Net price') {
                 $Unit_Price = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Subtotal') {
                 $Value_Exclusive = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Tax') {
                 $VAT = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Amount') {
                 $Value_Inclusive = $x;
                 $validfile++;                 
              }   
              echo $lineArr[$x];
              echo "<br>";
           } 
         echo "<br>";
         echo $validfile;
         echo "<br>";
         if($validfile <> 31 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Check file - Order fields missing!";
            return $eTO;
         }          
         if(count($lineArr) < 31 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Invalid Column Count - expecting 31 columns Found " . count($lineArr);
            return $eTO;
         }          
         unset($fileArr[0]);      
      } else {
         $eTO->type = FLAG_ERRORTO_ERROR;
         $eTO->description = "First line expected to be a header!";
         return $eTO;
      }
      
      $hasheader = 'Y';
      $hasdetail = 'Y';
      $newOrder  = '';


      foreach ($fileArr as $key=>$line) {
      	
           // convert line to CSV
           $lineArr=str_getcsv($line, ";", '"', "\\");
           
           if(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$soNumber]))<>'') {
               if ($newOrder <> trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$soNumber]))) {
               	
                   /*******************
                   *   ORDER HEAD
                   *******************/
                   $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                   $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                   $postingOrdersHoldingTO->principalUid = 354;
                   $postingOrdersHoldingTO->updateProduct="N";
                   $postingOrdersHoldingTO->insertProduct="Y";
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->updateStoreDepot = "Y";
                   $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                   $postingOrdersHoldingTO->dataSource = DS_EDI;
                   $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                   $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                   $reqDelYY = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$requestedDeliveryDate])),6,4);
                   $reqDelMM = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$requestedDeliveryDate])),3,2);
                   $reqDelDD = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$requestedDeliveryDate])),0,2);
                   
                   $postingOrdersHoldingTO->requestedDeliveryDate = $reqDelYY . '-' . $reqDelMM . '-' .  $reqDelDD ;
                   $postingOrdersHoldingTO->capturedBy = "RICH";
                   $postingOrdersHoldingTO->deliveryInstructions = '';
                   $postingOrdersHoldingTO->additionalDetails = '';
                   $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                   $postingOrdersHoldingTO->offInvoiceDiscountType = '';
                   $postingOrdersHoldingTO->depotLookupRef = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$Plant]));
                   
                   $ordDelYY = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$creationDate])),6,4);
                   $ordDelMM = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$creationDate])),3,2);
                   $ordDelDD = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$creationDate])),0,2);
          
                   $postingOrdersHoldingTO->orderDate =  $ordDelYY . '-' . $ordDelMM . '-' . $ordDelDD;
               
                   if ($postingOrdersHoldingTO->orderDate === false) {
                        $eTO->type = FLAG_ERRORTO_ERROR;
                        $eTO->description = "Order date invalid format or empty";
                        $eTO->identifier = ET_CUSTOMER;
                        return $eTO;
                   }
                   
                   $cdno  = str_pad(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$soNumber])),10,"0",STR_PAD_LEFT); 
                   $docno = str_pad(trim(substr($cdno,2,8)),8,"0",STR_PAD_LEFT);

                   $postingOrdersHoldingTO->shipToName = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$deliveryName]));
                   $postingOrdersHoldingTO->deliverName = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$deliveryName]));
                   $postingOrdersHoldingTO->clientDocumentNo = $cdno;
                   $postingOrdersHoldingTO->documentNo = $docno;
                   $postingOrdersHoldingTO->documentTypeUId = DT_SALES_ORDER;
                   $postingOrdersHoldingTO->storeLookupRef = '';
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = 'Y';
                   $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                   $postingOrdersHoldingTO->oldAccount = '';
                   $postingOrdersHoldingTO->salesAgentStoreIdentifier = '';
                   $postingOrdersHoldingTO->reference = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$PONumber])),0,19);
               
                   $newOrder = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$soNumber]));

                   $arrTO[] = $postingOrdersHoldingTO;
               
                   // Create the StoreTO
                   $postingStoreTO = new PostingStoreTO;
                   $postingStoreTO->DMLType = "INSERT";
                   $postingStoreTO->principalStoreUId = "";
                   $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                   $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$deliveryName])));
                   $postingStoreTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$deliveryAdd1])));
                   $postingStoreTO->deliverAdd2 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$deliveryAdd2])));
                   $postingStoreTO->deliverAdd3 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$deliveryAdd3])));
                   $postingStoreTO->billName = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$deliveryName])));
                   $postingStoreTO->billAdd1 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$deliveryAdd1])));
                   $postingStoreTO->billAdd2 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$deliveryAdd2])));
                   $postingStoreTO->billAdd3 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$deliveryAdd3])));
                   $postingStoreTO->vatNumber = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$vatno])));    
                   $postingStoreTO->telNo1    = ''; 
                   $postingStoreTO->depot = ""; // this will be set by the processing script
                   $postingStoreTO->deliveryDay = "8";
                   $postingStoreTO->noVAT = 0;
                   $postingStoreTO->onHold = "0";
                   $postingStoreTO->chain = ""; // this will be set by the processing script
                   $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                   $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                   $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->oldAccount ='';

                   $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
                                                                           
                   // lookup special field(s)
                   $postingSpecialFieldTO = new PostingSpecialFieldTO;
                   $postingSpecialFieldTO->DMLType = "INSERT";
                   $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                   $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                   $postingSpecialFieldTO->fielduid = 427; // Special Field
                   $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                   $postingSpecialFieldTO->value = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$SoldToParty]));
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;

                   $postingSpecialFieldTO = new PostingSpecialFieldTO;
                   $postingSpecialFieldTO->DMLType = "INSERT";
                   $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                   $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                   $postingSpecialFieldTO->fielduid = 428; // Special Field
                   $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                   $postingSpecialFieldTO->value = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$ShipToParty]));
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;
                   
                   $postingSpecialFieldTO = new PostingSpecialFieldTO;
                   $postingSpecialFieldTO->DMLType = "INSERT";
                   $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                   $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                   $postingSpecialFieldTO->fielduid = 431; // Special Field
                   $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                   $postingSpecialFieldTO->value = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$salesGroup]));
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;

                   $postingSpecialFieldTO = new PostingSpecialFieldTO;
                   $postingSpecialFieldTO->DMLType = "INSERT";
                   $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                   $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                   $postingSpecialFieldTO->fielduid = 432; // Special Field
                   $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                   $postingSpecialFieldTO->value = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$salesOffice]));
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;


               }       
                     $newOrder = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$soNumber]));
           }       
           /******************
           *   ORDER DETAILS
           *****************/
          
           $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
           
           if(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$VAT])) == '0.00' ) {
           	   $vrat = '00.00';
           } else {
               $vrat = '15.00';           	
           }
           $postingOrdersHoldingDetailTO->clientLineNo  =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',ltrim($lineArr[$Item_no],'0')));
           $postingOrdersHoldingDetailTO->listPrice     =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$Unit_Price]));
           $postingOrdersHoldingDetailTO->quantity      =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$Order_Qty]));
           $postingOrdersHoldingDetailTO->discountValue = '';
           $postingOrdersHoldingDetailTO->nettPrice     =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$Unit_Price]));
           $postingOrdersHoldingDetailTO->extPrice      =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$Value_Exclusive]));
           $postingOrdersHoldingDetailTO->vatRate       =  $vrat;
           $postingOrdersHoldingDetailTO->vatAmount     =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$VAT]));;
           $postingOrdersHoldingDetailTO->totalPrice    =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$Value_Inclusive]));;
           $postingOrdersHoldingDetailTO->productCode   =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$Material_No]));
           $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
           $postingOrdersHoldingDetailTO->pallets       = 0;
           $postingOrdersHoldingDetailTO->productName   = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$Description]));;

           $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
           $hasdetail = 'Y';




      }   
      if($hasdetail =='Y') {
           $eTO->type = FLAG_ERRORTO_SUCCESS;
           $eTO->description = "Successful";
           $eTO->object = $arrTO;
           return $eTO;
      } else {
     	    $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid File or no Details";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
      }       


      
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------       
    //BM FOODS New System
    function adaptorTOH_BMF2($content, $onlineFileProcessItem) {
         
         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
         global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

         $eTO = new ErrorTO;
         if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
         $arrTO=array();
         $processingLine=0;
      
         $headerarray=array('HRD');

         $fileArr = explode("\n",$content);
         
         foreach ($fileArr as $key=>$line) {
         	
               // convert line to CSV
               $lineArr=str_getcsv($line, ",", '"', "\\");
               
               if (trim($lineArr[0] ==  "HRD01"))   {
               	
                      if (isset($postingOrdersHoldingTO)) {
                          if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
                               $eTO->type = FLAG_ERRORTO_ERROR;
                               $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
                               $eTO->identifier = ET_CUSTOMER;
                               return $eTO;
                          }
                          $arrTO[] = $postingOrdersHoldingTO;
                          unset($postingOrdersHoldingTO);
                      }
                      
                      $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                      $postingOrdersHoldingTO->updateProduct="Y";
                      $postingOrdersHoldingTO->insertProduct="Y";
                      $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                      $postingOrdersHoldingTO->principalUid     = 290 ;
                      $postingOrdersHoldingTO->vendorUid        = $onlineFileProcessItem["vendor_uid"];
                      $postingOrdersHoldingTO->captureDate      = CommonUtils::getGMTime(0);
                      $postingOrdersHoldingTO->capturedBy       = 'BMSAGE'; // dont change this as notifications run off it
                      $postingOrdersHoldingTO->incomingFile     = basename($onlineFileProcessItem["file_being_processed"]);
                      $postingOrdersHoldingTO->dataSource       = DS_EDI;
                      $postingOrdersHoldingTO->documentNo       = substr(trim($lineArr[2]), 6, 8);  //use provided doc no
                      
                      echo trim($lineArr[2]);
                      
                      $postingOrdersHoldingTO->clientDocumentNo = trim($lineArr[2]);  //use provided doc no
                      $postingOrdersHoldingTO->reference        = trim($lineArr[4]); //PO NUMBER.
                      $postingOrdersHoldingTO->vendorReference  = trim($lineArr[2]);;
                      $postingOrdersHoldingTO->oldAccount       = trim($lineArr[13]);
                      $postingOrdersHoldingTO->debtorsStoreIdentifier = "";
                      $postingOrdersHoldingTO->salesAgentStoreIdentifier = $postingOrdersHoldingTO->oldAccount; //same as old acc.
                      $postingOrdersHoldingTO->storeLookupRef   = trim($lineArr[13]);
                      $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';  //use generic => EDI pricing is used. no problem, happy days :)
   
                      $postingOrdersHoldingTO->deliverName    = trim($lineArr[5]);
                      $postingOrdersHoldingTO->shipToName     = $postingOrdersHoldingTO->deliverName;
 
                      $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;

                      $postingOrdersHoldingTO->orderDate = substr(trim($lineArr[3]), 2, 2) . '-' . substr(trim($lineArr[3]),4, 2) . '-' . substr(trim($lineArr[3]), 6, 2); //format YY-MM-DD, MySQL will accept this.
                      $postingOrdersHoldingTO->requestedDeliveryDate = substr(trim($lineArr[3]), 2, 2) . '-' . substr(trim($lineArr[3]),4, 2) . '-' . substr(trim($lineArr[3]), 6, 2); 
                      //check order date. must be a valid date and not 1970-01-01
                      $ordDate = date('Ymd', strtotime($postingOrdersHoldingTO->orderDate));  //if value malformed will be 1970-01-01 and check below will activate!
                      if(($postingOrdersHoldingTO->orderDate===false) || !(checkdate(substr($ordDate, 4,2), substr($ordDate,6,2), substr($ordDate, 0,4))) || ($ordDate == '1970-01-01')){
                           $eTO->type = FLAG_ERRORTO_ERROR;
                           $eTO->description = "Order date Invalid format or empty";
                           $eTO->identifier = ET_CUSTOMER;
                           return $eTO;
                      }

                      /*******************
                      *   CREATE STORE
                      *******************/
                     $postingStoreTO = new PostingStoreTO;
                     $postingStoreTO->DMLType = "INSERT";
                     $postingStoreTO->principalStoreUId = '';
                     $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                     $postingStoreTO->deliverName = $postingOrdersHoldingTO->deliverName;
                     $postingStoreTO->deliverAdd1 = trim($lineArr[6]);
                     $postingStoreTO->deliverAdd2 = trim($lineArr[7]);
                     $postingStoreTO->deliverAdd3 = trim($lineArr[8]);
                     $postingStoreTO->billName    = $postingOrdersHoldingTO->deliverName;   
                     $postingStoreTO->billAdd1    = trim($lineArr[6]);                 
                     $postingStoreTO->billAdd2    = trim($lineArr[7]);             
                     $postingStoreTO->billAdd3    = trim($lineArr[8]);
                     $postingStoreTO->vatNumber   = trim($lineArr[15]);
                     $postingStoreTO->depot = ''; // this will be set by the processing script
                     $postingStoreTO->deliveryDay = "8";
                     $postingStoreTO->noVAT = 0;
                     $postingStoreTO->onHold = "0";
                     $postingStoreTO->chain = ''; // this needs to be assigned by exceptions user.
                     $postingStoreTO->altPrincipalChainUId = ''; // let the posting allocate the generic chain
                     $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                     $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                     $postingStoreTO->ownedBy = '';
                     $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;  //NB!!!
                     $postingStoreTO->updatePrincipalStore = 'Y';
                     
                     $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO; 

                     // lookup special field(s) - enforce this specific one
                     $postingSpecialFieldTO = new PostingSpecialFieldTO;
                     $postingSpecialFieldTO->DMLType = "INSERT";
                     $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                     $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                     $postingSpecialFieldTO->fielduid = 444; // Special Field
                     $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                     $postingSpecialFieldTO->value = trim($lineArr[13]);
                     $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;
               } 
               if (trim($lineArr[0] ==  "DTL01"))   {
               	     /*******************
                     *   ORDER DETAILS
                     *******************/
                     
                      // echo "<pre>";
                      // print_r($lineArr);
                          
                          $postingOrdersHoldingTO->depotLookupRef = trim($lineArr[3]);
                     
                          $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();  // should not be necessary to check document type as other types should end up as zeros
                          $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                          $postingOrdersHoldingDetailTO->pallets             = 0;
                          $postingOrdersHoldingDetailTO->clientPageNo        = '';
                          $postingOrdersHoldingDetailTO->clientLineNo        = substr(trim($lineArr[2]),0,5); 
                          $postingOrdersHoldingDetailTO->productCode         = trim($lineArr[4]) . "-" . trim($lineArr[6]);
                          $postingOrdersHoldingDetailTO->productName         = trim($lineArr[5]);
                          $postingOrdersHoldingDetailTO->itemspercase        = substr(trim($lineArr[6]),1,2); 
                          $postingOrdersHoldingDetailTO->discountReference   = '';
                          $postingOrdersHoldingDetailTO->quantity            = trim($lineArr[8]);
                          $postingOrdersHoldingDetailTO->listPrice           = trim($lineArr[9]);
                          $postingOrdersHoldingDetailTO->discountValue       = (trim($lineArr[9]) * trim($lineArr[10]) /100);
                          $postingOrdersHoldingDetailTO->nettPrice           = $postingOrdersHoldingDetailTO->listPrice - $postingOrdersHoldingDetailTO->discountValue;
                          $postingOrdersHoldingDetailTO->extPrice            = ($postingOrdersHoldingDetailTO->nettPrice * $postingOrdersHoldingDetailTO->quantity);
                          $postingOrdersHoldingDetailTO->vatRate             = "0015.00";; // use RT Masterfiles
                          $postingOrdersHoldingDetailTO->vatAmount           = round($postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100 ,2);
                          $postingOrdersHoldingDetailTO->totalPrice          = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;
                          $postingOrdersHoldingTO->detailArr[]               = $postingOrdersHoldingDetailTO;
                          // echo "<pre>";
                          // print_r($postingOrdersHoldingDetailTO);
                          // echo "<br>"; 
                          

              }   

         }

         $arrTO[] = $postingOrdersHoldingTO; //add final order, no need to check if has detail as we do that in the begin.     
         $eTO->type = FLAG_ERRORTO_SUCCESS;
         $eTO->description = "Successful";
         $eTO->object = $arrTO;
         return $eTO;
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------
    function adaptorTOH_RICHSINVOICES($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;      
      $headerarray=array('Date');

      $fileArr = explode("\n",$content);
      
      $validfile = 0;
      
      if (in_array(trim(substr($fileArr[0],0,4)), $headerarray))   {
         // validate first line of file
         $lineArr=str_getcsv($fileArr[0], ";", '"', "\\");

         for ($x = 0; $x <= 50; $x++) {
        	    if(trim($lineArr[$x])=='Date') {
         	       $iDate = $x;
         	       $validfile++;
              } elseif(trim($lineArr[$x])=='Document') {
                 $oDocument = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Document Number') {
                 $invNumber = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Inv Addrnum') {
                 $invAddrnum = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Inv Name') {
                 $InvName = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Inv PO Box') {
                 $InvPOBox = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Inv City') {
                 $InvCity = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Inv Post Code') {
                 $InvPostCode = $x;
                 $validfile++;     
              } elseif(trim($lineArr[$x])=='Inv Country') {
                 $InvCountry = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Deliv Addrnum') {
                 $DelivAddrnum = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Deliv Name') {
                 $DelivName = $x;
                 $validfile++;                  
              } elseif(trim($lineArr[$x])=='Deliv Name2') {
                 $DelivName2 = $x;
                 $validfile++;                   
              } elseif(trim($lineArr[$x])=='Deliv Street') {
                 $DelivStreet = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Deliv City') {
                 $Deliv_City = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Deliv Post Code') {
                 $DelivPostCode = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Account Number') {
                 $AccountNumber = $x;
                 $validfile++;                  
              } elseif(trim($lineArr[$x])=='Deliv Acc No.') {
                 $DelivAccNo = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='VAT Reg No (RICH)') {
                 $VATRegNo_RICH = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Customer PO No.') {
                 $CustomerPONo = $x;
                 $validfile++;                              
              } elseif(trim($lineArr[$x])=='Deliv Method') {
                 $DelivMethod = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Payment Terms') {
                 $PaymentTerms = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='PaymentTerms1') {
                 $PaymentTerms1 = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Payment Terms2') {
                 $PaymentTerms2 = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Payment Terms3') {
                 $PaymentTerms3 = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='VAT Reg No (Cust)') {
                 $VATRegNoCust = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Representative') {
                 $Representative = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Warehouse') {
                 $Warehouse = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Deliv Note No.') {
                 $Value_Exclusive = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Page') {
                 $Page = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='Item No') {
                 $Item_No = $x;
                 $validfile++;                 
              } elseif(trim($lineArr[$x])=='SO Number') {
                 $SONumber = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='SO Itm No') {
                 $SOItmNo = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Product Code') {
                 $ProductCode = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Description') {
                 $Description = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Order Qty') {
                 $OrderQty = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Invoice Qty') {
                 $InvoiceQty = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='To Follow Qty') {
                 $ToFollowQty = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Unit') {
                 $Unit = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Unit Price') {
                 $UnitPrice = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Disc') {
                 $Disc = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Exclusive Value') {
                 $ExclusiveValue = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='VAT Amnt') {
                 $VATAmnt = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Inclusive Value') {
                 $InclusiveValue = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Total Excl VAT') {
                 $TotalExclVAT = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Total VAT Amnt') {
                 $TotalVATAmnt = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Total') {
                 $Total = $x;
                 $validfile++; 
              } elseif(trim($lineArr[$x])=='Total Mass') {
                 $TotalMass = $x;
                 $validfile++; 
              } 
         }     
         echo "<br>";
         echo $validfile;
         echo "<br>";
         if($validfile <> 46 ) {
             $eTO->type = FLAG_ERRORTO_ERROR;
             $eTO->description = "Check file - Order fields missing!";
             return $eTO;
         }          
         if(count($lineArr) < 46 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Invalid Column Count - expecting 46 columns Found " . count($lineArr);
            return $eTO;
         }          
         unset($fileArr[0]);      
      } else {
         $eTO->type = FLAG_ERRORTO_ERROR;
         $eTO->description = "First line expected to be a header!";
         return $eTO;
      }
      
      $hasheader = 'Y';
      $hasdetail = 'Y';
      $newOrder  = '';

      foreach ($fileArr as $key=>$line) {
      	
           // convert line to CSV
           $lineArr=str_getcsv($line, ";", '"', "\\");
           
           if(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$SONumber]))<>'') {
               if ($newOrder <> trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$SONumber]))) {
               	
                   /*******************
                   *   ORDER HEAD
                   *******************/
                   $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                   $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                   $postingOrdersHoldingTO->principalUid = 354;
                   $postingOrdersHoldingTO->updateProduct="N";
                   $postingOrdersHoldingTO->insertProduct="Y";
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->updateStoreDepot = "Y";
                   $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                   $postingOrdersHoldingTO->dataSource = DS_EDI;
                   $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                   $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                   
                   $postingOrdersHoldingTO->requestedDeliveryDate = '' ;
                   $postingOrdersHoldingTO->capturedBy = "RICH";
                   $postingOrdersHoldingTO->deliveryInstructions = '';
                   $postingOrdersHoldingTO->additionalDetails = '';
                   $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                   $postingOrdersHoldingTO->offInvoiceDiscountType = '';
                   $postingOrdersHoldingTO->depotLookupRef = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$Warehouse]));
                   
                   $ordDelYY = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$iDate])),6,4);
                   $ordDelMM = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$iDate])),3,2);
                   $ordDelDD = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$iDate])),0,2);
          
                   $postingOrdersHoldingTO->orderDate =  $ordDelYY . '-' . $ordDelMM . '-' . $ordDelDD;
               
                   if ($postingOrdersHoldingTO->orderDate === false) {
                        $eTO->type = FLAG_ERRORTO_ERROR;
                        $eTO->description = "Order date invalid format or empty";
                        $eTO->identifier = ET_CUSTOMER;
                        return $eTO;
                   }
                   
                   $cdno  = str_pad(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$SONumber])),10,"0",STR_PAD_LEFT); 
                   $docno = str_pad(trim(substr($cdno,2,8)),8,"0",STR_PAD_LEFT);

                   $postingOrdersHoldingTO->shipToName = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$DelivName]));
                   $postingOrdersHoldingTO->deliverName = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$DelivName]));
                   $postingOrdersHoldingTO->clientDocumentNo = $cdno;
                   $postingOrdersHoldingTO->documentNo = $docno;
                   $postingOrdersHoldingTO->invoiceDate = $postingOrdersHoldingTO->orderDate; 
                   $postingOrdersHoldingTO->invoiceNumber = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$invNumber]));                  
                   $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                   $postingOrdersHoldingTO->storeLookupRef = '';
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = 'Y';
                   $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                   $postingOrdersHoldingTO->oldAccount = '';
                   $postingOrdersHoldingTO->skipInvoiceComputationCheck = "N";
                   $postingOrdersHoldingTO->salesAgentStoreIdentifier = '';
                   $postingOrdersHoldingTO->reference = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$CustomerPONo])),0,19);
               
                   $newOrder = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$SONumber]));
                   
//                 print_r($postingOrdersHoldingTO);
               	
              
                   $arrTO[] = $postingOrdersHoldingTO;
               
                   // Create the StoreTO
                   $postingStoreTO = new PostingStoreTO;
                   $postingStoreTO->DMLType = "INSERT";
                   $postingStoreTO->principalStoreUId = "";
                   $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                   $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$DelivName])));
                   $postingStoreTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$DelivStreet])));
                   $postingStoreTO->deliverAdd2 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$Deliv_City])));
                   $postingStoreTO->deliverAdd3 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$DelivPostCode])));
                   $postingStoreTO->billName = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$InvName])));
                   $postingStoreTO->billAdd1 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$InvPOBox])));
                   $postingStoreTO->billAdd2 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$InvCity])));
                   $postingStoreTO->billAdd3 = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$InvPostCode])));
                   $postingStoreTO->vatNumber = mysqli_real_escape_string($this->dbConn->connection,trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$VATRegNoCust])));    
                   $postingStoreTO->telNo1    = ''; 
                   $postingStoreTO->depot = ""; // this will be set by the processing script
                   $postingStoreTO->deliveryDay = "8";
                   $postingStoreTO->noVAT = 0;
                   $postingStoreTO->onHold = "0";
                   $postingStoreTO->chain = ""; // this will be set by the processing script
                   $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                   $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                   $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->oldAccount ='';

                   $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
                                                                           
                   // lookup special field(s)
                   $postingSpecialFieldTO = new PostingSpecialFieldTO;
                   $postingSpecialFieldTO->DMLType = "INSERT";
                   $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                   $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                   $postingSpecialFieldTO->fielduid = 427; // Special Field
                   $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                   $postingSpecialFieldTO->value = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$DelivAccNo]));
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;

                   $postingSpecialFieldTO = new PostingSpecialFieldTO;
                   $postingSpecialFieldTO->DMLType = "INSERT";
                   $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                   $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                   $postingSpecialFieldTO->fielduid = 428; // Special Field
                   $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                   $postingSpecialFieldTO->value = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$DelivAccNo]));
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;

               }       
                     $newOrder = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $lineArr[$SONumber]));
           }       
           /******************
           *   ORDER DETAILS
           *****************/
          
           $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
           
           if(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$VATAmnt])) == '0.00' ) {
           	   $vrat = '00.00';
           } else {
               $vrat = '15.00';           	
           }
           if(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$InvoiceQty])) > 0) {
           	
               $qtyVal        = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$OrderQty]));
               $uPValue       = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$UnitPrice]));
               
               $ExclValue     = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$ExclusiveValue]));
               
           	   $discountValue =  round($uPValue - ( $ExclValue / $qtyVal),2);

               $nettValue     =  round($uPValue - $discountValue,2);

               $postingOrdersHoldingDetailTO->clientLineNo  =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',ltrim($lineArr[$Item_No],'0')));
               $postingOrdersHoldingDetailTO->listPrice     =  $uPValue;
               $postingOrdersHoldingDetailTO->quantity      =  $qtyVal;
               $postingOrdersHoldingDetailTO->discountValue =  $discountValue;
               $postingOrdersHoldingDetailTO->nettPrice     =  $nettValue;
               $postingOrdersHoldingDetailTO->extPrice      =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$ExclusiveValue]));
               $postingOrdersHoldingDetailTO->vatRate       =  $vrat;
               $postingOrdersHoldingDetailTO->vatAmount     =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$VATAmnt]));
               $postingOrdersHoldingDetailTO->totalPrice    =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$InclusiveValue]));
               $postingOrdersHoldingDetailTO->productCode   =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$ProductCode]));
               $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
               $postingOrdersHoldingDetailTO->pallets       = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$lineArr[$InvoiceQty]));;
               $postingOrdersHoldingDetailTO->productName   = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$Description]));;
               
               $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
               $hasdetail = 'Y';
           }
      } 

      if($hasdetail =='Y') {
           $eTO->type = FLAG_ERRORTO_SUCCESS;
           $eTO->description = "Successful";
           $eTO->object = $arrTO;
           return $eTO;
      } else {
     	    $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid File or no Details";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
      }       

      
    }
// --------------------------------------------------------------------------------------------------------------------------------
   function adaptorTOH_GOOSEBUMPSDOCS($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;
      
      $headerarray=array('Sales Doc.', 'Sales Order Number');

      $fileArr =  get_object_vars(json_decode($content));
//      echo "<pre>";
//      print_r($fileArr);
//      echo "</pre>";
      
// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

     /*******************
     *   ORDER HEAD
     *******************/
     $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
     $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
     $postingOrdersHoldingTO->principalUid = 369;
     $postingOrdersHoldingTO->updateProduct="Y";
     $postingOrdersHoldingTO->insertProduct="Y";
     $postingOrdersHoldingTO->enforceSameDepot = "N";
     $postingOrdersHoldingTO->updateStoreDepot = "Y";
     $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
     $postingOrdersHoldingTO->dataSource = DS_EDI;
     $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
     $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
     
     $postingOrdersHoldingTO->requestedDeliveryDate = '' ;
     $postingOrdersHoldingTO->capturedBy = "GOOSE";
     $postingOrdersHoldingTO->deliveryInstructions = '';
     $postingOrdersHoldingTO->additionalDetails = '';
     $postingOrdersHoldingTO->offInvoiceDiscount = 0;
     $postingOrdersHoldingTO->offInvoiceDiscountType = '';          
     $ordDelYY = substr(trim($fileArr['invoiceDate']),0,4);
     $ordDelMM = substr(trim($fileArr['invoiceDate']),5,2);
     $ordDelDD = substr(trim($fileArr['invoiceDate']),8,2);
     
     $postingOrdersHoldingTO->orderDate =  $ordDelYY . '-' . $ordDelMM . '-' . $ordDelDD;
     
     if ($postingOrdersHoldingTO->orderDate === false) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Order date invalid format or empty";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
     }
     
     $cdno  = trim($fileArr['invoice']);
     
     if (strlen($fileArr['invoice']) <= 8 ) {
     	    $docno = substr($fileArr['invoice'],0,1) . str_pad(trim(substr($fileArr['invoice'],1,7)),7,"0",STR_PAD_LEFT);
     } else {
          $docno = substr($fileArr['invoice'],0,2) . str_pad(trim(substr($fileArr['invoice'],-6)),6,"0",STR_PAD_LEFT);
     }
     
     $dname = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $fileArr['customerName']));
     $dadd1 = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $fileArr['shipAddress1']));
     $dadd2 = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $fileArr['shipAddress2']));
     $dadd3 = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $fileArr['shipAddress3']));
 
     $bname = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $fileArr['customerName']));
     $badd1 = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $fileArr['shipAddress1']));
     $badd2 = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $fileArr['shipAddress2']));
     $badd3 = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $fileArr['shipAddress3']));
     
     $dlook = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $fileArr['customer']));       

     $postingOrdersHoldingTO->shipToName       = $dname;
     $postingOrdersHoldingTO->deliverName      = $dname;
     $postingOrdersHoldingTO->clientDocumentNo = $cdno;
     $postingOrdersHoldingTO->documentNo       = $docno;
     $postingOrdersHoldingTO->invoiceDate      = $postingOrdersHoldingTO->orderDate; 
     $postingOrdersHoldingTO->invoiceNumber    = $cdno;                
     $postingOrdersHoldingTO->documentTypeUId  = DT_ORDINV;
     $postingOrdersHoldingTO->storeLookupRef   = $dlook;  // special field UID comes from arry above and not OLFPM
     $postingOrdersHoldingTO->enforceSameDepot = "N";
     $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = 'N';
     $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
     $postingOrdersHoldingTO->oldAccount = '';
     $postingOrdersHoldingTO->salesAgentStoreIdentifier = '';
     $postingOrdersHoldingTO->vendorReference = trim($fileArr['customerTaxNumber']);
     $postingOrdersHoldingTO->reference = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', $fileArr['customerPurchaseOrder'])),0,19);

     // Create the StoreTO
     $postingStoreTO = new PostingStoreTO;
     $postingStoreTO->DMLType = "INSERT";
     $postingStoreTO->principalStoreUId = "";
     $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
     $postingStoreTO->deliverName = $dname;
     $postingStoreTO->deliverAdd1 = $dadd1;
     $postingStoreTO->deliverAdd2 = $dadd2;
     $postingStoreTO->deliverAdd3 = $dadd3;
     $postingStoreTO->billName = $dname;
     $postingStoreTO->billAdd1 = $badd1;
     $postingStoreTO->billAdd2 = $badd2;
     $postingStoreTO->billAdd3 = $badd3;
     $postingStoreTO->vatNumber = trim($fileArr['customerTaxNumber']);; 
     $postingStoreTO->telNo1    = ''; 
     $postingStoreTO->depot = ""; // this will be set by the processing script
     $postingStoreTO->deliveryDay = "8";
     $postingStoreTO->noVAT = 0;
     $postingStoreTO->onHold = "0";
     $postingStoreTO->chain = ""; // this will be set by the processing script
     $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
     $postingStoreTO->status = FLAG_STATUS_ACTIVE;
     $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
     $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
     $postingStoreTO->oldAccount = $dlook;

     $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;

     // lookup special field(s) - enforce this specific one
     $postingSpecialFieldTO = new PostingSpecialFieldTO;
     $postingSpecialFieldTO->DMLType = "INSERT";
     $postingSpecialFieldTO->principal = $postingStoreTO->principal;
     $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
     $postingSpecialFieldTO->fielduid = 451; // Special Field
     $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
     $postingSpecialFieldTO->value = $dlook;
     $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO; 
     
     $lineTot = 1;
     	     
     for ($x = 0; $x <= count($fileArr['details']); $x++) {
          $detarry = get_object_vars($fileArr['details'][$x]);
          
          /******************
          *   ORDER DETAILS
          *****************/
          $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
        
          if(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$detarry['lineTax'])) == '0.00' ) {
                  $vrat = '00.00';
          } else {
                  $vrat = '15.00';           	
          }
          if(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$detarry['orderQty'])) > 0) {
          	
               $postingOrdersHoldingTO->depotLookupRef = trim($detarry['warehouse']);
          	   
               $qtyVal        = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$detarry['orderQty']));
               $uPValue       = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$detarry['price']));
               $nettValue     =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$detarry['price']));
               $postingOrdersHoldingDetailTO->clientLineNo  =  $lineTot;
               $postingOrdersHoldingDetailTO->listPrice     =  $uPValue;
               $postingOrdersHoldingDetailTO->quantity      =  $qtyVal;
               $postingOrdersHoldingDetailTO->discountValue =  '';
               $postingOrdersHoldingDetailTO->nettPrice     =  $nettValue;
               $postingOrdersHoldingDetailTO->extPrice      =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$detarry['lineTotal']));
               $postingOrdersHoldingDetailTO->vatRate       =  $vrat;
               $postingOrdersHoldingDetailTO->vatAmount     =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$detarry['lineTax']));
               $postingOrdersHoldingDetailTO->totalPrice    =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x2c]/', '',$detarry['lineTotalInclTax']));
               $postingOrdersHoldingDetailTO->productCode   =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$detarry['stockCode']));
               $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
               $postingOrdersHoldingDetailTO->productName   =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$detarry['stockDescription']));
               $postingOrdersHoldingDetailTO->productGTIN   =  trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$detarry['pickingCode']));
                           
               $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
               
               $lineTot++;
               $hasdetail = 'Y';
               
          }
     }       
           $arrTO[] = $postingOrdersHoldingTO;  
     
           if($hasdetail =='Y') {
               $eTO->type = FLAG_ERRORTO_SUCCESS;
               $eTO->description = "Successful";
               $eTO->object = $arrTO;
               return $eTO;
           } else {
               $eTO->type = FLAG_ERRORTO_ERROR;
               $eTO->description = "Invalid File or no Details";
               $eTO->identifier = ET_CUSTOMER;
                return $eTO;
           }
    
     
   } 
// --------------------------------------------------------------------------------------------------------------------------------       

   function adaptorTOH_ROLLINGCHICKENDOCS($content, $onlineFileProcessItem) {
         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
          global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
         $eTO = new ErrorTO;
   
         $fileArr = explode("\n",$content);
         $headerarray=array('Header'); 
         
         include_once ($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");
      
         if (in_array(trim(substr($fileArr[0],1,6)), $headerarray))   {
                // validfirst line of file
              $count = 0;  
                
              foreach ($fileArr as $key=>$line) {
         	            // convert line to CSV
                      $lineArr=str_getcsv($line, ",", '"', "\\");
                      
                      if (trim($lineArr[0] ==  "Header"))   {
                           // Check for incoming duplicate Numbers
                           
                           $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
                           $seDup = $MaintenanceDAO->checkForDuplicates('380', trim($lineArr[1]));
                           
                           if(count($seDup) > 0) {
                           	     $dupDocumentNumber = 'Y';  
                           } else {
                                 $dupDocumentNumber = 'N'; 	
                           } 
                      }
                      if($dupDocumentNumber == 'N') {
                           if (trim($lineArr[0] ==  "Header"))   {
                                if (isset($postingOrdersHoldingTO)) {
                                    if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
                                         $eTO->type = FLAG_ERRORTO_ERROR;
                                         $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
                                         $eTO->identifier = ET_CUSTOMER;
                                         return $eTO;
                                    }
                                    $arrTO[] = $postingOrdersHoldingTO;
                                    unset($postingOrdersHoldingTO);
                                 }
                                 $count++;
                                 $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                                 $postingOrdersHoldingTO->updateProduct="N";
                                 $postingOrdersHoldingTO->insertProduct="N";
                                 $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                                 $postingOrdersHoldingTO->principalUid     = 380 ;
                                 $postingOrdersHoldingTO->vendorUid        = $onlineFileProcessItem["vendor_uid"];
                                 $postingOrdersHoldingTO->captureDate      = CommonUtils::getGMTime(0);
                                 $postingOrdersHoldingTO->capturedBy       = 'CHICKEN'; // dont change this as notifications run off it
                                 $postingOrdersHoldingTO->incomingFile     = basename($onlineFileProcessItem["file_being_processed"]);
                                 $postingOrdersHoldingTO->dataSource       = DS_EDI;
                                 $postingOrdersHoldingTO->documentNo       = trim($lineArr[1]);        //use provided doc no
                                
                                 $postingOrdersHoldingTO->clientDocumentNo = trim($lineArr[1]);        //use provided doc no
                                 $postingOrdersHoldingTO->reference        = trim($lineArr[7]); //PO NUMBER.
                                 $postingOrdersHoldingTO->vendorReference  = '';
                                 $postingOrdersHoldingTO->oldAccount       = '';
                                 $postingOrdersHoldingTO->debtorsStoreIdentifier = "";
                                 $postingOrdersHoldingTO->salesAgentStoreIdentifier = ''; //same as old acc.
                                 $postingOrdersHoldingTO->chainLookupRef = '2837';  //use generic => EDI pricing is used. no problem, happy days :)
                                 if(trim($lineArr[4]) == 'FLM001' && trim($lineArr[18]) <> '' ) {
                                      $postingOrdersHoldingTO->storeLookupRef   = trim($lineArr[18]);
                                 } elseif(trim($lineArr[4]) == 'FLM009' && trim($lineArr[18]) <> '' ) {
                                      $postingOrdersHoldingTO->storeLookupRef   = trim($lineArr[18]);
                                 } else {
                                      $postingOrdersHoldingTO->storeLookupRef   = trim($lineArr[4]);
                                 }
                                 $postingOrdersHoldingTO->deliverName    = trim($lineArr[13]);
                                 $postingOrdersHoldingTO->shipToName     = $postingOrdersHoldingTO->deliverName;
                         
                                 $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                         
                                 $postingOrdersHoldingTO->orderDate = substr(trim($lineArr[6]), 8, 2) . '/' . substr(trim($lineArr[6]),3, 2) . '/' . substr(trim($lineArr[6]), 0, 2); //format YY-MM-DD, MySQL will accept this.
                                 $postingOrdersHoldingTO->requestedDeliveryDate = substr(trim($lineArr[6]), 8, 2) . '-' . substr(trim($lineArr[6]),3, 2) . '-' . substr(trim($lineArr[6]), 0, 2); 
                                 //check order date. must be a valid date and not 1970-01-01
                                 $ordDate = date('Ymd', strtotime($postingOrdersHoldingTO->orderDate));  //if value malformed will be 1970-01-01 and check below will activate!
                                 if(($postingOrdersHoldingTO->orderDate===false) || !(checkdate(substr($ordDate, 4,2), substr($ordDate,6,2), substr($ordDate, 0,4))) || ($ordDate == '1970-01-01')){
                                      $eTO->type = FLAG_ERRORTO_ERROR;
                                      $eTO->description = "Order date Invalid format or empty";
                                      $eTO->identifier = ET_CUSTOMER;
                                      return $eTO;
                                 }
                                 
                                 $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
                                 $seDup = $MaintenanceDAO->addToDocControl('380', trim($lineArr[1]), basename($onlineFileProcessItem["file_being_processed"]));
                           }    
                         
                           if (trim($lineArr[0]) ==  "Detail" )   {
                                 /*******************
                                     *   ORDER DETAILS
                                 *******************/
                                 $count++;
                                 
                                 $postingOrdersHoldingTO->depotLookupRef = '';
                          
                                 $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();  // should not be necessary to check document type as other types should end up as zeros
                                 $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                                 $postingOrdersHoldingDetailTO->pallets             = 0;
                                 $postingOrdersHoldingDetailTO->clientPageNo        = '';
                                 $postingOrdersHoldingDetailTO->clientLineNo        = ''; 
                                 $postingOrdersHoldingDetailTO->productCode         = trim($lineArr[9]) ;
                                 $postingOrdersHoldingDetailTO->productName         = trim($lineArr[10]);
                                 $postingOrdersHoldingDetailTO->itemspercase        = ''; 
                                 $postingOrdersHoldingDetailTO->discountValue       = '';
                                 $postingOrdersHoldingDetailTO->discountReference   = '';
                                 $postingOrdersHoldingDetailTO->quantity            = trim($lineArr[2]);
                                 $postingOrdersHoldingDetailTO->listPrice           = trim($lineArr[3]);
                                 if(trim($lineArr[4])-trim($lineArr[3]) == 0) {$vrat = '0.00';} else {$vrat = VAL_VAT_RATE_TBLSTD;}
                                 $postingOrdersHoldingDetailTO->nettPrice           = $postingOrdersHoldingDetailTO->listPrice;
                                 $postingOrdersHoldingDetailTO->extPrice            = ($postingOrdersHoldingDetailTO->nettPrice * $postingOrdersHoldingDetailTO->quantity);
                                 $postingOrdersHoldingDetailTO->vatRate             = $vrat;
                                 $postingOrdersHoldingDetailTO->vatAmount           = round($postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100 ,2);
                                 $postingOrdersHoldingDetailTO->totalPrice          = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;
                                 $postingOrdersHoldingTO->detailArr[]               = $postingOrdersHoldingDetailTO;
                           }
                      } else {
                      	if (trim($lineArr[0] ==  "Header"))   {
                            echo "<br>";
                            echo "Duplicate " . trim($lineArr[1]) . "  - Not Processed" ;
                          	echo "<br>";
                        }
                      }
              }             
               
              $arrTO[] = $postingOrdersHoldingTO; //add final order, no need to check if has detail as we do that in the begin.
              if($count > 0)  {
                     $eTO->type = FLAG_ERRORTO_SUCCESS;
                     $eTO->description = "Successful";
                     $eTO->object = $arrTO;
                     return $eTO;
              } else {
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "No Orders in batch - Ignored";
                  return $eTO;
              }      
                     
         } else {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "First line expected to be a header!";
            return $eTO;
         }
   } 
// --------------------------------------------------------------------------------------------------------------------------------       

    // Hasty Tasty - New Smollan XML
    function adaptorTOH_HASTYTASTYSMOL($content, $onlineFileProcessItem) {

         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
         global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
         $eTO = new ErrorTO;
         if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
         
         $fileArr = $importDAO->getXMLorderDataXML();
//         preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))
         $linecount = 0;
         $loop = 'Header';
         $oAccNo = "";

       
         foreach ($fileArr as $line) {
             $linecount++;
             if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  != 'order_file' && trim($line['UID']) == 1) {
                   $eTO->type = FLAG_ERRORTO_ERROR;
                   $eTO->description = "First line expected to be order_file!";
                   return $eTO;	
             }         	        

//             echo trim($line['UID']) . ' =  ' . $linecount . "    -    " .  trim($line['F1']) . "    -    " .  $line['F2'];
//             echo "<br>";

             if($loop == 'Header') {
                  if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == 'transaction_no' &&  $linecount == trim($line['UID']))  {
                       /*******************
                      ORDER HEAD
                      *******************/
                       $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                       $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                       $postingOrdersHoldingTO->principalUid = 71;
                       $postingOrdersHoldingTO->updateProduct="N";
                       $postingOrdersHoldingTO->insertProduct="N";
                       $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                       $postingOrdersHoldingTO->dataSource = DS_EDI;
                       $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                       $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                       $postingOrdersHoldingTO->capturedBy = 'Smollan';
                       $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                       $postingOrdersHoldingTO->enforceSameDepot = "N";
                       $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                  }	        
                 if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == 'branch_no' &&  $linecount == trim($line['UID']))  {
                       $postingOrdersHoldingTO->oldAccount = trim($line['F2']);
                       $oAccNo = trim($line['F2']);
                 }
                 if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == 'customer_pono' &&  $linecount == trim($line['UID']))  {
                       $postingOrdersHoldingTO->reference = trim($line['F2']);
                 }              	
                 if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == 'order_date' &&  $linecount == trim($line['UID']))  {              	
                      $odate = substr(trim($line['F2']),8,4) . '-' . substr(trim($line['F2']),3,2) . '-' . substr(trim($line['F2']),0,2);                      
                      $postingOrdersHoldingTO->orderDate = $odate;
                      if ($postingOrdersHoldingTO->orderDate === false) {
                           $eTO->type = FLAG_ERRORTO_ERROR;
                           $eTO->description = "Order date invalid format or empty";
                           $eTO->identifier = ET_CUSTOMER;
                           return $eTO;
                      }
                 }
                 if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == 'required_date' &&  $linecount == trim($line['UID']))  {              	
                       $postingOrdersHoldingTO->requestedDeliveryDate = ""; 
                 }
                 if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == 'comment1' &&  $linecount == trim($line['UID']))  {              	
                       $postingOrdersHoldingTO->deliveryInstructions = trim($line['F2']);
                 }	



                 if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == 'comment2' &&  $linecount == trim($line['UID']))  {              	
                       $postingOrdersHoldingTO->shipToName  = trim($line['F2']) . ' - ' . $oAccNo; 
                       $postingOrdersHoldingTO->deliverName = trim($line['F2']) . ' - ' . $oAccNo;
                       $postingOrdersHoldingTO->deliverAdd1 = '';
                       $postingOrdersHoldingTO->deliverAdd2 = '';
                       $postingOrdersHoldingTO->deliverAdd3 = '';
                       $count++;  
           	     }
                 if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == '/order_hdr' &&  $linecount == trim($line['UID']))  {
                       $loop = 'Detail';  	
                 }
             }   
  
             if($loop == 'Detail') {
                    if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == 'line_no' &&  $linecount == trim($line['UID'])) {   
                           $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                           $postingOrdersHoldingDetailTO->clientLineNo = trim($line['F2']);
                           $postingOrdersHoldingDetailTO->clientPageNo = trim($line['F2']);
                           $hasDetail = trim($line['F1']);
                    }	
                    if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == 'supplier_product_code' &&  $linecount == trim($line['UID']))  {   
                          $postingOrdersHoldingDetailTO->productCode = trim($line['F2']);	
                    }	
                    if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == 'product_description' &&  $linecount == trim($line['UID']))  { 
                          $postingOrdersHoldingDetailTO->productName = trim($line['F2']);
                    }
                    if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == 'quantity' &&  $linecount == trim($line['UID']))  { 
                          $postingOrdersHoldingDetailTO->quantity = trim($line['F2']);
                          $postingOrdersHoldingDetailTO->listPrice      = '';
                          $postingOrdersHoldingDetailTO->discountValue = '';
                          $postingOrdersHoldingDetailTO->nettPrice      = '';
                          $postingOrdersHoldingDetailTO->extPrice       = '';
                          $postingOrdersHoldingDetailTO->vatRate        = '';
                          $postingOrdersHoldingDetailTO->vatAmount      = '';
                          $postingOrdersHoldingDetailTO->totalPrice     = ''; 
                          $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                          $postingOrdersHoldingDetailTO->pallets = 0;                                                 
                          $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

                    }	
                    if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == '/order_det' &&  $linecount == trim($line['UID'])) {                   

                    } 
                    if(preg_replace('/[\xEF\xBB\xBF]/', '', trim($line['F1']))  == '/order' &&  $linecount == trim($line['UID'])) {                    	
                          if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
                          	
                              $eTO->type = FLAG_ERRORTO_ERROR;
                              $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
                              $eTO->identifier = ET_CUSTOMER;
                              return $eTO;
                          }
                          $arrTO[] = $postingOrdersHoldingTO;
                          $loop = 'Header';
                    }                 
             }
         }         
         if($count > 0)  {
                 $eTO->type = FLAG_ERRORTO_SUCCESS;
                 $eTO->description = "Successful";
                 $eTO->object = $arrTO;
                 return $eTO;
         } 
    }     
// --------------------------------------------------------------------------------------------------------------------------------
// ********************************************************************************************************************************

    function adaptorTOH_SHOPIFY($content, $onlineFileProcessItem) {
      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO=new StoreDAO($this->dbConn);

      $fileArr = explode("\n",$content);

      // put into common TO
      $arrTO=array();
      $processingLine=0;
      
      $fileArr = explode("\n",$content);
      
      $validfile = 0;
      
//    ﻿"Name","Email","Financial status","Paid at","Fulfillment status","Fulfilled at","Currency","Subtotal","Shipping","Taxes","Total","Discount Code","Shipping Method","Created at","Lineitem quantity","Lineitem name","Lineitem price","Lineitem compare at price","Lineitem discount","Lineitem sku","Lineitem requires shipping","Lineitem taxable","Lineitem fulfillment status","Shipping Name","Shipping Street","Shipping Address1","Shipping Address2","Shipping Company","Shipping City","Shipping Zip","Shipping Province","Shipping Country","Shipping Phone","Cancelled at","Payment Method","Location","Device","ID","Tags","Source","Phone"
//    "Name","Email","Financial status","Paid at","Fulfillment status","Fulfilled at","Currency","Subtotal","Shipping","Taxes","Total","Discount Code","Shipping Method","Created at","Lineitem quantity","Lineitem name","Lineitem price","Lineitem compare at price","Lineitem discount","Lineitem sku","Lineitem requires shipping","Lineitem taxable","Lineitem fulfillment status","Shipping Name","Shipping Street","Shipping Address1","Shipping Address2","Shipping Company","Shipping City","Shipping Zip","Shipping Province","Shipping Country","Shipping Phone","Cancelled at","Payment Method","Location","Device","ID","Tags","Source","Phone"

         // validate first line of file
         $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");
//         echo "<pre>";
//         print_r($lineArr);
         
         if(preg_replace('/[\xEF\xBB\xBF\x22]/', '', $lineArr[0])=='Name') {
              for ($x = 0; $x <= 50; $x++) {        	
                   if(preg_replace('/[\xEF\xBB\xBF\x22]/', '', $lineArr[$x])=='Name') {
                       $Name = $x;
                       $validfile++;
//                       echo preg_replace('/[\xEF\xBB\xBF\x22]/', '', $lineArr[$x]);
                   } elseif(trim($lineArr[$x])=='Email') {
                       $Channel = $x;
                       $validfile++;    
                   } elseif(trim($lineArr[$x])=='Financial status') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Paid at') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Fulfillment status') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Fulfilled at') {
                       $Channel = $x;
                       $validfile++;    
                   } elseif(trim($lineArr[$x])=='Currency') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Subtotal') {
                       $subTotal = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Shipping') {
                       $Shipping = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Taxes') {
                       $Channel = $x;
                       $validfile++;    
                   } elseif(trim($lineArr[$x])=='Total') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Discount Code') {
                       $disCode = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Shipping Method') {
                       $shipMethod = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Created at') {
                       $order_date = $x;
                       $validfile++;    
                   } elseif(trim($lineArr[$x])=='Lineitem quantity') {
                       $qty = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Lineitem name') {
                       $productDisc = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Lineitem price') {
                       $sp = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Lineitem compare at price') {
                       $Channel = $x;
                       $validfile++;    
                   } elseif(trim($lineArr[$x])=='Lineitem discount') {
                       $discount = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Lineitem sku') {
                       $bc = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Lineitem requires shipping') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Lineitem taxable') {
                       $Channel = $x;
                       $validfile++;    
                   } elseif(trim($lineArr[$x])=='Lineitem fulfillment status') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Shipping Name') {
                       $StoreName = $x;
                       $validfile++;
                   } elseif(trim($lineArr[$x])=='Shipping Street') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Shipping Address1') {
                       $deliverAdd1 = $x;
                       $validfile++;    
                   } elseif(trim($lineArr[$x])=='Shipping Address2') {
                       $deliverAdd2 = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Shipping Company') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Shipping City') {
                       $deliverAdd3 = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Shipping Zip') {
                       $deliverAdd_pc = $x;
                       $validfile++;    
                   } elseif(trim($lineArr[$x])=='Shipping Province') {
                       $wareHouse = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Shipping Country') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Shipping Phone') {
                       $Shipping_Phone = $x;
                       $validfile++;
                   } elseif(trim($lineArr[$x])=='Billing Name') {
                       $bill_name = $x;
                       $validfile++;
                   } elseif(trim($lineArr[$x])=='Billing Address1') {
                       $bill_add1 = $x;
                       $validfile++;
                   } elseif(trim($lineArr[$x])=='Billing City') {
                       $bill_add3 = $x;
                       $validfile++;
                   } elseif(trim($lineArr[$x])=='Billing Zip') {
                       $bill_zip = $x;
                       $validfile++;
                   } elseif(trim($lineArr[$x])=='Billing Province') {
                       $bill_Prov = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Cancelled at') {
                       $Channel = $x;
                       $validfile++;    
                   } elseif(trim($lineArr[$x])=='Payment Method') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Location') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='ID') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Tags') {
                       $Channel = $x;
                       $validfile++;    
                   } elseif(trim($lineArr[$x])=='Source') {
                       $Channel = $x;
                       $validfile++;                       
                   } elseif(trim($lineArr[$x])=='Phone') {
                       $Channel = $x;
                       $validfile++;                       
                   }                         
              }
              
              echo $validfile;              
              if($validfile <> 45 ) {
                      $eTO->type = FLAG_ERRORTO_ERROR;
                      $eTO->description = "Check file - Order fields missing!";
                      return $eTO;
              }          
              if(count($lineArr) < 40 ) {
                   $eTO->type = FLAG_ERRORTO_ERROR;
                   $eTO->description = "Invalid Column Count - expecting 40 columns Found " . count($lineArr);
                   return $eTO;
              }          
              unset($fileArr[0]);
         } else {
                   $eTO->type = FLAG_ERRORTO_ERROR;
                   $eTO->description = "First line expected to be a header!";
                   return $eTO;
         }
         $neworder    = '';
         $hasdetail   = 'N';
         $addShipping = 'N';
         $priceTotal  = 0;
          
         foreach ($fileArr as $key=>$line) {
               // convert line to CSV
               $lineArr=str_getcsv($line, ",", '"', "\\");
               if ($neworder <> preg_replace('/[\xEF\xBB\xBF\x22]/', '', $lineArr[$Name])) {
                    $neworder = preg_replace('/[\xEF\xBB\xBF\x22]/', '', $lineArr[$Name]);
                    
                    if($hasdetail == 'Y' && $addShipping == 'Y' ) {
                    	    $clientLineNo++;
                          $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                          $postingOrdersHoldingDetailTO->clientLineNo =  $clientLineNo;
                          $postingOrdersHoldingDetailTO->listPrice     = $shippingCharge * (100/(100+$postingOrdersHoldingDetailTO->vatRate));
                          
                          $postingOrdersHoldingDetailTO->quantity      = 1;
                          $postingOrdersHoldingDetailTO->discountValue = 0;
                          $postingOrdersHoldingDetailTO->nettPrice     = round($shippingCharge * (100/(100+VAL_VAT_RATE_TBLSTD)),4);  ;
                          $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->quantity * $postingOrdersHoldingDetailTO->nettPrice ;
                          $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                          $postingOrdersHoldingDetailTO->vatAmount     = $postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100 ;
                          $postingOrdersHoldingDetailTO->totalPrice    = $shippingCharge;
                          $postingOrdersHoldingDetailTO->productCode   = '2720000';
                          $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                          $postingOrdersHoldingDetailTO->pallets       = 0;
                          $postingOrdersHoldingDetailTO->productName   = 'Delivery Fees';
                          $postingOrdersHoldingDetailTO->overridePriceType = "2" ;
                          $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
                    }
                    $clientLineNo = 0;
                    $hasdetail   = 'N';
                    
                    // Look up discount code
                    $foundDiscount = 'F';
                    $storedSubTot = $lineArr[$subTotal];
                    if($lineArr[$disCode] <> '') {
                          include_once($ROOT.$PHPFOLDER.'DAO/CustomDAO.php');
                          $ManageDiscounts = new CustomDAO($this->dbConn);
                          $discountRec     = $ManageDiscounts->getOneDiscountRecordByCode($lineArr[$disCode]);    
                          if(count($discountRec) > 0 ) {
                              $disValue = $discountRec[0]['amount'];
                              $disType  = $discountRec[0]['type'];
                              $foundDiscount = 'T';
                          } else {
                              $disValue = 0;
                              $disType  = '';
                              $foundDiscount = 'F'; 	
                          }
                    } else {
                    	  $foundDiscount = 'F';
                        $disValue = 0;
                        $disType  = '';                    	
                    }
                    /*******************
                     *   ORDER HEAD
                     *******************/
                    $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                    $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                    $postingOrdersHoldingTO->principalUid = 216;
                    $postingOrdersHoldingTO->updateProduct="N";
                    $postingOrdersHoldingTO->insertProduct="N";
                    $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                    $postingOrdersHoldingTO->dataSource = DS_WEB;
                    $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                    $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                    $postingOrdersHoldingTO->requestedDeliveryDate = "";
                    $postingOrdersHoldingTO->capturedBy = "Shopify";
                    $postingOrdersHoldingTO->deliveryInstructions = '';
                    $postingOrdersHoldingTO->additionalDetails = '';
                    $collOrder = 'N';

                    if($lineArr[$shipMethod] == 'Standard Rate' || $lineArr[$shipMethod] == 'Local Delivery') {
                        if(in_array($lineArr[$wareHouse], array('Western Cape'))) {
                             $deplook = 'CPT' ;
                        } elseif(in_array($lineArr[$wareHouse], array('Gauteng','Free State','North West','Limpopo'))) {
                             $deplook = 'LER' ;
                        } elseif(in_array($lineArr[$wareHouse], array('KwaZulu-Natal'))) {
                             $deplook = 'BEV';
                        } else {
                             $deplook = 'CPT';                    	
                        }
                    } else {
                    	 $collOrder = 'Y';
 
                    	 if($lineArr[$shipMethod] == 'Cape Town') {
                            $deplook = 'CPT';
                    	 } elseif ($lineArr[$shipMethod] == 'KZN') {
                    	 	    $deplook = 'BEV';
                    	 } elseif ($lineArr[$shipMethod] == 'JHB') {
                    	 	    $deplook = 'LER';
                    	 } else {
                    	 	    $deplook = 'CPT';
                    	 }
                    }
                    echo "<br>";
                    echo $shipMethod;
                    echo "<br>";
                    echo $deplook;
                    $postingOrdersHoldingTO->depotLookupRef = $deplook;
                    $postingOrdersHoldingTO->orderDate =  substr($lineArr[$order_date],6,4) . "-" . substr($lineArr[$order_date],3,2) . "-" . substr($lineArr[$order_date],0,2);
               
                    if ($postingOrdersHoldingTO->orderDate === false) {
                             $eTO->type = FLAG_ERRORTO_ERROR;
                             $eTO->description = "Order date invalid format or empty";
                             $eTO->identifier = ET_CUSTOMER;
                             return $eTO;
                    }
                    if($collOrder == 'N') {
                         $postingOrdersHoldingTO->shipToName = trim($lineArr[$StoreName]);
                         $postingOrdersHoldingTO->deliverName = trim($lineArr[$StoreName]);
                    } else {
                         $postingOrdersHoldingTO->shipToName = trim($lineArr[$bill_name]);
                         $postingOrdersHoldingTO->deliverName = trim($lineArr[$bill_name]);
                    }     
                    $postingOrdersHoldingTO->clientDocumentNo = trim($lineArr[$Name]);
                    $postingOrdersHoldingTO->documentNo = '';
        	          $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                    $postingOrdersHoldingTO->storeLookupRef = "" ; 
                    $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = "Y";
                    $postingOrdersHoldingTO->enforceSameDepot = "N";
                    $postingOrdersHoldingTO->chainLookupRef = 'SC';
                    $postingOrdersHoldingTO->oldAccount = '';
                    $postingOrdersHoldingTO->salesAgentStoreIdentifier = "";
                    $postingOrdersHoldingTO->reference = substr(trim($lineArr[$Name]),0,19);
                    
                    $newStore = trim($lineArr[$id]) . trim($lineArr[$FormID]);
                    
                    $arrTO[] = $postingOrdersHoldingTO;
                                   
                    // Create the StoreTO
                    $postingStoreTO = new PostingStoreTO;
                    $postingStoreTO->DMLType = "INSERT";
                    $postingStoreTO->principalStoreUId = "";
                    $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                    if($collOrder == 'N') {
                         $postingStoreTO->deliverName = trim($lineArr[$StoreName]);
                         $postingStoreTO->deliverAdd1 = trim($lineArr[$deliverAdd1]);
                         $postingStoreTO->deliverAdd2 = trim($lineArr[$deliverAdd2]);
                         $postingStoreTO->deliverAdd3 = trim($lineArr[$deliverAdd2]) . '   ' . trim($lineArr[$deliverAdd_pc]);                    	
                    } else {
                         $postingStoreTO->deliverName = trim($lineArr[$bill_name]);
                         $postingStoreTO->deliverAdd1 = trim($lineArr[$bill_add1]);
                         $postingStoreTO->deliverAdd2 = trim($lineArr[$bill_add3]);
                         $postingStoreTO->deliverAdd3 = trim($lineArr[$bill_Prov]) . '   ' . trim($lineArr[$bill_zip]);                    	
                    }
                    $postingStoreTO->billName = $postingStoreTO->deliverName;
                    $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
                    $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
                    $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
                    $postingStoreTO->vatNumber = ''; 
                    $postingStoreTO->telNo1    = trim($lineArr[$Shipping_Phone]);
                    $postingStoreTO->depot = ""; // this will be set by the processing script
                    $postingStoreTO->deliveryDay = "8";
                    $postingStoreTO->noVAT = 0;
                    $postingStoreTO->onHold = "0";
                    $postingStoreTO->emailAdd = trim($lineArr[$Shipping_Phone]);
                    $postingStoreTO->chain = ""; // this will be set by the processing script
                    $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                    $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                    $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                    $postingStoreTO->ownedBy = '';
                    $postingStoreTO->oldAccount = '';
                    
                    $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
                    
                    $postingSpecialFieldTO = new PostingSpecialFieldTO;
                    $postingSpecialFieldTO->DMLType = "INSERT";
                    $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                    $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                    $postingSpecialFieldTO->fielduid = 253;   // Store Special Field
                    $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                    $postingSpecialFieldTO->value = "ITDELI";
                    $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;
                    
                    // End Create the StoreTO
                    
                    // Check for shipping charge
                    
                    if(preg_replace('/[\xEF\xBB\xBF\x22]/', '', $lineArr[$Shipping] > 0)) {
                         $addShipping = 'Y';
                         $shippingCharge = $lineArr[$Shipping];
                    } else {
                         $addShipping = 'N';
                         $shippingCharge = 0;
                    }
               }
               $clientLineNo++;
               
               /******************
               *   ORDER DETAILS
               *****************/
               
               $lPrice = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x20]/', '',$lineArr[$sp])) * 100 / (100+VAL_VAT_RATE_TBLSTD);
          
               $priceTotal = $priceTotal + ($lineArr[$sp] * $lineArr[$qty]);
          
               $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
               $postingOrdersHoldingDetailTO->clientLineNo =  $clientLineNo;
               $postingOrdersHoldingDetailTO->listPrice     = $lPrice;
               $postingOrdersHoldingDetailTO->quantity      = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x20]/', '',$lineArr[$qty]));
               $postingOrdersHoldingDetailTO->discountValue = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x20]/', '',$lineArr[$discount]));
               $postingOrdersHoldingDetailTO->nettPrice     = $postingOrdersHoldingDetailTO->listPrice - ($postingOrdersHoldingDetailTO->discountValue);
               $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->quantity * $postingOrdersHoldingDetailTO->nettPrice ;
               $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
               $postingOrdersHoldingDetailTO->vatAmount     = $postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100 ;
               $postingOrdersHoldingDetailTO->totalPrice    = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;
               
               $fileProd = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$bc]));
               if($fileProd == '12307796') {
                   $pCode = '12392614';
               } elseif($fileProd == '601AO18500BL') {
                   $pCode = '601AO20500CFR';
               } elseif($fileProd == 'DD002BLUE-UNIT') {
                   $pCode = 'DD002BLUEUNIT';
               } elseif($fileProd == 'FAG0400NUNIT') {
                   $pCode = 'BOR400-UNIT';
               } elseif($fileProd == 'LOA003UNIT') {
                   $pCode = 'LOA003-UNIT';                   
               } elseif($fileProd == 'RRCHILLI250ML-UNIT') {
                   $pCode = 'RRCANOLA-UNIT';                   
               } elseif ($fileProd == 'SCH002UNIT') {
                   $pCode = 'SCH002-UNIT';
               } elseif($fileProd == 'TRI024X400E') {
                   $pCode = 'CHOPP400-UNIT';
               } elseif($fileProd == 'TRI0400EUNIT') {
                   $pCode = 'CHOPP400-UNIT';
               } else {
                   $pCode = $fileProd;
               }
               $postingOrdersHoldingDetailTO->productCode   = $pCode ;
               $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
               $postingOrdersHoldingDetailTO->pallets       = 0;
               $postingOrdersHoldingDetailTO->productName   = trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '',$lineArr[$productDisc]));;
               $postingOrdersHoldingDetailTO->overridePriceType = "2" ;

               $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

               $hasdetail = 'Y';
         } // end of file loop
         
         // process the final loop to db
         
         if($foundDiscount == 'T') {
                 $postingOrdersHoldingTO->offInvoiceDiscount = $disValue;
                 $postingOrdersHoldingTO->offInvoiceDiscountType = $disType;         
         } elseif($foundDiscount == 'F' && $storedSubTot < $priceTotal ) {
                 $disValue = round($priceTotal - $storedSubTot,2);
                 $postingOrdersHoldingTO->offInvoiceDiscount = $disValue;
                 $postingOrdersHoldingTO->offInvoiceDiscountType = 'A'; 
         } else {
                 $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                 $postingOrdersHoldingTO->offInvoiceDiscountType = '';
         }
         if($hasdetail == 'Y' && $addShipping == 'Y' ) {
               $clientLineNo++;
               $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
               $postingOrdersHoldingDetailTO->clientLineNo =  $clientLineNo;
               $postingOrdersHoldingDetailTO->listPrice     = round($shippingCharge * (100/(100+VAL_VAT_RATE_TBLSTD)),4);           
               $postingOrdersHoldingDetailTO->quantity      = 1;
               $postingOrdersHoldingDetailTO->discountValue = 0;
               $postingOrdersHoldingDetailTO->nettPrice     = $postingOrdersHoldingDetailTO->listPrice;
               $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->quantity * $postingOrdersHoldingDetailTO->nettPrice ;
               $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
               $postingOrdersHoldingDetailTO->vatAmount     = $postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100 ;
               $postingOrdersHoldingDetailTO->totalPrice    = $shippingCharge;
               $postingOrdersHoldingDetailTO->productCode   = '2720000';
               $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
               $postingOrdersHoldingDetailTO->pallets       = 0;
               $postingOrdersHoldingDetailTO->productName   = 'Delivery Fees';
               $postingOrdersHoldingDetailTO->overridePriceType = "2" ;
               $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
         }
         
//         $arrTO[] = $postingOrdersHoldingTO;
//         unset($postingOrdersHoldingTO);
   
         $eTO->type = FLAG_ERRORTO_SUCCESS;
         $eTO->description = "Successful";
         $eTO->object = $arrTO;
         return $eTO;

    }
// ********************************************************************************************************************************
    function adaptorTOH_RHODES_SYKNAMO($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;
      
      $headerarray=array('Comment');

      $fileArr = explode("\n",$content);
      
      $validfile = 0;
//      echo count($fileArr);
      
      if (in_array(trim(substr($fileArr[0],0,7)), $headerarray))   {
         // validate first line of file
         $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");
                  
         for ($x = 0; $x <= 24; $x++) {
         	    if(trim($lineArr[$x])=='Customer code') {    //1
         	       $Customer_code_offset = $x;
         	       $validfile++;
              } elseif(trim($lineArr[$x])=='ID') {         //2
                 $id = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Comment') {    //3
                 $Comment = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Date') {       //4
                 $order_date = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Currency') {   //5
                 $currency = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Exchange rate') {    //6
                 $exchange_rate = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Product code') {     //7
                 $product_code = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Principle') {        //x
                 $PrincipalId = $x;
                 $validfile++;                  
              } elseif(trim($lineArr[$x])=='Quantity') {         //9
                 $quantity = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Discount') {         //10
                 $discount = $x;
                 $validfile++;                
              } elseif(trim($lineArr[$x])=='Reference') {        //11
                 $reference = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='User') {             //12
                 $skyuser = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Customer name') {    //13
                 $Customer_name = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='List price') {       //14
                 $List_price = $x;
                 $validfile++;	
              } elseif(trim($lineArr[$x])=='Price') {            //15
                 $Price = $x;
                 $validfile++;	
              } elseif(trim($lineArr[$x])=='Price list') {       //16
                 $Price_list = $x;
                 $validfile++;	
              } elseif(trim($lineArr[$x])=='Prices include tax') {   //17
                 $Prices_include_tax = $x;
                 $validfile++;	
              } elseif(trim($lineArr[$x])=='Product name') {        //18
                 $Product_name = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Quote') {                //19
                 $Quote = $x;
                 $validfile++;	
              } elseif(trim($lineArr[$x])=='RecipientsXX') {         //20
                 $Recipients = $x;
                 $validfile++;	
              } elseif(trim($lineArr[$x])=='SignatureXX') {          //21
                 $Signature = $x;
                 $validfile++;	
              } elseif(trim($lineArr[$x])=='Tax') {                  //22
                 $Tax = $x;
                 $validfile++;	
              } elseif(trim($lineArr[$x])=='Unit name') {            //23
                 $Unit_name = $x;
                 $validfile++;	
              } elseif(trim($lineArr[$x])=='Unit price') {           // 24
                 $Unit_price = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Warehouse') {            // 8
                 $Warehouse = $x;
                 $validfile++;
              }   
//              echo $lineArr[$x];
//              echo "<br>";
         }
//         echo "<br>";
//         echo $validfile;
//         echo "<br>";
//         echo 'Line Array' . count($lineArr) ;
//         echo "<br>";
         if($validfile <> 22 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Check file - Order fields missing!";
            return $eTO;
         }          
         if(count($lineArr) <= 19 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Invalid Column Count - expecting 22 columns Found " . count($lineArr);
            return $eTO;
         }          
         unset($fileArr[0]);
      } else {
         $eTO->type = FLAG_ERRORTO_ERROR;
         $eTO->description = "First line expected to be a header!";
         return $eTO;
      }
      
      $hasheader = 'N';
      $hasdetail = 'N';
      $l = 0;
      
      foreach ($fileArr as $key=>$line) {
      	
//      	echo "<br>";
      	
//      	echo substr($fileArr[$l],strpos($fileArr[$l],',',0)+1,7);
      	
      	$l++;
      	
       	  if (strpos($fileArr[$l],',',0) <> FALSE || trim(substr($fileArr[$l],strpos($fileArr[$l],',',0)+1,7)) <> '') {
      	
     	         // convert line to CSV
   	           $lineArr=str_getcsv($line, ",", '"', "\\");
               
//               print_r($lineArr);
       
               if (  $hasheader == 'N') {
                   // Determine Principal code from first line of order      	
                        $currPrincipal = 389;	
                   /*******************
                   *   ORDER HEAD
                   *******************/
                   $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                   $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                   $postingOrdersHoldingTO->principalUid = $currPrincipal;
                   $postingOrdersHoldingTO->updateProduct="N";
                   $postingOrdersHoldingTO->insertProduct="N";
                   $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                   $postingOrdersHoldingTO->dataSource = DS_EDI;
                   $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                   $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                   $postingOrdersHoldingTO->requestedDeliveryDate = "";
                   $postingOrdersHoldingTO->capturedBy = substr(trim($lineArr[$skyuser]),0,20);
                   $postingOrdersHoldingTO->deliveryInstructions = trim($lineArr[$Comment]);
                   $postingOrdersHoldingTO->depotLookupRef = trim($lineArr[$Warehouse]);    
                   $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                   $postingOrdersHoldingTO->offInvoiceDiscountType = '';       
                   $postingOrdersHoldingTO->orderDate =  $lineArr[$order_date];
                  
                   if ($postingOrdersHoldingTO->orderDate === false) {
                            $eTO->type = FLAG_ERRORTO_ERROR;
                            $eTO->description = "Order date invalid format or empty";
                            $eTO->identifier = ET_CUSTOMER;
                            return $eTO;
                   }
                   $postingOrdersHoldingTO->shipToName = $lineArr[$Customer_name];
                   $postingOrdersHoldingTO->deliverName = $lineArr[$Customer_name];
                   $postingOrdersHoldingTO->clientDocumentNo = trim($lineArr[$id]);
                   $postingOrdersHoldingTO->documentNo = 'RF' . str_pad(trim($lineArr[$id]),6,'0',STR_PAD_LEFT);
                   $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV; 	
                   $postingOrdersHoldingTO->storeLookupRef = $lineArr[$Customer_code_offset];
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                   $postingOrdersHoldingTO->oldAccount = '';
                   
                   if(trim($lineArr[$reference])=='') { $ref = '*'; } else {$ref = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', trim($lineArr[$reference]))),0,19);}
      
                   $postingOrdersHoldingTO->reference = $ref;
                   
                   // Create the StoreTO
                   $postingStoreTO = new PostingStoreTO;
                   $postingStoreTO->DMLType = "INSERT";
                   $postingStoreTO->principalStoreUId = "";
                   $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                   $postingStoreTO->deliverName = $lineArr[$Customer_name];
                   $postingStoreTO->deliverAdd1 = '';
                   $postingStoreTO->deliverAdd2 = '';
                   $postingStoreTO->deliverAdd3 = '';
                   $postingStoreTO->billName = $postingStoreTO->deliverName;
                   $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
                   $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
                   $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
                   $postingStoreTO->vatNumber = ''; 
                   $postingStoreTO->telNo1    = ''; 
                   $postingStoreTO->depot = ""; // this will be set by the processing script
                   $postingStoreTO->deliveryDay = "8";
                   $postingStoreTO->noVAT = 0;
                   $postingStoreTO->onHold = "0";
                   $postingStoreTO->chain = ""; // this will be set by the processing script
                   $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                   $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                   $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->oldAccount = $lineArr[$Customer_code_offset];
       
                   $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
                   
                   
                   // lookup special field(s) - enforce this specific one
                   $postingSpecialFieldTO = new PostingSpecialFieldTO;
                   $postingSpecialFieldTO->DMLType = "INSERT";
                   $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                   $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                   $postingSpecialFieldTO->fielduid = "515"; // Special Field
                   $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                   $postingSpecialFieldTO->value = $lineArr[$Customer_code_offset];
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;
        
                   
                   
                   $hasheader = 'Y';
                   
                    $arrTO[] = $postingOrdersHoldingTO;
               }       
               /******************
               *   ORDER DETAILS
               *****************/
              
               $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
       
               $postingOrdersHoldingDetailTO->listPrice     = $lineArr[$List_price];
               $postingOrdersHoldingDetailTO->quantity      = $lineArr[$quantity];
               $postingOrdersHoldingDetailTO->nettPrice     = $lineArr[$List_price];;
               $postingOrdersHoldingDetailTO->extPrice      = $lineArr[$quantity] * $lineArr[$List_price];
               $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
               $postingOrdersHoldingDetailTO->vatAmount     = $lineArr[$quantity] * $lineArr[$List_price] * VAL_VAT_RATE_TBLSTD /100;
               $postingOrdersHoldingDetailTO->totalPrice    = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;
               $postingOrdersHoldingDetailTO->productCode   = $lineArr[$product_code];
               $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
               $postingOrdersHoldingDetailTO->pallets       = 0;
               $postingOrdersHoldingDetailTO->productName   =  $lineArr[$product_code];;
               $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
               $hasdetail = 'Y';
          }
      }     
      if($hasdetail =='Y') {
           $eTO->type = FLAG_ERRORTO_SUCCESS;
           $eTO->description = "Successful";
           $eTO->object = $arrTO;
           return $eTO;
      } else {
     	    $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid File or no Details";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
      }       
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------       
   //  Vendor 43, Order File 
    // Convert a CSV file with orders or Credits
    
    function adaptorTOH_RANDOM($content, $onlineFileProcessItem) {

         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
         global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

         $eTO = new ErrorTO;
         if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
            $arrTO=array();
             $processingLine=0;
      
             $headerarray=array('PRINCIPAL');

             $fileArr = explode("\n",$content);
      
             $validfile = 0;
             echo count($fileArr);    
             echo "<pre>";
             print_r($fileArr)  ;
             echo "<br>";
             echo strtoupper(trim(substr($fileArr[0],0,9)));
             echo "<br>";
      
             if (in_array(strtoupper(trim(substr($fileArr[0],0,9))), $headerarray))   {
                   // validate first line of file
                    $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");
                  
                    for ($x = 0; $x <= 15; $x++) {
         	              if(strtoupper(trim($lineArr[$x]))=='PRINCIPAL') {
                            $principal = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='SRCH BRANCH CODE') {
                            $branchCode = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='NAME') {
                            $store = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='TYPE') {
                            $Type = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='SRCH REFERENCE') {
                            $srchRef = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='CLAIM DATE') {
                            $clmDate = $x;
                            $validfile++;                  
                        } elseif(strtoupper(trim($lineArr[$x]))=='PRINCIPAL REFERENCE') {
                            $prRef = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='CR INCLUSIVE AMOUNT') {
                            $inclAmount = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='STATUS') {
                            $status = $x;
                           $validfile++;
                        }   
                        echo $lineArr[$x];
                        echo "<br>";
                    }
                    echo "<br>";
                    echo $validfile;
                    echo "<br>";
                    if($validfile <> 9 ) {
                          $eTO->type = FLAG_ERRORTO_ERROR;
                          $eTO->description = "Check file - Order / Claim fields missing!";
                          return $eTO;
                    }          
                    unset($fileArr[0]);
             } else {
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "First line expected to be a header!";
                  return $eTO;
             }
       
             $hasheader = 'N';
             $hasdetail = 'N';
      
             foreach ($fileArr as $key=>$line) {
      	
                   // convert line to CSV
                   $lineArr=str_getcsv($line, ",", '"', "\\");
           
                   print_r($lineArr);
                   
                   if($lineArr[$status] <> 'A'){
                       continue;
                   }

                   if($lineArr[$branchCode] == 'branchCode'){
                       continue;
                   }
                   $tprArr = array('RCLM', 'PCLM' );
                   
                   if(!in_array($lineArr[$Type], $tprArr)) {
                      continue;
                   }

                   // Determine Principal code from first line of file  	
                   $currPrincipal = trim($lineArr[$principal]);	
                   
                   $specialField = array(304 => "536",
                                        386 => "537");
                                        
                   $spField = isset($specialField[$currPrincipal]) ? $specialField[$currPrincipal] : "  ";                           
                   
                   /*******************
                   *   ORDER HEAD
                   *******************/
                   $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                   $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                   $postingOrdersHoldingTO->principalUid = $currPrincipal;
                   $postingOrdersHoldingTO->updateProduct="N";
                   $postingOrdersHoldingTO->insertProduct="N";
                   $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                   $postingOrdersHoldingTO->dataSource = DS_EDI;
                   $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                   $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                   $postingOrdersHoldingTO->requestedDeliveryDate = "";
                   $postingOrdersHoldingTO->capturedBy = "DEBTORS";
                   $postingOrdersHoldingTO->deliveryInstructions = $lineArr[$prRef];;
                   $postingOrdersHoldingTO->salesAgentStoreIdentifier = "SP" . $spField ;
                   $postingOrdersHoldingTO->reference = $lineArr[$srchRef];

                   $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                   $postingOrdersHoldingTO->offInvoiceDiscountType = '';  

                   $postingOrdersHoldingTO->depotLookupRef = "";

                   $postingOrdersHoldingTO->orderDate =  $lineArr[$clmDate];
              
                   if ($postingOrdersHoldingTO->orderDate === false) {
                        $eTO->type = FLAG_ERRORTO_ERROR;
                        $eTO->description = "Order date invalid format or empty";
                        $eTO->identifier = ET_CUSTOMER;
                        return $eTO;
                   }
                   $postingOrdersHoldingTO->shipToName = $lineArr[$Customer_name];
                   $postingOrdersHoldingTO->deliverName = $lineArr[$Customer_name];
                   $postingOrdersHoldingTO->clientDocumentNo = '';
                   $postingOrdersHoldingTO->documentNo = '';
                   $postingOrdersHoldingTO->documentTypeUId = DT_MCREDIT_PRICING; 
                   $postingOrdersHoldingTO->storeLookupRef = str_pad(trim(substr($lineArr[$branchCode],strpos($lineArr[$branchCode],'-')+1,7)),6,'0',STR_PAD_LEFT);
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                   $postingOrdersHoldingTO->oldAccount = '';
               
                   $arrTO[] = $postingOrdersHoldingTO;
                   /******************
                    *   ORDER DETAILS
                    *****************/
          
                   $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                   
                   $np     = round($lineArr[$inclAmount] * 100 / (100 + VAL_VAT_RATE_TBLSTD),2 );
                   $vatAmt = round($lineArr[$inclAmount] - $lineArr[$inclAmount] * 100 / (100 + VAL_VAT_RATE_TBLSTD),2 ); 

                   $postingOrdersHoldingDetailTO->listPrice     = $np ;
                   $postingOrdersHoldingDetailTO->quantity      = 1;
                   $postingOrdersHoldingDetailTO->nettPrice     = $np;
                   $postingOrdersHoldingDetailTO->extPrice      = $np;
                   $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                   $postingOrdersHoldingDetailTO->vatAmount     = $vatAmt;
                   $postingOrdersHoldingDetailTO->totalPrice    = $lineArr[$inclAmount];
                   $postingOrdersHoldingDetailTO->productCode   = 'MC01';
                   $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                   $postingOrdersHoldingDetailTO->pallets       = 0;
                   $postingOrdersHoldingDetailTO->productName   =  '';
 
                   $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
               
                   $hasdetail = 'Y';
             }
             if($hasdetail =='Y') {
                 $eTO->type = FLAG_ERRORTO_SUCCESS;
                 $eTO->description = "Successful";
                 $eTO->object = $arrTO;
                return $eTO;
             } else {
     	           $eTO->type = FLAG_ERRORTO_ERROR;
                 $eTO->description = "Invalid File or no Details";
                 $eTO->identifier = ET_CUSTOMER;
                 return $eTO;
             }       
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------
    function adaptorTOH_NELLWYN($content, $onlineFileProcessItem) {

         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
         global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

         $eTO = new ErrorTO;
         if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
            $arrTO=array();
             $processingLine=0;
             
             // DD NUMBER
      
             $headerarray=array('SALES DOCUMENT', 'SALES DOC.', 'REF', 'SD DOC.');

             $fileArr = explode("\n",$content);
      
             $validfile = 0;
             echo count($fileArr);    
//           echo "<pre>";
//           print_r($fileArr)  ;
//           echo "<br>";
//           echo strtoupper(trim(substr($fileArr[0],0,14)));
//           echo "<br>";

// Sales document,Sold-to party,DD,Name 1,Material,Description,Document Date,Order quantity
// Sales doc.	Sold-to pt	Delivery Document	Sold-to party	Material	Material Number	Document Date	Order Quantity
      
             if (in_array(strtoupper(trim(substr($fileArr[0],0,14))), $headerarray) || in_array(strtoupper(trim(substr($fileArr[0],0,10))), $headerarray) || in_array(strtoupper(trim(substr($fileArr[0],0,3))), $headerarray) || in_array(strtoupper(trim(substr($fileArr[0],0,7))), $headerarray) )   {
                   // validate first line of file
                    $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");
                  
                    for ($x = 0; $x <= 15; $x++) {
         	              if(strtoupper(trim($lineArr[$x]))=='DD # ' || strtoupper(trim($lineArr[$x]))=='DELIVERY DOCUMENT' || strtoupper(trim($lineArr[$x]))=='DD' || strtoupper(trim($lineArr[$x]))=='DELIVERY' || strtoupper(trim($lineArr[$x]))=="DD") {
                            $docNo = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='DOCUMENT DATE' || strtoupper(trim($lineArr[$x]))=='DOC.DATE' || strtoupper(trim($lineArr[$x]))=='ORDER DATE' || strtoupper(trim($lineArr[$x]))=='DOC. DATE') {
                            $oDate = $x;
                            $validfile++;                     
                        } elseif(strtoupper(trim($lineArr[$x]))=='REQ.DLV.DT'   || strtoupper(trim($lineArr[$x]))=='DELIVERY DATE' || strtoupper(trim($lineArr[$x]))=='DEL DATE' || strtoupper(trim($lineArr[$x]))=='DLV.DATE' ) {
                            $divDate = $x;
                            $validfile++;  
                        } elseif(strtoupper(trim($lineArr[$x]))=='SALES DOCUMENT' || strtoupper(trim($lineArr[$x]))=='SALES DOC.' || strtoupper(trim($lineArr[$x]))=='REF' || strtoupper(trim($lineArr[$x]))=='SD DOC.') {
                            $PoNo = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='ORDER QUANTITY' || strtoupper(trim($lineArr[$x]))=='CONFIRMED QTY' || strtoupper(trim($lineArr[$x]))=='CONFIRMED QUANTITY' || strtoupper(trim($lineArr[$x]))=='CONQTY(CS)') {
                            $cases = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='NAME 1' || strtoupper(trim($lineArr[$x]))=='SOLD-TO PARTY' || strtoupper(trim($lineArr[$x]))=='CUSTOMER NAME') {
                            $Customer_name = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='SOLD-TO PT' || strtoupper(trim($lineArr[$x]))=='ACCOUNT' || strtoupper(trim($lineArr[$x]))=='XXSOLD-TO PARTY') {
                            $storeCode = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='PO' || strtoupper(trim($lineArr[$x]))=='CUSTOMER PO#' || strtoupper(trim($lineArr[$x]))=='PURCHASE ORDER NO.' || strtoupper(trim($lineArr[$x]))=='PO NUMBER') {
                            $reference = $x;
                            $validfile++;                                 
                        } elseif(strtoupper(trim($lineArr[$x]))=='MATERIAL' || strtoupper(trim($lineArr[$x]))=='KC CODE') {
                            $prodCode = $x;
                            $validfile++;                  
                        } elseif(strtoupper(trim($lineArr[$x]))=='DESCRIPTION' || strtoupper(trim($lineArr[$x]))=='MATERIAL NUMBER') {
                            $product = $x;
                            $validfile++;
                        } 
                        echo $lineArr[$x];
                        echo "<br>";
                    }
                    echo "<br>";
                    echo $validfile;
                    echo "<br>";
                    if($validfile < 8 ) {
                          $eTO->type = FLAG_ERRORTO_ERROR;
                          $eTO->description = "Check file - Order";
                          return $eTO;
                    }          
                    unset($fileArr[0]);
             } else {
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "First line expected to be a header!";
                  return $eTO;
             }
       
             $hasheader = 'N';
             $hasdetail = 'N';
             
             $currPrincipal = 401;	
      
             foreach ($fileArr as $key=>$line) {
      	
                   // convert line to CSV
                   $lineArr=str_getcsv($line, ",", '"', "\\");
           
                   print_r($lineArr);
                   
                   echo "<br>";
                   echo trim($lineArr[$docNo]);
                    echo "<br>";
                   
                   if(trim($lineArr[$docNo]) == ''){
                       continue;
                   }     
                   
                   /*******************
                   *   ORDER HEAD
                   *******************/
                   $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                   $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                   $postingOrdersHoldingTO->principalUid = $currPrincipal;
                   $postingOrdersHoldingTO->updateProduct="N";
                   $postingOrdersHoldingTO->insertProduct="N";
                   $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                   $postingOrdersHoldingTO->dataSource = DS_EDI;
                   $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                   $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                   $postingOrdersHoldingTO->requestedDeliveryDate = $lineArr[$divDate];
                   $postingOrdersHoldingTO->capturedBy = "KIMBER";
                   $postingOrdersHoldingTO->deliveryInstructions = '';
                   $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                   $postingOrdersHoldingTO->offInvoiceDiscountType = '';                     
                   $postingOrdersHoldingTO->salesAgentStoreIdentifier = 538 ;
                   $postingOrdersHoldingTO->reference =  $lineArr[$reference];

                   $postingOrdersHoldingTO->depotLookupRef = "";
                   
                   if(substr($lineArr[$oDate],0,3) == '202') {
                   	   $postingOrdersHoldingTO->orderDate =  $lineArr[$divDate];
                   } else {
                   	   // 0123456789
                   	   // 11-08-2023
                   	   $postingOrdersHoldingTO->orderDate =  substr($lineArr[$divDate],6,4) . "-" . substr($lineArr[$divDate],3,4) . "-" . substr($lineArr[$divDate],0,2);
                   }
                   
                   if(substr($lineArr[$oDate],0,3) == '202') {
                   	   $postingOrdersHoldingTO->orderDate =  $lineArr[$oDate];
                   } else {
                   	   $postingOrdersHoldingTO->orderDate =  substr($lineArr[$oDate],6,4) . "-" . substr($lineArr[$oDate],3,4) . "-" . substr($lineArr[$oDate],0,2);
                   }
                       
              
                   if ($postingOrdersHoldingTO->orderDate === false) {
                        $eTO->type = FLAG_ERRORTO_ERROR;
                        $eTO->description = "Order date invalid format or empty";
                        $eTO->identifier = ET_CUSTOMER;
                        return $eTO;
                   }
                   $postingOrdersHoldingTO->shipToName = $lineArr[$Customer_name];
                   $postingOrdersHoldingTO->deliverName = $lineArr[$Customer_name];
                   $postingOrdersHoldingTO->clientDocumentNo = $lineArr[$PoNo];
                   $postingOrdersHoldingTO->documentNo = $lineArr[$docNo];
                   $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV; 
                   $postingOrdersHoldingTO->storeLookupRef = $lineArr[$storeCode];
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                   $postingOrdersHoldingTO->oldAccount = '';
               
                   $arrTO[] = $postingOrdersHoldingTO;
                   /******************
                    *   ORDER DETAILS
                    *****************/
          
                   $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

                   $postingOrdersHoldingDetailTO->listPrice     = $np ;
                   $postingOrdersHoldingDetailTO->quantity      = $lineArr[$cases];
                   $postingOrdersHoldingDetailTO->nettPrice     = '';
                   $postingOrdersHoldingDetailTO->extPrice      = '';
                   $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                   $postingOrdersHoldingDetailTO->vatAmount     = '';
                   $postingOrdersHoldingDetailTO->totalPrice    = '';
                   $postingOrdersHoldingDetailTO->productCode   = $lineArr[$prodCode];
                   $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                   $postingOrdersHoldingDetailTO->pallets       = 0;
                   $postingOrdersHoldingDetailTO->productName   =  '';
 
                   $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
               
                   $hasdetail = 'Y';
                   
                   echo $lineArr[$cases];
                   echo "<br>";
                   
             }
             if($hasdetail =='Y') {
                 $eTO->type = FLAG_ERRORTO_SUCCESS;
                 $eTO->description = "Successful";
                 $eTO->object = $arrTO;
                return $eTO;
             } else {
     	           $eTO->type = FLAG_ERRORTO_ERROR;
                 $eTO->description = "Invalid File or no Details";
                 $eTO->identifier = ET_CUSTOMER;
                 return $eTO;
             }       
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------
    function adaptorTOH_DFSA($content, $onlineFileProcessItem) {

         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
         global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

         $eTO = new ErrorTO;
         if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
            $arrTO=array();
             $processingLine=0;
             
             // DD NUMBER
      
             $headerarray=array('ORDER STATUS');

             $fileArr = explode("\n",$content);
      
             $validfile = 0;
             echo count($fileArr);    
 echo "<pre>";
         print_r($fileArr)  ;
          echo "<br>";
          echo strtoupper(trim(substr($fileArr[0],3,12)));
           echo "<br>";
           
           echo "CC";

// Sales document,Sold-to party,DD,Name 1,Material,Description,Document Date,Order quantity
// Sales doc.	Sold-to pt	Delivery Document	Sold-to party	Material	Material Number	Document Date	Order Quantity
      
             if (in_array(strtoupper(trim(substr($fileArr[0],3,12))), $headerarray) )   {
                   // validate first line of file
                    $lineArr=str_getcsv($fileArr[0], "	", '"', "\\");
 echo "aa";
 
 
 
                   print_r($lineArr);
                    echo "<br>";
                  
                    for ($x = 0; $x <= 15; $x++) {

                    }

             } else {
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "First line expected to be a header!";
                  return $eTO;
             }
          echo "bb";
/*     
             $hasheader = 'N';
             $hasdetail = 'N';
             
             $currPrincipal = 401;	
      
             foreach ($fileArr as $key=>$line) {
      	
                   // convert line to CSV
                   $lineArr=str_getcsv($line, ",", '"', "\\");
           
                   print_r($lineArr);
                   
                   echo "<br>";
                   echo trim($lineArr[$docNo]);
                    echo "<br>";
                   
                   if(trim($lineArr[$docNo]) == ''){
                       continue;
                   }     
                   
                   /*******************
                   *   ORDER HEAD
                   *******************
                   $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                   $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                   $postingOrdersHoldingTO->principalUid = $currPrincipal;
                   $postingOrdersHoldingTO->updateProduct="N";
                   $postingOrdersHoldingTO->insertProduct="N";
                   $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                   $postingOrdersHoldingTO->dataSource = DS_EDI;
                   $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                   $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                   $postingOrdersHoldingTO->requestedDeliveryDate = $lineArr[$divDate];
                   $postingOrdersHoldingTO->capturedBy = "KIMBER";
                   $postingOrdersHoldingTO->deliveryInstructions = '';
                   $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                   $postingOrdersHoldingTO->offInvoiceDiscountType = '';                     
                   $postingOrdersHoldingTO->salesAgentStoreIdentifier = 538 ;
                   $postingOrdersHoldingTO->reference =  $lineArr[$reference];

                   $postingOrdersHoldingTO->depotLookupRef = "";

                   $postingOrdersHoldingTO->orderDate =  $lineArr[$oDate];
              
                   if ($postingOrdersHoldingTO->orderDate === false) {
                        $eTO->type = FLAG_ERRORTO_ERROR;
                        $eTO->description = "Order date invalid format or empty";
                        $eTO->identifier = ET_CUSTOMER;
                        return $eTO;
                   }
                   $postingOrdersHoldingTO->shipToName = $lineArr[$Customer_name];
                   $postingOrdersHoldingTO->deliverName = $lineArr[$Customer_name];
                   $postingOrdersHoldingTO->clientDocumentNo = $lineArr[$PoNo];
                   $postingOrdersHoldingTO->documentNo = $lineArr[$docNo];
                   $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV; 
                   $postingOrdersHoldingTO->storeLookupRef = $lineArr[$storeCode];
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                   $postingOrdersHoldingTO->oldAccount = '';
               
                   $arrTO[] = $postingOrdersHoldingTO;
                   /******************
                    *   ORDER DETAILS
                    ****************
          
                   $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

                   $postingOrdersHoldingDetailTO->listPrice     = $np ;
                   $postingOrdersHoldingDetailTO->quantity      = $lineArr[$cases];
                   $postingOrdersHoldingDetailTO->nettPrice     = '';
                   $postingOrdersHoldingDetailTO->extPrice      = '';
                   $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                   $postingOrdersHoldingDetailTO->vatAmount     = '';
                   $postingOrdersHoldingDetailTO->totalPrice    = '';
                   $postingOrdersHoldingDetailTO->productCode   = $lineArr[$prodCode];
                   $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                   $postingOrdersHoldingDetailTO->pallets       = 0;
                   $postingOrdersHoldingDetailTO->productName   =  '';
 
                   $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
               
                   $hasdetail = 'Y';
                   
                   echo $lineArr[$cases];
                   echo "<br>";
                   
             }
             if($hasdetail =='Y') {
                 $eTO->type = FLAG_ERRORTO_SUCCESS;
                 $eTO->description = "Successful";
                 $eTO->object = $arrTO;
                return $eTO;
             } else {
     	           $eTO->type = FLAG_ERRORTO_ERROR;
                 $eTO->description = "Invalid File or no Details";
                 $eTO->identifier = ET_CUSTOMER;
                 return $eTO;
             }       
    */
    }

// --------------------------------------------------------------------------------------------------------------------------------

/*

V;476;1;476000000659;PNPH001;PNP013;Pick n Pay Vaal Mall ;PO12345;ZAR;;20210906;ADMIN;807.02;928.07;10;0;0;0;Pick n Pay Retailers (Pty) Ltd;Shop 133 Vaal Mall Cnr Barrage Road and ;Vanderbijlpark;;1911;GAUTENG;WC;ZA;Pick 'n Pay Head Office;P.O.Box  1310;Bedfordview;;2008;GAUTENG;WC;ZA;4090105588;;
D;0005;Sweet Base Large;BXS;1;567.8125;0;0;0;493.7478;567.81;15;
D;0053;Crispy Coney Rolls;BXS;1;463.381;0;0;0;402.9391;463.38;15;

*/


   function adaptorTOH_BAKEITEASYDOCS($content, $onlineFileProcessItem) {
         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
          global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
         $eTO = new ErrorTO;
   
         $fileArr = explode("\n",$content);
         $headerarray=array('V'); 
         
         include_once ($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");
      
         if (in_array(trim(substr($fileArr[0],0,1)), $headerarray))   {
                // validfirst line of file
              $count = 0; 
              $hasDetail = 'N';
  
              foreach ($fileArr as $key=>$line) {
         	            // convert line to CSV
                      $lineArr=str_getcsv($line, ";", '"', "\\");
                      
                       //echo "<pre>";
                       //print_r($lineArr);
                      
                      if (trim($lineArr[0] ==  "V"))   {
                           // Check for incoming duplicate Numbers
                           
                           if(trim($lineArr[2]) <>  "1") {
                                $dupDocumentNumber = 'Y';
                                echo "Dup";
                                echo "<br>"; 
                           } else {
                                $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
                                $seDup = $MaintenanceDAO->checkForDuplicates('407', trim($lineArr[3]));
   
                                if(count($seDup) > 0) {
                           	       
                                } else {
                                      $dupDocumentNumber = 'N'; 	
                                }                            	
                           	
                                }
                      }
                      if($dupDocumentNumber == 'N') {
                           if (trim($lineArr[0] ==  "V"))   {
                           	
                           	     $whDocNoPrefixArr=$importDAO->getWhInvPrefix(550, trim($lineArr[1]));
                           	     
                           	     print_r($whDocNoPrefixArr);
                           	     
                           	     if(count($whDocNoPrefixArr) == 1) {
                                       $whDocNoPrefix = $whDocNoPrefixArr[0]['value'];
                           	     } else {
                           	     	     $whDocNoPrefix = 'X';
                           	     }
//                           	     echo   $whDocNoPrefix ;
//                           	     echo "<br>";
//                           	     echo "<br>";
                           	
                                 $count++;
                                 $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                                 $postingOrdersHoldingTO->updateProduct="N";
                                 $postingOrdersHoldingTO->insertProduct="N";
                                 $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                                 $postingOrdersHoldingTO->principalUid     = 407 ;
                                 $postingOrdersHoldingTO->vendorUid        = $onlineFileProcessItem["vendor_uid"];
                                 $postingOrdersHoldingTO->captureDate      = CommonUtils::getGMTime(0);
                                 $postingOrdersHoldingTO->capturedBy       = 'BIE'; // dont change this as notifications run off it
                                 $postingOrdersHoldingTO->incomingFile     = basename($onlineFileProcessItem["file_being_processed"]);
                                 $postingOrdersHoldingTO->dataSource       = DS_EDI;
                                 $postingOrdersHoldingTO->documentNo       = $whDocNoPrefix . trim(substr($lineArr[3],5,7));        //use provided doc no
                                 $postingOrdersHoldingTO->clientDocumentNo = trim($lineArr[3]);        //use provided doc no
                                 $postingOrdersHoldingTO->invoiceNumber    = trim($lineArr[3]);        //use provided doc no
                                 $postingOrdersHoldingTO->reference        = trim($lineArr[7]); //PO NUMBER.
                                 $postingOrdersHoldingTO->vendorReference  = '';
                                 $postingOrdersHoldingTO->oldAccount       = '';
                                 $postingOrdersHoldingTO->debtorsStoreIdentifier = "";
                                 $postingOrdersHoldingTO->salesAgentStoreIdentifier = ''; //same as old acc.
                                 $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';  //use generic => EDI pricing is used. no problem, happy days :)
                                 $postingOrdersHoldingTO->storeLookupRef   = trim($lineArr[5]);
                                 $postingOrdersHoldingTO->deliverName    = trim($lineArr[6]);
                                 $postingOrdersHoldingTO->shipToName     = $postingOrdersHoldingTO->deliverName;
                                       
                                 $postingOrdersHoldingTO->depotLookupRef = trim($lineArr[1]);;

                                 $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                                 $postingOrdersHoldingTO->offInvoiceDiscountType = '';  
                               
                                 $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                               
                                 $postingOrdersHoldingTO->orderDate = substr(trim($lineArr[10]), 0, 4) . '/' . substr(trim($lineArr[10]),4, 2) . '/' . substr(trim($lineArr[10]), 6, 2); //format YY-MM-DD, MySQL will accept this.
                                 $postingOrdersHoldingTO->requestedDeliveryDate = substr(trim($lineArr[10]), 0, 4) . '/' . substr(trim($lineArr[10]),4, 2) . '/' . substr(trim($lineArr[10]),6, 2);
                                 //check order date. must be a valid date and not 1970-01-01
                                 $ordDate = date('Ymd', strtotime($postingOrdersHoldingTO->orderDate));  //if value malformed will be 1970-01-01 and check below will activate!
                                 if(($postingOrdersHoldingTO->orderDate===false) || !(checkdate(substr($ordDate, 4,2), substr($ordDate,6,2), substr($ordDate, 0,4))) || ($ordDate == '1970-01-01')){
                                     $eTO->type = FLAG_ERRORTO_ERROR;
                                     $eTO->description = "Order date Invalid format or empty";
                                     $eTO->identifier = ET_CUSTOMER;
                                     return $eTO;
                                 }
                                       
                                 // Create the StoreTO
                                 $postingStoreTO = new PostingStoreTO;
                                 $postingStoreTO->DMLType = "INSERT";
                                 $postingStoreTO->principalStoreUId = "";
                                 $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                                //  echo "<br>Name<br>";
                                //  echo preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[6])));
                                //  echo "<br>";
                                //  echo mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[6]));
                                //  echo "<br>";
                                                    
                                 $postingStoreTO->deliverName = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[6])));
                                 $postingStoreTO->deliverAdd1 = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[18])));
                                 $postingStoreTO->deliverAdd2 = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[19])));
                                 $postingStoreTO->deliverAdd3 = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[20])));
                                 $postingStoreTO->billName = $postingStoreTO->deliverName;
                                 $postingStoreTO->billAdd1 = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[26])));
                                 $postingStoreTO->billAdd2 = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[27])));
                                 $postingStoreTO->billAdd3 = trim(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[29])))) . '  ' . preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[22])));
                                 
                                 $postingStoreTO->vatNumber = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', mysqli_real_escape_string($this->dbConn->connection,trim($lineArr[34])));
                                 $postingStoreTO->telNo1    = ''; 
                                 $postingStoreTO->depot = ""; // this will be set by the processing script
                                 $postingStoreTO->deliveryDay = "8";
                                 $postingStoreTO->noVAT = 0;
                                 $postingStoreTO->onHold = "0";
                                 $postingStoreTO->chain = ""; // this will be set by the processing script
                                 $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                                 $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                                 $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                                 $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
                                 $postingStoreTO->oldAccount = trim($lineArr[5]);
                              
                                 $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
                              
                                 // lookup special field(s) - enforce this specific one
                                 $postingSpecialFieldTO = new PostingSpecialFieldTO;
                                 $postingSpecialFieldTO->DMLType = "INSERT";
                                 $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                                 $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                                 $postingSpecialFieldTO->fielduid = 548; // Special Field
                                 $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                                 $postingSpecialFieldTO->value = trim($lineArr[5]);
                                 $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;                                 
                                 
                                 $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
                                 $seDup = $MaintenanceDAO->addToDocControl('407', trim($lineArr[1]), basename($onlineFileProcessItem["file_being_processed"]));
                            }     	
                            if (trim($lineArr[0]) ==  "D" )   {
                                  /*******************
                                   *   ORDER DETAILS
                                   *******************/
                                   $hasDetail = 'Y';
                              
                                   $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();  // should not be necessary to check document type as other types should end up as zeros
                                   $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                                   $postingOrdersHoldingDetailTO->pallets             = 0;
                                   $postingOrdersHoldingDetailTO->clientPageNo        = '';
                                   $postingOrdersHoldingDetailTO->clientLineNo        = ''; 
                                   $postingOrdersHoldingDetailTO->productCode         = trim($lineArr[1]) ;
                                   $postingOrdersHoldingDetailTO->productName         = trim($lineArr[2]);
                                   $postingOrdersHoldingDetailTO->itemspercase        = ''; 
                                   $postingOrdersHoldingDetailTO->discountValue       = '';
                                   $postingOrdersHoldingDetailTO->discountReference   = '';
                                   $postingOrdersHoldingDetailTO->quantity            = trim($lineArr[4]);
                                   $postingOrdersHoldingDetailTO->listPrice           = trim($lineArr[9]);
                                   $postingOrdersHoldingDetailTO->nettPrice           = $postingOrdersHoldingDetailTO->listPrice;
                                   $postingOrdersHoldingDetailTO->extPrice            = ($postingOrdersHoldingDetailTO->nettPrice * $postingOrdersHoldingDetailTO->quantity);
                                   $postingOrdersHoldingDetailTO->vatRate             = trim($lineArr[11]);
                                   $postingOrdersHoldingDetailTO->vatAmount           = round($postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100 ,2);
                                   $postingOrdersHoldingDetailTO->totalPrice          = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;
                                   $postingOrdersHoldingTO->detailArr[]               = $postingOrdersHoldingDetailTO;
                            }
                      } else {
                           echo "<br>";
                           echo "Duplicate or Credit " . trim($lineArr[1]) . "  - Not Processed" ;
                           echo "<br>";
                      }                   
              }
              
//              print_r($postingOrdersHoldingTO);
              
              $arrTO[] = $postingOrdersHoldingTO; //add final order, no need to check if has detail as we do that in the begin.
              if($count > 0 && $hasDetail == 'Y')  {
                   $eTO->type = FLAG_ERRORTO_SUCCESS;
                   $eTO->description = "Successful";
                   $eTO->object = $arrTO;
                   return $eTO;
              } else {
                   $eTO->type = FLAG_ERRORTO_ERROR;
                   $eTO->description = "No Orders in batch - Ignored";
                   return $eTO;
              }
         } else {
              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "First line expected to be a header!";
              return $eTO;
         }
   }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------
   
    function adaptorTOH_UPAP($content, $onlineFileProcessItem) {

         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
         global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

         $eTO = new ErrorTO;
         if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
            $arrTO=array();
             $processingLine=0;      
             $headerarray=array('PRINCIPAL CODE');

             $fileArr = explode("\n",$content);
      
             $validfile = 0;
             echo count($fileArr);    
//           echo "<pre>";
//           print_r($fileArr)  ;
//           echo "<br>";
//           echo strtoupper(trim(substr($fileArr[0],0,14)));
//           echo "<br>";

// Principal CODE,Order type,Order date,Order no.,Customer order ref ,Sold-to,Sold-to customer name,Free Stock Indicator,Product,Description,Ordered quantity,Sales unit,Gross price,Discount/Charge 1,Net price,Valuation + tax,Pay-by,Acronym

      
             if (in_array(strtoupper(trim(substr($fileArr[0],0,14))), $headerarray) || in_array(strtoupper(trim(substr($fileArr[0],0,10))), $headerarray) )   {
                   // validate first line of file
                    $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");
                    
                    //print_r($lineArr);
                    
                    //echo count($lineArr);
                    
                    //echo "<br>";
                    
                    if(count($lineArr) > 1) {
                         	
                         for ($x = 0; $x <= 20; $x++) {
              	              if(strtoupper(trim($lineArr[$x]))=='ORDER TYPE') {
                                 $docType = $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='ORDER DATE') {
                                 $oDate = $x;
                                 $validfile++;                     
                             } elseif(strtoupper(trim($lineArr[$x]))=='ORDER NO.') {
                                 $docNo = $x;
                                 $validfile++;  
                             } elseif(strtoupper(trim($lineArr[$x]))=='CUSTOMER ORDER REF') {
                                 $reference = $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='SOLD-TO') {
                                 $storeCode = $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='SOLD-TO CUSTOMER NAME') {
                                 $Customer_name = $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='FREE STOCK INDICATOR') {
                                  $freeStock= $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='PRODUCT') {
                                 $prodCode = $x;
                                 $validfile++;                                 
                             } elseif(strtoupper(trim($lineArr[$x]))=='DESCRIPTION') {
                                 $product = $x;
                                 $validfile++;                  
                             } elseif(strtoupper(trim($lineArr[$x]))=='ORDERED QUANTITY') {
                                 $cases = $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='SALES UNIT') {
                                 $salesUnit = $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='GROSS PRICE') {
                                 $gPrice = $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='DISCOUNT/CHARGE 1') {
                                 $discount = $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='NET PRICE') {
                                 $netPrice = $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='VALUATION + TAX') {
                                 $valuation = $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='PAY-BY') {
                                 $payBy = $x;
                                 $validfile++;
                             } elseif(strtoupper(trim($lineArr[$x]))=='ACRONYM') {
                                 $acronym = $x;
                                 $validfile++;
                             }
//                             echo $lineArr[$x];
//                             echo "<br>";
                         }
//                         echo "<br>";
//                         echo $validfile;
//                         echo "<br>";
                         if($validfile < 17 ) {
                               $eTO->type = FLAG_ERRORTO_ERROR;
                               $eTO->description = "Check file - Order";
                               return $eTO;
                         }          
                         unset($fileArr[0]);
                         
                    } else {
                              $eTO->type = FLAG_ERRORTO_ERROR;
                              $eTO->description = "No orders in file!";
                              return $eTO;
                    }
             } else {
                   $eTO->type = FLAG_ERRORTO_ERROR;
                   $eTO->description = "First line expected to be a header!";
                   return $eTO;
             }
            
             $hasheader = 'N';
             $hasdetail = 'N';
             $newOrder  = '';
             
             $currPrincipal = 412;	
           
             foreach ($fileArr as $key=>$line) {
      	   	
                 // convert line to CSV
                 $lineArr=str_getcsv($line, ",", '"', "\\");
             
                 // print_r($lineArr);
//                 echo "<br>";
//                 echo trim($lineArr[$docNo]);
//                 echo "<br>";
                 
                 if(trim($lineArr[$docNo]) == '' && $hasheader == 'N'){
                       $eTO->type = FLAG_ERRORTO_SUCCESS;
                       $eTO->description = "File Empty";
                       return $eTO;
                 }
                 
                 $hasheader = 'Y';
                 
                 if($newOrder <> trim($lineArr[$docNo])) {
                         if(trim($newOrder) <> '') {
                         	
                         	                        echo "<pre>";
//                        print_r($postingOrdersHoldingTO);
//                        echo "<br>";
                         	
                         	
                            $arrTO[] = $postingOrdersHoldingTO;
                         }
                         $newOrder = trim($lineArr[$docNo]);
                        
                         /*******************
                         *   ORDER HEAD
                         *******************/
                         $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                         $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                         $postingOrdersHoldingTO->principalUid = $currPrincipal;
                         $postingOrdersHoldingTO->updateProduct="N";
                         $postingOrdersHoldingTO->insertProduct="N";
                         $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                         $postingOrdersHoldingTO->dataSource = DS_EDI;
                         $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                         $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                         $postingOrdersHoldingTO->requestedDeliveryDate = '';
                         $postingOrdersHoldingTO->capturedBy = "UPAP";
                         $postingOrdersHoldingTO->deliveryInstructions = '';
                         $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                         $postingOrdersHoldingTO->offInvoiceDiscountType = '';                     
                         $postingOrdersHoldingTO->salesAgentStoreIdentifier = 561 ;
                         $postingOrdersHoldingTO->reference =  $lineArr[$reference];
                
                         $postingOrdersHoldingTO->depotLookupRef = "";
                
                         $postingOrdersHoldingTO->orderDate =  $lineArr[$oDate];
                
                         if ($postingOrdersHoldingTO->orderDate === false) {
                              $eTO->type = FLAG_ERRORTO_ERROR;
                              $eTO->description = "Order date invalid format or empty";
                              $eTO->identifier = ET_CUSTOMER;
                             return $eTO;
                         }
                         $postingOrdersHoldingTO->shipToName = $lineArr[$Customer_name];
                         $postingOrdersHoldingTO->deliverName = $lineArr[$Customer_name];
                         $postingOrdersHoldingTO->clientDocumentNo = $lineArr[$docNo];
                         $postingOrdersHoldingTO->documentNo = $lineArr[$docNo];
                         $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV; 
                         $postingOrdersHoldingTO->storeLookupRef = $lineArr[$storeCode];
                         $postingOrdersHoldingTO->enforceSameDepot = "N";
                         $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                         $postingOrdersHoldingTO->oldAccount = '';
                    }
                    /******************
                     *   ORDER DETAILS
                     *****************/
             
                    $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
         
                    $postingOrdersHoldingDetailTO->listPrice     = $np ;
                    $postingOrdersHoldingDetailTO->quantity      = $lineArr[$cases];
                    $postingOrdersHoldingDetailTO->nettPrice     = $lineArr[$netPrice];;
                    $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->quantity * $postingOrdersHoldingDetailTO->nettPrice;
                    $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                    $postingOrdersHoldingDetailTO->vatAmount     = $postingOrdersHoldingDetailTO->quantity * $postingOrdersHoldingDetailTO->nettPrice * VAL_VAT_RATE_TBLSTD / 100;;
                    $postingOrdersHoldingDetailTO->totalPrice    = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount ;
                    $postingOrdersHoldingDetailTO->productCode   = $lineArr[$prodCode];
                    $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                    $postingOrdersHoldingDetailTO->pallets       = 0;
                    $postingOrdersHoldingDetailTO->productName   =  $lineArr[$product];;
         
                    $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
                    
                    $hasdetail = 'Y';
             }
             
//                                     echo "<pre>";
//                        print_r($postingOrdersHoldingTO);
//                        echo "<br>";
             
                    
             $arrTO[] = $postingOrdersHoldingTO;
                    
             if($hasdetail =='Y') {
                 $eTO->type = FLAG_ERRORTO_SUCCESS;
                 $eTO->description = "Successful";
                 $eTO->object = $arrTO;
                return $eTO;
             } else {
     	           $eTO->type = FLAG_ERRORTO_ERROR;
                 $eTO->description = "Invalid File or no Details";
                 $eTO->identifier = ET_CUSTOMER;
                 return $eTO;
             }       
    }
// --------------------------------------------------------------------------------------------------------------------------------
    function adaptorTOH_API_SHOPIFY($content, $onlineFileProcessItem) {
      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO=new StoreDAO($this->dbConn);      
      $inArray = json_decode($content, true);
      
//      echo "<pre>";
//      print_r($inArray);
//      echo "<br>";
      
      $hasdetail   = 'N';
//      echo "<br>";
      
//      echo count($inArray);
//      echo "<br>";
      

         foreach($inArray as $key=>$row) {
//      	          echo $key . '   ' . $row . "<br>";
//                  echo "<br>Here<br>";
                  
                  /*******************
                     *   ORDER HEAD
                  ******************/
                  if($key == 'requireddata') {
                       $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                       $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                       $postingOrdersHoldingTO->updateProduct="N";
                       $postingOrdersHoldingTO->insertProduct="N";
                       $postingOrdersHoldingTO->skipInvoiceComputationCheck="Y";
                       $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                       $postingOrdersHoldingTO->dataSource = DS_WS;
                       $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                       $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                       $postingOrdersHoldingTO->requestedDeliveryDate = "";
                       $postingOrdersHoldingTO->capturedBy = "Shopify-API";
                       $postingOrdersHoldingTO->additionalDetails = '';
                       $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                  } 
                  if($key == 'principalId') {
                        $postingOrdersHoldingTO->principalUid = trim($row);
                  }
                  if($key == 'reference_number')  {
                       $postingOrdersHoldingTO->clientDocumentNo = trim($row);
                       $postingOrdersHoldingTO->documentNo = '';
                  }                
                  if($key == 'customer_account_code') {
                  	
                  	
                  }                     
                  if($key == 'customer_name') {
                        $postingOrdersHoldingTO->shipToName = trim($row);
                        $postingOrdersHoldingTO->deliverName = trim($row);
                        $postingOrdersHoldingTO->storeLookupRef = "" ; 
                        $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = "Y";
                        $postingOrdersHoldingTO->enforceSameDepot = "N";
                        $postingOrdersHoldingTO->chainLookupRef = 'SC';
                        $postingOrdersHoldingTO->oldAccount = '';
                        $postingOrdersHoldingTO->salesAgentStoreIdentifier = "";
                        
                        // Create the StoreTO
                        $postingStoreTO = new PostingStoreTO;
                        $postingStoreTO->DMLType = "INSERT";
                        $postingStoreTO->principalStoreUId = "";
                        $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                        $postingStoreTO->deliverName = trim($row);
                        $postingStoreTO->billName = $postingStoreTO->deliverName;
                    
                        $postingStoreTO->vatNumber = ''; 

                        $postingStoreTO->depot = ""; // this will be set by the processing script
                        $postingStoreTO->deliveryDay = "8";
                        $postingStoreTO->noVAT = 0;
                        $postingStoreTO->onHold = "0";
                        $postingStoreTO->chain = ""; // this will be set by the processing script
                        $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                        $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                        $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                        $postingStoreTO->ownedBy = '';
                        $postingStoreTO->oldAccount = '';
                        
                        $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
                    
                        $postingSpecialFieldTO = new PostingSpecialFieldTO;
                        $postingSpecialFieldTO->DMLType = "INSERT";
                        $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                        $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                        $postingSpecialFieldTO->fielduid = 253;   // Store Special Field
                        $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                        $postingSpecialFieldTO->value = "THETEQ";

                        $postingSpecialFieldTO = new PostingSpecialFieldTO;
                        $postingSpecialFieldTO->DMLType = "INSERT";
                        $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                        $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                        $postingSpecialFieldTO->fielduid = 257;   // Store Special Field
                        $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                        $postingSpecialFieldTO->value = "CW";
                  }
                  if($key == 'physical_address_1') {
                         $postingStoreTO->deliverAdd1 = trim($row);
                         $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1; 
                  }
                  if($key == 'physical_address_2') {
                         $postingStoreTO->deliverAdd2 = trim($row);
                         $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
                  }                                      
                  if($key == 'physical_address_3') {
                          $add3_1 = $postingStoreTO->deliverAdd3 = trim($row);
                  }          
                  if($key == 'physical_address_4') {
                        $postingStoreTO->deliverAdd3 = $add3_1 . '   ' . trim($row);
                        $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
                  }          
                  if($key == 'region') {
                        if(in_array($lineArr[$wareHouse], array('Western Cape'))) {
                             $deplook = 'CPT' ;
                        } elseif(in_array($lineArr[$wareHouse], array('Gauteng','Free State','North West','Limpopo'))) {
                             $deplook = 'CPT' ;
                        } elseif(in_array($lineArr[$wareHouse], array('KwaZulu-Natal'))) {
                             $deplook = 'CPT';
                        } else {
                    	       $deplook = 'CPT';                    	
                        }
                        $postingOrdersHoldingTO->depotLookupRef = $deplook;                  	
                  }                         

                  if($key == 'shipping') {
                       if(preg_replace('/[\xEF\xBB\xBF\x22]/', '', trim($row) > 0)) {
                         $addShipping = 'Y';
                         $shippingCharge = trim($row);
                    } else {
                         $addShipping = 'N';
                         $shippingCharge = 0;
                    } 
                  }  
                  if($key == 'order_discount_type') {
                       $postingOrdersHoldingTO->offInvoiceDiscountType = "A";
                  }
                  if($key == 'order_discount_amount') {
                  	
                  	   echo "<br>";
                  	   echo trim($row);
                  	    echo "<br>";
                  	  
                       $postingOrdersHoldingTO->offInvoiceDiscount = trim($row);
                  }
                 if($key == 'email_address') {
                       $postingStoreTO->emailAdd = trim($row);
                  }
                 if($key == 'contact_number') {
                       $postingStoreTO->telNo1    = trim($row);
                  }
                 if($key == 'purchase_order_number') {
                 	     $postingOrdersHoldingTO->reference = substr(trim($row),0,19);
                 }
                 if($key == 'order_date') {
                       $postingOrdersHoldingTO->orderDate =  "20" . substr(trim($row),0,2) . "-" . substr(trim($row),3,2) . "-" . substr(trim($row),6,2);                	
                       if ($postingOrdersHoldingTO->orderDate === false) {
                             $eTO->type = FLAG_ERRORTO_ERROR;
                             $eTO->description = "Order date invalid format or empty";
                             $eTO->identifier = ET_CUSTOMER;
                             return $eTO;
                       }
                 }
                 if($key == 'required_date') {
                 } 
                 if($key == 'delivery_instructions') {
                       $postingOrdersHoldingTO->deliveryInstructions = trim($row);
                 }
                 if($key == 'detail_lines') {
                       $arrTO[] = $postingOrdersHoldingTO;
                       $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;
                    
                       foreach($inArray['detail_lines'] as $dRow ) { 
                           $clientLineNo++;                 	   	
                           foreach($dRow as $dKey=>$dline) {
                               // echo $dKey . '   ' . $dline . "<br>";
                               // echo "<br>Here Detail<br>";
                               if($dKey == 'product_code') {
                                     $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                                     $postingOrdersHoldingDetailTO->clientLineNo =  $clientLineNo;
                                     $postingOrdersHoldingDetailTO->productCode   = trim($dline);
                               }      
                               if($dKey == 'product_description') {
                                     $postingOrdersHoldingDetailTO->productName   = trim($dline);	
                                     if(str_replace(" ","",str_replace("'","",trim($dline))) == "MonthlyMembersSubscription") {
                                     	     $postingOrdersHoldingDetailTO->productCode = '2725000';
                                     }
                               }
                               if($dKey == 'order_quantity') {
                               	  $postingOrdersHoldingDetailTO->quantity = trim($dline);	                      	
                               }
                               if($dKey == 'selling_price') {
                                    $postingOrdersHoldingDetailTO->listPrice = trim($dline);	
                               	
                               }
                               if($dKey == 'line_discount_type') {
                               }
                               if($dKey == 'line_discount_amount') {
                               	    $postingOrdersHoldingDetailTO->discountValue = trim($dline);
                               }
                               if($dKey == 'line_nett_price') {
                                  	$netIncl = trim($dline);
                                    
                               }
                               if($dKey == 'vat_rate') {
                                      $postingOrdersHoldingDetailTO->vatRate = trim($dline);
                                      $netExcl = round($netIncl * 100/(100+$postingOrdersHoldingDetailTO->vatRate),2) ;
                               	      $postingOrdersHoldingDetailTO->nettPrice   = $netExcl ;
                               	      $postingOrdersHoldingDetailTO->extPrice    = $postingOrdersHoldingDetailTO->quantity * $netExcl ;
                                      $postingOrdersHoldingDetailTO->totalPrice  = $postingOrdersHoldingDetailTO->quantity * $netIncl ;

                                      $postingOrdersHoldingDetailTO->vatAmount   = $postingOrdersHoldingDetailTO->totalPrice - $postingOrdersHoldingDetailTO->extPrice;
                                        
                                      $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                                      $postingOrdersHoldingDetailTO->pallets       = 0;
                          
                                      $postingOrdersHoldingDetailTO->overridePriceType = "2" ;
                               }
                           }

                           $clientLineNo = 0;
                           $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
                           
                           $hasdetail = 'Y';
                       }
                 }
                 
                 if($hasdetail == 'Y' && $addShipping == 'Y' ) {
                    	    $clientLineNo++;
                          $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                          $postingOrdersHoldingDetailTO->clientLineNo =  $clientLineNo;
                          $postingOrdersHoldingDetailTO->listPrice     = $shippingCharge * (100/(100+$postingOrdersHoldingDetailTO->vatRate));
                          
                          $postingOrdersHoldingDetailTO->quantity      = 1;
                          $postingOrdersHoldingDetailTO->discountValue = 0;
                          $postingOrdersHoldingDetailTO->nettPrice     = round($shippingCharge * (100/(100+VAL_VAT_RATE_TBLSTD)),4);  ;
                          $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->quantity * $postingOrdersHoldingDetailTO->nettPrice ;
                          $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                          $postingOrdersHoldingDetailTO->vatAmount     = $postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100 ;
                          $postingOrdersHoldingDetailTO->totalPrice    = $shippingCharge;
                          $postingOrdersHoldingDetailTO->productCode   = '2720000';
                          $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                          $postingOrdersHoldingDetailTO->pallets       = 0;
                          $postingOrdersHoldingDetailTO->productName   = 'Delivery Fees';
                          $postingOrdersHoldingDetailTO->overridePriceType = "2" ;
                          $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
                 }
         }
         if($hasdetail =='Y') {
                     $eTO->type = FLAG_ERRORTO_SUCCESS;
                     $eTO->description = "Successful";
                     $eTO->object = $arrTO;
                     return $eTO;
         } else {
                     $eTO->type = FLAG_ERRORTO_ERROR;
                     $eTO->description = "Invalid File or no Details";
                     $eTO->identifier = ET_CUSTOMER;
                     return $eTO;
         }
    }       
// ********************************************************************************************************************************

   function adaptorTOH_GOLDENSNACKSDOCS($content, $onlineFileProcessItem) {
     // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
     global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
     $eTO = new ErrorTO;
   
     $fileArr = explode("\n",$content);
     $headerarray=array('AREA CODE AND DESCRIPTION');
     $fileArr = explode("\n",$content);
        
     $cleanString = strToUpper(preg_replace('/[\xFF\xFE\x00\x22]/', '', $fileArr[0]));

     if (in_array(substr($cleanString,0,25), $headerarray))   {
            // validate first line of file

            
            
            $lineArr=str_getcsv($cleanString, ",", '"', "\\");
            for ($x = 0; $x <= 50; $x++) {
    	
               if(strToUpper(trim($lineArr[$x]))=='AREA CODE AND DESCRIPTION') {
                  $areaDepot = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='DOCUMENT TYPE') {
                  $dType = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='DOCUMENT DATE') {
                  $dDate = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='REFERENCE') {
                  $docNumber = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='CUSTOMER ACCOUNT') {
                  $custAcc = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='CUSTOMER NAME') {
                  $delName = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='PHYSICAL ADDRESS 1') {
                  $dAdd1 = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='PHYSICAL ADDRESS 2') {
                  $dAdd2 = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='PHYSICAL ADDRESS 3') {
                  $dAdd3 = $x;
                  $validfile++;     
               } elseif(strToUpper(trim($lineArr[$x]))=='PHYSICAL ADDRESS 4') {
                  $dAdd4 = $x;
                  $validfile++;                 
               } elseif(strToUpper(trim($lineArr[$x]))=='PHYSICAL ADDRESS 5') {
                  $dAdd5 = $x;
                  $validfile++; 
               } elseif(strToUpper(trim($lineArr[$x]))=='POSTAL ADDRESS 1') {
                  $pAdd1 = $x;
                  $validfile++;                  
               } elseif(strToUpper(trim($lineArr[$x]))=='Postal Address 2') {
                  $pAdd2 = $x;
                  $validfile++;                   
               } elseif(strToUpper(trim($lineArr[$x]))=='POSTAL ADDRESS 3') {
                  $pAdd3 = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='POSTAL ADDRESS 4') {
                  $pAdd4 = $x;
                  $validfile++; 
               } elseif(strToUpper(trim($lineArr[$x]))=='POSTAL ADDRESS 5') {
                  $pAdd5 = $x;
                  $validfile++; 
               } elseif(strToUpper(trim($lineArr[$x]))=='CUSTOMER ORDER NO') {
                  $poNum = $x;
                  $validfile++;                  
               } elseif(strToUpper(trim($lineArr[$x]))=='ORDER NUMBER') {
                  $ordNum = $x;
                  $validfile++;                 
               } elseif(strToUpper(trim($lineArr[$x]))=='LINE NUMBER') {
                  $LineNo = $x;
                  $validfile++;                 
               } elseif(strToUpper(trim($lineArr[$x]))=='STOCK CODE') {
                  $ProdCode = $x;
                  $validfile++;                              
               } elseif(strToUpper(trim($lineArr[$x]))=='STOCK DESCRIPTION') {
                  $productName = $x;
                  $validfile++;                 
               } elseif(strToUpper(trim($lineArr[$x]))=='QUANTITY') {
                  $quantity = $x;
                  $validfile++;                 
               } elseif(strToUpper(trim($lineArr[$x]))=='VALUE (EXCL) AFTER DISCOUNT') {
                  $netPriceEx = $x;
                  $validfile++;                 
               } elseif(strToUpper(trim($lineArr[$x]))=='VALUE (INCL) AFTER DISCOUNT') {
                  $netPriceIn = $x;
                  $validfile++;                 
               } elseif(strToUpper(trim($lineArr[$x]))=='SYSTEM DATE AND TIME') {
                  $dateTime = $x;
                  $validfile++;
               } 
         }     
         echo "<br>";
         echo $validfile;
         echo "<br>";
         if($validfile <> 24 ) {
             $eTO->type = FLAG_ERRORTO_ERROR;
             $eTO->description = "Check file - Order fields missing!";
             return $eTO;
         }          
         if(count($lineArr) < 24 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Invalid Column Count - expecting 24 columns Found " . count($lineArr);
            return $eTO;
         }          
         unset($fileArr[0]);      
     } else {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "First line expected to be a header!";
            return $eTO;
     } 
      
      include_once ($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");
      
 	    $newDocument = '';
      $count = 0;
      $dupDocumentNumber = 'N';  
        
      foreach ($fileArr as $key=>$line) {
              // convert line to CSV
  
              $lineArr=str_getcsv(preg_replace('/[\x00]/', '', $line), ",", '"');
              
              $docNo = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$docNumber])); 
              
              if(strToUpper($lineArr[$dType]) == 'CREDIT NOTE' || $docNo == '') {
              	  continue;
              }
              
              if ($docNo <>  $newDocument)   {
                   // Check for incoming duplicate Numbers
                   
                   $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
                   $seDup = $MaintenanceDAO->checkForDuplicates('417', $docNo);
                   
                   if(count($seDup) > 0) {
                         $dupDocumentNumber = 'Y';  
                   } else {
                         $dupDocumentNumber = 'N'; 	
                   }
                   
                   if($dupDocumentNumber == 'Y') {
                          echo "<br>";
                          echo "Duplicate " . $docNo . "  - Not Processed" ;
                          echo "<br>";
                          continue ;
                   } 

                   if($dupDocumentNumber == 'N') {
                        if ($newDocument <>  '')   {
                             if (isset($postingOrdersHoldingTO)) {
                                if (sizeof($postingOrdersHoldingTO->detailArr)==0) {
                                     $eTO->type = FLAG_ERRORTO_ERROR;
                                     $eTO->description = "No Detail Lines found for Header Line @line:".($key+1);
                                     $eTO->identifier = ET_CUSTOMER;
                                     return $eTO;
                                }
                                // echo "<pre>";
                                // print_r($postingOrdersHoldingTO);
                                
                                $arrTO[] = $postingOrdersHoldingTO;
                                unset($postingOrdersHoldingTO);
                             }
                         }
                         
                         $count++;
                         $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                         $postingOrdersHoldingTO->updateProduct="N";
                         $postingOrdersHoldingTO->insertProduct="N";
                         $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                         $postingOrdersHoldingTO->principalUid     = 417 ;
                         $postingOrdersHoldingTO->vendorUid        = $onlineFileProcessItem["vendor_uid"];
                         $postingOrdersHoldingTO->captureDate      = CommonUtils::getGMTime(0);
                         $postingOrdersHoldingTO->capturedBy       = 'GoldenS'; // dont change this as notifications run off it
                         $postingOrdersHoldingTO->incomingFile     = basename($onlineFileProcessItem["file_being_processed"]);
                         $postingOrdersHoldingTO->dataSource       = DS_EDI;
                         if(substr($docNo,0,1) == 'G') {
                         	    $snipdoc = trim(substr($docNo,1,8)); 
                         } else {
                         	    $snipdoc = trim($docNo);
                         }
                         $postingOrdersHoldingTO->documentNo       = $snipdoc;
                         $postingOrdersHoldingTO->clientDocumentNo = $docNo;        //use provided doc no
                         $postingOrdersHoldingTO->reference        = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$poNum])); //PO NUMBER.
                         $postingOrdersHoldingTO->vendorReference  = '';
                         $postingOrdersHoldingTO->oldAccount       = '';
                         $postingOrdersHoldingTO->debtorsStoreIdentifier = "";
                         $postingOrdersHoldingTO->salesAgentStoreIdentifier = ''; //same as old acc.
                         $postingOrdersHoldingTO->chainLookupRef = '3214';  //use generic => EDI pricing is used. no problem, happy days :)
                         $postingOrdersHoldingTO->storeLookupRef   = ''; //trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$custAcc]));
                         $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = 'Y';
                         $postingOrdersHoldingTO->deliverName    = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$delName]));
                         $postingOrdersHoldingTO->shipToName     = $postingOrdersHoldingTO->deliverName;
                         $postingOrdersHoldingTO->depotLookupRef = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$areaDepot])) ;
                 
                         $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                         $postingOrdersHoldingTO->orderDate = substr(trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$dateTime])), 0, 4) . '-' . substr(trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', 'a',$lineArr[$dateTime])), 5, 2) . '-' . substr(trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', 'a',$lineArr[$dateTime])), 8, 2);
//                       $postingOrdersHoldingTO->orderDate = '2022-05-25';
                         $postingOrdersHoldingTO->requestedDeliveryDate = $postingOrdersHoldingTO->orderDate;
                         //check order date. must be a valid date and not 1970-01-01
                         $ordDate = date('Ymd', strtotime($postingOrdersHoldingTO->orderDate));  //if value malformed will be 1970-01-01 and check below will activate!
                         if(($postingOrdersHoldingTO->orderDate===false) || !(checkdate(substr($ordDate, 4,2), substr($ordDate,6,2), substr($ordDate, 0,4))) || ($ordDate == '1970-01-01')){
                              $eTO->type = FLAG_ERRORTO_ERROR;
                              $eTO->description = "Order date Invalid format or empty";
                              $eTO->identifier = ET_CUSTOMER;
                              return $eTO;
                         }
                         /*******************
                         *   CREATE STORE
                         *******************/
                         $postingStoreTO = new PostingStoreTO;
                         $postingStoreTO->DMLType = "INSERT";
                         $postingStoreTO->principalStoreUId = '';
                         $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                         $postingStoreTO->deliverName = $postingOrdersHoldingTO->deliverName;
                         $postingStoreTO->deliverAdd1 = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$dAdd1]));
                         $postingStoreTO->deliverAdd2 = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$dAdd2]));
                         $postingStoreTO->deliverAdd3 = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$dAdd3]));
                         $postingStoreTO->billName    = $postingOrdersHoldingTO->deliverName;   
                         $postingStoreTO->billAdd1    = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$pAdd1]));                 
                         $postingStoreTO->billAdd2    = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$pAdd2]));             
                         $postingStoreTO->billAdd3    = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$pAdd3]));
                         $postingStoreTO->vatNumber   = '';
                         $postingStoreTO->depot = ''; // this will be set by the processing script
                         $postingStoreTO->deliveryDay = "8";
                         $postingStoreTO->noVAT = 0;
                         $postingStoreTO->onHold = "0";
                         $postingStoreTO->chain = ''; // this needs to be assigned by exceptions user.
                         $postingStoreTO->altPrincipalChainUId = ''; // let the posting allocate the generic chain
                         $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                         $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                         $postingStoreTO->ownedBy = '';
                         $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;  //NB!!!
                         $postingStoreTO->updatePrincipalStore = 'Y';
             
                         $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO; 

                         // lookup special field(s) - enforce this specific one
                         $postingSpecialFieldTO = new PostingSpecialFieldTO;
                         $postingSpecialFieldTO->DMLType = "INSERT";
                         $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                         $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                         $postingSpecialFieldTO->fielduid = 573; // Special Field
                         $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                         $postingSpecialFieldTO->value = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$custAcc]));
                         $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;                       
                         
                         $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
                         $seDup = $MaintenanceDAO->addToDocControl('417', trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$docNumber])), basename($onlineFileProcessItem["file_being_processed"]));
                   }
                   $newDocument = $docNo;

              }
              if($dupDocumentNumber == 'N') {                           
                    /*******************
                        *   ORDER DETAILS
                    *******************/
                    $count++;
                                               
                    $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();  // should not be necessary to check document type as other types should end up as zeros
                    $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                    $postingOrdersHoldingDetailTO->pallets             = 0;
                    $postingOrdersHoldingDetailTO->clientPageNo        = '';
                    $postingOrdersHoldingDetailTO->clientLineNo        = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$LineNo])) ; 
                    $postingOrdersHoldingDetailTO->productCode         = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$ProdCode])) ;
                    $postingOrdersHoldingDetailTO->productName         = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$productName]));
                    $postingOrdersHoldingDetailTO->itemspercase        = ''; 
                    $postingOrdersHoldingDetailTO->discountValue       = '';
                    $postingOrdersHoldingDetailTO->discountReference   = '';
                    $postingOrdersHoldingDetailTO->quantity            = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$quantity]));
                    $postingOrdersHoldingDetailTO->listPrice           = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$netPriceEx])) / trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$quantity]));
                    $vrat = VAL_VAT_RATE_TBLSTD;
                    $postingOrdersHoldingDetailTO->nettPrice           = $postingOrdersHoldingDetailTO->listPrice;
                    $postingOrdersHoldingDetailTO->extPrice            = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$netPriceEx]));
                    $postingOrdersHoldingDetailTO->vatRate             = $vrat;
                    $postingOrdersHoldingDetailTO->vatAmount           = round(trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$netPriceIn])) - trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$netPriceEx])) ,2);
                    $postingOrdersHoldingDetailTO->totalPrice          = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$netPriceIn]));
                    $postingOrdersHoldingTO->detailArr[]               = $postingOrdersHoldingDetailTO;
              } 
      }             
       
      $arrTO[] = $postingOrdersHoldingTO; //add final order, no need to check if has detail as we do that in the begin.
      if($count > 0)  {
             $eTO->type = FLAG_ERRORTO_SUCCESS;
             $eTO->description = "Successful";
             $eTO->object = $arrTO;
             return $eTO;
      } else {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "No Orders in batch - Ignored";
          return $eTO;
      }      
             
   } 
// --------------------------------------------------------------------------------------------------------------------------------       
// --------------------------------------------------------------------------------------------------------------------------------
    function adaptorTOH_API_ALL($content, $onlineFileProcessItem) {
      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO=new StoreDAO($this->dbConn);      
      $inArray = json_decode($content, true);
      
      $hasdetail   = 'N';
      $hasheader   = 'N';
      $storeProd = '';                 	   	

//      echo "<br>";
//      echo count($inArray);
//      echo "<br>";
        foreach($inArray as $key=>$row) {
//     	          echo $key . '   ' . $row . "<br>";
//                echo "<br>Here<br>";

                  // Set up Principal Parameters
                  
                  if($key == 'principalId') {
                        if(trim($row) == 290) {
                        	   $incomingPrin      = 290;
                             $capBy             = "BMF-API";
                             $stripDelNme       = 'N'	;
                             $updProd           = 'Y';
                             $insProd           = 'Y';
                             $chainLook         = 'GENERIC CHAIN';
                             $specFields        = 1;
                             $specField1id      = 444; 
                        } elseif(trim($row) == 216)	{
                             $incomingPrin     = 216;
                             $capBy            = "Shopify-API";
                             $stripDelNme      = 'Y'	;
                             $updProd          = 'N';
                             $insProd          = 'Y';
                             $chainLook        = 'SC';
                             $specFields       = 2;
                             $specField1id     = '253';
                             $specField1Value  = 'THETEQ';  ;
                             $specField2id     = '257';
                             $specField2Value  = "CW";
                            
                        } else {
                             $eTO->type = FLAG_ERRORTO_ERROR;
                             $eTO->description = "Unknown Principal in API File";
                             return $eTO;                        	
                        }
                        /*******************
                         *   ORDER HEAD
                         ******************/
                        $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                        $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                        $postingOrdersHoldingTO->principalUid = $incomingPrin;
                        $postingOrdersHoldingTO->updateProduct= $updProd;
                        $postingOrdersHoldingTO->insertProduct= $insProd;
                        $postingOrdersHoldingTO->skipInvoiceComputationCheck="Y";
                        $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                        $postingOrdersHoldingTO->dataSource = DS_API;
                        $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                        $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                        $postingOrdersHoldingTO->requestedDeliveryDate = "";
                        $postingOrdersHoldingTO->capturedBy = $capBy;
                        $postingOrdersHoldingTO->additionalDetails = '';
                        $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                  } 
                  if($key == 'reference_number')  {
                  	   if($incomingPrin == 290) {
                  	   	
                  	   	    $stpos = strpos($row,"M") + 1 ;
                  	   	
                            $postingOrdersHoldingTO->documentNo       = trim(substr($row,$stpos,10));  //use provided doc no
                            $postingOrdersHoldingTO->clientDocumentNo = trim($row);
                            $postingOrdersHoldingTO->vendorReference  = trim($row);                   	   	
                  	   } else {
                            $postingOrdersHoldingTO->clientDocumentNo = trim($row);
                            $postingOrdersHoldingTO->documentNo = '';
                       }
                  }                       
                  if($key == 'customer_account_code') {
                       if($incomingPrin == 290) {
                       	    $bmAccNo = trim($row); 
                  	   } else {
                  	   	    $postingOrdersHoldingTO->oldAccount = '';
                  	   	    $postingOrdersHoldingTO->storeLookupRef = "" ; 
                  	   	    $postingOrdersHoldingTO->debtorsStoreIdentifier = "";
                  	   	    $postingOrdersHoldingTO->salesAgentStoreIdentifier = "";
                  	   	    $specField1Value = "THETEQ"; 
                       }  
                  }                     
                  if($key == 'customer_name') {
                        $postingOrdersHoldingTO->shipToName = trim($row);
                        $postingOrdersHoldingTO->deliverName = trim($row);
                        $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = $stripDelNme;
                        $postingOrdersHoldingTO->enforceSameDepot = "N";
                        $postingOrdersHoldingTO->chainLookupRef = $chainLook;
                        $bm02Store = str_replace(' ','',trim($row));
                        
                        // Create the StoreTO
                        $postingStoreTO = new PostingStoreTO;
                        $postingStoreTO->DMLType = "INSERT";
                        $postingStoreTO->principalStoreUId = "";
                        $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                        $postingStoreTO->deliverName = trim($row);
                        $postingStoreTO->billName = $postingStoreTO->deliverName;
                    
                        $postingStoreTO->vatNumber = ''; 

                        $postingStoreTO->depot = ""; // this will be set by the processing script
                        $postingStoreTO->deliveryDay = "8";
                        $postingStoreTO->noVAT = 0;
                        $postingStoreTO->onHold = "0";
                        $postingStoreTO->chain = ""; // this will be set by the processing script
                        $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                        $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                        $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                        $postingStoreTO->ownedBy = '';
                  }      
                  if($key == 'physical_address_1') {
                         $postingStoreTO->deliverAdd1 = trim($row);
                         $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1; 
                  }
                  if($key == 'physical_address_2') {
                         $postingStoreTO->deliverAdd2 = trim($row);
                         $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
                  }                                      
                  if($key == 'physical_address_3') {
                          $add3_1 = $postingStoreTO->deliverAdd3 = trim($row);
                  }          
                  if($key == 'physical_address_4') {
                        $postingStoreTO->deliverAdd3 = $add3_1 . '   ' . trim($row);
                        $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
                  }          
                  if($key == 'region') {
                        if(in_array($lineArr[$wareHouse], array('Western Cape'))) {
                             $deplook = 'CPT' ;
                        } elseif(in_array($lineArr[$wareHouse], array('Gauteng','Free State','North West','Limpopo'))) {
                             $deplook = 'CPT' ;
                        } elseif(in_array($lineArr[$wareHouse], array('KwaZulu-Natal'))) {
                             $deplook = 'CPT';
                        } else {
                    	       $deplook = 'CPT';                    	
                        }
                        $savedWarehouse = $deplook;               	
                  }
                  if($key == 'shipping') {
                       if(preg_replace('/[\xEF\xBB\xBF\x22]/', '', trim($row) > 0)) {
                         $addShipping = 'Y';
                         $shippingCharge = trim($row);
                    } else {
                         $addShipping = 'N';
                         $shippingCharge = 0;
                    } 
                  }  
                  if($key == 'order_discount_type') {
                       $postingOrdersHoldingTO->offInvoiceDiscountType = "A";
                  }
                  if($key == 'order_discount_amount') {
                       $postingOrdersHoldingTO->offInvoiceDiscount = trim($row);
                  }
                  if($key == 'email_address') {
                       $postingStoreTO->emailAdd = trim($row);
                  }
                  if($key == 'contact_number') {
                       $postingStoreTO->telNo1    = trim($row);
                  }
                  if($key == 'purchase_order_number') {
                 	     $postingOrdersHoldingTO->reference = substr(trim($row),0,19);
                  }
                  if($key == 'order_date') {
                 	     if(strLen(trim($row)) == 6 ) {
                             $postingOrdersHoldingTO->orderDate =  "20" . substr(trim($row),0,2) . "-" . substr(trim($row),3,2) . "-" . substr(trim($row),6,2);
                 	     } else {
                             $postingOrdersHoldingTO->orderDate =  trim($row);
                 	     }
                 	     if ($postingOrdersHoldingTO->orderDate === false) {
                             $eTO->type = FLAG_ERRORTO_ERROR;
                             $eTO->description = "Order date invalid format or empty";
                             $eTO->identifier = ET_CUSTOMER;
                             return $eTO;
                       }
                  }
                  if($key == 'required_date') {
                  } 
                  if($key == 'delivery_instructions') {
                       $postingOrdersHoldingTO->deliveryInstructions = trim($row);
                  }
                  if($key == 'document_type') {
                       $bmDocType = trim($row);
                       $postingOrdersHoldingTO->additionalDetails = trim($row);
                  }
                  if($key == 'detail_lines') {
                  	
                       if(trim($bmDocType) == 'INVOICE') {
                       	
                       	    $postingOrdersHoldingTO->oldAccount = "IN". trim($bmAccNo);
                            $postingOrdersHoldingTO->debtorsStoreIdentifier = "";
                            $postingOrdersHoldingTO->salesAgentStoreIdentifier = $postingOrdersHoldingTO->oldAccount; //same as old acc.
                            $postingOrdersHoldingTO->storeLookupRef   = "IN". trim($bmAccNo);
                            $specField1Value = "IN". trim($bmAccNo);
                       } else {
                       	
                       	    if(trim($bmAccNo) == 'BMAR002') {
                                  $postingOrdersHoldingTO->oldAccount = $bm02Store;
                                  $postingOrdersHoldingTO->debtorsStoreIdentifier = "";
                                  $postingOrdersHoldingTO->salesAgentStoreIdentifier = $postingOrdersHoldingTO->oldAccount; //same as old acc.
                                  $postingOrdersHoldingTO->storeLookupRef   = $bm02Store;
                                  $specField1Value = $bm02Store;
                       	    } else {
                                  $postingOrdersHoldingTO->oldAccount = trim($bmAccNo);
                                  $postingOrdersHoldingTO->debtorsStoreIdentifier = "";
                                  $postingOrdersHoldingTO->salesAgentStoreIdentifier = $postingOrdersHoldingTO->oldAccount; //same as old acc.
                                  $postingOrdersHoldingTO->storeLookupRef   = trim($bmAccNo);
                                  $specField1Value = trim($bmAccNo);
                       	    }
                       	
                   	
                       }
                       
                       $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;
                       $postingStoreTO->updatePrincipalStore = 'Y';
                        
                       $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
                  	
                  	
                  	    if($specFields == 1 || $specFields == 2) {
                    
                               $postingSpecialFieldTO = new PostingSpecialFieldTO;
                               $postingSpecialFieldTO->DMLType = "INSERT";
                               $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                               $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                               $postingSpecialFieldTO->fielduid = $specField1id ;   // Store Special Field
                               $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                               $postingSpecialFieldTO->value = $specField1Value;
                               $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;                        	
                        }
                        if($specFields == 2) {
                               $postingSpecialFieldTO = new PostingSpecialFieldTO;
                               $postingSpecialFieldTO->DMLType     = "INSERT";
                               $postingSpecialFieldTO->principal   = $postingStoreTO->principal;
                               $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                               $postingSpecialFieldTO->fielduid    = $specField2id;   // Store Special Field
                               $postingSpecialFieldTO->entityUId   = ""; // the processor will assign this when store is created
                               $postingSpecialFieldTO->value       = $specField2Value;
                               $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;
                        }
                        if(count($inArray['detail_lines'] == 0) && $bmDocType == 'INVOICE') {
                           if($hasheader == 'N') {
                                   $postingOrdersHoldingTO->depotLookupRef = "BM100";
                      
                                   $arrTO[] = $postingOrdersHoldingTO;
                                   $hasheader = 'Y';
                           }
                        } else {                 	
                 	
                 	          $clientLineNo = 0;
                 	     
                            foreach($inArray['detail_lines'] as $dRow ) { 
                                 foreach($dRow as $dKey=>$dline) {
                                  
                                    if($dKey == 'product_code') {
                                                      	
                                    	    if($storeProd <> trim($dline) && $storeProd <> '') {
                                               if($hasheader == 'N') {
                                                     $postingOrdersHoldingTO->depotLookupRef = $savedWarehouse;
                                                                                                          
                                                     $arrTO[] = $postingOrdersHoldingTO;
                                                     $hasheader = 'Y';
                                               }
                                               $clientLineNo++;
                                    	    	    // Save OrdersHoldingDetail for line
                                               $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                                               $postingOrdersHoldingDetailTO->clientLineNo        = $clientLineNo .'000';
                                               $postingOrdersHoldingDetailTO->productCode         = $savedProdCode;
                                               $postingOrdersHoldingDetailTO->productName         = $savedProdName;
                                               $postingOrdersHoldingDetailTO->quantity            = $savedQty;
                                               $postingOrdersHoldingDetailTO->listPrice           = $savedListPrice;
                                               $postingOrdersHoldingDetailTO->discountValue       = $savedDiscountValue;
                                               $postingOrdersHoldingDetailTO->vatRate             = $savedVatRate;
                                               $postingOrdersHoldingDetailTO->listPrice           = $savedListPrice;
                                               $postingOrdersHoldingDetailTO->discountValue       = $savedDiscountValue;
                                               $postingOrdersHoldingDetailTO->nettPrice           = $savedNettPrice;
                                               $postingOrdersHoldingDetailTO->extPrice            = $savedExtPrice;
                                               $postingOrdersHoldingDetailTO->vatRate             = $savedVatRate;
                                               $postingOrdersHoldingDetailTO->vatAmount           = $savedVatAmount;
                                               $postingOrdersHoldingDetailTO->totalPrice          = $savedlnTotal;
                                               $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                                               $postingOrdersHoldingDetailTO->pallets             = 0;
                                               $postingOrdersHoldingDetailTO->overridePriceType   = $savedOverRidePrice ;
                                               
                                               $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
                                        }
                                        $savedProdCode = trim($dline);
                                        $storeProd     = trim($dline);
                                    }
                                    if($dKey == 'product_description') {
                                    	     if($incomingPrin == 290) {
                                    	         $savedProdName = trim($dline); 	
                                    	     } elseif($incomingPrin == 216) {
                                               if(str_replace(" ","",str_replace("'","",trim($dline))) == "MonthlyMembersSubscription") {
                                          	       $savedProdName = '2725000';
                                                } 	     	
                                    	     } else {
                                    	     	       $savedProdName = trim($dline);
                                    	     }
                                    }
                                    if($dKey == 'order_quantity') {
                                         $savedQty = trim($dline);
                                    }
                                    if($dKey == 'selling_price') {
                                         $savedListPrice = trim($dline);
                                    }
                                    if($dKey == 'line_discount_type') {
                                    }
                                    if($dKey == 'line_discount_amount') {
                                    	    $savedDiscountValue = trim($dline);
                                    }
                                    if($dKey == 'line_nett_price') {
                                       	$netLinePrice = trim($dline);
                                    }
                                    if($dKey == 'vat_rate') {
                                          $savedVatRate = trim($dline);
                                    	     if($incomingPrin == 290) {
                                    	     	     $savedNettPrice     = $savedListPrice - $savedDiscountValue;
                                    	     	     $savedExtPrice      = ($savedListPrice - $savedDiscountValue) * $savedQty;
                                    	     	     $savedVatAmount     = round($savedExtPrice * $savedVatRate / 100 ,2);
                                    	     	     $savedlnTotal       = round($savedExtPrice + $savedVatAmount,2);
                                    	     	     $savedOverRidePrice = '2';
                                    	     } elseif($incomingPrin == 216) {
                                                $savedNettPrice     = round($netLinePrice * 100/(100+$savedVatRate),2) ;
                                                $savedExtPrice      = $savedNettPrice * $savedQty;
                                                $savedlnTotal       = $netLinePrice   * $savedQty;
                                                $savedVatAmount     = $netLinePrice - $savedNettPrice;
                                                $savedOverRidePrice = '2';                                           } 	     	
                                    	     } else {
                                    	     	     $savedNettPrice     = $savedListPrice - $savedDiscountValue;
                                    	     	     $savedExtPrice      = ($savedListPrice - $savedDiscountValue) * $savedQty;
                                    	     	     $savedVatAmount     = round($savedExtPrice * $savedVatRate / 100 ,2);
                                    	     	     $savedlnTotal       = round($savedExtPrice + $savedVatAmount,2);
                                    	     	     $savedOverRidePrice = '';
                                    	     }
                                    if($dKey == 'warehouse') {
                                    	     if($incomingPrin == 290) {
                                    	         $savedWarehouse = trim($dline);
                                          }
                                    }
                                    if($dKey == 'items_per_case') {
                                    	     if($incomingPrin == 290) {
                                    	         $savedProdCode = $savedProdCode . "-" . trim($dline);
                                          }
                                    }      
                     
                                }
                                $hasdetail = 'Y';
                            }
                       }  
                  }
        }
        
        if($bmDocType == 'INVOICE') {
        	
                // Save OrdersHoldingDetail for Dummy Invoice
                $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                $postingOrdersHoldingDetailTO->clientLineNo        = $clientLineNo .'1000';
                $postingOrdersHoldingDetailTO->productCode         = 'DUMMY01';
                $postingOrdersHoldingDetailTO->productName         = 'Dummy Product for Invoices';
                $postingOrdersHoldingDetailTO->quantity            =  1;
                $postingOrdersHoldingDetailTO->listPrice           =  0;
                $postingOrdersHoldingDetailTO->discountValue       =  0 ;
                $postingOrdersHoldingDetailTO->vatRate             =  0 ;
                $postingOrdersHoldingDetailTO->listPrice           =  0 ;
                $postingOrdersHoldingDetailTO->discountValue       =  0 ;
                $postingOrdersHoldingDetailTO->nettPrice           =  0 ;
                $postingOrdersHoldingDetailTO->extPrice            =  0 ;
                $postingOrdersHoldingDetailTO->vatRate             =  0 ;
                $postingOrdersHoldingDetailTO->vatAmount           =  0 ;
                $postingOrdersHoldingDetailTO->totalPrice          =  0 ;
                $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                $postingOrdersHoldingDetailTO->pallets             = 0;
                $postingOrdersHoldingDetailTO->overridePriceType   = $savedOverRidePrice ;
                
                $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;                       	
                
                $hasdetail = 'Y';
        } else {
        
             if($hasheader == 'N') {
                   $postingOrdersHoldingTO->depotLookupRef = $savedWarehouse;
                      
                   $arrTO[] = $postingOrdersHoldingTO;
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;    	
                   $hasheader = 'Y';
             }
             $clientLineNo++;
             // Save OrdersHoldingDetail for line
             $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
             $postingOrdersHoldingDetailTO->clientLineNo        = $clientLineNo .'000';
             $postingOrdersHoldingDetailTO->productCode         = $savedProdCode;
             $postingOrdersHoldingDetailTO->productName         = $savedProdName;
             $postingOrdersHoldingDetailTO->quantity            = $savedQty;
             $postingOrdersHoldingDetailTO->listPrice           = $savedListPrice;
             $postingOrdersHoldingDetailTO->discountValue       = $savedDiscountValue;
             $postingOrdersHoldingDetailTO->vatRate             = $savedVatRate;
             $postingOrdersHoldingDetailTO->listPrice           = $savedListPrice;
             $postingOrdersHoldingDetailTO->discountValue       = $savedDiscountValue;
             $postingOrdersHoldingDetailTO->nettPrice           = $savedNettPrice;
             $postingOrdersHoldingDetailTO->extPrice            = $savedExtPrice;
             $postingOrdersHoldingDetailTO->vatRate             = $savedVatRate;
             $postingOrdersHoldingDetailTO->vatAmount           = $savedVatAmount;
             $postingOrdersHoldingDetailTO->totalPrice          = $savedlnTotal;
             $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
             $postingOrdersHoldingDetailTO->pallets             = 0;
             $postingOrdersHoldingDetailTO->overridePriceType   = $savedOverRidePrice ;
              
             $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
              
             if($hasdetail == 'Y' && $addShipping == 'Y' ) {
                   $clientLineNo++;
                   $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                   $postingOrdersHoldingDetailTO->clientLineNo =  $clientLineNo;
                   $postingOrdersHoldingDetailTO->listPrice     = $shippingCharge * (100/(100+$postingOrdersHoldingDetailTO->vatRate));
                   
                   $postingOrdersHoldingDetailTO->quantity      = 1;
                   $postingOrdersHoldingDetailTO->discountValue = 0;
                   $postingOrdersHoldingDetailTO->nettPrice     = round($shippingCharge * (100/(100+VAL_VAT_RATE_TBLSTD)),4);  ;
                   $postingOrdersHoldingDetailTO->extPrice      = $postingOrdersHoldingDetailTO->quantity * $postingOrdersHoldingDetailTO->nettPrice ;
                   $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                   $postingOrdersHoldingDetailTO->vatAmount     = $postingOrdersHoldingDetailTO->extPrice * $postingOrdersHoldingDetailTO->vatRate / 100 ;
                   $postingOrdersHoldingDetailTO->totalPrice    = $shippingCharge;
                   $postingOrdersHoldingDetailTO->productCode   = '2720000';
                   $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                   $postingOrdersHoldingDetailTO->pallets       = 0;
                   $postingOrdersHoldingDetailTO->productName   = 'Delivery Fees';
                   $postingOrdersHoldingDetailTO->overridePriceType = "2" ;
                   $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
              }
        }     
              
         if($hasdetail =='Y') {
                     $eTO->type = FLAG_ERRORTO_SUCCESS;
                     $eTO->description = "Successful";
                     $eTO->object = $arrTO;
                     return $eTO;
         } else {
                     $eTO->type = FLAG_ERRORTO_ERROR;
                     $eTO->description = "Invalid File or no Details";
                     $eTO->identifier = ET_CUSTOMER;
                     return $eTO;
         } 
    }    

// ********************************************************************************************************************************
    function adaptorTOH_BCS_SYKNAMO($content, $onlineFileProcessItem) {

      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;

      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
      $arrTO=array();
      $processingLine=0;
      
      $headerarray=array('Comment');

      $fileArr = explode("\n",$content);
      
      $validfile = 0;
//      echo count($fileArr);
      
      if (in_array(trim(substr($fileArr[0],0,7)), $headerarray))   {
         // validate first line of file
         $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");
                  
         for ($x = 0; $x <= 29; $x++) {
         	    if(trim($lineArr[$x])=='Customer code') {
         	       $Customer_code_offset = $x;
         	       $validfile++;                       // 0
              } elseif(trim($lineArr[$x])=='ID') {
                 $id = $x;
                 $validfile++;                       // 1
              } elseif(trim($lineArr[$x])=='Comment') {
                 $Comment = $x;
                 $validfile++;                       // 2
              } elseif(trim($lineArr[$x])=='Date') {
                 $order_date = $x;
                 $validfile++;                       // 3
              } elseif(trim($lineArr[$x])=='Product code') {
                 $product_code = $x;
                 $validfile++;                       // 4
              } elseif(trim($lineArr[$x])=='Currency') {   //5
                 $currency = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Exchange rate') {    //6
                 $exchange_rate = $x;
                 $validfile++;
              } elseif(trim($lineArr[$x])=='Quantity') {
                 $quantity = $x;
                 $validfile++;                       // 7
              } elseif(trim($lineArr[$x])=='Discount') {
                 $discount = $x;
                 $validfile++;                       // 8               
              } elseif(trim($lineArr[$x])=='Reference') {
                 $reference = $x;
                 $validfile++;                       // 9
              } elseif(trim($lineArr[$x])=='User') {
                 $skyuser = $x;
                 $validfile++;                       // 10
              } elseif(trim($lineArr[$x])=='Customer name') {
                 $Customer_name = $x;
                 $validfile++;                       // 11
              } elseif(trim($lineArr[$x])=='List price') {
                 $List_price = $x;
                 $validfile++;                       // 12
              } elseif(trim($lineArr[$x])=='Price') {
                 $Price = $x;
                 $validfile++;                       // 13	
              } elseif(trim($lineArr[$x])=='Price list') {
                 $Price_list = $x;
                 $validfile++;                       // 14
              } elseif(trim($lineArr[$x])=='Prices include tax') {
                 $Prices_include_tax = $x;
                 $validfile++;                       // 15
              } elseif(trim($lineArr[$x])=='Product name') {
                 $Product_name = $x;
                 $validfile++;                       // 16
              } elseif(trim($lineArr[$x])=='Quote') {
                 $Quote = $x;
                 $validfile++;                       // 17	
              } elseif(trim($lineArr[$x])=='Recipients') {
                 $Recipients = $x;
                 $validfile++;                       // 18
              } elseif(trim($lineArr[$x])=='Signature') {
                 $Signature = $x;
                 $validfile++;                       // 19
              } elseif(trim($lineArr[$x])=='Tax') {
                 $Tax = $x;
                 $validfile++;                       // 20	
              } elseif(trim($lineArr[$x])=='Unit name') {
                 $Unit_name = $x;
                 $validfile++;                       // 21
              } elseif(trim($lineArr[$x])=='Unit price') {
                 $Unit_price = $x;
                 $validfile++;                       // 22
              } elseif(trim($lineArr[$x])=='Warehouse') {
                 $Warehouse = $x;
                 $validfile++;                       // 23
              } elseif(trim($lineArr[$x])=='Additional Instructions/Feedback:') {
                 $addin = $x;
                 $validfile++;	                      // 24
              } elseif(trim($lineArr[$x])=='Email Recipient') {
                 $eRec = $x;
                 $validfile++;                        // 25
              } elseif(trim($lineArr[$x])=='Expected Delivery Date') {
                 $edDate = $x;
                 $validfile++;                        // 26
              } elseif(trim($lineArr[$x])=='Order Number :') {
                 $orderNumber = $x;
                 $validfile++;                       // 27
              }   
          }
          
          
          //         echo "<br>";
//         echo $validfile;
//         echo "<br>";
 
          if($validfile <> 28 ) {
             $eTO->type = FLAG_ERRORTO_ERROR;
             $eTO->description = "Check file - Order fields missing!";
             return $eTO;
          }          
          if(count($lineArr) <= 25 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Invalid Column Count - expecting 22 columns Found " . count($lineArr);
            return $eTO;
          }          
          unset($fileArr[0]);
      } else {
         $eTO->type = FLAG_ERRORTO_ERROR;
         $eTO->description = "First line expected to be a header!";
         return $eTO;
      }
      
      $hasheader = 'N';
      $hasdetail = 'N';
      $l = 0;
      
      foreach ($fileArr as $key=>$line) {
      	
//      	echo "<br>";
      	
//      	echo substr($fileArr[$l],strpos($fileArr[$l],',',0)+1,7);
      	
      	$l++;
      	
       	  if (strpos($fileArr[$l],',',0) <> FALSE || trim(substr($fileArr[$l],strpos($fileArr[$l],',',0)+1,7)) <> '') {
      	
     	         // convert line to CSV
   	           $lineArr=str_getcsv($line, ",", '"', "\\");
               
//               print_r($lineArr);
       
               if (  $hasheader == 'N') {
                   // Determine Principal code from first line of order      	
                        $currPrincipal = 428;	
                   /*******************
                   *   ORDER HEAD
                   *******************/
                   $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                   $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                   $postingOrdersHoldingTO->principalUid = $currPrincipal;
                   $postingOrdersHoldingTO->updateProduct="Y";
                   $postingOrdersHoldingTO->insertProduct="Y";
                   $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                   $postingOrdersHoldingTO->dataSource = DS_EDI;
                   $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                   $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                   $postingOrdersHoldingTO->requestedDeliveryDate = "";
                   $postingOrdersHoldingTO->capturedBy = substr(trim($lineArr[$skyuser]),0,20);
                   $postingOrdersHoldingTO->deliveryInstructions = trim($lineArr[$Comment]);
                   $postingOrdersHoldingTO->depotLookupRef = trim($lineArr[$Warehouse]);    
                   $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                   $postingOrdersHoldingTO->offInvoiceDiscountType = '';       
                   $postingOrdersHoldingTO->orderDate =  $lineArr[$order_date];
                  
                   if ($postingOrdersHoldingTO->orderDate === false) {
                            $eTO->type = FLAG_ERRORTO_ERROR;
                            $eTO->description = "Order date invalid format or empty";
                            $eTO->identifier = ET_CUSTOMER;
                            return $eTO;
                   }
                   $postingOrdersHoldingTO->shipToName = $lineArr[$Customer_name];
                   $postingOrdersHoldingTO->deliverName = $lineArr[$Customer_name];
                   $postingOrdersHoldingTO->clientDocumentNo = trim($lineArr[$id]);
                   $postingOrdersHoldingTO->documentNo = 'BC' . str_pad(trim($lineArr[$id]),6,'0',STR_PAD_LEFT);
                   $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV; 	
                   $postingOrdersHoldingTO->storeLookupRef = $lineArr[$Customer_code_offset];
                   $postingOrdersHoldingTO->enforceSameDepot = "N";
                   $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                   $postingOrdersHoldingTO->oldAccount = '';
                   
                   if(trim($lineArr[$reference])=='') { $ref = '*'; } else {$ref = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', trim($lineArr[$reference]))),0,19);}
      
                   $postingOrdersHoldingTO->reference = substr(trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22]/', '', trim($lineArr[$orderNumber]))),0,19);
                   
                   // Create the StoreTO
                   $postingStoreTO = new PostingStoreTO;
                   $postingStoreTO->DMLType = "INSERT";
                   $postingStoreTO->principalStoreUId = "";
                   $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                   $postingStoreTO->deliverName = $lineArr[$Customer_name];
                   $postingStoreTO->deliverAdd1 = '';
                   $postingStoreTO->deliverAdd2 = '';
                   $postingStoreTO->deliverAdd3 = '';
                   $postingStoreTO->billName = $postingStoreTO->deliverName;
                   $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
                   $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
                   $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
                   $postingStoreTO->vatNumber = ''; 
                   $postingStoreTO->telNo1    = ''; 
                   $postingStoreTO->depot = ""; // this will be set by the processing script
                   $postingStoreTO->deliveryDay = "8";
                   $postingStoreTO->noVAT = 0;
                   $postingStoreTO->onHold = "0";
                   $postingStoreTO->chain = ""; // this will be set by the processing script
                   $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                   $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                   $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->ownedBy = $postingOrdersHoldingTO->vendorUid;
                   $postingStoreTO->oldAccount = $lineArr[$Customer_code_offset];
       
                   $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
                   
                   
                   // lookup special field(s) - enforce this specific one
                   $postingSpecialFieldTO = new PostingSpecialFieldTO;
                   $postingSpecialFieldTO->DMLType = "INSERT";
                   $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                   $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                   $postingSpecialFieldTO->fielduid = "577"; // Special Field
                   $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                   $postingSpecialFieldTO->value = $lineArr[$Customer_code_offset];
                   $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;
                   $hasheader = 'Y';
                   
                    $arrTO[] = $postingOrdersHoldingTO;
               }       
               /******************
               *   ORDER DETAILS
               *****************/
               if(substr($lineArr[$product_code],0,1) <> ".") {
                   $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                  
                   $postingOrdersHoldingDetailTO->listPrice     = round($lineArr[$Unit_price],2);
                   $postingOrdersHoldingDetailTO->discountValue = "0000.00";
                   $postingOrdersHoldingDetailTO->quantity      = $lineArr[$quantity];
                   $postingOrdersHoldingDetailTO->nettPrice     = round($lineArr[$Unit_price],2);
                  
                   $postingOrdersHoldingDetailTO->extPrice      = $lineArr[$quantity] * round($lineArr[$Unit_price],2);
                   $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                   $postingOrdersHoldingDetailTO->vatAmount     = $lineArr[$quantity] * round($lineArr[$Unit_price],2) * VAL_VAT_RATE_TBLSTD /100;
                   $postingOrdersHoldingDetailTO->totalPrice    = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount;
                   $postingOrdersHoldingDetailTO->productCode   = $lineArr[$product_code];
                   $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                   $postingOrdersHoldingDetailTO->pallets       = 0;
                   $postingOrdersHoldingDetailTO->productName   =  $lineArr[$product_code];;
                   $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
                   $hasdetail = 'Y';
               }     
          }
      }     
      if($hasdetail =='Y') {
           $eTO->type = FLAG_ERRORTO_SUCCESS;
           $eTO->description = "Successful";
           $eTO->object = $arrTO;
           return $eTO;
      } else {
     	    $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Invalid File or no Details";
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
      }       
    }
// --------------------------------------------------------------------------------------------------------------------------------
    function adaptorTOH_UMATIE($content, $onlineFileProcessItem) {
      // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
      global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
      
      $eTO = new ErrorTO;
      if (!isset($storeDAO)) $storeDAO=new StoreDAO($this->dbConn);      
      $inArray = json_decode($content, true);
      
  echo "<pre>";
 print_r($inArray);
 echo "<br>";
      
      $hasdetail   = 'N';
//      echo "<br>";
      
//      echo count($inArray);
//      echo "<br>";
      

         foreach($inArray[0] as $key=>$row) {
      	          echo $key . '   ' . $row . "<br>";
                  echo "<br>Here<br>";
                  if($key == 'SalesOrder') {

                        	  
                            /*******************
                             *   ORDER HEAD
                             ******************/

                             $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                             $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                             $postingOrdersHoldingTO->updateProduct="N";
                             $postingOrdersHoldingTO->insertProduct="N";
                             $postingOrdersHoldingTO->skipInvoiceComputationCheck="Y";
                             $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                             $postingOrdersHoldingTO->dataSource = DS_WS;
                             $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                             $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                             $postingOrdersHoldingTO->requestedDeliveryDate = $row['DeliveryDate'];
                             $postingOrdersHoldingTO->capturedBy = "UMATIE";
                             $postingOrdersHoldingTO->additionalDetails = '';
                             $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
                             $postingOrdersHoldingTO->principalUid = $onlineFileProcessItem["principal_uid"];
                             $postingOrdersHoldingTO->clientDocumentNo = ltrim($row['InvoiceNumber'],'0');
                             $postingOrdersHoldingTO->documentNo = ltrim($row['InvoiceNumber'],'0');

                             $postingOrdersHoldingTO->orderDate = $row['OrderDate'];
                             if ($postingOrdersHoldingTO->orderDate === false) {
                                   $eTO->type = FLAG_ERRORTO_ERROR;
                                   $eTO->description = "Order date invalid format or empty";
                                   $eTO->identifier = ET_CUSTOMER;
                                   return $eTO;
                             }
                             $postingOrdersHoldingTO->deliveryInstructions = '';
                             $postingOrdersHoldingTO->depotLookupRef = ''; 
                  }          
                  if($key == 'Customer') {    
                  	
                  	                        	  echo $row[0]['Customer'];
                        	  echo "<br>Sub<br>";
                       	    echo $row[0]['CustomerCode'];
                        	  echo "<br>Sub<br>";
                  	
                  	
                  	                         
                        $postingOrdersHoldingTO->shipToName     = trim($row[0]['Customer']);
                        $postingOrdersHoldingTO->deliverName    = trim($row[0]['Customer']);
                        $postingOrdersHoldingTO->storeLookupRef = "" ; 
                        $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = "N";
                        $postingOrdersHoldingTO->enforceSameDepot = "N";
                        $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                        $postingOrdersHoldingTO->oldAccount = '';
                        $postingOrdersHoldingTO->salesAgentStoreIdentifier = "";
                        
                        // Create the StoreTO
                        $postingStoreTO = new PostingStoreTO;
                        $postingStoreTO->DMLType = "INSERT";
                        $postingStoreTO->principalStoreUId = "";
                        $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                        $postingStoreTO->deliverName = trim($row[0]['Customer']);
                        $postingStoreTO->billName = $postingStoreTO->deliverName;
                    
                        $postingStoreTO->vatNumber = trim($row[0]['CompanyTaxNumber']);

                        $postingStoreTO->depot = ""; // this will be set by the processing script
                        $postingStoreTO->deliveryDay = "8";
                        $postingStoreTO->noVAT = 0;
                        $postingStoreTO->onHold = "0";
                        $postingStoreTO->chain = ""; // this will be set by the processing script
                        $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                        $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                        $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
                        $postingStoreTO->ownedBy = '';
                        $postingStoreTO->oldAccount = '';
                        
                        $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
                    
                        $postingSpecialFieldTO = new PostingSpecialFieldTO;
                        $postingSpecialFieldTO->DMLType = "INSERT";
                        $postingSpecialFieldTO->principal = $postingStoreTO->principal;
                        $postingSpecialFieldTO->deliverName = $postingOrdersHoldingTO->deliverName;
                        $postingSpecialFieldTO->fielduid = 582;   // Store Special Field
                        $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                        $postingSpecialFieldTO->value = trim($row[0]['CustomerCode']);

                  }
                  
                  if($key == 'OrderDetail') {
                       $arrTO[] = $postingOrdersHoldingTO;
                       $postingOrdersHoldingTO->postingSpecialFieldTOArr[] = $postingSpecialFieldTO;
                    
                    
                       foreach($inArray['OrderDetail'] as $dRow ) { 
                             $clientLineNo++;
                             
                             print_r[$dRow];
                             echo "<br>act<br>";                 	   	
                             foreach($dRow[0] as $dKey=>$dline) {
                                   echo $dKey . '   ' . $dline . "<br>";
                                  echo "<br>Here Detail<br>";
                                  if($dKey == 'OrderLine') {
                                      $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                                      $postingOrdersHoldingDetailTO->clientLineNo =  trim($dline); 	
                                  }
                                  if($dKey == 'StockCode') {                                    
                                       $postingOrdersHoldingDetailTO->productCode   = trim($dline);
                                  }      
                                  if($dKey == 'Description') {
                                       $postingOrdersHoldingDetailTO->productName   = trim($dline);	
                                  }
                                   if($dKey == 'OrderQty') {
                                       $postingOrdersHoldingDetailTO->quantity = trim($dline);	                      	
                                   }
                                   if($dKey == 'Price') {
                                        $postingOrdersHoldingDetailTO->listPrice = trim($dline);
                                        $postingOrdersHoldingDetailTO->vatRate   = VAL_VAT_RATE_TBLSTD;
                                    
                              	        $postingOrdersHoldingDetailTO->nettPrice   = trim($dline);
                                        $postingOrdersHoldingDetailTO->extPrice    = $postingOrdersHoldingDetailTO->quantity * $postingOrdersHoldingDetailTO->nettPrice ;
                                        $postingOrdersHoldingDetailTO->vatAmount   = $postingOrdersHoldingDetailTO->extPrice * VAL_VAT_RATE;
                                    
                                        $postingOrdersHoldingDetailTO->totalPrice  = $postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount ;

                                        $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                                        $postingOrdersHoldingDetailTO->pallets       = 0;
                          
                                        $postingOrdersHoldingDetailTO->overridePriceType = "2" ;
                                  }
                                  $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;
                                  
                                  $hasdetail = 'Y';
                                  
                             }
                       }
                  }
                 
         }
         
         print_r($postingOrdersHoldingTO);
         echo "E";
         die();
         
         
         if($hasdetail =='Y') {
                     $eTO->type = FLAG_ERRORTO_SUCCESS;
                     $eTO->description = "Successful";
                     $eTO->object = $arrTO;
                     return $eTO;
         } else {
                     $eTO->type = FLAG_ERRORTO_ERROR;
                     $eTO->description = "Invalid File or no Details";
                     $eTO->identifier = ET_CUSTOMER;
                     return $eTO;
         }
    }  


// --------------------------------------------------------------------------------------------------------------------------------       
   //  Vendor 43, Order File 
   // Convert a CSV file with orders or Credits
    
    function adaptorTOH_BERKLEYORDERS($content, $onlineFileProcessItem) {

         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
         global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
         
         $eTO = new ErrorTO;
         if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
            $arrTO=array();
             $processingLine=0; 
                   
             $headerarray=array('KWEDB_ID');

             $fileArr = explode("\n",$content);
      
             $validfile = 0;
             if (in_array(strtoupper(trim(substr($fileArr[0],0,8))), $headerarray))   {
                   // validate first line of file
                    $lineArr=str_getcsv($fileArr[0], "|", '"', "\\");
                  
                    for ($x = 0; $x <= 32; $x++) {
         	              if(strtoupper(trim($lineArr[$x]))=='KWEDB_ID') {   //0
                            $vendor = $x;
                            $validfile++;
         	              } elseif(strtoupper(trim($lineArr[$x]))=='AUTOINDEX') {   //1
                            $aIndex = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='ORDERNUM') {   //2
                            $documentNo = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='ORDERDATE') {   //3
                            $orderDate = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='DCLINK') {   //4
                            $dcLink = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='CUSTOMERCODE') {   //5
                            $storeCode = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='CUSTOMERPO') {   //6
                            $poNum = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='PHYSICAL1') {   //7
                            $phys1 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='PHYSICAL2') {   //8
                            $phys2 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='PHYSICAL3') {   //9
                            $phys3 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='PHYSICAL4') {   //10
                            $phys4 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='PHYSICAL5') {   //10
                            $phys5 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='PHYSICALPC') {   //12
                            $physPc = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='POSTAL1') {   //13
                            $post1 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='POSTAL2') {   //14
                            $post2 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='POSTAL3') {   //15
                            $post3 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='POSTAL4') {   //16
                            $post4 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='POSTAL5') {   //17
                            $post5 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='POSTALPC') {   //18
                            $postPc = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='IINVSETTLEMENTTERMSID') {    //19
                            $inVest1 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='STOCKID') {    //20
                            $stockId = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='WAREHOUSE') {   //21
                            $wareHouse = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='SKUCODE') {   //22
                            $prodCode = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='CDESCRIPTION') {   //23
                            $product = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='FLINEDISCOUNT') {    //24
                            $lineDiscount = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='FQUANTITY') {   //26
                            $quantity = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='FUNITPRICEEXCL') {
                            $exclPrice = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='FUNITPRICEINCL') {
                            $inclPrice = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='CLINENOTES') {
                            $lineNote = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='CLOTNUMBER') {
                            $lotNumber = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='DLOTEXPIRYDATE') {
                            $expDate = $x;
                            $validfile++;
                        } elseif(strtoupper(trim($lineArr[$x]))=='ILINEID') {
                            $lineID = $x;
                            $validfile++; 
                        }   
//                        echo $lineArr[$x];
//                        echo "<br>";
                    }
                    echo "<br>";
                    echo $validfile;
                    echo "<br>";
                    if($validfile <> 32 ) {
                          $eTO->type = FLAG_ERRORTO_ERROR;
                          $eTO->description = "Check file - Order / Claim fields missing!";
                          return $eTO;
                    }          
                    unset($fileArr[0]);
             } else {
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "First line expected to be a header!";
                  return $eTO;
             }
          
             $hasheader = 'N';
             $hasdetail = 'N';
             $savePON = '';
             unset($fileArr[0]);
             
             include_once ($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");
      
             foreach ($fileArr as $key=>$line) {
             	

             	
                   $trimLine = trim(str_replace(',',' ',$line))  ;         	
             	     // echo $line;
                   // echo "<br>";      	
                   // convert line to CSV
                   $lineArr=str_getcsv($trimLine, "|", '"', "\\");

           
                   if($lineArr[$documentNo] <> $savePON){
                   	
                   	    if($lineArr[$vendor] == 425) {
                           $prinId = 425;
                   	    } else {
                           $prinId = 426;	
                   	    }

                        // Check for incoming duplicate Numbers
                   
                        $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
                        $seDup = $MaintenanceDAO->checkForDuplicates($prinId, $lineArr[$documentNo]);
                   
                        if(count($seDup) > 0) {
                               $dupDocumentNumber = 'Y';  
                        } else {
                               $dupDocumentNumber = 'N'; 	
                        }
                        
                        if($dupDocumentNumber == 'Y') {
                               echo "<br>";
                               echo "Duplicate " . $lineArr[$documentNo] . "  - Not Processed" ;
                       //      echo "<br>";
                             continue ;
                        }
                        
                        /*******************
                         *   ORDER HEAD
                         *******************/
                         $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                         $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                         $postingOrdersHoldingTO->principalUid = $prinId;
                         $postingOrdersHoldingTO->updateProduct="N";
                         $postingOrdersHoldingTO->insertProduct="N";
                         $postingOrdersHoldingTO->vendorUid = $onlineFileProcessItem["vendor_uid"];
                         $postingOrdersHoldingTO->dataSource = DS_EDI;
                         $postingOrdersHoldingTO->incomingFile = basename($onlineFileProcessItem["file_being_processed"]);
                         $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                         $postingOrdersHoldingTO->requestedDeliveryDate = "";
                         $postingOrdersHoldingTO->capturedBy = "SQLORDERS";
                         $postingOrdersHoldingTO->deliveryInstructions = '';
                         $postingOrdersHoldingTO->salesAgentStoreIdentifier = '' ;
                         $postingOrdersHoldingTO->reference = $lineArr[$poNum];

                         $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                         $postingOrdersHoldingTO->offInvoiceDiscountType = '';  

                         $postingOrdersHoldingTO->depotLookupRef = $lineArr[$wareHouse];

                         $postingOrdersHoldingTO->orderDate =  $lineArr[$orderDate];
              
                         if ($postingOrdersHoldingTO->orderDate === false) {
                              $eTO->type = FLAG_ERRORTO_ERROR;
                              $eTO->description = "Order date invalid format or empty";
                              $eTO->identifier = ET_CUSTOMER;
                              return $eTO;
                         }
                         $postingOrdersHoldingTO->shipToName = $lineArr[$store];
                         $postingOrdersHoldingTO->deliverName = $lineArr[$store];
                         $postingOrdersHoldingTO->clientDocumentNo = $lineArr[$documentNo];
                         $postingOrdersHoldingTO->documentNo = trim(substr($lineArr[$documentNo],3,6));
                         $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV; 
                         $postingOrdersHoldingTO->storeLookupRef = trim($lineArr[$storeCode]);
                         $postingOrdersHoldingTO->enforceSameDepot = "Y";
                         $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                         $postingOrdersHoldingTO->oldAccount = '';
               
                         $arrTO[] = $postingOrdersHoldingTO;
                         $savePON = $lineArr[$documentNo];
                         
                         $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
                         $seDup = $MaintenanceDAO->addToDocControl($prinId, trim(preg_replace('/[\x22\x27\x2C\x0A\x0D]/', '', $lineArr[$documentNo])), basename($onlineFileProcessItem["file_being_processed"]));

                         
                   }
                   
                   /******************
                    *   ORDER DETAILS
                    *****************/
     
                   $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                   
                   $discountVal = $lineArr[$exclPrice] * $lineArr[$lineDiscount] / 100;
                   $netPrice    = $lineArr[$exclPrice] - $discountVal;
                   $postingOrdersHoldingDetailTO->clientLineNo  = $lineArr[$lineID] ;
                   $postingOrdersHoldingDetailTO->listPrice     = $lineArr[$exclPrice] ;
                   $postingOrdersHoldingDetailTO->quantity      = $lineArr[$quantity];
                   $postingOrdersHoldingDetailTO->discountValue = $discountVal;
                   $postingOrdersHoldingDetailTO->discountReference = $lineArr[$lineDiscount];
                   $postingOrdersHoldingDetailTO->nettPrice     = round($netPrice, 2);
                   $postingOrdersHoldingDetailTO->extPrice      = round($netPrice * $lineArr[$quantity]);
                   $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                   $postingOrdersHoldingDetailTO->vatAmount     = round($netPrice * $lineArr[$quantity] * VAL_VAT_RATE_TBLSTD,2) ;
                   $postingOrdersHoldingDetailTO->totalPrice    = round($netPrice * $lineArr[$quantity] + $postingOrdersHoldingDetailTO->vatAmount,2) ;
                   $postingOrdersHoldingDetailTO->productCode   = $lineArr[$prodCode];
                   $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                   $postingOrdersHoldingDetailTO->pallets       = 0;
                   $postingOrdersHoldingDetailTO->productName   = $lineArr[$product];
                   $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

                   $hasdetail = 'Y';    
             }
             if($hasdetail =='Y') {
                 $eTO->type = FLAG_ERRORTO_SUCCESS;
                 $eTO->description = "Successful";
                 $eTO->object = $arrTO;
                return $eTO;
             } else {
             	   echo "<br><br>No Transactions in File - Remove File<br>";             	
     	           $eTO->type = FLAG_ERRORTO_SUCCESS;
                 $eTO->description = "No Transactions in File - Remove File";
                 $eTO->object = $arrTO;
                return $eTO;
             }       
    }
// --------------------------------------------------------------------------------------------------------------------------------
    function adaptorTOH_NATURESORDERS($content, $onlineFileProcessItem) {

         // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
         global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
         
         $eTO = new ErrorTO;
         if (!isset($storeDAO)) $storeDAO = new StoreDAO($this->dbConn);
            $arrTO=array();
             $processingLine=0; 
                   
             $headerarray=array('ORDER');

             $fileArr = explode("\n",$content);
             
             
             
             echo strtoupper(trim(substr(preg_replace('/[\xEF\xBB\xBF\xCF\xBD\x3A]/', '', $fileArr[0]),0,5)));
             
             echo "<br>ddd";
      
             $validfile = 0;
             if (in_array(strtoupper(trim(substr(preg_replace('/[\xEF\xBB\xBF\xCF\xBD\x3A]/', '', $fileArr[0]),0,5))), $headerarray))   {
                   // validate first line of file
                    $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");
                  
                    for ($x = 0; $x <= 18; $x++) {
         	              if(strtoupper(trim(preg_replace('/[\xEF\xBB\xBF\xCF\xBD\x3A]/', '',$lineArr[$x])))=='ORDER NO') {   //1
                            $documentNo = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='ORDER DATE') {   //2
                            $orderDate = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='NEED BY DAY') {   //3
                            $shipby = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='CUSTOMER NO') {   //4
                            $custid = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='CUSTOMER') {   //5
                            $store = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='ADDRESS1') {   //7
                            $add1 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='ADDRESS2') {   //8
                            $add2 = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='STORE CODE') {   //6
                            $storeCode = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='PO') {   //10
                            $poNum = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='LINE NUM') {   //10
                            $lineID = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='PRODUCT CODE') {   //12
                            $prodCode = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='PRODUCT NAME') {   //13
                            $product = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='CAPTURED BY') {   //14
                            $capBy = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))== 'QUANTITY') {   //16
                            $quantity = $x;
                            $validfile++;
                        } elseif(strtoupper(trim(str_replace(':','',$lineArr[$x])))=='UNIT PRICE') {   //17
                            $price = $x;
                            $validfile++;
                        }   
                        echo $lineArr[$x];
                        echo "<br>";
                    }
                    echo "<br>";
                    echo $validfile;
                    echo "<br>";
                    if($validfile <> 15 ) {
                          $eTO->type = FLAG_ERRORTO_ERROR;
                          $eTO->description = "Check file - Order / Claim fields missing!";
                          return $eTO;
                    }          
                    unset($fileArr[0]);
             } else {
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "First line expected to be a header!";
                  return $eTO;
             }
          
             $hasheader = 'N';
             $hasdetail = 'N';
             $savePON = '';
             unset($fileArr[0]);
             
    
             foreach ($fileArr as $key=>$line) {
             	
                   //$trimLine = trim(str_replace(',',' ',$line))  ;         	
             	     // convert line to CSV
                   $lineArr=str_getcsv($line, ",", '"', "\\");

                   //echo "<pre>";
                   //print_r($lineArr);
                  
                   $prinId = 443;
                        
                   echo $lineArr[$documentNo];
                   echo "<br>";
                   
                   if($lineArr[$documentNo] <> $savePON) {
                   	
                         /*******************
                         *   ORDER HEAD
                         *******************/
                         $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                         $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                         $postingOrdersHoldingTO->principalUid  = 443;
                         $postingOrdersHoldingTO->updateProduct ="N";
                         $postingOrdersHoldingTO->insertProduct ="N";
                         $postingOrdersHoldingTO->vendorUid     = $onlineFileProcessItem["vendor_uid"];
                         $postingOrdersHoldingTO->dataSource    = DS_EDI;
                         $postingOrdersHoldingTO->incomingFile  = basename($onlineFileProcessItem["file_being_processed"]);
                         $postingOrdersHoldingTO->captureDate   = CommonUtils::getGMTime(0);
                         $postingOrdersHoldingTO->requestedDeliveryDate = "";
                         $postingOrdersHoldingTO->capturedBy = "NATURES";
                         $postingOrdersHoldingTO->deliveryInstructions = '';
                         $postingOrdersHoldingTO->salesAgentStoreIdentifier = '' ;
                         $postingOrdersHoldingTO->reference = $lineArr[$poNum];

                         $postingOrdersHoldingTO->offInvoiceDiscount = 0;
                         $postingOrdersHoldingTO->offInvoiceDiscountType = '';  

                         $postingOrdersHoldingTO->depotLookupRef = '';
                         
                         $ordDate = substr($lineArr[$orderDate],6,4) . '-' . substr($lineArr[$orderDate],3,2) . '-' . substr($lineArr[$orderDate],0,2);

                         $postingOrdersHoldingTO->orderDate =  $ordDate;
              
                         if ($postingOrdersHoldingTO->orderDate === false) {
                              $eTO->type = FLAG_ERRORTO_ERROR;
                              $eTO->description = "Order date invalid format or empty";
                              $eTO->identifier = ET_CUSTOMER;
                              return $eTO;
                         }
                         $postingOrdersHoldingTO->shipToName = $lineArr[$store];
                         $postingOrdersHoldingTO->deliverName = $lineArr[$store];
                         $postingOrdersHoldingTO->clientDocumentNo = $lineArr[$documentNo];
                         $postingOrdersHoldingTO->documentNo = trim($lineArr[$documentNo]);
                         $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV; 
                         $postingOrdersHoldingTO->storeLookupRef = trim($lineArr[$storeCode]);
                         $postingOrdersHoldingTO->enforceSameDepot = "Y";
                         $postingOrdersHoldingTO->chainLookupRef = 'GENERIC CHAIN';
                         $postingOrdersHoldingTO->oldAccount = '';
                         $arrTO[] = $postingOrdersHoldingTO;
                         $savePON = $lineArr[$documentNo];
                   
                   }
 
                   /******************
                    *   ORDER DETAILS
                    *****************/
     
                   $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                   
                   $netPrice    = $lineArr[$price];
                   $postingOrdersHoldingDetailTO->clientLineNo  = $lineArr[$lineID] ;
                   $postingOrdersHoldingDetailTO->listPrice     = $lineArr[$exclPrice] ;
                   $postingOrdersHoldingDetailTO->quantity      = $lineArr[$quantity];
                   $postingOrdersHoldingDetailTO->discountReference = '';
                   $postingOrdersHoldingDetailTO->nettPrice     = round($netPrice, 2);
                   $postingOrdersHoldingDetailTO->extPrice      = round($netPrice * $lineArr[$quantity]);
                   $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
                   $postingOrdersHoldingDetailTO->vatAmount     = round($netPrice * $lineArr[$quantity] * VAL_VAT_RATE_TBLSTD,2) ;
                   $postingOrdersHoldingDetailTO->totalPrice    = round($netPrice * $lineArr[$quantity] + $postingOrdersHoldingDetailTO->vatAmount,2) ;
                   $postingOrdersHoldingDetailTO->productCode   = $lineArr[$prodCode];
                   $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
                   $postingOrdersHoldingDetailTO->pallets       = 0;
                   $postingOrdersHoldingDetailTO->productName   = $lineArr[$product];
                   $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

                   $hasdetail = 'Y';    
             }
             
             if($hasdetail =='Y') {
                 $eTO->type = FLAG_ERRORTO_SUCCESS;
                 $eTO->description = "Successful";
                 $eTO->object = $arrTO;
                return $eTO;
             } else {
             	   echo "<br><br>No Transactions in File - Remove File<br>";             	
     	           $eTO->type = FLAG_ERRORTO_SUCCESS;
                 $eTO->description = "No Transactions in File - Remove File";
                 $eTO->object = $arrTO;
                return $eTO;
             }       
    }

// ********************************************************************************************************************************

   function adaptorTOH_TRIGZPFMORDERS($content, $onlineFileProcessItem) {
     // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
     global $ROOT, $PHPFOLDER, $storeDAO, $importDAO;
     $eTO = new ErrorTO;   
   
     $fileArr = explode("\n",$content);
     $headerarray=array('PLACE');
     $fileArr = explode("\n",$content);
  
        
     $cleanString = strToUpper(preg_replace('/[\xCF\xBB\xEF\xBD\xBF\xFF\xFE\x00\x22]/', '', $fileArr[0]));
     echo "<pre>";
     if (in_array(substr($cleanString,0,5), $headerarray))   {
     	
            $lineArr=str_getcsv($cleanString, ",", '"', "\\");
            for ($x = 0; $x <= 50; $x++) {
    	
               if(strToUpper(trim($lineArr[$x]))=='PLACE') {
                  $storePos = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='CUSTOM ORDER NUMBER') {
                  $poNum = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='PRODUCT') {
                  $prodCode = $x;
                  $validfile++;
               } elseif(strToUpper(trim($lineArr[$x]))=='QUANTITY') {
                  $quantity = $x;
                  $validfile++;
               }    
         }     
         echo "<br>";
         echo $validfile;
         echo "<br>";
         if($validfile <> 4 ) {
             $eTO->type = FLAG_ERRORTO_ERROR;
             $eTO->description = "Check file - Order fields missing!";
             return $eTO;
         }          
         if(count($lineArr) < 4 ) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Invalid Column Count - expecting 4 columns Found " . count($lineArr);
            return $eTO;
         }          
         unset($fileArr[0]);      
     } else {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "First line expected to be a header!";
            return $eTO;
     } 
 	    $newDocument = '';
      $count = 0;
      $dupDocumentNumber = 'N';  
        
      foreach ($fileArr as $key=>$line) {
            // convert line to CSV
  
            $lineArr=str_getcsv(preg_replace('/[\x00]/', '', $line), ",", '"');
         
            $clnStoreString = str_replace(")","",str_replace("(","",str_replace("(MPU)", "",str_replace("(DF)", "",(str_replace("(DFL)", "",$lineArr[$storePos])))))); 
           
            
            if ($lineArr[$poNum] <> $newDocument)   {
             
                   $count++;
                   $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                   $postingOrdersHoldingTO->updateProduct    =   "N";
                   $postingOrdersHoldingTO->insertProduct    =   "N";
                   $postingOrdersHoldingTO->onlineFileProcessingUId = $onlineFileProcessItem["uid"];
                   $postingOrdersHoldingTO->principalUid     = 439 ;
                   $postingOrdersHoldingTO->vendorUid        = 61 ;
                   $postingOrdersHoldingTO->captureDate      = CommonUtils::getGMTime(0);
                   $postingOrdersHoldingTO->capturedBy       = 'TRIGPFM'; 
                   $postingOrdersHoldingTO->incomingFile     = basename($onlineFileProcessItem["file_being_processed"]);
                   $postingOrdersHoldingTO->dataSource       = DS_EDI;
                   $postingOrdersHoldingTO->documentNo       = '';
                   $postingOrdersHoldingTO->clientDocumentNo = '';   
                   $postingOrdersHoldingTO->reference        = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$poNum]));     //PO NUMBER.
                   $postingOrdersHoldingTO->vendorReference  = '';
                   $postingOrdersHoldingTO->oldAccount       = '';
                   $postingOrdersHoldingTO->debtorsStoreIdentifier    = "";
                   $postingOrdersHoldingTO->salesAgentStoreIdentifier = ''; 
                   $postingOrdersHoldingTO->chainLookupRef            = '3278';  
                   
                   $clnStoreString = str_replace(")","",str_replace("(","",str_replace("(DF)", "",(str_replace("(DFL)", "",$lineArr[$storePos])))));                   
                   
                   
                   $postingOrdersHoldingTO->storeLookupRef            = trim(substr($clnStoreString, -6)) ;; 
                   $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = 'N';
                   $postingOrdersHoldingTO->deliverName                      = trim(preg_replace('/[\x22\x27\x2C\x0A\x0D\x00]/', '', $lineArr[$storePos    ]));
                   $postingOrdersHoldingTO->shipToName                       = $postingOrdersHoldingTO->deliverName;
                  
                   $postingOrdersHoldingTO->documentTypeUId       = DT_ORDINV;
                   $postingOrdersHoldingTO->orderDate             = date('Y-m-d');
                   $postingOrdersHoldingTO->requestedDeliveryDate = $postingOrdersHoldingTO-> orderDate;
                   $ordDate = date('Ymd', strtotime($postingOrdersHoldingTO->orderDate));                   
                   $arrTO[] = $postingOrdersHoldingTO;
                   $newDocument = $lineArr[$poNum];
            } 
             
             /******************
             *   ORDER DETAILS
             *****************/
     
             $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                   
             $netPrice    = "";
             $postingOrdersHoldingDetailTO->clientLineNo  = '';
             $postingOrdersHoldingDetailTO->listPrice     = '';
             $postingOrdersHoldingDetailTO->quantity      = $lineArr[$quantity];
             $postingOrdersHoldingDetailTO->discountReference = '';
             $postingOrdersHoldingDetailTO->nettPrice     = '';
             $postingOrdersHoldingDetailTO->extPrice      = '';
             $postingOrdersHoldingDetailTO->vatRate       = VAL_VAT_RATE_TBLSTD;
             $postingOrdersHoldingDetailTO->vatAmount     = '';
             $postingOrdersHoldingDetailTO->totalPrice    = '';
             
             $cleanProdCode = trim(str_replace('-','',substr($lineArr[$prodCode],0,7)));
             
             if($cleanProdCode =='25GCT') {
           	      $pcode = '28GCT';
             } elseif($cleanProdCode =='2964073') {    // 16x85
           	      $pcode = '700083932013';
             } elseif($cleanProdCode =='85GJLT') {
           	      $pcode = '700083932013';
             } elseif($cleanProdCode =='85GKCT') {     // 16x85
           	      $pcode = '700083932044';           	      
             } elseif($cleanProdCode =='85GSCT') {     // 16x85
           	      $pcode = '700083932020';           	      
             } elseif($cleanProdCode =='85GSST') {     // 16x85
           	      $pcode = '700083932006';
             } elseif($cleanProdCode =='85GWCT') {     // 16x85
           	      $pcode = '700083932037';
             } elseif($cleanProdCode =='DUP 296') {   // 16x85
           	      $pcode = '700083932037';           	      
             } elseif($cleanProdCode =='85GCYT') {
           	      $pcode = '85GCYT'; 
             } else {
             	    $pcode = $cleanProdCode;
             }
             
             $postingOrdersHoldingDetailTO->productCode   = $pcode;
             $postingOrdersHoldingDetailTO->principalProductUid = ""; // will be looked up by product code in processor
             $postingOrdersHoldingDetailTO->pallets       = 0;
             $postingOrdersHoldingDetailTO->productName   = $lineArr[$prodCode];
             $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

      }
      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;        
   } 
// --------------------------------------------------------------------------------------------------------------------------------

}

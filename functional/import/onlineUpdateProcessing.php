<?php


/*------------------------------------------
 *          Online Update Processing
 * ------------------------------------------
 *
 *  processor for the document update tables
 *  batch process and lookups for reduced DB usage
 *
 * ------------------------------------------ */

include_once 'ROOT.php'; include_once $ROOT.'PHPINI.php';
include_once $ROOT.$PHPFOLDER."DAO/db_Connection_Class.php";
include_once $ROOT.$PHPFOLDER.'properties/Constants.php';
include_once $ROOT.$PHPFOLDER."libs/CommonUtils.php";
include_once $ROOT.$PHPFOLDER."libs/BroadcastingUtils.php";
include_once $ROOT.$PHPFOLDER.'DAO/BIDAO.php';
include_once $ROOT.$PHPFOLDER.'DAO/PostBIDAO.php';
include_once $ROOT.$PHPFOLDER."DAO/ImportDAO.php";
include_once $ROOT.$PHPFOLDER."DAO/DepotDAO.php";
include_once $ROOT.$PHPFOLDER."DAO/ChainDAO.php";
include_once $ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php';
include_once $ROOT.$PHPFOLDER.'DAO/PostTransactionDAO.php';
include_once $ROOT.$PHPFOLDER.'DAO/DocumentUpdateDAO.php';
include_once $ROOT.$PHPFOLDER.'DAO/PostDocumentUpdateDAO.php';
include_once $ROOT.$PHPFOLDER.'DAO/PostStoreDAO.php';
include_once $ROOT.$PHPFOLDER.'DAO/PostProductDAO.php';
include_once $ROOT.$PHPFOLDER.'TO/ErrorTO.php';
include_once $ROOT.$PHPFOLDER.'TO/SmartEventTO.php';
include_once $ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateConfirmationTO.php';
include_once $ROOT.$PHPFOLDER.'TO/PostingDocumentStatusTO.php';
include_once $ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateInvoiceTO.php';
include_once $ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateInvoiceTotalTO.php';
include_once $ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateInvoiceDetailTO.php';
include_once $ROOT.$PHPFOLDER.'TO/PostingDocumentUpdatePODTO.php';
include_once $ROOT.$PHPFOLDER.'TO/PostingDocumentTO.php';
include_once $ROOT.$PHPFOLDER.'TO/PostingDocumentDetailTO.php';
include_once $ROOT.$PHPFOLDER.'TO/PostingStoreTO.php';


set_time_limit(15*60); // 15 mins

// temporarily fudge the session for validation purposes
if (!isset($_SESSION)) session_start();
$_SESSION['user_id'] = SESSION_ADMIN_USERID;
$_SESSION['principal_id'] = "";


$statST = microtime(true);
echo "------- START: " . CommonUtils::getGMTime(0) . " -------<BR>";

  $updates = new onlineUpdateProcessing();

$statET = microtime(true);
echo "[@>>>JOBS:" . $updates->jobCount .";TT:" , (round($statET - $statST,4)) , "@]<BR>";
echo "------- END: " . CommonUtils::getGMTime(0) . " -------<BR>";
echo "<BR>[***EOS***]";




class onlineUpdateProcessing {


  public $errorTO;
  public $jobCount = 0; //counter for script output

  private $dbConn;
  private $docUpdateDAO;
  private $postDocUpdateDAO;
  private $importDAO;
  private $depotDAO;
  private $chainDAO;
  private $principalDAO;
  private $principalArr = array();
  private $documentStatusArr = array();
  private $updateControlArr = array();
  private $reasonCodeArr = array();
  private $postTransactionDAO;
  private $postStoreDAO;
  private $postProductDAO;
  private $BIDAO;
  private $PostBIDAO;

  private $depotArr;

  public function __construct() {

    $this->dbConn = new dbConnect();
    $this->dbConn->dbConnection();
    $this->docUpdateDAO = new DocumentUpdateDAO($this->dbConn);
    $this->postDocUpdateDAO = new PostDocumentUpdateDAO($this->dbConn);
    $this->postTransactionDAO = new PostTransactionDAO($this->dbConn);
    $this->principalDAO = new PrincipalDAO($this->dbConn);
    $this->importDAO = new ImportDAO($this->dbConn);
    $this->depotDAO = new DepotDAO($this->dbConn);
    $this->BIDAO = new BIDAO($this->dbConn);
    $this->PostBIDAO = new PostBIDAO($this->dbConn);
    $this->storeDAO = new StoreDAO($this->dbConn);
    $this->postStoreDAO = new PostStoreDAO($this->dbConn);
    $this->chainDAO = new ChainDAO($this->dbConn);
    $this->postProductDAO = new PostProductDAO($this->dbConn);


    //PURGE OLD PROCESSED
    $this->postDocUpdateDAO->purgeProcessedUpdates();
    $this->dbConn->dbinsQuery("commit");  //commit purge.

    $this->depotArr = $this->depotDAO->getAllDepotsGlobally();


    if(!isset($_GET['process'])){
      echo 'ERROR: No process Id passed!';
      return;
    }

    $processAllocation = $this->docUpdateDAO->getDocumentUpdateProcessMapping($_GET['process'])[0]['update_type_list'];

    //START PROCESS
    $qArr = $this->docUpdateDAO->getQueuedItems(array("'Q'","'E'"), $processAllocation);

    if(count($qArr)>0){

      $this->jobCount = count($qArr);

      //ONLY IF THERE are queued items do we populate the lookup arrays ** ONE TIME **
      $this->principalArr = $this->principalDAO->getAllPrincipalCodesArray();
      $this->documentStatusArr = $this->importDAO->getStatusArray();
      $this->updateControlArr = $this->docUpdateDAO->getDocumentUpdateControlArray();
      $this->reasonCodeArr = $this->importDAO->getReasonCodeArray();


      foreach($qArr as $duUId => $row){

        $this->errorTO = new ErrorTO();
        /*-----------------------------------------------
         * GLOBAL LOOKUP VALUES.
         */

        //depot lookup, might have already been set.
        if(empty($row['depot_uid'])){ //caters for zero and blank
          $row['depot_uid'] = $this->lookupDepotCode($row['depot_lookup']);
          if($row['depot_uid'] == false){
            $this->setDocumentUpdateError($duUId, "No Depot matched lookup code!");
            continue;
          }
        }

        //principal lookup
        $principalStatus="";
        if(empty($row['principal_uid'])){ //caters for zero and blank
          $principalArray = $this->lookupPrincipalCode($row['principal_lookup']);
          $row['principal_uid'] = $principalArray["uid"];
          $principalStatus = $principalArray["status"];
          if($row['principal_uid'] == false){
            $this->setDocumentUpdateError($duUId, "No Principal matched lookup code!");
            continue;
          }
          if ($principalStatus!=FLAG_STATUS_ACTIVE) {
            $this->setDocumentUpdatePrincipalUId($duUId, $row['principal_uid']);
            continue;
          }
        }

        //doc status lookup
        if(!empty($row['document_status_lookup'])){
          if(empty($row['document_status_uid'])){ //caters for zero and blank
            $row['document_status_uid'] = $this->lookupStatusCode($row['document_status_lookup']);
            if($row['document_status_uid'] == false){
              $this->setDocumentUpdateError($duUId, "No Document Status matched lookup code!");
              continue;
            }
          }
        }
        /*-----------------------------------------------*/

        //SPLIT PROCESSING HERE BY TYPE!
        //this function will return an errorTO and do the document(TT) updating or creation of any documents.
        switch ($row['update_type_uid']){  //update type
          case UPDATE_DOCUMENT_TYPE_CONFIRM:
            $eTO = $this->processConfirmation($row);
            break;
          case UPDATE_DOCUMENT_TYPE_INVOICE:
            $eTO = $this->processInvoice($row);
            break;
          case UPDATE_DOCUMENT_TYPE_CORRECTION:
            $eTO = $this->processCorrection($row);
            break;
          case UPDATE_DOCUMENT_TYPE_INVOICE_2:
            $eTO = $this->processInvoice($row);
            break;
          case UPDATE_DOCUMENT_TYPE_POD_VIT:
            $eTO = $this->processPOD_VIT($row);
            break;
          case UPDATE_DOCUMENT_TYPE_GRV:
            $eTO = $this->processGRV($row);
            break;
          case UPDATE_DOCUMENT_TYPE_POD_ULL:
            $eTO = $this->processPOD_ULL($row);
            break;
          case UPDATE_DOCUMENT_TYPE_AUDIT_LOG:
            $eTO = $this->processAUDITLOG($row);
            break;
          case UPDATE_DOCUMENT_TYPE_BUYER_POD:
            $eTO = $this->processBuyerPOD($row);
            break;
          default:
            $eTO = new ErrorTO();
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Unknown Update Type : " . $row['update_type_uid'];
            break;
        }

        if ($eTO->type != FLAG_ERRORTO_SUCCESS){

          $this->dbConn->dbinsQuery("rollback");
          $this->setDocumentUpdateError($duUId, "Process Error: " . $eTO->description, $eTO->object); //post failure, update any set fields.

        } else {

          $seTO = $this->postDocUpdateDAO->setDocumentUpdateSuccess($duUId, $eTO->object);  //successful confirmation
          if($seTO->type != FLAG_ERRORTO_SUCCESS){
            BroadcastingUtils::sendAlertEmail("Error in onlineUpdateProcessing", "error running setDocumentUpdateSuccess : " . $seTO->description, "Y", false);
            $this->dbConn->dbinsQuery("rollback");
          } else {
            $this->dbConn->dbinsQuery("commit");  //commit for process also.
          }

        }


      } //eoloop.

    } else {
      echo 'No Updates!';
    }

  }


  private function processConfirmation($rArr){

    $eTO = new ErrorTO();
    $eTO->object = $rArr;
    $updateDepotFlag = false;

    $principalMisterSweet="104";

    //safe guard!
    if($rArr['update_type_uid'] != UPDATE_DOCUMENT_TYPE_CONFIRM){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "processConfirmation unknown update type!";
      return $eTO;
    }

    //status and parameters control.
    $controlRow = $this->lookupControlItem($this->updateControlArr, UPDATE_DOCUMENT_TYPE_CONFIRM);  //find control match - must exist for confirmations.
    if(!count($controlRow)==1){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "No settings found for CONFIRM UPDATE, Type:" . UPDATE_DOCUMENT_TYPE_CONFIRM . " in Update Control!";
      return $eTO;
    }

    //*** REDIRECTION OF ORDERS ACROSS DEPOTS ***
    //p1 parameter - enables a confirmation to update an order across DEPOTs - for depot redirects - ONLY If the principal uid is listed.
    //It is a list of principals that are enabled for this feature will only be turned on when we identify who they are.
    $principalRedirectAllowedArr = explode(',', trim(CommonUtils::getParamValuesFromString($controlRow['additional_parameter_string'], "p1")));


    // temporarily put this in until MrS sort out their problems.
    if ((substr($rArr['document_number'],0,2)=="UL") && ($rArr['principal_uid']==$principalMisterSweet)) {
      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "MrSweet Ullmanns invoice cofirmation leg not yet implemented";
      $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
      $eTO->object['description'] = $eTO->description;
      return $eTO;
    }

    //validate lookup parameters.
    if(!is_numeric($rArr['principal_uid']) || empty($rArr['principal_uid']) ||
       !is_numeric($rArr['document_number']) || empty($rArr['document_number']) ||
       !is_numeric($rArr['depot_uid']) || empty($rArr['depot_uid'])
       ){

      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "The Principal/Document/Depot value is not set!";
      return $eTO;

    } else {



      //locate document by : PRINCIPAL + DEPOT + DOCUMENT NO ... and by DOCUMENT_TYPE_UID if supplied - type will be included at a later date.
      if ($rArr['document_type_uid']==DT_STOCKTRANSFER) $dT=DT_STOCKTRANSFER.",".DT_ASN;
      else if ($rArr['document_type_uid']==DT_ORDINV) $dT=DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE;
      else $dT=$rArr['document_type_uid'];
      $docArr = $this->docUpdateDAO->getDocumentbyPrincipalDepotDocumentNo($rArr['principal_uid'], $rArr['depot_uid'], $rArr['document_number'], $documentTypeUIdList=$dT, 6);

      if(count($docArr)==0 && in_array($rArr['principal_uid'], $principalRedirectAllowedArr) && $rArr['skip_depot_update']=="N"){  //is this principal listed.

        //*** REDIRECTION OF ORDERS ACROSS DEPOTS ***
        //locate document by : PRINCIPAL + DOCUMENT NO. - type will be included at a later date.
        $docArr = $this->docUpdateDAO->getDocumentbyPrincipalDocumentNo($rArr['principal_uid'], $rArr['document_number'], $documentTypeUId=$rArr['document_type_uid'], $documentStatusUId = DST_CANCELLED_NOT_OUR_AREA);
        if(count($docArr)>0){
          $updateDepotFlag = true;
        }

      }


      if(count($docArr)==0){

        if ($rArr['document_source_optional']=="Y") {
          // bypass this document
          $eTO->type = FLAG_ERRORTO_SUCCESS;
          $eTO->description = "Source Document not found for matching, but has been set to be bypassed";
          $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
          $eTO->object['description'] = $eTO->description;
          return $eTO;
        } else {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "The document could not be found!";
          return $eTO;
        }

      } else {


        //safe guard -> several document matched check!
        // -> duplicate document numbers
        // -> redirection of order issue
        if(count($docArr)>1){

          //build list of doc mast uids in error string.
          $dUIdlist = array();
          foreach($docArr as $dR){ $dUIdlist[] = $dR['dmUId']; }
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "*Multiple documents matched! (" . join(':', $dUIdlist) . ")";  //special errror.
          return $eTO;

        } else {

          //located one document -> OK.
          $eTO->object['document_master_uid'] = $docArr[0]['dmUId'];  //set document master uid for success on document_update.

          if(empty($rArr['document_status_uid'])){ //blank or zero

            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "The document status field is empty/invalid!";
            return $eTO;

          } else {

              //UPDATE DOCUMENT IF CURRENT STATUS IS IN => allowed update list
              if(in_array($docArr[0]['document_status_uid'], explode(',', $controlRow['update_status_list'] ))){

                $postDocUpCnf = new PostingDocumentUpdateConfirmationTO();
                $postDocUpCnf->dmUId = $docArr[0]['dmUId'];
                $postDocUpCnf->principalUId = $rArr['principal_uid'];
                $postDocUpCnf->depotUId = $rArr['depot_uid'];
                $postDocUpCnf->mergedDate = $rArr['merge_date'];
                $postDocUpCnf->mergedTime = $rArr['merge_time'];
                $postDocUpCnf->confirmationFile = $rArr['incoming_filename'];
                $postDocUpCnf->deliveryDayUId = $rArr['delivery_day_uid']; // only updates if not empty
                $postDocUpCnf->dueDeliveryDate = $rArr['due_delivery_date'];
                
                echo "Here";

                //UPDATE DOCUMENT AS THE CUR. STATUS IS LOWER
                $peTO = $this->postDocUpdateDAO->setDocumentConfirmation($postDocUpCnf, $depotRedirect = $updateDepotFlag);

                if($peTO->type != FLAG_ERRORTO_SUCCESS){

                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "Query error updating document - " . $peTO->description;
                  return $eTO;

                } else {
                	
                  //SET DOCUMENT STATUS
                  $postingDocumentStatusTO = new PostingDocumentStatusTO();
                  $postingDocumentStatusTO->documentMasterUId = $postDocUpCnf->dmUId;
                  $postingDocumentStatusTO->documentStatusUId = $rArr['document_status_uid'];
                  $postingDocumentStatusTO->comment = $postDocUpCnf->confirmationFile;
                  $postingDocumentStatusTO->skipValidation = 'Y';
                  $postingDocumentStatusTO->orderSequenceNo = $docArr[0]['order_sequence_no'];
                  $seTO = $this->postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);

                  if($seTO->type != FLAG_ERRORTO_SUCCESS){

                    $eTO->type = FLAG_ERRORTO_ERROR;
                    $eTO->description = "Query error updating document - " . $seTO->description;
                    return $eTO;

                  } else {

                    /*--------------------------------------------------------*/
                    // Insert any smart event notifications for the delivery exception.
                    // Method returns S if a notification should be created or
                    // E if no notification is required to be loaded.
                    /*--------------------------------------------------------*/
                    $notifyTO = $this->BIDAO->getNotificationDeliveryException($postDocUpCnf->dmUId);
                    if($notifyTO->type == FLAG_ERRORTO_SUCCESS){

                      foreach($notifyTO->object as $row){ //contains list of notification_re uids.

                        $smartTO = new SmartEventTO();
                        $smartTO->type = SE_DELIVERY_EXCEPTION;
                        $smartTO->typeUid = $row; //uid of notification_re
                        $smartTO->dataUid = $postDocUpCnf->dmUId;
                        $smartTO->generalReference1 = $rArr['uid'];  //store the uid so we know which confirmation fired this event.

                        $this->PostBIDAO->postSmartEvent($smartTO);
                      }
                    }
                    /*--------------------------------------------------------*/

                    //**** SUCCESS POINT 1 ****
                    $eTO->type = FLAG_ERRORTO_SUCCESS;
                    $eTO->description = "Successful";
                    $eTO->object['document_updated_flag'] = 1;  //updated the document.
                    $eTO->object['description'] = $eTO->description;
                    return $eTO;

                  }

                }

              //IGNORE DOCUMENT AND MARK UPDATE AS COMPLETE IF IN => ignore list
              } else if(in_array($docArr[0]['document_status_uid'], explode(',', $controlRow['ignore_status_list'] ))){


                //**** SUCCESS POINT 2 ****
                //DON'T UPDATE DOCUMENT AS THE CUR. STATUS IS HIGHER
                $eTO->type = FLAG_ERRORTO_SUCCESS;
                $eTO->description = "No update required!";
                $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
                $eTO->object['description'] = $eTO->description;
                return $eTO;


              //ELSE FAIL
              } else {
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "The current document status is not listed in update control! (status-uid:" . $docArr[0]['document_status_uid'] . ")";
                return $eTO;
              }

          }
        }
      }
    }

    return $eTO;

  }



  private function processInvoice($rArr){


    $eTO = new ErrorTO();

    //CHECKS
    //safe guard!
    if(!in_array($rArr['update_type_uid'],array(UPDATE_DOCUMENT_TYPE_INVOICE,UPDATE_DOCUMENT_TYPE_INVOICE_2))){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "processInvoice unknown update type!";
      return $eTO;
    }
    //MUST CONTAIN DETAIL LINES
    if(!isset($rArr['detail']) || count($rArr['detail'])==0){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "No detail lines for Invoice!";
      return $eTO;
    }
    //validate lookup parameters.
    if(!is_numeric($rArr['principal_uid']) || empty($rArr['principal_uid']) ||
       !is_numeric($rArr['document_number']) || empty($rArr['document_number']) ||
       !is_numeric($rArr['depot_uid']) || empty($rArr['depot_uid'])
       ){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "The Principal/Document/Depot value(s) is not set!";
      return $eTO;
    }
    if(!in_array($rArr['document_status_uid'], array(DST_INVOICED, DST_CANCELLED, DST_CANCELLED_NOT_OUR_AREA))){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "Unknown status!";
      return $eTO;
    }



    //ADDITIONAL LOOKUPS
    //status and parameters control.
    $controlRow = $this->lookupControlItem($this->updateControlArr, $rArr['update_type_uid']);  //find control match - must exist for confirmations.
    if(!count($controlRow)==1){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "No settings found for INVOICE UPDATE, Type:" . $rArr['update_type_uid'] . " in Update Control!";
      return $eTO;
    }

    //reason code lookup.
    if(!empty($rArr['pod_reason_lookup'])){
      if(empty($rArr['pod_reason_uid'])){ //caters for zero and blank
        $rArr['pod_reason_uid'] = $this->lookupReasonCode($rArr['pod_reason_lookup']);
        if($rArr['pod_reason_uid'] == false){
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "POD Reason Code lookup failure!";
          return $eTO;
        }
      }
    }
    $eTO->object = $rArr;


    //FIND TRANSACTION ON -> PRINCIPAL + DEPOT + DOCUMENT NO
    if ($rArr['document_type_uid']==DT_STOCKTRANSFER) $dT=DT_STOCKTRANSFER.",".DT_ASN;
    else if ($rArr['document_type_uid']==DT_ORDINV) $dT=DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE;
    else $dT=$rArr['document_type_uid'];
    $docArr = $this->docUpdateDAO->getDocumentbyPrincipalDepotDocumentNo($rArr['principal_uid'], $rArr['depot_uid'], $rArr['document_number'], $documentTypeUIdList=$dT, 24);

    if(count($docArr)==0){

      if ($rArr['document_source_optional']=="Y"){
        // bypass this document
        $eTO->type = FLAG_ERRORTO_SUCCESS;
        $eTO->description = "Source Document not found for matching, but has been set to be bypassed";
        $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
        $eTO->object['description'] = $eTO->description;
        return $eTO;
      } else {


        /*------------------------------------------------------------*/
        /*  CHECK IF PRINCIPAL HAS DOCUMENT - FOR ALL TIME, ANY DEPOT */
        /*------------------------------------------------------------*/
        /*
         *  This will not process the document update if found, only flag it as
         *  a proper error (list of dm.uids) or mark it as successful - if no document found!
         *
        /*------------------------------------------------------------*/
        $principalAllowedAllTimeArr = explode(',', trim(CommonUtils::getParamValuesFromString($controlRow['additional_parameter_string'], "p1")));

        //principal is set to use this feature on document updates
        if(in_array($rArr['principal_uid'], $principalAllowedAllTimeArr)){

          //LOOK IF THIS PRINCIPAL HAS A DOCUMENT LIKE THIS AT ALL -> cross depots -> all time.
          $docArr = $this->docUpdateDAO->getDocumentbyPrincipalDocumentNo($rArr['principal_uid'], $rArr['document_number'], $documentTypeUIdList = $dT, $documentStatusUId="", $skipDateLimitation=true);

          if(count($docArr)==0){

            //there was zero document numbers like this for the principal for
            //all time, across all depots, so we don't know what to update
            //flag as successful...
            $eTO->type = FLAG_ERRORTO_SUCCESS;
            $eTO->description = "Principal-document does not exist!";
            $eTO->object = $rArr;
            $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
            $eTO->object['description'] = $eTO->description;
            return $eTO;

          } else {

            //documents were found somewhere...
            $eTO->type = FLAG_ERRORTO_ERROR;
            $dUIdlist = array();
            foreach($docArr as $dR){ $dUIdlist[] = $dR['dmUId']; }
            $eTO->description = "Principal-documents exist, but not matching (" . join(',', $dUIdlist) . ")";
            return $eTO;
          }

        } else {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Document could not be found!";
          return $eTO;
        }
        /*------------------------------------------------------------*/


      }

    }


    //safe guard -> several document matched check!
    // -> duplicate document numbers
    // -> redirection of order issue
    if(count($docArr)>1){

      //build list of doc mast uids in error string.
      $dUIdlist = array();
      foreach($docArr as $dR){ $dUIdlist[] = $dR['dmUId']; }
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "*Multiple documents matched! (" . join(':', $dUIdlist) . ")";  //special errror.
      return $eTO;

    }


    if ($this->depotArr[$rArr['depot_uid']]["inv_conf_full_doc_check"]=="Y") {
      if($docArr[0]['total_detail'] != count($rArr['detail'])){ //do detail totals match up??
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "*Document detail lines do not match! (" . $docArr[0]['total_detail'] . ':' .  count($rArr['detail']) . ")";
        return $eTO;
      }
    }
    $rArr['document_master_uid'] = $docArr[0]['dmUId'];  //set document master uid for success on document_update.
    $eTO->object = $rArr;


    // Do the BATCH insert here regardless of updating next stage, as the insert will do a check if exists.
    // We pass the DM UID here as well because at this stage it hasnt been updated back to document_update table
    $this->errorTO = $this->postTransactionDAO->postBatch($rArr["uid"],$rArr['document_master_uid'], $rArr['principal_uid']);
    if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      return $this->errorTO;
    }


    //DO WE UPDATE DOCUMENT?
    //CHECK IF CURRENT STATUS IS IN => allowed update list
    if(
        (in_array($docArr[0]['document_status_uid'], explode(',', $controlRow['update_status_list'] ))) ||
        ($controlRow['update_status_list']=="*")
       ){


      //UPDATE - YES


      //UPDATE TOTALS DEPENDING ON STATUS TYPE
      $updateValues = false;
      if(in_array($rArr['document_status_uid'], array(DST_INVOICED, DST_CANCELLED))){
        $updateValues = true;
      }


      //MASTER AND HEADER UPDATE
      $postDocUpInv = new PostingDocumentUpdateInvoiceTO();
      $postDocUpInv->dmUId = $docArr[0]['dmUId'];
      $postDocUpInv->principalUId =  $rArr['principal_uid'];
      $postDocUpInv->invoiceFile = trim($rArr['incoming_filename']);
      $postDocUpInv->invoiceDate =  $rArr['invoice_date'];
      $postDocUpInv->invoiceNumber = $rArr['invoice_number'];
      $postDocUpInv->podReasonUid = $rArr['pod_reason_uid'];
      $postDocUpInv->pages = $rArr['pages'];
      $peTO = $this->postDocUpdateDAO->setDocumentInvoice($postDocUpInv);

      if($peTO->type != FLAG_ERRORTO_SUCCESS){

        $eTO->description = 'Error in setDocumentInvoice: ' . $peTO->description;
        return $eTO;

      } else {

        //SET DOCUMENT STATUS
        $postingDocumentStatusTO = new PostingDocumentStatusTO();
        $postingDocumentStatusTO->documentMasterUId = $docArr[0]['dmUId'];
        $postingDocumentStatusTO->documentStatusUId = $rArr['document_status_uid'];
        $postingDocumentStatusTO->skipValidation = 'Y';
        $postingDocumentStatusTO->orderSequenceNo = $docArr[0]['order_sequence_no'];
        $seTO = $this->postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);


        if($seTO->type != FLAG_ERRORTO_SUCCESS){

          $eTO->description = 'Error in setDocumentStatus: ' . $seTO->description;
          return $eTO;

        } else {


          //updates detail values and header totals.
          if(!$updateValues){


            //ONLY CHANGED THE STATUS AND INVOICE INFO
            //Status DST_CANCELLED_NOT_OUR_AREA will end here...
            $eTO->type = FLAG_ERRORTO_SUCCESS;
            $eTO->description = "Successful-2";
            $eTO->object = $rArr;
            $eTO->object['document_updated_flag'] = 1; ///document was updated!
            $eTO->object['description'] = $eTO->description;
            return $eTO;


          } else {

            $postDocUpInvTOT = new PostingDocumentUpdateInvoiceTotalTO();
            $postDocUpInvTOT->dmUId = $docArr[0]['dmUId'];
            $postDocUpInvTOT->principalUId = $rArr['principal_uid'];
            $postDocUpInvTOT->cases = 0;
            $postDocUpInvTOT->exclusiveTotal = 0;
            $postDocUpInvTOT->vatTotal = 0;
            $postDocUpInvTOT->invoiceTotal = 0;

            //UPDATE DETAIL
            $processedPPUIdArr=array();
            foreach($rArr['detail'] as $d){


              //FIND PRODUCT UID

            if ($rArr['principal_uid'] == 253) {
               $pfArr = $this->importDAO->getPrincipalProductByAltCode($rArr['principal_uid'], $d['product_code'], "");
            } elseif ($rArr['principal_uid'] == 257) {
               $pfArr = $this->importDAO->getPrincipalProductByAltCode($rArr['principal_uid'], $d['product_code'], "");
            } elseif ($rArr['principal_uid'] == 259) {
               $pfArr = $this->importDAO->getPrincipalProductByAltCode($rArr['principal_uid'], $d['product_code'], "");
            } else {
               $pfArr = $this->importDAO->getPrincipalProductByCode($rArr['principal_uid'], $d['product_code'], "");
              }
//              $pfArr = $this->importDAO->getPrincipalProductByCode($rArr['principal_uid'], $d['product_code'], "");
              if(count($pfArr)==0){
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "Product Code: " . $d['product_code'] . " could not be located!";
                return $eTO;
              }

              $processedPPUIdArr[]=$pfArr[0]['uid'];

              //FIND DETAIL LINE
              $concatLineNo = abs($d['page_no']) . abs($d['line_no']);
              if ($concatLineNo=="00") {
                $ddArr = $this->docUpdateDAO->getDocumentDetailbyProductUId($docArr[0]['dmUId'], $pfArr[0]['uid']);
              } else {
                $ddArr = $this->docUpdateDAO->getDocumentDetailbyProductLineNo($docArr[0]['dmUId'], $pfArr[0]['uid'], $concatLineNo);
              }
              if(count($ddArr)!=1){
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "Error locating detail line @dmUId:" . $docArr[0]['dmUId'] .'; productUId:'. $pfArr[0]['uid'].'; lineNo:'. $concatLineNo;
                return $eTO;
              }


              //UPDATE NEW TOTALS AND CALCULATE
              $postingDetail = new PostingDocumentUpdateInvoiceDetailTO();
              $postingDetail->ddUId = $ddArr[0]['uid'];
              $postingDetail->documentQty = $d['document_qty'];
              $postingDetail->deliveredQty = $d['delivery_qty'];
              $postingDetail->extendedPrice = round(($d['document_qty'] * $ddArr[0]['net_price']),2);
              $postingDetail->vatAmount = round(($postingDetail->extendedPrice * ($ddArr[0]['vat_rate']/100)),2);
              $postingDetail->total = round($postingDetail->extendedPrice + $postingDetail->vatAmount, 2);


              //TOTALS
              $postDocUpInvTOT->cases += $d['document_qty'];
              $postDocUpInvTOT->exclusiveTotal += $postingDetail->extendedPrice;
              $postDocUpInvTOT->vatTotal += $postingDetail->vatAmount;
              $postDocUpInvTOT->invoiceTotal += $postingDetail->total;


              //UPDATE LINE
              $ddTO = $this->postDocUpdateDAO->setDocumentInvoiceDetail($postingDetail);
              if($ddTO->type != FLAG_ERRORTO_SUCCESS){
                $eTO->description = 'Error in setDocumentInvoiceDetail: ' . substr($ddTO->description, -100);
                return $eTO;
              }

            } //eoloop.

            // Some depots send inv confirmations that only have detail lines for the invoiced products.
            // The remainder needs to be set to zero
            if (($this->depotArr[$rArr['depot_uid']]["inv_conf_full_doc_check"]=="N") && (sizeof($processedPPUIdArr)>0)) {
              $rTO = $this->postDocUpdateDAO->setExcludedDocumentInvoiceDetail($docArr[0]['dmUId'], implode(",",$processedPPUIdArr));
              if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
                $eTO->description = 'Error calling setExcludedDocumentInvoiceDetail in setDocumentInvoiceTotal: ' . $rTO->description;
                return $eTO;
              }
            }

            //UPDATE TOTALS
            $ptTO = $this->postDocUpdateDAO->setDocumentInvoiceTotal($postDocUpInvTOT);

            if($ptTO->type == FLAG_ERRORTO_SUCCESS){

              $eTO->type = FLAG_ERRORTO_SUCCESS;
              $eTO->description = "Successful-1";
              $eTO->object = $rArr;
              $eTO->object['document_updated_flag'] = 1; ///document was updated!
              $eTO->object['description'] = $eTO->description;
              return $eTO;

            } else {
              $eTO->description = 'Error in setDocumentInvoiceTotal: ' . $ptTO->description;
              return $eTO;
            }


          }
        }
      }


    //IGNORE DOCUMENT AND MARK UPDATE AS COMPLETE IF IN => ignore list
    } else if(in_array($docArr[0]['document_status_uid'], explode(',', $controlRow['ignore_status_list'] ))){


      //UPDATE - NO

      //**** SUCCESS POINT 2 ****
      //DON'T UPDATE DOCUMENT AS THE CUR. STATUS IS HIGHER
      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "No update required!";
      $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
      $eTO->object['description'] = $eTO->description;
      return $eTO;


    } else {  //ELSE FAIL

      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "The current document status is not listed in update control! (status-uid:" . $docArr[0]['document_status_uid'] . ")";
      return $eTO;

    }

  }

  private function processCorrection($rArr){

    $eTO = new ErrorTO();
    $eTO->object = $rArr;

    //CHECKS
    //safe guard!
    if($rArr['update_type_uid'] != UPDATE_DOCUMENT_TYPE_CORRECTION){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "processCreditNote unknown update type!";
      return $eTO;
    }
    //MUST CONTAIN DETAIL LINES
    if(!isset($rArr['detail']) || count($rArr['detail'])==0){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "No detail lines for Invoice!";
      return $eTO;
    }
    //validate lookup parameters.
    if(!is_numeric($rArr['principal_uid']) || empty($rArr['principal_uid']) ||
       // !is_numeric($rArr['document_number']) || empty($rArr['document_number']) ||
       !is_numeric($rArr['depot_uid']) || empty($rArr['depot_uid'])
       ){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "The Principal/Document/Depot value(s) is not set!";
      return $eTO;
    }
    if (($rArr["document_source_optional"]=="N") && (trim($rArr['source_document_number'])=="")) {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "Invalid Source Document Number!";
      return $eTO;
    }
    /*
    Dont need this as the credit note can be any status, but when we insert it it is 81=processed
    if(!in_array($rArr['document_status_uid'], array(DST_CANCELLED))){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "Unknown status!";
      return $eTO;
    }
    */

    // first ensure correction note itself is not duplicated
    $docArr = $this->docUpdateDAO->getDocumentbyPrincipalDepotDocumentNo($rArr['principal_uid'],
                                                                         $rArr['depot_uid'],
                                                                         $rArr['document_number'],
                                                                         $documentTypeUIdList=$rArr["document_type_uid"],
                                                                         $pastMonths=24);

    // GR files from ullmanns are correction update files - latest version so if it exists, just change reason code and additional_type later
    if (substr($rArr["incoming_filename"],0,2)!="GR") {
      if (count($docArr)>0) {
        $eTO->type = FLAG_ERRORTO_SUCCESS;
        $eTO->description = "Duplicated Correction Note (already processed) in Update Control";
        $eTO->object['description'] = $eTO->description;
        return $eTO;
      }
    }


    //ADDITIONAL LOOKUPS
    //status and parameters control.
    $controlRow = $this->lookupControlItem($this->updateControlArr, UPDATE_DOCUMENT_TYPE_CORRECTION);  //find control match - must exist for confirmations.
    if(!count($controlRow)==1){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "No settings found for INVOICE UPDATE, Type:" . UPDATE_DOCUMENT_TYPE_CORRECTION . " in Update Control!";
      return $eTO;
    }

    //reason code lookup.
    if(!empty($rArr['pod_reason_lookup'])){
      if(empty($rArr['pod_reason_uid'])){ //caters for zero and blank
        $rArr['pod_reason_uid'] = $this->lookupReasonCode($rArr['pod_reason_lookup']);
        if($rArr['pod_reason_uid'] == false){
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "POD Reason Code lookup failure!";
          $eTO->object['description'] = $eTO->description;
          return $eTO;
        }
      }
    }
    $eTO->object = $rArr;


    // GR files from ullmanns are correction update files - latest version so if it exists, just change reason code and additional_type
    if ((substr($rArr["incoming_filename"],0,2)=="GR") && (count($docArr)>0)) {

      $scTO = $this->postTransactionDAO->setCorrectionUpdated($docArr[0]["dmUId"], $rArr["pod_reason_uid"], $rArr["additional_type"]);

      if($scTO->type != FLAG_ERRORTO_SUCCESS){
        $eTO->description = 'Error in setCorrectionUpdated: ' . $scTO->description;
        return $eTO;
      }

      // no further processing for GR files then if already exists
      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful-1";
      $eTO->object['description'] = $scTO->description;
      return $eTO;

    }


    //FIND PARENT TRANSACTION ON -> PRINCIPAL + DEPOT + DOCUMENT NO
    $docArr=array();
    if ($rArr['source_document_number']!="") {
      switch ($rArr["document_type_uid"]) {
        case DT_ARRIVAL_CORRECTION:
          $dT=DT_ARRIVAL;
          break;
        case DT_CREDITNOTE:
          $dT=DT_ORDINV.",".DT_ORDINV_ZERO_PRICE;
          break;
        case DT_UPLIFT_DEBIT:
        case DT_UPLIFT_CREDIT:
          $dT=DT_UPLIFTS;
          break;
        case DT_DEBITNOTE:
          $dT=DT_ORDINV.",".DT_ORDINV_ZERO_PRICE;
          break;
        default:
          $dT=DT_ORDINV.",".DT_ORDINV_ZERO_PRICE;
      }
      $docArr = $this->docUpdateDAO->getDocumentbyPrincipalDepotDocumentNo($rArr['principal_uid'], $rArr['depot_uid'], $rArr['source_document_number'], $documentTypeUIdList=$dT);
      if((count($docArr)==0) && ($rArr['document_source_optional']=="N")) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Credit/Correction Source Document could not be found!";
        return $eTO;
      } elseif ((count($docArr)==0) && ($rArr['document_source_optional']=="Y")) {
        $eTO->object['document_master_uid'] = 0;
        $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
      } else {
        // found doc
        $eTO->object['document_master_uid'] = $docArr[0]['dmUId'];  //set document master uid for success on document_update.
      }
    } else {
      $eTO->object['document_master_uid'] = 0;
      $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
    }


    //DO WE UPDATE DOCUMENT?
    //CHECK IF CURRENT STATUS IS IN => allowed update list
    $tCases=$tSellingPrice=$tExclusiveTotal=$tVATTotal=$tInvoiceTotal=0;
    $dTO = new PostingDocumentTO();
    if(count($docArr)>0) {

      // some doc types only use the psm store uid and do no further matching or updates
      if (in_array($rArr["document_type_uid"],array(DT_UPLIFT_CREDIT,DT_UPLIFT_DEBIT))) {

        // do nothing - the rest of script will use this DM psm_uid as the store

      } else if(
          (in_array($docArr[0]['document_status_uid'], explode(',', $controlRow['update_status_list'] ))) ||
          ($controlRow['update_status_list']=="*")
         ){


        //MASTER AND HEADER UPDATE
        $peTO = $this->postDocUpdateDAO->setDocumentCredited($rArr['principal_uid'], $docArr[0]['dmUId'], $rArr['incoming_filename'], $rArr['source_document_number']);
        if($peTO->type != FLAG_ERRORTO_SUCCESS){
          $eTO->description = 'Error in setDocumentCredited: ' . $peTO->description;
          return $eTO;
        }

        //SET DOCUMENT STATUS
        $postingDocumentStatusTO = new PostingDocumentStatusTO();
        $postingDocumentStatusTO->documentMasterUId = $docArr[0]['dmUId'];
        $postingDocumentStatusTO->documentStatusUId = DST_DIRTY_POD;
        $postingDocumentStatusTO->skipValidation = 'Y';
        $postingDocumentStatusTO->orderSequenceNo = $docArr[0]['order_sequence_no'];
        $seTO = $this->postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);

        if($seTO->type != FLAG_ERRORTO_SUCCESS){
          $eTO->description = 'Error in setDocumentStatus: ' . $seTO->description;
          return $eTO;
        }

        //UPDATE DETAIL
        foreach($rArr['detail'] as $d){

          //FIND PRODUCT UID
          $pTO = $this->getProduct($rArr['principal_uid'],$d['product_uid'],$d['product_code']);
          if ($pTO->type!=FLAG_ERRORTO_SUCCESS) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Product Code: " . $d['product_code'] . " could not be located nor sysnonmf created!";
            return $eTO;
          }
          list($productUId,$nonMFProductCode) = array($pTO->object["product_uid"],$pTO->object["non_mf_product_code"]);

          //FIND DETAIL LINE
          $concatLineNo = abs($d['page_no']) . abs($d['line_no']);
          if ($concatLineNo=="00") {
            $ddArr = $this->docUpdateDAO->getDocumentDetailbyProductUId($docArr[0]['dmUId'], $productUId);
          } else {
            $ddArr = $this->docUpdateDAO->getDocumentDetailbyProductLineNo($docArr[0]['dmUId'], $productUId, $concatLineNo);
          }
          if(count($ddArr)!=1){
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Error locating detail line @dmUId:" . $docArr[0]['dmUId'] .'; productUId:'. $productUId.'; lineNo:'. $concatLineNo;
            return $eTO;
          }


          //UPDATE NEW TOTALS AND CALCULATE
          $postingDetail = new PostingDocumentUpdateInvoiceDetailTO();
          $postingDetail->ddUId = $ddArr[0]['uid'];
          $postingDetail->documentQty = $ddArr[0]['document_qty'];

          if (in_array($rArr["document_type_uid"],array(DT_DEBITNOTE,DT_UPLIFT_DEBIT))) {
            // cannot go too positive - set the highest to max ordered_qty in such case
            if (($ddArr[0]['delivered_qty']+$d['delivery_qty'])>$ddArr[0]['ordered_qty']) {
              $postingDetail->deliveredQty = $ddArr[0]['ordered_qty'];
            } else {
              $postingDetail->deliveredQty = ($ddArr[0]['delivered_qty']+abs($d['delivery_qty']));
            }

          } else {
            // cannot go negative - set the lowest to zero in such case
            // ( also remember that doc types 4,7,14 are stored as negative figures )
            if (($ddArr[0]['delivered_qty']-$d['delivery_qty'])<0) {
              $postingDetail->deliveredQty = 0;
            } else {
              $postingDetail->deliveredQty = ($ddArr[0]['delivered_qty']-abs($d['delivery_qty']));
            }

          }

	        /* 28/01/2013 ~ we do not update value on an invoice !
          $postingDetail->extendedPrice = round(($postingDetail->deliveredQty * $ddArr[0]['net_price']),2);
          $postingDetail->vatAmount = (($ddArr[0]['vat_rate']>0)?round(($postingDetail->extendedPrice * ($ddArr[0]['vat_rate']/100)),2) : 0);
          $postingDetail->total = round($postingDetail->extendedPrice + $postingDetail->vatAmount, 2);
	        */
          $postingDetail->extendedPrice = false;
          $postingDetail->vatAmount = false;
          $postingDetail->total = false;

          //UPDATE LINE
          $ddTO = $this->postDocUpdateDAO->setDocumentInvoiceDetail($postingDetail);
          if($ddTO->type != FLAG_ERRORTO_SUCCESS){
            $eTO->description = 'Error in processCreditNote for setDocumentInvoiceDetail: ' . substr($ddTO->description, -100);
            return $eTO;
          }

          /**********************************************************
           * START : Set Credit Note values for later usage
           * ********************************************************/
          $crSP=$ddArr[0]['net_price'];
          $crET=round($d['delivery_qty'] * $crSP,2);
          $crVR=$ddArr[0]['vat_rate'];
          $crVT=(($ddArr[0]['vat_rate']>0)?round($crET * ($crVR/100),2) : 0);
          $tCases+=$d['delivery_qty'];
          $tSellingPrice+=$crSP;
          $tExclusiveTotal+=$crET;
          $tVATTotal+=$crVT;
          $tInvoiceTotal+=$crET+$crVT;

          // setup the documentDetailTO for later
          $ddTO = new PostingDocumentDetailTO();
          $ddTO->lineNo = $ddTO->clientLineNo = $d['line_no'];
          $ddTO->productUId = $productUId;
          $ddTO->productCode = $nonMFProductCode;
          $ddTO->orderedQty = $d['delivery_qty'];
          $ddTO->documentQty = $d['delivery_qty'];
          $ddTO->deliveredQty = $d['delivery_qty'];
          $ddTO->sellingPrice = $crSP;
          $ddTO->discountValue = $d['discount_value'];
          $ddTO->netPrice = $crSP;
          $ddTO->extendedPrice = $crET;
          $ddTO->vatAmount = $crVT;
          $ddTO->vatRate = $crVR;
          $ddTO->total = $crET+$crVT;

          $dTO->detailArr[] = $ddTO;
          /**********************************************************
           * END : Set Credit Note values for later usage
           * ********************************************************/

        } //eoloop.

        /* 2/01/2013 ~ no longer needed as we do not update pricing
        //UPDATE TOTALS
        $ptTO = $this->postDocUpdateDAO->setDocumentHeaderTotals($docArr[0]['dmUId'],"ordered_qty");

        if($ptTO->type != FLAG_ERRORTO_SUCCESS){
          $eTO->description = 'Error in setDocumentInvoiceTotal: ' . $ptTO->description;
          return $eTO;
        }
        */

        $eTO->object['document_updated_flag'] = 1; ///document was updated!


      //IGNORE DOCUMENT AND MARK UPDATE AS COMPLETE IF IN => ignore list
      } else if(in_array($docArr[0]['document_status_uid'], explode(',', $controlRow['ignore_status_list'] ))){


        //UPDATE - NO

        //**** SUCCESS POINT 2 ****
        //DON'T UPDATE DOCUMENT AS THE CUR. STATUS IS HIGHER
        $eTO->object['document_updated_flag'] = 0; ///document was NOT updated, not a problem


      } else {  //ELSE FAIL

        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "The current document status is not listed in update control! (status-uid:" . $docArr[0]['document_status_uid'] . ")";
        return $eTO;

      }

    } // end : document count > 0 ; update source document


    // SUM totals for correction note itself if it wasn't populated in the source document processing above
    $principalStoreUId = ((isset($docArr[0]))?$docArr[0]['principal_store_uid']:0);
    if (sizeof($dTO->detailArr)==0) {

      // create unknown store if it doesnt exist and is not being used from DM matched
      if ($principalStoreUId==0) {
        $mfS = $this->importDAO->getPrincipalStoreByOldAccount($rArr['principal_uid'],VAL_UNKNOWN_STORE_OLD_ACCOUNT,"");
        if (sizeof($mfS)==0){
          $rTO = $this->createUnknownStore($rArr['principal_uid']);
          if($rTO->type!=FLAG_ERRORTO_SUCCESS){
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = $rTO->description;
            return $eTO;
          }
          $principalStoreUId = $rTO->identifier;
        } else {
          $principalStoreUId = $mfS[0]["uid"];
        }
      }

      foreach($rArr['detail'] as $d) {

        //FIND PRODUCT UID

        $pTO = $this->getProduct($rArr['principal_uid'],$d['product_uid'],$d['product_code']);
        if ($pTO->type!=FLAG_ERRORTO_SUCCESS) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Product Code: " . $d['product_code'] . " could not be located nor sysnonmf created!";
          return $eTO;
        }
        list($productUId,$nonMFProductCode) = array($pTO->object["product_uid"],$pTO->object["non_mf_product_code"]);

        $tCases+=$d['delivery_qty'];
        $tSellingPrice+=$d['list_price'];
        $tExclusiveTotal+=$d['extended_price'];
        $tVATTotal+=$d['vat_amount'];
        $tInvoiceTotal+=$d['total'];

        // setup the documentDetailTO for later
        $ddTO = new PostingDocumentDetailTO();
        $ddTO->lineNo = $ddTO->clientLineNo = $d['line_no'];
        $ddTO->productUId = $productUId;
        $ddTO->productCode = $nonMFProductCode;
        $ddTO->orderedQty = $d['delivery_qty'];
        $ddTO->documentQty = $d['delivery_qty'];
        $ddTO->deliveredQty = $d['delivery_qty'];
        $ddTO->sellingPrice = $d['list_price'];
        $ddTO->discountValue = $d['discount_value'];
        $ddTO->netPrice = $d['nett_price'];
        $ddTO->extendedPrice = $d['extended_price'];
        $ddTO->vatAmount = $d['vat_amount'];
        $ddTO->vatRate = $d['vat_rate'];
        $ddTO->total = $d['total'];
        $ddTO->podReasonUId = $d['pod_reason_uid'];

        // do a quick pricing calculation check because this pricing is going to be used from the file as the source doc was not used
        if (($ddTO->total-(($ddTO->sellingPrice-$ddTO->discountValue)*$ddTO->documentQty+$ddTO->vatAmount)) > VAL_PRICE_VARIATION_ALLOWED) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Incorrect pricing calculation in adaptorVITAL_INV for Vital Depot calculation for {$rArr['document_number']}";
            return $eTO;
        }
        if ((!in_array($rArr["document_type_uid"],array(DT_ARRIVAL_CORRECTION,DT_UPLIFT_CREDIT,DT_UPLIFT_DEBIT))) && ($rArr['source_document_number']!="")) {
          if ($ddTO->total==0) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Pricing found to be ZERO for Credit Note processing in processCreditNote for {$rArr['document_number']}";
            return $eTO;
          }
        }

        $dTO->detailArr[] = $ddTO;

      }

    }

    // CREATE actual Correction Note document itself
    // ( also remember that doc types 4,7,14 are stored as negative figures, but the postDocument will override the signage accordingly for these )
    $dTO->DMLType = "INSERT";
    $dTO->principalUId = $rArr['principal_uid'];
    $dTO->depotUId = $rArr['depot_uid']; // must be this depot, and not the UNKNOWN STORE depot uid !!
    $dTO->documentNumber = $rArr['document_number'];
    $dTO->sourceDocumentNumber = $rArr['source_document_number'];
    $dTO->invoiceNumber = ((isset($docArr[0]))?trim($docArr[0]['invoice_number']):"");
    $dTO->customerOrderNumber = ((isset($docArr[0]))?trim($docArr[0]['customer_order_number']):"");
    $dTO->documentTypeUId = $rArr["document_type_uid"];
    $dTO->processedDate = gmdate(GUI_PHP_DATE_FORMAT);
    $dTO->processedTime = gmdate(GUI_PHP_TIME_FORMAT);
    $dTO->mergedDate = gmdate(GUI_PHP_DATE_FORMAT);
    $dTO->mergedTime = gmdate(GUI_PHP_TIME_FORMAT);
    $dTO->validationDate = gmdate(GUI_PHP_DATE_FORMAT);
    $dTO->validationTime = gmdate(GUI_PHP_TIME_FORMAT);
    $dTO->validationStatus = 2; // unknown
    $dTO->incomingFile = $rArr['incoming_filename'];
    $dTO->TransmissionFlag1 = $dTO->TransmissionFlag2 = $dTO->TransmissionFlag3 = $dTO->TransmissionFlag4 = "0";
    $dTO->orderDate = $rArr['invoice_date'];
    $dTO->invoiceDate = $rArr['invoice_date'];
    $dTO->deliveryDate = $rArr['invoice_date'];
    $dTO->documentStatusUId = DST_PROCESSED;
    $dTO->principalStoreUId = $principalStoreUId;
    $dTO->cases = $tCases;
    $dTO->sellingPrice = $tSellingPrice;
    $dTO->exclusiveTotal = $tExclusiveTotal;
    $dTO->vatTotal = $tVATTotal;
    $dTO->invoiceTotal = $tInvoiceTotal;
    $dTO->dataSource = DS_EDI;
    $dTO->capturedBy = "CORRCONF";
    $dTO->additionalDetails = $rArr["additional_details"];
    $dTO->podReasonUId = $rArr["pod_reason_uid"];
    $dTO->fileLogUId = $rArr["file_log_uid"];
    $dTO->additionalType = $rArr["additional_type"];
    $dTO->claimNumber = $rArr['claim_number'];

    // do the posting into TT
    $this->errorTO = $this->postTransactionDAO->postDocument($dTO, $pWebSourceChecksAlreadyDone=false);
    if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      return $this->errorTO;
    }
    $dmUId = $dTO->dmUId;

    // Do the BATCH insert here regardless of updating next stage, as the insert will do a check if exists.
    // We pass the DM UID here as well because at this stage it hasnt been updated back to document_update table
    $this->errorTO = $this->postTransactionDAO->postBatch($rArr["uid"],$dmUId, $rArr['principal_uid']);
    if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      return $this->errorTO;
    }


    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful-1";
    $eTO->object['description'] = $eTO->description;
    return $eTO;

  }

  //vital
  private function processPOD_VIT($rArr){

    $eTO = new ErrorTO();
    $eTO->object = $rArr;
    $updateDepotFlag = false;


    //safe guard!
    if($rArr['update_type_uid'] != UPDATE_DOCUMENT_TYPE_POD_VIT){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "processConfirmation unknown update type!";
      return $eTO;
    }

    //status and parameters control.
    $controlRow = $this->lookupControlItem($this->updateControlArr, UPDATE_DOCUMENT_TYPE_POD_VIT);  //find control match - must exist for confirmations.
    if(!count($controlRow)==1){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "No settings found for CONFIRM UPDATE, Type:" . UPDATE_DOCUMENT_TYPE_POD_VIT . " in Update Control!";
      return $eTO;
    }

    //validate lookup parameters.
    if(!is_numeric($rArr['principal_uid']) || empty($rArr['principal_uid']) ||
       !is_numeric($rArr['document_number']) || empty($rArr['document_number']) ||
       !is_numeric($rArr['depot_uid']) || empty($rArr['depot_uid'])
       ){

      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "The Principal/Document/Depot value is not set!";
      return $eTO;

    } else {


      //locate document by : PRINCIPAL + DEPOT + DOCUMENT NO ... and by DOCUMENT_TYPE_UID if supplied - type will be included at a later date.
      $dT=DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE;
      $docArr = $this->docUpdateDAO->getDocumentbyPrincipalDepotDocumentNo($rArr['principal_uid'], $rArr['depot_uid'], $rArr['document_number'], $documentTypeUIdList=$dT);

      if(count($docArr)==0){
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "The document could not be found!";
        return $eTO;
      } else {


        //safe guard -> several document matched check!
        // -> duplicate document numbers
        // -> redirection of order issue
        if(count($docArr)>1){

          //build list of doc mast uids in error string.
          $dUIdlist = array();
          foreach($docArr as $dR){ $dUIdlist[] = $dR['dmUId']; }
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "*Multiple documents matched! (" . join(':', $dUIdlist) . ")";  //special errror.
          return $eTO;

        } else {


          //located one document -> OK.
          $eTO->object['document_master_uid'] = $docArr[0]['dmUId'];  //set document master uid for success on document_update.

          if(empty($rArr['document_status_uid'])){ //blank or zero

            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "The document status field is empty/invalid!";
            return $eTO;

          } else {

              //UPDATE DOCUMENT IF CURRENT STATUS IS IN => allowed update list
              if(in_array($docArr[0]['document_status_uid'], explode(',', $controlRow['update_status_list'] ))){

                  //SET DOCUMENT STATUS
                  $postingDocumentStatusTO = new PostingDocumentStatusTO();
                  $postingDocumentStatusTO->documentMasterUId = $docArr[0]['dmUId'];
                  $postingDocumentStatusTO->documentStatusUId = $rArr['document_status_uid'];
                  $postingDocumentStatusTO->comment = $rArr['incoming_filename'];
                  $postingDocumentStatusTO->skipValidation = 'Y';
                  $postingDocumentStatusTO->orderSequenceNo = $docArr[0]['order_sequence_no'];
                  $seTO = $this->postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);

                  if($seTO->type != FLAG_ERRORTO_SUCCESS){

                    $eTO->type = FLAG_ERRORTO_ERROR;
                    $eTO->description = "Query error updating document in processPOD - " . $seTO->description;
                    return $eTO;

                  } else {

                    //**** SUCCESS POINT 1 ****
                    $eTO->type = FLAG_ERRORTO_SUCCESS;
                    $eTO->description = "Successful";
                    $eTO->object['document_updated_flag'] = 1;  //updated the document.
                    $eTO->object['description'] = $eTO->description;
                    return $eTO;
                  }


              //IGNORE DOCUMENT AND MARK UPDATE AS COMPLETE IF IN => ignore list
              } else if(in_array($docArr[0]['document_status_uid'], explode(',', $controlRow['ignore_status_list'] ))){


                //**** SUCCESS POINT 2 ****
                //DON'T UPDATE DOCUMENT AS THE CUR. STATUS IS HIGHER
                $eTO->type = FLAG_ERRORTO_SUCCESS;
                $eTO->description = "No update required!";
                $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
                $eTO->object['description'] = $eTO->description;
                return $eTO;


              //ELSE FAIL
              } else {
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "The current document status is not listed in update control! (status-uid:" . $docArr[0]['document_status_uid'] . ")";
                return $eTO;
              }

          }
        }
      }
    }

    return $eTO;

  }


  //pod/pd files.
  private function processPOD_ULL($rArr){

    $eTO = new ErrorTO();
    $eTO->object = $rArr;
    $updateDepotFlag = false;

    //safe guard!
    if($rArr['update_type_uid'] != UPDATE_DOCUMENT_TYPE_POD_ULL){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "processPOD unknown update type!";
      return $eTO;
    }

    //status and parameters control.
    $controlRow = $this->lookupControlItem($this->updateControlArr, UPDATE_DOCUMENT_TYPE_POD_ULL);  //find control match - must exist for confirmations.
    if(!count($controlRow)==1){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "No settings found for POD UPDATE, Type:" . UPDATE_DOCUMENT_TYPE_POD_ULL . " in Update Control!";
      return $eTO;
    }


    //validate lookup parameters.
    if(!is_numeric($rArr['principal_uid']) || empty($rArr['principal_uid']) ||
       !is_numeric($rArr['document_number']) || empty($rArr['document_number']) ||
       !is_numeric($rArr['depot_uid']) || empty($rArr['depot_uid'])
       ){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "The Principal/Document/Depot value is not set!";
      return $eTO;
    }


    //reason code lookup.
    if(!empty($rArr['pod_reason_lookup'])){
      if(empty($rArr['pod_reason_uid'])){ //caters for zero and blank
        $rArr['pod_reason_uid'] = $this->lookupReasonCode($rArr['pod_reason_lookup']);
        if($rArr['pod_reason_uid'] == false){
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "POD Reason Code lookup failure!";
          return $eTO;
        }
      }
    }
    $eTO->object = $rArr; //return to update table.


    //lookup document type list.
    //some expand as certain types are merged on output.
    switch ($rArr['document_type_uid']){
      case DT_ORDINV:
        $dT = DT_ORDINV.",".DT_ORDINV_ZERO_PRICE;
        break;
      case DT_UPLIFTS:
        $dT = DT_UPLIFTS;
        break;
      case DT_STOCKTRANSFER:
        $dT = DT_STOCKTRANSFER;
        break;
      case DT_DELIVERYNOTE:
        $dT = DT_DELIVERYNOTE;
        break;
      default:
        //than simple try lookup using all valid POD document types...
        $dT = DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE.",".DT_UPLIFTS.",".DT_STOCKTRANSFER;
      break;
    }

    //end of lookups etc...
    /*---------------------*/


    //locate document for update : PRINCIPAL + DEPOT + DOCUMENT NO + DOCUMENT_TYPE_UID
    $docArr = $this->docUpdateDAO->getDocumentbyPrincipalDepotDocumentNo($rArr['principal_uid'], $rArr['depot_uid'], $rArr['document_number'], $documentTypeUIdList=$dT, 24);

    if(count($docArr)==0){

        /*------------------------------------------------------------*/
        /*  CHECK IF PRINCIPAL HAS DOCUMENT - FOR ALL TIME, ANY DEPOT */
        /*------------------------------------------------------------*/
        /*
         *  This will not process the document update if found, only flag it as
         *  a proper error (list of dm.uids) or mark it as successful - if no document found!
         *
        /*------------------------------------------------------------*/
        $principalAllowedAllTimeArr = explode(',', trim(CommonUtils::getParamValuesFromString($controlRow['additional_parameter_string'], "p1")));

        //principal is set to use this feature on document updates
        if(in_array($rArr['principal_uid'], $principalAllowedAllTimeArr)){

          //LOOK IF THIS PRINCIPAL HAS A DOCUMENT LIKE THIS AT ALL -> cross depots -> all time.
          $docArr = $this->docUpdateDAO->getDocumentbyPrincipalDocumentNo($rArr['principal_uid'], $rArr['document_number'], $documentTypeUIdList = $dT, $documentStatusUId="", $skipDateLimitation=true);

if ($docArr[0]['dmUId']==2393897) {
	echo "<pre>";
	print_r($docArr[0]);
		echo "</pre>";
		echo "<br>";
}


          if(count($docArr)==0){

            //there was zero document numbers like this for the principal for
            //all time, across all depots, so we don't know what to update
            //flag as successful...
            $eTO->type = FLAG_ERRORTO_SUCCESS;
            $eTO->description = "Principal-document does not exist!";
            $eTO->object = $rArr;
            $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
            $eTO->object['description'] = $eTO->description;
            return $eTO;

          } else {

            //documents were found somewhere...
            $eTO->type = FLAG_ERRORTO_ERROR;
            $dUIdlist = array();
            foreach($docArr as $dR){ $dUIdlist[] = $dR['dmUId']; }
            
            $eTO->description = "Principal-documents exist, but not matching (" . join(',', $dUIdlist) . ")";
            return $eTO;
          }

        } else {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Document could not be found!";
          return $eTO;
        }
        /*------------------------------------------------------------*/

    }

    //safe guard -> several document match check!
    if(count($docArr)>1){
      //build list of doc mast uids in error string.
      $dUIdlist = array();
      foreach($docArr as $dR){ $dUIdlist[] = $dR['dmUId']; }
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "*Multiple documents matched! (" . join(':', $dUIdlist) . ")";  //special errror
      return $eTO;
    }


    //located one document -> OK.
    $eTO->object['document_master_uid'] = $docArr[0]['dmUId'];  //set document master uid for success on document_update.

    if(empty($rArr['document_status_uid'])){ //blank or zero

      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "The document status field is empty/invalid!";
      return $eTO;

    } else {

      //CANCELLED documents via POD or Invoice files.
      if($docArr[0]['document_status_uid']==DST_CANCELLED && $rArr['document_status_uid']==DST_CANCELLED){

          //**** SUCCESS POINT -CANCEL- ****
          //DON'T UPDATE DOCUMENT AS THE DOC IS CANCELLED....
          $eTO->type = FLAG_ERRORTO_SUCCESS;
          $eTO->description = "No update required for cancelled!";
          $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
          $eTO->object['description'] = $eTO->description;
          return $eTO;

      } else if($rArr['document_status_uid']==DST_CANCELLED) {

          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Cancellation of Invoices are not allowed via POD updates/files!";
          return $eTO;

      } else {

        //UPDATE DOCUMENT IF CURRENT STATUS IS IN => allowed update list
        if(in_array($docArr[0]['document_status_uid'], explode(',', $controlRow['update_status_list'] ))){  //invoiced => pod ok / dirty pod

            //SET DOCUMENT STATUS
            $postingDocumentStatusTO = new PostingDocumentStatusTO();
            $postingDocumentStatusTO->documentMasterUId = $docArr[0]['dmUId'];
            $postingDocumentStatusTO->documentStatusUId = $rArr['document_status_uid'];
            $postingDocumentStatusTO->comment = $rArr['incoming_filename'];
            $postingDocumentStatusTO->skipValidation = 'Y';
            $postingDocumentStatusTO->orderSequenceNo = $docArr[0]['order_sequence_no'];
            $seTO = $this->postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);

            if($seTO->type != FLAG_ERRORTO_SUCCESS){

              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "Query error updating document in processPOD - " . $seTO->description;
              return $eTO;

            } else {

              //UPDATE POD FIELDS
              $postDocUpPOD = new PostingDocumentUpdatePODTO();
              $postDocUpPOD->dmUId = $docArr[0]['dmUId'];
              $postDocUpPOD->principalUId =  $rArr['principal_uid'];
              $postDocUpPOD->claimNumber = trim($rArr['claim_number']);
              $postDocUpPOD->grvNumber =  $rArr['grv_number'];
              $postDocUpPOD->deliveryDate = $rArr['delivery_date'];
              $postDocUpPOD->podReasonUid = $rArr['pod_reason_uid'];
              $peTO = $this->postDocUpdateDAO->setDocumentPOD($postDocUpPOD);

              if($peTO->type != FLAG_ERRORTO_SUCCESS){
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = 'Error in setDocumentPOD: ' . $peTO->description;
                return $eTO;

              } else {

                //**** SUCCESS POINT -MAIN- ****
                $eTO->type = FLAG_ERRORTO_SUCCESS;
                $eTO->description = "Successful";
                $eTO->object['document_updated_flag'] = 1;  //updated the document.
                $eTO->object['description'] = $eTO->description;
                return $eTO;
              }
            }

        //IGNORE DOCUMENT AND MARK UPDATE AS COMPLETE IF IN => ignore list ie: already updated
        } else if(in_array($docArr[0]['document_status_uid'], explode(',', $controlRow['ignore_status_list'] ))){ //should only be pod ok & dirty pod

          //UPDATE - NO

          //**** SUCCESS POINT -IGNORE- ****
          //DON'T UPDATE DOCUMENT AS THE CUR. STATUS IS HIGHER
          $eTO->type = FLAG_ERRORTO_SUCCESS;
          $eTO->description = "No update required!";
          $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
          $eTO->object['description'] = $eTO->description;
          return $eTO;


        //ELSE FAIL
        } else {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "The current document status is not listed in update control! (status-uid:" . $docArr[0]['document_status_uid'] . ")";
          return $eTO;
        }

      }
    }

    return $eTO;

  }

  // POD from the Buyer
  private function processBuyerPOD($rArr){

    $eTO = new ErrorTO();
    $eTO->object = $rArr;
    $updateDepotFlag = false;


    //safe guard!
    if($rArr['update_type_uid'] != UPDATE_DOCUMENT_TYPE_BUYER_POD){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "processConfirmation unknown update type!";
      return $eTO;
    }

    //status and parameters control.
    $controlRow = $this->lookupControlItem($this->updateControlArr, UPDATE_DOCUMENT_TYPE_BUYER_POD);  //find control match - must exist for confirmations.
    if(!count($controlRow)==1){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "No settings found for CONFIRM UPDATE, Type:" . UPDATE_DOCUMENT_TYPE_BUYER_POD . " in Update Control!";
      return $eTO;
    }

    //validate lookup parameters.
    if(!is_numeric($rArr['principal_uid']) || empty($rArr['principal_uid']) ||
        !is_numeric($rArr['source_document_master_uid']) || empty($rArr['source_document_master_uid']) ||
        empty($rArr['reference']) // MUST NOT BE BLANK as it is used for lookups !!
    ){

      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "The Principal/Source DM Uid/Reference value is not set!";
      return $eTO;

    } else {


      $docArr = $this->importDAO->getDocumentsByON($rArr['principal_uid'], $rArr['reference'], $depotUId=false, DT_ORDINV);

      if(count($docArr)==0){
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "The Principal's document could not be found!";
        return $eTO;
      } else {


        //safe guard -> several document matched check!
        if(count($docArr)>1){

          //build list of doc mast uids in error string.
          $dUIdlist = array();
          foreach($docArr as $dR){ $dUIdlist[] = $dR['uid']; }
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "*Multiple documents matched! (" . join(':', $dUIdlist) . ")";  //special errror.
          return $eTO;

        } else {

          //UPDATE DOCUMENT IF CURRENT STATUS IS IN => allowed update list
          if(!in_array($docArr[0]['document_status_uid'], explode(',', $controlRow['update_status_list'] ))){  //invoiced => pod ok / dirty pod
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Principal Document not of correct status!";
            $eTO->object['document_updated_flag'] = 0; ///document was NOT updated
            $eTO->object['description'] = $eTO->description;
            return $eTO;
          }

          //located one document -> OK.
          $eTO->object['document_master_uid'] = $docArr[0]['uid'];  //set document master uid for success on document_update.

          if(!empty($docArr[0]['buyer_document_status_uid']) || !empty($docArr[0]['pod_document_master_uid'])){

            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "The source document has already been matched (POD'd)!";
            return $eTO;

          } else {

              $podStatus = DST_DELIVERED_POD_OK;

              $rsDD =  $this->docUpdateDAO->getDocumentDetail($docArr[0]['uid']);

              $dtlLnCnt=0;
              foreach($rArr['detail'] as $d){

                $dtlLnCnt++;

                //FIND DETAIL LINE of principal's order
                if(empty($d['product_uid'])){
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "Buyer PODs require Product UIds!";
                  return $eTO;
                }

                $ddArr = $this->findInResultSet_documentDetail($rsDD, $d['product_uid']);
                if(count($ddArr)==0){
                  $podStatus = DST_DIRTY_POD; // at the moment it only caters for unique product codes
                } else if (count($ddArr)>1) {
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "Duplcated product uids not catered for";
                  return $eTO;
                } else {

                  if ($d['delivery_qty']!=$ddArr[0]['document_qty']) {
                    $podStatus = DST_DIRTY_POD;
                  }

                  //UPDATE NEW TOTALS AND CALCULATE
                  $postingDetail = new PostingDocumentUpdateInvoiceDetailTO();
                  $postingDetail->ddUId = $ddArr[0]['uid'];
                  $postingDetail->buyerDeliveredQty = $d['delivery_qty'];

                  //UPDATE the buyer delivered qty only
                  $ddTO = $this->postDocUpdateDAO->setDocumentInvoiceDetail($postingDetail);
                  if($ddTO->type != FLAG_ERRORTO_SUCCESS){
                    $eTO->description = 'Error in processCreditNote for setDocumentInvoiceDetail: ' . substr($ddTO->description, -100);
                    return $eTO;
                  }

                } // 1 row found

              } // product loop

              if (count($rsDD)!=$dtlLnCnt) {
                $podStatus = DST_DIRTY_POD;
              }

              //SET DOCUMENT STATUS after detail update as it requires knowledge of differing quantities
              $postingDocumentStatusTO = new PostingDocumentStatusTO();
              $postingDocumentStatusTO->documentMasterUId = $docArr[0]['uid'];
              $postingDocumentStatusTO->buyerDocumentStatusUId = $podStatus;
              $postingDocumentStatusTO->podDocumentMasterUId = $rArr['source_document_master_uid'];
              $postingDocumentStatusTO->skipValidation = 'Y';
              $postingDocumentStatusTO->orderSequenceNo = $docArr[0]['order_sequence_no'];
              $seTO = $this->postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);

              if($seTO->type != FLAG_ERRORTO_SUCCESS){

                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "Query error updating document in processPOD - " . $seTO->description;
                return $eTO;

              }

              $postingDocumentStatusTO->documentMasterUId = $rArr['source_document_master_uid'];
              $postingDocumentStatusTO->podDocumentMasterUId = $docArr[0]['uid'];
              $postingDocumentStatusTO->orderSequenceNo = $docArr[0]['order_sequence_no'];
              $seTO = $this->postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);

              if($seTO->type != FLAG_ERRORTO_SUCCESS){

                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "Query error updating document in processPOD - " . $seTO->description;
                return $eTO;

              }

              //**** SUCCESS POINT ****
              $eTO->type = FLAG_ERRORTO_SUCCESS;
              $eTO->description = "Successful";
              $eTO->object['document_updated_flag'] = 1;  //updated the document.
              $eTO->object['description'] = $eTO->description;
              return $eTO;

          }

        }
      }
    }

    return $eTO;

  }


  // arrival
  private function processGRV($rArr){

    $eTO = new ErrorTO();
    $eTO->object = $rArr;

    //CHECKS
    if($rArr['update_type_uid'] != UPDATE_DOCUMENT_TYPE_GRV){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "processGRV unknown update type!";
      return $eTO;
    }
    //MUST CONTAIN DETAIL LINES
    if(!isset($rArr['detail']) || count($rArr['detail'])==0){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "No detail lines for GRV / Arrival!";
      return $eTO;
    }
    //validate lookup parameters.
    if(!is_numeric($rArr['principal_uid']) || empty($rArr['principal_uid']) ||
       !is_numeric($rArr['document_number']) || empty($rArr['document_number']) ||
       !is_numeric($rArr['depot_uid']) || empty($rArr['depot_uid'])
       ){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "The Principal/Document/Depot value(s) is not set!";
      return $eTO;
    }



    // first ensure correction note itself is not duplicated
    $docArr = $this->docUpdateDAO->getDocumentbyPrincipalDepotDocumentNo($rArr['principal_uid'], $rArr['depot_uid'], $rArr['document_number'], $documentTypeUIdList=$rArr["document_type_uid"], $pastMonths = 24);
    if (count($docArr)>0) {
      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Duplicate Document (already processed)";
      $eTO->object['description'] = $eTO->description;
      return $eTO;
    }


    //ADDITIONAL LOOKUPS
    //status and parameters control.
    $controlRow = $this->lookupControlItem($this->updateControlArr, UPDATE_DOCUMENT_TYPE_GRV);  //find control match - must exist for confirmations.
    if(!count($controlRow)==1){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "No settings found for INVOICE UPDATE, Type:" . UPDATE_DOCUMENT_TYPE_GRV . " in Update Control!";
      return $eTO;
    }



    $principalStoreUId = ((isset($docArr[0]))?$docArr[0]['principal_store_uid']:0);

    // create principal store if it doesnt exist
    $mfS = $this->importDAO->getPrincipalStoreByOldAccount($rArr['principal_uid'],VAL_PSM_OLD_ACCOUNT_PREFIX.$rArr['depot_uid'],"");
    if (sizeof($mfS)==0){
      $rTO = $this->postStoreDAO->createPrincipalDepotStore($rArr['principal_uid'],$rArr['depot_uid']);
      if($rTO->type!=FLAG_ERRORTO_SUCCESS){
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = $rTO->description;
        return $eTO;
      }
      $principalStoreUId = $rTO->identifier;
    } else {
      $principalStoreUId = $mfS[0]["uid"];
    }

    $dTO = new PostingDocumentTO();

    $tCases=0;
    foreach($rArr['detail'] as $d) {

      //FIND PRODUCT UID
      $pTO = $this->getProduct($rArr['principal_uid'],$d['product_uid'],$d['product_code']);
      if ($pTO->type!=FLAG_ERRORTO_SUCCESS) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Product Code: " . $d['product_code'] . " could not be located nor sysnonmf created!";
        return $eTO;
      }
      list($productUId,$nonMFProductCode) = array($pTO->object["product_uid"],$pTO->object["non_mf_product_code"]);

      $tCases+=$d['ordered_qty'];

      // setup the documentDetailTO for later
      $ddTO = new PostingDocumentDetailTO();
      $ddTO->productUId = $productUId;
      $ddTO->productCode = $nonMFProductCode;
      $ddTO->orderedQty = $d['delivery_qty'];
      $ddTO->documentQty = $d['delivery_qty'];
      $ddTO->deliveredQty = $d['delivery_qty'];
      $ddTO->sellingPrice = $d['list_price'];
      $ddTO->discountValue = $d['discount_value'];
      $ddTO->netPrice = $d['nett_price'];
      $ddTO->extendedPrice = $d['extended_price'];
      $ddTO->vatAmount = $d['vat_amount'];
      $ddTO->vatRate = $d['vat_rate'];
      $ddTO->total = $d['total'];
      $ddTO->lineNo = $d['page_no'].$d['line_no'];
      $ddTO->clientLineNo = $d['page_no'].$d['line_no'];

      // do a quick pricing calculation check because this pricing is going to be used from the file as the source doc was not used
      if (($ddTO->total-(($ddTO->sellingPrice-$ddTO->discountValue)*$ddTO->documentQty+$ddTO->vatAmount)) > VAL_PRICE_VARIATION_ALLOWED) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Incorrect pricing calculation in adaptorVITAL_INV for Vital Depot calculation for {$rArr['document_number']}";
          return $eTO;
      }

      $dTO->detailArr[] = $ddTO;

    }

    // CREATE Arrival document itself
    $dTO->DMLType = "INSERT";
    $dTO->principalUId = $rArr['principal_uid'];
    $dTO->depotUId = $rArr['depot_uid']; // must be this depot, and not the UNKNOWN STORE depot uid !!
    $dTO->documentNumber = $rArr['document_number'];
    $dTO->sourceDocumentNumber = $rArr['source_document_number'];
    $dTO->customerOrderNumber = $rArr['reference'];
    $dTO->claimNumber = $rArr['claim_number'];
    $dTO->documentTypeUId = DT_ARRIVAL;
    $dTO->processedDate = gmdate(GUI_PHP_DATE_FORMAT);
    $dTO->processedTime = gmdate(GUI_PHP_TIME_FORMAT);
    $dTO->mergedDate = gmdate(GUI_PHP_DATE_FORMAT);
    $dTO->mergedTime = gmdate(GUI_PHP_TIME_FORMAT);
    $dTO->validationDate = gmdate(GUI_PHP_DATE_FORMAT);
    $dTO->validationTime = gmdate(GUI_PHP_TIME_FORMAT);
    $dTO->validationStatus = 2; // unknown
    $dTO->incomingFile = $rArr['incoming_filename'];
    $dTO->TransmissionFlag1 = $dTO->TransmissionFlag2 = $dTO->TransmissionFlag3 = $dTO->TransmissionFlag4 = "0";
    $dTO->orderDate = $rArr['invoice_date'];
    $dTO->invoiceDate = $rArr['invoice_date'];
    $dTO->deliveryDate = $rArr['delivery_date'];
    $dTO->documentStatusUId = DST_PROCESSED;
    $dTO->principalStoreUId = $principalStoreUId;
    $dTO->cases = $tCases;
    $dTO->sellingPrice = 0;
    $dTO->exclusiveTotal = 0;
    $dTO->vatTotal = 0;
    $dTO->invoiceTotal = 0;
    $dTO->dataSource = DS_EDI;
    $dTO->capturedBy = "ARCONF";
    $dTO->fileLogUId = $rArr["file_log_uid"];


    // do the posting into TT
    $this->errorTO = $this->postTransactionDAO->postDocument($dTO, $pWebSourceChecksAlreadyDone=false);
    if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = $this->errorTO->description;
      return $eTO;
    }


    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object['description'] = $eTO->description;
    return $eTO;

  }

  // This function is a bit adhoc - coding happens in an adhoc way based on principal-specific requirements unfortunately
  private function processAUDITLOG($rArr){

    // this method only caters for MrSweets at moment

    $eTO = new ErrorTO();
    $eTO->object = $rArr;

    //CHECKS
    if($rArr['update_type_uid'] != UPDATE_DOCUMENT_TYPE_AUDIT_LOG){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "processAUDITLOG unknown update type!";
      return $eTO;
    }
    //validate lookup parameters.
    if(!is_numeric($rArr['principal_uid']) || empty($rArr['principal_uid']) ||
       !is_numeric($rArr['document_number']) || empty($rArr['document_number'])
       ){
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "The Principal/Document value(s) is not set!";
      return $eTO;
    }

    //LOOK IF THIS PRINCIPAL HAS A DOCUMENT LIKE THIS AT ALL -> cross depots -> all time.
    $dT=DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE;
    $docArr = $this->docUpdateDAO->getDocumentbyPrincipalDocumentNo($rArr['principal_uid'], $rArr['document_number'], $documentTypeUIdList = $dT, $documentStatusUId="", $skipDateLimitation=false);

    $comment = "";
    if(count($docArr)==0){

      $eTO->type = FLAG_ERRORTO_SUCCESS; // dont treat as error as this isnt really critical type of confirmation, only informational
      $eTO->description = "Source Document could not be found";
      $eTO->object['description'] = $eTO->description;
      return $eTO;

    }


    // set up the messages
    // remember that the type codes (eg.MRSINVCNF) parameters need to be kept in synch with parameterBase.php !
    if (preg_match("/_INV_/i",$rArr["incoming_filename"])) {

      $exactMatchParams=array("comment"=>"MrSweet Inv Note Conf recvd ".$rArr["merge_date"],
                              "type"=>"MRSINVCNF");
      $altMatchParams=array("comment"=>"MrSweet Inv Conf recvd but invoice number differs ({$rArr["invoice_number"]})",
                              "type"=>"MRSINVCNF");

    } else if (preg_match("/_CRN_/i",$rArr["incoming_filename"])) {

      $exactMatchParams=array("comment"=>"MrSweet Credit Note Conf recvd ".$rArr["merge_date"],
                              "type"=>"MRSCRCNF");
      $altMatchParams=array("comment"=>"MrSweet Credit Note Conf recvd but invoice number differs ({$rArr["invoice_number"]})",
                              "type"=>"MRSCRCNF");

    } else if (preg_match("/_CAN_/i",$rArr["incoming_filename"])) {

      $exactMatchParams=array("comment"=>"MrSweet Cancelled Note Conf recvd ".$rArr["merge_date"],
                              "type"=>"MRSCANCNF");
      $altMatchParams=array("comment"=>"MrSweet Cancelled Note Conf recvd, no invoice number to verify",
                              "type"=>"MRSCANCNF");

    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "Could not create audit log ({$d["dmUId"]}) because of unidentified filename type!";
      return $eTO;
    }

    // loop through each as the depot is not known ... find an exact match using invoice number as well
    $found = false;
    $notFoundUIds  = array();
    foreach ($docArr as $d) {

      if ((!empty($rArr["invoice_number"])) && substr(str_pad($rArr["invoice_number"],8,"0",STR_PAD_LEFT),2)==substr(str_pad($d["invoice_number"],8,"0",STR_PAD_LEFT),2)) {
        $rTO = $this->postTransactionDAO->postDepotAuditLog($d["dmUId"], $changedBy="0", $comment=$exactMatchParams["comment"], $statusUid = false, $type=$exactMatchParams["type"]);
        if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Could not create audit log ({$d["dmUId"]})!";
          return $eTO;
        }
        $found = true;

      } else {
        $notFoundUIds[] = $d["dmUId"];
      }

    }

    // if not found exactly then store audit log against each of the alternatives where inv number unspecified or differs
    if (!$found) {
      foreach ($notFoundUIds as $uid) {
        $rTO = $this->postTransactionDAO->postDepotAuditLog($uid, $changedBy="0", $comment=$altMatchParams["comment"], $statusUid = false, $type=$altMatchParams["type"]);
        if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Could not create audit log ({$uid})!";
          return $eTO;
        }
      }
    }

    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object['description'] = $eTO->description;
    return $eTO;

  }



  private function setDocumentUpdateError($uid, $msg, $objArr = false){

    $reTO = $this->postDocUpdateDAO->setDocumentUpdateError($uid, $msg, $objArr);
    if ($reTO->type != FLAG_ERRORTO_SUCCESS) {
      BroadcastingUtils::sendAlertEmail("Error in onlineUpdateProcessing", $reTO->description, "Y", $quietMode = false);
      $this->dbConn->dbinsQuery("rollback"); // only rollback this because we mustnt have posted order reprocessed
    } else {
      $this->dbConn->dbinsQuery("commit");  //commit here as we continue looping from the caller.
    }
    return $reTO; //for if we need to handle @ loop(caller) level.

  }

  // only used if the principal is inactive
  private function setDocumentUpdatePrincipalUId($uid, $principalUId){

    $reTO = $this->postDocUpdateDAO->setDocumentUpdatePrincipalUId($uid, $principalUId);
    if ($reTO->type != FLAG_ERRORTO_SUCCESS) {
      BroadcastingUtils::sendAlertEmail("Error in onlineUpdateProcessing setDocumentUpdatePrincipalUId ", $reTO->description, "Y", $quietMode = false);
      $this->dbConn->dbinsQuery("rollback"); // only rollback this because we mustnt have posted order reprocessed
    } else {
      $this->dbConn->dbinsQuery("commit");  //commit here as we continue looping from the caller.
    }
    return $reTO; //for if we need to handle @ loop(caller) level.

  }

  //lookups
  private function lookupDepotCode($code){

    $r = false;

    switch ($code) {
      case 'J': //UJ
        $r = 2; //uid
        break;
      case 'D': //UD
        $r = 3; //uid
        break;
      case 'C':  //UC
        $r = 5; //uid
        break;
      case 'P': //FP-PE
        $r = 6; //uid
        break;
      case 'E': //TURNER
        $r = 7; //uid
        break;
      case 'F': //UF
        $r = 133;  //uid
        break;
      case 'B': //UB
        $r = 14;  //uid
        break;

      default:
        $r = false; //unknown?
        break;
    }

    return $r;

  }


  private function lookupPrincipalCode($code){

    $r = array("uid"=>false,"status"=>false);
    foreach($this->principalArr as $prinRow){
      if($code == $prinRow['principal_code']){
        $r = array("uid"=>$prinRow['uid'],"status"=>$prinRow['status']);
      }
    }

    //no matches? => check uplift account
    if($r["uid"] == false){
      foreach($this->principalArr as $prinRow){
        if($code == $prinRow['principal_uplift_code']){
          $r = array("uid"=>$prinRow['uid'],"status"=>$prinRow['status']);
        }
      }
    }

    return $r;

  }


  private function lookupStatusCode($code){

    $code = trim($code);
    $r = false;

    //HACK
    if($code == 'F0' || $code == 'DUP-#'){
      $code = 'PS';
    }

    if($code == ''){
      return $r;
    }

    foreach($this->documentStatusArr as $sRow){
      if($code == $sRow['status_code']){
        $r = $sRow['uid'];
      }
    }
    return $r;

  }


  //pod reason code
  private function lookupReasonCode($code){

    $code = trim($code);
    $r = false;

    foreach($this->reasonCodeArr as $sRow){
      if($code == $sRow['code']){
        $r = $sRow['uid'];
      }
    }
    return $r;

  }




  private function lookupControlItem($arr, $type_uid){

    $r = array();
    foreach($arr as $sRow){
      if($type_uid == $sRow['update_type_uid']){
        $r = $sRow;
        break;
      }
    }
    return $r;

  }

  private function createUnknownStore($principalUId) {
    $errorTO = new ErrorTO;

    // lookup the generic chain
    $mfC = $this->chainDAO->getPrincipalChainByOldCode($principalUId, CHAIN_GENERIC_OLD_CODE);
    if (sizeof($mfC)==0){
      $errorTO->type = FLAG_ERRORTO_ERROR;
      $errorTO->description = "Generic Chain could not be located in order to auto-create System UNKNOWN STORE!";
      return $errorTO;
    }

    $postingStoreTO = new PostingStoreTO;
    $postingStoreTO->DMLType = "INSERT";
    $postingStoreTO->principalStoreUId ="";
    $postingStoreTO->principal = $principalUId;
    $postingStoreTO->deliverName = "UNKNOWN STORE";
    $postingStoreTO->deliverAdd1 = "USED BY SYSTEM - DO NOT CHANGE";
    $postingStoreTO->billName = "UNKNOWN";
    $postingStoreTO->billAdd1 = "UNKNOWN";
    $postingStoreTO->depot = 99;
    $postingStoreTO->deliveryDay =8;
    $postingStoreTO->noVAT=0;
    $postingStoreTO->onHold = 0;
    $postingStoreTO->chain = $mfC[0]["uid"];
    $postingStoreTO->altPrincipalChainUId = $mfC[0]["uid"];
    $postingStoreTO->branchCode = "";
    $postingStoreTO->oldAccount = VAL_UNKNOWN_STORE_OLD_ACCOUNT; // if null, let posting alloc sequence automatically
    $postingStoreTO->allocatePermissionsUserList="";
    $postingStoreTO->ledgerBalance="";
    $postingStoreTO->ledgerCreditLimit="";
    $postingStoreTO->status=FLAG_STATUS_ACTIVE;
    $postingStoreTO->vendorCreatedByUId="";
    $postingStoreTO->ownedBy="";

    $rTO=$this->postStoreDAO->postPrincipalStore($postingStoreTO);
    if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
      $errorTO->type = FLAG_ERRORTO_ERROR;
      $errorTO->description = "Unable to create Unknown Store : ".$rTO->description;
      return $errorTO;
    }

    $errorTO->type = FLAG_ERRORTO_SUCCESS;
    $errorTO->description = "Successfully created Unknown Store";
    $errorTO->identifier = $rTO->identifier;

    return $errorTO;

  }

  private function getProduct($principalUId,$productUId,$productCode) {
    $eTO = new ErrorTO();


    $nonMFProductCode = "";
    $pfArr = array();
    if (empty($productUId)) {
     if ($principalUId == 253) {
        $pfArr = $this->importDAO->getPrincipalProductByAltCode($principalUId, $productCode, "");
      } elseif ($principalUId == 257) {
        $pfArr = $this->importDAO->getPrincipalProductByAltCode($principalUId, $productCode, "");
      } else {
        $pfArr = $this->importDAO->getPrincipalProductByCode($principalUId, $productCode, "");
        }
        if(count($pfArr)==0){
          // attempt to get system non master file product
          $pfArr = $this->importDAO->getPrincipalProductByCode($principalUId, VAL_PRODUCTCODE_NOT_ON_MF, "");
          if(count($pfArr)==0){
            // create the system non master file product
            $rTO = $this->postProductDAO->createPrincipalNonMFProduct($principalUId);
            if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "Product Code: " . $productCode . " could not be located nor sysnonmf created!";
              return $eTO;
            } else {
              $prUId = $rTO->identifier;
            }
            $nonMFProductCode = $productCode; // proper code, not nonmf code
          } else {
            $prUId = $pfArr[0]['uid'];
            $nonMFProductCode = $productCode;
          }
        } else {
          $prUId = $pfArr[0]['uid'];
        }
    } else {
      $prUId = $productUId;
    }

    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = array("product_uid"=>$prUId,
                         "non_mf_product_code"=>$nonMFProductCode,
                         "rs"=>$pfArr);

    return $eTO;
  }

  private function findInResultSet_documentDetail($rs, $productUId) {

    $arr = array();

    foreach ($rs as $row) {
      if ($row['product_uid']==$productUId) $arr[] = $row;
    }

    return $arr;

  }

}




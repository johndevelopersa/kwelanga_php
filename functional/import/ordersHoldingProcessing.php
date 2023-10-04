<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/ImportDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/StoreDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/ProductDAO.php");
include_once($ROOT . $PHPFOLDER . 'DAO/DepotDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ChainDAO.php');
include_once($ROOT . $PHPFOLDER . "DAO/PostImportDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PostProductDAO.php");
include_once($ROOT . $PHPFOLDER . 'DAO/PostStoreDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostMiscellaneousDAO.php');
include_once($ROOT . $PHPFOLDER . "DAO/PostTransactionDAO.php");
include_once($ROOT . $PHPFOLDER . 'DAO/PostDocumentUpdateDAO.php');
include_once($ROOT . $PHPFOLDER . "libs/CommonUtils.php");
include_once($ROOT . $PHPFOLDER . "libs/BroadcastingUtils.php");
include_once($ROOT . $PHPFOLDER . 'TO/PostingOrderTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingOrderDetailTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingOrderDocumentPricingTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingStoreTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingStoreEDIUpdateTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingSpecialFieldTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingProductTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingDocumentUpdateTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingDocumentUpdateDetailTO.php');
include_once($ROOT . $PHPFOLDER . 'functional/export/adaptor/AdaptorDocumentExport.php');  //depot export adaptors.

//temp fixes imports.
include_once($ROOT . $PHPFOLDER . 'DAO/BbqUpdateDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/MaintenanceDAO.php');


class OrdersHoldingDocument
{
    public $storeDAO; // used outside for screens
    public $productDAO;
    public $depotDAO; // used outside for screens

    private $dbConn;
    private $postImportDAO;
    private $importDAO;
    private $principalDAO;
    private $chainDAO;
    private $postMiscDAO;
    private $postTransactionDAO;
    private $postStoreDAO;
    private $postProductDAO;
    private $postDocumentUpdateDAO;
    private $preferences = [];
    private $rs = [];

    function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
        $this->postImportDAO = new PostImportDAO($this->dbConn);
        $this->importDAO = new ImportDAO($this->dbConn);
        $this->principalDAO = new PrincipalDAO($this->dbConn);
        $this->storeDAO = new StoreDAO($this->dbConn);
        $this->productDAO = new ProductDAO($this->dbConn);
        $this->depotDAO = new DepotDAO($this->dbConn);
        $this->chainDAO = new ChainDAO($this->dbConn);
        $this->postMiscDAO = new PostMiscellaneousDAO($this->dbConn);
        $this->postTransactionDAO = new PostTransactionDAO($this->dbConn);
        $this->postStoreDAO = new PostStoreDAO($this->dbConn);
        $this->postProductDAO = new PostProductDAO($this->dbConn);
        $this->postDocumentUpdateDAO = new PostDocumentUpdateDAO($this->dbConn);
    }

    public function processDocuments()
    {
        $startFetch = microtime(true);
        echo "Fetching records to process...<br>";

        $this->rs = $this->importDAO->getOrdersHoldingForProcessing(); // gets full entity set, using orders_holding_uid as key

        echo "Pending records... " . count($this->rs->headerArr) . "<br>";
        echo "Fetch took... " . round(microtime(true) - $startFetch, 6) . "s<br>";

        foreach ($this->rs->headerArr as &$oh) {

            $validateErrorTO = $this->validateDocument($oh);
            if ($validateErrorTO->isError()) {

                // order not suspended for "approval required"
                if ($validateErrorTO->identifier != FLAG_STATUS_SUSPENDED) {

                    //update errors.
                    $eTO = $this->postImportDAO->setOrdersHoldingStatus($oh["principal_uid"], $oh["uid"], "U", $validateErrorTO->object, $validateErrorTO->description, "");
                    if ($eTO->isError()) {
                        BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing 2", $eTO->description, "Y", $quietMode = false);
                        echo "Error 99 Occurred for Document " . $oh["document_number"];
                    }
                }

            } else {

                //success
                $postErrorTO = $this->postOrder($validateErrorTO->object, $oh["uid"]);
                if ($postErrorTO->isError()) {
                    echo "[postOrder] " . $postErrorTO->description . "\n";

                    $this->dbConn->dbinsQuery("rollback"); // rollback both this and the posting

                    //TODO: add unique principal check here, mark orders holding as deleted.

                    $eTO = $this->postImportDAO->setOrdersHoldingStatus($validateErrorTO->object->principalUId, $oh["uid"], "S", FLAG_ERRORTO_ERROR, $postErrorTO->description, "");
                    if ($eTO->isError()) {
                        BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing", $eTO->description, "Y", $quietMode = false);
                    }

                } else {

                    // update OH status
                    $eTO = $this->postImportDAO->setOrdersHoldingStatus($oh["principal_uid"], $oh["uid"], "U", FLAG_ERRORTO_SUCCESS, "Successful", $validateErrorTO->object->orderSequenceNo);
                    if ($eTO->type != FLAG_ERRORTO_SUCCESS) {
                        BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing", $eTO->description, "Y", $quietMode = false);
                        $this->dbConn->dbinsQuery("rollback"); // only rollback this because we mustn't have posted order reprocessed
                    } else {
                        $this->dbConn->dbinsQuery("commit"); // must commit here otherwise locking occurs because edi generation uses separate dbConn
                    }

                    //TODO: we need a global event system for new documents and then a separate system that keeps track of extracts per principals etc.
                    // create depot export
                    if ($validateErrorTO->object->documentType !== DT_BUYER_GOODS_INWARD) {
                        $adaptorDocEx = new AdaptorDocumentExport($this->dbConn);
                        //self-contained error notifications
                        $exportResult = $adaptorDocEx->generateExport($validateErrorTO->object, $this->storeDAO, $this->productDAO, $this->principalDAO);
                    }
                }
            }

            //commit successes and orders holding status updates
            $this->dbConn->dbinsQuery("commit");
            unset($validateResp);
        }
    }

    public function validateDocument(&$oh): ErrorTO
    {
        $hdrStatus = [];
        $dtlStatus = [];
        $dtlStatusMsg = "";
        $statusMsg = "";
        $_SESSION['principal_id'] = $oh["principal_uid"]; // set session for creation of stores / products

        $this->setPrincipalPreferences($oh);
        $pPricingConflictAction = $this->getPrincipalPricingConflictAction($oh);
        $oh["mfPS"] = $mfPS = $this->getPrincipalStore($oh, $hdrStatus); // also sets the oh[principal_store_uid]

        if (($oh["document_type_uid"] == DT_BUYER_GOODS_INWARD) &&
            ($oh["data_source"] = DS_WS) &&
            ($oh["captured_by"] == "PNP") &&
            (substr($oh["document_type"], 0, 5) != "G_REC")) {
            $hdrStatus[] = "10.0";
        }

        $depotUId = $this->getDepotIfNotPSM($oh, $hdrStatus);
        $this->setStoreDepot($depotUId, $oh, $mfPS, $hdrStatus);
        $approvalReq = $this->setDocumentOriginAction($depotUId, $oh, $hdrStatus);
        if ($approvalReq === true) {
            return ErrorTO::NewError("Document failed Validation, remaining validation skipped", FLAG_STATUS_SUSPENDED);
        }

        /******************************************************************************************************
         * START : CREATE the store / update fields / update special fields
         * ****************************************************************************************************/

        // 1. Update store fields if set - fields are only updated if not ""
        if (!count($hdrStatus)) {

            $storeErrorTO = $this->updateStore($oh);
            if ($storeErrorTO->isError()) {
                // processing MUST be stopped here, as some fields could be critical for processing !
                echo "[UpdateStore] " . $storeErrorTO->description . "\n";
                $hdrStatus[] = "2.7";
            }
        }

        // 2. First load/update any special fields with allowUpdate set.
        //    - The rest will be processed in create store below, and in entirety if creating store
        $continue = $this->updateStoreSF($oh, $hdrStatus);
//      if ($continue===true) continue; // cannot allow to continue if store becomes inaccurate

        // 3. create the store if requested & supplied
        //    - MUST ONLY CREATE if atleast one of lookup vals are filled in as otherwise the risk is too high for store to
        //      keep getting created continually each run
        $this->createStore($oh, $pPricingConflictAction, $depotUId, $hdrStatus);

        /******************************************************************************************************
         * END : CREATE the store / update special fields
         * ****************************************************************************************************/

        // document number check is now done during posting automatically
        if (($oh["capture_date"] == "") || (strtotime($oh["capture_date"]) === false)) {
            $hdrStatus[] = "5.0";
        }
        if (($oh["order_date"] == "") || (strtotime($oh["order_date"]) === false)) {
            $hdrStatus[] = "5.1";
        }
        if (($oh["delivery_date"] != "") && (strtotime($oh["delivery_date"]) === false)) {
            $hdrStatus[] = "5.2";
        }
        if (($oh["invoice_date"] != "") && (strtotime($oh["invoice_date"]) === false)) {
            $hdrStatus[] = "5.3";
        }
        if ($oh["principal_store_uid"] == "") {
            $hdrStatus[] = "6.0";
        }

        $postingOrderTO = $this->prepareOrderTO($oh, $hdrStatus, $dtlStatus, $dtlStatusMsg, $pPricingConflictAction);
        if (count($hdrStatus) > 0) {
            $statusMsg .= "Error(s) - Failed to Process";
        }
        if ($statusMsg == "") {
            $statusMsg = $dtlStatusMsg; // just to have something to fill in
        }
        if (empty($hdrStatus) && !empty($dtlStatus)) {
            $hdrStatus[] = "E";
        }

        //no need to check both status from here...

        // set status at header level regardless of error or not, to blank it out if no errors
        // - do not set status (even to blank it out) if no errors because that causes the exception_notified flag to be flipped to Q if
        //   the posting (postOrder) fails.
        if (!empty($hdrStatus)) {
            return (ErrorTO::NewError($statusMsg))
                ->setObject(implode(",", $hdrStatus));
        }

        return (ErrorTO::NewSuccess("Successfully validated"))
            ->setObject($postingOrderTO);
    }

    private function setPrincipalPreferences($oh)
    {
        // get the preferences for import now that the principal has been checked
        if (($oh["principal_uid"] != "") && (!isset($this->preferences[$oh["principal_uid"]]))) {
            $mfPref = $this->importDAO->getImportPreferences($oh["principal_uid"]); // importDAO changed to now always return row
            $this->preferences[$oh["principal_uid"]] = $mfPref[0]; // must set to something otherwise will attempt to retrieve every loop
        }
    }

    private function getPrincipalPricingConflictAction($oh)
    {
        // pricing_conflict_action is set in ProcessorTOH, but not for WS. WS uses import_preference
        if ($oh["pricing_conflict_action"] == "") {

            if (($oh["data_source"] == DS_WS) && ($oh["captured_by"] == "PNP")) {
                $pPricingConflictAction = $this->preferences[$oh["principal_uid"]]["pricing_conflict_action_ws_pnp"];
            } elseif (($oh["data_source"] == DS_WS) && ($oh["captured_by"] == "CHECKERS")) {
                $pPricingConflictAction = $this->preferences[$oh["principal_uid"]]["pricing_conflict_action_ws_checkers"];
            } elseif ($oh["data_source"] == DS_DIRECTSQL) {
                $pPricingConflictAction = $this->preferences[$oh["principal_uid"]]["pricing_conflict_action_directsql"];
            } else {
                // Pnp field used to be the generic before it was renamed so do this just in case
                $pPricingConflictAction = $this->preferences[$oh["principal_uid"]]["pricing_conflict_action_ws_pnp"];
            }

        } else {
            $pPricingConflictAction = $oh["pricing_conflict_action"];
        }

        return $pPricingConflictAction;
    }

    private function getPrincipalStore(&$oh, &$hdrStatus)
    {
        $mfPS = [];
        // get the store for basic validations if it is linked already - must be done after lookups
        if ($oh["principal_store_uid"] != "") {
            $mfPS = $this->storeDAO->getPrincipalStoreItem($oh["principal_store_uid"]);
            if (sizeof($mfPS) == 0) {
                echo "store is not found - should NEVER occur unless a fiddle occurred";
                $hdrStatus[] = "99"; // store is not found - should NEVER occur unless a fiddle occurred
            }
        } elseif ($oh["principal_uid"] != "") {
            // hierarchy of lookups is critical ! Failure to do so properly can result in duplicate stores.
            // 1. GLN
            if ($oh["ship_to_gln"] != "") {
                $mfPS = $this->storeDAO->getPrincipalStoreByGLN($oh["principal_uid"], $oh["ship_to_gln"]);
            }
            // 2. Old Account -- to prevent duplicated stores that were created when the client was not onlineEDI processing etc.
            if ((sizeof($mfPS) == 0) && (trim($oh["old_account"]) != "")) {
                $mfPS = $this->importDAO->getPrincipalStoreByOldAccount($oh["principal_uid"], $oh["old_account"], "");
            }
            // 3. Special Fields
            if ((sizeof($mfPS) == 0) && (trim($oh["store_lookup_ref"]) != "")) {
                if ((!isset($this->rs->onlineFileProcessingMapping[$oh["uid"]])) || ($this->rs->onlineFileProcessingMapping[$oh["uid"]]["psm_special_field_uid"] == "")) {
                    $hdrStatus[] = "1.0"; // no store special field id was designated for lookup
                } else {
                    if (substr($oh["sales_agent_store_identifier"], 0, 2) == "SP") {
                        $mfPS = $this->storeDAO->getPrincipalStoreBySF($oh["principal_uid"], substr($oh["sales_agent_store_identifier"], 2, 3), trim($oh["store_lookup_ref"]), $oh["vendor_uid"]);
                    } else {
                        $mfPS = $this->storeDAO->getPrincipalStoreBySF($oh["principal_uid"], $this->rs->onlineFileProcessingMapping[$oh["uid"]]["psm_special_field_uid"], $oh["store_lookup_ref"], $oh["vendor_uid"]);
                    }
                }
            }
            // 4. Stripped Deliver Name (usually for those principals who dont have their own unique ref for a store)
            if ((sizeof($mfPS) == 0) && ($oh["flag_stripped_deliver_name_lookup_ref"] == "Y") && (trim($oh["deliver_name"]) != "")) {
                $mfPS = $this->importDAO->getPrincipalStoreByStrippedDeliverName($oh["principal_uid"], $oh["deliver_name"], "");
            }
            if (sizeof($mfPS) > 0) {
                $oh["principal_store_uid"] = $mfPS[0]["uid"];
                $eTO = $this->postImportDAO->setOrdersHoldingPrincipalStoreUId($oh["principal_uid"], $oh["uid"], $oh["principal_store_uid"]);
                if ($eTO->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing", $eTO->description, "Y", $quietMode = false);
                }
            }
        }

        return $mfPS;
    }

    private function getDepotIfNotPSM($oh, &$hdrStatus)
    {
        // use special fields if set. At the moment no other way is catered for.
        // if depot lookup is supplied, get the uid here, as it is used in validations regardless of store being created
        $depotUId = "";
        if (!empty($oh["depot_uid"])) {
            $depotUId = $oh["depot_uid"];
        } else {
            if (
                (!empty($oh["depot_special_field_uid"])) ||
                (isset($this->rs->onlineFileProcessingMapping[$oh["uid"]]) && ($this->rs->onlineFileProcessingMapping[$oh["uid"]]["pd_special_field_uid"] != ""))
            ) {
                $pdSF = ((!empty($oh["depot_special_field_uid"])) ? $oh["depot_special_field_uid"] : $this->rs->onlineFileProcessingMapping[$oh["uid"]]["pd_special_field_uid"]);
                if ($oh["depot_lookup_ref"] != "") {
                    if ($this->rs->onlineFileProcessingMapping[$oh["uid"]]["pd_special_field_uid"] == '999') {
                        $mfD = $this->depotDAO->getPrincipalDepotBySF($oh["principal_uid"], substr($oh["sales_agent_store_identifier"], 12, 3), $oh["depot_lookup_ref"]); // dont halt processing ... let transaction suspend as store will not have been created.
                    } else {
                        $mfD = $this->depotDAO->getPrincipalDepotBySF($oh["principal_uid"], $pdSF, $oh["depot_lookup_ref"]); // dont halt processing ... let transaction suspend as store will not have been created.
                    }
                    if (!empty($mfD)) {
                        // override the value if certain harding stores
                        // commented out by mark 2012.06.21
                        /*
                         if (($oh["vendor_uid"]==V_HARDING_VENDOR) && ($oh["depot_lookup_ref"]=="FS") && in_array($oh["sales_agent_store_identifier"],array("FS10001","FS00260","FS48945","FS00256","FS40222","FS00021","FS00020","FS07004","FS00221","FS00023","FS46082","FS00220","FS48165","FS7696","FS51221","FS07005","FS00231","FS00224","FS00034","FS00099","FS00215","FS00212"))) {
                        $depotUId = 3; // UD
                        } else {
                        $depotUId = $mfD[0]["uid"];
                        }
                        */
                        $depotUId = $mfD[0]["uid"];
                    } else {
                        if ($oh["depot_lookup_ref_enforced"] == "Y") {
                            $hdrStatus[] = "2.0";
                        }
                    }
                } else {
                    /* For the time being, allow stores to be created without a depot code
                     $hdrStatus[]="2.2"; // cannot create store if depot special field value not supplied
                    */
                }
            }

        }
        return $depotUId;
    }

    private function setStoreDepot($depotUId, $oh, &$mfPS, &$hdrStatus)
    {
        // validate/update the depot if necessary, dont validate if depot_lookup_ref is not blank but not depot uid found
        if (($depotUId != "") && (sizeof($mfPS) > 0) && ($depotUId != $mfPS[0]["depot_uid"])) {
            if ($oh["update_store_depot"] == "Y") {
                $mfPS[0]["depot_uid"] = $depotUId;
                $eTO = $this->postStoreDAO->updateStoreDepot($oh["principal_uid"], $oh["principal_store_uid"], $depotUId);
                if ($eTO->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing", $eTO->description, "Y", $quietMode = false);
                    $hdrStatus[] = "2.6";
                }
            } elseif ($oh["enforce_same_depot"] == "Y") {
                $hdrStatus[] = "2.5";
            }
        }
    }

    private function setDocumentOriginAction($depotUId, $oh, &$hdrStatus)
    {
        // now that the depot is known we can determine the document origin action
        // - call it regardless of depot being empty or not, as the logic inside function needs to cater for that
        // - call regardless of hdrStatus errors or not
        if ($oh["document_origin_queried"] != "Y") {
            $mfDOA = $this->principalDAO->getPrincipalDocumentOriginAction($oh["principal_uid"], DOAT_REQUIRES_APPROVAL_REQ);
            $approvalReq = $this->principalDAO->resolvePrincipalDocumentOriginAction($mfDOA, $oh["document_type_uid"], $depotUId, $oh["data_source"], $oh["captured_by"]); // this also handles empty (false)
            // override all other statuses with just this value and discontinue processing as this document needs to be explicitly approved
            $eTO = $this->postImportDAO->setOrdersHoldingDocumentOrigin($oh["principal_uid"], $oh["uid"], (($approvalReq) ? "R.A" : false));
            if ($eTO->type != FLAG_ERRORTO_SUCCESS) {
                BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing 1", $eTO->description, "Y", $quietMode = false);
                echo "Error 99 Occurred for Document " . $postingOrderTO->documentNumber . " Unable to set document origin status";
                $hdrStatus[] = "99";
            }
            $this->dbConn->dbinsQuery("commit");
            if ($approvalReq) return true;
        }

        return false;
    }

    private function updateStore($oh): ErrorTO
    {
        if (
            ($oh["principal_store_uid"] != "") &&
            (isset($this->rs->storeArr[$oh["uid"]])) &&
            ($this->rs->storeArr[$oh["uid"]]["update_principal_store"] == "Y")
        ) {
            $postingStoreEDIUpdateTO = new PostingStoreEDIUpdateTO;
            $postingStoreEDIUpdateTO->principalStoreUId = $oh["principal_store_uid"];
            $postingStoreEDIUpdateTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["deliver_name"]);
            $postingStoreEDIUpdateTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["deliver_add1"]);
            $postingStoreEDIUpdateTO->deliverAdd2 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["deliver_add2"]);
            $postingStoreEDIUpdateTO->deliverAdd3 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["deliver_add3"]);
            $postingStoreEDIUpdateTO->billName = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["bill_name"]);
            $postingStoreEDIUpdateTO->billAdd1 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["bill_add1"]);
            $postingStoreEDIUpdateTO->billAdd2 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["bill_add2"]);
            $postingStoreEDIUpdateTO->billAdd3 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["bill_add3"]);
            $postingStoreEDIUpdateTO->eanCode = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["gln_code"]);
            $postingStoreEDIUpdateTO->vatNumber = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["vat_number"]);
            $postingStoreEDIUpdateTO->vatNumber2 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["vat_number_2"]);

            // these fields are controlled by a flag
            $postingStoreEDIUpdateTO->deliveryDay = (($this->rs->storeArr[$oh["uid"]]["update_principal_store"] == "Y") ? mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["delivery_day"]) : false);
            $postingStoreEDIUpdateTO->noVAT = (($this->rs->storeArr[$oh["uid"]]["no_vat"] == "Y") ? mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["no_vat"]) : false);
            $postingStoreEDIUpdateTO->status = (($this->rs->storeArr[$oh["uid"]]["update_store_status"] == "Y") ? FLAG_STATUS_ACTIVE : false);

            return $this->postStoreDAO->setStoreFieldsFromEDI($postingStoreEDIUpdateTO);
        }
        return ErrorTO::NewSuccess("No store update required");
    }

    private function updateStoreSF($oh, &$hdrStatus)
    {
        if ($oh["principal_store_uid"] != "") {
            if (isset($this->rs->specialFieldArr[$oh["uid"]])) {
                foreach ($this->rs->specialFieldArr[$oh["uid"]] as $sfRow) {
                    if ($sfRow["allow_update"] != "Y") continue;

                    if (trim($sfRow["value"]) == "") {
                        // $hdrStatus[]="2.4"; // dont give an error here as the user cannot see special field, and cannot update the EDI file
                        echo "<br>Update of special field from EDI could not be performed --> entity:{$oh["principal_store_uid"]} field:{$sfRow["field_uid"]} value:{$sfRow["value"]}<br>";
                        return true;
                    }

                    $postingSpecialFieldTO = new PostingSpecialFieldTO;
                    $postingSpecialFieldTO->DMLType = "UPDATE";
                    $postingSpecialFieldTO->principal = $oh["principal_uid"];
                    $postingSpecialFieldTO->deliverName = ((!isset($this->rs->storeArr[$oh["uid"]])) ? "" : mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["deliver_name"])); // spefial fields need not have a store level - only used to passback msg
                    $postingSpecialFieldTO->fielduid = $sfRow["field_uid"];
                    $postingSpecialFieldTO->entityUId = $oh["principal_store_uid"];
                    $postingSpecialFieldTO->value = $sfRow["value"];
                    $postingSpecialFieldTO->skipValidation = (($sfRow["skip_validation"] == "Y") ? "Y" : "N");

                    $eTO = $this->postMiscDAO->postSpecialField($postingSpecialFieldTO);
                    if ($eTO->type == FLAG_ERRORTO_SUCCESS) {
                        // do nothing
                    } else {
                        // processing MUST be stopped here, else store will keep getting created every run because it wont find store on special fields if using them
                        // $hdrStatus[]="2.3"; // dont give an error here as the user cannot see special field, and cannot update the EDI file
                        echo "<br>entity:{$postingSpecialFieldTO->entityUId} field:{$postingSpecialFieldTO->fielduid} value:{$postingSpecialFieldTO->value} " . $eTO->description . "<br>";
                        // unfortunately, nobody will know about this error.
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function createStore(&$oh, $pPricingConflictAction, $depotUId, &$hdrStatus)
    {
        if (
            ($oh["principal_store_uid"] == "") &&
            (isset($this->rs->storeArr[$oh["uid"]])) &&
            ($this->rs->storeArr[$oh["uid"]]["deliver_name"] != "")
        ) {
            // FIND CHAIN - use generic chain if VENDOR Pricing, else use full lookup
            $chainUId = "";
            // use special fields if set. At the moment no other way is catered for.
            // sp12 3ch123wh123
            // 012345678901234
            if ($pPricingConflictAction == PCA_USE_VENDOR) {
                $mfGC = $this->chainDAO->getPrincipalChainByOldCode($oh["principal_uid"], CHAIN_GENERIC_OLD_CODE); // generic chain
                if (!empty($mfGC)) {
                    $chainUId = $mfGC[0]["uid"];
                } else {
                    /* For the time being, allow stores to be created without a chain code
                     $hdrStatus[]="4.2"; // Cannot create the store automatically as the generic chain could not be found
                          */
                }
            } elseif ($oh["chain_lookup_ref"] != "") {
                if (isset($this->rs->onlineFileProcessingMapping[$oh["uid"]])) {
                    if ($this->rs->onlineFileProcessingMapping[$oh["uid"]]["pcm_special_field_uid"] == '999') {
                        $mfPC = $this->chainDAO->getPrincipalChainBySF($oh["principal_uid"], substr($oh["sales_agent_store_identifier"], 7, 3), $oh["chain_lookup_ref"]); // dont halt processing ... let transaction suspend as store will not have been created.
                    } else {
                        $mfPC = $this->chainDAO->getPrincipalChainBySF($oh["principal_uid"], $this->rs->onlineFileProcessingMapping[$oh["uid"]]["pcm_special_field_uid"], $oh["chain_lookup_ref"]); // dont halt processing ... let transaction suspend as store will not have been created.
                    }
                    if (!empty($mfPC)) {
                        // override the value if certain harding stores
                        $chainUId = $mfPC[0]["uid"];
                    } else {
                        /* For the time being, allow stores to be created without a chain code
                         $hdrStatus[]="3.0"; // cannot create store if chain special field returned no values on lookup against PCM
                        */
                    }
                } else {
                    $hdrStatus[] = "3.1"; // cannot create store if chain special field id is not specified
                }
            } else {
                /* For the time being, allow stores to be created without a chain code
                 $hdrStatus[]="3.2"; // cannot create store if chain special field value not supplied
                */
            }

            if (sizeof($hdrStatus) == 0) {
                $postingStoreTO = new PostingStoreTO;
                $postingStoreTO->DMLType = "INSERT";
                $postingStoreTO->principalStoreUId = "";
                $postingStoreTO->principal = $oh["principal_uid"];
                $postingStoreTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["deliver_name"]);
                $postingStoreTO->deliverAdd1 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["deliver_add1"]);
                $postingStoreTO->deliverAdd2 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["deliver_add2"]);
                $postingStoreTO->deliverAdd3 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["deliver_add3"]);
                $postingStoreTO->billName = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["bill_name"]);
                $postingStoreTO->billAdd1 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["bill_add1"]);
                $postingStoreTO->billAdd2 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["bill_add2"]);
                $postingStoreTO->billAdd3 = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["bill_add3"]);
                $postingStoreTO->eanCode = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["gln_code"]);
                $postingStoreTO->vatNumber = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["vat_number"]);
                $postingStoreTO->depot = $depotUId; // allow depot to be blank
                $postingStoreTO->deliveryDay = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["delivery_day"]);
                $postingStoreTO->noVAT = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["no_vat"]);
                $postingStoreTO->onHold = "0";
                $postingStoreTO->chain = $chainUId; // allow chain to be blank
                $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
                $postingStoreTO->branchCode = "";
                $postingStoreTO->oldAccount = $this->rs->storeArr[$oh["uid"]]["old_account"]; // if null, let posting alloc sequence automatically
                $postingStoreTO->allocatePermissionsUserList = "";
                $postingStoreTO->ledgerBalance = "";
                $postingStoreTO->ledgerCreditLimit = "";
                $postingStoreTO->status = FLAG_STATUS_ACTIVE;
                $postingStoreTO->vendorCreatedByUId = $oh["vendor_uid"];
                $postingStoreTO->ownedBy = $this->rs->storeArr[$oh["uid"]]["owned_by"];

                // special field(s)
                $sfTOArr = [];
                $foundSpecialFieldValue = false;
                if ((isset($this->rs->onlineFileProcessingMapping[$oh["uid"]])) &&
                    // ($this->rs->onlineFileProcessingMapping[$oh["uid"]]["psm_special_field_uid"]!="") &&
                    (isset($this->rs->specialFieldArr[$oh["uid"]]))
                ) {
                    foreach ($this->rs->specialFieldArr[$oh["uid"]] as $sfRow) {
                        $postingSpecialFieldTO = new PostingSpecialFieldTO;
                        $postingSpecialFieldTO->DMLType = "INSERT";
                        $postingSpecialFieldTO->principal = $oh["principal_uid"];
                        $postingSpecialFieldTO->deliverName = mysqli_real_escape_string($this->dbConn->connection, $this->rs->storeArr[$oh["uid"]]["deliver_name"]);
                        $postingSpecialFieldTO->fielduid = $sfRow["field_uid"];
                        $postingSpecialFieldTO->entityUId = ""; // the processor will assign this when store is created
                        $postingSpecialFieldTO->value = $sfRow["value"];
                        $postingSpecialFieldTO->skipValidation = (($sfRow["skip_validation"] == "Y") ? "Y" : "N");

                        $sfTOArr[] = $postingSpecialFieldTO;

                        if (($this->rs->onlineFileProcessingMapping[$oh["uid"]]["psm_special_field_uid"] == $postingSpecialFieldTO->fielduid) &&
                            (trim($postingSpecialFieldTO->value) != "") &&
                            (trim($oh["store_lookup_ref"]) == trim($postingSpecialFieldTO->value))) {
                            $foundSpecialFieldValue = true;
                        } elseif ($this->rs->onlineFileProcessingMapping[$oh["uid"]]["psm_special_field_uid"] == 999) {
                            if ((trim(substr($oh["sales_agent_store_identifier"], 2, 3)) == $postingSpecialFieldTO->fielduid) &&
                                (trim($postingSpecialFieldTO->value) != "") &&
                                (trim(substr($oh["store_lookup_ref"], 0, 9)) == trim($postingSpecialFieldTO->value))) {
                                $foundSpecialFieldValue = true;
                            }
                        }
                    }

                    $this->dbConn->dbinsQuery("commit;"); // commit any current status / uid updates before creating store

                    // MUST ONLY CREATE if atleast one of lookup vals are filled in as otherwise the risk is too high for store to keep getting created continually each run
                    if ($foundSpecialFieldValue ||
                        (($oh["old_account"] == $this->rs->storeArr[$oh["uid"]]["old_account"]) && (trim($oh["old_account"] != ""))) ||
                        (($oh["ship_to_gln"] == $this->rs->storeArr[$oh["uid"]]["gln_code"]) && (trim($oh["ship_to_gln"] != ""))) ||
                        (($oh["flag_stripped_deliver_name_lookup_ref"] == "Y") && (trim($oh["deliver_name"]) != ""))
                    ) {

                        $eTO = $this->postStore($postingStoreTO, $postingSpecialFieldTOArr = $sfTOArr);
                        if (($eTO->type != FLAG_ERRORTO_SUCCESS) || ($postingStoreTO->principalStoreUId == "")) {
                            $statusMsg .= $eTO->description;
                            $hdrStatus[] = "4.0"; // store failed to be created
                            $this->dbConn->dbinsQuery("rollback;");
                        } else {
                            $oh["principal_store_uid"] = $postingStoreTO->principalStoreUId;
                            $eTO = $this->postImportDAO->setOrdersHoldingPrincipalStoreUId($oh["principal_uid"], $oh["uid"], $oh["principal_store_uid"]);
                            if ($eTO->type != FLAG_ERRORTO_SUCCESS) {
                                BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing", $eTO->description, "Y", $quietMode = false);
                            }
                        }

                    } else $hdrStatus[] = "4.1";
                }
            } // end create store
        }
    }

    private function postStore($postingStoreTO, $postingSpecialFieldTOArr = false)
    {
        $eTO = $this->postStoreDAO->postPrincipalStore($postingStoreTO);
        if ($eTO->type == FLAG_ERRORTO_SUCCESS) {
            $postingStoreTO->principalStoreUId = $eTO->identifier;
            // create any special fields
            if ($postingSpecialFieldTOArr !== false) {
                foreach ($postingSpecialFieldTOArr as $postingSpecialFieldTO) {
                    if (trim($postingSpecialFieldTO->value) == "") continue;
                    $postingSpecialFieldTO->entityUId = $postingStoreTO->principalStoreUId;
                    $eTO = $this->postMiscDAO->postSpecialField($postingSpecialFieldTO); // NB : Stores don't support multiple values, if we need to, then you cant call individually like this as prev will be deleted
                    if ($eTO->type == FLAG_ERRORTO_SUCCESS) {
                        // do nothing
                    } else {
                        // processing MUST be stopped here, else store will keep getting created every run because it won't find store on special fields if using them
                        $eTO->description = "Could not create store special fields in EDI import: " . $eTO->description;
                        // BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing", "Could not create store in EDI import: ".$eTO->description, "Y", $quietMode = false);
                        return $eTO;
                    }
                }
            }

        } else {
            // if error, let it suspend in OH
            $postingStoreTO->principalStoreUId = "";
            BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing", "Could not create store in EDI import: " . $eTO->description, "Y", $quietMode = false);
            $eTO->description = "Could not create store in EDI import: " . $eTO->description . " This error is purposely ignored to allow execution to continue.";
        }

        return $eTO;
    }

    private function prepareOrderTO($oh, &$hdrStatus, &$dtlStatus, &$dtlStatusMsg, $pPricingConflictAction)
    {
        // set up header
        $postingOrderTO = new PostingOrderTO;
        $postingOrderTO->DMLType = "INSERT";
        $postingOrderTO->storeChainUId = $oh["principal_store_uid"];
        $postingOrderTO->principalUId = $oh["principal_uid"];
        $postingOrderTO->orderNumber = $oh["reference"]; // the PO number
        $postingOrderTO->deliveryInstructions = addslashes($oh["delivery_instructions"]);
        $postingOrderTO->documentDate = $oh["order_date"];
        $postingOrderTO->deliveryDueDate = $oh["expiry_date"];
        $postingOrderTO->deliveryDate = $oh["delivery_date"];
        $postingOrderTO->expiryDate = $oh["expiry_date"];
        $postingOrderTO->offInvoiceDiscount = $oh["off_invoice_discount"];
        $postingOrderTO->offInvoiceDiscountType = $oh["off_invoice_discount_type"];
        $postingOrderTO->invoiceDate = $oh["invoice_date"];
        $postingOrderTO->requestedDeliveryDate = $oh["requested_delivery_date"];
        $postingOrderTO->captureUserUId = 0;
        $postingOrderTO->capturedBy = $oh["captured_by"];
        $postingOrderTO->ediCreated = "N";
        $postingOrderTO->incomingFileName = $oh["incoming_file"];
        $postingOrderTO->documentType = $oh["document_type_uid"];
        $postingOrderTO->dataSource = $oh["data_source"];
        $postingOrderTO->documentNumber = $oh["document_number"];
        $postingOrderTO->clientDocumentNumber = $oh["client_document_number"];
        $postingOrderTO->salesAgentStoreIdentifier = $oh["sales_agent_store_identifier"];
        $postingOrderTO->debtorsStoreIdentifier = $oh["debtors_store_identifier"];
        $postingOrderTO->vendorUId = $oh["vendor_uid"];
        $postingOrderTO->generalReference1 = $oh["general_reference_1"];
        $postingOrderTO->generalReference2 = $oh["general_reference_2"];
        $postingOrderTO->vendorBuyingGroupCode = $oh["chain_lookup_ref"];
        $postingOrderTO->skipUniqueOrderNoFlag = $oh["force_skip_unique_order_no"];  //skip the unique order check
        $postingOrderTO->buyerAccountReference = $oh["vendor_reference"];
        $postingOrderTO->uniqueCreatorId = $oh["ws_unique_creator_id"];
        $postingOrderTO->orderStartStatus = $oh["document_status_uid"];
        $postingOrderTO->invoiceNumber = $oh["invoice_number"];
        $postingOrderTO->fileLogUId = $oh["file_log_uid"];
        $postingOrderTO->additionalType = $oh["additional_type"];
        $postingOrderTO->additionalDetails = $oh["additional_details"];
        $postingOrderTO->mfPS = $oh["mfPS"]; // new attribute

        // We need to differentiate between edi imports (e.g: Instant Trading) who set the depotUId only when the store is to be created!
        if (($oh["user_modified_depot"] == "Y") && (strval($oh["depot_uid"]) != "")) {
            $postingOrderTO->processedDepotUId = $oh["depot_uid"];
            $postingOrderTO->forceDepotUId = $oh["depot_uid"];
        }

        // setup the unique store ref. This may not always be correct, as if the adaptor sent through old_account as well as SF and SF successfully returned a store, then old account is used for setup
        if ($oh["old_account"] != "") $postingOrderTO->principalStoreIdentifier = $oh["old_account"];
        elseif ($oh["store_lookup_ref"] != "") $postingOrderTO->principalStoreIdentifier = $oh["store_lookup_ref"];
        elseif ($oh["ship_to_gln"] != "") $postingOrderTO->principalStoreIdentifier = $oh["ship_to_gln"];
        elseif ($oh["flag_stripped_deliver_name_lookup_ref"] == "Y") $postingOrderTO->principalStoreIdentifier = "NW" . $oh["principal_store_uid"];
        else $postingOrderTO->principalStoreIdentifier = "NW" . $oh["principal_store_uid"];

        // Process Detail lines
        if ((!isset($this->rs->detailArr[$oh["uid"]])) || (sizeof($this->rs->detailArr[$oh["uid"]]) == 0)) {
            $hdrStatus[] = "6.1";
        }

        if (sizeof($hdrStatus) == 0) {

            foreach ($this->rs->detailArr[$oh["uid"]] as &$ohd) {
                $hasPriceDifference = false;
                $skipPriceNotif = false;
                $singleRowDtlStatus = [];

                $this->createProduct($oh, $ohd, $singleRowDtlStatus, $pPricingConflictAction);
                $this->updateProduct($postingOrderTO, $oh, $ohd, $singleRowDtlStatus);

                if ($ohd["principal_product_uid"] == "") {
                    $singleRowDtlStatus[] = "7.0";
                }

                $this->checkPricingComputation($oh, $ohd, $singleRowDtlStatus);

                // check quantities
                if (abs(floor($ohd["quantity"])) != abs($ohd["quantity"])) {
                    $singleRowDtlStatus[] = "7.1";
                }

                if (sizeof($singleRowDtlStatus) == 0) {
                    $postingOrderDetailTO = new PostingOrderDetailTO;
                    $postingOrderDetailTO->productUId = $ohd["principal_product_uid"];
                    $postingOrderDetailTO->quantity = $ohd["quantity"];
                    $postingOrderDetailTO->pallets = $ohd["pallets"];
                    $postingOrderDetailTO->clientLineNo = $ohd["client_line_no"];
                    $postingOrderDetailTO->clientPageNo = $ohd["client_page_no"];
                    $postingOrderDetailTO->mass = $ohd["mass"];
                    $postingOrderDetailTO->volume = $ohd["volume"];
                    $postingOrderDetailTO->originalProductCode = $ohd["original_product_code"]; // if modified differs from unmodified
                    $postingOrderDetailTO->wsUniqueCreatorId = $ohd["ws_unique_creator_id"];
                    $postingOrderDetailTO->additionalType = $ohd["additional_type"];
                    $postingOrderDetailTO->mfPP = $ohd["mfPP"]; // new attribute

                    if (($oh["document_type_uid"] == DT_ORDINV) || ($oh["document_type_uid"] == DT_ORDINV_ZERO_PRICE) || ($oh["document_type_uid"] == DT_SALES_ORDER)) {

                        // Use a different flag for pricing conflict as the user could have overridden it.
                        // It will be either blank meaning it has not been overridden or it will be PCA_USE_OWN / PCA_USE_VENDOR (will not be STOP)
                        $pPricingConflictActionReVal = ($ohd["override_price_type"] == "") ? $pPricingConflictAction : $ohd["override_price_type"];

                        // check against RT prices, exclusive of bulk discounting
                        $hasPriceDifference = $this->checkPricingDifference($postingOrderTO, $postingOrderDetailTO, $oh, $ohd, $singleRowDtlStatus, $pPricingConflictActionReVal);

                        // determine which price to use
                        $this->choosePricing($oh, $ohd, $postingOrderTO, $postingOrderDetailTO, $pPricingConflictActionReVal, $hasPriceDifference, $singleRowDtlStatus);

                    } // end pricing where it is validated and enforced

                    // stock transfer just carry through whatever values if supplied - used by warehouses to bill the principal on value of stock moved
                    // uplifts will have "Pro Forma" pricing attached controlled by the principal_document_type table. Although any doc type can be loaded in this
                    //    table, we limit to uplifts here as with the depot extract file
                    elseif (($oh["document_type_uid"] == DT_STOCKTRANSFER) ||
                        ($oh["document_type_uid"] == DT_UPLIFTS) ||
                        ($oh["document_type_uid"] == DT_BUYER_ORIGINATED_CREDIT_CLAIM) ||
                        ($oh["document_type_uid"] == DT_BUYER_ORIGINATED_DEBIT_CLAIM)) {
                        $postingOrderDetailTO->listPrice = trim($ohd["list_price"]);
                        $postingOrderDetailTO->discountValue = trim($ohd["discount_value"]);
                        $postingOrderDetailTO->nettPrice = trim($ohd["nett_price"]);
                        $postingOrderDetailTO->extPrice = trim($ohd["ext_price"]);
                        $postingOrderDetailTO->totPrice = trim($ohd["total_price"]);
                        $postingOrderDetailTO->vatAmount = trim($ohd["vat_amount"]);
                        $postingOrderDetailTO->productVatRate = trim($ohd["vat_rate"]);

                        // checkers only send through a list price, and we have to work out whether it has vat or not included
                        // to derive the remaining fields
                        if (($postingOrderTO->capturedBy == CB_CHECKERS_WS) &&
                            (
                                ($oh["document_type_uid"] == DT_BUYER_ORIGINATED_CREDIT_CLAIM) ||
                                ($oh["document_type_uid"] == DT_BUYER_ORIGINATED_DEBIT_CLAIM)
                            )
                        ) {

                            [$calculatedVATRate, $msg] = $this->productDAO->getCalculatedVATRate($postingOrderTO->principalUId,
                                $postingOrderTO->storeChainUId,
                                $postingOrderDetailTO->productUId,
                                $this->storeDAO,
                                $postingOrderTO->mfPS,
                                $postingOrderDetailTO->mfPP
                            );
                            if ($calculatedVATRate === false) {
                                $singleRowDtlStatus[] = "7.2";
                            } else {
                                $postingOrderDetailTO->productVatRate = $calculatedVATRate;
                                if (floatval($calculatedVATRate) > 0) {
                                    // assume then that the CHECKERS list pricing is inclusive of VAT
                                    $postingOrderDetailTO->listPrice = round($postingOrderDetailTO->listPrice / ($postingOrderDetailTO->productVatRate / 100 + 1), 2);
                                }
                                $postingOrderDetailTO->nettPrice = $postingOrderDetailTO->listPrice;
                                $postingOrderDetailTO->extPrice = $postingOrderDetailTO->nettPrice * $postingOrderDetailTO->quantity;
                                $postingOrderDetailTO->vatAmount = round($postingOrderDetailTO->extPrice * ($postingOrderDetailTO->productVatRate / 100), 2);
                                $postingOrderDetailTO->totPrice = $postingOrderDetailTO->extPrice + $postingOrderDetailTO->vatAmount;

                            }
                        }
                    }

                    // utilise ammended quantities if it qualifies
                    if (($ohd["user_modified"] == "Y") && (strval($ohd["amended_quantity"]) != "")) {
                        $postingOrderDetailTO->userModified = "Y";
                        $postingOrderDetailTO->quantity = $ohd["amended_quantity"];
                        if (floatval($postingOrderDetailTO->nettPrice) > 0) {
                            $postingOrderDetailTO->extPrice = $postingOrderDetailTO->nettPrice * $postingOrderDetailTO->quantity;
                            // $postingOrderDetailTO->vatRate=trim($ohd["vat_rate"]); // not a TO field
                            $postingOrderDetailTO->productVatRate = trim($ohd["vat_rate"]);
                            if (floatval($postingOrderDetailTO->productVatRate) > 0) $postingOrderDetailTO->vatAmount = round($postingOrderDetailTO->extPrice * (floatval($postingOrderDetailTO->productVatRate) / 100), 2);
                            else $postingOrderDetailTO->vatAmount = 0;
                            $postingOrderDetailTO->totPrice = $postingOrderDetailTO->extPrice + $postingOrderDetailTO->vatAmount;
                        }
                    }

                    //end of product loop
                    //if capture = pnp
                    //and principal is enabled for items_per_case
                    $pProductConversionItemsPerCasePNP = $this->preferences[$oh["principal_uid"]]["pnp_conversion_items_per_case"] ?? 'N';
                    $pProductConversionItemsPerCaseCheckers = $this->preferences[$oh["principal_uid"]]["checkers_conversion_items_per_case"] ?? 'N';
                    $pProductConvertCases = ($ohd['convert_cases_to_units'] ?? 'N');

                    if ($postingOrderTO->documentType == DT_ORDINV && $pProductConvertCases == 'Y' &&
                        (
                            ($postingOrderTO->capturedBy == 'PNP' && $pProductConversionItemsPerCasePNP == 'Y') ||
                            ($postingOrderTO->capturedBy == 'CHECKERS' && $pProductConversionItemsPerCaseCheckers == 'Y')
                        )
                    ) {

                        $safeItemsPerCase = (int)($ohd['items_per_case'] ?? 1);

                        $postingOrderDetailTO->oldQuantity = $postingOrderDetailTO->quantity;
                        $postingOrderDetailTO->oldPrice = $postingOrderDetailTO->nettPrice;

                        $postingOrderDetailTO->quantity = (int)($postingOrderDetailTO->quantity * $safeItemsPerCase);
                        $postingOrderDetailTO->nettPrice = round($postingOrderDetailTO->nettPrice / $safeItemsPerCase, 4);

                        //this minor hack but not work for all principals...
                        $postingOrderDetailTO->priceOverrideValue = $postingOrderDetailTO->nettPrice;
                    }

                    $postingOrderTO->detailArr[] = $postingOrderDetailTO;

                } // end sizeof detailArr

                // set status at detail level regardless of error or not, so as to blank it out if no errors
                $eTO = $this->postImportDAO->setOrdersHoldingDetailStatusItem($ohd["uid"], implode(",", $singleRowDtlStatus));
                if ($eTO->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing 1", $eTO->description, "Y", $quietMode = false);
                    echo "Error 99 Occurred for Document " . $postingOrderTO->documentNumber . " Status:" . implode(",", $singleRowDtlStatus);
                    $hdrStatus[] = "99";
                }

                if (count($singleRowDtlStatus) > 0) {
                    $dtlStatusMsg = "Error(s) occurred at detail level- Failed to Process";
                }

                $dtlStatus = array_merge($dtlStatus, $singleRowDtlStatus);
            } // end products loop
        } // end if detail lines

        return $postingOrderTO;
    }

    private function createProduct($oh, &$ohd, &$dtlStatus, $pPricingConflictAction)
    {
        // set the product uid, using product_gtin as first priority
        $ohd["mfPP"] = $mfPP = [];
        if ($ohd["principal_product_uid"] == "") {
            // set the product uid, using innercasing gtin as first priority
            if ($ohd["product_sku_gtin"] != "") {
                $mfPP = $this->productDAO->getPrincipalProductByICGTIN($oh["principal_uid"], $ohd["product_sku_gtin"]);
            }
            // set the product uid, using outercasing gtin as second priority
            if ((sizeof($mfPP) == 0) && ($ohd["product_gtin"] != "")) {
                $mfPP = $this->productDAO->getPrincipalProductByOCGTIN($oh["principal_uid"], $ohd["product_gtin"]);
            }
            // set the product uid, using Shrink gtin as third priority
            if ((sizeof($mfPP) == 0) && ($ohd["product_gtin"] != "")) {
                $mfPP = $this->productDAO->getPrincipalProductBySKGTIN($oh["principal_uid"], $ohd["product_gtin"]);
            }
            // set the product uid, using product_code as forth priority
            if ((sizeof($mfPP) == 0) && ($ohd["product_code"] != "")) {
                $mfPP = $this->productDAO->getPrincipalProductByCode($oh["principal_uid"], $ohd["product_code"]);
            }
        }
        if (sizeof($mfPP) > 0) {
            $ohd['principal_product_uid'] = $mfPP[0]['uid'];
            $ohd['items_per_case'] = $mfPP[0]['items_per_case'];
            $ohd['convert_cases_to_units'] = $mfPP[0]['convert_cases_to_units'];

            if(empty($ohd['convert_cases_to_units'])){
                die('error - invalid convert to cases');
            }

            $ohd['mfPP'] = $mfPP;

            $eTO = $this->postImportDAO->setOrdersHoldingPrincipalProductUId($ohd["uid"], $ohd['principal_product_uid'], $mfPP[0]['items_per_case'], $mfPP[0]['convert_cases_to_units']);
            if ($eTO->type != FLAG_ERRORTO_SUCCESS) {
                BroadcastingUtils::sendAlertEmail('Error in ordersHoldingProcessing', $eTO->description, 'Y', $quietMode = false);
            }
        }

        // create the product if it doesnt exist, remember to enforce the product identifiers - only product_Code is required as that is a key, not GTIN, and the rule
        // is that if you want to create the product, then you must populate product_code properly from adaptor
        if (
            ($oh["insert_product"] == "Y") &&
            ($ohd["principal_product_uid"] == "") &&
            (trim($ohd["product_code"]) != "") &&
            ($pPricingConflictAction == PCA_USE_VENDOR)
        ) {
            $postingProductTO = new PostingProductTO;
            $postingProductTO->DMLType = "INSERT";
            $postingProductTO->principal = $oh["principal_uid"];
            $postingProductTO->productCode = $ohd["product_code"];
            $postingProductTO->productDescription = substr(trim($ohd["product_name"]), 0, 50);
            /* only barcodes from productSubmit form should manage barcodes due to complexities involved
             $postingProductTO->skuGTINList=array($ohd["product_sku_gtin"]); // Although we populate this field, it actually gets inserted into principal_product_depot_gtin table.
            $postingProductTO->outerCasingGTINList=array($ohd["product_gtin"]); // Although we populate this field, it actually gets inserted into principal_product_depot_gtin table.
            */
            $postingProductTO->weight = "0";
            if (empty($ohd["insert_vat_rate"])) {
                $postingProductTO->productVATRate = ((strval(trim($ohd["vat_rate"])) == "") ? VAL_VAT_RATE_TBLSTD : trim($ohd["vat_rate"]));
            } else {
                $postingProductTO->productVATRate = trim($ohd["insert_vat_rate"]);
            }
            $postingProductTO->enforcePalletConsignment = "N";

            $this->dbConn->dbinsQuery("commit;"); // commit any current status / uid updates before creating product

            $eTO = $this->postProduct($postingProductTO);
            if (($eTO->type != FLAG_ERRORTO_SUCCESS) || ($postingProductTO->UId == "")) {
                $dtlStatus[] = "7.1"; // product failed to be created
                $this->dbConn->dbinsQuery("rollback;");
            } else {
                $ohd["principal_product_uid"] = $postingProductTO->UId;
                $ohd["items_per_case"] = (int)$postingProductTO->itemsPerCase;
                $eTO = $this->postImportDAO->setOrdersHoldingPrincipalProductUId($ohd["uid"], $ohd["principal_product_uid"], $postingProductTO->itemsPerCase);
                if ($eTO->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing", $eTO->description, "Y", $quietMode = false);
                }
            }
        }
    }

    private function postProduct($postingProductTO)
    {
        $eTO = $this->postProductDAO->postProduct($postingProductTO, SESSION_ADMIN_USERID);
        if ($eTO->type == FLAG_ERRORTO_SUCCESS) {
            $postingProductTO->UId = $eTO->identifier;
        } else {
            // if error, let it suspend in OH
            $postingProductTO->UId = "";
            BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing", "Could not create product in EDI import: " . $eTO->description, "Y", $quietMode = false);
            $eTO->description = "Could not create product in EDI import: " . $eTO->description . " This error is purposely ignored to allow execution to continue.";
        }

        return $eTO;
    }

    private function updateProduct($postingOrderTO, $oh, $ohd, &$dtlStatus)
    {
        if (($oh["update_product"] == "Y") &&
            (in_array($postingOrderTO->documentType, [DT_ORDINV, DT_ORDINV_ZERO_PRICE])) &&
            (!empty($ohd["principal_product_uid"]))) {

            // This updates only certain fields, and only for principals using vendor pricing details

            if ($ohd["update_product_status"] == "Y") $tStatus = FLAG_STATUS_ACTIVE;
            else $tStatus = false;

            if ($ohd["update_product_vat_rate"] == "Y") $tVATRate = $ohd["vat_rate"];
            else $tVATRate = false;

            $eTO = $this->postProductDAO->postProductOFP($oh["principal_uid"], $ohd["principal_product_uid"], $oh["created_date"], $vatRate = $tVATRate, $description = ((!empty($ohd["product_name"])) ? $ohd["product_name"] : false), $status = $tStatus);
            if ($eTO->type != FLAG_ERRORTO_SUCCESS) {
                $dtlStatus[] = "7.2";
                BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing", $eTO->description, "Y", $quietMode = false);
            }
        }
    }

    private function checkPricingComputation($oh, $ohd, &$dtlStatus)
    {
        // check pricing
        if (($ohd["list_price"] != "") && ($oh["skip_computation_check"] != "Y")) {
            // vendor pricing does not compute, allow 1.5% discrepancy or as constant specifies
//      if(!is_numeric($ohd['vat_amount'])) { echo "NP"; print_r($ohd); echo "<br>";}
//      if(!is_numeric($ohd['quantity'])) { echo "QT"; print_r($ohd); echo "<br>";}

            if (
                (abs(round($ohd["nett_price"] * $ohd["quantity"] + (float)$ohd["vat_amount"], 2) - round($ohd["total_price"], 2)) > ($ohd["total_price"] * VAL_PRICE_VARIATION_ALLOWED)) ||
                (abs(round($ohd["ext_price"] + (float)$ohd["vat_amount"], 2) - round($ohd["total_price"], 2)) > ($ohd["total_price"] * VAL_PRICE_VARIATION_ALLOWED))
            ) {
                $dtlStatus[] = "8.0";
            }

            // do not allow negative pricing
            if ($ohd["nett_price"] < 0) {
                $dtlStatus[] = "8.1";
            }
        }
    }

    private function checkPricingDifference($postingOrderTO, $postingOrderDetailTO, $oh, $ohd, &$dtlStatus, $pPricingConflictActionReVal)
    {
        $hasPriceDifference = false;
        // check against RT prices, exclusive of bulk discounting
        $mfAP = $this->productDAO->getActivePricesForProduct($postingOrderTO->principalUId, $postingOrderTO->storeChainUId, $postingOrderDetailTO->productUId);
        if ((sizeof($mfAP) == 0) || ($mfAP[0]["price"] < 0)) {
            if (($pPricingConflictActionReVal == PCA_USE_OWN) || ($pPricingConflictActionReVal == PCA_STOP)) {
                $dtlStatus[] = "9.0"; // prevent the posting if instructed to use RT price
            }
            $hasPriceDifference = true;
        } else {
            // set flag if price difference
            if (abs(round($mfAP[0]["price"], 2) - round($ohd["nett_price"], 2)) > 0) {
                if ($mfAP[0]["price"] != 0) $hasPriceDifference = true; // ( allow a zero price which is sometimes used for non RT depots )
            }
        }

        // set flag for price difference, skip if there is a price diff but we are not using RT prices anyway
        if (
            ($hasPriceDifference) &&
            (
                (sizeof($mfAP) > 0) ||
                (
                    (sizeof($mfAP) == 0) &&
                    ($pPricingConflictActionReVal != PCA_USE_VENDOR)
                )
            ) &&
            ($ohd["price_diff_notified"] == "") &&
            (
                // if not specified at lower level with a N or Y, then take higher level. Lower level is the setting against
                // onlineFileProcessingMapping set during ProcessorTOH. Higher level ($preferences) is the import_preference setting
                // which should really only be for PnP WS
                (($this->preferences[$oh["principal_uid"]]["price_variance_notification"] == "Y") && ($oh["check_price_variance"] == "")) ||
                ($oh["check_price_variance"] == "Y")
            ) // the generic, top-level setting for principal or the file preference
        ) {
            // only send out once
            $eTO = $this->postImportDAO->setOrdersHoldingPriceDiffNotified($ohd["uid"], FLAG_STATUS_QUEUED);
            if ($eTO->type != FLAG_ERRORTO_SUCCESS) {
                BroadcastingUtils::sendAlertEmail("Error in setOrdersHoldingPriceDiffNotified", $eTO->description, "Y", $quietMode = false);
                $dtlStatus[] = "9.2";
            }
        }
        return $hasPriceDifference;
    }

    private function choosePricing($oh, $ohd, $postingOrderTO, &$postingOrderDetailTO, $pPricingConflictActionReVal, $hasPriceDifference, &$dtlStatus)
    {
        // determine which price to use
        if ($pPricingConflictActionReVal == PCA_USE_OWN) {
            // let posting assign the pricing, except if redirected from manual capture screen for authorisation
            if ($oh["data_source"] == DS_CAPTURE) {
                $postingOrderDetailTO->priceOverrideValue = trim($ohd["nett_price"]); // all other values e.g: vat to be calculated as per m/f settings
            }
        } elseif ($pPricingConflictActionReVal == PCA_USE_VENDOR) {
            $postingOrderDetailTO->discountReference = trim($ohd["discount_reference"]);
            $postingOrderDetailTO->priceOverrideUseSuppliedVals = true;
            if ($oh["skip_computation_check"] == "Y") $postingOrderTO->skipInvoiceComputationCheck = "Y"; // this happens at a global level and only needs 1 detail row to be set
            $postingOrderDetailTO->listPrice = trim($ohd["list_price"]);
            $postingOrderDetailTO->discountValue = trim($ohd["discount_value"]);
            $postingOrderDetailTO->priceOverrideValue = trim($ohd["nett_price"]);
            $postingOrderDetailTO->nettPrice = trim($ohd["nett_price"]);
            $postingOrderDetailTO->extPrice = trim($ohd["ext_price"]);
            // $postingOrderDetailTO->vatRate=trim($ohd["vat_rate"]); vatRate is not a TO field
            $postingOrderDetailTO->vatAmount = trim($ohd["vat_amount"]);
            $postingOrderDetailTO->totPrice = trim($ohd["total_price"]);

            // Ensure the EDI VAT is used
            $postingOrderDetailTO->productVatRate = trim($ohd["vat_rate"]);
            $postingOrderDetailTO->storeNoVat = (($postingOrderDetailTO->productVatRate > 0) ? 0 : 1);
        } elseif ($pPricingConflictActionReVal == PCA_STOP) {
            if ($hasPriceDifference) {
                $dtlStatus[] = "9.1";
            }
            // let posting assign the pricing as RT and Vendor prices are same
        } else {
            // should never get here
            echo "should never get here";
            $dtlStatus[] = "99";
        }
    }

    private function postOrder(&$postingOrderTO, $ohUId)
    {
        $pTO = $this->postTransactionDAO->postOrder($postingOrderTO);
        if ($pTO->isError()) {
            return $pTO;
        }

        // Buyer Goods Inward need to find the principal's order and update it.
        if ($postingOrderTO->documentType == DT_BUYER_GOODS_INWARD) {
            $pTO = $this->postDocumentUpdate($postingOrderTO);
        }

        return $pTO;
    }

    private function postDocumentUpdate($postingOrderTO)
    {
        $docTO = (new PostingDocumentUpdateTO)
            ->setPrincipalUId($postingOrderTO->principalUId)
            ->setUpdateTypeUid(UPDATE_DOCUMENT_TYPE_BUYER_POD)
            ->setCreatedDatetime(CommonUtils::getGMTime(0))
            ->setSourceDocumentMasterUid($postingOrderTO->dMUId)
            ->setDocumentTypeUId($postingOrderTO->documentType)
            ->setMergeDate(date('Y-m-d'))
            ->setMergeTime(date('H:i:s'))
            ->setSkipDepotUpdate("Y") // update the psm depot uid or not
            ->setDocumentNumber($postingOrderTO->documentNumber)
            ->setDocumentSourceOptional("N")
            ->setReference($postingOrderTO->orderNumber)
            ->setDepotUId(VAL_UNKNOWN_DEPOT);

        foreach ($postingOrderTO->detailArr as $dtl) {
            $detailTO = (new PostingDocumentUpdateDetailTO)
                ->setPageNo($dtl->clientPageNo)
                ->setLineNo($dtl->clientLineNo)
                ->setProductCode($dtl->productCode)
                ->setProductUId($dtl->productUId)
                ->setOrderedQty($dtl->quantity)     // do not use amended_qty. also does not get used, but we can set it
                ->setDocumentQty($dtl->quantity)
                ->setDeliveredQty($dtl->quantity);  // for the time being we ignore rejected_quantity as PnP only use Accepted Quantity so u can deduce

            $docTO->addDetailArr($detailTO);
        }

        return $this->postDocumentUpdateDAO->postDocumentUpdate($docTO);
    }

    // called from any screens that need to pre-validate a document only

    public function getOHDocument($ohUId)
    {
        $this->rs = [];
        $this->rs = $this->importDAO->getOrdersHoldingForProcessing($ohUId); // gets full entity set, using orders_holding_uid as key

        if (!count($this->rs->headerArr)) {
            return [false, ["No Document found"]];
        }

        $validateResp = new ErrorTO();
        foreach ($this->rs->headerArr as $r) {
            $validateResp = $this->validateDocument($r);

            // ensure Success is written as Success is only updated in postOrder which we do not call
            if ($validateResp->isSuccess()) {
                /* PostingOrderTO */
                // $validateResp->result

                $eTO = $this->postImportDAO->setOrdersHoldingProformaStatus($validateResp->object->principalUId, $ohUId, FLAG_ERRORTO_SUCCESS, "Successful");
                if ($eTO->isError()) {
                    BroadcastingUtils::sendAlertEmail("Error in ordersHoldingProcessing", $eTO->description, "Y", $quietMode = false);
                }
                $this->dbConn->dbinsQuery("commit");
            }

            break;
        }

        return [$validateResp->isSuccess(), $validateResp->object];
    }
}


$runMe = ((isset($_GET["RUNME"]) && $_GET["RUNME"] == "Y") ? true : false); // if called directly from url
//direct run!
if ($runMe) {

    ini_set('memory_limit', '512M');
    error_reporting(-1);
    ini_set('display_errors', 1);

    // temporarily fudge the session for validation purposes
    if (!isset($_SESSION)) session_start();
    $_SESSION['user_id'] = "000";

    set_time_limit(15 * 60); // 15 mins
    $dbConn = new dbConnect();
    $dbConn->dbConnection();

    $OHD = new OrdersHoldingDocument($dbConn);
    $OHD->processDocuments();

    //---------------------------------------------------
    //  temporary custom updates: start
    //---------------------------------------------------
    $customUpdatesStart = microtime(true);
    $BbqUpdateDAO = new BbqUpdateDAO($dbConn);

    //HASTY-TASTY FROZEN FOODS divides quantities by a factor of "4" for all depots besides (TK Logistics and Warehousing -- 202)
    // the update: floor(dd.ordered_qty / 4)
    $gBBQ = $BbqUpdateDAO->getHastyPNPDCOrders();
    if (count($gBBQ) > 0) {
        $BbqUpdateDAO->updateHastyPNPDCOrder($gBBQ);
        echo count($gBBQ) . " Hasty Tasty Orders updated\n";
    } else {
        echo "No Hasty Tasty orders to Update\n";
    }

    //TODO: this should be a flag at the principal level, when triggered we update status to deleted in orders holding.
    //TODO: see line 1068 : use select pp.principal_uid, pp.order_number_unique_ws from principal_preference pp where pp.order_number_unique_ws = 'Y'
    //marks all orders holding that have triggered a "unique" as Deleted.
    $bbqUpdate = $BbqUpdateDAO->clearNotUniquePO();
    if (count($bbqUpdate) > 0) {
        echo count($bbqUpdate) . " Unique PO's Cleared\n";
    } else {
        echo "No Unique PO's Cleared\n";
    }

    //RICH: START
    // TODO: changes required on the omni extract.
    // updates document status from UNACCEPTED to ACCEPTED on SALES_ORDER documents for "Rich Products Corporation Africa (354)"
    $bbqUpdate = $BbqUpdateDAO->checkOrderStatus();
    if (count($bbqUpdate) > 0) {
        echo count($bbqUpdate) . " Richs Orders updated\n";
    } else {
        echo "No Richs Orders updated\n";
    }

    // TODO: replace with a default status
    // updates document status from UNACCEPTED to INVOICED on ORDINV documents for "Rich Products Corporation Africa (354)"
    $bbqIUpdate = $BbqUpdateDAO->checkInvoiceStatus();
    if (count($bbqIUpdate) > 0) {
        echo count($bbqUpdate) . " Richs Invoices updated\n";
    } else {
        echo "No Richs Invoices updated\n";
    }

    //TODO: confirm is this is still required?
    // Set the alt_principal_chain_uid for "Rich Products Corporation Africa (354)"
    $bbqUpdate = $BbqUpdateDAO->cstoresAltChain();
    if (count($bbqUpdate) > 0) {
        echo count($bbqUpdate) . " Richs C-Stores chains correct\n";
    } else {
        echo "No Richs C-Stores chains corrected\n";
    }
    //RICH: END

    // Creates document_credit_source links for "SAMS Tissue (390)"
    $srcDUpdate = $BbqUpdateDAO->createCredNoteUidLookup();
    if (count($srcDUpdate) > 0) {
        echo count($srcDUpdate) . " Source Doc Uid`s Updated\n";
    } else {
        echo "No Source Doc Uid`s Updated\n";
    }

    //TODO: add change to orders_holding, all principals on document type 23 -- do this update...
    // Update AOD's
    $BbqUpdateDAO->UpdateAODNO();
    echo "AOD s Checked\n";

    // Update Check Decimal Values
    $BbqUpdateDAO->updateDeciTotals();
    echo "Decimal Totals Checked\n";

    //TODO: we need to change this operationally to work via a flag.
    // Update Wilmar Excella line
    echo "Checking Wilmar PnP EXCELLA 750 orders\n";
    $BbqUpdateDAO->zeroOrderLine();

    // Update IDO Special Fields
    echo "Checking IDO Special Fields\n";
    $gBBQ = $BbqUpdateDAO->idoSpecialFields();

    // Update Honey field walk-in stock
    $mDtk = (new MaintenanceDAO($dbConn))->copyHoneyfieldsWalkingstock();

    echo "Custom updates Took: " . round((microtime(true) - $customUpdatesStart), 6) . "\n";
    echo "Copy HoneyFields Walking Stock\n";

    //end of custom updates.

    //---------------------------------------------------
    echo "ordersHoldingProcessing Completed\n";
    echo "[***EOS***]";
}
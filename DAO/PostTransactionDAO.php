<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'properties/dbSettings.inc');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . "libs/CommonUtils.php");
include_once($ROOT . $PHPFOLDER . "libs/GUICommonUtils.php");
include_once($ROOT . $PHPFOLDER . "libs/ValidationCommonUtils.php");
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT . $PHPFOLDER . "DAO/AdministrationDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/CommonDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/StoreDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/DepotDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/ProductDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/BIDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/ImportDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/SequenceDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/MiscellaneousDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PostMiscellaneousDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PostStockDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/SpecialValidationDAO.php");
include_once($ROOT . $PHPFOLDER . "libs/BroadcastingUtils.php");

/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostTransactionDAO
{
    public $errorTO;
    private $dbConn;
    // DAOs registered here for reusage in multiple calls
    private $administrationDAO;
    private $commonDAO;
    private $principalDAO;
    private $storeDAO;
    private $depotDAO;
    private $productDAO;
    private $importDAO;
    private $sequenceDAO;
    private $principalPreferenceArr; // is only set if postOrderValidation was called
    private $biDAO;
    private $miscDAO;
    private $postMiscDAO;
    private $postStockDAO;
    private $specialValidationDAO;
    private $depotArr;
    private $pDT_ProformaPricing = array();


    function __construct($dbConn)
    {
        global $administrationDAO, $commonDAO, $principalDAO, $storeDAO, $productDAO, $importDAO, $sequenceDAO, $miscDAO, $miscellaneousDAO,
               $postMiscDAO, $postMiscellaneousDAO, $postStockDAO, $specialValidationDAO, $depotDAO; // these get values from outside the class, does NOT use private var of same name

        $this->dbConn = $dbConn;
        $this->errorTO = new ErrorTO;
        // re-use above globals what we can from calling program to improve speed
        $this->administrationDAO = $administrationDAO;
        $this->commonDAO = $commonDAO;

        $this->principalDAO = $principalDAO;
        if (!isset($this->principalDAO)) $this->principalDAO = new PrincipalDAO($dbConn);

        $this->storeDAO = $storeDAO;
        $this->productDAO = $productDAO;
        $this->importDAO = $importDAO;
        $this->depotDAO = $depotDAO;
        $this->sequenceDAO = $sequenceDAO;
        $this->postStockDAO = $postStockDAO;
        $this->specialValidationDAO = $specialValidationDAO;
        if (isset($miscellaneousDAO)) $this->miscDAO = $miscellaneousDAO; else $this->miscDAO = $miscDAO; // some scrips use misc, other miscellaneousDAO
        if (isset($postMiscellaneousDAO)) $this->postMiscDAO = $postMiscellaneousDAO; else $this->postMiscDAO = $postMiscDAO; // some scrips use misc, other miscellaneousDAO

        if (!isset($this->depotDAO)) $depotDAO = $this->depotDAO = new DepotDAO($this->dbConn); else $depotDAO = $this->depotDAO;
        $this->depotArr = $this->depotDAO->getAllDepotsGlobally();

        $mfPDocType = $this->principalDAO->getAllPrincipalDocumentTypes_ProformaPricing(); // document types overrides if any
        foreach ($mfPDocType as $r) {
            $this->pDT_ProformaPricing[$r["principal_uid"]][] = $r["document_type_uid"];
        }
    }

    /*
     *
     *  User Roles
     *
     */

    public function postOrderValidation(&$postingOrderTO, &$documentTypeDescription)
    {
        global $ROOT, $PHPFOLDER;

        include_once($ROOT . $PHPFOLDER . "TO/PostingOrderDocumentPricingTO.php");
        include_once($ROOT . $PHPFOLDER . "DAO/StockDAO.php");
        $stockDAO = new StockDAO($this->dbConn);

        // if not set from constructor globals, then initialise here
        if (!isset($this->administrationDAO)) $administrationDAO = $this->administrationDAO = new AdministrationDAO($this->dbConn); else $administrationDAO = $this->administrationDAO;
        if (!isset($this->commonDAO)) $commonDAO = $this->commonDAO = new CommonDAO($this->dbConn); else $commonDAO = $this->commonDAO;
        if (!isset($this->principalDAO)) $principalDAO = $this->principalDAO = new PrincipalDAO($this->dbConn); else $principalDAO = $this->principalDAO;
        if (!isset($this->storeDAO)) $storeDAO = $this->storeDAO = new StoreDAO($this->dbConn); else $storeDAO = $this->storeDAO;
        if (!isset($this->productDAO)) $productDAO = $this->productDAO = new ProductDAO($this->dbConn); else $productDAO = $this->productDAO;
        if (!isset($this->importDAO)) $importDAO = $this->importDAO = new ImportDAO($this->dbConn); else $importDAO = $this->importDAO;
        if (!isset($this->depotDAO)) $depotDAO = $this->depotDAO = new DepotDAO($this->dbConn); else $depotDAO = $this->depotDAO;
        if (!isset($this->sequenceDAO)) $sequenceDAO = $this->sequenceDAO = new SequenceDAO(null); else $sequenceDAO = $this->sequenceDAO;
        if (!isset($this->biDAO)) $biDAO = $this->biDAO = new BIDAO($this->dbConn); else $biDAO = $this->biDAO;
        if (!isset($this->miscDAO)) $miscDAO = $this->miscDAO = new MiscellaneousDAO($this->dbConn); else $miscDAO = $this->miscDAO;
        if (!isset($this->postMiscDAO)) $postMiscDAO = $this->postMiscDAO = new PostMiscellaneousDAO($this->dbConn); else $postMiscDAO = $this->postMiscDAO;
        if (!isset($this->specialValidationDAO)) $specialValidationDAO = $this->specialValidationDAO = new SpecialValidationDAO($this->dbConn); else $specialValidationDAO = $this->specialValidationDAO;


        // get user. don't pass it because this is more secure.
        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $principalId = ($userId == SESSION_ADMIN_USERID) ? $postingOrderTO->principalUId : $_SESSION['principal_id']; // remember that if principalAliasId is set, the $postingOrderTO->principalUId would have contained this value
        $principalAliasId = (((!isset($_SESSION['principal_alias_id'])) || ($_SESSION['principal_alias_id'] == "")) ? $principalId : $_SESSION['principal_alias_id']);


        // quotations can be amended\


        if (in_array($postingOrderTO->documentType, array(DT_QUOTATION, DT_PURCHASE_ORDER))) {

            if (!empty($postingOrderTO->dMUId)) {

                $hasRole = $administrationDAO->hasRole($userId, $principalId, ROLE_MANAGE_QUOTATION);
                if (!$hasRole) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "User does not have permission to manage quotations";
                    return false;
                }

                // we should ideally check the store, depot, chain permissions here - not implemented at moment
                $sql = "SELECT a.version, b.document_status_uid
      	             FROM   document_master a,
      	                    document_header b
      	             WHERE  a.uid = b.document_master_uid
      	             AND    a.uid = '{$postingOrderTO->dMUId}'
      	             AND    a.document_type_uid in (" . DT_QUOTATION . "," . DT_PURCHASE_ORDER . ")
      	             AND    a.principal_uid = {$principalId}";

                $rs = $this->dbConn->dbGetAll($sql);
                if (count($rs) == 0) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Existing Quotation not found";
                    return false;
                }

                if (!in_array($rs[0]["document_status_uid"], array(DST_ACCEPTED, DST_UNACCEPTED, DST_IN_PROGRESS))) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Only quotations in unaccepted or accepted status can be amended";
                    return false;
                }

                $postingOrderTO->documentMasterVersion = intval($rs[0]["version"]) + 1;

                $sql = "UPDATE document_master a,
      	                    document_header b
      	             SET    a.document_number = concat(a.document_number,'-',a.version),
      	                    b.document_status_uid = '" . DST_CANCELLED . "'
      	             WHERE  a.uid = b.document_master_uid
      	             AND    a.uid = {$postingOrderTO->dMUId}
      	             AND    a.document_type_uid in (" . DT_QUOTATION . "," . DT_PURCHASE_ORDER . ")
      	             AND    b.document_status_uid in (" . DST_UNACCEPTED . "," . DST_ACCEPTED . "," . DST_IN_PROGRESS . ")
      	             AND    a.principal_uid = {$principalId}";


                $this->errorTO = $this->dbConn->processPosting($sql, $postingOrderTO->orderNumber);
                if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                    return $this->errorTO;
                }

            } else {

                $postingOrderTO->documentMasterVersion = 1;

            }

        }


        // check same principals
        if ($postingOrderTO->principalUId != $principalAliasId) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Principal Passed differs from registered session principal.";
            return false;
        };

        if (!ValidationCommonUtils::checkPostingType($postingOrderTO->DMLType)) return false;

        // check if principal is valid
        if ($userId != SESSION_ADMIN_USERID) {
            $mfP = $principalDAO->getUserPrincipalItem($principalAliasId, $userId);
        } else {
            $mfP = $principalDAO->getPrincipalItem($principalAliasId);
        }
        if (sizeof($mfP) == 0) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "User does not have access to this principal, or principal not found.";
            return false;
        };

        // Do not allow any documents to be processed if the principal is suspended.
        if ($mfP[0]["status"] != FLAG_STATUS_ACTIVE) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Principal is not active - No documents may be processed";
            return false;
        }

        // limit in orders is 50 but in DM it is 20
        if (strlen(trim($postingOrderTO->orderNumber)) > 20) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "The reference can only be a maximum of 20 characters  - " . $principalAliasId;
            return false;
        }

        // check if store is valid
        if ($userId != SESSION_ADMIN_USERID) {
            $mfPS = $storeDAO->getUserPrincipalStoreItem($userId, $postingOrderTO->storeChainUId);
        } else {
            $mfPS = $storeDAO->getPrincipalStoreItem($postingOrderTO->storeChainUId);
        }

// print_r($mfPS);
        if (sizeof($mfPS) == 0) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "User does not have access to this store, or store not found  - " . $principalAliasId;
            return false;
        };
        if ($mfPS[0]['principal_uid'] != $principalAliasId) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "This Store is not registered to the Principal.";
            return false;
        };
        if ($mfPS[0]['on_hold'] != 0) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "This Store is ON HOLD. Orders may not be processed to it.";
            return false;
        };

        if ($mfPS[0]['status'] != FLAG_STATUS_ACTIVE) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "This Store is not ACTIVE. Orders may not be processed to it.  - " . $mfPS[0]['status'] . " s- " . $mfPS[0]['store_name'] . " - " . $principalAliasId;
            return false;
        };
        if ($mfPS[0]['principal_chain_uid'] == "") {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "The selected store does not have a Primary Chain assigned to it.  - " . $principalAliasId;
            return false;
        };
        if ($mfPS[0]['chain_status'] != FLAG_STATUS_ACTIVE) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "The Chain that this Store is linked to is not ACTIVE. Orders may not be processed to it.  - " . $principalAliasId;
            return false;
        };
        // only do the check for orders, so as to allow the WMS users to create credit notes / dirty POD's for whatever store
        // we could also check the user type who is doing the change, but we will see how this works over time.
        if (in_array($postingOrderTO->documentType, array(DT_ORDINV, DT_ORDINV_ZERO_PRICE, DT_DELIVERYNOTE))) {
            if (($mfPS[0]['owned_by'] != "") && ($mfPS[0]['owned_by'] != $postingOrderTO->vendorUId) && ($mfPS[0]['owned_by'] != V_UNKNOWN_VENDOR)) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "The store you have selected is not owned by the principal/vendor. Only stores owned by principal/vendor can have documents created for it.";
                return false;
            };
        }
        if (($mfPS[0]['depot_uid'] == "") || ($mfPS[0]['depot_uid'] == "0")) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "The store you have selected does not have a depot assigned to it. Please modify the store and assign a depot before orders can be captured for it ($principalAliasId)";
            return false;
        };
        $postingOrderTO->processedDepotUId = (($postingOrderTO->processedDepotUId == "") ? $mfPS[0]["depot_uid"] : $postingOrderTO->processedDepotUId); // only use this depot if not force overridden
        $postingOrderTO->WMS = $mfPS[0]["wms"];
        if (empty($postingOrderTO->orderStartStatus)) $postingOrderTO->orderStartStatus = $mfPS[0]["order_start_status"];
        if (trim(strval($postingOrderTO->storeNoVat)) == "") $postingOrderTO->storeNoVat = $mfPS[0]['no_vat']; // set this to save bulk discounts having to look it up

        // check (ALL ! as defined, not as passed) store special fields are validated. NB : some fields requred for inserting eg.editable are not filled in here
        if (isset($mfPS[0])) {
            if (in_array($postingOrderTO->documentType, array(DT_ORDINV, DT_ORDINV_ZERO_PRICE, DT_DELIVERYNOTE))) {
                $mfSF = $miscDAO->getPrincipalSpecialFieldValues($principalAliasId, $postingOrderTO->storeChainUId, CT_STORE_SHORTCODE); // remember this returns all sf loaded, including newly configured ones without values yet
                foreach ($mfSF as $smpfLine) {
                    $resultOK = $postMiscDAO->postSpecialFieldValidation($smpfLine["value"], $smpfLine["sf_uid"], "VALIDATE", $depotUId = $postingOrderTO->processedDepotUId); // NB!! At the moment stores dont handle multiple sf values
                    if (!($resultOK === true)) {
                        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                        $this->errorTO->description = "Store Special Fields failed validation - Could not create order. " . $resultOK->description;
                        return false;
                    }
                }
            }
        }
        $postingOrderTO->storeNoVATAuthorisedBy = $mfPS[0]["vat_excl_authorised_by"];


        // from the above, determine the document preferences
        $this->principalPreferenceArr = $mfPPref = $principalDAO->getPrincipalPreferences($principalAliasId); // always returns atleast 1 row


        // check unique order number if required
        if ((trim($postingOrderTO->orderNumber) != "") && ($postingOrderTO->skipUniqueOrderNoFlag != "Y")) {

            $errMsg = "";

            $mfNDUP = (($userId != SESSION_ADMIN_USERID) ? $biDAO->getNotificationDocketCaptureDuplicationForUser($userId, $principalAliasId) : false);
            $isDuplicate = false;
            $nIsWarningDup = false;
            $nIsErrorDup = false;
            $isCriticalDup = false;
            if (($mfNDUP !== false) && (sizeof($mfNDUP) > 0)) {
                if ($mfNDUP["value"] == "WARNING") $nIsWarningDup = true;
                else if ($mfNDUP["value"] == "ERROR") $nIsErrorDup = true;
            }

            if (!in_array($postingOrderTO->documentType, array(DT_QUOTATION, DT_PURCHASE_ORDER)) &&
                (
                    (($mfPPref[0]["order_number_unique"] == "Y") && ($postingOrderTO->dataSource != DS_WS)) ||
                    (($mfPPref[0]["order_number_unique_ws"] == "Y") && ($postingOrderTO->dataSource == DS_WS))
                )
            ) {

                $mfDUPO = $importDAO->getOrdersByON($principalAliasId, $postingOrderTO->orderNumber, $postingOrderTO->processedDepotUId, $postingOrderTO->documentType, $postingOrderTO->storeChainUId);
                if (sizeof($mfDUPO) > 0) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Principal " . $principalAliasId . " has been configured for unique order references. Order Reference is not unique in Orders.  - " . $principalAliasId;
                    $isDuplicate = $isCriticalDup = true;
                    $errMsg = $this->errorTO->description;
                }
                $mfDUPD = $importDAO->getDocumentsByON($principalAliasId, $postingOrderTO->orderNumber, $postingOrderTO->processedDepotUId, ($postingOrderTO->documentType == DT_ORDINV_ZERO_PRICE) ? DT_ORDINV : $postingOrderTO->documentType, $postingOrderTO->storeChainUId); // clipper converts it
                if (sizeof($mfDUPD) > 0) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Principal " . $principalAliasId . " has been configured for unique order references. Order Reference is not unique in Tracking Transaction.  - " . $principalAliasId;
                    $isDuplicate = $isCriticalDup = true;
                    $errMsg = $this->errorTO->description;
                }

            } // else check if it was a double submit from capture screen
            else if (
                (
                    (($mfPPref[0]["order_number_unique"] == "N") && ($postingOrderTO->dataSource != DS_WS)) ||
                    (($mfPPref[0]["order_number_unique_ws"] == "N") && ($postingOrderTO->dataSource == DS_WS))
                ) && ($userId != SESSION_ADMIN_USERID)
            ) {

                // if there is a specific notification set, use that, or just use the general one, should only be one output type which is "SCREEN"
                // 1. Must be atleast > 2 mins because of timeout on capture side being 2 mins
                // 2. Check order is unique. Dont join on order_sequence_no so that it traps possible double submits - remember that submitting program sends the detail one by one.
                if (sizeof($mfNDUP) > 0) {
                    // this combines the critical error double submit check, so need to check its results during RS iteration below
                    $sql = "select order_number, order_sequence_no, if (a.capturedate < DATE_SUB(now(),INTERVAL 2*60 SECOND),'<','>') date_group
  						from orders a
  						where principal_uid = " . $postingOrderTO->principalUId . "
  						and   storechain_uid = " . $postingOrderTO->storeChainUId . "
  						and   (a.order_number = '{$postingOrderTO->orderNumber}' and a.order_number is not null and a.order_number!='')
  						and   a.document_type = {$postingOrderTO->documentType}
  						and   (
  								a.capturedate < DATE_SUB(now(),INTERVAL 2*60 SECOND) or
  								-- if any are in here, they cannot be bypassed by parameter notification
  							    (
  										  a.capturedate >= DATE_SUB(now(),INTERVAL 5*60 SECOND)
  									and   a.captureuser_uid = '{$userId}'
  								 )
  							  )";
                } else {
                    // use generic : user cannot submit same product within last 5 mins, for themselves (prevents double submits)
                    $sql = "select order_number, order_sequence_no, '>' date_group
  						from orders a
  						where principal_uid = " . $postingOrderTO->principalUId . "
  						and   storechain_uid = " . $postingOrderTO->storeChainUId . "
  						and   (a.order_number = '{$postingOrderTO->orderNumber}' and a.order_number is not null and a.order_number!='')
  						and   a.document_type = {$postingOrderTO->documentType}
  						and   a.capturedate >= DATE_SUB(now(),INTERVAL 5*60 SECOND)
  						and   a.captureuser_uid = '{$userId}'";
                }

                $this->dbConn->dbQuery("SET time_zone='+0:00'");
                $this->dbConn->dbinsQuery($sql);
                if (!$this->dbConn->dbQueryResult) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Error occurred in duplicate check SQL";
                    return false;
                }

                if ($this->dbConn->dbQueryResultRows > 0) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Order Number is not unique.";
                    $isDuplicate = true;
                    $errMsg = $this->errorTO->description;
                    while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                        // check critical duplicate double submit for any in loop
                        if ($row["date_group"] == ">") {
                            $isCriticalDup = true; // this also doubles up as stopping if no notifications are specified
                            $errMsg = "This Order has already been captured for this reference for this principal-store within the last 5 minutes.";
                        }
                    }
                }

            } // end screen check double submit

            if ($isDuplicate) {
                // if the source is CAPTURE, then apply the notification rule
                if ($userId != SESSION_ADMIN_USERID) {

                    // the nIsErrorDup must come before the isCritical so that the message is correct, as nIsErrorDup is also $isCriticalDup
                    if ($nIsErrorDup) {
                        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                        $this->errorTO->description = "This Order has already been captured for this reference. Duplication check has been enforced as per Notifications Setting.";
                        return false;
                    } else if ($nIsWarningDup) {
                        // just ask user if not already confirmed
                        if ($postingOrderTO->confirmOption != "Y") {
                            $this->errorTO->type = FLAG_ERRORTO_WARNING;
                            $this->errorTO->description = "This Order has already been captured for this reference. Are you sure you would like to continue ?";
                            return false;
                        }
                    } else if ($isCriticalDup) {
                        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                        $this->errorTO->description = $errMsg;
                        return false;
                    }

                } else {
                    return false;
                }
            }

        } // end blank order number and/or skip check


        // check if Doc Type is valid
        if ($userId != SESSION_ADMIN_USERID) {
            $mfDT = $commonDAO->getUserDocumentTypesAllowedItem($userId, $principalAliasId, $postingOrderTO->documentType);
        } else {
            $mfDT = $commonDAO->getDocumentTypesAllowedItem($principalAliasId, $postingOrderTO->documentType);
        }
        if (sizeof($mfDT) == 0) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "User does not have access to this document type, or document type not found.";
            return false;
        } else $documentTypeDescription = $mfDT[0]["description"];

        // check if STATUS is valid
        if ($postingOrderTO->deleted == "") $postingOrderTO->deleted = 0;
        if (!ValidationCommonUtils::checkFieldBooleanSimple($postingOrderTO->deleted)) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Invalid Deleted Status.";
            return false;
        };

        // del instr length
        if (strlen($postingOrderTO->deliveryInstructions) > 250) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Delivery Instructions can only be a maximum of 100 chars";
            return false;
        };
        // customer ref length
        if (strlen($postingOrderTO->orderNumber) > 255) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Customer Reference must be a maximum of 255 chars";
            return false;
        };

        // check document date
        if (preg_match(GUI_PHP_DATE_VALIDATION, $postingOrderTO->documentDate, $parts)) {
            if (!checkdate($parts[2], $parts[3], $parts[1])) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Invalid Document Date format.";
                return false;
            }
        } else {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Invalid Document Date format.";
            return false;
        }
        if (in_array($userId, array(11, 1376, 1387, 2103, 1248, 1667))) {
            $daysInt = 180;
        } else {
            $daysInt = 30;
        }

        if (strtotime($postingOrderTO->documentDate) < strtotime("-" . $daysInt . " days")) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Document Date cannot be older than " . $daysInt . " days.  - " . $principalAliasId;
            return false;
        }


        //set the stock available flag for detail loop.
        $checkAvailableStock = false;
        if ($postingOrderTO->WMS == 'Y' && in_array($postingOrderTO->documentType, array(DT_ORDINV, DT_ORDINV_ZERO_PRICE, DT_DELIVERYNOTE, DT_DESTRUCTION_DISPOSAL))) {

            include_once($ROOT . $PHPFOLDER . "DAO/TransactionDAO.php");
            $transactionDAO = new TransactionDAO($this->dbConn);
            $flag = $transactionDAO->getOrderStockAvailableFlag($postingOrderTO->processedDepotUId, $principalAliasId);
            if (isset($flag[0]['check_flag']) && $flag[0]['check_flag'] == 'Y') {
                $checkAvailableStock = true;
            }
        }

        // check delivery date
        if ($postingOrderTO->deliveryDate != "") {
            if (preg_match(GUI_PHP_DATE_VALIDATION, $postingOrderTO->deliveryDate, $parts)) {
                if (!checkdate($parts[2], $parts[3], $parts[1])) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Invalid Delivery Date format.";
                    return false;
                }
                if (($userId != SESSION_ADMIN_USERID) && (strtotime($postingOrderTO->deliveryDate) < strtotime(CommonUtils::getUserDate(+1)))) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Delivery Date must be later than today.";
                    return false;
                }
            } else {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Invalid Delivery Date format.";
                return false;
            }
        }

        // check roles
        // check roles
        if ($postingOrderTO->documentType == DT_QUOTATION) {
            $hasRole = $administrationDAO->hasRole($userId, $postingOrderTO->principalUId, ROLE_QUOTATION_CAPTURE);
        } else if ($postingOrderTO->documentType == DT_PURCHASE_ORDER) {
            $hasRole = $administrationDAO->hasRole($userId, $postingOrderTO->principalUId, ROLE_PURCHASE_ORDER_CAPTURE);
        } else if ($postingOrderTO->documentType == DT_CREDITNOTE) {
            $hasRole = $administrationDAO->hasRole($userId, $postingOrderTO->principalUId, 104);
        } else if ($postingOrderTO->documentType == DT_SUPPLIER_INVOICE) {
            $hasRole = $administrationDAO->hasRole($userId, $postingOrderTO->principalUId, ROLE_SUPPLIER_INVOICE_CAPTURE);
        } else if ($postingOrderTO->documentType == DT_PAYMENTTO) {
            $hasRole = $administrationDAO->hasRole($userId, $postingOrderTO->principalUId, ROLE_PAYMENTTO_CAPTURE);
        } else {
            $hasRole = $administrationDAO->hasRole($userId, $postingOrderTO->principalUId, ROLE_ORDER_CAPTURE);
        }
        if (!$hasRole) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "You do not have permissions to CAPTURE this document type " . $postingOrderTO->documentType;
            return false;
        };

        // Check Product Details - the identifiers are used for the capture screen to highlight the row
        $documentTotal = 0;
        $tempDocumentTotal = 0;
        $i = 0;
        $lineNo = 0;
        $pageNo = 1;
        $pList = array();
        foreach ($postingOrderTO->detailArr as &$postingOrderDetailTO) {
            $lineNo++;
            // only allocate line numbering if not supplied
            if ($postingOrderDetailTO->lineNo == "") {
                if ($lineNo > 10) {
                    $lineNo = 1;
                    $pageNo++;
                }
                $postingOrderDetailTO->pageNo = $pageNo;
                $postingOrderDetailTO->lineNo = $lineNo;
            }

            $mfPP = $productDAO->getUserPrincipalProductItem($principalAliasId, $postingOrderDetailTO->productUId, $userId);
            if (sizeof($mfPP) == 0) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Principal Product not found.";
                return false;
            };
            if ($postingOrderDetailTO->quantity == "") {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Quantity Not Entered for Product " . $mfPP[0]["product_code"];
                $this->errorTO->identifier = "Q";
                $this->errorTO->identifier2 = $i;
                return false;
            }
            // Allow decimal Quantities

            $decQtyAllowed = $productDAO->getAllowDecimalFlag($postingOrderDetailTO->productUId);

            if ($decQtyAllowed['allow_decimal'] == 'Y') {
                $postingOrderDetailTO->quantity = $postingOrderDetailTO->quantity * 100;
            } elseif (abs(floor($postingOrderDetailTO->quantity)) != abs($postingOrderDetailTO->quantity)) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Quantities cannot have decimal places.";
                $this->errorTO->identifier = "Q";
                $this->errorTO->identifier2 = $i;
                return false;
            }
//        echo "<pre>";
//print_r($postingOrderTO);
//        echo "</pre>";
            // Additional Depot Settings not part of Store Fields resultset
            if ($this->depotArr[$postingOrderTO->processedDepotUId]["allow_dup_products"] == "N") {
                // check for dup products - this depot does not permit it
                if (isset($pList[$postingOrderDetailTO->productUId])) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Duplicate product not allowed for this depot for Product " . $mfPP[0]["product_code"];
                    $this->errorTO->identifier = "Q";
                    $this->errorTO->identifier2 = $i;
                    return false;
                }
                $pList[$postingOrderDetailTO->productUId] = $postingOrderDetailTO->productUId;
            }

            // necessary for bulk discounts, fill this in here to save processing time. Leaving it blank forces function to look it up again
            if (intval($mfPP[0]["items_per_case"]) > 1) {
                $postingOrderDetailTO->qtyConvertedToCases = ceil($postingOrderDetailTO->quantity / intval($mfPP[0]["items_per_case"]));
            } else {
                $postingOrderDetailTO->qtyConvertedToCases = $postingOrderDetailTO->quantity;
            }

            // carry through extra fields to save bulk discounts from having to look it up again.
            // also doubles as using the RT Vat settings where order is from EDI and Vat needs to use RT m/f
            if (strval(trim($postingOrderDetailTO->productVatRate)) == "") {
                $postingOrderDetailTO->vatRateWasEmpty = true;
                $postingOrderDetailTO->productVatRate = $mfPP[0]["vat_rate"]; // add a new attribute !
            } else {
                $postingOrderDetailTO->vatRateWasEmpty = false; // add a new attribute !
            }
            if (strval(trim($postingOrderDetailTO->majorCategory)) == "") $postingOrderDetailTO->majorCategory = ((strval(trim($mfPP[0]["major_category"])) == "") ? false : $mfPP[0]["major_category"]);

            // check if pallet calculation correct
            if (in_array($postingOrderTO->documentType, array(DT_STOCKTRANSFER, DT_DELIVERYNOTE, DT_ORDINV, DT_ORDINV_ZERO_PRICE, DT_WALKIN_INVOICE))) {
                if ($mfPP[0]["enforce_pallet_consignment"] == "Y") {
                    if (($mfPP[0]["units_per_pallet"] == "0") || ($mfPP[0]["units_per_pallet"] == "")) {
                        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                        $this->errorTO->description = "Product " . $mfPP[0]["product_code"] . " requires a unit pallet specification but has not been set in masterfiles.";
                        $this->errorTO->identifier = "P";
                        $this->errorTO->identifier2 = $i;
                        return false;
                    }
                    $pallets = $postingOrderDetailTO->quantity / $mfPP[0]["units_per_pallet"];
                    if (ceil($pallets) != ($pallets)) {
                        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                        $this->errorTO->description = "Product " . $mfPP[0]["product_code"] . " requires a whole pallet load or whole multiple thereof. A pallet load for this product is specified at " . $mfPP[0]["units_per_pallet"] . " unit(s).";
                        $this->errorTO->identifier = "P";
                        $this->errorTO->identifier2 = $i;
                        return false;
                    }
                    if ($pallets != $postingOrderDetailTO->pallets) {
                        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                        $this->errorTO->description = "Incorrect pallet calculation supplied by posting. Please contact Retail Trading Management.";
                        $this->errorTO->identifier = "P";
                        $this->errorTO->identifier2 = $i;
                        return false;
                    }
                }
            } else $postingOrderDetailTO->pallets = "";

            if ($postingOrderTO->documentType == DT_CANCELLEDNOTE) {
                // overwrite quatities with zero - needed for onlineFileProcessing otherwise those trans get rejected
                $postingOrderDetailTO->quantity = "0";
            } else if ($postingOrderTO->documentType == DT_ORDINV_ZERO_PRICE) {
                // set pricing to Zero.... wo file outouts this DT_ as DT_ORDINV
                $postingOrderDetailTO->chosenPricingUId = "0";
                $postingOrderDetailTO->priceType = GUICommonUtils::translateDealType(VAL_DEALTYPE_NETT_PRICE);
                $postingOrderDetailTO->dealTypeUId = VAL_DEALTYPE_NETT_PRICE;
                $postingOrderDetailTO->listPrice = "0.00";
                $postingOrderDetailTO->dealTypeValue = "0.00";
                $postingOrderDetailTO->discountValue = "0.00";
                $postingOrderDetailTO->nettPrice = "0.00";
                $postingOrderDetailTO->priceOverride = "N";
                $postingOrderDetailTO->discountReference = "";
            }

            if ($mfPP[0]['status'] == FLAG_STATUS_DELETED) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "This Product (" . $mfPP[0]['product_description'] . ") is Deleted. Orders may not be processed to it.";
                $this->errorTO->identifier = "P";
                $this->errorTO->identifier2 = $i;
                return false;
            } else if (($mfPP[0]['status'] == FLAG_STATUS_SUSPENDED) && (in_array($postingOrderTO->documentType, array(DT_DELIVERYNOTE, DT_ORDINV, DT_ORDINV_ZERO_PRICE)))) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "This Product (" . $mfPP[0]['product_description'] . ") is Suspended. Orders/Delivery Notes may not be processed to it.";
                return false;
            };

            $postingOrderDetailTO->productNoVATAuthorisedBy = $mfPP[0]["vat_excl_authorised_by"];
            $postingOrderDetailTO->productCode = $mfPP[0]["product_code"];
            $postingOrderDetailTO->productDescription = $mfPP[0]["product_description"];
            $postingOrderDetailTO->mfProductVatRate = $mfPP[0]["vat_rate"];
            $postingOrderDetailTO->itemspercase = $mfPP[0]["items_per_case"];


            if ($checkAvailableStock) {
                $mfStock = $stockDAO->getPrincipalProductStock($postingOrderTO->processedDepotUId, $principalAliasId, $postingOrderDetailTO->productUId);
                $availableStock = (isset($mfStock[0]['available'])) ? $mfStock[0]['available'] : 0;
                //available

                if ($postingOrderDetailTO->quantity > $availableStock) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Product <strong>" . $postingOrderDetailTO->productCode . '</strong> only has ' . $availableStock . ' available stock!';
                    return false;
                }
            }

            $i++;

        } // end product detail checks
        unset($postingOrderDetailTO);


        if (in_array($postingOrderTO->documentType, array(DT_ORDINV, DT_WALKIN_INVOICE))) {

            // returns order TOs with all pricing applied. documentType is checked inside as well.
            $this->errorTO = $productDAO->getFinalInvoicePricing($postingOrderTO, $documentTotal, $userId, $principalAliasId);
            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                return false;
            }

            // Some EDI and Checkers WS orders need to have just the VAT applied if we are using CHeckers pricing (VENDOR)
            if (($principalAliasId == RETAIL_TR) && ($postingOrderTO->turnover == 'Y')) {
                foreach ($postingOrderTO->detailArr as &$postingOrderDetailTO) {

                    list($calculatedVATRate, $msg) = $this->productDAO->getCalculatedVATRate($principalAliasId, $postingOrderTO->storeChainUId, $postingOrderDetailTO->productUId);
                    if ($calculatedVATRate === false) {
                        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                        $this->errorTO->description = "Could not retrieve calculated vat rate : " . $msg;
                        return false;
                    }
                    $turnoverPrice = $postingOrderDetailTO->listPrice;

                    $turnoverPriceArr = $this->productDAO->getDiscountValue($principalAliasId,
                        $postingOrderTO->storeChainUId,
                        $postingOrderDetailTO->productUId);
                    $postingOrderDetailTO->listPrice = $postingOrderDetailTO->extPrice * $postingOrderDetailTO->discountValue / 100;
                    $postingOrderDetailTO->discountValue = $turnoverPriceArr[0]["discount_value"];
                    $postingOrderDetailTO->nettPrice = $postingOrderDetailTO->extPrice * $postingOrderDetailTO->discountValue / 100;
                    $postingOrderDetailTO->vatAmount = round($postingOrderDetailTO->nettPrice * ($calculatedVATRate / 100), 2);
                    $postingOrderDetailTO->totPrice = ($postingOrderDetailTO->extPrice * $postingOrderDetailTO->discountValue / 100) + $postingOrderDetailTO->vatAmount;
                }
            } else {
                foreach ($postingOrderTO->detailArr as &$postingOrderDetailTO) {
                    if (
                        ($postingOrderDetailTO->priceOverrideUseSuppliedVals) &&
                        ($postingOrderDetailTO->vatRateWasEmpty === true)
                    ) {

                        list($calculatedVATRate, $msg) = $this->productDAO->getCalculatedVATRate($principalAliasId, $postingOrderTO->storeChainUId, $postingOrderDetailTO->productUId);
                        if ($calculatedVATRate === false) {
                            $this->errorTO->type = FLAG_ERRORTO_ERROR;
                            $this->errorTO->description = "Could not retrieve calculated vat rate : " . $msg;
                            return false;
                        }

                        $postingOrderDetailTO->productVatRate = $calculatedVATRate;
                        $postingOrderDetailTO->vatAmount = round($postingOrderDetailTO->extPrice * ($calculatedVATRate / 100), 2);
                        $postingOrderDetailTO->totPrice = $postingOrderDetailTO->extPrice + $postingOrderDetailTO->vatAmount;

                    }
                } // end product loop
            }
            unset($postingOrderDetailTO);


            // CHECK CREDIT LIMIT if has Notfication loaded
            // MUST be done after all other pricing calculations such as discounts
            if (
                ($mfPS[0]["ledger_credit_limit"] != "") &&
                ((intval($mfPS[0]["ledger_balance"]) + $documentTotal) > intval($mfPS[0]["ledger_credit_limit"]))
            ) {
                $mfNCL = $biDAO->getNotificationCreditLimitForUser($userId, $principalAliasId, $postingOrderTO->storeChainUId);
                // if there is a specific notification set, use that, or just use the general one, should only be one output type which is "SCREEN"
                if (sizeof($mfNCL) > 0) {
                    if ($mfNCL["value"] == "WARNING") {
                        // just ask user if not already confirmed
                        if ($postingOrderTO->confirmOption != "Y") {
                            $this->errorTO->type = FLAG_ERRORTO_WARNING;
                            $this->errorTO->description = "This Order exceeds the allowed credit limit (Balance: {$mfPS[0]["ledger_balance"]} , Credit Limit {$mfPS[0]["ledger_credit_limit"]}) for this principal. Are you sure you would like to continue ?<BR><BR>";
                            return false;
                        }
                    } else if ($mfNCL["value"] == "ERROR") {
                        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                        $this->errorTO->description = "This Order exceeds the allowed credit limit (Balance: {$mfPS[0]["ledger_balance"]} , Credit Limit {$mfPS[0]["ledger_credit_limit"]}) for this prinicipal. This check has been enforced as per Notifications Setting<BR>";
                        return false;
                    }
                } // end notification loaded
            } // end exceeded limit

        } else {

            // assign proforma pricing for reporting purposes only to non Orders according to what is loaded in principal_document_type.
            if ((isset($this->pDT_ProformaPricing[$principalAliasId])) &&
                (in_array($postingOrderTO->documentType, $this->pDT_ProformaPricing[$principalAliasId]))) {

                foreach ($postingOrderTO->detailArr as &$postingOrderDetailTO) {

                    // skip if pricing is already supplied
                    if ((empty($postingOrderDetailTO->listPrice)) && ($postingOrderDetailTO->priceOverrideUseSuppliedVals !== true)) {
                        // came from capture screen (or captured required approval in OH)
                        if (($postingOrderTO->dataSource == DS_CAPTURE) && ($postingOrderDetailTO->priceOverrideValue > 0)) {

                            list($calculatedVATRate, $msg) = $this->productDAO->getCalculatedVATRate($principalAliasId, $postingOrderTO->storeChainUId, $postingOrderDetailTO->productUId);
                            if ($calculatedVATRate === false) {
                                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                                $this->errorTO->description = "Could not retrieve calculated vat rate : " . $msg;
                                return false;
                            }

                            $postingOrderDetailTO->listPrice = $postingOrderDetailTO->priceOverrideValue;
                            $postingOrderDetailTO->discountValue = "0";
                            $postingOrderDetailTO->nettPrice = $postingOrderDetailTO->priceOverrideValue;
                            $postingOrderDetailTO->extPrice = $postingOrderDetailTO->nettPrice * $postingOrderDetailTO->quantity;
                            $postingOrderDetailTO->productVatRate = $calculatedVATRate;
                            $postingOrderDetailTO->vatAmount = round($postingOrderDetailTO->extPrice * ($calculatedVATRate / 100), 2);
                            $postingOrderDetailTO->totPrice = $postingOrderDetailTO->extPrice + $postingOrderDetailTO->vatAmount;

                        } // any other (EDI) source where no vals supplied
                        else {

                            $mfAP = $productDAO->getActivePricesForProduct($principalAliasId,
                                $postingOrderTO->storeChainUId,
                                $postingOrderDetailTO->productUId,
                                $returnVATSettings = true);

                            if (isset($mfAP[0])) {
                                // active price found
                                $postingOrderDetailTO->listPrice = $mfAP[0]["list_price"];
                                $postingOrderDetailTO->discountValue = $mfAP[0]["discount_value"];
                                $postingOrderDetailTO->nettPrice = $mfAP[0]["price"];
                                $postingOrderDetailTO->extPrice = $postingOrderDetailTO->nettPrice * $postingOrderDetailTO->quantity;
                                $postingOrderDetailTO->productVatRate = $mfAP["calculatedVatRate"]; // this index is at root level asit applies for all rows, unlike the rest
                                $postingOrderDetailTO->vatAmount = round($postingOrderDetailTO->extPrice * ($postingOrderDetailTO->productVatRate / 100), 2);
                                $postingOrderDetailTO->totPrice = $postingOrderDetailTO->extPrice + $postingOrderDetailTO->vatAmount;
                            } else {
                                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                                $this->errorTO->description = "No active pricing found for product!";
                                return $this->errorTO;
                            }

                        }
                    }

                } // end loop
                unset($postingOrderDetailTO);


            }

        }


        // ALL is OK, so assign the Order Sequence
        if ($postingOrderTO->orderSequenceNo == "") {
            //get sequence
            $postingOrderTO->orderSequenceNo = $sequenceDAO->getOrdersSequence();
            if ($postingOrderTO->orderSequenceNo == "") {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Unable to Assign the Order Sequence!";
                return false;
            }
        }


        // VAT Authorisations - done in its own loop as it needs pricing to have been set
        // Went live : 19-Nov-2012 @ 16:10
        $i = 0;
        unset($postingOrderDetailTO);
        foreach ($postingOrderTO->detailArr as $postingOrderDetailTO) {

            if (
                // there is a risk here if the pricing is from EDI but VAT is being set from RT masterfiles then the vat auth is not enforced
                // so this line was added below
                (($postingOrderDetailTO->vatRateWasEmpty) || ($postingOrderDetailTO->priceOverrideUseSuppliedVals === false)) &&
                ($postingOrderTO->documentType == DT_ORDINV) &&
                ($postingOrderDetailTO->extPrice > 0) &&
                ($postingOrderDetailTO->vatAmount == 0) &&
                ((($postingOrderTO->storeNoVATAuthorisedBy == "") && ($mfPS[0]['no_vat'] == 1)) || (($postingOrderDetailTO->productNoVATAuthorisedBy == "") && ($postingOrderDetailTO->mfProductVatRate == 0)))
            ) {

                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                if (($postingOrderDetailTO->productNoVATAuthorisedBy == "") && ($postingOrderDetailTO->mfProductVatRate == 0)) {
                    $this->errorTO->description = "This Product (" . $postingOrderDetailTO->productDescription . ") is not authorised for zero vat. Orders may not be processed to it.";
                    $this->errorTO->identifier = "P";
                    $this->errorTO->identifier2 = $i;
                } else {
                    $this->errorTO->description = "This Store (" . ((isset($mfPS[0]['deliver_name'])) ? $mfPS[0]['deliver_name'] : $mfPS[0]['store_name']) . ") is not authorised for zero vat. Orders may not be processed to it.";
                }
                return false;

            }

            $i++;
        }


        // provide an EXIT for custom validation
        $rTO = $specialValidationDAO->exitPrincipalDocumentValidation($postingOrderTO);
        if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = $rTO->description;
            return false;
        }

        // This is done as the last step so that we don't waste sequences !!
        if ((in_array($postingOrderTO->documentType, array(DT_QUOTATION, DT_PURCHASE_ORDER))) && ($postingOrderTO->documentNumber != "")) {
            //
        } else {

            $postingOrderTO->useRTDocNum = $mfPPref[0]["use_rt_doc_num"];
            // determine whether to use a doc sequence or not
            $useAutoSeq = $principalDAO->usesDocumentNumberAutoSeq($principalAliasId, $postingOrderTO->documentType, $postingOrderTO->dataSource, $postingOrderTO->processedDepotUId, $postingOrderTO->capturedBy);
            if($useAutoSeq instanceof ErrorTO && $useAutoSeq->isError()){
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = $useAutoSeq->description;
                return false;
            }

            if (($useAutoSeq === false) && ($postingOrderTO->documentNumber == "")) {
                // If the config says to use the client sequence, and the doc number needs to be transformed from the value in clientDocumentNumber, then
                // the value needs to have already been placed inside document number, hence our check on documentNumber==""
                $postingOrderTO->documentNumber = $postingOrderTO->clientDocumentNumber; // if hasn't been passed, then overwrite it
            } else if ($useAutoSeq === true) {

                if ($mfPPref[0]["document_number_prefix"] == NULL) {
                    $postingOrderTO->documentNumber = $sequenceDAO->getDocumentNumberSequence($postingOrderTO->documentType, $principalAliasId, $postingOrderTO->processedDepotUId, $postingOrderTO->dataSource);
                } else {
                    $postingOrderTO->documentNumber = $mfPPref[0]["document_number_prefix"] . substr($sequenceDAO->getDocumentNumberSequence($postingOrderTO->documentType, $principalAliasId, $postingOrderTO->processedDepotUId, $postingOrderTO->dataSource), -6);
                }
                //get sequence
                // $postingOrderTO->documentNumber = $sequenceDAO->getDocumentNumberSequence($postingOrderTO->documentType, $principalAliasId, $postingOrderTO->processedDepotUId, $postingOrderTO->dataSource);
                if ($postingOrderTO->documentNumber == "") {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Unable to Assign the Document Sequence!";
                    return false;
                }
            }

            if (strlen($postingOrderTO->documentNumber) > 10) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Client Sequences are restricted to 10 chars in length.";
                return false;
            }
            if ($postingOrderTO->documentNumber == "") {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Your preferences indicate you have auto sequence turned off, please input a customer number to allocate as the document number.";
                return false;
            }
            if ($mfPPref[0]["document_unique"] == "Y") {
                $mfDUPO = $importDAO->getOrdersByDN($principalAliasId, $postingOrderTO->documentNumber, $postingOrderTO->processedDepotUId, $postingOrderTO->documentType);
                if (sizeof($mfDUPO) > 0) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Document Number is not unique in Orders.{$principalAliasId}-{$postingOrderTO->documentNumber}-{$postingOrderTO->processedDepotUId}-{$postingOrderTO->documentType}";
                    return false;
                }
                $mfDUPD = $importDAO->getDocumentsByDN($principalAliasId, $postingOrderTO->documentNumber, $postingOrderTO->processedDepotUId, ($postingOrderTO->documentType == DT_ORDINV_ZERO_PRICE) ? DT_ORDINV : $postingOrderTO->documentType); // clipper converts it
                if (sizeof($mfDUPD) > 0) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Document Number is not unique in Tracking Transaction.  - " . $principalAliasId . "<br>";
                    return false;
                }
            }

        }

        if ($principalAliasId == 369) {
            echo "<br>";
            echo "Here";
            echo $postingOrderTO->documentNumber;
            echo "<br>";
        }


        // end determine document number


        return true;

    }

    // TOs are pass-by-reference anyway, so ampersand is not really necessary
    public function Postorder(&$postingOrderTO)
    {
        global $ROOT, $PHPFOLDER;

        if (!isset($this->postStockDAO)) $postStockDAO = $this->postStockDAO = new PostStockDAO($this->dbConn); else $postStockDAO = $this->postStockDAO;

        // validation above uses the now() for duplicate check !
        $this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

        // provide an EXIT for custom overrides
        if (!isset($this->specialValidationDAO)) $specialValidationDAO = $this->specialValidationDAO = new SpecialValidationDAO($this->dbConn); else $specialValidationDAO = $this->specialValidationDAO;
        $rTO = $specialValidationDAO->exitPrincipalPostDocumentOverrides($postingOrderTO);
        if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = $rTO->description;
            return false;
        }

        $documentTypeDescription = "";

        $resultOK = $this->postOrderValidation($postingOrderTO, $documentTypeDescription);

        if ($resultOK === true) {

            if ($postingOrderTO->DMLType == "INSERT") {
                // create the header
                if ($postingOrderTO->deliveryDate != "") $pDD = "'{$postingOrderTO->deliveryDate}'"; else $pDD = " null ";
                $sql = "insert into orders
						(
						storechain_uid,
						principal_uid,
						order_number,
						order_sequence_no,
						delivery_instructions,
						date,
						batchguid,
						capturedate,
						captureuser_uid,
						captured_by,
						deleted,
						edi_created,
						edi_filename,
						document_type,
						deliverydate,
						processed_depot_uid,
						data_source,
						document_number,
						client_document_number,
						general_reference_1,
						general_reference_2,
						incoming_filename,
						vendor_buying_group_code,
            force_depot_uid,
    		 	  additional_type,
    		 	  pod_reason_code
						)
						values
						(
							" . $postingOrderTO->storeChainUId . ",
							" . $postingOrderTO->principalUId . ",
							'" . $postingOrderTO->orderNumber . "',
							" . $postingOrderTO->orderSequenceNo . ",
							'" . $postingOrderTO->deliveryInstructions . "',
							'" . $postingOrderTO->documentDate . "',
							'" . $postingOrderTO->batchGUID . "',
							'" . gmdate(GUI_PHP_DATETIME_FORMAT) . "',
							" . $postingOrderTO->captureUserUId . ",
							'" . $postingOrderTO->capturedBy . "',
							" . $postingOrderTO->deleted . ",
							'" . (($postingOrderTO->WMS == "Y") ? "S" : $postingOrderTO->ediCreated) . "',
							'" . $postingOrderTO->ediFileName . "',
							" . $postingOrderTO->documentType . ",
							{$pDD},
							{$postingOrderTO->processedDepotUId},
							'{$postingOrderTO->dataSource}',
							'{$postingOrderTO->documentNumber}',
							'{$postingOrderTO->clientDocumentNumber}',
							'{$postingOrderTO->generalReference1}',
							'{$postingOrderTO->generalReference2}',
							'{$postingOrderTO->incomingFileName}',
							'" . substr($postingOrderTO->vendorBuyingGroupCode, 0, 20) . "',
              " . (($postingOrderTO->forceDepotUId == "") ? "NULL" : $postingOrderTO->forceDepotUId) . ",
              '" . $postingOrderTO->additionalType . "',
              '" . $postingOrderTO->podreasonuid . "'
						)";

                $this->errorTO = $this->dbConn->processPosting($sql, $postingOrderTO->orderNumber);
                if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->description .= $sql;
                    // echo $this->errorTO->description;
                    return $this->errorTO;
                }
                $postingOrderTO->UId = $this->dbConn->dbGetLastInsertId();
                $this->errorTO->identifier2 = strtoupper($documentTypeDescription);
                $postingOrderTO->captureDate = gmdate(GUI_PHP_DATE_FORMAT);
                $postingOrderTO->captureTime = gmdate(GUI_PHP_TIME_FORMAT);

                // create the detail
                $sql = "insert into orders_detail
						(
						orders_uid,
						product_uid,
						quantity,
						pallets,
						chosen_pricing_uid,
						price_type,
						list_price,
						discount_value,
						nett_price,
						price_override,
						price_override_value,
						line_no,
						page_no,
						client_line_no,
						client_page_no,
    			  additional_type
						)
						values ";
                $cases = $sellingPrice = $exclusiveTotal = $vatTotal = $invoiceTotal = 0;
                foreach ($postingOrderTO->detailArr as $key => &$postingOrderDetailTO) {
                    if ($postingOrderDetailTO->chosenPricingUId == "") $postingOrderDetailTO->chosenPricingUId = 0;
                    if ($postingOrderDetailTO->listPrice == "") $postingOrderDetailTO->listPrice = 0;
                    if ($postingOrderDetailTO->discountValue == "") $postingOrderDetailTO->discountValue = 0;
                    if ($postingOrderDetailTO->nettPrice == "") $postingOrderDetailTO->nettPrice = 0;
                    if ($postingOrderDetailTO->pallets == "") $postingOrderDetailTO->pallets = 0;
                    if ($postingOrderDetailTO->priceOverrideValue == "") $postingOrderDetailTO->priceOverrideValue = 0;

                    if ($key == "0") $comma = ""; else $comma = ",";
                    $postingOrderDetailTO->ordersUId = $postingOrderTO->UId;

                    if ($postingOrderTO->documentType == DT_MCREDIT_OTHER) {
                        $postingOrderDetailTO->listPrice = round(($postingOrderDetailTO->listPrice / $postingOrderDetailTO->itemspercase), 2);
                        $postingOrderDetailTO->nettPrice = round(($postingOrderDetailTO->nettPrice / $postingOrderDetailTO->itemspercase), 2);
                        $postingOrderDetailTO->extPrice = round(($postingOrderDetailTO->extPrice / $postingOrderDetailTO->itemspercase), 2);
                        $postingOrderDetailTO->vatAmount = round(($postingOrderDetailTO->vatAmount / $postingOrderDetailTO->itemspercase), 2);
                        $postingOrderDetailTO->totPrice = round(($postingOrderDetailTO->totPrice / $postingOrderDetailTO->itemspercase), 2);
                    }

                    $sql .= "{$comma}(
								{$postingOrderDetailTO->ordersUId},
								{$postingOrderDetailTO->productUId},
								'{$postingOrderDetailTO->quantity}',
								{$postingOrderDetailTO->pallets},
								{$postingOrderDetailTO->chosenPricingUId},
								'{$postingOrderDetailTO->priceType}',
								{$postingOrderDetailTO->listPrice},
								{$postingOrderDetailTO->discountValue},
								{$postingOrderDetailTO->nettPrice},
								'{$postingOrderDetailTO->priceOverride}',
								{$postingOrderDetailTO->priceOverrideValue},
								'{$postingOrderDetailTO->lineNo}',
								'{$postingOrderDetailTO->pageNo}',
								'{$postingOrderDetailTO->clientLineNo}',
								'{$postingOrderDetailTO->clientPageNo}',
								'{$postingOrderDetailTO->additionalType}'
							)";

                    $cases += $postingOrderDetailTO->quantity;
                    $sellingPrice += $postingOrderDetailTO->nettPrice; // nettprice, not list price
                    $exclusiveTotal += $postingOrderDetailTO->extPrice;
                    $vatTotal += (float)$postingOrderDetailTO->vatAmount;
                    $invoiceTotal += $postingOrderDetailTO->totPrice;
                }

                $this->errorTO = $this->dbConn->processPosting($sql, $postingOrderTO->orderNumber);
                if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                    return $this->errorTO;
                }

                if (sizeof($postingOrderTO->pricingDocumentArr) > 0) {
                    // create the document pricing if necessary
                    $sql = "insert into orders_pricing_document
							(
							orders_uid,
							description,
							chosen_pricing_document_uid,
							quantity,
							deal_type_uid,
							value,
							apply_per_unit,
							cumulative_type,
							unit_price_type_uid,
							apply_level,
							principal_product_uid,
							discount_value
							)
							values ";
                    foreach ($postingOrderTO->pricingDocumentArr as $key => $PostingOrderDocumentPricingTO) {
                        $ppUId = ($PostingOrderDocumentPricingTO->principalProductUId == "") ? "NULL" : $PostingOrderDocumentPricingTO->principalProductUId;
                        if ($key == "0") $comma = ""; else $comma = ",";
                        $PostingOrderDocumentPricingTO->ordersUId = $postingOrderTO->UId;
                        $sql .= "{$comma}(
									{$PostingOrderDocumentPricingTO->ordersUId},
									'{$PostingOrderDocumentPricingTO->description}',
									{$PostingOrderDocumentPricingTO->chosenPricingDocumentUId},
									{$PostingOrderDocumentPricingTO->quantity},
									{$PostingOrderDocumentPricingTO->dealTypeUId},
									{$PostingOrderDocumentPricingTO->value},
									'{$PostingOrderDocumentPricingTO->applyPerUnit}',
									'{$PostingOrderDocumentPricingTO->cumulativeType}',
									{$PostingOrderDocumentPricingTO->unitPriceTypeUId},
									'{$PostingOrderDocumentPricingTO->applyLevel}',
									{$ppUId},
									$PostingOrderDocumentPricingTO->discountValue
								)";
                    }

                    $this->errorTO = $this->dbConn->processPosting($sql, $postingOrderTO->orderNumber);
                    if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                        $PostingOrderDocumentPricingTO->UId = $this->dbConn->dbGetLastInsertId();
                    } else {
                        return $this->errorTO; // failed
                    }
                } // end document pricing


                // orders originating from the web, need to be put straight into TT as well
                if ($this->principalPreferenceArr[0]["direct_insert_tt"] == "Y") {
                    include_once($ROOT . $PHPFOLDER . "TO/PostingDocumentTO.php");
                    include_once($ROOT . $PHPFOLDER . "TO/PostingDocumentDetailTO.php");

//    				print_r($postingOrderTO);

                    $dTO = new PostingDocumentTO();
                    $dTO->DMLType = "INSERT";
                    $dTO->principalUId = $postingOrderTO->principalUId;
                    $dTO->depotUId = $postingOrderTO->processedDepotUId;
                    $dTO->documentNumber = $postingOrderTO->documentNumber;
                    $dTO->clientDocumentNumber = $postingOrderTO->clientDocumentNumber;
                    $dTO->sourceDocumentNumber = $postingOrderTO->sourceDocumentNumber;
                    $dTO->invoiceNumber = $postingOrderTO->invoiceNumber;
                    $dTO->fileLogUId = $postingOrderTO->fileLogUId;
                    $dTO->documentTypeUId = $postingOrderTO->documentType;
                    $dTO->processedDate = gmdate(GUI_PHP_DATE_FORMAT);
                    $dTO->processedTime = gmdate(GUI_PHP_TIME_FORMAT);
                    $dTO->mergedDate = $dTO->processedDate;
                    $dTO->mergedTime = $dTO->processedTime;
                    $dTO->validationDate = $dTO->processedDate;
                    $dTO->validationTime = $dTO->processedTime;
                    $dTO->validationStatus = 2; // unknown
                    $dTO->incomingFile = $postingOrderTO->incomingFileName;
                    $dTO->TransmissionFlag1 = $dTO->TransmissionFlag2 = $dTO->TransmissionFlag3 = $dTO->TransmissionFlag4 = "0";
                    $dTO->orderSequenceNo = $postingOrderTO->orderSequenceNo;
                    $dTO->orderDate = $postingOrderTO->documentDate;
                    $dTO->invoiceDate = (($postingOrderTO->invoiceDate == "") ? $dTO->orderDate : $postingOrderTO->invoiceDate);
                    $dTO->deliveryDate = $postingOrderTO->deliveryDate;
                    $dTO->requestedDeliveryDate = $postingOrderTO->requestedDeliveryDate;
                    $dTO->claimNumber = $postingOrderTO->claimNumber;
                    $dTO->documentServiceTypeUId = $postingOrderTO->documentServiceTypeUId;
                    $dTO->documentRepCodeUid = $postingOrderTO->documentRepCodeUid;
                    $dTO->additionalType = $postingOrderTO->additionalType;
                    $dTO->additionalDetails = $postingOrderTO->additionalDetails;
                    $dTO->deliveryDueDate = $postingOrderTO->deliveryDueDate;
                    $dTO->version = $postingOrderTO->documentMasterVersion;
                    $dTO->podReasonUId = $postingOrderTO->podreasonuid;

                    //regardless of depot an - arrival, credit note, debit note, stock adjustment - increase/decrease becomes : PROCESSED
                    if (in_array($dTO->documentTypeUId, array(DT_ARRIVAL, DT_CREDITNOTE, DT_MDEBIT_NOTE, DT_STOCKADJUST_NEG, DT_UPLIFT_CREDIT, DT_STOCKADJUST_POS, DT_SUPPLIER_INVOICE, DT_MCREDIT_PRICING, DT_MCREDIT_VALUE, DT_MCREDIT_OTHER, DT_BUYER_ORIGINATED_CREDIT_CLAIM, DT_PAYMENTTO))) {
                        $dTO->documentStatusUId = 81; //Processed. {
                    } elseif (in_array($dTO->documentTypeUId, array(DT_QUOTATION))) {
                        $dTO->documentStatusUId = 75; //Accepted.
                    } elseif (in_array($dTO->documentTypeUId, array(DT_GOODS_IN_TRANSIT))) {
                        $dTO->documentStatusUId = 76; //Accepted.
                    } else {
                        if ($postingOrderTO->orderStartStatus != "") {
                            $dTO->documentStatusUId = $postingOrderTO->orderStartStatus;  //depot set status.
                        } else {
                            $dTO->documentStatusUId = 74; //Unaccepted, (Queued for Processing is no longer a requirement as orders are processed in full on web system.)
                        }
                    }

                    $dTO->principalStoreUId = $postingOrderTO->storeChainUId;

                    // update depot store association for WMS
                    if (($postingOrderTO->WMS == "Y") && (in_array($postingOrderTO->documentType, array(DT_ORDINV, DT_ORDINV_ZERO_PRICE, DT_DELIVERYNOTE, DT_WALKIN_INVOICE)))) {
                        $WMSPrincipalArr = $this->principalDAO->getDepotPrincipal($postingOrderTO->processedDepotUId);
                        // only update if single link only
                        if (sizeof($WMSPrincipalArr) == 1) {
                            $depotPrincipalStoreArr = $this->storeDAO->getPrincipalStoreParentAssociations($WMSPrincipalArr[0]["principal_uid"], $postingOrderTO->storeChainUId);
                            // only update if single link only
                            if (sizeof($depotPrincipalStoreArr) == 1) {
                                $dTO->depotPrincipalStoreUId = $depotPrincipalStoreArr[0]["psm_parent_uid"];
                            }
                        }
                    }

                    $dTO->customerOrderNumber = $postingOrderTO->orderNumber;
                    $dTO->cases = $cases;
                    $dTO->sellingPrice = $sellingPrice;
                    $dTO->exclusiveTotal = $exclusiveTotal;
                    $dTO->vatTotal = $vatTotal;
                    $dTO->invoiceTotal = $invoiceTotal;
                    $dTO->dataSource = $postingOrderTO->dataSource;
                    $dTO->capturedBy = (intval($postingOrderTO->captureUserUId) == 0) ? $postingOrderTO->capturedBy : $postingOrderTO->captureUserUId;
                    $dTO->buyerAccountReference = $postingOrderTO->buyerAccountReference;
                    $dTO->offInvoiceDiscount = $postingOrderTO->offInvoiceDiscount;
                    $dTO->offInvoiceDiscountType = $postingOrderTO->offInvoiceDiscountType;

                    // NB : Do NOT use $$postingOrderDetailTO as the "as $postingOrderDetailTO" because it overwrites all the original array values !
                    foreach ($postingOrderTO->detailArr as $TO) {
                        $ddTO = new PostingDocumentDetailTO();
                        $ddTO->lineNo = $TO->pageNo . $TO->lineNo;
                        $ddTO->clientLineNo = $TO->clientPageNo . $TO->clientLineNo;
                        $ddTO->productUId = $TO->productUId;
                        $ddTO->orderedQty = $TO->quantity;
                        $ddTO->documentQty = $TO->quantity;
                        $ddTO->deliveredQty = $TO->quantity;
                        $ddTO->sellingPrice = $TO->listPrice;
                        $ddTO->discountValue = $TO->discountValue;
                        $ddTO->discountReference = $TO->discountReference;
                        $ddTO->netPrice = $TO->nettPrice;
                        $ddTO->extendedPrice = $TO->extPrice;
                        $ddTO->vatAmount = $TO->vatAmount;
                        $ddTO->vatRate = $TO->productVatRate;
                        $ddTO->total = $TO->totPrice;
                        $ddTO->pallets = $TO->pallets;
                        $ddTO->productCode = $TO->originalProductCode;
                        $ddTO->wsUniqueCreatorId = $TO->wsUniqueCreatorId;
                        $ddTO->additionalType = $TO->additionalType;
                        $ddTO->userModified = $TO->userModified;
                        $ddTO->comment = $TO->comment;

                        //minor hack
                        $ddTO->oldQuantity = $TO->oldQuantity;
                        $ddTO->oldPrice = $TO->oldPrice;

                        $dTO->detailArr[] = $ddTO;
                    }

                    // do the posting into TT
                    $this->errorTO = $this->postDocument($dTO, $pWebSourceChecksAlreadyDone = true);
                    if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                        return $this->errorTO;
                    } else {
                        $postingOrderTO->dMUId = $dTO->dmUId;
                    }

                }

            } // end INSERT
            // START : STOCK ITEM MASTER UPDATES
            if ($postingOrderTO->WMS == "Y" && $postingOrderTO->documentType != DT_GOODS_IN_TRANSIT) {

                // update stock
                foreach ($postingOrderTO->detailArr as $TO) {
                    if ($postingOrderTO->documentType == DT_ARRIVAL || $postingOrderTO->documentType == DT_SUPPLIER_INVOICE) {
                        $rTO = $postStockDAO->updateStockArrival($postingOrderTO->principalUId, $postingOrderTO->processedDepotUId, $TO->productUId, $TO->quantity);
                        if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
                            $this->errorTO->type = FLAG_ERRORTO_ERROR;
                            $this->errorTO->description = "Failed to update Stock Arrival in postOrder : " . $rTO->description;
                            return $this->errorTO;
                        }
                        //  Reduce GIT from arrival

                        $rTO = $postStockDAO->reduceGitFromArrival($postingOrderTO->principalUId, $postingOrderTO->processedDepotUId, $TO->productUId, $TO->quantity);
                        if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
                            $this->errorTO->type = FLAG_ERRORTO_ERROR;
                            $this->errorTO->description = "Failed to update GIT in postOrder : " . $rTO->description;
                            return $this->errorTO;
                        }

                        // Update Pallets in Warehouse stock
                        // Get stock warehouse and principal

                        $palDep = $postStockDAO->getStockDepotPrin($postingOrderTO->processedDepotUId);

                        if ($palDep[0]['pallet_principal'] <> NULL && $palDep[0]['pallet_depot'] && $TO->productCode == "CHEP01") {
                            // Update pallet prin and depot = arrival
                            $upPal = $postStockDAO->updatestockTransferArrival($palDep[0]['pallet_principal'],
                                $palDep[0]['pallet_depot'],
                                '154718',
                                $TO->quantity);
                        }

                    } else if ($postingOrderTO->documentType == DT_STOCKADJUST_POS) {

                        //adjust stock - positive, add stock items
                        $rTO = $postStockDAO->updateStockAdjustment($postingOrderTO->principalUId, $postingOrderTO->processedDepotUId, $TO->productUId, $TO->quantity, $negative = false);
                        if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
                            $this->errorTO->type = FLAG_ERRORTO_ERROR;
                            $this->errorTO->description = "Failed to update Stock Adjustment in postOrder : " . $rTO->description;
                            return $this->errorTO;
                        }

                    } else if ($postingOrderTO->documentType == DT_STOCKADJUST_NEG) {

                        //adjust stock - negative, reduce stock items
                        $rTO = $postStockDAO->updateStockAdjustment($postingOrderTO->principalUId, $postingOrderTO->processedDepotUId, $TO->productUId, $TO->quantity, $negative = true);
                        if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
                            $this->errorTO->type = FLAG_ERRORTO_ERROR;
                            $this->errorTO->description = "Failed to update Stock Adjustment in postOrder : " . $rTO->description;
                            return $this->errorTO;
                        }

                    } else if ($postingOrderTO->documentType == DT_UPLIFTS) {
                        /* uplifts no longer update stock
		    				$rTO=$postStockDAO->updateStockUplift($postingOrderTO->principalUId, $postingOrderTO->processedDepotUId, $TO->productUId, $TO->quantity);
		    				if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
		    				  $this->errorTO->type=FLAG_ERRORTO_ERROR;
		    				  $this->errorTO->description="Failed to update Stock Uplifts in postOrder : ".$rTO->description;
									return $this->errorTO;
		    				}
		    				*/
                    } else if (in_array($postingOrderTO->documentType, array(DT_ORDINV, DT_ORDINV_ZERO_PRICE, DT_DELIVERYNOTE, DT_DESTRUCTION_DISPOSAL, DT_WALKIN_INVOICE))) {
                        $rTO = $postStockDAO->updateStockOrder($postingOrderTO->principalUId, $postingOrderTO->processedDepotUId, $TO->productUId, $TO->quantity);
                        if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
                            $this->errorTO->type = FLAG_ERRORTO_ERROR;
                            $this->errorTO->description = "Failed to update Stock Order in postOrder : " . $rTO->description;
                            return $this->errorTO;
                        }
                    }
                }
            }
            // stock Update for GIT
            if ($postingOrderTO->documentType == DT_GOODS_IN_TRANSIT) {
                // file_put_contents($ROOT.$PHPFOLDER.'log/posttx21.txt', print_r($postingOrderTO, TRUE), FILE_APPEND);

                // Get GIT Depot
                $gitDepot = $postStockDAO->getGoodsInTransitDepot($postingOrderTO->storeChainUId);

                if (count($gitDepot) == 1) {
                    $updateRecievingGitDep = 'Y';
                } else {
                    $updateRecievingGitDep = 'N';
                }
                file_put_contents($ROOT . $PHPFOLDER . 'log/postda21e.txt', print_r($postingOrderTO->detailArr, TRUE), FILE_APPEND);
                foreach ($postingOrderTO->detailArr as $detTo) {
                    file_put_contents($ROOT . $PHPFOLDER . 'log/postgd21dd.txt', $detTo->productUId, FILE_APPEND);


                    // Add to delivered and recalculate balances
                    $gitDel = $postStockDAO->updateGoodsInTransitDelivered($postingOrderTO->principalUId, $postingOrderTO->processedDepotUId, $detTo->productUId, $detTo->quantity);
                    if ($updateRecievingGitDep == 'Y') {
                        // CheckForExistingCustomerBalances if product exists in Receiving DepotDAO
                        $gitProdLine = $postStockDAO->checkRecDepProdLine($postingOrderTO->principalUId, $gitDepot[0]['branch_code'], $detTo->productUId);


                        if (count($gitProdLine) > 0) {
                            // Update GIT

                            $this->errorTO = $postStockDAO->updateGoodsInTransit($postingOrderTO->principalUId, $gitDepot[0]['branch_code'], $detTo->productUId, $detTo->quantity);
                            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                                $this->errorTO->description = "Failed to update GIT delivered : " . $rTO->description;
                                return $this->errorTO;
                            }
                        } else {
                            // Insert Prod
                            $this->errorTO = $postStockDAO->insertGoodsInTransit($postingOrderTO->principalUId, $gitDepot[0]['branch_code'], $detTo->productUId, $detTo->quantity, $detTo->productCode, $detTo->productDescription);
                            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                                $this->errorTO->description = "Failed to insert GIT record : " . $rTO->description;
                                return $this->errorTO;
                            }
                        }
                    }
                }
            }
            // END : STOCK ITEM MASTER UPDATES
        } else {
            // failed validation
            return $this->errorTO;
        }
        $this->errorTO->description = "Order successfully saved." . (($this->principalPreferenceArr[0]["direct_insert_tt"] == "Y") ? "<br>Direct posting to Transaction Tracking applied." : "");

        return $this->errorTO;
    }

    public function closeOrderEDI($orderSequenceNo, $fileName)
    {
        $sql = "UPDATE orders
				SET edi_created='Y',
					edi_filename='" . $fileName . "'
			    WHERE  order_sequence_no=" . $orderSequenceNo;

        $this->errorTO = $this->dbConn->processPosting($sql, $orderSequenceNo);

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Order successfully saved";
            $this->errorTO->identifier = $this->dbConn->dbGetLastInsertId();
        } else {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
        }

        return $this->errorTO;
    }


    public function closeDocumentDepotEDI($orderSequenceNo, $fileName)
    {

        $sql = "UPDATE orders
              SET
                edi_depot_created='Y',
                edi_depot_filename='" . trim($fileName) . "'
            WHERE  order_sequence_no = " . $orderSequenceNo;

        $this->errorTO = $this->dbConn->processPosting($sql, $orderSequenceNo);

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "EDI Export Flag and Filename Successfully Updated!";
            $this->errorTO->identifier = $this->dbConn->dbGetLastInsertId();
        } else {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
        }

        return $this->errorTO;
    }


    // $webSourceChecksAlreadyDone ~ if this is an insert directly after an web source capture, it means most checks are already done, so skip to save time
    public function postDocumentValidation(&$postingDocumentTO, $webSourceChecksAlreadyDone = false)
    {
        global $ROOT;
        global $PHPFOLDER;

        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $biDAO = new BIDAO($this->dbConn);


        // this must come before the escape below !!!
        if ($postingDocumentTO->DMLType == "INSERT") {
            // if is set up for notification onCreate, then set the flag if not already supplied
            $mfNDI = $biDAO->getNotificationDocumentConfirmation($postingDocumentTO->principalUId, $postingDocumentTO->depotUId, $postingDocumentTO->documentTypeUId, $postingDocumentTO->dataSource, $postingDocumentTO->capturedBy, $postingDocumentTO->documentStatusUId);
            if (sizeof($mfNDI) > 0) {
                foreach ($mfNDI as $mf) {
                    $postingDocumentTO->notificationArray[] = $mf['uid'];
                }
            }
        }

        if ($webSourceChecksAlreadyDone === true) return true; // save time, not necessary to check the exists check below either as we can rely on the db constraint

        include_once($ROOT . $PHPFOLDER . "DAO/StoreDAO.php");
        include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");
        include_once($ROOT . $PHPFOLDER . "DAO/DepotDAO.php");
        include_once($ROOT . $PHPFOLDER . "DAO/TransactionDAO.php");
        include_once($ROOT . $PHPFOLDER . "DAO/ImportDAO.php");
        include_once($ROOT . $PHPFOLDER . "DAO/ProductDAO.php");
        include_once($ROOT . $PHPFOLDER . "DAO/BIDAO.php");
        include_once($ROOT . $PHPFOLDER . "DAO/SequenceDAO.php");

        // if not set from constructor globals, then initialise here
        if (!isset($this->principalDAO)) $principalDAO = $this->principalDAO = new PrincipalDAO($this->dbConn); else $principalDAO = $this->principalDAO;
        if (!isset($this->storeDAO)) $storeDAO = $this->storeDAO = new StoreDAO($this->dbConn); else $storeDAO = $this->storeDAO;
        if (!isset($this->productDAO)) $productDAO = $this->productDAO = new ProductDAO($this->dbConn); else $productDAO = $this->productDAO;
        if (!isset($this->importDAO)) $importDAO = $this->importDAO = new ImportDAO($this->dbConn); else $importDAO = $this->importDAO;
        $depotDAO = new DepotDAO($this->dbConn);
        $transactionDAO = new TransactionDAO($this->dbConn);

        if (($postingDocumentTO->DMLType != "INSERT") && ($postingDocumentTO->DMLType != "UPDATE")) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Invalid DMLType.";
            return false;
        }

        /* save on processing for time being
		// check if principal is valid
		$mfP = $principalDAO->getPrincipalItem($postingDocumentTO->principalUId);
		if(sizeof($mfP)==0) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Principal.";
			return false;
		};


		// check if store is valid
		$mfPS = $storeDAO->getPrincipalStoreExclChainItem($postingDocumentTO->principalStoreUId, $showVendorStores=true);
		if(sizeof($mfPS)==0) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Principal Store.".$postingDocumentTO->principalStoreUId;
			return false;
		};

		// check if depot is valid
		$mfD = $depotDAO->getDepotItem($postingDocumentTO->depotUId);
		if(sizeof($mfD)==0) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Depot.";
			return false;
		};


		*/

        // check fields are float cases, sellingPrice, exclusive total, total, vat total, invoice total -- skipped

        // check deleted, on_hold, no_vat fields are boolean -- skipped

        // check if document status is valid -- skipped

        // check if pod reason is valid -- skipped

        // skip all date checks
        /*
		if (preg_match(GUI_PHP_DATE_VALIDATION,$postingOrderTO->documentDate,$parts)) {
			if(!checkdate($parts[2],$parts[3],$parts[1])) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Document Date format.";
				return false;
			}
		} else {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Document Date format.";
			return false;
		  }
		  */

        // IF Update, check the document you are updating is "same"
        if ($postingDocumentTO->DMLType == "UPDATE") {
            //check if exists
            $docArr = $transactionDAO->getDocumentParentItem($postingDocumentTO->principalUId, $postingDocumentTO->dmUId);
            if (sizeof($docArr) == 0) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Could not find document for update.";
                return false;
            };
            // check critical fields are same
            if (
                ($postingDocumentTO->principalUId != $docArr[0]["principal_uid"]) ||
                ($postingDocumentTO->principalStoreUId != $docArr[0]["principal_store_uid"]) ||
                ($postingDocumentTO->documentNumber != $docArr[0]["document_number"]) ||
                ($postingDocumentTO->documentTypeUId != (($docArr[0]["document_type_uid"] == DT_ORDINV_ZERO_PRICE) ? DT_ORDINV : $docArr[0]["document_type_uid"])) ||
                ($postingDocumentTO->depotUId != $docArr[0]["depot_uid"])
            ) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Attempted Update of document but critical unique identifiers (fields) differ.{$postingDocumentTO->principalStoreUId}:{$docArr[0]["principal_store_uid"]}-{$postingDocumentTO->depotUId}:{$docArr[0]["depot_uid"]}";
                return false;
            }
        } elseif ($postingDocumentTO->DMLType == "INSERT") {
            $docArr = $importDAO->getDocumentMasterByOtherKey($postingDocumentTO->principalUId, $postingDocumentTO->documentNumber, $postingDocumentTO->documentTypeUId, $postingDocumentTO->depotUId, "");
            if (sizeof($docArr) > 0) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Specified DMLTYPE INSERT but could not insert - document already exists.";
                return false;
            };
        }


        // check if product is valid
        $docDetArr = $transactionDAO->getDocumentDetails($postingDocumentTO->dmUId);
        $lineNo = 0;
        $pageNo = 1;
        foreach ($postingDocumentTO->detailArr as &$row) {

            // Assign the line number if not setup
            $lineNo++;
            if ($row->lineNo == "") {
                if ($lineNo > 10) {
                    $lineNo = 1;
                    $pageNo++;
                }
                $row->lineNo = $pageNo . $lineNo;
            }

            $mfPP = $productDAO->getPrincipalProductItem($postingDocumentTO->principalUId, $row->productUId); // don't worry about checking product status
            if (sizeof($mfPP) == 0) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Principal Product (" . $row->productUId . ") not found.";
                return false;
            };
            // ignore deleted status if coming from backend
            if ($userId != SESSION_ADMIN_USERID) {
                if ($mfPP[0]["status"] == FLAG_STATUS_DELETED) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Principal Product (" . $row->productUId . ") is a deleted product. You must undelete it to be able to use it.";
                    return false;
                };
            }
            // check if line has diff product. dont worry about checking if product exists because could be new addition
            // same line numbers are allowed but must have different product because the key is line+prod
            $found = false;

            echo "<pre>";
            print_r($docDetArr);

            echo "</pre>";


            foreach ($docDetArr as $line) {
                if (($row->lineNo == $line["line_no"]) && ($row->productUId == $line["product_uid"])) {
                    $found = true;
                    break;
                }
            }
            if ((!$found) && (!empty($docDetArr))) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Line number (" . $row->lineNo . ") has a different product already on it in product_detail table uid:" . $postingDocumentTO->dmUId;
                return false;
            };
        }

        return true;

    }

    public function postDocument($postingDocumentTO, $pWebSourceChecksAlreadyDone = false)
    {

        global $ROOT, $PHPFOLDER;

        // documents must be left padded with zeros for backwards compatibility with clipper
        $postingDocumentTO->documentNumber = str_pad(trim($postingDocumentTO->documentNumber), 8, "0", STR_PAD_LEFT);

        $resultOK = $this->postDocumentValidation($postingDocumentTO, $webSourceChecksAlreadyDone = $pWebSourceChecksAlreadyDone);
        $this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time


        if ($resultOK === true) {


            //CREDIT AND DEBIT NOTES GET THEIR OWN UNIQUE NUMBER -> ALT DOCUMENT NUMBER.
            if (in_array($postingDocumentTO->documentTypeUId, array(DT_CREDITNOTE, DT_DEBITNOTE, DT_MCREDIT_DAMAGES, DT_MCREDIT_OTHER, DT_MCREDIT_PRICING, DT_MCREDIT_PROMOTIONS, DT_MDEBIT_NOTE, DT_MCREDIT_STORE))) {
                include_once($ROOT . $PHPFOLDER . "DAO/SequenceDAO.php");
                $sequenceDAO = new SequenceDAO($this->dbConn);
                $postingDocumentTO->alternateDocumentNumber = $sequenceDAO->getAlternateDocumentNumberSequence($postingDocumentTO->documentTypeUId,
                    $postingDocumentTO->principalUId,
                    $postingDocumentTO->depotUId,
                    $postingDocumentTO->dataSource);
                $postingDocumentTO->podReasonUId;
            }

            // override signage for certain doc types
            $negativeSignageApplies = ((in_array($postingDocumentTO->documentTypeUId, array(DT_CREDITNOTE, DT_ARRIVAL_CORRECTION, DT_UPLIFT_CREDIT, DT_MCREDIT_DAMAGES, DT_MCREDIT_OTHER, DT_MCREDIT_PRICING, DT_MCREDIT_PROMOTIONS, DT_MCREDIT_STORE))) ? true : false);

            if ($postingDocumentTO->DMLType == "INSERT") {
                if ($postingDocumentTO->orderSequenceNo == "") $oSN = "NULL"; else $oSN = $postingDocumentTO->orderSequenceNo;
                // Gypo for Brenner PNP Orders

                if (mysqli_real_escape_string($this->dbConn->connection, trim($postingDocumentTO->buyerAccountReference)) == '1000000785' && $postingDocumentTO->principalUId == 4) {
                    $gypodepot = 134;
                } else {
                    $gypodepot = $postingDocumentTO->depotUId;
                }

                $sql = "INSERT INTO document_master
	    				  (`depot_uid`, `principal_uid`, `document_number`, `alternate_document_number`, `document_type_uid`, `additional_type`,
                  `processed_date`, `processed_time`, " .
                    " `incoming_file`,`confirmation_file`, " .
                    "`dop_file`, `rwr_file`,  `invoice_file`, `credit_note_file`,`last_updated`, Transmission_flag_1, Transmission_flag_2,
    					        Transmission_flag_3, Transmission_flag_4, order_sequence_no, file_log_uid, client_document_number, version ) values (" .
                    "'" . $gypodepot . "', " .
                    "'" . $postingDocumentTO->principalUId . "', " .
                    "'" . $postingDocumentTO->documentNumber . "', " .
                    "'" . $postingDocumentTO->alternateDocumentNumber . "', " .
                    "'" . $postingDocumentTO->documentTypeUId . "', " .
                    ((trim($postingDocumentTO->additionalType == "")) ? "NULL" : "'" . $postingDocumentTO->additionalType . "'") . ", " .
                    "'" . $postingDocumentTO->processedDate . "', " .
                    "'" . $postingDocumentTO->processedTime . "', " .
                    "'" . $postingDocumentTO->incomingFile . "', " .
                    "'" . $postingDocumentTO->confirmationFile . "', " .
                    "'" . $postingDocumentTO->dopFile . "', " .
                    "'" . $postingDocumentTO->rwrFile . "', " .
                    "'" . $postingDocumentTO->invoiceFile . "', " .
                    "'" . $postingDocumentTO->creditNoteFile . "', " .
                    "now(), " .
                    "'" . $postingDocumentTO->TransmissionFlag1 . "', " .
                    "'" . $postingDocumentTO->TransmissionFlag2 . "', " .
                    "'" . $postingDocumentTO->TransmissionFlag3 . "', " .
                    "'" . $postingDocumentTO->TransmissionFlag4 . "', " .
                    $oSN . ", " .
                    ((empty($postingDocumentTO->fileLogUId)) ? "NULL" : $postingDocumentTO->fileLogUId) . ", " .
                    "'" . $postingDocumentTO->clientDocumentNumber . "', " .
                    "'{$postingDocumentTO->version}'" .

                    ")";
            } elseif ($postingDocumentTO->DMLType == "UPDATE") {
                if ($postingDocumentTO->orderSequenceNo == "") $oSN = "order_sequence_no"; else $oSN = $postingDocumentTO->orderSequenceNo;
                $sql = "UPDATE document_master
    			  SET  	processed_date         = '" . $postingDocumentTO->processedDate . "',
						processed_time         = '" . $postingDocumentTO->processedTime . "',
						merged_date            = '" . $postingDocumentTO->mergedDate . "',
						merged_time            = '" . $postingDocumentTO->mergedTime . "',
						validation_date        = '" . $postingDocumentTO->validationDate . "',
						validation_time        = '" . $postingDocumentTO->validationTime . "',
						validation_status      = '" . $postingDocumentTO->validationStatus . "',
						incoming_file          = '" . $postingDocumentTO->incomingFile . "',
						confirmation_file      = '" . $postingDocumentTO->confirmationFile . "',
						dop_file               = '" . $postingDocumentTO->dopFile . "',
						rwr_file               = '" . $postingDocumentTO->rwrFile . "',
						invoice_file           = '" . $postingDocumentTO->invoiceFile . "',
						credit_note_file       = '" . $postingDocumentTO->creditNoteFile . "',
						Transmission_flag_1    = '" . $postingDocumentTO->TransmissionFlag1 . "',
						Transmission_flag_2    = '" . $postingDocumentTO->TransmissionFlag2 . "',
						Transmission_flag_3    = '" . $postingDocumentTO->TransmissionFlag3 . "',
						Transmission_flag_4    = '" . $postingDocumentTO->TransmissionFlag4 . "',
						order_sequence_no      = " . $oSN . ",
					    last_updated           = now()
					WHERE uid = '" . $postingDocumentTO->dmUId . "'";
            }

            $this->errorTO = $this->dbConn->processPosting($sql, $postingDocumentTO->documentNumber);

            $dmUId = "";
            if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                if ($postingDocumentTO->DMLType == "INSERT") {

                    $this->errorTO->description = "Document successfully saved";
                    $this->errorTO->identifier = $this->dbConn->dbGetLastInsertId();
                    $dmUId = $this->errorTO->identifier;
                    $postingDocumentTO->dmUId = $dmUId;


                    /*-----------------------------------------*/
                    /*              SMART EVENT                */
                    /*-----------------------------------------*/
                    if (count($postingDocumentTO->notificationArray) > 0) { //set in validation.

                        include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");
                        include_once($ROOT . $PHPFOLDER . 'TO/SmartEventTO.php');

                        $postBIDAO = new PostBIDAO($this->dbConn);

                        foreach ($postingDocumentTO->notificationArray as $nUId) {
                            $smartEventTO = new SmartEventTO();
                            $smartEventTO->type = SE_NOTIFICATION;
                            $smartEventTO->typeUid = $nUId;
                            $smartEventTO->dataUid = $postingDocumentTO->dmUId;
                            $seTO = $postBIDAO->postSmartEvent($smartEventTO);

                            if ($seTO->type != FLAG_ERRORTO_SUCCESS) {
                                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                                $this->errorTO->description = $seTO->description;
                                return false;
                            }
                        }

                    }
                    /*-----------------------------------------*/


                } else {
                    $this->errorTO->identifier = $postingDocumentTO->dmUId;
                    $dmUId = $this->errorTO->identifier;
                }
            } else {
                echo nl2br($sql);
                $this->errorTO->description .= $sql;
                return $this->errorTO;
            }


            // process the header
            $sellingPrice = trim($postingDocumentTO->sellingPrice) == "" ? "NULL" : $postingDocumentTO->sellingPrice;
            $podReasonUId = trim($postingDocumentTO->podReasonUId) == "" ? "NULL" : $postingDocumentTO->podReasonUId;
            $pDD = trim($postingDocumentTO->deliveryDate) == "" ? "0000-00-00" : $postingDocumentTO->deliveryDate;
            $pPODRD = trim($postingDocumentTO->podReturnedDate) == "" ? "0000-00-00" : $postingDocumentTO->podReturnedDate;
            $pRequestedDD = trim($postingDocumentTO->requestedDeliveryDate) == "" ? "0000-00-00" : $postingDocumentTO->requestedDeliveryDate;
            $pDueDD = trim($postingDocumentTO->deliveryDueDate) == "" ? "0000-00-00" : $postingDocumentTO->deliveryDueDate;

            if (trim($postingDocumentTO->invoiceDate) == "") $postingDocumentTO->invoiceDate = "0000-00-00";
            // some eg.AR files (cr & dr) will supply the invoice date
            /* logic changed by Mark 19Nov2014 to support setting the invoiceDate from the Adaptors
        if (in_array($postingDocumentTO->documentTypeUId,array(DT_ORDINV,DT_ORDINV_ZERO_PRICE,DT_DELIVERYNOTE))) {
          $postingDocumentTO->invoiceDate = "0000-00-00";
        } else {
          if ($postingDocumentTO->invoiceDate=="0000-00-00") $postingDocumentTO->invoiceDate = $postingDocumentTO->orderDate;
        }
        */
            if (!in_array($postingDocumentTO->documentTypeUId, array(DT_ORDINV, DT_ORDINV_ZERO_PRICE, DT_DELIVERYNOTE))) {
                if ($postingDocumentTO->invoiceDate == "0000-00-00") $postingDocumentTO->invoiceDate = $postingDocumentTO->orderDate;
            }

            if ($postingDocumentTO->DMLType == "INSERT") {
                $sql = "INSERT INTO document_header
    				  (document_master_uid, order_date, Invoice_Date, requested_delivery_date, delivery_date, pod_returned_date, document_status_uid, `document_service_type_uid`, `overide_rep_code_uid`,principal_store_uid,
                                  depot_principal_store_uid, customer_order_number, invoice_number, cases ,selling_price, exclusive_total, vat_total, invoice_total,
                                  discount_reference,pod_reason_uid,source_document_number,grv_number,claim_number,data_source,captured_by, buyer_account_reference, additional_details, off_invoice_discount , off_invoice_discount_type,
                                  due_delivery_date)
                               VALUES (" .
                    "'" . $postingDocumentTO->dmUId . "', " .
                    "'" . $postingDocumentTO->orderDate . "', " .
                    "'" . $postingDocumentTO->invoiceDate . "', " .
                    "'" . $pRequestedDD . "', " .
                    "'" . $pDD . "', " .
                    "'" . $pPODRD . "', " .
                    "'" . $postingDocumentTO->documentStatusUId . "', " .
                    "'" . (($postingDocumentTO->documentServiceTypeUId == "") ? "0" : $postingDocumentTO->documentServiceTypeUId) . "', " .
                    "'" . (($postingDocumentTO->documentRepCodeUid == "") ? "0" : $postingDocumentTO->documentRepCodeUid) . "', " .
                    "'" . $postingDocumentTO->principalStoreUId . "', " .
                    (($postingDocumentTO->depotPrincipalStoreUId == "") ? "NULL" : $postingDocumentTO->depotPrincipalStoreUId) . ", " .
                    "'" . addSlashes($postingDocumentTO->customerOrderNumber) . "', " .
                    "'" . $postingDocumentTO->invoiceNumber . "', " .
                    "'" . (($negativeSignageApplies) ? abs($postingDocumentTO->cases) * -1 : $postingDocumentTO->cases) . "', " .
                    $sellingPrice . ", " .
                    "'" . (($negativeSignageApplies) ? abs($postingDocumentTO->exclusiveTotal) * -1 : $postingDocumentTO->exclusiveTotal) . "', " .
                    "'" . (($negativeSignageApplies) ? abs($postingDocumentTO->vatTotal) * -1 : $postingDocumentTO->vatTotal) . "', " .
                    "'" . (($negativeSignageApplies) ? abs($postingDocumentTO->invoiceTotal) * -1 : $postingDocumentTO->invoiceTotal) . "', " .
                    "'" . addSlashes($postingDocumentTO->discountReference) . "', " .
                    $podReasonUId . ", " .
                    "'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingDocumentTO->sourceDocumentNumber)) . "', " .
                    "'" . addSlashes($postingDocumentTO->grvNumber) . "', " .
                    "'" . mysqli_real_escape_string($this->dbConn->connection, $postingDocumentTO->claimNumber) . "', " .
                    "'" . mysqli_real_escape_string($this->dbConn->connection, $postingDocumentTO->dataSource) . "', " .
                    "'" . mysqli_real_escape_string($this->dbConn->connection, $postingDocumentTO->capturedBy) . "', " .
                    "'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingDocumentTO->buyerAccountReference)) . "', " .
                    "'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingDocumentTO->additionalDetails)) . "',
                                        " . mysqli_real_escape_string($this->dbConn->connection, trim($postingDocumentTO->offInvoiceDiscount)) . ",
                                       '" . mysqli_real_escape_string($this->dbConn->connection, trim($postingDocumentTO->offInvoiceDiscountType)) . "',
                                       '" . $pDueDD . "')";

            } elseif ($postingDocumentTO->DMLType == "UPDATE") {
                $sql = "UPDATE document_header
						SET 	order_date = '" . $postingDocumentTO->orderDate . "',
									invoice_date = '" . $postingDocumentTO->invoiceDate . "',
									delivery_date = '" . $postingDocumentTO->deliveryDate . "',
									pod_returned_date = '" . $postingDocumentTO->podReturnedDate . "',
									document_status_uid = '" . $postingDocumentTO->documentStatusUId . "',
									principal_store_uid = '" . $postingDocumentTO->principalStoreUId . "',
									customer_order_number = '" . addSlashes($postingDocumentTO->customerOrderNumber) . "',
									invoice_number = '" . $postingDocumentTO->invoiceNumber . "',
									cases = '" . (($negativeSignageApplies) ? abs($postingDocumentTO->cases) * -1 : $postingDocumentTO->cases) . "',
									selling_price = $sellingPrice,
									exclusive_total = '" . (($negativeSignageApplies) ? abs($postingDocumentTO->exclusiveTotal) * -1 : $postingDocumentTO->exclusiveTotal) . "',
									vat_total = '" . (($negativeSignageApplies) ? abs($postingDocumentTO->vatTotal) * -1 : $postingDocumentTO->vatTotal) . "',
									invoice_total = '" . (($negativeSignageApplies) ? abs($postingDocumentTO->invoiceTotal) * -1 : $postingDocumentTO->invoiceTotal) . "',
									discount_reference = '" . addSlashes($postingDocumentTO->discountReference) . "',
									pod_reason_uid = " . $postingDocumentTO->podReasonUId . ",
									source_document_number = '" . $postingDocumentTO->sourceDocumentNumber . "',
									grv_number = '" . addSlashes($postingDocumentTO->grvNumber) . "',
									claim_number = '" . $postingDocumentTO->claimNumber . "',
									data_source = '" . $postingDocumentTO->dataSource . "',
									captured_by = '" . $postingDocumentTO->capturedBy . "'
						WHERE document_master_uid = '" . $dmUId . "'";
            }

            $this->errorTO = $this->dbConn->processPosting($sql, $postingDocumentTO->documentNumber);

            if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                if ($postingDocumentTO->DMLType == "INSERT") {
                    $postingDocumentTO->dhUId = $this->dbConn->dbGetLastInsertId();
                } else {
                    $this->dbConn->dbQuery("select distinct uid from document_header where document_master_uid = '" . $dmUId . "'");
                    if (!$this->dbConn->dbQueryResult) {
                        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                        $this->errorTO->description = "Could not Lookup Document Header UID during update postDocument";
                        return $this->errorTO;
                    }
                    $row = mysql_fetch_array($this->dbConn->dbQueryResult);
                    $postingDocumentTO->dhUId = $row["uid"];
                }
                $this->errorTO->description = "Document successfully saved";
            } else {
                $this->errorTO->description .= $sql;
                return $this->errorTO;
            }


            // process the detail
            foreach ($postingDocumentTO->detailArr as $row) {
                // check if insert or update, for example dmltype may be update, but there may now be an additional product
                $this->dbConn->dbQuery("select uid from document_detail
    									WHERE document_master_uid = '" . $dmUId . "'
										AND   line_no = '" . $row->lineNo . "'
										AND   product_uid = '" . $row->productUId . "' ");
                $type = "";
                $pallets = ($row->pallets == "") ? "NULL" : $row->pallets;
                if ($this->dbConn->dbQueryResultRows == 0) {

                    //CHANGE BY: ONYX, DATE: 2013.02.12 - NEW ORDERS INV AND DEL QTYS ARE SET TO ZERO AND ONLY UPDATED WHEN INVOICED FOR REPORTING.
                    if (
                        (in_array($postingDocumentTO->documentTypeUId, array(DT_ORDINV, DT_DELIVERYNOTE, DT_ORDINV_ZERO_PRICE))) &&
                        (in_array($postingDocumentTO->documentStatusUId, array(DST_ACCEPTED, DST_UNACCEPTED)))
                    ) {
                        $row->documentQty = 0;
                        $row->deliveredQty = 0;
                    }

                    //$ddTO->oldQuantity = $TO->oldQuantity;
                    //                        $ddTO->oldPrice

                    $type = "INSERT";
                    $sql = "INSERT INTO document_detail
	    				  (
                    document_master_uid, line_no, client_line_no, product_uid, ordered_qty, document_qty,
                    delivered_qty, selling_price, discount_value, discount_reference, net_price,
                    extended_price, vat_amount, vat_rate, total, pallets, pod_reason_uid, product_code,
                    ws_unique_creator_id, additional_type, user_modified,  old_quantity, old_price, comment
                  )
                  VALUES
                  (" .
                        "'" . $dmUId . "', " .
                        "'" . $row->lineNo . "', " .
                        "'" . $row->clientLineNo . "', " .
                        "'" . $row->productUId . "', " .
                        "'" . (($negativeSignageApplies) ? abs($row->orderedQty) * -1 : $row->orderedQty) . "', " .
                        "'" . (($negativeSignageApplies) ? abs($row->documentQty) * -1 : $row->documentQty) . "', " .
                        "'" . (($negativeSignageApplies) ? abs($row->deliveredQty) * -1 : $row->deliveredQty) . "', " .
                        (($row->sellingPrice == "") ? "0" : $row->sellingPrice) . ", " .
                        (($row->discountValue == "") ? "0" : $row->discountValue) . ", " .
                        "'" . addSlashes(substr($row->discountReference, 0, 20)) . "', " .
                        (($row->netPrice == "") ? "0" : $row->netPrice) . ", " .
                        (($row->extendedPrice == "") ? "0" : (($negativeSignageApplies) ? abs($row->extendedPrice) * -1 : $row->extendedPrice)) . ", " .
                        (($row->vatAmount == "") ? "0" : (($negativeSignageApplies) ? abs($row->vatAmount) * -1 : $row->vatAmount)) . ", " .
                        (($row->vatRate == "") ? "0" : $row->vatRate) . ", " .
                        (($row->total == "") ? "0" : (($negativeSignageApplies) ? abs($row->total) * -1 : $row->total)) . ", " .
                        $pallets . ", " .
                        (($row->podReasonUId == "") ? "NULL" : $row->podReasonUId) . ", " .
                        "'" . addSlashes($row->productCode) . "', " .
                        "'{$row->wsUniqueCreatorId}', " .
                        "'{$row->additionalType}', " .
                        "'{$row->userModified}', " .
                        "" . (($row->oldQuantity == "") ? "NULL" : "'" . $row->oldQuantity . "'") . ", " .
                        (($row->oldPrice == "") ? "NULL" : $row->oldPrice) . ", " .
                        "'" . substr($row->comment, 0, 60) . "'" .
                        ")";

                } else {
                    $type = "UPDATE";
                    $sql = "UPDATE document_detail
	    			  SET	ordered_qty = '" . (($negativeSignageApplies) ? abs($row->orderedQty) * -1 : $row->orderedQty) . "',
							document_qty = '" . (($negativeSignageApplies) ? abs($row->documentQty) * -1 : $row->documentQty) . "',
							delivered_qty = '" . (($negativeSignageApplies) ? abs($row->deliveredQty) * -1 : $row->deliveredQty) . "',
							selling_price = '" . $row->sellingPrice . "',
							discount_value = '" . $row->discountValue . "',
							discount_reference = '" . addSlashes($row->discountReference) . "',
							net_price = '" . $row->netPrice . "',
							extended_price = '" . (($negativeSignageApplies) ? abs($row->extendedPrice) * -1 : $row->extendedPrice) . "',
							vat_amount = '" . (($negativeSignageApplies) ? abs($row->vatAmount) * -1 : $row->vatAmount) . "',
							vat_rate = '" . $row->vatRate . "',
							total = '" . (($negativeSignageApplies) ? abs($row->total) * -1 : $row->total) . "',
							pallets = " . $pallets . ",
							pod_reason_uid = " . (($row->podReasonUId == "") ? "NULL" : $row->podReasonUId) . "
						WHERE document_master_uid = '" . $dmUId . "'
						AND   line_no = '" . $row->lineNo . "'
						AND   product_uid = '" . $row->productUId . "' ";
                }

                $this->errorTO = $this->dbConn->processPosting($sql, $postingDocumentTO->documentNumber);

                if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                    if ($type == "INSERT") {
                        $row->ddUId = $this->dbConn->dbGetLastInsertId();
                    } else {
                        $this->dbConn->dbQuery("select distinct uid from document_detail
													WHERE document_master_uid = '" . $dmUId . "'
													AND   line_no = '" . $row->lineNo . "'
													AND   product_uid = '" . $row->productUId . "' ");
                        if (!$this->dbConn->dbQueryResult) {
                            $this->errorTO->type = FLAG_ERRORTO_ERROR;
                            $this->errorTO->description = "Could not Lookup Document detail UID during update postDocument";
                            return $this->errorTO;
                        }
                        $row2 = mysql_fetch_array($this->dbConn->dbQueryResult);
                        $row->ddUId = $row2["uid"];
                    }
                    $this->errorTO->description = "Document successfully saved";
                } else {
                    $this->errorTO->description .= $sql;
                    return $this->errorTO;
                }
            }


        } else {
            return $this->errorTO;
        }

        return $this->errorTO;
    }


    //simple document update from backend
    //*** NO VALIDATION ***
    public function postDocumentConfirmUpdate($postingDocumentConfirmTO)
    {

        // documents must be left padded with zeros for backwards compatibility with clipper
        $postingDocumentConfirmTO->documentNumber = str_pad(trim($postingDocumentConfirmTO->documentNumber), 8, "0", STR_PAD_LEFT);

        $podReasonUId = trim($postingDocumentConfirmTO->podReasonUId) == "" ? "NULL" : $postingDocumentConfirmTO->podReasonUId;

        $this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

        //DOC MAST & DOC HEAD => SINGLE UPDATE
        $sql = "UPDATE document_master m
			  LEFT JOIN document_header h on m.uid = h.document_master_uid
    		  SET

    		  	m.merged_date            = '" . $postingDocumentConfirmTO->mergedDate . "',
    			m.merged_time            = '" . $postingDocumentConfirmTO->mergedTime . "',
    			m.validation_date        = '" . $postingDocumentConfirmTO->validationDate . "',
    			m.validation_time        = '" . $postingDocumentConfirmTO->validationTime . "',
    			m.validation_status      = '" . $postingDocumentConfirmTO->validationStatus . "',
    			m.dop_file               = '" . $postingDocumentConfirmTO->dopFile . "',
    			m.rwr_file               = '" . $postingDocumentConfirmTO->rwrFile . "',
    			m.invoice_file           = '" . $postingDocumentConfirmTO->invoiceFile . "',
    			m.credit_note_file       = '" . $postingDocumentConfirmTO->creditNoteFile . "',
    		    m.last_updated           = NOW(),

          		h.invoice_date = '" . $postingDocumentConfirmTO->invoiceDate . "',
          		h.delivery_date = '" . $postingDocumentConfirmTO->deliveryDate . "',
          		h.pod_returned_date = '" . $postingDocumentConfirmTO->podReturnedDate . "',
          		h.document_status_uid = '" . $postingDocumentConfirmTO->documentStatusUId . "',
          		h.invoice_number = '" . $postingDocumentConfirmTO->invoiceNumber . "',
          		h.cases = '" . $postingDocumentConfirmTO->cases . "',
          		h.exclusive_total = '" . $postingDocumentConfirmTO->exclusiveTotal . "',
          		h.vat_total = '" . $postingDocumentConfirmTO->vatTotal . "',
          		h.invoice_total = '" . $postingDocumentConfirmTO->invoiceTotal . "',
          		h.pod_reason_uid = " . $podReasonUId . ",
          		h.source_document_number = '" . $postingDocumentConfirmTO->sourceDocumentNumber . "',
          		h.grv_number = '" . mysqli_real_escape_string($this->dbConn->connection, $postingDocumentConfirmTO->grvNumber) . "',
          		h.claim_number = '" . $postingDocumentConfirmTO->claimNumber . "'

			WHERE m.uid = '" . $postingDocumentConfirmTO->dmUId . "'";


        $this->errorTO = $this->dbConn->processPosting($sql, $postingDocumentConfirmTO->documentNumber);


        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Document Master and Header Successfully Updated";
        } else {
            $this->errorTO->description .= $sql;
            return $this->errorTO;
        }


        //update detail rows.
        //update by doc mast uid + product uid + line no to match.
        foreach ($postingDocumentConfirmTO->detailArr as $row) {

            $sql = "UPDATE document_detail
              		SET
                    	document_qty = '" . $row->documentQty . "',
                    	delivered_qty = '" . $row->deliveredQty . "',
                    	extended_price = '" . $row->extendedPrice . "',
                    	vat_amount = '" . $row->vatAmount . "',
                    	total = '" . $row->total . "',
                    	pod_reason_uid = " . (($row->podReasonUId == "") ? "NULL" : $row->podReasonUId) . "
                    WHERE document_master_uid = '" . $postingDocumentConfirmTO->dmUId . "'
                    AND   line_no = '" . $row->lineNo . "'
                    AND   product_uid = '" . $row->productUId . "' ";

            $this->errorTO = $this->dbConn->processPosting($sql, $postingDocumentConfirmTO->documentNumber);


            if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->description = "Document successfully saved";
            } else {
                $this->errorTO->description .= $sql;
                return $this->errorTO;
            }

        }  //eo-loop

        return $this->errorTO;
    }

    // the documents created bi trigger calles this
    public function setOrdersHoldingExceptionNotified($ohUIDList)
    {
        $sql = "UPDATE orders_holding oh,
					 orders_holding_detail ohd
			  SET oh.exception_notified='" . FLAG_ERRORTO_SUCCESS . "',
				  ohd.exception_notified='" . FLAG_ERRORTO_SUCCESS . "'
			  WHERE oh.uid in ({$ohUIDList})
			  AND   oh.uid = ohd.orders_holding_uid";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    // the documents created bi trigger calles this
    public function setOrdersHoldingCancelledOrdersNotified($ohUIDList)
    {
        $sql = "UPDATE orders_holding
			  SET cancelled_order_notified='" . FLAG_ERRORTO_SUCCESS . "'
			  WHERE uid in ({$ohUIDList})";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    // the documents created bi trigger calles this
    public function setOrdersHoldingDetailPriceDiffNotified($ohdUIDList)
    {
        $sql = "UPDATE orders_holding_detail
			  SET price_diff_notified='" . FLAG_ERRORTO_SUCCESS . "'
			  WHERE uid in ({$ohdUIDList})";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    /*
     *
     * START : direct updates by user from orders holding exception management screen
     *
     * WARNING : These use a sql user VAR @userId
     *
     */

    // the extra join on principal is done because this is called from the user screen for security
    public function setOrdersHoldingDetailDeleted($ohdUID, $principalUId)
    {
        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $this->dbConn->dbQuery("SET @userId={$userId}");

        $sql = "UPDATE orders_holding_detail ohd,
					 orders_holding oh
			  SET ohd.status='" . FLAG_STATUS_DELETED . "',
				    ohd.user_action_status=concat_ws(',',oh.user_action_status,'" . FLAG_STATUS_DELETED . "'),
						ohd.deleted_by = '{$userId}'
			  WHERE ohd.uid = '{$ohdUID}'
			  and   ohd.orders_holding_uid = oh.uid
			  and   oh.principal_uid = '{$principalUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding Detail successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    // the extra join on principal is done because this is called from the user screen for security
    public function setOrdersHoldingDeleted($ohUID, $principalUId)
    {
        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $this->dbConn->dbQuery("SET @userId={$userId}");

        $sql = "UPDATE orders_holding oh
			  SET oh.status='" . FLAG_STATUS_DELETED . "',
				    oh.user_action_status=concat_ws(',',oh.user_action_status,'" . FLAG_STATUS_DELETED . "'),
						oh.deleted_by = '{$userId}'
			  WHERE oh.uid = '{$ohUID}'
			  and   oh.principal_uid = '{$principalUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setOrdersHoldingStore($ohUID, $principalUId, $psmUId)
    {
        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $this->dbConn->dbQuery("SET @userId={$userId}");

        $sql = "UPDATE orders_holding oh
			  SET oh.principal_store_uid=" . (($psmUId == "") ? "null" : $psmUId) . ",
				  oh.user_action_status=concat_ws(',',oh.user_action_status,'S')
			  WHERE oh.uid = '{$ohUID}'
			  and   oh.principal_uid = '{$principalUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setOrdersHoldingForceUniqueFlag($ohUID, $principalUId, $flag)
    {
        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $this->dbConn->dbQuery("SET @userId={$userId}");

        $sql = "UPDATE orders_holding oh
    			  SET oh.force_skip_unique_order_no='" . $flag . "',
    				  oh.user_action_status=concat_ws(',',oh.user_action_status,'UFD')
    			  WHERE oh.uid = '{$ohUID}'
    			  and   oh.principal_uid = '{$principalUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setOrdersHoldingDepot($ohUID, $principalUId, $depotUId)
    {
        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $this->dbConn->dbQuery("SET @userId={$userId}");

        $sql = "UPDATE orders_holding oh
      			  SET oh.depot_uid='" . $depotUId . "',
          			  oh.user_action_status=concat_ws(',',oh.user_action_status,'UFD'),
          			  oh.user_modified_depot='Y',
          			  oh.update_store_depot='N',
          			  oh.enforce_same_depot='N'
    			  WHERE oh.uid = '{$ohUID}'
            and   oh.principal_uid = '{$principalUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setOrdersHoldingOrderDate($ohUID, $principalUId, $date)
    {

        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $this->dbConn->dbQuery("SET @userId={$userId}");

        $sql = "UPDATE orders_holding oh
            SET oh.order_date='{$date}',
                oh.user_action_status=concat_ws(',',oh.user_action_status,'SOD')
            WHERE oh.uid = '{$ohUID}'
            and   oh.principal_uid = '{$principalUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setOrdersHoldingReference($ohUID, $principalUId, $reference)
    {

        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $this->dbConn->dbQuery("SET @userId={$userId}");

        $sql = "UPDATE orders_holding oh
            SET oh.reference='" . mysqli_real_escape_string($this->dbConn->connection, $reference) . "',
            oh.user_action_status=concat_ws(',',oh.user_action_status,'UR')
            WHERE oh.uid = '{$ohUID}'
            and   oh.principal_uid = '{$principalUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setOrdersHoldingApprove($ohUID, $principalUId)
    {
        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $this->dbConn->dbQuery("SET @userId={$userId}");

        $sql = "UPDATE orders_holding oh
          SET oh.status='',
            oh.user_action_status=concat_ws(',',oh.user_action_status,'UAD')
          WHERE oh.uid = '{$ohUID}'
          and   oh.principal_uid = '{$principalUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setOrdersHoldingProduct($ohdUID, $principalUId, $ppUId)
    {
        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $this->dbConn->dbQuery("SET @userId={$userId}");

        $sql = "UPDATE orders_holding_detail ohd,
					 orders_holding oh
			  SET ohd.principal_product_uid=" . (($ppUId == "") ? "null" : $ppUId) . ",
				  ohd.user_action_status=concat_ws(',',ohd.user_action_status,'P')
			  WHERE ohd.uid = '{$ohdUID}'
			  and   ohd.orders_holding_uid = oh.uid
			  and   oh.principal_uid = '{$principalUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding Detail successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setOrdersHoldingAmendedQuantity($ohdUID, $principalUId, $amendedQuantity)
    {
        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $this->dbConn->dbQuery("SET @userId={$userId}");

        $sql = "UPDATE orders_holding_detail ohd,
					         orders_holding oh
      			  SET ohd.amended_quantity=" . $amendedQuantity . ",
          			  ohd.user_action_status=concat_ws(',',ohd.user_action_status,'AQ'),
          			  ohd.user_modified='Y'
          	WHERE ohd.uid = '{$ohdUID}'
            and   ohd.orders_holding_uid = oh.uid
            and   oh.principal_uid = '{$principalUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding Detail successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setOrdersHoldingOverridePriceType($ohdUID, $principalUId, $priceType)
    {
        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $this->dbConn->dbQuery("SET @userId={$userId}");

        $sql = "UPDATE orders_holding_detail ohd,
					 orders_holding oh
			  SET ohd.override_price_type={$priceType},
				  ohd.user_action_status=concat_ws(',',ohd.user_action_status,'OPT')
			  WHERE ohd.uid = '{$ohdUID}'
			  and   ohd.orders_holding_uid = oh.uid
			  and   oh.principal_uid = '{$principalUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding Detail successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    /*
     *
     * END : direct updates by user from orders holding exception management screen
     *
     */

    // the documents created bi trigger calles this
    public function setOrdersHoldingEDIFileDefNotified($ohUIDList)
    {
        $sql = "UPDATE orders_holding
			  SET edifiledef_notified='" . FLAG_ERRORTO_SUCCESS . "'
			  WHERE uid in ({$ohUIDList})";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Orders Holding successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setDocumentStatusValidation($postingDocumentStatusTO)
    {

        global $ROOT, $PHPFOLDER;
        include_once($ROOT . $PHPFOLDER . "DAO/TransactionDAO.php");
        include_once($ROOT . $PHPFOLDER . "DAO/StockDAO.php");

        if (!isset($_SESSION)) session_start;
        $principalId = $_SESSION['principal_id'];
        $postingDocumentStatusTO->userId = $_SESSION['user_id'];


        $transactionDAO = new TransactionDAO($this->dbConn);
        $biDAO = new BIDAO($this->dbConn);


        /* -------------------------------------- *
         *   Document Confirmation/Notifications  *
         * -------------------------------------- */

        //simple request for notication document settings
        $mfT = $transactionDAO->getSimpleDocumentByDMUId($postingDocumentStatusTO->documentMasterUId);
        if (count($mfT) == 0) { //safe guard
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Order does not exist.";
            return false;
        }

        $mfNDI = $biDAO->getNotificationDocumentConfirmation($mfT[0]['principal_uid'], $mfT[0]['depot_uid'], $mfT[0]['document_type_uid'], $mfT[0]['data_source'], $mfT[0]['captured_by'], $postingDocumentStatusTO->documentStatusUId);
        if (sizeof($mfNDI) > 0) {

            include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");
            include_once($ROOT . $PHPFOLDER . 'TO/SmartEventTO.php');

            $postBIDAO = new PostBIDAO($this->dbConn);
            foreach ($mfNDI as $mf) {
                $smartEventTO = new SmartEventTO();
                $smartEventTO->type = SE_NOTIFICATION;
                $smartEventTO->typeUid = $mf['uid'];
                $smartEventTO->dataUid = $postingDocumentStatusTO->documentMasterUId;
                $seTO = $postBIDAO->postSmartEvent($smartEventTO);

                //treat this as a failure.
                if ($seTO->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("Error in PostTransactionDAO", "error creating notification via smart events in setDocumentStatusValidation : " . $seTO->description, "N", true);
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Error occureed when creating notification via smart events in setDocumentStatusValidation : " . $seTO->description;
                    return false;
                }
            }

        }
        /* -------------------------------------- */


        if ($postingDocumentStatusTO->skipValidation == "Y") {


            return true;  //bypass validation.


        } else {

            $mfT = $transactionDAO->getDepotDocumentItem($postingDocumentStatusTO->userId, $postingDocumentStatusTO->documentMasterUId); // this also checks if user has access to principal
            if (sizeof($mfT) == 0) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "You do not have access to this information, or order does not exist.";
                return false;
            }

            //stock take mode enabled?
            $stockDAO = new StockDAO($this->dbConn);
            if ($stockDAO->checkStockMode($mfT[0]['principal_uid'], $mfT[0]['depot_uid'])) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = 'Stock take mode is enabled for this depot-principal, please try again later!';
                return false;
            }


            if (!in_array($mfT[0]["document_type_uid"], array(DT_ORDINV, DT_ORDINV_ZERO_PRICE, DT_DELIVERYNOTE, DT_UPLIFTS, DT_DESTRUCTION_DISPOSAL, DT_QUOTATION, DT_WALKIN_INVOICE))) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Only Orders can be managed by depots";
                return false;
            }

            if ($postingDocumentStatusTO->documentStatusUId == DST_ACCEPTED) {
                if (!in_array($mfT[0]["document_status_uid"], explode(",", $transactionDAO->getUnacceptedOrderStatuses() . ',' . DST_INPICK))) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Documents can only be accepted from an unaccepted status";
                    return false;
                }
            } else if ($postingDocumentStatusTO->documentStatusUId == DST_UNACCEPTED) {
                if (!in_array($mfT[0]["document_status_uid"], array(DST_ACCEPTED))) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Documents can only be Unaccepted from Accepted status.";
                    return false;
                }
            } else if ($postingDocumentStatusTO->documentStatusUId == DST_CANCELLED) {
                if (!in_array($mfT[0]["document_status_uid"], explode(",", $transactionDAO->getUnacceptedOrderStatuses() . "," . DST_INPICK . "," . DST_ACCEPTED . "," . DST_INVOICED))) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Documents can only be cancelled from an unaccepted, accepted, inpick or invoiced status";
                    return false;
                }
            } else if ($postingDocumentStatusTO->documentStatusUId == DST_INPICK) {
                if (!in_array($mfT[0]["document_status_uid"], array(DST_ACCEPTED, DST_INVOICED))) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Documents can only be picked from Accepted or Invoiced status.";
                    return false;
                }
            } else if ($postingDocumentStatusTO->documentStatusUId == DST_INVOICED) {
                if (
                    ($mfT[0]["document_status_uid"] == DST_INPICK) ||
                    (($mfT[0]["document_status_uid"] == DST_ACCEPTED) && ($mfT[0]["skip_inpick_stage"] == "Y"))
                ) {
                    // no problem
                } else {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Documents can only be invoiced from InPick status (or accepted if your depot does not use the inpick status.";
                    return false;
                }
            } else if ($postingDocumentStatusTO->documentStatusUId == DST_WAITING_DISPATCH) {
                if (
                    ($mfT[0]["document_status_uid"] == DST_INPICK) ||
                    (($mfT[0]["document_status_uid"] == DST_ACCEPTED) && ($mfT[0]["skip_inpick_stage"] == "Y"))
                ) {
                    // no problem
                } else {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Documents can only be Waiting Dispatch from InPick status (or accepted if your depot does not use the inpick status.";
                    return false;
                }
            } else if ($postingDocumentStatusTO->documentStatusUId == DST_DELIVERED_POD_OK) {
                if ($mfT[0]["document_status_uid"] != DST_INVOICED) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Documents can only be debriefed from Invoiced status.";
                    return false;
                }
            } else if ($postingDocumentStatusTO->documentStatusUId == DST_DIRTY_POD) {
                if ($mfT[0]["document_status_uid"] != DST_INVOICED) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Documents can only be debriefed from Invoiced status.";
                    return false;
                }
            } else {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Invalid To-Status passed";
                return false;
            }

            return true;
        }
    }

    public function setCorrectionUpdated($dmUId, $podReasonUId, $additionalType)
    {

        $sql = "UPDATE document_master a,
                 document_header b
    			SET  a.additional_type = '{$additionalType}',
    			     b.pod_reason_uid  = '{$podReasonUId}'
    			WHERE a.uid = '{$dmUId}'
    			AND   a.uid = b.document_master_uid
    			AND   a.document_type_uid = " . DT_CREDITNOTE;

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "setCorrectionUpdated failed : " . $this->errorTO->description;
            return $this->errorTO;
        }

        return $this->errorTO;

    }

    public function setDocumentStatus($postingDocumentStatusTO)
    {

        global $ROOT, $PHPFOLDER;
        $this->dbConn->dbQuery("SET time_zone='+0:00'");

        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];

        if (!isset($_SESSION)) session_start();
        $principalId = $_SESSION['principal_id'];

        include_once($ROOT . $PHPFOLDER . "DAO/AdministrationDAO.php");
        $adminDAO = new AdministrationDAO($this->dbConn);
        $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_MANAGE_QUOTATION);
// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
        if (CommonUtils::isDepotUser()) {

            $resultOK = $this->setDocumentStatusValidation($postingDocumentStatusTO);

            if ($resultOK === true) {

                /********************************************/
                /*               INVOICE SEQ
            /********************************************/
                $invoiceSQL = "";
                if ($postingDocumentStatusTO->documentStatusUId == DST_INVOICED) {

                    include_once($ROOT . $PHPFOLDER . "TO/SequenceTO.php");
                    include_once($ROOT . $PHPFOLDER . "DAO/SequenceDAO.php");
                    include_once($ROOT . $PHPFOLDER . "DAO/TransactionDAO.php");

                    //need some info from order for the seq lookup.
                    $tranDAO = new TransactionDAO($this->dbConn);
                    $docArr = $tranDAO->getSimpleDocumentByDMUId($postingDocumentStatusTO->documentMasterUId);

                    if (count($docArr) == 0) {
                        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                        $this->errorTO->description = "Error locating document!";   //this should not happen!
                        return $this->errorTO;
                    }

                    $getSequenceResult = false; //preset
                    $sequenceTO = new SequenceTO();
                    $sequenceTO->sequenceKey = "INVNUM"; //this really should NOT be used anywhere else!!!
                    $sequenceTO->sequenceStart = 0;
                    $sequenceTO->sequenceLen = 6;
                    $sequenceTO->principalUId = $docArr[0]['principal_uid'];
                    $sequenceTO->depotUId = $docArr[0]['depot_uid'];
                    $sequenceTO->documentTypeUId = $docArr[0]['document_type_uid'];
                    $sequenceDAO = new SequenceDAO($this->dbConn);
                    $seqResult = $sequenceDAO->getSequence($sequenceTO, $getSequenceResult);
// Change for stock count day invoice date

// file_put_contents($ROOT.$PHPFOLDER.'log/debug0507.txt', print_r($postingDocumentStatusTO, TRUE), FILE_APPEND);

                    if ($getSequenceResult === false) {
                        $mthendDepots = array('179', '392', '393', '400');
                        if (in_array($docArr[0]['depot_uid'], $mthendDepots) && date("Y-m-d") >= '2023-08-31' && date("Y-m-d") <= '2023-08-31') {
                            $invoiceSQL = "  invoice_number=(select distinct document_number from document_master where uid='{$postingDocumentStatusTO->documentMasterUId}'),
                                      invoice_date='2023-09-01' ";
                                      
                        } else {
                            $invoiceSQL = "  invoice_number=(select distinct document_number from document_master where uid='{$postingDocumentStatusTO->documentMasterUId}'),
                                      invoice_date= '" . $postingDocumentStatusTO->overideInvDate . "'";
                        }
                    } else {
                        $invoiceSQL = "  invoice_number='{$getSequenceResult}',
                                      invoice_date= '" . $postingDocumentStatusTO->overideInvDate . "'";
                    }

                }
                /********************************************/


                $SET = [];

                if (($postingDocumentStatusTO->documentStatusUId != DST_DELIVERED_POD_OK) &&
                    ($postingDocumentStatusTO->documentStatusUId != DST_DIRTY_POD)) {
                    $SET[] = " invoice_date = '" . (date("Y-m-d")) . "' ";
                }

                $SET[] = " document_status_uid='{$postingDocumentStatusTO->documentStatusUId}' ";

                $SET[] = " tracking_number='$postingDocumentStatusTO->trackingnumber'";

                if (trim($invoiceSQL) != "") $SET[] = $invoiceSQL;


                if (($postingDocumentStatusTO->documentStatusUId = DST_INVOICED) && ($postingDocumentStatusTO->repcode > 0)) {
                    $updcmd = (implode(",", $SET)) . " ,oh.overide_rep_code_uid = $postingDocumentStatusTO->repcode";
                } else {
                    $updcmd = (implode(",", $SET));
                }

                $sql = "UPDATE document_header oh
      		      SET " . $updcmd . "
          			WHERE document_master_uid = '{$postingDocumentStatusTO->documentMasterUId}'";

                $this->errorTO = $this->dbConn->processPosting($sql, "");


                if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->description = "setDocumentStatus failed : " . $this->errorTO->description;
                    return $this->errorTO;
                }


                $comment = mysqli_real_escape_string($this->dbConn->connection, $postingDocumentStatusTO->comment);
                $this->errorTO = $this->postDepotAuditLog($postingDocumentStatusTO->documentMasterUId, $postingDocumentStatusTO->userId, $comment, $postingDocumentStatusTO->documentStatusUId);

                if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->description = "setDocumentStatus failed : " . $this->errorTO->description;
                    return $this->errorTO;
                }

                if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->description = "setDocumentStatus successfully set";
                    $this->errorTO->identifier = GUICommonUtils::translateDocumentStatusType($postingDocumentStatusTO->documentStatusUId);
                }

            } else {

                return $this->errorTO;

            }
            return $this->errorTO;


        } else {

//    file_put_contents('postingDocumentStatusTO.txt', print_r($postingDocumentStatusTO, TRUE), FILE_APPEND);
            $resultOK = $this->setDocumentStatusValidation($postingDocumentStatusTO);

            if ($resultOK === true) {

                $set = array();
                if ($postingDocumentStatusTO->documentStatusUId !== false) $set[] = " document_status_uid='{$postingDocumentStatusTO->documentStatusUId}'";
                if ($postingDocumentStatusTO->buyerDocumentStatusUId !== false) $set[] = " buyer_document_status_uid='{$postingDocumentStatusTO->buyerDocumentStatusUId}'";
                if ($postingDocumentStatusTO->podDocumentMasterUId !== false) $set[] = " pod_document_master_uid='{$postingDocumentStatusTO->podDocumentMasterUId}'";

                if ($postingDocumentStatusTO->documentTypeUId == DT_QUOTATION) {
                    include_once($ROOT . $PHPFOLDER . "TO/SequenceTO.php");
                    include_once($ROOT . $PHPFOLDER . "DAO/SequenceDAO.php");

                    $getSequenceResult = false; //preset
                    $sequenceTO = new SequenceTO();
                    $sequenceTO->sequenceKey = "INVNUM"; //this really should NOT be used anywhere else!!!
                    $sequenceTO->sequenceStart = 0;
                    $sequenceTO->sequenceLen = 6;
                    $sequenceTO->principalUId = $postingDocumentStatusTO->principalUId;
                    $sequenceTO->depotUId = $postingDocumentStatusTO->depotUId;
                    $sequenceTO->documentTypeUId = $postingDocumentStatusTO->documentTypeUId;

                    $sequenceDAO = new SequenceDAO($this->dbConn);
                    $seqResult = $sequenceDAO->getSequence($sequenceTO, $getSequenceResult);

                    $set[] = " invoice_number='" . (($getSequenceResult === false) ? $postingDocumentStatusTO->documentNumber : $getSequenceResult) . "' ";
                    $set[] = " invoice_date=curdate() ";
                    file_put_contents($ROOT . $PHPFOLDER . 'log/debug.txt', print_r($postingDocumentStatusTO, TRUE), FILE_APPEND);
                    if (($postingDocumentStatusTO->documentStatusUId = DST_INVOICED) && ($postingDocumentStatusTO->trackingnumber <> '')) {
                        $set[] = "delivery_instructions='$postingDocumentStatusTO->trackingnumber'";
                    }

// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
                }

                $sql = "UPDATE document_header oh , orders o
              SET " . implode(",", $set) . "
              WHERE document_master_uid = '{$postingDocumentStatusTO->documentMasterUId}'
              AND   order_sequence_no = '{$postingDocumentStatusTO->orderSequenceNo}'";
// file_put_contents('postingDocumentStatusTO.txt', $sql, FILE_APPEND);
                $this->errorTO = $this->dbConn->processPosting($sql, "");

                if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->description = "setDocumentStatus failed : " . $this->errorTO->description;
                } else {
                    $this->errorTO->description = "setDocumentStatus successfully set";
                }
                return $this->errorTO;

            } else {
                return $this->errorTO;
            }
            return $this->errorTO;

        }

    }


    public function postDepotAuditLog($documentMasterUId, $changedBy, $comment, $docStatusUid = false, $type = "DPT")
    {


        $docStatusSQL = ($docStatusUid !== false || !empty($docStatusUid)) ? (mysqli_real_escape_string($this->dbConn->connection, $docStatusUid)) : ('NULL');

        // always store a log entry
        $sql = "INSERT INTO document_depot_audit_log
              (document_master_uid, activity_date, changed_by, comment, document_status_uid, `type`)
            VALUES
            (
              '" . mysqli_real_escape_string($this->dbConn->connection, $documentMasterUId) . "',
              NOW(),
              '" . mysqli_real_escape_string($this->dbConn->connection, $changedBy) . "',
              '" . mysqli_real_escape_string($this->dbConn->connection, $comment) . "',
              " . $docStatusSQL . ",
              '" . $type . "'
            )";

        $this->errorTO = $this->dbConn->processPosting($sql, "");
        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Depot Audit Log Failed : " . $this->errorTO->description;
        } else {
            $this->errorTO->description = "Depot Audit Log Successfully updated!";
        }
        return $this->errorTO;
    }

    // the batches from document_update_batch
    public function postBatch($duUId, $dmUId, $principalUId)
    {

        $sql = "INSERT INTO document_batch
              (`principal_uid`,
               `document_number`,
            	 `invoice_number`,
            	 `document_master_uid`,
            	 `principal_product_uid`,
            	 `product_code`,
            	 `batch_reference_1`,
            	 `batch_reference_2`)
            SELECT {$principalUId},
                   dub.document_number,
                	 dub.invoice_number,
                	 {$dmUId},
                	 pp.uid,
                	 dub.product_code,
                	 dub.batch_reference_1,
                	 dub.batch_reference_2
            FROM  document_update du
                    INNER JOIN document_update_batch dub ON dub.document_update_uid = du.uid
                    LEFT JOIN principal_product pp ON pp.principal_uid = {$principalUId} and pp.product_code = dub.product_code
            WHERE du.uid = {$duUId}
            AND   NOT EXISTS (
              SELECT 1
              FROM   document_batch db
              WHERE  db.document_master_uid = {$dmUId}
              AND    db.product_code = dub.product_code
              AND    db.batch_reference_1 = db.batch_reference_1
              AND    db.batch_reference_2 = db.batch_reference_2
            )";

        $this->errorTO = $this->dbConn->processPosting($sql, "");
        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "postBatch Failed : " . $this->errorTO->description;
            echo $this->errorTO->description;
        } else {
            $this->errorTO->description = "v Successfully updated!";
        }
        return $this->errorTO;
    }


    public function setDocumentReason($dmUId, $reasonUId)
    {

        $sql = "UPDATE document_header oh
                  SET pod_reason_uid='" . mysqli_real_escape_string($this->dbConn->connection, $reasonUId) . "'
            WHERE document_master_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $dmUId) . "'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Reason update failed : " . $this->errorTO->description;
        } else {
            $this->errorTO->description = "Reason Successfully updated!";
        }
        return $this->errorTO;
    }

    public function setDocumentDebriefValidation($postingDocumentDebriefTO)
    {

        global $ROOT, $PHPFOLDER;
        include_once($ROOT . $PHPFOLDER . "DAO/TransactionDAO.php");

        if (!isset($_SESSION)) session_start;
        $userId = $_SESSION['user_id'];


//    if (!CommonUtils::isDepotUser()) {
//      $this->errorTO->type=FLAG_ERRORTO_ERROR;
//      $this->errorTO->description="Invalid Principal Type for User - attempted depot management procedures.";
//      return false;
//    }


        $transactionDAO = new TransactionDAO($this->dbConn);
        $mfT = $transactionDAO->getDepotDocumentItem($userId, $postingDocumentDebriefTO->dmUId); // this also checks if user has access to principal
        if (sizeof($mfT) == 0) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "You do not have access to this information, or order does not exist.";
            return false;
        }

        if (preg_match(GUI_PHP_DATE_VALIDATION, $postingDocumentDebriefTO->deliveryDate, $parts)) {
            if (!checkdate($parts[2], $parts[3], $parts[1])) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Invalid Delivery Date format - " . $postingDocumentDebriefTO->deliveryDate;
                return false;
            }
        } else {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Invalid Delivery Date format - " . $postingDocumentDebriefTO->deliveryDate;
            return false;
        }

        /*  REMOVAL OF DELIVERY DATE CHECKS - LOOSE CHECKING IE: EDGE...
    //del date must be equal too or greater than invoice date.
    if(strtotime($postingDocumentDebriefTO->deliveryDate) >= strtotime($mfT[0]['invoice_date'])){
    } else {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description="Delivery Date cannot be before the Invoice Date - " . $postingDocumentDebriefTO->deliveryDate;
      return false;
    }

    if(strtotime($postingDocumentDebriefTO->deliveryDate) > strtotime(gmdate('Y-m-d'))){
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description="Delivery Date cannot be before the current date.";
      return false;
    }
    */


        //only for delivered partial.
        if ($postingDocumentDebriefTO->updateDeliveredQty == 'Y') {

            if (count($postingDocumentDebriefTO->ddUIdArr) == 0 || count($postingDocumentDebriefTO->amendedQtyArr) == 0) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Error Invalid amended qty passed";
                return false;
            }

            if ($postingDocumentDebriefTO->acceptQty != 'Y') {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "User has not accepted the quantities";
                return false;
            }

            $mfTD = $transactionDAO->getUserDepotDocumentDetails($postingDocumentDebriefTO->dmUId, $userId, $arrayIndex = "dd_uid"); // permissions are enforced

            if ((sizeof($mfTD) != sizeof($postingDocumentDebriefTO->ddUIdArr)) || (sizeof($mfTD) != sizeof($postingDocumentDebriefTO->amendedQtyArr))) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Array sizes not valid or you do not have permissions to manage this document from the depot side.";
                return false;
            }


            $i = 0;

            $changeDelQty = false;  //there must be atleast 1 qty diff for this order to be processed as a partial del.

            foreach ($postingDocumentDebriefTO->ddUIdArr as $r) {
                if (!isset($mfTD[$r])) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Supplied document id's do not correspond with fetched document.";
                    return false;
                }
                if (($postingDocumentDebriefTO->amendedQtyArr[$i] < 0) || !is_numeric($postingDocumentDebriefTO->amendedQtyArr[$i])) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Invalid Amended Qty : {$postingDocumentDebriefTO->amendedQtyArr[$i]}";
                    return false;
                }


// xcvzxcvzxcvzxcvzxcvzxcvzxcvzxcv


                if ($postingDocumentDebriefTO->amendedQtyArr[$i] > $mfTD[$r]["document_qty"]) {

                    file_put_contents('log/dao.txt', print_r($mfTD, TRUE), FILE_APPEND);

                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Supplied amended quantities exceed document qty : {$postingDocumentDebriefTO->amendedQtyArr[$i]}";
                    return false;
                }

                if ($postingDocumentDebriefTO->amendedQtyArr[$i] != $mfTD[$r]["document_qty"]) {
                    $changeDelQty = true;
                }

                $i++;
            }

            if ($changeDelQty === false) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "No quantites have changed - change a quantity or select full delivery! ";
                return false;
            }

        }

        return true;

    }

    public function setDocumentDebrief($postingDocumentDebriefTO)
    {
        global $ROOT, $PHPFOLDER;

        $this->dbConn->dbQuery("SET time_zone='+0:00'");

        $resultOK = $this->setDocumentDebriefValidation($postingDocumentDebriefTO); //validation is mainly for the delivery date.

        if ($resultOK === true) {

            $sql = "UPDATE document_header oh
                INNER JOIN document_master m on oh.document_master_uid = m.uid
                SET
                  oh.source_document_number = m.document_number,
                  oh.delivery_date='{$postingDocumentDebriefTO->deliveryDate}',
                  oh.grv_number = '{$postingDocumentDebriefTO->grvNumber}',
                  oh.waybill_number = '{$postingDocumentDebriefTO->waybillNumber}',
                  oh.debrief_comment = '{$postingDocumentDebriefTO->debriefComment}' 
                  
                WHERE oh.document_master_uid = '{$postingDocumentDebriefTO->dmUId}'";

            $this->errorTO = $this->dbConn->processPosting($sql, "");

            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {

                $this->errorTO->description = "Document Debrief failed : " . $this->errorTO->description;
                return $this->errorTO;

            } else {
                $i = 0;
                foreach ($postingDocumentDebriefTO->ddUIdArr as $r) {

                    $sql = "UPDATE document_detail SET delivered_qty={$postingDocumentDebriefTO->amendedQtyArr[$i]}
                  WHERE uid = {$r}";

                    $this->errorTO = $this->dbConn->processPosting($sql, "");

                    if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                        $this->errorTO->description = "Document Debrief, detail update failed : " . $this->errorTO->description;
                        return $this->errorTO;
                    }
                    $i++;

                }
                /*          if($postingDocumentDebriefTO->paymentType <> 0) {

          	include_once($ROOT.$PHPFOLDER."DAO/PostPaymentDAO.php");

          	  if (!isset($_SESSION)) session_start();
              $principalId = $_SESSION['principal_id'];

              $PostPaymentDAO = new PostPaymentDAO($this->dbConn);
              $this->errorTO = $PostPaymentDAO->insertPaymentDetails($principalId,
                                                           $postingDocumentDebriefTO->dmUId,
                                                           $postingDocumentDebriefTO->paymentType,
                                                           $postingDocumentDebriefTO->deliveryDate,
                                                           $postingDocumentDebriefTO->paymentAmount);
              if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
              ?>
                   <script type='text/javascript'>parent.showMsgBoxInfo('Payment Details insert failed')</script>
              <?php
                    return $this->errorTO;
              }
          }  // End of update payment details
*/
                $this->errorTO->description = "Document Debrief successfully updated!";
            }
            return $this->errorTO;

        } else {
            return $this->errorTO;
        }
    }

    public function setDocumentDetailAmendedValidation($postingDepotAmendDocumentDetailTO)
    {
        global $ROOT, $PHPFOLDER;

        include_once($ROOT . $PHPFOLDER . "DAO/TransactionDAO.php");

        if (!isset($_SESSION)) session_start;
        $userId = $_SESSION["user_id"];
        $principalId = $_SESSION['principal_id'];
        $principalAliasId = (($_SESSION['principal_alias_id'] == "") ? $principalId : $_SESSION['principal_alias_id']);
        $principalType = $_SESSION['principal_type'];
        //$postingDocumentStatusTO->userId = $_SESSION['user_id'];

        $transactionDAO = new TransactionDAO($this->dbConn);

        if (!CommonUtils::isDepotUser()) $mfTD = $transactionDAO->getDocumentWithDetailItem($userId, $principalId, $postingDepotAmendDocumentDetailTO->dmUId);
        else $mfTD = $transactionDAO->getUserDepotDocumentDetails($postingDepotAmendDocumentDetailTO->dmUId, $userId, $arrayIndex = "dd_uid"); // permissions are enforced

        if ((sizeof($mfTD) != sizeof($postingDepotAmendDocumentDetailTO->ddUIdArr)) || (sizeof($mfTD) != sizeof($postingDepotAmendDocumentDetailTO->amendedQtyArr))) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Array sizes not valid or you do not have permissions to manage this document from the depot side.";
            return false;
        }

        if (!CommonUtils::isDepotUser()) {
            if ($mfTD[0]["document_type_uid"] != DT_QUOTATION and $mfTD[0]["document_type_uid"] != DT_PURCHASE_ORDER and $mfTD[0]["document_type_uid"] != DT_ORDER) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Invalid Principal Type for User - attempted depot management procedures.";
                return false;
            }

            include_once($ROOT . $PHPFOLDER . "DAO/AdministrationDAO.php");
            $adminDAO = new AdministrationDAO($this->dbConn);

            $hasRoleQ = $adminDAO->hasRole($userId, $principalId, ROLE_MANAGE_QUOTATION);
            $hasRoleO = $adminDAO->hasRole($userId, $principalId, ROLE_MANAGE_ORDERS);
            if (!$hasRoleQ && !$hasRoleO) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "User does not have permissions to manage quotations";
                return false;
            }

            // put array into proper indexing
            $temp = array();
            foreach ($mfTD as $r) {
                $temp[$r["dd_uid"]] = $r;
            }
            $mfTD = $temp;
        }

        if ($postingDepotAmendDocumentDetailTO->acceptQty != "Y") {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "User has not accepted the quantities";
            return false;
        }

        $i = 0;

        if ($postingDepotAmendDocumentDetailTO->documentTypeUId != DT_UPLIFTS) {

            $allZeroChk = array();

            foreach ($postingDepotAmendDocumentDetailTO->ddUIdArr as $r) {
                if (!isset($mfTD[$r])) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Supplied document id's do not correspond with fetched document.";
                    return false;
                }

                //file_put_contents('var.txt', print_r($postingDepotAmendDocumentDetailTO,TRUE), FILE_APPEND);
                //file_put_contents('var.txt', "here", FILE_APPEND);

                if ($postingDepotAmendDocumentDetailTO->allowDecimal[$i] == "Y") {
                    $postingDepotAmendDocumentDetailTO->amendedQtyArr[$i] = $postingDepotAmendDocumentDetailTO->amendedQtyArr[$i] * 100;
                    //file_put_contents('var.txt', $postingDepotAmendDocumentDetailTO->amendedQtyArr[$i] * 100, FILE_APPEND) 	;
                }
                if (($postingDepotAmendDocumentDetailTO->amendedQtyArr[$i] < 0) || (!preg_match(GUI_PHP_INTEGER_REGEX, $postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]))) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Invalid Amended Qty : {$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]}";
                    return false;
                }


                // WE SHOULD really lock the table whilst we do this until after the update, but should be ok for now
                if (CommonUtils::isDepotUser()) {

                    // Allow negative stock // ,'392', '397', '396', '401' //

                    $allowDepNegStock = array('228', '294', 'XXX', '408', '410', '421', '423', '425', '119', '417', '460', '469', '453', '485', '486');

                    $allowPrinNegStock = array('342', '324', 'XXX');

                    if ($mfTD[$r]["non_stock_item"] == 'N' && $mfTD[$r]["allow_git"] == 'Y') {
                        if ($mfTD[$r]["goods_in_transit"] == NULL) {
                            $gitBal = 0;
                        } else {
                            $gitBal = $mfTD[$r]["goods_in_transit"];
                        }

                        if ($mfTD[$r]["closing"] + $gitBal < $postingDepotAmendDocumentDetailTO->amendedQtyArr[$i] && $postingDepotAmendDocumentDetailTO->amendedQtyArr[$i] != 0) {
                            $this->errorTO->type = FLAG_ERRORTO_ERROR;
                            $this->errorTO->description = "Supplied amended quantities exceeds Stock on incl(GIT) hand : {$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]}";
                            return false;
                        }
                    } elseif ($mfTD[$r]["non_stock_item"] == 'N' && $mfTD[$r]["waiting_dispatch"] == 'Y') {
                        if ($mfTD[$r]["goods_in_transit"] == NULL) {
                            $gitBal = 0;
                        } else {
                            $gitBal = $mfTD[$r]["goods_in_transit"];
                        }

                        if ($mfTD[$r]["available"] - $mfTD[$r]["pending_dispatch"] + $gitBal < $postingDepotAmendDocumentDetailTO->amendedQtyArr[$i] && $postingDepotAmendDocumentDetailTO->amendedQtyArr[$i] != 0) {
                            $this->errorTO->type = FLAG_ERRORTO_ERROR;
                            $this->errorTO->description = "Supplied amended quantities exceeds Stock on incl(GIT) Available : {$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]}";
                            return false;
                        }
                    } else {
                        if ($mfTD[$r]["non_stock_item"] == 'N' && $mfTD[$r]["disable_stock_check"] <> 'Y'
                            && !in_array($mfTD[$r]["principal_uid"], $allowPrinNegStock)
                            && !in_array($mfTD[$r]["depot_uid"], $allowDepNegStock)) {

                            file_put_contents($ROOT . $PHPFOLDER . 'log/varX.txt', print_r($postingDepotAmendDocumentDetailTO->allowDecimal, TRUE));
                            if ($postingDepotAmendDocumentDetailTO->allowDecimal[$i] <> "Y") {
                                if ($mfTD[$r]["closing"] < $postingDepotAmendDocumentDetailTO->amendedQtyArr[$i] && $postingDepotAmendDocumentDetailTO->amendedQtyArr[$i] != 0) {
                                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                                    $this->errorTO->description = "Supplied amended quantities exceed Stock on hand : {$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]}";
                                    return false;
                                }
                            }
                        }
                    }


                }
                if ($mfTD[$r]["document_type_uid"] <> DT_QUOTATION) {
                    if ($postingDepotAmendDocumentDetailTO->allowDecimal[$i] == "Y") {

                        // file_put_contents($ROOT.$PHPFOLDER.'log/ptt27.txt', print_r($postingDepotAmendDocumentDetailTO->amendedQtyArr[$i], TRUE), FILE_APPEND);
                        // file_put_contents($ROOT.$PHPFOLDER.'log/ptt27.txt', print_r($mfTD[$r]["ordered_qty"], TRUE), FILE_APPEND);
                        /*    	 	file_put_contents($ROOT.$PHPFOLDER.'log/ptt27.txt', ($postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]) > $mfTD[$r]["ordered_qty"], FILE_APPEND);
                  if(($postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]) <> $mfTD[$r]["ordered_qty"]) {
                      $this->errorTO->type=FLAG_ERRORTO_ERROR;
                      $this->errorTO->description="Supplied amended qqqASuantities exceed ordered qty : {$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]}";
                      return false;
                  } */
                    } else {
                        if ($postingDepotAmendDocumentDetailTO->amendedQtyArr[$i] > $mfTD[$r]["ordered_qty"]) {
                            $this->errorTO->type = FLAG_ERRORTO_ERROR;
                            $this->errorTO->description = "Supplied amended Quantities exceed ordered qty : {$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]}";
                            return false;
                        }
                    }

                }

                // set the passback vars using the SAME order
                $postingDepotAmendDocumentDetailTO->principalProductUIdArr[$i] = $mfTD[$r]["product_uid"];
                $postingDepotAmendDocumentDetailTO->principalUId = $mfTD[$r]["principal_uid"];
                $postingDepotAmendDocumentDetailTO->processedDepotUId = $mfTD[$r]["depot_uid"];
                $postingDepotAmendDocumentDetailTO->documentTypeUId = $mfTD[$r]["document_type_uid"];


                //build array of totals, checks if all are zero.
                $allZeroChk[$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]] = '1';

                $i++;
            }

            if (count($allZeroChk) == 1 && isset($allZeroChk[0])) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Quantites are all zero, document could not be invoiced,<br>Cancel the document if no stock is available!";
                return false;
            }

        }

        if (CommonUtils::isDepotUser()) {
            if (!in_array($postingDepotAmendDocumentDetailTO->documentTypeUId, array(DT_ORDINV, DT_ORDINV_ZERO_PRICE, DT_DELIVERYNOTE, DT_UPLIFTS, DT_DESTRUCTION_DISPOSAL, DT_WALKIN_INVOICE))) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Invalid Document Passed";
                return false;
            }
        } else {
            if (!in_array($postingDepotAmendDocumentDetailTO->documentTypeUId, array(DT_QUOTATION, DT_PURCHASE_ORDER))) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Invalid Document Passed";
                return false;
            }
        }

        return true;
    }

    public function setDocumentDetailAmended($postingDepotAmendDocumentDetailTO)
    {
        $this->dbConn->dbQuery("SET time_zone='+0:00'");

        $resultOK = $this->setDocumentDetailAmendedValidation($postingDepotAmendDocumentDetailTO);

        if ($resultOK === true) {

            if (!isset($_SESSION)) session_start;
            $userId = $_SESSION["user_id"];

            $i = 0;
            foreach ($postingDepotAmendDocumentDetailTO->ddUIdArr as $r) {
                $sql = "UPDATE document_detail
							set
                    document_qty={$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]},
                    delivered_qty={$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]},
                    extended_price = {$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]}*net_price,
                    vat_amount = {$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]}*net_price*(vat_rate/100),
                    total = {$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]}*net_price+({$postingDepotAmendDocumentDetailTO->amendedQtyArr[$i]}*net_price*(vat_rate/100)),
                    batch='" . substr(((isset($postingDepotAmendDocumentDetailTO->batchArr[$i])) ? $postingDepotAmendDocumentDetailTO->batchArr[$i] : ""), 0, 30) . "'
							where  uid = {$r}";

                $this->errorTO = $this->dbConn->processPosting($sql, "");

                if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->description = "setDocumentDetailAmmended failed : " . $this->errorTO->description;
                    return $this->errorTO;
                }

                $i++;
            }

            // update document header details to be sum of amended quantities - should only therefore be called when moving to invoiced status not back
            $sql = "UPDATE document_header dh,
									 (select sum(dd.delivered_qty) sdq, sum(dd.extended_price) sep, sum(dd.vat_amount) sva, sum(dd.total) st
										from   document_detail dd
										where  dd.document_master_uid = {$postingDepotAmendDocumentDetailTO->dmUId}
										group by dd.document_master_uid) dd
							set   cases = sdq,
										exclusive_total = sep,
										vat_total = sva,
										invoice_total = st
							where dh.document_master_uid = {$postingDepotAmendDocumentDetailTO->dmUId}";

            $this->errorTO = $this->dbConn->processPosting($sql, "");

            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->description = "setDocumentDetailAmended failed in updating DH : " . $this->errorTO->description;
                return $this->errorTO;
            }

            // always create log entry
            $logSQL = "insert into document_depot_audit_log (document_master_uid, activity_date, changed_by, comment)
							 values
							({$postingDepotAmendDocumentDetailTO->dmUId},
							 now(),
							 {$userId},
							 'Ammended Quantities : " . implode(",", $postingDepotAmendDocumentDetailTO->amendedQtyArr) . "')";

            $this->errorTO = $this->dbConn->processPosting($sql, "");

            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->description = "setDocumentDetailAmended failed in audit log : " . $this->errorTO->description;
                return $this->errorTO;
            }


            if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->description = "setDocumentDetailAmended successfully set";
            }

        } else {

            return $this->errorTO;

        }

        return $this->errorTO;
    }

    public function setDocumentDetailAmendedReverse($dmUId)
    {
        $this->dbConn->dbQuery("SET time_zone='+0:00'");

        if (!isset($_SESSION)) session_start;

        $sql = "UPDATE document_detail dd,
								 document_header dh
					set    dd.document_qty=ordered_qty,
								 dd.delivered_qty=ordered_qty,
								 dd.extended_price = ordered_qty*net_price,
								 dd.vat_amount = ordered_qty*net_price*(vat_rate/100),
								 dd.total = ordered_qty*net_price+(ordered_qty*net_price*(vat_rate/100))
					where  dd.document_master_uid = {$dmUId}
					and    dh.document_master_uid = {$dmUId}";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "setDocumentDetailAmendedReverse failed : " . $this->errorTO->description;
            return $this->errorTO;
        }

        // update document header details to be sum of amended quantities - should only therefore be called when moving to invoiced status not back
        $sql = "UPDATE document_header dh,
								 (select sum(dd.ordered_qty) sdq, sum(dd.extended_price) sep, sum(dd.vat_amount) sva, sum(dd.total) st
									from   document_detail dd
									where  dd.document_master_uid = {$dmUId}
									group by dd.document_master_uid) dd
						set   cases = sdq,
									exclusive_total = sep,
									vat_total = sva,
									invoice_total = st,
								  invoice_number = ''
						where dh.document_master_uid = {$dmUId}";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "setDocumentDetailAmendedReverse failed in updating DH : " . $this->errorTO->description;
            return $this->errorTO;
        }

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "setDocumentDetailAmmended successfully set";
        }

        return $this->errorTO;
    }

    public function setDocumentDetailCancelled($dmUId)
    {
        $this->dbConn->dbQuery("SET time_zone='+0:00'");

        $sql = "UPDATE document_detail dd,
								 document_header dh
					set    dd.document_qty=0,
								 dd.delivered_qty=0,
								 dd.extended_price = 0,
								 dd.vat_amount = 0,
								 dd.total = 0,
								 dh.invoice_number = '',
								 -- dh.invoice_date =
								 dh.cases = 0,
								 dh.exclusive_total = 0,
								 dh.vat_total = 0,
								 dh.invoice_total = 0
					where  dd.document_master_uid = {$dmUId}
					and    dh.document_master_uid = {$dmUId}";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "setDocumentDetailCancelled failed : " . $this->errorTO->description;
            return $this->errorTO;
        }

        $this->errorTO->description = "setDocumentDetailCancelled successfully set";
        return $this->errorTO;
    }


    // limited validation check done here, no permissions check
    function associateDocumentDepotStore($dMUId, $psmUId)
    {
        $sql = "update 	document_header dh
						set 		depot_principal_store_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $psmUId) . "'
						where 	document_master_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $dMUId) . "'
						and     exists (select 1 from principal_store_master psm
														where  psm.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $psmUId) . "')";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Document Depot Store successfully Associated";
        } else {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description .= "Failed to associate Document Depot Store!";
        }

        return $this->errorTO;
    }


    // queue epod transaction to be linked
    public function postEPOD($postingEPODTO)
    {

        $this->dbConn->dbQuery("SET time_zone='+0:00'");

        $sql = "INSERT INTO epod_notice
            (
              `document_master_uid`,
              `principal_store_master_uid`,
              `amount`,
              `rsa_id`,
              `cellphone_number`,
              `description`,
              `customer_order_number`,
              `document_number`,
              `document_url`,
              `request_status`,
              `epod_status_msg`,
              `delivery_date`,
              `created_datetime`,
              `created_by_user_uid`
            ) VALUES (
              " . mysqli_real_escape_string($this->dbConn->connection, $postingEPODTO->documentMasterUId) . ",
              " . mysqli_real_escape_string($this->dbConn->connection, $postingEPODTO->principalStoreMasterUId) . ",
              " . mysqli_real_escape_string($this->dbConn->connection, $postingEPODTO->amount) . ",
              '" . mysqli_real_escape_string($this->dbConn->connection, $postingEPODTO->rsaId) . "',
              '" . mysqli_real_escape_string($this->dbConn->connection, $postingEPODTO->cellphoneNumber) . "',
              '" . mysqli_real_escape_string($this->dbConn->connection, $postingEPODTO->description) . "',
              '" . mysqli_real_escape_string($this->dbConn->connection, $postingEPODTO->orderNumber) . "',
              '" . mysqli_real_escape_string($this->dbConn->connection, $postingEPODTO->documentNumber) . "',
              '" . mysqli_real_escape_string($this->dbConn->connection, $postingEPODTO->documentUrl) . "',
              '" . FLAG_STATUS_QUEUED . "',
              'Request Pending',
              '" . mysqli_real_escape_string($this->dbConn->connection, $postingEPODTO->deliveryDate) . "',
              NOW(),
              '" . mysqli_real_escape_string($this->dbConn->connection, $postingEPODTO->createdByUserUid) . "'
            )";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "EPOD request successfully queued!";
        } else {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description .= "EPOD request failure!";
        }

        return $this->errorTO;

    }


    public function postEPODSuccess($epUId, $deliveryNoticeId, $numberRequests)
    {

        $this->dbConn->dbQuery("SET time_zone='+0:00'");

        $sql = "UPDATE epod_notice
              SET
                delivery_notice_id = '" . mysqli_real_escape_string($this->dbConn->connection, $deliveryNoticeId) . "',
                number_of_requests = '" . mysqli_real_escape_string($this->dbConn->connection, $numberRequests) . "',
                request_status = '" . FLAG_ERRORTO_SUCCESS . "',
                epod_status_msg = 'Approval Pending',
                response_datetime = NOW()
              WHERE uid = " . mysqli_real_escape_string($this->dbConn->connection, $epUId) . "";


        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "EPOD Successfully updated!";
        } else {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description .= "EPOD Failed to update!";
        }

        return $this->errorTO;
    }


    public function postEPODError($epUId, $numberRequests, $errorLimit)
    {

        //deactivate after error limit reached
        $flag = FLAG_STATUS_QUEUED;
        if ($numberRequests >= $errorLimit) {
            $flag = FLAG_ERRORTO_ERROR;
        }

        $sql = "UPDATE epod_notice
              SET
                number_of_requests = '" . mysqli_real_escape_string($this->dbConn->connection, $numberRequests) . "',
                request_status = '" . $flag . "',
                epod_status_msg = 'Request failure, count:" . mysqli_real_escape_string($this->dbConn->connection, $numberRequests) . "/" . mysqli_real_escape_string($this->dbConn->connection, $errorLimit) . "'
              WHERE uid = " . mysqli_real_escape_string($this->dbConn->connection, $epUId) . "";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "EPOD Successfully updated!";
        } else {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description .= "EPOD Failed to update!";
        }

        return $this->errorTO;
    }


    public function postEPODUpdate($epUId, $statusMsg, $statusCode, $requestStatus = FLAG_ERRORTO_SUCCESS, $userId = 0)
    {


        $sql = "UPDATE epod_notice
              SET
                epod_status_code = '" . mysqli_real_escape_string($this->dbConn->connection, $statusCode) . "',
                epod_status_msg = '" . mysqli_real_escape_string($this->dbConn->connection, $statusMsg) . "',
                request_status = '" . mysqli_real_escape_string($this->dbConn->connection, $requestStatus) . "',
                last_update_by_user_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "'
              WHERE uid = " . mysqli_real_escape_string($this->dbConn->connection, $epUId) . "";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "EPOD Successfully updated!";
        } else {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description .= "EPOD Failed to update!";
        }

        return $this->errorTO;
    }

    // should only be used by billing process
    public function setBillingInvoiceDate()
    {

        $sql = "update
            document_master a,
            document_header b
          set  b.invoice_date = curdate()
          where a.uid = b.document_master_uid
          and a.principal_uid = 171
          and  b.invoice_date = '0000-00-00'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Invoice Date successfully set";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setDeliveryDetails($dmUId, $transporterName, $truckRegistration, $chepPalletNumber)
    {

        $sql = "UPDATE document_header
          SET    transporter_name = '" . (substr($transporterName, 0, 60)) . "',
                 truck_registration = '" . (substr($truckRegistration, 0, 20)) . "',
                 chep_pallet_number = '" . (substr($chepPalletNumber, 0, 20)) . "'
            WHERE document_master_uid = '{$dmUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "setDeliveryDetails failed : " . $this->errorTO->description;
            return $this->errorTO;
        }

        return $this->errorTO;

    }

    public function postDocumentImage($imageTO)
    {

        $sql = "INSERT INTO document_image (
            document_master_uid,
            image_type,
            uploaded_by_user_uid,
            uploaded_datetime,
            user_agent_string,
            image_data
          ) VALUES (
            {$imageTO->dmUId},
            '{$imageTO->imageType}',
            '{$imageTO->uploadedByUserUId}',
            now(),
            '{$imageTO->userAgentString}',
            '{$imageTO->imageData}'
          )";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type !== FLAG_ERRORTO_SUCCESS) {
            return $this->errorTO;
        }

        $this->errorTO->description = "Signature successfully stored against document";
        return $this->errorTO;
    }

    public function setTripsheetDetails($dmList, $transporterID, $tripSheetNumber, $tripSheetDate, $tripSheetUser, $dmuid)
    {

        $sql = "UPDATE document_header
          SET    tripsheet_number     = " . mysqli_real_escape_string($this->dbConn->connection, $tripSheetNumber) . ",
                 trip_transporter_uid = " . mysqli_real_escape_string($this->dbConn->connection, $transporterID) . ",
                 tripsheet_date       = '" . mysqli_real_escape_string($this->dbConn->connection, $tripSheetDate) . "',
                 tripsheet_created_by = " . mysqli_real_escape_string($this->dbConn->connection, $tripSheetUser) . "
          WHERE  document_master_uid in(" . mysqli_real_escape_string($this->dbConn->connection, $dmList) . ");";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "UpdateTripsheetDetailsFailed : " . $this->errorTO->description;
            return $this->errorTO;
        }

        foreach ($dmuid as $r) {
            $sql = "INSERT INTO `document_tripsheet` (`document_master_uid`,
                                               `tripsheet_number`, 
                                               `tripsheet_date`, 
                                               `transporter_id`,
                                               `tripsheet_created_by`) 
             VALUES ( " . mysqli_real_escape_string($this->dbConn->connection, $r) . ", 
                      " . mysqli_real_escape_string($this->dbConn->connection, $tripSheetNumber) . ", 
                      '" . mysqli_real_escape_string($this->dbConn->connection, $tripSheetDate) . "',
                      " . mysqli_real_escape_string($this->dbConn->connection, $transporterID) . " , 
                      " . mysqli_real_escape_string($this->dbConn->connection, $tripSheetUser) . ");";

            $this->errorTO = $this->dbConn->processPosting($sql, "");
        }

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "setTripsheetDetails : " . $this->errorTO->description;
            return $this->errorTO;
        }
        return $this->errorTO;
    }

    public function updatePickingStatus($doclist, $picklistNumber, $picklistDate)
    {

        $sql = "update document_header dh 
            set    dh.document_status_uid = 87,
                   dh.pick_list_number    = $picklistNumber, 
                   dh.pick_list_date      = '$picklistDate' 
            where  dh.document_master_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $doclist) . ");";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "updatePickingStatus : " . $this->errorTO->description;
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setWaybillNumber($principalId, $list, $wayBillNumber)
    {

        $sql = "UPDATE document_master dm, document_header dh SET dh.waybill_number = $wayBillNumber
          WHERE  dh.document_master_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $list) . ");";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "setWaybillNumber : " . $this->errorTO->description;
            return $this->errorTO;
        }

        return $this->errorTO;

    }

    public function updateTrackingNo($orderSeq, $trackNo, $field)
    {

        if ($field == 'PO') {
            $sql = "update document_header dh set dh.customer_order_number = '" . $trackNo . "'
          where dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $orderSeq) . ";";
        } else {
            $sql = "update orders o set o.delivery_instructions = '" . $trackNo . "'
          where o.order_sequence_no = " . mysqli_real_escape_string($this->dbConn->connection, $orderSeq) . ";";
        }

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "updateTrackingNo : " . $this->errorTO->description;
            return $this->errorTO;
        }

        return $this->errorTO;

    }

    public function resetDocumentstatus($orderSeq)
    {

        $sql = "update document_header dh set dh.document_status_uid = " . DST_INVOICED . "  
          where dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $orderSeq) . "; ";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Reset Status : " . $this->errorTO->description;
            return $this->errorTO;
        }

        return $this->errorTO;

    }

    public function updatePodReceipt($orderUidList, $userID, $rdate)
    {

        $sql = "update document_master dm set dm.pod_received = '" . mysqli_real_escape_string($this->dbConn->connection, $rdate) . "', 
  	                                      dm.pod_receipt_captured_by = " . mysqli_real_escape_string($this->dbConn->connection, $userID) . " 
            where dm.uid in (" . mysqli_real_escape_string($this->dbConn->connection, $orderUidList) . ");";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "updatePodReceipt : " . $this->errorTO->description;
            return $this->errorTO;
        }

        return $this->errorTO;

    }

    public function SetWaybillNumberFromTracking($principalId, $dmuid)
    {

        $sequenceDAO = new SequenceDAO($this->dbConn);
        $sequenceTO = new SequenceTO;
        $sequenceTO->sequenceKey = LITERAL_SEQ_WAYBILL;
        $sequenceTO->principalUId = $principalId;
        $result = $sequenceDAO->getSequence($sequenceTO, $seqVal);

        if ($result->type != FLAG_ERRORTO_SUCCESS) {
            return $result;
        } else {
            return $seqVal;
        }
    }

    public function updateWaybillNumberFromTracking($wayBillNumber, $dmuid)
    {

        $sql = "UPDATE document_header dh SET dh.waybill_number = $wayBillNumber
          WHERE  dh.document_master_uid in ('" . mysqli_real_escape_string($this->dbConn->connection, $dmuid) . "');";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "setWaybillNumber : " . $this->errorTO->description;
            return $this->errorTO;
        }

        return $this->errorTO;

    }

    public function createStandingOrderRecord($newdate, $soLead, $storeUid, $docUid, $orderRequired)
    {

        $sql = "INSERT INTO `standing_orders` (`order_create_date`, 
                                           `document_master_uid`, 
                                           `order_required_month`, 
                                           `lead_time`, 
                                           `principal_store_uid`) 
           VALUES ('" . date_format($newdate, 'Y-m-d') . "', 
                    " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . ", 
                    " . mysqli_real_escape_string($this->dbConn->connection, $orderRequired) . ",  
                    " . mysqli_real_escape_string($this->dbConn->connection, $soLead) . ",  
                    " . mysqli_real_escape_string($this->dbConn->connection, $storeUid) . ");";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "SOadded : " . $this->errorTO->description;
            return $this->errorTO;
        }

        return $this->errorTO;

    }

    public function updateTripSheetImage($principalId, $tripsheetNumber, $imagefile)
    {

        $sql = "UPDATE `document_tripsheet` dt,
                   `document_master` dm SET `image_file`='" . $imagefile . "' 
            WHERE   dm.uid = dt.document_master_uid
            AND     tripsheet_number = " . mysqli_real_escape_string($this->dbConn->connection, $tripsheetNumber) . "
            AND     dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ";";
        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "TripSheet Update : " . $this->errorTO->description;
            return $this->errorTO;
        }

        return $this->errorTO;

    }

    public function updateDocumentHeaderImage($principalId, $docno, $imagefile, $doctype, $docnoField)
    {

        $sql = "UPDATE document_header dh, 
                   document_master dm set dh.scanned_document_exists = 'Y', dh.image_file = '" . $imagefile . "'
            WHERE  dm.uid = dh.document_master_uid
            AND    dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
            AND    dm.document_type_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $doctype) . ")
            AND    dm." . mysqli_real_escape_string($this->dbConn->connection, $docnoField) . " = " . mysqli_real_escape_string($this->dbConn->connection, $docno) . ";";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "TripSheet Update : " . $this->errorTO->description;
            return $this->errorTO;
        }

        return $this->errorTO;

    }

// **********************************************************************************************************************************
    public function insertNewDDline($newlineArray)
    {

        $wdocUid = mysqli_real_escape_string($this->dbConn->connection, $newlineArray['documentUid']);
        $wlineNo = mysqli_real_escape_string($this->dbConn->connection, $newlineArray['lineNo']) + 1;
        $wprodId = mysqli_real_escape_string($this->dbConn->connection, $newlineArray['prodUid']);
        $wvatr = mysqli_real_escape_string($this->dbConn->connection, $newlineArray['vatRate']);
        $allDec = mysqli_real_escape_string($this->dbConn->connection, $newlineArray['allDec']);
        $wprice = mysqli_real_escape_string($this->dbConn->connection, $newlineArray['wPrice']);

        if ($allDec == 'Y') {
            $docQty = mysqli_real_escape_string($this->dbConn->connection, $newlineArray['wQty']) * 100;
            $wprice = mysqli_real_escape_string($this->dbConn->connection, $newlineArray['wPrice']);
        } else {
            $docQty = mysqli_real_escape_string($this->dbConn->connection, $newlineArray['wQty']);
        }


        $isql = "INSERT INTO `document_detail` (`document_master_uid`, 
                                              `line_no`, 
                                              `product_uid`, 
                                              `ordered_qty`, 
                                              `document_qty`, 
                                              `delivered_qty`,
                                              `selling_price`,
                                              `net_price`, 
                                              `extended_price`,
                                              `vat_rate`, 
                                              `vat_amount`, 
                                              `total`
                                              ) VALUES (" . $wdocUid . ", 
                                                        " . $wlineNo . ", 
                                                        " . $wprodId . ", 
                                                        " . $docQty . ", 
                                                        " . $docQty . ", 
                                                        " . $docQty . ", 
                                                        " . $wprice . ", 
                                                        " . $wprice . ", 
                                                        " . $docQty . " * " . $wprice . ", 
                                                        " . $wvatr . ", 
                                                        " . $docQty . " * " . $wprice . " * (" . $wvatr . " / 100),
                                                        " . $docQty . " * " . $wprice . " * (1 + (" . $wvatr . " / 100)))";

        $this->errorTO = $this->dbConn->processPosting($isql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "New Line not added - Contact support : " . $this->errorTO->description;
            return $this->errorTO;
        }

        $this->dbConn->dbQuery("commit");

        $hsql = "update document_header dh set dh.cases = (select sum(dd.ordered_qty)
                                                        from .document_detail dd
                                                        where dd.document_master_uid = " . $wdocUid . "),
                                                dh.exclusive_total = (select sum(dd.extended_price)
                                                                      from .document_detail dd
                                                                      where dd.document_master_uid = " . $wdocUid . "),
                                                dh.vat_total       = (select sum(dd.vat_amount)
                                                                      from .document_detail dd
                                                                      where dd.document_master_uid = " . $wdocUid . "),
                                                dh.invoice_total   = (select sum(dd.total)
                                                                      from .document_detail dd
                                                                      where dd.document_master_uid = " . $wdocUid . ")
              where dh.document_master_uid = " . $wdocUid . " ;";

        $errorTO = $this->dbConn->processPosting($hsql, "");

        if ($errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "Headers not Updated : " . $errorTO->description;
            echo($errorTO->description);;
        }
        $this->dbConn->dbQuery("commit");

        return ($errorTO->type);
// **********************************************************************************************************************************

    }

// **********************************************************************************************************************************

    public function SaveAuthorisedTransctions($userId, $docNO)
    {

        $sql = "UPDATE `document_master` dm SET `authorised_by_uid`= " . mysqli_real_escape_string($this->dbConn->connection, $userId) . ",
                                            `authorise_date_time` = NOW()
            WHERE   dm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $docNO) . ";";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Auth Updated : " . $this->errorTO->description;
            return $this->errorTO;
        } else {
            $this->dbConn->dbQuery("commit");
        }

        return $this->errorTO;

    }

// **********************************************************************************************************************************

    public function SaveAuthorisedPayments($userId, $docNO)
    {

        $sql = "UPDATE `payment_header` ph SET `authorised_by_uid`= " . mysqli_real_escape_string($this->dbConn->connection, $userId) . ",
                                           `authorise_date_time` = NOW()
            WHERE   ph.uid = " . mysqli_real_escape_string($this->dbConn->connection, $docNO) . ";";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Auth Updated : " . $this->errorTO->description;
            return $this->errorTO;
        } else {
            $this->dbConn->dbQuery("commit");
        }

        return $this->errorTO;

    }

// **********************************************************************************************************************************
    public function UpdateOverRideRep($docNo, $repUID, $userId)
    {

        $sql = "update document_header dh set dh.overide_rep_code_uid    = " . mysqli_real_escape_string($this->dbConn->connection, $repUID) . ", 
                                          dh.overide_rep_code_change = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . "   
            where dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $docNo) . ";";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Failed to Updat : " . $this->errorTO->description;
            return $this->errorTO;
        } else {
            $this->dbConn->dbQuery("commit");
        }

        return $this->errorTO;

    }

// **********************************************************************************************************************************
    public function UpdateDocumentCopies($docId)
    {

        $sql = "update document_header dh set dh.copies = dh.copies+1 
            where dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $docId) . ";";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Failed to Updat : " . $this->errorTO->description;
            return $this->errorTO;
        } else {
            $this->dbConn->dbQuery("commit");
        }

        return $this->errorTO;

    }

// ***********************************************************************************************************************************************
    public function updateInvoiceMailedFlag($docUid)
    {

        $sql = "UPDATE document_header dh SET dh.invoice_mailed = 'Y' 
  	           where  dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . " ;";


        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Failed to Update : " . $this->errorTO->description;
            return $this->errorTO;
        } else {
            $this->dbConn->dbQuery("commit");
        }

        return $this->errorTO;
    }
// **********************************************************************************************************************************


}

?>
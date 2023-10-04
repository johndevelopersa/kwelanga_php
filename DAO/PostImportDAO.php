<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
@include_once($ROOT . $PHPFOLDER . 'DAO/PrincipalDAO.php'); // backend doesnt have this file

class PostImportDAO
{
    public $errorTO;
    private $dbConn;

    function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
        $this->errorTO = new ErrorTO;
    }

    public function postOrdersHoldingValidation($postOrdersHoldingTO)
    {
        $principalDAO = new PrincipalDAO($this->dbConn);

        // ignore if empty as sometimes the document is rerouted from Capture Screen which doesnt have vendor
        if (!empty($postOrdersHoldingTO->vendorUid)) {
            // this can return multiple rows and we could go and check the vendor account against each, but !empty is sufficient for now
            $mfPV = $principalDAO->getPrincipalVendor($postOrdersHoldingTO->vendorUid, $postOrdersHoldingTO->principalUid);

            if (empty($mfPV)) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Vendor does not have access to this Principal / Seller";
                return false;
            }
        }

        // it needs stringent validation due to depot exports format
        foreach ($postOrdersHoldingTO->detailArr as $row) {
            if (
                ((trim($row->mass) != "") && (!preg_match(GUI_PHP_FLOAT_REGEX, $row->mass))) ||
                ((trim($row->volume) != "") && (!preg_match(GUI_PHP_FLOAT_REGEX, $row->volume)))
            ) {
                $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                $this->errorTO->description = "Non-Numeric Mass / Volume passed";
                return false;
            }
        }

        return true;

    }

    public function postOrdersHolding($postOrdersHoldingTO)
    {

        $result = $this->postOrdersHoldingValidation($postOrdersHoldingTO);

        if ($result !== true) {
            return $this->errorTO;
        }

        $this->dbConn->dbQuery("SET time_zone='+0:00'");   //For NOW() - createddate.

        $sql = "insert into orders_holding
				(
				`principal_uid`,
				`vendor_uid`,
				`status`,
				`status_msg`,
				`created_date`,
				`data_source`,
				`incoming_file`,
				`user_action_status`,
				`last_change_by_userid`,
				`ws_unique_creator_id`,
				`document_number`,
				`client_document_number`,
        `source_document_number`,
				`capture_date`,
				`order_date`,
				`delivery_date`,
        `requested_delivery_date`,
				`invoice_date`,
        `invoice_number`,
				`captured_by`,
				`reference`,
				`general_reference_1`,
				`general_reference_2`,
				`delivery_instructions`,
				`document_type`,
				`document_type_uid`,
				`ship_to_name`,
				`ship_to_gln`,
				`buyer_gln`,
				`deliver_name`,
				`old_account`,
				`debtors_store_identifier`,
				`sales_agent_store_identifier`,
				`principal_store_uid`,
				`depot_lookup_ref`,
				`chain_lookup_ref`,
				`store_lookup_ref`,
        `depot_special_field_uid`,
        `depot_lookup_ref_enforced`,
				`online_file_processing_uid`,
				`cancelled_order_notified`,
				`check_price_variance`,
				`pricing_conflict_action`,
				`edifiledef_notified`,
				`flag_stripped_deliver_name_lookup_ref`,
				`vendor_reference`,
				`file_log_uid`,
				`enforce_same_depot`,
				`update_store_depot`,
        `update_product`,
        `insert_product`,
        `skip_computation_check`,
        `document_origin_queried`,
        `depot_uid`,
        `document_status_uid`,
        `exception_notified`,
        `additional_type`,
        `additional_details`,
        `expiry_date`,
        `off_invoice_discount`,
        `off_invoice_discount_type`
						)
						values (
		          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->principalUid) . "',
		          " . (($postOrdersHoldingTO->vendorUid == "") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->vendorUid)) . ",
		          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->status) . "',
		          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->statusMsg) . "',
		          NOW(),
		          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->dataSource) . "',
		          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->incomingFile) . "',
		          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->userActionStatus) . "',
		          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->lastChangeByUserid) . "',
		          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->wsUniqueCreatorId) . "',
		          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->documentNo) . "',
		          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->clientDocumentNo) . "',
              '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->sourceDocumentNo) . "',
    				  " . ((empty($postOrdersHoldingTO->captureDate)) ? ("NULL") : ("'" . $postOrdersHoldingTO->captureDate . "'")) . ",
    				  " . ((empty($postOrdersHoldingTO->orderDate)) ? ("NULL") : ("'" . $postOrdersHoldingTO->orderDate . "'")) . ",
    				  " . ((empty($postOrdersHoldingTO->deliveryDate)) ? ("NULL") : ("'" . $postOrdersHoldingTO->deliveryDate . "'")) . ",
                                      " . ((empty($postOrdersHoldingTO->requestedDeliveryDate)) ? ("NULL") : ("'" . $postOrdersHoldingTO->requestedDeliveryDate . "'")) . ",
    				  " . ((empty($postOrdersHoldingTO->invoiceDate)) ? ("NULL") : ("'" . $postOrdersHoldingTO->invoiceDate . "'")) . ",
              '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->invoiceNumber) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->capturedBy) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, trim($postOrdersHoldingTO->reference)) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, trim($postOrdersHoldingTO->generalReference1)) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, trim($postOrdersHoldingTO->generalReference2)) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, substr(trim($postOrdersHoldingTO->deliveryInstructions), 0, 100)) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->documentType) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->documentTypeUId) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->shipToName, 0, 100)) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->shipToGLN) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->buyerGLN) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->deliverName) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->oldAccount) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->debtorsStoreIdentifier) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->salesAgentStoreIdentifier) . "',
    				  " . mysqli_real_escape_string($this->dbConn->connection, (empty($postOrdersHoldingTO->principalStoreUId)) ? "NULL" : $postOrdersHoldingTO->principalStoreUId) . ",
    				  '" . mysqli_real_escape_string($this->dbConn->connection, trim($postOrdersHoldingTO->depotLookupRef)) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, trim($postOrdersHoldingTO->chainLookupRef)) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, trim($postOrdersHoldingTO->storeLookupRef)) . "',
    				  " . ((empty($postOrdersHoldingTO->depotSpecialFieldUId)) ? ("NULL") : ($postOrdersHoldingTO->depotSpecialFieldUId)) . ",
              '" . mysqli_real_escape_string($this->dbConn->connection, trim($postOrdersHoldingTO->depotLookupRefEnforced)) . "',
    				  " . ((empty($postOrdersHoldingTO->onlineFileProcessingUId)) ? ("NULL") : ($postOrdersHoldingTO->onlineFileProcessingUId)) . ",
    				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->cancelledOrderNotified) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->checkPriceVariance) . "',
    				  " . ((empty($postOrdersHoldingTO->pricingConflictAction)) ? "NULL" : ($postOrdersHoldingTO->pricingConflictAction)) . ",
    				  '" . $postOrdersHoldingTO->EDIFileDefNotified . "',
    				  '" . $postOrdersHoldingTO->flagStrippedDeliverNameLookupRef . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, trim($postOrdersHoldingTO->vendorReference)) . "',
              " . (($postOrdersHoldingTO->fileLogUId == "") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->fileLogUId)) . ",
    				  '" . $postOrdersHoldingTO->enforceSameDepot . "',
    					'" . $postOrdersHoldingTO->updateStoreDepot . "',
              '" . $postOrdersHoldingTO->updateProduct . "',
              '" . $postOrdersHoldingTO->insertProduct . "',
              '" . $postOrdersHoldingTO->skipInvoiceComputationCheck . "',
              '" . $postOrdersHoldingTO->documentOriginQueried . "',
              " . ((empty($postOrdersHoldingTO->depotUId)) ? ("NULL") : ($postOrdersHoldingTO->depotUId)) . ",
              " . ((empty($postOrdersHoldingTO->documentStatusUId)) ? ("NULL") : ($postOrdersHoldingTO->documentStatusUId)) . ",
              '" . $postOrdersHoldingTO->exceptionNotified . "',
              '" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->additionalType, 0, 30)) . "',
              '" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->additionalDetails, 0, 100)) . "',
              " . ((empty($postOrdersHoldingTO->expiryDate)) ? ("NULL") : ("'" . $postOrdersHoldingTO->expiryDate . "'")) . ",
              " . ((empty($postOrdersHoldingTO->offInvoiceDiscount)) ? (0) : ("'" . $postOrdersHoldingTO->offInvoiceDiscount . "'")) . ",
              " . ((empty($postOrdersHoldingTO->offInvoiceDiscountType)) ? ("NULL") : ("'" . $postOrdersHoldingTO->offInvoiceDiscountType . "'")) . "
				  )";

        $this->errorTO = $this->dbConn->processPosting($sql, '');

        $uid = "";
        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "ERROR occurred inserting into orders_holding: " . $this->errorTO->description;
            return $this->errorTO;
        } else {
            $uid = $this->dbConn->dbGetLastInsertId();
        }

        // insert the detail
        $sql = "insert into orders_holding_detail
				(
				`orders_holding_uid`,
				`principal_product_uid`,
				`quantity`,
		    `rejected_quantity`,
				`pallets`,
				`list_price`,
				`discount_value`,
				`discount_reference`,
				`nett_price`,
				`ext_price`,
				`vat_amount`,
				`total_price`,
				`vat_rate`,
		    `insert_vat_rate`,
		    `product_sku_gtin`,
				`product_gtin`,
				`product_code`,
		    `original_product_code`,
				`product_name`,
				`client_line_no`,
				`client_page_no`,
        `update_product_status`,
		    `update_product_vat_rate`,
        `override_price_type`,
        `mass`,
        `volume`,
		    `ws_unique_creator_id`,
		    `additional_type`
						)
						values ";

        foreach ($postOrdersHoldingTO->detailArr as $no => $empty) {

            $comma = ($no == 0) ? ('') : (', ');

            $rejectedQuantity = (empty($postOrdersHoldingTO->detailArr[$no]->rejectedQuantity) ? "NULL" : $postOrdersHoldingTO->detailArr[$no]->rejectedQuantity);

            $sql .= $comma . "({$uid},
				  " . mysqli_real_escape_string($this->dbConn->connection, (empty($postOrdersHoldingTO->detailArr[$no]->principalProductUid)) ? "NULL" : $postOrdersHoldingTO->detailArr[$no]->principalProductUid) . ",
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->quantity) . "',
				  " . $rejectedQuantity . ",
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->pallets) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->listPrice) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->discountValue) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->discountReference) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->nettPrice) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->extPrice) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->vatAmount) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->totalPrice) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->vatRate) . "',
		      '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->insertVatRate) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->productSKUGTIN) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->productGTIN) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->productCode) . "',
		      '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->originalProductCode) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, preg_replace('/[\x00-\x1F\x80-\xFF\x22\x26\xEF\xBD\xBF]/', '', substr($postOrdersHoldingTO->detailArr[$no]->productName, 0, 100))) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->clientLineNo) . "',
				  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->clientPageNo) . "',
          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->updateProductStatus) . "',
          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->updateProductVATRate) . "',
          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->overridePriceType) . "',
          " . ((trim($postOrdersHoldingTO->detailArr[$no]->mass) == "") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->mass)) . ",
          " . ((trim($postOrdersHoldingTO->detailArr[$no]->volume) == "") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->volume)) . ",
          '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->detailArr[$no]->wsUniqueCreatorId) . "',
          '" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->detailArr[$no]->additionalType, 0, 1000)) . "'
				  )";
        }

        if (empty($postOrdersHoldingTO->detailArr)) {
            if ($postOrdersHoldingTO->skipDtlLineCountCheck == "N") {
                $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                $this->errorTO->description = "No Detail Lines found - and skip flag not set!";
                return $this->errorTO;
            }
        } else {
            $this->errorTO = $this->dbConn->processPosting($sql, '');

            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->description = "ERROR occurred inserting into orders_holding_holding_detail: " . $this->errorTO->description;
                echo $postOrdersHoldingTO->documentNo . " - " . $postOrdersHoldingTO->principalUid;
                return $this->errorTO;
            }
        }

        // insert the store if necessary
        if (isset($postOrdersHoldingTO->postingStoreTO)) {
            $sql = "insert into orders_holding_store
					(orders_holding_uid,
					 deliver_name,
					 deliver_add1,
					 deliver_add2,
					 deliver_add3,
					 bill_name,
					 bill_add1,
 					 bill_add2,
					 bill_add3,
					 gln_code,
					 vat_number,
					 vat_number_2,
 					 delivery_day,
           no_vat,
					 old_account,
					 owned_by,
					 update_principal_store,
					 update_delivery_day,
					 update_no_vat,
           update_store_status)
				  values (
						{$uid},
						'" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->postingStoreTO->deliverName, 0, 60)) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->postingStoreTO->deliverAdd1, 0, 60)) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->postingStoreTO->deliverAdd2, 0, 60)) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->postingStoreTO->deliverAdd3, 0, 60)) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->postingStoreTO->billName, 0, 60)) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->postingStoreTO->billAdd1, 0, 60)) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->postingStoreTO->billAdd2, 0, 60)) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, substr($postOrdersHoldingTO->postingStoreTO->billAdd3, 0, 60)) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingStoreTO->eanCode) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingStoreTO->vatNumber) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingStoreTO->vatNumber2) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingStoreTO->deliveryDay) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingStoreTO->noVAT) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingStoreTO->oldAccount) . "'," .
                ((trim($postOrdersHoldingTO->postingStoreTO->ownedBy) == "") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingStoreTO->ownedBy)) . ",
						'" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingStoreTO->updatePrincipalStore) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingStoreTO->updateDeliveryDay) . "',
						'" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingStoreTO->updateNoVAT) . "',
            '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingStoreTO->updateStoreStatus) . "'
					)";

            $this->errorTO = $this->dbConn->processPosting($sql, '');

            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->description = "ERROR occurred inserting into orders_holding_store: " . $this->errorTO->description;
                return $this->errorTO;
            }
        }

        // insert the special field(s) - can be done separately from store creation
        if (sizeof($postOrdersHoldingTO->postingSpecialFieldTOArr) > 0) {
            $sql = "insert into orders_holding_special_field
					(
					`orders_holding_uid`,
					`field_uid`,
					`value`,
					`entity_uid`,
					`allow_update`,
					`skip_validation`
					)
					values ";

            foreach ($postOrdersHoldingTO->postingSpecialFieldTOArr as $no => $empty) {

                $comma = ($no == 0) ? ('') : (', ');

                $sql .= $comma . "({$uid},
						  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingSpecialFieldTOArr[$no]->fielduid) . "',
						  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingSpecialFieldTOArr[$no]->value) . "',
						  " . (($postOrdersHoldingTO->postingSpecialFieldTOArr[$no]->entityUId == "") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingSpecialFieldTOArr[$no]->entityUId)) . ",
						  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingSpecialFieldTOArr[$no]->allowUpdate) . "',
						  '" . mysqli_real_escape_string($this->dbConn->connection, $postOrdersHoldingTO->postingSpecialFieldTOArr[$no]->skipValidation) . "'
						  )";
            }

            $this->errorTO = $this->dbConn->processPosting($sql, '');

            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->description = "ERROR occurred inserting into orders_holding_special_field: " . $this->errorTO->description;
                return $this->errorTO;
            }
        }


        $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        return $this->errorTO;

    }


    public function postRemittanceValidation($postRemittanceTO)
    {
        $principalDAO = new PrincipalDAO($this->dbConn);

        // ignore if empty as sometimes the document is rerouted from Capture Screen which doesnt have vendor
        if (!empty($postRemittanceTO->vendorUId)) {
            // this can return multiple rows and we could go and check the vendor account against each, but !empty is sufficient for now
            $mfPV = $principalDAO->getPrincipalVendor($postRemittanceTO->vendorUId, $postRemittanceTO->principalUId);

            if (empty($mfPV)) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Vendor does not have access to this Principal / Seller for Remittances";
                return false;
            }
        }

        if (
            ((trim($postRemittanceTO->totalAmount) != "") && (!preg_match(GUI_PHP_FLOAT_REGEX, $postRemittanceTO->totalAmount)))
        ) {
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "Non-Numeric total amount passed";
            return false;
        }

        // it needs stringent validation due to depot exports format
        foreach ($postRemittanceTO->detailArr as $row) {
            if (
                ((trim($row->amount) != "") && (!preg_match(GUI_PHP_FLOAT_REGEX, $row->amount))) ||
                ((trim($row->originalAmount) != "") && (!preg_match(GUI_PHP_FLOAT_REGEX, $row->originalAmount))) ||
                ((trim($row->adjustmentAmount) != "") && (!preg_match(GUI_PHP_FLOAT_REGEX, $row->adjustmentAmount)))
            ) {
                $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                $this->errorTO->description = "Non-Numeric amount / original amount / adjustment amount passed";
                return false;
            }
        }

        return true;

    }

    public function postRemittance($postRemittanceTO)
    {

        $result = $this->postRemittanceValidation($postRemittanceTO);

        if ($result !== true) {
            return $this->errorTO;
        }

        $this->dbConn->dbQuery("SET time_zone='+0:00'");   //For NOW() - createddate.

        $sql = "insert into document_remittance
    				(
    				`ws_unique_creator_id`,
    				`data_source`,
    				`captured_by`,
    				`capture_date`,
    				`insert_datetime`,
    				`payment_effective_date`,
    				`principal_uid`,
    				`principal_gln`,
    				`vendor_uid`,
    				`vendor_reference`,
    				`document_number`,
    				`reference`,
            `total_amount`,
    				`document_type`,
    				`document_type_uid`,
    				`buyer_gln`
						)
						values (
              '" . mysqli_real_escape_string($this->dbConn->connection, $postRemittanceTO->wsUniqueCreatorId) . "',
              '" . mysqli_real_escape_string($this->dbConn->connection, $postRemittanceTO->dataSource) . "',
              '" . mysqli_real_escape_string($this->dbConn->connection, $postRemittanceTO->capturedBy) . "',
              " . ((empty($postRemittanceTO->captureDate)) ? ("NULL") : ("'" . $postRemittanceTO->captureDate . "'")) . ",
              NOW(),
              " . ((empty($postRemittanceTO->captureDate)) ? ("NULL") : ("'" . $postRemittanceTO->paymentEffectiveDate . "'")) . ",
              '" . mysqli_real_escape_string($this->dbConn->connection, $postRemittanceTO->principalUId) . "',
		          " . (($postRemittanceTO->principalGLN == "") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postRemittanceTO->principalGLN)) . ",
		          " . (($postRemittanceTO->vendorUId == "") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postRemittanceTO->vendorUId)) . ",
		          '" . mysqli_real_escape_string($this->dbConn->connection, trim($postRemittanceTO->vendorReference)) . "',
		          '" . mysqli_real_escape_string($this->dbConn->connection, $postRemittanceTO->documentNo) . "',
    				  '" . mysqli_real_escape_string($this->dbConn->connection, trim($postRemittanceTO->reference)) . "',
    				  " . ((empty($postRemittanceTO->totalAmount)) ? ("NULL") : ($postRemittanceTO->totalAmount)) . ",
    				  '" . mysqli_real_escape_string($this->dbConn->connection, trim($postRemittanceTO->documentType)) . "',
    				  " . ((empty($postRemittanceTO->totalAmount)) ? ("NULL") : ($postRemittanceTO->documentTypeUId)) . ",
    				  '" . mysqli_real_escape_string($this->dbConn->connection, trim($postRemittanceTO->buyerGLN)) . "'
				  )";

        $this->errorTO = $this->dbConn->processPosting($sql, '');

        $uid = "";
        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "ERROR occurred inserting into document_remittance: " . $this->errorTO->description;
            return $this->errorTO;
        } else {
            $uid = $this->dbConn->dbGetLastInsertId();
        }

        // insert the detail
        $sql = "insert into document_remittance_detail
    				(
            `principal_uid`,
    				`document_remittance_uid`,
    				`type`,
    				`line_no`,
    		    `amount`,
    				`original_amount`,
    				`adjustment_reason`,
    				`adjustment_reference`,
    				`adjustment_amount`,
    				`invoice_creation_date`,
    				`invoice_reference`,
            `document_type`
						)
						values ";

        foreach ($postRemittanceTO->detailArr as $no => $row) {

            $comma = ($no == 0) ? ('') : (', ');

            $sql .= $comma . "(
            " . ((trim($row->principalUId) == "") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, trim($row->principalUId))) . ",
            {$uid},
            '" . mysqli_real_escape_string($this->dbConn->connection, trim($row->type)) . "',
            '" . mysqli_real_escape_string($this->dbConn->connection, trim($row->lineNo)) . "',
    				" . ((empty($row->amount)) ? ("NULL") : ($row->amount)) . ",
    				" . ((empty($row->originalAmount)) ? ("NULL") : ($row->originalAmount)) . ",
    				'" . mysqli_real_escape_string($this->dbConn->connection, trim($row->adjustmentReason)) . "',
    				'" . mysqli_real_escape_string($this->dbConn->connection, trim($row->adjustmentReference)) . "',
    				" . ((empty($row->adjustmentAmount)) ? ("NULL") : ($row->adjustmentAmount)) . ",
    				" . ((empty($row->invoiceCreationDate)) ? ("NULL") : "'" . $row->invoiceCreationDate . "'") . ",
    				'" . mysqli_real_escape_string($this->dbConn->connection, trim($row->invoiceReference)) . "',
    				'" . mysqli_real_escape_string($this->dbConn->connection, trim($row->documentType)) . "'
				  )";
        }

        $this->errorTO = $this->dbConn->processPosting($sql, '');

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "ERROR occurred inserting into document_remittance_detail : " . $this->errorTO->description;
            echo $postRemittanceTO->documentNo . " - " . $postRemittanceTO->principalUId;
            return $this->errorTO;
        }

        $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        return $this->errorTO;

    }


    // called during PULL from sureserver
    // updates the database on phpbackend
    // PRINCIPAL only has a pull, and due to small data volumes and infrequent changes and no changes coming from backend side, we just delete and insert
    public function postPrincipal($postingPrincipalTO)
    {
        global $errorTO;
        global $dbConn;
        $dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

        // check if already exists in synch tables. If so, then LOCK it.
        $sql = "delete from principal where uid = '" . $postingPrincipalTO->puid . "' ";
        $dbConn->dbinsQuery($sql);
        if (!$dbConn->dbQueryResult) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Could not delete row in principal table. " . mysql_info($dbConn->connection);
            return $errorTO;
        }

        $sql = "INSERT INTO `principal`
    				  (
						uid,
						`principal_code`,
						`name`,
						`physical_add1`,
                        `physical_add2`,
                        `physical_add3`,
                        `physical_add4`,
                        `postal_add1`,
                        `postal_add2`,
                        `postal_add3`,
                        `postal_add4`,
                        `vat_num`,
                        `rt_acc_num`,
                        `office_tel`,
                        `email_add`,
                        `contactperson`,
						`alt_principal_code`,
						`banking_details`,
						principal_type,
						principal_gln,
						last_updated,
						last_synched,
						last_synch_status
    				  )
    				  VALUES (" .
            "'" . $postingPrincipalTO->puid . "'," .
            "'" . $postingPrincipalTO->principal_code . "'," .
            "'" . $postingPrincipalTO->name . "'," .
            "'" . $postingPrincipalTO->physical_add1 . "'," .
            "'" . $postingPrincipalTO->physical_add2 . "'," .
            "'" . $postingPrincipalTO->physical_add3 . "'," .
            "'" . $postingPrincipalTO->physical_add4 . "'," .
            "'" . $postingPrincipalTO->postal_add1 . "'," .
            "'" . $postingPrincipalTO->postal_add2 . "'," .
            "'" . $postingPrincipalTO->postal_add3 . "'," .
            "'" . $postingPrincipalTO->postal_add4 . "'," .
            "'" . $postingPrincipalTO->vat_num . "'," .
            "'" . $postingPrincipalTO->rt_acc_num . "'," .
            "'" . $postingPrincipalTO->office_tel . "'," .
            "'" . $postingPrincipalTO->email_add . "'," .
            "'" . $postingPrincipalTO->contactperson . "'," .
            "'" . $postingPrincipalTO->altPrincipalCode . "'," .
            "'" . $postingPrincipalTO->bankingDetails . "'," .
            "'" . $postingPrincipalTO->principalType . "'," .
            "'" . $postingPrincipalTO->principalGLN . "'," .
            "'" . $postingPrincipalTO->lastUpdated . "'," .
            "now()," .
            "'" . FLAG_ERRORTO_SUCCESS . "'" .
            ")";


        $errorTO = $dbConn->processPosting($sql, $postingPrincipalTO->name);
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->identifier = $postingPrincipalTO->puid; // get the UID just created. Note there is no autocounter.
            $errorTO->description = "Principal Successfully Created.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->identifier = $postingPrincipalTO->puid;
            $errorTO->description = "Failed to create Principal." . mysql_error($dbConn->connection) . $errorTO->description;
            return $errorTO;
        }

        return $errorTO;
    }


    public function postFileLog($postingFileLogTO)
    {
        global $errorTO;
        global $dbConn;

        $dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

        if ($postingFileLogTO->DMLType == "INSERT") {
            // line_count is updated separately
            $sql = "insert into file_log (file_name, processed_date, status, vendor_uid, vendor_removal_date, vendor_removed,
										error_count,error_type, error_msg, principal_uid, online_file_processing_uid)
				  values (
					'{$postingFileLogTO->fileName}',
					now(),
					'{$postingFileLogTO->status}',
					{$postingFileLogTO->vendorUId},
					null,
					'N',
					0,
					'{$postingFileLogTO->errorType}',
					'{$postingFileLogTO->errorMsg}',
					" . (($postingFileLogTO->principalUId == "") ? "null" : $postingFileLogTO->principalUId) . ",
					$postingFileLogTO->onlineFileProcessingUId
				  )";
        }

        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "file_log Successfully created.";
            $errorTO->identifier = $dbConn->dbGetLastInsertId();
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Error occurred creating file_log.";
            return $errorTO;
        }

        return $errorTO;
    }

    // the onlineFileProcessingUId is passed here as well in case the filewildcard changed from first run
    public function setFileLogResult($uId, $status, $errorMsg, $errorType, $onlineFileProcessingUId, $documentNumber = "", $clientDocumentNumber = "")
    {
        global $errorTO;
        global $dbConn;

        $dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time
        // NB !! The processed date MUST BE GMT !! as the BI trigger export file log (9) compares run dates against this and it expects GMT
        $sql = "update file_log
			  set processed_date=now(),
				  status='{$status}',
				  error_msg='" . mysqli_real_escape_string($this->dbConn->connection, substr($errorMsg, 0, 2048)) . "',
				  error_type='{$errorType}',
				  error_count=" . ((($errorType == ET_CUSTOMER) && ($status != FLAG_ERRORTO_SUCCESS)) ? "error_count+1" : "error_count") . ",
				  online_file_processing_uid={$onlineFileProcessingUId},
          document_number='{$documentNumber}',
          client_document_number='{$clientDocumentNumber}'
			  where uid = '{$uId}'";

        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "file_log Successfully Updated.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Error occurred updating file_log.";
            return $errorTO;
        }

        return $errorTO;
    }

    public function updateEmailLogEntry($UID, $status, $emailMapUID)
    {
        global $errorTO;
        global $dbConn;

        $sql = "update email_log
			  set status = '{$status}',
			  	  email_file_mapping = " . (int)$emailMapUID . "
			  where uid = " . (int)$UID;

        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "file_log Successfully Updated in setFileLogParticulars.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Error occurred updating file_log in setFileLogParticulars.";
            return $errorTO;
        }

        return $errorTO;
    }


    public function setFileLogParticulars($uId, $lineCount)
    {
        global $errorTO;
        global $dbConn;

        $dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

        $sql = "update file_log
			  set line_count = {$lineCount}
			  where uid = '{$uId}'";

        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "file_log Successfully Updated in setFileLogParticulars.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Error occurred updating file_log in setFileLogParticulars.";
            return $errorTO;
        }

        return $errorTO;
    }

    public function setFileLogVendorResult($uId, $vendorRemoved)
    {
        global $errorTO;
        global $dbConn;

        $dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

        $sql = "update file_log
			  set vendor_removed='{$vendorRemoved}',
                  vendor_removal_date=now()
			  where uid = '{$uId}'";
        echo "<br>";
        echo $sql;
        echo "<br>";
        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "file_log Successfully Updated.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Error occurred updating file_log." . $errorTO->description;
            return $errorTO;
        }

        return $errorTO;
    }

    // the backend is only permitted to update certain fields to prevent problems with overwriting critical flags like on_hold and novat
    public function setStoreFieldsFromBackend($postingStoreTO)
    {
        global $errorTO;
        global $dbConn;

        $dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

        $strippedDeliverName = CommonUtils::getStrippedValue($postingStoreTO->deliverName);

        $sql = "update principal_store_master
			  set deliver_name = '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->deliverName) . "',
				  stripped_deliver_name = '" . $strippedDeliverName . "',
				  deliver_add1 = '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->deliverAdd1) . "',
				  deliver_add2 = '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->deliverAdd2) . "',
				  bill_name = '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->billName) . "',
				  bill_add1 = '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->billAdd1) . "',
  				  bill_add2 = '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->billAdd2) . "',
				  delivery_day_uid = '" . $postingStoreTO->deliveryDay . "'
			  where uid = '{$postingStoreTO->principalStoreUId}'";

        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "Store Update from Backend Successful.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description .= "Error Updating Store from Backend.";
            return $errorTO;
        }

        return $errorTO;
    }

    public function purgeProcessedOrdersHolding($age)
    {
        global $dbConn;

        $sql = "DELETE FROM orders_holding
			    WHERE status IN ('D','S','E')
			        AND datediff(now(),processed_date) > {$age}";
        $errorTO = $dbConn->processPosting($sql, "");
        if($errorTO->isError()){
            $errorTO->description .= "error purging 'orders_holding' table";
            return $errorTO;
        }
        $dbConn->dbinsQuery("commit");

        //stores
        $sql = "DELETE os 
                    FROM orders_holding_store os
                    LEFT JOIN orders_holding oh on os.orders_holding_uid = oh.uid
                WHERE oh.uid IS NULL";
        $errorTO = $dbConn->processPosting($sql, "");
        if($errorTO->isError()){
            $errorTO->description .= "error purging 'orders_holding_store' table";
            return $errorTO;
        }
        $dbConn->dbinsQuery("commit");

        //special fields
        $sql = "DELETE ohsf 
                    FROM orders_holding_special_field ohsf
                    LEFT JOIN orders_holding oh on ohsf.orders_holding_uid = oh.uid
                WHERE oh.uid IS NULL";

        $errorTO = $dbConn->processPosting($sql, "");
        if($errorTO->isError()){
            $errorTO->description .= "error purging 'orders_holding_special_field' table";
            return $errorTO;
        }
        $dbConn->dbinsQuery("commit");

        //detail records
        $sql = "DELETE od 
                    FROM orders_holding_detail od
                    LEFT JOIN orders_holding oh on od.orders_holding_uid = oh.uid
                WHERE oh.uid IS NULL";

        $errorTO = $dbConn->processPosting($sql, "");
        if($errorTO->isError()){
            $errorTO->description .= "error purging 'orders_holding_detail' table";
            return $errorTO;
        }
        $dbConn->dbinsQuery("commit");

        return ErrorTO::NewSuccess("successfully purged orders_holding_* tables");
    }

    /*
     *
     * START : These are set by processing script, as opposed to the postTransactionDAO->setOrdersHolding... which is set by user
     *
     * WARNING : These use a sql user VAR @userId
     *
     */

    public function setOrdersHoldingPrincipalStoreUId($principalUId, $oHUId, $pSMUId)
    {
        global $errorTO;
        global $dbConn;

        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $dbConn->dbQuery("SET @userId={$userId}");

        $sql = "update orders_holding
			  set principal_store_uid = '{$pSMUId}'
			  where uid = '{$oHUId}'
			  and   principal_uid = '{$principalUId}'";

        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "setOrdersHoldingPrincipalStoreUId Successful.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description .= "Error setOrdersHoldingPrincipalStoreUId.";
            return $errorTO;
        }

        return $errorTO;
    }

    public function setOrdersHoldingProformaStatus($principalUId, $oHUId, $status, $statusMsg)
    {
        global $errorTO;
        global $dbConn;

        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $dbConn->dbQuery("SET @userId={$userId}");
        $dbConn->dbQuery("SET time_zone='+0:00'");

        // R.A / R.A.MP / SUSP never executes (unless proforma) this section as they are excluded from processing until released, so if you need to be set to Q then you must insert it with Q
        $sql = "update orders_holding
              set proforma_status = '{$status}',
                  proforma_status_msg = substring('" . mysqli_real_escape_string($this->dbConn->connection, $statusMsg) . "',1,256)
              where uid = '{$oHUId}'
              and   principal_uid = '{$principalUId}'";

        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "setOrdersHoldingStatus Successful.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description .= "Error setOrdersHoldingStatus.";
            return $errorTO;
        }

        return $errorTO;
    }

    public function setOrdersHoldingStatus($principalUId, $oHUId, $statusLevel, $status, $statusMsg, $orderSequenceNumber = "")
    {
        global $errorTO;
        global $dbConn;

        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $dbConn->dbQuery("SET @userId={$userId}");
        $dbConn->dbQuery("SET time_zone='+0:00'");

        // R.A / R.A.MP / SUSP never executes (unless proforma) this section as they are excluded from processing until released, so if you need to be set to Q then you must insert it with Q
        $sql = "update orders_holding
          set status_level = '{$statusLevel}',
              exception_notified = if('{$status}' not in ('" . FLAG_ERRORTO_SUCCESS . "','') and
                        (ifnull(status,'')!='{$status}' or ifnull(status_msg,'')!='" . mysqli_real_escape_string($this->dbConn->connection, $statusMsg) . "'),'" . FLAG_STATUS_QUEUED . "',exception_notified),
              status = '{$status}', -- this MUST come AFTER the exception_notified field as you are using this initial value in the comparison
              status_msg = substring('" . mysqli_real_escape_string($this->dbConn->connection, $statusMsg) . "',1,256),
              processed_date = now(),
              order_sequence_number = " . ((empty($orderSequenceNumber)) ? "NULL" : $orderSequenceNumber) . "
          where uid = '{$oHUId}'
          and   principal_uid = '{$principalUId}'";


        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "setOrdersHoldingStatus Successful.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description .= "Error setOrdersHoldingStatus.";
            return $errorTO;
        }

        return $errorTO;
    }

    // should only be called when you are determining the document origin for the first time
    public function setOrdersHoldingDocumentOrigin($principalUId, $oHUId, $status)
    {
        global $errorTO;
        global $dbConn;

        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $dbConn->dbQuery("SET @userId={$userId}");

        $dbConn->dbQuery("SET time_zone='+0:00'");

        if ($status !== false) $set[] = "status = '{$status}'";
        $set[] = "document_origin_queried = 'Y'";

        $sql = "update orders_holding
      set " . (implode(",", $set)) . "
      where uid = '{$oHUId}'
      and   principal_uid = '{$principalUId}'";

        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "setOrdersHoldingDocumentOrigin Successful.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description .= "Error setOrdersHoldingDocumentOrigin.";
            return $errorTO;
        }

        return $errorTO;
    }

    public function setOrdersHoldingDetailStatusItem($ohdUId, $status)
    {
        global $errorTO;
        global $dbConn;

        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $dbConn->dbQuery("SET @userId={$userId}");

        $sql = "update orders_holding_detail
			  set exception_notified = if('{$status}'!='' and ifnull(status,'')!='{$status}','" . FLAG_STATUS_QUEUED . "',exception_notified),
				  status = '{$status}'
			  where uid = '{$ohdUId}'";

        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "setOrdersHoldingDetailStatus Successful.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description .= "Error setOrdersHoldingDetailStatus.";
            return $errorTO;
        }

        return $errorTO;
    }


    public function setOrdersHoldingPrincipalProductUId($ohdUId, $pPUId, $pPItemsPerCase = 1, $convert_cases_to_units = 'N')
    {
        global $errorTO;
        global $dbConn;

        if (!isset($_SESSION)) session_start();
        $userId = $_SESSION['user_id'];
        $dbConn->dbQuery("SET @userId={$userId}");

        $sql = "update orders_holding_detail
                       set principal_product_uid = '{$pPUId}',
                           items_per_case = '{$pPItemsPerCase}',
                           convert_cases_to_units = '{$convert_cases_to_units}'
			  where uid = '{$ohdUId}'";

        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "setOrdersHoldingPrincipalProductUId Successful.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description .= "Error setOrdersHoldingPrincipalProductUId.";
            return $errorTO;
        }

        return $errorTO;
    }

    /*
     *
     * END : These are set by processing script, as opposed to the postTransactionDAO->setOrdersHolding... which is set by user
     *
     */

    public function setOrdersHoldingPriceDiffNotified($ohdUId, $status)
    {
        global $errorTO;
        global $dbConn;

        $sql = "update orders_holding_detail
			  set price_diff_notified = '{$status}'
			  where uid = '{$ohdUId}'";

        $errorTO = $dbConn->processPosting($sql, "");
        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "setOrdersHoldingPriceDiffNotified Successful.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description .= "Error setOrdersHoldingPriceDiffNotified.";
            return $errorTO;
        }

        return $errorTO;
    }


    public function removeDeliveryDaysforDepot($depotUid)
    {

        global $errorTO, $dbConn;

        $dbConn->dbQuery("SET time_zone='+0:00'");

        // check if already exists in synch tables. If so, then LOCK it.
        $sql = "delete from `delivery_day` where `depot_uid` = '" . mysqli_real_escape_string($this->dbConn->connection, $depotUid) . "' AND imported_date < (NOW() - INTERVAL 2 HOUR)";
        $dbConn->dbinsQuery($sql);
        if (!$dbConn->dbQueryResult) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Could not delete old depot (Uid: " . $depotUid . ") in delivery day table. " . mysql_info($dbConn->connection);
            return $errorTO;
        }

    }

    public function postBulkDeliveryDays($postingTOArr)
    {

        global $errorTO, $dbConn;

        $dbConn->dbQuery("SET time_zone='+0:00'");

        $sql = "INSERT INTO `delivery_day`
				  (
				`depot_uid`,
				`deliver_name`,
				`delivery_area`,
				`day_uid`,
				`imported_date`
				  )
			VALUES ";

        $sqlValueArr = array();

        foreach ($postingTOArr as $postingTO) {
            $sqlValueArr[] = "(" .
                "" . mysqli_real_escape_string($this->dbConn->connection, $postingTO->depotUid) . "," .
                "'" . mysqli_real_escape_string($this->dbConn->connection, $postingTO->deliverName) . "'," .
                "'" . mysqli_real_escape_string($this->dbConn->connection, $postingTO->deliveryArea) . "'," .
                "" . mysqli_real_escape_string($this->dbConn->connection, $postingTO->dayUid) . "," .
                "NOW()" .
                ")";
        }

        $sql = $sql . join(', ', $sqlValueArr);


        $errorTO = $dbConn->processPosting($sql, "");

        if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $errorTO->description = "Delivery Day Successfully created.";
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Failed to insert Delivery Day Query: " . mysql_error($dbConn->connection) . $errorTO->description . "\n" . $sql . "\n";
        }

        return $errorTO;
    }


    public function validatePostTripsheet($postingTripsheetTO)
    {


        if (empty($postingTripsheetTO->tripsheetRef)) {
            return 'Tripsheet Ref No is empty/invalid!';
        }

        if (empty($postingTripsheetTO->depotUid)) {
            return 'Depot UID is empty/invalid!';
        }

        if (empty($postingTripsheetTO->principalUidArr) || empty($postingTripsheetTO->documentNumberArr) || empty($postingTripsheetTO->storeNameArr)) {
            return 'One of the Tripsheet Detail Array is empty/invalid!';
        }

        return true;  //SINGLE SUCCESS POINT

    }


    public function postTripsheet($postingTripsheetTO)
    {


        $resultOK = $this->validatePostTripsheet($postingTripsheetTO);
        if ($resultOK) {

            global $errorTO;

            $this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

            $sql = "INSERT INTO `tripsheet_header`
                    (
                            `tripsheet_ref`,
                            `tripsheet_date`,
                            `truck_reg_number`,
                            `stop_count`,
                            `depot_uid`,
                            `incoming_filename`,
                            `imported_timestamp`
                    ) VALUES ( " .
                "'" . mysqli_real_escape_string($this->dbConn->connection, $postingTripsheetTO->tripsheetRef) . "',
                            '" . mysqli_real_escape_string($this->dbConn->connection, $postingTripsheetTO->date) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingTripsheetTO->truckRegNumber) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingTripsheetTO->stopCount) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingTripsheetTO->depotUid) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, substr(trim($postingTripsheetTO->incomingFilename), 0, 30)) . "',
                    NOW()
            );";

            $errorTO = $this->dbConn->processPosting($sql, "");

            if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {

                $errorTO->description = "Tripsheet header row successful.";
                $errorTO->identifier = $this->dbConn->dbGetLastInsertId();
                $headerUid = $errorTO->identifier;
                //INSERT DETAIL ROWS.

                if (count($postingTripsheetTO->principalUidArr) == count($postingTripsheetTO->documentNumberArr) &&
                    count($postingTripsheetTO->principalUidArr) == count($postingTripsheetTO->storeNameArr)
                    && !empty($headerUid)) {

                    $sql = "INSERT INTO `tripsheet_detail`
        	   		(
        	   			`tripsheet_header_uid`,
        	   			`principal_uid`,
        	   			`document_number`,
        	   			`store_name`
        	   		) VALUES";

                    $sqlArr = array();
                    foreach ($postingTripsheetTO->principalUidArr as $key => $principalUid) {

                        $sqlArr[] = " (
      	      		   '" . mysqli_real_escape_string($this->dbConn->connection, $headerUid) . "'," .
                            "'" . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "'," .
                            "'" . mysqli_real_escape_string($this->dbConn->connection, $postingTripsheetTO->documentNumberArr[$key]) . "'," .
                            "'" . mysqli_real_escape_string($this->dbConn->connection, $postingTripsheetTO->storeNameArr[$key]) . "'
      	      		  ) ";

                    }
                    $sql .= join(', ', $sqlArr);


                    $errorTO = $this->dbConn->processPosting($sql, "");

                    if ($errorTO->type == FLAG_ERRORTO_SUCCESS) {
                        $errorTO->description = "Tripsheet import successful.";
                    } else {
                        $errorTO->type = FLAG_ERRORTO_ERROR;
                        $errorTO->description .= " MySQL Tripsheet Error 'unsynched_tripsheet_detail'.";
                        return $errorTO;
                    }

                } else {
                    $errorTO->type = FLAG_ERRORTO_ERROR;
                    $errorTO->description .= " Error mismatch on detail arrays.";
                    return $errorTO;
                }

            } else {

                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description .= " MySQL Tripsheet Error 'unsynched_tripsheet_header'." . $sql;
                return $errorTO;

            }

        }

        return $errorTO;
    }


}

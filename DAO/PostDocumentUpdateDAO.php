<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');


class PostDocumentUpdateDAO {


  public  $errorTO;
  private $dbConn;


  function __construct($dbConn) {
    $this->dbConn = $dbConn;
    $this->errorTO = new ErrorTO;
  }


  public function postDocumentUpdate($postDocumentUpdateTO){
  	
    $sql = "INSERT INTO document_update (
              `document_master_uid`,
              `source_document_master_uid`,
              `update_type_uid`,
              `additional_type`,
              `principal_lookup`,
              `principal_uid`,
              `depot_lookup`,
              `depot_uid`,
              `skip_depot_update`,
              `document_number`,
              `source_document_number`,
              `merge_date`,
              `merge_time`,
              `document_status_lookup`,
              `document_status_uid`,
              `created_datetime`,
              `incoming_filename`,
              `file_log_uid`,
              `invoice_date`,
              `delivery_date`,
              `due_delivery_date`,
              `invoice_number`,
              `pod_reason_lookup`,
              `document_type_uid`,
              `delivery_day_uid`,
              `document_source_optional`,
              `reference`,
              `claim_number`,
              `grv_number`,
              `additional_details`,
              `pages`
           ) VALUES (
              ".((empty($postDocumentUpdateTO->documentMasterUid))?"NULL":$postDocumentUpdateTO->documentMasterUid).",
              ".((empty($postDocumentUpdateTO->sourceDocumentMasterUid))?"NULL":$postDocumentUpdateTO->sourceDocumentMasterUid).",
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->updateTypeUid)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->additionalType)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->principalLookup)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->principalUId)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->depotLookup)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->depotUId)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->skipDepotUpdate)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->documentNumber)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->sourceDocumentNumber)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->mergeDate)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->mergeTime)."',
              '".mysqli_real_escape_string($this->dbConn->connection, trim($postDocumentUpdateTO->documentStatusLookup))."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->documentStatusUId)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->createdDatetime)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->incomingFilename)."',
              ".((empty($postDocumentUpdateTO->fileLogUId))?"NULL":$postDocumentUpdateTO->fileLogUId).",
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->invoiceDate)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->deliveryDate)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->dueDeliveryDate)."',
              '".mysqli_real_escape_string($this->dbConn->connection, trim($postDocumentUpdateTO->invoiceNumber))."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->podReasonLookup)."',
              ".(($postDocumentUpdateTO->documentTypeUId=="")?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->documentTypeUId)).",
              ".(($postDocumentUpdateTO->deliveryDayUId=="")?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->deliveryDayUId)).",
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->documentSourceOptional)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->reference)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->claimNumber)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->grvNumber)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->additionalDetails)."',
              '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->pages)."'
           )";

    $this->errorTO = $this->dbConn->processPosting($sql,'');

    $uid = "";
    if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
      echo $this->errorTO->description;
      $this->errorTO->description = "ERROR occurred inserting into document_update: ".$this->errorTO->description;
      return $this->errorTO;
    } else {
      $uid = $this->dbConn->dbGetLastInsertId();
    }

    //confirmations and pods have no detail rows.
    if(is_array($postDocumentUpdateTO->detailArr) && count($postDocumentUpdateTO->detailArr) > 0){
      // insert the detail
      $sql = "INSERT INTO document_update_detail
                (
                `document_update_uid`,
                `product_uid`,
                `product_code`,
                `line_no`,
                `page_no`,
                `ordered_qty`,
                `document_qty`,
                `delivery_qty`,
                `pod_reason_lookup`,
                `list_price`,
                `discount_value`,
                `nett_price`,
                `extended_price`,
                `vat_amount`,
                `vat_rate`,
                `total`
             ) VALUES ";

           $sqlDArr = array();
           foreach($postDocumentUpdateTO->detailArr as $no => $empty) {

              // make sure pricing fields are numeric / contain a zero if blank. If a non-numeric is passed then unfortunaltely this loses the val.
              $postDocumentUpdateTO->detailArr[$no]->listPrice*=1;
              $postDocumentUpdateTO->detailArr[$no]->discountValue*=1;
              $postDocumentUpdateTO->detailArr[$no]->nettPrice*=1;
              $postDocumentUpdateTO->detailArr[$no]->extendedPrice*=1;
              $postDocumentUpdateTO->detailArr[$no]->vatAmount*=1;
              $postDocumentUpdateTO->detailArr[$no]->total*=1;

              $sqlDArr[] = "(
                {$uid},
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->productUId)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->productCode)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->lineNo)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->pageNo)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->orderedQty)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->documentQty)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->deliveredQty)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->podReasonLookup)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->listPrice)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->discountValue)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->nettPrice)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->extendedPrice)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->vatAmount)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->vatRate)."',
                '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->detailArr[$no]->total)."'
              )";
           }

      $this->errorTO = $this->dbConn->processPosting($sql . join(',', $sqlDArr),'');

      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "ERROR occurred inserting into document_update_detail: ".$this->errorTO->description;
        return $this->errorTO;
      }
    }

    // batches
    if(is_array($postDocumentUpdateTO->batchArr) && count($postDocumentUpdateTO->batchArr) > 0){
      // insert the detail
      $sql = "INSERT INTO document_update_batch
                (
                document_update_uid,
              	document_number,
              	invoice_number,
              	product_code,
              	batch_reference_1,
              	batch_reference_2
             ) VALUES ";

      $sqlDArr = array();
      foreach($postDocumentUpdateTO->batchArr as $no => $empty) {

        $sqlDArr[] = "(
          {$uid},
          '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->batchArr[$no]->documentNumber)."',
          '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->batchArr[$no]->invoiceNumber)."',
          '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->batchArr[$no]->productCode)."',
          '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->batchArr[$no]->batchReference1)."',
          '".mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateTO->batchArr[$no]->batchReference2)."'
        )";
      }

      $this->errorTO = $this->dbConn->processPosting($sql . join(',', $sqlDArr),'');

      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "ERROR occurred inserting into document_update_batch: ".$this->errorTO->description;
        return $this->errorTO;
      }
    }

    $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
    return $this->errorTO;

  }



    public function setDocumentUpdateError($duUId, $msg, $obj) {

      $this->dbConn->dbQuery("SET time_zone='+0:00'");

      $sql="UPDATE document_update
                SET
                  processed_datetime = NOW(),
                  processed_status = 'E',
                  processed_msg = '" . mysqli_real_escape_string($this->dbConn->connection, substr($msg, 0, 300)) . "'";

      if($obj!=false && is_array($obj) && isset($obj['principal_uid'])){
      $sql.="    , principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $obj['principal_uid']) . ",
                   depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $obj['depot_uid']) . ",
                   document_status_uid = " . mysqli_real_escape_string($this->dbConn->connection, $obj['document_status_uid']) . ",
                   pod_reason_uid = " . mysqli_real_escape_string($this->dbConn->connection, $obj['pod_reason_uid']) . "";
      }

      $sql.=" WHERE uid = '{$duUId}'";


      $this->errorTO = $this->dbConn->processPosting($sql,"");
      if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "setDocumentUpdateError Successful.";
      } else {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description .= "Error setDocumentUpdateError.";
        return $this->errorTO;
      }

      return $this->errorTO;

    }



    public function setDocumentUpdateSuccess($duUId, $itemArray) {

      $this->dbConn->dbQuery("SET time_zone='+0:00'");

      $sql="UPDATE document_update
                SET
                  processed_datetime = NOW(),
                  processed_status = 'S',
                  processed_msg = '" . mysqli_real_escape_string($this->dbConn->connection, substr($itemArray['description'],0,300)) . "',
                  principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $itemArray['principal_uid']) . ",
                  depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $itemArray['depot_uid']) . ",
                  document_status_uid = " . mysqli_real_escape_string($this->dbConn->connection, $itemArray['document_status_uid']) . ",
                  document_master_uid = " . (($itemArray['document_master_uid']=="")?"NULL":$itemArray['document_master_uid']) . ",
                  document_updated_flag = " . mysqli_real_escape_string($this->dbConn->connection, $itemArray['document_updated_flag']) . ",
                  pod_reason_uid = " . mysqli_real_escape_string($this->dbConn->connection, $itemArray['pod_reason_uid']) . "
            WHERE uid = '{$duUId}'";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
      if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "setDocumentUpdateError Successful.";
      } else {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description .= "Error setDocumentUpdateError.";
        return $this->errorTO;
      }

      return $this->errorTO;

    }


    public function setDocumentUpdatePrincipalUId($duUId, $principalUId) {

      $this->dbConn->dbQuery("SET time_zone='+0:00'");

      $sql="UPDATE document_update
                SET
                  principal_uid = {$principalUId}
            WHERE uid = '{$duUId}'";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
      if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
      $this->errorTO->description = "setDocumentUpdateError Successful.";
      } else {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description .= "Error setDocumentUpdateError.";
        return $this->errorTO;
      }

      return $this->errorTO;

    }


    public function setDocumentConfirmation($postDocumentUpdateConfirmationTO, $depotRedirect = false){

      $set=array();
      $set[]=" m.merged_date = IF(m.merged_date = '0000-00-00', '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateConfirmationTO->mergedDate)."', m.merged_date) ";
      $set[]=" m.merged_time = IF(m.merged_time = '00:00:00',   '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateConfirmationTO->mergedTime)."', m.merged_time) ";
      $set[]=" m.confirmation_file = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateConfirmationTO->confirmationFile)."'";
      $set[]=" p.delivery_day_uid = " . (($postDocumentUpdateConfirmationTO->deliveryDayUId=="")?"p.delivery_day_uid":mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateConfirmationTO->deliveryDayUId));

      if($postDocumentUpdateConfirmationTO->dueDeliveryDate != '0000-00-00'){ //may be blank in the case of edi invalid status'es
        $set[]=" h.due_delivery_date = '" . $postDocumentUpdateConfirmationTO->dueDeliveryDate . "'";
      }


      if ($depotRedirect) {
        $set[]="m.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateConfirmationTO->depotUId);  // for redirects
        $set[]="p.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateConfirmationTO->depotUId);  // for redirects
      }

      $sql="UPDATE document_master m
              INNER JOIN document_header h on m.uid = h.document_master_uid
              INNER JOIN principal_store_master p on h.principal_store_uid = p.uid and p.principal_uid = m.principal_uid
              SET
                ".(implode(",",$set))."
             WHERE m.uid = {$postDocumentUpdateConfirmationTO->dmUId}
              AND m.principal_uid = {$postDocumentUpdateConfirmationTO->principalUId}";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
      if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "setDocumentUpdateError Successful.";
        return $this->errorTO;
      } else {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description .= "Error setDocumentUpdateError.";
        return $this->errorTO;
      }

      return $this->errorTO;

    }

    public function setDocumentCredited($principalUId, $dmUId, $creditNoteFile, $creditNoteDocumentNumber){

      // note that this does not update document_status_uid to Dirty POD because you have to call setDocumentStatus as that does other stuff too
      $sql="UPDATE document_master a
                   INNER JOIN document_header b on a.uid = b.document_master_uid
              SET
                a.credit_note_file = '" . mysqli_real_escape_string($this->dbConn->connection, $creditNoteFile) . "',
                b.source_document_number = '" . mysqli_real_escape_string($this->dbConn->connection, $creditNoteDocumentNumber) . "'
             WHERE a.uid = {$dmUId}
             AND   a.principal_uid = {$principalUId}";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
      if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "setDocumentCredited Successful.";
        return $this->errorTO;
      } else {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description .= "Error setDocumentCredited.";
        return $this->errorTO;
      }

      return $this->errorTO;

    }


    public function setDocumentInvoice($postDocumentUpdateInvoiceTO){

      $podReason = $postDocumentUpdateInvoiceTO->podReasonUid;
      $podReason = ($podReason!=0 && $podReason!='') ? (mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceTO->podReasonUid)) : ("NULL");

      $sql="UPDATE document_master m
              INNER JOIN document_header h on m.uid = h.document_master_uid
              SET
                m.invoice_file = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceTO->invoiceFile) . "',
                h.invoice_date = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceTO->invoiceDate) . "',
                h.invoice_number = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceTO->invoiceNumber) . "',
                h.pod_reason_uid = " . $podReason . ",
                h.pages = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceTO->pages) . "'
             WHERE m.uid = {$postDocumentUpdateInvoiceTO->dmUId}
              AND m.principal_uid = {$postDocumentUpdateInvoiceTO->principalUId}";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
      if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "setDocumentUpdateError Successful.";
        return $this->errorTO;
      } else {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description .= "Error setDocumentUpdateError.";
        return $this->errorTO;
      }

      return $this->errorTO;

    }


    public function setDocumentInvoiceTotal($postDocumentUpdateInvoiceTotalTO){


      $sql="UPDATE document_master m
              INNER JOIN document_header h on m.uid = h.document_master_uid
              SET
                h.cases = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceTotalTO->cases) . "',
                h.exclusive_total = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceTotalTO->exclusiveTotal) . "',
                h.vat_total = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceTotalTO->vatTotal) . "',
                h.invoice_total = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceTotalTO->invoiceTotal) . "'
             WHERE m.uid = {$postDocumentUpdateInvoiceTotalTO->dmUId}
              AND m.principal_uid = {$postDocumentUpdateInvoiceTotalTO->principalUId}";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
      if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "setDocumentUpdateError Successful.";
        return $this->errorTO;
      } else {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description .= "Error setDocumentUpdateError.";
        return $this->errorTO;
      }

      return $this->errorTO;

    }

    public function setDocumentHeaderTotals($dmUId,$qtyFieldName) {
      $this->dbConn->dbQuery("SET time_zone='+0:00'");

      $sql="UPDATE document_header dh,
                     (select sum(dd.{$qtyFieldName}) sdq, sum(dd.extended_price) sep, sum(dd.vat_amount) sva, sum(dd.total) st
                      from   document_detail dd
                      where  dd.document_master_uid = {$dmUId}
                      group by dd.document_master_uid) dd
                set   cases = sdq,
                      exclusive_total = sep,
                      vat_total = sva,
                      invoice_total = st
                where dh.document_master_uid = {$dmUId}";

      $this->errorTO = $this->dbConn->processPosting($sql,"");

      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description="setDocumentHeaderTotals failed : ".$this->errorTO->description;
        return $this->errorTO;
      }

      $this->errorTO->description="setDocumentHeaderTotals successfully set";
      return $this->errorTO;
    }


    public function setDocumentInvoiceDetail($postDocumentUpdateInvoiceDetailTO){

      $set=array();
      if ($postDocumentUpdateInvoiceDetailTO->documentQty!==false) $set[]="document_qty = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceDetailTO->documentQty) . "'";
      if ($postDocumentUpdateInvoiceDetailTO->deliveredQty!==false) $set[]="delivered_qty = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceDetailTO->deliveredQty) . "'";
      if ($postDocumentUpdateInvoiceDetailTO->buyerDeliveredQty!==false) $set[]="buyer_delivered_qty = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceDetailTO->buyerDeliveredQty) . "'";

      if ($postDocumentUpdateInvoiceDetailTO->extendedPrice!==false) $set[]="extended_price = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceDetailTO->extendedPrice) . "'";
      if ($postDocumentUpdateInvoiceDetailTO->vatAmount!==false) $set[]="vat_amount = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceDetailTO->vatAmount) . "'";
      if ($postDocumentUpdateInvoiceDetailTO->total!==false) $set[]="total = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdateInvoiceDetailTO->total) . "'";

      $sql="UPDATE document_detail
              SET
                ".implode(",",$set)."
             WHERE uid = {$postDocumentUpdateInvoiceDetailTO->ddUId}";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
      if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description .= "Error setDocumentUpdateError.";
        return $this->errorTO;
      }

      $this->errorTO->description = "setDocumentUpdateError Successful.";
      return $this->errorTO;

    }


    // resets products on the specific document that are not in the parameter list passed (eg. inv confirmations where only invoiced products are sent thru)
    public function setExcludedDocumentInvoiceDetail($dmUId, $ppUIdList){


      $sql="UPDATE document_detail
              SET
                document_qty = 0,
                delivered_qty = 0,
                extended_price = 0,
                vat_amount = 0,
                total = 0
              WHERE document_master_uid = {$dmUId}
              AND   product_uid not in ({$ppUIdList})";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
      if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description .= "Error setExcludedDocumentInvoiceDetail.";
        return $this->errorTO;
      }

      $this->errorTO->description = "setExcludedDocumentInvoiceDetail Successful.";
      return $this->errorTO;

    }


    public function setDocumentPOD($postDocumentUpdatePODTO){

      $podReason = (!empty($postDocumentUpdatePODTO->podReasonUid)) ? (mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdatePODTO->podReasonUid)) : ("NULL");

      $sql="UPDATE document_master m
              INNER JOIN document_header h on m.uid = h.document_master_uid
              SET
                h.delivery_date = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdatePODTO->deliveryDate) . "',
                h.pod_reason_uid = " . $podReason . ",
                h.grv_number = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdatePODTO->grvNumber) . "',
                h.claim_number = '" . mysqli_real_escape_string($this->dbConn->connection, $postDocumentUpdatePODTO->claimNumber) . "'
             WHERE m.uid = {$postDocumentUpdatePODTO->dmUId}
              AND m.principal_uid = {$postDocumentUpdatePODTO->principalUId}";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
      if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "setDocumentUpdateError Successful.";
        return $this->errorTO;
      } else {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description .= "Error setDocumentUpdateError.";
        return $this->errorTO;
      }

      return $this->errorTO;

    }


    public function purgeProcessedUpdates() {

      $sql=" delete from document_update
             where  created_datetime < (curdate() - interval 180 day)
                    and  processed_status = 'S'";

      $this->errorTO = $this->dbConn->processPosting($sql,"");

      if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "Successfully purged processed.";
      } else  {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description .= "Error purging processed.";
        return $this->errorTO;
      }

    }
}

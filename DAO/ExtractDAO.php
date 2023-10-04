<?php

include_once 'ROOT.php';
include_once $ROOT.'PHPINI.php';
include_once $ROOT.$PHPFOLDER.'properties/Constants.php';
include_once $ROOT.$PHPFOLDER.'libs/CommonUtils.php';
include_once $ROOT.$PHPFOLDER.'TO/ErrorTO.php';
include_once $ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php';


class ExtractDAO {

  private $dbConn;

  function __construct($dbConn) {
    $this->dbConn = $dbConn;
  }

  //online Export Mapping
  public function getDailyExtractInvoicedOrders($principalUId, 
                                                $notificationUId, 
                                                $orderBy="se.status DESC, a.uid, c.uid") {

    $sql = "select se.uid as se_uid, 
                   a.uid  as dm_uid, 
                   a.document_number, 
                   a.alternate_document_number, 
                   a.document_type_uid, 
                   a.depot_uid, 
                   dpt.name as depot_name,
                   dpt.code as depot_code,
                   b.order_date, 
                   b.invoice_date, 
                   b.delivery_date,
                   b.due_delivery_date, 
                   b.document_status_uid, 
                   b.data_source, 
                   b.source_document_number,
                   b.principal_store_uid,
                   b.requested_delivery_date,
                   b.off_invoice_discount_type,
                   b.off_invoice_discount,
                   b.trip_transporter_uid,
                   b.captured_by,
                   psm.deliver_name, 
                   psm.deliver_add1, 
                   psm.deliver_add2, 
                   psm.deliver_add3,
                   psm.bill_name, 
                   psm.bill_add1, 
                   psm.bill_add2, 
                   psm.bill_add3, 
                   psm.old_account,
                   psm.branch_code, 
                   psm.vat_number, 
                   psm.tel_no1, 
                   psm.tel_no2, 
                   psm.email_add, 
                   psm.principal_chain_uid,
                   b.invoice_number, 
                   b.exclusive_total, 
                   b.vat_total, 
                   b.invoice_total,
                   c.uid as 'ddUid',
                   c.product_uid, 
                   pp.product_code, 
                   pp.alt_code, 
                   pp.product_description, 
                   pp.items_per_case,
                   pp.outer_casing_gtin,
                   pp.weight,
                   pp.allow_decimal,
                   pp.product_guid,
                   c.ordered_qty, 
                   c.document_qty, 
                   c.delivered_qty, 
                   c.selling_price, 
                   c.discount_value, 
                   c.net_price, 
                   c.extended_price,
                   c.vat_amount, 
                   c.vat_rate, 
                   c.total, 
                   c.line_no, 
                   c.client_line_no, 
                   c.product_code as `detail_product_code`, 
                   c.batch AS 'Batch',
                   b.customer_order_number, 
                   b.grv_number, 
                   b.claim_number,
                   r.uid as reason_uid, 
                   r.description as reason_description, 
                   a.incoming_file, 
                   b.buyer_account_reference,
                   pp.major_category, 
                   pp.packing, 
                   a.client_document_number, 
                   c.ws_unique_creator_id as 'ohd_ws_unique_creator_id',
                   psm.ean_code, 
                   a.additional_type, 
                   pp.non_stock_item,
                   o.delivery_instructions,
                   dg.sku_gtin, 
                   dg.outercasing_gtin,
                   ppl.Region as 'pnpRegion',
                   ppl.Region_name as 'pnpRegName',
                   area.description AS 'Area'
           from document_master a
           left join orders o on a.order_sequence_no = o.order_sequence_no
           inner join document_header b on a.uid = b.document_master_uid
           left join reason_code r on b.pod_reason_uid = r.uid
           inner join document_detail c on a.uid = c.document_master_uid
           inner join smart_event se on a.uid = se.data_uid and se.`type` = '".SE_EXTRACT."' 
                                                            and se.type_uid = {$notificationUId} 
                                                            and se.status IN ('" . FLAG_STATUS_QUEUED . "', '" . FLAG_ERRORTO_ERROR . "') #retry extraction errors
           left join depot dpt on a.depot_uid = dpt.uid
           left join principal_store_master psm on b.principal_store_uid = psm.uid
           left join principal_product pp on c.product_uid = pp.uid
           left join principal_product_depot_gtin dg on dg.principal_product_uid = pp.uid
           left join pnp_store_list ppl on ppl.GLN = psm.ean_code
           left join area ON area.uid = psm.area_uid
           where  a.principal_uid = '{$principalUId}' 
           and  b.invoice_date > curdate() - interval 50 day 
           order  by {$orderBy}";
           //echo $sql;
    return $this->dbConn->dbGetAll($sql);

  }

  //online Export Mapping
  public function getDailyExtractInvoicedOrdersReverse($principalUId, $notificationUId, $orderBy="se.status DESC, a.uid, c.uid") {

    $sql = "select se.uid as se_uid, 
                   a.uid  as dm_uid, 
                   a.document_number, 
                   a.alternate_document_number, 
                   if(a.document_type_uid = 1,1,4) as 'document_type_uid', 
                   a.depot_uid, 
                   dpt.name as depot_name,
                   dpt.code as depot_code,
                   b.order_date, 
                   b.invoice_date, 
                   b.delivery_date,
                   b.due_delivery_date, 
                   b.document_status_uid, 
                   b.data_source, 
                   b.source_document_number,
                   b.principal_store_uid,
                   b.requested_delivery_date,
                   b.off_invoice_discount,
                   b.trip_transporter_uid,
                   psm.deliver_name, 
                   psm.deliver_add1, 
                   psm.deliver_add2, 
                   psm.deliver_add3,
                   psm.bill_name, 
                   psm.bill_add1, 
                   psm.bill_add2, 
                   psm.bill_add3, 
                   psm.old_account,
                   psm.branch_code, 
                   psm.vat_number, 
                   psm.tel_no1, 
                   psm.tel_no2, 
                   psm.email_add, 
                   psm.principal_chain_uid,
                   b.invoice_number, 
                   b.exclusive_total, 
                   b.vat_total, 
                   b.invoice_total,
                   c.uid as 'ddUid',
                   c.product_uid, 
                   pp.product_code, 
                   pp.alt_code, 
                   pp.product_description, 
                   pp.items_per_case,
                   pp.outer_casing_gtin,
                   pp.weight,
                   c.ordered_qty, 
                   c.document_qty, 
                   c.delivered_qty, 
                   c.selling_price, 
                   c.discount_value, 
                   c.net_price, 
                   c.extended_price,
                   c.vat_amount, 
                   c.vat_rate, 
                   c.total, 
                   c.line_no, 
                   c.client_line_no, 
                   c.product_code as `detail_product_code`, 
                   c.batch AS 'Batch',
                   b.customer_order_number, 
                   b.grv_number, 
                   b.claim_number,
                   r.uid as reason_uid, 
                   r.description as reason_description, 
                   a.incoming_file, 
                   b.buyer_account_reference,
                   pp.major_category, 
                   pp.packing, 
                   a.client_document_number, 
                   c.ws_unique_creator_id as 'ohd_ws_unique_creator_id',
                   psm.ean_code, 
                   a.additional_type, 
                   pp.non_stock_item,
                   o.delivery_instructions,
                   dg.sku_gtin, 
                   dg.outercasing_gtin
           from document_master a
           left join orders o on a.order_sequence_no = o.order_sequence_no
           inner join document_header b on a.uid = b.document_master_uid
           left join reason_code r on b.pod_reason_uid = r.uid
           inner join document_detail c on a.uid = c.document_master_uid
           inner join smart_event se on a.uid = se.data_uid and se.`type` = '".SE_EXTRACT."' 
                                                            and se.type_uid = {$notificationUId} 
                                                            and se.status IN ('" . FLAG_STATUS_CLOSED . "') #retry extraction errors
           left join depot dpt on a.depot_uid = dpt.uid
           left join principal_store_master psm on b.principal_store_uid = psm.uid
           left join principal_product pp on c.product_uid = pp.uid
           left join principal_product_depot_gtin dg on dg.principal_product_uid = pp.uid
           where  a.principal_uid = '{$principalUId}'
           AND    a.document_type_uid in (1, 4)
           AND    b.invoice_date between '2023-03-01' and '2023-03-31'
           order  by {$orderBy}";
  echo "<pre>";           
  echo $sql;

    return $this->dbConn->dbGetAll($sql);

  }


  public function getDailyExtractInvoicedOrdersWithParms($principalUId, 
                                                         $notificationUId, 
                                                         $whId, 
                                                         $chainUIdIn, 
                                                         $orderBy="se.status DESC, a.uid DESC, c.uid",
                                                         $docType='') {
  	
    if($whId == '') {
        $whVar = "";
    } else {
        $whVar = "and    a.depot_uid in (". mysqli_real_escape_string($this->dbConn->connection, $whId) . ")";
    }
  	
     if($chainUIdIn == '') {
        $chVar = "";
    } else {
        $chVar = "and    a.depot_uid in (". mysqli_real_escape_string($this->dbConn->connection, $whId) . ")";
    }

     if($docType == '') {
        $docTypeVar = "";
    } else {
        $docTypeVar = "and    a.document_type_uid in (". mysqli_real_escape_string($this->dbConn->connection, $docType) . ")";
    }

    $sql = "select se.uid as se_uid, 
                   a.uid  as dm_uid, 
                   a.document_number, 
                   a.alternate_document_number, 
                   a.document_type_uid, 
                   a.depot_uid,
                   a.processed_date, 
                   dpt.name as depot_name,
                   dpt.code as depot_code,
                   b.order_date, 
                   b.invoice_date, 
                   b.delivery_date,
                   b.due_delivery_date, 
                   b.document_status_uid, 
                   b.data_source, 
                   b.source_document_number,
                   b.principal_store_uid,
                   b.requested_delivery_date,
                   psm.deliver_name, 
                   psm.deliver_add1, 
                   psm.deliver_add2, 
                   psm.deliver_add3,
                   psm.bill_name, 
                   psm.bill_add1, 
                   psm.bill_add2, 
                   psm.bill_add3, 
                   psm.old_account,
                   psm.branch_code, 
                   psm.vat_number, 
                   psm.tel_no1, 
                   psm.tel_no2, 
                   psm.email_add, 
                   psm.principal_chain_uid,
                   b.invoice_number, 
                   b.exclusive_total, 
                   b.vat_total, 
                   b.invoice_total,
                   c.product_uid, 
                   pp.product_code, 
                   pp.alt_code, 
                   pp.product_description, 
                   pp.items_per_case,
                   pp.outer_casing_gtin,
                   pp.short_description,
                   pp.product_guid,
                   pp.revenue_account,
                   c.ordered_qty, 
                   c.document_qty, 
                   c.delivered_qty, 
                   c.selling_price, 
                   c.discount_value, 
                   c.net_price, 
                   c.extended_price,
                   c.vat_amount, 
                   c.vat_rate, 
                   c.total, 
                   c.line_no, 
                   c.client_line_no, 
                   c.product_code as `detail_product_code`, 
                   c.batch AS 'Batch',
                   b.customer_order_number, 
                   b.grv_number, 
                   b.claim_number,
                   r.uid as reason_uid, 
                   r.description as reason_description, 
                   a.incoming_file, 
                   b.buyer_account_reference,
                   pp.major_category, 
                   pp.packing, 
                   a.client_document_number, 
                   c.ws_unique_creator_id as 'ohd_ws_unique_creator_id',
                   psm.ean_code, 
                   a.additional_type, 
                   pp.non_stock_item,
                   o.delivery_instructions,
                   dg.sku_gtin, 
                   dg.outercasing_gtin,
                   b.buyer_account_reference,
                   se.type_uid,
                   ppc.comments as 'prod_group_code'
           from document_master a
           left join orders o on a.order_sequence_no = o.order_sequence_no
           inner join document_header b on a.uid = b.document_master_uid
           left join reason_code r on b.pod_reason_uid = r.uid
           inner join document_detail c on a.uid = c.document_master_uid
           inner join smart_event se on a.uid = se.data_uid and se.`type` = '".SE_EXTRACT."' 
                                                            and se.type_uid = {$notificationUId} 
                                                            and se.status IN ('" . FLAG_STATUS_QUEUED . "', '" . FLAG_ERRORTO_ERROR . "') #retry extraction errors
           left join depot dpt on a.depot_uid = dpt.uid
           left join principal_store_master psm on b.principal_store_uid = psm.uid
           left join principal_product pp on c.product_uid = pp.uid
           left join principal_product_depot_gtin dg on dg.uid = pp.uid
           left JOIN .principal_product_category ppc on pp.major_category  = ppc.uid
           where  a.principal_uid = '{$principalUId}'  
           and  b.invoice_date > curdate() - interval 30 day " 
           . $whVar . " " 
           . $chVar . " "
           . $docTypeVar . " 
           order  by {$orderBy}";
//echo "<br>";           
//echo $sql;
//echo "<br>"; 
    return $this->dbConn->dbGetAll($sql);

  }



  public function getExtractErrors($principalUId, $notificationUIdArr) {

    $sql = "select se.uid se_uid, se.general_reference_1, se.general_reference_2, a.uid dm_uid, a.document_number, a.document_type_uid, a.depot_uid, dpt.name depot_name,
                       b.document_status_uid, b.data_source,
                       b.principal_store_uid, psm.deliver_name, psm.bill_name, psm.bill_add1, psm.bill_add2, psm.bill_add3,
                       b.customer_order_number reference, b.invoice_number, b.customer_order_number
                from   document_master a,
                       document_header b,
                       smart_event se,
                       depot dpt,
                       principal_store_master psm

                where  a.principal_uid = '{$principalUId}'
                and    a.uid = b.document_master_uid
                and    a.uid = se.data_uid
                and    se.`type` = '" . SE_EXTRACT . "'
                and    se.type_uid in (".implode(",",$notificationUIdArr).")
                and    se.status = '" . FLAG_ERRORTO_ERROR . "'
                and    a.depot_uid = dpt.uid
                and    b.principal_store_uid = psm.uid

                order  by dpt.name,psm.deliver_name, a.document_number";

    return $this->dbConn->dbGetAll($sql);

  }

  public function getExtractForPeriod($fromDate, $toDate, $principalId = false){

    $sql = "SELECT
                  p.uid as 'principal_id',
                  p.name AS 'principal_name',
                  e.uid as 'se_id',
                  e.processed_date AS 'date', # will be the sent date
                  e.general_reference_1 as 'filename',
                  GROUP_CONCAT(DISTINCT CONCAT(m.uid,':',m.document_number) separator ';') as 'document_number_list',
                  t.description as 'document_type',
                  r.user_uid_list,
                  (SELECT GROUP_CONCAT(c.email_addr separator ';') from principal_contact c where c.principal_uid = p.uid AND FIND_IN_SET(c.uid,r.user_uid_list)) as 'user_list'
          FROM notification_recipients r
            INNER JOIN principal p on r.principal_uid = p.uid
            INNER JOIN smart_event e on e.`type` = '" . SE_EXTRACT . "' and r.uid = e.type_uid
            INNER JOIN document_master m on e.data_uid = m.uid and p.uid = m.principal_uid
            INNER JOIN document_type t on m.document_type_uid = t.uid
          WHERE DATE(e.processed_date) BETWEEN '{$fromDate}' AND '{$toDate}'
            AND e.`status` = 'C'
            " . ((!$principalId)?"":" AND p.uid = {$principalId}") . "
            AND p.uid != " . BILLING_PRINCIPAL_ID . "
          GROUP BY e.general_reference_1, e.processed_date
          ORDER BY e.processed_date DESC";

    return $this->dbConn->dbGetAll($sql);

  }

  public function getDailyExtractBatch($dmUIdArr) {

    if (sizeof($dmUIdArr)==0) return array();

    $sql = "select *
            from   document_batch db
            where  db.document_master_uid in (".implode(",",$dmUIdArr).")";

      return $this->dbConn->dbGetAll($sql);

  }

  public function getSourceDocumentInvoiceDate($principalId, $SourceDocNo) {
	
	     $sql = "select dh.invoice_date, invoice_number
               from .document_header dh, document_master dm
               where dm.uid = dh.document_master_uid
               and   dm.principal_uid   = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               and   dm.document_number = '". mysqli_real_escape_string($this->dbConn->connection, $SourceDocNo) . "';";
               

	     return $this->dbConn->dbGetAll($sql);        
	
    }

  public function getCurrentStockRecords($principalId, $depUid) {
	
	     $sql = "select s.principal_id, 
                      s.depot_id,  
                      s.principal_product_uid,
                      s.stock_descrip, 
                      s.stock_item ,
                      s.closing, 
                      s.allocations + s.in_pick as 'Alloc',
                      pp.items_per_case, 
                      ppg.outercasing_gtin
               from .stock s
               left join principal_product_depot_gtin ppg on ppg.principal_product_uid = s.principal_product_uid
               left join principal_product pp on pp.uid = s.principal_product_uid      
               where s.principal_id = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               and   s.depot_id in (". mysqli_real_escape_string($this->dbConn->connection, $depUid) . ");";
               

	     return $this->dbConn->dbGetAll($sql);        
	
    }
// *************************************************************************************************************************************

  public function getDailyExtractInvoicedOrdersWithParmsVersion2($principalUId, 
                                                         $notificationUId, 
                                                         $whId, 
                                                         $chainUIdIn, 
                                                         $orderBy="se.status DESC, a.uid DESC, c.uid",
                                                         $docType='') {
  	
    if($whId == '') {
        $whVar = "";
    } else {
        $whVar = "and    a.depot_uid in (". mysqli_real_escape_string($this->dbConn->connection, $whId) . ")";
    }
  	
     if($chainUIdIn == '') {
        $chVar = "";
    } else {
        $chVar = "and    psm.principal_chain_uid in (". mysqli_real_escape_string($this->dbConn->connection, $chainUIdIn) . ")";
    }

     if($docType == '') {
        $docTypeVar = "";
    } else {
        $docTypeVar = "and    a.document_type_uid in (". mysqli_real_escape_string($this->dbConn->connection, $docType) . ")";
    }

    $sql = "select se.uid as se_uid, 
                   a.uid  as dm_uid, 
                   a.document_number, 
                   a.alternate_document_number, 
                   a.document_type_uid, 
                   a.depot_uid,
                   a.processed_date, 
                   dpt.name as depot_name,
                   dpt.code as depot_code,
                   b.order_date, 
                   b.invoice_date, 
                   b.delivery_date,
                   b.due_delivery_date, 
                   b.document_status_uid, 
                   b.data_source, 
                   b.source_document_number,
                   b.principal_store_uid,
                   b.requested_delivery_date,
                   psm.deliver_name, 
                   psm.deliver_add1, 
                   psm.deliver_add2, 
                   psm.deliver_add3,
                   psm.bill_name, 
                   psm.bill_add1, 
                   psm.bill_add2, 
                   psm.bill_add3, 
                   psm.old_account,
                   psm.branch_code, 
                   psm.vat_number, 
                   psm.tel_no1, 
                   psm.tel_no2, 
                   psm.email_add, 
                   psm.principal_chain_uid,
                   b.invoice_number, 
                   b.exclusive_total, 
                   b.vat_total, 
                   b.invoice_total,
                   c.product_uid, 
                   pp.product_code, 
                   pp.alt_code, 
                   pp.product_description, 
                   pp.items_per_case,
                   pp.outer_casing_gtin,
                   pp.short_description,
                   pp.product_guid,
                   pp.revenue_account,
                   c.ordered_qty, 
                   c.document_qty, 
                   c.delivered_qty, 
                   c.selling_price, 
                   c.discount_value, 
                   c.net_price, 
                   c.extended_price,
                   c.vat_amount, 
                   c.vat_rate, 
                   c.total, 
                   c.line_no, 
                   c.client_line_no, 
                   c.product_code as `detail_product_code`, 
                   c.batch AS 'Batch',
                   b.customer_order_number, 
                   b.grv_number, 
                   b.claim_number,
                   r.uid as reason_uid, 
                   r.description as reason_description, 
                   a.incoming_file, 
                   b.buyer_account_reference,
                   pp.major_category, 
                   pp.packing, 
                   a.client_document_number, 
                   c.ws_unique_creator_id as 'ohd_ws_unique_creator_id',
                   psm.ean_code, 
                   a.additional_type, 
                   pp.non_stock_item,
                   o.delivery_instructions,
                   dg.sku_gtin, 
                   dg.outercasing_gtin,
                   b.buyer_account_reference,
                   se.type_uid,
                   ppc.comments as 'prod_group_code'
           from document_master a
           left join orders o on a.order_sequence_no = o.order_sequence_no
           inner join document_header b on a.uid = b.document_master_uid
           left join reason_code r on b.pod_reason_uid = r.uid
           inner join document_detail c on a.uid = c.document_master_uid
           inner join smart_event se on a.uid = se.data_uid and se.`type` = '".SE_EXTRACT."' 
                                                            and se.type_uid = {$notificationUId} 
                                                            and se.status IN ('" . FLAG_STATUS_QUEUED . "', '" . FLAG_ERRORTO_ERROR . "') # retry extraction errors
           left join depot dpt on a.depot_uid = dpt.uid
           left join principal_store_master psm on b.principal_store_uid = psm.uid
           left join principal_product pp on c.product_uid = pp.uid
           left join principal_product_depot_gtin dg on dg.uid = pp.uid
           left JOIN .principal_product_category ppc on pp.major_category  = ppc.uid
           where  a.principal_uid = '{$principalUId}'
           and  b.invoice_date > curdate() - interval 30 day "
           . $whVar . " " 
           . $chVar . " "
           . $docTypeVar . " 
           order  by {$orderBy}";
// echo "<br>";           
// echo $sql;
// echo "<br>"; 
    return $this->dbConn->dbGetAll($sql);

  }
//*************************************************************************************************************************************************************************************
  public function getJobExecutionParms($principalUId) {
  
       $sql = "SELECT *
               FROM .job_execution a
               WHERE a.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalUId) . ";";  
       
       return $this->dbConn->dbGetAll($sql);
  }
//*************************************************************************************************************************************************************************************
  public function getDailyExtractInvoicedHeaders($principalUId, $notificationUId, $docType = '') {
  
       if($docType == '') {
          $docTypeQuery = '';
       } else {
           $docTypeQuery = "AND dm.document_type_uid = '1'";  	
       }
       
       $sql = "SELECT se.uid AS se_uid,
                      dm.document_type_uid,
                      dm.document_number,
                      dh.customer_order_number,
                      dh.principal_store_uid,
                      invoice_date,
                      dh.buyer_account_reference,
                      psm.deliver_name,
                      d.name AS 'Warehouse',
                      ROUND(SUM(dd.total),2) AS 'Total'
               FROM .document_master dm
               INNER JOIN document_header dh ON dh.document_master_uid = dm.uid
               INNER JOIN document_detail dd ON dd.document_master_uid = dm.uid
               INNER JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
               INNER JOIN depot d on dm.depot_uid = d.uid
               INNER JOIN smart_event se on dm.uid = se.data_uid and se.`type` = '".SE_EXTRACT."' 
                                            and se.type_uid = {$notificationUId} 
                                            and se.status IN ('" . FLAG_STATUS_QUEUED . "', '" . FLAG_ERRORTO_ERROR . "') # retry extraction errors
               WHERE dm.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "
               and  dh.invoice_date > curdate() - interval 30 day 
               " . $docTypeQuery . "
               GROUP BY dm.uid ;";  
       
       return $this->dbConn->dbGetAll($sql);
  }
//*************************************************************************************************************************************************************************************
  public function getManageSpecialField($fldId, $entityId, $value) {
  	
       $sql = "SELECT *
               FROM .special_field_details sfd
               WHERE sfd.field_uid = '". mysqli_real_escape_string($this->dbConn->connection, $fldId) . "'
               AND   sfd.entity_uid = ' ". mysqli_real_escape_string($this->dbConn->connection, $entityId) . " ';";
               
               $spfRecs = $this->dbConn->dbGetAll($sql);
               
       if(count($spfRecs) <> 0) {

           $sql = "UPDATE special_field_details sfd SET sfd.value = ' ". mysqli_real_escape_string($this->dbConn->connection, $value) . " '
                   WHERE sfd.field_uid = '". mysqli_real_escape_string($this->dbConn->connection, $fldId) . "'
                   AND   sfd.entity_uid = ' ". mysqli_real_escape_string($this->dbConn->connection, $entityId) . " ';" ;      	

           $this->errorTO = $this->dbConn->processPosting($sql,"");
              
           if($this->errorTO->type == 'S') {
                $this->dbConn->dbQuery("commit");
                // echo "<br>" . $sql . "<br>";
                return $this->errorTO;     	
           } else {
       	        echo $sql;
                return $this->errorTO;  
           }
       } else {

           $sql = "INSERT INTO special_field_details (special_field_details.field_uid,
                                                      special_field_details.entity_uid,
                                                      special_field_details.value)
                   VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $fldId)    . "', 
                           '" . mysqli_real_escape_string($this->dbConn->connection, $entityId) . "', 
                           '" . mysqli_real_escape_string($this->dbConn->connection, $value)    . "');";       	
  	       	
           $this->errorTO = $this->dbConn->processPosting($sql,"");
              
           if($this->errorTO->type == 'S') {
                $this->dbConn->dbQuery("commit");
                // echo "<br>" . $sql . "<br>";
                return $this->errorTO;     	
           } else {
       	        echo $sql;
                return $this->errorTO;  
           }       	
       } 
  }
//*************************************************************************************************************************************************************************************

}

?>

<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class TransactionDAO {
	private $dbConn;

	function __construct($dbConn) {

       $this->dbConn = $dbConn;
    }

	// remember that store_string is being phased out
	// $dateFieldToUse is actual field name eg. 'order_date'
	// $docType is optional
	// NOTE : IT IS NOT necessary to do LEFT join on user_principal_store/chain because ALL documents in document tables are stores only.
	public function getDocumentsArray($userId, $principalId, $startDate, $endDate, $dateFieldToUse, $docType="", $arrayIndex, $enforceStorePermission="Y") {
		$sql="SELECT a.uid 
                 dm_uid,
                 a.principal_uid,
                 a.depot_uid, 
                 depot.name 
                 depot_name, 
                 a.document_number,
                 a.document_type_uid, 
                 ifnull(pdt.description,document_type.description) document_type_description, 
                 a.processed_date,
                 a.processed_time,
                 a.invoice_file, 
                 a.incoming_file, 
                 p.scanned_pod_start,
                 document_header.document_status_uid as status_uid,
                 document_header.order_date,
                 document_header.invoice_date,
                 document_header.delivery_date,
                 document_header.principal_store_uid,
                 document_header.customer_order_number,
                 document_header.grv_number,
                 document_header.claim_number,
                 document_header.tripsheet_number,
                 document_header.decimal_updated,
                 IF(scans.uid IS NOT NULL,'Y','N') as scanned_document_exists,
                 trim(document_header.source_document_number) source_document_number, 
                 status.description status,
                 document_header.invoice_number,
                 document_header.cases,
                 document_header.selling_price,
                 document_header.exclusive_total,
                 document_header.vat_total,
                 document_header.invoice_total, 
                 a.alternate_document_number,
                 document_header.requested_delivery_date, 
                 document_header.due_delivery_date,
                 principal_store_master.deliver_name store_name, 
                 principal_store_master.uid psm_uid, 
                 `day`.name delivery_day, 
                 a.order_sequence_no,
                 document_header.data_source, 
                 principal_store_master.epod_store_flag, 
                 depot.delivery_note,
                 dts.image_file as 'dtsimage'
          FROM   document_master a
          LEFT Join document_tripsheet dts on dts.document_master_uid = a.uid
          LEFT Join principal p on p.uid = a.principal_uid
          LEFT Join document_type ON a.document_type_uid = document_type.uid
          LEFT Join principal_document_type pdt on a.document_type_uid = pdt.document_type_uid and pdt.principal_uid = '{$principalId}'
          LEFT Join depot ON a.depot_uid = depot.UID
          LEFT JOIN document_scans scans ON a.uid = scans.document_master_uid          
		  LEFT Join document_header ON a.uid = document_header.document_master_uid
          LEFT Join principal_store_master ON document_header.principal_store_uid = principal_store_master.uid
          LEFT Join `day` ON principal_store_master.delivery_day_uid = `day`.uid
          LEFT Join `status` ON document_header.document_status_uid = `status`.uid ";

          if ($enforceStorePermission=="Y") 	$sql.= " LEFT Join user_principal_store ups ON ups.principal_store_uid = document_header.principal_store_uid AND ups.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)."
                                                       LEFT Join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION."  and (ur.entity_uid is null or ur.entity_uid={$principalId}) ";

          $sql.=" LEFT JOIN user_principal_chain upc ON upc.principal_chain_uid = principal_store_master.principal_chain_uid AND upc.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)."
                  LEFT Join user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION."  and (urc.entity_uid is null or urc.entity_uid={$principalId})
                  INNER JOIN user_principal_depot ON user_principal_depot.principal_id = principal_store_master.principal_uid AND user_principal_depot.depot_id = a.depot_uid AND user_principal_depot.user_id=".mysqli_real_escape_string($this->dbConn->connection, $userId)."
                  WHERE a.principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'";

          if ($enforceStorePermission=="Y") {$sql.= "AND (ups.uid is not null OR ups.uid is not null)";}

          $sql.="AND   (upc.uid is not null OR urc.uid is not null)
                 AND   ".$dateFieldToUse." between '".mysqli_real_escape_string($this->dbConn->connection, $startDate)."' 
                                           AND     '".mysqli_real_escape_string($this->dbConn->connection, $endDate)  ."' ";

      if ($docType!="") $sql.=" AND document_type.code='".$docType."' ";

      // this order by simply ensures that where the correction note is the same but where the type differs that it is grouped against the right source document type
			$sql .= "order  by if(document_header.source_document_number='',a.document_number,document_header.source_document_number),
        			 				   if(document_header.source_document_number='',1,2),
                         if(a.document_type_uid in (1,4,23),1,
                            if(a.document_type_uid in (2,7,22),2,
                              if(a.document_type_uid in (5,14),3,
                                  4
                                )
                              )
                            ),
        			 				   a.uid"; 
        			 				   
		// 				   if ($userId == 11 ) {echo $sql;}      			 				   

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ($arrayIndex!="") $arr[$row[$arrayIndex]] = $row;
				else $arr[] = $row;
			}
		}

		return $arr;
	}


	// this skips permissions and only selects for the depot warehouse principal for every principal-depot that that depot user has access to
	// WARNING : this uses the depot_principal_store_uid to give the delivery day and regions !
	// - the principal limiter has been re-applied as @ 2016-04-13
	public function getDepotDocumentsArray($userId, $type="", $startDate="", $endDate="", $dateFieldToUse="") {
	  if (!isset($_SESSION)) session_start();
	  $principalId  = $_SESSION['principal_id'];

		$sql="SELECT a.uid dm_uid,
                 a.principal_uid,
                 p.name principal_name,
                 a.depot_uid, 
                 depot.name 
                 depot_name, 
                 a.document_number,
                 a.document_type_uid, 
                 ifnull(pdt.description,document_type.description) document_type_description, 
                 a.processed_date,
                 a.processed_time,
                 p.scanned_pod_start, 
                 document_header.document_status_uid as status_uid,
                 document_header.order_date,
                 document_header.invoice_date,
                 document_header.delivery_date,
                 document_header.requested_delivery_date,                 
                 document_header.principal_store_uid,
                 document_header.customer_order_number, 
                 status.description status,
                 document_header.invoice_number,
                 document_header.cases,
                 document_header.selling_price,
                 document_header.exclusive_total,
                 document_header.vat_total,
                 document_header.invoice_total,                  
                 document_header.tripsheet_number,
                 document_header.data_source,
                 document_header.decimal_updated, 
                 document_header.grv_number,
                 document_header.claim_number,                
                 IF(scans.uid IS NOT NULL,'Y','N') as scanned_document_exists,                 
                 trim(document_header.source_document_number) source_document_number,
                 psm2.deliver_name store_name, 
                 psm2.uid psm_uid, 
                 `day`.name delivery_day, 
                 a.order_sequence_no,
                 psm1.deliver_name depot_store_name, 
                 psm1.uid depot_psm_uid, 
                 area.description area_description, 
                 depot.delivery_note,
                 dts.image_file as 'dtsimage' 
         FROM   document_master a
         LEFT Join document_tripsheet dts on dts.document_master_uid = a.uid and dts.tripsheet_number = (select max(dt2.tripsheet_number) from document_tripsheet dt2 where dt2.document_master_uid = a.uid) 
         INNER JOIN user_principal_depot ON user_principal_depot.principal_id = a.principal_uid AND user_principal_depot.depot_id = a.depot_uid AND user_principal_depot.user_id=".mysqli_real_escape_string($this->dbConn->connection, $userId)."
         LEFT Join document_type ON a.document_type_uid = document_type.uid
         LEFT Join principal_document_type pdt on a.document_type_uid = pdt.document_type_uid and pdt.principal_uid = a.principal_uid
         INNER Join depot ON a.depot_uid = depot.UID
         INNER Join document_header ON a.uid = document_header.document_master_uid
         LEFT Join principal_store_master psm1 ON document_header.depot_principal_store_uid = psm1.uid -- the depot's store details
         LEFT Join principal_store_master psm2 ON document_header.principal_store_uid = psm2.uid -- the principal's store details
         LEFT Join `day` ON psm1.delivery_day_uid = `day`.uid
         LEFT Join area on area.uid = psm1.area_uid
         LEFT JOIN document_scans scans ON a.uid = scans.document_master_uid
         INNER Join `status` ON document_header.document_status_uid = `status`.uid
         LEFT JOIN principal p ON a.principal_uid = p.uid
         WHERE depot.wms = 'Y' " . (isset($_SESSION['depot_id'])?(" and a.depot_uid = '" . $_SESSION['depot_id'] . "'"):('')) . "
         and   a.principal_uid = '{$principalId}'";

         // there are no date restrictions
         if ($type=="unaccepted")    $sql.=" AND 	a.document_type_uid in (".DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE.",".DT_UPLIFTS.",".DT_DESTRUCTION_DISPOSAL .",".DT_WALKIN_INVOICE.")
                                             AND	document_header.document_status_uid in (".self::getUnacceptedOrderStatuses().") ";
         else if ($type=="accepted") $sql.=" AND a.document_type_uid in (".DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE.",".DT_UPLIFTS.",".DT_DESTRUCTION_DISPOSAL.",".DT_WALKIN_INVOICE.")
                                             AND document_header.document_status_uid in (".DST_ACCEPTED.") ";
         else if ($type=="inpick")   $sql.=" AND a.document_type_uid in (".DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE.",".DT_UPLIFTS.",".DT_DESTRUCTION_DISPOSAL.",".DT_WALKIN_INVOICE.")
                                             AND	document_header.document_status_uid in (".DST_INPICK.") ";
         else if ($type=="invoiced") $sql.=" AND a.document_type_uid in (".DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE.",".DT_UPLIFTS.",".DT_DESTRUCTION_DISPOSAL.",".DT_WALKIN_INVOICE.",".DT_GOODS_IN_TRANSIT .")
                                             AND	document_header.document_status_uid in (".DST_INVOICED .", " . DST_WAITING_DISPATCH .", " . DST_RE_DELIVERY .") ";
         else if ($type=="all")      $sql.=" AND ".$dateFieldToUse." between '".mysqli_real_escape_string($this->dbConn->connection, $startDate)."' and '".mysqli_real_escape_string($this->dbConn->connection, $endDate)."' ";
         else $sql.=" a.uid = 999999 ";

         $sql .= "order  by a.principal_uid, a.depot_uid, document_header.order_date";

         $this->dbConn->dbQuery($sql);

         $arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}


	// $dateFieldToUse is actual field name eg. 'order_date'
	// $docType is optional
	// NB : the depot comes from backend and it was the depot at time of capture, possibly not the store depot as now





	public function getDocumentsWithDetailArray($userId, $principalId, $startDate, $endDate, $dateFieldToUse, $docType="", $arrayIndex, $enforceStorePermission="Y") {



		$sql="SELECT a.uid dm_uid,a.principal_uid,a.depot_uid, depot.name depot_name, a.document_number,,a.client_document_number,a.document_type_uid, ifnull(pdt.description,document_type.description) document_type_description, a.processed_date,
					 a.processed_time,a.invoice_file,
					 document_header.order_date,document_header.invoice_date,document_header.delivery_date,
					 document_header.principal_store_uid,document_header.customer_order_number,document_header.grv_number,
					 document_header.claim_number, trim(document_header.source_document_number) source_document_number, status.description status,
					 document_header.invoice_number,document_header.cases,document_header.selling_price,document_header.exclusive_total,
				 	 document_header.vat_total,document_header.invoice_total,
					 principal_store_master.deliver_name store_name, principal_store_master.uid psm_uid, `day`.name delivery_day,
					 document_detail.line_no,document_detail.product_uid,document_detail.ordered_qty,document_detail.document_qty,
					 document_detail.delivered_qty,document_detail.selling_price,document_detail.discount_value,document_detail.discount_reference,
					 document_detail.net_price,document_detail.extended_price,document_detail.vat_amount,document_detail.vat_rate,document_detail.total,
					 principal_product.product_code, principal_product.product_description, a.order_sequence_no
			FROM   document_master a
			        LEFT Join document_type ON a.document_type_uid = document_type.uid
							LEFT Join principal_document_type pdt on a.document_type_uid = pdt.document_type_uid and pdt.principal_uid = a.principal_uid
			        LEFT Join depot ON a.depot_uid = depot.UID
			        LEFT Join document_header ON a.uid = document_header.document_master_uid
			        LEFT Join principal_store_master ON document_header.principal_store_uid = principal_store_master.uid
			        			LEFT Join `day` ON principal_store_master.delivery_day_uid = `day`.uid
			        LEFT Join `status` ON document_header.document_status_uid = `status`.uid
			        LEFT Join document_detail ON a.uid = document_detail.document_master_uid
			        			LEFT Join principal_product ON document_detail.product_uid = principal_product.uid ";
		if ($enforceStorePermission=="Y")
			$sql.= " LEFT Join user_principal_store ON user_principal_store.principal_store_uid = document_header.principal_store_uid AND user_principal_store.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)."
					 LEFT Join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION."  and (ur.entity_uid is null or ur.entity_uid={$principalId}) ";
		$sql.="
       				LEFT JOIN user_principal_chain upc ON upc.principal_chain_uid = principal_store_master.principal_chain_uid AND upc.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)."
       				LEFT Join user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION."  and (urc.entity_uid is null or urc.entity_uid={$principalId})
       				INNER JOIN user_principal_depot ON user_principal_depot.principal_id = principal_store_master.principal_uid AND user_principal_depot.depot_id = principal_store_master.depot_uid AND user_principal_depot.user_id=".mysqli_real_escape_string($this->dbConn->connection, $userId)."
			WHERE a.principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
			AND   (upc.uid is not null OR urc.uid is not null)
			AND   ".$dateFieldToUse." between '".mysqli_real_escape_string($this->dbConn->connection, $startDate)."' and '".mysqli_real_escape_string($this->dbConn->connection, $endDate)."' ";

			if ($docType!="") $sql.=" AND document_type.code='".$docType."' ";

			$sql .= "order  by if(document_header.source_document_number='',a.document_number,document_header.source_document_number),
			 				   if(document_header.source_document_number='',1,2),
			 				   a.uid";


 		//ini_set("memory_limit", "400M");

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ($arrayIndex!="") $arr[$row[$arrayIndex]] = $row;
				else $arr[] = $row;
			}
		}

		return $arr;
	}

	// must mimic getDocumentsWithDetailArray as it is used in same viewTracking screen to show exclusion
	public function getDocumentsCountWithoutPermissions($userId, $principalId, $startDate, $endDate, $dateFieldToUse, $docType="") {
		$sql="SELECT count(*) cnt
			  FROM   document_master a
						LEFT JOIN user_principal_depot upd ON a.principal_uid = upd.principal_id and a.depot_uid = upd.depot_id and upd.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."',
				   document_header b
						LEFT JOIN user_principal_store ups ON b.principal_store_uid = ups.principal_store_uid and ups.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
						LEFT JOIN user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid={$principalId}),
				   principal_store_master c
 						LEFT JOIN user_principal_chain upc ON c.principal_chain_uid = upc.principal_chain_uid and upc.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
 						LEFT JOIN user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid={$principalId})
			WHERE a.principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
			AND   a.uid = b.document_master_uid
			AND   b.principal_store_uid = c.uid
			AND   ".$dateFieldToUse." between '".mysqli_real_escape_string($this->dbConn->connection, $startDate)."' and '".mysqli_real_escape_string($this->dbConn->connection, $endDate)."'
			AND   (upd.uid is null or (upc.uid is null and urc.uid is null) or (ups.uid is null and ur.uid is null)) ";

			if ($docType!="") $sql.=" AND document_type.code='".$docType."' ";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// although this is an item, you will need to loop through multiple items, to get each product
	// distinct is put in here in case the user permissions are buggered up and cause rows to be doubled. This would be critical if you were printing a
	// invoice document and totals were doubled !
	public function getDocumentWithDetailItem($userId, $principalId, $dMUId, $orderBy=false, $includeStock=false) {

          $sql="SELECT distinct a.uid dm_uid,
                                a.principal_uid,p.name as principal_name,
                                postal_add1 as prin_add1,
                                postal_add2 as prin_add2,
                                postal_add3 as prin_add3,
                                vat_num as prin_vat,
                                p.email_add as p_email,
                                p.office_tel,
                                p.office_tel2,
                                banking_details,
                                p.export_number,
                                company_reg,
                                a.depot_uid,
                                depot.name
                                depot_name,
                                depot_address1,
                                depot_address2,
                                depot_address3,                                                                
                                depot.disable_stock_check, 
                                depot.bypass_qty_restrictions,
                                depot.allow_negative_stock,
                                depot.allow_git, 
                                depot.allow_git_in,
                                depot.waiting_dispatch,
                                a.document_number,
                                a.client_document_number,
                                a.alternate_document_number,
                                a.version,
                                a.document_type_uid,
                                ifnull(pdt.description,document_type.description) document_type_description, a.processed_date,
                                a.order_sequence_no,
                                a.processed_time,
                                a.invoice_file,
                                document_header.order_date,
                                document_header.invoice_date,
                                document_header.delivery_date,
                                document_header.principal_store_uid,
                                document_header.customer_order_number,
                                document_header.grv_number,
                                document_header.claim_number,
                                document_header.principal_store_uid,
                                trim(document_header.source_document_number) source_document_number,
                                status.description as status, document_header.document_status_uid as status_uid,
                                document_header.invoice_number,
                                document_header.cases,
                                document_header.selling_price,
                                document_header.exclusive_total,
                                document_header.vat_total,
                                document_header.invoice_total,
                                document_header.overide_rep_code_uid,
                                principal_store_master.deliver_name store_name,
                                principal_store_master.deliver_add1,
                                principal_store_master.deliver_add2,
                                principal_store_master.deliver_add3,
                                principal_store_master.bill_name,
                                principal_store_master.bill_add1,
                                principal_store_master.bill_add2,
                                principal_store_master.bill_add3,
                                principal_store_master.uid psm_uid,
                                principal_store_master.vat_number,
                                principal_store_master.export_number_enabled,
                                `day`.name delivery_day,
                                document_detail.line_no,
                                document_detail.product_uid,
                                document_detail.ordered_qty,
                                document_detail.document_qty,
                                document_detail.uid dd_uid,
                                document_detail.delivered_qty,
                                document_detail.selling_price,
                                document_detail.discount_value,
                                document_detail.discount_reference,
                                document_detail.net_price,
                                document_detail.extended_price,
                                document_detail.vat_amount,
                                document_detail.vat_rate,document_detail.total,
                                principal_product.product_code,
                                principal_product.product_description,
                                principal_product.allow_decimal, 
                                principal_product.weight,
                                (select distinct 1
                                     from document_master dm2, document_header dh2
                                     where dm2.uid = dh2.document_master_uid
                                     and dh2.source_document_number = a.document_number
                                     and dm2.depot_uid = a.depot_uid) has_associated_notes,
                                document_detail.pallets,
                                o.uid orders_uid,
                                IFNULL(users.full_name,document_header.captured_by) as 'captured_by_name',
                                document_header.document_status_uid,
                                o.delivery_instructions,
                                principal_store_master.tel_no1,
                                principal_store_master.tel_no2,
                                principal_store_master.email_add,
                                s.description as document_service,
                                concat(rep.first_name, ' ',
                                rep.surname) as rep_name,
                                rep.rep_code ,
                                rc.description as 'crdreason',
                                depot.delivery_note,
                                document_detail.batch,
                                principal_product.weight,
                                document_detail.comment,
                                pdt.tcs,
                                pdt.btcs
                                ".(($includeStock)?",goods_in_transit":"")."
                                ".(($includeStock)?",stock.closing":"")."
                  FROM    document_master a
                          LEFT Join document_type ON a.document_type_uid = document_type.uid
                          LEFT Join principal_document_type pdt on a.document_type_uid = pdt.document_type_uid and pdt.principal_uid = a.principal_uid
                          LEFT Join orders o ON a.order_sequence_no = o.order_sequence_no
                          LEFT JOIN depot ON a.depot_uid = depot.UID
                          INNER JOIN document_header ON a.uid = document_header.document_master_uid
                          LEFT JOIN principal_store_master ON document_header.principal_store_uid = principal_store_master.uid
                          LEFT JOIN `day` ON principal_store_master.delivery_day_uid = `day`.uid
                          LEFT Join `status` ON document_header.document_status_uid = `status`.uid
                          INNER Join document_detail ON a.uid = document_detail.document_master_uid
                          LEFT JOIN principal_product ON document_detail.product_uid = principal_product.uid
                          LEFT Join user_principal_store ON user_principal_store.principal_store_uid = document_header.principal_store_uid AND user_principal_store.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)."
                          LEFT Join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid={$principalId})
                          LEFT JOIN user_principal_chain upc ON upc.principal_chain_uid = principal_store_master.principal_chain_uid AND upc.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)."
                          LEFT Join user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid={$principalId})
                          INNER JOIN user_principal_depot ON user_principal_depot.principal_id = principal_store_master.principal_uid AND user_principal_depot.depot_id = principal_store_master.depot_uid AND user_principal_depot.user_id=".mysqli_real_escape_string($this->dbConn->connection, $userId)."
                          LEFT JOIN users on document_header.captured_by = users.uid
                          INNER JOIN principal p on a.principal_uid = p.uid
                          LEFT JOIN document_service s on document_header.document_service_type_uid = s.uid
                          LEFT JOIN principal_sales_representative rep on principal_store_master.principal_sales_representative_uid = rep.uid
                          LEFT JOIN reason_code rc on rc.uid = document_header.pod_reason_uid
                          ".(($includeStock)?" left join stock on a.principal_uid = stock.principal_id and a.depot_uid = stock.depot_id and document_detail.product_uid = stock.principal_product_uid ":"")."
                  WHERE a.principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
                  AND   a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $dMUId)."'
                  and   (user_principal_store.uid is not null or ur.uid is not null)
                  AND (upc.uid is NOT NULL or urc.uid is NOT NULL) ".
                   (($orderBy!==false)?" ORDER BY ".$orderBy:"");

          return $this->dbConn->dbGetAll($sql);

	}

	public function getDocumentWithDetailIgnorePermissionsItem($dMUId, $orderBy=false) {

          $sql="SELECT  a.uid dm_uid,
                        a.principal_uid,
                        p.name as principal_name,
                        postal_add1 as prin_add1,
                        postal_add2 as prin_add2,
                        postal_add3 as prin_add3,
                        physical_add1 as prin_ph_add1,
                        physical_add2 as prin_ph_add2,
                        physical_add3 as prin_ph_add3,
                        vat_num as prin_vat,
                        p.email_add as p_email,
                        p.office_tel,
                        p.office_tel2,
                        p.banking_details,
                        p.alt_banking_details,
                        p.office_tel,
                        export_number,
                        company_reg,
                        a.depot_uid,
                        depot.name
                        depot_name,
                        depot_address1,
                        depot_address2,
                        depot_address3,
                        depot.priced_uplift,                        
                        depot.disable_stock_check, 
                        depot.bypass_qty_restrictions,
                        depot.allow_negative_stock, 
                        a.document_number,
                        a.client_document_number,
                        a.alternate_document_number,
                        a.document_type_uid,
                        ifnull(pdt.description,
                        document_type.description) document_type_description,
                        a.processed_date,
                        a.order_sequence_no,
                        a.processed_time,
                        a.invoice_file,
                        a.additional_type,
                        document_header.order_date,
                        document_header.invoice_date,
                        document_header.delivery_date,
                        document_header.due_delivery_date,
                        document_header.principal_store_uid,
                        document_header.customer_order_number,
                        document_header.grv_number,
                        document_header.claim_number,
                        document_header.waybill_number,
                        document_header.copies,
                        document_header.invoice_mailed,
                        trim(document_header.source_document_number) source_document_number,
                        status.description as status,
                        document_header.document_status_uid as status_uid,
                        document_header.invoice_number,
                        document_header.cases,
                        document_header.selling_price,
                        document_header.exclusive_total,
                        document_header.vat_total,
                        document_header.invoice_total,
                        document_header.off_invoice_discount as 'Stored_discount',
                        document_header.off_invoice_discount_type as 'Stored_discount_type',
                        document_header.buyer_account_reference,
                        document_header.requested_delivery_date,
                        document_header.invoice_mailed,
                        principal_store_master.deliver_name store_name,
                        principal_store_master.deliver_add1,
                        principal_store_master.deliver_add2,
                        principal_store_master.deliver_add3,
                        principal_store_master.bill_name,
                        principal_store_master.bill_add1,
                        principal_store_master.bill_add2,
                        principal_store_master.bill_add3,
                        principal_store_master.uid psm_uid,
                        principal_store_master.vat_number,
                        principal_store_master.vat_number_2,
                        principal_store_master.no_vat,
                        principal_store_master.export_number_enabled,
                        principal_store_master.branch_code,
                        principal_store_master.old_account,
                        principal_store_master.bank_details_to_print,
                        principal_store_master.q_r_code_to_print,
                        principal_store_master.off_invoice_discount as 'Current_discount',
                        principal_store_master.auto_mail_invoice,
                        principal_store_master.no_prices_on_invoice,
                        `day`.name delivery_day,
                        document_detail.line_no,
                        document_detail.product_uid,
                        document_detail.ordered_qty,
                        document_detail.document_qty,
                        document_detail.delivered_qty,
                        document_detail.buyer_delivered_qty,
                        document_detail.selling_price,
                        document_detail.discount_value,
                        document_detail.discount_reference,
                        document_detail.net_price,
                        document_detail.extended_price,
                        document_detail.vat_amount,
                        document_detail.vat_rate,
                        document_detail.total,
                        principal_product.product_code,
                        principal_product.alt_code,
                        principal_product.product_description,
                        principal_product.items_per_case, 
                        principal_product.ean_code,
                        principal_product.allow_decimal,
                        principal_product.no_discount,                         
                        principal_product.weight,
                        (SELECT DISTINCT 1
                                FROM document_master dm2,
                                     document_header dh2
                                WHERE dm2.uid = dh2.document_master_uid
                                AND dh2.source_document_number = a.document_number
                                AND dm2.depot_uid = a.depot_uid) has_associated_notes,
                        document_detail.pallets,
                        o.uid orders_uid,
                        depot.wms as 'depot_wms',
                        IFNULL(ur.full_name,document_header.captured_by) as 'captured_by_name',
                        document_header.document_status_uid,
                        o.delivery_instructions,
                        principal_store_master.tel_no1,
                        principal_store_master.tel_no2,
                        principal_store_master.email_add,
                        s.description as document_service,
                        concat(rep.first_name, ' ', rep.surname) as rep_name,
                        rep.rep_code,
                        rc.description as 'crdreason',
                        document_detail.batch,
                        principal_product.weight,
                        pdt.tcs,
                        pdt.btcs,
                        pdt.statementmessage,
                        concat(overrep.first_name, ' ',
                        overrep.surname) as overrep_name,
                        overrep.uid as overrep_code,
                        area.uid as areauid,
                        area.description as area,
                        sfd.value as 'SFAccount',
                        sfdh.value AS 'IHeading',
                        i_dispatched
                  FROM   document_master a
                          LEFT Join orders o ON a.order_sequence_no = o.order_sequence_no
                          LEFT Join document_type ON a.document_type_uid = document_type.uid
                          LEFT Join principal_document_type pdt on a.document_type_uid = pdt.document_type_uid and pdt.principal_uid = a.principal_uid
                          LEFT Join depot ON a.depot_uid = depot.UID
                          INNER Join document_header ON a.uid = document_header.document_master_uid
                          LEFT Join principal_store_master ON document_header.principal_store_uid = principal_store_master.uid
                          LEFT JOIN `day` ON principal_store_master.delivery_day_uid = `day`.uid
                          LEFT Join `status` ON document_header.document_status_uid = `status`.uid
                          INNER Join document_detail ON a.uid = document_detail.document_master_uid
                          LEFT JOIN principal_product ON document_detail.product_uid = principal_product.uid
                          LEFT JOIN users ur on document_header.captured_by = ur.uid
                          INNER JOIN principal p on a.principal_uid = p.uid
                          LEFT JOIN document_service s on document_header.document_service_type_uid = s.uid
                          LEFT JOIN principal_sales_representative rep on principal_store_master.principal_sales_representative_uid = rep.uid
                          LEFT JOIN reason_code rc on rc.uid=document_header.pod_reason_uid
                          LEFT JOIN principal_sales_representative overrep on document_header.overide_rep_code_uid = overrep.uid  
                          LEFT JOIN area on principal_store_master.area_uid = area.uid
                          LEFT JOIN special_field_fields sff on sff.principal_uid = principal_store_master.principal_uid and sff.`type` = 'S' and sff.report = 'Y'
                          LEFT JOIN special_field_details sfd on sfd.entity_uid = principal_store_master.uid and sff.uid = sfd.field_uid
                          LEFT JOIN special_field_fields  sfh on sfh.principal_uid = principal_store_master.principal_uid and sfh.`type` = 'S' and sfh.value_list = 'HEADING'
                          LEFT JOIN special_field_details sfdh on sfdh.entity_uid = principal_store_master.uid and sfh.uid = sfdh.field_uid

                          LEFT JOIN document_tripsheet dts on dts.document_master_uid = a.uid AND dts.tripsheet_removed_by IS NULL
                  WHERE a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $dMUId)."'".
                   (($orderBy!==false)?" ORDER BY ".$orderBy:"");

          return $this->dbConn->dbGetAll($sql);

	}

	// the detail lines are stored as a sub TO array
	// NB: More than one document may be returned, particularly in that the filter is NOT done per DEPOT !
	// NB: PERMISSIONS are NOT checked here because it is used by viewDocumentPermissions.php. Your calling script must validate permissions by the values returned
	public function getDocumentWithDetailByDNItem($userId, $principalId, $documentNumber) {
		$sql="SELECT a.uid dm_uid, a.processed_date, a.order_sequence_no, a.document_number, a.principal_uid, a.depot_uid,
					 b.principal_store_uid, upd.uid upd_uid, if (ur.uid is not null,9999999999,ups.uid) ups_uid , if (urc.uid is not null,9999999999,upc.uid) upc_uid
			FROM   document_master a
						LEFT JOIN user_principal_depot upd ON a.principal_uid = upd.principal_id and a.depot_uid = upd.depot_id and upd.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."',
				   document_header b
						LEFT JOIN user_principal_store ups ON b.principal_store_uid = ups.principal_store_uid and ups.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
						LEFT JOIN user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid={$principalId}),
				   principal_store_master c
 						LEFT JOIN user_principal_chain upc ON c.principal_chain_uid = upc.principal_chain_uid and upc.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
 						LEFT JOIN user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid={$principalId})
			WHERE a.principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
			AND   a.document_number = '".mysqli_real_escape_string($this->dbConn->connection, $documentNumber)."'
			AND   a.uid = b.document_master_uid
			AND   b.principal_store_uid = c.uid
			ORDER by a.uid";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		// now add the detail for each, use reference for row
		foreach ($arr as &$row) {
			$row["document_detail_array"]=array();

			$sql="select a.uid dd_uid, if( hasRole(".mysqli_real_escape_string($this->dbConn->connection, $userId).",".mysqli_real_escape_string($this->dbConn->connection, $principalId).",".ROLE_BYPASS_USER_PRODUCT_RESTRICTION.") ,1,upp.uid) upp_uid
				  from   document_detail a
							LEFT JOIN user_principal_product upp ON a.product_uid = upp.principal_product_uid and upp.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
				  where  document_master_uid = '".$row["dm_uid"]."'";

			$this->dbConn->dbQuery($sql);
			if ($this->dbConn->dbQueryResultRows > 0) {
				while($row2 = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
					$row["document_detail_array"][]=$row2;
				}
			}
		}

		return $arr;
	}


	// must not do store/chain permissions check, only simply get document if user has access to the principal and depot
	public function getDocumentMasterItem($userId, $UId) {
		$sql="select a.uid, 
		             a.principal_uid,
		             a.depot_uid,
		             a.document_number,
		             a.document_type_uid,
		             a.processed_date,
		             a.processed_time,
		             merged_date,
		             merged_time,
		             validation_date,
		             validation_time,
		             validation_status,
		             a.incoming_file,
		             confirmation_file, 
		             c.edi_depot_filename as dop_file,
		             rwr_file,
		             invoice_file,
		             credit_note_file,
		             transmission_flag_1,
		             transmission_flag_2,
		             transmission_flag_3,
		             transmission_flag_4,
		             dh.guid, 
		             a.last_updated, 
		             ifnull(pdt.description,document_type.description) document_type_description,
		             c.captureuser_uid user_uid, 
		             d.full_name, 
		             oh.captured_by, 
		             e.uid principal_uid, 
		             e.name principal_name, 
		             f.name depot_name,
		             c.edi_filename, 
		             c.order_sequence_no, 
		             oh.uid as 'orders_holding_uid', 
		             fl.uid as 'file_log_uid', 
		             fl.file_name as 'file_log_file_name'
				FROM  document_master a
				LEFT JOIN .document_header dh on dh.document_master_uid = a.uid
        LEFT JOIN document_type ON a.document_type_uid = document_type.uid
        LEFT Join principal_document_type pdt on a.document_type_uid = pdt.document_type_uid and pdt.principal_uid = a.principal_uid
        INNER JOIN user_principal_depot b ON a.principal_uid = b.principal_id and a.depot_uid = b.depot_id and  b.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
        LEFT JOIN orders c ON c.principal_uid = a.principal_uid and c.order_sequence_no = a.order_sequence_no
        LEFT JOIN users d ON dh.captured_by = d.uid
        LEFT JOIN principal e ON a.principal_uid = e.uid
        LEFT JOIN depot f ON a.depot_uid = f.uid
        LEFT JOIN orders_holding oh on a.order_sequence_no = oh.order_sequence_number
        LEFT JOIN file_log fl on oh.file_log_uid = fl.uid
        WHERE a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $UId)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}


	public function getSimpleDocumentByDMUId($UId) {
		$sql="select a.uid, a.principal_uid, depot_uid, a.document_number,
                 a.document_type_uid, c.order_sequence_no, h.data_source, h.captured_by, h.document_status_uid, p.system_uid
				from  document_master a
                  INNER JOIN document_header h on a.uid = h.document_master_uid
                  INNER JOIN principal p on a.principal_uid = p.uid
                  LEFT JOIN orders c ON c.principal_uid = a.principal_uid and c.order_sequence_no = a.order_sequence_no
				where a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $UId)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}


	public function getDepotDocumentItem($userId, $UId) {
		$sql="SELECT a.uid, 
                 a.principal_uid,
		             depot_uid,
		             a.document_number,
		             document_type_uid, 
		             c.document_status_uid, 
		             c.principal_store_uid, 
		             c.depot_principal_store_uid,
								 f.name depot_name, 
								 f.wms, 
								 p.name as principal_name, 
								 c.invoice_date, 
								 f.skip_inpick_stage, 
								 f.delivery_note,
								 f.waiting_dispatch, 
								 c.invoice_total, 
								 c.overide_rep_code_uid, 
								 c.trip_transporter_uid
          FROM  document_master a
          INNER JOIN user_principal_depot b ON a.principal_uid = b.principal_id 
                                            AND a.depot_uid = b.depot_id 
                                            AND  b.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
          LEFT JOIN document_header c ON a.uid = c.document_master_uid
          INNER JOIN depot f ON a.depot_uid = f.uid and f.wms='Y'
          INNER JOIN principal p on a.principal_uid = p.uid
          WHERE a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $UId)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// although this is an item, you will need to loop through multiple items, to get each product
	public function getOrderWithDetailItem($userId, $principalId, $orderSeq) {
		$sql="SELECT a.uid, depot.name depot_name, a.delivery_instructions, principal_store_master.deliver_name, principal_store_master.deliver_add1, principal_store_master.deliver_add2, principal_store_master.deliver_add3,
					 od.product_uid, a.order_number, a.order_sequence_no, od.product_uid, principal_product.product_code, principal_product.product_description,
					 od.quantity, a.date order_date, a.capturedate, a.captureuser_uid, ifnull(pdt.description,document_type.description) document_type_description, od.price_type, od.list_price, od.discount_value, od.nett_price,
					 day.name delivery_day, a.deliverydate, od.pallets, od.price_override_value, od.price_override, opd.description bulk_description, a.document_number
				FROM   orders a
				        LEFT Join document_type ON a.document_type = document_type.uid
								LEFT Join principal_document_type pdt on document_type.uid = pdt.document_type_uid and pdt.principal_uid = a.principal_uid
				        LEFT Join principal_store_master ON a.storechain_uid = principal_store_master.uid
	        			LEFT Join `day` ON principal_store_master.delivery_day_uid = `day`.uid
				      	LEFT Join depot ON principal_store_master.depot_uid = depot.UID
						    LEFT Join user_principal_store ON user_principal_store.principal_store_uid = a.storechain_uid AND user_principal_store.user_uid='".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
								LEFT JOIN user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid={$principalId})
								LEFT JOIN user_principal_chain ON user_principal_chain.principal_chain_uid = principal_store_master.principal_chain_uid AND user_principal_chain.user_uid='".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
								LEFT Join user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid={$principalId})
								INNER JOIN user_principal_depot ON user_principal_depot.principal_id = principal_store_master.principal_uid AND user_principal_depot.depot_id = principal_store_master.depot_uid AND user_principal_depot.user_id='".mysqli_real_escape_string($this->dbConn->connection, $userId)."',
					   orders_detail od
				        LEFT Join principal_product ON od.product_uid = principal_product.uid
						    LEFT Join (select opd.orders_uid, opd.principal_product_uid, group_concat(description separator '<br>') description
        								   from orders_pricing_document opd
        								   where opd.apply_level='".DPL_ITEM."'
        								   group by opd.orders_uid, opd.principal_product_uid) opd ON od.product_uid = opd.principal_product_uid and opd.orders_uid = od.orders_uid
				WHERE a.principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
				AND   a.order_sequence_no = '".mysqli_real_escape_string($this->dbConn->connection, $orderSeq)."'
				AND   a.uid = od.orders_uid
				AND   (user_principal_chain.uid is not null or urc.uid is not null)
				AND   (user_principal_store.uid is not null or ur.uid is not null)";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getOrderPricingDocumentItems($ordersUId, $applyLevel) {
		$sql="SELECT a.uid, a.orders_uid, a.chosen_pricing_document_uid, a.quantity, a.deal_type_uid, a.value, a.description, b.unit_price_type_uid,
					 c.description unit_price_type_description, discount_value
				FROM   orders_pricing_document a,
					   pricing_document b,
					   unit_price_type c
				WHERE a.orders_uid = '".mysqli_real_escape_string($this->dbConn->connection, $ordersUId)."'
				and   a.chosen_pricing_document_uid = b.uid
				and   b.unit_price_type_uid = c.uid
				and   a.apply_level = '".mysqli_real_escape_string($this->dbConn->connection, $applyLevel)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getDocumentStatusArray($limitDropDown) {
		$sql="select uid, status_code, description, asset, dropdown
			  from   status ";
		if ($limitDropDown) $sql.=" where dropdown='1' order by `order` ";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[$row['uid']] = $row;
		}

		return $arr;
	}

	public function getRTDocumentStatusArray($RTstatus = 'Y') {
		$sql="select uid, status_code, description, asset
			  from   status
                      where rt_status = '{$RTstatus}' order by `order` ";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[$row['uid']] = $row;
		}

		return $arr;
	}


	public function getDocumentParentItem($principalId, $dMUId) {
		$sql="SELECT a.uid dm_uid,a.principal_uid,a.depot_uid, depot.name depot_name, a.document_number,a.document_type_uid, ifnull(pdt.description,document_type.description) document_type_description, a.processed_date,
					 a.processed_time,a.invoice_file,
					 document_header.order_date,document_header.invoice_date,document_header.delivery_date,
					 document_header.principal_store_uid,document_header.customer_order_number,document_header.grv_number,
					 document_header.claim_number, trim(document_header.source_document_number) source_document_number, status.description status,
					 document_header.invoice_number,document_header.cases,document_header.selling_price,document_header.exclusive_total,
				 	 document_header.vat_total,document_header.invoice_total,
					 principal_store_master.deliver_name store_name, principal_store_master.uid psm_uid, `day`.name delivery_day,
					 (select distinct 1 from document_master dm2, document_header dh2 where dm2.uid = dh2.document_master_uid and dh2.source_document_number = a.document_number and dm2.depot_uid = a.depot_uid) has_associated_notes
			FROM   document_master a
			        LEFT Join document_type ON a.document_type_uid = document_type.uid
							LEFT Join principal_document_type pdt on a.document_type_uid = pdt.document_type_uid and pdt.principal_uid = a.principal_uid
			        LEFT Join depot ON a.depot_uid = depot.UID
			        LEFT Join document_header ON a.uid = document_header.document_master_uid
			        LEFT Join principal_store_master ON document_header.principal_store_uid = principal_store_master.uid
			        			LEFT Join `day` ON principal_store_master.delivery_day_uid = `day`.uid
			        LEFT Join `status` ON document_header.document_status_uid = `status`.uid
			WHERE a.principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
			AND   a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $dMUId)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getDocumentDetails($dMUId) {
		$sql="SELECT a.*, b.product_description, b.allow_decimal 
          FROM   document_detail a
          LEFT JOIN principal_product b on a.product_uid = b.uid
          WHERE a.document_master_uid='".mysqli_real_escape_string($this->dbConn->connection, $dMUId)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// this enforces permissions !
	public function getUserDepotDocumentDetails($dMUId, $userUId, $arrayIndex="") {
		$sql="SELECT a.uid 
		             dd_uid,a.ordered_qty, 
		             a.document_qty, 
		             a.delivered_qty, 
		             b.product_code, 
		             b.product_description, 
		             c.available, 
		             c.closing,
		             c.pending_dispatch,
		             c.goods_in_transit,
                 dm.document_type_uid, 
                 dm.depot_uid, 
                 a.product_uid, 
                 f.wms,
                 f.waiting_dispatch, 
                 dh.document_status_uid, 
                 dh.principal_store_uid,
                 dh.customer_order_number, 
                 dm.order_sequence_no,
                 dm.document_number, 
                 dm.principal_uid, 
                 dh.invoice_number,
                 dh.buyer_account_reference, 
                 a.batch, 
                 dh.invoice_total, 
                 b.non_stock_item, 
                 b.allow_decimal,
                 dh.overide_rep_code_uid,
                 f.disable_stock_check,
                 f.waiting_dispatch,
                 f.allow_git, 
                 f.allow_git_in
          FROM   document_detail a
          INNER JOIN document_master dm on dm.uid = a.document_master_uid
          INNER JOIN user_principal_depot upd ON dm.principal_uid = upd.principal_id and dm.depot_uid = upd.depot_id and upd.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userUId)."'
          INNER JOIN depot f ON dm.depot_uid = f.uid and f.wms='Y'
          INNER JOIN document_header dh ON a.document_master_uid = dh.document_master_uid
          LEFT JOIN principal_product b on a.product_uid = b.uid
          LEFT JOIN stock c on dm.principal_uid = c.principal_id and dm.depot_uid = c.depot_id and a.product_uid = c.principal_product_uid
    			WHERE a.document_master_uid='".mysqli_real_escape_string($this->dbConn->connection, $dMUId)."'";
		$this->dbConn->dbQuery($sql);
		
 // 	file_put_contents('../../../kwelanga_php/log/err2.txt',$sql, FILE_APPEND); 

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ($arrayIndex=="") $arr[] = $row;
				else $arr[$row[$arrayIndex]] = $row;
			}
		}

		return $arr;
	}

	// NB! Capture date and Order date are {from,to} format
	// this is used as an audit, so the mf lookups must be left joins incase of data anomalies -
	// Skip the user permissions check if blanks in foreign keys, the permissions must be done like, to also allow for a value not found in mf lookup
	public function getUserOrdersArray($userId, $principalId, $captureDate, $orderDate, $capturedBy, $orderSeq) {
		$cd=explode(",",$captureDate);
		$od=explode(",",$orderDate);
		$sql="SELECT o.uid, o.storechain_uid, psm.deliver_name, o.principal_uid, o.order_number, o.order_sequence_no, od.product_uid, pp.product_code,
						 pp.product_description, od.quantity, o.date order_date, o.capturedate, o.deleted, o.edi_created, o.edi_filename, o.document_type,
						 ifnull(pdt.description,dt.description) dt_description, od.chosen_pricing_uid, od.price_type, od.list_price, od.discount_value, od.nett_price,
						 o.deliverydate
				from   orders o
							INNER JOIN (select distinct user_id, principal_id from user_principal_depot where user_principal_depot.user_id='".mysqli_real_escape_string($this->dbConn->connection, $userId)."') user_principal_depot ON user_principal_depot.principal_id = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
							LEFT JOIN document_type dt ON dt.uid = o.document_type
							LEFT Join principal_document_type pdt on dt.uid = pdt.document_type_uid and pdt.principal_uid = o.principal_uid
								LEFT JOIN user_role ON dt.role_id = user_role.role_id and user_role.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."' and user_role.entity_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
					 		LEFT JOIN principal_store_master psm ON o.storechain_uid = psm.uid
					 		LEFT Join user_principal_store ON user_principal_store.principal_store_uid = psm.uid AND user_principal_store.user_uid='".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
							LEFT Join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid={$principalId})
							LEFT Join user_principal_chain ON user_principal_chain.principal_chain_uid = psm.principal_chain_uid AND user_principal_chain.user_uid='".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
							LEFT Join user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid={$principalId}),
					   orders_detail od
							LEFT JOIN principal_product pp ON od.product_uid = pp.uid
							LEFT JOIN user_principal_product ON pp.uid = user_principal_product.principal_product_uid and user_principal_product.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'

					WHERE o.principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
					AND   o.uid = od.orders_uid
					AND   if( hasRole(".mysqli_real_escape_string($this->dbConn->connection, $userId).",".mysqli_real_escape_string($this->dbConn->connection, $principalId).",".ROLE_BYPASS_USER_PRODUCT_RESTRICTION.") ,1,user_principal_product.uid) is not null
					AND   o.deleted != 1
					AND   if(dt.uid is null,1,user_role.uid) is not null
					AND   (if(pp.uid is null,1,user_principal_product.uid) is not null or hasRole(".mysqli_real_escape_string($this->dbConn->connection, $userId).",".mysqli_real_escape_string($this->dbConn->connection, $principalId).",".ROLE_BYPASS_USER_PRODUCT_RESTRICTION."))
					AND   if(psm.uid is null,1,if (urc.uid is not null,urc.uid,user_principal_chain.uid)) is not null
					AND   if(psm.uid is null,1,if (ur.uid is not null,ur.uid,user_principal_store.uid)) is not null ";

			if ($captureDate!="") $sql.=" AND date(o.capturedate) BETWEEN '".mysqli_real_escape_string($this->dbConn->connection, $cd[0])."' AND '".mysqli_real_escape_string($this->dbConn->connection, $cd[1])."' ";
			if ($orderDate!="") $sql.=" AND date(o.date) BETWEEN '".mysqli_real_escape_string($this->dbConn->connection, $od[0])."' AND '".mysqli_real_escape_string($this->dbConn->connection, $od[1])."' ";
			if ($capturedBy!="") $sql.=" AND o.captureuser_uid = '".mysqli_real_escape_string($this->dbConn->connection, $capturedBy)."' ";
			if ($orderSeq!="") $sql.=" AND o.order_sequence_no = '".mysqli_real_escape_string($this->dbConn->connection, $orderSeq)."' ";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// NB! Capture date and Order date are {from,to} format
	// this is used as an audit, so the mf lookups must be left joins incase of data anomalies -
	// Skip the user permissions check if blanks in foreign keys, the permissions must be done like, to also allow for a value not found in mf lookup
	public function getOrdersArray($captureDate, $orderDate, $capturedBy, $orderSeq) {
		$cd=explode(",",$captureDate);
		$od=explode(",",$orderDate);
		$sql="SELECT distinct o.storechain_uid, psm.deliver_name, o.principal_uid, o.order_number, o.order_sequence_no,
						o.date order_date, o.capturedate, o.deleted, o.edi_created, o.edi_filename, o.document_type,
						ifnull(pdt.description,dt.description) dt_description, o.deliverydate, u.uid user_uid, u.full_name capture_name
				from   orders o
					LEFT JOIN document_type dt ON dt.uid = o.document_type
					LEFT Join principal_document_type pdt on dt.uid = pdt.document_type_uid and pdt.principal_uid = o.principal_uid
				 	LEFT JOIN principal_store_master psm ON o.storechain_uid = psm.uid
					LEFT JOIN users u ON u.uid = o.captureuser_uid
				WHERE 1=1 ";

			if ($captureDate!=",") $sql.=" AND date(o.capturedate) BETWEEN '".mysqli_real_escape_string($this->dbConn->connection, $cd[0])."' AND '".mysqli_real_escape_string($this->dbConn->connection, $cd[1])."' ";
			if ($orderDate!=",") $sql.=" AND date(o.date) BETWEEN '".mysqli_real_escape_string($this->dbConn->connection, $od[0])."' AND '".mysqli_real_escape_string($this->dbConn->connection, $od[1])."' ";
			if ($capturedBy!="") $sql.=" AND o.captureuser_uid = '".mysqli_real_escape_string($this->dbConn->connection, $capturedBy)."' ";
			if ($orderSeq!="") $sql.=" AND o.order_sequence_no = '".mysqli_real_escape_string($this->dbConn->connection, $orderSeq)."' ";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getOrdersItem($orderSeq) {
		$sql="SELECT distinct o.storechain_uid, psm.deliver_name, o.principal_uid, o.order_number, o.order_sequence_no,
						o.date order_date, o.capturedate, o.deleted, o.edi_created, o.edi_filename, o.edi_depot_filename, o.document_type, o.processed_depot_uid,
						ifnull(pdt.description,dt.description) dt_description, o.deliverydate, u.uid user_uid, u.full_name capture_name
				from   orders o
					LEFT JOIN document_type dt ON dt.uid = o.document_type
					LEFT Join principal_document_type pdt on dt.uid = pdt.document_type_uid and pdt.principal_uid = o.principal_uid
				 	LEFT JOIN principal_store_master psm ON o.storechain_uid = psm.uid
					LEFT JOIN users u ON u.uid = o.captureuser_uid
				WHERE o.order_sequence_no = '".mysqli_real_escape_string($this->dbConn->connection, $orderSeq)."' ";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}


	public function getDocumentUidByOrderSeq($orderSeq, $principalId) {
		$sql="select o.uid
          from  document_master o
          WHERE o.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
          AND o.order_sequence_no = '".mysqli_real_escape_string($this->dbConn->connection, $orderSeq)."' ";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
        $arr = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC);
		}

		return $arr;
	}


	// This is now also used as a query not just on exceptions...
	public function getElectronicExceptions($principalUId, $reference=false) {
		// NB: The ohd where clause must be where i have it, and not as part of main where clause as otherwise if it is only product on document, the whole order is excl
		// you need to do the complex join to product tbl as authorisations havent yet derived the ppuid
		$sql="select oh.uid oh_uid, ohd.uid ohd_uid, p.name principal_name, client_document_number, oh.document_type_uid, ifnull(pdt.description,dt.description) document_type, psm.deliver_name,
					 oh.created_date, oh.capture_date, oh.order_date, oh.delivery_date, processed_date, oh.cancelled_order_notified, ohd.price_diff_notified, oh.status oh_status, ohd.status status_dtl,
					 oh.status_msg, v.name vendor_name, data_source, if(data_source='WS',oh.ws_unique_creator_id,incoming_file) incoming_ref, oh.user_action_status oh_user_action_status,
					 ohd.user_action_status ohd_user_action_status, reference, general_reference_1, general_reference_2, delivery_instructions, ship_to_gln, ship_to_name,
					 debtors_store_identifier, sales_agent_store_identifier, store_lookup_ref, chain_lookup_ref, depot_lookup_ref, client_line_no, client_page_no, pp.product_description, product_name,
					 quantity, list_price, nett_price, total_price, vat_amount, ohd.vat_rate, ext_price, discount_value, product_gtin, product_sku_gtin, ohd.product_code, ohd.status ohd_status,
					 override_price_type, pp.uid as product_uid, psm.uid as psm_uid, force_skip_unique_order_no, CONCAT(u.full_name, ' (',u.uid,')') as 'deleted_by_user',
		       psm.delivery_day_uid, stk.available stock_available, oh.depot_uid, ohd.amended_quantity, expiry_date
			  from   orders_holding oh
                  left join document_type dt on oh.document_type_uid = dt.uid
                  LEFT Join principal_document_type pdt on dt.uid = pdt.document_type_uid and pdt.principal_uid = oh.principal_uid
                  left join orders_holding_detail ohd on oh.uid = ohd.orders_holding_uid " .(($reference!==false)?"":"and if(ohd.status is null,0,ohd.status) != '".FLAG_STATUS_DELETED."'") . "
                          left join principal_product_depot_gtin ppdg on  (if(ifnull(ohd.product_gtin,'')='','XXX',ohd.product_gtin) = ppdg.outercasing_gtin or if(ifnull(ohd.product_sku_gtin,'')='','XXX',ohd.product_sku_gtin) = ppdg.sku_gtin) and
                                                                           ppdg.principal_product_uid in (select uid from principal_product where principal_uid = {$principalUId})
                               left join principal_product pp2 on ppdg.principal_product_uid = pp2.uid -- the pp from the GTIN join
                          left join principal_product pp on ohd.principal_product_uid = pp.uid -- the pp normal join
                  left join vendor v on oh.vendor_uid = v.uid
                  left join users u on oh.deleted_by = u.uid
                  left join principal_store_master psm on oh.principal_store_uid = psm.uid
                  left join stock stk on (ifnull(pp2.product_code,ifnull(pp.product_code,ohd.product_code))) = stk.stock_item and oh.principal_uid = stk.principal_id and psm.depot_uid = stk.depot_id,
					  principal p
			  where  oh.principal_uid = '{$principalUId}' ".
			  (($reference!==false)?"":" and    (if(oh.status is null,0,oh.status) not in ('".FLAG_ERRORTO_SUCCESS."','".FLAG_STATUS_DELETED."') and oh.status != '' and oh.status != '') ")."
			  and    p.uid = oh.principal_uid ".
			  (($reference!==false)?" and (upper(oh.reference) like upper('%{$reference}%') or upper(oh.client_document_number) like upper('%{$reference}%')) ":"")."
			  and oh.document_type_uid not in ( '" . DT_BUYER_GOODS_INWARD . "')
			  order  by created_date desc, oh.uid";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// This is now also used as a query not just on exceptions...
	public function getOrdersHoldingByFileName($principalUId, $fileName) {
		// NB: The ohd where clause must be where i have it, and not as part of main where clause as otherwise if it is only product on document, the whole order is excl
		$sql="select oh.uid oh_uid, ohd.uid ohd_uid, p.name principal_name, client_document_number, oh.document_type_uid, ifnull(pdt.description,dt.description) document_type, psm.deliver_name,
					 oh.created_date, oh.capture_date, oh.order_date, processed_date, oh.cancelled_order_notified, ohd.price_diff_notified, oh.status oh_status, ohd.status status_dtl,
					 oh.status_msg, v.name vendor_name, data_source, if(data_source='WS',oh.ws_unique_creator_id,incoming_file) incoming_ref, oh.user_action_status oh_user_action_status,
					 ohd.user_action_status ohd_user_action_status, reference, general_reference_1, general_reference_2, delivery_instructions, ship_to_gln, ship_to_name,
					 debtors_store_identifier, sales_agent_store_identifier, store_lookup_ref, chain_lookup_ref, depot_lookup_ref, client_line_no, client_page_no, pp.product_description, product_name,
					 quantity, list_price, nett_price, total_price, vat_amount, ohd.vat_rate, ext_price, discount_value, product_gtin, ohd.product_code, ohd.status ohd_status,
					 override_price_type, pp.uid as product_uid, psm.uid as psm_uid
			  from   orders_holding oh
						left join document_type dt on oh.document_type_uid = dt.uid
						LEFT Join principal_document_type pdt on dt.uid = pdt.document_type_uid and pdt.principal_uid = oh.principal_uid
					 	left join orders_holding_detail ohd on oh.uid = ohd.orders_holding_uid
							left join principal_product pp on ohd.principal_product_uid = pp.uid
						left join vendor v on oh.vendor_uid = v.uid
						left join principal_store_master psm on oh.principal_store_uid = psm.uid,
					 principal p
			  where  oh.principal_uid = '{$principalUId}'
			  and    p.uid = oh.principal_uid
			  and    upper(oh.incoming_file) like upper('%{$fileName}%')
			  order  by created_date desc, oh.uid";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getElectronicExceptionsCount($principalUId) {
		// This will include suspended documents requiring authorisation
		// NB: The ohd where clause must be where i have it, and not as part of main where clause as otherwise if it is only product on document, the whole order is excl
		$sql="select count(*) cnt
			  from   orders_holding oh
			  where  oh.principal_uid = '{$principalUId}'
			  and    ifnull(oh.status,'S') not in ('".FLAG_ERRORTO_SUCCESS."','".FLAG_STATUS_DELETED."','')";

		$this->dbConn->dbQuery($sql);

		$cnt=0;
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$cnt=$row["cnt"];
			}
		}

		return $cnt;
	}

	public function getUnacceptedOrderStatuses() {
		/*
		 * 77	Delivery & POD - OK
				78	Dirty POD
				47	Cancelled Order
				81	Processed
				76	Invoiced
				75	Accepted
				79	Status Error
				74	Unaccepted
				83	Unknown Status
				86	Queued for Processing
				87	In-Pick
		*/

		$statusList=DST_UNACCEPTED.",".DST_QUEUED.",83,79";
		/*
	  $sql="select group_concat(uid separator ',') status_list
					from   status
					where  rt_status = 'Y'
					and    processing_order is not null
					and    processing_order < 3";

		$this->dbConn->dbQuery($sql);

		$statusList="";
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$statusList=$row["status_list"];
			}
		} else $statusList="";

		*/

		return $statusList;
	}


	public function getUnacceptedDepotOrdersCount($userId) {
		// its done this way (instead of hardcoding) to make more portable, but mainly to improve query speed saving a join
		$sql="select count(*) cnt
					from  user_principal_depot a,
							 	document_master b,
							 	document_header c,
								depot d
					where  a.user_id = '{$userId}'
					and    a.principal_id = b.principal_uid
					and    a.depot_id = b.depot_uid
					and    b.document_type_uid in (".DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE.")
					and    b.uid = c.document_master_uid
					and    c.document_status_uid in (".($this->getUnacceptedOrderStatuses()).")
					and    a.depot_id = d.uid
					and    d.wms = 'Y'";

		$this->dbConn->dbQuery($sql);

		$cnt=0;
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$cnt=$row["cnt"];
			}
		}

		return $cnt;
	}

	// the principal limiter has been re-applied as @ 2016-04-13
	public function getOrdersStatusCount($userId) {
		$uAList=$this->getUnacceptedOrderStatuses();

		if (!isset($_SESSION)) session_start();
		$principalId  = $_SESSION['principal_id'];

		$sql="select IFNULL(sum(if(document_status_uid=".DST_ACCEPTED.",1,0)),0) accepted_cnt,
								 IFNULL(sum(if(document_status_uid=".DST_INPICK.",1,0)),0) inpick_cnt,
								 IFNULL(sum(if(document_status_uid=".DST_INVOICED.",1,0)),0) invoiced_cnt,
								 IFNULL(sum(if(document_status_uid=".DST_WAITING_DISPATCH.",1,0)),0) waitDispatch_cnt,
								 IFNULL(sum(if(document_status_uid in ({$uAList}),1,0)),0) unaccepted_cnt
					from  user_principal_depot a,
							 	document_master b,
							 	document_header c,
								depot d
					where  a.user_id = '{$userId}'
					and    a.principal_id = '{$principalId}'
					and    a.principal_id = b.principal_uid
					and    a.depot_id = b.depot_uid
					and    b.document_type_uid in (".DT_ORDINV.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE.",".DT_UPLIFTS.",".DT_DESTRUCTION_DISPOSAL.",".DT_WALKIN_INVOICE.")
					and    b.uid = c.document_master_uid
					and    c.document_status_uid in ({$uAList},".DST_ACCEPTED.",".DST_INPICK.",".DST_INVOICED.")
					and    a.depot_id = d.uid
					and    d.wms = 'Y'
                                        " . (isset($_SESSION['depot_id'])?(" and    b.depot_uid = '" . $_SESSION['depot_id'] . "'"):('')) . "";

		$this->dbConn->dbQuery($sql);

		$cntArr=array("accepted_cnt"=>"0","invoiced_cnt"=>"0","unaccepted_cnt"=>"0");
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$cntArr=$row;
			}
		}

		return $cntArr;
	}


	// remember that this sql is same as the one for report 27 !! so keep in synch where necessary
	public function getPriceVarianceComparisonList($principalUId, $processedDate) {
		$sql = "select oh.reference, ifnull(pdt.description,dt.description) document_type, oh.processed_date, dm.document_number, ohd.price_diff_notified, pp.product_description,
						 psm.deliver_name store_name, dpt.name depot_name,
						 dd.selling_price list_price, dd.discount_value discount_value, dd.net_price nett_price, dd.extended_price ext_price,
						 dd.vat_amount vat_amount, dd.vat_rate vat_rate, dd.total total_price,
						 ohd.list_price edi_list_price, ohd.discount_value edi_discount_value, ohd.nett_price edi_nett_price, ohd.ext_price edi_ext_price,
						 ohd.vat_amount edi_vat_amount, ohd.vat_rate edi_vat_rate, ohd.total_price edi_total_price
				from orders_holding oh
					  	  	inner join orders_holding_detail ohd on oh.uid = ohd.orders_holding_uid
					  	  	inner join document_type dt on dt.uid = oh.document_type_uid
									LEFT Join principal_document_type pdt on dt.uid = pdt.document_type_uid and pdt.principal_uid = oh.principal_uid
					  	  	left join principal_product pp on ohd.principal_product_uid = pp.uid
							left join principal_store_master psm on oh.principal_store_uid = psm.uid
					  	  	left join document_header dh on dh.customer_order_number = oh.reference -- this must come first so indexes are used properly
						  	left join document_master dm on dm.uid = dh.document_master_uid and dm.principal_uid = oh.principal_uid and dm.document_type_uid = oh.document_type_uid
					  	  	left join document_detail dd on dm.uid = dd.document_master_uid and dd.product_uid = ohd.principal_product_uid
							left join depot dpt on dpt.uid = dm.depot_uid
				where oh.principal_uid = '{$principalUId}'
				and   date(oh.processed_date) = '{$processedDate}'
				and   exists (select 1 from orders_holding_detail c where c.orders_holding_uid = oh.uid and c.price_diff_notified in ('Q','S'))
				order by oh.processed_date desc, oh.uid, ohd.principal_product_uid";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;

	}

	public function getDocumentDepotAuditLog($userId, $dMUId) {

		return $this->dbConn->dbGetAll("select a.*, c.full_name, concat(s.description, ' (', a.document_status_uid ,')') as status_description
				from  document_depot_audit_log a
							INNER JOIN document_master dm ON a.document_master_uid = dm.uid
							INNER JOIN user_principal_depot b ON dm.principal_uid = b.principal_id and dm.depot_uid = b.depot_id and  b.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
							LEFT JOIN users c ON a.changed_by = c.uid
                                                        LEFT JOIN `status` s on a.document_status_uid = s.uid
				where a.document_master_uid = '".mysqli_real_escape_string($this->dbConn->connection, $dMUId)."'
                                ORDER BY a.activity_date DESC
                                ");

	}

	public function getDocumentDepotAuditStatusPrinted($dMUId, $statusUId) {

                $comment = "PRINTED";

		return $this->dbConn->dbGetAll("select a.*, c.full_name
				from  document_depot_audit_log a
							INNER JOIN document_master dm ON a.document_master_uid = dm.uid
							LEFT JOIN users c ON a.changed_by = c.uid
				where   a.document_master_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $dMUId) . "'
                                  AND   a.document_status_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $statusUId) . "'
                                  AND   a.comment = '" . $comment . "'");

	}


	// should only be called once dmUIds have been gotten through secure function
	// must show all special fields, including new ones unstored
	public function getDocumentsSpecialFields($dMUIdList) {

		$sql="select dm.uid dm_uid, group_concat(sff.name SEPARATOR '|') sff_names, group_concat(ifnull(sfd.value,'') SEPARATOR '|') sfd_values,
								 group_concat(sff.uid SEPARATOR '|') sff_uids
					from   document_master dm
								 		inner join special_field_fields sff on dm.document_type_uid = ifnull(sff.document_type_uid,dm.document_type_uid) and
																													 sff.principal_uid = dm.principal_uid and
																													 sff.type = 'TT' and
																													 sff.status = 'A'
								 		left join special_field_details sfd on dm.uid = sfd.entity_uid and
																													 sff.uid = sfd.field_uid
					where  dm.uid in ({$dMUIdList})
					group  by dm.uid";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[$row["dm_uid"]] = $row;
			}
		}

		return $arr;

	}

	// must return all special fields, used or not in dm, but only show deleted fields if they are used
	public function getDocumentSpecialFields($dMUId, $dTUId) {
		$sql="select entity_uid dm_uid, sff.name, sfd.value, sff.uid, sff.status, sff.editable, sff.value_validation, sff.value_list, sff.label_list
					from	 special_field_fields sff
								 		left join document_master dm on dm.uid = '{$dMUId}' and
																										dm.document_type_uid = ifnull(sff.document_type_uid,dm.document_type_uid) and
																										dm.principal_uid = sff.principal_uid
								 		left join special_field_details sfd on dm.uid = sfd.entity_uid and
																													 sff.uid = sfd.field_uid
					where  sff.type = 'TT'
					and    (sff.status!='D' || (sff.status='D' and sfd.value is not null))
					and    (sff.document_type_uid is null or (sff.document_type_uid='{$dTUId}'))";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;

	}



	public function getDocumentEPODItemByDOCMASTID($dMUId){

		$sql="select
                      e.*,
                       m.principal_uid
                      from  epod_notice e
                      inner join document_master m on e.document_master_uid = m.uid
                      where e.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $dMUId);

		return $this->dbConn->dbGetAll($sql);
	}


	public function getDocumentEPODItemByNoticeID($dnId){

		$sql="select
                          e.*
                      from  epod_notice e
                      where e.delivery_notice_id = '" . mysqli_real_escape_string($this->dbConn->connection, $dnId) . "'";

		return $this->dbConn->dbGetAll($sql);

	}


	public function getDocumentEPODItem($epodUId){

		$sql="select
                          e.*,
                          m.principal_uid
                      from  epod_notice e
                      inner join document_master m on e.document_master_uid = m.uid
                      where e.uid = " . mysqli_real_escape_string($this->dbConn->connection, $epodUId);

		return $this->dbConn->dbGetAll($sql);

	}

	public function getQueuedEPOD($flag){

          $sql="select *
                    from   epod_notice
                    where  request_status = '" . mysqli_real_escape_string($this->dbConn->connection, $flag) . "'";

          return $this->dbConn->dbGetAll($sql);

	}


	public function getDocumentEPODInvoiceDetails($dMUId){

          $sql="select

		  m.uid,
		  m.document_number,
		  m.depot_uid,
                  m.principal_uid,
		  m.document_type_uid,
		  h.document_status_uid,
                  h.invoice_total,
                  h.customer_order_number,
                  h.invoice_date,
                  h.delivery_date,
                  p.uid as psm_uid,
                  p.deliver_name,
                  p.epod_store_flag,
                  p.epod_rsa_id,
                  p.epod_cellphone_number

              from  document_master m
              inner join document_header h on m.uid = h.document_master_uid
              inner join principal_store_master p on h.principal_store_uid = p.uid

              where m.uid =  " . mysqli_real_escape_string($this->dbConn->connection, $dMUId);

          return $this->dbConn->dbGetAll($sql);

	}


	public function getDocumentReasonByAssociatedStatus($statusUId) {

    $sql="select uid, 
                 code, 
                 description
          from reason_code
          where '".mysqli_real_escape_string($this->dbConn->connection, $statusUId)."' in (associated_status_uid)";

    return $this->dbConn->dbGetAll($sql);

  }


	public function getStockItemMovement($principalId, $depotId, $productId, $sinceDate = false) {

          $sinceSQL = "";
          if($sinceDate !== false){
            $sinceSQL = " and h.invoice_date >= date('".mysqli_real_escape_string($this->dbConn->connection, $sinceDate)."')  -- will need to be amended in due course ";
          }
          $sql="SELECT
                        t.description as 'Document Type',
                        m.document_number as 'Document Number',
                        d.document_qty as 'Document Qty',
                        h.invoice_date as 'Invoice Date',
                        s.description as 'Status'
                FROM document_master  m
                        INNER JOIN document_header h on m.uid = h.document_master_uid
                        INNER JOIN document_detail d on m.uid = d.document_master_uid and d.product_uid = '".mysqli_real_escape_string($this->dbConn->connection, $productId)."'
                        INNER JOIN principal_product p on d.product_uid = p.uid
                        INNER JOIN document_type t on m.document_type_uid = t.uid
                        inner join depot w on m.depot_uid = w.uid
                        inner join principal c on m.principal_uid = c.uid
                        inner join `status` s on h.document_status_uid = s.uid
                where m.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
                        and m.depot_uid = '".mysqli_real_escape_string($this->dbConn->connection, $depotId)."'
                        and (h.document_status_uid in (76, 77,78, 81) or m.document_type_uid in (5))
                        ".$sinceSQL."
                ORDER BY h.invoice_date ASC";

          return $this->dbConn->dbGetAll($sql);

        }

  // Send Invoiced Orders to PnP through the WS
  // must return all rows even if data errors so that it can be loaded as an error
  public function getPnPWSInvoiced(){
          /* old : improved for performance
          $sql="select a.uid, 
                       a.principal_uid, 
                       a.document_number, 
                       b.order_date, 
                       b.invoice_date, 
                       b.delivery_date, 
                       b.principal_store_uid,
                       d.bill_name, 
                       d.bill_add1, 
                       d.bill_add2, 
                       d.bill_add3, 
                       d.ean_code,
                       b.customer_order_number, 
                       b.invoice_number, 
                       b.cases, 
                       b.selling_price selling_price, 
                       b.exclusive_total, 
                       b.vat_total, 
                       b.invoice_total,
                       e.principal_gln , 
                       e.vat_num principal_vat_number
                from  document_master a,
                    document_header b,
                    principal_preference c,
                    principal_store_master d,
                    principal e
                where a.uid = b.document_master_uid
                and   a.document_type_uid in (1,13)
                and   b.document_status_uid in (76,77,78)
                and   a.principal_uid = c.principal_uid
                and   c.pnp_ws_invoice_enabled = 'Y'
                and   (b.invoice_date >= c.pnp_ws_starting_invoice_date or ifnull(b.invoice_date,'')='')
                and   b.principal_store_uid = d.uid
                and   d.retailer = ".RETAILER_PNP."
                and   a.principal_uid = e.uid
                and   not exists (select 1 from smart_event se where se.type = '".SE_INVOICE_UPLOAD."' and type_uid = a.principal_uid and data_uid = a.uid)";
*/
          // rewritten from above as an attempt to improve speed and indexing usage
          // - it is useless to send PnP invoices after the truck has come and gone
          $sql = "select a.uid, 
                         a.principal_uid, 
                         a.document_number, 
                         b.order_date, 
                         b.invoice_date, 
                         b.delivery_date, 
                         b.principal_store_uid,
                         d.bill_name, 
                         d.bill_add1, 
                         d.bill_add2, 
                         d.bill_add3, 
                         d.ean_code,
                         b.customer_order_number, 
                         b.invoice_number, 
                         b.cases, 
                         b.selling_price selling_price, 
                         b.exclusive_total, 
                         b.vat_total, 
                         b.invoice_total,
                         e.principal_gln , 
                         e.vat_num principal_vat_number, 
                         e.name principal_name
                  from       principal_preference c
                  inner join principal_store_master d on c.principal_uid = d.principal_uid and
                             d.retailer = ".RETAILER_PNP."
                   left join principal e on d.principal_uid = e.uid
                   inner join document_header b on b.document_status_uid in (76,77,78) 
                                                and (b.invoice_date >= c.pnp_ws_starting_invoice_date 
                                                and b.invoice_date >= DATE_SUB(CURDATE(),INTERVAL 3 DAY)) 
                                                and b.principal_store_uid = d.uid
                   inner join document_master a on  a.uid = b.document_master_uid 
                                                and a.document_type_uid in (1,13) 
                                                and
                                                a.principal_uid = d.principal_uid
                   where not exists (select 1 from smart_event se
                  									 where se.type = '".SE_INVOICE_UPLOAD."'
                  									 and type_uid = a.principal_uid
                  									 and data_uid = a.uid
                  									 and se.status in ('".FLAG_ERRORTO_SUCCESS."','".FLAG_ERRORTO_INFO."','".FLAG_ERRORTO_REJECTED."')
                  									)
                   and c.pnp_ws_invoice_enabled = 'Y'";

          $rsHdr = $this->dbConn->dbGetAll($sql);

          if (sizeof($rsHdr)==0) return $rsHdr;

          // get the unique uids for 2nd query
          $uniqueUIds = array();
          foreach ($rsHdr as $row) {
            $uniqueUIds[$row["uid"]] = $row["uid"];
          }

          // you must use left joins as the barcodes could have been removed and you don't want it to not find the dtl rows
          $sql="select dd.document_master_uid, 
                       dd.product_uid, 
                       dd.document_qty, 
                       dd.selling_price, 
                       dd.discount_value,
                       dd.net_price, 
                       dd.extended_price,
                       dd.vat_amount, 
                       dd.vat_rate, 
                       dd.total, 
                       ppdg.outercasing_gtin, 
                       dd.line_no,
                       pp.product_code, 
                       pp.product_description, 
                       pp.weight, 
                       pp.items_per_case
                from  document_detail dd
                left  join principal_product pp on dd.product_uid = pp.uid
                left  join principal_product_depot_gtin ppdg on dd.product_uid = ppdg.principal_product_uid
                where dd.document_master_uid in (".implode(",",$uniqueUIds).")";

          $this->dbConn->dbQuery($sql);

          $rsDtl = array();
          while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
            $rsDtl[$row["document_master_uid"]][] = $row;
          }

          return array("header"=>$rsHdr, "detail"=>$rsDtl);

  }

  // Send Invoiced Orders to PnP through the WS
  // must return all rows even if data errors so that it can be loaded as an error
  public function getCheckersWSInvoiced(){
    // rewritten from above as an attempt to improve speed and indexing usage
    // - it is useless to send PnP invoices after the truck has come and gone
    $sql = "select a.uid, 
                   a.principal_uid, 
                   a.document_number, 
                   b.order_date, 
                   b.invoice_date, 
                   b.delivery_date, 
                   b.principal_store_uid,
                   d.bill_name, 
                   d.bill_add1, 
                   d.bill_add2, 
                   d.bill_add3, 
                   d.ean_code,
                   d.checkers_region,
                   b.customer_order_number, 
                   b.invoice_number, 
                   b.cases, 
                   b.selling_price selling_price, 
                   b.exclusive_total, 
                   b.vat_total, 
                   b.invoice_total,
                   e.principal_gln , 
                   e.vat_num principal_vat_number, 
                   e.name principal_name, 
                   pv.username, 
                   pv.password,
                   pv.vendor_system
                  from  principal_preference c
                  inner join principal_store_master d on c.principal_uid = d.principal_uid 
                                                    and  d.retailer = ".RETAILER_CHECKERS."
                  left join principal e on d.principal_uid = e.uid
                  inner join document_header b on b.document_status_uid in (76,77,78) 
                                               and (b.invoice_date >= c.checkers_ws_starting_invoice_date 
                                               and b.invoice_date >= DATE_SUB(CURDATE(),INTERVAL 2 DAY)) 
                                               and b.principal_store_uid = d.uid
                  inner join document_master a on a.uid = b.document_master_uid 
                                               and a.document_type_uid in (1,13) 
                                               and a.principal_uid = d.principal_uid
                  inner join principal_vendor pv on pv.principal_uid = c.principal_uid 
                                                 and pv.vendor_uid = ".V_CHECKERS_VENDOR." 
                                                 and IFNULL(pv.trade_document_type,'') = IF(pv.trade_document_type = 'IGNOREORDERS','IGNOREORDERS','') 
                                                 and IFNULL(pv.region,'') = IFNULL(d.checkers_region,'')
                                                 and pv.status = '".FLAG_STATUS_ACTIVE."'
                  where not exists (select 1 
                                    from smart_event se
                                    where se.type = '".SE_INVOICE_UPLOAD_REST."'
                                    and type_uid = a.principal_uid
                                    and data_uid = a.uid
                                    and se.status in ('".FLAG_ERRORTO_SUCCESS."','".FLAG_ERRORTO_INFO."','".FLAG_ERRORTO_REJECTED."')
                                   )
                  and c.checkers_ws_invoice_enabled = 'Y'";
              //    echo "<br>";
              //    echo $sql;
              //    
              //    echo "<br>";

    $rsHdr = $this->dbConn->dbGetAll($sql);

    if (sizeof($rsHdr)==0) return $rsHdr;

    // get the unique uids for 2nd query
    $uniqueUIds = array();
    foreach ($rsHdr as $row) {
      $uniqueUIds[$row["uid"]] = $row["uid"];
    }

    // you must use left joins as the barcodes could have been removed and you don't want it to not find the dtl rows
    $sql="select dd.document_master_uid, 
                 dd.product_uid, 

                 if(pp.principal_uid = 64  AND dd.bbq_updated = 'Y',round(dd.document_qty / dd.conversion,0),
                 if(pp.principal_uid = 400 AND pp.alt_code = 'm' AND dd.bbq_updated = 'Y',round(dd.document_qty / dd.conversion,0),
                 if(pp.principal_uid = 403 AND dd.bbq_updated = 'Y',round(dd.document_qty / dd.conversion,0),
                    dd.document_qty))) as 'document_qty',
                 
                 if(pp.principal_uid = 64 AND dd.bbq_updated = 'Y',round(dd.selling_price / dd.conversion,0),
                 if(pp.principal_uid = 400, ohd.list_price,
                 if(pp.principal_uid = 403, ohd.list_price,
                    dd.selling_price))) as 'selling_price',      
                 
                 dd.discount_value,

                 if(pp.principal_uid = 64 AND dd.bbq_updated = 'Y',round(dd.net_price / dd.conversion,0),
                 if(pp.principal_uid = 400, ohd.list_price,
                 if(pp.principal_uid = 403, ohd.list_price,
                    dd.net_price))) as 'net_price', 

                 dd.extended_price,
                 dd.vat_amount, 
                 dd.vat_rate, 
                 dd.total, 
                 ppdg.outercasing_gtin, 
                 dd.line_no,
                 pp.product_code, 
                 pp.product_description, 
                 pp.weight, 
                 pp.items_per_case, 
                 ppdg.sku_gtin,
                 pp.packing, 
                 ohd.list_price, 
                 ohd.product_gtin                  
           from  document_detail dd
           LEFT JOIN document_master dm on dm.uid = dd.document_master_uid 
           LEFT JOIN .orders_holding oh ON oh.order_sequence_number = dm.order_sequence_no
           LEFT JOIN .orders_holding_detail ohd ON ohd.orders_holding_uid = oh.uid AND ohd.principal_product_uid = dd.product_uid
           left join principal_product pp on dd.product_uid = pp.uid
           left join principal_product_depot_gtin ppdg on dd.product_uid = ppdg.principal_product_uid
           where dd.document_master_uid in (".implode(",",$uniqueUIds).")";
           
//           echo $sql;

    $this->dbConn->dbQuery($sql);

    $rsDtl = array();
    while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
      $rsDtl[$row["document_master_uid"]][] = $row;
    }

    return array("header"=>$rsHdr, "detail"=>$rsDtl);

  }

  // This function was developed especially for the orderProcessingCard to show additional fields not in the main get method
  // I didn't want to add yet another field to that function as the resultset is getting too large !

  public function getOrderProcessingCardExtraFields($dmUId) {

    // some manual captures come thru OH (eg. authorisations), hence the username lookup
    $sql="select dh.additional_details, 
                 dm.additional_type, 
                 if(u.full_name <>'' && u.full_name <> NULL,u.full_name,dh.captured_by) as 'full_name',
                 dm.incoming_file as 'orderLink', 
                 dm.rwr_file as 'map'           
                 
          from   document_master dm,
                 document_header dh
                 left join users u on dh.data_source='".DS_CAPTURE."' and u.uid = dh.captured_by
          where  dm.uid = dh.document_master_uid
          and    dm.uid = '".$dmUId."'";

    return $this->dbConn->dbGetAll($sql);

  }


  public function getDocumentServiceTypesAll() {

    $sql="select
              uid, description
            from   document_service";

    return $this->dbConn->dbGetAll($sql);

  }
  
  public function getDocumentRepCodes($principalId) {

    $sql="select uid,concat(ps.first_name, ' ', ps.surname) as 'description'
          from .principal_sales_representative ps
          where ps.principal_uid = " . $principalId . " 
          and   ps.`status` = 'A' 
          and   ps.`allow_override` = 'Y';" ;

    return $this->dbConn->dbGetAll($sql);

  }
  

    public function getOrderStockAvailableFlag($depotUId, $principalUId){

    // gets the flag and level for the flag.
    $sql = "SELECT
                  IF(d.available_stock_check = 'Y', 'Y', p.available_stock_check) AS 'check_flag',
                  IF(d.available_stock_check = 'Y', 'DEPOT', 'PRINCIPAL') AS 'check_level'
            FROM depot d
                  LEFT JOIN principal_preference p ON p.principal_uid = {$principalUId}
            WHERE d.uid = {$depotUId}";

    return $this->dbConn->dbGetAll($sql);

  }

  public function userHasAccessToDocument($dmUId, $userUId) {
    $sql = "SELECT 1
            FROM   document_master a
                      INNER JOIN document_header ON a.uid = document_header.document_master_uid
                      LEFT JOIN principal_store_master ON document_header.principal_store_uid = principal_store_master.uid
                      LEFT Join user_principal_store ON user_principal_store.principal_store_uid = document_header.principal_store_uid AND user_principal_store.user_uid='{$userUId}'
                      LEFT Join user_role ur on {$userUId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid=a.principal_uid)
                      LEFT JOIN user_principal_chain upc ON upc.principal_chain_uid = principal_store_master.principal_chain_uid AND upc.user_uid='{$userUId}'
                      LEFT Join user_role urc on {$userUId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid=a.principal_uid)
                      INNER JOIN user_principal_depot ON user_principal_depot.principal_id = principal_store_master.principal_uid AND user_principal_depot.depot_id = principal_store_master.depot_uid AND user_principal_depot.user_id='{$userUId}'
            WHERE a.uid = '{$dmUId}'
            and   (user_principal_store.uid is not null or ur.uid is not null)
            AND   (upc.uid is NOT NULL or urc.uid is NOT NULL) ";

    $rs = $this->dbConn->dbGetAll($sql);

    if (count($rs)==0) return false;

    return true;
  }

  public function getDeliveryDetails($dmUId, $userUId) {

    $sql = "SELECT transporter_name, truck_registration, chep_pallet_number
            FROM   document_header
            WHERE  document_master_uid = '{$dmUId}'";

    $rs = $this->dbConn->dbGetAll($sql);

    return $rs;

  }

  public function getTripSheetInvoices($depotId, $principalId) {
  	
  	if ($principalId==390) {
  		$srtorder= "GROUP BY pr.name, wda.wh_area, psm.deliver_name, dm.document_type_uid, dm.document_number";
  	} else {
  		$srtorder= "GROUP BY pr.name, a.description, dh.invoice_date, psm.deliver_name, dm.document_type_uid, dm.document_number";
  	}

    $sql = "SELECT dm.uid as 'dm_uid',
                   pr.name as 'Principal',
                   dm.principal_uid as 'PrincipalID',
                   substr(dm.document_number,3,6) as 'Docno',
                   psm.deliver_name as 'Store',
                   sum(dd.document_qty) as 'Cases',
                   sum(dd.document_qty * pp.weight) as 'Weight',
                   s.description as 'Dstatus',
                   dt.description as 'Dtype',
                   dh.invoice_date AS 'Invoice Date',
                   a.description AS 'Area',                   
                   wda.wh_area AS 'W_Area'
            FROM   document_master dm
            INNER JOIN document_header dh on dm.uid = dh.document_master_uid
            INNER JOIN document_detail dd on dm.uid = dd.document_master_uid
            INNER JOIN depot d ON dm.depot_uid = d.uid
            INNER JOIN principal_store_master psm on psm.uid = dh.principal_store_uid
            LEFT JOIN  area a on psm.area_uid = a.uid
            LEFT JOIN  warehouse_store_master wsm ON psm.warehouse_link = wsm.link
            LEFT JOIN  warehouse_area wda ON wda.uid = wsm.delivery_area
            INNER JOIN principal_product pp ON dd.product_uid = pp.uid
            INNER JOIN document_type dt on dt.uid = dm.document_type_uid
            INNER JOIN principal pr on pr.uid = dm.principal_uid
            INNER JOIN `status` s on dh.document_status_uid = s.uid
            WHERE  dm.depot_uid = $depotId
            AND    dm.document_type_uid in (1,2,6,13)
            AND    dh.document_status_uid in (76,77,78)
            AND    dh.invoice_date > d.tripsheet_switch_on_date
            AND    dh.on_a_tripsheet_number <= 0 ".
            $srtorder ;
            
    $rs = $this->dbConn->dbGetAll($sql);

    return $rs;

  }

   public function getDocumentsOnTripsheet($principalId,$tsnumber) {
  	
    $sql = "select dm.uid as 'dm_uid',
                   p.name as 'Principal',
                   substr(dm.document_number,3,6) as 'Docno',
                   psm.deliver_name as 'Store',
                   dh.cases as 'Cases',
                   dh.invoice_total AS 'total',
                   dts.tripsheet_number,
                   t.name
           from   document_tripsheet dts,
                  document_header dh,
                  document_master dm,
                  transporter t,
                  principal_store_master psm,
                  principal p
           where  dts.document_master_uid = dm.uid
           and    dm.uid  = dh.document_master_uid
           and    psm.uid = dh.principal_store_uid
           and    dts.transporter_id = t.uid
           and    p.uid = dm.principal_uid
           and    dm.principal_uid = '". mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
           and    dts.tripsheet_removed_date is null
           and    dts.tripsheet_number = '". mysqli_real_escape_string($this->dbConn->connection, $tsnumber)."'";

    $rc = $this->dbConn->dbGetAll($sql);

    return $rc;

  } 
 
  public function  InvoicesDetailsbyTripsheetNumberAsco($prO) {
  	
    $sql = "SELECT dm.uid as 'dm_uid',
                   pr.name as 'Principal',
                   dm.principal_uid as 'PrincipalID',
                   substr(dm.document_number,3,6) as 'Docno',
                   psm.deliver_name as 'Store',
                   psm.deliver_add1 as 'add1',
                   dh.cases as 'Cases',
                   dh.invoice_total AS 'total',
                   
                   
                   sum(if(pp.product_code = 'HBB-003 18X70G' ,dd.document_qty,0))  as 'BUN 70g',
                   sum(if(pp.product_code = 'HBP001' ,dd.document_qty,0))  as 'FLM HBP',
                   sum(if(pp.product_code = 'HBS001' ,dd.document_qty,0))  as 'FLM HBS',
                   sum(if(pp.product_code = 'HDP001' ,dd.document_qty,0))  as 'FLM HDP',
                   sum(if(pp.product_code = 'HDS001' ,dd.document_qty,0))  as 'FLM HDS',
                   sum(if(pp.product_code = 'JFR' ,dd.document_qty,0))  as 'Jumbo French',
                   sum(if(pp.product_code = 'FL001' ,dd.document_qty,0))  as 'Foot Long',
                   sum(if(pp.product_code = 'PITA-001 (10)' ,dd.document_qty,0))  as 'Pita',
                   sum(if(pp.product_code = 'WR001-25CM' ,dd.document_qty,0))  as 'Wraps',
                   sum(if(pp.product_code = 'AIB_B' ,dd.document_qty,0))  as 'AIB_B',
                   sum(if(pp.product_code = 'AIB_W' ,dd.document_qty,0))  as 'AIB_W',
                   sum(if(pp.product_code = 'BR_B'  ,dd.document_qty,0))   as 'BR_B',
                   sum(if(pp.product_code = 'BR_W'  ,dd.document_qty,0))   as 'BR_W',
                   sum(dd.document_qty * pp.weight) as 'Weight',
                   s.description as 'Dstatus',
                   dh.tripsheet_number AS 'TripNo',
                   dh.tripsheet_date   AS 'Date',
                   tp.name AS 'Transporter_Name',
                   tp.address1 AS 'Transporter_Add1',
                   tp.address2 AS 'Transporter_Add2',
                   tp.address3 AS 'Transporter_Add2',
                   tp.email    AS 'Transporter_email',
                   d.Name      AS 'Depot',
                   d.depot_email_list AS 'Depot_email',
                   u.full_name AS 'User'
            FROM document_header dh,
                 document_master dm,
                 document_detail dd,
                 depot d,
                 principal_store_master psm,
                 principal_product pp,
                 principal pr,
                 status  s,
                 transporter tp,
                 users u
           WHERE     dh.tripsheet_number = '". mysqli_real_escape_string($this->dbConn->connection, $prO->postFindnumber)."'
           AND       dm.depot_uid        = '". mysqli_real_escape_string($this->dbConn->connection, $prO->depotUId) ."'
           AND       dm.uid              = dh.document_master_uid
           AND       dm.uid         = dd.document_master_uid
           AND       dm.depot_uid   = d.uid
           AND       psm.uid        = dh.principal_store_uid
           AND   dd.product_uid = pp.uid
           AND   pr.uid         = dm.principal_uid
           AND   s.uid          = dh.document_status_uid
           AND   pp.uid         = dd.product_uid
           AND   tp.uid         = dh.trip_transporter_uid
           AND   u.uid          = tripsheet_created_by
           GROUP BY psm.deliver_name,dm.document_number";

    $rs = $this->dbConn->dbGetAll($sql);

    return $rs;

  } 

  public function  InvoicesDetailsbyTripsheetNumberProduct($prO) {
  	
    $sql = "SELECT dm.uid as 'dm_uid',
                   pr.name as 'Principal',
                   dm.principal_uid as 'PrincipalID',
                   substr(dm.document_number,3,6) as 'Docno',
                   psm.deliver_name as 'Store',
                   psm.deliver_add1 as 'add1',
                   dh.cases as 'Cases',
                   dh.invoice_total AS 'total',
                   pp.product_code AS 'product_code',
                   pp.product_description AS 'product_description',
                   pp.allow_decimal,
                   dd.product_uid AS 'Product_uid',
                   dd.document_qty AS 'Quantity',
                   dd.document_qty * pp.weight as 'Weight',
                   dd.extended_price,
                   s.description as 'Dstatus',
                   dh.tripsheet_number AS 'TripNo',
                   dh.tripsheet_date   AS 'Date',
                   tp.name AS 'Transporter_Name',
                   tp.address1 AS 'Transporter_Add1',
                   tp.address2 AS 'Transporter_Add2',
                   tp.address3 AS 'Transporter_Add2',
                   tp.email    AS 'Transporter_email',
                   d.Name      AS 'Depot',
                   d.depot_email_list AS 'Depot_email',
                   u.full_name AS 'User'
            FROM document_header dh,
                 document_master dm,
                 document_detail dd,
                 depot d,
                 principal_store_master psm,
                 principal_product pp,
                 principal pr,
                 status  s,
                 transporter tp,
                 users u
           WHERE     dh.tripsheet_number = '". mysqli_real_escape_string($this->dbConn->connection, $prO->postFindnumber)."'
           AND       dm.depot_uid        = '". mysqli_real_escape_string($this->dbConn->connection, $prO->depotUId) ."'
           AND       dm.uid              = dh.document_master_uid
           AND       dm.uid         = dd.document_master_uid
           AND       dm.depot_uid   = d.uid
           AND       psm.uid        = dh.principal_store_uid
           AND   dd.product_uid = pp.uid
           AND   pr.uid         = dm.principal_uid
           AND   s.uid          = dh.document_status_uid
           AND   pp.uid         = dd.product_uid
           AND   tp.uid         = dh.trip_transporter_uid
           AND   u.uid          = tripsheet_created_by";

    $rs = $this->dbConn->dbGetAll($sql);

    return $rs;

  } 


//***************************************************************************************************************************************************************************
  public function getDocumentsForPicking($principalId, $depotId, $area) {

    $sql = "SELECT  d.name As 'Depot',
                    dm.uid AS 'docuid',
                    wa.uid AS 'wa.uid', 
                    wa.wh_area AS 'wa_name', 
                    dm.document_number,
                    dh.order_date, 
                    dh.due_delivery_date,
                    psm.deliver_name, 
                    dh.cases, 
                    dh.document_status_uid,
                                        dm.document_type_uid
            FROM        document_master dm 
            INNER JOIN  document_header dh ON dm.uid = dh.document_master_uid 
            INNER JOIN  principal_store_master psm ON dh.principal_store_uid = psm.uid
            INNER JOIN  depot d ON d.uid= dm.depot_uid
            INNER JOIN  warehouse_store_master wsm ON wsm.link = psm.warehouse_link
            INNER JOIN  warehouse_area wa ON wsm.delivery_area = wa.uid
            WHERE       dm.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId)."
            AND         dh.document_status_uid in (74,75)
            AND         dm.document_type_uid in (1,6,13)
            AND         wa.uid = ". mysqli_real_escape_string($this->dbConn->connection, $area). "
            ORDER BY wa.wh_area;";

            $ts = $this->dbConn->dbGetAll($sql);

            return $ts;

  }
//***************************************************************************************************************************************************************************


  public function getDocumentPickingDetail($prO) {

    $sql = "select pp.product_code AS 'Product Code',
                   pp.product_description as 'Product',
                   sum(dd.ordered_qty) AS 'Ordered_Quantity',
                   pp.weight as 'Weight'
            from document_master dm, 
                 document_header dh,
	               document_detail dd, 
	               principal_product pp
	          where dm.uid = dh.document_master_uid
            and   dm.uid = dd.document_master_uid
            and   dd.product_uid = pp.uid
            and   dh.pick_list_number = '". mysqli_real_escape_string($this->dbConn->connection, $prO->postFindnumber)."'
            group by pp.product_code ";

    $ts = $this->dbConn->dbGetAll($sql);

    return $ts;

  }
// *********************************************************************
// *********************************************************************

  public function getDocumentPickingHeader($prO) {

    $sql = "select dm.uid,
                   depot.name As 'Depot',
                   area.description,
                   dm.document_number,
                   psm.deliver_name,
                   dh.cases, 
                   dh.pick_list_number,
                   o.delivery_instructions,
                   now() as 'DT'
		        from document_master dm, 
                 document_header dh,
                 orders o,
	               principal_store_master psm
	          left join area on area.uid = psm.area_uid,
	          depot  
            where dm.uid = dh.document_master_uid
            and o.order_sequence_no = dm.order_sequence_no
            and   dh.principal_store_uid = psm.uid
            and   depot.uid= dm.depot_uid
            and   dh.pick_list_number = '". mysqli_real_escape_string($this->dbConn->connection, $prO->postFindnumber)."'";

    $ts = $this->dbConn->dbGetAll($sql);

    return $ts;

  }
// *********************************************************************



  public function getDocumentImage($dmUId, $imageType) {

    $sql = "SELECT *
            FROM   document_image
            WHERE  document_master_uid = {$dmUId}
            AND    image_type = '{$imageType}'";

    $ts = $this->dbConn->dbGetAll($sql);

    return $ts;

  }

   public function getInvoicesbyTripsheetNumber($prO) {

    $sql = "SELECT dm.uid as 'dm_uid',
                   pr.name as 'Principal',
                   pr.short_name,
                   dm.principal_uid as 'PrincipalID',
                   substr(dm.document_number,3,6) as 'Docno',
                   TRIM(LEADING '0' FROM dm.document_number ) as 'lDocno',
                   psm.deliver_name as 'Store',
                   sum(dd.document_qty) as 'Cases',
                   sum(dd.document_qty * pp.weight) as 'Weight',
                   s.description as 'Dstatus',
                   dh.tripsheet_number AS 'TripNo',
                   dh.tripsheet_date   AS 'Date',
                   tp.name AS 'Transporter_Name',
                   tp.address1 AS 'Transporter_Add1',
                   tp.address2 AS 'Transporter_Add2',
                   tp.address3 AS 'Transporter_Add2',
                   tp.email    AS 'Transporter_email',
                   d.Name      AS 'Depot',
                   d.uid       as 'Depot_Uid',
                   d.depot_email_list AS 'Depot_email',
                   u.full_name AS 'User',
                   dh.customer_order_number AS 'po'
            FROM document_header dh,
                 document_master dm,
	               document_detail dd,
	               depot d,
	               principal_store_master psm,
	               principal_product pp,
	               principal pr,
	               status  s,
	               transporter tp,
	               users u
           WHERE  dh.tripsheet_number = '". mysqli_real_escape_string($this->dbConn->connection, $prO->postFindnumber)."'
           AND    dm.depot_uid  = '". mysqli_real_escape_string($this->dbConn->connection, $prO->depotUId) ."'
           AND   dm.uid         = dh.document_master_uid
           AND   dm.uid         = dd.document_master_uid
           AND   dm.depot_uid   = d.uid
           AND   psm.uid        = dh.principal_store_uid
           AND   dd.product_uid = pp.uid
           AND   pr.uid         = dm.principal_uid
           AND   s.uid          = dh.document_status_uid
           AND   pp.uid         = dd.product_uid
           AND   tp.uid         = dh.trip_transporter_uid
           AND   u.uid          = tripsheet_created_by
           GROUP BY pr.name, dm.document_number";

    $tsDT = $this->dbConn->dbGetAll($sql);

    return $tsDT;
   }
  public function principalShortName($psmId) {

    $sql = "select psm.short_name
            from .principal_store_master psm
            where psm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $psmId) ;

    $sn = $this->dbConn->dbGetAll($sql);

    return $sn;

  }

  public function getUserEmail($userId) {

    $sql = "SELECT u.user_email as 'EmailAddress'
            FROM users u
            WHERE u.uid = $userId";

    $ue = $this->dbConn->dbGetAll($sql);

    return $ue;

  }
  
   public function  getProductDetail($pline)  {

    $sql = "SELECT pp.product_code, pp.product_description
            FROM principal_product pp
            WHERE pp.uid = " . $pline ;

    $pdd = $this->dbConn->dbGetAll($sql);

    return $pdd;

  }
 
  public function getCustomerEmail($psmId) {
  	
  	$sffsql = "select sff.uid
               from   principal_store_master psm
               left join .special_field_fields sff on sff.principal_uid = psm.principal_uid and upper(trim(sff.name)) like '%EMAIL%'
               where psm.uid = ". mysqli_real_escape_string($this->dbConn->connection, $psmId) ;
               
    $sff = $this->dbConn->dbGetAll($sffsql);
    
    $sflist = '';
    
    foreach($sff as $sf) {
   	
    	if($sflist <> '') {
    		$sflist = $sflist . ',';
    	}
    	$sflist = $sflist . $sf['uid'];  
    }

    $sql = "SELECT psm.email_add as 'EmailAddress',
                   sfd.value as 'NextEmailAdress'
            FROM principal_store_master psm
            LEFT JOIN special_field_details sfd on sfd.field_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $sflist) . " ) and sfd.entity_uid = psm.uid
            WHERE psm.uid = ". mysqli_real_escape_string($this->dbConn->connection, $psmId) ;

    $ue = $this->dbConn->dbGetAll($sql);

    return $ue;

  }
  public function getDocumentTypeSubject($dtId) {

    $sql = "select dt.description AS 'DocumentDiscription'
            from document_type dt
            where dt.uid = $dtId";

    $dts = $this->dbConn->dbGetAll($sql);

    return $dts;
  }
  public function getWayBillsToPrint($principalId,$postFROMDATE,$postTODATE,$postWBSTATUS) {
  	
  	if ($postWBSTATUS==1) {
  		$dhwb = "dh.waybill_number is null";
  	} else {
  	  $dhwb = "dh.waybill_number is not null";
    }	
    $sql = "select dm.uid,
                   dm.document_number, 
                   dh.invoice_date, 
                   psm.bill_name, 
                   psm.bill_add1, 
                   psm.bill_add2, 
                   psm.bill_add3,
                   p.name as 'Principal',
                   p.postal_add1,
                   p.postal_add2,
                   p.postal_add3, 
                   dh.waybill_number, 
                   o.delivery_instructions, 
                   o.order_number 
           from document_master dm, 
                orders o, 
                principal p,
                document_header dh, 
                principal_store_master psm 
           where dm.uid = dh.document_master_uid 
           and dm.order_sequence_no = o.order_sequence_no 
           and dh.principal_store_uid = psm.uid
           and dm.principal_uid = p.uid 
           and dm.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
           and dm.processed_date between '".mysqli_real_escape_string($this->dbConn->connection, $postFROMDATE)."' and '".mysqli_real_escape_string($this->dbConn->connection, $postTODATE). "'
           and o.delivery_instructions <> ''
           and   " . mysqli_real_escape_string($this->dbConn->connection, $dhwb) . ";";
           
    $mfTS = $this->dbConn->dbGetAll($sql);

    return $mfTS;
  }
  public function getWayBillsToPrintUseDocument($prO) {
  	
    $sql = "select dm.document_number, 
                   dh.invoice_date, 
                   psm.bill_name, 
                   psm.bill_add1, 
                   psm.bill_add2, 
                   psm.bill_add3,
                   p.name as 'Principal',
                   p.postal_add1,
                   p.postal_add2,
                   p.postal_add3, 
                   dh.waybill_number, 
                   o.delivery_instructions, 
                   o.order_number 
           from document_master dm, 
                orders o, 
                principal p,
                document_header dh, 
                principal_store_master psm 
           where dm.uid = dh.document_master_uid 
           and dm.order_sequence_no = o.order_sequence_no 
           and dh.principal_store_uid = psm.uid
           and dm.principal_uid = p.uid 
           and dm.uid like '%" . trim(mysqli_real_escape_string($this->dbConn->connection, $prO->postFindnumber)) . "%';";
                      
    $mfWB = $this->dbConn->dbGetAll($sql);

    return $mfWB;
  }
  public function getDocumentDetailsToUpdate($principalId, $docNo) {
  	
    $sql = "select dm.document_number,
                   dm.order_sequence_no,
                   psm.deliver_name, 
                   o.delivery_instructions,
                   dh.customer_order_number,
                   dm.uid,
                   dh.document_status_uid,
                   s.description as 'Status',
                   dm.depot_uid,
                   d.name as 'Depot',
                   psm.uid as 'StoreUid' 
           from document_master dm, 
                orders o, 
                principal p,
                document_header dh, 
                principal_store_master psm, 
                depot d,
                `status` s
           where dm.uid = dh.document_master_uid 
           and dm.order_sequence_no = o.order_sequence_no 
           and dh.principal_store_uid = psm.uid
           and dm.principal_uid = p.uid
           and dm.depot_uid = d.uid
           and dh.document_status_uid = s.uid
           and dm.document_type_uid in (1,6,13,27,2,32,4)
           and dm.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . " 
           and dm.document_number like '%" . trim(mysqli_real_escape_string($this->dbConn->connection, $docNo)) . "%';";
        
    $mfDDU = $this->dbConn->dbGetAll($sql);

    return $mfDDU;
  }

  public function getFreeStockInvoices($principalId,$postFROMDATE,$postTODATE,$chlist,$prdlist,$postffactor) {
  	
    $sql = "select psm.uid AS 'Store_Uid',  
                   psm.deliver_name AS 'Store',
		               pp.product_code,
		               pp.product_description, 
		               sum(dd.document_qty) AS 'Quantity', 
                   IF( '" .mysqli_real_escape_string($this->dbConn->connection, $postffactor). "' between 1 and 15 ,floor(sum(dd.document_qty)/".mysqli_real_escape_string($this->dbConn->connection, $postffactor)."), '0') AS 'Free_Stock',       
                   (select max(dh.order_date)
                    from   document_master dm, 
			                     document_header dh
                           where dm.uid = dh.document_master_uid
                           and   dm.principal_uid in ( '" .mysqli_real_escape_string($this->dbConn->connection, $principalId) ."')
                           and   dm.document_type_uid = 13
                           and   dh.order_date between  '" .mysqli_real_escape_string($this->dbConn->connection, $postFROMDATE)."' and  '" .mysqli_real_escape_string($this->dbConn->connection, $postTODATE)."'
                           and   dh.principal_store_uid = psm.uid
                           group by dh.principal_store_uid) AS 'Last_Order'       
           from document_master dm,
                document_header dh, 
	              document_detail dd, 
	               principal_store_master psm, 
	               principal_product pp
            where dm.uid = dh.document_master_uid 
            and dm.uid = dd.document_master_uid 
            and dh.principal_store_uid = psm.uid 
            and dd.product_uid = pp.uid
            and dm.document_type_uid in (1) 
            and dh.document_status_uid in (76,77,78) 
            and   dm.principal_uid in ( '" .mysqli_real_escape_string($this->dbConn->connection, $principalId) ."')
            and   dd.product_uid in (" .mysqli_real_escape_string($this->dbConn->connection, $prdlist) .")
            and   dh.invoice_date between  '" .mysqli_real_escape_string($this->dbConn->connection, $postFROMDATE)."' and  '" .mysqli_real_escape_string($this->dbConn->connection, $postTODATE)."'
            group by dh.principal_store_uid, dd.product_uid;";
        
    $mfFS = $this->dbConn->dbGetAll($sql);

    return $mfFS;
  }

  public function getPrincipalWarehouses($principalId, $userId) {

    $sql = "select d.uid, 
                   d.name
            from .user_principal_depot upd, depot d
            where upd.depot_id = d.uid
            and   upd.principal_id = " . $principalId . "
            and   upd.user_id = " . $userId ;

    $whlist = $this->dbConn->dbGetAll($sql);

    return $whlist;

  }

  public function getInvoicesToReceipt($principalId, $depotid, $start, $end) {

    $sql = "select dm.uid AS 'documentuid', 
                   dm.depot_uid,
                   d.name AS 'depot',
                   dm.document_number,
                   dh.invoice_number,
                   dh.invoice_date, 
                   psm.deliver_name, 
                   dh.delivery_date,
                   dh.cases,
                   dh.invoice_total
            from document_master dm,
                 document_header dh,
                 depot d, 
	               principal_store_master psm
            where dm.uid = dh.document_master_uid
            and   dm.depot_uid = d.uid
            and   dh.principal_store_uid = psm.uid
            and   dm.principal_uid = " . $principalId . "
						and   dh.document_status_uid in (77,78)
            and   dm.document_type_uid = 1
            and   dm.pod_received is null
            and   dm.depot_uid = " . $depotid . "
            and   dh.invoice_date between ' " . $start  . "' and ' " . $end  . "'
            order by dh.invoice_number";
            
    $invlist = $this->dbConn->dbGetAll($sql);

    return $invlist;

  }

  public function getCurrentCustomers($principalId) {

    $sql = "select distinct(psm.uid), 
                   psm.deliver_name
            from document_master dm,
                 document_header dh,
                 principal_store_master psm
            where dm.uid = dh.document_master_uid
            and   dh.principal_store_uid = psm.uid
            and dm.principal_uid = " . $principalId . "
            and   dh.invoice_date >= DATE_SUB(CURDATE(),INTERVAL 180 DAY)
            order by psm.deliver_name";
            
    $custlist = $this->dbConn->dbGetAll($sql);

    return $custlist;

  }

  public function getCustomerRecordsDetail($principalId, $store, $start, $end) {

    $sql = "select dm.uid as 'duid',
                   dm.document_number, 
                   dh.invoice_date,
                   psm.uid as 'psmuid',
                   psm.deliver_name,
                   psm.deliver_add1,
                   psm.deliver_add2,
                   psm.deliver_add3, 
                   s.description,
                   pp.product_code, 
                   pp.product_description, 
                   dd.document_qty, 
                   dd.net_price,
                   dd.extended_price,
                   dd.vat_amount,
                   dd.total,
                   dh.cases,
                   dh.exclusive_total,
                   dh.vat_total,
                   dh.invoice_total,
                   if(pdt.description is null, dt.description, pdt.description) as 'DocType',
                   cb.month_end_date  AS 'med',
                   cb.total_due as 'td',
                   cb.current as 'curr',
                   cb.30days as '30d',
                   cb.60days as '60d',
                   cb.90days as '90d',
                   cb.120days as '120d'  
            from document_master dm
            left join principal_document_type pdt on pdt.principal_uid = dm.principal_uid and pdt.document_type_uid =dm.document_type_uid,
                 document_header dh,
                 document_detail dd,
                 principal_product pp,
                 `status` s,
                 document_type dt,
                 principal_store_master psm
            left join customer_balance cb on cb.customer_uid = psm.uid and cb.month_end_date = '2018-04-30'
where dm.uid = dh.document_master_uid 
and   dm.uid = dd.document_master_uid 
and   dh.principal_store_uid = psm.uid
and   dd.product_uid = pp.uid
and   dh.document_status_uid = s.uid
and   dm.document_type_uid = dt.uid 
and   dm.principal_uid = " .$principalId. "
and   dh.document_status_uid in (81,47,76,77,78)
and   dh.principal_store_uid in (" .$store. ") 
and   dh.invoice_date between '" .$start. "' and '" .$end. "'
order by dh.invoice_date DESC ";

$custInvlist = $this->dbConn->dbGetAll($sql);

    return $custInvlist;

  }

  public function getDocumentPaymentDetails($prO) {

    $sql = "select dm.uid,
                   dm.principal_uid,
                   p.name as 'principal_name',
                   p.physical_add1 as 'prin_ph_add1',
                   p.physical_add2 as 'prin_ph_add2',
                   p.physical_add3 as 'prin_ph_add3',
                   p.postal_add1 as 'prin_add1',
                   p.postal_add2 as 'prin_add2',
                   p.postal_add4 as 'prin_add3',
                   p.email_add as 'p_email',
                   p.office_tel as 'office_tel',
                   dm.document_number, 
                   dh.due_delivery_date, 
                   dh.invoice_total, 
                   pt.pay_type, 
                   dh.payment_amount, 
                   psm.deliver_name, 
                   psm.deliver_add1, 
                   psm.deliver_add2, 
                   psm.deliver_add3 
            from document_master dm, 
                 document_header dh, 
                 principal_store_master psm,
                 payment_type pt,
                 principal p 
            where dm.uid = dh.document_master_uid 
            and dh.principal_store_uid = psm.uid 
            and dh.payment_type = pt.uid 
            and p.uid = dm.principal_uid
            and dm.uid = " .  trim(mysqli_real_escape_string($this->dbConn->connection, $prO->postFindnumber)) . ";";

    $custPayment = $this->dbConn->dbGetAll($sql);

    return $custPayment;

  }
  
  public function getInvoicesNotOnTripsheet($prinUid, $docno ) {
 	  
      $sql = "select dm.uid as 'dmUid',
                   dm.depot_uid, 
                   dm.document_number, 
                   dh.tripsheet_number, 
                   dh.tripsheet_date,
                   dh.document_status_uid,
                   s.description as 'document_status'
            from .document_master dm,
                  document_header dh,
                  `status` s
            where dm.uid = dh.document_master_uid
            and   dh.document_status_uid = s.uid
            and   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinUid) . "
            and   dm.document_type_uid = 1
            and   dm.document_number = '" . str_pad(mysqli_real_escape_string($this->dbConn->connection, $docno) ,8,"0",STR_PAD_LEFT) . "' 
            order by dm.document_number";

       $docTripsheet = $this->dbConn->dbGetAll($sql);

      return $docTripsheet;
  
  } 
  
  public function getTripsheetNumbers($prinUid, $tsno ) {
 	  
      $sql = "select distinct(dh.tripsheet_number), 
                     dm.depot_uid, 
                     dh.tripsheet_date,
                     dh.trip_transporter_uid, 
                     t.name As 'transporter'
              from .document_master dm, .document_header dh, .transporter t
              where dm.uid = dh.document_master_uid
              and   dh.trip_transporter_uid = t.uid
              and   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinUid) . "
              and   dh.tripsheet_date > curdate() - interval 200 day
              and   dh.tripsheet_number = ". mysqli_real_escape_string($this->dbConn->connection, $tsno) ."; " ;
            
      $numTripsheet = $this->dbConn->dbGetAll($sql);

      return $numTripsheet;
  
  } 

  public function checkForExistingSo($psmuid, $mth) {
 	  
      $sql = "select psm.deliver_name, 
                     so.order_required_month,
                     if (so.order_required_month = 1,'January',
                     if (so.order_required_month = 2,'February',
                     if (so.order_required_month = 3,'March',
                     if (so.order_required_month = 4,'April', 
                     if (so.order_required_month = 5,'May',
                     if (so.order_required_month = 6,'June',
                     if (so.order_required_month = 7,'July',
                     if (so.order_required_month = 8,'August',
                     if (so.order_required_month = 9,'September',
                     if (so.order_required_month = 10,'October',
                     if (so.order_required_month = 11,'November',
                     if (so.order_required_month = 12,'December','Unknown')))))))))))) as 'somonth'
              from  standing_orders so, 
                    principal_store_master psm
              where so.principal_store_uid = psm.uid
              and   so.principal_store_uid =  " . mysqli_real_escape_string($this->dbConn->connection, $psmuid) . "
              and   so.order_required_month = " . mysqli_real_escape_string($this->dbConn->connection, $mth) . "; " ;
            
      $mfCSD = $this->dbConn->dbGetAll($sql);

      return $mfCSD;
  
  } 

  public function getImageFileName($psmuid, $docno) {
 	  
      $sql = "select dh.image_file, dt.image_file as 'trip_image'
              from      document_master dm, 
                        document_header dh
              left join document_tripsheet dt on dt.document_master_uid = dh.document_master_uid
              where dm.uid = dh.document_master_uid
              and   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $psmuid) . "
              and   dm.document_number in (" . mysqli_real_escape_string($this->dbConn->connection, $docno) . "); " ;
  
      $mfCSD = $this->dbConn->dbGetAll($sql);

      return $mfCSD;
  
  } 

  public function getSalesByProduct($principalId, $StartDate, $EndDate, $ChainList, $DocStatus, $ProdList) {
 	  
      $sql = "select  dh.principal_store_uid as 'storeUid',
                      psm.deliver_name,
                      pp.product_code,
                      pp.product_description,
                      sum(d.document_qty) as 'quantity'
              from  document_master dm, 
                    document_header dh,
                    document_detail d
              left join principal_product pp on d.product_uid = pp.uid,
                    principal_store_master psm
              where dm.uid = dh.document_master_uid
              and   dm.uid = d.document_master_uid
              and   dh.principal_store_uid = psm.uid
              and   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
              and   dh.invoice_date between '" . mysqli_real_escape_string($this->dbConn->connection, $StartDate) . "' and '" . mysqli_real_escape_string($this->dbConn->connection, $EndDate) . "'
              and   dh.document_status_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $DocStatus) . ")
              and   psm.principal_chain_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $ChainList) . ")
              and   d.product_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $ProdList) . ")
              group by dh.principal_store_uid, d.product_uid; " ;

      $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 
	public function getBillingPrincipalArray() {
      $sql="select distinct(p.uid), p.name
            from .principal_charge_rate pr, .principal p
            where pr.principal_uid = p.uid
            and   pr.`status` = 'A'
            order by p.name";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[$row['uid']] = $row;
		}

		return $arr;
	}
  public function getDocumentCompleteToUpdate($docUid) {
  	
    $sql = "select dm.document_number,
                   psm.deliver_name,
                   dh.customer_order_number,
                   pp.product_code,
                   pp.product_description,
                   dd.ordered_qty,
                   dd.net_price,
                   dd.extended_price,
                   dd.line_no,
                   pp.allow_decimal
           from document_master dm,
                document_header dh,
                document_detail dd,
                principal_store_master psm,
                principal_product pp
           where dm.uid = dh.document_master_uid
           and   dm.uid = dd.document_master_uid
           and   dh.principal_store_uid = psm.uid
           and   dd.product_uid = pp.uid
           and dm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . ";";
        
    $mfDDU = $this->dbConn->dbGetAll($sql);

    return $mfDDU;
  }	

  public function getPrincipalUsers($prinUid) {
  	
    $sql = "select distinct( u.uid), u.full_name
            from .user_principal_depot upd, .users u 
            where u.uid = upd.user_id 
            and   upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $prinUid) . " 
            and   u.organisation_name <> 'STAFF' 
            and   u.deleted <> 1 
            order by u.full_name ;";
            
            $mfPUL = $this->dbConn->dbGetAll($sql);

    return $mfPUL;
  }
  
  public function getPrincipalUsersHoney($prinUid) {
  	
    $sql = "select distinct( u.uid), u.full_name
            from .user_principal_depot upd, .users u 
            where u.uid = upd.user_id 
            and   upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $prinUid) . " 
            and   u.organisation_name <> 'STAFF' 
            and   u.deleted <> 1 
            and   u.active = 'Y'
            order by u.full_name ;";
            
            $mfPUL = $this->dbConn->dbGetAll($sql);

    return $mfPUL;
  }	  	

  public function getUsertransactionType($prinUid) {
  	
    $sql = "select dt.uid, dt.description
             from .document_type dt
             where dt.role_id in (select distinct(ur.role_id)
                                  from .user_role ur, user_principal_depot upd, users u
                                  where ur.user_id = upd.user_id
                                  and   u.uid = upd.user_id
                                  and   upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $prinUid) . "
                                  and   u.organisation_name <> 'STAFF' 
                                  and   u.deleted <> 1)
                                  and   dt.show_on_capture = 'Y';";
            
            $mfTTP = $this->dbConn->dbGetAll($sql);

    return $mfTTP;
  }	

  public function getUnauthorisedTransactions($prinUid, $userId, $documentType) {
  	
    $sql = "select dm.uid,
                   dm.document_number as 'document_number', 
                   dh.invoice_date as 'Date',
                   dm.document_type_uid,
                   dt.description as 'DocType',  
                   dh.principal_store_uid,
                   psm.deliver_name, 
                   dh.document_status_uid,
                   s.description as 'Status',
                   u.full_name,
                   dh.cases,
                   dh.invoice_total as 'Total' 
            from   document_master dm, 
                   document_header dh,
                   principal_store_master psm, 
                   document_type dt,
                   `status` s,
                   users u
            where dm.uid = dh.document_master_uid
            and   dh.principal_store_uid = psm.uid
            and   dm.document_type_uid = dt.uid
            and   dh.document_status_uid = s.uid
            and   dh.captured_by   = u.uid
            and   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinUid) . "
            and   dh.captured_by   = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . "
            and   dm.document_type_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $documentType) . ")
            and   dm.authorised_by_uid is null
            and   dm.processed_date > '2019-01-01'
            order by dt.description, dh.invoice_date ;";
            
            $mfTTX = $this->dbConn->dbGetAll($sql);

    return $mfTTX;
  }	
  public function getUnauthorisedPayments($prinUid, $userId, $documentType) {
  	
    $sql = "select ph.uid,
                   ph.payment_number as 'document_number', 
                   ph.payment_date as 'Date', "
                   . DT_PAYMENT . " as 'document_type_uid',
                   'Payment' as 'DocType',  
                   if(ph.payment_by = 1,psm.principal_uid,pch.uid) as 'principal_store_uid',
                   if(ph.payment_by = 1,psm.deliver_name,pch.description) as 'deliver_name', "
                   . DST_PROCESSED . " as 'document_status_uid',
                   'Captured' as 'Status',
                   u.full_name,
                   '1' as 'cases',
                   ph.amount as 'Total'
            from      payment_header ph
            left join principal_store_master psm on ph.principal_store_uid = psm.uid
            left join principal_chain_master pch on ph.principal_store_uid = pch.uid,
                      payment_detail pd,
                      users u
            where ph.uid = pd.payment_header_uid
            and   ph.principal_store_uid = psm.uid
            and   ph.captured_by = u.uid
            and   ph.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinUid) . "
            and   ph.captured_by   = " . mysqli_real_escape_string($this->dbConn->connection, $userId)  . "
            and   ph.authorised_by_uid is null
            and   ph.payment_date > '2019-01-01';";
            
            $mfTTX = $this->dbConn->dbGetAll($sql);

    return $mfTTX;
  }	
// ***********************************************************************************************************************************************
  public function getDocumentHeaderToUpdate($principalId, $docNo, $docType) {
  	
    $sql = "select dm.uid,
                   dm.document_number, 
                   dh.overide_rep_code_uid,
                   psr.uid as 'psruid', 
                   psr.first_name, 
                   psr.surname, 
                   psm.deliver_name
            from       document_master dm, 
                       document_header dh
            left join  principal_sales_representative psr on dh.overide_rep_code_uid = psr.uid 
                                                          and psr.`status` = '" . FLAG_STATUS_ACTIVE . "' 
                                                          and psr.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $principalId)      . " ,
                                                          principal_store_master psm
            where dm.uid = dh.document_master_uid
            and   dh.principal_store_uid = psm.uid
            and   dm.principal_uid     = "  . mysqli_real_escape_string($this->dbConn->connection, $principalId)      . " 
            and   dm.document_number   like  '%" . trim(mysqli_real_escape_string($this->dbConn->connection, $docNo)) . "%' 
            and   dm.document_type_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $docType)          . ";";

    $mfDDU = $this->dbConn->dbGetAll($sql);
    
    if (sizeof($mfDDU)==0) {
    
         $sql = "select dm.uid,
                        dm.alternate_document_number as 'document_number', 
                        dh.overide_rep_code_uid, 
                        psr.uid as 'psruid',
                        psr.first_name, 
                        psr.surname, 
                        psm.deliver_name
                 from   document_master dm, 
                        document_header dh
                 left join  principal_sales_representative psr on dh.overide_rep_code_uid = psr.uid 
                                                               and psr.`status` = '" . FLAG_STATUS_ACTIVE . "' 
                                                               and psr.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $principalId)      . " ,
                                                               principal_store_master psm
                 where dm.uid = dh.document_master_uid
                 and   dh.principal_store_uid = psm.uid
                 and   dm.principal_uid     = "  . mysqli_real_escape_string($this->dbConn->connection, $principalId)      . " 
                 and   dm.alternate_document_number   like  '%" . trim(mysqli_real_escape_string($this->dbConn->connection, $docNo)) . "%' 
                 and   dm.document_type_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $docType)          . ";";
                 
         $mfDDU = $this->dbConn->dbGetAll($sql);
    }
    return $mfDDU;
  }
// ***********************************************************************************************************************************************
  public function getUpliftDetailsToUpdate($principalId, $docNo) {
  	
    $sql = "select dm.uid as 'masterUid', 
                   dd.uid as 'detailUid',
                   dm.document_number,
                   if(trim(o.delivery_instructions) = '', p.name, o.delivery_instructions) as 'Principal',
                   dm.depot_uid, 
                   d.name as 'warehouse',
                   dh.document_status_uid,
                   dh.order_date,
                   dh.cases,
                   psm.deliver_name,
                   psm.uid as 'psmuid',
                   psm.branch_code,
                   dd.product_uid,
                   pp.product_code,
                   pp.alt_code,
                   pp.product_description,
                   dd.ordered_qty,
                   dd.additional_type,
                   dd.net_price,
                   pp.unit_value,
                   p.name as 'Principal Name',
                   if(psm.alt_principal_chain_uid like '%MAKRO%', pp.vendor_code1, pp.vendor_code2) as 'ArticleNo',
                   if(length(ppdg.outercasing_gtin) > 5,ppdg.outercasing_gtin, if(length(ppdg.sku_gtin) > 5,ppdg.sku_gtin, pp.product_code)) as 'ProdNo'
            from document_master dm,
                 orders o, 
                 document_header dh, 
                 document_detail dd, 
                 principal_store_master psm,
                 principal p, 
                 depot d,
                 principal_product pp
            LEFT JOIN principal_product_depot_gtin ppdg on ppdg.principal_product_uid = pp.uid     
            where dm.uid = dh.document_master_uid
            AND   o.order_sequence_no = dm.order_sequence_no
            and   dm.uid = dd.document_master_uid
            and   dh.principal_store_uid = psm.uid
            and   dd.product_uid = pp.uid
            and   p.uid = dm.principal_uid
            and   dm.depot_uid = d.uid
           and dm.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . " 
           and dm.document_number like '%" . trim(mysqli_real_escape_string($this->dbConn->connection, $docNo)) . "%';";         
    $mfDDU = $this->dbConn->dbGetAll($sql);
    
    //jb
    //echo $sql;

    return $mfDDU;
  }
// ***********************************************************************************************************************************************
  public function getUpliftDocumentNumber($docUid) {
  	   
  	   $sql = "SELECT dm.document_number 
  	           FROM   document_master dm 
  	           where  dm.uid = " .mysqli_real_escape_string($this->dbConn->connection, $docUid) . " ;";

       $docNo = $this->dbConn->dbGetAll($sql);

       return $docNo;  	
  }	
// ***********************************************************************************************************************************************
  public function getPackagingBoxes($docUid) {
  	   
  	   $sql = "SELECT dd.product_uid, 
  	                  pp.product_code, 
  	                  pp.product_description, 
  	                  pp.old_code, 
  	                  sum(dd.document_qty) as 'BoxQty'
               FROM .document_detail dd
               LEFT JOIN .principal_product pp ON pp.uid = dd.product_uid
               WHERE dd.document_master_uid = " .mysqli_real_escape_string($this->dbConn->connection, $docUid) . "
               AND   pp.old_code IS NOT NULL 
               GROUP BY pp.old_code ;";

       $trigBox = $this->dbConn->dbGetAll($sql);

       return $trigBox;  	
  }	
// ***********************************************************************************************************************************************

  
}  

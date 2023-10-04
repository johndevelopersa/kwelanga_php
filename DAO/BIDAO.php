<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class BIDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

	public function getNotificationTypes($arrayIndex) {
		$sql="select *
			  from   notification
			  order  by system_category";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ($arrayIndex=="") $arr[] = $row;
				else $arr[$row[$arrayIndex]] = $row;
			}
		}

		return $arr;
	}

	public function getNotificationItem($UId) {

		$sql="select *
			  from   notification
			  where  uid = '".mysqli_real_escape_string($this->dbConn->connection, $UId)."'";

		return $this->dbConn->dbGetAll($sql);
	}

	public function getNotificationRecipients($principalUId, $notificationUId) {

		$sql="select nr.*, n.description, n.message, n.system_category
			  from   notification_recipients nr
		           INNER JOIN principal p on p.uid = nr.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."',
               notification n
			  where  nr.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
			  and    nr.notification_uid = '".mysqli_real_escape_string($this->dbConn->connection, $notificationUId)."'
			  and    nr.notification_uid = n.uid";

		return $this->dbConn->dbGetAll($sql);
	}
// ******************************************************************************************************************
	public function getWarehouseNotificationRecipients($principalUId, $notificationUId) {

		$sql="SELECT distinct(dm.depot_uid), 
                dm.principal_uid,
					 pc.email_addr, 
					 dm.document_number,
					 psm.deliver_name,
					 se.general_reference_2
FROM document_master dm
INNER JOIN .document_header dh ON dm.uid = dh.document_master_uid
INNER JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
LEFT JOIN principal_contact pc ON pc.principal_uid = dm.principal_uid AND pc.depot_uid = dm.depot_uid,
     smart_event se
WHERE dm.uid = se.data_uid
AND   dm.principal_uid = 354
AND   se.`status` IN ('E')
		
		
		
		select nr.*, n.description, n.message, n.system_category
			  from   notification_recipients nr
		              INNER JOIN principal p on p.uid = nr.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."',
               notification n
			  where  nr.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
			  and    nr.notification_uid = '".mysqli_real_escape_string($this->dbConn->connection, $notificationUId)."'
			  and    nr.notification_uid = n.uid";

		return $this->dbConn->dbGetAll($sql);
	}
	
	
	
	
	

	public function getNotificationRecipientItem($UId) {

		$sql="select a.*, b.description
			  from   notification_recipients a
		              INNER JOIN principal p on p.uid = a.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."',
               notification b
			  where  a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $UId)."'
        and    a.notification_uid = b.uid";

		return $this->dbConn->dbGetAll($sql);
	}

	public function getActiveNotificationRecipients() {

		$sql="select *
			  from   notification_recipients nr
		              INNER JOIN principal p on p.uid = nr.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."'
			  where  service_status = '".FLAG_STATUS_ACTIVE."'";

		return $this->dbConn->dbGetAll($sql);
	}

	public function getBIPriceDealExpiries($userId, $principalUId, $days) {

		$sql="select a.principal_uid, count(*) cnt
			  from   pricing a
      			      INNER JOIN principal p on p.uid = a.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."'
      						left join (select distinct principal_id from user_principal_depot upd where upd.user_id = '{$userId}' and upd.principal_id = '{$principalUId}') upd on a.principal_uid = upd.principal_id
                       		left join principal_store_master b on a.chain_store = b.uid
                        			left join user_principal_store ups on b.uid = ups.principal_store_uid and ups.user_uid = '{$userId}'
      						left join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid={$principalUId})
                        			left join user_principal_chain upc on b.principal_chain_uid = upc.principal_chain_uid and upc.user_uid = '{$userId}'
                        		left join user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid={$principalUId})
			  where  a.principal_uid = '{$principalUId}'
			  and    start_date <= now()
			  and    end_date between curdate() and DATE_ADD(CURDATE(),INTERVAL {$days} DAY)
			  and    (ups.uid is not null or ur.uid is not null)
			  and    (upc.uid is not null or urc.uid is not null)
			  and    upd.principal_id is not null
			  group  by principal_uid";

		return $this->dbConn->dbGetAll($sql);
	}

	// lists are comma separated, can be blank
	public function getBIAvailStockThreshold($userId, $principalUId, $qty, $productList, $depotList) {
		global $ROOT; global $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
		$administrationDAO = new AdministrationDAO($this->dbConn);
    	$hasRole = $administrationDAO->hasRole($userId, $principalUId, ROLE_BYPASS_USER_PRODUCT_RESTRICTION);
    	// lift the user restriction if has role
    	if ($hasRole) $where=""; else $where=" AND user_principal_product.uid is not null ";

		if ($productList!="") $whereP=" and principal_product.uid in ({$productList}) "; else $whereP="";
		if ($depotList!="") $whereD=" and a.depot_id in ({$depotList}) "; else $whereD="";

		$sql="select a.principal_id, group_concat(depot_code) depot_codes, sum(cnt) cnt
			  from (
					select a.principal_id, c.code depot_code, count(*) cnt
					  from   stock a
					      INNER JOIN principal p on p.uid = a.principal_id and p.status = '".FLAG_STATUS_ACTIVE."'
								LEFT JOIN principal_product on a.stock_item = principal_product.product_code and a.principal_id = principal_product.principal_uid
									LEFT JOIN user_principal_product ON principal_product.uid = user_principal_product.principal_product_uid and user_principal_product.user_uid = '{$userId}',
							   user_principal_depot b,
							   depot c
					  where  a.principal_id='{$principalUId}'
					  and    opening < '{$qty}'
					  and    a.principal_id = b.principal_id
					  and    a.depot_id = b.depot_id
		  			  and    b.user_id = '{$userId}'
					  and    b.principal_id = '{$principalUId}'
					  and    a.depot_id = c.uid
					  {$whereP}
					  {$whereD}
					  group  by a.principal_id, c.code
					) a";

		return $this->dbConn->dbGetAll($sql);
	}

	//called from smart events to get documents list from smart event data_uid
	public function getBIDocumentsByUIdList($documentMasterUidList) {

          $sql="select dm.uid, p.uid as principal_uid, p.name principal_name, dm.depot_uid, d.name depot_name, dm.document_number, dm.document_type_uid, dt.description document_type, dm.order_sequence_no,
                                 dh.order_date, dh.delivery_date, dh.customer_order_number, dm.incoming_file, dm.processed_date, dm.processed_time,
                                 dh.data_source, dh.captured_by, dh.document_status_uid, o.client_document_number, s.description as 'status', merged_date, merged_time,
                                 psm.uid psm_uid, psm.deliver_name, psm.deliver_add1, psm.deliver_add2, psm.deliver_add3, psm.bill_name, psm.bill_add1, psm.bill_add2, psm.bill_add3,
                                 psm.old_account
                          from  document_master dm
                                left join orders o on dm.order_sequence_no = o.order_sequence_no, -- at the moment orders origination from clipper wont be in orders
                                document_header dh,
                                principal p,
                                depot d,
                                document_type dt,
                                status s,
                                principal_store_master psm
                          where  dm.uid = dh.document_master_uid
                          and    dm.uid IN ({$documentMasterUidList})
                          and    dm.principal_uid = p.uid
                          and    dm.depot_uid = d.uid
                          and    dm.document_type_uid = dt.uid
                          and    dh.document_status_uid = s.uid
                          and    dh.principal_store_uid = psm.uid";

          return $this->dbConn->dbGetAll($sql);
	}

	public function getBIDeliveryExceptionsByUIdList($documentMasterUidList) {

          $sql="SELECT
                  dm.uid, p.uid as principal_uid, p.name principal_name, dm.depot_uid,
                  d.name depot_name, dm.document_number, dm.document_type_uid,
                  dt.description `document_type`,
                  dh.order_date, dh.requested_delivery_date, dh.due_delivery_date, dh.customer_order_number,
                  dm.incoming_file, dm.processed_date,
                  dm.processed_time, dh.data_source, dh.captured_by,
                  psm.uid psm_uid, psm.deliver_name,
                  cal.*
              from  document_master dm
                        INNER JOIN principal p on p.uid = dm.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."',
                    document_header dh,
                    depot d,
                    document_type dt,
                    principal_store_master psm,
                    depot_delivery_calendar cal
              where  dm.uid = dh.document_master_uid
              and    dm.uid IN ({$documentMasterUidList})
              and    dm.depot_uid = d.uid
              and    dm.document_type_uid = dt.uid
              and    dh.principal_store_uid = psm.uid
              and    cal.depot_uid = dm.depot_uid
              and    cal.`type` = 1
              and    DATE(FROM_UNIXTIME(cal.timestamp)) = dh.due_delivery_date";

          return $this->dbConn->dbGetAll($sql);
	}

  //called from smart events to get documents list from smart event data_uid
  public function getBIDocumentsDetailsByUIdList($documentMasterUIdList) {
          $sql="select dd.document_master_uid dm_uid, dd.uid dd_uid, dd.product_uid, pp.product_code, pp.product_description, dd.ordered_qty, dd.selling_price,
                       dd.discount_value, dd.document_qty, dd.net_price, dd.vat_rate, pp.major_category
                          from  document_detail dd,
                                principal_product pp
                          where  dd.document_master_uid IN ({$documentMasterUIdList})
                          and    dd.product_uid = pp.uid";

          $this->dbConn->dbQuery($sql);

          $arr=array();
          if ($this->dbConn->dbQueryResultRows > 0) {
            while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
              $arr[$row["dm_uid"]][] = $row;  //group by principal and status
            }
          }

          return $arr;
  }



	public function getBIElectronicExceptions($principalUId) {

		// NB: The ohd where clause must be where i have it, and not as part of main where clause as otherwise if it is only product on document, the whole order is excl
		// NB: Dont check the exception_notified flag because that is handled in the processor and some get resent regardless of flag
		$sql="select oh.uid oh_uid, ohd.uid ohd_uid, p.name principal_name, client_document_number, oh.document_type_uid, ifnull(pdt.description,dt.description) document_type, psm.deliver_name,
					 oh.created_date, oh.order_date, processed_date, oh.cancelled_order_notified, oh.status status_hdr, ohd.status status_dtl,
					 oh.status_msg, oh.reference, oh.exception_notified exception_notified_hdr, ohd.exception_notified exception_notified_dtl
			  from   orders_holding oh
						left join document_type dt on oh.document_type_uid = dt.uid
						LEFT Join principal_document_type pdt on dt.uid = pdt.document_type_uid and pdt.principal_uid = '{$principalUId}'
					 	left join orders_holding_detail ohd on oh.uid = ohd.orders_holding_uid and if(ohd.status is null,0,ohd.status)!='".FLAG_STATUS_DELETED."'
						left join principal_store_master psm on oh.principal_store_uid = psm.uid
					 INNER JOIN principal p on p.uid = oh.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."'
			  where  oh.principal_uid = '{$principalUId}'
			  and    (if(oh.status is null,0,oh.status) not in ('".FLAG_ERRORTO_SUCCESS."','".FLAG_STATUS_DELETED."','') or
					  (oh.cancelled_order_notified='".FLAG_STATUS_QUEUED."'))
			  order  by created_date desc, oh.uid";

		return $this->dbConn->dbGetAll($sql);
	}

	public function getBIPriceVariances($principalUId) {

		// NB: The ohd where clause must be where i have it, and not as part of main where clause as otherwise if it is only product on document, the whole order is excl
		// NB: Dont check the exception_notified flag because that is handled in the processor and some get resent regardless of flag
		$sql="select oh.uid oh_uid, ohd.uid ohd_uid, p.name principal_name, client_document_number, oh.document_type_uid, ifnull(pdt.description,dt.description) document_type, psm.deliver_name,
					 oh.created_date, oh.order_date, processed_date, oh.cancelled_order_notified, ohd.price_diff_notified, oh.reference
			  from   orders_holding oh
						left join document_type dt on oh.document_type_uid = dt.uid
						LEFT Join principal_document_type pdt on dt.uid = pdt.document_type_uid and pdt.principal_uid = '{$principalUId}'
					 	left join orders_holding_detail ohd on oh.uid = ohd.orders_holding_uid and if(ohd.status is null,0,ohd.status)!='".FLAG_STATUS_DELETED."'
						left join principal_store_master psm on oh.principal_store_uid = psm.uid
					 INNER JOIN principal p on p.uid = oh.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."'
			  where  oh.principal_uid = '{$principalUId}'
			  and    (ohd.price_diff_notified='".FLAG_STATUS_QUEUED."')
			  and    oh.created_date > curdate() -- interval 3 day -- to protect against accidentally queued or old
			  order  by created_date desc, oh.uid";

		return $this->dbConn->dbGetAll($sql);

	}

	public function getBIEDIFileDefn($principalUId) {
		// NB: The ohd where clause must be where i have it, and not as part of main where clause as otherwise if it is only product on document, the whole order is excl
		// NB: Dont check the exception_notified flag because that is handled in the processor and some get resent regardless of flag
		$sql="select oh.uid, oh.status, oh.created_date, oh.processed_date, oh.incoming_file, oh.online_file_processing_uid, oh.client_document_number, oh.reference, oh.deliver_name
				from orders_holding oh
				        INNER JOIN principal p on p.uid = oh.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."'
				where oh.principal_uid = '{$principalUId}'
				and   edifiledef_notified = '".FLAG_STATUS_QUEUED."'
				order by incoming_file, client_document_number";

		return $this->dbConn->dbGetAll($sql);
	}

	// cant make a generic one where u pass the notificationUId because of order ranking clause below
	// a specific user one overrides a general user one
	public function getNotificationDocketCaptureDuplicationForUser($userId, $principalUId) {
		$sql="select *
				from   notification_recipients
				where  principal_uid = '{$principalUId}'
				and    notification_uid = '".NT_DOCKET_CAPTURE_DUPLICATION."'
				order  by if(user_uid_list is null or user_uid_list='',2,1), if (value='ERROR',1,2)";

		$this->dbConn->dbQuery($sql);

		$obj=array();

		// only return the highest ranking notification, as there is no limit on how many notifications you can capture of the same type
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			if ($row["user_uid_list"]=="") {
				$obj["value"]=$row["value"];
				$obj["delivery_type"]=$row["delivery_type"];
				$obj["user_uid_list"]=$row["user_uid_list"];
				return $obj;
			} else {
				$arr=explode(",",$row["user_uid_list"]);
				if (in_array($userId,$arr)) {
					$obj["value"]=$row["value"];
					$obj["delivery_type"]=$row["delivery_type"];
					$obj["user_uid_list"]=$row["user_uid_list"];
					return $obj;
				}
			}
		}

		return $obj;
	}

	// this notification is at principal level.
	// The 2nd, 3rd etc... params are the filters
	// Pass Principal as blank if you want to retrieve across all principals, which is also why this notification returns array[][] and not array[] like the others
	public function getNotificationDocumentConfirmation($principalUId,$depotUId,$documentTypeUId,$dataSource,$capturedByDescriptor, $documentStatusUid = "") {
		$sql="select nr.*, n.description
				from   notification_recipients nr
				        INNER JOIN principal p on p.uid = nr.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."',
  						notification n
				where  (principal_uid = '{$principalUId}' or '{$principalUId}'='')
				and    notification_uid = '".NT_DOCUMENT_CONFIRMATION."'
				and    nr.notification_uid = n.uid
				and    nr.service_status = '".FLAG_STATUS_ACTIVE."' ";
		$sql .=	" order  by principal_uid, notification_uid";

		$this->dbConn->dbQuery($sql);

		$obj=array();

		// Convert all Notifications found of this type to the defined variables for parameters.
		// There is no limit on how many notifications you can capture of the same type
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$fields=array();

			$fields["depot_list"]=$fields["document_type_list"]=$fields["data_source_list"]=$fields["captured_by_descriptor_list"]="";
			// overwrite with proper value if found
			$fields["depot_list"]=CommonUtils::getParamValuesFromString($row["additional_parameter_string"],"p1",$paramSeparator="&",$paramValueAsignment="=");
			$fields["document_type_list"]=CommonUtils::getParamValuesFromString($row["additional_parameter_string"],"p2",$paramSeparator="&",$paramValueAsignment="=");
			$fields["data_source_list"]=CommonUtils::getParamValuesFromString($row["additional_parameter_string"],"p3",$paramSeparator="&",$paramValueAsignment="=");
			$fields["captured_by_descriptor_list"]=CommonUtils::getParamValuesFromString($row["additional_parameter_string"],"p4",$paramSeparator="&",$paramValueAsignment="=");
      $fields["destination_document_status"]=CommonUtils::getParamValuesFromString($row["additional_parameter_string"],"p5",$paramSeparator="&",$paramValueAsignment="=");
      $fields["adaptor_script"]=CommonUtils::getParamValuesFromString($row["additional_parameter_string"],"p6",$paramSeparator="&",$paramValueAsignment="=");

			$obj[]=array_merge($row,$fields);
		}

		// if specific filter params are supplied, then only return that row or nothing
		if (($depotUId!="") || ($documentTypeUId!="") || ($dataSource!="") || ($capturedByDescriptor!="")) {
			$dObj=array();
			foreach ($obj as $o) {
				$arrD=explode(",",$o["depot_list"]);
				$arrDT=explode(",",$o["document_type_list"]);
				$arrDS=explode(",",$o["data_source_list"]);
				$arrCBD=explode(",",$o["captured_by_descriptor_list"]);
        $arrDDS=explode(",",$o["destination_document_status"]);

        /*
        if ($principalUId==218) {
          echo "x : ".(($o["depot_list"]=="") || ($o["depot_list"]=="*") || (in_array($depotUId,$arrD)))."<br>";
          echo "x : ".(($o["document_type_list"]=="") || ($o["document_type_list"]=="*") || (in_array($documentTypeUId,$arrDT)))."<br>";
          echo "x : ".(($o["data_source_list"]=="") || ($o["data_source_list"]=="*") || (in_array($dataSource,$arrDS)))."<br>";
          echo "x : ".(($o["captured_by_descriptor_list"]=="") || ($o["captured_by_descriptor_list"]=="*") || (in_array($capturedByDescriptor,$arrCBD)))."<br>";
          echo "x : ".(in_array($documentStatusUid,$arrDDS))."<br>";
        }
        */

				if (
					(($o["depot_list"]=="") || ($o["depot_list"]=="*") || (in_array($depotUId,$arrD))) &&
					(($o["document_type_list"]=="") || ($o["document_type_list"]=="*") || (in_array($documentTypeUId,$arrDT))) &&
					(($o["data_source_list"]=="") || ($o["data_source_list"]=="*") || (in_array($dataSource,$arrDS))) &&
					(($o["captured_by_descriptor_list"]=="") || ($o["captured_by_descriptor_list"]=="*") || (in_array($capturedByDescriptor,$arrCBD))) &&
          (
           ($documentStatusUid == "" && $o["destination_document_status"]=="") || //will work for existing confirmations - document creation.
           ($documentStatusUid == "" && (in_array(DST_UNACCEPTED,$arrDDS) || in_array(DST_QUEUED,$arrDDS))) || //new confirmations loaded where status is set. (74 => unaccepted OR 86 => queued for processing)
           ($o["destination_document_status"]=="*") ||
           (($o["destination_document_status"]=="") && (in_array($documentStatusUid,array(DST_UNACCEPTED,DST_QUEUED)))) || //all status
           (in_array($documentStatusUid,$arrDDS))
          )  //will work for other status changes
          ){
              $dObj[]=$o;
         }

			}
			return $dObj;
		}

		return $obj;
	}

	// Pass Principal as blank if you want to retrieve across all principals, which is also why this notification returns array[][] and not array[] like the others
	// MUST be ordered by notification UID, then principal_uid, as the processing func commits after all of same type have been processed !
	public function getNotificationElectronicException($principalUId) {
		$sql="select nr.*, n.description
				from   notification_recipients nr
				          INNER JOIN principal p on p.uid = nr.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."',
  						notification n
				where  (principal_uid = '{$principalUId}' or '{$principalUId}'='')
				and    notification_uid = '".NT_ELECTRONIC_IMPORT_EXCEPTION."'
				and    nr.notification_uid = n.uid
				and    nr.service_status = '".FLAG_STATUS_ACTIVE."'
				order  by notification_uid, principal_uid";

		return $this->dbConn->dbGetAll($sql);
	}

	// Pass Principal as blank if you want to retrieve across all principals, which is also why this notification returns array[][] and not array[] like the others
	// MUST be ordered by notification UID, then principal_uid, as the processing func commits after all of same type have been processed !
	public function getNotificationPriceVariance($principalUId) {
		$sql="select nr.*, n.description
				from   notification_recipients nr
				          INNER JOIN principal p on p.uid = nr.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."',
  						notification n
				where  (principal_uid = '{$principalUId}' or '{$principalUId}'='')
				and    notification_uid = '".NT_EDI_PRICE_VARIANCE."'
				and    nr.notification_uid = n.uid
				and    nr.service_status = '".FLAG_STATUS_ACTIVE."'
				order  by notification_uid, principal_uid";

		return $this->dbConn->dbGetAll($sql);
	}

	// rsArr must be an array generated by a select * on notification recipients
	// criteriaArr is an array of params where first element is p1... etc.
	// params are returned as fields with indexes ["p1"] etc...
	public function getHighestRankedNotificationForUser($userId, $rsArr, $criteriaArr) {
		$obj=array();

		// only notifications applicable for user, and set the specific parameter fields
		foreach ($rsArr as $row) {
			$uArr=explode(",",$row["user_uid_list"]); // users do not have an asterisk for select all
			// only consider the notifications that apply to the user
			if ((in_array($userId,$uArr)) || ($row["user_uid_list"]=="")) {
				$fields=array(); // extra fields added

				$params=explode("&",$row["additional_parameter_string"]);
				// put the params into fields
				foreach ($params as $p) {
					$arr=explode("=",$p);
					if (substr($arr[0],0,1)=="p") $fields[$arr[0]]=$arr[1];
				}
				// now compare param values passed
				$i=1;
				if ($row["user_uid_list"]=="") $ranking=0; else $ranking=1000; // give specified users priority;
				$found=false;
				foreach ($criteriaArr as $c) {
					// treat all of the following conditions as having passed
					if (
						(!isset($fields["p".$i])) ||
						($fields["p".$i]=="*") ||
						($fields["p".$i]=="")
					   ) {
					   	// not specified, so accept it but dont increase the ranking
					   	$ranking+=0;
					   	$found=true;
					   } else if (in_array($c,explode(",",$fields["p".$i]))) {
							$ranking++; // each criteria that matches specifically must increase the ranking
							$found=true;
					   }
					$i++;
				}
				if (($found) && (!isset($obj[$ranking]))) $obj[$ranking]=array_merge($row,$fields);
			} // if in user list
		}
		krsort($obj); // sort in reverse order by key

		// now just pop off and return the top element
		foreach ($obj as $o) {
			return $o;
		}

		return $obj; // incase of empty array
	}

	// cant make a generic one where u pass the notificationUId because of order ranking clause below
	// a specific user one overrides a general user one
	public function getNotificationCreditLimitForUser($userId, $principalUId, $principalStoreUId) {
		$sql="select *
				from   notification_recipients
				where  principal_uid = '{$principalUId}'
				and    notification_uid = '".NT_CREDIT_LIMIT."'
				order  by if(user_uid_list is null or user_uid_list='',2,1),
						  if (value='ERROR',1,2)";

		$this->dbConn->dbQuery($sql);

		$rsArr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$rsArr[]=$row;
		}

		$notification=$this->getHighestRankedNotificationForUser($userId, $rsArr, array($principalStoreUId));

		return $notification;

	}


	// enforceMS ~ when rapidly creating files for same pricipal and user, files need to be created by MS for uniqueness as execution time us under 1 second
	public function getNotificationFilename($notificationId,$userId,$principalUId,$enforceMS=false) {
		if ($enforceMS) {
			$ms=gettimeofday();
			$ms=".".$ms["usec"]; // milliseconds
		} else {
			$ms="";
		}
		$nowCompressed = CommonUtils::getGMTimeCompressed(0);
		$fileName = $nowCompressed.$ms.".n{$notificationId}.{$userId}.{$principalUId}.csv";

		return $fileName;
	}

	// enforceMS ~ when rapidly creating files for same pricipal and user, files need to be created by MS for uniqueness as execution time us under 1 second
	// Leaves off the extension
	public function getNotificationFilenameGeneric($notificationId,$userId,$principalUId,$enforceMS=false) {
		if ($enforceMS) {
			$ms=gettimeofday();
			$ms=".".$ms["usec"]; // milliseconds
		} else {
			$ms="";
		}
		$nowCompressed = CommonUtils::getGMTimeCompressed(0);
		$fileName = $nowCompressed.$ms.".n{$notificationId}.{$userId}.{$principalUId}";

		return $fileName;
	}

  public function getExtractFilename($principalUId,$notificationUId,$enforceMS=false) {
    if ($enforceMS) {
      $ms=gettimeofday();
      $ms=".".$ms["usec"]; // milliseconds
    } else {
      $ms="";
    }
    $nowCompressed = CommonUtils::getGMTimeCompressed(0);
    $fileName = $nowCompressed.$ms.".ext{$notificationUId}.{$principalUId}.csv";

    return $fileName;
  }

	public function getAllEDIFileDefNotificationOFD() {
		$sql="select nr.*, n.description, n.message, p.name principal_name
			  from   notification_recipients nr
		              INNER JOIN principal p on p.uid = nr.principal_uid and p.status = '".FLAG_STATUS_ACTIVE."',
               notification n
			  where  nr.notification_uid = ".NT_EDIFILEDEF."
			  and    nr.notification_uid = n.uid
			  order  by nr.principal_uid, n.uid";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}


	public function getEDIFileDefNotificationExport() {
		$sql="select nr.*, n.description, n.message, p.name principal_name
			  from   notification_recipients nr,
                     notification n,
					 principal p
			  where  nr.notification_uid = ".NT_EDIFILEDEF_EXPORT."
			  and    nr.notification_uid = n.uid
			  and    nr.principal_uid = p.uid
			  order  by nr.principal_uid, n.uid";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}


	public function getQueuedSmartEvents($type, $typeUid) {
	// events remain queued if the notification fails anywhere, so always select using Q status
	// We should check principal status here but it is low risk and there isnt a principalid on this table
	  $sql="SELECT
            	`uid`,
            	`created_date`,
            	`type`,
            	`type_uid`,
            	`processed_date`,
            	`status`,
            	`status_msg`,
            	`data_uid`,
                `general_reference_1`,
                `general_reference_2`
			 FROM smart_event s
			 WHERE  s.type = '{$type}'
			  	" . (($typeUid!=false)?("and s.type_uid = {$typeUid} "):("")) . "
			  	and s.status = '".FLAG_STATUS_QUEUED."'
			  	AND s.created_date > DATE(NOW() - INTERVAL 9 MONTH)
			 ORDER BY created_date";
		return $this->dbConn->dbGetAll($sql);
	}


	public function getSmartEventsByTypeData($type, $typeUid = false, $dataUid = false) {

            // events remain queued if the notification fails anywhere, so always select using Q status
            $sql="SELECT *
			 FROM smart_event s
			 WHERE  s.type = '{$type}'
			  	" . (($typeUid!=false)?("and s.type_uid = {$typeUid} "):("")) . "
                                " . (($dataUid!=false)?("and s.data_uid = {$dataUid} "):("")) . "
			  	and s.status = '".FLAG_STATUS_CLOSED."'
			 ORDER BY created_date";

		return $this->dbConn->dbGetAll($sql);
	}


	public function getSmartEventItem($seUid) {

            // events remain queued if the notification fails anywhere, so always select using Q status
            $sql="SELECT *
			 FROM smart_event s
			 WHERE s.uid = '".$seUid."'";

            return $this->dbConn->dbGetAll($sql);
	}


  // NB!! This function is also called by sql reports
  // @param returntype ~ 1=simple status ; 2=full desc ; 3 = array of both
  public function getPnPInvoiceUploadStatus($retailer,
                                            $documentTypeUId,
                                            $documentStatusUId,
                                            $invoiceDate,
                                            $seStatus,
                                            $seStatusMsg,
                                            $seCreatedDate,
                                            $serviceEnabledStatus,
                                            $serviceInvStartDate,
                                            $returnType=1) {

    if($retailer!=RETAILER_PNP) $pnpInvStatus = array("n/a","Not a PnP Retailer store");
    else if(!in_array($documentTypeUId,array(DT_ORDINV,DT_ORDINV_ZERO_PRICE))) $pnpInvStatus = array("n/a","Document type is not an order");
    else if(!in_array($documentStatusUId,array(DST_INVOICED,DST_DELIVERED_POD_OK,DST_DIRTY_POD))) $pnpInvStatus = array("Queued","Document not yet invoiced");
    // these get done before service status check as the service might have been turned off but you still want to report on one's past
    else if($seStatus=="S") $pnpInvStatus = array("Successful","Successfully Uploaded ".$seCreatedDate);
    else if($seStatus=="E") $pnpInvStatus = array("Failed!","Attempted upload failed with ".$seStatusMsg);
    else if($seStatus=="I") $pnpInvStatus = array("Info",$seStatusMsg);
    else if(($serviceEnabledStatus=="Y") && (strtotime($invoiceDate) < strtotime($serviceInvStartDate))) $pnpInvStatus = array("n/a","Invoice Date precedes activation date");
    else if(($serviceEnabledStatus=="Y") && (strtotime($invoiceDate) >= strtotime($serviceInvStartDate))) $pnpInvStatus = array("Queued","Queued for upload");
    //
    // There ideally should be one more condition in between here to check those where the service is not enabled (but was) and where it only got around to being q'd as these will now show as n/a instead
    //
    else $pnpInvStatus = array("n/a","Service not enabled");

    if ($returnType==1) return $pnpInvStatus[0];
    else if ($returnType==2) return $pnpInvStatus[1];
    else return $pnpInvStatus;
  }

  // NB!!!
  // The logic of this should be kept in sync with report ID :
  public function getDocumentElectronicInterfaces($principalId, $postDOCMASTID) {
    // fields are named specifically like this (pnp) as we will expand in future and have different retailers etc...
    $sql = "select pp.pnp_ws_invoice_enabled, pp.pnp_ws_starting_invoice_date, se.status pnp_inv_status, se.status_msg pnp_inv_status_msg, se.created_date pnp_inv_created_date,
                    dh.invoice_date, psm.retailer, dh.document_status_uid, dm.document_type_uid
            from   principal_preference pp
                      left join smart_event se on pp.principal_uid = se.type_uid and
                                                  type = '".SE_INVOICE_UPLOAD."' and
                                                  data_uid = '".$postDOCMASTID."',
                   document_master dm,
                   document_header dh,
                   principal_store_master psm
            where  pp.principal_uid = {$principalId}
            and    dm.uid = '".$postDOCMASTID."'
            and    dm.uid = dh.document_master_uid
            and    dh.principal_store_uid = psm.uid";

    $this->dbConn->dbQuery($sql);

    $arr=array();
    // there should really be only 1 row returned ... but ...
    if ($this->dbConn->dbQueryResultRows > 0) {
      while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
        // determine PnP Invoice eligibility - ranking of these are vital !!
        $pnpInvStatus = $this->getPnPInvoiceUploadStatus( $row["retailer"],
                                                          $row["document_type_uid"],
                                                          $row["document_status_uid"],
                                                          $row["invoice_date"],
                                                          $row["pnp_inv_status"],
                                                          $row["pnp_inv_status_msg"],
                                                          $row["pnp_inv_created_date"],
                                                          $row["pnp_ws_invoice_enabled"],
                                                          $row["pnp_ws_starting_invoice_date"],
                                                          $returnType=3);


        $arr[]= array("pnp_inv_status"=>$pnpInvStatus[0],
                      "pnp_inv_status_msg"=>$pnpInvStatus[1],
                      "retailer"=>$row["retailer"]);

      }
    } else {
        $arr[]= array("pnp_inv_status"=>"No Document",
                      "pnp_inv_status_msg"=>"No Document",
                      "retailer"=>"No Document");
    }

    return $arr;

  }


  //checks if a notification for the provided document id must be created for the delivery calendar.
  public function getNotificationDeliveryException($documentMasterUId) {

    $errorTO = new ErrorTO();
    $errorTO->type = FLAG_ERRORTO_ERROR;  //preset.
    $errorTO->object = array();


    //1. get document data
    //2  compare to delivery calendar data : by depot/date/type
    $sql="SELECT
                m.uid, m.principal_uid, m.depot_uid, m.document_type_uid,
                h.due_delivery_date, cal.*
         FROM document_master m
        INNER JOIN document_header h on m.uid = h.document_master_uid
        INNER JOIN depot_delivery_calendar cal on m.depot_uid = cal.depot_uid
                                                and cal.`type` = 1
                                                and due_delivery_date = DATE(FROM_UNIXTIME(cal.timestamp))
        WHERE m.uid = {$documentMasterUId}";

     $exceptionArr = $this->dbConn->dbGetAll($sql);

    // check point
    // no date for depot - EXIT
    // date exists by depot/date/type - CONT.
    if(count($exceptionArr)==0 || !isset($exceptionArr[0]['principal_uid'])){
      return $errorTO;
    }


    //3 . get principal loaded notifications
    $recipientsArr = $this->getNotificationRecipients($exceptionArr[0]['principal_uid'], NT_DELIVERY_EXCEPTION);

    //check, no recipients/notifications created, return.
    if(count($recipientsArr)==0){
      return $errorTO;
    }


    $depotId = $exceptionArr[0]['depot_uid'];
    $documentTypeId = $exceptionArr[0]['document_type_uid'];


    //check mappings / options
    //build list of notification uids
    $resultArr = array();
    foreach($recipientsArr as $re){

      $depotFilter = CommonUtils::getParamValuesFromString($re["additional_parameter_string"],"p1","&","=");
      $documentTypeFilter = CommonUtils::getParamValuesFromString($re["additional_parameter_string"],"p2","&","=");

       if (
           (($depotFilter=="") || ($depotFilter=="*") || (in_array($depotId, explode(',',$depotFilter)))) &&
           (($documentTypeFilter=="") || ($documentTypeFilter=="*") || (in_array($documentTypeId, explode(',',$documentTypeFilter))))
          ){
         $resultArr[] = $re['uid'];
       }

    }

    if(count($resultArr)>0){
      $errorTO->type = FLAG_ERRORTO_SUCCESS;  //yes load smart events for list of type uids.
      $errorTO->object = $resultArr;
    }

    return $errorTO;

  }


}
?>

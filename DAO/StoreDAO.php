<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class StoreDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

	// get stores the user is registered for
	public function getUserPrincipalStoreArray($userId, $principalUId, $arrayIndex, $filterArr = array(), $showVendorStores=false, $showOnlyStoresInTT=false, $limitDepotId = false) {

	    // if came from a screen list, check to see if you need to include deleted
		if (isset($_POST["PAGETYPE"])) $pageType=$_POST["PAGETYPE"]; else if (isset($_GET["PAGETYPE"])) $pageType=$_GET["PAGETYPE"]; else $pageType="A";

/*
		$sqlSpecialJOIN = '';
		if(isset($filterArr['special_field_or']) && $filterArr['special_field_or'] != ''){
	      $sqlSpecialJOIN = " LEFT JOIN (select sd.entity_uid, group_concat(sd.value) value from special_field_details sd
											LEFT JOIN special_field_fields sf on sd.field_uid = sf.uid and sf.type = '".CT_STORE_SHORTCODE."' and sf.principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."
											where sf.uid IS NOT NULL and sd.value like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['special_field_or'])."%'
										 	GROUP BY sd.entity_uid
										 ) fsd on b.uid = fsd.entity_uid ";
		}
*/
    $sqlSpecialJOIN = '';
		// chains might be invalid
		$sql="select 'II', a.uid, b.uid psm_uid, b.deliver_name store_name, b.deliver_add1, b.deliver_add2, c.uid depot_uid, c.name depot_name, d.description chain_name, b.principal_uid,
				     g.uid dd_uid, g.name delivery_day, b.on_hold, b.status, d.status chain_status, GROUP_CONCAT(IFNULL(sd.value,'') order by sf.order separator ',') as special_fields,
					 b.ean_code, b.old_account, concat(rep.first_name,' ',rep.surname) as 'rep_name'
				from   principal_store_master b
							left join user_principal_chain upc on b.principal_chain_uid = upc.principal_chain_uid and upc.user_uid = {$userId}  -- chain can be modified or not exist
							LEFT JOIN special_field_fields sf on sf.principal_uid = b.principal_uid and sf.type = '".CT_STORE_SHORTCODE."'
							LEFT JOIN special_field_details sd on sd.field_uid = sf.uid and sd.entity_uid = b.uid and sd.uid = (SELECT MAX(x.uid) FROM special_field_details x WHERE x.field_uid = sf.uid AND x.entity_uid = b.uid)
							left join depot c on b.depot_uid = c.uid
                                                        left join principal_sales_representative rep on b.principal_sales_representative_uid = rep.uid
							{$sqlSpecialJOIN}
							left join user_principal_store a on a.principal_store_uid = b.uid and a.user_uid = ".mysqli_real_escape_string($this->dbConn->connection, $userId)."
							left join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid={$principalUId})
							left join user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid={$principalUId})
					   		left join principal_chain_master d on b.principal_chain_uid = d.uid and b.principal_uid = d.principal_uid
				       		left join `day` g on b.delivery_day_uid = g.uid
					   		left join user_principal_depot e on b.principal_uid = e.principal_id and b.depot_uid = e.depot_id and e.user_id = ".mysqli_real_escape_string($this->dbConn->connection, $userId)."   -- principal_store.depot can be modified
				where  b.principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."
                                ".(($limitDepotId!==false)?(' and b.depot_uid = "'.mysqli_real_escape_string($this->dbConn->connection, $limitDepotId).'" '):(''))."
				and    (c.uid is null or e.uid is not null)
			  	and    (d.uid is null or (upc.uid is not null or urc.uid is not null))".
				(($showVendorStores===false)?" and b.owned_by is null ":"").
				(($showOnlyStoresInTT===true)?" and exists (select 1 from document_header dh where dh.principal_store_uid = b.uid ) ":"")."
				and    b.status = '".(($pageType==FLAG_STATUS_DELETED)?FLAG_STATUS_DELETED:FLAG_STATUS_ACTIVE)."'
				and    (a.uid is not null or ur.uid is not null)";

        //UID
		if(isset($filterArr['uid']) && $filterArr['uid'] != ''){
	      $sql .= " and b.uid like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['uid'])."%' ";
	    }

	    //OR special fields - needs to be enclosed :(
	    $sqlSpecialOR = '';
		if(isset($filterArr['special_field_or']) && $filterArr['special_field_or'] != ''){
	      $sqlSpecialOR = " or fsd.entity_uid is not null ";
	      $sqlSpecialOR = '';
	    }

		//OR special fields - needs to be enclosed :(
	    $sqlEanCodeOR = '';
		if(isset($filterArr['ean_code_or']) && $filterArr['ean_code_or'] != ''){
	      $sqlEanCodeOR = " or b.ean_code like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['ean_code_or'])."%' ";
	    }


	    //Store Name
		if(isset($filterArr['store']) && $filterArr['store'] != ''){

		  if($sqlSpecialOR != '' && $sqlEanCodeOR != ''){
		    $sql .= " and ( b.deliver_name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['store'])."%' " . $sqlEanCodeOR . " " . $sqlSpecialOR . " ) ";
		  } else if($sqlEanCodeOR != ''){
		    $sql .= " and ( b.deliver_name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['store'])."%' " . $sqlEanCodeOR . ") ";
		  } else if($sqlSpecialOR != ''){
		    $sql .= " and ( b.deliver_name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['store'])."%' " . $sqlSpecialOR . ") ";
		  } else {
	        $sql .= " and b.deliver_name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['store'])."%' ";
		  }

	    }

	    //deliver 1
		if(isset($filterArr['del_add1']) && $filterArr['del_add1'] != ''){
	      $sql .= " and b.deliver_add1 like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['del_add1'])."%' ";
	    }

	    //deliver 2
		if(isset($filterArr['del_add2']) && $filterArr['del_add2'] != ''){
	      $sql .= " and b.deliver_add2 like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['del_add2'])."%' ";
	    }

	    //depot name
		if(isset($filterArr['depot']) && $filterArr['depot'] != ''){
	      $sql .= " and c.name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['depot'])."%'";
	    }

	    //chain name
	    if(isset($filterArr['chain']) && $filterArr['chain'] != ''){
	      $sql .= " and d.description like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['chain'])."%' ";
	    }

		//old_account
	    if(isset($filterArr['old_account']) && $filterArr['old_account'] != ''){
	      $sql .= " and b.old_account like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['old_account'])."%' ";
	    }

	    //AND special fields
		if(isset($filterArr['special_field']) && $filterArr['special_field'] != ''){
	      $sql .= " and sd.value like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['special_field'])."%' ";
	    }

	    $sql .= " group by b.uid order by b.deliver_name";

//    file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/capdebug2.txt', $sql , FILE_APPEND);
		$this->dbConn->dbQuery($sql);


		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ($arrayIndex=="principal_id") $arr[$row['principal_id']] = $row;
				else $arr[] = $row;
			}
		}

		return $arr;
	}

	public function getUserPrincipalStoreArrayNew($userId, $principalUId, $arrayIndex, $filterArr = array(), $showVendorStores=false, $showOnlyStoresInTT=false, $limitDepotId = false) {

	    // if came from a screen list, check to see if you need to include deleted
		if (isset($_POST["PAGETYPE"])) $pageType=$_POST["PAGETYPE"]; else if (isset($_GET["PAGETYPE"])) $pageType=$_GET["PAGETYPE"]; else $pageType="A";

		$sql="SELECT 'LL',
		a.uid, 
          b.uid psm_uid, 
          b.deliver_name store_name, 
          b.deliver_add1, 
          b.deliver_add2, 
          c.uid depot_uid, 
          c.name depot_name, 
          d.description chain_name, 
          b.principal_uid,
          g.uid dd_uid, 
          g.name delivery_day, 
          b.on_hold, 
          b.status, 
          d.status chain_status, 
          '' as special_fields,
          b.ean_code, 
          b.old_account, 
          concat(rep.first_name,' ',rep.surname) as 'rep_name'
          FROM principal_store_master b
          LEFT JOIN user_principal_chain upc on b.principal_chain_uid = upc.principal_chain_uid and upc.user_uid = {$userId}  -- chain can be modified or not exist
          LEFT JOIN depot c on b.depot_uid = c.uid
          LEFT JOIN  principal_sales_representative rep on b.principal_sales_representative_uid = rep.uid
          LEFT JOIN  user_principal_store a on a.principal_store_uid = b.uid and a.user_uid = ".mysqli_real_escape_string($this->dbConn->connection, $userId)."
          LEFT JOIN  user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid={$principalUId})
          LEFT JOIN  user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid={$principalUId})
          LEFT JOIN  principal_chain_master d on b.principal_chain_uid = d.uid and b.principal_uid = d.principal_uid
          LEFT JOIN  `day` g on b.delivery_day_uid = g.uid
          LEFT JOIN  user_principal_depot e on b.principal_uid = e.principal_id and b.depot_uid = e.depot_id and e.user_id = ".mysqli_real_escape_string($this->dbConn->connection, $userId)."   -- principal_store.depot can be modified
          WHERE  b.principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."
                                   ".(($limitDepotId!==false)?(' and b.depot_uid = "'.mysqli_real_escape_string($this->dbConn->connection, $limitDepotId).'" '):(''))."
          AND (c.uid is null or e.uid is not null)
          AND (d.uid is null or (upc.uid is not null or urc.uid is not null))".
          (($showVendorStores===false)?" and b.owned_by is null ":"").
          (($showOnlyStoresInTT===true)?" and exists (select 1 from document_header dh where dh.principal_store_uid = b.uid ) ":"")."
          AND   b.status = '".(($pageType==FLAG_STATUS_DELETED)?FLAG_STATUS_DELETED:FLAG_STATUS_ACTIVE)."'
          AND  (a.uid is not null or ur.uid is not null)";

        //UID
		if(isset($filterArr['uid']) && $filterArr['uid'] != ''){
	      $sql .= " and b.uid like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['uid'])."%' ";
	    }

	    //OR special fields - needs to be enclosed :(
	    $sqlSpecialOR = '';
		if(isset($filterArr['special_field_or']) && $filterArr['special_field_or'] != ''){
	      $sqlSpecialOR = " or fsd.entity_uid is not null ";
	    }

		//OR special fields - needs to be enclosed :(
	    $sqlEanCodeOR = '';
		if(isset($filterArr['ean_code_or']) && $filterArr['ean_code_or'] != ''){
	      $sqlEanCodeOR = " or b.ean_code like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['ean_code_or'])."%' ";
	    }


	    //Store Name
		if(isset($filterArr['store']) && $filterArr['store'] != ''){

		  if($sqlSpecialOR != '' && $sqlEanCodeOR != ''){
		    $sql .= " and ( b.deliver_name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['store'])."%' " . $sqlEanCodeOR . " " . $sqlSpecialOR . " ) ";
		  } else if($sqlEanCodeOR != ''){
		    $sql .= " and ( b.deliver_name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['store'])."%' " . $sqlEanCodeOR . ") ";
		  } else if($sqlSpecialOR != ''){
		    $sql .= " and ( b.deliver_name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['store'])."%' " . $sqlSpecialOR . ") ";
		  } else {
	        $sql .= " and b.deliver_name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['store'])."%' ";
		  }

	    }

	    //deliver 1
		if(isset($filterArr['del_add1']) && $filterArr['del_add1'] != ''){
	      $sql .= " and b.deliver_add1 like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['del_add1'])."%' ";
	    }

	    //deliver 2
		if(isset($filterArr['del_add2']) && $filterArr['del_add2'] != ''){
	      $sql .= " and b.deliver_add2 like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['del_add2'])."%' ";
	    }

	    //depot name
		if(isset($filterArr['depot']) && $filterArr['depot'] != ''){
	      $sql .= " and c.name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['depot'])."%'";
	    }

	    //chain name
	    if(isset($filterArr['chain']) && $filterArr['chain'] != ''){
	      $sql .= " and d.description like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['chain'])."%' ";
	    }

		//old_account
	    if(isset($filterArr['old_account']) && $filterArr['old_account'] != ''){
	      $sql .= " and b.old_account like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['old_account'])."%' ";
	    }

	    //AND special fields
		if(isset($filterArr['special_field']) && $filterArr['special_field'] != ''){
	      $sql .= " and sd.value like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['special_field'])."%' ";
	    }

	    $sql .= " group by b.uid order by b.deliver_name";


		$this->dbConn->dbQuery($sql);


		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ($arrayIndex=="principal_id") $arr[$row['principal_id']] = $row;
				else $arr[] = $row;
			}
		}

		return $arr;
	}


	public function getAllGlobalStores() {
		$sql="select uid, deliver_name, deliver_add1, deliver_add2,deliver_add3, bill_name, bill_add1, bill_add2, bill_add3, ean_code, vat_number,
					no_vat, on_hold, chain_uid, branch_code
				from   global_store_master";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[$row['uid']] = $row;
			}
		}

		return $arr;
	}

	public function getGlobalStoreItem($storeUId) {
		$sql="select uid, deliver_name, deliver_add1, deliver_add2,deliver_add3, bill_name, bill_add1, bill_add2, bill_add3, ean_code, vat_number,
					no_vat, on_hold, chain_uid, branch_code, old_account
				from   global_store_master
				where  uid='".mysqli_real_escape_string($this->dbConn->connection, $storeUId)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// this list needs to be filtered because otherwise 10'000+ rows are returned.
	// the difference with this func and getUserPrincipalStoreArray() is that getUserPrincipalStoreArray only returns stores the user is actually registered for.
	// getAllPrincipalStoresUser() returns all stores but only for principals the user is registered for.
	public function getAllPrincipalStoresUser($userId, $principalUId, $filterArr = array(), $showVendorStores=false) {

		// THIS FUNCTION NEEDS OVERHAUL ! IT HAS LOST ITS PURPOSE AND WAY. Please chat to Mark if you should ever need it //
		$sql="select a.uid psm_uid, a.deliver_name store_name, a.deliver_add1, a.deliver_add2, b.name principal_name, c.name depot_name, d.description chain_name,
					a.on_hold, a.status, d.status chain_status, ean_code, a.old_account
				from   	user_principal_depot e,
					    principal_store_master a
					    	left join user_principal_chain f on f.user_uid = ".mysqli_real_escape_string($this->dbConn->connection, $userId)." and f.principal_chain_uid = a.principal_chain_uid
							left join principal b on a.principal_uid = b.uid
				       		left join depot c on a.depot_uid = c.uid
				       		left join user_role urc on urc.user_id = ".mysqli_real_escape_string($this->dbConn->connection, $userId)." and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalUId).")
				       		INNER JOIN principal_chain_master d on a.principal_chain_uid = d.uid and a.principal_uid = d.principal_uid
				where  e.user_id = ".mysqli_real_escape_string($this->dbConn->connection, $userId)."
				and    a.principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."
				and    e.depot_id = a.depot_uid
				and    e.principal_id = a.principal_uid
				and    (f.uid is not null or urc.uid is not null)
				and    a.status = '".FLAG_STATUS_ACTIVE."' ".
				(($showVendorStores===false)?" and a.owned_by is null ":"");


        //UID
		if(isset($filterArr['uid']) && $filterArr['uid'] != ''){
	      $sql .= " and a.uid like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['uid'])."%' ";
	    }

	    //Store Name
		if(isset($filterArr['store']) && $filterArr['store'] != ''){
	      $sql .= " and a.deliver_name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['store'])."%' ";
	    }

	    //deliver 1
		if(isset($filterArr['del_add1']) && $filterArr['del_add1'] != ''){
	      $sql .= " and a.deliver_add1 like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['del_add1'])."%' ";
	    }

	    //deliver 2
		if(isset($filterArr['del_add1']) && $filterArr['del_add1'] != ''){
	      $sql .= " and a.deliver_add2 like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['del_add1'])."%' ";
	    }

		//principal name
		if(isset($filterArr['prin']) && $filterArr['prin'] != ''){
	      $sql .= " and b.name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['prin'])."%'";
	    }

	    //depot name
		if(isset($filterArr['depot']) && $filterArr['depot'] != ''){
	      $sql .= " and c.name like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['depot'])."%'";
	    }

	    //chain name
	    if(isset($filterArr['chain']) && $filterArr['chain'] != ''){
	      $sql .= " and d.description like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['chain'])."%' ";
	    }

		//old_account
	    if(isset($filterArr['old_account']) && $filterArr['old_account'] != ''){
	      $sql .= " and a.old_account like '%".mysqli_real_escape_string($this->dbConn->connection, $filterArr['old_account'])."%' ";
	    }

	    $sql .= " order  by a.deliver_name";


		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[$row['psm_uid']] = $row;
			}
		}

		return $arr;
	}

	// Item functions must not exclude stores based on status
	public function getPrincipalStoreItem($uid) {
		$sql="select a.uid, 
		             a.deliver_name store_name, 
		             a.deliver_add1, 
		             a.deliver_add2, 
		             a.deliver_add3,
                 a.bill_name, 
                 a.bill_add1, 
                 a.bill_add2, 
                 a.bill_add3, 
                 a.tel_no1, 
                 a.tel_no2,
                 a.email_add, 
                 a.principal_uid, 
                 b.name principal_name, 
                 c.code depot_code, 
                 a.depot_uid,
                 c.name depot_name, 
                 a.principal_chain_uid, 
                 a.alt_principal_chain_uid, 
                 d.description chain_name,
                 a.ean_code, 
                 a.vat_number, 
                 a.branch_code, 
                 a.no_vat, 
                 a.on_hold, 
                 a.delivery_day_uid, 
                 a.order_day_uid, 
                 a.store_string,
                 a.old_account, 
                 a.captured_by, 
                 a.ledger_balance, 
                 a.ledger_credit_limit, 
                 a.status,
                 d.status chain_status, 
                 a.owned_by, 
                 a.vendor_created_by_uid, 
                 c.wms, 
                 a.area_uid,
                 area.description area_description, 
                 `day`.name delivery_day, 
                 a.epod_store_flag,
                 a.epod_rsa_id, a.epod_cellphone_number, 
                 a.principal_sales_representative_uid,
                 c.order_start_status, 
                 a.vat_excl_authorised_by, 
                 a.retailer,
                 a.bank_details_to_print,
                 a.q_r_code_to_print, 
                 a.export_number_enabled, 
                 a.vat_number_2,
		             b.export_number, 
		             a.export_number_enabled,
		             a.courier_code,
		             a.off_invoice_discount,
		             a.warehouse_link,
		             a.auto_mail_invoice,
		             a.no_prices_on_invoice,
		             if(a.local_country='Local','Y','N') AS 'LC'
			from  principal_store_master a
                          left join principal b on a.principal_uid = b.uid
                          left join depot c on a.depot_uid = c.uid
                          left join principal_chain_master d on a.principal_chain_uid = d.uid and a.principal_uid = d.principal_uid
                          left join area on area.uid = a.area_uid
                          left join `day` on `day`.uid = a.delivery_day_uid
			where  a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $uid)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getPrincipalStoreByGLN($principalUId,$GLN) {
		$sql="select uid, old_account, deliver_name, stripped_deliver_name, owned_by, vendor_created_by_uid, depot_uid, no_vat
			  from   principal_store_master
			  where  principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
			  and    ean_code='".mysqli_real_escape_string($this->dbConn->connection, $GLN)."'
			  order  by if(status='".FLAG_STATUS_DELETED."',2,1), if(owned_by is null,1,2)";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// gets stores, ignoring the chain link
	public function getPrincipalStoreExclChainItem($uid, $showVendorStores=false) {
		$sql="select a.uid, a.deliver_name store_name, a.deliver_add1, a.deliver_add2, a.deliver_add3, a.bill_name, a.bill_add1, a.bill_add2, a.bill_add3, a.principal_uid, b.name principal_name,
				c.code depot_code, a.depot_uid, c.name depot_name, a.principal_chain_uid, d.description chain_name, a.ean_code, a.vat_number, a.branch_code, a.no_vat, a.on_hold,
				a.delivery_day_uid, a.store_string, a.old_account, a.captured_by, a.status, d.status chain_status
				from  principal_store_master a
					   left join principal b on a.principal_uid = b.uid
				       left join depot c on a.depot_uid = c.uid
				       LEFT JOIN principal_chain_master d on a.principal_chain_uid = d.uid and a.principal_uid = d.principal_uid
				where  a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $uid)."'".
				(($showVendorStores===false)?" and a.owned_by is null ":"");

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// Item functions must not exclude stores based on status
	public function getUserPrincipalStoreItem($userId, $principalStoreUId) {

		if (!isset($_SESSION)) session_start() ;
		$principalType = $_SESSION['principal_type']; // when accessing from the perspective of depot, allow view all (check is not done as per depot-user but rather principal-type)

		// skip security check if system user
    	if (($userId==SESSION_ADMIN_USERID) || ($principalType==PT_DEPOT)) {
			$sql="select b.uid, b.uid psm_uid, b.deliver_name, b.deliver_add1, b.deliver_add2, b.deliver_add3, b.bill_name, b.bill_add1, b.bill_add2, b.bill_add3,
					       b.principal_uid, b.depot_uid, c.name depot_name, b.principal_chain_uid, d.description chain_name,
					       b.ean_code, b.vat_number, b.branch_code, b.no_vat, b.on_hold, b.delivery_day_uid, b.captured_by, b.ledger_balance, b.ledger_credit_limit,
						   b.status, d.status chain_status, b.owned_by, b.vendor_created_by_uid, b.tel_no1, b.tel_no2, b.email_add, c.wms, c.order_start_status,
                b.vat_excl_authorised_by
					from   principal_store_master b
					       left join depot c on b.depot_uid = c.uid
					       INNER JOIN principal_chain_master d on b.principal_chain_uid = d.uid and b.principal_uid = d.principal_uid
				  	where  b.uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalStoreUId)."'";
    	} else {
    		$sql="select a.uid, b.uid psm_uid, b.deliver_name, b.deliver_add1, b.deliver_add2, b.deliver_add3, b.bill_name, b.bill_add1, b.bill_add2, b.bill_add3,
					       b.principal_uid, b.depot_uid, c.name depot_name, b.principal_chain_uid, d.description chain_name,
					       b.ean_code, b.vat_number, b.branch_code, b.no_vat, b.on_hold, b.delivery_day_uid, b.captured_by, b.ledger_balance, b.ledger_credit_limit,
						   b.status, d.status chain_status, b.owned_by, b.vendor_created_by_uid, b.tel_no1, b.tel_no2, b.email_add, c.wms, c.order_start_status,
                b.vat_excl_authorised_by
					from   principal_store_master b
								left join depot c on b.depot_uid = c.uid
						   		left join user_principal_store a on a.principal_store_uid = b.uid and a.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
								left join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid=b.principal_uid)
  						   INNER JOIN principal_chain_master d on b.principal_chain_uid = d.uid and b.principal_uid = d.principal_uid,
						   user_principal_depot e -- store depot can change
				  	where  b.uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalStoreUId)."'
				  	and    b.principal_uid = e.principal_id
				  	and    b.depot_uid = e.depot_id
					and    (a.uid is not null or ur.uid is not null)
					and    e.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'";
    	}

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// this does NOT join on user_principal_store because it needs to be used by screen to add stores the user does not already have
	public function getAllStoresByPrincipalChain($principalId, $principalChainId, $showVendorStores=false) {
		$sql="select b.uid principal_chain_uid, b.description chain_name, c.principal_uid, d.name principal_name, e.uid depot_uid, e.name depot_name, c.uid store_uid, c.deliver_name store_name,
				     c.on_hold, c.status
				from   principal_chain_master b,
				       principal_store_master c,
				       principal d,
				       depot e
				where  c.principal_chain_uid = b.uid
				and    c.principal_uid = b.principal_uid
				and    c.principal_uid = d.uid
				and    c.depot_uid = e.uid
				and exists (
							 select 1
							 from   user_principal_depot f
							 where  c.principal_uid = f.principal_id
							 and    c.depot_uid = f.depot_id
							)
				and    c.principal_uid ='".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
				and    b.uid ='".mysqli_real_escape_string($this->dbConn->connection, $principalChainId)."'
				and    b.status = '".FLAG_STATUS_ACTIVE."'".
				(($showVendorStores===false)?" and c.owned_by is null ":"")."
				and    c.status = '".FLAG_STATUS_ACTIVE."'";

		$this->dbConn->dbinsQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// same as above, but more efficient because of exclusive join eliminating stores user already has
	public function getAllStoresByPrincipalChainExclusive($principalId, $principalChainId,$userId, $showVendorStores=false) {
		// NOTE: not necessary to check for BYPASS STORE ROLE here because this functionality should only be used when u not using the role for user
		$sql="select b.uid principal_chain_uid, b.description chain_name, c.principal_uid, d.name principal_name, e.uid depot_uid, e.name depot_name, c.uid store_uid, c.deliver_name store_name,
				     c.on_hold, c.status
				from   principal_chain_master b,
				       principal_store_master c,
				       principal d,
				       depot e
				where  c.principal_chain_uid = b.uid
				and    c.principal_uid = b.principal_uid
				and    c.principal_uid = d.uid
				and    c.depot_uid = e.uid
				and exists (
							 select 1
							 from   user_principal_depot f
							 where  c.principal_uid = f.principal_id
							 and    c.depot_uid = f.depot_id
							)
				and not exists (
								select 1
								from   user_principal_store g
								where  g.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
								and    c.uid = g.principal_store_uid
							)
				and    c.principal_uid ='".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
				and    b.uid ='".mysqli_real_escape_string($this->dbConn->connection, $principalChainId)."'".
				(($showVendorStores===false)?" and c.owned_by is null ":"")."
				and    b.status = '".FLAG_STATUS_ACTIVE."'
				and    c.status = '".FLAG_STATUS_ACTIVE."'";

		$this->dbConn->dbinsQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getAllPrincipalChainsForUser($userId, $principalUId,$lfilter) {
		  if (isset($_POST["PAGETYPE"])) $pageType=$_POST["PAGETYPE"]; else if (isset($_GET["PAGETYPE"])) $pageType=$_GET["PAGETYPE"]; else $pageType=FLAG_STATUS_ACTIVE;

         if($lfilter==CHAIN_FILTER_PRICE) {
      	    $chainfilter = "and b.chain_group = " . CHAIN_FILTER_PRICE ;
         } elseif($lfilter==CHAIN_FILTER_DEBTOR) {
              if($principalUId == 305)	{
                   $chainfilter = "and b.chain_group = " . CHAIN_FILTER_DEBTOR ;
              } else {
                   $chainfilter = "and b.chain_group = " . CHAIN_FILTER_PRICE ;
              }
         } else {
       	    $chainfilter = "";
         }
		  $sql = "select b.uid as principal_chain_uid, b.description chain_name, b.status
			       	from   principal_chain_master b
			      	left join user_principal_chain a on a.principal_chain_uid = b.uid and a.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
			      	left join user_role urc on '".mysqli_real_escape_string($this->dbConn->connection, $userId)."' = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid=".mysqli_real_escape_string($this->dbConn->connection, $principalUId).")
			      	where  b.principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."
				and (a.uid is not null or urc.uid is not null)
				and    b.status = '{$pageType}'
				" .$chainfilter. "
        order by b.description";

		$this->dbConn->dbinsQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getAllAlternatePrincipalChainsForUser($userId, $principalUId) {
		if (isset($_POST["PAGETYPE"])) $pageType=$_POST["PAGETYPE"]; else if (isset($_GET["PAGETYPE"])) $pageType=$_GET["PAGETYPE"]; else $pageType=FLAG_STATUS_ACTIVE;


		$sql = "select b.uid as principal_chain_uid, 
		               b.description chain_name, 
		               b.status
				    from   principal_chain_master b
				    left join user_principal_chain a on a.principal_chain_uid = b.uid 
				                                     and a.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
			 	    left join user_role urc on '".mysqli_real_escape_string($this->dbConn->connection, $userId)."' = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." 
			 	                            and (urc.entity_uid is null or urc.entity_uid=".mysqli_real_escape_string($this->dbConn->connection, $principalUId).")
				where  b.principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."
				and (a.uid is not null or urc.uid is not null)
				and    b.status = '{$pageType}'
                            order by b.description";

		$this->dbConn->dbinsQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}





	public function getAllChainsForPrincipal($principalUId) {
		$sql="select uid principal_chain_uid, description chain_name, status
			 	from   principal_chain_master
			 	where  principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
			 	and    status = '".FLAG_STATUS_ACTIVE."'";

		$this->dbConn->dbinsQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[$row['principal_chain_uid']] = $row;
			}
		}

		return $arr;
	}

	public function getPrincipalChainItem($chainUId) {
		$sql="select a.uid, a.description chain_name, principal_uid, status, a.captured_by, a.old_code
				from   principal_chain_master a
				where  uid = '".mysqli_real_escape_string($this->dbConn->connection, $chainUId)."'";

		$this->dbConn->dbinsQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getUserPrincipalChainItem($userId, $principalChainUId){

		if ($userId==SESSION_ADMIN_USERID) {
			$sql="select b.uid, b.description chain_name, b.captured_by
					from   principal_chain_master b
					where  b.uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalChainUId)."'
					and    b.status = '".FLAG_STATUS_ACTIVE."'";
		} else {
		    $sql="select b.uid as principal_chain_uid, b.description chain_name, b.captured_by
                	from   principal_chain_master b
                		left join user_principal_chain a on a.principal_chain_uid = b.uid and a.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
                    	left join user_role urc on '".mysqli_real_escape_string($this->dbConn->connection, $userId)."' = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid = b.principal_uid)
                	where  (a.uid IS NOT NULL or urc.uid IS NOT NULL)
                	and	b.uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalChainUId)."'
                	and	b.status = '".FLAG_STATUS_ACTIVE."'";

		}

		$this->dbConn->dbinsQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getAllGlobalChains() {
		$sql="select uid, description, status
				from   global_chain_master
				where  status = '".FLAG_STATUS_ACTIVE."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[$row['uid']] = $row;
			}
		}

		return $arr;
	}

	public function getGlobalChainItem($UId) {
		$sql="select uid, description, status
				from   global_chain_master
				where  uid = '".mysqli_real_escape_string($this->dbConn->connection, $UId)."'
				and    status = '".FLAG_STATUS_ACTIVE."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// essentially returns all stores for all principals by search criteria
	// keywords must be already lowercased !
	// a Stored function is made use of, which can be quite slow, so becareful of the length of the string passed to it !!
	public function getUserAgentPrincipalStoreArray($userId, $keywordsArr, $showVendorStores=false) {
		$where="";
		foreach ($keywordsArr as $w) {
			if ($where!="") $where.=" and concat(b.deliver_name,b.deliver_add1,c.name,d.description,p.name,g.name) like '%".trim($w)."%' ";
			else $where=" concat(b.deliver_name,b.deliver_add1,c.name,d.description,p.name,g.name) like '%".trim($w)."%' ";
			/* too slow to use function
			if ($where!="") $where.=" and alphaNumericValue(concat(b.deliver_name,b.deliver_add1)) like '%".trim($w)."%' ";
			else $where=" alphaNumericValue(concat(b.deliver_name,b.deliver_add1)) like '%".trim($w)."%' ";
			*/
		}
		if ($where!="") $where=" and ({$where}) ";
		$sql="select b.uid psm_uid, b.deliver_name store_name, b.deliver_add1, c.name depot_name, d.description chain_name, p.name principal,g.name delivery_day,
					 b.status, d.status chain_status, b.owned_by, b.vendor_created_by_uid
				from   principal_store_master b
							left join depot c on b.depot_uid = c.uid
							left join user_principal_store a on a.principal_store_uid = b.uid and a.user_uid = '{$userId}'
							left join user_role ur on '{$userId}' = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid = b.principal_uid)
							left join user_role urc on '{$userId}' = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid = b.principal_uid)
					   INNER JOIN principal_chain_master d on b.principal_chain_uid = d.uid and b.principal_uid = d.principal_uid
				       	left join `day` g on b.delivery_day_uid = g.uid
				       	left join user_principal_chain f on f.principal_chain_uid = b.principal_chain_uid and f.user_uid = '{$userId}',   -- chain can be modified
					   user_principal_depot e,   -- principal_store.depot can be modified
					   principal p
				where  b.principal_uid = e.principal_id
				and    e.user_id = '{$userId}'
			  	and    b.depot_uid = e.depot_id
				and    e.principal_id = p.uid
				and    (a.uid is not null or ur.uid is not null)
				and    (a.uid is not null or ur.uid is not null)".
				(($showVendorStores===false)?" and b.owned_by is null ":"")."
				and    b.status = '".FLAG_STATUS_ACTIVE."'
				{$where}
				order  by b.deliver_name";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// essentially returns all stores for a principal by search criteria, BUT is different from agent search as it indicates whether or not permissions are present
	// NOTE: stores outside of chain/depot permissions are shown !
	// keywords must be already lowercased !
	// a Stored function is made use of, which can be quite slow, so becareful of the length of the string passed to it !!
	public function getUserSearchPrincipalStoreArray($userId, $principalId, $keywordsArr, $showVendorStores=true) {
		$where="";
		foreach ($keywordsArr as $w) {
			if ($where!="") $where.=" and concat(b.deliver_name,b.deliver_add1,c.name,d.description,p.name,b.ean_code) like '%".trim($w)."%' ";
			else $where=" concat(b.deliver_name,b.deliver_add1,c.name,d.description,p.name,b.ean_code) like '%".trim($w)."%' ";
			/* too slow to use function
			if ($where!="") $where.=" and alphaNumericValue(concat(b.deliver_name,b.deliver_add1)) like '%".trim($w)."%' ";
			else $where=" alphaNumericValue(concat(b.deliver_name,b.deliver_add1)) like '%".trim($w)."%' ";
			*/
		}
		if ($where!="") $where=" and ({$where}) ";

		if ($showVendorStores===false) {
			$where.=" and b.owned_by is null ";
		}

		$sql="select distinct b.uid psm_uid, b.deliver_name store_name, b.deliver_add1, depot_uid, c.name depot_name, d.description chain_name, p.name principal,
					 if((a.uid is not null or ur.uid is not null) and (upc.uid is not null or urc.uid IS NOT NULL) and upd.uid is not null,1,0) has_store_permission, b.status, b.owned_by, b.vendor_created_by_uid,
					 b.ean_code, g.uid dd_uid, g.name delivery_day
				from   principal_store_master b
							left join depot c on b.depot_uid = c.uid
							left join user_principal_store a on a.principal_store_uid = b.uid and a.user_uid = '{$userId}'
							left join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid = b.principal_uid)
							left join user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid = b.principal_uid)
							left join user_principal_chain upc on upc.principal_chain_uid = b.principal_chain_uid and upc.user_uid = '{$userId}'
							left join user_principal_depot upd on upd.principal_id = b.principal_uid and upd.depot_id = b.depot_uid and upd.user_id = '{$userId}'
					   		left join principal_chain_master d on b.principal_chain_uid = d.uid and b.principal_uid = d.principal_uid and d.status='".FLAG_STATUS_ACTIVE."'
					   		left join `day` g on b.delivery_day_uid = g.uid,
					   principal p
				where  b.principal_uid = '{$principalId}'
				and    b.principal_uid = p.uid
				-- and    (a.uid is not null or ur.uid is not null)
				-- and    b.status = '".FLAG_STATUS_ACTIVE."'
				{$where}
				order  by b.deliver_name";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}


	// get store uid by special field, *** could be multiple returned ***
	// this uses only the smpd UID and not label for field due to improved performance
// NOTE : IT IS A SYSTEM STANDARD THAT store usage is not confined to the vendor who created the store! Ranking applies instead !
	/**
	 * @return array(uid)
	 */
	public function getPrincipalStoreBySF($principalId, $specialFieldId, $specialFieldValue, $vendorUId="") {
          if (trim($specialFieldValue)=="") {
                  return array();
          }

          // not necessary to join on type=S because fieldvalueId should only be supplied for correct entityType, trying to keep joins to minimum
          $sql="select   psm.uid, owned_by, vendor_created_by_uid, psm.depot_uid, psm.no_vat
                          from   principal_store_master psm,
                                     special_field_details sfd
                          where  psm.principal_uid = '{$principalId}'
                          and    psm.uid = sfd.entity_uid
                          and    sfd.field_uid = '{$specialFieldId}'
                          and    sfd.value = '".mysqli_real_escape_string($this->dbConn->connection, $specialFieldValue)."'
                          -- and    (owned_by is null or owned_by = '{$vendorUId}') -- changed to rely on the order by clause
                          order  by if(psm.status='D',2,1),
                                            if(psm.owned_by is null,1,
                                                   if(psm.owned_by = '{$vendorUId}',2,
                                                          if(psm.owned_by = '".V_UNKNOWN_VENDOR."',2,3)
                                                    )
                                                  )";

          return $this->dbConn->dbGetAll($sql);

	}


	// get store uid by special field, *** could be multiple returned ***
	// NOTE : IT IS A SYSTEM STANDARD THAT store usage is not confined to the vendor who created the store! Ranking applies instead !
	/**
	 * @return array(uid)
	 */
	public function getPrincipalStoreBySFName($principalId, $specialFieldName, $specialFieldValue, $vendorUId="") {
          if (trim($specialFieldValue)=="") {
                  return array();
          }

          $sql="select   psm.uid, owned_by, vendor_created_by_uid
                          from   principal_store_master psm,
                                           special_field_fields sff,
                                           special_field_details sfd
                          where  psm.principal_uid = '{$principalId}'
                          and    psm.principal_uid = sff.principal_uid
                          and    psm.uid = sfd.entity_uid
                          and    sff.type = 'S'
                          and    sff.name = '{$specialFieldName}'
                          and    sff.uid = sfd.field_uid
                          and    sfd.value = '".mysqli_real_escape_string($this->dbConn->connection, $specialFieldValue)."'
                          -- and    (owned_by is null or owned_by = '{$vendorUId}') -- changed to rely on the order by clause
                          order  by if(psm.status='D',2,1),
                                            if(psm.owned_by is null,1,
                                                   if(psm.owned_by = '{$vendorUId}',2,
                                                          if(psm.owned_by = '".V_UNKNOWN_VENDOR."',2,3)
                                                    )
                                                  )";

          return $this->dbConn->dbGetAll($sql);
	}

	// Warning : this returns stores for all depots, not just the depot that the user has access to !
	public function getPrincipalStoreParentAssociations($principalId, $psmChildUId) {

            $sql="select  a.psm_parent_uid, a.uid, b.deliver_name, b.deliver_add1, b.deliver_add2, b.deliver_add3, c.name delivery_day, d.description area_description,
                                                              b.depot_uid, e.name depot_name
                                    from   principal_store_association a,
                                                             principal_store_master b
                                                                            left join day c on c.uid = b.delivery_day_uid
                                                                            left join area d on d.uid = b.area_uid
                                                                            left join depot e on e.uid = b.depot_uid
                                    where  a.principal_uid = '{$principalId}'
                                    and    psm_child_uid = '{$psmChildUId}'
                                    and    a.psm_parent_uid = b.uid";

            return $this->dbConn->dbGetAll($sql);
	}

	// psmUId ~ the principal store uid that you want to compare against
	public function getPrincipalStoreSuggestedLinks($principalId, $psmUId) {

            $sql="select  b.uid principal_store_uid, b.deliver_name, b.deliver_add1, b.deliver_add2, b.deliver_add3, d.description area_description,
                                                              b.depot_uid, e.name depot_name
                                    from   principal_store_master a, -- the principal's store you want to compare against
                                                             principal_store_master b  -- the depot's store list
                                                                            left join area d on d.uid = b.area_uid
                                                                            left join depot e on e.uid = b.depot_uid
                                    where  a.uid = '{$psmUId}'
                                    and    b.principal_uid = '{$principalId}'
                                    and    (
                                                                            (b.branch_code = a.branch_code and ifnull(b.branch_code,'') != '') or
                                                                            (b.vat_number = a.vat_number and ifnull(b.vat_number,'') != '') or
                                                                            (b.stripped_deliver_name = a.stripped_deliver_name) or
                                                                            (b.deliver_add1 = a.deliver_add1 and ifnull(b.deliver_add1,'') != '') or
                                                                            (b.old_account = a.old_account)
                                                                    )";

            return $this->dbConn->dbGetAll($sql);
	}

	public function getPrincipalAreas($principalId) {

            $sql="select  uid, description
                                    from 		area
                                    where   principal_uid = '{$principalId}'
                                    order by description ";

            return $this->dbConn->dbGetAll($sql);
	}

	public function getPrincipalAreaItem($uId) {

            $sql="select  uid, description
                                    from 		area
                                    where   uid = '{$uId}'";

            return $this->dbConn->dbGetAll($sql);
	}

  public function getStoreSpecialFieldValues($fieldUId,$uIdList) {

      $sql="select  b.entity_uid, b.value
            from    special_field_fields a,
                    special_field_details b
            where   a.uid = b.field_uid
            and     a.uid = '{$fieldUId}'
            and     entity_uid in ({$uIdList})
            and     `type` = 'S'";

      $this->dbConn->dbQuery($sql);

      $arr=array();
      if ($this->dbConn->dbQueryResultRows > 0) {
        while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
          $arr[$row["entity_uid"]] = $row["value"];
        }
      }

      return $arr;
  }


  public function getPrincipalSalesRepAll($principalId, $status = FLAG_STATUS_ACTIVE) {

    $sql="select
                uid, principal_uid, rep_code, first_name, surname, identity_number,
                email_addr, mobile_number, alternate_contact_number, shipto_address1,
                shipto_address2, shipto_address3, `status`, created_datetime,
                created_by_user_uid, last_update_datetime, last_update_user_uid
          from   principal_sales_representative s
          where  s.principal_uid = '{$principalId}'
            and  s.status = '{$status}'
          order by rep_code, first_name, surname";

    return $this->dbConn->dbGetAll($sql);

  }
// ********************************************************************************************************************
  public function getPrincipalArea($principalId) {

    $sql="select a.uid, a.description 
          from area a 
          where a.principal_uid = '{$principalId}' 
          and a.`status` = 'A'
          order by a.description";

    return $this->dbConn->dbGetAll($sql);

  }


// ********************************************************************************************************************



  public function getPrincipalSalesRepItem($repId, $principalId) {

    $sql="SELECT
                s.uid, principal_uid, rep_code, first_name, surname, identity_number,
                email_addr, mobile_number, alternate_contact_number, shipto_address1,
                shipto_address2, shipto_address3, `status`, created_datetime,
                created_by_user_uid, last_update_datetime, last_update_user_uid,
                u1.full_name as created_by_user_name, u2.full_name as last_update_user_name,
                s.sales_target
          FROM   principal_sales_representative s
            LEFT JOIN users u1 on s.created_by_user_uid = u1.uid
            LEFT JOIN users u2 on s.last_update_user_uid = u2.uid
          WHERE  s.uid = '{$repId}'
            AND  s.principal_uid = '{$principalId}'";

    return $this->dbConn->dbGetAll($sql);

  }


  public function getPrincipalSalesRepByCode($repCode, $principalId) {

    $sql="SELECT
                s.uid, principal_uid, rep_code, first_name, surname, identity_number,
                email_addr, mobile_number, alternate_contact_number, shipto_address1,
                shipto_address2, shipto_address3, `status`, created_datetime,
                created_by_user_uid, last_update_datetime, last_update_user_uid,
                u1.full_name as created_by_user_name, u2.full_name as last_update_user_name
          FROM  principal_sales_representative s
            LEFT JOIN users u1 on s.created_by_user_uid = u1.uid
            LEFT JOIN users u2 on s.last_update_user_uid = u2.uid
          WHERE  s.rep_code = '{$repCode}'
            AND  s.principal_uid = '{$principalId}'";

    return $this->dbConn->dbGetAll($sql);

  }

  public function getFreeStockStoreDetails($storeUId) {

    $sql="select psm.deliver_name, 
                 psm.deliver_add1, 
		             psm.deliver_add2, 
		             psm.deliver_add3, 
		             psm.bill_name, 
		             psm.bill_add1, 
		             psm.bill_add2, 
		             psm.bill_add3, 
		             psm.depot_uid AS 'Warehouse_Uid',
		             d.name AS 'Warehouse_Name',
		             psm.principal_chain_uid AS 'Chain_Uid',
		             pcm.description AS 'Chain_Name',
		             psm.branch_code,
		             psm.old_account
          from .principal_store_master psm, .depot d, .principal_chain_master pcm 
          where psm.depot_uid = d.uid
          and   psm.principal_chain_uid = pcm.uid
          and   psm.uid = " .mysqli_real_escape_string($this->dbConn->connection, $storeUId) ;

    return $this->dbConn->dbGetAll($sql);

  }
// **********************************************************************************************************************************************************************
  public function getWarehouseStoreDetails($WSWH, $WSUID, $WSNAME ,$GLN, $BRANCH, $WAREA, $WSSTATUS) {
  	
      if(trim(mysqli_real_escape_string($this->dbConn->connection, $WSUID)) <> '0') {
          $sqwsUid = "AND   wsm.uid LIKE '%" .mysqli_real_escape_string($this->dbConn->connection, $WSUID) . "%'" ; 
      } else {
          $sqwsUid = '';
      }

      if(trim(mysqli_real_escape_string($this->dbConn->connection, $WSNAME)) <> '0') {
          $sqWsName = "AND   wsm.del_point_name LIKE '%" .mysqli_real_escape_string($this->dbConn->connection, $WSNAME) . "%'" ;   
      } else {
          $sqWsName = '';
      }

      if(trim(mysqli_real_escape_string($this->dbConn->connection, $GLN)) <> '0') {
          $sqGln = "AND   wsm.gln LIKE '%" .mysqli_real_escape_string($this->dbConn->connection, $GLN) . "%'" ;   
      } else {
          $sqGln = '';
      }

      if(trim(mysqli_real_escape_string($this->dbConn->connection, $BRANCH)) <> '0') {
          $sqBranch = "AND   wsm.branch LIKE '%" .mysqli_real_escape_string($this->dbConn->connection, $BRANCH) . "%'" ;   
      } else {
          $sqBranch = '';
      }

      if(trim(mysqli_real_escape_string($this->dbConn->connection, $WAREA)) <> '0') {
          $sqWsArea = "AND   wa.wh_area LIKE '% " .mysqli_real_escape_string($this->dbConn->connection, $WAREA) . "%'" ; 
      } else {
          $sqWsArea = '';
      }
      if(trim(mysqli_real_escape_string($this->dbConn->connection, $WSSTATUS)) == 'A') {
          $sqWsStatus = "AND   wsm.`status` = '" .mysqli_real_escape_string($this->dbConn->connection, $WSSTATUS) . "' ";
      } elseif(trim(mysqli_real_escape_string($this->dbConn->connection, $WSSTATUS)) == '0') {
          $sqWsStatus = "AND   wsm.`status` = '0' ";
      } else {
          $sqWsStatus = "AND   wsm.`status` = 'D' ";
      }      
      
  	  $sql="SELECT wsm.uid AS 'wsmUid',
                   wsm.del_point_name,
                   wsm.gln,
                   wsm.branch,
                   wsm.ndd,
                   wsm.delivery_area,
                   wa.wh_area,
                   d.name AS 'DAY',
                   wsm.`status` as 'AD'
  	             
            FROM .warehouse_store_master wsm
            LEFT JOIN .warehouse_area wa ON wa.uid = wsm.delivery_area
            LEFT JOIN .day d ON d.uid = wsm.ndd
            WHERE wsm.depot_uid LIKE '%" . mysqli_real_escape_string($this->dbConn->connection, $WSWH) . "%'
            " .  $sqwsUid    . " 
            " .  $sqWsName   . " 
            " .  $sqGln      . " 
            " .  $sqWsArea   . "
            " .  $sqBranch   . "
            " .  $sqWsStatus . "
            ORDER BY wsm.del_point_name
            LIMIT 20 ;";

    return $this->dbConn->dbGetAll($sql);

  }
// **********************************************************************************************************************************************************************

}

?>
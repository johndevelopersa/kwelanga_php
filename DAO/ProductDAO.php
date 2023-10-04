<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class ProductDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

	public function getPrincipalProductCategoryArray($principalId, $status, $arrayIndex=false, $pcUIDList=false) {
		if (($pcUIDList!==false) && (trim($pcUIDList)=="")) {
			return array();
		}
		$sql="SELECT uid,
				description,
				status
			  FROM `principal_product_category`
			  WHERE
			  `principal_uid` = ".mysqli_real_escape_string($this->dbConn->connection, $principalId).
			  (($pcUIDList!==false)?" AND uid in (".mysqli_real_escape_string($this->dbConn->connection, $pcUIDList).") ":"").
		      " AND `status` = '".mysqli_real_escape_string($this->dbConn->connection, $status)."'";

	    $sql.=" ORDER BY `description`";

		$this->dbConn->dbQuery($sql);

		$arr = array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			    if ($arrayIndex===false) {
					$arr[] = $row;
				} else {
					$arr[$row[$arrayIndex]] = $row;
				}
			}
		}
		return $arr;
	}

	public function getProductCategoryItem($procat_id) {
		$sql="SELECT uid, description, status
			  FROM `principal_product_category`
			  WHERE uid = '".mysqli_real_escape_string($this->dbConn->connection, $procat_id)."'";

		$this->dbConn->dbQuery($sql);

		$arr = array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			    $arr[] = $row;
			}
		}
		return $arr;
	}

	public function getProductCategoryByPrincipleId($principleId) {
		$sql="SELECT uid, description, status
			  FROM `principal_product_category`
			  WHERE principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principleId)."'";

		$this->dbConn->dbQuery($sql);

		$arr = array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			    $arr[] = $row;
			}
		}
		return $arr;
	}

	public function getPrincipalProductsArray($principalId, $arrayIndex="") {
		// if came from a screen list, check to see if you need to include deleted
		if (isset($_POST["PAGETYPE"])) $pageType=$_POST["PAGETYPE"]; else if (isset($_GET["PAGETYPE"])) $pageType=$_GET["PAGETYPE"]; else $pageType="A";

                $sortBySQL = (isset($_SESSION["up_pSortBy"]) && $_SESSION["up_pSortBy"]=='C')?('product_code'):('product_description');


		$sql="SELECT uid, principal_uid, product_code, product_description, ean_code, weight, vat_rate, major_category, minor_category, status, product_string,
						alt_code
			  FROM principal_product a
	          WHERE principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
	          AND status in (".(($pageType==FLAG_STATUS_DELETED)?"'D'":"'A','S'").")
	          ORDER BY {$sortBySQL}";

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

	public function getUserPrincipalProductsArray($principalId, $userId, $allProducts = false, $showOnlyProductsInTT = false, $arrayIndex=false, $productUIDList=false) {

        // if came from a screen list, check to see if you need to include deleted
        if (isset($_POST["PAGETYPE"])) $pageType=$_POST["PAGETYPE"]; else if (isset($_GET["PAGETYPE"])) $pageType=$_GET["PAGETYPE"]; else $pageType="A";
        if (($productUIDList!==false) && (trim($productUIDList)=="")) {
                return array();
        }

    	$administrationDAO = new AdministrationDAO($this->dbConn);
    	$hasRole = $administrationDAO->hasRole($userId, $principalId, ROLE_BYPASS_USER_PRODUCT_RESTRICTION);

        //product sorting.
        $sortBySQL = (isset($_SESSION["up_pSortBy"]) && $_SESSION["up_pSortBy"]=='C')?('product_code'):('product_description');

    	// lift the user restriction if has role
    	if ($hasRole) $where=""; else $where=" AND b.uid is not null ";

		$sql="SELECT a.uid, 
                 a.principal_uid, 
		             a.product_code, 
		             a.product_description, 
		             a.ean_code, 
		             a.weight, 
		             a.vat_rate, 
                 a.major_category,
                 a.minor_category, 
                 a.status, 
                 a.product_string, 
                 a.enforce_pallet_consignment, 
                 a.units_per_pallet, 
                 alt_code, 
                 a.items_per_case,
                 a.allow_decimal,
                 c.description as product_category,
                 GROUP_CONCAT(IFNULL(pmct.lable,'')  order by pmct.`order` separator ';' ) as minor_category_lables_list,
                 GROUP_CONCAT(IFNULL(pmc.value,'') order by pmct.`order` separator ';' ) as minor_category_list
          FROM principal_product a
          LEFT JOIN user_principal_product b ON a.uid = b.principal_product_uid and b.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
          LEFT JOIN principal_product_category c ON a.major_category = c.uid AND c.status = 'A'
          LEFT JOIN principal_product_minor_category mc ON a.uid = mc.principal_product_uid
          LEFT JOIN product_minor_category pmc ON mc.product_minor_category_uid = pmc.uid
          LEFT JOIN product_minor_category_type pmct ON pmc.minor_category_type_uid = pmct.uid
          WHERE a.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'".
		                            	(($productUIDList!==false)?" AND a.uid in ({$productUIDList}) ":"").
                                  (($showOnlyProductsInTT===true)?(" AND exists (select 1 from document_detail dd where dd.product_uid = a.uid ) "):("")).
          (($allProducts===true)?(""):("AND a.status in (".(($pageType==FLAG_STATUS_DELETED)?"'D'":"'A','S'").")")).
          $where."
          GROUP BY a.uid
          ORDER BY {$sortBySQL}";

          $this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ($arrayIndex===false) {
					$arr[] = $row;
				} else {
					$arr[$row[$arrayIndex]] = $row;
				}
			}
		}

		return $arr;
	}

	public function getPrincipalProductItem($principalId, $principalProductId) {
		$sql="SELECT uid, 
		             principal_uid, 
		             product_code,
		             alt_code, 
		             product_description, 
		             ean_code, 
		             weight, 
		             vat_rate, 
		             major_category, 
		             minor_category, 
		             status, 
		             product_string,
		             enforce_pallet_consignment, 
		             units_per_pallet, 
		             alt_code, 
		             outer_casing_gtin, 
		             items_per_case, 
		             vat_excl_authorised_by, 
		             packing
			  FROM principal_product
	          WHERE principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalId)."
	          AND   uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalProductId)."'
	          ORDER BY product_description";
	          
		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getPrincipalProductByCode($principalUId, $productCode) {
		$sql="select uid, 
		             product_code, 
		             product_description,
		             items_per_case,
                     convert_cases_to_units
			  from   principal_product
			  where  principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
			  and    product_code='".mysqli_real_escape_string($this->dbConn->connection, $productCode)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// for Checkers orders some principals determine the principal in their group by seeing which principal has the product loaded
	// GTIN can be an array or a single string val
  // *************************************************************************************************************************************************************
	public function getPrincipalProductByICGTIN($principalIdList, $GTIN) {

	  if (is_array($GTIN)) $where = " AND   b.sku_gtin IN ('".implode("','",mysqli_real_escape_string($this->dbConn->connection, $GTIN))."')";
	  else $where = " AND   b.sku_gtin = '".mysqli_real_escape_string($this->dbConn->connection, $GTIN)."'";

	  $sql="SELECT a.uid, 
                 a.principal_uid, 
                 product_code, 
                 product_description, 
                 weight, 
                 vat_rate, 
                 major_category, 
                 minor_category, 
                 status, 
                 product_string,
                 enforce_pallet_consignment, 
                 units_per_pallet, 
                 alt_code, 
                 b.sku_gtin, 
                 b.outercasing_gtin, 
                 b.depot_uid, 
                 a.items_per_case,
                 a.convert_cases_to_units
          FROM   principal_product a,
                 principal_product_depot_gtin b
          WHERE a.principal_uid IN (".mysqli_real_escape_string($this->dbConn->connection, $principalIdList).")
                {$where}
          AND   b.sku_gtin != '' -- to protect against blank parameters
          AND   a.uid = b.principal_product_uid";

	  $this->dbConn->dbQuery($sql);

	  $arr=array();
	  if ($this->dbConn->dbQueryResultRows > 0) {
	    while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
	      $arr[] = $row;
	    }
	  }

	  return $arr;
	}
   // *************************************************************************************************************************************************************
	public function getPrincipalProductBySKGTIN($principalIdList, $GTIN) {

	  if (is_array($GTIN)) $where = " AND   b.shrink_gtin IN ('".implode("','",mysqli_real_escape_string($this->dbConn->connection, $GTIN))."')";
	  else $where = " AND   b.shrink_gtin = '".mysqli_real_escape_string($this->dbConn->connection, $GTIN)."'";

	  $sql="SELECT a.uid, 
                 a.principal_uid, 
                 product_code, 
                 product_description, 
                 weight, 
                 vat_rate, 
                 major_category, 
                 minor_category, 
                 status, 
                 product_string,
                 enforce_pallet_consignment, 
                 units_per_pallet, 
                 alt_code, 
                 b.sku_gtin, 
                 b.outercasing_gtin,
                 b.shrink_gtin, 
                 b.depot_uid, 
                 a.items_per_case,
                 a.convert_cases_to_units
          FROM   principal_product a,
                 principal_product_depot_gtin b
          WHERE a.principal_uid IN (".mysqli_real_escape_string($this->dbConn->connection, $principalIdList).")
                {$where}
          AND   b.shrink_gtin != '' -- to protect against blank parameters
          AND   a.uid = b.principal_product_uid";

	  $this->dbConn->dbQuery($sql);

	  $arr=array();
	  if ($this->dbConn->dbQueryResultRows > 0) {
	    while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
	      $arr[] = $row;
	    }
	  }

	  return $arr;
	}
  // *************************************************************************************************************************************************************

	// FOR THE moment this does not join on depot, but will need to in future !!
	public function getPrincipalProductByOCGTIN($principalIdList, $GTIN) {
	  // for PnP orders some principals determine the principal in their group by seeing which principal has the product loaded
	  // GTIN can be an array or a single string val

	  if (is_array($GTIN)) $where = " AND   b.outercasing_gtin IN ('".implode("','",$GTIN)."')";
	  else $where = " AND   b.outercasing_gtin = '".mysqli_real_escape_string($this->dbConn->connection, $GTIN)."'";


		$sql="SELECT a.uid, 
                 a.principal_uid, 
                 product_code, 
                 product_description, 
                 weight, 
                 vat_rate, 
                 major_category, 
                 minor_category, 
                 status, 
                 product_string,
                 enforce_pallet_consignment, 
                 units_per_pallet, 
                 alt_code, 
                 b.sku_gtin, 
                 b.outercasing_gtin, 
                 b.depot_uid, 
                 a.items_per_case,
                 a.convert_cases_to_units
         FROM principal_product a,
              principal_product_depot_gtin b
         WHERE a.principal_uid IN (".mysqli_real_escape_string($this->dbConn->connection, $principalIdList).")
               {$where}
         AND   b.outercasing_gtin != '' -- to protect against blank parameters
         AND   a.uid = b.principal_product_uid";
      
		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}


	public function getUserPrincipalProductItem($principalId, $principalProductId, $userId) {
    	$where="";
		// lift the user restriction if has role
		if ($userId!=SESSION_ADMIN_USERID) {
			$administrationDAO = new AdministrationDAO($this->dbConn);
	    	$hasRole = $administrationDAO->hasRole($userId, $principalId, ROLE_BYPASS_USER_PRODUCT_RESTRICTION);
			if ($hasRole) $where=""; else $where=" AND b.uid is not null ";
		}

		$sql="SELECT a.uid, 
                 a.principal_uid, 
                 a.product_code, 
                 a.product_description, 
                 a.weight, 
                 a.vat_rate, 
                 a.major_category,
                 a.minor_category, 
                 a.status, 
                 a.product_string, 
                 enforce_pallet_consignment, 
                 units_per_pallet, 
                 alt_code, 
                 outer_casing_gtin,
                 a.items_per_case, 
                 unit_value, 
                 size_type, 
                 size_width, 
                 size_length, 
                 size_height, 
                 GROUP_CONCAT(IFNULL(c.depot_uid,'') 
                              ORDER BY c.uid) AS gtin_depot_uid_list,
                 GROUP_CONCAT(c.sku_gtin ORDER BY c.uid) AS sku_gtin_list, GROUP_CONCAT(c.outercasing_gtin ORDER BY c.uid) AS outer_casing_gtin_list,
                 a.vat_excl_authorised_by,
                 a.packing, 
                 a.non_stock_item,
                 a.web_capture,
                 a.load_to_shopify,
                 a.no_discount,
                 a.allow_decimal
          FROM 	 principal_product  a
          LEFT JOIN user_principal_product b ON a.uid = b.principal_product_uid and b.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
          LEFT JOIN principal_product_depot_gtin c ON a.uid = c.principal_product_uid
          WHERE principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalId)."
          AND   a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalProductId)."' ".
          $where." GROUP BY a.uid
          ORDER BY product_description";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}


	public function getDealType($arrayType) {
		$sql="Select uid, description, unit
			  from deal_type";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if($arrayType=="uid") $arr[$row['uid']] = $row;
				else $arr[] = $row;
			}
		}

		return $arr;
	}

	public function getDealTypeItem($uid) {
		$sql="Select uid, description, unit
			  from   deal_type
			  where uid = '".mysqli_real_escape_string($this->dbConn->connection, $uid)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getPrincipalPricingDeals($principalId,$daysPast) {
		$this->dbConn->dbQuery("SET time_zone='+2:00'"); // for the moment set to SA time

		$sql="Select a.uid, a.chain_store, a.customer_type_uid, g.description customer_type, a.deal_type_uid AS dtuid, b.description AS dealtype_description, a.user_uid, c.full_name, if (a.price_type_uid='".PRT_PRODUCT."',d.product_description,ppc.description) product_description,
						if (a.price_type_uid='".PRT_PRODUCT."',d.product_code,'') product_code, a.active,  a.capture_date, a.excl_incl, a.status_uid, e.description AS StatusDescrip, e.status_code,  a.start_date, a.end_date, a.discount_value,
						a.list_price, c.username, chain_store, a.price_type_uid, f.description price_type,
						if(a.end_date<date(now()),'<',if((a.end_date>=date(now()) and (a.start_date<=date(now()))),'=','>')) scope,
						if(h.deliver_name!='',h.deliver_name,i.description) entity_description, a.deleted, a.principal_product_uid, a.reference,
						i.status pcm_status
				FROM pricing a
					Left Join deal_type b ON a.deal_type_uid = b.uid
					Left Join users c ON a.user_uid = c.UID
					Left Join principal_product d ON a.principal_product_uid = d.uid
					Left Join principal_product_category ppc ON a.principal_product_uid = ppc.uid
					Left Join `status` e ON a.status_uid = e.uid
					Left Join price_type f ON a.price_type_uid = f.uid
					Left Join customer_type g ON a.customer_type_uid = g.uid
					Left Join principal_store_master h ON a.chain_store = h.uid and a.customer_type_uid = 2
					Left Join principal_chain_master i ON a.chain_store = i.uid and a.customer_type_uid = 1
				WHERE end_date > DATE_SUB(CURDATE(),INTERVAL ".$daysPast." DAY)
				AND   a.principal_uid=".mysqli_real_escape_string($this->dbConn->connection, $principalId)."
				ORDER BY scope, start_date DESC, end_date ASC";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ( (($row['customer_type_uid']==CT_CHAIN) && ($row['pcm_status']==FLAG_STATUS_ACTIVE)) || ($row['customer_type_uid']==CT_STORE)) $arr[] = $row;
			}
		}

		return $arr;
	}

	// same as above, but restricted to user permissions.
	// NOTE: The depot Join is excluded on user_principal_depot because it depot is not in chains table
	public function getUserPrincipalPricingDeals($userId,$principalId,$daysPast, $active, $expired, $forthcoming,$chainList,$productList,$storeList,$productGroups='') {
		$this->dbConn->dbQuery("SET time_zone='+2:00'"); // for the moment set to SA time

		$where="";
		// lift the user restriction if has role
		if ($userId!=SESSION_ADMIN_USERID) {
			$administrationDAO = new AdministrationDAO($this->dbConn);
	    	$hasRole = $administrationDAO->hasRole($userId, $principalId, ROLE_BYPASS_USER_PRODUCT_RESTRICTION);
			if ($hasRole) $where=""; else $where=" AND (user_principal_product.uid is not null or a.price_type_uid='".PRT_PRODUCT_GROUP."')";
		}

		$dates="";
		if ($active) {
			$dates = " (date(now()) between a.start_date and a.end_date) ";
		}
		if ($expired) {
			if ($dates=="") $dates = " (a.end_date<date(now())) ";
			else $dates.= " or (a.end_date<date(now())) ";
		}
		if ($forthcoming) {
			if ($dates=="") $dates = " (a.start_date>date(now())) ";
			else $dates.= " or (a.start_date>date(now())) ";
		}
		if ($dates!="") $dates=" and (".$dates.")";

		$where2="";
		if ($chainList!="") {
			$where2.= " and (i.uid in ({$chainList}) or a.customer_type_uid!=".CT_CHAIN.") ";
		} else $where2.= " and (a.customer_type_uid!=".CT_CHAIN.") ";

		if ($productList!="") {
			$where2.= " and ((d.uid in ({$productList}) and a.price_type_uid=".PRT_PRODUCT.") or (a.price_type_uid!=".PRT_PRODUCT.")) ";
		} else $where2.= " and (a.price_type_uid!=".PRT_PRODUCT.") ";

		if ($storeList!="") {
			$where2.= " and (h.uid in ({$storeList}) or a.customer_type_uid!=".CT_STORE.") ";
		} else $where2.= " and (a.customer_type_uid!=".CT_STORE.") ";

		if ($productGroups!="") {
		  $where2.=  ' and (a.principal_product_uid in ('.$productGroups.') or a.price_type_uid!='.PRT_PRODUCT_GROUP.') ';
		} else {
		  $where2.=  ' and (a.price_type_uid!='.PRT_PRODUCT_GROUP.') ';
		}


		/*
		 * NB ! Remember that this select lists prices at the level they were entered at, not on any derived values.
		 * 		For example, if you wish to show chain prices, then prices per chain directly, and not per store-chain
		 */

		// at the moment there is no user permissions check on product.
		$sql="Select a.uid, a.chain_store, a.customer_type_uid, g.description customer_type, a.deal_type_uid AS dtuid, b.description AS dealtype_description, a.user_uid, c.full_name, if (a.price_type_uid='".PRT_PRODUCT."',d.product_description,ppc.description) product_description,
					if (a.price_type_uid='".PRT_PRODUCT."',d.product_code,'') product_code, a.active,  a.capture_date, a.excl_incl, a.status_uid, e.description AS StatusDescrip, e.status_code,  a.start_date, a.end_date, a.discount_value,
					a.list_price, c.username, chain_store, a.price_type_uid, f.description price_type,
					if(a.end_date<date(now()),'<',if((a.end_date>=date(now()) and (a.start_date<=date(now()))),'=','>')) scope,
					if(h.deliver_name!='',h.deliver_name,i.description) entity_description, a.deleted, a.principal_product_uid, a.reference,
					i.status pcm_status
				FROM pricing a
					INNER JOIN (select distinct principal_id from user_principal_depot where user_principal_depot.principal_id = ".mysqli_real_escape_string($this->dbConn->connection, $principalId)." and user_principal_depot.user_id=".mysqli_real_escape_string($this->dbConn->connection, $userId).") user_principal_depot
							ON user_principal_depot.principal_id = a.principal_uid
					Left Join user_principal_store ON user_principal_store.principal_store_uid = a.chain_store AND user_principal_store.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)." AND a.customer_type_uid = ".CT_STORE."
					left join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid={$principalId})
					Left Join user_principal_chain ON user_principal_chain.principal_chain_uid = a.chain_store AND user_principal_chain.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)." AND a.customer_type_uid = ".CT_CHAIN."
					left join user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid={$principalId})

					Left Join deal_type b ON a.deal_type_uid = b.uid
					Left Join users c ON a.user_uid = c.UID
					Left Join principal_product d ON a.principal_product_uid = d.uid and a.price_type_uid = ".PRT_PRODUCT."
							Left Join user_principal_product ON d.uid = user_principal_product.principal_product_uid and user_principal_product.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
					Left Join principal_product_category ppc ON a.principal_product_uid = ppc.uid and a.price_type_uid = ".PRT_PRODUCT_GROUP."
					Left Join `status` e ON a.status_uid = e.uid
					Left Join price_type f ON a.price_type_uid = f.uid
					Left Join customer_type g ON a.customer_type_uid = g.uid
					Left Join principal_store_master h ON a.chain_store = h.uid and a.customer_type_uid = ".CT_STORE."
					Left Join principal_chain_master i ON a.chain_store = i.uid and a.customer_type_uid = ".CT_CHAIN."
				WHERE end_date > DATE_SUB(CURDATE(),INTERVAL ".$daysPast." DAY)
				AND   a.principal_uid=".mysqli_real_escape_string($this->dbConn->connection, $principalId)."
				AND   (if(a.customer_type_uid = ".CT_CHAIN.",user_principal_chain.uid,user_principal_store.uid) is not null or
						((a.customer_type_uid = ".CT_STORE." and ur.uid is not null) or (a.customer_type_uid = ".CT_CHAIN." and urc.uid is not null)))".
				$where2 . $where . $dates . "
				ORDER BY scope, start_date DESC, end_date ASC";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ( (($row['customer_type_uid']==CT_CHAIN) && ($row['pcm_status']==FLAG_STATUS_ACTIVE)) || ($row['customer_type_uid']==CT_STORE)) $arr[] = $row;
			}
		}

		return $arr;
	}


	// do not enforce the pricing deleted flag here because the screen needs to show deleted
	public function getPrincipalPricingItem($principalId, $dealUId) {
		$this->dbConn->dbQuery("SET time_zone='+2:00'"); // for the moment set to SA time

		$sql="Select a.UID, a.chain_store, a.customer_type_uid, g.description customer_type, a.deal_type_uid AS dtuid, b.description AS dealtype_description, a.user_uid, c.full_name, if (a.price_type_uid='".PRT_PRODUCT."',d.product_description,ppc.description) product_description,
						if (a.price_type_uid='".PRT_PRODUCT."',d.product_code,'') product_code, a.active,  a.capture_date, a.excl_incl, a.status_uid, e.description AS StatusDescrip, e.status_code,  a.start_date, a.end_date, a.discount_value,
						a.list_price, c.username, chain_store, a.price_type_uid, f.description price_type,
						if(a.end_date<date(now()),'<',if((a.end_date>=date(now()) and (a.start_date<=date(now()))),'=','>')) scope,
						a.principal_product_uid, a.reference, a.deleted, i.status pcm_status
				FROM pricing a
					Left Join deal_type b ON a.deal_type_uid = b.uid
					Left Join users c ON a.user_uid = c.UID
					Left Join principal_product d ON a.principal_product_uid = d.uid
					Left Join principal_product_category ppc ON a.principal_product_uid = ppc.uid
					Left Join `status` e ON a.status_uid = e.uid
					Left Join price_type f ON a.price_type_uid = f.uid
					Left Join customer_type g ON a.customer_type_uid = g.uid
					left Join principal_chain_master i ON a.chain_store = i.uid and a.customer_type_uid = 1
				WHERE a.principal_uid=".mysqli_real_escape_string($this->dbConn->connection, $principalId)."
				AND   a.uid = ".mysqli_real_escape_string($this->dbConn->connection, $dealUId);

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ( (($row['customer_type_uid']==CT_CHAIN) && ($row['pcm_status']==FLAG_STATUS_ACTIVE)) || ($row['customer_type_uid']==CT_STORE)) $arr[] = $row;
			}
		}

		return $arr;
	}

	// do not enforce the pricing deleted flag here because the screen needs to show deleted
	// same as above, but restricted to user permissions.
	// NOTE: The depot Join is excluded on user_principal_depot because it depot is not in chains table
	public function getUserPrincipalPricingItem($userId, $principalId, $dealUId) {
		$this->dbConn->dbQuery("SET time_zone='+2:00'"); // for the moment set to SA time

		$where="";
		// lift the user restriction if has role
		if ($userId!=SESSION_ADMIN_USERID) {
			$administrationDAO = new AdministrationDAO($this->dbConn);
	    	$hasRole = $administrationDAO->hasRole($userId, $principalId, ROLE_BYPASS_USER_PRODUCT_RESTRICTION);
			if ($hasRole) $where=""; else $where=" AND (user_principal_product.uid is not null or a.price_type_uid='".PRT_PRODUCT_GROUP."') ";
		}

		$sql="Select a.UID, a.chain_store, a.customer_type_uid, g.description customer_type, a.deal_type_uid AS dtuid, b.description AS dealtype_description, a.user_uid, c.full_name, if (a.price_type_uid='".PRT_PRODUCT."',d.product_description,ppc.description) product_description,
						if (a.price_type_uid='".PRT_PRODUCT."',d.product_code,'') product_code, a.active,  a.capture_date, a.excl_incl, a.status_uid, e.description AS StatusDescrip, e.status_code,  a.start_date, a.end_date, a.discount_value,
						a.list_price, c.username, chain_store, a.price_type_uid, f.description price_type,
						if(a.end_date<date(now()),'<',if((a.end_date>=date(now()) and (a.start_date<=date(now()))),'=','>')) scope,
						a.principal_product_uid, a.reference, a.deleted, i.status pcm_status
				FROM pricing a
					INNER JOIN (select distinct principal_id from user_principal_depot where user_principal_depot.principal_id = ".mysqli_real_escape_string($this->dbConn->connection, $principalId)." and user_principal_depot.user_id=".mysqli_real_escape_string($this->dbConn->connection, $userId).") user_principal_depot
							ON user_principal_depot.principal_id = a.principal_uid
					Left Join user_principal_store ON user_principal_store.principal_store_uid = a.chain_store AND user_principal_store.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)." AND a.customer_type_uid = ".CT_STORE."
					left join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid={$principalId})
    				Left Join user_principal_chain ON user_principal_chain.principal_chain_uid = a.chain_store AND user_principal_chain.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)." AND a.customer_type_uid = ".CT_CHAIN."
					left join user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid={$principalId})

					Left Join deal_type b ON a.deal_type_uid = b.uid
					Left Join users c ON a.user_uid = c.UID
					Left Join principal_product d ON a.principal_product_uid = d.uid
						Left Join user_principal_product ON d.uid = user_principal_product.principal_product_uid and user_principal_product.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
					Left Join principal_product_category ppc ON a.principal_product_uid = ppc.uid
					Left Join `status` e ON a.status_uid = e.uid
					Left Join price_type f ON a.price_type_uid = f.uid
					Left Join customer_type g ON a.customer_type_uid = g.uid
					left Join principal_chain_master i ON a.chain_store = i.uid and a.customer_type_uid = 1
				WHERE a.principal_uid=".mysqli_real_escape_string($this->dbConn->connection, $principalId)."
				AND   (if(a.customer_type_uid = ".CT_CHAIN.",user_principal_chain.uid,user_principal_store.uid) is not null or
					 ((a.customer_type_uid = ".CT_STORE." and ur.uid is not null) or (a.customer_type_uid = ".CT_CHAIN." and urc.uid is not null)))".
				$where."
				AND   a.uid = ".mysqli_real_escape_string($this->dbConn->connection, $dealUId);

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ( (($row['customer_type_uid']==CT_CHAIN) && ($row['pcm_status']==FLAG_STATUS_ACTIVE)) || ($row['customer_type_uid']==CT_STORE)) $arr[] = $row;
			}
		}

		return $arr;
	}

	// this function adds the price calculation according to discount price type to a row
	public function addPricingFieldsToActivePrice (&$activePriceRow) {
    
		// add the price calculation fields
		$price = -1;
		$price_type = $activePriceRow['DealType'];
		$discountValue = -1;
		switch( $activePriceRow['deal_type_uid'] ) {
			case VAL_DEALTYPE_NETT_PRICE:
				$price		 = number_format( $activePriceRow['list_price'], 2, '.', '' );
				$discountValue = 0;
				break;
			case VAL_DEALTYPE_PERCENTAGE:
				$price		 = number_format( $activePriceRow['list_price'] - ( $activePriceRow['list_price'] * ( $activePriceRow['discount_value'] / 100 ) ), 2, '.', '' );
				$discountValue = $activePriceRow['list_price'] * ( $activePriceRow['discount_value'] / 100 );
				break;
			case VAL_DEALTYPE_AMOUNT_OFF:
				$price		 = number_format( $activePriceRow['list_price'] - $activePriceRow['discount_value'], 2, '.', '' );
				$discountValue = $activePriceRow['discount_value'];
				break;
			default:
				break;
		}

		if (($price<0) || ($discountValue<0) || ($price==-1) || (!is_numeric($price))) {
			$activePriceRow['price']=-1;
			$activePriceRow['price_type']="";
			trigger_error("Error occurred in getActivePricesForProduct getting Price: negative price occurred",E_USER_ERROR);
		} else {
			$activePriceRow['price']=$price;
			$activePriceRow['price_type']=$price_type;
			// swop around the values so as to conform to discount_value naming convention
			$activePriceRow['deal_type_value'] = $activePriceRow['discount_value'];
			$activePriceRow['discount_value'] = $discountValue;
		  }

		return true;

	}


	public function getStorePrice($principalUId, $storeUId, $principalProductUId) {
		global $ROOT; global $PHPFOLDER;

		/*
		 * You might in future want to control time zone for now() and curdate() by setting the timezone to be that of the user.
		 * For the moment, South Africa is seen as the controlling time.
		 */
		$this->dbConn->dbQuery("SET time_zone='+2:00'"); // for the moment set to SA time

		$sql="SELECT pricing.*, deal_type.description AS DealType,
					 concat(if(pricing.price_type_uid='".PRT_PRODUCT."','Product','Product Group'),' @ Store Level') pricing_level,
					 'Selected Store' description_level,pp.items_per_case 
		     FROM pricing
    		         LEFT Join deal_type ON deal_type.uid = pricing.deal_type_uid
					 LEFT Join principal_product pp ON pricing.principal_product_uid=pp.major_category and
													   pricing.price_type_uid='".PRT_PRODUCT_GROUP."' and
													   pricing.principal_uid=pp.principal_uid and
													   pp.uid=".mysqli_real_escape_string($this->dbConn->connection, $principalProductUId)."
		       LEFT Join principal_product pI on pI.uid= ".mysqli_real_escape_string($this->dbConn->connection, $principalProductUId)."	
		     WHERE (
					(pricing.price_type_uid='".PRT_PRODUCT."' and pricing.principal_product_uid=".mysqli_real_escape_string($this->dbConn->connection, $principalProductUId).") or
					(pp.uid is not null and pricing.price_type_uid='".PRT_PRODUCT_GROUP."')
				   )
		     AND   pricing.chain_store=".mysqli_real_escape_string($this->dbConn->connection, $storeUId)."
		     AND   pricing.principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."
		     AND   pricing.customer_type_uid = '".CT_STORE."'
		     AND   pricing.deleted = 0
		     AND   CURDATE() between pricing.start_date and pricing.end_date
			 AND   exists (select 1 from principal_store_master j
											left join principal_chain_master i on j.principal_chain_uid=i.uid
							where (i.status = 'A' or j.principal_chain_uid is null)
							and pricing.chain_store = j.uid)
		     ORDER BY if(pricing.price_type_uid='".PRT_PRODUCT."',1,2) DESC,
		                 start_date DESC, 
		                 end_date ASC, 
		                 pricing.list_price ASC";

		$this->dbConn->dbQuery($sql);
		if (!$this->dbConn->dbQueryResult) trigger_error("Error occurred in getStorePrice getting Price",E_USER_ERROR);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$this->addPricingFieldsToActivePrice($row); // modifies parameter
			$arr[] = $row;
		}

		return $arr;

	}
  
  public function getDiscountValue($principalUId, $storeUId, $principalProductUId)
  {
    $sql="select p.discount_value as 'discount_value'
         from pricing p
         where p.principal_product_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalProductUId)."";
         
    $this->dbConn->dbQuery($sql);
    
    $arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[] = $row;
		}
    return $arr;
  }
  
	public function getChainPrice($principalUId, $storeUId, $principalProductUId) {
		global $ROOT; global $PHPFOLDER;

		/*
		 * You might in future want to control time zone for now() and curdate() by setting the timezone to be that of the user.
		 * For the moment, South Africa is seen as the controlling time.
		 */
		$this->dbConn->dbQuery("SET time_zone='+2:00'"); // for the moment set to SA time

		$arr=array();

		include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
		$storeDAO = new StoreDAO($this->dbConn);
		$mfPS = $storeDAO->getPrincipalStoreItem($storeUId);
		if (sizeof($mfPS)==0) {
			return $arr;
		}
		$primaryChain=((trim($mfPS[0]['principal_chain_uid'])=="")?"NULL":$mfPS[0]['principal_chain_uid']); // the OH processing allows chains to be blank for stores
		$altChain=($mfPS[0]['alt_principal_chain_uid']=="")?$primaryChain:$mfPS[0]['alt_principal_chain_uid'];
		$sql="SELECT pricing.*, deal_type.description AS DealType,
					 concat(if(pricing.price_type_uid='".PRT_PRODUCT."','Product','Product Group'),
							' @ ',
							if(pricing.chain_store={$primaryChain},'Primary','Alternate'),
							' Chain Level') pricing_level,
					    i.description description_level, pI.items_per_case 
		     FROM pricing
	   		         LEFT Join deal_type ON deal_type.uid = pricing.deal_type_uid
					 LEFT Join principal_product pp ON pricing.principal_product_uid=pp.major_category and
													   pricing.price_type_uid='".PRT_PRODUCT_GROUP."' and
													   pricing.principal_uid=pp.principal_uid and
													   pp.uid=".mysqli_real_escape_string($this->dbConn->connection, $principalProductUId)."
					 LEFT Join principal_product pI on pI.uid= ".mysqli_real_escape_string($this->dbConn->connection, $principalProductUId)."							   
					 INNER JOIN principal_chain_master i ON pricing.chain_store = i.uid and i.status = '".FLAG_STATUS_ACTIVE."'
		     WHERE (
					(pricing.price_type_uid='".PRT_PRODUCT."' and pricing.principal_product_uid=".mysqli_real_escape_string($this->dbConn->connection, $principalProductUId).") or
					(pp.uid is not null and pricing.price_type_uid='".PRT_PRODUCT_GROUP."')
				   )
		     AND   pricing.chain_store in ({$primaryChain},{$altChain})
		     AND   pricing.principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."
		     AND   pricing.customer_type_uid = '".CT_CHAIN."'
		     AND   pricing.deleted = 0
		     AND   CURDATE() between start_date and end_date
		     ORDER BY if(pricing.chain_store={$primaryChain},1,2),
					  if(pricing.price_type_uid='".PRT_PRODUCT."',1,2) DESC,
					  start_date DESC, end_date ASC, pricing.list_price ASC";
					  
//       file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/sqlP.txt', $sql, FILE_APPEND);

	    $this->dbConn->dbQuery($sql);
	    if (!$this->dbConn->dbQueryResult) trigger_error("Error occurred in getChainPrice getting Price",E_USER_ERROR);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$this->addPricingFieldsToActivePrice($row); // modifies parameter
			$arr[] = $row;
		}

		return $arr;

	}

	// NB !
	// if by product, then the default price can be product or price category level, but
	// if by product category, the default price must only be from category as category translates to multiple products, but not the other way around
	public function getGenericChainDefaultPrice($principalUId, $principalProductUId, $productUIdType) {
		/*
		 * You might in future want to control time zone for now() and curdate() by setting the timezone to be that of the user.
		 * For the moment, South Africa is seen as the controlling time.
		 */
		$this->dbConn->dbQuery("SET time_zone='+2:00'"); // for the moment set to SA time

		if ($productUIdType==PRT_PRODUCT) {
			$sql="SELECT pricing.*, deal_type.description AS DealType,
						 'Product @ Generic Chain Default Level' pricing_level,
					 i.description description_level, pI.items_per_case 
			     FROM pricing
	    		         LEFT Join deal_type ON deal_type.uid = pricing.deal_type_uid
						 LEFT Join principal_product pp ON pricing.principal_product_uid=pp.major_category and
														   pricing.price_type_uid='".PRT_PRODUCT_GROUP."' and
														   pricing.principal_uid=pp.principal_uid and
														   pp.uid=".mysqli_real_escape_string($this->dbConn->connection, $principalProductUId)."
						 LEFT Join principal_product pI on pI.uid= ".mysqli_real_escape_string($this->dbConn->connection, $principalProductUId)."	
						 INNER JOIN principal_chain_master i ON pricing.chain_store = i.uid and i.status = '".FLAG_STATUS_ACTIVE."'
			     WHERE (
						(pricing.price_type_uid='".PRT_PRODUCT."' and pricing.principal_product_uid=".mysqli_real_escape_string($this->dbConn->connection, $principalProductUId).") or
						(pp.uid is not null and pricing.price_type_uid='".PRT_PRODUCT_GROUP."')
					   )
			     AND   pricing.chain_store=(select uid from principal_chain_master pcm where pcm.old_code='".CHAIN_GENERIC_OLD_CODE."' and principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."')
			     AND   pricing.principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."
			     AND   pricing.customer_type_uid = '".CT_CHAIN."'
			     AND   pricing.deleted = 0
			     AND   CURDATE() between start_date and end_date
			     ORDER BY if(pricing.price_type_uid='".PRT_PRODUCT."',1,2),
						  start_date DESC, end_date ASC, pricing.list_price ASC";
		} else if ($productUIdType==PRT_PRODUCT_GROUP) {
			$sql="SELECT pricing.*, deal_type.description AS DealType, 'Product Group @ Generic Chain Default Level' pricing_level
			     FROM pricing
	    		         LEFT Join deal_type ON deal_type.uid = pricing.deal_type_uid
						 INNER JOIN principal_chain_master i ON pricing.chain_store = i.uid and i.status = '".FLAG_STATUS_ACTIVE."'
			     WHERE pricing.price_type_uid='".PRT_PRODUCT_GROUP."'
				 AND   pricing.principal_product_uid=".mysqli_real_escape_string($this->dbConn->connection, $principalProductUId)."
			     AND   pricing.chain_store=(select uid from principal_chain_master pcm where pcm.old_code='".CHAIN_GENERIC_OLD_CODE."' and principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."')
			     AND   pricing.principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."
			     AND   pricing.customer_type_uid = '".CT_CHAIN."'
			     AND   pricing.deleted = 0
			     AND   CURDATE() between start_date and end_date
			     ORDER BY if(pricing.price_type_uid='".PRT_PRODUCT."',1,2),
						  start_date DESC, end_date ASC, pricing.list_price ASC";
		}

	    $this->dbConn->dbQuery($sql);
		if (!$this->dbConn->dbQueryResult) trigger_error("Error occurred in getGenericChainDefaultPrice getting Generic Chain Price",E_USER_ERROR);

		$arr=array();

	    if ($this->dbConn->dbQueryResultRows > 0) {
	    	while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
	    		$this->addPricingFieldsToActivePrice($row); // modifies parameter
				$arr[] = $row;
			}
	    }

	    return $arr;

	}

	public function getCalculatedVATRate($principalUId, $storeUId, $principalProductUId, $storeDAO=false, $mfPS=false, $mfPP=false) {
	  global $ROOT; global $PHPFOLDER;

	  include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");

	  $calculatedVATRate=false;

    if ($storeDAO==false) $storeDAO = new StoreDAO($this->dbConn);
    $mfPS=$storeDAO->getPrincipalStoreItem($storeUId);
    if (empty($mfPS)) {
      return array(false,"Store VAT setting could not be retrieved in getActivePricesForProduct.");
    }
    $mfPP=$this->getPrincipalProductItem($principalUId,$principalProductUId);
    if (empty($mfPP)) {
      return array(false,"Product VAR Rate could not be retrieved in getActivePricesForProduct");
    }

    if (strval($mfPS[0]["no_vat"])=="0") {
      $calculatedVATRate = $mfPP[0]["vat_rate"]; // let the vat rate control it
    } else {
      $calculatedVATRate = "0.00";
    }

    return array($calculatedVATRate, "Successful");
	}

	// This gets only the first level of pricing that is found
	// you must pass a store uid and not a chain uid
	public function getActivePricesForProduct($principalUId, $storeUId, $principalProductUId, $returnVATSettings=false) {
		global $ROOT; global $PHPFOLDER;
		
		$calculatedVATRate=false;
		if ($returnVATSettings) {
  		 list($calculatedVATRate, $msg) = $this->getCalculatedVATRate($principalUId, $storeUId, $principalProductUId);
  		 if ($calculatedVATRate===false) {
  		   echo "Could not retrieve calculated vat rate : ".$msg;
  		   return false;
  		 }
		}

		$arr=array();

		// Remember that the calculatedVatRate is returned at root level not at [0],[1] level as it is same for all rows

		$arr=$this->getStorePrice($principalUId, $storeUId, $principalProductUId);
		if (sizeof($arr)>0) {
		 $arr["calculatedVatRate"]=$calculatedVATRate;
		 return $arr;
		}

		$arr=$this->getChainPrice($principalUId, $storeUId, $principalProductUId);
		if (sizeof($arr)>0) {
		 $arr["calculatedVatRate"]=$calculatedVATRate;
		 return $arr;
		}

		$arr=$this->getGenericChainDefaultPrice($principalUId, $principalProductUId, PRT_PRODUCT);
		if (sizeof($arr)>0) {
		 $arr["calculatedVatRate"]=$calculatedVATRate;
		 return $arr;
		}
 
		return $arr;

	}

	// this function returns Every eligible pricing level rows for a product and store
	public function getAllEligibleActivePricesForProduct($principalUId, $storeUId, $principalProductUId) {
		$arr=$this->getStorePrice($principalUId, $storeUId, $principalProductUId);
		$arr=array_merge($arr,$this->getChainPrice($principalUId, $storeUId, $principalProductUId));
		$arr=array_merge($arr,$this->getGenericChainDefaultPrice($principalUId, $principalProductUId, PRT_PRODUCT));

		return $arr;
	}

	// NB : THE NAMING CONVENTION "discount_value" implies a positive column as the FORMULA IS ALWAYS NETT_PRICE=ITEM_PRICE MINUS-DISCOUNT ! (minus). So always convert it.

	// The Item bulk discount is returned with a Total as it only returns 1 row consolidated.
	// NB: VAT is not applied !
	/**
	 * @param cases or invoiceAmount are required
	 * @param applyLevel ~ Document or Item level
	 * @param lineDealActivePrice ~ array of Active Price fields from getActivePricesForProduct, only needed if apply Level = Item
	 * @return for showAtLevel = I:
	 * 			array(true/false,
	 * 				  array("discount_value",
	 * 					    "nett_price",
	 * 						"ext_price",
	 * 						"discount_reference",
	 * 						"selectedArr[]"))
	 * @return for showAtLevel = D:
	 * 			array(true/false,
	 * 				  array("..."))
	 */
	public function getActivePricesForDocument($principalUId, $storeUId, $showAtLevel, $invoiceTotal, $invoiceCases, $lineDealActivePrice="", $cases="", $extPrice="") {
		// NOTE: applyLevel is not the pricing_document.apply_level ! It is a flow control.
		if (($showAtLevel=="I") && (!is_array($lineDealActivePrice))) {
			return array(false,"lineDealActivePrice needs to be passed if showAtLevel is Item");
		}

		// Anything processed at Item level is treated the same, except in that it can choose whether to use the item cases/price or the invoice cases/prices as the trigger
		$sqlLevel=($showAtLevel=="I")?"'".DPL_ITEM."','".DPL_DOCUMENT_ITEM."'":"'{$showAtLevel}'"; // B means document discount is processed at item level but using document totals

		// if it is passed, it must have relevant fields
		$documentLevel=true;
		if ((is_array($lineDealActivePrice)) &&
				(
					(!isset($lineDealActivePrice["deal_type_uid"])) ||
					(!isset($lineDealActivePrice["discount_value"])) ||
					(!isset($lineDealActivePrice["price"])) ||
					(!isset($lineDealActivePrice["list_price"])) ||
					(!isset($lineDealActivePrice["product_uid"])) ||
					(!isset($lineDealActivePrice["product_category"]))
				)
			) {
			return array(false,"lineDealActivePrice incorrect format");
		}

		$this->dbConn->dbQuery("SET time_zone='+2:00'"); // for the moment set to SA time

		// This sql applies the Product/Category restriction. It (Same SQL) is used for each level.
		if ($showAtLevel!="D") {
			$sqlPr =" and (ifnull(product_type,'')='' or
							(
								product_type in ('P','PC') and
								exists (select 1 from pricing_document_product pdp
										where a.uid = pdp.pricing_document_uid
										and (
												(pdp.product_entity_uid = ".$lineDealActivePrice["product_uid"]." and a.product_type = 'P' ) or
												(pdp.product_entity_uid = {$lineDealActivePrice["product_category"]} and a.product_type = 'PC' )
											)
										)
							)
						  ) ";
		} else {
			$sqlPr="";
		}


		// *************************** SPECIFIC STORE **************************
		// sort in reverse product_type order, so that when populating selectedArr it overwrites last one with the top priority last
		$sql="select a.uid, a.grouping, a.description, a.unit_price_type_uid, a.quantity, a.deal_type_uid, a.value, apply_per_unit, cumulative_type, apply_level
			  from   pricing_document a
			  where  store_chain_uid = '".mysqli_real_escape_string($this->dbConn->connection, $storeUId)."'
			  and    customer_type_uid = ".CT_STORE."
			  and    principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
			  and    status='".FLAG_STATUS_ACTIVE."'
			  and    apply_level in ({$sqlLevel})
			  and    CURDATE() between start_date and end_date".
			  $sqlPr."
			  order  by quantity ASC,
						if(product_type='P',3,
								if(ifnull(product_type,'PC')='PC',2,1)  -- see comment above for expln
						  ) ";

		$this->dbConn->dbQuery($sql);
		if (!$this->dbConn->dbQueryResult) return false;

		// setup the difference values to check against. Only used in triggering, not calculations !
		$unitPriceTypeValues[DPL_ITEM][UPT_CASES]=$cases;
		$unitPriceTypeValues[DPL_ITEM][UPT_CHARGE]=$extPrice;
		$unitPriceTypeValues[DPL_DOCUMENT_ITEM][UPT_CASES]=$invoiceCases;
		$unitPriceTypeValues[DPL_DOCUMENT_ITEM][UPT_CHARGE]=$invoiceTotal;
		$unitPriceTypeValues[DPL_DOCUMENT][UPT_CASES]=$invoiceCases;
		$unitPriceTypeValues[DPL_DOCUMENT][UPT_CHARGE]=$invoiceTotal;

		$hasOverrides=false;

		$selectedArr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)) {
			// apply the cumulative_type
			if ($showAtLevel=="I") {
				if (($row["cumulative_type"]==DPCT_NETT_PRICE) && ($lineDealActivePrice["deal_type_uid"]!=VAL_DEALTYPE_NETT_PRICE)) continue;
				else if (($row["cumulative_type"]==DPCT_DISCOUNTS_ZERO) && (floatval($lineDealActivePrice["discount_value"])!=0)) continue;
			}

			if ($row["quantity"]<=$unitPriceTypeValues[$row["apply_level"]][$row["unit_price_type_uid"]]) {
				$selectedArr[$row["grouping"]]["uid"]=$row["uid"];
				$selectedArr[$row["grouping"]]["quantity"]=$row["quantity"];
				$selectedArr[$row["grouping"]]["deal_type_uid"]=$row["deal_type_uid"];
				$selectedArr[$row["grouping"]]["value"]=$row["value"];
				$selectedArr[$row["grouping"]]["unit_price_type_uid"]=$row["unit_price_type_uid"];
				$selectedArr[$row["grouping"]]["description"]=$row["description"];
				$selectedArr[$row["grouping"]]["apply_per_unit"]=$row["apply_per_unit"];
				$selectedArr[$row["grouping"]]["apply_level"]=$row["apply_level"];
				$selectedArr[$row["grouping"]]["cumulative_type"]=$row["cumulative_type"];
				if ($row["cumulative_type"]==DPCT_DISCOUNTS_OVERRIDE) {
					$hasOverrides=true;
				}
			}
		}

		// *************************** SPECIFIC CHAIN **************************
		if ($this->dbConn->dbQueryResultRows == 0) {
			// sort in reverse product_type order, so that when populating selectedArr it overwrites last one with the top priority last
			$sql="select a.uid, a.grouping, a.description, a.unit_price_type_uid, a.quantity, a.deal_type_uid, a.value, apply_per_unit, cumulative_type,
						 apply_level, a.store_chain_uid
			  from   pricing_document a,
					 principal_store_master b
			  where  b.uid = '".mysqli_real_escape_string($this->dbConn->connection, $storeUId)."'
			  and    b.status = '".FLAG_STATUS_ACTIVE."'
			  and    a.customer_type_uid = ".CT_CHAIN."
		 	  and    a.store_chain_uid in (b.principal_chain_uid, b.alt_principal_chain_uid)
			  and    a.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
			  and    a.status='".FLAG_STATUS_ACTIVE."'
			  and    apply_level in ({$sqlLevel})
			  and    CURDATE() between start_date and end_date ".
			  $sqlPr."
			  order  by if(a.store_chain_uid=b.principal_chain_uid,1,2),
						quantity ASC,
						if(product_type='P',3,
								if(ifnull(product_type,'PC')='PC',2,1)  -- see comment above for expln
						  )";

			$this->dbConn->dbQuery($sql);
			if (!$this->dbConn->dbQueryResult) return false;

			$level=1;
			$chainHold="";
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)) {
				// apply the cumulative_type
				if ($showAtLevel=="I") {
					if (($row["cumulative_type"]==DPCT_NETT_PRICE) && ($lineDealActivePrice["deal_type_uid"]!=VAL_DEALTYPE_NETT_PRICE)) continue;
					else if (($row["cumulative_type"]==DPCT_DISCOUNTS_ZERO) && (floatval($lineDealActivePrice["discount_value"])!=0)) continue;
				}
				if (($row["quantity"]<=$unitPriceTypeValues[$row["apply_level"]][$row["unit_price_type_uid"]]) && ((!isset($selectedArr[$row["grouping"]])) || ($level==2))) {
					$level=2;
					if ($chainHold=="") $chainHold=$row["store_chain_uid"];
					else if ($chainHold!=$row["store_chain_uid"]) break; // only take the primary chain if it is present
					$selectedArr[$row["grouping"]]["uid"]=$row["uid"];
					$selectedArr[$row["grouping"]]["quantity"]=$row["quantity"];
					$selectedArr[$row["grouping"]]["deal_type_uid"]=$row["deal_type_uid"];
					$selectedArr[$row["grouping"]]["value"]=$row["value"];
					$selectedArr[$row["grouping"]]["unit_price_type_uid"]=$row["unit_price_type_uid"];
					$selectedArr[$row["grouping"]]["description"]=$row["description"];
					$selectedArr[$row["grouping"]]["apply_per_unit"]=$row["apply_per_unit"];
					$selectedArr[$row["grouping"]]["apply_level"]=$row["apply_level"];
					$selectedArr[$row["grouping"]]["cumulative_type"]=$row["cumulative_type"];
					if ($row["cumulative_type"]==DPCT_DISCOUNTS_OVERRIDE) {
						$hasOverrides=true;
					}
				}
			}
		}

		// *************************** GENERIC CHAIN **************************
		if ($this->dbConn->dbQueryResultRows == 0) {
			// sort in reverse product_type order, so that when populating selectedArr it overwrites last one with the top priority last
			$sql="select a.uid, a.grouping, a.description, a.unit_price_type_uid, a.quantity, a.deal_type_uid, a.value, apply_per_unit, cumulative_type,
						apply_level, a.store_chain_uid
			  from   pricing_document a
			  where  a.customer_type_uid = ".CT_CHAIN."
		 	  and    a.store_chain_uid = (select uid from principal_chain_master pcm where pcm.old_code='".CHAIN_GENERIC_OLD_CODE."' and principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."')
			  and    a.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
			  and    a.status='".FLAG_STATUS_ACTIVE."'
			  and    apply_level in ({$sqlLevel})
			  and    CURDATE() between start_date and end_date ".
			  $sqlPr."
			  order  by quantity ASC,
						if(product_type='P',3,
								if(ifnull(product_type,'PC')='PC',2,1)  -- see comment above for expln
						  )";

			$this->dbConn->dbQuery($sql);
			if (!$this->dbConn->dbQueryResult) return false;

			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)) {
				// apply the cumulative_type
				if ($showAtLevel=="I") {
					if (($row["cumulative_type"]==DPCT_NETT_PRICE) && ($lineDealActivePrice["deal_type_uid"]!=VAL_DEALTYPE_NETT_PRICE)) continue;
					else if (($row["cumulative_type"]==DPCT_DISCOUNTS_ZERO) && (floatval($lineDealActivePrice["discount_value"])!=0)) continue;
				}

				if (($row["quantity"]<=$unitPriceTypeValues[$row["apply_level"]][$row["unit_price_type_uid"]]) && ((!isset($selectedArr[$row["grouping"]])) || ($level==3))) {
					$level=3;
					$selectedArr[$row["grouping"]]["uid"]=$row["uid"];
					$selectedArr[$row["grouping"]]["quantity"]=$row["quantity"];
					$selectedArr[$row["grouping"]]["deal_type_uid"]=$row["deal_type_uid"];
					$selectedArr[$row["grouping"]]["value"]=$row["value"];
					$selectedArr[$row["grouping"]]["unit_price_type_uid"]=$row["unit_price_type_uid"];
					$selectedArr[$row["grouping"]]["description"]=$row["description"];
					$selectedArr[$row["grouping"]]["apply_per_unit"]=$row["apply_per_unit"];
					$selectedArr[$row["grouping"]]["apply_level"]=$row["apply_level"];
					$selectedArr[$row["grouping"]]["cumulative_type"]=$row["cumulative_type"];
					if ($row["cumulative_type"]==DPCT_DISCOUNTS_OVERRIDE) {
						$hasOverrides=true;
					}
				}
			}
		}

		// **************************** RETURNED VALUES CALCULATIONS ****************
		// BULK ITEM DISCOUNTS effectively CHANGE the LIST_PRICE and therefore the extended_price when multiplied by cases!
		$returnArr=array();
		if ($showAtLevel=="D") {
			foreach ($selectedArr as $row) {
				$returnArr[]=$row;
			}
		} else {
			// setup starting values depending on Cumulative Type
			if ($hasOverrides) {
				// ie. DPCT_DISCOUNTS_OVERRIDE, effective undo the applied discount and start again
				$returnArr["discount_value"] = 0; // discard the list price deal discounts
			} else {
				// DPCT_DISCOUNTS_ZERO, DPCT_NETT_PRICE ~ checked during building of $selectedArr
				// DPCT_DISCOUNTS_CUMULATIVE
				$returnArr["discount_value"] = $lineDealActivePrice["discount_value"]; // do not *cases because this is a starting value and the discount is per 1 list_price to which we are adding the bulk discounts (*cases)
			}
			//$initialEP=$cases*($lineDealActivePrice["list_price"]-$lineDealActivePrice["discount_value"]);
			$initialEP=$cases*($lineDealActivePrice["list_price"]-$returnArr["discount_value"]);
			$returnArr["initial_discount_value"]=$lineDealActivePrice["discount_value"]; // the item-price deal discount value
			$returnArr["selectedArr"]=array();
			foreach ($selectedArr as &$row) {
				// convert to a monetary value
				$returnVal=0;
				list($result,$returnVal)=$this->calculateBulkDiscountValue($row["deal_type_uid"], $row["value"],($row["deal_type_uid"]==VAL_DEALTYPE_PERCENTAGE)?$initialEP:$lineDealActivePrice["list_price"]);
				if ($result!==true) {
					return array(false, $returnVal); // contains description if false
				}
				$returnVal=$returnVal/$cases; // already checked for zero numeratior in calculation function above

				//Y also doubles up as directly off list price
				if ($row["apply_per_unit"]=="Y") {
					// skip if percentage as (% x LP = % x EP / cases) ... we have already divided by cases above
					if ($row["deal_type_uid"]==VAL_DEALTYPE_PERCENTAGE) {
						$returnArr["discount_value"]+=$returnVal*(-1); // bulk discounts store discounts as -ive values, but this column stores it as +ive, so reverse it
						// affix the discount value also to the row (so there will be a major and minor discount_value)
						$row["discount_value"]=$returnVal*(-1);
					} else {
						// essentially if cases, this is the same as taking the amount directly off list_price ie... if user says they want to take 10% off list_price instead of off ext_price
						// NB : Always USE DPL_ITEM for first dimension here as we want each line's contribution, not off invoice total !
						$returnArr["discount_value"]+=$unitPriceTypeValues[DPL_ITEM][$row["unit_price_type_uid"]]*($returnVal*(-1)); // bulk discounts store discounts as -ive values, but this column stores it as +ive, so reverse it
						// affix the discount value also to the row (so there will be a major and minor discount_value)
						$row["discount_value"]=$unitPriceTypeValues[DPL_ITEM][$row["unit_price_type_uid"]]*$returnVal*(-1);
					}
				} else {
					$returnArr["discount_value"]+=$returnVal*(-1); // bulk discounts store discounts as -ive values, but this column stores it as +ive, so reverse it
					// affix the discount value also to the row (so there will be a major and minor discount_value)
					$row["discount_value"]=$returnVal*(-1);
				}

				$returnArr["discount_reference"]=$row["description"];
				$returnArr["selectedArr"][]=$row; // pass the rows used in arriving at the discount value as it needs to be stored by orders capture
			}

			$returnArr["nett_price"]=$lineDealActivePrice["list_price"]-$returnArr["discount_value"]; // note that because discounts assumed to be +ive, we tfore subtract
			$returnArr["ext_price"]=$returnArr["nett_price"]*$cases;
			if (sizeof($selectedArr)==0) $returnArr["discount_reference"]="";
			//else if (sizeof($selectedArr)==1) $returnArr["discount_reference"]=""; // set in loop above
			else if (sizeof($selectedArr)>1) $returnArr["discount_reference"]="Mult.Bulk Disc"; // override description as only 1 line is shown
		}

		return array(true,$returnArr); // must return true as it is critical that calling process knows if this procedure fell over !!!

	}

	/**
	 * return the discount value, using same signage standard as the values that are stored in pricing_document ie. -ive decreases value
	 * function only to be used for Bulk discounts (Item and Document) as it ignores Nett Price
	 * NOTE ! The Bulk Discounts work off the invoice amount, and not the nett item-price (list_price-discount) !
	 *
	 * @param dealTypeUId ~ uid for Nett Price, Percentage, Amount Off etc
	 * @param pricingValue ~ the factore (% value or amount off)
	 * @param invoiceAmount ~ the calculated price (used for percentage), and NOT the Nett Item-Price !
	 * @return array(true/false, errormsg/actual calculated value)
	 */
	public function calculateBulkDiscountValue($dealTypeUId, $pricingValue, $invoiceAmount) {
		switch ($dealTypeUId) {
			case VAL_DEALTYPE_AMOUNT_OFF: {
					return array(true,(floatval($invoiceAmount)==0)?0:$pricingValue);
					break;
			 	}
			case VAL_DEALTYPE_NETT_PRICE: {
					return array(false,"NETT PRICE not currently supported. Could not calculate."); // eg. you specify a nett price to discount to exactly on the bulk discount - but why would you ?
					break;
			}
			case VAL_DEALTYPE_PERCENTAGE: {
					if (!preg_match(GUI_PHP_FLOAT_REGEX,$invoiceAmount)) return array(false,"No Invoice Amount passed. Could not calculate.");
					else return array(true,(floatval($invoiceAmount)==0)?0:$invoiceAmount*($pricingValue/100));
					break;
			 }
			default: {
				return array(false,"ERROR saving order - uncatered for document pricing (bulk) type.");
			}
		}
	}

	// VAT is calculated
	public function getFinalInvoicePricing(&$postingOrderTO, &$documentTotal, $userId, $principalId) {
		global $ROOT; global $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER."TO/PostingOrderDocumentPricingTO.php");
		include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");

		$errorTO = new ErrorTO();

		// get the store DAO if needed and its fields are not supplied for later use
		if (strval($postingOrderTO->storeNoVat)=="") {
			include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
			$storeDAO = new StoreDAO($this->dbConn);
			$mfPS=$storeDAO->getPrincipalStoreItem($postingOrderTO->storeChainUId);
			if (empty($mfPS)) {
				$errorTO->type=FLAG_ERRORTO_ERROR;
				$errorTO->description="Store VAT setting could not be retrieved.";
				return $errorTO;
			}
		}

		$i=0; // must be zero based for JS array on capture screen
		$tempDocumentTotal=0;
		$documentCases=0;
   
		if ($postingOrderTO->documentType==DT_ORDINV || $postingOrderTO->documentType==DT_WALKIN_INVOICE || $postingOrderTO->documentType==DT_SALES_ORDER) {

			$administrationDAO = new AdministrationDAO($this->dbConn);
			foreach ($postingOrderTO->detailArr as &$postingOrderDetailTO) { 
				// get the product DAO if needed and its fields are not supplied
       
				if (($postingOrderDetailTO->qtyConvertedToCases=="") ||
					(strval($postingOrderDetailTO->productVatRate)=="") ||
					(strval($postingOrderDetailTO->majorCategory)=="")
					) {
					$mfPP=$this->getPrincipalProductItem($principalId,$postingOrderDetailTO->productUId);
       
					if (empty($mfPP)) {
						$errorTO->type=FLAG_ERRORTO_ERROR;
						$errorTO->description="Product Conversion Factor/VAT Rate could not be retrieved for this product @line:".($i+1);
						$errorTO->identifier="P";
						$errorTO->identifier2=$i;
						return $errorTO;
					}
              
				}

				// lookup the product category
				if ($postingOrderDetailTO->majorCategory===false) {
					$postingOrderDetailTO->majorCategory = ((strval(trim($mfPP[0]["major_category"]))=="")?false:$mfPP[0]["major_category"]);
				}

				// lookup the vat settings
				if (strval($postingOrderDetailTO->productVatRate)=="") {
					$postingOrderDetailTO->productVatRate = floatval($mfPP[0]["vat_rate"]);
				}
				if (strval($postingOrderTO->storeNoVat)=="") {
					$postingOrderTO->storeNoVat = $mfPS[0]["no_vat"];
				}
				// end vat settings

				// determine the item:cases conversion factor
				if ($postingOrderDetailTO->qtyConvertedToCases=="") {
					$mfPP=$this->getPrincipalProductItem($principalId,$postingOrderDetailTO->productUId);
					if (empty($mfPP)) {
						$errorTO->type=FLAG_ERRORTO_ERROR;
						$errorTO->description="Product Conversion Factor could not be retrieved for this product @line:".($i+1);
						$errorTO->identifier="P";
						$errorTO->identifier2=$i;
						return $errorTO;
					}
					if (intval($mfPP[0]["items_per_case"])>1) {
						$postingOrderDetailTO->qtyConvertedToCases=ceil($postingOrderDetailTO->quantity/intval($mfPP[0]["items_per_case"]));
					} else {
						$postingOrderDetailTO->qtyConvertedToCases=$postingOrderDetailTO->quantity;
					}
				}

				// retrieve basic item pricing
				// NB : THE NAMING CONVENTION "discount_value" implies a positive column as the FORMULA IS ALWAYS NETT_PRICE=ITEM_PRICE MINUS-DISCOUNT ! (minus). So always convert it.

				// general override price checks, this also doubles up as a "use vendor pricing" only if pricing was exactly the same (ie. no diffs)
				if ($postingOrderDetailTO->priceOverrideValue!="") {
					$postingOrderDetailTO->priceOverride="Y";
					if(!is_numeric($postingOrderDetailTO->priceOverrideValue)) {
						$errorTO->type=FLAG_ERRORTO_ERROR;
						$errorTO->description="You have specified an Override Price, but supplied an invalid non-numeric value '{$postingOrderDetailTO->priceOverrideValue}' for Product @line:".($i+1);
						$errorTO->identifier="P"; // used in capture screen to highlight row
						$errorTO->identifier2=$i;
						return $errorTO;
					};
					// check override role
					$hasRole = $administrationDAO->hasRole($userId,$postingOrderTO->principalUId,ROLE_ALLOW_PRICE_OVERRIDE);
					if(!$hasRole) {
						$errorTO->type=FLAG_ERRORTO_ERROR;
						$errorTO->description="You do not have permissions to override price(s).";
						$errorTO->identifier="P";
						$errorTO->identifier2=$i;
						return $errorTO;
					};
					$postingOrderDetailTO->chosenPricingUId="";
					if ($postingOrderDetailTO->priceOverrideUseSuppliedVals) {
						// Use existing supplied
						// If true, then the supplied discount value is subtracted from supplied list price to give override price, with a check done against supplied overridePrice.
						// Also only cater for DV as an amount off.
						$postingOrderDetailTO->priceType=GUICommonUtils::translateDealType(VAL_DEALTYPE_AMOUNT_OFF);
						$postingOrderDetailTO->dealTypeUId=VAL_DEALTYPE_AMOUNT_OFF;
						$postingOrderDetailTO->dealTypeValue=$postingOrderDetailTO->discountValue;
            // $postingOrderDetailTO->nettPrice=trim($postingOrderDetailTO->listPrice) - trim($postingOrderDetailTO->discountValue);
            if ($postingOrderTO->skipInvoiceComputationCheck!="Y") {
              // floatvals cant be compared so use str
  						if (abs($postingOrderDetailTO->nettPrice - $postingOrderDetailTO->priceOverrideValue)>($postingOrderDetailTO->priceOverrideValue*VAL_PRICE_VARIATION_ALLOWED)) {
  							$errorTO->type=FLAG_ERRORTO_ERROR;
  							$errorTO->description="You have specified an Override Price using supplied values for calculation, but this does not equal the supplied overridePriceValue for Product @line:".($i+1);
  							$errorTO->identifier="P"; // used in capture screen to highlight row, at the moment capture screen doesnt allow priceOverrideUseSuppliedVals=true functionality, only false
  							$errorTO->identifier2=$i;
  							return $errorTO;
  						}
  						if (floatval($postingOrderDetailTO->nettPrice)<0) {
  							$errorTO->type=FLAG_ERRORTO_ERROR;
  							$errorTO->description="You have specified an Override Price using supplied values for calculation, but this resulted in an invalid value for Product @line:".($i+1);
  							$errorTO->identifier="P"; // used in capture screen to highlight row, at the moment capture screen doesnt allow priceOverrideUseSuppliedVals=true functionality, only false
  							$errorTO->identifier2=$i;
  							return $errorTO;
  						}
            }
					} else {
						// use only final override price
						$postingOrderDetailTO->priceType=GUICommonUtils::translateDealType(VAL_DEALTYPE_NETT_PRICE);
						$postingOrderDetailTO->dealTypeUId=VAL_DEALTYPE_NETT_PRICE;
						$postingOrderDetailTO->listPrice=$postingOrderDetailTO->priceOverrideValue;
						$postingOrderDetailTO->discountValue="0";
						$postingOrderDetailTO->nettPrice=$postingOrderDetailTO->priceOverrideValue;
						$postingOrderDetailTO->dealTypeValue=0;
					}
					// $postingOrderDetailTO->discountReference=""; // deprecated 27Jun2012, we now carry thru this val if using vendor pricing

				// end : general priceOverrideValue
				} else {
					$postingOrderDetailTO->priceOverride="N";
					
//		print_r($postingOrderDetailTO);
				    // check a valid price exists for this product capture to avoid clipper problems
					$arrPrices=$this->getActivePricesForProduct($principalId,$postingOrderTO->storeChainUId,$postingOrderDetailTO->productUId, $postingOrderTO->documentType, $postingOrderDetailTO->itemspercase);
					if ((sizeof($arrPrices)==0) || ($arrPrices[0]['price']==-1)) {
						$errorTO->type=FLAG_ERRORTO_ERROR;
						$errorTO->description="No PRICING could be found for this product @line:".($i+1) . '  ' . $postingOrderDetailTO->productCode;
						$errorTO->identifier="P";
						$errorTO->identifier2=$i;
						return $errorTO;
					} else {
						$postingOrderDetailTO->chosenPricingUId=$arrPrices[0]['uid'];
						$postingOrderDetailTO->priceType=$arrPrices[0]['price_type'];
						$postingOrderDetailTO->dealTypeUId=$arrPrices[0]['deal_type_uid'];
						$postingOrderDetailTO->listPrice=$arrPrices[0]['list_price'];
						$postingOrderDetailTO->discountValue=$arrPrices[0]['discount_value'];
						$postingOrderDetailTO->nettPrice=round($arrPrices[0]['price'],2);
						$postingOrderDetailTO->discountReference=((trim($arrPrices[0]['reference'])!="")?$arrPrices[0]['reference']:$postingOrderDetailTO->discountReference); // keep passed val if not overridden with new ref
						$postingOrderDetailTO->dealTypeValue=$arrPrices[0]['deal_type_value'];
					}
			  }
       

				$tempDocumentTotal+=$postingOrderDetailTO->nettPrice*$postingOrderDetailTO->quantity; // just to get the amount temporarily for bulk discounts
				$documentCases+=(intval($postingOrderDetailTO->qtyConvertedToCases)>0)?$postingOrderDetailTO->qtyConvertedToCases:$postingOrderDetailTO->quantity;
		    	$i++;
			} // end loop

			/******************************************
			 * START : BULK DISCOUNTS
			 ******************************************/

			$skipBulkDiscountsInEntirety=false; // 1 product skip = all products skip
      $hasPriceOverrideUseSuppliedVals=false; // 1 product = all products

			// apply any bulk item discount values at ITEM LEVEL
			foreach ($postingOrderTO->detailArr as &$postingOrderDetailTO) {

				if ($postingOrderDetailTO->priceOverrideUseSuppliedVals) {
					$skipBulkDiscountsInEntirety=true; // skip if edi pricing. Also, if using edi pricing and skipping computation checks (the flag is set) then bulk discounts are risky !!!
          $hasPriceOverrideUseSuppliedVals=true;
					break;
				}

				// get any Bulk Discounts at ITEM level
				// we artificially build the array object that would have been returned if we were getting active prices, so that it can be passed directly as well if you wanted to.
				list($result,$mfODPTO) = $this->getActivePricesForDocument($principalId,
																		  $postingOrderTO->storeChainUId,
																		  $showAtLevel="I", // this is not the same as DPL_ITEM, as this is a flow control parameter
																		  $invoiceTotal=$tempDocumentTotal,
																		  $invoiceCases=$documentCases,
																		  $lineDealActivePrice=array("deal_type_uid"=>$postingOrderDetailTO->dealTypeUId,
																									 "discount_value"=>$postingOrderDetailTO->discountValue,
																									 "price"=>$postingOrderDetailTO->nettPrice,
																									 "list_price"=>$postingOrderDetailTO->listPrice,
																									 "product_uid"=>$postingOrderDetailTO->productUId,
																									 "product_category"=>$postingOrderDetailTO->majorCategory),
																		  $cases=(intval($postingOrderDetailTO->qtyConvertedToCases)>0)?$postingOrderDetailTO->qtyConvertedToCases:$postingOrderDetailTO->quantity,
																		  $extPrice=$postingOrderDetailTO->nettPrice*$postingOrderDetailTO->quantity);
				if ($result!==true) {
					$errorTO->type=FLAG_ERRORTO_ERROR;
					$errorTO->description=" ERROR saving order - could not retrieve document pricing at item level (bulk).".$mfODPTO;
					return $errorTO;
				}

				// override some of the TO values if Bulk Item discounts exist as established above
				if ((sizeof($mfODPTO)>0) && (sizeof($mfODPTO["selectedArr"])>0)) {
					// remember if Item level only returns 1 dimensional array
					$postingOrderDetailTO->discountValue=$mfODPTO["discount_value"]; // remember naming convention rules imply discount_value is positive column
					$postingOrderDetailTO->nettPrice=round($mfODPTO["nett_price"],2);
					$postingOrderDetailTO->discountReference=((trim($mfODPTO["discount_reference"])!="")?$mfODPTO["discount_reference"]:$postingOrderDetailTO->discountReference); // keep passed val if not overridden with new ref
					$postingOrderDetailTO->dealTypeUId=VAL_DEALTYPE_AMOUNT_OFF;
					$postingOrderDetailTO->priceType=GUICommonUtils::translateDealType ($postingOrderDetailTO->dealTypeUId);
					$postingOrderDetailTO->dealTypeValue=$mfODPTO["discount_value"];
				}
				// setup the rows used in the discount calculation for storage
				foreach ($mfODPTO["selectedArr"] as $row) {
					$pODPTO = new PostingOrderDocumentPricingTO();
					$pODPTO->chosenPricingDocumentUId=$row["uid"];
					$pODPTO->quantity=$row["quantity"];
					$pODPTO->dealTypeUId=$row["deal_type_uid"];
					$pODPTO->value=$row["value"];
					$pODPTO->unitPriceTypeUId=$row["unit_price_type_uid"];
					$pODPTO->description=$row["description"];
					$pODPTO->applyPerUnit=$row["apply_per_unit"];
					$pODPTO->cumulativeType=$row["cumulative_type"];
					$pODPTO->applyLevel=DPL_ITEM;
					$pODPTO->principalProductUId=$postingOrderDetailTO->productUId;
					$pODPTO->discountValue=$row["discount_value"];  // remember naming convention rules imply discount_value is positive column

					$postingOrderTO->pricingDocumentArr[]=$pODPTO;
				}

				$documentTotal+=$postingOrderDetailTO->nettPrice*$postingOrderDetailTO->quantity;
			}


			$documentBulkDiscounts=0;
			$result=false;
			if (!$skipBulkDiscountsInEntirety) {

				// apply the bulk document discount values at DOCUMENT LEVEL
				list($result,$mfODPTO) = $this->getActivePricesForDocument($principalId,
																		  $postingOrderTO->storeChainUId,
																		  $showAtLevel="D", // this is not the same as DPL_ITEM, as this is a flow control parameter
																		  $invoiceTotal=$documentTotal,
																		  $invoiceCases=$documentCases,
																		  $lineDealActivePrice="",
																		  $cases=$documentCases,
																		  $extPrice=$documentTotal);
				if ($result!==true) {
					$errorTO->type=FLAG_ERRORTO_ERROR;
					$errorTO->description.=" ERROR saving order - could not retrieve document pricing (bulk).".$mfODPTO;
					return $errorTO;
				}
				foreach ($mfODPTO as $row) {
					$pODPTO = new PostingOrderDocumentPricingTO();
					$pODPTO->chosenPricingDocumentUId=$row["uid"];
					$pODPTO->quantity=$row["quantity"];
					$pODPTO->dealTypeUId=$row["deal_type_uid"];
					$pODPTO->value=$row["value"];
					$pODPTO->unitPriceTypeUId=$row["unit_price_type_uid"];
					$pODPTO->description=$row["description"];
					$pODPTO->applyPerUnit=$row["apply_per_unit"];
					$pODPTO->cumulativeType=$row["cumulative_type"];
					$pODPTO->applyLevel=DPL_DOCUMENT;
					$pODPTO->principalProductUId="";

					// convert to a monetary value
					$returnVal=0;
					list($result,$returnVal)=$this->calculateBulkDiscountValue($pODPTO->dealTypeUId, $pODPTO->value,$documentTotal);
					if ($result!==true) {
						$errorTO->type=FLAG_ERRORTO_ERROR;
						$errorTO->description=$returnVal; // containst description if false
						return $errorTO;
					}

					$pODPTO->discountValue=$returnVal*(-1); // remember naming convention rules imply discount_value is positive column
					$postingOrderTO->pricingDocumentArr[]=$pODPTO;

					// convert to a monetary value
					list($result,$returnVal)=$this->calculateBulkDiscountValue($pODPTO->dealTypeUId, $pODPTO->value,$documentTotal);
					if ($result!==true) {
						$errorTO->type=FLAG_ERRORTO_ERROR;
						$errorTO->description=$returnVal; // containst description if false
						return $errorTO;
					}
					$documentBulkDiscounts+=$returnVal;
				}

			}

			/******************************************
			 * END : BULK DISCOUNTS
			 ******************************************/

			// set the vat amounts - although we skip bulk disc if atleast 1 product is bypassed, be still need to calc the extPrice here for those that arent skipped
  		foreach ($postingOrderTO->detailArr as &$postingOrderDetailTO) {
        if ($postingOrderDetailTO->priceOverrideUseSuppliedVals) {
          continue;
        }
				$postingOrderDetailTO->extPrice = floatval($postingOrderDetailTO->nettPrice)*intval($postingOrderDetailTO->quantity);
				if (strval($postingOrderTO->storeNoVat)=="0") {
					$postingOrderDetailTO->vatAmount = round($postingOrderDetailTO->extPrice*($postingOrderDetailTO->productVatRate/100),2);
				} else {
					$postingOrderDetailTO->vatAmount = 0;
				}
				$postingOrderDetailTO->totPrice = $postingOrderDetailTO->extPrice + $postingOrderDetailTO->vatAmount;


				// reset the productVatRate to be 0.00 if no vat was applied - needed so that depot calculations work
				if ((strval($postingOrderTO->storeNoVat)=="1") || (floatval($postingOrderDetailTO->productVatRate)==0)) {
				  $postingOrderDetailTO->productVatRate="0";
				}

      }

			$documentTotal+=$documentBulkDiscounts;
		} // end documentType if

		$errorTO->type = FLAG_ERRORTO_SUCCESS;
		$errorTO->description = "Successfully processed Pricing in getFinalInvoicePricing()";

		return $errorTO;
	}

	// essentially returns all products for a principal by search criteria, BUT is different from agent search as it indicates whether or not permissions are present
	// keywords must be already lowercased !
	// a Stored function is made use of, which can be quite slow, so becareful of the length of the string passed to it !!
	public function getUserSearchPrincipalProductArray($userId, $principalId, $keywordsArr) {
		$where="";
		foreach ($keywordsArr as $w) {
			if ($where!="") $where.=" and concat(b.product_code,b.product_description) like '%".trim($w)."%' ";
			else $where=" concat(b.product_code,b.product_description) like '%".trim($w)."%' ";
			/* too slow to use function
			if ($where!="") $where.=" and alphaNumericValue(concat(b.deliver_name,b.deliver_add1)) like '%".trim($w)."%' ";
			else $where=" alphaNumericValue(concat(b.deliver_name,b.deliver_add1)) like '%".trim($w)."%' ";
			*/
		}
		if ($where!="") $where=" and ({$where}) ";
		$sql="select b.product_code, b.product_description,
					 if(a.uid is not null or ur.uid is not null,1,0) has_product_permission
				from   principal_product b
							left join user_principal_product a on a.principal_product_uid = b.uid and a.user_uid = '{$userId}'
							left join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_PRODUCT_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid=b.principal_uid)
				where  b.principal_uid = '{$principalId}'
				-- and    (a.uid is not null or ur.uid is not null)
				{$where}
				order  by b.product_description";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	/**
	 * @return asmnbsd, sdfsdf,
	 */
	public function getUserPrincipalPricingDocuments($userId, $principalId, $customerType, $storeChainId) {
		$sql="select a.uid, 
		             a.grouping, 
		             a.principal_uid, 
		             a.description, 
		             a.customer_type_uid, 
		             a.store_chain_uid, 
		             a.unit_price_type_uid,
                 a.quantity,
                 a.deal_type_uid, 
                 a.value, 
                 a.status, 
                 a.start_date, 
                 a.end_date, 
                 a.apply_level, 
                 apply_per_unit, 
                 cumulative_type,
                 a.product_type
			  from   pricing_document a
        INNER JOIN (select distinct principal_id from user_principal_depot where user_principal_depot.principal_id = ".mysqli_real_escape_string($this->dbConn->connection, $principalId)." and user_principal_depot.user_id=".mysqli_real_escape_string($this->dbConn->connection, $userId).") user_principal_depot
							ON user_principal_depot.principal_id = a.principal_uid
						Left Join user_principal_store ON user_principal_store.principal_store_uid = a.store_chain_uid AND user_principal_store.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)." AND a.customer_type_uid = ".CT_STORE."
						left join user_role ur on {$userId} = ur.user_id and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid={$principalId})
						Left Join user_principal_chain ON user_principal_chain.principal_chain_uid = a.store_chain_uid AND user_principal_chain.user_uid=".mysqli_real_escape_string($this->dbConn->connection, $userId)." AND a.customer_type_uid = ".CT_CHAIN."
						left join user_role urc on {$userId} = urc.user_id and urc.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (urc.entity_uid is null or urc.entity_uid={$principalId})
						Left Join principal_store_master h ON a.store_chain_uid = h.uid and a.customer_type_uid = ".CT_STORE."
						Left Join principal_chain_master i ON a.store_chain_uid = i.uid and a.customer_type_uid = ".CT_CHAIN."
			  where  a.principal_uid='".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
			  and    a.customer_type_uid='".mysqli_real_escape_string($this->dbConn->connection, $customerType)."'
			  and    a.store_chain_uid='".mysqli_real_escape_string($this->dbConn->connection, $storeChainId)."'
			  and   (if(a.customer_type_uid = ".CT_CHAIN.",user_principal_chain.uid,user_principal_store.uid) is not null or
			  		   ((a.customer_type_uid = ".CT_STORE." and ur.uid is not null) or (a.customer_type_uid = ".CT_CHAIN." and urc.uid is not null)))
			  order by a.grouping, unit_price_type_uid, quantity";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		$uidList=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$row["productArr"]=array();
				$arr[$row["uid"]]=$row;
				$uidList[]=$row["uid"];
			}
		}

		if (sizeof($uidList)>0) {
			// There is no permissions check on product. See comments as to why in the documentPricing Screen.
			$sql="select pdp.*
				  from   pricing_document a,
						 pricing_document_product pdp
				  where  a.uid = pdp.pricing_document_uid
				  and 	 a.product_type in ('P','PC')
				  and    a.uid in (".implode(",",$uidList).")
				  order by a.grouping, unit_price_type_uid, quantity, a.uid";

			$this->dbConn->dbQuery($sql);

			if ($this->dbConn->dbQueryResultRows > 0) {
				while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
					$arr[$row["pricing_document_uid"]]["productArr"][]=$row;
				}
			}
		}

		return $arr;
	}

	public function getUnitPriceTypes() {
		$sql="select uid, description
			  from   unit_price_type";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getUnitPriceTypeItem($uid) {
		$sql="select uid, description
			  from   unit_price_type
			  where  uid = '".mysqli_real_escape_string($this->dbConn->connection, $uid)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	// principal is added for security
	public function getDocumentPriceItem($uid, $principalUId) {
		$sql="select uid, description
			  from   pricing_document
			  where  uid = '".mysqli_real_escape_string($this->dbConn->connection, $uid)."'
			  and    principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getUserDocumentPriceItem($uid, $principalUId, $userUId) {
		$sql="select a.uid, a.principal_uid, a.description, a.customer_type_uid, a.store_chain_uid, a.unit_price_type_uid,
					 a.quantity, a.deal_type_uid, a.value, a.status
			  from   pricing_document a
			  where  uid = '".mysqli_real_escape_string($this->dbConn->connection, $uid)."'
			  and    principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
			  and    exists (select 1 from user_principal_depot b where a.principal_uid = b.principal_id and user_id='".mysqli_real_escape_string($this->dbConn->connection, $userUId)."')";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getAllProductMinorCategory($principalId, $systemId, $status = FLAG_STATUS_ACTIVE) {

          $sql="select d.uid, value, f.uid as field_uid, lable, `order`, required
                from   product_minor_category d
                  inner join product_minor_category_type f on d.minor_category_type_uid = f.uid
                where  d.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
                  and f.system_uid = '".mysqli_real_escape_string($this->dbConn->connection, $systemId)."'
                    " . (($status!==false)?(" and d.status = '".mysqli_real_escape_string($this->dbConn->connection, $status)."' "):("")) . "
                  ORDER BY value";

          $this->dbConn->dbQuery($sql);
          $arr=array();
          if ($this->dbConn->dbQueryResultRows > 0) {
            while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
              $arr[$row['field_uid']][] = $row;
            }
          }

          return $arr;


	}

	public function getProductMinorCategoryLables($principalUId, $systemId) {

          //look on principal level
          $sql="select *
                  from   product_minor_category_type
                where system_uid = '".mysqli_real_escape_string($this->dbConn->connection, $systemId)."'
                  AND principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
                  ORDER BY IFNULL(`order`,999)";

          $resultArr = $this->dbConn->dbGetAll($sql);

          if(count($resultArr) == 0){

            //look on system level
            $sql="select *
                  from   product_minor_category_type
                where system_uid = '".mysqli_real_escape_string($this->dbConn->connection, $systemId)."'
                  AND principal_uid IS NULL
                  ORDER BY IFNULL(`order`,999)";

            $resultArr = $this->dbConn->dbGetAll($sql);
          }

          return $resultArr;

	}

        public function getProductMinorCategoryByProductUid($productUid) {

          $sql = "select p.*, c.minor_category_type_uid, c.value, ct.lable from principal_product_minor_category  p
                  inner join product_minor_category c on p.product_minor_category_uid = c.uid
                  inner join product_minor_category_type ct on c.minor_category_type_uid = ct.uid
                  where  p.principal_product_uid = '".mysqli_real_escape_string($this->dbConn->connection, $productUid)."'";

          return $this->dbConn->dbGetAll($sql);

        }

        // cant imagine this ever being used outside of looking up directly on ppuid as key
        public function getPrincipalProductGTINsByUIds($ppUIdArr) {

          $sql = "select sku_gtin, outercasing_gtin
                  from   principal_product_depot_gtin
                  where  principal_product_uid in (".implode(",",$ppUIdArr).") ";

          $this->dbConn->dbQuery($sql);
          $arr=array();
          if ($this->dbConn->dbQueryResultRows > 0) {
            while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
              $arr[$row['principal_product_uid']] = $row;
            }
          }

          return $arr;

        }

        public function getNonStockItemByProductUid($productUid) {

          $sql = "select pp.non_stock_item, pp.direct_inv
                  from principal_product pp
                  where  pp.uid = '".mysqli_real_escape_string($this->dbConn->connection, $productUid)."'";
                  
          $this->dbConn->dbQuery($sql);
          
          $arr=array();
          if ($this->dbConn->dbQueryResultRows > 0) {
            while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
              $arr = $row;
            }
          }
          return $arr;
           
        }
// ***************************************************************************************************************************************************************
	public function getAllowDecimalFlag($prodUId) {

          //look on principal level
          $sql="select pp.allow_decimal
                FROM principal_product pp
                WHERE pp.uid = " . mysqli_real_escape_string($this->dbConn->connection, $prodUId);

           $this->dbConn->dbQuery($sql);
          
          $arr=array();
          if ($this->dbConn->dbQueryResultRows > 0) {
            while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
              $arr = $row;
            }
          }
          return $arr;
  }
// ***************************************************************************************************************************************************************

}



?>

<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class StockDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

	public function getUserPrincipalStock($userId, $principalId, $minorCategoryFilterArr = array()) {
		$administrationDAO = new AdministrationDAO($this->dbConn);
    	$hasRole = $administrationDAO->hasRole($userId, $principalId, ROLE_BYPASS_USER_PRODUCT_RESTRICTION);
    	// lift the user restriction if has role
    	if ($hasRole) $where=""; else $where=" AND user_principal_product.uid is not null ";

            $minorCategorySQL = '';
            if(is_array($minorCategoryFilterArr) && count($minorCategoryFilterArr) > 0){
              foreach($minorCategoryFilterArr as $i){
                if(!empty($i) && is_numeric($i))
                  $minorCategorySQL .= ' and EXISTS (select 1 from principal_product_minor_category ppc where ppc.principal_product_uid = p.uid and ppc.product_minor_category_uid = "'.mysqli_real_escape_string($this->dbConn->connection, $i).'") ';
              }
            }

         if ($principalId <> 160) $psort = '  principal_id, a.depot_id, stock_descrip'; else  $psort = 'principal_id, a.depot_id, stock_item';

            $sql="select a.principal_id, 
                         c.name principal_name, 
                         a.depot_id, 
                         d.name depot_name, 
                         if(principal_product.uid is null,
                         a.stock_item,principal_product.product_code) stock_item,
                         if(principal_product.uid is null,
                         a.stock_descrip,principal_product.product_description) stock_descrip,
                         a.`opening`,
                         a.`arrivals`,
                         a.`uplifts`,
                         a.`returns_cancel`,
                         a.`returns_nc`,
                         a.`delivered`,
                         a.`adjustment`,
                         a.`closing`,
                         a.`allocations`,
                         a.`in_pick`,
                         a.`available`,
                         a.`lost_sales_cancel`,
                         a.`lost_sales_oos`,
                         a.`stock_count`,
                         a.`stock_count_date`,
                         a.data_generated_date, 
                         a.last_updated, 
                         a.goods_in_transit, 
                         a.blocked_stock,
                         p.allow_decimal
                         
                  FROM   stock a
                  LEFT JOIN principal_product on a.stock_item = principal_product.product_code and a.principal_id = principal_product.principal_uid
               -- LEFT JOIN user_principal_product ON principal_product.uid = user_principal_product.principal_product_uid and user_principal_product.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
                  LEFT JOIN principal_product p on a.principal_product_uid = p.uid and a.principal_id = p.principal_uid
                  INNER JOIN principal c on a.principal_id = c.uid
                  INNER JOIN depot d on a.depot_id = d.uid
                  WHERE  a.principal_id = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
                  AND EXISTS (SELECT 1 from user_principal_depot upd
                              WHERE a.principal_id = upd.principal_id
                              AND a.depot_id = upd.depot_id
                              AND upd.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
                              AND upd.principal_id = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."')

                  AND    (principal_product.uid is null or principal_product.status != '".FLAG_STATUS_DELETED."')".

                      $where."
                  " . $minorCategorySQL . "
                  ORDER BY " . $psort ;
                  
          return $this->dbConn->dbGetAll($sql);
	}

	public function getPrincipalStock($principalId, $minorCategoryFilterArr = array()) {

	  $minorCategorySQL = '';
	  if(is_array($minorCategoryFilterArr) && count($minorCategoryFilterArr) > 0){
	    foreach($minorCategoryFilterArr as $i){
	      if(!empty($i) && is_numeric($i))
	        $minorCategorySQL .= ' and EXISTS (select 1 from principal_product_minor_category ppc where ppc.principal_product_uid = p.uid and ppc.product_minor_category_uid = "'.mysqli_real_escape_string($this->dbConn->connection, $i).'") ';
	    }
	  }


	  $sql="select a.principal_id, a.depot_id, d.name depot_name, if(principal_product.uid is null,a.stock_item,principal_product.product_code) stock_item,
                        if(principal_product.uid is null,a.stock_descrip,principal_product.product_description) stock_descrip,
                         a.`opening`,a.`arrivals`,a.`uplifts`,a.`returns_cancel`,a.`returns_nc`,a.`delivered`,a.`adjustment`,a.`closing`,a.`allocations`,
                                a.`in_pick`,a.`available`,a.`lost_sales_cancel`,a.`lost_sales_oos`,a.`stock_count`,a.`stock_count_date`,
                         a.data_generated_date, a.last_updated, a.goods_in_transit, a.blocked_stock
                  from   stock a
                            LEFT JOIN principal_product on a.stock_item = principal_product.product_code and a.principal_id = principal_product.principal_uid
                            LEFT JOIN principal_product p on a.principal_product_uid = p.uid and a.principal_id = p.principal_uid
                            INNER JOIN depot d on a.depot_id = d.uid
                  where  a.principal_id = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
                  and    (principal_product.uid is null or principal_product.status != '".FLAG_STATUS_DELETED."')".
                  $minorCategorySQL . "
                  order  by a.principal_id, a.depot_id, stock_descrip";

	  return $this->dbConn->dbGetAll($sql);
	}

	public function getDepotPrincipalStock($userId, $principalId, $minorCategoryFilterArr = array()) {

          $minorCategorySQL = '';
          if(is_array($minorCategoryFilterArr) && count($minorCategoryFilterArr) > 0){
            foreach($minorCategoryFilterArr as $i){
              if(!empty($i) && is_numeric($i))
                $minorCategorySQL .= ' and EXISTS (select 1 from principal_product_minor_category ppc where ppc.principal_product_uid = p.uid and ppc.product_minor_category_uid = "'.mysqli_real_escape_string($this->dbConn->connection, $i).'") ';
            }
          }

          $sql="SELECT a.principal_id, 
                       c.name principal_name, 
                       a.depot_id, 
                       d.name depot_name, 
                       if(p.uid is null,a.stock_item,p.product_code) stock_item,
                       if(p.uid is null,a.stock_descrip,p.product_description) stock_descrip,
                       a.`opening`,
                       a.`arrivals`,
                       a.`uplifts`,
                       a.`returns_cancel`,
                       a.`returns_nc`,
                       a.`delivered`,
                       a.`adjustment`,
                       a.`closing`,
                       a.`allocations`,
                       a.`in_pick`,
                       a.`available`,
                       a.`lost_sales_cancel`,
                       a.`lost_sales_oos`,
                       a.`stock_count`,
                       a.`stock_count_date`,
                       a.data_generated_date, 
                       a.last_updated, 
                       a.goods_in_transit, 
                       a.blocked_stock,
                       p.allow_decimal
                FROM   stock a
                INNER JOIN principal_product p on a.principal_product_uid = p.uid and a.principal_id = p.principal_uid
                LEFT JOIN user_principal_product ON p.uid = user_principal_product.principal_product_uid and user_principal_product.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."',
                user_principal_depot b,
                principal c,
                depot d
                WHERE  a.principal_id = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
                AND    a.principal_id = b.principal_id
                AND    a.depot_id = b.depot_id
                AND    b.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
                AND    a.principal_id = c.uid
                AND    a.depot_id = d.uid
                AND    d.wms = 'Y'
                AND     p.status != '".FLAG_STATUS_DELETED."'
                                " . $minorCategorySQL . "
                ORDER BY a.principal_id, a.depot_id, stock_descrip";


          return $this->dbConn->dbGetAll($sql);

	}


	public function getStockCountProducts($depotId, $principalId, $categoryUIds=false){

          $sql = "SELECT  a.principal_id as principal_uid, 
                          c.name as principal_name, 
                          a.depot_id as depot_uid, 
                          d.name as depot_name,
                          p.uid as product_uid, 
                          p.product_code as product_code, 
                          p.product_description,
                          a.`closing`, 
                          a.`available`, 
                          a.`in_pick`, 
                          ppc.description AS 'Catagory'
                 FROM   stock a
                 INNER JOIN principal_product p on a.principal_product_uid = p.uid and a.principal_id = p.principal_uid
                 INNER JOIN principal c on a.principal_id = c.uid
                 INNER JOIN depot d on a.depot_id = d.uid
                 LEFT  JOIN principal_product_category ppc ON ppc.uid = p.major_category
                 WHERE  a.principal_id = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
                 AND  a.depot_id = '".mysqli_real_escape_string($this->dbConn->connection, $depotId)."'
                 AND  d.wms = 'Y'
                 AND  p.status = 'A'
                 ".(($categoryUIds)? " AND ppc.uid IN ($categoryUIds)" : " " )."
                 ORDER  BY ppc.description , p.product_code";

          return $this->dbConn->dbGetAll($sql);

	}


        public function getPrincipalProductStock($depotUId, $principalId, $productUId){

          $sql="select a.principal_id, c.name principal_name, a.depot_id, d.name depot_name, a.stock_item, a.stock_descrip,
                       a.`opening`, a.`arrivals`,a.`uplifts`, a.`returns_cancel`, a.`returns_nc`, a.`delivered`, a.`adjustment`, a.`closing`,
                       a.`allocations`, a.`in_pick`, a.`available`, a.`lost_sales_cancel`, a.`lost_sales_oos`, a.`stock_count`,
                       a.`stock_count_date`, a.data_generated_date, a.last_updated
                          from   stock a
                              INNER JOIN principal_product on a.stock_item = principal_product.product_code and a.principal_id = principal_product.principal_uid,
                                     principal c,
                                     depot d
                          where  a.principal_id = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
                          and    a.depot_id = '".mysqli_real_escape_string($this->dbConn->connection, $depotUId)."'
                          and    principal_product.uid = '".mysqli_real_escape_string($this->dbConn->connection, $productUId)."'
                          and    a.principal_id = c.uid
                          and    a.depot_id = d.uid
                          order  by a.principal_id, a.depot_id, stock_descrip";
                          
          return $this->dbConn->dbGetAll($sql);

	}


	public function getUserPrincipalProductStock($userId, $principalId, $productUId, $depotUId) {
		$administrationDAO = new AdministrationDAO($this->dbConn);
    	$hasRole = $administrationDAO->hasRole($userId, $principalId, ROLE_BYPASS_USER_PRODUCT_RESTRICTION);
    	// lift the user restriction if has role
    	if ($hasRole) $where=""; else $where=" AND user_principal_product.uid is not null ";

		$sql="SELECT a.principal_id, 
                 c.name principal_name, 
		             a.depot_id, 
		             d.name depot_name, 
		             a.stock_item, 
		             a.stock_descrip,
		             a.`opening`,
		             a.`arrivals`,
		             a.`uplifts`,
		             a.`returns_cancel`,
		             a.`returns_nc`,
		             a.`delivered`,
		             a.`adjustment`,
		             IF(principal_product.allow_decimal = 'Y', a.`closing`/100, a.`closing`),
		             a.`allocations`,
                 a.`in_pick`,
                 IF(principal_product.allow_decimal = 'Y', round(a.`available`/100,2), a.`available`) as 'available',
                 a.`lost_sales_cancel`,
                 a.`lost_sales_oos`,
                 a.`stock_count`,
                 a.`stock_count_date`,
                 a.data_generated_date, 
                 a.last_updated
          FROM   stock a
          LEFT JOIN principal_product on a.stock_item = principal_product.product_code and a.principal_id = principal_product.principal_uid
          LEFT JOIN user_principal_product ON principal_product.uid = user_principal_product.principal_product_uid and user_principal_product.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."',
                    user_principal_depot b,
                    principal c,
                    depot d
          WHERE  a.principal_id = b.principal_id
          AND    a.depot_id = b.depot_id
          AND    b.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
          AND    b.principal_id = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
          AND    b.depot_id = '".mysqli_real_escape_string($this->dbConn->connection, $depotUId)."'
          AND    principal_product.uid = '".mysqli_real_escape_string($this->dbConn->connection, $productUId)."'
          AND    a.principal_id = c.uid
          AND    a.depot_id = d.uid ".
          $where."
          ORDER BY a.principal_id, a.depot_id, stock_descrip";

          return $this->dbConn->dbGetAll($sql);

	}

        public function checkStockMode($principalId, $depotId){

          $sql="SELECT 1 FROM   stock_take_mode
                WHERE  principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) ."'
                AND    depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId) ."'";
          $arr = $this->dbConn->dbGetAll($sql);

          return (count($arr) > 0) ? true : false;

        }

        public function getPreviousStockTakeDate($principalId, $depotId){

          $sql="SELECT MAX(snapshot_date) as date FROM stock_audit
                WHERE  principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) ."'
                  AND  depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId) ."'
                  AND  snapshot_type = 1";
          $a = $this->dbConn->dbGetAll($sql);

          return (isset($a['0']['date'])) ? $a['0']['date'] : false;

        }

        public function getDateAuditProducts($principalId, $depotId, $prodId){
        	
        	if(mysqli_real_escape_string($this->dbConn->connection, $prodId) == '') {
             $prodsel = '';
        	} else {
             $prodsel = "AND s.principal_product_uid = '" . (mysqli_real_escape_string($this->dbConn->connection, $prodId)) . "'";
          } 

          $sql="SELECT s.stock_item, s.principal_product_uid, s.stock_descrip, s.closing
                FROM   `stock` s
                WHERE  s.`principal_id` = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) ."'
                AND    s.`depot_id`     = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId) ."'"
                . $prodsel . ";";
    
          return $mfCS = $this->dbConn->dbGetAll($sql);
        }

// ********************************************************************************************************************************************
   public function CheckForStockRecord($principalId, $depotId, $ProductUid) {
         $sql = " select a.uid, a.principal_id, a.depot_id, a.principal_product_uid, a.available
                  from stock a
                  where a.principal_id          = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) ."'
                  and   a.depot_id              = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId)     ."'
                  and   a.principal_product_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $ProductUid)  ."';";
                  
          return  $this->dbConn->dbGetAll($sql);
   
   }

// ********************************************************************************************************************************************

}

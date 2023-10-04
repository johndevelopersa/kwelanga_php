<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');


class SkymanoApiDAO {
	
	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
}	

// ************************************************************************************************************************************
   public function getWarehouseClosing($prinUid, $depotList) {
   	
          if($depotList == 186) {
          	  $conDep = '186,449,236';
          } else {
              $conDep = $depotList;  	
          }  	    

          $sql = "SELECT d.uid as 'depot',
                         d.name AS 'wh',
                         " . $depotList . " AS 'whUid',
                         sfd.value,
                         pp.uid as 'ppUid',
                         pp.product_code, 
                         pp.spare, 
                         pp.product_description,
                         pp.items_per_case,
                         sum(s.available) as 'available',
                         sum(s.closing),
                         ul.stock_level
                  FROM " . iDATABASE . ".stock s
                  LEFT JOIN " . iDATABASE . ".principal_product pp ON pp.uid = s.principal_product_uid
                  LEFT JOIN " . iDATABASE . ".special_field_details sfd ON sfd.field_uid = 612 AND sfd.entity_uid = s.depot_id
                  LEFT JOIN " . iDATABASE . ".depot d ON d.uid = s.depot_id
                  LEFT JOIN " . iDATABASE . ".skynamo_update_log ul ON ul.depot_uid = s.depot_id 
                                                                    AND ul.principal_product_uid = s.principal_product_uid 
                                                                    AND ul.update_type = '1'
                  WHERE s.principal_id = " . mysqli_real_escape_string($this->dbConn->connection,$prinUid) . "
                  AND   s.depot_id IN (" . mysqli_real_escape_string($this->dbConn->connection,$conDep) . ")
                  AND   pp.spare IS NOT NULL
                  AND   if(ul.uid IS NULL, 1, TIMEDIFF(NOW(), ul.dateTime) > '00:59:00')
                  AND   if(ul.stock_level IS NOT NULL,(s.available) <> ul.stock_level,1)
                  GROUP BY pp.product_code
                  
                  ORDER BY s.depot_id , spare";
                  //echo "<pre>";
                  //echo $sql;
                  //die();
                  
          return $this->dbConn->dbGetAll($sql);
          
   }      
// ************************************************************************************************************************************
 public function updateSkyProductIds($prodCode, $prodId) {

    $sql="UPDATE principal_product pp SET pp.spare = '" . mysqli_real_escape_string($this->dbConn->connection,$prodId) . "'
          WHERE pp.principal_uid = 216
          AND   pp.product_code = '" . mysqli_real_escape_string($this->dbConn->connection,$prodCode) . "'";

    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to Omni Import Update in setOmniInvoiceStatus";
    } else {
    	$this->dbConn->dbQuery("commit");
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;

  }
// ****************************************************************************************************************************************************
   public function updateSkyTime($depId, $ppUid, $stockLevel) {
   	
   	            $sql = "SELECT *
                        FROM .skynamo_update_log ul
                        WHERE ul.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection,$depId) . "
                        AND   ul.principal_product_uid = " . mysqli_real_escape_string($this->dbConn->connection,$ppUid) . "
                        AND   ul.update_type = 1";

                $logRec = $this->dbConn->dbGetAll($sql);   
                
                if(count($logRec) == 0) {
                     $sql = "INSERT INTO skynamo_update_log (skynamo_update_log.update_type,
                                                             skynamo_update_log.depot_uid,
                                                             skynamo_update_log.principal_product_uid,
                                                             skynamo_update_log.dateTime,
                                                             skynamo_update_log.stock_level)
                             VALUE ('1', 
                                    " . mysqli_real_escape_string($this->dbConn->connection,$depId) . ", 
                                    " . mysqli_real_escape_string($this->dbConn->connection,$ppUid) . ",
                                    NOW(),
                                    '" . mysqli_real_escape_string($this->dbConn->connection,$stockLevel) . "');";
                                    
                     $this->dbConn->dbQuery($sql);

                     if (!$this->dbConn->dbQueryResult) {
                         $this->errorTO->type = FLAG_ERRORTO_ERROR;
                         $this->errorTO->description = "Failed to Omni Import Update in setOmniInvoiceStatus";
                     } else {
    	                   $this->dbConn->dbQuery("commit");
                         $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                         $this->errorTO->description = "Successful";
                     }
                }  else {
                     
                     $sql = "UPDATE skynamo_update_log ul SET ul.dateTime = NOW(), ul.stock_level = " . mysqli_real_escape_string($this->dbConn->connection,$stockLevel) . "
   	                         WHERE ul.depot_uid  = " . mysqli_real_escape_string($this->dbConn->connection,$depId) . "
                             AND ul.update_type  = 1
                             AND ul.principal_product_uid = " . mysqli_real_escape_string($this->dbConn->connection,$ppUid) . ";";
                             
                     $this->dbConn->dbQuery($sql);

                     if (!$this->dbConn->dbQueryResult) {
                         $this->errorTO->type = FLAG_ERRORTO_ERROR;
                         $this->errorTO->description = "Failed to Omni Import Update in setOmniInvoiceStatus";
                     } else {
                         $this->dbConn->dbQuery("commit");
                         $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                         $this->errorTO->description = "Successful";
                     }
                }
                return $this->errorTO;      
       }
// ************************************************************************************************************************************


}  
?>  
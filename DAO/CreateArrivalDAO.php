<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class CreateArrivalDAO {
	
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

// ***************************************************************************************************************************
	public function getUserArrivalDepot($pvUid) {

        $sql = "SELECT pv.allowed_warehouses, u.uid as 'userId'
                FROM .principal_vendor pv
                LEFT JOIN users u ON u.username = pv.username 
                WHERE pv.pv_uid  = '"  . mysqli_real_escape_string($this->dbConn->connection, $pvUid) ."';";

        return $this->dbConn->dbGetAll($sql);
	}
	
// ***************************************************************************************************************************
   public function getUserArrivalStore($prinId, $depotId) {
		
           $sql = "SELECT psm.uid
                   FROM .principal_store_master psm
                   WHERE psm.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $prinId) ."
                   AND   psm.depot_uid     = "  . mysqli_real_escape_string($this->dbConn->connection, $depotId) ."
                   AND   psm.old_account = CONCAT('ARRIVAL', " . mysqli_real_escape_string($this->dbConn->connection, $depotId) .") ";

           return $this->dbConn->dbGetAll($sql);
           
   }

// ***************************************************************************************************************************
   public function insertUserArrivalStore($prinId, $depotId, $userId) {
          // Get Prin name
          
          $sql = "SELECT if(p.short_name IS NOT NULL,p.short_name,trim(SUBSTR(p.name,1,15))) AS 'PNAME'
                  FROM principal p WHERE p.uid = "  . mysqli_real_escape_string($this->dbConn->connection, $prinId) .";";
                  
                  $pName = $this->dbConn->dbGetAll($sql);

          // Get Depot name
          
          $sql = "SELECT trim(d.name) AS 'DNAME'
                  FROM .depot d
                  WHERE d.uid = "  . mysqli_real_escape_string($this->dbConn->connection, $depotId) .";";
                  
                  $dName = $this->dbConn->dbGetAll($sql);
                  
          $storeName = "Arrival - " . $dName[0]['DNAME'] . "" . $pName[0]['DNAME']  ;
          
          $stripStoreName = strtolower(str_replace(' ', '', $storeName));
                 
          
          // Get Generic Principal Chain UID
          
          $sql = "SELECT pcm.uid
                  FROM .principal_chain_master pcm
                  WHERE pcm.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
                  AND   pcm.old_code = '999';";
                  
                  $chainUid = $this->dbConn->dbGetAll($sql);
          
           $sql = "INSERT INTO `principal_store_master` (`principal_uid`, 
                                                         `deliver_name`, 
                                                         `deliver_add1`, 
                                                         `deliver_add2`, 
                                                         `deliver_add3`, 
                                                         `bill_name`, 
                                                         `bill_add1`, 
                                                         `bill_add2`, 
                                                         `bill_add3`, 
                                                         `ean_code`, 
                                                         `vat_number`, 
                                                         `vat_number_2`, 
                                                         `depot_uid`, 
                                                         `principal_chain_uid`, 
                                                         `branch_code`, 
                                                         `old_account`, 
                                                         `captured_by`, 
                                                         `stripped_deliver_name`, 
                                                         `last_updated`, 
                                                         `last_change_by_userid`, 
                                                         `epod_rsa_id`, 
                                                         `epod_cellphone_number`, 
                                                         `retailer`, 
                                                         `q_r_code_to_print`, 
                                                         `warehouse_link`) 
                   VALUES ('"  . mysqli_real_escape_string($this->dbConn->connection, $prinId) ."', 
                           '"  . mysqli_real_escape_string($this->dbConn->connection, $storeName) ."',
                           ' ', 
                           ' ', 
                           ' ', 
                           '"  . mysqli_real_escape_string($this->dbConn->connection, $storeName) ."', 
                           ' ', 
                           ' ', 
                           ' ', 
                           '', 
                           '', 
                           '', 
                           '"  . mysqli_real_escape_string($this->dbConn->connection, $depotId) ."', 
                           '"  . mysqli_real_escape_string($this->dbConn->connection, $chainUid[0]['uid']) ."', 
                           '', 
                           concat('ARRIVAL',"  . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "), 
                           '11', 
                           '"  . mysqli_real_escape_string($this->dbConn->connection, $stripStoreName) ."', 
                           NOW(), 
                           '"  . mysqli_real_escape_string($this->dbConn->connection, $userId) ."', 
                           '', 
                           '', 
                           NULl, 
                           NULL, 
                           '');";
           $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                  $this->errorTO->description="Create Arrival Store Failed : ". $sql .$this->errorTO->description;
                  return $this->errorTO;
          } else {
                  $psmUId = $this->dbConn->dbGetLastInsertId();
                  $this->errorTO->identifier = $psmUId ; 
                  $this->dbConn->dbQuery("commit");   
                  return $this->errorTO;
          }
   }
// ***************************************************************************************************************************
   public function scanStockArrival($PostingDocumentTO) {
   	
          global $ROOT, $PHPFOLDER;

          include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
          
          $principalUId = $PostingDocumentTO->principalUId ;
          $depotUId     = $PostingDocumentTO->depotUId;
          
          foreach ($PostingDocumentTO->detailArr as $dRow) {          	
          	
                $productDAO = new ProductDAO($this->dbConn);
                $nSI = $productDAO->getNonStockItemByProductUid($dRow->productUId);
                
                if($nSI['non_stock_item'] == "N") { 
                	    $processedQty = $dRow->documentQty;                
                
                      if (trim($processedQty)=="") $processedQty = "0";
                      $sql= "UPDATE stock SET arrivals  = arrivals + abs(" . mysqli_real_escape_string($this->dbConn->connection, $processedQty) ."),
                                                          closing   = closing  + abs(" . mysqli_real_escape_string($this->dbConn->connection, $processedQty) ."),
                                                          available = if(available >= 0, available, 0) + abs(" . mysqli_real_escape_string($this->dbConn->connection, $processedQty) .") - abs(allocations) - abs(in_pick)
                             WHERE principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalUId) ."
                             AND   depot_id     = " . mysqli_real_escape_string($this->dbConn->connection, $depotUId) . " 
                             AND   principal_product_uid = '" . mysqli_real_escape_string($this->dbConn->connection,$dRow->productUId) ."'";



                     $this->errorTO = $this->dbConn->processPosting($sql,"");

                     if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                              $this->errorTO->description="Failed to updateStockArrival : ".$this->errorTO->description;
                              return $this->errorTO;
                     }
                
                     if (mysqli_affected_rows($this->dbConn->connection) == 0) {
                           $sql="INSERT INTO stock (principal_id,
                                                    depot_id,
                                                    principal_product_uid,
                                                    stock_item,
                                                    stock_descrip,
                                                    opening,
                                                    arrivals,
                                                    uplifts,
                                                    returns_cancel,
                                                    returns_nc,
                                                    delivered,
                                                    adjustment,
                                                    closing,
                                                    allocations,
                                                    in_pick,
                                                    available,
                                                    lost_sales_cancel,
                                                    lost_sales_oos,
                                                    stock_count,
                                                    stock_count_date,
                                                    guid,
                                                    data_generated_date,
                                                    last_updated)
                                 SELECT " . mysqli_real_escape_string($this->dbConn->connection, $principalUId)     .",
                                        " . mysqli_real_escape_string($this->dbConn->connection, $depotUId)         .",
                                        " . mysqli_real_escape_string($this->dbConn->connection, $dRow->productUId) .",
                                        product_code, 
                                        product_description, 
                                        0,
                                        abs(" . mysqli_real_escape_string($this->dbConn->connection, $processedQty) ."),
                                        0,
                                        0,
                                        0,
                                        0,
                                        0,
                                        abs(" . mysqli_real_escape_string($this->dbConn->connection, $processedQty) ."),
                                        0,
                                        0,
                                        abs(" . mysqli_real_escape_string($this->dbConn->connection, $processedQty) ."),
                                        0,
                                        0,
                                        0,
                                        null,
                                        null,
                                        now(), 
                                        now()
                                  FROM   principal_product
                                  WHERE  principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalUId) ."'
                                  AND    uid = '" . mysqli_real_escape_string($this->dbConn->connection, $dRow->productUId) . " '";

                                  // file_put_contents($ROOT.'ftp/api/tapiorderBDs' . test_input(trim($JSON['reference_number'])) . date("YmdHis") . '.txt', $sql, FILE_APPEND );

                                  $this->errorTO = $this->dbConn->processPosting($sql,"");
                     }
                }
          }          

          if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
                   $this->dbConn->dbQuery("commit"); 
                   $this->errorTO->description="Update Stock Arrival successful";
                   return $this->errorTO;
          }
   }
}

<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class CreateStockMovementDAO {
	
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

// ***************************************************************************************************************************
	public function getUserStockMovementDepot($pvUid) {

        $sql = "SELECT pv.allowed_warehouses, u.uid as 'userId'
                FROM " . iDATABASE . ".principal_vendor pv
                LEFT JOIN users u ON u.pv_user = pv_uid 
                WHERE pv.pv_uid  = '"  . mysqli_real_escape_string($this->dbConn->connection, $pvUid) ."';";

        return $this->dbConn->dbGetAll($sql);
	}
	
// ***************************************************************************************************************************
   public function getStockMovementStore($prinId, $depotId, $docType) {
   	
          if($docType == DT_ARRIVAL) {
             $stType = 'ARRIVAL';
          } elseif(substr($docType,0,14) == DT_STOCKADJUST) {
             $stType = 'STOCK ADJUSTMENT';
          }  	      
		
           $sql = "SELECT MAX(psm.uid) AS 'psmUid'
                   FROM " . iDATABASE . ".principal_store_master psm
                   WHERE psm.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $prinId) ."
                   AND   psm.depot_uid     = "  . mysqli_real_escape_string($this->dbConn->connection, $depotId) ."
                   AND   psm.old_account   = CONCAT('". mysqli_real_escape_string($this->dbConn->connection, $stType) ."','-', " . mysqli_real_escape_string($this->dbConn->connection, $depotId) .") ";

           return $this->dbConn->dbGetAll($sql);
           
   }

// ***************************************************************************************************************************
   public function insertStockMovementStore($prinId, $depotId, $userId, $docType) {
   	
          if($docType == DT_ARRIVAL) {
             $stType = 'ARRIVAL';
          } elseif($docType == DT_STOCKADJUST_POS || $docType == DT_STOCKADJUST_NEG) {
             $stType = 'STOCK ADJUSTMENT';
          }  	       
          // Get Prin name
          
          $sql = "SELECT if(p.short_name IS NOT NULL,p.short_name,trim(SUBSTR(p.name,1,15))) AS 'PNAME'
                  FROM " . iDATABASE . ".principal p WHERE p.uid = "  . mysqli_real_escape_string($this->dbConn->connection, $prinId) .";";
                  
                  $pName = $this->dbConn->dbGetAll($sql);

          // Get Depot name
          
          $sql = "SELECT trim(d.name) AS 'DNAME'
                  FROM " . iDATABASE . ".depot d
                  WHERE d.uid = "  . mysqli_real_escape_string($this->dbConn->connection, $depotId) .";";
                  
                  $dName = $this->dbConn->dbGetAll($sql);
                  
          $storeName = mysqli_real_escape_string($this->dbConn->connection, $stType) .' - ' . $dName[0]['DNAME'] .' - ' . $pName[0]['PNAME']  ;
          
          $stripStoreName = strtolower(str_replace(' ', '', $storeName));
                 
          
          // Get Generic Principal Chain UID
          
          $sql = "SELECT pcm.uid
                  FROM " . iDATABASE . ".principal_chain_master pcm
                  WHERE pcm.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
                  AND   pcm.old_code = '999';";
                  
                  $chainUid = $this->dbConn->dbGetAll($sql);
          
           $sql = "INSERT INTO " . iDATABASE . ".`principal_store_master` (`principal_uid`, 
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
                           CONCAT('". mysqli_real_escape_string($this->dbConn->connection, $stType) ."','-', " . mysqli_real_escape_string($this->dbConn->connection, $depotId) ."),
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
           
           print_r($this->errorTO);
           echo "<br>Here";

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
   public function stockMovementStockTransaction($PostingDocumentTO) {
   	
          global $ROOT, $PHPFOLDER;

          include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
          
          $principalUId = $PostingDocumentTO->principalUId ;
          $depotUId     = $PostingDocumentTO->depotUId;
          
          if($PostingDocumentTO->documentTypeUId == DT_ARRIVAL) {
          	   $documentTypeColumn = 'arrivals' ;         	
          } elseif($PostingDocumentTO->documentTypeUId == DT_STOCKADJUST_POS || $PostingDocumentTO->documentTypeUId == DT_STOCKADJUST_NEG) {
          	   $documentTypeColumn = 'adjustment' ;         	
          }

          foreach ($PostingDocumentTO->detailArr as $dRow) {          	
          	
                $productDAO = new ProductDAO($this->dbConn);
                $nSI = $productDAO->getNonStockItemByProductUid($dRow->productUId);
                
                if($nSI['non_stock_item'] == "N") { 
                	    
                	    // Check for Stock record
                	    
                	    $sql = "SELECT *
                              FROM " . iDATABASE . ".stock s
                              WHERE principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalUId) ."
                              AND   depot_id     = " . mysqli_real_escape_string($this->dbConn->connection, $depotUId) . " 
                              AND   principal_product_uid = '" . mysqli_real_escape_string($this->dbConn->connection,$dRow->productUId) ."'";                
                
//echo "<br>";
//echo "<pre>";
//echo $sql;
//echo "<br>";
                      $sRecord = $this->dbConn->dbGetAll($sql);
//echo count($sRecord);
                      if(count($sRecord) == 1) {
                                $processedQty = $dRow->documentQty;
                                if($PostingDocumentTO->documentTypeUId == DT_STOCKADJUST_NEG) {
                                    $direction = -1;	
                                } else {
                                    $direction = +1;	
                                }                                
                                $sql= "UPDATE stock SET " . $documentTypeColumn . "  = " . $documentTypeColumn  ." + " . mysqli_real_escape_string($this->dbConn->connection, $processedQty) * $direction .",
                                                          closing   = closing   + " . mysqli_real_escape_string($this->dbConn->connection, $processedQty) * $direction .",
                                                          available = available + " . mysqli_real_escape_string($this->dbConn->connection, $processedQty) * $direction ."
                                       WHERE principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalUId) ."
                                       AND   depot_id     = " . mysqli_real_escape_string($this->dbConn->connection, $depotUId) . " 
                                       AND   principal_product_uid = '" . mysqli_real_escape_string($this->dbConn->connection,$dRow->productUId) ."'";

                                $this->errorTO = $this->dbConn->processPosting($sql,"");

                                if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                                     $this->errorTO->description="Failed to updateStockMovement : ".$this->errorTO->description;
                                      $this->errorTO;
                                }
                      } else {
                                           
                             $arrivalQty = $adjustQty = 0;
                             if($PostingDocumentTO->documentTypeUId == DT_ARRIVAL) {
                                   $processedQty = $dRow->documentQty;
                                   $arrivalQty = abs(mysqli_real_escape_string($this->dbConn->connection, $processedQty));
                             } elseif($PostingDocumentTO->documentTypeUId == DT_STOCKADJUST_POS) {
                                   $processedQty = $dRow->documentQty;
                                   $adjustQty  = abs(mysqli_real_escape_string($this->dbConn->connection, $processedQty));
                             } elseif($PostingDocumentTO->documentTypeUId == DT_STOCKADJUST_NEG) {
                                   $processedQty = $dRow->documentQty * -1;
                                   $adjustQty  = abs(mysqli_real_escape_string($this->dbConn->connection, $processedQty) * -1);
                             }
                
                            $sql="INSERT INTO " . iDATABASE . ".stock (principal_id,
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
                                         " . $arrivalQty . ",
                                         0,
                                         0,
                                         0,
                                         0,
                                         " . $adjustQty . ",
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
                                  FROM   " . iDATABASE . ".principal_product
                                  WHERE  principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalUId) ."'
                                  AND    uid = '" . mysqli_real_escape_string($this->dbConn->connection, $dRow->productUId) . " '";
//echo "hh<br>";
//echo "<pre>";
//echo $sql;
//echo "<br>";
                                  $this->errorTO = $this->dbConn->processPosting($sql,"");
                      }
                }          
          }
          if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
                   $this->dbConn->dbQuery("commit"); 
                   $this->errorTO->description="Update Stock Movement successful";
                   return $this->errorTO;
          }
   }       
}

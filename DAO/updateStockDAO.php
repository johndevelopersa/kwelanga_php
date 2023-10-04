<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    
    
class updateStockDAO {
	private $dbConn;

	function __construct($dbConn) 
	{

       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
	}
  
   //***************************************************************************************************************************************************************************  
  
  public function getUserWarehouses($userID, $principalId) {
  	
    $sql = "SELECT DISTINCT(d.uid) as 'warehouse_uid', 
    							d.name AS 'warehouse' 
						FROM  depot d
						INNER JOIN user_principal_depot upd ON upd.depot_id = d.uid
						WHERE upd.principal_id = ". mysqli_real_escape_string($this->dbConn->connection, $principalId). "
						AND upd.user_id = ". mysqli_real_escape_string($this->dbConn->connection, $userID). " ; " ;

    $depl = $this->dbConn->dbGetAll($sql);

    return $depl;

  }
  
   //***************************************************************************************************************************************************************************
 	
 	public function getMinimumStockQuantity($principalId, $depotId)
 	{
 			//echo $depotId;
 		
 		  	  $sql = "SELECT pp.product_code AS 'Product Code',
												 pp.product_description AS 'Product Description',
		 										 s.minimum_stock_level AS 'Minimum Stock Quantity',
		 										 d.name AS 'Warehouse',
		 										 s.uid AS 'Stock_UID'
								 FROM stock s 
								 INNER JOIN principal_product pp ON s.principal_product_uid = pp.uid
								 LEFT JOIN principal_product_category ppc on ppc.uid = pp.major_category
								 INNER JOIN depot d on d.uid = s.depot_id
								 WHERE s.principal_id = ". mysqli_real_escape_string($this->dbConn->connection, $principalId)  . "
								 AND   s.depot_id = ". mysqli_real_escape_string($this->dbConn->connection, $depotId) . "
								 and   pp.status = 'A'
								 ORDER BY pp.product_description; ";
								 
  	  
              $tRep = $this->dbConn->dbGetAll($sql);
              
              return $tRep; 
 		
 	}
    //***************************************************************************************************************************************************************************

 	public function updateMinimumStockQuantity($stkqty, $stkUid) 	{	
 
       $sql = "UPDATE stock s SET s.minimum_stock_level = ". mysqli_real_escape_string($this->dbConn->connection, $stkqty)  . "
               WHERE  s.uid = ". mysqli_real_escape_string($this->dbConn->connection, $stkUid)  . ";";  

       $this->errorTO = $this->dbConn->processPosting($sql,"");
       
       if($this->errorTO->type == 'S') {
                  $this->dbConn->dbQuery("commit");
                  return $this->errorTO; 
       } else {
                  $this->errorTO->description="Big Problem X003 ";
                   return $this->errorTO; 
       }  	
  }
  
   //***************************************************************************************************************************************************************************

 	public function validateMinimumStockQuantity($stkqty) {
 		
          if (!is_numeric(test_input($stkqty))){          	
               $this->errorTO->type = FLAG_ERRORTO_ERROR;
               $this->errorTO->description="Non Numeric Found in Capture <br><br>Start again :("; 
               return $this->errorTO;  	
          } else {          	
          	    $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
          	    return $this->errorTO;
          	
          } 
  }         		
   //***************************************************************************************************************************************************************************

 }
?>

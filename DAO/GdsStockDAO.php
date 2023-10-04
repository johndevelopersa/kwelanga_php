<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class GdsStockDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  // **************************************************************************************************************************************************** 

  public function deleteGdsTemp() {
  
       $bldsql = "DROP TABLE IF EXISTS gds_stock_temp";
       
       $result = $this->dbConn->dbQuery($bldsql);
  
  }
  // **************************************************************************************************************************************************** 
  public function createGdsTemp() {
  
       $bldsql = "CREATE TABLE gds_stock_temp ( `Stock Code`                 VARCHAR(20)  NULL,
                                                `Stock Description`          VARCHAR(50)  NULL,
                                                `Level`                      VARCHAR(10)  NULL,
                                                `On Order`                   VARCHAR(10)  NULL,
                                                `Reserved`                   VARCHAR(10)  NULL,
                                                `Available`                  VARCHAR(10)  NULL,
                                                `Preferred Supplier Name`    VARCHAR(50)  NULL,
                                                `Warehouse Description`      VARCHAR(50)  NULL);";
                               
       $errorTO = $this->dbConn->processPosting($bldsql,"");
                    
       if($errorTO->type == 'S') {
                 $this->dbConn->dbQuery("commit");
                 return $this->errorTO;
       } else {
                 return $this->errorTO; 	
       }          
  }
  // **************************************************************************************************************************************************** 
  public function loadGdsFile($frow) {  

                $sql='LOAD DATA LOCAL INFILE "' . mysqli_real_escape_string($this->dbConn->connection, $frow) . '" INTO TABLE gds_stock_temp
                    FIELDS TERMINATED BY ","
                    OPTIONALLY ENCLOSED BY "\""
                    ESCAPED BY "\\\"
                    LINES TERMINATED BY "\\r\\n" 
                    IGNORE 1 LINES';	
 
                    $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
                    if($this->errorTO->type == 'S') {
                             $this->dbConn->dbQuery("commit");
                    }  
}  
  
  // **************************************************************************************************************************************************** 

  public function clearStockBalances($principal) {
        
        $sql = "DELETE FROM stock WHERE principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . ";";
  	
       $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
       if($this->errorTO->type == 'S') {
       	    $this->dbConn->dbQuery("commit");
            return $this->errorTO;     	
       } else {
            return "F"	;
       }  	
  }  
  
  // **************************************************************************************************************************************************** 

  public function loadAvailableStock($principal, $fileDate) {
  	
        $sql = "INSERT IGNORE INTO stock (stock.principal_id,
                   stock.depot_id,
                   stock.principal_product_uid,
                   stock.stock_item,
                   stock.stock_descrip,
                   stock.available,
                   stock.stock_count_date,
                   stock.data_generated_date)

                SELECT " . mysqli_real_escape_string($this->dbConn->connection, $principal) . ",
                       d.uid, 
                       pp.uid,
                       pp.product_code, 
                       pp.product_description, 
                       g.Available,
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fileDate) . "',
                       NOW()
                FROM .gds_stock_temp g
                LEFT JOIN .principal_product pp ON pp.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . " 
                                                AND pp.product_code = g.`Stock Code`
                LEFT JOIN .depot d ON TRIM(REPLACE(REPLACE(d.name,' ',''),'GDSC-','')) = TRIM(REPLACE(REPLACE(g.`Warehouse Description`,' ',''),'C-',''))
                WHERE d.uid IS NOT NULL
                ORDER BY g.`Warehouse Description`;";

        $this->errorTO = $this->dbConn->processPosting($sql,"");
                     
        if($this->errorTO->type == 'S') {
             $this->dbConn->dbQuery("commit");
             return $this->errorTO;     	
        } else {
             return $this->errorTO; ;
        }

  }
}  
?>
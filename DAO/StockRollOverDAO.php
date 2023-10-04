<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


class CatStockRollOverDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  
//******************************************************************************************************************************************
public function GetProductCat($PrincipalUID){

            $sql = "SELECT *
                    FROM principal_product_category ppc
                    WHERE ppc.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $PrincipalUID) ."
                    ORDER BY ppc.comments";
            $pCat = $this->dbConn->dbGetAll($sql);
          
     return $pCat;	
  	 }
  	 


//******************************************************************************************************************************************
public function validateRollOver($PrincipalUid, $depotId, $rCat, $rDate){

            $sql = "SELECT s.stock_count_date, 
                           s.closing,
                           s.stock_count,
                           s.stock_item
                    FROM stock s 
                    LEFT JOIN .principal_product pp ON s.principal_product_uid = pp.uid
                    LEFT JOIN .principal_product_category ppc ON ppc.uid = pp.major_category
                    WHERE s.principal_id = ". mysqli_real_escape_string($this->dbConn->connection, $PrincipalUid) ."
                    AND   s.depot_id     = ". mysqli_real_escape_string($this->dbConn->connection, $depotId) ."
                    AND   ppc.uid IN (". mysqli_real_escape_string($this->dbConn->connection, $rCat) .")";
           
            $vRoll = $this->dbConn->dbGetAll($sql);
            
            $contRollover = 'T';	
            foreach($vRoll as $rRow) {
                 /*
                 if($rRow['stock_count_date'] == $rDate ) {
            	   	     // Write to RO Log
            	   	
            	         $contRollover = 'F';	
            	   	
            	   } */
            	   if($rRow['closing'] <> $rRow['stock_count'] ) {
            	   	     print_r($rRow);
                       $contRollover = 'F';	
                       // Write to RO Log
            	   }
            	   
            	               	
            	
            }
            
            return $contRollover;
  	 }


//******************************************************************************************************************************************
public function catStockRollOver($PrincipalUid, $depotId, $rCat, $rDate) {
             
             // Roll Over
             
             $sql = "UPDATE stock s 
                     LEFT JOIN .principal_product pp ON s.principal_product_uid = pp.uid
                     LEFT JOIN .principal_product_category ppc ON ppc.uid = pp.major_category
                                           SET s.last_opening = s.opening, 
                                               s.last_count_date = s.stock_count_date,
                                               s.opening = s.closing,
                                               s.stock_count = s.closing,
                                               s.arrivals = 0,
                                               s.adjustment = 0,
                                               s.returns_cancel = 0,
                                               s.delivered = 0,
                                               s.stock_count_date = '". mysqli_real_escape_string($this->dbConn->connection, $rDate) ."'
                     WHERE s.principal_id = ". mysqli_real_escape_string($this->dbConn->connection, $PrincipalUid) ."
                     AND   s.depot_id     = ". mysqli_real_escape_string($this->dbConn->connection, $depotId) ."
                     AND   ppc.uid IN (". mysqli_real_escape_string($this->dbConn->connection, $rCat) .")";

             $this->errorTO = $this->dbConn->processPosting($sql,"");
           
             if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) { 
                    return ($this->errorTO);       	                  
             } else {
              	   $this->dbConn->dbQuery("commit");
                   return ($this->errorTO);
             }
}

//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************





//************************************************END END END END END END END******************************************************************************************
}
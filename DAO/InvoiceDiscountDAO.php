<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class InvoiceDiscountDAO {
	public   $errorTO;
	private  $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
    }
// *****************************************************************************************************************************************
    public function fetchInvoiceDiscounts($psmUid, $invDate, $principalUid, $totNett, $displayNumber, $disUid) {
    	
        if(mysqli_real_escape_string($this->dbConn->connection, $displayNumber) == 1) {
             $lim = "LIMIT 1";        
        } else {
             $lim = ""; 
      	} 
      	if(mysqli_real_escape_string($this->dbConn->connection, $disUid) == '') {
             $disStat  = "AND   cid.`active` = 'Y'";
             if($lim == 1) {
                  $disRange = "AND   '" . mysqli_real_escape_string($this->dbConn->connection, $invDate) . "' BETWEEN cid.start_date AND cid.end_date";
             } else {
             	    $disRange = '';
             }
        } else {
             $disStat  = "AND   cid.uid = " .mysqli_real_escape_string($this->dbConn->connection, $disUid);
             $disRange = "";
    	  } 
    	
    	  $sql = "SELECT *
                FROM .customer_invoice_discount cid
                WHERE cid.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
                AND   cid.principal_chain_store_uid = " . mysqli_real_escape_string($this->dbConn->connection, $psmUid) . " 
                " . $disRange . "
                AND   cid.minimum_value <= " . mysqli_real_escape_string($this->dbConn->connection, $totNett) . "
                " . $disStat . "
                ORDER BY cid.start_date DESC, cid.end_date ASC, cid.minimum_value DESC
                " . $lim . ";";
    	
         $idisc = $this->dbConn->dbGetAll($sql);
         return $idisc;
    }
// *****************************************************************************************************************************************
    public function fetchCustomerList($principalUid) {

         $sql = "SELECT psm.uid as 'CustID',
                        psm.deliver_name as 'Customer'
                 FROM .principal_store_master psm
                 WHERE psm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
                 AND   psm.`status` = 'A'
                 AND   psm.deliver_name not like '%Stock Movement,%'
                 ORDER BY psm.deliver_name;";

         $cList = $this->dbConn->dbGetAll($sql);
         
         return $cList;
    }
// *****************************************************************************************************************************************
    public function validateInput($fromDate, $endDate, $postDISVAL, $postDISTYPE) {
    	
    	
        if(checkdate(mysqli_real_escape_string($this->dbConn->connection, substr($fromDate,5,2)), mysqli_real_escape_string($this->dbConn->connection, substr($fromDate,8,2)), mysqli_real_escape_string($this->dbConn->connection, substr($fromDate,0,4))) <> 1 ) {
           $this->errorTO->type = 'E';
           $this->errorTO->description="From Date Not valid - Start Again";
           return $this->errorTO;        	
        }
 
        if(checkdate(mysqli_real_escape_string($this->dbConn->connection, substr($endDate,5,2)), mysqli_real_escape_string($this->dbConn->connection, substr($endDate,8,2)), mysqli_real_escape_string($this->dbConn->connection, substr($endDate,0,4))) <> 1 ) {
           $this->errorTO->type = 'E';
           $this->errorTO->description="End Date Not valid - Start Again";
           return $this->errorTO;        	
        }
        
        if(mysqli_real_escape_string($this->dbConn->connection, $fromDate) > mysqli_real_escape_string($this->dbConn->connection, $endDate) ) {
           $this->errorTO->type = 'E';
           $this->errorTO->description="End Date Cannot be before Start Date - Start Again";
           return $this->errorTO;        	
        }
        
        if(mysqli_real_escape_string($this->dbConn->connection, $postDISTYPE) == 'P' &&  mysqli_real_escape_string($this->dbConn->connection, $postDISVAL) > 100 ) {
           $this->errorTO->type = 'E';
           $this->errorTO->description="Discount Percentage cann be mor the 100 - Start Again";
           return $this->errorTO;        	
        }        
        $this->errorTO->type = 'S';
        return $this->errorTO;       
        
     }   
// *****************************************************************************************************************************************
   public function insertDiscountRecord($principalUid, $postFROMDATE, $postENDDATE, $postCUSTUID, $postDISTYPE,$postDISVAL, $postMINVAL, $userUId ) {
   	
       $qsql = "INSERT INTO customer_invoice_discount (customer_invoice_discount.principal_uid,
                                       customer_invoice_discount.principal_chain_store_type,
                                       customer_invoice_discount.principal_chain_store_uid,
                                       customer_invoice_discount.start_date,
                                       customer_invoice_discount.end_date,
                                       customer_invoice_discount.discount_type,
                                       customer_invoice_discount.discount_amount,
                                       customer_invoice_discount.minimum_value,
                                       customer_invoice_discount.last_change_by_userid)
               VALUES (" . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . " ,
                       'S',
                       " . mysqli_real_escape_string($this->dbConn->connection, $postCUSTUID)  . " ,
                       '" . mysqli_real_escape_string($this->dbConn->connection, $postFROMDATE)    . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $postENDDATE)    . "',
                       'P',
                       " . mysqli_real_escape_string($this->dbConn->connection, $postDISVAL)  . " ,
                       " . mysqli_real_escape_string($this->dbConn->connection, $postMINVAL)  . " ,
                       " . mysqli_real_escape_string($this->dbConn->connection, $userUId)     . "); ";

               $this->errorTO = $this->dbConn->processPosting($qsql,"");
       
               if($this->errorTO->type == 'S') {
               	     $this->errorTO->description="Successful";
                     $this->dbConn->dbQuery("commit");
                     return $this->errorTO; 
               } else {
               	     echo "<pre>";
               	     print_r($this->errorTO);
               	     echo "<br>";
               	     echo $qsql;
               	     echo "<br>";
                     $this->errorTO->description="Big Problem ";
                     return $this->errorTO; 
               }              
   }
// *****************************************************************************************************************************************
   public function storeInvoiceDiscountUid($dm_uid, $discount_val)  {
   	
   	     $sql = "UPDATE document_header dh SET dh.off_invoice_discount = " . mysqli_real_escape_string($this->dbConn->connection, $discount_val)  . "
   	             WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $dm_uid)  . ";";
   	             
         echo $sql;

                 $this->errorTO = $this->dbConn->processPosting($sql,"");
       
                 if($this->errorTO->type == 'S') {
                     $this->errorTO->description="Successful";
                     $this->dbConn->dbQuery("commit");
                     return $this->errorTO; 
                 }
    }           
// *****************************************************************************************************************************************

}
?>
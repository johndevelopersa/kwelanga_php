<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');


class PPBCUSTOMERSDAO {
	
	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
}	

// ************************************************************************************************************************************
   public function getCustomerFromAccount($principalUid, $ppbAccount) {
   	
        $sql = "SELECT psm.uid as 'psmUid', 
                       psm.deliver_name, 
                       psm.old_account, 
                       sfd.value, 
                       psm.old_account=sfd.value
                FROM .principal_store_master psm
                LEFT JOIN .special_field_details sfd ON sfd.field_uid = 621 AND sfd.entity_uid = psm.uid
                WHERE psm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection,$principalUid) . "
                AND  sfd.value = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbAccount) . "'" ;
            
        $cList =  $this->dbConn->dbGetAll($sql);
        return $cList;
    
   }                           
// ******************************************************************************************************************
   public function updatePpbSores($psmUid,
                                  $ppbDelName,
                                  $ppbDelAdd1,
                                  $ppbDelAdd2,
                                  $ppbDelAdd3,
                                  $ppbBillName,
                                  $ppbBillAdd1,
                                  $ppbBillAdd2,
                                  $ppbBillAdd3,
                                  $ppbVat,
                                  $ppbCreditLimit,
                                  $ppbBalance,
                                  $ppbHold,
                                  $ppbBranch,
                                  $ppbAccount,
                                  $kosDepotUid) {      
           
           $sql = "UPDATE principal_store_master psm SET psm.deliver_name        = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbDelName)      . "',
                                                         psm.deliver_add1        = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbDelAdd1)      . "',
                                                         psm.deliver_add2        = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbDelAdd2)      . "',
                                                         psm.deliver_add3        = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbDelAdd3)      . "',
                                                         psm.bill_name           = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBillName)     . "',
                                                         psm.bill_add1           = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBillAdd1)      . "',
                                                         psm.bill_add2           = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBillAdd2)     . "',
                                                         psm.bill_add3           = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBillAdd3)     . "',
                                                         psm.vat_number          = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbVat)          . "',
                                                         psm.ledger_credit_limit = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbCreditLimit)  . "',
                                                         psm.ledger_balance      = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBalance)      . "',
                                                         psm.on_hold             = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbHold)         . "',
                                                         psm.branch_code         = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBranch)       . "',
                                                         psm.old_account         = '" . mysqli_real_escape_string($this->dbConn->connection,$ppbAccount)      . "'
                  WHERE psm.uid = " . mysqli_real_escape_string($this->dbConn->connection,$psmUid) ;                                                                                          
            
                  $this->errorTO = $this->dbConn->processPosting($sql,"");

                  if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                          $this->errorTO->description="PPB account Updated Failed : ". $sql .$this->errorTO->description;
                          echo "<br>"; 
                          echo $sql;
                          echo "<br>";
                          
                          return $this->errorTO;         	                  
                  } else {
                          $this->dbConn->dbQuery("commit");
                          $this->errorTO->description="PPB account Updated Successful";
                          return $this->errorTO;                
                  }                      
        }              
// ******************************************************************************************************************
   public function insertPpbSores($prinUid,
                                  $ppbDelName,
                                  $ppbDelAdd1,
                                  $ppbDelAdd2,
                                  $ppbDelAdd3,
                                  $ppbBillName,
                                  $ppbBillAdd1,
                                  $ppbBillAdd2,
                                  $ppbBillAdd3,
                                  $ppbVat,
                                  $ppbDepot,
                                  $kosChainUid,
                                  $kosAltChainUid,
                                  $ppbCreditLimit,
                                  $ppbBalance,
                                  $ppbHold,
                                  $ppbBranch,
                                  $ppbAccount) {

      $sql = "INSERT INTO `principal_store_master` (`principal_uid`,
                                                   `deliver_name`, 
                                                   `deliver_add1`, 
                                                   `deliver_add2`, 
                                                   `deliver_add3`, 
                                                   `bill_name`, 
                                                   `bill_add1`, 
                                                   `bill_add2`, 
                                                   `bill_add3`,
                                                   `vat_number`, 
                                                   `principal_chain_uid`,
                                                   `alt_principal_chain_uid`,
                                                   `branch_code`,
                                                   `ledger_credit_limit`, 
                                                   `ledger_balance`,       
                                                   `on_hold`,
                                                   `old_account`)
              VALUES ("  . mysqli_real_escape_string($this->dbConn->connection,$prinUid)         . ",
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbDelName)      . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbDelAdd1)      . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbDelAdd2)      . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbDelAdd3)      . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBillName)     . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBillAdd1)     . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBillAdd2)     . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBillAdd3)     . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbVat)          . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$kosChainUid)     . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$kosAltChainUid)  . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBranch)       . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbCreditLimit)  . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbBalance)      . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbHold)         . "',  
                      '" . mysqli_real_escape_string($this->dbConn->connection,$ppbAccount)      . "')";
                  //echo $sql;
                  $this->errorTO = $this->dbConn->processPosting($sql,"");

                  if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                          $this->errorTO->description="PPB account Insert Failed : ". $sql .$this->errorTO->description;
                          echo "<br>"; 
                          echo $sql;
                          echo "<br>";
                          
                          return $this->errorTO;         	                  
                  } else {
                          $this->dbConn->dbQuery("commit");
                          $this->errorTO->description="PPB account Updated Successful";
                  }
                  
                  $lastPsmUid = $this->dbConn->dbGetLastInsertId();
                
                  $sql = "INSERT INTO special_field_details(special_field_details.field_uid,
                                                          special_field_details.entity_uid,
                                                          special_field_details.value)
                          VALUES (621," . $lastPsmUid . ",'" . mysqli_real_escape_string($this->dbConn->connection,$ppbAccount) . "');";                                   
                
                  $this->errorTO = $this->dbConn->processPosting($sql,"");

                  if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                          $this->errorTO->description="PPB account insert Failed : ". $sql .$this->errorTO->description;
                          echo "<br>"; 
                          echo $sql;
                          echo "<br>";
                          
                          return $this->errorTO;         	                  
                  } else {
                          $this->dbConn->dbQuery("commit");
                          $this->errorTO->description="PPB account insert Successful";
                          return $this->errorTO;                
                  }                     


    }
// ******************************************************************************************************************
   public function getWareHouseMapping() {
   
       $sql = "SELECT sfd.value, sfd.entity_uid
               FROM special_field_details sfd
               WHERE sfd.field_uid = 627";
       
       $whList =  $this->dbConn->dbGetAll($sql);
       return $whList;
   }
// ******************************************************************************************************************
   public function getPrincipalChainMapping() {
   
       $sql = "SELECT sfd.value, sfd.entity_uid
               FROM special_field_details sfd
               WHERE sfd.field_uid = 629";
       
       $chList =  $this->dbConn->dbGetAll($sql);
       return $chList;
   }
// ******************************************************************************************************************
      
}   
?>  
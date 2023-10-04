<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');

class PostPaymentDAO {
	private $dbConn;

	  function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }
    public function insertPaymentDetails($principalId, $dmUid, $ptype, $deliveryDate, $paymentAmount) {
  	
     	// Get store UID
         $gssql = " SELECT dh.principal_store_uid 
                   FROM document_header dh 
                   WHERE dh.document_master_uid = '{$dmUid}'; ";
          	
         $storeUid = $this->dbConn->dbGetAll($gssql);
          	
       // Get payment sequence No
          $sequenceDAO = new SequenceDAO($this->dbConn);
          $sequenceTO = new SequenceTO;
          $errorTO = new ErrorTO;
          $sequenceTO->sequenceKey=LITERAL_SEQ_PAYMENT;
          $sequenceTO->principalUId = $principalId;
          $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
          
          if ($result->type!=FLAG_ERRORTO_SUCCESS) {
             return $result;
          }          	
       // Insert into payment header and detail
  	
  	      $isql = "INSERT INTO `payment_header` (`type_uid`,
  	                                             `principal_uid`,
                                                 `payment_number`, 
                                                 `payment_date`,
                                                 `principal_store_uid`, 
                                                 `amount`) 
                   VALUES ({$ptype},
                           {$principalId},
                           {$seqVal}, 
                          '{$deliveryDate}', 
                          '{$storeUid[0]['principal_store_uid']}',
                          '-{$paymentAmount}');";
                              
            $this->errorTO = $this->dbConn->processPosting($isql,"");

            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                $this->errorTO->description="Payment Header Update Failed failed : ". $isql .$this->errorTO->description;
                return $this->errorTO;         	                  
            } 
            if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
            	    $pdUId = $this->dbConn->dbGetLastInsertId();
            	    
                  $idsql = "INSERT INTO `payment_detail` (`payment_header_uid`, 
                                                    `document_master_uid`, 
                                                    `payment_amount`) 
                            VALUES ( {$pdUId}, 
                                    '{$dmUid}' , 
                                    '-{$paymentAmount}');";

                 $this->errorTO = $this->dbConn->processPosting($idsql,"");
      
                 if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                    $this->errorTO->description="Payment Detail Update Failed failed : ".$this->errorTO->description;
                    return $this->errorTO;         	                  
                 }
                 return $this->errorTO;                
            }
    }  
}
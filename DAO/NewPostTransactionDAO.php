<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class PostNewTransactionDAO {
	  public $errorTO;
	  private $dbConn;

	  function __construct($dbConn) {
		    $this->dbConn = $dbConn;
		   	$this->errorTO = new ErrorTO;
    }

// **********************************************************************************************************************************
    public function invoiceDetailLine($ddUID, $ddDocQty, $ddNetPrice) {
    	
    	  $docQty   = mysqli_real_escape_string($this->dbConn->connection, $ddDocQty); 
    	  $netPrice = mysqli_real_escape_string($this->dbConn->connection, $ddNetPrice);
    	  $extPrice = $docQty * $netPrice;
    	  $vatAmt   = round($docQty * $netPrice * 15.00/100,2);
    	  $inTot    = round($docQty * $netPrice + $docQty * $netPrice * 15.00/100,2);
    	  
  	
        $sql = "update document_detail dd set dd.document_qty     = " . $docQty   . " , 
                                              dd.net_price        = " . $netPrice . " ,
                                              dd.extended_price   = " . $extPrice . " ,
                                              dd.vat_amount       = " . $vatAmt   . " ,
                                              dd.total            = " . $inTot    . "
              where dd.uid = " . mysqli_real_escape_string($this->dbConn->connection, $ddUID) . ";";
        $this->errorTO = $this->dbConn->processPosting($sql,"");

        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description="Failed to Update : ".$this->errorTO->description;
            return $this->errorTO;
        } else {
        	$this->dbConn->dbQuery("commit");
        }
  
        return $this->errorTO;	
  	
    }  
// **********************************************************************************************************************************
    public function invoiceHeader($docId, $ownInvoice, $invoiceDate, $docStatus  ) {
  	
         $sql="update document_header dh set dh.invoice_date        = '" . mysqli_real_escape_string($this->dbConn->connection, $invoiceDate) . "',
                                             dh.invoice_number      = '" . mysqli_real_escape_string($this->dbConn->connection, $ownInvoice)  . "',
                                             dh.document_status_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $docStatus)   . "
               where dh.document_master_uid = ".mysqli_real_escape_string($this->dbConn->connection, $docId) . ";";
            
               $this->errorTO = $this->dbConn->processPosting($sql,"");
               
//               echo $sql;

         if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
             $this->errorTO->description="Failed to Update : ".$this->errorTO->description;
             return $this->errorTO;
         } else {
        	   $this->dbConn->dbQuery("commit");
         }
  
         return $this->errorTO;	
  	
    }
// **********************************************************************************************************************************
    public function recalulateHeader($docId) {
	
         $usql = "update document_header dh set     dh.cases           = (select sum(dd.document_qty)
                                                                          from   document_detail dd
                                                                          where  dd.document_master_uid = " . $docId . "),
                                                    dh.exclusive_total = (select sum(dd.extended_price)
                                                                          from .document_detail dd
                                                                          where dd.document_master_uid = "  . $docId . "),
                                                    dh.vat_total       = (select sum(dd.vat_amount)
                                                                          from .document_detail dd
                                                                          where dd.document_master_uid = "  . $docId . "),
                                                    dh.invoice_total   = (select sum(dd.total)
                                                                          from .document_detail dd
                                                                           where dd.document_master_uid = "  . $docId . ")
                  where dh.document_master_uid = " . $docId . " ;" ;
				
				 $this->errorTO = $this->dbConn->processPosting($usql,"");	
				  	
         if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                 $this->errorTO->description="Failed to Update : ".$this->errorTO->description;
                 return $this->errorTO;
         } else {
        	       $this->dbConn->dbQuery("commit");
         }
  
         return $this->errorTO;
    }	
// **********************************************************************************************************************************
}
?>
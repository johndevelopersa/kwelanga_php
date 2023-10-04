<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class glacialUpdateDAO {

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  // **************************************************************************************************************************************************** 
  public function getGlacialPrinInvoiceFiles() {

       $sql = "SELECT ofp.root_dir_constant, 
                      ofp.file_path, 
                      ofp.file_wildcard, 
                      ofm.principal_uid
               FROM .online_file_processing ofp
               LEFT JOIN .online_file_processing_mapping ofm ON ofm.online_file_processing_uid = ofp.uid
               WHERE ofp.adaptor_name = 'GDS_INV';";

          $fResult = $this->dbConn->dbGetAll($sql);
          
          return $fResult;
   }       
  // **************************************************************************************************************************************************** 
  public function loadGlacialFile($frow) {  

                $sql='LOAD DATA LOCAL INFILE "' . mysqli_real_escape_string($this->dbConn->connection, $frow) . '" INTO TABLE glacial_invoice_update
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
      public function manageInvoiceTransactions($prinId, $refField, $ONField, $sDField, $qtyField, $fileName) {

        $sql = "SELECT pp.product_code, 
                       pp.uid,
                       dm.uid as 'docUid', 
                       dm.document_number,
                       dh.uid as 'dhUid',
                       dh.document_status_uid,
                       dd.uid AS 'ddUid',
                       dd.product_uid,
                       dd.document_qty,
                       dh.invoice_date,
                       dd.ordered_qty,
                       psm.uid AS 'psmUid',
                       `" . mysqli_real_escape_string($this->dbConn->connection, $qtyField) . "` AS 'qtyField' ,
                       `" . mysqli_real_escape_string($this->dbConn->connection, $refField) . "` AS 'refField'
                  FROM " . $fileName . " t
                  LEFT JOIN principal_product pp ON pp.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
                                                  AND pp.product_code = SUBSTR(`" . mysqli_real_escape_string($this->dbConn->connection, $sDField) . "`,1, POSITION(' ' IN trim(`" . mysqli_real_escape_string($this->dbConn->connection, $sDField) . "`)))
                  LEFT JOIN document_master dm ON dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . " 
                                                AND trim(LEADING '0' from dm.document_number) = " . mysqli_real_escape_string($this->dbConn->connection, $ONField) . "
                  LEFT JOIN document_header dh ON dh.document_master_uid = dm.uid 
                  LEFT JOIN document_detail dd ON dd.document_master_uid = dm.uid  AND dd.product_uid = pp.uid                                
                  LEFT JOIN principal_store_master psm on psm.uid = dh.principal_store_uid
                  WHERE SUBSTR(" . mysqli_real_escape_string($this->dbConn->connection, $refField) . ",1,1) = 'T';";
                  

          $utresult = $this->dbConn->dbGetAll($sql);
          
          return $utresult;
   }
  // **************************************************************************************************************************************************** 
      public function updateGlacialInvoiceTransactions($dmUid, $ppUid, $iDate, $docQty, $iNum) {

          $sql = "UPDATE document_master dm
                  LEFT JOIN document_header dh ON dh.document_master_uid = dm.uid 
                  LEFT JOIN document_detail dd ON dd.document_master_uid = dm.uid  
                                               AND dd.product_uid = " . mysqli_real_escape_string($this->dbConn->connection, $ppUid) . "
                  LEFT JOIN principal_store_master psm ON psm.uid = dh.principal_store_uid 
                                                       SET dh.document_status_uid = " . DST_INVOICED . ",
                                                           dh.invoice_number = '" . mysqli_real_escape_string($this->dbConn->connection, trim(substr($iNum,1,6))) . "',
                                                           dh.invoice_date   = '" . mysqli_real_escape_string($this->dbConn->connection, $iDate) . "',
                                                           dd.document_qty   = '" . mysqli_real_escape_string($this->dbConn->connection, $docQty) . "',
                                                                                                                      dd.extended_price = dd.net_price * dd.document_qty,
                                                           dd.vat_amount     = dd.net_price * '" . mysqli_real_escape_string($this->dbConn->connection, $docQty) . "' * dd.vat_rate/100,
                                                           dd.total    = (dd.net_price * '" . mysqli_real_escape_string($this->dbConn->connection, $docQty) . "') + (dd.net_price * '" . mysqli_real_escape_string($this->dbConn->connection, $docQty) . "' * dd.vat_rate/100),
                                                           psm.retailer = 2
                  WHERE dm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $dmUid) . "
                  and   dh.document_status_uid <> " . DST_INVOICED . ";";

            $this->errorTO = $this->dbConn->processPosting($sql,"");
            
            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                     $this->errorTO->description="Document Update Failed  : ".$this->errorTO->description;
                     return $this->errorTO;       	                  
            } else {
                     $this->dbConn->dbQuery("commit");                   	                 
                     return $this->errorTO;      
            }
       }  
  
  // **************************************************************************************************************************************************** z
}  

  // **************************************************************************************************************************************************** z



?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class ProcessFilesDAO {
	private $dbConn;

   function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
   }
// **************************************************************************************************************************
      public function dropTempFilesTable($userUId) {

           $bldsql = "DROP TABLE IF EXISTS file_temp_" .mysqli_real_escape_string($this->dbConn->connection, $userUId) ;

           $result = $this->dbConn->dbQuery($bldsql);
           
           $this->dbConn->dbQuery("commit");
           
      }
//******************************************************************************************************************************************************
      public function createTempFilesTable($userUId) {

             $bldsql = "CREATE TABLE file_temp_" .mysqli_real_escape_string($this->dbConn->connection, $userUId) . " (
                                                `fld1`              VARCHAR(60)  NULL,
                                                `fld2`              VARCHAR(60)  NULL,
                                                `fld3`              VARCHAR(60)  NULL,
                                                `fld4`              VARCHAR(60)  NULL,
                                                `fld6`              VARCHAR(60)  NULL,
                                                `fld7`              VARCHAR(60)  NULL,
                                                `fld8`              VARCHAR(60)  NULL,
                                                `fld9`              VARCHAR(60)  NULL,
                                                `fld10`             VARCHAR(60)  NULL) ";

               $dtresult = $this->dbConn->dbQuery($bldsql);
               
               $this->dbConn->dbQuery("commit");
               
      }
//*************************************************************************************************************************************************
      public function uploadFileDataTemp($fname, $userUId) {
      	
           global $ROOT;
      	
           $dirPath = $ROOT. "ftp/file_upload/";
           
           $sql='LOAD DATA LOCAL INFILE "' . $dirPath . $fname . '" INTO TABLE file_temp_' .mysqli_real_escape_string($this->dbConn->connection, $userUId) . '
                 FIELDS TERMINATED BY ","
                 OPTIONALLY ENCLOSED BY "\""
                 ESCAPED BY "\\\"
                 LINES TERMINATED BY "\\r\\n" 
                 IGNORE 1 LINES';	
           $rTO = $this->dbConn->processPosting($sql,"");
           $this->dbConn->dbQuery("commit");
      	
      }	
//*************************************************************************************************************************************************
      public function manageInvoiceTransactions($prinId, $reF, $ON, $sD, $qty) {
      	
      	if($reF ==  'A'){
             $refField = 'fld1';	
      	} elseif($reF ==  'B') {
             $refField = 'fld2';	
      	} elseif($reF ==  'C') {      	
             $refField = 'fld3';	
      	} elseif($reF ==  'D') {      	
             $refField = 'fld4';	
      	} elseif($reF ==  'E') {
             $refField = 'fld5'	;
      	} elseif($reF ==  'F') {      		
             $refField = 'fld5';;	
      	} elseif($reF ==  'G') {      		
            $refField = 'fld6';	
      	}      		
      		
      	if($ON ==  'B'){
             $ONField = 'fld2';	
      	} elseif($ON ==  '2') {
             $ONField = 'fld3';	
      	} elseif($ON ==  'D') {      	
             $ONField = 'fld4';	
      	} elseif($ON ==  'E') {      	
             $ONField = 'fld5';	
      	} elseif($ON ==  'F') {
             $ONField = 'fld6'	;
      	} elseif($ON ==  'G') {      		
             $ONField = 'fld7';;	
      	} elseif($ON ==  'H') {      		
            $ONField = 'fld8';	
      	}      		      		
      		
      	if($sD ==  'C'){
             $sDField = 'fld4';	
      	} elseif($sD ==  'D') {
             $sDField = 'fld5';	
      	} elseif($sD ==  'E') {      	
             $sDField = 'fld6';	
      	} elseif($sD ==  'F') {      	
             $sDField = 'fld7';	
      	} elseif($sD ==  'G') {
             $sDField = 'fld8'	;
      	} elseif($sD ==  'H') {      		
             $sDField = 'fld9';;	
      	} elseif($sD ==  'I') {      		
            $sDField = 'fld10';	
      	}      		      		
      		
      	if($qty ==  'C'){
             $qtyField = 'fld4';	
      	} elseif($qty ==  'D') {
             $qtyField = 'fld5';	
      	} elseif($qty ==  'E') {      	
             $qtyField = 'fld6';	
      	} elseif($qty ==  'F') {      	
             $qtyField = 'fld7';	
      	} elseif($qty ==  'G') {
             $qtyField = 'fld8'	;
      	} elseif($qty ==  'H') {      		
             $qtyField = 'fld9';;	
      	} elseif($qty ==  'I') {      		
            $qtyField = 'fld10';	
      	}      		      		
      		
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
                       `" . mysqli_real_escape_string($this->dbConn->connection, $qtyField) ."`,
                       `" . mysqli_real_escape_string($this->dbConn->connection, $refField) . "`
                  FROM .file_temp_11 t
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
//*************************************************************************************************************************************************
      public function updateInvoiceTransactions($dmUid, $ppUid, $iDate, $docQty, $iNum) {

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
//*************************************************************************************************************************************************


}
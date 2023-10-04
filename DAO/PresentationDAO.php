<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php'); 
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');   	

class PresentationDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// *************************************************************************************************************************
  public function getRepList($principalId) {

    $sql = "SELECT *
             FROM .principal_sales_representative psr
             WHERE psr.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
             AND   psr.`status` = 'A'
             ORDER BY psr.first_name";
            
    $mfDDU = $this->dbConn->dbGetAll($sql);

    return $mfDDU;
  }

// *************************************************************************************************************************
  public function getStoresToBeMailed($principalId, $repUid, $startDate, $endDate) {
  	
       $sql = "SELECT psr.uid, 
                      psr.first_name, 
                      psm.deliver_name, 
                      dm.document_number,
                      dm.uid AS 'Document_Uid',
                      dh.invoice_date,
                      dh.cases
               FROM .principal_sales_representative psr
               INNER JOIN .principal_store_master psm ON psm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . " AND psm.principal_sales_representative_uid = psr.uid
               INNER JOIN .document_header dh ON dh.principal_store_uid = psm.uid AND  dh.invoice_date BETWEEN '" .mysqli_real_escape_string($this->dbConn->connection, $startDate) . "' 
                                                                                                       AND     '" .mysqli_real_escape_string($this->dbConn->connection, $endDate) . "'
                                                                                  AND dh.document_status_uid = " . DST_UNACCEPTED . "                     
               INNER JOIN .document_master dm ON dm.uid = dh.document_master_uid AND dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               WHERE psr.uid = " .mysqli_real_escape_string($this->dbConn->connection, $repUid) . "
               AND   psm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               ORDER BY dh.invoice_date ;" ; 	
               
//               echo $sql;
               
       $repl = $this->dbConn->dbGetAll($sql);

       return $repl;

  	}
  
// *************************************************************************************************************************
  public function getMailDetails($principalId, $docNo, $repID) {
  	
       $sql = "SELECT dm.document_number, 
                      psm.deliver_name,
                      psm.branch_code, 
                      psr.email_addr, 
                      p.name AS 'Principal'
               FROM        document_master dm
               INNER JOIN  document_header dh ON dm.uid = dh.document_master_uid
               INNER JOIN  principal_store_master psm ON psm.uid = dh.principal_store_uid
               INNER JOIN  principal p ON p.uid = dm.principal_uid,
               principal_sales_representative psr
               WHERE dm.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               AND   dm.document_number IN (" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . ")
               AND   psr.uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $repID) . ") ;";
               
       $mDetails = $this->dbConn->dbGetAll($sql);

       return $mDetails;

  	}
// *************************************************************************************************************************
  public function setMailedStatus($principalId, $docNo) {
  	
       $sql = "UPDATE document_master dm
               INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid SET dh.document_status_uid = " . DST_ACCEPTED . "
               WHERE dm.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               AND   dm.document_number IN (" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . ");"; 	
               
       
                $this->errorTO = $this->dbConn->processPosting($sql,"");
       
                if($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                    $this->dbConn->dbQuery("commit");
                }
                
                $seupdate = $this->errorTO->type;
                
       return $seupdate;

  	}    	  
// *************************************************************************************************************************
}

?>
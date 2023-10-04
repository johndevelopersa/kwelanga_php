<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class MaintenanceNewDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  // **************************************************************************************************************************************************** 
  public function getDocumentDetailsToUpdate($principalId, $docNo) {
  	
    $sql = "SELECT dm.document_number,
                   dm.order_sequence_no,
                   psm.deliver_name, 
                   o.delivery_instructions,
                   dh.customer_order_number,
                   dm.uid,
                   dh.document_status_uid,
                   s.description as 'Status',
                   dm.depot_uid,
                   d.name as 'Depot',
                   psm.uid as 'StoreUid' 
           FROM document_master dm, 
                orders o, 
                principal p,
                document_header dh, 
                principal_store_master psm, 
                depot d,
                `status` s
           WHERE dm.uid = dh.document_master_uid 
           AND   dm.order_sequence_no = o.order_sequence_no 
           AND dh.principal_store_uid = psm.uid
           AND dm.principal_uid = p.uid
           AND dm.depot_uid = d.uid
           AND dh.document_status_uid = s.uid
           AND dm.document_type_uid in (1,6,13,27,2,32,4)
           AND dm.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . " 
           AND dm.document_number like '%" . trim(mysqli_real_escape_string($this->dbConn->connection, $docNo)) . "%';";
        
    $mfDDU = $this->dbConn->dbGetAll($sql);

    return $mfDDU;
  }
  // **************************************************************************************************************************************************** 

  public function resetDocumentstatus($orderSeq) {
  	
 	    $sql="update document_header dh set dh.document_status_uid = " . DST_INVOICED . "  
          where dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $orderSeq). "; ";

    $this->errorTO = $this->dbConn->processPosting($sql,"");

    if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
      $this->errorTO->description="Reset Status : ".$this->errorTO->description;
      return $this->errorTO;
    }

    return $this->errorTO;

  }

  // **************************************************************************************************************************************************** 



}  
?>
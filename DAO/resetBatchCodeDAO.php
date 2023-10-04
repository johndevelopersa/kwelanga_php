<?php
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class resetBatchCodeDAO {
	private $dbConn;

	function __construct($dbConn) 
	{

       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
	}

//***************************************************************************************************************************************************************************  

	public function getSessionVariable($principalID, $depotID){
		
		$sql = "SELECT dm.principal_uid, 
									 d.uid
						FROM .document_master dm
						INNER JOIN .document_detail dd ON dd.document_master_uid = dm.uid
						INNER JOIN .principal_product pp ON pp.uid = dd.product_uid
						INNER JOIN .principal p ON pp.principal_uid = p.uid
						INNER JOIN .depot d ON d.uid = dm.depot_uid
						WHERE dm.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalID). "
						AND d.uid = ". mysqli_real_escape_string($this->dbConn->connection, $depotID). " ; " ;
						
		$depl = $this->dbConn->dbGetAll($sql);

    return $depl;

	}
	
//*************************************************************************************************************************************************************************** 	
	
		public function getBatchCodeInfo($docNo, $principalID, $prodCode){

	  if(trim($prodCode) <> '') {
	  	  $prodline = "AND pp.product_code IN ('" . mysqli_real_escape_string($this->dbConn->connection, $prodCode). "')";
	  } else {
	  	  $prodline = "";
	  }
    
    $sql = "SELECT dd.uid AS 'UID',
    							 dm.document_number AS 'Document Number', 
    							 p.name AS 'Principal Name', 
    							 pp.product_code AS 'Product Code', 
    							 pp.product_description AS 'Product Description', 
    							 dd.document_qty AS 'Document Quantity', 
    							 dd.batch AS 'Batch Code'
						FROM .document_master dm
						INNER JOIN .document_detail dd ON dd.document_master_uid = dm.uid
						INNER JOIN .principal_product pp ON pp.uid = dd.product_uid
						INNER JOIN .principal p ON pp.principal_uid = p.uid
						LEFT JOIN .smart_event se ON se.data_uid = dm.uid AND se.`type` ='EXT'
						WHERE dm.principal_uid =  ". mysqli_real_escape_string($this->dbConn->connection, $principalID). "
						AND dm.document_number IN (" . mysqli_real_escape_string($this->dbConn->connection, $docNo) .")
						" . $prodline . "
						ORDER BY dd.line_no,pp.product_code;";	
		
		$depl = $this->dbConn->dbGetAll($sql);

    return $depl;
	
	}

//*************************************************************************************************************************************************************************** 	

	public function updateBatchNumber($batchCode, $UID) {
		
		$sql = "UPDATE document_detail dd SET dd.batch = '". mysqli_real_escape_string($this->dbConn->connection, $batchCode)  . "'                                   
            WHERE dd.uid = " . mysqli_real_escape_string($this->dbConn->connection, $UID) . ";";


	$this->errorTO = $this->dbConn->processPosting($sql,"");
       
       if($this->errorTO->type == 'S') {
                  $this->dbConn->dbQuery("commit");
                  return $this->errorTO; 
       } else {
                  $this->errorTO->description="Error Updating Records ";
                   return $this->errorTO; 
       }  
    
	}

//*************************************************************************************************************************************************************************** 
}
?>
<?php
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

class ManageClaimsDAO {
	private $dbConn;

	function __construct($dbConn) 
	{

       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
	}

//***************************************************************************************************************************************************************************  

	public function getClaimlist($principalID, $startDate) {
		
		
   $sql =  "SELECT TRIM(LEADING '0' FROM dm.document_number) AS 'Docno',
                   dm.uid as 'dmUid',
                   dh.invoice_date,
                   s.uid AS 'StatUid',
                   s.description AS 'Status',
                   dt.description AS 'DT',
                   dm.additional_type AS 'Add_Type',
                   dt.uid,
                   psm.deliver_name,
                   dh.customer_order_number,
                   depot.name AS 'WH',
                   depot.short_name AS 'Short_Wh',
                   dh.cases,
                   round(dh.invoice_total,2) AS 'Total'
            FROM .document_master dm
            INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid
            INNER JOIN .document_type dt ON dt.uid = dm.document_type_uid
            INNER JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
            INNER JOIN .depot ON depot.uid = dm.depot_uid
            INNER JOIN .`status` s ON s.uid = dh.document_status_uid
            WHERE dm.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalID). " 
            AND   dm.document_type_uid = ". DT_BUYER_ORIGINATED_CREDIT_CLAIM . "
            AND   dh.invoice_date > '". mysqli_real_escape_string($this->dbConn->connection, $startDate). " '
            AND   s.uid = " .DST_PROCESSED ;
						
   $clmlst = $this->dbConn->dbGetAll($sql);

    return $clmlst;

	}
	
//*************************************************************************************************************************************************************************** 	
   public function getClaimStartDate($principalID) { 
        
        $sql = "SELECT pp.checkers_ws_starting_claims_date
                FROM .principal_preference pp
                WHERE pp.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalID). ";";	

        $clmStart = $this->dbConn->dbGetAll($sql);

        return $clmStart;
   }
}
?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class RemittanceDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }
    
  public function getCaptureDateArray($principalId)
  {
    $sql="SELECT uid,capture_date,vendor_reference,total_amount
			  FROM `document_remittance`
			  WHERE principal_uid = '". mysqli_real_escape_string($this->dbConn->connection, $principalId) ."'";
			  
// echo $sql;

		$this->dbConn->dbQuery($sql);

		$arr = array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
        $arr[] = $row;
			}
		}
    return $arr;
	}
  
  public function getRemittanceArray($principalId) 
  {
                                
    global $errorTO, $dbConn;
    global $paramsArr, $reportSQLTO, $reportDAO, $postREPORTID, $userId, $principalId, $principalCode; 
    
    $dbConn->dbQuery("SET time_zone='+0:00'");
    
		$sql="select dr.uid,dr.capture_date,drd.original_amount,drd.invoice_reference,dr.insert_datetime,'&2' as 'period'
			from   document_remittance dr,
                   document_remittance_detail drd
                   where  dr.uid = drd.document_remittance_uid
            and    drd.document_type = 'CREDIT_NOTE' 
            and    dr.principal_uid = '&principalId' 
            and dr.uid in (&1)
            and drd.`type` = 'header'
            and drd.document_type = 'CREDIT_NOTE'
            and drd.invoice_reference NOT LIKE 'Z%'
            ORDER BY drd.invoice_reference";
            
    if ($postREPORTID!="") {
            $reportDAO->reportSQL_prepareStatement ($sql,
                                                    $reportSQLTO->reportRow,
                                              $reportSQLTO->database,
                                                    $postREPORTID,
                                                    $userId,
                                                    $principalId,
                                                    $principalCode,
                                                    $paramsArr);
     
}


		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}
 }
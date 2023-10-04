<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

class checkDecimalTotalsDAO {
	private $dbConn;

	function __construct($dbConn) {

       $this->dbConn = $dbConn;
    }
  // **************************************************************************************************************************************************** 

  public function getDocsToUpdate($prnUid) {
 	  
      $sql = "SELECT dm.uid as 'dmUid', 
                     dm.document_number, 
                     pp.allow_decimal,
                     dh.document_status_uid,
                     dd.ordered_qty,
                     dd.document_qty,
                     dd.extended_price,
                     dd.total
              FROM .document_master dm
              INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid
              INNER JOIN .document_detail dd ON dd.document_master_uid = dm.uid
              INNER JOIN .principal_product pp ON pp.uid = dd.product_uid
              WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prnUid) . "
              AND   dm.check_decimal_headers in ('P','N');";

      $gBBQ = $this->dbConn->dbGetAll($sql);
      return $gBBQ;
  
  }
  // **************************************************************************************************************************************************** 

  public function updateDocsNoAction($dmUid) {
 	  
      $sql = "UPDATE document_master dm SET dm.check_decimal_headers = 'Y'
              WHERE dm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $dmUid) . ";";

      $errorTO =$this->dbConn->processPosting($sql,"");
      $this->dbConn->dbQuery("commit");
  
  }  
  // **************************************************************************************************************************************************** 
  public function updateDocsAction($checkDocument, 
                                   $updateDecHeader,
                                   $csTot,
                                   $exTot,
                                   $totTot) {

      $sql = "UPDATE document_master dm 
              INNER JOIN .document_detail dd ON dd.document_master_uid = dm.uid
              INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid 
                                   SET dh.decimal_updated       = '" . mysqli_real_escape_string($this->dbConn->connection, $updateDecHeader) . "',
                                       dm.check_decimal_headers = '" . mysqli_real_escape_string($this->dbConn->connection, $updateDecHeader) . "',
                                       dh.cases                 = "  . mysqli_real_escape_string($this->dbConn->connection, $csTot) . ",
                                       dh.exclusive_total       = "  . mysqli_real_escape_string($this->dbConn->connection, $exTot) . ",
                                       dh.invoice_total         = "  . mysqli_real_escape_string($this->dbConn->connection, $totTot) . "
              WHERE dm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $checkDocument);

      $errorTO =$this->dbConn->processPosting($sql,"");
      $this->dbConn->dbQuery("commit");
      }
  // **************************************************************************************************************************************************** 

}
?>
                                       
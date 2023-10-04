<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

/* NOTES:
 * All functions in this DAO are not mainstream functions, often only used once-off for a specific screen.
 * It is separated out like this to keep TransactionDAO from growing too large, as well as to provide
 * for an alternative to putting the sql directly into the presentation layer / calling script.
 * 
 * This satisfies an abstracted, multi-tiered architecture.
 */
 
class TransactionDedicatedDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

	public function getCrossReferenceDocumentDetail($principalUId, $storeUId, $documentType, $documentNumber) {
    $pDocumentNumber = str_pad(trim($documentNumber),8,"0",STR_PAD_LEFT);
    
		$sql="select a.uid dm_uid, c.product_uid, c.ordered_qty, c.document_qty, c.delivered_qty
          from   document_master a,
               document_header b,
               document_detail c
          where  a.uid = b.document_master_uid
          and    a.uid = c.document_master_uid
          and    a.principal_uid = '{$principalUId}'
          and    b.principal_store_uid = '{$storeUId}'
          and    a.document_type_uid = '{$documentType}'
          and    a.document_number = '{$pDocumentNumber}' ";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysql_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

}
?>

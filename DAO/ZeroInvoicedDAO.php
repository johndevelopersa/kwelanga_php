<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


class ZeroInvoicedDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
//*********************************************************************************************************************************************************************8
 public function getZeroInvoices($principalId, $depUId, $contactUid) {

  If($depUId == NULL) {
      $depSel = ''; 	
  } else {
      $depSel = 'AND dm.depot_uid = ' . mysqli_real_escape_string($this->dbConn->connection, $depUId); 
  }

	 $sql = "SELECT p.name AS 'Principal',
	                p.short_name AS  'prinShort',
	                dm.uid AS 'docUid',
	                dm.document_number AS 'Document_Number', 
	                dh.invoice_date,
                  psm.deliver_name AS 'Store_Name',
                  pp.product_code,
                  pp.product_description AS 'Product_Name',	 
                  dd.ordered_qty         AS 'Ordered_Quantity',
                  dd.document_qty        AS 'Invoice_Quantity',		 
                  dd.ordered_qty - dd.document_qty AS 'Short',
                  concat(rep.first_name, ' ', rep.surname) AS 'Rep Name',
                  d.name AS 'Warehouse',
                  d.short_name AS 'whShort'
		 
           FROM document_master dm
           LEFT JOIN document_header dh  ON dh.document_master_uid = dm.uid
           LEFT JOIN document_detail dd ON dd.document_master_uid = dm.uid
           LEFT JOIN principal_product pp ON dd.product_uid = pp.uid
           LEFT JOIN principal_store_master psm ON dh.principal_store_uid = psm.uid
           LEFT JOIN principal_sales_representative rep ON rep.uid = psm.principal_sales_representative_uid
           LEFT JOIN depot d ON d.uid = dm.depot_uid
           LEFT JOIN  principal p ON p.uid = dm.principal_uid
           WHERE dh.document_status_uid IN (76,77,78)
           AND dm.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) ."
           AND dh.invoice_date >= curdate() - interval 1 day
           AND dd.ordered_qty > dd.document_qty
           " . $depSel . "
           AND NOT EXISTS (SELECT 1 
                           FROM smart_event se
                           WHERE se.data_uid = dm.uid
                           AND se.type     = '" . SE_ZERO_REPORT . "'
                           AND se.type_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $contactUid) .")
           ORDER BY d.uid, psm.deliver_name, dm.document_number";
       
      $zInvoices = $this->dbConn->dbGetAll($sql);
       
     return $zInvoices;
	}
//*********************************************************************************************************************************************************************8
     function getNotificationRecip($prinUid) {
     
             $sql = "SELECT a.uid AS 'ContactUid',
                            a.email_addr,
                            a.mobile_number,
                            a.notify_type,
                            a.depot_uid
                     FROM .principal_contact a
                     WHERE a.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $prinUid) ."
                     AND   a.contact_type_uid = " . CTD_ZERO_INVOICES;

             return $this->dbConn->dbGetAll($sql);
     }
//*********************************************************************************************************************************************************************8
     function updateZeroInvSmartEvent($contactUid, $docUid) {
     	  
     	    $sql = "INSERT INTO smart_event (smart_event.created_date,
                                           smart_event.`type`,
                                           smart_event.type_uid,
                                           smart_event.processed_date,
                                           smart_event.`status`,
                                           smart_event.`status_msg`,
                                           smart_event.data_uid)
                  VALUES (NOW(),
                  '" . SE_ZERO_REPORT . "',
                  ". mysqli_real_escape_string($this->dbConn->connection, $contactUid) .",
                  NOW(),
                  'C',
                  'Run Once record only',
                  ". mysqli_real_escape_string($this->dbConn->connection, $docUid) .")";
                  
                  // echo $sql;
                                    
                  $this->errorTO = $this->dbConn->processPosting($sql,"");
              
            if($this->errorTO->type == 'S') {
                $this->dbConn->dbQuery("commit");
                // echo "<br>" . $sql . "<br>";
                return $this->errorTO;     	
            } else {
       	        // echo $sql;
                return $this->errorTO;  
            }        
     	
     	
     }     
//*********************************************************************************************************************************************************************8
}
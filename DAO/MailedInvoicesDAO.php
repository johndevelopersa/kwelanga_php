<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class MailedInvoicesDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// **************************************************************************************************************************************************** 
  public function getMailedInvoiceRecipients() {
  	
       $sql = "SELECT pc.uid AS 'pcUid',
                      pc.principal_uid, 
                      pc.depot_uid,
                      pc.email_addr, 
                      d.name, 
                      d.mail_invoices_principal_uid, 
                      d.mail_invoices_start
               FROM  principal_contact pc
               LEFT  JOIN .depot d ON d.uid = pc.depot_uid
               WHERE pc.contact_type_uid = " . CTD_MAIL_INVOICES ;
       
       $loadd = $this->dbConn->dbGetAll($sql);
       
       return $loadd;
  }

//***************************************************************************************************************************************************************************  
  public function getDocumentsForMail($depotId, $prinList, $startDate, $pcUid) {

         $sql = "SELECT dm.uid AS 'dmUid',
                        dm.document_number,
                        dh.invoice_date,
                        p.name AS 'prinName',
                        p.short_name AS 'pShortName'
                 FROM .document_master dm
                 INNER JOIN .document_header dh ON dm.uid = dh.document_master_uid
                 INNER JOIN .principal p ON p.uid = dm.principal_uid
                 WHERE dm.principal_uid IN (" .mysqli_real_escape_string($this->dbConn->connection, $prinList).")
                 AND   dm.depot_uid = " .mysqli_real_escape_string($this->dbConn->connection, $depotId)."
                 AND   dh.document_status_uid IN (". DST_INVOICED ."," . DST_DELIVERED_POD_OK . "," . DST_DIRTY_POD . ")
                 AND NOT EXISTS (SELECT 1 
                                 FROM smart_event se
                                 WHERE se.data_uid = dm.uid
                                 AND se.type = '" . SE_MAIL_INVOICE . "'
                                 AND se.type_uid = " .mysqli_real_escape_string($this->dbConn->connection, $pcUid).")
                 AND   dh.invoice_date > '" .mysqli_real_escape_string($this->dbConn->connection, $startDate)."'
                 ORDER BY dm.principal_uid;";
                 
        $docList = $this->dbConn->dbGetAll($sql);
       
        return $docList;
        
   }     

//***************************************************************************************************************************************************************************  
  public function flagDocumentAsSent($dmUid, $pcUid) {
  	      
  	      $sql = "INSERT INTO smart_event (smart_event.created_date,
                              smart_event.`type`,
                              smart_event.type_uid,
                              smart_event.processed_date,
                              smart_event.`status`,
                              smart_event.data_uid)
                  VALUES(NOW(), 
                          '" . SE_MAIL_INVOICE . "',"
                             . mysqli_real_escape_string($this->dbConn->connection, $pcUid) .",
                               NULL,
                              'C' ,"
                             . mysqli_real_escape_string($this->dbConn->connection, $dmUid) .");";

          $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                  $this->errorTO->description="Invoice Not Flaged : ". $sql .$this->errorTO->description;
                  return $this->errorTO;
          } else {
                  $this->dbConn->dbQuery("commit");   
                  return $this->errorTO;
          }
  	
  }
//***************************************************************************************************************************************************************************  


}         	 
?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class DearSystemDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  // **************************************************************************************************************************************************** 

  public function getRoyalSaltProducts($prinUid, $pcode) {
  	
      $sql = "SELECT pp.uid AS 'pUid',
                     pp.product_code,
                     pp.product_description,
                     pp.product_guid,
                     pp.revenue_account
              FROM .principal_product pp
              WHERE pp.principal_uid = '". mysqli_real_escape_string($this->dbConn->connection, $prinUid) . "'
              AND   pp.product_code  = '". mysqli_real_escape_string($this->dbConn->connection, $pcode) .  "'";

      $rsUpdate = $this->dbConn->dbGetAll($sql);
      return $rsUpdate;
  
  }
  
  // **************************************************************************************************************************************************** 
  public function updateRoyalSaltGuid($podUid, $prodGuid, $revAcc) {
  	
      $sql = "UPDATE .principal_product pp SET pp.product_guid    = '". mysqli_real_escape_string($this->dbConn->connection, $prodGuid) . "',
                                               pp.revenue_account = '". mysqli_real_escape_string($this->dbConn->connection, $revAcc) . "'
              WHERE pp.uid = ". mysqli_real_escape_string($this->dbConn->connection, $podUid) . ";";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
                   
      if($this->errorTO->type == 'S') {
      	    $this->dbConn->dbQuery("commit");
            return $this->errorTO;     	
      } else {
      	    return $this->errorTO;
      }
  }
  // **************************************************************************************************************************************************** 
  public function getOrdersForDear($principalUId, 
                                   $notificationUId,
                                   $currDoc) {
       if(mysqli_real_escape_string($this->dbConn->connection, $currDoc) == '' ) {
            $docSelect = ""; 	
       } else {
       	    $docSelect = "AND dm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $currDoc);
       }
                                  	
       $sql = "SELECT dm.uid AS 'dm_uid',
                      dm.depot_uid AS 'depotUid',
                      dm.document_number,
                      dm.client_document_number,
                      dm.processed_date,
                      dh.customer_order_number,
                      dh.invoice_number,
                      d.name AS 'depot_name',
                      dh.principal_store_uid AS 'psmUid',
                      psm.deliver_name,
                      psm.principal_chain_uid,
                      psm.alt_principal_chain_uid,
                      psm.branch_code,
                      dh.invoice_date,
                      dh.order_date,
                      dh.document_status_uid,
                      dh.due_delivery_date,
                      dh.delivery_date,
                      dh.requested_delivery_date,
                      dm.incoming_file,
                      dh.buyer_account_reference,
                      o.delivery_instructions,
                      pp.uid,
                      pp.product_code,
                      pp.alt_code,
                      pp.product_description,
                      pp.product_guid,
                      pp.revenue_account,
                      dd.line_no,
                      dd.ordered_qty,
                      dd.document_qty,
                      dd.net_price,
                      dd.discount_value,
                      dd.extended_price,
                      dd.vat_rate,
                      dd.vat_amount,
                      dd.total,
                      se.uid as 'smartUid',
                      se.status,
                      dh.captured_by,
                      u.username ,
                      s.description as 'Stat'         
               FROM .document_master dm
               INNER JOIN .smart_event se ON  dm.uid = se.data_uid 
                                          AND se.type = '" . SE_EXTRACT . "'
                                          AND se.type_uid = " . mysqli_real_escape_string($this->dbConn->connection, $notificationUId) . "
                                          AND se.status IN ('" . FLAG_STATUS_QUEUED . "', '" . FLAG_ERRORTO_ERROR . "')    
               INNER JOIN document_header dh ON dh.document_master_uid = dm.uid
               INNER JOIN document_detail dd ON dd.document_master_uid = dm.uid  
               INNER JOIN principal_store_master psm ON psm.uid = dh.principal_store_uid
               LEFT JOIN .users u ON u.uid = dh.captured_by
               INNER JOIN depot d ON d.uid = dm.depot_uid
               LEFT  JOIN .orders o ON o.order_sequence_no = dm.order_sequence_no 
               LEFT JOIN .principal_product pp ON pp.uid = dd.product_uid
               INNER JOIN `status` s on s.uid = dh.document_status_uid                   
               WHERE dm.principal_uid =  " . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "
               AND   se.created_date > curdate() - interval 55 day 
               " . $docSelect . "
               ORDER BY dh.invoice_number ;";

    return $this->dbConn->dbGetAll($sql);

  }
  // **************************************************************************************************************************************************** 

  public function checkForExistingSale($docuid, $getStatus) {
  	
    if(mysqli_real_escape_string($this->dbConn->connection, $getStatus) == '') {
        $statLine = "";
    } else {     		
        $statLine = "AND dss.sale_status = " . mysqli_real_escape_string($this->dbConn->connection, $getStatus) ;
  	}
  	
      $sql = "SELECT *
              FROM .dear_systems_sales dss
              WHERE dss.document_master_uid = '". mysqli_real_escape_string($this->dbConn->connection, $docuid) . "'
              ". $statLine . ";";
//              echo "<br>";
//              echo $sql;
//              echo "<br>";

      $rsUpdate = $this->dbConn->dbGetAll($sql);
      return $rsUpdate;
  }
  // **************************************************************************************************************************************************** 
  public function insertSale($docuid, 
                             $saleId, 
                             $saleOrderNumber, 
                             $saleInvoiceTaskID,
                             $saleInvoiceNumber, 
                             $saleStat) {
  	
      $sql = "INSERT INTO dear_systems_sales (dear_systems_sales.document_master_uid,
                                              dear_systems_sales.sale_id,
                                              dear_systems_sales.sale_order_number,
                                              dear_systems_sales.sale_invoice_task_id,
                                              dear_systems_sales.sale_invoice_number,
                                              dear_systems_sales.sale_status)
              VALUES ('". mysqli_real_escape_string($this->dbConn->connection, $docuid)            .  "',
                      '". mysqli_real_escape_string($this->dbConn->connection, $saleId)            .  "',
                      '". mysqli_real_escape_string($this->dbConn->connection, $saleOrderNumber)   .  "',
                      '". mysqli_real_escape_string($this->dbConn->connection, $saleInvoiceTaskID) .  "',
                      '". mysqli_real_escape_string($this->dbConn->connection, $saleInvoiceNumber) .  "',
                      '". mysqli_real_escape_string($this->dbConn->connection, $saleStat)          .  "');";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
                   
      if($this->errorTO->type == 'S') {
      	    $this->dbConn->dbQuery("commit");
            return $this->errorTO;     	
      } else {
      	    return $this->errorTO;
      }
  }
  // **************************************************************************************************************************************************** 
  public function updateSale($saleUid, 
                             $saleStat) {
  	
      $sql = "UPDATE dear_systems_sales SET sale_status = '". mysqli_real_escape_string($this->dbConn->connection, $saleStat) .  "'
              WHERE dear_systems_sales.uid = " . mysqli_real_escape_string($this->dbConn->connection, $saleUid);

      $this->errorTO = $this->dbConn->processPosting($sql,"");
                   
      if($this->errorTO->type == 'S') {
      	    $this->dbConn->dbQuery("commit");
            return $this->errorTO;     	
      } else {
      	    return $this->errorTO;
      }
  }  
  // **************************************************************************************************************************************************** 
  public function getRoyalSaltSpecField($fUid, $entUid) {
  	
  	    $sql = "SELECT *
                FROM .special_field_details sfd
                WHERE sfd.field_uid  = " . mysqli_real_escape_string($this->dbConn->connection, $fUid) . "
                AND   sfd.entity_uid = " . mysqli_real_escape_string($this->dbConn->connection, $entUid) . ";";

        $result = $this->dbConn->dbGetAll($sql);
        return $result;
        
  }
  // **************************************************************************************************************************************************** 

}  
?>
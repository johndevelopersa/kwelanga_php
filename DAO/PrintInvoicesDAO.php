<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


class PrintInvoicesDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
//***************************************************************Get Tripsheet Invoices******************************************************************************************************  
  public function GetTripSheetInvoices($Search,$wareHouseCde){
   
           $sql = "SELECT trim(LEADING '0' FROM dm.document_number) AS 'DocumentNumber',
                          dm.uid AS 'docUid',
                          if(p.short_name IS NULL, p.name, p.short_name) AS 'Principal',
                          trim(psm.deliver_name) AS 'Store',
                          if(dh.copies > 0, 'Y', 'N') AS 'invPrinted',
                          dh.invoice_date,
                          t.name AS 'Transporter',
                          th.tripsheet_number                  
	                 FROM " . iDATABASE . ".tripsheet_header th 
	                 INNER JOIN " . iDATABASE . ".tripsheet_detail td ON td.tripsheet_master_uid = th.uid AND td.removed_flag = 'N' 
	                 INNER JOIN " . iDATABASE . ".document_header dh ON dh.document_master_uid = td.document_master_uid 
	                 INNER JOIN " . iDATABASE . ".document_master dm ON dm.uid = dh.document_master_uid 
	                 LEFT JOIN " . iDATABASE . ".principal_store_master psm ON psm.uid = dh.principal_store_uid 
	                 LEFT JOIN " . iDATABASE . ".principal p ON p.uid = psm.principal_uid
	                 LEFT JOIN " . iDATABASE . ".transporter t ON t.uid = th.transporter_id 
	                 WHERE th.tripsheet_number = '". mysqli_real_escape_string($this->dbConn->connection, $Search) ."'
	                 AND dm.depot_uid = '". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."'
	                 ORDER BY psm.deliver_name";

           $TsInvoice = $this->dbConn->dbGetAll($sql);
           
    return $TsInvoice;	
  	
  }
//*******************************************************************************************************************************************************************************************  
	public function getDocumentWithDetailList($tripNumber, $wareHouseCde, $orderBy='FALSE') {

          $sql="SELECT  dm.uid AS dm_uid,
                        dm.principal_uid,
                        p.name as principal_name,
                        p.physical_add1 as prin_ph_add1,
                        p.physical_add2 as prin_ph_add2,
                        p.physical_add3 as prin_ph_add3,
                        p.vat_num as prin_vat,
                        p.email_add as p_email,
                        p.office_tel,
                        p.banking_details,
                        dm.depot_uid,
                        d.name AS 'Depot', 
                        dm.document_number,
                        dm.client_document_number,
                        dh.order_date,
                        dh.invoice_date,
                        dh.principal_store_uid,
                        dh.customer_order_number,
                        dh.copies,
                        dh.invoice_printed,
                        dh.invoice_number,
                        psm.deliver_name store_name,
                        psm.deliver_add1,
                        psm.deliver_add2,
                        psm.deliver_add3,
                        psm.bill_name,
                        psm.bill_add1,
                        psm.bill_add2,
                        psm.bill_add3,
                        psm.uid psm_uid,
                        psm.vat_number,
                        psm.vat_number_2,
                        psm.no_vat,
                        psm.branch_code,
                        dd.line_no,
                        dd.product_uid,
                        dd.ordered_qty,
                        dd.document_qty,
                        dd.delivered_qty,
                        dd.buyer_delivered_qty,
                        dd.selling_price,
                        dd.discount_value,
                        dd.discount_reference,
                        dd.net_price,
                        dd.extended_price,
                        dd.vat_amount,
                        dd.vat_rate,
                        dd.total,
                        pp.product_code,
                        pp.product_description,
                        pp.items_per_case
                 FROM   " . iDATABASE . ".document_master dm
                 INNER JOIN " . iDATABASE . ".document_header dh ON dm.uid = dh.document_master_uid
                 INNER JOIN " . iDATABASE . ".document_detail dd ON dm.uid = dd.document_master_uid
                 INNER JOIN " . iDATABASE . ".principal_store_master psm ON dh.principal_store_uid = psm.uid
                 INNER JOIN " . iDATABASE . ".depot d ON dm.depot_uid = d.UID
                 INNER JOIN " . iDATABASE . ".principal_product pp ON dd.product_uid = pp.uid
                 INNER JOIN " . iDATABASE . ".principal p ON p.uid = dm.principal_uid
                 WHERE dm.uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $dMUId). ")
                 ORDER BY dm.uid ;";

          return $this->dbConn->dbGetAll($sql);

	}
//*******************************************************************************************************************************************************************************************  

	public function getDocumentWithDetailByTripSheet($tripNumber, $wareHouseCde, $orderBy='FALSE') {

          $sql="SELECT  dm.uid AS dm_uid,
                        dm.principal_uid,
                        th.tripsheet_number,
                        t.name AS 'driver',
                        th.tripsheet_date,
                        if(p.short_name IS NULL, p.name, p.short_name) AS 'Principal',
                        dm.depot_uid,
                        d.name AS 'Depot', 
                        dm.document_number,
                        dh.order_date,
                        psm.deliver_name AS 'store_name',
                        psm.uid psm_uid,
                        dd.uid AS 'dd_uid',
                        dd.line_no,
                        dd.product_uid,
                        dd.ordered_qty,
                        pp.product_code,
                        pp.product_description
                 FROM " . iDATABASE . ".tripsheet_header th 
                 INNER JOIN " . iDATABASE . ".tripsheet_detail td ON td.tripsheet_master_uid = th.uid AND td.removed_flag = 'N' 
                 INNER JOIN " . iDATABASE . ".document_header dh ON dh.document_master_uid = td.document_master_uid 
                 INNER JOIN " . iDATABASE . ".document_master dm ON dm.uid = dh.document_master_uid 
                 INNER JOIN " . iDATABASE . ".document_detail dd ON dm.uid = dd.document_master_uid
                 INNER JOIN " . iDATABASE . ".principal_product pp ON dd.product_uid = pp.uid 
	               LEFT JOIN  " . iDATABASE . ".principal_store_master psm ON psm.uid = dh.principal_store_uid 
	               INNER JOIN " . iDATABASE . ".depot d ON dm.depot_uid = d.uid
	               LEFT JOIN  " . iDATABASE . ".principal p ON p.uid = psm.principal_uid
	               LEFT JOIN  " . iDATABASE . ".transporter t ON t.uid = th.transporter_id 
	               WHERE th.tripsheet_number = '". mysqli_real_escape_string($this->dbConn->connection, $tripNumber) ."'
	               AND dm.depot_uid = '". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."'
	               ORDER BY Principal, dm.document_number ";
	               
	               // echo $sql;
	               
	               
	               

          return $this->dbConn->dbGetAll($sql);

	}
	
//*******************************************************************************************************************************************************************************************  
  public function GetDateInvoices($startDate,$endDate,$principalId){
  
   $sql = "SELECT wsm.uid as 'WsmUid', 
                  t.name AS 'Driver', replace(replace(t.vehicle_reg, ' ',''),'-','') AS 'VehicleReg',
                  t.depot_uid AS 'Depot',
                  psm.deliver_name AS 'Store', 
                  th.tripsheet_number AS 'TripSheetNo', 
                  concat(wsm.latitude, ',', wsm.longitude) AS 'Coordinates', 
                  concat(dm.principal_uid,' - ',trim(LEADING '0' FROM dm.document_number)) AS 'DocumentNumber',
                  dm.uid,
                  p.name AS 'Principal' 		 
                  FROM " . iDATABASE . ".tripsheet_header th 
                  INNER JOIN " . iDATABASE . ".tripsheet_detail td ON td.tripsheet_master_uid = th.uid AND td.removed_flag = 'N' 
                  INNER JOIN " . iDATABASE . ".document_header dh ON dh.document_master_uid = td.document_master_uid 
                  INNER JOIN " . iDATABASE . ".document_master dm ON dm.uid = dh.document_master_uid 
                  LEFT JOIN " . iDATABASE . ".principal_warehouse_store_link pl ON pl.principal_store_master_uid = dh.principal_store_uid 
                                                                                AND pl.depot_uid = dm.depot_uid 		 
                  LEFT JOIN " . iDATABASE . ".principal_store_master psm ON psm.uid = dh.principal_store_uid 
                  LEFT JOIN " . iDATABASE . ".principal p ON p.uid = psm.principal_uid
                  LEFT JOIN " . iDATABASE . ".warehouse_store_master wsm ON wsm.uid = pl.warehouse_store_master_uid
                  LEFT JOIN " . iDATABASE . ".transporter t ON th.transporter_id = t.uid 
         
           WHERE th.tripsheet_date BETWEEN '". mysqli_real_escape_string($this->dbConn->connection, $startDate) ."' AND '". mysqli_real_escape_string($this->dbConn->connection, $endDate) ."'
           AND p.uid ='". mysqli_real_escape_string($this->dbConn->connection, $principalId) ."'
           ORDER BY wsm.del_point_name";

           $TsInvoice = $this->dbConn->dbGetAll($sql);
           
    return $TsInvoice;	
    
  }
//*******************************************************************************************************************************************************************************************  
   public function UserCat($userUId){
      $sql = "SELECT u.category
              FROM " . iDATABASE . ".users u
              WHERE u.uid = '". mysqli_real_escape_string($this->dbConn->connection, $userUId) ."'";

           $UserC = $this->dbConn->dbGetAll($sql);
           
      return $UserC;		
    }  
//*******************************************************************************************************************************************************************************************  
   public function updateInvoicesPrinted($invList) {
   	
      $sql = "UPDATE " . iDATABASE . ".document_header dh SET dh.invoice_printed = dh.invoice_printed + 1
              WHERE dh.document_master_uid IN (" . $invList . ")";
              
              echo $sql;
   	
      $this->dbConn->dbQuery($sql);

      if (!$this->dbConn->dbQueryResult) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed to Omni Import Update in setOmniInvoiceStatus";
      } else {
        	$this->dbConn->dbQuery("commit");
          $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
          $this->errorTO->description = "Successful";
      }

    return $this->errorTO;   	
   	
   }   
//*******************************************************************************************************************************************************************************************  
   public function insertIntoDocLog($invList,$userId) {
   	
   	  $cnt = 0;
   	
   	  foreach(explode(',',$invList) as $row) {
   	
           $sql = "INSERT INTO kwelanga_live.document_log(" . iDATABASE . ".document_log.document_master_uid,
                                                          " . iDATABASE . ".document_log.change_by_user,
                                                          " . iDATABASE . ".document_log.change_datetime,
                                                          " . iDATABASE . ".document_log.change_type)
                   VALUES (" . $row[0] . ",
                           " . $userId . ",
                           NOW(),
                           'Invoice Printed');   ";
   	
                   $this->dbConn->dbQuery($sql);

                   if (!$this->dbConn->dbQueryResult) {
                          $this->errorTO->type = FLAG_ERRORTO_ERROR;
                          $this->errorTO->description = "Failed to inert into Document Log";
                   } else {
                          $this->dbConn->dbQuery("commit");
                          $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                          $this->errorTO->description = "Successful";
                   }
                   $cnt++;
                            
        }
        return $this->errorTO;  
   }   
//*******************************************************************************************************************************************************************************************  
    
  
  
  
  
  
  
  
}
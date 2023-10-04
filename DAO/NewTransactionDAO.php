<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class NewTransactionDAO {
	  private $dbConn;

	  function __construct($dbConn) {

       $this->dbConn = $dbConn; 
       $this->errorTO = new ErrorTO;
    }
//*******************************************************************************************************************************************************
	 public function getDocumentWithDetailsForPrinting($dMUId, $orderBy=false, $special_field_list) {
	 				$special_fieldArray = explode(',',$special_field_list);
	 				// Get Special field Uids;
          $spcount = 0;
          $spv1 = $spv2 = $spv3 = $spv4 = $spv5 = $spv6 = $spv7 = $spv8 = $spv9 = $spv10 = '';
          $sp0  = $sp1  = $sp2  = $sp3  = $sp4  = $sp5  = $sp6  = $sp7  = $sp8  = $sp9  = $sp10  = '';
          if(count($special_fieldArray) > 0 ) {
          	   for ($spcount = 0; $spcount <= 10; $spcount++) { 
                  	 	if($spcount == 0 ) {
	 				     	        	  if(substr(trim($special_fieldArray[$spcount]),0,1) == 'D')	 {
	 				     	        	  	    $sp1  = "LEFT JOIN special_field_details sp1 on sp1.entity_uid = depot.uid and " . substr(trim($special_fieldArray[$spcount]),1,3) . " = sp1.field_uid";	
	 				     	        	  } else {
	 				     	        	  	    if(trim($special_fieldArray[$spcount]) == '')	
	 				     	        	            $fuid = "999";
	 				     	        	        else {     
	 				     	        	  	        $fuid = substr(trim($special_fieldArray[$spcount]),1,3);
	 				     	        	  	    }   
	 				     	        	  	     $sp1  = "LEFT JOIN special_field_details sp1 on sp1.entity_uid = principal_store_master.uid and " . $fuid . " = sp1.field_uid";	
	 				     	        	  }
	 				     	        	  $spv1 = substr(trim($special_fieldArray[$spcount]),1,3);
  					          } 		
                  	 	if($spcount == 1 ) {                  	 		
	 				     	        	  if(substr(trim($special_fieldArray[$spcount]),0,1) == 'D')	 {
	 				     	        	  	    $sp2  = "LEFT JOIN special_field_details sp2 on sp2.entity_uid = depot.uid and " . substr(trim($special_fieldArray[$spcount]),1,3) . " = sp2.field_uid";	
	 				     	        	  } else {	 				     	        	  	
	 				     	        	  	    if(substr(trim($special_fieldArray[$spcount]),0,1) == '')	
	 				     	        	            $fuid = "999";
	 				     	        	        else { 	 				     	        	        	  
	 				     	        	  	        $fuid = substr(trim($special_fieldArray[$spcount]),1,3);
	 				     	        	  	    }   
	 			                          $sp2  = "LEFT JOIN special_field_details sp2 on sp2.entity_uid = principal_store_master.uid and " .  $fuid . " = sp2.field_uid";	
	 				     	        	  }
	 				     	        	  $spv2 = substr(trim($special_fieldArray[$spcount]),1,3);
  					          } 		
                  	 	if($spcount == 2 ) {
	 				     	        	  if(substr(trim($special_fieldArray[$spcount]),0,1) == 'D')	 {
	 				     	        	  	    $sp3  = "LEFT JOIN special_field_details sp3 on sp3.entity_uid = depot.uid and " . substr(trim($special_fieldArray[$spcount]),1,3) . " = sp3.field_uid";	
	 				     	        	  } else {
	 				     	        	  	 	  if(trim($special_fieldArray[$spcount]) == '')	
	 				     	        	            $fuid = "999";
	 				     	        	        else {     
	 				     	        	  	        $fuid = substr(trim($special_fieldArray[$spcount]),1,3);
	 				     	        	  	     }   	 				     	        	  	
	 				     	        	         $sp3  = "LEFT JOIN special_field_details sp3 on sp3.entity_uid = principal_store_master.uid and " . $fuid . " = sp3.field_uid";	
	 				     	        	  }
	 				     	        	  $spv3 = substr(trim($special_fieldArray[$spcount]),0,1);
  					          } 		
                  	 	if($spcount == 3 ) {
	 				     	        	  if(substr(trim($special_fieldArray[$spcount]),0,1) == 'D')	 {
	 				     	        	  	     $sp4  = "LEFT JOIN special_field_details sp4 on sp4.entity_uid = depot.uid and " . substr(trim($special_fieldArray[$spcount]),1,3) . " = sp4.field_uid";	
	 				     	        	  } else {	 				     	        	  	
 	        	  	                   if(substr(trim($special_fieldArray[$spcount]),0,1) == '')	
	 				     	        	            $fuid = "999";
	 				     	        	         else {     
	 				     	        	  	        $fuid = substr(trim($special_fieldArray[$spcount]),1,3);
	 				     	        	  	     }   
	 				     	        	         $sp4  = "LEFT JOIN special_field_details sp4 on sp4.entity_uid = principal_store_master.uid and " . $fuid . " = sp4.field_uid";	
	 				     	        	  }
	 				     	        	  $spv4 = substr(trim($special_fieldArray[$spcount]),1,3);
  					          } 		
                  	 	if($spcount == 4 ) {
	 				     	        	  if(substr(trim($special_fieldArray[$spcount]),0,1) == 'D')	 {
	 				     	        	  	     $sp5  = "LEFT JOIN special_field_details sp5 on sp5.entity_uid = depot.uid and " . substr(trim($special_fieldArray[$spcount]),1,3) . " = sp5.field_uid";	
	 				     	        	  } else {	 				     	        	  	
 	        	  	                   if(substr(trim($special_fieldArray[$spcount]),0,1) == '')	
	 				     	        	            $fuid = "999";
	 				     	        	         else {     
	 				     	        	  	        $fuid = substr(trim($special_fieldArray[$spcount]),1,3);
	 				     	        	  	     } 
	 				     	        	         $sp5  = "LEFT JOIN special_field_details sp5 on sp5.entity_uid = principal_store_master.uid and " . $fuid . " = sp5.field_uid";	
	 				     	        	  }
	 				     	        	  $spv5 = substr(trim($special_fieldArray[$spcount]),1,3);
  					          } 		
                  	 	if($spcount == 5 ) {
	 				     	        	  if(substr(trim($special_fieldArray[$spcount]),0,1) == 'D')	 {
	 				     	        	  	  $sp6  = "LEFT JOIN special_field_details sp6 on sp6.entity_uid = depot.uid and " . substr(trim($special_fieldArray[$spcount]),1,3) . " =sp6.field_uid";	
	 				     	        	  } else {	 				     	        	  	
 	        	  	                   if(trim($special_fieldArray[$spcount]) == '')	
	 				     	        	            $fuid = "999";
	 				     	        	         else {     
	 				     	        	  	        $fuid = substr(trim($special_fieldArray[$spcount]),1,3);
	 				     	        	  	     } 
	 				     	        	         $sp6  = "LEFT JOIN special_field_details sp6 on sp6.entity_uid = principal_store_master.uid and " . $fuid . " =sp6.field_uid";	
	 				     	        	  }
	 				     	        	  $spv6 = substr(trim($special_fieldArray[$spcount]),1,3);
  					          } 		
                  	 	if($spcount == 6 ) {
	 				     	        	  if(substr(trim($special_fieldArray[$spcount]),0,1) == 'D')	 {
	 				     	        	  	     $sp7  = "LEFT JOIN special_field_details sp7 on sp7.entity_uid = depot.uid and " . substr(trim($special_fieldArray[$spcount]),1,3) . " = sp7.field_uid";	
	 				     	        	  } else {	 				     	        	  	
 	        	  	                   if(substr(trim($special_fieldArray[$spcount]),0,1) == '')	
	 				     	        	            $fuid = "999";
	 				     	        	         else {     
	 				     	        	  	        $fuid = substr(trim($special_fieldArray[$spcount]),1,3);
	 				     	        	  	     } 
	 				     	        	         $sp7  = "LEFT JOIN special_field_details sp7 on sp7.entity_uid = principal_store_master.uid and " . $fuid . " = sp7.field_uid";	
	 				     	        	  }
	 				     	        	  $spv7 = substr(trim($special_fieldArray[$spcount]),1,3);
  					          } 		
                    	if($spcount == 7 ) {
	 				     	        	  if(substr(trim($special_fieldArray[$spcount]),0,1) == 'D')	 {
	 				     	        	  	     $sp8  = "LEFT JOIN special_field_details sp8 on sp8.entity_uid = depot.uid and " . substr(trim($special_fieldArray[$spcount]),1,3) . " = sp8.field_uid";	
	 				     	        	  } else {	 				     	        	  	
 	        	  	                   if(substr(trim($special_fieldArray[$spcount]),1,3) == '')	
	 				     	        	            $fuid = "999";
	 				     	        	         else {     
	 				     	        	  	        $fuid = substr(trim($special_fieldArray[$spcount]),1,3);
	 				     	        	  	     } 
	 				     	        	         $sp8  = "LEFT JOIN special_field_details sp8 on sp8.entity_uid = principal_store_master.uid and " . $fuid . " = sp8.field_uid";	
	 				     	        	  }
	 				     	        	  $spv8 = substr(trim($special_fieldArray[$spcount]),1,3);
  					          } 		                  	
                  	 	if($spcount == 8 ) {
	 				     	        	  if(substr(trim($special_fieldArray[$spcount]),0,1) == 'D')	 {
	 				     	        	  	     $sp9  = "LEFT JOIN special_field_details sp9 on sp9.entity_uid = depot.uid and " . substr(trim($special_fieldArray[$spcount]),1,3) . " =sp9.field_uid";	
	 				     	        	  } else {	 				     	        	  	
 	        	  	                   if(substr(trim($special_fieldArray[$spcount]),1,3) == '')	
	 				     	        	            $fuid = "999";
	 				     	        	         else {     
	 				     	        	  	        $fuid = substr(trim($special_fieldArray[$spcount]),1,3);
	 				     	        	  	     } 
	 				     	        	      $sp9  = "LEFT JOIN special_field_details sp9 on sp9.entity_uid = principal_store_master.uid and " . $fuid . " = sp9.field_uid";	
	 				     	        	  }
	 				     	        	  $spv9  = substr(trim($special_fieldArray[$spcount]),1,3);
  					          } 		
                  	 	if($spcount == 9 ) {
	 				     	        	  if(substr(trim($special_fieldArray[$spcount]),0,1) == 'D')	 {
	 				     	        	  	     $sp10  = "LEFT JOIN special_field_details sp10 on sp10.entity_uid = depot.uid and " . substr(trim($special_fieldArray[$spcount]),1,3) . " = sp10.field_uid";	
	 				     	        	  } else {	 				     	        	  	
 	        	  	                   if(substr(trim($special_fieldArray[$spcount]),0,1) == '')	
	 				     	        	            $fuid = "999";
	 				     	        	         else {     
	 				     	        	  	        $fuid = substr(trim($special_fieldArray[$spcount]),1,3);
	 				     	        	  	     } 
	 				     	        	        $sp10  = "LEFT JOIN special_field_details sp10 on sp10.entity_uid = principal_store_master.uid and " . $fuid . " =sp10.field_uid";	
	 				     	        	  }
	 				     	        	  $spv10 = substr(trim($special_fieldArray[$spcount]),1,3);
  					          } 		
               }
  				}  else {
	 					   $sp0  = "";
	 					   $spv0 = "";
	 	      } 	 	      
	 	      
          $sql="SELECT  a.uid dm_uid,
                        a.principal_uid,
                        p.name as principal_name,
                        postal_add1 as prin_add1,
                        postal_add2 as prin_add2,
                        postal_add3 as prin_add3,
                        physical_add1 as prin_ph_add1,
                        physical_add2 as prin_ph_add2,
                        physical_add3 as prin_ph_add3,
                        vat_num as prin_vat,
                        p.email_add as p_email,
                        p.office_tel,
                        p.office_tel2,
                        p.banking_details,
                        p.alt_banking_details,
                        p.office_tel,
                        export_number,
                        company_reg,
                        a.depot_uid,
                        depot.name
                        depot_name,
                        depot_address1,
                        depot_address2,
                        depot_address3,
                        depot.priced_uplift,                        
                        depot.disable_stock_check, 
                        depot.bypass_qty_restrictions,
                        depot.allow_negative_stock, 
                        a.document_number,
                        a.client_document_number,
                        a.alternate_document_number,
                        a.document_type_uid,
                        ifnull(pdt.description,
                        document_type.description) document_type_description,
                        a.processed_date,
                        a.order_sequence_no,
                        a.processed_time,
                        a.invoice_file,
                        a.additional_type,
                        document_header.order_date,
                        document_header.invoice_date,
                        document_header.delivery_date,
                        document_header.due_delivery_date,
                        document_header.principal_store_uid,
                        document_header.customer_order_number,
                        document_header.grv_number,
                        document_header.claim_number,
                        document_header.waybill_number,
                        document_header.copies,
                        trim(document_header.source_document_number) source_document_number,
                        status.description as status,
                        document_header.document_status_uid as status_uid,
                        document_header.invoice_number,
                        document_header.cases,
                        document_header.selling_price,
                        document_header.exclusive_total,
                        document_header.vat_total,
                        document_header.invoice_total,
                        principal_store_master.deliver_name store_name,
                        principal_store_master.deliver_add1,
                        principal_store_master.deliver_add2,
                        principal_store_master.deliver_add3,
                        principal_store_master.bill_name,
                        principal_store_master.bill_add1,
                        principal_store_master.bill_add2,
                        principal_store_master.bill_add3,
                        principal_store_master.uid psm_uid,
                        principal_store_master.vat_number,
                        principal_store_master.vat_number_2,
                        principal_store_master.no_vat,
                        principal_store_master.export_number_enabled,
                        principal_store_master.branch_code,
                        principal_store_master.old_account,
                        principal_store_master.bank_details_to_print,
                        principal_store_master.q_r_code_to_print,
                        `day`.name delivery_day,
                        document_detail.line_no,
                        document_detail.product_uid,
                        document_detail.ordered_qty,
                        document_detail.document_qty,
                        document_detail.delivered_qty,
                        document_detail.selling_price,
                        document_detail.discount_value,
                        document_detail.discount_reference,
                        document_detail.net_price,
                        document_detail.extended_price,
                        document_detail.vat_amount,
                        document_detail.vat_rate,
                        document_detail.total,
                        principal_product.product_code,
                        principal_product.alt_code,
                        principal_product.product_description,
                        principal_product.items_per_case, 
                        principal_product.ean_code,                         
                        principal_product.weight,
                        (SELECT DISTINCT 1
                                FROM document_master dm2,
                                     document_header dh2
                                WHERE dm2.uid = dh2.document_master_uid
                                AND dh2.source_document_number = a.document_number
                                AND dm2.depot_uid = a.depot_uid) has_associated_notes,
                        document_detail.pallets,
                        o.uid orders_uid,
                        depot.wms as 'depot_wms',
                        IFNULL(ur.full_name,document_header.captured_by) as 'captured_by_name',
                        document_header.document_status_uid,
                        o.delivery_instructions,
                        principal_store_master.tel_no1,
                        principal_store_master.tel_no2,
                        principal_store_master.email_add,
                        s.description as document_service,
                        concat(rep.first_name, ' ', rep.surname) as rep_name,
                        rep.rep_code,
                        rc.description as 'crdreason',
                        document_detail.batch,
                        principal_product.weight,
                        pdt.tcs,
                        pdt.btcs,
                        pdt.statementmessage,
                        concat(overrep.first_name, ' ',
                        overrep.surname) as overrep_name,
                        overrep.rep_code as overrep_code,
                        area.uid as areauid,
                        area.description as area, 
                        if('" . $spv1  . "' = '','', sp1.value)  as 'sp1' , 
                        if('" . $spv2  . "' = '','', sp2.value)  as 'sp2' , 
                        if('" . $spv3  . "' = '','', sp3.value)  as 'sp3' , 
                        if('" . $spv4  . "' = '','', sp4.value)  as 'sp4' ,  
                        if('" . $spv5  . "' = '','', sp5.value)  as 'sp5' ,  
                        if('" . $spv6  . "' = '','', sp6.value)  as 'sp6' ,  
                        if('" . $spv7  . "' = '','', sp7.value)  as 'sp7' ,  
                        if('" . $spv8  . "' = '','', sp8.value)  as 'sp8' ,   
                        if('" . $spv9  . "' = '','', sp9.value)  as 'sp9' ,  
                        if('" . $spv10 . "' = '','', sp10.value) as 'sp10'   
                FROM       document_master a
                         LEFT JOIN  orders o ON a.order_sequence_no = o.order_sequence_no
                         LEFT JOIN  document_type ON a.document_type_uid = document_type.uid
                         LEFT JOIN  principal_document_type pdt on a.document_type_uid = pdt.document_type_uid and pdt.principal_uid = a.principal_uid
                         LEFT JOIN  depot ON a.depot_uid = depot.UID
                         INNER JOIN document_header ON a.uid = document_header.document_master_uid
                         LEFT JOIN  principal_store_master ON document_header.principal_store_uid = principal_store_master.uid
                         LEFT JOIN  `day` ON principal_store_master.delivery_day_uid = `day`.uid
                         LEFT JOIN  `status` ON document_header.document_status_uid = `status`.uid
                         INNER JOIN document_detail ON a.uid = document_detail.document_master_uid
                         LEFT JOIN  principal_product ON document_detail.product_uid = principal_product.uid
                         LEFT JOIN  users ur on document_header.captured_by = ur.uid
                         INNER JOIN principal p on a.principal_uid = p.uid
                         LEFT JOIN  document_service s on document_header.document_service_type_uid = s.uid
                         LEFT JOIN  principal_sales_representative rep on principal_store_master.principal_sales_representative_uid = rep.uid
                         LEFT JOIN  reason_code rc on rc.uid=document_header.pod_reason_uid
                         LEFT JOIN  principal_sales_representative overrep on document_header.overide_rep_code_uid = overrep.uid  
                         LEFT JOIN  area on principal_store_master.area_uid = area.uid "
                         . $sp1  . " "
                         . $sp2  . " "
                         . $sp3  . " "
                         . $sp4  . " "
                         . $sp5  . " "
                         . $sp6  . " "
                         . $sp7  . " "
                         . $sp8  . " "
                         . $sp9  . " "
                         . $sp10 . " "
                         . $sp0  . "
                         WHERE      a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $dMUId)."'".
                         (($orderBy!==false)?" ORDER BY ".$orderBy:"");

// echo $sql;

       return $this->dbConn->dbGetAll($sql);                       
   


	 }
//*******************************************************************************************************************************************************
	 public function deleteTempSo($userId) {
	 
	 $bldsql = "DROP TABLE IF EXISTS rich_temp_sonumber_11";
   
   $result = $this->dbConn->dbQuery($bldsql);
   }
   //*******************************************************************************************************************************************************
   public function createTempSos($userId) {
   
	       $bldsql = "CREATE TABLE rich_temp_sonumber_11 (`InvNum`   VARCHAR(20)  NULL,
	                                                      `LineNo`   VARCHAR(20)  NULL,
	                                                      `SoNum`    VARCHAR(12)  NULL,
	                                                      `FileName` VARCHAR(50)  NULL)";
	       $dtresult = $this->dbConn->dbQuery($bldsql);
	 
	 }
	 
	 //*******************************************************************************************************************************************************
	 public function deleteTempRich($userId) {
	 
	           $trichsql = "DROP TABLE IF EXISTS rich_temp_11" ;
  	          $result = $this->dbConn->dbQuery($trichsql);
   
	 }
	 //*******************************************************************************************************************************************************
	 
	 public function createTempRich($userId) {
	 
	           $bldsql = "CREATE TABLE rich_temp_11              (`Date`              VARCHAR(20)  NULL,
	                                                              `Document`          VARCHAR(20)  NULL,
	                                                              `Document Number`   VARCHAR(20)  NULL,
	                                                              `Inv Addrnum`       VARCHAR(20)  NULL,
	                                                              `Inv Name`          VARCHAR(100) NULL,
	                                                              `Inv PO Box`        VARCHAR(100) NULL,
  	                                                             `Inv City`          VARCHAR(100) NULL,
                                                                `Inv Post Code`     VARCHAR(100) NULL,
	                                                              `Inv Country`       VARCHAR(100) NULL,
	                                                              `Deliv Addrnum`     VARCHAR(100) NULL,
	                                                              `Deliv Name`        VARCHAR(100) NULL,
	                                                              `Deliv Name2`       VARCHAR(100) NULL,
	                                                              `Deliv Street`      VARCHAR(100) NULL,
	                                                              `Deliv City`        VARCHAR(100) NULL,
	                                                              `Deliv Post Code`   VARCHAR(50)  NULL,
	                                                              `Account Number`    VARCHAR(50)  NULL,
	                                                              `Deliv Acc No.`     VARCHAR(50)  NULL,
	                                                              `VAT Reg No (RICH)` VARCHAR(50)  NULL,
	                                                              `Customer PO No.`   VARCHAR(50)  NULL,
  	                                                             `Deliv Method`      VARCHAR(50)  NULL,
                                                                `Payment Terms`     VARCHAR(50)  NULL,
	                                                              `Payment Terms1`    VARCHAR(50)  NULL,
	                                                              `Payment Terms2`    VARCHAR(50)  NULL,
	                                                              `Payment Terms3`    VARCHAR(50)  NULL,
 	                                                              `VAT Reg No (Cust)` VARCHAR(50)  NULL,
	                                                              `Representative`    VARCHAR(50)  NULL,
	                                                              `Warehouse`         VARCHAR(50)  NULL,
	                                                              `Deliv Note No.`    VARCHAR(50)  NULL,
	                                                              `Page`              VARCHAR(50)  NULL,
	                                                              `Item No`           VARCHAR(50)  NULL,
	                                                              `SO Number`         VARCHAR(50)  NULL,
	                                                              `Product Code`      VARCHAR(50)  NULL,
  	                                                             `Description`       VARCHAR(50)  NULL,
                                                                `Order Qty`         VARCHAR(50)  NULL,
	                                                              `Invoice Qty`       VARCHAR(50)  NULL,
	                                                              `To Follow Qty`     VARCHAR(50)  NULL,
	                                                              `Unit`              VARCHAR(50)  NULL,
 	                                                              `Unit Price`        VARCHAR(50)  NULL,
	                                                              `Disc`              VARCHAR(50)  NULL,
	                                                              `Exclusive Value`   VARCHAR(50)  NULL,
	                                                              `VAT Amnt`          VARCHAR(50)  NULL,
	                                                              `Inclusive Value`   VARCHAR(50)  NULL,
	                                                              `Total Excl VAT`    VARCHAR(50)  NULL,
	                                                              `Total VAT Amnt`    VARCHAR(50)  NULL,
	                                                              `Total`             VARCHAR(50)  NULL,
  	                                                            `Total Mass`        VARCHAR(50)  NULL,
                                                                `Spare`             VARCHAR(50)  NULL,
                                                                `FileName`             VARCHAR(50)  NULL)";
	           $result = $this->dbConn->dbQuery($bldsql);
	 }          
   //*******************************************************************************************************************************************************
   public function emptySoTable($userId) {
   
	       $bldsql = "TRUNCATE TABLE rich_temp_sonumber_11" ;
	       $dtresult = $this->dbConn->dbQuery($bldsql);
	 
	 }          
	 //*******************************************************************************************************************************************************
   public function loadSoTable($userId) {
   
	       $bldsql = "insert into rich_temp_sonumber_11 (rich_temp_sonumber_11.InvNum,
	                                                     rich_temp_sonumber_11.SoNum,
	                                                     rich_temp_sonumber_11.FileName)
	                  (select rich_temp_11.`Document Number`,
	                          lpad(rich_temp_11.`SO Number`,10,'0'),
	                          concat('RPC', '_', lpad(rich_temp_11.`SO Number`,10,'0'), '_' , rich_temp_11.`Document Number`, '.csv')
	              	   from rich_temp_11
                     where trim(rich_temp_11.`SO Number`) <> '' || trim(rich_temp_11.`SO Number`) is not NULL)";
	       
	       $rTO = $this->dbConn->processPosting($bldsql,"");

         if($rTO->type == "S"){
         	   echo "<br>";
		         echo " Load SO Check Table Query: OK<br>";
		         $this->dbConn->dbQuery("commit");
		     } else{
		     	   echo " Load SO Check Table Query: Error<br>";
		     	   echo "<br>";
		     	   echo $bldsql;
		     	   echo "<br>";
		     }
	 
	 }          
	 //*******************************************************************************************************************************************************
   public function updateRichTable($userId) {
   
	       $bldsql = "update rich_temp_11,
	                         rich_temp_sonumber_11 set rich_temp_11.`SO Number` = rich_temp_sonumber_11.SoNum,
	                                                   rich_temp_11.`FileName` = rich_temp_sonumber_11.FileName
                    where  rich_temp_sonumber_11.InvNum = rich_temp_11.`Document Number`" ;
	       
	       $rTO = $this->dbConn->processPosting($bldsql,"");

         if($rTO->type == "S"){
         	   echo "<br>";
		         echo " Update Rich Temp Table Query: OK<br>";
		         $this->dbConn->dbQuery("commit");
		     } else{
		     	   echo " Update Rich Temp Table Query: Error<br>";
		     	   echo "<br>";
		     	   echo $bldsql;
		     	   echo "<br>";
		     }
	 
	 }          
	 //********************************************************************************************************************************************************	 
   public function updateRichInvoicedTransactions($userId) {
   
	       $bldsql = "update document_master dm
                    INNER JOIN document_header dh on dm.uid = dh.document_master_uid
                    INNER JOIN document_detail dd on dm.uid = dd.document_master_uid
                    INNER JOIN principal_product pp on pp.uid = dd.product_uid
                    INNER JOIN principal_store_master psm on psm.uid = dh.principal_store_uid
                    INNER JOIN document_type dt on dt.uid = dm.document_type_uid
                    INNER JOIN rich_temp_11 on lpad(trim(rich_temp_11 .`SO Number`),10,'0') = lpad(right(dm.client_document_number,6),10,'0')
                                            and lpad(dd.client_line_no,8,'0') = lpad(rich_temp_11 .`SO Itm No`,8,'0') 
                              SET dh.document_status_uid    = 76,
                                  dh.invoice_date           = concat(substr(rich_temp_11.`Date`,7,4), '-' , substr(rich_temp_11.`Date`,4,2), '-' , substr(rich_temp_11.`Date`,1,2)),
                                  dh.invoice_number         = rich_temp_11.`Document Number`,
                                  dh.customer_order_number  = rich_temp_11.`Customer PO No.`,
                                  psm.vat_number            = trim(substr(rich_temp_11.`VAT Reg No (Cust)`,3,10)),
                                  old_price                 = dd.net_price,
                                  dd.document_qty           = rich_temp_11.`Invoice Qty`,
                                  dd.selling_price          = replace(rich_temp_11.`Unit Price`,',',''),
                                  dd.net_price              = if(replace(rich_temp_11.`Exclusive Value`,',','') / replace(rich_temp_11.`Invoice Qty`,',','') IS NOT NULL, 
                                                              round(replace(rich_temp_11.`Exclusive Value`,',','') / replace(rich_temp_11.`Invoice Qty`,',',''),2) ,
                                                              0),
                                  dd.extended_price         = replace(rich_temp_11.`Exclusive Value`,',',''), 
                                  dd.vat_amount             = replace(rich_temp_11.`VAT Amnt`,',',''), 
                                  dd.total                  = replace(rich_temp_11.`Inclusive Value`,',',''), 
                                  psm.bill_name             = rich_temp_11.`Inv Name`,
                                  psm.bill_add1             = concat('PO Box  ',rich_temp_11.`Inv PO Box`),
                                  psm.bill_add2             = rich_temp_11.`Inv City`,
                                  rich_temp_11.Spare = 'S',
                                  rich_temp_11.Processed_Date = CURDATE()                                  
                    WHERE dm.principal_uid = 354
                    AND   dm.document_type_uid in (1)
                    AND   dm.incoming_file LIKE '%RICH%'
                    AND   dh.document_status_uid in (74,75)
                    AND   dm.processed_date > curdate() - interval 7 day
                    AND   trim(rich_temp_11.Spare) is NULL "  ;
	                  
	       $rTO = $this->dbConn->processPosting($bldsql,"");
	       
//	       echo $bldsql;

         if($rTO->type == "S"){
         	   echo "<br>";
		         echo " Update Rich Invoiced transactions Query: OK<br>";
		         $this->dbConn->dbQuery("commit");
		     } else{
		     	   echo " Update Rich Invoiced transactions Query: Error<br>";
		     	   echo "<br>";
		     	   echo $bldsql;
		     	   echo "<br>";
		     }
	 
	 }          
	 //********************************************************************************************************************************************************	 
   public function checkInvoicedTransactionsTotals($userId) {
        $sql = "select distinct(dm.uid), rt.Processed_Date
                from rich_temp_11 rt 
								inner join .document_master dm on rt.`SO Number` = dm.client_document_number
								where dm.principal_uid = 354
								and   trim(rt.Spare)= 'S'
								and   rt.Processed_Date = CURDATE()";
								
				$ttc =  $this->dbConn->dbGetAll($sql);   	
				
				foreach($ttc as $ttcrow) {	
					
					  $usql = "update document_master dm
                     inner join .document_header dh on dm.uid = dh.document_master_uid
                     inner join .document_detail dd on dm.uid = dd.document_master_uid set dd.vat_amount = 0, dd.total = 0
                     where dh.invoice_date  = '" . $ttcrow['Processed_Date'] . "'
                     and   dm.principal_uid = 354
                     and   dh.document_status_uid = 76
                     and   dd.document_qty = 0";
					
					 	$rTO = $this->dbConn->processPosting($usql,"");

            if($rTO->type == "S"){
         	      echo "<br>";
		            echo " Check Detail Transaction Totals Query: OK<br>";
		            $this->dbConn->dbQuery("commit");
		        } else{
		     	      echo " Check Detail Transaction Totals Query: Error<br>";
		     	      echo "<br>";
		     	      echo $usql;
		     	      echo "<br>";
		        }
		        
					  $usql = "update document_header dh set dh.cases         = (select sum(dd.document_qty)
                                                                      from .document_detail dd
                                                                      where dd.document_master_uid = " . $ttcrow[uid] . "),
                                                dh.exclusive_total = (select sum(dd.extended_price)
                                                                      from .document_detail dd
                                                                      where dd.document_master_uid = " . $ttcrow[uid] . "),
                                                dh.vat_total       = (select sum(dd.vat_amount)
                                                                      from .document_detail dd
                                                                      where dd.document_master_uid = " . $ttcrow[uid] . "),
                                                dh.invoice_total   = (select sum(dd.total)
                                                                      from .document_detail dd
                                                                      where dd.document_master_uid = " . $ttcrow[uid] . ")
                  where dh.document_master_uid = " . $ttcrow[uid] . " ;" ;
				
				  	$rTO = $this->dbConn->processPosting($usql,"");

            if($rTO->type == "S"){
         	      echo "<br>";
		            echo " Check Header Transaction Totals Query: OK<br>";
		            $this->dbConn->dbQuery("commit");
		        } else{
		     	      echo " Check Header Transaction Totals Query: Error<br>";
		     	      echo "<br>";
		     	      echo $usql;
		     	      echo "<br>";
		        }
				}	
	 }          
	 //********************************************************************************************************************************************************	 
   public function checkDoubledItemNos($userId) {
   	
   	   $dsql = "SELECT rich_temp_11.`SO Number`,
   	                   rich_temp_11.`Item No`, 
   	                   rich_temp_11.`SO Itm No`,
   	                   rich_temp_11.`Invoice Qty`,
   	                   rich_temp_11.`Unit Price`,
   	                   rich_temp_11.`Exclusive Value`,
   	                   rich_temp_11.`VAT Amnt`,
   	                   rich_temp_11.`Inclusive Value`
                FROM .rich_temp_11 
                WHERE trim(rich_temp_11.Spare) is NULL
                AND   rich_temp_11.Processed_Date = CURDATE() 
                ORDER BY rich_temp_11.`SO Number`, rich_temp_11.`SO Itm No` , rich_temp_11.`Item No`  DESC";
                
                // trim(rich_temp_11.Spare) is NULL"
                
                $cwin =  $this->dbConn->dbGetAll($dsql); 
                
                $update = 'F';
                
               
                foreach($cwin as $crow) {
                  	 if($crow['SO Number'] . '  ' . $crow['Item No'] == $crow['SO Number'] . '  ' . $crow['SO Itm No']) {
                  	 	    if($update == 'T') {  	    	
                  	 	    	  $uSql = "UPDATE  rich_temp_11 SET rich_temp_11.`Invoice Qty` = " . $invQ . ",
                                               rich_temp_11.`Unit Price`  = " . $uP . ",
                                               rich_temp_11.`Exclusive Value` = " . $eV . ",
                                               rich_temp_11.`VAT Amnt` = " . $vA . ",
                                               rich_temp_11.`Inclusive Value` = " . $iQ . "
                                       WHERE rich_temp_11.`SO Number` = " . $crow['SO Number'] . " 
                                       AND   rich_temp_11.`SO Itm No` = " . $crow['SO Itm No'] . "
                                       AND   rich_temp_11.`Item No`   = " . $crow['SO Itm No'] . " ;";                                                               
                                     
                              $rTO = $this->dbConn->processPosting($uSql,"");
                              
                              $this->dbConn->dbQuery("commit");
                  	 	    }
                  	 	    
                  	 	    $update = 'F';
                  	 	    
                  	 } else {
                  	 	    $invQ = $crow['Invoice Qty'];
   	                      $uP = $crow['Unit Price'];
   	                      $eV = $crow['Exclusive Value'];
   	                      $vA = $crow['VAT Amnt'];
   	                      $iQ = $crow['Inclusive Value'];
                  	 	    $update = 'T';
                  	 }          
                }
   }
	 //********************************************************************************************************************************************************	 
   public function getDocumentToInvoice($principalId, $docNo, $check) {

					// First check PO Number 
   	      if ($check == "PO") {
   	      	 $lookon = "AND   dh.customer_order_number = '".mysqli_real_escape_string($this->dbConn->connection, $docNo)."'";
  	      } else {
   	      	 $lookon = "AND   dm.document_number LIKE '%".mysqli_real_escape_string($this->dbConn->connection, $docNo)."%'";
   	      }
   	      
   	      $sql = "SELECT dm.uid as 'dmUid',
   	                     dm.document_number,
   	                     dd.uid as 'ddUID', 
   	                     psm.deliver_name,
                         dh.invoice_date,
                         dh.customer_order_number, 
                         dh.document_status_uid, 
                         dd.product_uid, 
                         pp.product_code, 
                         pp.product_description, 
                         dd.ordered_qty, 
                         dd.document_qty, 
                         dd.net_price
                  FROM document_master dm, 
                         document_header dh,
                         document_detail dd, 
                         principal_store_master psm,
                         principal_product pp
                  WHERE dm.uid  = dh.document_master_uid
                  AND   dm.uid  = dd.document_master_uid
                  AND   psm.uid = dh.principal_store_uid
                  AND   pp.uid  = dd.product_uid
                  AND   dm.principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalId) ." "
                  . $lookon ." ;";
// echo $sql;

       return $this->dbConn->dbGetAll($sql);    	
   	
   }

//********************************************************************************************************************************************************	 

}  
?>
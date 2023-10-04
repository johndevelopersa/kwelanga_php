<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php'); 
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');   	
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');

class ReportsDAO {
	private $dbConn;

	function __construct($dbConn) {

       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
       
    }

  public function getCustomerOpeningBalance($CustomerUid, $PaymentBy, $StartDate) {
  	
  	  if($PaymentBy==PAYMENT_BY_GROUP){
          $sql = "select pcm.description as 'Customer',
                  if(cb.total_due <> 0,cb.total_due, '0.00') as 'Total' 
                  from principal_chain_master pcm 
                  left join customer_balance cb on pcm.uid = cb.customer_uid and cb.payment_by = " . mysqli_real_escape_string($this->dbConn->connection, $PaymentBy) . " and cb.month_end_date =  '" . mysqli_real_escape_string($this->dbConn->connection, $StartDate) . "' - interval 1 day
                  where pcm.uid =  '" . mysqli_real_escape_string($this->dbConn->connection, $CustomerUid) . "'" ;  		
  		} else {
          $sql = "select psm.deliver_name as 'Customer',
                  if(cb.total_due <> 0,cb.total_due, '0.00') as 'Total' 
                  from principal_store_master psm 
                  left join customer_balance cb on psm.uid = cb.customer_uid and cb.payment_by = " . mysqli_real_escape_string($this->dbConn->connection, $PaymentBy) . " and cb.month_end_date =  '" . mysqli_real_escape_string($this->dbConn->connection, $StartDate) . "' - interval 1 day
                  where psm.uid =  '" . mysqli_real_escape_string($this->dbConn->connection, $CustomerUid) . "'" ;  		  		
  		
  	  }
  	  $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 

  public function getPrincipalName($principalId) {
 	  
      $sql = "select p.name as 'Principal'
		          from principal p
              where p.uid =  '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'" ;

      $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  }
  
  // **************************************************************************************************************************************************** 
  public function getPriceGroups($principalId, $currgroup) {
 	  
      $sql = "select pcm.uid, 
                     pcm.description as 'Chain',
                     pcm.chain_group, 
                     pcm.`status`
              from .principal_chain_master pcm
              where pcm.uid in (" . mysqli_real_escape_string($this->dbConn->connection, $currgroup) . ")
              and   pcm.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
              and   pcm.`status` = 'A'
              and   pcm.chain_group = 1" ;
    
      $gCBP = $this->dbConn->dbGetAll($sql);
      return $gCBP;
  
  } 
  // **************************************************************************************************************************************************** 
  public function getPriceRecords($principalId, $date, $currgroup) {
 	  
      $sql = "select p.chain_store,
                     pp.uid AS 'principal_product_uid',
                     p.list_price, 
                     p.deal_type_uid, 
                     p.discount_value,
                     pp.product_code, 
                     pp.product_description,
                     p.start_date, 
                     if(ppc.description is null,'Unknown Catagory',ppc.description) as 'Catagory',
                     if(ppc.description is null,'499',ppc.order) as 'CatagoryOrder' , 
                     '" . PRT_PRODUCT . "' AS 'LINE_GRP'
              from   pricing p,
                     principal_product pp
              left join principal_product_category ppc on ppc.uid = pp.major_category
              where  pp.uid = p.principal_product_uid
              and    p.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
              and    '" . mysqli_real_escape_string($this->dbConn->connection, $date) . "' between p.start_date and p.end_date
              and    p.customer_type_uid = 1
              and    p.price_type_uid = " . PRT_PRODUCT . "
              and    p.chain_store in (" . mysqli_real_escape_string($this->dbConn->connection, $currgroup) . ")
              and    p.deleted = 0
              and    pp.status <> 'D'
              group by p.chain_store, " . PRT_PRODUCT . " ASC, ppc.description, pp.product_description, p.start_date ASC" ;
// echo $sql;
      $gCBP = $this->dbConn->dbGetAll($sql);
      return $gCBP;
  
  } 
  // **************************************************************************************************************************************************** 
  public function getCatagoryPriceRecords($principalId, $date, $currgroup) {
 	  
      $sql = "select p.chain_store,
                     pp.uid AS 'principal_product_uid',
                     p.list_price, 
                     p.deal_type_uid, 
                     p.discount_value,
                     pp.product_code, 
                     pp.product_description,
                     p.start_date, 
                     if(ppc.description is null,'Unknown Catagory',ppc.description) as 'Catagory',
                     if(ppc.description is null,'499',ppc.order) as 'CatagoryOrder', 
                     '" . PRT_PRODUCT_GROUP . "' AS 'LINE_GRP'
              FROM pricing p
              LEFT JOIN principal_product pp ON pp.principal_uid = 305 AND pp.major_category = p.principal_product_uid
              LEFT JOIN principal_product_category ppc on ppc.uid = pp.major_category
              WHERE p.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
              AND   p.price_type_uid = " . PRT_PRODUCT_GROUP . "
              AND    '" . mysqli_real_escape_string($this->dbConn->connection, $date) . "' between p.start_date and p.end_date
              AND    p.chain_store in (" . mysqli_real_escape_string($this->dbConn->connection, $currgroup) . ")
              AND    p.deleted = 0
              GROUP BY p.chain_store, " . PRT_PRODUCT_GROUP . " ASC,  ppc.description, pp.product_description, p.start_date ASC" ;
// echo $sql;
      $gCBP = $this->dbConn->dbGetAll($sql);
      return $gCBP;
  
  } 
  // **************************************************************************************************************************************************** 



  public function checkIfPriceRecordExists($product, $userId, $field1) {
 	  
      $sql = "select *
              from   `temp_price_report_" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "`
              where  `" . mysqli_real_escape_string($this->dbConn->connection, $field1) . "` = " . mysqli_real_escape_string($this->dbConn->connection, $product) . "
              " ;

      $gCBP = $this->dbConn->dbGetAll($sql);
      return $gCBP;
  
  } 
  // **************************************************************************************************************************************************** 
  public function checkIfCatagoryRecordExists($catagory, $userId, $field1) {
 	  
      $sql = "select *
              from   `temp_price_report_" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "`
              where  `" . mysqli_real_escape_string($this->dbConn->connection, $field1) . "` = '" . mysqli_real_escape_string($this->dbConn->connection, $catagory) . "'
              " ;

      $gCBP = $this->dbConn->dbGetAll($sql);
      return $gCBP;
  
  } 
  // **************************************************************************************************************************************************** 
  public function insertIntoTempPrice($userId, $field1, $field2, $field3, $fldval, $fldval2, $fldval3, $sort) {
 	  
      $isql = "INSERT INTO `temp_price_report_" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "`
             (`" . mysqli_real_escape_string($this->dbConn->connection, $field1) . "`,
              `" . mysqli_real_escape_string($this->dbConn->connection, $field2) . "`,  
              `" . mysqli_real_escape_string($this->dbConn->connection, $field3) . "`,  
              `sort` )                          
             VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $fldval)  . "',
                     '" . mysqli_real_escape_string($this->dbConn->connection, $fldval2) . "',
                     '" . mysqli_real_escape_string($this->dbConn->connection, $fldval3) . "',
                     '" . mysqli_real_escape_string($this->dbConn->connection, $sort)    . "')";
                      
             $itresult = $this->dbConn->dbQuery($isql);
             $this->dbConn->dbQuery("commit");
             
             return;
  
  } 
  // **************************************************************************************************************************************************** 
  public function updateTempPrice($userId, $field1, $fldval, $where) {
 	  
      $isql = "UPDATE`temp_price_report_" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "` 
                                            set " . mysqli_real_escape_string($this->dbConn->connection, $field1) . " = '" . mysqli_real_escape_string($this->dbConn->connection, $fldval) . "' 
               WHERE trim(`Field1`) = '" . mysqli_real_escape_string($this->dbConn->connection, $where) . "' ";
               
//               echo "<br>";
//               echo $isql;
//                echo "<br>";
                      
             $itresult = $this->dbConn->dbQuery($isql);
             $this->dbConn->dbQuery("commit");

             return;
  
  } 
  // **************************************************************************************************************************************************** 

  public function getDateRangeYear($principalId, $start_date) {
 	  
      $sql = "select *
              from .principal_financial_year ye
              where ye.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
              and   ye.year = '" . mysqli_real_escape_string($this->dbConn->connection, substr($start_date,0,4)) . "';";
              
      $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 
  // **************************************************************************************************************************************************** 
  public function getCustomerName($CustomerUid, $PaymentBy) {
  	
  	  if ($PaymentBy == 1) {
           $sql = "select psm.deliver_name as 'Customer'
                   from .principal_store_master psm
                   where psm.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $CustomerUid) . "';";
 	  	} else {
           $sql = "select pcm.description as 'Customer'
                   from .principal_chain_master pcm
                   where pcm.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $CustomerUid) . "';"; 	  		
	  	}
      $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 
  // **************************************************************************************************************************************************** 
  public function getCustomerSalesDocuments($principalId, $CustomerUid, $StartDate, $EndDate, $paymentBy) {
  	
  	if (mysqli_real_escape_string($this->dbConn->connection, $paymentBy) == PAYMENT_BY_CUSTOMER ) {
       $CustSelection = "and   dh.principal_store_uid in  (". mysqli_real_escape_string($this->dbConn->connection, $CustomerUid) . ")";
    } else {
       $CustSelection = "and   psm.alt_principal_chain_uid in (". mysqli_real_escape_string($this->dbConn->connection, $CustomerUid) . ")";
    }
  	
 	  
      $sql = "select dm.document_number, 
                     dh.invoice_date, 
                     if(pdt.description is NULL or dm.document_type_uid = 27 , dt.description, pdt.description ) as 'description', 
                     dm.document_type_uid,
                     dh.source_document_number, 
                     dh.invoice_total,
                     trim(psm.deliver_name) as 'CustomerName' ,
                     " . $paymentBy . " as 'PaymentBy',
                     dh.payment_status,
                     dh.payment_number
              from      document_master dm
              left Join principal_document_type pdt on dm.principal_uid = pdt.principal_uid and dm.document_type_uid = pdt.document_type_uid , 
                        document_header dh, 
                        document_type dt,
                        principal_store_master psm
              left join principal_chain_master pcm on pcm.uid = psm.alt_principal_chain_uid 
              where     dm.uid = dh.document_master_uid 
              and       dm.document_type_uid = dt.uid
              and       dh.principal_store_uid = psm.uid
              and       dm.principal_uid =  '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
              and       dm.document_type_uid in (1,6,13,27,4,31,47,52,33,32) 
              and       dh.document_status_uid in (73,76,77,78,81)
              and       dh.payment_status <> " .IGNORE_INVOICE . "
              and       dh.invoice_date between  '" . mysqli_real_escape_string($this->dbConn->connection, $StartDate) . "' and '" . mysqli_real_escape_string($this->dbConn->connection, $EndDate). "' 
              "  .  $CustSelection . " ;";
              
//              echo $sql;
              
      $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 
  // **************************************************************************************************************************************************** 
  public function getCustomerPaymentDocuments($principalId, $CustomerUid, $StartDate, $Enddate) {
 	  
      $sql = "select  ph.payment_number, 
                      ph.payment_date,
                      pt.pay_type, 
                      dm.document_number, 
                      pd.payment_amount
             from payment_header ph, 
                  payment_detail pd, 
                  payment_type pt, 
                  document_master dm 
             where ph.uid = pd.payment_header_uid 
             and ph.type_uid = pt.uid 
             and pd.document_master_uid = dm.uid 
             and ph.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
             and ph.payment_date between '" . mysqli_real_escape_string($this->dbConn->connection, $StartDate) . "' and  '" . mysqli_real_escape_string($this->dbConn->connection, $Enddate). "'
             and ph.principal_store_uid =  '" . mysqli_real_escape_string($this->dbConn->connection, $CustomerUid) . "'
             and ph.status = 'A';";
      

      $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 
  // **************************************************************************************************************************************************** 
  public function getCustomerPaymentTotals($principalId, $CustomerUid, $StartDate, $Enddate) {
 	  
      $sql = "select  ph.payment_number, 
                      ph.payment_date,
                      pt.pay_type, 
                      '', 
                      ph.amount as 'payment_amount'
             from payment_header ph, 
                  payment_type pt
             where ph.type_uid = pt.uid 
             and ph.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
             and ph.payment_date between '" . mysqli_real_escape_string($this->dbConn->connection, $StartDate) . "' and  '" . mysqli_real_escape_string($this->dbConn->connection, $Enddate). "'
             and ph.principal_store_uid =  '" . mysqli_real_escape_string($this->dbConn->connection, $CustomerUid) . "'
             and ph.status = 'A';";
      
// echo $sql;
      $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 
  // **************************************************************************************************************************************************** 


  public function getDetailedLedgerData($CustomerUid) {
 	  
 	  $tsql = "select `sort`,
 	                  `Field1`,
                    `Field2`,
                    `Field3`,
                    `Field4`,
                    `Field5`
             from temp_ledger where 1
             order by sort, Field1;";
    $mfT = $dbConn->dbGetAll($tsql);
 	  return $mfT;
 	} 
// *************************************************************************************************************************
// Billing Queries Begin Here
// *************************************************************************************************************************
	public function CreateTempBillingReportTable() {

      $bldsql = "DROP TABLE IF EXISTS temp_billing_report;   ";

      $dtresult = $this->dbConn->dbQuery($bldsql);
      $this->dbConn->dbQuery("commit");

      $bldsql = "CREATE TABLE temp_billing_report (`Field1`  VARCHAR(60) NULL,
                                                   `Field2`  VARCHAR(60) NULL,
                                                   `Field3`  VARCHAR(60) NULL,
                                                   `Field4`  VARCHAR(60) NULL,
                                                   `Field5`  VARCHAR(60) NULL,
                                                   `Field6`  VARCHAR(60) NULL,
                                                   `Field7`  VARCHAR(60) NULL,
                                                   `Field8`  VARCHAR(60) NULL,
                                                   `Field9`  VARCHAR(60) NULL,
                                                   `Field10` VARCHAR(60) NULL,
                                                   `Field11` VARCHAR(60) NULL,
                                                   `Field12` VARCHAR(60) NULL,
                                                   `Field13` VARCHAR(60) NULL,
                                                   `Field14` VARCHAR(60) NULL,
                                                   `Field15` VARCHAR(60) NULL,
                                                   `Sort` TINYINT(1) NULL);";

      $dtresult = $this->dbConn->dbQuery($bldsql);
      $this->dbConn->dbQuery("commit");
  }
// *************************************************************************************************************************
	public function CreateTempBillingTransactionTable() {

      $bldsql = "DROP TABLE IF EXISTS temp_billing ;";

      $dtresult = $this->dbConn->dbQuery($bldsql);
      $this->dbConn->dbQuery("commit");

      $bldsql = "CREATE TABLE temp_billing  (`Field1`  VARCHAR(20),
                                             `Field2`  VARCHAR(20),
                                             `Field3`  VARCHAR(20),
                                             `Field4`  VARCHAR(20),
                                             `Field5`  VARCHAR(20),
                                             `Field6`  VARCHAR(20),
                                             `Field7`  VARCHAR(20),
                                             `Field8`  VARCHAR(20),
                                             `Field9`  VARCHAR(20));";

      $dtresult = $this->dbConn->dbQuery($bldsql);
      $this->dbConn->dbQuery("commit");
  }
// *************************************************************************************************************************

	public function getBillingPrincipalDetailsArray($principalArray) {
      $sql="select pr.uid,
                   pr.principal_uid, 
                   pr.principal_product_uid, 
                   pr.charge, 
                   pr.report_type,
                   pr.document_types,
                   pr.document_status,
                   pr.warehouses_to_exclude
           from .principal_charge_rate pr, principal p
           where pr.principal_uid = p.uid
           and   pr.principal_uid in ( " .$principalArray. ")
           and   pr.`status` = 'A'
           order by pr.principal_uid";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[$row['uid']] = $row;
		}

		return $arr;
	}
// *************************************************************************************************************************
// Document Charge - Orders
// *************************************************************************************************************************

   public function SetBillingDocumentCharge($principalId, 
                                            $rate, 
                                            $doctypes,
                                            $start, 
                                            $end, 
                                            $whtoexclude, 
                                            $chargeType,
                                            $filestore) {
                                            	
   $crtTemp = $this->CreateTempBillingReportTable();                                            	   
   
   // Report Headings
   $Heading1 = $this->WriteFileHeadings($chargeType, "1") ;
   
   // Extract Orders and write to temp report file
   
   if(mysqli_real_escape_string($this->dbConn->connection, $whtoexclude) == NULL) {
   	   $depsel = "";
   	} else {
   		  $depsel = "and dm.depot_uid NOT IN (". mysqli_real_escape_string($this->dbConn->connection, $whtoexclude) . ")";
   	}
    $sql = "INSERT INTO `temp_billing_report` (`Field1`, 
                    `Field2`, 
                    `Field3`, 
                    `Field4`, 
                    `Field5`, 
                    `Field6`, 
                    `Field7`, 
                    `Field8`, 
                    `Field9`, 
                    `Field10`,
                    `Field11`,
                    `Field12`,
                    `Field13`,
                    `Field14`,
                    `Sort`,
                    `Field15`) (select  dm.processed_date,                                             
                                        mid(dm.document_number,3,6),                                  
                                        d.description,
                                        'Accepted',
                                        count(dm.document_number),
                                        round(". mysqli_real_escape_string($this->dbConn->connection, $rate) . ",2),
                                        round(count(dm.document_number) * ". mysqli_real_escape_string($this->dbConn->connection, $rate) . ",2),
                                        '',
                                        '',
                                        dh.data_source,
                                        p.name,
                                        '". mysqli_real_escape_string($this->dbConn->connection, $start) ."',
                                        '". mysqli_real_escape_string($this->dbConn->connection, $end) ."',
                                        dp.name,
                                        '2',
                                        if(dm.principal_uid in (74,293), psm.deliver_name, '')
                               from .document_master dm, 
                                     document_header dh,
                                     document_type d,
                                     principal_store_master psm, 
                                     principal p,
                                     depot dp
                               where dm.uid = dh.document_master_uid
                               and   dh.principal_store_uid = psm.uid
                               and dm.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                               and dm.processed_date between '". mysqli_real_escape_string($this->dbConn->connection, $start) . ",' 
                                                     and     '". mysqli_real_escape_string($this->dbConn->connection, $end) . ",'
                               and dm.document_type_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $doctypes) ." )
                               and dm.document_type_uid = d.uid 
                               and dm.principal_uid = p.uid
                               and dm.depot_uid = dp.uid
                               " .$depsel. "
                               group by dm.document_number
                               order by dm.processed_date);" ;
                      $itresult = $this->dbConn->dbQuery($sql);
                      $this->dbConn->dbQuery("commit");
// Section 2 Totals

    $sql = "INSERT INTO `temp_billing_report` (`Field1`, 
                    `Field2`, 
                    `Field3`, 
                    `Field4`, 
                    `Field5`, 
                    `Field6`, 
                    `Field7`, 
                    `Field8`, 
                    `Field9`, 
                    `Field10`,
                    `Field11`,
                    `Field12`,
                    `Field13`,
                    `Field14`,
                    `Sort`) (select  'Total',
                                     '',
                                     '',
                                     '',
                                     sum(Field5),
                                     '',
                                     round(sum(Field7),2),
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '3'
                               from `temp_billing_report` tbr
                               where tbr.Sort = 2);" ;
                               
                      $itresult = $this->dbConn->dbQuery($sql);
                      $this->dbConn->dbQuery("commit");

// ********************************************************************************************
   // Extract Invoices and write to temp report file
   
   if(mysqli_real_escape_string($this->dbConn->connection, $whtoexclude) == NULL) {
   	   $depsel = "";
   	} else {
   		  $depsel = "and dm.depot_uid NOT IN (". mysqli_real_escape_string($this->dbConn->connection, $whtoexclude) . ")";
   	}
    $sql = "INSERT INTO `temp_billing_report` (`Field1`, 
                    `Field2`, 
                    `Field3`, 
                    `Field4`, 
                    `Field5`, 
                    `Field6`, 
                    `Field7`, 
                    `Field8`, 
                    `Field9`, 
                    `Field10`,
                    `Field11`,
                    `Field12`,
                    `Field13`,
                    `Field14`,
                    `Sort`) (select  b.order_date,                                             
                                    mid(dm.document_number,3,6), 
                                    d.description,
                                    s.description,
                                    if (dm.document_type_uid in (2) or f.general_reference_1 is null ,'0', count(dm.document_number)),
                                    round(". mysqli_real_escape_string($this->dbConn->connection, $rate) . ",2),
                                    if (dm.document_type_uid in (2) or f.general_reference_1 is null ,'0', round(count(dm.document_number) * " . mysqli_real_escape_string($this->dbConn->connection, $rate) ." ,2)),
                                    mid(b.invoice_number,3,6),
                                    b.invoice_date,      
                                    f.general_reference_1,                                                 
                                    e.name,
                                    '". mysqli_real_escape_string($this->dbConn->connection, $start) ."',
                                    '". mysqli_real_escape_string($this->dbConn->connection, $end) ."',
                                    dp.name,
                                    '4'
                            from `document_master` dm
                            left join `smart_event` f on dm.uid = f.data_uid and f.`type` = 'EXT' and f.`status` = 'C'
                            left join `document_header` b on dm.uid = b.document_master_uid   
                            left join `document_type` d on dm.document_type_uid = d.uid 
                            left join `principal` e on dm.principal_uid = e.uid 
                            left join `status` s on b.document_status_uid = s.uid
                            left join `depot` dp on dm.depot_uid = dp.uid                        
                            where dm.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                            and   b.invoice_date between '". mysqli_real_escape_string($this->dbConn->connection, $start) . ",' 
                                                   and     '". mysqli_real_escape_string($this->dbConn->connection, $end) . ",'
                            and dm.document_type_uid in   (". mysqli_real_escape_string($this->dbConn->connection, $doctypes) ." )
                            and b.document_status_uid in (76,77,78,47,73)"
                            . $depsel . "                            
                            group by dm.document_number, f.general_reference_1
                            order by b.invoice_date);;" ;
                      $itresult = $this->dbConn->dbQuery($sql);
                      $this->dbConn->dbQuery("commit");

                      $Heading1 = $this->WRitefileheadings(99, "5") ;

// Section 2 Totals

    $sql = "INSERT INTO `temp_billing_report` (`Field1`, 
                    `Field2`, 
                    `Field3`, 
                    `Field4`, 
                    `Field5`, 
                    `Field6`, 
                    `Field7`, 
                    `Field8`, 
                    `Field9`, 
                    `Field10`,
                    `Field11`,
                    `Field12`,
                    `Field13`,
                    `Field14`,
                    `Sort`) (select  'Total',
                                     '',
                                     '',
                                     '',
                                     sum(Field5),
                                     '',
                                     round(sum(Field7),2),
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '6'
                               from `temp_billing_report` tbr
                               where tbr.Sort = 4);" ;
                               
                      $itresult = $this->dbConn->dbQuery($sql);
                      $this->dbConn->dbQuery("commit");

   // Insert Blank Line  

        $Heading1 = $this->WriteFileHeadings(99, "7") ;

// Section 2 Totals

    $sql = "INSERT INTO `temp_billing_report` (`Field1`, 
                    `Field2`, 
                    `Field3`, 
                    `Field4`, 
                    `Field5`, 
                    `Field6`, 
                    `Field7`, 
                    `Field8`, 
                    `Field9`, 
                    `Field10`,
                    `Field11`,
                    `Field12`,
                    `Field13`,
                    `Field14`,
                    `Sort`) (select  'Grand Total',
                                     '',
                                     '',
                                     '',
                                     sum(Field5),
                                     '',
                                     round(sum(Field7),2),
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '8'
                               from `temp_billing_report` tbr
                               where tbr.Sort in (2,4));" ;
                               
                      $itresult = $this->dbConn->dbQuery($sql);
                      $this->dbConn->dbQuery("commit");

       // Get principal Name
              
       $sql = "select `name`
             from `principal` p
             where  p.uid =" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
             Limit 1";
         
       $prresult = $this->dbConn->dbGetAll($sql);
       
       $output = $this->ExportSaveFile($filestore, mysqli_real_escape_string($this->dbConn->connection, $end), trim($prresult[0]['name']), " Summary Report " ); 
       
// Return Document Total 
       $sql = "select sum(Field5) as 'total' from `temp_billing_report` tbr where tbr.Sort in (2,4);";
       $docresult = $this->dbConn->dbGetAll($sql);
//       print_r($docresult);  
       return $docresult;
        
   }
   
// *************************************************************************************************************************
// Turnover Charge
// *************************************************************************************************************************
   public function SetBillingTurnoverCharge($principalId, 
                                            $rate,  
                                            $doctypes,
                                            $start, 
                                            $end, 
                                            $whtoexclude, 
                                            $chargeType,
                                            $filestore,
                                            $doc_status) {

   $crtTemp = $this->CreateTempBillingReportTable();   
   // Report Headings
   $Heading1 = $this->WriteFileHeadings($chargeType, "1") ;

   if(mysqli_real_escape_string($this->dbConn->connection, $whtoexclude) == NULL) {
   	   $depsel = "";
   	} else {
   		  $depsel = "and dm.depot_uid NOT IN (". mysqli_real_escape_string($this->dbConn->connection, $whtoexclude) . ")";
   	}

   if(mysqli_real_escape_string($this->dbConn->connection, $doc_status) == NULL) {
   	   $docstat = "and dh.document_status_uid in (76,77,78,81,73)";
   	} else {
   		  $docstat = "and dh.document_status_uid in (". mysqli_real_escape_string($this->dbConn->connection, $doc_status) . ")";
   	}

   	
   $sql = "INSERT INTO `temp_billing_report` (`Field1`, 
                                              `Field2`, 
                                              `Field3`, 
                                              `Field4`, 
                                              `Field5`, 
                                              `Field6`, 
                                              `Field7`, 
                                              `Field8`, 
                                              `Field9`, 
                                              `Field10`,
                                              `Field11`,
                                              `Field12`,
                                              `Field13`,
                                              `Field14`,
                                              `Field15`,
                                              `Sort`) (select d.name,
                                                              pcm.description,
                                                              substr(dm.document_number,3,6),
                                                              dt.description as 'Document_Type',
                                                              psm.deliver_name,
                                                              dh.customer_order_number,
                                                              sum(dd.document_qty),
                                                              round(sum(dd.extended_price),0),
                                                              round(sum(dd.vat_amount),0),
                                                              round(sum(dd.total),0),
                                                              dh.invoice_date,
                                                              '". mysqli_real_escape_string($this->dbConn->connection, $start) ."',
                                                              '". mysqli_real_escape_string($this->dbConn->connection, $end)   ."',
                                                              '',
                                                              sfd.value,
                                                              '2'
                                                       from .document_master dm,
                                                             document_header dh, 
                                                             document_detail dd ,
                                                             principal p,
                                                             depot d, 
                                                             principal_chain_master pcm,
                                                             document_type dt,
                                                             principal_store_master psm                                                             
                                                       LEFT JOIN .special_field_fields sff ON sff.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                                                                                           AND sff.name LIKE '%Voqado Account%'
                                                       LEFT JOIN .special_field_details sfd ON sfd.field_uid = sff.uid
                                                                                            AND sfd.entity_uid = psm.uid 
                                                       LEFT JOIN .special_fields_debtors sft ON sft.sfd_value = sfd.value
                                                                                             AND sft.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "										                                     
                                                       where dm.uid = dh.document_master_uid
                                                       and   dm.uid = dd.document_master_uid
                                                       and   dm.depot_uid = d.uid
                                                       AND   dm.principal_uid = p.uid
                                                       and   dh.principal_store_uid = psm. uid
                                                       and   psm.principal_chain_uid = pcm.uid
                                                       and   dm.document_type_uid = dt.uid
                                                       and   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                                                       and   dh.invoice_date between '". mysqli_real_escape_string($this->dbConn->connection, $start) ." ' 
                                                                             and '". mysqli_real_escape_string($this->dbConn->connection, $end) ."'
                                                       and   dm.document_type_uid in (". mysqli_real_escape_string($this->dbConn->connection, $doctypes) ." )
                                                       " .$docstat . "
                                                       " .$depsel  . "
                                                       AND   if(p.split_debtors = 'Y', sft.debtors = 'Y', 1=1) 
                                                       group by dm.uid
                                                       order by dt.description, dh.invoice_date);";
                      $itresult = $this->dbConn->dbQuery($sql);
                      $this->dbConn->dbQuery("commit");

    $sql = "INSERT INTO `temp_billing_report` (`Field1`, 
                    `Field2`, 
                    `Field3`, 
                    `Field4`, 
                    `Field5`, 
                    `Field6`, 
                    `Field7`, 
                    `Field8`, 
                    `Field9`, 
                    `Field10`,
                    `Field11`,
                    `Field12`,
                    `Field13`,
                    `Field14`,
                    `Field15`,
                    `Sort`) (select  'Total',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     round(sum(Field7),0),
                                     round(sum(Field8),0),
                                     round(sum(Field9),0),
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '3'
                               from `temp_billing_report` tbr
                               where tbr.Sort = 2);" ;
                               
                      $itresult = $this->dbConn->dbQuery($sql);
                      $this->dbConn->dbQuery("commit");

 $Heading1 = $this->WriteFileHeadings(99, "4") ;
 
    $sql = "INSERT INTO `temp_billing_report` (`Field1`, 
                    `Field2`, 
                    `Field3`, 
                    `Field4`, 
                    `Field5`, 
                    `Field6`, 
                    `Field7`, 
                    `Field8`, 
                    `Field9`, 
                    `Field10`,
                    `Field11`,
                    `Field12`,
                    `Field13`,
                    `Field14`,
                    `Field15`,
                    `Sort`) (select  'Rate %',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     ". mysqli_real_escape_string($this->dbConn->connection, $rate) .",
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '5'
                               from `temp_billing_report` tbr
                               where tbr.Sort = 2 LIMIT 1);" ;
                               
                      $itresult = $this->dbConn->dbQuery($sql);
                      $this->dbConn->dbQuery("commit");

 $Heading1 = $this->WriteFileHeadings(99, "6") ; 

    $sql = "INSERT INTO `temp_billing_report` (`Field1`, 
                    `Field2`, 
                    `Field3`, 
                    `Field4`, 
                    `Field5`, 
                    `Field6`, 
                    `Field7`, 
                    `Field8`, 
                    `Field9`, 
                    `Field10`,
                    `Field11`,
                    `Field12`,
                    `Field13`,
                    `Field14`,
                    `Field15`,
                    `Sort`) (select  'Charge',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     round( " . mysqli_real_escape_string($this->dbConn->connection, $rate) . " * 
                                      (select sum(Field8) from `temp_billing_report` tbr where tbr.Sort = 2) / 100,0),
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '',
                                     '7'
                               from `temp_billing_report` tbr
                               where tbr.Sort = 2 LIMIT 1);" ;
                               
                      $itresult = $this->dbConn->dbQuery($sql);
                      $this->dbConn->dbQuery("commit"); 
    
       // Get principal Name
              
       $sql = "select `name`
               from `principal` p
               where  p.uid =" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               Limit 1";
         
       $prresult = $this->dbConn->dbGetAll($sql);
       
       $output = $this->ExportSaveFile($filestore, mysqli_real_escape_string($this->dbConn->connection, $end), trim($prresult[0]['name']), " Turnover Report " ); 
       
// Return Document Total 
       $sql = "select sum(Field8) as 'total' from `temp_billing_report` tbr where tbr.Sort in (2);";
       $toresult = $this->dbConn->dbGetAll($sql);
//       print_r($toresult);  
       return $toresult;

} 
// End Of Turnover Charge
// *************************************************************************************************************************
   public function WriteFileHeadings($chargeType, $sortOrder) {
   // Write file Headings
      $sql = "select bh.headings
              from .billing_report_headings bh
              where bh.charge_type = " . mysqli_real_escape_string($this->dbConn->connection, $chargeType) .";";

      $arr =$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
        $arr = $row;
    }
    $sql = "INSERT INTO `temp_billing_report` (`Field1`, 
                                               `Field2`, 
                                               `Field3`, 
                                               `Field4`, 
                                               `Field5`, 
                                               `Field6`, 
                                               `Field7`, 
                                               `Field8`, 
                                               `Field9`, 
                                               `Field10`,
                                               `Field11`,
                                               `Field12`,
                                               `Field13`,
                                               `Field14`,
                                               `Field15`,
                                               `Sort`)
            VALUES (" . $arr['headings'] ."," . $sortOrder . ");";                                   

            $itresult = $this->dbConn->dbQuery($sql);
            $this->dbConn->dbQuery("commit");
	}	
// *************************************************************************************************************************
   public function ExportSaveFile($filestore, $end, $principalName, $reportn) {
   	
       $path = $filestore . "archives/billing/" . date("Y", strtotime($end)) . "/" . date("F", strtotime($end));
       if(!is_dir($path)) {mkdir($path, 0777, true);}
       $fileName = $path . "/" . ucwords(strtolower(trim(str_replace("'"," ",str_replace("/"," ",stripslashes($principalName)))))) . $reportn . date("F", strtotime($end)) ." ". date("Y", strtotime($end)) . ".csv";

       $utresult= Array();
        $tsql = "select `Field1`,   
                        `Field2`,   
                        `Field3`,   
                        `Field4`,   
                        `Field5`,   
                        `Field6`,   
                        `Field7`,   
                        `Field8`,   
                        `Field9`,   
                        `Field10`,  
                        `Field11`,  
                        `Field14`,
                        `Field15`
                        `Field12`,  
                        `Field13`
         from `temp_billing_report` 
         where 1
         order by `Sort`, Field1;";
        $utresult = $this->dbConn->dbGetAll($tsql);

      if (count($utresult) == 0) {
         file_put_contents($fileName, "No Rows Selected<BR><BR>", FILE_APPEND);
	       return;	
      }

      $cnt = 0;

      foreach ($utresult as $brow) {
         if($cnt==0) {file_put_contents($fileName, implode(',',$brow) . "\n");} else {
            file_put_contents($fileName, implode(',',$brow) . "\n", FILE_APPEND);	
         }
         $cnt++;
      }
   }  // End Of Export
// *************************************************************************************************************************
   public function InsertIntoBillingTransaction($principalId,
                                                $productId,
                                                $rate,                                                             
                                                $quantity,
                                                $cost,
                                                $turnover,
                                                $topercentage,
                                                $StartDate,    
                                                $EndDate)  {

      $sql = "INSERT INTO `temp_billing` (`Field1`, 
                                          `Field2`, 
                                          `Field3`, 
                                          `Field4`, 
                                          `Field5`, 
                                          `Field6`, 
                                          `Field7`, 
                                          `Field8`,
                                          `Field9`)
            VALUES ("  . mysqli_real_escape_string($this->dbConn->connection, $principalId)   . ",
                    '" . mysqli_real_escape_string($this->dbConn->connection, $productId)     . "',  
                    '" . mysqli_real_escape_string($this->dbConn->connection, $rate)          . "',  
                    '" . mysqli_real_escape_string($this->dbConn->connection, $quantity)      . "',  
                    '" . mysqli_real_escape_string($this->dbConn->connection, $cost)          . "',  
                    '" . mysqli_real_escape_string($this->dbConn->connection, $turnover)      . "',  
                    '" . mysqli_real_escape_string($this->dbConn->connection, $topercentage)  . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $StartDate)     . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $EndDate)       . "');";                                   

            $itresult = $this->dbConn->dbQuery($sql);
            $this->dbConn->dbQuery("commit");
    }
// *************************************************************************************************************************
   public function CalculateMinimumCharge() {
   	
   	   $sql = "select * 
   	           from temp_billing bt
   	           where trim(bt.Field2) = 95924";
   	           $minarr = $this->dbConn->dbGetAll($sql);
             
       foreach($minarr as $mline) {
      	   
            $tsql = "select sum(dd.Field5)
                     from temp_billing dd 
                     where dd.Field1 = " . $mline['Field1'] . "
                     and trim(dd.Field2) <> 95924";
            $txtotal = $this->dbConn->dbGetAll($tsql);
            $minadj = 0;
            if($txtotal[0]['sum(dd.Field5)'] < $mline['Field3'] )	{       //   Sales less than minimum Charge
                 $minadj = $mline['Field3'] - $txtotal[0]['sum(dd.Field5)'] ;      // Subtract the sales from the minimum to get the adjustment
            } 
       	    $usql =  "update temp_billing bt set bt.Field3 = " .$minadj . "
   	                  where trim(bt.Field2) = 95924
   	                  and bt.Field1 = " . $mline['Field1'];  
           
            $usql = $this->dbConn->dbQuery($usql);
            $this->dbConn->dbQuery("commit");
       }           	           
 }

// *************************************************************************************************************************
   public function ExtractSystemOrders($end, $filestore) {
   	
   	// H,1,49,20180118,4538932722,109603,PNP HYPER - FAERIE GLEN (HC10),CNR ATTERBURY AND SE,LIKATS CAUSE,,PNP HYPER,PO BOX 1310,BEDFORDVIEW,GAUTENG,205,ANTEL JOHANNESBURG,460,Pick n Pay Inland,6001007010003,,800998,950159
    // D,0101,377529,VERG 100% E/VIRGIN OIL 12X500,1,487.08,0
    // T,00003
       $sql = "select trim(tb.Field1) as 'Principal_Uid',
                      trim(tb.Field2) as 'Prod_Uid',
                      trim(tb.Field3) as 'Rate',
                      if(trim(tb.Field4)='',0,trim(tb.Field4)) as 'Quantity',
                      trim(tb.Field5) as 'Total',
                      trim(tb.Field6) as 'Turnover',
                      trim(tb.Field7) as 'topercent',
                      trim(tb.Field8) as 'SDate',
                      trim(tb.Field9) as 'EDate',                                                                  
                      trim(p.name) as 'Principal', 
                      trim(p.physical_add1) as 'add1',
                      trim(p.physical_add2) as 'add2',
                      trim(p.physical_add3) as 'add3',
                      trim(pp.product_code) as 'Code',
                      trim(pp.product_description) as 'product_description'
               from temp_billing tb, 
                    principal p, 
                    principal_product pp
               where trim(tb.Field1) = p.uid
               and   trim(tb.Field2) = pp.uid";
                             
              $utresult = $this->dbConn->dbGetAll($sql);

       $sequenceDAO = new SequenceDAO($this->dbConn);
       $sequenceTO  = new SequenceTO;
       $errorTO     = new ErrorTO;
       $sequenceTO->sequenceKey=LITERAL_SEQ_KOS_EXTRACT;
       $sequenceTO->depotUId = '';
       $sequenceTO->principalUId = 309;
       $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
       
       $path = $filestore . "ftp/kwelanga/" ;
       if(!is_dir($path)) {mkdir($path, 0777, true);}
       $fileName = $path . "KOS" . str_pad($seqVal,5,"0",STR_PAD_LEFT) . ".csv";
       
       if (count($utresult) == 0) {
          file_put_contents($fileName, "No Rows Selected<BR><BR>", FILE_APPEND);
	        return;	
       }
       $cnt = 0;
       $pidstore = '';

       foreach ($utresult as $brow) {
       	   if($pidstore <> $brow['Principal_Uid']) {
       	      // Write header record	
                 $fileHrow = "H,1,309,"
                 . str_replace('-','',$end) . ","
                 . str_replace('-','',$end) . "," 
                 . $brow['Principal'] . ","
                 . $brow['add1'] . ","
                 . $brow['add2'] . ","
                 . $brow['add3'] . "," 
                 . $brow['Principal_Uid'] . ","
                 . 'KOS'  . ","     // WH Look Up //
                 . ''  . ","
                 . ''  . ","
                 . ''  . ","
                 . ''  . ","
                 . ''  . "," ; 
                 

                 if($cnt == 0) {
                     file_put_contents($fileName, $fileHrow . "\n");
	               } else {
	                   file_put_contents($fileName, $fileHrow . "\n", FILE_APPEND);
	               }
                 echo $brow['Principal'];
//                 echo $fileHrow;
                 $cnt++;
                 echo "<br>";      	   	
                 $pidstore = $brow['Principal_Uid'];
           }
           
//           D,0101,377529,VERG 100% E/VIRGIN OIL 12X500,1,487.08,0
           
           $fileDrow = "D,0101," 
                      . $brow['Code']                . ","
                      . $brow['product_description'] . ","
                      . $brow['Quantity']            . ","
                      . $brow['Rate']                . ","
                      . $brow['Total']               . ","
                      . $brow['Turnover']            . ","
                      . $brow['topercent']           . ","
                      . $brow['SDate']               . ","
                      . $brow['EDate'];
                      
//            echo $fileDrow;
            file_put_contents($fileName, $fileDrow . "\n", FILE_APPEND);          
            $cnt++;
            echo "<br>";    
       }
       $cnt++;
       $fileTrow = "T," . str_pad($cnt,5,"0",STR_PAD_LEFT);      
//       echo $fileTrow;
       file_put_contents($fileName, $fileTrow, FILE_APPEND);
       $cnt++;
      echo "<br>"; 
   	
   }	
// *************************************************************************************************************************
  public function getCustomerAgeing($CustomerUid, $PaymentBy, $EndDate) {
  	
      $sql = "select cb.total_due     as 'Total Due',
                     cb.current       as 'Current',
                     cb.30days        as '30 Days',
                     cb.60days        as '60 Days',
                     cb.90days        as '90 Days',
                     cb.120days       as 'Over90 Days' 
              from .customer_balance cb
              where cb.month_end_date  = '" . mysqli_real_escape_string($this->dbConn->connection, $EndDate)    . "'
              and   concat(trim(cb.payment_by),trim(cb.customer_uid)) in (
                            concat(trim(" . mysqli_real_escape_string($this->dbConn->connection, $PaymentBy)   . "),
                                   trim(" . mysqli_real_escape_string($this->dbConn->connection, $CustomerUid) . ")))";

  	  $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 
  // **************************************************************************************************************************************************** 
  public function getSummaryPaymentDocuments($principalId, $StartDate, $Enddate) {
 	  
      $sql = "select  psm.deliver_name,
                      pt.pay_type,
                      ph.payment_date,
                      ph.capture_date,
                      pd.payment_amount,
                      ph.payment_number,
                      u.username as 'Captured By'
              from  payment_header ph, 
                    payment_detail pd, 
                    document_master dm, 
                    document_header dd,
                    principal_store_master psm, 
                    payment_type pt,
                    users u 
              where ph.uid = pd.payment_header_uid   
              and   pd.document_master_uid = dm.uid 
              and   dd.document_master_uid = dm.uid
              and   ph.principal_store_uid = psm.uid 
              and   ph.type_uid in (".PT_CASH.") 
              and   ph.type_uid = pt.uid 
              and   ph.captured_by = u.uid 
              and   ph.payment_date between '" . mysqli_real_escape_string($this->dbConn->connection, $StartDate)   . "' and  '" . mysqli_real_escape_string($this->dbConn->connection, $Enddate). "'
              and   ph.principal_uid   =        '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "';";

      $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 
// *************************************************************************************************************************
  public function getCashOutDocuments($principalId, $CustomerUid, $StartDate, $EndDate) {

      $sql = "select dm.document_number,
                     dh.principal_store_uid,
                     psm.deliver_name,
                     dh.invoice_date,
                     dh.invoice_total,
                     dh.payment_status,
                     dm.document_type_uid,
                     o.delivery_instructions,
                     dh.customer_order_number
              from document_master dm,
                   document_header dh,
                   orders o,
                   principal_store_master psm
              where dm.uid = dh.document_master_uid
              and   dh.principal_store_uid = psm.uid
              and   dm.principal_uid =      '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
              and   dh.invoice_date between '" . mysqli_real_escape_string($this->dbConn->connection, $StartDate)   . "'
                                    and     '" . mysqli_real_escape_string($this->dbConn->connection, $EndDate) . "'
              and   dm.document_type_uid in (" . DT_PAYMENTTO .")
              and   psm.principal_chain_uid in ('" . mysqli_real_escape_string($this->dbConn->connection, $CustomerUid) . "')
              and   o.order_sequence_no = dm.order_sequence_no;";

      $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 
// *************************************************************************************************************************
  public function getPrincipalBankingDetails($principalId) {

      $sql = "select p.banking_details, p.name as 'principal_name', pdt.statementmessage 
              from .principal p
              left join principal_document_type pdt on p.uid = pdt.principal_uid and pdt.document_type_uid = " . DT_STATEMENT . "  
              where p.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'";
 
              $bDTL = $this->dbConn->dbGetAll($sql);
              return $bDTL;
  
  } 
// *************************************************************************************************************************
  public function insertIntoGeneralTemp($userId, 
                                        $fldval, 
                                        $fldval2, 
                                        $fldval3,
                                        $fldval4, 
                                        $fldval5, 
                                        $fldval6,
                                        $fldval7, 
                                        $fldval8, 
                                        $fldval9,
                                        $fldval10, 
                                        $fldval11, 
                                        $fldval12,
                                        $fldval13,
                                        $fldval14, 
                                        $fldval15,
                                        $sort) {
 	  
      $isql = "INSERT INTO `temp_sales_report_" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "`
                                    (`Field1`,
                                     `Field2`,
                                     `Field3`,
                                     `Field4`,
                                     `Field5`,
                                     `Field6`,
                                     `Field7`,
                                     `Field8`,
                                     `Field9`,
                                     `Field10`,
                                     `Field11`,
                                     `Field12`,
                                     `Field13`,
                                     `Field14`,
                                     `Field15`, 
                                     `Sort`)                          
               VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $fldval)   . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval2)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval3)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval4)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval5)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval6)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval7)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval8)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval9)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval10) . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval11) . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval12) . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval13) . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval14) . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fldval15)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $sort)     . "')";
                      
             $itresult = $this->dbConn->dbQuery($isql);
             $this->dbConn->dbQuery("commit");
             
             return;
 } 
// **************************************************************************************************************************************************** 
  public function getMonthlyPrincipalSales($principalId, $YearDate) {

      $sql = "select year(dh.invoice_date) as 'Year',
                     MONTH(dh.invoice_date) as 'MM',  
                     MONTHNAME(dh.invoice_date) as 'Month',
                     pcm.uid as 'ChainId', 
                     trim(pcm.description) as 'Chain',
                     sum(dh.cases) as 'Cases', 
                     round(sum(dh.exclusive_total),0) as 'Total'
              from  document_master dm, 
                    document_header dh, 
                    principal_store_master psm,
                    principal_chain_master pcm
              where dm.uid = dh.document_master_uid
              and   dh.principal_store_uid = psm.uid
              and   pcm.uid = psm.alt_principal_chain_uid
              and   dm.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
              and   dm.document_type_uid in (1,6,13.4)
              and   dh.document_status_uid in (76,73,77,78,81)
              and   dh.invoice_date between '" . $YearDate . "-01-01' and '" . $YearDate . "-12-31'
              group by year(dh.invoice_date), month(dh.invoice_date), psm.alt_principal_chain_uid;";
              
              $bDTL = $this->dbConn->dbGetAll($sql);
              return $bDTL;
  
  } 
// *************************************************************************************************************************
  public function CheckForExistingRec($userId, $chainYear) {

      $sql = "select * 
              from temp_sales_report_" . mysqli_real_escape_string($this->dbConn->connection, $userId) . " t 
              where t.`Field15` = '" . mysqli_real_escape_string($this->dbConn->connection, $chainYear) . "';" ;

              $bDTL = $this->dbConn->dbGetAll($sql);
              return $bDTL;
  
  } 
// *************************************************************************************************************************
 public function UpdateGeneralTemp($userId, $fld, $total, $yycuid) {

      $sql = "update temp_sales_report_" . mysqli_real_escape_string($this->dbConn->connection, $userId) . " t 
                                          SET " . mysqli_real_escape_string($this->dbConn->connection, $fld) . " = " . mysqli_real_escape_string($this->dbConn->connection, $total) . " 
              where t.`Field15` = '" . mysqli_real_escape_string($this->dbConn->connection, $yycuid) . "' ;" ;

             $itresult = $this->dbConn->dbQuery($sql);
             
             $this->dbConn->dbQuery("commit");
             
             return;
  } 
// *************************************************************************************************************************
 public function voqadoReconReport($startDate, $endDate, $tableName) {
 	
        $sql1 = "SELECT p.name AS 'Principal', 
                        if(dm.document_type_uid in (" . DT_ORDINV . "," . DT_DELIVERYNOTE . "," . DT_ORDINV_ZERO_PRICE  . "),
                        if(p.uid in (71), UPPER(vth.Company),UPPER(vt.Company)), 
                        UPPER(vtc.Company)) AS 'Company',

                        if(dm.document_type_uid in (" . DT_ORDINV . "," . DT_DELIVERYNOTE . "," . DT_ORDINV_ZERO_PRICE  . "),
                        if(p.uid in (71), if(trim(vth.Reference) IS NULL,'1',''), 
                        if(trim(vt.Reference) IS NULL,'1','')) ,
                        if(trim(vtc.Reference) IS NULL,'1','') ) AS 'Missing from Voq',

                        if(dm.document_type_uid IN (1,6,13),if(p.uid not in (71), dm.document_number, dh.invoice_number), dm.alternate_document_number) AS 'Document Number', 
                        psm.deliver_name as 'Store',
                        dt.description AS 'Document Type',
                        s.description  AS 'Status',		 
                        round(dh.invoice_total,2) AS 'Invoice Total', 
                        dh.cases AS 'Invoice Cases', 
                        dh.invoice_date  AS 'Invoice Date',

                        if(dm.document_type_uid in (" . DT_ORDINV . "," . DT_DELIVERYNOTE . "," . DT_ORDINV_ZERO_PRICE  . "),
                        if(p.uid not in (71), vth.`Type`, vt.`Type`), 
                        vtc.`Type`) AS 'Voqado Type',
                        
                        if(dm.document_type_uid in (" . DT_ORDINV . "," . DT_DELIVERYNOTE . "," . DT_ORDINV_ZERO_PRICE  . "),
                        if(p.uid in (71), vth.Reference, vt.Reference), vtc.Reference ) AS 'Voqado Document No',

                        if(dm.document_type_uid in (" . DT_ORDINV . "," . DT_DELIVERYNOTE . "," . DT_ORDINV_ZERO_PRICE  . "),
                        if(p.uid in (71), vth.Amount, vt.Amount), vtc.Amount ) AS 'Voqado Amount',

                        if(dm.document_type_uid in (" . DT_ORDINV . "," . DT_DELIVERYNOTE . "," . DT_ORDINV_ZERO_PRICE  . "),
                        if(p.uid in (71), if(trim(vth.Reference) IS NULL,'1',''), 
                        if(trim(vt.Reference) IS NULL,'1','')) ,
                        if(trim(vtc.Reference) IS NULL,'1','') ) AS 'Missing from Voq',

                        if(dm.document_type_uid in (" . DT_ORDINV . "," . DT_DELIVERYNOTE . "," . DT_ORDINV_ZERO_PRICE  . "),
                        if(p.uid in (71), if(round(dh.invoice_total,2) - vth.Amount <> 0, 'Value', ''),
                        if(round(abs(dh.invoice_total),2) - vt.Amount <> 0, 'Value', '')) ,
                        if(round(abs(dh.invoice_total),2)  + vtc.Amount <> 0, 'Value', '')) AS 'Value Different',
                        se.general_reference_1 as 'SE Status',
                        '" . $startDate . "' as 'Report Start Date',
                        '" . $endDate   . "' as 'Report End Date'
                 FROM .voqado_extract_parameters vep
                 INNER JOIN .document_master dm ON dm.principal_uid = vep.principal_uid
                 LEFT JOIN   smart_event se on se.data_uid = dm.uid and se.type_uid = vep.notification_uid
                 INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid
                 INNER JOIN principal_store_master psm on psm.uid = dh.principal_store_uid
                 INNER JOIN .principal p ON p.uid = dm.principal_uid
                 INNER JOIN .document_type dt ON dt.uid = dm.document_type_uid
                 INNER JOIN .`status` s ON s.uid = dh.document_status_uid
                 LEFT JOIN " . mysqli_real_escape_string($this->dbConn->connection, $tableName) . " vt  ON replace(UPPER(vt.Company),  '_', '') = vep.voqado_code AND LPAD(vt.Reference,8,'0') = LPAD(dm.document_number,8,'0') AND vt.Type = if(dm.document_type_uid IN (1,6,13), 'IN', 'CR')
                 LEFT JOIN " . mysqli_real_escape_string($this->dbConn->connection, $tableName) . " vth ON replace(UPPER(vth.Company), '_', '') = vep.voqado_code AND LPAD(vth.Reference,8,'0') = LPAD(dh.invoice_number,8,'0') AND vth.Type = if(dm.document_type_uid IN (1,6,13), 'IN', 'CR')
                 LEFT JOIN " . mysqli_real_escape_string($this->dbConn->connection, $tableName) . " vtc ON replace(UPPER(vtc.Company), '_', '') = vep.voqado_code AND LPAD(vtc.Reference,8,'0') = LPAD(dm.alternate_document_number,8,'0') AND vtc.Type = if(dm.document_type_uid IN (1,6,13), 'IN', 'CR')
                 WHERE dh.invoice_date BETWEEN '" . mysqli_real_escape_string($this->dbConn->connection, $startDate) . "' 
                                       AND     '" . mysqli_real_escape_string($this->dbConn->connection, $endDate)   . "' 
                 AND   dm.document_type_uid IN ( " . DT_ORDINV             . ",
                                                 " . DT_DELIVERYNOTE       . ",
                                                 " . DT_ORDINV_ZERO_PRICE  . ", 
                                                 " . DT_CREDITNOTE         . ",  
                                                 " . DT_MCREDIT_OTHER      . ", 
                                                 " . DT_MCREDIT_PRICING    . ")
                 AND   dh.document_status_uid IN ( " . DST_INVOICED         . ",
                                                   " . DST_PROCESSED        . ",
                                                   " . DST_POD_SCANNED      . ",
                                                   " . DST_DELIVERED_POD_OK . ", 
                                                   " . DST_DIRTY_POD        . ")
                 ORDER BY p.name, dh.invoice_date; ";

              $bDTL = $this->dbConn->dbGetAll($sql1);
              return $bDTL;


}

// *************************************************************************************************************************
 public function deleteVoqadoTempTable($fileseqnumber) {

    $bldsql = "DROP TABLE IF EXISTS voqado_temp_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ";   ";
    $result = $this->dbConn->dbQuery($bldsql);

 }
// *************************************************************************************************************************
 public function deleteReportTempTable($fileseqnumber) {

    $bldsql = "DROP TABLE IF EXISTS voqado_reports_temp_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ";   ";
    $result = $this->dbConn->dbQuery($bldsql);

 } 
 // *************************************************************************************************************************
 public function createVoqadoTempTable($fileseqnumber) { 
 	
     $bldsql = "CREATE TABLE voqado_temp_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . " (`Company` VARCHAR(10) NULL,
                                                                                                                       `Type` VARCHAR(5) NULL,
                                                                                                                       `Reference` VARCHAR(15) NULL,
                                                                                                                       `Date` VARCHAR(20) NULL,
                                                                                                                       `Code` VARCHAR(10) NULL,
                                                                                                                       `Name` VARCHAR(50) NULL,
                                                                                                                       `excl_vat` VARCHAR(10) NULL,
                                                                                                                       `Vat` VARCHAR(10) NULL,
                                                                                                                       `Amount` VARCHAR(10) NULL,
                                                                                                                       `System_date` VARCHAR(20) NULL) ;";
                                     
    $result = $this->dbConn->dbQuery($bldsql);
 }
 
 // *************************************************************************************************************************
 public function createReportsTempTable($fileseqnumber) { 
 	
     $bldsql = "CREATE TABLE voqado_reports_temp_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . " (`record` VARCHAR(200) NULL) ;";
                                     
    $result = $this->dbConn->dbQuery($bldsql);
 }
 // *************************************************************************************************************************
 public function loadVoqadoTempTable($dirPath, $fileName, $fileseqnumber) {
 	
 	echo mysqli_real_escape_string($this->dbConn->connection, $dirPath) . trim(mysqli_real_escape_string($this->dbConn->connection, $fileName));
 	echo "<br>"; 
     $sql='LOAD DATA LOCAL INFILE "' . mysqli_real_escape_string($this->dbConn->connection, $dirPath) . trim(mysqli_real_escape_string($this->dbConn->connection, $fileName)) . '"  
           INTO TABLE voqado_temp_'  . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . '
           CHARACTER SET latin1
           FIELDS TERMINATED BY "," 
           OPTIONALLY ENCLOSED BY "\"" 
           ESCAPED BY "\\\" 
           LINES TERMINATED BY "\\r\\n" 
           IGNORE 1 LINES';	

     $rTO = $this->dbConn->processPosting($sql,"");     
     $this->dbConn->dbQuery("commit");
}

 // *************************************************************************************************************************
 public function loadReportsTempTable($dirPath, $fileName, $fileseqnumber) {
 	
     $sql='LOAD DATA LOCAL INFILE "' . mysqli_real_escape_string($this->dbConn->connection, $dirPath) . trim(mysqli_real_escape_string($this->dbConn->connection, $fileName)) . '"  
           INTO TABLE voqado_reports_temp_'  . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . '
           CHARACTER SET latin1
           FIELDS TERMINATED BY "x" 
           OPTIONALLY ENCLOSED BY "\"" 
           ESCAPED BY "\\\" 
           LINES TERMINATED BY "\\r\\n"';	

     $rTO = $this->dbConn->processPosting($sql,"");     
     $this->dbConn->dbQuery("commit");
}

 // *************************************************************************************************************************
  function SetUpReportDistribution($DocumentDiscription, $seqFilename, $destEmail )  {   
    
      $dResult = '';
      $recipientsCheckCount = 0;
     
      // SETUP DISTRIBUTION
      $postingDistributionTO = new PostingDistributionTO;
      $postingDistributionTO->DMLType = "INSERT";
      $postingDistributionTO->deliveryType = BT_EMAIL;
      $postingDistributionTO->subject = trim($DocumentDiscription);
      $postingDistributionTO->body = 'Attached is your '.trim($DocumentDiscription). ' From Kwelanga Solutions'; 
     //    if($filePath!=false){
            $postingDistributionTO->attachmentFile = $seqFilename;
     //    }
 
      $postingDistributionTO->destinationAddr = $destEmail;
      $postDistributionDAO = new PostDistributionDAO($this->dbConn);
      $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);

      if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
         $this->errorTO->type=FLAG_ERRORTO_ERROR;
         $this->errorTO->description = get_class($this)." Extract distribution queue failed for for nr.UID {$recipientUId} and Recipient/Contact: '{$re}'.";
         BroadcastingUtils::sendAlertEmail("System Error", $this->errorTO->description, "Y", true);
         return $this->errorTO;
      } else {
         $recipientsCheckCount++;  //successful
         $this->dbConn->dbinsQuery("commit");
      }
      return ($destEmail);	
  }
// ******************************************************************************************************************************************
  public function getKwelangaDebtorsAccount($principalId) {
 	  
      $sql = "SELECT *
              FROM special_fields_debtors sfd
              WHERE sfd.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
              AND   sfd.debtors = 'Y'
              ORDER BY sfd.name;";
      

      $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 
// ******************************************************************************************************************************************
  public function getKwelangaDebtorsReportType($principalId) {
 	  
      $sql = "SELECT *
              FROM .voqado_reports v
              WHERE v.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
              AND   v.`status` = 'A'
              ORDER BY `order`;";
      

      $gSBP = $this->dbConn->dbGetAll($sql);
      return $gSBP;
  
  } 
  // **************************************************************************************************************************************************** 
  public function getprincipalParams($principalId) {
 	  
      $sql = "SELECT *
              FROM .voqado_extract_parameters a
              WHERE a.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "';";
      

      $pParm = $this->dbConn->dbGetAll($sql);
      return $pParm;
  
  } 
  // **************************************************************************************************************************************************** 
  public function getTransporterReport($prinList, $depList, $transList, $startDate, $endDate) {
  	
  	  $tOwn =  
 	  
      $sql = "SELECT t.name AS 'Transporter / agent',
                     dt.tripsheet_number AS 'Tripsheet',
                     dt.tripsheet_date AS 'Trip Sheet Date',
                     p.name AS 'Principal',
                     dm.document_number AS 'Document Number',
                     psm.deliver_name AS 'Store',
                     s.description AS 'Status',
                     dh.cases AS 'Cases / Balers',
                     round(dh.exclusive_total,2) AS 'Exclusive Total',
                     dt.dispatch_number as 'Dispatch Number'
              FROM .transporter t
              LEFT JOIN document_tripsheet dt ON dt.transporter_id = t.uid
              INNER JOIN document_master dm ON dm.uid = dt.document_master_uid
              INNER JOIN document_header dh ON dh.document_master_uid = dm.uid AND dm.depot_uid = 392
              INNER JOIN principal_store_master psm ON psm.uid = dh.principal_store_uid
              INNER JOIN `status` s ON s.uid = dh.document_status_uid
              INNER JOIN principal p ON p.uid = dm.principal_uid
              WHERE t.uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $transList) . ")
              AND   t.depot_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $depList) . ")
              AND   dm.principal_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $prinList) . ")
              AND   dt.tripsheet_date BETWEEN '" . mysqli_real_escape_string($this->dbConn->connection, $startDate) . "' 
                                      AND     '" . mysqli_real_escape_string($this->dbConn->connection, $endDate)   . "'
              ORDER BY t.name, dt.tripsheet_number, dm.principal_uid, dm.document_number ;";

              $tRep = $this->dbConn->dbGetAll($sql);
              return $tRep;
  
  } 
  // **************************************************************************************************************************************************** 
  public function getTransporterOwnerList($whId) {
  	
  	  $sql = "SELECT DISTINCT(t.owner), d.uid, d.name, d.short_name
              FROM .transporter t
              LEFT JOIN depot d ON d.uid = t.depot_uid
              WHERE t.depot_uid IN ( " . mysqli_real_escape_string($this->dbConn->connection, $whId) . ")
              AND   t.`status` = 'A'
              ORDER BY d.name, t.owner";
  	  
              $tRep = $this->dbConn->dbGetAll($sql);
              return $tRep;
  }
  // **************************************************************************************************************************************************** 
  public function getUserTransporterList($whId, $userId, $status) {
  	
  	$sql = "SELECT t.uid, t.name, t.user_uid 
            FROM .transporter t 
            WHERE t.depot_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $whId) . ") 
            AND t.uid in (SELECT d.transporter_uid
                          FROM  user_depot_transporter d
                          WHERE d.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $whId) . "
            AND   d.user_uid = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . ")
            AND   t.`status` = 'A' 
            ORDER BY t.name;";

            $tRep = $this->dbConn->dbGetAll($sql);
            return $tRep;            
            
  }  
  
   
  // **************************************************************************************************************************************************** 
  public function deleteOwnerReportTempTable($fileseqnumber) {
      
       $sql = "DROP TABLE IF EXISTS temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . " ;";
       $result = $this->dbConn->dbQuery($sql);
       $this->dbConn->dbQuery("commit");	
       
       return;
  	
  }  
  

  // **************************************************************************************************************************************************** 
  public function createOwnerReportTempTable($fileseqnumber) {

        $sql = "CREATE TABLE temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . " (`Principal`  VARCHAR(15) NULL,
                                                                                                                             `Warehouse` VARCHAR(25) NULL,
                                                                                                                             `Date` VARCHAR(15) NULL,
                                                                                                                             `Document_No` VARCHAR(100) NULL,                    
                                                                                                                             `Store` VARCHAR(100) NULL,                    
                                                                                                                             `Doc_Type` VARCHAR(20) NULL,                   
                                                                                                                             `Status` VARCHAR(20) NULL,                     
                                                                                                                             `Trip_Sheet_Number` VARCHAR(20) NULL,          
                                                                                                                             `Driver` VARCHAR(50) NULL,                     
                                                                                                                             `Owner` VARCHAR(50) NULL,                      
                                                                                                                             `Cases` VARCHAR(20) NULL,                   
                                                                                                                             `Value` VARCHAR(20) NULL,                      
                                                                                                                             `GRV_Number` VARCHAR(20) NULL,
                                                                                                                             `Days`  VARCHAR(5) NULL,
                                                                                                                             `source_document_number` VARCHAR(20) NULL,
                                                                                                                             `Start_Date` VARCHAR(12) NULL,
                                                                                                                             `End_Date` VARCHAR(12) NULL,
                                                                                                                             `Principal_uid` VARCHAR(5) NULL,
                                                                                                                             `doc_uid` VARCHAR(10) NULL,     
                                                                                                                             `Spare` VARCHAR(20) NULL,
                                                                                                                             `Sort`  VARCHAR(3) NULL);";                   
  
       $result = $this->dbConn->dbQuery($sql);
       $this->dbConn->dbQuery("commit");	
       
       return;  
  
  }
  // **************************************************************************************************************************************************** 
  public function insertTransporterMTDSales($fileseqnumber, $whId, $sDate, $eDate, $ownStr) {
  	
  	   if($ownStr == "") {
              $tLine = "AND   t.owner IS NULL ";
  	   } else {
               $tLine = "AND   t.owner in ('" .$ownStr . "')";
  	   }
       $sql = "INSERT INTO temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . " (temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Principal`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Warehouse`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Date`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Document_No`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Store`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Doc_Type`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Status`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Trip_Sheet_Number`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Driver`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Owner`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Cases`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Value`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`GRV_Number`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Days`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`source_document_number`,                                                                                                                            
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Start_Date`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`End_Date`,                                                                                                                            
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Principal_uid`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`doc_uid`,
                                                                                                                           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Sort`)
         (SELECT p.name AS 'Principal',
                 d.name AS 'Warehouse',
                 dh.invoice_date AS 'Date',
                 dm.document_number, 
                 psm.deliver_name AS 'Store',
                 doc.description AS 'Doc Type',
                 s.description AS 'Status' ,
                 dh.tripsheet_number AS 'Trip Sheet Number', 
                 t.name AS 'Driver', 
                 if(t.owner IS NULL, 'ZZZUnknown Owner', t.owner) AS 'Owner', 
                 dh.cases AS 'Cases',
                 round(dh.exclusive_total,2) AS 'Value',
                 if(dh.document_status_uid = " . DST_INVOICED . ", 'POD/GRV Outstanding',if(dh.grv_number<>'',dh.grv_number,'Missing GRV')) AS 'GRV Number',
                 if(dh.document_status_uid = " . DST_INVOICED . ", CURDATE() - dh.invoice_date,'') AS 'Days Outstanding',
                 dh.source_document_number,
                 '" . mysqli_real_escape_string($this->dbConn->connection, $sDate) . "',
                 '" . mysqli_real_escape_string($this->dbConn->connection, $sDate) . "',
                 dm.principal_uid,
                 dm.uid, 
                 '1'    
          FROM .document_master dm
          INNER JOIN .document_header dh   ON dh.document_master_uid = dm.uid
          INNER JOIN .principal_store_master psm ON dh.principal_store_uid = psm.uid
          INNER JOIN .document_type doc ON doc.uid = dm.document_type_uid
          INNER JOIN .`status` s ON dh.document_status_uid = s.uid
          INNER JOIN principal p on p.uid = dm.principal_uid
          INNER JOIN depot d on d.uid = dm.depot_uid
          LEFT JOIN  transporter t ON dh.trip_transporter_uid = t.uid AND t.`status` ='A'
          WHERE dm.depot_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $whId) . ")
          AND   dh.invoice_date BETWEEN '" . mysqli_real_escape_string($this->dbConn->connection, $sDate) . "' 
                                AND     '" . mysqli_real_escape_string($this->dbConn->connection, $eDate) . "'
          AND   dm.document_type_uid IN (" . DT_ORDINV . ")
          AND   dh.document_status_uid IN (" . DST_INVOICED . "," . DST_DELIVERED_POD_OK . "," . DST_DIRTY_POD . ")
          "  . $tLine . ");" ;
  
          $itresult = $this->dbConn->dbQuery($sql);
          $this->dbConn->dbQuery("commit");
             
          return;

  }
  // **************************************************************************************************************************************************** 
  public function insertTransporterMTDCredits($fileseqnumber, $whId, $sDate, $eDate, $ownStr) {
  	
        $sql = "INSERT INTO temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . " (temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Principal`,                                                                                                                                                                                                                                         
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Warehouse`,                                                                                                                                                                                                                                         
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Date`,                     
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Document_No`,              
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Store`,                    
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Doc_Type`,                 
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Status`,                   
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Trip_Sheet_Number`,        
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Driver`,                   
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Owner`,                    
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Cases`,                    
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Value`,                    
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`GRV_Number`,               
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Days`,                     
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`source_document_number`,                                                                                                                 
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Start_Date`,               
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`End_Date`,                 
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Principal_uid`,            
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`doc_uid`,                  
                                                                                                                            temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".`Sort`)                     
         (SELECT p.name AS 'Principal',
                 d.name AS 'Warehouse',
                 dh.invoice_date AS 'Date',
                 dm.document_number, 
                 psm.deliver_name AS 'Store',
                 doc.description AS 'Doc Type',
                 s.description AS 'Status' ,
                 dh2.tripsheet_number AS 'Trip Sheet Number', 
                 t.name AS 'Driver', 
                 if(t.owner IS NULL, 'ZZZUnknown Owner', t.owner) AS 'Owner',
                 dh.cases AS 'Cases',
                 round(dh.exclusive_total,2) AS 'Value',
                 '' AS 'GRV Number',
                 '' AS 'Days Outstanding',
                 dh.source_document_number,                 
                 '" . mysqli_real_escape_string($this->dbConn->connection, $sDate) . "',
                 '" . mysqli_real_escape_string($this->dbConn->connection, $sDate) . "',
                 dm.principal_uid,
                 dm.uid, 
                 '1'                 
          FROM  temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . "  tt
          INNER join document_master dm  ON dm.principal_uid = tt.principal_uid
          INNER JOIN document_header dh ON dh.document_master_uid = dm.uid AND tt.`source_document_number` = dh.source_document_number
          INNER JOIN principal_store_master psm ON dh.principal_store_uid = psm.uid
          INNER JOIN document_type doc ON doc.uid = dm.document_type_uid
          INNER JOIN `status` s ON dh.document_status_uid = s.uid
          INNER JOIN principal p on p.uid = dm.principal_uid
          INNER JOIN depot d on d.uid = dm.depot_uid 
          LEFT JOIN  document_credit_source dcs ON dcs.credit_uid = dm.uid
          LEFT JOIN  document_header dh2 ON dh2.document_master_uid = dcs.invoice_uid
          LEFT JOIN  transporter t ON dh2.trip_transporter_uid = t.uid AND t.`status` ='A'
          WHERE dm.depot_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $whId) . ")
          AND   dh.invoice_date BETWEEN '" . mysqli_real_escape_string($this->dbConn->connection, $sDate) . "' 
                                AND     '" . mysqli_real_escape_string($this->dbConn->connection, $eDate) . "'
          AND   dm.document_type_uid IN (" . DT_CREDITNOTE . ")
          AND   dh.document_status_uid IN (" . DST_PROCESSED . ")
          AND   tt.`Status` = 'Dirty POD')";

          $this->errorTO = $this->dbConn->dbQuery($sql);
          
          $this->dbConn->dbQuery("commit");
             
          return;
  	
  }



  // **************************************************************************************************************************************************** 
  public function insertOwnerTotals($fileseqnumber, $ot) {
  	
  	    if($ot == 'O') {$gLine = "GROUP BY t.Owner"; } else { $gLine = "";}

        $sql = "INSERT INTO temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . " 
                                (temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".Owner,
                                 temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".Cases,
                                 temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".Value,
											           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".Spare,
											           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".Store,
											           temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . ".Sort)
                SELECT if( '" . mysqli_real_escape_string($this->dbConn->connection, $ot) . "' = 'O',t.Owner, ''), 
                       SUM(t.Cases),
                       round(SUM(t.Value),2), 
                       COUNT(t.doc_uid),
                       if( '" . mysqli_real_escape_string($this->dbConn->connection, $ot) . "' = 'O','Owner Total', 'Grand Total') ,
                       if( '" . mysqli_real_escape_string($this->dbConn->connection, $ot) . "' = 'O','2', '3')
                FROM temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . " t
                WHERE 1
                " . $gLine . " ;";

        $this->errorTO = $this->dbConn->dbQuery($sql);
          
        $this->dbConn->dbQuery("commit");
             
        return;
  }
  // **************************************************************************************************************************************************** 
  public function extractReport($fileseqnumber) {
  
        $sql = "SELECT `Warehouse`, 
                       `Date`,                     
                       `Document_No` as 'Document No',              
                       `Store`,                    
                       `Doc_Type` as 'Document Type',                 
                       `Status`,                   
                       `Trip_Sheet_Number` as 'Trip Sheet Number',        
                       `Driver`,                   
                        if(substr(`Owner`,1,3) = 'ZZZ',trim(substr(`Owner`,4,50)), `Owner` ) AS 'Owner',       
                       `Cases`,                    
                       `Value`,                    
                       `GRV_Number` as '',               
                       `Days`,                     
                       `source_document_number` as 'Source Document Number',
                       `Spare` as 'No of Documents',   
                       `Start_Date` as 'Start Date',               
                       `End_Date` as 'End Date',                 
                       `Principal_uid`,            
                       `doc_uid`,                  
                       `Sort`              
                FROM temp_owner_report_" . mysqli_real_escape_string($this->dbConn->connection, $fileseqnumber) . " t
                WHERE 1
                ORDER BY t.Owner DESC, sort ASC, t.`source_document_number`, t.`Doc_Type` DESC ;";

       $result = $this->dbConn->dbGetAll($sql);
       
       return $result;
  
  }
  // **************************************************************************************************************************************************** 
  public function getOwnerReportList($fileseqnumber) {
  	
  	   $sql = "SELECT *
               FROM .transporter_owner tow
               WHERE tow.send_mail = 'Y';";

  }

  // **************************************************************************************************************************************************** 
  public function getOwnerMailingList($depGroup) {
  	
  	   $sql = "SELECT tow.uid, 
  	                  tow.email, 
  	                  d.uid as 'depUid'
               FROM .transporter_owner tow
               LEFT JOIN .depot d ON d.depot_group = tow.depot_group
               WHERE tow.send_mail = 'N'
               AND   tow.depot_group = '" . mysqli_real_escape_string($this->dbConn->connection, $depGroup) . "'
               ORDER BY tow.email;";
  	
        $result = $this->dbConn->dbGetAll($sql);
       
        return $result;
  }
  // **************************************************************************************************************************************************** 
  public function getTransporterSales($sumLevel, $prinList, $statList, $startDate, $endDate, $whList, $reportBy) {

           if($sumLevel == 'T') {
           	     if($reportBy == 'N') {
                       $summaryList = "GROUP BY d.uid, t.name";
                       $repBy = 't.name';
           	     } else {
                       $summaryList = "GROUP BY d.uid, t.group";
                       $repBy = "t.`group`";
           	     }
  	       } else {
                if($reportBy == 'N') {
                       $repBy = 't.name';
           	     } else {
                       $repBy = "t.`group`";
           	     }    	
                 $summaryList = "GROUP BY d.uid";
 	         } 
 	          	
           $sql = "SELECT if(d.short_name IS NOT NULL,d.short_name,d.name) AS 'Warehouse',
                          ROUND(SUM(dd.extended_price),2) AS 'Exclusive Total',
                          ROUND(SUM(dd.vat_amount),2) AS 'VAT Total',
                          ROUND(SUM(dd.total),2) AS 'Invoice Total',
                          " . $repBy . " AS 'Transporter',
                          d.uid AS 'whUid'
                   FROM .document_master dm
                   INNER JOIN .document_header dh ON dm.uid = dh.document_master_uid
                   INNER JOIN .document_detail dd ON dm.uid = dd.document_master_uid
                   INNER JOIN .depot d ON d.uid = dm.depot_uid
                   LEFT JOIN .transporter t ON t.uid = dh.trip_transporter_uid
                   WHERE dm.principal_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $prinList) . ")
                   AND   dh.document_status_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $statList) . ")
                   AND   dh.invoice_date BETWEEN   '" . mysqli_real_escape_string($this->dbConn->connection, $startDate) . "' 
                                         AND       '" . mysqli_real_escape_string($this->dbConn->connection, $endDate) . "'
                   AND   dm.depot_uid IN (          " . mysqli_real_escape_string($this->dbConn->connection, $whList) . ")
                   AND   dm.depot_uid NOT IN (417)
                   " . $summaryList ." 
                   ORDER BY d.uid, t.name;";
                   
//                   echo $sql;

           $result = $this->dbConn->dbGetAll($sql);
       
           return $result;

  }

  // **************************************************************************************************************************************************** 
  public function getStockDemand($prinList, $whList) {
      
      $sql = "SELECT p.name AS 'Principal',
                     d.name AS 'Warehouse',
                     dm.document_number AS 'Document_Number',
                     dh.order_date,
                     datediff(curdate(),dh.order_date) as 'Days',
                     psm.deliver_name,
                     pp.product_code,
                     pp.product_description,
                     SUM(dd.ordered_qty) AS 'Qty',
                     s.closing AS 'ClosingStock',
                     round(dd.net_price,2) AS 'NetPrice'
              FROM document_master dm
              INNER JOIN document_header dh ON dh.document_master_uid = dm.uid
              INNER JOIN document_detail dd ON dd.document_master_uid = dm.uid
              INNER JOIN principal_store_master psm ON psm.uid = dh.principal_store_uid
              INNER JOIN principal p ON p.uid = dm.principal_uid
              INNER JOIN depot d ON d.uid = dm.depot_uid
              INNER JOIN principal_product pp ON  pp.uid = dd.product_uid
              LEFT  JOIN stock s ON s.principal_id = p.uid AND s.depot_id = d.uid AND s.principal_product_uid = pp.uid
              WHERE dm.principal_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $prinList) . ")
              AND   dh.document_status_uid IN (74,75,87)
              AND   dm.document_type_uid IN (1,6,13)
              AND   dm.depot_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $whList) . ")
              GROUP BY dd.document_master_uid
              ORDER BY dm.principal_uid, dm.depot_uid, pp.product_code, dd.document_master_uid";
      
//                   echo $sql;

              $result = $this->dbConn->dbGetAll($sql);
       
             return $result;      
  }

  // **************************************************************************************************************************************************** 

}    




?>     
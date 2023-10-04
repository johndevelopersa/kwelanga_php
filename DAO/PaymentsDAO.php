<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php'); 
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');   	

class PaymentsDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// *************************************************************************************************************************
  public function getPaymentTypes($principalId) {

    $sql = "select *
            from .payment_type pt
            where pt.reporting = 'Y'
            order by pt.`order`";
            
    $paymenttype = $this->dbConn->dbGetAll($sql);

    return $paymenttype;
  }


// *************************************************************************************************************************
  public function getPaymentCustomers($principalId) {

    $sql = "select distinct(psm.uid), 
                   psm.deliver_name as 'Customer',
                   psm.payment_by
            from   principal_store_master psm
            where  psm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId)     . "
            and    psm.payment_by = " . PAYMENT_BY_CUSTOMER . "
            and    psm.`status`= 'A'
            order by psm.deliver_name";
            
    $custlist = $this->dbConn->dbGetAll($sql);

    return $custlist;
  }
// *************************************************************************************************************************
  public function getPaymentGroups($principalId) {

    $sql = "select distinct(pcm.uid), 
                   pcm.description as 'Customer',
                   pcm.payment_by
            from   principal_chain_master pcm,
                   principal_store_master psm
            where  pcm.uid = psm.alt_principal_chain_uid
            and    pcm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId)     . "
            and    pcm.payment_by    = " . PAYMENT_BY_GROUP . "
            and    psm.payment_by    = " . PAYMENT_BY_GROUP . " 
            order by pcm.description";
            
    $grouplist = $this->dbConn->dbGetAll($sql);

    return $grouplist;
  }
// *************************************************************************************************************************
  public function getPaymentGroupChains($principalId) {

    $sql = "select distinct(pcm.uid), 
                   pcm.description as 'Customer',
                   pcm.payment_by
            from   principal_chain_master pcm
            where  pcm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId)     . "
            and    pcm.payment_by    = " . PAYMENT_BY_GROUP . "
            order by pcm.description";
            
    $grouplist = $this->dbConn->dbGetAll($sql);

    return $grouplist;
  }  
// *************************************************************************************************************************
  public function getUnmatchedInvoices($principalId, $storeId, $paymentBy) {

    if (mysqli_real_escape_string($this->dbConn->connection, $paymentBy) == PAYMENT_BY_CUSTOMER ) {
       $CustSelection = "and   dh.principal_store_uid in  (". mysqli_real_escape_string($this->dbConn->connection, $storeId) . ")";
    } else {
       $CustSelection = "and   psm.alt_principal_chain_uid in (". mysqli_real_escape_string($this->dbConn->connection, $storeId) . ")";
    }

    $sql = "Payment Amount does NOT equal Matched Amountselect dm.document_number, 
                   dh.invoice_date, 
                   dh.document_master_uid, 
                   pd.payment_amount, 
                   if( " . mysqli_real_escape_string($this->dbConn->connection, $paymentBy) . " = " .PAYMENT_BY_GROUP . ", pcm.description,psm.deliver_name) as 'Custname',
                   dh.payment_status, 
                   round(dh.invoice_total,2) as 'Invoice_Total',
                   if(sum(dh.payment_amount) is not null,round(dh.invoice_total,2) + sum(dh.payment_amount),round(dh.invoice_total,2)) as 'Matched', 
                   dh.principal_store_uid 
            from      document_master dm, 
                      document_header dh
            LEFT JOIN payment_detail pd on dh.document_master_uid = pd.document_master_uid
            LEFT JOIN payment_header ph on pd.payment_header_uid = ph.uid
            LEFT JOIN principal_store_master psm on dh.principal_store_uid = psm.uid
            LEFT JOIN principal_chain_master pcm on pcm.uid = psm.alt_principal_chain_uid
            where dm.uid = dh.document_master_uid
            and   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId)     . "
            and   dm.document_type_uid in (1,27,52)
            and   dh.document_status_uid in (73,76,77,78,52)
            and   psm.payment_by    =   " . mysqli_real_escape_string($this->dbConn->connection, $paymentBy)       . "
            "     . $CustSelection . "
            and   dh.payment_status in (" . UNMATCHED_INVOICE ."," . MATCHED_INVOICE_PARTIAL . ")
            and   dh.invoice_total > 0
            group by dm.uid"  ;

    $umIN = $this->dbConn->dbGetAll($sql);

    return $umIN;
  }	
// *************************************************************************************************************************
    public function PaymentValidation($Customer, $PaymentType, $PaymentAmount, $PaymentDate) {

      global $ROOT; global $PHPFOLDER;

      // get user. don't pass it because this is more secure.
      if (!isset($_SESSION)) session_start();
      
      $userId = $_SESSION['user_id'];
      $systemId = $_SESSION['system_id'];
      if ($Customer == 'Select Customer')  {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="No Customer Selected <BR><BR>";
          return $this->errorTO;      	
      }  		
 
      if ($PaymentDate < date_format(date_sub(date_create(CommonUtils::getUserDate()),date_interval_create_from_date_string("180 days")),"Y-m-d")) {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Payment Date Cannot be older than 180 Days<BR>";
          return $this->errorTO;
      }
      
      if ($PaymentDate > CommonUtils::getUserDate()) {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Payment Date Cannot be in the future<BR>";
          return $this->errorTO;
      }
      
       if ($PaymentType == 0) {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="How can you proceed if you have not seleted a payment type .. SLAP.. SLAP.<BR>";
          return $this->errorTO;
      }      

      $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description="Success - Move On";
      return $this->errorTO;
		}
// *************************************************************************************************************************
    public function SavePaymentValidation($PaymentAmount, $paysum) {

      global $ROOT; global $PHPFOLDER;

      // get user. don't pass it because this is more secure.
      if (!isset($_SESSION)) session_start();
      
      $userId = $_SESSION['user_id'];
      $systemId = $_SESSION['system_id'];

      if ($PaymentAmount == 0) {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Zero Payment Captured<BR>";
          return $this->errorTO;
      }

      $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description="Success - Move On";
      return $this->errorTO;
		}		
// *************************************************************************************************************************
  public function getPaymentDetailsMulti($prO) {

        $sql = "select dm.uid,
                   dm.principal_uid,
                   p.name as 'principal_name',
                   p.physical_add1 as 'prin_ph_add1',
                   p.physical_add2 as 'prin_ph_add2',
                   p.physical_add3 as 'prin_ph_add3',
                   p.postal_add1 as 'prin_add1',
                   p.postal_add2 as 'prin_add2',
                   p.postal_add4 as 'prin_add3',
                   p.email_add as 'p_email',
                   p.office_tel as 'office_tel',
                   dm.document_number,
                   dm.document_type_uid as 'document_type', 
                   dh.invoice_total,
                   dh.matched_amount,
                   dh.invoice_date, 
                   pt.pay_type,
                   ph.payment_number,
                   ph.amount, 
                   pd.payment_amount,
                   psm.uid as 'psm_uid', 
                   psm.deliver_name, 
                   psm.deliver_add1, 
                   psm.deliver_add2, 
                   psm.deliver_add3 
            from   payment_header ph, 
				           payment_detail pd,
				           document_master dm, 
                   document_header dh, 
                   principal_store_master psm,
                   payment_type pt,
                   principal p 
            where ph.uid = pd.payment_header_uid
            and   pd.document_master_uid = dm.uid 
            and   dm.uid = dh.document_master_uid 
            and dh.principal_store_uid = psm.uid 
            and dh.payment_type = pt.uid 
            and p.uid = dm.principal_uid
            and ph.principal_uid  = " .  trim(mysqli_real_escape_string($this->dbConn->connection, $prO->principalUid))   . "
            and ph.payment_number = " .  trim(mysqli_real_escape_string($this->dbConn->connection, $prO->postFindnumber)) . ";";
            
         $custPayment = $this->dbConn->dbGetAll($sql);

         return $custPayment;

  }
// *************************************************************************************************************************
    public function CreditFormValidation($Customer, $CreditReason, $CreditInvoice, $PaymentDate) {
    	
      global $ROOT; global $PHPFOLDER;

      // get user. don't pass it because this is more secure.
      if (!isset($_SESSION)) session_start();
      
      $userId = $_SESSION['user_id'];
      $systemId = $_SESSION['system_id'];
      if ($Customer == 'Select Customer')  {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="No Customer Selected <BR><BR>";
          return $this->errorTO;      	
      }  		
 
      if ($PaymentDate < date_format(date_sub(date_create(CommonUtils::getUserDate()),date_interval_create_from_date_string("90 days")),"Y-m-d")) {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Credit Note Date Cannot be older than 30 Days<BR>";
          return $this->errorTO;
      }
      
      if ($PaymentDate > CommonUtils::getUserDate()) {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Credit Note Date Cannot be in the future<BR>";
          return $this->errorTO;
      }
      
       if ($CreditReason == '') {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Please select a Reason for the Credit<BR>";
          return $this->errorTO;
      }      

       if ($CreditInvoice == '') {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Invoice Number cannot be Blank<BR>";
          return $this->errorTO;
      }      

      $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description="Success - Move On";
      return $this->errorTO;
		}
// *************************************************************************************************************************
  public function getInvoiceToCredit($principalId, $storeId, $paymentBy) {

    $sql = "select dm.document_number, 
                   dh.invoice_date, 
                   dh.document_master_uid, 
                   pd.payment_amount, 
                   if( " . mysqli_real_escape_string($this->dbConn->connection, $paymentBy) . " = " .PAYMENT_BY_GROUP . ", pcm.description,psm.deliver_name) as 'Custname',
                   dh.payment_status, 
                   round(dh.invoice_total,2) as 'Invoice_Total',
                   if(sum(pd.payment_amount) is not null,round(dh.invoice_total,2) + sum(pd.payment_amount),round(dh.invoice_total,2)) as 'Matched', 
                   dh.principal_store_uid 
            from      document_master dm, 
                      document_header dh
            LEFT JOIN payment_detail pd on dh.document_master_uid = pd.document_master_uid
            LEFT JOIN payment_header ph on pd.payment_header_uid = ph.uid
            LEFT JOIN principal_store_master psm on dh.principal_store_uid = psm.uid
            LEFT JOIN principal_chain_master pcm on pcm.uid = psm.alt_principal_chain_uid
            where dm.uid = dh.document_master_uid
            and   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId)     . "
            and   dm.document_type_uid in (1,27)
            and   dh.document_status_uid in (76,77,78)
            and   psm.payment_by    =   " . mysqli_real_escape_string($this->dbConn->connection, $paymentBy)       . "
            "     . $CustSelection . "
            and   dh.payment_status in (" . UNMATCHED_INVOICE ."," . MATCHED_INVOICE_PARTIAL . ")
            and   dh.invoice_total > 0
            group by dm.uid"  ;

    $umIN = $this->dbConn->dbGetAll($sql);

    return $umIN;
  }	
// *************************************************************************************************************************
  public function getUnAllocatedInvoiceAmts($principalId, $storeId) {
  	
  	  if(mysqli_real_escape_string($this->dbConn->connection, $principalId == 207)) {$excludeOld = "AND dh.invoice_date > '2019-01-01'";
  	  	                                                                         } else { $excludeOld = " ";}
  
      $sql = "SELECT dm.document_number, 
                     dh.invoice_date, 
                     dh.document_master_uid,
                     psm.deliver_name as 'Custname',
                     round(dh.invoice_total,2) AS 'Invoice_Total',
                     (SELECT sum(a.allocated_amount) 
                      FROM .allocations a
                      WHERE a.allocation_to_number = dm.document_number
                      GROUP BY a.allocation_to_number) AS 'Allocation_Amt',
                     if((SELECT DISTINCT(a.allocation_to_number) 
                         FROM .allocations a
                         WHERE a.allocation_to_number = dm.document_number
                         GROUP BY a.allocation_to_number) IS NULL,'N','Y') AS 'Allocated'		  
              FROM       document_master dm
              INNER JOIN document_header dh
              LEFT JOIN  principal_store_master psm on dh.principal_store_uid = psm.uid
              WHERE dm.uid = dh.document_master_uid
              AND   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
              AND   dm.document_type_uid in (1,27,52) "
              . $excludeOld . " 
              AND   dh.document_status_uid in (73,76,77,78,52)
              AND   dh.principal_store_uid = " . mysqli_real_escape_string($this->dbConn->connection, $storeId) . "
              GROUP BY dm.document_number";
      
      $uAIAmt = $this->dbConn->dbGetAll($sql);
       
      $umIN = Array();
      
      foreach($uAIAmt as $row) {
      	if($row['Allocated'] == 'N' ||  $row['Inv Total'] + $row['Allocation Amt']  > 0) {
      	array_push($umIN, $row);	
      	}   	
      }

      return $umIN;
  }

// *************************************************************************************************************************

}

?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."TO/PaymentsTO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class CustomerBalancesDAO {
	
    private $dbConn;

    function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
    }
// *************************************************************************************************************************
    public function GetDebtorActivePrincipals() {
       
       $psql = "select p.uid as 'PrincipalId',
                       p.name as 'Principal',
                       p.debtor_active,
                       p.financial_year_start
                 from .principal p
                 where p.debtor_active = 'Y';";
                 
       $prinarr = $this->dbConn->dbGetAll($psql);
       return $prinarr;
    }

// *************************************************************************************************************************
    public function GetAgingDates($principalId, $today) {
//   Get Age analysis dates
        
        
        $dsql =   "select a.principal_uid,
                          a.year,
                          a.period,
                          a.start_date as 'Current Start',
                          a.end_date   as 'Current End',
                          a.30_start   as '30 Start',
                          a.30_end     as '30 End',
                          a.60_start   as '60 Start',
                          a.60_end     as '60 End',
                          a.90_start   as '90 Start',
                          a.90_end     as '90 End',
                          a.91_start   as '91 Start',
                          a.91_end     as '91 End'
                   from .principal_period a
                   where a.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                   and   '". mysqli_real_escape_string($this->dbConn->connection, $today) . "' between a.start_date and a.end_date";
     

echo $dsql;

                  $periodarr = $this->dbConn->dbGetAll($dsql);
                  return $periodarr;
    } 
// *************************************************************************************************************************
    public function GroupIndividualStatus($principalId, $paymentBy, $oneCustomer, $updateBatch) {
    	
    	if($oneCustomer == '') {
         $oneCustomerParm = '';
    	} else {
         $oneCustomerParm = "and psm.uid in (". mysqli_real_escape_string($this->dbConn->connection, $oneCustomer) .")";
    	} 	
    	if($updateBatch == '') {
         $updateBatchParm = "and psm.balance_update_batch is null";
    	} else {
         $updateBatchParm = "and psm.balance_update_batch in (". mysqli_real_escape_string($this->dbConn->connection, $updateBatch) .")";
    	} 	
         $cusql = "select psm.uid,
                          psm.payment_by,
                          psm.deliver_name as 'bname'  
                   from   principal_store_master psm
                   where  psm.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                   and    psm.payment_by    = ". mysqli_real_escape_string($this->dbConn->connection, $paymentBy) . " " .
                   $updateBatchParm . " " .
                   $oneCustomerParm . " ;";
                  
         $indivGroupPaymentCust = $this->dbConn->dbGetAll($cusql);

         return $indivGroupPaymentCust;         
    }	
// *************************************************************************************************************************
    public function GroupChainStatus($principalId, $updateBatch) {
        $cusql = "select pcm.uid,
                         pcm.payment_by,
                         pcm.description as 'bname' 
                  from   principal_chain_master pcm
                  where  pcm.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                  and    pcm.payment_by = " . PAYMENT_BY_GROUP . "
                  and    pcm.balance_update_batch = ". mysqli_real_escape_string($this->dbConn->connection, $updateBatch) . " ;";
        
        $gcstatusarr = $this->dbConn->dbGetAll($cusql);

        return $gcstatusarr;         
    }	    
// *************************************************************************************************************************
    public function FetchPeriodBalance($principalId,
                                       $start_date,
                                       $end_date,
                                       $chainStoreUid,
                                       $paymentBy) {

           if (mysqli_real_escape_string($this->dbConn->connection, $paymentBy) == PAYMENT_BY_CUSTOMER ) {
                $CustSelection = "and   dh.principal_store_uid in  (". mysqli_real_escape_string($this->dbConn->connection, $chainStoreUid) . ")";
                $selectLine    = "dh.invoice_total as 'invoice_total'";
           } else {
                $CustSelection = "and   psm.alt_principal_chain_uid in (". mysqli_real_escape_string($this->dbConn->connection, $chainStoreUid) . ") 
                                  group by psm.alt_principal_chain_uid ";
                $selectLine    = "sum(dh.invoice_total) as 'invoice_total'";
           }
           $DateRange = "and   dh.invoice_date between '". mysqli_real_escape_string($this->dbConn->connection, $start_date) . "' and '". mysqli_real_escape_string($this->dbConn->connection, $end_date) . "'";

           $ssql = "select  " . $selectLine ." 
                    from      document_master dm,
                              document_header dh,
                              principal_store_master psm,
                              principal_chain_master pcm
                    where dm.uid = dh.document_master_uid
                    and   dh.principal_store_uid = psm.uid
                    and   if(psm.alt_principal_chain_uid is null,pcm.uid = psm.principal_chain_uid, pcm.uid = psm.alt_principal_chain_uid)
                    and   dm.document_type_uid in (1,6,13,27,33,52)
                    and   dh.document_status_uid in (73,76,77,78,81)
                    and   dm.principal_uid  =   " . mysqli_real_escape_string($this->dbConn->connection, $principalId)     . "
                    and   dh.payment_status not in (" . IGNORE_INVOICE  . ")
                    " . $DateRange . " 
                    " . $CustSelection . ";";
                                    
//                    file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/sqlC.txt', $ssql, FILE_APPEND); 

           $salesarr = $this->dbConn->dbGetAll($ssql);
           $ageSales = 0;
              
           foreach($salesarr as $salesarrrow)   {
           	   $ageSales = $ageSales + $salesarrrow['invoice_total'];

           }
           return $ageSales;
    }
// *************************************************************************************************************************
    public function FetchPeriodCredits($principalId,
                                       $current_start,
                                       $current_end,
                                       $chainStoreUid,
                                       $paymentBy)            {

           if (mysqli_real_escape_string($this->dbConn->connection, $paymentBy) == PAYMENT_BY_CUSTOMER ) {
                $CustSelection = "and   dh.principal_store_uid in  (". mysqli_real_escape_string($this->dbConn->connection, $chainStoreUid) . ")";
                $selectLine    = "round(sum(dh.invoice_total),0) as 'invoice_total'";
           } else {
                $CustSelection = "and   psm.alt_principal_chain_uid in (". mysqli_real_escape_string($this->dbConn->connection, $chainStoreUid) . ") 
                                  group by psm.alt_principal_chain_uid ";
                $selectLine    = "sum(dh.invoice_total) as 'invoice_total'";
           }
          $CurrentDateRange = "and   dh.invoice_date between '". mysqli_real_escape_string($this->dbConn->connection, $current_start) . "' 
                                                and     '". mysqli_real_escape_string($this->dbConn->connection, $current_end) . "'";

          $ssql = "select " .$selectLine. ",
                             MAX(dh.invoice_date) as 'invoice_date'
                   from   document_master dm,      
                          document_header dh,  
                          principal_store_master psm,
                          principal_chain_master pcm
                   where dm.uid = dh.document_master_uid
                   and   dh.principal_store_uid = psm.uid
                   and   if(psm.alt_principal_chain_uid is null,pcm.uid = psm.principal_chain_uid, pcm.uid = psm.alt_principal_chain_uid)
                   and   dm.document_type_uid in (" . DT_CREDITNOTE . "," . DT_MCREDIT_OTHER . ", " . DT_MCREDIT_VALUE . ", " . DT_MCREDIT_PRICING . ")
                   and   dh.document_status_uid in (" . DST_PROCESSED . ")
                   and   psm.payment_by    =   " . mysqli_real_escape_string($this->dbConn->connection, $paymentBy)       . "
                   and   dm.principal_uid  =   " . mysqli_real_escape_string($this->dbConn->connection, $principalId)     . "
                   and   dh.payment_status not in (" . IGNORE_INVOICE  . ")
                    " . $CurrentDateRange . " 
                    " . $CustSelection . ";";

     //          file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/sqlCC.txt', $ssql, FILE_APPEND); 
                    
                   $salesarr = $this->dbConn->dbGetAll($ssql);
                   
                   return $salesarr;
                   
    }   
// *************************************************************************************************************************
    public function FetchPeriodPayments($principalId,
                                        $current_start,
                                        $current_end,
                                        $chainStoreUid,
                                        $paymentBy)            {

           if (mysqli_real_escape_string($this->dbConn->connection, $paymentBy) == PAYMENT_BY_CUSTOMER ) {
                $CustSelection = "and   dh.principal_store_uid in  (". mysqli_real_escape_string($this->dbConn->connection, $chainStoreUid) . ")";
                $selectLine    = "round(sum(ph.amount),2) as 'payment_amount' ";
                $group_by = "";

           } else {
                $CustSelection = "and dh.principal_store_uid in (". mysqli_real_escape_string($this->dbConn->connection, $chainStoreUid) . ")";
                $selectLine    = "sum(round(pd.payment_amount,2)) as 'payment_amount' ";
                $group_by = "group by dh.principal_store_uid" ;

           }
          
          $CurrentDateRange = "and   ph.payment_date between '". mysqli_real_escape_string($this->dbConn->connection, $current_start) . "' 
                                                     and     '". mysqli_real_escape_string($this->dbConn->connection, $current_end) . "'";
                                                     
          $ssql = "select " . $selectLine . ",
                            MAX(ph.payment_date) AS 'invoice_date'
                   from payment_header ph
                   where ph.principal_uid        = " . mysqli_real_escape_string($this->dbConn->connection, $principalId)   . "
                   and   ph.principal_store_uid  = " . mysqli_real_escape_string($this->dbConn->connection, $chainStoreUid) . "
                   " . $CurrentDateRange . "
                   and   ph.status = 'A' 
                   " . $group_by . ";";                      

          $paymentarr = $this->dbConn->dbGetAll($ssql);
          
          return $paymentarr;
    }

// *************************************************************************************************************************
    public function CheckForExistingCustomerBalances($principalId,
                                                     $monthEndDate,
                                                     $storeUid,
                                                     $paymentBy)  {

         $cbsql = "select cb.uid
                   from .customer_balance cb
                   where cb.principal_uid  = " . mysqli_real_escape_string($this->dbConn->connection, $principalId)       . "
                   and   cb.month_end_date = '". mysqli_real_escape_string($this->dbConn->connection, $monthEndDate)      . "'
                   and   cb.payment_by     = '". mysqli_real_escape_string($this->dbConn->connection, $paymentBy)         . "'
                   and   cb.customer_uid   = '". mysqli_real_escape_string($this->dbConn->connection, $storeUid)          . "';";
                   
                   echo "<pre>";
                   echo $cbsql;
                   echo "<br>";
                   
         $existarr = $this->dbConn->dbGetAll($cbsql);

        return $existarr;
    }
    
// *************************************************************************************************************************
    public function InsertIntoCustomerBalance($principalId,
                                              $year,
                                              $period,
                                              $med,
                                              $paymentBy,
                                              $storeUid,
                                              $currentAmount,
                                              $day30Amount,
                                              $day60Amount,
                                              $day90Amount,
                                              $day91Amount,
                                              $bname)  {
                                              	
    $totalDue = mysqli_real_escape_string($this->dbConn->connection, $currentAmount) +
                mysqli_real_escape_string($this->dbConn->connection, $day30Amount)   +
                mysqli_real_escape_string($this->dbConn->connection, $day60Amount)   +
                mysqli_real_escape_string($this->dbConn->connection, $day90Amount)   +
                mysqli_real_escape_string($this->dbConn->connection, $day91Amount);
                
    $cbsql = "INSERT INTO `customer_balance` (`principal_uid`, 
                                              `year`, 
                                              `period`,
                                              `month_end_date`, 
                                              `payment_by`, 
                                              `customer_uid`,
                                              `total_due`, 
                                              `current`, 
                                              `30days`, 
                                              `60days`, 
                                              `90days`, 
                                              `120days`,
                                              `last_update`) 
              VALUES (". mysqli_real_escape_string($this->dbConn->connection, $principalId)       . ",
                      ". mysqli_real_escape_string($this->dbConn->connection, substr($year,2,2))  . ",
                      ". mysqli_real_escape_string($this->dbConn->connection, $period)            . ",
                     '". mysqli_real_escape_string($this->dbConn->connection, $med)               . "',
                      ". mysqli_real_escape_string($this->dbConn->connection, $paymentBy)         . ",
                      ". mysqli_real_escape_string($this->dbConn->connection, $storeUid )         . ",
                      ". mysqli_real_escape_string($this->dbConn->connection, $totalDue )         . ",
                      ". mysqli_real_escape_string($this->dbConn->connection, $currentAmount)     . ", 
                      ". mysqli_real_escape_string($this->dbConn->connection, $day30Amount)       . ", 
                      ". mysqli_real_escape_string($this->dbConn->connection, $day60Amount)       . ",
                      ". mysqli_real_escape_string($this->dbConn->connection, $day90Amount)       . ",
                      ". mysqli_real_escape_string($this->dbConn->connection, $day91Amount)       . ",
                      now());";

                 $this->errorTO = $this->dbConn->processPosting($cbsql,"");
      
                 if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) { 
                 	
//                 	  echo "<br>";
//                 	  echo $cbsql;
//                    echo "<br>";
                    $this->errorTO->description="Customer Balance Insert Failed : ".$this->errorTO->description;
                    return $this->errorTO;         	                  
                 } 
                 $this->dbConn->dbQuery("commit");
        
        $this->errorTO->description= $bname . " Balances Inserted ".$this->errorTO->description;
        return $this->errorTO;
    }
    
// *************************************************************************************************************************
    public function UpdateCustomerBalance($cbUid,
                                          $currentAmount,
                                          $day30Amount,
                                          $day60Amount,
                                          $day90Amount,
                                          $day91Amount,
                                          $bname) {

    $totalDue = mysqli_real_escape_string($this->dbConn->connection, $currentAmount) +
                mysqli_real_escape_string($this->dbConn->connection, $day30Amount)   +
                mysqli_real_escape_string($this->dbConn->connection, $day60Amount)   +
                mysqli_real_escape_string($this->dbConn->connection, $day90Amount)   +
                mysqli_real_escape_string($this->dbConn->connection, $day91Amount);
                
    $cbsql = "UPDATE `customer_balance` set `total_due`    = " . $totalDue      . ",
                                            `current`      = " . $currentAmount . ",
                                            `30days`       = " . $day30Amount   . ",
                                            `60days`       = " . $day60Amount   . ",
                                            `90days`       = " . $day90Amount   . ",
                                            `120days`      = " . $day91Amount   . ",
                                            `last_update` = now()
                                             
              WHERE customer_balance.uid = ". mysqli_real_escape_string($this->dbConn->connection, $cbUid) . ";";
 
                 $this->errorTO = $this->dbConn->processPosting($cbsql,"");
      
                 if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                    $this->errorTO->description="Customer Balance Update Failed : ".$this->errorTO->description;
                    return $this->errorTO;         	                  
                 } 
                 $this->dbConn->dbQuery("commit");
        
        $this->errorTO->description= $bname . " Balances Updated ".$this->errorTO->description;
        return $this->errorTO;
    }
    
// *************************************************************************************************************************
    public function GetUnmatchedCreditNotes($principalId) {
    	
         $unsql = "select 'Credit Notes',
                           dm.uid as 'creditNoteUid',                  
                           dm.document_number,       
                           dm.document_type_uid,     
                           dh.source_document_number,
                           dh.payment_status ,         
                           dh.invoice_total as 'updateTotal',          
                            'Invoices',
                            (select dm2.uid
                             from  document_master dm2, 
                                   document_header dh2
                             where dm2.uid = dh2.document_master_uid
                             and   dm2.principal_uid =  ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                             and   dm2.document_type_uid in (1,27,6,13)
                             and   dh.source_document_number = dm2.document_number) as 'invoiceUid',
                             (select dh2.payment_status
                             from  document_master dm2, 
                                   document_header dh2
                             where dm2.uid = dh2.document_master_uid
                             and   dm2.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                             and   dm2.document_type_uid in (1,27,6,13)
                             and   dh.source_document_number = dm2.document_number) as 'paymentStatus',
                             (select dh2.matched_amount
                             from  document_master dm2,
                                   document_header dh2
                             where dm2.uid = dh2.document_master_uid
                             and   dm2.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                             and   dm2.document_type_uid in (" . DT_ORDINV . ", " . DT_QUOTATION . "," . DT_DELIVERYNOTE . "  ," . DT_ORDINV_ZERO_PRICE . " )
                             and   dh.source_document_number = dm2.document_number) as 'matchedAmount',
                             (select dh2.payment_amount
                             from  document_master dm2,
                                   document_header dh2
                             where dm2.uid = dh2.document_master_uid
                             and   dm2.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                             and   dm2.document_type_uid in (" . DT_ORDINV . ", " . DT_QUOTATION . "," . DT_DELIVERYNOTE . "  ," . DT_ORDINV_ZERO_PRICE . " )
                             and   dh.source_document_number = dm2.document_number) as 'paymentAmount' ,        
                             (select dm2.uid
                             from   document_master dm2,
                                    document_header dh2
                             where  dm2.uid = dh2.document_master_uid
                             and    dm2.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                             and    dm2.document_type_uid in (" . DT_ORDINV . ", " . DT_QUOTATION . "," . DT_DELIVERYNOTE . "  ," . DT_ORDINV_ZERO_PRICE . " )
                             and    dh.source_document_number = dm2.document_number) as 'sourceUid',
                            (select dh2.document_status_uid
                             from   document_master dm2,
                                    document_header dh2
                             where  dm2.uid = dh2.document_master_uid
                             and    dm2.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                             and    dm2.document_type_uid in (" . DT_ORDINV . ", " . DT_QUOTATION . "," . DT_DELIVERYNOTE . "  ," . DT_ORDINV_ZERO_PRICE . " )
                             and    dh.source_document_number = dm2.document_number) as 'SourceDocumentStatus'
                  from  document_master dm, .document_header dh
                  where dm.uid = dh.document_master_uid
                  and   dm.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                  and   dm.document_type_uid in (" . DT_CREDITNOTE . "," . DT_MCREDIT_OTHER . ", " . DT_MCREDIT_VALUE . ", " . DT_MCREDIT_PRICING . ")
                  and   dh.payment_status not in (" . MATCHED_CREDIT. ", " . IGNORE_INVOICE . ");" ;
         
         $unmatchedInvArr = $this->dbConn->dbGetAll($unsql);
         return $unmatchedInvArr;      
                  
    }
    
// *************************************************************************************************************************
    public function MatchPaymentsToInvoices($principalId) {

         $upunsql = "update          payment_detail pd
                     left join       document_header dh  on pd.document_master_uid = dh.document_master_uid,      
                     payment_header  ph  set  dh.payment_status = if(round(dh.invoice_total,2) + round(pd.payment_amount) = 0,1,2),
		                                 dh.payment_amount = pd.payment_amount,
		                                 dh.matched_amount = round(dh.invoice_total,2) + round(pd.payment_amount,2),
		                                 dh.payment_number = ph.payment_number,
		                                 dh.`payment_type` = ph.type_uid,
		                                 pd.payment_match  = (" . MATCHED_CREDIT. ")
                     where pd.payment_header_uid = ph.uid
                     and   ph.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                     and   pd.payment_match is null;";

         $this->errorTO = $this->dbConn->processPosting($upunsql,"");
         
         if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
             $this->errorTO->description="Payment Update Failed : ".$this->errorTO->description;
             return $this->errorTO;         	                  
         } 
         $this->dbConn->dbQuery("commit");
           
         $this->errorTO->description= "Payment Updated ".$this->errorTO->description;
         return $this->errorTO;
    }
// *************************************************************************************************************************

    public function UpdateUnmatchedSourceInvoices($updateTotal,$InvoiceUid, $creditUid) {
    	
        $upunsql = "update  document_header dh set dh.matched_amount      =  (dh.invoice_total - dh.matched_amount) + ". mysqli_real_escape_string($this->dbConn->connection, $updateTotal). ",
                                                   dh.payment_status      =  if((dh.invoice_total - dh.matched_amount) + ". mysqli_real_escape_string($this->dbConn->connection, $updateTotal) ." = 0,1,2) , 
                                                   dh.source_document_uid =  ". mysqli_real_escape_string($this->dbConn->connection, $creditUid) ."
                     where dh.document_master_uid in ( ". mysqli_real_escape_string($this->dbConn->connection, $InvoiceUid) .");";

         $this->errorTO = $this->dbConn->processPosting($upunsql,"");
         
         if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
             $this->errorTO->description="Source Document Update Failed : ".$this->errorTO->description;
             return $this->errorTO;         	                  
         } 
         $this->dbConn->dbQuery("commit");
           
         $this->errorTO->description= $InvoiceUid . "Source Document Updated ".$this->errorTO->description;
         return $this->errorTO;
    }
// *************************************************************************************************************************
    public function UpdateUnmatchedSourceCredits($InvoiceUid, $creditUid) {
    	
         $upunsql = "update  document_header dh set dh.payment_status = ". MATCHED_CREDIT ."  , 
                                                    dh.source_document_uid =  ". mysqli_real_escape_string($this->dbConn->connection, $InvoiceUid) ."
                   where dh.document_master_uid in ( ". mysqli_real_escape_string($this->dbConn->connection, $creditUid) .");";

         $this->errorTO = $this->dbConn->processPosting($upunsql,"");
         
         if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
             $this->errorTO->description="Source Credit Document Update Failed : ".$this->errorTO->description;
             return $this->errorTO;         	                  
         } 
         $this->dbConn->dbQuery("commit");
           
         $this->errorTO->description= $creditUid . "Source Credit Document Updated ".$this->errorTO->description;
         return $this->errorTO;
    }

// *************************************************************************************************************************
    public function MatchUnmatchedManualCreditsToInvoices($principalId) {
    	
         $upunsql = "update document_master dm,
                            document_header dh,
                            document_master inv, 
                            document_header inh set dh.source_document_number = inv.document_number,
                            inh.source_document_uid    = dm.uid,
                            inh.source_document_number = inv.document_number,
                            dh.payment_status = 3,
                            inh.payment_status = if((inh.invoice_total - inh.matched_amount) + dh.invoice_total = 0,1,2),
                            inh.matched_amount = (inh.invoice_total - inh.matched_amount)  + dh.invoice_total
                     where dm.uid = dh.document_master_uid
                     and   dm.principal_uid = inv.principal_uid
                     and   lpad(dh.customer_order_number,8,'00') = inv.document_number
                     and   inv.uid = inh.document_master_uid
                     and   dm.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                     and   dm.document_type_uid in (" . DT_MCREDIT_OTHER . ", " . DT_MCREDIT_VALUE . ", " . DT_MCREDIT_PRICING . ")
                     and   dh.payment_status <> = ". MATCHED_CREDIT . " ;";

         $this->errorTO = $this->dbConn->processPosting($upunsql,"");
         
         if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
             $this->errorTO->description="Manual Credit match Failed : ".$this->errorTO->description;
             return $this->errorTO;         	                  
         } 
         $this->dbConn->dbQuery("commit");
           
         $this->errorTO->description= $creditUid . "Smanual Credit Match Successful ".$this->errorTO->description;
         return $this->errorTO;
    }

// *************************************************************************************************************************

} 

?>
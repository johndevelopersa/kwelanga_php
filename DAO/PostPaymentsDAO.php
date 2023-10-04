<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."TO/PaymentsTO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class PostPaymentsDAO {
	
    private $dbConn;

    function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
    }
// *************************************************************************************************************************
    public function postPaymentValidation($PaymentsTO, $cdate) {
    	
    	global $ROOT; global $PHPFOLDER;
    	    	
    	$errorTO = new ErrorTO;

        $dsql = "select *
                 from .payment_header ph
                 where ph.principal_store_uid = '" . $PaymentsTO->CustomerUid    . "'
                 and   ph.type_uid            = '" . $PaymentsTO->PaymentType    . "'
                 and   ph.amount              = '" . $PaymentsTO->PaymentAmount  . "'
                 and   ph.capture_date        = '" . $cdate . "';";    
 
        $utresult = $this->dbConn->dbGetAll($dsql);

      if (count($utresult) <> 0) {
          $errorTO->type=FLAG_ERRORTO_ERROR;
          $errorTO->description="This Payment for this Customer for Today already captured <BR>";
          return $errorTO;
      } else {
          $errorTO->type=FLAG_ERRORTO_SUCCESS;
          $errorTO->description="Success - Move On";
          return $errorTO;
      }
    }

// *************************************************************************************************************************
    public function SavePaymentRecordsHeader($PaymentsTO)  {
    	
    	global $ROOT; global $PHPFOLDER;
    	
      $cdate=date_format(date_create(CommonUtils::getUserDate()), 'Y-m-d');

      $this->errorTO = $this->postPaymentValidation($PaymentsTO, $cdate);
 
      if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) { 

          // Get payment sequence No
          $sequenceDAO = new SequenceDAO($this->dbConn);
          $sequenceTO = new SequenceTO;
          $errorTO = new ErrorTO;
          $sequenceTO->sequenceKey=LITERAL_SEQ_PAYMENT;
          $sequenceTO->principalUId = $PaymentsTO->PrincipalUid;
          $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
          
          if ($result->type!=FLAG_ERRORTO_SUCCESS) {
             return $result;
          }
          
//          echo $seqVal;          	
         // Insert into payment header and detail
          $isql = "INSERT INTO `payment_header` (`principal_uid`, 
                                                 `payment_number`, 
                                                 `type_uid`, 
                                                 `payment_date`, 
                                                 `capture_date`, 
                                                 `payment_by`,
                                                 `principal_store_uid`, 
                                                 `amount`, 
                                                 `captured_by`) 
                   VALUES ( '" . $PaymentsTO->PrincipalUid                   . "',
                            '" . $seqVal                                     . "',                        
                            '" . $PaymentsTO->PaymentType                    . "',
                            '" . $PaymentsTO->PaymentDate                    . "',
                            '" . $cdate                                      . "',
                            '" . substr($PaymentsTO->CustomerUid,0,1)        . "', 
                            '" . trim(substr($PaymentsTO->CustomerUid,1,10)) . "', 
                            0-" . $PaymentsTO->PaymentAmount                 . ", 
                            '" . $PaymentsTO->CapturedBy                    . "')";

            $this->errorTO = $this->dbConn->processPosting($isql,"");

            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {   
                $this->errorTO->description="Payment Header Update Failed failed : ". $isql .$this->errorTO->description;
                return $this->errorTO;         	                  
            } else {
            	  $pdUId = $this->dbConn->dbGetLastInsertId();
            	  $this->errorTO->description=$pdUId;
            	  $this->errorTO->identifier=$seqVal;
            	  return $this->errorTO; 
            }
      } else {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Payment for this Customer for Today already captured <BR>";
          return $this->errorTO;
      }
    }
// *************************************************************************************************************************
    public function SavePaymentRecordsDetail($PaymentsTO, $phUId, $PayCount, $payseq)  {
    	
    	global $ROOT; global $PHPFOLDER;  
               
                  $idsql = "INSERT INTO `payment_detail` (`payment_header_uid`, 
                                                          `document_master_uid`, 
                                                          `payment_amount`,
                                                           payment_count) 
                            VALUES ( " . mysqli_real_escape_string($this->dbConn->connection,$phUId)      . " , 
                                    '" . $PaymentsTO->InvoiceList                                         . "',
                                    0- " . str_replace(' ', '',$PaymentsTO->InvoicePaymentAmount)         . " ,
                                    "    . mysqli_real_escape_string($this->dbConn->connection,$PayCount) . ")";

                                   
                 $this->errorTO = $this->dbConn->processPosting($idsql,"");
      
                 if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                    $this->errorTO->description="Payment Detail Update Failed failed : ".$this->errorTO->description;
                    return $this->errorTO;         	                  
                 } 
                 $this->dbConn->dbQuery("commit");
                 
                 $idsql = "update .document_header dh set dh.payment_amount = if(dh.payment_amount in (0,NULL) ,0-" . str_replace(' ', '',$PaymentsTO->InvoicePaymentAmount) .",dh.payment_amount-" . str_replace(' ', '',$PaymentsTO->InvoicePaymentAmount) .") ,
                                                          dh.payment_status = if(dh.payment_amount+round(dh.invoice_total,2)=0,". MATCHED_INVOICE_FULL ."," . MATCHED_INVOICE_PARTIAL. "), 
                                                          dh.payment_type = " . $PaymentsTO->PaymentType   .",  
                                                          dh.matched_amount = dh.payment_amount+round(dh.invoice_total,2) ,
                                                          dh.payment_number =  " . mysqli_real_escape_string($this->dbConn->connection,$payseq)  . "
                           where dh.document_master_uid = " .$PaymentsTO->InvoiceList       .";";

                $this->errorTO = $this->dbConn->processPosting($idsql,"");

                 if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                     $this->errorTO->description="Payment Header Update Failed failed : ". $isql .$this->errorTO->description;
                     return $this->errorTO;         	                  
                 } else {
                     $this->dbConn->dbQuery("commit");
                     $this->errorTO->description="Payment Successfully Saved";
                     return $this->errorTO;                
                 }
   }
    
// *************************************************************************************************************************
    public function AutoMatchCredits($principalUid)  {
    	
          // Get Debtors active Principals
          
          $psql = "select p.uid as 'PrinUid',
                          p.name as 'Principal'
                   from   principal p
                   where  p.debtor_active = 'Y'
                   and    p.uid = " . mysqli_real_escape_string($this->dbConn->connection,$principalUid)  . " ;";
                   
                   $prinarr = $this->dbConn->dbGetAll($psql);
          
           foreach($prinarr as $prinrow) {
 
                  // Get Un Matched Credits by Principal  //

                  $credsql = "select dm.uid as 'docuid',
                                     dm.principal_uid,
                                     dm.document_type_uid,  
                                     dh.source_document_number,
                                     dh.customer_order_number, 
                                     dh.invoice_total
                              from  document_master dm, 
                                    document_header dh
                              where dm.uid = dh.document_master_uid
                              and   dm.principal_uid = " . $prinrow['PrinUid'] .   "
                              and   dh.invoice_date > '2013-01-01'
                              and   dm.document_type_uid in (" . DT_CREDITNOTE . "," . DT_MCREDIT_VALUE . "," .  DT_MCREDIT_OTHER . ")
                              and   dh.payment_status in (" . UNMATCHED_INVOICE . "," . MATCHED_INVOICE_PARTIAL . ")
                              order by dm.principal_uid, dh.source_document_number , dm.document_type_uid ;";

                  $credarr = $this->dbConn->dbGetAll($credsql);
                  
                  if(count($credarr) > 0) {
                  
                     foreach($credarr as $credrow) {
                     	   // Get the source invoice / document UID
                     	   
//                     	   print_r($credrow);
                     	   $somethingToMatch = 'F';
                     	   
                     	   if($credrow['document_type_uid'] == DT_CREDITNOTE) {
                     	   	   $matrow = "and   dh.source_document_number like '%". $credrow['source_document_number'] ."%'" ;
                     	   	   $somethingToMatch = 'T';
                     	   } else {
                     	   	   if(trim($credrow['customer_order_number'])<> '') {
                     	   	   	   $matrow = "and   dm.document_number like '%". trim($credrow['customer_order_number']) ."%'"  ; 
                     	   	       $somethingToMatch = 'T';
                     	   	   }               	   	
                     	   }
                     	   if($somethingToMatch == 'T')  {
                     	   	     $csql = "select dm.uid as 'InvoiceUid', 
                  	                           dh.source_document_number,
                  	                           dh.matched_amount,
                  	                           dh.invoice_total,
                  	                           " . $credrow['docuid'] . " as 'CreditUid',
                  	                           " . $credrow['invoice_total'] . " as 'CreditNoteValue'
                                        from document_master dm, document_header dh
                                        where dm.uid = dh.document_master_uid
                                        and   dm.principal_uid = " . $credrow['principal_uid'] . "
                                        " . $matrow . " 
                                        and   dm.document_type_uid in ( " . DT_ORDINV . "," . DT_QUOTATION .  ");";
                                        
                                        // echo $csql;
                  	  
                  	          $srcarr = $this->dbConn->dbGetAll($csql);
                  	          
                  	          if(count($srcarr) > 0) {
                  	               $this->errorTO = $this->UpdateAutoMatchInvoice($srcarr[0]['CreditNoteValue'], $srcarr[0]['InvoiceUid'], $srcarr[0]['CreditUid']);
                                   $this->errorTO = $this->UpdateAutoMatchCredit($srcarr[0]['CreditUid'], $srcarr[0]['InvoiceUid']); 
                     	   	    }
                     	   }
                  	 }
                  	 
                  	 $this->dbConn->dbQuery("commit");
                     $this->errorTO->description="Credits Successfully Matched";
                     $this->errorTO->type="S";
                     return $this->errorTO; 
                        
                  }  else {
                  	 $this->errorTO->description="No Credits for " . $prinrow['PrinUid'] ;
                  	 $this->errorTO->type="S";
                      return $this->errorTO;  
                  }	
           }
    }
// *************************************************************************************************************************
    public function UpdateAutoMatchInvoice($creditnoteValue, $invoiceDocUid, $CreditNoteUid )  {

            $pssql = "update document_header dhh 
                      left join payment_detail pd on pd.document_master_uid = dhh.document_master_uid
                                set dhh.payment_status =  if(round(dhh.payment_amount,2) + ". round($creditnoteValue,2) ." + round(dhh.invoice_total,2) =0 , " .MATCHED_INVOICE_FULL ."," .MATCHED_INVOICE_PARTIAL ."),
                                    dhh.matched_amount =  round(dhh.payment_amount,2)    + ". round($creditnoteValue,2) ." + round(dhh.invoice_total,2),
                                    dhh.payment_amount =  dhh.payment_amount + ". round($creditnoteValue,2) .",
                                    dhh.source_document_uid = " . $CreditNoteUid  . "
                      where dhh.document_master_uid  = ". $invoiceDocUid. " ;";

             $this->errorTO = $this->dbConn->processPosting($pssql,"");
      
             if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                  $this->errorTO->description="Invoice Header Update Failed : ".$this->errorTO->description;
                  return $this->errorTO;         	                  
             } 
             $this->dbConn->dbQuery("commit");
    }    
                    
// *************************************************************************************************************************
    public function UpdateAutoMatchCredit($creditDocUid, $invoiceDocUid)  {

             $pssql = "update document_header dhh set dhh.payment_status = " . MATCHED_CREDIT . ",
                                                      dhh.source_document_uid = " . $invoiceDocUid  . "
                       where  dhh.document_master_uid = ". $creditDocUid.";";
 
             $this->errorTO = $this->dbConn->processPosting($pssql,"");
      
             if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                  $this->errorTO->description="Credit Note Header Update Failed : ".$this->errorTO->description;
                  return $this->errorTO;         	                  
             } 
             $this->dbConn->dbQuery("commit");
             return $this->errorTO;
    }	           
// *************************************************************************************************************************
    public function SavePaymentRecordsToTracking($principalId, $paymentNo)  {
    	
    	    global $ROOT; global $PHPFOLDER;
    	   
    	    $paysql = "select ph.principal_uid,
                            ph.payment_number,
                            ph.type_uid,
                            ph.payment_date,
                            ph.capture_date, 
                            ph.payment_by,
                            ph.principal_store_uid,
                            ph.amount, 
                            ph.captured_by,
                            pd.payment_amount,
                            pd.payment_count,
                            dm.document_number,
                            dm.depot_uid,
                            dh.matched_amount, 
                            dh.payment_status
                      from  payment_header ph,
                            payment_detail pd, 
                            document_master dm,
                            document_header dh
                      where ph.uid = pd.payment_header_uid
                      and   pd.document_master_uid = dm.uid
                      and   dm.uid = dh.document_master_uid      
                      and   ph.principal_uid  = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) ."
                      and   ph.payment_number = " . mysqli_real_escape_string($this->dbConn->connection, $paymentNo)   ." ;";
    
          $paymentDetails = $this->dbConn->dbGetAll($paysql);

          $lineNo=1;          
          
          foreach($paymentDetails as $prinrow) {
    	       
    	            // Get Order sequence No
                     $sequenceDAO = new SequenceDAO($this->dbConn);
                     $sequenceTO = new SequenceTO;
                     $errorTO = new ErrorTO;
                     $sequenceTO->sequenceKey=LITERAL_SEQ_ORDER;
                     $sequenceTO->principalUId = $principalId;
                     $result=$sequenceDAO->getSequence($sequenceTO,$orderSeqVal);
          
                     if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                        return $result;
                    }
             
                     $dmsql="INSERT INTO document_master (`depot_uid`, 
                                                   `principal_uid`, 
                                                   `document_number`,
                                                   `document_type_uid`, 
                                                   `processed_date`, 
                                                   `processed_time`, 
                                                   `last_updated`,
                                                   `order_sequence_no`, 
                                                   `version` ) 
                            VALUES ("  .	$prinrow['depot_uid']          . ",
                                    "  .	$prinrow['principal_uid']      . ",                
                                    '" .	str_pad($prinrow['payment_count'],3,'0',STR_PAD_LEFT) . $prinrow['payment_number']      . "',
                                    "  .  DT_PAYMENT                     . "  ,   --   document_type_uid
                                    '" .  gmdate(GUI_PHP_DATE_FORMAT)    .  "',   --   processed_date
                                    '" .  gmdate(GUI_PHP_TIME_FORMAT)    .  "',   --   processed_time
                                    now(),                                        --   last_updated               
                                    "  .  $orderSeqVal                   .  ",    --   order_sequence_no,             
                                    1)  ;                                         --   version " ; 
                                                                                                   
                     $this->errorTO = $this->dbConn->processPosting($dmsql,"");
                     $this->dbConn->dbQuery("commit"); 
                     $dmUId = $this->dbConn->dbGetLastInsertId();                                     
                                                   
                     $dhsql="INSERT INTO document_header (document_master_uid, 
                                                          order_date, 
                                                          invoice_date,
                                                          document_status_uid, 
                                                          principal_store_uid, 
                                                          customer_order_number,
                                                          invoice_number, 
                                                          exclusive_total,
                                                          vat_total,
                                                          discount_reference,
                                                          grv_number,
                                                          claim_number,
                                                          cases,
                                                          invoice_total, 
                                                          source_document_number, 
                                                          captured_by)
                                                         
                                                          VALUES (" . $dmUId . ",  
                                                                  '" . $prinrow['payment_date']        . "',                           
                                                                  '" . $prinrow['capture_date']        . "',   
                                                                  " . DST_PAYMENT                      . " ,
                                                                  " . $prinrow['principal_store_uid']  . " ,
                                                                  ''                                       ,
                                                                  ''                                       ,
                                                                  0                                        ,
                                                                  0                                        , 
                                                                  ''                                       ,
                                                                  ''                                       ,
                                                                  ''                                       ,
                                                                  1                                        , 
                                                                  " . $prinrow['payment_amount']       . " ,
                                                                  " . $prinrow['document_number']      . " , 
                                                                  " . $prinrow['captured_by']          . ");";       
                     $this->errorTO = $this->dbConn->processPosting($dhsql,"");
                     $this->dbConn->dbQuery("commit"); 
                                                   
          	         $ddsql="INSERT INTO document_detail (document_master_uid, 
                                                          line_no, 
                                                          product_uid, 
                                                          ordered_qty,
                                                          document_qty,
                                                          delivered_qty,
                                                          selling_price, 
                                                          discount_value,
                                                          net_price,
                                                          extended_price,
                                                          vat_amount,
                                                          vat_rate,
                                                          Discount_reference,
                                                          total)
                             VALUES (" .  $dmUId    . ", 
                                     " .  $lineNo   . "
                                     9999,1,1,1,0,0,0,0,0,0,'', 
                                     " . $prinrow['payment_amount']      . ")";                                     
                     $lineNo++; 
                     $this->errorTO = $this->dbConn->processPosting($ddsql,"");
                     $this->dbConn->dbQuery("commit"); 
          }
    }
// *************************************************************************************************************************
// Update allocations Table 
// *************************************************************************************************************************
    public function UpdateAllocations($PaymentsTO, $phuid, $PayHeader)  { 
    	
        $sql = "INSERT INTO allocations (allocations.principal_uid,
                            allocations.allocation_to_account,
                            allocations.transaction_uid,      -- Payment uid
                            allocations.document_type_uid,
                            allocations.payment_credit_total,
                            allocations.allocation_date,
                            allocations.allocation_to_uid, -- source document uid
                            allocations.allocation_to_number,
                            allocations.allocation_to_total,
                            allocations.allocated_amount,
                            allocations.allocated_by,
                            allocations.`status` )
                VALUES ( '" . $PaymentsTO->PrincipalUid                   . "',
                         '" . trim(substr($PaymentsTO->CustomerUid,1,10)) . "',
                         "  . mysqli_real_escape_string($this->dbConn->connection, $phuid) . ",
                         46,  
                         0-" . $PaymentsTO->PaymentAmount                 . ",
                         '"  . $PaymentsTO->PaymentDate                   . "',
                         if( '". $PayHeader ."' = 'DONE','"  . $PaymentsTO->InvoiceList . "',3),
                         if( '". $PayHeader ."' = 'DONE',(SELECT dm.document_number from document_master dm where dm.uid = '"  . $PaymentsTO->InvoiceList . "') , 'Unallocated Payment'),
                         if( '". $PayHeader ."' = 'DONE',(SELECT dh.invoice_total FROM .document_header dh WHERE dh.document_master_uid = '"  . $PaymentsTO->InvoiceList . "'), 0 ),	
                         if( '". $PayHeader ."' = 'DONE', 0- " . str_replace(' ', '',$PaymentsTO->InvoicePaymentAmount) .  ", " . $PaymentsTO->UnAllocatedAmount .") ,
                         '" . $PaymentsTO->CapturedBy                     . "',
                         'C')";
                 $this->errorTO = $this->dbConn->processPosting($sql,"");
                 $this->dbConn->dbQuery("commit");
                 return $this->errorTO; 
   }
// *************************************************************************************************************************
// Clear Invoices add to allocations Table 
// *************************************************************************************************************************
  public function insertAllocatedTransaction($principalId, $storeId, $doctype,$amount,$tdate, $docno) {  
  	
  	  // get user. don't pass it because this is more secure.
      if (!isset($_SESSION)) session_start();
      
      $userId = $_SESSION['user_id'];
  
        $pssql = "INSERT INTO `allocations` (`principal_uid`, 
                              `allocation_to_account`, 
                              `document_type_uid`, 
                              `payment_credit_total`, 
                              `allocation_date`, 
                              `allocation_to_number`, 
                              `allocated_amount`, 
                              `allocated_by`, 
                              `status`, 
                              `Comment`) 
                   VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "', 
                           '" . mysqli_real_escape_string($this->dbConn->connection, $storeId) . "', 
                           '" . mysqli_real_escape_string($this->dbConn->connection, $doctype) . "', 
                           '" . mysqli_real_escape_string($this->dbConn->connection, $amount) . "', 
                           '" . mysqli_real_escape_string($this->dbConn->connection, $tdate) . "', 
                           '" . mysqli_real_escape_string($this->dbConn->connection, $docno) . "', 
                           '" . mysqli_real_escape_string($this->dbConn->connection, $amount) . "', 
                           '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "', 
                           'C', 
                           'Forced');";
                           
                   $this->errorTO = $this->dbConn->processPosting($pssql,"");      
                   if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                        $this->errorTO->description=$this->errorTO->description ."<br>";
                        $this->errorTO->sql = $sql;                      
                        return $this->errorTO;         	                  
                   } 
                   $this->dbConn->dbQuery("commit");
                   return $this->errorTO;

// *************************************************************************************************************************
  
  
  
  
  }



}
?>
<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
include_once($ROOT .$PHPFOLDER  . 'TO/PostingDocumentTO.php');  
include_once($ROOT .$PHPFOLDER  . 'TO/PostingDocumentDetailTO.php'); 

class CreateTransactionDAO {
	
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }
// ********************************************************************************************************************************************
   public function getdocumentSequences($principalId, 
                                        $sequenceType,
                                        $transType,
                                        $whUid,
                                        $dataSrc) {
         // Get Order sequence No
         $sequenceDAO = new SequenceDAO($this->dbConn);
         $sequenceTO = new SequenceTO;
         $errorTO = new ErrorTO;
         $sequenceTO->sequenceKey=$sequenceType;
         $sequenceTO->documentTypeUId = $transType;
         $sequenceTO->principalUId = $principalId;
         $sequenceTO->depotUId     = $whUid;
         $sequenceTO->dataSource   = $dataSrc;
         $result=$sequenceDAO->getSequence($sequenceTO,$docNoVal);

         return $docNoVal;
         
    }        

// ********************************************************************************************************************************************
   public function getStartStatus($depUid) {
        
    	// Get Transaction status
         $transArr = array(DT_ORDINV, DT_UPLIFTS, DT_ORDINV_ZERO_PRICE, DT_DELIVERYNOTE);
    	   
    	   if(in_array(mysqli_real_escape_string($this->dbConn->connection, $transType), $transArr)) {
               $sql = "SELECT d.order_start_status
                       FROM .depot d
                       WHERE d.uid = "  .	mysqli_real_escape_string($this->dbConn->connection, $depUid) ;
               $res = $this->dbConn->dbGetAll($sql);  
               
               $dst = $res[0]['order_start_status'];      
    	   } else { 
               $dst = DST_PROCESSED;
    	   }
    	   
    	   return $dst;
    	   
}

// ********************************************************************************************************************************************
   public function getStartStatusUsingStoreUID($storeUid) {
        
        $sql = "SELECT d.order_start_status, 
                       d.uid AS 'depotUid'
                FROM principal_store_master psm 
                INNER JOIN depot d ON psm.depot_uid = d.uid
                WHERE psm.uid = "  .	mysqli_real_escape_string($this->dbConn->connection, $storeUid) ;
               
        $res = $this->dbConn->dbGetAll($sql);  
               
       return $res;
    	   
}  

// ********************************************************************************************************************************************
    public function createTransaction($PostingDocumentTO) {

         $dmsql="INSERT INTO document_master (`depot_uid`, 
                                              `principal_uid`, 
                                              `document_number`,
                                              `document_type_uid`, 
                                              `processed_date`, 
                                              `processed_time`,
                                              `order_sequence_no`, 
                                              `version`,
                                              `api_reference`,
                                              `rwr_file`,
                                              `incoming_file`,
                                              `confirmation_file`) 
                 VALUES  ("  .	mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->depotUId)                                     . " ,
                          "  .	mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->principalUId)                                 . " ,                
                         '"  .	str_pad(mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->documentNumber),8,"0", STR_PAD_LEFT)  . "',  
                          "  .  mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->documentTypeUId)                              . " ,   --   document_type_uid
                         '"  .	mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->processedDate)                                . "',  
                         '"  .  mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->processedTime)                                . "',   --   processed_time           
                          "  .  mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->orderSequenceNo)                              . " ,   --   order_sequence_no,             
                         '"  .	mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->version)                                      . "',  --   version 
                         '"  .	mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->apiReference)                                . "',  --   Incoming order Reference
                         '"  .	mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->rwrFile)                                      . "',
                         '"  .	mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->incomingFile)                                 . "',
                         
                         '"  .	mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->confirmationFile)                             . "');";
  
//echo "<br>";
//echo "<pre>";
//echo $dmsql;
//echo "<br>";

                $this->errorTO = $this->dbConn->processPosting($dmsql,"");
                if ($this->errorTO->type != FLAG_ERRORTO_ERROR) {
                        $this->dbConn->dbQuery("commit"); 
                        $dmUId = $this->dbConn->dbGetLastInsertId();
                    
                        $headerCs = $headerExcl = $headerVat = $headerTot = 0; 

                        foreach ($PostingDocumentTO->detailArr as $row) {
                        	
                        	  if(mysqli_real_escape_string($this->dbConn->connection, $row->orderedQty)  > 0) {   
                        	
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
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->lineNo)            . ",
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->productUId)        . ",
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->orderedQty)        . ",
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->documentQty)       . ",
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->deliveredQty)      . ",
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->sellingPrice)      . ",
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->discountValue)     . ",
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->netPrice)          . ",
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->extendedPrice)     . ",
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->vatAmount)         . ",
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->vatRate)           . ",
                                                '" . mysqli_real_escape_string($this->dbConn->connection, $row->discountReference). "', 
                                                " . mysqli_real_escape_string($this->dbConn->connection, $row->total)             . ")";  
//echo "ww<br>";
//echo "<pre>";
//echo $ddsql;
//echo "<br>";


                                $this->errorTO = $this->dbConn->processPosting($ddsql,"");
                            
                                $headerCs   = $headerCs   + mysqli_real_escape_string($this->dbConn->connection, $row->orderedQty);
                                $headerExcl = $headerExcl + mysqli_real_escape_string($this->dbConn->connection, $row->extendedPrice);
                                $headerVat  = $headerVat  + mysqli_real_escape_string($this->dbConn->connection, $row->vatAmount);
                                $headerTot  = $headerTot  + mysqli_real_escape_string($this->dbConn->connection, $row->total);
                            }      
                        }
                         
                 if ($this->errorTO->type != FLAG_ERRORTO_ERROR) {
                        $this->dbConn->dbQuery("commit");
                        $dhsql="INSERT INTO document_header (document_master_uid, 
                                                             order_date, 
                                                             invoice_date,
                                                             due_delivery_date,
                                                             requested_delivery_date,
                                                             delivery_date,
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
                                                             data_source, 
                                                             captured_by)
                                                  
                                VALUES ( "  . $dmUId . ",  
                                        '"  . mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->orderDate)           . "',                           
                                        '"  . mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->invoiceDate)         . "',
                                        '"  . mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->deliveryDate)        . "',   
                                        '"  . mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->deliveryDate)        . "',   
                                        '"  . mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->deliveryDate)        . "',   
                                         "  . mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->documentStatusUId)   . " ,
                                         "  . mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->principalStoreUId)   . " ,
                                        '"  . mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->customerOrderNumber) . "',
                                        '',
                                         "  . mysqli_real_escape_string($this->dbConn->connection, $headerExcl) . ",
                                         "  . mysqli_real_escape_string($this->dbConn->connection, $headerVat)  . ",
                                        '"  . mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->discountReference)   . "',
                                        '',
                                        '',
                                         "  . mysqli_real_escape_string($this->dbConn->connection, $headerCs) . ",
                                         "  . mysqli_real_escape_string($this->dbConn->connection, $headerTot) . ",
                                        '',
                                        '"  . mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->dataSource)   . "',
                                        '"  . mysqli_real_escape_string($this->dbConn->connection, $PostingDocumentTO->capturedBy)   . "');";       

//echo "ww<br>";
//echo "<pre>";
//echo $dhsql;
//echo "<br>";
                        $this->errorTO = $this->dbConn->processPosting($dhsql,"");                         

                        if ($this->errorTO->type != FLAG_ERRORTO_ERROR) {
                                  $this->dbConn->dbQuery("commit"); 
                                  $this->errorTO->type = FLAG_ERRORTO_SUCCESS;                                 
                                  $this->errorTO->description = "Transaction loaded successfully";
         	              } else {
         	              	        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                	                $this->errorTO->description = "Insert into document header failed";
                	                $this->errorTO->identifier = $dhsql;
         	              }
                 } else { 
                 	     $this->errorTO->type = FLAG_ERRORTO_ERROR; 
                 	     $this->errorTO->description = "Insert into document detail failed";
                 }      
         }  else {
               $this->errorTO->type = FLAG_ERRORTO_ERROR;
               $this->errorTO->description = "Insert into document master failed";
         }    
         return $this->errorTO;

    }

}
// ********************************************************************************************************************************************                                                                                                                    
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');    	

class PostNewTransactionRecordDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// **************************************************************************************************************************************************** 
    public function SaveTransactionToTracking($principalId, 
                                              $depotId, 
                                              $branch,
                                              $userId,
                                              $chepPalUid, 
                                              $cases, 
                                              $tripList,
                                              $comments,
                                              $recptUid)  {
    	
    	    global $ROOT; global $PHPFOLDER;
    	   
          $lineNo=1;
          
          // Get Principal Store
              
              $sql = "SELECT *
                      FROM .principal_store_master psm
                      WHERE psm.principal_uid    = "   . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                      AND   psm.branch_code      = '"  . mysqli_real_escape_string($this->dbConn->connection, $branch)      . "'
                      AND   psm.depot_uid        = "   . mysqli_real_escape_string($this->dbConn->connection, $depotId)     . ";";
                      
              $prinDet = $this->dbConn->dbGetAll($sql);
              
              if(count($prinDet) == 0) {?>
                   <script type='text/javascript'>parent.showMsgBoxError('Bomb out - Dispatch store not set up')</script> 
                   <?php
                   echo $sql ;
              }
          
          // Get Dispatch sequence No
             $sequenceDAO = new SequenceDAO($this->dbConn);
             $sequenceTO = new SequenceTO;
             $errorTO = new ErrorTO;
             $sequenceTO->sequenceKey=LITERAL_SEQ_ORDER;
             $sequenceTO->principalUId = mysqli_real_escape_string($this->dbConn->connection, $principalId);
             $result=$sequenceDAO->getSequence($sequenceTO,$orderSeqVal);
             
             $orderSequence = $orderSeqVal;
             
          // Get Dispatch Document No
             $sequenceDAO = new SequenceDAO($this->dbConn);
             $sequenceTO = new SequenceTO;
             $errorTO = new ErrorTO;
             $sequenceTO->sequenceKey=LITERAL_SEQ_PALLETCONTROL;
             $result=$sequenceDAO->getSequence($sequenceTO,$orderSeqVal);
             
             $disDocNo = $orderSeqVal; 
          
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
                            VALUES ("  .	mysqli_real_escape_string($this->dbConn->connection, $depotId)         . ",
                                    "  .	mysqli_real_escape_string($this->dbConn->connection, $principalId)     . ",                
                                    '" .	str_pad($disDocNo,8,"0", STR_PAD_LEFT)                                 . "',
                                    "  .  DT_DELIVERYNOTE                                                        . "  ,  --   document_type_uid
                                    '" .  gmdate(GUI_PHP_DATE_FORMAT)                                            . "',   --   processed_date
                                    '" .  gmdate(GUI_PHP_TIME_FORMAT)                                            . "',   --   processed_time
                                    now()                                                                            ,   --   last_updated               
                                    "  .  $orderSequence                                                         . " ,   --   order_sequence_no,             
                                    1);                                                                                   --   version " ; 
                     $this->errorTO = $this->dbConn->processPosting($dmsql,"");
                     
                     if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) { 
                     	   return $this->errorTO;
                     }	                        
                     
                     $this->dbConn->dbQuery("commit"); 
                     $dmUId = $this->dbConn->dbGetLastInsertId();
                     
                     $dhsql="INSERT INTO document_header (document_master_uid, 
                                                          order_date, 
                                                          invoice_date,
                                                          document_status_uid, 
                                                          principal_store_uid,
                                                          customer_order_number,
                                                          buyer_account_reference,
                                                          invoice_number, 
                                                          exclusive_total,
                                                          vat_total,
                                                          discount_reference,
                                                          grv_number,
                                                          claim_number,
                                                          cases,
                                                          invoice_total, 
                                                          source_document_number,
                                                          debrief_comment,
                                                          additional_details, 
                                                          captured_by)
                                                         
                                                          VALUES (" . $dmUId . ",  
                                                                  '" . date('Y-m-d')                   . "',                           
                                                                  '" . date('Y-m-d')                   . "',     
                                                                  "  . DST_INVOICED                    . " ,
                                                                  "  . $prinDet[0]['uid']              . " ,
                                                                  '"  . $branch                        . "',
                                                                  '"  . $recptUid                      . "',
                                                                  ''                                       ,
                                                                  0                                        ,
                                                                  0                                        , 
                                                                  ''                                       ,                                ''                                       ,
                                                                  ''                                       ,
                                                                  " . $cases . "                           , 
                                                                  0                                        ,
                                                                  ''                                       ,
                                                                  '"  . mysqli_real_escape_string($this->dbConn->connection, $comments)      . "', 
                                                                  '"  . mysqli_real_escape_string($this->dbConn->connection, $tripList)      . "', 
                                                                  " . $userId   . ");";       
                     $this->errorTO = $this->dbConn->processPosting($dhsql,"");

                     if ($this->errorTO->type !=FLAG_ERRORTO_SUCCESS) { 
                     	   return $this->errorTO;
                     }	                

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
                             VALUES (" .  $dmUId      . ", 
                                     " .  $lineNo     . ",
                                     " .  $chepPalUid . ",
                                     " . $cases . ",
                                     " . $cases . ",
                                     " . $cases . ",
                                     0,0,0,0,0,0,'', 
                                     0)";                     

                     $lineNo++; 
                     $this->errorTO = $this->dbConn->processPosting($ddsql,"");

                     if ($this->errorTO->type !=FLAG_ERRORTO_SUCCESS) { 
                     	   return $this->errorTO;
                     }	                

                     $this->dbConn->dbQuery("commit");
                     $this->errorTO->identifier = $dmUId;
                     return $this->errorTO;
    }
//***************************************************************************************************************************************************************************  
 
} 
?>
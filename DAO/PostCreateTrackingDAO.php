<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."TO/PaymentsTO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class PostCreateTrackingDAO {
	
    private $dbConn;

    function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
    }
// *************************************************************************************************************************
    public function getConsolidationWarehousesChains($principalId)  {
    	
         $sql   = "SELECT pp.consolidate_by_warehouses, 
                          pp.consolidate_by_chains, 
                          pp.consolidated_warehouse, 
                          pp.consolidated_chain
                   FROM .principal_preference pp
                   WHERE pp.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) .";";
    	
    	   $wCH = $this->dbConn->dbGetAll($sql);	
    	   
    	   return $wCH;
    	
    }
// *************************************************************************************************************************
    public function CreateConsolidatedTransaction($principalId, $whId, $pchain, $cChain, $cdepot)  {
    	
          global $ROOT; global $PHPFOLDER;
          // Get transcations to be consolidated
    	    
          $csql = "SELECT dm.uid
                   FROM .document_master dm
                   INNER JOIN document_header dh ON dm.uid = dh.document_master_uid
                   INNER JOIN principal_store_master psm on psm.uid = dh.principal_store_uid
                   LEFT  JOIN principal_preference pp ON dm.principal_uid = pp.principal_uid
                   WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) ."
                   AND   dm.document_type_uid IN (" . DT_ORDINV . "," . DT_DELIVERYNOTE . "," . DT_ORDINV_ZERO_PRICE . ")
                   AND   dh.document_status_uid IN (" . DST_INVOICED . "," . DST_DIRTY_POD . "," . DST_DELIVERED_POD_OK . ")
                   AND   dh.invoice_date > pp.consolidated_transactions_start
                   AND   dm.depot_uid  = " . mysqli_real_escape_string($this->dbConn->connection, $whId) ."
                   AND   psm.principal_chain_uid = " . mysqli_real_escape_string($this->dbConn->connection, $pchain) ."
                   AND   dh.invoice_date >= '2020-09-01'
                   AND   dm.consolidated = 'N';";
    	    
          $aCT = $this->dbConn->dbGetAll($csql);
    	    
    	    if (count($aCT) > 0) {
               // Get payment sequence No
               $sequenceDAO = new SequenceDAO($this->dbConn);
               $sequenceTO = new SequenceTO;
               $errorTO = new ErrorTO;
               $sequenceTO->sequenceKey=LITERAL_SEQ_CONSOLIDATED;
               $sequenceTO->principalUId = mysqli_real_escape_string($this->dbConn->connection, $principalId);
               $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
          
               if ($result->type=FLAG_ERRORTO_SUCCESS) {
               	
               	   $docString = array();
          	
                   // set document master             
                   foreach($aCT as $row) {            	
                        $usql = "UPDATE document_master dm set dm.consolidated = 'Y',
                                                               dm.consolidated_reference = " . $seqVal . "
                                 WHERE dm.uid in (" . $row['uid'] . ") ;" ; 
                   
                        $this->errorTO = $this->dbConn->processPosting($usql,"");
                        
                        array_push($docString,$row['uid']);
       
                        if($this->errorTO->type == 'S') {
                           $this->dbConn->dbQuery("commit");
                        }
                   }
                   // Extract and create transaction
                   
                   //  Get consolidated store by region / chain / alt chain
           
                   $ssql = "SELECT psm.uid, 
                                   psm.deliver_name
                            FROM principal_store_master psm
                            WHERE psm.principal_uid           = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                            AND   psm.alt_principal_chain_uid = " . mysqli_real_escape_string($this->dbConn->connection, $pchain) . " 
                            AND   psm.principal_chain_uid     = " . mysqli_real_escape_string($this->dbConn->connection, $cChain) . " 
                            AND   psm.depot_uid               = " . mysqli_real_escape_string($this->dbConn->connection, $whId) . ";";
                          
                   $cStore = $this->dbConn->dbGetAll($ssql);
                   
                   if (count($cStore) <> 1) {
                        echo "<br>";
                        echo "Bomb Out";
                        echo "<br>";
                        return;
              
                   } else {
                                      
                        // Get Consolidated transaction details
                        $docList = implode(",",$docString);
                        $dsql = "SELECT dd.product_uid, 
                                        SUM(dd.document_qty)   AS 'Qty', 
                                        SUM(dd.extended_price) AS 'ExtP', 
                                        SUM(dd.vat_amount)     AS 'VA', 
                                        SUM(dd.total)          AS 'Tot'
                                 FROM .document_detail dd
                                 WHERE dd.document_master_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $docList) . ")
                                 and   dd.extended_price > 0
                                 GROUP BY dd.product_uid;";
                        
                        $ddArr = $this->dbConn->dbGetAll($dsql);
                        
                        // Get Order sequence No
                        $sequenceDAO = new SequenceDAO($this->dbConn);
                        $sequenceTO = new SequenceTO;
                        $errorTO = new ErrorTO;
                        $sequenceTO->sequenceKey=LITERAL_SEQ_ORDER;
                        $sequenceTO->principalUId = $principalId;
                        $result=$sequenceDAO->getSequence($sequenceTO,$orderSeqVal);
                        
                        $dmsql = "INSERT INTO document_master (`depot_uid`, 
                                                               `principal_uid`, 
                                                               `document_number`,
                                                               `document_type_uid`, 
                                                               `processed_date`, 
                                                               `processed_time`, 
                                                               `last_updated`,
                                                               `order_sequence_no`, 
                                                               `version` ) 
                                   VALUES ("  . mysqli_real_escape_string($this->dbConn->connection, $cdepot)      . ",
                                           "  . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ",                
                                           '"  . str_pad( $seqVal,8,'0',STR_PAD_LEFT)       . "',
                                           "  .  DT_ORDINV                                 . "  ,   --   document_type_uid
                                    '" .  gmdate(GUI_PHP_DATE_FORMAT)                      .  "',   --   processed_date
                                    '" .  gmdate(GUI_PHP_TIME_FORMAT)                      .  "',   --   processed_time
                                    now(),                                                          --   last_updated               
                                    "  .  $orderSeqVal                                     .  ",    --   order_sequence_no,             
                                    1)  ;                                                           --   version " ; 
                                                                                    
                        echo "<br>";
                        echo $dmsql;
                        echo "<br>"; 
                        $this->errorTO = $this->dbConn->processPosting($dmsql,"");
                        $this->dbConn->dbQuery("commit"); 
                        $dmUId = $this->dbConn->dbGetLastInsertId();
                        
                        $lineNo = 1;
                        $case   = $excl  =  $vata  =  $gtot  =  0;
                       
                        foreach($ddArr as $drow) {
                       
                                $ddsql = "INSERT INTO document_detail (document_master_uid, 
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
                                          VALUES (" .  $dmUId        . ", 
                                                  " .  $lineNo       . ",
                                                  " .  $drow['product_uid']     . ",
                                                  " .  $drow['Qty']     . ",
                                                  " .  $drow['Qty']     . ",
                                                  " .  $drow['Qty']     . ",
                                                  " .  $drow['ExtP'] / $drow['Qty']    . ",
                                                  0,
                                                  " .  $drow['ExtP'] / $drow['Qty']    . ",
                                                  " .  $drow['ExtP']                   . ",
                                                  " .  $drow['VA']                     . ",
                                                  " .  VAL_VAT_RATE_TBLSTD             . ",
                                                  '', 
                                                  " .  $drow['Tot']     . ")";   
                                                  echo "<br>";
                                                  echo  $ddsql;                                 
                                                  echo "<br>";
                                $this->errorTO = $this->dbConn->processPosting($ddsql,"");
                                $this->dbConn->dbQuery("commit"); 
                                
                                $lineNo++;
                     
                                $case = $case + $drow['Qty']  ;
                                $excl = $excl + $drow['ExtP']  ;  
                                $vata = $vata + $drow['VA']  ;  
                                $gtot = $gtot + $drow['Tot']  ;
                        }     

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
                                                                  '" . gmdate(GUI_PHP_DATE_FORMAT)        . "',                           
                                                                  '" . gmdate(GUI_PHP_DATE_FORMAT)        . "',   
                                                                  " . DST_INVOICED                        . " ,
                                                                  " . $cStore[0]['uid']                   . " ,
                                                                  ''                                       ,
                                                                  ''                                       ,
                                                                  " .  $excl . "                           ,
                                                                  " .  $vata . "                           ,
                                                                  ''                                       ,
                                                                  ''                                       ,
                                                                  ''                                       ,
                                                                  " . $case  . "                           ,
                                                                  " . $gtot  . "                           ,
                                                                  ''                                       , 
                                                                  'CONSOLIDATE');"; 
                                                                  
                                                                  echo $dhsql;
                                                                  
                        $this->errorTO = $this->dbConn->processPosting($dhsql,"");
                        $this->dbConn->dbQuery("commit"); 
                   }

               }
          }
    }
// *************************************************************************************************************************

}
?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');    	

class AgedStockDAO {
	private $dbConn;

    function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
    }

// *************************************************************************************************************************
  public function getStoreRep($prinUid, $psmUid) {
  
  $sql = "SELECT psr.uid, psr.first_name
          FROM principal_sales_representative psr 
          WHERE psr.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinUid) . ";";
  $reparr = $this->dbConn->dbGetAll($sql);
  
  return $reparr;
  }
// *************************************************************************************************************************
  public function getDocumentDetailsToUpdate($principalId, $docNo) {
  	
    $sql = "select dm.document_number,       
                   dm.order_sequence_no,     
                   psm.deliver_name,         
                   o.delivery_instructions,  
                   dh.customer_order_number, 
                   dm.uid,                   
                   dh.document_status_uid,   
                   s.description as 'Status',
                   d.uid as 'depot_uid',     
                   d.name as 'Depot',        
                   psm.uid as 'StoreUid',    
                   dm.uid  as 'docUid',       
                   dd.uid as 'detailUid',    
                   dh.order_date,            
                   dd.product_uid,           
                   pp.product_code,          
                   ppdg.outercasing_gtin,    
                   pp.product_description,   
                   dd.ordered_qty,   
                   pp.unit_value,            
                   p.name as 'Principal',
                   asd.uplift_number
           FROM      document_master dm
           LEFT JOIN aged_stock_detail asd ON asd.uplift_number = dm.document_number,
                     orders o,
                     principal p,
                     document_header dh,
                     document_detail dd,
                     principal_store_master psm, 
                     depot d,
                     `status` s,
                     principal_product pp
           LEFT JOIN principal_product_depot_gtin ppdg ON ppdg.principal_product_uid = pp.uid  AND pp.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
           where dm.uid = dh.document_master_uid
           and   dm.uid = dd.document_master_uid   
           and dm.order_sequence_no = o.order_sequence_no 
           and dh.principal_store_uid = psm.uid
           and dm.principal_uid = p.uid
           and dm.depot_uid = d.uid
           and dh.document_status_uid = s.uid
           and dd.product_uid = pp.uid
           and dm.document_type_uid in (" . DT_UPLIFTS . ")
           and dm.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . " 
           and dm.document_number = '" . str_pad(trim(mysqli_real_escape_string($this->dbConn->connection, $docNo)),8,'0', STR_PAD_LEFT) . "';";
        
    $mfDDU = $this->dbConn->dbGetAll($sql);
    
//  echo $sql;

    return $mfDDU;
  
  }

// *************************************************************************************************************************

  public function whReceiptValidation($boxes, $uvalue, $delby) {

        if(!is_numeric(test_input($boxes))) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description="Boxes not Numeric";
            return $this->errorTO;
        } else { 
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        }	

        if(!is_numeric(test_input($uvalue))) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description="Value not Numeric";
            return $this->errorTO;
        } else { 
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        }	

        if(test_input($delby) == 'Select Rep') {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description="No Delivered By / Rep Selected";
            return $this->errorTO;
        } else { 
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        }	


        $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        return $this->errorTO;
  }
// *************************************************************************************************************************
  public function getWareHouseReceipt($docUid) { 

       $sql = "SELECT *
               FROM .aged_stock_warehouse_receipt asw
               WHERE asw.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid) .";";

       $whSr = $this->dbConn->dbGetAll($sql);
    
//   echo $sql;

       return $whSr;
  }
// *************************************************************************************************************************
  public function validateWarehseBoxes($boxes, $docUid) { 

        if(!is_numeric(test_input($boxes))) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description="Boxes not Numeric";
            return $this->errorTO;
        }
        $sql  = "SELECT asw.boxes
                 FROM   aged_stock_warehouse_receipt asw
                 WHERE  asw.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid) .";";

        $whSr = $this->dbConn->dbGetAll($sql);
        if($whSr[0]['boxes'] <> $boxes ) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description="Warehouse Receipt and Boxes Quantity do not Balance";
            return $this->errorTO;
        } else {
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        }
        
        return $this->errorTO;

  }
// *************************************************************************************************************************
  public function validateUpliftDetail($ul, $dsp, $rf ,$ntf, $dam, $agStk) {
 
        if(!is_numeric($ul)) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description="Uplift Quantity not Numeric";
            return $this->errorTO;
        } elseif(!is_numeric($dsp)) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description="Display Quantity not Numeric";
            return $this->errorTO;
        } elseif(!is_numeric($rf)) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description="Store Refused Quantity not Numeric";
            return $this->errorTO;
        } elseif(!is_numeric($dam)) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description="Damages Quantity not Numeric";
            return $this->errorTO;
        } elseif(!is_numeric($ntf)) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description="Not Found Quantity not Numeric";
            return $this->errorTO;
        } elseif($agStk <> ($ul+$dsp+$rf+$ntf+$dam) ) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description="Line Quantity Not Balanced";
            return $this->errorTO;

        } else {
        	  $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        	  return $this->errorTO;
        }
  	
  }   
  
// *************************************************************************************************************************
  public function getDocumentDetailsToDispatch($principalId, $docNo) {

      $sql = "select dm.principal_uid,
                     dm.document_number,       
                     psm.deliver_name,         
                     o.delivery_instructions,  
                     dh.customer_order_number, 
                     dm.uid,                   
                     dh.document_status_uid,   
                     s.description as 'Status',
                     d.uid as 'depot_uid',     
                     d.name as 'Depot',        
                     psm.uid as 'StoreUid',    
                     dm.uid  as 'docUid',       
                     dd.uid as 'detailUid',    
                     dh.order_date,            
                     dd.product_uid,           
                     pp.product_code,          
                     pp.product_description,   
                     dd.ordered_qty,   
                     pp.unit_value,            
                     p.name as 'Principal',
                     awr.boxes,
                     awr.value, 
                     awr.`comment`,
                     asd.document_master_uid,
                     asd.`found`,
                     psd.uid as 'psdID',
                     psd.deliver_name AS 'Dispath_to',
                     ppd.uid AS 'Dispatch_prodID'
              FROM      document_master dm
              LEFT JOIN aged_stock_warehouse_receipt awr ON awr.document_master_uid = dm.uid
              LEFT JOIN aged_stock_detail asd ON asd.document_master_uid = dm.uid
              LEFT JOIN principal_store_master psd ON psd.principal_uid AND psd.branch_code = 999,
                        orders o,
                        principal p,
                        document_header dh,
                        document_detail dd,
                        principal_store_master psm, 
                        depot d,
                        `status` s,
                        principal_product pp
              LEFT JOIN principal_product ppd ON ppd.principal_uid = 346 AND ppd.product_code = 'BOX01'           
              where dm.uid = dh.document_master_uid
              and   dm.uid = dd.document_master_uid   
              and dm.order_sequence_no = o.order_sequence_no 
              and dh.principal_store_uid = psm.uid
              and dm.principal_uid = p.uid
              and dm.depot_uid = d.uid
              and dh.document_status_uid = s.uid
              and dd.product_uid = pp.uid
              and dm.document_type_uid in (" . DT_UPLIFTS . ")
              and dm.principal_uid   = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . " 
              and dm.document_number = '" . str_pad(trim(mysqli_real_escape_string($this->dbConn->connection, $docNo)),8,'0', STR_PAD_LEFT) . "';";
        
    $mfDDU = $this->dbConn->dbGetAll($sql);
    
// echo $sql;

    return $mfDDU;
}
// *************************************************************************************************************************
	public function getBoxDetailsToUpdate($principalUID, $docNum) {
		
			$sql = "SELECT dm.principal_uid as 'prinUid',
                     dm.uid AS 'docUid', 
                     dm.document_number, 
                     psm.uid as 'StoreUid', 
                     psm.deliver_name, 
                     dh.invoice_date,
                     dh.document_status_uid,
                     p.uid,
                     p.name
              FROM   document_master dm
              INNER JOIN .document_header dh on dm.uid = dh.document_master_uid
              INNER JOIN .principal p ON p.uid = dm.principal_uid
              INNER JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
              WHERE dm.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalUID) . "
              AND   dm.document_number = " . mysqli_real_escape_string($this->dbConn->connection, $docNum) . "
              and   dh.document_status_uid <> " . DST_WAREHOUSE_RECEIPT. ";"; 
							
      $result = $this->dbConn->dbGetAll($sql);
      							
			return $result;
}
// ******************************************************************************************************************
  public function getRepListNew($principalId) {

    $sql = "SELECT *
             FROM .principal_sales_representative psr
             WHERE psr.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
             AND   psr.`status` = 'A'
             ORDER BY psr.first_name";
            
    $repl = $this->dbConn->dbGetAll($sql);

    return $repl;
  }

//*************************************************************************************************************************************************
      public function insertBoxReceiptRecord($docUid,
                                             $value,
                                             $repUid,
                                             $noBoxes,
                                             $stRef,
                                             $comments,
                                             $userUId,
                                             $boxArr) {
      	
           $sql = "INSERT IGNORE INTO document_rvl_box_header (document_rvl_box_header.document_master_uid,
                                                               document_rvl_box_header.receipt_date,
                                                               document_rvl_box_header.value,
                                                               document_rvl_box_header.rep_uid,
                                                               document_rvl_box_header.noOfBoxes,
                                                               document_rvl_box_header.store_reference,
                                                               document_rvl_box_header.comments,
                                                               document_rvl_box_header.captured_by)
                   VALUES (" . mysqli_real_escape_string($this->dbConn->connection, $docUid)   . ",
                          NOW(),
                          '" . mysqli_real_escape_string($this->dbConn->connection, $value)    . "',
                          "  . mysqli_real_escape_string($this->dbConn->connection, $repUid)   . ",
                          '" . mysqli_real_escape_string($this->dbConn->connection, $noBoxes)  . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $stRef)    . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $comments) . "',
                          "  . mysqli_real_escape_string($this->dbConn->connection, $userUId)  . ") ;" ;
                          

                   $this->errorTO = $this->dbConn->processPosting($sql,"");

                   if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                         $this->errorTO->description="Box Header Insert Failed : ". $sql .$this->errorTO->description;
                         return $this->errorTO;         	                  
                   } 
                   
                   if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
            	            $pdUId = $this->dbConn->dbGetLastInsertId();
            	            $iSuccess = 'Y';
            	            $savedBoxes = 0;
            	            
            	            $this->dbConn->dbQuery("commit");
            	            
            	            foreach($boxArr as $brow) {
            	            	    if(trim($brow) <> '') {
   
                                        $isql = "INSERT IGNORE INTO document_rvl_box_detail (document_rvl_box_detail.document_rvl_box_header_uid,
                                                                                             document_rvl_box_detail.box_number)
                                                 VALUES ("  . $pdUId . ",
                                                        '"  . mysqli_real_escape_string($this->dbConn->connection, $brow) . "');";
                                           	    	// echo $isql; 
                                        $this->errorTO = $this->dbConn->processPosting($isql,"");

                                        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                                                $this->errorTO->description="Box Detail Insert Failed : ". $isql .$this->errorTO->description;
                                                $iSuccess = 'N';
                                               return $this->errorTO;         	                  
                                        }
                                       $savedBoxes++;
                                       $this->dbConn->dbQuery("commit"); 
                                }
                          }      
            	            if ($iSuccess == 'Y' && $savedBoxes == $noBoxes) {
            	            	     
            	            	     $sql = "UPDATE document_header dh set dh.document_status_uid = " . DST_WAREHOUSE_RECEIPT . " 
            	            	            WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $docUid);
            	            	     
            	            	     $this->errorTO = $this->dbConn->processPosting($sql,"");

                                 if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                                         $this->errorTO->description="Failed to Update Status : ". $sql .$this->errorTO->description;
                                         return $this->errorTO;         	                  
                                 }
                                 $this->dbConn->dbQuery("commit"); 
                                 return $this->errorTO;  
                          } else {
                          	     $this->errorTO->type = 'F';
                          	     $this->errorTO->description="Something went wrong : ". $isql .$this->errorTO->description;
                          	     return $this->errorTO;  
                          }
                   }
      }
// *************************************************************************************************************************
      public function checkDupBoxNo($prinId, $boxNo) {
       
          $sql = "SELECT dm.principal_uid, bd.box_number, bd.dispatched
                   FROM .document_rvl_box_detail bd
                   INNER JOIN .document_rvl_box_header bh ON bh.uid = bd.document_rvl_box_header_uid
                   INNER JOIN document_master dm ON bh.document_master_uid = dm.uid
                   WHERE dm.principal_uid = '"  . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "'
                   AND   bd.box_number    = '"  . mysqli_real_escape_string($this->dbConn->connection, $boxNo)  . "'";
                   
           $dupBox = $this->dbConn->dbGetAll($sql);

           return $dupBox;

      }
// *************************************************************************************************************************
      public function checkDispatchedBoxNo($prinId, $boxNo, $dispYn) {
      	
           if($dispYn == "Y") {
               $disLine = "AND bd.dispatched = 'Y'";      	   	
           } else {
               $disLine = "AND 1";
           }
           $sql = "SELECT *
                   FROM .document_rvl_box_detail bd
                   INNER JOIN .document_rvl_box_header bh ON bh.uid = bd.document_rvl_box_header_uid
                   INNER JOIN  document_master dm ON bh.document_master_uid = dm.uid
                   WHERE dm.principal_uid = '"  . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "'
                   AND   bd.box_number    = '"  . mysqli_real_escape_string($this->dbConn->connection, $boxNo)  . "'"
                   . $disLine .";";

           $dupBox = $this->dbConn->dbGetAll($sql);

           return $dupBox;
      }
// *************************************************************************************************************************
      public function fetchPrincipal($prinId) {
      	   $sql = "SELECT *
                   FROM principal p
                   WHERE p.uid = '"  . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "'";

           $prinDet = $this->dbConn->dbGetAll($sql);

           return $prinDet;

      }

// *************************************************************************************************************************
    public function SaveDispatchToTracking($principalId, $depotId, $userId, $cases, $boxlist, $delBy, $disref, $disCom)  {
    	
    	    global $ROOT; global $PHPFOLDER;
    	   
          $lineNo=1;
          
          // Get Principal Store
              
              $sql = "SELECT *
                      FROM .principal_store_master psm
                      WHERE psm.principal_uid = "   . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                      AND   psm.old_account   = '"  . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
                      AND   psm.depot_uid     = "   . mysqli_real_escape_string($this->dbConn->connection, $depotId) . ";";
              //echo $sql;        
              $prinDet = $this->dbConn->dbGetAll($sql);
              
              if(count($prinDet) == 0) {?>
                   <script type='text/javascript'>parent.showMsgBoxError('Bomb out - Dispatch store not set up - DAO001')</script> 
                   <?php
                   die();              	
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
             $sequenceTO->sequenceKey=LITERAL_SEQ_RVLDISPATCH;
             $result=$sequenceDAO->getSequence($sequenceTO,$orderSeqVal);
             
             $disDocNo = $orderSeqVal; 
          
             if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                 return $result;
             }
             
//           echo "here";
             
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
                                                          invoice_number, 
                                                          exclusive_total,
                                                          vat_total,
                                                          discount_reference,
                                                          grv_number,
                                                          claim_number,
                                                          cases,
                                                          invoice_total, 
                                                          source_document_number,
                                                          trip_transporter_uid,
                                                          debrief_comment, 
                                                          captured_by)
                                                         
                                                          VALUES (" . $dmUId . ",  
                                                                  '" . date('Y-m-d')                   . "',                           
                                                                  '" . date('Y-m-d')                   . "',     
                                                                  "  . DST_INVOICED                    . " ,
                                                                  "  . $prinDet[0]['uid']              . " ,
                                                                  ' " . $disref . "'                       ,
                                                                  ''                                       ,
                                                                  0                                        ,
                                                                  0                                        , 
                                                                  ''                                       ,
                                                                  ''                                       ,
                                                                  ''                                       ,
                                                                  " . $cases . "                           , 
                                                                  0                                        ,
                                                                  ''                                       ,
                                                                  "  . $delBy                          . " ,
                                                                  '" . $disCom                         . "',
                                                                  "  . $userId   . ");";       
//                     echo "<pre>";
//                     echo $dhsql;

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
                             VALUES (" .  $dmUId    . ", 
                                     " .  $lineNo   . ",
                                     154670,
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
                     
                     foreach (explode(',',$boxlist) as $box) {
                     	
                     	   if(trim($box) <> "") {                     	
                     	        $sql = "UPDATE .document_rvl_box_detail bd SET bd.dispatched = 'Y',
                                                                      bd.dispatch_document_uid = " .  $dmUId    . "
                                      WHERE bd.box_number = '" .  $box    . "';";
                                  
                             $this->errorTO = $this->dbConn->processPosting($sql,"");
                             if ($this->errorTO->type !=FLAG_ERRORTO_SUCCESS) { 
                     	           return $this->errorTO;
                             }	                
                             $this->dbConn->dbQuery("commit");
                         }    
                     }
                     $this->errorTO->identifier = $dmUId;
                     return $this->errorTO;
    }

// *************************************************************************************************************************
    public function getDispatchDocDetails($docUid) {
    	
          $sql = "SELECT dm.document_number, 
                         psm.deliver_name, 	
                         dvh.store_reference, 
                         rbd.box_number, 
                         dvh.value
                  FROM .document_rvl_box_detail rbd
                  LEFT JOIN .document_rvl_box_header dvh ON dvh.uid = rbd.document_rvl_box_header_uid 
                  LEFT JOIN .document_master dm ON dm.uid = dvh.document_master_uid
                  LEFT JOIN .document_header dh ON dh.document_master_uid = dm.uid
                  LEFT JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
                  WHERE rbd.dispatch_document_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $docUid) . ";";

           $disPat = $this->dbConn->dbGetAll($sql);

           return $disPat; 	
    }

// *************************************************************************************************************************
   public function selectUserWarehouse($uId, $prin, $currWh) {
   	   
   	   if($currWh == '') {
   	      $cr = "";
   	   } else {
   	      $cr = " AND upd.depot_id = " . mysqli_real_escape_string($this->dbConn->connection, $currWh) ;
   	   }

       $sql = "SELECT d.uid AS 'WhUid',
                      d.name AS 'Warehouse',
                      d.pallet_depot,
                      d.pallet_principal
               FROM " . iDATABASE . ".user_principal_depot upd
               LEFT JOIN depot d ON d.uid = upd.depot_id 
               WHERE upd.user_id = " . mysqli_real_escape_string($this->dbConn->connection, $uId)  . "
               AND   upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $prin)
               . $cr ;
               
       $whDetails = $this->dbConn->dbGetAll($sql);

       return $whDetails;
  }
//***************************************************************************************************************************************************************************  



}  
?>
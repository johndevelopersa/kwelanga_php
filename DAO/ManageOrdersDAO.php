<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class ManageOrdersDAO {
	private $dbConn;

   function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
   }
// **************************************************************************************************************************
   public function getUseRepUid($userUId) {

          $sql="select psr.uid
                from .principal_sales_representative psr
                where psr.capture_user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userUId)."';";

          return $this->dbConn->dbGetAll($sql);
   }
// **************************************************************************************************************************
   public function deleteExistingUserStores($userUId) {

          $sql =  "delete from user_principal_store 
                   where  user_principal_store.user_uid in ('".mysqli_real_escape_string($this->dbConn->connection, $userUId)."');";

          $this->errorTO = $this->dbConn->processPosting($sql,"");
          
          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
              $this->errorTO->description="Existing Store Delete Failed : ".$this->errorTO->description;
              return $this->errorTO;       	                  
          }           
          
          $this->dbConn->dbQuery("commit"); 
          
	        return $this->errorTO->type;
   }
// **************************************************************************************************************************
   public function loadNewUserStores($principalId, $userUId, $repId) {
   	
          $sql =  "insert into user_principal_store 
          
                           (user_principal_store.user_uid, 
                            user_principal_store.principal_store_uid)
                                                     
                            (select '".mysqli_real_escape_string($this->dbConn->connection, $userUId)."', 
                                    psm.uid
                             from .principal_store_master psm
                             where psm.principal_uid in ('".mysqli_real_escape_string($this->dbConn->connection, $principalId)."')
                             and   psm.`status` = 'A'
                             and   psm.principal_sales_representative_uid in ('".mysqli_real_escape_string($this->dbConn->connection, $repId)."'));";  

          $this->errorTO = $this->dbConn->processPosting($sql,"");
          
          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
              /* Debug  echo $sql; */
              return $this->errorTO->type;
          }           

          $this->dbConn->dbQuery("commit"); 
          
	        return $this->errorTO;   	
   }
// **************************************************************************************************************************
   public function deleteExistingUserChains($userUId) {

          $sql =  "delete from user_principal_chain
                   where  user_principal_chain.user_uid in ('".mysqli_real_escape_string($this->dbConn->connection, $userUId)."');";

          $this->errorTO = $this->dbConn->processPosting($sql,"");
          
          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
              $this->errorTO->description="Existing Chain Delete Failed : ".$this->errorTO->description;
              return $this->errorTO;       	                  
          }           
          
          $this->dbConn->dbQuery("commit"); 
          
	        return $this->errorTO->type;   	
   	
   	}
// **************************************************************************************************************************
   public function loadNewUserChains($principalId, $userUId, $repId) {
   	
          $sql =  "insert into user_principal_chain 
                           (user_principal_chain.principal_chain_uid, 
                            user_principal_chain.user_uid) 
                                                     
                            (select distinct(psm.principal_chain_uid) , 
                                             '".mysqli_real_escape_string($this->dbConn->connection, $userUId)."'
                             from .principal_store_master psm
                             where psm.principal_uid in ('".mysqli_real_escape_string($this->dbConn->connection, $principalId)."')
                             and   psm.`status` = 'A'
                             and   psm.principal_sales_representative_uid in ('".mysqli_real_escape_string($this->dbConn->connection, $repId)."'));";  

          $this->errorTO = $this->dbConn->processPosting($sql,"");
          
          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
              /* Debug */ echo $sql; 
              return $this->errorTO;
          }           

          $this->dbConn->dbQuery("commit"); 
          
	        return $this->errorTO;   	
   }
// **************************************************************************************************************************  
   public function getuserWarehouses($principalId, $userUId, $warehouseID) {
   	
          $sql =  "select d.uid, d.name
                   from user_principal_depot upd,
                        depot d
                   where upd.depot_id = d.uid
                   and   upd.user_id  = '".mysqli_real_escape_string($this->dbConn->connection, $userUId)."'
                   and   upd.principal_id = ('".mysqli_real_escape_string($this->dbConn->connection, $principalId)."')
                   and   upd.depot_id <> ('".mysqli_real_escape_string($this->dbConn->connection, $warehouseID)."')";  

          return $this->dbConn->dbGetAll($sql);  	
   }
// **************************************************************************************************************************  
   public function updateOrderWarehouse($documentUid, $newDep) {
   	
          $sql =  "update document_master dm set dm.depot_uid = ".mysqli_real_escape_string($this->dbConn->connection, $newDep)."   
                   where dm.uid = ".mysqli_real_escape_string($this->dbConn->connection, $documentUid). " ;";  
                   
          $this->errorTO = $this->dbConn->processPosting($sql,"");
          
          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
              /* Debug  echo $sql; */
              return $this->errorTO;
          }           

          $this->dbConn->dbQuery("commit"); 
          
	        return $this->errorTO;   	
   }
// **************************************************************************************************************************  
   public function updateStoreWarehouse($ssUid, $newDep) {
   	
          $ssql =  "update principal_store_master psm  set psm.depot_uid = '".mysqli_real_escape_string($this->dbConn->connection, $newDep)."' 
                    where psm.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $ssUid) . "';";

          $this->errorTO = $this->dbConn->processPosting($ssql,"");
          
          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
              /* Debug  echo $ssql;*/ 
              return $this->errorTO;
          }           

          $this->dbConn->dbQuery("commit"); 
          
	        return $this->errorTO;   	
   }
// **************************************************************************************************************************  
   public function getListOfDocumentsToPrint($principalId, $docNo, $storePart, $interval, $docType) {
   	
         if (trim($docNo) <> '' ) {
              $docNoParm = "and dm.document_number like '%". $docNo ."%' " ;
         } else {
              $docNoParm = "";
         } 	
   	
         if (trim($storePart) <> '' ) {
              $storePartParm = "and psm.deliver_name like '%". $storePart ."%' ";
         } else {
              $storePartParm = "";
         } 	   	
   	
          $ssql =  "select dm.uid,
                           dm.document_number, 
                           d.name as 'Warehouse',
                           dh.invoice_date,
                           s.uid as 'StatusUid', 
                           s.description as 'Status',
                           psm.deliver_name,
                           dt.uid as 'TypeUid',
                           dt.description as 'Type',
                           dh.cases,
                           dh.exclusive_total
                    from document_master dm, 
                         document_header dh,
                         principal_store_master psm,
                         `status` s, 
                         document_type dt,
                         depot d
                    where dm.uid = dh.document_master_uid
                    and   dh.principal_store_uid = psm.uid
                    and   dh.document_status_uid = s.uid
                    and   dm.document_type_uid = dt.uid
                    and   dm.depot_uid = d.uid
                    and   dm.principal_uid = '". mysqli_real_escape_string($this->dbConn->connection, $principalId)."'  
                    and   dm.document_type_uid in (" . $docType . ")  "
                    . $docNoParm . "  "  
                    . $storePartParm . "  
                    and   dh.invoice_date between curdate() - interval ". mysqli_real_escape_string($this->dbConn->connection, $interval). " day and curdate();";

// echo $ssql;

        return $this->dbConn->dbGetAll($ssql);
          
   }
// **************************************************************************************************************************  
  public function getListOfChains($principalList) {
 	  
      $sql = "select pcm.uid,
                     pcm.description
              from .principal_chain_master pcm
              where pcm.principal_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $principalList) . ")";
              
              
      $aPW = $this->dbConn->dbGetAll($sql);
      return $aPW;
  }
  // **************************************************************************************************************************************************** 
  public function getListOfGDSDocumentsToPrint($principalId, $userUId, $docNo ,$postChainName, $Printed, $invDate) {

         if (trim($docNo) <> '' ) {
              $docNoParm = "and dm.document_number like '%". $docNo ."%' " ;
         } else {
              $docNoParm = "";
         } 	
   	
         if (trim($postChainName) == '2726' ) {
              $ChainNameParm = "and dh.captured_by like '%RICH%' ";
              $dStatus       = "and   dh.document_status_uid = " . DST_INVOICED ;
         } else {
              $ChainNameParm = "and dh.captured_by not like '%RICH%' ";
              $dStatus       = "and   dh.document_status_uid = " . DST_UNACCEPTED ;
         } 	   	

         if (trim($Printed) == '2' ) {
              $noCopies = "and   dh.copies = 0" ;
         } else {
              $noCopies = "and   dh.copies > 0 and dh.invoice_date = '" . mysqli_real_escape_string($this->dbConn->connection, $invDate) . "' " ;
         } 	
      $sql = "select dm.uid,
                     dm.document_number, 
                     d.name as 'Warehouse',
                     dh.invoice_date,
                     s.uid as 'StatusUid', 
                     s.description as 'Status',
                     dh.copies,
                     psm.deliver_name,
                     dt.uid as 'TypeUid',
                     dt.description as 'Type',
                     dh.cases,
                     dh.exclusive_total,
                     dm.client_document_number, 
                     dh.captured_by
              from document_master dm, 
                   document_header dh,
                   principal_store_master psm,
                   `status` s, 
                   document_type dt,
                   depot d,
                   user_principal_depot upd
              where dm.uid = dh.document_master_uid
              and   dh.principal_store_uid = psm.uid
              and   dh.document_status_uid = s.uid
              and   dm.document_type_uid = dt.uid
              and   dm.depot_uid = d.uid
              and   dm.principal_uid = '". mysqli_real_escape_string($this->dbConn->connection, $principalId)."'  
              and   dm.document_type_uid in (" .DT_ORDINV ."," . DT_ORDINV_ZERO_PRICE . ")
              and   upd.user_id = '". mysqli_real_escape_string($this->dbConn->connection, $userUId)."' 
              and   upd.depot_id = dm.depot_uid
              and   upd.principal_id = dm.principal_uid  " 
              . $ChainNameParm . "  "
              . $dStatus . "  "
              . $noCopies  
              . $docNoParm . "  " ; 

        return $this->dbConn->dbGetAll($sql);
  }
  // **************************************************************************************************************************************************** 
  public function getDocumentToUpdate($docUid) {
  	
    $sql = "select p.uid as 'Principal',
                   dm.document_number,
                   dm.order_sequence_no,
                   psm.deliver_name, 
                   o.delivery_instructions,
                   dh.customer_order_number,
                   dm.uid,
                   dh.document_status_uid,
                   dh.grv_number,
                   dh.claim_number, 
                   dh.off_invoice_discount, 
                   dh.off_invoice_discount_type,                   
                   s.description as 'Status',
                   dm.depot_uid,
                   d.name as 'Depot',
                   psm.uid as 'StoreUid' 
           from document_master dm, 
                orders o, 
                principal p,
                document_header dh, 
                principal_store_master psm, 
                depot d,
                `status` s
           where dm.uid = dh.document_master_uid 
           and dm.order_sequence_no = o.order_sequence_no 
           and dh.principal_store_uid = psm.uid
           and dm.principal_uid = p.uid
           and dm.depot_uid = d.uid
           AND s.uid = dh.document_status_uid
           and dm.uid = " . trim(mysqli_real_escape_string($this->dbConn->connection, $docUid)) . ";";
        
    $mfDDU = $this->dbConn->dbGetAll($sql);

    return $mfDDU;
  }
  // **************************************************************************************************************************************************** 
   public function CancelOrder($documentUid) {
   	
   	   $sql = "UPDATE document_master dm, .document_header dh, .document_detail dd set dh.document_status_uid = '47',
                                                                                       dh.invoice_date = curdate(),
                                                                                       dh.cases           = 0,
                                                                                       dh.exclusive_total = 0,
                                                                                       dh.vat_total       = 0,
                                                                                       dh.invoice_total   = 0,
                                                                                       dd.delivered_qty   = 0,
                                                                                       dd.extended_price  = 0,
                                                                                       dd.vat_amount      = 0,
                                                                                       dd.total           = 0,
                                                                                       dh.pod_reason_uid  = 23
               WHERE dm.uid = dh.document_master_uid
               AND dm.uid = dd.document_master_uid
               AND dm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $documentUid) ;
   	
               $this->errorTO = $this->dbConn->processPosting($sql,"");

               if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                        $this->errorTO->description="Un Cancel Details Failed  : ".$this->errorTO->description;
                        return $this->errorTO;       	                  
                } else {
                        $this->dbConn->dbQuery("commit");                   	                 
                        return $this->errorTO;      
                } 
   }  
  
  // **************************************************************************************************************************************************** 
   public function unCancelOrder($documentUid) { 
   	
           $sql = "UPDATE document_detail dd  SET  dd.document_qty   = dd.ordered_qty,                                                  
                                                   dd.extended_price = dd.ordered_qty * dd.net_price,                                 
                                                   dd.vat_amount     = dd.ordered_qty * dd.net_price * dd.vat_rate / 100,                             
                                                   dd.total          = (dd.ordered_qty * dd.net_price) + (dd.ordered_qty * dd.net_price * dd.vat_rate / 100)                                 
                   WHERE dd.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $documentUid) ;
                  
                   $this->errorTO = $this->dbConn->processPosting($sql,"");
          
                   if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                       $this->errorTO->description="Un Cancel Details Failed  : ".$this->errorTO->description;
                       return $this->errorTO;       	                  
                   } else {
                   	      $this->dbConn->dbQuery("commit");
                          $sql = "UPDATE document_header dh SET dh.document_status_uid = " . DST_ACCEPTED . ",
                                                                dh.invoice_date = dh.order_date,
                                                                dh.pod_reason_uid = NULL
                                  WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $documentUid) ;
                   	      
                          $this->errorTO = $this->dbConn->processPosting($sql,"");
                   	      
                          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                                 $this->errorTO->description="Un Cancel Header Failed  : ".$this->errorTO->description;
                                 return $this->errorTO;       	                  
                          } else { 
                                $this->dbConn->dbQuery("commit"); 	
                                $sql = "UPDATE document_header dh SET dh.cases = (SELECT SUM(dd.document_qty)
                                                                                  FROM   document_detail dd
                                                                                  WHERE  dd.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $documentUid) . "
                                                                                  GROUP BY dd.document_master_uid ),
                                                                      dh.exclusive_total = (SELECT SUM(dd.extended_price)
                                                                                            FROM .document_detail dd
                                                                                            WHERE dd.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $documentUid) . "
                                                                                            GROUP BY dd.document_master_uid),
                                                                      dh.vat_total       = (SELECT SUM(dd.vat_amount)
                                                                                            FROM .document_detail dd
                                                                                            WHERE dd.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $documentUid) . "
                                                                                            GROUP BY dd.document_master_uid),
                                                                      dh.invoice_total   = (SELECT SUM(dd.total)
                                                                                            FROM .document_detail dd
                                                                                            WHERE dd.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $documentUid) . "
                                                                                            GROUP BY dd.document_master_uid)
                                       WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $documentUid) . " ;";

                                 $this->errorTO = $this->dbConn->processPosting($sql,"");

                                if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                                     $this->errorTO->description="Un Cancel Details Failed  : ".$this->errorTO->description;
                                     return $this->errorTO;       	                  
                                } else {
                   	                 $this->dbConn->dbQuery("commit");                   	                 
                   	                 return $this->errorTO;      
                                }
                          }                  	
                   }
   }
  // **************************************************************************************************************************************************** 
     public function insertInDocumentlog($docId,
                                         $userID ,
                                         $oldWh,
                                         $newWh, 
                                         $type,
                                         $comments,
                                         $tsUID)   {
                                                       	
            $sql = "INSERT INTO document_log (document_log.document_master_uid,
                          document_log.change_by_user,
                          document_log.change_datetime,
                          document_log.old_value,
                          document_log.change_value,
                          document_log.change_type,
                          document_log.comments,
                          document_log.tripsheet_header_uid)
                    VALUES ("  . mysqli_real_escape_string($this->dbConn->connection, $docId)    . ",
                            "  . mysqli_real_escape_string($this->dbConn->connection, $userID)   . ",
                            NOW(),
                            "  . mysqli_real_escape_string($this->dbConn->connection, $oldWh)    . ",
                            "  . mysqli_real_escape_string($this->dbConn->connection, $newWh)    . ",
                            '" . mysqli_real_escape_string($this->dbConn->connection, $type)     . "',
                            '" . mysqli_real_escape_string($this->dbConn->connection, $comments) . "',
                            " . mysqli_real_escape_string($this->dbConn->connection, $tsUID) . "); ";
                            
            $this->errorTO = $this->dbConn->processPosting($sql,"");
            
            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                     $this->errorTO->description="Document Log Insert Failed  : ".$this->errorTO->description;
                     return $this->errorTO;       	                  
            } else {
                     $this->dbConn->dbQuery("commit");                   	                 
                     return $this->errorTO;      
            }
       }
  // **************************************************************************************************************************************************** 
       public function resetAcceptedStatus($orderUid ,$chgStat, $newAccStat) {
  	
           $sql="UPDATE document_header dh SET dh.document_status_uid = " . $newAccStat . "  
                 WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $orderUid). "; ";

           $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
               $this->errorTO->description="Reset Accepted Status : ".$this->errorTO->description;
               return $this->errorTO;
          }

          return $this->errorTO;

       }
 // **************************************************************************************************************************************************** 
       public function resetDocumentstatusNew($orderUid) {
  	
           $sql="UPDATE document_header dh SET dh.document_status_uid = " . DST_INVOICED . "  
                 WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $orderUid). "; ";

           $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
               $this->errorTO->description="Reset Status : ".$this->errorTO->description;
               return $this->errorTO;
          }

          return $this->errorTO;

       }

  // **************************************************************************************************************************************************** 
       public function updateChangePO($orderUid, $newPO) {
       	
       	    $sql = "UPDATE document_header dh SET dh.customer_order_number = '" . mysqli_real_escape_string($this->dbConn->connection, $newPO). "' 
                    WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $orderUid). "; ";
       	
           $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
               $this->errorTO->description="Reset Status : ".$this->errorTO->description;
               return $this->errorTO;
          }

          return $this->errorTO;
       	
       }
  // **************************************************************************************************************************************************** 
       public function updateChangeGRV($orderUid, $newPO) {
       	
       	    $sql = "UPDATE document_header dh SET dh.grv_number = '" . mysqli_real_escape_string($this->dbConn->connection, $newPO). "' 
                    WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $orderUid). "; ";
       	
           $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
               $this->errorTO->description="Reset Status : ".$this->errorTO->description;
               return $this->errorTO;
          }

          return $this->errorTO;
       	
       }

  // **************************************************************************************************************************************************** 
       public function updateChangeClaim($orderUid, $newPO) {
       	
       	    $sql = "UPDATE document_header dh SET dh.claim_number = '" . mysqli_real_escape_string($this->dbConn->connection, $newPO). "' 
                    WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $orderUid). "; ";
       	
           $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
               $this->errorTO->description="Reset Status : ".$this->errorTO->description;
               return $this->errorTO;
          }

          return $this->errorTO;
       	
       }

  // **************************************************************************************************************************************************** 

       public function updateChangeOid($orderUid, $newDIS, $disType) {
       	
       	    $sql = "UPDATE document_header dh SET dh.off_invoice_discount      = '" . mysqli_real_escape_string($this->dbConn->connection, $newDIS)  . "',
       	                                          dh.off_invoice_discount_type = '" . mysqli_real_escape_string($this->dbConn->connection, $disType) . "'               
                    WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $orderUid). "; ";
       	
           $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
               $this->errorTO->description="Reset Status : ".$this->errorTO->description;
               return $this->errorTO;
          }

          return $this->errorTO;
       	
       }


  // **************************************************************************************************************************************************** 
  public function getInvoiceDetailsToAmendNew($docUid) {
  	
       $sql = "SELECT dm.uid AS 'dUid', 
                      dm.document_number,
                      dm.document_type_uid,
                      dh.principal_store_uid,
                      dh.invoice_date,
                      dh.customer_order_number,
                      dh.document_status_uid,
                      s.description,
                      psm.deliver_name,
                      dd.product_uid,
                      pp.product_code,
                      pp.product_description,
                      dd.ordered_qty,
                      dd.document_qty,
                      dd.delivered_qty,
                      dt.tripsheet_number,
                      dt.i_dispatched,
                      dt.t_dispatched, 
                      se.status,
                      th.tripsheet_number,
                      td.removed_flag,
                      td.t_dispatched,
                      td.i_dispatched,
                      th.verified_for_dispatch,
                      th.tripsheet_date,
                      th.depot_uid
               FROM .document_master dm
               INNER JOIN .document_header dh ON dm.uid = dh.document_master_uid
               INNER JOIN .document_detail dd ON dd.document_master_uid = dm.uid
               INNER JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
               INNER JOIN .principal_product pp ON pp.uid = dd.product_uid
               INNER JOIN .`status` s ON dh.document_status_uid = s.uid 
               
               LEFT  JOIN tripsheet_detail td ON td.document_master_uid = dm.uid 
                                              AND td.removed_flag = 'N'
               LEFT  JOIN tripsheet_header th ON th.uid = td.tripsheet_master_uid 
                                              AND th.depot_uid = dm.depot_uid              
               LEFT JOIN  .document_tripsheet dt ON dm.uid = dt.document_master_uid AND dt.tripsheet_removed_by IS null
               LEFT JOIN  .smart_event se on se.data_uid = dm.uid AND se.type = 'EXT' 
               WHERE dm.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $docUid)  . "';";
//             echo $sql;
              
               $aCuDet = $this->dbConn->dbGetAll($sql);
         
               return $aCuDet;
  }
  // **************************************************************************************************************************************************** 
} 
?>
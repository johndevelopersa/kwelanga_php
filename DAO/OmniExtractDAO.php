<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');


class OmniExtractDAO {
	
	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
}		

// ****************************************************************************************************************************************************

  public function getJobExecutionEntries($jobName, $jobId=false, $orderBy = false) {

		$where = (($jobId===false)?"":" and job_id = '{$jobId}' ");

		$sql="select a.uid as 'jeUid', 
		             script_name, 
		             page_params,
		             a.principal_uid,
                 a.last_run,
		             curdate(),
		             active_status
          from   " . iDATABASE . ".job_execution a
          left join principal p on a.principal_uid = p.uid and p.status = '".FLAG_STATUS_ACTIVE."'
          where  a.name='{$jobName}'
          and (p.uid is not null or a.principal_uid is null)
          ".$where." ";			  
		if($orderBy){
				$sql .= " ORDER BY {$orderBy} 
				LIMIT 1";
		}	
		
//    echo "<br>";
//    echo $sql;
//    echo "<br>";



    return $this->dbConn->dbGetAll($sql);

  }
// ****************************************************************************************************************************************************

  public function jeStatusFlag($jeUID, $jeStat) {
  
     $sql = "UPDATE " . iDATABASE . ".job_execution je SET je.active_status = '"  . mysqli_real_escape_string($this->dbConn->connection, $jeStat) . "', 
                                         je.last_run = NOW()
             WHERE je.uid = " . mysqli_real_escape_string($this->dbConn->connection, $jeUID) . "";  
  
     $this->errorTO = $this->dbConn->processPosting($sql,"");
                
     if($this->errorTO->type == 'S') {
            $this->dbConn->dbQuery("commit"); 
            return $this->errorTO;    	
     } else {
     	     echo $sql;
     	     echo "<br>";
           return $this->errorTO;  ;
     }
} 
// ******************************************************** // ********************************************************************************************

  public function getOrdersForOmni($principalUId, 
                                   $notificationUId, 
                                   $whId, 
                                   $activeDep ) {
       $sql = "SELECT dm.uid AS 'dm_uid',
                      dm.depot_uid AS 'depotUid',
                      dm.document_number,
                      dm.client_document_number,
                      dm.alternate_document_number,
                      dm.processed_date,
                      dh.customer_order_number,
                      dh.invoice_number,
                      dh.data_source,
                      d.name AS 'depot_name',
                      dh.principal_store_uid AS 'psmUid',
                      psm.deliver_name,
                      psm.principal_chain_uid,
                      psm.alt_principal_chain_uid,
                      psm.branch_code,
                      dh.invoice_date,
                      dh.order_date,
                      dh.document_status_uid,
                      dh.due_delivery_date,
                      dh.delivery_date,
                      dh.requested_delivery_date,
                      dh.source_document_number,
                      dh.claim_number,
                      dm.incoming_file,
                      dh.buyer_account_reference,
                      o.delivery_instructions,
                      pp.uid,
                      pp.uid AS 'pp.uid',
                      pp.product_code,
                      pp.alt_code,
                      pp.product_description,
                      dd.uid as 'detailUid',
                      dd.line_no,
                      dd.ordered_qty,
                      dd.document_qty,
                      dd.net_price,
                      dd.discount_value,
                      dd.extended_price,
                      dd.vat_rate,
                      dd.vat_amount,
                      dd.total,
                      se.uid as 'smartUid',
                      se.status,
                      se.type_uid,
                      dh.captured_by,
                      u.username          
               FROM " . iDATABASE . ".document_master dm
               INNER JOIN " . iDATABASE . ".smart_event se ON  dm.uid = se.data_uid 
                                          AND se.type = '" . SE_EXTRACT . "'
                                          AND se.type_uid = " . mysqli_real_escape_string($this->dbConn->connection, $notificationUId) . "
                                          AND se.status IN ('" . FLAG_STATUS_QUEUED . "', '" . FLAG_ERRORTO_ERROR . "')    
               INNER JOIN " . iDATABASE . ".document_header dh ON dh.document_master_uid = dm.uid
               INNER JOIN " . iDATABASE . ".document_detail dd ON dd.document_master_uid = dm.uid  
               INNER JOIN " . iDATABASE . ".principal_store_master psm ON psm.uid = dh.principal_store_uid
               LEFT JOIN  " . iDATABASE . ".users u ON u.uid = dh.captured_by
               INNER JOIN " . iDATABASE . ".depot d ON d.uid = dm.depot_uid
               LEFT  JOIN " . iDATABASE . ".orders o ON o.order_sequence_no = dm.order_sequence_no 
               LEFT JOIN  " . iDATABASE . ".principal_product pp ON pp.uid = dd.product_uid                   
               WHERE dm.principal_uid =  " . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "
               AND   dm.depot_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $activeDep) . ")
               AND   se.created_date > curdate() - interval 30 day
               ORDER BY se.status DESC, se.processed_date ASC ;";
               
//               echo "<pre>";
//               echo $sql;

    return $this->dbConn->dbGetAll($sql);

  }

// ****************************************************************************************************************************************************
public function updateSmartEventDirectly($smartUid, $general1 = "", $general2 = "", $statusFlag = FLAG_STATUS_CLOSED) {

    $this->dbConn->dbQuery("SET time_zone='+2:00'");

    // events remain queued if the notification fails anywhere, so always select using Q status
    $general1 = ($general1!="") ? (trim("'" . mysqli_real_escape_string($this->dbConn->connection, substr($general1, 0, 80)) . "'")) : ('NULL');
    $general2 = ($general2!="") ? (trim("'" . mysqli_real_escape_string($this->dbConn->connection, substr($general2, 0, 80)) . "'")) : ('NULL');

    $sql="UPDATE " . iDATABASE . ".smart_event
          SET  status = '".$statusFlag."',
               general_reference_1 = ".$general1.",
               general_reference_2 = ".$general2.",
               processed_date = NOW(),
               error_count = error_count + 1
          WHERE  uid = ({$smartUid})";
          
//        echo "<br>";
//        echo $sql;
//        echo "<br>";
          
    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to update smart_event in OmniExtractDAO->updateSmartEventDirectly";
       
       // echo "<br>";
       // echo $sql;
       // echo "<br>";
       
    } else {
    	$this->dbConn->dbQuery("commit");
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;

  }

// ****************************************************************************************************************************************************
public function getListofOmniErrors($principalUId, 
                                    $extractUId,
                                    $errCnt) {
	
        $sql = "SELECT dm.document_number, 
                       psm.deliver_name, 
                       dh.order_date, 
                       psm.uid as 'psm_uid',
                       se.uid AS 'seUid',
                       se.error_count, 
                       se.data_uid, 
                       se.status, 
                       se.general_reference_1, 
                       se.general_reference_2,
                       se.error_count,
                       pc.email_addr,
                       p.name as 'Principal'
               FROM " . iDATABASE . ".document_master dm
               INNER JOIN " . iDATABASE . ".document_header dh ON dh.document_master_uid = dm.uid
               INNER JOIN " . iDATABASE . ".principal_store_master psm ON psm.uid = dh.principal_store_uid
               inner JOIN " . iDATABASE . ".smart_event se ON se.type_uid = " . mysqli_real_escape_string($this->dbConn->connection, $extractUId) . " 
                                          AND se.data_uid = dm.uid
               INNER JOIN principal p on p.uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "  
               LEFT JOIN  " . iDATABASE . ".principal_contact pc ON pc.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . " 
                                                AND pc.contact_type_uid = " . CTD_EDI . " 
                                                AND pc.depot_uid IS NULL                           
               WHERE dm.principal_uid =  " . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "
               AND   se.status = 'E'
               AND   se.error_count < " . mysqli_real_escape_string($this->dbConn->connection, $errCnt) . "
               ORDER BY pc.email_addr ";              

 // echo "<br>";
 // echo $sql;
 // echo "<br>";
 
        return $this->dbConn->dbGetAll($sql);
        
        /* " . FLAG_ERRORTO_ERROR . " */

}

// ****************************************************************************************************************************************************
 public function setOmniImportStatus($dmUIdList, $setype, $dhStatus) {

    $sql="UPDATE "     . iDATABASE . ".document_master dm 
          inner JOIN " . iDATABASE . ".smart_event se ON dm.uid = se.data_uid AND se.type_uid = " . mysqli_real_escape_string($this->dbConn->connection,$setype) . " 
          inner JOIN " . iDATABASE . ".document_header dh on dm.uid = dh.document_master_uid  SET dm.merged_date = CURDATE(), 
                                                                                dm.merged_time = CURTIME(), 
                                                                                dm.rwr_file = 'Omni Success',
                                                                                dh.document_status_uid = " . mysqli_real_escape_string($this->dbConn->connection,$dhStatus) . "
          WHERE se.uid in ({$dmUIdList})";
          
//          echo $sql;

    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to Omni Import Update in setOmniImportStatus";
    } else {
    	$this->dbConn->dbQuery("commit");
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;

  }
// ****************************************************************************************************************************************************
  public function setDocumentConfirmationStatus($prinId, $dhStatus, $invStatus) {  
  	
  	if($prinId == 396) {
  		   $capVar = "AND dh.data_source like '%CAPTURE%'";  		
  	}  else {
  		  $capVar = "AND dh.data_source not like '%CAPTURE%'";
  	}
  	
  	if($invStatus == DST_INVOICED) {
  	     $finDate = "dh.invoice_date = '" .  date('Y-m-d') . "'";	
  	} else {
  	     $finDate = "dh.invoice_date = dh.order_date";	
  	}  	

    $sql="update " . iDATABASE . ".document_master dm 
          inner JOIN " . iDATABASE . ".document_header dh on dm.uid = dh.document_master_uid
          inner JOIN " . iDATABASE . ".document_detail dd on dm.uid = dd.document_master_uid  set dd.document_qty = dd.ordered_qty,
                                                                                dh.document_status_uid = " . mysqli_real_escape_string($this->dbConn->connection,$invStatus) . ",
                                                                                " . $finDate . " 
          WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection,$prinId) . "
          " .  $capVar . "
          AND   dh.document_status_uid = " . mysqli_real_escape_string($this->dbConn->connection,$dhStatus) ;

    $this->dbConn->dbQuery($sql);
    
    if (!$this->dbConn->dbQueryResult) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed to Invoice Documents Update in postBIDAO->setDocumentConfirm";
    } else {   	
    	    $this->dbConn->dbQuery("commit");
          $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
          $this->errorTO->description = "Successful";

          $inssql = "INSERT INTO " . iDATABASE . ".smart_event (smart_event.created_date,
                                  smart_event.type,
                                  smart_event.type_uid,
                                  smart_event.status,
                                  smart_event.data_uid)
     
                     SELECT NOW(), 'N', nr.uid, 'Q', dm.uid
                     FROM " . iDATABASE . ".document_master dm
                     INNER JOIN " . iDATABASE . ".document_header dh ON dh.document_master_uid = dm.uid
                     LEFT JOIN  " . iDATABASE . ".notification_recipients nr ON trim(nr.p1) = dm.depot_uid
                                                          AND dh.captured_by in (trim(nr.p4))
                                                          AND nr.principal_uid = dm.principal_uid
                     WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection,$prinId) . "
                     AND   dh.document_status_uid = " . mysqli_real_escape_string($this->dbConn->connection,$invStatus) ."
                     AND   dh.order_date > curdate() - interval 1 day
                     " .  $capVar . "
                     AND NOT EXISTS (SELECT 1 
                                     FROM " . iDATABASE . ".smart_event se
                                     WHERE se.data_uid = dm.uid
                                     AND se.type = 'N'
                                     AND se.type_uid = nr.uid);";
          
            $this->errorTO = $this->dbConn->processPosting($inssql,"");
           
            if($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                     $this->dbConn->dbQuery("commit");
            }
                 
            $seInsert = $this->errorTO->type;
            return $seInsert;      
     }

     return $this->errorTO;

  }
// ************************************************************************************************************************************
   public function updateOmniErrorCount($seUid) {
          
          $sql = "UPDATE " . iDATABASE . ".smart_event se SET se.error_count = se.error_count + 1
                  where se.uid = " . mysqli_real_escape_string($this->dbConn->connection,$seUid) . ";"	;
   	
          $this->dbConn->dbQuery($sql);

          if (!$this->dbConn->dbQueryResult) {
              $this->errorTO->type = FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Failed to Omni Import Update in setOmniImportStatus";
          } else {
              $this->dbConn->dbQuery("commit");
              $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
              $this->errorTO->description = "Successful";
          }

          return $this->errorTO;   	
   	
   	}
// ************************************************************************************************************************************
   public function getActiveStatus() {
  
       $sql = "SELECT j.uid, 
                      j.last_run, 
                      TIMESTAMPDIFF(SECOND, j.last_run, NOW()) AS 'Secs',  
                      j.active_status, 
                      p.name
               FROM " . iDATABASE . ".job_execution j
               LEFT JOIN " . iDATABASE . ".principal p ON p.uid = j.principal_uid
               WHERE j.name = 'OmniImportsNew '
               AND   j.script_name = 'extractPrincipalTransactionsForOmni'
               ORDER BY p.name ;";

        return $this->dbConn->dbGetAll($sql);
    }
// ************************************************************************************************************************************
   public function updateActiveStatus($uidList, $tmU=FALSE) {
   	   if($tmU == TRUE) {
            $updTime = ", j.last_run = NOW()";
       } else {
            $updTime = "";
       }
       
       $sql = "UPDATE " . iDATABASE . ".job_execution j SET j.active_status = 'N'" 
                                          . $updTime . "                                    
               WHERE j.uid IN ( " . mysqli_real_escape_string($this->dbConn->connection,$uidList) . ");";

       $this->dbConn->dbQuery($sql);

       if (!$this->dbConn->dbQueryResult) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Transaction Status not Reset";
       } else {
            $this->dbConn->dbQuery("commit");
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "Successful";
       }

       return $this->errorTO;

   	}
// ************************************************************************************************************************************
   public function prepareBatchlist($prin, $depot) {
   	
       $sql = "UPDATE " . iDATABASE . ".principal_product_available_batch ppv SET `status`='U'
               WHERE  ppv.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection,$prin)  . "
               AND    ppv.depot_uid     = " . mysqli_real_escape_string($this->dbConn->connection,$depot) . ";";
   
       $this->dbConn->dbQuery($sql);

       if (!$this->dbConn->dbQueryResult) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed to update batch Table";
       } else {
            $this->dbConn->dbQuery("commit");
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "Successful";
       }

       return $this->errorTO;
   	
   	
   }
// ************************************************************************************************************************************
   public function getOmniReportExecution($type) {
   	
        $sql = "SELECT *
                FROM " . iDATABASE . ".job_execution je 
                WHERE je.name = '" . mysqli_real_escape_string($this->dbConn->connection,$type) . "';"; 

        return $this->dbConn->dbGetAll($sql);   	
   }
// ************************************************************************************************************************************
   public function insertProductBatches($pUid, 
                                        $dUid,
                                        $pCode,
                                        $batch,
                                        $sDate,
                                        $level) {
                                        	
        echo "<br>";                                	
        echo $batch;
        echo "<br>";                                	
                                        	
        if($batch == "BN-115-24") {
             $sDate = "2024-5-11"	;
        }                                	
        if($batch == "MCG 13/06/23") {
             $sDate = "2024-5-11"	;
        }
        if($batch == "MCG 13/06/2023") {
             $sDate = "2024-5-11"	;
        }        
            
                                        	  
        $sql = "INSERT INTO principal_product_available_batch (principal_product_available_batch.principal_uid,
                                                               principal_product_available_batch.depot_uid,
                                                               principal_product_available_batch.principal_product_uid,
                                                               principal_product_available_batch.product_code,
                                                               principal_product_available_batch.batch,
                                                               principal_product_available_batch.batch_sort,
                                                               principal_product_available_batch.quantity,
                                                               principal_product_available_batch.generated_date_time,
                                                               principal_product_available_batch.`status`)
                (SELECT " . $pUid .",
                        " . $dUid .",
                        pp.uid,
                        '" . $pCode . "',
                        '" . $batch . "',
                        '" . $sDate . "',
                        "  . $level . ",
                        NOW(),
                        'A'
                 FROM principal_product pp 
                 WHERE pp.principal_uid = " . $pUid ."
                 AND pp.product_code = '" . $pCode ."')";

       $this->dbConn->dbQuery($sql);

       if (!$this->dbConn->dbQueryResult) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed to Insert Batch Row";
       } else {
            $this->dbConn->dbQuery("commit");
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "Successful";
       }

       return $this->errorTO;
   	

}
// ************************************************************************************************************************************
   public function getAvailableBatches($prin, $dep, $pUid) {
   	
   	    if($dep == '400') {
   	        $dep = 393;	
   	    }
   	
   	    $sql = "SELECT av.uid AS 'batUid', 
   	                   av.principal_product_uid, 
   	                   av.product_code, 
   	                   av.batch, 
   	                   av.quantity, 
   	                   av.reduced_quantity
                FROM .principal_product_available_batch av
                WHERE av.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection,$prin) . "
                AND   av.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection,$dep) . "
                AND   av.`status` = 'A'
                AND   av.principal_product_uid = " . mysqli_real_escape_string($this->dbConn->connection,$pUid) . "
                ORDER BY av.product_code, av.batch_sort;";
                
        return $this->dbConn->dbGetAll($sql);
   	
   }
// ************************************************************************************************************************************

   public function updateBatchLog($batUid,
                                  $detailUid,
                                  $ppuid,
                                  $batch,
                                  $qty,
                                  $dmUid) {
                                  	
       $sql = "UPDATE principal_product_available_batch pb 
                               SET pb.reduced_quantity = pb.reduced_quantity + " . mysqli_real_escape_string($this->dbConn->connection,$qty) . "
               WHERE pb.uid = " . mysqli_real_escape_string($this->dbConn->connection,$batUid) . ";";

       $this->dbConn->dbQuery($sql);

       if (!$this->dbConn->dbQueryResult) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed to Insert Batch Row";
       } else {
            $this->dbConn->dbQuery("commit");
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "Successful";
       }
       
       $sql = "SELECT *
               FROM principal_product_allocated_batch ab
               WHERE ab.document_detail_uid = "  . mysqli_real_escape_string($this->dbConn->connection,$detailUid) . ";";
                
       $abQuery = $this->dbConn->dbGetAll($sql);        
                
       if(count($abQuery) > 0) {
           $sql = "UPDATE principal_product_allocated_batch ab SET ab.batch    = '"  . mysqli_real_escape_string($this->dbConn->connection,$batch)     . "',
                                                                   ab.quantity =  "  . mysqli_real_escape_string($this->dbConn->connection,$qty)       . "           
                   WHERE ab.document_detail_uid = "  . mysqli_real_escape_string($this->dbConn->connection,$detailUid) . ";";       	
       	
           $this->dbConn->dbQuery($sql);

           if (!$this->dbConn->dbQueryResult) {
               $this->errorTO->type = FLAG_ERRORTO_ERROR;
               $this->errorTO->description = "Failed to Update Batch Row";
           } else {
               $this->dbConn->dbQuery("commit");
               $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
               $this->errorTO->description = "Successful";
           }

           return $this->errorTO;   
               	       	
       }  else {
       	
           $sql = "INSERT INTO principal_product_allocated_batch (principal_product_allocated_batch.document_detail_uid,
                                                                  principal_product_allocated_batch.document_master_uid,
                                                                  principal_product_allocated_batch.principal_product_uid,
                                                                  principal_product_allocated_batch.batch,
                                                                  principal_product_allocated_batch.quantity)
                   VALUES ("  . mysqli_real_escape_string($this->dbConn->connection,$detailUid) . " , 
                           "  . mysqli_real_escape_string($this->dbConn->connection,$dmUid)     . " , 
                           "  . mysqli_real_escape_string($this->dbConn->connection,$ppuid)     . " , 
                          '"  . mysqli_real_escape_string($this->dbConn->connection,$batch)     . "', 
                           "  . mysqli_real_escape_string($this->dbConn->connection,$qty)       . ");";

          $this->dbConn->dbQuery($sql);

          if (!$this->dbConn->dbQueryResult) {
              $this->errorTO->type = FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Failed to Insert Batch Row";
          } else {
              $this->dbConn->dbQuery("commit");
              $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
              $this->errorTO->description = "Successful";
          }

          return $this->errorTO; 
          
       }        
                       

   }
// ************************************************************************************************************************************
   public function updateOmniInvoiceStatus($prinUid,
                                           $iDate,
                                           $iOrderNumber,
                                           $iReference,
                                           $iQuantity,
                                           $iValueExcl,
                                           $iLineNumber,
                                           $iStockCode) {
          $sql = "UPDATE document_master dm
                  INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid
                  INNER JOIN .document_detail dd ON dd.document_master_uid = dm.uid
                  INNER JOIN .principal_product pp ON pp.uid = dd.product_uid
                  INNER JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
                                        SET dh.document_status_uid = " . DST_INVOICED . ", 
                                            dh.invoice_number      = '"  . mysqli_real_escape_string($this->dbConn->connection,$iReference)   . "', 
                                            dd.document_qty        = '"  . mysqli_real_escape_string($this->dbConn->connection,$iQuantity)    . "',
                                            dd.extended_price      = '"  . mysqli_real_escape_string($this->dbConn->connection,$iValueExcl)   . "',
                                            psm.retailer           = if(dh.captured_by = 'CHECKERS', 2, if(dh.captured_by = 'PNP',1,NULL)),
                                            dh.invoice_date        = '"  . mysqli_real_escape_string($this->dbConn->connection,$iDate)        . "'
                  WHERE dm.principal_uid   = "   . mysqli_real_escape_string($this->dbConn->connection,$prinUid)      . "
                  AND   dm.document_number = LPAD('"  . mysqli_real_escape_string($this->dbConn->connection,$iOrderNumber) . "',8,'0')
                  AND   trim(pp.product_code) = trim('"  . mysqli_real_escape_string($this->dbConn->connection,$iStockCode)   . "')
                  AND   dd.client_line_no / "  . mysqli_real_escape_string($this->dbConn->connection,$iLineNumber)  . " = 10 ;";
          $this->dbConn->dbQuery($sql);

          if (!$this->dbConn->dbQueryResult) {
               $this->errorTO->type = FLAG_ERRORTO_ERROR;
               $this->errorTO->description = "Failed to Insert Batch Row";
          } else {
               $this->dbConn->dbQuery("commit");
               $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
               $this->errorTO->description = "Successful";
          }

          return $this->errorTO;
   }
// ************************************************************************************************************************************
   public function checkUpdateStatus($prinUid, $iOrderNumber) {

          $sql = "SELECT dm.document_number, dh.document_status_uid
                  FROM  document_master dm
                  INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid
                  WHERE dm.principal_uid   = "   . mysqli_real_escape_string($this->dbConn->connection,$prinUid)      . "
                  AND   dm.document_number = LPAD('"  . mysqli_real_escape_string($this->dbConn->connection,$iOrderNumber) . "',8,'0')";
          
          return $this->dbConn->dbGetAll($sql);
          
   }      
// ************************************************************************************************************************************
 public function setOmniInvoiceStatus($dmUIdList, $setype, $invNo) {

    $sql="UPDATE "     . iDATABASE . ".document_master dm 
          inner JOIN " . iDATABASE . ".smart_event se ON dm.uid = se.data_uid AND se.type_uid = " . mysqli_real_escape_string($this->dbConn->connection,$setype) . " 
          inner JOIN " . iDATABASE . ".document_header dh on dm.uid = dh.document_master_uid  SET dm.merged_date = CURDATE(), 
                                                                                                  dm.merged_time = CURTIME(), 
                                                                                                  dm.rwr_file = 'Omni Success',
                                                                                                  dh.invoice_number = '" . mysqli_real_escape_string($this->dbConn->connection,$invNo) . "'
          WHERE se.uid in ({$dmUIdList})";
          

    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to Omni Import Update in setOmniInvoiceStatus";
    } else {
    	$this->dbConn->dbQuery("commit");
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;

  }
// ****************************************************************************************************************************************************
      function updateGDStock($prin, $whCode, $stckCode, $level, $avail) {

              $sql = "SELECT s.uid AS 'stockUid',
                             s.depot_id AS 'depotId'
                      FROM " . iDATABASE . ".stock s
                      WHERE s.principal_id = "  . mysqli_real_escape_string($this->dbConn->connection,$prin) . "
                      AND   s.depot_id = (SELECT sfd.entity_uid
                                          FROM " . iDATABASE . ".special_field_details sfd 
                                          WHERE sfd.field_uid = (SELECT sff.uid
                                                                 FROM " . iDATABASE . ".special_field_fields sff
                                                                 WHERE sff.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection,$prin) . "
                                                                 AND   sff.`type` = 'D')
                                                                 AND   sfd.value = '"  . mysqli_real_escape_string($this->dbConn->connection,$whCode) . "')
                                          AND s.stock_item = '"  . mysqli_real_escape_string($this->dbConn->connection,$stckCode) . "'";

             $stockRec = $this->dbConn->dbGetAll($sql);
             
             if(count($stockRec) == 1) { 
             	
             	      $depUid = $stockRec[0]['depotId'];
             	
             	      $sql = "UPDATE " . iDATABASE . ".stock s SET s.opening = "  . mysqli_real_escape_string($this->dbConn->connection,$level) . ",
             	                                                   s.closing = "  . mysqli_real_escape_string($this->dbConn->connection,$level) . ",
                                                                 s.`allocations` = 0,
                                                                 s.available = "  . mysqli_real_escape_string($this->dbConn->connection,$avail) . "
                            WHERE s.uid = "  . mysqli_real_escape_string($this->dbConn->connection,$stockRec[0]['stockUid']) ;	
                    
                    $this->dbConn->dbQuery($sql);

                    if (!$this->dbConn->dbQueryResult) {
                         $this->errorTO->type = FLAG_ERRORTO_ERROR;
                         $this->errorTO->description = "Failed to update Stock";
                    } else {
                         $this->dbConn->dbQuery("commit");
                         $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                         $this->errorTO->description = "Stock Record Update Successful";
                    }
             
             } else {
             	      
                   $sql = "SELECT pp.uid AS 'ppUid',
             	                     pp.product_code,
             	                     pp.product_description
                            FROM " . iDATABASE . ".principal_product pp
                            WHERE pp.principal_uid = 380
                            AND   pp.product_code ='"  . mysqli_real_escape_string($this->dbConn->connection,$stckCode) . "';";
             	
                    $prodRec = $this->dbConn->dbGetAll($sql);
             
                    if(count($prodRec) == 1) {
                    	
                   $sql = "SELECT sfd.entity_uid
                           FROM " . iDATABASE . ".special_field_details sfd 
                           WHERE sfd.field_uid = (SELECT sff.uid
                                                  FROM " . iDATABASE . ".special_field_fields sff
                                                  WHERE sff.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection,$prin) . "
                                                  AND   sff.`type` = 'D')
                                                  AND   sfd.value = '"  . mysqli_real_escape_string($this->dbConn->connection,$whCode) . "'";
                    	
                           $depRec = $this->dbConn->dbGetAll($sql);	
                    	     
                    	     $depotId = $depRec[0]['entity_uid'];
                    	
                    	     $sql = "INSERT IGNORE INTO " . iDATABASE . ".stock (stock.principal_id,
                                                                               stock.depot_id,
                                                                               stock.principal_product_uid,
                                                                               stock.stock_item,
                                                                               stock.stock_descrip,
                                                                               stock.opening,
                                                                               stock.closing,
                                                                               stock.`allocations`,
                                                                               stock.available)
                                   VALUES ("  . mysqli_real_escape_string($this->dbConn->connection,$prin)                              . ", 
                                           "  . mysqli_real_escape_string($this->dbConn->connection,$depotId)                           . ", 
                                           "  . mysqli_real_escape_string($this->dbConn->connection,$prodRec[0]['ppUid'])               . ",
                                          '"  . mysqli_real_escape_string($this->dbConn->connection,$prodRec[0]['product_code'])        . "',
                                          '"  . mysqli_real_escape_string($this->dbConn->connection,$prodRec[0]['product_description']) . "', 
                                           "  . mysqli_real_escape_string($this->dbConn->connection,$level)  . ",
                                           "  . mysqli_real_escape_string($this->dbConn->connection,$level)  . ",
                                            0,
                                           "  . mysqli_real_escape_string($this->dbConn->connection,$avail)  . ");";

                            $this->dbConn->dbQuery($sql);
                    	
                            if (!$this->dbConn->dbQueryResult) {
                                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                                    $this->errorTO->description = "Failed to update Stock";
                            } else {
                                    $this->dbConn->dbQuery("commit");
                                    $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                                    $this->errorTO->description = "Stock Record Insert Successful";
                            }
                    }        
             }            	
             return $this->errorTO;
      }
// ****************************************************************************************************************************************************
      function getCreditNoteProductBatch($prin, $sourceDoc) {
   	
   	    $sql = "SELECT a.uid AS 'batUid', 
   	                   dd.product_uid, 
   	                   a.batch, 
   	                   dd.document_qty as 'quantity'
                FROM " . iDATABASE . ".principal_product_allocated_batch a
                INNER JOIN " . iDATABASE . ".document_detail dd ON dd.uid = a.document_detail_uid
                INNER JOIN " . iDATABASE . ".document_master dm ON dd.document_master_uid = dm.uid
                WHERE dm.document_number = '" . mysqli_real_escape_string($this->dbConn->connection,$sourceDoc) . "'
                AND   dm.principal_uid   = "  . mysqli_real_escape_string($this->dbConn->connection,$prin) . "
                AND   dm.document_type_uid = " . DT_ORDINV . "
                GROUP BY a.document_detail_uid;" ;   	                   
   	                   
 
                //echo "<pre>";
                //echo $sql;
                //echo "ee<BR>";
                     
 return $this->dbConn->dbGetAll($sql); 

      }      	
// ****************************************************************************************************************************************************

}  
?>  
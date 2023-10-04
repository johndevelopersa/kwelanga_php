<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class deBriefingDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  // **************************************************************************************************************************************************** 

  public function getReturnDocument($principal, $docNum) {
  	
  	     $sql = "SELECT p.uid AS 'prinUid',
                        p.name AS 'principal',
                        dm.document_number,
                        dm.uid AS 'dmUid',
                        psm.deliver_name as 'store',
                        d.uid AS 'depotUid',
                        d.name AS 'depot',
                        d.redelivery_warehouse,
                        dh.invoice_date,
                        s.description AS 'status',
                        th.tripsheet_number,
                        th.uid AS 'thUid',
                        t.name AS 'transporter',
                        dh.document_status_uid
                 FROM " . iDATABASE . ".document_master dm
                 INNER JOIN " . iDATABASE . ".document_header dh ON dm.uid = dh.document_master_uid
                 INNER JOIN " . iDATABASE . ".principal p ON p.uid = dm.principal_uid
                 INNER JOIN " . iDATABASE . ".depot d ON d.uid = dm.depot_uid
                 INNER JOIN " . iDATABASE . ".principal_store_master psm ON psm.uid = dh.principal_store_uid
                 INNER JOIN " . iDATABASE . ".`status` s ON s.uid = dh.document_status_uid
                 LEFT  JOIN " . iDATABASE . ".tripsheet_detail td ON td.document_master_uid = dm.uid AND td.removed_flag = 'N'
                 LEFT  JOIN " . iDATABASE . ".tripsheet_header th ON th.uid = td.tripsheet_master_uid
                 LEFT  JOIN " . iDATABASE . ".transporter t ON t.uid = th.transporter_id
                 WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . "
                 AND   dm.document_number = '" . mysqli_real_escape_string($this->dbConn->connection, $docNum) . "'
                 AND   th.uid IS NOT NULL";
                 
                 $rDoc = $this->dbConn->dbGetAll($sql);
                 
                 return $rDoc;
  	}
  // **************************************************************************************************************************************************** 
  public function getReasonList() {
  	
         $sql = "SELECT rc.uid AS 'rcUid',
                        rc.description AS 'reason'
                 FROM " . iDATABASE . ".reason_code rc
                 WHERE rc.associated_status_uid IN (" . DST_DIRTY_POD . ")";
  	    
         $rcList = $this->dbConn->dbGetAll($sql);
                 
         return $rcList;  	    
  }
  // **************************************************************************************************************************************************** 
 
     public function removeReDeliveryFromTripSheet($list, 
                                                   $postreason, 
                                                   $userUId, 
                                                   $tsUid,
                                                   $depotId,
                                                   $tsNum) {
              $sql = "UPDATE " . iDATABASE . ".document_header dh set dh.on_a_tripsheet_number = 0,
                                                    dh.tripsheet_number             = null,
                                                    dh.tripsheet_date               = '0000-00-00',
                                                    dh.trip_transporter_uid         = null,
                                                    dh.tripsheet_created_by         = null
                      WHERE dh.document_master_uid =" . mysqli_real_escape_string($this->dbConn->connection, $list) . ";";
               
              $this->errorTO = $this->dbConn->processPosting($sql,"");
              if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                    echo "<pre>";
                    echo $sql;
                    print_r($this->errorTO);                  
                    $this->errorTO->description="Error removing document (pt001) : ".$this->errorTO->description;
                    return $this->errorTO;
              } else {
              	
                    $sql = "UPDATE " . iDATABASE . ".document_tripsheet ds set ds.tripsheet_removed_by = " . mysqli_real_escape_string($this->dbConn->connection, $userUId) . ",
                                                      ds.tripsheet_removed_date      = '" . date("Y-m-d") . "',
                                                      ds.reason                      = " . mysqli_real_escape_string($this->dbConn->connection, $postreason) . "                                       
                            WHERE ds.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $list) . ";";
                    $this->errorTO = $this->dbConn->processPosting($sql,"");

                    if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                         echo "<pre>";
                         echo $sql;
                         $this->errorTO->description="Error removing document (pt002) : ".$this->errorTO->description;
                         return $this->errorTO;
                    } else {
                    	
                         $sql = "UPDATE     " . iDATABASE . ".tripsheet_detail td
                                 INNER JOIN " . iDATABASE . ".tripsheet_header th ON th.uid = td.tripsheet_master_uid 
                                                               AND th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tsNum) . "'
                                                               SET td.removed_from_tripsheet = " . mysqli_real_escape_string($this->dbConn->connection, $userUId) . ",
                                                               td.removed_date           = '" . date("Y-m-d") . "',
                                                               td.removed_reason         = " . mysqli_real_escape_string($this->dbConn->connection, $postreason) . " ,                                      
                                                               td.removed_flag = 'Y'
                                 WHERE td.document_master_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $list)     . "
                                 AND   th.depot_uid           = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId)  . "'
                                 AND   th.uid                 = '" . mysqli_real_escape_string($this->dbConn->connection, $tsUid)    . "' ;";
      
                         $this->errorTO = $this->dbConn->processPosting($sql,"");

                         if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                                echo "<pre>";
                                echo $sql;
                                $this->errorTO->description="Error removing document (pt003) : ".$this->errorTO->description;
                                return $this->errorTO;
                         } else {
                         	      
                         	      $sql = "UPDATE " . iDATABASE . ".tripsheet_control tc SET `removed` = 'Y',
  	                                                                                      `date_time` = NOW()
                                        WHERE  tc.document_master_uid =  "  . mysqli_real_escape_string($this->dbConn->connection, $list)  . "
                                        AND    tc.tripsheet_number    =  '"  . mysqli_real_escape_string($this->dbConn->connection, $tsNum) . "';";
  	        
                                $this->errorTO = $this->dbConn->processPosting($sql,"");

                                if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                                       echo "<pre>";
                                       echo $sql;
                                       $this->errorTO->description="Error Updating document (pt010) : ".$this->errorTO->description;
                                       return $this->errorTO;
                                } else {
                                       $this->dbConn->dbQuery("commit");
                                }
                         }  
                    }            	
              }
              return $this->errorTO;
      }  

//***************************************************************************************************************************************************************************  
     public function getReDeliverDetail($dmUid, $userUId) {
     	
              $sql = "SELECT dm.principal_uid AS 'prinUid',
                             dm.document_number,
                             dm.uid AS 'dmUid',
                             d.uid AS 'depotUid',
                             d.redelivery_warehouse,
                             d.name AS 'depot',
                             d.redelivery_warehouse,
                             dd.uid AS 'ddUid',
                             dd.product_uid,
                             dd.document_qty,
                             " .$userUId ." AS 'userUid'
                      FROM " . iDATABASE . ".document_master dm
                      INNER JOIN " . iDATABASE . ".document_header dh ON dm.uid = dh.document_master_uid
                      INNER JOIN " . iDATABASE . ".document_detail dd ON dm.uid = dd.document_master_uid
                      INNER JOIN " . iDATABASE . ".depot d ON d.uid = dm.depot_uid
                      WHERE dm.uid = "  . mysqli_real_escape_string($this->dbConn->connection, $dmUid)  . ";";
     	
              $rdProd = $this->dbConn->dbGetAll($sql);
                 
              return $rdProd;  	         	
     }
//***************************************************************************************************************************************************************************  
     public function setReDeliverStatus($dmUid,
                                        $userUid,
                                        $rdWh,
                                        $reason,
                                        $tsNum) {
     	
              $sql = "UPDATE " . iDATABASE . ".document_header dh SET dh.document_status_uid       = " . DST_RE_DELIVERY . ",
                                                                      dh.buyer_document_status_uid = " . DST_RE_DELIVERY . "
                      WHERE dh.document_master_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $dmUid);
                      
              $this->errorTO = $this->dbConn->processPosting($sql,"");

              if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                      echo "<pre>";
                      echo $sql;
                      $this->errorTO->description="Error Updating document (pt010) : ".$this->errorTO->description;
                      return $this->errorTO;
              } else {
              	   
                   $sql = "INSERT INTO " . iDATABASE . ".document_redelivery_log (document_redelivery_log.document_master_uid,
                                       " . iDATABASE . ".document_redelivery_log.redelivery_user_uid,
                                       " . iDATABASE . ".document_redelivery_log.redeliver_warehouse_uid,
                                       " . iDATABASE . ".document_redelivery_log.created_datetime,
                                       " . iDATABASE . ".document_redelivery_log.reason_uid,
                                       " . iDATABASE . ".document_redelivery_log.tripsheet_number)
                           VALUES ("  . mysqli_real_escape_string($this->dbConn->connection, $dmUid)    . ", 
                                   "  . mysqli_real_escape_string($this->dbConn->connection, $userUid)  . ",
                                   "  . mysqli_real_escape_string($this->dbConn->connection, $rdWh)     . ",
                                   NOW(),
                                   "  . mysqli_real_escape_string($this->dbConn->connection, $reason)   . ",
                                  '"  . mysqli_real_escape_string($this->dbConn->connection, $tsNum)    . "')";
              	
                   $this->errorTO = $this->dbConn->processPosting($sql,"");

                   if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                         echo "<pre>";
                         echo $sql;
                         $this->errorTO->description="Error Updating document (pt010) : ".$this->errorTO->description;
                         return $this->errorTO;
                   } else {
                         $this->dbConn->dbQuery("commit");
                   }        
                   return $this->errorTO;    
              }
     }
//***************************************************************************************************************************************************************************  
  public function getFullDelDocument($principal, $docNum) {
  	
  	     $sql = "SELECT p.uid AS 'prinUid',
                        p.name AS 'principal',
                        dm.document_number,
                        dm.uid AS 'dmUid',
                        psm.deliver_name as 'store',
                        d.uid AS 'depotUid',
                        d.name AS 'depot',
                        d.redelivery_warehouse,
                        dh.invoice_date,
                        s.description AS 'status',
                        dh.document_status_uid
                 FROM " . iDATABASE . ".document_master dm
                 INNER JOIN " . iDATABASE . ".document_header dh ON dm.uid = dh.document_master_uid
                 INNER JOIN " . iDATABASE . ".principal p ON p.uid = dm.principal_uid
                 INNER JOIN " . iDATABASE . ".depot d ON d.uid = dm.depot_uid
                 INNER JOIN " . iDATABASE . ".principal_store_master psm ON psm.uid = dh.principal_store_uid
                 INNER JOIN " . iDATABASE . ".`status` s ON s.uid = dh.document_status_uid
                 WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . "
                 AND   dm.document_number = '" . mysqli_real_escape_string($this->dbConn->connection, $docNum) . "'";

                 $rdDoc = $this->dbConn->dbGetAll($sql);
                 
                 return $rdDoc;
  	}
//***************************************************************************************************************************************************************************  
 public function getFullPartialDocumentDetail($principal, $docNum) {
  	
  	     $sql = "SELECT p.uid AS 'prinUid',
                        p.name AS 'principal',
                        dm.document_number,
                        dm.uid AS 'dmUid',
                        psm.deliver_name as 'store',
                        d.uid AS 'depotUid',
                        d.name AS 'depot',
                        d.redelivery_warehouse,
                        dh.invoice_date,
                        dd.document_qty,
                        pp.uid AS 'prodUid',
                        pp.product_code AS 'prodCode',
                        pp.product_description AS 'product',
                        s.description AS 'status',
                        dh.document_status_uid
                 FROM " . iDATABASE . ".document_master dm
                 INNER JOIN " . iDATABASE . " .document_header dh ON dm.uid = dh.document_master_uid
                 INNER JOIN " . iDATABASE . " .document_detail dd ON dm.uid = dd.document_master_uid
                 INNER JOIN " . iDATABASE . " .principal p ON p.uid = dm.principal_uid
                 INNER JOIN " . iDATABASE . " .depot d ON d.uid = dm.depot_uid
                 INNER JOIN " . iDATABASE . " .principal_store_master psm ON psm.uid = dh.principal_store_uid
                 INNER JOIN " . iDATABASE . " .principal_product pp ON pp.uid = dd.product_uid

                 INNER JOIN " . iDATABASE . ".`status` s ON s.uid = dh.document_status_uid
                 WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . "
                 AND   dm.document_number = '" . mysqli_real_escape_string($this->dbConn->connection, $docNum) . "'";
                 // echo $sql;
                 $rdDoc = $this->dbConn->dbGetAll($sql);
                 
                 return $rdDoc;
  	}
//***************************************************************************************************************************************************************************  
  public function getReturnUomDocument($principal, $docNum) {
  	
  	     $sql = "SELECT p.uid AS 'prinUid',
                        p.name AS 'principal',
                        dm.document_number,
                        dm.uid AS 'dmUid',
                        psm.deliver_name as 'store',
                        d.uid AS 'depotUid',
                        d.name AS 'depot',
                        d.redelivery_warehouse,
                        dh.invoice_date,
                        s.description AS 'status',
                        th.tripsheet_number,
                        th.uid AS 'thUid',
                        t.name AS 'transporter',
                        dh.document_status_uid
                 FROM " . iDATABASE . ".document_master dm
                 INNER JOIN " . iDATABASE . ".document_header dh ON dm.uid = dh.document_master_uid
                 INNER JOIN " . iDATABASE . ".principal p ON p.uid = dm.principal_uid
                 INNER JOIN " . iDATABASE . ".depot d ON d.uid = dm.depot_uid
                 INNER JOIN " . iDATABASE . ".principal_store_master psm ON psm.uid = dh.principal_store_uid
                 INNER JOIN " . iDATABASE . ".`status` s ON s.uid = dh.document_status_uid
                 LEFT  JOIN " . iDATABASE . ".tripsheet_detail td ON td.document_master_uid = dm.uid AND td.removed_flag = 'N'
                 LEFT  JOIN " . iDATABASE . ".tripsheet_header th ON th.uid = td.tripsheet_master_uid
                 LEFT  JOIN " . iDATABASE . ".transporter t ON t.uid = th.transporter_id
                 WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . "
                 AND   dm.document_number = '" . mysqli_real_escape_string($this->dbConn->connection, $docNum) . "'
                 AND   th.uid IS NOT NULL";
                 // echo $sql;                 
                 $rDoc = $this->dbConn->dbGetAll($sql);
                 
                 return $rDoc;
  	}
  // **************************************************************************************************************************************************** 



}
?>
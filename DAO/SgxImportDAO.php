<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');


class SgxImportDAO {
	
	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
}	

// ************************************************************************************************************************************
   public function flagOrdersAsSuccess($dmUid, $seUid, $fName) {
   	
    $sql = "UPDATE smart_event se SET se.status = '" . FLAG_STATUS_CLOSED . "',
                                      se.general_reference_1 = '" . $fName ."' 
            WHERE se.uid = " . mysqli_real_escape_string($this->dbConn->connection,$seUid) ;
            // echo "<br>"; 
            // echo $sql;
            // echo "<br>";
    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
         $this->errorTO->type = FLAG_ERRORTO_ERROR;
         $this->errorTO->description = "Failed to Update Smart Event";
    } else {
         $this->dbConn->dbQuery("commit");
         $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
         $this->errorTO->description = "Successful";
    }

    $sql = "UPDATE document_header dh SET dh.document_status_uid = " . DST_ACCEPTED . "
            WHERE dh.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection,$dmUid);
    
    // echo "<br>"; 
    // echo $sql;
    // echo "<br>";
    
    
    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to Update Document Header";
    } else {
    	$this->dbConn->dbQuery("commit");
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }
    return $this->errorTO;

  }
  
// ************************************************************************************************************************************
  public function flagOrdersAsError($seUid) {
   	
    $sql = "UPDATE smart_event se SET se.status = '" . FLAG_STATUS_ERROR . "',
                                      se.general_reference_2 = 'Account Number Error'
            WHERE se.uid = " . mysqli_real_escape_string($this->dbConn->connection,$seUid) ;
            // echo "<br>"; 
            // echo $sql;
            // echo "<br>";
    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
         $this->errorTO->type = FLAG_ERRORTO_ERROR;
         $this->errorTO->description = "Failed to Update Smart Event";
    } else {
         $this->dbConn->dbQuery("commit");
         $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
         $this->errorTO->description = "Successful";
    }
 }   
// ************************************************************************************************************************************

   public function getSgxStoreErrors($recipient) {

       $sql = "SELECT se.uid AS 'se_uid', 
                      dm.uid AS 'dm_uid',
                      dm.document_number,
                      psm.uid AS 'psm_uid',
                      psm.deliver_name AS 'store',
                      dh.order_date,
                      se.general_reference_2 ,
                      p.name AS 'Principal'
               FROM smart_event se
               LEFT JOIN document_master dm ON se.data_uid = dm.uid
               LEFT JOIN document_header dh ON dh.document_master_uid = dm.uid
               LEFT JOIN principal_store_master psm ON psm.uid = dh.principal_store_uid
               LEFT JOIN .principal p ON p.uid = dm.principal_uid
               WHERE se.type_uid = " . mysqli_real_escape_string($this->dbConn->connection,$recipient) . " 
               AND   se.`status` = 'E';";
               
       return $this->dbConn->dbGetAll($sql);
   }

// ************************************************************************************************************************************

   public function getSgxContacts() {

       $sql = "SELECT pc.email_addr
               FROM .principal_contact pc
               WHERE pc.contact_type_uid = " . CTD_SGX_ACCOUNTS ;
               
       return $this->dbConn->dbGetAll($sql);
   }
// ******************************************************************************************************************
  function insertSfdAccount($sffUid, $psmUID, $sfdValue) {
  	
  	 $sql ="SELECT *
            FROM special_field_details sfd
            WHERE sfd.field_uid  = "  . mysqli_real_escape_string($this->dbConn->connection, $sffUid) . "
            AND   sfd.entity_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $psmUID) . ";";
   
     $sfdList = $this->dbConn->dbGetAll($sql);
     
     If(count($sfdList)	== 0) {
            $dsql = "INSERT INTO special_field_details (special_field_details.field_uid,
                                   special_field_details.entity_uid,
                                   special_field_details.value)
                      VALUES ("  . mysqli_real_escape_string($this->dbConn->connection, $sffUid)   . ",
                              "  . mysqli_real_escape_string($this->dbConn->connection, $psmUID)   . ",
                             '"  . mysqli_real_escape_string($this->dbConn->connection, $sfdValue) . "');"  ;
  	
            $this->errorTO = $this->dbConn->processPosting($dsql,"");

            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                     $this->errorTO->description="SFD Account Insert Failed : ". $dsql .$this->errorTO->description;
                     echo "<br>"; 
                     echo $dsql;
                     echo "<br>";

                     return $this->errorTO;         	                  
            } else {
                     $this->dbConn->dbQuery("commit");
                     $this->errorTO->description="SFD Account Insert Successful";
                     return $this->errorTO;                
            }
     } else {
     	
     	   $sql = "UPDATE special_field_details sfd SET sfd.value = '"  . mysqli_real_escape_string($this->dbConn->connection, $sfdValue) . "'
                 WHERE sfd.field_uid  = "  . mysqli_real_escape_string($this->dbConn->connection, $sffUid) . "
                 AND   sfd.entity_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $psmUID) . ";";
 
            $this->errorTO = $this->dbConn->processPosting($sql,"");

            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                     $this->errorTO->description="SFD Account Update Failed : ". $sql .$this->errorTO->description;
                     echo "<br>"; 
                     echo $sql;
                     echo "<br>";

                     return $this->errorTO;         	                  
            } else {
                     $this->dbConn->dbQuery("commit");
                     $this->errorTO->description="SFD Account Update Successful";
                     return $this->errorTO;                
            }     	     	
     }
  }
// ******************************************************************************************************************
  function setSgxTransactionToDelete($seUid) {
  	 $dsql = "update smart_event se SET se.`status` = 'C', se.general_reference_1 = 'Error Cleared'
              WHERE se.uid = " . mysqli_real_escape_string($this->dbConn->connection, $seUid) ;
  	echo $sql;
              $this->errorTO = $this->dbConn->processPosting($dsql,"");

              if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                     $this->errorTO->description="Transaction Delete Failed : ". $dsql .$this->errorTO->description;
                     return $this->errorTO;         	                  
              } else {
                     $this->dbConn->dbQuery("commit");
                     $this->errorTO->description="Transaction Delete Successful";
                     return $this->errorTO;                
              }  	
  	
  	
  }
// ******************************************************************************************************************

  function getSFDTransactionToManage($docUid){

       $sql = 'SELECT  p.name AS "Principal",
                   d.name AS "Warehouse",
                   dm.document_number,
                   dh.invoice_date,
                   psm.deliver_name,
                   psm.uid as "psmUid",
                   se.general_reference_2,
                   dm.uid,
                   se.uid as "seUid",
                   se.`type`,
                   se.type_uid,
                   sff.order as "sffOrder",
                   sff.uid as "sffUid",
                   sfd.entity_uid,
                   sfd.value
           FROM        document_master dm
           INNER JOIN  document_header dh ON dm.uid = dh.document_master_uid
           INNER JOIN  principal_store_master psm ON psm.uid = dh.principal_store_uid
           LEFT JOIN   special_field_fields sff ON sff.principal_uid = dm.principal_uid AND sff.name = "SGX Lookup Code"
           LEFT JOIN   special_field_details sfd ON sff.uid = sfd.field_uid AND sfd.entity_uid = psm.uid
           INNER JOIN  depot d ON d.uid = dm.depot_uid
           INNER JOIN  principal p ON p.uid = dm.principal_uid
           INNER JOIN  smart_event se ON dm.uid = se.data_uid
           WHERE dm.uid = ' . mysqli_real_escape_string($this->dbConn->connection, $docUid) . '
           AND   se.`type` = "EXT";';

		return $this->dbConn->dbGetAll($sql);
  }
// ******************************************************************************************************************
  function clearSgxDepotStock($sgPrin, $sgDepot) {
  	
  	   $sql = "DELETE FROM stock 
               WHERE principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $sgPrin)  . " 
               AND   depot_id     = " . mysqli_real_escape_string($this->dbConn->connection, $sgDepot) . ";"; 
  	
  	   $this->errorTO = $this->dbConn->processPosting($sql,"");

       if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
              $this->errorTO->description="Stock Delete Failed : ". $sql .$this->errorTO->description;
              return $this->errorTO;         	                  
       } else {
              $this->dbConn->dbQuery("commit");
              $this->errorTO->description="Transaction Delete Successful";
              return $this->errorTO;                
       }
  }
// ******************************************************************************************************************
  function updateSgxDepotStock($updPrin, 
         	                     $sgxDepot, 
         	                     $prodCode, 
         	                     $stockQty,
         	                     $createDateTime) {
         	                     	
         	                     	echo $stockQty;
  	
  	   $sql = "INSERT INTO stock (stock.principal_id,
                                  stock.depot_id,
                                  stock.principal_product_uid,
                                  stock.stock_item,
                                  stock.stock_descrip,
                                  stock.opening,
                                  stock.arrivals,
                                  stock.uplifts,
                                  stock.returns_cancel,
                                  stock.returns_nc,
                                  stock.delivered,
                                  stock.adjustment,
                                  stock.closing,
                                  stock.allocations,
                                  stock.in_pick,
                                  stock.available,
                                  stock.last_updated,
                                  stock.data_generated_date)
               SELECT " . mysqli_real_escape_string($this->dbConn->connection, $updPrin)  . ", 
                      " . mysqli_real_escape_string($this->dbConn->connection, $sgxDepot) . ", 
                      pp.uid, 
                      pp.product_code, 
                      pp.product_description, 
                      " . mysqli_real_escape_string($this->dbConn->connection, $stockQty) . ",
                      0,
                      0,
                      0,
                      0,
                      0,
                      0,
                      " . mysqli_real_escape_string($this->dbConn->connection, $stockQty) . ",
                      0,
                      0,
                      " . mysqli_real_escape_string($this->dbConn->connection, $stockQty) . ",
                      NOW(),
                      '" . mysqli_real_escape_string($this->dbConn->connection, $createDateTime) . "'
               FROM principal_product pp 
               WHERE pp.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $updPrin) . "
               AND pp.product_code = trim('" . mysqli_real_escape_string($this->dbConn->connection, $prodCode) ."');"; 
       // echo $sql;
  	   $this->errorTO = $this->dbConn->processPosting($sql,"");

       if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
              $this->errorTO->description="Stock Update Failed : ". $sql .$this->errorTO->description;
              return $this->errorTO;         	                  
       } else {
              $this->dbConn->dbQuery("commit");
              $this->errorTO->description="Stock Update Successful";
              return $this->errorTO;                
       }
  }
// ******************************************************************************************************************
  function loadInvoicesToTemp($sgxInvNum, 
         	                    $sgxcredFlag, 
         	                    $sgxInvDate, 
         	                    $uppDocNum,
         	                    $lineNum,
         	                    $uppProd,
         	                    $sgxQty,
         	                    $sgxTransNo,
         	                    $sgxFileName) {
      $sql = "SELECT *
              FROM temp
              WHERE FLD1 = '" . mysqli_real_escape_string($this->dbConn->connection, $sgxInvNum)   . "'
              AND   FLD5 = '" . mysqli_real_escape_string($this->dbConn->connection, $lineNum)     . "';";
              
      $findRow = $this->dbConn->dbGetAll($sql);
      
      if(count($findRow) == 0) {       
      
          $sql = "INSERT INTO temp (fld1,
                                    fld2,
                                    fld3,
                                    fld4,
                                    fld5,
                                    fld6,
                                    fld7,
                                    fld8,
                                    fld9)
                  VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $sgxInvNum)   . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $sgxcredFlag) . "',
                          '" . $sgxInvDate   . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $uppDocNum)  . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $lineNum)     . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $uppProd)     . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $sgxQty)      . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $sgxTransNo)  . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $sgxFileName)  . "');";
                  // echo $sql;    
                  $this->errorTO = $this->dbConn->processPosting($sql,"");

                  if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                        $this->errorTO->description="Stock Update Failed : ". $sql .$this->errorTO->description;
                        return $this->errorTO;         	                  
                  } else {
                        $this->dbConn->dbQuery("commit");
                        $this->errorTO->description="Stock Update Successful";
                        return $this->errorTO;                
                  } 
      }
      
             
}                           
// ******************************************************************************************************************
  function loadInvoicesToUpdate($sgxInvNum, 
         	                    $sgxcredFlag, 
         	                    $sgxInvDate, 
         	                    $uppDocNum,
         	                    $lineNum,
         	                    $uppProd,
         	                    $sgxQty,
         	                    $sgxTransNo,
         	                    $sgxFileName) {
      $sql = "SELECT *
              FROM document_update_sgx dus
              WHERE dus.sgx_transaction_number = '" . mysqli_real_escape_string($this->dbConn->connection, $sgxInvNum)   . "'
              AND   dus.line_number            = '" . mysqli_real_escape_string($this->dbConn->connection, $lineNum)     . "';";
              
      $findRow = $this->dbConn->dbGetAll($sql);
      
      if(count($findRow) == 0) {       
      
          $sql = "INSERT INTO document_update_sgx (sgx_transaction_number,
                                                   credit_note,
                                                   transaction_date,
                                                   principal_document,
                                                   line_number,
                                                   product_code,
                                                   quantity,
                                                   tranasction_number,
                                                   file_name)
                  VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $sgxInvNum)   . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $sgxcredFlag) . "',
                          '" . $sgxInvDate   . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $uppDocNum)  . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $lineNum)     . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $uppProd)     . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $sgxQty)      . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $sgxTransNo)  . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $sgxFileName)  . "');";
                  //echo $sql;    
                  $this->errorTO = $this->dbConn->processPosting($sql,"");

                  if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                        $this->errorTO->description="Transaction Update Failed : ". $sql .$this->errorTO->description;
                        return $this->errorTO;         	                  
                  } else {
                        $this->dbConn->dbQuery("commit");
                        $this->errorTO->description="Transaction Update Successful";
                        return $this->errorTO;                
                  } 
      }    
  }                           
// ******************************************************************************************************************
      function getUnExtractedDocuments($dType) {
           
           $sql = "SELECT *
                   FROM document_update_sgx sg
                   WHERE sg.credit_note = '" . mysqli_real_escape_string($this->dbConn->connection, $dType)  . "'
                   AND   sg.update_status = 'Q'
                   ORDER BY sg.sgx_transaction_number";
                   
          $unDocs = $this->dbConn->dbGetAll($sql);
          
          return $unDocs;
      }
// ******************************************************************************************************************
      function getUpdateInvoiceDocument($Prin,
                                        $sgxTrans,
                                        $sgxLine,
                                        $prinDocno, 
                                        $prodCode,
                                        $invDate,
                                        $docQty ) {
      	
          $sql = "UPDATE document_master dm
                  LEFT JOIN document_header dh ON dm.uid = dh.document_master_uid
                  LEFT JOIN document_update_sgx sg ON sg.sgx_transaction_number = '" . mysqli_real_escape_string($this->dbConn->connection, $sgxTrans))  . "'
                                                   AND sg.line_number           = '" . mysqli_real_escape_string($this->dbConn->connection, $sgxLine))   . "'
                  LEFT JOIN .principal_product pp ON pp.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $Prin)  . "
                                               AND pp.product_code    = '" . mysqli_real_escape_string($this->dbConn->connection, $prodCode)  . "'
                  LEFT JOIN .document_detail dd ON dd.document_master_uid = dm.uid AND dd.product_uid = pp.uid
                                                SET  sg.update_status = 'C'
                                                     dh.document_status_uid       = " . DST_INVOICED . ",
                                                     dh.invoice_date              = '" . mysqli_real_escape_string($this->dbConn->connection, $invDate))  . "',
                                                     dd.document_qty              = '" . mysqli_real_escape_string($this->dbConn->connection, $docQty))    . "',
                                                     dd.extended_price            = '" . mysqli_real_escape_string($this->dbConn->connection, $docQty))  . "' * dd.net_price,
                                                     dd.vat_amount                = '" . mysqli_real_escape_string($this->dbConn->connection, $docQty))  . "' * dd.net_price * ". VAL_VAT_RATE . ",
                                                     dd.total                     = '" . mysqli_real_escape_string($this->dbConn->connection, $docQty))  . "' * dd.net_price * ". VAL_VAT_RATE_ADD . ",
                                                     dm.alternate_document_number = '" . mysqli_real_escape_string($this->dbConn->connection, $sgxTrans))  . "'
                  WHERE dm.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $Prin)  . "
                  AND   trim(LEADING '0' FROM dm.document_number) = trim(LEADING '0' FROM '" . mysqli_real_escape_string($this->dbConn->connection, $prinDocno))  . "'
                  fld4 <> ""
                  AND   dm.document_number IS NOT null"
                  echo "<pre>";
                  echo $sql;
                  die();

          $this->dbConn->dbQuery($sql);

          if (!$this->dbConn->dbQueryResult) {
                   $this->errorTO->type = FLAG_ERRORTO_ERROR;
                   $this->errorTO->description = "Failed to Update Smart Event";
          } else {
                   $this->dbConn->dbQuery("commit");
                   $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                   $this->errorTO->description = "Successful";
          }
      }
}  
?>  
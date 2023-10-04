<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'properties/dbSettings.inc');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php') ;


class FileUploadDAO {

	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// ************************************************************************************************************************************
  public function getAvailableUploads($principalId, $query, $uplid) {
  	
  	      if($query == 'ONE'){
               $fList = 'DISTINCT(uff.update_file_type),uff.principal_uid';
               $fType = '';
  	      } else {
              $fList = '*';
              $fType = "AND  uff.update_file_type = '" . mysqli_real_escape_string($this->dbConn->connection, $uplid) ."'";
  	      }	   
  	
          $sql = "SELECT " . $fList . " 
                  FROM " . iDATABASE . ".upload_file_fields uff
                  WHERE " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . " IN (uff.principal_uid)
                  " . $fType . "
                  ORDER BY uff.csv_column";
          
          return $this->dbConn->dbGetAll($sql);  	
  	
  }	
// ************************************************************************************************************************************
      public function dropTempFilesTable($userUId) {

           $bldsql = "DROP TABLE IF EXISTS file_upl_temp_" .mysqli_real_escape_string($this->dbConn->connection, $userUId) ;

           $result = $this->dbConn->dbQuery($bldsql);
           
           $this->dbConn->dbQuery("commit");
           
      }
//******************************************************************************************************************************************************
      public function createTempFilesTable($userUId, $NoFields, $tmpArr ) {
      	      $allFields = '';
      	      ksort($tmpArr);
      	      
      	      foreach($tmpArr as $key=>$value) {
      	      	$allFields = $allFields . "`" . $tmpArr[$key] . "`     VARCHAR(60)  NULL, ";
      	      }
              $bldsql = "CREATE TABLE file_upl_temp_" .mysqli_real_escape_string($this->dbConn->connection, $userUId) . " (
                                               " . $allFields . "
                                               `Z`     VARCHAR(60)  NULL) ";

               $dtresult = $this->dbConn->dbQuery($bldsql);
               
               $this->dbConn->dbQuery("commit");
               
      }
//*************************************************************************************************************************************************
      public function uploadFileDataTemp($fname, $userUId, $fHead) {
      	
           global $ROOT;
           
           if($fHead == "Y") {
              $lin = "IGNORE 1 LINES";
           } else {
              $lin = "";
           }
      	
           $dirPath = $ROOT. "temp/";
           
           $sql='LOAD DATA LOCAL INFILE "' . $dirPath . $fname . '" INTO TABLE file_upl_temp_' .mysqli_real_escape_string($this->dbConn->connection, $userUId) . '
                 CHARACTER SET utf8 
                 FIELDS TERMINATED BY ","
                 OPTIONALLY ENCLOSED BY "\""
                 ESCAPED BY "\\\"
                 LINES TERMINATED BY "\\r\\n" 
                 ' . $lin ;	
                 
           $this->errorTO = $this->dbConn->processPosting($sql,"");
           
           if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) { 
                 return ($this->errorTO);       	                  
           } else {
            	   $this->dbConn->dbQuery("commit");
            	   return ($this->errorTO);
           }
      }	
//*************************************************************************************************************************************************
      public function addWhToCmgj($tblName, $parms) {
      	
      	  $lookUp = substr($parms, strpos($parms, '-')+1,1);
      	
          $sql = "UPDATE " . $tblName . " t 
                  LEFT JOIN .checkers_store_master ch ON trim(LEADING '0' from ch.StoreNumber) = trim(LEADING '0' from trim(SUBSTR(t.Branch_Lookup,POSITION('-' IN t.Branch_Lookup)+1,6)))
                  LEFT JOIN .principal_store_master psm ON psm.ean_code = ch.GLN AND psm.principal_uid = 408 
                  LEFT JOIN .depot d ON d.uid = psm.depot_uid SET t.Region = psm.depot_uid, t.Store = d.name 
                  WHERE 1";
                  
            $this->errorTO = $this->dbConn->processPosting($sql,"");
            
            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                     $this->errorTO->description="Temp File Failed  : ".$this->errorTO->description;
                     return $this->errorTO;       	                  
            } else {
                     $this->dbConn->dbQuery("commit"); 
                     
                     $sql = "SELECT *
                             FROM ". $tblName . "
                             WHERE 1;";
                             
                     $result = $this->dbConn->dbGetAll($sql);
                     
                     ob_clean();
                     
                     foreach ($result as $brow) {
                             $csv_export.= implode(',',$brow) . "\n";
                     }
                     $fileName = "CMGJ.csv";
                     
                     ob_clean();

                     header("Content-Description: File Transfer");
                     header("Content-Disposition: attachment; filename=\"".$fileName."\"");
                     header("Content-Type: application/force-download");
                     echo $csv_export;
                     
                     flush();
            }
      }
//*************************************************************************************************************************************************
      public function coegaPoCheck($tblName, $docFld, $poFld) {
      	
      	        $a = substr($docFld, strpos($docFld,'-')+1,1);
      	        $b = substr($poFld,  strpos($poFld ,'-')+1,1);
      	
      	$sql = "SELECT trim(LEADING '0' FROM f.`Document Number`) AS 'File Document',
                       f.`PO Number` AS 'File PO', 
                       if(dm.document_number IS NULL,'',trim(LEADING '0' FROM dm.document_number)) AS 'Order Document',
                       if(dh.customer_order_number IS NULL,'',dh.customer_order_number) AS 'Order PO',
                       if(dm.document_number IS NULL, 'Document number not Found','') AS 'Document Not Matched',
                       if(TRIM(dh.customer_order_number) <> TRIM(f.`PO Number`), 'PO Mismatch','') AS 'PO Number Match',
                       if(dh.order_date IS NULL,'',dh.order_date) AS 'Order Date'
                FROM " . mysqli_real_escape_string($this->dbConn->connection, $tblName) . " f
                LEFT join document_master dm ON dm.principal_uid = 391  AND  dm.document_number = CONCAT('00',f.`Document Number`)
                LEFT JOIN .document_header dh ON dh.document_master_uid = dm.uid
                WHERE f.`Document Number` <> '';";

                $result = $this->dbConn->dbGetAll($sql);
                     
                ob_clean();
                
                $csv_export .= "File Document, File PO, Order Document, Order PO, Document Not Matched,PO Number Match,Order Date" . "\n";
                     
                foreach ($result as $brow) {
                        $csv_export.= implode(',',$brow) . "\n";
                }
                $fileName = "PO Match.csv";
                     
                ob_clean();

                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=\"".$fileName."\"");
                header("Content-Type: application/force-download");
                echo $csv_export;
                     
                flush();
      }
//*************************************************************************************************************************************************
      public function getSpecialFieldId($prinId) {
             // Get Special Field
             $sql = "SELECT sff.uid as 'fldId'
                     FROM .special_field_fields sff 
                     WHERE sff.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
                     AND   sff.`type` = 'S';";
                  
             $sfd = $this->dbConn->dbGetAll($sql);
             
             return $sfd;	
      }
//*************************************************************************************************************************************************
      public function checkIfStoreExists($prinId, $fldId, $val) {
           
           $sql = "SELECT *
                   FROM principal_store_master psm
                   INNER JOIN .special_field_details sfd ON " . $fldId . " = sfd.field_uid 
                                                         AND psm.uid       = sfd.entity_uid
                   WHERE psm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
                   AND   trim(sfd.value) = '" . mysqli_real_escape_string($this->dbConn->connection, $val) . "'";
          
          $sfdExists = $this->dbConn->dbGetAll($sql);
          
          return $sfdExists;
      	
      }
//*************************************************************************************************************************************************
 public function getTempFileRecords($tblName) {
 	
 	    $sql = "SELECT *
              FROM " . mysqli_real_escape_string($this->dbConn->connection, $tblName) . " f
              WHERE 1";
 	
              $tmpFile = $this->dbConn->dbGetAll($sql);
          
              return $tmpFile; 	
}
//*************************************************************************************************************************************************
      public function maintainPrincipalStores($tblName, 
                                              $prinId,
                                              $userid,
                                              $spf, 
                                              $fld1, 
                                              $fld2,
                                              $fld3,
                                              $fld4,
                                              $fld5,
                                              $fld6,
                                              $fld7,
                                              $fld8,
                                              $fld9,
                                              $fld10,
                                              $fld11,
                                              $fld12,
                                              $fld13) {
                                              	
                $delName = $fld1;                                   	
                $deladd1 = $fld2;                                   	
                $deladd2 = $fld3;
                $deladd3 = $fld4;                                 	

                $billName = $fld5;                                  	
                $billadd1 = $fld6;                                   	
                $billadd2 = $fld7;                                  	
                $billadd3 = $fld8;     	

                $vatNo    = $fld9;                          	
                $Chain    = $fld10;                                   	
                $depot    = $fld11;                                   	
                $oldacc   = $fld12;
                $specFld  = $fld13;    	

                $sql = "INSERT INTO `principal_store_master` (`principal_uid`, 
                                                              `deliver_name`, 
                                                              `deliver_add1`, 
                                                              `deliver_add2`, 
                                                              `deliver_add3`, 
                                                              `bill_name`, 
                                                              `bill_add1`, 
                                                              `bill_add2`, 
                                                              `bill_add3`,
                                                              `vat_number`, 
                                                              `depot_uid`, 
                                                              `principal_chain_uid`, 
                                                              `alt_principal_chain_uid`,
                                                              `captured_by` ,
                                                              `Date_Time`,
                                                              `old_account`)
                        VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $delName) . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $deladd1) . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $deladd2) . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $deladd3) . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $billName) . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $billAdd1) . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $billAdd2) . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $billAdd3) . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $vatNo)    . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $Chain)    . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $Chain)    . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $depot)    . "',
                                '" . mysqli_real_escape_string($this->dbConn->connection, $userid)   . "',
                                NOW(),
                                '" . mysqli_real_escape_string($this->dbConn->connection, $oldacc)   . "')";
                                
                                echo $sql;
                                
                $this->errorTO = $this->dbConn->processPosting($sql,"");
            
                if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                        $this->errorTO->description="Failed to Create Store  : ".$this->errorTO->description;
                        return $this->errorTO;       	                  
                } else {
                        $this->dbConn->dbQuery("commit");               	
                        $pdUId = $this->dbConn->dbGetLastInsertId();
                        
                        $sql = "INSERT INTO special_field_details (special_field_details.field_uid, 
                                                                   special_field_details.entity_uid,
                                                                   special_field_details.value)
                                VALUES (" .$spf. "," . $pdUId . ", " . $specFld . ")";
                        $this->errorTO = $this->dbConn->processPosting($sql,"");
                        echo $sql;
            
                        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                               $this->errorTO->description="Failed to Create Store  : ".$this->errorTO->description;
                               return $this->errorTO;       	                  
                        } else {
                               $this->dbConn->dbQuery("commit"); 
                        }
                }
    }            
//*************************************************************************************************************************************************
      public function loadUppStores($tblName, $prinId, $userId) {
      	
      	  // Get Stores from Imports and if store Exists
      	  
      	  $sql = 'SELECT if(trim(ftu.`Store name`) = "", trim(ftu.`Entity name`), trim(ftu.`Store name`)) AS "Store",
      	                 ftu.`Entity name`,
                         psm.deliver_name,
                         psm.bill_name,  
                         psm.uid as "psmUid",
                         ftu.`Customer`,
                         ftu.`EU VAT no`,
                         ftu.`Address line 1`,
                         ftu.`Address line 2`,
                         ftu.`Address line 3`,
                         psm.vat_number,
                         ftu.`Shipment site`,
                         sdep.`entity_uid` as "DepUid",
                         psm.depot_uid,
                         ftu.`Shipment site`,
                         psm.courier_code 
                  FROM      ' . iDATABASE . "." . $tblName. ' ftu
                  LEFT join ' . iDATABASE . '.special_field_details sfd  ON ftu.`Customer` = sfd.value AND sfd.field_uid = 561
                  LEFT JOIN ' . iDATABASE . '.principal_store_master psm ON psm.uid = sfd.entity_uid
                  LEFT join ' . iDATABASE . '.special_field_details sdep ON ftu.`Shipment site` = sdep.value AND sdep.field_uid = 594
                  WHERE ftu.`Shipment site` IN ("10UPP","50UPP","40UPP","70UPP");';
                  
                  $updateCount = 0;
                  $insertCount = 0;
                  $updateStatus = "F";
                  
          $storeList = $this->dbConn->dbGetAll($sql);
          
          if(count($storeList) == 0) {
          	$this->errorTO->type = FLAG_ERRORTO_ERROR;
          	return $this->errorTO;
          }
          
          foreach($storeList as $sRow) {
               if($sRow['psmUid'] == NULL) {
               	      
               	      $delName = $billName = mysqli_real_escape_string($this->dbConn->connection, str_replace("'"," ",$sRow['Store']));
               	      
               	      $deladd1 = $billAdd1 = mysqli_real_escape_string($this->dbConn->connection, str_replace("'"," ",$sRow['Address line 1']));
               	      $deladd2 = $billAdd2 = mysqli_real_escape_string($this->dbConn->connection, str_replace("'"," ",$sRow['Address line 2']));
               	      $deladd3 = $billAdd3 = mysqli_real_escape_string($this->dbConn->connection, str_replace("'"," ",$sRow['Address line 3']));

               	      $vatNo   = mysqli_real_escape_string($this->dbConn->connection, str_replace("'"," ",$sRow['EU VAT no']));
                      
                      $Chain   = '3182';
                      
                      $depot   =  mysqli_real_escape_string($this->dbConn->connection, $sRow['DepUid']);
                      
                      $userid  =  $userId;
                      
                      $oldacc  = mysqli_real_escape_string($this->dbConn->connection, $sRow['Customer']);
                      
                      $spf     = 561;
               	
               	      $sql = "INSERT INTO `principal_store_master` (`principal_uid`, 
                                                                    `deliver_name`, 
                                                                    `deliver_add1`, 
                                                                    `deliver_add2`, 
                                                                    `deliver_add3`, 
                                                                    `bill_name`, 
                                                                    `bill_add1`, 
                                                                    `bill_add2`, 
                                                                    `bill_add3`,
                                                                    `vat_number`, 
                                                                    `depot_uid`, 
                                                                    `principal_chain_uid`, 
                                                                    `alt_principal_chain_uid`,
                                                                    `captured_by` ,
                                                                    `Date_Time`,
                                                                    `old_account`)
                              VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $delName) . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $deladd1) . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $deladd2) . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $deladd3) . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $billName) . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $billAdd1) . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $billAdd2) . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $billAdd3) . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $vatNo)    . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $Chain)    . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $Chain)    . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $depot)    . "',
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $userId)   . "',
                                      NOW(),
                                      '" . mysqli_real_escape_string($this->dbConn->connection, $oldacc)   . "')";
                                
                              $this->errorTO = $this->dbConn->processPosting($sql,"");
            
                              if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                                  $this->errorTO->description="Failed to Create Store  : ".$this->errorTO->description;
                                  return $this->errorTO;       	                  
                              } else {
                                      $this->dbConn->dbQuery("commit");               	
                                      $pdUId = $this->dbConn->dbGetLastInsertId();
                        
                                      $sql = "INSERT INTO special_field_details (special_field_details.field_uid, 
                                                                                 special_field_details.entity_uid,
                                                                                 special_field_details.value)
                                              VALUES (" . $spf . "," . $pdUId . ", '" . $oldacc . "')";
                                
                                
                                      $this->errorTO = $this->dbConn->processPosting($sql,"");
            
                                      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                                             $this->errorTO->description="Failed to Create Store  : ".$this->errorTO->description;
                                             return $this->errorTO;       	                  
                                      } else {
                                      	     $insertCount++;
                                             $this->dbConn->dbQuery("commit");
                                      }
                              }
                  } else {
               	
               	  if(str_replace("'"," ",$sRow['Store']) == $sRow['deliver_name']) {
               	       $updateDelName = "";
               	  }   else {
               	       echo "update Store <br>";
                       $updateDelName = "psm.deliver_name = '" . mysqli_real_escape_string($this->dbConn->connection, str_replace("'"," ",$sRow['Store'])) . "' ";
               	       $updateStatus = "T";
               	  }
               	  if(str_replace("'"," ",$sRow['Entity name']) == $sRow['bill_name']) {
               	       $updateBillName = "";
               	  }   else {
               	       echo "update BillName <br>";
                       $updateBillName = "psm.bill_name = '" . mysqli_real_escape_string($this->dbConn->connection, str_replace("'"," ",$sRow['Entity name'])) . "' ";
               	       $updateStatus = "T";
               	  }
               	  if(str_replace("'"," ",$sRow['EU VAT no']) == $sRow['vat_number']) {
               	       $updateVatNo = "";
               	  }   else {
               	       echo "update VAT <br>";
                       $updateVatNo = "psm.vat_number = '" . mysqli_real_escape_string($this->dbConn->connection, str_replace("'"," ",$sRow['EU VAT no'])) . "' ";
               	       $updateStatus = "T";
               	  }               	  
               	  if(str_replace("'"," ",$sRow['DepUid']) == $sRow['depot_uid']) {
               	       $updateDepotId = "";
               	  }   else {
               	       echo "Update Depot <br>";
                       $updateDepotId = "psm.depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, str_replace("'"," ",$sRow['DepUid'])) . "' ";
               	       $updateStatus = "T";
               	  }    
              	  if(str_replace("'"," ",$sRow['Shipment site']) == $sRow['courier_code']) {
               	       $updateCourierCode = "";
               	  }   else {
               	       echo "Update Courier Code <br>";
                       $updateCourierCode = "psm.courier_code = '" . mysqli_real_escape_string($this->dbConn->connection, str_replace("'"," ",$sRow['Shipment site'])) . "' ";
               	       $updateStatus = "T";
               	  }     
               	  if($updateStatus == "T") { 
               	  	       $updateCount++; 
               	           $updateStatus = "F";
               	           if($updateDelName <> '') {               	           	
               	                  $sql = "UPDATE " . iDATABASE . ".`principal_store_master` psm SET " . $updateDelName . "
               	                          WHERE  `uid`= " .$sRow['psmUid'] . ";";
               	                  
               	                  $this->errorTO = $this->dbConn->processPosting($sql,"");
            
                                  if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                                           $this->errorTO->description="Failed to Update Deliver Name : ".$this->errorTO->description;
                                           return $this->errorTO;       	                  
                                  } else {
                                           $this->dbConn->dbQuery("commit"); 
                                  }	
               	           }               	           
              	           if($updateBillName <> '') {               	           	
               	                  $sql = "UPDATE " . iDATABASE . ".`principal_store_master`  psm  SET " . $updateBillName . "
               	                          WHERE  `uid`= " .$sRow['psmUid'] . ";";
               	                  
               	                  $this->errorTO = $this->dbConn->processPosting($sql,"");
            
                                  if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                                           $this->errorTO->description="Failed to Update Bill Name : ".$this->errorTO->description;
                                           return $this->errorTO;       	                  
                                  } else {
                                           $this->dbConn->dbQuery("commit"); 
                                  }	
               	           }
              	           if($updateVatNo <> '') {               	           	
               	                  $sql = "UPDATE " . iDATABASE . ".`principal_store_master` psm SET " . $updateVatNo . "
               	                          WHERE  `uid`= " .$sRow['psmUid'] . ";";
               	                  
               	                  $this->errorTO = $this->dbConn->processPosting($sql,"");
            
                                  if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                                           $this->errorTO->description="Failed to Update VAT No : ".$this->errorTO->description;
                                           return $this->errorTO;       	                  
                                  } else {
                                           $this->dbConn->dbQuery("commit"); 
                                  }	
               	           }

              	           if($updateDepotId <> '') {               	           	
               	                  $sql = "UPDATE " . iDATABASE . ".`principal_store_master`  psm  SET " . $updateDepotId . "
               	                          WHERE  `uid`= " .$sRow['psmUid'] . ";";
               	                  
               	                  $this->errorTO = $this->dbConn->processPosting($sql,"");
            
                                  if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                                           $this->errorTO->description="Failed to Update Depot : ".$this->errorTO->description;
                                           return $this->errorTO;       	                  
                                  } else {
                                           $this->dbConn->dbQuery("commit"); 
                                  }	
               	           }
              	           if($updateCourierCode <> '') {               	           	
               	                  $sql = "UPDATE " . iDATABASE . ".`principal_store_master` psm SET " . $updateCourierCode . "
               	                          WHERE  `uid`= " .$sRow['psmUid'] . ";";
               	                  
               	                  $this->errorTO = $this->dbConn->processPosting($sql,"");
            
                                  if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                                           $this->errorTO->description="Failed to Courier Code : ".$this->errorTO->description;
                                           return $this->errorTO;       	                  
                                  } else {
                                           $this->dbConn->dbQuery("commit"); 
                                  }	
               	           }
               	  }         
               } 
          }
          
          echo "     Number of stores updated  - " . $updateCount;
          echo "<br><br>"  ;         
          echo "     Number of stores inserted - " . $insertCount;
          
          $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
          return $this->errorTO;
      }      
//*************************************************************************************************************************************************
      public function getUserDepots($prin, $user) {

           $sql = "SELECT depot.uid AS 'depUid', depot.name AS 'Warehouse'
                   FROM " . iDATABASE . ".user_principal_depot upd
                   INNER JOIN " . iDATABASE . ".depot ON depot.uid = upd.depot_id
                   WHERE upd.user_id = " . mysqli_real_escape_string($this->dbConn->connection, $user) . "
                   AND   upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $prin) . ";";
           
          $depotList = $this->dbConn->dbGetAll($sql);
          
          return $depotList;
       }
//*************************************************************************************************************************************************
      public function stockCountFile($tblName, $prinId, $depId) {
      	
      	  $sql = "SELECT ppc.description,
                         s.stock_item, 
                         s.stock_descrip,
                         ftu.Description,
                         s.closing,
                         ftu.`count` ,
                         s.closing - ftu.`count`
                  FROM .stock s
                  LEFT JOIN .principal_product pp ON pp.uid = s.principal_product_uid
                  LEFT JOIN .principal_product_category ppc ON ppc.uid = pp.major_category
                  LEFT JOIN file_upl_temp_11 ftu on trim(ftu.Description) = trim(s.stock_descrip) 
                  WHERE  s.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
                  AND    s.depot_id = " . mysqli_real_escape_string($this->dbConn->connection, $depId) . "
                  AND    s.stock_item <> 'TEST';";
          
          $result = $this->dbConn->dbGetAll($sql);
          
          ob_clean();
                
          $csv_export .= "Category,Product Code,Product,Closing,Count,Variance" . "\n";
               
          foreach ($result as $brow) {
                  $csv_export.= implode(',',$brow) . "\n";
          }
          $fileName = "Stock Take Variances.csv";
               
          ob_clean();

          header("Content-Description: File Transfer");
          header("Content-Disposition: attachment; filename=\"".$fileName."\"");
          header("Content-Type: application/force-download");
          echo $csv_export;
               
          flush();
      }

//*************************************************************************************************************************************************
       
}
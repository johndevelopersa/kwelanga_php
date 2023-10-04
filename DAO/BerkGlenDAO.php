<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");


class BerkGlenDAO {

  private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// ************************************************************************************************************************************
  public function uploadProcessStores($prinId, $dirPath) {
  	
           $bldsql = "DROP TABLE IF EXISTS glenBerkTemp";
           $result = $this->dbConn->dbQuery($bldsql);
           
           $this->dbConn->dbQuery("commit");
       
           //******************************************************************************************************************************************************
       
            $bldsql = "CREATE TABLE glenBerkTemp (`FLD1`              VARCHAR(100)  NULL,
                                                  `FLD2`              VARCHAR(100)  NULL,
                                                  `FLD3`              VARCHAR(100)  NULL,
                                                  `FLD4`              VARCHAR(100)  NULL,
                                                  `FLD5`              VARCHAR(100)  NULL,
                                                  `FLD6`              VARCHAR(100)  NULL,
                                                  `FLD7`              VARCHAR(100)  NULL,
                                                  `FLD8`              VARCHAR(100)  NULL,
                                                  `FLD9`              VARCHAR(100)  NULL,
                                                  `FLD10`             VARCHAR(100)  NULL,
                                                  `FLD11`             VARCHAR(100)  NULL,
                                                  `FLD12`             VARCHAR(100)  NULL,
                                                  `FLD13`             VARCHAR(100)  NULL,
                                                  `FLD14`             VARCHAR(100)  NULL,
                                                  `FLD15`             VARCHAR(100)  NULL);";
                                   
            $dtresult = $this->dbConn->dbQuery($bldsql);
            
            //*************************************************************************************************************************************************
       
            $sql='LOAD DATA LOCAL INFILE "' . $dirPath . '" INTO TABLE glenBerkTemp
                  FIELDS TERMINATED BY "|"
                  OPTIONALLY ENCLOSED BY "\""
                  ESCAPED BY "\\\"
                  LINES TERMINATED BY "\\r\\n" 
                  IGNORE 1 LINES';
       
            $this->errorTO = $this->dbConn->processPosting($sql,"");
          
            if($this->errorTO->type == 'S') {
                  $this->dbConn->dbQuery("commit");
                  echo "<br>";
                  echo 'File Successful Uploaded<br><br>' . $dirPath . ' <br>';
                  echo "<br>";
            } else {
                  echo "<br>";
                  echo 'File has a problem<br><br>' . $dirPath . ' <br>';
                  echo "<br>";
                  echo "<pre>";
                  print_r($this->errorTO); 
            }
                      
            
            $sql = "SELECT uid AS 'SpUid'
                    FROM special_field_fields spf
                    WHERE spf.principal_uid IN ('". mysqli_real_escape_string($this->dbConn->connection, $prinId) . "')
                    AND   spf.`type` = 'S'";
                    
            $specFld = $this->dbConn->dbGetAll($sql); 
            
            $specFldOutput = $specFld[0]['SpUid'];
            
            echo "<br>";
            
            $sql = "SELECT pcm.uid AS 'chainID'
                    FROM principal_chain_master pcm
                    WHERE pcm.principal_uid  IN ('". mysqli_real_escape_string($this->dbConn->connection, $prinId) . "')
                    AND   pcm.old_code = '999'";
            
            $chainFld = $this->dbConn->dbGetAll($sql); 
            
            $chainFldOutput =  $chainFld[0]['chainID'];
            
            $sql = "SELECT *
                    FROM glenberktemp gb
                    WHERE gb.FLD1 = '". mysqli_real_escape_string($this->dbConn->connection, $prinId) . "'";
            
            $storeList = $this->dbConn->dbGetAll($sql);
            foreach($storeList AS $sRow) {
            	
            	    $sql = "SELECT *
                          FROM special_field_details sfd
                          WHERE sfd.field_uid = " . $specFldOutput . "
                          AND   sfd.value = '" . $sRow['FLD3']   . "'";
                          
                  $stExist = $this->dbConn->dbGetAll($sql);
                  
                  if(count($stExist) == 0) {
                  	
                  	     $currentStore = $sRow['FLD3'];
                  	
                         if($sRow['FLD8'] == 'E1') {
                            $depId = '7'; 
                         } elseif($sRow['FLD8'] == 'J1') {
                            $depId = '195';            	    
                         } elseif($sRow['FLD8'] == 'B1') {
                            $depId = '393';  
                         } else {
                            $depId = '452';
                         }	
                         
                         if(mysqli_real_escape_string($this->dbConn->connection, $prinId) == 425) {
                              $depList = "'J1','B1','E1'"; 	
                         } else {
                              $depList = "'E1'";	
                         }
                         
                         $depList = "'J1','B1','E1'";      	
                         
                         $sql = "INSERT INTO `principal_store_master` ( `principal_uid`,
                                             `deliver_name`, 
                                             `deliver_add1`, 
                                             `deliver_add2`, 
                                             `deliver_add3`, 
                                             `bill_name`, 
                                             `bill_add1`, 
                                             `bill_add2`, 
                                             `bill_add3`,
                                             `principal_store_master`.vat_number,
                                             `depot_uid`, 
                                             `principal_chain_uid`,
                                             `principal_store_master`.alt_principal_chain_uid, 
                                             `old_account`,
                                             `last_change_by_userid`)
                      
                                 SELECT '". mysqli_real_escape_string($this->dbConn->connection, $prinId) . "',
                                        '" . str_replace("'", " ",$sRow['FLD4'])  . "',    
                                        '" . str_replace("'", " ",$sRow['FLD11']) . "',
                                        '" . str_replace("'", " ",$sRow['FLD12']) . "',
                                        '" . str_replace("'", " ",$sRow['FLD13']) . "',
                                        '" . str_replace("'", " ",$sRow['FLD4'])  . "',    
                                        '" . str_replace("'", " ",$sRow['FLD11']) . "',
                                        '" . str_replace("'", " ",$sRow['FLD12']) . "',
                                        '" . str_replace("'", " ",$sRow['FLD13']) . "',
                                        '" . str_replace("'", " ",$sRow['FLD7'])  . "',    
                                        '" . $depId. "',
                                        '" . $chainFldOutput. "',
                                        '" . $chainFldOutput. "',
                                        '" . $sRow['FLD3']   . "',
                                        612
                                 FROM glenBerkTemp f
                                 WHERE FLD8 IN (" .  $depList . ")
                                 AND   FLD3 = " . $currentStore . ";";
                                 
                         $this->errorTO = $this->dbConn->processPosting($sql,"");
              
                         if($this->errorTO->type == 'S') {
                                 $this->dbConn->dbQuery("commit");
                         } else {
                              // echo $sql;
                              $this->errorTO;  
                         }
                  }
            }
            
            $sql = "INSERT ignore INTO special_field_details (special_field_details.field_uid,
                                                              special_field_details.value,
                                                              special_field_details.entity_uid)

                    SELECT " . $specFldOutput . ",
                               psm.old_account,
                               psm.uid
                    FROM principal_store_master psm 
                    WHERE psm.principal_uid = '". mysqli_real_escape_string($this->dbConn->connection, $prinId) . "'
                    AND   psm.principal_chain_uid = '" . $chainFldOutput. "'";
            	
            $this->errorTO = $this->dbConn->processPosting($sql,"");
              
            if($this->errorTO->type == 'S') {
                  $this->dbConn->dbQuery("commit");
                  return $this->errorTO;
            } else {
       	        // echo $sql;
                  return $this->errorTO;  
            }            
            
            
  }

//********************************************************************************************************************************************************	 
 
}
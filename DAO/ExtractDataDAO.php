<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');


class ExtractDataDAO {

  private $dbConn;

  function __construct($dbConn) {
    $this->dbConn = $dbConn;
  }
// ************************************************************************************************************************************
  public function getDocumentList() {

     $sql = "SELECT *
             FROM temp_voqado
             WHERE 1";

            return $this->dbConn->dbGetAll($sql);
  }
// ************************************************************************************************************************************

  public function getVoqadoParms($principalId) {

     $sql = "SELECT vep.principal_uid, vep.voqado_account_field, vep.notification_uid
             FROM .voqado_extract_parameters vep
             WHERE vep.principal_uid IN (". mysqli_real_escape_string($this->dbConn->connection, $principalId) . ")"; 

            return $this->dbConn->dbGetAll($sql);
  }
// ************************************************************************************************************************************

  public function insertToSmartEvent($principalId, $notificationId, $docNo, $doctype) {
  	
  	      if ($doctype == 'C') {
  	          $numField = "dm.alternate_document_number"; 	  	      	
  	      } else {
  	      	  if($principalId == '71') {
  	      	  	  $numField = "dm.document_number";  	      	     	
  	      	  } else {
  	      	      $numField = "dm.document_number"; 	
  	      	  }
  	      }
          $sql = "insert into smart_event (created_date, type, type_uid, processed_date, status, status_msg, data_uid) 
                  select NOW(), 'EXT'," . mysqli_real_escape_string($this->dbConn->connection, $notificationId) . ", NULL, 'C', '', dm.uid 
                  from document_master dm, 
                       document_header dh, 
	                     principal_store_master psm 
                  where dm.uid = dh.document_master_uid 
                  and   dh.principal_store_uid = psm.uid 
                  and   dm.principal_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ") 
                  and   dm.document_type_uid in (1,4,13,6,34,27,37,31) 
                  and   dh.document_status_uid in (76,77,78,73,81)
                  and not exists (select 1 from smart_event se 
                                  where se.data_uid = dm.uid 
								                  and se.type = 'EXT' 
								                  and se.type_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $notificationId) . " ))
                  AND  " . $numField . " = lpad(" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . ",8,'0' )";

           $rTO = $this->dbConn->processPosting($sql,''); 
           $this->dbConn->dbQuery("commit");
  }
  
 // ************************************************************************************************************************************

  public function unFlagSmartEvent($principalId, $notificationId, $docNo, $doctype) {  
  
    	   if ($doctype == 'C') {
  	          $numField = "dm.alternate_document_number"; 	  	      	
  	     } else {
  	    	  if($principalId == '71') {
  	       	    $numField = "dm.document_number";  	      	     	
  	        } else {
  	      	    $numField = "dm.document_number"; 	
  	        }
  	     }
   
        $sql = "UPDATE    document_master dm
                left join smart_event se on se.data_uid = dm.uid and se.`type_uid` IN (" . mysqli_real_escape_string($this->dbConn->connection, $notificationId) . "),
                          document_header dh SET se.`status` = 'Q'
                where     dm.uid = dh.document_master_uid
                AND       dm.principal_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ") 
                AND       dh.document_status_uid IN (76,77,78, 81, 73)
                AND       dm.document_type_uid in (1,4,31, 32)
                AND       " . $numField . " = lpad(" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . ",8,'0')";
                
 //               echo $sql;
  
         $rTO = $this->dbConn->processPosting($sql,''); 
         $this->dbConn->dbQuery("commit");  
   }  
  
 // ************************************************************************************************************************************

  public function checkSpecialFields($principalId, $sfdId, $spfield, $docNo, $doctype) {   
 
    	   if ($doctype == 'C') {
  	          $numField = "dm.alternate_document_number"; 	  	      	
  	     } else {
  	    	  if($principalId == '71') {
  	       	    $numField = "dm.document_number";  	      	     	
  	        } else {
  	      	    $numField = "dm.document_number"; 	
  	        }
  	     }
         $sql = "SELECT *
                 FROM .special_field_details sfd
                 WHERE sfd.field_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $sfdId) . ") 
                 AND   sfd.entity_uid IN (SELECT dh.principal_store_uid
                                          FROM .document_master dm, document_header dh
                                          WHERE dh.document_master_uid = dm.uid
                                          AND   dm.principal_uid  in (" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ") 
                                          AND   " . $numField . " = lpad(" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . ",8,'0' ))";
                 
                 $sPF = $this->dbConn->dbGetAll($sql);
              
                 if(sizeof($sPF) > 0 && mysqli_real_escape_string($this->dbConn->connection, $spfield) <> $sPF[0]['value'] ) {
                 	     $sql = "UPDATE `special_field_details` SET `value` = '" . mysqli_real_escape_string($this->dbConn->connection, $spfield) . "'
                 	             WHERE  `field_uid`  in (" . mysqli_real_escape_string($this->dbConn->connection, $sfdId) . ") 
                 	             AND    `entity_uid` in (SELECT dh.principal_store_uid
                                                       FROM .document_master dm, document_header dh
                                                       WHERE dh.document_master_uid = dm.uid
                                                       AND   dm.principal_uid  in (" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ") 
                                                       AND   " . $numField . " = lpad(" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . ",8,'0'))";
                 	
 	
                        $rTO = $this->dbConn->processPosting($sql,''); 
                        $this->dbConn->dbQuery("commit");                  	                 	
                 } else {
                 	      
                 	      $sql = "INSERT INTO `special_field_details` (`field_uid`, `value`, `entity_uid`) 
                 	              VALUES (" . mysqli_real_escape_string($this->dbConn->connection, $sfdId) . ", 
                 	                      '" . mysqli_real_escape_string($this->dbConn->connection, $spfield) . "', 
                 	                      (SELECT dh.principal_store_uid
                                        FROM .document_master dm, document_header dh
                                        WHERE dh.document_master_uid = dm.uid
                                        AND   dm.principal_uid  in (" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ") 
                                        AND   " . $numField . " = lpad(" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . ",8,'0' )))";
                 
                        $rTO = $this->dbConn->processPosting($sql,''); 
                        $this->dbConn->dbQuery("commit");                  	
                 	
                 }
  }
 // ************************************************************************************************************************************

  public function setJeToRun($principalId) {   
  	  $sql = "UPDATE job_execution je SET je.last_run = concat(substr(je.last_run,1,11) ,'04:00:0')
              WHERE je.script_name = 'voqadoExtactDocument'
              AND   je.principal_uid in (" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ");";              
      $rTO = $this->dbConn->processPosting($sql,''); 
             $this->dbConn->dbQuery("commit"); 
  }	

// ************************************************************************************************************************************






}  


?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class BcScannerDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  // **************************************************************************************************************************************************** 

  public function getScannerLogInfo($whUid) {
  
       $sql = "SELECT  th.uid AS 'tripUid',
                       dm.app_json_response,
                       dm.app_metadata_json_response
                  FROM .tripsheet_header th 
                  INNER JOIN .tripsheet_detail td ON th.uid = td.tripsheet_master_uid AND td.removed_flag = 'N'
                  LEFT JOIN .document_master dm ON dm.uid = td.document_master_uid
                  WHERE th.depot_uid IN (" . $whUid . ")
                  AND th.tripsheet_date > '2022-03-01'
                  AND th.tripsheet_scan_log = 'N'";
                  
//                  echo "<br>";
//                  echo "<pre>";
//                  echo $sql;
//                  echo "<br>";
       
       $uPList = $this->dbConn->dbGetAll($sql);

       return $uPList ;
  
  }
  // **************************************************************************************************************************************************** 
  public function checkForExistingLogEntry($tsUid) {
  
       $sql = "SELECT *
               FROM .load_scanner_log lsl
               WHERE lsl.trip_sheet_header_uid = ". mysqli_real_escape_string($this->dbConn->connection, $tsUid);
               
       $recExist = $this->dbConn->dbGetAll($sql);

       return $recExist ;
  } 
  
  // **************************************************************************************************************************************************** 
  public function checkForExistingLogDetailEntry($tsUid,
                                                 $docMastUid,
                                                 $prodUid,
                                                 $docQty) {
  
       $sql = "SELECT *
               FROM .load_scanner_log_detail lsd
               WHERE lsd.trip_sheet_header_uid = " . mysqli_real_escape_string($this->dbConn->connection, $tsUid)      . "
               AND   lsd.document_uid         = " . mysqli_real_escape_string($this->dbConn->connection, $docMastUid) . "
               AND   lsd.product_uid          = " . mysqli_real_escape_string($this->dbConn->connection, $prodUid)    . "
               AND   lsd.document_qty         = " . mysqli_real_escape_string($this->dbConn->connection, $docQty)        ;
  
       $detRecExist = $this->dbConn->dbGetAll($sql);

       return $detRecExist ;  
      
   }
 // **************************************************************************************************************************************************** 
  public function insertintoLogHeader($tsUid,
                                      $userName,
                                      $password,
                                      $createdDateTime,
                                      $transporterUId, 
                                      $vehicleReg,
                                      $otp,            
                                      $latitude,       
                                      $longitude ,     
                                      $timestampLocale,
                                      $uploadedDateTime,
                                      $timestamp) {
                                      	
       if($uploadedDateTime == '') {$uploadedDateTime = $createdDateTime; }       	
  
       $sql = "INSERT INTO load_scanner_log (load_scanner_log.trip_sheet_header_uid,
                              load_scanner_log.username,
                              load_scanner_log.password,
                              load_scanner_log.dateTime,
                              load_scanner_log.transporter,
                              load_scanner_log.vehicleReg,
                              load_scanner_log.otp,
                              load_scanner_log.longitude,
                              load_scanner_log.latitude,
                              load_scanner_log.dateTimeStamp,
                              load_scanner_log.timestamp,
                              load_scanner_log.uploadedDateTime )
               VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $tsUid)            . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $userName)         . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $password)         . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $createdDateTime)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $transporterUId)   . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $vehicleReg)       . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $otp)              . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $latitude)         . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $longitude )       . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $timestampLocale)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $timestamp)        . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $uploadedDateTime) . "')";

               $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
               if($this->errorTO->type == 'S') {
                    $this->dbConn->dbQuery("commit");
                    return $this->errorTO;     	
               } else {
                      echo "<br>";
                      echo $sql;
                      echo "<br>";
                      return $this->errorTO;  
               }                           
   }
  // **************************************************************************************************************************************************** 
  public function insertIntoLogDetail($tsUid,
                                      $docUid,
                                      $tsNum,
                                      $prodUid,
                                      $documentQty,
                                      $amendedQty, 
                                      $resCode,
                                      $resUid) {
  
       $sql = "INSERT INTO load_scanner_log_detail (load_scanner_log_detail.load_scanner_log_uid,
                                                    load_scanner_log_detail.trip_sheet_header_uid,
                                                    load_scanner_log_detail.document_uid,
                                                    load_scanner_log_detail.product_uid,
                                                    load_scanner_log_detail.document_qty,
                                                     load_scanner_log_detail.amended_qty,                                     load_scanner_log_detail.reason_code,
                                     load_scanner_log_detail.reason_uid)
               VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $tsUid)       . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $tsNum)      . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $docUid)       . "',                       
                       '" . mysqli_real_escape_string($this->dbConn->connection, $prodUid)     . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $documentQty) . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $amendedQty)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $resCode)     . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $resUid)      . "')"; 

               $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
               if($this->errorTO->type == 'S') {
                    $this->dbConn->dbQuery("commit");
                    return $this->errorTO;     	
               } else {
                      echo "<br>";
                      echo $sql;
                      echo "<br>";
                      return $this->errorTO;  
               }                           
   }
  // **************************************************************************************************************************************************** 

  public function updateTripsheetFlag($tshUid) {
  
       $sql = "UPDATE .tripsheet_header th SET th.tripsheet_scan_log = 'Y'
               WHERE th.uid = " . mysqli_real_escape_string($this->dbConn->connection, $tshUid);               
               
               $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
               if($this->errorTO->type == 'S') {
                    $this->dbConn->dbQuery("commit");
                    return $this->errorTO;     	
               } else {
                      echo "<br>";
                      echo $sql;
                      echo "<br>";
                      return $this->errorTO;  
               }                           
   }
  // **************************************************************************************************************************************************** 
  public function extractScannedLoadsheets() {
  	
       $sql = "SELECT *
               FROM .load_scanner_log a
               WHERE 1";
               
       $scanResult = $this->dbConn->dbGetAll($sql);

       return $scanResult ;
  	
  }

  // **************************************************************************************************************************************************** 

}  
?>
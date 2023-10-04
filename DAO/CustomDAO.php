<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class CustomDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  // **************************************************************************************************************************************************** 

  public function getAllDiscountRecords() {
  	
      $sql = "SELECT *
              FROM ido_discount_codes idc
              WHERE idc.code IS NOT NULL";

      $aSR = $this->dbConn->dbGetAll($sql);
      return $aSR;
  
  }
  
  // **************************************************************************************************************************************************** 
  public function getOneDiscountRecord($disUid) {
  	
      $sql = "SELECT *
              FROM ido_discount_codes idc
              WHERE idc.uid = ". mysqli_real_escape_string($this->dbConn->connection, $disUid) ;

      $aSR = $this->dbConn->dbGetAll($sql);
      return $aSR;
  
  }
  
  // **************************************************************************************************************************************************** 
  public function getOneDiscountRecordByCode($disCode) {
  	
      $sql = "SELECT *
              FROM ido_discount_codes idc
              WHERE trim(idc.code) = '". mysqli_real_escape_string($this->dbConn->connection, trim($disCode)) ."';" ;

      $discountRec = $this->dbConn->dbGetAll($sql);
      return $discountRec;
  
  }      
  // **************************************************************************************************************************************************** 

  public function updateDiscountRecord($disUid, 
                                       $distype,
                                       $disCode,
                                       $disAmnt,
                                       $disStat) {
                                       	
      if(mysqli_real_escape_string($this->dbConn->connection, $distype) == 1) {$typeDes = 'Percentage';}  else {$typeDes = 'Fixed Amount';}                              	

      if(mysqli_real_escape_string($this->dbConn->connection, $disStat) == 'Active') {$aStat = 'A';}  else {$aStat = 'D';}                              	

	
      $sql = "UPDATE ido_discount_codes SET `type`  = '" . mysqli_real_escape_string($this->dbConn->connection, trim($typeDes)) . "', 
                                            `code`  = '" . mysqli_real_escape_string($this->dbConn->connection, trim($disCode)) . "', 
                                            `amount`= '" . mysqli_real_escape_string($this->dbConn->connection, trim($disAmnt)) . "', 
                                            `status`= '" . mysqli_real_escape_string($this->dbConn->connection, $aStat) . "'
              WHERE  `uid`= ". mysqli_real_escape_string($this->dbConn->connection, $disUid) ;

      $this->errorTO = $this->dbConn->processPosting($sql,"");
      $this->dbConn->dbQuery("commit");
             
      if($this->errorTO->type == 'S') {
            return $this->errorTO->type;     	
      } else {
            return "F"	;
      }
  }  
  // **************************************************************************************************************************************************** 
  public function insertDiscountRecord($distype,
                                       $disCode,
                                       $disAmnt,
                                       $disStat) {
                                       	
      if(mysqli_real_escape_string($this->dbConn->connection, $distype) == 1) {$typeDes = 'Percentage';}  else {$typeDes = 'Fixed Amount';}                              	

      if(mysqli_real_escape_string($this->dbConn->connection, $disStat) == 'Active') {$aStat = 'A';}  else {$aStat = 'D';}                              	


      $sql = "INSERT INTO `kwelanga_live`.`ido_discount_codes` (`type`, 
                                                                `code`, 
                                                                `amount`, 
                                                                `status`) 
              VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $typeDes) . "', 
                      '" . mysqli_real_escape_string($this->dbConn->connection, $disCode) . "', 
                      '" . mysqli_real_escape_string($this->dbConn->connection, $disAmnt) . "', 
                      '" . mysqli_real_escape_string($this->dbConn->connection, $aStat) . "')";

      $this->errorTO = $this->dbConn->processPosting($sql,"");
      $this->dbConn->dbQuery("commit");
             
      if($this->errorTO->type == 'S') {
            return $this->errorTO->type;     	
      } else {
            return "F"	;
      }
  }
  // **************************************************************************************************************************************************** 

}  
?>
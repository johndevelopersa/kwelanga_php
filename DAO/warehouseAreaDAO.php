<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/TransporterDAO.php');

class WarehouseAreaDao {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  	
	//************************************************
	public function getAreaDetails($filtersearch,$wareHouseCde)
{
	
	
	 $sql = "SELECT * 
FROM warehouse_area a
WHERE a.wh_area LIKE '%". mysqli_real_escape_string($this->dbConn->connection, $filtersearch) ."%' 
AND a.depot_uid =". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."";	

      $User = $this->dbConn->dbGetAll($sql);

     return $User;   	
	
	}
	//
public function delArea($areaUID)
{
	
	$sql = "UPDATE warehouse_area a 
          SET a.`status`='D'
          WHERE a.uid =". mysqli_real_escape_string($this->dbConn->connection, $areaUID) ."";	
     
     
     
     	   $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
            $this->errorTO->description="Failed To Delete Area <br><br> Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
           
           
           
            echo "<br>";
            return $this->errorTO;         	                  
        }
        $this->dbConn->dbQuery("commit"); 
       return $this->errorTO;  
	

	
	
	
	}
	
//
public function ModifyAreaDetails($areaUID){
	
	$sql = "SELECT* 
          FROM warehouse_area wa
          INNER JOIN depot d ON d.uid = wa.depot_uid
          WHERE wa.uid =". mysqli_real_escape_string($this->dbConn->connection, $areaUID) ."";	
	
	 $areaDET = $this->dbConn->dbGetAll($sql);

     return $areaDET;   	
	
	}
	public function UpdateWArea($areaUID,$AName,$depottid){
		 			$sql = "UPDATE warehouse_area a 
          SET a.wh_area ='". mysqli_real_escape_string($this->dbConn->connection, $AName) ."',
              a.depot_uid =". mysqli_real_escape_string($this->dbConn->connection, $depottid) ." 
          WHERE a.uid =". mysqli_real_escape_string($this->dbConn->connection, $areaUID) ."";	
     
     
     
     	   $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
            $this->errorTO->description="Failed To Delete Area <br><br> Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
           
            echo "<br>";
            return $this->errorTO;         	                  
        }
        $this->dbConn->dbQuery("commit"); 
       return $this->errorTO;
		}
		
	public function ADDArea($AName,$depottid){
		 		
	$sql = "INSERT INTO warehouse_area (wh_area, depot_uid)
               VALUES ('". mysqli_real_escape_string($this->dbConn->connection, $AName) ."',". mysqli_real_escape_string($this->dbConn->connection, $depottid) .")";	
     
     
     
     	   $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
            $this->errorTO->description="Failed To Delete Area <br><br> Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
            
            echo "<br>";
            return $this->errorTO;         	                  
        }
        $this->dbConn->dbQuery("commit"); 
       return $this->errorTO;
		}	
		
		public function WarehouseDet($userUId,$principalId){
			
	$sql = "SELECT *
          FROM user_principal_depot upd
          INNER JOIN depot d ON d.uid = upd.depot_id
           WHERE upd.user_id =". mysqli_real_escape_string($this->dbConn->connection, $userUId) ."
           AND upd.principal_id =". mysqli_real_escape_string($this->dbConn->connection, $principalId) ."";	

      $Whdetail = $this->dbConn->dbGetAll($sql);

     return $Whdetail;   	
			
			
			}
	public function getDelAreaDetails($filtersearch,$wareHouseCde)
{
	
	
	 $sql = "SELECT wda.uid AS uid, wda.wh_description AS wh_description, wa.uid AS warehouse_uid, wa.wh_area AS wh_area
FROM warehouse_delivery_area wda
INNER JOIN warehouse_area wa ON wa.uid = wda.wh_area
WHERE wa.depot_uid =". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ." ";	

      $User = $this->dbConn->dbGetAll($sql);

     return $User;   	
	
	}
	
	
	//
	public function ModifyDelAreaDetails($areaUID){

	$sql = "SELECT wda.uid AS uid, wda.wh_description AS wh_description, wa.uid AS warehouse_uid, wa.wh_area AS wh_area
FROM warehouse_delivery_area wda
INNER JOIN warehouse_area wa ON wa.uid = wda.wh_area
WHERE wda.uid =". mysqli_real_escape_string($this->dbConn->connection, $areaUID) ." ";	
	
	 $areaDET = $this->dbConn->dbGetAll($sql);

     return $areaDET;   	
	
	}
	public function WarehouseAreaDet($wareHouseCde){
	
	$sql = "SELECT *
FROM warehouse_area wa
WHERE wa.depot_uid =". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."";	
	
	 $areaDET = $this->dbConn->dbGetAll($sql);

     return $areaDET;   	
	
	}
	//UpdateDArea
	public function UpdateDArea($areaUID,$AName,$depottid){
		 		
	$sql = "UPDATE warehouse_delivery_area wda
          SET wda.wh_description = '". mysqli_real_escape_string($this->dbConn->connection, $AName ) ."',
          wda.wh_area = ". mysqli_real_escape_string($this->dbConn->connection, $depottid ) ."
          WHERE wda.uid =". mysqli_real_escape_string($this->dbConn->connection, $areaUID) ." ";	
     
     
     
     	   $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
            $this->errorTO->description="Failed To Delete Area <br><br> Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
           
            echo "<br>";
            return $this->errorTO;         	                  
        }
        $this->dbConn->dbQuery("commit"); 
       return $this->errorTO;
		}
	public function ADDDArea($AName,$depottid){
		 		
	$sql = "INSERT INTO warehouse_delivery_area(wh_description,wh_area)
          VALUES ('". mysqli_real_escape_string($this->dbConn->connection, $AName) ."',". mysqli_real_escape_string($this->dbConn->connection, $depottid) .")";	
     
     
     
     	   $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
            $this->errorTO->description="Failed To Delete Area <br><br> Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
          
            echo "<br>";
            return $this->errorTO;         	                  
        }
        $this->dbConn->dbQuery("commit"); 
       return $this->errorTO;
		}	
}
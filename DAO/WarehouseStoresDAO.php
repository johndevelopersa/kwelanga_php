<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/TransporterDAO.php');

class WarehouseStoreDao {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  //*****************************************************************************************************************************************
  
  public function getStoreDetails($filtersearch,$wareHouseCde)
{
	
	
	 $sql = "SELECT *
FROM warehouse_store_master wsm
WHERE wsm.del_point_name LIKE '%". mysqli_real_escape_string($this->dbConn->connection, $filtersearch) ."%'
AND wsm.depot_uid = ". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."";	

      $User = $this->dbConn->dbGetAll($sql);

     return $User;   	
	
	}
	
	public function getStore($UID)
	
	{
		 
      $sql = "SELECT * 
              FROM warehouse_store_master wsm
              LEFT JOIN depot d ON d.uid = wsm.depot_uid
              LEFT JOIN warehouse_delivery_area wda ON wda.uid = wsm.delivery_area
              WHERE wsm.uid =". mysqli_real_escape_string($this->dbConn->connection, $UID) ."";


      $Store = $this->dbConn->dbGetAll($sql);

     return $Store;  
		
		
		}
  public function DelAreaDet($wareHouseCde){

	$sql = "SELECT wsm.uid, wsm.wh_description 
          FROM warehouse_delivery_area wsm 
          INNER JOIN warehouse_area wa ON wa.uid = wsm.wh_area
          WHERE wa.depot_uid = ". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."
          ";	

      $Whdetail = $this->dbConn->dbGetAll($sql);

     return $Whdetail;   	
			
			
			}
			
	public function UpdateStore($DelArea,$Branch,$GLN,$Name,$Add1,$Add2,$Add3,$Lat,$Long,$UID,$Ndd,$Nod){
		$sql = "update warehouse_store_master wsm
            SET wsm.delivery_area = ". mysqli_real_escape_string($this->dbConn->connection, $DelArea) .", wsm.branch = ". mysqli_real_escape_string($this->dbConn->connection, $Branch) .", wsm.gln = ". mysqli_real_escape_string($this->dbConn->connection, $GLN) .", wsm.del_point_name = '". mysqli_real_escape_string($this->dbConn->connection, $Name) ."',wsm.add1 = '". mysqli_real_escape_string($this->dbConn->connection, $Add1) ."',wsm.add2 = '". mysqli_real_escape_string($this->dbConn->connection, $Add2) ."',wsm.add3 = '". mysqli_real_escape_string($this->dbConn->connection, $Add3) ."',wsm.latitude= '". mysqli_real_escape_string($this->dbConn->connection, $Lat) ."',wsm.longitude = '". mysqli_real_escape_string($this->dbConn->connection, $Long) ."',wsm.ndd = ". mysqli_real_escape_string($this->dbConn->connection, $Ndd) .",wsm.nod = ". mysqli_real_escape_string($this->dbConn->connection, $Nod) ."
            
            WHERE wsm.uid = ". mysqli_real_escape_string($this->dbConn->connection, $UID) ."";
            
            
        $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
            $this->errorTO->description="Failed To Modify Store <br><br> Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
            echo $sql;
            echo "<br>";
            return $this->errorTO;         	                  
        }
        $this->dbConn->dbQuery("commit"); 
       return $this->errorTO;
		
		}		
  //**************************************************8
  
  public function AddStore($wareHouseCde,$DelArea,$Branch,$GLN,$Name,$Add1,$Add2,$Add3,$Lat,$Long,$Ndd,$Nod){
		$sql = "INSERT INTO warehouse_store_master (depot_uid,delivery_area,branch,gln,del_point_name,add1,add2,add3,latitude,longitude,ndd,nod)
            VALUES (". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) .",". mysqli_real_escape_string($this->dbConn->connection, $DelArea) .",". mysqli_real_escape_string($this->dbConn->connection, $Branch) .",". mysqli_real_escape_string($this->dbConn->connection, $GLN) .",'". mysqli_real_escape_string($this->dbConn->connection, $Name) ."','". mysqli_real_escape_string($this->dbConn->connection, $Add1) ."','". mysqli_real_escape_string($this->dbConn->connection, $Add2) ."','". mysqli_real_escape_string($this->dbConn->connection, $Add3) ."','". mysqli_real_escape_string($this->dbConn->connection, $Lat) ."','". mysqli_real_escape_string($this->dbConn->connection, $Long) ."','". mysqli_real_escape_string($this->dbConn->connection, $Ndd) ."','". mysqli_real_escape_string($this->dbConn->connection, $Nod) ."')
            ";
            
            
        $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
            $this->errorTO->description="Failed To Add Store <br><br> Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
            echo $sql;
            echo "<br>";
            return $this->errorTO;         	                  
        }
        $this->dbConn->dbQuery("commit"); 
       return $this->errorTO;
		
		}		
  }
<?php
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


class greaterWarehouseAreaDAO {
	
	private $dbConn;

	function __construct($dbConn) 
	{

       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
	}
	
//*************************************************************************************************************************************************************************** 	
	
	public function getGreaterWarehouseArea($principalID, $depotID){
		
		$sql = "SELECT psm.uid AS 'Unique ID', 
									 d.uid AS 'Warehouse ID'
						FROM .principal_store_master psm 
						INNER JOIN .principal p ON psm.principal_uid = p.uid
						INNER JOIN .depot d ON psm.depot_uid = d.uid
						INNER JOIN .warehouse_store_master wsm ON d.uid = wsm.depot_uid
						INNER JOIN .warehouse_delivery_area wda ON wda.uid = wsm.uid 
						INNER JOIN .warehouse_area wa ON wa.uid = wsm.uid
						WHERE psm.uid = ". mysqli_real_escape_string($this->dbConn->connection, $principalID). "
						AND d.uid = ". mysqli_real_escape_string($this->dbConn->connection, $depotID). " ; " ;
						
						
		$depl = $this->dbConn->dbGetAll($sql);

    return $depl;
	
	}
	
//*************************************************************************************************************************************************************************** 		
	public function checkDeliveryArea($postDelAreaName){
	
		$sql =	"SELECT wda.wh_description,  
										wda.wh_ndd
						FROM .warehouse_delivery_area wda
						WHERE wda.wh_description = '" . mysqli_real_escape_string($this->dbConn->connection, $postDelAreaName). "' ; " ;
						
	 $depl = $this->dbConn->dbGetAll($sql);
	
	 return $depl;
	
	}
	
//***************************************************************************************************************************************************************************
	
	public function insertDeliveryArea($postDeliveryArea, $postWarehouseArea, $postNDD){
		
		$sql = "INSERT INTO `test_kwelanga1`.`warehouse_delivery_area` (`wh_description`, `wh_area`, `wh_ndd`)
						VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $postDeliveryArea) . "',
										'" . mysqli_real_escape_string($this->dbConn->connection, $postWarehouseArea) . "',
										'" . mysqli_real_escape_string($this->dbConn->connection, $postNDD) . "');";
									  
					$this->errorTO = $this->dbConn->processPosting($sql,"");
					
					if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                $this->errorTO->description="Uplift Record Create Failed : ".$this->errorTO->description;
                echo "<br>";
                echo $sql;
                echo "<br>";
                return $this->errorTO;         	                  
          }
          $this->dbConn->dbQuery("commit"); 
          return $this->errorTO; 				  
          
		}	
	
	
//***************************************************************************************************************************************************************************	
	
	 public function getGreaterDeliveryArea($depotID) {
	 	
	 	$sql = "SELECT wa.uid AS 'WhUid', 
	 								 wa.wh_area
						FROM .warehouse_area wa
						WHERE wa.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $depotID). " ; " ;						
						
	 $depl = $this->dbConn->dbGetAll($sql);
	
	 return $depl;
	 
	 }

//***************************************************************************************************************************************************************************	

	public function getNDD() {

	$sql = "SELECT d.uid AS 'dUID',
                   d.name
		      FROM day d 
          WHERE 1 ;" ;
					
	 $dep2 = $this->dbConn->dbGetAll($sql);
	
	 return $dep2;

	}	
//***************************************************************************************************************************************************************************	

	public function searchWarehouseDeliveryArea($postWhDeliveryArea, $postStat, $depotID){
		
		$WarehouseSQL = '';
		
		if (mysqli_real_escape_string($this->dbConn->connection, $postWhDeliveryArea) <> '0') {
        $WarehouseSQL = "AND wda.wh_description LIKE '%" . mysqli_real_escape_string($this->dbConn->connection, $postWhDeliveryArea) . "%' ";
    } 
    
  	 $sql = "SELECT wda.uid, 
		 								wda.wh_description, 
									 	wda.wh_area, 
										wda.wh_ndd, 
									 	wda.`status`,
									 	wa.depot_uid
						FROM .warehouse_delivery_area wda
            INNER JOIN .warehouse_area wa ON wda.wh_area = wa.uid 
						WHERE wa.depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $depotID).  "'
						AND wda.`status` = '" . mysqli_real_escape_string($this->dbConn->connection, $postStat).  "'
											".$WarehouseSQL;
											
											echo $sql;
		
		$depl = $this->dbConn->dbGetAll($sql);
	
		return $depl;
	
	}

//***************************************************************************************************************************************************************************	

	public function updateWarehouseArea($whDelAreaName, $whArea, $warehouseStatus, $whUid){
		
		$sql = "UPDATE warehouse_delivery_area wda SET wda.wh_description  = '" . mysqli_real_escape_string($this->dbConn->connection, $whDelAreaName) ."',
							 																		`status`  = '" . mysqli_real_escape_string($this->dbConn->connection, $warehouseStatus) ."'
						WHERE wda.uid  = " . mysqli_real_escape_string($this->dbConn->connection, $whUid) ."
						AND wda.wh_area = " . mysqli_real_escape_string($this->dbConn->connection, $whArea) . ";";
						
			 $this->errorTO = $this->dbConn->processPosting($sql,"");
       
       if($this->errorTO->type == 'S') {
             $this->dbConn->dbQuery("commit");
             return $this->errorTO;     	
       } else {
             echo $sql;
             return $this->errorTO;  
       } 

	}
	
//***************************************************************************************************************************************************************************	

	}
?>
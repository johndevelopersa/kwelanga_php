<?php
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class warehouseDeliveryPointDAO {
	
	private $dbConn;

	function __construct($dbConn) 
	{

       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
	}
	
//***************************************************************************************************************************************************************************

	public function getWhDelPoint($principalID, $depotID){
		
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

	public function checkDeliveryPoint($postDelPointName) {
	
	$sql = "SELECT wsm.uid, 
								 wsm.del_point_name, 
								 wsm.wh_delivery_area, 
								 wsm.depot_uid
				 FROM .warehouse_store_master wsm	
				 WHERE wsm.del_point_name = '" . mysqli_real_escape_string($this->dbConn->connection, $postDelPointName). "' ; " ;
				 
	 $depl = $this->dbConn->dbGetAll($sql);
	
	 return $depl;
	 
	}
	
//***************************************************************************************************************************************************************************	
	
	public function insertDeliveryPoint($postWhDeliveryPoint, $postWhDeliveryArea, $depotID){
		
		$sql = "INSERT INTO `test_kwelanga1`.`warehouse_store_master` (`del_point_name`, `wh_delivery_area`, `depot_uid`)
						VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $postWhDeliveryPoint) . "',
										'" . mysqli_real_escape_string($this->dbConn->connection, $postWhDeliveryArea) . "',
										'" . mysqli_real_escape_string($this->dbConn->connection, $depotID) . "');";
									  
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
	
	 public function getWarehouseDeliveryArea($depotID) {
	 	
	 	$sql = "SELECT  wsm.uid AS 'WhUID',
									  wsm.del_point_name,
									  wsm.wh_delivery_area,
									  wa.wh_area,
									  wsm.`status`,
									  wsm.depot_uid
						FROM .warehouse_store_master wsm
					  INNER JOIN .warehouse_area wa ON wsm.wh_delivery_area = wa.uid
						WHERE wsm.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $depotID). " ; " ;					
						
						
	 $depl = $this->dbConn->dbGetAll($sql);
	
	 return $depl;
	 
	 }	
	
//***************************************************************************************************************************************************************************		

	
		public function searchWarehouseDeliveryPoint($postWhDeliveryPoint, $postStat, $depotID){
		
		$WarehouseSQL = '';
		
		if (mysqli_real_escape_string($this->dbConn->connection, $postWhDeliveryPoint) <> '0') {
        $WarehouseSQL = "AND wsm.del_point_name LIKE '%" . mysqli_real_escape_string($this->dbConn->connection, $postWhDeliveryPoint) . "%' ";
    } 
    
  	 $sql = "SELECT wsm.uid AS 'WhUID',
								 		wsm.del_point_name,
									  wsm.wh_delivery_area,
									  wa.wh_area,
									  wsm.`status`,
									  wsm.depot_uid
						FROM .warehouse_store_master wsm	
						INNER JOIN .warehouse_area wa ON wsm.wh_delivery_area = wa.uid
						WHERE wsm.depot_uid =  '" . mysqli_real_escape_string($this->dbConn->connection, $depotID). "'
						AND wsm.`status` = '" . mysqli_real_escape_string($this->dbConn->connection, $postStat).  "'
											".$WarehouseSQL;            
            
		$depl = $this->dbConn->dbGetAll($sql);
	
		return $depl;
	
	}

//***************************************************************************************************************************************************************************		
	
		public function updateWarehouseDeliveryPoint($uid, $whDelPointName, $warehouseStatus, $whDelArea, $whUid){
		
		$sql = "UPDATE warehouse_store_master SET wsm.del_point_name  = '" . mysqli_real_escape_string($this->dbConn->connection, $whDelPointName) ."',
							 																		`status`  = '" . mysqli_real_escape_string($this->dbConn->connection, $warehouseStatus) ."'			 																	
						FROM warehouse_store_master wsm 
						WHERE  wsm.wh_delivery_area = '" . mysqli_real_escape_string($this->dbConn->connection, $whDelArea). "'
						AND  wsm.depot_uid  = '" . mysqli_real_escape_string($this->dbConn->connection, $whUid). "'
						AND  wsm.uid  = '" . mysqli_real_escape_string($this->dbConn->connection, $uid)."';";
						
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
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


class WarehouseStoreLinkDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  //********************************************************
  public function getStoreUid($Type,$search,$wareHouseCde){
  	
       if($Type=='B') { 	     
            $Sel = "WHERE wsm.branch = ". mysqli_real_escape_string($this->dbConn->connection, $search) ."";
       } elseif($Type=='G') {
       	    $Sel = "WHERE wsm.gln = ". mysqli_real_escape_string($this->dbConn->connection, $search) ."";	
       }elseif($Type=='U')  {
       	    $Sel = "WHERE wsm.uid = ". mysqli_real_escape_string($this->dbConn->connection, $search) ."";	
        }else {
       		
       		
       }
                  
                 
       $sql = "SELECT wsm.uid, wsm.del_point_name
               FROM warehouse_store_master wsm
               ".$Sel." AND wsm.depot_uid = ". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."";	

       $StoreUID = $this->dbConn->dbGetAll($sql);

                   
                   
                   $this->errorTO = $this->dbConn->processPosting($sql,"");
            
      
        return $StoreUID;
	
                   
                   }   	

 //********************************************************************************************************************************************************
 


 public function getStoreUidName($NameSearch,$wareHouseCde)
{
  $sql = "SELECT wsm.uid, wsm.del_point_name
          FROM warehouse_store_master wsm
          WHERE wsm.del_point_name LIKE  '%". mysqli_real_escape_string($this->dbConn->connection, $NameSearch) ."%' AND wsm.depot_uid = ". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ." ";	

      $StoreUID = $this->dbConn->dbGetAll($sql);

     return $StoreUID;   	
}

//**********************************************************************************************************************************************************
public function getPrincipals($userUId,$wareHouseCde,$StoreUID){

$sql = "SELECT p.uid AS principal_uid, 
        if (p.short_name IS NULL , p.name, p.short_name) AS principal_name, 
        psm.uid AS store_uid, 
        psm.deliver_name AS store_name
        FROM user_principal_depot upd
        LEFT JOIN principal_warehouse_store_link  pl ON pl.principal_uid =upd.principal_id
                                             AND pl.depot_uid = upd.depot_id
                                             AND pl.warehouse_store_master_uid = ". mysqli_real_escape_string($this->dbConn->connection, $StoreUID) ."
                                             AND pl.`status`= 'Y'
        LEFT JOIN principal p ON p.uid = upd.principal_id
        LEFT JOIN principal_store_master psm ON psm.uid= pl.principal_store_master_uid                                             
        LEFT JOIN warehouse_store_master wsm ON wsm.uid = pl.warehouse_store_master_uid
        WHERE upd.user_id = ". mysqli_real_escape_string($this->dbConn->connection, $userUId) ." 
        AND upd.depot_id = ". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."
        
        ORDER BY psm.deliver_name DESC
        ";
 
 $Principals = $this->dbConn->dbGetAll($sql);

     return $Principals;   	
}
//*************************************************************

Public function getPrincipalStoreUID($Type,$search,$principalUID,$wareHouseCde){


       if($Type=='B') { 	     
            $Sel = "WHERE psm.branch_code = ". mysqli_real_escape_string($this->dbConn->connection, $search) ." AND psm.principal_uid =". mysqli_real_escape_string($this->dbConn->connection, $principalUID) ."";
       } elseif($Type=='G') {
       	    $Sel = "WHERE psm.ean_code = ". mysqli_real_escape_string($this->dbConn->connection, $search) ." AND psm.principal_uid =". mysqli_real_escape_string($this->dbConn->connection, $principalUID) ."";	
       }elseif($Type=='U')  {
       	    $Sel = "WHERE psm.branch_code = ". mysqli_real_escape_string($this->dbConn->connection, $search) ." AND psm.principal_uid =". mysqli_real_escape_string($this->dbConn->connection, $principalUID) ."";	
        }else {
       		
       		
       }
                  
                 
       $sql = "SELECT * 
               FROM principal_store_master psm
               ".$Sel." AND psm.depot_uid = ". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."";	

       $StoreUID = $this->dbConn->dbGetAll($sql);

                   return $StoreUID;
                   }   	


public function getWarehouseStoreName($wstoreID){
	
	$sql =      "SELECT * 
               FROM warehouse_store_master wsm
               WHERE wsm.uid = ". mysqli_real_escape_string($this->dbConn->connection, $wstoreID) ."";	

       $warehousestore = $this->dbConn->dbGetAll($sql);

                   return $warehousestore;
                   }   	
//*********************************************************************************************	
public function LinkStores($wareHouseCde,$principalUID,$pStoreUID,$wstoreID){
	
	$sql =        "INSERT IGNORE INTO principal_warehouse_store_link (depot_uid,principal_uid,principal_store_master_uid,warehouse_store_master_uid)
                 VALUES (". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) .",". mysqli_real_escape_string($this->dbConn->connection, $principalUID) .",". mysqli_real_escape_string($this->dbConn->connection, $pStoreUID) .",". mysqli_real_escape_string($this->dbConn->connection, $wstoreID) .")";
	
	 
	 $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
            $this->errorTO->description="Failed To Add Link <br><br> Contact Kwelanga Support".$this->errorTO->description;
           
            return $this->errorTO;         	                  
        }
        $this->dbConn->dbQuery("commit"); 
       return $this->errorTO;
	
	
	
	
	}	
	//********************************************************************************
	public function CheckLink($wareHouseCde,$pStoreUID,$wstoreID){
	
	    $sql = "SELECT *
              FROM principal_warehouse_store_link pl
              WHERE pl.depot_uid = ". mysqli_real_escape_string($this->dbConn->connection, $wareHouseCde) ."
              AND pl.principal_store_master_uid = ". mysqli_real_escape_string($this->dbConn->connection, $pStoreUID) ."
              AND pl.warehouse_store_master_uid = ". mysqli_real_escape_string($this->dbConn->connection, $wstoreID) ."";	

      $StoreUID = $this->dbConn->dbGetAll($sql);
    
     return $StoreUID;   	
	
	
	}
	//************************************************************
	
	
	
	public function getPStoreUidName($NameSearch,$principalUID){
	
	    $sql = "SELECT psm.uid, psm.deliver_name
              FROM principal_store_master psm
              WHERE psm.deliver_name LIKE  '%". mysqli_real_escape_string($this->dbConn->connection, $NameSearch) ."%'  AND psm.principal_uid =". mysqli_real_escape_string($this->dbConn->connection, $principalUID) ." ";	

      $StoreUID = $this->dbConn->dbGetAll($sql);
    
     return $StoreUID;   	
	
	
	}
	//************************************************************
	public function UpdateLink($LinkUid,$LinkStatus){
		
		$sql = "UPDATE principal_warehouse_store_link pl
            SET pl.`status`= '". mysqli_real_escape_string($this->dbConn->connection,$LinkStatus) ."'
            WHERE pl.uid =". mysqli_real_escape_string($this->dbConn->connection,$LinkUid) ."";
	  
	  
	  $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
            $this->errorTO->description="Failed To Delete Link <br><br> Contact Kwelanga Support".$this->errorTO->description;
           
            return $this->errorTO;         	                  
        }
        $this->dbConn->dbQuery("commit"); 
       return $this->errorTO;
		
		}
	
	
	
}


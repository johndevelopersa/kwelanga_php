<?php


class IntegrationDAO {
	
	private $dbConn;
	private $databaseKey = "_integration_json";
	
	function __construct($dbConn) {		
		$this->dbConn = $dbConn;
	}
	
	public function getIntegrationKey($type){
		return strtolower(trim($type)) . "{$this->databaseKey}";
	}
	
	//useful for said processors
	public function getAllByType($type){
				 
		$typeKey = $this->getIntegrationKey($type);	
		$records = $this->dbConn->dbGetAll("select principal_uid, `$typeKey` from principal_preference WHERE length(`$typeKey`) > 10");				
		
		$result = [];
		if(is_array($records) && count($records) > 0){
			//index by principal
			foreach($records as $row){
				$typeDataArr = json_decode($row[$typeKey], true);
				if(is_array($typeDataArr) && count($typeDataArr) > 0){
					$result[$row['principal_uid']] = $typeDataArr;
				}
			}
		}
		return $result;
	}
	
	//used for listing of integrations for said principal
	public function getAllByPrincipal($principalId) : array {
		
		$records = $this->dbConn->dbGetAll("select * from principal_preference where principal_uid = ".$principalId." LIMIT 1");			
		$result = [];
		
		if(is_array($records) && isset($records[0])){
			foreach($records[0] as $key => $value){
				  //does the key contain a integration value?
				  if(strpos($key,$this->databaseKey)!==false){
					
					  //is there actual integration for this thingie?
					  $appName = str_replace("_integration_json","",$key);
					  $appName = strtolower($appName);
					  
					  if(!is_dir(__DIR__ . "/" . $appName)){
						  continue;
					  }
					  
					  //add to list of integrations
					  $result[$appName] = json_decode($value, true);
				  }
			}
		}
		
		return $result;
	}
	
	//useful for getting array of type
	public function getForPrincipalByType($principalId, $type) : array {
		$records = $this->dbConn->dbGetAll("select * from principal_preference where principal_uid = ".$principalId);
		$typeKey = $this->getIntegrationKey($type);	
		if(!is_array($records) || is_array($records) && !isset($records[0]) || !isset($records[0][$typeKey])){
			return [];    
		}		
		$typeArr = json_decode($records[0][$typeKey], true);				
		if(!count($typeArr) || !isset($typeArr['title'])){
			return [];   
		}		
		return $typeArr;
	}
	
	public function save($principalId, $type, $arr){
		
		$arr['updated'] = date("Y-m-d H:i:s");
		if(!isset($arr['created'])){
			$arr['created'] = date("Y-m-d H:i:s");
		}

		$key = $this->getIntegrationKey($type);		

		$query = "UPDATE principal_preference 
					SET `{$key}` = '" . mysqli_real_escape_string($this->dbConn->connection, json_encode($arr, JSON_PRETTY_PRINT)) . "' 
				  WHERE principal_uid = '{$principalId}' LIMIT 1";
		return $this->dbConn->processPosting($query, "");		
	}
	
	public function remove($principalId, $type){			
		$key = $this->getIntegrationKey($type);		
		$query = "UPDATE principal_preference 
					SET `{$key}` = '{}' 
				  WHERE principal_uid = '{$principalId}'
				  LIMIT 1";			
		return $this->dbConn->processPosting($query, "");		
	}
	
}
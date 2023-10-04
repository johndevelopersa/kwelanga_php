<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class SynchronisationDAO {
	private $dbConn;
	
	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }
	
	
	public function getDownloadCounts() {
		$sql="select 'Principals' name, 'PRIN' source, if(last_synch_status is null,'X',last_synch_status) last_synch_status, count(*) cnt
				from   principal
				where  if(last_synch_status is null,'X',last_synch_status) in ('X','E','U')
				group  by if(last_synch_status is null,'X',last_synch_status)";
				
		$this->dbConn->dbQuery($sql);

		$arr=array();
		// make sure all categories are enforced
		$arr["PRIN"]["name"] = "Principals";
		while($row = mysql_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[$row["source"]][$row["last_synch_status"]] = $row["cnt"];
		}

		return $arr;
	}
	
	public function getDownloadPrincipalErrors() {
		$sql="select last_synch_msg, count(*) cnt
				from  principal 
				where  last_synch_status = 'E'";
				
		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysql_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[] = $row;
		}

		return $arr;
	}
	
	
	public function getPrincipalsForSynching($lastUpdated,$rowsToReturn) {
		$sql="select *
			  from   principal
			  where  (last_updated <= '".mysql_real_escape_string($lastUpdated)."' or last_updated is null)
			  and    (last_synch_status = 'U' or last_synch_status is null)
			  LIMIT ".mysql_real_escape_string($rowsToReturn);
			  			
		$this->dbConn->dbQuery($sql);
		
		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysql_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}
		
		return $arr;
	}
	
}	
?>

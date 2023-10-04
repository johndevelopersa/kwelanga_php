<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class GlacialDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// **************************************************************************************************************************************************** 
  public function getUserPricipalList($UserUid) {
  	
       $sql = "SELECT DISTINCT(p.uid) AS 'principal_uid', 
                      p.name AS 'principal'
               FROM principal p
               INNER JOIN user_principal_depot upd ON upd.principal_id = p.uid
               WHERE upd.user_id = " . mysqli_real_escape_string($this->dbConn->connection, $UserUid) . " 
               AND   p.`status` = 'A'
               ORDER BY p.name ;" ;
               
//             echo $sql;

       $uPList = $this->dbConn->dbGetAll($sql);

       return $uPList ;
  }
// **************************************************************************************************************************************************** 
  public function getPrincipalChainList($principalUid) {
  	
       $sql = "SELECT DISTINCT(p.name) AS 'principal_name',
                               p.uid   AS 'principal_uid',
                               pcm.uid  AS 'chain_uid', 
                               pcm.description AS 'chain'
               FROM principal_chain_master pcm,
                    principal p 
               WHERE pcm.principal_uid = p.uid
               AND   pcm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . " 
               AND   pcm.`status` = 'A';" ;
               
       $uCList = $this->dbConn->dbGetAll($sql);

       return $uCList ;
  }
// **************************************************************************************************************************************************** 
  public function getActiveWhList($principalUid, $chainUid) {
  	
       $sql = "SELECT SUBSTR(je.page_params,1,POSITION('#' IN je.page_params) -1)                                 AS 'Wh_List', 
                      SUBSTR(je.page_params,POSITION('#' IN je.page_params) +1,
		                                        POSITION('+' IN je.page_params) - POSITION('#' IN je.page_params)-1 ) AS 'ch_List', 
                      SUBSTR(je.page_params,POSITION('+' IN je.page_params) +1,2 ) AS 'status_List', je.uid       AS 'JE_UID' 
               FROM job_execution je 
               WHERE je.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . " 
               AND je.name = 'OmniImports' 
               AND SUBSTR(je.page_params,POSITION('#' IN je.page_params) +1,4) = " . mysqli_real_escape_string($this->dbConn->connection, $chainUid) . ";" ;
               
       $aWList = $this->dbConn->dbGetAll($sql);

       return $aWList ;  	
  	
  	}

// **************************************************************************************************************************************************** 
  public function getWarehouseList($principalUid, $UserUid, $pchain) {

       $sql = "SELECT d.name AS 'Warehouse',
                      d.uid  AS 'Warehouse_uid',
                      p.name AS 'principal_name',
                      pcm.description AS 'chain_name'
               FROM .depot d
               INNER JOIN user_principal_depot upd ON upd.depot_id = d.uid
               INNER JOIN principal p on p.uid = upd.principal_id
               INNER JOIN principal_chain_master pcm on p.uid = pcm.principal_uid and pcm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $pchain) . "
               WHERE upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
               AND   upd.user_id      = " . mysqli_real_escape_string($this->dbConn->connection, $UserUid) . ";" ;
               
               $whList = $this->dbConn->dbGetAll($sql);

               return $whList ;
  	}
// **************************************************************************************************************************************************** 
  public function postUpdateParams($je_uid, $je_params)  {
  	
  	     $sql = "UPDATE job_execution je SET je.page_params = '" . mysqli_real_escape_string($this->dbConn->connection, $je_params) . "'
                 WHERE je.uid = " . mysqli_real_escape_string($this->dbConn->connection, $je_uid) . ";" ;
  	
        $this->errorTO = $this->dbConn->processPosting($sql,"");
        $this->dbConn->dbQuery("commit");
        
        if($this->errorTO->type == 'S') {
            $this->dbConn->dbQuery("commit");   	
        } else {
        	  echo "<br>";
        	  echo $sql;
        	  echo "<br>";
        }	 
        
        return $this->errorTO;
  }	
// **************************************************************************************************************************************************** 
}  
?>
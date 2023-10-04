<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


class UserPermissionsDOA {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
//************************************************************************************************************************************************************
  public function GetUsers($Search){
   
           $sql = "SELECT*
                   FROM ".iDATABASE.".users u
                   WHERE u.username LIKE '%". mysqli_real_escape_string($this->dbConn->connection, $Search) ."%'";

           $Users = $this->dbConn->dbGetAll($sql);

    return $Users;	
  	
  }
//************************************************************************************************************************************************************
  public function UserDetails($userID){
  
  	        $sql = "SELECT*
                    FROM ".iDATABASE.".users u
                    WHERE u.uid =". mysqli_real_escape_string($this->dbConn->connection, $userID) ."";

            $User = $this->dbConn->dbGetAll($sql);

     return $User;	  	
  }





//************************************************************************************************************************************************************
     public function GetCUsers($SearchC,$Cat){
   
            $sql = "SELECT*
                    FROM ".iDATABASE.".users u
                    WHERE u.username LIKE '%". mysqli_real_escape_string($this->dbConn->connection, $SearchC) ."%'
                    AND u.category = '". mysqli_real_escape_string($this->dbConn->connection, $Cat) ."'";

            $CUsers = $this->dbConn->dbGetAll($sql);
            
     return $CUsers;	
  	
  }

//************************************************************************************************************************************************************
     public function Principals($CUserID){
   
            $sql = "SELECT p.name AS 'principal_name', d.name AS 'depot_name', upd.uid
                    FROM user_principal_depot upd
                    INNER JOIN depot d ON d.uid = upd.depot_id
                    INNER JOIN principal p ON p.uid = upd.principal_id
                    WHERE upd.user_id = ". mysqli_real_escape_string($this->dbConn->connection, $CUserID) ."
                    ORDER BY d.name
                    ";

            $Principal = $this->dbConn->dbGetAll($sql);
           
     return $Principal;	
  	 }
  	 
//************************************************************************************************************************************************************
     public function UpdateADepots($NUserID,$CUserID){
   
            $sql = "INSERT IGNORE INTO user_principal_depot (user_principal_depot.user_id,
                    user_principal_depot.depot_id,
                    user_principal_depot.principal_id)
                    SELECT ". mysqli_real_escape_string($this->dbConn->connection, $NUserID) .", upd.depot_id, upd.principal_id
                    FROM user_principal_depot upd
                    WHERE upd.user_id = ". mysqli_real_escape_string($this->dbConn->connection, $CUserID) .";";
    
   
  	   	 $this->errorTO = $this->dbConn->processPosting($sql,"");

        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description="Failed To Update Depots Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
            echo $sql;
            echo "<br>";
            return $this->errorTO;
        }
        $this->dbConn->dbQuery("commit");
       return $this->errorTO;

  	 
}
//************************************************************************************************************************************************************

     public function UpdateSDepots($NUserID,$CUserID,$List){

           $sql = " INSERT IGNORE INTO user_principal_depot (user_principal_depot.user_id,
                    user_principal_depot.depot_id,
                    user_principal_depot.principal_id)
                    SELECT ". mysqli_real_escape_string($this->dbConn->connection, $NUserID) .", upd.depot_id, upd.principal_id
                    FROM user_principal_depot upd
                    WHERE upd.user_id = ". mysqli_real_escape_string($this->dbConn->connection, $CUserID) ."
                    AND upd.uid IN(". mysqli_real_escape_string($this->dbConn->connection, $List) .")";
    
       
  	   	 $this->errorTO = $this->dbConn->processPosting($sql,"");

        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description="Failed To Update Depots Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
            echo $sql;
            echo "<br>";
            return $this->errorTO;
        }
        $this->dbConn->dbQuery("commit");
       return $this->errorTO;
}
//************************************************************************************************************************************************************
  public function UpdateARoles($NUserID,$CUserID){

           $sql = "INSERT ignore INTO user_role (user_role.user_id,
                   user_role.role_id,
                   user_role.entity_uid)
                   SELECT ". mysqli_real_escape_string($this->dbConn->connection, $NUserID) .", a.role_id, a.entity_uid
                   FROM user_role a
                   WHERE a.user_id = ". mysqli_real_escape_string($this->dbConn->connection, $CUserID) ."";
    
       
  	   	 $this->errorTO = $this->dbConn->processPosting($sql,"");
        
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description="Failed To Update Roles Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
            echo $sql;
            echo "<br>";
            return $this->errorTO;
        }
        $this->dbConn->dbQuery("commit");
       return $this->errorTO;  
     }
//************************************************************************************************************************************************************
  public function UpdateSRoles($NUserID,$CUserID,$List){

           $sql = "INSERT ignore INTO user_role (user_role.user_id,
                   user_role.role_id,
                   user_role.entity_uid)
                   SELECT ". mysqli_real_escape_string($this->dbConn->connection, $NUserID) .", a.role_id, a.entity_uid
                   FROM user_role a
                   WHERE a.user_id = ". mysqli_real_escape_string($this->dbConn->connection, $CUserID) ."
                   AND a.entity_uid IN (". mysqli_real_escape_string($this->dbConn->connection, $List) .");";
    
       
  	   	 $this->errorTO = $this->dbConn->processPosting($sql,"");
        
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description="Failed To Update Roles Contact Kwelanga Support".$this->errorTO->description;
            echo "<br>";
            echo $sql;
            echo "<br>";
            return $this->errorTO;
        }
        $this->dbConn->dbQuery("commit");
       return $this->errorTO; 
     }
//************************************************************************************************************************************************************

//************************************************************************************************************************************************************




public function Getprincipal($CUserID){
   
            $sql = "SELECT DISTINCT p.uid,p.name
                    FROM user_role ur
                    INNER JOIN principal p ON p.uid = ur.entity_uid
                    WHERE ur.user_id = ". mysqli_real_escape_string($this->dbConn->connection, $CUserID) ."";

            $Cprincipal = $this->dbConn->dbGetAll($sql);
           
     return $Cprincipal;	
  	 }
  	 



}
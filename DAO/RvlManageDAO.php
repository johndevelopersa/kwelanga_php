<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class RvlManagmentDAO {
	
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
    }
    
// ******************************************************************************************************************
    	public function getRvlSingleRep($repUid) {

      		  $sql = "SELECT psr.uid, psr.first_name, psr.email_addr, psr.`status`
                    FROM .principal_sales_representative psr
                    WHERE psr.uid = " . mysqli_real_escape_string($this->dbConn->connection, $repUid)  . "
                    ORDER BY psr.first_name;";
                    
            return $this->dbConn->dbGetAll($sql);
	}
// ******************************************************************************************************************

    	public function getRvlPrincipalReps($principalUId, $rStatus, $tSel) {
    		
            if(trim($rStatus) == 'A') {
                    $statLine = "AND psr.`status` = 'A'";
  	        } else {
                    $statLine = "AND psr.`status` = '" . mysqli_real_escape_string($this->dbConn->connection, $rStatus)  . "'";
           	}

           	if(trim($tSel) == '') {
           	        $tSelLine = "";
           	} else {
           	        $tSelLine = "AND psr.first_name like '%" . mysqli_real_escape_string($this->dbConn->connection, $tSel)  . "%'";
           	}

      		  $sql = "SELECT psr.uid, psr.first_name, psr.email_addr, psr.`status`
                    FROM .principal_sales_representative psr
                    WHERE psr.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUId)  . " "
                    . $statLine . " "
                    . $tSelLine . "
                    ORDER BY psr.first_name;";
                    
            return $this->dbConn->dbGetAll($sql);
	}

// ******************************************************************************************************************
  public function updatePsr($tUid, $tName, $nStatus, $rEmail){
  	
       $sql = "UPDATE `principal_sales_representative` SET `first_name`= '" . mysqli_real_escape_string($this->dbConn->connection, $tName) ."', 
                                                           `email_addr`= '" . mysqli_real_escape_string($this->dbConn->connection, $rEmail) ."', 
                                                           `status`    = '" . mysqli_real_escape_string($this->dbConn->connection, $nStatus) ."' 
               WHERE  `uid`= " . mysqli_real_escape_string($this->dbConn->connection, $tUid) .";";
       
       
       $this->errorTO = $this->dbConn->processPosting($sql,"");
       
       if($this->errorTO->type == 'S') {
             $this->dbConn->dbQuery("commit");
             return $this->errorTO;     	
       } else {
             echo $sql;
             return $this->errorTO;  
       } 
  }

// ******************************************************************************************************************
  public function checkRepname($principalId, $tname) {
      
    $sql = "SELECT psr.first_name 
            FROM .principal_sales_representative psr
            WHERE psr.principal_uid = '". mysqli_real_escape_string($this->dbConn->connection, $principalId)  . "'
            AND  psr.first_name LIKE  '%". mysqli_real_escape_string($this->dbConn->connection, $tname)    . "%'
            limit 1;";
        	
    $tNam = $this->dbConn->dbGetAll($sql);

    return $tNam;
  }
  
// ******************************************************************************************************************
  public function addSalesRep($principalId, $postTNAME, $postREMAIL, $rCDE="") {
  
        $sql = "INSERT INTO `principal_sales_representative` (`principal_uid`, 
                                                            `rep_code`     , 
                                                            `first_name`   , 
                                                            `surname`      , 
                                                            `email_addr`   , 
                                                            `email_add2`)
              VALUES (" . mysqli_real_escape_string($this->dbConn->connection, $principalId)  . ", 
                     '" . mysqli_real_escape_string($this->dbConn->connection, $rCDE)         . "', 
                     '" . mysqli_real_escape_string($this->dbConn->connection, $postTNAME)    . "', 
                     '" . mysqli_real_escape_string($this->dbConn->connection, $postTNAME)    . "', 
                     '" . mysqli_real_escape_string($this->dbConn->connection, $postREMAIL)   . "',
                     '" . mysqli_real_escape_string($this->dbConn->connection, $postREMAIL)   . "');"; 	
 
              $this->errorTO = $this->dbConn->processPosting($sql,"");

              if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) { 
                     echo "<br>"; 
                     echo $sql;
                     echo "<br>";
                     $this->errorTO->description="Rep Load Failed : ". $sql .$this->errorTO->description;
                     return $this->errorTO;         	                  
              } else {
                     $this->dbConn->dbQuery("commit");
                     $this->errorTO->description="Rep Load Successful";
                     return $this->errorTO;                
              } 
  } 
// ******************************************************************************************************************

  
  public function getAgedStockStoreList($startDate, $endDate, $principalId, $rep, $stor, $docNo,$area ) {
  	
       if(trim($rep) <> '') {
            $repVar  = "AND   psr.first_name LIKE '%"     . mysqli_real_escape_string($this->dbConn->connection, $rep)   . "%'";
       } else {
            $repVar  = '';  
       } 	
       
       if(trim($stor) <> '') {
            $storVar = "AND   psm.deliver_name LIKE '%"   . mysqli_real_escape_string($this->dbConn->connection, $stor)  . "%'"; 	
       } else {
            $storVar = '';
       } 	
       
       if(trim($docNo) <> '') {
            $docVar  = "AND   dm.document_number LIKE '%" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . "%'";	
       } else {
            $docVar  = '';	
       } 	              
       
       if(trim($area) <> '') {
            $araVar  = "AND   a.description LIKE '%" . mysqli_real_escape_string($this->dbConn->connection, $area)  . "%'";  	
       } else {
            $araVar  = '';	
       }
       
       $sql = "SELECT s.description AS 'Status',
                      a.description AS 'Area',
                      dh.invoice_date,
                      dm.uid as 'dm_uid',
                      dm.document_number,
                      psm.deliver_name AS 'Store',
                      psr.first_name
               FROM document_master dm
               INNER JOIN document_header dh ON dh.document_master_uid = dm.uid
               LEFT JOIN `status` s ON s.uid = dh.document_status_uid
               LEFT JOIN principal_store_master psm ON psm.uid = dh.principal_store_uid
               LEFT JOIN .principal_chain_master pcm ON pcm.uid = psm.principal_chain_uid
               LEFT JOIN .principal_sales_representative psr ON psr.uid = psm.principal_sales_representative_uid
               LEFT JOIN .area a ON a.uid = psm.area_uid
               WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               AND   dh.invoice_date BETWEEN '" . mysqli_real_escape_string($this->dbConn->connection, $startDate) . "' 
                                     AND     '" . mysqli_real_escape_string($this->dbConn->connection, $endDate) . "'
               " . $repVar  . "                      
               " . $storVar . "                      
               " . $docVar  . "                      
               " . $araVar  . " 
               AND dh.document_status_uid = " . DST_UNACCEPTED . "   
               ORDER BY dh.invoice_date ;" ; 	
               
               // echo $sql;
               
       $repl = $this->dbConn->dbGetAll($sql);

       return $repl;

  	}

// ******************************************************************************************************************
  function update($sffUid, $psmUID, $sfdValue) {

  	
              $this->errorTO = $this->dbConn->processPosting($dsql,"");

              if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) { 
                  echo "<br>"; 
                   echo $dsql;
                  echo "<br>";
                     $this->errorTO->description="Transaction Delete Failed : ". $dsql .$this->errorTO->description;
                     return $this->errorTO;         	                  
              } else {
                     $this->dbConn->dbQuery("commit");
                     $this->errorTO->description="Transaction Delete Successful";
                     return $this->errorTO;                
              }  	
   }
 
// ******************************************************************************************************************
  public function getRepListNew($principalId) {

    $sql = "SELECT *
             FROM .principal_sales_representative psr
             WHERE psr.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
             AND   psr.`status` = 'A'
             ORDER BY psr.first_name";
            
    $repl = $this->dbConn->dbGetAll($sql);

    return $repl;
  }
}
?>



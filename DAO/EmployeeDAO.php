<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/EmployeeDAO.php');

class EmployeeDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// **************************************************************************************************************************************************** 
  public function getEmployeeDetails($searchVal, $searchBy, $depId, $showDeleted) {
  	
  	   if($searchVal == '') {
  	   	    $sVar = "1";
  	   } else {
           if(mysqli_real_escape_string($this->dbConn->connection, $searchBy) == 1) {
               $sVar = "ed.name LIKE '%"  . mysqli_real_escape_string($this->dbConn->connection, $searchVal) . "%'" ;
           } elseif(mysqli_real_escape_string($this->dbConn->connection, $searchBy) == 2) {
           	   $sVar = "ed.code like '%"  . mysqli_real_escape_string($this->dbConn->connection, $searchVal) . "%'" ;
  	       } else {
  	           $eC = trim(substr($searchVal,0, strpos($searchVal,'-') - 1));
  	             
               $sVar = "ed.code = '"  . mysqli_real_escape_string($this->dbConn->connection, $eC) ."'";
  	       }
  	   }

  	   if($showDeleted == 'A') {
  	   	    $sdel = " ed.`status` = 'D'";
  	   } else {
  	   	    $sdel = " ed.`status` = 'A'";
  	   }
  	
       $sql = "SELECT ed.uid AS 'empUid',
                      ed.depot_uid,
                      ed.code,
                      ed.name,
                      ed.id_number,
                      ed.duty,
                      ejf.uid AS 'jobUid',
                      ejf.job_description,
                      ed.`status`,
                      ed.comments
               FROM " . iDATABASE . ".employee_details ed
               LEFT JOIN employee_job_function ejf on ejf.uid = ed.duty
               WHERE " . $sVar . "
               AND ed.depot_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $depId) . "
               AND " . $sdel . ";";

       $loadd = $this->dbConn->dbGetAll($sql);
       
       return $loadd;
  }

// **************************************************************************************************************************************************** 


  public function insertEmployeeRecords($eUid, $etim, $oldJob, $ecom, $newJob, $updatedBy, $depId) {
  	
  	   if($newJob == 'Change Employee Job') {
  	       $ejob = $oldJob; 
  	   } else {
  	       $ejob = $newJob; 	
  	       
  	       $sql = "UPDATE " . iDATABASE . ".`employee_details` SET `duty`='"  . mysqli_real_escape_string($this->dbConn->connection, $newJob)  . "' 
  	               WHERE  `uid`="  . mysqli_real_escape_string($this->dbConn->connection, $eUid)  . ";" ;
  	               
           $this->errorTO = $this->dbConn->processPosting($sql,"");
            
           if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                  $this->errorTO->description="Error Updating Employee Job : ".$this->errorTO->description;
                  echo "<br>";
                  echo $sql;
                  echo "<br>";
                  return $this->errorTO;         	                  
            }
            
            $this->dbConn->dbQuery("commit"); 
            return $this->errorTO;   	  	               
  	       
  	   }
  	    $sql = "INSERT INTO " . iDATABASE . " . employee_file_records (employee_file_records.employee_uid,
                                                    employee_file_records.record_time,
                                                    employee_file_records.employee_job,
                                                    employee_file_records.comments,
                                                    employee_file_records.updated_by,
                                                    employee_file_records.depot_uid,
                                                    employee_file_records.date)
                VALUES ("  . mysqli_real_escape_string($this->dbConn->connection, $eUid)               . ",
                        '" . mysqli_real_escape_string($this->dbConn->connection, $etim)               . "',
                        "  . mysqli_real_escape_string($this->dbConn->connection, $ejob)               . ",
                        '" . mysqli_real_escape_string($this->dbConn->connection, $ecom)               . "',
                        '" . mysqli_real_escape_string($this->dbConn->connection, $updatedBy)          . "',
                        '" . mysqli_real_escape_string($this->dbConn->connection, $depId)              . "',
                        '" . mysqli_real_escape_string($this->dbConn->connection, $etim)  . "')";
  	
        $this->errorTO = $this->dbConn->processPosting($sql,"");
        
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
              $this->errorTO->description="Error Inserting Employee Record : ".$this->errorTO->description;
              echo "<br>";
              echo $sql;
              echo "<br>";
              return $this->errorTO;
        }
        $this->dbConn->dbQuery("commit"); 
        return $this->errorTO;   	
  }

//***************************************************************************************************************************************************************************  
   public function selectUserWarehouse($uId, $prin, $currWh) {
   	   
   	   if($currWh == '') {
   	      $cr = "";
   	   } else {
   	      $cr = " AND upd.depot_id = " . mysqli_real_escape_string($this->dbConn->connection, $currWh) ;
   	   }

       $sql = "SELECT d.uid AS 'WhUid',
                      d.name AS 'Warehouse',
                      d.pallet_depot,
                      d.pallet_principal
               FROM " . iDATABASE . ".user_principal_depot upd
               LEFT JOIN depot d ON d.uid = upd.depot_id 
               WHERE upd.user_id = " . mysqli_real_escape_string($this->dbConn->connection, $uId)  . "
               AND   upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $prin)
               . $cr ;
               
       $whDetails = $this->dbConn->dbGetAll($sql);

       return $whDetails;
  }
//***************************************************************************************************************************************************************************  

   public function getEmployeeJobs() {

        $sql = "SELECT ej.uid AS 'jobUid',
                       ej.job_description
                FROM " . iDATABASE . ".employee_job_function ej
                WHERE ej.`Status` = 'Y';" ;  	

       $ejs = $this->dbConn->dbGetAll($sql);

       return $ejs;   	
   }
//***************************************************************************************************************************************************************************  
     function getEmployeeNumbers($depId) {
     	
          $sql = "SELECT *
                  FROM " . iDATABASE . ".employee_details e
                  WHERE e.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $depId)  . "
                  and   e.status = 'A' 
                  order by e.name;"; 

          $cc = $this->dbConn->dbGetAll($sql);

          return $cc;
     	
     }

// **************************************************************************************************************************************************** 
public function getEmployeeJobDes() {

        $sql = "SELECT uid, job_description
                FROM employee_job_function" ;	

      $ejs = $this->dbConn->dbGetAll($sql);

     return $ejs;   	
   }
//***************************************************************************************************************************************************************************  

 public function getEmployeeJobsFiltered($filtersearch) {

        $sql = "SELECT uid, job_description
                FROM employee_job_function ejf
                where ejf.job_description LIKE '%". mysqli_real_escape_string($this->dbConn->connection, $filtersearch)."%'";	

      $ejs = $this->dbConn->dbGetAll($sql);

     return $ejs;   	
   }

//***************************************************************************************************************************************************************************  
public function updatefunction($postTUID, $postTNAME, $postNSTATUS){
	
	
	$sql = "update employee_job_function ejf
          SET ejf.job_description ='"  . mysqli_real_escape_string($this->dbConn->connection, $postTNAME). "', ejf.`Status` = '". mysqli_real_escape_string($this->dbConn->connection, $postNSTATUS)."'
                  
          WHERE ejf.uid ='". mysqli_real_escape_string($this->dbConn->connection, $postTUID)."'";
	
      
      $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
            $this->errorTO->description="Error Inserting Employee Record : ".$this->errorTO->description;
            echo "<br>";
            echo $sql;
            echo "<br>";
            return $this->errorTO;         	                  
        }
        $this->dbConn->dbQuery("commit"); 
       return $this->errorTO;  
	
	
	
	
	
	}
//***************************************************************************************************************************************************************************  


  public function insertJobFunction($newJob) {
  	
  	
       $sql = "INSERT INTO employee_job_function (job_description, status)
               VALUES ('"  . mysqli_real_escape_string($this->dbConn->connection, $newJob)  . "', 'Y')";
      
      $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
            $this->errorTO->description="Error Inserting Employee Record : ".$this->errorTO->description;
            echo "<br>";
            echo $sql;
            echo "<br>";
            return $this->errorTO;         	                  
        }
        $this->dbConn->dbQuery("commit"); 
       return $this->errorTO;  

  }
// **************************************************************************************************************************************************** 



     function checkDuplicateCode($depUid, $ecode) {
     	
          $sql = "SELECT *
                  FROM " . iDATABASE . ".employee_details e
                  WHERE e.depot_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $depUid)  . "
                  and   e.code      = '" . mysqli_real_escape_string($this->dbConn->connection, $ecode)   . "';"; 

          $cc = $this->dbConn->dbGetAll($sql);

          return $cc;
     	
     }     
//***************************************************************************************************************************************************************************  

     function insertNewEmployee($empWh, 
                                $empCode,
                                $empName,
                                $empId,
                                $empJob,
                                $empComment,
                                $userUId) {
                                	
                                	
            $sql = "INSERT INTO " . iDATABASE . ".`employee_details` (`depot_uid`, 
                                                    `code`, 
                                                    `name`, 
                                                    `id_number`, 
                                                    `duty`, 
                                                    `status`,
                                                    `comments`,                                                    
                                                    `captured_by`,
                                                    `last_updated`) 
                    VALUES ('"  . mysqli_real_escape_string($this->dbConn->connection, $empWh)      . "', 
                            '"  . mysqli_real_escape_string($this->dbConn->connection, $empCode)    . "', 
                            '"  . mysqli_real_escape_string($this->dbConn->connection, $empName)    . "', 
                            '"  . mysqli_real_escape_string($this->dbConn->connection, $empId)      . "', 
                            '"  . mysqli_real_escape_string($this->dbConn->connection, $empJob)     . "', 
                            'A',
                            '"  . mysqli_real_escape_string($this->dbConn->connection, $empComment) . "',
                            '"  . mysqli_real_escape_string($this->dbConn->connection, $userUId)    . "',
                            NOW());";  
                               	
            $this->errorTO = $this->dbConn->processPosting($sql,"");
            
            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                 $this->errorTO->description="Error Inserting Employee Details : ". $this->errorTO->description;
                 echo "<br>";
                 echo $sql;
                 echo "<br>";
                 return $this->errorTO;         	                  
            }
            $this->dbConn->dbQuery("commit"); 
            return $this->errorTO;   	    
     
      }


//***************************************************************************************************************************************************************************  
     function employeeDataValidation($empWh, 
                                     $empCode,
                                     $empName,
                                     $empJob,
                                     $actionType) {
                                     	
          if($empWh == 'Select New Warehouse' && $actionType == 'A') {
                $this->errorTO->type=FLAG_ERRORTO_ERROR;
                $this->errorTO->description="Error! No Warehouse Selected";
                return $this->errorTO;
          } 
          
          if(trim($empCode) == '' && $actionType == 'A')  {
                $this->errorTO->type=FLAG_ERRORTO_ERROR;
                $this->errorTO->description="Error! Employee Code Cannot Be Blank";
                return $this->errorTO;
          }
          
          if(strlen(trim($empName)) < 3 )  {
                $this->errorTO->type=FLAG_ERRORTO_ERROR;
                $this->errorTO->description="Error! Employee Name Too Short";
                return $this->errorTO;
          } 
          if($actionType == 'A') {
              if(trim($empJob)  == 'Select New Function' || trim($empJob)  == '') {
                    $this->errorTO->type=FLAG_ERRORTO_ERROR;
                    $this->errorTO->description="Error! Employee Job Not Selected";
                    return $this->errorTO;
              }
      
              $EmployeeDAO = new EmployeeDAO($this->dbConn); 
              $empDup = $EmployeeDAO->checkDuplicateCode($empWh, $empCode);
          
              if(count($empDup) > 0) {
                   $this->errorTO->type=FLAG_ERRORTO_ERROR;
                   $this->errorTO->description="Error! Employee Code Already Exists";
                   return $this->errorTO;
              }
          }    
          
          $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
          $this->errorTO->description="Validation Successful";
          
          return $this->errorTO;
     }
//***************************************************************************************************************************************************************************  

     function updateEmployeeDetails($empWh, 
                                    $empCode,
                                    $empName,
                                    $empId,
                                    $empJob,
                                    $empComment,
                                    $userUId,
                                    $empStatus,
                                    $empUid) {
                                   	
           $sql = "UPDATE " . iDATABASE . ".`employee_details` SET `depot_uid`     = '"  . mysqli_real_escape_string($this->dbConn->connection, $empWh)      . "', 
                                                 `name`          = '"  . mysqli_real_escape_string($this->dbConn->connection, $empName)    . "', 
                                                 `id_number`     = '"  . mysqli_real_escape_string($this->dbConn->connection, $empId)      . "', 
                                                 `duty`          = '"  . mysqli_real_escape_string($this->dbConn->connection, $empJob)     . "',
                                                 `comments`      = '"  . mysqli_real_escape_string($this->dbConn->connection, $empComment) . "',
                                                 `captured_by`   = '"  . mysqli_real_escape_string($this->dbConn->connection, $userUId)    . "',
                                                 `status`        = '"  . mysqli_real_escape_string($this->dbConn->connection, $empStatus)  . "',
                                                 `last_updated`  = NOW()
                   WHERE  `uid`= "  . mysqli_real_escape_string($this->dbConn->connection, $empUid)  . ";";
           
           $this->errorTO = $this->dbConn->processPosting($sql,"");
            
           if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                 $this->errorTO->description="Error Inserting Employee Details : ". $this->errorTO->description;
                 echo "<br>";
                 echo $sql;
                 echo "<br>";
                 return $this->errorTO;         	                  
           }
           $this->dbConn->dbQuery("commit"); 
           return $this->errorTO;   	               
                   	
     }
     
//***************************************************************************************************************************************************************************  

     function getEmployeeList($empWh) {
     
           $sql = "SELECT ed.uid, 
                          ed.depot_uid, 
                          ed.code,
                          ed.name
                   FROM .employee_details ed
                   WHERE ed.depot_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $empWh)  . "
                   AND   ed.`status` = 'A'
                   ORDER BY name";
                   
           $el = $this->dbConn->dbGetAll($sql);

           return $el;                   
      }

//***************************************************************************************************************************************************************************  
     function checkForDuplicates($empWh, $empId, $tDate) {

           $sql = "SELECT *
                   FROM employee_file_records efr
                   WHERE efr.depot_uid    = "  . mysqli_real_escape_string($this->dbConn->connection, $empWh)  . "
                   AND   efr.employee_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $empId)  . "
                   AND   efr.date = '"  . mysqli_real_escape_string($this->dbConn->connection, $tDate)  . "'";


           $dupStr = $this->dbConn->dbGetAll($sql);

           return $dupStr;                
     }
//***************************************************************************************************************************************************************************  

}         	 
?>
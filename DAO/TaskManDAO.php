<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/TaskManDAO.php");	
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class TaskManDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// **************************************************************************************************************************************************** 
  public function getDebtorAdminPricipalList() {
  	
       $sql = "SELECT DISTINCT(p.uid) AS 'principal_uid', 
                      p.name AS 'principal'
               FROM principal p
               INNER JOIN .voqado_extract_parameters vep on vep.principal_uid = p.uid
               WHERE vep.`status` = 'A'
               ORDER BY p.name  ;" ;
               
//             echo $sql;

       $uPList = $this->dbConn->dbGetAll($sql);

       return $uPList ;
  }
// **************************************************************************************************************************************************** 
  public function getPrincipalDebtorsList($principalUid) {
  	
       $sql = "SELECT pdc.uid as 'debtor_uid',
                      pdc.debtor_code,
                      pdc.debtor_name,
                      p.name AS 'principal_name',
                      p.uid  AS 'principal_uid'
               FROM .dm_principal_debtor_codes pdc
               INNER JOIN principal p on p.uid = pdc.principal_uid
               WHERE pdc.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . " 
               AND   pdc.`status` = 'A';";
               
       $uCList = $this->dbConn->dbGetAll($sql);

       return $uCList ;
  }

// **************************************************************************************************************************************************** 
  public function getDebtorTasks($principalUid, $debtorUid) {


       $sql = "SELECT dt.uid AS 'TaskId',
                      dt.task_code,
                      dt.task,
                      if(pdt.uid IS NULL,'NA','A') AS 'Task_Active',
                      p.uid AS 'principal_uid',
                      p.name AS 'principal_name',
                      pdc.uid AS 'debtor_uid',
                      pdc.debtor_name,
                      pdt.task_uid,
                      pdt.due as 'requiedDayUid',
                      dr.description as 'requireDay',
                      dr.catagory as 'requiredDayCatagory',
                      pdt.`status`,
                      it.uid AS 'inputId',
                      it.input AS 'inputType'
               FROM .dm_tasks dt
               LEFT JOIN .dm_input_type it ON it.uid = dt.input_uid 
               LEFT JOIN .dm_principal_debtor_task pdt ON pdt.task_uid = dt.uid
                                                       AND pdt.principal_uid   = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
                                                       AND   pdt.debtor_uid    = " . mysqli_real_escape_string($this->dbConn->connection, $debtorUid)    . "
                                                       AND   pdt.`status` = 'A'
               LEFT JOIN dm_principal_debtor_codes pdc on pdc.uid = " . mysqli_real_escape_string($this->dbConn->connection, $debtorUid) . "
               LEFT JOIN principal p on p.uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
               LEFT JOIN dm_day_required dr on pdt.due = dr.uid
               WHERE dt.`status` ='A'
               order by dt.order; ";

       $tList = $this->dbConn->dbGetAll($sql);

       return $tList ;
  }
// **************************************************************************************************************************************************** 

  public function getRequiredDay() {
  	
       $sql = "SELECT ddr.uid AS 'requiedDayUid',
                      ddr.description AS 'requireDay',
                      ddr.catagory    AS 'requiredDayCatagory'
               FROM .dm_day_required ddr
               WHERE ddr.`status` = 'A'
               ORDER BY ddr.catagory, ddr.sort;" ; 	

       $reqList = $this->dbConn->dbGetAll($sql);

       return $reqList ;
  	
  }
// **************************************************************************************************************************************************** 
  public function deleteExistingPrinDebtorTasks($principalUid, $debtorUid) {
  	
       $sql = "DELETE FROM dm_principal_debtor_task
               WHERE  principal_uid   = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
               AND    debtor_uid      = " . mysqli_real_escape_string($this->dbConn->connection, $debtorUid)    . ";";
               
       $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
       if($this->errorTO->type == 'S') {
       	    $this->dbConn->dbQuery("commit");
            return $this->errorTO->type;     	
       } else {
       	    echo $sql;
            return $this->errorTO->type;  
       }
  }      
// **************************************************************************************************************************************************** 
  public function insertNewPrinDebtorTasks($principalUid, $debtorUid, $taskUid, $dueUid, $stat, $si) {
  	
       
       $testsql = "SELECT *
                   FROM .dm_principal_debtor_task pdt
                   WHERE pdt.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
                   AND   pdt.debtor_uid    = " . mysqli_real_escape_string($this->dbConn->connection, $debtorUid)    . "
                   AND   pdt.task_uid      = " . mysqli_real_escape_string($this->dbConn->connection, $taskUid)     . ";";
                   
      $existTask = $this->dbConn->dbGetAll($testsql);

      if(count($existTask)== 0 && $si <> 'SKIPINSERT')    {

               $sql = "INSERT INTO `dm_principal_debtor_task` (`principal_uid`, 
                                                               `debtor_uid`, 
                                                               `task_uid`, 
                                                               `due`,
                                                               `status`) 
                       VALUES ("  . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . ", 
                               "  . mysqli_real_escape_string($this->dbConn->connection, $debtorUid)    . ", 
                               "  . mysqli_real_escape_string($this->dbConn->connection, $taskUid)      . ", 
                               "  . mysqli_real_escape_string($this->dbConn->connection, $dueUid)       . ",
                               '" . mysqli_real_escape_string($this->dbConn->connection, $stat)         . "');";
               
                $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
                if($this->errorTO->type == 'S') {
       	              $this->dbConn->dbQuery("commit");
                     return $this->errorTO;     	
                } else {
                      echo "<br>";
                      echo $sql;
                      echo "<br>";
                      return $this->errorTO->type;  
                }
      } else {
      	
      	       $sql = "UPDATE dm_principal_debtor_task pdt set pdt.`status` = '" . mysqli_real_escape_string($this->dbConn->connection, $stat)     . "',
      	                                                       pdt.`due`    = if('" . mysqli_real_escape_string($this->dbConn->connection, $stat)     . "' = 'D',
      	                                                                         NULL,
      	                                                                         '"  . mysqli_real_escape_string($this->dbConn->connection, $dueUid)  . "') 
                       WHERE pdt.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
                       AND   pdt.debtor_uid    = " . mysqli_real_escape_string($this->dbConn->connection, $debtorUid)    . "
                       AND   pdt.task_uid      = " . mysqli_real_escape_string($this->dbConn->connection, $taskUid)     . ";";

                $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
                if($this->errorTO->type == 'S') {
       	              $this->dbConn->dbQuery("commit");
                     return $this->errorTO;     	
                } else {
                      echo "<br>";
                      echo $sql;
                      echo "<br>";
                      return $this->errorTO->type;  
                }
      }        

  }
// **************************************************************************************************************************************************** 

  public function getTaskUsers() {
  	
       $sql = "SELECT *
               FROM users u
               WHERE u.dm_tasks = 'Y'
               ORDER BY u.user_email;" ; 	

       $userList = $this->dbConn->dbGetAll($sql);

       return $userList ;
  	
  }
  
// **************************************************************************************************************************************************** 

  public function getTaskUserCapture($userId) {
  	
       $sql = "SELECT *
               FROM users u
               WHERE u.dm_tasks = 'Y' 
               AND u.uid  = "  . mysqli_real_escape_string($this->dbConn->connection, $userId) ; 	

       $getUser = $this->dbConn->dbGetAll($sql);

       return $getUser ;
  	
  }  

// **************************************************************************************************************************************************** 

  public function getTaskMonth() {
  	
       $sql = "SELECT *
               FROM .dm_month dmm
               WHERE 1;";

       $monthList = $this->dbConn->dbGetAll($sql);

       return $monthList ;
  	
  }

// **************************************************************************************************************************************************** 
  public function getUserTasks($principalUid, $debtorUid, $tUser, $tMonth) {

      $dsql = "SELECT *
               FROM .dm_month dmm
               WHERE dmm.month_no = " . mysqli_real_escape_string($this->dbConn->connection, $tMonth) . " ;";

      $monthList = $this->dbConn->dbGetAll($dsql);

      $sql = "SELECT dtt.uid AS 'task_transaction_uid',
                     pdt.principal_uid,
                     pdt.debtor_uid,
                     trim(p.name) as 'principal_name',
                     trim(pdc.debtor_name) AS 'debtor_name',
                     pdt.task_uid,
                     dt.task,
                     dt.input_uid as 'inputId',
                     dt.allow_file_upload,
                     pdt.due,
                     dtt.owner_uid,
                     if(dtt.uid IS NULL,'NA','A') AS 'Task_Allocated',
                     if(dtt.owner_uid IS NULL || dtt.owner_uid = " . mysqli_real_escape_string($this->dbConn->connection, $tUser) . ",'Allow','Disable') as 'Allow_Task',
                     c.cal_date,
                     if(dtt.owner_uid IS NULL || dtt.owner_uid = " . mysqli_real_escape_string($this->dbConn->connection, $tUser) . ",dtt.`comment`,'') as 'comment',
                     dtt.`status` AS 'dtt_status',
                     dtt.feedback_status,
                     '" . $monthList[0]['month_start'] . "' AS 'start_date'
              FROM .dm_principal_debtor_task pdt
              LEFT JOIN .dm_task_transaction dtt ON dtt.year = '" . mysqli_real_escape_string($this->dbConn->connection, substr($monthList[0]['month_start'],0,4)) . "' 
                                                 AND dtt.month = '" . mysqli_real_escape_string($this->dbConn->connection, $tMonth) . "' 
                                                 AND pdt.task_uid = dtt.task_uid
                                                 AND dtt.debtor_uid = " . mysqli_real_escape_string($this->dbConn->connection, $debtorUid) . "  
              LEFT JOIN .dm_tasks dt ON dt.uid = pdt.task_uid
              LEFT JOIN .dm_day_required dr ON dr.uid = pdt.due
              LEFT JOIN .calendar c ON c.cal_date BETWEEN '" . mysqli_real_escape_string($this->dbConn->connection, $monthList[0]['month_start']) . "' 
                                                  AND '" . mysqli_real_escape_string($this->dbConn->connection, $monthList[0]['month_end'])   . "' 
                                                  AND if(dr.catagory = 'Day',dr.number = SUBSTR(c.cal_date,-2),
                                                  if(dr.catagory = 'Working Day',dr.number = SUBSTR(c.cal_date,-2),
                                                  if(dr.catagory = 'First Day',01 = SUBSTR(c.cal_date,-2),
                                                  if(dr.catagory = 'last Day',c.month_end='Y',''))))
              LEFT JOIN principal p on p.uid = pdt.principal_uid
              LEFT JOIN dm_principal_debtor_codes pdc on pdc.uid = pdt.debtor_uid                                 
              WHERE pdt.principal_uid  = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
              AND   pdt.debtor_uid     = " . mysqli_real_escape_string($this->dbConn->connection, $debtorUid) . "
              AND   dt.`status` = 'A'
              ORDER BY dt.order ";  	
//             echo "<pre>";
//             echo $sql;

       $tskList = $this->dbConn->dbGetAll($sql);

       return $tskList ;
  	
  }
// **************************************************************************************************************************************************** 
  public function postTaskTransaction($DmTaskTO, $src) {
  	     
        $dsql = "SELECT * 
                 FROM dm_task_transaction dtt
                 WHERE dtt.year           =  " . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->Year)           . " 
                 AND   dtt.month          =  " . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->Month)          . " 
                 AND   dtt.principal_uid  =  " . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->PrincipalUid)   . "
                 AND   dtt.debtor_uid     =  " . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->DebtorList)     . "
                 AND   dtt.task_uid       =  " . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->TaskUid)        . ";";

      $existList = $this->dbConn->dbGetAll($dsql);

      if(count($existList) == 0 &&  mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->Status)  == 'A') {
            
             $sql = "INSERT INTO `dm_task_transaction` (`year`, 
                                                        `month`, 
                                                        `start_date`, 
                                                        `principal_uid`, 
                                                        `debtor_uid`, 
                                                        `task_uid`, 
                                                        `input_type`, 
                                                        `comment`, 
                                                        `owner_uid`, 
                                                        `captured_by`, 
                                                        `due_date`, 
                                                        `last_updated`,
                                                        `status`) 
              VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->Year)         . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->Month)        . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->StartDate)    . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->PrincipalUid) . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->DebtorList)   . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->TaskUid)      . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->InputType)    . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->Comments)     . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->OwnerID)      . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->CapturedBy)   . "',
                      '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->dueDate)      . "',
                      now(),                     
                      '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->Status)      . "');";
 
              $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
              if($this->errorTO->type == 'S') {
       	             $this->dbConn->dbQuery("commit");
                     return $this->errorTO;     	
              } else {
                     echo "<br>";
                     echo $sql;
                     echo "<br>";
                     return $this->errorTO;  
              }
      	
      } else {
      	     $srcClean = mysqli_real_escape_string($this->dbConn->connection, $src);
      	     
      	     if($srcClean == 'alloCapture') {
      	     	     $uAr = Array($DmTaskTO->OwnerID, NULL);
      	     	     if(trim($existList[0]['uid']) <> '' && in_array($existList[0]['owner_uid'],$uAr)) {
      	     	            $updateSql = "SET `comment`        = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->Comments)  . "',
                                             dtt.`status`    = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->Status)    . "',
                                             dtt.owner_uid   = if('" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->Status)    . "' = 'A',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->OwnerID)   . "',NULL)"; 
                          $sql = "UPDATE    `dm_task_transaction` dtt
                                  LEFT JOIN  dm_principal_debtor_codes pdc ON dtt.debtor_uid = pdc.uid  " . $updateSql . " 
                                  WHERE  dtt.uid = " . mysqli_real_escape_string($this->dbConn->connection, $existList[0]['uid']) . ";";

                          $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
                          if($this->errorTO->type == 'S') {
       	                        $this->dbConn->dbQuery("commit");
       	                        return $this->errorTO; 
                          } else {
                                echo "<br>";
                                echo $sql;
                                echo "<br>";
                            return $this->errorTO->type;  
                          }
                   } else {
                         $this->errorTO->type = 'S' ;	
                         return $this->errorTO;
                   }      
             } elseif($src == 'UserCapture') { 
             	
             	      if($DmTaskTO->InputType == 1) {
             	      	    
             	                   $updateSql = "SET dtt.input_yn         = if($DmTaskTO->InputYn = 1,'Y','N'),
             	      	                              dtt.input_date       = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->InputDate)                                . "',
             	      	                              dtt.input_comment    = CONCAT('" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->InputComment)  . "','  '),
             	      	                              dtt.feedback_status  = if($DmTaskTO->InputYn = 1,'C',NULL)";

                                 $sql = "UPDATE    `dm_task_transaction` dtt
                                         LEFT JOIN  dm_principal_debtor_codes pdc ON dtt.debtor_uid = pdc.uid  " . $updateSql . " 
                                         WHERE  dtt.uid = " . mysqli_real_escape_string($this->dbConn->connection, $existList[0]['uid']) . " ;";

             	                   $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
             	                   if($this->errorTO->type == 'S') {
             	                        $this->dbConn->dbQuery("commit");
             	                        return $this->errorTO; 
             	                   } else {
                                      echo "<br>";
                                      echo $sql;
                                      echo "<br>";
                                      return $this->errorTO->type;  
                                 }             	      	                 
             	      } elseif ($DmTaskTO->InputType == 2) {
             	      	            $updateSql = "SET pdc.debtor_contact1  = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->debtor_contact1)          . "',
                                                    pdc.debtor_contact2  = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->debtor_contact2)          . "',
                                                    pdc.debtor_tel1      = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->debtor_tel1)              . "',
                                                    pdc.debtor_tel2      = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->debtor_tel2)              . "',
                                                    pdc.debtor_email1    = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->debtor_email1)            . "',
                                                    pdc.debtor_email2    = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->debtor_email2)            . "',
                                                    pdc.portal           = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->portal)                   . "',
                                                    pdc.portal_username  = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->portal_user)              . "',
                                                    pdc.portal_password  = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->portal_password)          . "',
                                                    pdc.comments         = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->debtor_update_comments)   . "',
                                                    pdc.contactupdated   = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->debtor_update_date)       . "', 
                                                    pdc.updated_by       = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->CapturedBy)               . "',
                                                    pdc.last_updated     = NOW(), 
                                               
                                                    dtt.input_date       = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->debtor_update_date) . "',     
                                                    dtt.feedback_status  = 'C' "; 

                                 $sql = "UPDATE    `dm_task_transaction` dtt
                                         LEFT JOIN  dm_principal_debtor_codes pdc ON dtt.debtor_uid = pdc.uid  " . $updateSql . " 
                                         WHERE  dtt.uid = " . mysqli_real_escape_string($this->dbConn->connection, $existList[0]['uid']) . " ;";

             	                   $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
             	                   if($this->errorTO->type == 'S') {
             	                        $this->dbConn->dbQuery("commit");
             	                        return $this->errorTO; 
             	                   } else {
                                      echo "<br>";
                                      echo $sql;
                                      echo "<br>";
                                      return $this->errorTO->type;  
                                 }             	      	              
                                                                         
             	      } elseif ($DmTaskTO->InputType == 3) {

                                   $updateSql  = "SET dtt.input_date       = '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->communication_date) . "',      
                                                      dtt.feedback_status  = 'C' "; 
                                            
                                   $InsertSql  = "INSERT INTO `dm_principal_contact` (`principal_uid`, 
                                                                                      `date`, 
                                                                                      `contact_comment`, 
                                                                                      `captured_by`, 
                                                                                      `last_updated`) 
                                                  VALUES ("  . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->PrincipalUid)           . ", 
                                                          '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->communication_date)     . "',
                                                          '" . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->communication_comments) . "', 
                                                           " . mysqli_real_escape_string($this->dbConn->connection, $DmTaskTO->CapturedBy) . ", 
                                                           NOW())";

                                   $this->errorTO = $this->dbConn->processPosting($InsertSql,"");
                                   
                                   if($this->errorTO->type == 'S') {
       	                                $this->dbConn->dbQuery("commit");
                                   } else {
                                        echo "<br>";
                                        echo $sql;
                                        echo "<br>";
                                        return $this->errorTO->type;  
                                   }
                                   
                                 $sql = "UPDATE    `dm_task_transaction` dtt
                                         LEFT JOIN  dm_principal_debtor_codes pdc ON dtt.debtor_uid = pdc.uid  " . $updateSql . " 
                                         WHERE  dtt.uid = " . mysqli_real_escape_string($this->dbConn->connection, $existList[0]['uid']) . " ;";

             	                   $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
             	                   if($this->errorTO->type == 'S') {
             	                        $this->dbConn->dbQuery("commit");
             	                        return $this->errorTO; 
             	                   } else {
                                      echo "<br>";
                                      echo $sql;
                                      echo "<br>";
                                      return $this->errorTO->type;  
                                 } 
             	      } else {
             	      	   
             	      	     // Nothing Happens here - Add on if new update type
             	      	
             	      }
      	     }
      }
  }
// **************************************************************************************************************************************************** 
  public function getDatePeriod($tMonth) {

      $dsql = "SELECT *
               FROM .dm_month dmm
               WHERE dmm.month_no = " . mysqli_real_escape_string($this->dbConn->connection, $tMonth) . " ;";

      $monthList = $this->dbConn->dbGetAll($dsql);
      
      return $monthList;
  }     
// **************************************************************************************************************************************************** 
  public function getUserTaskToManage($ttask_uid ) {


         $sql = "SELECT dtt.uid AS 'dtt_uid',
                        dtt.start_date,
                        dtt.principal_uid,
                        p.name AS 'principal_name',
                        dtt.debtor_uid,
                        pdc.debtor_name,
                        dtt.task_uid,
                        dtt.owner_uid,
                        dtt.comment,
                        u.full_name,
                        u.dm_tasks,       
                        dt.task,
                        dtt.input_type,
                        dtt.input_yn,
                        dt.allow_file_upload,
                        dtt.input_date,
                        dtt.input_comment,
                        pdc.debtor_contact1,
                        pdc.debtor_contact2,
                        pdc.debtor_email1,
                        pdc.debtor_email2,
                        pdc.debtor_tel1,
                        pdc.debtor_tel2,
                        pdc.portal,
                        pdc.portal_username,
                        pdc.portal_password,
                        pdc.contactupdated,
                        pdc.comments              
                 FROM .dm_task_transaction dtt
                 INNER JOIN .principal p ON p.uid = dtt.principal_uid
                 INNER JOIN .dm_principal_debtor_codes pdc ON pdc.uid = dtt.debtor_uid
                 INNER JOIN .dm_tasks dt ON dt.uid = dtt.task_uid
                 INNER JOIN .users u ON u.uid = dtt.owner_uid
                 WHERE dtt.uid = " . mysqli_real_escape_string($this->dbConn->connection, $ttask_uid) ;
                 
//                 echo $sql;
                 
      $taskToMan = $this->dbConn->dbGetAll($sql);
      
      return $taskToMan;
  }
// **************************************************************************************************************************************************** 
  public function getUserFTPlogin($user_uid ) {  
  	
  	   $sql = "SELECT *
               FROM .ftp_server fs
               WHERE fs.taskman_userid = " . mysqli_real_escape_string($this->dbConn->connection, $user_uid) . "
               AND   fs.process_uid = 5;";

       $taskftp = $this->dbConn->dbGetAll($sql);
      
       return $taskftp;             
  }
  
// **************************************************************************************************************************************************** 
   public function insertIntoUploadIndex($principal_uid, $filname, $fileType, $userId, $transUid ) {  
   	
   	
  	   $sql = "INSERT INTO dm_file_upload_index (dm_file_upload_index.principal_uid,
                                  dm_file_upload_index.file_name,
                                  dm_file_upload_index.file_type,
                                  dm_file_upload_index.task_transaction_uid,
                                  dm_file_upload_index.uploaded_by,
                                  dm_file_upload_index.upload_time)
               VALUES ("  . mysqli_real_escape_string($this->dbConn->connection, $principal_uid)  . ",
                       '" . mysqli_real_escape_string($this->dbConn->connection, $filname)   . "', 
                       '" . mysqli_real_escape_string($this->dbConn->connection, $fileType)  . "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $transUid)  . "',
                        " . mysqli_real_escape_string($this->dbConn->connection, $userId)    . ",
                        now());"; 

               $this->errorTO = $this->dbConn->processPosting($sql,"");
             
               if($this->errorTO->type == 'S') {
                    $this->dbConn->dbQuery("commit");
                    return $this->errorTO; 
               } else {
                   if(strpos($this->errorTO->description,'Duplicate entry')) {
                   	    $this->errorTO->type = 'D';
                   	    return $this->errorTO;
                   } else {
                        echo "<br>";
                        echo $sql;
                        echo "<pre>";   
                        print_r($this->errorTO); 
                   } 
               } 
   }
// **************************************************************************************************************************************************** 
   public function getUploadedFileList($transUid ) { 
   	
        $sql = "SELECT *
                FROM .dm_file_upload_index fui
                WHERE fui.task_transaction_uid in ('" . mysqli_real_escape_string($this->dbConn->connection, $transUid)  . "');";

        $upLoadedFiles = $this->dbConn->dbGetAll($sql);
      
        return $upLoadedFiles;  
   	
   	}
// **************************************************************************************************************************************************** 
   public function getFilesToTransfer() {    
   
        $sql = "SELECT fui.uid as 'fuiUid',
                       fui.principal_uid,
                       fui.task_transaction_uid,
                       fui.file_name,
                       dtt.year AS 'tYear', 
                       dtt.month AS 'tMonth',
                       trim(substr(p.name,1,POSITION(' ' IN p.name))) AS 'Principal'
                       FROM .dm_file_upload_index fui
                       LEFT JOIN dm_task_transaction dtt ON fui.task_transaction_uid = dtt.uid
                       INNER JOIN .principal p ON fui.principal_uid = p.uid
                       WHERE fui.tranferred_to_storage = 'N' ;";  
        
        $filesToLoad = $this->dbConn->dbGetAll($sql);
      
        return $filesToLoad;  
   	
   }
// **************************************************************************************************************************************************** 
   public function updateFileTransfer($task_transaction_uid, $taskStatus, $taskError) { 
   	
              $sql = "UPDATE dm_file_upload_index fui SET fui.tranferred_to_storage = '" . mysqli_real_escape_string($this->dbConn->connection, $taskStatus) . "',
                                                          fui.transferred_time      = NOW(),
                                                          fui.transfer_error        = '" . mysqli_real_escape_string($this->dbConn->connection, $taskError) . "' 
                      
                      WHERE fui.uid =  " . mysqli_real_escape_string($this->dbConn->connection, $task_transaction_uid) . ";";	
   	
   	          $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
              if($this->errorTO->type == 'S') {
             	            $this->dbConn->dbQuery("commit");
             	            return $this->errorTO; 
             	} else {
                          echo "<br>";
                          echo $sql;
                          echo "<br>";
                          return $this->errorTO->type;  
              } 
   }   
// **************************************************************************************************************************************************** 
   
} 
?>
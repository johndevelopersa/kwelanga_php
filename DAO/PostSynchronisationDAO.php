<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class PostSynchronisationDAO {
	private $dbConn;
	private $errorTO;
	
	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO();
    }
	
	
	public function setSynchedPrincipalResult($runTime, $uid, $status, $synchMessage) {
		$this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time
		
		$sql="update principal
			  set    last_synched = now(),
					 last_synch_status = '".mysql_real_escape_string($status)."',
 					 last_synch_msg = '".mysql_real_escape_string(substr($synchMessage,0,512))."' 
			  where  (last_updated <= '".mysql_real_escape_string($runTime)."' or last_updated is null)
			  and    uid = '".mysql_real_escape_string($uid)."'";
			  			
		$errorTO = $this->dbConn->processPosting($sql,"uid:".$uid); // do this this way and not direct dbinsQuery call because need to pick up 0 rows updated due to last_updated
		
		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {
			$this->errorTO->type = FLAG_ERRORTO_SUCCESS;
			$this->errorTO->description = "Successfully set principal result.";
		} else {
			$this->errorTO->type = FLAG_ERRORTO_ERROR;
			$this->errorTO->description = "Failed to set principal result for uid (".$uid.").".mysql_error($this->dbConn->connection);
		}
		
		return $this->errorTO;
	}
	
	public function setJobResult($type, $runDate, $jobEnded, $message, $completedSuccessfully) {
		global $ROOT; global $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
		
		$timeSeconds=CommonUtils::getMicroTime(); 
		if($completedSuccessfully!="1") $completedSuccessfully="0";
		// it is necessary to create a temporary connection to be able to commit only this section
		$dbConn = new dbConnect();
        $dbConn->dbConnection();
        
        $dbConn->dbQuery("select 1 from job 
						  where type='".mysql_real_escape_string($type)."'
						  and   run_date='".mysql_real_escape_string($runDate)."'");
		
		
		if ($dbConn->dbQueryResultRows==0) {
			$sql="insert into job
					(type, run_date, job_ended, message, completed_successfully, start_time_seconds, end_time_seconds)
				  values
					(
						'".mysql_real_escape_string($type)."',
						'".mysql_real_escape_string($runDate)."',
						'".mysql_real_escape_string($jobEnded)."',
						'".mysql_real_escape_string($message)."',
						'".mysql_real_escape_string($completedSuccessfully)."',
						".$timeSeconds.",
						".$timeSeconds."
					)";
		} else {
			$sql="update job
				  set job_ended='".mysql_real_escape_string($jobEnded)."',
					  message=concat(message,'".mysql_real_escape_string($message)."'),
					  completed_successfully='".mysql_real_escape_string($completedSuccessfully)."',
                      end_time_seconds=".$timeSeconds."
				  where type='".mysql_real_escape_string($type)."'
				  and   run_date='".mysql_real_escape_string($runDate)."'";
		}
		$errorTO = $dbConn->processPosting($sql,$type); // do this this way and not direct dbinsQuery call because need to pick up 0 rows updated due to last_updated
		
		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {
			$this->errorTO->type = FLAG_ERRORTO_SUCCESS;
			$this->errorTO->description = "Successfully created/updated job.";
		} else {
			$this->errorTO->type = FLAG_ERRORTO_ERROR;
			$this->errorTO->description = "Failed to create/update job. ".mysql_error($dbConn->connection);
		}
		
		$dbConn->dbQuery("commit");
		
		return $this->errorTO;
	}
	
}	
?>

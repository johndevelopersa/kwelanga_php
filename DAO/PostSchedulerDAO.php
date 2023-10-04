<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/ValidationCommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'libs/FTPClass.php');
/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostSchedulerDAO {
	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
    }

    /*
     *
     *  User Roles
     *
     */

    public function postScheduleValidation($postingSchedulerTO) {
    	global $ROOT; global $PHPFOLDER;

    	include_once($ROOT.$PHPFOLDER."DAO/ReportDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/SchedulerDAO.php");
    	$reportDAO = new ReportDAO($this->dbConn);
    	$schedulerDAO = new SchedulerDAO($this->dbConn);

    	// get user. don't pass it because this is more secure.
    	if (!isset($_SESSION)) session_start();
    	$userId = $_SESSION['user_id'];
    	$principalId = $_SESSION['principal_id'];

    	if (($postingSchedulerTO->userUId!=$userId)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Inconsistent User UID passed.";
			return false;
		}

        if (empty($postingSchedulerTO->principalUId)) {  //caters for blank string, nulls and zero values.
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Selected Principal UID Error.";
			return false;
		}

    	if (!ValidationCommonUtils::checkPostingType($postingSchedulerTO->DMLType)) return false;

		if($postingSchedulerTO->jobType!=SCD_JT_REPORT) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Object/Report type.";
			return false;
		} else {
			// this also does the user validation for the report so no explicit role check necessary
			$mfR = $reportDAO->getReportItemForUser($userId, $principalId, $postingSchedulerTO->objectId);
			if (sizeof($mfR)<1) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="No Report Specified, or you do not have access to this report or report does not exist.";
				return false;
			}
			if (($mfR[0]["parameter_fields"]!="") && ($postingSchedulerTO->parameterList=="")) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Parameters are required for this report.";
				return false;
			}
		};

		// no further processing is required for delete
		if ($postingSchedulerTO->DMLType=="DELETE") return true;

		if (
			(
    		 ($postingSchedulerTO->runDay!="") &&
    		 ($postingSchedulerTO->runWeek!="")
    		) ||
    		(
    		 ($postingSchedulerTO->runDay=="") &&
    		 ($postingSchedulerTO->runWeek=="")
    		)
    		) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Atleast one (and at most) of the run intervals must be chosen.";
			return false;
		}

		if ($postingSchedulerTO->runDay!="") {
			$chkArr = explode(",",$postingSchedulerTO->runDay);
			$chkArr2=array();
			foreach ($chkArr as $d) {
				//dont use is_int or is_numeric because is_int treats string ints as false
				if ((!preg_match(GUI_PHP_INTEGER_REGEX,$d)) || ($d>31) || ($d<1)) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="Invalid Day Specified";
					return false;
				}
				$chkArr2[$d]=$d;
			}
			if (sizeof($chkArr)!=sizeof($chkArr2)) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Duplicate Days passed";
				return false;
			}
		} else {
			$chkArr = explode(",",$postingSchedulerTO->runWeek);
			$chkArr2=array();
			foreach ($chkArr as $w) {
				//dont use is_int or is_numeric because is_int treats string ints as false
				if ((!preg_match(GUI_PHP_INTEGER_REGEX,$w)) || ($w>6) || ($w<0)) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="Invalid Week Specified";
					return false;
				}
				$chkArr2[$w]=$w;
			}
			if (sizeof($chkArr)!=sizeof($chkArr2)) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Duplicate WeekDay passed";
				return false;
			}
		}

		$chkArr = explode(",",$postingSchedulerTO->runTime);
		$chkArr2=array();
		foreach ($chkArr as $t) {
			switch ($t) {
        case "3": break;
				case "6": break;
				case "9": break;
				case "12": break;
				case "15": break;
				case "18": break;
				default: {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="Invalid Time Specified";
						return false;
						}
			}
			$chkArr2[$t]=$t;
		}
		if (sizeof($chkArr)!=sizeof($chkArr2)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Duplicate Time passed";
			return false;
		}

		if (!ValidationCommonUtils::checkFieldYesNoSimple($postingSchedulerTO->regenerate)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Regenerate value";
			return false;
		}



    	if ($postingSchedulerTO->outputType != SCD_OT_CSV && $postingSchedulerTO->outputType != SCD_OT_HTML && $postingSchedulerTO->outputType != SCD_OT_XML) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Output Type";
			return false;
		}

		if ($postingSchedulerTO->destinationType!=SCD_DT_EMAIL && $postingSchedulerTO->destinationType!=SCD_DT_FTP) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Destination Type";
			return false;
		}

		if ($postingSchedulerTO->destinationType == SCD_DT_EMAIL){

		  if ($postingSchedulerTO->altRecipientList!="") {
  			$postingSchedulerTO->altRecipientList=str_replace(" ","",$postingSchedulerTO->altRecipientList);
  			$chkArr = explode(",",$postingSchedulerTO->altRecipientList);
  			$chkArr2=array();
  			foreach ($chkArr as $e) {
  				//dont use is_int or is_numeric because is_int treats string ints as false
  				if (!preg_match(GUI_PHP_EMAIL_REGEX,$e)) {
  					$this->errorTO->type=FLAG_ERRORTO_ERROR;
  					$this->errorTO->description="Invalid Email Format for : ".$e.". Must be comma separated list of format name@domain.something";
  					return false;
  				}
  				$chkArr2[$e]=$e;
  			}
  			if (sizeof($chkArr)!=sizeof($chkArr2)) {
  				$this->errorTO->type=FLAG_ERRORTO_ERROR;
  				$this->errorTO->description="Duplicate email passed";
  				return false;
  			}
    	  }

          if (($postingSchedulerTO->altRecipientList=="") && ($postingSchedulerTO->sendToSelf!="Y")) {
          	$this->errorTO->type=FLAG_ERRORTO_ERROR;
          	$this->errorTO->description="There must be atleast 1 recipient, if send-to-self is not selected";
          	return false;
          }

		  if (!ValidationCommonUtils::checkFieldYesNoSimple($postingSchedulerTO->sendToSelf)){
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Send-to-Self value";
			return false;
		  }

		}



		if ($postingSchedulerTO->destinationType == SCD_DT_FTP){

		  //IF FTP DONT ALLOW HTML.
		  if($postingSchedulerTO->outputType == SCD_OT_HTML){
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Output Type 'HTML' Cannot be sent over FTP. Please select another output type.";
			return false;
		  }

		  if(empty($postingSchedulerTO->destinationAddress)){
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid FTP Settings";
			return false;
		  }

		  $FTP = unserialize($postingSchedulerTO->destinationAddress);

		  //CHECK VALUES
		  if(empty($FTP['HOST'])){  //DUAL - ISSET AND != '' (NOTE: A VALUE: 0 = false)
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid FTP Host, please specify one.";
			return false;
		  }

		  if(empty($FTP['USR'])){
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid FTP Username, please specify one.";
			return false;
		  }

		  if(empty($FTP['PWD'])){
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid FTP Password, please specify one.";
			return false;
		  }

	      if(empty($FTP['PORT'])){
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid FTP Port, please specify one - Default 21";
			return false;
		  }


		  //CREATE A DUMMY FILE TO UPLOAD TO FTP SITE PROVIDED.
		  $dummyFile = $ROOT.'test.tmp.'.date('YmdHis');
		  $fh = @fopen($dummyFile, 'w'); //create file if doesn't exist
          if ($fh) {
            //dummy data - timestamps.
            fwrite($fh, date('Y.m.d H:i:s')."\r\n".date('Y.m.d H:i:s')."\r\n".date('Y.m.d H:i:s')."\r\n".date('Y.m.d H:i:s'));
            fclose($fh);
          }
          @fclose($fh);


		  //DO AN ACTUAL FTP TEST - SERVER CHECK ON VALUES, COPY FILE TEST FILE.
		  $ftpObj = new FTP();
		  $ftpResult = $ftpObj->sendFile($FTP['HOST'], $FTP['USR'], $FTP['PWD'], $FTP['FOLDER'], basename($dummyFile), false, $FTP['PORT'], $FTP['MODE']);

		  unlink($dummyFile);  //DELETE LOCAL FILE

		  //CHECK FTP TEST RESULTS? : CAN ONLY BE SUCCESS TO PASS
          if($ftpResult->type != FLAG_ERRORTO_SUCCESS){
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
			$this->errorTO->description = $ftpResult->description;
			return false;
          }

		}


		if ($postingSchedulerTO->DMLType=="INSERT") {
				//
		} else if ($postingSchedulerTO->DMLType=="UPDATE") {
			// check if schedule exists
			$mfS = $schedulerDAO->getScheduleItemForUser($userId, $principalId, $postingSchedulerTO->uid);
			if(sizeof($mfS)<1) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="The Schedule could not be found for editing.";
				return false;
			};
		  }

		return true;

    }

    public function postSchedule($postingSchedulerTO) {
		$this->dbConn->dbQuery("SET time_zone='+0:00'");

    	$resultOK = $this->postScheduleValidation($postingSchedulerTO);

    	if ($resultOK) {

    	  //STORE BLANKS AS NULL => BETTER MYSQL STORAGE
    	  $postingSchedulerTO->altRecipientList = (empty($postingSchedulerTO->altRecipientList)) ? ('NULL') : ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->altRecipientList) . "'");
          $postingSchedulerTO->destinationAddress = (empty($postingSchedulerTO->destinationAddress)) ? ('NULL') : ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->destinationAddress) . "'");
          $postingSchedulerTO->runDay = ($postingSchedulerTO->runDay == '') ? ('NULL') : ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->runDay) . "'");
          $postingSchedulerTO->runWeek = ($postingSchedulerTO->runWeek == '') ? ('NULL') : ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->runWeek) . "'");

    		 if($postingSchedulerTO->DMLType=="INSERT") {

    			$sql="INSERT INTO scheduler
    				  (
						job_type,
						object_id,
						run_day,
						run_week,
						run_time,
						last_run_date,
						created_date,
						regenerate,
						alt_recipient_list,
						send_to_self,
						parameter_list,
						principal_uid,
						principal_code,
						user_uid,
						destination_type,
						destination_address,
						output_type
    				  )
    				  VALUES (".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->jobType) . "',".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->objectId) . "',".
    				  	"" . $postingSchedulerTO->runDay . ",".
    				  	"" . $postingSchedulerTO->runWeek . ",".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->runTime) . "',".
    				  	"NULL,".
    				  	"NOW(),".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->regenerate) . "',".
    				  	"" . $postingSchedulerTO->altRecipientList . ",".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->sendToSelf) . "',".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->parameterList) . "',".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->principalUId) . "',".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->principalCode) . "',".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->userUId) . "',".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->destinationType) . "',".
    			        "" . $postingSchedulerTO->destinationAddress . ",".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->outputType) . "'".
    				  ")";

    		  } else if ($postingSchedulerTO->DMLType=="UPDATE") {
            
            // THIS MUST NEVER BE CONVERTED TO A DELETE AND INSERT AS THE SCHEDULED JOBS FOR DAILY (RI/RP/RU/RO/RI) REPORTS
            // Store the "run once" for each document under a specific uid, and if that's changed the customer will recieve EVERYTHING again!!

            // Do NOT set the last_run_date to null as the packaged daily reports will then run incorrectly at the next interval and
            // will send incorrectly according to the day / intent of report
	    			$sql="UPDATE scheduler
	    				  SET   job_type = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->jobType) . "',
								object_id = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->objectId) . "',
								run_day = " . $postingSchedulerTO->runDay . ",
								run_week = " . $postingSchedulerTO->runWeek . ",
								run_time = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->runTime) . "',
								-- last_run_date = null,
								regenerate = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->regenerate) . "',
								alt_recipient_list = " . $postingSchedulerTO->altRecipientList . ",
								send_to_self = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->sendToSelf) . "',
								parameter_list = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->parameterList) . "',
								principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->principalUId) . "',
								principal_code = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->principalCode) . "',
								user_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->userUId) . "',
								destination_type = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->destinationType) . "',
								destination_address = " . $postingSchedulerTO->destinationAddress . ",
								output_type = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->outputType) . "'
						  WHERE uid = " . mysqli_real_escape_string($this->dbConn->connection, $postingSchedulerTO->uid);
	    		  } else if ($postingSchedulerTO->DMLType=="DELETE") {
	    			$sql="DELETE FROM scheduler
						  WHERE uid = ".$postingSchedulerTO->uid;
	    		  }

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingSchedulerTO->uid);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
			  	if ($postingSchedulerTO->DMLType=="INSERT") {
			  		$this->errorTO->description="Schedule Successfully Created.";
			  		$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId(); // get the UID just created
			  	} else if ($postingSchedulerTO->DMLType=="UPDATE") {
			  		$this->errorTO->description="Schedule Successfully Updated.";
			  		$this->errorTO->identifier=$postingSchedulerTO->uid;
			  	} else if ($postingSchedulerTO->DMLType=="DELETE") {
			  		$this->errorTO->description="Schedule Successfully Deleted.";
			  	}

			  	// if delete, remove jobs too, dont worry about error flag here
			  	if ($postingSchedulerTO->DMLType=="DELETE") {
	    			$sql="DELETE FROM scheduler_job where sheduler_uid = ".$postingSchedulerTO->uid;
	    			$resultTO2 = $this->dbConn->processPosting($sql,$postingSchedulerTO->uid);
	    			if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
	    				$this->errorTO->description.="<BR>History successfully removed additionally.";
	    			} else {
	    				$this->errorTO->description.="<BR>History failed to be removed additionally. ".$resultTO->description;
	    			}
	    		}

  		  	    return $this->errorTO;
			  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
    }


    public function postScheduleJob($postingSchedulerJobTO) {

   		 if ($postingSchedulerJobTO->DMLType=="INSERT") {
   			$sql="INSERT INTO scheduler_job
   				  (
					scheduler_uid,
					run_date,
					queued_date,
					run_result,
					run_msg,
					attachment_file,
					distribution_source_identifier
   				  )
   				  VALUES (".
   				  	"'".$postingSchedulerJobTO->schedulerUId."',".
   				  	"'".$postingSchedulerJobTO->runDate."',".
   				  	"'".$postingSchedulerJobTO->queuedDate."',".
   				  	"'".$postingSchedulerJobTO->runResult."',".
   				  	"'".$postingSchedulerJobTO->runMsg."',".
   				  	"'".$postingSchedulerJobTO->attachmentFile."',".
   				  	"'".$postingSchedulerJobTO->distributionSourceIdentifier."'".
   				  ")";
   		 } else {
   		 	$sql="update scheduler_job
   				  set run_date='".$postingSchedulerJobTO->runDate."',					  
					  run_result='".$postingSchedulerJobTO->runResult."',
					  run_msg='".$postingSchedulerJobTO->runMsg."',
					  attachment_file='".$postingSchedulerJobTO->attachmentFile."',
					  distribution_source_identifier='".$postingSchedulerJobTO->distributionSourceIdentifier."'
				  where uid = '".$postingSchedulerJobTO->UId."' ";
   		 }

 		 $this->errorTO = $this->dbConn->processPosting($sql,$postingSchedulerJobTO->UId);

		  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
		  	if ($postingSchedulerJobTO->DMLType=="INSERT") {
		  		$this->errorTO->description="Schedule Job Successfully inserted.";
		  		$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId(); // get the UID just created
		  	} else if ($postingSchedulerJobTO->DMLType=="UPDATE") {
		  		$this->errorTO->description="Schedule Job Successfully Updated.";
		  		$this->errorTO->identifier=$postingSchedulerJobTO->UId;
		  	}
		  	return $this->errorTO;
		  }

    	return $this->errorTO;
    }


    public function setScheduleJobResult($UId, $runResult, $runMsg) {

  		$sql="update scheduler_job
  			  set run_result = '".mysqli_real_escape_string($this->dbConn->connection, $runResult)."',
				  run_msg = '".mysqli_real_escape_string($this->dbConn->connection, $runMsg)."'
  			  where uid = '".mysqli_real_escape_string($this->dbConn->connection, $UId)."'";

	  $this->errorTO = $this->dbConn->processPosting($sql,$UId);

	  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
	  		$this->errorTO->description="ScheduleJob Successfully updated.";
	  		$this->errorTO->identifier=$UId; // get the UID just updated
	  }

      return $this->errorTO;
    }

     public function setScheduleJobDistribution($UId, $distributionSourceIdentifier) {

  		$sql="update scheduler_job
  			  set distribution_source_identifier = '".mysqli_real_escape_string($this->dbConn->connection, $distributionSourceIdentifier)."'
  			  where uid = '".mysqli_real_escape_string($this->dbConn->connection, $UId)."'";

	  $this->errorTO = $this->dbConn->processPosting($sql,$UId);

	  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
	  		$this->errorTO->description="ScheduleJob Successfully updated.";
	  		$this->errorTO->identifier=$UId; // get the UID just updated
	  }

      return $this->errorTO;
    }


    public function setScheduleResult($sId, $runDate) {

  		$sql="update scheduler
  			  set last_run_date = '".mysqli_real_escape_string($this->dbConn->connection, $runDate)."', regenerate = 'N'
  			  where uid = '".mysqli_real_escape_string($this->dbConn->connection, $sId)."'";

	  $this->errorTO = $this->dbConn->processPosting($sql,$sId);

	  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
	  		$this->errorTO->description="Schedule Successfully updated.";
	  		$this->errorTO->identifier=$sId; // get the UID just updated
	  }

      return $this->errorTO;
    }

}
?>

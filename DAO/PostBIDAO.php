<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostBIDAO {
	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
    }

    public function postNotificationValidation($postingNotificationRecipientsTO) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/BIDAO.php");

		$biDAO = new BIDAO($this->dbConn);
		$administrationDAO = new AdministrationDAO($this->dbConn);

		if (!ValidationCommonUtils::checkPostingType($postingNotificationRecipientsTO->DMLType)) return false;

		$mfN = $biDAO->getNotificationItem($postingNotificationRecipientsTO->notificationUId);
		if (sizeof($mfN)==0) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Notification type specified.";
			return false;
		}

		// get user. don't pass it because this is more secure.
    	if (!isset($_SESSION)) session_start();
    	$principalId = $_SESSION['principal_id']; // used for hasRole validation
    	$userId = $_SESSION['user_id'];
    	$staffUser = $_SESSION['staff_user'];

    	if (($mfN[0]["system_category"]=="EXPORT") && ($staffUser!="Y")) {
    		$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Notifications of type EXPORT can only be modified by a RT Staff Member";
			return false;
    	}

    	if ($principalId!=$postingNotificationRecipientsTO->principalUId) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Principal Id differs from that passed!";
			return false;
		}

		// it is necessary to enforce SU role check here, because some notification such as CAPTURE DUPLICATE check must not be able to be changed by user themselves unless SU
		$hasRoleSU = $administrationDAO->hasRoleSuperUser($userId,$principalId);
		if (!$hasRoleSU===true) {
			echo "You do not have permissions (Super User Role) to modify notifications!";
			return;
		}


		if (($mfN[0]["value_required"]=="Y") && ($postingNotificationRecipientsTO->value=="")) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Value ({$mfN[0]["value_description"]}) is a required field.";
			return false;
		}

		if ($postingNotificationRecipientsTO->value!="") {
			// value type = T same as non blank field so skip...
			if (($mfN[0]["value_type"]=="I") && (!preg_match(GUI_PHP_INTEGER_REGEX,$postingNotificationRecipientsTO->value))) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Integer Value expected (no decimal).";
				return false;
			}
			if (($mfN[0]["value_type"]=="F") && (!preg_match(GUI_PHP_FLOAT_REGEX,$postingNotificationRecipientsTO->value))) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Float/Decimal Value expected.";
				return false;
			}
			if ($mfN[0]["values_allowed"]!="") {
				$valuesAllowed=explode(",",$mfN[0]["values_allowed"]);
				if (sizeof($valuesAllowed)>0) {
					$found=false;
					foreach ($valuesAllowed as $v) {
						if ($v==$postingNotificationRecipientsTO->value) {
							$found=true;
							break;
						}
					}
					if (!$found) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="Invalid Value - not one of allowed values.".$mfN[0]["values_allowed"];
						return false;
					}
				}
			}
		}

		if ($postingNotificationRecipientsTO->outputType=="") {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Output type not specified.";
			return false;
		}
		$valuesAllowed=explode(",",$mfN[0]["output_types_allowed"]);
		if (sizeof($valuesAllowed)>0) {
			$found=false;
			foreach ($valuesAllowed as $v) {
				if ($v==$postingNotificationRecipientsTO->outputType) {
					$found=true;
					break;
				}
			}
			if (!$found) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Output Type - not one of allowed values.";
				return false;
			}
		}

		if ($postingNotificationRecipientsTO->deliveryType=="") {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Delivery type not specified.";
			return false;
		}
		$valuesAllowed=explode(",",$mfN[0]["delivery_types_allowed"]);
		if (sizeof($valuesAllowed)>0) {
			$found=false;
			foreach ($valuesAllowed as $v) {
				if ($v==$postingNotificationRecipientsTO->deliveryType) {
					$found=true;
					break;
				}
			}
			if (!$found) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Delivery Type - not one of allowed values.";
				return false;
			}
		}

		// validate additional parameters
		if ($mfN[0]["additional_parameters_required"]!="") {
			$aPR=explode(",",$mfN[0]["additional_parameters_required"]);
			$params=explode("&",$postingNotificationRecipientsTO->additionalParameterString); // split up the supplied params
			foreach ($aPR as $req) {
				$found=false;
				foreach ($params as $p) {
					$valArr=explode("=",$p);
					if ((isset($valArr[0])) && ($valArr[0]=="p{$req}") && (isset($valArr[1])) && ($valArr[1]!="")) {
						$found=true;
						break;
					}
				}
				if (!$found) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="A required additional parameter was not found with a value";
					return false;
				}
			}

		}

		if (($mfN[0]["recipients_required"]=="Y") && ($postingNotificationRecipientsTO->userUIdList=="")) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="No Recipients/Contacts specified.";
			return false;
		}
		if ($postingNotificationRecipientsTO->userUIdList!="") {
			$recipients=explode(",",$postingNotificationRecipientsTO->userUIdList);
			foreach ($recipients as $r) {
				if ($mfN[0]["recipient_type"]==NRT_CONTACT) {
					$miscellaneousDAO = new MiscellaneousDAO($this->dbConn);
					$mfPC = $miscellaneousDAO->getContactItem($principalId,"",$r);
					if (sizeof($mfPC)==0) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="Invalid Contact UId ({$r})";
						return false;
					}
				} else {
					$mfU=$administrationDAO->getUserItem($r); // dont worry about checking whether user within priviledges
					if (sizeof($mfU)==0) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="Invalid Recipient UId ({$r})";
						return false;
					}
					if (($postingNotificationRecipientsTO->deliveryType==BT_EMAIL) && (!preg_match(GUI_PHP_EMAIL_REGEX,$mfU[0]["user_email"]))) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="Chosen User's ({$mfU[0]["full_name"]}) email address is not a valid format for delivery.";
						return false;
					}
					if (($postingNotificationRecipientsTO->deliveryType==BT_SMS) && (!preg_match(GUI_PHP_MOBILE_REGEX,$mfU[0]["user_cell"]))) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="Chosen User's ({$mfU[0]["full_name"]}) cell/mobile number is not a valid format for delivery.";
						return false;
					}
					if (($mfU[0]["suspended"]=="1") || ($mfU[0]["deleted"]=="1")) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="Chosen User ({$mfU[0]["full_name"]}) has been suspended or deleted.";
						return false;
					}
					$dayDiff=floor((strtotime(date(GUI_PHP_DATETIME_FORMAT))-strtotime($mfU[0]["lastlogin"]))/24/60/60);
					if ($dayDiff>30) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="Chosen User ({$mfU[0]["full_name"]}) has not logged in within the last 30 days, and therefore is disallowed.";
						return false;
					}
				}
			}
		}


		if ($postingNotificationRecipientsTO->DMLType=="INSERT") {
			if ($postingNotificationRecipientsTO->UId!="") {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Insert specified, but UID not blank";
				return false;
			}
		} else {
			$mfNR=$biDAO->getNotificationRecipientItem($postingNotificationRecipientsTO->UId);
			if (sizeof($mfNR)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Notification Recipient cannot be found for UPDATE/DELETE";
				return false;
			}
		}

		return true;

    }

    public function postNotification($postingNotificationRecipientsTO) {
    	$resultOK = $this->postNotificationValidation($postingNotificationRecipientsTO);
    	if ($resultOK) {
    		if ($postingNotificationRecipientsTO->DMLType=="INSERT") {
    			$sql="INSERT INTO notification_recipients
    				  (
						notification_uid,
						principal_uid,
						user_uid_list,
						value,
						output_type,
						delivery_type,
						additional_parameter_string
    				  )
					  VALUES (
						{$postingNotificationRecipientsTO->notificationUId},
						{$postingNotificationRecipientsTO->principalUId},
						'{$postingNotificationRecipientsTO->userUIdList}',
						'{$postingNotificationRecipientsTO->value}',
						{$postingNotificationRecipientsTO->outputType},
						{$postingNotificationRecipientsTO->deliveryType},
						'{$postingNotificationRecipientsTO->additionalParameterString}'
					  )";
    		} else if ($postingNotificationRecipientsTO->DMLType=="UPDATE") {
    			$sql="UPDATE notification_recipients
					  SET notification_uid='{$postingNotificationRecipientsTO->notificationUId}',
						  user_uid_list='{$postingNotificationRecipientsTO->userUIdList}',
						  value='{$postingNotificationRecipientsTO->value}',
						  output_type='{$postingNotificationRecipientsTO->outputType}',
						  delivery_type='{$postingNotificationRecipientsTO->deliveryType}',
						  additional_parameter_string='{$postingNotificationRecipientsTO->additionalParameterString}'
					  WHERE uid = '{$postingNotificationRecipientsTO->UId}'";
    		} else if ($postingNotificationRecipientsTO->DMLType=="DELETE") {
    			$sql="DELETE FROM notification_recipients
					  WHERE uid = '{$postingNotificationRecipientsTO->UId}'";
    		}

   		  $this->errorTO = $this->dbConn->processPosting($sql,"");

		  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
		  	if ($postingNotificationRecipientsTO->DMLType=="INSERT") {
		  		$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId();
		  		$this->errorTO->description="Notification Successfully Activated";
		  	} else if ($postingNotificationRecipientsTO->DMLType=="UPDATE")	$this->errorTO->description="Notification Successfully Updated";
		  	else if ($postingNotificationRecipientsTO->DMLType=="DELETE")	$this->errorTO->description="Notification Successfully Deleted";
		  	return $this->errorTO;
		  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
    }

    // only use if error, due to error_count incrementation
    public function postNotificationRecipientResult($nrUId, $status, $statusMsg) {
       echo $statusMsg;
       echo $status;
       echo $nrUId;


    	if ($status==FLAG_ERRORTO_ERROR) {
 			$sql="UPDATE notification_recipients
				  SET status_msg=if(error_count>=9,'Service Suspended due to error count.','{$statusMsg}'),
					  service_status=if(if(error_count is null,0,error_count)>=9,'".FLAG_STATUS_SUSPENDED."',service_status),
					  error_count=if(error_count is null,0,error_count)+1
				  WHERE uid = '{$nrUId}'";
    	} else {
    		$sql="UPDATE notification_recipients
				  SET status_msg='{$statusMsg}',
					  service_status='{$status}',
					  error_count=0
				  WHERE uid = '{$nrUId}'";
    	}

   		  $this->errorTO = $this->dbConn->processPosting($sql,"");

		  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
		  	$this->errorTO->description="Notification Result successfully set";
		  	return $this->errorTO;
		  }

    	return $this->errorTO;
    }

    public function postNotificationRecipientDistribution($nrUId, $dSI) {
 			$sql="UPDATE notification_recipients
				  SET distribution_source_identifier='{$dSI}'
				  WHERE uid = '{$nrUId}'";

   		  $this->errorTO = $this->dbConn->processPosting($sql,"");

		  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
		  	$this->errorTO->description="Notification Recipient successfully set";
		  	return $this->errorTO;
		  }

    	return $this->errorTO;
    }

    public function postNotificationRecipientStart($uId) {
    	$this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA)
 		$sql="UPDATE notification_recipients
			  SET run_date=now(),
				  status_msg = ''
			  WHERE uid = '{$uId}'";

   		$this->errorTO = $this->dbConn->processPosting($sql,"");

		if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
		 	$this->errorTO->description="Notification Recipient successfully set";
		  	return $this->errorTO;
		}

    	return $this->errorTO;
    }

    //IT Dynamic  - FILE CONFIRMATION Export
	public function queueAllExportFileLog() {
		global $errorTO, $dbConn;

		$dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time
		// NB !! The processed date MUST BE GMT !! as the BI trigger export file log (9) compares run dates against this and it expects GMT

		// NB !! This can only work as long as single selection radio for p3, not a checkbox on notification form when loading export confirmation !!
		// It will also keep sending file exports for files where the duplicate status / error has been unresolved since last run
		$sql="	insert into smart_event (created_date, type, type_uid, processed_date, status, status_msg, data_uid)

            select now(), 'E', nr.uid, null, '".FLAG_STATUS_QUEUED."', '', a.uid
            from   file_log a,
                   notification n,
                   notification_recipients nr
            where  n.uid = nr.notification_uid
            and    n.uid = ".NT_EDIFILEDEF_EXPORT."
            and    nr.additional_parameter_string REGEXP concat('[&]p3=',a.online_file_processing_uid,'([&]|$)') -- must be done like this to avoid matching p3=3 with p3=32 etc.
            and    not exists (select 1 from smart_event se
                               where se.data_uid = a.uid
                               and se.type = 'E'
                               and se.type_uid = nr.uid
                               and (se.created_date >= a.processed_date or
                              (se.created_date < a.processed_date and se.status = '".FLAG_STATUS_QUEUED."')))
            and    a.processed_date > curdate() - interval if(nr.run_date is null,0,3) day -- protect against weekend fallovers";

		$this->dbConn->dbinsQuery($sql);

		if (!$this->dbConn->dbQueryResult) {
			$this->errorTO->type = FLAG_ERRORTO_ERROR;
			$this->errorTO->description = "Failed to insert into smart_event in postExportDAO->queueAllExportFileLog";
		} else {
			$this->errorTO->type = FLAG_ERRORTO_SUCCESS;
			$this->errorTO->description = "Successful";
		}

		return $this->errorTO;
	}

  public function postSmartEvent($smartEventTO){

    $this->dbConn->dbQuery("SET time_zone='+0:00'");

    $sql = "select 1
            from smart_event se
            where se.data_uid=".mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->dataUid)."
            and   se.type='".mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->type)."'
            and   se.type_uid=".mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->typeUid)."
            and   se.status in ('Q','E')";

    $checkRows = $this->dbConn->dbGetAll($sql);

    if (count($checkRows)>0) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Smart Event Insert Failed - ".mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->dataUid) ." - there is already a queued entry or an entry in error!";
      return $this->errorTO;
    }

    $sql="INSERT INTO `smart_event`
          (`created_date`, `type`, `type_uid`, `status`, `data_uid` , `general_reference_1`, `general_reference_2`)
          VALUES (
            NOW(),
            '" . mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->type) . "',
            "  . mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->typeUid) . ",
            '" . FLAG_STATUS_QUEUED . "',
            "  . mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->dataUid) . ",
            '" . mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->generalReference1) . "',
            '" . mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->generalReference2) . "'
          )";

    $this->dbConn->dbinsQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Smart Event Insert Failure!";
    } else {
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Smart Event Successful";
      $this->errorTO->identifier = $this->dbConn->dbGetLastInsertId();
    }

    return $this->errorTO;

  }

  public function removeSmartEventsByTypeData($type, $typeUid, $dataUid) {

    $sql="delete from `smart_event`
          where type = '{$type}'
          and   type_uid = {$typeUid}
          and   data_uid = {$dataUid}";

    $this->dbConn->dbinsQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Smart Event delete Failure!";
    } else {
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Smart Event delete Successful";
    }

    return $this->errorTO;
  }

  // use this also if you want to control the status directly
  public function postSmartEventBulk($smartEventTO){

    $this->dbConn->dbQuery("SET time_zone='+0:00'");

    if (!is_array($smartEventTO->dataUid)) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Smart Event Bulk Insert Failure : no data uids supplied / is not an array";
      return $this->errorTO;
    }

    $values=array();
    foreach ($smartEventTO->dataUid as $d) {
      $values[] = "(
                    NOW(),
                    '" . mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->type) . "',
                    " . mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->typeUid) . ",
                    '" . mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->status) . "',
                    '" . substr(mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->statusMsg),0,256) . "',
                    " . mysqli_real_escape_string($this->dbConn->connection, $d) . ",
                    '" . mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->generalReference1) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $smartEventTO->generalReference2) . "'
                   )";
    }

    if (sizeof($values)==0) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Smart Event Bulk Insert Failure : no data uids supplied / is not an array";
      return $this->errorTO;
    }

    $sql="INSERT INTO `smart_event`
          (`created_date`, `type`, `type_uid`, `status`, `status_msg`, `data_uid` , `general_reference_1`, `general_reference_2`)
          VALUES ".implode(",",$values);

    $this->dbConn->dbinsQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Smart Event Bulk Insert Failure!".$sql;
    } else {
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Smart Event Bulk Successful";
    }

    return $this->errorTO;

  }
  public function setSmartEventStatus($smartUid, $general1 = "", $general2 = "") {

    $this->dbConn->dbQuery("SET time_zone='+0:00'");

    // events remain queued if the notification fails anywhere, so always select using Q status
    $general1 = ($general1!="") ? ("'" . mysqli_real_escape_string($this->dbConn->connection, substr($general1, 0, 50)) . "'") : ('NULL');
    $general2 = ($general2!="") ? ("'" . mysqli_real_escape_string($this->dbConn->connection, substr($general2, 0, 50)) . "'") : ('NULL');

    $sql="UPDATE smart_event
          SET  status = '".FLAG_STATUS_CLOSED."',
               general_reference_1 = ".$general1.",
               general_reference_2 = ".$general2.",
               processed_date = NOW()
          WHERE  uid = {$smartUid}";

    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed to update smart_event in postBIDAO->setSmartEventStatus";
    } else {
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "Successful";
    }

    return $this->errorTO;

  }

  public function setSmartEventStatusBulk($smartUidList, $general1 = "", $general2 = "", $statusFlag = FLAG_STATUS_CLOSED) {

    $this->dbConn->dbQuery("SET time_zone='+0:00'");

    // events remain queued if the notification fails anywhere, so always select using Q status
    $general1 = ($general1!="") ? ("'" . mysqli_real_escape_string($this->dbConn->connection, substr($general1, 0, 80)) . "'") : ('NULL');
    $general2 = ($general2!="") ? ("'" . mysqli_real_escape_string($this->dbConn->connection, substr($general2, 0, 80)) . "'") : ('NULL');

    $sql="UPDATE smart_event
          SET  status = '".$statusFlag."',
               general_reference_1 = ".$general1.",
               general_reference_2 = ".$general2.",
               processed_date = NOW()
          WHERE  uid in ({$smartUidList})";
          
    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to update smart_event in postBIDAO->setSmartEventStatusBulk";
      
      echo "<br>";
      echo $sql;
      echo "<br>";
      
    } else {
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;

  }
// ***********************************************************************************************************************************
  public function setSmartEventStatusIndivNew($smartUidList, $statusFlag = FLAG_STATUS_CLOSED) {

    $this->dbConn->dbQuery("SET time_zone='+0:00'");

       foreach($smartUidList as $line) {
       	   $errUid = substr($line,0,strpos($line, '&'));
           $genErr = trim(substr($line,strpos($line, '&') + 1,20));
           
           $sql="UPDATE smart_event  SET  status = '".$statusFlag."',
                                                      general_reference_1 = '".$genErr."',
                                                      general_reference_2 = '',
                                                      processed_date = NOW()
                 WHERE  uid in (" . mysqli_real_escape_string($this->dbConn->connection,$errUid) . ")";

           $this->dbConn->dbQuery($sql);

           if (!$this->dbConn->dbQueryResult) {
                  $this->errorTO->type = FLAG_ERRORTO_ERROR;
                  $this->errorTO->description = "Failed to update smart_event in postBIDAO->setSmartEventStatusBulk";
      
                  echo "<br>";
                  echo $sql;
                  echo "<br>";
      
           } else {
                $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                $this->errorTO->description = "Successful";
           }
       }       
       return $this->errorTO;

}
// ***********************************************************************************************************************************  

  // only update if was not set via export depot file
  public function setDMDopFile($dmUIdList, $filename) {
 
    $sql="UPDATE document_master a
                  left join orders b on a.order_sequence_no = b.order_sequence_no and a.principal_uid = b.principal_uid
          set   a.dop_file = if(ifnull(a.dop_file,'')='','{$filename}',a.dop_file),
                b.edi_depot_filename = if(ifnull(b.edi_depot_filename,'')='','{$filename}',b.edi_depot_filename)
          WHERE  a.uid in ({$dmUIdList})";

    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to dop file in postBIDAO->setDMDopFile";
    } else {
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;

  }

// ************************************************************************************************************************************
  public function setOmniImport($dmUIdList) {

    $sql="update document_master dm 
          inner JOIN smart_event se ON dm.uid = se.data_uid AND se.type_uid = 787 set dm.merged_date = CURDATE(), 
                                                                                      dm.merged_time = CURTIME(), 
                                                                                      dm.rwr_file =    'Omni Success'
          WHERE  se.uid in (" . mysqli_real_escape_string($this->dbConn->connection,$dmUIdList) . ")";
          
          echo $sql;

    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to Omni Import Update in postBIDAO->setDMDopFile";
    } else {
    	$this->dbConn->dbQuery("commit");
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;

  }
// ************************************************************************************************************************************
  public function setOmniImportAll($dmUIdList, $setype, $dhStatus) {

    $sql="update document_master dm 
          inner JOIN smart_event se ON dm.uid = se.data_uid AND se.type_uid = " . mysqli_real_escape_string($this->dbConn->connection,$setype) . " 
          inner JOIN document_header dh on dm.uid = dh.document_master_uid  SET dm.merged_date = CURDATE(), 
                                                                                dm.merged_time = CURTIME(), 
                                                                                dm.rwr_file = 'Omni Success',
                                                                                dh.document_status_uid = " . mysqli_real_escape_string($this->dbConn->connection,$dhStatus) . "
          WHERE se.uid in ({$dmUIdList})";

    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to Omni Import Update in postBIDAO->setOmniImportAll";
    } else {
    	$this->dbConn->dbQuery("commit");
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;

  }
// ************************************************************************************************************************************
  public function setDocumentConfirm($prinId, $dhStatus, $invStatus) {  
  	
  	if($prinId == 396) {
  		   $capVar = "AND dh.data_source like '%CAPTURE%'";  		
  	}  else {
  		  $capVar = "AND dh.data_source not like '%CAPTURE%'";
  	}
  	

    $sql="update document_master dm 
          inner JOIN document_header dh on dm.uid = dh.document_master_uid
          inner JOIN document_detail dd on dm.uid = dd.document_master_uid  set dd.document_qty = dd.ordered_qty,
                                                                                dh.document_status_uid = " . mysqli_real_escape_string($this->dbConn->connection,$invStatus) . ",
                                                                                dh.invoice_date = '" . date("Y-m-d") .  "'
          WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection,$prinId) . "
          " .  $capVar . "
          AND   dh.document_status_uid = " . mysqli_real_escape_string($this->dbConn->connection,$dhStatus) ;

    $this->dbConn->dbQuery($sql);
    
    if (!$this->dbConn->dbQueryResult) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed to Invoice Documents Update in postBIDAO->setDocumentConfirm";
    } else {   	
    	    $this->dbConn->dbQuery("commit");
          $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
          $this->errorTO->description = "Successful";
            
          $inssql = "INSERT INTO .smart_event (smart_event.created_date,
                                  smart_event.`type`,
                                  smart_event.type_uid,
                                  smart_event.`status`,
                                  smart_event.data_uid)
     
                     SELECT NOW(), 'N', nr.uid, 'Q', dm.uid
                     FROM .document_master dm
                     INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid
                     LEFT JOIN notification_recipients nr ON trim(nr.p1) = dm.depot_uid
                                                          AND dh.captured_by in (trim(nr.p4))
                                                          AND nr.principal_uid = dm.principal_uid
                     WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection,$prinId) . "
                     AND   dh.document_status_uid = " . mysqli_real_escape_string($this->dbConn->connection,$invStatus) ."
                     AND   dh.order_date > curdate() - interval 1 day
                     " .  $capVar . "
                     AND NOT EXISTS (SELECT 1 
                                     FROM smart_event se
                                     WHERE se.data_uid = dm.uid
                                     AND se.type = 'N'
                                     AND se.type_uid = nr.uid);";
          
            $this->errorTO = $this->dbConn->processPosting($inssql,"");
           
            if($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                     $this->dbConn->dbQuery("commit");
            }
                 
            $seInsert = $this->errorTO->type;
            return $seInsert;      
     }

     return $this->errorTO;

  }
// ************************************************************************************************************************************
  public function setSmartEventStatusIndividual($UidList, $general1 = "", $general2 = "", $statusMsg , $statusFlag) {

    $this->dbConn->dbQuery("SET time_zone='+0:00'");
    
    $general1 = ($general1!="") ? ("'" . mysqli_real_escape_string($this->dbConn->connection, substr($general1, 0, 80)) . "'") : ('NULL');
    $general2 = ($general2!="") ? ("'" . mysqli_real_escape_string($this->dbConn->connection, substr($general2, 0, 80)) . "'") : ('NULL');

    $sql="UPDATE smart_event SET  status              = '".$statusFlag."',
                                  general_reference_1 = ".$general1.",
                                  general_reference_2 = ".$general2.",
                                  status_msg          = '" . substr(mysqli_real_escape_string($this->dbConn->connection, trim($statusMsg)),0,254) . "',
                                  processed_date      = NOW()
          WHERE  uid in ({$UidList})" ;

//  echo "<br>";
//  echo $sql;
//  echo "<br>";


    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to update smart_event in postBIDAO->setSmartEventStatusIndividual";
      
      echo "<br>";
      echo $sql;
      echo "<br>";
      
    } else {
    	$this->dbConn->dbQuery("commit");
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;

  }
// ************************************************************************************************************************************
  public function setOmniUpdateTrans($dmUIdList, $setype, $dhStatus) {

    $sql="update document_master dm 
          inner JOIN smart_event se ON dm.uid = se.data_uid AND se.type_uid = " . mysqli_real_escape_string($this->dbConn->connection,$setype) . " 
          inner JOIN document_header dh on dm.uid = dh.document_master_uid  
          inner JOIN document_detail dd on dm.uid = dd.document_master_uid  SET dm.merged_date = CURDATE(), 
                                                                                dm.merged_time = CURTIME(), 
                                                                                dm.rwr_file = 'Omni Success',
                                                                                dh.document_status_uid = " . mysqli_real_escape_string($this->dbConn->connection,$dhStatus) . ",
                                                                                dd.document_qty = if(" . mysqli_real_escape_string($this->dbConn->connection,$dhStatus) ." = " . DST_INVOICED . ",dd.ordered_qty, dd.document_qty)
          WHERE dm.uid in ({$dmUIdList})";

    $this->dbConn->dbQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to Omni Import Update in postBIDAO->setOmniImportAll";
    } else {
    	$this->dbConn->dbQuery("commit");
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;

  }
  
// ************************************************************************************************************************************
  public function setSeErrorCount($seUid) {
  	
          $esql="UPDATE smart_event se SET  se.error_count = se.error_count + 1
          WHERE  uid = " . mysqli_real_escape_string($this->dbConn->connection,$seUid) ;

          $this->dbConn->dbQuery($esql);

          if (!$this->dbConn->dbQueryResult) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Failed to Update Smart Event Errors";
          } else {
    	          $this->dbConn->dbQuery("commit");
                $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                $this->errorTO->description = "Successful";
          }

          return $this->errorTO;

  } 	

  
// ************************************************************************************************************************************
 
}

?>
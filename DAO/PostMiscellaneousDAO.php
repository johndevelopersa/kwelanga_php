<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostMiscellaneousDAO {
	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
  }


    /*
     *
     * Update Store Principal Details
     *
     */


  // NB: This code relies on the passed being one for every SFF, and not for just what user entered or select few !
  public function postSpecialFieldValidation($value, $fieldUid, $DMLType, $depotUId=false){

    $value = trim($value);

    global $ROOT, $PHPFOLDER;
    include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
    $miscDAO = new MiscellaneousDAO($this->dbConn);

    $spfArr = $miscDAO->getPrincipalSpecialFieldbyUid($fieldUid);

    //this should never occur if so - pray to the GODS if this Code executes.
    if(!count($spfArr)>0){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Special Field Field Not Found!";
      return $this->errorTO;
    }

    // skip validation if doesn't apply to depot
    // at the moment this should only be used for store special fields, but theoretically even a depot special field could be validated
    // simply by passing a depotUID that is not false. But only submitStore and postOrder uses this for time being
    if (($depotUId!==false) && ($spfArr[0]["validation_depot_list"]!="")) {
      if (!in_array($depotUId,explode(",",$spfArr[0]["validation_depot_list"]))) {
        return true; // supplied depot is not one of ones that validation applies to
      }
    }

    $spfName = $spfArr[0]['name'];

    //FIELD IS REQUIRED
    //empty(0) = true : Therefore 'zero' might be a value of sorts... therefore check that too.
    if($spfArr[0]['required'] == 'Y' && empty($value) && $value != '0'){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = $spfName . " is Required!";  //$output using the special fields name for users :)
      return $this->errorTO;
    }

    //fail if field is not 'editable' and DML is UPDATE.
    if($spfArr[0]['editable'] == 'N' && $DMLType == 'UPDATE'){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = $spfName . " is marked as a non-editable field!";
      return $this->errorTO;
    }

    //db field is INT therefore ONLY validate if greater or equal to 1, exclude 0 and NULL
    //field is greater or equal to min length
    if($spfArr[0]['value_min_length'] >= 1 && !(strlen($value) >= $spfArr[0]['value_min_length'])){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = $spfName . " is too SHORT for inputted value, minimum is ".$spfArr[0]['value_min_length']." character(s).";
      return $this->errorTO;
    }

    //db field is INT therefore ONLY validate if greater or equal to 1, exclude 0 and NULL
    //field value must be equal or less than max length
    if($spfArr[0]['value_max_length'] >= 1 && !(strlen($value) <= $spfArr[0]['value_max_length'])){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = $spfName . " is too LONG for inputted value, maximum is ".$spfArr[0]['value_max_length']." character(s).";
      return $this->errorTO;
    }

    //numeric
    if(trim($spfArr[0]['value_validation']) == 'NUMERIC' && !is_numeric($value)){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = $spfName . " is a numeric field - digits are only allowed!";
      return $this->errorTO;
    }

    //text
    if(trim($spfArr[0]['value_validation']) == 'TEXT' && !ctype_alpha($value)){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = $spfName . " is an alpha field - letters are only allowed!";
      return $this->errorTO;
    }

    //date
    //DATE CAN BE EMPTY! SET THE REQUIRED FIELD ON TO REQUIRE ALWAYS... as a field type DOES NOT make it required!
    if(trim($spfArr[0]['value_validation']) == 'DATE' && strlen($value)>=1){
      if (preg_match(GUI_PHP_DATE_VALIDATION, $value, $parts) !== false) {
        if(count($parts) != 4){
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = $spfName . " only accepts a date format (YYYY-MM-DD). Malformed entry detected!";
          return $this->errorTO;
        } else if(!checkdate($parts[2],$parts[3],$parts[1])) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = $spfName . " only accepts a date format (YYYY-MM-DD). Date validation failed!";
          return $this->errorTO;
        }
      }
    }

    //CheckBox - you cant have a radio without a list of allowed values !
    if(trim($spfArr[0]['value_validation']) == 'RADIO'){
      $parts=explode("?",$spfArr[0]['value_list']);
      if (!in_array($value,explode(",",$parts[1]))) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = $spfName . "  - This field may only contain a selection from the list of values provided!";
        return $this->errorTO;
      }
    }

    //preg
    if(substr(trim($spfArr[0]['value_validation']),0,4) == 'PREG' && strlen($value)>1){

      $pregArr = explode('=',$spfArr[0]['value_validation']);
      if(isset($pregArr[1]) && strlen($pregArr[1])>2){
        if(preg_match($pregArr[1], $value, $parts) !== false){
          if(!count($parts)>0){  //parts must contain atleast one found match - might be a better way...
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = $spfName . " - entry is invalid!";  //how do we describe an unknown validation?!?!
            return $this->errorTO;
          }
        }
      }
    }

    return true;

  }

/*
 * BE CAREFUL : when calling this, if you only supply 1 field value for a specific field_uid, and the db has multiple values loaded, the 3 will be deleted and only 1 created in place of
 */

  function postSpecialField($postingSpecialFieldTO) {
	// NB: blanks are still validated, just not updated or inserted
      	file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/sql.txt', print_r($postingSpecialFieldTO, TRUE), FILE_APPEND);

	// first check if contents have any non blank values
	$hasOnlyNonBlankValue=true;
	if (is_array($postingSpecialFieldTO->value)) {
      foreach($postingSpecialFieldTO->value as $value){
        if (trim($value)!="") { $hasOnlyNonBlankValue=false; break; }
      }
  } else {
    if (trim($postingSpecialFieldTO->value)!="") { $hasOnlyNonBlankValue=false; }
  }

	// perform validation if not bypassed, this MUST be called even for blanks so that validation can be checked
	if ($postingSpecialFieldTO->skipValidation!="Y") {
	    if (is_array($postingSpecialFieldTO->value)) {
	      foreach($postingSpecialFieldTO->value as $value){
	        $resultOK = $this->postSpecialFieldValidation($value, $postingSpecialFieldTO->fielduid, $postingSpecialFieldTO->DMLType, $depotUId=$postingSpecialFieldTO->depotUId);
	        if (!$resultOK) break;
	      }
	    } else {
	      $resultOK = $this->postSpecialFieldValidation($postingSpecialFieldTO->value, $postingSpecialFieldTO->fielduid, $postingSpecialFieldTO->DMLType, $depotUId=$postingSpecialFieldTO->depotUId);
	    }
	} else $resultOK=true;

	// Store in DB
    if ($resultOK===true) {

	  // leave untouched if no values
	  if ($hasOnlyNonBlankValue) {
	  	$this->errorTO->type = FLAG_ERRORTO_SUCCESS; // must not be treated as an error as EDI sometimes requires the store to be created and will rollback if error returned from here
	  	$this->errorTO->description = "No Values Found. No Updates or Deletes made";
	  	return $this->errorTO;
	  }

      $userId = $_SESSION['user_id'];

  	   //delete all existing fields - this extra check on value is to prevent unnecessary special field audit triggers
      $sql = "DELETE FROM `special_field_details` ".
        		 "WHERE `entity_uid`  = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSpecialFieldTO->entityUId) . "'  ".
        		 "AND `field_uid`     = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSpecialFieldTO->fielduid) . "' ";

      if (is_array($postingSpecialFieldTO->value)) {
        $sql .= " AND `value`         not in ('" . mysqli_real_escape_string($this->dbConn->connection, implode("','",$postingSpecialFieldTO->value)) . "') ";
      } else {
        $sql .= " AND `value`         != '" . mysqli_real_escape_string($this->dbConn->connection, $postingSpecialFieldTO->value) . "' ";
      }

      $this->errorTO = $this->dbConn->processPosting($sql, $postingSpecialFieldTO->value);

      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description .= "Failed to update special field (".$postingSpecialFieldTO->fielduid."). A possible reason is that entity uid on this table has not been updated during data migration.";
      } else {


	    //INSERT NEW OR UPDATED FIELDS.
      if (is_array($postingSpecialFieldTO->value)) {
      	

        $sqlArr = array();
        foreach($postingSpecialFieldTO->value as $value) {
          if ((trim($value)!="") && ($value!='0')) {
            // $sqlArr [] = " ( '" . mysqli_real_escape_string($this->dbConn->connection, $postingSpecialFieldTO->fielduid) . "', "."'" . mysqli_real_escape_string($this->dbConn->connection, $value) . "', " . "'" . mysqli_real_escape_string($this->dbConn->connection, $postingSpecialFieldTO->entityUId) . "', {$userId})";
            $sqlArr [] = " SELECT {$postingSpecialFieldTO->fielduid}, '" . mysqli_real_escape_string($this->dbConn->connection, $value) . "', {$postingSpecialFieldTO->entityUId}, {$userId}
                           FROM   special_field_fields sff
        	                 WHERE  sff.uid = {$postingSpecialFieldTO->fielduid}
        	                 AND    NOT EXISTS (
        	                         SELECT 1
        	                         FROM   special_field_details sfd
                	                 WHERE  `entity_uid`  = {$postingSpecialFieldTO->entityUId}
                             		   AND    `field_uid`   = {$postingSpecialFieldTO->fielduid}
                                   AND    `value`       = '" . mysqli_real_escape_string($this->dbConn->connection, $value) . "')";
          }
        }
        $sql = (count($sqlArr)>0) ? ("INSERT INTO  `special_field_details` (`field_uid`, `value`, entity_uid, last_change_by_userid )".
                                      join(' UNION ALL ', $sqlArr)) : ('');

      } else {
			if (trim($postingSpecialFieldTO->value)!="") {
	          $sql = "INSERT INTO  `special_field_details` (`field_uid`, `value`, entity_uid, last_change_by_userid )  ".
	          		   "SELECT ".
	                    "'" . mysqli_real_escape_string($this->dbConn->connection, $postingSpecialFieldTO->fielduid) . "', ".
	                    "'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingSpecialFieldTO->value)) . "', ".
	                    "'" . mysqli_real_escape_string($this->dbConn->connection, $postingSpecialFieldTO->entityUId) . "', ".
	                    "'" . $userId . "'
	                  FROM   special_field_fields sff
	                  WHERE  sff.uid = {$postingSpecialFieldTO->fielduid}
	                  AND    NOT EXISTS (
	                          SELECT 1
	                          FROM   special_field_details sfd
        	                  WHERE  `entity_uid`  = {$postingSpecialFieldTO->entityUId}
                      		  AND    `field_uid`   = {$postingSpecialFieldTO->fielduid}
                            AND    `value`       = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSpecialFieldTO->value) . "')";
			}
        }
        
        file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/sql.txt', $sql, FILE_APPEND);

        if (!empty($sql))
          $this->errorTO = $this->dbConn->processPosting($sql, $postingSpecialFieldTO->deliverName);

      }
      if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "Special Fields Successfully Created.";
      } else {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed to create Special Fields. ".$this->errorTO->description;
      }

      return $this->errorTO;

  	} else {
  	  return $this->errorTO;
  	}
  }


  public function postPrincipalContactValidation($postingPrincipalContactTO) {

    global $ROOT, $PHPFOLDER;

    if($postingPrincipalContactTO->principalUid != $_SESSION ['principal_id']){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Principal Session and Submitted Form Mismatch!";
      return $this->errorTO;
    }

    if(!in_array($postingPrincipalContactTO->DMLType, array('INSERT','UPDATE','DELETE'))){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Invalid DML Type.";
      return $this->errorTO;
    }

    if (!preg_match(GUI_PHP_EMAIL_REGEX,$postingPrincipalContactTO->emailAddr)){
	  $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Invalid Principal Contact E-mail Address.";
      return $this->errorTO;
    }

    if ($postingPrincipalContactTO->mobileNumber!='' && !is_numeric($postingPrincipalContactTO->mobileNumber)){
	  	$this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Mobile Number must be numeric.";
      return $this->errorTO;
    }

    if($postingPrincipalContactTO->DMLType=='DELETE'){

      $this->dbConn->dbQuery("select 1
														  from   notification_recipients
															where  find_in_set({$postingPrincipalContactTO->cuid}, user_uid_list)");

      if ($this->dbConn->dbQueryResultRows > 0) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
      	$this->errorTO->description = "This Contact cannot be deleted because there are notifications (System Messaging) loaded using this contact as a recipient. You need to remove this contact from those notifications first.";
      	return $this->errorTO;
      }

    }

    /*
     * Skip Validation on vendorUId
     */

    //check ftp host is set if so check other settings and test.

    if ($postingPrincipalContactTO->ftpAddr != ''){

      include_once($ROOT.$PHPFOLDER.'libs/FTPClass.php');
      $ftpArr = unserialize($postingPrincipalContactTO->ftpAddr);

	  //CHECK VALUES
	  if(empty($ftpArr['HOST'])){  //DUAL - ISSET AND != '' (NOTE: A VALUE: 0 = false)
		$this->errorTO->type=FLAG_ERRORTO_ERROR;
		$this->errorTO->description="Invalid FTP Host, please specify one.";
		return false;
	  }

	  if(empty($ftpArr['USR'])){
		$this->errorTO->type=FLAG_ERRORTO_ERROR;
		$this->errorTO->description="Invalid FTP Username, please specify one.";
		return false;
	  }

	  if(empty($ftpArr['PWD'])){
		$this->errorTO->type=FLAG_ERRORTO_ERROR;
		$this->errorTO->description="Invalid FTP Password, please specify one.";
		return false;
	  }

      if(empty($ftpArr['PORT'])){
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
	  $ftpResult = $ftpObj->sendFile($ftpArr['HOST'], $ftpArr['USR'], $ftpArr['PWD'], $ftpArr['FOLDER'], basename($dummyFile), false, $ftpArr['PORT'], $ftpArr['MODE']);

	  unlink($dummyFile);  //DELETE LOCAL FILE

	  //CHECK FTP TEST RESULTS? : CAN ONLY BE SUCCESS TO PASS
        if($ftpResult->type != FLAG_ERRORTO_SUCCESS){
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
		$this->errorTO->description = $ftpResult->description;
		return false;
        }

	}

	return true;

  }


  public function postPrincipalContact($postingPrincipalContactTO) {

    $userId = $_SESSION ['user_id'];
    $principalId = $_SESSION ['principal_id'];

    $resultOK = $this->postPrincipalContactValidation($postingPrincipalContactTO);

    if ($resultOK===true) {

        $depotUid = (!empty($postingPrincipalContactTO->depotUid)) ? ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->depotUid) . "'") : ('NULL');
        $ftpAddr = (!empty($postingPrincipalContactTO->ftpAddr)) ? ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->ftpAddr) . "'") : ('NULL');

        if($postingPrincipalContactTO->DMLType == 'INSERT'){

          $sql = "INSERT INTO `principal_contact`
          				  (
      						`principal_uid`,
      						`depot_uid`,
      						`contact_type_uid`,
      						`email_addr`,
      						`mobile_number`,
      						`ftp_addr`
          				  )
          				  VALUES (".
          				  "'" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->principalUid) . "',".
          				  "" . $depotUid . ",".
          				  "'" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->contactTypeUid) . "',".
          				  "'" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->emailAddr) . "',".
          				  "'" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->mobileNumber) . "',".
          				  "" . $ftpAddr . "
          				  )";
        } else if($postingPrincipalContactTO->DMLType == 'UPDATE'){

          $sql = "UPDATE `principal_contact`
          				  SET
      						`depot_uid` =  " . $depotUid . ",
      						`contact_type_uid` = '" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->contactTypeUid) . "',
      						`email_addr`= '" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->emailAddr) . "',
      						`mobile_number` =  '" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->mobileNumber) . "',
      						`ftp_addr` = " . $ftpAddr . "
          		   WHERE uid = '" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->cuid) . "'
          		   AND principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->principalUid) . "'";

         } else if($postingPrincipalContactTO->DMLType == 'DELETE'){

          $sql = "DELETE from `principal_contact`
          		   WHERE uid = '" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->cuid) . "'
          		   AND principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalContactTO->principalUid) . "'";

        }

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
          if($postingPrincipalContactTO->DMLType == 'INSERT'){
            $this->errorTO->description = 'Successfully Created Principal Contact';
          } else if($postingPrincipalContactTO->DMLType == 'UPDATE') {
            $this->errorTO->description = 'Successfully Updated Principal Contact';
          } else {
            $this->errorTO->description = 'Successfully Deleted Principal Contact';
          }
          $this->errorTO->identifier = $this->dbConn->dbGetLastInsertId();
          return $this->errorTO;
        }
    } else {

    }

    return $this->errorTO;
  }


  public function postUserTracking($userId, $principalId, $remoteAddress) {

    $this->dbConn->dbQuery("SET time_zone='+0:00'");

    $sql = "INSERT INTO user_tracking (user_uid,
						                           principal_uid,
						                           remote_address,
						                           login_date_time )
    				VALUES ("."'" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "',".
    				          "'" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "',".
      				        "'" . mysqli_real_escape_string($this->dbConn->connection, $remoteAddress) . "',". "NOW())";

    $this->errorTO = $this->dbConn->processPosting($sql, '');

    if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {

      return $this->errorTO;
    }

    return $this->errorTO;
  }


    public function postFTPServerResult($ftpServerUid, $status, $statusMsg) {

      $this->dbConn->dbQuery("SET time_zone='+0:00'");

      $sql="UPDATE ftp_server
      	  	SET
          		`last_run_status` 	= '" . mysqli_real_escape_string($this->dbConn->connection, $status)   . "',
          		`last_run_status_msg` 	= '" . mysqli_real_escape_string($this->dbConn->connection, substr($statusMsg,0,300))   . "',
          		`last_run_date` 	= NOW()
       	    WHERE uid = " . mysqli_real_escape_string($this->dbConn->connection, $ftpServerUid);

      $this->errorTO = $this->dbConn->processPosting($sql,"");

      if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
      	$this->errorTO->description = "FTP Server Successfully Updated.";
      	return $this->errorTO;
      } else {
        return $this->errorTO;
      }

    }


    public function postFTPLocationsCounters($locationUid, $counter){

      $this->dbConn->dbQuery("SET time_zone='+0:00'");

      $sql="UPDATE ftp_fetch_location
      	  	SET
          		`file_counter` 	= '" . mysqli_real_escape_string($this->dbConn->connection, $counter) . "',
          		`last_file_matched_date` 	= NOW()
       	    WHERE uid = " . mysqli_real_escape_string($this->dbConn->connection, $locationUid);

      $this->errorTO = $this->dbConn->processPosting($sql,"");

      if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
      	$this->errorTO->description = "FTP Server Successfully Updated.";
      	return $this->errorTO;
      } else {
        return $this->errorTO;
      }

    }


  public function postFTPFileLog($postingFTPFileLogTO) {

    $this->dbConn->dbQuery("SET time_zone='+0:00'");

    $sql = "INSERT INTO ftp_file_log
    				  (
						`server_path`,
						`server_filename`,
						`local_path`,
						`local_filename`,
						`filesize_bytes`,
						`ftp_fetch_location_uid`,
						`imported_datetime`
    				  )
    				  VALUES (".
                      "'" . mysqli_real_escape_string($this->dbConn->connection, $postingFTPFileLogTO->serverPath) . "',".
    				  "'" . mysqli_real_escape_string($this->dbConn->connection, $postingFTPFileLogTO->serverFilename) . "',".
    				  "'" . mysqli_real_escape_string($this->dbConn->connection, $postingFTPFileLogTO->localPath) . "',".
    				  "'" . mysqli_real_escape_string($this->dbConn->connection, $postingFTPFileLogTO->localFilename) . "',".
    				  "'" . mysqli_real_escape_string($this->dbConn->connection, $postingFTPFileLogTO->filesizeBytes) . "',".
      				  "'" . mysqli_real_escape_string($this->dbConn->connection, $postingFTPFileLogTO->ftpFetchLocationUid) . "',".
    				  "NOW())";

    $this->errorTO = $this->dbConn->processPosting($sql, '');

    if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
      return $this->errorTO;
    }

    return $this->errorTO;
  }

}
?>

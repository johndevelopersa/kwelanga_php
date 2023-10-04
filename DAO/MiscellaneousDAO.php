<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');


class MiscellaneousDAO {


  private $dbConn;


  function __construct($dbConn) {
       $this->dbConn = $dbConn;
  }


  public function getVenderLoginUID($username,$password,$vendorGln){
  	$sql="select uid
			  from vendor
			  where username = '".mysqli_real_escape_string($this->dbConn->connection, $username)."'
			  and password   = '".mysqli_real_escape_string($this->dbConn->connection, $password)."'
			  and vendor_gln = '".mysqli_real_escape_string($this->dbConn->connection, $vendorGln)."'";

    return $this->dbConn->dbGetAll($sql);

  }


	/*
	 *	pass no uid and it'll generate a list of vendors,
	 *	give it a id and it'll give you the info for that vendor if found.
	 */
  public function getVendersArray($uid = false){

      if($uid === false){

        $sql="select
                    name, uid, vendor_gln
                      from vendor";

      } else {

    $sql="select
            name, vendor_gln
              from vendor
                    where uid = '".mysqli_real_escape_string($this->dbConn->connection, $uid)."'";

      }
    return $this->dbConn->dbGetAll($sql);
  }


  public function getJobExecution($jobName, $jobId=false) {
		$this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA)

		$where = (($jobId===false)?"":" and job_id = '{$jobId}' ");

		// remember that the update of last_run updates EVERY time slot for that job, not just the current one.
		$sql="select script_name, 
		             page_params, 
		             group_concat(a.uid) 
		             uid_list,
		             a.principal_uid,
		             curdate(), 
		             a.uid as 'jeuid'
				  from   job_execution a
				  left join principal p on a.principal_uid = p.uid and p.status = '".FLAG_STATUS_ACTIVE."'
				  where  a.name='{$jobName}'
				  and   (
						      (
						  	    ( (last_run is null) or (last_run < curdate()) ) or
						  	      ( (last_run is not null) and (time_to_sec(last_run) < time_to_sec(time_to_run)) ) 
						  ) and
						  (time_to_sec(now())>time_to_sec(time_to_run))
						)
				and (p.uid is not null or a.principal_uid is null)
			  ".$where."
        group by script_name, page_params";
        
// echo "<br>"; 
// echo $sql;
// echo "<br>"; 
    return $this->dbConn->dbGetAll($sql);
  }

  public function getJobExecutionEntries($jobName, $jobId=false, $orderBy = false) {

		$where = (($jobId===false)?"":" and job_id = '{$jobId}' ");

		// remember that the update of last_run updates EVERY time slot for that job, not just the current one.
		$sql="select a.uid, 
		             script_name, 
		             page_params, 
		             group_concat(a.uid) 
		             uid_list,
		             a.principal_uid,
                 a.last_run,
		             curdate()
				  from   job_execution a
				  left join principal p on a.principal_uid = p.uid and p.status = '".FLAG_STATUS_ACTIVE."'
				  where  a.name='{$jobName}'
					and (p.uid is not null or a.principal_uid is null)
			  ".$where." ";
		
		$sql .= " group by script_name, page_params ";
			  
		if($orderBy){
				$sql .= " ORDER BY {$orderBy} ";
		}	

    return $this->dbConn->dbGetAll($sql);

  }

  public function getJobExecutionByName($jobName, $principalUId = false) {

    if ($principalUId!==false ) $where = " and principal_uid = {$principalUId}";
    else $where = "";

    $sql="select distinct script_name
            from   job_execution
            where  name = '{$jobName}'
            ".$where."
          order by script_name";

    return $this->dbConn->dbGetAll($sql);

  }

  public function setJobExecution($jobName, $uidList=false) {

		$this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA)

		$sql="update job_execution
 			  set last_run = now()
			  	where  name='{$jobName}'".
          (($uidList===false)?"":" and uid in ({$uidList})");

		$eTO=$this->dbConn->processPosting($sql,$jobName);

    	if ($eTO->type==FLAG_ERRORTO_SUCCESS) {
    		$eTO->description="Job Execution Successfully updated. ";
    	}

    	return $eTO;

  }


  // depot uid not used at present so pass empty string
  public function getContactTypes($principalId, $depotId) {
		$sql="select pc.uid, depot_uid, d.name depot_name, contact_type_uid, ct.name, ct.description, email_addr, mobile_number, ftp_addr
			  from   principal_contact pc
						left join depot d on pc.depot_uid = d.uid,
					 contact_type ct
			  where  pc.contact_type_uid = ct.uid
			  and    principal_uid = '{$principalId}'";

    return $this->dbConn->dbGetAll($sql);
  }


  public function getContactTypesArray() {
		$sql="select
				c.uid,
				c.name,
				c.description
			  from   contact_type c";

    return $this->dbConn->dbGetAll($sql);
  }


  // depot is not used for time being
  public function getContactItem($principalId, $depotId, $cUId) {
		$sql="select pc.uid, description, depot_uid, contact_type_uid, ct.name, ct.description, email_addr, mobile_number, ftp_addr
			  from   principal_contact pc,
					 contact_type ct
			  where  pc.contact_type_uid = ct.uid
			  and    principal_uid = '{$principalId}'
			  and    pc.uid = '{$cUId}'";

    return $this->dbConn->dbGetAll($sql);
  }


	/**
	 * @return vc.uid, vc.contact_type_uid, ct.name, ct.description, email_addr, mobile_number, ftp_addr
	 * */
  public function getVendorContactItem($vendorId, $cUId) {
		$sql="select vc.uid, vc.contact_type_uid, ct.name, ct.description, email_addr, mobile_number, ftp_addr
			  from   vendor v,
					 vendor_contact vc,
				     contact_type ct
			  where  v.uid = '{$vendorId}'
			  and    v.uid = vc.vendor_uid
			  and    vc.contact_type_uid = '{$cUId}'
			  and    vc.contact_type_uid = ct.uid";

    return $this->dbConn->dbGetAll($sql);
  }


  public function getCustomerTypes() {
		$sql="select uid, description, shortcode
			  from   customer_type";

    return $this->dbConn->dbGetAll($sql);
  }


  public function GetprincipalSpecialFields($principalId, $entityType) {
		$sql="SELECT uid, principal_uid, name, editable, required, value_min_length, value_max_length, value_validation, value_list, label_list
              FROM special_field_fields
              WHERE principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
			  AND   type = '".mysqli_real_escape_string($this->dbConn->connection, $entityType)."'
              ORDER BY if(`order` is null,999,`order`), name";

    return $this->dbConn->dbGetAll($sql);
	}


	public function getPrincipalSpecialFieldbyUid($uid) {
		$sql="SELECT uid, principal_uid, name, editable, required, value_min_length, value_max_length, value_validation, validation_depot_list,
                  value_list, label_list
              FROM special_field_fields
              WHERE uid = '".mysqli_real_escape_string($this->dbConn->connection, $uid)."'";

    return $this->dbConn->dbGetAll($sql);
  }


  // note: all fields are listed first, then values retrieved, not other way around to ensure that ALL fields are returned for purposes of
  // eg. storeForm.php capture.
  // NB : At the moment stores cant handle an array of values for same sf. PostTransactionDAO calls this routine for validation as well !

  public function getPrincipalSpecialFieldValues($principalId, $entityUId, $entityType, $arrayIndex=false, $orderBy="order") {
		$sql="SELECT a.uid 
		             smpd_uid, 
		             b.uid 
		             sf_uid, 
		             a.field_uid, 
		             a.value, 
		             a.entity_uid, 
		             b.principal_uid, 
		             b.name, 
		             b.editable, 
		             required,
                 value_min_length, 
                 value_max_length, 
                 value_validation, 
                 processing_order, 
                 b.value_list, 
                 b.label_list
           
          FROM special_field_fields b
          left join special_field_details a on a.field_uid = b.uid  and a.entity_uid = '".mysqli_real_escape_string($this->dbConn->connection, $entityUId)."'
          WHERE b.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
          AND   b.type = '".mysqli_real_escape_string($this->dbConn->connection, $entityType)."'
          ORDER BY if(`".$orderBy."` is null,999,`".$orderBy."`),name, a.uid";

		$this->dbConn->dbQuery($sql);
		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if (!$arrayIndex) $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
			}
		}
		return $arr;
  }
  // only one special field but all entities in list
  public function getPrincipalSpecialFieldValuesMultEntities($principalId, $fieldUId, $entityUIdList, $entityType, $arrayIndex=false, $orderBy="order") {
       
       $sql="SELECT a.uid smpd_uid, 
                    b.uid sf_uid, 
                    a.field_uid, 
                    a.value, 
                    a.entity_uid, 
                    b.principal_uid, 
                    b.name, 
                    b.editable, 
                    required,
                    value_min_length, 
                    value_max_length, 
                    value_validation, 
                    processing_order, 
                    b.value_list, 
                    b.label_list
              FROM special_field_fields b
              LEFT JOIN special_field_details a on a.field_uid = b.uid  and a.entity_uid in ({$entityUIdList})
              WHERE b.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
              AND   b.type          = '".mysqli_real_escape_string($this->dbConn->connection, $entityType)."'
              AND   b.uid           = '".mysqli_real_escape_string($this->dbConn->connection, $fieldUId)."'
              ORDER BY if(`".$orderBy."` is null,999,`".$orderBy."`),name, a.uid";
file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/sql.txt', $sql, FILE_APPEND);

    $this->dbConn->dbQuery($sql);
    $arr=array();
    if ($this->dbConn->dbQueryResultRows > 0) {
      while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
        if (!$arrayIndex) $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
      }
    }
    return $arr;
  }
//******************************************************************************************************************************************

  public function getSpecialFieldValues($fldId, $entityUId) {
		
		$sql = "SELECT sfd.uid, 
		               sfd.value, 
		               sfd.entity_uid
           
           FROM special_field_details sfd 
           WHERE sfd.field_uid  = "  . mysqli_real_escape_string($this->dbConn->connection, $fldId)       . "
           AND   sfd.entity_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $entityUId)   . "';";
file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/sql.txt', $sql, FILE_APPEND);

           return $this->dbConn->dbGetAll($sql);
  }
  
//*********************************************************************************************************************  

	public function getActiveFTPServers($processID) {

      $sql="SELECT `uid`,
                   `host`,
                   `username`,
                   `password`,
                   `port`,
                   `passive_mode`,
                   `active`,
                   `direction`,
                   `encryption`
            FROM ftp_server
            WHERE active  = '" . FLAG_STATUS_ACTIVE . "'
            and process_uid = " . mysqli_real_escape_string($this->dbConn->connection, $processID) . "";

      return $this->dbConn->dbGetAll($sql);
  }


  public function getActiveFTPLocations($ftpServerUid) {

		$sql="SELECT
				`uid`, `server_file_backup_flag`,
				`server_file_path`, `file_wildcard`, `file_end_delimiter`,
				`root_dir_constant`, `local_file_path`, `prepend_local_filename`,
				`ftp_type`, `last_file_matched_date`, `file_counter`, zip_filename, create_zip_flag
			  FROM ftp_fetch_location
			  WHERE active  = '".FLAG_STATUS_ACTIVE."'
			  	AND ftp_server_uid = ".mysqli_real_escape_string($this->dbConn->connection, $ftpServerUid)."";

    return $this->dbConn->dbGetAll($sql);
  }

  public function getPresentation($to) {

          $arr = $this->dbConn->dbGetAll("SELECT * FROM `presentation_control` p
                      WHERE   p.presentation_type = '". mysqli_real_escape_string($this->dbConn->connection, $to->type) ."'
                        AND   system_uid = '". mysqli_real_escape_string($this->dbConn->connection, $to->systemUId) ."'
                        AND   (principal_uid = '". mysqli_real_escape_string($this->dbConn->connection, $to->principalUid) ."' or principal_uid is null)
                        AND   ((FIND_IN_SET('". mysqli_real_escape_string($this->dbConn->connection, $to->depotUId) ."',depot_uid)>0) or depot_uid is null)
                        AND   ((FIND_IN_SET('". mysqli_real_escape_string($this->dbConn->connection, $to->documentTypeUId) ."',document_type_uid)>0) or document_type_uid is null)
                        AND   ((FIND_IN_SET('". mysqli_real_escape_string($this->dbConn->connection, $to->documentStatusUId) ."',document_status_uid)>0) or document_status_uid is null)
                        AND   (user_category = '". mysqli_real_escape_string($this->dbConn->connection, $to->userCategory) ."' or user_category is null )
                        AND   (platform = '". mysqli_real_escape_string($this->dbConn->connection, $to->platform) ."' or platform is null )
                        ORDER  BY
                          if(principal_uid is not null,1,2),
                          if(depot_uid is not null,1,2),
                          if(document_type_uid is not null,1,2),
                          if(document_status_uid is not null,1,2)");
                          
           return (isset($arr[0])?$arr[0]:array());  //return first row only!
  }
  public function getDocumentInfo($docno, $type) {
  	
  	   if ($type=='C') {
  	   	   $typeStr = "dm.order_sequence_no = " . mysqli_real_escape_string($this->dbConn->connection, $docno) ;  	   	
  	   } else {
  	   	   $typeStr = "dm.uid = ". mysqli_real_escape_string($this->dbConn->connection, $docno);
  	   }
   	   
        return $this->dbConn->dbGetAll("SELECT dm.uid AS 'document_master_uid', dm.document_type_uid, dh.document_status_uid, dt.description, dm.principal_uid, dm.depot_uid 
                                        FROM document_master dm, document_header dh, document_type dt 
                                        WHERE dm.uid = dh.document_master_uid
                                        AND   dt.uid = dm.document_type_uid
                                        AND   " . $typeStr . ";");
  }

  public function getDocumentAuditTrail($dMUId) {

    return $this->dbConn->dbGetAll("select aoh.*, u.full_name, oh.user_action_status
                              from   document_master dm,
                                		 orders_holding oh
                                		 	left join ".DATABASE_AUDITOR.".orders_holding aoh on oh.uid = aoh.uid
                                		 	left join users u on aoh.change_by_userid = u.uid
                              where  dm.uid = {$dMUId}
                              and    oh.order_sequence_number = dm.order_sequence_no
                              order  by aoh.change_date");

  }

  public function getStockUserWarehouse($principalId, $userId) {

    return $this->dbConn->dbGetAll("select d.uid, d.name
                                    from .user_principal_depot upd, .depot d
                                    where upd.depot_id = d.uid
                                    and   upd.principal_id = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
                                    and   upd.user_id      = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'");

  }
// ****************************************************************************************************************************************************
  public function getPrincipalSpecialFieldValuesByFid($entityUId, $fldUid) {
		$sql="SELECT sff.entity_uid, 
                 sff.field_uid,
                 sff.value
          FROM .special_field_details sff
          WHERE sff.field_uid = '".mysqli_real_escape_string($this->dbConn->connection, $fldUid)."'
          AND   sff.entity_uid = '".mysqli_real_escape_string($this->dbConn->connection, $entityUId)."'";

		return $this->dbConn->dbGetAll($sql);
  }
// ****************************************************************************************************************************************************

}
?>

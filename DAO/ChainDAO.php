<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class ChainDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

	public function getPrincipalChainByOldCode($principalId, $oldCode) {
		$sql="select a.uid
				from   principal_chain_master a
				where  a.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
				and    a.old_code = '".mysqli_real_escape_string($this->dbConn->connection, $oldCode)."'";

                return $this->dbConn->dbGetAll($sql);
	}

	public function getPrincipalsChainsArray() {
		$sql="select uid, principal_uid, description
				from   principal_chain_master";

                return $this->dbConn->dbGetAll($sql);
	}

	public function getPrincipalChainsArray($principalId) {
		$sql="select uid, principal_uid, description, scanned_document_prefix
				from   principal_chain_master
				where principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'";

                return $this->dbConn->dbGetAll($sql);
	}


	// this function is duplicated in StoreDAO... in time the store one needs to be removed.
	public function getPrincipalChainItem($chainUId) {
		$sql="select a.uid, a.description chain_name, principal_uid, status, a.captured_by
				from   principal_chain_master a
				where  uid = '".mysqli_real_escape_string($this->dbConn->connection, $chainUId)."'
				and    a.status = '".FLAG_STATUS_ACTIVE."'";

                return $this->dbConn->dbGetAll($sql);
	}

	// chain special fields
	public function getPrincipalChainFields($principalId) {
		$sql="SELECT uid, principal_uid, name
              FROM principal_chain_master_special_fields
              WHERE principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'";

                return $this->dbConn->dbGetAll($sql);
	}

	// get store uid by special field, *** could be multiple returned ***
	// this uses only the smpd UID and not label for field due to improved performance
	/**
	 * @return array(uid)
	 */
	public function getPrincipalChainBySF($principalId, $specialFieldId, $specialFieldValue) {
		// not necessary to join on type=C because fieldvalueId should only be supplied for correct entityType, trying to keep joins to minimum

		$sql="select   pcm.uid
				from   principal_chain_master pcm,
					   special_field_details sfd
				where  pcm.principal_uid = '{$principalId}'
				and    pcm.uid = sfd.entity_uid
				and    sfd.field_uid = '{$specialFieldId}'
				and    sfd.value = '{$specialFieldValue}'";

                return $this->dbConn->dbGetAll($sql);
	}

	// get store uid by special field, *** could be multiple returned ***
	/**
	 * @return array(uid)
	 */
	public function getPrincipalChainBySFName($principalId, $specialFieldName, $specialFieldValue) {
		$sql="select   pcm.uid
				from   principal_chain_master pcm,
					   special_field_fields sff,
					   special_field_details sfd
				where  pcm.principal_uid = '{$principalId}'
				and    pcm.principal_uid = sff.entity_uid
				and    psm.uid = sfd.entity_uid
				and    sff.type = 'C'
				and    sff.name = '{$specialFieldName}'
				and    sff.uid = sfd.field_uid
				and    sfd.value = '{$specialFieldValue}'";

                return $this->dbConn->dbGetAll($sql);
	}

}
?>

<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/ValidationCommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostChainDAO {
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

    public function postPrincipalChainValidation($postingPrincipalChainTO) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
    	$administrationDAO = new AdministrationDAO($this->dbConn);
    	$principalDAO = new PrincipalDAO($this->dbConn);
    	$storeDAO = new StoreDAO($this->dbConn);

    	// get user. don't pass it because this is more secure.
    	if (!isset($_SESSION)) session_start();
    	$userId = $_SESSION['user_id'];

    	if (!ValidationCommonUtils::checkPostingType($postingPrincipalChainTO->DMLType)) return false;

		// check if principal is valid
		$mfP = $principalDAO->getUserPrincipalArray($userId,"principal_id");
		if(!isset($mfP[$postingPrincipalChainTO->principalId])) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="User does not have access to this principal, or principal not found.";
			return false;
		};

		// check if STATUS is valid
		if(!ValidationCommonUtils::checkStatus($postingPrincipalChainTO->status)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Status.";
			return false;
		};

		if ($postingPrincipalChainTO->DMLType=="INSERT") {
			// check roles
			$hasRole = $administrationDAO->hasRole($userId,$postingPrincipalChainTO->principalId,ROLE_ADD_CHAIN);
			if(!$hasRole) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have permissions to Add Chains";
				return false;
			};

			// check chain name is unique. replace the \" with " to get SQL to work
			$name=trim($postingPrincipalChainTO->chainName);
			$after=preg_replace('/\s+/','',$name);
			$after=preg_replace('/[^a-zA-Z0-9]/', '', $after);
			$after=strtolower($after);
			$sql="
				select description
				from principal_chain_master
				where principal_uid = ".$postingPrincipalChainTO->principalId."
				and		 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(
						 replace(lower(description),\"'\",\"\"),
						 	'\"',''),
							 ' ',''),
							 '.',''),
							 '-',''),
							 '&',''),
							 '_',''),
							 '*',''),
							 '!',''),
							 '@',''),
							 '#',''),
							 '$',''),
							 '%',''),
							 '^',''),
							 '(',''),
							 ')',''),
							 '/',''),
							 '\\\',''),
							 ',',''),
							 '[',''),
							 ']',''),
							 '{',''),
							 '}',''),
							 '?',''),
							 ';',''),
							 ':',''),
							 '<',''),
							 '>','') = '".$after."'";

		    $this->dbConn->dbinsQuery($sql);
			if ($this->dbConn->dbQueryResultRows > 0) {
				$list="";
				while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
					$list.=$row['description']."<BR>";
				}
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="A Chain Name already exists that is too similar to the new chain.<BR><BR>".$list;
				return false;
			}
		} else if ($postingPrincipalChainTO->DMLType=="UPDATE") {
			// check roles
			$hasRole = $administrationDAO->hasRole($userId,$postingPrincipalChainTO->principalId,ROLE_MODIFY_CHAIN);
			if(!$hasRole) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have permissions to Modify Chain Details";
				return false;
			};

			// check if chain exists
			$mfPC = $storeDAO->getPrincipalChainItem($postingPrincipalChainTO->principalChainUId);
			if(sizeof($mfPC)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="The Principal Chain could not be found for editing.";
				return false;
			};
			// check principal is same
			if($mfPC[0]['principal_uid']!=$postingPrincipalChainTO->principalId) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="The Principal Chain principal differs from the principal passed.";
				return false;
			};
		  }

		return true;

    }

    public function postPrincipalChain($postingPrincipalChainTO) {
    	if (!isset($_SESSION)) session_start;
    	$userId = $_SESSION['user_id'];

    	$resultOK = $this->postPrincipalChainValidation($postingPrincipalChainTO);
    	if ($resultOK) {
    		 if ($postingPrincipalChainTO->DMLType=="INSERT") {
    			$sql="INSERT INTO principal_chain_master
    				  (
						principal_uid,
						description,
						status,
    			  old_code,
						captured_by,
						last_change_by_userid
    				  )
    				  VALUES (" .
    				  	mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalChainTO->principalId) . ",".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalChainTO->chainName) . "',".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalChainTO->status) . "',".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalChainTO->oldCode) . "',".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "',
						 '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "'".
    				  ")";
    		  } else if ($postingPrincipalChainTO->DMLType=="UPDATE") {
	    			$sql="UPDATE principal_chain_master
	    				  SET description='" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalChainTO->chainName) . "',
    							  status='" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalChainTO->status) . "',
    							  old_code='" . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalChainTO->oldCode) . "',
    							  last_change_by_userid='" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "'
						  WHERE uid = " . mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalChainTO->principalChainUId);
	    		  }

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingPrincipalChainTO->chainName);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
			  	if ($postingPrincipalChainTO->DMLType=="INSERT") {
			  		$this->errorTO->description="Chain Successfully Added to Principal Chain Master.";
			  		$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId(); // get the UID just created
			  	}
			  	else if ($postingPrincipalChainTO->DMLType=="UPDATE")	$this->errorTO->description="Principal-Chain Successfully Updated.";
			  	return $this->errorTO;
			  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
    }

    /*
     *
     * GLOBAL CHAIN
     *
     */
    public function postGlobalChainValidation($postingGlobalChainTO) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
    	$administrationDAO = new AdministrationDAO($this->dbConn);
    	$principalDAO = new PrincipalDAO($this->dbConn);
    	$storeDAO = new StoreDAO($this->dbConn);

    	// get user. don't pass it because this is more secure.
    	if (!isset($_SESSION)) session_start();
    	$userId = $_SESSION['user_id'];
    	$principalId = $_SESSION['principal_id']; // used for hasRole validation

    	if (!ValidationCommonUtils::checkPostingType($postingGlobalChainTO->DMLType)) return false;

		// check if STATUS is valid
		if(!ValidationCommonUtils::checkStatus($postingGlobalChainTO->status)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Status.";
			return false;
		};

		// check roles
		$hasRole = $administrationDAO->hasRole($userId,$principalId,ROLE_ADD_CHAIN);
		if(!$hasRole) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="You do not have permissions to Add Chains";
			return false;
		};

		// check chain name is unique. replace the \" with " to get SQL to work
		$name=trim($postingGlobalChainTO->chainName);
		$after=preg_replace('/\s+/','',$name);
		$after=preg_replace('/[^a-zA-Z]/', '', $after);
		$after=strtolower($after);
		$sql="
			select description
			from global_chain_master
			where 	 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(
					 replace(lower(description),\"'\",\"\"),
					 	'\"',''),
						 ' ',''),
						 '.',''),
						 '-',''),
						 '&',''),
						 '_',''),
						 '*',''),
						 '!',''),
						 '@',''),
						 '#',''),
						 '$',''),
						 '%',''),
						 '^',''),
						 '(',''),
						 ')',''),
						 '/',''),
						 '\\\',''),
						 ',',''),
						 '[',''),
						 ']',''),
						 '{',''),
						 '}',''),
						 '?',''),
						 ';',''),
						 ':',''),
						 '<',''),
						 '>','') = '".$after."'";

	    $this->dbConn->dbinsQuery($sql);
		if ($this->dbConn->dbQueryResultRows > 0) {
			$list="";
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$list.=$row['description']."<BR>";
			}
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="A Global Chain Name already exists that is too similar to the new chain.<BR><BR>".$list;
			return false;
		}

		return true;

    }

    public function postGlobalChain($postingGlobalChainTO) {
    	$resultOK = $this->postGlobalChainValidation($postingGlobalChainTO);
    	if ($resultOK) {
    		 if ($postingGlobalChainTO->DMLType=="INSERT") {
    			$sql="INSERT INTO global_chain_master
    				  (
						description,
						status
    				  )
    				  VALUES (".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingGlobalChainTO->chainName) . "',".
    				  	"'" . mysqli_real_escape_string($this->dbConn->connection, $postingGlobalChainTO->status) . "'".
    				  ")";
    		  }

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingGlobalChainTO->chainName);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
			  	if ($postingGlobalChainTO->DMLType=="INSERT")	$this->errorTO->description="Chain Successfully Added to Global Chain Master.";
			  	return $this->errorTO;
			  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
    }


}
?>

<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/ValidationCommonUtils.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostAdminUserDAO {
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

    public function postRolesValidation($postingUserRolesTO, $userId) {
    	if (!ValidationCommonUtils::checkPostingType($postingUserRolesTO->DMLType)) return false;

		if((!preg_match(GUI_PHP_INTEGER_REGEX,$postingUserRolesTO->userId)) || (!preg_match(GUI_PHP_INTEGER_REGEX,$postingUserRolesTO->roleId))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid User or Role ID type";
			return false;
		};

		// more secure than passing
    	if (!isset($_SESSION)) session_start;
		//$userId = $_SESSION['user_id'];
		$principalId = $_SESSION['principal_id'];
		$staff_user = (isset($_SESSION['staff_user'])) ? $_SESSION['staff_user'] : false;
    $admin_user = (isset($_SESSION['admin_user'])) ? $_SESSION['admin_user'] : false;


		// cannot add roles to self
		if ($userId == $postingUserRolesTO->userId && ($staff_user != 'Y' && $admin_user != 'Y')) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="You cannot modify roles for yourself.";
			return false;
		};

		$administrationDAO = new AdministrationDAO($this->dbConn);
		$principalDAO = new PrincipalDAO($this->dbConn);

		$mfRecipientUser = $administrationDAO->getUserItem($postingUserRolesTO->userId);
		$mfRole = $administrationDAO->getRoleItem($postingUserRolesTO->roleId);
		$hasRole = $administrationDAO->hasRole($postingUserRolesTO->userId,$postingUserRolesTO->principalId,$postingUserRolesTO->roleId);
		$mfUserPrincipals = $principalDAO->getUserPrincipalArray($postingUserRolesTO->userId, "");

		$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;

		$hasAdminRoleReceiver=$administrationDAO->userIsAdministrator($postingUserRolesTO->userId);

		// cannot add roles to user if you yourself don't have the role.
		if (!$adminUser && !CommonUtils::isDepotUser()) {
			$hasRoleSelf = $administrationDAO->hasRole($userId,$postingUserRolesTO->principalId,$postingUserRolesTO->roleId);
			if (!$hasRoleSelf) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You cannot add roles to a user if you do not have the role yourself.";
				return false;
			};
		}

		if (sizeof($mfRole)==0) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Role Passed. Role does not exist.";
			return false;
		};

		// can only add roles restricted to Admin to an Admin user
		if ((!$hasAdminRoleReceiver) && ($mfRole[0]['restricted_to']==FLAG_ROLE_RESTRICTEDTO_ADMIN)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Person receiving this role must be an Administrator.";
			return false;
		};

		if ($postingUserRolesTO->DMLType=="DELETE") {
			if (!$hasRole) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="The user does not have the role you requested for deletion.";
				return false;
			};
			// can only remove role if has Remove Role role
			if (!$adminUser) {
				$hasRole=$administrationDAO->hasRole($userId,$postingUserRolesTO->principalId,ROLE_REMOVE_ROLE_FROM_USER);
				if (!$hasRole) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="You must have the Remove Role role allocated to remove roles.";
					return false;
				};
			}
		}

		if (!isset($mfUserPrincipals[$postingUserRolesTO->principalId])) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="This user receiving the roles does not have access to this principal";
			return false;
		};

		if ($postingUserRolesTO->DMLType=="INSERT") {
			// must have the SU role to create SU
			if (!$adminUser) {
				if ($postingUserRolesTO->roleId==ROLE_SUPERUSER) {
					$hasRoleSU=$administrationDAO->hasRole($userId,$postingUserRolesTO->principalId,ROLE_SUPERUSER);
					if (!$hasRoleSU) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="You must have the SU role allocated to assign this role.";
						return false;
					};
				}
			}
			if ($hasRole) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="This user already has the role you requested for addition.";
				return false;
			};
			// can only add role if has Add Role role
			if (!$adminUser) {
				$hasRole=$administrationDAO->hasRole($userId,$postingUserRolesTO->principalId,ROLE_ADD_ROLE_TO_USER);
				if (!$hasRole) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="You must have the Add Role role allocated to assign roles.";
					return false;
				};
			}

		}

		// check user that we are modifying is within priviledges
		if (!$adminUser) {
			$mfUsers=$administrationDAO->getUsersByPrincipalDepotArray($userId);
			if (!isset($mfUsers[$postingUserRolesTO->userId])) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have priviledges to modify this user";
				return false;
			};
		}

		return true;

    }

    public function postUserRole($postingUserRolesTO, $userId) {
    	$resultOK = $this->postRolesValidation($postingUserRolesTO, $userId);
    	if ($resultOK) {
    		if ($postingUserRolesTO->DMLType=="DELETE") {
	    		$sql="delete from user_role
	    			  where  user_id=".$postingUserRolesTO->userId."
	    			  and    role_id=".$postingUserRolesTO->roleId."
	    			  and    entity_uid=".$postingUserRolesTO->principalId;
    		} else if ($postingUserRolesTO->DMLType=="INSERT") {
    			$sql="INSERT INTO user_role
    				  (
						user_id,
						role_id,
						entity_uid
    				  )
    				  VALUES (".
    				  	$postingUserRolesTO->userId.",".
    				  	$postingUserRolesTO->roleId.",".
    				  	$postingUserRolesTO->principalId.
    				  ")";
  		  }

  		  $this->errorTO = $this->dbConn->processPosting($sql,$postingUserRolesTO->roleId);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {

			  	if ($postingUserRolesTO->DMLType=="INSERT")	$this->errorTO->description="Roles Successfully Added";
			  	else if ($postingUserRolesTO->DMLType=="DELETE")	$this->errorTO->description="Roles Successfully Removed";
			  	return $this->errorTO;
			  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
    }

    /*
     *
     *  User Principal Depot
     *
     */
    public function postPrincipalDepotValidation($postingUserPrincipalDepotTO, $userId) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/DepotDAO.php");

    	if (!ValidationCommonUtils::checkPostingType($postingUserPrincipalDepotTO->DMLType)) return false;

		if((!preg_match(GUI_PHP_INTEGER_REGEX,$postingUserPrincipalDepotTO->userId)) || (!preg_match(GUI_PHP_INTEGER_REGEX,$postingUserPrincipalDepotTO->principalId))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid User or Principal ID type";
			return false;
		};

		// more secure than passing
    	if (!isset($_SESSION)) session_start;
		//$userId = $_SESSION['user_id'];
		$principalId = $_SESSION['principal_id'];

		$administrationDAO = new AdministrationDAO($this->dbConn);
		$hasSURole=$administrationDAO->hasRoleSuperUser($userId, $principalId);
		$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
		$principalDAO = new PrincipalDAO($this->dbConn);
		if ($adminUser) {
			$depotDAO = new DepotDAO($this->dbConn);
			$mfDepot=$depotDAO->getDepotItem($postingUserPrincipalDepotTO->depotId);
			$mfPrincipal=$principalDAO->getPrincipalItem($postingUserPrincipalDepotTO->principalId);
		} else {
			$mfSUUPD = $principalDAO->getUserPrincipalDepotItem($userId,$postingUserPrincipalDepotTO->principalId,$postingUserPrincipalDepotTO->depotId);
		  }
		$mfUPD = $principalDAO->getUserPrincipalDepotItem($postingUserPrincipalDepotTO->userId,$postingUserPrincipalDepotTO->principalId,$postingUserPrincipalDepotTO->depotId);

		if ($adminUser) {
			if (sizeof($mfDepot)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Depot supplied";
				return false;
			}
			if (sizeof($mfPrincipal)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Principal supplied";
				return false;
			}
		} else {
			if (sizeof($mfSUUPD)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="User cannot add principals to users which they do not have privileges for";
				return false;
			}
		}

		if ($postingUserPrincipalDepotTO->DMLType=="DELETE") {
			if (sizeof($mfUPD)!=1) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="No rows found for deletion";
				return false;
			};

			if (!$adminUser) {
				$hasRole = $administrationDAO->hasRole($userId,$postingUserPrincipalDepotTO->principalId,ROLE_REMOVE_PRINCIPAL_FROM_USER);
				// the user must have the remove Principal Role to remove privileges
				if (!$hasRole) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="This user does not have the Remove Principal Role (for general user / Super User) to remove privileges";
					return false;
				};
			}
		}

		if ($postingUserPrincipalDepotTO->DMLType=="INSERT") {
			if (sizeof($mfUPD)>0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="This user already has the principal you requested for addition.";
				return false;
			};

			// skip check if administrator as administrators can give principals from global list and won't need to be registered themselves for that principal
			if (!$adminUser) {
				$hasRole = $administrationDAO->hasRole($userId,$postingUserPrincipalDepotTO->principalId,ROLE_ADD_PRINCIPAL_TO_USER);
				// the user must have the add Principal Role to assign privileges
				if (!$hasRole) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="This user does not have the Add Principal Role (for general user / Super User) to assign privileges";
					return false;
				};
			}
		}

		return true;

    }

    public function postUserPrincipalDepot($postingUserPrincipalDepotTO, $userId) {
    	$resultOK = $this->postPrincipalDepotValidation($postingUserPrincipalDepotTO, $userId);
    	if ($resultOK) {

    		if ($postingUserPrincipalDepotTO->DMLType=="DELETE") {
	    		$sql="delete from user_principal_depot
	    			  where  user_id=".$postingUserPrincipalDepotTO->userId."
	    			  and    principal_id=".$postingUserPrincipalDepotTO->principalId."
	    			  and    depot_id=".$postingUserPrincipalDepotTO->depotId;
    		} else if ($postingUserPrincipalDepotTO->DMLType=="INSERT") {
    			$sql="INSERT INTO user_principal_depot
    				  (
						user_id,
						principal_id,
						depot_id
    				  )
    				  VALUES (".
    				  	$postingUserPrincipalDepotTO->userId.",".
    				  	$postingUserPrincipalDepotTO->principalId.",".
    				  	$postingUserPrincipalDepotTO->depotId.
    				  ")";
    		  }

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingUserPrincipalDepotTO->principalId);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
			  	if ($postingUserPrincipalDepotTO->DMLType=="INSERT") $this->errorTO->description="Principal-Depot Successfully Added";
			  	else if ($postingUserPrincipalDepotTO->DMLType=="DELETE") $this->errorTO->description="Principal-Depot Successfully Removed";
			  	return $this->errorTO;
			  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
    }


    /*
     *
     *  User Principal Store
     *
     */
    public function postPrincipalStoreValidation($postingUserPrincipalStoreTO, $userId) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");

    	// more secure than passing
    	if (!isset($_SESSION)) session_start;
		//$userId = $_SESSION['user_id'];
		$principalId = $_SESSION['principal_id'];

    	if (!ValidationCommonUtils::checkPostingType($postingUserPrincipalStoreTO->DMLType)) return false;

		if((!preg_match(GUI_PHP_INTEGER_REGEX,$postingUserPrincipalStoreTO->userId)) || (!preg_match(GUI_PHP_INTEGER_REGEX,$postingUserPrincipalStoreTO->principalStoreUId))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid User or Principal ID type";
			return false;
		};

		$administrationDAO = new AdministrationDAO($this->dbConn);
		$hasSURole=$administrationDAO->hasRoleSuperUser($userId, $principalId);
		$isAdminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
		$principalDAO = new PrincipalDAO($this->dbConn);
		$storeDAO = new StoreDAO($this->dbConn);

		$mfUP = $principalDAO->getUserPrincipalArray($postingUserPrincipalStoreTO->userId,"principal_id"); // check recipient user has acess to add to principal
		$mfStore=$storeDAO->getPrincipalStoreItem($postingUserPrincipalStoreTO->principalStoreUId);
		$mfUPD = $principalDAO->getUserPrincipalDepotItem($postingUserPrincipalStoreTO->userId,$mfStore[0]['principal_uid'],$mfStore[0]['depot_uid']); // check store P-D is within priviledges
		$mfUPC = $storeDAO->getUserPrincipalChainItem($postingUserPrincipalStoreTO->userId,$mfStore[0]['principal_chain_uid']); // validate chain

		// check recipient has access to the principal first
		if (!isset($mfUP[$postingUserPrincipalStoreTO->principalId])) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Recipient User does not have access to this principal";
			return false;
		}

		// check recipient has access to the principal-depot for store
		if (sizeof($mfUPD)==0) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="This store is registered to a Principal-Depot for which the Recipient User does not have priviledges for.";
			return false;
		}

		// check recipient has access to the principal-chain for store
		if (sizeof($mfUPC)==0) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="This store is registered to a Principal-Chain for which the Recipient User does not have priviledges for.";
			return false;
		}

		if (!$isAdminUser) {
			$mfSUPS = $storeDAO->getUserPrincipalStoreItem($userId,$postingUserPrincipalStoreTO->principalStoreUId);
			$mfPS = $storeDAO->getPrincipalStoreItem($postingUserPrincipalStoreTO->principalStoreUId);
	    }
		$mfUPS = $storeDAO->getUserPrincipalStoreItem($postingUserPrincipalStoreTO->userId,$postingUserPrincipalStoreTO->principalStoreUId);

		// invalid store
		if (sizeof($mfStore)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Principal-Store supplied";
				return false;
		}

		$skipRMSUcheck=false; // adding store to self because this user created it
		if ((!$isAdminUser) && (!$hasSURole)) {
			// skip check if user created the store
			if ($mfPS[0]['captured_by']!=$userId) {
				if (sizeof($mfSUPS)==0) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="User cannot add principal-stores to users which they themselves do not have privileges for";
					return false;
				}
			} else $skipRMSUcheck=true;
		}

		// only check if has role maintain store users if not adding for self
		if (!$skipRMSUcheck) {
			$hasRole=$administrationDAO->hasRole($userId,$_SESSION['principal_id'],ROLE_MAINTAIN_STORE_USERS);
			if (!$hasRole) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have priviledges to add/remove stores for this user";
				return false;
			};
		}

		if ($postingUserPrincipalStoreTO->DMLType=="DELETE") {
			if (sizeof($mfUPS)!=1) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="No rows found for deletion";
				return false;
			};
		}

		if ($postingUserPrincipalStoreTO->DMLType=="INSERT") {
			if (sizeof($mfUPS)>0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="This user already has the principal-store you requested for addition, or the store bypass role is loaded.";
				return false;
			};
		}

		// check user that we are modifying is within priviledges
		if (!$isAdminUser) {
			$mfUsers=$administrationDAO->getUsersByPrincipalDepotArray($userId);
			if (!isset($mfUsers[$postingUserPrincipalStoreTO->userId])) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have priviledges to modify this user";
				return false;
			};
		}

		return true;

    }

    public function postUserPrincipalStore($postingUserPrincipalStoreTO, $userId) {
    	$resultOK = $this->postPrincipalStoreValidation($postingUserPrincipalStoreTO, $userId);
    	if ($resultOK) {

    		if ($postingUserPrincipalStoreTO->DMLType=="DELETE") {
	    		$sql="delete from user_principal_store
	    			  where  user_uid=".$postingUserPrincipalStoreTO->userId."
	    			  and    principal_store_uid=".$postingUserPrincipalStoreTO->principalStoreUId;
    		} else if ($postingUserPrincipalStoreTO->DMLType=="INSERT") {
    			$sql="INSERT INTO user_principal_store
    				  (
						user_uid,
						principal_store_uid
    				  )
    				  VALUES (".
    				  	$postingUserPrincipalStoreTO->userId.",".
    				  	$postingUserPrincipalStoreTO->principalStoreUId.
    				  ")";
    		  }

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingUserPrincipalStoreTO->principalStoreUId);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
				if ($postingUserPrincipalStoreTO->DMLType=="INSERT") $this->errorTO->description="Principal-Store Successfully Added to User";
				if ($postingUserPrincipalStoreTO->DMLType=="DELETE") $this->errorTO->description="Principal-Store Successfully Removed from User";
			  	return $this->errorTO;
			  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
    }

    /*
     *
     *  User Chain
     *
     */
    public function postChainValidation($postingUserChainTO, $userId) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");

    	// more secure than passing
    	if (!isset($_SESSION)) session_start;
		$userId = $_SESSION['user_id'];
		$principalId = $_SESSION['principal_id'];

    	if (!ValidationCommonUtils::checkPostingType($postingUserChainTO->DMLType)) return false;

		if((!preg_match(GUI_PHP_INTEGER_REGEX,$postingUserChainTO->userId)) || (!preg_match(GUI_PHP_INTEGER_REGEX,$postingUserChainTO->principalChainUId))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid User or Chain ID type";
			return false;
		};

		$administrationDAO = new AdministrationDAO($this->dbConn);
		$isAdminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
		$storeDAO = new StoreDAO($this->dbConn);

		$mfChain=$storeDAO->getPrincipalChainItem($postingUserChainTO->principalChainUId);

		if (!$isAdminUser) {
			$mfSUPC = $storeDAO->getUserPrincipalChainItem($userId,$postingUserChainTO->principalChainUId);
			$mfPC = $storeDAO->getPrincipalChainItem($postingUserChainTO->principalChainUId);
	    }
		$mfUPC = $storeDAO->getUserPrincipalChainItem($postingUserChainTO->userId,$postingUserChainTO->principalChainUId);

		// invalid chain
		if (sizeof($mfChain)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Chain supplied";
				return false;
		}

		if (!$isAdminUser) {
			// skip check if user created the chain
			if ($mfPC[0]['captured_by']!=$userId) {
				if (sizeof($mfSUPC)==0) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="User cannot add chain(s) to users which they themselves do not have privileges for";
					return false;
				}
			}
		}

		$hasRole=$administrationDAO->hasRole($userId,$_SESSION['principal_id'],ROLE_MAINTAIN_CHAIN_USERS);
		if (!$hasRole) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="You do not have priviledges to add/remove chain(s) for this user";
			return false;
		};

		if ($postingUserChainTO->DMLType=="DELETE") {
			if (sizeof($mfUPC)!=1) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="No rows found for deletion";
				return false;
			};
		}

		if ($postingUserChainTO->DMLType=="INSERT") {
			if (sizeof($mfUPC)>0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="This user already has the chain you requested for addition.";
				return false;
			};
		}

		// check user that we are modifying is within priviledges
		if (!$isAdminUser) {
			$mfUsers=$administrationDAO->getUsersByPrincipalDepotArray($userId);
			if (!isset($mfUsers[$postingUserChainTO->userId])) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have priviledges to modify this user";
				return false;
			};
		}

		return true;

    }

    public function postUserChain($postingUserChainTO, $userId) {
    	$resultOK = $this->postChainValidation($postingUserChainTO, $userId);
    	if ($resultOK) {

    		if ($postingUserChainTO->DMLType=="DELETE") {
	    		$sql="delete from user_principal_chain
	    			  where  user_uid=".$postingUserChainTO->userId."
	    			  and    principal_chain_uid=".$postingUserChainTO->principalChainUId;
    		} else if ($postingUserChainTO->DMLType=="INSERT") {
    			$sql="INSERT INTO user_principal_chain
    				  (
						user_uid,
						principal_chain_uid
    				  )
    				  VALUES (".
    				  	$postingUserChainTO->userId.",".
    				  	$postingUserChainTO->principalChainUId.
    				  ")";
    		  }

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingUserChainTO->principalChainUId);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
				if ($postingUserChainTO->DMLType=="INSERT") $this->errorTO->description="Principal-Chain Successfully Added";
				if ($postingUserChainTO->DMLType=="DELETE") $this->errorTO->description="Principal-Chain Successfully Removed";
			  	return $this->errorTO;
			  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
    }


    public function postProductValidation($postingUserProductTO) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");

    	// more secure than passing
    	if (!isset($_SESSION)) session_start;
		$userId = $_SESSION['user_id'];
		$principalId = $_SESSION['principal_id'];

    	if (!ValidationCommonUtils::checkPostingType($postingUserProductTO->DMLType)) return false;

		if((!preg_match(GUI_PHP_INTEGER_REGEX,$postingUserProductTO->userId)) || (!preg_match(GUI_PHP_INTEGER_REGEX,$postingUserProductTO->principalProductUId))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid User or Product ID type ($postingUserProductTO->userId:$postingUserProductTO->principalProductUId)";
			return false;
		};

		$administrationDAO = new AdministrationDAO($this->dbConn);
		$isAdminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
		$productDAO = new ProductDAO($this->dbConn);

		$mfProduct=$productDAO->getPrincipalProductItem($postingUserProductTO->principalId,$postingUserProductTO->principalProductUId);

		if (!$isAdminUser) {
			$mfSUPP = $productDAO->getUserPrincipalProductItem($postingUserProductTO->principalId,$postingUserProductTO->principalProductUId,$userId);
	    }
		$mfUPP = $productDAO->getUserPrincipalProductItem($postingUserProductTO->principalId,$postingUserProductTO->principalProductUId,$postingUserProductTO->userId);

		// invalid product
		if (sizeof($mfProduct)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Product supplied";
				return false;
		}
		if ($mfProduct[0]["status"]==FLAG_STATUS_DELETED) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Product supplied. Product is deleted. To be able to use this product, you must undelete it.";
				return false;
		}

		if (!$isAdminUser) {
			if (sizeof($mfSUPP)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="User cannot add product(s) to users which they themselves do not have privileges for";
				return false;
			}
		}

		$hasRole=$administrationDAO->hasRole($userId,$_SESSION['principal_id'],ROLE_MAINTAIN_PRODUCT_USERS);
		if (!$hasRole) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="You do not have priviledges to add/remove product(s) for this user";
			return false;
		};

		if ($postingUserProductTO->DMLType=="DELETE") {
			if ((sizeof($mfUPP)!=1) || ($mfUPP[0]["status"]==FLAG_STATUS_DELETED)) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="No rows found for deletion";
				return false;
			};
		}

		if ($postingUserProductTO->DMLType=="INSERT") {
			if (sizeof($mfUPP)>0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="This user already has the product you requested for addition, or has the bypass product restriction role.";
				return false;
			};
		}

		// check user that we are modifying is within priviledges
		if (!$isAdminUser) {
			$mfUsers=$administrationDAO->getUsersByPrincipalDepotArray($userId);
			if (!isset($mfUsers[$postingUserProductTO->userId])) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have priviledges to modify this user";
				return false;
			};
		}

		return true;

    }

    public function postUserProduct($postingUserProductTO) {
    	$resultOK = $this->postProductValidation($postingUserProductTO);
    	if ($resultOK) {

    		if ($postingUserProductTO->DMLType=="DELETE") {
	    		$sql="delete from user_principal_product
	    			  where  user_uid=".$postingUserProductTO->userId."
	    			  and    principal_product_uid=".$postingUserProductTO->principalProductUId;
    		} else if ($postingUserProductTO->DMLType=="INSERT") {
    			$sql="INSERT INTO user_principal_product
    				  (
						user_uid,
						principal_product_uid
    				  )
    				  VALUES (".
    				  	$postingUserProductTO->userId.",".
    				  	$postingUserProductTO->principalProductUId.
    				  ")";
    		  }

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingUserProductTO->principalProductUId);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
				if ($postingUserProductTO->DMLType=="INSERT") $this->errorTO->description="User Principal-Product Successfully Added";
				if ($postingUserProductTO->DMLType=="DELETE") $this->errorTO->description="User Principal-Product Successfully Removed";
			  	return $this->errorTO;
			  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
    }

    /*
     *
     *  User
     * NB : This function skips Roles if the user is an administrator
     *
     */
    public function postUserValidation($postingUserTO, $userId) {
    	if (!isset($_SESSION)) session_start();
    	if (!ValidationCommonUtils::checkPostingType($postingUserTO->DMLType)) return false;

  	  // more secure than passing
    	if (!isset($_SESSION)) session_start;
  		//$userId = $_SESSION['user_id'];
  		$principalId = $_SESSION['principal_id'];

  		if(($postingUserTO->userId=="") && ($postingUserTO->DMLType=="UPDATE")) {
  			$this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description="UserId not supplied.";
  			return false;
  		};

  		if(($postingUserTO->category!=FLAG_PRINCIPAL_USER) && ($postingUserTO->category!=FLAG_DEPOT_USER) && ($postingUserTO->category!=FLAG_SALESAGENT_USER) && ($postingUserTO->category!=FLAG_TRUCKDRIVER_USER)) {
  			$this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description="User Category may only be Principal, Depot, Truck Driver or Sales Agent type.";
  			return false;
  		};

  		if(($postingUserTO->category==FLAG_SALESAGENT_USER) && ($postingUserTO->organisationName=="")) {
  			$this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description="Sales Agents require an organisation name to be filled in.";
  			return false;
  		};

  		if(!preg_match(GUI_PHP_EMAIL_REGEX,$postingUserTO->userEmail)) {
  			$this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description="Email must be of recognisable structure.";
  			return false;
  		};

  		if((strlen($postingUserTO->userName) < 6) && ($postingUserTO->DMLType=="INSERT")) {
  			$this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description="UserName must be atleast 6 characters.";
  			return false;
  		};

  		/*
  		if(strlen($postingUserTO->password) < 6) {
  			$returnMessages=new ErrorTO;
  			$this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description="Password must be atleast 6 characters.";
  			print(CommonUtils::getJavaScriptMsg($returnMessages));
  			return;
  		};
  		*/

  		if(!ValidationCommonUtils::checkFieldBooleanSimple($postingUserTO->suspended)) {
  			$this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description="Suspended must be either Yes or No.";
  			return false;
  		};

  		if(!ValidationCommonUtils::checkFieldBooleanSimple($postingUserTO->deleted)) {
  			$this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description="Deleted must be either Yes or No.";
  			return false;
  		};

  		if(!ValidationCommonUtils::checkFieldBooleanSimple($postingUserTO->selfRegistered)) {
  			$this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description="SelfRegistered must be either Yes or No.";
  			return false;
  		};

  		$administrationDAO = new AdministrationDAO($this->dbConn);
  		$recipientHasSURole=$administrationDAO->hasRoleSuperUser($postingUserTO->userId, $principalId); // recipient is a SU ?
  		$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
  		$principalDAO = new PrincipalDAO($this->dbConn);

  		if ($postingUserTO->DMLType=="INSERT") {
  			$mfUserUN=$administrationDAO->getUsersByUserNameArray($postingUserTO->userName);
  			// userName must NOT exist
  			if (sizeof($mfUserUN)>0) {
  				$this->errorTO->type=FLAG_ERRORTO_ERROR;
  				$this->errorTO->description="UserName already exists. Could not add user.";
  				return false;
  			};

  			// skip check for administrators as they choose from global list and are not registered for these all.
  			/*
  			if (!$hasAdminRole) {
  				$mfSUUPD = $principalDAO->getUserPrincipalDepotItem($userId,$postingUserTO->principalId(fld does not exist),$postingUserTO->depotId);
  				if(sizeof($mfSUUPD)==0) {
  					$this->errorTO->type=FLAG_ERRORTO_ERROR;
  					$this->errorTO->description="You can only add users for principal-depots for which you have access to.";
  					return false;
  				}
  			}
  			*/

  			if (!$adminUser) {
  			  // need the Create User role to create a general User
  				$hasRole = $administrationDAO->hasRole($userId,$_SESSION['principal_id'],ROLE_CREATE_USER);
  				if (!$hasRole) {
  						$this->errorTO->type=FLAG_ERRORTO_ERROR;
  						$this->errorTO->description="You do not have permissions to create a GENERAL USER.";
  						return false;
  				};
  			}


  		} else if ($postingUserTO->DMLType=="UPDATE") {
  			$mfU = $administrationDAO->getUserItem($postingUserTO->userId);
  			// user MUST exist
  			if (sizeof($mfU)==0) {
  				$this->errorTO->type=FLAG_ERRORTO_ERROR;
  				$this->errorTO->description="User could not be found for updating";
  				return false;
  			};
  			// check user that we are modifying is within priviledges
  			if (!$adminUser) {
  				$mfUsers=$administrationDAO->getUsersByPrincipalDepotArray($userId);
  				if (!isset($mfUsers[$postingUserTO->userId])) {
  					$this->errorTO->type=FLAG_ERRORTO_ERROR;
  					$this->errorTO->description="You do not have priviledges to modify this user";
  					return false;
  				};
  			}
  			// if user we are modifying is SU, you must have MODIFY SU role or DELETE SU appropriately
  			if (($mfU[0]['deleted'] != $postingUserTO->deleted) && ($postingUserTO->deleted!=0)) {
  				// modified deleted status
  				if (!$adminUser) {
  					if ($recipientHasSURole) $hasRole = $administrationDAO->hasRole($userId,$principalId,ROLE_DELETE_SU);
  					else $hasRole = $administrationDAO->hasRole($userId,$principalId,ROLE_DELETE_GU);
  					if (!$hasRole) {
  						$this->errorTO->type=FLAG_ERRORTO_ERROR;
  						$this->errorTO->description="You do not have permissions to delete a GENERAL/SUPER USER.";
  						return false;
  					};
  				}
  			} else {
  				// modified other fields
  				if (
  					($postingUserTO->userName != $mfU[0]['username']) ||
  				  	//($postingUserTO->password != $mfU[0]['password']) ||
  				  	($postingUserTO->fullName != $mfU[0]['full_name']) ||
  				  	($postingUserTO->userEmail != $mfU[0]['user_email']) ||
  				  	($postingUserTO->userTel != $mfU[0]['user_tel']) ||
  				  	($postingUserTO->userCell != $mfU[0]['user_cell']) ||
  				  	($postingUserTO->suspended != $mfU[0]['suspended']) ||
  				  	($postingUserTO->selfRegistered != $mfU[0]['selfregistered']) ||
  				  	($postingUserTO->category != $mfU[0]['category'])
  					) {
  					if (!$adminUser) {
  						if ($recipientHasSURole) $hasRole = $administrationDAO->hasRoleInList($userId,$postingUserTO->userId,ROLE_MODIFY_SU,$mfU[0]['category'],$principalId);
  						else $hasRole = $administrationDAO->hasRoleInList($userId,$postingUserTO->userId,ROLE_MODIFY_GU,$mfU[0]['category'], $principalId);
  						if (!$hasRole) {
  							$this->errorTO->type=FLAG_ERRORTO_ERROR;
  							$this->errorTO->description="You do not have permissions to modify a GENERAL/SUPER USER.";
  							return false;
  						}
  					}
  				}
			  }

		  }

  		return true;

    }

    public function postUser($postingUserTO, $userId) {
    	global $ROOT; global $PHPFOLDER;

    	$resultOK = $this->postUserValidation($postingUserTO, $userId);

    	if ($resultOK) {

    		if ($postingUserTO->DMLType=="UPDATE") {
	    		$sql="UPDATE users
	    			  SET full_name='".$postingUserTO->fullName."',
	    			  	  user_email='".$postingUserTO->userEmail."',
	    			  	  user_tel='".$postingUserTO->userTel."',
	    			  	  user_cell='".$postingUserTO->userCell."',
	    			  	  suspended=".$postingUserTO->suspended.",
	    			  	  deleted=".$postingUserTO->deleted.",
	    			  	  category='".$postingUserTO->category."',
    						  organisation_name = '{$postingUserTO->organisationName}',
    						  staff_user = '". $postingUserTO->staffUser."',
    						  admin_user = '". $postingUserTO->adminUser."'
	    			  where  uid=".$postingUserTO->userId;
    		} else if ($postingUserTO->DMLType=="INSERT") {

    			include_once($ROOT.$PHPFOLDER.'libs/EncryptionClass.php');
    			$eC = new EncryptionClass();

          $newUserPWD = NEW_USER_PWD;
    			$eR_dbk = $eC->encrypt(ENCRYPT_DB_KEY, $newUserPWD, ENCRYPT_PWD_LENGTH);

    			if (sizeof($eC->errors)>0) trigger_error('Encryption of user password failed!', E_USER_ERROR); // should terminate processing due to ExceptionThrower
    			$sql="INSERT INTO users
    				  (
    						username,
    						password,
    						full_name,
    						user_email,
    						user_tel,
    						user_cell,
    						suspended,
    						selfregistered,
    						deleted,
    						category,
    						created_date,
    						created_by,
    						organisation_name,
    						staff_user,
    						admin_user,
                system_uid
    				  )
    				  VALUES (".
    				  	"'".$postingUserTO->userName."',".
    				  	"'{$eR_dbk}',".
    				  	"'".$postingUserTO->fullName."',".
    				  	"'".$postingUserTO->userEmail."',".
    				  	"'".$postingUserTO->userTel."',".
    				  	"'".$postingUserTO->userCell."',".
    				  	$postingUserTO->suspended.",".
    				  	$postingUserTO->selfRegistered.",".
    				  	$postingUserTO->deleted.",".
    				  	"'".$postingUserTO->category."',".
    				  	"'" . gmdate(GUI_PHP_DATE_FORMAT) . "', ".
    				  	$userId.",
      					'{$postingUserTO->organisationName}',
      					'".$postingUserTO->staffUser."',
    				  	'".$postingUserTO->adminUser."', ".
                $_SESSION['system_id'] . 
    				  ")";
    		  }

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingUserTO->fullName);

    	} else {
    		return $this->errorTO;
    	  }

    	if (($postingUserTO->DMLType=="INSERT") && ($this->errorTO->type==FLAG_ERRORTO_SUCCESS)) {
    		$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId();
    	}
    	return $this->errorTO;
    }


    public function postUserPreferencesValidation($postingUserPreferencesTO) {
    	if (!isset($_SESSION)) session_start();
    	if (!ValidationCommonUtils::checkPostingType($postingUserPreferencesTO->DMLType)) return false;

    	$administrationDAO = new AdministrationDAO($this->dbConn);

		// more secure than passing
    	if (!isset($_SESSION)) session_start;
		$userId = $_SESSION['user_id'];
		$userCategory = $_SESSION['category'];

		if($userId!=$postingUserPreferencesTO->userUId) {
			$returnMessages=new ErrorTO;
			$returnMessages->type=FLAG_ERRORTO_ERROR;
			$returnMessages->description="UserId differs from session.";
			print(CommonUtils::getJavaScriptMsg($returnMessages));
			return;
		};

		if (!preg_match(GUI_PHP_INTEGER_REGEX,$postingUserPreferencesTO->pageSizeDefault)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid page size default supplied.";
			return false;
		}
		if (!in_array($postingUserPreferencesTO->notifyExceptionTag,array('N','Y'))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Value passed for Notify Exception Tag.";
			return false;
		}

    if (!in_array($postingUserPreferencesTO->capturePreValidationFlag,array('N','Y'))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Value passed for Capture Pre-Validation.";
			return false;
		}


		if ($postingUserPreferencesTO->DMLType=="UPDATE") {
			$mfUP=$administrationDAO->getUserPreferences($userId);
			if (sizeof($mfUP)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="User Preference not found for UPDATE.";
				return false;
			} else if ($mfUP[0]["user_uid"]!=$postingUserPreferencesTO->userUId) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="User Preference userid differs for supplied UId.";
				return false;
			}
		}

		if (!in_array($postingUserPreferencesTO->notifyDepotOrderTag,array("Y","N"))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid value for notification depot order tag.";
			return false;
		}
		if (($userCategory!=FLAG_DEPOT_USER) && ($postingUserPreferencesTO->notifyDepotOrderTag=="Y")) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Notification depot order tag can only be enabled for depot users.";
			return false;
		}

		return true;

    }

    public function postUserPreference($postingUserPreferencesTO) {
    	global $ROOT; global $PHPFOLDER;

    	if ($postingUserPreferencesTO->pageSizeDefault=="") $postingUserPreferencesTO->pageSizeDefault="0";

    	$resultOK = $this->postUserPreferencesValidation($postingUserPreferencesTO);

    	//null handling...
    	$userReportOutputSetting = ($postingUserPreferencesTO->userReportOutputSetting!='')?("'" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->userReportOutputSetting) . "'"):('NULL');

    	if ($resultOK) {

    		if ($postingUserPreferencesTO->DMLType=="UPDATE") {
	    		$sql="UPDATE user_preference
	    			  	SET page_size_default='" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->pageSizeDefault)."',
                    tracking_transaction_day_gap = '" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->trackingTransactionDayGap)."',
                    tracking_transaction_columns = '" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->trackingTransactionColumns)."',
                    notify_exception_tag = '" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->notifyExceptionTag)."',
										notify_depot_order_tag = '" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->notifyDepotOrderTag)."',
                    user_report_output_setting = " . $userReportOutputSetting . ",
                    capture_pre_validation_flag = '" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->capturePreValidationFlag)."',
                    sort_product_dropdown = '" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->sortProductDropdown)."',
                    display_access_log = '" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->displayAccessLog)."'
	    			  where
	    			  	uid='" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->UId)."'
					  	and    user_uid='" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->userUId)."'";
    		} else if ($postingUserPreferencesTO->DMLType=="INSERT") {
    			$sql="INSERT INTO user_preference
                (
                  user_uid,
                  page_size_default,
                  tracking_transaction_day_gap,
                  tracking_transaction_columns,
                  notify_exception_tag,
                  notify_depot_order_tag,
                  user_report_output_setting,
                  capture_pre_validation_flag,
                  sort_product_dropdown,
                  display_access_log
    				  )
    				  VALUES (
    				  	" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->userUId).",
    				  	" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->pageSizeDefault).",
    				  	'" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->trackingTransactionDayGap)."',
    				  	'" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->trackingTransactionColumns)."',
								'" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->notifyExceptionTag)."',
								'" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->notifyDepotOrderTag)."',
								" . $userReportOutputSetting . ",
                '" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->capturePreValidationFlag)."',
                '" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->sortProductDropdown)."',
                '" . mysqli_real_escape_string($this->dbConn->connection, $postingUserPreferencesTO->displayAccessLog)."'
    				  )";
    		  }

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingUserPreferencesTO->userUId);
    		  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
    		  	if ($postingUserPreferencesTO->DMLType=="INSERT") {
    		  		$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId();
    		  	}
    		  	$this->errorTO->description="User Preference successfully submitted";
    		  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
    }

}
?>

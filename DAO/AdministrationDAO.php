<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class AdministrationDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

    public function resetPassword($userId, $sendEmail) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
    	include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
    	include_once($ROOT.$PHPFOLDER.'libs/EncryptionClass.php');

		$eC = new EncryptionClass();
    $newUserPWD = NEW_USER_PWD;
		$eR_dbk = $eC->encrypt(ENCRYPT_DB_KEY, $newUserPWD, ENCRYPT_PWD_LENGTH);
		if (sizeof($eC->errors)>0) trigger_error('Encryption of user password failed!', E_USER_ERROR); // should terminate processing due to ExceptionThrower

    	$sql="update users" .
    		 " set password='".mysqli_real_escape_string($this->dbConn->connection, $eR_dbk)."'" .
    		 " where uid=".mysqli_real_escape_string($this->dbConn->connection, $userId);

    	$eTO=$this->dbConn->processPosting($sql,"UserId: ".$userId);

    	if ($eTO->type==FLAG_ERRORTO_SUCCESS) {
    		$eTO->description="Password Successfully Reset. ";

    		if ($sendEmail=="Y") {
    			$sendResultETO=BroadcastingUtils::sendEmailNewUser($userId,$this->dbConn);
    			$eTO->description .= $sendResultETO->description;
    		}
    	}

    	return $eTO;
    }

    // this should be the only place where a password can be specified by a user, hence the pwd strength test here
    public function changePassword($userId, $password, $sendEmail) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
    	include_once($ROOT.$PHPFOLDER.'libs/EncryptionClass.php');

    	if (
    		(strlen($password)<6) ||
    		((!preg_match("/[a-zA-Z]/",$password)) || (!preg_match("/[0-9]/",$password)))
    		) {
    		$eTO = new ErrorTO;
    		$eTO->type = FLAG_ERRORTO_ERROR;
    		$eTO->description = "Password must be atleast 6 characters, consisting of both alphabetic and numeric characters.";
    		return $eTO;
    	}

		$eC = new EncryptionClass();
		$eR_dbk = $eC->encrypt(ENCRYPT_DB_KEY, $password, ENCRYPT_PWD_LENGTH);
		if (sizeof($eC->errors)>0) trigger_error('Encryption of user password failed!', E_USER_ERROR); // should terminate processing due to ExceptionThrower


		//Start
        //user can't use old pwd
        //compare old and new encrypted pwd
    	$this->dbConn->dbQuery("SELECT u.password
    						FROM users u
						 where u.uid = ".mysqli_real_escape_string($this->dbConn->connection, $userId));
    	$num = mysqli_num_rows($this->dbConn->dbQueryResult);

    	if($num > 0){

          $oldPassword = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC);

          // Binary safe string comparison
          //Returns < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
          $chkOldNewPwd = strcmp($oldPassword['password'], $eR_dbk);

          if ($chkOldNewPwd == '0') {
      		$eTO = new ErrorTO;
      		$eTO->type = FLAG_ERRORTO_ERROR;
      		$eTO->description = "Password must be different from your existing one, please try again. (". $chkOldNewPwd . ")";
      		return $eTO;
      	  }
    	}
    	//End

    	$sql="update users" .
    		 " set password='".mysqli_real_escape_string($this->dbConn->connection, $eR_dbk)."', last_password_change_date = CURDATE()" .
    		 " where uid=".mysqli_real_escape_string($this->dbConn->connection, $userId);

    	$eTO=$this->dbConn->processPosting($sql,"UserId: ".$userId);

    	if ($eTO->type==FLAG_ERRORTO_SUCCESS) {
    		$eTO->description="Password Successfully Changed. ";

    		if ($sendEmail=="Y") {
    			$sendResultETO=BroadcastingUtils::sendEmailNewUser($userId,$this->dbConn);
    			$eTO->description .= $sendResultETO->description;
    		}
    	}

    	return $eTO;
    }

    //actionFilterIds value passed as eg. "23,45,66"
    public function getUserMenuActions($userId, $systemId, $entityUId, $parent, $actionFilterIds) {


		$sql="select a.uid, a.description as description, a.level, a.role_uid, a.url, a.parent, a.override_path, if(a.override_path is null,a.parent, a.override_path) derived_path,
				     b.uid user_role_uid
					from   menu_role a
                                          inner join system_menu s on a.uid = s.menu_role_uid and s.system_uid = '".mysqli_real_escape_string($this->dbConn->connection, $systemId)."'
						   left join user_role b on a.role_uid=b.role_id and
					                               b.user_id=".mysqli_real_escape_string($this->dbConn->connection, $userId)." and
												   (b.entity_uid=".mysqli_real_escape_string($this->dbConn->connection, $entityUId)."  or b.entity_uid is null)
					where  a.level > 2
					and    a.parent=".mysqli_real_escape_string($this->dbConn->connection, $parent)."
					and    (a.role_uid is null or (a.role_uid is not null and b.role_id is not null))
					order  by if(a.order is null,100,a.order), a.uid";

		if ($actionFilterIds!="") $sql.=" and a.uid in (".$actionFilterIds.")";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[$row['uid']] = $row;
		}

		return $arr;
	}

	public function getRolesArray($userId, $entityUId, $systemId) {
		$sql="select a.uid, description, long_description, default_value, a.group, if(b.uid is null,'N','Y') user_has_role, a.parent, a.restricted_to
				from   role a
                                  inner join system_role sr on a.uid = sr.role_uid and sr.system_uid = '".mysqli_real_escape_string($this->dbConn->connection, $systemId)."'
					left join user_role b on a.uid = b.role_id
										and b.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
										and b.entity_uid = '".mysqli_real_escape_string($this->dbConn->connection, $entityUId)."'
			  order by if(parent is not null,parent,a.uid), if(parent is null,1,2), a.uid";
		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[$row['uid']] = $row;
		}

		return $arr;
	}

	public function getRoleItem($roleId) {
		$sql="select a.uid, description, long_description, default_value, a.group, a.parent, a.restricted_to
				from   role a
				where  a.uid = ".mysqli_real_escape_string($this->dbConn->connection, $roleId);

		return $this->dbConn->dbGetAll($sql);
	}


	public function getUserItem($userId) {
		$sql="select uid, password, username, full_name, user_email, user_tel, user_cell, lastlogin, suspended, selfregistered, deleted, category,
                 organisation_name, staff_user, admin_user, system_uid
				from users
				where uid = ".mysqli_real_escape_string($this->dbConn->connection, $userId);

		return $this->dbConn->dbGetAll($sql);
	}

	public function getUsersByUserNameArray($userName) {
		$sql="select uid, password, username, full_name, user_email, user_tel, user_cell, lastlogin, suspended, selfregistered, deleted, a.category
				from users a
				where username = '".mysqli_real_escape_string($this->dbConn->connection, $userName)."'";
		return $this->dbConn->dbGetAll($sql);
	}

	public function getUsersArray() {
		$sql="select a.uid, username, full_name, user_email, user_tel, user_cell, user_acl, lastlogin, a.suspended, selfregistered, deleted, a.category,
	    			group_concat(b.depot_id) depot_id_list, group_concat(if(d.name is null,'unassociated',d.name)) depot_name, b.principal_id,
					if(c.name is null,'unassociated',c.name) principal_name, uc.name as category_name, organisation_name
				from   users a
                                inner join user_category uc on a.system_uid = uc.system_uid and a.category = uc.code
				left join user_principal_depot b on a.uid = b.user_id
				left join principal c on b.principal_id = c.uid
				left join depot d on b.depot_id = d.uid
                                where a.system_uid = '".$_SESSION['system_id']."'
			group by a.uid, username, full_name, user_email, user_tel, user_cell, user_acl, lastlogin, a.suspended, selfregistered, deleted, a.category,
	    			b.principal_id, if(c.name is null,'unassociated',c.name)

			 order by a.full_name";

                 // should never use the user uid as key as it will hide user from other principals as only last uid is used then
                return $this->dbConn->dbGetAll($sql);

	}

	// get users within priviledges to see, include self
	public function getUsersByPrincipalDepotArray($userId) {
		$sql="select *
			  from (
				select distinct a.uid, username, full_name, user_email, user_tel, user_cell, user_acl, lastlogin, a.suspended, selfregistered, deleted, a.category,
	    			c.depot_id, if(e.name is null,'unassociated',e.name) depot_name, b.principal_id, if(d.name is null,'unassociated',d.name) principal_name, uc.name as category_name, organisation_name
				from   users a,
                                       user_category uc,
                                       user_principal_depot b,
                                       user_principal_depot c
				left join principal d on c.principal_id = d.uid
				left join depot e on c.depot_id = e.uid
				where a.uid = c.user_id
				and   b.principal_id = c.principal_id
                                and   a.system_uid = uc.system_uid
                                and   a.category = uc.code
				and   b.depot_id = c.depot_id
				and   b.user_id=".mysqli_real_escape_string($this->dbConn->connection, $userId)."
                                and   a.system_uid = '".$_SESSION['system_id']."'
				union
				select distinct a.uid, username, full_name, user_email, user_tel, user_cell, user_acl, lastlogin, a.suspended, selfregistered, deleted, a.category,
	    			null, 'unassociated', null, 'unassociated', uc.name as category_name, organisation_name
				from   users a
                                  inner join user_category uc on a.system_uid = uc.system_uid and a.category = uc.code
				where a.deleted = 0
                                and   a.system_uid = '".$_SESSION['system_id']."'
				and   not exists (select 1 from user_principal_depot b where a.uid = b.user_id)
				and   a.created_by='".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
				) a
			  order by a.full_name";
		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[$row['uid']] = $row;
		}

		return $arr;
	}

	// userType=users.category
	public function getUserRolesArray($userId, $arrayIndex, $userType) {
		if ($userType==FLAG_PRINCIPAL_USER) {
			$sql="select a.uid, a.user_id, c.principal_id, if(d.name is null,'GLOBAL SETTING',d.name) principal_name, a.role_id, b.description,
					 	  b.group, c.depot_id,  if(e.name is null,'GLOBAL SETTING',e.name) depot_name
							from  user_role a
									   left join user_principal_depot c on a.entity_uid = c.principal_id and a.user_id = c.user_id
									      left join principal d on c.principal_id = d.uid
									      	left join depot e on c.depot_id = e.uid,
									role b
					where  a.role_id = b.uid
					and    a.user_id = ".mysqli_real_escape_string($this->dbConn->connection, $userId);
			$this->dbConn->dbQuery($sql);
		}
		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			if ($arrayIndex=="role_id") $arr[$row['role_id']] = $row;
			else $arr[$row['uid']] = $row;
		}

		return $arr;
	}

	// only check the session flag if is for current user, otherwise use this to check a different user
	public function userIsAdministrator($userId) {
		$sql="select 1
				from   users a
				where  a.admin_user = 'Y'
				and    a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'";
		$this->dbConn->dbQuery($sql);

		if (($this->dbConn->dbQueryResultRows==1) || ($userId==SESSION_ADMIN_USERID)) return true;
		else return false;
	}

	public function hasRoleSuperUser($userId, $entityUId) {
		$sql="select 1
				from   user_role a
				where  a.role_id = ".ROLE_SUPERUSER."
				and    a.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
				and    a.entity_uid = '".mysqli_real_escape_string($this->dbConn->connection, $entityUId)."'";
		$this->dbConn->dbQuery($sql);

		// try broader global setting if not found
		if ($this->dbConn->dbQueryResultRows<1) {
			$sql="select 1
				from   user_role a
				where  a.role_id = ".ROLE_SUPERUSER."
				and    a.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
				and    a.entity_uid is null";
			$this->dbConn->dbQuery($sql);
		}

		if (($this->dbConn->dbQueryResultRows==1) || ($userId==SESSION_ADMIN_USERID)) return true;
		else return false;
	}

	public function hasRole($userId, $entityUId, $roleId) {
		if ($userId==SESSION_ADMIN_USERID) return true;

		$sql="select 1
				from   user_role a
				where  a.role_id = ".$roleId."
				and    a.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
				and    a.entity_uid = '".mysqli_real_escape_string($this->dbConn->connection, $entityUId)."'";
		$this->dbConn->dbQuery($sql);

		// try broader global setting if not found
		if ($this->dbConn->dbQueryResultRows<1) {
			$sql="select 1
				from   user_role a
				where  a.role_id = ".$roleId."
				and    a.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
				and    a.entity_uid is null";
			$this->dbConn->dbQuery($sql);
		}

		if ($this->dbConn->dbQueryResultRows>0) return true;
		else return false;
	}

	public function hasRoleAnyPrincipal($userId, $roleId) {
	  if ($userId==SESSION_ADMIN_USERID) return true;

	  $sql="select 1
    			from   user_role a
    			where  a.role_id = ".$roleId."
    			and    a.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'";

	  $this->dbConn->dbQuery($sql);

	  if ($this->dbConn->dbQueryResultRows>0) return true;
	  else return false;
	}

        public function hasProduct($userId, $productUId, $principalId){

            $sql = "SELECT 1 FROM principal_product p
                             INNER JOIN user_principal_product up ON p.uid = up.principal_product_uid and up.user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
                    WHERE p.uid = '".mysqli_real_escape_string($this->dbConn->connection, $productUId)."'";

            return (count($this->dbConn->dbGetAll($sql))==0)?false:true;
        }

        public function hasDepot($userId, $depotUId, $principalId){

            $sql = "SELECT 1 FROM user_principal_depot
                      WHERE user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
                        AND depot_id = '".mysqli_real_escape_string($this->dbConn->connection, $depotUId)."'
                        AND principal_id = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'";

            return (count($this->dbConn->dbGetAll($sql))==0)?false:true;
        }

	//$roleSetIds eg= "1,2,3"
	public function hasRoleInSet($userId, $entityUId, $roleSetIds) {
		$sql="select 1
				from   user_role a
				where  a.role_id in (".$roleSetIds.")
				and    a.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
				and    a.entity_uid = '".mysqli_real_escape_string($this->dbConn->connection, $entityUId)."'";
		$this->dbConn->dbQuery($sql);

		// try broader global setting if not found
		if ($this->dbConn->dbQueryResultRows<1) {
			$sql="select 1
				from   user_role a
				where  a.role_id in (".$roleSetIds.")
				and    a.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
				and    a.entity_uid is null";
			$this->dbConn->dbQuery($sql);
		}

		if (($this->dbConn->dbQueryResultRows>0) || ($userId==SESSION_ADMIN_USERID)) return true;
		else return false;
	}

	// use this function to check role where the user may be linked to many principals and u don't care which principal exactly.
	// eg. for checking when user details are being modified and and one principal will do for checking against the SU
	public function hasRoleInList($userIdActioner, $userIdRecipient, $roleId, $userType, $entityUId) {
		global $ROOT; global $PHPFOLDER;
		// skip this check if administrator user because admin users aren't confined by principal-depot registrations
		$userIsAdmin = $this->userIsAdministrator($userIdActioner);
		if ($userIsAdmin) return true;

		// skip this check if recipient is not allocated yet to a P-D.
		include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
		$principalDAO = new PrincipalDAO($this->dbConn);
		$mfPD = $principalDAO->getUserPrincipalDepotArray($userIdRecipient,"");
		if (sizeof($mfPD)==0) return true;

		//if ($userType==FLAG_PRINCIPAL_USER) {
			$sql="select 1
					from   user_role a,
						    user_principal_depot c,
						    user_principal_depot d
					where  a.role_id = ".$roleId."
					and    a.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userIdActioner)."'
					and    c.user_id = a.user_id
					and    d.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userIdRecipient)."'
					and    a.entity_uid = c.principal_id
					and    c.principal_id = d.principal_id
					and    c.depot_id = d.depot_id";
		//}

		$this->dbConn->dbQuery($sql);

		// try broader global setting if not found
		if ($this->dbConn->dbQueryResultRows<1) {
			$sql="select 1
				from   user_role a
				where  a.role_id = ".$roleId."
				and    a.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userIdActioner)."'
				and    a.entity_uid is null";
			$this->dbConn->dbQuery($sql);
		}

		if (($this->dbConn->dbQueryResultRows>0) || ($userIdActioner==SESSION_ADMIN_USERID) || ($userIdRecipient==SESSION_ADMIN_USERID)) return true;
		else return false;
	}

	public function getRoleProfiles($systemId) {
		$sql="select uid, description, role_list
			  from   role_profile where system_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $systemId) . "'";

		return $this->dbConn->dbGetAll($sql);
	}

	public function getSuperUsersForPrincipal($principalUId) {
		$sql="select distinct a.uid, user_email
				from   users a,
				       user_principal_depot b,
				       user_role c
				where  a.uid = b.user_id
				and    b.principal_id = '{$principalUId}'
				and    a.uid = c.user_id
				and    b.principal_id = c.entity_uid
				and    c.role_id = ".ROLE_SUPERUSER;

		return $this->dbConn->dbGetAll($sql);
	}

	public function getUserPreferences($userId) {
		$sql="select *
				from  user_preference
				where user_uid = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'";

		return $this->dbConn->dbGetAll($sql);
	}

	public function getSalesAgentsForPrincipal($principalId) {
		$sql="select a.uid user_uid, full_name, organisation_name, group_concat(b.depot_id) depot_list, c.chain_list
				from   users a
							left join (
								select a.uid, group_concat(c.principal_chain_uid) chain_list
								from   users a,
				                   user_principal_chain c,
										 principal_chain_master d
								where  category = '".FLAG_SALESAGENT_USER."'
								and    d.principal_uid = '{$principalId}'
								and    a.uid = c.user_uid
								and    c.principal_chain_uid = d.uid
								and    deleted=0
							   group by a.uid
							) c on a.uid = c.uid,
							user_principal_depot b
				where  category = '".FLAG_SALESAGENT_USER."'
				and    a.uid = b.user_id
				and    b.principal_id = '{$principalId}'
				and    deleted=0
				group by a.uid, full_name, organisation_name, c.chain_list";

		return $this->dbConn->dbGetAll($sql);
	}

	public function getPermissionCounts($userId, $principalId) {
		$sql="select store.totcnt store_totcnt, if(urs.user_id is null,store.pcnt,store.totcnt) store_pcnt, product.totcnt product_totcnt,
					 if (urp.user_id is null,product.pcnt,product.totcnt) product_pcnt,
					 chain.totcnt chain_totcnt,
					 if(urc.user_id is null,chain.pcnt,chain.totcnt) chain_pcnt,
					 prindepot.totcnt prindepot_totcnt, prindepot.pcnt prindepot_pcnt,
					 if(urc.user_id is null,0,1) has_chain_bypass,
					 if(urs.user_id is null,0,1) has_store_bypass,
					 if(urp.user_id is null,0,1) has_product_bypass
				from   users a
							left join (select distinct user_id from user_role ur where ur.user_id = '{$userId}' and ur.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid='{$principalId}')) urc on urc.user_id = a.uid
							left join (select distinct user_id from user_role ur where ur.user_id = '{$userId}' and ur.role_id = ".ROLE_BYPASS_USER_STORE_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid='{$principalId}')) urs on urs.user_id = a.uid
							left join (select distinct user_id from user_role ur where ur.user_id = '{$userId}' and ur.role_id = ".ROLE_BYPASS_USER_PRODUCT_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid='{$principalId}')) urp on urp.user_id = a.uid,
						 (select count(*) totcnt, sum(if(ups.uid is null,0,1)) pcnt
						  from principal_store_master psm
						  			left join user_principal_store ups on psm.uid = ups.principal_store_uid and ups.user_uid = '{$userId}'
						  			left join user_principal_chain psmupc on psm.principal_chain_uid = psmupc.principal_chain_uid and psmupc.user_uid = '{$userId}'
									left join user_role ur on ur.user_id = '{$userId}' and ur.role_id = ".ROLE_BYPASS_USER_CHAIN_RESTRICTION." and (ur.entity_uid is null or ur.entity_uid='{$principalId}')
									inner join user_principal_depot psmupd on psm.principal_uid = psmupd.principal_id and psm.depot_uid = psmupd.depot_id and psmupd.user_id='{$userId}'
						  where  psm.principal_uid='{$principalId}' and (psmupc.uid IS NOT NULL or ur.uid IS NOT NULL)) store,
						 (select count(*) totcnt, sum(if(upp.uid is null,0,1)) pcnt
						  from principal_product pp
						  			left join user_principal_product upp on pp.uid = upp.principal_product_uid and upp.user_uid='{$userId}'
						  where  pp.principal_uid='{$principalId}') product,
				 		 (select count(*) totcnt, sum(if(upc.uid is null,0,1)) pcnt
						  from principal_chain_master pcm
						  			left join user_principal_chain upc on pcm.uid = upc.principal_chain_uid and upc.user_uid='{$userId}'
						  where  pcm.principal_uid='{$principalId}') chain,
						 (select count(*) totcnt, sum(if(upd.uid is null,0,1)) pcnt
						  from (select distinct principal_id, depot_id from user_principal_depot where principal_id='{$principalId}') pd
						  			left join user_principal_depot upd on pd.principal_id = upd.principal_id and pd.depot_id = upd.depot_id and upd.user_id='{$userId}'
						  where  pd.principal_id='{$principalId}') prindepot
				where  a.uid = '{$userId}'";

		return $this->dbConn->dbGetAll($sql);
	}


	// get users within priviledges to see, include self
	public function getUsersAccessLogForDays($userId, $forPastDays = 30) {

		$sql="SELECT
				t.uid,
                t.user_uid,
                t.principal_uid,
                t.login_date_time,
                t.remote_address,
                p.name as 'principal_name',
                p.system_uid as 'system'
			  FROM user_tracking t
				LEFT JOIN principal p on t.principal_uid = p.uid
			  WHERE t.user_uid = ".mysqli_real_escape_string($this->dbConn->connection, $userId)."
			  AND DATE(t.login_date_time) BETWEEN (CURDATE()-INTERVAL " . $forPastDays . " DAY) AND CURDATE()
			  ORDER BY t.login_date_time DESC";
		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[$row['uid']] = $row;
		}

		return $arr;
	}
	// can be used to validate principal access by passing third parameter
	public function getUsersPrincipals($userId, $systemId, $principalId=false) {

		$sql="SELECT distinct a.principal_id, b.name principal_name, b.principal_code, b.principal_type
						from   user_principal_depot a,
							   principal b
						where  b.uid = a.principal_id
						and    a.user_id = ".mysqli_real_escape_string($this->dbConn->connection, $userId)."
                        and    b.system_uid = ".mysqli_real_escape_string($this->dbConn->connection, $systemId).
                        (($principalId===false)?"":" and b.uid = ".mysqli_real_escape_string($this->dbConn->connection, $principalId)) . "
						order  by b.name";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[$row['principal_id']] = $row;
		}

		return $arr;
	}


	public function getAllFieldPreferences($principalId, $systemId, $systemName, $docType = false){

          $sql="select field_preference.hide_field
                from  field_preference
                where system_uid = '".mysqli_real_escape_string($this->dbConn->connection, $systemId)."'
                and   (FIND_IN_SET('".mysqli_real_escape_string($this->dbConn->connection, $principalId)."',principal_uid)>0)
                and   1
                "  .  (($docType!==false)?(" and (document_type_uid = '".mysqli_real_escape_string($this->dbConn->connection, $docType)."' OR document_type_uid is null) "):('')) . "
                order by if(document_type_uid is not null,1,2)";
                      
           return $this->dbConn->dbGetAll($sql);

	}
 // **********************************************************************
 // ************************************************************************
  // **********************************************************************
 // *************************************************************************


	public function getSystemByUid($systemId, $dbase=''){

          $sql="select * from ".mysqli_real_escape_string($this->dbConn->connection, $dbase).".`system`
                where uid = '".mysqli_real_escape_string($this->dbConn->connection, $systemId)."'";
                
          return $this->dbConn->dbGetAll($sql);

	}


	public function getUserCategoryAll(){

          if (!isset($_SESSION)) session_start;
          $systemId = $_SESSION['system_id'];

          $sql="select
                  `uid`, `system_uid`, `code`, `name`
                from  user_category
                where system_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $systemId) . "'";

          return $this->dbConn->dbGetAll($sql);

	}

  public function getVATAuthorisations($principalUId){

          $sql="select storeCnt, prdCnt
                from   (select count(*) storeCnt
                        from   principal_store_master
                        where  principal_uid = {$principalUId}
                        and    ifnull(vat_excl_authorised_by,'')=''
                        and    no_vat=1
                        and    status = '".FLAG_STATUS_ACTIVE."') a,
                       (select count(*) prdCnt
                        from   principal_product
                        where  principal_uid = {$principalUId}
                        and    ifnull(vat_excl_authorised_by,'')=''
                        and    vat_rate=0
                        and    status = '".FLAG_STATUS_ACTIVE."') b";

          $result=$this->dbConn->dbGetAll($sql);

          $retArr=array("storeCnt"=>$result[0]["storeCnt"], "prdCnt"=>$result[0]["prdCnt"]);

          $sql="select uid, deliver_name
                from   principal_store_master
                where  principal_uid = {$principalUId}
                and    ifnull(vat_excl_authorised_by,'')=''
                and    no_vat=1
                and    status = '".FLAG_STATUS_ACTIVE."'
                limit  3";

          $result=$this->dbConn->dbGetAll($sql);

          $retArr["storeList"]=$result;

          $sql="select uid, product_code, product_description
                from   principal_product
                where  principal_uid = {$principalUId}
                and    ifnull(vat_excl_authorised_by,'')=''
                and    vat_rate=0
                and    status = '".FLAG_STATUS_ACTIVE."'
                limit  3";

          $result=$this->dbConn->dbGetAll($sql);

          $retArr["prdList"]=$result;

          return $retArr;

  }

 public function getCaptureUsers($principalUId) {
    $sql="select distinct  (u.uid), u.full_name
          from .users u, .user_principal_depot upd, .user_role ur
          where u.uid = upd.user_id
          and   upd.principal_id = $principalUId
          and   u.organisation_name not in ('STAFF')
          and   ur.user_id = u.uid
          and   ur.role_id in (73)";

    return $this->dbConn->dbGetAll($sql);
  }
//***************************************************************************************************************************************************************************  
 public function getUserPrincipals($userId) {
    $sql="SELECT DISTINCT(upd.principal_id), TRIM(p.name) as 'Principal'
          FROM .user_principal_depot upd
          LEFT JOIN .principal p ON upd.principal_id = p.uid
          WHERE upd.user_id = " . $userId . "
          AND   p.`status` = 'A'
          ORDER BY p.name";

    return $this->dbConn->dbGetAll($sql);
  }
//***************************************************************************************************************************************************************************  

  public function checkWarehouseUserAdmin($userUId) {
  	
    $sql = "SELECT u.uid,
                   u.category
            FROM users u
            WHERE u.uid = ". mysqli_real_escape_string($this->dbConn->connection, $userUId) .";";
        	
    $wUser = $this->dbConn->dbGetAll($sql);

    return $wUser;
  }
//***************************************************************************************************************************************************************************  


}
?>
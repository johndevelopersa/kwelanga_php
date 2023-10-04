<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class DepotDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

	public function getAllDepotsArray() {
		$sql="SELECT a.uid, a.code, a.name depot_name
			   from depot a
         WHERE system_uid = ".mysqli_real_escape_string($this->dbConn->connection, $_SESSION['system_id'])."";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[$row['uid']] = $row;
			}
		}

		return $arr;
	}

  // Do NOT use system id here. All depots need to be returned for validation processes.
  public function getAllDepotsGlobally() {
    $sql="SELECT a.*
         from depot a";

    $this->dbConn->dbQuery($sql);

    $arr=array();
    if ($this->dbConn->dbQueryResultRows > 0) {
      while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
        $arr[$row['uid']] = $row;
      }
    }

    return $arr;
  }

	// historical dd's hardcoded - drop in future when not needed
	public static function getHistoricalRegionDD() {
		$regionArr[]=array("id"=>"UJ","description"=>"Ullmanns Johannesburg");
		$regionArr[]=array("id"=>"UC","description"=>"Ullmanns Cape Town");
		$regionArr[]=array("id"=>"UD","description"=>"Ullmanns Durban");
		$regionArr[]=array("id"=>"TE","description"=>"Turner Brothers East London");
		$regionArr[]=array("id"=>"FP","description"=>"Ullmanns Port Elizabeth");
		$regionArr[]=array("id"=>"SR","description"=>"Schoeman & Roodt");
		$regionArr[]=array("id"=>"UB","description"=>"Ullmanns Benoni");
		$regionArr[]=array("id"=>"AS","description"=>"AST - African-Spirit");
		$regionArr[]=array("id"=>"MM","description"=>"Megamore");
		$regionArr[]=array("id"=>"DC","description"=>"Deco Distribution F.S.");
		$regionArr[]=array("id"=>"TC","description"=>"Brenco Cape");
		$regionArr[]=array("id"=>"ME","description"=>"Megamor EL");

		return $regionArr;
	}



	public function getAllDepotsForPrincipalArray($userId, $principalId) {

		$sql="SELECT a.uid, a.code, a.name depot_name
			  from depot a
			  where exists (select 1 from user_principal_depot b
			  				where a.uid = b.depot_id
			  				and   b.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
			  				and   b.principal_id = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."')";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[$row['uid']] = $row;
			}
		}

		return $arr;
	}

	public function getDepotItemForDepotUser($userId, $depot) {

		$sql="SELECT a.uid, a.code, a.name depot_name
			  from depot a
			  where exists (select 1 from user_principal_depot b
			  				where a.uid = b.depot_id
                                                        and   b.user_id = '".mysqli_real_escape_string($this->dbConn->connection, $userId)."'
			  				and   b.depot_id = '".mysqli_real_escape_string($this->dbConn->connection, $depot)."')";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[$row['uid']] = $row;
			}
		}

		return $arr;
	}

	public function getAllDepotsForUserWHS($userId, $systemId) {


          if(CommonUtils::isAdminUser() || CommonUtils::isStaffUser()){

            $sql="select d.uid, 
                         d.name, 
                         d.wms, 
                         skip_inpick_stage,
                         waiting_dispatch,
                         no_unaccepted
                  FROM depot d
                  WHERE d.system_uid = ".mysqli_real_escape_string($this->dbConn->connection, $systemId)."
                  AND d.wms = 'Y'
                  order by d.name";

          } else {

            $sql="select d.uid, 
                         d.name, 
                         d.wms, 
                         d.skip_inpick_stage,
                         d.waiting_dispatch,
                         no_unaccepted
                  FROM user_principal_depot u
                  inner join depot d on u.depot_id = d.uid and system_uid = ".mysqli_real_escape_string($this->dbConn->connection, $systemId)."
                  inner join principal p on u.principal_id = p.uid and d.system_uid = ".mysqli_real_escape_string($this->dbConn->connection, $systemId)."
                  where u.user_id = ".mysqli_real_escape_string($this->dbConn->connection, $userId)."
                  and d.wms = 'Y'                  
                  group by u.depot_id order by d.name";
          }
		return $this->dbConn->dbGetAll($sql);
	}




	public function getDepotItem($depotUId) {
		$sql="SELECT a.uid, a.code, a.name depot_name, depot_email_list, wms, delivery_calendar_enabled, delivery_calendar_parameters,
		             a.skip_inpick_stage,charge,paper_charge, delivery_note, waiting_dispatch,no_unaccepted
			  from depot a
			  where  a.uid ='".mysqli_real_escape_string($this->dbConn->connection, $depotUId)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[$row['uid']] = $row;
			}
		}

		return $arr;
	}

	// get depot uid by special field, *** could be multiple returned ***
	// this uses only the UID and not label for field due to improved performance
	// Although depots are NOT stored by principal (ie. there is no principal_depot table), the special fields are
	/**
	 * @return array(uid)
	 */
	public function getPrincipalDepotBySF($principalId, $specialFieldId, $specialFieldValue) {
		// this differs from the other SF lookups in store and chain beacause depots are not stored by principal therefore entity_uid can be repeated
		$sql="select   d.uid, d.code
				from   depot d,
					   special_field_fields sff,
					   special_field_details sfd
				where  '{$principalId}' = sff.principal_uid
				and    d.uid = sfd.entity_uid
				and    sff.type = 'D'
				and    sff.uid = '{$specialFieldId}'
				and    sff.uid = sfd.field_uid
				and    sfd.value = '{$specialFieldValue}'";

		return $this->dbConn->dbGetAll($sql);
	}

	// get store uid by special field, *** could be multiple returned ***
	/**
	 * @return array(uid)
	 */
	public function getPrincipalDepotBySFName($principalId, $specialFieldName, $specialFieldValue) {
		$sql="select   d.uid
				from   depot d,
					   special_field_fields sff,
					   special_field_details sfd
				where  '{$principalId}' = sff.principal_uid
				and    d.uid = sfd.entity_uid
				and    sff.type = 'D'
				and    sff.name = '{$specialFieldName}'
				and    sff.uid = sfd.field_uid
				and    sfd.value = '{$specialFieldValue}'";

		return $this->dbConn->dbGetAll($sql);
	}

	public function getDepotDeliveryCalendarByYear($depotId, $year) {

          $sql="select   c.*, d.name as 'depot_name', u.uid as user_uid, u.full_name as user_name
                  from   depot_delivery_calendar c
                    inner join depot d on c.depot_uid = d.uid
                    left join users u on c.created_by_user_uid = u.uid
                  where  c.depot_uid = '{$depotId}'
                    and  timestamp BETWEEN ".mktime(0,0,0,1,1,$year)." AND ".mktime(0,0,0,12,31,$year)."";

          return $this->dbConn->dbGetAll($sql);
	}

	public function getDepotDeliveryCalendarByTimestamp($depotId, $timestamp) {

          $sql="select   c.*, d.name as 'depot_name', u.uid as user_uid, u.full_name as user_name
                  from   depot_delivery_calendar c
                    inner join depot d on c.depot_uid = d.uid
                    left join users u on c.created_by_user_uid = u.uid
                  where  c.depot_uid = '{$depotId}'
                    and  timestamp = '{$timestamp}'";

          return $this->dbConn->dbGetAll($sql);
	}

	public function getWarehouseAreas($userId, $depotId) {
		

          $sql="SELECT DISTINCT(wa.uid) as 'waUid', wa.wh_area
                FROM warehouse_area wa
                INNER JOIN .user_principal_depot upd ON upd.depot_id = " . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "
                                                     AND upd.depot_id = wa.depot_uid 
                                                     AND upd.user_id = '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "'
                WHERE wa.`status` = 'A'
                ORDER BY wa.wh_area ";

          return $this->dbConn->dbGetAll($sql);
	}

}
?>

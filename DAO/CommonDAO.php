<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');


class CommonDAO {

  private $dbConn;

  function __construct($dbConn) {
    $this->dbConn = $dbConn;
  }

  public function getDaysArray() {

    $sql = "select uid, name
                    from   day
                    ORDER BY `uid`";

    $this->dbConn->dbQuery($sql);

    $arr = array();
    if ($this->dbConn->dbQueryResultRows > 0) {
      while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)) {
        $arr[$row['uid']] = $row;
      }
    }

    return $arr;
  }

  public function getPeriodArray($principalId, $sort) {
  	
  	if($sort == "Dates") {
  		 $order = "order by pp.start_date";
  	} else {
  		$order = "order by pp.year, pp.period";
  	}
  	
  	if(in_array($principalId, array(305)))  { 
  		   $range = 1000;
  	} else { 
  		  if(in_array($principalId, array(207)))   {
  		     $range = 390; 
        } else {
  	       $range = 90;
  	    }   
  	}

    $sql = "select pp.uid,
            pp.principal_uid,
            pp.year, 
            pp.period, 
            if(pp.principal_uid = 305,'2018-01-01',pp.start_date) as 'start_date',
            pp.end_date, 
            if(curdate() >= pp.start_date,if(curdate() <= pp.end_date,'Current',0),0) as 'sort'
            from .principal_period pp
            where pp.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) ."
            and  substr(pp.end_date,1,7) between substr(DATE_SUB(curdate(), INTERVAL " . $range . " DAY),1,7) and substr(curdate(),1,7)"
            . $order . " ;";
            
 //    echo $sql;
            
    return $this->dbConn->dbGetAll($sql);
    
  }

  public function getFinYearArray($principalId) {

    $sql = "select fy.uid,
            fy.principal_uid,
            fy.year, 
            fy.start_date,
            fy.end_date
            from .principal_financial_year fy
            where fy.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) ."
            order by fy.year;";
    return $this->dbConn->dbGetAll($sql);
    
  }



  public function getDocumentTypesAllowedArray($userId, $principalId) {

    $sql = "select distinct a.uid, ifnull(pdt.description,a.description) description, code, show_on_capture
				from   document_type a
							left join (select distinct user_id, entity_uid, role_id from user_role) b on
									a.role_id = b.role_id and
									b.user_id = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . " and
									(b.entity_uid=" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . " or b.entity_uid is null)
							left join principal_document_type pdt on a.uid = pdt.document_type_uid and pdt.principal_uid = '{$principalId}'
				where  a.role_id is null or (a.role_id is not null and b.role_id is not null)";

    return $this->dbConn->dbGetAll($sql);
  }


  public function getReportDocumentTypesAllowedArray($userId, $principalId) {

    $sql = "select distinct a.uid, ifnull(pdt.description,a.description) description, code
				from   document_type a
							left join (select distinct user_id, entity_uid, role_id from user_role) b on
									a.report_role_id = b.role_id and
									b.user_id = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . " and
									(b.entity_uid=" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . " or b.entity_uid is null)
							left join principal_document_type pdt on a.uid = pdt.document_type_uid and pdt.principal_uid = '{$principalId}'
				where  a.role_id is null or (a.role_id is not null and b.role_id is not null)";

    return $this->dbConn->dbGetAll($sql);
  }


  public function getDocumentTypesAllowedItem($principalId, $docTypeUId) {

    $sql = "select distinct a.uid, ifnull(pdt.description,a.description) description, code
				from   document_type a
									left join principal_document_type pdt on a.uid = pdt.document_type_uid and pdt.principal_uid = '{$principalId}'
				where a.uid=" . mysqli_real_escape_string($this->dbConn->connection, $docTypeUId);

    return $this->dbConn->dbGetAll($sql);
  }


  public function getUserDocumentTypesAllowedItem($userId, $principalId, $docTypeUId) {

    $sql = "select distinct a.uid, ifnull(pdt.description,a.description) description, code
				from   document_type a
							left join (select distinct user_id, entity_uid, role_id from user_role) b on
									a.role_id = b.role_id and
									b.user_id = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . " and
									(b.entity_uid=" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . " or b.entity_uid is null)
							left join principal_document_type pdt on a.uid = pdt.document_type_uid and pdt.principal_uid = '{$principalId}'
				where  a.role_id is null or (a.role_id is not null and b.role_id is not null)
				and    a.uid=" . mysqli_real_escape_string($this->dbConn->connection, $docTypeUId);
				
    return $this->dbConn->dbGetAll($sql);
  }


}


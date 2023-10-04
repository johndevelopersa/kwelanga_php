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
      while ($row = mysql_fetch_array($this->dbConn->dbQueryResult, MYSQL_ASSOC)) {
        $arr[$row['uid']] = $row;
      }
    }

    return $arr;
  }


  public function getDocumentTypesAllowedArray($userId, $principalId) {

    $sql = "select distinct a.uid, ifnull(pdt.description,a.description) description, code, show_on_capture
				from   document_type a
							left join (select distinct user_id, entity_uid, role_id from user_role) b on
									a.role_id = b.role_id and
									b.user_id = " . mysql_real_escape_string($userId) . " and
									(b.entity_uid=" . mysql_real_escape_string($principalId) . " or b.entity_uid is null)
							left join principal_document_type pdt on a.uid = pdt.document_type_uid and pdt.principal_uid = '{$principalId}'
				where  a.role_id is null or (a.role_id is not null and b.role_id is not null)";

    return $this->dbConn->dbGetAll($sql);
  }


  public function getReportDocumentTypesAllowedArray($userId, $principalId) {

    $sql = "select distinct a.uid, ifnull(pdt.description,a.description) description, code
				from   document_type a
							left join (select distinct user_id, entity_uid, role_id from user_role) b on
									a.report_role_id = b.role_id and
									b.user_id = " . mysql_real_escape_string($userId) . " and
									(b.entity_uid=" . mysql_real_escape_string($principalId) . " or b.entity_uid is null)
							left join principal_document_type pdt on a.uid = pdt.document_type_uid and pdt.principal_uid = '{$principalId}'
				where  a.role_id is null or (a.role_id is not null and b.role_id is not null)";

    return $this->dbConn->dbGetAll($sql);
  }


  public function getDocumentTypesAllowedItem($principalId, $docTypeUId) {

    $sql = "select distinct a.uid, ifnull(pdt.description,a.description) description, code
				from   document_type a
									left join principal_document_type pdt on a.uid = pdt.document_type_uid and pdt.principal_uid = '{$principalId}'
				where a.uid=" . mysql_real_escape_string($docTypeUId);

    return $this->dbConn->dbGetAll($sql);
  }


  public function getUserDocumentTypesAllowedItem($userId, $principalId, $docTypeUId) {

    $sql = "select distinct a.uid, ifnull(pdt.description,a.description) description, code
				from   document_type a
							left join (select distinct user_id, entity_uid, role_id from user_role) b on
									a.role_id = b.role_id and
									b.user_id = " . mysql_real_escape_string($userId) . " and
									(b.entity_uid=" . mysql_real_escape_string($principalId) . " or b.entity_uid is null)
							left join principal_document_type pdt on a.uid = pdt.document_type_uid and pdt.principal_uid = '{$principalId}'
				where  a.role_id is null or (a.role_id is not null and b.role_id is not null)
				and    a.uid=" . mysql_real_escape_string($docTypeUId);
				
    return $this->dbConn->dbGetAll($sql);
  }


}


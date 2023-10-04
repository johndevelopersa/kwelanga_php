<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');


class ExportDAO {

	private $dbConn;

	function __construct($dbConn) {
          $this->dbConn = $dbConn;
        }


 	//online Export Mapping
	public function getOnlineExportMappingbyType($type, $typeUid) {

          $sql="SELECT
                  *
                FROM online_export_mapping f
                WHERE type = '{$type}'
                  AND type_uid = {$typeUid}";

          return $this->dbConn->dbGetAll($sql);
	}

	//IT Dynamic  - FILE CONFIRMATION Export
	public function getExportFileLogList($principalUId, $list) {

          $sql="SELECT
                          uid,
                          status,
                          error_msg,
                          processed_date,
                          file_name,
                          line_count,
                          document_number,
                          client_document_number
                FROM file_log f
                where uid in ({$list})";

          return $this->dbConn->dbGetAll($sql);
	}

        //RIESES EDI  - FILE CONFIRMATION Export
	public function getExportRiesesFileOrders($principalUId, $list) {

          $sql="SELECT
                  f.uid,
                  f.processed_date,
                  f.file_name,
                  f.status,
                  GROUP_CONCAT(IFNULL(h.document_number,'')) as document_list
                FROM file_log f
                  left join orders_holding h on f.uid = h.file_log_uid
                WHERE f.uid in ({$list})
                  AND f.principal_uid = '{$principalUId}'
                GROUP BY f.uid" ;

          return $this->dbConn->dbGetAll($sql);

	}


        //SMOLLAN EDI  - FILE CONFIRMATION Export
	public function getExportSmollanFileOrders($principalUId, $list) {

          $sql="SELECT
                  f.uid as file_log_uid,
                  f.file_name,
                  f.processed_date,
                  h.uid as orders_holding_uid,
                  h.client_document_number,
                  h.deliver_name,
                  ROUND(SUM(d.ext_price),2) as extended_price,
                  h.depot_lookup_ref,
                  h.`document_type`,
                  p.principal_code,
                  h.principal_uid,
                  h.online_file_processing_uid
                FROM file_log f
                  INNER JOIN orders_holding h on f.uid = h.file_log_uid
                  INNER JOIN orders_holding_detail d on h.uid = d.orders_holding_uid
                  LEFT JOIN principal p on h.principal_uid = p.uid
                WHERE f.uid in ({$list})

                GROUP BY h.uid" ;

          return $this->dbConn->dbGetAll($sql);

	}
}
?>

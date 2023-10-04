<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');



class DocumentUpdateDAO {

  private $dbConn;

  function __construct($dbConn) {
    $this->dbConn = $dbConn;
  }


  //online Export Mapping
  public function getQueuedItems($statusArr, $UpdateTypeUidAllocationList = 0){

    // the principal is only known after processing, hence the left join
    $sql = "SELECT
              u.*
            FROM document_update u
                    LEFT JOIN principal p on u.principal_uid = p.uid and p.status = '".FLAG_STATUS_ACTIVE."'
            WHERE
              processed_status IN (".join(',',$statusArr).")
              " . (($UpdateTypeUidAllocationList!=0) ? "AND u.update_type_uid IN ({$UpdateTypeUidAllocationList})" : "" ) . "
            AND (p.uid is not null or (p.uid is null and ifnull(u.principal_uid,0)=0))
            LIMIT 5000"; //need to get errors(re-process) and queued items.
    $this->dbConn->dbQuery($sql);

    $arr=array(); $uidList=array();
    if ($this->dbConn->dbQueryResultRows > 0) {
      while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
        $arr[$row['uid']]=$row;
        $uidList[]=$row['uid'];
      }
    }

    if (sizeof($uidList)>0) {

      //add detail arr
      $sql = "SELECT
                d.*
              FROM  document_update_detail d
              WHERE document_update_uid in (".implode(",",$uidList).")";

      $this->dbConn->dbQuery($sql);

      while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
          $arr[$row['document_update_uid']]['detail'][] = $row;
      }

    }


    return $arr;
  }


  //online Export Mapping
  public function getExceptions(){

    $sql = "SELECT
              u.*, p.name as principal, d.name as depot
            FROM document_update u
            left join principal p on u.principal_uid = p.uid
            left join depot d on u.depot_uid = d.uid
            WHERE
              processed_status = 'E'"; //need to get errors(re-process) and queued items.
    $this->dbConn->dbQuery($sql);

    $arr=array();
    if ($this->dbConn->dbQueryResultRows > 0) {
      while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
        $arr[$row['uid']]=$row;
      }
    }

    return $arr;
  }


  public function getDocumentbyPrincipalDepotDocumentNo($principalUId, $depotUId, $documentNumber, $documentTypeUIdList="", $pastMonths = 12){

    if ($documentTypeUIdList=="") {
      $dT="AND m.document_type_uid IN (".DT_ORDINV.",".DT_UPLIFTS.",".DT_STOCKTRANSFER.",".DT_DELIVERYNOTE.",".DT_ORDINV_ZERO_PRICE.",".DT_ASN.")";
    } else {
      $dT="AND m.document_type_uid IN ({$documentTypeUIdList})";
    }

    $sql = "SELECT
              m.uid as dmUId,
              m.document_type_uid,
              m.order_sequence_no,
              h.uid as dhUId,
              h.document_status_uid,
              h.principal_store_uid,
              m.depot_uid,
              count(d.uid) as total_detail,
              h.invoice_number,
              h.customer_order_number
            FROM document_master m
            INNER JOIN document_header h on m.uid = h.document_master_uid
            INNER JOIN document_detail d on m.uid = d.document_master_uid
            WHERE m.principal_uid = {$principalUId}
              AND m.depot_uid = {$depotUId}
              AND m.document_number = '{$documentNumber}'
              AND date(m.processed_date) > (CURDATE()-INTERVAL {$pastMonths} MONTH)
              {$dT}
            GROUP BY m.uid";

    return $this->dbConn->dbGetAll($sql);

  }


  public function getDocumentDetailbyProductLineNo($dmUId, $productUId, $lineNo){

    $sql = "SELECT
              `uid`,
              `line_no`,
              `product_uid`,
              `ordered_qty`,
              `document_qty`,
              `delivered_qty`,
              `net_price`,
              `vat_rate`
            FROM document_detail d
            WHERE d.document_master_uid = {$dmUId}
              AND d.product_uid = {$productUId}
              AND d.line_no = '{$lineNo}'";

    return $this->dbConn->dbGetAll($sql);

  }

  public function getDocumentDetailbyProductUId($dmUId, $productUId){

    $sql = "SELECT
              d.`uid`,
              d.`line_no`,
              d.`product_uid`,
              d.`ordered_qty`,
              d.`document_qty`,
              d.`delivered_qty`,
              d.`net_price`,
              d.`vat_rate`
            FROM document_detail d
            WHERE d.document_master_uid = {$dmUId}
              AND d.product_uid = '{$productUId}'";              

    return $this->dbConn->dbGetAll($sql);

  }

  public function getDocumentDetail($dmUId) {
    $sql="SELECT *
    FROM   document_detail a
    WHERE  a.document_master_uid = {$dmUId}";

    $this->dbConn->dbQuery($sql);

    $arr=array();
    if ($this->dbConn->dbQueryResultRows > 0) {
      while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
        $arr[] = $row;
      }
    }

      return $arr;
    }



  public function getDocumentbyPrincipalDocumentNo($principalUId, $documentNumber, $documentTypeUId="", $documentStatusUId="", $skipDateLimitation=false){

    if ($documentTypeUId=="") {
      $dT="AND m.document_type_uid IN (".DT_ORDINV.",".DT_UPLIFTS.",".DT_STOCKTRANSFER.",".DT_DELIVERYNOTE.",".DT_ORDINV_ZERO_PRICE.",".DT_ASN.")";
    } else {
      if ($documentTypeUId==DT_STOCKTRANSFER) $dT="AND m.document_type_uid IN ({$documentTypeUId},".DT_ASN.")";
      else $dT="AND m.document_type_uid IN ({$documentTypeUId})";
    }

    if (!$skipDateLimitation) {
      $dateLimit=" AND date(m.processed_date) > (CURDATE()-INTERVAL 6 MONTH) ";
    } else $dateLimit="";

    $dStatusSQL = ($documentStatusUId=="")?(""):(" AND h.document_status_uid IN ({$documentStatusUId})");

    $sql = "SELECT
              m.uid as dmUId,
              m.document_type_uid,
              m.order_sequence_no,
              h.uid as dhUId,
              h.document_status_uid,
              h.principal_store_uid,
              h.customer_order_number,
              h.invoice_number
            FROM document_master m
            INNER JOIN document_header h on m.uid = h.document_master_uid
            WHERE m.principal_uid = {$principalUId}
              AND m.document_number = '{$documentNumber}'
              {$dateLimit}
              {$dT}
              {$dStatusSQL}";

    return $this->dbConn->dbGetAll($sql);

  }


  public function getDocumentUpdateControlArray(){

    $sql = "SELECT
              *
            FROM document_update_control";

    return $this->dbConn->dbGetAll($sql);

  }


  public function getDocumentUpdateProcessMapping($processId){

    $sql = "SELECT
              GROUP_CONCAT(m.update_type_uid) as update_type_list
            FROM document_update_process_mapping m where m.process_uid = '{$processId}'";

    return $this->dbConn->dbGetAll($sql);

  }



}

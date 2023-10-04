<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'properties/dbSettings.inc');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');

/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostExtractDAO {

  public $errorTO;
  private $dbConn;

  function __construct($dbConn) {
    $this->dbConn = $dbConn;
    $this->errorTO = new ErrorTO;
  }

  public function queueAllInvoiced($principalUIDList,
                                   $notificationUId,
                                   $inclCancelled = true,
                                   $p_dtArr = false,
                                   $p_wDSArr = false,
                                   $fromInvDate=false,
                                   $toInvDate=false,
                                   $chainUIdIn=false,
                                   $dataSource=false,
                                   $capturedBy=false,
                                   $depotUId = false,
                                   $altChainUIdIn=false,
                                   $tripTransporter=false) {

    global $errorTO, $dbConn;

    // swop default params
    $dtArr = (($p_dtArr===false)?array(DT_ORDINV, DT_ORDINV_ZERO_PRICE, DT_DELIVERYNOTE, DT_MINVOICE, DT_QUOTATION, DT_PURCHASE_ORDER):$p_dtArr);
    $wDSArr = (($p_wDSArr===false)?array(DST_INVOICED, DST_DELIVERED_POD_OK, DST_DIRTY_POD, DST_POD_SCANNED):$p_wDSArr);

    if ($inclCancelled){
      $wDSArr[] = DST_CANCELLED;
      //$wDSArr[] = DST_CANCELLED_NOT_OUR_AREA; //deliberately excluded
    }

    $dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time
    // NB !! The processed date MUST BE GMT !! as the BI trigger export file log (9) compares run dates against this and it expects GMT
    // NB !! This can only work as long as single selection radio for p3, not a checkbox on notification form when loading export confirmation !!
    // It will also keep sending file exports for files where the duplicate status / error has been unresolved since last run
    $sql = "insert into smart_event (created_date, type, type_uid, processed_date, status, status_msg, data_uid)
            select now(), '" . SE_EXTRACT . "', {$notificationUId}, null, '" . FLAG_STATUS_QUEUED . "', '', a.uid
            from   document_master a,
                   document_header b,
                   principal_store_master c
            where  a.uid = b.document_master_uid
            and    b.principal_store_uid = c.uid
            and    a.principal_uid in ({$principalUIDList})
            and    a.document_type_uid in (" . join(',', $dtArr) . ")
            and    b.document_status_uid in (" . join(',', $wDSArr) . ") ";

    if ($depotUId!==false) {
    	    if(in_array($principalUIDList, array(351,424))) {
               $sql .= " and a.depot_uid in (" . join(',', $depotUId) . ") ";
    	    } else {
    	    	$sql .= " and a.depot_uid = {$depotUId} ";
    	    }
    }
    $sql.=" and b.invoice_date > curdate() - interval 40 day ";  // reduce resultset - use invoice date as order can be invoiced several months after processed date
    if ($chainUIdIn!==false)       $sql.=" and c.principal_chain_uid in ({$chainUIdIn}) ";
    if ($altChainUIdIn!==false)    $sql.=" and c.alt_principal_chain_uid in ({$altChainUIdIn}) ";
    if ($dataSource!==false)       $sql.=" and b.data_source = '{$dataSource}' ";
    if ($capturedBy!==false)       $sql.=" and b.captured_by = '{$capturedBy}' ";   
    if ($tripTransporter!==false)  $sql.=" and b.trip_transporter_uid in (" . join(',',$tripTransporter). ") ";
    $sql.=" and    not exists (select 1 from smart_event se
                                        where se.data_uid = a.uid
                                        and se.type = '" . SE_EXTRACT . "'
                                        and se.type_uid = {$notificationUId}
                               )";
// echo "<pre>";
// echo $sql;
    $this->dbConn->dbinsQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to insert into smart_event in postExtractDAO->queueAllInvoiced " . mysqli_error($this->dbConn->connection);
    } else {
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;
  }


  public function queueAllCreditsAndDebits($principalUIDList,
                                           $notificationUId,
                                           $p_dtArr = false,
                                           $depotUId=false,
                                           $fromInvDate=false,
                                           $toInvDate=false,
                                           $sourceDataSource=false,
                                           $sourceCapturedBy=false,
                                           $altChainUIdIn=false,
                                           $chainUIdIn=false) {

    global $errorTO, $dbConn;

    // swop default params
    $dtArr = (($p_dtArr===false)?array(DT_CREDITNOTE, DT_DEBITNOTE, DT_MCREDIT_DAMAGES, DT_MCREDIT_OTHER, DT_MCREDIT_PRICING, DT_MCREDIT_PROMOTIONS, DT_MDEBIT_NOTE, DT_MCREDIT_STORE):$p_dtArr);

    $dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time
    // NB !! The processed date MUST BE GMT !! as the BI trigger export file log (9) compares run dates against this and it expects GMT
    // NB !! This can only work as long as single selection radio for p3, not a checkbox on notification form when loading export confirmation !!
    // It will also keep sending file exports for files where the duplicate status / error has been unresolved since last run
    $sql = "insert into smart_event (created_date, type, type_uid, processed_date, status, status_msg, data_uid)(
            select now(), 'EXT', {$notificationUId}, null, '" . FLAG_STATUS_QUEUED . "', '', a.uid
            from   document_master a
            inner join document_header b on a.uid = b.document_master_uid 
            inner join principal_store_master psm on b.principal_store_uid = psm.uid";

    if ($sourceDataSource!==false || $sourceCapturedBy!==false) {
    $sql .= " inner join document_master dm2 on b.source_document_number = dm2.document_number and
                                               dm2.document_type_uid in (1,13,6) and
                                               dm2.principal_uid in ({$principalUIDList}) and
                                               a.depot_uid = dm2.depot_uid
             inner join document_header dh2 on dm2.uid = dh2.document_master_uid ";
      if ($sourceDataSource!==false)    $sql.=" and dh2.data_source = '{$sourceDataSource}' ";
      if ($sourceCapturedBy!==false)    $sql.=" and dh2.captured_by = '{$sourceCapturedBy}' ";
    }

    if ($depotUId!==false) {
    	    if($principalUIDList == 351) {
               $sql .= " and a.depot_uid in (" . join(',', $depotUId) . ") ";
    	    } else {
    	    	$sql .= " and a.depot_uid = {$depotUId} ";
    	    }
      }

    $sql .= " where  a.principal_uid in ({$principalUIDList})
              and    a.document_type_uid in (".implode(",",$dtArr).") ";
    if ($chainUIdIn!==false)       $sql.=" and psm.principal_chain_uid in ({$chainUIdIn}) ";
    if ($altChainUIdIn!==false) $sql.=" and psm.alt_principal_chain_uid in ({$altChainUIdIn}) ";
    $sql.=" and b.invoice_date > curdate() - interval 31 day "; //reduce resultset - use invoice date as order can be invoiced several months after processed date

    $sql.= " and   not exists (select 1 from smart_event se
                               where se.data_uid = a.uid
                               and se.type = '" . SE_EXTRACT . "'
                               and se.type_uid = {$notificationUId}
                      ))";
 //echo "<pre>";
 //echo $sql;
 //echo "ee<BR>"; 
    $this->dbConn->dbinsQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed to insert into smart_event in postExtractDAO->queueAllInvoiced " . mysqli_error($this->dbConn->connection);
    } else {
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;
  }

  public function queueAllClaims($principalUIDList,
                                 $notificationUId,
                                 $p_dtArr = false,
                                 $fromInvDate=false,
                                 $toInvDate=false,
                                 $sourceDataSource=false,
                                 $sourceCapturedBy=false) {

    global $errorTO, $dbConn;

    // swop default params
    $dtArr = (($p_dtArr===false)?array(DT_BUYER_ORIGINATED_CREDIT_CLAIM):$p_dtArr);

    $dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time
    // NB !! The processed date MUST BE GMT !! as the BI trigger export file log (9) compares run dates against this and it expects GMT
    // NB !! This can only work as long as single selection radio for p3, not a checkbox on notification form when loading export confirmation !!
    // It will also keep sending file exports for files where the duplicate status / error has been unresolved since last run
    $sql = "insert into smart_event (created_date, type, type_uid, processed_date, status, status_msg, data_uid)
            select now(), 'EXT', {$notificationUId}, null, '" . FLAG_STATUS_QUEUED . "', '', a.uid
                    from   document_master a
                             inner join document_header b on a.uid = b.document_master_uid ";

    if ($sourceDataSource!==false || $sourceCapturedBy!==false) {
      $sql .= "inner join document_master dm2 on b.source_document_number = dm2.document_number and
                                                  dm2.document_type_uid in (1,13,6) and
                                                  dm2.principal_uid in ({$principalUIDList}) and
                                                  a.depot_uid = dm2.depot_uid
                                                  inner join document_header dh2 on dm2.uid = dh2.document_master_uid ";
      if ($sourceDataSource!==false)    $sql.=" and dh2.data_source = '{$sourceDataSource}' ";
      if ($sourceCapturedBy!==false)    $sql.=" and dh2.captured_by = '{$sourceCapturedBy}' ";
    }

    $sql .= " where  a.principal_uid in ({$principalUIDList})
              and    a.document_type_uid in (".implode(",",$dtArr).") ";

    $sql.=" and b.invoice_date > curdate() - interval 30 day "; //reduce resultset - use invoice date as order can be invoiced several months after processed date

    $sql.= " and   not exists (select 1 from smart_event se
                              where se.data_uid = a.uid
                              and se.type = '" . SE_EXTRACT . "'
                              and se.type_uid = {$notificationUId}
                      )";
                      

    $this->dbConn->dbinsQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed to insert into smart_event in postExtractDAO->queueAllInvoiced " . mysqli_error($this->dbConn->connection);
    } else {
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;
  }

  public function queueStandingOrders($principalUIDList,
                                 $notificationUId,
                                 $p_dtArr = false,
                                 $fromInvDate=false,
                                 $toInvDate=false,
                                 $sourceDataSource=false,
                                 $sourceCapturedBy=false) {

    global $errorTO, $dbConn;

    $dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time
    // NB !! The processed date MUST BE GMT !! as the BI trigger export file log (9) compares run dates against this and it expects GMT
    // NB !! This can only work as long as single selection radio for p3, not a checkbox on notification form when loading export confirmation !!
    // It will also keep sending file exports for files where the duplicate status / error has been unresolved since last run
    $sql = "insert into smart_event (created_date, 
                                     type, 
                                     type_uid, 
                                     processed_date, 
                                     status, 
                                     status_msg, 
                                     data_uid)
                                     select now(), 
                                            'EXT', 
                                            {$notificationUId}, 
                                            null, 
                                            '" . FLAG_STATUS_QUEUED . "',
                                            '', 
                                            a.uid
                                     from standing_orders so, 
                                          document_master a
                                     where so.document_master_uid = a.uid
                                     and   a.principal_uid in ({$principalUIDList})
                                     and   so.order_create_date <= curdate() 
                                     and   not exists (select 1 from smart_event se
                                                                where se.data_uid = a.uid
                                                                and se.type = '" . SE_EXTRACT . "'
                                                                and se.type_uid = {$notificationUId}
                                                                and se.status <> 'C' )";

    $this->dbConn->dbinsQuery($sql);

    if (!$this->dbConn->dbQueryResult) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed to insert into smart_event in postExtractDAO->queueStandingOrders " . mysqli_error($this->dbConn->connection);
    } else {
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
    }

    return $this->errorTO;
  }


}

?>

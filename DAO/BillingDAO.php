<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");


class BillingDAO {

  private $dbConn;


  public function __construct($dbConn) {
     $this->dbConn = $dbConn;
  }


  public function getBillingCounts($principalId, $periodStart, $periodEnd) {

  $sql = "SELECT * FROM
          (
            SELECT
                    'order' AS 'type',
                    p.uid as 'principal_id',
                    m.processed_date as 'date',
                    COUNT(DISTINCT m.uid) as 'order_count',
                    SUM(IFNULL(pg.pages, 0)) as 'pages',
                    IF(h.data_source = '".DS_EDI."', m.incoming_file,
                      IF(h.data_source = '".DS_CAPTURE."',
                        CONCAT('CAPTURE: ',u.full_name),
                          CONCAT(h.data_source,': ',captured_by))
                    ) as 'description',
                    IFNULL(ip.uid, id.uid) as 'billing_mapping_uid',
                    IF(ip.uid IS NULL, id.document_type_uid, ip.document_type_uid) as 'mapping_document_type_uid',
                    IF(ip.uid IS NULL, id.depot_uid, ip.depot_uid) as 'mapping_depot_uid',
                    IF(ip.uid IS NULL, id.data_source, ip.data_source) as 'mapping_data_source',
                    pg.page_mapping_uid
            FROM principal p
                    INNER JOIN document_master m ON p.uid = m.principal_uid
                    INNER JOIN document_header h on m.uid = h.document_master_uid
                    INNER JOIN document_type t ON m.document_type_uid = t.uid
                    INNER JOIN billing_mapping id on id.key_type = 'ORD' AND id.principal_uid = 0 #defaults
                    LEFT JOIN billing_mapping ip on ip.key_type = 'ORD' AND ip.principal_uid = p.uid
                    LEFT JOIN users u on h.captured_by = u.uid

                    #sub-select for page count, which has its own settings.
                    LEFT JOIN (
                                    SELECT
                                            m.uid as 'muid',
                                            CEILING(COUNT(d.uid) / ".BILLING_PAGE_LINES.") as 'pages',
                                            COUNT(d.uid) as 'lines',
                                            IFNULL(ip.uid, id.uid) as 'page_mapping_uid'
                                    FROM principal p
                                            INNER JOIN document_master m ON p.uid = m.principal_uid
                                            INNER JOIN document_header h on m.uid = h.document_master_uid
                                            INNER JOIN document_detail d on m.uid = d.document_master_uid
                                            INNER JOIN document_type t ON m.document_type_uid = t.uid
                                            INNER JOIN billing_mapping id on id.key_type = 'PAGE' AND id.principal_uid = 0 #defaults
                                            LEFT JOIN billing_mapping ip on ip.key_type = 'PAGE' AND ip.principal_uid = p.uid
                                    WHERE p.system_uid = " . SYS_RETAIL . "
                                            AND p.uid = " . mysql_real_escape_string($principalId) . "
                                            AND m.processed_date BETWEEN '" . mysql_real_escape_string($periodStart) . "' AND '" . mysql_real_escape_string($periodEnd) . "'

                                            #implicit includes
                                            AND (FIND_IN_SET( m.document_type_uid, IF(ip.uid IS NULL, id.document_type_uid, ip.document_type_uid)) OR IF(ip.uid IS NULL, id.document_type_uid, ip.document_type_uid) IS NULL)
                                            AND (FIND_IN_SET( m.depot_uid, IF(ip.uid IS NULL, id.depot_uid, ip.depot_uid)) OR IF(ip.uid IS NULL, id.depot_uid, ip.depot_uid) IS NULL)
                                            AND (FIND_IN_SET( h.data_source, IF(ip.uid IS NULL, id.data_source, ip.data_source)) OR IF(ip.uid IS NULL, id.data_source, ip.data_source) IS NULL)
                                    GROUP BY m.uid
                    ) as pg ON pg.muid = m.uid
            WHERE p.system_uid = " . SYS_RETAIL . "
                    AND p.uid = " . mysql_real_escape_string($principalId) . "
                    AND m.processed_date BETWEEN '" . mysql_real_escape_string($periodStart) . "' AND '" . mysql_real_escape_string($periodEnd) . "'

                    #implicit includes
                    AND (FIND_IN_SET( m.document_type_uid, IF(ip.uid IS NULL, id.document_type_uid, ip.document_type_uid)) OR IF(ip.uid IS NULL, id.document_type_uid, ip.document_type_uid) IS NULL)
                    AND (FIND_IN_SET( m.depot_uid, IF(ip.uid IS NULL, id.depot_uid, ip.depot_uid)) OR IF(ip.uid IS NULL, id.depot_uid, ip.depot_uid) IS NULL)
                    AND (FIND_IN_SET( h.data_source, IF(ip.uid IS NULL, id.data_source, ip.data_source)) OR IF(ip.uid IS NULL, id.data_source, ip.data_source) IS NULL)

            GROUP BY
                    IF(h.data_source = '" . DS_EDI . "', m.incoming_file, m.uid)

            UNION ALL

            SELECT
                    'extract' AS 'type',
                    m.principal_uid as 'principal_id',
                    DATE(e.processed_date) as 'date',
                    COUNT(e.uid) as 'extract_count',
                    0,
                    e.general_reference_1 as 'description',
                    IFNULL(ip.uid, id.uid) as 'billing_mapping_uid',
                    IF(ip.uid IS NULL, id.document_type_uid, ip.document_type_uid) as 'mapping_document_type_uid',
                    IF(ip.uid IS NULL, id.depot_uid, ip.depot_uid) as 'mapping_depot_uid',
                    IF(ip.uid IS NULL, id.data_source, ip.data_source) as 'mapping_data_source',
                    0
            FROM smart_event e
                    INNER JOIN document_master m ON e.data_uid = m.uid
                    INNER JOIN notification_recipients n ON e.type_uid = n.uid AND n.notification_uid = ".NT_DAILY_EXTRACT_CUSTOM." AND n.principal_uid = m.principal_uid
                    INNER JOIN billing_mapping id on id.key_type = 'EXT' AND id.principal_uid = 0 #defaults
                    LEFT JOIN billing_mapping ip on ip.key_type = 'EXT' AND ip.principal_uid = m.principal_uid
            WHERE e.`type` = '" . SE_EXTRACT . "'
                    AND e.`status` = '" . FLAG_STATUS_CLOSED . "' #only charge for successful, resent errors will get picked up by processed/completed date.
                    AND e.type_uid = n.uid
                    AND DATE(e.processed_date) BETWEEN '" . mysql_real_escape_string($periodStart) . "' AND '" . mysql_real_escape_string($periodEnd) . "'
                    AND m.principal_uid = " . mysql_real_escape_string($principalId) . "
                    AND e.general_reference_1 != 'CLEARED' #remove items that have been cleared by principal
                    AND e.general_reference_1 != 'CLEARED ERROR'
                    AND (FIND_IN_SET( m.document_type_uid, IF(ip.uid IS NULL, id.document_type_uid, ip.document_type_uid)) OR IF(ip.uid IS NULL, id.document_type_uid, ip.document_type_uid) IS NULL)
                    AND (FIND_IN_SET( m.depot_uid, IF(ip.uid IS NULL, id.depot_uid, ip.depot_uid)) OR IF(ip.uid IS NULL, id.depot_uid, ip.depot_uid) IS NULL)
                    #data source not enabled as of yet for extracts
                    #AND (FIND_IN_SET( h.data_source, IF(ip.uid IS NULL, id.data_source, ip.data_source)) OR IF(ip.uid IS NULL, id.data_source, ip.data_source) IS NULL)
            GROUP BY e.general_reference_1

          ) as a

          ORDER BY a.type desc, a.date asc";

    return $this->dbConn->dbGetAll($sql);
  }


  public function getBillablePrincipals() {
    
  global $errorTO, $dbConn;
  global $paramsArr, $reportSQLTO, $reportDAO, $postREPORTID, $userId, $principalId, $principalCode; 
  
  $dbConn->dbQuery("SET time_zone='+0:00'");

    $sql = "SELECT
            b.uid as 'principal_id',b.cancelled as 'cancelled',b.turnover as 'turnover',b.paper_charge as paper_charge, b.doc_charge as docs_charge,&1 as startDate, &2 as EndDate
          FROM principal b
          WHERE system_uid = " . SYS_RETAIL . "
            AND b.charge = 'Y' ";
            

    if ($postREPORTID!="") {
            $reportDAO->reportSQL_prepareStatement ($sql,
                                                    $reportSQLTO->reportRow,
                                              $reportSQLTO->database,
                                                    $postREPORTID,
                                                    $userId,
                                                    $principalId,
                                                    $principalCode,
                                                    $paramsArr);
     
    } 

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
    
  }

  public function getBillingOrders($principalUId) {
  
  global $errorTO, $dbConn;
  global $paramsArr, $reportSQLTO, $reportDAO, $postREPORTID, $userId, $principalId, $principalCode; 
  
  $dbConn->dbQuery("SET time_zone='+0:00'");
  
  $sql= "select a.document_number,dt.description as 'document_type',s.description as 'status',dh.invoice_number,dh.invoice_date,a.processed_date,a.incoming_file,p.name,d.name as 'depotName',d.charge,d.paper_charge,d.uid as 'depot_uid',&1 as startDate , &2 as endDate
         from .document_master a 
         left join depot d on a.depot_uid = d.uid
         left join document_header dh on a.uid = dh.document_master_uid
         left join principal p on p.uid = a.principal_uid
         left join status s on s.uid = dh.document_status_uid
         left join .document_type dt on dt.uid = a.document_type_uid
         where a.processed_date between (&1) and (&2)
         and d.charge = 'Y'
         and p.charge = 'Y'
         and a.document_type_uid in (1,2,6,13)  
         and a.principal_uid = ".mysql_real_escape_string($principalUId)."
         ORDER BY a.document_number";       
         
         
   if ($postREPORTID!="") {
            $reportDAO->reportSQL_prepareStatement ($sql,
                                                    $reportSQLTO->reportRow,
                                              $reportSQLTO->database,
                                                    $postREPORTID,
                                                    $userId,
                                                    $principalId,
                                                    $principalCode,
                                                    $paramsArr);
     
    } 

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

  return $arr;
  }  
  
  public function getInvoicedOrders($principalUId) {
  
  global $errorTO, $dbConn;
  global $paramsArr, $reportSQLTO, $reportDAO, $postREPORTID, $userId, $principalId, $principalCode; 
  
  $dbConn->dbQuery("SET time_zone='+0:00'");
  
    $sql= "select distinct a.document_number,d.description as 'document_type',s.description as 'status',a.processed_date,dh.invoice_number,dh.invoice_date,a.incoming_file,p.name,dp.name as 'depotName',dp.charge,dp.paper_charge,dp.uid as 'depot_uid'
     from `document_master` a
     left join `smart_event` f on a.uid = f.data_uid and f.`type` = 'EXT'
     left join `document_header` dh on a.uid = dh.document_master_uid   
     left join `document_type` d on a.document_type_uid = d.uid 
     left join `principal` p on a.principal_uid = p.uid 
     left join `status` s on dh.document_status_uid = s.uid
     left join `depot` dp on a.depot_uid = dp.uid                        
      where  dh.invoice_date between (&1) and (&2)                          
      and a.document_type_uid in (1,6,13) 
      and dh.document_status_uid in (76,77,78)
      and dp.charge = 'Y'
      and a.principal_uid = ".mysql_real_escape_string($principalUId)."";
         
   if ($postREPORTID!="") {
            $reportDAO->reportSQL_prepareStatement ($sql,
                                                    $reportSQLTO->reportRow,
                                              $reportSQLTO->database,
                                                    $postREPORTID,
                                                    $userId,
                                                    $principalId,
                                                    $principalCode,
                                                    $paramsArr);
     
    } 

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
  
  }
  public function getTurnoverByPrincipal($principalUId) {
  
  global $errorTO, $dbConn;
  global $paramsArr, $reportSQLTO, $reportDAO, $postREPORTID, $userId, $principalId, $principalCode; 
  
  $dbConn->dbQuery("SET time_zone='+0:00'");
  
  $sql= "select round(sum(b.exclusive_total),2) as turnover
        from .document_master a, .document_header b
        where a.uid = b.document_master_uid
        and   a.principal_uid = ".mysql_real_escape_string($principalUId)."
        and   a.document_type_uid in (1,4)
        and   b.invoice_date between (&1) and (&2)    
        and   b.document_status_uid in (76,77,78,81)";
         
   if ($postREPORTID!="") {
            $reportDAO->reportSQL_prepareStatement ($sql,
                                                    $reportSQLTO->reportRow,
                                              $reportSQLTO->database,
                                                    $postREPORTID,
                                                    $userId,
                                                    $principalId,
                                                    $principalCode,
                                                    $paramsArr);
     
    } 

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr[0]["turnover"];
  
  }
  
   public function getInvoicedOrdersWithCancelled($principalUId) {
  
  global $errorTO, $dbConn;
  global $paramsArr, $reportSQLTO, $reportDAO, $postREPORTID, $userId, $principalId, $principalCode; 
  
  $dbConn->dbQuery("SET time_zone='+0:00'");
  
    $sql= "select distinct a.document_number,d.description as 'document_type',s.description as 'status',a.processed_date,dh.invoice_number,dh.invoice_date,a.incoming_file,p.name,dp.name as 'depotName',dp.charge,dp.paper_charge,dp.uid as 'depot_uid'
     from `document_master` a
     left join `smart_event` f on a.uid = f.data_uid and f.`type` = 'EXT'
     left join `document_header` dh on a.uid = dh.document_master_uid   
     left join `document_type` d on a.document_type_uid = d.uid 
     left join `principal` p on a.principal_uid = p.uid 
     left join `status` s on dh.document_status_uid = s.uid
     left join `depot` dp on a.depot_uid = dp.uid                        
      where  dh.invoice_date between (&1) and (&2)                          
      and a.document_type_uid in (1,6,13) 
      and dh.document_status_uid in (47,76,77,78)
      and dp.charge = 'Y'
      and a.principal_uid = ".mysql_real_escape_string($principalUId)."";
         
   if ($postREPORTID!="") {
            $reportDAO->reportSQL_prepareStatement ($sql,
                                                    $reportSQLTO->reportRow,
                                              $reportSQLTO->database,
                                                    $postREPORTID,
                                                    $userId,
                                                    $principalId,
                                                    $principalCode,
                                                    $paramsArr);
     
    } 

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
  
  }
  public function calculatePages($lineCount,$depotUId){
   
    $pageCount = 0;
    $j = 1;
    $i=0;
    $depotArr = array(2,3,5,6);
    if (in_array($depotUId, $depotArr)) {
      
      while($i<=130){      
        if ($lineCount >= $i && $lineCount <= $i+13) {
          $pageCount = $j;
          
        }
        $j+=1;
        $i+=13;
        
      } 
    } else {
        while($i<=100){      
        if ($lineCount >= $i && $lineCount <= $i+10)
          $pageCount = $j;
        
        $i+=10;$j+=1;
        } 
      }
     
		return $pageCount;
  
  }
  public function getPages( $principalUId,$docNum) {
  
  global $errorTO, $dbConn;
  global $paramsArr, $reportSQLTO, $reportDAO, $postREPORTID, $userId, $principalId, $principalCode; 
  
  $dbConn->dbQuery("SET time_zone='+0:00'");
  
  $sql= "select dh.pages as page_count
         from `document_master` dm          
           left join `document_header` dh on dm.uid = dh.document_master_uid   
           left join `document_type` d on dm.document_type_uid = d.uid 
           left join `principal` e on dm.principal_uid = e.uid 
           left join `status` s on dh.document_status_uid = s.uid
           left join `depot` dp on dm.depot_uid = dp.uid                        
        where  dh.invoice_date between (&1) and (&2)                  
        and dp.charge = 'Y'
        and dm.principal_uid = ".mysql_real_escape_string($principalUId)."
        and dm.document_number = ".mysql_real_escape_string($docNum)."
        group by dm.principal_uid";
         
   if ($postREPORTID!="") {
            $reportDAO->reportSQL_prepareStatement ($sql,
                                                    $reportSQLTO->reportRow,
                                              $reportSQLTO->database,
                                                    $postREPORTID,
                                                    $userId,
                                                    $principalId,
                                                    $principalCode,
                                                    $paramsArr);
     
    } 

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr[0]["page_count"];
  
  }
  
  public function getDocNum($principalUId) {
  global $errorTO, $dbConn;
  global $paramsArr, $reportSQLTO, $reportDAO, $postREPORTID, $userId, $principalId, $principalCode; 
  
  $dbConn->dbQuery("SET time_zone='+0:00'");
  
  $sql= "select distinct a.document_number,d.description as 'document_type',s.description as 'status',a.processed_date,dh.invoice_number,dh.invoice_date,a.incoming_file,p.name,dp.name as 'depotName',dp.charge,dp.paper_charge,dp.uid as 'depot_uid',&1 as startDate , &2 as endDate
         from `document_master` a
           left join `smart_event` f on a.uid = f.data_uid and f.`type` = 'EXT'
           left join `document_header` dh on a.uid = dh.document_master_uid   
           left join `document_type` d on a.document_type_uid = d.uid 
           left join `principal` p on a.principal_uid = p.uid 
           left join `status` s on dh.document_status_uid = s.uid
           left join `depot` dp on a.depot_uid = dp.uid 
          left join document_detail dt on dt.document_master_uid = a.uid                       
          where  dh.invoice_date between (&1) and (&2)             
          and dh.document_status_uid in (47,76,77,78)
          and dp.charge = 'Y' 
          and a.principal_uid = ".mysql_real_escape_string($principalUId)."
          ORDER BY a.document_number";
         
   if ($postREPORTID!="") {
            $reportDAO->reportSQL_prepareStatement ($sql,
                                                    $reportSQLTO->reportRow,
                                              $reportSQLTO->database,
                                                    $postREPORTID,
                                                    $userId,
                                                    $principalId,
                                                    $principalCode,
                                                    $paramsArr);
     
    } 

		$this->dbConn->dbQuery($sql);
   
		$arr =array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[]= $row;
			}
		}

		return $arr;
  
  }
  public function countPages($principalUId, $docNum) {
  global $errorTO, $dbConn;
  global $paramsArr, $reportSQLTO, $reportDAO, $postREPORTID, $userId, $principalId, $principalCode; 
  
  $dbConn->dbQuery("SET time_zone='+0:00'");
  
  $sql= "SELECT count( a.document_number) as page_count
         from `document_master` a
           left join `smart_event` f on a.uid = f.data_uid and f.`type` = 'EXT'
           left join `document_header` b on a.uid = b.document_master_uid   
           left join `document_type` d on a.document_type_uid = d.uid 
           left join `principal` e on a.principal_uid = e.uid 
           left join `status` s on b.document_status_uid = s.uid
           left join `depot` dp on a.depot_uid = dp.uid 
			  left join document_detail dt on dt.document_master_uid = a.uid                       
        where  b.invoice_date between (&1) and (&2)           
        and dp.charge = 'Y'
        and a.principal_uid = ".mysql_real_escape_string($principalUId)."
        and a.document_number = ".mysql_real_escape_string($docNum)."";
         
   if ($postREPORTID!="") {
            $reportDAO->reportSQL_prepareStatement ($sql,
                                                    $reportSQLTO->reportRow,
                                              $reportSQLTO->database,
                                                    $postREPORTID,
                                                    $userId,
                                                    $principalId,
                                                    $principalCode,
                                                    $paramsArr);
     
    } 

		$this->dbConn->dbQuery($sql);
   
		$arr =array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[]= $row;
			}
		}

		return $arr[0]["page_count"];
  
  }
  
  public function getAllowedBillingPrincipalUsers() {

    return array(
                  11,  # Alan Argall
                  679, # Susan Harding
                  366, # Mark Willman
                  954, # Joelle Iwunze
                );

  }
  
  public function getBillingArray() 
  {
                                   
    global $errorTO, $dbConn;
    global $paramsArr, $reportSQLTO, $reportDAO, $postREPORTID, $userId, $principalId, $principalCode; 
    
    $dbConn->dbQuery("SET time_zone='+0:00'");
    
		$sql="select *
          from .billing_temp bt
          LEFT JOIN .principal p
            on p.uid = bt.Principal ";
            
  
    if ($postREPORTID!="") {
            $reportDAO->reportSQL_prepareStatement ($sql,
                                                    $reportSQLTO->reportRow,
                                              $reportSQLTO->database,
                                                    $postREPORTID,
                                                    $userId,
                                                    $principalId,
                                                    $principalCode,
                                                    $paramsArr);
     
    } 

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

}


?>
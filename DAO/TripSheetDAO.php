<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostStockDAO.php');

class TripSheetDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// **************************************************************************************************************************************************** 
  public function getTripSheetInvoices($depId, $tripNo) {
  	
       $sql = "SELECT dm.depot_uid,
                      d.name AS 'Warehouse',
                      dm.principal_uid,
                      p.name as 'Principal', 
                      dm.document_number, 
                      dh.principal_store_uid,
                      psm.deliver_name, 
                      dt.tripsheet_number,
                      dt.tripsheet_date,
                      dt.i_dispatched,
                      dt.t_dispatched,
                      td.document_verified_for_dispatch,
                      th.verified_for_dispatch,
                      t.name,
                      dh.cases
               FROM tripsheet_header th
               INNER JOIN tripsheet_detail td ON td.tripsheet_master_uid = th.uid AND td.removed_flag = 'N'
               LEFT JOIN  document_tripsheet dt ON dt.tripsheet_number = th.tripsheet_number 
                                                AND dt.document_master_uid = td.document_master_uid
                                                AND dt.tripsheet_removed_by IS NULL
               INNER JOIN document_master dm ON dm.uid = dt.document_master_uid
               INNER JOIN document_header dh ON dm.uid = dh.document_master_uid
               INNER JOIN depot d on d.uid = dm.depot_uid
               INNER JOIN principal p on p.uid = dm.principal_uid
               INNER JOIN principal_store_master psm on psm.uid = dh.principal_store_uid
               INNER JOIN .transporter t ON t.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $depId) . " AND t.uid = dt.transporter_id
               WHERE th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tripNo) . "'
               ORDER BY psm.deliver_name;";

       $uPList = $this->dbConn->dbGetAll($sql);

       return $uPList ;

  }
// **************************************************************************************************************************************************** 
  public function getTripSheetTransporter2($depotId, $stat='A', $tSel='') {
  	
  	if(trim($stat) == '') {
       $statLine = "AND tp.`status` = 'A'";
  	} else {
       $statLine = "AND tp.`status` = '" . mysqli_real_escape_string($this->dbConn->connection, $stat)  . "'";
  	}

  	if(trim($tSel) == '') {
       $tSelLine = " ";
  	} else {
       $tSelLine = "AND tp.`name` like '%" . mysqli_real_escape_string($this->dbConn->connection, $tSel)  . "%'";
  	}

    $sql = "SELECT tp.uid,tp.name, tp.`status`
            FROM   transporter tp
            WHERE  tp.depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId)  . "' "
            . $statLine . " "
            . $tSelLine . "
            ORDER BY tp.name";

    $ts = $this->dbConn->dbGetAll($sql);

    return $ts;

  }
// **************************************************************************************************************************************************** 
  public function checkTripSheetDispatch($prinId, $docno,  $tripNo ) {
     
       $sql = "SELECT dm.document_number, 
                      dt.tripsheet_number, 
                      dt.i_dispatched,
                      dt.t_dispatched,
                      td.document_verified_for_dispatch
               FROM .document_master dm
               INNER JOIN .tripsheet_detail td ON td.document_master_uid = dm.uid AND td.removed_flag = 'N'
               INNER JOIN .tripsheet_header th ON th.uid = td.tripsheet_master_uid
               INNER JOIN .document_tripsheet dt on dm.uid = dt.document_master_uid  AND dt.tripsheet_removed_by IS NULL
               WHERE dm.principal_uid     = '" . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "'
               AND   dt.tripsheet_number  = '" . mysqli_real_escape_string($this->dbConn->connection, $tripNo) . "'
               AND   dm.document_number   = '" . mysqli_real_escape_string($this->dbConn->connection, $docno)  . "';";

       $cts = $this->dbConn->dbGetAll($sql);

       return $cts;
  }
// **************************************************************************************************************************************************** 
  public function countTripSheetDispatched($depId, $tripNo ) {
     
       $sql = "SELECT count(td.document_verified_for_dispatch) AS 'docCnt'
               FROM .document_master dm
               INNER JOIN .tripsheet_detail td ON td.document_master_uid = dm.uid AND  td.removed_flag = 'N'
               INNER JOIN .tripsheet_header th ON th.uid = td.tripsheet_master_uid
               INNER JOIN .document_tripsheet dt on dm.uid = dt.document_master_uid AND dt.tripsheet_removed_by IS NULL
               WHERE th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tripNo)  . "'
               AND   dm.depot_uid        = "  . mysqli_real_escape_string($this->dbConn->connection, $depId)   . "
               AND   td.document_verified_for_dispatch = 'P';";
               
       $cntts = $this->dbConn->dbGetAll($sql);

       return $cntts;
  }
//***************************************************************************************************************************************************************************
  public function pendTripSheetDispatch($iPrinId, $iDocNo, $itripNo, $userUId ) {
        $sql = "UPDATE document_master dm
                INNER JOIN .tripsheet_detail td ON td.document_master_uid = dm.uid AND  td.removed_flag = 'N'
                INNER JOIN .tripsheet_header th ON th.uid = td.tripsheet_master_uid
                INNER JOIN .document_tripsheet dt on dm.uid = dt.document_master_uid AND dt.tripsheet_removed_by IS NULL               
                                                                                         SET dt.document_verified_for_dispatch = 'P',
                                                                                             td.document_verified_for_dispatch = 'P',
                                                                                             dt.dispatched_by = " . mysqli_real_escape_string($this->dbConn->connection, $userUId) . "
               WHERE dm.principal_uid     = '" . mysqli_real_escape_string($this->dbConn->connection, $iPrinId) . "'
               AND   dt.tripsheet_number  = '" . mysqli_real_escape_string($this->dbConn->connection, $itripNo) . "'
               AND   dm.document_number   = '" . mysqli_real_escape_string($this->dbConn->connection, $iDocNo)  . "';";
 
               $this->errorTO = $this->dbConn->processPosting($sql,"");
                    
               if($this->errorTO->type == 'S') {
                    $this->dbConn->dbQuery("commit");
                    return $this->errorTO;     	
               } else {
                      echo "<br>";
                      echo $sql;
                      echo "<br>";
                      return $this->errorTO;  
               } 
  }
//***************************************************************************************************************************************************************************
  public function saveTripSheetDispatch($depotId, $tripNo, $parm, $tripSheetDespatch, $userId ) {

              // Update Tripsheet Detail
              
              $sql = "SELECT td.uid AS 'tdUid',
                             dm.principal_uid,
                             th.tripsheet_number,
                             th.depot_uid, 
                             td.document_master_uid, 
                             td.document_verified_for_dispatch, 
                             d.allow_pending_dispatch,
                             dd.product_uid,
                             dd.document_qty
                      FROM .tripsheet_header th
                      LEFT  JOIN depot d ON d.uid = th.depot_uid 
                      LEFT  JOIN tripsheet_detail td ON td.tripsheet_master_uid = th.uid
                      LEFT  JOIN document_master dm ON dm.uid = td.document_master_uid
                      LEFT  JOIN document_tripsheet dt ON dt.document_master_uid = dm.uid AND dt.tripsheet_removed_by IS NULL
                      LEFT  JOIN document_detail  dd ON dd.document_master_uid = td.document_master_uid
                      WHERE th.depot_uid        = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "'
                      AND   th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tripNo)  . "';" ;             
              
             $tsArr = $this->dbConn->dbGetAll($sql);
             
             $sql = "UPDATE tripsheet_header th
                      LEFT  JOIN tripsheet_detail td ON td.tripsheet_master_uid = th.uid
                      LEFT  JOIN document_master dm ON dm.uid = td.document_master_uid
                      LEFT  JOIN document_tripsheet dt ON dt.document_master_uid = dm.uid AND dt.tripsheet_removed_by IS NULL
                      LEFT  JOIN document_header dh ON dm.uid = dh.document_master_uid SET th.verified_for_dispatch = 'Y',
                                                                                           th.dispatch_number = ' " . mysqli_real_escape_string($this->dbConn->connection, $tripSheetDespatch) . " ',
                                                                                           th.dispatched_by = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . ",
                                                                                           th.dispatched_date_time = NOW(),
                                                                                           dh.pending_dispatch = if('" . $tsArr[0]['allow_pending_dispatch'] . "' = 'Y','N',dh.pending_dispatch),
                                                                                           dt.document_verified_for_dispatch = 'Y',
                                                                                           td.document_verified_for_dispatch = 'Y',
                                                                                           dt.dispatch_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tripSheetDespatch) . "'
                      WHERE th.depot_uid        = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "'
                      AND   th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tripNo) ."'"  ;

             $this->errorTO = $this->dbConn->processPosting($sql,"");

              if($this->errorTO->type <> 'S') {
                    return $this->errorTO;
              }
 
             if(count($tsArr) > 0 && $tsArr[0]['allow_pending_dispatch'] == 'Y') { 
                   		foreach($tsArr as $row) {
                            $PostStockDAO = new PostStockDAO($this->dbConn);
                            $this->errorTO = $PostStockDAO->removeStockQtyFromPending($row['principal_uid'], 
                                                      $row['depot_uid'], 
                                                      $row['product_uid'], 
                                                      $row['document_qty'],
                                                      $row['document_master_uid']);
                                                      

                            if($this->errorTO->type <> 'S') {
                            	echo "LOOP";
                                     return $this->errorTO;
                            }                         
                      }
             }
             	            
             if($this->errorTO->type == 'S') {
       	          $this->dbConn->dbQuery("commit");
                  return $this->errorTO;     	
             } else {
       	         echo $sql;
                 return $this->errorTO;  
             } 
  }
//***************************************************************************************************************************************************************************
  public function getLoadSheetDetails($depotId, $tripNo, $orderBy) {

              $sql = "SELECT dm.principal_uid,
                             p.name AS 'Principal',
                             p.short_name as 'PSN',
                             dt.tripsheet_number,
                             dt.tripsheet_date, 
                             dm.document_number, 
                             psm.deliver_name,
                             dh.cases,
                             dh.exclusive_total,
                             pp.uid as 'prodId' ,
                             pp.product_code, 
                             pp.product_description,
                             dd.line_no,
                             dd.document_qty,
                             t.name AS 'Transporter',
                             d.name AS 'Warehouse',
                             d.depot_group
                      FROM .document_master dm
                      INNER JOIN document_header dh ON dm.uid = dh.document_master_uid
                      INNER JOIN document_detail dd ON dd.document_master_uid = dm.uid
                      INNER JOIN principal_store_master psm ON dh.principal_store_uid = psm.uid
                      INNER JOIN document_tripsheet dt ON dt.document_master_uid = dm.uid
                      INNER JOIN principal_product pp ON pp.uid = dd.product_uid
                      INNER JOIN principal p ON dm.principal_uid = p.uid
                      INNER JOIN .transporter t ON dt.transporter_id = t.uid
                      INNER JOIN .depot d ON d.uid = dm.depot_uid
                      WHERE d.uid = " . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "
                      AND   dt.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tripNo)  . "' 
                      ORDER BY " .$orderBy . ";";                      

              $loadd = $this->dbConn->dbGetAll($sql);

              return $loadd;
  }
//***************************************************************************************************************************************************************************  
  public function getTripSheetInvoicesNew($depotId, 
                                          $principalId,
                                          $postPRUID, 
                                          $postWAREA,
                                          $postWDOCNO,
                                          $postWINVDATE,
                                          $postWSTORE,
                                          $postWNDD) {	
  	$prinSql = $areaSql = $docnoSql = $invDSql = $storeSql = $nddSql = '';
  	
    if (mysqli_real_escape_string($this->dbConn->connection, $postPRUID) <> '0') {
        $prinSql = "AND pr.name like '%" . mysqli_real_escape_string($this->dbConn->connection, $postPRUID) . "%' ";
    } 

    if (mysqli_real_escape_string($this->dbConn->connection, $postWAREA) <> '0') {
        $areaSql = "AND wda.wh_area like '%" . mysqli_real_escape_string($this->dbConn->connection, $postWAREA) . "%' ";
    } 

    if (mysqli_real_escape_string($this->dbConn->connection, $postWDOCNO) <> '0') {
        $docnoSql = "AND dm.document_number like '%" . mysqli_real_escape_string($this->dbConn->connection, $postWDOCNO) . "%' ";
    } 

    if (mysqli_real_escape_string($this->dbConn->connection, $postWINVDATE) <> '0') {
        $invDSql = "AND dh.invoice_date >= '" . mysqli_real_escape_string($this->dbConn->connection, $postWINVDATE) . "' ";
    } 

    if (mysqli_real_escape_string($this->dbConn->connection, $postWSTORE) <> '0') {
        $storeSql = "AND psm.deliver_name like '%" . mysqli_real_escape_string($this->dbConn->connection, $postWSTORE) . "%' ";
    } 
    
    if (mysqli_real_escape_string($this->dbConn->connection, $postWNDD) <> '0') {
        $nddSql= "AND day.short_name like '%" . mysqli_real_escape_string($this->dbConn->connection, $postWNDD) . "%' ";
    }
    
    $srtorder= "GROUP BY pr.name, a.description, dh.invoice_date, psm.deliver_name, dm.document_type_uid, dm.document_number";
    
    $sql = "SELECT dm.uid as 'dm_uid',
                   pr.name as 'Principal',
                   dm.principal_uid as 'PrincipalID',
                   TRIM(LEADING '0' FROM dm.document_number) as 'Docno',
                   psm.deliver_name as 'Store',
                   sum(dd.document_qty) as 'Cases',
                   sum(dd.document_qty * pp.weight) as 'Weight',
                   s.description as 'Dstatus',
                   dt.description as 'Dtype',
                   dh.invoice_date AS 'Invoice Date',
                   a.description AS 'Area',                   
                   wda.wh_area AS 'W_Area',
                   day.short_name as 'NDD',
                   dh.document_status_uid AS 'Redeliver'
            FROM   document_master dm
            INNER JOIN document_header dh on dm.uid = dh.document_master_uid
            INNER JOIN document_detail dd on dm.uid = dd.document_master_uid
            INNER JOIN depot d ON dm.depot_uid = d.uid
            INNER JOIN principal_store_master psm on psm.uid = dh.principal_store_uid
            LEFT JOIN  area a on psm.area_uid = a.uid
            LEFT JOIN  principal_warehouse_store_link psl ON psl.principal_store_master_uid = psm.uid
            LEFT JOIN  warehouse_store_master wsm ON psl.warehouse_store_master_uid = wsm.uid
            LEFT JOIN  warehouse_area wda ON wda.uid = wsm.delivery_area
            left JOIN  day on day.uid = wsm.ndd
            INNER JOIN principal_product pp ON dd.product_uid = pp.uid
            INNER JOIN document_type dt on dt.uid = dm.document_type_uid
            INNER JOIN principal pr on pr.uid = dm.principal_uid
            INNER JOIN `status` s on dh.document_status_uid = s.uid
            WHERE  dm.depot_uid = $depotId
            AND    dm.document_type_uid in (". DT_ORDINV            . ","
                                             . DT_DELIVERYNOTE      . ","
                                             . DT_ORDINV_ZERO_PRICE . ","
                                             . DT_UPLIFTS           . ")
            AND if(d.waiting_dispatch = 'Y', dh.document_status_uid IN (" . DST_WAITING_DISPATCH . ", 
                                                                        " . DST_RE_DELIVERY . "), 
                                             dh.document_status_uid IN (" . DST_INVOICED . ")) 
            AND    dh.invoice_date > d.tripsheet_switch_on_date
            AND    dh.on_a_tripsheet_number <= 0 
            ". $prinSql
             . $areaSql
             . $docnoSql
             . $invDSql
             . $storeSql
             . $nddSql
             . $srtorder ;
    $rs = $this->dbConn->dbGetAll($sql);

    return $rs;

  }
//***************************************************************************************************************************************************************************  
  public function getDocumentsForPickingNew($principalId, 
                                            $depotId,
                                            $grArea,
                                            $postPRUID, 
                                            $postWAREA,
                                            $postWDOCNO,
                                            $postWINVDATE,
                                            $postWSTORE,
                                            $postWNDD) {

    if (mysqli_real_escape_string($this->dbConn->connection, $postPRUID) <> '0') {
        $prinSql = "AND p.name like '%" . mysqli_real_escape_string($this->dbConn->connection, $postPRUID) . "%' ";
    } 

    if (mysqli_real_escape_string($this->dbConn->connection, $postWAREA) <> '0') {
        $areaSql = "AND wda.wh_description like '%" . mysqli_real_escape_string($this->dbConn->connection, $postWAREA) . "%' ";
    } 

    if (mysqli_real_escape_string($this->dbConn->connection, $postWDOCNO) <> '0') {
        $docnoSql = "AND dm.document_number like '%" . mysqli_real_escape_string($this->dbConn->connection, $postWDOCNO) . "%' ";
    } 

    if (mysqli_real_escape_string($this->dbConn->connection, $postWINVDATE) <> '0') {
        $invDSql = "AND dh.invoice_date >= '" . mysqli_real_escape_string($this->dbConn->connection, $postWINVDATE) . "' ";
    } 

    if (mysqli_real_escape_string($this->dbConn->connection, $postWSTORE) <> '0') {
        $storeSql = "AND psm.deliver_name like '%" . mysqli_real_escape_string($this->dbConn->connection, $postWSTORE) . "%' ";
    } 
    
    if (mysqli_real_escape_string($this->dbConn->connection, $postWNDD) <> '0') {
        $nddSql= "AND day.short_name like '%" . mysqli_real_escape_string($this->dbConn->connection, $postWNDD) . "%' ";
    }

    if (mysqli_real_escape_string($this->dbConn->connection, $grArea) <> '0') {
        $grAreaSql = "AND wa.wh_area like '%" . mysqli_real_escape_string($this->dbConn->connection, $grArea) . "%' ";
    }
    
    $srtorder= "ORDER BY dm.document_number";

    $sql = "SELECT  p.name AS 'Principal',
                    d.name As 'Depot',
                    dm.uid AS 'docuid',
                    wa.uid AS 'wa.uid', 
                    wda.wh_description AS 'wa_name', 
                    dm.document_number,
                    dh.order_date, 
                    dh.due_delivery_date,
                    psm.deliver_name, 
                    dh.cases, 
                    dh.document_status_uid,
                    dm.document_type_uid,
                    day.short_name,
                    wa.wh_area
            FROM        document_master dm 
            INNER JOIN  document_header dh ON dm.uid = dh.document_master_uid 
            INNER JOIN  principal_store_master psm ON dh.principal_store_uid = psm.uid
            INNER JOIN  depot d ON d.uid= dm.depot_uid
            INNER JOIN  principal p on dm.principal_uid = p.uid
            LEFT  JOIN  warehouse_store_master wsm ON wsm.link = psm.warehouse_link
            LEFT JOIN   warehouse_delivery_area wda ON wsm.wh_delivery_area = wda.uid
            LEFT JOIN   warehouse_area wa ON wa.uid = wda.wh_greater_area
            LEFT JOIN day day ON wda.wh_ndd = day.uid         
            WHERE       dm.depot_uid = ". mysqli_real_escape_string($this->dbConn->connection, $depotId)."
            AND         dh.document_status_uid in (" . DST_UNACCEPTED . "," . DST_ACCEPTED . ")
            AND         dm.document_type_uid in (" . DT_ORDINV . "," . DT_DELIVERYNOTE . "," . DT_ORDINV_ZERO_PRICE . ")
            ". $prinSql
             . $areaSql
             . $docnoSql
             . $invDSql
             . $storeSql
             . $nddSql
             . $grAreaSql
             . $srtorder . "; ";
             
//            echo "<pre>";
//            echo $sql;

            $ts = $this->dbConn->dbGetAll($sql);

            return $ts;

  } 
//***************************************************************************************************************************************************************************  
  public function getUserWarehouses($userId) {
  	
  	
    $sql = "SELECT DISTINCT(d.uid) as 'warehouse_uid', 
                   d.name AS 'warehouse'
            FROM user_principal_depot upd
            INNER JOIN depot d ON upd.depot_id = d.uid
            WHERE upd.user_id = ". mysqli_real_escape_string($this->dbConn->connection, $userId). "; " ;

    $depl = $this->dbConn->dbGetAll($sql);

    return $depl;

  }

//***************************************************************************************************************************************************************************  
  public function getUserPrincipals($userId, $depotId) {
  	
  	
    $sql = "SELECT DISTINCT(upd.principal_id) as 'prin_uid'
            FROM user_principal_depot upd
            WHERE upd.user_id  = ". mysqli_real_escape_string($this->dbConn->connection, $userId)  . "
            AND   upd.depot_id = ". mysqli_real_escape_string($this->dbConn->connection, $depotId) . " ; " ;

    $depl = $this->dbConn->dbGetAll($sql);

    return $depl;

  }
//***************************************************************************************************************************************************************************  
  public function addTransporter($tname, $depotId) {

    $sql = "INSERT INTO `transporter` (`name`,
                                       `depot_uid`) 
            VALUES ('". mysqli_real_escape_string($this->dbConn->connection, $tname)    . "',
                    '". mysqli_real_escape_string($this->dbConn->connection, $depotId)  . "');";

            $this->errorTO = $this->dbConn->processPosting($sql,"");
              
            if($this->errorTO->type == 'S') {
                  $this->dbConn->dbQuery("commit");
                  return $this->errorTO;     	
            } else {
                  echo $sql;
                  return $this->errorTO;  
           } 
  }
//***************************************************************************************************************************************************************************  
  public function checkTransporter($tname, $depotId) {
      
    $sql = "SELECT t.name 
            FROM .transporter t
            WHERE t.depot_uid = '". mysqli_real_escape_string($this->dbConn->connection, $depotId)  . "'
            AND  t.name LIKE '%". mysqli_real_escape_string($this->dbConn->connection, $tname)    . "%'
            limit 1;";
        	
    $tNam = $this->dbConn->dbGetAll($sql);

    return $tNam;
  }
//***************************************************************************************************************************************************************************  
  public function checkWarehouseUser($userUId){
  	
    $sql = "SELECT u.uid,
                   u.category
            FROM users u
            WHERE u.uid = ". trim($userUId) . ";";
            
    $wUser = $this->dbConn->dbGetAll($sql);

    return $wUser;  	
  	
  	
  }
//***************************************************************************************************************************************************************************  
  public function updateTransporter($tUid, $tName, $nStatus){
  	
       $sql = "UPDATE `transporter` SET `name`   = '" . mysqli_real_escape_string($this->dbConn->connection, $tName) ."',
                                        `status` = '" . mysqli_real_escape_string($this->dbConn->connection, $nStatus) ."'   
               WHERE  `uid`= " . mysqli_real_escape_string($this->dbConn->connection, $tUid) .";";
       
       $this->errorTO = $this->dbConn->processPosting($sql,"");
       
       if($this->errorTO->type == 'S') {
             $this->dbConn->dbQuery("commit");
             return $this->errorTO;     	
       } else {
             echo $sql;
             return $this->errorTO;  
       } 
  }
//***************************************************************************************************************************************************************************  

  public function getTripSheetDetails($depotId, $tname, $selTsNo, $iDate) {
  	
    if(trim($selTsNo) == "") {
        $selTsNo = "";
    } else {    
        $selTsNo = "AND  dt.tripsheet_number = '". mysqli_real_escape_string($this->dbConn->connection, $selTsNo) . "' ";
    }
  	
    if(trim($selTsName) == "") {
        $selTsName = "";
    } else {    
        $selTsName = "AND  t.name LIKE '% " . mysqli_real_escape_string($this->dbConn->connection, $tname) . "%' ";
    } 

    if(trim($selTsName) == "") {
        $selTsName = "";
    } else {    
        $selTsName = "AND  dt.tripsheet_date >= '" . mysqli_real_escape_string($this->dbConn->connection, $iDate) . "%' ";
    }

        $sql = "SELECT dt.tripsheet_number, 
                       dm.document_number,
                       t.uid,t.name, 
                       psm.deliver_name, 
                       dh.customer_order_number, 
                       sum(dh.cases) AS 'Cases', 
                       dt.tripsheet_date
                FROM .document_tripsheet dt
                INNER JOIN .document_master dm ON dm.uid = dt.document_master_uid
                INNER JOIN .document_header dh ON dm.uid = dh.document_master_uid
                INNER JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
                INNER JOIN .transporter t ON t.uid = dt.transporter_id
                WHERE dm.depot_uid = ". mysqli_real_escape_string($this->dbConn->connection, $depotId) ." 
                AND dt.tripsheet_removed_by IS NULL "
                . $selTsNo   . " "
                . $selTsName . " "
                . $selTsDate . " 
                GROUP BY dt.tripsheet_number
                ORDER BY t.name, dt.tripsheet_number;";
 
        	
    $tDet = $this->dbConn->dbGetAll($sql);

    return $tDet;  		
  }
// ****************************************************************************************************************************************************   
  public function gettripSheetReason() {
  	
    $sql = "select uid,
                   code,
                   description
            from .reason_code 
            where reason_code.code = 'TS'";

    $rc = $this->dbConn->dbGetAll($sql);

    return $rc;

  }  
//***************************************************************************************************************************************************************************    
   public function getDocumentsOnTripsheet($depotId, $tsnumber, $remFlag) {
   	
   	if($remFlag == "A") {
         $srem = "";
   	} else {
         $srem = "AND    td.removed_flag = '". mysqli_real_escape_string($this->dbConn->connection, $remFlag) ."'";
   	}
   	
   	// Clean TS Number
   	
   	if(strpos($tsnumber,'-')) {
         $tsNum =  ltrim(substr(mysqli_real_escape_string($this->dbConn->connection, $tsnumber),strpos($tsnumber,'-')+1,10),'0');
   	} else {
         $tsNum = ltrim(mysqli_real_escape_string($this->dbConn->connection, $tsnumber),'0');
   	}
    $sql = "SELECT  dm.uid as 'dm_uid',
                    p.name as 'Principal',
                    p.short_name as 'shortname',
                    substr(dm.document_number,3,6) as 'Docno',
                    psm.deliver_name as 'Store',
                    dh.cases as 'Cases',
                    dh.invoice_total AS 'total',
                    th.tripsheet_number,
                    t.name,
                    dh.document_status_uid,
                    dh.buyer_document_status_uid AS 'Redeliver',
                    dh.decimal_updated,
                    td.removed_flag,
                    td.t_dispatched,
                    td.i_dispatched,
                    th.tripsheet_date,
                    th.verified_for_dispatch
            FROM .tripsheet_header th
            INNER JOIN .tripsheet_detail td ON th.uid = td.tripsheet_master_uid
            INNER JOIN  document_master dm  ON dm.uid = td.document_master_uid
            INNER JOIN  document_header dh  ON dm.uid = dh.document_master_uid
            INNER JOIN  transporter t       ON t.uid  = th.transporter_id
            INNER JOIN  principal_store_master psm ON dh.principal_store_uid = psm.uid
            INNER JOIN  principal p ON dm.principal_uid = p.uid
            WHERE  th.depot_uid = '". mysqli_real_escape_string($this->dbConn->connection, $depotId)."'
            AND    th.tripsheet_number = '". mysqli_real_escape_string($this->dbConn->connection, $tsNum)."'
            " . $srem . ";";
            
            // echo $sql;

    $rc = $this->dbConn->dbGetAll($sql);

    return $rc;

  }   
//***************************************************************************************************************************************************************************  
    public function removeInvoiceFromTripSheet($list, $postreason, $userUId, $tsNum, $dispatchFlag, $depotId) {
    if($dispatchFlag == 'Y') { $rdFlag = "dh.buyer_document_status_uid = ". DT_REDELIVERY_INVOICE . ","; }	else { $rdFlag = "";}
    $sql = "update document_header dh set dh.on_a_tripsheet_number = 0,
                                          dh.tripsheet_number             = null,
                                          dh.tripsheet_date               = '0000-00-00',
                                          dh.trip_transporter_uid         = null,
                                          " . $rdFlag . "
                                          dh.tripsheet_created_by         = null
            where dh.document_master_uid =" . mysqli_real_escape_string($this->dbConn->connection, $list) . ";";
            
            $this->errorTO = $this->dbConn->processPosting($sql,"");
            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                  echo "<pre>";
                  echo $sql;
                  print_r($this->errorTO);                  
                  $this->errorTO->description="Error removing document (pt001) : ".$this->errorTO->description;
                  return $this->errorTO;
            }

    $sql = "update document_tripsheet ds set ds.tripsheet_removed_by = " . mysqli_real_escape_string($this->dbConn->connection, $userUId) . ",
                                      ds.tripsheet_removed_date      = '" . date("Y-m-d") . "',
                                      ds.reason                      = " . mysqli_real_escape_string($this->dbConn->connection, $postreason) . "                                       
           where ds.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $list) . ";";
            $this->errorTO = $this->dbConn->processPosting($sql,"");

            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                  echo "<pre>";
                  echo $sql;
                  $this->errorTO->description="Error removing document (pt002) : ".$this->errorTO->description;
                  return $this->errorTO;
            }

   $sql = "UPDATE tripsheet_detail td
           INNER JOIN tripsheet_header th on th.uid = td.tripsheet_master_uid and th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tsNum) . "'
                                    SET td.removed_from_tripsheet = " . mysqli_real_escape_string($this->dbConn->connection, $userUId) . ",
                                        td.removed_date           = '" . date("Y-m-d") . "',
                                        td.removed_reason         = " . mysqli_real_escape_string($this->dbConn->connection, $postreason) . " ,                                      
                                        td.removed_flag = 'Y'
           WHERE td.document_master_uid = " . mysqli_real_escape_string($this->dbConn->connection, $list)     . "
           AND   th.depot_uid           = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "'
           AND   th.tripsheet_number    = '" . mysqli_real_escape_string($this->dbConn->connection, $tsNum)   . "' ;";
           
            $this->errorTO = $this->dbConn->processPosting($sql,"");

            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                  echo "<pre>";
                  echo $sql;
                  $this->errorTO->description="Error removing document (pt003) : ".$this->errorTO->description;
                  return $this->errorTO;
            }
            
    if($dispatchFlag == 'Y') {
         $sql = "UPDATE tripsheet_header th
                 INNER JOIN .tripsheet_detail td ON th.uid = td.tripsheet_master_uid
                 LEFT  JOIN .load_scanner_log lsl ON th.uid = lsl.trip_sheet_header_uid
                 LEFT  JOIN .load_scanner_log_detail sld ON sld.load_scanner_log_uid = lsl.uid 
                                                        AND td.document_master_uid = sld.document_uid
                 LEFT  JOIN .document_detail dd ON dd.document_master_uid = sld.document_uid 
                                                AND dd.product_uid = sld.product_uid 
                                                SET dd.document_qty = sld.document_qty, 
                                                dd.delivered_qty = sld.document_qty                                      
                 WHERE th.depot_uid           = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "'
                 AND   th.tripsheet_number    = '" . mysqli_real_escape_string($this->dbConn->connection, $tsNum)   . "'
                 AND   dd.document_master_uid IS NOT NULL;" ;
    	
            $this->errorTO = $this->dbConn->processPosting($sql,"");

         if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                  echo "<pre>";
                  echo $sql;
                  $this->errorTO->description="Error Resetting Document (pt004) : ".$this->errorTO->description;
                  return $this->errorTO;
         }
    }
    
    return $this->errorTO;
  }  

//***************************************************************************************************************************************************************************  

  public function getTripSheetsNew($depotId, $posttransporter, $selTsNo, $selDate) {
  	
        if(trim($selTsNo) == "0") {
             $selTsVar = "";
        } else {    
             $selTsVar = "AND  dt.tripsheet_number = '". mysqli_real_escape_string($this->dbConn->connection, $selTsNo) . "' ";
        }  	
  	
        if(trim($posttransporter) == "0") {
             $selTrnVar = "";
        } else {
             $selTrnVar = "AND dh.trip_transporter_uid = " . mysqli_real_escape_string($this->dbConn->connection, $posttransporter) . " "; 
        }

        if(trim($selDate) == "0") {
             $selDatVar = "";
        } else {
             $selDatVar = "AND dh.tripsheet_date >= " . mysqli_real_escape_string($this->dbConn->connection, $selDate) . " "; 
        }  	

    $sql = "select dm.uid AS 'dm_uid',
                   t.name AS 'transporter', 
                   dh.tripsheet_number, 
                   dh.tripsheet_date, 
                   count(dm.document_number) AS 'Documents'
            from .document_master dm, document_header dh, .transporter t
            where dm.uid = dh.document_master_uid
            and   dh.trip_transporter_uid = t.uid
            and   dm.depot_uid = ". mysqli_real_escape_string($this->dbConn->connection, $depotId) . "
            " . $selTsVar  . "
            " . $selTrnVar . "
            " . $selDatVar . "
            group by dh.tripsheet_number
            order by dh.tripsheet_date DESC, dh.tripsheet_number DESC";

    $rs = $this->dbConn->dbGetAll($sql);

    return $rs;

  }
//***************************************************************************************************************************************************************************  

  public function getTripSheets($depotId, $posttransporter, $subdays) {

    $sql = "select dm.uid AS 'dm_uid',
                   t.name AS 'transporter', 
                   dh.tripsheet_number, 
                   dh.tripsheet_date, 
                   count(dm.document_number) AS 'Documents'
            from .document_master dm, document_header dh, .transporter t
            where dm.uid = dh.document_master_uid
            and   dh.trip_transporter_uid = t.uid
            and   dm.depot_uid = ". mysqli_real_escape_string($this->dbConn->connection, $depotId) . "
            and   dh.trip_transporter_uid = " . mysqli_real_escape_string($this->dbConn->connection, $posttransporter) . "
            and   " . $subdays . "
            group by dh.tripsheet_number
            order by dh.tripsheet_date DESC, dh.tripsheet_number DESC";
            
//            echo "<pre>";
//            echo $sql;

    $rs = $this->dbConn->dbGetAll($sql);

    return $rs;

  }
//***************************************************************************************************************************************************************************  
  public function getTripSheetTransporter($depotId) {

    $sql = "SELECT tp.uid,tp.name
            FROM   transporter tp
            WHERE  tp.depot_uid = $depotId
            ORDER BY tp.name";

    $ts = $this->dbConn->dbGetAll($sql);

    return $ts;

  }
//***************************************************************************************************************************************************************************  
  public function getCurrentTransporter($depotId, $tripNumber) {

    $sql = "SELECT th.uid AS 'TsUid',
                   th.tripsheet_number, 
                   th.tripsheet_date, 
                   t.uid, 
                   t.name as 'Transporter', 
                   COUNT(td.document_master_uid) as 'NoDocs'
            FROM  tripsheet_header th
            INNER JOIN .tripsheet_detail td ON td.tripsheet_master_uid = th.uid
            LEFT JOIN .transporter t ON t.uid = th.transporter_id
            WHERE th.depot_uid        =  " . mysqli_real_escape_string($this->dbConn->connection, $depotId)     . "
            AND   th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tripNumber)  . "'
            GROUP BY td.tripsheet_master_uid ;";

    $ts = $this->dbConn->dbGetAll($sql);

    return $ts;

  }
//***************************************************************************************************************************************************************************  
  public function updateCurrentTransporterNew($depotId, $tripNno , $transporter, $userUId) {

    $rsql = "UPDATE tripsheet_header th
             INNER JOIN tripsheet_detail td ON td.tripsheet_master_uid = th.uid
             LEFT JOIN  transporter t ON t.uid = th.transporter_id SET th.transporter_id = " . mysqli_real_escape_string($this->dbConn->connection, $transporter)     . "
             WHERE th.depot_uid        =  " . mysqli_real_escape_string($this->dbConn->connection, $depotId)     . "
             AND   th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tripNno)  . "';";
 
    $this->errorTO = $this->dbConn->processPosting($rsql ,"");
    
    if($this->errorTO->type == 'S') {
            $this->dbConn->dbQuery("commit");
            return $this->errorTO;     	
     } else {
     	      $this->errorTO->description="Error Updating Trip Sheet (pt005) : ".$this->errorTO->description;
            echo "<br>";
            echo $rsql;
            echo "<br>";
            return $this->errorTO;  
     }
  	
  }
//***************************************************************************************************************************************************************************  
  public function setTripsheetHeaderNew($wh, $tripSheetNumber, $transporterID, $tripSheetDate, $tripSheetUser) {

        $sql = "INSERT INTO tripsheet_header (tripsheet_header.depot_uid,
                                              tripsheet_header.tripsheet_number,
                                              tripsheet_header.tripsheet_date,
                                              tripsheet_header.tripsheet_created_by,
                                              tripsheet_header.transporter_id)
                VALUES  (" .  mysqli_real_escape_string($this->dbConn->connection, $wh) . ",
                         "  . mysqli_real_escape_string($this->dbConn->connection, $tripSheetNumber) . ",
                         '" . mysqli_real_escape_string($this->dbConn->connection, $tripSheetDate)   . "',
                         "  . mysqli_real_escape_string($this->dbConn->connection, $tripSheetUser)   . ",
                         "  . mysqli_real_escape_string($this->dbConn->connection, $transporterID)   . ")";
                                         
         $this->errorTO = $this->dbConn->processPosting($sql,"");
        
         if ($this->errorTO->type=FLAG_ERRORTO_SUCCESS) {
         	    $lastThUid = $this->dbConn->dbGetLastInsertId();         	
              $this->errorTO->description="setTripsheetDetails :  ". $this->errorTO->description;
              $this->errorTO->identifier = $lastThUid;
              $this->dbConn->dbQuery("commit");              
              return $this->errorTO;
           }
           return $this->errorTO;
  }
//***************************************************************************************************************************************************************************  
  public function setTripsheetDetailNew($tmUid, $dmUid) {

        $sql = "INSERT INTO tripsheet_detail (tripsheet_detail.tripsheet_master_uid,
                            tripsheet_detail.document_master_uid)
                VALUES  (" .  mysqli_real_escape_string($this->dbConn->connection, $tmUid) . ",
                         "  . mysqli_real_escape_string($this->dbConn->connection, $dmUid) . ")";
 
        $this->errorTO = $this->dbConn->processPosting($sql,"");
            
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description="setTripsheetDetails : ".$this->errorTO->description;
            return $this->errorTO;
        }
        
        $sql="UPDATE document_header SET on_a_tripsheet_number = '1'
              WHERE  document_master_uid in("  . mysqli_real_escape_string($this->dbConn->connection, $dmUid) . ");" ;
          
        $this->errorTO = $this->dbConn->processPosting($sql,"");
    
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description="UpdateTripsheetDetailsFailed : ".$this->errorTO->description;
            return $this->errorTO;
        }
        $this->dbConn->dbQuery("commit");
        return $this->errorTO;
  }
//***************************************************************************************************************************************************************************  
  public function getLoadSheetDetailsVersion4($depotId, $tripNo, $orderBy) {

              $sql = "SELECT dm.principal_uid,
                             p.name AS 'Principal',
                             p.short_name as 'PSN',
                             th.tripsheet_number,
                             th.tripsheet_date, 
                             dm.document_number, 
                             psm.deliver_name,
                             dh.cases,
                             dh.exclusive_total,
                             pp.uid as 'prodId' ,
                             pp.product_code, 
                             pp.product_description,
                             dd.line_no,
                             dd.document_qty,
                             t.name AS 'Transporter',
                             d.name AS 'Warehouse',
                             d.depot_group
                      FROM tripsheet_header th
                      INNER JOIN tripsheet_detail td ON td.tripsheet_master_uid = th.uid
                      INNER JOIN .document_master dm ON dm.uid = td.document_master_uid
                      INNER JOIN document_header dh  ON dm.uid = dh.document_master_uid
                      INNER JOIN document_detail dd  ON dd.document_master_uid = dm.uid
                      INNER JOIN principal_store_master psm ON dh.principal_store_uid = psm.uid
                      INNER JOIN principal_product pp ON pp.uid = dd.product_uid
                      INNER JOIN principal p ON dm.principal_uid = p.uid
                      INNER JOIN .transporter t ON th.transporter_id = t.uid
                      INNER JOIN .depot d ON d.uid = dm.depot_uid
                      WHERE d.uid = " . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "
                      AND   th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tripNo)  . "'
                      AND   td.removed_flag = 'N'
                      ORDER BY " .$orderBy . ";";                      

              $loadd = $this->dbConn->dbGetAll($sql);

              return $loadd;
  }
//***************************************************************************************************************************************************************************  
  public function insertIntoTripsheetControl($tsNum, $dmUid) {
        
        $sql = "INSERT INTO `kwelanga_live`.`tripsheet_control` (`document_master_uid`,
                                                                 `tripsheet_number`) 
                VALUES ("   . mysqli_real_escape_string($this->dbConn->connection, $dmUid) . ",
                         "  . mysqli_real_escape_string($this->dbConn->connection, $tsNum) . ");"	;
            $this->errorTO = $this->dbConn->processPosting($sql,"");

            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                  $this->errorTO->description="Error Updating document (pt010) : ".$this->errorTO->description;
                  return $this->errorTO;
            } 
            return $this->errorTO; 	
  }  

//***************************************************************************************************************************************************************************  
  public function updateRemoveTripsheetControl($tmUid, $dmUid) {
  	
  	
  	$sql = "UPDATE tripsheet_control tc SET `removed` = 'Y',
  	                                        `date_time` = NOW()
            WHERE  tc.document_master_uid =  "  . mysqli_real_escape_string($this->dbConn->connection, $tmUid) . "
            AND    tc.tripsheet_number    =  "  . mysqli_real_escape_string($this->dbConn->connection, $dmUid) . ";";
  	        
            $this->errorTO = $this->dbConn->processPosting($sql,"");

            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                  echo "<pre>";
                  echo $sql;
                  $this->errorTO->description="Error Updating document (pt010) : ".$this->errorTO->description;
                  return $this->errorTO;
            }
            $this->dbConn->dbQuery("commit");
            return $this->errorTO;
  }  

//***************************************************************************************************************************************************************************  
  public function userAccessToTripsheet($tsnumber, $userId, $whId) {
  	
       $sql = "SELECT *
               FROM .transporter t
               INNER JOIN .tripsheet_header th ON th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tsnumber) . "' AND th.transporter_id IN (t.uid)
               WHERE t.depot_uid IN ( " . mysqli_real_escape_string($this->dbConn->connection, $whId) . ")
               AND t.uid in (SELECT d.transporter_uid
                             FROM .user_depot_transporter d
                             WHERE d.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $whId)   . "
                             AND   d.user_uid  = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . ")
               AND   t.`status` = 'A'";
       
               $aTs = $this->dbConn->dbGetAll($sql);
               return $aTs; 
  }
//***************************************************************************************************************************************************************************  
  public function getTripSheetHeaderStatus($tsNumber, $depotId ) {
  	      
       $sql = "SELECT th.t_dispatched
               FROM .tripsheet_header th
               WHERE th.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $depotId)   . "
               AND   th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tsNumber)   . "';";
  	
               $aTs = $this->dbConn->dbGetAll($sql);
               return $chkts;   	
 	
  }
//***************************************************************************************************************************************************************************  
} 
?>
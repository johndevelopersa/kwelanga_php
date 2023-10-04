<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class LoadPlanningDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
//***************************************************************************************************************************************************************************  
  public function getDocumentsForLoading($principalId, 
                                            $depotId,
                                            $grArea,
                                            $postPRUID, 
                                            $postWAREA,
                                            $postWDOCNO,
                                            $postWINVDATE,
                                            $postWSTORE,
                                            $postWNDD,
                                            $selList) {

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
                    wa.wh_area,
                    '0' AS 'check'
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
            AND         dm.uid NOT IN (" . mysqli_real_escape_string($this->dbConn->connection, $selList) . ")
            ". $prinSql
             . $areaSql
             . $docnoSql
             . $invDSql
             . $storeSql
             . $nddSql
             . $grAreaSql
             . $srtorder . "; ";
             
//          echo "<pre>";
//          echo $sql;
          
            $tsnew = $this->dbConn->dbGetAll($sql);
            
            $sql2 = "SELECT  p.name AS 'Principal',
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
                    wa.wh_area,
                    '1' AS 'check'
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
            AND         dm.uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $selList) . ")";        
            
            $tsel = $this->dbConn->dbGetAll($sql2);
            
            $ts = array_merge($tsel, $tsnew );
            
            return $ts;

  } 
//***************************************************************************************************************************************************************************  
   public function getDocumentsForManaging($selList) {
  	 
            $sql = "SELECT  p.name AS 'Principal',
                    d.name As 'Depot',
                    dm.uid AS 'docuid',
                    wa.uid AS 'wa.uid', 
                    wda.wh_description AS 'wa_name', 
                    dm.document_number,
                    dm.amended,
                    dh.order_date, 
                    dh.invoice_total,
                    dh.document_status_uid AS 'StatUid',
                    psm.deliver_name, 
                    dh.cases, 
                    dh.customer_order_number,
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
            WHERE       dm.uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $selList) . ")";        
            
            $tsel = $this->dbConn->dbGetAll($sql);
            
            return $tsel;
   }
//***************************************************************************************************************************************************************************  
   public function getDocumentsToAmend($selList) {

        $sql = "SELECT  p.name AS 'Principal',
                        d.name As 'Depot',
                        dm.uid AS 'docuid',
                        dm.document_number,
                        dm.amended,
                        dh.order_date, 
                        dh.invoice_total,
                        dh.cases, 
                        dh.customer_order_number,
                        psm.deliver_name,
                        day.short_name,
                        wa.wh_area,
                        dd.uid AS 'dduid',
                        dd.product_uid,
                        pp.product_code,
                        pp.product_description,
                        pg.outercasing_gtin,
                        dd.ordered_qty,
                        s.available
                FROM        document_master dm 
                INNER JOIN  document_header dh ON dm.uid = dh.document_master_uid
                INNER JOIN  document_detail dd ON dm.uid = dd.document_master_uid 
                INNER JOIN  principal_store_master psm ON dh.principal_store_uid = psm.uid
                INNER JOIN  principal_product pp ON pp.uid = dd.product_uid
                LEFT JOIN   principal_product_depot_gtin pg ON pg.uid = pp.uid
                LEFT JOIN   stock s ON s.depot_id = dm.depot_uid AND s.principal_product_uid = dd.product_uid
                INNER JOIN  depot d ON d.uid= dm.depot_uid
                INNER JOIN  principal p on dm.principal_uid = p.uid
                LEFT JOIN   warehouse_store_master wsm ON wsm.link = psm.warehouse_link
                LEFT JOIN   warehouse_delivery_area wda ON wsm.wh_delivery_area = wda.uid
                LEFT JOIN   warehouse_area wa ON wa.uid = wda.wh_greater_area
                LEFT JOIN   day day ON wda.wh_ndd = day.uid         
                
                WHERE  dm.uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $selList) . ")";        
            
                $aDoc = $this->dbConn->dbGetAll($sql);
            
                return $aDoc;
   }
//***************************************************************************************************************************************************************************  
   public function saveDocumentAmendments($ddUid, $aAmount, $userUid, $loadSheetNumber) {

       $sql = "UPDATE document_master dm
               INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid
               INNER JOIN .document_detail dd ON dd.document_master_uid = dm.uid SET dm.amended  = " . mysqli_real_escape_string($this->dbConn->connection, $loadSheetNumber) . ",
                                                                          dm.authorised_by_uid   = " . mysqli_real_escape_string($this->dbConn->connection, $userUid) . ",
                                                                          dd.buyer_delivered_qty = if(dd.buyer_delivered_qty is NULL or dd.buyer_delivered_qty = 0  ,dd.ordered_qty,dd.buyer_delivered_qty),
                                                                          dd.ordered_qty = " . mysqli_real_escape_string($this->dbConn->connection, $aAmount) . "
               WHERE dd.uid = " . mysqli_real_escape_string($this->dbConn->connection, $ddUid);
               
//               echo "<pre>";
//               echo $sql;
               $this->errorTO = $this->dbConn->processPosting($sql,"");
       
               if($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                     $this->dbConn->dbQuery("commit");
               }
               
               return $this->errorTO;
   }
//***************************************************************************************************************************************************************************  
  public function setLoadSheetDetails($dmList, $transporterID, $tripSheetNumber, $tripSheetDate, $tripSheetUser, $dmuid) {
  	
    $sql="UPDATE document_header
          SET    tripsheet_number     = "  . mysqli_real_escape_string($this->dbConn->connection, $tripSheetNumber) . ",
                 trip_transporter_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $transporterID) . ",
                 tripsheet_date       = '" . mysqli_real_escape_string($this->dbConn->connection, $tripSheetDate) . "',
                 tripsheet_created_by = "  . mysqli_real_escape_string($this->dbConn->connection, $tripSheetUser) . ",
                 on_a_tripsheet_number       = abs(on_a_tripsheet_number) + 1,
                 document_status_uid  = " . DST_INPICK . "
          WHERE  document_master_uid in("  . mysqli_real_escape_string($this->dbConn->connection, $dmList) . ");";
          
    $this->errorTO = $this->dbConn->processPosting($sql,"");

    if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
      $this->errorTO->description="UpdateTripsheetDetailsFailed : ".$this->errorTO->description;
      return $this->errorTO;
    }
    
    foreach ($dmuid as $r) {
       $sql="INSERT INTO `document_tripsheet` (`document_master_uid`,
                                               `tripsheet_number`, 
                                               `tripsheet_date`, 
                                               `transporter_id`,
                                               `tripsheet_created_by`) 
             VALUES ( "  . mysqli_real_escape_string($this->dbConn->connection, $r) . ", 
                      "  . mysqli_real_escape_string($this->dbConn->connection, $tripSheetNumber) . ", 
                      '" . mysqli_real_escape_string($this->dbConn->connection, $tripSheetDate) . "',
                      "  . mysqli_real_escape_string($this->dbConn->connection, $transporterID) . " , 
                      "  . mysqli_real_escape_string($this->dbConn->connection, $tripSheetUser) . ");";
 
        $this->errorTO = $this->dbConn->processPosting($sql,"");
    }   
    
       if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->description="setTripsheetDetails : ".$this->errorTO->description;
          return $this->errorTO;
       }
       return $this->errorTO;
  }
//***************************************************************************************************************************************************************************  
  
} 
?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class PalletControlDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// **************************************************************************************************************************************************** 
  public function getTransporterDetails($searchVal, $depId) {
  	
       $sql = "SELECT *
               FROM transporter t
               WHERE depot_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $depId) . "
               AND   t.name LIKE '%" . trim(mysqli_real_escape_string($this->dbConn->connection, $searchVal)) . "%' 
               AND   t.`status` = 'A';";
               
       $loadd = $this->dbConn->dbGetAll($sql);

       return $loadd;
  }

// **************************************************************************************************************************************************** 
   public function selectUserWarehouse($uId, $prin) {

       $sql = "SELECT d.uid AS 'WhUid',
                      d.name AS 'Warehouse'
               FROM .user_principal_depot upd
               LEFT JOIN .depot d ON d.uid = upd.depot_id 
               WHERE upd.user_id = " . mysqli_real_escape_string($this->dbConn->connection, $uId)  . "
               AND   upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $prin);

       $whDetails = $this->dbConn->dbGetAll($sql);

       return $whDetails;
  }
//***************************************************************************************************************************************************************************  
  public function updatePalletBalances($principalId, 
                                       $depotId,
                                       $chepPalUid,
                                       $chepPal, 
                                       $recpType, 
                                       $recpUid,
                                       $recQty,
                                       $retQty) {
                                       	
                                       	
       $sql = "SELECT *
               FROM  pallet_balance pb
               WHERE pb.`type` = '" . mysqli_real_escape_string($this->dbConn->connection, substr($recpType,0,1)) . "'
               AND   pb.pallet_account_uid = " . mysqli_real_escape_string($this->dbConn->connection, $recpUid);
               
       $palletRecs = $this->dbConn->dbGetAll($sql);               
               
       if(count($palletRecs) == 0) {
            $sql = "INSERT IGNORE INTO pallet_balance(pallet_balance.`type`,
                                                      pallet_balance.pallet_account_uid)
                    VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, substr($recpType,0,1)) . "',
                             " . mysqli_real_escape_string($this->dbConn->connection, $recpUid) . ");" ;    	

                     $this->errorTO = $this->dbConn->processPosting($sql,"");
                     
                     if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) { 
                     	   return $this->errorTO;
                     }
       }  
               
       $sql = "UPDATE  pallet_balance pb SET pb.pallets_received = pb.pallets_received - " . mysqli_real_escape_string($this->dbConn->connection, $recQty) . ",
                                             pb.pallets_returned = pb.pallets_returned + " . mysqli_real_escape_string($this->dbConn->connection, $retQty) . ",
                                             pb.pallet_balance   = pb.opening + pb.pallets_received + pb.pallets_returned
               WHERE pb.`type` = '" . mysqli_real_escape_string($this->dbConn->connection, substr($recpType,0,1)) . "' 
               AND   pb.pallet_account_uid = " . mysqli_real_escape_string($this->dbConn->connection, $recpUid);

               $this->errorTO = $this->dbConn->processPosting($sql,"");
                     
               if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) { 
                   return $this->errorTO;
               }
               
       $sql = "SELECT *
               FROM .stock s
               WHERE s.principal_id          = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               AND   s.depot_id              = " . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "
               AND   s.principal_product_uid = " . mysqli_real_escape_string($this->dbConn->connection, $chepPalUid)  ;

       $palletStkRec = $this->dbConn->dbGetAll($sql);               
               
       if(count($palletStkRec) == 0) {
       	
            $sql = "INSERT IGNORE INTO stock (stock.principal_id, 
                                              stock.depot_id, 
                                              stock.principal_product_uid, 
                                              stock.stock_item)
                    VALUES (" . mysqli_real_escape_string($this->dbConn->connection, $principalId) .",
       	                    " . mysqli_real_escape_string($this->dbConn->connection, $depotId)     . ",
       	                    " . mysqli_real_escape_string($this->dbConn->connection, $chepPalUid)  . ",
       	                    '" . mysqli_real_escape_string($this->dbConn->connection, $chepPal)  . "');";
       	                    
            $this->errorTO = $this->dbConn->processPosting($sql,"");
                     
            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) { 
                   return $this->errorTO;
            }
            $this->dbConn->dbQuery("commit"); 
             
       }

       $usql = "UPDATE stock s SET s.delivered      = if(s.delivered IS NULL, " . mysqli_real_escape_string($this->dbConn->connection, $recQty) .",
                                                         s.delivered        - " . mysqli_real_escape_string($this->dbConn->connection, $recQty) . "),
                                  s.returns_cancel  = if(s.returns_cancel IS NULL," . mysqli_real_escape_string($this->dbConn->connection, $retQty) . ",
                                                         s.returns_cancel + " . mysqli_real_escape_string($this->dbConn->connection, $retQty) . "),
                                  s.closing         = if(s.closing IS NULL,0- " . mysqli_real_escape_string($this->dbConn->connection, $recQty) . " + " . mysqli_real_escape_string($this->dbConn->connection, $retQty) . ",
                                                         s.closing - " . mysqli_real_escape_string($this->dbConn->connection, $recQty) . " + " . mysqli_real_escape_string($this->dbConn->connection, $retQty) . ")
               WHERE s.principal_id           = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               AND   s.depot_id               = " . mysqli_real_escape_string($this->dbConn->connection, $depotId)     . "
               AND   s.principal_product_uid  = " . mysqli_real_escape_string($this->dbConn->connection, $chepPalUid)  ;
               
       $this->errorTO = $this->dbConn->processPosting($usql,"");
       $this->dbConn->dbQuery("commit");
                     
       return $this->errorTO;
  }
//***************************************************************************************************************************************************************************  
  public function getPalletDocument($docmastId) {
  	
        $sql = "SELECT dm.uid,
                       dm.principal_uid,
                       p.name AS 'Principal',
                       dm.depot_uid,
                       dm.document_number,
                       dm.document_type_uid,
                       d.name AS 'Depot',
                       SUBSTR(dh.buyer_account_reference,1,1) AS 'RecTyp',
                       trim(substr(dh.buyer_account_reference,3,15)) AS 'RecUid',
                       t.name AS 'TranspName',
                       psm.deliver_name AS 'RecName',
                       dh.invoice_date,
                       dh.debrief_comment AS 'comment',
                       dh.additional_details AS 'TripNos',
                       pp.uid AS 'ProdUid',
                       pp.product_code,
                       pp.product_description,
                       dd.document_qty,
                       pb.pallets_received,
                       pb.pallets_returned,
                       pb.pallet_balance
                FROM .document_master dm
                INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid
                INNER JOIN .document_detail dd ON dd.document_master_uid = dm.uid
                INNER JOIN .principal p ON dm.principal_uid = p.uid
                INNER JOIN .depot d ON d.uid = dm.depot_uid
                INNER JOIN .principal_product pp ON pp.uid = dd.product_uid
                LEFT JOIN  .transporter t ON t.uid = trim(substr(dh.buyer_account_reference,3,15))
                LEFT JOIN  .principal_store_master psm ON psm.uid = trim(substr(dh.buyer_account_reference,3,15))
                LEFT JOIN  .pallet_balance pb ON pb.`type` = SUBSTR(dh.buyer_account_reference,1,1) 
                              AND pb.pallet_account_uid = trim(substr(dh.buyer_account_reference,3,15)) 
                WHERE dm.uid = " . mysqli_real_escape_string($this->dbConn->connection, $docmastId) . ";";	

        $palDetails = $this->dbConn->dbGetAll($sql);

        return $palDetails;  	
  }
// ********************************************************************************************************************************      
   public function getPalletWarehouse($depotUid) {
   	
   	   $sql1 = "SELECT d.pallet_depot, d.pallet_principal
   	           FROM depot d 
   	           WHERE d.uid = " . mysqli_real_escape_string($this->dbConn->connection, $depotUid) . ";";	
   	           
//          echo $sql1;
   	         
       $palDep = $this->dbConn->dbGetAll($sql1);

       return $palDep;  	
   }
//***************************************************************************************************************************************************************************  



} 
?>
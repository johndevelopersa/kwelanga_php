<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class TransportCostDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  // **************************************************************************************************************************************************** 

  public function getActiveTransporters() {
 	  
      $sql = "select t.uid, t.name, t.address1 
              from transporter t, .transport_costs tc 
              where  t.uid = tc.transporter 
              and  curdate() between tc.start_date and tc.end_date 
              limit 1 " ;

      $gTPR = $this->dbConn->dbGetAll($sql);
      return $gTPR;
  
  }

  // **************************************************************************************************************************************************** 
  public function dropTempTable($userUId) {
  	
  	$bldrate = "DROP TABLE IF EXISTS temp_rate_" . $userUId . ";   ";

    $this->errorTO = $this->dbConn->dbQuery($bldrate);
    $this->dbConn->dbQuery("commit"); 
  	
  	$result = $this->errorTO;
    return $result;
  	
  }	
  // **************************************************************************************************************************************************** 
  public function createTempTable($userUId) {
  	
         $bldrate = "CREATE TABLE temp_rate_" . $userUId . " (`Field1` VARCHAR(8) NULL,
                                                              `Field2` VARCHAR(100) NULL,
                                                              `Field3` INT(5) NULL,
                                                              `Field4` DECIMAL(7,2) NULL,
                                                              `Field5` DECIMAL(10,2) NULL,
                                                              `Field6` INT(2) NULL,
                                                              `Field7` INT(2) NULL,
                                                              `Field8` DECIMAL(7,2) NULL,
                                                              `Field9` DECIMAL(7,2) NULL,
                                                              `Field10` DECIMAL(7,2) NULL,
                                                              `Field11` DECIMAL(7,2) NULL,
                                                              `Field12` DECIMAL(7,2) NULL,
                                                              `Field13` DECIMAL(7,2) NULL,
                                                              `Field14` DECIMAL(7,2) NULL,
                                                              `Field15` DECIMAL(7,2) NULL,
                                                              `Field16` DECIMAL(7,2) NULL,
                                                              `Field17` DECIMAL(7,2) NULL,
                                                              `Sort` TINYINT(1) NULL);";
         $this->errorTO = $this->dbConn->dbQuery($bldrate);
         $this->dbConn->dbQuery("commit");  
  	
  	     $result = $this->errorTO;
  	     
  	     return $result;

  }
  // **************************************************************************************************************************************************** 
  public function insertPeriodTransactions($principal, $start, $end, $userUId, $stTransporter) {

    $sql = "insert into temp_rate_" . $userUId . " (Field1, Field2,Field3,Field4,Field5,Field6,Field7,Sort)(
            select dm.document_number, 
                   psm.deliver_name, 
                   sum(dd.document_qty) as 'Cases', 
                   round(sum(dd.document_qty * pp.weight),2) as 'Total Mass', 
                   round(sum(dd.extended_price),2) as 'Value', 
                   pst.from_area_uid as 'From Area', 
                   pst.to_area_uid as 'To Area',
                   '1'
            from document_master dm, 
                 document_header dh, 
                 document_detail dd, 
                 principal_product pp, 
                 principal_store_master psm
            left Join principal_store_transporter pst on pst.principal_store_uid = psm.uid and pst.transporter_uid = " . mysqli_real_escape_string($this->dbConn->connection, $stTransporter) . "     
            where dm.uid = dh.document_master_uid 
            and   dm.uid = dd.document_master_uid 
            and   dh.principal_store_uid = psm.uid 
            and   dd.product_uid = pp.uid 
            and   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . "  
            and   dm.document_type_uid in (1, 6, 13) 
            and   dh.document_status_uid in (76,77,78)
            and   dh.invoice_date between '2019-08-01' and '2019-08-31'
            group by dm.document_number);";
     
            $this->errorTO = $this->dbConn->processPosting($sql,"");
            
                             $this->dbConn->dbQuery("commit");
 
            if($this->errorTO->type == 'S' && $this->errorTO->object[records] > 0) {
                 return $this->errorTO->type;     	
            } else {
                 return "F"	;
            }                 
  }
// **************************************************************************************************************************************************** 
  public function minimumChargeCalc($transporter, $userUId) {
	
	// Minimum Charge Calc

     $sql = "update temp_rate_" . $userUId . "  t 
             left join .transport_areas ta on t.Field7 = ta.uid
             left join  transport_costs tc on tc.area_definition = ta.area_definition_uid
                                           and tc.charge_type = 2 
                                           and tc.transporter = " . mysqli_real_escape_string($this->dbConn->connection, $transporter) . "
                                           set t.Field8 = tc.rate;";

     $this->errorTO = $this->dbConn->processPosting($sql,"");
     $this->dbConn->dbQuery("commit");
             
     if($this->errorTO->type == 'S' && $this->errorTO->object[changed] > 0) {
            return $this->errorTO->type;     	
     } else {
            return "F"	;
     }     
  }
// **************************************************************************************************************************************************** 
  public function additionalKGCharge($transporter, $userUId) {
	
	// additional KG Rate

     $sql = "update temp_rate_" . $userUId . "  t 
             left join .transport_costs tc on tc.from_area    = t.Field6 
                                           and tc.`to area`   = t.Field7 
                                           and tc.charge_type = 4
                                           and tc.transporter = " . mysqli_real_escape_string($this->dbConn->connection, $transporter) . "
                                           set t.Field9       = tc.rate,
                                               t.Field10      = (if(t.Field4 - tc.base_quantity > 0, 
                                                                 round(tc.rate * (t.Field4 - tc.base_quantity),2),
                                                                 0))
     where 1;"  ;

     $this->errorTO = $this->dbConn->processPosting($sql,"");
     $this->dbConn->dbQuery("commit");
             
     if($this->errorTO->type == 'S' && $this->errorTO->object[changed] > 0) {
            return $this->errorTO->type;     	
     } else {
            return "F"	;
     }   
	
  }

// **************************************************************************************************************************************************** 
  public function documentCharge($transporter, $userUId) {
	
	// -- Document Charge

     $sql = "update temp_rate_" . $userUId . "  t  
             left join .transport_costs tc on tc.charge_type = 3 
             and tc.transporter = " . mysqli_real_escape_string($this->dbConn->connection, $transporter) . "
                                      set  t.Field11 = tc.rate
     where 1;";
             
     $this->errorTO = $this->dbConn->processPosting($sql,"");
     $this->dbConn->dbQuery("commit");
             
     if($this->errorTO->type == 'S' && $this->errorTO->object[changed] > 0) {
            return $this->errorTO->type;     	
     } else {
            return "F"	;
     }   
  }	  
// **************************************************************************************************************************************************** 
  public function backDoorCharge($transporter, $userUId) { 
  // -- Back Door Charge

     $sql = "update temp_rate_" . $userUId . "  t  
     left join .transport_costs tc on tc.charge_type = 5 
                                   and tc.transporter = " . mysqli_real_escape_string($this->dbConn->connection, $transporter) . " 
     left join .transport_costs tc2 on tc2.charge_type = 7  
                                    and tc.transporter = " . mysqli_real_escape_string($this->dbConn->connection, $transporter) . " 
                                    set t.Field12 = round(tc.rate + (if(t.Field4 - tc2.base_quantity > 0, tc2.rate * (t.Field4 - tc2.base_quantity),0)),2) 
     where 1; ";     
     
     $this->errorTO = $this->dbConn->processPosting($sql,"");     
     $this->dbConn->dbQuery("commit");
             
     if($this->errorTO->type == 'S' && $this->errorTO->object[changed] > 0) {
            return $this->errorTO->type;     	
     } else {
            return "F"	;
     }   
  }

// **************************************************************************************************************************************************** 
  public function dieselSurcharge($transporter, $userUId, $postDP) {
  // Diesel Surcharge  	

    $sql = "update temp_rate_" . $userUId . "  t 
            left join .transport_costs tc on tc.charge_type = 6  
                                          and tc.transporter = " . mysqli_real_escape_string($this->dbConn->connection, $transporter) . " 
                                          and " . $postDP . " between tc.low_price and tc.high_price   
                                          set t.Field13 = round(tc.base_quantity * tc.surcharge/ 100,2)  
    where 1";
    
    $this->errorTO = $this->dbConn->processPosting($sql,"");

    $this->dbConn->dbQuery("commit");
    
    if($this->errorTO->type == 'S' && $this->errorTO->object[changed] > 0) {
         return $this->errorTO->type;     	
    } else {
         return "F"	;
    }  }	
// **************************************************************************************************************************************************** 
  public function warehouseCharge($postMARGE, $userUId) {
  // WareHouse Charge  	

    $sql = "update temp_rate_" . $userUId . "  t  set t.Field14 = round(t.Field5 * " . $postMARGE . "/ 100,2)  
    where 1";

    $this->errorTO = $this->dbConn->processPosting($sql,"");
    $this->dbConn->dbQuery("commit");   
             
    if($this->errorTO->type == 'S' && $this->errorTO->object[changed] > 0) {
           return $this->errorTO->type;     	
    } else {
           return "F"	;
    }   

  }	
// **************************************************************************************************************************************************** 


  public function updateStoreAreas($principal, $docNo) {
  // Set Store Area  	

    $sql = "select pst.uid as 'pstUid',
                   psm.uid as 'psmUid', 
                   psm.deliver_name, 
                   psm.deliver_add1, 
                   psm.deliver_add2, 
                   psm.deliver_add3,
                   pst.principal_store_uid,
                   taf.area_name as 'From Area',
                   taf.uid as 'frmUid',
                   tat.area_name as 'To Area',
                   tat.uid as 'toUid',
                   tad.definition 
		 
            from principal_store_master psm, 
                 document_master dm, 
	               document_header dh
            left Join .principal_store_transporter pst on pst.principal_store_uid = dh.principal_store_uid and pst.transporter_uid = 9
            left join .transport_areas taf on taf.uid = pst.from_area_uid
            left join .transport_areas tat on tat.uid = pst.to_area_uid
            left join .transport_area_definition tad on tad.uid = tat.area_definition_uid	  
            where dm.uid = dh.document_master_uid
            and   dh.principal_store_uid = psm.uid
            and   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . " 
            and   dm.document_number = " . mysqli_real_escape_string($this->dbConn->connection, $docNo) . ";";
            
      $mfDDU = $this->dbConn->dbGetAll($sql);
      return $mfDDU;
  }
// **************************************************************************************************************************************************** 
  public function getAreaArray($transporter) {

      $sql = "select ta.uid as 'taUID',
                     ta.uid as 'taTUid',
                     ta.area_name
              from .transport_areas ta
              where ta.transporter_uid = " . mysqli_real_escape_string($this->dbConn->connection, $transporter) . ";";
  
      $mfTA = $this->dbConn->dbGetAll($sql);
      return $mfTA;

  }
// **************************************************************************************************************************************************** 
  public function insertStoreArea($transporter, $srtoeUid, $fromArea, $toArea) {

      $sql = "INSERT INTO `kwelanga_live`.`principal_store_transporter` (`principal_store_uid`, 
                                                                         `transporter_uid`, 
                                                                         `from_area_uid`, 
                                                                         `to_area_uid`) 
              VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $srtoeUid) . "', 
                      '" . mysqli_real_escape_string($this->dbConn->connection, $transporter)  .  "', 
                      '" . mysqli_real_escape_string($this->dbConn->connection, $fromArea) . "', 
                      '" . mysqli_real_escape_string($this->dbConn->connection, $toArea) . "');";
  
     $this->errorTO = $this->dbConn->processPosting($sql,"");     
     $this->dbConn->dbQuery("commit");
             
     if($this->errorTO->type == 'S') {
            return $this->errorTO->type;     	
     } else {
     	      print_r($this->errorTO);
            return "F"	;
     }   
      return $mfTA;

  }
// **************************************************************************************************************************************************** 
  public function updateStoreArea($pstUid, $fromArea, $toArea) {

      $sql = "update principal_store_transporter pst set pst.from_area_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $fromArea) . "',  
                                                         pst.to_area_uid   = '" . mysqli_real_escape_string($this->dbConn->connection, $toArea) . "'   
              where pst.uid = " . mysqli_real_escape_string($this->dbConn->connection, $pstUid) . ";"; 
  
     $this->errorTO = $this->dbConn->processPosting($sql,"");     
     $this->dbConn->dbQuery("commit");
             
     if($this->errorTO->type == 'S') {
            return $this->errorTO->type;     	
     } else {
     	      print_r($this->errorTO);
            return "F"	;
     }   
      return $mfTA;

  }
// **************************************************************************************************************************************************** 
  public function getdocumentsWithPallets($principal, $start, $end, $userUId) {

      $sql = "update     document_master dm
              left join  temp_rate_" . $userUId . "  t on trim(dm.document_number) = trim(t.Field1),	  
                         document_detail dd, 
                         principal_product pp, 
                         principal_product_category ppc set t.Field15 = dd.document_qty
              where dm.uid = dd.document_master_uid
              and   dd.product_uid = pp.uid
              and   pp.major_category = ppc.uid
              and   dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principal) . "
              and   dm.document_number in  (374,375,376,382,384,356,353,379,366,367,377,372,370,373,369,368, 415)
              and   ppc.description like '%pallet%';"; 
  
       $this->errorTO = $this->dbConn->processPosting($sql,"");     
       $this->dbConn->dbQuery("commit");
             
       if($this->errorTO->type == 'S') {
            return $this->errorTO->type;     	
       } else {
       	      print_r($this->errorTO);
              return "F"	;
       }   
       return $mfTA;

  }
// **************************************************************************************************************************************************** 
  public function calculateTotals($userUId) {

      $sql = "update temp_rate_" . $userUId . "  t set t.Field16 = (t.Field8 + t.Field10 + t.Field11 + t.Field12 + t.Field13 + t.Field14)
              where 1;"; 
  
       $this->errorTO = $this->dbConn->processPosting($sql,"");     
       $this->dbConn->dbQuery("commit");
             
       if($this->errorTO->type == 'S') {
            return $this->errorTO->type;     	
       } else {
       	      print_r($this->errorTO);
              return "F"	;
       }   
       return $mfTA;

  }
// **************************************************************************************************************************************************** 
  public function calculatePercentage($userUId) {

      $sql = "update temp_rate_" . $userUId . "  t set t.Field17 = round((t.Field8 + t.Field10 + t.Field11 + t.Field12 + t.Field13 + t.Field14) / t.Field5 * 100,1)
              where 1;"; 
  
       $this->errorTO = $this->dbConn->processPosting($sql,"");     
       $this->dbConn->dbQuery("commit");
             
       if($this->errorTO->type == 'S') {
            return $this->errorTO->type;     	
       } else {
       	      print_r($this->errorTO);
              return "F"	;
       }   
       return $mfTA;

  } 
// **************************************************************************************************************************************************** 
  public function calculateGrandTotals($userUId) {
  	
       $sql = "insert into temp_rate_11 (Field1, Sort) values ('',10);";  	
       $this->errorTO = $this->dbConn->processPosting($sql,"");     
       $this->dbConn->dbQuery("commit");
 
       $sql = "insert into temp_rate_11 (Field1, Field5, Field16, Field17, Sort)
              (select 'Totals', round(sum(t.field5),2), round(sum(t.field16),2), round(sum(t.field16) / sum(t.field5) * 100,1), 100
              from .temp_rate_11 t
              where 1);"; 
  
       $this->errorTO = $this->dbConn->processPosting($sql,"");     
       $this->dbConn->dbQuery("commit");
             
       if($this->errorTO->type == 'S') {
            return $this->errorTO->type;     	
       } else {
       	      print_r($this->errorTO);
              return "F"	;
       }   
       return $mfTA;

  }   
// **************************************************************************************************************************************************** 
}
?>


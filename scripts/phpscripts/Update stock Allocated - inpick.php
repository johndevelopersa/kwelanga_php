<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/Update stock Allocated - inpick.php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

$principalsToCheck = array(216);

// $principalsToCheck = array(271, 64, 342, 304, 216, 347);

foreach($principalsToCheck as $prinrow) {
	
// Get each SKD 

//print_r($prinrow);

     $sql = "select distinct(upd.depot_id) as 'warehouse' ,
                    upd.principal_id as 'principal'
             from   user_principal_depot upd, depot d, document_master dm
             where d.uid = upd.depot_id
             and   upd.principal_id  =  ".  $prinrow . " 
             and   dm.principal_uid = upd.principal_id
             and   dm.processed_date > curdate() - interval 45 day
             and   d.wms = 'Y'
             and   dm.depot_uid = d.uid
             order by upd.principal_id, upd.depot_id;";             


     $pdarr = $dbConn->dbGetAll($sql);

     foreach($pdarr as $row) {
     
         // Update Allocations - Zero Fields
    	
         $oasql = "update stock s set s.allocations = 0 where s.principal_id = " . $row['principal'] . " and s.depot_id = " . $row['warehouse'] . ";";

         $errorTO = $dbConn->processPosting($oasql,"");
      
         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
              $errorTO->description="Zero Field Failed : ".$errorTO->description;
              echo($errorTO->description);
         } else {
         	    $dbConn->dbQuery("commit");
         }
         $oasql = "update stock s set s.allocations = (select 0-sum(dd.ordered_qty)
                                                       from document_master dm, 
                                                            document_header dh, 
                                                            document_detail dd
                                                       where dm.uid = dh.document_master_uid
                                                       and   dm.uid = dd.document_master_uid
                                                       and   dm.depot_uid     = " . $row['warehouse'] . "
                                                       and   dm.principal_uid = " . $row['principal'] . "
                                                       and   dm.processed_date >= curdate() - interval 45 day
                                                       and   dm.document_type_uid in (1,6,13,27)
                                                       and   dh.document_status_uid in (74,75)
                                                       and   dd.product_uid = s.principal_product_uid
                                                       group by dd.product_uid)
                                                       
                  where  s.principal_id = " . $row['principal'] . "  
                  and    s.depot_id     = " . $row['warehouse'] . " 
                  and    s.principal_product_uid = (select dd2.product_uid
                                                    from document_master dm2, 
                                                         document_header dh2, 
	                                                       document_detail dd2
                                                    where dm2.uid = dh2.document_master_uid
                                                    and   dm2.uid = dd2.document_master_uid
                                                    and   dm2.depot_uid     = " . $row['warehouse'] . "
                                                    and   dm2.principal_uid = " . $row['principal'] .  " 
                                                    and   dh2.invoice_date >= curdate() - interval 45 day
                                                    and   dm2.document_type_uid in (1,6,13,27)
                                                    and   dh2.document_status_uid in (74,75)
                                                    and   dd2.product_uid = s.principal_product_uid
                                                    group by dd2.product_uid); ";
    
         $errorTO = $dbConn->processPosting($oasql,"");
      
         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
             $errorTO->description="Allocation Update Failed : ".$errorTO->description;
             echo($errorTO->description);
         } else {
             $dbConn->dbQuery("commit");     	
         }
         echo($errorTO->description);    
         echo "<br>";	

     	   // Update Inpick Fields - Zero Fields
    	
         $oasql = "update stock s set in_pick = 0 where " . $row['principal'] . " and s.depot_id = " . $row['warehouse'] . ";";

         $errorTO = $dbConn->processPosting($oasql,"");
      
         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
              $errorTO->description="Zero Field Failed : ".$errorTO->description;
              echo($errorTO->description);
         } else {
         	        $dbConn->dbQuery("commit");
         }
         $oasql = "update stock s set in_pick = (select 0-sum(dd.ordered_qty)
                                                 from document_master dm, 
                                                      document_header dh, 
                                                      document_detail dd
                                                 where dm.uid = dh.document_master_uid
                                                 and   dm.uid = dd.document_master_uid
                                                 and   dm.principal_uid  = " . $row['principal'] . "
                                                 and   dm.depot_uid      = " . $row['warehouse'] . "
                                                 and   dh.invoice_date >= curdate() - interval 45 day
                                                 and   dm.document_type_uid in (1,6,13)
                                                 and   dh.document_status_uid in (87)
                                                 and   dd.product_uid = s.principal_product_uid
                                                 group by dd.product_uid)
                  where s.principal_id = " . $row['principal'] . "
                  and   s.depot_id     = " . $row['warehouse'] . "
                  and   s.principal_product_uid = (select dd2.product_uid
                                                   from document_master dm2, 
                                                   document_header dh2, 
                                                   document_detail dd2
                                                   where dm2.uid = dh2.document_master_uid
                                                   and   dm2.uid = dd2.document_master_uid
                                                   and   dm2.depot_uid     =  " . $row['warehouse'] . " 
                                                   and   dm2.principal_uid =  " . $row['principal'] . "
                                                   and   dh2.invoice_date >= curdate() - interval 45 day
                                                   and   dm2.document_type_uid in (1,6,13)
                                                   and   dh2.document_status_uid in (87)
                                                   and   dd2.product_uid = s.principal_product_uid
                                                   group by dd2.product_uid);";
                                                   
                                                   echo "<br>";
                                                   echo $oasql;
                                                   echo "<br>";
    
         $errorTO = $dbConn->processPosting($oasql,"");
      
         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
             $errorTO->description="Allocation Update Failed : ".$errorTO->description;
             echo($errorTO->description);
         } else {
         	   $dbConn->dbQuery("commit");  
         }
    }
}

    
    // Recalcalculate stock Balance
    
    $bsql = "update stock a set  a.closing = (a.opening + 
                      a.arrivals + 
                      a.returns_cancel + 
                      a.returns_nc + 
                      a.delivered + 
                      a.adjustment),

                      a.available = (a.opening  + 
                      a.arrivals + 
                      a.returns_cancel + 
                      a.returns_nc + 
                      a.delivered + 
                      a.adjustment +
                      a.allocations +
                      a.in_pick)
             where 1;";

             $errorTO = $dbConn->processPosting($bsql,"");
      
             if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                  $errorTO->description="Allocation Update Failed : ".$errorTO->description;
                  echo($errorTO->description);
             } 
             $dbConn->dbQuery("commit");  
               

//***************************************************************************************************************************************************************************************************
    echo "End";


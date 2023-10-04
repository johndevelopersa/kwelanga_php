<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/RepairAllocated.php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

$whUid   = '230';
$prinUid = '305';

$dateFrom = '2019-01-01';

// Update allocated
         $usql = "update stock s set s.allocations = 0 
                  where s.principal_id = " . $prinUid . "  
                  and s.depot_id       = " . $whUid   . " ; " 

         $errorTO = $dbConn->processPosting($usql,"");
      
         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                    $errorTO->description="Failed : ".$errorTO->description;
                    echo($errorTO->description); ;         	                  
         } 
         $dbConn->dbQuery("commit");

         $usql = "update stock s set s.allocations = (select 0-sum(dd.ordered_qty)
                                                      from document_master dm, 
                                                           document_header dh, 
	                                                         document_detail dd
                                                      where dm.uid = dh.document_master_uid
                                                      and   dm.uid = dd.document_master_uid
                                                      and   dm.depot_uid     =   " . $whUid      . "
                                                      and   dm.principal_uid =   " . $prinUid    . "  
                                                      and   dm.processed_date >= " . $dateFrom   . "
                                                      and   dm.document_type_uid = 1
                                                      and   dh.document_status_uid in (74,75)
                                                      and   dd.product_uid = s.principal_product_uid
                                                      group by dd.product_uid)
                  where s.principal_id = " . $prinUid    . "
                  and   s.depot_id     = " . $whUid      . "
                  and   s.principal_product_uid = (select dd2.product_uid
                                                   from document_master dm2, 
                                                        document_header dh2, 
                                                        document_detail dd2
                                                   where dm2.uid = dh2.document_master_uid
                                                   and   dm2.uid = dd2.document_master_uid
                                                   and   dm2.depot_uid     =   " . $whUid      . "
                                                   and   dm2.principal_uid =   " . $prinUid    . "  
                                                   and   dh2.invoice_date >= " . $dateFrom   . "
                                                   and   dm2.document_type_uid = 1
                                                   and   dh2.document_status_uid in (74,75)
                                                   and   dd2.product_uid = s.principal_product_uid
                                                   group by dd2.product_uid); "

         $errorTO = $dbConn->processPosting($usql,"");
      
         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                    $errorTO->description="Failed : ".$errorTO->description;
                    echo($errorTO->description);       	                  
         } 
         $dbConn->dbQuery("commit");

//  -- Update IN Pick

         $usql = "update stock s set in_pick = 0 where s.principal_id = =   " . $prinUid    . "   and s.depot_id = =   " . $whUid      . ";"
         
         $errorTO = $dbConn->processPosting($usql,"");
      
         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                    $errorTO->description="Failed : ".$errorTO->description;
                    echo($errorTO->description);               
         } 
         $dbConn->dbQuery("commit");         
         
         
          $usql = "update stock s set in_pick = (select 0-sum(dd.ordered_qty)
                                  from document_master dm, 
                                       document_header dh, 
	                                    document_detail dd
                                       where dm.uid = dh.document_master_uid
                                       and   dm.uid = dd.document_master_uid
                                       and   dm.depot_uid      =   " . $whUid     . "
                                       and   dm.principal_uid  =   " . $prinUid   . "  
                                       and   dh.invoice_date  >=   " . $dateFrom  . "
                                       and   dm.document_type_uid = 1
                                       and   dh.document_status_uid in (87)
                                       and   dd.product_uid = s.principal_product_uid
                                       group by dd.product_uid)
                   where s.principal_id =   " . $prinUid   . " 
                   and   s.depot_id     =   " . $whUid     . "
                   and   s.principal_product_uid = (select dd2.product_uid
                                                    from document_master dm2, 
                                                         document_header dh2, 
                                                         document_detail dd2
                                                    where dm2.uid = dh2.document_master_uid
                                                    and   dm2.uid = dd2.document_master_uid
                                                    and   dm.depot_uid      =   " . $whUid     . "
                                                    and   dm.principal_uid  =   " . $prinUid   . "  
                                                    and   dh.invoice_date  >=   " . $dateFrom  . "
                                                    and   dm2.document_type_uid = 1
                                                    and   dh2.document_status_uid in (87)
                                                    and   dd2.product_uid = s.principal_product_uid
                                                    group by dd2.product_uid); "
                                      
         $errorTO = $dbConn->processPosting($usql,"");
      
         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                    $errorTO->description="Failed : ".$errorTO->description;
                    echo($errorTO->description);               
         } 
         $dbConn->dbQuery("commit");                                               
                                      
                                      
          $usql = "update stock a set  a.closing = (a.opening + 
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
                   where a.principal_id = =   " . $prinUid    . ":"                                   


         $errorTO = $dbConn->processPosting($usql,"");
      
         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                    $errorTO->description="Failed : ".$errorTO->description;
                    echo($errorTO->description);               
         } 
         $dbConn->dbQuery("commit");                           

    echo "<br>";
    echo "Repair Allocated complete";
    echo "<br>";
    echo "End";
    echo "<br>";    

?>
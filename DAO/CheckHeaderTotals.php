<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/CheckHeaderTotals.php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

    $sql = "select distinct(dm.uid) 
            from .document_master dm, document_header dh, .document_detail dd 
            where dm.uid = dh.document_master_uid 
            and dm.uid   = dd.document_master_uid 
            and dm.processed_date > '2018-10-01'
            and dm.principal_uid = 309;";

    $docs = $dbConn->dbGetAll($sql);
    foreach ($docs as $doc1) {
	
         $usql = "update document_header dh set dh.cases           = (select sum(dd.ordered_qty)
                                                                      from .document_detail dd
																	                                    where dd.document_master_uid = " . $doc1['uid'] . "),
                                                dh.exclusive_total = (select sum(dd.extended_price)
                                                                      from .document_detail dd
																	                                    where dd.document_master_uid = " . $doc1['uid'] . "),
                                                dh.vat_total       = (select sum(dd.vat_amount)
                                                                      from .document_detail dd
														                                          where dd.document_master_uid = " . $doc1['uid'] . "),
									                              dh.invoice_total   = (select sum(dd.total)
                                                                      from .document_detail dd
														                                          where dd.document_master_uid = " . $doc1['uid'] . ")
                  where dh.document_master_uid = " . $doc1['uid'] . " ;";
                  
                  echo $usql;
                  echo "<br>";
                  
                  
         $errorTO = $dbConn->processPosting($usql,"");
      
          if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                    $errorTO->description="Header Update Failed : ".$errorTO->description;
                    echo($errorTO->description); ;         	                  
          } 
          $dbConn->dbQuery("commit");
	
	        echo($errorTO->description);    
          echo "<br>";	
	
    }

    echo "End";

?>
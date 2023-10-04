<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$sql = "select lpad(dm.client_document_number,10,0) as 'Sales Doc.',
               dm.processed_date as 'Created On', 
               sfd.value as 'Plant',
               sfda.value as 'Customer',		  
               dd.client_line_no as 'Item',
               pp.product_code as 'Material',
               dd.ordered_qty as 'Picked Quantity',
               dm.client_document_number,
               dm.uid
         from  document_master dm 
         left join document_header dh on dh.document_master_uid = dm.uid
         left join document_detail dd on dd.document_master_uid = dm.uid
         left join .special_field_details sfd on sfd.field_uid = 429 and sfd.entity_uid = dm.depot_uid
         left join .special_field_details sfda on sfda.field_uid = 427 and sfda.entity_uid = dh.principal_store_uid
         left join .principal_product pp on pp.uid = dd.product_uid
         where dm.principal_uid = 354
         and   dm.processed_date >= '2019-11-07'
         and   dh.document_status_uid in (" . DST_UNACCEPTED . ")
         and   dm.incoming_file like '%RICH%'
         order by dm.client_document_number";
 
         $pdoc = $dbConn->dbGetAll($sql);
         
         $firstdoc = '';         
         
         foreach ($pdoc as $doc1) {
         	  if($firstdoc <> $doc1['Sales Doc.']) {
         	  	  $filename  = "Pick_Confirmations_" . $doc1['Sales Doc.'] . ".csv";
         	  	  $headerrow = "Sales Doc.;Created On;Plant; Customer;Item;Material;Picked Quantity  \r\n";
//       	  	  file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/ftp/richs/out/' . $filename ,$headerrow); 
         	  	  $firstdoc = $doc1['Sales Doc.'];   
         	  	  
         	  	  // Change status to accepted
         	  	  
         	  	  $updsql = "update document_header dh, 
         	  	                    document_detail dd set dh.document_status_uid = " . DST_ACCEPTED . ",
         	  	                                           dd.buyer_delivered_qty = dd.ordered_qty
         	  	             where  dd.document_master_uid = dh.document_master_uid
         	  	             and    dh.document_master_uid = " . $doc1['uid'];
         	  	                      	  	  
         	  	   $rTO = $dbConn->processPosting($updsql,"");

                if($rTO->type == "S"){
	                 	echo "Query: OK Confirmation" . $doc1['Sales Doc.'] . " Extracted<br>";
		                $dbConn->dbQuery("commit");
         	      }
         	  }    
         	      
         	      $detrow = $doc1['Sales Doc.'] .";". 
         	                $doc1['Created On'] .";".
         	                $doc1['Plant'] .";".
         	                $doc1['Customer'] .";".
         	                $doc1['Item'] .";".
         	                $doc1['Material'] .";".
         	                $doc1['Picked Quantity']. " \r\n";
//       	  	  file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/ftp/richs/out/' . $filename ,$detrow ,FILE_APPEND);           	
        }
         	
echo "End";
?>
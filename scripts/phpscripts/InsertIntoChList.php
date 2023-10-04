<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/InsertIntoChList.php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

// Get list of Checkers stores



    $sql = "select distinct(ch.gln),
                   concat(ch.Name , '   ', ch.branch) as 'Name',
                   psm.deliver_add1 as 'IA1',
                   psm.deliver_add2 as 'IA2',
                   psm.deliver_add3 as 'IA3', 
                   'Shoprite Checkers Pty Ltd' as 'BA1',
                   'PO Box 215' as 'BA2',
                   'Brackenfell' as 'BA3',
                   'GP' as 'BA4', 
                   ch.branch 
            from .principal_store_master psm
            left join checkers_store_list ch on ch.branch =  trim(replace(right(psm.deliver_name,6),')',''))
            where psm.depot_uid in (2)
            and   ch.gln <> '';";

    $docs = $dbConn->dbGetAll($sql);
    
    $num = 0;
    
    foreach ($docs as $doc1) {
       $sql1 = "select *
                from checkers_list 
                where trim(checkers_list.FLD1) = trim( " . $doc1['gln'] . ")";

       $docs2 = $dbConn->dbGetAll($sql1);
       
       if(count($docs2)==0) {
          $num++;
          echo $num;
          echo "<br>";
          
          $sql3 = "insert into checkers_list (FLD1,FLD2,FLD3,FLD4,FLD5,FLD6,FLD7,FLD8,FLD9,FLD10) VALUES ( '" . $doc1['gln'] . "',
                                                                                                  ' " . $doc1['Name'] . "',
                                                                                                  ' " . $doc1['IA1']  . "',
                                                                                                  ' " . $doc1['IA2']  . "',
                                                                                                  ' " . $doc1['IA3']  . "',
                                                                                                  ' " . $doc1['BA1']  . "',
                                                                                                  ' " . $doc1['BA2']  . "',
                                                                                                  ' " . $doc1['BA2']  . "',
                                                                                                  ' " . $doc1['BA4']  . "',
                                                                                                  ' " . $doc1['branch']  . "');";

          $errorTO = $dbConn->processPosting($sql3,"");
      
          if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                    $errorTO->description="Insert Failed : ".$errorTO->description;
                    echo($errorTO->description); ;         	                  
          } 
          $dbConn->dbQuery("commit");
          
          
       }
    }	
    echo $num . " Records Inserted -- End";

?>
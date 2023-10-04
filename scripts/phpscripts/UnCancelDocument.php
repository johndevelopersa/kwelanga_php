<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/UnCancelDocument.php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

echo $value . "Starting Un Cancel";                                    
echo ("<BR>");
echo ("<BR>");                  

$principUID = 408;

$endStatus  = 75;

$ordelist = array('00707797',
'00707798',
'00707799',
'00707801',
'00707802',
'00707803',
'00707804',
'00707805',
'00707816',
'00707831',
'00707832',
'00707833',
'00707834',
'00707835',
'00707837',
'00707838',
'00707982',
'00708011',
'00708018',
'00708033',
'00708046',
'00708049',
'00708052',
'00708066',
'00708067',
'00708068',
'00708070',
'00708071',
'00708072',
'00708080',
'00708083',
'00708084',
'00708085',
'00708086',
'00708087',
'00708089',
'00708090',
'00708097',
'00708098',
'00708099',
'00708100',
'00708101',
'00708102',
'00708122',
'00708123',
'00708124',
'00708125',
'00708126',
'00708128',
'00708129',
'00708130',
'00708131',
'00708132',
'00708133',
'00708134',
'00708148',
'00708149',
'00708150',
'00708151',
'00708152',
'00708154',
'00708155',
'00708156',
'00708157',
'00708173',
'00708174',
'00708175',
'00708176',
'00708177',
'00708178',
'00708179',
'00708181',
'00708182',
'00708186',
'00708208',
'00708209',
'00708210',
'00708211',
'00708212',
'00708213',
'00708214',
'00708215',
'00708216',
'00708217',
'00708218',
'00708219',
'00708220',
'00708221',
'00708222',
'00708223',
'00708224',
'00708225',
'00708226',
'00708237',
'00708258',
'00708259',
'00708260',
'00708262',
'00708263',
'00708264',
'00708266',
'00708267',
'00708268',
'00708270',
'00708272',
'00708285',
'00708286',
'00708290',
'00708293',
'00708295',
'00708296',
'00708300');

foreach ($ordelist as $value) {

	      $bldsql = "truncate table temp;   ";

        $dtresult = $dbConn->dbQuery($bldsql);
        $dbConn->dbQuery("commit");
	      
        $isql = "insert into temp (FLD1, FLD2, FLD3, FLD4, FLD5, FLD6, FLD7) (select dd.document_master_uid, 
                                                                        dd.ordered_qty, 
                                                                        dd.net_price, 
                                                                        dd.vat_rate, 
                                                                        dd.uid,
                                                                        dh.document_status_uid,
                                                                        '" .$value ."'
                                                                    from document_detail dd
                                                                    inner join  document_header dh on dh.document_master_uid = dd.document_master_uid
                                                                    where dd.document_master_uid in (select dm.uid                                                                                                         
                                                                                                     from .document_master dm                                                                                         
                                                                                                     where dm.principal_uid = " .$principUID . "
                                                                                                     and dm.document_number in ('" .$value ."'))) ";
        
        $utresult = $dbConn->dbQuery($isql);
        $dbConn->dbQuery("commit");
        
        // Check that status is cancelled
        
        $csql = "select distinct(FLD7), FLD6
                 from temp;";
                 
        $aSR = $dbConn->dbGetAll($csql);
        
        
        foreach ($aSR as $cvalue) {
        	
        	echo $cvalue['FLD5'] . '   ' ;
        	if($cvalue['FLD6'] == 47 ) {
        		       echo " Un Cancelling " . $value ;
                   
                   $isql = "update document_detail dd, temp t set dd.document_qty   = trim(t.FLD2),                                                  
                                                                  dd.extended_price = trim(t.FLD2) * trim(FLD3),                                 
                                                                  dd.vat_amount     = (trim(t.FLD2) * trim(t.FLD3)) * trim(t.FLD4)/100,                             
                                                                  dd.total          = (trim(t.FLD2) * trim(FLD3)) + ((trim(t.FLD2) * trim(t.FLD3)) * trim(t.FLD4)/100)                                 
                            where dd.uid = trim(t.FLD5);";
                  
                   $utresult = $dbConn->dbQuery($isql);
                   $dbConn->dbQuery("commit");
                                               
                   echo $value . " Detail Updated";                                    
                   echo ("<BR>");                                                                                                                         
                                                                                                                 
                   $isql = "update document_header dh, temp t set dh.document_status_uid = " . $endStatus . ",
                                                                  dh.invoice_date = dh.order_date,
                                                                  dh.pod_reason_uid = NULL
                            where dh.document_master_uid = trim(t.FLD1)" ;
       
                            $utresult = $dbConn->dbQuery($isql);
                            $dbConn->dbQuery("commit");
       
                   $iusql   = "select distinct(trim(t.FLD1)) as 'U' from temp t where 1" ;
                   $docU    = $dbConn->dbGetAll($iusql);
                   $docUid  = $docU[0]['U'];
                   echo "Document UID - " . $docUid;
                   echo ("<BR>");                                         
                   echo $value . " Header Updated";                                    
                   echo ("<BR>");
       
                   $usql = "update document_header dh set dh.cases = (select sum(dd.document_qty)
                                                                      from   document_detail dd
                                                                      where  dd.document_master_uid = " . $docUid . "
                                                                      group by dd.document_master_uid ),
                                                   dh.exclusive_total = (select sum(dd.extended_price)
                                                                         from .document_detail dd
                                                                         where dd.document_master_uid = " . $docUid . "
                                                                         group by dd.document_master_uid),
                                                   dh.vat_total       = (select sum(dd.vat_amount)
                                                                         from .document_detail dd
                                                                         where dd.document_master_uid = " . $docUid . "
                                                                         group by dd.document_master_uid),
                                                   dh.invoice_total   = (select sum(dd.total)
                                                                         from .document_detail dd
                                                                         where dd.document_master_uid = " . $docUid . "
                                                                         group by dd.document_master_uid)
                  where dh.document_master_uid = " . $docUid . " ;";
                  
                 $utresult = $dbConn->dbQuery($usql);
                 $dbConn->dbQuery("commit");
            
                 echo $value . " Header Re Calculated";                                    
                 echo ("<BR>"); 
        	} else { 
        		     echo " Existing Status not cancelled - No Action " . $value ;
        	}
        	echo ("<BR>"); 
        	echo ("<BR>");
        }          
}

      
echo ("<BR>");
echo "End";
?>
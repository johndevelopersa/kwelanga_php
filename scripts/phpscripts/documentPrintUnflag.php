<?php


include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'DAO/ExportDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostTransactionDAO.php');
include_once($ROOT.$PHPFOLDER.'functional/export/adaptor/AdaptorDocumentExport.php');  //depot export adaptors.
include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/PostingOrderTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrderDetailTO.php');


    $dbConn = new dbConnect();
    $dbConn->dbConnection();

?>

<br><br><br><br>
<div align="center">


<div style="background:#efefef;border:1px solid #ccc;padding:10px;width:340px;font-family: arial;">
<form method="POST">
  Document Number:<br>
  <input type="text" value="" name="DOCNO"><br>
  <span style="font-size:10px;" >Type the document number you want to unflag as printed!</span> <br>
  <input type="submit" value="Submit" name="submit">
</form>
</div>
</div>
<?php


if(isset($_POST['submitlog'])){


  $dbConn->dbQuery("update document_depot_audit_log
                      set comment = 'PRINTED-UNFLAGGED'
                      where document_master_uid='".mysql_real_escape_string(trim($_POST['MUID']))."'
                        and document_status_uid = '76'
                        and comment = 'PRINTED'
                        ");

  // make sure the lock is released
  $dbConn->dbInsQuery("commit");


} elseif(isset($_POST['submit'])){


    $dArr = $dbConn->dbGetAll("SELECT
                                  m.uid as muid,
                                  m.document_number,
                                  p.uid as puid,
                                  p.name as principal_name,
                                  d.uid as depot_uid,
                                  d.name as depot_name,
                                  l.uid as luid,
                                  l.`comment`
                                   from document_master m
                                  inner join principal p on m.principal_uid = p.uid
                                  inner join depot d on m.depot_uid = d.uid
                                  inner join document_depot_audit_log l on m.uid = l.document_master_uid and document_status_uid = '76' and l.`comment` = 'PRINTED'
                                  where m.processed_date > (NOW()-INTERVAL 90 DAY)
                                  and m.document_number = ".mysql_real_escape_string(abs(trim($_POST['DOCNO'])))."
                                  group by m.uid");

    echo '<br><br>';
    echo '<form method="POST">';
    echo '<div align="center" style="padding:20px;background:#efefef;">';
    foreach($dArr as $r){
     echo '<input type="radio" name="MUID" value="'.$r['muid'].'"> ' ,  implode(' | ',$r) , '<br>';
    }
    echo '<hr><input type="submit" value="UNFLAG" name="submitlog">';
    echo '</div></form>';

}



?>
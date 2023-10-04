<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER."DAO/SequenceDAO.php");

$dbConn = new dbConnect();
$dbConn->dbConnection(); // can connect to any db, as long as db is referenced as part of sql passed

$sequenceDAO = new SequenceDAO(null);

$tableName="x_store";
$colName="f33";

$rows = $dbConn->dbGetAll("select uid, {$colName} from {$tableName}");

foreach ($rows as $r) {
  $oASeq = $sequenceDAO->getStoreOASequence();
  $rTO = $dbConn->processPosting("Update {$tableName} SET {$colName}='{$oASeq}' WHERE uid = {$r["uid"]}", "");
  if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
    echo "<p style='color:red;'>Updating UID {$r["uid"]} was unsuccessful</p>";
  }

}

$dbConn->dbinsQuery("commit");

?>
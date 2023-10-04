<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


$dbConn = new dbConnect();  //Create new database object
$dbConn->dbConnection();  //Database connection method

$errorTO = new ErrorTO;

$userId = (isset($_POST['USERID'])) ? ($_POST['USERID']) : (false);
$postSIZE = (isset($_POST['SIZE'])) ? ($_POST['SIZE']) : (false);

if($userId !== false || $postSIZE !== false ){

  if(is_numeric($userId)){

    $sql = "INSERT INTO user_screen_log
      				  (
  						user_uid,
  						screen_size,
  						entry_timestamp
      				  )
      				  VALUES (".
      				  "'" . mysql_real_escape_string($userId) . "',".
      				  "'" . mysql_real_escape_string($postSIZE) . "',".
      				  "NOW())";

      $errorTO = $dbConn->processPosting($sql, '');

      if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {
        $dbConn->dbinsQuery("commit;");
        return $errorTO;
      } else {
        //SILENT FAIL
        mysql_query("rollback", $dbConn->connection);
      }
  }
}



?>
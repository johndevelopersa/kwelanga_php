<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");


$dbConn = new dbConnect();
$dbConn->dbConnection();
$adminDAO = new AdministrationDAO($dbConn);

if (!isset($_SESSION)) session_start;
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION['user_id'];
$userCategory = $_SESSION['user_category'];

$postDOCMASTID = false;
$postSTATUSID = false;
$postVALIDATE = 0;
$postBULKACTION = 0;
CommonUtils::setPostVars();


//validation
if($postDOCMASTID == false || $postSTATUSID == false || empty($postSTATUSID) || empty($postSTATUSID)){
  echo 'ERROR: Invalid/Empty ID passed!';
  return;
}

if($userCategory!="D"){ //logging is active only for depot users...
  echo "ERROR: Invalid User, only Depot Type users can preform this action!";
  return;
}

if($postBULKACTION==1){

  $docArr = explode(',',$postDOCMASTID);
  foreach($docArr as $docId){

    $postTransactionDAO = new PostTransactionDAO($dbConn);
    $result = $postTransactionDAO->postDepotAuditLog($docId, $userId, 'PRINTED', $postSTATUSID);

    if($result->type != FLAG_ERRORTO_SUCCESS){
      mysql_query("rollback", $dbConn->connection);
      echo 'ERROR: updating log - ' . $result->description;
      return;
    }
  }

  mysql_query("commit", $dbConn->connection);
  echo 'SUCCESS';
  return;

  
} else {


  //REPRINTS ALLOWED? - DON'T CHECK THE LOG
  $allowReprints = $adminDAO->hasRole($userId,$principalId,ROLE_REPRINT_DOCUMENT);


  //FIRST FINDS IF THERE WAS A PRINT FOUND FOR THIS DOUCMENT ID.
  //RETURNS "SUCCESS" if all OK.
  //check has been printed before
  $transactionDAO = new TransactionDAO($dbConn);
  $mfT = $transactionDAO->getDocumentDepotAuditStatusPrinted($postDOCMASTID, $postSTATUSID); // principal security check done inside


  if(count($mfT)>0 && trim($mfT[0]['comment']) == 'PRINTED'){

    if($allowReprints){
      if($postVALIDATE==1){
        echo 'REPRINT';
        return;
      }
    } else {
      echo 'SORRY - Document has been printed already!';
      echo "\nPrinted by: " . $mfT[0]['full_name'] . " on: " . $mfT[0]['activity_date'];
      return;
    }

  }

  if($postVALIDATE==0){

    $postTransactionDAO = new PostTransactionDAO($dbConn);
    $result = $postTransactionDAO->postDepotAuditLog($postDOCMASTID, $userId, 'PRINTED', $postSTATUSID);

    if($result->type == FLAG_ERRORTO_SUCCESS){
      mysql_query("commit", $dbConn->connection);
      echo 'SUCCESS';
      return;
    } else {
      mysql_query("rollback", $dbConn->connection);
      echo 'ERROR: updating log - ' . $postBULKACTION . $result->description;
      return;
    }

  } else {

    echo 'SUCCESS';

  }

}

?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");

if (!isset($_SESSION)) session_start() ;
$userUId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

$postDMUID = ( (isset($_GET["DMUID"])) ? mysqli_real_escape_string($dbConn->connection, $_GET['DMUID']) : ((isset($_POST["DMUID"]))?mysqli_real_escape_string($dbConn->connection, $_POST['DMUID']):"") );
$postTRANSPORTERNAME = ((isset($_POST["f_dni_TRANSPORTERNAME"]))?mysqli_real_escape_string($dbConn->connection, $_POST['f_dni_TRANSPORTERNAME']):false);
$postTRUCKREGISTRATION = ((isset($_POST["f_dni_TRUCKREGISTRATION"]))?mysqli_real_escape_string($dbConn->connection, $_POST['f_dni_TRUCKREGISTRATION']):false);
$postCHEPPALLETNUMBER = ((isset($_POST["f_dni_CHEPPALLETNUMBER"]))?mysqli_real_escape_string($dbConn->connection, $_POST['f_dni_CHEPPALLETNUMBER']):false);

include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
$transactionDAO = new TransactionDAO($dbConn);

$adminDAO = new AdministrationDAO($dbConn);

$hasRoleTT = $adminDAO->hasRole($userUId,$principalId,ROLE_TRANSACTION_TRACKING);
if(!$hasRoleTT){
  echo "<p style='color:#e62727;font-family:calibri;font-size:14px;font-weight:bold;'>Sorry, you do not have permissions to VIEW TRACKING!</p>";
  return;
}

$hasAccess = $transactionDAO->userHasAccessToDocument($postDMUID, $userUId);
if(!$hasAccess){
  echo "<p style='color:#e62727;font-family:calibri;font-size:14px;font-weight:bold;text-align:center;'>Sorry, you do not have permissions to view this document</p>";
  return;
}

$mfDD = $transactionDAO->getDeliveryDetails($postDMUID, $userUId);


// first handle submit of changes
if ($postTRANSPORTERNAME!==false) {

  include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");

  $postTransactionDAO = new PostTransactionDAO($dbConn);
  $rTO = $postTransactionDAO->setDeliveryDetails($postDMUID, $postTRANSPORTERNAME, $postTRUCKREGISTRATION, $postCHEPPALLETNUMBER);

  if ($rTO->type==FLAG_ERRORTO_SUCCESS) {
    $dbConn->dbinsQuery("commit");
    echo "<p style='color:#559325;font-family:calibri;font-size:14px;font-weight:bold;text-align:center;'>Successfully stored Delivery Details</p>";
  }  else {
    $dbConn->dbinsQuery("rollback");
    echo "<p style='color:#e62727;font-family:calibri;font-size:14px;font-weight:bold;text-align:center;'>Failed to store Delivery Details!</p>".$rTO->description;
  }

  return;

}


$transporterName = ((isset($mfDD[0]))?$mfDD[0]["transporter_name"]:"");
$truckRegistration = ((isset($mfDD[0]))?$mfDD[0]["truck_registration"]:"");
$chepPalletNumber = ((isset($mfDD[0]))?$mfDD[0]["chep_pallet_number"]:"");
?>
      <form id='frmDeliveryNoteInfo' name='frmDeliveryNoteInfo' method='post' action='{$_SERVER["PHP_SELF"]}' target='if_frmDeliveryNoteInfo' enctype='multipart/form-data' >
        <table width='500'; style='border:none'>
           <tr>
             <td width="50%"; style="border:none">
                <input type='hidden' id='DMUID' name='DMUID' value='{$postDMUID}'>
                <input type='text' id='f_dni_TRANSPORTERNAME' name='f_dni_TRANSPORTERNAME' value='<?php echo $transporterName ?>' size='60' maxlen='60' placeholder='Transporter Name' style='padding-left:10px;' >
             </td>
             <td width="50%"; style="border:none">
                <input type='text' id='f_dni_TRUCKREGISTRATION' name='f_dni_TRUCKREGISTRATION' value='<?php echo $truckRegistration ?>' size='20' maxlen='20' placeholder='Truck Registration' style='padding-left:10px;' >
             </td>
           </tr>
           <tr>
             <td width="50%"; style="border:none">
                <input type='text' id='f_dni_DATE' name='f_dni_DATE' value='<?php echo $collectDate ?>' size='60' maxlen='60' placeholder='Collection Date' style='padding-left:10px;' >
             </td>
             <td width="50%"; style="border:none">
                <input type='text' id='f_dni_TIME' name='f_dni_TIME' value='<?php echo $collectTime ?>' size='20' maxlen='20' placeholder='Collection Time' style='padding-left:10px;' >
             </td>
           </tr>

           <tr>
             <td width="50%"; style="border:none">
                <input type='text' id='f_dni_CHEPPALLETNUMBER' name='f_dni_CHEPPALLETNUMBER' value='<?php echo $chepPalletNumber ?>' size='20' maxlen='20' placeholder='CHEP Pallet Number' style='padding-left:10px;' >             </td>
             <td width="50%"; style="border:none">&nbsp;</td>
           </tr>
	   </table>
       </form>
       <br>
       <input type='button' class='submit' value='Save Delivery Note Info' onclick='document.frmDeliveryNoteInfo.submit();' />";




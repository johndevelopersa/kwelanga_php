<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];



// fields
$fldChosenPCRB = 'ChosenSalesReps';
$divAjaxMainContentArea = "ajaxMainContentArea";
$divAjaxEditContent = "ajaxEditContent";

$DMLType = 'UPDATE';
if (!CommonUtils::isAdminUser() && !CommonUtils::isStaffUser()){

  $dbConn = new dbConnect();
  $dbConn->dbConnection();
  $adminDAO = new AdministrationDAO($dbConn);

  //CHECK ROLES
  $DMLType = 'UPDATE';
  if($adminDAO->hasRole($userId, $principalId, ROLE_MODIFY_PRINCIPAL_SALES_REP)){
    $DMLType = 'UPDATE';
  } else if ($adminDAO->hasRole($userId, $principalId, ROLE_VIEW_PRINCIPAL_SALES_REP)){
    $DMLType = 'VIEW';
  } else {
     echo 'You do not have permissions to UPDATE/VIEW a Principal Sales Rep.';
     return;
  }

}


/*--------------------------------------------------------------------------------------------------------------------------
 *
 * START OF SCREEN
 *
 *--------------------------------------------------------------------------------------------------------------------------*/


echo "<HTML><HEAD></HEAD><BODY>";
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');

?>

<script type="text/javascript" >

  function selectedSalesReps(val) {

    $('#<?php echo $divAjaxMainContentArea ?>').hide();

    AjaxRefresh("REPID="+val+"&DMLTYPE=<?php echo $DMLType; ?>",
        "<?php echo $ROOT.$PHPFOLDER; ?>functional/salesrep/salesRepForm.php",
                    "<?php echo $divAjaxEditContent ?>",
                    "Please wait whilst page is refreshed...",
                    "");
  }

  function backToSalesRepsList(){
    $('#<?php echo $divAjaxEditContent ?>').html('');	//EMPTY OUT AREA
    $('#<?php echo $divAjaxMainContentArea ?>').show(); //DISPLAY OTHER AREA
    adjustMyFrameHeight();
  }

  function refreshSelectSalesReps() {
    AjaxRefresh("USERID=<?php echo $userId; ?>&RBNAME=<?php echo $fldChosenPCRB; ?>&CALLBACK=selectedSalesReps(this.value);&PRINCIPALID=<?php echo $principalId; ?>&RBTYPE=radio",
                "<?php echo $ROOT.$PHPFOLDER; ?>functional/salesrep/salesRepListTable.php",
                "<?php echo $divAjaxMainContentArea; ?>",
                "Please wait whilst page is refreshed...",
                "");
  }

  refreshSelectSalesReps();

</script>
<?php

echo "<DIV id='".$divAjaxEditContent."'></DIV>";
echo "<BR><DIV id='".$divAjaxMainContentArea."'></DIV>";
echo "</BODY></HTML>";
#--------------------------------------------------------------------------------------------------------------------------

?>
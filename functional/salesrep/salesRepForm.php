<?php

include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'functional/main/access_control.php');
include_once($ROOT.$PHPFOLDER.'libs/common.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER."elements/basicInputElement.php");


//setup
if (! isset($_SESSION)) session_start();
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION["user_id"];
$dbConn = new dbConnect();
$dbConn->dbConnection();
$storeDAO = new StoreDAO($dbConn);
$adminDAO = new AdministrationDAO($dbConn);


$postREPID = (isset($_POST['REPID']) && is_numeric($_POST['REPID'])) ? $_POST['REPID'] : false;
if (isset($_POST['DMLTYPE'])) $postDMLTYPE = $_POST['DMLTYPE'];
else if (isset($_GET['DMLTYPE'])) $postDMLTYPE = $_GET['DMLTYPE'];


if ($postREPID === false) {

  //CREATE
  $postREPID = false;
  $postREPCODE = '';
  $postFIRSTNAME = '';
  $postSURNAME = '';
  $postIDENTITYNUMBER = '';
  $postEMAILADDR = '';
  $postMOBILENUMBER = '';
  $postALTERNATECONTACTNUMBER = '';
  $postSHIPTOADDRESS1 = '';
  $postSHIPTOADDRESS2 = '';
  $postSHIPTOADDRESS3 = '';
  $postSALESTARGET = '';
  $postSTATUS = FLAG_STATUS_ACTIVE;

} else {

  //MODIFY | VIEW
  $repArr = $storeDAO->getPrincipalSalesRepItem($postREPID, $principalId);

  if (!count($repArr) > 0) {
    echo "<h2>Principal Sales Rep does not exist</h2>";
    return;
  } else {

    //UPDATE
    $postREPID = $repArr[0]['uid'];
    $postREPCODE = $repArr[0]['rep_code'];
    $postFIRSTNAME = $repArr[0]['first_name'];
    $postSURNAME = $repArr[0]['surname'];
    $postIDENTITYNUMBER = $repArr[0]['identity_number'];
    $postEMAILADDR = $repArr[0]['email_addr'];
    $postMOBILENUMBER = $repArr[0]['mobile_number'];
    $postALTERNATECONTACTNUMBER = $repArr[0]['alternate_contact_number'];
    $postSHIPTOADDRESS1 = $repArr[0]['shipto_address1'];
    $postSHIPTOADDRESS2 = $repArr[0]['shipto_address2'];
    $postSHIPTOADDRESS3 = $repArr[0]['shipto_address3'];
    $postSALESTARGET = $repArr[0]['sales_target'];
    $postSTATUS = $repArr[0]['status'];
    $postUPDATEDBY = $repArr[0]['last_update_user_name'] . ' @ ' . $repArr[0]['last_update_datetime'] . ' ('.$repArr[0]['last_update_user_uid'].')';
    $postCREATEDBY = $repArr[0]['created_by_user_name'] . ' @ ' . $repArr[0]['created_datetime'] . ' ('.$repArr[0]['created_by_user_uid'].')';

  }
}

if (!CommonUtils::isAdminUser() && !CommonUtils::isStaffUser()){

  //CHECK ROLES
  switch ($postDMLTYPE) {
    case "INSERT" :
      {
        $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_ADD_PRINCIPAL_SALES_REP);
        break;
      }
    case "UPDATE" :
      {
        $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_MODIFY_PRINCIPAL_SALES_REP);
        break;
      }
    case "VIEW" :
      {
        $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_VIEW_PRINCIPAL_SALES_REP);
        break;
      }
    default :
      $hasRole = false;
  }

  if (!CommonUtils::isAdminUser() && !CommonUtils::isStaffUser() && ($hasRole!==true)){
     echo 'You do not have permissions to ' , $postDMLTYPE , ' a Principal Sales Rep.';
     return;
  }

}



#--------------------------------------------------------------------------------------------------------------------------

//FORM OUTPUT

#--------------------------------------------------------------------------------------------------------------------------


$class = 'odd';
echo "<BR>";
if (($postDMLTYPE=="UPDATE") || ($postDMLTYPE=="VIEW")) {
  echo '<div align="center"><INPUT type="submit" class="submit" value="Back to Sales Reps" onClick="backToSalesRepsList()"></DIV><BR>';
}
echo '<INPUT type="hidden" id="REPID" value="' . $postREPID . '" />';
echo "<TABLE width='500' STYLE='line-height:22px;' id='repForm'>";
echo "<thead><tr>";
  echo '<th colspan="2">', mb_convert_case($postDMLTYPE, MB_CASE_TITLE), ' principal sales representative</th>';
echo "</tr></thead>";
echo '<tbody>';
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td width='130'>Rep Code: </td>";
  echo "<td><INPUT type='text' maxlength='10' size='10' id='REPCODE' value='" . $postREPCODE . "' /></td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td>Identity No.: </td>";
  echo "<td><INPUT type='text' maxlength='13' size='13' id='IDENTITYNUMBER' value='" . $postIDENTITYNUMBER . "' /></td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td>First Name: </td>";
  echo "<td><INPUT type='text' maxlength='100' size='50' id='FIRSTNAME' value='" . $postFIRSTNAME . "' /></td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td>Surname: ",GUICommonUtils::requiredField(),"</td>";
  echo "<td><INPUT type='text' maxlength='100' size='50' id='SURNAME' value='" . $postSURNAME . "' /></td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td>E-mail Address: ",GUICommonUtils::requiredField(),"</td>";
  echo "<td><INPUT type='text' maxlength='100' size='50' id='EMAILADDR' value='" . $postEMAILADDR . "' /></td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td>Mobile No.: </td>";
  echo "<td><INPUT type='text' maxlength='3' size='3' id='MOBILENUMBER1' value='" . substr($postMOBILENUMBER,0,3) . "' /> <INPUT type='text' maxlength='7' size='7' id='MOBILENUMBER2' value='" . substr($postMOBILENUMBER,3,10) . "' /></td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td>Alternate Contact No.:</td>";
  echo "<td><INPUT type='text' maxlength='3' size='3' id='ALTERNATECONTACTNUMBER1' value='" . substr($postALTERNATECONTACTNUMBER,0,3) . "' /> <INPUT type='text' maxlength='7' size='7' id='ALTERNATECONTACTNUMBER2' value='" . substr($postALTERNATECONTACTNUMBER,3,10) . "' /></td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td valign='top'>Ship to Address: </td>";
  echo "<td>
          <INPUT type='text' maxlength='50' size='30' id='SHIPTOADDRESS1' value='" . $postSHIPTOADDRESS1 . "' /><br>
          <INPUT type='text' maxlength='50' size='30' id='SHIPTOADDRESS2' value='" . $postSHIPTOADDRESS2 . "' /><br>
          <INPUT type='text' maxlength='50' size='30' id='SHIPTOADDRESS3' value='" . $postSHIPTOADDRESS3 . "' /><br>
        </td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
echo "<td>Sales Target:</td>";
echo "<td><INPUT type='text' maxlength='12' size='12' id='SALESTARGET' value='" . $postSALESTARGET . "' /></td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td>Status: </td>";
  echo "<td>";
    BasicInputElement::getCSS3RadioHorizontal("STATUS","Active,Deleted",FLAG_STATUS_ACTIVE.",".FLAG_STATUS_DELETED,$postSTATUS);
  echo "</td>";
echo "</tr>";


//meta information
if($postDMLTYPE!='INSERT'){
  echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
    echo "<td>Updated by:</td>";
    echo "<td style='color:#555;'>{$postUPDATEDBY}</td>";
  echo "</tr>";
  echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
    echo "<td>Created by:</td>";
    echo "<td style='color:#555;'>{$postCREATEDBY}</td>";
  echo "</tr>";
}

echo "</tbody>";
echo "</TABLE><br/>";

if ($postDMLTYPE != "VIEW") {
  echo "<INPUT type='button' class='submit' onclick='submitForm(\"" . $postDMLTYPE . "\");' value=' Submit ' />";
}

#--------------------------------------------------------------------------------------------------------------------------

?>
<script type='text/javascript' defer>

var alreadySubmitted=false;

function submitForm(p_type) {

    if (alreadySubmitted) {
      return;
    }
    alreadySubmitted=true;

    var params='DMLTYPE='+p_type;
    params+='&REPID='+document.getElementById("REPID").value;
    params+='&REPCODE='+document.getElementById("REPCODE").value;
    params+='&FIRSTNAME='+document.getElementById("FIRSTNAME").value;
    params+='&SURNAME='+document.getElementById("SURNAME").value;
    params+='&IDENTITYNUMBER='+document.getElementById("IDENTITYNUMBER").value;
    params+='&EMAILADDR='+document.getElementById("EMAILADDR").value;
    params+='&MOBILENUMBER='+document.getElementById("MOBILENUMBER1").value + document.getElementById("MOBILENUMBER2").value;
    params+='&ALTERNATECONTACTNUMBER='+document.getElementById("ALTERNATECONTACTNUMBER1").value + document.getElementById("ALTERNATECONTACTNUMBER2").value;
    params+='&SHIPTOADDRESS1='+document.getElementById("SHIPTOADDRESS1").value;
    params+='&SHIPTOADDRESS2='+document.getElementById("SHIPTOADDRESS2").value;
    params+='&SHIPTOADDRESS3='+document.getElementById("SHIPTOADDRESS3").value;
    params+='&SALESTARGET='+document.getElementById("SALESTARGET").value;
    params+='&STATUS='+convertElementToArray(document.getElementsByName("STATUS"));
    params = params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element

    AjaxRefreshWithResult(params,
                          '<?php  echo $ROOT . $PHPFOLDER ?>functional/salesrep/salesRepSubmit.php',
                          'alreadySubmitted=false;  if (msgClass.type=="S") successCallback("'+p_type+'");',
                          'Request is processed...');

}

function successCallback(p_type) {

  if (p_type=="INSERT") {

      document.getElementById("REPCODE").value = '';
      document.getElementById("FIRSTNAME").value = '';
      document.getElementById("SURNAME").value = '';
      document.getElementById("IDENTITYNUMBER").value = '';
      document.getElementById("EMAILADDR").value = '';
      document.getElementById("MOBILENUMBER1").value = '';
      document.getElementById("MOBILENUMBER2").value = '';
      document.getElementById("ALTERNATECONTACTNUMBER1").value = '';
      document.getElementById("ALTERNATECONTACTNUMBER2").value = '';
      document.getElementById("SHIPTOADDRESS1").value = '';
      document.getElementById("SHIPTOADDRESS2").value = '';
      document.getElementById("SHIPTOADDRESS3").value = '';
      document.getElementById("SALESTARGET").value = '';

  }
}

<?php
  //disable all form elements when viewing.
  if ($postDMLTYPE == "VIEW") { ?>
  $('#repForm :input').attr("disabled", true);
<?php } ?>

</script>

<?php


include_once ('ROOT.php');
include_once ($ROOT . 'PHPINI.php');
include_once ($ROOT . $PHPFOLDER . 'functional/main/access_control.php');
include_once ($ROOT . $PHPFOLDER . 'libs/common.php');
include_once ($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/DepotDAO.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/MiscellaneousDAO.php');



//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();
$miscellaneousDAO = new MiscellaneousDAO($dbConn);
$depotDAO = new DepotDAO($dbConn);
$adminDAO = new AdministrationDAO($dbConn);
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION["user_id"];
$postGETCONTACTID = (isset($_POST['CONTACTID']) && is_numeric($_POST['CONTACTID'])) ? $_POST['CONTACTID'] : false;



if (isset($_POST['DMLTYPE'])) $postDMLTYPE = $_POST['DMLTYPE'];
else if (isset($_GET['DMLTYPE'])) $postDMLTYPE = $_GET['DMLTYPE'];
if (! isset($_SESSION)) session_start();



if ($postGETCONTACTID === false) {

  //CREATE
  $postEMAILADDR = (isset($_POST['PRINNAME'])) ? $_POST['PRINNAME'] : "";
  $postMOBILENO = (isset($_POST['PRINNAME'])) ? $_POST['PRINNAME'] : "";
  $postCONTACTTYPE = (isset($_POST['PRINNAME'])) ? $_POST['PRINNAME'] : "";
  $postDEPOT = (isset($_POST['PRINNAME'])) ? $_POST['PRINNAME'] : "";
  $postFTPHOST = '';
  $postFTPUSR = '';
  $postFTPPWD = '';
  $postFTPFOLDER = '';
  $postFTPPORT = 21;
  $postFTPMODE = 1;

} else {

  //MODIFY | VIEW
  $ctI = $miscellaneousDAO->getContactItem($principalId, "", $postGETCONTACTID);

  if (!count($ctI) > 0) {
    echo "<h2>Principal Contact does not exist.</h2>";
    return;
  } else {

    $postEMAILADDR = $ctI[0]['email_addr'];
    $postMOBILENO = $ctI[0]['mobile_number'];
    $postCONTACTTYPE = $ctI[0]['contact_type_uid'];
    $postDEPOT = $ctI[0]['depot_uid'];

    $FTPArr = unserialize($ctI[0]['ftp_addr']);
    //INCLUDE INVALID SERIAL/ARRAY VALUE HANDLING
    $postFTPHOST = (isset($FTPArr['HOST'])) ? ($FTPArr['HOST']) : ('');
    $postFTPUSR = (isset($FTPArr['USR'])) ? ($FTPArr['USR']) : ('');
    $postFTPPWD = (isset($FTPArr['PWD'])) ? ($FTPArr['PWD']) : ('');
    $postFTPFOLDER = (isset($FTPArr['FOLDER'])) ? ($FTPArr['FOLDER']) : ('');
    $postFTPPORT = (isset($FTPArr['PORT'])) ? ($FTPArr['PORT']) : (21);
    $postFTPMODE = (isset($FTPArr['MODE'])) ? ($FTPArr['MODE']) : (1);

  }
}

//CHECK ROLES

switch ($postDMLTYPE) {
  case "INSERT" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_ADD_PRINCIPAL_CONTACT);
      break;
    }
  case "UPDATE" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_MODIFY_PRINCIPAL_CONTACT);
      break;
    }
  case "VIEW" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_VIEW_PRINCIPAL_CONTACT);
      break;
    }
  default :
    $hasRole = false;
}

$adminUser = (isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
$staffUser = (isset($_SESSION['staff_user']) && ($_SESSION['staff_user']=="Y"))?true:false;
if ((!$adminUser) && ($hasRole!==true)) {
   echo 'You do not have permissions to ' , $postDMLTYPE , ' a Principal Contact.';
   return;
}

if($postGETCONTACTID === false && $postDMLTYPE != 'INSERT'){
  die('Please select a Principal Contact to Update!');
}


#--------------------------------------------------------------------------------------------------------------------------

//FORM OUTPUT

#--------------------------------------------------------------------------------------------------------------------------


$class = 'odd';
echo "<BR>";
echo '<INPUT type="hidden" id="CONTACTUID" value="' . $postGETCONTACTID . '" />';
echo "<TABLE>";
echo "<thead><tr>";
  echo '<th colspan="2">', mb_convert_case($postDMLTYPE, MB_CASE_TITLE), ' principal contact</th>';
echo "</tr></thead>";
echo '<tbody>';
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td>Contact Type: ",GUICommonUtils::requiredField(),"</td>";
  echo "<td>";

  //CONTACT TYPES
  $ctArr = $miscellaneousDAO->getContactTypesArray();
  echo '<SELECT id="CONTACTTYPE" style="width:200px;">';
    echo '<option value="" style="color:#999">Select Contact Type...</option>';
  foreach($ctArr as $ct){
    $s = ($postCONTACTTYPE == $ct['uid']) ? ('SELECTED') : ('');
    echo '<option value="' , $ct['uid'] , '" '.$s.'>' , $ct['name'] , '</option>';
  }
  echo '</SELECT>';

  echo "</td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td>E-mail Address: ",GUICommonUtils::requiredField(),"</td>";
  echo "<td><INPUT type='text' style='width:200px;' maxlength='80' id='EMAILADDR' value='" . $postEMAILADDR . "' /></td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td>Mobile Number:</td>";
  echo "<td><INPUT type='text' size='12' maxlength='12' id='MOBILENO' value='" . $postMOBILENO . "' /></td>";
echo "</tr>";
echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td title='Allocate this user to a specific depot, or leave on All Depots'>Allocated Depot:</td>";
  echo "<td>";

  //ALLOCATION DEPOT
  $depotsArr = $depotDAO->getAllDepotsArray();  //a.uid, a.code, a.name depot_name
  echo '<SELECT id="DEPOT" style="width:200px;">';
    echo '<option value="" style="color:#999">All Depots</option>';
  foreach($depotsArr as $depotItem){
    $s = ($postDEPOT == $depotItem['uid']) ? ('SELECTED') : ('');
    echo '<option value="',$depotItem['uid'],'" '.$s.'>',$depotItem['code'],' - ',$depotItem['depot_name'],'</option>';
  }
  echo '</SELECT>';

  echo "</td>";
echo "</tr>";


//FTP SETTINGS
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
  echo '<TD height="25">FTP Host/Server:</TD>';
  echo "<TD><INPUT TYPE='TEXT' id='FTPHOST' value='".$postFTPHOST."' maxlength='30' size='30'> <span style='".FONT_UNOBTRUSIVE_INFO."'>eg: retailtrading.net, ftp.example.com</span></TD>";
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
   echo '<TD height="25">FTP Username:</TD>';
   echo "<TD><INPUT TYPE='TEXT' id='FTPUSR' value='".$postFTPUSR."' maxlength='30' size='30'></TD>";
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
   echo '<TD height="25">FTP Password:</TD>';
   echo "<TD><INPUT TYPE='" . ((!$staffUser && !$adminUser)?('password'):('TEXT')) . "' id='FTPPWD' value='".$postFTPPWD."' maxlength='30' size='30'></TD>";
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
   echo '<TD height="25">FTP Folder: </TD>';
   echo "<TD><INPUT TYPE='TEXT' id='FTPFOLDER' value='".$postFTPFOLDER."' maxlength='30' size='30'> <span style='".FONT_UNOBTRUSIVE_INFO."'>eg: reports/directory</span></TD>";
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
   echo '<TD height="25">FTP Port:</TD>';
   echo "<TD><INPUT TYPE='TEXT' id='FTPPORT' value='".$postFTPPORT."'  maxlength='5' size='5'> <span style='".FONT_UNOBTRUSIVE_INFO."'>Default 21</span></TD>";
echo '</TR>';
echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
   echo '<TD height="25">FTP Mode:</TD>';

   echo "<TD id='F_FTP_MODE' height='25'>";
   BasicInputElement::getGeneralHorizontalRB('FTPMODE', 'Passive,Active','1,0', $postFTPMODE, 'N', 'N', NULL, NULL, NULL);
   echo "</TD>";
echo '</TR>';

echo "</tbody>";
echo "</TABLE><br />";

if (($postDMLTYPE == "INSERT") || ($postDMLTYPE == "UPDATE")) {
  echo "<INPUT type='button' class='submit' onclick='submitContentForm(\"" . $postDMLTYPE . "\");' value='Submit Contact' />";
  if($postDMLTYPE == "UPDATE"){
    echo "<INPUT type='button' class='submit' onclick='submitContentForm(\"DELETE\");' value='Delete Contact' />";
  }
}

#--------------------------------------------------------------------------------------------------------------------------


$dbConn->dbClose();

?>
<script type='text/javascript' defer>

var alreadySubmitted=false;

function submitContentForm(p_type) {
	if (alreadySubmitted) {
		return;
	}
	alreadySubmitted=true;

	var params='DMLTYPE='+p_type;

	params+='&CONTACTUID='+document.getElementById("CONTACTUID").value;
	params+='&EMAILADDR='+document.getElementById("EMAILADDR").value;
	params+='&MOBILENO='+document.getElementById("MOBILENO").value;
	params+='&DEPOT='+document.getElementById("DEPOT").value;
	params+='&CONTACTTYPE='+document.getElementById("CONTACTTYPE").value;

	var FTPHOST = encodeURIComponent(document.getElementById("FTPHOST").value.replace(/'/g,''));

	if(FTPHOST != ""){
  		//FTP DETAILS IF SELECTED
	  	params+='&FTPHOST='+FTPHOST;
  		params+='&FTPUSR='+encodeURIComponent(document.getElementById("FTPUSR").value.replace(/'/g,''));
  		params+='&FTPPWD='+encodeURIComponent(document.getElementById("FTPPWD").value.replace(/'/g,''));
  		params+='&FTPFOLDER='+encodeURIComponent(document.getElementById("FTPFOLDER").value.replace(/'/g,''));
  		params+='&FTPPORT='+encodeURIComponent(document.getElementById("FTPPORT").value.replace(/'/g,''));
  		params+='&FTPMODE='+convertElementToArray(document.getElementsByName('FTPMODE'));
	}

	params = params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php  echo $ROOT . $PHPFOLDER ?>functional/contact/contactSubmit.php',
						  'alreadySubmitted=false; successCallback();  if (msgClass.type=="S") successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
						  'Please wait while request is processed...');
}

function successCallback(p_type) {

	if (p_type=="INSERT") {
		document.getElementById("CONTACTUID").value='';
		document.getElementById("EMAILADDR").value='';
		document.getElementById("MOBILENO").value='';
		document.getElementById("DEPOT").options[0].selected = true;
		document.getElementById("CONTACTTYPE").options[0].selected = true;

	} else if(p_type == "UPDATE") {
	  displayContactTable();
	} else if(p_type == "DELETE") {
	  displayContactTable();
	  nextStep(1);
	}
}

function errorCallback(p_type) {
}
</script>

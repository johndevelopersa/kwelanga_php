<?php

include_once ('ROOT.php');
include_once ($ROOT . 'PHPINI.php');
include_once ($ROOT . $PHPFOLDER . 'functional/main/access_control.php');
include_once ($ROOT . $PHPFOLDER . 'libs/common.php');
include_once ($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once ($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/PostPrincipalDAO.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/PrincipalDAO.php');
include_once ($ROOT . $PHPFOLDER . 'TO/PrincipalTO.php');
include_once ($ROOT . $PHPFOLDER . 'TO/PostingPrincipalTO.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
include_once ($ROOT . $PHPFOLDER . 'elements/basicSelectElement.php');
CommonUtils::getSystemConventions();


//Create new database object
$dbConn = new dbConnect();
//Database connection method
$dbConn->dbConnection();

if (isset($_POST['DMLTYPE'])) $postDMLTYPE = $_POST['DMLTYPE'];
else if (isset($_GET['DMLTYPE'])) $postDMLTYPE = $_GET['DMLTYPE'];
if (! isset($_SESSION)) session_start();
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION["user_id"];

//Debug Param PrincipalID
//echo $aPrincipalId;
$postLOADPRINID = (isset($_POST['PRINID']) && is_numeric($_POST['PRINID'])) ? $_POST['PRINID'] : false;

if ($postLOADPRINID === false) {

  //CREATE
  $postPRINNAME = (isset($_POST['PRINNAME'])) ? $_POST['PRINNAME'] : "";
  $postPRINCODE = (isset($_POST['PRINCODE'])) ? $_POST['PRINCODE'] : "";
  $postALTPRINCODE = (isset($_POST['ALTPRINCODE'])) ? $_POST['ALTPRINCODE'] : "";
  $postEMAIL = (isset($_POST['EMAIL'])) ? $_POST['EMAIL'] : "";
  $postPHYAD1 = (isset($_POST['PHYAD1'])) ? $_POST['PHYAD1'] : "";
  $postPHYAD2 = (isset($_POST['PHYAD2'])) ? $_POST['PHYAD2'] : "";
  $postPHYAD3 = (isset($_POST['PHYAD3'])) ? $_POST['PHYAD3'] : "";
  $postPHYAD4 = (isset($_POST['PHYAD4'])) ? $_POST['PHYAD4'] : "";
  $postPSTAD1 = (isset($_POST['PSTAD1'])) ? $_POST['PSTAD1'] : "";
  $postPSTAD2 = (isset($_POST['PSTAD2'])) ? $_POST['PSTAD2'] : "";
  $postPSTAD3 = (isset($_POST['PSTAD3'])) ? $_POST['PSTAD3'] : "";
  $postPSTAD4 = (isset($_POST['PSTAD4'])) ? $_POST['PSTAD4'] : "";
  $postVATNO = (isset($_POST['VATNO'])) ? $_POST['VATNO'] : "";
  $postRTTACC = (isset($_POST['RTTACC'])) ? $_POST['RTTACC'] : "";
  $postPRINGLN = (isset($_POST['PRINGLN'])) ? $_POST['PRINGLN'] : "";
  $postCTCPER = (isset($_POST['CTCPER'])) ? $_POST['CTCPER'] : "";
  $postOTEL = (isset($_POST['OTEL'])) ? $_POST['OTEL'] : "";
  $postBANKDET = (isset($_POST['BANKDET'])) ? $_POST['BANKDET'] : "";
  $postPTYPE = (isset($_POST['PTYPE'])) ? $_POST['PTYPE'] : "";
  $postPRINID = '';  //only used for UPDATE, needs to be int
  $postPRINUPLIFTCODE = (isset($_POST['PRINUPLIFTCODE'])) ? $_POST['PRINUPLIFTCODE'] : "";
  $postSTATUS = (isset($_POST['STATUS'])) ? $_POST['STATUS'] : FLAG_STATUS_ACTIVE;
  $postEXPORTNUMBER = (isset($_POST['EXPORTNUMBER'])) ? $_POST['EXPORTNUMBER'] : "";
  $postCHARGE = (isset($_POST['CHARGE'])) ? $_POST['CHARGE'] : ("N");
$postDOCCHARGE = (isset($_POST['DOCCHARGE'])) ? $_POST['DOCCHARGE'] : ("N");
  $postCANCELCHARGE = (isset($_POST['CANCELCHARGE'])) ? $_POST['CANCELCHARGE'] : ("N");
  $postDEBTORCHARGE = (isset($_POST['DEBTORCHARGE'])) ? $_POST['DEBTORCHARGE'] : ("N");

} else {

  //MODIFY | VIEW


  //Get Principal Data.
  $PrincipalDAO = new PrincipalDAO($dbConn);
  $SelectPrin = $PrincipalDAO->getPrincipalItem($postLOADPRINID);

  if (! count($SelectPrin) > 0) {
    echo "Principal does not exist.";
    return;
  } else {

    //Debug Principal Data
    //var_dump($SelectPrin);


    $SelectPrin = $SelectPrin[0];

    $postPRINNAME = $SelectPrin['principal_name'];
    $postPRINCODE = $SelectPrin['principal_code'];
    $postALTPRINCODE = $SelectPrin['alt_principal_code'];
    $postEMAIL = $SelectPrin['email_add'];
    $postPHYAD1 = $SelectPrin['physical_add1'];
    $postPHYAD2 = $SelectPrin['physical_add2'];
    $postPHYAD3 = $SelectPrin['physical_add3'];
    $postPHYAD4 = $SelectPrin['physical_add4'];
    $postPSTAD1 = $SelectPrin['postal_add1'];
    $postPSTAD2 = $SelectPrin['postal_add2'];
    $postPSTAD3 = $SelectPrin['postal_add3'];
    $postPSTAD4 = $SelectPrin['postal_add4'];
    $postVATNO = $SelectPrin['vat_num'];
    $postRTTACC = $SelectPrin['rt_acc_num'];
    $postPRINGLN = $SelectPrin['principal_gln'];
    $postCTCPER = $SelectPrin['contactperson'];
    $postOTEL = $SelectPrin['office_tel'];
    $postBANKDET = $SelectPrin['banking_details'];
    $postPTYPE = $SelectPrin['principal_type'];
    $postPRINID = $SelectPrin['uid'];
    $postPRINUPLIFTCODE = $SelectPrin['principal_uplift_code'];
    $postSTATUS = $SelectPrin['status'];
    $postEXPORTNUMBER = $SelectPrin['export_number'];
    $postCHARGE = $SelectPrin['charge'];
    $postDOCCHARGE = $SelectPrin['doc_charge'];
    $postCANCELCHARGE = $SelectPrin['cancelled'];
    $postDEBTORCHARGE = $SelectPrin['turnover'];

  }
}

//CHECK ROLES
$adminDAO = new AdministrationDAO($dbConn);
switch ($postDMLTYPE) {
  case "INSERT" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_ADD_PRINCIPAL);
      break;
    }
  case "UPDATE" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_MODIFY_PRINCIPAL);
      break;
    }
  case "VIEW" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_VIEW_PRINCIPAL);
      break;
    }
  default :
    $hasRole = false;
}
if (! $hasRole) {
  echo 'You do not have permissions to ' , $postDMLTYPE , ' a Principal.';
  return;
}

#--------------------------------------------------------------------------------------------------------------------------

//FORM OUTPUT
$class = 'odd';

echo "<BR>";
echo '<INPUT type="hidden" id="PRINID" value="' . $postPRINID . '" />';
echo "<TABLE>";
echo "<thead><tr>";
echo '<th colspan="2">', mb_convert_case($postDMLTYPE, MB_CASE_TITLE), ' new ' . strtolower(SNC::principal) . '</th>';
echo "</tr></thead>";
echo '<tbody>';
echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Name " , GUICommonUtils::requiredField() , "</td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='PRINNAME' value='" . $postPRINNAME . "' /></td>";
echo "</tr>";
echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">" . SNC::principal . " Code " , GUICommonUtils::requiredField() , "</td>";
echo "<td><INPUT type='text' size='3' maxlength='3' id='PRINCODE' value='" . $postPRINCODE . "' ". (($postDMLTYPE!='INSERT')?('DISABLED'):(''))." /></td>";
echo "</tr>";
echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">" . SNC::principal . " Uplift Code </td>";
echo "<td><INPUT type='text' size='3' maxlength='3' id='PRINUPLIFTCODE' value='" . $postPRINUPLIFTCODE . "' /></td>";
echo "</tr>";
echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Alternate " . SNC::principal . " Code</td>";
echo "<td><INPUT type='text' size='3' maxlength='3' id='ALTPRINCODE' value='" . $postALTPRINCODE . "' ". (($postDMLTYPE!='INSERT')?('DISABLED'):(''))."/></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Physical Address</td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='PHYAD1' value='" . $postPHYAD1 . "' /></td>";
echo "</tr>";
echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\"></td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='PHYAD2' value='" . $postPHYAD2 . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\"></td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='PHYAD3' value='" . $postPHYAD3 . "' /></td>";
echo "</tr>";
echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Area Code</td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='PHYAD4' value='" . $postPHYAD4 . "' /></td>";
echo "</tr>";

// Postal Address
echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Postal Address</td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='PSTAD1' value='" . $postPSTAD1 . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\"></td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='PSTAD2' value='" . $postPSTAD2 . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\"></td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='PSTAD3' value='" . $postPSTAD3 . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Area Code</td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='PSTAD4' value='" . $postPSTAD4 . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">VAT No# " , GUICommonUtils::requiredField() , "</td>";
echo "<td><INPUT type='text' size='15' maxlength='14' id='VATNO' value='" . $postVATNO . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">GLN No#</td>";
echo "<td><INPUT type='text' size='15' maxlength='15' id='PRINGLN' value='" . $postPRINGLN . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">RT Acc No#</td>";
echo "<td><INPUT type='text' size='5' maxlength='5' id='RTTACC' value='" . $postRTTACC . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Contact Person/s</td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='CTCPER' value='" . $postCTCPER . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Email Address " , GUICommonUtils::requiredField() , "</td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='EMAIL' value='" . $postEMAIL . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Office Contact No#</td>";
echo "<td><INPUT type='text' size='50' maxlength='50' id='OTEL' value='" . $postOTEL . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Banking Details</td>";
echo "<td><INPUT type='text' size='50' maxlength='255' id='BANKDET' value='" . $postBANKDET . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">" . SNC::principal . " Type</td>";
echo "<td>";
BasicInputElement::getGeneralHorizontalRB("PTYPE", SNC::principal.",Sales Agent", PT_PRINCIPAL . "," . PT_SALES_AGENT, $postPTYPE, "N", (($postDMLTYPE!='INSERT')?('Y'):('N')), null, null, null);
echo "</td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Export Number</td>";
echo "<td><INPUT type='text' size='20' maxlength='20' id='EXPORTNUMBER' value='" . $postEXPORTNUMBER . "' /></td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Charge " . SNC::principal . " </td>";
echo "<td>";
BasicInputElement::getGeneralHorizontalRB('CHARGE',"Yes,No","Y,N",$postCHARGE,"N","N",null,null,null); 
echo "</td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Document Charge</td>";
echo "<td>";
BasicInputElement::getGeneralHorizontalRB('DOCCHARGE',"Yes,No","Y,N",$postDOCCHARGE,"N","N",null,null,null); 
echo "</td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Cancel Charge </td>";
echo "<td>";
BasicInputElement::getGeneralHorizontalRB('CANCELCHARGE',"Yes,No","Y,N",$postCANCELCHARGE,"N","N",null,null,null); 
echo "</td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Charge Turnover</td>";
echo "<td>";
BasicInputElement::getGeneralHorizontalRB('DEBTORCHARGE',"Yes,No","Y,N",$postDEBTORCHARGE,"N","N",null,null,null); 
echo "</td>";
echo "</tr>";

echo "<tr class=\"".GUICommonUtils::styleEO($class)."\">";
echo "<td bgcolor=\"#87CEFA\">Status</td>";
echo "<td>";
BasicInputElement::getCSS3RadioHorizontal("STATUS", "Active,Suspended",FLAG_STATUS_ACTIVE.",".FLAG_STATUS_SUSPENDED, $postSTATUS);
echo "</td>";
echo "</tr>";

echo "</tbody>";
echo "</TABLE><br />";

if (($postDMLTYPE == "INSERT") || ($postDMLTYPE == "UPDATE")) {
  echo "<INPUT type='button' class='submit' onclick='submitContentForm(\"" . $postDMLTYPE . "\");' value='Submit Principal' />";
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
	params+='&PRINID='+document.getElementById("PRINID").value;
	params+='&PRINNAME='+document.getElementById("PRINNAME").value;
	params+='&PRINCODE='+document.getElementById("PRINCODE").value;
  params+='&PRINUPLIFTCODE='+document.getElementById("PRINUPLIFTCODE").value;
	params+='&ALTPRINCODE='+document.getElementById("ALTPRINCODE").value;
	params+='&PHYAD1='+document.getElementById("PHYAD1").value;
	params+='&PHYAD2='+document.getElementById("PHYAD2").value;
	params+='&PHYAD3='+document.getElementById("PHYAD3").value;
	params+='&PHYAD4='+document.getElementById("PHYAD4").value;
	params+='&PSTAD1='+document.getElementById("PSTAD1").value;
	params+='&PSTAD2='+document.getElementById("PSTAD2").value;
	params+='&PSTAD3='+document.getElementById("PSTAD3").value;
	params+='&PSTAD4='+document.getElementById("PSTAD4").value;
	params+='&VATNO='+document.getElementById("VATNO").value;
	params+='&PRINGLN='+document.getElementById("PRINGLN").value;
	params+='&RTTACC='+document.getElementById("RTTACC").value;
	params+='&EMAIL='+document.getElementById("EMAIL").value;
	params+='&CTCPER='+document.getElementById("CTCPER").value;
	params+='&OTEL='+document.getElementById("OTEL").value;
	params+='&BANKDET='+document.getElementById("BANKDET").value;
	params+='&PTYPE='+convertElementToArray(document.getElementsByName("PTYPE"));
	params+='&STATUS='+convertElementToArray(document.getElementsByName("STATUS"));
	params+='&EXPORTNUMBER='+document.getElementById("EXPORTNUMBER").value;
  params+='&CHARGE='+convertElementToArray(document.getElementsByName("CHARGE"));
  params+='&DOCCHARGE='+convertElementToArray(document.getElementsByName("DOCCHARGE"));
  params+='&CANCELCHARGE='+convertElementToArray(document.getElementsByName("CANCELCHARGE"));
  params+='&DEBTORCHARGE='+convertElementToArray(document.getElementsByName("DEBTORCHARGE"));

	params = params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php  echo $ROOT . $PHPFOLDER ?>functional/principals/principalSubmit.php',
						  'alreadySubmitted=false; successCallback();  if (msgClass.type=="S") successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
						  'Please wait while request is processed...');
}

function successCallback(p_type) {
	toggleSteps(1,"<?php echo $ROOT . $PHPFOLDER ?>");
	if (p_type=="INSERT") {
		document.getElementById("PRINID").value='';
		document.getElementById("PRINNAME").value='';
		document.getElementById("PRINCODE").value='';
		document.getElementById("ALTPRINCODE").value='';
		document.getElementById("PHYAD1").value='';
		document.getElementById("PHYAD2").value='';
		document.getElementById("PHYAD3").value='';
		document.getElementById("PHYAD4").value='';
		document.getElementById("PSTAD1").value='';
		document.getElementById("PSTAD2").value='';
		document.getElementById("PSTAD3").value='';
		document.getElementById("PSTAD4").value='';
		document.getElementById("VATNO").value='';
		document.getElementById("VATNO").value='';
		document.getElementById("PRINGLN").value='';
		document.getElementById("EMAIL").value='';
		document.getElementById("CTCPER").value='';
		document.getElementById("OTEL").value='';
		document.getElementById("BANKDET").value='';
		document.getElementById("EXPORTNUMBER").value='';    
	}
}

function errorCallback(p_type) {
}
</script>

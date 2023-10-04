<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');



//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();


if(!isset($_SESSION)) session_start;

$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$postDMLTYPE = (isset($_POST['DMLTYPE'])) ? $_POST['DMLTYPE'] : 'VIEW';
$dmliAr = array('INSERT' => 1, 'UPDATE' => 2, 'VIEW' => 3);
$postDMLTYPEint = isset($dmliAr[$postDMLTYPE])? $dmliAr[$postDMLTYPE] : 3;
$fldChosenContactRB = 'ChosenContact';
$divAjaxContactList = "ajaxContactList";
$divAjaxContactDetails = "ajaxContactDetails";
$adminDAO = new AdministrationDAO($dbConn);


print("&nbsp;"); // for some reason, the javascript does NOT work if this line is missing.


//CHECK ROLES

switch ($postDMLTYPE) {
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
if (!$hasRole) {
  echo 'You do not have permissions to ' , $postDMLTYPE , ' a Principal Contact.';
  return;
}


$adminUser = (isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
if ((!$adminUser) && ($hasRole!==true)) {
   echo 'You do not have permissions to ' , $postDMLTYPE , ' a Principal Contact.';
   return;
}

?>
    <SCRIPT type='text/javascript' defer>
	function refreshContactDetails(p_type) {
		var type;

		if (p_type==1) type="INSERT"; else if (p_type==2) type="UPDATE"; else if (p_type==3) type="VIEW";
		AjaxRefresh("CONTACTID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenContactRB ?>"))+"&DMLTYPE="+type,
					"<?php echo $ROOT.$PHPFOLDER ?>functional/contact/contactForm.php",
					"<?php echo $divAjaxContactDetails ?>",
					"Please wait whilst page is refreshed...",
					"");
	}
	function resetActionArea(){
		document.getElementById('<?php echo $divAjaxContactDetails; ?>').innerHTML='&nbsp;';
	}

	function submitForm(p_type) {
		// the submitContentForm is only available when userDetails.php has been called
		var userId=convertElementToArray(document.getElementsByName("<?php echo $fldChosenContactRB ?>"));
		if ((p_type=="UPDATE") && (userId=="")) {
			alert('Please select a user before Submitting...');
			return;
		}
		submitContentForm(p_type);
	}
	</SCRIPT>
<?php


    if($postDMLTYPE=='UPDATE'){
      GUICommonUtils::getSteps(array("Step 1:<BR>Select Contact","Step 2:<BR>Modify Contact"));
    } else {
      GUICommonUtils::getSteps(array("Step 1:<BR>Select Contact","Step 2:<BR>View Contact"));
    }


	print("<div id='step1'>");
    print("<img src='".$DHTMLROOT.$PHPFOLDER."images/downarrow.gif' onmouseover='$(this).css({cursor:\"hand\"});' onclick='$(\"#box1\").height(300);' /><img src='".$DHTMLROOT.$PHPFOLDER."images/uparrow.gif' onmouseover='$(this).css({cursor:\"hand\"});' onclick='$(\"#box1\").height(130);' />");
    if($postDMLTYPE=='UPDATE'){
      print("<span> Please choose a Contact to modify, or choose 'Add Contact'...</span>");
    } else {
      print("<span> Please choose a Contact to view...</span>");
    }
    print("<div id='box1' style='overflow:auto;'>"); // 3 rows data
	print("<div id='".$divAjaxContactList."'></div>");
    print("</div>");
	print("</div>");

	print("<div id='step2'>");
	print("<div id='".$divAjaxContactDetails."'>Please select a Principal Contact in Step 1 first.</div>");
	print("</div>");



$dbConn->dbClose();
?>
<script type='text/javascript' defer>

$("div[id*='step']").css({display:'none'});
$("#step1").css({display:'block'});
toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");

function nextStep(step) {
	toggleSteps(step,"<?php echo $ROOT.$PHPFOLDER ?>");
}

function displayContactTable(){
AjaxRefresh("RBNAME=<?php echo $fldChosenContactRB; ?>&CALLBACK=refreshContactDetails(<?php echo $postDMLTYPEint ?>); nextStep(2);",
	   "<?php echo $ROOT.$PHPFOLDER; ?>functional/contact/contactListTable.php",
	   "<?php echo $divAjaxContactList; ?>",
	   "Please wait whilst page is refreshed...",
	   "");

}
displayContactTable();
</script>
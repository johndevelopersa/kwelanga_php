<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
CommonUtils::getSystemConventions();

if (!isset($_SESSION)) session_start;

$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$adminUser = (isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y")) ? true:false;


$fldChosenUserRB='ChosenUser';  // field names for this form
$fldChosenPrincipalRB='ChosenPrincipal';
$fldChosenDepotRB='ChosenDepot';
$divAjaxUserList="ajaxUserList";  // the ajax divs. refreshed independently
$divAjaxPrincipalList="ajaxPrincipalList";
$divAjaxDepotList="ajaxDepotList";
$divAjaxUPDList="ajaxUPDList";


?>
<SCRIPT type='text/javascript' defer>
  function refreshUserPrincipals() {
  	<?php if ($adminUser) { ?>
  		AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>")),
  	<?php } else { ?>
  		AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&FILTERONPRINCIPAL=Y&FILTERPRINCIPALVALUE=<?php echo $_SESSION['principal_id'] ?>",
  	<?php   } ?>
              "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUserPrincipalDepotListTable.php",
              "<?php echo $divAjaxUPDList ?>",
              "Please wait whilst page is refreshed...",
              "");
  }

</SCRIPT>
<?php

function userList() {
	global $ROOT; global $PHPFOLDER; global $DHTMLROOT; global $divAjaxUserList; global $fldChosenUserRB; global $adminUser;
	print("<span style=''>Please choose a user to apply a ".strtolower(SNC::principal)." to...</span>");
	print("<div id='box1' style='overflow:auto;'>"); // 3 rows data
	print("<div id='".$divAjaxUserList."'></div>");
	print("<scr"."ipt type='text/javascript' defer>");
	// too many backslashes to put directly into callback of AjaxRefresh
	print("function ulCallback() {
		 	//refreshUserPrincipals();
			toggleSteps(2,\"{$ROOT}{$PHPFOLDER}\");
	       }");
	print("AjaxRefresh(\"RBTYPE=&RBNAME=".$fldChosenUserRB."&CALLBACK=ulCallback();\",
					   \"".$ROOT.$PHPFOLDER."functional/administration/adminUsersListTable.php\",
					   \"".$divAjaxUserList."\",
					   \"Please wait whilst page is refreshed...\",
					   \"\");");
	print("</scr"."ipt>");
	print("</div>");
}

function principalList() {
	global $ROOT; global $PHPFOLDER; global $DHTMLROOT; global $divAjaxPrincipalList; global $fldChosenPrincipalRB;

        $adminView = (CommonUtils::isStaffUser() || CommonUtils::isDepotUser() || CommonUtils::isAdminUser()) ? ("Y") : ("N");

	print("<span style=''>Please choose a ".strtolower(SNC::principal)." to apply for the user chosen...</span>");
	print("<div id='box2' style='overflow:auto;'>"); // 5 rows data
	print("<div id='".$divAjaxPrincipalList."'></div>");
	print("<scr"."ipt type='text/javascript' defer>");
	// too many backslashes to put directly into callback of AjaxRefresh
	print("function plCallback() {
			toggleSteps(3,\"{$ROOT}{$PHPFOLDER}\");
	       }");
	print("AjaxRefresh(\"RBNAME=".$fldChosenPrincipalRB."&ADMINVIEW=".$adminView."&CALLBACK=plCallback();\",
		   		   \"".$ROOT.$PHPFOLDER."functional/administration/adminPrincipalsListTable.php\",
		   		   \"".$divAjaxPrincipalList."\",
		   		   \"Please wait whilst page is refreshed...\",
		   		   \"\");");
	print("</scr"."ipt>");
	print("</div>");
}

function depotList() {
	global $ROOT; global $PHPFOLDER; global $DHTMLROOT; global $divAjaxDepotList; global $fldChosenDepotRB; global $adminUser; global $userId; global $principalId;
	print("<span style=''>Please choose a depot to apply for the ".strtolower(SNC::principal)." chosen ...</span>");
	print("<div id='box3' style='overflow:auto;'>");
	print("<div id='".$divAjaxDepotList."'></div>");
	print("<scr"."ipt type='text/javascript' defer>");
	if ($adminUser)
		print("AjaxRefresh(\"RBNAME=".$fldChosenDepotRB."&RBTYPE=tick&ADMINVIEW=Y\",");
	else
		print("AjaxRefresh(\"RBNAME=".$fldChosenDepotRB."&RBTYPE=tick&ADMINVIEW=N&USERID=".$userId."&PRINCIPALID=".$principalId."\",");
		   	print("\"".$ROOT.$PHPFOLDER."functional/administration/adminDepotsListTable.php\",
		   		   \"".$divAjaxDepotList."\",
		   		   \"Please wait whilst page is refreshed...\",
		   		   \"\");");
	print("</scr"."ipt>");
	print("</div>");
}

        echo "<br>";
	GUICommonUtils::getSteps(array("Step 1:<BR>Select User","Step 2:<BR>Select ".SNC::principal."","Step 2:<BR>Select Depots","Step 3:<BR>Submit Changes"));
	print("<div id='step1'>");
	userList();
	print("</div>");

	print("<div id='step2'>");
	print("<div id='".$divAjaxUPDList."'></div>");
        principalList();
	print("</div>");

	print("<div id='step3'>");
	depotList();
	print("</div>");

	print("<CENTER>");
	print("<div id='step4'><BR>");
	print("<input class='submit' type='submit' value='Add ".SNC::principal."-Depots to User' onclick='submitContentForm(\"INSERT\");'/>");
	print("<input class='submit' type='submit' value='Remove ".SNC::principal."-Depots from User' onclick='submitContentForm(\"DELETE\");'/>");
	print("</div>");
	print("</CENTER>");


?>
<script type='text/javascript' defer>
var alreadySubmitted=false;

$("div[id*='step']").css({display:'none'});
$("#step1").css({display:'block'});
toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");

function submitContentForm(p_type){
	if (alreadySubmitted){
		return;
	}
	alreadySubmitted=true;
	var params='DMLTYPE='+p_type;
	params+='&USERID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"));

	if (document.getElementsByName("<?php echo $fldChosenPrincipalRB ?>")!="undefined") {
		params+='&PRINCIPALID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenPrincipalRB ?>"));
	}



	if (document.getElementsByName("<?php echo $fldChosenDepotRB ?>")!="undefined") {
		params+='&DEPOTID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenDepotRB ?>"));
	}

	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUserPrincipalSubmit.php',
						  'alreadySubmitted=false; if (msgClass.type=="S") successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
						  'Please wait while request is processed...');
}

function successCallback(p_type) {
	toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");
}

function errorCallback(p_type) {
	toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");
}

</script>

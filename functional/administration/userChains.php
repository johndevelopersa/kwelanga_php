<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;


// field names for this form
$fldChosenUserRB='ChosenUser'; 
$fldChosenCRB='ChosenChain'; 

// the ajax divs. refreshed independently	
$divAjaxUserList="ajaxUserList"; 
$divAjaxCList="ajaxChainList"; 
$divAjaxUCList="ajaxUCList"; 

print("&nbsp;"); // for some reason, the javascript does NOT work if this line is missing.

?>
    <SCRIPT type='text/javascript' defer>
	function refreshUserChain() { 
		AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>")),
					"<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUserChainsListTable.php",
					"<?php echo $divAjaxUCList ?>",
					"Please wait whilst page is refreshed...",
					""); 
	}
	</SCRIPT> 
<?php
// the step icons
GUICommonUtils::getSteps(array("Step 1:<BR>Choose User","Step 2:<BR>Select Chain","Step 3:<BR>Submit Changes","View Current<BR>User Chains"));

print("<div id='step1'>");
print("<span style=''>Please choose a user to apply chain to...</span>");
print("<div id='box1' style='overflow:auto;'>"); // 3 rows data
print("<div id='".$divAjaxUserList."'></div>");
print("<scr"."ipt type='text/javascript' defer>");
print("AjaxRefresh(\"RBNAME=".$fldChosenUserRB."&CALLBACK=refreshUserChain();\",
				   \"".$ROOT.$PHPFOLDER."functional/administration/adminUsersListTable.php\",
				   \"".$divAjaxUserList."\",
				   \"Please wait whilst page is refreshed...\",
				   \"\");");
print("</scr"."ipt>");
print("</div>");
print("</div>");

print("<div id='step2'>");
print("<span style=''>Please choose a Chain to apply to user ...</span>");
print("<div id='box2' style='overflow:auto;'>"); // 5 rows data
print("<div id='".$divAjaxCList."'></div>");
print("<scr"."ipt type='text/javascript' defer>");
if ($adminUser) {
	print("AjaxRefresh(\"RBNAME=".$fldChosenCRB."&ADMINVIEW=Y\",
					   \"".$ROOT.$PHPFOLDER."functional/administration/adminChainsListTable.php\",
					   \"".$divAjaxCList."\",
					   \"Please wait whilst page is refreshed...\",
					   \"\");");
} else {
	print("AjaxRefresh(\"RBNAME=".$fldChosenCRB."\",
					   \"".$ROOT.$PHPFOLDER."functional/administration/adminChainsListTable.php\",
					   \"".$divAjaxCList."\",
					   \"Please wait whilst page is refreshed...\",
					   \"\");");
  }					  					   
print("</scr"."ipt>");
print("</div>");
print("</div>");

print("<CENTER>");
print("<div id='step3'>");
print("<input class='submit' type='submit' value='Add Chain to User' onclick='submitContentForm(\"INSERT\");'/>");
print("<input class='submit' type='submit' value='Remove Chain from User' onclick='submitContentForm(\"DELETE\");'/>");
print("</div>");
print("</CENTER>");

print("<div id='step4'>");
print("<div id='".$divAjaxUCList."'></div>");
print("</div>");

?>
<script type='text/javascript' defer>
var alreadySubmitted=false;

$("div[id*='step']").css({display:'none'});
$("#step1").css({display:'block'});
toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");

function submitContentForm(p_type) {
	if (alreadySubmitted) {
		parent.showMsgBoxInfo(msg);
		return;
	}
	alreadySubmitted=true;
	var params='DMLTYPE='+p_type;
	params+='&USERID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"));
	params+='&CHAINUID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenCRB ?>"));
	
	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUserChainSubmit.php',
						  'alreadySubmitted=false; refreshUserChain();',
						  'Please wait while request is processed...');
}
</script>

<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalName = $_SESSION['principal_name'];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;


// field names for this form
$fldChosenUserRB='ChosenUser'; 
$fldChosenPRB='ChosenProduct'; 
$fldChosenPrincipal='ChosenPrincipal'; 

// the ajax divs. refreshed independently	
$divAjaxUserList="ajaxUserList"; 
$divAjaxPList="ajaxProductList"; 
$divAjaxUserPrincipalList="ajaxPrincipalList"; 
$divAjaxUPList="ajaxUPList"; 

print("&nbsp;"); // for some reason, the javascript does NOT work if this line is missing.

?>
    <SCRIPT type='text/javascript' defer>
    function refreshUserPrincipals() {
    	<?php if ($adminUser) { ?>
		AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&TAGID=<?php echo $fldChosenPrincipal; ?>&PRINCIPALID=<?php echo $principalId; ?>&ONCHANGE=refreshProduct(); refreshUserProduct(); nextStep(3);",
					"<?php echo $ROOT.$PHPFOLDER ?>elements/ajaxPrincipalDD.php",
					"<?php echo $divAjaxUserPrincipalList ?>",
					"Please wait whilst user principals are refreshed...",
					"refreshProduct(); refreshUserProduct();");
		<?php } else { ?>
			refreshProduct();
			refreshUserProduct();
		<?php   } ?>
	} 
	function refreshUserProduct() {
		<?php if ($adminUser) { ?>
		AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&PRINCIPALID="+document.getElementById('<?php echo $fldChosenPrincipal; ?>').value,
		<?php } else { ?>
		AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&PRINCIPALID=<?php echo $principalId; ?>",
		<?php } ?>
					"<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUserProductsListTable.php",
					"<?php echo $divAjaxUPList ?>",
					"Please wait whilst page is refreshed...",
					""); 
	}
	function refreshProduct() { 
		<?php if ($adminUser) { ?>
		AjaxRefresh("USERID=<?php echo $userId; ?>&RBNAME=<?php echo $fldChosenPRB; ?>&RBTYPE=tick&ADMINVIEW=Y&PRINCIPALID="+document.getElementById('<?php echo $fldChosenPrincipal; ?>').value,
		<?php } else { ?>
		AjaxRefresh("USERID=<?php echo $userId; ?>&RBNAME=<?php echo $fldChosenPRB; ?>&RBTYPE=tick&PRINCIPALID=<?php echo $principalId; ?>",
		<?php } ?>
					"<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminProductsListTable.php",
					"<?php echo $divAjaxPList ?>",
					"Please wait whilst page is refreshed...",
					""); 
	}
	</SCRIPT> 
<?php

function userList() {
	global $divAjaxUserList; global $fldChosenUserRB; global $ROOT; global $PHPFOLDER;
	print("<span style=''>Please choose a user to apply product to...</span>");
	print("<div id='".$divAjaxUserList."'></div>");
	print("<scr"."ipt type='text/javascript' defer>");
	print("AjaxRefresh(\"RBNAME=".$fldChosenUserRB."&CALLBACK=refreshUserPrincipals(); nextStep(2); \",
					   \"".$ROOT.$PHPFOLDER."functional/administration/adminUsersListTable.php\",
					   \"".$divAjaxUserList."\",
					   \"Please wait whilst page is refreshed...\",
					   \"refreshUserProduct();\");");
	print("</scr"."ipt>");
}
function principalList() {
	global $divAjaxUserPrincipalList;
	print("<span>Please choose a principal for this user to load stores for ...</span>");
	print("<div id='".$divAjaxUserPrincipalList."'>No User Chosen.</div>");
}
function productList() {
	global $divAjaxPList; global $adminUser; global $fldChosenPRB; global $ROOT; global $PHPFOLDER;
	print("<span style=''>Please choose a Product to apply to user ...</span>");
	print("<div id='".$divAjaxPList."'>No user or principal chosen.</div>");
}

if ($adminUser) {
	// the step icons
	GUICommonUtils::getSteps(array("Step 1:<BR>Choose User","Step 2:<BR>Select Principal","Step 3:<BR>Select Product","Step 4:<BR>Submit Changes","View Current<BR>User Products"));
	
	print("<div id='step1'>");
	userList();
	print("</div>");
	
	print("<div id='step2'>");
	principalList();
	print("</div>");
	
	print("<div id='step3'>");
	productList();
	print("</div>");
	
	print("<div id='step4'>");
	print("<input class='submit' type='submit' value='Add Product to User' onclick='submitContentForm(\"INSERT\");'/>");
	print("<input class='submit' type='submit' value='Remove Product from User' onclick='submitContentForm(\"DELETE\");'/>");
	print("</div>");
	
	print("<div id='step5'>");
	print("<div id='".$divAjaxUPList."'></div>");
	print("</div>");	
} else {
	// the step icons
	GUICommonUtils::getSteps(array("Step 1:<BR>Choose User","Step 2:<BR>Select Product<BR>from <B>".$principalName."</B>","Step 3:<BR>Submit Changes","View Current<BR>User Products"));
	
	print("<div id='step1'>");
	userList();
	print("</div>");
	
	print("<div id='step2'>");
	productList();
	print("</div>");
	
	print("<div id='step3'>");
	print("<input class='submit' type='submit' value='Add Product to User' onclick='submitContentForm(\"INSERT\");'/>");
	print("<input class='submit' type='submit' value='Remove Product from User' onclick='submitContentForm(\"DELETE\");'/>");
	print("</div>");
	
	print("<div id='step4'>");
	print("<div id='".$divAjaxUPList."'></div>");
	print("</div>");
  }


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
	<?php 
	if ($adminUser) echo "params+='&PRINCIPALID='+document.getElementById('".$fldChosenPrincipal."').value;";
	else echo "params+='&PRINCIPALID=".$principalId."';";
	?>
	params+='&PRODUCTUID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenPRB ?>"));
	
	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUserProductSubmit.php',
						  'alreadySubmitted=false; refreshUserProduct(); if (msgClass.type=="S") successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
						  'Please wait while request is processed...');
}

function successCallback(p_type) {
	toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");
}

function errorCallback(p_type) {
	toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");
}
function nextStep(step) {
	toggleSteps(step,"<?php echo $ROOT.$PHPFOLDER ?>");
}
</script>

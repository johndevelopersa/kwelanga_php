<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');

if(!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalName = $_SESSION['principal_name'];

// passed vars
if (isset($_POST['action'])) { $action = $_POST['action']; } 
else $action = ""; 

if ($action=="") { echo "no action supplied"; return; } 

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// check permissions
$administrationDAO=new AdministrationDAO($dbConn);
$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
$hasRole=$administrationDAO->hasRoleInSet($userId,$principalId,ROLE_CREATE_USER.",".ROLE_MODIFY_SU.",".ROLE_DELETE_SU.",".ROLE_MODIFY_GU.",".ROLE_DELETE_GU);
$hasRole=$administrationDAO->hasRoleInSet($userId,$principalId,ROLE_MAINTAIN_STORE_USERS);

if (!$hasRole) { echo "You do not have any permissions to add/remove user stores"; return; }

// field names for this form
$fldChosenUserRB='ChosenUser'; 
$fldChosenPrincipal='ChosenPrincipal'; 
$fldChosenPSRB='ChosenPrincipalStore'; 
$fldChosenChainRB='ChosenChain';
$fldChosenBatchUser='ChosenBatchUser'; 

// the ajax divs. refreshed independently	
$divAjaxUserList="ajaxUserList"; 
$divAjaxPSList="ajaxPrincipalStoreList"; 
$divAjaxChainList="ajaxChainList"; 
$divAjaxUPSList="ajaxUPSList"; 
$divAjaxUserPrincipalList="ajaxUPL";

print("&nbsp;"); // for some reason, the javascript does NOT work if this line is missing.

?>
    <SCRIPT type='text/javascript' defer>
    function refreshUserPrincipals() {
    	<?php
    	    // when the list is completed downloaded / generated, then call these
    		if ($action==MENU_USER_STORE_ALLOCATIONS) $callBack1="refreshPS(); refreshUserPS();"; 
    		else if ($action==MENU_ADD_STORE_BYCHAIN_USER) $callBack1="refreshChain(); refreshUserPS();"; 
    		else if ($action==MENU_ADD_STORE_BYUSER_USER) $callBack1="refreshUserPS();"; 
    		else $callBack1="";
    		// when the user selects a row, call these
    		if ($action==MENU_USER_STORE_ALLOCATIONS) $callBack2="refreshUserPS(); refreshPS();"; 
    		else if ($action==MENU_ADD_STORE_BYCHAIN_USER) $callBack2="refreshChain(); refreshUserPS();"; 
    		else $callBack2="refreshUserPS();";
    	?>
    	<?php if (($adminUser) && ($action!=MENU_ADD_STORE_BYUSER_USER)) { ?>
		AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&TAGID=<?php echo $fldChosenPrincipal; ?>&PRINCIPALID=<?php echo $principalId; ?>&ONCHANGE=<?php echo $callBack1; ?> nextStep(3);",
					"<?php echo $ROOT.$PHPFOLDER ?>elements/ajaxPrincipalDD.php",
					"<?php echo $divAjaxUserPrincipalList ?>",
					"Please wait whilst user principals are refreshed...",
					"<?php echo $callBack2; ?>");
		<?php } else { 
					echo $callBack1;
		       } ?>
	} 
	function refreshUserPS() {
		<?php if (($adminUser) && ($action!=MENU_ADD_STORE_BYUSER_USER)) { ?>
			if (document.getElementById('<?php echo $fldChosenPrincipal ?>').value=='') return;
			AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&PRINCIPALID="+document.getElementById("<?php echo $fldChosenPrincipal ?>").value,	
		<?php } else { ?>
			AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&PRINCIPALID=<?php echo $principalId ?>",
		<?php   } ?>
						"<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUserPrincipalStoresListTable.php",
						"<?php echo $divAjaxUPSList ?>",
						"Please wait whilst page is refreshed...",
						"");
	} 
	function refreshPS() {
		<?php if (($action==MENU_ADD_STORE_BYCHAIN_USER) || ($action==MENU_ADD_STORE_BYUSER_USER)) echo "return;"; ?>
		<?php if ($adminUser) print("var adminView='Y';"); else print("var adminView='N';"); ?>
		<?php if ($adminUser) { ?>
		if (document.getElementById('<?php echo $fldChosenPrincipal ?>').value=='') return;
		AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&ADMINVIEW="+adminView+"&RBNAME=<?php echo $fldChosenPSRB ?>&PRINCIPALID="+document.getElementById("<?php echo $fldChosenPrincipal ?>").value+"&USESTOREBYPASSROLE=Y",
		<?php } else { ?>
		AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&ADMINVIEW="+adminView+"&RBNAME=<?php echo $fldChosenPSRB ?>&PRINCIPALID=<?php echo $principalId; ?>&USESTOREBYPASSROLE=Y",
		<?php } ?>
					"<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminPrincipalStoresListTable.php",
					"<?php echo $divAjaxPSList ?>",
					"Please wait while list of stores is retrieved... this could take a few moments....",
					"");
	}
	
	function refreshChain() { 
		<?php if ($adminUser) { ?>
		if (document.getElementById('<?php echo $fldChosenPrincipal ?>').value=='') return;
		AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&ADMINVIEW=N&RBNAME=<?php echo $fldChosenChainRB ?>&RBTYPE=radio&PRINCIPALID="+document.getElementById("<?php echo $fldChosenPrincipal ?>").value+"&CALLBACK=nextStep(4);",
		<?php } else { ?>
		AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&ADMINVIEW=N&RBNAME=<?php echo $fldChosenChainRB ?>&RBTYPE=radio&PRINCIPALID=<?php echo $principalId; ?>&CALLBACK=nextStep(3);",
		<?php } ?>
					"<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminChainsListTable.php",
					"<?php echo $divAjaxChainList ?>",
					"Please wait whilst Chains page is refreshed...",
					""); 
	}
	
	</SCRIPT> 
<?php

function userList() {
	global $ROOT; global $PHPFOLDER; global $DHTMLROOT; global $divAjaxUserList; global $fldChosenUserRB;
	print("<span style=''>Please choose a user to apply store to...</span>");
	print("<div id='".$divAjaxUserList."'></div>");
	print("<scr"."ipt type='text/javascript' defer>");
	print("AjaxRefresh(\"RBNAME=".$fldChosenUserRB."&CALLBACK=refreshUserPrincipals(); nextStep(2); \",
					   \"".$ROOT.$PHPFOLDER."functional/administration/adminUsersListTable.php\",
					   \"".$divAjaxUserList."\",
					   \"Please wait whilst page is refreshed...\",
					   \"\");");
	print("</scr"."ipt>");
} 
function principalList() {
	global $divAjaxUserPrincipalList;
	print("<span>Please choose a principal for this user to load stores for ...</span>");
	print("<div id='".$divAjaxUserPrincipalList."'>No User Chosen.</div>");
}
function chainList() {
	global $ROOT; global $PHPFOLDER; global $adminUser; global $fldChosenChainRB; global $divAjaxChainList; global $principalId; global $fldChosenPrincipal; global $fldChosenUserRB;
	print("<span style=''>Please choose a Chain to add Stores from ...</span>");
	print("<div id='".$divAjaxChainList."'></div>");
}

if ($action==MENU_USER_STORE_ALLOCATIONS) {
	if ($adminUser) {
		// the step icons
		GUICommonUtils::getSteps(array("Step 1:<BR>Choose User","Step 2:<BR>Select Principal","Step 3:<BR>Select Store","Step 4:<BR>Submit Changes","View Current Stores"));
		
		print("<div id='step1'>");
		userList();
		print("</div>");
		
		print("<div id='step2'>");
		principalList();
		print("</div>");
		
		print("<div id='step3'>");
		print("<span style=''>Please choose a Principal Store to apply to user above ...</span><BR><span style='font-size:9px'>(if your store is not listed, it could be that the chosen user doesn't have access to the principal under which it is assigned, or wrong principal chosen.)</span>");
		print("<div id='".$divAjaxPSList."'>No User or Principal Chosen.</div>");
		print("</div>");
		
		print("<div id='step4'>");
		print("<input class='submit' type='submit' value='Add Store to User' onclick='submitContentForm(\"INSERT\",\"\");'/>");
		print("<input class='submit' type='submit' value='Remove Store from User' onclick='submitContentForm(\"DELETE\",\"\");'/>");
		print("</div>");
		
		print("<div id='step5'>");
		print("<div id='".$divAjaxUPSList."'>No User Chosen</div>");
		print("</div>");
	} else {
		// the step icons
		GUICommonUtils::getSteps(array("Step 1:<BR>Choose User","Step 2:<BR>Select Store<BR>from <B>".$principalName."</B>","Step 3:<BR>Submit Changes","View Current Stores"));
		
		print("<div id='step1'>");
		userList();
		print("</div>");
		
		print("<div id='step2'>");
		print("<span style=''>Please choose a Principal Store to apply to chosen user ...</span><BR><span style='font-size:9px'>(if your store is not listed, it could be that the chosen user doesn't have access to the principal under which it is assigned)</span>");
		print("<div id='".$divAjaxPSList."'></div>");
		print("</div>");
		
		print("<div id='step3'>");
		print("<input class='submit' type='submit' value='Add Store to User' onclick='submitContentForm(\"INSERT\",\"\");'/>");
		print("<input class='submit' type='submit' value='Remove Store from User' onclick='submitContentForm(\"DELETE\",\"\");'/>");
		print("</div>");
		
		print("<div id='step4'>");
		print("<div id='".$divAjaxUPSList."'>No User Chosen</div>");
		print("</div>");
	  }
} else if ($action==MENU_ADD_STORE_BYCHAIN_USER) {
	if ($adminUser) {
		GUICommonUtils::getSteps(array("Step 1:<BR>Choose User","Step 2:<BR>Select Principal","Step 3:<BR>Select Chain","Step 4:<BR>Submit Changes","View Current Stores"));
		
		print("<div id='step1'>");
		userList();
		print("</div>");
		
		print("<div id='step2'>");
		principalList();
		print("</div>");
		
		print("<div id='step3'>");
		chainList();
		print("</div>");
		
		print("<div id='step4'>");
		print("<input class='submit' type='submit' value='Add All Stores from Selected Chain' onclick='submitContentForm(\"INSERT\",\"CHAIN\");'/>");
		print("</div>");
		
		print("<div id='step5'>");
		print("<div id='".$divAjaxUPSList."' style=''>No User/Principal Chosen</div>");
		print("</div>");
		
	} else {
		GUICommonUtils::getSteps(array("Step 1:<BR>Choose User","Step 2:<BR>Select Chain<BR>from <B>".$principalName."</B>","Step 3:<BR>Submit Changes","View Current Stores"));
		
		print("<div id='step1'>");
		userList();
		print("</div>");
		
		print("<div id='step2'>");
		print("<span style=''>Please choose a Chain to add Stores from ...</span>");
		print("<div id='".$divAjaxChainList."'></div>");
		print("<scr"."ipt type='text/javascript' defer>");
		if ($adminUser) {
			print("AjaxRefresh(\"RBNAME=".$fldChosenChainRB."&RBTYPE=radio&ADMINVIEW=Y&PRINCIPALID=\"+document.getElementById('".$fldChosenPrincipal."').value,");
		} else {
			print("AjaxRefresh(\"RBNAME=".$fldChosenChainRB."&RBTYPE=radio&ADMINVIEW=N&PRINCIPALID=".$principalId."\",");
		  }
						print("\"".$ROOT.$PHPFOLDER."functional/administration/adminChainsListTable.php\",
							   \"".$divAjaxChainList."\",
							   \"Please wait whilst Chains page is refreshed...\",
						   	   \"\");");
		print("</scr"."ipt>");
		print("</div>");
		
		print("<div id='step3'>");
		print("<input class='submit' type='submit' value='Add All Stores from Selected Chain' onclick='submitContentForm(\"INSERT\",\"CHAIN\");'/>");
		print("</div>");
		
		print("<div id='step4'>");
		print("<div id='".$divAjaxUPSList."'>No User/Principal Chosen</div>");
		print("</div>");
	  }
	
  } else if ($action==MENU_ADD_STORE_BYUSER_USER) {
		// the step icons
		GUICommonUtils::getSteps(array("Step 1:<BR>Choose User","Step 2:<BR>Select Model User","Step 3:<BR>Submit Changes","View Current Stores"));
		
		print("<div id='step1'>");
		userList();
		print("</div>");
		
		print("<div id='step2'>");
		print("&nbsp"); BasicSelectElement::getUsersWithinPriviledgesDD($fldChosenBatchUser,"","N","N",null,null,null,$dbConn,$userId, $principalId);
		print("</div>");
		
		print("<CENTER>");
		print("<div id='step3'>");
		print("<input class='submit' type='submit' value='Add All Stores by User' onclick='submitContentForm(\"INSERT\",\"USER\");'/>");
		print("</div>");
		print("</CENTER>");
		
		print("<div id='step4'>");
		print("<div id='".$divAjaxUPSList."'></div>");
		print("</div>");
	}

$dbConn->dbClose();
?>
<script type='text/javascript' defer>
var alreadySubmitted=false;

$("div[id*='step']").css({display:'none'});
$("#step1").css({display:'block'});
toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");

function submitContentForm(p_type, p_batchType) {
	if ((p_batchType=='CHAIN') || (p_batchType=='USER')) {
		var answer = confirm('You have chosen to add all stores by a chain/user. This can result in thousands of stores added and can take 30+ seconds to run. If you then decide you made a mistake, stores can only be deleted individually one by one. Select OK to continue posting, or CANCEL to go back.');
		if (!answer) return "cancelled"; 
	}
	if (alreadySubmitted) {
		return;
	}
	alreadySubmitted=true;
	var params='DMLTYPE='+p_type;
	params+='&USERID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"));
	if (p_batchType!="") {
		params+='&BATCHTYPE='+p_batchType;
	}
	
	<?php 
	if (($adminUser) && (($action==MENU_USER_STORE_ALLOCATIONS) || ($action==MENU_ADD_STORE_BYCHAIN_USER))) echo "params+='&PRINCIPALID='+document.getElementById('".$fldChosenPrincipal."').value;";
	else echo "params+='&PRINCIPALID=".$principalId."';";
	?>
	if (p_batchType=='') params+='&PRINCIPALSTOREUID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenPSRB ?>"));
	if (p_batchType=='USER') params+='&BATCHUSER='+document.getElementById("<?php echo $fldChosenBatchUser ?>").value;
	if (p_batchType=='CHAIN') params+='&BATCHCHAIN='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenChainRB ?>"));
	
	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUserStoreSubmit.php',
						  'alreadySubmitted=false; refreshUserPS(); if ((msgClass.type=="S") || (msgClass.type=="I")) successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
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

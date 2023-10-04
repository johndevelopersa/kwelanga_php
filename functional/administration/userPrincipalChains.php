<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');


if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalName = $_SESSION['principal_name'];
$adminUser = (isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;


// field names for this form
$fldChosenUserRB='ChosenUser';
$fldChosenCRB='ChosenChain';
$fldChosenPrincipal='ChosenPrincipal';


// the ajax divs. refreshed independently
$divAjaxUserList="ajaxUserList";
$divAjaxCList="ajaxChainList";
$divAjaxUserPrincipalList="ajaxPrincipalList";
$divAjaxUCList="ajaxUCList";


?>
    <SCRIPT type='text/javascript' >

	function refreshChain() {
		<?php if ($adminUser) { ?>
		AjaxRefresh("USERID=<?php echo $userId; ?>&RBNAME=<?php echo $fldChosenCRB; ?>&RBTYPE=tick&ADMINVIEW=Y&PRINCIPALID=<?php echo $principalId; ?>",
		<?php } else { ?>
		AjaxRefresh("USERID=<?php echo $userId; ?>&RBNAME=<?php echo $fldChosenCRB; ?>&RBTYPE=tick&PRINCIPALID=<?php echo $principalId; ?>",
		<?php } ?>
					"<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminChainsListTable.php",
					"<?php echo $divAjaxCList ?>",
					"Please wait whilst page is refreshed...",
					"");
	}

	function refreshUsers() {
			AjaxRefresh("RBTYPE=tick&RBNAME=<?php echo $fldChosenUserRB; ?>&CALLBACK=addUser(); ",
		   "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUsersListTable.php",
		   "<?php echo $divAjaxUserList ?>",
		   "Please wait whilst page is refreshed...",
		   "");
	}


	//select a user action?
	function addUser(){ return; }



	//SUBMIT FUNCTIONS

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
		params+='&USERIDARR='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"));
		params+='&CHAINUID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenCRB ?>"));

		params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
		AjaxRefreshWithResult(params,
							  '<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUserChainSubmit.php',
							  'alreadySubmitted=false; if (msgClass.type=="S") successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
							  'Please wait while request is processed...');
	}

	function successCallback(p_type) {
	  	refreshUsers();
	  	refreshChain();
		toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");
	}

	function errorCallback(p_type) {
		toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");
	}
	function nextStep(step) {
		toggleSteps(step,"<?php echo $ROOT.$PHPFOLDER ?>");
	}


	</SCRIPT>
<?php


	// the step icons
	GUICommonUtils::getSteps(array("Step 1:<BR>Choose User","Step 2:<BR>Select Chain<BR>from <B>".$principalName."</B>","Step 3:<BR>Submit Changes"));

	//STEP 1
	echo '<div id="step1">';
    	echo("<span style=''>Please choose the users to apply chains to...</span>");
    	echo("<div id='".$divAjaxUserList."'></div>");
    	echo("<script type='text/javascript' defer>");
          echo 'refreshUsers();';
    	echo("</script>");
	echo '</div>';


	//STEP 2
	echo '<div id="step2">';
	  echo("<span style=''>Please choose a Chain to apply to the chosen users...</span>");
	  echo("<div id='".$divAjaxCList."'>No user or principal chosen.</div>");
	  echo("<script type='text/javascript' > refreshChain(); </script>");
	echo '</div>';


	//STEP 3
	echo '<div id="step3">';
	echo '<BR><BR>';
	echo("<input class='submit' type='submit' value='Add Chain to User' onclick='submitContentForm(\"INSERT\");'/>");
	echo("<input class='submit' type='submit' value='Remove Chain from User' onclick='submitContentForm(\"DELETE\");'/>");
	echo '</div>';


?>
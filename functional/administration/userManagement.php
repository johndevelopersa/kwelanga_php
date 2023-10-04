<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');

if(!isset($_SESSION)) session_start;
$user_id = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
if (isset($_SESSION['m_id'])) { $m_id = $_SESSION['m_id']; } // should have been set by generalAjaxBase.php

// passed vars
if (isset($_POST['action'])) { $action = $_POST['action']; }
else $action = "";

// field names for this form
$fldChosenUserRB='ChosenUser';
// the ajax divs. refreshed independently

$divAjaxUserList="ajaxUserList";
$divAjaxUserDetails="ajaxUserDetails";

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

// check permissions
$administrationDAO = new AdministrationDAO($dbConn);
$hasRole=$administrationDAO->hasRoleInSet($user_id,$principalId,ROLE_CREATE_USER.",".ROLE_MODIFY_SU.",".ROLE_DELETE_SU.",".ROLE_MODIFY_GU.",".ROLE_DELETE_GU);

$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
if ((!$adminUser) && ($hasRole!==true)) { echo "You do not have any permissions to add/modify/delete users"; return; }

?>
<SCRIPT type='text/javascript' defer>
    function refreshUserDetails(p_type) {
            var type;

            $('#userDetails').show();
            $('#userList').hide();

            if (p_type==1) type="UPDATE"; else if (p_type==2) type="INSERT";
            AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&DMLTYPE="+type,
                                    "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/userDetails.php",
                                    "<?php echo $divAjaxUserDetails ?>",
                                    "Please wait whilst page is refreshed...",
                                    "");
    }
    function resetActionArea(){
            document.getElementById('<?php echo $divAjaxUserDetails; ?>').innerHTML='&nbsp;';
    }

    function submitForm(p_type) {
            // the submitContentForm is only available when userDetails.php has been called
            var userId=convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"));
            if ((p_type=="UPDATE") && (userId=="")) {
                    alert('Please select a user before Submitting...');
                    return;
            }
            submitContentForm(p_type);
    }
    function refreshUserlist(){
      <?php
            print("AjaxRefresh(\"RBNAME=".$fldChosenUserRB."&CALLBACK=refreshUserDetails(1); \",
                                       \"".$ROOT.$PHPFOLDER."functional/administration/adminUsersListTable.php\",
                                       \"".$divAjaxUserList."\",
                                       \"Please wait whilst page is refreshed...\",
                                       \"\");");
       ?>
      }
    </SCRIPT>
<?php

if ($action=="") return;

function userList () {

	global $ROOT; global $PHPFOLDER; global $DHTMLROOT; global $divAjaxUserList; global $fldChosenUserRB;

	echo("<BR><span style=''>Please choose a user to modify...</span>");
	echo("<div id='box1' style='overflow:auto;'>"); // 3 rows data
	echo("<div id='".$divAjaxUserList."'></div>");
	echo("<script type='text/javascript' defer>");
            echo 'refreshUserlist();';
	echo("</script>");
	echo("</div>");
}

// update includes delete
if ($action=="UPDATE") {
	// the step icons
	//GUICommonUtils::getSteps(array("Step 1:<BR>Choose User","Step 2:<BR>Modify User Details"));

	echo("<div id='userList'>");
	userList();
	echo("</div>");

	echo("<div id='userDetails' style='display:none;'>");
        echo '<BR><BR><input type="submit" class="submit" value="Back to Users" onclick="backToUserList()"><BR><BR>';
	echo("<div id='".$divAjaxUserDetails."'></div>");

	echo("</div>");

} else if ($action=="INSERT") {

	echo("<BR>");

	echo("<div id='".$divAjaxUserDetails."'></div>");

	?>
		<script type='text/javascript' defer>
			refreshUserDetails(2);
		</script>
	<?php

  }
$dbConn->dbClose();
?>
<script type='text/javascript' defer>

//$("div[id*='step']").css({display:'none'});
//$("#step1").css({display:'block'});
//toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");

function backToUserList(){
  $('#userDetails').hide();
  $('#userList').show();
}


function nextStep(step) {
	toggleSteps(step,"<?php echo $ROOT.$PHPFOLDER ?>");
}

</script>
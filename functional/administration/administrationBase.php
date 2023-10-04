<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

if(!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];
$principalUId = $_SESSION['principal_uid'];
if (isset($_GET['m_id'])) { $m_id=$_GET['m_id']; $_SESSION['m_id'] = $_GET['m_id']; } else $m_id="";

// field names for this form
$fldTab='Tab';

// the ajax divs. refreshed independently	
$divAjaxTabs="ajaxTabs"; 
$divAjaxTabsContent="ajaxTabsContent";

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<HTML>
<HEAD>
	<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
	<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/default.css' rel='stylesheet' type='text/css'>
	
	<SCRIPT type='text/javascript'>
	    $(document).ready(function() {
	    	$(".tabDiv").css({backgroundImage:"url(<?php echo $ROOT.$PHPFOLDER ?>images/tab-unsel.png)",fontWeight:'normal',textDecoration:'none'});
			$(".tabDiv").click(function()
		    { 
		        $(".tabDiv").css({backgroundImage:"url(<?php echo $ROOT.$PHPFOLDER ?>images/tab-unsel.png)",fontWeight:'normal',textDecoration:'none'});
			    $(this).css({backgroundImage:"url(<?php echo $ROOT.$PHPFOLDER ?>images/tab-sel.png)",fontWeight: 'bold',textDecoration:'underline'});
			});
			$(".tabDiv").mouseover(function()
		    { 
		        $(this).css({cursor:'hand'});
			});
		});
		
		function getContent(path,action) {
			AjaxRefresh(action,
						path,
						"<?php echo $divAjaxTabsContent ?>",
						"Please wait whilst page is refreshed...",
						"");
		}
					
	</SCRIPT>
</HEAD>
<BODY>
<?php
$administrationDAO = new AdministrationDAO($dbConn);
$hasRole1=$administrationDAO->hasRole($userId,$principalUId,ROLE_MAINTAIN_STORES);
$hasRole2=$administrationDAO->hasRole($userId,$principalUId,ROLE_USER_MNT_AD);
$hasRole3=$administrationDAO->hasRole($userId,$principalUId,ROLE_USER_MNT_MF);
print("<CENTER>");
print("<div id='".$divAjaxTabs."' style='overflow:hidden;'>");
print("<table class='tableReset'>");
print("<tr class='tableReset'>");
if ($hasRole2 || $hasRole3) {
	print("<td class='tableReset'><div class='tabDiv' onclick='getContent(\"".$ROOT.$PHPFOLDER."functional/administration/userManagement.php\",\"\");'>Create / Modify User</div></td>");
	print("<td class='tableReset'><div class='tabDiv' onclick='getContent(\"".$ROOT.$PHPFOLDER."functional/administration/userRoles.php\",\"\");'>User Roles</div></td>");
	print("<td class='tableReset'><div class='tabDiv' onclick='getContent(\"".$ROOT.$PHPFOLDER."functional/administration/userPrincipalDepots.php\",\"\");'>User Principal-Depots</div></td>");
} else {
	print("<td class='tableReset'><div class='tabDivDisabled' onclick='alert(\"This Stores Tab is only enabled if you have the UserManagement Role.\");'>Create / Modify User</div></td>");
	print("<td class='tableReset'><div class='tabDivDisabled' onclick='alert(\"This Stores Tab is only enabled if you have the UserManagement Role.\");'>User Roles</div></td>");
	print("<td class='tableReset'><div class='tabDivDisabled' onclick='alert(\"This Stores Tab is only enabled if you have the UserManagement Role.\");'>User Principal-Depots</div></td>");	
  }

// stores only available if you have the masterfiles-maintenance-->Maintain-Stores menu option
if ($hasRole1) {
	print("<td class='tableReset'><div class='tabDiv' onclick='getContent(\"".$ROOT.$PHPFOLDER."functional/administration/userPrincipalStores.php\",\"\");'>User Stores</div></td>");
} else {
	print("<td class='tableReset'><div class='tabDivDisabled' onclick='alert(\"This Stores Tab is only enabled if you have the MaintainStores Role.\");'>User Stores</div></td>");
  }

print("<td class='tableReset'><div class='tabDiv' onclick='getContent(\"".$ROOT.$PHPFOLDER."functional/administration/userChains.php\",\"\");'>User Chains</div></td>");
print("</tr>");
print("</table>");
print("</div>");
print("</CENTER>");
print("<BR>");

// content from tab is displayed here
print("<div id='".$divAjaxTabsContent."'></div>");

?>

</HTML>
</BODY>
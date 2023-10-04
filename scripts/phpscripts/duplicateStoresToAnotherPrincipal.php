<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostScriptsDAO.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId=$_SESSION['principal_id'];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

if (isset($_POST["process"])) $postPROCESS=mysqli_real_escape_string($dbConn->connection,$_POST["process"]); else $postPROCESS="N";
if (isset($_POST["FC"])) $postFC=mysqli_real_escape_string($dbConn->connection,$_POST["FC"]); else $postFC="";
if (isset($_POST["FD"])) $postFD=mysqli_real_escape_string($dbConn->connection,$_POST["FD"]); else $postFD="";
if (isset($_POST["TP"])) $postTP=mysqli_real_escape_string($dbConn->connection,$_POST["TP"]); else $postTP="";
if (isset($_POST["TC"])) $postTC=mysqli_real_escape_string($dbConn->connection,$_POST["TC"]); else $postTC="";
if (isset($_POST["TD"])) $postTD=mysqli_real_escape_string($dbConn->connection,$_POST["TD"]); else $postTD="";


$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;

if($userId <> 11) {
	echo "You cannot run this";
	return;
	
}



if (!($adminUser===true)) {
	echo "Incorrect Priviledges";
	return;
}

if ($postPROCESS=="Y") {
	if (($postTD!="") && ($postFD=="")) {
		echo "<BR><SPAN style='font-size:12px; font-weight:bold; color:".COLOR_URGENT_TEXT."'>If you specify a TO depot, then the FROM depot is required</SPAN><BR>";		
	} else if (($postFC=="") || ($postTC=="") || ($postTP=="")) {
		echo "<BR><SPAN style='font-size:12px; font-weight:bold; color:".COLOR_URGENT_TEXT."'>Principal and Chain are required fields</SPAN><BR>";
	} else {
		$postScriptsDAO = new PostScriptsDAO ($dbConn);
		$resultTO=$postScriptsDAO->postDuplicateStores( $principalId, $postFC, $postFD, $postTP, $postTC, $postTD );
		echo "<BR><SPAN style='font-size:12px; font-weight:bold; color:".COLOR_URGENT_TEXT."'>".$resultTO->description."</SPAN><BR>";
		if ($resultTO->type===FLAG_ERRORTO_SUCCESS) {
			$dbConn->dbinsQuery("commit;");
		} else {
			$dbConn->dbinsQuery("rollback;");
		}
	}
}

echo "<HTML>
	  <HEAD>
		<SCRIPT type=\"text/javascript\" language=\"javascript\" src=\"".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js\"></script>
		<link href=\"{$DHTMLROOT}{$PHPFOLDER}css/default.css\" rel=\"stylesheet\" type=\"text/css\" />
		<STYLE>
			table { border:1; border-style:solid; font-size:11px}
			td { border:0; border-left:1; border-style:solid; border-right:1; }
		</STYLE>
	  </HEAD>
	  <BODY style='font-size:10; text-align:center'>";

echo "<SPAN style='".FONT_UNOBTRUSIVE_INFO."'>Copy Stores from 1 principal-chain to another principal-chain</SPAN>";

echo "<form id='param' name='param' action='{$_SERVER["PHP_SELF"]}' method='post'>";
echo "<TABLE>";
echo "<TR><TD colspan=2><B>From<B></TD></TR>";
echo "<TR><TD>Principal:</TD>";
echo "<TD>".$_SESSION['principal_name']."</TD>";
echo "</TR>";
echo "<TR><TD>Chain:</TD>";
echo "<TD>"; BasicSelectElement::getPrincipalChainsDD("FC",$postFC,"N","N",null,null,null,$dbConn,$principalId);  echo "</TD>";
echo "</TR>";
echo "<TR><TD>Depot:</TD>";
echo "<TD>"; BasicSelectElement::getUserDepotsForPrincipalDD("FD",$postFD,"N","N",null,null,null,$dbConn,$userId,$principalId);  echo "</TD>";
echo "</TR>";
echo "<TR><TD colspan=2><B>To<B></TD></TR>";
echo "<TR><TD>Principal:</TD>";
echo "<TD>"; BasicSelectElement::getUserPrincipalDD("TP",$postTP,"N","N","document.param.submit();",null,null,$dbConn,$userId); echo "</TD>";
echo "</TR>";
echo "<TR><TD>Chain:</TD>";
echo "<TD>"; BasicSelectElement::getPrincipalChainsDD("TC",$postTC,"N","N",null,null,null,$dbConn,$postTP);  echo "</TD>";
echo "</TR>";
echo "<TR><TD>Depot:</TD>";
echo "<TD>"; BasicSelectElement::getUserDepotsForPrincipalDD("TD",$postTD,"N","N",null,null,null,$dbConn,$userId,$postTP);  echo "</TD>";
echo "</TR>";
echo "<TR><TD colspan=2 style='text-align:center;'><INPUT type='hidden' value='N' name='process' id='process'>
						<INPUT type='button' class='submit' value='submit' onclick='document.param.process.value=\"Y\"; document.param.submit();'>
		  </TD>";
echo "</TR>";

echo "</TABLE>";
echo "</FORM>";

echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable
?>
<SCRIPT type="text/javascript">
adjustMyFrameHeight();
</SCRIPT>
				
</BODY></HTML>




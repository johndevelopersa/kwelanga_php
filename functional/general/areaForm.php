<?php
	include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	require($ROOT.$PHPFOLDER."functional/main/access_control.php");
	include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
	include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
	include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");  //Custom Fields

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalName = $_SESSION['principal_name'];


// the ajax divs. refreshed independently
$divAjaxMainContentArea="ajaxMainContentArea";

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

$postDMLTYPE = (isset($_POST['DMLTYPE'])) ? (htmlspecialchars($_POST['DMLTYPE'])) : ("INSERT");
// this is the value when coming from modifyArea as well
$postAREAUID = (isset($_POST['AREAUID'])) ? (htmlspecialchars($_POST['AREAUID'])) : ("");

if ($postAREAUID!="") {
	include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
	$storeDAO = new StoreDAO($dbConn);
	$mfA=$storeDAO->getPrincipalAreaItem($postAREAUID);
	$postUID = $mfA[0]['uid'];
	$postDESCRIPTION = $mfA[0]['description'];
} else  {
	if (isset($_POST['UID'])) $postUID = $_POST['UID']; else $postUID="";
	if (isset($_POST['DESCRIPTION'])) $postDESCRIPTION = $_POST['DESCRIPTION']; else $postDESCRIPTION="";
}


#--------------------------------------------------------------------------------------------------------------------------

    /*
     *
     * START OF SCREEN
     *
     */
  echo "<html><head></head><body>";

  echo "<div id='".$divAjaxMainContentArea."'><BR>";

  echo "<table width='720' border='1'>";
  echo "<tr class='even'>";
  if ($postDMLTYPE=="INSERT") {
  	echo '<td colspan="4"><b> Add a new Area to the master list</b></td>';
  } else {
   	echo '<td colspan="4"><b> Modify details of an existing Area</b></td>';
    }
  echo '</tr>';

  echo "<TR class='".GUICommonUtils::styleEO($class)."'><TD>Description"; GUICommonUtils::requiredField(); echo "</TD><TD><input id='DESCRIPTION' type='text' value='{$postDESCRIPTION}' size=80 maxlength=80></TD></TR>";
  echo "</table><BR>";

  echo "<INPUT type='submit' class='submit' name='submit' value='Submit Area' onclick='submitContentForm(\"".$postDMLTYPE."\");'>\n";

  echo "</FORM></div>";  // main content area
  echo "</body></html>";
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

	params+='&UID=<?php echo $postAREAUID ?>';
	params+='&DESCRIPTION='+document.getElementById("DESCRIPTION").value;

	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/general/areaSubmit.php',
						  'alreadySubmitted=false; if (msgClass.type=="S") successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
						  'Please wait while request is processed...');
}

function successCallback(p_type) {
	if (p_type=="INSERT") {
		document.getElementById("DESCRIPTION").value='';
	}
}

function errorCallback(p_type) {

}
</script>
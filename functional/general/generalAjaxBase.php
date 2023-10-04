<?php

include_once 'ROOT.php';
include_once $ROOT.'PHPINI.php';
require $ROOT.$PHPFOLDER."functional/main/access_control.php";
include_once $ROOT.$PHPFOLDER.'properties/Constants.php';
include_once $ROOT.$PHPFOLDER.'libs/GUICommonUtils.php';
include_once $ROOT.$PHPFOLDER."elements/datePickerElement.php";

if(!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$m_id = (isset($_GET['m_id'])) ? $_GET['m_id'] : ""; // sets it to level 1 menu option

// field names for this form
$fldTab='Tab';
$divAjaxTabs="ajaxTabs";  // the ajax divs. refreshed independently
$divAjaxTabsContent="ajaxTabsContent";

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<HTML>
<HEAD>
	<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
	<script type='text/javascript' language='javascript' src='<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.autoscroll.js'></script>
	<?php DatePickerElement::getDatePickerLibs(); ?>
  <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>
	<SCRIPT type='text/javascript'>
		function getContent(path,action) {
			AjaxRefresh(action,
						path,
						"<?php echo $divAjaxTabsContent ?>",
						"Please wait whilst page is refreshed...",
						"");
		}
		// autoscroll start
		$(document).ready(function(){
			document.body.focus(); $.autoscroll.init({step: 200});
			<?php echo (isset($_GET['callback']))?($_GET['callback']):('') ?>
		});
		// autoscroll end


	</SCRIPT>
</HEAD>
<BODY>
<!-- page navbar : start /-->
<div align="center">
  <?php GUICommonUtils::getTabs($userId,$principalId,$m_id,"",$dbConn);   // get the overheadtabs ?>
</div>
<!-- page navbar : end /-->

<!-- page content : start /-->
<div align="center">
   <div id="<?php echo $divAjaxTabsContent?>"></div>
</div>
<!-- page content : end /-->
</HTML>
</BODY>
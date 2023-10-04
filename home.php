<?php
// NOTE: this page uses IE only components :SDK userData
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'elements/Messages.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostMiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/MobileDetect.php');
CommonUtils::getSystemConventions();

if (!isset($_SESSION)) session_start();
$_SESSION["DESKTOP"]="Y";
$userId=$_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

$isDesktopOverride = ((isset($_GET["DESKTOP"]))?true:false);

if (!$isDesktopOverride) {
  $detect = new MobileDetect();
  if ($detect->isMobile()) {
    $_SESSION["DESKTOP"]="N";
    header("Location: ".HOST_SURESERVER_AS_NEWUSER."m/index.php");
    exit;
  }
  /*
  $_SESSION["DESKTOP"]="N";
  header("Location: ".HOST_SURESERVER_AS_USER."m/index.php");
  exit;
  */
}

$dbConn = new dbConnect();  //Create new database object
$dbConn->dbConnection();  //Database connection method

$administrationDAO = new AdministrationDAO($dbConn);
$mfUP=$administrationDAO->getUserPreferences($userId);



/*----------------------------
 *
 * 	USER TRACKING
 *
 *----------------------------*/

//DON'T LOG => if user refreshes and is still on the same userid + principal.
if(isset($_SESSION['old_user_id']) && isset($_SESSION['old_principal_id']) && $_SESSION['old_user_id'] == $userId && $_SESSION['old_principal_id'] == $principalId){

} else {

  $_SESSION['old_user_id'] = $userId;
  $_SESSION['old_principal_id'] = $principalId;

  $miscDAO = new PostMiscellaneousDAO($dbConn);

  $trackResult = $miscDAO->postUserTracking($userId, $principalId, getenv('REMOTE_ADDR'));

  if ($trackResult->type == FLAG_ERRORTO_SUCCESS) {
    $dbConn->dbinsQuery("commit;");
  } else {
    //SILENT FAIL
    mysqli_query("rollback", $dbConn->connection);
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"  xmlns:sdk="">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo SNC::title ?></title>
<script id="script_1" type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
<style type="text/css">
	sdk\:cacher {
	    behavior: url(#default#userData);
	}
</style>
</head>

<body id="pagew" marginwidth="0" marginheight="0" leftmargin="0" topmargin="0" style='padding:0px;margin:0px;height:100%;width:100%;' width="100%" class="mobile-background">

<sdk:cacher id="cachetag"></sdk:cacher>

<?php
include_once($ROOT.$PHPFOLDER."functional/main/header.php");

/*
 * <!-- <iframe src="<?php echo $ROOT.$PHPFOLDER ?>functional/main/header.php" name="topFrame" scrolling="No" noresize="noresize" id="topFrame" height="113px" title="head" style='width:100%;' border="0" frameborder="0" framespacing="0" marginheight="0" marginwidth="0"></iframe>
 */

?>
<div style="position:absolute;top:115px;left:0px;right:0px;bottom:0px;" id="wrapper">
<iframe src="<?php echo $ROOT.$PHPFOLDER ?>functional/main/content.php" class="autoHeight" name="content" scrolling="yes" id="content" title="head" style='display:block;width:100%;height:100%;margin:0px;padding:0px;border:0px;' border="0" frameborder="0" framespacing="0"  marginheight="0" marginwidth="0" onload="resizeme()"></iframe>
</div>

<div id='tagExceptions'
	 style='position:absolute;
			x-index:1000;
			top:130px;
			left:0px;
			width:35px;
			height:146px;
			display:none;
			background-image:url("<?php echo $ROOT.$PHPFOLDER ?>images/you-have-exceptions.png");'
	title='[Click this tag to hide it termorarily] Please visit the Orders Import Exception screen under the transaction menu to see these. These need to be resolved before your warehouse cutoff time.'
	onclick='this.style.display="none";'>

</div>
<div id='tagDepotOrders'
	 style='position:absolute;
			x-index:1000;
			top:280px;
			left:0px;
			width:35px;
			height:146px;
			display:none;
			background-image:url("<?php echo $ROOT.$PHPFOLDER ?>images/you-have-depot_orders.png");'
	title='[Click this tag to hide it termorarily] Please visit the Transaction Tracking screen under the transaction menu to see these.'
	onclick='this.style.display="none";'>

</div>


<script type="text/javascript">

// the exceptions

function clPreference() {
	this.notifyExceptionTag = "<?php echo ((isset($mfUP[0]) && $mfUP[0]["notify_exception_tag"]=="Y")?"Y":"N") ?>";
	this.notifyDepotOrderTag = "<?php echo ((isset($mfUP[0]) && $mfUP[0]["notify_depot_order_tag"]=="Y")?"Y":"N") ?>";
}
userPreference = new clPreference();

var intervalTimer1=setInterval("checkExceptions();", 1000*60*30); // every 30 mins
var intervalTimer2=setInterval("checkDepotOrders();", 1000*60*30); // every 30 mins


$(document).ready(function(){

	checkExceptions(); // do it on first load
	checkDepotOrders(); // do it on first load

	//data collection for screensizes - close project 1 August.
	//collectUserScrSize();

<?php
  $userAgent = $_SERVER['HTTP_USER_AGENT'];
  if((strpos($userAgent, 'MSIE 6.0')!==false) || ((strpos($userAgent, 'MSIE 7.0')!==false) && (strpos($userAgent, 'Trident')===false))){ ?>
  popBox('<div style="color:#000;font-size:10px;line-height:14px;"><h2 title="<?php echo $_SERVER['HTTP_USER_AGENT'] ?>">System Notification</h2><br>Our system has detected that your browser (Internet Explorer 6/7) is <strong>out of date.</strong><br> It has known <strong>security flaws</strong> and may <strong>not display all features</strong> of this and other websites.<br><br><br><div align="center"><a href="http://windows.microsoft.com/en-US/internet-explorer/downloads/ie-8" target="_blank" style="color:#fff;font-size:12px;font-weight:bold;display:block;text-align:center;width:200px;line-height:28px;border:2px solid #fff;background:red;">Download Upgrade</a></div><br></div>','error','600');
<?php } ?>

});

function showMessagePopup1() {
  parent.popBox('<div align="center" id="fileUpload" style="color:#444;">'+
      '<b><u>Please Note:</u></b><br><br>'+
      'Periodically we receive requests from our clients to disable advertisements or popups.<br><br>'+
      'Retail Trading does not in any way implement or make use of such unsolicited marketing - these popups or adware are the product of '+
      '3rd party software having been installed on the clients computer external to our system. They are usually caused by plugins, toolbars or other malware/adware that the client has '+
      ' installed on their machines directly or indirectly which has the capability of "hijacking" any webpage and making it seem as if the website is authoring such functionality.<br><br>'+
      'If your machine exhibits such behaviour, you should uninstall any recently installed plugins or toolbars, applications or contact your '+
      ' computer/networks support personnel.'+
      '</div>','general');
}

var tagFailCnt=0; // same var used for both
function checkExceptions() {
	if (userPreference.notifyExceptionTag!="Y") return;

	$.ajax({
	  url: '<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/checkExceptions.php',
	  global: false,
	  type: 'POST',
    data: '',
    dataType: 'html',
	  cache: false,
	  timeout: 120000,
	  //window.alert(msg);
	  success: function(msg){
		if (msg=="Y") {
			document.getElementById('tagExceptions').style.display="inline";
		} else if (msg=="N") {
			document.getElementById('tagExceptions').style.display="none";
		} else {
			alert('The alert for Exceptions checking has failed in breakpoint #1.');
		}
    tagFailCnt=0;
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
      tagFailCnt++;
	  	if (tagFailCnt>3) alert('The alert for Exceptions checking has failed in breakpoint #2.');
	  }
  	});
}
function checkDepotOrders() {
	if (userPreference.notifyDepotOrderTag!="Y") return;

	$.ajax({
	  url: '<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/checkDepotOrders.php',
	  global: false,
	  type: 'POST',
      data: '',
      dataType: 'html',
	  cache: false,
	  timeout: 120000,
	  success: function(msg){
		if (msg=="Y") {
			document.getElementById('tagDepotOrders').style.display="inline";
		} else if (msg=="N") {
			document.getElementById('tagDepotOrders').style.display="none";
		} else {
			alert('The alert for Depot Orders checking has failed.'+msg);
		}
    tagFailCnt=0;
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
      tagFailCnt++;
	  	if (tagFailCnt>3) alert('The alert for Depot Orders checking has failed.');
	  }
  	});
}


//data collection for screensizes - close project 1 August.
function collectUserScrSize() {

  var param = 'SIZE=' + screen.width + 'x' + screen.height + '&USERID=<?php echo $userId ?>';

	$.ajax({
	  url: '<?php echo $ROOT.$PHPFOLDER ?>scripts/phpscripts/postUserScrSize.php',
	  global: false,
	  type: 'POST',
      data: param,
      dataType: 'html',
	  cache: false,
	  timeout: 1200,
	  success: function(msg){
		//no return...
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
	  	//no error...
	  }
  	});
}
//data collection end of user screen sizes...


// stats gathering

  function resizeme(){
    //fix for ie quirks mode.
    var bodyH = $(window).height();
    bodyH = bodyH-120;
    $('#wrapper').css('height',bodyH);
  }

		// to allow iframes to change location url of other iframe, called with javascript:parent.change_iframe_content
    function change_iframe_content(url)
    {
      document.getElementById('content').src=url;
    }

</script>

<!-- flyover popup and messaging boxes -->
<?php
	Messages::msgboxModalLayer();
	Messages::msgboxSubModalLayer();
	Messages::msgBoxSystemFeedback();
	Messages::msgBoxError();
	Messages::msgBoxInfo();
	Messages::msgBoxInput();
	Messages::msgBoxContent();
	Messages::tipBox();
?>

<img name='endpositioner' src='<?php echo $DHTMLROOT.$PHPFOLDER; ?>images/invis.gif' /> <!-- used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable -->
<?php


/*----------------------------
 *
 * 	DIRECT MENU LINKING -
 *
 *----------------------------*/

if(isset($_GET['m_id'])){

  $jumpTO = $_GET['m_id'];
  $menuLvl1 = $dbConn->dbQuery("select a.target, a.url from   menu_role a where uid = '".mysqli_real_escape_string($dbConn->connection, $jumpTO)."'");
  $menuLvl1 = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQL_ASSOC);

  $tabJumpTO = false;
  if(isset($_GET['tab_id'])){
    $tabJumpTO = $_GET['tab_id'];
    $menuLvl2 = $dbConn->dbQuery("select a.target, a.url from   menu_role a where uid = '".mysqli_real_escape_string($dbConn->connection, $tabJumpTO)."'");
    $menuLvl2 = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQL_ASSOC);

    $menuLvl2['url'] = str_replace(array(".\$ROOT","\$ROOT"),'../../../',$menuLvl2['url']);
    $menuLvl2['url'] = str_replace(array(".\$PHPFOLDER","\$PHPFOLDER"),$PHPFOLDER,$menuLvl2['url']);

    if(isset($_GET['param']) && strpos($menuLvl2['url'],'getContent')!==false){

      $sA = explode(',',$menuLvl2['url']);
      $parm = str_replace(array('"',')',';'), array('','',''), $sA[1]);
      if($parm==''){
        $parm = join('=',explode(':',$_GET['param']));
      } else {
        $parm = $parm . '&' . join('=',explode(':',$_GET['param']));
      }
      $menuLvl2['url'] = $sA[0] . ',"' . $parm . '");';
    }

  }


  ?>
  <script type="text/javascript">
  	window.<?php echo $menuLvl1['target']; ?>.location.href = '<?php echo $menuLvl1['url'] . '?m_id='.$jumpTO, ($tabJumpTO!==false)?('&callback='.urlencode($menuLvl2['url'])):(''); ?>';
  </script>
   <?php
}

?>
</body>
<noframes>
<body>
</body>
</noframes>
</html>
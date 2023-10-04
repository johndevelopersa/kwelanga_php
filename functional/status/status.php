<?php

  //includes
  include_once ('ROOT.php');
  include_once ($ROOT.'PHPINI.php');
  include_once ($ROOT.$PHPFOLDER.'functional/main/access_control.php');
  include_once ('systemStatusClass.php');

  //db Connect
  $dbConn = new dbConnect();
  $dbConn->dbConnection();

?>
<html>
<head>
	<title>RTT System Status &amp; Statistics</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css"">

body{margin:5px;padding:0px;}
body, table, td {font-size:9pt;font-family:verdana;}
#otday, #utday {float:left;display:block;margin:5px;font-weight:bold;font-size:55pt;text-align:center;padding:10px;
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
	-webkit-box-shadow: 1px 2px 4px rgba(0,0,0,.5);
	-moz-box-shadow: 1px 2px 4px rgba(0,0,0,.5);
	box-shadow: 1px 2px 4px rgba(0,0,0,.5);}
#otday span, #utday span {display:block;font-size:11pt;}
#otday{border:2px solid #032041;background:#0e4e96;color:#fff;}
#utday{border:2px solid #d56d08;background:#f6f863;color:#000;}
#otday b{display:block;font-size:9pt;font-weight:normal;}
#uiTopDiv{padding:4px 15px;background:#f0b12e;color:#000;border-bottom:1px solid #000;}
#uiTopDiv a{color:#000;text-decoration:none;font-style:italic}
</style>

<script type="text/JavaScript" src="<?php echo $ROOT.$PHPFOLDER; ?>js/jquery.js"></script>
<script language="JavaScript" src="<?php echo $ROOT.$PHPFOLDER; ?>libs/fushion/JSClass/FusionCharts.js"></script>
<script type="text/JavaScript">


$(document).ready(function() {
  setTimeout("autoRefresh()", 1000);
});

function autoRefresh(){

  //alert('test');
  var obj = jQuery('#refreshSec');
  var time = obj.text();

  time = time-1;
  if(time < 1){
    location.reload(true);
  } else {
	obj.text(time);
  	setTimeout("autoRefresh()", 1000);
  }
}


</script>
</head>
<body>

<div id="uiTopDiv">
	<a href="javascript:location.reload(true)">Refresh</a> | Auto-Refresh in: <span id="refreshSec">1800</span> (30 min)
</div>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>FusionCharts Free Documentation</title>
</head>

<body>
<BR /><BR /><BR />
<div align="center">
<H2>WEB Capture/Order Statistics</H2>
<TABLE BORDER="0" style="background:#efefef;">
  <TR><TD><?php systemStatus::ordersTodayGraphDay($dbConn); ?></TD></TR>
  <TR><TD><?php systemStatus::ordersTodayGraphMonth($dbConn); ?></TD></TR>
  <TR><TD><?php systemStatus::ordersTodayGraphYear($dbConn); ?></TD></TR>
  <TR><TD><?php systemStatus::ordersCasesValueGraphYear($dbConn); ?></TD></TR>
  <TR><TD><?php systemStatus::ordersTypesTotalToday($dbConn); ?></TD></TR>
</TABLE>

<BR /><BR /><BR />

<H2>User Statistics</H2>
<TABLE BORDER="0" style="background:#efefef;">
  <TR>
  	<TD><?php systemStatus::userTodayGraph($dbConn); ?></TD>
  </TR><TR>
  	<TD><?php systemStatus::userTodayGraphTotal($dbConn); ?></TD>
  </TR>
</TABLE>

<BR /><BR /><BR />
<BR /><BR /><BR />

</div>
</body>
</html>
<?php


include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'libs/common.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDepotDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/DepotTO.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");  //Custom Fields
include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
include_once($ROOT.$PHPFOLDER."elements/Messages.php");


//Database Connection
$dbConn = new dbConnect();
$dbConn->dbConnection();
$depotDAO = new DepotDAO($dbConn);


if (!isset($_SESSION)) session_start();
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION["user_id"];
$depotId = $_GET['DEPOTID'];

$getDEPOTID = (isset($_GET['DEPOTID']))?$_GET['DEPOTID']:false;
if($getDEPOTID==false){
  die("Error: invalid depot supplied!");
}
#check permissions
$adminDAO = new AdministrationDAO($dbConn);
if (!$adminDAO->hasRole($userId, $principalId, ROLE_MODIFY_DEPOT_CALENDAR)) {
  echo 'You do not have permissions to MODIFY this Depot.';
  return;
}

#has allocation to this depot.
if (!$adminDAO->hasDepot($userId, $depotId, $principalId)) {
  echo 'You do not have permissions to access this depot!';
  return;
}
$depotArr = $depotDAO->getDepotItem($depotId);
if(count($depotArr)==0){
  die("Error: invalid depot supplied!");
}
$depotArr = $depotArr[$depotId];

if($depotArr['delivery_calendar_enabled']!='Y'){
  die("Error: Depot not enabled for delivery calender!");
}
$depotParam = $depotArr['delivery_calendar_parameters'];


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<LINK href="<?php echo $DHTMLROOT.$PHPFOLDER?>css/default.css" rel="stylesheet" type="text/css">
<LINK href="<?php echo $DHTMLROOT.$PHPFOLDER?>css/uipopup_min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE">
<head><title>Depot Delivery Calendar</title></head>
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
<style type="text/css">

  body{
    background:#047;
  }

  a.action,a.action:link,a.action:visited{
    display:block;
    font-size:12pt;
    background-color:#7498bf;
    color:#fff;
    text-decoration:none;
    line-height:28px;
    padding:0px 15px;
    text-align:center;
  }
  a.action:hover{
    color:#fff;
    background-color:lightskyblue;
    box-shadow: 0px 0px 10px #002947;
  }
  .year, .depotname{
    color:aliceBlue;
    font-weight:bold;
  }
  .year{
    font-size:80px;
    line-height:60px;
  }
  .depotname{
    font-size:30px;
    line-height:30px;
  }
  .closeWindow{
    display:block;
    position:absolute;top:5px;right:5px;width:160px;
    line-height:28px;text-decoration:none;font-weight:bold;
    font-size:16px;background:#fff;color:#FA5858;
    border:1px solid #efefef;
    border-top:0px;border-right:0px;
  }
  .copy{
    margin:20px 0px;
    text-align:center;
    color:#7498bf;
    font-size:9pt;
  }
  div.monthBlk{
    background:#fff;
    padding:2px;
    box-shadow: 0px 0px 10px #002947;
  }
  div.monthHeader{background:aliceblue;}
  div.monthName{
    text-align:center;
    width:100%;
    background:#7498bf;
    line-height:26px;
    font-size:14pt;
    color:#fff;
  }
  .monthWeekdays th{
    color:#333;
    text-align:center;
    font-size:10pt;
    padding:2px;
  }
  .calendarTable td{
    padding:6px;
  }
  .monthTable{margin-bottom:8px;}
  .monthTable td{
    padding:0px;
    text-align: center;
    line-height:24px;
  }

  .day_border{
    border:1px solid #efefef;
  }

  .day_non_del, .day_non_del a.dayLink, .day_non_del a.dayLink:link, .day_non_del a.dayLink:visited{
    background:#FE9A2E;
    color:#fff;
    font-weight:bold;
    border-color:#fff;
  }
  a.dayLink,a.dayLink:link,a.dayLink:visited{
    color:#333;
    display:block;
    width:100%;
    line-height:24px;
  }

  a.dayLink:hover{
    background:red;
    color:#fff;
  }
  #boxLayer{
    position:fixed;
    top:0px;
    left:0px;
    right:0px;
    bottom:0px;
    width:100%;
    text-align:center;
    vertical-align:middle;

  }
  #boxLayerInner{
    margin-top:150px;
    background:aliceBlue;
    padding:5px 10px;
    border:2px solid #7498bf;
    box-shadow: 1px 1px 25px #333;

    width:500px;
  }
  #boxLayerContent{
    padding:25px 0px;
  }
  .day_today{
    color:#333;
    border:2px solid red;
  }
  .legend{
    color:#fff;
    margin-bottom:10px;
    padding:0px 10px;
  }
  .legend_icon{
    display:inline-block;
    width:18px;
    height:16px;
    margin-bottom:-3px;
    margin-right:10px;
  }

</style>

</head>
<body>

<?php

  $yr = isset($_GET['y']) ? $_GET['y'] : date('Y');
  $yrPrev = $yr - 1 ;
  $yrNext = $yr + 1;

  $calArr = $depotDAO->getDepotDeliveryCalendarByYear($getDEPOTID, $yr);
  $dayKeyArr = array();
  foreach($calArr as $k=>$d){
    $dayKeyArr[$k] = $d['timestamp'];
  }

  echo '<div align="center">';

      echo '<table border="0" class="tableReset"><tr><td colSpan="3" align="center">';
      echo '<div class="depotname">' . trim($depotArr['depot_name']) . "</div>";
      echo '</td></tr><tr><td width="110">';
      echo '<a href="?DEPOTID='.$depotId.'&y='.$yrPrev.'" class="action rdCrn5" >Previous</a>';

      echo '</td><td valign="top" align="center" width="220">';

      echo '<div class="year">' . $yr . "</div>";
      if(isset($_GET['y'])&&$_GET['y']!=date('Y')){
        echo '<a  href="?DEPOTID='.$depotId.'" style="color:#fff;">[Current Year]</a>';
      } else {
        echo '<br>';
      }
      echo '</td><td width="110">';
      echo '<a  href="?DEPOTID='.$depotId.'&y='.$yrNext.'" class="action rdCrn5" >Next</a>';
      echo '</td></tr></table>';

    echo '<a href="javascript:closeWindow();" class="closeWindow rdCrn8">CLOSE</a>';
  echo '</div>';
  echo '<div style="clear:both"></div>';

  echo '<div align="left" class="legend"><span class="legend_icon day_non_del"></span> Non Delivery Day</div>';

  echo '<table border="0" width="100%" class="tableReset calendarTable"><tr>';
  for($month = 1; $month <= 12; $month++){

    if($month == 5 || $month == 9){
      echo '</tr><tr>';
    }
    echo '<td valign="top">';
    echo '<div >';

    $date = mktime(12, 0, 0, $month, 1, $yr);
    $daysInMonth = date("t", $date);
    // calculate the position of the first day in the calendar (sunday = 1st column, etc)
    $offset = date("w", $date);
    $rows = 1;

    echo '<div class="monthBlk rdCrn8">';

    echo '<div class="monthHeader rdCrn8">';
      echo '<div class="monthName rdCrn8">' . date("F", $date) . '</div>';
      echo '<table border="0" class="tableReset monthWeekdays" width="100%">';
      echo '<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thur</th><th>Fri</th><th>Sat</th></tr>';
      echo '</table>';
    echo '</div>';


    echo '<table border="0" class="tableReset monthTable" width="100%" cellpadding="0" cellspacing="0">';
    echo '<tr>';

    for($i = 1; $i <= $offset; $i++){echo "<td></td>";}
    for($day = 1; $day <= $daysInMonth; $day++){
      if( ($day + $offset - 1) % 7 == 0 && $day != 1)
      {
      echo "</tr><tr>";
      $rows++;
      }

      $class = '';
      if(date('Ynj')==$yr.$month.$day){
        $class = 'day_today';
      }

      $off = 'day_border';
      $wkDay = ($day + $offset - 1)%7;
      $wkend = false;
      if(trim(CommonUtils::getParamValuesFromString($depotParam, "p1"))=='WKEND'){
        if($wkDay == 0 || $wkDay == 6){ //and parameter set like that.
          $off .= ' day_non_del';
          $wkend = true;
        }
      }

      $isNonDD = false;
      $nonDDArr = array();
      if(($key=array_search(mktime(0,0,0,$month,$day,$yr), $dayKeyArr))!==false){
        $isNonDD = true;
        $off .= ' day_non_del';
        $nonDDArr = $calArr[$key];
      }

      $title = ($isNonDD)?('title="' . strtoupper(trim($nonDDArr['comment'])) . '"'):('');

      $dayHTML = $day;
      if(!$wkend){
        $dayHTML = '<a href="javascript:showForm('.$day.','.$month.','.$yr.')" class="dayLink" '.$title.'>'.$day.'</a>';
      }
      echo "<td class=\"".$off." ".$class."\" id='dayid_".mktime(0,0,0,$month,$day,$yr)."'>" . $dayHTML  . "</td>";
    }

    while( ($day + $offset) <= $rows * 7){
      echo "<td></td>";
      $day++;
    }

    for($i=$rows; $i <= 5 ;$i++){
      echo '</tr><td>&nbsp;</td>';
    }

    echo "</tr>\n";
    echo "</table>\n";
    echo '</div>';
    echo '</div>';
    echo '</td>';

  }
  echo '</tr></table>';

?>

<script type="text/javascript">

  function showForm(day, month, year){

    var InnerBox = '<div id="boxLayer" ><div align="center">';
    InnerBox += '<div id="boxLayerInner" class="rdCrn10"><div align="right"><a href=\"javascript:closeForm()\">close</a></div><div id="boxLayerContent"></div></div>';
    InnerBox += '</div></div>';
    jQuery(InnerBox).appendTo('body');

    AjaxRefresh("USERID=<?php echo $userId ?>&DEPOTID=<?php echo $depotId ?>&DAY="+day+"&MONTH="+month+"&YEAR="+year,
                "<?php echo $ROOT.$PHPFOLDER ?>functional/depot/deliveryCalendarForm.php",
                "boxLayerContent",
                "Loading content...",
                "");
  }

  function closeWindow(){ //ie work around
    window.close();
  }
  function closeForm(){
   $('#boxLayer').remove();
  }
  var alreadySubmitted=false;
  function submitForm(){
    if (alreadySubmitted) {
            return;
    }
    alreadySubmitted=true;
    var params ='&SETDAY=' + convertElementToArray(document.getElementsByName('DDFLAG'));
    params+='&DEPOTID='+document.getElementById("DDDEPOTID").value;
    params+='&DAY='+document.getElementById("DDDAY").value;
    params+='&MONTH='+document.getElementById("DDMONTH").value;
    params+='&YEAR='+document.getElementById("DDYEAR").value;
    params+='&DMLTYPE='+document.getElementById("DMLTYPE").value;
    params+='&DDUID='+document.getElementById("DDUID").value
    params+='&COMMENT='+encodeURIComponent(document.getElementById("DDCOMMENT").value.replace(/'/g,'').replace(/"/g,''));
    params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
    AjaxRefreshWithResult(params,
                          '<?php echo $ROOT.$PHPFOLDER ?>functional/depot/deliveryCalendarSubmit.php',
                          'alreadySubmitted=false; if (msgClass.type=="S") successCallback(msgClass);',
                          'Please wait while request is processed ...');
  }
  function successCallback(msgClass){

    closeForm();

    var setday = msgClass.identifier;
    var dayID = msgClass.identifier2;
    if(setday=='DD'){
      $('#dayid_'+dayID).removeClass('day_non_del');
    } else {
      $('#dayid_'+dayID).addClass('day_non_del');
    }
  }
</script>

  <div class="copy">Powered by Retail Trading Technologies (Pty) Ltd</div>
</body>
</html>

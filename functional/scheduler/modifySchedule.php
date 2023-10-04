<?php


//LIBS
include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'DAO/SchedulerDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');


//SESSION
if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId=$_SESSION['principal_id'];


//DB CONNECT
$dbConn = new dbConnect();
$dbConn->dbConnection();


$postDMLTYPE = (isset($_POST['DMLTYPE'])) ? $_POST['DMLTYPE'] : 'UPDATE';
$divWriteAreaID = 'chooseScheduleDetails';


//GET USER SCHEDULERS - FOR CURRENT PRINCIPAL!
$schedulerDAO = new SchedulerDAO($dbConn);
$schedules = $schedulerDAO->getAllSchedulesForUser($userId, $principalId);


#--------------------------------------------------------------------------------------------------------------------------
#
#  START OF SCREEN
#
#--------------------------------------------------------------------------------------------------------------------------


echo '<br>';

if(!count($schedules)>0){

   //NO SCHEDULES
   echo '<div align="center"><div style="width:350px;display:block;font-style:italic;padding:5px;border:1px solid lightSkyBlue;border-right:0px;border-left:0px;">No schedules created for this principal.</div>';

} else {

if($postDMLTYPE == 'UPDATE'){
  echo '<DIV style="', FONT_UNOBTRUSIVE_INFO , '">Your current active schedules, select one below to view and edit.</DIV>';
} elseif($postDMLTYPE == 'HISTORY') {
  echo '<DIV style="', FONT_UNOBTRUSIVE_INFO , '">Select a schedule below, to view its history and download previous results.</DIV>';
}
echo '<BR>';

    echo '<TABLE width="450" border="0">';
    echo '<THEAD><TR>';
    echo '<TH bgcolor="#87CEFA">Select Schedule</Th><Th>';

    //Build Scheduler DD
    echo '<SELECT name="" onChange="',($postDMLTYPE == 'UPDATE')?('refreshSchedule'):('refreshJobs'),'(this.value)">';
    echo '<OPTION style="color:#999" value="">No Schedule Selected... </OPTION>';
    foreach ($schedules as $s) {
	  echo '<option value="' . $s["uid"] . '" >' . $s["report_name"]. (($s["scheduler_scope"]=="P")?" (***PRINCIPAL SCOPE***)":"") . ' - ' . GUICommonUtils::translateScheduleType($s['job_type']) . ' (' . $s["uid"] .')</option>';
    }
    echo '</SELECT>';

    echo '</TH>';
    echo '</TR></THEAD>';
    echo '</TABLE>';

	echo '<BR>';

	echo '<div id="'.$divWriteAreaID.'"></div>';

 }

?>
<script type="text/javascript" >

function refreshSchedule(uid){
  if (uid!='' || uid!=undefined){
	AjaxRefresh("SCHEDULEID="+uid+"&DMLTYPE=<?php echo $postDMLTYPE ?>",
				"<?php echo $ROOT.$PHPFOLDER; ?>functional/scheduler/scheduleForm.php",
				"<?php echo $divWriteAreaID ?>",
				"Loading Schedule Details...",
				"");
  }

}

function refreshJobs(uid) {

	if (uid!='' || uid!=undefined){
		AjaxRefreshHTML("SCHEDULEID="+uid,
						"<?php echo $ROOT.$PHPFOLDER; ?>functional/scheduler/scheduleHistory.php",
						"chooseScheduleDetails",
						"Retrieving History...",
						"");
	}
}
</script>


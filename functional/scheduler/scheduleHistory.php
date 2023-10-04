<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/SchedulerDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
include_once($ROOT.$PHPFOLDER."DAO/DistributionDAO.php");


if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId=$_SESSION['principal_id'];


$postSCHEDULEID = (isset($_POST["SCHEDULEID"]) && $_POST["SCHEDULEID"] != '') ? ($_POST["SCHEDULEID"]) : (die());
$hasAdminRole = (isset($_SESSION['admin_user']) && $_SESSION['admin_user'] == 'Y') ? (true) : (false);


$dbConn = new dbConnect();
$dbConn->dbConnection();


$schedulerDAO = new SchedulerDAO($dbConn);
$scheduleJob = $schedulerDAO->getAllJobsForUserSchedule($userId, $principalId, $postSCHEDULEID, 7);

$distributionDAO = new DistributionDAO($dbConn);


#--------------------------------------------------------------------------------------------------------------------------
#
#  START OF SCREEN
#
#--------------------------------------------------------------------------------------------------------------------------


echo '<BR>';

if (!sizeof($scheduleJob)>0) {

  //NO HISTORY
  echo '<div align="center" ><div style="width:500px;display:block;font-style:italic;padding:5px;border:1px solid lightSkyBlue;border-right:0px;border-left:0px;">No history currently available.</div></div>';

} else {

  echo '<DIV style="', FONT_UNOBTRUSIVE_INFO , '">Displaying the selected schedule\'s history for the past week.</DIV>';
  echo "<input id='F_UID' type='hidden' value='".$postSCHEDULEID."' />";
  echo "<BR>";

	$i=0;
	foreach ($scheduleJob as $sj) {

	  if($i==7) break;

		// get the distributions
		$mfD=$distributionDAO->getDistributions($sj["distribution_source_identifier"]);

        $class = 'even';

        echo '<TABLE align="center" width="600">';
		echo '<TR class="' . GUICommonUtils::styleEO($class) . '">';
		    echo '<TD>Run Date</TD>';
		    echo '<TD>'	. $sj["run_date"];
			echo '<TD style="border-left:1px solid lightSkyBlue;">Queued Date</TD>';
			echo '<TD>' . $sj["queued_date"] . '</TD>';
		echo '</TR>';

		if ($sj["run_result"]==FLAG_ERRORTO_ERROR) $COLOR="#CC0000";
		else $COLOR="00CC00";
		echo '<TR class="' . GUICommonUtils::styleEO($class) . '">';
			echo "<TD nowrap>Run Result</TD>";
			echo '<TD colSpan="3" style="color:'.$COLOR.'">' .  GUICommonUtils::translateResult($sj["run_result"]) . '</TD>';
		echo '</TR>';

		echo '<TR class="' . GUICommonUtils::styleEO($class) . '">';
			echo "<TD nowrap>Run Message</TD>";
			echo '<TD colSpan="3">' . $sj["run_msg"] . '</TD>';
		echo '</TR>';

		echo '<TR class="' . GUICommonUtils::styleEO($class) . '">';
			echo "<TD nowrap>Attachment File</TD>";
			echo '<TD colSpan="3">';
			if ($sj["attachment_file"]!="") echo "<input type='submit' class='submit' style='margin-left:0px;' value='Download File' onclick='window.open(\"".$ROOT.$PHPFOLDER."functional/general/downloadFile.php?TYPE=REPORT&UID=".$sj['uid']."\",\"reportDownloadFile\",\"scrollbars=yes,width=400,height=550\");' />";
			if ($hasAdminRole) echo ' <small>'.basename($sj["attachment_file"]) . '</small>';
			echo "</TD>";
		echo '</TR>';

		echo '<TR class="' . GUICommonUtils::styleEO($class) . '">';
			echo "<TD>Distributions Log: </TD>";
			echo '<TD colSpan="3" style="padding:0px;">';
			if (sizeof($mfD)==0) {
				echo '<span style="color:red">ERROR: No log found!</span>';
			} else {
				echo "<TABLE style='border:0px;font-size:10px;'>";
				foreach ($mfD as $key=>$d) {
					if ($key==0) {
						echo "<TR><TH>Run Msg</TH><TH>Status</TH><TH>Recipient Address</TH></TR>";
					}
					echo "<TR><TD>{$d["run_msg"]}</TD><TD>".(GUICommonUtils::translateStatus($d["status"]))."</TD><TD>" , ($d["delivery_type"] != BT_FTP) ? ($d["addr"]) : ('FTP Server') , "</TD></TR>";
				}
				echo "</TABLE>";
			}
			echo "</TD>";
		echo '</TR>';
        echo '</TABLE>';

        //HIDE UID OF JOB/HISTORY AWAY AS IN-IMPORTANT TO CLIENT/USER
        echo '<DIV align="right" style="width:600px;font-size:7pt;color:#999;" >id.' . $sj['uid'] . '</DIV>';
        echo '<BR>';
		$i++;
	}
}

?>
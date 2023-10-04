<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();


echo "<HTML>
	  <HEAD>
		<STYLE>
			table { border:1; border-style:solid; }
			td { border-left:1; border-left-style:solid; border-right-style:solid; border-right:1; border-bottom:1px solid; }
			.odd {background-color:#e4e6cc};
		</STYLE>
	  </HEAD>
	  <BODY style='font-size:11;'>";
	

echo "<input id='params' value='' size=100 />"; // override and pass params
echo "<input class='submit' type='submit' onclick='window.location=\"".$_SERVER["PHP_SELF"]."?\"+document.getElementById(\"params\").value;' value='submit params' />"; // override and pass params


if (isset($_GET["df_1"])) $df_1=$_GET["df_1"]; else $df_1=3;	  
if (isset($_GET["dt_1"])) $dt_1=$_GET["dt_1"]; else $dt_1=0;
// 1.
echo "<BR><BR><HR>
		Orders originating from SureServer for last ".$df_1." to ".$dt_1." days prior to today, but not yet processed into Transaction Tracking.<BR>
	  <HR><BR><BR>";

// this select HAS to be done this way as it is fast, and any other way takes 1-3 minutes.

// NB there is a report for this now, so not needed anymore !
$dbConn->dbQuery("select distinct a.uid, a.principal_uid, p.name principal_name, order_number, a.order_sequence_no, capturedate, a.deleted, edi_created, edi_filename, 
						dt.description document_type, u.full_name, 
						if(dm.uid is null,'Not Found in TT','Product count differs') error_type, a.pcnt orders_prod_cnt, sum(if(ifnull(dm.uid,0)=0,0,1)) tt_prod_cnt
					from   (select a.uid, principal_uid, order_number, order_sequence_no, capturedate, deleted, edi_created, edi_filename, document_type, captureuser_uid, count(*) pcnt
							  from orders a, orders_detail b
							  where  a.deleted != 1
							  and    date(capturedate) between DATE_SUB(CURDATE(), INTERVAL {$df_1} DAY) and DATE_SUB(CURDATE(), INTERVAL {$dt_1} DAY)
							  and    a.uid = b.orders_uid
							  group  by a.uid) a
                           left join users u on a.captureuser_uid = u.uid
								   left join document_type dt on a.document_type = dt.uid
								   left join principal p on a.principal_uid = p.uid
								   left join document_master dm on dm.order_sequence_no = a.order_sequence_no
										left join document_detail dd on dm.uid = dd.document_master_uid
					group  by a.uid
					having (sum(ifnull(dm.uid,0))=0 or count(*) != a.pcnt)
					order  by capturedate desc");

GUICommonUtils::outputRS($dbConn);	
echo "<br><input type='button' onclick='document.getElementById(\"params\").value+=\"&df_1=".$df_1."&dt_1=".$dt_1."\"' value='add these params to param line' />"; // override and pass params

/*
 * Possibly in future put a check in at DETAIL product level !
 */


echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable
?>
<SCRIPT type="text/javascript">
parent.adjustMyFrameHeight();
</SCRIPT>
				
</BODY></HTML>




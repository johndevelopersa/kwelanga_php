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
$dbConn->dbConnectionAuditor();


echo "<HTML>
	  <HEAD>
		<STYLE>
			table { border:1; border-style:solid; }
			td { border-left:1; border-style:solid; border-right:1; }
		</STYLE>
	  </HEAD>
	  <BODY style='font-size:10;'>";
	

echo "<input id='params' value='' size=100 />"; // override and pass params
echo "<input class='submit' type='submit' onclick='window.location=\"".$_SERVER["PHP_SELF"]."?\"+document.getElementById(\"params\").value;' value='submit params' />"; // override and pass params


if (isset($_GET["df_1"])) $df_1=$_GET["df_1"]; else $df_1=7;	  
if (isset($_GET["dt_1"])) $dt_1=$_GET["dt_1"]; else $dt_1=1;
// 1.
echo "<BR><BR><HR>
		Triggers last fired.<BR>
	  <HR><BR><BR>";

$dbConn->dbQuery("select 'pricing' tbl, max(if(change_type='I',change_date,null)) inserts, max(if(change_type='U',change_date,null)) updates, max(if(change_type='D',change_date,null)) deletes
from pricing
union all
select 'pricing_document' tbl, max(if(change_type='I',change_date,null)) inserts, max(if(change_type='U',change_date,null)) updates, max(if(change_type='D',change_date,null)) deletes
from pricing_document
union all
select 'principal_chain_master' tbl, max(if(change_type='I',change_date,null)) inserts, max(if(change_type='U',change_date,null)) updates, max(if(change_type='D',change_date,null)) deletes
from principal_chain_master
union all
select 'principal_product' tbl, max(if(change_type='I',change_date,null)) inserts, max(if(change_type='U',change_date,null)) updates, max(if(change_type='D',change_date,null)) deletes
from principal_product
union all
select 'principal_store_master' tbl, max(if(change_type='I',change_date,null)) inserts, max(if(change_type='U',change_date,null)) updates, max(if(change_type='D',change_date,null)) deletes
from principal_store_master
union all
select 'special_field_details' tbl, max(if(change_type='I',change_date,null)) inserts, max(if(change_type='U',change_date,null)) updates, max(if(change_type='D',change_date,null)) deletes
from special_field_details
");
					
GUICommonUtils::outputRS($dbConn);	
echo "<input type='button' onclick='document.getElementById(\"params\").value+=\"&df_1=".$df_1."&dt_1=".$dt_1."\"' value='add these params to param line' />"; // override and pass params

/*
 * Possibly in future put a check in at DETAIL product level !
 */


echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable
?>
<SCRIPT type="text/javascript">
parent.adjustMyFrameHeight();
</SCRIPT>
				
</BODY></HTML>




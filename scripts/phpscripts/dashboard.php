<?php
include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");

//if(file_exists($ROOT.$PHPFOLDER."libs/CommonUtils.php")) {echo "Here<br>";} else {echo "Lost<br>";};
//echo "Should Have<br>";

if (!isset($_SESSION)) session_start();
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION['user_id'];

if(!CommonUtils::isStaffUser()){
  echo '<h1>Restricted Access</h1>';
  return;
}


?>
<HTML>
<HEAD>
	<TITLE>RUN BACKEND PROCESSES - DASHBOARD</TITLE>
<link href="<?php echo $DHTMLROOT.$PHPFOLDER ?>/css/default.css" rel="stylesheet" type="text/css">
<STYLE type="text/css">
body{margin:0px;padding:20px;}
</STYLE>
</HEAD>
<BODY>

<BR />

<div style="float:left;margin-right: 30px;">
<h1>Outgoing</h1>
<?php
	$innerHTML = "<TABLE style='background:#fff;border:0px;font-size:10px;'>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD width=\"200\"><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/viewDepotExtracts.php\" style='color:red;'>View Depot Extracts</A></strong></TD>
		<TD>Resend Depot extracts</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD width=\"200\"><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/createDepotExport.php\" style='color:red;'>Create Depot Extract</A></strong></TD>
		<TD>Create Depot replacement</TD>
	</TR>
 	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD class='odd'><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/manuallyRunExtract.php\" style='color:red;'>Manual Run Extract</A></strong></TD>
		<TD>Manually Run Extract</TD>
	</TR>
 	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD class='odd'><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/extractManage.php\" style='color:red;'>Extracts Management</A></strong></TD>
		<TD>Extracts Management</TD>
	</TR>
	</TABLE>";

	GUICommonUtils::outputBlkBlue($innerHTML,500,false);
?>
</div>

<div style="float:left;margin-bottom: 20px;">
<h1>Incoming</h1>
<?php
	$innerHTML = "<TABLE style='background:#fff;border:0px;font-size:10px;'>

	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/touExceptions.php\" style='color:red;'>Document Update Exceptions</A></strong></TD>
		<TD>junk</TD>
	</TR>

	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD width=\"200\"><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/viewEmailOrders.php\" style='color:red;'>Email Orders Viewer</A></strong></TD>
		<TD>Incoming Email Viewer</TD>
	</TR>

<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD width=\"200\"><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/testPowerBi.php\" style='color:red;'>Test PowerBi</A></strong></TD>
		<TD>Incoming Email Viewer</TD>
	</TR>
	
	</TABLE>";

	GUICommonUtils::outputBlkBlue($innerHTML,500,false);
?>
</div>

<div style="float:left;clear:both">
<h1>Misc</h1>
<?php
	$innerHTML = "<TABLE style='background:#fff;border:0px;font-size:10px;'>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD width=\"200\"><strong><A href=\"" .  $ROOT.$PHPFOLDER . "functional/status/status.php\" style='color:red;' target=\"_blank\">System Status</A></strong></TD>
		<TD>View System Status and Statistics: Orders, Users</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD width=\"200\"><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/viewDocumentPermissions.php\" style='color:red;' >View User Document Permissions</A></strong></TD>
		<TD>Check why a user cannot view a document.</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD width=\"200\"><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/user_permissions.php\" style='color:red;' >User Permissions </A></strong></TD>
		<TD>Check User => Principal Permissions.</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/system_users.php\" style='color:red;'>User Log</A></strong></TD>
		<TD>junk</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/documentPrintUnflag.php\" style='color:red;'>Document PRINT UNFLAG</A></strong></TD>
		<TD>unflag a document a user has printed!</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD width=\"200\"><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/updateStoreMissingFields.php\" style='color:red;'>updateStoreMissingFields.php</A></strong></TD>
		<TD>Sets the fields in principal_store_master after a manual insert: stripped_deliver_name, last_synch_status, last_updated</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/system_batchChecking.php\" style='color:red;'>system_batchChecking.php</A></strong></TD>
		<TD>Checks for Data Integrity across DB, particulary orders</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD ><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/system_Triggers.php\" style='color:red;'>system_Triggers.php</A></strong></TD>
		<TD>Checks Triggers are up and running</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD class='odd'><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/duplicateStoresToAnotherPrincipal.php\" style='color:red;'>Duplicate Principal-Chain Stores</A></strong></TD>
		<TD>Copy Stores from a Principal-Chain to another Principal-Chain</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD class='odd'><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/serverMaintFileList.php\" style='color:red;'>File Repository File Count</A></strong></TD>
		<TD>File Repository File Count - assess archiving requirements</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD class='odd'><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/csvStoreUpdate.php?user=1976\" style='color:red;'>Store Updates from CSV file</A></strong></TD>
		<TD>Store Updates from CSV file</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD class='odd'><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/csvStoreImport.php?user=1976\" style='color:red;'>Store Import from CSV file</A></strong></TD>
		<TD>Store Import from CSV file</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD class='odd'><strong><A href=\"" .  $ROOT.$PHPFOLDER . "scripts/phpscripts/principalInvoiceReExtraction.php\" style='color:red;'>Re-extract Invoices and Credits per day</A></strong></TD>
		<TD>Re-extract Inv/Crs extracts per day</TD>
	</TR>
	<TR class=\"".GUICommonUtils::styleEO($class)."\">
		<TD class='odd'><strong><A href=\"" .  $ROOT.$PHPFOLDER . "functional/import/importSylkoSOR.php \" style='color:red;'>Upload the Sylko SOR s</A></strong></TD>
		<TD>Upload the Sylko SOR s</TD>
	</TR>
	</TABLE>";

	GUICommonUtils::outputBlkBlue($innerHTML,500,false);
?>
</div>

</BODY>
</HTML>
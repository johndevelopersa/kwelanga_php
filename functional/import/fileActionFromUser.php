<?php
/*
 * NB: This file is called by a user that does not need a logon. 
 * The link to get here is generated by the onlineFileProcessing script to enable user to carry out an action on a file that is holding up processing. 
 */
 
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once ($ROOT.$PHPFOLDER."DAO/ImportDAO.php");
include_once ($ROOT.$PHPFOLDER."DAO/PostImportDAO.php");
include_once ($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once ($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once ($ROOT.$PHPFOLDER."libs/BroadcastingUtils.php");

$dbConn = new dbConnect(); $dbConn->dbConnection();

if (isset($_GET['MASTID'])) $postMASTID=mysql_real_escape_string(htmlspecialchars($_GET['MASTID']));
else if (isset($_POST['MASTID'])) $postMASTID=mysql_real_escape_string(htmlspecialchars($_POST['MASTID']));
else $postMASTID="";

if (isset($_GET["KEYFROMLINK"])) $postKEYFROMLINK=$_GET["KEYFROMLINK"]; else $postKEYFROMLINK=""; // the user came to this page from an email link - no userlogin necessary
if (isset($_GET["TYPE"])) $postTYPE=$_GET["TYPE"]; else $postTYPE=""; 

$importDAO = new ImportDAO($dbConn);
$mfFL = $importDAO->getFileLogItemByUId($postMASTID);

if (sizeof($mfFL)==0) {
	echo "ERROR : Invalid File Entry or File not found.<br>It may also be that you have already removed the file?";
	return;
}
if ($guid=md5($mfFL["uid"].$mfFL["vendor_uid"].$mfFL["file_name"].$mfFL["principal_uid"])!=$postKEYFROMLINK) {
	echo "ERROR : Invalid Link Key";
	return;
}

$runTime=CommonUtils::getGMTimeCompressed(0); // for filename

if ($postTYPE=="file") {
	if (file_exists($mfFL["file_name"])) {
		echo "<body style='font-family:courier' ><hr>File output started<hr>";
		echo nl2br(str_replace(' ','&nbsp;',htmlspecialchars(file_get_contents($mfFL["file_name"]))));
		echo "<hr>File output ended<hr></body>";
	} else echo "File cannot be found for reading.";
	
	return;
} else if ($postTYPE=="remove") {
	$postImportDAO = new PostImportDAO($dbConn);
	if (!file_exists($mfFL["file_name"])) {
		echo "ERROR : File cannot be found for removal!";
	} else {
		$bkupFolderError=CommonUtils::createBkupDirs(dirname($mfFL["file_name"])."/","2");
		if ($bkupFolderError===false) {
			echo "An error occurred removing file!";
			BroadcastingUtils::sendAlertEmail("Could not create bkup folders during fileActionFromUser !","Could not create bkup folders in ".dirname($mfFL["file_name"])."!","Y");
			return;
		}
		$result=rename($mfFL["file_name"], $bkupFolderError.basename($mfFL["file_name"]).".".$runTime);
		if ($result===false) {
			echo "An error occurr moving file!";
			BroadcastingUtils::sendAlertEmail("Could not move file during fileActionFromUser","Could not move file {$mfFL["file_name"]} to bkup folders during fileActionFromUser !","Y");
			continue;
		}
		echo "Successfully removed file. Processing has been released.";
		$eTO=$postImportDAO->setFileLogVendorResult($postMASTID, "Y");
		if ($eTO->type!=FLAG_ERRORTO_SUCCESS) BroadcastingUtils::sendAlertEmail("System Error","Could not update setFileLogVendorResult for uid {$mfFL["uid"]} during fileActionFromUser !".$eTO->description,"Y");
		$dbConn->dbinsQuery("commit;"); 
	}
	
	return;
}


echo "<HTML>
	  <HEAD>
	  <link href='{$DHTMLROOT}{$PHPFOLDER}css/minDefault.css' rel='stylesheet' type='text/css'>
	  <STYLE>
	  td { border-bottom:1px; border-bottom-style:solid; vertical-align:top; font-size:13;}
	  .c1 { color:#A0A0A0; font-size:12px;}
	  </STYLE>
	  </HEAD>
	  <BODY style='width:200px; font-family:Verdana,Arial,Helvetica,sans-serif;'>
	  <CENTER>";

echo "<table class='tableReset' style='border:1; border-style:solid;'>
	  <tr style='background-color:".COLOR_UNOBTRUSIVE_INFO."; color:white; text-align:center; height:120px;'>
	  <td colspan=2 style='background-image:url({$DHTMLROOT}images/retailtrading_02.jpg)'></td>
	  </tr>
	  <tr style='background-color:".COLOR_UNOBTRUSIVE_INFO."; color:white; text-align:center;'>
	  <td colspan=2>V E N D O R&nbsp;&nbsp;&nbsp;&nbsp;E D I&nbsp;&nbsp;&nbsp;&nbsp;F I L E&nbsp;&nbsp;&nbsp;&nbsp;M A N A G E M E N T&nbsp;&nbsp;&nbsp;&nbsp;C O N S O L E</td>
	  </tr>";
 
$status=($mfFL["status"]==FLAG_ERRORTO_SUCCESS)?GUICommonUtils::translateResult($mfFL["status"])." (Previously Posted), now duplicated.":GUICommonUtils::translateResult($mfFL["status"]);
$msg=($mfFL["status"]==FLAG_ERRORTO_SUCCESS)?"File waiting to be processed is duplicated":$mfFL["error_msg"];
$recommendation=($mfFL["stop_on_error"]=="Y")?"<span style='color:red;'>This import is configured to <b><u>STOP</u></b> all processing until this error is resolved (processing order of files is enforced by sequence in filename). Therefore subsequent files are not processed until this file is cleared. If you remove this file, processing will continue with next files. If you need this file to be processed before the others, rather do NOT remove the file, but instead upload a replacement file to overwrite it.</span><br><br>":"";
echo "<tr><td class='c1'>File Name:</td><td>".(basename($mfFL["file_name"]))."</td></tr>
	  <tr><td class='c1'>Processing Status:</td><td>{$status}</td></tr>
      <tr><td class='c1'>Processing Message:</td><td>{$msg}</td></tr>
	  <tr><td class='c1'>Attempts to Process:</td><td>{$mfFL["error_count"]}</td></tr>
	  <tr><td class='c1'>Recommendation:</td><td>{$recommendation} If you are sure this latest file waiting is invalid, you can remove this file from our servers.<br><br>Alternatively, if you think the error is caused by your store/product etc. masterfiles not being up-to-date, simply use the Retailtrading System to update the masterfiles and processing will continue if the error is resolved.</td></tr>
	  <tr><td class='c1'>View the File Contents:</td><td><a href='{$_SERVER["PHP_SELF"]}?MASTID={$postMASTID}&KEYFROMLINK={$postKEYFROMLINK}&TYPE=file' target='_blank'>&lt;click here&gt;</a></td></tr>
	  <tr><td colspan=2 class='c1' style='text-align:center;'><input type='submit' class='submit' value='Remove this File' onclick='window.location=\"{$_SERVER["PHP_SELF"]}?MASTID={$postMASTID}&KEYFROMLINK={$postKEYFROMLINK}&TYPE=remove\";' /></td></tr>";
	  
echo "</table>";

echo "</CENTER>
	  </BODY>
	  </HTML>";
	
$dbConn->dbClose();
?>


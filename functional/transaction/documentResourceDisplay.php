<?php

//---------------------------------------------
//
//  SCRIPT TO SHOW SCANNED RESOURCES
//
//---------------------------------------------

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
require_once $ROOT.$PHPFOLDER.'libs/storage/Storage.php';
require_once $ROOT.$PHPFOLDER.'DAO/ScansDAO.php';


if (!isset($_SESSION)) session_start();
if(!isset($_SESSION['user_id']) || !isset($_SESSION['principal_id']) || !isset($_SESSION['staff_user'])){ die(); }  //STOP ERRORS.

$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$staffUser = $_SESSION['staff_user'];

//DB conn
$dbConn = new dbConnect();
$dbConn->dbConnection();  


if(empty($_GET['DOCMASTID'])){
	echo 'error: invalid/blank document master uid';
	return;
}

$docArr = (new ScansDAO($dbConn))->getDocumentScanByDocumentMasterID((int)$_GET['DOCMASTID']);

if(!is_array($docArr) || !count($docArr)){
	echo 'error: no scans for requested document!';
	return;
}

$docArr = $docArr[0];
$publicUrl = Storage::getPublicURL(STORAGE_DOMAIN, $docArr['storage_path']);

?>
<html>
	<title>Document: <?= basename($docArr['storage_path']) ?></title>
<style>
	html,body{
		height:100%;
		width:100%;
		padding:0;
		margin:0;
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
	#topBox { 
		position:absolute;
		top:0;
		height:80px;
		padding:0 25px;		
		width:100%;
	}
	iframe { 
		position:absolute;
		top:80px;
		bottom:0;
		height:100%;
		border:0px;
		width:100%;
	}
	.btn-download{
		padding:0 15px;		
		margin-right:40px;
		margin-top:20px;
		float:right;		
		height: 32px;
		line-height:32px;
		font-size: 12px;
		font-weight: bold;
		color: #FFFFFF;
		background-color: DarkSlateGray;
		border: 0px;
		text-decoration: none;
	}
	h3 {
		margin-bottom:5px;
	}
</style>	
</body>
	<div id="topBox">
		<a href="<?= $publicUrl ?>?download=true" class="btn-download">DOWNLOAD DOCUMENT</a>
		<h3><?= basename($docArr['storage_path']) ?></h3>		
		Document No: <?= $docArr['document_no'] ?><br>
		Size : <?= $docArr['file_size_bytes'] ?> bytes
	</div>
	<iframe src="<?= $publicUrl ?>"></iframe>
</body>
</html>


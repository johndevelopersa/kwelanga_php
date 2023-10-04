<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
require $ROOT . $PHPFOLDER . "functional/main/access_control.php";
include_once $ROOT . $PHPFOLDER . 'properties/Constants.php';
require_once $ROOT . $PHPFOLDER . 'libs/storage/Storage.php';


if (!isset($_SESSION)) session_start();
$userId = $_SESSION["user_id"];
$principalId = $_SESSION["principal_id"];

if (isset($_GET['TYPE'])) $postTYPE = $_GET['TYPE']; else $postTYPE = "";
if (isset($_GET['UID'])) $postUID = $_GET['UID']; else $postUID = "";
if (isset($_GET['HTMLOUTPUT'])) $postHTMLOUTPUT = $_GET['HTMLOUTPUT']; else $postHTMLOUTPUT = "N";
if ($postUID == "") {
	echo "UID must be passed.";
	return;
}

$dbConn = new dbConnect();
$dbConn->dbConnection();

//setup S3 storage class.
$storage = new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);

$obj = array();
$fileName = "";
if ($postTYPE == "REPORT") {
	include_once($ROOT . $PHPFOLDER . 'DAO/SchedulerDAO.php');
	$schedulerDAO = new SchedulerDAO($dbConn);
	
	//get schedule job log entry!
	$obj = $schedulerDAO->getScheduleJobItemForUser($userId, $principalId, $postUID); // implements own security
	if (sizeof($obj) == 0) {
		echo "Invalid Job Schedule Id or you do not have permissions to view this Job.";
		return;
	}
	
	//get the physical file from S3.
	$keyPath = str_replace('../','',$obj[0]['attachment_file']);
	$s3Report = Storage::getObject(S3_BUCKET_NAME, $keyPath);
	
	if (!$s3Report) {
		echo "Failure locating report file: {$keyPath}";
		return;
	}	
	
	$fileSize = $s3Report->headers['size'] ?? strlen($s3Report->body);
			
	header("Content-Type: $type; name=\"" . basename($keyPath) . "\"");
	header("Content-Disposition: attachment; filename=\"" . basename($keyPath) . "\"");
	header("Content-length: $fileSize");
	header("Cache-control: private"); //use this to open files directly
	echo $s3Report->body;
	return;
	
} else if ($postTYPE == "ORDER") {
	$staffUser = (isset($_SESSION['staff_user']) && ($_SESSION['staff_user'] == "Y")) ? true : false;
	if ($staffUser === true) {
		include_once($ROOT . $PHPFOLDER . 'DAO/TransactionDAO.php');
		$tranDAO = new TransactionDAO($dbConn);
		$obj = $tranDAO->getOrdersItem($postUID);
		$viewableFileName = basename($obj[0]["edi_filename"]); // hide the fullname from the user. The real name we get from the folder has the timestamp appended. see next.
		$folder = "bkup/" . date("Y/m/", strtotime($obj[0]["capturedate"]));
		// for orders, the timestamp has been appended so have to scroll through file list
		$handle = opendir($ROOT . FILE_FTP_DOPS_PATH . $folder);
		$fileList = array();
		while (false !== ($file = readdir($handle))) {
			if (preg_match("/" . basename($obj[0]["edi_filename"]) . "/", $file)) {
				$fileList[] = $file;
			}
		}
		if (isset($fileList[0])) $fileName = $ROOT . FILE_FTP_DOPS_PATH . $folder . $fileList[0];
		if (sizeof($fileList[0]) > 1) {
			echo "More than one file found with same name but having a different time suffix.";
			return;
		}
	} else {
		echo "You do not have permissions to view this Order.";
		return;
	}

} else if ($postTYPE == "DEPOTEXPORT") {

	$staffUser = (isset($_SESSION['staff_user']) && ($_SESSION['staff_user'] == "Y")) ? true : false;

	if ($staffUser === true) {

		include_once($ROOT . $PHPFOLDER . 'DAO/TransactionDAO.php');
		include_once($ROOT . $PHPFOLDER . 'DAO/ExportDAO.php');
		$tranDAO = new TransactionDAO($dbConn);
		$exportDAO = new ExportDAO($dbConn);

		$obj = $tranDAO->getOrdersItem($postUID);
		if (!count($obj) > 0) {
			echo "Failure locating order!";
			return;
		} else {

			$viewableFileName = basename($obj[0]["edi_depot_filename"]);

			// if the path is provided, use that instead for non standard depot extract files ie. file was a confirmation
			if (basename($obj[0]["edi_depot_filename"]) != $obj[0]["edi_depot_filename"]) {

				$fullpath = dirname($obj[0]["edi_depot_filename"]) . "/";

			} else {

				$exportMapArr = $exportDAO->getOnlineExportMappingbyType('D', $obj[0]['processed_depot_uid']);
				if (count($obj) == 0) {
					echo "Failure on depot mapping!";
					return;
				}

				$bkfolder = "bkup/" . date("Y/m/d/", strtotime($obj[0]["capturedate"]));
				$fullpath = SERVER_ROOT . '/archives/exports/' . $exportMapArr[0]['folder_name'] . $bkfolder;

			}

			if (is_file($fullpath . $viewableFileName)) {
				$fileName = $fullpath . $viewableFileName;
			} else {
				echo "Failure locating backup file!";
				return;
			}

		}

	} else {
		echo "You do not have permissions to view this Order.";
		return;
	}


} else if ($postTYPE == "EXTRACTFILE") {


	$staffUser = (isset($_SESSION['staff_user']) && ($_SESSION['staff_user'] == "Y")) ? true : false;

	if ($staffUser !== true) {
		echo "You do not have permissions to view this file!";
		return;
	}
	
	include_once($ROOT . $PHPFOLDER . 'DAO/TransactionDAO.php');
	include_once($ROOT . $PHPFOLDER . 'DAO/BIDAO.php');

	$tranDAO = new TransactionDAO($dbConn);
	$biDAO = new BIDAO($dbConn);
	$seArr = $biDAO->getSmartEventItem($postUID);

	if (!count($seArr)) {
		echo "Failure locating file!";
		return;
	}

	$docPrincipalId = $tranDAO->getDocumentWithDetailIgnorePermissionsItem($seArr[0]['data_uid'])[0]['principal_uid']; //there is no other direct get document by uid???
	
	//lookup principal folder name for extracts eg: 74_capeherb
	$principalDirPartial = FILE_ARCHIVE_EXTRACTS_PATH . "{$docPrincipalId}_";
	$storageExtractsDirListing = Storage::getBucket(S3_BUCKET_NAME, $principalDirPartial, null, 10);
	if(!$storageExtractsDirListing || (is_array($storageExtractsDirListing) && !count($storageExtractsDirListing))){
		echo "Error locating principal extract direction: $principalDirPartial";
		return;
	}

	$dirWithFile = array_pop($storageExtractsDirListing)['name'];


	$pathPartsArr = explode('/', str_replace($principalDirPartial, '', $dirWithFile));
	$principalSpecialFolderName = array_shift($pathPartsArr);

	$matchDirPath = $principalDirPartial . $principalSpecialFolderName;

	$filename = $seArr[0]['general_reference_1'];
	$year = date('Y', strtotime($seArr[0]['created_date']));
	$monthStart = date('m', strtotime($seArr[0]['created_date']));
	$monthRange = 3;
	$filepath = false;
	$fileSize = 0;

	#check every matched dir for file.
	for ($mCnt = 0; $mCnt <= $monthRange; $mCnt++) {

		$month = $monthStart + $mCnt;
		$year = ($month > 12) ? $year + 1 : $year;
		$month = ($month > 12) ? ($month - 12) : $month;

		$verifyFilePath = "{$matchDirPath}/{$year}/" . str_pad(($month), 2, '0', STR_PAD_LEFT) . "/{$filename}";
		$file = Storage::getObject(S3_BUCKET_NAME, $verifyFilePath);

		if ($file !== false) {
			$filepath = $verifyFilePath;
			$fileData = $file->body;
			$fileSize = $file->headers['size'] ?? strlen($file->body);
			break;
		}
	}

	if (!$filepath) {
		echo "Failure on locating file, could be deleted!";
		return;
	}

	$viewableFileName = basename($filename);
	$fileName = $filepath;

	//handle extracts at this level!
	if ($postHTMLOUTPUT == "Y") {
		echo "<body style='font-family:courier; font-size:8px;' ><hr>File output started<hr>";
		echo nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($fileData)));
		echo "<hr>File output ended<hr></body>";
		return;
	}

	$type = "text/plain";

	header("Content-Type: $type; name=\"" . $viewableFileName . "\"");
	header("Content-Disposition: attachment; filename=\"" . $viewableFileName . "\"");
	header("Content-length: $fileSize");
	header("Cache-control: private"); //use this to open files directly
	echo($fileData);
	return;


} else if ($postTYPE == "EDI") {
	$staffUser = (isset($_SESSION['staff_user']) && ($_SESSION['staff_user'] == "Y")) ? true : false;
	if ($staffUser === true) {
		include_once($ROOT . $PHPFOLDER . 'DAO/ImportDAO.php');
		$importDAO = new ImportDAO($dbConn);
		$obj = $importDAO->getPrincipalEDIFilesProcessed($principalId, $fLUId = $postUID);
		if (sizeof($obj) == 0) {
			echo "Unable to locate File Log as passed";
			return;
		}
		$folder = str_replace("/home/retailtr", "c:", dirname($obj[0]["file_name"])) . "/processedSuccess/bkup/" . date("Y/m/", strtotime($obj[0]["processed_date"]));

		// for edi, the timestamp has been appended so have to scroll through file list
		$handle = opendir($folder);
		$fileList = array();
		while (false !== ($file = readdir($handle))) {
			if (preg_match("/" . basename($obj[0]["file_name"]) . "/", $file)) {
				$fileList[] = $file;
			}
		}
		if (isset($fileList[0])) {
			$fileName = $folder . $fileList[0];
			$viewableFileName = basename($fileName);
		}
		if (sizeof($fileList[0]) > 1) {
			echo "More than one file found with same name but having a different time suffix.";
			return;
		}
	} else {
		echo "You do not have permissions to view this EDI File.";
		return;
	}
} else {
	echo "Invalid Type passed.";
	return;
}

if (file_exists($fileName) === false) {
	echo "File could not be found. .html and .dat files are not stored for retrieval, or if this file was captured at end of the month, it is possible that processing FTP put the file into the next month backup folder.";
	return;
}

if ($postHTMLOUTPUT == "Y") {
	echo "<body style='font-family:courier; font-size:8px;' ><hr>File output started<hr>";
	echo nl2br(str_replace(' ', '&nbsp;', htmlspecialchars(file_get_contents($fileName))));
	echo "<hr>File output ended<hr></body>";
} else {
	$fsize = filesize($fileName);
	//$type = "application/octet-stream";
	$type = "text/plain";

	header("Content-Type: $type; name=\"" . $viewableFileName . "\"");
	header("Content-Disposition: attachment; filename=\"" . $viewableFileName . "\"");
	header("Content-length: $fsize");
	header("Cache-control: private"); //use this to open files directly

	echo file_get_contents($fileName);
}

$dbConn->dbClose();

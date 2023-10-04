<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');

if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId=$_SESSION["principal_id"];

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterList=urldecode($_POST["FILTERLIST"]); $postFilterList=explode(',',$postFilterList); } else $postFilterList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["CALLBACK"])) $postCALLBACK=$_POST["CALLBACK"]; else $postCALLBACK="";
if (isset($_POST["RBTYPE"])) $postRBTYPE=$_POST["RBTYPE"]; else $postRBTYPE="radio";
if (isset($_POST["COMPRESS"])) $postCOMPRESS=$_POST["COMPRESS"]; else $postCOMPRESS="Y";
if (isset($_POST["SHOWFILTER"])) $postSHOWFILTER=$_POST["SHOWFILTER"]; else $postSHOWFILTER="Y";

$CListArr=array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
$miscellaneousDAO=new MiscellaneousDAO($dbConn);

// field names for this form
$fldChosenRB=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["RBNAME"]));
$fldFilterListname="CListFilter"; // the names of the filter fields
$fldFilterListUsageArr=array(1=>"N","Y","Y","Y","Y","Y","Y");
$fldFilterListSizeArr=array(1=>"0","5","5","5","5","5","5");

$contactArr = $miscellaneousDAO->getContactTypes($principalId, "");


class CTbl {
	public $selected;
	public $uId;
	public $depotName;
	public $contactDescription;
	public $emailAddr;
	public $mobileNumber;
	public $ftpAddr;
}

foreach ($contactArr as $row) {
	$class=new CTbl;
	$class->selected=false;
	$class->uId=$row['uid'];
	$class->depotName=$row['depot_name'];
	$class->contactDescription=$row['name'];
	$class->emailAddr=$row['email_addr'];
	$class->mobileNumber=$row['mobile_number'];
	$ftpArr = (!empty($row['ftp_addr']))?unserialize($row['ftp_addr']):'';
	$class->ftpAddr = (isset($ftpArr['HOST']))?($ftpArr['HOST']):('');
	$CListArr[]=$class;
}

$pArr=GUICommonUtils::applyFilter($CListArr,$postFilterList);

$valuesRB=array(); // determine the RB values
foreach ($pArr as $row) {
	$valuesRB[]=$row->uId;
}


$headers=array("UID","Depot Name","Contact Type","Email","Mobile Number","FTP Address");
$tdExtraColArr=array(); $tdExtraRowArr=array();

// button row - must be own table due to button sizes being wider than output columns
echo "<BR>";
print("<TABLE>");
	// filter row
	if ($postSHOWFILTER=="Y") {
	GUICommonUtils::getFilterFields($fldFilterListname,
									$fldFilterListUsageArr,
									$fldFilterListSizeArr,
									$postFilterList,
									$postPAGEDEST,
									"+'&RBNAME=".$fldChosenRB."&RBTYPE={$postRBTYPE}&CALLBACK=".$postCALLBACK."'",
									$_SERVER["PHP_SELF"]);
	}

	// display data
	GUICommonUtils::outputRBTable ($headers,
								   $pArr,
								   $fldChosenRB,
								   $valuesRB,
								   $postCALLBACK,
								   $postRBTYPE,
								   "",
								   "");
print("</TABLE>");


?>
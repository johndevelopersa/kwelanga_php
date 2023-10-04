<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');

if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId=$_SESSION['principal_id'];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterAList=urldecode($_POST["FILTERLIST"]); $postFilterAList=explode(',',$postFilterAList); } else $postFilterAList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["RBTYPE"])) $postRBTYPE=$_POST["RBTYPE"]; else $postRBTYPE="tick";
if (isset($_POST["RBNAME"])) $postRBNAME=$_POST["RBNAME"]; else $postRBNAME="";
if (isset($_POST["CALLBACK"])) $postCALLBACK=$_POST["CALLBACK"]; else $postCALLBACK="";

// field names for this form
$fldChosenARB=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["RBNAME"]));
$fldFilterAListname="AListFilter"; // the names of the filter fields
$fldFilterAListUsageArr=array(1=>"N","Y","Y");
$fldFilterAListSizeArr=array(1=>"0","5","5");

$AListArr=array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
$storeDAO=new StoreDAO($dbConn);
$areaArr=$storeDAO->getPrincipalAreas($principalId);

class ATbl {
	public $selected;
	public $uId;
	public $description;
}
foreach ($areaArr as $row) {
	$valuesRB[]=$row['uid'];
	$class=new ATbl;
	$class->selected=false;
	$class->uId=$row['uid'];
	$class->description=$row['description'];

  $AListArr[] = $class;

}
$pArr = GUICommonUtils::applyFilter($AListArr,$postFilterAList);

$valuesRB=array(); // determine the RB values
foreach ($pArr as $row) {
	$valuesRB[]=$row->uId;
}


// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterAListname,
									$fldFilterAListUsageArr,
									$fldFilterAListSizeArr,
									$postFilterAList,
									$postPAGEDEST,
									"+'&RBNAME=".$fldChosenARB."&RBTYPE=".$postRBTYPE."&CALLBACK=".$postCALLBACK."'",
									$ROOT.$PHPFOLDER."functional/administration/adminAreasListTable.php");
	// the data

	GUICommonUtils::outputRBTable (array("UId","Area Description"),
								   $pArr,
								   $fldChosenARB,
								   $valuesRB,
								   $postCALLBACK,
								   $postRBTYPE,
								   "",
								   "");
print("</TABLE>");


?>
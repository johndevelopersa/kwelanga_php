<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

// divs
$divAjaxMainContentArea="ajaxMainContentArea"; 

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterPSList=urldecode($_POST["FILTERLIST"]); $postFilterPSList=explode(',',$postFilterPSList); } else $postFilterPSList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["CALLBACK"])) $postCALLBACK=$_POST["CALLBACK"]; else $postCALLBACK=""; 

if (!isset($_SESSION)) session_start;

echo "<BR>";

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// field names for this form
$fldChosenGSRB=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["RBNAME"]));
$fldFilterPSListname="PSListFilter"; // the names of the filter fields
$fldFilterPSListUsageArr=array(1=>"N","Y","Y","Y","Y","Y");
$fldFilterPSListSizeArr=array(1=>"0","5","5","5","5","5");

$GSListArr=array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
$storeDAO=new StoreDAO($dbConn);

$headers=array("Deliver Name","Deliver Addr 1","Deliver Addr 2","VAT No.","Branch Code");
$storeArr=$storeDAO->getAllGlobalStores(); 
class GSTbl {
	public $selected;
	public $deliver_name;
	public $deliver_add1;
	public $deliver_add2;
	public $vatNo;
	public $branchCode;
}
foreach ($storeArr as $row) {
	$class=new GSTbl;
	$class->selected=false;
	$class->deliver_name=$row['deliver_name'];
	$class->deliver_add1=$row['deliver_add1'];
	$class->deliver_add2=$row['deliver_add2'];
	$class->vatNo=$row['vat_number'];
	$class->branchCode=$row['branch_code'];
	$GSListArr[]=$class;
	$valuesRB[]=$row['uid'];
}

$pArr=GUICommonUtils::applyFilter($GSListArr,$postFilterPSList);

// the applyfilter above keeps the original indexes
$valuesRB2=array();
foreach ($pArr as $key=>$value) {
	$valuesRB2[]=$valuesRB[$key];	
}

// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterPSListname,
									$fldFilterPSListUsageArr,
									$fldFilterPSListSizeArr,
									$postFilterPSList,
									$postPAGEDEST,
									"+'&RBNAME=".$fldChosenGSRB."&CALLBACK=".$postCALLBACK."'",
									$ROOT.$PHPFOLDER."functional/administration/adminGlobalStoresListTable.php");
	// the data
	GUICommonUtils::outputRBTable ($headers,
								   $pArr,
								   $fldChosenGSRB,
								   $valuesRB2,
								   $postCALLBACK,
								   "radio",
								   "",
								   "");
print("</TABLE>");

$dbConn->dbClose();

?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

// divs
$divAjaxMainContentArea="ajaxMainContentArea"; 

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterPSList=urldecode($_POST["FILTERLIST"]); $postFilterPSList=explode(',',$postFilterPSList); } else $postFilterPSList='';
if (isset($_POST["PRINCIPALID"])) $postPRINCIPALID=$_POST["PRINCIPALID"]; else $postPRINCIPALID="";
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"]; else $postUSERID="";
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["CALLBACK"])) $postCALLBACK=$_POST["CALLBACK"]; else $postCALLBACK=""; 

if (!isset($_SESSION)) session_start;

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// field names for this form
$fldChosenGSRB=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["RBNAME"]));
$fldFilterPSListname="PSListFilter"; // the names of the filter fields
$fldFilterPSListUsageArr=array(1=>"N","Y","Y","Y");
$fldFilterPSListSizeArr=array(1=>"0","5","5","5");

$GCListArr=array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
$storeDAO=new StoreDAO($dbConn);

$headers=array("UID","Chain Name","Status");
$chainArr=$storeDAO->getAllPrincipalChainsForUser($postUSERID,$postPRINCIPALID, CHAIN_FILTER_PRICE); 
class PCTbl {
	public $selected;
	public $principal_chain_uid;
	public $chainName;
	public $status;
}
foreach ($chainArr as $row) {
	$class=new PCTbl;
	$class->selected=false;
	$class->principal_chain_uid=$row['principal_chain_uid'];
	$class->chainName=$row['chain_name'];
	$class->status=$row['status'];
	$GCListArr[]=$class;
}

$pArr=GUICommonUtils::applyFilter($GCListArr,$postFilterPSList);

$valuesRB=array(); // determine the RB values
foreach ($pArr as $row) {
	$valuesRB[]=$row->principal_chain_uid; 
}


echo "<BR>";
// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterPSListname,
									$fldFilterPSListUsageArr,
									$fldFilterPSListSizeArr,
									$postFilterPSList,
									$postPAGEDEST,
									"+'&RBNAME=".$fldChosenGSRB."&CALLBACK=".$postCALLBACK."&USERID=".$postUSERID."&PRINCIPALID=".$postPRINCIPALID."'",
									$ROOT.$PHPFOLDER."functional/administration/adminPrincipalChainsListTable.php");
	// the data
	GUICommonUtils::outputRBTable ($headers,
								   $pArr,
								   $fldChosenGSRB,
								   $valuesRB,
								   $postCALLBACK,
								   "radio",
								   "",
								   "");
print("</TABLE>");

$dbConn->dbClose();

?>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterUCList=urldecode($_POST["FILTERLIST"]); $postFilterUCList=explode(',',$postFilterUCList); } else $postFilterUCList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"]; else $postUSERID="";
if (isset($_POST["PRINCIPALID"])) $postPRINCIPALID=$_POST["PRINCIPALID"]; else $postPRINCIPALID="";

if ($postUSERID=="") {
	print("No User Chosen");
	return;
}

// field names for this form
$fldFilterUCListname="UCListFilter"; // the names of the filter fields
$fldFilterUCListUsageArr=array(1=>"Y","Y");
$fldFilterUCListSizeArr=array(1=>"5","5");

include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
$storeDAO=new StoreDAO($dbConn);
$UCArr=$storeDAO->getAllPrincipalChainsForUser($postUSERID,$postPRINCIPALID);

// strip off the columns we want. Remember that fetch_array doubles up everything.
$UCListArr=array();
class UCTbl {
	public $principal_chain_uid;
	public $chain_name;
}
// determine the RB values
foreach ($UCArr as $row) {
	$class=new UCTbl;
	$class->principal_chain_uid=$row['principal_chain_uid'];
	$class->chain_name=$row['chain_name'];
	$UCListArr[]=$class;
}

$UCListArr=GUICommonUtils::applyFilter($UCListArr,$postFilterUCList);

$valuesRB=array(); // determine the RB values
foreach ($UCListArr as $row) {
	$valuesRB[]=$row->principal_chain_uid; 
}


// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterUCListname,
									$fldFilterUCListUsageArr,
									$fldFilterUCListSizeArr,
									$postFilterUCList,
									$postPAGEDEST,
									"+'&USERID=".$postUSERID."&PRINCIPALID=".$postPRINCIPALID."'",
									$ROOT.$PHPFOLDER."functional/administration/adminUserChainsListTable.php");
	// the data
	GUICommonUtils::outputTable (array("UId","Chain Name"),
								   $UCListArr,
								   "",
								   "");
print("</TABLE>");

$dbConn->dbClose();
?>		



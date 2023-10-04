<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');

if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterCList=urldecode($_POST["FILTERLIST"]); $postFilterCList=explode(',',$postFilterCList); } else $postFilterCList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["ADMINVIEW"])) $postADMINVIEW=$_POST["ADMINVIEW"]; else $postADMINVIEW="N";
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"]; else $postUSERID="N";
if (isset($_POST["PRINCIPALID"])) $postPRINCIPALID=$_POST["PRINCIPALID"]; else $postPRINCIPALID="";
if (isset($_POST["RBTYPE"])) $postRBTYPE=$_POST["RBTYPE"]; else $postRBTYPE="tick";
if (isset($_POST["CALLBACK"])) $postCALLBACK=$_POST["CALLBACK"]; else $postCALLBACK="";

// field names for this form
$fldChosenCRB=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["RBNAME"]));
$fldFilterCListname="CListFilter"; // the names of the filter fields
$fldFilterCListUsageArr=array(1=>"N","Y","Y");
$fldFilterCListSizeArr=array(1=>"0","5","5");

$CListArr=array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
$CListSelArr = array();
$storeDAO=new StoreDAO($dbConn);
// sometimes the administrator needs to select chains from global list, other times from list recipient user has eg. when adding stores by chain
if ($postADMINVIEW=="Y") $chainArr=$storeDAO->getAllChainsForPrincipal($postPRINCIPALID);
else $chainArr=$storeDAO->getAllPrincipalChainsForUser($userId,$postPRINCIPALID);

class CTbl {
	public $selected;
	public $principalChainUId;
	public $chain_name;
}
foreach ($chainArr as $row) {
	$valuesRB[]=$row['principal_chain_uid'];
	$class=new CTbl;
	$class->selected=false;
	$class->principalChainUId=$row['principal_chain_uid'];
	$class->chain_name=$row['chain_name'];

    if(isset($_POST['PAGESELECTED']) && in_array($row['principal_chain_uid'], explode(',', $_POST['PAGESELECTED']))){
      $CListSelArr[$row['principal_chain_uid']] = $class;  //protect from filter.
    } else {
       $CListArr[$row['principal_chain_uid']] = $class;
    }

}

$pArr = GUICommonUtils::applyFilter($CListArr,$postFilterCList);
$pArr = $CListSelArr + $pArr;  //apend selected to filtered arreay.

$valuesRB=array(); // determine the RB values
foreach ($pArr as $row) {
	$valuesRB[]=$row->principalChainUId;
}


// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterCListname,
									$fldFilterCListUsageArr,
									$fldFilterCListSizeArr,
									$postFilterCList,
									$postPAGEDEST,
									"+'&RBNAME=".$fldChosenCRB."&RBTYPE=".$postRBTYPE."&ADMINVIEW=".$postADMINVIEW."&PRINCIPALID=".$postPRINCIPALID."&CALLBACK=".$postCALLBACK."&PAGESELECTED='+convertElementToArray(document.getElementsByName('" . $fldChosenCRB . "'))+''",
									$ROOT.$PHPFOLDER."functional/administration/adminChainsListTable.php");
	// the data
	GUICommonUtils::outputRBTable (array("UId","Chain Name"),
								   $pArr,
								   $fldChosenCRB,
								   $valuesRB,
								   $postCALLBACK,
								   $postRBTYPE,
								   "",
								   "");
print("</TABLE>");

$dbConn->dbFree();
$dbConn->dbClose();
?>



<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
ob_start(); //Turn on output buffering	
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterUPSList=urldecode($_POST["FILTERLIST"]); $postFilterUPSList=explode(',',$postFilterUPSList); } else $postFilterUPSList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["PRINCIPALID"])) $postPRINCIPALID=$_POST["PRINCIPALID"]; else $postPRINCIPALID="";
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"]; else $postUSERID="";
if (isset($_POST["PAGEOWNEDBY"])) $pageOwnedBy=$_POST["PAGEOWNEDBY"]; else if (isset($_GET["PAGEOWNEDBY"])) $pageOwnedBy=$_GET["PAGEOWNEDBY"]; else $pageOwnedBy="P";

if ($postUSERID=="") {
	print("No User Chosen");
	return;
}
if ($postPRINCIPALID=="") {
	print("No Principal Chosen");
	return;
}

// field names for this form
$fldFilterUPSListname="UPSListFilter"; // the names of the filter fields
$fldFilterUPSListUsageArr=array(1=>"Y","Y","Y","Y","Y","Y","Y");
$fldFilterUPSListSizeArr=array(1=>"5","5","5","5","5","5","5");

include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
$storeDAO=new StoreDAO($dbConn);
$UPSArr=$storeDAO->getUserPrincipalStoreArray($postUSERID,$postPRINCIPALID,"",$showVendorStores=($pageOwnedBy=="B")?true:false);

// strip off the columns we want. Remember that fetch_array doubles up everything.
$UPSListArr=array();
class UPSTbl {
	public $uid;
	public $store_uid;
	public $store_name;
	public $deliver_add1;
	public $deliver_add2;
	public $depot_name;
	public $chain_name;
}
// determine the RB values
foreach ($UPSArr as $row) {
	if ($row['principal_uid']==$postPRINCIPALID) {
		$class=new UPSTbl;
		$class->uid=$row['uid'];
		$class->store_uid=$row['psm_uid'];
		$class->store_name=$row['store_name'];
		$class->deliver_add1=$row['deliver_add1'];
		$class->deliver_add2=$row['deliver_add2'];
		$class->depot_name=$row['depot_name'];
		$class->chain_name=$row['chain_name'];
		$UPSListArr[]=$class;
	}
}

$UPSListArr=GUICommonUtils::applyFilter($UPSListArr,$postFilterUPSList);

$valuesRB=array(); // determine the RB values
foreach ($UPSListArr as $row) {
	$valuesRB[]=$row->uid;
}


// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterUPSListname,
									$fldFilterUPSListUsageArr,
									$fldFilterUPSListSizeArr,
									$postFilterUPSList,
									$postPAGEDEST,
									"+'&USERID=".$postUSERID."&PRINCIPALID=".$postPRINCIPALID."'",
									$ROOT.$PHPFOLDER."functional/administration/adminUserPrincipalStoresListTable.php",
									$inclOptions=array(1,2) // show status and ownedBy extra params
									);
	// the data
	GUICommonUtils::outputTable (array("UId","Store UId","Store Name","Deliver Addr 1","Deliver Addr 2","Depot Name","Chain Name"),
								   $UPSListArr,
								   "",
								   "");
print("</TABLE>");

$dbConn->dbFree();
$dbConn->dbClose();

$htmlBody = ob_get_clean();
$htmlBody = gzencode($htmlBody, 9, FORCE_GZIP);
header ("Content-Encoding: gzip");
header ('Content-Length: '.strlen($htmlBody));
echo $htmlBody;
?>
<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
ob_start(); //Turn on output buffering
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');


//Session
if (!isset($_SESSION)) session_start;
$userId = $_SESSION["user_id"];
$principalId = $_SESSION["principal_id"];
$depotId = $_SESSION["depot_id"];

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();


// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterPSList = urldecode($_POST["FILTERLIST"]); $postFilterPSList=explode(',',$postFilterPSList); } else $postFilterPSList='';
$postPAGEDEST     = (isset($_POST["PAGEDEST"]))    ? ($_POST["PAGEDEST"]) : ('');
$postADMINVIEW    = (isset($_POST["ADMINVIEW"]))   ? ($_POST["ADMINVIEW"]) : ('');
$postCALLBACK     = (isset($_POST["CALLBACK"]))    ? ($_POST["CALLBACK"]) : ('');
$postRBTYPE       = (isset($_POST["RBTYPE"]))      ? ($_POST["RBTYPE"]) : ('');
$postUSERID       = (isset($_POST["USERID"]))      ? ($_POST["USERID"]) : ('');
$postPRINCIPALID  = (isset($_POST["PRINCIPALID"])) ? ($_POST["PRINCIPALID"]) : ('');
if (isset($_POST["PAGEOWNEDBY"])) $pageOwnedBy=$_POST["PAGEOWNEDBY"]; else if (isset($_GET["PAGEOWNEDBY"])) $pageOwnedBy=$_GET["PAGEOWNEDBY"]; else $pageOwnedBy="P";

// field names for this form
$fldChosenPSRB = htmlspecialchars($_POST["RBNAME"]);
$fldFilterPSListname = 'PSListFilter'; // the names of the filter fields

$sqlFilterArr = array();
if ($postADMINVIEW=="Y") {

  //Filter Fields Setup
  $fldFilterPSListUsageArr = array(1 => 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');
  $fldFilterPSListSizeArr = array(1 => '0', '5', '5', '5', '5', '5', '5', '5', '5', '5');

  //Build filter for sql query
  if (isset($postFilterPSList[1]) && $postFilterPSList[1] != '') { $sqlFilterArr['uid'] = $postFilterPSList [1]; }
  if (isset($postFilterPSList[2]) && $postFilterPSList[2] != '') { $sqlFilterArr['store'] = $postFilterPSList [2]; }
  if (isset($postFilterPSList[3]) && $postFilterPSList[3] != '') { $sqlFilterArr['del_add1'] = $postFilterPSList [3]; }
  if (isset($postFilterPSList[4]) && $postFilterPSList[4] != '') { $sqlFilterArr['del_add2'] = $postFilterPSList [4]; }
  if (isset($postFilterPSList[5]) && $postFilterPSList[5] != '') { $sqlFilterArr['prin'] = $postFilterPSList [6]; } // note that 5 is skipped !
  if (isset($postFilterPSList[6]) && $postFilterPSList[6] != '') { $sqlFilterArr['depot'] = $postFilterPSList [7]; }
  if (isset($postFilterPSList[7]) && $postFilterPSList[7] != '') { $sqlFilterArr['chain'] = $postFilterPSList [8]; }
  if (isset($postFilterPSList[8]) && $postFilterPSList[8] != '') { $sqlFilterArr['old_account'] = $postFilterPSList [9]; }

} else {

  //Filter Fields Setup
  $fldFilterPSListUsageArr = array(1 => 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y','Y','Y');
  $fldFilterPSListSizeArr = array(1 => '0', '5', '5', '5', '5', '5', '5','5','5');

  //Build filter for sql query
  if (isset($postFilterPSList[1]) && $postFilterPSList[1] != '') { $sqlFilterArr['uid'] = $postFilterPSList [1]; }
  if (isset($postFilterPSList[2]) && $postFilterPSList[2] != '') { $sqlFilterArr['store'] = $postFilterPSList [2]; }
  if (isset($postFilterPSList[3]) && $postFilterPSList[3] != '') { $sqlFilterArr['del_add1'] = $postFilterPSList [3]; }
  if (isset($postFilterPSList[4]) && $postFilterPSList[4] != '') { $sqlFilterArr['del_add2'] = $postFilterPSList [4]; }
  if (isset($postFilterPSList[6]) && $postFilterPSList[6] != '') { $sqlFilterArr['depot'] = $postFilterPSList [6]; } // note that 5 is skipped !
  if (isset($postFilterPSList[7]) && $postFilterPSList[7] != '') { $sqlFilterArr['chain'] = $postFilterPSList [7]; }
  if (isset($postFilterPSList[8]) && $postFilterPSList[8] != '') { $sqlFilterArr['old_account'] = $postFilterPSList [8]; }

}

$PSListArr=array(); // strip off the columns we want. Remember that fetch_array doubles up everything.

$storeDAO=new StoreDAO($dbConn);
// note we pass the SUPERUSER's ID (aka the person doing the action), not the user who we are adding the store to
// the SU can only add stores to users for which they have access to themselves


if ($postADMINVIEW=="Y") {

  $headers=array("UID","Store Name","Deliver Addr 1","Deliver Addr 2","GLN","Principal Name","Depot Name","Chain Name","Generic Principal Lookup");

  //get all stores for principals that the user is registerd for, not stores that the user is registered for
  if (isset($_POST["FILTERLIST"])){
    $storeArr = $storeDAO->getAllPrincipalStoresUser($postUSERID, $postPRINCIPALID,$sqlFilterArr);
  } else {
    $storeArr = array();
  }

  class PSTbl {
    public $selected;
    public $uid;
    public $store_name;
    public $deliver_add1;
    public $deliver_add2;
    public $ean_code;
    public $principal_name;
    public $depot_name;
    public $chain_name;
    public $old_account;
  }

  foreach($storeArr as $row) {
    $class = new PSTbl();
    $class->selected = false;
    $class->uid = $row ['psm_uid'];
    $class->store_name = $row ['store_name'];
    $class->deliver_add1 = $row ['deliver_add1'];
    $class->deliver_add2 = $row ['deliver_add2'];
    $class->ean_code = $row ['ean_code'];
    $class->principal_name = $row ['principal_name'];
    $class->depot_name = $row ['depot_name'];
    $class->chain_name = $row ['chain_name'];
    $class->old_account = $row ['old_account'];

    $PSListArr [] = $class;
  }

} else {
	$headers=array("PS UID","Store Name","Deliver Addr 1","Deliver Addr 2","GLN","Depot Name","Chain Name","Generic Principal Lookup");

    //Include Special fields header if principal has them
    include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
    $miscDAO = new MiscellaneousDAO($dbConn);
    $smpf=$miscDAO->getPrincipalSpecialFields($principalId,CT_STORE_SHORTCODE);

    if(count($smpf)>0){
      $smpfHeader = array();
      foreach($smpf as $sf){
      $headers[] = $sf['name'];
      $fldFilterPSListUsageArr[] = 'Y';
      $fldFilterPSListSizeArr[] = '5';
      }
    }


	// get stores the user is registered for
    if (isset($_POST["FILTERLIST"])){
	  $storeArr=$storeDAO->getUserPrincipalStoreArray($_SESSION['user_id'],$postPRINCIPALID,"",$filterArr=$sqlFilterArr,$showVendorStores=($pageOwnedBy=="B")?true:false, false, (CommonUtils::isDepotUser())?$depotId:false);
    } else {
      $storeArr = array();
    }

	class PSTbl {
		public $selected;
		public $uid;
		public $store_name;
		public $deliver_add1;
		public $deliver_add2;
		public $ean_code;
		public $depot_name;
		public $chain_name;
		public $old_account;
		//public $special_fields; - has an issue when column is removed.
	}

	foreach ($storeArr as $row) {
		$class=new PSTbl;
		$class->selected       = false;
		$class->uid            = $row['psm_uid'];
		$class->store_name     = $row['store_name'];
		$class->deliver_add1   = $row['deliver_add1'];
		$class->deliver_add2   = $row['deliver_add2'];
		$class->ean_code   	   = $row['ean_code'];
		$class->depot_name     = $row['depot_name'];
		$class->chain_name     = $row['chain_name'];
        $class->old_account = $row ['old_account'];


		if(count($smpf)>0){
		  $specialFldList = $row['special_fields'];
		  $specialFldArr = explode(',',$specialFldList);
		  if(count($specialFldArr)>0){
	        $dataArr = array();
  		    foreach($smpf as $k=>$no){
  		      $field = $no['name'] . '_spf';
  		      $class->$field = trim($specialFldArr[$k]);
  		    }
		  }
		}

		$PSListArr[] = $class;
	}
}

//SPECIAL FIELD FILTERING OCCURS ON THIS LEVEL NOT DAO.
$pArr=GUICommonUtils::applyFilter($PSListArr,$postFilterPSList);

$valuesRB=array(); // determine the RB values
foreach ($pArr as $row) {
	$valuesRB[]=$row->uid;
}

// button row - must be own table due to button sizes being wider than output columns

echo '<TABLE>';
	// filter row
	GUICommonUtils::getFilterFields($fldFilterPSListname,
									$fldFilterPSListUsageArr,
									$fldFilterPSListSizeArr,
									$postFilterPSList,
									$postPAGEDEST,
									"+'&USERID=".$postUSERID."&RBNAME=".$fldChosenPSRB."&RBTYPE=".$postRBTYPE."&ADMINVIEW=".$postADMINVIEW."&CALLBACK=".$postCALLBACK."&PRINCIPALID=".$postPRINCIPALID."'",
									$ROOT.$PHPFOLDER."functional/administration/adminPrincipalStoresListTable.php",
									$inclOptions=array(1,2) // show status and ownedBy extra params
									);


// the data - ONLY DISPLAY AFTER FILTERED
if (isset($_POST["FILTERLIST"])){
	GUICommonUtils::outputRBTable ($headers,
								   $pArr,
								   $fldChosenPSRB,
								   $valuesRB,
								   $postCALLBACK,
								   $postRBTYPE,
								   "",
								   "");
} else {
  echo '<tr><td colspan="7"><span style="color:red">Please use filters to define your list.</span></td></tr>';
  GUICommonUtils::outputRBTable ($headers,array(),'','','','','','');
}

echo '</TABLE>';


$dbConn->dbClose();

$htmlBody = ob_get_clean();
$htmlBody = gzencode($htmlBody, 9, FORCE_GZIP);
header ("Content-Encoding: gzip");
header ('Content-Length: '.strlen($htmlBody));
echo $htmlBody;
?>
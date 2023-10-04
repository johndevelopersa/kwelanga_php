<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterUPDList=urldecode($_POST["FILTERLIST"]); $postFilterUPDList=explode(',',$postFilterUPDList); }
else $postFilterUPDList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"];
else $postPAGEDEST="";
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"];
else $postUSERID="";
if (isset($_POST["FILTERONPRINCIPAL"])) $postFILTERONPRINCIPAL=$_POST["FILTERONPRINCIPAL"]; else $postFILTERONPRINCIPAL="";
if (isset($_POST["FILTERPRINCIPALVALUE"])) $postFILTERPRINCIPALVALUE=$_POST["FILTERPRINCIPALVALUE"]; else $postFILTERPRINCIPALVALUE="";

if ($postUSERID=="") {
	print("No User Chosen");
	return;
}

// field names for this form
$fldFilterUPDListname="UPDListFilter"; // the names of the filter fields
$fldFilterUPDListUsageArr=array(1=>"Y","Y","Y","Y","Y");
$fldFilterUPDListSizeArr=array(1=>"5","5","5","5","5");

include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
$principalDAO=new PrincipalDAO($dbConn);
$UPDArr=$principalDAO->getUserPrincipalDepotArray($postUSERID,"");

// strip off the columns we want. Remember that fetch_array doubles up everything.
$UPDListArr=array();
class UPDTbl {
	public $uid;
	public $depot_id;
	public $depot_name;
	public $principal_id;
	public $principal_name;
}
// determine the RB values
foreach ($UPDArr as $row) {
	if ($postFILTERONPRINCIPAL=="Y") {
		if ($row['principal_id']==$postFILTERPRINCIPALVALUE) {
			$class=new UPDTbl;
			$class->uid=$row['uid'];
			$class->depot_id=$row['depot_id'];
			$class->depot_name=$row['depot_name'];
			$class->principal_id=$row['principal_id'];
			$class->principal_name=$row['principal_name'];
			$UPDListArr[]=$class;
		}
	} else {
		$class=new UPDTbl;
		$class->uid=$row['uid'];
		$class->depot_id=$row['depot_id'];
		$class->depot_name=$row['depot_name'];
		$class->principal_id=$row['principal_id'];
		$class->principal_name=$row['principal_name'];
		$UPDListArr[]=$class;
	}
}

$UPDListArr=GUICommonUtils::applyFilter($UPDListArr,$postFilterUPDList);

$valuesRB=array(); // determine the RB values
foreach ($UPDListArr as $row) {
	$valuesRB[]=$row->uid; 
}

// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterUPDListname,
									$fldFilterUPDListUsageArr,
									$fldFilterUPDListSizeArr,
									$postFilterUPDList,
									$postPAGEDEST,
									"+'&USERID=".$postUSERID."&FILTERONPRINCIPAL=".$postFILTERONPRINCIPAL."&FILTERPRINCIPALVALUE=".$postFILTERPRINCIPALVALUE."'",
									$ROOT.$PHPFOLDER."functional/administration/adminUserPrincipalDepotListTable.php");
	// the data
	GUICommonUtils::outputTable (array("UID","Depot Id","Depot Name","Principal Id","Principal Name"),
								   $UPDListArr,
								   "",
								   "");
print("</TABLE>");

$dbConn->dbFree();
$dbConn->dbClose();
?>		



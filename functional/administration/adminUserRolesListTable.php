<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterUserRoleList=urldecode($_POST["FILTERLIST"]); $postFilterUserRoleList=explode(',',$postFilterUserRoleList); } else $postFilterUserRoleList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["FILTERONPRINCIPAL"])) $postFILTERONPRINCIPAL=$_POST["FILTERONPRINCIPAL"]; else $postFILTERONPRINCIPAL="";
if (isset($_POST["FILTERPRINCIPALVALUE"])) $postFILTERPRINCIPALVALUE=$_POST["FILTERPRINCIPALVALUE"]; else $postFILTERPRINCIPALVALUE="";
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"]; else $postUSERID="";

if ($postUSERID=="") {
	print("No User Chosen");
	return;
}

// field names for this form
$fldFilterUserRoleListname="UserRoleListFilter"; // the names of the filter fields
$fldFilterUserRoleListUsageArr=array(1=>"Y","Y","Y","Y","Y","Y","Y","Y","Y");
$fldFilterUserRoleListSizeArr=array(1=>"5","5","5","5","5","5","5","5","5");

$administrationDAO=new AdministrationDAO($dbConn);
$mfU=$administrationDAO->getUserItem($postUSERID);
$rolesArr=$administrationDAO->getUserRolesArray($postUSERID,"",$mfU[0]['category']);

// strip off the columns we want. Remember that fetch_array doubles up everything.
$userRolesListArr=array();
class UserRoleTbl {
	public $uid;
	public $user_id;
	public $depot_id;
	public $depot_name;
	public $principal_id;
	public $principal_name;
	public $role_id;
	public $description;
	public $group;
}
// determine the RB values
foreach ($rolesArr as $row) {
	if ($postFILTERONPRINCIPAL=="Y") {
		if (($row['principal_id']==$postFILTERPRINCIPALVALUE) || ($row['principal_id']=="")) {
			$class=new UserRoleTbl;
			$class->uid=$row['uid'];
			$class->user_id=$row['user_id'];
			$class->depot_id=$row['depot_id'];
			$class->depot_name=$row['depot_name'];
			$class->principal_id=$row['principal_id'];
			$class->principal_name=$row['principal_name'];
			$class->role_id=$row['role_id'];
			$class->description=$row['description'];
			$class->group=$row['group'];
			$userRolesListArr[]=$class;
		}
	} else {
		$class=new UserRoleTbl;
		$class->uid=$row['uid'];
		$class->user_id=$row['user_id'];
		$class->depot_id=$row['depot_id'];
		$class->depot_name=$row['depot_name'];
		$class->principal_id=$row['principal_id'];
		$class->principal_name=$row['principal_name'];
		$class->role_id=$row['role_id'];
		$class->description=$row['description'];
		$class->group=$row['group'];
		$userRolesListArr[]=$class;
	  }
}

$userRolesArr=GUICommonUtils::applyFilter($userRolesListArr,$postFilterUserRoleList);

$valuesRB=array(); // determine the RB values
foreach ($userRolesArr as $row) {
	$valuesRB[]=$row->uid;
}

// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterUserRoleListname,
									$fldFilterUserRoleListUsageArr,
									$fldFilterUserRoleListSizeArr,
									$postFilterUserRoleList,
									$postPAGEDEST,
									"+'&USERID=".$postUSERID."&FILTERONPRINCIPAL=".$postFILTERONPRINCIPAL."&FILTERPRINCIPALVALUE=".$postFILTERPRINCIPALVALUE."'",
									$ROOT.$PHPFOLDER."functional/administration/adminUserRolesListTable.php");
	// the data
	GUICommonUtils::outputTable (array("User Role ID","User ID","Depot Id","Depot Name","Principal Id","Principal Name","Role Id","Role Description","Group"),
								   $userRolesArr,
								   "",
								   "");
print("</TABLE>");


$dbConn->dbClose();
?>



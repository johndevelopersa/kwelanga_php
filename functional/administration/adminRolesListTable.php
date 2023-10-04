<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$systemId = $_SESSION['system_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterRoleList=urldecode($_POST["FILTERLIST"]); $postFilterRoleList=explode(',',$postFilterRoleList); }
else $postFilterRoleList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"];
else $postPAGEDEST="";
// these 2 fields only used when adding role to a chosen user and principal
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"];
else $postUSERID="";
if (isset($_POST["PRINCIPALID"])) $postPRINCIPALID=$_POST["PRINCIPALID"];
else $postPRINCIPALID="";
if (isset($_POST["SHOWROLEPROFILES"])) $postSHOWROLEPROFILES=$_POST["SHOWROLEPROFILES"];
else $postSHOWROLEPROFILES="N";

// field names for this form
$fldChosenRoleRB=($_POST["RBNAME"]);
$fldFilterRoleListname="RoleListFilter"; // the names of the filter fields
$fldFilterRoleListUsageArr=array(1=>"N","Y","Y","Y","Y","Y","Y");
$fldFilterRoleListSizeArr=array(1=>"0","5","5","5","5","5","5");

$administrationDAO=new AdministrationDAO($dbConn);
$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
$rolesArr=$administrationDAO->getRolesArray($postUSERID,$postPRINCIPALID, $systemId);


// NB: the header role on the table has JS that autoselects all below it, but this is an onclick event so shouldnt fire by setting it through JS



// strip off the columns we want. Remember that fetch_array doubles up everything.
$rolesListArr=array();
class RoleTbl {
	public $selected;
	public $uid;
	public $description;
	public $long_description;
	public $group;
	public $user_has_role;
	public $restricted_to;
}
// determine the RB values
$parentArr=array(); // can't be part of main class for display purposes!
foreach ($rolesArr as $row) {
	if ((!$adminUser) && ($row["restricted_to"]==FLAG_ROLE_RESTRICTEDTO_ADMIN) && ($row['parent']!="")) continue; // show the header atleast, else hide

	if ($row['parent']=="") $indenter="";
	else $indenter="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

	$class=new RoleTbl;
	$class->selected=false;
	$class->uid=$row['uid'];
	$class->description=$indenter.$row['description'];
	$class->long_description="<div style='overflow:hidden; text-overflow: ellipsis; width:200px;' onmouseover='parent.displayTip(this,5,135,this.innerHTML)' onmouseout='parent.hideTip();' >".$row['long_description']."</div>";
	$class->group=$row['group'];
	$class->user_has_role= ($row['user_has_role']=='Y')?('Y'):('N');
	switch($row['restricted_to']) {
		case FLAG_ROLE_RESTRICTEDTO_ADMIN: {
			$class->restricted_to="Administrator";
			break;
			}
		case FLAG_ROLE_RESTRICTEDTO_GENERAL: {
			$class->restricted_to="General";
			break;
			}
		default: $class->restricted_to=$row['restricted_to'];
	}
	$parentArr[$row['uid']]=$row['parent'];
	$rolesListArr[]=$class;
}
$extraColArr[2]=" nowrap ";
$extraColArr[3]=" nowrap ";


$rolesArr=GUICommonUtils::applyFilter($rolesListArr,$postFilterRoleList);

// set up the JS and values. This must be done after filter.
$valuesRB=array();
$js="function RoleClass () { this.value; this.parent; } ";
$js.="var rolesArr=[];";
foreach ($rolesArr as $row) {
	$valuesRB[]=$row->uid;

	$js.="var roleClass=new RoleClass();";
	$js.="roleClass.value=".$row->uid.";";
	if ($parentArr[$row->uid]=="") $js.="roleClass.parent='';";
	else $js.="roleClass.parent=".$parentArr[$row->uid].";";
	$js.="rolesArr.push(roleClass);";
}


$mfRP=$administrationDAO->getRoleProfiles($systemId);

echo '<div style="display:block;width:850px">';
echo "<div align='left'><H2 style='color:#047;margin-bottom:0px;'>Profile</H2>";
echo "You may choose a profile to apply : <BR><BR><SELECT id='roleProfile' ><OPTION value=''>No Profile Selected</OPTION>";
foreach ($mfRP as $row) {
        echo "<OPTION value='".$row["role_list"]."' >".$row["description"]."</OPTION>";
}
echo "</SELECT><input class='submit' type='submit' value='Apply Profile' onclick=\"applyProfile('roleProfile');\"/></div><BR>";


echo "<div align='left'><H2 style='color:#047;margin-bottom:0px;'>Roles</H2>or select the individual roles to apply : </div><BR>";

// button row - must be own table due to button sizes being wider than output columns
echo('<TABLE id="roleTableID" width="850">');
	// filter row
	GUICommonUtils::getFilterFields($fldFilterRoleListname,
									$fldFilterRoleListUsageArr,
									$fldFilterRoleListSizeArr,
									$postFilterRoleList,
									$postPAGEDEST,
									"+'&RBNAME=".$fldChosenRoleRB."&USERID=".$postUSERID."&PRINCIPALID=".$postPRINCIPALID."'",
									$ROOT.$PHPFOLDER."functional/administration/adminRolesListTable.php");
	// the data
	GUICommonUtils::outputRBTable (array("UId","Description","Long Description","Group","User has Role?","Restricted to"),
								   $rolesArr,
								   $fldChosenRoleRB,
								   $valuesRB,
								   "autoTicks(this);",
								   "tick",
								   $extraColArr,
								   "");
echo("</TABLE></div>");


$dbConn->dbClose();
?>
<SCRIPT type="text/javascript" defer>
function autoTicks(fld) {
	<?php echo $js; ?>
	var roleFlds = document.getElementsByName('<?php echo $fldChosenRoleRB; ?>');
	var start=false; var checked=false;
	for (var i=0; i<rolesArr.length; i++) {
		if (!start) {
			if (rolesArr[i].value==fld.value) {
				if (rolesArr[i].parent!='') return;
				start=true;
				checked=!roleFlds[i].checked;
			}
		} else if (rolesArr[i].parent=="") return;
		if (start) (checked)?roleFlds[i].checked=false:roleFlds[i].checked=true;
	}
}
</SCRIPT>



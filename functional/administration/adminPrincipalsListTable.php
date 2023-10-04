<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
CommonUtils::getSystemConventions();

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];
//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterPrincipalList=urldecode($_POST["FILTERLIST"]); $postFilterPrincipalList=explode(',',$postFilterPrincipalList); }
else $postFilterPrincipalList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"];
else $postPAGEDEST="";
if (isset($_POST["ADMINVIEW"])) $postADMINVIEW=$_POST["ADMINVIEW"];
else $postADMINVIEW="N";
if (isset($_POST["ONCLICK"])) $postONCLICK=$_POST["ONCLICK"];
else $postONCLICK="";
if (isset($_POST["CALLBACK"])) $postCALLBACK=$_POST["CALLBACK"];
else $postCALLBACK="";

$adminUser=($postADMINVIEW=="Y")?true:false;

// field names for this form
$fldChosenPrincipalRB=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["RBNAME"]));
$fldFilterPrincipalListname="PrincipalListFilter"; // the names of the filter fields

	$fldFilterPrincipalListUsageArr=array(1=>"N","Y","Y");
	$fldFilterPrincipalListSizeArr=array(1=>"0","5","5");


$PrincipalsListArr=array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
$principalDAO=new PrincipalDAO($dbConn);
// note we pass the SUPERUSER's ID (aka the person doing the action), not the user who we are adding the principal to
// the SU can only add principals to users for which they have access to themselves
$headers = array("".SNC::principal." UID","".SNC::principal." Name");
$principalArr = $principalDAO->getUserPrincipalArray($userId, "");


class PrincipalTbl {
        public $selected;
        public $uid;
        //public $principal_code;
        public $principal_name;
        //public $physical_add1;
        //public $physical_add2;
        //public $suspended;
}
foreach ($principalArr as $row) {
        $class=new PrincipalTbl;
        $class->selected=false;
        $class->uid=$row['principal_id'];
        //$class->principal_code=$row['principal_code'];
        $class->principal_name=$row['principal_name'];
        //$class->physical_add1="";//$row['physical_add1'];
        //$class->physical_add2="";//$row['physical_add2'];
        $PrincipalsListArr[]=$class;
}


$pArr=GUICommonUtils::applyFilter($PrincipalsListArr,$postFilterPrincipalList);

$valuesRB=array(); // determine the RB values
foreach ($pArr as $row) {
	$valuesRB[]=$row->uid;
}

// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterPrincipalListname,
                                        $fldFilterPrincipalListUsageArr,
                                        $fldFilterPrincipalListSizeArr,
                                        $postFilterPrincipalList,
                                        $postPAGEDEST,
                                        "+'&RBNAME=".$fldChosenPrincipalRB."&ADMINVIEW=".$postADMINVIEW."&ONCLICK=".$postONCLICK."&CALLBACK=".$postCALLBACK."'",
                                        $ROOT.$PHPFOLDER."functional/administration/adminPrincipalsListTable.php");
	// the data
	GUICommonUtils::outputRBTable ($headers,
                                        $pArr,
                                        $fldChosenPrincipalRB,
                                        $valuesRB,
                                        $postONCLICK.$postCALLBACK,
                                        "radio",
                                        "",
                                        "");
print("</TABLE>");


$dbConn->dbClose();
?>



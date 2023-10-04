<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');


// divs
$divAjaxMainContentArea="ajaxMainContentArea";

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterPPList=urldecode($_POST["FILTERLIST"]); $postFilterPPList=explode(',',$postFilterPPList); } else $postFilterPPList='';
if (isset($_POST["PRINCIPALID"])) $postPRINCIPALID=$_POST["PRINCIPALID"]; else $postPRINCIPALID="";
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"]; else $postUSERID="";
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["CALLBACK"])) $postCALLBACK=$_POST["CALLBACK"]; else $postCALLBACK="";

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$dbConn = new dbConnect();  //Create new database object
$dbConn->dbConnection();
$adminDAO = new AdministrationDAO($dbConn);
$fldPref = $adminDAO->getAllFieldPreferences($principalId, $systemId, 'PRODUCTLIST');
$GCListArr=array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
$productDAO=new ProductDAO($dbConn);

// field names for this form
$fldChosenPPRB=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["RBNAME"]));
$fldFilterPPListname="PPListFilter"; // the names of the filter fields



$fldFilterPPListUsageArr=array(1=>"N","Y","Y","Y","Y");
$fldFilterPPListSizeArr=array(1=>"0","5","5","5","5");
$headers=array("UID","Product Code","Alt. Code","Description");

$hideCategory = (GUICommonUtils::showHideField($fldPref, 'CATEGORY', $class, false)=="")?false:true;
if(!$hideCategory){
  $fldFilterPPListUsageArr[] = "Y";
  $fldFilterPPListSizeArr[] = "5";
  $headers[] = "Category";
}

//var_dump($v);


$productArr=$productDAO->getUserPrincipalProductsArray($postPRINCIPALID,$postUSERID);
class PPTbl {
	public $selected;
	public $uid;
	public $productCode;
	public $altCode;
	public $productDescription;
	public $productCategory;
}
foreach ($productArr as $row) {
	$class=new PPTbl;
	$class->selected=false;
	$class->uid=$row['uid'];
	$class->productCode=$row['product_code'];
	$class->altCode=$row['alt_code'];
	$class->productDescription=$row['product_description'];
        if(!$hideCategory){
          $class->productCategory=$row['product_category'];
        } else {
          unset($class->productCategory);
        }
	$GCListArr[]=$class;
}

$pArr=GUICommonUtils::applyFilter($GCListArr,$postFilterPPList);

$valuesRB=array(); // determine the RB values
foreach ($pArr as $row) {
	$valuesRB[]=$row->uid;
}

echo "<BR>";
// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterPPListname,
									$fldFilterPPListUsageArr,
									$fldFilterPPListSizeArr,
									$postFilterPPList,
									$postPAGEDEST,
									"+'&RBNAME=".$fldChosenPPRB."&CALLBACK=".$postCALLBACK."&USERID=".$postUSERID."&PRINCIPALID=".$postPRINCIPALID."'",
									$ROOT.$PHPFOLDER."functional/administration/adminPrincipalProductsListTable.php");
	// the data
	GUICommonUtils::outputRBTable ($headers,
								   $pArr,
								   $fldChosenPPRB,
								   $valuesRB,
								   $postCALLBACK,
								   "radio",
								   "",
								   "");
print("</TABLE>");

$dbConn->dbClose();

?>
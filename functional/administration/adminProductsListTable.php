<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');

if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterPList=urldecode($_POST["FILTERLIST"]); $postFilterPList=explode(',',$postFilterPList); } else $postFilterPList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["ADMINVIEW"])) $postADMINVIEW=$_POST["ADMINVIEW"]; else $postADMINVIEW="N";
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"]; else $postUSERID="N";
if (isset($_POST["PRINCIPALID"])) $postPRINCIPALID=$_POST["PRINCIPALID"]; else $postPRINCIPALID="";
if (isset($_POST["RBTYPE"])) $postRBTYPE=$_POST["RBTYPE"]; else $postRBTYPE="tick";

// field names for this form
$fldChosenPRB=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["RBNAME"]));
$fldFilterPListname="PListFilter"; // the names of the filter fields
$fldFilterPListUsageArr=array(1=>"N","Y","Y","Y","Y");
$fldFilterPListSizeArr=array(1=>"0","5","5","5","5");

$PListArr=array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
$productDAO=new ProductDAO($dbConn);
// sometimes the administrator needs to select products from global list, other times from list recipient user has eg. when adding products by product
if ($postADMINVIEW=="Y") $productArr=$productDAO->getPrincipalProductsArray($postPRINCIPALID); 
else $productArr=$productDAO->getUserPrincipalProductsArray($postPRINCIPALID,$userId);

class PTbl {
	public $selected;
	public $principalProductUId;
	public $productCode;
	public $altCode;
	public $productDescription;
} 
foreach ($productArr as $row) {
	$valuesRB[]=$row['uid'];  
	$class=new PTbl;
	$class->selected=false;
	$class->principalProductUId=$row['uid'];
	$class->productCode=$row['product_code'];
	$class->altCode=$row['alt_code'];
	$class->productDescription=$row['product_description'];
	$PListArr[]=$class;
}

$pArr=GUICommonUtils::applyFilter($PListArr,$postFilterPList);

$valuesRB=array(); // determine the RB values
foreach ($pArr as $row) {
	$valuesRB[]=$row->principalProductUId; 
}

// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterPListname,
									$fldFilterPListUsageArr,
									$fldFilterPListSizeArr,
									$postFilterPList,
									$postPAGEDEST,
									"+'&RBNAME=".$fldChosenPRB."&RBTYPE=".$postRBTYPE."&ADMINVIEW=".$postADMINVIEW."&PRINCIPALID=".$postPRINCIPALID."'",
									$ROOT.$PHPFOLDER."functional/administration/adminProductsListTable.php");
	// the data
	GUICommonUtils::outputRBTable (array("UId","Product Code","Alt. Code","Product Name"),
								   $pArr,
								   $fldChosenPRB,
								   $valuesRB,
								   "",
								   $postRBTYPE,
								   "",
								   "");
print("</TABLE>");

$dbConn->dbFree();
$dbConn->dbClose();
?>		



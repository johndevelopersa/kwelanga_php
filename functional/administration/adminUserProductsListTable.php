<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterUPList=urldecode($_POST["FILTERLIST"]); $postFilterUPList=explode(',',$postFilterUPList); } else $postFilterUPList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"]; else $postUSERID="";
if (isset($_POST["PRINCIPALID"])) $postPRINCIPALID=$_POST["PRINCIPALID"]; else $postPRINCIPALID="";

if ($postUSERID=="") {
	print("No User Chosen");
	return;
}

// field names for this form
$fldFilterUPListname="UPListFilter"; // the names of the filter fields
$fldFilterUPListUsageArr=array(1=>"Y","Y","Y","Y");
$fldFilterUPListSizeArr=array(1=>"5","5","5","5");

include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
$productDAO=new ProductDAO($dbConn);
$UPArr=$productDAO->getUserPrincipalProductsArray($postPRINCIPALID, $postUSERID);

$administrationDAO = new AdministrationDAO($dbConn);
$hasSeeAllRole = $administrationDAO->hasRole($postUSERID, $postPRINCIPALID, ROLE_BYPASS_USER_PRODUCT_RESTRICTION);
if ($hasSeeAllRole) echo "<SPAN style='color:".COLOR_UNOBTRUSIVE_INFO."; font-weight:bold;'>This User Has the \"ByPass Product Restriction\" Role loaded and can therefore access ALL products!</SPAN>";

// strip off the columns we want. Remember that fetch_array doubles up everything.
$UPListArr=array();
class UPTbl {
	public $principal_product_uid;
	public $productCode;
	public $altCode;
	public $product_name;
}
// determine the RB values
foreach ($UPArr as $row) {
	$class=new UPTbl;
	$class->principal_product_uid=$row['uid'];
	$class->productCode=$row['product_code'];
	$class->altCode=$row['alt_code'];
	$class->product_name=$row['product_description'];
	$UPListArr[]=$class;
}

$UPListArr=GUICommonUtils::applyFilter($UPListArr,$postFilterUPList);

$valuesRB=array(); // determine the RB values
foreach ($UPListArr as $row) {
	$valuesRB[]=$row->principal_product_uid; 
}


// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	// filter row
	GUICommonUtils::getFilterFields($fldFilterUPListname,
									$fldFilterUPListUsageArr,
									$fldFilterUPListSizeArr,
									$postFilterUPList,
									$postPAGEDEST,
									"+'&USERID=".$postUSERID."&PRINCIPALID=".$postPRINCIPALID."'",
									$ROOT.$PHPFOLDER."functional/administration/adminUserProductsListTable.php");
	// the data
	GUICommonUtils::outputTable (array("UId","Product Code","Alt. Code","Product Name"),
								   $UPListArr,
								   "",
								   "");
print("</TABLE>");

$dbConn->dbClose();
?>		



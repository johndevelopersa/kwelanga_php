<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');


//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();


// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterDepotList=urldecode($_POST["FILTERLIST"]); $postFilterDepotList=explode(',',$postFilterDepotList); }
else $postFilterDepotList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"]; else $postPAGEDEST="";
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"]; else $postUSERID="";
if (isset($_POST["PRINCIPALID"])) $postPRINCIPALID=$_POST["PRINCIPALID"]; else $postPRINCIPALID="";
if (isset($_POST["RBTYPE"])) $postRBTYPE=$_POST["RBTYPE"]; else $postRBTYPE="tick";
$postADMINVIEW=$_POST["ADMINVIEW"];


// field names for this form
$fldChosenDepotRB=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["RBNAME"]));
$fldFilterDepotListname="DepotListFilter"; // the names of the filter fields
$fldFilterDepotListUsageArr=array(1=>"N","Y","Y","Y");
$fldFilterDepotListSizeArr=array(1=>"0","5","5","5");

$depotsListArr=array(); // strip off the columns we want. Remember that fetch_array doubles up everything.


$depotDAO=new DepotDAO($dbConn);
$headers=array("Depot UID","Depot Code","Depot Name");

if ($postADMINVIEW=="Y") $depotArr=$depotDAO->getAllDepotsArray();
else $depotArr=$depotDAO->getAllDepotsForPrincipalArray($postUSERID,$postPRINCIPALID);

$depotsSelArr = array();

class DepotTbl {
	public $selected;
	public $uid;
	public $code;
	public $depot_name;
}

foreach ($depotArr as $row) {
	$class=new DepotTbl;
	if ($postADMINVIEW=="Y") $class->selected=false; else $class->selected=true;
	$class->uid=$row['uid'];
	$class->code=$row['code'];
	$class->depot_name=$row['depot_name'];

    if(isset($_POST['PAGESELECTED']) && in_array($row['uid'], explode(',', $_POST['PAGESELECTED']))){
      $depotsSelArr[$row['uid']] = $class;  //protect from filter.
    } else {
       $depotsListArr[$row['uid']] = $class;
    }
}

$pArr = GUICommonUtils::applyFilter($depotsListArr,$postFilterDepotList);
$pArr = $depotsSelArr + $pArr;


$valuesRB=array(); // determine the RB values
foreach ($pArr as $row) {
	$valuesRB[]=$row->uid;
}

// button row - must be own table due to button sizes being wider than output columns
print("<TABLE>");
	if ($postADMINVIEW=="Y") {
		// filter row
		GUICommonUtils::getFilterFields($fldFilterDepotListname,
										$fldFilterDepotListUsageArr,
										$fldFilterDepotListSizeArr,
										$postFilterDepotList,
										$postPAGEDEST,
										"+'&RBNAME=".$fldChosenDepotRB."&RBTYPE=".$postRBTYPE."&ADMINVIEW=Y&USERID=".$postUSERID."&PRINCIPALID=".$postPRINCIPALID."&PAGESELECTED='+convertElementToArray(document.getElementsByName('" . $fldChosenDepotRB . "'))+''",
										$ROOT.$PHPFOLDER."functional/administration/adminDepotsListTable.php");
		// the data
		GUICommonUtils::outputRBTable ($headers,
									   $pArr,
									   $fldChosenDepotRB,
									   $valuesRB,
									   "",
									   $postRBTYPE,
									   "",
									   "");
	} else {
		// filter row
		GUICommonUtils::getFilterFields($fldFilterDepotListname,
										$fldFilterDepotListUsageArr,
										$fldFilterDepotListSizeArr,
										$postFilterDepotList,
										$postPAGEDEST,
										"+'&RBNAME=".$fldChosenDepotRB."&RBTYPE=".$postRBTYPE."&ADMINVIEW=N&USERID=".$postUSERID."&PRINCIPALID=".$postPRINCIPALID."&PAGESELECTED='+convertElementToArray(document.getElementsByName('" . $fldChosenDepotRB . "'))+''",
										$ROOT.$PHPFOLDER."functional/administration/adminDepotsListTable.php");
		// the data
		GUICommonUtils::outputRBTable ($headers,
									   $pArr,
									   $fldChosenDepotRB,
									   $valuesRB,
									   "",
									   $postRBTYPE,
									   "",
									   "");
	}

print("</TABLE>");

$dbConn->dbFree();
$dbConn->dbClose();
?>



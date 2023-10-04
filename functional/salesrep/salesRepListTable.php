<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();


// passed POST Fields
if (isset($_POST["FILTERLIST"])) { $postFilterPList = urldecode($_POST["FILTERLIST"]); $postFilterPList = explode(',',$postFilterPList); } else $postFilterPList = '';
$postPAGEDEST = (isset($_POST["PAGEDEST"])) ? $_POST["PAGEDEST"] : "";
$postUSERID = (isset($_POST["USERID"])) ? $_POST["USERID"] : "N";
$postPRINCIPALID = (isset($_POST["PRINCIPALID"])) ? $_POST["PRINCIPALID"] : "";
$postRBTYPE = (isset($_POST["RBTYPE"])) ? $_POST["RBTYPE"] : "tick";
$postCALLBACK = (isset($_POST["CALLBACK"]))    ? ($_POST["CALLBACK"]) : ('');
$postPAGETYPE = (isset($_POST["PAGETYPE"])) ? $_POST["PAGETYPE"] : FLAG_STATUS_ACTIVE;

// field names for this form
$fldChosenPRB = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["RBNAME"]));
$fldFilterPListname = "PListFilter"; // the names of the filter fields
$fldFilterPListUsageArr = array(1=>"N","Y","Y","Y","Y","Y","Y");
$fldFilterPListSizeArr = array(1=>"0","5","5","5","5","5","5");
$rListArr = array();
$storeDAO = new StoreDAO($dbConn);

// sometimes the administrator needs to select products from global list, other times from list recipient user has eg. when adding products by product
$repArr = $storeDAO->getPrincipalSalesRepAll($postPRINCIPALID, $postPAGETYPE);


class RTbl {
  public $selected;
  public $repUId;
  public $repCode;
  public $firstName;
  public $surname;
  public $identityNumber;
  public $emailAddr;
}
foreach ($repArr as $row) {
  $class = new RTbl;
  $class->selected=false;
  $class->repUId=$row['uid'];
  $class->repCode=$row['rep_code'];
  $class->firstName=$row['first_name'];
  $class->surname=$row['surname'];
  $class->identityNumber=$row['identity_number'];
  $class->emailAddr = $row['email_addr'];
  $rListArr[] = $class;
}

$rArr=GUICommonUtils::applyFilter($rListArr,$postFilterPList);

$valuesRB=array(); // determine the RB values
foreach ($rArr as $row) {
  $valuesRB[]=$row->repUId;
}

// button row - must be own table due to button sizes being wider than output columns
echo "<TABLE>";

  // filter row
  GUICommonUtils::getFilterFields($fldFilterPListname,
                                  $fldFilterPListUsageArr,
                                  $fldFilterPListSizeArr,
                                  $postFilterPList,
                                  $postPAGEDEST,
                                  "+'&RBNAME=".$fldChosenPRB."&RBTYPE=".$postRBTYPE."&CALLBACK=".$postCALLBACK."&PRINCIPALID=".$postPRINCIPALID."'",
                                  $_SERVER['PHP_SELF']);
  // the data
  GUICommonUtils::outputRBTable (array("UId","Rep Code","First Name","Surname","Identity Number","Email Address"),
                                  $rArr,
                                  $fldChosenPRB,
                                  $valuesRB,
                                  $postCALLBACK,
                                  $postRBTYPE,
                                  "",
                                  "");

echo "</TABLE>";


?>
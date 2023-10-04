<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');


if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];


//DB Connection
$dbConn = new dbConnect();
$dbConn->dbConnection();


// passed POST Fields
$postFilterUserList = (isset($_POST["FILTERLIST"])) ? (explode(',',urldecode($_POST["FILTERLIST"]))) : ('');
$postPAGEDEST       = (isset($_POST["PAGEDEST"])) ? ($_POST["PAGEDEST"]) : ('');
$postCALLBACK       = (isset($_POST["CALLBACK"])) ? ($_POST["CALLBACK"]) : ('');
$postRBTYPE         = (isset($_POST["RBTYPE"])) ? ($_POST["RBTYPE"]) : ('radio');
$postCOMPRESS       = (isset($_POST["COMPRESS"])) ? ($_POST["COMPRESS"]) : ('Y');
$postSHOWFILTER     = (isset($_POST["SHOWFILTER"])) ? ($_POST["SHOWFILTER"]) : ('Y');
$postPAGETYPE       = (isset($_POST["PAGETYPE"])) ? ($_POST["PAGETYPE"]) : ('A');  //Active | Deleted Users
$postPAGETYPE       = ($postPAGETYPE == 'A') ? (0) : (1);  //Change A, D to 0, 1


// field names for this form
$fldChosenUserRB = mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST["RBNAME"]));
$fldFilterUserListname = "UserListFilter"; // the names of the filter fields
$fldFilterUserListUsageArr = array(1=>"N","Y","Y","Y","Y","Y");
$fldFilterUserListSizeArr = array(1=>"0","5","5","5","5","5");


$administrationDAO = new AdministrationDAO($dbConn);
$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
if ($adminUser) {
  $usersArr = $administrationDAO->getUsersArray();
} else {
  $usersArr = $administrationDAO->getUsersByPrincipalDepotArray($userId);
}



//Remove Active / Deleted.
foreach($usersArr as $k => $row){
  if(isset($row['deleted']) && $row['deleted'] != $postPAGETYPE){
    unset($usersArr[$k]);
  }
}

// strip off the columns we want. Remember that fetch_array doubles up everything.
$usersListArr = array();
$usersSelArr = array();

class UsrTbl {
  public $selected;
  public $uid;
  public $username;
  public $full_name;
  public $category;
  public $organisation;
}


foreach ($usersArr as $row) {
  $class = new UsrTbl();
  $class->selected = false;
  $class->uid = $row['uid'];
  $class->username = $row['username'];
  $class->full_name = $row['full_name'];
  $class->category = $row['category_name'];
  $class->organisation = $row['organisation_name'];

  if(isset($_POST['PAGESELECTED']) && in_array($row['uid'], explode(',', $_POST['PAGESELECTED']))){
    $usersSelArr[$row['uid']] = $class;  //protect from filter.
  } else {
     $usersListArr[$row['uid']] = $class;
  }

}


//Apply Filter
$usersArr = GUICommonUtils::applyFilter($usersListArr,$postFilterUserList);

$usersArr = $usersSelArr + $usersArr;  //apend selected to filtered arreay.

// determine the RB values
$valuesRB=array();
foreach ($usersArr as $row) {
	$valuesRB[]=$row->uid;
}


// --------------------------------------------------------------------------------------------------------------
/*
 * START OF SCREEN
 */
// --------------------------------------------------------------------------------------------------------------

// button row - must be own table due to button sizes being wider than output columns
echo '<TABLE>';
	// filter row
	if ($postSHOWFILTER=="Y") {
		GUICommonUtils::getFilterFields($fldFilterUserListname,
                                                $fldFilterUserListUsageArr,
                                                $fldFilterUserListSizeArr,
                                                $postFilterUserList,
                                                $postPAGEDEST,
                                                "+'&RBNAME=".$fldChosenUserRB."&RBTYPE={$postRBTYPE}&CALLBACK=".$postCALLBACK."&PAGESELECTED='+convertElementToArray(document.getElementsByName('" . $fldChosenUserRB . "'))+''",
                                                $ROOT.$PHPFOLDER."functional/administration/adminUsersListTable.php");
	}
	// the data
	GUICommonUtils::outputRBTable (array("User UID","User Name","Full Name","Category","Organisation"),
                                        $usersArr,
                                        $fldChosenUserRB,
                                        $valuesRB,
                                        $postCALLBACK,
                                        $postRBTYPE,
                                        "",
                                        "");
echo '</TABLE>';

// --------------------------------------------------------------------------------------------------------------



?>
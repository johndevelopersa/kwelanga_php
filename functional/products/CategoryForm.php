<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'libs/common.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostProductDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingProductTO.php');
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');


$dbConn = new dbConnect();  //Create new database object.
$dbConn->dbConnection();  //Select live db.


if (isset($_POST['DMLTYPE'])){
  $postDMLTYPE = $_POST['DMLTYPE'];
} elseif (isset($_GET['DMLTYPE'])){
  $postDMLTYPE = $_GET['DMLTYPE'];
}

if (!isset($_SESSION)) session_start();

$principalId = $_SESSION['principal_id'];
$userId = $_SESSION["user_id"];

//Debug Param PrincipalID
//echo $aPrincipalId;

$postLOADPROCATID = (isset($_POST['PROCATID']) && is_numeric($_POST['PROCATID'])) ? $_POST['PROCATID'] : false;

if ($postLOADPROCATID === false) {
  
  //CREATE
  $postPROCATNAME = (isset($_POST['PROCATNAME'])) ? $_POST['PROCATNAME'] : '';
  $postPROCATSTATUS = (isset($_POST['PROCATSTATUS'])) ? $_POST['PROCATSTATUS'] : FLAG_STATUS_ACTIVE;
  $postPROCATID = '';  //only used for UPDATE, needs to be int
  
} else {
  
  //MODIFY | VIEW  
  
  //Get Principal Data.     
  $ProductDAO = new ProductDAO($dbConn);
  $GetCategory = $ProductDAO->getProductCategoryItem($postLOADPROCATID);
  
  if (! count($GetCategory) > 0) {
    echo 'Product Category not found.';
    return;
  } else {
    
    //Debug Principal Data
    //var_dump($GetCategory);     
    
    foreach($GetCategory as $category){
      $postPROCATNAME = $category['description'];
      $postPROCATSTATUS = $category['status'];
      $postPROCATID = $category['uid'];      
      break;
    }

  }
}

//CHECK ROLES
$adminDAO = new AdministrationDAO($dbConn);
switch ($postDMLTYPE) {
  case "INSERT" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_ADD_PRODUCT);
      break;
    }
  case "UPDATE" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_MODIFY_PRODUCT);
      break;
    }
  case "VIEW" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_VIEW_PRODUCT);
      break;
    }
  default :
    $hasRole = false;
}
if (!$hasRole) {
  echo 'You do not have permissions to ' , $postDMLTYPE , ' a Product Category.';
  return;
}

#--------------------------------------------------------------------------------------------------------------------------

//FORM OUTPUT

echo '<BR>';
echo '<INPUT type="hidden" id="PROCATID" value="' . $postPROCATID . '" />';
echo '<TABLE>';
echo '<thead><tr>';
echo '<th colspan="2">', mb_convert_case($postDMLTYPE, MB_CASE_TITLE), ' product category</th>';
echo '</tr></thead>';
echo '<tbody>';
echo '<tr class="even">';
echo '<td bgcolor="#87CEFA" width="150">Category Name: '; 
  GUICommonUtils::requiredField(); 
echo '</td>';
echo '<td><INPUT type="text" size="50" maxlength="50" id="PROCATNAME" value="' . $postPROCATNAME . '" /></td>';
echo '</tr>';
echo '<TR class="odd"><TD>Status: '; 
  GUICommonUtils::requiredField(); 
echo '</TD><TD>'; 
  BasicInputElement::getGeneralHorizontalRB('PROCATSTATUS','Active,Deleted',FLAG_STATUS_ACTIVE.','.FLAG_STATUS_DELETED,$postPROCATSTATUS,'N',(($postDMLTYPE=='VIEW')?('Y'):('N')),null,null,null); 
echo '</TD></TR>';
echo '</tbody>';
echo '</TABLE><br />';

if (($postDMLTYPE == 'INSERT') || ($postDMLTYPE == 'UPDATE')) {
  echo '<INPUT type="button" class="submit" onclick="submitContentForm(\'' . $postDMLTYPE . '\');" value="Submit Category" />';
}

#--------------------------------------------------------------------------------------------------------------------------


$dbConn->dbClose();

?>
<script type='text/javascript' defer>

var alreadySubmitted=false;

function submitContentForm(p_type) {
	if (alreadySubmitted) {
		return;
	}
	alreadySubmitted=true;

	var params='DMLTYPE='+p_type;
	params+='&UID='+document.getElementById("PROCATID").value;
	params+='&PROCATNAME='+document.getElementById("PROCATNAME").value;
	params+='&PROCATSTATUS='+convertElementToArray(document.getElementsByName("PROCATSTATUS"));
	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php  echo $ROOT . $PHPFOLDER ?>functional/products/CategorySubmit.php',
						  'alreadySubmitted=false; successCallback();  if (msgClass.type=="S") successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
						  'Please wait while request is processed...');
}

function successCallback(p_type) {
	toggleSteps(1,"<?php echo $ROOT . $PHPFOLDER ?>");
	if (p_type=="INSERT") {
		document.getElementById("PROCATID").value='';
		document.getElementById("PROCATNAME").value='';
	}
}

function errorCallback(p_type) {
}
</script>

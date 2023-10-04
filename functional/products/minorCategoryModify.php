<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'libs/common.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');


if (!isset($_SESSION)) session_start() ;
$principalId = $_SESSION['principal_id'] ;
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$userId = $_SESSION["user_id"];
$systemId = $_SESSION["system_id"];


$dbConn = new dbConnect();  //dbConn obj
$dbConn->dbConnection();


$productDAO = new ProductDAO($dbConn);
$mfPMGL=$productDAO->getProductMinorCategoryLables($principalAliasId, $systemId);



echo '<BR><BR>';

if(count($mfPMGL)==0){

  echo '<i>principal / system not configured for minor categories!</i>';
  return;
}

echo '<table width="500">';
  echo '<tr><td height="30" width="100">Select type:</td><td>';

    $default = 0;
    $catValueArr = array();
    $catLabelArr = array();
    foreach($mfPMGL as $cat){
      $catValueArr[] = $cat['uid'];
      $catLabelArr[] = $cat['lable'];
      $default = $mfPMGL[0]['uid'];
    }
    BasicInputElement::getCSS3RadioHorizontal('SELTYPEUID', join(',', $catLabelArr), join(',', $catValueArr), $default, false, "showCategory();");

  echo '</td></tr>';
  echo '<tr><td>Status:</td><td>';
    BasicInputElement::getCSS3RadioHorizontal('SELSTATUS', 'Active,Deleted', FLAG_STATUS_ACTIVE.','.FLAG_STATUS_DELETED, FLAG_STATUS_ACTIVE, false, "showCategory();");

  echo '</th></tr>';


echo '</table>';

echo '<BR><BR>';
echo '<div style="border:1px dashed #047;padding:20px 0px;display:block;width:540px;" id="categoryList">loading...</div>';


?>
<script type='text/javascript' >

  function showCategory(){
    var level = $('input:radio[name=SELTYPEUID]:checked').val();
    var status = $('input:radio[name=SELSTATUS]:checked').val();
    if(level == undefined){
      alert('ERROR - Invalid Category Selected');
    } else {
      getMinorCategoryForm("TYPEUID=" + level + "&STATUS=" + status);
    }
  }

  function getMinorCategoryForm(param){
    $('#categoryList').html("");
    AjaxRefresh(param,
                "<?php echo $ROOT.$PHPFOLDER; ?>functional/products/minorCategoryForm.php",
		"categoryList",
		"loading...",
		"");
  }

  getMinorCategoryForm("TYPEUID=<?php echo $default?>");
</script>

</body>
</html>
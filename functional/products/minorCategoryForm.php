<?php



include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'libs/common.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');


if (!isset($_SESSION)) session_start() ;
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION["user_id"];
$systemId = $_SESSION["system_id"];


$dbConn = new dbConnect();
$dbConn->dbConnection();
$productDAO = new ProductDAO($dbConn);



$postTYPEUID = (isset($_POST['TYPEUID']))?$_POST['TYPEUID']:false;
$postSTATUS = (isset($_POST['STATUS']))?$_POST['STATUS']:FLAG_STATUS_ACTIVE;

if($postTYPEUID == false){
  die('ERROR: Invalid Category Level!');
}

$mfPMG = $productDAO->getAllProductMinorCategory($principalId, $systemId, $postSTATUS);

echo($postSTATUS == FLAG_STATUS_DELETED)?('<div style="display:none">'):('<div>');
echo '<FORM id="group_form">';
echo '<INPUT type="HIDDEN" value="'.$postTYPEUID.'" name="TYPEUID">';
echo '<input type="HIDDEN" name="DMLTYPE" value="INSERT" />';
echo '<input type="HIDDEN" name="PCUID" value="" />';
echo '<input type="HIDDEN" name="STATUS" value="' . $postSTATUS . '" />';
echo '<span id="editMsg"></span>';
echo "<table width='500'>
            <tr>
              <td width='100'>Description:</td>
              <td align='left'>
                  <input type='text' name='PCVALUE' size='50' value='' />
              </td>
              <td><input type='button' class='submit' value='Submit' onClick='submitContent();'></td>
            </tr>
      </table></FORM>
      <br>";
echo '</div>';

if(!isset($mfPMG[$postTYPEUID])){

  echo '<i>no categories defined</i>';
} else {

  //var_dump($mfPMG[$postFIELDUID]);
  $class = 'even';
  $categoryName = (isset($mfPMG[$postTYPEUID][0]["lable"]))?($mfPMG[$postTYPEUID][0]["lable"]):('error');

  echo '<div align="left" style="width:500px;line-height:30px;"><strong>'.$categoryName.'</strong></div>';
  echo '<table width="500"><tr><th width="20">UId</th><th>Description </th><th width="80">Actions</th></tr>';
  foreach($mfPMG[$postTYPEUID] as $item){
      echo '<tr class="' . GUICommonUtils::styleEO($class). '"><td>' . $item['uid'] . '</td>
        <td style="color:#047;"><strong>' . $item['value'] . '</strong></td>';

      if($postSTATUS == FLAG_STATUS_ACTIVE){
        echo '<th><A href="#" onClick="editItem('.$item['uid'].',\''.$item['value'].'\')">[edit]</a> &nbsp;&nbsp;&nbsp;<a href="#" onClick="deleteItem('.$item['uid'].')">[delete]</a><BR>';
      } else {
        echo '<th><a href="#" onClick="undeleteItem('.$item['uid'].')">[activate]</a><BR>';
      }
  }
  echo '</table>';

}
?>

<script type='text/javascript' >


var alreadySubmitted=false;

function successCallback() {
  fld = $('input[name=TYPEUID]').val();
  selstatus = $('input[name=SELSTATUS]:checked').val();
  getMinorCategoryForm("TYPEUID="+fld+"&STATUS="+selstatus);
}

function submitContent() {

  //check that we are subbmitting the correct form
  var modType = $('input:radio[name=SELTYPEUID]:checked').val();
  var frmType = $('input[name=TYPEUID]').val();
  if(modType != frmType){
    alert('Error: Selected Type and form type mismatch! ('+modType+':'+frmType+')');
  }

  if (alreadySubmitted) {
    alert('You have already clicked on submit...');
    return;
  }
  alreadySubmitted=true;

  var params = $("#group_form").serialize();

  AjaxRefreshWithResult(params,
                      '<?php echo $ROOT.$PHPFOLDER ?>functional/products/minorCategorySubmit.php',
                      'alreadySubmitted=false; if (msgClass.type=="S") successCallback(msgClass);',
                      'Please wait while request is processed...');
}


function deleteItem(id){
  if(confirm("Are you sure you want to delete this item?")){
    $('input[name=PCUID]').val(id);
    $('input[name=DMLTYPE]').val('DELETE');
    $('input[name=STATUS]').val('<?php echo FLAG_STATUS_DELETED ?>');
    submitContent();
  }
}

function undeleteItem(id){
  if(confirm("Are you sure you want to activate this item?")){
    $('input[name=PCUID]').val(id);
    $('input[name=DMLTYPE]').val('DELETE');
    $('input[name=STATUS]').val('<?php echo FLAG_STATUS_ACTIVE ?>');
    submitContent();
  }
}

function editItem(id, value){

  $('input[name=PCUID]').val(id);
  $('input[name=DMLTYPE]').val('UPDATE');
  $('input[name=PCVALUE]').val(value);
  $('#editMsg').html('<div style="color:red;"> - Edit Mode - </div>');
}

</script>
</body>
</html>
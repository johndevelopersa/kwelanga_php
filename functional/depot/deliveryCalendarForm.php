<?php


include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'libs/common.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."elements/basicInputElement.php");
include_once($ROOT.$PHPFOLDER."DAO/DepotDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");


if (!isset($_SESSION)) session_start() ;
$principalId = $_SESSION['principal_id'] ;
$userId = $_SESSION["user_id"];


$postDAY = 0;
$postMONTH = 0;
$postYEAR = 0;
$postDEPOTID = false;
CommonUtils::setPostVars();


$date = mktime(0, 0, 0, $postMONTH, $postDAY, $postYEAR);
//form values
$isNonDD = false;
$fComment = '';
$fUser = $_SESSION['full_name'];
$fValue = 'DD';
$DDUId = '';
$DMLType = 'INSERT';


if(empty($postMONTH)||empty($postDAY)||empty($postYEAR)){
  echo 'Invalid Day/Month/Year supplied!';
  return;
}
if(checkdate($postMONTH, $postDAY, $postYEAR)!==true){
  echo 'Invalid Date supplied!';
  return;
}
if(empty($postDEPOTID)){
  echo 'Invalid DepotID supplied!';
  return;
}


$dbConn = new dbConnect();
$dbConn->dbConnection();
$depotDAO = new DepotDAO($dbConn);
$depotArr = $depotDAO->getDepotItem($postDEPOTID);
$depotArr = $depotArr[$postDEPOTID];


$calArr = $depotDAO->getDepotDeliveryCalendarByTimestamp($postDEPOTID, strtotime(date('Y/m/d', $date)));
if(count($calArr)>0){
  $isNonDD = true;
  $fValue = 'ND';
  $fComment = $calArr[0]['comment'];
  $fUser = $calArr[0]['user_name'];
  $DDUId = $calArr[0]['uid'];
  $DMLType = 'UPDATE';
}

echo '<div align="center">';
  echo '<h2 style="margin:0px;padding:0px;">' . date('l jS, F Y', $date) .' </h2>';

  echo '<form>';
  echo  '<input type="hidden" value="'.$DMLType.'" id="DMLTYPE">';
  echo  '<input type="hidden" value="'.$DDUId.'" id="DDUID">';
  echo  '<input type="hidden" value="'.$postDAY.'" id="DDDAY">';
  echo  '<input type="hidden" value="'.$postMONTH.'" id="DDMONTH">';
  echo  '<input type="hidden" value="'.$postYEAR.'" id="DDYEAR">';
  echo  '<input type="hidden" value="'.$postDEPOTID.'" id="DDDEPOTID">';
  echo '<div style="display:block;width:400px;border-top:1px solid lightSkyBlue;border-bottom:1px solid lightSkyBlue;margin:20px 0px;">';

  echo '<table class="tableReset" width="100%" style="line-height:25px;">
          <tr><td width="70">Depot:</td>
            <td><Strong>' . $depotArr['depot_name'] .'</strong></td></tr>
          <tr>';

  echo '<td >Day is:</td>';
    echo '<td>';
    basicInputElement::getCSS3RadioHorizontal('DDFLAG','DELIVERY DAY,NON DELIVERY DAY','DD,ND',$fValue,$disabled = false,$onChangeJS="displayComment()", $cssStyleSize = 0);
  echo '</td>';
  echo '</tr><tr class="commentSection">';
  $display = (($isNonDD)?'':'style="display:none;"');
  echo '<td ' . $display . '>Comment: ',GUICommonUtils::requiredField(),'</td>';
    echo '<td ' . $display . '>';
  echo  '<input type="text" maxlength="100" size="30" value="'.$fComment.'" id="DDCOMMENT">';
   echo '</td>';
 echo '</tr><tr class="commentSection">';
  echo '<td ' . $display . '>Edited by: </td>';
    echo '<td ' . $display . '>';
  echo  '<span style="color:#333">'.$fUser.'</span>';
   echo '</td></tr></table></div>';
 echo   '<input type="button" onClick="submitForm()" value="Submit" class="submit"> <input type="button" onClick="closeForm()" value="Cancel" class="submit">';
echo '</form>';
 echo '</div>';

?>
<script type="text/javascript">
  function displayComment(){
    $('.commentSection').children('td').css({'display':'none'});
    if (convertElementToArray(document.getElementsByName('DDFLAG'))=='ND'){
      $('.commentSection').children('td').css({'display':'table-cell'});
    }
  }
</script>

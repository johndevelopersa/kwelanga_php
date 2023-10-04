<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');


if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$systemId = $_SESSION['system_id'];
$systemName = $_SESSION['system_name'];

if(!isset($dbConn)){
  //Create new database object
  $dbConn = new dbConnect();
  $dbConn->dbConnection();
}

?>

<link href="<?php echo $DHTMLROOT.$PHPFOLDER.'css/css.php?SYSID='.$systemId.'&SYSNAME='.$systemName ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo $DHTMLROOT.$PHPFOLDER ?>css/mobile.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
<script type="text/javascript">

  $(document).ready(function(){
  });

  function userInfo(){

    <?php

      $pName = (isset($_SESSION['principal_name'])) ? ($_SESSION['principal_name']) : ('');
      $adminDAO = new AdministrationDAO($dbConn);

      $adminUser = (isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
      $hasRoleSU = $adminDAO->hasRoleSuperUser($userId, $principalId);

      $priv = 'General Priviledges';
      if ($adminUser && $hasRoleSU){
        $priv = 'Administrator & SuperUser Priviledges';
      } else if ($adminUser) {
        $priv = 'Administrator Priviledges';
      } else if ($hasRoleSU) {
        $priv = 'SuperUser Priviledges';
      }

      $userInfo = '<div style="display:block;">Category : '.GUICommonUtils::translateCategoryUser($_SESSION['category']).'<br />'.
        'Priviledges : <I>'.$priv.'</I><br />'.
        'E-mail : '.     $_SESSION['user_email']  . '<br>'.
        'Username : '.     $_SESSION['username']  . ' (UId: '.     $_SESSION['user_id']  . ')<br>' .
        ((CommonUtils::isStaffUser())?('Staff User* : YES'):(''));

      $userInfo .= '</div>';

    ?>
      parent.popBox('<div style="color:#000;line-height:20px;" align="center"><div align="left"><?php echo $userInfo ?></div></div>','info');

  }

  <?php

    $mfP = BasicSelectElement::getLogonUserPrincipalDD("principal_list",$principalId,"N","N",null,null,null,$dbConn,$userId);
    $content = "<form name='principal_list' method='post' action='{$ROOT}{$PHPFOLDER}functional/principals/principal_select.php'>";
    $content.='<BR><h3>Change '.SNC::principal.' to:</h3>'.$mfP[1].'<BR><BR>
                       <INPUT type="submit" class="submit" value="submit">
                       <BR><BR><BR>';
    $content.="</form>";

    echo '
      function showPrincipalPopup() {
              parent.popBox(\'<div style="color:#000" align="center">' . str_replace(array("\r\n","'","\n"), array("","\\'",""), $content) . '</div>\',\'general\');
              parent.document.getElementById(\'principal_list\').focus();
      }';

echo '</script>';


echo '<div id="header_layer">';
echo '<div id="headerMain">';

  echo '<div style="white-space: nowrap;position:absolute;top:38px;right:5px;display:block;" id="userInfo">
          <a href="javascript:userInfo()"><div class="shad">'.$_SESSION['full_name'].'&nbsp;&nbsp;&nbsp;</div></a>
        </div>';

  echo '<div id="uiprinblock">';


      // change depot
    if(CommonUtils::isDepotUser()){

      $depotDAO = new DepotDAO($dbConn);
      $depotArr = $depotDAO->getAllDepotsForUserWHS($userId, $systemId);

      echo '<a href="javascript:;" onClick="showDepotPopup();" class="header_principal" title="Change Depot" style="position:absolute;top:5px;right:5px;" >
              <div class="shad">'.$_SESSION['depot_name'].'</div>
            </a>';
      $content = '<h3>Change Depot to:</h3><div style="margin:25px 0px 30px 0px;">';
      if(count($depotArr)>0){
        foreach($depotArr as $row) {
          $content .= '<form name="depotForm" method="post" action="'.$ROOT.$PHPFOLDER.'functional/principals/principal_select.php" style="margin:8px 0px;padding:0px;">';
          $content .= '<input type="submit" name="depot_list['.$row['uid'].']" value="'.$row['name']."|".$row['skip_inpick_stage'].'" class="submit" style="width:200px;line-height:22px;" />';
          $content .= '</form>';
        }
      }
      $content .= '</div>';

      echo '<script type="text/javascript">
          function showDepotPopup() {
            parent.popBox(\'<div style="color:#000" align="center">' . str_replace(array("\r\n","'","\n"), array("","\\'",""), $content) . '</div>\',\'general\');
          }
      </script>';

    }
    echo '<a href="javascript:;" onClick="showPrincipalPopup();" class="header_principal" title="Change '.SNC::principal.'" style="position:absolute;top:5px;right:5px;" >
            <div class="shad">'.$pName.'</div>
          </a>';



echo '</div>';

echo '</div>';

include($ROOT. $PHPFOLDER . 'functional/main/menuMobile.php');

echo '</div>';
echo '</div>';
?>
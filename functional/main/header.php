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

<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/app.js"></script>
<script type="text/javascript">

  var expandedLvl1;
  var tlvl1;
  var tlvl2;
  var delayOut = 200;

  function resetMenuH(){
    $("p.menu_head").css({backgroundColor:"red"});
    $("p.menu_head").css({backgroundPosition:"right 6px"});
    $("p.menu_head").attr('style','');
  }
  function turnOnMenuH(obj){
    $(obj).attr('style','-webkit-box-shadow:0px 0px 10px rgba(168,210,243,.8);-moz-box-shadow:0px 0px 10px rgba(168,210,243,.8);box-shadow:0px 0px 10px rgba(168,210,243,.8);');
    $(obj).css({backgroundPosition:"right -26px"});
    $(obj).css({backgroundColor:"Gainsboro"});
  }

  $(document).ready(function(){

    $('p.menu_head').hover(	//hover : hide on mouseout lv0
      function (e) {
        clearTimeout(tlvl1);
        resetMenuH();
        turnOnMenuH(this);
        $(this).parents().siblings().children("div.menu_body").stop().css('display', 'none');
        $(this).siblings('div.menu_body').stop(true,false).css('display', 'block');
        expandedLvl1 = $(this).siblings('div.menu_body').get(0);
      },
      function (e) {
        // set it with a timer so that the collapse can be stopped by lvl2 submenu in time.
        clearTimeout(tlvl1);
        tlvl1=setTimeout("expandedLvl1.style.display='none';resetMenuH();",delayOut);
      }
    );

    $('div.menu_body').hover(	//hover : hide on mouseout lv1
      function (e) {
        turnOnMenuH($(this).parent().children("p"));
        clearTimeout(tlvl1);
      },
      function (e) {
        // set it with a timer so that the collapse can be stopped by lvl2 submenu in time.
        clearTimeout(tlvl1);
        tlvl1=setTimeout("expandedLvl1.style.display='none';resetMenuH();",delayOut);
      }
    );

    // level 2 animation
    $('a.menu_head2').hover(
      function (e) {
        popup = $('#lvl2popup').get(0);
        if ($(this).siblings('div.menu_body2').length>1) popup.innerHTML = $(this).next().html();
        else popup.innerHTML = $(this).siblings('div.menu_body2').html();
        var position = $(this).offset();
        position.top -= 1;
        position.left += $(this).width() + 35;
        $('#lvl2popup').css(position);
        $('#lvl2popup').stop(true,false).css('display', 'block');
      },
      function (e) { }
    );

    $('a.aaa').hover(
      function (e) {
        clearTimeout(tlvl1);
      },
      function (e) {
      }
    );

    $('#lvl2popup').hover(
      function (e) {
        clearTimeout(tlvl1);
        clearTimeout(tlvl2);
      },
      function (e) {
        clearTimeout(tlvl1);
        tlvl1=setTimeout("expandedLvl1.style.display='none';",delayOut);
        clearTimeout(tlvl2);
        tlvl2=setTimeout("$('#lvl2popup').css({'display':'none'});",delayOut);
      }
    );

    $('a.menu_head2').hover(
      function (e) {
        clearTimeout(tlvl1);
        clearTimeout(tlvl2);
      },
      function (e) {
        clearTimeout(tlvl2);
        tlvl2=setTimeout("$('#lvl2popup').css({'display':'none'});",delayOut);
        clearTimeout(tlvl1);
        tlvl1=setTimeout("expandedLvl1.style.display='none';",delayOut);
      }
    );

  });


  function showPermissionsPopup() {
          var contentDivName='permissionsSearchDiv';
          var searchFldId='permissionsSearch';
          var typeFldName='SEARCHTYPE';
          var content='<div style="color:#000;" align="center"><h3>Ever wondered what you are not seeing ?</h3><BR>It could (for example) be that you are running a report to total figures (eg. sales) but are not getting all the figures across all stores because you do not have permissions to view some stores. You can use this popup to see if a store or product exists but for which you do not have permissions to view.</p>Please type search keywords, separated with a comma(s) for each word.<BR>Note:phrases are less accurate as subtle variations exist, and avoid words like AND.<BR><BR><INPUT type=text id='+searchFldId+' value=\'\' />'
                          +'<input type=radio name='+typeFldName+' value=\'summary\' CHECKED />Summary'
                          +'<input type=radio name='+typeFldName+' value=\'stores\' />Stores'
                          +'<input type=radio name='+typeFldName+' value=\'products\' />Products'
                          +'<input type=radio name='+typeFldName+' value=\'document\' />Document'
                          +'<input type=submit class=submit value=\'submit\' onclick=\'getPermissions("'+searchFldId+'","'+contentDivName+'","'+typeFldName+'");\' /><HR>'
                          +'<DIV id='+contentDivName+' style="overflow:auto;max-height:180px;background:#fff;"><br><br><br><br><br><br><br></DIV></DIV><BR>';
          //parent.showMsgBoxContent(content); //OLD VERSION
          parent.popBox(content,'general',700);
          parent.document.getElementById(searchFldId).focus();
  }

  function getPermissions(searchFldId,resultDiv,typeFldName) {
          var searchVal=parent.document.getElementById(searchFldId).value;
          var type=convertElementToArray(parent.document.getElementsByName(typeFldName));
          AjaxRefreshHTML("SEARCHCRITERIA="+searchVal+"&SEARCHTYPE="+type,
                                          "<?php echo $ROOT.$PHPFOLDER; ?>functional/administration/functions/getPopupPermissionsSearch.php",
                                          resultDiv,
                                          "Retrieving Search ...",
                                          "");
  }

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

      $userInfo .= '<br><a href="javascript:popBoxClose();showPermissionsPopup()">[click to view permissions]</a></div>';

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


if(ENVIRONMENT != 'PRODUCTION'){
  echo '<div style="position:absolute;top:0;left:150px;;z-index: 10000;background:orangered;color:white;;padding:0.5rem 2rem;font-size:16px;">
            Environment: '.ENVIRONMENT.'
        </div>';
  }


echo '<div id="header_layer">';
echo '<div id="headerMain">';

  echo '<div style="white-space: nowrap;float:right;display:block;" id="userInfo"><a href="javascript:userInfo()"><div class="shad">'.$_SESSION['full_name'].'&nbsp;&nbsp;&nbsp;</div></a>
    </div>';

  echo '<div id="uiprinblock">';


            // change depot
          if(CommonUtils::isDepotUser()){

            $depotDAO = new DepotDAO($dbConn);
            $depotArr = $depotDAO->getAllDepotsForUserWHS($userId, $systemId);

            echo '<a href="javascript:;" onClick="showDepotPopup();" class="header_principal" title="Change Depot" ><div class="shad">'.$_SESSION['depot_name'].'</div></a>';
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
          echo '<a href="javascript:;" onClick="showPrincipalPopup();" class="header_principal" title="Change '.SNC::principal.'"><div class="shad">'.$pName.'</div></a>';



echo '</div>';

  //user section menu - items are system based and not role based.
  /*$userMenuArr = $dbConn->dbGetAll("select
                                      a.uid, a.role_uid mroleid, a.class, a.description as description,
                                      a.level, a.url, a.parent, a.override_path, a.target
                                    from   menu_role a
                                      inner join system_menu s on a.uid = s.menu_role_uid and s.system_uid = '".$systemId."'
                                    where section = 2
                                      order  by   if (a.order is null,999,a.order)");
*/


  /*
  $userMenuArr= array();
  foreach($userMenuArr as $mi){
    echo '<a class="aaa" href="'.$mi['url'].'" target="'.$mi['target'].'">
            <span style="float:left;">'.GUICommonUtils::systemParseMenuItem($mi['description']).'</span>
            <span style="float:right;"></span>
            <div style="clear:both"></div>
          </a>';
  }*/

#echo '</div>';
echo '</div>';

  include($ROOT. $PHPFOLDER . 'functional/main/menu.php');

echo '</div>';
echo '</div>';
?>
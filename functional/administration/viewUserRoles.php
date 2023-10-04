<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/DepotDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
CommonUtils::getSystemConventions();

if (!isset($_SESSION)) session_start();
$userId=$_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$userCategory = $_SESSION['category'];
$systemId = $_SESSION['system_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();

$prinDAO = new PrincipalDAO($dbConn);
$depotDAO = new DepotDAO($dbConn);
$adminDAO = new AdministrationDAO($dbConn);

$postTYPE = false;
$postUSERID = false;
$postPRINCIPALID = false;
CommonUtils::setPostVars();


if($postUSERID==false){
  echo 'Invalid User ID passed!';
}


if($postTYPE == 'PRINCIPAL'){


  $alArr = $prinDAO->getUserPrincipalDepotArray($postUSERID, "");

  $depotArr = $depotDAO->getAllDepotsArray();
  $grpArr = array();
  foreach($alArr as $r){
    $grpArr[$r['principal_id']][] = $r;
  }

  if(count($grpArr)==0){
    echo 'No Allocation for user!';
  } else {
    echo '<table width="100%"><thead>';
    echo '<tr><th valign="top">'.SNC::principal.'</th>';
    foreach($depotArr as $d){ echo '<th valign="top" style="width:80px;">' . str_replace(' ','<br>',$d['depot_name']) . '</th>';}

    $class = 'odd';
    echo '</tr></thead>';
    foreach($grpArr as $p){
      echo '<tr class="'. GUICommonUtils::styleEO($class) .'">';
      echo '<td style="border-right:1px solid lightSkyblue;"><a href="javascript:;" onClick="content.userAccessDetails(\'USERID='.$postUSERID.'&PRINCIPALID='.$p[0]['principal_id'].'\',\'PERMISSION\')" title="Click to View Permissions" style="font-weight:normal;">' . $p[0]['principal_name'] . '</a></td>';

      foreach($depotArr as $d){
       $out = false;
        foreach($p as $a){
         if($a['depot_id'] == $d['uid']){
           $out = '<td style="text-align:center;border-right:1px solid lightSkyblue;"><img src="images/tick_yes.gif"></td>';
         }
        }
        echo ($out)?$out:('<td style="text-align:center;border-right:1px solid lightSkyblue;"></td>');
      }
      echo '</tr>';
    }
    echo '</table>';

  }


} else if($postTYPE == 'PERMISSION'){

  if($postPRINCIPALID==false){
    echo 'Invalid '.SNC::principal;
  } else {

    $pArr = $prinDAO->getPrincipalItem($postPRINCIPALID);
    echo '<BR>User roles for : <strong>'.$pArr[0]['principal_name'] .'</strong> ('. $pArr[0]['uid'] . ')';
    echo '<BR><BR>';
    $adminDAO = new AdministrationDAO($dbConn);
    $rArr = $adminDAO->getRolesArray($postUSERID, $postPRINCIPALID, $systemId);

    echo '<table width="100%"><thead>';
    echo '<tr><th width="20">UId</th><th>Role Description</th><th>Group</th><th width="80">User has Role?</th></tr></thead>';
    $class = 'odd';
    foreach($rArr as $r){
      echo '<tr class="'. GUICommonUtils::styleEO($class) .'">';
      echo '<td style="border-right:1px solid lightSkyblue;"><small>'.$r['uid'] .'</small></td>';
      echo '<td style="border-right:1px solid lightSkyblue;"><span title="'.$r['long_description'].'">'.$r['description'].'</span></td>';
      echo '<td style="border-right:1px solid lightSkyblue;">'.$r['group'].'</td>';
      echo '<td style="text-align:center;">'.(($r['user_has_role']=='Y')?('<img src="images/tick_yes.gif">'):('')).'</td>';
      echo '</tr>';
    }
    echo '</table>';
  }


} else {
  echo 'unknown type requested!';
}


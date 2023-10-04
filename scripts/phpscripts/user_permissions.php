<?php

include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');


if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId=$_SESSION['principal_id'];


$postUSER = (!empty($_POST['postUSER'])) ? ($_POST['postUSER']) : (false);
$postPRINCIPAL = (!empty($_POST['postPRINCIPAL'])) ? ($_POST['postPRINCIPAL']) : (false);

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();


echo '<link href="'. $DHTMLROOT.$PHPFOLDER .'css/default.css" rel="stylesheet" type="text/css">';
echo '<br><br>';
echo '<div align="center">';
echo '<form method="POST" action="">';

echo '<table>';
echo '<tr>';
  echo'<td align="center" height="40">';
    BasicSelectElement::getUsersWithinPriviledgesDD("postUSER",$postUSER,"N","N",null,null,null,$dbConn,$userId, $principalId);
  echo '</td>';
echo '</tr><tr>';
  echo'<td align="center" height="40">';
  if(!empty($postUSER)){
  BasicSelectElement::getUserPrincipalDD("postPRINCIPAL",$postPRINCIPAL,"N","N",null,null,null,$dbConn,$postUSER);
  }
  echo '</td>';
echo '</tr>';
  echo '</table>';

  echo '<BR>';
  echo '<INPUT type="submit" class="submit" value="submit">';



echo '<BR><BR>';

if($postUSER!==false && $postPRINCIPAL!==false){

  echo '<HR>';

  include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
  $adminDAO = new AdministrationDAO($dbConn);

  $mfP = $adminDAO->getPermissionCounts($postUSER, $postPRINCIPAL);

		$pixelToPercRatio=5;

		echo '<H2>Visible by User:</H2>';

		echo "<TABLE style='text-align:left;font-size:13px;'>";
		$perc=($mfP[0]["store_pcnt"]>0)?round(($mfP[0]["store_pcnt"]/$mfP[0]["store_totcnt"])*100):0;
		$width=round($pixelToPercRatio*($perc));
		echo "<TR><TD nowrap>Stores (within my<br>chains & depots):</TD><TD nowrap><div style='background-color:#00AA80; width:{$width}px;'>&nbsp;</div></td><td>{$perc}% ({$mfP[0]["store_pcnt"]} out of {$mfP[0]["store_totcnt"]})</TD></TR>";

		$perc=$perc=($mfP[0]["product_pcnt"]>0)?round(($mfP[0]["product_pcnt"]/$mfP[0]["product_totcnt"])*100):0;
		$width=round($pixelToPercRatio*($perc));
		echo "<TR><TD nowrap>Products:</TD><TD nowrap><div style='background-color:#00AAAA; width:{$width}px;'>&nbsp;</div></td><td>{$perc}% ({$mfP[0]["product_pcnt"]} out of {$mfP[0]["product_totcnt"]})</TD></TR>";

		$perc=$perc=($mfP[0]["chain_pcnt"]>0)?round(($mfP[0]["chain_pcnt"]/$mfP[0]["chain_totcnt"])*100):0;
		$width=round($pixelToPercRatio*($perc));
		echo "<TR><TD nowrap>Chains:</TD><TD nowrap><div style='background-color:#00AACC; width:{$width}px;'>&nbsp;</div></td><td>{$perc}% ({$mfP[0]["chain_pcnt"]} out of {$mfP[0]["chain_totcnt"]})</TD></TR>";

		$perc=$perc=($mfP[0]["prindepot_pcnt"]>0)?round(($mfP[0]["prindepot_pcnt"]/$mfP[0]["prindepot_totcnt"])*100):0;
		$width=round($pixelToPercRatio*($perc));
		echo "<TR><TD nowrap>Depots:</TD><TD nowrap><div style='background-color:#00AAFF; width:{$width}px;'>&nbsp;</div></td><td>{$perc}% ({$mfP[0]["prindepot_pcnt"]} out of {$mfP[0]["prindepot_totcnt"]})</TD></TR>";

		echo "</TABLE>";

	    echo '<BR>';

	    echo '<H2>Permissions:</H2>';

		echo '<TABLE>';

		$userRoleArr = $adminDAO->getRolesArray($postUSER,$postPRINCIPAL);
		foreach($userRoleArr as $uRole){
		  echo '<TR bgcolor="',($uRole['user_has_role']=='Y')?('#A9F5A9'):('#F5A9A9'),'" style="border-bottom:1px solid #fff;">';
    		 echo '<TD>'. $uRole['description'] . '</TD>';
    		 echo '<TD>'. substr($uRole['long_description'],0,60) . '</TD>';
    		  echo '<TD>'.$uRole['group']. '</TD>';
    		  echo '<TD>'.$uRole['user_has_role']. '</TD>';
    		  echo '<TD>'.$uRole['restricted_to']. '</TD>';
		  echo '</TR>';
		}


		echo '</TABLE>';


}


echo '</form>';
echo '</div>';


?>
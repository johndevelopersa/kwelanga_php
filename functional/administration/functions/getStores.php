<?php

/*
 *
 * GET STORES IN JSON FOR CAPTURE SCREEN - MUST BE LIGHTWEIGHT - PHP && Returned data.
 *
 */

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION)) session_start() ;

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");



if(!isset($_SESSION['user_id']) && !isset($_SESSION['principal_id'])){
 die('ERROR: INVALID SESSION');
}


$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$depotId = isset($_SESSION['depot_id'])?$_SESSION['depot_id']:false;

$filterArr = array();
$filterArr['store'] = (isset($_POST['SEARCHSTRING'])) ? ($_POST['SEARCHSTRING']) : ('');
$postFieldsToReturn = (isset($_POST['FIELDS'])) ? (explode(',',$_POST['FIELDS'])) : (false);  //RETURN ONLY THE FIELDS YOU WANT => SMALLER DATA DL FOR AJAX.

$postVENDOR = (isset($_POST['VENDOR'])) ? ($_POST['VENDOR']) : (false);
$postVENDOR = ($postVENDOR==true) ? true : false;

if(!isset($postFieldsToReturn[0]) || $postFieldsToReturn[0] == '') exit();  //no fields = no return

if(in_array('special_field_or',$postFieldsToReturn)){
  $filterArr['special_field_or'] = $filterArr['store'];
}
if(in_array('ean_code_or',$postFieldsToReturn)){
  $filterArr['ean_code_or'] = $filterArr['store'];
}


$dbConn = new dbConnect();  //Create new database object
$dbConn->dbConnection();  //Database connection method

$storeDAO = new StoreDAO($dbConn);
$limitDepot = (CommonUtils::isDepotUser() && !empty($depotId))?($depotId):(false); //for depot users can only see stores for the selected warehouse.
$mfS = $storeDAO->getUserPrincipalStoreArray($userId, $principalAliasId, null, $filterArr, $postVENDOR, false, $limitDepot);


if (count($mfS)>0) {

  //format into javascript object.
  $j = 0;
  echo 'storeArray={';

  //Build Header Row
  for($h=0; $h < 1; $h++){

      //$headers = array_keys();
      $headerArr = array();
      foreach($mfS[$h] as $k => $data){

      if((in_array($k,$postFieldsToReturn)) || ($k == 'special_fields' && in_array('special_field_or',$postFieldsToReturn))){
        $headerArr[] = $k;
      }

    }
    echo '0:"',join(',',$headerArr).'",';
  }

  //Write JSON / String Array.
  $countMFS = count($mfS);
  foreach($mfS as $srKey => $sr){

    $dataArr = array();
    foreach($sr as $dkey => $sd){
      if((in_array($dkey,$postFieldsToReturn)) || ($dkey == 'special_fields' && in_array('special_field_or',$postFieldsToReturn))){
        //make sure the data is object/array friendly
        if($dkey=='delivery_day'&&$sd=='Not Known') $sd = ''; //change for delivery day - less text.
        $dataArr[] = utf8_encode(trim(addslashes(str_replace(array('"',"'",','), array('','',';'), $sd))));
      }
    }
    echo ($srKey+1) , ':"' , join(',',$dataArr) , '"';
    $j++;
    if($j != $countMFS) echo ',';
  }
  echo '};' . "\n";



  //Include Special fields header if principal has them
  $miscDAO = new MiscellaneousDAO($dbConn);
  $smpf = $miscDAO->getPrincipalSpecialFields($principalAliasId, CT_STORE_SHORTCODE);

  $specialFieldNames = array();
  if(count($smpf)>0){
    foreach($smpf as $sf){
      $specialFieldNames[] = utf8_encode(trim(addslashes(str_replace(array('"',"'",','), array('','',';'),$sf['name']))));
    }
  }
  echo 'specialFldNames={0:"' . implode(',',$specialFieldNames) . '"};';


}

$dbConn->dbClose();

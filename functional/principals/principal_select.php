<?php


/*-------------------------------------------------
 *
 * 	BROWSER + USER PLATFORM INFO
 *
  -------------------------------------------------*/

$browserArr = array();
$browserArr['agent'] = $_SERVER['HTTP_USER_AGENT'];
$browserArr['encoding']  =  str_replace(' ', '', $_SERVER['HTTP_ACCEPT_ENCODING']);
$browserArr['os']  = php_uname('s');
$browserArr['release']  = php_uname('r');
$browserArr['machine']  = php_uname('m');
setcookie('cookies_test',time() + '3600');  //NB!!!! Like other headers, cookies must be sent before any output from your script (this is a protocol restriction).
$browserArr['cookies'] = (isset($_COOKIE['cookies_test'])) ? 'Yes' : 'No';
$browserStr = serialize($browserArr);


include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'functional/main/access_control.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
CommonUtils::getSystemConventions();


if (!isset($_SESSION)) session_start();
$userId = $_SESSION["user_id"]; // user id set in access_control above
$systemId = $_SESSION["system_id"];
$systemName = $_SESSION['system_name'];
if(!isset($_SESSION['depot_id'])){
  $_SESSION['depot_id'] = "0";  //preset
}

$renderDepotSelect = false;

$dbConn = new dbConnect();	//Create new database object
$dbConn->dbConnection();	//Database connection method

$administrationDAO = new AdministrationDAO($dbConn);
$prinDAO = new PrincipalDAO($dbConn);
$depotDAO = new DepotDAO($dbConn);


// has selected one
if (isset($_POST['principal_list'])) {

	$principal_id_strings = $_POST['principal_list'];

	$strings=explode(',',$principal_id_strings);

	$principal_id = ( (isset($strings[1])) ? $strings[0] : "" );
	$principal_name = ( (isset($strings[1])) ? $strings[1] : "" );
	$principal_code = ( (isset($strings[1])) ? $strings[2] : "" );
	$principal_type = ( (isset($strings[1])) ? $strings[3] : "" );

	if (!isset($_SESSION)) session_start();

	$mfP = $prinDAO->getPrincipalItem($principal_id);
	if (($mfP[0]["status"]!=FLAG_STATUS_ACTIVE) && (!CommonUtils::isStaffUser())) {
	  echo "<p><span><h1>This Principal has been disabled - you cannot logon !</h1></span></p>";
	  session_unset();
	  session_destroy();
    header('Location:'.$ROOT.'elogin');  //brower independant
	  return;
	}

	$_SESSION['principal_id']         = $principal_id;
	$_SESSION['principal_name']       = $principal_name;
	$_SESSION['principal_code']       = $principal_code;
	$_SESSION['principal_type']       = $principal_type;
	$_SESSION['principal_alias_id']   = "";
	$_SESSION['principal_alias_name'] = "";

	echo '<meta http-equiv="refresh" content="0;url='.$ROOT.$PHPFOLDER.'home.php?">';
	return;
}

if($_SESSION['depot_id'] == 0){
  if(CommonUtils::isDepotUser()){
    $depotArr = $depotDAO->getAllDepotsForUserWHS($userId, $systemId);
    if(count($depotArr)==1){
      $_SESSION['depot_id'] = $depotArr[0]['uid'];
      $_SESSION['depot_name'] = $depotArr[0]['name'];
      $_SESSION['skip_inpick_stage'] = $depotArr[0]['skip_inpick_stage'];
      $_SESSION['waiting_dispatch']  = $depotArr[0]['waiting_dispatch'];
      $_SESSION['no_unaccepted']     = $depotArr[0]['no_unaccepted'];
    } elseif(count($depotArr)>1){
      $renderDepotSelect = true;
    }
  }
}

if (isset($_POST['depot_list'])) {
  $renderDepotSelect = false;
  //unknown key value;
  foreach($_POST['depot_list'] as $id => $depotName){
    $_SESSION['depot_id'] = $id;
    $vals = explode("|",$depotName);
    $_SESSION['depot_name'] = $vals[0];
    $_SESSION['skip_inpick_stage'] = $vals[1];
    $_SESSION['waiting_dispatch']  = $vals[2];
    $_SESSION['no_unaccepted']     = $vals[3];
  }
}
// this must be done before local usage of dbconn otherwise RS is lost
// load the user preferences

if($renderDepotSelect){

  $innerHTML = '<h3 style="font-size: 13px;color:red;margin-top:25px;">Please select a Depot</h3>
                <div style="margin:25px 0px 30px 0px;">';

                foreach($depotArr as $row) {
                  $innerHTML .= '<form name="depotForm" method="post" action="' . htmlentities($_SERVER['PHP_SELF']) . '" style="margin:8px 0px;padding:0px;">';
                  $innerHTML .= '<input type="submit" name="depot_list['.$row['uid'].']" value="'.$row['name']." - ".$row['skip_inpick_stage']."|".$row['waiting_dispatch'] ."|".$row['no_unaccepted'].'" class="submit" style="width:200px;line-height:22px;" />';
                  $innerHTML .= '</form>';
                }

                $innerHTML .= '</div>';


} else {

  $mfUP = $administrationDAO->getUserPreferences($userId);


 //Collect data records from database
  $dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA)
  $dbConn->dbinsQuery("UPDATE users SET lastlogin = now(), browser_user_info = '" . mysqli_real_escape_string($dbConn->connection, $browserStr) . "' WHERE uid	= '{$userId}';");
  $dbConn->dbinsQuery("commit;");


  $mfUPR = $prinDAO->getUserPrincipalArray($userId,"",true); //has built in permisions.
  $num_of_principals = sizeof($mfUPR);

  // set the user preferences
  $_SESSION["up_dps"] = (sizeof($mfUP)==0) ? (VAL_GUI_MAX_ROWS_RETURNED) : ($mfUP[0]["page_size_default"]);
  $_SESSION["up_pSortBy"] = (isset($mfUP[0]['sort_product_dropdown']))?($mfUP[0]['sort_product_dropdown']):('D');
  $_SESSION["up_cPreValid"] = (isset($mfUP[0]['capture_pre_validation_flag']))?($mfUP[0]['capture_pre_validation_flag']):('N');



  switch ($num_of_principals) {

    case 0:
        $_SESSION['principal_id'] = "0";
        $_SESSION['allowed_principals'] = serialize(array());
        header("Location:{$ROOT}{$PHPFOLDER}home.php");
    break;

    case 1:
        foreach ($mfUPR as $row) {
                $_SESSION['principal_id'] = $row['principal_id'];
                $_SESSION['principal_name'] = $row['principal_name'];
                $_SESSION['principal_code'] = $row['principal_code'];
                $_SESSION['principal_type'] = $row['principal_type'];
                $_SESSION['principal_alias_id'] = "";
                $_SESSION['principal_alias_name'] = "";
                $_SESSION['allowed_principals'] = serialize(array($row['principal_id']));
        }
        header("Location:{$ROOT}{$PHPFOLDER}home.php");
    break;

    default;

        $innerHTML = '
                        <h3 style="font-size: 13px;color:DimGray;margin-top:25px;">Please select a '.SNC::principal.'</h3>
                        <form name="principalForm" method="post" action="' . htmlentities($_SERVER['PHP_SELF']) . '" style="margin:15px 0px 20px 0px;padding:0px;">
                        <select name="principal_list" id="principallistID" autofocus="autofocus" style="font-size:13px;margin:18px 0px 10px 0px;">';

                        $pArr=array();
                        foreach ($mfUPR as $row) {
                                $innerHTML .= "<option value='{$row['principal_id']},{$row['principal_name']},{$row['principal_code']},{$row['principal_type']}'>".$row['principal_name']."</option>";
                                $pArr[$row['principal_id']]=$row['principal_id'];
                        }
                        $innerHTML .= '</select><br /><br />
                        <input type="submit" value="Submit" class="submit" />
                        </form>';
                        $_SESSION['allowed_principals'] = serialize($pArr);


    break;
  }

}

if (!isset($innerHTML)) $innerHTML="";
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo SNC::title ?></title>
    <link href="<?php echo $DHTMLROOT.$PHPFOLDER.'css/css.php?SYSID='.$systemId.'&SYSNAME='.$systemName ?>" rel="stylesheet" type="text/css" />
    </head>
    <body>
      <div align="left">
        <div id="headerMain">
          <div align="left" id="headerLogo"></div>
        </div>
      </div>

    <div align="center" style="margin-top:160px;">
      <?php echo GUICommonUtils::outputBlkBlue($innerHTML,450,false); ?>
    </div>
</body>
</html>
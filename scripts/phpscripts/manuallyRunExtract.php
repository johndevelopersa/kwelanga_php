<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/BIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostBIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');



if (!isset($_SESSION)) session_start();
$param = '?RUNME=Y';

if(!CommonUtils::isStaffUser()){

  echo 'Restricted Access!';

} else {

  $dbConn = new dbConnect();
  $dbConn->dbConnection();
  $miscDAO = new MiscellaneousDAO($dbConn);
  $mfJE = $miscDAO->getJobExecutionByName($jobName = "dailyExtracts");

  if(isset($_GET['RUNALL']) && $_GET['RUNALL'] == 'Y'){

    '<div align="center"><h1 style="color:red">running all extracts...</h1>';
     foreach($mfJE as $ex){
    $scriptPath = $ROOT.$PHPFOLDER."functional/extracts/daily/{$ex['script_name']}.php";
    if(is_file($scriptPath)){
      echo '<strong>SCRIPT: ' . basename($scriptPath) . '</strong><br>';
      echo '<iframe src="'.$scriptPath.$param.'" width="100%" height="50" frame-border="0" border="0" style="border:0px;"></iframe><br><hr>';
    }
    }
    echo '</div>';
    return;
  }

  echo '<div align="center">';
  echo '<h1>Manually Run Extract</h1>';

  echo '<a href="?RUNALL=Y" onclick="startStockTake()" class="wrap start rdCrn5">Run All Extracts</a>';
  echo '<br><br>';

  foreach($mfJE as $ex){
    $scriptPath = $ROOT.$PHPFOLDER."functional/extracts/daily/{$ex['script_name']}.php";
    if(is_file($scriptPath)){
      echo '<a href="'.$scriptPath.$param.'" target="_blank" class="rdCrn5" style="display:block;text-decoration:none;line-height:30px;width:300px;margin:5px;background:aliceBlue;border:2px solid lightskyblue;">'.$ex['script_name'].'</a>';
    }
  }
  echo '</div>';
}

?>
<style type="text/css">

  .wrap {width:280px;}
  .start, .bigbutton{
    display:block;
    margin-top:5px;
    padding:14px 0px;
    border:2px solid #DF0101;
    background:#FA5858;
    color:#fff;
    text-decoration:none;
    font-size:22px;
    font-weight:bold;
  }
  .bigbutton {
    border:2px solid lightskyblue;
    background:aliceBlue;
    color:#047;
  }
  .start.enable{background:lightskyblue;border-color:#047}
  .start:hover{background:#F7BE81; border:2px solid #FE9A2E;text-decoration:none;}
  .bigbutton:hover{color:#fff;background:#F7BE81; border:2px solid #FE9A2E;text-decoration:none;}
  .large-input{line-height:20px;height:20px;font-size:12px;padding:0px 2px;}
  #RowHighlight, #RowHighlight td{background:#FCFFB4;}
  .hasVariance, .hasVariance td{color:#B40404;}
  .hasVariance td a {color:#B40404;text-decoration:underline;}
  .hasVariance td a:hover {text-decoration:none;}
</style>
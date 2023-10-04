<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."DAO/DistributionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostDistributionDAO.php");
include_once($ROOT.$PHPFOLDER.'DAO/BIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostBIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$dbConn = new dbConnect();
$dbConn->dbConnection();
$bIDAO = new BIDAO($dbConn);
$distributionDAO = new DistributionDAO($dbConn);
$postDistributionDAO = new PostDistributionDAO($dbConn);
$miscellaneousDAO = new MiscellaneousDAO($dbConn);


$nUId = (isset($_GET['nuid'])) ? $_GET['nuid'] : false;
$pcUId = (isset($_GET['pcuid'])) ? $_GET['pcuid'] : false;
$postPrinUId = (isset($_GET['prinuid'])) ? $_GET['prinuid'] : false;
$postDUId = (isset($_GET['duid'])) ? $_GET['duid'] : false;
$postResendUid = (isset($_GET['resendUid'])) ? $_GET['resendUid'] : false;


?>
<script type='text/javascript' language='javascript' src="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
<script type='text/javascript' language='javascript' src="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

<LINK href="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>css/default.css" rel='stylesheet' type='text/css'>
<style type="text/css">
  body, th, td {font-size:11px;font-family:arial,verdana}
  </style>
<?php
echo '<div align="center" ><br>';


if($nUId == false && $pcUId == false && $postDUId == false && $postResendUid == false){

  echo '<h2 style="color:red">Error : no uid passed!</h2>';

} else if ($nUId != false) {


  $mfNR = $bIDAO->getNotificationRecipientItem($nUId);  //get notification
  $mfUsersArr = explode(',', $mfNR[0]['user_uid_list']); //get users

  $disArr = array();


  echo '<h2>Select the e-mail address.</h2><hr style="width:400px">';
  foreach($mfUsersArr as $mfUid){
    $mfC = $miscellaneousDAO->getContactItem($mfNR[0]["principal_uid"], "", $mfUid);
    if(isset($mfC[0]['email_addr'])){
      echo '<a href="?pcuid=' . $mfC[0]['uid'] . '&prinuid=' . $mfNR[0]["principal_uid"] . '">' . $mfC[0]['email_addr'] . '</a><hr style="width:400px">';
    }
  }


} else if ($pcUId != false) {

  $older = (isset($_GET['older']))?(abs($_GET['older'])+7):(0);

  $mfC = $miscellaneousDAO->getContactItem($postPrinUId, "", $pcUId);
  $dArr = $distributionDAO->getDistributionsForNDaysByEmail($mfC[0]['email_addr'], $days = (7 + $older));

  backBy(1);
  echo '<h2>Select the email you want to view</h2>';
  echo '<table class="tableReset"  border=1 cellpadding="5" cellspacing="0">';
  echo '<tr>';
    //echo '<th>uid</th>';
    echo '<th>date</th><th>subject</th><th>e-mail</th><th>attachment file</th><th></th>';
  echo '</tr>';

  foreach($dArr as $d){
    echo '<tr>';
    //echo '<td>' . $d['uid'] . '</td>';
    echo '<td>' . $d['run_date'] . '</td>';
    echo '<td>' . substr($d['subject'],0,128) . '</td>';
    echo '<td>' . $d['destination_addr'] . '</td>';
    echo '<td>' . basename($d['attachment_file']) . '</td>';
    echo '<td style="background:yellow;"><a href="?duid='.$d['uid'].'">open</a></td>';
    echo '</tr>';
  }

  echo '</table>';


  echo '<br><br><a href="?'.  http_build_query($_GET).'&older='.(($older==0)?(7):($older)).'" style="color:#F78181;font-size:14px;font-weight:bold;text-decoration:none;">Older... +7 days</a>';


} else if ($postDUId != false){

  backBy(1);
  echo '<br><div><a href="?resendUid='.$postDUId.'" style="background:darkred;font-size:14px;text-decoration:none;padding:10px 25px;color:#fff;">RESEND EMAIL</A></div>';
  echo '<br><hr>';


  //VIEWER
  include_once($ROOT.$PHPFOLDER.'libs/EncryptionClass.php');
  $eC = new EncryptionClass();
  $_GET['DID'] = $eC->encryptUIDValue($postDUId, 2, 8);
  include('distributionCard.php');


} else if ($postResendUid != false){

  backBy(2);

  $rTO = $postDistributionDAO->setDistributionStatus($postResendUid, FLAG_STATUS_QUEUED);
  if($rTO->type == FLAG_ERRORTO_SUCCESS){
    $dbConn->dbinsQuery("commit;");
    echo '<h2>Successfully flagged email to be resent!</h2>';
  } else {
    $dbConn->dbinsQuery("rollback;");
    echo 'error has occured flagging distribution';
    echo $rTO->description;
  }


}

echo '</div>';

function backBy($n){
  echo '<a href="javascript:history.go(-'.$n.')" style="background:orange;font-size:12px;color:#fff;padding:5px 15px;">back</a><Br><br><hr style="width:400px">';

}
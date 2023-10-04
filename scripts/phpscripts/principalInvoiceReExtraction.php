<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once ($ROOT.$PHPFOLDER."DAO/BIDAO.php");
include_once ($ROOT.$PHPFOLDER."DAO/PostExtractDAO.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

if (!isset($_SESSION)) session_start();
$userId=$_SESSION['user_id'];

$postPRINCIPALUID = ((isset($_POST["PRINCIPALUID"]))?$_POST["PRINCIPALUID"]:$_SESSION['principal_id']);
$postDATE = ((isset($_POST["DATE"]))?$_POST["DATE"]:"");
$postDOCTYPES = implode(",",((isset($_POST["DOCTYPES"]))?$_POST["DOCTYPES"]:array()) );
$postNOTIFICATIONUID = ((isset($_POST["NOTIFICATIONUID"]))?$_POST["NOTIFICATIONUID"]:NT_DAILY_EXTRACT_CUSTOM);

$dbConn = new dbConnect();
$dbConn->dbConnection(); // can connect to any db, as long as db is referenced as part of sql passed

if(!CommonUtils::isStaffUser()){

  echo 'Restricted Access!';
  return;

}

$miscDAO = new MiscellaneousDAO($dbConn);
$mfJE = $miscDAO->getJobExecutionByName($jobName = "dailyExtracts", $postPRINCIPALUID);

?>
<style>
body { font-family: calibri; }
td.col1 { font-weight:bold; color:#AAAAAA; }
</style>

<form id='paramForm' name='paramForm' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post'>

<table>

<tr><td class='col1'>Choose Principal :</td>
<td>
<?php echo BasicSelectElement::getUserPrincipalDD("PRINCIPALUID", $postPRINCIPALUID, false, false, "", "", "", $dbConn, $userId) ?>
</td></tr>

<tr><td class='col1'>Enter the Invoice Date to Extract for :</td>
<td><input type='text' name='DATE' value='<?php echo $postDATE ?>'> format : YYYY-MM-DD</td></tr>

<tr><td class='col1'>Check Document Types:</td>
<td>
  <input type='checkbox' name='DOCTYPES[]' value='1,13,6' <?php echo ((in_array("1",explode(",",$postDOCTYPES)))?"checked='checked'":"") ?> > Orders/Invoice&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <input type='checkbox' name='DOCTYPES[]' value='4' <?php echo ((in_array("4",explode(",",$postDOCTYPES)))?"checked='checked'":"") ?> > Credits&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <input type='checkbox' name='DOCTYPES[]' value='8' <?php echo ((in_array("8",explode(",",$postDOCTYPES)))?"checked='checked'":"") ?> > Debits&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</td></tr>

<tr><td class='col1'>Extraction Job:</td>
<td>
  <input type='radio' name='NOTIFICATIONUID' value='<?php echo NT_DAILY_EXTRACT_CUSTOM ?>' <?php echo (($postNOTIFICATIONUID==NT_DAILY_EXTRACT_CUSTOM)?"checked='checked'":"") ?> > Primary&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <input type='radio' name='NOTIFICATIONUID' value='<?php echo NT_DAILY_EXTRACT_ALTCUSTOM?>' <?php echo (($postNOTIFICATIONUID==NT_DAILY_EXTRACT_ALTCUSTOM)?"checked='checked'":"") ?> > Secondary&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</td></tr>

</table>
<br>

<input type='button' value='submit' onclick='document.paramForm.submit();'>
</form>
<?php

if (($postDATE != "") && ($postDOCTYPES!="")) {
  $parts = explode("-",$postDATE);
  $y=((isset($parts[0]))?$parts[0]:"");
  $m=((isset($parts[1]))?$parts[1]:"");
  $d=((isset($parts[2]))?$parts[2]:"");
  if (!checkdate($m, $d, $y)) {
    echo "<p>Invalid Date</p>";
    return;
  }

  // get the type_uid
  $biDAO = new BIDAO($dbConn);
  $reArr = $biDAO->getNotificationRecipients($postPRINCIPALUID, $postNOTIFICATIONUID);
  if (count($reArr)==0) {
    echo "<p>No recipients found for this principal extract - or Extract failed to load notification load recipients in ".__FILE__."</p>";
    return;
  }
  $recipientUId = $reArr[0]['uid'];

  $postExtractDAO = new PostExtractDAO($dbConn);

  // make sure all are loaded for the chosen dates
  $docTypes = array();
  if (in_array("1",explode(",",$postDOCTYPES))) { $docTypes[]="1"; $docTypes[]="13"; $docTypes[]="6"; }
  if (count($docTypes)>0) {
    $rTO = $postExtractDAO->queueAllInvoiced($postPRINCIPALUID, $recipientUId, $inclCancelled = false, $docTypes, $p_wDSArr=false,$postDATE,$postDATE);
    if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
      echo "<p>Export failed to call postExtractDAO->queueAllInvoiced in ".__FILE__." " . $rTO->description."</p>";
    } else {
      $dbConn->dbinsQuery("commit;");
    }
  }

  $docTypes = array();
  if (in_array("4",explode(",",$postDOCTYPES))) $docTypes[]="4";
  if (in_array("8",explode(",",$postDOCTYPES))) $docTypes[]="8";
  if (count($docTypes)>0) {
    $rTO = $postExtractDAO->queueAllCreditsAndDebits($postPRINCIPALUID, $recipientUId, $p_dtArr=$docTypes, $postDATE, $postDATE);
    if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
      echo "<p>Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".__FILE__." " . $rTO->description."</p>";
    } else {
      $dbConn->dbinsQuery("commit;");
    }
  }


  $sql = "update
                smart_event a,
          		  document_master b,
          		  document_header c
          set a.status = 'Q'
          where a.data_uid = b.uid
          and   b.uid = c.document_master_uid
          and   b.document_type_uid in ({$postDOCTYPES})
          and   b.principal_uid = {$postPRINCIPALUID}
          and   c.invoice_date = '{$postDATE}'
          and   a.type = '".SE_EXTRACT."'
          and   a.type_uid = '".$recipientUId."'";

  $rTO = $dbConn->processPosting($sql, "");

  if ($rTO->type!="S") {
    $dbConn->dbinsQuery("rollback");
    echo "<p>ERROR : Could not Requeue !".mysql_error($dbConn->connection)."</p>";
    return;
  } else {
    $dbConn->dbinsQuery("commit");
    echo "<p>Requeued {$rTO->object["rows_matched"]} docs.</p>";
  }

  echo '<div align="center"><h1 style="color:red">';
  $param = '?RUNME=Y&SKIPINSERT=Y';
  if (count($mfJE)==0) {
    echo "No JobExecutions loaded for this principal - cannot extract.";
  }
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
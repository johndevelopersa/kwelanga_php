<?php

/* * ********************************************************************************************
 * *
 * *  This job can run as many times per day as is necessary according to job scheduler.
 * *
 * *  It executes notifications that occur throughout the day by triggers
 * *
 * ******************************************************************************************** */

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "functional/extracts/daily/extractController.php");
include_once($ROOT.$PHPFOLDER."libs/EncryptionClass.php");



class extractManagement extends extractController {


  public function renderErrors(){

    global $ROOT,$PHPFOLDER;
    $cryptPrincipalId = isset($_GET['p']) ? $_GET['p'] : die("Invalid Request!");

    $encryption = new EncryptionClass();
    $principalId = $encryption->decryptUIDValue($cryptPrincipalId);
    if($principalId===false){
      die("Restricted Access!");
    }

    // only supports one of each type !
    $reArr = $this->bIDAO->getNotificationRecipients($principalId, NT_DAILY_EXTRACT_CUSTOM);
    if (count($reArr)!=0) {
      $recipientUIdArr[] = $reArr[0]['uid'];
    }
        
    $reArr1 = $this->bIDAO->getNotificationRecipients($principalId, NT_DAILY_EXTRACT_ALTCUSTOM1);
    if (count($reArr1)!=0) {
      $recipientUIdArr[] = $reArr1[0]['uid'];
    }
        
    $reArr2 = $this->bIDAO->getNotificationRecipients($principalId, NT_DAILY_EXTRACT_ALTCUSTOM2);
    if (count($reArr2)!=0) {
      $recipientUIdArr[] = $reArr2[0]['uid'];
    }
        
    $reArr3 = $this->bIDAO->getNotificationRecipients($principalId, NT_DAILY_EXTRACT_ALTCUSTOM3);
    if (count($reArr3)!=0) {
      $recipientUIdArr[] = $reArr3[0]['uid'];
    }
        
    $reArr4 = $this->bIDAO->getNotificationRecipients($principalId, NT_DAILY_EXTRACT_ALTCUSTOM4);
    if (count($reArr4)!=0) {
      $recipientUIdArr[] = $reArr4[0]['uid'];
    }
    
    if (count($recipientUIdArr)==0) {
      die("No extracts loaded for principal!");
    }
    
    //get list of errors.
    foreach($recipientUIdArr as $Arr){
    $seDocs = $this->extractDAO->getExtractErrors($principalId, $recipientUIdArr);
    }
    //get a grouped list of missing special fields...
    //more work will need to be done here with multiple missing special fields...
    $spfArr = array();
    $spfDataArr = array();
    foreach($seDocs as $e){
      if(!empty($e['general_reference_1'])){
        $eArr = explode(',', $e['general_reference_1']);
        foreach($eArr as $i){
          $spfArr[$i] = $i;
        }
      }
    }
    foreach($spfArr as $i){
      $spfArr = $this->miscDAO->getPrincipalSpecialFieldbyUid($i);  //build list of names for special fields missing.....
      if(isset($spfArr[0]['principal_uid']) && $spfArr[0]['principal_uid'] == $principalId){
        $spfDataArr[$i] = $spfArr;
      }
    }



    $prinArr = $this->principalDAO->getPrincipalItem($principalId);


    $class = 'odd';
    echo '<!DOCTYPE html>
          <html>
          <head>
            <title>Extract Errors</title>
          </head>
          <body style="padding:0px;margin:0px;background:#efefef">';

    echo '<LINK href="'. $ROOT.$PHPFOLDER.'css/default.css" rel="stylesheet" type="text/css">
          <script type="text/javascript" language="javascript" src="'. $ROOT.$PHPFOLDER.'js/jquery.js"></script>';
    echo "<div align='center'>";

    echo '<div style="width:750px;background:#047;font-size:36px;padding:10px 10px;color:#fff;">'.$prinArr[0]['principal_name'].'</div>';
    echo '<div style="width:750px;display:block;background:#fff;padding:10px 10px;">';
    echo '<br><h1 style="font-size:28px;color:red;">Store Errors</h1>';

    echo "<h3>The following stores have missing data required for your extract from Kwelanga Solutions.</h3>";
    echo "<P style='width:700px;text-align:left;line-height:22px;'><u>Following these easy steps:<br></u>";
    echo "1. Log into the Kwelanga Solutions system and add the missing data. <a href='".HOST_SURESERVER_AS_USER."' target='_blank'>click here to login</a><br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;To add this information, navigate to <q>Masterfile Maintenance</q> > <q>Maintain Stores</q> > <q>Modify Store</q><br>";
    echo "2. Once completed, you will receive these orders in the next extract process run.<br>";
    echo "<br>";
    echo '<i style="color:red;">If one of the below errors is NOT required to be extracted, click on "CLEAR" to remove it.</i>';
    echo "</P>";

    echo "<br>";

    echo "<div align=\"left\">Total errors: " . count($seDocs) . '</div>';
    echo "<table border='0' width=\"100%\">";
    echo "<thead><tr><th>Store</th><th>Depot</th><th>Document No.</th><th>Error</th><th>Action</th></tr></thead>";
    foreach($seDocs as $se){
        echo "<tr style='border:0px;border-bottom:1px solid #fff;height:25px;' class='".GUICommonUtils::styleEO($class)."' id='serow_".$se['se_uid']."'>";

          echo "<td><strong>" . $se['deliver_name'] . "</strong></td>"; //<td><input type='text' value='' name=''></td>";
          echo "<td>" . $se['depot_name'] . "</td>";
          echo "<td>". $se['document_number'] . "</td>";
          echo "<td valign='top' style='color:red;' >";

          $eArr = explode(',', $se['general_reference_1']);
          $eList = array();
          foreach($eArr as $i){
            if(isset($spfDataArr[$i])){
              $eList[] = trim($spfDataArr[$i][0]['name']);
            }
          }
          echo join(', ', $eList);


          echo "</td>";

          //delete error
          echo '<td><a href="javascript:;" onClick="actionCLEAR('.$se['se_uid'].')">[CLEAR]</a></td>';

        echo "</tr>";
    }
    echo '</table>';
    echo "</div>";

    echo "<br><br>";


  }
}


$extract = new extractManagement();
$extract->renderErrors();


?>
<script type="text/javascript">

function actionCLEAR(uid){

  if(confirm("Are you sure you want to clear this extract error?\n\n*This action cannot be undone!")){

    params = 'ACTION=CLEAR&SMARTID=' + uid;
    $.ajax({
      url: "<?php echo $ROOT.$PHPFOLDER ?>functional/extracts/errorManagementSubmit.php",
      global: false,
      type: 'POST',
      data: params,
      dataType: 'html',
      cache: false,
      success: function(msg){
        try {
            eval(msg);
            if (msgClass.type=="S"){
              $('#serow_'+uid).children('td').attr('style','text-decoration:line-through;color:#999');
            } else {
              alert(msgClass.description);
            }
        } catch (e) { alert('an unexpected error occurred:'+e.description+msg); }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        alert('Error Occured Deleting Store Error');
      }
  });

  }
}

</script>
</body>
</html>





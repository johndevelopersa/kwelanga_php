<?php


include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'libs/common.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDepotDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/DepotTO.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/DepotDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");  //Custom Fields
include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");

//Database Connection
$dbConn = new dbConnect();
$dbConn->dbConnection();


if (!isset($_SESSION)) session_start() ;
$principalId = $_SESSION['principal_id'] ;
$userId = $_SESSION["user_id"];


$postDMLTYPE = (isset($_POST['DMLTYPE'])) ? ($_POST['DMLTYPE']):("VIEW");
$postDEPOTID = (isset($_POST['DEPOTID']) && is_numeric($_POST['DEPOTID'])) ? $_POST['DEPOTID'] : false;



if($postDEPOTID == false){

  $postDEPOTCODE = (isset($_POST['DEPOTCODE'])) ? ($_POST['DEPOTCODE']):("");
  $postDEPOTNAME = (isset($_POST['DEPOTNAME'])) ? ($_POST['DEPOTNAME']):("");
  $postDEPOTEMAILLST = (isset($_POST['DEPOTEMAILLST'])) ? ($_POST['DEPOTEMAILLST']):("");
  $postDEPOTWMS = (isset($_POST['DEPOTWMS'])) ? ($_POST['DEPOTWMS']):("N");
  $postDEPOTDDCALENDAR = 'N';
  $postSKIPINPICKSTAGE = 'N';
  $postDEPOTCHARGE = 'Y';
  $postDELIVERYNOTE = 'N';
  $postDEPOTPAPERCHARGE = 'N';

} else {

  $depotDAO = new DepotDAO($dbConn);
  $depotSel = $depotDAO->getDepotItem($postDEPOTID);  //a.uid, a.code, a.name depot_name

  //make sure fields are set.
  //Re-SET Depot ID.
  $postDEPOTID = (isset($depotSel[$postDEPOTID]) && isset($depotSel[$postDEPOTID]['uid'])) ? ($depotSel[$postDEPOTID]['uid']):("");
  $postDEPOTCODE = (isset($depotSel[$postDEPOTID]) && isset($depotSel[$postDEPOTID]['code'])) ? ($depotSel[$postDEPOTID]['code']):("");
  $postDEPOTNAME = (isset($depotSel[$postDEPOTID]) && isset($depotSel[$postDEPOTID]['depot_name'])) ? ($depotSel[$postDEPOTID]['depot_name']):("");
  $postDEPOTEMAILLST = (isset($depotSel[$postDEPOTID]) && isset($depotSel[$postDEPOTID]['depot_email_list'])) ? ($depotSel[$postDEPOTID]['depot_email_list']):("");
  $postDEPOTWMS = (isset($depotSel[$postDEPOTID]) && isset($depotSel[$postDEPOTID]['wms'])) ? ($depotSel[$postDEPOTID]['wms']):("N");
  $postDEPOTDDCALENDAR = (isset($depotSel[$postDEPOTID]) && isset($depotSel[$postDEPOTID]['delivery_calendar_enabled'])) ? ($depotSel[$postDEPOTID]['delivery_calendar_enabled']):("N");
  $postSKIPINPICKSTAGE = (isset($depotSel[$postDEPOTID]) && isset($depotSel[$postDEPOTID]['skip_inpick_stage'])) ? ($depotSel[$postDEPOTID]['skip_inpick_stage']):("N");
  $postDEPOTCHARGE = (isset($depotSel[$postDEPOTID]) && isset($depotSel[$postDEPOTID]['charge'])) ? ($depotSel[$postDEPOTID]['charge']):("Y");
  $postDEPOTPAPERCHARGE = (isset($depotSel[$postDEPOTID]) && isset($depotSel[$postDEPOTID]['paper_charge'])) ? ($depotSel[$postDEPOTID]['paper_charge']):("N");
  $postDELIVERYNOTE = (isset($depotSel[$postDEPOTID]) && isset($depotSel[$postDEPOTID]['delivery_note'])) ? ($depotSel[$postDEPOTID]['delivery_note']):("N");
}


/*
 * ROLE PATROL
 */

$adminDAO = new AdministrationDAO($dbConn);
switch ($postDMLTYPE) {
  case "INSERT" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_ADD_DEPOT);
      break;
    }
  case "UPDATE" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_MODIFY_DEPOT);
      break;
    }
  case "VIEW" :
    {
      $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_VIEW_DEPOT);
      break;
    }
  default :
    $hasRole = false;
}
if (! $hasRole) {
  echo 'You do not have permissions to ' , $postDMLTYPE , ' a Depot.';
  return;
}



#--------------------------------------------------------------------------------------------------------------------------
/*
 * START OF DEPOT SCREEN
 */
#--------------------------------------------------------------------------------------------------------------------------

$class = 'odd';
  echo '<br />';
  echo '<INPUT type="hidden" id="DEPOTID" value="' . $postDEPOTID . '" />';
  echo '<TABLE width="600" border="0">';
  echo '<thead><tr>';
  echo '<th colspan="2">', mb_convert_case($postDMLTYPE, MB_CASE_TITLE), ' Depot</th>';
  echo '</tr></thead>';
  echo '<tbody style="font-size: 11px;">';
  echo '<tr class="'.GUICommonUtils::styleEO($class).'">';
    echo '<td width="140">Depot Code: ',GUICommonUtils::requiredField(),'</td>';
    echo '<td><INPUT type="text" size="2" maxlength="2" id="DEPOTCODE" value="' . $postDEPOTCODE . '" '. (($postDMLTYPE!='INSERT')?('DISABLED'):('')).'/></td>';
  echo '</tr>';
  echo '<tr class="'.GUICommonUtils::styleEO($class).'">';
    echo '<td >Depot Name: ',GUICommonUtils::requiredField(),'</td>';
    echo '<td><INPUT type="text" size="30" maxlength="30" id="DEPOTNAME" value="' . $postDEPOTNAME . '" /></td>';
  echo '</tr>';
  echo '<tr class="'.GUICommonUtils::styleEO($class).'">';
    echo '<td >Uses RT online DOPS<br>Warehouse Management System: ',GUICommonUtils::requiredField(),'</td>';
    echo '<td>'; BasicInputElement::getGeneralHorizontalRB('DEPOTWMS',"Yes,No","Y,N",$postDEPOTWMS,"N","N",null,null,null); echo '</td>';
  echo '</tr>';
  echo '<tr class="'.GUICommonUtils::styleEO($class).'">';
  echo '<td >Skip In-Pick Stage: </td>';
  echo '<td>'; BasicInputElement::getGeneralHorizontalRB('SKIPINPICKSTAGE',"Yes,No","Y,N",$postSKIPINPICKSTAGE,"N","N",null,null,null); echo '</td>';
  echo '</tr>';
  echo '<tr class="'.GUICommonUtils::styleEO($class).'">';
  echo '<td >Charge Depot: </td>';
  echo '<td>'; BasicInputElement::getGeneralHorizontalRB('DEPOTCHARGE',"Yes,No","Y,N",$postDEPOTCHARGE,"N","N",null,null,null); echo '</td>';
  echo '</tr>';
  echo '<tr class="'.GUICommonUtils::styleEO($class).'">';
  echo '<td >Delivery Note: </td>';
  echo '<td>'; BasicInputElement::getGeneralHorizontalRB('DELIVERYNOTE',"Yes,No","Y,N",$postDELIVERYNOTE,"N","N",null,null,null); echo '</td>';
  echo '</tr>';
  echo '<tr class="'.GUICommonUtils::styleEO($class).'">';
  echo '<td >Charge Paper: </td>';
  echo '<td>'; BasicInputElement::getGeneralHorizontalRB('DEPOTPAPERCHARGE',"U,C,N","U,C,N",$postDEPOTPAPERCHARGE,"N","N","N",null,null); echo '</td>';
  echo '</tr>';

  //DEPOT CONTACTS
  echo '<tr class="'.GUICommonUtils::styleEO($class).'">';
    echo '<td title="Depot Contacts, will receive any information/alerts/news pertaining to this Depot.">Contact Email List: </td>';
    echo '<td>';
      //JS func to handle addition of input boxes.
   echo <<<EOF
  <SCRIPT type="text/javascript">
  function addNewField(writeId, size, len){jQuery('#'+writeId).append(' <INPUT type="text" size="'+size+'" maxlength="'+len+'" value="" style="margin:1px 0px;">');}
  </SCRIPT>
EOF;

   echo '<span id="emailList">';

   $noEmailList = false;
   if(!empty($postDEPOTEMAILLST)){
     $emailListArr = explode(';',$postDEPOTEMAILLST);
     if(count($emailListArr)>0){
       foreach($emailListArr as $e){
         echo ' <INPUT type="text" size="30" maxlength="50" value="'.trim($e).'" style="margin:1px 0px;">';
       }
     } else {
       $noEmailList = true;
     }
   } else {
     $noEmailList = true;
   }

   if($noEmailList == true){
     echo '<script>addNewField(\'emailList\', 30, 50);</script>';
   }
    echo '</span> ';
    if($postDMLTYPE=="INSERT" || $postDMLTYPE=="UPDATE"){
      echo'<A href="javascript:addNewField(\'emailList\', 30, 50)" title="Add Another Field"><img src="../../images/add_icon.png" width="24" height="24" border="0" style="padding:0px;margin:0px;margin-bottom:-8px"></a>';
    }
    echo '</td>';
  echo '</tr>';



  /*
   * CUSTOM DEPOT FIELDS : START
   */



  $miscDAO = new MiscellaneousDAO($dbConn);

  if ($postDEPOTID==""){
    $smpf = $miscDAO->getPrincipalSpecialFields($principalId,CT_DEPOT_SHORTCODE);
  } else {
    $smpf = $miscDAO->getPrincipalSpecialFieldValues($principalId,$postDEPOTID,CT_DEPOT_SHORTCODE);
  }


  for($i = 0; $i < count($smpf); $i++) {

    $line = $smpf[$i];
    $value = str_replace(' ','',$line["name"]);
    $postVal = ($postDEPOTID=="") ? ("") : (htmlentities(htmlspecialchars($line['value'])));
    $required = ($line['required']=='Y') ? true : false;
    $maxLength = ($line['value_max_length'] >= 1) ? ($line['value_max_length']) : (30);  //30 is the default max length.

    echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo '<TD>'. $line["name"] . ' ' , ($required)?(GUICommonUtils::requiredField()):('') , '</TD>';
        echo '<TD id="csf_' . $value . '"><INPUT type="text" size="20" maxlength="' . $maxLength . '" value="' .$postVal . '" ',($line["editable"]=='N' && $postDEPOTID!="")?('DISABLED'):(''),'>';

        //loop through array and echo same names out.
        for($j = $i; $j < count($smpf); $j++) {
            if(isset($smpf[$i+1]) && $value == str_replace(' ','',$smpf[$i+1]["name"])){
              echo ' <INPUT type="text" size="20" maxlength="' . $maxLength . '" value="' .$smpf[$i+1]["value"] . '" ',($line["editable"]=='N' && $postDEPOTID!="")?('DISABLED'):(''),'>';
              $i++;
          }
        }

        echo '<span id="nfid',$i,'"></span>';
        if($postDMLTYPE=="INSERT" || ($postDMLTYPE=="UPDATE" && $line["editable"]=='Y')){
          echo'<A href="javascript:addNewField(\'nfid'.$i.'\', 20, ' . $maxLength . ')" title="Add Another Field"><img src="../../images/add_icon.png" width="24" height="24" border="0" style="padding:0px;margin:0px;margin-bottom:-8px"></a>';
        }
      echo '</TD>';
    echo '</TR>',"\n";

   }

  /*
   * CUSTOM DEPOT FIELDS : END
   */


  //DEPOT DELIVERY CALENDAR : START
  if(
      $postDMLTYPE=="UPDATE"
      && $postDEPOTDDCALENDAR == 'Y'  //has calendar enabled.
      && $adminDAO->hasRole($userId, $principalId, ROLE_MODIFY_DEPOT_CALENDAR)  //user can change dd calender.
    ){

    //display
    echo '<tr class="'.GUICommonUtils::styleEO($class).'">';
      echo '<td>Delivery Calendar:</td>';
      echo '<td>';
      echo '<INPUT type="button" class="submit" onclick="window.open(\''.$ROOT.$PHPFOLDER.'functional/depot/deliveryCalendar.php?DEPOTID='.$postDEPOTID.'\');" value="Maintain Delivery Calender" style="margin:5px 0px" /></td>';
    echo '</tr>';
  }
  //DEPOT DELIVERY CALENDER : END



  echo '</tbody>';
  echo '</TABLE><br />';

  if (($postDMLTYPE == "INSERT") || ($postDMLTYPE == "UPDATE")) {
    echo '<INPUT type="button" class="submit" onclick="submitContentForm(\'' . $postDMLTYPE . '\')" value="Submit Depot" />';
  }

  //make space for expanding custom fields => so submit doesn't get hidden => user needs to scroll down.
  echo '<br /><br /><br /><br /><br /><br /><br /><br />';

#--------------------------------------------------------------------------------------------------------------------------


$dbConn->dbClose();

?>
<script type='text/javascript' >

var alreadySubmitted=false;

function successCallback(p_type) {

	if (p_type=="INSERT") {
  		document.getElementById("DEPOTCODE").value='';
  		document.getElementById("DEPOTNAME").value='';
  		document.getElementById("DEPOTID").value='';

  		var obj = jQuery('#emailList input');
  		for(var i=0;i<obj.length;i++){
        	obj.eq(i).val('');
      	}
  		//empty custom fields
  		<?php
  	      foreach ($smpf as $line) {
  	    	echo 'var obj = jQuery(\'#csf_', str_replace(' ','',$line["name"]),' input\').val(\'\');';
  	      }
  	    ?>
	}
}

function submitContentForm(p_type) {
	if (alreadySubmitted) {
		return;
	}
	alreadySubmitted=true;

	var params='DMLTYPE='+p_type;
	params+='&DEPOTCODE='+document.getElementById("DEPOTCODE").value;
	params+='&DEPOTNAME='+document.getElementById("DEPOTNAME").value;
	params+='&DEPOTID='+document.getElementById("DEPOTID").value;
	params+='&DEPOTWMS='+convertElementToArray(document.getElementsByName("DEPOTWMS"));
	params+='&SKIPINPICKSTAGE='+convertElementToArray(document.getElementsByName("SKIPINPICKSTAGE"));
  params+='&DEPOTCHARGE='+convertElementToArray(document.getElementsByName("DEPOTCHARGE"));
  params+='&DEPOTPAPERCHARGE='+convertElementToArray(document.getElementsByName("DEPOTPAPERCHARGE"));
  params+='&DELIVERYNOTE='+convertElementToArray(document.getElementsByName("DELIVERYNOTE"));

	//contact list
  	var obj = jQuery('#emailList input');
  	var emailList = new Array();
  	for(var i=0;i<obj.length;i++){
      emailList[i] = obj.eq(i).val().replace(/'/g,'').replace(/"/g,'');
    }
	params+='&DEPOTEMAILLST='+emailList.join(';');;

	<?php

	  //BUILD SUBMIT FOR CUSTOM FIELDS
      foreach ($smpf as $line) {
        $value = str_replace(' ','',$line["name"]);
       ?>
       var obj = jQuery('#csf_<?php echo $value ?> input');
	   var <?php echo $value ?> = new Array();
	   for(var i=0;i<obj.length;i++){
         <?php echo $value?>[i] = obj.eq(i).val().replace(/'/g,'').replace(/"/g,'');
       }
		<?php
		 echo "params+='&".$value."='+".$value.".join('#,#');";
      }

     ?>

	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/depot/depotSubmit.php',
						  'alreadySubmitted=false; successCallback();if (msgClass.type=="S") successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
						  'Please wait while request is processed ...');
}

function errorCallback(p_type) {}

</script>

<?php

include_once 'ROOT.php'; include_once $ROOT.'PHPINI.php';
require $ROOT.$PHPFOLDER."functional/main/access_control.php";
include_once $ROOT.$PHPFOLDER."elements/basicSelectElement.php";
include_once $ROOT.$PHPFOLDER.'elements/basicInputElement.php';
include_once $ROOT.$PHPFOLDER.'libs/GUICommonUtils.php';
include_once $ROOT.$PHPFOLDER."DAO/AdministrationDAO.php";
include_once $ROOT.$PHPFOLDER."DAO/DistributionDAO.php";
include_once $ROOT.$PHPFOLDER."DAO/BIDAO.php";
include_once $ROOT.$PHPFOLDER.'properties/Constants.php';
include_once $ROOT.$PHPFOLDER.'elements/Messages.php'; // ie8 layers z-index weirdly, so popup in this form will appear under parent's modal layer regardless of z-index

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$staffUser = $_SESSION['staff_user'];

// fields
$fldChosenDRB = 'ChosenDeal';

$dbConn = new dbConnect();
$dbConn->dbConnection();

if (isset($_GET["action"])) $postDMLTYPE = ($_GET["action"]); else $postDMLTYPE = "VIEW";
if (isset($_GET["PFORM_RB"])) $postPFORMRB = ($_GET["PFORM_RB"]); else $postPFORMRB = "SMART";  //changed to GET for less complexity
if (isset($_GET["PFORM_N"])) $postPFORMN = ($_GET["PFORM_N"]); else $postPFORMN = false; // uid of notification type
$postPFORMNNAME = (isset($_GET["PFORM_NNAME"])) ? $_GET["PFORM_NNAME"] : 'INVALID'; // uid of notification type

Messages::msgboxSubModalLayer();



// check roles
$hasModifyRole=false; // initialised because it is checked later without knowing DMLTYPE
$hasViewRole=false; // initialised because it is checked later without knowing DMLTYPE

// it is necessary to enforce SU role check here, because some notification such as CAPTURE DUPLICATE check must not be able to be changed by user themselves unless SU
$administrationDAO = new AdministrationDAO($dbConn);
$hasRoleSU = $administrationDAO->hasRoleSuperUser($userId,$principalId);

if (!$hasRoleSU===true) {
	echo "You do not have permissions (Super User Role) to modify notifications!";
	return;
}


#--------------------------------------------------------------------------------------------------------------------------
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<HTML>
<HEAD>
<script type='text/javascript' language='javascript' src="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
<script type='text/javascript' language='javascript' src="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
<link href='<?php ECHO $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>
<STYLE>

  table {
          border:0px;
  }
  .notificationItem, a.notificationItem:link, a.notificationItem:visited {
    color:#efefef;
  }
  a.notificationItemNormal{
    width:220px;
    margin:4px 0px;
    display:block;
  }
  #notificationDiv {
    margin-top:-20px;
  }
  #notificationOptions {
    background:aliceblue;
    border-bottom: 1px solid lightskyblue;
  }
  #notificationOptions{
    padding:30px 0px;
  }
  #notificationDiv .menuTab{
    padding:0px;
    margin:0px;
    padding-right:100px;
  }
  #notificationDiv .menuTab .link{
    margin-top:-1px;
    display:block;
    width:100px;
    height:32px;
    padding:0px 30px 0px 50px;
    background: aliceblue url('<?php ECHO $DHTMLROOT.$PHPFOLDER ?>images/menu_icon.png') 5px center no-repeat;
    color:#047;
    font-size:18px;
    font-weight:bold;
    line-height:30px;
    text-decoration:none;
    border:1px solid #87CEFA;
    border-top:0px;
    outline:0px;
    text-align:center;
  }
  #notificationOptions, #notificationDiv .menuTab .link{
    -webkit-box-shadow:0px 3px 5px rgba(0,0,0,.1);
    -moz-box-shadow:0px 3px 5px rgba(0,0,0,.1);
    box-shadow:0px 3px 5px rgba(0,0,0,.1);
  }
  .info{
    color:#888;
    line-height:16px;
  }
</STYLE>
<SCRIPT type="text/javascript" >


  $(document).ready(function(){

    $("a.notificationTabItem").click(function(){
      $('.notificationTabs li').removeClass("active");  //remove all active
      $(this).parent('li').addClass('active');  //high the selected menu item.
    });

  });

  function refreshRecipients(type,index) {
          //showPopup();

          // do not allow filters because you will lose your user selection
          if (type=='U') {
                  AjaxRefresh("RBNAME=POPUP_USERS&RBTYPE=tick&SHOWFILTER=N&COMPRESS=Y&PAGESIZE=1000",
                                                  "<?php echo $ROOT.$PHPFOLDER; ?>functional/administration/adminUsersListTable.php",
                                                  "innerParamsPopup",
                                                  "Retrieving User List ...",
                                                  "populateUsers("+index+"); showPopup();addRecipientButtons("+index+");");
          } else {
                  AjaxRefresh("RBNAME=POPUP_USERS&RBTYPE=tick&SHOWFILTER=N&COMPRESS=Y&PAGESIZE=1000",
                                                  "<?php echo $ROOT.$PHPFOLDER; ?>functional/contact/contactListTable.php",
                                                  "innerParamsPopup",
                                                  "Retrieving Contact List ...",
                                                  "populateUsers("+index+"); showPopup();addRecipientButtons("+index+");");
          }
  }
  function addRecipientButtons(index){
          // NB! you must use jquery HTML to set innerHTML as otherwise memory leaks occur and previous param DOM tree still exists in mem before garbage collector eventually runs. It doesn't help to first set innerhtml to null either.
          var bar='<input type=\"submit\" class=\"btn btn-info btn-small\" value=\"Cancel\" onclick=\"hideParamsPopup();\" />'
                     +'<input type=\"submit\" class=\"btn btn-info btn-small\" value=\"Submit Parameters\" onclick=\"setRecipientsParams('+index+');\" />';
          $('#headerParamsPopup,#footerParamsPopup').html(bar);
  }
  function populateUsers(index) {
          params=$('#FORM_RECIPIENTS_'+index).val();
          fld=document.getElementsByName('POPUP_USERS');
          if (params!='') {
                  var arr=params.split(',');
                  for (var i=0; i<arr.length; i++) {
                          for (var j=0; j<fld.length; j++) {
                                  if (fld[j].value==arr[i]) {
                                          fld[j].checked=true;
                                          break;
                                  }
                          }
                  }
          }
  }

  function showPopup() {
    $("#paramsLayer").show();
    $("#paramDull").show();
  }

  function hideParamsPopup() {
    $("#innerParamsPopup").html("");
    $("#innerParamsPopup").empty();
    $("#innerParamsPopup").children("input").remove();
    $("div#paramsLayer").hide();
    $("#paramDull").hide();
  }

  function setRecipientsParams(index) {
          // force garbage collection in IE so no old DOM is left
          if (typeof(CollectGarbage) == "function") CollectGarbage();
          var params=convertElementToArray(document.getElementsByName('POPUP_USERS'));
          $('#FORM_RECIPIENTS_'+index).val(params);
          hideParamsPopup();

          return true;
  }

  var alreadySubmitted=false;
  function submitContentForm(index) {
          if (alreadySubmitted) {
                  return;
          }
          alreadySubmitted=true;

          var uid=document.getElementById('FORM_UID_'+index).value;
          var active=document.getElementById('FORM_A_'+index).value; // active is not passed... it is only used to establish the dmltype

          var params='';
          var dmlType='';
          if (uid=='') {
            dmlType='INSERT';
            params='DMLTYPE=INSERT';
          } else {
            if (active=="N") { //reverse, for absolute value for deletion.
              dmlType='DELETE';
              params='DMLTYPE=DELETE';
            } else {
              dmlType='UPDATE';
              params='DMLTYPE=UPDATE';
            }
          }

          params+='&UID='+uid;
          params+='&NOTIFICATIONUID='+document.getElementById('FORM_NUID_'+index).value;
          params+='&USERUIDLIST='+document.getElementById('FORM_RECIPIENTS_'+index).value;
          if (document.getElementById('FORM_V_'+index)) params+='&VALUE='+document.getElementById('FORM_V_'+index).value;
          params+='&OUTPUTTYPE='+document.getElementById('FORM_OT_'+index).value;
          params+='&DELIVERYTYPE='+document.getElementById('FORM_DT_'+index).value;
          if (document.getElementById('FORM_APS_'+index)) params+='&APS='+encodeURIComponent(document.getElementById('FORM_APS_'+index).value);

          params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
          AjaxRefreshWithResult(params,
                                '<?php echo $ROOT.$PHPFOLDER; ?>functional/SMART/notificationSubmit.php',
                                'alreadySubmitted=false; if (msgClass.type=="S") successCallback("'+dmlType+'",msgClass.identifier); else errorCallback("'+dmlType+'");',
                                'Please wait while request is processed...');
  }

  function successCallback(p_type, id) {

    //refresh the page
    showSubMsgBoxModal();
    <?php
      echo 'window.location="?PFORM_RB=' . $postPFORMRB . '&PFORM_N=' . $postPFORMN . '&PFORM_NNAME=' . $postPFORMNNAME . '";';
    ?>

  }

  function errorCallback(p_type) {
  }

  function refreshParams(APS_string,index) {
          showPopup(); // do it up here so that modal is activated
          // NB! you must use jquery HTML to set innerHTML as otherwise memory leaks occur and previous param DOM tree still exists in mem before garbage collector eventually runs. It doesn't help to first set innerhtml to null either.
          var bar='<input type=\"submit\" class=\"btn btn-info btn-small\" value=\"Cancel\" onclick=\"hideParamsPopup();\" />'
                     +'<input type=\"submit\" class=\"btn btn-info btn-small\" value=\"Submit Parameters\" onclick=\"setParams('+index+');\" />';
          $('#headerParamsPopup,#footerParamsPopup').html(bar);
          // do not allow filters because you will lose your user selection ; we also dont need APS_string to be passed as it is looked up in paramterBase.php, but havent time to check
          AjaxRefresh("GENERALUSAGE=Y&TYPEID=NOTIFICATION&OBJUID="+document.getElementById('PFORM_N').value+"&"+APS_string+"&"+document.getElementById('FORM_APS_'+index).value,
                                          "<?php echo $ROOT.$PHPFOLDER; ?>functional/reports/parameterBase.php",
                                          "innerParamsPopup",
                                          "Retrieving Parameter Options ...",
                                          ""); // populates values by passed parameter APS_string
  }

  function setParams(index) {
          // force garbage collection in IE so no old DOM is left
          if (typeof(CollectGarbage) == "function") CollectGarbage();
          if (!setparamBaseValues()) return false; // function in parameterBase.php - populate the hidden fields with their values, check req vals.
          document.getElementById('FORM_APS_'+index).value=getParamsURL();
          hideParamsPopup();

          return true;
  }

</SCRIPT>

</HEAD>
<BODY>
<div align="right" style="padding:10px 20px;">

<?php

//please note notice.
$NoteHTML = "<span style='font-size:1.2em'><strong>PLEASE NOTE</strong></span><br />
	  SMS distributions are ONLY sent during the hours of 8am and 4pm, Monday to Friday.<br><br>
	  SMS's outside of these working hours are converted to emails instead.<br><br>
	  This is because some notifications are triggered by events that can occur repeatedly, requesting your intervention, which often cannot be solved outside of working
	  hours as the relevant staff are not available. RT wishes to reduce the inconvenience of being sms'd repeatedly during your off-time.
	  </SPAN>";
echo '<a  class="btn btn-danger" href="javascript:;" onClick="parent.popBox(\'' . htmlentities(str_replace(array("\r","\n","'"), array('','',"\'"),$NoteHTML)) . '\', \'warn\',700)">PLEASE NOTE</a>';

?>

</div>
<div align="center">

<?php


// the popup to choose params ... for some reason this must be BEFORE the fieldsets otherwise it crashes on ajax return ??!?!?!
// this has extra divs enclosing the main div because when u submit the filter it will otherwise lose the added headers.

?>
<!-- the popup to choose params -->
<DIV id="paramDull" style='position:fixed;top:0px;left:0px;opacity:0.35;filter:alpha(opacity=35);background:lightskyblue;display:none;height:100%;width:100%;z-index:-10'></DIV>
<DIV id='paramsLayer' style='display:none;z-index=20;position:absolute;top:50%;margin-top:-200px;left:0px;width:100%;' align="center">
  <DIV id='paramsPopup' style='overflow:auto;border:5px solid #1e4272;background-color:white;height:400px;padding:10px;width:750px;' >
    <DIV id='headerParamsPopup'></DIV>
    <DIV id='innerParamsPopup'></DIV>
    <DIV id='footerParamsPopup'></DIV>
  </DIV>
</DIV>
<?php



//notification Tabs
echo '<div id="tracking-tabs" class="tracking-tabs-container notificationTabs">'; //append menu id for multiple menu tabs on the same screen.
echo '<ul>';


$bIDAO = new BIDAO($dbConn);
$mfN = $bIDAO->getNotificationTypes("uid");
// get unique systems
$sysArr = array();
foreach ($mfN as $key=>$row) {
  $sysArr[$row["system_category"]]="";
}


    foreach ($sysArr as $key=>$s) {
      echo '<li class="' . (($key==$postPFORMRB) ? ' active ' : '') . '">' .
              '<a href="javascript:;" class="notificationTabItem" onClick="jQuery(\'#notificationOptions\').show();jQuery(\'#notificationOptions div\').hide();jQuery(\'#notification_' . str_replace(' ','',$key) . '\').show();" >' . $key . '</a>' .
           '</li>';
    }
    //retain a form value for backwards compat.
    echo '<input type="hidden" value="'.$postPFORMRB.'" name="PFORM_RB" id="PFORM_RB">';


  echo  '</ul>';
echo '</div>';

echo '<div id="notificationDiv">';
  echo '<div id="notificationOptions" ' . (($postPFORMN!==false)?'style="display:none"':'') . ' >';

  foreach(array_keys($sysArr) as $sysCategory){
    echo '<div id="notification_' . str_replace(' ','',$sysCategory) . '" ' . (($postPFORMRB!=$sysCategory)?'style="display:none"':'') . '>';
    foreach ($mfN as $row) {
      if ($row['system_category']==$sysCategory) {
        echo '<a href="javascript:;" onClick="showSubMsgBoxModal();window.location=\'?PFORM_RB=' . $sysCategory . '&PFORM_N=' . $row["uid"] . '&PFORM_NNAME=' . $row["description"] . '\'" class="btn notificationItemNormal ' . (($row["uid"]==$postPFORMN) ? ' active ' : ' btn-primary notificationItem ') . '">' .
                $row["description"] .
              '</a>';
      }
    }
    echo '</div>';
  }
  //retain a form value for backwards compat.
  echo '<input type="hidden" value="'.$postPFORMN.'" name="PFORM_N" id="PFORM_N">';
  echo '</div>';
  echo '<div align="right" class="menuTab" ><a href="javascript:;" onClick="$(\'#notificationOptions\').slideToggle();" class="link" title="Hide/Show Notification Menu">Menu</a></div>';
echo '</div>';


// don't go any further until parameters chosen
if($postPFORMN == false){
  return;
}



function convertToDD ($list, $id, $default) {
  $listArr=explode(",",$list);
  echo "<SELECT id='{$id}' >";
  foreach ($listArr as $row) {
          if ($default==$row) echo "<OPTION value='{$row}' SELECTED >{$row}</OPTION>";
          else echo "<OPTION value='{$row}' >{$row}</OPTION>";
  }
  echo "</SELECT>";
}


echo '<h2 style="color:#047;margin-bottom:5px;">'.$postPFORMNNAME.'</h2>';
echo '<div style="width:500px;color:#555;">' . $mfN[$postPFORMN]["value_description"] . '</div>';

$mfNR = $bIDAO->getNotificationRecipients($principalId, $postPFORMN);

if (($postPFORMRB=="EXPORT") && ($staffUser!="Y")) {
	echo "Add New is not available to non staff members for EXPORT<BR>";
} else {
	echo "<BR><input type='button' class=\"btn btn-success\" value='Create New' onclick='showSubMsgBoxModal(); document.getElementById(\"pformaction\").submit();' /><BR><BR>";
	echo "<FORM id='pformaction' name='pform' action='{$_SERVER["PHP_SELF"]}' method='get' >
			<INPUT type='hidden' name='action' value='INSERT' />
			<INPUT type='hidden' name='PFORM_RB' value='{$postPFORMRB}' />
                        <INPUT type='hidden' name='PFORM_NNAME' value='{$postPFORMNNAME}' />
			<INPUT type='hidden' name='PFORM_N' value='{$postPFORMN}' />
		  </FORM>";
}

if ((sizeof($mfNR)==0) && ($postDMLTYPE!="INSERT")) {
	echo "No Notifications Configured";
	return;
}

echo "<BR><SPAN style='".FONT_UNOBTRUSIVE_INFO."'>{$mfN[$postPFORMN]["message"]}</SPAN><BR>";


$distributionDAO = new DistributionDAO($dbConn);
echo "<FIELDSET>
	  <LEGEND style='color:#303030;'>Notification Details</LEGEND>
          <BR>";


$i=0;
if ($postDMLTYPE=="INSERT") {

	// first output hidden fields for submit

	echo "<INPUT type='hidden' id='FORM_UID_{$i}' name='FORM_UID_{$i}' value='' />";
	echo "<INPUT type='hidden' id='FORM_NUID_{$i}' name='FORM_NUID_{$i}' value='{$mfN[$postPFORMN]["uid"]}' />";
        echo "<input type='hidden' id='FORM_A_{$i}' value='Y'>";  //html comment this row out for submit to work.

        echo "<TABLE class='tblReset' style='border:0;width:800px;'>";
	echo "<TR class='even'>";
		echo "<TD>Description: </TD>";
		echo "<TD style='font-weight:bold;'>{$mfN[$postPFORMN]["description"]}</TD>";
	echo "</TR>";

	if ($mfN[$postPFORMN]["value_required"]=="Y") {
		echo "<TR class='even'>";
			echo "<TD>{$mfN[$postPFORMN]["value_description"]}</TD>";
			if ($mfN[$postPFORMN]["values_allowed"]!="") {
				echo "<TD>";
				convertToDD ($mfN[$postPFORMN]["values_allowed"], "FORM_V_{$i}", $mfN[$postPFORMN]["value_default"]);
				echo "</TD>";
			} else {
				echo "<TD><input type='text' id='FORM_V_{$i}' name='FORM_V_{$i}' value='{$mfN[$postPFORMN]["value_default"]}' size='50' ></TD>";
			}
		echo "</TR>";
	}
	echo "<TR class='even'>";
		if ($mfN[$postPFORMN]["recipient_type"]==NRT_USERS) {
			echo "<TD>Recipients: </TD>";
			echo "<TD><input type='button' class='btn btn-info btn-small' value='Select Users' onclick='refreshRecipients(\"U\",{$i});' > <input type='text' id='FORM_RECIPIENTS_{$i}' name='FORM_RECIPIENTS_{$i}' value='' size='50' READONLY DISABLED></TD>";
		} else {
			echo "<TD>Contacts: </TD>";
			echo "<TD><input type='button' class='btn btn-info btn-small' value='Select Contacts' onclick='refreshRecipients(\"C\",{$i});' > <input type='text' id='FORM_RECIPIENTS_{$i}' name='FORM_RECIPIENTS_{$i}' value='' size='50' READONLY DISABLED> </TD>";
		}
	echo "</TR>";
	echo "<TR class='even'>";
		echo "<TD>Output Type: </TD>";
		echo "<TD>";
		BasicSelectElement::getBroadcastOutputTypesDD("FORM_OT_{$i}","","N","N",null,null,null,$mfN[$postPFORMN]["output_types_allowed"]);
		echo "</TD>";
	echo "</TR>";
	echo "<TR class='even'>";
		echo "<TD>Delivery Type: </TD>";
		echo "<TD>";
		BasicSelectElement::getBroadcastDeliveryTypesDD("FORM_DT_{$i}","","N","N",null,null,null,$mfN[$postPFORMN]["delivery_types_allowed"]);
		echo "</TD>";
	echo "</TR>";

	if ($mfN[$postPFORMN]["additional_parameter_string"]!="") {
		echo "<TR class='even'>";
			echo "<TD>Parameters</TD>";
			echo "<TD>";
                        echo "<input class='btn btn-info btn-small' type='submit' value='Specify Params' onclick='refreshParams(\"{$mfN[$postPFORMN]["additional_parameter_string"]}\",{$i});' style='float:left;'/>";
			echo "<textarea id='FORM_APS_{$i}' name='FORM_APS_{$i}' type='text' rows='1' cols='50' DISABLED ></TEXTAREA> ";
			echo "</TD>";
		echo "</TR>";
	}
	echo "</TR>";
        echo "</TABLE><BR>";
        echo "<input type='button' class='btn btn-primary' value='Submit New' onclick='submitContentForm({$i});' />";


} else {

	foreach ($mfNR as $row) {


              // You must be RT staff to configure exports
              if (($row["system_category"]=="EXPORT") && ($staffUser!="Y")) {
                      echo "Only RT Staff can view/modify settings for EXPORT";
                      return;
              }

              // get the distributions
              $mfD=$distributionDAO->getDistributions($row["distribution_source_identifier"]);

              // first output hidden fields for submit
              echo "<INPUT type='hidden' id='FORM_UID_{$i}' name='FORM_UID_{$i}' value='{$row["uid"]}' />";
              echo "<INPUT type='hidden' id='FORM_NUID_{$i}' name='FORM_NUID_{$i}' value='{$row["notification_uid"]}' />";
              echo "<input type='hidden' name='FORM_A_{$i}' id='FORM_A_{$i}' value='Y' />"; // must always be shown as checked... if not checked, then delete

              echo "<TABLE class='tblReset' style='border:0;width:800px;line-height:22px;'>";

		if ($i % 2) $class="odd"; else $class="even";
		echo "<TR class='{$class}'>";
			echo "<TD>Description: </TD>";
			echo "<TD><strong>{$mfN[$postPFORMN]["description"]}</strong> <span style='float:right;color:#555;'>{$row["uid"]}</span></TD>";
		echo "</TR>";

		if ($mfN[$postPFORMN]["value_required"]=="Y") {
			echo "<TR class='{$class}'>";
				echo "<TD>{$mfN[$postPFORMN]["value_description"]}</TD>";
				if ($mfN[$postPFORMN]["values_allowed"]!="") {
					echo "<TD>";
					convertToDD ($mfN[$postPFORMN]["values_allowed"], "FORM_V_{$i}", $row["value"]);
					echo "</TD>";
				} else {
					echo "<TD><input type='text' id='FORM_V_{$i}' name='FORM_V_{$i}' value='{$row["value"]}' size='50' ></TD>";
				}
			echo "</TR>";
		}
		echo "<TR class='{$class}'>";
			if ($mfN[$postPFORMN]["recipient_type"]==NRT_USERS) {
				echo "<TD>Recipients: </TD>";
				echo "<TD><input type='button' class='btn btn-info btn-small' value='Select Users' onclick='refreshRecipients(\"U\",{$i});' > <input type='text' id='FORM_RECIPIENTS_{$i}' name='FORM_RECIPIENTS_{$i}' value='{$row["user_uid_list"]}' size='50' READONLY DISABLED></TD>";
			} else {
				echo "<TD>Contacts: </TD>";
				echo "<TD><input type='button' class='btn btn-info btn-small' value='Select Contacts' onclick='refreshRecipients(\"C\",{$i});' > <input type='text' id='FORM_RECIPIENTS_{$i}' name='FORM_RECIPIENTS_{$i}' value='{$row["user_uid_list"]}' size='50' READONLY DISABLED> </TD>";
			}
		echo "</TR>";
		echo "<TR class='{$class}'>";
			echo "<TD>Output Type: </TD>";
			echo "<TD>";
			BasicSelectElement::getBroadcastOutputTypesDD("FORM_OT_{$i}",$row["output_type"],"N","N",null,null,null,$mfN[$postPFORMN]["output_types_allowed"]);
			echo "</TD>";
		echo "</TR>";
		echo "<TR class='{$class}'>";
			echo "<TD>Delivery Type: </TD>";
			echo "<TD>";
			BasicSelectElement::getBroadcastDeliveryTypesDD("FORM_DT_{$i}",$row["delivery_type"],"N","N",null,null,null,$mfN[$postPFORMN]["delivery_types_allowed"]);
			echo "</TD>";
		echo "</TR>";

		if ($mfN[$postPFORMN]["additional_parameter_string"]!="") {
			echo "<TR class='{$class}'>";
				echo "<TD>Parameters</TD>";
				echo "<TD>";
                                echo "<input class='btn btn-info btn-small' type='submit' value='Specify Params' onclick='refreshParams(\"{$mfN[$postPFORMN]["additional_parameter_string"]}\",{$i});' style='float:left;' />";
				echo "<textarea id='FORM_APS_{$i}' name='FORM_APS_{$i}' type='text' rows='1' cols='50' DISABLED >{$row["additional_parameter_string"]}</TEXTAREA> ";
				echo "</TD>";
			echo "</TR>";
		}


                /*-------------------------------------------
                 *      status and run information
                 */

		echo "<TR class='{$class}'>";
			echo "<TD>Service Status: </TD>";
			echo "<TD>".GUICommonUtils::translateStatus($row["service_status"])."</TD>";
		echo "</TR>";
		echo "<TR class='{$class}'>";
			echo "<TD>Last Run Date: </TD>";
			echo "<TD>{$row["run_date"]}</TD>";
		echo "</TR>";
		echo "<TR class='{$class}'>";
			echo "<TD>Last Status Message: </TD>";
			echo "<TD><div style='color:red;font-weight:bold;'>{$row["status_msg"]}</div></TD>";
		echo "</TR>";
		echo "<TR class='{$class}'>";
			echo "<TD>Error Count:<br><span class='info'>suspends after 10</span></TD>";
			echo "<TD>{$row["error_count"]}</TD>";
		echo "</TR>";
                /*-------------------------------------------*/


		echo "<TR class='{$class}'>";
			echo "<TD>Distributions Log:<br><span class='info'>for last run</span></TD>";

			if (sizeof($mfD)==0) {
				echo "<TD><span class='info'>no logs available</span></TD>";
			} else {
                          echo "<TD style='padding:0px;'>";
				echo "<TABLE width='100%' style='font-family:Calibri, Verdana, Ariel, sans-serif;font-size:12px;border:0px;'>";
				foreach ($mfD as $key=>$d) {
					if ($key==0) {
						echo "<TR><TH>Run Date</TH><TH>Run Msg</TH><TH>Status</TH><TH>Recipient Address</TH></TR>";
					}
                                        $isFTPAddress = @unserialize($d["addr"]);
					echo "<TR><TD>{$d["run_date"]}</TD><TD>{$d["run_msg"]}</TD><TD>".(GUICommonUtils::translateStatus($d["status"]))."</TD><TD>" . (($isFTPAddress)?'<i>FTP ADDRESS</i>':$d["addr"]) . "</TD></TR>";
				}
				echo "</TABLE>";
                          echo "</TD>";
			}

		echo "</TR>";

                if($staffUser=="Y"){
                  echo "<TR class='{$class}'>";

                    echo "<TD>Admin Operations:</TD>
                            <TD>";
                    echo "<a href='".$ROOT . $PHPFOLDER ."functional/SMART/distributionViewer.php?nuid=".$row["uid"]."' target='_blank' class='btn btn-small'>Open Distribution</a>";

                    //extract errors.
                    if (in_array($postPFORMN,array(NT_DAILY_EXTRACT_CUSTOM, NT_DAILY_EXTRACT_ALTCUSTOM1,NT_DAILY_EXTRACT_ALTCUSTOM2,NT_DAILY_EXTRACT_ALTCUSTOM3,NT_DAILY_EXTRACT_ALTCUSTOM4))){
                      include_once($ROOT.$PHPFOLDER."libs/EncryptionClass.php");
                      $encryption = new EncryptionClass();
                      $epid = $encryption->encryptUIDValue($principalId, 0, 6); //error is by principal not notification uid.
                      echo " <a href='".$ROOT . $PHPFOLDER ."functional/extracts/errorManagement.php?p=".$epid."' target='_blank' class='btn btn-small'>Open Errors</a>";
                    }

                    echo "</TD>";
                  echo "</TR>";
                }


                echo "</TR></TABLE><BR>";
		echo "<input type='button' class='btn btn-primary' value='Submit' onclick='submitContentForm({$i});' /> ";
                echo "<input type='button' class='btn btn-danger' value='Delete' onclick=\"jQuery('#FORM_A_{$i}').val('N');submitContentForm({$i});\" />";
                echo "<BR><BR><BR>";

		$i++;
	}

}

echo "</FIELDSET>";


?>
</div>
</BODY>
</HTML>
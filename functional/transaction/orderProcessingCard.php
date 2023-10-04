<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/BIDAO.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");

if (!isset($_SESSION)) session_start() ;
$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$principalName = $_SESSION['principal_name'] ;

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

if (isset($_GET['DOCMASTID'])) $postDOCMASTID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_GET['DOCMASTID']));
else if (isset($_POST['DOCMASTID'])) $postDOCMASTID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DOCMASTID']));
else $postDOCMASTID="";

$transactionDAO = new TransactionDAO($dbConn);
$bIDAO = new BIDAO($dbConn);
// this also doubles as the security check because this sql joins on user_principal_depot
$mfDM = $transactionDAO->getDocumentMasterItem($userId,$postDOCMASTID);
$mfOPC = $transactionDAO->getOrderProcessingCardExtraFields($postDOCMASTID);
$mfEE = $bIDAO->getDocumentElectronicInterfaces($principalId, $postDOCMASTID);

if (sizeof($mfDM)==0) {
	echo "You do not have access to this information, or document master does not exist.";
	return;
}

include_once($ROOT.$PHPFOLDER."DAO/BIDAO.php");
$bIDAO = new BIDAO($dbConn);
$extracts = $bIDAO->getSmartEventsByTypeData(SE_EXTRACT, false, $postDOCMASTID);


?>
<HTML>
<HEAD>

  <style type="text/css">
    .tabHead {font-weight:bold; color: #047;padding:3px 2px 3px 5px; }
    td {text-align:left;padding:3px 2px 3px 5px;}
  </style>


</HEAD>
<BODY>

  <div style='color:#444; margin:15px 0px; '>
<TABLE style='border-style:double;background-color:#fff;font-size:11px;line-height:18px;'>
<TR>
<TD colspan="2" style='height:25px;text-align:center; background-color:#047; color:white; font-weight:bold;font-size:11px;'>Order Processing Information</TD>
</TR>

<TR>
	<TD class="tabHead" >Principal:</TD>
	<TD style='font-weight:normal; '><?php echo $mfDM[0]['principal_name'] .  ' (' . $mfDM[0]['principal_uid'] . ') '; ?></TD>
</TR>
<TR  style="background-color:aliceBlue;">
	<TD class="tabHead">Depot:</TD>
	<TD style='font-weight:normal; '><?php echo $mfDM[0]['depot_name'] . ' (' . $mfDM[0]['depot_uid'] . ')'; ?></TD>
</TR>
<TR>
	<TD class="tabHead">Document Number:</TD>
	<TD style='font-weight:normal; '><?php echo $mfDM[0]['document_number']; ?></TD>
</TR>
<TR  style="background-color:aliceBlue;">
	<TD class="tabHead">Document Type:</TD>
	<TD style='font-weight:normal; '><?php echo $mfDM[0]['document_type_description']; ?></TD>
</TR>
<TR>
	<TD class="tabHead">Processed Date:</TD>
	<TD style='font-weight:normal; '><?php echo $mfDM[0]['processed_date']; ?></TD>
</TR>
<TR  style="background-color:aliceBlue;">
	<TD class="tabHead">Processed Time:</TD>
	<TD style='font-weight:normal; '><?php echo $mfDM[0]['processed_time']; ?> <small>GMT + 0:00</small></TD>
</TR>
<TR>
	<TD class="tabHead">Merged Date:</TD>
	<TD style='font-weight:normal; '><?php echo $mfDM[0]['merged_date']; ?></TD>
</TR>
<TR  style="background-color:aliceBlue;">
	<TD class="tabHead">Merged Time:</TD>
	<TD style='font-weight:normal; '><?php echo $mfDM[0]['merged_time']; ?> <small>GMT + 0:00</small></TD>
</TR>
<TR>
	<TD class="tabHead">Incoming File:</TD>
	<TD style='font-weight:normal; '>
          <?php

            if (($_SESSION["staff_user"]=="Y") && !empty($mfDM[0]['file_log_uid']) && (strtoupper(basename($mfDM[0]['file_log_file_name'] )) ==  strtoupper($mfDM[0]['incoming_file']))) {
                    echo "<a href='#' style='color:#DF0101' onclick='window.open(\"functional/general/downloadFile.php?TYPE=EDI&HTMLOUTPUT=N&UID=" . $mfDM[0]['file_log_uid'] ."\",\"EDIDownloadFile\",\"scrollbars=yes,width=600,height=400,resizable=yes\");' >".
                             $mfDM[0]['incoming_file'] .
                             "</a>";
            } else echo $mfDM[0]['incoming_file'];

          ?>
        </TD>
</TR>
<TR  style="background-color:aliceBlue;">
	<TD class="tabHead">Confirmation File:</TD>
	<TD style='font-weight:normal; '><?php echo $mfDM[0]['confirmation_file']; ?></TD>
</TR>
<TR>
	<TD class="tabHead">Out File:</TD>
	<TD style='font-weight:normal; '>
        <?php
		if (($_SESSION["staff_user"]=="Y") && (basename($mfDM[0]['dop_file'])!="")) {
			echo "<a href='#' style='color:#DF0101' onclick='window.open(\"functional/general/downloadFile.php?TYPE=DEPOTEXPORT&UID=".basename($mfDM[0]['order_sequence_no'])."\",\"DEPOTExportFile\",\"scrollbars=yes,width=500,height=400,resizable=yes\");' >".
				 basename($mfDM[0]['dop_file']).
				 "</a>";
		} else echo basename($mfDM[0]['dop_file']);
	?>
        </TD>
</TR>


<TR style="background-color:aliceBlue;">
  <TD class="tabHead" >Extracted Date:</TD>
  <TD style='font-weight:normal; '>
    <?php
    if(isset($extracts[0]['created_date'])){
      echo date('Y-m-d', strtotime($extracts[0]['created_date']));
    }
    ?>
  </TD>
</TR>
<TR  >
  <TD class="tabHead">Extracted File:</TD>
  <TD style='font-weight:normal; '>
    <?php

    if(!empty($extracts[0]['general_reference_1'])){

      if(CommonUtils::isStaffUser()){
        echo '<a href="javascript:;" style="color:#DF0101" onclick="window.open(\'functional/general/downloadFile.php?TYPE=EXTRACTFILE&UID=' . $extracts[0]['uid'] . '\', \'DEPOTExportFile\', \'scrollbars=yes,width=500,height=400\');" >' . $extracts[0]['general_reference_1'] . '</a>';
      } else {
        echo $extracts[0]['general_reference_1'];
      }

    }

    ?></TD>
</TR>

<TR  style="background-color:aliceBlue;">
	<TD class="tabHead">Last Update:</TD>
	<TD style='font-weight:normal; '><?php echo $mfDM[0]['last_updated']; ?></TD>
</TR>
<TR>
	<TD class="tabHead">Captured By:</TD>
	<TD style='font-weight:normal; '><?php echo !empty($mfDM[0]['full_name']) ? ($mfDM[0]['full_name'] . ' : '.$mfDM[0]['user_uid']) : (!empty($mfOPC[0]['full_name'])?$mfOPC[0]['full_name']:($mfDM[0]['captured_by'])); ?></TD>
</TR>


<!--<TR  style="background-color:aliceBlue;">
	<TD class="tabHead">WOR file:</TD>
	<TD style='font-weight:normal; '>
	<?php
		if (($_SESSION["staff_user"]=="Y") && (basename($mfDM[0]['edi_filename'])!="")) {
			echo "<a href='#' style='color:#DF0101' onclick='window.open(\"functional/general/downloadFile.php?TYPE=ORDER&UID=".basename($mfDM[0]['order_sequence_no'])."\",\"WORDownloadFile\",\"scrollbars=yes,width=500,height=400\");' >".
				 basename($mfDM[0]['edi_filename']).
				 "</a>";
		} else echo basename($mfDM[0]['edi_filename']);
	?></TD>
</TR>-->

<TR style="background-color:aliceBlue;">
	<TD class="tabHead">Order Sequence No:</TD>
	<TD style='font-weight:normal; '><?php echo $mfDM[0]['order_sequence_no']; ?></TD>
</TR>
<TR>
  <TD class="tabHead">Invoice uploaded to PnP:</TD>
  <TD style='font-weight:normal; '><?php echo $mfEE[0]["pnp_inv_status"]." : ".$mfEE[0]["pnp_inv_status_msg"]; ?>
  </TD>
</TR>
<TR style="background-color:aliceBlue;">
  <TD class="tabHead">Additional Details:</TD>
  <TD style='font-weight:normal; '><?php echo $mfOPC[0]["additional_details"]; ?>
  </TD>
</TR>
<TR>
  <TD class="tabHead">Additional Type:</TD>
  <TD style='font-weight:normal; '>
  <?php
    if ($mfDM[0]['document_type_uid']==DT_CREDITNOTE) {
      if ($mfOPC[0]['additional_type']=="C") echo "Charge";
      else if (trim($mfOPC[0]['additional_type'])=="NC") echo "No Charge";
      else echo $mfOPC[0]['additional_type'];
    } else echo $mfOPC[0]['additional_type'];
  ?>
  </TD>
</TR>
<TR style="background-color:aliceBlue;">
  <TD class="tabHead">Document Audit Log:</TD>
  <TD style='font-weight:normal; '><?php
   echo "<a href='javascript:;' onClick=\"window.open('functional/transaction/documentAuditCard.php?DOCMASTID={$postDOCMASTID}','myOPC','scrollbars=yes,width=400,height=550,resizable=yes');\" style='border:none; text-decoration:none;'><img src='images/audit_log.gif' style='border:none; text-decoration:none; width:18px; height:18px;' title='View Audit Log for this order' /></a>";
   ?>
  </TD>
</TR>
<TR style="background-color:aliceBlue;">
  <TD class="tabHead">Queue for Extract 1:</TD>
  <TD style='font-weight:normal; '><?php
   $ACTIONTYPE="REQUEUEINVEXT";
   $NOTIFICATIONID =NT_DAILY_EXTRACT_CUSTOM;
   $KEYACTION=md5($postDOCMASTID.$mfDM[0]["principal_uid"].$ACTIONTYPE);
   echo "<a href='javascript:;' onClick=\"window.open('functional/transaction/viewTrackingIdentifiersAction.php?DOCMASTID={$postDOCMASTID}&ACTIONTYPE={$ACTIONTYPE}&NOTIFICATIONID={$NOTIFICATIONID}&KEYACTION={$KEYACTION}','myIAction','scrollbars=yes,width=400,height=550,resizable=yes');\" style='border:none; text-decoration:none;'>Requeue</a>";
   ?>
  </TD>
</TR>
<TR style="background-color:aliceBlue;">
  <TD class="tabHead">Queue for Extract 2:</TD>
  <TD style='font-weight:normal; '><?php
   $ACTIONTYPE="REQUEUEINVEXT";
   $NOTIFICATIONID=NT_DAILY_EXTRACT_ALTCUSTOM1;
   $KEYACTION=md5($postDOCMASTID.$mfDM[0]["principal_uid"].$ACTIONTYPE);
   echo "<a href='javascript:;' onClick=\"window.open('functional/transaction/viewTrackingIdentifiersAction.php?DOCMASTID={$postDOCMASTID}&ACTIONTYPE={$ACTIONTYPE}&NOTIFICATIONID={$NOTIFICATIONID}&KEYACTION={$KEYACTION}','myIAction','scrollbars=yes,width=400,height=550,resizable=yes');\" style='border:none; text-decoration:none;'>Requeue 2</a>";
   ?>
  </TD>
</TR>
<TR style="background-color:aliceBlue;">
  <TD class="tabHead">Queue for Extract 3:</TD>
  <TD style='font-weight:normal; '><?php
   $ACTIONTYPE="REQUEUEINVEXT";
   $NOTIFICATIONID=NT_DAILY_EXTRACT_ALTCUSTOM2;
   $KEYACTION=md5($postDOCMASTID.$mfDM[0]["principal_uid"].$ACTIONTYPE);
   echo "<a href='javascript:;' onClick=\"window.open('functional/transaction/viewTrackingIdentifiersAction.php?DOCMASTID={$postDOCMASTID}&ACTIONTYPE={$ACTIONTYPE}&NOTIFICATIONID={$NOTIFICATIONID}&KEYACTION={$KEYACTION}','myIAction','scrollbars=yes,width=400,height=550,resizable=yes');\" style='border:none; text-decoration:none;'>Requeue 3</a>";
   ?>
  </TD>
</TR>
<TR style="background-color:aliceBlue;">
  <TD class="tabHead">Queue for Extract 4:</TD>
  <TD style='font-weight:normal; '><?php
   $ACTIONTYPE="REQUEUEINVEXT";
   $NOTIFICATIONID=NT_DAILY_EXTRACT_ALTCUSTOM3;
   $KEYACTION=md5($postDOCMASTID.$mfDM[0]["principal_uid"].$ACTIONTYPE);
   echo "<a href='javascript:;' onClick=\"window.open('functional/transaction/viewTrackingIdentifiersAction.php?DOCMASTID={$postDOCMASTID}&ACTIONTYPE={$ACTIONTYPE}&NOTIFICATIONID={$NOTIFICATIONID}&KEYACTION={$KEYACTION}','myIAction','scrollbars=yes,width=400,height=550,resizable=yes');\" style='border:none; text-decoration:none;'>Requeue 4</a>";
   ?>
  </TD>
</TR>
<TR style="background-color:aliceBlue;">
  <TD class="tabHead">Queue for Extract 5:</TD>
  <TD style='font-weight:normal; '><?php
   $ACTIONTYPE="REQUEUEINVEXT";
   $NOTIFICATIONID=NT_DAILY_EXTRACT_ALTCUSTOM4;
   $KEYACTION=md5($postDOCMASTID.$mfDM[0]["principal_uid"].$ACTIONTYPE);
   echo "<a href='javascript:;' onClick=\"window.open('functional/transaction/viewTrackingIdentifiersAction.php?DOCMASTID={$postDOCMASTID}&ACTIONTYPE={$ACTIONTYPE}&NOTIFICATIONID={$NOTIFICATIONID}&KEYACTION={$KEYACTION}','myIAction','scrollbars=yes,width=400,height=550,resizable=yes');\" style='border:none; text-decoration:none;'>Requeue 5</a>";
   ?>
  </TD>
</TR>


<!-- footer -->
<TR>
<TD colspan="2" style='text-align:center; color:grey;height:30px; font-weight:normal;'>
  <?php
    echo gmdate('d/m/Y H:i:s') . ' - UID: ' . $postDOCMASTID;
  ?>
</TD>
</TR>
</TABLE>
  </div>

</BODY>

</HTML>

<?php
$dbConn->dbClose();
?>
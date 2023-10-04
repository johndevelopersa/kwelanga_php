<?php
/* NB:
 * This should only be accessible by a depot user from a depot principal
 */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."elements/basicInputElement.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
CommonUtils::getSystemConventions();

if (!isset($_SESSION)) session_start() ;
$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$skipInPickStage = ((isset($_SESSION['skip_inpick_stage']))?$_SESSION['skip_inpick_stage']:"N");

$resultStatus = 0;
$mfTArr = array();
$mfTDArr = array();


$dbConn = new dbConnect();
$dbConn->dbConnection();

$adminDAO = new AdministrationDAO($dbConn);

if (!CommonUtils::isDepotUser()) {

  $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_MANAGE_QUOTATION);
  if (!$hasRole) {
    echo "Do do not have permissions to manage quotations";
    return;
  }

}


//system configure.

$scrPref = $adminDAO->getAllFieldPreferences($principalId, $systemId, 'TRACKING');


if (isset($_GET['DOCMASTID'])) $postDOCMASTID = (htmlspecialchars($_GET['DOCMASTID']));
else if (isset($_POST['DOCMASTID'])) $postDOCMASTID = (htmlspecialchars($_POST['DOCMASTID']));
else $postDOCMASTID = "";
$postBULKACTION = (isset($_GET['BULKACTION']) && $_GET['BULKACTION'] == 1) ? (true) : false; //BULK ACTIONS FOR DOCMATARR


if($postBULKACTION){


  /* --------------------- *
   *    BULK ACTIONS
   * --------------------- */
  $postDOCMASTARR = (isset($_GET['DOCMASTARR'])) ? ($_GET['DOCMASTARR']) : false; //BULK ACTIONS FOR DOCMATARR

  if(!$postDOCMASTARR || count($postDOCMASTARR)==0){
    echo "ERROR - No documents have been selected!";
    return;
  } else {

    foreach($postDOCMASTARR as $postDOCMASTID){

      $result = getDocument($postDOCMASTID);
      if($result->type != FLAG_ERRORTO_SUCCESS){
        echo $result->description;
        return;
      } else {
        if($resultStatus!=0 && $result->identifier != $resultStatus){
          echo "ERROR - Documents are not all the same status!";
          return;
        }
        $resultStatus = $result->identifier;  //document status
        $mfTArr[] = $result->object['H'];
        $mfTDArr[] = $result->object['D'];
      }

    }
  }

} else {


  /* --------------------- *
   *    SINGLE ACTIONS
   * --------------------- */
  $result = ((!CommonUtils::isDepotUser())?getPrincipalDocument($postDOCMASTID):getDocument($postDOCMASTID));

  if($result->type != FLAG_ERRORTO_SUCCESS){
    echo $result->description;
    return;
  } else {
    $resultStatus = $result->identifier;  //document status
    
    if (!CommonUtils::isDepotUser()) {
      $mfTArr[] = $result->object;
      $mfTDArr[] = $result->object;
    } else {
      $mfTArr[] = $result->object['H'];
      $mfTDArr[] = $result->object['D'];
    }
  }

}
  /* --------------------- *
   *    RENDER : DISPLAY
   * --------------------- */

echo "<table class='tableReset' width='100%'>";

if (!CommonUtils::isDepotUser()) {

  if ($resultStatus == DST_UNACCEPTED) {

    renderDocumentSingle($mfTArr);

    echo "<tr><td width='150'>Change Status to :</td><td>";
    BasicInputElement::getCSS3RadioHorizontal("MD_STATUS","UNACCEPTED,ACCEPTED,CANCEL,AMEND","{$resultStatus},A,C,AMEND-USE-CAPTURE",$resultStatus,$disabled = false);
    echo "</td></tr>";

  } else if ($resultStatus == DST_ACCEPTED) {

    renderDocumentSingle($mfTArr);
    
    echo "<tr><td width='150'>Change Status to :</td><td>";

    BasicInputElement::getCSS3RadioHorizontal("MD_STATUS",
                         strtoupper(GUICommonUtils::translateDocumentStatusType($resultStatus)). ",AMEND,CANCEL," . strtoupper(SNC::status_invoice),
                         "{$resultStatus}.,AMEND-USE-CAPTURE,C,I",
                          $resultStatus,
                          $disabled = false,
                          "mdStatusChange();");
//
//    echo "<tr><td width='150'>Change Status to :</td><td>";
//    BasicInputElement::getCSS3RadioHorizontal("MD_STATUS","ACCEPTED,AMEND,CANCEL,INVOICE","{$resultStatus},AMEND-USE-CAPTURE,C,I",$resultStatus,$disabled = false, "mdStatusChange();"
//     );

    echo "<TR ".GUICommonUtils::showHideField($scrPref,'REPCODE',$f,false).">";
    echo "<TD>Over Ride Sales Code :</TD>";
    echo "<TD>";
  
    echo "<div class='select'>";
    BasicSelectElement::getDocumentRepCodes("REPCODE","","N","N","",null,null,$dbConn,$principalAliasId);
    echo "</div>";

    echo "</td></tr>";

    echo "<TR ".GUICommonUtils::showHideField($scrPref,'TRACKINGNUMBER',$f,false).">";
    echo "<TD>Required Invoice Date :</TD>";
    echo "<TD>";
    echo "<div class='select'>";
    DatePickerElement::getDatePicker('TRACKINGNUMBER', date('Y-m-d'));
    echo "</div>"; 
    echo "</td></tr>";  
    
    renderQuotationAmmend();

  } else if ($resultStatus == DST_IN_PROGRESS) {

    renderDocumentSingle($mfTArr);

    echo "<tr><td width='150'>Change Status to :</td><td>";
    BasicInputElement::getCSS3RadioHorizontal("MD_STATUS","IN-PROGRESS,JOB-COMPLETE,CANCEL,AMEND","{$resultStatus},JOB-COMPLETE,C,AMEND-USE-CAPTURE",$resultStatus,$disabled = false);
    echo "</td></tr>";
    
   if ($mfTArr[0][0]['document_type_uid'] != DT_PURCHASE_ORDER) {
       echo "<tr><td width='150' colspan='2' >
          <br><center>
          <input type='submit' class='submit' value='Print Proforma Invoice' onclick='printQuotationProformaInvoice({$postDOCMASTID});'>&nbsp;&nbsp;
          <input type='submit' class='submit' value='Print Job Card' onclick='printQuotationJobCard({$postDOCMASTID});'>
          </center>
          </td></tr>";
   }       

  } else if ($resultStatus == DST_JOB_COMPLETE) {

    renderDocumentSingle($mfTArr);

    echo "<tr><td width='150'>Change Status to :</td><td>";
    BasicInputElement::getCSS3RadioHorizontal("MD_STATUS","JOB-COMPLETE,INVOICE,CANCEL","{$resultStatus},I,C",$resultStatus,$disabled = false, "mdStatusChange();");
    echo "</td></tr>";
   if ($mfTArr[0][0]['document_type_uid'] != DT_PURCHASE_ORDER) { 
       echo "<tr><td width='150' colspan='2' >
          <br><center>
          <input type='submit' class='submit' value='Print Delivery Note / Completion Certificate' onclick='printQuotationCompletionCertificate({$postDOCMASTID});'>&nbsp;&nbsp;
          </center>
          </td></tr>";
   } 
    renderQuotationAmmend();

  }
//*************************************************************
   else if ($resultStatus==DST_INVOICED) {

  renderDocumentSingle($mfTArr);

  $statusOpt = strtoupper(SNC::status_invoice).",CAPTURE PAYMENT";
  $statusVal = "{$resultStatus},DF";
  $aVal = round($mfTArr[0][0]['invoice_total'],2);
 
  if(GUICommonUtils::showHideField($scrPref,'DEBRIEF:DELPARTIAL',$f, false)==""){
    $statusOpt .= ', CAPTURE CREDIT';
    $statusVal .= ',DP';
  }
  echo "<tr><td  width='150' >Change Status to :</td><td>";
  BasicInputElement::getCSS3RadioHorizontal("MD_STATUS", $statusOpt, $statusVal, $resultStatus, $disabled = false, "mdStatusChange();");
  echo "</td></tr>";

   echo '<tr class="tr_deldetails" style="display:none;">';
   echo '<td colspan=2 style="font-weight:bold; border-bottom:1px; border-bottom-style:solid;height:30px" valign="bottom">Debriefing Details :</td></tr>';

   echo '<tr style="display:none;" class="tr_deldetails"><td colspan="2">';

   echo "<table class='tableReset' style='font-size:14px;' width='100%'>";
   echo '<tr><td width="150" style="padding:0px;">'.SNC::status_delivery.' Date: </td><td align="left" style="color:#555">';
    DatePickerElement::getDatePicker("MD_DELDATE",date('Y-m-d'));
   echo '</td>';
   
   
   echo '<td>';
     echo "<input id='MD_GRVNO' type='hidden' value='' />";
     echo '</td>';
     echo '<td>';
      echo "<input id='MD_WAYBILLNO' type='hidden' value=''/>";
     echo '</td>';
   
   
   
   
   echo '<td>';
     echo "<input id='MD_WAYBILLNO' type='hidden' value='' size=20 maxlength=20 />";
   echo '</td>';
   echo '<td>';
      echo "<input id='MD_CLAIMNO' type='hidden' value='' size=15 maxlength=15 />";
   echo '</td></tr>';
   echo '<td style="padding:0px;" >Comments: </td>';
     echo '<td align="left" style="color:#555;" colSpan="1" >';
      echo "<input id='MD_COMMENT' type='text' value='' size=25  maxlength=100 /></td></tr>";
//   echo '<tr><td style="padding:0px;" >Payment Type</td>';
//   echo '<td align="left" style="color:#555;"><select id="MD_PAYMENTTYPE"><option value="0">No Payment Received</option><option value="1">CASH</option><option value="2">EFT</option><option value="3">As Per Trading Terms</option></select></td>';
//   echo '<td style="padding:0px;" >Amount: </td>';
//   echo '<td align="left" style="color:#555;" colSpan="1" >';
//   echo "<input id='MD_PAYMENTAMOUNT' type='number' value='$aVal' size=10  maxlength=10 />";
//   echo '</td></tr>';
//   echo '</td></tr>';
     rendorDeBriefOptions(DST_DIRTY_POD);
   echo '</table>';
   echo '</td></tr>';
   
   //credit
   echo "<tr class='tr_deldetails_credit'  style='display:none;'>
          <td colSpan='2'>

            <div style='display:block;height:140px;overflow:auto;border:2px;border-style:dotted; border-color:#DDDDDD;'>
            <table class='tableReset' style='font-size:12px; ' width='100%'>
                <tr bgcolor='#FCFFB4' ><th style='line-height:22px;'>Product Code</th><th>Product Description</th><th>Ordered Qty</th><th>Document Qty</th><th>Delivered Qty</th></tr>";
  

                foreach($mfTDArr[0] as $r) {

                  echo "<tr style='border-bottom:1px solid #efefef'>
                        <td class='miTD' style='line-height:24px;border-right:1px solid #efefef'><strong>{$r["product_code"]}</strong></td>
                        <td class='miTD' style='border-right:1px solid #efefef'><strong>{$r["product_description"]}</strong></td>
                        <td class='miTD' style='border-right:1px solid #efefef'>{$r["ordered_qty"]}</td>
                        <td class='miTD' style='border-right:1px solid #efefef'>{$r["document_qty"]}</td>
                        <td class='miTD' >
                          <input type='hidden' name='MD_DOCMASTID' value='{$postDOCMASTID}' size='8'>
                          <input type='hidden' name='MD_DDUID[]' value='{$r["dd_uid"]}' size='8'>
                          <input type='text' name='MD_AMENDEDQTY[]' class='MD_AMENDEDQTY' value='{$r["document_qty"]}' size='8'></td>
                        </tr>";
                 }

	echo "</table></div>
              </td>
              </tr>";

   echo "<tr class='tr_deldetails_credit'  style='display:none;'>
          <td colspan=2 style='font-weight:bold; text-align:right;'>I accept these quantities : <input name='MD_ACCEPTQTY' type='checkbox' value='Y' UNCHECKED onChange='acceptQty(this)'></td>
        </tr>";
  /*
  echo "<tr><td>Comment</td><td><input id='MD_COMMENT' type='text' value='' size=60 maxlength=512 /></td></tr>
        <tr><td>Change Depot to (Cancelled status only):</td><td><select><option value=''>Unknown Depot</option><option value='{$mfT[0]["depot_uid"]}'>{$mfT[0]["depot_name"]}</option></select></td></tr>";
  */

}  else {
  echo "Document Status not eligible for Management by Depot";
}

//*************************************************************

} else if ($resultStatus == DST_UNACCEPTED) {

  if($postBULKACTION){
    renderDocumentBulk($mfTArr);
  } else {
    renderDocumentSingle($mfTArr);
  }

  echo "<tr><td width='150'>Change Status to :</td><td>";
  BasicInputElement::getCSS3RadioHorizontal("MD_STATUS","UNACCEPTED,ACCEPTED","{$resultStatus},A",$resultStatus,$disabled = false);
  echo "</td></tr>";

  /*
  echo "<tr><td>Comment</td><td><input id='MD_COMMENT' type='text' value='' size=60 maxlength=512 /></td></tr>
        <tr><td>Change Depot to (Cancelled status only):</td><td><select><option value=''>Unknown Depot</option><option value='{$mfT[0]["depot_uid"]}'>{$mfT[0]["depot_name"]}</option></select></td></tr>";
  */

} else if ($resultStatus==DST_ACCEPTED) {

  if($postBULKACTION){
    renderDocumentBulk($mfTArr);
  } else {
    renderDocumentSingle($mfTArr);
  }
  echo "<tr><td width='150'>Change Status to :</td><td>";
  if ($skipInPickStage!='Y') {
      if ($principalAliasId == 216) {
         BasicInputElement::getCSS3RadioHorizontal("MD_STATUS",  strtoupper(GUICommonUtils::translateDocumentStatusType($resultStatus)).",UNACCEPTED,IN-PICK,CANCEL","{$resultStatus},UNACCEPTED,IP,C",$resultStatus,$disabled = false,"mdStatusChange();");
      } else {
         BasicInputElement::getCSS3RadioHorizontal("MD_STATUS",  strtoupper(GUICommonUtils::translateDocumentStatusType($resultStatus)).",IN-PICK,CANCEL","{$resultStatus},IP,C",$resultStatus,$disabled = false,"mdStatusChange();");
      }  
  } else {
    BasicInputElement::getCSS3RadioHorizontal("MD_STATUS",
                                              strtoupper(GUICommonUtils::translateDocumentStatusType($resultStatus)).",".strtoupper(SNC::status_invoice).",CANCEL",
                                              "{$resultStatus},I,C",
                                              $resultStatus,
                                              $disabled = false,
                                              "mdStatusChange();");

    echo "<TR ".GUICommonUtils::showHideField($scrPref,'REPCODE',$f,false).">";
    echo "<TD>Over Ride Sales Code :</TD>";
    echo "<TD>";
  
    echo "<div class='select'>";
    BasicSelectElement::getDocumentRepCodes("REPCODE","","N","N","",null,null,$dbConn,$principalAliasId);
    echo "</div>";

    echo "<TR ".GUICommonUtils::showHideField($scrPref,'TRACKINGNUMBER',$f,false).">";
    echo "<TD>Required Invoice Date :</TD>";
    echo "<TD>";
    echo "<div class='select'>";
    DatePickerElement::getDatePicker('TRACKINGNUMBER', date('Y-m-d'));
    echo "</div>"; 
    echo "</td></tr>";  


  }

  rendorcanceloptions(DST_CANCELLED);

  // the Invoice button is on the accepted section if not using inPick status
  if ($skipInPickStage=='Y') {
    renderAmmend();
  }

  /*
  echo "<tr><td>Comment</td><td><input id='MD_COMMENT' type='text' value='' size=60 maxlength=512 /></td></tr>
        <tr><td>Change Depot to (Cancelled status only):</td><td><select><option value=''>Unknown Depot</option><option value='{$mfT[0]["depot_uid"]}'>{$mfT[0]["depot_name"]}</option></select></td></tr>";
  */

} else if ($resultStatus==DST_INPICK) {

  renderDocumentSingle($mfTArr);

  echo "<tr><td width='150'>Change Status to :</td><td>";
  BasicInputElement::getCSS3RadioHorizontal("MD_STATUS","ACCEPTED,".strtoupper(GUICommonUtils::translateDocumentStatusType($resultStatus)).",".strtoupper(SNC::status_invoice).",CANCEL","A,".DST_INPICK.",I,C",$resultStatus,$disabled = false,"mdStatusChange();");
  echo "</td></tr>";
  
  echo "<TR ".GUICommonUtils::showHideField($scrPref,'REPCODE',$f,false).">";
  echo "<TD>Over Ride Sales Code :</TD>";
  echo "<TD>";
  
  echo "<div class='select'>";
  BasicSelectElement::getDocumentRepCodes("REPCODE","","N","N","",null,null,$dbConn,$principalAliasId);
  echo "</div>";
  echo "</TD>";
  echo "</TR>";  

    echo "<TR ".GUICommonUtils::showHideField($scrPref,'TRACKINGNUMBER',$f,false).">";
    echo "<TD>Required Invoice Date :</TD>";
    echo "<TD>";
    echo "<div class='select'>";
    DatePickerElement::getDatePicker('TRACKINGNUMBER', date('Y-m-d'));
    echo "</div>"; 
    echo "</td></tr>";  

  rendorCancelOptions(DST_CANCELLED);
  /*
  echo "<tr><td>Comment</td><td><input id='MD_COMMENT' type='text' value='' size=60 maxlength=512 /></td></tr>
        <tr><td>Change Depot to (Cancelled status only):</td><td><select><option value=''>Unknown Depot</option><option value='{$mfT[0]["depot_uid"]}'>{$mfT[0]["depot_name"]}</option></select></td></tr>";
  */

  renderAmmend();

} else if ($resultStatus==DST_INVOICED) {

  renderDocumentSingle($mfTArr);

  $statusOpt = strtoupper(SNC::status_invoice).",".strtoupper(SNC::status_delivered)." FULL";
  $statusVal = "{$resultStatus},DF";
  $aVal = round($mfTArr[0][0]['invoice_total'],2);
 
  if(GUICommonUtils::showHideField($scrPref,'DEBRIEF:DELPARTIAL',$f, false)==""){
    $statusOpt .= ','.strtoupper(SNC::status_delivered).' PARTIAL';
    $statusVal .= ',DP';
  }

  echo "<tr><td  width='150' >Change Status to :</td><td>";
  BasicInputElement::getCSS3RadioHorizontal("MD_STATUS", $statusOpt, $statusVal, $resultStatus, $disabled = false, "mdStatusChange();");
  echo "</td></tr>";


   echo '<tr class="tr_deldetails" style="display:none;">';
   echo '<td colspan=2 style="font-weight:bold; border-bottom:1px; border-bottom-style:solid;height:30px" valign="bottom">Debriefing Details :</td></tr>';

   echo '<tr style="display:none;" class="tr_deldetails"><td colspan="2">';

   echo "<table class='tableReset' style='font-size:14px;' width='100%'>";
   echo '<tr><td width="150" style="padding:0px;">'.SNC::status_delivery.' Date: </td><td align="left" style="color:#555">';
    DatePickerElement::getDatePicker("MD_DELDATE",date('Y-m-d'));
   echo '</td>';

  echo '<td width="50" style="padding:0px;'.GUICommonUtils::showHideField($scrPref,'DEBRIEF:GRV',$f, true).';">GRV No: </td>';
    echo '<td align="left" style="color:#555;'.GUICommonUtils::showHideField($scrPref,'DEBRIEF:GRV',$f, true).';">';
     echo "<input id='MD_GRVNO' type='text' value='' size=20 maxlength=20 />";
  echo '</td></tr>';

   echo '<tr class="tr_deldetails" style="display:none;"><td style="padding:0px;" >Waybill No: </td>';
     echo '<td align="left" style="color:#555;">';
      echo "<input id='MD_WAYBILLNO' type='text' value='' size=20 maxlength=20 />";
   echo '</td>';
   echo '<td style="padding:0px;display:none;" class="tr_deldetails_credit">Claim No: </td>';
     echo '<td align="left" style="color:#555; display:none;" class="tr_deldetails_credit">';
      echo "<input id='MD_CLAIMNO' type='text' value='' size=15 maxlength=15 />";
   echo '</td></tr>';
   echo '<td style="padding:0px;" >Comments: </td>';
     echo '<td align="left" style="color:#555;" colSpan="1" >';
      echo "<input id='MD_COMMENT' type='text' value='' size=25  maxlength=100 /></td></tr>";
//   echo '<tr><td style="padding:0px;" >Payment Type</td>';
//   echo '<td align="left" style="color:#555;"><select id="MD_PAYMENTTYPE"><option value="0">No Payment Received</option><option value="1">Cash</option><option value="2">EFT</option><option value="3">Account</option><option value="4">Card</option></select></td>';
//   echo '<td style="padding:0px;" >Amount: </td>';
//   echo '<td align="left" style="color:#555;" colSpan="1" >';
//   echo "<input id='MD_PAYMENTAMOUNT' type='number' value='$aVal' size=10  maxlength=10 />";
//   echo '</td></tr>';
//   echo '</td></tr>';
     rendorDeBriefOptions(DST_DIRTY_POD);
   echo '</table>';
   echo '</td></tr>';
   
   //credit
   echo "<tr class='tr_deldetails_credit'  style='display:none;'>
          <td colSpan='2'>

            <div style='display:block;height:140px;overflow:auto;border:2px;border-style:dotted; border-color:#DDDDDD;'>
            <table class='tableReset' style='font-size:12px; ' width='100%'>
                <tr bgcolor='#FCFFB4' ><th style='line-height:22px;'>Product Code</th><th>Product Description</th><th>Ordered Qty</th><th>Document Qty</th><th>Delivered Qty</th></tr>";

                foreach($mfTDArr[0] as $r) {
                	file_put_contents('var2.txt', print_r($r,TRUE), FILE_APPEND);
                	if($r['allow_decimal'] == 'Y') {
                        $dOrderedQty = $r["ordered_qty"] /100;
                        $dDocumentQty = $r["document_qty"] /100;
                 	} else {
                        $dOrderedQty = $r["ordered_qty"];
                        $dDocumentQty = $r["document_qty"];
                 	}
                  
                  file_put_contents('var2.txt', $dDocumentQty, FILE_APPEND);
                  echo "<tr style='border-bottom:1px solid #efefef'>
                        <td class='miTD' style='line-height:24px;border-right:1px solid #efefef'><strong>{$r["product_code"]}</strong></td>
                        <td class='miTD' style='border-right:1px solid #efefef'><strong>{$r["product_description"]}</strong></td>
                        <td class='miTD' style='border-right:1px solid #efefef'>" . $dOrderedQty  . "</td>
                        <td class='miTD' style='border-right:1px solid #efefef'>" . $dDocumentQty . "</td>
                        <td class='miTD' >
                          <input type='hidden' name='MD_DOCMASTID' value='{$postDOCMASTID}' size='8'>
                          <input type='hidden' name='MD_DDUID[]' value='{$r["dd_uid"]}' size='8'>
                          <input type='text' name='MD_AMENDEDQTY[]' class='MD_AMENDEDQTY' value='{$dDocumentQty}' size='8'></td>
                        </tr>";
                 }

	echo "</table></div>
              </td>
              </tr>";

   echo "<tr class='tr_deldetails_credit'  style='display:none;'>
          <td colspan=2 style='font-weight:bold; text-align:right;'>I accept these quantities : <input name='MD_ACCEPTQTY' type='checkbox' value='Y' UNCHECKED onChange='acceptQty(this)'></td>
        </tr>";
  /*
  echo "<tr><td>Comment</td><td><input id='MD_COMMENT' type='text' value='' size=60 maxlength=512 /></td></tr>
        <tr><td>Change Depot to (Cancelled status only):</td><td><select><option value=''>Unknown Depot</option><option value='{$mfT[0]["depot_uid"]}'>{$mfT[0]["depot_name"]}</option></select></td></tr>";
  */

}  else {
  echo "Document Status not eligible for Management by Depot";
}
echo "</table>";


// WARNING : the .load() jquery event causes scripts to be executed twice in IE. The page is not fetched twice.
echo "<script type='text/javascript' defer>
          if (!debriefDocument.isLoadedManage) {
              debriefDocument.isLoadedManage=true;
              $('#m_action').unbind('click');
              $('#m_action').click(
              function(){
                debriefDocument.manageAccept('".(($postBULKACTION)?(join(',',$postDOCMASTARR)):($postDOCMASTID)) . "');
              }
            )
          }

          function acceptQty(obj){
            $('.MD_AMENDEDQTY').each(function(){
              if($(obj).attr('checked')){
                $(this).attr('disabled',true);
                $(this).css('background','#ccc');
              } else {
                $(this).attr('disabled',false);
                $(this).css('background','#fff');
              }
            })
          }

      //handles display of the various additional status options...
      function mdStatusChange() {
        var v=convertElementToArray(document.getElementsByName('MD_STATUS'));
        
        //hide all.
        $('.tr_canceldetails').css({'display':'none'});
        $('.tr_amenddetails').css({'display':'none'});
        $('.tr_deldetails').css({'display':'none'});
        $('.tr_deldetails_credit').css({'display':'none'});

        if (v=='I'){
          $('.tr_amenddetails').css({'display':'table-row'});
        } else if (v=='C'){
          $('.tr_canceldetails').css({'display':'table-row'});
        }

        if (v=='DF'){
          $('.tr_deldetails').css({'display':'table-row'});
        } else if (v=='DP'){
          $('.tr_deldetails').css({'display':'table-row'});
          $('.tr_deldetails_credit').css({'display':'table-row'});
        }

      }

      </script>"; // assign the click event to action button



 function getDocument($docMastId){

   global $dbConn, $userId;

  $resultTO = new ErrorTO();
  $resultTO->type = FLAG_ERRORTO_ERROR; //preset


  // don't do security checks on principal because not document details are revealed, and on submit, the security is then validated
  $transactionDAO = new TransactionDAO($dbConn);
  $mfT = $transactionDAO->getDepotDocumentItem($userId, $docMastId);
  $mfTD = $transactionDAO->getUserDepotDocumentDetails($docMastId, $userId);

  if ((sizeof($mfT)==0) || (sizeof($mfTD)==0)) {
    $resultTO->description = "ERROR - You do not have access to this information, or order does not exist.";
    return $resultTO;
  }

  if (!in_array($mfT[0]["document_type_uid"],array(DT_ORDINV,DT_ORDINV_ZERO_PRICE,DT_DELIVERYNOTE,DT_UPLIFTS,DT_DESTRUCTION_DISPOSAL,DT_WALKIN_INVOICE))) {
    $resultTO->description = "ERROR - Only Orders and Delivery Notes can be managed by depots";
    return $resultTO;
  }

  if (in_array($mfT[0]["document_status_uid"],explode(",",$transactionDAO->getUnacceptedOrderStatuses()))) {
    $resultTO->identifier = DST_UNACCEPTED;
  } else {
    $resultTO->identifier = $mfT[0]["document_status_uid"];
  }

  $resultTO->type = FLAG_ERRORTO_SUCCESS;
  $resultTO->object = array('H'=>$mfT, 'D'=>$mfTD);
  return $resultTO;

 }

 function getPrincipalDocument($docMastId){

   global $dbConn, $userId, $principalId;

   $resultTO = new ErrorTO();
   $resultTO->type = FLAG_ERRORTO_ERROR; //preset


   // don't do security checks on principal because not document details are revealed, and on submit, the security is then validated
   $transactionDAO = new TransactionDAO($dbConn);
   $mfT = $transactionDAO->getDocumentWithDetailItem($userId, $principalId, $docMastId,$orderBy=false,$inclStock=true);

   if (sizeof($mfT)==0){
     $resultTO->description = "ERROR - You do not have access to this information, or document does not exist.";
     return $resultTO;
   }

   if (!in_array($mfT[0]["document_type_uid"],array(DT_QUOTATION, DT_PURCHASE_ORDER))) {
   	echo $mfT[0]["document_type_uid"];
     $resultTO->description = "ERROR - Only Quotations can be managed by Principals";
     return $resultTO;
   }
   if (in_array($mfT[0]["document_status_uid"],explode(",",$transactionDAO->getUnacceptedOrderStatuses()))) {
     $resultTO->identifier = DST_UNACCEPTED;
   } else {
     $resultTO->identifier = $mfT[0]["document_status_uid"];
   }

   $resultTO->type = FLAG_ERRORTO_SUCCESS;
   $resultTO->object = $mfT;
   return $resultTO;

 }


 function renderDocumentBulk($arr){
   echo '<tr><td valign="top"><font color="red">Bulk Action applies to : </font></td><td align="left">';
   echo '<select size="4" disabled="disabled">';
   foreach($arr as $d){
     echo '<option >' . $d[0]['depot_name'] . ' - ' . $d[0]['principal_name']  . ' - ' . $d[0]['document_number'];
   }
   echo '</select>';
   echo '</td></tr>';
 }

 function renderDocumentSingle($arr){
   echo '<tr><td valign="top">Action applies to : </td><td align="left" style="color:#555">';
   echo $arr[0][0]['depot_name'] . ' - ' . $arr[0][0]['principal_name']  . ' - ' . $arr[0][0]['document_number'];
   echo '</td></tr>';
 }


 function rendorCancelOptions($associatedStatus){

   global $dbConn;

   echo '<tr class="tr_canceldetails" style="display:none;">';
   echo '<td colspan=2 style="font-weight:bold; border-bottom:1px; border-bottom-style:solid;height:30px" valign="bottom">Cancellation Details :</td></tr>';

   echo '<tr style="display:none;" class="tr_canceldetails"><td >Reason: ' , GUICommonUtils::requiredField() , '</td><td align="left" style="color:#555">';
   BasicSelectElement::getDocumentReasonByAssociatedStatus("MD_REASONCODE","","N","N",null,null,null,$dbConn, $associatedStatus);
   echo '</td></tr>';

   echo '<tr class="tr_canceldetails" style="display:none;"><td>Comments: </td><td align="left" style="color:#555">';
   echo "<input id='MD_COMMENT' type='text' value='' size=60 maxlength=512 />";
   echo '</td></tr>';

 }

 function rendorDeBriefOptions($associatedStatus){

   global $dbConn;

   echo '<tr class="tr_deldetails_credit" style="display:table-row;">';
   echo '<td colspan=2 style="font-weight:bold; border-bottom:1px; border-bottom-style:solid;height:30px" valign="bottom">Return Details :</td></tr>';

   echo '<tr class="tr_deldetails_credit" style="display:table-row;"><td >Reason: ' , GUICommonUtils::requiredField() , '</td><td align="left" style="color:#555">';
   BasicSelectElement::getDocumentReasonByAssociatedStatus("MD_REASONCODE","","N","N",null,null,null,$dbConn, $associatedStatus);
   echo '</td></tr>';

 }



 function renderAmmend() {

   global $mfTArr, $mfTDArr, $postDOCMASTID, $ROOT, $PHPFOLDER;
   
   $upliftFlag = ($mfTArr[0][0]["document_type_uid"] == DT_UPLIFTS) ? true : false;

   echo "<tr class='tr_amenddetails' style='display:none;'>
          <td  colspan=2 style='font-weight:bold; border-bottom:1px; border-bottom-style:solid;height:30px' valign='bottom'>Amend Details :</td>
        </tr>
        <tr class='tr_amenddetails'  style='display:none;'>
          <td colSpan='2'>";



   echo "<div style='display:block;height:140px;overflow:auto;border:2px;border-style:dotted; border-color:#DDDDDD;'>
              <table class='tableReset' style='font-size:12px;' width='100%'>
                <tr bgcolor='#FCFFB4' >
                  <th style='line-height:22px;'>Product Code</th>
                  <th>Product Description</th>
                  <th>Ordered Qty</th>";

           if(!$upliftFlag){
           	
           	  if($mfTDArr[0][0]["disable_stock_check"] == 'Y') {
           	  	   echo "<th>&nbsp;</th>
                   <th>Amended Qty</th>";
           	  } else {
           	  	   echo "<th>Stock on Hand</th>
                   <th>Amended Qty</th>"; 
           	  }
           } else {
             echo "<th></th>
                   <th>Uplift Qty</th>";
           }

           if ($mfTArr[0][0]["delivery_note"]=="Y") {
             echo "<th>Batch Number</th>";
           }

   echo "</tr>";

   foreach($mfTDArr[0] as $r) {
   	   	
   	if ($r["non_stock_item"] == 'Y' || $r["principal_uid"] == 342 || $r["depot_uid"] == 284 || $r["principal_uid"] == 390 || $r["principal_uid"] == 365 || $r["principal_uid"] == 305 || $r["depot_uid"] == 119 || $r["depot_uid"] == 469 || $r["depot_uid"] == 485 || $r["depot_uid"] == 486 ) {
            $amended=$r["ordered_qty"];
            $aOrdered = $r["ordered_qty"];
            $aClosing = $r["closing"];
    } else {   
       $amended=0;
       if($r["allow_decimal"] == 'Y') {
            $aOrdered=$r["ordered_qty"] /100;
            $aClosing = $r["closing"] / 100;
            if (intval($r["closing"]) < 0) { 
                $amended=0;
            } elseif (intval($r["closing"])<$r["ordered_qty"]) {
                $amended=$r["closing"] / 100;
            } else {
                $amended=$r["ordered_qty"] / 100;
            }
       } elseif($r["waiting_dispatch"] == 'Y') {
            $aOrdered = $r["ordered_qty"];
            $aClosing = $r["available"] + $r["goods_in_transit"] - $r["pending_dispatch"];
            if (intval($r["available"] + $r["goods_in_transit"] - $r["pending_dispatch"])<0) { 
                $amended=0;
            } elseif (intval($r["available"]) + $r["goods_in_transit"] - $r["pending_dispatch"]<$r["ordered_qty"]) {
                $amended=($r["available"] + $r["goods_in_transit"] - $r["pending_dispatch"]) ;
            } else {
                $amended=$r["ordered_qty"];
            } 
       } else {
            $aOrdered = $r["ordered_qty"];
            $aClosing = $r["closing"];
            
            if (intval($r["closing"])<0) { 
                $amended=0;
            } elseif (intval($r["closing"])<$r["ordered_qty"]) {
                $amended=$r["closing"];
            } else {
                $amended=$r["ordered_qty"];
            }
       }
       
//        file_put_contents('transfile.txt', "JJ", FILE_APPEND);
//        file_put_contents('transfile.txt', print_r($mfTDArr, TRUE), FILE_APPEND);
       
    }   
     echo "<tr style='border-bottom:1px solid #efefef'>
           <td class='miTD' style='line-height:24px;border-right:1px solid #efefef'><strong>{$r["product_code"]}</strong></td>
           <td class='miTD' style='border-right:1px solid #efefef'><strong>{$r["product_description"]}</strong></td>
           <td class='miTD' style='border-right:1px solid #efefef' width='80'>" . $aOrdered . "</td>";
     if(!$upliftFlag){
        	if ($r["non_stock_item"] == 'Y' ) {	
             echo "<td class='miTD' style='border-right:1px solid #efefef' width='80'>NSI</td>";
          } elseif ($r["disable_stock_check"] == 'Y') {
          	 echo "<td class='miTD' style='border-right:1px solid #efefef' width='80'>&nbsp;</td>";  
          } else {
             echo "<td class='miTD' style='border-right:1px solid #efefef' width='80'>{$aClosing}</td>";
          }
       echo "<td class='miTD'  width='80'>
               <input type='hidden' name='MD_DOCMASTID' value='{$postDOCMASTID}' size='8'>
               <input type='hidden' name='MD_DDUID[]' value='{$r["dd_uid"]}' size='8'>
               <input type='hidden' name='MD_ALLOWDECIMAL[]' value='{$r["allow_decimal"]}' size='8'>
               <input type='text'   name='MD_AMENDEDQTY[]' class='MD_AMENDEDQTY' value='{$amended}' size='8'>
             </td>";
     } else {
       echo "<td class='miTD'></td>";
       echo "<td class='miTD'  width='80'>
               <input type='hidden' name='MD_DOCMASTID' value='{$postDOCMASTID}' size='8'>
               <input type='hidden' name='MD_DDUID[]' value='{$r["dd_uid"]}' size='8'>
               <input type='text' name='MD_AMENDEDQTY[]' class='MD_AMENDEDQTY' value='{$r["ordered_qty"]}' disabled='disabled' size='8'>
             </td>";
     }
     if ($mfTArr[0][0]["delivery_note"]=="Y") {
        echo "<td class='miTD'  width='80'>
               <input type='text' name='MD_BATCH[]' class='MD_BATCH' value='{$r["batch"]}' size='8' maxlen='30'>
             </td>";
     }

     echo "</tr>";
   }

   echo "</table></div>
         </td>
         </tr>";
   echo "<tr class='tr_amenddetails'  style='display:none;'>
         <td colspan=2 style='font-weight:bold; text-align:right;'>I accept these quantities : <input name='MD_ACCEPTQTY' type='checkbox' value='Y' ".(!$upliftFlag?"UNCHECKED":"CHECKED")." onChange='acceptQty(this)'></td>
         </tr>";
 }

 function renderQuotationAmmend() {

   global $mfTArr, $postDOCMASTID;

   echo "<tr class='tr_amenddetails' style='display:none;'>
          <td  colspan=2 style='font-weight:bold; border-bottom:1px; border-bottom-style:solid;height:30px' valign='bottom'>Amend Details :</td>
        </tr>
        <tr class='tr_amenddetails'  style='display:none;'>
          <td colSpan='2'>";



   echo "<div style='display:block;height:140px;overflow:auto;border:2px;border-style:dotted; border-color:#DDDDDD;'>
              <table class='tableReset' style='font-size:12px;' width='100%'>
                <tr bgcolor='#FCFFB4' >
                  <th style='line-height:22px;'>Product Code</th>
                  <th>Product Description</th>
                  <th>Ordered Qty</th>";

   echo "<th>Stock on Hand</th>
         <th>Amended Qty</th>";

   echo "</tr>";

   foreach($mfTArr[0] as $r) {

     $amended=$r["ordered_qty"];

     echo "<tr style='border-bottom:1px solid #efefef'>
             <td class='miTD' style='line-height:24px;border-right:1px solid #efefef'><strong>{$r["product_code"]}</strong></td>
             <td class='miTD' style='border-right:1px solid #efefef'><strong>{$r["product_description"]}</strong></td>
             <td class='miTD' style='border-right:1px solid #efefef' width='80'>{$r["ordered_qty"]}</td>";

     echo "<td class='miTD' style='border-right:1px solid #efefef' width='80'>{$r["closing"]}</td>";
     echo "<td class='miTD'  width='80'>
           <input type='hidden' name='MD_DOCMASTID' value='{$postDOCMASTID}' size='8'>
           <input type='hidden' name='MD_DDUID[]' value='{$r["dd_uid"]}' size='8'>
           <input type='text' name='MD_AMENDEDQTY[]' class='MD_AMENDEDQTY' value='{$amended}' size='8'>
           </td>";

     echo "</tr>";
   }

   echo "</table></div>
       </td>
       </tr>";
   echo "<tr class='tr_amenddetails'  style='display:none;'>
     <td colspan=2 style='font-weight:bold; text-align:right;'>I accept these quantities : <input name='MD_ACCEPTQTY' type='checkbox' value='Y' UNCHECKED onChange='acceptQty(this)'></td>
     </tr>";
}
?>
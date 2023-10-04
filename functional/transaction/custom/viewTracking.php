<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
CommonUtils::getSystemConventions();

if (!isset($_SESSION)) session_start();
$principalId  = $_SESSION['principal_id'];
$principalCode = $_SESSION['principal_code'];
$userId       = $_SESSION['user_id'];
$principalType = $_SESSION['principal_type'];
$systemId = $_SESSION['system_id'];
$skipInPickStage = ((isset($_SESSION['skip_inpick_stage']))?$_SESSION['skip_inpick_stage']:"N");

$dbConn = new dbConnect();
$dbConn->dbConnection();

$principalDAO = new PrincipalDAO($dbConn);
$transactionDAO = new TransactionDAO($dbConn);

$principalArr = $principalDAO->getPrincipalItem($principalId);
$principalAltCode = $principalArr[0]['alt_principal_code'];

$adminDAO = new AdministrationDAO($dbConn);
$epodHasRole = $adminDAO->hasRole($userId,$principalId,ROLE_EPOD_TRANSACTION_TRACKING);
$hasRoleTT = $adminDAO->hasRole($userId,$principalId,ROLE_TRANSACTION_TRACKING);
$hasRoleManageQuotation = $adminDAO->hasRole($userId,$principalId,ROLE_MANAGE_QUOTATION);

if(!$hasRoleTT){
  echo "Sorry, you do not have permissions to VIEW TRACKING!";
  return;
}

//system configure.
$scrPref = $adminDAO->getAllFieldPreferences($principalId, $systemId, 'TRACKING');


        // field names for this form
        $fldFilterListname="TranListFilter"; // the names of the filter fields
        $tranListArr = array(); // strip off the columns we want.
        $tdExtraColArr = array();
        $tdExtraRowArr = array();
        $showResourceImage = false; //ResourceImage check


	$hasRole=$adminDAO->hasRole($userId,$principalId,ROLE_TT_REMOVE_STORE_LIMIT); // if has this role, then skip store permissions on list
	if ($hasRole) $flag="N"; else $flag="Y";

	if (isset($_GET["FILTERLIST"])) { $postFilterList=$_GET["FILTERLIST"]; $postFilterList=explode(',',$postFilterList); } else $postFilterList="";
	if (isset($_GET["FROMDATE"])) $postFROMDATE=mysql_real_escape_string(htmlspecialchars($_GET["FROMDATE"])); else $postFROMDATE="";
	if (isset($_GET["TODATE"])) mysql_real_escape_string(htmlspecialchars($postTODATE=$_GET["TODATE"])); else $postTODATE="";
	if (isset($_GET["DATETYPE"])) mysql_real_escape_string(htmlspecialchars($postDATETYPE=$_GET["DATETYPE"])); else $postDATETYPE="";
	if (isset($_GET["SCRUSAGE"])) mysql_real_escape_string(htmlspecialchars($postSCRUSAGE=$_GET["SCRUSAGE"])); else $postSCRUSAGE="1";

	$mfUP = $adminDAO->getUserPreferences($userId);

	if (sizeof($mfUP)==0) {
	  $trackingTransactionDayGap = 1;
	  $trackingTransactionColumns = false;
  } else {
    $trackingTransactionDayGap = ($mfUP[0]["tracking_transaction_day_gap"]!=0)?($mfUP[0]["tracking_transaction_day_gap"]):(1);
    $trackingTransactionColumns = ($mfUP[0]["tracking_transaction_columns"]!='') ? ($mfUP[0]["tracking_transaction_columns"]):(false);
  }

	if ($postFROMDATE=="") $postFROMDATE = CommonUtils::getUserDate(-($trackingTransactionDayGap));
	if ($postTODATE=="") $postTODATE = CommonUtils::getUserDate();
	if ($postDATETYPE=="") $postDATETYPE = "processed_date";

	echo "<HTML>";
	echo "<HEAD>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.js'></script>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js'></script>";
	// autoscroll start
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.autoscroll.js'></script>";
	echo "<scr"."ipt type=\"text/javascript\">";
	echo "\$(document).ready(function(){
          document.body.focus(); \$.autoscroll.init({step: 200});
          });";

	echo "</script>";
	// autoscroll end
	DatePickerElement::getDatePickerLibs();
	echo "<LINK href='".$ROOT.$PHPFOLDER."css/default.css' rel='stylesheet' type='text/css'>" ;
        echo '<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />';

        echo "</HEAD>";

	echo "<BODY id='viewTracking'><CENTER>";


	// START : DEPOT Users Screen Usage selection
	// NB: the first 4 options show EVERYTHING of that status, no date params necessary.
	if (CommonUtils::isDepotUser()) {

          $cntArr=$transactionDAO->getOrdersStatusCount($userId);
          $uACnt=$cntArr["unaccepted_cnt"];
          $aCnt=$cntArr["accepted_cnt"];
          $iPCnt=$cntArr["inpick_cnt"];
          $iCnt=$cntArr["invoiced_cnt"];

          $hideUnaccepted = (GUICommonUtils::showHideField($scrPref,'UNACCEPTED',$f, false)=="") ? false : true;

          echo '<div id="tracking-tabs" class="tracking-tabs-container" style="margin-bottom:20px;">
                  <ul>';
          if(!$hideUnaccepted){
              echo '<li class="tab '.(($postSCRUSAGE==1)?('active'):('')).'"><a href="javascript:;" onClick="changeScreenUsage(1);">Unaccepted ('.$uACnt.')</a></li>';
          }
              echo '<li class="'.(($postSCRUSAGE==2)?('active'):('')).'"><a href="javascript:;" onClick="changeScreenUsage(2);">Accepted ('.$aCnt.')</a></li>';
              if ($skipInPickStage!="Y") {
                echo '<li class="'.(($postSCRUSAGE==3)?('active'):('')).'"><a href="javascript:;" onClick="changeScreenUsage(3);">In-Pick ('.$iPCnt.')</a></li>';
              } else {
                // just a safety check so documents cannot "hide"
                if ($iPCnt>0) {
                  echo "WARNING !!!<br><br>This depot is set to not use the In-Pick stage but documents were found currently in the in-pick status !!!";
                }
              }
              echo '<li class="'.(($postSCRUSAGE==4)?('active'):('')).'"><a href="javascript:;" onClick="changeScreenUsage(4);">'. SNC::status_invoiced . '(' . $iCnt.')</a></li>';
              echo '<li class="'.(($postSCRUSAGE==5)?('active'):('')).'"><a href="javascript:;" onClick="changeScreenUsage(5);">Search</a></li>';
          echo  '</ul>
               </div>';


          if ($postSCRUSAGE==1 || $postSCRUSAGE==2) {
            echo 'Bulk Actions : ';
            echo '<a href="javascript:;" onclick="showManageBulk();">[Manage Selected]</a>';
            echo "<BR><BR>";
          }

	}
	// END : DEPOT Users Screen Usage selection

	echo "<FORM action='".$_SERVER['PHP_SELF']."'  style='margin:0; padding:0; ".(((CommonUtils::isDepotUser()) && ($postSCRUSAGE!=5))?" display:none;":"")." ' >";
	echo "<BR>";
        //echo "<SPAN style='font-family:Verdana,Arial,Helvetica,sans-serif; font-weight:bold;font-size:0.8em;'></SPAN>";
	echo "<TABLE class='tblReset' width=\"400\">";
        echo '<thead><tr><th colspan="2">Parameters</th></tr></thead>';
	echo "<TR>";
		echo "<TD nowrap>Filter on:</TD>";
		echo "<TD >";

                //updated filter by... DD
		$labels = array('Processed Date','Order Date',SNC::status_invoice.' Date','Delivery Date','POD Returned Date');
		$values = array('processed_date','order_date','invoice_date','delivery_date','pod_returned_date');
                BasicSelectElement::buildGenericDD("DATETYPE", $labels, $values, $postDATETYPE, "N", "N", null, null, null);

		echo "</TD>";
	echo "</TR>";

	echo "<TR>";
		echo "<TD>From:</TD>";
		echo "<TD >";
			DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE);
		echo "</TD>";
	echo "</TR>";
	echo "<TR>";
		echo "<TD>To:</TD>";
		echo "<TD >";
			DatePickerElement::getDatePicker("TODATE",$postTODATE);
		echo "</TD>";
	echo "</TR>";
		echo "<TR><TD colSpan=\"2\"  height='40' valign='bottom' align=\"left\"><SPAN style='color:".COLOR_UNOBTRUSIVE_INFO.";font-size:10px; font-weight:normal'>Please use the SUBMIT FILTER button on the table below, and enter any additional parameters above the columns.</SPAN></TD></TR>";
	echo "</TABLE>";
	echo "</FORM>";


	// only query if submit filter was clicked
	if ($postFilterList=="" && !(isset($_GET['AUTOSUBMIT'])&&$_GET['AUTOSUBMIT']==1)) {
		$mfDocs = array();
	} else {

		if (CommonUtils::isDepotUser()) {

			if ($postSCRUSAGE==1) {
				$mfDocs = $transactionDAO->getDepotDocumentsArray($userId,"unaccepted");
			} else if ($postSCRUSAGE==2) {
			  $mfDocs = $transactionDAO->getDepotDocumentsArray($userId,"accepted");
			} else if ($postSCRUSAGE==3) {
				$mfDocs = $transactionDAO->getDepotDocumentsArray($userId,"inpick");
			} else if ($postSCRUSAGE==4) {
				$mfDocs = $transactionDAO->getDepotDocumentsArray($userId,"invoiced");
			} else {
			  $mfDocs = $transactionDAO->getDepotDocumentsArray($userId, "all", $postFROMDATE, $postTODATE, $postDATETYPE);
			}

		} else {
		  $mfDocs = $transactionDAO->getDocumentsArray($userId, $principalId, $postFROMDATE, $postTODATE, $postDATETYPE, "", "", $flag);
		  $mfExclDocCnt = $transactionDAO->getDocumentsCountWithoutPermissions($userId, $principalId, $postFROMDATE, $postTODATE, $postDATETYPE, "");
		}

	}


    if (!CommonUtils::isDepotUser()) {
      $fldFilterListUsageArr=array(1=>"Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","N","N","N");
      $fldFilterListSizeArr=array(1=>"5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","0","0","0");
      $headers = array(
                      'documentNumber' => 'Document Reference',
                      'orderDate' => SNC::status_order.' Date',
                      'invoiceDate' => SNC::status_invoice.' Date',
                      'requestedDeliveryDate' => 'Requested '.SNC::status_delivery.' Date',
                      'dueDeliveryDate' => 'Due '.SNC::status_delivery.' Date',
                      'deliveryDate' => SNC::status_delivery.' Date',
                      'customer' => 'Customer',
                      'epod' => 'EPOD',
                      'status' => 'Status',
                      'cases' =>'Cases',
                      'documentType' => 'Document Type',
                      'invoiceNumber' => SNC::invoice_no,
                      'deliveryDay' => SNC::status_delivery.' Day',
                      'grvNumber' => 'GRV No',
                      'claimNumber' => 'Claim No',
                      'customerOrderNumber' => 'Customer Order No',
                      'alternateDocumentNumber' => 'Alternate Document Number',
                      'sourceDocumentNumber' => 'Source Document No',
                      'depotName' => 'Depot',
                      'inFile' => 'Incoming File',
                      'processDate' => 'Processed Date',
                      'processingDetail' => 'Processing Detail',
                      'resourceImagery' => 'Resource Imagery',
                      'specialFields' => 'Special Field(s)',
                       // anything after this comment, if it has a reference in userPreference, will cause an offset error, so only use for holding vars
                      'principalUId' => 'Hold Var 1',
                      );
      if ($hasRoleManageQuotation) {
        $temp = array(1=>"Y");
        foreach($fldFilterListUsageArr as $r) $temp[] = $r;
        $fldFilterListUsageArr = $temp;

        $temp = array(1=>"5");
        foreach($fldFilterListSizeArr as $r) $temp[] = $r;
        $fldFilterListSizeArr = $temp;

        $headers = array_merge(array('manage' => 'Manage'), $headers); // doesnt renumers indexes from 0 so cant use above
      }

    } else {
      $fldFilterListUsageArr=array(1=>"N","N","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y");
      $fldFilterListSizeArr=array(1=>"0","0","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5");
      $headers = array(
                      'selectRow' => '',
                      'manage' => 'Manage',
                      'principalName' => SNC::principal,
                      'documentNumber' => 'Document Reference',
                      'orderDate' => SNC::status_order.' Date',
                      'invoiceDate' => SNC::status_invoice.' Date',
                      'deliveryDate' => SNC::status_delivery.' Date',
                      'customer' => 'Customer (Principal)',
                      'deliveryLocation' => 'Delivery Location',
                      'deliveryArea' => 'Delivery Area',
                      'status' => 'Status',
                      'cases' => 'Cases',
                      'documentType' => 'Document Type',
                      'invoiceNumber' => SNC::invoice_no,
                      'deliveryDay' => SNC::status_delivery.' Day (Depot)',
                      'customerOrderNumber' => 'Customer Order No',
                      'depotName' => 'Depot',
                      'sourceDocumentNumber' => 'Source Document No',
                      'processDate' => 'Processed Date',
                      'principalUId' => '',
                      );

    }



  /********************************
   * START : Process Special Fields
   * ******************************/
  // get the unique list of document master uids
  $dmUIdArr=array();
  foreach($mfDocs as $d) {
    $dmUIdArr[]=$d["dm_uid"];
  }
  $mfSF=array(); // recordset
  $mfSFHdr=array(); // headers
  if (sizeof($dmUIdArr)>0) {
    $mfSF=$transactionDAO->getDocumentsSpecialFields(implode(",",$dmUIdArr));
    foreach($mfSF as $v) {
          $hdrsArr=explode("|",$v["sff_names"]);
      foreach($hdrsArr as $h) {
        $mfSFHdr[$h]=$h; // store unique field names
      }
    }
  }
  foreach($headers as &$h) {
    if ($h=="Special Field(s)") {
          $h.="<br>".implode("|",$mfSFHdr);
    }
  }
  unset($h); // must do this as if $h is used later it modifies the header again !!
  /********************************
   * END : Process Special Fields
   * ******************************/

  if ((isset($mfExclDocCnt)) && (!CommonUtils::isDepotUser())) {
    echo "<SPAN style='color:".COLOR_URGENT_TEXT."'>".$mfExclDocCnt[0]["cnt"]." document(s) were excluded due to missing user permissions on store/chain/principal-depot</SPAN>";
  }


  // legend
  if ((CommonUtils::isDepotUser() && $postSCRUSAGE==5) || !CommonUtils::isDepotUser()) {
    echo "<table class='tableReset' style='width:100%;'><tr>";
    echo "<td style='width:20px; text-align:center; padding-left:30px; padding-bottom:0; margin-bottom:0;'><img src='".$DHTMLROOT.$PHPFOLDER."images/subitem-icon.png' height='15px' /></td><td>Linked Document</td>";
    echo "</tr></table>";
  }


  //CHECK IF STAFF MEMBER
  if(CommonUtils::isStaffUser()){
    $showResourceImage = true;
  } else {

    //Check Principal Level 1 Check
    //$principalArr var is set higher above in this script
    if(isset($principalArr[0]['activity_price_bucket_1']) && $principalArr[0]['activity_price_bucket_1'] == 'Y'){
      $showResourceImage = true;
    }

  }


    // product permissions is not checked here on purpose.
	$headerSet = false;
	$dMUIdArr = Array();
	$dsArr = array("WS","EDI"); // from orders
	for ($i = 0; $i<sizeof($mfDocs); $i++) {
		$row = $mfDocs[$i];

		if (!$headerSet) {
      $data = array();

      // WHY ARE WE NOT INDICATING CREDIT NOTES WITH AN ICON INDENTED FOR DEPOT USERS ????
      if (!CommonUtils::isDepotUser()) {

        // start : quotations action panel
        if ($hasRoleManageQuotation) {

          if (($row["document_type_uid"]==DT_QUOTATION) OR ($row["document_type_uid"]==DT_PURCHASE_ORDER)) {
            // a span is used instead of a div to stop wrapping
            if (in_array($row["status_uid"],array(DST_UNACCEPTED,DST_ACCEPTED,DST_IN_PROGRESS,DST_JOB_COMPLETE))) {
              $data['manage'] ="<span id='APANEL_{$row["dm_uid"]}' style='white-space:nowrap;'>
                                <a href='javascript:;' onclick='showManage({$row["dm_uid"]}, getRowIndex(this), this, null);' style='border:none; text-decoration:none;'><img src='{$DHTMLROOT}{$PHPFOLDER}images/gear.jpg' style='border:none; text-decoration:none; height:18px;' title='Manage / Amend this Quotation' /></a>
                                </span>";
            } else $data['manage'] = "";

            $data['manage'].="<a href='javascript:;' onclick=\"copyQuotationDocument('{$row["dm_uid"]}');\" style='border:none; text-decoration:none;'><img src='{$DHTMLROOT}{$PHPFOLDER}images/copy-document.jpg' style='border:none; text-decoration:none; height:18px;' title='Copy Document for Editing' /></a>";
            $data['manage'].="<a href='javascript:;' onclick=\"openCard('documentAuditCard.php','DOCMASTID={$row["dm_uid"]}'); ;\" style='border:none; text-decoration:none;'><img src='{$DHTMLROOT}{$PHPFOLDER}images/audit_log.gif' style='border:none; text-decoration:none; height:18px;' title='View depot Audit Log for this order' /></a>";
            $data['manage'].="<div id='DMUID_{$row["dm_uid"]}' style='display:none;'></div>"; // this div is here only so that the row can be hidden (findable) after successful status change
          } else {
            $data['manage'] = "";
          }

        }
        // end : quotations action panel

        if(
            ($row['source_document_number']!="") &&
            (in_array($row["document_type_uid"],array(DT_CREDITNOTE,DT_UPLIFT_CREDIT,DT_ARRIVAL_CORRECTION,DT_DEBITNOTE,DT_UPLIFT_DEBIT)))
          ) {
            $data['documentNumber']="<img src='".$DHTMLROOT.$PHPFOLDER."images/subitem-icon.png' height='14px' style='border:0;' />&nbsp;".$row['document_number'];
          } else {
            $data['documentNumber']=$row['document_number'];
          }

          $data['orderDate']=$row['order_date'];
          $data['invoiceDate']=$row['invoice_date'];
          $data['deliveryDate']=$row['delivery_date'];
          $data['requestedDeliveryDate']=$row['requested_delivery_date'];
          $data['dueDeliveryDate']=$row['due_delivery_date'];

          $linkCust="<A style='color:grey;' href=\"javascript:;\" onClick=\"window.open('".$ROOT.$PHPFOLDER."functional/stores/storeCard.php?PRINCIPALSTOREUID=".$row['principal_store_uid']."','myStore','scrollbars=yes,width=400,height=550');\">".$row['store_name']."</A>";
          $data['customer']=$linkCust;

          //EPOD
          $data['epod'] = "";
          if($row['epod_store_flag'] == 'Y' && $epodHasRole){
            $data['epod'] = "<A href=\"javascript:;\" onClick=\"openCard('orderEPODCard.php','DOCMASTID=" . $row['dm_uid'] . "');\">[view]</A>";
          }

          $data['status']="<div id='status".$row['dm_uid']."'>{$row['status']}</div>";
          $data['cases']=$row['cases'];
          $data['documentType']=$row['document_type_description'];
          $data['invoiceNumber']=$row['invoice_number'];
          $data['deliveryDay']=$row['delivery_day'];
          $data['grvNumber']=$row['grv_number'];
          $data['claimNumber']=$row['claim_number'];
          $data['customerOrderNumber']=$row['customer_order_number'];
          $data['alternateDocumentNumber'] = (!empty($row['alternate_document_number'])) ? str_pad($row['alternate_document_number'], 8, '0', STR_PAD_LEFT) : '';
          $data['sourceDocumentNumber']=$row['source_document_number'];
          $data['depotName']=$row['depot_name'];
          $data['inFile']=$row['incoming_file'];
          $data['processDate']=$row['processed_date'];
          $data['processingDetail']="<A href=\"javascript:;\" onClick=\"openCard('orderProcessingCard.php','DOCMASTID=" . $row['dm_uid'] . "');\">[identifiers]</A>".
                                   ((in_array($row['data_source'],$dsArr))?"<A href=\"javascript:;\" onClick=\"window.open('".$ROOT.$PHPFOLDER."functional/transaction/ordersHoldingCard.php?REFERENCE={$row['customer_order_number']}','myOH','scrollbars=yes,width=800,height=400,resizable=yes');\">[incoming EDI]</A>":"");

          if($row['depot_uid'] == 2 && in_array($row['status_uid'],array(77,78)) && $row["document_type_uid"] == 1){
            if($showResourceImage){
              $data['resourceImagery'] = "<A href=\"".$ROOT.$PHPFOLDER."functional/transaction/documentResourceCard.php?DOCNO=" . $row['document_number'] . "\" target='_blank'>[view]</A>";
            } else {
              $data['resourceImagery'] = "<span style='color:#666;text-decoration:line-through'>[view]</SPAN>";
            }
          } else {
            $data['resourceImagery'] = '';
          }

          /*******************************************************************************
           * START : Put special field values into correct aligned column (pipe separator)
           *******************************************************************************/
          if (isset($mfSF[$row["dm_uid"]])) {
                  // echo "<br>";
                  $iHdr=0; $sfVals=array();
                  $n=explode("|",$mfSF[$row["dm_uid"]]["sff_names"]);
                  $v=explode("|",$mfSF[$row["dm_uid"]]["sfd_values"]);
                  $sfVals=array_fill_keys($mfSFHdr,""); // use same keys
                  foreach($n as $h) {
                    $sfVals[$h]=$v[$iHdr];
                    $iHdr++;
                  }
                  $data['specialFields'] = ((isset($mfSF[$row["dm_uid"]]))?implode("|",$sfVals)."<a href='#' onclick='showManageSF({$row["dm_uid"]});'>&lt;edit&gt;</a>":"");
          }
          /*******************************************************************************
           * END : Put special fields into correct aligned column (pipe separator)
           *******************************************************************************/

          $data['principalUId'] = $row['principal_uid'];


      } else {

          $data['selectRow'] = "";
          if ((in_array($row["document_type_uid"],array(DT_ORDINV,DT_ORDINV_ZERO_PRICE,DT_DELIVERYNOTE,DT_UPLIFTS,DT_DESTRUCTION_DISPOSAL))) && (in_array($postSCRUSAGE,array(1,2)))) {
            $headers['selectRow'] = "<div style='white-space:nowrap;' align='center'>Select<br><a href='javascript:selectAll(1,\"MANAGE_ITEM\")' style='color:#F90'>All</a> - <a href='javascript:selectAll(0,\"MANAGE_ITEM\")' style='color:#F90'>None</a></div>";
            $data['selectRow'] = "<div align='center' id='CHECKITEM_{$row["dm_uid"]}'><input type='checkbox' value='{$row["dm_uid"]}' name='MANAGE_ITEM[]' class='MANAGE_ITEM'></div>";
          }

          // start : depot users action panel
          $data['manage'] = '';
          if ((in_array($row["document_type_uid"],array(DT_ORDINV,DT_ORDINV_ZERO_PRICE,DT_DELIVERYNOTE,DT_UPLIFTS,DT_DESTRUCTION_DISPOSAL))) && (in_array($postSCRUSAGE,array(1,2,3,4)))) {
                // a span is used instead of a div to stop wrapping
                $data['manage'] ="<span id='APANEL_{$row["dm_uid"]}' style='white-space:nowrap;'>
                                  <a href='javascript:;' onclick='showManage({$row["dm_uid"]}, getRowIndex(this), this, \"{$row["delivery_note"]}\");' style='border:none; text-decoration:none;'><img src='{$DHTMLROOT}{$PHPFOLDER}images/gear.jpg' style='border:none; text-decoration:none; width:18px; height:18px;' title='Manage / Amend this order' /></a>
                                  <a href='javascript:;' onclick='showLink({$row["dm_uid"]}, getRowIndex(this), this);' style='border:none; text-decoration:none;'><img src='{$DHTMLROOT}{$PHPFOLDER}images/link.gif' style='border:none; text-decoration:none; width:18px; height:18px;' title='Link to Delivery Location' /></a>
                                  </span>";
          }
          $data['manage'].="<a href='javascript:;' onclick=\"openCard('documentAuditCard.php','DOCMASTID={$row["dm_uid"]}'); ;\" style='border:none; text-decoration:none;'><img src='{$DHTMLROOT}{$PHPFOLDER}images/audit_log.gif' style='border:none; text-decoration:none; width:18px; height:18px;' title='View depot Audit Log for this order' /></a>";
          $data['manage'].="<div id='DMUID_{$row["dm_uid"]}' style='display:none;'></div>"; // this div is here only so that the row can be hidden (findable) after successful status change
          // end : depot users action panel

          $data['principalName']=$row['principal_name'];
          $data['documentNumber']=$row['document_number'];
          $data['orderDate']=$row['order_date'];
          $data['invoiceDate']=$row['invoice_date'];
          $data['deliveryDate']=$row['delivery_date'];
          $linkCust="<A style='color:grey;' href=\"javascript:;\" onClick=\"window.open('".$ROOT.$PHPFOLDER."functional/stores/storeCard.php?pAlias={$row['principal_uid']}&PRINCIPALSTOREUID=".$row['principal_store_uid']."','myStore','scrollbars=yes,width=400,height=550');\">".$row['store_name']."</A>";
          $data['customer']=$linkCust;
          $data['deliveryLocation']="<div id='delloc".$row['dm_uid']."'>".(($row["depot_store_name"]=="")?"<span style='color:#CCCCCC;'>... not linked ...</span>":$row["depot_store_name"])."</div>";
          $data['deliveryArea']="<div id='delarea".$row['dm_uid']."'>".(($row["area_description"]=="")?"<span style='color:#CCCCCC;'>... not linked ...</span>":$row["area_description"])."</div>";
          $data['status']="<div id='status".$row['dm_uid']."'>". (($row['status']!='Invoiced')?($row['status']):(SNC::status_invoiced)) . "</div>"; // is referenced by depot users after a status change occurs
          $data['cases']=$row['cases'];
          $data['documentType']=$row['document_type_description'];
          $data['invoiceNumber']=$row['invoice_number'];
          $data['deliveryDay']="<div id='delday".$row['dm_uid']."'>".(($row["delivery_day"]=="")?"<span style='color:#CCCCCC;'>... not linked ...</span>":$row["delivery_day"])."</div>"; // from depot's perspective !
          $data['customerOrderNumber']=$row['customer_order_number'];
          $data['depotName']=$row['depot_name'];
          $data['sourceDocumentNumber']=$row['source_document_number'];
          $data['processDate']=$row['processed_date'];
          $data['principalUId'] = $row['principal_uid'];

      }


      $tdExtraRowArr[]=" nowrap ";
      $headerSet=true;

		}

    // if last row
    if ($i==(sizeof($mfDocs)-1)) {
            $tranListArr[]=$data;
            $dMUIdArr[]=$row['dm_uid'];
    } else {
            // next row is a change in control break
            if ($row['dm_uid']!=$mfDocs[$i+1]['dm_uid']) {
                    $tranListArr[] = $data;  //change to array - unset removed columns later...
                    $dMUIdArr[]=$row['dm_uid'];
                    $headerSet=false;
            }
    }

  } // end loop


  // loop through once again to set the correction flags & the document link ref
  for ($i=0; $i<sizeof($tranListArr); $i++) {
          $row=$tranListArr[$i];  //row and translistarr are real arrays - changes below.
          if (!CommonUtils::isDepotUser()) {
                  if ($i<(sizeof($tranListArr)-1)) {
                          if ($row['documentNumber']==$tranListArr[$i+1]['sourceDocumentNumber']) {
                                  $tranListArr[$i]['documentNumber'] .= " <B>(C)</B>";
                                  $tranListArr[$i]['sourceDocumentNumber'] .= " <B>".$row['documentNumber']."</B>";
                          }
                  }
          }
          // can only hyperlink it now because otherwise comparison would fail
    $tranListArr[$i]['documentNumber'] = "<A href=\"javascript:;\" onClick=\"window.open('".$ROOT.$PHPFOLDER."functional/transaction/documentCard.php?pAlias={$row['principalUId']}&DOCMASTID=".$dMUIdArr[$i]."','myOrderProcessing".$dMUIdArr[$i]."','scrollbars=yes,width=750,height=600,resizable=yes,status=yes');\">".$row['documentNumber']."</A>";

    // remove the principalId so doesnt print in table
    unset($tranListArr[$i]['principalUId']);
  }

  // SYSTEM Preferences
  GUICommonUtils::systemFieldPreferenceFilter("TRACKINGTABLE",
                                              $systemId,
                                              $principalId,
                                              $fldFilterListUsageArr,
                                              $fldFilterListSizeArr,
                                              $headers,
                                              $tranListArr);


  /*
   *
   * USER PREF : REMOVE NON SELECTED COLUMNS IN PREF
   *
   */
  if (!CommonUtils::isDepotUser()) {

    if($trackingTransactionColumns){  //if user has pref
      $userSetCols = explode(',',$trackingTransactionColumns);

       //REBUILD THESE ARRAYS - REMOVING NON SET COLUMNS AND ORDERING AS PER USER TT COL ARR
      $tranListReColsArr = array();
      $headersReColsArr = array();
      $fldFilterListUsageReColsArr = array();
      $fldFilterListSizeReColsArr = array();
      $fiListNo = 1; //fldFilters 1 based.

      if ($hasRoleManageQuotation) {
        $headersReColsArr[] = $headers["manage"];
        // assumes manage col is always in position 1, wont affect loop below as $no=1 will never enounter index of "manage"
        $fldFilterListUsageReColsArr[$fiListNo] = $fldFilterListUsageArr[1];
        $fldFilterListSizeReColsArr[$fiListNo] = $fldFilterListSizeArr[1];
        $fiListNo++;

        //change data array returned...
        foreach($tranListArr as $kyNo => $rowData){
          $tranListReColsArr[$kyNo]["manage"] = $rowData["manage"];
        }
      }

      foreach($userSetCols as $ukey => $userCol){  //loop through user pref columns...

        //get the properties of a class into an array...
        $no = 1;

        foreach($headers as $colNo=>$ttCols){
          if ($userCol == $colNo) {
            //change headers and filter
            $headersReColsArr[] = $headers[$colNo];
            $fldFilterListUsageReColsArr[$fiListNo] = $fldFilterListUsageArr[$no];
            $fldFilterListSizeReColsArr[$fiListNo] = $fldFilterListSizeArr[$no];
            $fiListNo++;
          }
          $no++;
        }

        //change data array returned...
        foreach($tranListArr as $kyNo => $rowData){
          foreach($rowData as $fkey => $fdata){
            if ($userCol == $fkey) {
              $tranListReColsArr[$kyNo][$fkey] = $fdata;
            }
          }
        }

      }

      //re-set the main arrays
      $tranListArr = $tranListReColsArr;
      $headers = $headersReColsArr;
      $fldFilterListUsageArr = $fldFilterListUsageReColsArr;
      $fldFilterListSizeArr =  $fldFilterListSizeReColsArr;

    }

/*
 * USER PREF : END.
 */
  }
	$pArr = GUICommonUtils::applyFilter($tranListArr,$postFilterList);

	// button row - must be own table due to button sizes being wider than output columns
	echo '<TABLE id="documentTable" width="100%">';

              GUICommonUtils::getFilterFieldsNonAjax($fldFilterListname,
                                                      $fldFilterListUsageArr,
                                                      $fldFilterListSizeArr,
                                                      $postFilterList,
                                                      "+'&FROMDATE='+document.getElementById(\"FROMDATE\").value+'&TODATE='+document.getElementById(\"TODATE\").value+'&DATETYPE='+convertElementToArray(document.getElementsByName(\"DATETYPE\"))+'&SCRUSAGE={$postSCRUSAGE}';",
                                                      $ROOT.$PHPFOLDER."functional/transaction/viewTracking.php");
              // the data
              GUICommonUtils::outputTable ( $headers,
                                            $pArr,
                                            $tdExtraColArr,
                                            $tdExtraRowArr);

	echo '</TABLE>';

		echo "<SPAN style='color:".COLOR_UNOBTRUSIVE_INFO."; font-weight:bold'>***NEW FEATURE*** To automatically scroll across, press and hold down CTRL and move mouse to edge of page</SPAN>";

	echo "</CENTER></BODY></HTML>";
	$dbConn->dbClose();
	echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable

	// unfortunately couldnt use the generic popup box and the .load jquery method as the css doesnt work afterwards and DOM elements are not visible when dynamically created
	echo "<div id='div_MANAGE' class='rdCrn10' style='z-index:100; padding:10px; top:0px; left:0px; width:750px; position:absolute; display:none; background-color:#EFEFEF;border:3px solid #666;white-space:nowrap;'>

					<div style='color:#1e4272;line-height:35px;' align='center'><strong>Available options for Document(s) :</strong></div>
					<div id='div_MANAGECONTENT' class='rdCrn3' style='display:block;padding:15px 10px;border:1px solid #999; background-color:#FEFEFE; overflow:auto;' >

					</div>
          <BR>
          <div align='center'>
          <table class='tableReset' cellspacing=0 cellpadding=0><tr>
						<td><input id='m_action' type='submit' class='submit' value='Accept Changes' /></td>
						<td><input type='submit' class='submit' value='Cancel' onclick='hideManageScreen();' /></td>
	          <td id='btnDeliveryNoteInfo' style='display:none;'>
	             <input type='button' class='submit' value='Delivery Note Info' onclick='showDeliveryNoteInfo();' />
	          </td>
					</tr></table>

	        <!-- only shows if delivery note info btn clicked -->
	        <div id='deliveryNoteInfoWrapper' style='display:none;border:1px solid rgb(102,102,102);border-radius:6px;-webkit-border-radius:6px;padding:10px;margin:10px;' >
	        </div>

	        </div>
        <BR>
			</div>";

?>

<SCRIPT type="text/javascript" defer>
adjustMyFrameHeight();
function getRowIndex(e) {
	var r=$(e).closest('tr')[0].rowIndex-1;
	return r;
}

<?php
/******************************************
 * START : DEPOT-PRINCIPAL Functions
 ******************************************/

if ((CommonUtils::isDepotUser()) || ($hasRoleManageQuotation)) {
?>

var isLoadedManage=false; // see WARNING comments against .load below

function showManageBulk() {

  var selectedArr = new Array();
  var no = 0;
  $('input[name="MANAGE_ITEM[]"]').each(function() {
      if($(this).attr('checked')){
        selectedArr[no] = $(this).val();
        no++;
      }
  });

  if(selectedArr.length==0){
    alert('No documents have been selected!');
  } else {
    displayManage();
    var param = '?pAlias=&BULKACTION=1&DOCMASTARR[]=' + selectedArr.join('&DOCMASTARR[]=');
    $('#div_MANAGECONTENT').load('<?php echo $ROOT.$PHPFOLDER."functional/transaction/manageDocument.php" ?>'+param, isLoadedManage=false);
  }

}


// deliveryNote is depot.delivery_note needs to be printed as a companion ~ show additional fields as well
function showManage(dmUId, rowIndex, thisField, deliveryNote) {
  active_dmUId = dmUId;
  displayManage(deliveryNote);
  // WARNING : The .load() jquery event causes scripts to be executed twice in IE. The page is not fetched twice.
  // 					 The content is passed to .html() prior to scripts being removed. This executes the script blocks before they are discarded and again after.
  //					 This is because JQuery uses the html() to parse the innerHTML when it is received which executes the script and again when it is inserted into DOM
  $('#div_MANAGECONTENT').load('<?php echo $ROOT.$PHPFOLDER."functional/transaction/manageDocument.php?pAlias=&DOCMASTID=" ?>'+dmUId, isLoadedManage=false);
}

//deliveryNote is depot.delivery_note needs to be printed as a companion ~ show additional fields as well
function displayManage(deliveryNote){

  if ((typeof deliveryNote == "undefined") || (deliveryNote===false)) deliveryNote='N';

  $('#div_MANAGECONTENT').html("<center style='background-color:white;'><img src='<?php echo $DHTMLROOT.$PHPFOLDER ?>images/loading.gif' /></center>");
  // unfortunately the modal layer in parent doesnt work with z-index here
  $('#viewTracking').append("<div id='vt_modalLayer' "+
                            " style='position:absolute; top:0; left:0; width:100%; height:100%; z-index:99; background-image:url(\"<?php echo $DHTMLROOT.$PHPFOLDER; ?>images/modal-layer.png\");'>"+
                            "</div>");

  //$('#div_MANAGE,#div_MANAGECONTENT').css({'height':'','max-height':''}); // reset the height so max-height doesnt use the last size
  $('#div_MANAGE').css({'marginTop':f_scrollTop()+30,'marginLeft':(f_clientWidth()-750)/2,'max-height':f_clientHeight()-100}).show(500,
  function(){
                  // $('#div_MANAGECONTENT').css({'height':$('#div_MANAGE').css('max-height').replace('px','')-80});
  }
  );
  $('#m_action').prop("onclick", null).attr("onclick", null); // remove the action event, returning val will set the action, also acts as a safety
  $('#m_action').unbind('click');

  // delivery note info handling
  $('#deliveryNoteInfoWrapper')
         .css({'display':'none'})
         .html(''); // the wrapper
  if (deliveryNote=='Y') {
    $('#btnDeliveryNoteInfo').css({'display':'table-cell'}); // the btn
  } else {
    $('#btnDeliveryNoteInfo').css({'display':'none'});
  }

}


var isLoadedLink=false; // see WARNING comments against .load below
function showLink(dmUId,rowIndex, thisField) {
	// unfortunately the modal layer in parent doesnt work with z-index here
	$('#viewTracking').append("<div id='vt_modalLayer' "+
                                  "	style='position:absolute; top:0; left:0; width:100%; height:100%; z-index:99; background-image:url(\"<?php echo $DHTMLROOT.$PHPFOLDER; ?>images/modal-layer.png\");'>"+
                                  "</div>");

	$('#div_MANAGE,#div_MANAGECONTENT').css({'height':'','max-height':''}); // reset the height so max-height doesnt use the last size
	$('#div_MANAGE').css({'marginTop':f_scrollTop()+20,'marginLeft':f_scrollLeft()+f_clientWidth()/2-350,'max-height':f_clientHeight()-100+'px'}).show(700,
        function(){
                        $('#div_MANAGECONTENT').css({'height':$('#div_MANAGE').css('max-height').replace('px','')-80});
        }
        );

	$('#m_action').prop("onclick", null).attr("onclick", null); // remove the action event, returning val will set the action, also acts as a safety
	$('#m_action').unbind('click');

  // WARNING : The .load() jquery event causes scripts to be executed twice in IE. The page is not fetched twice.
  // 					 The content is passed to .html() prior to scripts being removed. This executes the script blocks before they are discarded and again after.
  //					 This is because JQuery uses the html() to parse the innerHTML when it is received which executes the script and again when it is inserted into DOM
	$('#div_MANAGECONTENT').load('<?php echo $ROOT.$PHPFOLDER."functional/transaction/manageLink.php?pAlias=&DOCMASTID=" ?>'+dmUId,
															 isLoadedLink=false);
}
// called when "Accept Changes" clicked
function manageAccept(dmUIdlist) {

    var pageGroup = <?php echo $postSCRUSAGE; ?>,
        skipInPickStage = '<?php echo $skipInPickStage; ?>',
        params='';

    var status = convertElementToArray(document.getElementsByName('MD_STATUS'));


    // get additional page fields for params
    if (((pageGroup==3) || (pageGroup==2 && skipInPickStage=='Y')) && (status=="I")) { // inpick changing to Invoiced
      params='&DDUID='+convertElementToArray(document.getElementsByName("MD_DDUID[]"));
      params+='&DOCMASTLIST='+dmUIdlist;
      params+='&AMENDEDQTY='+convertElementToArray(document.getElementsByName("MD_AMENDEDQTY[]"));
      params+='&ACCEPTQTY='+convertElementToArray(document.getElementsByName("MD_ACCEPTQTY"));

      // batch may contain CSV so that needs to be manually escaped so cant call convertElementToArray()
      var arr = new Array(),
          fld = document.getElementsByName("MD_BATCH[]");
    	for (var i=0; i<fld.length; i++) {
    		arr.push(fld[i].value.replace(',','|'));
    	}

      params+='&BATCH='+arr;
    } else if ((status=="I") && ('<?php echo ((!CommonUtils::isDepotUser())?1:2); ?>'=='1')) {
      // quotations
      params='&DDUID='+convertElementToArray(document.getElementsByName("MD_DDUID[]"));
      params+='&DOCMASTLIST='+dmUIdlist;
      params+='&AMENDEDQTY='+convertElementToArray(document.getElementsByName("MD_AMENDEDQTY[]"));
      params+='&ACCEPTQTY='+convertElementToArray(document.getElementsByName("MD_ACCEPTQTY"));
    }

    if (status=="A") {

      submitAction('ACCEPT',dmUIdlist,params,'refreshStatus("'+dmUIdlist+'",msgClass);');

    } else if (status=="AMEND-USE-CAPTURE") {
      var parentBody = window.parent.document.body;
      $("#content", parentBody).attr('src','<?php echo HOST_SURESERVER_AS_USER.$PHPFOLDER; ?>functional/transaction/quotationCapture.php?DOCMASTID='+dmUIdlist);

    } else if (status=="C") {

      // skip if not depot user ie. is quotation mgmnt
      var comment = '';
      if ($('#MD_COMMENT').length>0) {
        comment = encodeURIComponent(document.getElementById('MD_COMMENT').value);
        params+='&REASONCODE='+document.getElementById('MD_REASONCODE').value;
      }
      submitAction('CANCEL',dmUIdlist,'COMMENT='+comment+params,'refreshStatus("'+dmUIdlist+'",msgClass);');

    } else if (status=="I") {

      submitAction('INVOICE',dmUIdlist,params,'successCallBack("'+dmUIdlist+'",<?php echo ((CommonUtils::isDepotUser())?"true":"false") ?>); refreshStatus("'+dmUIdlist+'",msgClass);');

    } else if (status=="IN-PROGRESS") {

      submitAction('IN-PROGRESS',dmUIdlist,params,'successCallBack("'+dmUIdlist+'", false); refreshStatus("'+dmUIdlist+'",msgClass);');

    } else if (status=="JOB-COMPLETE") {

      submitAction('JOB-COMPLETE',dmUIdlist,params,'successCallBack("'+dmUIdlist+'", false); refreshStatus("'+dmUIdlist+'",msgClass);');

    } else if (status=="IP") {

      submitAction('INPICK',dmUIdlist,params,'successCallBack("'+dmUIdlist+'"); refreshStatus("'+dmUIdlist+'",msgClass);');

    } else if (status=="DF") {

      params='&DELDATE='+document.getElementById("MD_DELDATE").value;
      params+='&DOCMASTLIST='+dmUIdlist;
      params+='&GRVNO='+document.getElementById("MD_GRVNO").value;
      params+='&WAYBILLNO='+document.getElementById("MD_WAYBILLNO").value;
      params+='&COMMENT='+encodeURIComponent(document.getElementById("MD_COMMENT").value.replace(/'/g,'').replace(/"/g,''));

      submitAction('DELFULL',dmUIdlist,params,'refreshStatus("'+dmUIdlist+'",msgClass);');

    } else if (status=="DP") {

      params='&DELDATE='+document.getElementById("MD_DELDATE").value;
      params+='&DOCMASTLIST='+dmUIdlist;
      params+='&GRVNO='+document.getElementById("MD_GRVNO").value;
      params+='&WAYBILLNO='+document.getElementById("MD_WAYBILLNO").value;
      params+='&COMMENT='+encodeURIComponent(document.getElementById("MD_COMMENT").value.replace(/'/g,'').replace(/"/g,''));

      //credit info.
      params+='&CLAIMNO='+document.getElementById("MD_CLAIMNO").value;
      params+='&DDUID='+convertElementToArray(document.getElementsByName("MD_DDUID[]"));
      params+='&AMENDEDQTY='+convertElementToArray(document.getElementsByName("MD_AMENDEDQTY[]"));
      params+='&ACCEPTQTY='+convertElementToArray(document.getElementsByName("MD_ACCEPTQTY"));

      submitAction('DELPART',dmUIdlist,params,'refreshStatus("'+dmUIdlist+'",msgClass);');

    } else {
      // no change, orig value
    }
}
function manageSetLink(dMUId,psmChildUId,psmParentUId) {
	var s=((psmParentUId===false)?document.getElementById('MS_STORE').value:psmParentUId);
	if (s=="") {
  	alert('Please choose a Store in the search box first.');
  	return false;
	} else {
		if (alreadySubmitted){
			alert('You have already clicked on Link Submit... If you are sure the link is not stored then you may click submit again after 2 minutes.');
			return;
		}
		alreadySubmitted=true;

		var postJS='refreshAssociation('+dMUId+',msgClass); hideManageScreen();';
	  AjaxRefreshWithResult('PSMPARENTUID='+s+'&PSMCHILDUID='+psmChildUId+'&DOCMASTID='+dMUId+'&ACTIONTYPE=SETLINK',
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/manageLinkSubmit.php',
						  'alreadySubmitted=false; if(msgClass.type=="S") '+ postJS + ';',
						  'Please wait while request is processed...');
	}
}
// does not create link, only uses a store
function manageUseLink(dMUId,psmParentUId) {
  if (alreadySubmitted){
          alert('You have already clicked on Link Submit... If you are sure the link is not stored then you may click submit again after 2 minutes.');
          return;
  }
  alreadySubmitted=true;

  var postJS='refreshAssociation('+dMUId+',msgClass); hideManageScreen();';
  AjaxRefreshWithResult('PSMPARENTUID='+psmParentUId+'&DOCMASTID='+dMUId+'&ACTIONTYPE=USELINK',
                                    '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/manageLinkSubmit.php',
                                    'alreadySubmitted=false; if(msgClass.type=="S") '+ postJS + ';',
                                    'Please wait while request is processed...');
}
function manageRemoveLink(dMUId,assocUId) {
  if (alreadySubmitted){
          alert('You have already clicked on Link Submit... If you are sure the link is not stored then you may click submit again after 2 minutes.');
          return;
  }
  alreadySubmitted=true;

  var postJS='$("#tr"+'+assocUId+').remove();';
  AjaxRefreshWithResult('ASSOCUID='+assocUId+'&DOCMASTID='+dMUId+'&ACTIONTYPE=REMOVELINK',
					  '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/manageLinkSubmit.php',
					  'alreadySubmitted=false; if(msgClass.type=="S") '+ postJS + ';',
					  'Please wait while request is processed...');
}

function refreshStatus(dmUIdlist, msgClass) {

  var dmUIdArr = dmUIdlist.split(',');
  for(i =0 ; i < dmUIdArr.length; i++ ){
    dmUId = dmUIdArr[i];
    if (document.getElementById('status'+dmUId)) {
      document.getElementById('status'+dmUId).innerHTML=msgClass.identifier;
      $('#DMUID_'+dmUId).closest('tr').css({'background':'#CCCCCC'});
      $('#DMUID_'+dmUId).closest('tr').children('td').css({'background':'#CCCCCC'});
      $('#APANEL_'+dmUId).remove();   //remove single options
      $('#CHECKITEM_'+dmUId).remove();  //remove checkboxes

    }
  }
  hideManageScreen();

}
function refreshAssociation(dmUId, msgClass) {
	try {
	  eval(msgClass.identifier);
	} catch (e) {
	  alert('identifier could not be eval() in refreshAssociation! Please contact RT.');
	  return false;
	}

  if (document.getElementById('delloc'+dmUId)) {
    document.getElementById('delloc'+dmUId).innerHTML=msgClassIdentifier.delloc;
  }
  if (document.getElementById('delarea'+dmUId)) {
    document.getElementById('delarea'+dmUId).innerHTML=msgClassIdentifier.delarea;
  }
  if (document.getElementById('delday'+dmUId)) {
    document.getElementById('delday'+dmUId).innerHTML=msgClassIdentifier.delday;
  }
}


function selectAll(flag, className){
  $("."+className).each( function(){$(this).attr("checked",((flag == 1)?true:false));})
}

 function changeScreenUsage(optVal) {
    var l=window.location.toString().replace(/[?].*$/g,'');
    if(optVal == 5){
     window.location=l+'?SCRUSAGE='+optVal;
    } else {
     window.location=l+'?AUTOSUBMIT=1&SCRUSAGE='+optVal;
    }
  }

<?php
} // end show depot functions

/******************************************
 * END : DEPOT-PRINCIPAL Functions
 ******************************************/

?>

var alreadySubmitted=false;
function submitAction(actionType,dmUIdlist,extraParams,postJS){

  var params = '';
  var dmUIdArr = dmUIdlist.split(',');

  if (alreadySubmitted){
          alert('You have already clicked on submit... If you are sure the capture has NOT been stored then you may click submit again after 2 minutes.');
          return;
  }

  if(dmUIdArr.length == 0){
    alert('Document selection failure on screen!');
  } else if(dmUIdArr.length == 1){
    params='ACTIONTYPE='+actionType+'&DOCMASTID='+dmUIdArr[0]+'&'+extraParams;
  } else {
    //enable bulk action
    params='ACTIONTYPE='+actionType+'&BULKACTION=1&DOCMASTARR[]='+dmUIdArr.join('&DOCMASTARR[]=')+'&'+extraParams;
  }

  alreadySubmitted=true;
  AjaxRefreshWithResult(params,
                        '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/viewTrackingSubmit.php',
                        'alreadySubmitted=false; if(msgClass.type=="S"){'+ postJS + '};',
                        'Please wait while request is processed...');
}


function successCallBack(dmUIdlist, openPrint){

  if (typeof openPrint == 'undefined') openPrint=true;

  var dmUIdArr = dmUIdlist.split(',');
  if(dmUIdArr.length == 0){
    alert('Success Call back failure!');
  } else if(dmUIdArr.length == 1){
    if (openPrint) window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/documentCard.php?DOCMASTID='+dmUIdlist+'','myOrderProcessing','scrollbars=yes,width=750,height=600,resizable=yes');
  } else {
    if (openPrint) window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/documentCard.php?BULKACTION=1&DOCMASTID='+dmUIdlist+'','myOrderProcessing','scrollbars=yes,width=750,height=600,resizable=yes');
  }

}

function hideManageScreen() {
  active_dmUId = false;
  $("#deliveryNoteInfoWrapper").html('');
  $("#vt_modalLayer").remove();
  $("#div_MANAGE").hide(500);
  $('#div_MANAGE,#div_MANAGECONTENT').css({'height':'','max-height':''}); // reset the height so max-height doesnt use the last size
}

var isLoadedManageSF=false; // see WARNING comments against .load below
function showManageSF(dmUId) {


  $('#div_MANAGECONTENT').html("<center style='background-color:white;'><img src='<?php echo $DHTMLROOT.$PHPFOLDER ?>images/loading.gif' /></center>");

	// unfortunately the modal layer in parent doesnt work with z-index here
	$('#viewTracking').append("<div id='vt_modalLayer' "+
														"			style='position:absolute; top:0; left:0; width:100%; height:100%; z-index:99; background-image:url(\"<?php echo $DHTMLROOT.$PHPFOLDER; ?>images/modal-layer.png\");'>"+
														"</div>");

	$('#div_MANAGE,#div_MANAGECONTENT').css({'height':'','max-height':''}); // reset the height so max-height doesnt use the last size
	$('#div_MANAGE').css({'width':'550px','marginTop':f_scrollTop()+20,'marginLeft':f_clientWidth()/2-200,'max-height':f_clientHeight()-100}).show(700,
        function(){
                        // $('#div_MANAGECONTENT').css({'height':$('#div_MANAGE').css('max-height').replace('px','')-80});
        }
        );
	$('#m_action').prop("onclick", null).attr("onclick", null); // remove the action event, returning val will set the action, also acts as a safety
	$('#m_action').unbind('click');

  // WARNING : The .load() jquery event causes scripts to be executed twice in IE. The page is not fetched twice.
  // 					 The content is passed to .html() prior to scripts being removed. This executes the script blocks before they are discarded and again after.
  //					 This is because JQuery uses the html() to parse the innerHTML when it is received which executes the script and again when it is inserted into DOM
	$('#div_MANAGECONTENT').load('<?php echo $ROOT.$PHPFOLDER."functional/transaction/manageDocumentSF.php?pAlias=&DOCMASTID=" ?>'+dmUId,
															 isLoadedManageSF=false);

}

function openCard(file,param){

  AjaxRefreshHTML("",
    "<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/"+file+"?"+param,
    "cardResult",
    "Retrieving Data...",
    "");
 var html = '<div id="cardResult" style="font-size:16px;overflow:auto;height:380px;" align="center"><br><h2 style="color:#999">loading...</h2></div>';
 parent.popBox(html,'general',480);

}

var active_dmUId = false;
function showDeliveryNoteInfo() {
  $("#deliveryNoteInfoWrapper")
      .show()
      .load('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/deliveryNoteInfo.php?DMUID='+active_dmUId);
}

function printQuotationProformaInvoice(dmUId) {
  window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/documentCard.php?DOCMASTID='+dmUId+'','myQuotationProcessing','scrollbars=yes,width=750,height=600,resizable=yes');
}

function printQuotationJobCard(dmUId) {
  window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/jobCard.php?DOCMASTID='+dmUId+'','myQuotationProcessing','scrollbars=yes,width=750,height=600,resizable=yes');
}

function printQuotationCompletionCertificate(dmUId) {
  window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/quotationCompletionCertificate.php?DOCMASTID='+dmUId+'','myQuotationProcessing','scrollbars=yes,width=750,height=600,resizable=yes');
}
function copyQuotationDocument(dmUId) {
  var parentBody = window.parent.document.body;
  $("#content", parentBody).attr('src','<?php echo HOST_SURESERVER_AS_USER.$PHPFOLDER; ?>functional/transaction/quotationCapture.php?COPYDOCUMENT=Y&DOCMASTID='+dmUId);
}
</SCRIPT>
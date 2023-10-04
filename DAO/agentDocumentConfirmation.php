<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');


if (!isset($_SESSION)) session_start();
$principalId  = $_SESSION['principal_id'];
$principalCode = $_SESSION['principal_code'];
$userId       = $_SESSION['user_id'];
$principalType = $_SESSION['principal_type'];
$systemId = $_SESSION['system_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();

// on first time entry to screen, check session parameters for defaults
if ((!isset($_POST["FROMDATE"])) && (isset($_SESSION["AGENT_CONFIRMATION_PARAMS"]))) {
  $postFROMDATE = $_SESSION["AGENT_CONFIRMATION_PARAMS"]["FROMDATE"];
  $postSHOWPERSONALRECORD = $_SESSION["AGENT_CONFIRMATION_PARAMS"]["SHOWPERSONALRECORD"];
  $postSHOWGROUPRECORD = $_SESSION["AGENT_CONFIRMATION_PARAMS"]["SHOWGROUPRECORD"];
  $postGROUPBY = $_SESSION["AGENT_CONFIRMATION_PARAMS"]["GROUPBY"];
} else {
  $postFROMDATE = ((isset($_POST["FROMDATE"]))?$_POST["FROMDATE"]:CommonUtils::getUserDate($daysOffset=7));
  $postSHOWPERSONALRECORD = ((isset($_POST["SHOWPERSONALRECORD"]))?$_POST["SHOWPERSONALRECORD"]:"NS");
  $postSHOWGROUPRECORD = ((isset($_POST["SHOWGROUPRECORD"]))?$_POST["SHOWGROUPRECORD"]:"NS");
  $postGROUPBY = ((isset($_POST["GROUPBY"]))?$_POST["GROUPBY"]:"DAY(O)");
}

$adminDAO = new AdministrationDAO($dbConn);
$transactionDAO = new TransactionDAO($dbConn);

$hasRoleTT = $adminDAO->hasRole($userId,$principalId,ROLE_AGENT_DOCMENT_CONFIRMATION);

echo "<html>";
echo "<head>";
echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.js'></script>";
echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js'></script>";
echo "<scr"."ipt type=\"text/javascript\">";
echo "</script>";
DatePickerElement::getDatePickerLibs();
echo "<LINK href='".$ROOT.$PHPFOLDER."css/default.css' rel='stylesheet' type='text/css'>" ;
    echo '<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />';
echo "</HEAD>";

echo "<BODY id='viewTracking' style='font-family:arial;'><CENTER>";

// START : PARAMETERS

echo "<div style=''>";
echo "<FORM id='paramForm' action='".$_SERVER['PHP_SELF']."'  style='margin:0; padding:0;' method='POST' >";
echo "<BR><SPAN style='font-family:Verdana,Arial,Helvetica,sans-serif; font-weight:bold;font-size:0.8em;'>Parameters</SPAN>";
echo "<TABLE class='tblReset' width=\"600\">";
echo "<TR>";
  echo "<TD nowrap>Show records processed since :</TD>";
  echo "<TD >";
  DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE);
  echo "</TD>";
echo "</TR>";
echo "<TR>";
  echo "<TD>Show Personal Record of:</TD>";
  echo "<TD >";
  BasicInputElement::getCSS3RadioHorizontal("SHOWPERSONALRECORD","Accepted,Unaccepted,Not Specified","A,U,NS",$postSHOWPERSONALRECORD,$disabled = false,$onChangeJS="", $cssStyleSize = 0);
  echo "</TD>";
echo "</TR>";
echo "<TR>";
  echo "<TD>Show Group Record of:</TD>";
  echo "<TD >";
  BasicInputElement::getCSS3RadioHorizontal("SHOWGROUPRECORD","Accepted,Unaccepted,Not Specified","A,U,NS",$postSHOWGROUPRECORD,$disabled = false,$onChangeJS="", $cssStyleSize = 0);
  echo "</TD>";
echo "</TR>";
echo "<TR>";
  echo "<TD>Group results by:</TD>";
  echo "<TD >";
  BasicInputElement::getCSS3RadioHorizontal("GROUPBY","Principal,Data Source,Day (Processed),Day (Order),File","P,DS,DAY(P),DAY(O),F",$postGROUPBY,$disabled = false,$onChangeJS="", $cssStyleSize = 0);
  echo "</TD>";
echo "</TR>";
echo "<TR>";
  echo "<TD colspan=2 style='text-align:center;'>
          <input type='submit' class='submit' value='Submit' onclick='javascript:document.getElementById(\"paramForm\").submit();'>
        </TD>";
echo "</TR>";
echo "</TABLE>";
echo "</FORM>";
echo "</div><br>";

// END : PARAMETERS
echo "</center>";

if(!$hasRoleTT){
  echo "Sorry, you do not have permissions to VIEW Agent Document Confirmations!";
  return;
}


// postFROMDATE is defaulted if empty so leave out of below
if (!isset($_POST["FROMDATE"])) {
  echo "</body></html>";
  return;
}

echo "<br><table class='tableReset'><tr><td>Restrict me to only be able to accept INVOICED : </td><td>";
BasicInputElement::getCSS3RadioHorizontal("RESTRICTED","Yes,No","Y,N","Y",$disabled = false,$onChangeJS="", $cssStyleSize = 0);
echo "</td></tr></table>";

// remember the passed parameters
$_SESSION["AGENT_CONFIRMATION_PARAMS"] = array("FROMDATE"=>$postFROMDATE,
                                               "SHOWPERSONALRECORD"=>$postSHOWPERSONALRECORD,
                                               "SHOWGROUPRECORD"=>$postSHOWGROUPRECORD,
                                               "GROUPBY"=>$postGROUPBY);

// this may need to be transferred to a DB table in future
// but for the moment the requirements are unclear to make a table out of

// this keeps track of who the user keeps "an account" for to show whether anyone in the organisation has accepted the document
$mfAgent = array("1"=>"GP Harding",
                 "2"=>"Non RT Depot - MegaMor");
// 1 row per user Id (key is userid for direct lookup), Agent Id can be CSV
// At the moment it only works for 1 group (see comments in agentDocumentConfirmationSubmit.php)
$mfAgentUser = array( "366"=>"1,2" );

// You can load multiple rows for users, or principals and all fields are comma separated - including the userId
//  - Principal UIds are REQUIRED !
//  - empty value = wildcard
//  - ifEDI_OFPUId = only applies if the document is EDI, specify the OFP uids
$mfScope = array(
                  array("userId"=>"173,366",
                        "principalUId"=>"4,71",
                        "dataSource"=>DS_EDI,
                        "ifEDI_OFPUId"=>"2",
                        "documentTypeUId"=>DT_ORDINV.",".DT_UPLIFTS,
                        "documentStatusUId"=>"",
                        "depotUId"=>"",
                        "capturedBy"=>"GPH"
                        ),
                   array("userId"=>"173,366",
                        "principalUId"=>"4",
                        "dataSource"=>DS_CAPTURE,
                        "ifEDI_OFPUId"=>"",
                        "documentTypeUId"=>DT_ORDINV.",".DT_UPLIFTS.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE,
                        "documentStatusUId"=>"",
                        "depotUId"=>"",
                        "capturedBy"=>"173"
                        ),
                   array("userId"=>"173,366",
                        "principalUId"=>"70",
                        "dataSource"=>DS_CAPTURE,
                        "ifEDI_OFPUId"=>"",
                        "documentTypeUId"=>DT_ORDINV.",".DT_UPLIFTS.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE,
                        "documentStatusUId"=>"",
                        "depotUId"=>"",
                        "capturedBy"=>"168,279"
                        ),
                   array("userId"=>"173,366",
                        "principalUId"=>"104",
                        "dataSource"=>DS_EDI.",".DS_WS,
                        "ifEDI_OFPUId"=>"2",
                        "documentTypeUId"=>DT_ORDINV.",".DT_UPLIFTS.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE,
                        "documentStatusUId"=>"",
                        "depotUId"=>"",
                        "capturedBy"=>""
                        ),
                   array("userId"=>"173,366",
                        "principalUId"=>"114",
                        "dataSource"=>DS_CAPTURE,
                        "ifEDI_OFPUId"=>"",
                        "documentTypeUId"=>DT_ORDINV.",".DT_UPLIFTS.",".DT_ORDINV_ZERO_PRICE.",".DT_DELIVERYNOTE,
                        "documentStatusUId"=>"",
                        "depotUId"=>"",
                        "capturedBy"=>"173"
                        ),
                   array("userId"=>"173,366",
                        "principalUId"=>"123",
                        "dataSource"=>DS_EDI,
                        "ifEDI_OFPUId"=>"2",
                        "documentTypeUId"=>DT_ORDINV,
                        "documentStatusUId"=>"",
                        "depotUId"=>"2,3,5,6,7",
                        "capturedBy"=>""
                        ),
                   array("userId"=>"173,366",
                        "principalUId"=>"138",
                        "dataSource"=>DS_EDI,
                        "ifEDI_OFPUId"=>"2",
                        "documentTypeUId"=>DT_ORDINV.",".DT_ORDINV_ZERO_PRICE,
                        "documentStatusUId"=>"",
                        "depotUId"=>"",
                        "capturedBy"=>"GPH"
                        )

                );

// each condition above becomes an "or" condition grouping in select
$orCond = array();
$orCondOH = array();
foreach ($mfScope as $c) {
  $orCondInner = array();
  $orCondOHInner = array();
  if (in_array($userId,explode(",",$c["userId"]))) {
    $orCondInner[] = "a.principal_uid in ({$c["principalUId"]})"; // mandatory

    // the DM query
    if ($c["dataSource"]!="") $orCondInner[] = "b.data_source in ('".str_replace(",","','",$c["dataSource"])."')";
    if ((in_array(DS_EDI,explode(",",$c["dataSource"])) || ($c["dataSource"]=="")) && ($c["ifEDI_OFPUId"]!="")) $orCondInner[] = "((b.data_source='EDI' and h.online_file_processing_uid in ({$c["ifEDI_OFPUId"]})) or b.data_source!='EDI')";
    if ($c["documentTypeUId"]!="") $orCondInner[] = "a.document_type_uid in ({$c["documentTypeUId"]})";
    // document status is not applied at present because it can interfere with purpose of screen which is to show every document processed to the user, rather than by criteria so they have transparency over it depending on status.
    // if ($c["documentStatusUId"]!="") $orCondInner[] = "b.document_status_uid in ({$c["documentStatusUId"]})";
    if ($c["depotUId"]!="") $orCondInner[] = "a.depot_uid in ({$c["depotUId"]})";
    if ($c["capturedBy"]!="") $orCondInner[] = "b.captured_by in ('".str_replace(",","','",$c["capturedBy"])."')";

    if ($postSHOWGROUPRECORD=="A") $orCondInner[] = "(d2.data_uid is not null or d.uid is not null)";
    else if ($postSHOWGROUPRECORD=="U") $orCondInner[] = "d2.data_uid is null and d.uid is null";
    else if ($postSHOWGROUPRECORD=="NS") {};

    if ($postSHOWPERSONALRECORD=="A") $orCondInner[] = "d.uid is not null";
    else if ($postSHOWPERSONALRECORD=="U") $orCondInner[] = "d.uid is null";
    else if ($postSHOWPERSONALRECORD=="NS") {};

    // the OH query
    $dsArr = explode(",",$c["dataSource"]);
    if ((in_array(DS_EDI,$dsArr)) || (in_array(DS_WS,$dsArr)) || ($c["dataSource"]=="")) {
      $orCondOHInner[] = "a.principal_uid in ({$c["principalUId"]})"; // mandatory alse servers purpose of ensuring OH is queried as sizeof() > 0

      if ($c["ifEDI_OFPUId"]!="") {
       $orCondOHInner[] = "((data_source='EDI' and online_file_processing_uid in ({$c["ifEDI_OFPUId"]})) or a.data_source!='EDI')";
      }
      if ($c["documentTypeUId"]!="") $orCondOHInner[] = "document_type_uid in ({$c["documentTypeUId"]})";
      if ($c["capturedBy"]!="") $orCondOHInner[] = "a.captured_by in ('".str_replace(",","','",$c["capturedBy"])."')";
      // depot cannot be applied because it may not be set on OH
      // document status uid is applied at OH level in a very limited way so that the user has visibility of alll documents pending
      // if (($c["documentStatusUId"]!="") && (in_array(DST_UNACCEPTED,$dsArr))) $orCondOHInner[] = "document_type_uid in ({$c["documentTypeUId"]})";

      $orCondOH[] = "(" . implode(" and ",$orCondOHInner) . ")";
    }

    $orCond[] = "(" . implode(" and ",$orCondInner) . ")";

  }
}

if(empty($orCond)) {
  echo "User does not have any conditions defined for data retrieval.";
  return;
}

if ($postGROUPBY=="P") {
  $orderBy = "principal_uid, file_log_uid, order_date, document_number";
  $fieldToUse = "principal_uid";
  $headerFIeldToUse = "principal_name";
} else if ($postGROUPBY=="DS") {
  $orderBy = "data_source, principal_uid, file_log_uid, order_date, document_number";
  $fieldToUse = "data_source";
  $headerFIeldToUse = "data_source";
} else if ($postGROUPBY=="DAY(P)") {
  $orderBy = "processed_date desc, principal_uid, file_log_uid, order_date, document_number";
  $fieldToUse = "processed_date";
  $headerFIeldToUse = "processed_date";
} else if ($postGROUPBY=="DAY(O)") {
  $orderBy = "order_date desc, principal_uid, file_log_uid, document_number";
  $fieldToUse = "order_date";
  $headerFIeldToUse = "order_date";
} else if ($postGROUPBY=="F") {
  $orderBy = "file_log_uid desc, processed_date desc, order_date desc, principal_uid, document_number";
  $fieldToUse = "incoming_file";
  $headerFIeldToUse = "incoming_file";
} else {
  // should never occur.
  echo "Missing Group By Parameter";
  return;
}

// note that the DM is processed date but OH is filtered on created date as we want to avoid situation of half file going through much later than rest and filter only showing half of file
$sql="select *
      from
      (
        select a.uid dm_uid, a.document_number, e.description document_type, f.description document_status, a.principal_uid, g.name principal_name,
               d.uid se_uid, d2.data_uid se_uid_alt, a.file_log_uid, b.data_source, h.online_file_processing_uid, b.document_status_uid,
               b.order_date, a.processed_date, i.name depot_name, j.deliver_name store_name, b.customer_order_number, a.incoming_file,
               k.client_document_number, 'DM' tbl_src, d2.acpt_grp
        from   document_master a
              inner join document_header b on a.uid = b.document_master_uid
              left join smart_event d on a.uid = d.data_uid and
                                d.type = 'AGNTVERIFIED' and
                                d.type_uid = {$userId}
              left join (select d2.data_uid, group_concat(d2.general_reference_1 separator ',') acpt_grp from smart_event d2 where d2.type = 'AGNTVERIFIED' and d2.type_uid != {$userId} group by d2.data_uid) d2 on a.uid = d2.data_uid
              left join document_type e on a.document_type_uid = e.uid
              left join `status` f on b.document_status_uid = f.uid
              left join principal g on a.principal_uid = g.uid
              left join file_log h on a.file_log_uid = h.uid
              left join depot i on a.depot_uid = i.uid
              left join principal_store_master j on b.principal_store_uid = j.uid
              left join orders k on k.order_sequence_no = a.order_sequence_no
        where  a.processed_date >= '{$postFROMDATE}'
        and    (".implode(" or ",$orCond).")
        union all
        select null dm_uid, a.document_number, e.description document_type, a.`status` document_status, a.principal_uid, g.name principal_name,
               null se_uid, null se_uid_alt, a.file_log_uid, a.data_source, a.online_file_processing_uid, null document_status_uid,
               a.order_date, a.created_date, '(no depot assigned yet)' depot_name, if(ifnull(a.deliver_name,'')='','(no store assigned yet)',a.deliver_name) store_name, a.reference customer_order_number, a.incoming_file,
               a.client_document_number, 'OH' tbl_src, null acpt_grp
        from   orders_holding a
                  left join document_type e on a.document_type_uid = e.uid
                  left join principal g on a.principal_uid = g.uid
        where  a.created_date >= '{$postFROMDATE}'
        and    a.status != 'S'
        and    (".implode(" or ",$orCondOH).")
      ) a
      order  by ".$orderBy;

$rs=$dbConn->dbGetAll($sql);


if (empty($rs)) {
  echo "No Documents Found";
  return;
}

// get the counts
// - remember that the counts for group exclude those accepted by other groups the user is not a member of, and is true if either personal or group is set
// - this differs from the icon tick which is only ticked for group if group is directly set for member agents. It is not an either case.
$cnt=array();
foreach ($rs as $doc) {
  if (!isset($cnt[$doc[$fieldToUse]]["unaccepted"])) {
    $cnt[$doc[$fieldToUse]]["unaccepted"]=0;
    $cnt[$doc[$fieldToUse]]["per_unaccepted"]=0;
    $cnt[$doc[$fieldToUse]]["total"]=0;
  }

  if ($doc["tbl_src"]=="OH") {
    $cnt[$doc[$fieldToUse]]["total"]++; // so you can see if anthing is in group
    continue; // must come after initialisation
  }

  $agentInGroup=agentIsInGrp($acptGrp=$doc["acpt_grp"]);
  if (($doc["se_uid"]=="") && (($doc["se_uid_alt"]=="") && ($agentInGroup))) {
   $cnt[$doc[$fieldToUse]]["unaccepted"]++;
  }
  if ($doc["se_uid"]=="") {
   $cnt[$doc[$fieldToUse]]["per_unaccepted"]++;
  }

  $cnt[$doc[$fieldToUse]]["total"]++;
}

// the order by in select will make sure that rows are ordered properly by group
$groupCtrl=false;
$i=0;
echo "<style>
        .agentOH {
          color:gray;
        }
      </style>";
foreach ($rs as $doc) {
  if ((trim($doc[$fieldToUse])!=$groupCtrl) || (trim($doc[$fieldToUse])=="" && $groupCtrl===false)) {
    if ($groupCtrl!==false) {
      echo "</table>
            </div><br>";
    }
    $groupCtrl = trim($doc[$fieldToUse]);

    // color for summary in title bar
    if ($cnt[$doc[$fieldToUse]]["unaccepted"]!=0) $color="#fc2f34";
    else if ($cnt[$doc[$fieldToUse]]["per_unaccepted"]!=0) $color="#f9923f";
    else $color="#e5ebdd";

    echo "<div style='border:1; border-style:solid; border-color:#f1f1f1; '>
          <span style='width:100%; background-color:#f8feff; color:#302e2e; text-align:left;padding:2px;'>
          <a href='javascript:' onclick='javascript:expandDiv({$i});' ><img id='img{$i}' style='text-decoration:none; border:0;' src='".HOST_SURESERVER_AS_USER.HOST_SURESERVER_PHPFOLDER_AS_USER."images/plus_grey.png' /></a>&nbsp;&nbsp;&nbsp;
          Group : ".$doc[$headerFIeldToUse]."&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
          <span id='grpHdr{$i}' style='color:{$color}; font-size:11px;'>UnAccepted {$cnt[$doc[$fieldToUse]]["unaccepted"]}&nbsp;({$cnt[$doc[$fieldToUse]]["total"]})&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
          UnAccepted (Personally) {$cnt[$doc[$fieldToUse]]["per_unaccepted"]}&nbsp;({$cnt[$doc[$fieldToUse]]["total"]})</span>
          </span>
          <table id='tbl{$i}' class='tableReset' style='text-align:left; display:none;'>
          <tr>
              <th>Group<br>Accepted</th>
              <th>Personally<br>Accepted</th>
              <th>Principal</th>
              <th>Document Number</th>
              <th>Client Document Number</th>
              <th>Reference</th>
              <th>Order Date</th>
              <th>Store</th>
              <th>Depot</th>
              <th>Incoming File</th>
              <th>Status</th>
          </tr>";
    $grpHdrName = "grpHdr{$i}";
  }

  // fields that vary by table source
  if ($doc["tbl_src"]=="DM") {
    $iconName = "icon{$i}";
    $status = $doc["document_status"];
    $invoiced=((in_array($doc["document_status_uid"],array(DST_INVOICED,DST_DIRTY_POD,DST_DELIVERED_POD_OK)))?"Y":"N");
    $agentInGroup=agentIsInGrp($acptGrp=$doc["acpt_grp"]);
    $authorisationPersonal = "<a href='javascript:;' onclick='setAccept({$doc["dm_uid"]},\"{$grpHdrName}\",\"{$iconName}\",\"{$invoiced}\");' >".(($doc["se_uid"]=="")?"<img id='{$iconName}' style='border:0; text-decoration:none;' src='".HOST_SURESERVER_AS_USER.HOST_SURESERVER_PHPFOLDER_AS_USER."images/not_approved_16x16.png' alt='Accept' />":"<img id='{$iconName}' style='border:0; text-decoration:none;' src='".HOST_SURESERVER_AS_USER.HOST_SURESERVER_PHPFOLDER_AS_USER."images/approve_16x16.gif' alt='UNaccept' />")."</a>";
    $authorisationGroup = (($doc["se_uid_alt"]=="" || ($doc["se_uid_alt"]!="" && !$agentInGroup))?"<img src='".HOST_SURESERVER_AS_USER.HOST_SURESERVER_PHPFOLDER_AS_USER."images/not_approved_16x16.png' />":"<img src='".HOST_SURESERVER_AS_USER.HOST_SURESERVER_PHPFOLDER_AS_USER."images/approve_16x16.gif' />");
    $documentNumber = "<a href=\"javascript:;\" onClick=\"window.open('".$ROOT.$PHPFOLDER."functional/transaction/documentCard.php?pAlias={$doc["principal_uid"]}&DOCMASTID={$doc["dm_uid"]}','myOrderProcessing{$doc["dm_uid"]}','scrollbars=yes,width=750,height=600,resizable=yes');\">{$doc["document_number"]}</a>";
  } else {
    if ($doc["document_status"]=="") $status = "Awaiting Processing";
    else if ($doc["document_status"]=="D") $status = "Deleted by User";
    else if ($doc["document_status"]=="R.A") $status = "Suspended - Requires Authorisation";
    else if ($doc["document_status"]=="R.A.MP") $status = "Suspended - Requires Authorisation";
    else $status = "Suspended - Awaiting user action due to ERROR";

    $authorisationPersonal = "";
    $authorisationGroup = "";
    $documentNumber = $doc["document_number"];
  }

  $i++;
  if ($i & 1) $class="even"; else $class="odd";
  if ($doc["tbl_src"]=="OH") $rowClass="agentOH"; else $rowClass="";
  echo "<tr class='{$class}'>
            <td class='{$rowClass}'>{$authorisationGroup}</td>
            <td class='{$rowClass}'>{$authorisationPersonal}</td>
            <td class='{$rowClass}' style='white-space:nowrap;' nowrap>{$doc["principal_name"]}</td>
            <td class='{$rowClass}'>{$documentNumber}</td>
            <td class='{$rowClass}'>{$doc["client_document_number"]}</td>
            <td class='{$rowClass}' style='white-space:nowrap;' nowrap>{$doc["customer_order_number"]}</td>
            <td class='{$rowClass}' style='white-space:nowrap;' nowrap>{$doc["order_date"]}</td>
            <td class='{$rowClass}' style='white-space:nowrap;' nowrap>{$doc["store_name"]}</td>
            <td class='{$rowClass}' style='white-space:nowrap;' nowrap>{$doc["depot_name"]}</td>
            <td class='{$rowClass}'>{$doc["incoming_file"]}</td>
            <td class='{$rowClass}' style='white-space:nowrap;' nowrap>{$status}</td>
        </tr>";

}
if (!empty($rs)) {
  echo "</table>
        </div>";
}

function agentIsInGrp($acptGrp) {
  global $mfAgentUser, $userId;

  $agents=explode(",",$mfAgentUser[$userId]);
  foreach($agents as $a) {
    if (in_array($a,explode(",",$acptGrp))) {
      return true;
    }
  }
  return false;
}

?>
<script type="text/javascript" defer>
function expandDiv(i) {
  if ($('#img'+i).attr('src').search('plus_grey')!==-1) $('#img'+i).attr('src','<?php echo HOST_SURESERVER_AS_USER.HOST_SURESERVER_PHPFOLDER_AS_USER ?>images/minus_grey.png');
  else $('#img'+i).attr('src','<?php echo HOST_SURESERVER_AS_USER.HOST_SURESERVER_PHPFOLDER_AS_USER ?>images/plus_grey.png');
  $('#tbl'+i).slideToggle(1000);
}
// toggle both ways
var alreadySubmitted=false;
function setAccept(dmUId, grpHdrName, iconName, invoiced) {
  if ((convertElementToArray(document.getElementsByName("RESTRICTED"))=="Y") && (invoiced=="N")) {
    alert('You have elected to control your acceptance condition by checking the invoiced status : This document is not invoiced and therefore cannot be accepted!');
    return;
  };
  if (alreadySubmitted){
    alert('You have already clicked on action... If you are sure the action has NOT been stored then you may click again after 2 minutes.');
    return;
  }

  alreadySubmitted=true;
  var params='DMUID='+dmUId+'&AGENT=<?php echo $mfAgentUser[$userId] ?>';
  AjaxRefreshWithResult(params,
                        '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/agentDocumentConfirmationSubmit.php',
                        'alreadySubmitted=false; if(msgClass.type=="S") successProcessing(\''+grpHdrName+'\',\''+iconName+'\');',
                        'Please wait while request is processed...');
}
function successProcessing(grpHdrName, iconName) {
  $('#'+grpHdrName).html('***Counts disabled - please requery to view tally***');
  if ($('#'+iconName).attr('src').search('approve_16x16')!=-1) $('#'+iconName).attr('src','<?php echo HOST_SURESERVER_AS_USER.HOST_SURESERVER_PHPFOLDER_AS_USER ?>images/not_approved_16x16.png');
  else $('#'+iconName).attr('src','<?php echo HOST_SURESERVER_AS_USER.HOST_SURESERVER_PHPFOLDER_AS_USER ?>images/approve_16x16.gif');
}
</script>
<?php

echo "</body>
      </html>";

?>
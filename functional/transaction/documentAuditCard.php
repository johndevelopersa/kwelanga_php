<?php

/* NB:
 * This should only be accessible by a depot user from a depot principal
 */

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
require_once($ROOT . $PHPFOLDER . "functional/main/access_control.php");
include_once($ROOT . $PHPFOLDER . "DAO/TransactionDAO.php");
include_once($ROOT . $PHPFOLDER . "properties/Constants.php");
include_once($ROOT . $PHPFOLDER . "elements/basicInputElement.php");
include_once($ROOT . $PHPFOLDER . "libs/GUICommonUtils.php");
include_once($ROOT . $PHPFOLDER . "DAO/TransactionDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/MiscellaneousDAO.php");


if (!isset($_SESSION))
session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];


if (isset($_GET['DOCMASTID']))
  $postDOCMASTID = (htmlspecialchars($_GET['DOCMASTID']));
else if (isset($_POST['DOCMASTID']))
  $postDOCMASTID = (htmlspecialchars($_POST['DOCMASTID']));
else
  $postDOCMASTID = "";

$dbConn = new dbConnect();
$dbConn->dbConnection();
$transactionDAO = new TransactionDAO($dbConn);


/* also needed for basic audit log being displayed to users eg. MrSweet invoice confirmations
 * we now filter these rows out within this card if not dept user
if(!CommonUtils::isDepotUser()){
  echo "Feature available only for Depot Users!";
  return;
}
*/


// don't do security checks on principal because not document details are revealed, and on submit, the security is then validated
$mfT = $transactionDAO->getDocumentDepotAuditLog($userId, $postDOCMASTID); // principal security check done inside
if(CommonUtils::isStaffUser()){
  $miscDAO = new MiscellaneousDAO($dbConn);
  $mfAT = $miscDAO->getDocumentAuditTrail($postDOCMASTID);
}

?>

<h3><i>Event Audit Log for Document...</i></h3>

<table class='tableReset' style='line-height:25px;background:#fff;border-left:1px solid #ccc;border-right:1px solid #ccc;font-size:10pt;'>
  <tr bgcolor='#047' height='30' >
    <th style='color:#fff;'>Event Date</th>
    <th style='color:#fff;'>Status</th>
    <th style='color:#fff;'>By Person</th>
    <th style='color:#fff;'>Comment</th>
  </tr>

<?php
$isDepotUser = CommonUtils::isDepotUser();
foreach ($mfT as $r) {
  if ((!$isDepotUser) && ($r["type"]=="DPT")) continue;

  echo "<tr style='border-bottom:1px solid #ccc;'>
          <td nowrap>{$r["activity_date"]}</td>
          <td nowrap>{$r["status_description"]}</td>
          <td>{$r["full_name"]}</td>
          <td>{$r["comment"]}</td>
        </tr>";
}

echo "</table>";

if (CommonUtils::isStaffUser()) {

  echo "<h3><i>Audit Trail (Processing) for Document...</i></h3>
        <style>
          .atTH { border:1px; border-style:dotted; border-color:gray; padding:3px;}
          .atTD { border:1px; border-style:dotted; border-color:gray; padding:3px;}
        </style>";

  echo "<table class='tableReset' style='line-height:25px;
                                          background:#fff;
                                          border-left:1px solid #ccc;
                                          border-right:1px solid #ccc;
                                          font-size:10pt;
                                          font-family:arial'
                cellspacing='0' cellpadding='0'>
        <tr bgcolor='gray' height='30' >
        <th class='atTH' style='color:#fff;'>IP</th>
        <th class='atTH' style='color:#fff;'>Change Date (RSA Time)</th>
        <th class='atTH' style='color:#fff;'>Change Type</th>
        <th class='atTH' style='color:#fff;'>Changed By</th>
        <th class='atTH' style='color:#fff;'>Old Status</th>
        <th class='atTH' style='color:#fff;'>Old Status Msg</th>
        <th class='atTH' style='color:#fff;'>Old Store UId</th>
        <th class='atTH' style='color:#fff;'>Old Force Skip<br>Unique Order Ref</th>
        <th class='atTH' style='color:#fff;'>Old Order Date</th>
        <th class='atTH' style='color:#fff;'>User Action Type</th>
        </tr>";

  foreach ($mfAT as $r) {
    echo "<tr style='border-bottom:1px solid #ccc;'>
            <td class='atTD' nowrap>{$r["change_by"]}</td>
            <td class='atTD' nowrap>{$r["change_date"]}</td>
            <td class='atTD' nowrap>{$r["change_type"]}</td>
            <td class='atTD' nowrap>{$r["full_name"]} - {$r["change_by_userid"]}</td>
            <td class='atTD' nowrap>{$r["status"]}</td>
            <td class='atTD' nowrap>{$r["status_msg"]}</td>
            <td class='atTD' nowrap>{$r["principal_store_uid"]}</td>
            <td class='atTD' nowrap>{$r["force_skip_unique_order_no"]}</td>
            <td class='atTD' nowrap>{$r["order_date"]}</td>
            <td class='atTD' nowrap>{$r["user_action_status"]}</td>
          </tr>";
    }

    echo "</table>";

    if (count($mfAT)>0) {
      echo "( Each Row above displays the field values that were present on the time the change event occurred.<br>
              Therefore the current values are whatever is currently shown on the query screen. <br>
              The values against each row are not what the values were changed 'to' ! )";
    }

}


?>
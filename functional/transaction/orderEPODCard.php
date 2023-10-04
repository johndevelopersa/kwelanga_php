<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingEPODTO.php");
include_once($ROOT.$PHPFOLDER."libs/EncryptionClass.php");
include_once($ROOT.$PHPFOLDER."libs/EPODClient.php");


if (!isset($_SESSION)) session_start() ;
$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$principalName = $_SESSION['principal_name'] ;
$dbConn = new dbConnect();
$dbConn->dbConnection();
$transactionDAO = new TransactionDAO($dbConn);
$postTransactionDAO = new PostTransactionDAO($dbConn);
$encryption = new EncryptionClass();
$postDOCMASTID = (isset($_GET['DOCMASTID'])) ? ($_GET['DOCMASTID']) : (false);
$postEPODREQUEST = (isset($_GET['EPODREQUEST'])) ? ($_GET['EPODREQUEST']) : (false);
$postEPODID = (isset($_GET['EPODID'])) ? ($_GET['EPODID']) : (false);
$postEPODUPDATE = (isset($_GET['EPODUPDATE'])) ? ($_GET['EPODUPDATE']) : (false);
$postDELNOTID = (isset($_GET['DELNOTID'])) ? ($_GET['DELNOTID']) : (false);
$enableRequest = false;
$enabledComplete = false;

?>
<HTML>
<HEAD>
  <style type="text/css">
    div{color:#333;line-height:14px;}
    h1{color:#047;font-size:14pt;}
    .requestTB {background:#fff;border-collapse:collapse;}
    .requestTB td{padding:7px 10px;border:1px solid #ccc;}
    .requestTB td.nopad{padding:0px 8px;}
    .requestTB th{line-height:30px;background:#047;color:#fff;}
    </style>
    <meta name='SKYPE_TOOLBAR' content='SKYPE_TOOLBAR_PARSER_COMPATIBLE' />
</HEAD>
<BODY>
<DIV>
<?php


if($postEPODID!==false && $postEPODUPDATE!==false){


  echo "Processing Update...<br>";
  $eArr = $transactionDAO->getDocumentEPODItem($postEPODID);
  $date = date('Y-m-d', strtotime($eArr[0]['delivery_date']) + (6*86400));


  $eClient = new EPODClient();
  $update = $eClient->UpdateDeliveryNotice($postDELNOTID, $postEPODUPDATE, $remarks = '', $date, $addOptions = array('uid'=>$postEPODID));

  echo '<div style="font-size:11pt;background:#fff;border:1px solid #047;margin:20px 0px;padding:10px;width:380px;">';
  if($update->object['UpdateDeliveryNoticeResult']['ResponseCode'] == 0){

    if($postEPODUPDATE == 3){
      $msg = 'Processed';
    } else if($postEPODUPDATE == 4){
      $msg = 'Cancelled';
    }
    if(isset($update->object['UpdateDeliveryNoticeResult']['ResponseMessage'])){
      $msg = $update->object['UpdateDeliveryNoticeResult']['ResponseMessage'];
    }

    $upResult = $postTransactionDAO->postEPODUpdate($postEPODID, $msg, $postEPODUPDATE, FLAG_STATUS_CLOSED, $userId);
    if ($upResult->type == FLAG_ERRORTO_SUCCESS) {
      $result2 = mysql_query("commit", $dbConn->connection);
    } else {
      $result2 = mysql_query("rollback", $dbConn->connection);
      echo '<h2>Error!</h2>';
      echo $upResult->description;
    }

    echo $msg;

  } else {
    echo "<font color='red'>ERROR:<br>";
    echo $update->object['UpdateDeliveryNoticeResult']['ResponseMessage'];
    echo '</font>';
  }
  echo '</div>';





} else if($postDOCMASTID===false || !is_numeric($postDOCMASTID)){

  echo 'ERROR: No document master id passed!';

} else {


  //MANUAL EPOD REQUEST
  if($postEPODREQUEST == 1){

    $mfID = $transactionDAO->getDocumentEPODInvoiceDetails($postDOCMASTID);

    $postingEPODTO = new PostingEPODTO();
    $postingEPODTO->documentMasterUId = $postDOCMASTID; //unique key on document master -> disallows duplicate epod notices
    $postingEPODTO->principalStoreMasterUId = $mfID[0]['psm_uid'];
    $postingEPODTO->amount = number_format(round($mfID[0]['invoice_total'],2), 2, '.', '');
    $postingEPODTO->rsaId = $mfID[0]['epod_rsa_id'];
    if($mfID[0]['delivery_date'] != '0000-00-00'){
      $postingEPODTO->deliveryDate = date('Y-m-d', strtotime($mfID[0]['delivery_date']));
    } else {
      $postingEPODTO->deliveryDate = date('Y-m-d', strtotime($mfID[0]['invoice_date']) + 86400);
    }
    $postingEPODTO->description = $mfID[0]['deliver_name'];
    $postingEPODTO->orderNumber = $mfID[0]['customer_order_number'];
    $postingEPODTO->documentNumber = $mfID[0]['document_number'];
    $postingEPODTO->documentUrl = HOST_SURESERVER_AS_USER . SHORT_URL .'?mi='.$encryption->encryptUIDValue($postDOCMASTID, $saltMin = 1, $saltMax = 4);
    $postingEPODTO->cellphoneNumber = $mfID[0]['epod_cellphone_number'];
    $postingEPODTO->createdByUserUid = $userId;

    $queueResult = $postTransactionDAO->postEPOD($postingEPODTO); //unique key on document master -> disallows duplicate epod notices
    if ($queueResult->type == FLAG_ERRORTO_SUCCESS) {
      $result2 = mysql_query("commit", $dbConn->connection);
    } else {
      $result2 = mysql_query("rollback", $dbConn->connection);
      echo '<h2>Error!</h2>';
      echo $queueResult->description;
      die();
    }
  }


  //see if there is an entry
  $mfEI = $transactionDAO->getDocumentEPODItemByDOCMASTID($postDOCMASTID);

  //if not we treat it as a possible request epod and display information.
  if (count($mfEI)>0) {

    $enableRequest = false;
    $enabledComplete = ($mfEI[0]['epod_status_code']==1)?(true):(false);

    $deliveryNoticeRef = $mfEI[0]['delivery_notice_id'];
    $statusCode = $mfEI[0]['epod_status_code'];
    $status =  $mfEI[0]['epod_status_msg'];
    $documentNumber = $mfEI[0]['document_number'];
    $description = $mfEI[0]['description'];
    $amount = number_format(round($mfEI[0]['amount'],2), 2, '.', '');
    $orderNumber = $mfEI[0]['customer_order_number'];
    $rsaId = $mfEI[0]['rsa_id'];
    $cellphoneNumber = $mfEI[0]['cellphone_number'];
    $deliveryDate = $mfEI[0]['delivery_date'];
    $documentUrl = $mfEI[0]['document_url'];

  } else {

    $mfID = $transactionDAO->getDocumentEPODInvoiceDetails($postDOCMASTID);
    if($mfID[0]['document_status_uid'] == DST_INVOICED){
      $enableRequest = true; //show the request button.
    }
    $enabledComplete = false;


    $deliveryNoticeRef = '';
    $status = 'No notice generated';
    $statusCode = 0;
    $documentNumber = $mfID[0]['document_number'];
    $description = $mfID[0]['deliver_name'];
    $amount = number_format(round($mfID[0]['invoice_total'],2), 2, '.', '');
    $orderNumber = $mfID[0]['customer_order_number'];
    $rsaId = $mfID[0]['epod_rsa_id'];
    $cellphoneNumber = $mfID[0]['epod_cellphone_number'];
    if($mfID[0]['delivery_date'] != '0000-00-00'){
      $deliveryDate = date('Y-m-d', strtotime($mfID[0]['delivery_date']));
    } else {
      $deliveryDate = date('Y-m-d', strtotime($mfID[0]['invoice_date']) + 86400);
    }


    $documentUrl = HOST_SURESERVER_AS_USER . SHORT_URL .'?mi='.$encryption->encryptUIDValue($postDOCMASTID, $saltMin = 1, $saltMax = 4);

  }


  echo "<strong>Customer is enabled for EPOD deliveries.</strong>";
  echo "<br><br>";

  if($enableRequest){
    echo '<input class="submit" type="submit" value="Request Payment" onclick="popBoxClose();content.openCard(\'orderEPODCard.php\',\'DOCMASTID='.$mfID[0]['uid'].'&EPODREQUEST=1\');">';
    echo "<br><br>";
  }

  if($enabledComplete){
    echo 'Update: <input class="submit" type="submit" value="Delivered" onclick="popBoxClose();content.openCard(\'orderEPODCard.php\',\'EPODID='.$mfEI[0]['uid'].'&DELNOTID='.$mfEI[0]['delivery_notice_id'].'&EPODUPDATE=3\');">';
    echo "&nbsp;&nbsp;";
    echo '<input class="submit" type="submit" value="Cancelled" onclick="popBoxClose();content.openCard(\'orderEPODCard.php\',\'EPODID='.$mfEI[0]['uid'].'&DELNOTID='.$mfEI[0]['delivery_notice_id'].'&EPODUPDATE=4\');">';
    echo "<br><br>";
  }

  echo '<table class="requestTB"><tr>';
    echo '<th colSpan="2">Electronic Proof Of Delivery - Information</th>';
  echo '</tr><tr>';
      echo '<td>Delivery Notice Ref</td><td>' . $deliveryNoticeRef . '</td>';
    echo '</tr><tr>';
    echo '<td>Status</td><td style="color:red">' . $status . '</td>';
  echo '</tr><tr>';
    echo '<td>Document No.</td><td>'.$documentNumber.'</td>';
  echo '</tr><tr>';
    echo '<td>Description</td><td>'.$description.'</td>';
  echo '</tr><tr>';
    echo '<td>Amount</td><td>' . $amount . '</td>';
  echo '</tr><tr>';
    echo '<td>Customer Order No.</td><td>'.$orderNumber.'</td>';
  echo '</tr><tr>';
    echo '<td>Customer RSA ID</td><td>'.$rsaId.'</td>';
  echo '</tr><tr>';
    echo '<td>Customer Cellphone No.</td><td>'.$cellphoneNumber.'</td>';
  echo '</tr><tr>';
    echo '<td>Delivery Date</td><td>'.$deliveryDate.'</td>';
  echo '</tr><tr>';
    echo '<td>Document URL</td><td><a href="'.$documentUrl.'" target="_blank" style="color:#F90;font-weight:normal">[mobile version]</a></td>';
  echo '</tr></table>';
  echo '<div style="text-align:center; color:grey; font-size:7pt;">';
  echo '</div>';
  echo '<br>';


}


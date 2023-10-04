<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."functional/import/ordersHoldingProcessing.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");

if (!isset($_SESSION)) session_start() ;
$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

$postOHUID = (int)($_GET['OHUID']??false);

if ($postOHUID=="") {
  echo "Missing Parameter";
  return;
}

$OHD = new OrdersHoldingDocument($dbConn);
list($status, $result) = $OHD->getOHDocument($postOHUID);

if ($status!==true) {
  echo "<p style='font-weight:bold'>Document failed Validation with :</p>";
  foreach($result as $r) {
    echo "<p>".GUICommonUtils::translateOHExceptionStatus($r)."</p>";
  }
  return;
}

// add additional details to the TO
if (!isset($result->mfPS)) {
  if (isset($result->storeChainUId)) $mfPS = $OHD->storeDAO->getPrincipalStoreItem($result->storeChainUId);
  else $mfPS = [];
}
else $mfPS = $result->mfPS;
if (count($mfPS)==0) {
  echo "Store could not be determined";
  return;
}
$result->storeName = $mfPS[0]["store_name"];

$depotUId = ((strval($result->processedDepotUId)!="")?$result->processedDepotUId:$mfPS[0]["depot_uid"]);
$mfD = $OHD->depotDAO->getDepotItem($depotUId);
if (count($mfD)==0) {
  echo "Depot could not be determined";
  return;
}
$result->depotName = $mfD[$depotUId]["depot_name"];


if (intval($result->captureUserUId)!=0) {
  $administrationDAO = new AdministrationDAO($dbConn);
  $mfU = $administrationDAO->getUserItem($result->captureUserUId);
  $result->capturePersonName = $mfU[0]["full_name"];
} else {
  $result->capturePersonName = $result->capturedBy;
}


?>

<!DOCTYPE html>
<HTML>
  <TITLE>Document - View</TITLE>
<HEAD>
  <STYLE type="text/css">

    #wrapper{width:700px;text-align:left;}
    .detail {border:1px solid #aaa;padding:2px 5px;}
    #toolbar {font-size:12px;background:#047;padding:8px 10px}
    #toolbar a img{margin:2px 5px 2px 0px;}
    #toolbar a:hover{background:aliceBlue}
    #toolbar a{margin-right:10px;float:left;background:#fff;text-align:center;display:block;border:1px solid #047;padding:0px 8px;line-height:36px;text-decoration:none;color:#666;font-weight:bold;}
    #block{background:#fff;padding:20px 15px;border:1px solid #ccc;}
    .dtitle{text-align:left;}
    /*h2{color:#000;font-size:15px;line-height:25px;letter-spacing:0.2em;margin:20px 0px 5px 0px;}*/

    /* print styles */
    @media print {
      #noprint {
          visibility:hidden;
          display:none;
      }
      #wrapper{
        border:0px;
      }
      #block{padding:10px 0px;border:0px;}
    }

    table {font-size:12px;}
    table.grid
    {
      border-collapse:collapse;
    }
    table.grid td, table.grid th
    {
    border:1px solid #aaa;
    }
    table.grid th {background:#efefef;}
    .bordUnderline{border-bottom:1px solid #333;height:30px;}

</STYLE>
<script type='text/javascript' language='javascript' src='<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js'></script>
</HEAD>

<BODY style="font-family:Verdana,Arial,Helvetica,sans-serif;margin:0px;padding:0px;">

<!-- email -->
<div align="center" >

<table id="wrapper" cellspacing="0" cellpadding="0">
  <tr>
     <td>
      <div id="noprint"><!-- HIDE THIS PRINT AREA : START /--->

      </div><!-- HIDE THIS PRINT AREA : END /--->
    </td>
  </tr>
  <tr>
     <td>
     <br>
      <div style="width:100%; text-align:right;">
        <img src="<?php echo HOST_SURESERVER_AS_USER.$PHPFOLDER; ?>images/rt_powerby.gif" style="border:1px solid #ccc;float:right;">
      </div>
    </td>
  </tr>
  <tr>
    <td>
        <br>

      <div  align="center" style="margin:30px 0px;">
        <h1>*PROFORMA* Order</h1>
        <h3><?php echo $result->depotName; ?></h3>
      </div>

        <table border="0" cellpadding="6" cellspacing="0" width="100%" class="grid">
          <tr>
            <td width="120">Company:</td>
            <td colSpan="3"><strong><?php echo $_SESSION['principal_name']; ?></strong></td>
          </tr>
          <tr>
            <td>Date:</td>
            <td width="230"><strong><?php echo $result->documentDate; ?></strong></td>
            <td width="180">Time:</td>
            <td width="150"><strong><?php echo gmdate("H:i:s"); ?></strong> GMT+0</td>
          </tr>
          <tr>
            <td>Captured By: </td>
            <td><strong><?php echo $result->capturePersonName; ?></strong></td>
            <td>Document No.:</td>
            <td style="color:red;"><strong>PROFORMA</strong></td>
          </tr>
          <tr>
            <td>Delivery Point:</td>
            <td><strong><?php echo $result->storeName; ?></strong></td>
            <td>Customer Reference No.:</td>
            <td style="color:blue;"><strong><?php echo $result->orderNumber; ?></strong></td>
          </tr>
        </table>


        <div style="margin:30px 0px 10px 0px;"><strong>Line Item Details</strong></div>

      <TABLE style='border-collapse:collapse;' cellpadding=0; cellspacing=0; width="100%">
      <TR style='padding:0; margin:0;background:#efefef;'>
              <TH nowrap class='detail' colspan="1">Product</TH>
              <TH nowrap class='detail' colspan="1">Description</TH>
              <TH nowrap class='detail' colspan="1">Order<br>Qty</TH>
              <TH nowrap class='detail' colspan="1">Doc<br>Qty</TH>
              <TH nowrap class='detail' colspan="1">Del<br>Qty</TH>
              <TH nowrap class='detail' colspan="1">Pal<br>Qty</TH>
              <TH nowrap class='detail' colspan="1">Sell<br>Price</TH>
              <TH nowrap class='detail' colspan="1">Disc<br>Val</TH>
              <TH nowrap class='detail' colspan="1">Disc<br>Ref</TH>
              <TH nowrap class='detail' colspan="1">Nett<br>Price</TH>
              <TH nowrap class='detail' colspan="1">Ext<br>Price</TH>
              <TH nowrap class='detail' colspan="1">VAT<br>Amnt</TH>
              <TH nowrap class='detail' colspan="1">VAT<br>Rate</TH>
              <TH nowrap class='detail' colspan="1">Total</TH>
      </TR>
      <?php

      $totalPallets=$totalEP=$totalVAT=$totalINV=0;
// echo "<pre>"; print_r($result);
      foreach($result->detailArr as &$row) {
              $mfPP = $OHD->productDAO->getPrincipalProductItem($principalId, $row->productUId);
              if (count($mfPP)==0) {
                echo "Product could not be determined";
                return;
              }
              $row->rtProductCode = $mfPP[0]["product_code"];
              $row->rtProductDescription = $mfPP[0]["product_description"];

              $totalPallets+=intval($row->pallets);
              echo "<TR style='padding:2; margin:0;'>
                        <TD nowrap class='detail' style='text-align:left' align='left'>{$row->rtProductCode}</TD>
                        <TD nowrap class='detail' style='text-align:left' align='left'>{$row->rtProductDescription}</TD>
                        <TD nowrap class='detail'>{$row->quantity}</TD>
                        <TD nowrap class='detail'>0</TD>";
              echo "    <TD nowrap class='detail'>0</TD>
                        <TD nowrap class='detail'>".(($row->pallets=="")?"-":$row->pallets)."</TD>";

                  echo "<TD nowrap class='detail'>".number_format(floatval($row->listPrice),2)."</TD>
                        <TD nowrap class='detail'>".number_format(floatval($row->discountValue),2)."</TD>
                        <TD nowrap class='detail'>{$row->discountReference}</TD>";
                        echo "<TD nowrap class='detail'>".number_format(floatval($row->nettPrice),2)."</TD>
                        <TD nowrap class='detail'>".number_format(floatval($row->extPrice),2)."</TD>";
                        echo "<TD nowrap class='detail'>".number_format(floatval($row->vatAmount),2)."</TD>";
                        echo "<TD nowrap class='detail'>".number_format(floatval($row->productVatRate),2)."</TD>
                        <TD nowrap class='detail'>".number_format(floatval($row->totPrice),2)."</TD>";

              echo "</TR>";
              $totalEP+=$row->extPrice;
              $totalVAT+=$row->vatAmount;
              $totalINV+=$row->totPrice;
      }

      ?>

      <!-- total line -->
      <TR style='padding:0; margin:0;'>
      <TH colspan="5"></TH>

      <?php

        echo "<TH nowrap class='detail'>{$totalPallets}</TH>";
        echo "<TH colspan='4'></TH>";
        echo "<TH nowrap class='detail'>" , number_format($totalEP,2) , "</TH>";
        echo "<TH nowrap class='detail'>" , number_format($totalVAT,2) , "</TH>";
        echo "<TH colspan='1'></TH>
              <TH nowrap class='detail'>" , number_format($totalINV,2) , "</TH>";
        ?>
      </TR>
      </TABLE>

        <br><br>

        <br><br><br>

        <div valign="top" style="text-align:left; font-size:10px;">
          <?php echo (isset($_SESSION['full_name'])?$_SESSION['full_name']:'na') . ' (' . (isset($_SESSION['user_id'])?$_SESSION['user_id']:'0') , ') '; ?>
           @ <?php echo gmdate('Y-m-d H:i:s'); ?>
        </div>

        <br><br>



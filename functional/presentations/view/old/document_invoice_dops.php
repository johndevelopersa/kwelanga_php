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

<INPUT type="hidden" value="<?php echo $mfT[0]['dm_uid'] ?>" id="DOCMASTID">
<INPUT type="hidden" value="<?php echo $mfT[0]['document_status_uid'] ?>" id="STATUSID">


<table id="wrapper" cellspacing="0" cellpadding="0">
  <tr>
     <td>
      <div id="noprint"><!-- HIDE THIS PRINT AREA : START /--->

      </div><!-- HIDE THIS PRINT AREA : END /--->
    </td>
  </tr><tr>
    <td>
        <br>

      <div  align="center" style="margin:30px 0px;">
        <h1>*COPY* Invoice</h1>
        <h3><?php echo $mfT[0]['depot_name']; ?></h3>
      </div>

        <table border="0" cellpadding="6" cellspacing="0" width="100%" class="grid">
          <tr>
            <td width="120">Company:</td>
            <td colSpan="3"><strong><?php echo $mfT[0]['principal_name']; ?></strong></td>
          </tr>
          <tr>
            <td>Date:</td>
            <td width="230"><strong><?php echo $mfT[0]['order_date']; ?></strong></td>
            <td width="180">Time:</td>
            <td width="150"><strong><?php echo $mfT[0]['processed_time']; ?></strong></td>
          </tr>
          <tr>
            <td>Captured By: </td>
            <td><strong><?php echo $mfT[0]['captured_by_name']; ?></strong></td>
            <td>Document No.:</td>
            <td style="color:red;"><strong><?php echo $mfT[0]['document_number']; ?></strong></td>
          </tr>
          <tr>
            <td>Delivery Point:</td>
            <td><strong><?php echo $mfT[0]['store_name']; ?></strong></td>
            <td>Customer Reference No.:</td>
            <td style="color:blue;"><strong><?php echo $mfT[0]['customer_order_number']; ?></strong></td>
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
      $totalPallets=0;
      foreach($mfT as $row) {
              $totalPallets+=intval($row['pallets']);
              echo "<TR style='padding:2; margin:0;'>
                        <TD nowrap class='detail' style='text-align:left' align='left'>{$row['product_code']}</TD>
                        <TD nowrap class='detail' style='text-align:left' align='left'>{$row['product_description']}</TD>
                        <TD nowrap class='detail'>{$row['ordered_qty']}</TD>
                    <TD nowrap class='detail'>{$row['document_qty']}</TD>";
                      echo "<TD nowrap class='detail'>{$row['delivered_qty']}</TD>
                        <TD nowrap class='detail'>".(($row['pallets']=="")?"-":$row['pallets'])."</TD>";

              if (!$hasRoleVP) {
                      echo "<TD nowrap colspan=\"8\" class='detail'>not authorised to view pricing</TD>";
              } else {

                      echo "<TD nowrap class='detail'>".number_format($row['selling_price'],2)."</TD>
                                <TD nowrap class='detail'>".number_format($row['discount_value'],4)."</TD>
                                <TD nowrap class='detail'>{$row['discount_reference']}</TD>";
                                echo "<TD nowrap class='detail'>".number_format($row['net_price'],4)."</TD>
                                <TD nowrap class='detail'>".number_format($row['extended_price'],4)."</TD>";
                                echo "<TD nowrap class='detail'>".number_format($row['vat_amount'],2)."</TD>";
                                echo "<TD nowrap class='detail'>".number_format($row['vat_rate'],2)."</TD>
                                <TD nowrap class='detail'>".number_format($row['total'],2)."</TD>";
              }
              echo "</TR>";
              $totalEP=$row['exclusive_total']; // not cumulative !
              $totalVAT=$row['vat_total']; // not cumulative !
              $totalINV=$row['invoice_total']; // not cumulative !
      }

      if ($mfT[0]["orders_uid"]!="") {
              $mfDP = $transactionDAO->getOrderPricingDocumentItems($mfT[0]["orders_uid"],DPL_DOCUMENT);
              $totalBulkDiscount=0;
              if (sizeof($mfDP)>0) {
                      echo "<tr><TD class='detail' colspan=\"14\" nowrap style='text-align:left;'><b>Document Bulk Discounts / Charges</b></TD></tr>";

                      include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
                      $productDAO = new ProductDAO($dbConn);

                      if (!$hasRoleVP) {
                              echo "<tr><TD class='detail' colspan=\"14\" nowrap style='text-align:left;'>Bulk Discounts / Charges found, but user not authorised to view pricing</b></TD></tr>";
                      }
                      foreach ($mfDP as $row) {
                              $suffix=($row["deal_type_uid"]==VAL_DEALTYPE_AMOUNT_OFF)?"/On":"";
                              $calculatedPrice=$row["discount_value"]*(-1);
                              $discountPrice=$row["discount_value"];
                              // override some DTs with a suffix
                              switch ($row["deal_type_uid"]) {
                                      case VAL_DEALTYPE_AMOUNT_OFF: {
                                                      $suffixDesc="(&gt;={$row["quantity"]} {$row["unit_price_type_description"]})";
                                                      break;
                                              }
                                      case VAL_DEALTYPE_PERCENTAGE: {
                                                      $suffixDesc="({$row["value"]}% {$row["unit_price_type_description"]})";
                                                      break;
                                      }
                                      default: {
                                              $suffixDesc="";
                                      }
                              }
                              $totalBulkDiscount+=floatval($discountPrice);
                              echo "<tr>
                                              <TD class='detail' colspan=4 nowrap style='text-align:left;'>{$row["description"]}{$suffixDesc}</TD>
                                              <TD class='detail' nowrap>".(GUICommonUtils::translateDealType($row["deal_type_uid"]).$suffix)."</TD>
                                              <TD class='detail' nowrap>&nbsp;</TD>
                                              <TD class='detail' nowrap>&nbsp;</TD>
                                              <TD class='detail' nowrap>".number_format($discountPrice,4)."</TD>
                                              <TD class='detail' nowrap>&nbsp;</TD>
                                              <TD class='detail' nowrap>&nbsp;</TD>
                                              <TD class='detail' nowrap>".number_format($calculatedPrice,4)."</TD>
                                              <TD class='detail' nowrap>".number_format($calculatedPrice*VAL_VAT_RATE,2)."</TD>
                                              <TD class='detail' nowrap>".number_format(VAL_VAT_RATE*100,2)."</TD>
                                              <TD class='detail' nowrap>".number_format($calculatedPrice,2)."</TD>
                                        </tr>";
                                      /*  should be part of dh totals
                              $totalINV+=$calculatedPrice+($calculatedPrice*VAL_VAT_RATE);
                              $totalVAT+=$calculatedPrice*VAL_VAT_RATE;
                              $totalEP+=$calculatedPrice;
                              */
                      }

              }
      }

      ?>

      <!-- total line -->
      <TR style='padding:0; margin:0;'>
      <TH colspan="5"></TH>

      <?php

        echo "<TH nowrap class='detail'>{$totalPallets}</TH>";

       if ($hasRoleVP) {

            echo "<TH colspan='4'></TH>";

              echo "<TH nowrap class='detail'>" , number_format($totalEP,4) , "</TH>";

              echo "<TH nowrap class='detail'>" , number_format($totalVAT,2) , "</TH>";

              echo "<TH colspan='1'></TH>
                    <TH nowrap class='detail'>" , number_format($totalINV,2) , "</TH>";
       } ?>
      </TR>
      </TABLE>

        <br><br>

        <br><br><br>

        <div valign="top" style="text-align:left; font-size:10px;">
          <?php echo (isset($_SESSION['full_name'])?$_SESSION['full_name']:'na') . ' (' . (isset($_SESSION['user_id'])?$_SESSION['user_id']:'0') , ') '; ?>
           @ <?php echo gmdate('Y-m-d H:i:s'); ?>
        </div>

        <br><br>



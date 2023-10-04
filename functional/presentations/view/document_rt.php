<HTML>
  <TITLE>Document - View</TITLE>
<HEAD>
  <STYLE type="text/css">

    #wrapper{width:700px;text-align:left;}

    #toolbar {font-size:10px;background:#047;padding:8px 10px}
    #toolbar a img{margin:2px 5px 2px 0px;}
    #toolbar a:hover{background:aliceBlue}
    #toolbar a{margin-right:10px;float:left;background:#fff;text-align:center;display:block;border:1px solid #047;padding:0px 8px;line-height:36px;text-decoration:none;color:#666;font-weight:bold;}
    #block{background:#fff;padding:20px 15px;border:1px solid #ccc;}
    .dtitle{text-align:left;}
    h2{color:#047;font-size:15px;line-height:25px;letter-spacing:0.2em;margin:20px 0px 5px 0px;}

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
</STYLE>
<script type='text/javascript' language='javascript' src='<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js'></script>
</HEAD>
<BODY style="font-family:Verdana,Arial,Helvetica,sans-serif;margin:0px;padding:0px;background:#efefef;">

<!-- email -->
<div align="center">

<table id="wrapper" cellspacing="0" cellpadding="0">
  <tr>
     <td>
      <div id="noprint"><!-- HIDE THIS PRINT AREA : START /--->
        <?php if(!isset($_GET['NOTOOLBAR'])){ ?>
        <div id="toolbar">
          <a href="javascript:;" onclick='printHandler();'><img src="<?php echo $ROOT.$PHPFOLDER ?>images/print-icon.png" border="0" alt="" align="left" > Tax Invoice</a>
          <a href="javascript:;" onclick='emailDoc();'><img src="<?php echo $ROOT.$PHPFOLDER ?>images/email-icon.png" border="0" alt="" align="left" > Email</a>
          <div style="clear:both;"></div>
        </div>
        <?php } ?>
      </div><!-- HIDE THIS PRINT AREA : END /--->
    </td>
  </tr><tr>
    <td class="dtitle">
      <h2>
        <!-- powered by -->
        <img src='<?php echo HOST_SURESERVER_AS_USER.$PHPFOLDER; ?>images/rt_powerby.gif' style="border:1px solid #ccc;float:right;" >
        <?php echo (($mfT[0]["principal_uid"]!="49")?"Copy ":""); ?>Invoice for:<br><?php echo $principalName; ?>,<br><?php echo $mfT[0]['depot_name']; ?></h2>
        <div style="clear:both;"><BR></div>
    </td>
  </tr><tr>
    <td id="block">

  <TABLE style='border-style:none; width:100%'>

<!--  <?php if ($mfT[0]['has_associated_notes']=="1") { ?> -->
<!--  <TR> -->
<!--          <TD style='font-weight:bold; font-size:0.6em; color:red; text-align:center;' colspan="4">WARNING : This document has associated credits/debits applied to it that are not shown here!</TD> -- >
<!--  </TR> -- >
<!--  <?php } ?> -->

  <!-- doc dates and ref -->
  <TR>
          <TD colspan="2" style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Customer:</SPAN><BR><SPAN style='font-size:1.2em;'><?php echo $mfT[0]['store_name']; ?></SPAN></TD>
          <TD colspan="1" style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Document Number:</SPAN><BR><SPAN style='font-size:1.2em;'><?php echo $mfT[0]['document_number']; ?></SPAN></TD>
          <TD colspan="1" style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Client Document Number:</SPAN><BR><SPAN style='font-size:1.2em;'><?php echo $mfT[0]['client_document_number']; ?></SPAN></TD>
  </TR>
  <TR>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>OrderDate:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['order_date']; ?></SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Delivery Date:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['delivery_date']; ?></SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Invoice Date:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['invoice_date']; ?></SPAN></TD>
  </TR>
  <TR>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Document Type:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['document_type_description']; ?></SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Status:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['status']; ?></SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Invoice No:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['invoice_number']; ?></SPAN></TD>
  </TR>
  <TR>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Vat No:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['vat_number']; ?></SPAN></TD>
          <TD nowrap style=''></TD>
          <TD nowrap style=''></TD>
  </TR>
  <TR>
          <TD colspan="3">&nbsp;</TD>
  </TR>
  <TR>
          <TD style='font-weight:bold; font-size:0.6em; color:#999999;' colspan="3">Delivery Details</TD>
  </TR>

  <!-- other details -->
  <TR>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Customer Order No:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['customer_order_number']; ?></SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Delivery Day:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['delivery_day']; ?></SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Cases/Units:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['cases']; ?></SPAN></TD>
  </TR>
  <TR>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Claim No:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['claim_number']; ?></SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>GRV Number:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['grv_number']; ?></SPAN></TD>
          <TD nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Related Source Docket:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['source_document_number']; ?></SPAN></TD>
  </TR>
  <TR>
          <TD colspan="3" nowrap style=''><SPAN style='font-weight:normal; font-size:0.5em; color:#999999;'>Delivery Address:</SPAN><BR><SPAN style='font-size:0.8em;'><?php echo $mfT[0]['deliver_add1']; ?>, <?php echo $mfT[0]['deliver_add2']; ?>, <?php echo $mfT[0]['deliver_add3']; ?></SPAN></TD>
  </TR>

  <TR>
          <TD colspan="3">&nbsp;</TD>
  </TR>
  </TABLE>

    </td>
  </tr><tr>
    <td class="dtitle">
      <h2>Item Line Details</h2>
    </td>
  </tr><tr>
    <td id="block">
      <STYLE>
      th.detail { padding:3; margin:0; border:1px solid #999; font-weight:bold; font-size:0.7em; }
      td.detail { padding:3; margin:0; border-bottom-style:dashed; border-bottom-width:1; border-left-style:dashed; border-left-width:1; border-color:grey; font-weight:normal; font-size:0.7em; text-align:right; }
      </STYLE>

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
                                <TD nowrap class='detail'>".number_format($row['discount_value'],2)."</TD>
                                <TD nowrap class='detail'>{$row['discount_reference']}</TD>";
                                echo "<TD nowrap class='detail'>".number_format($row['net_price'],2)."</TD>
                                <TD nowrap class='detail'>".number_format($row['extended_price'],2)."</TD>";
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

    </td>
  </tr><tr>
    <td>

      <!-- footer -->
      <BR>
      <div valign="top" style="text-align:center; color:#444; font-weight:normal; font-size:9px;">
        User: <?php echo (isset($_SESSION['user_id'])?$_SESSION['user_id']:'0') , ' - ' , (isset($_SESSION['full_name'])?$_SESSION['full_name']:'na'); ?>
         | <?php echo gmdate('Y/m/d H:i:s'); ?>
      </div>

    </td>
  </tr>
</table>

  <BR><BR><BR>
</div>
</div>


<script type='text/javascript'>
function emailDoc() {
	var params="?USERID=<?php echo $userId; ?>&OBJECTID=<?php echo EO_DOC_CARD; ?>&SUBJECT=Order Details as per Request: <?php echo $postDOCMASTID; ?>&DOCMASTID=<?php echo $postDOCMASTID; ?>";
	window.location='<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/emailUserHTML.php'+params;
}

</script>

</BODY>
</HTML>

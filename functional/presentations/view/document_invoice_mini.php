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
        <div id="toolbar">
          <a href="javascript:;" onclick='printHandler()'><img src="<?php echo $ROOT.$PHPFOLDER ?>images/print-icon.png" border="0" alt="" align="left" > PRINT</a>
          <div style="clear:both;"></div>
        </div>
      </div><!-- HIDE THIS PRINT AREA : END /--->
    </td>
  </tr><tr>
    <td>
        <br>

      <div  align="left" style="margin:30px 0px;">
        <h1>Invoice</h1>
        <h3><?php echo $mfT[0]['principal_name']; ?></h3>
      </div>

        <table border="0" cellpadding="6" cellspacing="0" width="100%" class="grid">
          <tr>
            <td width="120" style='vertical-align:top;'>Company:</td>
            <td colSpan="3">
              <strong><?php echo $mfP[0]['principal_name']; ?></strong><br>
              <?php echo $mfP[0]['postal_add1']; ?><br>
              <?php echo $mfP[0]['postal_add2']; ?><br>
              <?php echo $mfP[0]['postal_add3']; ?><br>
              <?php echo $mfP[0]['postal_add4']; ?><br>
              Tel (office):<?php echo $mfP[0]['office_tel']; ?>, <?php echo $mfP[0]['email_add']; ?>
            </td>
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
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>Reference No.:</td>
            <td style="color:blue;"><strong><?php echo $mfT[0]['customer_order_number']; ?></strong></td>
          </tr>
          <tr>
            <td style='vertical-align:top;'>Deliver To:</td>
            <td colspan ="3">
              <strong><?php echo $mfT[0]['store_name']; ?></strong><br>
              <?php echo $mfT[0]['deliver_add1']; ?><br>
              <?php echo $mfT[0]['deliver_add2']; ?><br>
              <?php echo $mfT[0]['deliver_add3']; ?>
            </td>
          </tr>
        </table>


        <div style="margin:30px 0px 10px 0px;"><strong>Line Item Details</strong></div>

      <TABLE style='border-collapse:collapse;' cellpadding=0; cellspacing=0; width="100%">
      <TR style='padding:0; margin:0;background:#efefef;'>
              <TH nowrap class='detail' colspan="1">Product</TH>
              <TH nowrap class='detail' colspan="1">Description</TH>
              <TH nowrap class='detail' colspan="1">Doc<br>Qty</TH>
              <TH nowrap class='detail' colspan="1">Total</TH>
      </TR>
      <?php
      $totalPallets=0;
      foreach($mfT as $row) {
              $totalPallets+=intval($row['pallets']);
              echo "<TR style='padding:2; margin:0;'>
                        <TD nowrap class='detail' style='text-align:left' align='left'>{$row['product_code']}</TD>
                        <TD nowrap class='detail' style='text-align:left' align='left'>{$row['product_description']}</TD>
                    		<TD nowrap class='detail' style='text-align:right' align='right'>{$row['document_qty']}</TD>";


              if (!$hasRoleVP) {
                      echo "<TD nowrap colspan=\"8\" class='detail'>not authorised to view pricing</TD>";
              } else {

                          echo "<TD nowrap class='detail' style='text-align:right' align='right' >".number_format($row['total'],2)."</TD>";
              }
              echo "</TR>";
              $totalEP=$row['cases']; // not cumulative !
              $totalVAT=$row['vat_total']; // not cumulative !
              $totalINV=$row['invoice_total']; // not cumulative !
      }

      ?>

      <!-- total line -->
      <TR style='padding:0; margin:0;'>
      <TH colspan="2"></TH>

      <?php

       if ($hasRoleVP) {


              echo "<TH nowrap class='detail' style='text-align:right' align='right'>" , number_format($totalEP,0) , "</TH>
                    <TH nowrap class='detail' style='text-align:right' align='right'>" , number_format($totalINV,2,","," ") , "</TH>";
       } ?>
      </TR>
      </TABLE>

        <br><br>

        <p><strong>Banking Details:</strong></p>
        <p><?php echo $mfP[0]["banking_details"];  ?></p>

        <br><br><br>

        <div valign="top" style="text-align:left; font-size:10px;">
          <?php echo (isset($_SESSION['full_name'])?$_SESSION['full_name']:'na') . ' (' . (isset($_SESSION['user_id'])?$_SESSION['user_id']:'0') , ') '; ?>
           @ <?php echo gmdate('Y-m-d H:i:s'); ?>
        </div>

        <br><br>



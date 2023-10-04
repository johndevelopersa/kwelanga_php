<!DOCTYPE html>
<HTML>
  <TITLE>Document - View</TITLE>
<HEAD>
  <STYLE type="text/css">

    #wrapper{width:700px;text-align:left;}

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
    border:0px solid #fff;
    }
    table.grid th {background:#efefef;}
    .bordUnderline{border-bottom:0px solid #333;height:30px;}

</STYLE>
<script type='text/javascript' language='javascript' src='<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js'></script>
</HEAD>

<BODY style="font-family: Verdana,Arial,Helvetica,sans-serif;margin:0px;padding:0px;">

<!-- email -->
<div align="center" >


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

      <div  align="center" style="margin:30px 0px;">
        <h1>Picking Slip</h1>
        <h3><?php echo $mfT[0]['depot_name'] . ' - ' . $mfT[0]['document_type_description']; ?></h3>
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


        <div style="margin:50px 0px 10px 0px;"><strong>Line Item Details</strong></div>
        <table border="0" cellpadding="6" cellspacing="0" width="100%" class="grid">
          <tr>
            <th  width="100">Product Code:</td>
            <th  width="220"><strong>Description</strong></th>
            <th  width="90">Order Qty</th>
            <th  width="90">Picked Qty</th>
          </tr>
          <?php

          foreach($mfT as $row) {

            echo "<TR style='margin:0;'>
                      <TD nowrap class='detail' style='padding:8px; text-align:left' align='left'>{$row['product_code']}</TD>
                      <TD nowrap class='detail' style='text-align:left' align='left'>{$row['product_description']}</TD>";



            echo      "<TD nowrap class='detail'>{$row['ordered_qty']}</TD>
                      <TD></TD>
                  </TR>";

          }

          ?>
        </table>

        <br><br><br>

        <table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
              <td width="80" align="left" valign="bottom"><strong>Comments:</strong></td>
              <td class="bordUnderline">&nbsp;</td>
          </tr><tr>
            <td colspan="2" class="bordUnderline">&nbsp;</td>
          </tr><tr>
            <td colspan="2" class="bordUnderline">&nbsp;</td>
          </tr>
        </table>

        <br><br>

        <table border="0" cellpadding="0" cellspacing="0" width="100%" >
          <tr>
            <td width="150" valign="bottom"><strong>Picker Packer:</td><td  class="bordUnderline">&nbsp;</td>
            <td width="100" align="right" valign="bottom"><strong>Date:&nbsp;</td><td width="200" class="bordUnderline">&nbsp;</td>
          </tr><tr>
            <td valign="bottom" ><strong>Dispatch Clerk:</td><td width="250" class="bordUnderline">&nbsp;</td>
            <td width="100" align="right" valign="bottom"><strong>Date:&nbsp;</td><td width="200" class="bordUnderline">&nbsp;</td>
          </tr><tr>
           <td valign="bottom"><strong> Operations Manager:</td><td  class="bordUnderline">&nbsp;</td>

          </tr>
        </table>

        <br><br><br>

        <div valign="top" style="text-align:left; font-size:10px;">
          Printed by: <?php echo (isset($_SESSION['full_name'])?$_SESSION['full_name']:'na') . ' (' . (isset($_SESSION['user_id'])?$_SESSION['user_id']:'0') , ') '; ?>
           @ <?php echo gmdate('Y-m-d H:i:s'); ?>
        </div>

        <br><br>



<!DOCTYPE html>
<HTML>
  <TITLE>&nbsp</TITLE>
<HEAD>
  <STYLE type="text/css">

    #wrapper{width:700px;text-align:left;}

    #toolbar {font-size:12px;background:#047;padding:8px 10px}
    #toolbar a img{margin:2px 5px 2px 0px;}
    #toolbar a:hover{background:aliceBlue}
    #toolbar a{margin-right:10px;float:left;background:#fff;text-align:center;display:block;border:1px solid #047;padding:0px 8px;line-height:36px;text-decoration:none;color:#666;font-weight:bold;}
    #block{background:#fff;padding:10px 5px;border:1px solid #ccc;}
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
    
    img {
    float: right;
    margin: 0 0 10px 10px;
		}

    

</STYLE>

<script type='text/javascript' language='javascript' src='<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js'></script>
</HEAD>

<BODY style="font-family:Verdana,Arial,Helvetica,sans-serif;margin:0px;padding:0px;">


<!-- email -->
<div align="center" id="noprint" class="disableprint" >
<table id="wrapper" cellspacing="0" cellpadding="0">
  <tr>
     <td>
      <div id="noprint"><!-- HIDE THIS PRINT AREA : START /--->
        <div id="toolbar">
          <a href="javascript:window.print();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/print-icon.png" border="0" alt="" align="left" > PRINT</a>
          <?php if ($userCategory == "P") { ?>
                   <a href="javascript:;" onclick='emailDoc();'><img src="<?php echo $ROOT.$PHPFOLDER ?>images/email-icon.png" border="0" alt="" align="left" > Email</a>
          <?php } ?>
          <div style="clear:both;"></div>
        </div>
      </div><!-- HIDE THIS PRINT AREA : END /--->
    </td>
  </tr>
</table>
</div>
<table>
	
<?php 
 $filename = "images/logos/{$principalId}.jpg";
 $file = HOST_SURESERVER_AS_USER.$PHPFOLDER.$filename;
 $logo = ((file_exists($ROOT.$PHPFOLDER.$filename))?$file:HOST_SURESERVER_AS_USER.$PHPFOLDER."images/logos/blank.jpg");
    if ($mfT[0]['status_uid'] == 76) {
?> 
<tr>
        <TD><span div style='text-align:center; font-weight: bold; font-size:1.0em;' id="printedtxt"></span><span style='text-align:right; font-weight: bold; font-size:1.5em; '>Tax Invoice</span></TD>
</tr>
<?php
  } elseif ($mfT[0]['status_uid'] == 47) {
?>
<tr>  
        <TD><span style='font-weight: bold; font-size:1.0em;'id="printedtxt"></span><span style='font-weight: bold; font-size:1.5em;'>Cancelled Document</span></TD>
</tr>
<?php
  } elseif ($mfT[0]['status_uid'] == 77) {
?>
<tr>   	
        <TD><span style='text-align:center; font-weight: bold; font-size:1.5em;'>Copy Tax Invoice</span></TD>
</tr>
<tr>   	
        <TD><span style='text-align:center; font-size:1.2em;'><?php echo $mfT[0]['status'] ?></span></TD>
</tr>   
<?php
  } elseif ($mfT[0]['status_uid'] == 78) {
?> 
<tr>  	
        <TD><span style='font-weight: bold; font-size:1.5em;'>Copy Tax Invoice</span><BR><span style='text-align:center;'> <?php echo $mfT[0]['status'] ?></span> </TD>
</tr>
<?php
  } elseif ($mfT[0]['status_uid'] == 75) {
?>
<TR>   	
        <TD><span style='font-weight: bold; font-size:1.5em;'>Order</span><BR><span style='text-align:center;'> <?php echo $mfT[0]['status'] ?></span> </TD>
</TR>
<?php
  } elseif ($mfT[0]['status_uid'] == 74) {
?>
<tr> 	
        <TD><span style='font-weight:bold; font-size:2.0em; text-align:center;'>Order</span></TD>
</tr><tr>   	
        <TD><span style='text-align:center; font-size:1.2em;'><?php echo $mfT[0]['status'] ?></span></TD>
</tr>   
<?php
}  	
?> 
</table>
      	
<TABLE style='border-style:none; width:49%; float: left;'>
     <tr>
         <td nowrap valign="bottom"><strong><?php echo $mfT[0]['principal_name']; ?></strong></td>
         <td nowrap valign="bottom"></td>
     </tr><tr>
         <td nowrap valign="bottom" ><strong><?php echo $mfT[0]['prin_add1']?></strong></td>
     </tr><tr>
         <td nowrap valign="bottom" ><strong><?php echo $mfT[0]['prin_add2']?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $mfT[0]['prin_add3']; ?></strong></td>
      </tr><tr>
         <TD colspan="4" style=''><SPAN style='font-weight:bold; color:black;'></SPAN>Tel:&nbsp;&nbsp;<SPAN style='font-size:1.2em;'><?php echo substr($mfT[0]['office_tel'],0,3).' '. substr($mfT[0]['office_tel'],3,3) .' '. substr($mfT[0]['office_tel'],6,4)?></SPAN></TD>
      </tr><tr>
      </tr><tr>
         <TD colspan="4" style=''><SPAN style='font-weight:bold; color:black;'></SPAN>VAT no:&nbsp;&nbsp;<SPAN style='font-size:1.2em;'><?php echo $mfT[0]['prin_vat'];?></SPAN></TD>
      </tr><tr>
         <?php if ($mfT[0]['export_number_enabled'] == "Y") { ?>
             <TD colspan="4" style=''><SPAN style='font-weight:bold; color:black;'></SPAN>Export No:&nbsp;&nbsp;<SPAN style='font-size:1.2em;'><?php echo $mfT[0]['export_number'];?></SPAN></TD>
         <?php } ?>         	
          <?php if (trim($mfT[0]['company_reg']) <> NULL) { ?>
             <TD colspan="4" style=''><SPAN style='font-weight:bold; color:black;'></SPAN>Company&nbsp;Reg:&nbsp;&nbsp;<SPAN style='font-size:1.2em;' nowrap ><?php echo $mfT[0]['company_reg'];?></SPAN></TD>
         <?php } ?>         	
     </tr>
    </TABLE>
    <?php echo "<img src=$logo?" . time() . "style='width:175px; height:75px ;border:0; align=top; float: right; '</img>" ?>
	  <br style='clear:both;'>
<TABLE style='border-style:none; width:100%'>         	
  <TR>
          <TD colspan="4" style=''><SPAN style='font-weight:normal; color:#999999;'>Customer Deliver to</SPAN><BR><SPAN style='font-size:1.2em;'><?php echo $mfT[0]['store_name']; ?></SPAN></TD>
          <TD colspan="4" style=''><SPAN style='font-weight:normal; color:#999999;'>Customer Invoice to</SPAN><BR><SPAN style='font-size:1.2em;'><?php echo $mfT[0]['bill_name']; ?></SPAN></TD>
          <?php if ($mfT[0]['status_uid'] >= 76 AND trim($mfT[0]['invoice_number']) <> '') { ?>
          			<TD colspan="4" style=''><SPAN style='font-weight:normal; color:#999999; text-align:right;' nowrap>Invoice&nbsp;Number&nbsp;</SPAN><SPAN style='font-size:1.2em;'><?php echo substr($mfT[0]['invoice_number'],0,8); ?></SPAN></TD>
          <?php } elseif ($mfT[0]['status_uid'] >= 76) { ?>
          			<TD colspan="4" style=''><SPAN style='font-weight:normal; color:#999999; text-align:right;' nowrap>Invoice&nbsp;Number&nbsp;</SPAN><SPAN style='font-size:1.2em;'><?php echo substr($mfT[0]['document_number'],2,6); ?></SPAN></TD>
          <?php } else { ?>
         			<TD colspan="4" style=''><SPAN style='font-weight:normal; color:#999999; text-align:right;' nowrap>Document&nbsp;Number&nbsp;</SPAN><SPAN style='font-size:1.2em;'><?php echo substr($mfT[0]['document_number'],2,6); ?></SPAN></TD>
          <?php }?>
   </TR>
  <TR>
          <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999; 'font-size:1.0em;'><?php echo $mfT[0]['deliver_add1']; ?></TD>
          <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999; 'font-size:1.0em;'><?php echo $mfT[0]['bill_add1']; ?></TD>
          <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999;text-align:right; '>Invoice Date&nbsp;&nbsp;</SPAN><SPAN style='font-size:1.0em;'><?php echo $mfT[0]['invoice_date']; ?></SPAN></TD>
  </TR>
  <TR>
          <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999; 'font-size:1.0em;'><?php echo $mfT[0]['deliver_add2']; ?></TD>
          <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999; 'font-size:1.0em;'><?php echo $mfT[0]['bill_add2']; ?></TD>
  </TR>
  <TR>
          <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999; 'font-size:1.0em;'><?php echo $mfT[0]['deliver_add3']; ?></TD> 	
          <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999; 'font-size:1.0em;'><?php echo $mfT[0]['bill_add3']; ?></TD> 	
          <TD colspan="4">&nbsp;</TD>
  </TR>
  <TR>
          <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999;'>Vat No&nbsp;&nbsp;</SPAN><SPAN style='font-size:1.0em;'><?php echo $mfT[0]['vat_number']; ?></SPAN></TD>
          <TD colspan="4">&nbsp;</TD>
  </TR>
  <TR>          	
          <TD colspan="4">&nbsp;</TD>
  </TR>
  <TR>          
          <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999; text-align:left ;'>Customer Order No&nbsp;&nbsp;</SPAN><SPAN style='font-size:1.0em;'><?php echo $mfT[0]['customer_order_number']; ?></SPAN></TD>
          <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999; text-align:right; '>Cases/Units&nbsp;&nbsp;</SPAN><SPAN style='font-size:1.0em;'><?php echo $mfT[0]['cases']; ?></SPAN></TD>
          <?php if ($mfT[0]['status_uid'] >= 76 AND trim($mfT[0]['invoice_number']) <> '') { ?>
          			<TD colspan="4" style=''><SPAN style='font-weight:normal; color:#999999; text-align:right;' nowrap>Document&nbsp;Number&nbsp;&nbsp</SPAN><SPAN style='font-size:1.2em;'><?php echo substr($mfT[0]['document_number'],2,6); ?></SPAN></TD>
          <?php } else { ?>
           			<TD colspan="4" style=''><SPAN style='font-weight:normal; color:#999999; text-align:right;' nowrap>&nbsp</SPAN><SPAN style='font-size:1.2em;'>&nbsp</SPAN></TD>
         <?php } ?>
  </TR>
  <TR>
          <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999;'>Delivery Instructions&nbsp;&nbsp;</SPAN><SPAN style='font-size:1.0em;'><?php echo $mfT[0]['delivery_instructions']; ?></SPAN></TD>
          <TD colspan="3">&nbsp;</TD>
  </TR>
  <TR>
          <TD colspan="3">&nbsp;</TD>
  </TR>
  </TABLE>

    </td>
  </tr><tr>
    <td >
      <STYLE>
      th.detail { padding:3; margin:0; border:1px solid #999; font-weight:bold; }
      td.detail { padding:3; margin:0; border:1px solid #999; font-weight:normal; text-align:right; }
      </STYLE>

      <TABLE style='border-collapse:collapse;' cellpadding=2; cellspacing=0; width="100%">
      <TR style='padding:0; margin:0;background:#efefef;'>
              <TH nowrap class='detail' colspan="1">Product</TH>
              <TH nowrap class='detail' colspan="1">Description</TH>              
              <?php if ($mfT[0]['status_uid'] == 74) { ?> <TH nowrap class='detail' colspan="1">Order<br>Qty</TH>
              <?php  } elseif ($mfT[0]['status_uid'] == 74) { ?> <TH nowrap class='detail' colspan="1">Order<br>Qty</TH>  <?php } ?>                     
              <TH nowrap class='detail' colspan="1">Doc<br>Qty</TH>                           
              <TH nowrap class='detail' colspan="1">Sell<br>Price</TH>
              <TH nowrap class='detail' colspan="1">Disc<br>Val</TH>
              <TH nowrap class='detail' colspan="1">Nett<br>Price</TH>
              <TH nowrap class='detail' colspan="1">Ext<br>Price</TH>
              <TH nowrap class='detail' colspan="1">VAT<br>Amnt</TH>
              <TH nowrap class='detail' colspan="1">VAT<br>Rate</TH>
              <TH nowrap class='detail' colspan="1">Total</TH>
      </TR>
      <?php
      $totalPallets=0;
      $ordTot=0;
      $docTot=0;
      $weightTot=0;

      foreach($mfT as $row) {
              $totalPallets+=intval($row['pallets']);
              echo "<TR style='padding:2; margin:0;'>
                        <TD nowrap class='detail' style='text-align:left' align='left'>{$row['product_code']}</TD>
                        <TD nowrap class='detail' style='text-align:left' align='left'>{$row['product_description']}</TD>"; ?>
              <?php if ($mfT[0]['status_uid'] == 74) { ?>
                        <TD nowrap class='detail'><?php echo $row['ordered_qty']; ?></TD>  
              <?php } elseif ($mfT[0]['status_uid'] == 75) { ?>
                        <TD nowrap class='detail'><?php echo $row['ordered_qty'] ;?></TD> 
              <?php } ?>                                       
                        <TD nowrap class='detail'><?php echo $row['document_qty'] ;?></TD>
       <?php 
                        $ordTot+=$row['ordered_qty'];
                        $docTot+=$row['document_qty'] ;
                        $weightTot+=($row['document_qty']*$row['weight']);

              if (!$hasRoleVP) {
                      echo "<TD nowrap colspan=\"8\" class='detail'>not authorised to view pricing</TD>";
              } else {

                      echo "<TD nowrap class='detail'>".number_format($row['selling_price'],2)."</TD>
                                <TD nowrap class='detail'>".number_format($row['discount_value'],2)."</TD>";
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
                      echo "<tr><TD class='detail' colspan=\"11\" nowrap style='text-align:left;'><b>Document Bulk Discounts / Charges</b></TD></tr>";

                      include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
                      $productDAO = new ProductDAO($dbConn);

                      if (!$hasRoleVP) {
                              echo "<tr><TD class='detail' colspan=\"11\" nowrap style='text-align:left;'>Bulk Discounts / Charges found, but user not authorised to view pricing</b></TD></tr>";
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
      <TH colspan="2"></TH>
      <?php 
              if ($mfT[0]['status_uid'] == 74) { ?>
      	        <TH nowrap class='detail' style='text-align:right'><?php echo number_format($ordTot,0)?></TH> 
      <?php } elseif ($mfT[0]['status_uid'] == 75) { ?>
      	        <TH nowrap class='detail' style='text-align:right'><?php echo number_format($ordTot,0)?></TH>      
      <?php } elseif ($mfT[0]['status_uid'] == 47) { ?>
      	        <TH nowrap class='detail' style='text-align:right'><?php echo number_format($ordTot,0)?></TH>        
      <?php } 
      				if ($mfT[0]['status_uid'] <> 47) { ?>      
           	    <TH nowrap class='detail' style='text-align:right'><?php echo number_format($docTot,0)?></TH>
      <?php }             

       if ($hasRoleVP) {

            echo "<TH colspan='3'></TH>";

              echo "<TH nowrap class='detail' style='text-align:right'>" , number_format($totalEP,2) , "</TH>";

              echo "<TH nowrap class='detail' style='text-align:right'>" , number_format($totalVAT,2) , "</TH>";

              echo "<TH colspan='1'></TH>
                    <TH nowrap class='detail' style='text-align:right'>" , number_format($totalINV,2) , "</TH>";
       } ?>
      </TR>
      </TABLE>

    </td>
  </tr><tr>
    <td>

        <table border="0" cellpadding="0" cellspacing="0" width="100%" >
        </table>
<TABLE>
<?php
     if ($mfT[0]['status_uid'] >= 76 AND $weightTot>0) {
?>
   <TR>
   	<TD>&nbsp</TD>
  	</TR>
   <TR>
   	<TD>&nbsp</TD>
  	</TR>
  <TR>
   <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999; text-align:left ;'>Calculated Weight&nbsp;</SPAN><SPAN style='font-size:1.0em;'><?php echo number_format($weightTot,2)?></SPAN></TD>
  </TR>	
<?php
     }
     if (trim($mfT[0]['banking_details']) != '' AND ($mfT[0]['status_uid'] >= 76) ) {
?>
   <TR>
   	<TD>&nbsp</TD>
  	</TR>
   <TR>
   	<TD>&nbsp</TD>
  	</TR>
  <TR>
  <TD colspan="4" nowrap style=''><SPAN style='font-weight:normal; color:#999999; text-align:left ;'>Banking Details&nbsp;&nbsp;</SPAN><SPAN style='font-size:1.0em;'><?php echo $mfT[0]['banking_details']?></SPAN></TD>
  </TR>	
<?php
     }
?>	
<TR>
      <!-- footer -->
      <TR>
      	<TD>&nbsp</TD>
      	</TR>
      <TR>
      	<TD>&nbsp</TD>
      	</TR>
      <TR>
      <TD style="text-align:center; color:#444; font-weight:normal; font-size:8px;">
        User: <?php echo (isset($_SESSION['user_id'])?$_SESSION['user_id']:'0') , ' - ' , (isset($_SESSION['full_name'])?$_SESSION['full_name']:'na'); ?>
         | <?php echo gmdate('Y/m/d H:i:s'); ?>
      </TD>
      </TR>	
  </tr>
</TABLE>
<script type='text/javascript'>
function emailDoc() {
	var params="?USERID=<?php echo $userId; ?>&OBJECTID=<?php echo EO_DOC_CARD; ?>&SUBJECT=Order Details as per Request: <?php echo $postDOCMASTID; ?>&DOCMASTID=<?php echo $postDOCMASTID; ?>";
	window.location='<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/emailUserHTML.php'+params;
}

</script>

</BODY>
</HTML>
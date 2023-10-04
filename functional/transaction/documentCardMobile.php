<HTML>
    <style type="text/css">
    div{color:#333;font-size:8pt;line-height:14px;}
    h1{color:#047;font-size:14pt;}
    table {background:#fff;border-collapse:collapse;}
    table td{font-size:11px;padding:5px 4px;border:1px solid lightskyblue;}

    table.mainTB td{padding:5px 5px 5px 10px;}
    table td.total{color:red;font-weight:bold;font-size:12px;}
    tr.odd{background-color:aliceBlue}
    table th{background:#047;color:#fff;}
    table th.header{font-size:14px;line-height:30px;color:#fff;}
    table th.smallheader{background:#999;font-size:12px;line-height:20px;color:#fff;}



    </style>
<HEAD>
</HEAD>
<BODY style='color:#444; font-family:Helvetica,Verdana,Arial,sans-serif;'>



<div align="center" >
<div style="width:320px;">


<div style='color:#333; text-align:center;margin:5px 0px 10px 0px;padding-top:5px;'>
  <SPAN style='font-size:20; font-weight:bold;'>Retail Trading</SPAN> <Br>
  <SPAN style='font-size:10;'>End to end supply chain solutions</SPAN>
</div>
<!-- note that the full http path is used because this card is also emailable to user and needs to look it up -->



<TABLE width="100%" class="mainTB">
  <TR><TH colSpan="2" class="header">Invoice Details</TH></TR>

  <TR><TD>Document Number:</TD><TD><SPAN style='font-size:1.2em;'><strong><?php echo $mfT[0]['document_number']; ?></strong></TD></TR>
  <TR class="odd"><TD>Customer:</TD><TD><SPAN style='font-size:1.2em;'><?php echo $mfT[0]['store_name']; ?></TD></TR>
  <TR><TD>Customer Order No:</TD><TD><?php echo $mfT[0]['customer_order_number']; ?></TD></TR>
  <TR class="odd"><TD>OrderDate:</TD><TD><?php echo $mfT[0]['order_date']; ?></TD></TR>
  <TR><TD>Invoice Date:</TD><TD><?php echo $mfT[0]['invoice_date']; ?></TD></TR>
  <TR class="odd"><TD>Delivery Date:</TD><TD><?php echo $mfT[0]['delivery_date']; ?></TD></TR>
  <TR><TD>Document Type:</TD><TD><?php echo $mfT[0]['document_type_description']; ?></TD></TR>
  <TR class="odd"><TD>Status:</TD><TD><?php echo $mfT[0]['status']; ?></TD></TR>
  <TR><TD>Vat No:</TD><TD><?php echo $mfT[0]['vat_number']; ?></TD></TR>

  <TR class="odd"><TD>Cases:</TD><TD><?php echo $mfT[0]['cases']; ?></TD></TR>


  <?php

  /*
  <TR><TD>Delivery Day:</TD><TD><?php echo $mfT[0]['delivery_day']; ?></SPAN></TD></TR>
       <TD>Cases:</SPAN><BR><?php echo $mfT[0]['cases']; ?></SPAN></TD>
  <TR><TD>Claim No:</TD><TD><?php echo $mfT[0]['claim_number']; ?></SPAN></TD></TR>
  <TR><TD>GRV Number:</TD><TD><?php echo $mfT[0]['grv_number']; ?></SPAN></TD></TR>
       <TD>Related Source Docket:</SPAN><BR><?php echo $mfT[0]['source_document_number']; ?></SPAN></TD>
  */

  ?>

  <TR ><TD>Depot: </SPAN></TD><TD><?php echo $mfT[0]['depot_name']; ?></SPAN></TD></TR>
  <TR class="odd">
    <TD valign="top">Delivery Address:</TD>
    <TD><?php
          echo $mfT[0]['deliver_add1'];
          if(!empty($mfT[0]['deliver_add2'])) echo '<br>' . $mfT[0]['deliver_add2'];
          if(!empty($mfT[0]['deliver_add3'])) echo '<br>' . $mfT[0]['deliver_add3'];
        ?>
    </TD>
  </TR>


</TABLE>

<!-- detail -->

<TABLE width="100%">
  <TR><TH colSpan="4" class="header">Item Details</TH></TR>
  <TR>
    <TH class='smallheader'>Code</TH>
    <TH class='smallheader'>Description</TH>
    <TH class='smallheader'>Qty</TH>
    <TH class='smallheader'>Price</TH>
  </TR>

<?php
$totalPallets=$totalDocQty=0;
foreach($mfT as $no=>$row) {

	$totalPallets+=intval($row['pallets']);
        $totalDocQty += abs($row['document_qty']);

	echo "<TR style='padding:2; margin:0;' ".(($no%2)?('class="odd"'):('')).">";
		  echo "<TD nowrap class='detail' style='text-align:left' align='left'>{$row['product_code']}</TD>
                        <TD nowrap class='detail' style='text-align:left' align='left'>" . ((strlen($row['product_description'])>30)?(substr($row['product_description'],0,30).'...'):($row['product_description'])) . "</TD>
                        <TD nowrap class='detail' align='right'>".round($row['document_qty'],0)."</TD>";

	if (!$hasRoleVP) {
          echo "<TD nowrap class='detail'>not authorised to view pricing</TD>";
 	} else {
          echo "<TD nowrap class='detail' align='right'>".number_format($row['net_price'],2)."</TD>";
 	}

	echo "</TR>";
	$totalEP=$row['exclusive_total']; // not cumulative !
	$totalVAT=$row['vat_total']; // not cumulative !
	$totalINV=$row['invoice_total']; // not cumulative !
}


?>

<!-- total line -->
<TR style='padding:0; margin:0;'>
  <TD colSpan='3' align='right'>Sub Total:</TD>

<?php

 if ($hasRoleVP) {

	echo "<TD nowrap class='detail' align='right'>" , number_format($totalEP,2) , "</TD>";
        echo "<TR><TD colSpan='3' align='right'>V.A.T</TD>";
	echo "<TD nowrap class='detail' align='right'>" , number_format($totalVAT,2) , "</TD>";
        echo "</TR>";
        echo "<TR><TD colSpan='3' align='right' class='total'>Total:</TD>";
	echo "<TD align='right' class='total'>" , number_format($totalINV,2) , "</TD>";
        echo "</TR>";
 } ?>
</TR>
</TABLE>


<Br>
<div style="font-size:8pt;color:#777;" >mobile version - <?php echo date('Y/m/d H:i:s'); ?> </div>


</div>
</div>

</BODY>
</HTML>

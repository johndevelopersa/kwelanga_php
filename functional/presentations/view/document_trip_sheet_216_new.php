<?php 
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

$dbConn->dbConnection();

?>
<!DOCTYPE html>
<HTML>
<HEAD>
  <STYLE type="text/css">

    #wrapper{width:700px;text-align:left;}

    #toolbar {font-size:12px;background:#047;padding:8px 10px}
    #toolbar a img{margin:2px 5px 2px 0px;}
    #toolbar a:hover{background:aliceBlue}
    #toolbar a{margin-right:10px;float:left;background:#fff;text-align:left;display:block;border:1px solid #047;padding:0px 8px;line-height:36px;text-decoration:none;color:#666;font-weight:bold;}
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
    table.grid, table.grid th
    {
    border:1px solid #aaa;
    }
    table.grid th {background:#efefef;}
    .bordUnderline{border-bottom:1px solid #333;height:30px;}
    
    img {
    float: right;
    margin: 0 0 10px 10px;
		}
		
		td.hd1 {font-size:15px;
			      border:1px solid;
			      font-weight:bold; 
			      }

		td.hd2 {font-size:15px;
			      border:1px solid;
			      font-weight:normal; 
			      }
		td.d12 {font-size:12px;
			      border-right:1px solid;
			      border-left:1px solid;
			      font-weight:normal; 
			      }

		
</STYLE>

<script type='text/javascript' language='javascript' src='<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js'></script>
</HEAD>

<BODY style="font-family:Verdana,Arial,Helvetica,sans-serif;margin:0px;padding:0px;">

<!-- email -->
<div align="left" id="noprint" class="disableprint" >
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
<TITLE>Trip Sheet - View</TITLE>
</div>
<table style='border-style:none; width:100%'>
  <tr>
     <td>&nbsp;</td>
  </tr>
	<tr>
     <td style='width:1%;' >&nbsp;</td>
     <td style='width:49%; text-align:left;  font-weight:bold; font-size:20px;'>Driver trip Sheet</TD>
     <td style='width:41%; text-align:right; font-weight:bold; font-size:20px;'><?php echo trim($tsDT[0]['Principal']);?></TD>
     <td style='width:8%;' >&nbsp;</td></tr>
	<tr>
    <td &nbsp;&nbsp;</td>
  </tr>	
	<tr>
     <td>&nbsp;</td>
     <td style='text-align:left;  font-weight:bold; font-size:20px;'><?php echo $tsDT[0]['Depot']; ?></TD>
     <td style='text-align:right;'><span style='text-align:right; font-weight:normal; font-size:14px;'</span>Driver:&nbsp;&nbsp;&nbsp;</span><span style='text-align:right;  font-weight:bold; font-size:14px;'><?php echo $tsDT[0]['Transporter_Name'] ?></span></TD>
     <td>&nbsp;</td></tr>		
  </tr>
	<tr>
     <td>&nbsp;</td>
     <td style='text-align:left;'><span style='text-align:left; font-weight:normal; font-size:14px;'</span>Trip Sheet Number:&nbsp;&nbsp;&nbsp;</span><span style='text-align:left;  font-weight:bold; font-size:14px;'><?php echo substr($tsDT[0]['TripNo'],0,6); ?></span></TD>
     <td style='text-align:right;'><span style='text-align:right; font-weight:normal; font-size:14px;'</span>Date :&nbsp;&nbsp;&nbsp;</span><span style='text-align:right;  font-weight:bold; font-size:14px;'><?php echo $tsDT[0]['Date']; ?></span></TD>
     <td>&nbsp;</td></tr>		
  </tr>  
	<tr>
     <td>&nbsp;</td>
     <td style='text-align:left;'><span style='text-align:left; font-weight:normal; font-size:14px;'</span>Captured By:&nbsp;&nbsp;&nbsp;</span><span style='text-align:left;  font-weight:bold; font-size:14px;'><?php echo $tsDT[0]['User']; ?></span></TD>
     <td style='text-align:right;'><span style='text-align:right;  font-weight:bold; font-size:14px;'>&nbsp;&nbsp;&nbsp;</span></TD>
     <td>&nbsp;</td></tr>		
  </tr>  
  <tr>
     <td>&nbsp;</td>
  </tr>
</table>
<TABLE style= 'border-collapse:collapse'>
<?php
 $totos = $totns = $totns20 = $totab = $totaw = $totbw = $totfj =$totbb = $tottot = $totjsr = $totssr = $storedoc= 0;
 
// print_r($tsDT);

$prodcode = array();
?>
<TR>
  <td style='width:1%;  text-align:left;' >&nbsp;</td>
  <td class="hd1" style='width:8%;  text-align:left;' >&nbsp;Invoice No&nbsp;</td>
  <td class="hd1" style='width:15%; text-align:left; border-right:none;' >&nbsp;Store&nbsp;</td>
  <td class="hd1" style='width:15%; text-align:left; border-left :none;' >&nbsp;</td>
  <td class="hd1" style='width:10%; text-align:center;' wrap  >TOTAL&nbsp;</td> 
  <td class="hd1" style='width:5%; text-align:center;' wrap  >TIME IN&nbsp;</td>
  <td class="hd1" style='width:5%; text-align:center;' wrap  >TIME OUT&nbsp;</td>  
  <td class="hd1" style='width:5%;  text-align:centre;' wrap  >&nbsp;&nbsp;CASH&nbsp;</td>  
  <td class="hd1" style='width:5%;  text-align:centre;' wrap  >&nbsp;EFT&nbsp;</td>       
  <td class="hd1" style='width:10%;  text-align:centre;' wrap  >&nbsp;Comment&nbsp;</td>       
  <td class="hd1" style='width:15%; text-align:centre; border-right:solid; border-right-color:black; border-right-width:1px'; nowrap  >&nbsp;SIGN&nbsp;</td>
  <td style='width:1%;  text-align:left;' >&nbsp;</td>

</TR>
<?php

foreach($tsDT as $row) {
    if($storedoc <> $row['Docno'] ) {
    	   if($storedoc > 0) {
    	   	?>
    	   	<tr>
    	  		<td>&nbsp;</td>	
     	      <td class='d12' >&nbsp;</td>	
    	  		<td class='d12' >&nbsp;</td>	
     	      <td class='d12' >&nbsp;</td>	
    	  		<td class='d12' >&nbsp;</td>
            <td>&nbsp;</td>	
            <td>&nbsp;</td>	
            <td>&nbsp;</td>	  
            <td>&nbsp;</td>	
            <td>&nbsp;</td>	
            <td class='d12' style='border-left:none;' >&nbsp;</td>	    	   	
    	  	</tr>
    	   	<tr>
    	  		<td>&nbsp;</td>	
     	      <td class='d12' >&nbsp;</td>	
    	  		<td class='d12' >&nbsp;</td>	
     	      <td class='d12' style='font-weight:bold;' >&nbsp;Store Total</td>	
    	  		<td class='d12' style='font-weight:bold; text-align:right;' nowrap><?php echo $storeqty ?>&nbsp;&nbsp;</td>
            <td>&nbsp;</td>	
            <td>&nbsp;</td>	
            <td>&nbsp;</td>	   
            <td>&nbsp;</td>	
            <td>&nbsp;</td>	
            <td class='d12' style='border-left:none;' >&nbsp;</td>	    	   	
    	   	</tr>
    	   	<tr>
    	      	<td>&nbsp;</td>	
              <td class='d12' style='border-bottom:1px none;'>&nbsp;</td>	
    	      	<td class='d12' style='border-bottom:1px none;'>&nbsp;</td>	
              <td class='d12' style='border-bottom:1px none;'>&nbsp;</td>	
    	      	<td class='d12' style='border-bottom:1px none;'>&nbsp;</td>
              <td style='border-bottom:1px none;'>&nbsp;</td>	
              <td style='border-bottom:1px none;'>&nbsp;</td>	
              <td style='border-bottom:1px none;'>&nbsp;</td>	
              <td style='border-bottom:1px none;'>&nbsp;</td>	
              <td style='border-bottom:1px none;'>&nbsp;</td>	
              <td class='d12' style='border-left:none; border-bottom:1px solid;' >&nbsp;</td>	    	   	
    	   	</tr>
    	   	<tr>
    	     		<td>&nbsp;</td>	
              <td class='d12' >&nbsp;</td>	
    	     		<td class='d12' >&nbsp;</td>	
              <td class='d12' style='font-weight:bold;' >&nbsp;Store Value</td>	
    	     		<td class='d12' style='font-weight:bold; text-align:right;' nowrap><?php echo round($storeval,2) ?>&nbsp;&nbsp;</td>
              <td >&nbsp;</td>	
              <td>&nbsp;</td>	
              <td>&nbsp;</td>	
              <td>&nbsp;</td>	
              <td>&nbsp;</td>	
              <td class='d12' style='border-left:none;' >&nbsp;</td>	    	   	
    	   	</tr>
    	   	<tr>
    	      	<td>&nbsp;</td>	
              <td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>	
    	      	<td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>	
              <td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>	
    	      	<td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td class='d12' style='border-left:none; border-bottom:1px solid;' >&nbsp;</td>	    	   	
    	   	</tr>
    	   	<?php
    	   }
    	   $storedoc = $row['Docno'];
    	   $storeqty = 0;
    	   $storeval = 0;
?>
     <tr>
          <td>&nbsp;</td>
          <td class='hd2' nowrap><?php echo $row['Docno']?></td>
          <td class='hd2' colspan=2 nowrap>&nbsp;<?php echo $row['Store']?></td>
          <td class='hd2' style='text-align:right;' nowrap>&nbsp;&nbsp;</td>     	
          <td class='hd2'>&nbsp;</td>
          <td class='hd2'>&nbsp;</td>
          <td class='hd2'>&nbsp;</td>
          <td class='hd2'>&nbsp;</td>
          <td class='hd2'>&nbsp;</td>
          <td class='hd2'>&nbsp;</td>
          <td >&nbsp;</td>
     </tr>    	
<?php
    } 
       if (!in_array($row['Product_uid'], $prodcode)) {
           array_push($prodcode,$row['Product_uid']);
           ${$row['Product_uid']}=0;
       }
?>    
        <tr>
          <td>&nbsp;</td>	
          <td class='d12' >&nbsp;</td>	
          <td class='d12' nowrap>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row['product_code'] ?></td>
          <td class='d12' nowrap>&nbsp;<?php echo $row['product_description'] ?></td>
          <?php 
          if($row['allow_decimal'] == 'Y') { ?>
               <td class='d12' style='text-align:right;' nowrap><?php echo $row['Quantity'] / 100; ?>&nbsp;&nbsp;</td>
          <?php	
          } else { ?>
          <td class='d12' style='text-align:right;' nowrap><?php echo $row['Quantity']?>&nbsp;&nbsp;</td>
          <?php	
          } ?>
          <td>&nbsp;</td>	
          <td>&nbsp;</td>	
          <td>&nbsp;</td>	
          <td>&nbsp;</td>
          <td>&nbsp;</td>	
          <td class='d12' style='border-left:none;' >&nbsp;</td>	
      </tr>    	
      <?php    
      if($row['allow_decimal'] == 'Y') { 
       	     $storeqty += $row['Quantity'] / 100;
       	     $storeval += $row['extended_price'] / 100;
      } else {
             $storeqty += $row['Quantity'];
             $storeval += $row['extended_price'];
      }
      ${$row['Product_uid']} += $row['Quantity'];

}
?>
    	   	<tr>
    	   		<td>&nbsp;</td>	                                       
       	    <td class='d12' >&nbsp;</td>	                         
    	   		<td class='d12' >&nbsp;</td>                           
       	    <td class='d12' >&nbsp;</td>                           
    	   		<td class='d12' >&nbsp;</td>                           
            <td >&nbsp;</td>	                                     
            <td>&nbsp;</td>	                                       
            <td>&nbsp;</td>	                                       
            <td>&nbsp;</td>	                                       
            <td>&nbsp;</td>	                                       
            <td class='d12' style='border-left:none;' >&nbsp;</td>     	   	
    	   	</tr>
    	   	<tr>
    	     		<td>&nbsp;</td>	
              <td class='d12' >&nbsp;</td>	
    	     		<td class='d12' >&nbsp;</td>	
              <td class='d12' style='font-weight:bold;' >&nbsp;Store Total</td>	
    	     		<td class='d12' style='font-weight:bold; text-align:right;' nowrap><?php echo $storeqty ?>&nbsp;&nbsp;</td>
              <td >&nbsp;</td>	
              <td>&nbsp;</td>	
              <td>&nbsp;</td>	
              <td>&nbsp;</td>	
              <td>&nbsp;</td>	
              <td class='d12' style='border-left:none;' >&nbsp;</td>	    	   	
    	   	</tr>
    	   	<tr>
    	      	<td>&nbsp;</td>	
              <td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>	
    	      	<td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>	
              <td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>	
    	      	<td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td class='d12' style='border-left:none; border-bottom:1px solid;' >&nbsp;</td>	    	   	
    	   	</tr>
    	   	<tr>
    	     		<td>&nbsp;</td>	
              <td class='d12' >&nbsp;</td>	
    	     		<td class='d12' >&nbsp;</td>	
              <td class='d12' style='font-weight:bold;' >&nbsp;Store Value</td>	
    	     		<td class='d12' style='font-weight:bold; text-align:right;' nowrap><?php echo round($storeval,2) ?>&nbsp;&nbsp;</td>
              <td >&nbsp;</td>	
              <td>&nbsp;</td>	
              <td>&nbsp;</td>	
              <td>&nbsp;</td>	
              <td>&nbsp;</td>	
              <td class='d12' style='border-left:none;' >&nbsp;</td>	    	   	
    	   	</tr>
    	   	<tr>
    	      	<td>&nbsp;</td>	
              <td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>	
    	      	<td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>	
              <td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>	
    	      	<td class='d12' style='border-bottom:1px solid;'>&nbsp;</td>
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td style='border-bottom:1px solid;'>&nbsp;</td>	
              <td class='d12' style='border-left:none; border-bottom:1px solid;' >&nbsp;</td>	    	   	
    	   	</tr>

<tr>
	<td>&nbsp;</td>	
</td>

<tr>
	<td>&nbsp;</td>	
	<td>&nbsp;</td>	
	<td style='font-size:15px; font-weight:bold;' >Trip Sheet Summary </td>	
</td>

<tr>
	<td>&nbsp;</td>	
</td>
<?php

$triptot = 0;

foreach($prodcode as $pline) {
	
   $transactionDAO = new TransactionDAO($dbConn);
   $pdfT = $transactionDAO->getProductDetail($pline);
   ?>
   <tr>
      <td>&nbsp;</td>	
      <td>&nbsp;</td>	
   	  <td style='font-size:12px; font-weight:normal;'><?php echo $pdfT[0]['product_code']?></td>
   	  <td style='font-size:12px; font-weight:normal;'><?php echo $pdfT[0]['product_description']?></td>
   	  <td style='font-size:12px; font-weight:normal; text-align:right;'><?php echo ${$pline} ?>&nbsp;&nbsp;&nbsp;</td>
   </tr>   

   <tr>
      <td>&nbsp;</td>	
      <td>&nbsp;</td>	
      <td>&nbsp;</td>	
      <td>&nbsp;</td>	
   </tr>   

<?php
     $triptot += ${$pline};
}
?>
   <tr>
      <td>&nbsp;</td>	
      <td>&nbsp;</td>	
   	  <td>&nbsp;</td>
   	  <td style='font-size:12px; font-weight:bold;' nowrap>Total</td>
   	  <td style='font-size:12px; font-weight:bold; text-align:right;' nowrap><?php echo $triptot ?>&nbsp;&nbsp;&nbsp;</td>
   </tr>   
  <tr>
     <td>&nbsp;</td>
  </tr>
  <tr>
     <td>&nbsp;</td>
  </tr>  	
  <tr>
     <td>&nbsp;</td>
  </tr>
  <tr>
     <td>&nbsp;</td>
  </tr>  	
  <tr>
  	  <td>&nbsp;</td>	
      <td>&nbsp;</td>	
   	  
      <td colspan="3">***********************END***********************</td>
  </tr>
 	</table>
	</BODY>
</HTML>

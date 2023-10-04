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

<?php
// print_r($psDT);
// echo'<br>';
// print_r($psHD);
?>

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
<table style='border-style:none; width:90%'>
	<tr>
		<td colspan=1 style='width:20px;  border:none;'>&nbsp;</td>
		<td colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:bold; font-size:1.6em;'>Picking List</TD>
	</tr>
	<tr>
    <td &nbsp;&nbsp;</td>
  </tr>
	<tr>
    <td &nbsp;&nbsp;</td>
  </tr>	
	<tr>
		<td colspan=1 style='width:20px;  border:none;'>&nbsp;</td>
    <td colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:bold; font-size:1.0em;'><?php echo $psHD[0]['Depot']; ?></TD>
</tr>
<tr>
<td &nbsp;&nbsp;</td>
</tr>
<tr>
<td &nbsp;&nbsp;</td>
</tr>
<tr>
<td &nbsp;&nbsp;</td>
</tr>
<tr>
	 <td colspan=1 style='width:20px;  border:none;'>&nbsp;</td>
   <TD colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:mormal; font-size:1.0em;'>Picking List Number&nbsp;&nbsp;&nbsp;<?php echo $psHD[0]['pick_list_number']?></TD>
   <TD colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:mormal; font-size:1.0em;'>Date & Time Number&nbsp;&nbsp;&nbsp;<?php echo $psHD[0]['DT']?></TD>                                                                                                                       
	</tr>
<tr>
<td &nbsp;&nbsp;</td>
</tr>
<tr>
<td &nbsp;&nbsp;</td>
</tr>
<STYLE>
th {
  padding-left:0.1cm; text-align:left; width:100%; border-collapse:collapse; border-left:solid; border-left-width:1px; border-left-color:black; border-top:solid; border-top-width:1px; border-top-color:black; border-bottom:solid; border-bottom-width:1px; border-bottom-color:black;
}
td.dC {
  padding-left:0.1cm; border-left:solid; border-left-width:1px; border-left-color:black; font-weight:normal; font-size:0.8em
}
</STYLE>
<TABLE style= 'border-collapse:collapse'>
<TR>
  <TH colspan=1 style='width:20px;  border:none;'>&nbsp;</TH>
  <TH colspan=1 style='width:160px;  text-align:left;' nowrap  >Product Code&nbsp;</TH>
  <TH colspan=1 style='width:250px;  text-align:left;' nowrap  >&nbsp;Product Description&nbsp;No</TH>
  <TH colspan=1 style='width:70px;   text-align:center;' nowrap  >&nbsp;Quantity&nbsp;</TH>
  <TH colspan=1 style='width:70px;   text-align:left; border-right:solid; border-right-color:black; border-right-width:1px'; nowrap  >&nbsp;&nbsp;Picked Quantity&nbsp;</TH>
</TR>

<?php
$totQ=0;
foreach($psDT as $row) {
?>
      <tr>
        <td>&nbsp;</td>
        <td class='dC' nowrap><?php echo $row['Product Code']?></td>
        <td class='dC' nowrap><?php echo $row['Product']?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo $row['Ordered_Quantity']?></td>
        <td class='dC' style='border-right:solid; border-right-color:black; border-right-width:1px';' nowrap>&nbsp;&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td class='dC' nowrap>&nbsp;&nbsp;</td>
        <td class='dC' nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='text-align:right;' nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-right:solid; border-right-color:black; border-right-width:1px';' nowrap>&nbsp;&nbsp;</td>
      </tr>
<?php
      $totQ+= $row['Ordered_Quantity'];
}
?>  	
      <tr>
       <td>&nbsp;</td>
       <td style='border-style: solid none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: solid none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $totQ;?></td>
       <td style='border-top:solid; border-top-color:black; border-top-width:1px';' nowrap>&nbsp;&nbsp;</td>
      </tr>

	</table>
	
	<TABLE style= 'border-collapse:collapse'>
		<tr>
     <td &nbsp;&nbsp;</td>
    </tr>
		<tr>
     <td &nbsp;&nbsp;</td>
    </tr>
     <tr>
       <td>&nbsp;</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>Documents on Picking List</td>
      </tr>
		<tr>
     <td &nbsp;&nbsp;</td>
    </tr>
		<tr>
     <td &nbsp;&nbsp;</td>
    </tr>
          <tr>
             <td>&nbsp;</td>
             <td>Document Number</td>
             <td>&nbsp;</td>
             <td>Store</td>
             <td>&nbsp;</td>
             <td>Total Cases</td>
         </tr>
		<tr>
     <td &nbsp;&nbsp;</td>
    </tr>
		<tr>
     <td &nbsp;&nbsp;</td>
    </tr>
<?php    
			foreach($psHD as $row) {
?>
          <tr>
             <td>&nbsp;</td>
             <td><?php echo substr($row['document_number'],3,6)?></td>
             <td>&nbsp;</td>
             <td><?php echo $row['deliver_name']?></td>
             <td>&nbsp;</td>
             <td style='text-align:right;'><?php echo $row['cases']?></td>
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td>&nbsp;&nbsp;</td>
             <td>&nbsp;&nbsp;</td>
         </tr>
<?php
 
 }
 ?>
 		<tr>
     <td &nbsp;&nbsp;</td>
    </tr>
		<tr>
     <td &nbsp;&nbsp;</td>
    </tr>
		<tr>
     <td &nbsp;&nbsp;</td>
    </tr>
    </tr>
      <tr>
       <td colspan=1 style='width:20px;  border:none;'>&nbsp;</td>	
       <td colspan=2 style='border-style: none none none none; border-color:black; border-width:1px; width:50px;' nowrap>Picked BY by (Print Name)</td>
       <td colspan=2 style='border-style: none none solid none ; border-color:black; border-width:1px; width:100px;' nowrap>&nbsp;&nbsp;</td>    
     </tr>    
		</table>
	
	</BODY>
</HTML>

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
		<td colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:bold; font-size:1.6em;'>Trip Sheet</TD>
	</tr>
	<tr>
    <td &nbsp;&nbsp;</td>
  </tr>
	<tr>
    <td &nbsp;&nbsp;</td>
  </tr>	
	<tr>
    <td colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Depot']; ?></TD>
</tr>
<tr>
<td colspan="3" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:normal; font-size:0.8em;'>Email Address:</SPAN>&nbsp;&nbsp;<SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Depot_email'] ?></SPAN></TD>
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
   <TD colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:mormal; font-size:1.0em;'>Collection By </TD>
	</tr>
<tr>
<td &nbsp;&nbsp;</td>
</tr>
<tr>
   <td colspan="3" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Transporter_Name'] ?></SPAN></TD>
   <td colspan="3" style='text-align:left; background-color:white; color:black;' NOWRAP ><span style='font-weight:normal; font-size:0.8em;'>Tripsheet Number:&nbsp;&nbsp;&nbsp;</span><span style='font-weight:bold; font-size:1.0em;'><?php echo substr($tsDT[0]['TripNo'],0,6); ?></span></td>
	</tr>
<tr>
   <td colspan="3" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Transporter_Add1'] ?></SPAN></TD>
   <td colspan="3" style='text-align:left; background-color:white; color:black;' NOWRAP ><span style='font-weight:normal; font-size:0.8em;'>Date :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Date']; ?></span></td>
</tr>
<tr>
   <td colspan="3" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Transporter_Add2'] ?></SPAN></TD>
   <td colspan="3" style='text-align:right; background-color:white; color:black;'><span style='font-weight:bold; font-size:1.00em;'>&nbsp;</span></td>
</tr>
<tr>
   <td colspan="3" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Transporter_email'] ?></SPAN></TD>
   <td colspan="3" style='text-align:left; background-color:white; color:black;' NOWRAP ><span style='font-weight:normal; font-size:0.8em;'>Captured By:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['User']; ?></span></td>
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
  <TH colspan=1 style='width:65px;  text-align:left;' nowrap  >Principal&nbsp;</TH>
  <TH colspan=1 style='width:45px;  text-align:left;' nowrap  >&nbsp;Document&nbsp;No</TH>
  <TH colspan=1 style='width:160px; text-align:left;' nowrap  >&nbsp;Delivery&nbsp;Point&nbsp;</TH>
  <TH colspan=1 style='width:45px;  text-align:right;' nowrap  >&nbsp;&nbsp;Cases&nbsp;</TH>
  <TH colspan=1 style='width:45px;  text-align:right;' nowrap  >&nbsp;&nbsp;Weight&nbsp;</TH>
  <TH colspan=1 style='width:220px; text-align:left; border-right:solid; border-right-color:black; border-right-width:1px'; nowrap  >&nbsp;Comment&nbsp;</TH>
</TR>



<?php
$totQ=0; $totwt=0;
foreach($tsDT as $row) {
?>
      <tr>
        <td class='dC' nowrap><?php echo $row['Principal']?></td>
        <td class='dC' nowrap><?php echo $row['Docno']?></td>
        <td class='dC' nowrap><?php echo $row['Store']?></td>
        <td class='dC' style='text-align:right;' nowrap><?php echo $row['Cases']?></td>
        <td class='dC' style='text-align:right;' nowrap><?php echo round($row['Weight'],2)?></td>
        <td class='dC' style='border-right:solid; border-right-color:black; border-right-width:1px';' nowrap>&nbsp;&nbsp;</td>
      </tr>
      <tr>
        <td class='dC' nowrap>&nbsp;&nbsp;</td>
        <td class='dC' nowrap>&nbsp;&nbsp;</td>
        <td class='dC' nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='text-align:right;' nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='text-align:right;' nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-right:solid; border-right-color:black; border-right-width:1px';' nowrap>&nbsp;&nbsp;</td>
      </tr>
<?php
      $totQ+= $row['Cases'];
      $totwt+= $row['Weight'];
}
?>  	
      <tr>
       <td style='border-style: solid none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: solid none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: solid none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:right;' nowrap><?php echo $totQ;?></td>
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:right;' nowrap><?php echo round($totwt,2);?></td>
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
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>Goods accpted in good condition</td>
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
     <td &nbsp;&nbsp;</td>
    </tr>
		<tr>
     <td &nbsp;&nbsp;</td>
    </tr>
		<tr>
     <td &nbsp;&nbsp;</td>
    </tr>
      <tr>
       <td style='border-style: none none none none; border-color:black; border-width:1px; width:50px;' nowrap>Collected at (Time)</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;</td>
       <td style='border-style: none none solid none ; border-color:black; border-width:1px; width:100px;' nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>Vehicle Registration</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: none none solid none ; border-color:black; border-width:1px; width:100px;' nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
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
     <td &nbsp;&nbsp;</td>
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
     <td &nbsp;&nbsp;</td>
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
     <td &nbsp;&nbsp;</td>
    </tr>
      <tr>
       <td style='border-style: none none none none; border-color:black; border-width:1px; width:50px;' nowrap>Dispatched by (Print Name)</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;</td>
       <td style='border-style: none none solid none ; border-color:black; border-width:1px; width:100px;' nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px; width:50px;' nowrap>Dispatched by (Signiture)</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;</td>
       <td style='border-style: none none solid none ; border-color:black; border-width:1px; width:150px;' nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      </tr>   
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
     <td &nbsp;&nbsp;</td>
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
     <td &nbsp;&nbsp;</td>
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
     <td &nbsp;&nbsp;</td>
    </tr>
      <tr>
       <td style='border-style: none none none none; border-color:black; border-width:1px; width:50px;' nowrap>Collected by (Print Name)</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;</td>
       <td style='border-style: none none solid none ; border-color:black; border-width:1px; width:100px;' nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px; width:50px;' nowrap>Collected by (Signiture)</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;</td>
       <td style='border-style: none none solid none ; border-color:black; border-width:1px; width:150px;' nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: none none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
       </tr>      				
		</table>
	
	</BODY>
</HTML>

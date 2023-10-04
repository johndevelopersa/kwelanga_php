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
		<td style='text-align:left; background-color:white; color:Black; font-weight:bold; font-size:1.6em;'>Drivers Call Schedule</TD>
		<td>&nbsp</TD>
		<td>&nbsp</TD>
		<td  style='text-align:right; background-color:white; color:Black; font-weight:bold; font-size:1.6em;'><?php echo $tsDT[0]['Principal'];?>&nbsp;&nbsp;&nbsp;&nbsp;</TD>
	</tr>
	<tr>
    <td &nbsp;&nbsp;</td>
  </tr>	
	<tr>
    <td colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Depot']; ?></TD>
</tr>
<tr>
   <TD colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:mormal; font-size:1.0em;'>Driver</TD>
	</tr>
<tr>
<td &nbsp;&nbsp;</td>
</tr>
<tr>
   <td colspan="1" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Transporter_Name'] ?></SPAN></TD>
		<td>&nbsp</TD>
		<td>&nbsp</TD>
   <td colspan="1" style='text-align:right; background-color:white; color:black;' NOWRAP ><span style='font-weight:normal; font-size:0.8em;'>Call Sheet Number:&nbsp;&nbsp;&nbsp;</span><span style='font-weight:bold; font-size:1.0em;'><?php echo substr($tsDT[0]['TripNo'],0,6); ?>&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
	</tr>
<tr>
   <td colspan="1" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Transporter_Add1'] ?></SPAN></TD>
		<td>&nbsp</TD>
		<td>&nbsp</TD>
   <td colspan="1" style='text-align:right; background-color:white; color:black;' NOWRAP ><span style='font-weight:normal; font-size:0.8em;'>Date :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Date']; ?>&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
</tr>
<tr>
		<td>&nbsp</TD>
		<td>&nbsp</TD>
		<td>&nbsp</TD>
   <td colspan="1" style='text-align:right; background-color:white; color:black;' NOWRAP ><span style='font-weight:normal; font-size:0.8em;'>Captured By:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['User']; ?>&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
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
  padding-left:0.1cm; border-left:solid; border-left-width:1px; border-left-color:black; font-weight:normal; font-size:1.0em
}
</STYLE>
<TABLE style= 'border-collapse:collapse'  width:95%'>
<TR>
  <TH colspan=1 style='width:3%;  text-align:left;' >Invoice No&nbsp;</TH>
  <TH colspan=1 style='width:13%;  text-align:left;' nowrap  >&nbsp;STORE&nbsp;No</TH>
  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >BUN 70Gr (18)&nbsp;</TH>
  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >FLM HBP&nbsp;</TH>
  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >FLM HBS&nbsp;</TH>
  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >FLM HDP&nbsp;</TH>
  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >FLM HDS&nbsp;</TH>

  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >Jumbo French&nbsp;</TH>
  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >Foot Long&nbsp;</TH>

  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >PITA&nbsp;</TH>
  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >WRAPS&nbsp;</TH>
  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >AIB WHITE&nbsp;</TH>

  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >AIB BROWN&nbsp;</TH>
  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >BR WHITE&nbsp;</TH>
  <TH colspan=1 style='width:4%;  text-align:center;' wrap  >BR BROWN&nbsp;</TH>
  <TH colspan=1 style='width:5%;  text-align:center;' wrap  >TRAYS OUT&nbsp;</TH>
  <TH colspan=1 style='width:5%;  text-align:center;' wrap  >TRAYS IN&nbsp;</TH>  
  <TH colspan=1 style='width:6%;  text-align:center;' wrap  >INVOICE AMOUNT&nbsp;</TH> 
  <TH colspan=1 style='width:3%;  text-align:centre;' wrap  >CASH&nbsp;</TH>  
  <TH colspan=1 style='width:3%;  text-align:centre;' wrap  >EFT&nbsp;</TH>       
  <TH colspan=1 style='width:9%; text-align:centre; border-right:solid; border-right-color:black; border-right-width:1px'; nowrap  >SIGN&nbsp;</TH>
</TR>
<?php

$totb70 = $tothbp = $tothbs = $tothdp = $tothds = $totjuf = $totfol = $totpit = $totwrp = $totab = $totaw  = $totbw = $totbb = $tottot = 0;

// print_r($tsDT);

foreach($tsDT as $row) {
?>
      <tr>
        <td class='dC' nowrap><?php echo $row['Docno']?></td>
        <td class='dC' nowrap><?php echo $row['Store']?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['BUN 70g'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['FLM HBP'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['FLM HBS'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['FLM HDP'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['FLM HDS'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['Jumbo French'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['Foot Long'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['Pita'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['Wraps'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['AIB_W'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['AIB_B'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['BR_W'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap><?php echo round($row['BR_B'],2)?></td>
        <td class='dC' style='text-align:center;' nowrap>&nbsp;</td>        
        <td class='dC' style='text-align:center;' nowrap>&nbsp;</td>        
        <td class='dC' style='text-align:center;' nowrap><?php echo number_format(round($row['total'],2),2,"."," ") ?></td>
        <td class='dC' style='text-align:center;' nowrap>&nbsp;</td>        
        <td class='dC' style='text-align:center;' nowrap>&nbsp;</td>        
        <td class='dC' style='text-align:center;' nowrap>&nbsp;</td> 
        <td class='dC' style='border-right:solid; border-right-color:black; border-right-width:1px';' nowrap>&nbsp;&nbsp;</td>
      </tr>
     
      <tr>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-style: none solid solid solid; border-color:black; border-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-bottom:solid; border-bottom-color:black; border-bottom-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
        <td class='dC' style='border-style: none solid solid solid; border-color:black; border-width:0.2px'; nowrap>&nbsp;&nbsp;</td>
      </tr>
<?php 

      $totb70+= $row['BUN 70g'];
      $tothbp+= $row['FLM HBP'];
      $tothbs+= $row['FLM HBS'];
      $tothdp+= $row['FLM HDP'];
      $tothds+= $row['FLM HDS'];
      $totjuf+= $row['Jumbo French'];
      $totfol+= $row['Foot Long'];
      $totpit+= $row['Pita'];
      $totwrp+= $row['Wraps'];     
      $totab += $row['AIB_B'];
      $totaw += $row['AIB_W'];
      $totbw += $row['BR_W'];
      $totbb += $row['BR_B'];

      $tottot+= $row['total'];       
}
?>  	
      <tr>
       <td style='border-style: solid none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: solid none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;</td>
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $totb70;?></td>              
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $tothbp;?></td>              
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $tothbs;?></td>              
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $tothdp;?></td>              
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $tothds;?></td>              
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $totjuf;?></td>              
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $totfol;?></td>              
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $totpit;?></td>              
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $totwrp;?></td>              
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $totaw;?></td>               
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $totab;?></td>               
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $totbw;?></td>               
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo $totbb;?></td>               
       <td style='border-style: solid none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;</td>                           
       <td style='border-style: solid none none none; border-color:black; border-width:1px'; nowrap>&nbsp;&nbsp;</td>                           
       <td style='border-style: solid; border-color:black; border-width:1px; text-align:center;' nowrap><?php echo number_format(round($tottot,2),2,"."," ");?></td>     
       <td style='border-top:solid; border-top-color:black; border-top-width:1px';' nowrap>&nbsp;&nbsp;</td>                                    
       <td style='border-top:solid; border-top-color:black; border-top-width:1px';' nowrap>&nbsp;&nbsp;</td>                                    
       <td style='border-top:solid; border-top-color:black; border-top-width:1px';' nowrap>&nbsp;&nbsp;</td>       
      </tr>

	</table>
	</BODY>
</HTML>

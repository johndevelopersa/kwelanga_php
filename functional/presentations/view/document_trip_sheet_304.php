<!DOCTYPE html>
<HTML>
<HEAD>
  <style type="text/css">

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
    th {padding-left:0.1cm; text-align:left; width:100%; border-collapse:collapse; border-left:solid; border-left-width:1px; border-left-color:black; border-top:solid; border-top-width:1px; border-top-color:black; border-bottom:solid; border-bottom-width:1px; border-bottom-color:black;
    }
    td.tha {border: 1px solid black;
    	      border-collapse: collapse; 
    	      text-align:left;
    	      font-weight:bold;    	
    }
        
    td.dC {border-left:   1px solid black;
    	     border-right:  1px solid black;
    	     text-align:left;
    	     font-weight:normal; 
    	     padding-left:0.1cm;
    }
    
    td.db {border-left:    1px solid black;
    	     border-right:   1px solid black;
    	     border-bottom:  1px solid black;
    	     text-align:left;
    	     font-weight:normal; 
    	     padding-left:0.1cm;
    }    
    
</style>
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
<table style='border-style:none; width:85%;'>
	<tr>
		<td width="4%;"  style='text-align:left; background-color:white; color:Black; font-weight:bold; font-size:1.6em;'>&nbsp;</TD>
    <td width="31%;" style='text-align:left; background-color:white; color:Black; font-weight:bold; font-size:1.6em;'>Trip Sheet</TD>
		<td width="29%;" style='text-align:center; background-color:white; color:Black; font-weight:bold; font-size:1.6em;'>&nbsp;</TD>
		<td width="31%;" style='text-align:right; background-color:white; color:Black; font-weight:bold; font-size:1.6em;'>&nbsp;</TD>
		<td width="5%;"  style='text-align:left; background-color:white; color:Black; font-weight:bold; font-size:1.6em;'>&nbsp;</TD>
	</tr>
	<tr>
    <td colspan="5">&nbsp;</td>
  </tr>
	<tr>
    <td colspan="1">&nbsp;</td>
    <td colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Depot']; ?></TD>
    <td colspan="1">&nbsp;</td>
  </tr>
	<tr>
    <td colspan="3">&nbsp;</td>
    <?php 
      $filename = "images/logos/304.jpg";
      $file     = HOST_SURESERVER_AS_USER.$PHPFOLDER.$filename;
      if (file_exists($ROOT.$PHPFOLDER.$filename) == 1){
             $logo = $ROOT.$PHPFOLDER.$filename;
      ?>
          <td style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
               <?php echo "<img src=".$logo." style=width:120px; height:80px; float:right; >" ?>
          </td>
     <?php } ?> 
  </tr>
  <tr>
    <td colspan="1">&nbsp;</td>
    <td colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:mormal; font-size:1.0em;'>Collection By </TD>
    <td colspan="1">&nbsp;</td>
	</tr>
    <td colspan="5">&nbsp;</td>
  <tr>
    <td colspan="1">&nbsp;</td>  	
    <td colspan="2" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Transporter_Name'] ?></SPAN></TD>
    <td colspan="1" style='text-align:right; background-color:white; color:black;' NOWRAP ><span style='font-weight:normal; font-size:0.8em;'>Tripsheet Number:&nbsp;&nbsp;&nbsp;</span><span style='font-weight:bold; font-size:1.0em;'><?php echo substr($tsDT[0]['TripNo'],0,6); ?></span></td>
    <td colspan="1">&nbsp;</td>
	</tr>
  <tr>
    <td colspan="1">&nbsp;</td>
    <td colspan="2" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Transporter_Add1'] ?></SPAN></TD>
    <td colspan="1" style='text-align:right; background-color:white; color:black;' NOWRAP ><span style='font-weight:normal; font-size:0.8em;'>Date :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Date']; ?></span></td>
    <td colspan="1">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="1">&nbsp;</td>  	
    <td colspan="2" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Transporter_Add2'] ?></SPAN></TD>
    <td colspan="1" style='text-align:right; background-color:white; color:black;'><span style='font-weight:bold; font-size:1.00em;'>&nbsp;</span></td>
    <td colspan="1">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="1">&nbsp;</td>  	
    <td colspan="2" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['Transporter_email'] ?></SPAN></TD>
    <td colspan="1" style='text-align:right; background-color:white; color:black;' NOWRAP ><span style='font-weight:normal; font-size:0.8em;'>Captured By:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style='font-weight:bold; font-size:1.0em;'><?php echo $tsDT[0]['User']; ?></span></td>
    <td colspan="1">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="1">&nbsp;</td>  	
    <td colspan="3" style='text-align:left; background-color:white; color:Black;'>Starting Kilometers___________________    Ending Kilometers ___________________</td>
    <td colspan="1">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
  </tr>
</table>  
<table style='width: 85%; border-style:none; border-collapse: collapse;'>
   <tr>
      <td style='width: 4%;  border-style:none';>&nbsp;</td>
      <td class='tha'; style='width: 8%;'>&nbsp;Document&nbsp;no&nbsp;</td>
      <td class='tha'; style='width: 38%;'>&nbsp;Delivery&nbsp;Point&nbsp;</td>
      <td class='tha' style='width: 8%; '>&nbsp;&nbsp;Cases&nbsp;</td>
      <td class='tha' style='width: 8%; '>&nbsp;&nbsp;Weight&nbsp;</td>
      <td class='tha' style='width: 8%; '>&nbsp;Arrive&nbsp;Time&nbsp;</td>
      <td class='tha' style='width: 8%; '>&nbsp;Depart&nbsp;Time&nbsp;</td>
      <td class='tha' style='width: 9%; '>&nbsp;Customer&nbsp;Signiture&nbsp;</td>
      <td style='width: 5%;  border-style:none';>&nbsp;</td>
   </tr>
   
<?php
$totQ=0; $totwt=0;
foreach($tsDT as $row) {
	
     $totQ = $totQ  + $row['Cases']; 
     $totwt= $totwt + $row['Weight'];	
?>
      <tr>
      	<td>&nbsp;</td>
        <td class='dC'; ><?php echo $row['Docno']?></td>
        <td class='dC'; ><?php echo $row['Store']?></td>
        <td class='dC'; style='text-align:right;'>&nbsp;<?php echo $row['Cases']?>&nbsp;</td>
        <td class='dC'; style='text-align:right;'>&nbsp;<?php echo round($row['Weight'],2)?>&nbsp;</td>
        <td class='dC'; >&nbsp;</td>
        <td class='dC'; >&nbsp;</td>
        <td class='dC'; >&nbsp;</td>
      </tr>
      <tr>
      	<td>&nbsp;</td>
        <td class='dC'; >&nbsp;</td>
        <td class='dC'; >&nbsp;</td>
        <td class='dC'; >&nbsp;</td>
        <td class='dC'; >&nbsp;</td>
        <td class='dC'; >&nbsp;</td>
        <td class='dC'; >&nbsp;</td>
        <td class='dC'; >&nbsp;</td>
      </tr>

      <tr>
      	<td>&nbsp;</td>
        <td class='db'; >&nbsp;</td>
        <td class='db'; >&nbsp;</td>
        <td class='db'; >&nbsp;</td>
        <td class='db'; >&nbsp;</td>
        <td class='db'; >&nbsp;</td>
        <td class='db'; >&nbsp;</td>
        <td class='db'; >&nbsp;</td>
      </tr>

<?php } ?>   
   
      <tr>
      	<td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>Totals</td>
        <td class='db'; style='text-align:right;'>&nbsp;<?php echo $totQ; ?>&nbsp;</td>
        <td class='db'; style='text-align:right;'>&nbsp;<?php echo round($totwt,2); ?>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
</table>

<table style='width: 85%; border-style:none; border-collapse: collapse;'>
     <tr>
         <td style='width:5%; '>&nbsp;</td>
         <td style='width:43%;'>&nbsp;</td>
         <td style='width:4%;'>&nbsp;</td>
         <td style='width:43%;'>&nbsp;</td> 
         <td style='width:5%;'>&nbsp;</td>

     </tr>
     <tr>
         <td>&nbsp;</td>
         <td>&nbsp;</td>
     </tr>
     <tr>
         <td>&nbsp;</td>
         <td colspan="5" style='text-align:left';>Goods accpted in good condition</td>
     </tr>
     <tr>
         <td colspan="5">&nbsp;</td>
     </tr>
     <tr>
     	   <td>&nbsp;</td>
         <td style='style='text-align:left';>Collected at (Time)&nbsp&nbsp;&nbsp;____________________________________________________</td>
         <td>&nbsp;</td>
         <td style='style='text-align:right';>Vehicle Registration&nbsp&nbsp;&nbsp;____________________________________________________</td>
         <td>&nbsp;</td>     </tr>   
      <tr>
         <td colspan="5">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="5">&nbsp;</td>
     </tr>
      <tr>
         <td colspan="5">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="5">&nbsp;</td>
      </tr>
          <tr>
     	   <td>&nbsp;</td>
         <td style='style='text-align:left';>Dispatched by (Print Name)&nbsp&nbsp;&nbsp;____________________________________________________</td>
         <td>&nbsp;</td>
         <td style='style='text-align:right';>Dispatched by (Signiture)&nbsp&nbsp;&nbsp;____________________________________________________</td>
         <td>&nbsp;</td>     </tr>   
      <tr>
      <tr>
         <td colspan="5">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="5">&nbsp;</td>
     </tr>
      <tr>
         <td colspan="5">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="5">&nbsp;</td>
      </tr>
          <tr>
     	   <td>&nbsp;</td>
         <td style='style='text-align:left';>Collected by (Print Name)&nbsp&nbsp;&nbsp;____________________________________________________</td>
         <td>&nbsp;</td>
         <td style='style='text-align:right';>Collected by (Signiture)&nbsp&nbsp;&nbsp;____________________________________________________</td>
         <td>&nbsp;</td>     </tr>   
      <tr>
      <tr>
         <td colspan="5">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="5">&nbsp;</td>
      </tr>          				
</table>
<table style='width: 85%; border-style:none; border-collapse: collapse;'>
      <tr>
         <td colspan="5";>&nbsp;</td>
      </tr>
      <tr>
         <td colspan="5";>&nbsp;</td>
      </tr>
      <tr>
         <td colspan="5";>&nbsp;</td>
      </tr>      
      <tr>
         <td colspan="5";>&nbsp;</td>
      </tr>  
      <tr>
         <td colspan="5";>&nbsp;</td>
      </tr>  
	    <tr>
         <td style='width: 20%';>&nbsp;</td>
         <td style='width: 20%';>&nbsp;</td>
         <td style='width: 12%';>&nbsp;</td>
         <td rowspan='2'; style='width: 18%';><img src="<?php echo $ROOT.$PHPFOLDER; ?>images/kwelanga1.gif" style="width:80px; height:35px; float:right;" ></td>
         <td style='width: 5%';>&nbsp;</td>
      </tr>
      <tr>
         <td colspan="3";>&nbsp;</td>
      </tr>
      <tr>
         <td colspan="3";>&nbsp;</td>
         <td colspan="1"; style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo date('Y-m-d H:i:s'); ?></td>
         <td colspan="1";>&nbsp;</td>
      </tr>
      <tr>
         <td colspan="3";>&nbsp;</td>
         <td colspan="1"; style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo 'document_trip_sheet_304'; ?></td>
         <td colspan="1";>&nbsp;</td>
      </tr>
</table>	
</BODY>
</HTML>





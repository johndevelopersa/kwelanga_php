<!DOCTYPE html>
<html>
<head>
   <link   href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_template.css' rel='stylesheet' type='text/css'>

   <script type='text/javascript' language='javascript' src='<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js'></script>
</head>

<body style="font-family:Verdana,Arial,Helvetica,sans-serif;margin:0px;padding:0px;">


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
<TITLE>Bulk Picking List View</TITLE>
</div>
<table style="border-style:none; border-collapse:collapse; width:75%;">
     <tr>
        <td>&nbsp;</td>
        <td class="dc" colspan="6" style="text-align:left;">Picking List</TD>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td style="width:  5%; text-align:left;">&nbsp;</td>
        <td style="width: 20%; text-align:left;">&nbsp;</td>
        <td style="width: 30%; text-align:left;">&nbsp;</td>
        <td style="width: 10%; text-align:left;">&nbsp;</td>         	
        <td style="width: 10%; text-align:left;">&nbsp;</td>
        <td style="width: 10%; text-align:left;">&nbsp;</td>         	
        <td style="width: 10%; text-align:left;">&nbsp;</td>
        <td style="width:  5%; text-align:left;">&nbsp;</td>
    </tr>	
    <tr>
        <td>&nbsp;</td>
        <td class="dc3h" colspan="6" style="text-align:left;"><?php echo $psHD[0]['Depot']; ?></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="8" >&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="dc12h" colspan="1" style="text-align:left;">Picking List Number</td>
        <td class="dc12"  colspan="2" style="text-align:left;"><?php echo $psHD[0]['pick_list_number']?></td>
        <td class="dc12h" colspan="1" style="text-align:left;">Date & Time</td>
        <td class="dc12"  colspan="2" style="text-align:left;"><?php echo $psHD[0]['DT']?></td>                
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="8" >&nbsp;</td>
    </tr>
    <tr>
    	  <?php if($psHD[0]['delivery_instructions'] <> NULL) { ?>
           <td>&nbsp;</td>
           <td class="dc12h" colspan="1" style="text-align:left;">Additional Details</td>
           <td class="dc12"  colspan="2" style="text-align:left;"><?php echo $psHD[0]['delivery_instructions']; ?></td>
           <td class="dc12"  colspan="1" style="text-align:left;">&nbsp;</td>
           <td class="dc12"  colspan="2" style="text-align:left;">&nbsp;</td>                
           <td>&nbsp;</td>
        <?php } ?>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="th12" colspan="1" style="text-align:left; padding:3px;">Product Code</td>
        <td class="th12" colspan="2" style="text-align:left; padding:3px;">Product&nbsp;Description</td>
        <td class="th12" colspan="1" style="text-align:center; padding:3px; ">Weight</td>
        <td class="th12" colspan="1" style="text-align:center; padding:3px;">Qty</td>
        <td class="th12" colspan="1" style="text-align:center; padding:3px;">Picked&nbsp;Qty</td>        
        <td>&nbsp;</td>
    </tr>
         <?php
         $totW=$totQ=0;
         foreach($psDT as $row) { ?>
    
              <tr>
              <td>&nbsp;</td>
              <td class="dc5" colspan="1" style="text-align:left; padding:3px;"><?php echo $row['Product Code']?></td>
              <td class="dc5" colspan="2" style="text-align:left; padding:3px;"><?php echo $row['Product']?></td>
              <td class="dc5" colspan="1" style="text-align:center; padding:3px;"><?php echo $row['Ordered_Quantity'] * $row['Weight']?></td>
              <td class="dc5" colspan="1" style="text-align:center; padding:3px;"><?php echo $row['Ordered_Quantity'] ?></td>
              <td class="dc5" colspan="1" style="text-align:center;">&nbsp</td>        
              <td>&nbsp;</td>
         </tr>
        <tr>
              <td>&nbsp;</td>
              <td class="dc5" colspan="1" style="text-align:left;">&nbsp</td>
              <td class="dc5" colspan="2" style="text-align:left;">&nbsp</td>
              <td class="dc5" colspan="1" style="text-align:left;">&nbsp</td>
              <td class="dc5" colspan="1" style="text-align:left;">&nbsp</td>
              <td class="dc5" colspan="1" style="text-align:left;">&nbsp</td>        
              <td>&nbsp;</td>
         </tr>    
<?php
              $totQ+= $row['Ordered_Quantity'];
              $totW+= $row['Ordered_Quantity'] * $row['Weight'];
         }
         ?> 
        <tr>
              <td>&nbsp;</td>
              <td class="dc6" colspan="1" style="text-align:left;">&nbsp</td>
              <td class="dc6" colspan="2" style="text-align:left;">&nbsp</td>
              <td class="dc8" colspan="1" style="text-align:center;"><?php echo $totW;?></td>
              <td class="dc8" colspan="1" style="text-align:center;"><?php echo $totQ;?></td>
              <td class="dc6" colspan="1" style="text-align:left;">&nbsp</td>        
              <td>&nbsp;</td>
         </tr>
       <tr>
              <td colspan="8" >&nbsp;</td>
       </tr>
       <tr>
              <td colspan="8" >&nbsp;</td>
       </tr>
       <tr>
              <td>&nbsp;</td>
              <td class="dc12h" colspan="4" style="text-align:left;">Documents&nbsp;on&nbsp;Picking&nbsp;List</td>
              <td class="dc12"  colspan="2" style="text-align:left;">&nbsp</td>     
              <td>&nbsp;</td>
       </tr>    
       <tr>
              <td colspan="8" >&nbsp;</td>
       </tr>
       <tr>
              <td>&nbsp;</td>
              <td class="dc12h" colspan="1" style="text-align:left;">Document&nbsp;No</td>
              <td class="dc12h" colspan="2" style="text-align:left;">Store</td>
              <td class="dc12h"  colspan="1" style="text-align:right;">Total&nbsp;Cases</td>
              <td class="dc12"  colspan="1" style="text-align:left;">&nbsp;</td>     
              <td>&nbsp;</td>
       </tr>    
       <?php    
           foreach($psHD as $row) { ?>
               <tr>
                   <td>&nbsp;</td>
                   <td class="dc12" colspan="1" style="text-align:left;"><?php echo substr($row['document_number'],3,6)?></td>
                   <td class="dc12" colspan="2" style="text-align:left;"><?php echo $row['deliver_name']?></td>
                   <td class="dc12" colspan="1" style="text-align:right;"><?php echo $row['cases']?></td>
                </tr>
               <tr>
                   <td>&nbsp;</td>
                   <td class="dc12" colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc12" colspan="2" style="text-align:left;">&nbsp;</td>
                   <td class="dc12" colspan="1" style="text-align:right;">&nbsp;</td>
                </tr>
                </tr>
           <?php
           } ?>
       <tr>
              <td colspan="8" >&nbsp;</td>
       </tr>
       <tr>
              <td colspan="8" >&nbsp;</td>
       </tr>
       <tr>
              <td>&nbsp;</td>
              <td class="dc12h" colspan="2" style="text-align:left;">Picked&nbsp;by&nbsp;(Print&nbsp;Name)</td>
              <td class="dc7"   colspan="4" style="text-align:left;">&nbsp;</td>
              <td>&nbsp;</td>
        </tr>  
       <tr>
              <td colspan="8" >&nbsp;</td>
       </tr>
       <tr>
              <td colspan="8" >&nbsp;</td>
       </tr>
</table>
<table style="width:75%;"> 
       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
           <td rowspan="2"><img src="<?php echo $ROOT.$PHPFOLDER; ?>images/Kwelanga_Solutions_Logo_smaller.jpg" style="width:60px; height:40px; float:right;" ></td>
           <td width="12%;">&nbsp;</td>
       </tr>
       <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
       </tr>
       <tr>
             <td colspan="3"; style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo date('Y-m-d H:i:s'); ?></td>
       </tr>
       <tr>
            <td colspan="3"; style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo 'document_bulk_pick_list'; ?></td>
       </tr>

</table> 
	
	</BODY>
</HTML>

<!DOCTYPE html>
<HTML>
<HEAD>
    <link   href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_template.css' rel='stylesheet' type='text/css'>
     <script type='text/javascript' language='javascript' src='<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js'></script>
</HEAD> 

<TITLE>Trip Sheet - Print</TITLE>

    <body>
       <div align="center" id="noprint" class="disableprint" >
         <table id="wrapper" cellspacing="0" cellpadding="0">
          <tr>
            <td>
             <div id="noprint"><!-- HIDE THIS PRINT AREA : START /--->
               <div id="toolbar">
                 <a href="javascript:window.print();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/print-icon.png" border="0" alt="" align="left" > PRINT</a>
                  <div style="clear:both;"></div>
               </div>
             </div><!-- HIDE THIS PRINT AREA : END /--->
            </td>
          </tr>
         </table>
       </div>
    <table style="border-collapse:collapse; width:70%">
         <tr>
            <td style="width:  2%; text-align:left;">&nbsp;</td>
            <td style="width: 10%; text-align:left;">&nbsp;</td>
            <td style="width: 10%; text-align:left;">&nbsp;</td>
            <td style="width: 10%; text-align:left;">&nbsp;</td>         	
            <td style="width: 10%; text-align:left;">&nbsp;</td>
            <td style="width: 10%; text-align:left;">&nbsp;</td>
            <td style="width: 10%; text-align:left;">&nbsp;</td>
            <td style="width: 10%; text-align:left;">&nbsp;</td>
            <td style="width: 10%; text-align:left;">&nbsp;</td>
            <td style="width: 16%; text-align:left;">&nbsp;</td>
            <td style="width:  2%; text-align:left;">&nbsp;</td>
         </tr>
         <tr>
            <td style="text-align:left;">&nbsp;</td>
            <td class="dc" colspan="7" style="text-align:left;">Trip Sheet</td>
            <td class="dc" colspan="3" rowspan="3" align:right;"><img alt="<?php echo $tsDT[0]['Depot_Uid']  . '- ' .  ltrim(substr($tsDT[0]['TripNo'],0,6)); ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $tsDT[0]['Depot_Uid'] . ' - ' . ltrim(substr($tsDT[0]['TripNo'],0,6)); ?>&print=true" /></td>
            <td style="text-align:left;">&nbsp;</td>
         </tr>
         <tr> 
         	 <td colspan="1">&nbsp;</td>             
            <td colspan="3">&nbsp;</td>     
         </tr>             
         <tr>
            <td colspan="1">&nbsp;</td>
            <td class="dc2" colspan="3" style="text-align:left;"><?php echo $tsDT[0]['Depot']; ?></TD>
         </tr>
         <tr>              
            <td colspan="3">&nbsp;</td>     
         </tr>  
         <tr>
            <td colspan="1">&nbsp;</td>
            <td class="dc3h" colspan="6" style="text-align:left;">Collection By</td>
            <td class="dc3h" colspan="2" style="text-align:right;">Tripsheet Number</td>
            <td class="dc3"  colspan="2" style="text-align:right;"><?php echo substr($tsDT[0]['TripNo'],0,6); ?></td>
            <td colspan="1">&nbsp;</td>
         </tr>
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>  
         <tr>
            <td colspan="1">&nbsp;</td>
            <td class="dc3" colspan="6" style="text-align:left;"><?php echo $tsDT[0]['Transporter_Name'] ?></td>  
            <td colspan="4">&nbsp;</td>
            <td colspan="1">&nbsp;</td>
         </tr>
         <tr>
            <td colspan="1">&nbsp;</td>
            <td class="dc3" colspan="6" style="text-align:left;"><?php echo $tsDT[0]['Transporter_Add1'] ?></td>
            <td class="dc3h" colspan="2" style="text-align:left;">Date&nbsp;&&nbsp;Time</td>   
            <td class="dc3"  colspan="2" style="text-align:right;"><?php echo $tsDT[0]['Date']; ?></td> 
            <td colspan="1" >&nbsp;</td>      	
         </tr>      	
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>  
         <tr>
            <td colspan="1" >&nbsp;</td>
            <td class="dc3h" colspan="2" style="text-align:left;" >Captured By</td>
            <td class="dc3"  colspan="2" style="text-align:left;" ><?php echo $tsDT[0]['User']; ?></td>
            <td colspan="4" >&nbsp;</td>      	
         </tr>
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>  
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>  
   
         <tr>
            <td colspan="1" style="text-align:left;">&nbsp;</td>
            <td class="th1" colspan="1" style="text-align:left;">Principal</td>
            <td class="th1" colspan="1" style="text-align:left;">Document&nbsp;No</td>
            <td class="th1" colspan="3" style="text-align:left; padding:3px;">Delivery&nbsp;Point</td>
            <td class="th1" colspan="1" style="text-align:centre; padding:3px;">Cases</td>
            <td class="th1" colspan="1" style="text-align:centre; padding:3px;">GRV&nbsp;No</td>
            <td class="th1" colspan="1" style="text-align:left;  padding:3px;">CFG</td>
            <td class="th1" colspan="1" style="text-align:left;  padding:3px;">LAS</td>
            <td colspan="1" style="text-align:left;">&nbsp;</td>         
         </tr>
         <?php
         $totQ=0; $totwt=0;
//         echo "<pre>";
//         print_r($tsDT);
         
         
         foreach($tsDT as $row) { ?>
             <tr>
                <td colspan="1" style="text-align:left;">&nbsp;</td>
                <td class='dc5' colspan="1" nowrap><?php echo $row['short_name']?></td>
                <td class='dc5' colspan="1" nowrap><?php echo $row['lDocno']?></td>
                <td class='dc5' colspan="3" nowrap><?php echo $row['Store']?></td>
                <td class='dc5' colspan="1" style='text-align:center;' nowrap><?php echo $row['Cases'] ; ?></td>
                <td class='dc5' colspan="1" style='text-align:center;' nowrap>&nbsp;</td>
                <td class='dc5' colspan="1" style='text-align:center;' nowrap>&nbsp;</td>
                <td class='dc5' colspan="1" style='text-align:center;' nowrap>&nbsp;</td>
                <td colspan="1" style="text-align:left;">&nbsp;</td>
             </tr>
             <tr>
                <td colspan="1" style="text-align:left;">&nbsp;</td>
                <td class='dc5' colspan="1" nowrap>&nbsp;</td>
                <td class='dc5' colspan="1" nowrap>&nbsp;</td>
                <td class='dc5' colspan="3" nowrap>&nbsp;</td>
                <td class='dc5' colspan="1" style='text-align:center;' nowrap>&nbsp;</td>
                <td class='dc5' colspan="1" style='text-align:center;' nowrap>&nbsp;</td>
                <td class='dc5' colspan="1" style='text-align:center;' nowrap>&nbsp;</td>
                <td class='dc5' colspan="1" style='text-align:center;' nowrap>&nbsp;</td>
                <td colspan="1" style="text-align:left;">&nbsp;</td>
             </tr>

        <?php
         $totQ+= $row['Cases'];
         $totwt+= $row['Weight'];
         } ?>
         <tr>          	
             <td colspan="1" style="text-align:left;">&nbsp;</td>
             <td class='dc6' colspan="5" nowrap>&nbsp;</td>
             <td class='dc8' colspan="1" style='text-align:center;' nowrap><?php echo $totQ;?></td>
             <td class='dc8' colspan="1" style='text-align:center;' nowrap>&nbsp;</td>
             <td class='dc6' colspan="2" style='text-align:center;' nowrap>&nbsp;</td>
             <td colspan="1" style="text-align:left;">&nbsp;</td>
         </tr>
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>
         <tr>
            <td colspan="1" >&nbsp;</td>
            <td class="dc3" colspan="2" style="text-align:left;" >Dispatch Number</td>
            <td class="dc7" colspan="3" style="text-align:left;" >&nbsp;</td>
            <td colspan="5" >&nbsp;</td>    	
         </tr> 
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>
            <td colspan="1" >&nbsp;</td>
            <td class="dc3h" colspan="10" style="text-align:left;" >Goods accpted in good condition</td>
            <td colspan="1" >&nbsp;</td>    	
         </tr> 
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>
         <tr>
            <td colspan="1" >&nbsp;</td>
            <td class="dc3" colspan="2" style="text-align:left;" >Collected at (Time)</td>
            <td class="dc7" colspan="3" style="text-align:left;" >&nbsp;</td>
            <td class="dc3" colspan="2" style="text-align:left;" >Vehicle Registration</td>
            <td class="dc7" colspan="2" style="text-align:left;" >&nbsp;</td>         
            <td colspan="1" >&nbsp;</td>    	
         </tr> 
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>
         <tr>
            <td colspan="1" >&nbsp;</td>
            <td class="dc3" colspan="2" style="text-align:left;" >Dispatched by (Print Name)</td>
            <td class="dc7" colspan="3" style="text-align:left;" >&nbsp;</td>
            <td class="dc3" colspan="2" style="text-align:left;" >Dispatched by (Signiture)</td>
            <td class="dc7" colspan="2" style="text-align:left;" >&nbsp;</td>         
            <td colspan="1" >&nbsp;</td>    	
         </tr> 
         <tr>
            <td colspan="1" >&nbsp;</td>
            <td class="dc3" colspan="2" style="text-align:left;" >Collected by (Print Name)</td>
            <td class="dc7" colspan="3" style="text-align:left;" >&nbsp;</td>
            <td class="dc3" colspan="2" style="text-align:left;" >Collected by (Signiture)</td>
            <td class="dc7" colspan="2" style="text-align:left;" >&nbsp;</td>         
            <td colspan="1" >&nbsp;</td>    	
         </tr>
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>
         <tr>
            <td colspan="1" style="text-align:left;">&nbsp;</td>
            <td class="th1" colspan="2" style="text-align:left;">Inv No</td>
            <td class="th1" colspan="2" style="text-align:left;">Claim&nbsp;No</td>
            <td class="th1" colspan="4" style="text-align:left; padding:3px;">Reason&nbsp;Claim</td>
            <td class="th1" colspan="1" style="text-align:centre; padding:3px;">Pallet&nbsp;No</td>
            <td colspan="1" colspan="1" style="text-align:left;  padding:3px;">&nbsp;</td>
            <td colspan="1" style="text-align:left;">&nbsp;</td>         
         </tr>
         <tr>
            <td colspan="1" style="text-align:left;">&nbsp;</td>
            <td class="lr" colspan="2" style="text-align:left;">&nbsp;</td>
            <td class="lr" colspan="2" style="text-align:left;">&nbsp;</td>
            <td class="lr" colspan="4" style="text-align:left; padding:3px;">&nbsp;</td>
            <td class="lr" colspan="1" style="text-align:centre; padding:3px;">&nbsp;</td>
            <td style="text-align:left;  padding:3px;">&nbsp;</td>
            <td colspan="1" style="text-align:left;">&nbsp;</td>         
         </tr>
         <tr>
            <td colspan="1" style="text-align:left;">&nbsp;</td>
            <td class="lr" colspan="2" style="text-align:left;">&nbsp;</td>
            <td class="lr" colspan="2" style="text-align:left;">&nbsp;</td>
            <td class="lr" colspan="4" style="text-align:left; padding:3px;">&nbsp;</td>
            <td class="lr" colspan="1" style="text-align:centre; padding:3px;">&nbsp;</td>
            <td style="text-align:left;  padding:3px;">&nbsp;</td>
            <td colspan="1" style="text-align:left;">&nbsp;</td>         
         </tr>
         <tr>
            <td colspan="1" style="text-align:left;">&nbsp;</td>
            <td class="lr" colspan="2" style="text-align:left;">&nbsp;</td>
            <td class="lr" colspan="2" style="text-align:left;">&nbsp;</td>
            <td class="lr" colspan="4" style="text-align:left; padding:3px;">&nbsp;</td>
            <td class="lr" colspan="1" style="text-align:centre; padding:3px;">&nbsp;</td>
            <td style="text-align:left;  padding:3px;">&nbsp;</td>
            <td colspan="1" style="text-align:left;">&nbsp;</td>         
         </tr>
         <tr>
            <td colspan="1" style="text-align:left;">&nbsp;</td>
            <td class="lr" colspan="2" style="border-bottom-style:solid; border-bottom-width:1px; text-align:left;">&nbsp;</td>
            <td class="lr" colspan="2" style="border-bottom-style:solid; border-bottom-width:1px; text-align:left;">&nbsp;</td>
            <td class="lr" colspan="4" style="border-bottom-style:solid; border-bottom-width:1px; text-align:left; padding:3px;">&nbsp;</td>
            <td class="lr" colspan="1" style="border-bottom-style:solid; border-bottom-width:1px; text-align:centre; padding:3px;">&nbsp;</td>
            <td style="text-align:left;  padding:3px;">&nbsp;</td>
            <td colspan="1" style="text-align:left;">&nbsp;</td>         
         </tr>
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>         
         <tr>
            <td colspan="1" >&nbsp;</td>
            <td class="dc3" colspan="5" style="text-align:left;" >&nbsp;</td>
            <td  colspan="4" style="text-align:left;" style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
                       <img src="<?php echo $ROOT.$PHPFOLDER; ?>images/Kwelanga_Solutions_Logo_smaller.jpg" style="width:60px; height:40px; float:right;" ></td>
           <td colspan="1" >&nbsp;</td>    	
         </tr>
         <tr>
            <td colspan="1" >&nbsp;</td>
            <td class="dc3" colspan="5" style="text-align:left;" >&nbsp;</td>
            <td  colspan="4" style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
                                                                                  <?php echo date('Y-m-d H:i:s'); ?> </td>
           <td colspan="1" >&nbsp;</td>    	
         </tr> 
         <tr>
            <td colspan="1" >&nbsp;</td>
            <td class="dc3" colspan="5" style="text-align:left;" >&nbsp;</td>
            <td  colspan="4" style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo 'nel_trip_sheet_new'; ?>
           </td>
           <td colspan="1" >&nbsp;</td>    	
         </tr> 
         <tr>              
            <td colspan="11">&nbsp;</td>   
         </tr>
       </table>
   
	
	</BODY>
</HTML>

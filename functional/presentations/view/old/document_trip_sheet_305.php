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
		
		td.dc   {font-weight:normal; 
			       border-collapse: collapse;
			       border-style:solid;
			       border-width:0.05px;
			       height:15px
		        }
		      
		td.dh   {text-align:left; 
			       font-weight:bold; 
			       font-size:1.1em;			     
		        }

		td.dhl  {text-align:right; 
			       font-weight:bold; 
			       font-size:1.1em;			     
		        }
		td.dd   {text-align:left; 
			       font-weight:normal; 
			       font-size:1.0em;			     
		        }
		td.ddl  {text-align:right; 
			       font-weight:normal; 
             font-size:1.0em;			     
		        }

		td.dds  {text-align:left; 
			       font-weight:normal; 
			       font-size:1.0em;			     
		        }
		td.ddls {text-align:right; 
			       font-weight:normal; 
             font-size:1.3em;			     
		        }

    td.topr {text-align:center;
			       font-weight:bold; 
			       font-size:1.0em;			    
			       border-style:solid;
			       border-width:0.05px; 
			       border-collapse: collapse; 	
            }
    td.detr {text-align:center;
			       font-weight:normal; 
			       font-size:0.9em;			    
			       border-right-style:solid;
			       border-left-style:solid;
			       border-top-style:solid;
			       border-width:0.05px; 
			       border-collapse: collapse; 	
            }

    td.detrs {text-align:center;
			       font-weight:normal; 
			       font-size:0.9em;			    
			       border-right-style:solid;
			       border-left-style:solid;
			       border-width:0.05px; 
			       border-collapse: collapse; 	
            }

    td.foot1 {text-align:right; 
    	        border-top-style:solid;
    	        border-left-style:solid; 
    	        border-right-style:solid;  
    	        border-width:0.05px; 
    	        font-weight:bold; 
    	        font-size:1.0em;
    	        }
    td.foot1s{text-align:right; 
    	        border-bottom-style:solid;
    	        border-left-style:solid; 
    	        border-right-style:solid;  
    	        border-width:0.05px; 
    	        font-weight:bold; 
    	        font-size:1.1em;
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
<table style= 'border-collapse:collapse; width:95%'>        
	<tr>
    <td class="dc0" style='width:1%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
  </tr>
  <tr>
  	<td class="dc0">&nbsp;</td>
  	<td class="dh" colspan='2'>Driver Log Sheet</td>
  	<td colspan="5">&nbsp;</td>   
 	  <td class="dhl" colspan="12"><?php echo trim($tsDT[0]['Principal']);?></td>   
  </tr> 
  <tr>
 	  <td class="dc0">&nbsp;</td>  	
  	<td class="dd" colspan="6" nowrap ><span>Date :&nbsp;</span><span class="dds" ><?php echo $tsDT[0]['Date']; ?></span></td>
   	<td colspan="4">&nbsp;</td>   
    <td class="ddl" colspan="10" nowrap ><span>Trip Sheet Number:&nbsp;&nbsp;&nbsp;</span><span class="ddsl" ><?php echo substr($tsDT[0]['TripNo'],0,6); ?></span></td>
  </tr> 
  <tr>
 	  <td class="dc0">&nbsp;</td>  	
  	<td class="dd" colspan="6" nowrap ><span>Driver :&nbsp;</span><span class="dds" ><?php echo $tsDT[0]['Transporter_Name'] ?></span></td>
   	<td colspan="4">&nbsp;</td>   
    <td class="ddl" colspan="10" nowrap ><span>&nbsp&nbsp;&nbsp;&nbsp;</span><span class="ddsl" >&nbsp</span></td>
  </tr>   	
  <tr>
    <td colspan="20">&nbsp;</td>   
  </tr>   	
  <tr>
 	  <td class="dc0">&nbsp;</td>  	
  	<td class="dd" colspan="1" >Vehicle&nbsp;Reg</td>
  	<td class="dc" colspan="6" >&nbsp;</td>
    <td colspan="4">&nbsp;</td> 
  	<td class="dc0" colspan="2" text-align:right; >&nbsp;</td>
  	<td class="dc0" colspan="6" >&nbsp;
   </tr>   
  <tr>
    <td colspan="20">&nbsp;</td>   
  </tr>   
  <tr>
 	  <td class="dc0">&nbsp;</td>  	
    <td class="topr" colspan="2" style='border-style:none; text-align:left;'>Driver&nbsp;Equipment</td>
    <td colspan="8">&nbsp;</td>   
    <td class="topr" colspan="9" style='border-style:none; text-align:right;'>Driver&nbsp;Check</td>
  </tr>   	
  <tr>
    <td colspan="20">&nbsp;</td>   
  </tr>   
  <tr>
 	  <td class="dc0">&nbsp;</td>  	
    <td class="dc" colspan="1">&nbsp;</td>   
    <td class="dc" colspan="3">Driver Accepted</td>
    <td class="dc" colspan="3">Manager Confirm Returned</td>

    <td class="ddl" colspan="2">&nbsp;</td>   
 
    <td class="dc" colspan="2">Manager Out</td>   
    <td class="dc" colspan="3">&nbsp;</td>   
    <td class="dc" colspan="2">Manager In</td>
    <td class="dc" colspan="4">&nbsp;</td>   

  </tr>   	
  <tr>
 	  <td class="dc0">&nbsp;</td>  	
    <td class="dc" colspan="1">GPS No </td>   
    <td class="dc" colspan="3">&nbsp;</td>
    <td class="dc" colspan="3">&nbsp;</td>

    <td class="ddl" colspan="2">&nbsp;</td>   
 
    <td class="dc" colspan="2">&nbsp;</td>   
    <td class="dc" colspan="3">&nbsp;</td>   
    <td class="dc" colspan="2">&nbsp;</td>
    <td class="dc" colspan="4">&nbsp;</td>
  </tr>   
  <tr>
 	  <td class="dc0">&nbsp;</td>  	
    <td class="dc" colspan="1">Charger/s</td>   
    <td class="dc" colspan="3">&nbsp;</td>
    <td class="dc" colspan="3">&nbsp;</td>

    <td class="ddl" colspan="2">&nbsp;</td>   
 
    <td class="dc" colspan="2">Time Out</td>   
    <td class="dc" colspan="3">&nbsp;</td>   
    <td class="dc" colspan="2">Time In</td>
    <td class="dc" colspan="4">&nbsp;</td>
  </tr>   
  <tr>
 	  <td class="dc0">&nbsp;</td>  	
    <td class="dc" colspan="1">Spare Wheel</td>   
    <td class="dc" colspan="3">&nbsp;</td>
    <td class="dc" colspan="3">&nbsp;</td>

    <td class="ddl" colspan="2">&nbsp;</td>   
 
    <td class="dc" colspan="2">&nbsp;</td>   
    <td class="dc" colspan="3">&nbsp;</td>   
    <td class="dc" colspan="2">&nbsp;</td>
    <td class="dc" colspan="4">&nbsp;</td>  
  </tr>    
  <tr>
 	  <td class="dc0">&nbsp;</td>  	
    <td class="dc" colspan="1">Jack</td>   
    <td class="dc" colspan="3">&nbsp;</td>
    <td class="dc" colspan="3">&nbsp;</td>

    <td class="ddl" colspan="2">&nbsp;</td>   
 
    <td class="dc" colspan="2">&nbsp;</td>   
    <td class="dc" colspan="3">&nbsp;</td>   
    <td class="dc" colspan="2">Log Sheet Recon</td>
    <td class="dc" colspan="4">&nbsp;</td>   
  </tr>   
  <tr>
 	  <td class="dc0">&nbsp;</td>  	
    <td class="dc" colspan="1">Spanner</td>   
    <td class="dc" colspan="3">&nbsp;</td>
    <td class="dc" colspan="3">&nbsp;</td>

        <td class="ddl" colspan="2">&nbsp;</td>   
 
    <td class="dc" colspan="2">&nbsp;</td>   
    <td class="dc" colspan="3">&nbsp;</td>   
    <td class="dc" colspan="2">Credits Recon</td>
    <td class="dc" colspan="4">&nbsp;</td>    
  </tr>   
  <tr>
 	  <td class="dc0">&nbsp;</td>  	
    <td class="dc" colspan="1">Triangle</td>   
    <td class="dc" colspan="3">&nbsp;</td>
    <td class="dc" colspan="3">&nbsp;</td>

    <td class="ddl" colspan="10">&nbsp;</td>   
  </tr>

  <tr>
    <td class="dc0" >&nbsp;</td>
    <td class="topr" colspan='4' style='text-align:left; border-style:none;'>Detail Rows</td>
    <td colspan="15">&nbsp;</td>
  </tr>  
  <tr>
    <td colspan="20">&nbsp;</td>   
  </tr> 

</table>
       



<table style= 'border-collapse:collapse; width:95%'>
<tr>
  <td class="dc0" style='width:1%';>&nbsp;</td>
  <td class="topr" style='width:40%; text-align:left;'>Customer</td>
  <td class="topr" style='width:6%';>Document No</td>
  <td class="topr" style='width:10%;'>PO Number</td>
  <td class="topr" style='width:5%;'>Loaded Qty</td>
  <td class="topr" style='width:5%;'>Delivered Qty</td>
  <td class="topr" style='width:5%;'>Weight</td>
  <td class="topr" style='width:6%;'>Time In</td>
  <td class="topr" style='width:6%;'>Time Out</td>
  <td class="topr" style='width:6%;'>Stops in Order</td>
  <td class="topr" style='width:10%;'>Signed</td>

</TR>


<?php
// print_r($tsDT);
$totQ=0; $totwt=0;
foreach($tsDT as $row) {
?>
  <tr>
     <td class="dc0">&nbsp;</td>   
     <td class='detr' style='text-align:left;'><?php echo $row['Store']?></td>      	
     <td class='detr' style='text-align:left;'><?php echo $row['Docno']?></td>      	
     <td class="detr"><?php echo $row['po']?></td>
     <td class='detr'><?php echo $row['Cases']?></td>      	
     <td class="detr">&nbsp;</td>
     <td class='detr'><?php echo round($row['Weight'],2)?></td>      	
     <td class="detr">&nbsp;</td>
     <td class="detr">&nbsp;</td>
     <td class="detr">&nbsp;</td>
     <td class="detr">&nbsp;</td>

   </tr>
<?php
      $totQ+= $row['Cases'];
      $totwt+= $row['Weight'];
?>  	
  <tr>
    <td class="dc0">&nbsp;</td>          
    <td class="detrs">&nbsp;</td>          
    <td class="detrs">&nbsp;</td>          
    <td class="detrs">&nbsp;</td>         
    <td class="detrs">&nbsp;</td>           	
    <td class="detrs">&nbsp;</td>          
    <td class="detrs">&nbsp;</td>          
    <td class="detrs">&nbsp;</td>          
    <td class="detrs">&nbsp;</td>         
    <td class="detrs">&nbsp;</td>          
    <td class="detrs">&nbsp;</td>          
   </tr>
<?php } ?>  	

  <tr>
    <td>&nbsp;</td>          
    <td class="foot1" colspan="3" >Total on Invoices&nbsp;&nbsp;&nbsp;</td>          
    <td class="foot1" style='text-align:center';><?php echo $totQ ?></td>          
    <td class="foot1" style='text-align:center';>&nbsp;</td>         
    <td class="foot1" style='text-align:center';><?php echo $totwt ?></td>      
    <td style='border-top-style:solid; border-width:0.05px;'>&nbsp;</td>          
    <td style='border-top-style:solid; border-width:0.05px;'>&nbsp;</td>         
    <td style='border-top-style:solid; border-width:0.05px;'>&nbsp;</td>          
    <td style='border-top-style:solid; border-width:0.05px;'>&nbsp;</td>          
    </tr>
  <tr>
    <td>&nbsp;</td>          
    <td class="foot1s" colspan="3" >&nbsp;</td>          
    <td class="foot1s" >&nbsp;</td>          
    <td class="foot1s" >&nbsp;</td> 
    <td class="foot1s" >&nbsp;</td> 
  </tr>
  <tr>
    <td>&nbsp;</td>          
    <td class="foot1" colspan="3" >Total Loaded (Counted)&nbsp;&nbsp;&nbsp;</td>          
    <td class="foot1" style='text-align:center';>&nbsp;</td>          
    <td class="foot1" style='text-align:center';>&nbsp;</td>         
    <td class="foot1" style='text-align:center';>&nbsp;</td>      
  </tr>
  <tr>
    <td>&nbsp;</td>          
    <td class="foot1s" colspan="3" >&nbsp;</td>          
    <td class="foot1s" >&nbsp;</td>          
    <td class="foot1s" >&nbsp;</td> 
    <td class="foot1s" >&nbsp;</td> 
  </tr>
<table style= 'border-collapse:collapse; width:95%'>
	<tr>
    <td class="dc0" style='width:1%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>

</tr>
	<tr>
    <td class="dc0">&nbsp;</td>
    <td class="topr" Colspan="2">Total Stops Given</td>
    <td class="topr" Colspan="3">&nbsp;</td>
    <td class="dc0"  Colspan="1">&nbsp;</td>
    <td class="topr" Colspan="2">Total Stops Completed</td>
    <td class="topr" Colspan="3">&nbsp;</td>
    <td class="dc0"  Colspan="3">&nbsp;</td>    
    <td class="topr" Colspan="3" style='text-align:left';>&nbsp;Driver Rating&nbsp;&nbsp;</td>   
    <td class="topr" Colspan="3" style='text-align:right'>Good&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Poor&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>     
 </tr>
</table>  
	</BODY>
</HTML>

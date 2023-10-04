<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."elements/SignatureArea.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

$userId = ((isset($_GET["USERID"]))?$_GET["USERID"]:"");
$userCategory = ((isset($_GET["USERCATEGORY"]))?$_GET["USERCATEGORY"]:"");
$principalId = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:"");
$docmastId = ((isset($_GET["DOCMASTID"]))?$_GET["DOCMASTID"]:"");
$outputTyp = ((isset($_GET["OUTPUTTYP"]))?$_GET["OUTPUTTYP"]:"");

$dbConn->dbConnection();

// print_r($mfWB);
?>

<!DOCTYPE html>
<html>
   <title>Print Waybills</title>
    <head>
      <style type="text/css">
         #wrapper{width:700px;text-align:left;}
   
         #toolbar {font-size:12px;background:#047;padding:8px 10px}
         #toolbar a img{margin:2px 5px 2px 0px;}
         #toolbar a:hover{background:aliceBlue}
         #toolbar a{margin-right:10px;float:left;background:#fff;text-align:center;display:block;border:1px solid #047;padding:0px 8px;line-height:36px;text-decoration:none;color:#666;font-weight:bold;}
         #block{background:#fff;padding:10px 5px;border:1px solid #ccc;}
         .dtitle{text-align:left;}
   
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
          table.grid {
            border-collapse:collapse;
          }
          table.grid td, table.grid th {
            border:1px solid #aaa;
          }
          table.grid th {background:#efefef;}
          .bordUnderline{border-bottom:1px solid #333;height:30px;}
                   td.dc {background-color:white; 
                   color:black; 
                   font-weight:bold; 
                   font-size:20px;}

                   td.dc2 {background-color:white; 
                   color:black; 
                   font-weight:bold; 
                   font-size:18px;}

                   td.dc3 {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:15px;}

                   td.dc3a {background-color:white; 
                   color:black; 
                   font-weight:bold; 
                   font-size:15px;} 

                   td.dc4 {background-color:white; 
                   font-weight:normal; 
                   font-size:11px;
                   border-collapse:collapse;
                   border-left-style:solid;  border-left-color:black;  border-left-width:1px;
                   border-right-style:solid; border-right-color:black; border-right-width:1px;  }

                   td.dc5 {background-color:white; 
                   font-weight:normal; 
                   font-size:17px;
                   border-collapse:collapse;
                   border-left-style:solid;  border-left-color:black;  border-left-width:1px;
                   border-right-style:solid; border-right-color:black; border-right-width:1px;  }
                   
                   td.dc6 {border-collapse: collapse;
                   border-top-style:solid; border-top-color:black; border-top-width:1px;}
 
                   td.dc7 {border-collapse: collapse;
                   border-bottom-style:solid;   border-bottom-color:black;   border-bottom-width:1px; } 

                   td.dc7a {border-collapse: collapse;
                   border-top-style:dotted ;   border-top-color:black;   border-top-width:0.25px; } 

                   td.dc8 {border-collapse: collapse;
                   font-weight:normal; 
                   font-size:17px;
                   border-style:solid solid solid solid; border-color:black; border-width:1px; } 

                   td.wb1 {border-collapse: collapse; 
                   	       border-style: none} 
 
                   td.wb2 {border-collapse: collapse; 
                   	       border-style:solid solid solid solid; 
                   	       border-color:red; 
                   	       border-width:0.2px} 


                   
                   th.th1 {text-align:left; 
                   font-size:15px;
                   font-weight:bold; 
                   background-color:white; 
                   border-collapse:collapse; 
                   border-left-style:solid; border-left-width:1px; border-left-color:black;
                   border-right-style:solid; border-right-width:1px; border-right-color:black;
                   border-top-style:solid; border-top-width:1px; border-top-color:black;
                   border-bottom-style:solid; border-bottom-width:1px; border-bottom-color:black;}
                   
      </style>
      <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js"></script>
    </head>
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
     <table style="width:100%; border-collapse:collapse;">
      		<tr>
     			<td>&nbsp;</td>        			
     			<td colspan="12"; style="font-weight : bold; font-size:18px" ><br>Ordinary&nbsp;Inland<br>Parcel&nbsp;Post</td>
     				<?php 
                 $filename = "images/logos/sapostoffice.png";
                 $file     = HOST_SURESERVER_AS_USER.$PHPFOLDER.$filename;
                 if (file_exists($ROOT.$PHPFOLDER.$filename) == 1){
                    $logo = $ROOT.$PHPFOLDER.$filename;
             ?>
                  <td rowspan="2"; colspan="8"><?php echo "<img src=".$logo." style=width:150px; height:80px; float:right; >" ?></td>
             <?php } ?>    				
     			</tr>
     		<tr>
     			<td>&nbsp;</td>
     			<td colspan="2">No&nbsp;&nbsp;<?php echo str_pad($mfWB[0]["waybill_number"],6,"0" ,STR_PAD_LEFT); ?></td>
     			</tr>
     			<tr>
     		    <td class="wb1" style="width:2%">&nbsp;</td> <!--1 /--->     		
     	    	<td class="wb1" style="width:4%">&nbsp;</td>  <!--2  /--->
     	    	<td class="wb1" style="width:4%">&nbsp;</td>  <!--3  /--->   		
     		    <td class="wb1" style="width:4%">&nbsp;</td>  <!--4  /--->
     		    <td class="wb1" style="width:4%">&nbsp;</td>  <!--5  /--->
     	    	<td class="wb1" style="width:4%">&nbsp;</td>  <!--6  /--->
     	    	<td class="wb1" style="width:4%">&nbsp;</td>  <!--7  /--->
     	    	<td class="wb1" style="width:4%">&nbsp;</td>  <!--8 /--->
         		<td class="wb1" style="width:4%">&nbsp;</td>  <!--9 /--->
         		<td class="wb1" style="width:4%">&nbsp;</td>  <!--10  /--->
        		<td class="wb1" style="width:4%">&nbsp;</td>  <!--11 /--->            
        		<td class="wb1" style="width:5%">&nbsp;</td>  <!--12 /--->              
        		<td class="wb1" style="width:5%">&nbsp;</td>  <!--13  /--->      
        		<td class="wb1" style="width:5%">&nbsp;</td>  <!--14  /--->              
        		<td class="wb1" style="width:5%">&nbsp;</td>  <!--15 /--->              
        		<td class="wb1" style="width:5%">&nbsp;</td>  <!--16 /--->              
        		<td class="wb1" style="width:5%">&nbsp;</td>  <!--17 /--->              
        		<td class="wb1" style="width:5%">&nbsp;</td>  <!--18 /--->              
        		<td class="wb1" style="width:5%">&nbsp;</td>  <!--19 /--->              
        		<td class="wb1" style="width:18%">&nbsp;</td>  <!--20 /--->              
      		</tr>
     			  <td>&nbsp;</td>  
     				<td colspan="3">Office of Origin </td>
     				<td class="dc7" colspan="10"; style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        		<td class="wb1" style="width:5%">&nbsp;</td>  <!--17 /--->              
        		<td class="wb1" style="width:5%">Date</td>  <!--18 /--->              
        		<td class="wb1" style="width:5%">&nbsp;</td>  <!--19 /--->              
        		<td class="wb1" style="width:18%" "text-align:right;"><?php echo $mfWB[0]['invoice_date']; ?></td>  <!--20 /--->   
     				</tr>     			
      			<tr>
      	    <td>&nbsp;</td>  
     				<td class="dc7" colspan="17"; style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;</td>
     				</tr>    			
     			<tr>
     				<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
     				</tr>
     				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1">To</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3a" colspan="17"><?php echo $mfWB[0]["bill_name"]; ?></td> 
             </tr>
     				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1">&nbsp;</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3" colspan="17"><?php echo $mfWB[0]["bill_add1"]; ?></td> 
     				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1">&nbsp;</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3" colspan="17"><?php echo $mfWB[0]["bill_add2"]; ?></td> 
     				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1">&nbsp;</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3" colspan="17"><?php echo $mfWB[0]["bill_add3"]; ?></td> 
     			  <tr>
     				  <td>&nbsp;</td>
     				</tr>
     				<tr>
     			    <td>&nbsp;</td>  
    			    <td colspan="1">Contents</td>     					
    				  <td>&nbsp;</td>
     			    <td class="dc3a" colspan="8" >ALTAR BREADS</td>  
     					</tr>
     			 <tr>
      			<td>&nbsp;</td>  
     				<td class="dc6" colspan="17"; style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;</td>
    			  </tr>
    				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1">From</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3a" colspan="17" nowrap><?php echo $mfWB[0]["Principal"]; ?></td> 
             </tr>
     				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1">&nbsp;</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3" colspan="17"><?php echo $mfWB[0]["postal_add1"]; ?></td> 
     				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1">&nbsp;</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3" colspan="17"><?php echo $mfWB[0]["postal_add2"]; ?></td> 
     				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1">&nbsp;</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3" colspan="17"><?php echo $mfWB[0]["postal_add3"]; ?></td> 
     			<tr>
     			    <td>&nbsp;</td>  
      				<td class="dc7" colspan="17"; style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;</td>
           </tr>
     			<tr>
     			    <td>&nbsp;</td>  
      				<td colspan="8"; style="text-align:left;">Recieved By</td>
      				<td colspan="1"; style=border-left-style:solid;  border-left-color:black;  border-left-width:1px; </td>
      				<td colspan="7"; style="text-align:left;">Delivery Number</td>
           </tr>
     			<tr>
     			    <td>&nbsp;</td>  
      				<td colspan="8"; style="text-align:left;">&nbsp;</td>
      				<td colspan="1"; style=border-left-style:solid;  border-left-color:black;  border-left-width:1px; </td>
      				<td colspan="7"; style="text-align:left;">&nbsp;</td>
           </tr>
     			<tr>
     			    <td>&nbsp;</td>  
      				<td colspan="1"; style="text-align:left;">&nbsp;</td>
      				<td colspan="6"; style=border-bottom-style:dotted;  border-bottom-color:black;  border-left-width:1px; </td>
     				  <td colspan="1"; style="text-align:left;">&nbsp;</td>
      				<td colspan="1"; style=border-left-style:solid;  border-left-color:black;  border-left-width:1px; </td>
      				<td class="dc3" colspan="7"; style="text-align:left;"><?php echo $mfWB[0]["delivery_instructions"]; ?></td>
           </tr>
     			<tr>
     			    <td>&nbsp;</td>  
      				<td class="dc7" colspan="17"; style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;</td>
           </tr>
     			<tr>
     			    <td>&nbsp;</td>
     			    <?php 
                 $filename = "images/logos/sissors2.png";
                 $file     = HOST_SURESERVER_AS_USER.$PHPFOLDER.$filename;
                 if (file_exists($ROOT.$PHPFOLDER.$filename) == 1){
                    $logo = $ROOT.$PHPFOLDER.$filename;
             ?>
                  <td rowspan="1"; colspan="17"><?php echo "<img src=".$logo." style=width:400px; height:100px;>" ?></td>
             <?php } ?>    	
     			 </tr>
           <tr>
     			    <td>&nbsp;</td>
     			    <td colspan="2">No&nbsp;&nbsp;<?php echo str_pad($mfWB[0]["waybill_number"],6,"0" ,STR_PAD_LEFT); ?></td>
     			    <td>&nbsp;</td>
     			    <td class="dc3a" colspan="12">ACKNOWLEDGEMENT OF POSTING</td>           	
           	</tr>          
            <tr>
     			    <td>&nbsp;</td>
      			</tr>
    				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1" nowrap>Addressed to</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3a" colspan="10"><?php echo $mfWB[0]["bill_name"]; ?></td> 
              <td class="dc3" colspan="5"; style="text-align:right;">Tracking No</td>
             </tr>
     				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1">&nbsp;</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3" colspan="10"><?php echo $mfWB[0]["bill_add1"]; ?></td> 
              <td class="dc3" colspan="5"; style="text-align:right;"><?php echo $mfWB[0]["delivery_instructions"]; ?></td>
     				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1">&nbsp;</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3" colspan="17"><?php echo $mfWB[0]["bill_add2"]; ?></td> 
     				<tr>
     			    <td>&nbsp;</td>  
              <td colspan="1">&nbsp;</td>                    
    				  <td>&nbsp;</td>
              <td class="dc3" colspan="17"><?php echo $mfWB[0]["bill_add3"]; ?></td> 
     				<tr>
     			    <td>&nbsp;</td>  
    			    <td colspan="1">Contents</td>     					
    				  <td>&nbsp;</td>
     			    <td class="dc3" colspan="8" >ALTAR BREADS</td>  
     					</tr>
     			 <tr>
     			 <tr>
     			    <td>&nbsp;</td>  
      				<td class="dc7" colspan="17"; style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;</td>
           </tr>
      	</table>
    </body>
 </html>

<?php
$dbConn->dbClose();
?>


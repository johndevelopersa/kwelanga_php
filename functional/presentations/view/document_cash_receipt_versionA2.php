<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
 include_once($ROOT.$PHPFOLDER."DAO/PaymentsDAO.php");

// $userId = ((isset($_GET["USERID"]))?$_GET["USERID"]:"");
// $userCategory = ((isset($_GET["USERCATEGORY"]))?$_GET["USERCATEGORY"]:"");
// $principalId = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:"");
// $docmastId = ((isset($_GET["DOCMASTID"]))?$_GET["DOCMASTID"]:"");
// $outputTyp = ((isset($_GET["OUTPUTTYP"]))?$_GET["OUTPUTTYP"]:"");

$dbConn->dbConnection();

if (!isset($_SESSION)) session_start();
                $userId        = $_SESSION['user_id'];
                $userCategory  = $_SESSION['user_category'];
                $principalId   = $_SESSION['principal_id'];
                $principalName = $_SESSION['principal_name'];
                $principalType = $_SESSION['principal_type'];
                $depotId       = $_SESSION['depot_id'];

$adminDAO = new AdministrationDAO($dbConn);
$hasRoleVP   = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId),ROLE_VIEW_PRICE);

 $PaymentsDAO = new PaymentsDAO($dbConn);
 $mfT = $PaymentsDAO->getPaymentDetailsMulti($prO);
 
// if ($userId == 11) {
// print_r($mfT);	
// }
?>
<!DOCTYPE html>
<html>
   <title>&nbsp;</title>
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
           font-size:14px;}

           td.dc3 {background-color:white; 
           color:black; 
           font-weight:normal; 
           font-size:14px;}

           td.dc3a {background-color:white; 
           color:black; 
           font-weight:bold; 
           font-size:14px;} 

           td.dc4 {background-color:white; 
           font-weight:normal; 
           font-size:11px;
           border-collapse:collapse;
           border-left-style:solid;  border-left-color:black;  border-left-width:1px;
           border-right-style:solid; border-right-color:black; border-right-width:1px;  }

           td.dc5 {background-color:white; 
           font-weight:normal; 
           font-size:13px;
           border-collapse:collapse;
           border-left-style:solid;  border-left-color:black;  border-left-width:1px;
           border-right-style:solid; border-right-color:black; border-right-width:1px;  }
           
           td.dc6 {border-collapse: collapse;
           border-top-style:solid; border-top-color:black; border-top-width:1px;}
 
           td.dc7 {border-collapse: collapse;
           border-bottom-style:solid;   border-bottom-color:black;   border-bottom-width:1px; } 

           td.dc8 {border-collapse: collapse;
           font-weight:normal; 
           font-size:14px;
           border-style:solid solid solid solid; border-color:Gainsboro; border-width:1px; } 
           
           th.th1 {text-align:left; 
           font-size:14px;
           font-weight:bold; 
           background-color:white; 
           border-collapse:collapse; 
           border-left-style:solid; border-left-width:1px; border-left-color:black;
           border-right-style:solid; border-right-width:1px; border-right-color:black;
           border-top-style:solid; border-top-width:1px; border-top-color:black;
           border-bottom-style:solid; border-bottom-width:1px; border-bottom-color:black;}
           
           td.wb2 {border-collapse: collapse; 
           	       border-style:solid solid solid solid; 
           	       border-color:#f57900; 
           	       border-width:0.2px} 
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
                     <a href="javascript:;" onclick="emailDoc1();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/email-icon.png" border="0" alt="" align="left" > Email Self</a>
                     <a href="javascript:;" onclick="emailDoc2();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/email-icon.png" border="0" alt="" align="left" > Email Customer</a>
                 <div style="clear:both;"></div>
               </div>
             </div><!-- HIDE THIS PRINT AREA : END /--->
            </td>
          </tr>
         </table>
       </div>
     <table style="border-collapse:collapse; width:100%;">
     	 <tr>
     	     <td width="10%;" >&nbsp;</td>   
     	     <td width="11%;">&nbsp;</td>        	
     	     <td width="7%;" >&nbsp;</td>        	
     	     <td width="9%;" >&nbsp;</td>     	
     	     <td width="9%;" >&nbsp;</td>     	
     	     <td width="9%;" >&nbsp;</td>        	
     	     <td width="9%;" >&nbsp;</td>    
     	     <td width="8%;">&nbsp;</td>        	
     	     <td width="8%;">&nbsp;</td>     	
     	     <td width="9%;">&nbsp;</td>   
     	     <td width="12%;"&nbsp;</td>     	
      </tr>
      <tr>
      	<td>&nbsp;</td>  
      	</tr>
     	<tr>
         <td>&nbsp;</td>         	
         <td colspan="6"; style="text-align:left;">&nbsp;</td>
<?php 
         $filename = "images/logos/{$mfT[0]["principal_uid"]}.gif";
         $file     = HOST_SURESERVER_AS_USER.$PHPFOLDER.$filename;
         if (file_exists($ROOT.$PHPFOLDER.$filename) == 1){
                    $logo = $ROOT.$PHPFOLDER.$filename;
             ?>
                  <td rowspan="3" colspan="1"><?php echo "<img src=".$logo." style=width:50px; height:30px; float:left; >" ?></td>
             <?php } ?>
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td class="dc2" colspan="6"; style="text-align:left;"><?php echo $mfT[0]["principal_name"]; ?></td>
           </tr> 
         <tr>
             <td>&nbsp;</td>
             <td class="dc3" colspan="5"; style="text-align:left;" ><?php echo $mfT[0]["prin_ph_add1"]; ?></td>
             <td class="dc3" colspan="4"; style="text-align:right;">&nbsp;</td> 
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td class="dc3" colspan="5"; style="text-align:left;" ><?php echo $mfT[0]["prin_ph_add2"]; ?></td>
             <td class="dc3" colspan="4"; style="text-align:right;">&nbsp;</td> 
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td class="dc3" colspan="5"; style="text-align:left;" ><?php echo $mfT[0]["prin_ph_add3"]; ?></td>
             <td class="dc3" colspan="4"; style="text-align:right;">&nbsp;</td> 
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td class="dc3" colspan="5"; style="text-align:left ;" nowrap ><span style="font-weight:bold;">Email Address </span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo $mfT[0]["p_email"]; ?></span></td>
             <td class="dc3" colspan="4"; style="text-align:right;" nowrap ><span style="font-weight:bold;">&nbsp;</td>
          </tr> 
         <tr>
             <td>&nbsp;</td>
             <td class="dc3" colspan="3"; style="text-align:left;" nowrap ><span style="font-weight:bold;">Office Tel </span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo substr($mfT[0]['office_tel'],0,3).' '. substr($mfT[0]['office_tel'],3,3) .' '. substr($mfT[0]['office_tel'],6,4)?></span></td>
             <td class="dc3" colspan="1"; style="text-align:right ;" nowrap ><span style="font-weight:bold;">Date</td>
             <td class="dc3" colspan="1"; style="text-align:right ;" nowrap ><span style="font-weight:bold;"><?php echo date("Y-m-d"); ?></td>
             <td class="dc3" colspan="1"; style="text-align:right;" nowrap ><span style="font-weight:bold;">&nbsp;</td>


          </tr> 
     		  <tr>
     	     <td width="10%;" >&nbsp;</td>   
     	     <td width="11%;">&nbsp;</td>        	
     	     <td width="7%;" >&nbsp;</td>        	
     	     <td width="9%;" >&nbsp;</td>     	
     	     <td width="9%;" >&nbsp;</td>     	
     	     <td width="9%;" >&nbsp;</td>        	
     	     <td width="9%;" >&nbsp;</td>    
     	     <td width="8%;">&nbsp;</td>        	
     	     <td width="8%;">&nbsp;</td>     	
     	     <td width="9%;">&nbsp;</td>   
     	     <td width="12%;"&nbsp;</td>     	
         </tr>
         <tr>
         	  <td>&nbsp;</td> 
            <td class="dc"  colspan="4"; width="34%;" style="text-align:left;">Cash Payment Receipt</td>
            <td class="dc3" colspan="1"; style="text-align:right;" nowrap ><span style="font-weight:bold;">No.&nbsp;&nbsp;<?php echo $mfT[0]['payment_number']; ?></span></td>
       	 </tr>
     	   <tr>
            <td>&nbsp;</td>         	
            <td colspan="6"; style="text-align:left;">&nbsp;</td>
         </tr>   
         <tr>
         	  <td>&nbsp;</td> 
            <td class="dc3" colspan="6"; width="34%;" style="text-align:left;">Received from </td>
       	 </tr> 
     	   <tr>
            <td>&nbsp;</td>         	
            <td colspan="6"; style="text-align:left;">&nbsp;</td>
         </tr>    
        <tr>
             <td>&nbsp;</td>
             <td class="dc3" colspan="5"; style="text-align:left;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['deliver_name']; ?></span></td>
              <td class="dc3" colspan="4"; width="50%;" style="text-align:right;" nowrap ><span style="font-weight:normal;">&nbsp;</span></td>
         </tr> 
         <tr>
             <td>&nbsp;</td>
             <td class="dc3" colspan="5"; style="text-align:left;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['deliver_add1']; ?></span></td>
              <td class="dc3" colspan="4"; width="50%;" style="text-align:right;" nowrap ><span style="font-weight:normal;">&nbsp;</span></td>
          </tr>
         <tr>
             <td>&nbsp;</td>
             <td class="dc3" colspan="5"; style="text-align:left;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['deliver_add2']; ?></span></td>
             <td class="dc3" colspan="4"; style="text-align:right;" nowrap ><span style="font-weight:normal;">&nbsp;</span></td>
          </tr>
         <tr>
             <td>&nbsp;</td>
             <td class="dc3" colspan="5"; style="text-align:left;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['deliver_add3']; ?></span></td>
             <td class="dc3" colspan="4"; style="text-align:right;" nowrap ><span style="font-weight:normal;">&nbsp;</span></td>
          </tr>         
     	   <tr>
            <td>&nbsp;</td>         	
            <td colspan="6"; style="text-align:left;">&nbsp;</td>
         </tr>
         <tr>
         	  <td>&nbsp;</td> 
            <td class="dc3" colspan="2"; width="34%;" style="text-align:left;">Amount Received  (R)</td>
            <td class="dc2" colspan="2"; width="34%;" style="text-align:left;"><?php echo number_format($mfT[0]['amount'],2,'.',' '); ?></td>
         </tr>
         <?php
         $c = 0;
         $matchedTot = $paymentTotal = $invTotal = 0;
            foreach($mfT as $drow) {
            	if($c==0) {
            		?>
            		   <tr>
                      <td>&nbsp;</td>         	
                      <td colspan="6"; style="text-align:left;">&nbsp;</td>
                   </tr> 
                   <tr>
                      <th>&nbsp;</th>         	
                      <th class="th1" width="18%";>Invoice Number</th>
                      <th class="th1" width="18%";>Invoice Date</th>
                      <th class="th1" width="18%";>Invoice Amount</th>
                      <th class="th1" width="18%";>Payment Amont</th>
                      <th class="th1" width="18%";>Difference</th>
                   </tr>                
              
           <?php          
            	   $c++;	
            	}
            	
            	$paymentTotal = $paymentTotal + $drow['payment_amount'];
            	$invTotal     = $invTotal     + $drow['invoice_total'] ;
            	$matchedTot   = $matchedTot   + $drow['matched_amount'] ;
                      	
           ?>	
              <tr>
         	      <td>&nbsp;</td> 
                <td class="dc5" width="18%;" style="text-align:right;"><?php echo substr($drow['document_number'],2,6); ?></td>
                <td class="dc5" width="18%;" style="text-align:right;"><?php echo $drow['invoice_date']; ?></td>
                <td class="dc5" width="18%;" style="text-align:right;"><?php echo number_format($drow['invoice_total'],2,'.',' '); ?>&nbsp;</td>
                <td class="dc5" width="18%;" style="text-align:right;"><?php echo number_format($drow['payment_amount'],2,'.',' '); ?>&nbsp;</td>
                <td class="dc5" width="18%;" style="text-align:right;"><?php echo number_format($drow['matched_amount'],2,'.',' '); ?>&nbsp;</td>
       	      </tr>                     	
            
           <?php             
            
            
            }	
         
         
         ?>
              <tr>
         	      <td>&nbsp;</td> 
                <td class="dc6" colspan="2" style="text-align:right;">&nbsp;</td>
                <td class="dc8" width="18%;" style="text-align:right;">&nbsp;</td>
                <td class="dc8" width="18%;" style="text-align:right;">&nbsp;</td>
                <td class="dc8" width="18%;" style="text-align:right;">&nbsp;</td>
       	      </tr>           
         
         
         
              <tr>
         	      <td>&nbsp;</td> 
                <td colspan="2" style="text-align:right; font-weight:bold; ">Total&nbsp;&nbsp;&nbsp;</td>
                <td class="dc8" width="18%;" style="text-align:right; font-weight:bold;"><?php echo number_format($invTotal,2,'.',' '); ?>&nbsp;</td>
                <td class="dc8" width="18%;" style="text-align:right; font-weight:bold;"><?php echo number_format($paymentTotal,2,'.',' '); ?>&nbsp;</td>
                <td class="dc8" width="18%;" style="text-align:right; font-weight:bold;"><?php echo number_format($matchedTot,2,'.',' '); ?>&nbsp;</td>
       	      </tr>                           

     	   <tr>
            <td>&nbsp;</td>         	
            <td colspan="6"; style="text-align:left;">&nbsp;</td>
         </tr>   
     	   <tr>
            <td>&nbsp;</td>         	
            <td colspan="6"; style="text-align:left;">&nbsp;</td>
         </tr>   
         	  <td>&nbsp;</td> 
            <td class="dc3" colspan="4"; width="34%;" style="text-align:left;">Thank You for the Payment </td>
            <td class="dc2" colspan="2"; width="34%;" style="text-align:left;">&nbsp;</td>
         <tr>
	          <td>&nbsp;</td>         	
            <td colspan="6"; style="text-align:left;">&nbsp;</td>
         </tr>
       </table>
      <table style="border-collapse:collapse; width:90%; ">
     	   <tr>
     	   	    <td width="10%;" >&nbsp;</td> 
              <td class="dc8" width="55%;" style="text-align:center; color:Gainsboro;">Stamp</td> 
             <td ><img src=<?php echo HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER; ?>images/KWELANGA1.gif style="width:50px; height:25px; float:right;" ></td>         	
         </tr>   
         <tr>
         	  <td colspan=3 style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo date('Y-m-d H:i:s'); ?></td>
         </tr>
         <tr>
         	  <td colspan=3 style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo 'document_cash_receipt_versionA2'; ?></td>
         </tr>
         
      </table>
    </body>
 </html>

<?php
$dbConn->dbClose();
?>

<script type="text/javascript">
  function emailDoc1() {  
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo '';?>&USERID=<?php echo '';?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfSelf50';?>&PSMUID=<?php echo ('');?>&PRINNAM=<?php echo trim('');?>&CUSTNAME=<?php echo '' ;?>&TEMPLAT=<?php echo 'document_cash_receipt_versionA2.php';?>";
  }
  function emailDoc2() {
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo '';?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfCust50';?>&PSMUID=<?php echo ('');?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&CUSTNAME=<?php echo $custName ;?>&TEMPLAT=<?php echo 'detailed_ledger_version1.php';?>";
  }  
</script> 

<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."elements/SignatureArea.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

$userId = ((isset($_GET["USERID"]))?$_GET["USERID"]:"");
$principalId = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:"");
$docmastId = ((isset($_GET["DOCMASTID"]))?$_GET["DOCMASTID"]:"");
$outputTyp = ((isset($_GET["OUTPUTTYP"]))?$_GET["OUTPUTTYP"]:"");

$dbConn->dbConnection();

$adminDAO = new AdministrationDAO($dbConn);
$hasRoleSign = $adminDAO->hasRole(mysql_real_escape_string($userId), mysql_real_escape_string($principalId), ROLE_SIGNITURE);
$hasRoleVP   = $adminDAO->hasRole(mysql_real_escape_string($userId), mysql_real_escape_string($principalId),ROLE_VIEW_PRICE);

$transactionDAO = new TransactionDAO($dbConn);
$mfT = $transactionDAO->getDocumentWithDetailItem(mysql_real_escape_string($userId),mysql_real_escape_string($principalId),mysql_real_escape_string($docmastId), $orderBy="principal_product.product_code");

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
          <?php if (substr($outputTyp,0,3) == "pdf") { ?>
                   td.dc {background-color:white; 
                   color:black; 
                   font-weight:bold; 
                   font-size:60px;}

                   td.dc2 {background-color:white; 
                   color:black; 
                   font-weight:bold; 
                   font-size:43px;}

                   td.dc3 {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:40px;}

                   td.dc4 {background-color:white;
                   font-weight:normal; 
                   font-size:30px;
                   border-collapse: collapse;
                   border-left-style:solid;  border-left-color:black;  border-left-width:0.5px;
                   border-right-style:solid; border-right-color:black; border-right-width:0.5px;  }
 
                   td.dc5 {background-color:white;
                   font-weight:normal; 
                   font-size:30px;
                   border-collapse: collapse;
                   border-top-style:solid; border-top-color:black; border-top-width:0.5px;
                   border-left-style:solid;  border-left-color:black;  border-left-width:0.5px;
                   border-right-style:solid; border-right-color:black; border-right-width:0.5px;}  
                   
                   td.dc6 {background-color:white;
                   font-weight:normal; 
                   font-size:30px;
                   border-collapse:collapse;
                   border-top-style:solid; border-top-color:black; border-top-width:0.5px;
                   border-left-style:solid; border-left-color:black; border-left-width:0.5px;}  
                                      
                   td.dc7 {background-color:white;
                   font-weight:normal; 
                   font-size:30px;
                   border-collapse: collapse;
                   border-bottom-style:solid; border-bottom-color:black; border-bottom-width:0.5px; } 

                   td.dc8 {background-color:white;
                   font-weight:normal; 
                   font-size:30px;
                   border-collapse: collapse;
                   border-style:solid solid solid solid; border-color:black; border-width:0.5px; } 
                   
                   th.th1 {text-align:left; 
                   font-size:40px;
                   font-weight:bold;
                   background-color:white; 
                   border-collapse:collapse; 
                   border-left-style:solid; border-left-width:1px; border-left-color:black;
                   border-right-style:solid; border-right-width:1px; border-right-color:black;
                   border-top-style:solid; border-top-width:1px; border-top-color:black;
                   border-bottom-style:solid; border-bottom-width:1px; border-bottom-color:black;}
                   
          <?php } else { ?>         
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
 
                   td.dc4 {background-color:white; 
                   font-weight:normal; 
                   font-size:12px;
                   border-collapse:collapse;
                   border-left-style:solid;  border-left-color:black;  border-left-width:1px;
                   border-right-style:solid; border-right-color:black; border-right-width:1px;  }

                   td.dc5 {background-color:white; 
                   font-weight:normal; 
                   font-size:12px;
                   border-collapse:collapse;
                   border-left-style:solid;  border-left-color:black;  border-left-width:1px;
                   border-right-style:solid; border-right-color:black; border-right-width:1px;  }
                   
                   td.dc6 {border-collapse: collapse;
                   border-top-style:solid; border-top-color:black; border-top-width:1px;}
 
                   td.dc7 {border-collapse: collapse;
                   border-bottom-style:solid;   border-bottom-color:black;   border-bottom-width:1px; } 

                   td.dc8 {border-collapse: collapse;
                   border-style:solid solid solid solid; border-color:black; border-width:1px; } 
                   
                   th.th1 {text-align:left; 
                   font-size:15px;
                   font-weight:bold; 
                   background-color:white; 
                   border-collapse:collapse; 
                   border-left-style:solid; border-left-width:1px; border-left-color:black;
                   border-right-style:solid; border-right-width:1px; border-right-color:black;
                   border-top-style:solid; border-top-width:1px; border-top-color:black;
                   border-bottom-style:solid; border-bottom-width:1px; border-bottom-color:black;}
           <?php } ?> 

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
     <table style="border-collapse:collapse; width:98%">
         <tr>
             <?php if (in_array($mfT[0]['status_uid'],array(DST_INVOICED, DST_DELIVERED_POD_OK,DST_DIRTY_POD))) { ?>
                <td class="dc" style="text-align:left;">Tax Invoice</td>
                <td width="50%;" >&nbsp;</td>
             <?php $document_type = '38'; }
             elseif (in_array($mfT[0]['status_uid'],array(DST_CANCELLED))) { ?>
                <td class="dc" style="text-align:left;">Cancelled Document</td>
                <td width="50%;" >&nbsp;</td>
             <?php $document_type = '12'; }
             elseif (in_array($mfT[0]['status_uid'],array(DST_UNACCEPTED,DST_ACCEPTED,14))) { ?>
             	  <td class="dc" style="text-align:left;">Sales Order</td>
                <td width="50%;" >&nbsp;</td>
             <?php $document_type = '40';
             } else { ?>
                <td class="dc" style="text-align:left;">Proforma Invoice</td>
                <td width="50%;" >&nbsp;</td>
             <?php $document_type = '39'; } ?>
         </tr>
         <tr>
           <td width="50%;" >&nbsp;</td>
           <td width="50%;" >&nbsp;</td>
         </tr>         
         <tr>
           <td class="dc2" colspan="2" style="text-align:left;"><?php echo $mfT[0]["principal_name"]; ?></td>
         </tr>
         <tr>
           <td class="dc3" width="50%;" style="text-align:left;"><?php echo $mfT[0]["prin_add1"]; ?></td>
           <td class="dc3" width="50%;" style="text-align:right;" nowrap ><span style="font-weight:bold;">Email Address </span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo $mfT[0]["p_email"]; ?></span></td>
         </tr>
         <tr>
           <td class="dc3" width="50%;" style="text-align:left;"><?php echo $mfT[0]['prin_add2'] ?></td>
           <td class="dc3" width="50%;" style="text-align:right;" nowrap ><span style="font-weight:bold;">Office Tel </span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo substr($mfT[0]['office_tel'],0,3).' '. substr($mfT[0]['office_tel'],3,3) .' '. substr($mfT[0]['office_tel'],6,4)?></span></td>
         </tr>
        <tr>
           <td class="dc3" width="50%;" style="text-align:left;" nowrap ><span style="font-weight:bold;">VAT No </span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo $mfT[0]['prin_vat'] ?></span></td>
           <?php 
             if (trim($mfT[0]['office_tel2']) <> NULL) { ?>
               <td class="dc3" width="50%;" style="text-align:right;" nowrap ><span style="font-weight:bold;">/</span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo substr($mfT[0]['office_tel2'],0,3).' '. substr($mfT[0]['office_tel2'],3,3) .' '. substr($mfT[0]['office_tel2'],6,4)?></span></td>
           <?php } ?>
        </tr>   
        <tr>
           <?php if (trim($mfT[0]['company_reg']) <> NULL) { ?>
              <td class="dc3" width="50%;" style="text-align:left ;" nowrap ><span style="font-weight:bold ;">Company Reg No </span>&nbsp;&nbsp;<span style="font-weight:normal;" nowrap><?php echo $mfT[0]["company_reg"] ?></span></td>
           <?php } else { ?>
              <td width="50%;" >&nbsp;</td>
              <td width="50%;" >&nbsp;</td>
           <?php } ?>
        </tr>
         <tr>
           <td class="dc3" width="50%;" style="text-align:left ;" nowrap><span style="font-weight:bold ;">Date </span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo $mfT[0]["order_date"]; ?></span></td>
           <?php if (in_array($mfT[0]['status_uid'],array(DST_INVOICED, DST_DELIVERED_POD_OK,DST_DIRTY_POD))) { 
                   if (trim($mfT[0]['invoice_number']) == '' ){ ?>
                     <td class="dc3" width="50%;" style="text-align:right;" nowrap><span style="font-weight:bold;">Invoice Number</span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo substr($mfT[0]['document_number'],2,6); ?></span></td>
           <?php   } else { ?>
                     <td class="dc3" width="50%;" style="text-align:right;" nowrap><span style="font-weight:bold;">Invoice Number</span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo trim($mfT[0]['invoice_number']); ?></span></td>
           <?php   } 
                 } elseif (in_array($mfT[0]['status_uid'],array(DST_UNACCEPTED,DST_ACCEPTED,14))) { ?>
                     <td class="dc3" width="50%;" style="text-align:right;" nowrap><span style="font-weight:bold;">Document Number</span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo substr($mfT[0]['document_number'],2,6); ?></span></td>
           <?php   }
                 elseif (in_array($mfT[0]['status_uid'],array(DST_IN_PROGRESS))) { ?>
                     <td class="dc3" width="50%;" style="text-align:right;" nowrap><span style="font-weight:bold;">Quote Number</span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo substr($mfT[0]['document_number'],3,6); ?></span></td>
           <?php } ?>
         </tr>
         <tr>
           <td colspan=2>&nbsp;</td>
         </tr> 
          <tr>
           <td class="dc3" width="50%;" style="text-align:left;" nowrap ><span style="font-weight:bold;">Customer</span>&nbsp;&nbsp;</td>
           <td class="dc3" width="50%;" style="text-align:right;" nowrap ><span style="font-weight:bold;">Delivered To</span></td>

         </tr>
         <tr>
           <td class="dc3" width="50%;" style="text-align:left;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['bill_name']; ?></span></td>
           <td class="dc3" width="50%;" style="text-align:right;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['store_name']; ?></span></td>
         </tr>
          <tr>
           <td class="dc3" width="50%;" style="text-align:left;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['bill_add1']; ?></span></td>
           <td class="dc3" width="50%;" style="text-align:right;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['deliver_add1']; ?></span></td>
         </tr>        
         <tr>
           <td class="dc3" width="50%;" style="text-align:left;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['bill_add2']; ?></span></td>
           <td class="dc3" width="50%;" style="text-align:right;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['deliver_add2']; ?></span></td>
         </tr>         
         
         <tr>
           <td class="dc3" width="50%;" style="text-align:left;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['bill_add3']; ?></span></td>
           <td class="dc3" width="50%;" style="text-align:right;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['deliver_add3']; ?></span></td>
         </tr>         
         <tr>
           <td class="dc3" width="50%;" style="text-align:left;" nowrap ><span style="font-weight:bold ;">Customer VAT Number</span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo $mfT[0]['vat_number']; ?></span></td>
           <td class="dc3" width="50%;" style="text-align:right;" nowrap ><span style="font-weight:normal;">&nbsp;</span></td>
         </tr>
         <tr>
           <td width="50%;" >&nbsp;</td>
           <td width="50%;" >&nbsp;</td>
         </tr>         
         <tr>
           <td class="dc3" width="50%;" style="text-align:left ;" nowrap><span style="font-weight:bold ;">Customer Reference </span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo $mfT[0]["customer_order_number"] ?></span></td>
         </tr>
      <!-- detail -->
         <tr>
           <td class="dc3" style="text-align:left ;" nowrap><span style="font-weight:bold ;">Invoice Details</span></td>
         </tr>       	
         <tr>
           <td width="50%;" >&nbsp;</td>
           <td width="50%;" >&nbsp;</td>
         </tr>
       </table>     
       <table style= "border-collapse:collapse; width:100%; ">
         <tr>
           <th class="th1" style="width:15% ;" nowrap >Code</th>
           <th class="th1" style="width:28% ;" nowrap >Description</th>
           <th class="th1" style="width: 9% ;" nowrap >Quantity</th>
           <th class="th1" style="width: 9% ;" nowrap >Price</th>
           <th class="th1" style="width:15% ;" nowrap >Exclusive Total</th>
           <th class="th1" style="width: 9% ;" nowrap >Vat</th>
           <th class="th1" style="width:15% ;" nowrap >Inclusive Total</th>
         </tr>
         <?php $totQ=0; $totLP=0; $totDV=0; $totCP=0; $totNett=0; $totVAT=0; $totTot = 0; 
         $cls = "dc5";
       foreach($mfT as $row) {
         $nettCP=0;
         ?>
         <tr>
           <td class="<?php echo $cls; ?>" nowrap><?php echo $row["product_code"]?>&nbsp;</td>
           <td class="<?php echo $cls; ?>" style= text-align:left; nowrap><?php echo $row["product_description"]?>&nbsp;</td>
           <?php if (in_array($mfT[0]['status_uid'],array(DST_UNACCEPTED,DST_ACCEPTED,14))) {  
               $totQ+= $row["ordered_qty"] ?>
               <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo $row["ordered_qty"]?>&nbsp;</td>
           <?php } else { 
               $totQ+= $row["document_qty"]?>
               <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo $row["document_qty"]?>&nbsp;</td>
           <?php } 
                if (!$hasRoleVP) { $cls = "dc4"; ?>
               <td nowrap colspan=\"8\">not authorised to view pricing</td>
           <?php } else { ?> 	
               <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo number_format($row["net_price"],2, "."," ")?>&nbsp;</td>
           <?php $totCP+= $row["net_price"];
               if (in_array($mfT[0]['status_uid'],array(DST_UNACCEPTED,DST_ACCEPTED,14))) {            
                 $nettcp=$row["ordered_qty"]*$row["net_price"]; 
               } else {
                  $nettcp=$row["document_qty"]*$row["net_price"]; 
               } ?>
               <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo number_format($nettcp,2,"."," ")?>&nbsp;</td>
               <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo number_format($row["vat_amount"],2, "."," ")?>&nbsp;</td>
               <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo number_format($row["total"],2, "."," ")?>&nbsp;</td>
           <?php $totVAT+= $row["vat_amount"];
                 $totTot+= $row["total"] ;
                 $totNett+=$nettcp;
                 $cls = "dc4";
           } ?>
          </tr>
          <tr>
             <td class="<?php echo $cls; ?>" >&nbsp;</td>
             <td class="<?php echo $cls; ?>" >&nbsp;</td>
             <td class="<?php echo $cls; ?>" >&nbsp;</td>
             <td class="<?php echo $cls; ?>" >&nbsp;</td>
             <td class="<?php echo $cls; ?>" >&nbsp;</td>
             <td class="<?php echo $cls; ?>" >&nbsp;</td>
             <td class="<?php echo $cls; ?>" >&nbsp;</td>
          </tr> 
         <?php } ?>
<!-- total line -->
         <tr>
           <td class="dc6" colspan="2" >&nbsp;&nbsp;&nbsp;&nbsp;</td>
           <td class="dc8" style="text-align:right"; nowrap ><?php echo $totQ; ?>&nbsp;</td>
           <td class="dc6" >&nbsp;&nbsp;</td>           
           <td class="dc8" style="text-align:right"; nowrap ><?php echo number_format($totNett,2,'.',' '); ?>&nbsp;</td>
           <td class="dc8" style="text-align:right"; nowrap ><?php echo number_format($totVAT,2,'.',' '); ?>&nbsp;</td>
           <td class="dc8" style="text-align:right"; nowrap ><?php echo number_format($totTot,2,'.',' '); ?>&nbsp;</td>
         </tr>         
       </table>  
       <!-- footer -->
       <table style="width:100%;">
         <tr>
           <td class="dc3" >&nbsp;</td>
           <td class="dc3" >&nbsp;</td>
           <td class="dc3" >&nbsp;</td>
        </tr>	
        <tr>
           <?php  if (trim($mfT[0]['tcs']) <> NULL) { ?>
              <td class="dc2" style="text-align:left;" nowrap >Terms and Conditions </td>
           <?php } else { ?>
              <td class="dc3" >&nbsp;</td>
              <td class="dc3" >&nbsp;</td>
              <td class="dc3" >&nbsp;</td>
           <?php } ?>
        </tr>
        <tr>
           <?php  if (trim($mfT[0]['tcs']) <> NULL) { ?>
              <td class="dc3" style="width:80%"; "text-align:left"; nowrap ><?php echo (str_replace(chr(10),"<br>", $mfT[0]['tcs'])); ?></td>
           <?php } else { ?>
              <td class="dc3" >&nbsp;</td>
              <td class="dc3" >&nbsp;</td>
              <td class="dc3" >&nbsp;</td>
           <?php } ?>
         </tr>
         <tr>
           <td class="dc3" >&nbsp;</td>
         </tr>
         <tr>
           <td class="dc3" >&nbsp;</td>
         </tr>
         <tr>
         <?php  if (trim($mfT[0]['banking_details']) <> NULL) { ?>
           <td class="dc3" colspan="2" style="text-align:left"; nowrap >Banking Details&nbsp;&nbsp;<?php echo $mfT[0]["banking_details"]?></td>
          <?php } else { ?>
           <td class="dc3" >&nbsp;</td>
           <td class="dc3" >&nbsp;</td>
           <td class="dc3" >&nbsp;</td>
           <?php } ?>
         </tr>	
          <tr>
           <td class="dc3" >&nbsp;</td>
         </tr>
         <tr>
           <td class="dc3" >&nbsp;</td>
         </tr>
         <tr>
           <td colspan="2" style="text-align:center; color:grey; font-weight:normal; font-size:0.55em;">
             <img src="<?php echo HOST_SURESERVER_AS_USER.$PHPFOLDER; ?>images/rt_powerby.gif" style="width:75px; height:30px; float:right;" >
             <?php echo date('Y-m-d H:i:s'); ?>
           </td>
         	</tr>
       </table>      

    </body>
 </html>

<?php
$dbConn->dbClose();
?>

<script type="text/javascript">
  function emailDoc1() {
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfSelf'.trim($document_type). substr($mfT[0]['document_number'],2,6);?>&PSMUID=<?php echo ('');?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&TEMPLAT=<?php echo 'document_proforma_invoice_desktop.php';?>";
  }
  function emailDoc2() {
  	  window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfCust'.trim($document_type). substr($mfT[0]['document_number'],2,6);?>&PSMUID=<?php echo($mfT[0]['psm_uid']);?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&TEMPLAT=<?php echo 'document_proforma_invoice_desktop.php';?>";
}  
</script> 
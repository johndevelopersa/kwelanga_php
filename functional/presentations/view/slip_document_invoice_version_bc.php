<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

$userId = ((isset($_GET["USERID"]))?$_GET["USERID"]:"");
$userCategory = ((isset($_GET["USERCATEGORY"]))?$_GET["USERCATEGORY"]:"");
$principalId = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:"");
$docmastId = ((isset($_GET["DOCMASTID"]))?$_GET["DOCMASTID"]:"");
$outputTyp = ((isset($_GET["OUTPUTTYP"]))?$_GET["OUTPUTTYP"]:"");

$dbConn->dbConnection();

$adminDAO = new AdministrationDAO($dbConn);
$hasRoleSign = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId), ROLE_SIGNITURE);
$hasRoleVP   = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId),ROLE_VIEW_PRICE);

$transactionDAO = new TransactionDAO($dbConn);
$mfT = $transactionDAO->getDocumentWithDetailIgnorePermissionsItem(mysqli_real_escape_string($dbConn->connection, $docmastId));

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
                   font-size:16px;}

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
                   border-bottom-style:solid; border-bottom-width:1px; border-bottom-color:black;
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
                 <?php if ($userCategory == "P") { ?>
                     <a href="javascript:;" onclick="emailDoc1();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/email-icon.png" border="0" alt="" align="left" > Email Self</a>
                     <a href="javascript:;" onclick="emailDoc2();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/email-icon.png" border="0" alt="" align="left" > Email Customer</a>
                  <?php } ?>
                 <div style="clear:both;"></div>
               </div>
             </div><!-- HIDE THIS PRINT AREA : END /--->
            </td>
          </tr>
         </table>
       </div>
     <table style="border-collapse:collapse; width:90%">
            <?php 
            $filename = "images/logos/{$principalId}.jpg";
            $file     = HOST_SURESERVER_AS_USER.$PHPFOLDER.$filename;
            if (file_exists($ROOT.$PHPFOLDER.$filename) == 1) {
                   $logo = $ROOT.$PHPFOLDER.$filename; ?>
                  <tr>
                  	  <td colspan="1";>&nbsp;</td>
                  	  <td colspan="1";>&nbsp;</td>
                      <td colspan="2"; rowspan="3"; style="text-align:center;"" ><?php echo "<img src=".$logo." style=width:100px height:80px;>"?></td>	
                      <td colspan="2";>&nbsp;</td>
                  </tr>
     	            <tr>
                      <td width="4%;" style="border:none;"></td>
                      <td width="31%;" style="border:none;""></td>
                      <td width="30%;" style="border:none;" ></td>
                      <td width="21%;" style="border:none;" ></td>
                      <td width="10%;" style="border:none;" ></td>
                      <td width="4%;" style="border:none;"></td>
                  </tr>
                  <tr>
                      <td colspan="6";>&nbsp;</td>	
                  </tr>
            <?php 
            } ?>
            <tr>  
            <?php 
            if ($mfT[0]['document_type_uid'] == 6) { ?>
                  <td colspan="1";>&nbsp;</td>
                  <td class="dc3a"  colspan="4"; style="text-align:center;">DELIVERY NOTE</td>
                  <td colspan="1";>&nbsp;</td>
            <?php 
            } elseif ($mfT[0]['document_type_uid'] == 13) { ?>
                  <td colspan="1";>&nbsp;</td>
                  <td class="dc3a" colspan="4"; style="text-align:center;">ZERO PRICE INVOICE</td>
                  <td colspan="1";>&nbsp;</td>
            <?php 
            } else { ?>
                  <td colspan="1";>&nbsp;</td>
                  <td class="dc3a" colspan="4"; style="border:none; text-align:center;">TAX INVOICE</td>
                  <td colspan="1";>&nbsp;</td>
            <?php 
            } ?>
            </tr>
            <tr>
               <td colspan="6";>&nbsp;</td>	
           </tr>
           <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc2" colspan="3" style="text-align:left;" NOWRAP><?php echo $mfT[0]["principal_name"]; ?></td>
          	   <td colspan="2";>&nbsp;</td>          	
           </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc2" colspan="3" style="text-align:left;" NOWRAP><?php echo $mfT[0]["prin_add1"]; ?></td>
          	   <td colspan="2";>&nbsp;</td>          	
          </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc2" colspan="3" style="text-align:left;" NOWRAP><?php echo $mfT[0]["prin_add2"]; ?></td>
          	   <td colspan="2";>&nbsp;</td>          	
          </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc2" colspan="3" style="text-align:left;" NOWRAP><?php echo $mfT[0]["prin_add3"]; ?></td>
          	   <td colspan="2";>&nbsp;</td>          	
          </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc2" colspan="3" style="text-align:left;" NOWRAP><span style="font-weight:bold;">VAT No </span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo $mfT[0]['prin_vat'] ?></span></td>
          	   <td colspan="2";>&nbsp;</td>          	
          </tr>
           <tr>
               <td colspan="6";>&nbsp;</td>	
           </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc3" colspan="2" style="text-align:left;" nowrap><span style="font-weight:bold;">Doc Num</span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo ltrim($mfT[0]['document_number'],'0'); ?></span></td>
               <td class="dc2" colspan="2" style="text-align:right;" NOWRAP><span style="font-weight:bold;">Date </span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo $mfT[0]['invoice_date']; ?></span></td>
          	   <td colspan="1";>&nbsp;</td>          	
          </tr>
           <tr>
               <td colspan="6";>&nbsp;</td>	
           </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc3a" colspan="3" style="text-align:left;" NOWRAP>Invoice To</td>
          	   <td colspan="2";>&nbsp;</td>          	
          </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc2" colspan="3" style="text-align:left;" NOWRAP><?php echo $mfT[0]["store_name"]; ?></td>
          	   <td colspan="2";>&nbsp;</td>          	
          </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc2" colspan="3" style="text-align:left;" NOWRAP><?php echo $mfT[0]["deliver_add1"]; ?></td>
          	   <td colspan="2";>&nbsp;</td>          	
          </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc2" colspan="3" style="text-align:left;" NOWRAP><?php echo $mfT[0]["deliver_add2"]; ?></td>
          	   <td colspan="2";>&nbsp;</td>          	
          </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc2" colspan="3" style="text-align:left;" NOWRAP><span style="font-weight:bold;">Cust VAT No </span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo $mfT[0]['vat_number'] ?></span></td>
          	   <td colspan="2";>&nbsp;</td>          	
          </tr>
           <tr>
               <td colspan="6";>&nbsp;</td>	
           </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc3" colspan="2" style="text-align:left;" nowrap><span style="font-weight:bold;">Doc Num</span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo ltrim($mfT[0]['document_number'],'0'); ?></span></td>
               <td class="dc2" colspan="2" style="text-align:right;" NOWRAP><span style="font-weight:bold;">Date </span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo $mfT[0]['invoice_date']; ?></span></td>
          	   <td colspan="1";>&nbsp;</td>          	
           </tr>
           <tr>
               <td colspan="6";>&nbsp;</td>	
           </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
               <td class="dc3" colspan="4" style="text-align:left;" nowrap><span style="font-weight:bold;">Reference</span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo ltrim($mfT[0]['customer_order_number'],'0'); ?></span></td>
          	   <td colspan="1";>&nbsp;</td>          	
          </tr>
           <tr>
               <td colspan="6";>&nbsp;</td>	
           </tr>
          <tr>
          	   <td colspan="1";>&nbsp;</td>
          	   <td class="dc3a" style="text-align:left";  nowrap >Description</td>
          	   <td class="dc3a" style="text-align:right"; nowrap >Qty</td>
          	   <td class="dc3a" style="text-align:right"; nowrap >Price</td>
          	   <td class="dc3a" style="text-align:right"; nowrap >Incl. Total</td>
          	   <td colspan="1";>&nbsp;</td>          	
          </tr>
          <?php $totQ=0; $totVAT=0; $totTot = 0;
          $cls = "dc3";
          foreach($mfT as $row) { ?>
                <tr>
                    <td colspan="1";>&nbsp;</td> 
                	  <td class="<?php echo $cls; ?>" style= text-align:left;><?php echo $row["product_description"]?>&nbsp;</td>
                    <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo $row["document_qty"]?>&nbsp;</td>
                    <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo number_format($row["net_price"],2, "."," ")?>&nbsp;</td>
                    <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo number_format($row["total"],2, "."," ")?>&nbsp;</td>
                    <td colspan="1";>&nbsp;</td> 
                </tr>
                <tr>
                    <td colspan="1";>&nbsp;</td> 
                    <td class="<?php echo $cls; ?>" >&nbsp;</td>
                    <td class="<?php echo $cls; ?>" >&nbsp;</td>
                    <td class="<?php echo $cls; ?>" >&nbsp;</td>
                    <td class="<?php echo $cls; ?>" >&nbsp;</td>
                    <td colspan="1";>&nbsp;</td>
               </tr>                     
               <?php 
                 $totVAT+= $row["vat_amount"];
                 $totTot+= $row["total"] ;
//   ****************************************************************************************************************************          
          } ?>
<!-- total line -->
         
         <tr>
              <td colspan="2";>&nbsp;</td> 
              <td class="dc3a" colspan="2" style="text-align:right"; >Amnt Incl. Tax</td>
              <td class="dc3a" colspan="1" style="text-align:right"; ><?php echo number_format($totTot,2,'.',' ');?></td>
              <td colspan="1";>&nbsp;</td> 
         </tr>         
         <tr>
              <td colspan="2";>&nbsp;</td> 
              <td class="dc3a" colspan="2" style="text-align:right"; >Tax Amnt.</td>
              <td class="dc3a" colspan="1" style="text-align:right"; ><?php echo number_format($totVAT,2,'.',' ');?></td>
              <td colspan="1";>&nbsp;</td> 
         </tr>   
         <tr>
              <td colspan="2";>&nbsp;</td> 
              <td class="dc3a" colspan="2" style="text-align:right"; >Total </td>
              <td class="dc3a" colspan="1" style="text-align:right"; ><?php echo number_format($totTot,2,'.',' ');?></td>
              <td colspan="1";>&nbsp;</td> 
         </tr>
         <tr>
              <td class="dc3" >&nbsp;</td>
         <tr>
             <td colspan="1";>&nbsp;</td> 
             <?php
             if (trim($mfT[0]['banking_details']) <> NULL && !in_array($mfT[0]['document_type_uid'],array('4','2')) && ($mfT[0]['bank_details_to_print'] == 1) )  { ?>
                <td class="dc3" colspan="2" style="text-align:left";  nowrap >Banking Details&nbsp;&nbsp;<?php echo (str_replace(chr(43),"<br>", $mfT[0]["banking_details"]))?></td>
             <?php
             } else { ?>
               <td colspan="3";>&nbsp;</td> 
             <?php 
             } ?>
              <td colspan="3";>&nbsp;</td>
         </tr>	
         <tr>
              <td class="dc3" >&nbsp;</td>
         </tr>
         <tr>
               <td colspan="1";>&nbsp;</td> 
               <td class="dc3" colspan="4";><img alt="<?php echo $mfT[0]['principal_uid']  . ' - ' .  ltrim(substr($mfT[0]['document_number'],0,8),'0'); ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $mfT[0]['principal_uid'] . ' - ' . ltrim(substr($mfT[0]['document_number'],0,8),'0'); ?>&print=true" /></td>
               <td colspan="1";>&nbsp;</td>
         </tr>
         <tr>
              <td class="dc3" >&nbsp;</td>
         </tr>
         <tr>
              <td colspan="3";>&nbsp;</td>
              <td colspan="2"; style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
                  <img src="<?php echo $ROOT.$PHPFOLDER; ?>images/Kwelanga_Solutions_Logo_smaller.jpg" style="width:60px; height:40px; float:right;" ></td>
              <td colspan="1";>&nbsp;</td>
         </tr>
         <tr>
          	 <td colspan="3";>&nbsp;</td>
             <td colspan="2"; style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo date('Y-m-d H:i:s'); ?></td>
             <td colspan="1";>&nbsp;</td>
         </tr> 
         <tr>
             <td colspan="3";>&nbsp;</td>
             <td colspan="2"; style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo 'slip_document_invoice_version_bc'; ?></td>
             <td colspan="1";>&nbsp;</td>
         </tr>
         </table>
         <br><br> 

    </body>
 </html>

<?php
$dbConn->dbClose();
?>

<script type="text/javascript">
  function emailDoc1() {
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfSelf'.trim($document_type). substr($mfT[0]['document_number'],0,8);?>&PSMUID=<?php echo ('');?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&TEMPLAT=<?php echo 'document_invoice_version1.php';?>";
  }
  function emailDoc2() {
  	  window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfCust'.trim($document_type). substr($mfT[0]['document_number'],0,8);?>&PSMUID=<?php echo($mfT[0]['psm_uid']);?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&TEMPLAT=<?php echo 'document_invoice_version1.php';?>";
}  
</script> 
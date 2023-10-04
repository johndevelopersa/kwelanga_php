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

$adminDAO = new AdministrationDAO($dbConn);
$hasRoleSign = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId), ROLE_SIGNITURE);
$hasRoleVP   = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId),ROLE_VIEW_PRICE);

$transactionDAO = new TransactionDAO($dbConn);
$mfT = $transactionDAO->getDocumentWithDetailIgnorePermissionsItem(mysqli_real_escape_string($dbConn->connection, $docmastId));

// Get no of detail rows and weight

$noDetalRows  = 0;
$netWeight    = 0;
$grosWeight   = 0;

foreach($mfT as $row) {
  $noDetalRows++;
  $netWeight   = round($row["document_qty"] * $row["weight"],2);
  $grosWeight   = round($row["document_qty"] * $row["weight"],2);
}	

// print_r($mfT);
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
                   font-size:80px;}

                   td.dc2 {background-color:white; 
                   color:black; 
                   font-weight:bold; 
                   font-size:43px;}

                   td.dc3 {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:40px;}

                   td.dc3ts {background-color:white; 
                   color:red; 
                   font-weight:normal; 
                   font-size:40px;
                   border-top-style:solid;
                   border-top-width:0.5px;
                   border-left-style:solid;
                   border-right-style:solid;
                   border-width:0.5px;}

                   td.dc3s {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:40px;
                   border-left:solid;
                   border-right:solid; 
                   border-width:0.5px;}

                   td.dc3a {background-color:white; 
                   color:black; 
                   font-weight:bold; 
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

                   td.dc6a {background-color:white;
                   font-weight:normal; 
                   font-size:30px;
                   border-collapse:collapse;
                   border-top-style:solid; border-top-color:black; border-top-width:0.5px;
                                      
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
                   font-size:30px;}

                   td.dc2 {background-color:white; 
                   color:black; 
                   font-weight:bold; 
                   font-size:18px;}

                   td.dc3 {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:15px;}
         
                   td.dc3ts {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:15px;
                   border-top-style:solid;
                   border-left-style:solid;
                   border-right-style:solid;
                   border-width:0.5px;}

                   td.dc3s {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:15px;
                   border-left:solid;
                   border-right:solid; 
                   border-width:0.5px;}

                   td.dc3bs {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:15px;
                   border-left:solid;
                   border-right:solid;
                   border-bottom:solid; 
                   border-width:0.5px;}

                   td.dc3top {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:15px;
                   border-top:solid; 
                   border-width:0.5px;}

                   td.dc3bottom {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:15px;
                   border-bottom:solid; 
                   border-width:0.5px;}

                   td.dc3left {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:15px;
                   border-left:solid; 
                   border-width:0.5px;}
                   
                   td.dc3right {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:15px;
                   border-right:solid; 
                   border-width:0.5px;}

                   td.dc3all {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:15px;
                   border:solid; 
                   border-width:0.5px;}

                   tr.tc3all {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:15px;
                   border:solid; 
                   border-width:0.5px;}
                   
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
       <table style="border-collapse:collapse; width:98%">
           <tr>
               <td class="dc2" width="49%;" style="text-align:left;"><?php echo trim($mfT[0]["principal_name"]) ; ?></td>
               <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td> 
               <?php 
                   $filename = "images/logos/{$principalId}.jpg";
                   if (file_exists($ROOT.$PHPFOLDER.$filename) == 1) {
                       $logo = $ROOT.$PHPFOLDER.$filename;
                   ?>
                      <td width="49%;" rowspan="6";  style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
                       <img src="<?php echo $logo ?>" style=width:300px; height:170px; float:right; ></td>
                   <?php } ?> 
           </tr>
               <td class="dc3" width="49%;" style="text-align:left; height:7px;" nowrap ><span style="font-size:11px;">Co Reg No </span>&nbsp;&nbsp;<span style="font-weight:normal; font-size:11px;" nowrap><?php echo $mfT[0]["company_reg"] ?></span></td>
               <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td> 
           </tr>
           <tr>	
               <td class="dc3" width="49%;" style="text-align:left; height:7px;" nowrap ><span style="font-size:11px;">VAT No </span>&nbsp;&nbsp;<span style="font-weight:normal; font-size:11px;"><?php echo $mfT[0]['prin_vat'] ?></span></td>
               <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td>
           </tr>
           <tr>
               <td class="dc3" width="49%;" style="text-align:left; eight:10px;"><?php echo trim($mfT[0]["prin_ph_add1"])?>&nbsp;&nbsp;&nbsp;<?php echo trim($mfT[0]["prin_ph_add2"])?>&nbsp;&nbsp;&nbsp;<?php echo trim($mfT[0]["prin_add4"])?></td>
               <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td>
          </tr>
          <tr>	           
               <td class="dc3" width="49%;" style="text-align:left; eight:10px;"><?php echo trim($mfT[0]["prin_add1"])?>&nbsp;&nbsp;&nbsp;<?php echo trim($mfT[0]["prin_add2"])?>&nbsp;&nbsp;&nbsp;<?php echo trim($mfT[0]["prin_add4"])?></td>
               <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td>
          </tr>=
          </tr>
              <td class="dc3" width="49%;" style="text-align:left; height:7px;" nowrap ><span>Tel: </span>&nbsp;&nbsp;<span style="font-weight:normal;" nowrap>(27) 11 278 0300</span></td>
              <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td> 
          </tr>
          </tr>
              <td class="dc3" width="49%;" style="text-align:left; height:7px;" nowrap ><span>Fax: </span>&nbsp;&nbsp;<span style="font-weight:normal;" nowrap>(27) 11 693 1422</span></td>
              <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td> 
          </tr>
          <tr>
           <?php if (trim($mfT[0]['p_email']) <> NULL) { ?>
               <td class="dc3" width="49%;" style="text-align:left;" nowrap ><span style="font-weight:normal;"><?php echo trim($mfT[0]["p_email"]); ?></span></td>
               <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td>
         <?php } ?>
          </tr>
           <tr>
             <?php if (in_array($mfT[0]['status_uid'],array(DST_CANCELLED))) { ?>
                      <td class="dc" width="50%;"style="text-align:left;">Cancelled Document</td>
                      <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td>
             <?php    $document_type = '12'; 
                   } elseif (in_array($mfT[0]['status_uid'],array(DST_INVOICED, DST_DELIVERED_POD_OK,DST_DIRTY_POD))) { 
                         if ($mfT[0]['document_type_uid'] == 2) { ?>
                                    <td class="dc" width="50%;" style="text-align:left;">UPLIFT ADVICE</td>
                         <?php } elseif ($mfT[0]['document_type_uid'] == 6) { ?>
                                    <td class="dc" width="50%;" style="text-align:left;">DELIVERY NOTE</td>
                         <?php } elseif ($mfT[0]['document_type_uid'] == 3) { ?>
                                    <td class="dc" width="50%;" style="text-align:left;">STOCK TRANSFER</td>
                         <?php } elseif ($mfT[0]['document_type_uid'] == 13) { ?>
                                    <td class="dc" width="50%;" style="text-align:left;">ZERO PRICE INVOICE</td>
                         <?php } else { ?>
                                    <td class="dc" width="50%;" style="text-align:left;">TAX INVOICE</td>
                                    <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td>
                         <?php } ?>
             <?php    $document_type = STR_PAD($mfT[0]['document_type_uid'],2,'0',STR_PAD_LEFT);
                   } elseif (in_array($mfT[0]['status_uid'],array(DST_UNACCEPTED,DST_ACCEPTED,14))) { 
                         if ($mfT[0]['document_type_uid'] == 2) { ?>
                                    <td class="dc" width="50%;" style="text-align:left;">UPLIFT ADVICE</td>
                         <?php } elseif ($mfT[0]['document_type_uid'] == 6) { ?>
                                    <td class="dc" width="50%;" style="text-align:left;">DELIVERY NOTE</td>
                         <?php } elseif ($mfT[0]['document_type_uid'] == 3) { ?>
                                    <td class="dc" width="50%;" style="text-align:left;">STOCK TRANSFER</td>
                         <?php } elseif ($mfT[0]['document_type_uid'] == 13) { ?>
                                    <td class="dc" width="50%;" style="text-align:left;">ZERO PRICE INVOICE</td>
                         <?php } else { ?>
                            <td class="dc" style="text-align:left;">SALES ORDER</td>
                            <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td>
                         <?php } ?>
             <?php    $document_type = STR_PAD($mfT[0]['document_type_uid'],2,'0',STR_PAD_LEFT);
                   } else { ?>
                      <td class="dc" width="49%;" style="text-align:left;"><?php echo $mfT[0]['document_type_description'] ?></td>
                      <td class="dc2" width="2%;" style="text-align:left;">&nbsp;</td>
             <?php    $document_type = STR_PAD($mfT[0]['document_type_uid'],2,'0',STR_PAD_LEFT);
                 } ?>
                      <td class="dc3" width="49%;" style="text-align:right;">Page 1 of 1<br><?php echo date('d-m-Y H:i:s'); ?></td>
           </tr>
       </table>
       <table style="border-collapse:collapse; width:98%">
           <tr>
           	   <td class="dc3" width="49%;" style="text-align:left; border:solid; border-width:0.5px;  background-color:#DCDCDC;"><span style="font-weight:bold; font-size:12px;">Debtors Fax</span>&nbsp;&nbsp;<span style="font-weight:normal; font-size:12px;">(011) 412&nbsp;1969</span>
           	                                                                                           <span style="font-weight:bold; font-size:12px;">Sales Fax</span>&nbsp;&nbsp;<span style="font-weight:normal; font-size:12px;">(011) 412 2166</span></td>
               <td class="dc2" width="2%;"  style="text-align:left; ">&nbsp;</td>
               <td class="dc2" width="20%;" style="text-align:left; font-weight:normal"><img alt="<?php echo $mfT[0]['principal_uid']  . ' - ' .  ltrim(substr($mfT[0]['document_number'],0,8),'0'); ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $mfT[0]['principal_uid'] . ' - ' . ltrim(substr($mfT[0]['document_number'],0,8),'0'); ?>&print=true" /></td>
               <td class="dc2" width="28%;" style="text-align:left; ">&nbsp;</td>
               <td class="dc2" width="1%;"  style="text-align:left; ">&nbsp;</td>

           </tr>
              <td class="dc3ts" style="text-align:left;  background-color:#DCDCDC; "><span style="font-weight:bold;">Bill-To-Party</span></td>
              <td class="dc2"   style="text-align:left; ">&nbsp;</td>
              <td class="dc3ts" colspan="3" style="text-align:left;  background-color:#DCDCDC; "><span style="font-weight:bold;">Information</span></td>
           </tr>
           <tr>
              <td class="dc3s"     style="text-align:left;"><span style="font-weight:normal;"><?php echo trim($mfT[0]['bill_name']);?></span></td>
              <td class="dc2"      style="text-align:left;">&nbsp;</td>
              <td class="dc3left"  style="text-align:left; font-weight:bold;">Document Number</td>
              <td class="dc3"      style="text-align:left; font-weight:normal;"><?php echo substr(trim($mfT[0]['document_number']),2,6);?></td>
              <td class="dc3right" style="text-align:left;">&nbsp;</td>
           </tr>
           <tr>
              <td class="dc3s"     style="text-align:left;"><span style="font-weight:normal;"><?php echo trim($mfT[0]['bill_add1']);?></span></td>
              <td class="dc2"      style="text-align:left;">&nbsp;</td>
              <td class="dc3left"  style="text-align:left; font-weight:bold;">Date of Issue</td>
              <td class="dc3"      style="text-align:left; font-weight:normal;"><?php echo trim($mfT[0]['invoice_date']);?></td>
              <td class="dc3right" style="text-align:left;">&nbsp;</td>
           </tr>
           <tr>
              <td class="dc3s"     style="text-align:left;"><span style="font-weight:normal;"><?php echo trim($mfT[0]['bill_add2']);?></span></td>
              <td class="dc2"      style="text-align:left;">&nbsp;</td>
              <td class="dc3left"  style="text-align:left; font-weight:bold;">Del.N. No./Tax Point</td>
              <td class="dc3"      style="text-align:left; font-weight:normal;">&nbsp;</td>
              <td class="dc3right" style="text-align:left;">&nbsp;</td></tr>
           <tr>
              <td class="dc3bs"    style="text-align:left;"><span style="font-weight:normal;"><?php echo trim($mfT[0]['bill_add3']);?></span></td>
              <td class="dc2"      style="text-align:left;">&nbsp;</td>
              <td class="dc3left"  style="text-align:left; font-weight:bold;">PO No.</td>
              <td class="dc3"      style="text-align:left; font-weight:normal;"><?php echo trim($mfT[0]['customer_order_number']);?></td>
              <td class="dc3right" style="text-align:left;">&nbsp;</td>
           </tr>
           <tr>
              <td class="dc3s"     style="background-color:#DCDCDC; text-align:left;"><span style="font-weight:bold;">Ship-To-Party</span></td>
              <td class="dc2"      style="text-align:left;">&nbsp;</td>
              <td class="dc3left"  style="text-align:left; font-weight:bold;">PO Date.</td>
              <td class="dc3"      style="text-align:left; font-weight:normal;"><?php echo trim($mfT[0]['order_date']);?></td>
              <td class="dc3right" style="text-align:left;">&nbsp;</td>
           <tr>
              <td class="dc3s"     style="text-align:left;"><span style="font-weight:normal;"><?php echo trim($mfT[0]['store_name']);?></span></td>
              <td class="dc2"      style="text-align:left;">&nbsp;</td>
              <td class="dc3left"  style="text-align:left; font-weight:bold;">Customer No.</td>
              <td class="dc3"      style="text-align:left; font-weight:normal;"><?php echo trim($mfT[0]['SFAccount']);?></td>
              <td class="dc3right" style="text-align:left;">&nbsp;</td>
           </tr>
           <tr>
              <td class="dc3s"     style="text-align:left;"><span style="font-weight:normal;"><?php echo trim($mfT[0]['deliver_add1']);?></span></td>
              <td class="dc2"      style="text-align:left;">&nbsp;</td>
              <td class="dc3left"  style="text-align:left; font-weight:bold;">Customer VAT No.</td>
              <td class="dc3"      style="text-align:left; font-weight:normal;"><?php echo trim($mfT[0]['vat_number']);?></td>
              <td class="dc3right" style="text-align:left;">&nbsp;</td>           
          </tr>           
           <tr>
              <td class="dc3s"     style="text-align:left;"><span style="font-weight:normal;"><?php echo trim($mfT[0]['deliver_add2']);?></span></td>
              <td class="dc2"      style="text-align:left;">&nbsp;</td>
              <td class="dc3left"  style="text-align:left; font-weight:bold;">Currency</td>
              <td class="dc3"      style="text-align:left; font-weight:normal;">ZAR</td>
              <td class="dc3right" style="text-align:left;">&nbsp;</td> 
           <tr>
              <td class="dc3s"     style="text-align:left;"><span style="font-weight:normal;"><?php echo trim($mfT[0]['deliver_add3']);?></span></td>
              <td class="dc2"      style="text-align:left;">&nbsp;</td>           
              <td class="dc3left"  style="text-align:left; font-weight:bold;">Payment Terms</td>
              <td class="dc3"      style="text-align:left; font-weight:normal;">30 days from date of statement</td>
              <td class="dc3right" style="text-align:left;">&nbsp;</td> 
           <tr>           

           <tr>
              <td class="dc3s"     style="text-align:left;"><span style="font-weight:normal;">&nbsp;</span></td>
              <td class="dc2"      style="text-align:left;">&nbsp;</td> 
              <td class="dc3left"  style="text-align:left; font-weight:bold;">Delivery Terms</td>
              <td class="dc3"      style="text-align:left; font-weight:normal;">&nbsp;</td>
              <td class="dc3right" style="text-align:left;">&nbsp;</td> 
           </tr> 
           <tr>
               <td class="dc3s"    style=background-color:#DCDCDC; text-align:left;"><span style="font-weight:bold;"><span style="font-weight:bold;">Ship-from</span>&nbsp;&nbsp;
                                                                                              <span style="font-weight:normal;"><?php echo trim($mfT[0]['depot_name']);?></span></td>
               <td class="dc2"      style="text-align:left;">&nbsp;</td>
               <td class="dc3left"  style="text-align:left; font-weight:bold;">Gross Weight</td>
               <td class="dc3"      style="text-align:left; font-weight:normal;"><?php echo $grosWeight; ?></td>
               <td class="dc3right" style="text-align:left;">&nbsp;</td>        
           </tr>       
           <tr>
               <td class="dc3bs"    style="text-align:left; background-color:#DCDCDC;"><span style="font-weight:bold;"><span style="font-weight:bold;">Sales Office</span>&nbsp;&nbsp;
                                                                                                 <span style="font-weight:normal;">&nbsp;</span></td>
               <td class="dc2"      style="text-align:left;">&nbsp;</td>
               <td class="dc3left"  style="text-align:left; font-weight:bold;">Net Weight</td>
               <td class="dc3"      style="text-align:left; font-weight:normal;"><?php echo $netWeight; ?></td>
               <td class="dc3right" style="text-align:left;">&nbsp;</td> 
           </tr>
           <tr>
               <td class="dc3top" > &nbsp;</span></td>
               <td class="dc2"    >&nbsp;</td>
               <td class="dc3top" > &nbsp;</span></td>
               <td class="dc3top" > &nbsp;</span></td>
              <td class="dc3top"  > &nbsp;</span></td>
           </tr>


       </table>  
       <table style="border-collapse:collapse; width:98%">
          <tr class="tc3all">
            <td class="dc3" width="1%;" style="text-align:left;  background-color:#DCDCDC;">&nbsp;</td>
            <td class="dc3" width="13%;" style="text-align:left; background-color:#DCDCDC;">Item</td>
            <td class="dc3" width="33%;" style="text-align:left; background-color:#DCDCDC;">Material/Description</td>
            <td class="dc3" width="10%;" style="text-align:left; background-color:#DCDCDC;">Quantity</td>
            <td class="dc3" width="10%;" style="text-align:left; background-color:#DCDCDC; ">VAT%</td>	
            <td class="dc3" width="12%;" style="text-align:left; background-color:#DCDCDC; ">Unit&nbsp;Price</td>	
            <td class="dc3" width="10%;" style="text-align:left; background-color:#DCDCDC;">Discount</td>
            <td class="dc3" width="10%;" style="text-align:left; background-color:#DCDCDC;">Value</td>
            <td class="dc3" width="1%;" style="text-align:left;  background-color:#DCDCDC;">&nbsp;</td>
        	</tr>
        	<?php 
        	foreach($mfT as $row) {
               $nettCP=0;
               ?>
               <tr>
                   <td class="dc3left" style="text-align:left;">&nbsp;</td>
                   <td class="dc3" style="text-align:left;"nowrap><?php echo $noDetalRows; ?></td>
                   <td class="dc3" style="text-align:left;"nowrap><?php echo $row["product_description"]?>&nbsp;<br><?php echo $row["product_code"]?>&nbsp;</td>
                    <?php if (in_array($mfT[0]['status_uid'],array(DST_UNACCEPTED,DST_ACCEPTED, DST_INPICK,14))) {  
                        $totQ+= $row["ordered_qty"] ?>
                        <td class="dc3" style="text-align:left;"nowrap><?php echo $row["ordered_qty"]?>&nbsp;</td>
                    <?php } else { 
                         $totQ+= $row["document_qty"]?>
                         <td class="dc3" style="text-align:left;"nowrap><?php echo $row["document_qty"]?>&nbsp;</td>
                    <?php }?>
                    <td class="dc3" style="text-align:left;"nowrap><?php echo number_format($row["vat_rate"],2, "."," ")?>&nbsp;</td>
                    <td class="dc3" style="text-align:left;"nowrap><?php echo number_format($row["net_price"],2, "."," ")?>&nbsp;</td>
                    <td class="dc3" style="text-align:left;"nowrap><?php echo number_format($row["discount_value"],2, "."," ")?>&nbsp;</td>
                    <td class="dc3" style="text-align:right;"nowrap><?php echo number_format($row["extended_price"],2, "."," ")?>&nbsp;</td>
                    <td class="dc3right" style="text-align:left;">&nbsp;</td>
                    <?php 
                        $totVAT+= $row["vat_amount"];
                        $totTot+= $row["total"] ;
                        $totNett+=$nettcp;
          } ?>
                <tr>
                   <td class="dc3left" style="text-align:left;">&nbsp;</td>
                   <td colspan="7";>&nbsp;</td>
                   <td class="dc3right" style="text-align:left;">&nbsp;</td>
                </tr>
                <tr>
                   <td class="dc3left" style="text-align:left;">&nbsp;</td>
                   <td colspan="5";>&nbsp;</td>
                   <td class="dc3" style="text-align:left; font-weight:bold;" nowrap>Sub Total</td>
                   <td class="dc3" style="text-align:right; font-weight:bold;" nowrap><?php echo number_format($totTot - $totVAT,2, "."," ") ?>&nbsp;</td>
                   <td class="dc3right" style="text-align:left;">&nbsp;</td>
                </tr>
                <tr>
                   <td class="dc3left" style="text-align:left;">&nbsp;</td>
                   <td colspan="5";>&nbsp;</td>
                   <td class="dc3" style="text-align:left; font-weight:bold;" nowrap>VAT</td>
                   <td class="dc3" style="text-align:right; font-weight:bold;" nowrap><?php echo number_format($totVAT,2, "."," ") ?>&nbsp;</td>
                   <td class="dc3right" style="text-align:left;">&nbsp;</td>
                </tr>
                <tr>
                   <td class="dc3left" style="text-align:left;  border-bottom:solid; border-width:0.5px;">&nbsp;</td>
                   <td class="dc3bottom" colspan="5";>&nbsp;</td>
                   <td class="dc3bottom" style="text-align:left; font-weight:bold;" nowrap>Sub Total</td>
                   <td class="dc3bottom" style="text-align:right; font-weight:bold;" nowrap><?php echo number_format($totTot,2, "."," ") ?>&nbsp;</td>
                   <td class="dc3right" style="text-align:left;  border-bottom:solid; border-width:0.5px;">&nbsp;</td>
                </tr>
                <tr class="tc3all">
                   <td>&nbsp</td>
                   <td class="dc3" colspan="7";>RECEIVED IN GOOD ORDER BY:&nbsp<br>
                   	                            Name (please print)___________________&nbsp<br>
                   	                            Goods remain the property of Wilmar SA (Pty) Ltd. until paid in full.&nbsp</td>
                   <td>&nbsp</td>
                </tr>
                <tr class="tc3all">
                   <td>&nbsp</td>
                   <td class="dc3" colspan="7";>PLEASE NOTE: All payments shall be made to Wilmar SA (Pty) Ltd as per Wilmar SA (Pty) Ltd's nominated<br>
                   	                            bank account. In the event of any payments being mislaid; lost in the post; or transferred to an<br>
                   	                            incorrect banking account the Debtor shall still be liable to Wilmar SA (Pty) Ltd for payment.<br>
                   	                            Should Wilmar SA (Pty) Ltd at any time advise the Debtor of any change to Wilmar SA (Pty) Ltd<br>
                   	                            banking account details the Debtor shall confirm such change with the Financial Manager of Wilmar SA (Pty) Ltd
                   	                            before effecting any further payments, provided however that nothing contained herein shall be interpreted<br>
                   	                            as obliging Wilmar SA (Pty) Ltd to afford the Debtor any such indulgence to effect payment after due date.</td>
                   <td>&nbsp</td>
                </tr>
        </table>  



    </body>
 </html>

<?php
$dbConn->dbClose();
?>

<script type="text/javascript">
  function emailDoc1() {
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfSelf'.trim($document_type). substr($mfT[0]['document_number'],2,6);?>&PSMUID=<?php echo ('');?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&TEMPLAT=<?php echo 'document_invoice_versionDT.php';?>";
  }
  function emailDoc2() {
  	  window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfCust'.trim($document_type). substr($mfT[0]['document_number'],2,6);?>&PSMUID=<?php echo($mfT[0]['psm_uid']);?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&TEMPLAT=<?php echo 'document_invoice_versionDT.php';?>";
}  
</script> 
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
                   font-size:60px;}

                   td.dc2 {background-color:white; 
                   color:black; 
                   font-weight:bold; 
                   font-size:43px;}

                   td.dc3 {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:40px;}

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
                   font-size:35px;
                   border-collapse: collapse;
                   border-top-style:solid; border-top-color:black; border-top-width:0.5px;
                   border-left-style:solid;  border-left-color:black;  border-left-width:0.5px;
                   border-right-style:solid; border-right-color:black; border-right-width:0.5px;}  
                   
                   td.dc6 {border-collapse: collapse;
                   border-top-style:solid; border-top-color:black; border-top-width:0,5px;} 
                                      
                   td.dc7 {background-color:white;
                   font-weight:normal; 
                   font-size:30px;
                   border-collapse: collapse;
                   border-bottom-style:solid; border-bottom-color:black; border-bottom-width:0.5px; } 

                   td.dc8 {background-color:white;
                   font-weight:normal; 
                   font-size:35px;
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

                   td.dc8 {border-collapse: collapse;
                   font-weight:normal; 
                   font-size:17px;
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
                 <?php if ($userCategory == "P") { ?>
                     <a href="javascript:;" onclick="emailDocc1();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/email-icon.png" border="0" alt="" align="left" > Email Self</a>
                     <a href="javascript:;" onclick="emailDocc2();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/email-icon.png" border="0" alt="" align="left" > Email Customer</a>
                  <?php } ?>
                 <div style="clear:both;"></div>
               </div>
             </div><!-- HIDE THIS PRINT AREA : END /--->
            </td>
          </tr>
         </table>
       </div>
     <table style="border-collapse:collapse; width:100%;">
         <tr>
             <td width="3%;" >&nbsp;</td>         	
               <?php if(in_array($mfT[0]['status_uid'],array(DST_INVOICED, DST_DELIVERED_POD_OK,DST_DIRTY_POD))) { ?>
                   <td class="dc"  width="34%;" style="text-align:left;">PACKING LIST</td>
               <?php $document_type = '42'; 
               } elseif(in_array($mfT[0]['status_uid'],array(DST_PROCESSED))) { ?>
                   <td class="dc"  width="34%;" style="text-align:left;">CREDIT NOTE</td>
                <?php $document_type = '4'; 
               } else { ?>               
                   <td class="dc"  width="34%;" style="text-align:left;">PICKING LIST</td>
                <?php $document_type = '40'; } ?>
             <td colspan="2" width="28%;" >&nbsp;</td>
             <td width="35%;">&nbsp;</td>
             <td width="3%;" >&nbsp;</td> 
         </tr>
         <tr>
             <td width="3%;">&nbsp;</td>
             <td class="dc2" width="38%;" style="text-align:left;"><?php echo $mfT[0]["principal_name"]; ?></td>
             <td width="32%;">&nbsp;</td>
             <?php 
                 $filename = "images/logos/{$principalId}.png";
                 $file     = HOST_SURESERVER_AS_USER.$PHPFOLDER.$filename;
                 if (file_exists($ROOT.$PHPFOLDER.$filename) == 1){
                    $logo = $ROOT.$PHPFOLDER.$filename;
             ?>
                  <td rowspan="5" width="26%;" style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
                      <?php echo "<img src=".$logo." style=width:145px; height:95px; float:right; >" ?></td>
             <?php } ?>
             
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td class="dc3" style="text-align:left;" nowrap ><span style="font-weight:bold;">Document Number</span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo substr($mfT[0]['document_number'],2,6); ?></span></td>
             <td>&nbsp;</td>

               <?php if(in_array($mfT[0]['status_uid'],array(DST_INVOICED, DST_DELIVERED_POD_OK,DST_DIRTY_POD))) { ?>
             <td class="dc3" style="text-align:right;" nowrap ><span style="font-weight:bold;">Delivery Note Number</span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo substr($mfT[0]['document_number'],2,6); ?></span></td>
               <?php
               } elseif(in_array($mfT[0]['status_uid'],array(DST_PROCESSED))) { ?>
             <td class="dc3" style="text-align:right;" nowrap ><span style="font-weight:bold;">Credit Note Number</span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo substr($mfT[0]['alternate_document_number'],2,6); ?></span></td>
                <?php
               } else { ?>               
             <td class="dc3" style="text-align:right;" nowrap ><span style="font-weight:bold;">&nbsp;</td>
               <?php } ?>
             <td>&nbsp;</td>
         </tr> 
         <tr>
             <td>&nbsp;</td>
             <td class="dc3" style="text-align:left ;" nowrap ><span style="font-weight:bold;">Date </span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo $mfT[0]["invoice_date"]; ?></span></td>
             <td>&nbsp;</td>

               <?php if(in_array($mfT[0]['status_uid'],array(DST_INVOICED, DST_DELIVERED_POD_OK,DST_DIRTY_POD))) { ?>
             <td class="dc3" style="text-align:right;" nowrap ><span style="font-weight:bold;">Delivery Note Number</span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo substr($mfT[0]['document_number'],2,6); ?></span></td>
               <?php
               } elseif(in_array($mfT[0]['status_uid'],array(DST_PROCESSED))) { ?>
             <td class="dc3" style="text-align:right;" nowrap ><span style="font-weight:bold;">Credit Note Number</span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo substr($mfT[0]['alternate_document_number'],2,6); ?></span></td>
                <?php
               } else { ?>               
             <td class="dc3" style="text-align:right;" nowrap >&nbsp;</td>
               <?php } ?>
             <td>&nbsp;</td>
         </tr> 
         <tr>
             <td>&nbsp;</td>
         </tr>
         <tr> 
             <td>&nbsp;</td>
             <td class="dc3" style="text-align:left;" nowrap ><span style="font-weight:bold;">Recipient </span>&nbsp;&nbsp;</td>
             <td>&nbsp;</td>
             <td class="dc3" width="50%;" style="text-align:right;" nowrap ><span style="font-weight:bold;">&nbsp;</span></td>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td class="dc3" style="text-align:left;" nowrap ><span style="font-weight:normal;"><?php echo $mfT[0]['store_name']; ?></span></td>
             <td>&nbsp;</td>
             <td class="dc3" width="50%;" style="text-align:right;" nowrap ><span style="font-weight:normal;">&nbsp;</span></td>
             <td>&nbsp;</td>
         </tr> 
          <?php
         if($mfT[0]['delivery_instructions'] <> NULL) { ?>
           <tr>
              <td>&nbsp;</td>
              <td class="dc3" width="45%;" style="text-align:left;" nowrap ><span style="font-weight:bold ;">Instructions</span>&nbsp;&nbsp;<span style="font-weight:normal;"><?php echo $mfT[0]['delivery_instructions']; ?></span></td>
              <td class="dc3" width="45%;" style="text-align:right;" nowrap ><span style="font-weight:normal;">&nbsp;</span></td>
           </tr>
         <?php } ?>
         <tr>
             <td>&nbsp;</td>
         </tr>
       </table>
       <table style= "border-collapse:collapse; width:100%; ">
         <tr>
           <th style="width:2%;">&nbsp;</th>
           <th class="th1" style="width:30% ;" nowrap >&nbsp;&nbsp;Code</th>
           <th class="th1" style="width:50% ;" nowrap >&nbsp;&nbsp;Description</th>
           <th class="th1" style="width:20% ;" nowrap >&nbsp;&nbsp;Quantity</th>
           <th style="width:2%;">&nbsp;</th>

         </tr>
         <?php $totQ=0; $totLP=0; $totDV=0; $totCP=0; $totNett=0; $weightTot=0;
       foreach($mfT as $row) {
         $nettCP=0;
         $cls = "dc5";
         ?>
         <tr>
           <td>&nbsp;</td>         	
           <td class="<?php echo $cls; ?>" nowrap>&nbsp;&nbsp;<?php echo $row["product_code"]?>&nbsp;</td>
           <td class="<?php echo $cls; ?>" style= text-align:left; nowrap>&nbsp;&nbsp;<?php echo $row["product_description"]?>&nbsp;</td>
           <?php if (in_array($mfT[0]['status_uid'],array(DST_UNACCEPTED,DST_ACCEPTED, DST_INPICK,14))) {  
               $totQ+= $row["ordered_qty"] ?>
               <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo $row["ordered_qty"]?>&nbsp;</td>
           <?php } else { 
               $totQ+= $row["document_qty"]?>
               <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo $row["document_qty"]?>&nbsp;</td>
           <?php } ?> 	
          </tr>
          <tr>
             <td>&nbsp;</td>   
             <td class="<?php echo $cls; ?>" >&nbsp;</td>
             <td class="<?php echo $cls; ?>" >&nbsp;</td>
             <td class="<?php echo $cls; ?>" >&nbsp;</td>
             <td>&nbsp;</td>   
          </tr>
          <?php } ?>
           
<!-- total line -->
         <tr>
           <td>&nbsp;</td>   
           <td class="dc6" colspan="2" >&nbsp</td>
           <td class="dc8" style="text-align:right"; nowrap ><?php echo $totQ; ?>&nbsp;</td>
           <td>&nbsp;</td> 
         </tr>         
         <tr>
           <td>&nbsp;</td>  
         </tr>  
       </table>
       <?php if ($mfT[0]['status_uid'] >= 76 and $weightTot>0) { ?>
           <table style="border-collapse:collapse; width:100%;">
             <tr>
                 <td colspan="3">&nbsp;</td>
             </tr>
              <tr>
                 <td width="3%;">&nbsp;</td>
                 <td class="dc3" style="width:94%"; "text-align:left"; nowrap >Calculated Weight&nbsp;&nbsp;<?php echo number_format($weightTot,2)?></td>
                 <td width="3%;">&nbsp;</td>
              </tr>
              <tr>
                 <td colspan="3">&nbsp;</td>
              </tr>
           </table>
       <?php } ?>
       <!-- footer -->       
       <?php  if (trim($mfT[0]['tcs']) <> NULL) { ?>
           <table style="border-collapse:collapse; width:100%;">
              <tr>
                 <td width="3%;">&nbsp;</td>
                 <td class="dc2" style="width:94%"; "text-align:left;" nowrap >Notes</td>
                 <td width="3%;">&nbsp;</td>
              </tr>
              <tr>
                <td width="3%;">&nbsp;</td>
                <td class="dc3" style="width:94%"; "text-align:left"; nowrap ><?php echo (str_replace(chr(10),"<br>", $mfT[0]['tcs'])); ?></td>
                <td width="3%;">&nbsp;</td>
              </tr>
              <tr>
                <td width="3%;">&nbsp;</td>
                <td class="dc3a" style="width:94%"; "text-align:left"; nowrap ><?php echo (str_replace(chr(10),"<br>", $mfT[0]['btcs'])); ?>&nbsp;&nbsp;&nbsp;<?php echo $mfT[0]['branch_code']; ?></td>
                <td width="3%;">&nbsp;</td>
              </tr>
              <tr>
                <td>&nbsp;</td> 
              </tr>   
           </table>  
      <?php } ?>
      <?php  if (trim($mfT[0]['banking_details']) <> NULL && !in_array($mfT[0]['document_type_uid'],array('4','2')))  { ?>
           <table style="border-collapse:collapse; width:100%;">      
              <tr>
                <td width="3%;">&nbsp;</td>
                <td class="dc2" style="width:94%"; "text-align:left"; nowrap >Banking Details</td>
                <td width="3%;">&nbsp;</td>      
              </tr>       
              <tr>
               <td width="3%;">&nbsp;</td>
                <td class="dc3" style="width:96%"; "text-align:left"; nowrap ><?php echo (str_replace(chr(43),"<br>", $mfT[0]["alt_banking_details"]))?></td>
               <td width="3%;">&nbsp;</td>
              </tr>   
           </table>  
       <?php } ?>
       <table style="border-collapse:collapse; width:100%;"> 
          <tr>
           <td class="dc3" style="width:100%"; "text-align:left"; nowrap >Received in good order&nbsp;&nbsp;</td>
         </tr>
         <tr>
           <td class="dc3" >&nbsp;</td>
         </tr>
          <tr>
           <td class="dc3" style="width:100%"; "text-align:left"; nowrap >Name______________________&nbsp;&nbsp; Signed____________________   Date____________</td>
         </tr>
         <tr>
           <td class="dc3" >&nbsp;</td>
         </tr>
         <tr>
             <tr>
                 <td colspan="3">&nbsp;</td>
             </tr>
         <tr>
           <td width="100%;" style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
             <img src="<?php echo $ROOT.$PHPFOLDER; ?>images/kwelanga1.gif" style="width:75px; height:30px; float:right;" >
           </td>
         </tr>
         <tr>
           <td width="100%;" style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
             <?php echo date('Y-m-d H:i:s'); ?>
           </td>
         </tr> 
         <tr>
           <td width="100%;" style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
             <?php echo 'jcs_packing_list_version1.php'; ?>
           </td>
         </tr>
       </table> 
    </body>
 </html>

<?php
$dbConn->dbClose();
?>

<script type="text/javascript">
  function emailDocc1() {
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfSelf'.trim($document_type). substr($mfT[0]['document_number'],2,6);?>&PSMUID=<?php echo ('');?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&TEMPLAT=<?php echo 'document_delivery_note_version1.php';?>";
  }
  function emailDocc2() {
  	  window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfCust'.trim($document_type). substr($mfT[0]['document_number'],2,6);?>&PSMUID=<?php echo($mfT[0]['psm_uid']);?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&TEMPLAT=<?php echo 'document_delivery_note_version1.php';?>";
  }  
</script> 
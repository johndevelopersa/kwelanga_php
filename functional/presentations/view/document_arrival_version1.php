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
$hasRoleSign = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId), ROLE_SIGNITURE);
$hasRoleVP   = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId),ROLE_VIEW_PRICE);

$transactionDAO = new TransactionDAO($dbConn);
$mfT = $transactionDAO->getDocumentWithDetailItem(mysqli_real_escape_string($dbConn->connection,$userId),mysqli_real_escape_string($dbConn->connection,$principalId),mysqli_real_escape_string($dbConn->connection,$docmastId), $orderBy="principal_product.product_code");

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
            <td class="dc" style="text-align:left;">Warehouse Arrival</td>
           <?php $document_type = '39';  ?>
         </tr>
         <tr>
            <td width="50%;" >&nbsp;</td>
            <td width="50%;" >&nbsp;</td>            
         </tr>         
         <tr>
            <td class="dc2" width="50%;" style="text-align:left;" ><?php echo $mfT[0]['depot_name'];?></td>            
            <td width="50%;" >&nbsp;</td>            
         </tr>         
         <tr>
            <td width="50%;" >&nbsp;</td>
            <td width="50%;" >&nbsp;</td>            
         </tr>         
         <tr>
            <td class="dc3" width="60%;" style="text-align:left;"><span style="font-weight:bold;">Principal   </span><span style="font-weight:normal;"><?php echo $mfT[0]["principal_name"]; ?></span></td>
            <td width="50%;" >&nbsp;</td>
         </tr>         
         <tr>
            <td class="dc3" width="60%;" style="text-align:left;"><span style="font-weight:bold;">Arrival Date   </span><span style="font-weight:normal;"><?php echo $mfT[0]["order_date"]; ?></span></td>
         </tr>
          <tr>
            <td width="50%;" >&nbsp;</td>
         </tr>
         <tr>
           <td class="dc3" width="50%;" style="text-align:left;" nowrap><span style="font-weight:bold;">Document Number</span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo ltrim($mfT[0]['document_number'],'0'); ?></span></td>
         <tr>
           <td class="dc3" width="50%;" style="text-align:left ;" nowrap><span style="font-weight:bold ;">Principal Reference </span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo $mfT[0]["customer_order_number"] ?></span></td>
         </tr>         
         <tr>
           <td width="50%;" >&nbsp;</td>
           <td width="50%;" >&nbsp;</td>
         </tr>
         <!-- detail -->
         <tr>
           <td class="dc3" style="text-align:left ;" nowrap><span style="font-weight:bold ;">Arrival Details</span></td>
         </tr>
       </table>     
       <table style= "border-collapse:collapse; width:100%; ">
         <tr>
           <th class="th1" style="width:20% ;" nowrap >Code</th>
           <th class="th1" style="width:40% ;" nowrap >Description</th>
           <th class="th1" style="width:10% ;" nowrap >Received Quantity</th>
           <th class="th1" style="width:10% ;" nowrap >Checked </th>
         </tr>
           <?php $totQ=0;
               $cls = "dc5";
               foreach($mfT as $row) { ?>
                 <tr>
                   <td class="<?php echo $cls; ?>" nowrap><?php echo $row["product_code"]?>&nbsp;</td>
                   <td class="<?php echo $cls; ?>" style= text-align:left; nowrap><?php echo $row["product_description"]?>&nbsp;</td>
                   <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap><?php echo $row["ordered_qty"]?>&nbsp;</td>
                   <td class="<?php echo $cls; ?>" style="text-align:right"; nowrap>&nbsp;</td>
                  <?php $totQ+= $row["ordered_qty"] ; ?>
                 </tr>
                <tr>
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
           <td class="dc8" style="text-align:right"; nowrap >&nbsp;</td>
         </tr>         
       </table>
       
       <table style="width:88%;" >
         <tr>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>           
        </tr>	
         <tr>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>           
        </tr>
         <tr>
           <td class="dc3" width="50%;" style="text-align:left;" nowrap><span style="font-weight:bold;">Captured By    </span>&nbsp;&nbsp;<span style="font-weight:normal ;"><?php echo $mfT[0]['captured_by_name'] ?></span></td>
       </tr>
         <tr>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>           
        </tr>	
        <tr>
           <td class="dc2" style="text-align:left;" nowrap >Comments </td>
           <td class="dc3" >&nbsp;</td>
           <td class="dc3" >&nbsp;</td>
           <td class="dc3" >&nbsp;</td>
        </tr>
         <tr>
           <td class="dc3" >&nbsp;</td>
           <td class="dc3" >&nbsp;</td>
           <td class="dc3" >&nbsp;</td>
        </tr>	
        <tr>
           <td class="dc7" colspan="3" >&nbsp;</td>
         </tr>
         <tr>
           <td class="dc3" >&nbsp;</td>
           <td class="dc3" >&nbsp;</td>
           <td class="dc3" >&nbsp;</td>
         </tr>
         <tr>
           <td class="dc7" colspan="3" >&nbsp;</td>
         </tr>
         <tr>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>           
        </tr>
         <tr>
           <td class="dc2" >Recieved And Checked By </td>
           <td class="dc7" colspan="2" >&nbsp;</td>
          </tr>
         <tr>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>           
        </tr>	 
         <tr>
           <td class="dc2" >Received Date </td>
           <td class="dc7" colspan="2" >&nbsp;</td>
          </tr>
         <tr>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>           
        </tr>	 
        <tr>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>
           <td class="dc3" style="width:22%;">&nbsp;</td>           
        </tr>	           
        </table>
        <table style="width:100%;"> 
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
             <?php echo 'document_arrival_version1'; ?>
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
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfSelf'.trim($document_type). substr($mfT[0]['document_number'],2,6);?>&PSMUID=<?php echo ('');?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&TEMPLAT=<?php echo 'document_invoice_version1.php';?>";
  }
  function emailDoc2() {
  	  window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfCust'.trim($document_type). substr($mfT[0]['document_number'],2,6);?>&PSMUID=<?php echo($mfT[0]['psm_uid']);?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&TEMPLAT=<?php echo 'document_invoice_version1.php';?>";
}  
</script> 
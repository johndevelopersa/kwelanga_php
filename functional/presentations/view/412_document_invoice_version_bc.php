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

//if ($userId == 11) {
//print_r($mfT);	
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
                   font-size:20px;}

                   td.dc3 {background-color:white; 
                   color:black; 
                   font-weight:normal; 
                   font-size:12px;}

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
              <td style="width:2%">&nbsp;</td>
              <td style="width:15%">&nbsp;</td>
              <td style="width:38%">&nbsp;</td>
              <td style="width:5%">&nbsp;</td>
              <td style="width:38%">&nbsp;</td>
              <td style="width:2%">&nbsp;</td>              
     	   </tr>
     	   <tr>
              <?php 
                   $filename = "images/logos/{$principalId}.jpg";
                   $file     = HOST_SURESERVER_AS_USER.$PHPFOLDER.$filename;
                   if (file_exists($ROOT.$PHPFOLDER.$filename) == 1) {
                         $logo = $ROOT.$PHPFOLDER.$filename;
               ?>
                <td rowspan="6" colspan="2" style="text-align:right; border-style:none; color:grey; font-weight:normal; font-size:0.55em;">
                             <?php echo "<img src=".$logo." style=width:120px; height:150px; float:right; >" ?></td>
               <?php
                   } ?>
              <td class="dc2" colspan="4" style="text-align:left; border-style:none; padding:20px;"><?php echo $mfT[0]["principal_name"]; ?></td>              
              <td>&nbsp;</td>        
     	   </tr>     	
     	   
     	   
     	   
     	   <tr>
              <td class="dc3" style="text-align:left; padding-left:70px; font-size:10px;" nowrap >VAT No <?php echo $mfT[0]['prin_vat'] ?></td>
              <td>&nbsp;</td> 
              <td class="dc3" style="text-align:left; font-size:10px;" nowrap ><span style="font-weight:nornal ;"><?php echo trim($mfT[0]["prin_ph_add1"]) . "  " . trim($mfT[0]["prin_ph_add2"]); ?></td>
              <td>&nbsp;</td>        
     	   </tr>
         <tr>
             <td class="dc3" style="text-align:left; padding-left:70px; font-size:10px;" nowrap >Company Reg No <?php echo $mfT[0]["company_reg"] ?></td>
              <td>&nbsp;</td> 
              <td class="dc3" style="text-align:left; font-size:10px;" nowrap ><?php echo trim($mfT[0]["prin_ph_add3"]); ?></td></td>
              <td>&nbsp;</td>        
     	   </tr>

         <tr>
              <td class="dc3" style="text-align:left; padding-left:70px; font-size:10px;" nowrap ><?php echo trim($mfT[0]["prin_add1"]) . "  " . trim($mfT[0]["prin_add2"]) ?></td>
              <td>&nbsp;</td> 
              <td class="dc3" style="text-align:left; font-size:10px;" nowrap >(T) <?php echo substr($mfT[0]['office_tel'],0,3).' '. substr($mfT[0]['office_tel'],3,3) .' '. substr($mfT[0]['office_tel'],6,4); ?></td>
              <td>&nbsp;</td>        
     	   </tr>

         <tr>
              <td class="dc3" style="text-align:left; padding-left:70px; font-size:10px;" nowrap ><?php echo trim($mfT[0]["prin_add3"])?></td>
              <td>&nbsp;</td> 
              <td class="dc3" style="text-align:left; font-size:10px;" nowrap >(F) <?php echo substr($mfT[0]['office_tel2'],0,3).' '. substr($mfT[0]['office_tel2'],3,3) .' '. substr($mfT[0]['office_tel2'],6,4); ?></td></td>
              <td>&nbsp;</td>        
     	   </tr>

         <tr>
              <td class="dc3" style="text-align:left; padding-left:70px; font-size:10px;" nowrap >(e) <?php echo trim($mfT[0]["p_email"])?></td>
              <td>&nbsp;</td> 
              <td class="dc3" style="text-align:left; font-size:10px;" nowrap >(w) www.upap.co.za</td></td>
              <td>&nbsp;</td>        
     	   </tr>
         <tr>
              <td colspan="6">&nbsp;</td>
         </tr>     
         <tr>
              <td>&nbsp;</td>
              <td colspan="4" style="text-align:center; font-size:20px; background-color:#E6E6E6; font-weight:bold; ">Tax Invoice</td>  
              <td>&nbsp;</td>       
     	   </tr>
         <tr> 
         	    <td>&nbsp;</td>
              <td colspan="4" style=border-bottom-style:solid>&nbsp;</td>
              <td>&nbsp;</td>
         </tr> 
         <tr>
              <td>&nbsp;</td>
              <td colspan="1" style="text-align:left; font-size:15px; font-weight:bold; ">Invoice No.</td>
              <td colspan="1" style="text-align:left; font-size:15px; font-weight:bold; "><?php echo substr($mfT[0]['document_number'],0,8); ?></td>
         </tr>
         <tr> 
         	    <td>&nbsp;</td>
              <td colspan="4" style=border-bottom-style:none>&nbsp;</td>
              <td>&nbsp;</td>
         </tr> 

     </table>     
     <table style="border-collapse:collapse; width:98%">
         <tr>
         	    <td style="width:2%">&nbsp;</td>
              <td colspan="1" style="width:45%; border-width:0.5px; border-style:solid solid none solid; padding-left:5px; text-align:left; font-weight:bold;" >Invoice To</td>
              <td style="width:6%">&nbsp;</td>
              <td colspan="1" style="width:45%; border-width:0.5px; border-style:solid solid none solid; padding-left:5px; text-align:left; font-weight:bold;">Address</td>
         	    <td style="width:2%">&nbsp;</td>
         </tr>
         <tr>
         	    <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:normal;"" ><?php echo $mfT[0]['bill_name']; ?></td>
              <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:normal;""><?php echo $mfT[0]['bill_add1']; ?></td>
         	    <td>&nbsp;</td>
         </tr>
         <tr>
         	    <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:bold;" >&nbsp;</td>
              <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:normal;"><?php echo $mfT[0]['bill_add2']; ?></td>
         	    <td>&nbsp;</td>
         </tr>
         <tr>
         	    <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:normal;" >Customer VAT Number&nbsp;&nbsp;<?php echo $mfT[0]['vat_number']; ?></td>
              <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:normal;"><?php echo $mfT[0]['bill_add3']; ?></td>
         	    <td>&nbsp;</td>
         </tr>
         <tr>
         	    <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid solid solid;" >&nbsp;</td>
              <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid solid solid;">&nbsp;</td>
         	    <td>&nbsp;</td>
         </tr>
         <tr>
              <td colspan="5" style=border-bottom-style:none>&nbsp;</td>
         </tr>
         <tr>
         	    <td style="width:2%">&nbsp;</td>
              <td colspan="1" style="width:45%; border-width:0.5px; border-style:solid solid none solid; padding-left:5px; text-align:left; font-weight:bold;" >Deliver To</td>
              <td style="width:6%">&nbsp;</td>
              <td colspan="1" style="width:45%; border-width:0.5px; border-style:solid solid none solid; padding-left:5px; text-align:left; font-weight:bold;">Payment Details</td>
         	    <td style="width:2%">&nbsp;</td>
         </tr>
         <tr>
         	    <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:normal;"" ><?php echo $mfT[0]['store_name']; ?></td>
              <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:normal;"">&nbsp;</td>
         	    <td>&nbsp;</td>
         </tr>
         <tr>
         	    <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:bold;" ><?php echo $mfT[0]['deliver_add1']; ?></td>
              <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:normal;">Bank ABSA</td>
         	    <td>&nbsp;</td>
         </tr>
         <tr>
         	    <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:normal;" ><?php echo $mfT[0]['deliver_add2']; ?></td>
              <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid none solid; padding-left:5px; text-align:left; font-weight:normal;">Account No 4102351812</td>
         	    <td>&nbsp;</td>
         </tr>
         <tr>
         	    <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid solid solid; padding-left:5px; text-align:left; font-weight:normal;" ><?php echo $mfT[0]['deliver_add3']; ?></td>
              <td>&nbsp;</td>
              <td colspan="1" style="border-width:0.5px; border-style:none solid solid solid; padding-left:5px; text-align:left; font-weight:normal;">Branch Code 632005</td>
         	    <td>&nbsp;</td>
         </tr>
         <tr>
              <td colspan="5" style=border-bottom-style:none>&nbsp;</td>
         </tr>

     </table>     
     <table style="border-collapse:collapse; width:98%">         
         <tr style="height:20px">
         	    <td style="width:2%">&nbsp;</td>
              <td style="width:24%; border-width:0.5px; border-style:solid solid solid solid; text-align:center; font-weight:bold;" >Document Date</td>
              <td style="width:24%; border-width:0.5px; border-style:solid solid solid solid; text-align:center; font-weight:bold;" >Acc No</td>
              <td style="width:24%; border-width:0.5px; border-style:solid solid solid solid; text-align:center; font-weight:bold;" >Customer&nbsp;Reference</td>
              <td style="width:24%; border-width:0.5px; border-style:solid solid solid solid; text-align:center; font-weight:bold;" >Rep</td>
              <td style="width:2%">&nbsp;</td>
         </tr>          
         <tr style="height:20px">
         	    <td>&nbsp;</td>
              <td style="border-width:0.5px; border-style:none solid solid solid; text-align:center; font-weight:normal;" ><?php echo $mfT[0]["invoice_date"] ?></td>
              <td style="border-width:0.5px; border-style:none solid solid solid; text-align:center; font-weight:normal;" ><?php echo $mfT[0]["SFAccount"] ?></td>
              <td style="border-width:0.5px; border-style:none solid solid solid; text-align:center; font-weight:normal;" ><?php echo $mfT[0]["customer_order_number"] ?></td>
              <td style="border-width:0.5px; border-style:none solid solid solid;text-align:center; font-weight:normal;" >&nbsp;</td>
              <td>&nbsp;</td>
         </tr>
         <tr>
              <td colspan="5" style=border-bottom-style:none>&nbsp;</td>
         </tr>
     </table>     
     <table style="border-collapse:collapse; width:98%">
         <td style="width:2%;">&nbsp;</td>
         <td style="width:10%; border-width:1px; border-style:solid solid solid solid; padding-left:5px; text-align:left; font-weight:bold; ">Code</td>         
         <td style="width:40%; border-width:1px; border-style:solid solid solid solid; padding-left:5px; text-align:left; font-weight:bold;">Description</td>         
         <td style="width:10%; border-width:1px; border-style:solid solid solid solid;  padding-left:5px; text-align:center; font-weight:bold;">Qty</td>         
         <td style="width:10%; border-width:1px; border-style:solid solid solid solid;  padding-left:5px; text-align:center; font-weight:bold;">Price</td>         
         <td style="width:10%; border-width:1px; border-style:solid solid solid solid;  padding-left:5px; text-align:center; font-weight:bold;">Gross<br>Amt</></td>       
         <td style="width:6%; border-width:1px; border-style:solid solid solid solid;  padding-left:5px; text-align:center; font-weight:bold;">Discount<br>Amt</td>
         <td style="width:10%; border-width:1px; border-style:solid solid solid solid;  padding-left:5px; text-align:center; font-weight:bold;">Excl.<br>Amt</td>
         <td style="width:2%;">&nbsp;</td>
         <?php $totQ=0; $totLP=0; $totDV=0; $totCP=0; $totNett=0; $totVAT=0; $totTot = 0; $weightTot=0;
         foreach($mfT as $row) {
              $nettCP=0; 
              if($row["document_qty"] > 0) { ?>
                    <tr>
                       <td>&nbsp;</td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-left:2px; text-align:left; font-weight:normal; "><?php echo $row["product_code"]; ?></td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-left:2px; text-align:left; font-weight:normal; "><?php echo trim($row["product_description"]); ?></td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-right:5px; text-align:right; font-weight:normal; "><?php echo $row["document_qty"]; ?></td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-right:5px; text-align:right; font-weight:normal; "><?php echo number_format($row["net_price"],2, "."," "); ?></td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-right:5px; text-align:right; font-weight:normal; "><?php echo number_format($row["document_qty"]*$row["net_price"],2, "."," "); ?></td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-right:5px; text-align:right; font-weight:normal; ">&nbsp;</td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-right:5px; text-align:right; font-weight:normal; "><?php echo number_format($row["document_qty"]*$row["net_price"],2, "."," "); ?></td>
                       <td>&nbsp;</td>
                   </tr>
                  <tr>
                       <td>&nbsp;</td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                       <td style="border-width:0.5px; border-style:none solid none solid; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                       <td>&nbsp;</td>
                   </tr>
                         <?php $totVAT+= $row["vat_amount"];
                         $totTot+= $row["total"] ;
                         $totNett+=($row["document_qty"]*$row["net_price"]);
                  }
              } ?>
              <tr>
                   <td>&nbsp;</td>
                   <td style="border-width:0.5px; border-style:solid none none none; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                   <td style="border-width:0.5px; border-style:solid none none none; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                   <td colspan="2" style="border-width:0.5px; border-style:solid none none none; padding-right:10px; text-align:right; font-weight:normal; ">Currency:  ZAR</td>
                   <td colspan="2" style="border-width:0.5px; border-style:solid none none none; padding-right:5px; text-align:right; font-weight:bold; ">Sub Total</td>
                   <td style="border-width:0.5px; border-style:solid solid solid solid; padding-right:5px; text-align:right; font-weight:bold; "><?php echo number_format($totNett,2, "."," "); ?></td>
                   <td>&nbsp;</td>
               </tr>
              <tr>
                   <td>&nbsp;</td>
                   <td style="border-width:0.5px; border-style:none none none none; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                   <td style="border-width:0.5px; border-style:none none none none; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                   <td colspan="2" style="border-width:0.5px; border-style:none none none none; padding-right:5px; text-align:left; font-weight:bold; ">&nbsp;</td>
                   <td colspan="2" style="border-width:0.5px; border-style:none none none none; padding-right:5px; text-align:right; font-weight:bold; ">VAT 15%</td>
                   <td style="border-width:0.5px; border-style:solid solid solid solid; padding-right:5px; text-align:right; font-weight:bold; "><?php echo number_format($totVAT,2, "."," "); ?></td>
                   <td>&nbsp;</td>
               </tr>
              <tr>
                   <td>&nbsp;</td>
                   <td style="border-width:0.5px; border-style:none none none none; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                   <td style="border-width:0.5px; border-style:none none none none; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                   <td colspan="2" style="border-width:0.5px; border-style:none none none none; padding-left:2px; text-align:left; font-weight:bold; ">&nbsp;</td>
                   <td colspan="2" style="border-width:0.5px; border-style:none none none none; padding-right:5px; text-align:right; font-weight:bold; ">Total</td>
                   <td style="border-width:0.5px; border-style:solid solid solid solid; padding-right:5px; text-align:right; font-weight:bold; "><?php echo number_format($totVAT + $totNett,2, "."," "); ?></td>
                   <td>&nbsp;</td>
               </tr>               
     </table>
     <br>
     <table style="border-collapse:collapse; width:98%">
         <tr>
           <td style="width:2%;">&nbsp;</td>
           <td class="dc3" style="width:96%; text-align:center; font-weight:bold;" >Products marked with * are FSC Mix 70% certified<br><br>FSC Chain of Custody Code: SGSCH-COC-009743</td>
           <td style="width:2%;">&nbsp;</td>
        </tr>
         <tr>
           <td colspan="3">&nbsp;</td>
        <tr>
           <td style="width:2%;">&nbsp;</td>
           <td class="dc3" style="width:96%;" ><img alt="<?php echo ltrim(substr($mfT[0]['document_number'],0,8),'0'); ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo ltrim(substr($mfT[0]['document_number'],0,8),'0'); ?>&print=true" /></td>
           <td style="width:2%;">&nbsp;</td>
        </tr>
        <tr>
           <td style="width:2%;">&nbsp;</td>
           <td class="dc3" style="width:96%; text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
                                 <img src="<?php echo $ROOT.$PHPFOLDER; ?>images/Kwelanga_Solutions_Logo_smaller.jpg" style="width:60px; height:40px; float:right;" ></td>
           <td style="width:2%;">&nbsp;</td>
        </tr>
           <td style="width:2%;">&nbsp;</td>
           <td style="width:96%; text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
             <?php echo date('Y-m-d H:i:s'); ?>
           </td>
           <td style="width:2%;">&nbsp;</td>
         </tr> 
         <tr>
         	 <td style="width:2%;">&nbsp;</td>
           <td style="width:96%; text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
             <?php echo '412_document_invoice_version_bc'; ?>
           </td>
           <td style="width:2%;">&nbsp;</td>
         </tr>
     </table>
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
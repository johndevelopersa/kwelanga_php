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
$postnoitd = ((isset($_GET["NOOFDOCS"]))?$_GET["NOOFDOCS"]:"");

$dbConn->dbConnection();

$adminDAO = new AdministrationDAO($dbConn);
$hasRoleVP   = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId),ROLE_VIEW_PRICE);

if (isset($_POST['NOOFDOC'])) $postnoitd = ($_POST['NOOFDOC']);

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
          	
          	       td.topbox { border-collapse:collapse;
                               border-left-style:solid;  
                               border-left-color:red;  
                               border-left-width:0.5px;
                               border-right-style:solid; 
                               border-right-color:black; 
                               border-right-width:0.5px;
                               border-top-style:solid; 
                               border-top-color:black; 
                               border-top-width:0.5px; }
          	
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
                     }
 
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
          	
                  td.topboxleft { border-collapse:collapse;
                                  border-left-style:solid;  
                                  border-left-color:black;  
                                  border-left-width:0.5px;
                                  border-top-style:solid; 
                                  border-top-color:black; 
                                  border-top-width:0.5px; 
                                }
          	
                  td.topboxmiddle { border-collapse:collapse;
                                    border-top-style:solid; 
                                    border-top-color:black; 
                                    border-top-width:0.5px; 
                                  }     	
          	
                   td.topboxright { border-collapse:collapse;
                                    border-right-style:solid; 
                                    border-right-color:black; 
                                    border-right-width:0.5px;
                                    border-top-style:solid; 
                                    border-top-color:black; 
                                    border-top-width:0.5px; 
                                  }     	         	
 
                   td.topboxleft  { border-collapse:collapse;
                                  border-left-style:solid;  
                                  border-left-color:black;  
                                  border-left-width:0.5px;
                                  border-top-style:solid; 
                                  border-top-color:black; 
                                  border-top-width:0.5px; 
                                  }
          	
                   td.leftbox     { border-collapse:collapse;
                                    border-left-style:solid; 
                                    border-left-color:black; 
                                    border-left-width:0.5px; 
                                  }     	
          	
                   td.rightbox    { border-collapse:collapse;
                                    border-right-style:solid; 
                                    border-right-color:black; 
                                    border-right-width:0.5px;
                                  }     	         	
                   td.middlebox    { border-collapse:collapse;
                   	                 border-none;       
 
 
 
 
 
 
 
 
 
          	        
                   td.dc {background-color:white; 
                   color:black; 
                   font-weight:bold; 
                   font-size:16px;}

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
                   border-style:solid solid solid solid; border-color:black; border-width:1px; } 
                   
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

       <table style="width:100%; border-collapse: collapse; ">
           <tr>
           	   <td class="topboxleft" style="width:2%;  >&nbsp;</td>
           	   <td class="topboxleft" style="width:13%; >&nbsp;</td>
           	   <td class="topboxleft" style="width:13%; >&nbsp;</td>
           	   <td class="topboxleft" style="width:13%; >&nbsp;</td>
           	   <td class="topboxleft" style="width:13%; >&nbsp;</td>
           	   <td class="topboxleft" style="width:13%; >&nbsp;</td>
           	   <td class="topboxleft" style="width:13%; >&nbsp;</td>
           	   <td class="topboxleft" style="width:13%; >&nbsp;</td>
           	   <td class="topboxleft" style="width:7%;  >&nbsp;</td>
           </tr>
           <tr>
               <td class="topboxleft"   colspan="1" >&nbsp;</td>
               <td class="topboxmiddle" colspan="7" style="text-align:left; font-weight:bold;">Customer Transaction Record</td>        
               <td class="topboxright"  colspan="1" >&nbsp;</td>           	
           </tr>
           <tr>
           	   <td colspan="1" style="border-left: 1px solid black;">&nbsp;</td>
           	   <td colspan="7" style="text-align: left; font-weight:bold;;">Customer Details</td>        
           	   <td colspan="1" style="border-right: 1px solid black;">&nbsp;</td>  
           </tr>
 <?php
           $addstor = "";
           $headerstor = "";
           $docloop = 0;
           
//    print_r($mfCSD);
           foreach ($mfCSD as $row) {
             if($docloop < $postnoitd ) {        	
               if ($addstor == "") {
?>               
                  <tr>
                     <td class="topboxleft" colspan="1" >&nbsp;</td>
                     <td class="topboxmiddle" colspan="7" ><?php echo $row['deliver_name'];?></td>
                     <td class="topboxright" colspan="1" >&nbsp;</td> 
                  </tr>
                  <tr>
                     <td class="leftbox" colspan="1">&nbsp;</td>
                     <td class="middlebox" colspan="7"><?php echo $row['deliver_add1'];?></td>
                     <td class="rightbox" colspan="1">&nbsp;</td> 
                  </tr>
                  <tr>
                     <td class="leftbox" colspan="1">&nbsp;</td>
                     <td class="middlebox" colspan="7"><?php echo $row['deliver_add2'];?></td>
                     <td class="rightbox" colspan="1">&nbsp;</td> 
                  </tr>
                  <tr>
                     <td class="leftbox" colspan="1">&nbsp;</td>
                     <td class="middlebox" colspan="7"><?php echo $row['deliver_add3'];?></td>
                     <td class="rightbox"  colspan="1">&nbsp;</td> 
                  </tr>
                  <tr>
                     <td class="topboxleft" colspan="1">&nbsp;</td>
                     <td class="topboxmiddle" colspan="2" style="text-align:left; font-weight:bold;">Balances Outstanding as at:</td>
                     <td class="topboxmiddle" colspan="5" style="text-align:left;" font-weight:bold; >2017-09-30</td>
                     <td class="topboxright" colspan="1">&nbsp;</td> 
                  </tr>
                  <tr>
                     <td class="topboxleft" colspan="1">&nbsp;</td>
                     <td class="topboxmiddle" colspan="1" style="text-align:left; font-weight:bold;">&nbsp;&nbsp;Total Due</td>
                     <td class="topboxleft" colspan="1" style="text-align:left; font-weight:bold;">&nbsp;&nbsp;Current</td>
                     <td class="topboxleft" colspan="1" style="text-align:left; font-weight:bold;">&nbsp;&nbsp;30 Days</td>
                     <td class="topboxleft" colspan="1" style="text-align:left; font-weight:bold;">&nbsp;&nbsp;60 Days</td>
                     <td class="topboxleft" colspan="1" style="text-align:left; font-weight:bold;">&nbsp;&nbsp;90 Days</td>
                     <td class="topboxright" colspan="3" style="text-align:left;font-weight:bold;"&nbsp;</td> 
                  </tr>
                  <tr>

                     <td class="leftbox" colspan="1">&nbsp;</td>
                     <td class="middlebox" colspan="1" style="text-align:left;">&nbsp;&nbsp;<?php echo trim(number_format(round($row['td'],2),2,"."," "));?></td>
                     <td class="leftbox" colspan="1" style="text-align:left;">&nbsp;&nbsp;<?php echo trim(number_format(round($row['curr'],2),2,"."," "));?></td>
                     <td class="leftbox" colspan="1" style="text-align:left;">&nbsp;&nbsp;<?php echo trim(number_format(round($row['30d'],2),2,"."," "));?></td>
                     <td class="leftbox" colspan="1" style="text-align:left;">&nbsp;&nbsp;<?php echo trim(number_format(round($row['60d'],2),2,"."," "));?></td>
                     <td class="leftbox" colspan="1" style="text-align:left;">&nbsp;&nbsp;<?php echo trim(number_format(round($row['90d'],2),2,"."," "));?></td>
                     <td class="rightbox" colspan="3" style="text-align:left;"&nbsp;</td> 
                  </tr>
                  <tr>
                     <td class="topboxleft" colspan="1">&nbsp;</td>
                     <td class="topboxmiddle" colspan="1" style="text-align:left; font-weight:bold;">Order Frequency</td>
                     <td class="topboxleft"   colspan="5" style="text-align:left; font-weight:bold;">&nbsp;&nbsp;Notes</td>
                     <td class="topboxright" colspan="2" style="text-align:right;"&nbsp;</td> 
                  </tr>
                  <tr>
                     <td class="topboxleft" colspan="1">&nbsp;</td>
                     <td class="topboxmiddle" colspan="1" style="text-align:left; font-weight:bold;">Invoice No.</td>
                     <td class="topboxleft" colspan="1" style="text-align:left; font-weight:bold;">&nbsp;&nbsp;Doc Type</td>
                     <td class="topboxleft" colspan="1" style="text-align:left; font-weight:bold;">&nbsp;&nbsp;Invoice Date</td>
                     <td class="topboxmiddle" colspan="1" style="text-align:left; font-weight:bold;">&nbsp;</td>
                     <td class="topboxleft" colspan="1" style="text-align:right; font-weight:bold;">&nbsp;&nbsp;Quantity&nbsp;&nbsp;</td>
                     <td class="topboxleft" colspan="1" style="text-align:right; font-weight:bold;">&nbsp;</td>
                     <td class="topboxleft" colspan="1" style="text-align:right; font-weight:bold;">&nbsp;&nbsp;Total&nbsp;&nbsp;</td>
                     <td class="topboxright" colspan="1" style="text-align:left;"&nbsp;</td> 
                 </tr>
 
<?php
                 $addstor = "1"; 
               }
               if ($headerstor <> $row['document_number']) {
 
?>
                 <tr>
                     <td class="topboxleft" colspan="1">&nbsp;</td>
                     <td class="topboxmiddle" colspan="1" style="text-align:left;">&nbsp;&nbsp;<?php echo trim(substr($row['document_number'],2,6));?></td>
                     <td class="topboxleft" colspan="1" style="text-align:left;">&nbsp;&nbsp;<?php echo trim($row['DocType']);?></td>
                     <td class="topboxleft" colspan="1" style="text-align:left;">&nbsp;&nbsp;<?php echo $row['invoice_date'];?></td>
                     <td class="topboxmiddle" colspan="1" style="text-align:left;">&nbsp;</td>
                     <td class="topboxleft" colspan="1" style="text-align:right;"><?php echo trim(number_format($row['cases'],0,","," "));?>&nbsp;&nbsp;</td>
                     <td class="topboxleft" colspan="1" style="text-align:left; font-weight:bold;">&nbsp;</td>
                     <td class="topboxleft" colspan="1" style="text-align:right;"><?php echo trim(number_format(round($row['invoice_total'],2),2,"."," "));?>&nbsp;&nbsp;</td>
                     <td class="topboxright" colspan="1" style="text-align:left;"&nbsp;</td> 
                 </tr>
                  <tr>
                     <td class="topboxleft" colspan="1">&nbsp;</td>
                     <td class="topboxmiddle" colspan="1" style="text-align:left; font-weight:bold;">&nbsp;</td>
                     <td class="topboxleft" colspan="1" style="text-align:left; font-weight:bold;">&nbsp;&nbsp;Product Code</td>
                     <td class="topboxleft" colspan="2" style="text-align:left; font-weight:bold;">&nbsp;&nbsp;Product Description</td>
                     <td class="topboxleft" colspan="1" style="text-align:right; font-weight:bold;">Quantity&nbsp;&nbsp;</td>
                     <td class="topboxleft" colspan="1" style="text-align:right; font-weight:bold;">Price&nbsp;&nbsp;</td>
                     <td class="topboxleft" colspan="1" style="text-align:right; font-weight:bold;">Total&nbsp;&nbsp;</td>
                     <td class="topboxright" colspan="1" style="text-align:left;"&nbsp;</td> 
                 </tr>
<?php
            	      $headerstor = $row['document_number'];
            	      $docloop++;
               }
           } 
            
           if ( $headerstor == $row['document_number'] ) {
?>
                <tr>
                     <td class="leftbox" colspan="1">&nbsp;</td>
                     <td class="middlebox" colspan="1" style="text-align:left;">&nbsp;</td>
                     <td class="leftbox" colspan="1" style="text-align:left;">&nbsp;&nbsp;<?php echo $row['product_code'];?></td>
                     <td class="leftbox" colspan="2" style="text-align:left;">&nbsp;&nbsp;<?php echo $row['product_description'];?></td>
                     <td class="leftbox" colspan="1" style="text-align:right;">&nbsp;&nbsp;<?php echo trim(number_format(round($row['document_qty'],0),0,"."," "));?>&nbsp;&nbsp;</td>
                     <td class="leftbox" colspan="1" style="text-align:right;"><?php echo trim(number_format(round($row['net_price'],2),2,"."," "));?>&nbsp;&nbsp;</td>
                     <td class="leftbox" colspan="1" style="text-align:right;"><?php echo trim(number_format(round($row['total'],2),2,"."," "));?>&nbsp;&nbsp;</td>
                     <td class="rightbox" colspan="1" style="text-align:right;"&nbsp;</td>                 	
                </tr>
<?php   	    
            }
  
        }
?>
              <tr>
              	<td colspan="9" style="border-top: 1px solid black; border-collapse: collapse ">&nbsp;</td>
              </tr>               
       </table>
    </body>
 </html>

<?php
$dbConn->dbClose();
?>


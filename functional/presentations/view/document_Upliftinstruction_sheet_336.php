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

$transactionDAO = new TransactionDAO($dbConn);
$docNo = $transactionDAO->getUpliftDocumentNumber(mysqli_real_escape_string($dbConn->connection, $docmastId));

$transactionDAO = new TransactionDAO($dbConn);
$mfT = $transactionDAO->getUpliftDetailsToUpdate($principalId,$docNo[0]['document_number']);
// print_r($mfT);
?>

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
          table.grid {
            border-collapse:collapse;
          }
          table.grid td, table.grid th {
            border:1px solid #aaa;
          }
    table.grid th {background:#efefef;}
    .bordUnderline{border-bottom:1px solid #333;height:30px;}
    
    img {
    float: right;
    margin: 0 0 10px 10px;
		}
		
		<?php 
		if (substr($outputTyp,0,3) == "pdf") { ?>
          td.dc   {font-weight:normal; 
 	                  border-collapse: collapse;
                    border-style:solid;
                    border-width:1px;
                    height:25px
                    }
		      
          td.dh    {text-align:left; 
                    font-weight:bold; 
                    font-size:40px;			     
                    }
                    
          td.topr {text-align:center;
                   font-weight:bold; 
                   font-size:30px;
                   border-right-style:solid;
                   border-left-style:solid;
                   border-top-style:solid;
                   border-width:10px;
                   border-collapse:collapse; 
                   }     	
          td.detr {text-align:center;
                   font-weight:normal; 
                   font-size:25px;			    
                   border-right-style:solid;
                   border-left-style:solid;
                   border-width:1px; 
                   border-collapse:collapse; 	
                   }
          td.detrs {text-align:center;
                    font-weight:normal; 
                    font-size:30px;			    
                    border-right-style:solid;
                    border-left-style:solid;
                    border-width:1px; 
                    border-collapse: collapse; 	
                    }
          td.foot1 {text-align:right; 
                    border-top-style:solid;
                    border-left-style:solid; 
                    border-right-style:solid;  
                    border-width:2px; 
                    font-weight:bold; 
                    font-size:30px;
                    }
                    
         td.foot1b {border-top-style:solid; 
          	        border-left-style:none; 
          	        border-right-style:none;
                    border-width:200px;
                    }                    
          td.foot1s{text-align:right; 
                    border-bottom-style:solid;
                    border-left-style:solid; 
                    border-right-style:solid;  
                    border-width:2px; 
                    font-weight:bold; 
                    font-size:30px;
                    }	
     <?php 
     }  else { 
     ?>	
         td.dc   {font-weight:normal; 
 	                 border-collapse: collapse;
                    border-style:solid;
                    border-width:0.05px;
                    height:15px
                    }
		      
          td.dh    {text-align:left; 
                    font-weight:bold; 
                    font-size:15px;			     
                    }
          td.topr {text-align:center;
                   font-weight:bold; 
                   font-size:12px;			    
                   border-style:solid;
                   border-width:0.05px; 
                   border-collapse: collapse; 	
                   }     	

          td.detr {text-align:center;
                   font-weight:normal; 
                   font-size:12px;
                   border-right-style:solid;
                   border-left-style:solid;
                   border-top-style:solid;
                   border-width:0.05px; 
                   border-collapse: collapse; 	
                   }

          td.detrs {text-align:center;
                    font-weight:normal; 
                    font-size:12px;			    
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
                    font-size:12px;
                    }
         td.foot1b {border-top-style:solid; 
          	        border-left-style:none; 
          	        border-right-style:none;
                    border-width:0.05px; 
                    }                    
          td.foot1s{text-align:right; 
                    border-bottom-style:solid;
                    border-left-style:solid; 
                    border-right-style:solid;  
                    border-width:0.05px; 
                    font-weight:bold; 
                    font-size:12pxem;
                    }	
     <?php 
     }                  
     ?>	              
 
</STYLE>

<script type='text/javascript' language='javascript' src='<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js'></script>
</HEAD>
<BODY style="font-family:Verdana,Arial,Helvetica,sans-serif;margin:0px;padding:0px;">
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
       <TITLE>&nbsp;</TITLE>
    </div>

    <table style="border-collapse:collapse; width:100%;">        
       <tr>
          <td style="width:1%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:5%;">&nbsp;</td>
          <td style="width:4%;">&nbsp;</td>
       </tr>
       <tr>
        	<td class="dc0">&nbsp;</td>
        	<td class="dh" colspan='15'>Iram Uplift Instructions Form</td>
        	<td class="dh" colspan='2' style='text-align:right;'>Date</td>
        	<td class="dh" colspan='2' style='text-align:right;'><?php echo trim($mfT[0]['order_date']); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
       </tr> 
       <tr>
        	<td class="dc0">&nbsp;</td>
        	<td class="dh" colspan='3' style='text-align:left';>&nbsp;</td>
        	<td class="dh" colspan='7' style='text-align:left';>&nbsp;</td>
        	<td class="dh" colspan='6' style='text-align:right';>&nbsp;</td>
        	<td class="dh" colspan='4' style='text-align:left';>&nbsp;</td>
       </tr> 
       <tr>
        	<td class="dc0">&nbsp;</td>
        	<td class="dh" colspan='4' style='text-align:left;'>Vendor</td>
        	<td class="dh" colspan='6' style='text-align:left;'><?php echo $mfT[0]['Principal']; ?></td>
        	<td class="dh" colspan='6' style='text-align:right';>Uplift Number</td>
        	<td class="dh" colspan='4' style='text-align:right;'><?php echo ltrim($mfT[0]['document_number'],'0'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
       </tr> 
       <tr>
        	<td class="dc0" colspan='20'>&nbsp;</td>
       </tr>
       <tr>
        	<td class="dc0">&nbsp;</td>
        	<td class="dh" colspan='3' style='text-align:left';>Store</td>
        	<td class="dh" colspan='7' style='text-align:left';><?php echo trim($mfT[0]['deliver_name']); ?></td>
        	<td class="dh" colspan='7' style='text-align:right';>Reference Number</td>
        	<td class="dh" colspan='2' style='text-align:left';>   _____________________________</td>
        	<td class="dc0">&nbsp;</td>
       </tr> 
    </table>

      <table style= "border-collapse:collapse; width:100%;">
          <tr>
            <td style="width:1%;">&nbsp;</td>
            <td class="topr" style="width:14%; text-align:left;">Prod<br>Code</td>
            <td class="topr" style="width:9%;">Bar<br>Code</td>
            <td class="topr" style="width:28%;">Product</td>
            <td class="topr" style="width:7%;">Uplift<br>Qty</td>
            <td class="topr" style="width:8%;">Uplifted</td>
            <td class="topr" style="width:8%;">Display</td>
            <td class="topr" style="width:8%;">Store<br>Refused</td>
            <td class="topr" style="width:8%;">Not<br>Found</td>
            <td class="topr" style="width:8%;">Damages</td>
            <td style="width:1%;">&nbsp;</td>
          </tr>
          <?php
          // print_r($mfT);
          $totQ=0;
          
          $pline = 0;
          foreach($mfT as $row) {	?>
              <tr>
                 <td class="dc0"  style="height:25px";>&nbsp;</td>   
                 <td class="detr" style="height:25px; text-align:left;"><?php echo $row["product_code"]?></td>      	
                 <td class="detr" style="height:25px; text-align:left;"><?php echo $row["outercasing_gtin"]?></td>      	
                 <td class="detr" style="height:25px";><?php echo $row["product_description"]?></td>
                 <td class="detr" style="height:25px";><?php echo $row["ordered_qty"]?></td>      	
                 <td class="detr" style="height:25px";>&nbsp;</td>
                 <td class="detr" style="height:25px";>&nbsp;</td>    
                 <td class="detr" style="height:25px";>&nbsp;</td>      	
                 <td class="detr" style="height:25px";>&nbsp;</td>
                 <td class="detr" style="height:25px";>&nbsp;</td>
                 <td class="dc0"  style="height:25px";>&nbsp;</td>
              </tr>                     
          <?php 
           $pline++; 
           if ($pline== 22) { $pline = 0; ?>
           	   <tr>
           	   	  <td style="page-break-after: always";>Page</td>
           	   </tr>
               
            <?php 
          }
          } ?> 
          <tr>
          	<td>&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>             
            <td class="foot1"  >Total&nbsp;&nbsp;&nbsp;</td>          
            <td class="foot1"  style='text-align:center';><?php echo $row['cases']; ?></td>          
            <td class="foot1b" >&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>
            <td>&nbsp;</td>          
          </tr>
          <tr>

          	<td>&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>             
            <td class="foot1"  >Total Number of Boxes&nbsp;&nbsp;&nbsp;</td>          
            <td class="foot1"  style='text-align:center';>&nbsp;</td>          
            <td class="foot1b" >&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>
            <td class="foot1b" >&nbsp;</td>
            <td>&nbsp;</td>          
         <tr>
         <tr>
            <td style="width:1%; ">&nbsp;</td>
            <td style="width:14%;">&nbsp;</td>
            <td style="width:9%;" >&nbsp;</td>   
            <td style="width:28%;">&nbsp;</td>   
            <td style="width:7%;" >&nbsp;</td>   
            <td style="width:8%;" >&nbsp;</td>   
            <td style="width:8%;" >&nbsp;</td>   
            <td style="width:8%;" >&nbsp;</td>   
            <td style="width:8%;" >&nbsp;</td>   
            <td style="width:8%;" >&nbsp;</td>   
            <td style="width:1%;" >&nbsp;</td>
         </tr>
         <tr>
           <td colspan="11">&nbsp;</td>
         </tr>
         <tr>
           <td colspan="11">&nbsp;</td>
         </tr>
         <tr>
           <td          <tr>
           <td colspan="7">&nbsp;</td>
           <td colspan="4" rowspan="2"><img src="<?php echo $ROOT.$PHPFOLDER; ?>images/kwelanga1.gif" style="width:60px; height:24px; float:right;" ></td>
         </tr>
         <tr>
           <td colspan="7">&nbsp;</td>
         </tr>
         <tr>
         	 <td colspan="8">&nbsp;</td>
           <td colspan="3"; style="text-align:right; color:grey; font-weight:normal; font-size:3px;"><?php echo date('Y-m-d H:i:s'); ?></td>
         </tr>
         <tr>
         	 <td colspan="8">&nbsp;</td>
           <td colspan="3"; style="text-align:right; color:grey; font-weight:normal; font-size:3px;"><?php echo 'document_Upliftinstruction_sheet_336'; ?></td>
         </tr>
      </table>  
</BODY>
</HTML>
<?php
$dbConn->dbClose();
?>
<script type="text/javascript">
  function emailDoc() {  
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfSelf53';?>&PSMUID=<?php echo $mfT[0]['psmuid'];?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&CUSTNAME=<?php echo $custName ;?>&TEMPLAT=<?php echo 'document_Upliftinstruction_sheet_336.php';?>";
  	}
  function emailDoc2() {
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfCust53';?>&PSMUID=<?php echo $mfT[0]['psmuid']; ?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&CUSTNAME=<?php echo $custName ;?>&TEMPLAT=<?php echo 'document_Upliftinstruction_sheet_336.php';?>";
   }  
</script> 

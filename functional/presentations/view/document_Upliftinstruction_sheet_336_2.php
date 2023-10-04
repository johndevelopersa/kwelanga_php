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
$hasRoleVP   = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId),ROLE_VIEW_PRICE);

$transactionDAO = new TransactionDAO($dbConn);
$mfT = $transactionDAO->getDocumentWithDetailIgnorePermissionsItem(mysqli_real_escape_string($dbConn->connection, $docmastId));
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
    table.grid
    {
      border-collapse:collapse;
    }
    table.grid, table.grid th
    {
    border:1px solid #aaa;
    }
    table.grid th {background:#efefef;}
    .bordUnderline{border-bottom:1px solid #333;height:30px;}
    
    img {
    float: right;
    margin: 0 0 10px 10px;
		}
		
		td.dc   {font-weight:normal; 
			       border-collapse: collapse;
			       border-style:solid;
			       border-width:0.05px;
			       height:15px
		        }
		      
		td.dh   {text-align:left; 
			       font-weight:bold; 
			       font-size:1.1em;			     
		        }

		td.dhl  {text-align:right; 
			       font-weight:bold; 
			       font-size:1.1em;			     
		        }
		td.dd   {text-align:left; 
			       font-weight:normal; 
			       font-size:1.0em;			     
		        }
		td.ddl  {text-align:right; 
			       font-weight:normal; 
             font-size:1.0em;			     
		        }

		td.dds  {text-align:left; 
			       font-weight:normal; 
			       font-size:1.0em;			     
		        }
		td.ddls {text-align:right; 
			       font-weight:normal; 
             font-size:1.3em;			     
		        }

    td.topr {text-align:center;
			       font-weight:bold; 
			       font-size:1.0em;			    
			       border-style:solid;
			       border-width:0.05px; 
			       border-collapse: collapse; 	
            }
    td.detr {text-align:center;
			       font-weight:normal; 
			       font-size:0.9em;			    
			       border-right-style:solid;
			       border-left-style:solid;
			       border-top-style:solid;
			       border-width:0.05px; 
			       border-collapse: collapse; 	
            }

    td.detrs {text-align:center;
			       font-weight:normal; 
			       font-size:0.9em;			    
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
    	        font-size:1.0em;
    	        }
    td.foot1s{text-align:right; 
    	        border-bottom-style:solid;
    	        border-left-style:solid; 
    	        border-right-style:solid;  
    	        border-width:0.05px; 
    	        font-weight:bold; 
    	        font-size:1.1em;
    	        }	
    	        
   img {  display: block;
          margin-left: auto;
          margin-right: auto;
   } 	        
    	        
</STYLE>

<script type='text/javascript' language='javascript' src='<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js'></script>
</HEAD>

<BODY style="font-family:Verdana,Arial,Helvetica,sans-serif;margin:0px;padding:0px;">

<!-- email -->
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
<TITLE>Trip Sheet - View</TITLE>
</div>

<table style= 'border-collapse:collapse; width:95%'>        
	<tr>
    <td class="dc0" style='width:1%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
    <td class="dc0" style='width:5%;'>&nbsp;</td>
  </tr>
  <tr>
  	<td class="dc0" colspan='1'>&nbsp;</td>
  	<td class="dh"  colspan='10'>Iram Uplift Instructions Form</td>
    <td class="dh"  colspan='8' style='text-align:right';>&nbsp;</td>
  </tr> 
  <tr>
  	<td class="dc0" colspan='20'>&nbsp;</td>
  </tr>
  <tr>
  	<td class="dc0">&nbsp;</td>
  	<td class="dh" colspan='4' style='text-align:left';>Vendor</td>
  	<td class="dh" colspan='12' style='text-align:left';><?php echo trim($mfT[0]['principal_name']); ?></td>
  </tr>   
  <tr>
  	<td class="dc0" colspan='20'>&nbsp;</td>
  </tr>
  <tr>
    <td class="dc0">&nbsp;</td>
  	<td class="dh" colspan='4' style='text-align:left';>Uplift Number</td>
  	<td class="dh" colspan='4' style='text-align:left';><?php echo ltrim($mfT[0]['document_number'],'0'); ?></td>
 	  <td class="dh" colspan='2' style='text-align:right';>&nbsp;</td>
 	  <td class="dh" colspan='4' style='text-align:right';>Date</td>
  	<td class="dh" colspan='5' style='text-align:right';><?php echo $mfT[0]['order_date']; ?></td>  
  </tr>   
  <tr>
    <td class="dc0">&nbsp;</td>
  	<td class="dh" colspan='4' style='text-align:left';>Store</td>
  	<td class="dh" colspan='4' style='text-align:left';><?php echo $mfT[0]['store_name']; ?></td>
 	  <td class="dh" colspan='2' style='text-align:right';>&nbsp;</td>
 	  <td class="dh" colspan='4' style='text-align:right';>Site</td>
  	<td class="dh" colspan='5' style='text-align:right';><?php echo $mfT[0]['old_account']; ?></td>  
  </tr>  
  <tr>
  	<td class="dc0" colspan='20'>&nbsp;</td>
  </tr>  
</table>
<table style= 'border-collapse:collapse; width:95%'>
    <tr>
      <td class="dc0" style='width:1%';>&nbsp;</td>
      <td class="topr" style='width:9%; text-align:left;'>Product Code</td>
      <td class="topr" style='width:14%';>Bar Code</td>
      <td class="topr" style='width:40%;'>Product</td>
      <td class="topr" style='width:7%;'>Quantity to Uplift</td>
      <td class="topr" style='width:7%;'>Actual Quantity</td>
      <td class="topr" style='width:7%;'>Damaged</td>
      <td class="topr" style='width:7%;'>Dispay</td>
      <td class="topr" style='width:7%;'>Not Found</td>
      <td class="dc0"  style='width:1%';>&nbsp;</td>
    </TR>
<?php
// print_r($mfT);
$totQ=0;

$document_type = $mfT[0]['document_type_uid'];

foreach($mfT as $row) {	
?>
  <tr>
     <td class="dc0">&nbsp;</td>   
     <td class='detr' style='text-align:left;'>&nbsp<?php echo $row['product_code']?></td>      	
     <td class='detr' style='text-align:left;'>&nbsp<?php echo $row['ean_code']?></td>      	
     <td class="detr" style='text-align:left;'>&nbsp<?php echo $row['product_description']?></td>
     <td class='detr' style='text-align:right;'><?php echo $row['ordered_qty']?>&nbsp;&nbsp;</td>      	
     <td class="detr">&nbsp;</td>
     <td class='detr'>&nbsp;</td>      	
     <td class="detr">&nbsp;</td>
     <td class="detr">&nbsp;</td>
     <td class="dc0">&nbsp;</td>

  </tr>
  <tr>
     <td class="dc0">&nbsp;</td>          
     <td class="detrs">&nbsp;</td>          
     <td class="detrs">&nbsp;</td>          
     <td class="detrs">&nbsp;</td>         
     <td class="detrs">&nbsp;</td>           	
     <td class="detrs">&nbsp;</td>          
     <td class="detrs">&nbsp;</td>          
     <td class="detrs">&nbsp;</td>          
     <td class="detrs">&nbsp;</td>         
     <td class="dc0">&nbsp;</td>          
   </tr>
<?php } ?> 
   <tr>
     <td>&nbsp;</td>          
     <td class="foot1" colspan="3" >Total&nbsp;&nbsp;&nbsp;</td>          
     <td class="foot1" style='text-align:right';><?php echo $row['cases']; ?>&nbsp;&nbsp;</td>          
     <td class="foot1" colspan="4" >&nbsp;&nbsp;&nbsp;</td>         
   </tr>
  <tr>
    <td>&nbsp;</td>          
    <td class="foot1s" colspan="3" >&nbsp;</td>          
    <td class="foot1s" >&nbsp;</td>          
    <td class="foot1s" colspan="4" >&nbsp;&nbsp;&nbsp;</td> 
  </tr>


	</BODY>
</HTML>

<?php
$dbConn->dbClose();
?>

<script type="text/javascript">
  function emailDoc() {
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo $docmastId;?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfSelf'.trim($document_type). substr($mfT[0]['document_number'],0,8);?>&PSMUID=<?php echo ('');?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&TEMPLAT=<?php echo 'document_Upliftinstruction_sheet_336_2.php';?>";
  }
</script> 

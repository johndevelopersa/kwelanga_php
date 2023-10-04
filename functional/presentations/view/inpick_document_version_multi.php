<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."elements/SignatureArea.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

$userId = ((isset($_GET["USERID"]))?$_GET["USERID"]:"");
$principalId = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:"");
$docmastId = ((isset($_GET["DOCMASTID"]))?$_GET["DOCMASTID"]:"");
$outputTyp = ((isset($_GET["OUTPUTTYP"]))?$_GET["OUTPUTTYP"]:"");

$dbConn = new dbConnect();
$dbConn->dbConnection();

$adminDAO = new AdministrationDAO($dbConn);

?>

<!DOCTYPE html>
<html>
   <title>Picking Lists by Store</title>
          <link   href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_template.css' rel='stylesheet' type='text/css'>
      <head>
          <style type="text/css">

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
                  <div style="clear:both;"></div>
               </div>
             </div><!-- HIDE THIS PRINT AREA : END /--->
            </td>
          </tr>
         </table>
       </div>
       <?php
       $docmastList = explode(",",$docmastId);
       
        foreach($docmastList as $row) {
             $transactionDAO = new TransactionDAO($dbConn);
             $mfT = $transactionDAO->getDocumentWithDetailIgnorePermissionsItem(mysqli_real_escape_string($dbConn->connection, $row));
             // print_r($mfT);
             ?>
                 <table style="border-collapse:collapse; width:80%">
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc" colspan="4" style="text-align:left;">Picking List</td>
                        <td class="dc" colspan="5" style="text-align:left;">&nbsp;</td>
                        <td style="text-align:left;">&nbsp;</td>
                     </tr>
                     <tr>
                        <td style="width:  2%; text-align:left;">&nbsp;</td>
                        <td style="width: 10%; text-align:left;">&nbsp;</td>
                        <td style="width: 10%; text-align:left;">&nbsp;</td>
                        <td style="width: 10%; text-align:left;">&nbsp;</td>         	
                        <td style="width: 10%; text-align:left;">&nbsp;</td>
                        <td style="width: 10%; text-align:left;">&nbsp;</td>         	
                        <td style="width: 10%; text-align:left;">&nbsp;</td>
                        <td style="width: 10%; text-align:left;">&nbsp;</td>         	
                        <td style="width: 10%; text-align:left;">&nbsp;</td>
                        <td style="width: 16%; text-align:left;">&nbsp;</td>
                        <td style="width:  2%; text-align:left;">&nbsp;</td>
                     </tr>
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc2" colspan="4" style="text-align:left;"><?php echo $mfT[0]['depot_name'];?></td>
                        <td class="dc2" colspan="5" style=" text-align:right;"><img alt="<?php echo $mfT[0]['principal_uid']  . ' - ' .  ltrim(substr($mfT[0]['document_number'],0,8),'0'); ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $mfT[0]['principal_uid'] . ' - ' . ltrim(substr($mfT[0]['document_number'],0,8),'0'); ?>&print=true" /></td>
                        <td style="text-align:left;">&nbsp;</td>
                     </tr>
                     <tr>
                        <td colspan="11" style="text-align:left;">&nbsp;</td>
                     </tr>
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="1" style="text-align:left;">Principal</td>
                        <td class="dc3"  colspan="3" style="text-align:left;"><?php echo $mfT[0]["principal_name"]; ?></td>
                        <td colspan="7" style="text-align:left;">&nbsp;</td>
                     </tr> 
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="1" style="text-align:left;">Order Date</td>
                        <td class="dc3"  colspan="4" style="text-align:left;"><?php echo $mfT[0]["order_date"]; ?></td>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="1" style="text-align:right; padding:1px;">Picking&nbsp;Date</td>
                        <td class="dc3"  colspan="2" style="text-align:right; padding:1px;"><?php echo date("Y-m-d") ; ?></td>
                        <td style="text-align:left;">&nbsp;</td>
                     </tr>             
                     <tr>
                        <td colspan="11" style="text-align:left;">&nbsp;</td>
                     </tr>
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3" colspan="5" style="text-align:left;"><?php echo $mfT[0]['store_name']; ?></td>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="1" style="text-align:right; padding:1px;">Document&nbsp;No</td>
                        <td class="dc3 " colspan="2" style="text-align:right; padding:1px;"><?php echo ltrim($mfT[0]['document_number'],'0'); ?></td>
                        <td colspan="1" style="text-align:left;">&nbsp;</td>
                     </tr>             
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3" colspan="5" style="text-align:left;"><?php echo $mfT[0]['deliver_add1']; ?></td>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3" colspan="4" style="text-align:right;">&nbsp;</td>
                        <td colspan="1" style="text-align:left;">&nbsp;</td>
                     </tr>        
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3" colspan="5" style="text-align:left;"><?php echo $mfT[0]['deliver_add2']; ?></td>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="1" style="text-align:right; padding:1px;">Customer&nbsp;Reference</td>
                        <td class="dc3" colspan="2" style="text-align:right; padding:1px;"><?php echo $mfT[0]["customer_order_number"] ?></td>
                        <td colspan="1" style="text-align:left;">&nbsp;</td>
                     </tr>               
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3" colspan="5" style="text-align:left;"><?php echo $mfT[0]['deliver_add3']; ?></td>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="1" style="text-align:right; padding:1px;">&nbsp;</td>
                        <td class="dc3h" colspan="2" style="text-align:right;">&nbsp;</td>
                        <td colspan="1" style="text-align:left;">&nbsp;</td>
                     </tr>                 
                     <?php if ($mfT[0]["due_delivery_date"] <> "0000-00-00") { ?>
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="1" style="text-align:left; padding:1px;">Expiry&nbsp;Date</td>
                        <td class="dc3 " colspan="3" style="text-align:left;"><?php echo $mfT[0]["due_delivery_date"] ?></td>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3"  colspan="1" style="text-align:right;">&nbsp;</td>
                        <td class="dc3"  colspan="3" style="text-align:right;">&nbsp;</td>
                        <td colspan="1" style="text-align:left;">&nbsp;</td>
                     </tr>
                   <?php } ?> 
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="1" style="text-align:left; padding:1px;">Special&nbsp;Instructions</td>
                        <td class="dc3 " colspan="4" style="text-align:left;"><?php echo $mfT[0]["delivery_instructions"] ?></td>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="1" style="text-align:right; padding:1px;">Area</td>
                        <td class="dc3"  colspan="2" style="text-align:right; padding:1px;"><?php echo $mfT[0]['area']; ?></td>
                        <td colspan="1" style="text-align:left;">&nbsp;</td>
                     </tr>
                     <tr>
                        <td colspan="11" style="text-align:left;">&nbsp;</td>
                     </tr> 
                     <tr>
                     	  <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="9" style="text-align:left;">Order&nbsp;Details</td>
                        <td style="text-align:left;">&nbsp;</td>
                     </tr>
                     <tr>
                        <td colspan="1" style="text-align:left;">&nbsp;</td>
                        <td class="th1" colspan="1" style="text-align:left;">Code</td>
                        <td class="th1" colspan="5" style="text-align:left;">Description</td>
                        <td class="th1" colspan="1" style="text-align:right; padding:3px;">Order&nbsp;Qty</td>
                        <td class="th1" colspan="1" style="text-align:right; padding:3px;">Planned&nbsp;Qty</td>
                        <td class="th1" colspan="1" style="text-align:right; padding:3px;">Picked&nbsp;Qty</td>
                        <td colspan="1" style="text-align:left;">&nbsp;</td>         
                     </tr>
                     <?php $totQ=0;
                         $cls = "dc5";
                         foreach($mfT as $row) { 
                         	    if($row["buyer_delivered_qty"] == NULL) {$ordQty = $row["ordered_qty"]; } else { $ordQty = $row["buyer_delivered_qty"]; }
                         	    ?>
                              <tr>
                                 <td colspan="1" style="text-align:left;">&nbsp;</td>
                                 <td class="<?php echo $cls; ?>" colspan="1" style="text-align:left;"><?php echo $row["product_code"]?></td> 
                                 <td class="<?php echo $cls; ?>" colspan="5" style="text-align:left;"><?php echo $row["product_description"]?></td> 
                                 <td class="<?php echo $cls; ?>" colspan="1" style="text-align:right; padding:5px;"><?php echo $ordQty?></td>
                                 <td class="<?php echo $cls; ?>" colspan="1" style="text-align:right; padding:5px;"><?php echo $row["ordered_qty"]?></td>  
                                 <td class="<?php echo $cls; ?>" colspan="1" style="text-align:left;">&nbsp;</td>
                                 <td colspan="1" style="text-align:left;">&nbsp;</td>
                                 <?php $totQ+= $row["ordered_qty"] ; ?>
                              </tr>
                                 <td colspan="1" style="text-align:left;">&nbsp;</td>
                                 <td class="<?php echo $cls; ?>" colspan="1" style="text-align:left;" >&nbsp;</td> 
                                 <td class="<?php echo $cls; ?>" colspan="5" style="text-align:left;" >&nbsp;</td> 
                                 <td class="<?php echo $cls; ?>" colspan="1" style="text-align:left;" >&nbsp;</td> 
                                 <td class="<?php echo $cls; ?>" colspan="1" style="text-align:left;" >&nbsp;</td> 
                                 <td class="<?php echo $cls; ?>" colspan="1" style="text-align:left;" >&nbsp;</td>  
                                 <td colspan="1" style="text-align:left;">&nbsp;</td> 
                              </tr> 
                         <?php } ?>
                         <!-- total line -->
                    <tr>
                        <td colspan="1" style="text-align:left;">&nbsp;</td>    
                        <td class="dc6" colspan="6" >&nbsp;</td>
                        <td class="dc8" colspan="1" style="text-align:right; padding:5px;"><?php echo $totQ; ?></td>
                        <td class="dc8" colspan="2" style="text-align:right"; nowrap >&nbsp;</td>
                        <td colspan="1" style="text-align:left;">&nbsp;</td>  
                    </tr>         
                     <tr>
                        <td colspan="11" style="text-align:left;">&nbsp;</td>
                     </tr> 
                     <tr>
                        <td colspan="11" style="text-align:left;">&nbsp;</td>
                     </tr> 
                     <tr>
                     	  <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="9" style="text-align:left;">Comments</td>
                        <td style="text-align:left;">&nbsp;</td>
                     </tr>
                     <tr>
                        <td colspan="11" style="text-align:left;">&nbsp;</td>
                     </tr> 
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc7" colspan="8" style="text-align:left;">&nbsp;</td>
                        <td style="text-align:left;">&nbsp;</td>
                        <td style="text-align:left;">&nbsp;</td>
                     </tr>
                     <tr>
                        <td colspan="11" style="text-align:left;">&nbsp;</td>
                     </tr> 
                     <tr>
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc7" colspan="8" style="text-align:left;">&nbsp;</td>
                        <td style="text-align:left;">&nbsp;</td>
                        <td style="text-align:left;">&nbsp;</td>
                     </tr>
                     </tr>
                     <tr>
                        <td colspan="11" style="text-align:left;">&nbsp;</td>
                     </tr> 
                     <tr>
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc7" colspan="8" style="text-align:left;">&nbsp;</td>
                        <td style="text-align:left;">&nbsp;</td>
                        <td style="text-align:left;">&nbsp;</td>
                     </tr>
                     </tr>
                     <tr>
                        <td colspan="11" style="text-align:left;">&nbsp;</td>
                     </tr> 
                     <tr>
                     	  <td style="text-align:left;">&nbsp;</td>
                        <td class="dc3h" colspan="9" style="text-align:left;">Picked By</td>
                        <td style="text-align:left;">&nbsp;</td>
                     </tr>
                     <tr>
                     <tr>
                        <td style="text-align:left;">&nbsp;</td>
                        <td class="dc7" colspan="8" style="text-align:left;">&nbsp;</td>
                        <td style="text-align:left;">&nbsp;</td>
                        <td style="text-align:left;">&nbsp;</td>
                     </tr>
                     </tr>
                     <tr>
                        <td colspan="11" style="text-align:left;">&nbsp;</td>
                     </tr> 
                     <tr>
                        <td colspan="11" style="text-align:left;">&nbsp;</td>
                     </tr> 
                 </table>     
                 <table style="width:100%;"> 
                       <tr>
                           <td>&nbsp;</td>
                           <td>&nbsp;</td>
                           <td rowspan="2"><img src="<?php echo $ROOT.$PHPFOLDER; ?>images/Kwelanga_Solutions_Logo_smaller.jpg" style="width:60px; height:40px; float:right;" ></td>
                           <td width="12%;">&nbsp;</td>
                       </tr>
                       <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                       </tr>
                       <tr>
                             <td colspan="3"; style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo date('Y-m-d H:i:s'); ?></td>
                       </tr>
                       <tr>
                            <td colspan="3"; style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo 'inpick_document_version_multi'; ?></td>
                       </tr>
                       <tr>
                       	   <td ></td></td>
                       </tr>
                </table> 
                <p id="page-break">--- End of Page---</p>
        <?php } ?>     
    </body>
 </html>

<?php
$dbConn->dbClose();
?>
<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
    include_once($ROOT.$PHPFOLDER.'TO/PostingOrderNewDetailLineTO.php');

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      if (isset($_POST["INVOICE"])) $postINVOICE=test_input($_POST["INVOICE"]); else $postINVOICE = ''; 
      
      //Create new database object
      $dbConn = new dbConnect(); $dbConn->dbConnection();
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    	td.head1 {font-weight:normal;
    		        font-size:2em;text-align:left; 
    		        font-family: Calibri, Verdana, Ariel, sans-serif; 
    		        padding: 0 150px 0 150px }
      
      td.det1  {border-style:solid none solid none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: center; 
      	        font-weight: bold; 
      	        font-size: 15px;
      	        padding: 0 150px 0 150px  }

     td.det2  {border-style:solid solid solid none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: left; 
      	        font-weight: normal; 
      	        font-size: 12px;  }
    	
    	</style>

		</HEAD>
<body>
<?php
      $class = 'even';
      
      if (isset($_POST['canform'])) {
          return;	
      }
      
      if (isset($_POST['confirmform'])) {
           // Write new product to document detail and reclculate totals

           $docArray = $_POST;
           
           include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
           $postTransactionDAO = new PostTransactionDAO($dbConn);
           $result = $postTransactionDAO->insertNewDDline($docArray); 
           
           if($result == FLAG_ERRORTO_SUCCESS) { ?>
                 <script type='text/javascript'>parent.showMsgBoxInfo('Line Sucessfully added to order ')</script> 
              <?php 
           } else {
           	echo 'Oh Shit';
           }         
    }
      
      if (isset($_POST['finishform'])) {

             $qty      = test_input($_POST['Quantity']);
             $price    = test_input($_POST['ExclPrice']);
             $docUid   = test_input($_POST['documentUid']);
             $product  = test_input($_POST['Product']);
             
             // Validate Input  
             
             if($product=='Select Product') { ?>
                   <script type='text/javascript'>parent.showMsgBoxInfo('No Product selected')</script> 
                   <?php
                   unset($_POST['select']); 
                   unset($_POST['finishform']); 
             } elseif($qty<=0 || $qty > 999999  ) {  ?>
                   <script type='text/javascript'>parent.showMsgBoxInfo('Quantity Invalid')</script> 
                   <?php
                   unset($_POST['select']); 
                   unset($_POST['finishform']); 
             } elseif($price < 0 || $price > 9999999  ) {  ?>
                   <script type='text/javascript'>parent.showMsgBoxInfo('Price Invalid')</script> 
                   <?php
                   unset($_POST['select']); 
                   unset($_POST['finishform']); 
             } else {
             	     $PostingOrderNewDetailLineTO = new PostingOrderNewDetailLineTO;
                   $PostingOrderNewDetailLineTO->documentUid = $docUid;
                   $PostingOrderNewDetailLineTO->product     = $product;
                   $PostingOrderNewDetailLineTO->quantity    = $qty;
                   $PostingOrderNewDetailLineTO->priceValue  = $price; 
                   
                   // Display Complete Order
                   
                   $transactionDAO = new transactionDAO($dbConn);
             	     $mfCTX = $transactionDAO->getDocumentCompleteToUpdate($docUid, $userUId);
 
                   if (sizeof($mfCTX)==0) { ?>
                       <script type='text/javascript'>parent.showMsgBoxInfo("This is a huge 'Fricking' Problem")</script> 
                       <?php 
                       unset($postINVOICE);
                       unset($_POST['select']);
                   } else { ?>
                       <center>
                            <FORM name='Display Order' method=post action=''>
                                <table width="700"; style="border:none">
                                     <tr>
                                        <td class=head1 colspan="5"; style="text-align:center">Add an additional detail line to an Order</td>
                                     </tr>
                                     <tr>
                                        <td>&nbsp;</td>
                                     </tr>
                                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>" >
                                         <td colspan="5"; style="font-weight:bold; text-align:left;">Order Details</td>
                                     </tr>
                                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                        <td width="30%"; style="border:none">&nbsp</td>
                                        <td width="1%"; style="border:none">&nbsp</td>
                                        <td width="15%"; style="border:none">&nbsp</td>
                                        <td width="25%"; style="border:none">&nbsp</td>
                                        <td width="30%" ; style="border:none">&nbsp</td>
                                     </tr>
                                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                        <td style="border:none; text-align:left;"><?php echo "Customer  :  " . trim($mfCTX[0]['deliver_name'] ." "); ?></td>
                                        <td style="border:none">&nbsp</td>
                                        <td style="border:none">Document No. </td>
                                        <td style="border:none"><?php echo substr($mfCTX[0]['document_number'],2,6) ." "; ?></td>
                                        <td style="border:none">&nbsp</td>
                                     </tr>
                                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                     	   <td colspan="5"; style="text-align:left">&nbsp</td>
                                     </tr>
                                </table>
                                <table width="700"; style="border:none">
                                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>" >
                                        <td width="20%"; style="border:none; font-weight: bold; text-align:left;">Product Code</td>
                                        <td width="40%"; style="border:none; font-weight: bold; text-align:left;">Product</td>
                                        <td width="10%"; style="border:none; font-weight: bold; text-align:right;">Quantity</td>
                                        <td width="15%"; style="border:none; font-weight: bold; text-align:right;">Net Price</td>
                                        <td width="15%"; style="border:none; font-weight: bold; text-align:right;">Excl. Total</td>
                                     </tr>
                           <?php 
                                   $orderQty = $extPrice = 0;
                                   foreach($mfCTX as $drow) {
                                       $PostingOrderNewDetailLineTO->maxLineNo  = $drow['line_no']; ?>
                                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                          <td style="border:none; text-align:left;"><?php echo $drow['product_code']; ?></td>
                                          <td style="border:none; text-align:left;"><?php echo $drow['product_description']; ?></td>
                                          <td style="border:none; text-align:right;"><?php echo trim(round($drow['ordered_qty'],2)); ?></td>
                                          <td style="border:none; text-align:right;"><?php echo number_format($drow['net_price'],2,'.',' '); ?></td>
                                          <td style="border:none; text-align:right;"><?php echo number_format($drow['extended_price'],2,'.',' '); ?></td>
                                       </tr>
                                   <?php
                                       $orderQty = $orderQty  + $drow['ordered_qty'];
                                       $extPrice =  $extPrice + $drow['extended_price'];
                                   } 
                                       $orderQty = $orderQty  + $PostingOrderNewDetailLineTO->quantity;
                                       $extPrice = $extPrice  + ($PostingOrderNewDetailLineTO->quantity*$PostingOrderNewDetailLineTO->priceValue);                                   
                                   
                                       $pcStart = strpos($PostingOrderNewDetailLineTO->product,"#") + 1;
                                       $pclen   = strpos($PostingOrderNewDetailLineTO->product,"$") - $pcStart;
                           
                                       $pdStart = strpos($PostingOrderNewDetailLineTO->product,"$") + 1;
                                       $pdlen   = strpos($PostingOrderNewDetailLineTO->product,"%") - $pdStart;                           

                                       $vrStart = strpos($PostingOrderNewDetailLineTO->product,"%") + 1;
                                       $vrlen   = strpos($PostingOrderNewDetailLineTO->product,"^") - $vrStart;   
                                       
                                       $productCode = substr($PostingOrderNewDetailLineTO->product,$pcStart,$pclen);
                                       $productName = substr($PostingOrderNewDetailLineTO->product,$pdStart,$pdlen);
                                       $productUid  = substr($PostingOrderNewDetailLineTO->product,0,$pcStart-1);
                                       $vatRate     = substr($PostingOrderNewDetailLineTO->product,$vrStart,$vrlen);
                                       
                                   
                                   ?>
                                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                           <td style="border:none; text-align:left;  color:red;"><?php echo $productCode ; ?></td>
                                           <td style="border:none; text-align:left;  color:red;"><?php echo $productName ; ?></td>
                                           <td style="border:none; text-align:right; color:red;"><?php echo number_format($PostingOrderNewDetailLineTO->quantity,0,'.',' '); ?></td>
                                           <td style="border:none; text-align:right; color:red;"><?php echo number_format($PostingOrderNewDetailLineTO->priceValue,2,'.',' '); ?></td>
                                           <td style="border:none; text-align:right; color:red;"><?php echo number_format($PostingOrderNewDetailLineTO->quantity*$PostingOrderNewDetailLineTO->priceValue,2,'.',' '); ?></td>
                                        </tr>
                                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                           <td style="border:none; font-weight:bold; text-align:left;">Totals</td>
                                           <td style="border:none">&nbsp;</td>
                                           <td style="border:none; font-weight:bold; text-align:right;"><?php echo number_format($orderQty,0,'.',' '); ?></td>
                                           <td style="border:none; text-align:right;">&nbsp;</td>
                                           <td style="border:none; font-weight:bold; text-align:right;"><?php echo number_format($extPrice,2,'.',' '); ?></td>
                                        </tr>
                      	                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                      	                	 <td colspan="5"; style="text-align:center">
                                            <input type="hidden" id="documentUid"   name="documentUid" value=<?php echo $PostingOrderNewDetailLineTO->documentUid; ?>>
                                            <input type="hidden" id="prodUid"   name="prodUid" value=<?php echo $productUid; ?>>
                                            <input type="hidden" id="wQty"      name="wQty"    value=<?php echo $PostingOrderNewDetailLineTO->quantity; ?>>
                                            <input type="hidden" id="wPrice"    name="wPrice"  value=<?php echo $PostingOrderNewDetailLineTO->priceValue; ?>>
                                            <input type="hidden" id="vatRate"   name="vatRate" value=<?php echo $vatRate; ?>>
                                            <input type="hidden" id="lineNo"    name="lineNo"  value=<?php echo $PostingOrderNewDetailLineTO->maxLineNo; ?>>
                      	                	 	<INPUT TYPE="submit" class="submit" name="confirmform" value= "Confirm Changes"> <INPUT TYPE="submit" class="submit" name="canform" value= "Cancel"></td>
                                        </tr>
                                 </table> 
                           </form>        
                           <?php
                   }
             }
      }
     if (isset($_POST['firstform'])) {
          if ($postINVOICE !== '') {
              $transactionDAO = new transactionDAO($dbConn);
             	$mfDDU = $transactionDAO->getDocumentDetailsToUpdate($principalId,$postINVOICE);

              if (sizeof($mfDDU)==0) { ?>
                 <script type='text/javascript'>parent.showMsgBoxInfo('Document Number not Found')</script> 
              <?php 
                 unset($postINVOICE);
                 unset($_POST['select']);
              } else {
              	  if ($userUId == 11) {
                      if(!in_array($mfDDU[0]['document_status_uid'], array(DST_UNACCEPTED,DST_ACCEPTED, DST_INVOICED))) {
                         $allowStatus = 'N';
                      } else {
                         $allowStatus = 'Y';
                      }
                  } elseif ($userUId <> '11' && !in_array($mfDDU[0]['document_status_uid'], array(DST_UNACCEPTED,DST_ACCEPTED))) {
                         $allowStatus = 'N';
                  } else {
                         $allowStatus = 'Y';
                  }
                  if ($allowStatus == 'N' ) { ?>
                     <script type='text/javascript'>parent.showMsgBoxInfo('Document Aready invoiced. Lines cannot be added to it')</script> 
                  <?php 
                     unset($postINVOICE);
                      unset($firstform);
                  } else { 
                       $productDAO=new ProductDAO($dbConn);
	                     $mfPP = $productDAO->getUserPrincipalProductsArray($principalId, $userUId);
	                     ?>
                       <center>
                          <FORM name='Select Invoice' method=post action=''>
                             <table width="700"; style="border:none">
                               <tr>
                                  <td class=head1 colspan="5"; style="text-align:center">Add an additional detail line to an Order</td>
                               </tr>
                               <tr>
                                  <td>&nbsp;</td>
                               </tr>
                               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="5"; style="text-align:left">&nbsp</td>
                               </tr>
                               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                  <td width="30%"; style="border:none">&nbsp</td>
                                  <td width="1%"; style="border:none">&nbsp</td>
                                  <td width="15%"; style="border:none">&nbsp</td>
                                  <td width="25%"; style="border:none">&nbsp</td>
                                  <td width="30%" ; style="border:none">&nbsp</td>
                               </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                  <td style="border:none; text-align:left;"><?php echo "Customer  :  " . trim($mfDDU[0]['deliver_name'] ." "); ?></td>
                                  <td style="border:none">&nbsp</td>
                                  <td style="border:none">Document No. </td>
                                  <td style="border:none"><?php echo substr($mfDDU[0]['document_number'],2,6) ." "; ?></td>
                                  <td style="border:none">&nbsp</td>
                               </tr>
                               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="5"; style="text-align:left">&nbsp</td>
                               </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                  <td style="border:none; text-align:left;">Product</td>
                                  <td width="1%";  style="border:none">Quantity</td>
                                  <td width="10%"; style="border:none">Price</td>
                                  <td width="20%"; style="border:none">&nbsp</td>
                                  <td width="39%"; style="border:none">&nbsp</td>
                               </tr> 
                               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                  <td>
                                     <select name="Product" id="Product">
                                       <option value="Select Product">Select Product</option>
                                       <?php foreach($mfPP as $row) { ?>
                                             <option value="<?php echo trim($row['uid']) . "#" . trim($row['product_code']) ."$" . trim($row['product_description']) . "%" . trim($row['vat_rate']) . "^"; ?>"><?php echo $row['product_code'] ." - " . trim($row['product_description']); ?></option>
                                       <?php } ?>
                                     </select>
                                  </td>  
                                  <td style="border:none; text-align:right;">
                                  <input type="hidden" id="documentUid" name="documentUid" value=<?php echo $mfDDU[0]['uid'];?>>                               
                                  <input name='Quantity' id='Quantity' type='number' value=0 size=4  maxlength=4 /></td>
                                  
                                 <td style="border:none; text-align:right;">
                                  <input name='ExclPrice' id='ExclPrice' type='number' step="0.01" value=0 size=10  maxlength=10 /></td>                                  
                                  
                                  <td colspan="2"; style="text-align:left">&nbsp</td>
                               </tr>
                               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="5"; style="text-align:left">&nbsp</td>
                               </tr>
                      	       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                  <td colspan="5"; style="text-align:center"><INPUT TYPE="submit" class="submit" name="finishform" value= "Submit Line"><INPUT TYPE="submit" class="submit" name="canform" value= "Cancel"></td>
                               </tr>
                               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="5";>&nbsp</td>
                               </tr>
                             </table>
                          </form>
                  <?php
                  }
              }
          }  else { ?>
              <script type='text/javascript'>parent.showMsgBoxInfo('Document Number cannot be blank')</script> 
          <?php
              unset($_POST['firstform']);     	
          }
     }
if(!isset($_POST['firstform']) && !isset($_POST['finishform'])) { ?>
    <center>
       <FORM name='Select Invoice' method=post action=''>
            <table width="720"; style="border:none">
               <tr>
                 <td class=head1 >Add an additional detail line to an Order</td>
               </tr>
               <tr>
                 <td>&nbsp</td>
               </tr>	        	
               <tr>
                 <td class=head1 style="font-weight:normal; font-size:1em">Enter the Uplift Instruction Number</td>
               </tr>        	
            </table>
            <table width="720"; style="border:none" >        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="38%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="2%" ; style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                 <td style="text-align:left";>Enter Invoice Number</td>
                 <td colspan="4"; style="text-align:left"><input type="text" name="INVOICE"></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Invoice Details">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center> 
	</body>       
 </HTML>
<?php 
}
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 
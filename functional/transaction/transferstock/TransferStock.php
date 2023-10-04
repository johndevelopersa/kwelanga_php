<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/PosttransactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');


    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
    
      if (isset($_POST["User"]))        $postUser=$_POST["User"]; else $postUser = ''; 
      if (isset($_POST["ProductFrom"])) $postProductFrom=$_POST["ProductFrom"]; else $postProductFrom = ''; 
      if (isset($_POST["ProductTo"]))   $postProductTO=$_POST["ProductTo"];     else $postProductTO = ''; 
      if (isset($_POST["Quantity"]))    $postQuantity=$_POST["Quantity"];       else $postQuantity = 0; 
      if (isset($_POST["Warehouse"]))   $postWarehouse=$_POST["Warehouse"];     else $postWarehouse = 0; 
             
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
      if (isset($_POST['finish'])) {
      	
          // Store ID is Hardcode for HF at this stage 
          
          $storeUid = '891230686';
     
          // Create Stock Adjustment Decrease
     
          $PostStockDAO = new PostStockDAO($dbConn);
          $result = $PostStockDAO->createStockAdjustmentTransaction($principalId, 
                                                         $_POST['wareHouse'], 
                                                         DT_STOCKADJUST_NEG, 
                                                         $storeUid,
                                                         0-$_POST['fromQty'],
                                                         $_POST['userUID'],
                                                         $_POST['fromUid']);  
                                                         
           if ($result->type == FLAG_ERRORTO_SUCCESS) { 
                $PostStockDAO = new PostStockDAO($dbConn);                                                    
                $result = $PostStockDAO->updatestockTransferAdjustment($principalId, $_POST['wareHouse'], $_POST['fromUid'], 0-$_POST['fromQty']);
           }
           if ($result->type == FLAG_ERRORTO_SUCCESS) {            
                 // Create Stock Arrival Increase
                 $PostStockDAO = new PostStockDAO($dbConn);
                 $result = $PostStockDAO->createStockAdjustmentTransaction($principalId, 
                                                                           $_POST['wareHouse'], 
                                                                           DT_ARRIVAL, 
                                                                           $storeUid,
                                                                           $_POST['toQty'],
                                                                           $_POST['userUID'],
                                                                           $_POST['toUid']);      
           }
           if ($result->type == FLAG_ERRORTO_SUCCESS) {
                 $PostStockDAO = new PostStockDAO($dbConn);                                                    
                 $result = $PostStockDAO->updatestockTransferArrival($principalId, $_POST['wareHouse'], $_POST['toUid'], $_POST['toQty'] );
           }           
           if ($result->type == FLAG_ERRORTO_SUCCESS) {
                 $PostStockDAO = new PostStockDAO($dbConn);                                                               
                 $result = $PostStockDAO->recalculateStockBalances();
           }
           if ($result->type == FLAG_ERRORTO_SUCCESS) {  ?>        
           	     <script type='text/javascript'>parent.showMsgBoxInfo('Stock Transfer Successful')</script> 
           
           <?php } else {  ?>      
                 <script type='text/javascript'>parent.showMsgBoxError('Big Problem - Stock Transfer Successful')</script> 
           <?php           	
           }
 
      }
      
//************************************************************************************************************************
// **************************************************************************************
      if (isset($_POST['firstform'])) {
      	
          $fromHash    = strpos($postProductFrom,'#');
          $fromAmp     = strpos($postProductFrom,'$');
          $fromPercent = strpos($postProductFrom,'%');
          $fromCaret   = strpos($postProductFrom,'^');
          
      	
          $fromUID  = substr($postProductFrom,0, $fromHash) ;     	
          $fromPC   = substr($postProductFrom,$fromHash + 1, ($fromAmp)      - ($fromHash + 1) );
          $fromProd = substr($postProductFrom,$fromAmp  + 1, ($fromPercent)  - ($fromAmp  + 1) );
          $factor   = substr($postProductFrom,$fromPercent + 1, ($fromCaret) - ($fromPercent + 1) );

          $toHash    = strpos($postProductTO,'#');
          $toAmp     = strpos($postProductTO,'$');
          $toPercent = strpos($postProductTO,'%');
          $toCaret   = strpos($postProductTO,'^');

          $toUID   = substr($postProductTO,0, $toHash) ;
          $toPC    = substr($postProductTO,$toHash + 1, ($toAmp)      - ($toHash + 1) );
          $toProd  = substr($postProductTO,$toAmp  + 1, ($toPercent)  - ($toAmp  + 1) );
      	
          $depUid  = substr($postWarehouse,0, strpos($postWarehouse,'#')) ;
      	
      	
          if(trim($postWarehouse) != 'Select Warehouse') {
             if(trim($postProductFrom) != 'Select Product') {
            	   if(trim($postProductTO) != 'Select Product') {
            	   	  if(trim($fromUID != $toUID )) {  
            	   	  	  // Check for avaiable stock
            	   	  	  
            	   	  	 include_once($ROOT.$PHPFOLDER."DAO/StockDAO.php");
   	  
   	                   $StockDAO = new StockDAO($dbConn);                                                    
                       $stkrec    = $StockDAO->CheckForStockRecord($principalId, $depUid, $fromUID);
                       
                       if(count($stkrec) > 0 && $stkrec[0]['available'] >= $postQuantity ) { ?>
            	   	  	  
            	   	  	   <center>
                           <form name='Transfer' method=post target=''>
                              <br>
                              <table width="950"; style="border:none">
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td colspan=8; style="font-weight:bold;font-size:2em;text-align:center; font-family: Calibri, Verdana, Ariel, sans-serif;" >Confirm Product Transfer</td>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td colspan="8";>&nbsp</td>  
                                  </tr>                  
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td width="30%"; style="font-weight: bold; border:none">Transfer From Product</td>
                                     <td width="10%"; style="font-weight: bold;border:none">Available Stock</td>
                                     <td width="10%"; style="font-weight: bold;border:none">No of Cases</td>
                                     <td width="10%"; style="font-weight: bold;border:none">Items Per Case</td>
                                     <td width="1%";  style="font-weight: bold;border:none">&nbsp</td>
                                     <td width="30%"; style="font-weight: bold;border:none">Transfer To Product</td>
                                     <td width="10%"; style="font-weight: bold;border:none">Quantity Transferred</td>
                                     <td width="1%" ;>&nbsp</td>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td colspan="8";>&nbsp</td>  
                                  </tr>                                
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td width="30%";  style="font-weight: normal; border:none"><?php echo trim($fromPC . ' - ' . $fromProd) ?> </td>
                                     <td width="10%"; style="font-weight: normal;border:none"><?php echo trim($stkrec[0]['available']); ?> </td>
                                     <td width="10%"; style="font-weight: normal;border:none"><?php echo trim($postQuantity); ?></td>
                                     <td width="10%"; style="font-weight: normal;border:none"><?php echo trim($factor); ?> </td>
                                     <td width="1%";  style="font-weight: normal;border:none">&nbsp</td>
                                     <td width="30%"; style="font-weight: normal;border:none"><?php echo trim($toPC . ' - ' . $toProd); ?> </td>
                                     <td width="10%"; style="font-weight: normal;border:none"><?php echo trim($postQuantity * $factor); ?></td>
                                     <td width="1%" ;>&nbsp
                                     	    <input type="hidden" id="fromUid"   name="fromUid" value=<?php echo $fromUID; ?>>
                                          <input type="hidden" id="toUid"     name="toUid"   value=<?php echo $toUID; ?>>
                                          <input type="hidden" id="fromQty"   name="fromQty" value=<?php echo $postQuantity; ?>>
                                     	    <input type="hidden" id="toQty"     name="toQty"   value=<?php echo $postQuantity * $factor; ?>>
                                          <input type="hidden" id="wareHouse" name="wareHouse" value=<?php echo $depUid; ?>>
                                          <input type="hidden" id="userUID"   name="userUID" value=<?php echo $userUId; ?>>
                                     	
                                     </td>                                
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                	     <td colspan=8;">&nbsp;</td>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>" >
                                     <td colspan="8"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="finish" value= "Submit">
                                                                                 <INPUT TYPE="submit" class="submit" name="canfinish" value= "Cancel"></td>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td colspan=8;">&nbsp;</td>
                                  </tr>
                              </table>
                           </form>    
                         </center>
                       <?php } else { ?>
                       	   <script type='text/javascript' >parent.showMsgBoxError('The is no availble stock of the `from` Product' )</script>
                           <?php  unset($_POST['firstform'] ); 
                       }
                    } else { ?>
                     <script type='text/javascript' >parent.showMsgBoxError('From and To Products cannot be the same' )</script>
                     <?php  unset($_POST['firstform'] );                    	 
                    }
      	         } else { ?>	
                     <script type='text/javascript' >parent.showMsgBoxError('No TO Product Selected' )</script>
                     <?php  unset($_POST['firstform'] );
                 }        		
             } else {  ?>
             	    <script type='text/javascript' >parent.showMsgBoxError('No FROM Product Selected' )</script>
                  <?php  unset($_POST['firstform']); 	
             }   
          } else {  ?>
             	    <script type='text/javascript' >parent.showMsgBoxError('No Warehouse Selected' )</script>
                  <?php  unset($_POST['firstform']); 	
          }         
      }                                                                                                                          
      $productDAO=new productDAO($dbConn);
	    $mfPP = $productDAO->getUserPrincipalProductsArray($principalId, $userUId);

      $MiscellaneousDAO=new MiscellaneousDAO($dbConn);
	    $mfdep = $MiscellaneousDAO->getStockUserWarehouse($principalId, $userUId);	    
	    
	    
	//    print_r($mfPP);
	    
// **************************************************************************************************************************************
if(!isset($_POST['firstform']) && !isset($_POST['finishform'])) { ?>
    <center>
       <FORM name='Select Invoice' method=post action=''>
            <table width="720"; style="border:none">
               <tr>
                 <td class=head1 >Transfer Stock to Loose Units Stock Code</td>
               </tr>
               <tr>
                 <td>&nbsp</td>
               </tr>	        	
            </table>
            <table width="500"; style="border:none" >   
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="6";>&nbsp;</td>	
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td style="font-weight: bold;border:none">Select Warehouse&nbsp;&nbsp;&nbsp;&nbsp;
                       <select name="Warehouse" id="Warehouse"><option value="Select Warehouse">Select Warehouse</option>
                           <?php foreach($mfdep as $drow) { ?>
                                 <option value="<?php echo trim($drow['uid']) . "#" . trim($drow['name']); ?>"><?php echo $drow['uid'] ." - " . trim($drow['name']); ?></option>
                           <?php } ?>
                      </select>                  	
                  </td>
                   <td colspan="5";>&nbsp;</td>	
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="6";>&nbsp;</td>	
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td width="30%"; style="font-weight: bold; border:none">Transfer From Product Code </td>
                   <td width="15%"; style="font-weight: bold;border:none">No of Cases</td>
                   <td width="1%";  style="font-weight: bold;border:none">&nbsp</td>
                   <td width="35%"; style="font-weight: bold;border:none">Transfer To Product Code </td>
                   <td width="9%";  style="font-weight: bold;border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="5";>&nbsp;</td>	
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td>
                      <select name="ProductFrom" id="ProductFrom"><option value="Select Product" >Select Product</option>
                           <?php foreach($mfPP as $row) { ?>
                                 <option value="<?php echo trim($row['uid']) . "#" . trim($row['product_code']) ."$" . trim($row['product_description']) . "%" . trim($row['items_per_case']) . "^"; ?>"><?php echo $row['product_code'] ." - " . trim($row['product_description']); ?></option>
                           <?php } ?>
                      </select>
                   </td>                   
                   <td style="border:none; text-align:left;"><input name='Quantity' id='Quantity' type='number' value=1 size=4  maxlength=4 /></td>
                   <td >&nbsp;</td>	
                   <td>
                      <select name="ProductTo" id="ProductTo"><option value="Select Product">Select Product</option>
                           <?php foreach($mfPP as $row) { ?>
                                 <option value="<?php echo trim($row['uid']) . "#" . trim($row['product_code']) ."$" . trim($row['product_description']) . "%" . trim($row['items_per_case']) . "^"; ?>"><?php echo $row['product_code'] ." - " . trim($row['product_description']); ?></option>
                           <?php } ?>
                      </select>
                   </td>   
                   <td>&nbsp;</td> 
               </tr> 
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Confirm Transfer">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center> 

<?php 
} ?>
	</body>       
 </HTML>
 <?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 
<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
		include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
		include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/4_default.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
    <script>
        function selectAll(elementName, flag){
           $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
         }
    </script>
		</HEAD>
<body>

<?php

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      $postFROMDATE = (isset($_POST["FROMDATE"])) ? htmlspecialchars($_POST["FROMDATE"]) :  CommonUtils::getUserDate();
      $postTODATE   = (isset($_POST["TODATE"])) ? htmlspecialchars($postTODATE=$_POST["TODATE"]) : CommonUtils::getUserDate();  

      if (isset($_POST["CHAIN"])) $postChain=$_POST["CHAIN"]; else $postChain = ''; 
      if (isset($_POST["FREEFACTOR"])) $postffactor=$_POST["FREEFACTOR"]; else $postffactor = ''; 
      
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     
     include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
     
     $storeDAO = new StoreDAO($dbConn);
	   $mfC = $storeDAO->getAllPrincipalChainsForUser($userUId , $principalId);

     include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");

	   $productDAO = new ProductDAO($dbConn);
	   $mfP = $productDAO->getUserPrincipalProductsArray($principalId, $userUId);
	   
         if (isset($_POST['finish'])) {
          	$inv=array();          	
             if(!empty($_POST['finish'])){
               // Loop to store and display values of individual checked checkbox.
               foreach($_POST['finishi'] as $invselected){
               array_push($inv,$invselected);
               }
             } 
						 $result = new ErrorTO;
             ExtractFreeStockInvoices($principalId, $inv,$result);
              
             if ($result->type==FLAG_ERRORTO_SUCCESS) {
             ?>
                 <script type='text/javascript'>parent.showMsgBoxInfo('Invoices Successfully Created - Allow 15 mins For Processing')</script> 
       	     <?php
             }       	
             return;
         }
	   
	   

     if (isset($_POST['select'])) {
     	  $cha=array();
     	  $prda=array();
        if(!empty($_POST['CHAIN'])){
         // Loop to store and display values of individual checked checkbox.
            foreach($_POST['CHAIN'] as $chainselected){
              array_push($cha,$chainselected);
            }
        }
       if(!empty($_POST['PRODUCT'])){
        // Loop to store and display values of individual checked checkbox.
           foreach($_POST['PRODUCT'] as $prodselected){
           	 array_push($prda,$prodselected);
           }
       }
          if (sizeof($cha)==0 || sizeof($prda)==0 || $postffactor=='') { ?>
 				    <center>
 				    <table>
 					     <tr>
                  <td colspan="5" style="border:1px solid black; border-collapse: collapse; text-align: center; font-weight: bold;">No Sales Match The Supplied Parameters !!</td>            	
               </tr>	
    		    </table>
    		    </center>
         <?php 
           return;
         } else { 
         	
         	  $chlist = implode(',',$cha);
         	  $prdlist = implode(',',$prda);
         	           	  
         	  $transactionDAO = new transactionDAO($dbConn);
            $mfFS = $transactionDAO->getFreeStockInvoices($principalId,$postFROMDATE,$postTODATE,$chlist,$prdlist,$postffactor);
          ?> 
 		    <center>
          <FORM name='reprintts' method=post target=''>
            <Table style="border:1px solid black; border-collapse: collapse; float: center;">
             <tr>
               <td colspan="6" style="border:1px solid black; border-collapse: collapse; text-align: center; font-weight: bold;">Select Stores Requiring Invoicest</td>            	
             </tr>	
             <tr>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Store</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Product</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Last FS Order</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Invoice Quantity</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Free Stock</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Select</th> 
             </tr>
<?php
           $cl = "even";
           foreach ($mfFS as $row) {
              $cl = GUICommonUtils::styleEO($cl);
              $val = $row['Free_Stock'];
              if ($val > 0) {
?>
                <tr class="<?php echo $cl; ?>">
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Store'];?></td>
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['product_description'];?></td>
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Last_Order'];?></td>
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Quantity'];?></td>
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $val;?></td>
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><input type="checkbox" name="finishi[]" value=" <?php echo($row['Store']. ',' . $row['Store_Uid'] . ',' . $row['product_code'] . ',' . $row['product_description'] . ',' . $val);?>" ></td>
                </tr>
<?php 	      } 
          }
?>              
             </table> 
              <br><br>
             <table> 
              <tr>
                <td colspan="5"></td>
        	      <td><INPUT TYPE="submit" class="submit" name="finish[]" value= "Create Free Stock Invoices"></td>
              </tr>
             </table>
	     </center>
<?php
        }
        return;
    }  

$class = 'odd';
?>
<center>
	 <FORM name='Select Waybill' method=post action=''>
        <table width:"60%"; style="border:none">
        	<tr>
        		<td colspan"0"; style="font-weight:normal;font-size:2em;text-align:left"; font-family: Calibri, Verdana, Ariel, sans-serif; >Extract the Free Stock Sales Invoices</td>
        		</tr>
        	<tr>
        		 <td>&nbsp</td>
        		</tr>	        	
        	<tr>
        		<td style="font-weight:normal; font-size:1em">Choose the required parameters</td>
        		</tr>        	
         </table>
        <table width:"20%"; style="border:none" >        	
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
            <td>&nbsp</td>
            <td>&nbsp</td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td style="text-align:left";>Start Processed Date : </td>
           <td colspan="2"; style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="2">&nbsp</td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td style="text-align:left";>End Processed Date : </td>
           <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("TODATE",$postTODATE); ?> </td>
          </tr>         
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="2">&nbsp</td>
          </tr> 
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
        	<td>Chain(s):</td>
          <td>
            <div style='overflow:auto; width:500px; height:120px; font-size:12px;'>
               <div style="line-height:22px;padding:0px 10px;border-bottom:1px solid lightSkyblue;">Select: 
               	    <a href="javascript:;" onClick="selectAll('CHAIN[]',1)">All</a> | <a href="javascript:;" onClick="selectAll('CHAIN[]',0)">None</a>
               </div>      	
             <table class='tableReset'>
              <?php 
              foreach ($mfC as $row) {
		            $postC=$row['principal_chain_uid'];
              ?>
                <tr> 
              	  <td><input name='CHAIN[]' type='checkbox' value=<?php echo($postC);?>> </td><td><?php echo($row['chain_name']); ?></td>
                </tr>
              <?php } 
               ?>
              </table>
           </div>
          </td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
		      <td>Free Stock Factor:</td>
		      <td><input name='FREEFACTOR' type='text' size='5' maxlength='50' value='10' /></td>
        </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
        	<td>Product(s):</td>
          <td>
            <div style='overflow:auto; width:500px; height:120px; font-size:12px;'>
               <div style="line-height:22px;padding:0px 10px;border-bottom:1px solid lightSkyblue;">Select: 
               	    <a href="javascript:;" onClick="selectAll('PRODUCT[]',1)">All</a> | <a href="javascript:;" onClick="selectAll('PRODUCT[]',0)">None</a>
               </div>      	
             <table class='tableReset'>
              <?php 
              foreach ($mfP as $row) {
		            $postP=$row['uid'];
              ?>
                <tr> 
              	  <td><input name='PRODUCT[]' type='radio' value=<?php echo($postP);?>> </td><td><?php echo($row['product_code'].DELIMITER_OTHER_1.$row['product_description']); ?></td>
                </tr>
              <?php } 
               ?>
              </table>
           </div>
          </td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="3">&nbsp</td>
          </tr>
       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
        	 <td colspan="3"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="select" value= "Get Free Stock Invoice List"></td>
         </tr>          
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="3">&nbsp</td>
          </tr>  
 				</table>
		</form>
    </center> 
	</body>       
 </HTML>
<?php
 
function ExtractFreeStockInvoices($principalId, $inv1, $result) {
	
	  global $ROOT, $PHPFOLDER; $eTO;
	
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
    include_once($ROOT.$PHPFOLDER."TO/ErrorTO.php");
  
	  //Create new database object
    $dbConn = new dbConnect(); $dbConn->dbConnection();
    
    $sequenceDAO = new SequenceDAO($dbConn);
    $seq = $sequenceDAO->getFTPFileExportSequence();
    $filename = str_replace('[@SEQ]', $seq, 'FREEASC[@SEQ].csv');
    
    $linecount = 0;  
     
   include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
    	foreach ($inv1 as $row) {
		     $eainv = explode(',',$row);
	       $storeDAO = new StoreDAO($dbConn);
         $FsSt = $storeDAO->getFreeStockStoreDetails($eainv[1]);
	
	      $dataH = array();
        $dataH[] = 'H';                                                          
        $dataH[] = '1';                                                        
        $dataH[] = $principalId;
        $dataH[] = date("Ymd"); 
        $dataH[] = '';                           
        $dataH[] = '';                   
        $dataH[] = str_replace(',',' ',$FsSt[0]['deliver_name'])  ;                      
        $dataH[] = str_replace(',',' ',$FsSt[0]['deliver_add1']) ;
        $dataH[] = str_replace(',',' ',$FsSt[0]['deliver_add2']) ;
        $dataH[] = str_replace(',',' ',$FsSt[0]['deliver_add3']) ;
        $dataH[] = str_replace(',',' ',$FsSt[0]['bill_name']) ;
        $dataH[] = str_replace(',',' ',$FsSt[0]['bill_add1']) ;
        $dataH[] = str_replace(',',' ',$FsSt[0]['bill_add2']) ;
        $dataH[] = str_replace(',',' ',$FsSt[0]['bill_add3']) ;
        $dataH[] = $FsSt[0]['Warehouse_Uid'] ;
        $dataH[] = $FsSt[0]['Warehouse_Name'] ;
        $dataH[] = $FsSt[0]['Chain_Uid'] ;
        $dataH[] = $FsSt[0]['Chain_Name'] ;
        $dataH[] = '' ;
        $dataH[] = '';
        $dataH[] = $FsSt[0]['branch_code'] ;
        $dataH[] = $FsSt[0]['old_account'] ;
        $dataH[] = DT_ORDINV_ZERO_PRICE ;

        $linecount++;  
	
        $dataArr[] = join(",", $dataH);	

        $dataD = array();
        $dataD[] = 'D';
        $dataD[] =  '0101';
        $dataD[] =  $eainv[2];                                
        $dataD[] =  $eainv[3];
        $dataD[] =  abs($eainv[4]);
        $dataD[] =  number_format(round('0.00', 2), 2, '.', '');
        $dataD[] =  '';                                            

        $dataArr[] = join(',', $dataD);
        $linecount++;
 	    }	        
        $linecount++;
       
         $dataT2 = array();
         $dataT2[] = 'T';                                                  
         $dataT2[] = str_pad($linecount,5,"0",STR_PAD_LEFT);                 

         $dataArr[] = join(',', $dataT2);
        
         $data = join("\r\n",$dataArr);  

        //PATH AND LOCATION OF FILE
        $localPath = 'c:/www/ftp/asco/';
        if(!is_dir($localPath)){
           mkdir($localPath, 0777, TRUE);  
        }
        $bytesWrit = @file_put_contents($localPath . $filename, $data);
        
        if($bytesWrit == strlen($data)){
          /*------  SUCCESSFUL ------*/
          $result->type= FLAG_ERRORTO_SUCCESS;
          $result->identifier = $filename;
          return $result;
        } else {
          BroadcastingUtils::sendAlertEmail($errSubject, "The DEPOT Export file could not be created @principalUid:".$this->postingOrderTO->principalUId."; @path:".$localPath . $filename, "Y", $quietMode = false);
          $result->type = FLAG_ERRORTO_ERROR;
          return $result;
        }
	}
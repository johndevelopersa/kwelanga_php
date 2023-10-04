<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
		include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');		
    
	
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>POD Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/4_default.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

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
      $postRDATE    = (isset($_POST["RDATE"])) ? htmlspecialchars($postRDATE=$_POST["RDATE"]) : CommonUtils::getUserDate();     
      if (isset($_POST['warehouse'])) $postwarehouse = $_POST['warehouse']; else $postwarehouse="";
           
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();

          if (isset($_POST['finish'])) {
       	 	   if (isset($_POST['select'])) {
                $list = implode(",",$_POST['select']);
             
                include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
                $postTransactionDAO = new PostTransactionDAO($dbConn);
                $rTO = $postTransactionDAO->updatePodReceipt($list, $userUId, $postRDATE );
 
             if ($rTO->type==FLAG_ERRORTO_SUCCESS) {
                $dbConn->dbinsQuery("commit");
 ?>               
                <script type='text/javascript'>parent.showMsgBoxInfo('Invoices Successfully Receipted')</script>       
 <?php               
             }  else {
                $dbConn->dbinsQuery("rollback");
 ?>               
                <script type='text/javascript'>parent.showMsgBoxInfo('Invoices Receipting Failed')</script>       
 <?php 
         
             }
            }
         }
  if (isset($_POST['warehouse']) && $postwarehouse <> 'Select Warehouse') {
     if (isset($_POST['select'])) { 
        include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
        $transactionDAO = new transactionDAO($dbConn);
        $mfIL = $transactionDAO->getInvoicesToReceipt($principalId, $postwarehouse, $postFROMDATE, $postTODATE);     
     
// print_r($mfIL);
         if (sizeof($mfIL)==0) { ?>
 				     <center>
 					      <table>
 						      <tr>
 							      <td>&nbsp;</td>
 						      </tr>
 						    </table>
               <table>
 						     <tr>
                   <td colspan="5" style="border:1px solid black; border-collapse: collapse; text-align: center; font-weight: bold;">No Invoices Found !!</td>            	
                </tr>	
    			    </table>
    		   </center>
<?php 
        return;
       }
?>
		      <center>          
            <FORM name='reprintts' method=post target=''>
              <Table style="border:1px solid black; border-collapse: collapse; float: center;">
                <tr>
                  <td colspan="9" style="border:1px solid black; border-collapse: collapse; text-align: center; font-weight: bold;">Select Invoices Received</td>            	
                </tr>	
                <tr>
                  <th style="border:1px solid black; border-collapse: collapse; float: center;">Warehouse</th>
                  <th style="border:1px solid black; border-collapse: collapse; float: center;">Document Number</th>
                  <th style="border:1px solid black; border-collapse: collapse; float: center;">Invoice Number</th>
                  <th style="border:1px solid black; border-collapse: collapse; float: center;">Invoice Date</th>
                  <th style="border:1px solid black; border-collapse: collapse; float: center;">Store</th>
                  <th style="border:1px solid black; border-collapse: collapse; float: center;">Qty</th>
                  <th style="border:1px solid black; border-collapse: collapse; float: center;">Inc Value</th>
                  <th style="border:1px solid black; border-collapse: collapse; float: center;">Select</th>
               </tr>
<?php
           $cl = "even";
           foreach ($mfIL as $row) {
              $cl = GUICommonUtils::styleEO($cl);
 ?>
               <tr class="<?php echo $cl; ?>">
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['depot'];?></td>
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo substr($row['document_number'],2,6);?></td>
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo substr($row['invoice_number'],0,6); ?></td>
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['invoice_date'];?></td>
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['deliver_name'];?></td>
                  <td style="border:1px solid black; border-collapse: collapse; float: center; text-align:right; "><?php echo $row['cases'];?>&nbsp;</td>
                  <td style="border:1px solid black; border-collapse: collapse; float: center; text-align:right; "><?php echo number_format(round($row['invoice_total'],2),2,'.',' ');?>&nbsp;</td> 
                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['documentuid'];?>"></td>
              </tr>
<?php 	} ?>              
              </table> 
              <br><br>
              <table> 
                 <tr>
                   <td colspan="5"></td>
        	         <td><INPUT TYPE="submit" class="submit" name="finish" value= "Submit Selected Invoices"></td>
                 </tr>
              </table>
	       </center>
<?php
     }
         return;
  }        
         include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
         $transactionDAO = new transactionDAO($dbConn);
         $mfWH = $transactionDAO->getPrincipalWarehouses($principalId, $userUId );
?>    
    <center>
	    <FORM name='POD Receipt Paramenters' method=post action=''>
          <table width:"100%"; style="border:none">
           	<tr>
          		 <td>&nbsp</td>
         		</tr>	      
          	<tr>
           		<td colspan"0"; style="font-weight:normal;font-size:2em;text-align:left"; font-family: Calibri, Verdana, Ariel, sans-serif; >Select POD Receipt Parameters</td>
        		</tr>
        	  <tr>
        	    <td>&nbsp</td>
        		</tr>	        	
        	  <tr>
        	   	<td style="font-weight:normal; font-size:1em">Choose the required parameters</td>
        		</tr>        	
          </table>
        <table width:"30%"; >        	
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td>&nbsp</td>
             <td>&nbsp</td>
             <td>&nbsp</td>
             <td>&nbsp</td>
             <td>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td style="text-align:left";>Start Invoiced Date : </td>
             <td colspan="2"; style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
             <td>&nbsp</td>
             <td>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td Colspan="5">&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td style="text-align:left";>End Invoiced Date : </td>
             <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("TODATE",$postTODATE); ?> </td>
             <td>&nbsp</td>
             <td>&nbsp</td>    
            </tr>         
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td Colspan="5">&nbsp</td>
          </tr> 
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
         	   <td style="text-align:left";>Select Warehouse :</td>
             <td>
             	 <select name="warehouse" id="warehouse">
             		 <option value="Select Warehouse">Select Warehouse</option>
             			<?php foreach($mfWH as $row) { ?>
              				<option value="<?php echo $row['uid']; ?>"><?php echo $row['name']; ?></option>
              		<?php } ?>
              </select>
            </td>
            <td>&nbsp</td>
          </tr>        
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td Colspan="5">&nbsp</td>
          </tr>  
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td style="text-align:left";>Enter Receipt Date : </td>
             <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("RDATE",$postRDATE); ?> </td>
             <td>&nbsp</td>
             <td>&nbsp</td>    
          </tr>         
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td Colspan="5">&nbsp</td>
          </tr>  
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
        	   <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="select" value= "Get Invoices to Receipt"></td>
         </tr>           
         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td Colspan="5">&nbsp</td>
         </tr>  
 				</table>
    </center>  

</body>
 </HTML>
 
 <?php


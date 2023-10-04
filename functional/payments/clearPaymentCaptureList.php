<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/transaction/clearPaymentCaptureList.php


    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/PaymentsDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/PostPaymentsDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');		
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once($ROOT.$PHPFOLDER.'TO/PaymentsTO.php');    

    $errorTO = new ErrorTO;

    //Create new database object
    $dbConn = new dbConnect(); 
    $dbConn->dbConnection();

?>
<!DOCTYPE html>
<HTML>
  <HEAD>

		<TITLE>Document Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>


    <style type="text/css">
    	input { 
           text-align: right; 
      }  	
    	
    </style>    	
    	
		</HEAD>
<body>

<?php

    if (!isset($_SESSION)) session_start() ;
    $userUId     = $_SESSION['user_id'] ;
    $principalId = $_SESSION['principal_id'] ;
    $depotId     = $_SESSION['depot_id'] ;
    $systemId    = $_SESSION["system_id"];
    $systemName  = $_SESSION['system_name'];
 
      if (isset($_POST['Customer'])) $postCustomer = $_POST['Customer']; else $postCustomer="";

      $postPAYDATE = (isset($_POST["PAYDATE"])) ? htmlspecialchars($_POST["PAYDATE"]) :  CommonUtils::getUserDate();     
 
      if (isset($_POST['retfinish'])) {
          return;	
      }

      if (isset($_POST['finish'])) {
         if (isset($_POST['select'])) {
            foreach($_POST['select'] as $row) {
                  $storeId  = substr($row, strpos($row,'U') + 1, strpos($row,'C') - strpos($row,'U') - 1 ) ;
                  $tdate    = date('Y-m-d');
                  $docno    = substr($row, strpos($row,'C') + 1, strpos($row,'D') - strpos($row,'C') - 1 ) ;
                  $amount   = substr($row, strpos($row,'D') + 1, strpos($row,'R') - strpos($row,'D') - 1 ) ;
                  $PaymentsDAO = new PostPaymentsDAO($dbConn);
                  $errorTO = $PaymentsDAO->insertAllocatedTransaction($principalId, $storeId, 46, $amount, $tdate, $docno);

                  if($errorTO->type != FLAG_ERRORTO_SUCCESS) {
                       print_r($errorTO);
                       ?>
                       <script type='text/javascript' >parent.showMsgBoxError('<?php echo $errorTO->description;?>')</script>
                       <?php	
                       return;                	
                  }
            }
                  ?>
                       <script type='text/javascript' >parent.showMsgBoxInfo('Clearing Invoice List Successful')</script>
                       <?php	
                       unset ($_POST['firstform']);
                       unset ($_POST['finish']); 
                       unset ($_POST['select']);            

         } else {
              $errorTO->description= "No Invoices were selected to Clear <BR>";
              ?>
              <script type='text/javascript' >parent.showMsgBoxError('<?php echo $errorTO->description;?>')</script>
              <?php
              unset ($_POST['firstform']);
              unset ($_POST['finish']); 
              unset ($_POST['select']); 
         }
      }

// *******************************************************************************************************************************************
        
// Display List of invoices
      if (isset($_POST['firstform'])) {
         if(trim($_POST['Customer']) <> 'Select Cus') {
               $PaymentsDAO = new PaymentsDAO($dbConn);
               $umIN        = $PaymentsDAO->getUnAllocatedInvoiceAmts($principalId, trim(substr($_POST['Customer'],0,10)));
               if (sizeof($umIN)<> 0) { 
                   ?>
                   <center>
                      <form name='Invoices' method=post target=''>
                         <br>
                         <table width=50%; style="border:none;">
                            <tr>
                               <td colspan=5; style="font-weight:normal;font-size:2em;text-align:center; font-family: Calibri, Verdana, Ariel, sans-serif; border: None;">Paid Invoice Clearing</td>
                            </tr>
                            <tr>
                               <td colspan=5; >&nbsp;</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td colspan=2; style="font-weight:bold; font-size:12px; border:1px solid black; border-collapse: collapse;" >Customer </td>           
                               <td colspan=3; style="font-weight:bold; font-size:12px; border:1px solid black; border-collapse: collapse;" ><?php echo $umIN[0]['Custname'];?></td>           
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td colspan=5;>&nbsp</td>
                             </tr>
                            </tr>                                        
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td colspan=2; style="font-weight:bold; font-size:12px; border:1px solid black; border-collapse: collapse;">Document Number</td>
                               <td style="font-weight:bold; font-size:12px; border:1px solid black; border-collapse: collapse;">Invoice Date</td>
                               <td style="font-weight:bold; font-size:12px; border:1px solid black; border-collapse: collapse;">Invoice Amount</td>
                               <td style="font-weight:bold; font-size:12px; border:1px solid black; border-collapse: collapse;">Clear</td>
                            </tr>
                            <?php
                            $recno = 0;
                            foreach ($umIN as $row) {
                               $class = GUICommonUtils::styleEO($class);
                            ?>  
                              <tr class="<?php echo $class; ?>">
                                  <td colspan=2; style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo substr($row['document_number'],2,6);?></TD>
                                  <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['invoice_date'];?></td>
                                  <td style="border:1px solid black; border-collapse: collapse; text-align:right;"><?php echo $row['Invoice_Total'];?></td>
                                  <td style="border:1px solid black; border-collapse:collapse; float: center;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['document_master_uid'] ."U" . $postCustomer . "C" . $row['document_number']  . "D" . $row['Invoice_Total'] . "R";?> " <?php echo $checked ?> ></td>
                             </tr>
                             <?php   $recno++; 	} ?>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td colspan=5;>&nbsp</td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td colspan=1;>&nbsp;</td>
                               <td colspan=3;><INPUT TYPE="submit" class="submit" name="finish" value= "Submit Payments">&nbsp;&nbsp;&nbsp;
                                        <INPUT TYPE="submit" class="submit" name="canfinish" value= "Cancel"></td>
                               <td colspan=1;>&nbsp;</td>	              
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td colspan=5;>&nbsp</td>
                             </tr>
                         </table>
                      </form>    
                   </center> 
                 <?php
               } else {
                     $errorTO->description= "No Customer Un Paid Invoices <BR>";
                     ?>
                     <script type='text/javascript' >parent.showMsgBoxError('<?php echo $errorTO->description;?>')</script>
                     <?php 
                      unset ($_POST['firstform']);    
                      unset ($_POST['finish']);
                      unset ($_POST['select']);
               }      
         } else {
                $errorTO->description= "No Customer Selected <BR>";
                ?>
                <script type='text/javascript' >parent.showMsgBoxError('<?php echo $errorTO->description;?>')</script>
                <?php 
                unset ($_POST['firstform']);    
                unset ($_POST['finish']);
                unset ($_POST['select']);
         }      

      }

// *******************************************************************************************************************************************

if(!isset($_POST['firstform'])) {

     $PaymentsDAO = new PaymentsDAO($dbConn);
     $mfCS = $PaymentsDAO->getPaymentCustomers($principalId);   
     
?>
     <center>
    <FORM name='Clear Invoice Payment List' method=post action=''>
        <table width: 90% ; style="border:none">
           <tr>
              <td  style="font-weight:normal;font-size:2em;text-align:left"; font-family: Calibri, Verdana, Ariel, sans-serif; >Clear Invoice Payment List</td>
           </tr>
           <tr>
              <td>&nbsp</td>
           </tr>	        	   	
        </table>
        <table width:50% ; >        	
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan=5;>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               <td>Select Customer</td>
               <td>
                  <select name="Customer" id="Customer">
                     <option value="Select Customer">Select Customer</option>
                     <?php foreach($mfCS as $row) { ?>
                           <option value="<?php echo $row['uid']; ?>"><?php echo $row['Customer']; ?></option>
                    <?php  } ?>
                  </select>
                </td>
                <td colspan=3;>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                <td colspan=5;>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               <td colspan=5;>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               <td style="column-span:1; text-align:center;">&nbsp</td>
               <td style="column-span:1; text-align:left;">
                   <INPUT TYPE="submit" class="submit" name="firstform" value= "Get Un Paid Invoices">
                   <INPUT TYPE="submit" class="submit" name="retfinish" value= "Cancel">
               </td>
               <td style="column-span:2; text-align:center;">&nbsp</td>
           </tr>          
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               <td colspan=5;>&nbsp</td>
           </tr>  
        </table>
    </FORM>
    </center> 
<?php
}    
?>
	</body>       
 </HTML>


<?php
// http://www.kwelangasolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/transaction/PaymentCapture1.php


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
 
    if (isset($_POST['Customer']))   $postCustomer    = $_POST['Customer'];   else $postCustomer="";
    if (isset($_POST['REASONCODE'])) $postREASONCODE  = $_POST['REASONCODE']; else $postREASONCODE="";
    if (isset($_POST['INVOICE']))    $postINVOICE     = $_POST['INVOICE'];    else $postINVOICE="";

    $postPAYDATE = (isset($_POST["PAYDATE"])) ? htmlspecialchars($_POST["PAYDATE"]) :  CommonUtils::getUserDate();     
 
      if (isset($_POST['retfinish'])) {
          return;	
      }

      if (isset($_POST['finish'])) {
         if (isset($_POST['select'])) {
            $list = implode(",",$_POST['select']);
            
            $inamt      = $_POST['PaidAmount'];
            $parmsarray = explode("$",$list);
            $recno=0;
            $PayHeader = '';
            $paymentCount = 1;
            

            
            $sumamt = 0; 
            
            foreach($_POST['select'] as $sumrow) {            	
                $sumrecno=trim(substr($sumrow, strpos($sumrow, "#")+1,3));
                $sumamt = $sumamt + trim(str_replace(" ",'',$inamt[$sumrecno]));
            }

      	    $PaymentsDAO   = new PaymentsDAO($dbConn);
            $errorTO = $PaymentsDAO->SavePaymentValidation(trim(str_replace(" ",'',$postPAYMENTAMOUNT)), trim(str_replace(" ",'',$sumamt)));   
            if($errorTO->type!=FLAG_ERRORTO_SUCCESS) {
   ?>
              <script type='text/javascript' >parent.showMsgBoxError('<?php echo $errorTO->description;?>')</script>
   <?php    
            return;
            }
            
// Insert into her a precheck to see if the alloacted amount does not exceed the total amount - Do need tody
                    
            foreach($_POST['select'] as $row) {

               	$frecno=substr($row, strpos($row, "#")+1,3);

                $Paymarr=$row ."$". $inamt[trim($frecno)] ;
                $parmsarray=explode("$",$Paymarr);
               
            	  $PaymentsTO = new PaymentsTO;
            	  $PaymentsTO->PrincipalUid=$principalId; 
                $PaymentsTO->InvoiceList=$parmsarray[0];
                $PaymentsTO->PaymentType=$parmsarray[2];
                $PaymentsTO->PaymentDate=$parmsarray[4];
                $PaymentsTO->CustomerUid=$parmsarray[1];
                $PaymentsTO->PaymentAmount=$postPAYMENTAMOUNT;
                $PaymentsTO->InvoiceAmount=$parmsarray[5];
                $PaymentsTO->InvoicePaymentAmount=$parmsarray[7];
                $PaymentsTO->CapturedBy=$userUId;
                
               if($PayHeader == '') {
                    $PostPaymentsDAO   = new PostPaymentsDAO($dbConn);
                    $errorTO   = $PostPaymentsDAO->SavePaymentRecordsHeader($PaymentsTO);
                    $phuid     = $errorTO->description;
                    $payseq    = $errorTO->identifier;
                    $PayHeader = 'DONE';
                }
                $PostPaymentsDAO   = new PostPaymentsDAO($dbConn);
                $errorTO = $PostPaymentsDAO->SavePaymentRecordsDetail($PaymentsTO, $phuid, $paymentCount );
                $paymentCount++;                 
            }    
                // *********************************
                if($errorTO->type == FLAG_ERRORTO_SUCCESS){
                	
                	   $PostPaymentsDAO   = new PostPaymentsDAO($dbConn);
                     $errorTO = $PostPaymentsDAO->SavePaymentRecordsToTracking($principalId, $payseq);
                     
                     $returnMessages=new ErrorTO;
                     $returnMessages->type=FLAG_ERRORTO_SUCCESS;
                     $returnMessages->description="Payment Successfully Saved<BR>";
                     $returnMessages->description .= "<BR><a href=javascript:; onclick=window.open('functional/presentations/presentationManagement.php?TYPE=PAYMENTMULT&DSTATUS=Processed&CSOURCE=P&FINDNUMBER=".$payseq."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');>[VIEW/PRINT PAYMENT RECEIPT]</a>";
          
         ?>
                    <script 
                    	  type='text/javascript' >parent.showMsgBoxInfo("<?php echo $returnMessages->description;?>")
                    </script>
        <?php
	              return;
                } 
                // *********************************
         } else {
         	    $errorTO->description= "No Invoices were selected to match <BR>";
        ?>
              <script type='text/javascript' >parent.showMsgBoxError('<?php echo $errorTO->description;?>')</script>
        <?php 
              		
         }

      }
        
// Display List of invoices
      if (isset($_POST['firstform'])) {
            $PaymentsDAO   = new PaymentsDAO($dbConn);
            $errorTO = $PaymentsDAO->CreditFormValidation($_POST['Customer'], $postREASONCODE, $postINVOICE ,$postPAYDATE);   
            
            if($errorTO->type==FLAG_ERRORTO_SUCCESS) {
            	  $PaymentsDAO = new PaymentsDAO($dbConn);
                $umIN        = $PaymentsDAO->getInvoiceToCredit($principalId, trim($postINVOICE));    
                
                if (sizeof($umIN)==0) { 
                $errorTO->description= "Customer Invoice Not Found<BR>";
        ?>
          <script type='text/javascript' >parent.showMsgBoxError('<?php echo $errorTO->description;?>')</script>
        <?php 
          return;   		
     	}
          $matchedtotal = 0;
          foreach ($umIN as $srow) {
          	$matchedtotal = $matchedtotal + $srow['Matched'];
          }
          if (sizeof($umIN)==1 && $matchedtotal == $umIN[0]['Invoice_Total'] ) {
          	  $checked = "Checked";
          } else	{  
          	  $checked = "";
          }	 
               
                $class = 'odd';
   ?>   
                <center>
                  <form name='Invoices' method=post target=''>
                     <br>
                     <table width=50%; style="border:none;">
                        <tr>
                           <td colspan=6; style="font-weight:bold;font-size:2em;text-align:center; font-family: Calibri, Verdana, Ariel, sans-serif;" >Payment / Invoice Matching</td>
                        </tr>
                        <tr>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>
                          <td>&nbsp;</td>                          
                          <td>&nbsp;</td>                          
                        </tr>                                        
                        <tr class="<?php echo $class; ?>">
                          <td style="border:1px solid black; border-collapse: collapse; font-weight:bold;">Customer</td>
                          <td style="border:1px solid black; border-collapse: collapse;"> <?php echo $umIN[0]['Custname'];?> </td>
                          <td style="border:none;">&nbsp;</td>
                          <td style="border:1px solid black; border-collapse: collapse; font-weight:bold;">Amount Received</td>
                          <td colspan=2; style="border:1px solid black; border-collapse: collapse;"><input name='PaymentAmount' id='PaymentAmount' type='text' value= <?php echo $matchedtotal;?>  size=10  maxlength=10 /></td>
                        </tr>
                     </table>
                     <br>
                     <Table width=50%; style="border:none;">
                         <TR class="<?php echo $class; ?>">
                          <TH colspan=2; style="border:1px solid black; border-collapse: collapse; float: center;">Document Number</TH>
                          <TH style="border:1px solid black; border-collapse: collapse; float: center;">Invoice Date</TH>
                          <TH style="border:1px solid black; border-collapse: collapse; float: center;">Invoice Amount</TH>
                          <TH style="border:1px solid black; border-collapse: collapse; float: center;">Amount Paid</TH>
                          <TH style="border:1px solid black; border-collapse: collapse; float: center;">Match</TH>
                        </tr>
   <?php
                $recno = 0;
                foreach ($umIN as $row) {
                     $class = GUICommonUtils::styleEO($class);
 ?>  
                       <TR class="<?php echo $class; ?>">
                          <TD colspan=2; style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo substr($row['document_number'],2,6);?></TD>
                          <TD style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['invoice_date'];?></TD>
                          <TD style="border:1px solid black; border-collapse: collapse; text-align:right;"><?php echo $row['Invoice_Total'];?></TD>
                          <TD column-span:2; style="border:1px solid black; border-collapse: collapse; text-align:right;">
                                  <input name='PaidAmount[]' id='PaidAmount' type='text' value='<?php echo number_format($row['Matched'],2,'.',' ');?>' size=10  maxlength=10 />
                          <TD style="border:2px solid black; border-collapse:collapse; float: center;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['document_master_uid'] ."$" . $postCustomer . "$" . $postPAYMENTTYPE  . "$" . $postPAYMENTAMOUNT . "$" . $postPAYDATE ."$" . $row['Invoice_Total'] . "$#". $recno;?> " <?php echo $checked ?> ></TD>
                       </TR>
                 
<?php   $recno++; 	} ?>
                       <tr>
                       	  <td colspan=6; style="border-top: 2px solid black; text-align:right;">&nbsp</td>
                       </tr>
                     </Table>
                     <table width: 100% ; style="border:none">
                       <tr>
                       	<td colspan=6;>&nbsp</td>
                       </tr>
                     	 <tr>
                          <td column-span:6></td>
                          <td><INPUT TYPE="submit" class="submit" name="finish" value= "Submit Payments"></td>
                          <td><INPUT TYPE="submit" class="submit" name="canfinish" value= "Cancel"></td>
                       </tr>
                    </table>
                  </form>    
                  </center>             
   <?php
           return;
            } else{
   ?>
              <script type='text/javascript' >parent.showMsgBoxError('<?php echo $errorTO->description;?>')</script>
   <?php    }
      }
?>
<center>
<?php	
     $PaymentsDAO = new PaymentsDAO($dbConn);
     $mfCSI = $PaymentsDAO->getPaymentCustomers($principalId);   
     
     $PaymentsDAO = new PaymentsDAO($dbConn);
     $mfCSG = $PaymentsDAO->getPaymentGroups($principalId);        
     
     $mfCS  = array_merge($mfCSI,$mfCSG);
     
?>
    <FORM name='Capture Credit' method=post action=''>
        <table width: 100% ; style="border:none">
        	<tr>
        		<td  style="font-weight:normal;font-size:2em;text-align:left"; font-family: Calibri, Verdana, Ariel, sans-serif; >Capture Customer Credits</td>
        		</tr>
        	<tr>
        		 <td>&nbsp</td>
        		</tr>	        	
        	<tr>
        		<td style="font-weight:normal; font-size:1em">Choose the required parameters</td>
        		</tr>        	
         </table>
        <table width:30% ; >        	
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
            <td colspan=5;>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td colspan=5;>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td>Select Customer</td>
              <td>
                <select name="Customer" id="Customer">
                    <option value="Select Customer">Select Customer</option>
                    <?php foreach($mfCS as $row) { ?>
                      <option value="<?php echo $row['payment_by'] . $row['uid']; ?>"><?php echo $row['Customer']; ?></option>
                    <?php } ?>
                </select>
              </td>
              <td colspan=3;>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td column-span:5>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td style="text-align:left";>Credit Note Date : </td>
              <td column-span: 2; style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("PAYDATE",$postPAYDATE); ?> </td>
              <td colspan=3;>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td column-span:5>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td style="text-align:left";>Credit Note Reason</td>
             <td style="text-align:left";><?php BasicSelectElement::getDocumentReasonByAssociatedStatus("REASONCODE","","N","N",null,null,null,$dbConn, '95'); GUICommonUtils::requiredField();?>
             <td colspan=3;>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td colspan=5;>&nbsp</td>
          </tr>
         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td style="text-align:left";>Enter Invoice Number</td>
              <td colspan=4; style="text-align:left"><input type="text" name="INVOICE"><br></td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               <td colspan=5;>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               <td style="column-span:1; text-align:center;">&nbsp</td>
               <td style="column-span:1; text-align:left;">
                	<INPUT TYPE="submit" class="submit" name="firstform" value= "Fetch Invoice Details">
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

	</body>       
 </HTML>


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
		  input.num-lg{
			 font-size:12px;
			 padding:4px 8px;
			 height:auto;
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
      if (isset($_POST['PaymentType'])) $postPAYMENTTYPE = $_POST['PaymentType']; else $postPAYMENTTYPE="";
      if (isset($_POST['PaymentAmount'])) $postPAYMENTAMOUNT = $_POST['PaymentAmount']; else $postPAYMENTAMOUNT="0.00";
      if (isset($_POST['AmountRemain']))  $postUNALLOCAMOUNT = $_POST['AmountRemain'];  else $postUNALLOCAMOUNT="0.00";

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
              unset($_POST['firstform']);
              unset($_POST['finish']);
            }  else {
            	
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
                    $PaymentsTO->UnAllocatedAmount=$postUNALLOCAMOUNT;
                    $PaymentsTO->InvoicePaymentAmount=$parmsarray[7];
                    $PaymentsTO->CapturedBy=$userUId;
                
                    if($PayHeader == '') {
                        $PostPaymentsDAO   = new PostPaymentsDAO($dbConn);
                        $errorTO   = $PostPaymentsDAO->SavePaymentRecordsHeader($PaymentsTO);
                        $phuid     = $errorTO->description;
                        $payseq    = $errorTO->identifier;
                                               
                        if ($postUNALLOCAMOUNT <> 0) {
                           // Save un allocated amount in allocations - Once
                           $PostPaymentsDAO   = new PostPaymentsDAO($dbConn);
                           $errorTO = $PostPaymentsDAO->UpdateAllocations($PaymentsTO, $phuid, $PayHeader);
                        }
                        $PayHeader = 'DONE';
                        
                    }
                    $PostPaymentsDAO   = new PostPaymentsDAO($dbConn);
                    $errorTO = $PostPaymentsDAO->SavePaymentRecordsDetail($PaymentsTO, $phuid, $paymentCount, $payseq );
                    $paymentCount++;  
                    
                    // Update allocations table
                    
                    $PostPaymentsDAO   = new PostPaymentsDAO($dbConn);
                    $errorTO = $PostPaymentsDAO->UpdateAllocations($PaymentsTO, $phuid, $PayHeader);                                   
                }    
                // *********************************
                if($errorTO->type == FLAG_ERRORTO_SUCCESS){
          	
                    $PostPaymentsDAO   = new PostPaymentsDAO($dbConn);
                    $errorTO = $PostPaymentsDAO->SavePaymentRecordsToTracking($principalId, $payseq);
               
                    $returnMessages=new ErrorTO;
                    $returnMessages->type=FLAG_ERRORTO_SUCCESS;
                    $returnMessages->description="Payment Successfully Saved<BR>";
                    $returnMessages->description .= "<BR><a href=javascript:; onclick=window.open('functional/presentations/presentationManagement.php?TYPE=PAYMENTMULT&DSTATUS=Processed&CSOURCE=P&FINDNUMBER=".$payseq."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');>[VIEW/PRINT PAYMENT RECEIPT]</a>";
                    unset($_POST['firstform']);
                    unset($_POST['finish']);
 
   ?>
                   <script 
              	         type='text/javascript' >parent.showMsgBoxInfo("<?php echo $returnMessages->description;?>")
                   </script>
    <?php
                } 
                // *********************************
            }    
         } else {
         	    $errorTO->description= "No Invoices were selected to match <BR>";
    ?>
              <script type='text/javascript' >parent.showMsgBoxError('<?php echo $errorTO->description;?>')</script>
    <?php
              unset($_POST['firstform']);
              unset($_POST['finish']);
         }

      }
// **********************************************************************************************************************************************************        
// Display List of invoices
      if (isset($_POST['firstform'])) {
            $PaymentsDAO   = new PaymentsDAO($dbConn);
            $errorTO = $PaymentsDAO->PaymentValidation($_POST['Customer'], $postPAYMENTTYPE, $postPAYMENTAMOUNT ,$postPAYDATE);   
            
            if($errorTO->type==FLAG_ERRORTO_SUCCESS) {
            	  $PaymentsDAO = new PaymentsDAO($dbConn);
                $umIN       = $PaymentsDAO->getUnAllocatedInvoiceAmts($principalId, trim(substr($_POST['Customer'],1,10)));    
                
                if (sizeof($umIN)>0) { 
                   $matchedtotal = 0;
                   foreach ($umIN as $srow) {
                         $matchedtotal = $matchedtotal + $srow['Invoice_Total'] + $srow['Allocation_Amt'];
                   }
                   if (sizeof($umIN)==1 && $matchedtotal == $umIN[0]['Invoice_Total'] + $umIN[0]['Allocation_Amt'] ) {
                         $checked = "Checked";
                   } else	{  
                         $checked = "";
                   }	 
                   $class = 'odd';    ?>   
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
                              <tr class="<?php echo $class; ?>">
                                <th colspan=2; style="border:1px solid black; border-collapse: collapse; float: center;">Document Number</TH>
                                <th style="border:1px solid black; border-collapse: collapse; float: center;">Invoice Date</TH>
                                <th style="border:1px solid black; border-collapse: collapse; float: center;">Invoice Amount</TH>
                                <th style="border:1px solid black; border-collapse: collapse; float: center;">Amount Paid</TH>
                                <th style="border:1px solid black; border-collapse: collapse; float: center;">Match</TH>
                              </tr>
                              <?php
                              $recno = 0;
                              foreach ($umIN as $row) {
                                 $class = GUICommonUtils::styleEO($class);
 ?>               
                                 <tr id='payments' class="payment-row <?php echo $class; ?>">
                                    <td colspan=2; style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo substr($row['document_number'],2,6);?></TD>
                                    <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['invoice_date'];?></TD>
                                    <td style="border:1px solid black; border-collapse: collapse; text-align:right;"><?php echo $row['Invoice_Total'];?></TD>
                                    <td column-span:2; style="border:1px solid black; border-collapse: collapse; text-align:right;">
                                        <input name='PaidAmount[]' class='PaidAmount' type='text' value='<?php echo number_format($row['Invoice_Total'] + $row['Allocation_Amt'],2,'.','');?>' size=10  maxlength=10 />
                                    <td style="border:2px solid black; border-collapse:collapse; float: center;">
										                    <INPUT TYPE="checkbox" class="myInvoiceCheck" id="myCheck<?php echo $recno . "#" . $postPAYMENTAMOUNT ?>" name="select[]" value= "<?php echo $row['document_master_uid'] ."$" . $postCustomer . "$" . $postPAYMENTTYPE  . "$" . $postPAYMENTAMOUNT . "$" . $postPAYDATE ."$" . $row['Invoice_Total'] . "$#". $recno;?> " <?php echo $checked ?> >
									                  </td>
                                 </tr>
                              
                              <?php   $recno++; 	} ?>
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 	   <td colspan=6;>&nbsp;</td>
                                 </tr>
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          	         <td colspan="1" style="border-top: 1px solid black; text-align:right;">&nbsp</td>
                          	         <td colspan="5" style="border-top: 1px solid black; text-align:right;">
										                 <div>
											                  Total Allocated : <input type="text" name="AmountTotal" class="num-lg" size="6" value="0.00" readonly="readonly" />
										                    
											                  Unallocated Amount : <input type="text" name="AmountRemain" class="num-lg" size="6" value="0.00" readonly="readonly" />
										                 </div>
									                   </td>
										<!-- 
										difference it optional, but really the same as REMAINING for allocation
										DIFF:
										<input type="text" name="AmountDiff" class="num-lg" size="6" value="0.00" readonly="readonly" />
										/-->
									 </td>                                
                                 </tr>
                           </table>
                           <table width: 100% ; style="border:none">
                                <tr>
                                    <td colspan=6;>&nbsp</td>
                                </tr>
                        	       <tr>
                                    <td column-span:6></td>
                                    <td><INPUT TYPE="submit" class="submit" name="finish" value= "Submit Payments" ></td>
                                    <td><INPUT TYPE="submit" class="submit" name="canfinish" value= "Cancel" ></td>
                                </tr>
                           </table>
                       </form>    
                   </center>
              
					<script>
					
						//the form element.
						var formEle = $("form[name=Invoices]");
						
						//code for total updates!
						function updateTotal() {
														
							var paymentTotal = parseFloat(formEle.find("#PaymentAmount").val());
							var amountTotal = 0;
																										
							var checkSelects = formEle.find("input.myInvoiceCheck:checked");
							$(checkSelects).each(function(k,checkEle){								
								var invoiceAmt = parseFloat($(checkEle).parents(".payment-row").find("input.PaidAmount").val());
								if(invoiceAmt > 0){
									amountTotal += invoiceAmt									
								}
							});
							
							var amountRemain = paymentTotal - amountTotal;	
							
							// update totals.							
							$(formEle).find('[name=AmountTotal]').val(formatDecimal(amountTotal));
							$(formEle).find('[name=AmountRemain]').val(formatDecimal(amountRemain));
							//$(formEle).find('[name=AmountDiff]').val(formatDecimal(amountTotal - paymentTotal));														
						}
						
						//listen for changes.
						formEle.find("checkbox,input").change(function(){
							updateTotal();
						});

						//do it onload
						updateTotal();
						
							
						// format val to n number of decimal places
						// modified version of Danny Goodman's (JS Bible)
						function formatDecimal(val, n) {
							n = n || 2;
							var str = "" + Math.round ( parseFloat(val) * Math.pow(10, n) );
							while (str.length <= n) {
								str = "0" + str;
							}
							var pt = str.length - n;
							return str.slice(0,pt) + "." + str.slice(pt);
						}

					</script>
                   
                <?php               
                } else {
                      $errorTO->description= "No Un Paid Customer Invoices <BR>";
                ?>
                     <script type='text/javascript' >parent.showMsgBoxError('<?php echo $errorTO->description;?>')</script>
                <?php
                      unset($_POST['firstform']);
                      unset($_POST['finish']);
                }  
            } else{    ?>
                  <script type='text/javascript' >parent.showMsgBoxError('<?php echo $errorTO->description;?>')</script>
   <?php    unset ($_POST['finish']);
            unset ($_POST['firstform']);
            }
      }
// **********************************************************************************************************************************************************        
?>

<center>
<?php	
     $PaymentsDAO = new PaymentsDAO($dbConn);
     $mfCSI = $PaymentsDAO->getPaymentCustomers($principalId);   
     
     $PaymentsDAO = new PaymentsDAO($dbConn);
     $mfCSG = $PaymentsDAO->getPaymentGroups($principalId);        
     
     $mfCS  = array_merge($mfCSI,$mfCSG);
     
if(!isset($_POST['firstform']) && !isset($_POST['finish'])) : ?> 
    <FORM name='Capture Payment' method=post action=''>
        <table width: 100% ; style="border:none">
        	<tr>
        		<td  style="font-weight:normal;font-size:2em;text-align:left"; font-family: Calibri, Verdana, Ariel, sans-serif; >Capture Customer Payments</td>
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
                    <?php foreach($mfCS as $row) : 
						$checked = (($_POST['Customer']??"") == $row['payment_by'] . $row['uid'] ? "selected='selected'" : "");
					?>						
						<option <?php echo $checked ?> value="<?php echo $row['payment_by'] . $row['uid']; ?>"><?php echo $row['Customer']; ?></option>
                    <?php endforeach ?>
                </select>
              </td>
              <td colspan=3;>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td column-span:5>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td style="text-align:left";>Payment Type</td>
             <td style="text-align:left";><select name="PaymentType" id="PaymentType">
             	                              <option value="2" <?php echo (($_POST['PaymentType']??"") == "2" ? "selected='selected'" : "") ?>>EFT</option>
             	                              <option value="1" <?php echo (($_POST['PaymentType']??"") == "1" ? "selected='selected'" : "") ?>>CASH</option>
             	                              <option value="4" <?php echo (($_POST['PaymentType']??"") == "4" ? "selected='selected'" : "") ?>>Card</option>
             	                            </select></td>
             <td colspan=3;>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td column-span:5>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td style="text-align:left";>Processed Date : </td>
              <td column-span: 2; style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("PAYDATE",$postPAYDATE); ?> </td>
              <td colspan=3;>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               <td column-span:5>&nbsp</td>
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
             <td column-span:5>&nbsp</td>
           </tr>  
        </table>
    </FORM>
    </center> 

<!-- Capture Payment /-->
<?php endif; ?>
 

</body>       
 </HTML>


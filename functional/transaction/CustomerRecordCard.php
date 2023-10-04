<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
		include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
		include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');		
    include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
  
    
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

		</HEAD>
<body>

<?php

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      if (isset($_POST['customer'])) $postCustomer = $_POST['customer']; else $postCustomer="";
      if (isset($_POST['noitd'])) $postnoitd = $_POST['noitd']; else $postnoitd="";
    
      $fdate = date_create(CommonUtils::getUserDate());
      date_sub($fdate, date_interval_create_from_date_string('60 days'));
    
      $postFROMDATE = (isset($_POST["FROMDATE"])) ? htmlspecialchars($_POST["FROMDATE"]) :  date_format($fdate, 'Y-m-d');;
      $postTODATE   = (isset($_POST["TODATE"])) ? htmlspecialchars($postTODATE=$_POST["TODATE"]) : CommonUtils::getUserDate();     
      
      if (isset($_POST['select'])) {
         if (isset($_POST['finish'])) {
       	    $list =implode(",",$_POST['select']);
       	    $list2 = explode(",",$list);
//       	    print_r( $_POST['psummary']) ;
       	    
       	    if (isset($_POST['psummary'])) {
       	    	  if(substr($_POST['psummary'][0],0,2)=="PR") {
                ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Standing Order Creation Complete' <BR><BR><BR><a href=javascript:; onclick='functional/transaction/PrintCustomerRecordCard.php>[Return to CRC]</a>')
                       window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=WAYBILL&FINDNUMBER=<?PHP echo $list2[0]; ?>');
                    </script> 
                <?php 
                return;

                } elseif(substr($_POST['psummary'][0],0,2)=="SU") {
                ?>
                    <script type='text/javascript'>
                       window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CRSUMMARY&PSTORE=<?PHP echo trim(substr($_POST['psummary'][0],20,10)); ?>&STARTDATE=<?PHP echo $postTODATE; ?>&ENDDATE=<?PHP echo $postFROMDATE; ?>&NOOFDOCS=<?PHP echo trim(substr($_POST['psummary'][0],31,2)); ?>' , "_blank", "toolbar=no,scrollbars=yes,resizable=yes,width=750,height=600" );</script>
                    </script> 
                <?php 
                return;
       	        } elseif(substr($_POST['psummary'][0],0,2)=="CP") {
       	        	  $ddi = HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER. "functional/transaction/quotationCapture.php?COPYDOCUMENT=Y&DOCTYPE=27&DOCMASTID=" . trim(str_replace('-','',substr($_POST["psummary"][0],12,8)));
                ?>
       	        	  <script type='text/javascript'>
                       var parentBody = window.parent.document.body;
                       $("#content", parentBody).attr('src','<?php echo $ddi; ?>');
                    </script> 
                    <?php 
                    return;
                } elseif(substr($_POST['psummary'][0],0,2)=="SO") {
                    ?>
                       <script type='text/javascript'>window.open("<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/so/SetUpStandingOrder.php?DMUID=<?PHP echo (implode(",",$_POST["psummary"])); ?>" , "_self", "toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400" );</script> 
                    <?php 
                    return;
        	      } else { 
?>
      	 	     <script type='text/javascript' >parent.showMsgBoxError("No Action Selected<BR><BR>")</script> 
<?php
            }     
       	    return;
         }  
         }
      }   
      if (isset($_POST['select'])) {
     	
     	if($postCustomer == '' || $postCustomer == 'Select Customer') {
?>
         <script type='text/javascript' >parent.showMsgBoxError("No Customer Selected<BR><BR>")</script>
<?php 
          return;   		
     	}
        $inv=array();
     	
     	  foreach($_POST['doctyp'] as $invselected){
                array_push($inv,$invselected);
         }
         $invlist = implode(',',$inv);
          
        $transactionDAO = new TransactionDAO($dbConn);
        $mfCSD = $transactionDAO->getCustomerRecordsDetail($principalId,$postCustomer,$postFROMDATE,$postTODATE);
        if (sizeof($mfCSD)==0) { 
        	
        ?>
          <script type='text/javascript' >parent.showMsgBoxError("No Customer Invoices found for this period<BR><BR>")</script>
        <?php 
          return;   		
     	}
       	?>
		    <center>
          <FORM name='crcard' method=post target=''>
            <Table style="width:80%; border-top:1px solid black; border-collapse: collapse; float: center;">
             <tr>
               <td colspan="9" style="border-right: 1px solid black; border-left: 1px solid black; text-align: left; font-weight: bold;">Customer Transaction Record</td>            	
             <tr>
             	 <td colspan="9" style="border-collapse:collapse; border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">Customer Details</td>
             </tr>
<?php
           $cl = "even";
           $addstor = "";
           $headerstor = "";
           $docloop = 0;
           
//       print_r($mfCSD);
           
        foreach ($mfCSD as $row) {
        	

           if($docloop < $postnoitd ) {        	
               if ($addstor == "") {
?>                <tr>
                     <td colspan="3" style="border-right: 1px solid black; border-left: 1px solid black; text-align: left;"><?php echo $row['deliver_name'];?></td>
                     <td colspan="3" style="border-left:  1px solid black; text-align: right;">Balances Outstanding as at:</td>
                     <td colspan="2" style="text-align: left;">2018-04-30</td>
                     <td colspan="1" style="border-right:1px solid black; text-align: left;">&nbsp;</td> 
                  </tr>
                  <tr class="<?php echo $cl; ?>">
                     <td colspan="3" style="border-right: 1px solid black; border-left: 1px solid black; text-align: left;"><?php echo $row['deliver_add1'];?></td>
                     <td style="border-left:1px solid black; text-align: right;">Total Due</td>
                     <td style="border-left:1px solid black; text-align: right;">Current</td>
                     <td style="border-left:1px solid black; text-align: right;">30 Days</td>
                     <td style="border-left:1px solid black; text-align: right;">60 Days</td>
                     <td style="border-left:1px solid black; text-align: right;">90 Days</td>
                     <td style="border-left:1px solid black; border-right:1px solid black; text-align: right;">120+</td>
                  </tr>
                  <tr class="<?php echo $cl; ?>">
                     <td colspan="3" style="border-right: 1px solid black; border-left: 1px solid black; text-align: left;"><?php echo $row['deliver_add2'];?></td>
                     <td style="border-left:1px solid black; text-align: right;"><?php echo trim(number_format(round($row['td'],2),2,"."," "));?></td>
                     <td style="border-left:1px solid black; text-align: right;"><?php echo trim(number_format(round($row['curr'],2),2,"."," "));?></td>
                     <td style="border-left:1px solid black; text-align: right;"><?php echo trim(number_format(round($row['30d'],2),2,"."," "));?></td>
                     <td style="border-left:1px solid black; text-align: right;"><?php echo trim(number_format(round($row['60d'],2),2,"."," "));?></td>
                     <td style="border-left:1px solid black; text-align: right;"><?php echo trim(number_format(round($row['90d'],2),2,"."," "));?></td>
                     <td style="border-left:1px solid black; border-right:1px solid black; text-align: right;"><?php echo trim(number_format(round($row['120d'],2),2,"."," "));?></td>
                  </tr>
                  <tr class="<?php echo $cl; ?>">
                     <td colspan="3" style="border-right: 1px solid black; border-left: 1px solid black; text-align: left;"><?php echo $row['deliver_add3'];?></td>
                     <td style="border-left:1px solid black; border-top:1px solid black; border-right:1px solid black; border-right:1px solid black; border-right:1px solid black; text-align: right;">Order Frequency</td>
                     <td style="border-left:1px solid black; border-top:1px solid black; border-right:1px solid black; border-right:1px solid black; text-align: right;">Monthly</td>
                     <td style="border-left:1px solid black; border-top:1px solid black; border-right:1px solid black; text-align: right;">Notes</td>
                     <td colspan="3" style="border-left:1px solid black; border-right:1px solid black; border-top:1px solid black; border-right:1px solid black; text-align: right;">&nbsp;</td>
                  </tr>
<?php             $cl = "even";         ?>    	
                  <tr>
                     <td style="width:8%;  font-weight:bold; border:1px solid black; border-collapse:collapse; text-align:center;">Invoice No.</td>
                     <td style="width:10%; font-weight:bold; border:1px solid black; border-collapse:collapse; text-align:center;">Doc Type</td>
                     <td style="width:12%; font-weight:bold; border:1px solid black; border-collapse:collapse; text-align:center;">Invoice Date</td>
                     <td style="width:25%; font-weight:bold; border:1px solid black; border-collapse:collapse; text-align:center;">Product</td>
                     <td style="width:12%; font-weight:bold; border:1px solid black; border-collapse:collapse; text-align:center;">Prod Code</td>
                     <td style="width:10%; font-weight:bold; border:1px solid black; border-collapse:collapse; text-align:center;">Quantity</td>
                     <td style="width:8%; font-weight:bold; border:1px solid black; border-collapse:collapse; text-align:center;">Price</td>
                     <td style="width:8%; font-weight:bold; border:1px solid black; border-collapse:collapse; text-align:center;">Total</td>
                     <td style="width:11%;  font-weight:bold; border:1px solid black; border-collapse:collapse; text-align:center;">Select</td>
<?php
                  	 $addstor = "1"; 
                  	;
                  	 $cl = GUICommonUtils::styleEO($cl);
               }
               if ($headerstor <> $row['document_number']) {
            	    $cl = "odd";
?>                   <tr>
                        <td style="background-color:#f2f3f4; border:1px solid black; border-collapse:collapse; text-align:center;"><?php echo trim(substr($row['document_number'],2,6));?></td>
                        <td style="background-color:#f2f3f4; border:1px solid black; border-collapse:collapse; text-align:center;"><?php echo trim($row['DocType']);?></td>
                        <td style="background-color:#f2f3f4; border:1px solid black; border-collapse:collapse; text-align:center;"><?php echo $row['invoice_date'];?></td>
                        <td style="background-color:#f2f3f4; border:1px solid black; border-collapse:collapse; text-align:right;">&nbsp;</td>
                        <td style="background-color:#f2f3f4; border:1px solid black; border-collapse:collapse; text-align:right;">&nbsp;</td>
                        <td style="background-color:#f2f3f4; border:1px solid black; border-collapse:collapse; text-align:right;"><?php echo trim(number_format($row['cases'],0,","," "));?></td>
                        <td style="background-color:#f2f3f4; border:1px solid black; border-collapse:collapse; text-align:right;">&nbsp;</td>
                        <td style="background-color:#f2f3f4; border:1px solid black; border-collapse:collapse; text-align:right;"><?php echo trim(number_format(round($row['invoice_total'],2),2,"."," "));?></td>
                        <td style="background-color:#f2f3f4; border:1px solid black; border-collapse:collapse; text-align:right;"><INPUT TYPE="radio" name="select[]" value= "<?php echo $row['document_number'];?>"></td>
                    </tr>            	
<?php
            	      $headerstor = $row['document_number'];
            	      $docloop++;
               }
           } 
            
           if ( $headerstor == $row['document_number'] ) {
?>
                 <tr >
                     <td style="border-collapse:collapse; border-left: 1px solid black;">&nbsp;</td>
                     <td style="border:none;">&nbsp;</td>
                     <td style="border:none;">&nbsp;</td>
                     <td style="border-left: 1px solid black; border-collapse: collapse; text-align:left;"><?php echo $row['product_description'];?></td>
                     <td style="border-left: 1px solid black; border-collapse: collapse; text-align:left;"><?php echo $row['product_code'];?></td>
                     <td style="border-left: 1px solid black; border-collapse: collapse; text-align:right;"><?php echo trim(number_format(round($row['document_qty'],0),0,"."," "));?></td>
                     <td style="border-left: 1px solid black; border-collapse: collapse; text-align:right;"><?php echo trim(number_format(round($row['net_price'],2),2,"."," "));?></td>
                     <td style="border-left: 1px solid black; border-collapse: collapse; text-align:right;"><?php echo trim(number_format(round($row['total'],2),2,"."," "));?></td>
                     <td style="border-left: 1px solid black; border-right: 1px solid black; ">&nbsp;</td>
                </tr>         
<?php   	    
            }
  
        }
?> 

              <tr>
              	<td colspan="9" style="border-top: 1px solid black; border-collapse: collapse ">&nbsp;</td>
              </tr>               
             </table>
             <table>
             	<br><br>
             	<tr>
             		<td>Print Summary</td>
             		<td><INPUT TYPE="radio" name="psummary[]" value= "<?php echo "SU-" . $row['document_number'] ."-". $row['duid'] ."-". str_pad($row['psmuid'],10,'0',STR_PAD_LEFT) ."-". str_pad($postnoitd,2,'0',STR_PAD_LEFT);?>"></td>
             		<td>Print Invoice</td>
             		<td><INPUT TYPE="radio" name="psummary[]" value= "<?php echo "PR-" . $row['document_number'] ."-". $row['duid'] ."-". str_pad($row['psmuid'],10,'0',STR_PAD_LEFT) ."-". str_pad($postnoitd,2,'0',STR_PAD_LEFT);?>"></td>
             		<td>Copy Order</td>
             		<td><INPUT TYPE="radio" name="psummary[]" value= "<?php echo "CP-" . $row['document_number'] ."-". $row['duid'] ."-". str_pad($row['psmuid'],10,'0',STR_PAD_LEFT) ."-". str_pad($postnoitd,2,'0',STR_PAD_LEFT);?>"</td>
             		<td>Create Standing Order</td>
             		<td><INPUT TYPE="radio" name="psummary[]" value= "<?php echo "SO-" . $row['document_number'] ."-". $row['duid'] ."-". str_pad($row['psmuid'],10,'0',STR_PAD_LEFT) ."-". str_pad($postnoitd,2,'0',STR_PAD_LEFT);?>"</td>
             	</tr>
             	
             </table> 
              <br><br>
             <table style="border: none";> 
              <tr>
                <td colspan="4"></td>
        	      <td><INPUT TYPE="submit" class="submit" name="finish" value= "Finish"></td>
              </tr>
             </table>
           </FORM>  
	      </center>
<?php
       return;
    }
$transactionDAO = new TransactionDAO($dbConn);
$mfCS = $transactionDAO->getCurrentCustomers($principalId);    
    
     $class = 'odd';
?>
<center>
	 <FORM name='Select Customer' method=post action=''>
        <table width:"100%"; style="border:none">
        	<tr>
        		<td colspan"0"; style="font-weight:normal;font-size:2em;text-align:left"; font-family: Calibri, Verdana, Ariel, sans-serif; >Find Customer Records</td>
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
             <td Colspan="5">&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">



           	 <td>Select Customer</td>
              <td>
              	 <select name="customer" id="customer">
              			 <option value="Select Customer">Select Customer</option>
              			<?php foreach($mfCS as $row) { ?>
              					<option value="<?php echo $row['uid']; ?>"><?php echo $row['deliver_name']; ?></option>
              			<?php } ?>
              		</select>
              </td>
              <td>&nbsp</td>
              <td>&nbsp</td>
              <td>&nbsp</td>




          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td Colspan="5">&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td style="text-align:left";>Start Processed Date : </td>
              <td colspan="2"; style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
              <td>&nbsp</td>
              <td>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td Colspan="5">&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td style="text-align:left";>End Processed Date : </td>
             <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("TODATE",$postTODATE); ?> </td>
             <td>&nbsp</td>
             <td>&nbsp</td>    
          </tr>      
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               <td Colspan="5">&nbsp</td>
           </tr> 
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           	   <td>Number of documents</td>
           	   <td><select name="noitd"> 
           	   	      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4</option>
                      <option value="5">5</option>
                      <option value="6">6</option>
                      <option value="7">4</option>
                      <option value="8">8</option>
                      <option value="9">9</option>
                      <option value="10">10</option>
                   </select>
               </td>
               <td>&nbsp</td>
               <td>&nbsp</td>
               <td>&nbsp</td>
           </tr>
           <tr> 
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               <td Colspan="5">&nbsp</td>
           </tr> 
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           	   <td>Document Types</td>
                  <td><span style="Vertical-Align:center;">Invoices&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span><input type="checkbox" name="doctyp[]" value="1" checked></span></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
            </tr>
<?php $class = 'even'; ?>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           	   <td>&nbsp;</td>
                  <td><span style="Vertical-Align:center;">Credit&nbsp;Note&nbsp;&nbsp;&nbsp;</span><span><input type="checkbox" name="doctyp[]" value="4"></span></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
            </tr>
<?php $class = 'even'; ?>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           	   <td>&nbsp;</td>
                  <td><span style="Vertical-Align:center;">Payments&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span><input type="checkbox" name="doctyp[]" value="88"></span></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
            </tr>



           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               <td Colspan="5">&nbsp</td>
           </tr>
             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
        	     <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="select" value= "Get Customer Records"></td>
             </tr>          
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td Colspan="5">&nbsp</td>
           </tr>  
 				</table>
    </center> 
	</body>       
 </HTML>
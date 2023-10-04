<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/NewTransactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."DAO/NewPostTransactionDAO.php");

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      if (isset($_POST["INVOICE"])) $postINVOICE=test_input($_POST["INVOICE"]); else $postINVOICE = '';
      if (isset($_POST["DMUID"]))   $postDMUID=($_POST["DMUID"]); else $postDMUID = ''; 
      if (isset($_POST["CHECK"]))   $postCHECK=($_POST["CHECK"]); else $postCHECK = 'DN'; 
            
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

    	td.head2 {border-style:none;
    		        font-size:13px;
    		        font-weight: bold; 
    		        font-family: Calibri, Verdana, Ariel, sans-serif; 
    		        }
    	td.head2a {border-style:none;
    		        font-size:13px;
    		        font-weight: normal; 
    		        font-family: Calibri, Verdana, Ariel, sans-serif; 
    		        }      
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
           // Update Document to Invoiced Status
           $postINVOICEDATE = (isset($_POST["INVOICEDATE"])) ? htmlspecialchars($_POST["INVOICEDATE"]) :  CommonUtils::getUserDate();
      	   if (isset($_POST["OWNINVOICE"])) $postOWNINVOICE=test_input($_POST["OWNINVOICE"]); else $postOWNINVOICE = '';
      	   foreach($_POST['recId'] as $key=>$value){
      	   	    if($_POST['ordQty'][$key] <> $_POST['InvQty'][$key]) {
      	   	    	  $PostNewTransactionDAO = new PostNewTransactionDAO($dbConn);
      	   	        $result = $PostNewTransactionDAO->invoiceDetailLine($value, $_POST['InvQty'][$key], $_POST['NetPrice'][$key] ); 
      	   	        if($result->type <> FLAG_ERRORTO_SUCCESS) { ?>
                       <script type='text/javascript'>parent.showMsgBoxError('Update line Bomb Out !!! - Contact Support ')</script> 
                       <?php 
                       return;
                    }
                }       
           }
           $postNewTransactionDAO = new PostNewTransactionDAO($dbConn);
      	   $result = $postNewTransactionDAO->invoiceHeader($postDMUID, $postOWNINVOICE, $postINVOICEDATE, DST_INVOICED );      // DST_INPICK  DST_INVOICED
 	
           $postNewTransactionDAO = new PostNewTransactionDAO($dbConn);
      	   $result = $postNewTransactionDAO->recalulateHeader($postDMUID);
      	        
           if($result->type == FLAG_ERRORTO_SUCCESS) { ?>
                 <script type='text/javascript'>parent.showMsgBoxInfo('Document Sucessfully Invoiced ')</script> 
              <?php
              unset($_POST['firstform']);
              unset($_POST['confirmform']) ;
           } else {
           	echo 'Oh Shit';
           	return;
           }         
    }
// **********************************************************************************************************************************
      if (isset($_POST['firstform'])) {
          if ($postINVOICE !== '') {
              $transactionDAO = new NewTransactionDAO($dbConn);
             	$mfDDU = $transactionDAO->getDocumentToInvoice($principalId,$postINVOICE, $postCHECK);
              if (sizeof($mfDDU)<>0) {
              	  $docCount = 0;
              	  $docStore = '';
              	  foreach($mfDDU as $crow) { 
              	  	   if($docStore <> $crow['document_number']) {
              	  	   	  $docCount++;
              	       }
              	       $docStore = $crow['document_number'];
              	  }
              	  if($docCount == 1) {    
              	       if(in_array($mfDDU[0]['document_status_uid'], array(DST_UNACCEPTED,DST_ACCEPTED))) {?>
                       <center>
                          <form name='Select Invoice' method=post action=''>
                             <table width="1000"; style="border:none">
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td width="5%"; ">&nbsp</td>
                                     <td width="23%";">&nbsp</td>         
                                     <td width="45%";">&nbsp</td>         
                                     <td width="12%";">&nbsp</td>         
                                     <td width="5%";">&nbsp</td>         
                                     <td width="5%";">&nbsp</td>         
                                     <td width="5%"; ">&nbsp</td>         
                                 </tr> 
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                                     <td class=head1 colspan="7"; style="text-align:center">Convert a Document to Invoiced</td>
                                 </tr>
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                     <td colspan="7"; style="text-align:left">&nbsp</td>
                                 </tr>
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 	   <td class=head2 >&nbsp</td>
                                     <td class=head2  style="text-align:left;">Customer  :</td>
                                     <td class=head2a style="text-align:left;"><?php echo trim($mfDDU[0]['deliver_name']); ?></td> 
                                     <td class=head2 >&nbsp</td>
                                     <td class=head2  style="text-align:right;">Document No. </td>
                                     <td class=head2a style="text-align:right;"><?php echo substr($mfDDU[0]['document_number'],2,6) ." "; ?></td>
                                     <td class=head2 >&nbsp</td>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                     <td colspan="7"; style="text-align:left">&nbsp</td>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 	   <td class=head2 >&nbsp</td>
                                     <td class=head2  style="text-align:left;">PO Number  :</td>
                                     <td class=head2a style="text-align:left;"><?php echo trim($mfDDU[0]['customer_order_number']); ?></td> 
                                     <td class=head2 >&nbsp</td>
                                     <td class=head2  style="text-align:right;">&nbsp;</td>
                                     <td class=head2a style="text-align:right;">&nbsp;</td>
                                     <td class=head2 >&nbsp</td>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                     <td colspan="7"; style="text-align:left">&nbsp</td>
                                  </tr>                          
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                     <td class=head2  style="border:none">&nbsp</td>
                                     <td class=head2  style="text-align:left">Invoice Date :</td>
                                     <td class=head2a style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("INVOICEDATE",$mfDDU[0]['invoice_date']); ?> </td>
                                     <td class=head2  style="text-align:right";>Invoice Number</td>
                                     <td class=head2a colspan="2"; style="text-align:right"><input type="text" name="OWNINVOICE"></td>
                                     <td style="border:none"><input type='hidden' name='DMUID' id='DMUID'  value='<?php echo $mfDDU[0]['dmUid']; ?>'></td>             
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                     <td colspan="7"; style="text-align:left">&nbsp</td>
                                  </tr>
                                  
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td class=head2 style="border:none">&nbsp</td>
                                     <td class=head2 style="text-align:left;">Product Code</td>
                                     <td class=head2 style="text-align:left;">Product</td>
                                     <td class=head2 style="text-align:right;">Ordered Qty</td>
                                     <td class=head2 style="text-align:right;">Invoice Qty</td>
                                     <td class=head2 style="text-align:right;">Nett Price</td>
                                     <td class=head2 style="text-align:right;">&nbsp</td>
                                  </tr>
                                  <?php
                                     foreach($mfDDU as $row) { ?>
                                     	  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                           <td class=head2 style="border:none">&nbsp</td>
                                           <td class=head2a style="text-align:left;"><?php echo trim($row['product_code']); ?></td>
                                           <td class=head2a style="text-align:left;"><?php echo trim($row['product_description']); ?></td>
                                           <td class=head2a style="text-align:right;"><?php echo trim($row['ordered_qty']); ?><input type='hidden' name='ordQty[]' id='ordQty'  value='<?php echo trim($row['ordered_qty']); ?>'></td></td>
                                           <td class=head2a style="text-align:right;"><input name='InvQty[]' id='InvQty' type='text' value='<?php echo trim($row["ordered_qty"]); ?>' align='right'  size=5  maxlength=15 /></td>
                                           <td class=head2a style="text-align:right;"><input name='NetPrice[]' id='NetPrice' type='text' align='right' size=8  maxlength=8 value='<?php echo trim(round($row["net_price"],2)); ?>' /><td
                                           <td class=head2a style="text-align:right;"><input type='hidden' name='recId[]' id='recId'  value='<?php echo trim($row['ddUID']); ?>'></td>
                                        </tr>
                                    <?php                                     	
                                     }
                                     ?>
                                                                    
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                     <td colspan="7"; style="text-align:left">&nbsp</td>
                                  </tr>
                      	          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                     <td colspan="7"; style="text-align:center"><INPUT TYPE="submit" class="submit" name="confirmform" value= "Submit Line"><INPUT TYPE="submit" class="submit" name="canform" value= "Cancel"></td>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                     <td colspan="7";>&nbsp</td>
                                  </tr>
                             </table>
                          </form>                       
                       </center>
                  <?php
                       } else { ?>
                           <script type='text/javascript'>parent.showMsgBoxInfo('Document Aready invoiced')</script> 
                           <?php 
                           unset($_POST['firstform']);
                           unset($_POST['confirmform']);
                       }
                  } else {?>
                  	      <script type='text/javascript'>parent.showMsgBoxInfo('Multiple Documents found <br> Check Search Number<br>')</script> 
                          <?php 
                          unset($_POST['firstform']);
                          unset($_POST['confirmform']); 
                  	
                  }    
              } else { ?>
                  <script type='text/javascript'>parent.showMsgBoxInfo('Document not Found <br> Check Search Number<br>')</script> 
                  <?php 
                  unset($_POST['firstform']);
                  unset($_POST['confirmform']); 
              } 
          }  else { ?>
              <script type='text/javascript'>parent.showMsgBoxInfo('Document / PO Number cannot be blank')</script> 
          <?php
              unset($_POST['firstform']);
              unset($_POST['confirmform']);   	
          }
     }
if(!isset($_POST['firstform']) && !isset($_POST['confirmform'])) { ?>
    <center>
       <FORM name='Select Invoice' method=post action=''>
            <table width="720"; style="border:none">
               <tr>
                 <td class=head1 >Change Document To Invoiced Status</td>
               </tr>
               <tr>
                 <td>&nbsp</td>
               </tr>	        	
               <tr>
                 <td class=head1 style="font-weight:normal; font-size:1em">Enter the required Document Number or PO Number</td>
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
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                 <td class=head2 style="text-align:left";>Check on PO Number</td>
                 <td colspan="4"; style="text-align:left"><INPUT TYPE="checkbox" name="CHECK" value= "PO" checked></td>
               </tr>                 
                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>              
               
               
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                 <td class=head2 style="text-align:left";>Enter Document / PO Number</td>
                 <td colspan="4"; style="text-align:left"><input type="text" name="INVOICE"></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Document Details">
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
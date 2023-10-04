<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/MaintenanceDAO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/TripSheetDAO.php');
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
		
    if (!isset($_SESSION)) session_start() ;
        $userUId     = $_SESSION['user_id'] ;
        $principalId = $_SESSION['principal_id'] ;

        //Create new database object
        $dbConn  = new dbConnect(); 
        $dbConn->dbConnection();
        $errorTO = new ErrorTO;
/*         // Check Warehouse User
        $AdministrationDAO = new AdministrationDAO($dbConn);
        $uTS = $AdministrationDAO->checkWarehouseUserAdmin($userUId);
         
        if($uTS[0]['category'] <> 'D') {	?>
             <script type='text/javascript'>parent.showMsgBoxError('You are not a warehouse user <br><br> Cannot Continue!! ')</script>
             <?php	
             return;
         }
*/          
          ?>
        
<!DOCTYPE html>
<HTML>
      <HEAD>

         <TITLE>Document Management</TITLE>

         <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
         <link href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
         <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
         <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

         <style>
         </style>
      </HEAD>
      <BODY> 
            
       <script type='text/javascript'>parent.showMsgBoxError('Reset Invoice Status has Moved to Transaction Tracking. -- Select `Manage` in the Processing Detail Column -- Call Alan If you need some Help')</script>
        
        
        <?php
        
        return;
        
     
     
// **************************************************************************************************************************************************      	
          if (isset($_POST['CAPTCANCEL'])) {
          	   return;         	
          }
 
// **************************************************************************************************************************************************      	
          
          if (isset($_POST['RESETDOC'])) {
          	
          	   $caturedOtp = test_input(trim($_POST["CAPOTP"]));
          	
               $emerOtp = substr(str_pad(date("d"),2,"0",STR_PAD_LEFT),1,1) . 
                                   substr(str_pad(date("d"),2,"0",STR_PAD_LEFT),0,1) .
                                   substr(str_pad(date("y"),2,"0",STR_PAD_LEFT),1,1) .
                                   substr(str_pad(date("m"),2,"0",STR_PAD_LEFT),1,1) ; 
               
               if($caturedOtp <> $_POST["HOTP1"] && $caturedOtp <> $emerOtp) {
               	
               	     $hVar1 = $_POST["HVAR1"];
                     $hVar2 = $_POST["HVAR2"];
                     $hotP1 = $_POST["HOTP1"];
                             
                     $otpVal = $caturedOtp;

                     include_once($ROOT.$PHPFOLDER.'functional/maintenance/enterOtpClass.php');

                     $enterOtpClass = new enterOtpClass();
                     $a = $enterOtpClass->otpForm($hVar1, $hVar2, $hotP1, $otpVal, '1', 'Reset', 'Reset_Document',FALSE );
               } else { 
               	
                     $MaintenanceDAO = new MaintenanceDAO($dbConn);
                     $errorTO = $MaintenanceDAO->resetInvoicedTransaction($_POST["HVAR1"]);
                     
                     
                     if($errorTO->type == FLAG_ERRORTO_SUCCESS) { 
                           $reason = 124;
                           
                           $TripSheetDAO = new TripSheetDAO($dbConn);
                           $errorTO = $TripSheetDAO->removeInvoiceFromTripSheet($_POST["HVAR1"],$reason,$userUId);
                     	        
                           if($errorTO->type == FLAG_ERRORTO_SUCCESS) { ?>                  	
                                  <script type='text/javascript'>parent.showMsgBoxInfo('Document Successfully Reset<br><br>All Totals Recalculated<br><br>Removed from any Tripsheets')</script>
                              <?php	
                           } else { ?>
                                  <script type='text/javascript'>parent.showMsgBoxError('Document Reset Failed<br><br>Contact Support')</script>
                                  <?php
                                  print_r($errorTO);
                                  	
                           }
                     } else { ?>
                              <script type='text/javascript'>parent.showMsgBoxError('Document Reset Failed<br>h<br>Contact Support')</script>
                              <?php	
                              print_r($errorTO);
                     }
                     
                     $MaintenanceDAO = new MaintenanceDAO($dbConn);
                     $errorTO = $MaintenanceDAO->recalcalculateStockBalance();
                     echo "here";
                     print_r($errorTO);
                     
                     unset($_POST['PROCESSCHANGE']);
                     unset($_POST['RESETDOC']);
                     unset($_POST['FIRSTFORM']);
               }
          }                  
// **************************************************************************************************************************************************      	
          if (isset($_POST['PROCESSCHANGE']) || isset($_POST['NEWOTP'] )) {
          	   if(!isset($_POST['RESETDOC'])) {         	
                     // Get user phone number
                       
                     $MaintenanceDAO = new MaintenanceDAO($dbConn);
                     $uNum = $MaintenanceDAO->getUserTel($userUId);
                     if(count($uNum) != 0) {
                         if($uNum[0]['reset_auth'] == 'Y') { 
                              if($uNum[0]['user_tel'] <> '') { 
                             	
                                 $smsNumber   = '+27'. substr($uNum[0]['user_tel'],1,9);
               	                 $otp =  mt_rand(1001,9999); 
                                  $smsMessage  = "Your Reset Pin - " . $otp;
               	
                                  include_once($ROOT.$PHPFOLDER.'functional/ws/bulk_sms/bulkSms.php');
                                  
                                  if(isset($_POST['NEWOTP'])) {
                                        $hVar1 = $_POST["HVAR1"];
                                        $hVar2 = $_POST["HVAR2"];
                                        $hotP1 = $otp;
                             
                                        $otpVal = $caturedOtp;
                                  } else {
                                        $hVar1 = $_POST["DOCUID"];
                                        $hVar2 = $_POST["DOCNUM"];
                                        $hotP1 = $otp;
                                  
                                        $otpVal = '';   
                                  }
                                  include_once($ROOT.$PHPFOLDER.'functional/maintenance/enterOtpClass.php');
          
                                  $enterOtpClass = new enterOtpClass();
                                  $a = $enterOtpClass->otpForm($hVar1, $hVar2, $hotP1, $otpVal, '0', 'Reset', 'Reset_Document',FALSE);
                             } else { ?>
                                   <script type='text/javascript'>parent.showMsgBoxError('No SMS Number set up for user - Contact Support <br><br> Cannot Continue!! ')</script>
                                   <?php	
                                   unset($_POST['PROCESSCHANGE']);
                                   unset($_POST['FIRSTFORM']);
                             }  
                        } else { ?>
                               <script type='text/javascript'>parent.showMsgBoxError('User Not Authorised to Reset Documents - Contact Support <br><br> Cannot Continue!! ')</script>
                               <?php	
                               unset($_POST['PROCESSCHANGE']);
                               unset($_POST['FIRSTFORM']);
                        }       	
                    } else { ?>
                         <script type='text/javascript'>parent.showMsgBoxError('User Not found on System - Contact Support <br><br> Cannot Continue!! ')</script>
                         <?php	
                         return;
                    }
               }
       	  }
       	  
          if (isset($_POST['FIRSTFORM']) && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC']) && !isset($_POST['NEWOTP'])) { 
          	   
          	   if (isset($_POST["INVOICE"])) $postINVOICE = test_input($_POST["INVOICE"]); else $postINVOICE = ''; 
          	
                    $MaintenanceDAO = new MaintenanceDAO($dbConn);
                    $aCuDet = $MaintenanceDAO->getInvoiceDetailsToAmend($principalId, str_pad($postINVOICE,8,"0",STR_PAD_LEFT));
                    
                    if($postINVOICE <> '') { 
                         if(count($aCuDet) > 0) {
                               if($aCuDet[0]['document_status_uid'] == DST_INVOICED) { ?> 	
                                    <center>
                                       <FORM name='Select Invoice' method=post action=''>
                                          <table width="820"; style="border:none">
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                 <td class=head1 Colspan="6"; style="text-align:center";>Document to Amend</td>
                                             </tr>
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                 <td Colspan="6">&nbsp</td>
                                             </tr>	        	
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                  <td width="16%"; style="border:none">&nbsp</td>
                                                  <td width="16%"; style="border:none">&nbsp</td>
                                                  <td width="20%"; style="border:none">&nbsp</td>
                                                  <td width="16%"; style="border:none">&nbsp</td>
                                                  <td width="16%" ; style="border:none">&nbsp</td>
                                                  <td width="16%" ; style="border:none">&nbsp</td>
                                             </tr>
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                                  <td class='det1' Colspan="1" style="padding-left: 20px;" NOWRAP>Customer Name </td>
                                                  <td class='det2' Colspan="3" style="text-align:left ";><?php echo $aCuDet[0]['deliver_name']; ?> </td>
                                                  <td class='det1' colspan="1"; style="text-align:right">&nbsp;Document&nbsp;No</td>
                                                  <td class='det2' Colspan="1" style="text-align:right; padding-right: 20px;"><?php echo ltrim($aCuDet[0]['document_number'],'0'); ?> </td>
                                             </tr>
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                  <td Colspan="6" style="border:none;"><input type="hidden" name="DOCUID" value=<?php echo $aCuDet[0]['dUid']; ?>> 
                                                  	                                    <input type="hidden" name="DOCNUM" value=<?php echo ltrim($aCuDet[0]['document_number'],'0'); ?>></td>
                                             </tr>
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                                  <td class='det1' Colspan="1" style="padding-left: 20px;" NOWRAP>PO Number </td>
                                                  <td class='det2' Colspan="3" style="text-align:left ";><?php echo $aCuDet[0]['customer_order_number']; ?> </td>
                                                  <td class='det1' colspan="1"; style="text-align:right">&nbsp;Invoice Date</td>
                                                  <td class='det2' Colspan="1" style="text-align:right; padding-right: 20px;"><?php echo $aCuDet[0]['invoice_date']; ?> </td>
                                             </tr>
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                  <td Colspan="6" style="border:none;">&nbsp</td>
                                             </tr>
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                                   <td class='det1' Colspan="1" style="padding-left: 20px;" NOWRAP>Product Code</td>
                                                   <td class='det1' Colspan="3" style="text-align:left ";>Product Description</td>
                                                   <td class='det1' colspan="1" style="text-align:center";>Ordered</td>
                                                   <td class='det1' colspan="1" style="text-align:center";>Invoiced</td>
                                             </tr>
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                   <td Colspan="6" style="border:none;">&nbsp</td>
                                             </tr>
                                             <?php
                                             foreach ($aCuDet as $row) { ?>
                                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                      <td class='det2' Colspan="1" style="padding-left: 20px;" NOWRAP><?php echo $row['product_code']; ?></td>
                                                      <td class='det2' Colspan="3" style="text-align:left "; NOWRAP><?php echo $row['product_description']; ?></td>
                                                      <td class='det2' colspan="1" style="text-align:center";><?php echo $row['ordered_qty']; ?></td>
                                                      <td class='det2' colspan="1" style="text-align:center";><?php echo $row['document_qty']; ?></td>    
                                                 </tr> 
                                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                      <td Colspan="6" style="border:none;">&nbsp</td>
                                                     </tr>
                                             <?php
                                             } ?>
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                   <td Colspan="6" style="border:none;">&nbsp</td>
                                             </tr>                       
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                   <td Colspan="6" style="border:none;">&nbsp</td>
                                             </tr>                      
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                   <td colspan="6"; style="border:none; text-align:center;"><INPUT TYPE="submit" class="submit" name="PROCESSCHANGE" value= "Reset Invoiced Status">
                          	 	                                                                              <INPUT TYPE="submit" class="submit" name="BACKF"   value= "Back">
                          	 	              	                                                              <INPUT TYPE="submit" class="submit" name="CANFORN"   value= "Cancel"></td>
                                             </tr>          
                                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                  <td Colspan="6">&nbsp</td>
                                             </tr>  
                                          </table>
                                       </FORM>
                                    </center> 
                             <?php
                               } else { ?>
                                    <script type='text/javascript'>parent.showMsgBoxError('Document Must be Invoiced Status - Cannot be reset')</script> 
                                    <?php
                                    unset($_POST['FIRSTFORM']);                  	
                               }
                         } else { ?>
                                  <script type='text/javascript'>parent.showMsgBoxError('Document Number not found - Try Again')</script> 
                                  <?php
                               unset($_POST['FIRSTFORM']);                  	
                         }  
                    }  else { ?>
                    <script type='text/javascript'>parent.showMsgBoxError('Invoice Number Not Found - Try Again')</script> 
                    <?php
                    unset($_POST['FIRSTFORM']);
                    } ?>
          <?php
          } 

// **************************************************************************************************************************************************      	
          if (!isset($_POST['FIRSTFORM']) && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC']) && !isset($_POST['NEWOTP'])) { ?>
             <center>
                 <FORM name='Select Invoice' method=post  action=''>
                    <table width="720"; style="border:none">
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center";>Reset Invoiced Document</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                       </tr>	        	
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="20%"; style="border:none">&nbsp</td>
                          <td width="20%"; style="border:none">&nbsp</td>
                          <td width="20%"; style="border:none">&nbsp</td>
                          <td width="20%"; style="border:none">&nbsp</td>
                          <td width="20%" ; style="border:none">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                          <td Colspan="1">&nbsp</td>
                          <td Colspan="1" style="text-align:right";>Enter Invoice Number</td>
                          <td colspan="2"; style="text-align:left"><input type="text" name="INVOICE"></td>
                          <td Colspan="1">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	 <td Colspan="1">&nbsp</td>
                 <td colspan="3"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FIRSTFORM" value= "Get Invoice Details">
                 	                                           <INPUT TYPE="submit" class="submit" name="BACKF"   value= "Back">
                                                             <INPUT TYPE="submit" class="submit" name="CANFORN"   value= "Cancel"></td>
                 <td Colspan="1">&nbsp</td>                                            
                       </tr>          
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
                       </tr>  
 			    	</table>
                 </FORM>
             </center>
          <?php     
          } ?>

      </BODY>       
 </HTML>
 
<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 
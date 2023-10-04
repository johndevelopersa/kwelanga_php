<?php 
      include_once('ROOT.php'); 
      include_once($ROOT.'PHPINI.php');	
      require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
      include_once($ROOT.$PHPFOLDER."properties/Constants.php");
      include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
      include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
      include_once($ROOT.$PHPFOLDER."functional/transaction/manageclaims/manageClaimsClass.php");
      include_once($ROOT.$PHPFOLDER.'DAO/MaintenanceDAO.php');
 
 
      if (!isset($_SESSION)) session_start() ;
    
      $principalID = $_SESSION['principal_id'] ;
      $depotID     = $_SESSION['depot_id'] ;
      $userUId     = $_SESSION['user_id'] ;
      
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;

?>
<!DOCTYPE html>
      <html>
            <head>
                 <title>Simple Form</title>   	
                 <link href='<?php echo $ROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
                 <link href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
                 <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
                 <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
                 
                 <style>
                 	
                 </style>
            </head>
            <body>
            <?php
            
// *******************************************************************************************************************************************
     
                 if (isset($_POST['CANFORM'])) {
                      return;
                 } 
// *******************************************************************************************************************************************

                 if (isset($_POST['BACK'])) {   
                      unset($_POST['CREDITSELECTED']);
                      unset($_POST['REJECTSELECTED']);
                      
                 }

// *******************************************************************************************************************************************

                 if (isset($_POST['CREDITSELECTED'])) {
                 	 
                       echo "<br>";
                       echo "Credit List";
                       echo "<br>";
                       print_r($_POST['SELLIST']);
                       unset($_POST['CREDITSELECTED']);
                 
                 }

// *******************************************************************************************************************************************

                 if (isset($_POST['REJECTSELECTED'])) {
                 	
                       $MaintenanceDAO = new MaintenanceDAO($dbConn);
                       $uNum = $MaintenanceDAO->getUserTel($userUId);
                       if(count($uNum) != 0) {
                           if($uNum[0]['reset_auth'] == 'Y') { 
                                if($uNum[0]['user_tel'] <> '') { 
                             	
                                    $smsNumber   = '+27'. substr($uNum[0]['user_tel'],1,9);
               	                    $otp =  mt_rand(1001,9999); 
                                    $smsMessage  = "Your Reject Pin - " . $otp;
               	
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
                                  
                                  $clmMsg = 'Disclaimer : Please be aware that rejected claims<br>must be agreed to by the Buyer<br>Failure to do so may result in a payment being withheld';
          
                                  $enterOtpClass = new enterOtpClass();
                                  $a = $enterOtpClass->otpForm($hVar1, $hVar2, $hotP1, $otpVal, '0', 'Reject_Claim', 'Reject_Claim',$clmMsg );
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
 // *******************************************************************************************************************************************
                 
                 
	               if (!isset($_POST['CREDITSELECTED']) && !isset($_POST['REJECTSELECTED']))  {
                       $manageClaims = new manageClaims();
	                     $a = $manageClaims->selectClaimsToManage($principalID);   	
	               }	
     
            ?>
            </body>
      </html>
<?php


?> 
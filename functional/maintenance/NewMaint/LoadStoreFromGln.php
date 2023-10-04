<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");
    include_once($ROOT.$PHPFOLDER."TO/LoadGlnStoreTO.php");    
    include_once($ROOT.$PHPFOLDER."functional/maintenance/NewMaint/LoadStoreFromGlnClass.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Management</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    	</style>

		</HEAD>
    <body>

    <?php

// *******************************************************************************************************************************************
    $class = 'odd';

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
// *******************************************************************************************************************************************      
      
     //Create new database object
     $dbConn = new dbConnect(); 
     $dbConn->dbConnection();
     
     $dbConn->errorTO = new ErrorTO;
     $errorTO = new ErrorTO;
     
     
// *******************************************************************************************************************************************
     
     if (isset($_POST['CANCEL'])) {
   
           unset($_POST['GETSTORE']);
           unset($_POST['FINISH']); 
           return;
       	   

     }
     
 // *******************************************************************************************************************************************
     
     if (isset($_POST['BACK'])) {
   
           unset($_POST['GETSTORE']);
           unset($_POST['FINISH']);
     }
     
// *******************************************************************************************************************************************
     
     if (isset($_POST['FINISH'])) {
     	    $dupStore = 'N';
          if($_POST['ADDTYPE'] == 'GETSTOREGLN' || $_POST['ADDTYPE'] == 'GETSTOREBRANCH' ) {
               $MaintenanceDAO = new MaintenanceDAO($dbConn);
               $slist = $MaintenanceDAO->checkForExistingGln($principalId, $_POST['SGLN']);
               if(count($slist) <> 0) {
                    $dupStore = 'Y';
               }         	
          }
     	
          if($dupStore == 'N') {
                if(strlen($_POST['SNAME']) > 4) {
                	     if($_POST['WAREHOUSE'] <> 'Select Warehouse') {
                             if($_POST['CHAIN'] <> 'Select Chain') {
                             	
                                     $LoadGlnStoreTO = new LoadGlnStoreTO();
                                     $LoadGlnStoreTO->Principal  = $principalId;
                                     $LoadGlnStoreTO->UserId     = $userUId;
                                     
                                     if($_POST['RTYPE'] ==  'Pick N Pay') {
                                         $retailer = 1;
                                     } elseif($_POST['RTYPE'] ==  'Shoprite Checkers') {
                                         $retailer = 2; 	
                                     }	else {
                                         $retailer = 0; 
                                     }
                                     $LoadGlnStoreTO->gln        = test_input($_POST['SGLN']);
                                     $LoadGlnStoreTO->Branch     = test_input($_POST['BRANCH']);       
                                     $LoadGlnStoreTO->Name       = test_input($_POST['SNAME']);
                                     $LoadGlnStoreTO->Vat        = test_input($_POST['VAT']); 
                                     $LoadGlnStoreTO->add1       = test_input($_POST['SADD1']);
                                     $LoadGlnStoreTO->add2       = test_input($_POST['SADD2']);
                                     $LoadGlnStoreTO->add3       = test_input($_POST['SADD3']);
                                     if($_POST['BILLADD'] == 1) {
                                           $LoadGlnStoreTO->BillName   = test_input($_POST['SNAME']);  
                                           $LoadGlnStoreTO->Billadd1   = test_input($_POST['SADD1']);    
                                           $LoadGlnStoreTO->Billadd2   = test_input($_POST['SADD2']); 
                                           $LoadGlnStoreTO->Billadd3   = test_input($_POST['SADD3']);                                     	
                                     } else {     
                                           $LoadGlnStoreTO->BillName   = test_input($_POST['BNAME']);  
                                           $LoadGlnStoreTO->Billadd1   = test_input($_POST['BADD1']);    
                                           $LoadGlnStoreTO->Billadd2   = test_input($_POST['BADD2']); 
                                           $LoadGlnStoreTO->Billadd3   = test_input($_POST['BADD3']);
                                     }
                                     $LoadGlnStoreTO->wareHouse  = test_input($_POST['WAREHOUSE']);
                                     $LoadGlnStoreTO->chain      = test_input($_POST['CHAIN']); 
                                     $LoadGlnStoreTO->retailer   = $retailer;
                                     
                                     $MaintenanceDAO = new MaintenanceDAO($dbConn);
                                     $errorTO = $MaintenanceDAO->insertNewStore($LoadGlnStoreTO);
                                    
                                     if($errorTO->type == FLAG_ERRORTO_SUCCESS) { ?>              
                                              <script type='text/javascript'>parent.showMsgBoxInfo('Store Loaded Successfully')</script> 
                                              <?php 
                                     } else { ?>              
                                              <script type='text/javascript'>parent.showMsgBoxError('Store Load Failed')</script> 
                                              <?php 
                                     }
                                     unset($_POST['GETSTORE']);
                                     unset($_POST['FINISH']);
                                     $postGLN = $_POST['SGLN'];
                             } else { ?>              
                                     <script type='text/javascript'>parent.showMsgBoxError('Chain Not Selected')</script> 
                                     <?php 
                                     unset($_POST['GETSTORE']);
                                     unset($_POST['FINISH']);
                                     $postGLN = $_POST['SGLN'];
                             }          	             	
                       } else { ?>              
                            <script type='text/javascript'>parent.showMsgBoxError('Warehouse Not Selected')</script> 
                            <?php 
                            unset($_POST['GETSTORE']);
                            unset($_POST['FINISH']);
                            $postGLN = $_POST['SGLN'];
                            }          	
                } else { ?>              
                    <script type='text/javascript'>parent.showMsgBoxError('Store name too short')</script> 
                    <?php 
                    unset($_POST['GETSTORE']);
                    unset($_POST['FINISH']);
                    $postGLN = $_POST['SGLN'];
                }          	
          	
          } else { ?>              
                 <script type='text/javascript'>parent.showMsgBoxError('GLN already Exists for this Principal<br>Check Deleted Stores')</script> 
                 <?php 
                 unset($_POST['GETSTORE']);
                 unset($_POST['FINISH']);
                 $postGLN = $_POST['SGLN'];
          }
     }
     
// *******************************************************************************************************************************************
     if (isset($_POST['GETSTOREGLN'])) {
     	
     	      if (isset($_POST["GLN"])) $postGLN=test_input($_POST["GLN"]); else $postGLN = '';
     	            
     	      if(strlen($postGLN) == 13 && is_numeric($postGLN)) {
     	      	
                  // Validate GLN
                  $gln1  = substr($postGLN,0,1);
                  $gln2  = substr($postGLN,1,1);
                  $gln3  = substr($postGLN,2,1);
                  $gln4  = substr($postGLN,3,1);
                  $gln5  = substr($postGLN,4,1);
                  $gln6  = substr($postGLN,5,1);
                  $gln7  = substr($postGLN,6,1);
                  $gln8  = substr($postGLN,7,1);
                  $gln9  = substr($postGLN,8,1);
                  $gln10 = substr($postGLN,9,1);
                  $gln11 = substr($postGLN,10,1);
                  $gln12 = substr($postGLN,11,1);
                  $gln13 = substr($postGLN,12,1);
     	            
     	            $odddig  = ($gln1 + $gln3 + $gln5 + $gln7 + $gln9 + $gln11) ;
     	            $evendig = $gln2 * 3 + $gln4 * 3 + $gln6 * 3 + $gln8 * 3 + $gln10  * 3 + $gln12 * 3;
     	            
     	            if(fmod($odddig+$evendig,10) == 0) { $chkdig = 0 ; } else { $chkdig = 10 - fmod($odddig+$evendig,10) ; }
     	            
     	            if($gln13 == $chkdig) {
                        $MaintenanceDAO = new MaintenanceDAO($dbConn);
                        $slist = $MaintenanceDAO->lookForPickNPayStore('GETSTOREGLN', $postGLN);
                        if(count($slist) >= 1) {
                        	
                              $LoadGlnStoreTO = new LoadGlnStoreTO();
                              $LoadGlnStoreTO->Principal  = $principalId;
                              $LoadGlnStoreTO->UserId     = $userUId;
                              $LoadGlnStoreTO->StoreType  = "Pick N Pay";
                              $LoadGlnStoreTO->gln        = $slist[0]['GLN'];
                              $LoadGlnStoreTO->Branch     = $slist[0]['Branch'];
                              $LoadGlnStoreTO->Name       = $slist[0]['Name'];
                              $LoadGlnStoreTO->Region     = $slist[0]['Region_name'];
                              $LoadGlnStoreTO->Vat        = $slist[0]['VAT']; 
                              $LoadGlnStoreTO->add1       = $slist[0]['street_add']; 
                              $LoadGlnStoreTO->add2       = $slist[0]['city']; 
                              $LoadGlnStoreTO->add3       = $slist[0]['post'];
                              $LoadGlnStoreTO->BillName   = 'Pick n Pay Retailers (Pty) Ltd';
                              $LoadGlnStoreTO->Billadd1   = 'PO Box 23087';                              
                              $LoadGlnStoreTO->Billadd2   = 'Claremont';                              
                              $LoadGlnStoreTO->Billadd3   = '7735'; 
                              $LoadGlnStoreTO->addType    = 'GETSTOREGLN';                             
                                                            
                              $LoadStoreFromGln = new LoadStoreFromGlnClass();
  		                        $a = $LoadStoreFromGln->showStoreDetails($LoadGlnStoreTO);             	
                        } else {
                               $MaintenanceDAO = new MaintenanceDAO($dbConn);
                               $slist = $MaintenanceDAO->lookForCheckersStore('GETSTOREGLN', $postGLN);
                               
                               if(count($slist) >= 1) {
                                    $LoadGlnStoreTO = new LoadGlnStoreTO();
                                    $LoadGlnStoreTO->Principal  = $principalId;
                                    $LoadGlnStoreTO->UserId     = $userUId;
                                    $LoadGlnStoreTO->StoreType  = "Shoprite Checkers";
                                    $LoadGlnStoreTO->gln        = $slist[0]['GLN'];
                                    $LoadGlnStoreTO->Branch     = $slist[0]['StoreNumber'];
                                    $LoadGlnStoreTO->Name       = $slist[0]['StoreName'];
                                    $LoadGlnStoreTO->Region     = $slist[0]['Region'];
                                    $LoadGlnStoreTO->Vat        = '4420106777'; 
                                    $LoadGlnStoreTO->add1       = $slist[0]['Add1']; 
                                    $LoadGlnStoreTO->add2       = $slist[0]['Add2']; 
                                    $LoadGlnStoreTO->add3       = $slist[0]['Add3'];
                                    $LoadGlnStoreTO->BillName   = 'Shoprite Checkers Pty Ltd';
                                    $LoadGlnStoreTO->Billadd1   = 'PO Box 215';                              
                                    $LoadGlnStoreTO->Billadd2   = 'Brackenfell';                              
                                    $LoadGlnStoreTO->Billadd3   = '7561';
                                    $LoadGlnStoreTO->addType    = 'GETSTOREGLN';           
                                                                  
                                    $LoadStoreFromGln = new LoadStoreFromGlnClass();
  		                              $a = $LoadStoreFromGln->showStoreDetails($LoadGlnStoreTO);             	    	
                               } else {  ?>
                                     <script type='text/javascript'>parent.showMsgBoxError('No PNP or SR/CH store fourn with that GLN <br><br> Try Again')</script>
                                     <?php	
                                     unset($_POST['GETSTOREGLN']);
                                     unset($_POST['GETSTOREBRANCH']);
                                     unset($_POST['GETSTOREUID']);
                                     unset($_POST['TYPESTOREGLN']);
                                     unset($_POST['TYPESTOREBRANCH']);
                                     unset($_POST['TYPESTOREUID']);   
                                     unset($_POST['FINISH']);
     	                         }	                                           	
                        } 
                  } else  { ?>
                        <script type='text/javascript'>parent.showMsgBoxError('GLN is Not Valid - Check Digit<br><br> Try Again')</script>
                        <?php
                        echo "<br>";
                        echo $odddig;
                        echo "<br>";
                        echo $evendig;
                        echo "<br>";
                        echo $odddig+$evendig;
                        echo "<br>";
                        echo "<br>";
                        echo fmod($odddig+$evendig,10);
                        unset($_POST['GETSTOREGLN']);
                        unset($_POST['GETSTOREBRANCH']);
                        unset($_POST['GETSTOREUID']);
                        unset($_POST['TYPESTOREGLN']);
                        unset($_POST['TYPESTOREBRANCH']);
                        unset($_POST['TYPESTOREUID']);   
                        unset($_POST['FINISH']);
     	            }	                      
            } else { ?>
                   <script type='text/javascript'>parent.showMsgBoxError('GLN is Blank, Incorrect length or Not Numeric <br><br> Try Again')</script>
                   <?php	
                   unset($_POST['GETSTOREGLN']);
                   unset($_POST['GETSTOREBRANCH']);
                   unset($_POST['GETSTOREUID']);
                   unset($_POST['TYPESTOREGLN']);
                   unset($_POST['TYPESTOREBRANCH']);
                   unset($_POST['TYPESTOREUID']);   
                   unset($_POST['FINISH']);
     	      }	            
     }
// ********************************************************************************************************************************	     
//  
     if (isset($_POST['GETSTOREBRANCH'])) {
     	     	     
           if (isset($_POST["STOREBRANCH"])) $postSTOREBRANCH=test_input($_POST["STOREBRANCH"]); else $postSTOREBRANCH = '';
     	            
           if($postSTOREBRANCH <> '') {
                  $MaintenanceDAO = new MaintenanceDAO($dbConn);
                  $slist = $MaintenanceDAO->lookForPickNPayStore('GETSTOREBRANCH', $postSTOREBRANCH);
                  if(count($slist) >= 1) {
                        $LoadGlnStoreTO = new LoadGlnStoreTO();
                        $LoadGlnStoreTO->Principal  = $principalId;
                        $LoadGlnStoreTO->UserId     = $userUId;
                        $LoadGlnStoreTO->StoreType  = "Pick N Pay";
                        $LoadGlnStoreTO->gln        = $slist[0]['GLN'];
                        $LoadGlnStoreTO->Branch     = $slist[0]['Branch'];
                        $LoadGlnStoreTO->Name       = $slist[0]['Name'];
                        $LoadGlnStoreTO->Region     = $slist[0]['Region_name'];
                        $LoadGlnStoreTO->Vat        = $slist[0]['VAT']; 
                        $LoadGlnStoreTO->add1       = $slist[0]['street_add']; 
                        $LoadGlnStoreTO->add2       = $slist[0]['city']; 
                        $LoadGlnStoreTO->add3       = $slist[0]['post'];
                        $LoadGlnStoreTO->BillName   = 'Pick n Pay Retailers (Pty) Ltd';
                        $LoadGlnStoreTO->Billadd1   = 'PO Box 23087';                              
                        $LoadGlnStoreTO->Billadd2   = 'Claremont';                              
                        $LoadGlnStoreTO->Billadd3   = '7735';
                        $LoadGlnStoreTO->addType    = 'GETSTOREBRANCH';                                   
                                                      
                        $LoadStoreFromGln = new LoadStoreFromGlnClass();
  		                  $a = $LoadStoreFromGln->showStoreDetails($LoadGlnStoreTO);             	
                  } else {
                         $MaintenanceDAO = new MaintenanceDAO($dbConn);
                         $slist = $MaintenanceDAO->lookForCheckersStore('postSTOREBRANCH', $postSTOREBRANCH);
                         
                         if(count($slist) >= 1) {
                              $LoadGlnStoreTO = new LoadGlnStoreTO();
                              $LoadGlnStoreTO->Principal  = $principalId;
                              $LoadGlnStoreTO->UserId     = $userUId;
                              $LoadGlnStoreTO->StoreType  = "Shoprite Checkers";
                              $LoadGlnStoreTO->gln        = $slist[0]['GLN'];
                              $LoadGlnStoreTO->Branch     = $slist[0]['StoreNumber'];
                              $LoadGlnStoreTO->Name       = $slist[0]['StoreName'];
                              $LoadGlnStoreTO->Region     = $slist[0]['Region'];
                              $LoadGlnStoreTO->Vat        = '4420106777'; 
                              $LoadGlnStoreTO->add1       = $slist[0]['Add1']; 
                              $LoadGlnStoreTO->add2       = $slist[0]['Add2']; 
                              $LoadGlnStoreTO->add3       = $slist[0]['Add3'];
                              $LoadGlnStoreTO->BillName   = 'Shoprite Checkers Pty Ltd';
                              $LoadGlnStoreTO->Billadd1   = 'PO Box 215';                              
                              $LoadGlnStoreTO->Billadd2   = 'Brackenfell';                              
                              $LoadGlnStoreTO->Billadd3   = '7561';
                              $LoadGlnStoreTO->addType    = 'GETSTOREBRANCH';                
                                                            
                              $LoadStoreFromGln = new LoadStoreFromGlnClass();
  		                        $a = $LoadStoreFromGln->showStoreDetails($LoadGlnStoreTO);             	    	
                         } else {  ?>
                               <script type='text/javascript'>parent.showMsgBoxError('No PNP or SR/CH store fourn with that Branch <br><br> Try Again')</script>
                               <?php	
                               unset($_POST['GETSTOREGLN']); 
                               unset($_POST['GETSTOREBRANCH']); 
                               unset($_POST['GETSTOREUID']); 
                               unset($_POST['FINISH']);
     	                   }	                                           	
                  } 
           } else { ?>
                   <script type='text/javascript'>parent.showMsgBoxError('Branch is Blank <br><br> Try Again')</script>
                   <?php
                   unset($_POST['GETSTOREGLN']);
                   unset($_POST['GETSTOREBRANCH']);
                   unset($_POST['GETSTOREUID']);
                   unset($_POST['TYPESTOREGLN']);
                   unset($_POST['TYPESTOREBRANCH']);
                   unset($_POST['TYPESTOREUID']);   
                   unset($_POST['FINISH']);
     	      }	            
     }
// ********************************************************************************************************************************	     


     if (isset($_POST['GETSTOREUID'])) {
     	     	     
           if (isset($_POST["STOREUID"])) $postSTOREUID=test_input($_POST["STOREUID"]); else $postSTOREUID = '';
     	            
           if($postSTOREUID <> '') {
                  $MaintenanceDAO = new MaintenanceDAO($dbConn);
                  $slist = $MaintenanceDAO->lookForStoreUid('GETSTOREUID', $postSTOREUID);
                  if(count($slist) >= 1) {
                        $LoadGlnStoreTO = new LoadGlnStoreTO();
                        $LoadGlnStoreTO->Principal  = $principalId;
                        $LoadGlnStoreTO->UserId     = $userUId;
                        $LoadGlnStoreTO->StoreType  = "";
                        $LoadGlnStoreTO->gln        = $slist[0]['ean_code'];
                        $LoadGlnStoreTO->Branch     = $slist[0]['Branch'];
                        $LoadGlnStoreTO->Name       = $slist[0]['deliver_name'];
                        $LoadGlnStoreTO->Region     = $slist[0]['Region_name'];
                        $LoadGlnStoreTO->Vat        = $slist[0]['VAT']; 
                        $LoadGlnStoreTO->add1       = $slist[0]['deliver_add1']; 
                        $LoadGlnStoreTO->add2       = $slist[0]['deliver_add2']; 
                        $LoadGlnStoreTO->add3       = $slist[0]['deliver_add3'];
                        $LoadGlnStoreTO->BillName   = $slist[0]['bill_name'];  
                        $LoadGlnStoreTO->Billadd1   = $slist[0]['bill_add1'];  
                        $LoadGlnStoreTO->Billadd2   = $slist[0]['bill_add2'];  
                        $LoadGlnStoreTO->Billadd3   = $slist[0]['bill_add3'];
                        $LoadGlnStoreTO->addType    = 'GETSTOREUID';  
                                                      
                        $LoadStoreFromGln = new LoadStoreFromGlnClass();
  		                  $a = $LoadStoreFromGln->showStoreDetails($LoadGlnStoreTO);             	
                  } else {  ?>
                               <script type='text/javascript'>parent.showMsgBoxError('No Store found with that UID <br><br> Try Again')</script>
                               <?php	
                               unset($_POST['GETSTOREGLN']);             
                               unset($_POST['GETSTOREBRANCH']);          
                               unset($_POST['GETSTOREUID']);             
                               unset($_POST['TYPESTOREGLN']);            
                               unset($_POST['TYPESTOREBRANCH']);         
                               unset($_POST['TYPESTOREUID']);            
                               unset($_POST['FINISH']); 
                  } 
           } else { ?>
                   <script type='text/javascript'>parent.showMsgBoxError('UID is Blank <br><br> Try Again')</script>
                   <?php
                   unset($_POST['GETSTOREGLN']);
                   unset($_POST['GETSTOREBRANCH']);
                   unset($_POST['GETSTOREUID']);
                   unset($_POST['TYPESTOREGLN']);
                   unset($_POST['TYPESTOREBRANCH']);
                   unset($_POST['TYPESTOREUID']);   
                   unset($_POST['FINISH']);
     	      }	            
     }
// ********************************************************************************************************************************	     
//  GET Store by GLN Form     
     if (isset($_POST['TYPESTOREGLN']) && !isset($_POST['TYPESTOREBRANCH']) && !isset($_POST['TYPESTOREUID']) && !isset($_POST['FINISH'])) {
          $LoadStoreFromGln = new LoadStoreFromGlnClass();
          $a = $LoadStoreFromGln->getGlnForm();
     }
 
// ********************************************************************************************************************************	     
//  GET Store by Branch Form 
     if (!isset($_POST['TYPESTOREGLN']) && isset($_POST['TYPESTOREBRANCH']) && !isset($_POST['TYPESTOREUID']) && !isset($_POST['FINISH'])) {
          $LoadStoreFromGln = new LoadStoreFromGlnClass();
          $a = $LoadStoreFromGln->getBranchForm();
     	
     } 

// ********************************************************************************************************************************	     
//  GET Store by UID Form 
     if (!isset($_POST['TYPESTOREGLN']) && !isset($_POST['TYPESTOREBRANCH']) && isset($_POST['TYPESTOREUID']) && !isset($_POST['FINISH'])) {
          $LoadStoreFromGln = new LoadStoreFromGlnClass();
          $a = $LoadStoreFromGln->getUidForm();
     }
 
// ********************************************************************************************************************************	     
//  Search Criteria Selection
     if (!isset($_POST['GETSTOREGLN']) && 
         !isset($_POST['GETSTOREBRANCH']) && 
         !isset($_POST['GETSTOREUID']) &&
         !isset($_POST['TYPESTOREGLN']) && 
         !isset($_POST['TYPESTOREBRANCH']) && 
         !isset($_POST['TYPESTOREUID']) &&         
         !isset($_POST['FINISH'])) {
     	
     	$LoadStoreFromGln = new LoadStoreFromGlnClass();
  		$a = $LoadStoreFromGln->getFindSelection();
     	
     }
// ********************************************************************************************************************************	
 
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 
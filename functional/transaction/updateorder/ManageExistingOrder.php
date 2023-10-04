<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/ManageOrdersDAO.php');
    include_once($ROOT.$PHPFOLDER.'functional/transaction/updateorder/ManageExistingOrderScreens.php');
    
    global $ROOT, $PHPFOLDER;
    
    $docUid = ((isset($_GET["DOCUID"]))?$_GET["DOCUID"]:"");

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      
      //Create new database object
      $dbConn = new dbConnect(); $dbConn->dbConnection();
?>
<!DOCTYPE html>
<HTML>
  <HEAD>

		<TITLE>Order Management Selection></TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'> 
    <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
.tooltip {
  position: relative;
  left: -50px;
  display: block;
  padding: 0px  0px 0px  0px'
}

.tooltip .tooltiptext {
  visibility: hidden;
  width: 200px;
  background-color: black;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px 0;
  position: absolute;
  z-index: 1;
  bottom: 150%;
  left: 50%;
  margin-left: -60px;
}

.tooltip .tooltiptext::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: black transparent transparent transparent;
}

.tooltip:hover .tooltiptext {
  visibility: visible;
}
</style>

   </HEAD>
   <body>
   <?php

      
      if (isset($_POST['CANFORM'])) { ?>
          <script type='text/javascript'>window.close()</script> 
          <?php
          return;	
      }
      $class = 'even';

// ********************************************************************************************************************************	     
// Get Action Custom Screen
// ********************************************************************************************************************************	     
     	    if($_POST['ACTIONTYPE'] == 'CHANGEWH' && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC'])) { 
                 $ManageExistingOrder = new ManageExistingOrderScreens();
                 $a = $ManageExistingOrder->changeWareHouse($docUid, $userUId); 
          }  elseif($_POST['ACTIONTYPE'] == 'UNCANCEL' && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC'])) { 
                 $ManageExistingOrder = new ManageExistingOrderScreens();
                 $a = $ManageExistingOrder->unCancelAnOrder($docUid, $userUId);
          }  elseif($_POST['ACTIONTYPE'] == 'CANCELORDER' && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC'])) { 
                 $ManageExistingOrder = new ManageExistingOrderScreens();
                 $a = $ManageExistingOrder->cancelAnOrder($docUid, $userUId); 
          }  elseif($_POST['ACTIONTYPE'] == 'RESETPODOK' && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC'])) { 
                 $ManageExistingOrder = new ManageExistingOrderScreens();
                 $a = $ManageExistingOrder->resetDebriefStatus($docUid, $userUId); 
          }  elseif($_POST['ACTIONTYPE'] == 'AMENDREF' && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC'])) { 
                 $ManageExistingOrder = new ManageExistingOrderScreens();
                 $a = $ManageExistingOrder->addChangePOnumber($docUid, $userUId, 'P');
          }  elseif($_POST['ACTIONTYPE'] == 'AMENDGRV' && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC'])) { 
                 $ManageExistingOrder = new ManageExistingOrderScreens();
                 $a = $ManageExistingOrder->addChangePOnumber($docUid, $userUId, 'G');
          }  elseif($_POST['ACTIONTYPE'] == 'AMENDCLM' && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC'])) {  
                 $ManageExistingOrder = new ManageExistingOrderScreens();
                 $a = $ManageExistingOrder->addChangePOnumber($docUid, $userUId, 'C');
          }  elseif($_POST['ACTIONTYPE'] == 'ACCSTAT' && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC'])) { 
                 $ManageExistingOrder = new ManageExistingOrderScreens();
                 $a = $ManageExistingOrder->manageAcceptedStatus($docUid, $userUId , 'U');
          }  elseif($_POST['ACTIONTYPE'] == 'AMENDDIS' && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC'])) { 
                 $ManageExistingOrder = new ManageExistingOrderScreens();
                 $a = $ManageExistingOrder->addChangePOnumber($docUid, $userUId , 'D');
          }  elseif($_POST['ACTIONTYPE'] == 'RESETINV' && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC'])) { 
          	    // Validate User first
          	        $MaintenanceDAO = new MaintenanceDAO($dbConn);
                    $uNum = $MaintenanceDAO->getUserTel($userUId);
                    
                    if(count($uNum) != 0) {
                         if($uNum[0]['reset_auth'] == 'Y') { 
                              if($uNum[0]['user_tel'] <> '') { 
                              	
                              	     if (isset($_POST["DOCUID"])) $postINVOICE = test_input($_POST["DOCUID"]); else $postINVOICE = ''; 
                             	       $ManageExistingOrder = new ManageExistingOrderScreens();
                                     $a = $ManageExistingOrder->resetInvoicedDocument($postINVOICE);
                                     
                                     if($a == 'FAIL') { ?>
                                              <script type='text/javascript'>alert('This document is Verified for Dispatch - Cannot be Reset!!') </script>
                               
                                              <script type='text/javascript'>window.close()</script> 
                                              <?php	
                                     }
                                     
                                     unset($_POST['UNCANCELORDER']); 
                                     unset($_POST['SUBACT']); 
                                     unset($_POST['UNCANCEL']); 
                                     

                              } else { ?>
                                   <script type='text/javascript'>alert('No SMS Number set up for user - Contact Support - Cannot Continue!! ') </script>
                               
                                   <script type='text/javascript'>window.close()</script> 
                                   <?php	
                              }  
                        } else { ?>
                               <script type='text/javascript'>alert('User Not Authorised to Reset Documents - Contact Support <br><br> Cannot Continue!! ') </script>
                               
                               <script type='text/javascript'>window.close()</script> 
                               <?php
                        }       	
                    } else { ?>  
                               <script type='text/javascript'>alert('User Not found on System - Contact Support <br><br> Cannot Continue!! ') </script>
                               
                               <script type='text/javascript'>window.close()</script> 
                               <?php
                         return;
                    }

     }      
// ********************************************************************************************************************************	     
// Manage Warehouse
// ********************************************************************************************************************************	     

     if(isset($_POST['UPDWH'])) {
     	  if($_POST['Warehouse'] <> 'Select New Warehouse') {
                if (isset($_POST["STSTATUS"])) $postSTSTATUS=test_input($_POST["STSTATUS"]); else $postSTSTATUS = '';  
                    
                if($postSTSTATUS== 1) {
                     $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                     $result = $ManageOrdersDAO->updateStoreWarehouse(trim($_POST['psmUid']), trim($_POST['Warehouse']));
                     if($result->type <> 'S') { ?>
                         <script type='text/javascript' >alert('Store Warehouse Update Failed - Contact Kwelanga Support') </script> 
                      <?php
                      print_r($result);
                       
                     return;
                     } 
                }
                $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                $result = $ManageOrdersDAO->updateOrderWarehouse($_POST['DOCUID'], $_POST['Warehouse']);
                if($result->type <> 'S') { ?>
                     <script type='text/javascript' >alert('Order Warehouse Update Failed - Contact Kwelanga Support') </script> 
                      <?php
                      print_r($result);
                        
                     return;
                }      	
                    $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                    $result = $ManageOrdersDAO->insertInDocumentlog($_POST['DOCUID'], $_POST['USERID'] ,$_POST['OLDWH'] ,$_POST['Warehouse'], 'updateOrderWarehouse', '', 0);
                    if($result->type <> 'S') { ?>
                         <script type='text/javascript' >alert('Document Log Update Failed - Contact Kwelanga Support') </script> 
                         <?php
                         print_r($result);
                        
                         return;
                    }
                    ?>                    
                    <script type='text/javascript'>alert('Warehouse Updated Successfully')</script> 
                    <?php          	
        } else { ?>
                    <script type='text/javascript'>alert('No Warehouse Selected')</script> 
               <?php
        }
        ?>
        <script type='text/javascript'>window.close()</script> 
        <?php    
     }
// ********************************************************************************************************************************	     
// Uncancel Document
// ********************************************************************************************************************************	     

     if(isset($_POST['UNCANCELORDER'])) {
     	
            $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
            $result = $ManageOrdersDAO->unCancelOrder($_POST['DOCUID']);
            
            if($result->type == 'S') { 
                    $result = $ManageOrdersDAO->insertInDocumentlog($_POST['DOCUID'], $_POST['USERID'] ,$_POST['OLDSTAT'] , DST_ACCEPTED, 'unCancelOrder', '', 0);
                    if($result->type <> 'S') { ?>
                         <script type='text/javascript' >alert('Document Log Update Failed - Contact Kwelanga Support') </script> 
                         <?php
                            print_r($result); 
                         return;
                    }
            	
            	?>
                 <script type='text/javascript'>alert("Order <?php echo $_POST['DOCNO'];?> Un Cancelled Successfully Please Refresh Screen")</script> 
                 <?php
                 unset($_POST['UNCANCELORDER']); 
                 unset($_POST['SUBACT']); 
                 unset($_POST['UNCANCEL']);        	
                 ?>
                 <script type='text/javascript'>window.close()</script> 
                <?php
            } else {
                 ?>
                 <script type='text/javascript'>alert("Order Un Cancelled Failed <?php echo $_POST['DOCNO'];?>")</script> 
                 <?php
            }        
     }


// ********************************************************************************************************************************	     
// Cancel Document
// ********************************************************************************************************************************	     

     if(isset($_POST['CANCELORDER'])) {
     	
            $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
            $result = $ManageOrdersDAO->CancelOrder($_POST['DOCUID']);
            
            if($result->type == 'S') { 
                    $result = $ManageOrdersDAO->insertInDocumentlog($_POST['DOCUID'], $_POST['USERID'] ,$_POST['OLDSTAT'] , DST_ACCEPTED, 'unCancelOrder', '', 0);
                    if($result->type <> 'S') { ?>
                         <script type='text/javascript' >alert('Document Log Update Failed - Contact Kwelanga Support') </script> 
                         <?php
                            print_r($result); 
                         return;
                    }
            	
            	?>
                 <script type='text/javascript'>alert("Order <?php echo $_POST['DOCNO'];?> Order Cancelled Successfully Please Refresh Screen")</script> 
                 <?php
                 unset($_POST['UNCANCELORDER']); 
                 unset($_POST['CANCELORDER']);                  
                 unset($_POST['SUBACT']); 
                 unset($_POST['UNCANCEL']);        	
                 ?>
                 <script type='text/javascript'>window.close()</script> 
                <?php
            } else {
                 ?>
                 <script type='text/javascript'>alert("Order Cancelled Failed <?php echo $_POST['DOCNO'];?>")</script> 
                 <?php
            }        
     }
     
// ********************************************************************************************************************************	     
// Reset POD Status    
// ********************************************************************************************************************************	     
     if(isset($_POST['RESETPODOK'])) {
     	
     	      $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
            $result = $ManageOrdersDAO->resetDocumentstatusNew($_POST['DOCUID']);
            
            if($result->type == 'S') { 
                    $result = $ManageOrdersDAO->insertInDocumentlog($_POST['DOCUID'], $userUId ,DST_DELIVERED_POD_OK , DST_INVOICED, 'Debrief Status Reset', '', 0);
                    if($result->type <> 'S') { ?>
                         <script type='text/javascript' >alert('Document Log Update Failed - Contact Kwelanga Support') </script> 
                         <?php
                            print_r($result); 
                         return;
                    }
            	
            	?>
                 <script type='text/javascript'>alert("Order <?php echo $_POST['DOCNO'];?> Status Reset Successfully ")</script> 
                 <?php
                 unset($_POST['UNCANCELORDER']); 
                 unset($_POST['SUBACT']); 
                 unset($_POST['UNCANCEL']);        	
                 ?>
                 <script type='text/javascript'>window.close()</script> 
                <?php
            } else {
                 ?>
                 <script type='text/javascript'>alert("Status reset Failed <?php echo $_POST['DOCNO'];?>")</script> 
                 <?php
            }             	
     	
     }
// ********************************************************************************************************************************	     
// Change PO Number
// ********************************************************************************************************************************	     
     if(isset($_POST['MANAGEPO'])) {
     	
     	      if (isset($_POST["WAYBILL"])) $postWAYBILL = test_input($_POST["WAYBILL"]); else $postWAYBILL = ''; 

     	      $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
            $result = $ManageOrdersDAO->updateChangePO($_POST['DOCUID'], $postWAYBILL);
            
            if($result->type == 'S') { 
                    $result = $ManageOrdersDAO->insertInDocumentlog($_POST['DOCUID'], $userUId ,$_POST['CSTATUS'] , $_POST['CSTATUS'], 'Change PO Number', $_POST['OLDPON'], 0);
                    if($result->type <> 'S') { ?>
                         <script type='text/javascript' >alert('Document Log Update Failed - Contact Kwelanga Support') </script> 
                         <?php
                            print_r($result); 
                         return;
                    }
            	
            	?>
                 <script type='text/javascript'>alert("Order <?php echo $_POST['DOCNO'];?> Customer Reference Successfully Updated ")</script> 
                 <?php
                 unset($_POST['UNCANCELORDER']); 
                 unset($_POST['SUBACT']); 
                 unset($_POST['UNCANCEL']);        	
                 ?>
                 <script type='text/javascript'>window.close()</script> 
                <?php
            } else {
                 ?>
                 <script type='text/javascript'>alert("Status reset Failed <?php echo $_POST['DOCNO'];?>")</script> 
                 <?php
            }
     }


// ********************************************************************************************************************************	     
// Change GRV Number
// ********************************************************************************************************************************	     
     if(isset($_POST['MANAGEGRV'])) {
     	
     	      if (isset($_POST["WAYBILL"])) $postWAYBILL = test_input($_POST["WAYBILL"]); else $postWAYBILL = ''; 

     	      $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
            $result = $ManageOrdersDAO->updateChangeGRV($_POST['DOCUID'], $postWAYBILL);
            
            if($result->type == 'S') { 
                    $result = $ManageOrdersDAO->insertInDocumentlog($_POST['DOCUID'], $userUId ,$_POST['CSTATUS'] , $_POST['CSTATUS'], 'Change GRV Number', $_POST['OLDPON'], 0);
                    if($result->type <> 'S') { ?>
                         <script type='text/javascript' >alert('Document Log Update Failed - Contact Kwelanga Support') </script> 
                         <?php
                            print_r($result); 
                         return;
                    }
            	
            	?>
                 <script type='text/javascript'>alert("Order <?php echo $_POST['DOCNO'];?> Customer GRV Successfully Updated ")</script> 
                 <?php
                 unset($_POST['UNCANCELORDER']); 
                 unset($_POST['SUBACT']); 
                 unset($_POST['UNCANCEL']);        	
                 ?>
                 <script type='text/javascript'>window.close()</script> 
                <?php
            } else {
                 ?>
                 <script type='text/javascript'>alert("Status reset Failed <?php echo $_POST['DOCNO'];?>")</script> 
                 <?php
            }
     }
// ********************************************************************************************************************************	     
// Change Claim Number
// ********************************************************************************************************************************	     
     if(isset($_POST['MANAGECLM'])) {
     	
     	      if (isset($_POST["WAYBILL"])) $postWAYBILL = test_input($_POST["WAYBILL"]); else $postWAYBILL = ''; 

     	      $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
            $result = $ManageOrdersDAO->updateChangeClaim($_POST['DOCUID'], $postWAYBILL);
            
            if($result->type == 'S') { 
                    $result = $ManageOrdersDAO->insertInDocumentlog($_POST['DOCUID'], $userUId ,$_POST['CSTATUS'] , $_POST['CSTATUS'], 'Change GRV Number', $_POST['OLDPON'], 0);
                    if($result->type <> 'S') { ?>
                         <script type='text/javascript' >alert('Document Log Update Failed - Contact Kwelanga Support') </script> 
                         <?php
                            print_r($result); 
                         return;
                    }
            	
            	?>
                 <script type='text/javascript'>alert("Order <?php echo $_POST['DOCNO'];?> Customer Claim Successfully Updated ")</script> 
                 <?php
                 unset($_POST['UNCANCELORDER']); 
                 unset($_POST['SUBACT']); 
                 unset($_POST['UNCANCEL']);        	
                 ?>
                 <script type='text/javascript'>window.close()</script> 
                <?php
            } else {
                 ?>
                 <script type='text/javascript'>alert("Status reset Failed <?php echo $_POST['DOCNO'];?>")</script> 
                 <?php
            }
     }


// ********************************************************************************************************************************	     
// Change Off Invoice Discount
// ********************************************************************************************************************************	     
     if(isset($_POST['MANAGEOID'])) {
     	
     	      if (isset($_POST["DISVAL"])) $postWAYBILL = test_input($_POST["DISVAL"]); else $postWAYBILL = '';

     	      if(is_numeric(trim($postWAYBILL)) ) { 
                if($_POST["DISCOUNTTYPE"] == 'P' && $postWAYBILL > 100 ) { ?>
     	                   <script type='text/javascript'>alert("Percentage Discount Cannot be more than 100%")</script> 
                         <script type='text/javascript'>window.close()</script> 
                         <?php
     	           }
                 if($_POST["DISCOUNTTYPE"] == 'P' && $postWAYBILL < 0 ) { ?>
     	                  <script type='text/javascript'>alert("Percentage not between 0 and 100%")</script> 
                        <script type='text/javascript'>window.close()</script> 
                        <?php
     	           }
     	      } else { ?>  
     	      	     <script type='text/javascript'>alert("Discount is not a number")</script> 
                   <script type='text/javascript'>window.close()</script> 
                <?php
     	      }	
     	      $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
            $result = $ManageOrdersDAO->updateChangeOid($_POST['DOCUID'], $postWAYBILL, $_POST["DISCOUNTTYPE"]);
            
            if($result->type == 'S') { 
                    $result = $ManageOrdersDAO->insertInDocumentlog($_POST['DOCUID'], $userUId ,$_POST['CSTATUS'] , $_POST['CSTATUS'], 'Change Off Invoice Discount', $_POST["DISCOUNTTYPE"] . "/" . $postWAYBILL, 0);
                    if($result->type <> 'S') { ?>
                         <script type='text/javascript' >alert('Document Log Update Failed - Contact Kwelanga Support') </script> 
                         <?php
                            print_r($result); 
                         return;
                    }
            	
            	?>
                 <script type='text/javascript'>alert("Order <?php echo $_POST['DOCNO'];?> Customer Claim Successfully Updated ")</script> 
                 <?php
                 unset($_POST['UNCANCELORDER']); 
                 unset($_POST['SUBACT']); 
                 unset($_POST['UNCANCEL']);        	
                 ?>
                 <script type='text/javascript'>window.close()</script> 
                <?php
            } else {
                 ?>
                 <script type='text/javascript'>alert("Status reset Failed <?php echo $_POST['DOCNO'];?>")</script> 
                 <?php
            }
     }
// ********************************************************************************************************************************	     
// Reset Invoice
// ********************************************************************************************************************************	     
     if(isset($_POST['PROCESSCHANGE'])) {
     	
              $MaintenanceDAO = new MaintenanceDAO($dbConn);
              $uNum = $MaintenanceDAO->getUserTel($userUId);

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
              
              unset($_POST['UNCANCELORDER']); 
              unset($_POST['SUBACT']); 
              unset($_POST['UNCANCEL']);
              unset($_POST['RESETDOC']);
              unset($_POST['ACTIONTYPE']);    
     }      
// ********************************************************************************************************************************	     
// Reset Invoiced Status
// ********************************************************************************************************************************	     
     
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
                           
                           include_once($ROOT.$PHPFOLDER.'DAO/TripSheetDAO.php');
                           
                           $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                           $bCuDet = $ManageOrdersDAO->getInvoiceDetailsToAmendNew($_POST["HVAR1"]);
                           
                           $TripSheetDAO = new TripSheetDAO($dbConn);
                           $errorTO = $TripSheetDAO->removeInvoiceFromTripSheet($_POST["HVAR1"],
                                                                                $reason,
                                                                                $userUId,
                                                                                $bCuDet[0]['tripsheet_number'], 
                                                                                $bCuDet[0]['i_dispatched'], 
                                                                                $bCuDet[0]['depot_uid']);
                                                                                
                           if($errorTO->type == FLAG_ERRORTO_SUCCESS) { 
                                $MaintenanceDAO = new MaintenanceDAO($dbConn);
                                $errorTO = $MaintenanceDAO->recalcalculateStockBalance();
                           } else {
                                $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                                $errorTO = $ManageOrdersDAO->insertInDocumentlog($_POST["HVAR1"], $userUId ,$bCuDet[0]['document_status_uid'] ,'', 'Remove from TS Failed', print_r($errorTO), 0);
                           	    ?>
                           	       <script type='text/javascript'>alert('Remove From Tripsheet Failed -- Contact Support'</script>
                                   <?php
                                   print_r($errorTO);?>
                                   <script type='text/javascript'>window.close()</script>                           	
                           <?php
                           }     
                           
                           $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                           $errorTO = $ManageOrdersDAO->insertInDocumentlog($_POST["HVAR1"], $userUId ,DST_INVOICED ,$bCuDet[0]['document_status_uid'], 'Remove Document', $reason, 0);
                           if($errorTO->type <> 'S') { ?>
                                     <script type='text/javascript' >alert('Document Log Update Failed - Contact Kwelanga Support') </script>
                                     <?php
                                   print_r($errorTO);?> 
                                     <script type='text/javascript'>window.close()</script>                           	
                           <?php
                    }
                                                                                
                     	        
                           if($errorTO->type == FLAG_ERRORTO_SUCCESS) { ?>
                           	
                           	       <script type='text/javascript'>alert('Document Successfully Reset<?php echo "<br><br>"; ?>All Totals Recalculated<?php echo "<br><br>"; ?>Removed from any Tripsheets') </script>
                               
                                   <script type='text/javascript'>window.close()</script>  
                           	                       	
                              <?php	
                           } else { ?>
                           	       <script type='text/javascript'>alert('Document Reset Failed<?php echo "<br><br>"; ?>Contact Support'</script>
                                   <?php
                                   print_r($errorTO);?>
                                   <script type='text/javascript'>window.close()</script>
                           <?php        
                           }
                     } else { ?>
                           	       <script type='text/javascript'>alert('Document Reset Failed<?php echo "<br><br>"; ?>Contact Support'</script>
                                   <?php
                                   print_r($errorTO);?>
                                   <script type='text/javascript'>window.close()</script>
                           <?php
                     }
                     
                     unset($_POST['PROCESSCHANGE']);
                     unset($_POST['RESETDOC']);
                     unset($_POST['FIRSTFORM']);
               }
          }      
// ********************************************************************************************************************************	     
// Reset Accepted Status    
// ********************************************************************************************************************************	     
     if(isset($_POST['RESETACCST'])) {
     	
     	      if($_POST['CURSTAT'] == 'Accepted' ) {
     	      	     $changeStat = DST_ACCEPTED;
     	      	     $newAccStat = DST_UNACCEPTED;
     	      } else {
                   $changeStat = DST_UNACCEPTED;
                   $newAccStat = DST_ACCEPTED;
            }       
     	      $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
            $result = $ManageOrdersDAO->resetAcceptedStatus($_POST['DOCUID'], $changeStat, $newAccStat);
            
            if($result->type == 'S') { 
                    $result = $ManageOrdersDAO->insertInDocumentlog($_POST['DOCUID'], $userUId ,$changeStat , $newAccStat, 'Accepted Status Changed', '', 0);
                    if($result->type <> 'S') { ?>
                         <script type='text/javascript' >alert('Document Log Update Failed - Contact Kwelanga Support') </script> 
                         <?php
                            print_r($result); 
                         return;
                    }
            	
            	?>
                 <script type='text/javascript'>alert("Order <?php echo $_POST['DOCNO'];?> Status Reset Successfully ")</script> 
                 <?php
                 unset($_POST['UNCANCELORDER']); 
                 unset($_POST['SUBACT']); 
                 unset($_POST['UNCANCEL']);        	
                 ?>
                 <script type='text/javascript'>window.close()</script> 
                <?php
            } else {
                 ?>
                 <script type='text/javascript'>alert("Status reset Failed <?php echo $_POST['DOCNO'];?>")</script> 
                 <?php
            }             	
     	
     }
// ********************************************************************************************************************************	     
// Start Screen
// ********************************************************************************************************************************	     
     if (!isset($_POST['ACTIONTYPE']) && !isset($_POST['SUBACT']) && !isset($_POST['PROCESSCHANGE']) && !isset($_POST['RESETDOC'])) {
     	
         $ManageExistingOrder = new ManageExistingOrderScreens();
         $a = $ManageExistingOrder->selectItemToManage($docUid,  $userUId, $principalId);
     	
     }
// ********************************************************************************************************************************	      

   ?>      
	</body>       
 </HTML>
<?php 

 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 
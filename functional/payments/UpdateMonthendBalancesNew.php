<?php

ini_set('max_execution_time', 3600); //300 seconds = 5 minutes

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."libs/BroadcastingUtils.php");


class  UpdateMonthend  {
	
	 function UpdateMonthendBalancesNew($PrincipalId, $updateperiod, $updattype, $updateaccount,$updatbatch) {
	 	
	 	  global $ROOT, $PHPFOLDER;
      
      include_once($ROOT.$PHPFOLDER."DAO/CustomerBalancesDAO.php");

      $dbConn = new dbConnect();
      $dbConn->dbConnection();
      
      // Get principals whose balances are managed and stored

      //   Month End date for this update cycle
                   
             $todaysyear  = substr($updateperiod,0,4);
             $todaysmonth = substr($updateperiod,4,2);
      
             $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
             $periodarr             = $CustomerBalancesDAO->GetAgingDates($PrincipalId, ($todaysyear. "-". $todaysmonth . "-15" )) ;

             // Get all individual Payment Customers
             
             if($updattype == PAYMENT_BY_CUSTOMER) {
                   $CustomerBalancesDAO    = new CustomerBalancesDAO($dbConn);	
                     $indivGroupPaymentCust  = $CustomerBalancesDAO->GroupIndividualStatus($PrincipalId, $updattype, $updateaccount, $updatbatch) ;	  

                     if(count($indivGroupPaymentCust) == 0) {
                    	     $errorTO = new ErrorTO;
                    	     $errorTO->type   =  "S";
                    	     $errorTO->identifier   =  "No Customers to Update";
                    	     $errorTO->identifier2  = '';
                    	     return $errorTO;
                     }	
             } elseif($updattype == PAYMENT_BY_GROUP){
                   $CustomerBalancesDAO    = new CustomerBalancesDAO($dbConn);	
                     $indivGroupPaymentCust  = $CustomerBalancesDAO->GroupChainStatus($PrincipalId, $updatbatch) ;	  
                     if(count($indivGroupPaymentCust) == 0) {
                    	     $errorTO = new ErrorTO;
                    	     $errorTO->type   =  "S";
                    	     $errorTO->identifier   =  "No Group to Update";
                    	     $errorTO->identifier2  = '';
                    	     return $errorTO;
                     }	             	
             } else {
                     $errorTO = new ErrorTO;
                     $errorTO->type   =  "S";
                     $errorTO->identifier   =  "Invalid Payment Group";
                     $errorTO->identifier2  = '';
                     return $errorTO;
             	
             }
             
             $successfulUpdate = $failedUpdate = $successfulInsert = $failedInsert = 0;
           
             foreach($indivGroupPaymentCust as $indivGroupPaymentCustRow  ) {
             
                 foreach($periodarr as $periodrow) {
                        // Calcalulate 120 day amount - 120 days period range should include the past year
                        // Calculate monthly Sales  
                        
                        $d120sales   = $d90sales    = $d60sales    = $d60sales     = $dCurrentsales   = 0; 
                        $p120Payment = $p90Payment  = $p60Payment  = $p30Payment   = $pCurrentPayment = 0;
                        $day91Amount = $day90Amount = $day60Amount = $day30Amount  = $currentAmount   = 0;
                        $c120Credits = $c90Credits  = $c60Credits  = $c30Credits   = $cCurrentCredits = 0;
                       
                        // **************************************************** 
                        // Update all invoice values -- Begin    
                        // ****************************************************
                        $CustomerBalancesDAO = new CustomerBalancesDAO($dbConn);
                        $d120sales           = $CustomerBalancesDAO->FetchPeriodBalance($PrincipalId,
                                                                                        $periodrow['91 Start'],
                                                                                        $periodrow['91 End'],
                                                                                        $indivGroupPaymentCustRow['uid'],
                                                                                        $indivGroupPaymentCustRow['payment_by']) ;
                                                                                        
                        $CustomerBalancesDAO = new CustomerBalancesDAO($dbConn);
                        $d90sales            = $CustomerBalancesDAO->FetchPeriodBalance($PrincipalId,
                                                                                        $periodrow['90 Start'],
                                                                                        $periodrow['90 End'],
                                                                                        $indivGroupPaymentCustRow['uid'],
                                                                                        $indivGroupPaymentCustRow['payment_by']) ;             
                        $CustomerBalancesDAO = new CustomerBalancesDAO($dbConn);
                        $d60sales            = $CustomerBalancesDAO->FetchPeriodBalance($PrincipalId,
                                                                                        $periodrow['60 Start'],
                                                                                        $periodrow['60 End'],
                                                                                        $indivGroupPaymentCustRow['uid'],
                                                                                        $indivGroupPaymentCustRow['payment_by']) ;             
                        $CustomerBalancesDAO = new CustomerBalancesDAO($dbConn);
                        $d30sales            = $CustomerBalancesDAO->FetchPeriodBalance($PrincipalId,
                                                                                        $periodrow['30 Start'],
                                                                                        $periodrow['30 End'],
                                                                                        $indivGroupPaymentCustRow['uid'],
                                                                                        $indivGroupPaymentCustRow['payment_by']) ;             
                                       
                        $CustomerBalancesDAO = new CustomerBalancesDAO($dbConn);
                        $dCurrentsales       = $CustomerBalancesDAO->FetchPeriodBalance($PrincipalId,
                                                                                        $periodrow['Current Start'],
                                                                                        $periodrow['Current End'],
                                                                                        $indivGroupPaymentCustRow['uid'],
                                                                                        $indivGroupPaymentCustRow['payment_by']) ;                                
                        // **************************************************** 
                        // Update all invoice values -- End   
                        // ****************************************************
                        
                        // **************************************************** 
                        // Update all Payments -- Begin    
                        // ****************************************************
                        // 120 Days
                        // ****************************************************    
                        $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                        $p120paymentarr        = $CustomerBalancesDAO->FetchPeriodPayments($PrincipalId,
                                                                                           $periodrow['91 Start'],
                                                                                           $periodrow['91 End'],
                                                                                           $indivGroupPaymentCustRow['uid'],
                                                                                           $indivGroupPaymentCustRow['payment_by']) ; 
                                                                                           
                       
                        if(count($p120paymentarr) <> 0) {
                               $p120Payment = $p120Payment + $p120paymentarr[0]['payment_amount'];
                        }
                        // echo "<br>120 Day Payments<br>";
                        // echo round($p120Payment,0); 
                        // echo "<br>120 Day Payments<br>";
                                             
                        // ****************************************************                     
                        // 90 Days
                        $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                        $p90paymentarr         = $CustomerBalancesDAO->FetchPeriodPayments($PrincipalId,
                                                                                           $periodrow['90 Start'],
                                                                                           $periodrow['90 End'],
                                                                                           $indivGroupPaymentCustRow['uid'],
                                                                                           $indivGroupPaymentCustRow['payment_by']) ; 
                        if(count($p90paymentarr) <> 0) {      
                               $p90Payment = $p90Payment + $p90paymentarr[0]['payment_amount'];        
                        }
                        // ****************************************************                     
                        // 60 Days   
                                             
                        $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                        $p60paymentarr        = $CustomerBalancesDAO->FetchPeriodPayments($PrincipalId,
                                                                                           $periodrow['60 Start'],
                                                                                           $periodrow['60 End'],
                                                                                           $indivGroupPaymentCustRow['uid'],
                                                                                           $indivGroupPaymentCustRow['payment_by']) ; 
                                                                                           
                        if(count($p60paymentarr) <> 0) {  
                               $p60Payment = $p60Payment + $p60paymentarr[0]['payment_amount'];
                        }                      
                        // ****************************************************                     
                        // 30 Days   
                                      
                        $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                        $p30paymentarr         = $CustomerBalancesDAO->FetchPeriodPayments($PrincipalId,
                                                                                           $periodrow['30 Start'],
                                                                                           $periodrow['30 End'],
                                                                                           $indivGroupPaymentCustRow['uid'],
                                                                                           $indivGroupPaymentCustRow['payment_by']) ; 
                                                                                           
                        if(count($p30paymentarr) <> 0) {  
                               $p30Payment = $p30Payment + $p30paymentarr[0]['payment_amount'];
                        }    
                        // ****************************************************                     
                        // Current                                   
                        $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                        $pCurpaymentarr        = $CustomerBalancesDAO->FetchPeriodPayments($PrincipalId,
                                                                                           $periodrow['Current Start'],
                                                                                           $periodrow['Current End'],
                                                                                           $indivGroupPaymentCustRow['uid'],
                                                                                           $indivGroupPaymentCustRow['payment_by']) ; 
                                                                                           
                        if(count($pCurpaymentarr) <> 0) {
                               $pCurrentPayment = $pCurrentPayment + $pCurpaymentarr[0]['payment_amount'];

                        } 
                        // **************************************************** 
                        // Update all Payments -- End    
                        // **************************************************** 
                        
                        
                        // **************************************************** 
                        // Update all Credits -- Begin    
                        // ****************************************************
                        // First some housekeeping
                        $CustomerBalancesDAO    = new CustomerBalancesDAO($dbConn);
                        $updateUnmatchedCredits = $CustomerBalancesDAO->MatchPaymentsToInvoices($PrincipalId);
                        
                        // Check if manual credits with Documnet number in PO number have been matched to the invoices
                        
                        $CustomerBalancesDAO    = new CustomerBalancesDAO($dbConn);
                        $updateUnmatchedManualCredits = $CustomerBalancesDAO->MatchUnmatchedManualCreditsToInvoices($PrincipalId);
                        
                        $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                        $unmatchedInvArr = $CustomerBalancesDAO->GetUnmatchedCreditNotes($PrincipalId) ;
     
                        // 120 Days
                        // ****************************************************    
                        $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                        $c120creditarr         = $CustomerBalancesDAO->FetchPeriodCredits($PrincipalId,
                                                                                          $periodrow['91 Start'],
                                                                                          $periodrow['91 End'],
                                                                                          $indivGroupPaymentCustRow['uid'],
                                                                                          $indivGroupPaymentCustRow['payment_by']) ; 
print_r($c120creditarr);
                        if(count($c120creditarr) <> 0) {
                               $c120Credits = $c120Credits + $c120creditarr[0]['invoice_total'];
                        }                         
                        // 90 Days
                        // ****************************************************    
                        $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                        $c90creditarr         = $CustomerBalancesDAO->FetchPeriodCredits($PrincipalId,
                                                                                          $periodrow['90 Start'],
                                                                                          $periodrow['90 End'],
                                                                                          $indivGroupPaymentCustRow['uid'],
                                                                                          $indivGroupPaymentCustRow['payment_by']) ; 
                        
                        if(count($c90creditarr) <> 0) {
                               $c90Credits = $c90Credits + $c90creditarr[0]['invoice_total'];
                        }   
                        // 60 Days
                        // ****************************************************    
                        $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                        $c60creditarr         = $CustomerBalancesDAO->FetchPeriodCredits($PrincipalId,
                                                                                          $periodrow['60 Start'],
                                                                                          $periodrow['60 End'],
                                                                                          $indivGroupPaymentCustRow['uid'],
                                                                                          $indivGroupPaymentCustRow['payment_by']) ; 

                        if(count($c60creditarr) <> 0) {
                              $c60Credits = $c60Credits + $c60creditarr[0]['invoice_total'];

                        }    
                        // 30 Days
                        // ****************************************************    
                        $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                        $c30creditarr         = $CustomerBalancesDAO->FetchPeriodCredits($PrincipalId,
                                                                                          $periodrow['30 Start'],
                                                                                          $periodrow['30 End'],
                                                                                          $indivGroupPaymentCustRow['uid'],
                                                                                          $indivGroupPaymentCustRow['payment_by']) ; 

                        if(count($c30creditarr) <> 0) {
                               $c30Credits = $c30Credits + $c30creditarr[0]['invoice_total'];

                        }
                        // Current Days
                        // ****************************************************    
                        $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                        $cCurrentcreditarr     = $CustomerBalancesDAO->FetchPeriodCredits($PrincipalId,
                                                                                          $periodrow['Current Start'],
                                                                                          $periodrow['Current End'],
                                                                                          $indivGroupPaymentCustRow['uid'],
                                                                                          $indivGroupPaymentCustRow['payment_by']) ; 
                                                                                           
                        if(count($cCurrentcreditarr) <> 0) {
                             $cCurrentCredits = $cCurrentCredits + $cCurrentcreditarr[0]['invoice_total'];
                        }
                        // **************************************************** 
                        // Update all Credits -- End    
                        // **************************************************** 
                                                                                 
                  }     // end of Period loop 
                  
                  // **************************************************** 
                  // Update Customer Balance file
                  // ****************************************************                 
                  $day91Amount      = $d120sales      + $p120Payment      + $c120Credits;
                  $day90Amount      = $d90sales       + $p90Payment       + $c90Credits; 
                  $day60Amount      = $d60sales       + $p60Payment       + $c60Credits; 
                  $day30Amount      = $d30sales       + $p30Payment       + $c30Credits; 
                  $currentAmount    = $dCurrentsales  + $pCurrentPayment  + $cCurrentCredits;  
                  
                  $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                  $existarr              = $CustomerBalancesDAO->CheckForExistingCustomerBalances($PrincipalId,
                                                                                                  $periodrow['Current End'],
                                                                                                  $indivGroupPaymentCustRow['uid'],
                                                                                                  $indivGroupPaymentCustRow['payment_by']); 
                  if(count($existarr) > 0 ) {
                       $updateType = "Update";
                       $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                       $errorTO             = $CustomerBalancesDAO->UpdateCustomerBalance($existarr[0]['uid'],
                                                                                            $currentAmount,
                                                                                            $day30Amount,
                                                                                            $day60Amount,
                                                                                            $day90Amount,
                                                                                            $day91Amount,
                                                                                            $indivGroupPaymentCustRow['bname']);
                  if ($errorTO->type <> FLAG_ERRORTO_SUCCESS) {   
                          $failedUpdate++;
                          BroadcastingUtils::sendAlertEmail('Failed Balance Update - 1 - ', trim($indivGroupPaymentCustRow['bname']) . " Uid - " . $existarr[0]['uid'] , "Y", false);
                  } else {
                          $successfulUpdate++;
                  }	
                  
                  } else {
                       $updateType = "Insert";
                       $CustomerBalancesDAO   = new CustomerBalancesDAO($dbConn);
                       $errorTO               = $CustomerBalancesDAO->InsertIntoCustomerBalance($PrincipalId,
                                                                                                $periodrow['year'],
                                                                                                $periodrow['period'],
                                                                                                $periodrow['Current End'],
                                                                                                $indivGroupPaymentCustRow['payment_by'],
                                                                                                $indivGroupPaymentCustRow['uid'],
                                                                                                $currentAmount,
                                                                                                $day30Amount,
                                                                                                $day60Amount,
                                                                                                $day90Amount,
                                                                                                $day91Amount,
                                                                                                $indivGroupPaymentCustRow['bname']);
                       if ($errorTO->type <> FLAG_ERRORTO_SUCCESS) {   
                              $failedInsert++;
                              BroadcastingUtils::sendAlertEmail('Failed Balance Update - 2 - ', trim($indivGroupPaymentCustRow['bname']) . " Uid - " . $existarr[0]['uid'] , "Y", false);
                       } else {
                              $successfulInsert++;
                       }	
                  }
             } // End of Customer Loop
             
             $errorTO->identifier   =  "Inserted - " . str_pad($successfulInsert,3," ",STR_PAD_LEFT) . "   Updated - " . str_pad($successfulUpdate,3,".",STR_PAD_LEFT);
             $errorTO->identifier2  =  "Inserted - " . str_pad($successfulInsert,3," ",STR_PAD_LEFT) . "   Updated - " . str_pad($failedInsert,3,".",STR_PAD_LEFT);
             
             return $errorTO;
             
             // Process all customers for each periodUpdate balance for customer
      // *************************************************************************************************************************
   } // End of function
}

?>

<?php 
   include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/ZeroInvoicedDAO.php');    
    include_once($ROOT.$PHPFOLDER.'DAO/db_Connection_Class.php');
    include_once($ROOT.$PHPFOLDER.'DAO/postDistributionDAO.php');
    include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/messagingDAO.php');
    include_once($ROOT.$PHPFOLDER.'functional/ws/bulk_sms/bulkSmsClass.php');

 /*  
   if (!isset($_SESSION)) session_start() ;
      $userUId      = $_SESSION['user_id'] ;
      $principalId  = $_SESSION['principal_id'] ;
      $wareHouseCde = $_SESSION['depot_id'] ;
      $systemName   = $_SESSION['system_name'] ;
 */     
      $principalId =216;
      
      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
//********************************************************************************************************************************************************
   		
       $ZeroInvoicedDAO = new ZeroInvoicedDAO($dbConn);
       $gNt = $ZeroInvoicedDAO->getNotificationRecip($principalId);
       
       if(count($gNt) != 0) {

                $storeCust = '';
       	      	foreach($gNt as $row) {
       	      		
       	      		    $nt = "F";
       	      		
       	      		    $successUidArr = array();
       	      		
                      // Get Report data
       	              $ZeroInvoicedDAO = new ZeroInvoicedDAO($dbConn);
                      $InvoicesZero = $ZeroInvoicedDAO->getZeroInvoices($principalId, $gNt[0]['depot_uid'], $row['ContactUid']); 
                        		
                      // echo "<pre>";
   	                  // print_r ($InvoicesZero);
       	              // echo "<br>";
       	              if(count($InvoicesZero) != 0) {
      	      	     	        $noteType = explode(",",$row['notify_type']);
       	      	     	     
       	      	     	        if(in_array(NT_EMAIL, $noteType)) {
       	      	     	        	
       	      	     	        	      $bodyString = '';
                            
                                      // Set up new distribution TO
                                      $postingDistributionTO = new PostingDistributionTO($dbConn);
                                      $postingDistributionTO->DMLType = "INSERT";
                                      $postingDistributionTO->deliveryType = BT_EMAIL;
                            
                                      $messagingDAO = new messagingDAO($dbConn);
                                      $postingDistributionTO->subject = $messagingDAO->getTemplateZeroLinesSubject($elRow['Principal']);
                                      $postingDistributionTO->destinationAddr =  trim($row["email_addr"]); 
                            
                                      $messagingDAO = new messagingDAO($dbConn);
                                      $bodyString = $bodyString . $messagingDAO->getTemplateBodyZeroLinesHeader();       	      	     	      	
       	      	     	          	    $storeCust = ''; 
                                      foreach($InvoicesZero as $elRow) {
                                      	   if($storeCust != trim($elRow['Store_Name']) && $storeCust != '') {
                                      	         $bodyString = $bodyString . $messagingDAO->getTemplateBodyZeroLinesSpace(); 	
                                      	   }
                                           $storeCust = trim($elRow['Store_Name']);
                                           if(trim($elRow['whShort']) == '') {
                                           	     $wh = trim($elRow['Warehouse']);   
                                           } else {
                                           	     $wh = trim($elRow['whShort']); 
                                           }
                                           $docNo = ltrim($elRow['Document_Number'],'0'); 
                                           $idate = trim($elRow['invoice_date']);
                                           $deliver_name = trim($elRow['Store_Name']);
                                           $product      = trim($elRow['Product_Name']);
                                           $ordQty       = trim($elRow['Ordered_Quantity']);
                                           $invQty       = trim($elRow['Invoice_Quantity']);
                                           $short        = trim($elRow['Short']);
                                           
                                           $bodyString = $bodyString . $messagingDAO->getTemplateZeroLinesBody($wh, 
                                                                                                               $docNo, 
                                                                                                               $idate, 
                                                                                                               $deliver_name, 
                                                                                                               $product, 
                                                                                                               $ordQty, 
                                                                                                               $invQty, 
                                                                                                               $short);
                                      
                                           if(!in_array($elRow['docUid'],$successUidArr)) {
       	      	     	                          $successUidArr[] =  $elRow['docUid'];	
                                           } 
                                      } 
                                      $messagingDAO = new messagingDAO($dbConn);
                                      $bodyString = $bodyString . $messagingDAO->getTemplateBodyZeroLinesEnd();
                        	  	    	
                                      $postDistributionDAO = new postDistributionDAO($dbConn);
                                      $postingDistributionTO->body = $bodyString;
                                      
                                      $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
                            
       	      	     	          	    echo "Send Mail <br>";
       	      	     	          	    $nt = "T";
       	      	     	          	    $dbConn->dbinsQuery("commit;");     
       	      	     	        } 
       	      	     	        
       	      	     	        if(in_array(NT_SMS, $noteType)) {
       	      	     	          	
       	      	     	          	    $smsMessage = '';
       	      	     	          	    $storeCust  = '';
       	      	     	          	    $stDetail   = '';
       	      	     	          	    $smsNumber  = '+27'. substr($row['mobile_number'],1,9);
       	      	     	          	     
       	      	     	          	    foreach($InvoicesZero as $smRow) {
                                      	   if($storeCust != trim($smRow['Store_Name']) . "-" . trim($smRow['$stDocNo']) && $storeCust != '') {
                                      	   	
                                                 $smsMessage = $smsMessage. "Lines Not Invoiced \n";
       	      	     	          	               $smsMessage = $smsMessage. "Warehouse " . $stWh . " \n";
       	      	     	          	               $smsMessage = $smsMessage. "Customer "  . $stDelName . " \n";
       	      	     	          	               $smsMessage = $smsMessage. "Doc No  "   . $stDocNo . " \n";
       	      	     	          	               
       	      	     	          	               if(strlen($stDetail) > 440) {
       	      	     	          	               	     $smsDetail = substr($stDetail,0,440) . "\n List Shortened..";
       	      	     	          	               } else {
       	      	     	          	                     $smsDetail = $stDetail;
       	      	     	          	               }
       	      	     	          	               
       	      	     	          	               $smsMessage = $smsMessage . $smsDetail;
       	      	     	          	               
       	      	     	          	               echo "SMS Start<br>";
                                      	         echo $smsMessage;
                                      	         echo "<br>";
                                      	         
                                      	         $sndSms = new sendSmsClass($dbConn);
                                      	         $smsRes = $sndSms->sendSMS($smsNumber, $smsMessage);
                                      	         
                                      	         //echo "Call SMS <br><br><br><br>";
       	      	     	          	               
       	      	     	          	               $smsMessage = '';
       	      	     	          	               $stDetail  = '';
       	      	     	          	               
                                      	   }
                                      	   $storeCust = trim($smRow['Store_Name']) . "-" . trim($smRow['$stDocNo']);
                                      	   
                                      	   $stWh      = $wh;
                                      	   $stDelName = trim($smRow['Store_Name']);
                                      	   $stDocNo   = ltrim($smRow['Document_Number'],'0');
                                      	   
                                      	   $stDetail  = $stDetail . trim($smRow['product_code'])     . "\n";
                                      	   $stDetail  = $stDetail . "Ord- " .trim($smRow['Ordered_Quantity']) . " ";
                                      	   $stDetail  = $stDetail . "Inv- " .trim($smRow['Invoice_Quantity']) . "\n";
                                      	   
                                      	   if(!in_array($smRow['docUid'],$successUidArr)) {
                                                 // Flag Document as sent to contact
       	      	                                 $successUidArr[] =  $smRow['docUid'];	
                                           }
                                      } 
   
       	      	     	                $smsMessage = $smsMessage. "Lines Not Invoiced \n";
       	      	     	                $smsMessage = $smsMessage. "Warehouse " . $stWh . " \n";
       	      	     	                $smsMessage = $smsMessage. "Customer "  . $stDelName . " \n";
       	      	     	                $smsMessage = $smsMessage. "Doc No  "   . $stDocNo . " \n";
       	      	     	                     
       	      	     	                if(strlen($stDetail) > 440) {
       	      	     	          	          $smsDetail = substr($stDetail,0,440) . "\n List Shortened..";
       	      	     	          	    } else {
       	      	     	          	          $smsDetail = $stDetail;
       	      	     	          	    }
       	      	     	          	       
       	      	     	          	               $smsMessage = $smsMessage . $smsDetail;
       	      	     	                
       	      	     	                $nt = "T";
       	      	     	          
       	      	     	                // echo "SMS Start<br>";
                                	     echo $smsMessage;
                                      	         echo "<br>";
                                	    // echo "Call SMS <br>";
       	      	     	                echo "Send SMS <br>";
       	      	     	                 $sndSms = new sendSmsClass($dbConn);
                                       $smsRes = $sndSms->sendSMS($smsNumber, $smsMessage);
       	      	     	                //echo "End <br><br><br><br>";
       	      	     	                
       	      	     	                if(!in_array($smRow['docUid'],$successUidArr)) {
                                           // Flag Document as sent to contact
       	      	                           echo "<br>Adding<br>" . $smRow['docUid'];
       	      	     	                     $successUidArr[] =  $smRow['docUid'];	
                                      }
       	      	     	        }
       	      	     	        if($nt <> "T") {
       	                            echo "<br>";
       	                            echo "User Bomb Out - No Notify Type for " . $row['ContactUid'] ;
       	                            echo "<br>";
       	                            continue;
       	                      }              	      	     	    	
       	      	     	} else {
       	                         echo "<br>";
       	                         echo "No Report data found For Contact";
       	                         echo "<br>";
       	              }
       	              if(count($successUidArr) !=0 ) {
       	      		          foreach($successUidArr as $uRow) {
       	      		    	         $ZeroInvoicedDAO = new ZeroInvoicedDAO($dbConn);
                                 $errorTO = $ZeroInvoicedDAO->updateZeroInvSmartEvent($row['ContactUid'], $uRow);
                           
                                 if($errorTO->type != 'S') {
                                    echo "<br>";
       	                            echo "User Bomb Out - No Notify Type for " . $row['ContactUid'] ;
       	                            echo "<br>";
                                 } 
       	      		          }
       	      	      }
       	      	}
       	      	echo "<br>[***EOS***]<br>";

       } else {
       	     echo "<br>";
       	     echo "Bomb Out - No contact found for this report";
       }
       
//********************************************************************************************************************************************************
?>       
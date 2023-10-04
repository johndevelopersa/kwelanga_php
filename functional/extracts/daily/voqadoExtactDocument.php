<?php

/* * ********************************************************************************************
 * *
 * *  This job can run as many times per day as is necessary according to job scheduler.
 * *
 * *  It executes notifications that occur throughout the day by triggers
 * *
 * ******************************************************************************************** */

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "functional/extracts/daily/extractController.php");

include_once __DIR__ . "/../../../libs/api/voqado-api/VoqadoRestAPI.php";
include_once $ROOT . $PHPFOLDER . "functional/jobs/voqado/VoqadoConstants.php";

include_once $ROOT . $PHPFOLDER . "functional/jobs/voqado/VoqadoParameters.php";

include_once($ROOT . $PHPFOLDER . "DAO/onlineManageDAO.php"); 

$prinId = $je['principal_uid'];

//static method handler.
class voqadoExtactDocument {
  public static function generateOutput() {
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn, $prinId;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class voqadoExtactDocumentInit extends extractController {
	
     public function generateOutput() {
     	
          global $ROOT, $PHPFOLDER, $prinId;
          
          if (!isset($dbConn)) {
              $dbConn = new dbConnect();
              $dbConn->dbConnection();
          } 
     
          $voqadoParms = new VoquadoParms($this->dbConn);
          $vPa = $voqadoParms->getPrincipalParams($prinId); 	
	        
	        $principalUid       = $vPa[0]['principal_uid']; //uid of principal extract.
	        
	        $voqadocode         = $vPa[0]['voqado_code'];
  
	        $specialFieldUid    = $vPa[0]['voqado_account_field'];
	        
	        $specialFieldActive = $vPa[0]['use_account_field2'];
	        $specialFieldUid2   = $vPa[0]['voqado_account_field2'];	        
	        
	        $noteId             = $vPa[0]['notification_uid'];
	        
	        if ($vPa[0]['daily_extract_custom'] == 'NT_DAILY_EXTRACT_CUSTOM') {
	              $dailyExtractCustom = '10';       	
	        } elseif ($vPa[0]['daily_extract_custom'] == 'NT_DAILY_EXTRACT_ALTCUSTOM1') {
	              $dailyExtractCustom = '12';       	
	        } elseif ($vPa[0]['daily_extract_custom'] == 'NT_DAILY_EXTRACT_ALTCUSTOM2') {
	              $dailyExtractCustom = '13';       	
	        } elseif ($vPa[0]['daily_extract_custom'] == 'NT_DAILY_EXTRACT_ALTCUSTOM3') {
	              $dailyExtractCustom = '14';       	
	        } elseif ($vPa[0]['daily_extract_custom'] == 'NT_DAILY_EXTRACT_ALTCUSTOM4') {
	              $dailyExtractCustom = '15';       	
	        } else {
	              $dailyExtractCustom = '';       	
	        }	
	        
	        $accountsToExclude  = $vPa[0]['accounts_to_exclude'];          
	        
          //name in email and folder to place bkup files.
          $pArr = $this->principalDAO->getPrincipalItem($principalUid);
          if (count($pArr)==0) {
              BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
              return $this->errorTO;
          }
          $principalName = $pArr[0]['principal_name'];
          $folder = $principalUid . '_' . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.
       
       
          //use the receipients listed in the notification table instead of hard coding them!!!
          //expecting only one row loaded per principal extract
          $reArr = $this->bIDAO->getNotificationRecipients($principalUid, $dailyExtractCustom);
          if (count($reArr)==0) {
              BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
              return $this->errorTO;
          }
          $recipientUId = $reArr[0]['uid'];
       
          if (!$this->skipInsert) {
              // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
              $rTO = $this->postExtractDAO->queueAllInvoiced($principalUid, $recipientUId, $inclCancelled = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
              if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
                   BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
              } else {
                   $this->dbConn->dbinsQuery("commit;");
              }
              //credits and debit notes
            $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($principalUid, $recipientUId, array(DT_CREDITNOTE,DT_MCREDIT_OTHER, DT_MCREDIT_PRICING));  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
            if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
                BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
            } else {
                $this->dbConn->dbinsQuery("commit;");
            }
          }
          $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($principalUid, $recipientUId);
     
          /*  SUCCESS POINT - 1  */
          //nothing to do...
          if(count($seDocs)==0){
              echo "Successfully Completed Extract : ".get_class($this)." - No entries!<br>";
              $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
              $this->errorTO->description = "Successful";
             return $this->errorTO;
          }
     
          //group array
          $grpDocs = array();
          $psms=array();
          foreach($seDocs as $k=>$r){
     
               $type = 'i';
               $do_allocations = 'N';
               if ($r['document_type_uid'] == DT_CREDITNOTE) {
                    $type = 'c';
                    $do_allocations = 'N';
               } elseif ($r['document_type_uid'] == DT_MCREDIT_OTHER) {
                    $type = 'm';
                    $do_allocations = 'N';
               } elseif ($r['document_type_uid'] == DT_MCREDIT_PRICING) {
                    $type = 'm'; 
                    $do_allocations = 'N';     
             }

             $grpDocs[$type][$r['dm_uid']][] = $r;
             $psms[$r["principal_store_uid"]] = $r["principal_store_uid"];
          }
    
          // get special field values for all stores in above docs
          if (sizeof($psms)>0) {
               $sfvals_PA  = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($principalUid, $specialFieldUid, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
               $sfvals_PA2 = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($principalUid, $specialFieldUid2, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
          }
          //setup api class
          $voqadoApi = new VoqadoRestAPI(VoqadoConstants::ApiUri, VoqadoConstants::ApiUsername, VoqadoConstants::ApiPassword);
    
          $successCount = $errorCount = 0;
          foreach($grpDocs as $type => $orders){      	
             // print_r($orders);
    
               $errorSEUIdArr = array();
               $successSEUIdArr = array();
               
               foreach($orders as $ord){
               	   if(empty($sfvals_PA[$ord[0]["principal_store_uid"]]['value']) && empty($sfvals_PA2[$ord[0]["principal_store_uid"]]['value'])  ) { 
               	   	    $sfd = "no Special Field"; 
               	   } else { 
               	   	
               	  //echo $ord[0]['buyer_account_reference'];
               	  //echo "b<br>"; 
               	  //echo trim($specialFieldActive);
               	  //echo "e<br>";             	   	
               	   	    if(trim($ord[0]['buyer_account_reference']) == trim($specialFieldActive) && trim($specialFieldActive) <> '' ) {
                             $sfd = $sfvals_PA2[$ord[0]["principal_store_uid"]]['value'];	
                             $storeAcc = trim($sfvals_PA2[$ord[0]["principal_store_uid"]]['value']);
               	   	    } else {
                             $sfd = $sfvals_PA[$ord[0]["principal_store_uid"]]['value'];
                             $storeAcc = trim($sfvals_PA[$ord[0]["principal_store_uid"]]['value']); 
               	   	    }
               	   }
//                   echo $sfd . '   -    ' . substr(trim($ord[0]["deliver_name"]),0,30). '';
//                   echo "<br>";               	   
                   if(empty($sfvals_PA[$ord[0]["principal_store_uid"]]['value']) && empty($sfvals_PA2[$ord[0]["principal_store_uid"]]['value'])) {  //has no special field and/or blank...
                         $errorSEUIdArr[] = $ord[0]['se_uid'] .'&' . 'No Special Field' ; //list of smart event errors
                   } else {
           	          //setup header
                      echo "<pre style='font-size:14px;'>";
                      echo str_repeat("-", 75) . "\n";
                      echo "Voqado REST API - Post Invoices\n";
                      echo str_repeat("-", 75) . "\n";

                      // php settings
                      set_time_limit(15 * 60); // 15 mins
                      error_reporting(-1);
                      ini_set('display_errors', 1); 
                  
                     $period = '00';
                     $yy     = '';
                     switch (date("m", strtotime($ord[0]["invoice_date"]))) {
                        case '01':
                        $period = $vPa[0]['period_month_1'];
                        $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        break;
                     case '02':
                        $period = $vPa[0]['period_month_2'];
                        $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        break;
                     case '03':
                        $period = $vPa[0]['period_month_3'];
                        if($principalUid == 207) {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        } else {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;	
                        }
                        break;
                     case '04':
                        $period = $vPa[0]['period_month_4'];
                        if($principalUid == 207) {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        } else {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;	
                        }
                        break;
                     case '05':
                        $period = $vPa[0]['period_month_5'];
                        if($principalUid == 207) {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        } else {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;	
                        }
                        break;
                     case '06':
                        $period = $vPa[0]['period_month_6'];
                        if($principalUid == 207) {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        } else {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;	
                        }
                        break;
                     case '07':
                        $period = $vPa[0]['period_month_7'];
                        if($principalUid == 207) {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        } else {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;	
                        }
                        break;
                     case '08':
                        $period = $vPa[0]['period_month_8'];
                        if($principalUid == 207) {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        } else {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;	
                        }
                        break;
                     case '09':
                        $period = $vPa[0]['period_month_9'];
                        if($principalUid == 207) {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        } else {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;	
                        }
                        break;
                     case '10':
                        $period = $vPa[0]['period_month_10'];
                        if($principalUid == 207) {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        } else {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;	
                        }
                        break;
                     case '11':
                         $period = $vPa[0]['period_month_11'];
                         if($principalUid == 207) {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        } else {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;	
                        }
                         break;
                     case '12':
                         $period = $vPa[0]['period_month_12'];
                         if($principalUid == 207) {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                        } else {
                             $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;	
                        }
                         break;
                     }
                     
                     if($type == 'i') {
                           $docType = 'IN';
                           $docName = 'Invoice';
                           if($principalUid == 71) {
                                 $docNo   = ltrim($ord[0]['invoice_number'],'0');
                           } else {
                           	     $docNo   = ltrim($ord[0]['document_number'],'0');
                           }
                           $PoSNo1   = trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"]));
                           $PoSNo   = trim(str_replace("#",' ',$PoSNo1));
                           $allocationref    = '';
                           $allocationAmount = 0 ;
                           $allocationrefto  = '';                
                     } elseif ($type == 'm' ) {
                           $docType = 'CR';
                           $docName = 'Credit Note';
                           if(trim($ord[0]['alternate_document_number']) == '' ) {
                                $docNo    = ltrim($ord[0]['document_number'],'0');	                    	
                           } else {
                          	     $docNo   = ltrim($ord[0]['alternate_document_number'],'0'); 
                           }
                                        
                           $PoSNo1   = trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"]));
                           $PoSNo    = trim(str_replace("#",' ',$PoSNo1));
                           $allocationref    = '';
                           $allocationAmount = 0 ;
                           $allocationrefto  = '';    
                     } else {
                           $docType = 'CR';
                           $docName = 'Credit Note';
                           if(trim($ord[0]['alternate_document_number']) == '' ) {
                               $docNo   = ltrim($ord[0]['document_number'],'0');	                    	
                           } else {
                    	         $docNo   = ltrim($ord[0]['alternate_document_number'],'0'); 
                           }      
                           $POSNo1   = trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"]));
                           $PoSNo   = trim(str_replace("#",' ',$POSNo1));
                           $allocationref    = preg_replace("/[\n\r\t]/","",ltrim($docNo,'0'));
                           $allocationAmount = preg_replace("/[\n\r\t]/","",round($ord[0]['invoice_total'] + 0, 2)) ;
                           $allocationrefto  = preg_replace("/[\n\r\t]/","",ltrim($ord[0]['source_document_number'],'0'));
                     }           
                     $iDate   =  date("d/m/Y", strtotime($ord[0]["invoice_date"]));
                     $yDate   =  $yy ;
                     $deliverName = substr(trim($ord[0]["deliver_name"]),0,30);
                     
                     if($accountsToExclude == NULL || !in_array($storeAcc, explode(",", $accountsToExclude))) {
                     	   if($do_allocations == 'N') {
                                $invoiceData = [
                                               's_coyid'      => $voqadocode,
                                               's_code'       => $storeAcc,
                                               's_sname'      => $deliverName,
                                               's_ref'        => $docNo,
                                               's_type'       => $docType,
                                               's_desc'       => $docName,
                                               's_tyear'      => $yDate ,
                                               's_period'     => $period,
                                               's_tdate'      => $iDate,
                                               's_custono'    => $PoSNo ,
                                               's_exclamount' => round($ord[0]['exclusive_total'], 2) ,
                                               's_vamt'       => round($ord[0]['vat_total'], 2),
                                               's_amount'     => round($ord[0]['invoice_total'] + 0, 2),
                                               's_userid'     => 'TS',
                                               's_transactionstatus' => 'A',
                                               'smsadata' => [],
                                               'oiallocations' => []
                                               ];
                         } else {
                         	      $invoiceData = [
                                               's_coyid'      => $voqadocode,
                                               's_code'       => $storeAcc,
                                               's_sname'      => $deliverName,
                                               's_ref'        => $docNo,
                                               's_type'       => $docType,
                                               's_desc'       => $docName,
                                               's_tyear'      => $yDate ,
                                               's_period'     => $period,
                                               's_tdate'      => $iDate,
                                               's_custono'    => $PoSNo ,
                                               's_exclamount' => round($ord[0]['exclusive_total'], 2) ,
                                               's_vamt'       => round($ord[0]['vat_total'], 2),
                                               's_amount'     => round($ord[0]['invoice_total'] + 0, 2),
                                               's_userid'     => 'TS',
                                               's_transactionstatus' => 'A',
                                               'smsadata' => [],
                                               'oiallocations' => [
                                                                     ['s_allocationref'    => $allocationref,
                                                                      's_allocationrefto'  => $allocationrefto,
                                                                      's_allocationamount' => abs($allocationAmount)                              
                                                                     ],
                                                                     ['s_allocationref'    => $allocationrefto,
                                                                      's_allocationrefto'  => $allocationref,
                                                                      's_allocationamount' => $allocationAmount
                                                                     ]                                                           
                                                                  ] 
                                                ];
                         }
                       // display the sent data!
                       $response = $voqadoApi->Request("POST", "vqdebtortransactions/upddrtn", $invoiceData);
                       //echo "<br>";
                       //print_r($response) 	   ;                 
                       //echo "<br>";
                       if ($response->getSuccess()) {
                           
                               $body = $response->getBody();
                               if(array_key_exists('Reference', $body)) {
                               	    if($body['Reference'] == $docNo) {
                                         echo "SUCCESS!  " . $docNo . "\n<br>";
                                         $successCount++;
                                         $successSEUIdArr[] = $ord[0]['se_uid'] . '&' . 'Success: ' . $docNo . '%' . ''; // list of smart event success
                                	    }  else {
                               	         echo 'Bomb Out - success does not equal Docno'	;
                               	         $errorSEUIdArr[] = $ord[0]['se_uid']  .'&' . 'Bomb Out' ;   // list of smart event errors
                               	         $errorCount++;
                               	         $gen2 = '';
                               	    }
                               } elseif(array_key_exists('Error', $body)) {
                               
                                     if(strtoupper($body['Error']) == 'DUPLICATE REFERENCE' ) {
                                     	   echo strtoupper($body['Error']) . ' - ' . $ord[0]['se_uid'] . "\n<br>";
                               	         $successCount++;
                                         $successSEUIdArr[] = $ord[0]['se_uid'] . '&' . 'Success: ' . $docNo . '%' . 'DUPLICATE REFERENCE'; // list of smart event success
                                     } else {
                               	         echo "ERROR!\n<br>";
                               	         if(strtoupper($body['Error']) == 'INVALID DEBTOR CODE') {
                               	    	        $gen1 = 'Invalid Debtor Code';
                               	    	        $gen2 = '';
                               	         } else {
                               	         	    echo $response->getErrorMessage();
                               	    	        $gen1 = 'Unknown Error - What`s up here';
                               	    	        $gen2 = '';
                               	         }
                                         $errorSEUIdArr[] = $ord[0]['se_uid'] .'&' . $gen1 ;   // list of smart event errors
                                         $errorCount++;
                                     }
                               }
                       } else {
                                 echo "ERROR!\n<br>";
                                 echo $response->getErrorMessage();
                                 $gen1 = 'Un Known Error API Failure';
                          	     $gen2 = '';
                       }
                     }     
                   } //     eo special field check
               } //eo documents
               // Handle notification of the errors
               
               if (sizeof($errorSEUIdArr) > 0) {
               	     
                     $bIResult = $this->postBIDAO->setSmartEventStatusIndivNew($errorSEUIdArr, FLAG_ERRORTO_ERROR);
                     if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                           $this->errorTO->type = FLAG_ERRORTO_ERROR;
                           $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
                           BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
                           return $this->errorTO;
                     } else {
                           $fr = 'Y';
                           $eList = '';
                           foreach($errorSEUIdArr as $row ) {
                                if($fr == 'Y') { 
                                	  $sep = ""; 
                                } else {
                                	  $sep = ",";  
                                }
                                $fr = 'N';
                                $eList = $eList . $sep . substr($row,0,strpos($row, '&'));
                     	     }
                     }	     
                     $onlineErrorManagmentDAO = new onlineErrorManagmentDAO($dbConn);
                     $mLST = $onlineErrorManagmentDAO->getKosNotificationRecipientsAdditionalParm($principalUid, $eList, CTD_KOS_ACCOUNTS, $noteId);
                              
               } else {
               	
               	     $onlineErrorManagmentDAO = new onlineErrorManagmentDAO($dbConn);
                     $mLST = $onlineErrorManagmentDAO->getKosNotificationRecipientsNoErrors($principalUid,CTD_KOS_ACCOUNTS);
               }      	     

               $storeString = '';
               $bodyString = '';
               $docnosmArr = array();                           
               $c= 0;
               foreach($mLST as $elRow) { 
                // 	echo "<pre>";
                // 	echo($elRow['email_addr']);
                //	echo (implode(',',$docnosmArr));
               	
                    if($storeString <> trim($elRow['email_addr'])) {
                         if($storeString <> '' ) {
                         	
                             $onlineErrorManagmentDAO = new onlineErrorManagmentDAO($dbConn);
                             $bodyString = $bodyString . $onlineErrorManagmentDAO->getTemplateKosBodyErrorend($principalUid, 
                                                                                                              $specialFieldUid,
                                                                                                              $noteId,
                                                                                                              $storeString);

                             $postingDistributionTO->body = $bodyString;

                             $postDistributionDAO = new postDistributionDAO($dbConn);
                             $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
                             $dbConn->dbinsQuery("commit;");
                             $bodyString = '';
                             $docnosmArr = array();
                         }
                         // Set up new distribution TO
                         $postingDistributionTO = new PostingDistributionTO;
                         $postingDistributionTO->DMLType = "INSERT";
                         $postingDistributionTO->deliveryType = BT_EMAIL;
                         $onlineErrorManagmentDAO = new onlineErrorManagmentDAO($dbConn);
                         $postingDistributionTO->subject = $onlineErrorManagmentDAO->getTemplateVoqadoImportErrorSubject('KOS Accounts - ' . trim($elRow['Principal'])); 
                         $postingDistributionTO->destinationAddr =  trim($elRow["email_addr"]); 
                                                  
                         if($type == 'i') {$dt = 'Invoices';} else { $dt = 'Credit Notes';}
    	  	    
                         $onlineErrorManagmentDAO = new onlineErrorManagmentDAO($dbConn);
                         $bodyString = $bodyString . $onlineErrorManagmentDAO->getTemplateKosBodyErrorHeader($elRow['Principal'], $noteId, count($successSEUIdArr), count($errorSEUIdArr), $dt);

                         $postingDistributionTO->destinationAddr = $elRow['email_addr'];
                         $storeString = trim($elRow['email_addr']);
    	  	          }
    	  	          
    	  	          if (sizeof($errorSEUIdArr) > 0 ) {
                         $onlineErrorManagmentDAO = new onlineErrorManagmentDAO($dbConn);
                         $bodyString = $bodyString . $onlineErrorManagmentDAO->getTemplateBodyKosErrorBody($elRow['document_number'], $elRow['invoice_date'], substr($elRow['deliver_name'],0,30), $elRow['general_reference_1'], $elRow['dataUid'], $elRow['psm.uid'], $elRow['type']);    	  
                         $docnosmArr[] = $elRow['document_number'];
                    }                       
               }
               $onlineErrorManagmentDAO = new onlineErrorManagmentDAO($dbConn);
               $bodyString = $bodyString . $onlineErrorManagmentDAO->getTemplateKosBodyErrorend($principalUid, 
                                                                     $specialFieldUid,
                                                                     $noteId,
                                                                     $elRow['email_addr']);
                                                                     
               
               $postingDistributionTO->body = $bodyString;
               
               $postDistributionDAO = new postDistributionDAO($dbConn);
               $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
               
               $dbConn->dbinsQuery("commit;");
          }
          /*
           *  UPDATE SMART EVENT in BULK
           */
          //SUCCESSFUL ITEMS
          echo "<br>End";
          
          if (sizeof($successSEUIdArr) > 0) {
              foreach($successSEUIdArr as $line) {
                   $errUid = substr($line,0,strpos($line, '&'));
                   $gen1 = trim(substr($line,strpos($line, '&') + 1,strpos($line, '%') - strpos($line, '&') -1 ));
                   $gen2 = trim(substr($line,strpos($line, '%') + 1,20));
                   
                   $bIResult = $this->postBIDAO->setSmartEventStatusBulk($errUid,$gen1, $gen2);
                   if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                       $this->errorTO->type = FLAG_ERRORTO_ERROR;
                       $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
                       BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
                      return $this->errorTO;
                   }
          	  }
          }    
              /*------------------------------------------------------------------------------------------------------------------------*/

          echo "KOS - Successfully Completed Extract : ".get_class($this) . "<br>" . $pArr[0]['principal_name'] . "<br>" . "Documents Uploaded " . $successCount ;

          /*  SUCCESS POINT - 2  */
          $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
          $this->errorTO->description = "Successful";
          return $this->errorTO;

     }    
}

//direct run!
if ($runMe) {
  directRunExtract(__FILE__);
}

<?php

// "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/import/file_import/SGXstockFile.php?PRIN=412";


/* * ********************************************************************************************
 * *
 * *  Import Stock Balances from SGX  - XML File
 * *
 * ********************************************************************************************** */

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/SgxImportDAO.php");  

$prin     = ((isset($_GET["PRIN"]))?$_GET["PRIN"]:"");

if($prin == 412) {
      $constantsFile = "UppSGXConstants";
} elseif($prin == 4) {
      $constantsFile = "BrenncoSGXConstants";
} else {
      $constantsFile = "";
      echo "<br>";
      echo "<br>BOMB OUT - No Constants File<br>";
      echo "<br>";
}

require_once __DIR__ . "/../../../properties/" .$constantsFile. ".php";

set_time_limit(15*60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    
}

echo "<pre style='font-size:14px;'>";
echo str_repeat("-", 75) . "\n";
echo "Import SGX Stock \n";
echo str_repeat("-", 75) . "\n";


$results = array();

$path = $ROOT .  'ftp/sgx/in/';

$files = scandir($path);
$cont2 = "N";


      foreach($files as $key => $value) {
      	
      	 //echo $value;
      	 //echo "<br>F";
      	
      	 if(strpos($value, 'STKSUMMARY') > 1) {
      		
                 // Read entire file into string
                 $xmlfile = file_get_contents($path . $value);
        
                 // Convert xml string into an object
                 $new = simplexml_load_string($xmlfile);
        
                 // Convert into json
                 $con = json_encode($new);
        
                 // Convert into associative array
                 $newArr = json_decode($con, true);
                 
                 foreach($newArr as $stRowKey => $stRow) {
           	
                 	    if($stRowKey == 'vendor_no') {
                 	    	     if(strtoupper($stRow) == 'AUP') {
                                    $updPrin  = '412';
                                    $sgxDepot = '188';
                                    // Clear Stock balances
                                    
                                    $sgxUpdate = new SgxImportDAO($dbConn);
         	                          $errorTO   = $sgxUpdate->clearSgxDepotStock($updPrin, $sgxDepot);
         	                          
                 	    	     } else {
                                    $deletefile=unlink($path . $value);    
                                    if($deletefile) {  
                                          echo "File deleted.. <br>";    
                                    } else {
                                    	    echo "Unable to Delete the File.. <br>";    
                                    }
                                    $cont2 = "Y";
                                    break;
                 	    	     }
                 	    }
                 	    if($stRowKey == 'creation_date') {
                 	    	     $createDate = substr(strtoupper($stRow),6,4) . "/" . substr(strtoupper($stRow),3,2) . "/" . substr(strtoupper($stRow),0,2);
      
                 	    }
                      if($stRowKey == 'creation_time') {
                 	    	     $createDateTime = $createDate . ":" . trim($stRow);
                 	    }
                      if($stRowKey == 'stk_recon_det') {
                      	    $numLines = count($stRow['recon']);
                      	    for ($x = 0; $x < $numLines; $x++) {
                      	    	      foreach($stRow['recon'][$x] as $recKey => $recRow) {
                      	    	      	   if($recKey == 'supplier_product_code') {
                      	    	      	   	  $prodCode = $recRow;
                      	    	      	   }
                      	    	      	   if($recKey == 'quantity') {
                                              $stockQty = $recRow;
                                              $sgxUpdate = new SgxImportDAO($dbConn);
         	                                    $errorTO   = $sgxUpdate->updateSgxDepotStock($updPrin, 
         	                                                                                 $sgxDepot, 
         	                                                                                 $prodCode, 
         	                                                                                 $stockQty,
         	                                                                                 $createDateTime);
                      	    	           }
                      	    	       }
                            }
                      }
                 }
                 if($cont2 == "Y") {
                     $cont2 = "N";
                     continue;	
                 }
                 $deletefile=unlink($path . $value);    
                 if($deletefile) {  
                      echo "File deleted.. <br>";    
                 } else {
                      echo "Unable to Delete the File.. <br>";    
                 }
	       } elseif(strpos($value, 'INVOICES')) {

                 // Read entire file into string
                 $xmlfile = file_get_contents($path . $value);
        
                 // Convert xml string into an object
                 $new = simplexml_load_string($xmlfile);
        
                 // Convert into json
                 $con = json_encode($new);
        
                 // Convert into associative array
                 $newArr = json_decode($con, true);
                 
                  //echo "Start<br>";
                 	 //echo "<pre>";
                 //print_r($newArr);
               //echo "<br>End<br>End<br>";
                 
                 foreach($newArr as $stRowKey => $stRow) {
                 	    // echo $stRowKey . "<br>";       	
                 	    if($stRowKey == 'vendor_no') {
                 	    	     if(strtoupper($stRow) == 'AUP') {
                                    $updPrin  = '412';
                                    $sgxDepot = '188';
                 	    	     } else {
                                    $deletefile=unlink($path . $value);    
                                    //if($deletefile) {  
                                      //    echo "File deleted.. <br>";    
                                    //} else {
                                    //	    echo "Unable to Delete the File.. <br>";    
                                    //}
                                    $cont2 = "Y";
                                    break;
                 	    	     }
                 	    }
                 	    if($stRowKey == 'creation_date') {
                 	    	     $createDate = substr(strtoupper($stRow),6,4) . "/" . substr(strtoupper($stRow),3,2) . "/" . substr(strtoupper($stRow),0,2);
      
                 	    }
                      if($stRowKey == 'creation_time') {
                 	    	     $createDateTime = $createDate . ":" . trim($stRow);
                 	    }
                 	     
                 	    if($stRowKey == 'order') {
                 	    	       foreach($stRow as $okey => $oRow) {
                 	    	       	       print_r($oRow);
                 	    	       	       foreach($oRow as $headKey => $headRow) {
                 	    	       	       	   if($headKey == 'order_hdr') {
                 	    	       	       	   	    foreach($headRow as $hedDetKey => $hedDet) {
                 	    	       	       	   	    	    if($hedDetKey == 'invoice_no' ) {
                 	    	       	       	   	    	          $sgxInvNum = $hedDet;	
                 	    	       	       	   	    	    }
                 	    	       	       	   	    	    if($hedDetKey == 'credit' ) {
                 	    	       	       	   	    	          $sgxcredFlag = $hedDet;	
                 	    	       	       	   	    	    }
                 	    	       	       	   	    	    if($hedDetKey == 'invoice_date' ) {
                 	    	       	       	   	    	          $sgxInvDate = substr($hedDet,6,4) ."/". substr($hedDet,3,2) ."/". substr($hedDet,0,2);	
                 	    	       	       	   	    	    }                 	    	       	       	   	    	    
                	    	       	       	   	    	    if($hedDetKey == 'comment1' ) {
                 	    	       	       	   	    	          $uppDocNum = $hedDet;	
                 	    	       	       	   	    	    }
                	    	       	       	   	    	    if($hedDetKey == 'order_total_qty' ) {
                 	    	       	       	   	    	          $headCases = $hedDet;	
                 	    	       	       	   	    	    }
                	    	       	       	   	    	    if($hedDetKey == 'order_total_excl_vat' ) {
                 	    	       	       	   	    	          $headExcl = $hedDet;	
                 	    	       	       	   	    	    }
                	    	       	       	   	    	    if($hedDetKey == 'order_vat' ) {
                 	    	       	       	   	    	          $headVat = $hedDet;	
                 	    	       	       	   	    	    }
                	    	       	       	   	    	    if($hedDetKey == 'order_total_incl_vat' ) {
                 	    	       	       	   	    	          $headIncl = $hedDet;	
                 	    	       	       	   	    	    }
                 	    	       	       	   	    }
                                                echo $sgxInvNum;
                                                echo "<br>";
                                                echo $sgxcredFlag;
                                                echo "<br>";
                                                echo $sgxInvDate;
                                                echo "<br>";
                                                echo $uppDocNum;
                                                echo "<br>";
                                                echo $headCases;
                                                echo "<br>";
                                                echo $headExcl;
                                                echo "<br>";
                                                echo $headVat;
                                                echo "<br>";
                                                echo $headIncl;
                                                echo "<br>";
                                                if($sgxcredFlag == 'yes') {
                                                	    // Update to DP Header
                                                	    // Create Credit
                                                } else {
                                                      // Update to CP	Header
                                                }
                                                
                                                
                                                
                 	    	       	           }
                 	    	       	       	   if($headKey == 'order_det') {
                 	    	       	       	   	        echo "<br>";
                 	    	       	       	   	        print_r($headRow['product'] );
                 	    	       	       	   	        echo "<br>";
                 	    	       	       	   	     if (isset($headRow['product'][0])) {
                 	    	       	       	   	     	    $aType = "ACC";
                 	    	       	       	   	     } else {
                 	    	       	       	   	     	    $aType = "NOR";
                 	    	       	       	   	     }
                                                 echo $aType;
                                                 echo "<br>";
                 	    	       	       	   	     if($aType == "ACC") {
                 	    	       	       	   	           foreach($headRow['product'] as $detKey => $detLine) {
                 	    	       	       	   	     	            //print_r($detLine);
                 	    	       	       	   	     	            $lineNum = $detLine['line_no'];
                 	    	       	       	   	     	     	      $uppProd = $detLine['supplier_product_code'];
                 		    	       	       	   	     	     	    $sgxQty  = $detLine['quantity'];
                 		    	       	       	   	    
         	                                                    $sgxUpdate = new SgxImportDAO($dbConn);
         	                                                    $errorTO   = $sgxUpdate->loadInvoicesToTemp($sgxInvNum,
                                                                                                          $sgxcredFlag,
                                                                                                          $sgxInvDate,
                                                                                                          $uppDocNum,
                                                                                                          $lineNum,
                                                                                                          $uppProd,
                                                                                                          $sgxQty);
                 		                                          echo $lineNum;
                 		                                          echo "<br>";
                 		                                          echo $uppProd;
                 		                                          echo "<br>";
                 		                                          echo $sgxQty;
                 		                                          echo "<br>";
                                                       }  	    
                 	    	       	       	   	     } elseif($aType == "NOR") {
                                                              $lineNum = $headRow['product']['line_no'];
                 	    	       	       	   	     	     	      $uppProd = $headRow['product']['supplier_product_code'];
                 		    	       	       	   	     	     	    $sgxQty  = $headRow['product']['quantity']; 
                                                             
         	                                                    $sgxUpdate = new SgxImportDAO($dbConn);
         	                                                    $errorTO   = $sgxUpdate->loadInvoicesToTemp($sgxInvNum,
                                                                                                          $sgxcredFlag,
                                                                                                          $sgxInvDate,
                                                                                                          $uppDocNum,
                                                                                                          $lineNum,
                                                                                                          $uppProd,
                                                                                                          $sgxQty);
                 		                                          echo $lineNum;
                                                              echo "<br>";
                                                              echo $uppProd;
                                                              echo "<br>";
                                                              echo $sgxQty;
                                                              echo "<br>";
                                                 }
                                                 echo "<br>";                                              	
                                                 echo "<br>";                                                   
                                                 echo "<br>";  
                                           }                 	    	       	       	   	     
                 	    	       	       	   	   

                 	    	       	       }
                 	    	       }
                 	    }  
               	    

                 // $deletefile=unlink($path . $value);    
                 //if($deletefile) {  
                 //     echo "File deleted.. <br>";    
                 //} else {
                 //  echo "Unable to Delete the File.. <br>";    
                 //}





	       	
	               }    
	     
	       } else {
		         // echo "Not a stock file  to Upload<br>";
	       }
	       
         }
/*    
    
// xml file path

  

?>





    if($successCount > 0 ) {
          //create actual file to the FTP Folder for sending.
          
          $sequenceDAO = new SequenceDAO($dbConn);
          $sequenceTO  = new SequenceTO;
          $errorTO     = new ErrorTO;
          $sequenceTO->sequenceKey=LITERAL_SEQ_SGX_FILE;
          $sequenceTO->depotUId   = SGXConstants::DepotList;
          $result=$sequenceDAO->getSequence($sequenceTO,$fileSeqVal);
          
          // Flag the orders as success
          if (!isset($errorTO)) {
               $errorTO     = new ErrorTO;	
          }	
          
          $oFileName = "KS.". $vendorNumber .".". date('Ymd') . "." .  $fileSeqVal . ".ORDERS";
          
	        $copy = copy($ROOT .  'ftp/sgx/ordertemp.xml', $ROOT .  'ftp/sgx/out/' . $oFileName .'.xml');    	
   
          foreach($successSEUIdArr as $sRow) {
          	
                $seId = substr($sRow, 0, strpos($sRow, '&'));
          	    $dmID = substr($sRow, strpos($sRow, '&') + 1, strpos($sRow, '%') - strpos($sRow, '&') -1 );
         	
         	      $sgxUpdate = new SgxImportDAO($dbConn);
         	      $errorTO   = $sgxUpdate->flagOrdersAsSuccess($dmID, $seId, $oFileName);
          
          }
          
          if ($result->type!=FLAG_ERRORTO_SUCCESS) {
             return $result;
          }
   
    }
    
    if($errorCount > 0 ) {
    	  
    	  foreach($errorSEUIdArr as $eRow) {
             $sgxUpdate = new SgxImportDAO($dbConn);
             $errorTO   = $sgxUpdate->flagOrdersAsError($eRow);
    	  }
    }
    
    // Mail the Errors to SGX
    
    // Get List of error
    $sgxUpdate     = new SgxImportDAO($dbConn);
    $errorList   = $sgxUpdate->getSgxStoreErrors($recipientUId);
    
    If(count($errorList) != 0) {
    
          // Get SGX Error Receipients
    
          $sgxUpdate     = new SgxImportDAO($dbConn);
          $contactList   = $sgxUpdate->getSgxContacts();
          $storeHead  = '';
          $bodyString = '';
    
          If(count($contactList) != 0) {
          	   
          	   foreach($contactList as $cRow) {
          	   	      foreach($errorList as  $eRow) {
          	   	      	   if($storeHead <> $cRow['email_addr']) {
          	   	      	         $postingDistributionTO = new PostingDistributionTO;
                                 $postingDistributionTO->DMLType = "INSERT";
                                 $postingDistributionTO->deliveryType = BT_EMAIL;
          	   	                 $messagingDAO = new messagingDAO($dbConn);
                                 $postingDistributionTO->subject = $messagingDAO->getTemplateSgxImportErrorSubject(trim($eRow['Principal'])); 
                                 $postingDistributionTO->destinationAddr =  trim($cRow["email_addr"]); 
                                
                                 $messagingDAO = new messagingDAO($dbConn);
                                 $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorHeader('');
          	   	      	
                                 $storeHead = trim($cRow['email_addr']);
                           }
          	   	      	   
          	   	           $bodyString = $bodyString . $messagingDAO->getTemplateBodyGeneralError($eRow['document_number'], 
    	                                                                                            $eRow['order_date'], 
    	                                                                                            $eRow['store'], 
    	                                                                                            trim(preg_replace("/\r|\n/", "", $eRow['general_reference_2'])) , 
    	                                                                                            $eRow['dm_uid'], 
    	                                                                                            $eRow['psm_uid'],
    	                                                                                            $recipientUId,
    	                                                                                            trim($eRow['Principal']));
          	   	      }
          	   	      
                      $messagingDAO = new messagingDAO($dbConn);
                      $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorend($eRow['Principal']);
          
                      $postDistributionDAO = new postDistributionDAO($dbConn);
                      $postingDistributionTO->body = $bodyString;
                      $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
          
                      //print_r($postingDistributionTO);
          	          //echo "<br>";
          	          //echo "<br>";
          	          //echo "Next Contact<br>";
          	   }
          } else {
              echo "<br>";
              echo "<br>BOMB OUT - No SGX Contacts Set UP<br>";
              echo "<br>";
          }
    
  }         

*/
    echo "<br>";
    
    
    
    echo "<br>End of SGX Orders<br>";
    echo "[***EOS***]<br>";
    
 ?>  

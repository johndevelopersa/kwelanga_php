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
require_once $ROOT .$PHPFOLDER  .'libs/storage/Storage.php';

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
    $errorTO = new ErrorTO;
    
}

$storage = new Storage(S3_ACCESS_ID, S3_SECRET_KEY, true, S3_ENDPOINT, S3_REGION);

echo "<pre style='font-size:14px;'>";
echo str_repeat("-", 75) . "\n";
echo "Import SGX Stock \n";
echo str_repeat("-", 75) . "\n";


$results = array();

$path = $ROOT .  'ftp/sgx/in/';

$files = scandir($path);
$cont2 = "N";


      foreach($files as $key => $value) {
      	
      	 if(strpos($value, 'STKSUMMARY') > 1) {
      		       $xmlfile = '';
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
                 
                 if(!Storage::putObject(S3_BUCKET_NAME, FILE_ARCHIVE_LOGS_PATH . 'sgx/' . date('Y/m/d')  . '/' . $value, $xmlfile)){
                        die("UPLOAD FAILED!\n");
                 }
                  
                 $deletefile=unlink($path . $value);    
                 if($deletefile) {  
                      echo "File deleted.. <br>";    
                 } else {
                      echo "Unable to Delete the File.. <br>";    
                 }
	       } elseif(strpos($value, 'INVOICES')) {
                 $xmlfile = $new = $con = '';
                 $newArr = array();
                 // Read entire file into string
                 $xmlfile = file_get_contents($path . $value);
        
                 // Convert xml string into an object
                 $new = simplexml_load_string($xmlfile);
        
                 // Convert into json
                 $con = json_encode($new);
        
                 // Convert into associative array
                 $newArr = json_decode($con, true);
                 
                 echo "File Name<br>";
                 echo "<pre>";
                 echo $value;
                 $sgxFileName = $value;
                 echo "<br>End File Name<br>";
                 
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
                 	    	
                 	    	       // Determine no of orders in fileUploadDetails
                 	    	       // print_r($stRow);
                 	    	       if (isset($stRow[0])) {
                 	    	       	    Echo "onePlus" . "<br>";
                 	    	       	    $onePlus = 'Y';
                 	    	       } else {
                 	    	       	   Echo "onlyOne" . "<br>";
                 	    	       	   $onePlus = 'N';
                 	    	       }
                 	    	       
                 	    	       foreach($stRow as $okey => $oRow) {
                 	    	                       	    	       	
                 	    	       	     if($onePlus == 'Y') {
                 	    	       	     	      foreach($oRow['order_hdr'] as $hedkey=>$hedRow)	{
                 	    	       	     	      	  if($hedkey == 'transaction_no') {
                 	    	       	     	      	      $transNo = $hedRow;	
                 	    	       	     	      	  }
                 	    	       	     	      	  if($hedkey == 'invoice_no') {
                 	    	       	     	      	      $sgxInvoiceNo = $hedRow;	
                 	    	       	     	      	  }
                 	    	       	     	      	  if($hedkey == 'invoice_date') {
                 	    	       	     	      	  	  $sgxInvDate = substr($hedRow,6,4) . "/" . substr($hedRow,3,2) . "/" . substr($hedRow,0,2) ;	
                 	    	       	     	      	  }
                 	    	       	     	      	  if($hedkey == 'comment1') {
                 	    	       	     	      	      $uppInvoiceNo = $hedRow;	
                 	    	       	     	      	  }
                 	    	       	     	      	  if($hedkey == 'credit') {
                 	    	       	     	      	      $credNote = $hedRow;	
                 	    	       	     	      	  }
                 	    	       	     	       	  if($hedkey == 'order_total_qty') {
                 	    	       	     	      	      $sgxCases = $hedRow;	
                 	    	       	     	      	  }
                 	    	       	     	      	  if($hedkey == 'order_total_excl_vat') {
                 	    	       	     	      	      $sgxExcl = $hedRow;	
                 	    	       	     	      	  }
                 	    	       	     	      	  if($hedkey == 'order_vat') {
                 	    	       	     	      	      $sgxVat = $hedRow;	
                 	    	       	     	      	  }
                 	    	       	     	      	  if($hedkey == 'order_total_incl_vat') {
                 	    	       	     	      	      $sgxIncl = $hedRow;
                 	    	       	     	      	      
                 	    	       	     	      	  }
                 	    	       	     	      }
                 	    	       	     	      foreach($oRow['order_det'] as $detkey=>$detRow)	{
                 	    	       	     	      	   //echo $detkey . "<br>";
                 	    	       	     	      	   if($detkey == 'product') {
                 	    	       	     	      	   	    if (isset($detRow[0])) {
                 	    	       	       	   	     	         $aType = "ACC";
                 	    	       	       	   	          } else {
                 	    	       	       	   	     	         $aType = "NOR";
                 	    	       	       	   	          }
                 	    	       	       	   	          //echo $aType;
                                                      //echo "<br>";
                                                      
                                                      if($aType == "ACC") {
                                                          foreach($detRow as $lineKey=>$lineRow) {
                                                          	   $lineNum     = $lineRow['line_no'];
                                                          	   $uppProd     = $lineRow['supplier_product_code'];
                                                          	   $sgxQty      = $lineRow['quantity'];
                                                               $sgxExcl     = $lineRow['extended_value_excl_vat'];
                                                               $sgxVat      = $lineRow['extended_vat'];
                                                               $sgxlineTot  = $lineRow['extended_value_incl_vat'];
                         
                                                               $sgxUpdate = new SgxImportDAO($dbConn);
         	                                                     $errorTO   = $sgxUpdate->loadInvoicesToTemp($sgxInvoiceNo,
                                                                                                           $credNote,
                                                                                                           $sgxInvDate,
                                                                                                           $uppInvoiceNo,
                                                                                                           $lineNum,
                                                                                                           $uppProd,
                                                                                                           $sgxQty,
                                                                                                           $transNo,
                                                                                                           $sgxFileName);

                                                               $sgxUpdate = new SgxImportDAO($dbConn);
         	                                                     $errorTO   = $sgxUpdate->loadInvoicesToUpdate($sgxInvoiceNo,
                                                                                                             $credNote,
                                                                                                             $sgxInvDate,
                                                                                                             $uppInvoiceNo,
                                                                                                             $lineNum,
                                                                                                             $uppProd,
                                                                                                             $sgxQty,
                                                                                                             $transNo,
                                                                                                             $sgxFileName);

                                                          } 
                                                      } else { 
                                                      	
                                                          $lineNum     = $detRow['line_no'];
                                                          $uppProd     = $detRow['supplier_product_code'];
                                                          $sgxQty      = $detRow['quantity'];
                                                          $sgxExcl     = $detRow['extended_value_excl_vat'];
                                                          $sgxVat      = $detRow['extended_vat'];
                                                          $sgxlineTot  = $detRow['extended_value_incl_vat'];

                                                          $sgxUpdate = new SgxImportDAO($dbConn);
         	                                                $errorTO   = $sgxUpdate->loadInvoicesToTemp($sgxInvoiceNo,
                                                                                                      $credNote,
                                                                                                      $sgxInvDate,
                                                                                                      $uppInvoiceNo,
                                                                                                      $lineNum,
                                                                                                      $uppProd,
                                                                                                      $sgxQty,
                                                                                                      $transNo,
                                                                                                      $sgxFileName);   
                                                                                                      
                                                                                                      
                                                          $sgxUpdate = new SgxImportDAO($dbConn);
         	                                                $errorTO   = $sgxUpdate->loadInvoicesToUpdate($sgxInvoiceNo,
                                                                                                        $credNote,
                                                                                                        $sgxInvDate,
                                                                                                        $uppInvoiceNo,
                                                                                                        $lineNum,
                                                                                                        $uppProd,
                                                                                                        $sgxQty,
                                                                                                        $transNo,
                                                                                                        $sgxFileName);
                                                                                                         
                                                      }
                                                 }                     	    	       	     	      	
                 	    	       	     	      }
                 	    	       	     } else {
                 	    	       	     	    if($okey == 'order_hdr') {
                 	    	       	     	    	//echo "NOW IN HEAD";
                 	    	       	     	    	//echo "<br><br>";
                 	    	       	     	    	//print_r( $oRow);
                 	    	       	     	    	foreach($oRow as $sHedKey=>$sHedLine) {
                 	    	       	     	    		  //echo $sHedKey . '  ' . $sHedLine . '<br>';
                 	    	       	     	    		  if($sHedKey == 'transaction_no') {
                 	    	       	     	    		  	   $transNo      = $sHedLine;
                 	    	       	     	    		  }
                 	    	       	     	    		  if($sHedKey == 'invoice_no') {
                 	    	       	     	    		  	   $sgxInvoiceNo = $sHedLine;
                 	    	       	     	    		  }                 	    	       	     	    		  
                 	    	       	     	    		  if($sHedKey == 'invoice_date') {
                 	    	       	     	    		  
                 	    	       	     	    		  	   $sgxInvDate = substr($sHedLine,6,4) . "/" . substr($sHedLine,3,2) . "/" . substr($sHedLine,0,2) ;
                 	    	       	     	    		  }                 	    	       	     	    		  
                 	    	       	     	    		  if($sHedKey == 'comment1') {
                 	    	       	     	    		  	   $uppInvoiceNo = $sHedLine;
                 	    	       	     	    		  }
                 	    	       	     	    		  if($sHedKey == 'credit') {
                 	    	       	     	      	      $credNote = $sHedLine;	
                 	    	       	     	      	  }                   	    	       	     	    		  
                 	    	       	     	    		  if($sHedKey == 'order_total_qty') {
                 	    	       	     	    		  	   $sgxCases = $sHedLine;
                 	    	       	     	    		  }                   	    	       	     	    		  
                 	    	       	     	    		  if($sHedKey == 'order_total_excl_vat') {
                 	    	       	     	    		  	   $sgxExcl = $sHedLine;
                 	    	       	     	    		  }
                 	    	       	     	    		  if($sHedKey == 'order_vat') {
                 	    	       	     	    		  	   $sgxVat = $sHedLine;
                 	    	       	     	    		  }                   	    	       	     	    		  }  
                                                if($sHedKey == 'order_total_incl_vat') {
                 	    	       	     	    		  	   $sgxIncl = $sHedLine;
                 	    	       	     	    		  }
                 	    	       	     	    }
                 	    	       	     	    if($okey == 'order_det') {
                                	       	     	foreach($stRow['order_det'] as $detkey=>$detRow)	{
                 	    	       	     		               echo $detkey . "<br>";
                 	    	       	     	      	        if($detkey == 'product') {
                 	    	       	     	      	   	         if (isset($detRow[0])) {
                 	    	       	       	   	     	             $aType = "ACC";
                 	    	       	       	   	               } else {
                 	    	       	       	   	     	             $aType = "NOR";
                 	    	       	       	   	               }
                 	    	       	       	   	               echo $aType;
                 	    	       	     		               }
                 	    	       	     		               if($aType == "NOR") {
                 	    	       	                            $lineNum     = $detRow['line_no'];
                                                            $uppProd     = $detRow['supplier_product_code'];
                                                            $sgxQty      = $detRow['quantity'];
                                                            $sgxExcl     = $detRow['extended_value_excl_vat'];
                                                            $sgxVat      = $detRow['extended_vat'];
                                                            $sgxlineTot  = $detRow['extended_value_incl_vat'];         
                                                            /*
                                                            $sgxUpdate = new SgxImportDAO($dbConn);
         	                                                  $errorTO   = $sgxUpdate->loadInvoicesToTemp($sgxInvoiceNo,
                                                                                                        $credNote,
                                                                                                        $sgxInvDate,
                                                                                                        $uppInvoiceNo,
                                                                                                        $lineNum,
                                                                                                        $uppProd,
                                                                                                        $sgxQty,
                                                                                                        $transNo,
                                                                                                        $sgxFileName);
                 	    	                                    */
                                                            $sgxUpdate = new SgxImportDAO($dbConn);
         	                                                   $errorTO   = $sgxUpdate->loadInvoicesToUpdate($sgxInvoiceNo,
                                                                                                           $credNote,
                                                                                                           $sgxInvDate,
                                                                                                           $uppInvoiceNo,
                                                                                                           $lineNum,
                                                                                                           $uppProd,
                                                                                                           $sgxQty,
                                                                                                           $transNo,
                                                                                                           $sgxFileName);                 	    	       
                 	    	       
                 	    	       	     		                } else {
                 	    	       	     		         	            foreach($detRow as $lineKey=>$lineRow) {
                                                          	        $lineNum     = $lineRow['line_no'];
                                                          	        $uppProd     = $lineRow['supplier_product_code'];
                                                          	        $sgxQty      = $lineRow['quantity'];
                                                                    $sgxExcl     = $lineRow['extended_value_excl_vat'];
                                                                    $sgxVat      = $lineRow['extended_vat'];
                                                                    $sgxlineTot  = $lineRow['extended_value_incl_vat'];
                                                                    /*
                                                                    $sgxUpdate = new SgxImportDAO($dbConn);
         	                                                          $errorTO   = $sgxUpdate->loadInvoicesToTemp($sgxInvoiceNo,
                                                                                                                $credNote,
                                                                                                                $sgxInvDate,
                                                                                                                $uppInvoiceNo,
                                                                                                                $lineNum,
                                                                                                                $uppProd,
                                                                                                                $sgxQty,
                                                                                                                $transNo,
                                                                                                                $sgxFileName);
                                                                    */
                                                                    $sgxUpdate = new SgxImportDAO($dbConn);
         	                                                          $errorTO   = $sgxUpdate->loadInvoicesToUpdate($sgxInvoiceNo,
                                                                                                                  $credNote,
                                                                                                                  $sgxInvDate,
                                                                                                                  $uppInvoiceNo,
                                                                                                                  $lineNum,
                                                                                                                  $uppProd,
                                                                                                                  $sgxQty,
                                                                                                                  $transNo,
                                                                                                                  $sgxFileName);
                 	    	       	     		         	            }
                 	    	       	     		                }   	       	     	    
                 	    	       	                } 
                                          }
                 	    	       	     }
                 	    	        }
                 	    }
                 }  
               	 if(!Storage::putObject(S3_BUCKET_NAME, FILE_ARCHIVE_LOGS_PATH . 'sgx/' . date('Y/m/d')  . '/' . $value, $xmlfile)){
                        die("UPLOAD FAILED!\n");
                 }    

                 $deletefile=unlink($path . $value);    
                 if($deletefile) {  
                       echo "File deleted.. <br>";    
                 } else {
                       echo "Unable to Delete the File.. <br>";    
                 }
         } else {
		         //echo "Not a stock file  to Upload<br>";
	       }
      }
      
      // Process invoices here
      // Get un Extracted invoices
                 
      $sgxUpdate = new SgxImportDAO($dbConn);
      $unDocs    = $sgxUpdate->getUnExtractedDocuments('no');
      
      $errorFound = 'N';
 
      foreach($unDocs as $iRow) {
      	
                $rowUid    = $iRow['uid'];
                $sgxTxNo   = $iRow['sgx_transaction_number'];
                $docType   = $iRow['credit_note'];
                $txDate    = $iRow['transaction_date'];
                $prinDoc   = $iRow['principal_document'];
                $lineNo    = $iRow['line_number'];
                $prodCode  = $iRow['product_code'];
                $invQty    = $iRow['quantity'];
                $fileTxNo  = $iRow['tranasction_number'];
                $fileName  = $iRow['file_name'];
                $updSatus  = $iRow['update_status'];
                
                $sgxUpdate = new SgxImportDAO($dbConn);
                $errorTO   = $sgxUpdate->getUpdateInvoiceDocument($Prin,
                                                                  $sgxTxNo,
                                                                  $lineNo$sgxLine,
                                                                  $prinDoc, 
                                                                  $prodCode,
                                                                  $txDate,
                                                                  $invQty );
                                                                  
                                                                  
                
                
                echo $prin;
                echo $rowUid  . "<br>";
                echo $sgxTxNo  . "<br>";
                echo $docType   . "<br>";
                echo $txDate    . "<br>";
                echo $prinDoc   . "<br>";
                echo $lineNo    . "<br>";
                echo $prodCode  . "<br>";
                echo $invQty    . "<br>";
                echo $fileTxNo  . "<br>";
                echo $fileName  . "<br>";
                echo $updSatus  . "<br>";
                echo "End";
                echo "<br>";
              
              
              


      	            // Detail Update
      	    
      	    

      	
      	
      }
      // Last record Header Update
      
      // Flag as done
      
      
      
      

    echo "<br>";
    echo "<br>End of SGX Orders<br>";
    echo "[***EOS***]<br>";
    
 ?>  

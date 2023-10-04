<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/scanner_data/load_scanner_log.php

 include_once('ROOT.php'); 
 include_once($ROOT.'PHPINI.php');
 include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
 include_once ($ROOT.$PHPFOLDER."DAO/BcScannerDAO.php");
 include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

set_time_limit(60 * 10);
error_reporting(-1);
ini_set('display_errors', 1);

//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO; 
      
$BcScannerDAO = new BcScannerDAO($dbConn);
$seInsert = $BcScannerDAO->getScannerLogInfo('392,417, 401');


$ts = 0;

foreach($seInsert as $row) {
	
    // Check for existing record in log
             
       $BcScannerDAO = new BcScannerDAO($dbConn);
       $recExist = $BcScannerDAO->checkForExistingLogEntry($row['tripUid']);
       
       if(count($recExist) == 0) {        
       	
       	     if($row['app_json_response'] <>  '') {
                   $arr = json_decode($row['app_json_response'], TRUE);      
                   $userName        = $arr['logonTO']['userName'];
                   $password        = $arr['logonTO']['password'];
                   $createdDateTime = $arr['data']['createdDateTime'];
                   $transporterUId  = $arr['data']['transporterUId'];
                   $vehicleReg      = $arr['data']['vehicleReg'];
                   
                   $uploadedDateTime = $arr['data']['uploadedDateTime'];
                   $otp              = $arr['data']['otp'];
                   $latitude         = $arr['data']['coords']['latitude'];
                   $longitude        = $arr['data']['coords']['longitude'];
                   $timestampLocale  = $arr['data']['coords']['timestampLocale'];
                   
                   $timestamp = $arr['data']['coords']['timestamp'];
                   
                   
                   
                   // Insert into Log headerArr
                  
                   $BcScannerDAO = new BcScannerDAO($dbConn);
                   $errorTO  = $BcScannerDAO->insertintoLogHeader($row['tripUid'],
                                                                  $userName,
                                                                  $password,
                                                                  $createdDateTime,
                                                                  $transporterUId, 
                                                                  $vehicleReg,
                                                                  $otp,            
                                                                  $latitude,       
                                                                  $longitude,      
                                                                  $timestampLocale,
                                                                  $uploadedDateTime,
                                                                  $timestamp) ;
                                                                  
                   if($errorTO->Type = FLAG_ERRORTO_SUCCESS)  {
         	              $lastThUid = $dbConn->dbGetLastInsertId();  
                   	
                   } else {
                        echo "<br>";
                   	    echo "Bomb Out";
                   	    echo "<br>";
                   	                   	
                   }                                            
                   foreach($arr['data']['invoices'] as $irow) {
                         $dmUid = $irow['documentMasterUId'];
                         echo "<br>";
                         echo $dmUid;            
       	     
                         foreach($irow['products'] as $prow) {
                               $productUId          =  $prow['productUId']  ;
                               $qty                 =  $prow['qty']  ;
                               $orderedQty          =  $prow['orderedQty']  ;       	     	     
                               $qtyChangeReasonCode =  $prow['qtyChangeReason']['code']  ;
                               $qtyChangeReasonUid  =  $prow['qtyChangeReason']['uid']  ;
                               
                               // Check for existancce of detail
                               
                               $BcScannerDAO = new BcScannerDAO($dbConn);
                               $recExist = $BcScannerDAO->checkForExistingLogDetailEntry($row['tripUid'],
                                                                                         $dmUid,
                                                                                         $productUId,
                                                                                         $orderedQty);
                               if(count($recExist) == 0) {
                                     // Insert into Log Detail $lastThUid
                                     $BcScannerDAO = new BcScannerDAO($dbConn);
                                     $recExist = $BcScannerDAO->insertIntoLogDetail($lastThUid,
                                                                                    $dmUid,
                                                                                    $row['tripUid'],
                                                                                    $productUId,
                                                                                    $orderedQty,
                                                                                    $qty, 
                                                                                    $qtyChangeReasonCode,
                                                                                    $qtyChangeReasonUid) ;                           	
                                     if($errorTO->Type = FLAG_ERRORTO_SUCCESS)  {
                                     } else {
                                          echo "<br>";
                   	                      echo "Bomb Out";
                   	                      echo "<br>";
                                     }                                                                     	
                               }       	     	     
       	                 }
                   }
                   // Update Location Time and TS Header
                   
                   $BcScannerDAO = new BcScannerDAO($dbConn);
                   $SerrorTO = $BcScannerDAO->updateTripsheetFlag($row['tripUid']);
                   if($errorTO->Type = FLAG_ERRORTO_SUCCESS)  {
                           echo "<br>";
                           echo "Update Successful";
                           echo "<br>" ;                  
                   } else {
                           echo "<br>";
                           echo "Bomb Out";
                           echo "<br>";
                   }
             }
       }
}

echo "<br>[***EOS***]<br>";


?>
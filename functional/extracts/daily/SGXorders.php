<?php

/* * ********************************************************************************************
 * *
 * *  Extract Orders fro SGX  - XML File
 * *
 * *****************ss*************************************************************************** */

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/BIDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");
include_once($ROOT . $PHPFOLDER . 'DAO/MiscellaneousDAO.php');                                
include_once($ROOT . $PHPFOLDER . 'DAO/PostExtractDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExtractDAO.php');
include_once($ROOT . $PHPFOLDER . "DAO/messagingDAO.php");
include_once($ROOT . $PHPFOLDER . 'TO/SequenceTO.php');
include_once($ROOT . $PHPFOLDER . "DAO/SequenceDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/SgxImportDAO.php");  
include_once($ROOT . $PHPFOLDER . "DAO/postDistributionDAO.php");
include_once($ROOT . $PHPFOLDER . 'TO/PostingDistributionTO.php');


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
echo "Extract SGX Orders \n";
echo str_repeat("-", 75) . "\n";

/*-------------------------------------------------*/
/*  FETCH Notification Recipients ??
/*-------------------------------------------------*/
//use the receipients listed in the notification table instead of hard coding them!!!
//expecting only one row loaded per principal extract
$reArr = (new BIDAO($dbConn))->getNotificationRecipients(SGXConstants::PrincipalID, NT_DAILY_EXTRACT_ALTCUSTOM3);
if (count($reArr) == 0) {
    BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in " . __FILE__, "Y");
    exit;
}
$recipientUId = $reArr[0]['uid'];

/*-------------------------------------------------*/
/*  QUEUE DOCUMENTS IN SMART EVENTS
/*-------------------------------------------------*/
// Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
$documentTypeArr = [
    DT_ORDINV,
    DT_ORDINV_ZERO_PRICE,
];


$rTO = (new PostExtractDAO($dbConn))->queueAllInvoiced(SGXConstants::PrincipalID,
    $recipientUId,
    $inclCancelled = false,
    $documentTypeArr,
    $documentStatusArr = array(DST_UNACCEPTED),
    $fromInvDate='2023-07-12',
    $toInvDate= false,
    $chainUIdIn= false,
    $dataSource=false,
    $capturedBy=false,
    $depotUId = SGXConstants::DepotList,
    $altChainUIdIn=false);  
    
    //use the loaded receipientUID and not the notification type... *** same as document confirmations***
if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
    BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in " . __FILE__ . " ERROR:" . $rTO->description, "Y");
} else {
    $dbConn->dbinsQuery("commit;");
}

    /*-------------------------------------------------*/
    /*  FETCH ORDERS THAT NEED TO BE SENT.
    /*-------------------------------------------------*/
    echo "Fetching getDailySGXOrdersOrders\n";
    $seDocs = (new ExtractDAO($dbConn))->getDailyExtractInvoicedOrdersWithParms(SGXConstants::PrincipalID, $recipientUId,'','');

    if (!is_array($seDocs) || is_array($seDocs) && count($seDocs) == 0) {
       echo "Successful --> No outstanding orders found!";
       $errorTO = new ErrorTO();
       $errorTO->type = FLAG_ERRORTO_SUCCESS;  //preset.
       $errorTO->object = array();
    
        echo "<br>End of SGX Orders<br>";
        echo "[***EOS***]<br>";
    }

    echo "Found: " . count($seDocs) . " order lines\n";

    $vendorNumber    = SGXConstants::SgxVendor ;
    $senderNo        = 'kwelanga' ;
    $RecieptNo       = '6009801812007';
    $creationDate    = date('d/m/Y') ;
    $creation_time   = date('H:i:s') ;
    
    include_once($ROOT . $PHPFOLDER . "functional/extracts/xml_templates/sgx/SGXOrderSendingHeaderData.php");    
    
    file_put_contents($ROOT .  'ftp/sgx/ordertemp.xml', $XmlHeader); 

    $errorSEUIdArr   = array();
    $successSEUIdArr = array();
    $successCount    = 0;
    $errorCount      = 0;
    $lineNo = 0;
    $transctionNo = 0;
    $detStr = '';
    
    $transStore   = '';

    foreach($seDocs as $ord) {
    	
         $spfield  = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesByFid($ord["principal_store_uid"], SGXConstants::StoreLookupCode);
         $uppfield = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesByFid($ord["principal_store_uid"], SGXConstants::UppAccount);
         
         if(count($spfield) == 0 || $spfield[0]['value'] == '' || $spfield[0]['value'] == NULL) {
         	     if (!in_array($ord['se_uid'],$errorSEUIdArr)) {
         	          $accountCode = 'No Account Code';
         	          $errorSEUIdArr[] = $ord['se_uid'];
         	          $errorCount++;
         	     }     
         } else {
         	     $accountCode = $spfield[0]['value'];
         	     $custCode    = $uppfield[0]['value'];
               
               if($transStore <> $ord["document_number"] ) {
         	   
                   if($transctionNo > 0) {
         	   	
                       file_put_contents($ROOT .  'ftp/sgx/ordertemp.xml', $detStr, FILE_APPEND);	
                       include_once($ROOT . $PHPFOLDER . "functional/extracts/xml_templates/sgx/SGXOrderSendingDetailEndData.php"); 
                        
                       file_put_contents($ROOT .  'ftp/sgx/ordertemp.xml', $XmlDetEnd, FILE_APPEND);
                       $detStr       = '';
                       $lineNo = 0; 
                                              
                       echo "<br>";
                       echo "END OF " . $transctionNo;
                       echo "<br>";
         	   	
                   }
                   
                   $transctionNo++;
                   $transStore = $ord["document_number"]; 
                                
                   //setup header
                   include_once($ROOT . $PHPFOLDER . "functional/extracts/xml_templates/sgx/SGXOrderSendingData.php");
                   $headDet = str_replace(array('&&transctionNo&&',
                                                '&&branchNo&&',
                                                '&&poNumber&&',
                                                '&&orderDate&&',
                                                '&&$requiredDate&&',
                                                '&&$docNo&&',
                                                '&&$spAccount&&'), 
                                                 array($transctionNo,
                                                       $accountCode,
                                                       trim(str_replace(array('"',"'"),array('',''),$ord["customer_order_number"])),
                                                       date("d/m/Y", strtotime($ord["order_date"])),
                                                       date("d/m/Y", strtotime($ord["order_date"])),
                                                       ltrim($ord["document_number"],'0'),
                                                       $custCode),
                                                       $XmlOrder);  
                                                                                 
                   file_put_contents($ROOT .  'ftp/sgx/ordertemp.xml', $headDet, FILE_APPEND);
             
                   $successCount++;
                   $successSEUIdArr[] = $ord['se_uid'] . '&' . ltrim($ord["dm_uid"],'0') . '%' . ''; // list of smart event success
             
               }
               $lineNo++;

               $lineNoFile = $lineNo;  
         
               include_once($ROOT . $PHPFOLDER . "functional/extracts/xml_templates/sgx/SGXOrderSendingDetailData.php");
         
               $detStr       = $detStr . str_replace(array('&&lineNoFile&&',
                                                           '&&$productCode&&',
                                                           '&&productDescription&&',
                                                           '&&quantity&&',
                                                           '&&extendedExclValue&&'), 
                                                            array($lineNoFile,
                                                            trim(str_replace(['"'], [''], $ord[SGXConstants::SgxProdCode])),
                                                            trim(str_replace(['"', '\\', "\t", "\n", "\r"], ['', '', '', '', ''], $ord['product_description'])),
                                                            abs($ord['ordered_qty']),
                                                            number_format($ord['extended_price'], 2, '.', '')),
                                                      $XmlDetail);
         
                  file_put_contents($ROOT .  'ftp/sgx/ordertemp.xml', $detStr, FILE_APPEND);
                  $detStr       = '';	
         }
         
    }
    
    include_once($ROOT . $PHPFOLDER . "functional/extracts/xml_templates/sgx/SGXOrderSendingDetailEndData.php"); 
                        
    file_put_contents($ROOT .  'ftp/sgx/ordertemp.xml', $XmlDetEnd, FILE_APPEND);
    $detStr       = '';
    
    echo "<br>";
    echo "END OF " . $transctionNo;
    echo "<br>";
            	   	
    include_once($ROOT . $PHPFOLDER . "functional/extracts/xml_templates/sgx/SGXOrderSendingTrailerData.php"); 
                        
    file_put_contents($ROOT .  'ftp/sgx/ordertemp.xml', $XmlTrailer, FILE_APPEND);
    
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
    echo "<br>";
    
    
    
    echo "<br>End of SGX Orders<br>";
    echo "[***EOS***]<br>";
    
 ?>  

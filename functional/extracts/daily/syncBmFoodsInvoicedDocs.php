<?php

/* * ********************************************************************************************
 * *
 * *  Invoices to BMF system
 * *
 * *****************ss*************************************************************************** */

require_once __DIR__ . "/../../../libs/api/bmf/BMFoodsRestAPI.php";

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/BIDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/messagingDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/postDistributionDAO.php");
include_once($ROOT . $PHPFOLDER . 'TO/PostingDistributionTO.php');
include_once($ROOT .$PHPFOLDER  . 'DAO/ExtractDAO.php');
include_once($ROOT .$PHPFOLDER  . 'DAO/PostExtractDAO.php');
include_once ($ROOT.$PHPFOLDER."functional/maintenance/ResetDailyExtractTime.php");   	    


$principalUid = 290; //uid of principal extract.


set_time_limit(15*60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    $errorTO = new ErrorTO;
    
}

echo "<pre style='font-size:14px;'>";
echo str_repeat("-", 75) . "\n";
echo "BM FOOD INVOICES SYNC PROCESSOR\n";
echo str_repeat("-", 75) . "\n";

/*-------------------------------------------------*/
/*  FETCH Notification Recipients ??
/*-------------------------------------------------*/
//use the receipients listed in the notification table instead of hard coding them!!!
//expecting only one row loaded per principal extract
$reArr = (new BIDAO($dbConn))->getNotificationRecipients($principalUid, NT_DAILY_EXTRACT_ALTCUSTOM2);
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

$postExtractDAO = new PostExtractDAO($dbConn);
$extractDAO = new ExtractDAO($dbConn);

$rTO = $postExtractDAO->queueAllInvoiced($principalUid, 
                                         $recipientUId, 
                                         $inclCancelled = true,  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
                                         $p_dtArr = false,
                                         $p_wDSArr = false,
                                         $fromInvDate='2022-05-26',
                                         $toInvDate=false,
                                         $chainUIdIn=false,
                                         $dataSource=false,
                                         $capturedBy='BMF-API',
                                         $depotUId = false );
if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in "."syncBmFoodsInvoicedDocs"." " . $rTO->description, "Y");
} else {
        $dbConn->dbinsQuery("commit;");
}
/*-------------------------------------------------*/
/*  FETCH INVOICES THAT NEED TO BE SENT.
/*-------------------------------------------------*/

$seDocs = $extractDAO->getDailyExtractInvoicedOrders($principalUid
, $recipientUId);

/*  SUCCESS POINT - 1  */
//nothing to do...
if(count($seDocs)==0){
      echo "Successfully Completed Extract : "."syncBmFoodsInvoicedDocs"." - No entries!<br>";
      $errorTO->type = FLAG_ERRORTO_SUCCESS;
      $errorTO->description = "Successful";
      return $errorTO;
}

echo "Found: " . count($seDocs) . " order lines\n";

/*-----------------------------------------------------------------------*/
/*    OUTPUT
/*-----------------------------------------------------------------------*/
$errorSEUIdArr   = array(); //update errors at the end.
$successSEUIdArr = array();

$data = '';
$storeDoc = '';
foreach($seDocs as $k=>$docArr) {
    	
       if($storeDoc <> $docArr['client_document_number']) {
           $successSEUIdArr[] = $docArr['se_uid'];
          
           // print_r($docArr);
           
           $docNo = $docArr['client_document_number'];

           $data .= "HDR01"                   . "," .
                     $docNo                   . "," .
                     $docArr["invoice_date"] . "\r\n";
                     
           $storeDoc = $docArr['client_document_number'] ;         
       }
       $dashpos = stripos(trim($docArr['product_code']), "-");
       if($docArr['document_qty'] > 0) {
           $data   .= 'DTL01' . "," .
           $docNo  . "," . 
           trim($docArr['client_line_no']) . "," .                
           substr(trim($docArr['product_code']),0,$dashpos)   . "," .
           trim(substr(trim($docArr['product_code']),$dashpos+1,5))   . "," .
           $docArr['document_qty']   . "\r\n";
        }

}  //eo-documents
       
       
       echo $data;

$BmfHostName = 'http://105.255.163.102/BMFoods/Services/Kwelanga/api/Invoice/PostInvoice?ENV=LIVE';
$BmfUserName = 'connector';
$BmfPassword = 'D08C50A7';

echo "Starting to Post Orders";


$BmFoodsApi = new BMFoodsRestAPI($BmfHostName, $BmfUserName, $BmfPassword);
    /*-------------------------------------------------------------
     *   POST TO OMNI
     *------------------------------------------------------------
     */

$response = $BmFoodsApi->Request("POST", "", $data);

echo "SUCCESS!\n<br>";
print_r($response);

/*-------------------------------------------------------------
 *   UPDATE SMART EVENT in BULK
 *------------------------------------------------------------
 */
 
if (count($successSEUIdArr) > 0) {
    $bIResult = (new PostBIDAO($dbConn))->setSmartEventStatusBulk((implode(",", $successSEUIdArr)), "SUCCESS", "");
    if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $error = "Failed in " .  "syncBmFoodsInvoicedDocs" . " extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in " .  "syncBmFoodsInvoicedDocs" . " extract", $error, "Y", false);
        exit;
    }
}

/*-------------------------------------------------------------
 *   ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
 *------------------------------------------------------------
 */
 
if (sizeof($errorSEUIdArr) > 0) {
    //set these per the error messages...

    $sp = '';
    $eList = '';
    foreach ($errorSEUIdArr as $errorMessage => $UidArr) {
    	
        if (count($UidArr) > 0) {
            $bIResult = (new PostBIDAO($dbConn))->setSmartEventStatusBulk(implode(",", $UidArr), "ERROR", $errorMessage, FLAG_ERRORTO_ERROR);
            if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                $error = "Failed in " . "syncBmFoodsInvoicedDocs" . " on setting errror setSmartEventStatusBulk with {$bIResult->description}";
                BroadcastingUtils::sendAlertEmail("Error in " . "syncBmFoodsInvoicedDocs" . " extract", $error, "Y", false);
                exit;
            }
            foreach($UidArr as $erow) {
        	      $eList = $eList . $sp . $erow;
        	      $sp = ",";    	
            }
        
        }
     
    }
}

OmniErrorReporting($principalUid, NT_DAILY_EXTRACT_ALTCUSTOM2, CTD_EDI);

$dbConn->dbinsQuery("commit;");

$errorTO = new ErrorTO();
$errorTO->type = FLAG_ERRORTO_SUCCESS;  //preset.
$errorTO->object = array();

return($errorTO);


// ******************************************************************************************************************************
 function OmniErrorReporting($principalUId, $seType, $nType) {
 	
     global $ROOT; global $PHPFOLDER;
     
     include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
     include_once($ROOT . $PHPFOLDER . "DAO/messagingDAO.php");
     include_once($ROOT . $PHPFOLDER . "DAO/postDistributionDAO.php");
     include_once($ROOT . $PHPFOLDER . "TO/PostingDistributionTO.php");
     include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");
     
     if (!isset($dbConn)) {
        $dbConn = new dbConnect();
        $dbConn->dbConnection();
     }	
 	
     $messagingDAO = new messagingDAO($dbConn);
     $errLST = $messagingDAO->getOmniErrorNotificationRecipients($principalUId, $seType, $nType) ;
     
     if (count($errLST) > 0) {
    	
          $storeString = '';
          $bodyString = '';
    
          $c= 0;

          foreach($errLST as $elRow) {
          	   if($storeString <> trim($elRow['email_addr'])  ) {    	    	
                     // Set up new distribution TO
                    $postingDistributionTO = new PostingDistributionTO;
                    $postingDistributionTO->DMLType = "INSERT";
                    $postingDistributionTO->deliveryType = BT_EMAIL;
                    
                    $messagingDAO = new messagingDAO($dbConn);
                    $postingDistributionTO->subject = $messagingDAO->getTemplateOmniImportErrorSubject(trim($elRow['Principal'])); 
                    $postingDistributionTO->destinationAddr =  trim($elRow["email_addr"]); 
                    $storeString = trim($elRow['email_addr']);
                   
                    $messagingDAO = new messagingDAO($dbConn);
                    $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorHeader($elRow['Principal']);
 	  	         }
                    $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorBody($elRow['document_number'], 
                                                                                  $elRow['invoice_date'], 
                                                                                  trim($elRow['WhAbr']) . ' - ' .$elRow['deliver_name'], 
                                                                                  $elRow['status_msg'], 
                                                                                  $elRow['dataUid'], 
                                                                                  $elRow['psm.uid'], 
                                                                                  $elRow['type']);
          }
          
          $messagingDAO = new messagingDAO($dbConn);
          $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorend($elRow['Principal']);              	    	
          $postingDistributionTO->body = $bodyString;
          $postDistributionDAO = new postDistributionDAO($dbConn);
          $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
     }
     $dbConn->dbinsQuery("commit;");
      
 }

 
// ******************************************************************************************************************************

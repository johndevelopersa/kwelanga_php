<?php
/*
 https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/integrations/xero/pushBunduBrandsInvoices.php";

 * ------------------------------------------------------
 * PROCESSOR TO PUSH INVOICES TO XERO.COM FOR PENTACORP CATCH
 * ------------------------------------------------------
 */
/*-----------------------------------
	INTEGRATION PARAMETERS
-----------------------------------*/
$integrationTYPE = "xero";
$principalId = 454;
$principalStoreSpecialFieldID = 633;	/* !!PENTACORP CATCH : Xero Contact ID!! */
/*-----------------------------------*/

error_reporting(-1);
ini_set("display_errors", 1);

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT.$PHPFOLDER.'libs/common.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/BIDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostExtractDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/ExtractDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostBIDAO.php");
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . "DAO/OmniExtractDAO.php");

require __DIR__ . '/../IntegrationDAO.php';
require __DIR__ . '/IntegrationClass.php';	//xero integration class
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');


//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();
$errorTO = new ErrorTO;

$integrationDAO = new IntegrationDAO($dbConn);
$principalDAO   = new PrincipalDAO($dbConn);
$xeroApi        = new IntegrationClass();

echo "<pre>";

/*-------------------------------------------------*/
/*	Fetch Xero OAuth data for Principal
/*-------------------------------------------------*/
$authData = $integrationDAO->getForPrincipalByType($principalId, $integrationTYPE);
if(count($authData)==0){
    echo "no integration data for principal {$principalId} and type '{$integrationTYPE}'";
    return;
}
#var_dump($authData);

/*
-------------------------------------
|	Fetch Principal Info (not really needed?)
-------------------------------------
*/
$mfP = $principalDAO->getPrincipalItem($principalId);
if (count($mfP)==0) {
    BroadcastingUtils::sendAlertEmail("System Error", "xero push invoices load principal item failed in ".get_class($this)."!", "Y");
    return;
}
$principalName = $mfP[0]['principal_name'];
#var_dump($mfP);
echo "Principal {$principalId} --> {$principalName}\n";


/*-------------------------------------------------*/
/*  Fetch Notification Recipient ID
/*-------------------------------------------------*/
$reArr = (new BIDAO($dbConn))->getNotificationRecipients($principalId, NT_DAILY_EXTRACT_CUSTOM);
if (count($reArr)==0) {
    BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in " . __FILE__, "Y");
    return;
}
$recipientUId = $reArr[0]['uid'];
#var_dump($reArr);
echo "Using recipient: {$recipientUId}\n";

/*-------------------------------------------------*/
/*  Create Consolidated Transactions
/*-------------------------------------------------*/
/*-------------------------------------------------*/
/*  QUEUE DOCUMENTS IN SMART EVENTS
/*-------------------------------------------------*/
$rTO = (new PostExtractDAO($dbConn))->queueAllInvoiced($principalId, $recipientUId,
    $inclCancelled = false,  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
    $p_dtArr = false,
    $p_wDSArr = false,
    $fromInvDate='2023-07-01',
    $toInvDate=false,
    $chainUIdIn=false,
    $dataSource=false,
    $capturedBy=false,
    $depotUId = false	//ALAN: to modify for by DEPOT
);

//use the loaded receipientUID and not the notification type... *** same as document confirmations***
if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
    BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in " . __FILE__ . " ERROR:" . $rTO->description, "Y");
	return;
} else {
    $dbConn->dbinsQuery("commit;");
}

$depotList = "456,457,458,459";

/*-------------------------------------------------*/
/*  FETCH ORDERS THAT NEED TO BE SENT.
/*-------------------------------------------------*/
echo "Fetching getDailyExtractInvoicedOrders\n";
$seDocs = (new ExtractDAO($dbConn))->getDailyExtractInvoicedOrdersWithParms($principalId, 
                                                         $recipientUId, 
                                                         $depotList, 
                                                         '', 
                                                         $orderBy="se.status DESC, a.uid DESC, c.uid",
                                                         $docType='');


//print_r($seDocs);
//die();
if (!is_array($seDocs) || is_array($seDocs) && count($seDocs)==0) {
    echo "Successful --> No outstanding orders found!";
    return;
}
echo "Found: " . count($seDocs) . " order lines\n";


//group orders 
$bucketArr = [];
$storeArr = [];
foreach($seDocs as $row){
	$docId = $row['dm_uid'];
	$storeArr[$row['principal_store_uid']] = $row['principal_store_uid'];	//list of principal stores
		
	if(!isset($bucketArr[$docId])){
		$bucketArr[$docId] = [];
	}	
	$bucketArr[$docId][] = $row;
}


/*-------------------------------------------------*/
/*  COLLECT SPECIAL FIELDS
/*-------------------------------------------------*/
//FETCH CHAIN SPECIAL FIELDS XERO ID
$storeSpecialArr = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesMultEntities($principalId, $principalStoreSpecialFieldID, implode(",", $storeArr), CT_STORE_SHORTCODE, $arrayIndex = "entity_uid");

# we check these special fields values later
#var_dump($storeSpecialArr);
#var_dump($storeArr);
#var_dump($bucketArr);
#die();

/*-------------------------------------------------*/
/*	connect and test connection to xero.com
/*-------------------------------------------------*/
/* DON'T TOUCH : START */
try {

    $apiInstance = $xeroApi->getAccountingInstance($authData);

    //preform some simple action
    $st = microtime(1);
    $apiInstance->getContacts($xeroApi->getTenantId(), null, 'ContactStatus=="INACTIVE"', null, null, 1);
    $et = microtime(1);
    echo 'Connection test took:'.($et-$st)."\n";

} catch (Exception $e) {
    // Failed to get the access token or user details.
    BroadcastingUtils::sendAlertEmail("System Error", "failed to connect to XERO.COM API  {$principalId} --> {$principalName}: error - " . $e->getMessage(), "Y");
    return;
}

# dump this encase an update fails
#var_dump($xeroApi->getIntegrationArr());


/*
---------------------------------------
---> save the refreshed token data <---
---------------------------------------
*/
$errorTO = $integrationDAO->save($principalId, $integrationTYPE, $xeroApi->getIntegrationArr());
if ($errorTO->type != FLAG_ERRORTO_SUCCESS) {
    echo "error update of database failed: {$errorTO->description}";
    return ;
}
$dbConn->dbQuery("commit");
/*---------------------------------------*/
/* DON'T TOUCH : END */
/*---------------------------------------*/



/*---------------------------------------*/
/*	FOR TESTING / INTEGRATION USES - GET A LIST OF CONTACTS FROM XERO
/*---------------------------------------*/
/*
$getContacts = $apiInstance->getContacts($xeroApi->getTenantId(), null, 'ContactStatus=="ACTIVE"', null, null, $page = null);
$contactsArr = $getContacts->getContacts();
echo "Total Contacts: " . count($contactsArr) . "<br>";
foreach($contactsArr as $contant){
    echo $contant->getContactId() . " | " . $contant->getName(); // " | " . $contant->getAddresses();
    echo "<br>";
}
die("end of all contacts");
*/

$errorSEUIdArr = array();
$successSEUIdArr = array();

$successCount = 0;


//loop through each CHAIN ID...
foreach($bucketArr as $lineArr){			
							

	//BUILD UP DATA HERE FOR XERO INVOICE
	
	$storeId  = $lineArr[0]['principal_store_uid'];
	$storeName = $lineArr[0]['deliver_name'];
	
// print_r($lineArr);
	
	//DO WE HAVE A FILLED IN XERO SPECIAL FIELD?
	if(!isset($storeSpecialArr[$storeId]) || 
     empty($storeSpecialArr[$storeId]['value']) || 
     strlen($storeSpecialArr[$storeId]['value']) != 36)	//xero ids are 36 chars
  {
		
		echo "Empty Xero Id for Store: {$storeId} - {$storeName} !\n";
		
		//generate error for this chain item!		
		if(!isset($errorSEUIdArr["no store special field"])){
			$errorSEUIdArr["no store special field"] = [];		
		}
		$errorSEUIdArr["no store special field"][] = $lineArr[0]['se_uid'];

		continue;
	}
	
	//set the contact id value, this is used as the customer identify on xero's end!
	$chainXeroContactID = $storeSpecialArr[$storeId]['value'];
	
	//verify this STORED contact id via xero's API
	$verifiedContact = false;
	try {				
		$invContact = $apiInstance->getContact($xeroApi->getTenantId(), $chainXeroContactID);			
		if(count($invContact->getContacts())==1){				
			$verifiedContact = true;			
		}
	} catch (Exception $e) {			
		echo "verified contact error " . $e->getMessage() . "\n";
	}
	
	if(!$verifiedContact){
		
		echo "contact id not verified: {$chainXeroContactID}!\n";
		
		//generate error for this chain item!			
		if(!isset($errorSEUIdArr["store xeroid lookup"])){
			$errorSEUIdArr["store xeroid lookup"] = [];		
		}
		$errorSEUIdArr["no store special field"][] = $lineArr[0]['se_uid'];
		continue;	
	}
				

	$invLines = [];	//consolidated line items

	//calculate product totals, by chain
	foreach($lineArr as $lineRow){
		
		$productCode = $lineRow['alt_code'];
		$productDesc = $lineRow['product_description'];
		$productUnitAmount = (float)$lineRow['net_price'];	//this could vary across invoices (times and sessions)
		$productTotalTax = (float)$lineRow['vat_amount'];
		$productTotalQty = (int)$lineRow['document_qty'];
		$productTotalAmount = (float)$lineRow['extended_price'];
		$productTotalDiscountValue = 0;
	
		//CREATE XERO LINE ITEM - PER PRODUCT UID
		$lineObj = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
		$lineObj->setItemCode($productCode)
				->setDescription($productDesc)
				//->setDiscountAmount(number_format($productTotalDiscountValue, 2, '.', ''))
				->setLineAmount(number_format($productTotalAmount, 2, '.', ''))		//see: setLineAmountTypes				
				->setTaxAmount(number_format($productTotalTax, 2, '.', ''))
				//->setAccountCode('100')
				//->setDiscountRate()
				//->setRepeatingInvoiceId()
				->setTaxType('OUTPUT3')	//The tax type from TaxRates
				//->setTracking()	//Optional Tracking Category – see Tracking.
				->setUnitAmount(number_format($productUnitAmount, 2, '.', ''))
				->setQuantity((int)$productTotalQty);
		
		//add this line to the chain invoice
		array_push($invLines, $lineObj);
	}
	//set contact object
	$contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
	$contact->setContactId($chainXeroContactID);


	$invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
	$invoice->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCREC)
			->setReference(ltrim($lineArr[0]['invoice_number'],'0') . ' - ' . trim($lineArr[0]['customer_order_number']) . ' - ' . trim($lineArr[0]['branch_code']) . ' - ' . trim($lineArr[0]['deliver_name'])) 
      ->setInvoiceNumber($lineArr[0]['invoice_number'])
			//->setAmountDue()
			//->setAmountPaid()
			//->setTotalDiscount()
			//->setTotalTax()
			//->setTotal()			
			->setContact($contact)	//set the customer/client
			//->setCurrencyCode("ZAR")
			//->setCurrencyRate()
			->setDate($lineArr[0]['invoice_date'])
			->setDueDate($lineArr[0]['invoice_date'])							
			->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_SUBMITTED)			
			->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE)
			->setLineItems($invLines);	//provide all the line items


	try {
		
					echo "<br>";

		/*---------------------------------------*/
		/* ACTUALLY POST TO XERO.COM
		/*---------------------------------------*/		


		$invoices = new XeroAPI\XeroPHP\Models\Accounting\Invoices;
		$invoices->setInvoices([$invoice]);

// echo "Here";
// var_dump($invoices);


		$result = $apiInstance->createInvoices($xeroApi->getTenantId(), $invoices); // <---- ACTUAL POST REQUEST HERE
//echo "Here";
//var_dump($result);

    $result->getInvoices()[0]->getValidationErrors();

    if(is_array($result->getInvoices()[0]->getValidationErrors()) && count($result->getInvoices()[0]->getValidationErrors())){
     
          //there were errors
          $errors = [];
          foreach($result->getInvoices()[0]->getValidationErrors() as $error){
          	     $successCount++;
          	     echo "Invoice posting error: " . $error->getMessage() . "\n";
                 $updateSmartE = new OmniExtractDAO($dbConn);
                 
                 $general1 = "Invoice Error: " . $error->getMessage() . "\n";
                 $general2 = $error->getMessage() . "\n";
          }
          
          if(strpos($general2,"must be unique")) {
                   $seStatus = FLAG_STATUS_CLOSED;    
          } else {
                   $seStatus = FLAG_ERRORTO_ERROR;
          }

          $updateSmartE = new OmniExtractDAO($dbConn);
          $uSE = $updateSmartE->updateSmartEventDirectly($lineArr[0]['se_uid'], $general1, $general2, $statusFlag = $seStatus);

    } else {
         
          $xeroInvNum = $result->getInvoices()[0]->getInvoiceNumber();
		      echo "Created Invoice: " . $result->getInvoices()[0]->getInvoiceNumber() . ' --> '. $result->getInvoices()[0]->getTotal() . "\n";
      		echo "<br><br><br>";
          #var_dump(get_class_methods(($result->getInvoices()[0])));
		      
		      $successCount++;
		      $general1 = "SUCCESS";
          $general2 = $xeroInvNum;
          $updateSmartE = new OmniExtractDAO($dbConn);
          $uSE = $updateSmartE->updateSmartEventDirectly($lineArr[0]['se_uid'], $general1, $general2, $statusFlag = FLAG_STATUS_CLOSED);
    }
						
	} catch (Exception $e) {
		
		echo "Invoice posting error: \n";
		
    $general1 = "Bomb Out \n";
    
		$updateSmartE = new OmniExtractDAO($dbConn);
    $uSE = $updateSmartE->updateSmartEventDirectly($lineArr[0]['se_uid'], $general1, $general2 = "", $statusFlag = FLAG_ERRORTO_ERROR);

	}
  if($successCount >= 5) {
  	break;	
  }
  echo $successCount . "<br>";

} /* eo chain loop */


/*-------------------------------------------------------------
 *   UPDATE SMART EVENT in BULK
 *-----------------------------------------------------------*/
if (count($successSEUIdArr) > 0) {
	
    $bIResult = (new PostBIDAO($dbConn))->setSmartEventStatusBulk((implode(",", $successSEUIdArr)), "SUCCESS", "");
    if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $error = "Failed in " . get_class($this) . " extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in " . get_class($this) . " extract", $error, "Y", false);
        exit;
    }  
}
/*-------------------------------------------------------------
 *   ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
 *------------------------------------------------------------*/
 
if (count($errorSEUIdArr) > 0) {
	
    //errors are grouped by error message
    foreach ($errorSEUIdArr as $errorMessage => $UidArr) {    	
        if (count($UidArr) > 0) {
            $bIResult = (new PostBIDAO($dbConn))->setSmartEventStatusBulk(implode(",", $UidArr), "ERROR", $errorMessage, FLAG_ERRORTO_ERROR);
            if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                $error = "Failed in " . get_class($this) . " on setting errror setSmartEventStatusBulk with {$bIResult->description}";
                BroadcastingUtils::sendAlertEmail("Error in " . get_class($this) . " extract", $error, "Y", false);
                exit;
            }               
        }
    }
}0;

    echo "<br>End of Bundu Brands Invoices<br>";
    echo "[***EOS***]<br>";


    
//******************************************************************************************************************************
 function OmniErrorReporting($principalUId, $seType, $nType) {
 	
     global $ROOT; global $PHPFOLDER;
     
     include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
     include_once($ROOT . $PHPFOLDER . "DAO/messagingDAO.php");
     include_once($ROOT . $PHPFOLDER . "DAO/postDistributionDAO.php");
     include_once($ROOT . $PHPFOLDER . "TO/PostingDistributionTO.php");
     include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");
     include_once($ROOT . $PHPFOLDER . "DAO/OmniExtractDAO.php"); 
     
     require_once __DIR__ . "/../../../properties/Omni_Constants_418.php";   
      
     if (!isset($dbConn)) {
        $dbConn = new dbConnect();
        $dbConn->dbConnection();
     }	
     
     
     $constantsClass = "Omni_Constants_418" ;
 	
     $allowedErrors = 10;

     $eList = (new OmniExtractDAO($dbConn))->getListofOmniErrors($constantsClass::PrincipalID, 
                                                                 $constantsClass::extractType,
                                                                 $allowedErrors);
     //echo "<pre>";                                                        
     //print_r($eList);

    if (count($eList) > 0) {
    	
          $storeString = '';
          $bodyString = '';
    
          $c= 0;

          foreach($eList as $elRow) {
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
                    $postingDistributionTO->subject = $messagingDAO->getTemplateOmniImportErrorSubject(trim($elRow['Principal'])); 
                    $postingDistributionTO->destinationAddr =  trim($elRow["email_addr"]); 
                    $storeString = trim($elRow['email_addr']);
                    $messagingDAO = new messagingDAO($dbConn);
                    $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorHeader('');

                 }  
                
                    $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorBody($elRow['document_number'], 
    	                                                                                  $elRow['order_date'], 
    	                                                                                  $elRow['deliver_name'], 
    	                                                                                  trim(preg_replace("/\r|\n/", "", $elRow['general_reference_2'])) , 
    	                                                                                  $elRow['data_uid'], 
    	                                                                                  $elRow['psm_uid'],
    	                                                                                  $constantsClass::extractTypeInvoices);
    	                                                                           
    	              // Update Error count
    	              $c++;
    	              
    	              // echo $elRow['seUid'] . "<br>";
    	       
    	              // $tE = (new OmniExtractDAO($dbConn))->updateOmniErrorCount($elRow['seUid']);
    	              
    	              
             }   
          
          $messagingDAO = new messagingDAO($dbConn);
          $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorend($elRow['Principal']);
          
          
                                  	    	
          $postDistributionDAO = new postDistributionDAO($dbConn);
          $postingDistributionTO->body = $bodyString;
          $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
          
          //print_r($postingDistributionTO);
          
          
     }
     $dbConn->dbinsQuery("commit;");
      
 }

 
// ******************************************************************************************************************************

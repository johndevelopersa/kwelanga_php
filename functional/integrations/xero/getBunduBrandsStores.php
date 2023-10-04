<?php

/*
https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/integrations/xero/getBunduBrandsStores.php";
 * ------------------------------------------------------
 * PROCESSOR TO PUSH INVOICES TO XERO.COM FOR PENTACORP
 * ------------------------------------------------------
 */
 
 
/*-----------------------------------
	INTEGRATION PARAMETERS
-----------------------------------*/
$integrationTYPE = "xero";
$principalId = 454;
$principalChainSpecialFieldID = 633;	/* !!PENTACORP : Xero Contact ID!! */
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

require __DIR__ . '/../IntegrationDAO.php';
require __DIR__ . '/IntegrationClass.php';	//xero integration class
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');


//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

$integrationDAO = new IntegrationDAO($dbConn);
$principalDAO = new PrincipalDAO($dbConn);
$xeroApi = new IntegrationClass();


echo "<pre>";

/*-------------------------------------------------*/
/*	Fetch Xero OAuth data for Principal
/*-------------------------------------------------*/
$authData = $integrationDAO->getForPrincipalByType($principalId, $integrationTYPE);
if(count($authData)==0){
    echo "no integration data for principal {$principalId} and type '{$integrationTYPE}'";
    return;
}
// #var_dump($authData);

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
// #var_dump($mfP);
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
// #var_dump($reArr);
echo "Using recipient: {$recipientUId}\n";


/*-------------------------------------------------*/
/*  QUEUE DOCUMENTS IN SMART EVENTS
/*-------------------------------------------------*/
$rTO = (new PostExtractDAO($dbConn))->queueAllInvoiced($principalId, $recipientUId,
    $inclCancelled = false,  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
    $p_dtArr = false,
    $p_wDSArr = false,
    $fromInvDate=false,
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


/*-------------------------------------------------*/
/*  FETCH ORDERS THAT NEED TO BE SENT.
/*-------------------------------------------------*/
echo "Fetching getDailyExtractInvoicedOrders\n";
$seDocs = (new ExtractDAO($dbConn))->getDailyExtractInvoicedOrders($principalId, $recipientUId);

if (!is_array($seDocs) || is_array($seDocs) && count($seDocs)==0) {
    echo "Successful --> No outstanding orders found!";
    return;
}
echo "Found: " . count($seDocs) . " order lines\n";

//group orders into a bucket of []chainUID[]productUID <---- our master array
/*EG:
	array() {
	  [2756]=>
	  array() {
		[101209]=> []lines (with this product & chain)
		[101205]=> []lines (with this product & chain)
	  }
	}
*/

$chainBucketArr = [];
foreach($seDocs as $seRow){
	
	$chainId = $seRow['principal_chain_uid'];
	$productId = $seRow['product_uid'];	//this could also be product code
		
	if(!isset($chainBucketArr[$chainId])){
		$chainBucketArr[$chainId] = [];
	}
	if(!isset($chainBucketArr[$chainId][$productId])){
		$chainBucketArr[$chainId][$productId] = [];
	}
	
	$chainBucketArr[$chainId][$productId][] = $seRow;
	
}

$chainIdArr = array_keys($chainBucketArr);


#var_dump($chainIdArr);
#var_dump($chainBucketArr);
#var_dump($seDocs);


/*-------------------------------------------------*/
/*  COLLECT SPECIAL FIELDS
/*-------------------------------------------------*/
//FETCH CHAIN SPECIAL FIELDS XERO ID
$chainSpecialArr = (new MiscellaneousDAO($dbConn))->getPrincipalSpecialFieldValuesMultEntities($principalId, $principalChainSpecialFieldID, implode(",", $chainIdArr), CT_CHAIN_SHORTCODE, $arrayIndex = "entity_uid");


# we check these special fields values later
#var_dump($chainSpecialArr);

/*-------------------------------------------------*/
/*  ALLOCATE DEPOT CHAIN DOCUMENT NUMBER
/*-------------------------------------------------*/

//TODO: ALAN


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
# var_dump($xeroApi->getIntegrationArr());


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

$getContacts = $apiInstance->getContacts($xeroApi->getTenantId(), null, 'ContactStatus=="ACTIVE"', null, null, $page = null);
$contactsArr = $getContacts->getContacts();
echo "Total Contacts: " . count($contactsArr) . "<br>";
foreach($contactsArr as $contant){
    echo $contant->getContactId() . " | " . $contant->getName(); // " | " . $contant->getAddresses();
    echo "<br>";
}
die("end of all contacts");


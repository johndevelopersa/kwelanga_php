<?php

/* * ********************************************************************************************
 * *
 * *  LIVE Omni - SALES ORDER PUSH TO OMNI RESTAPI API
 * *
 * *********************************************************************************************** */
 
require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/BIDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/PostBIDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/messagingDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/postExtractDAO.php");
include_once($ROOT . $PHPFOLDER . 'TO/PostingDistributionTO.php');
require_once($ROOT . $PHPFOLDER . "properties/" . "Omni_Constants_" . $principal_uid . ".php");

$constantsClass = "Omni_Constants_" . $principal_uid ;

set_time_limit(15*60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    
}

echo "<pre style='font-size:14px;'>";
echo str_repeat("-", 75) . "\n";
echo "Que Transactions for DGS Omni Extract\n";
echo str_repeat("-", 75) . "\n";

/*-------------------------------------------------*/
/*  FETCH Notification Recipients ??
/*-------------------------------------------------*/
//use the receipients listed in the notification table instead of hard coding them!!!
//expecting only one row loaded per principal extract
$reArr = (new BIDAO($dbConn))->getNotificationRecipients($constantsClass::PrincipalID, NT_DAILY_EXTRACT_CUSTOM);
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
    DT_DELIVERYNOTE,
    DT_ORDINV,
    DT_ORDINV_ZERO_PRICE,
];
$documentStatusArr = [
    $importStatus
];

$errorTO = (new PostExtractDAO($dbConn))->queueAllInvoiced($constantsClass::PrincipalID,
                                                           $recipientUId,
                                                           $inclCancelled = false,
                                                           $documentTypeArr,
                                                           $documentStatusArr,
                                                           $fromInvDate=$constantsClass::transactionStart,
                                                           $toInvDate=false,
                                                           $chainUIdIn= false,
                                                           $dataSource=false,
                                                           $capturedBy=false,
                                                           $depotUId=false,
                                                           $altChainUIdIn=false);  
    
    //use the loaded receipientUID and not the notification type... *** same as document confirmations***
    
//    print_r ($errorTO);
    
    
if ($errorTO->type != FLAG_ERRORTO_SUCCESS) {
    BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in " . __FILE__ . " ERROR:" . $rTO->description, "Y");
} else {
    $dbConn->dbinsQuery("commit;");
    $errorTO->type = FLAG_ERRORTO_SUCCESS;
    $programComplete = 'Y';
}

/*-------------------------------------------------------------------------------------------------------------------------------------------------*/
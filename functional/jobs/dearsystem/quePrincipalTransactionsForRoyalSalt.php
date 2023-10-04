<?php

/* * ********************************************************************************************
 * *
 * *  LIVE Que Invoices from Dear Systems Extract
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
require_once __DIR__ . "/../../../properties/RoyalSaltConstants.php";

include_once($ROOT . $PHPFOLDER."DAO/DearSystemDAO.php");

set_time_limit(15*60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    
}

echo "<pre style='font-size:14px;'>";
echo str_repeat("-", 75) . "\n";
echo "Que Transactions for Royal Salt Dear Extract\n";
echo str_repeat("-", 75) . "\n";

/*-------------------------------------------------*/
/*  FETCH Notification Recipients ??
/*-------------------------------------------------*/
//use the receipients listed in the notification table instead of hard coding them!!!
//expecting only one row loaded per principal extract
$reArr = (new BIDAO($dbConn))->getNotificationRecipients(RoyalSaltConstants::PrincipalID, NT_DAILY_EXTRACT_CUSTOM);
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

$errorTO = (new PostExtractDAO($dbConn))->queueAllInvoiced(RoyalSaltConstants::PrincipalID,
                                                           $recipientUId,
                                                           $inclCancelled = false,
                                                           $documentTypeArr,
                                                           $documentStatusArr=false,
                                                           $fromInvDate=false,
                                                           $toInvDate=false,
                                                           $chainUIdIn= false,
                                                           $dataSource=false,
                                                           $capturedBy=false,
                                                           $depotUId=false,
                                                           $altChainUIdIn=false);  
    
    //use the loaded receipientUID and not the notification type... *** same as document confirmations***
    
if ($errorTO->type != FLAG_ERRORTO_SUCCESS) {
    BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in " . __FILE__ . " ERROR:" . $rTO->description, "Y");
} else {
    $dbConn->dbinsQuery("commit;");
    $errorTO->type = FLAG_ERRORTO_SUCCESS;
    $programComplete = 'Y';
}

$xtDocs = (new DearSystemDAO($dbConn))->getOrdersForDear(RoyalSaltConstants::PrincipalID, $recipientUId, '');

$docStore = "";
$firstOrder = 'T';

echo "<table>";

foreach($xtDocs as $orow) {
	
          if($docStore <> $orow['invoice_number'] )	{
          	  if($firstOrder == "T"){
                  echo "<br>";
                  echo "<h1>Next Invoice to be extracted - " .  $orow['invoice_number'] . "  " . $orow['deliver_name'] . "  " . $orow['Stat'] . "</h1>";		          	  	
                  echo "<br>";
                  echo "Invoices for Extract ";
                  echo "<br>";
                  $firstOrder = 'F';
          	  }         	
              // Get Special fields
              $sf555 = (new DearSystemDAO($dbConn))->getRoyalSaltSpecField(555, $orow['psmUid']); 
              
              if(trim($sf555[0]['value']) == '' || count($sf555) == 0)  {
                     $account_sf = 'Account not found - Do not Continue with Extract ***********'	;              	
              } else {
                     $account_sf = trim($sf555[0]['value']);
              }                
              
              $sf556 = (new DearSystemDAO($dbConn))->getRoyalSaltSpecField(556, $orow['psmUid']); 
              
              if(trim($sf556[0]['value']) == '' || count($sf556) == 0)  {
                     $region_sf = 'Region not found - Do not Continue with Extract ***********'	;              	
              } else {
                     $region_sf = trim($sf556[0]['value']);
              }
              
              echo "<tr>";
              echo "<td>" .  $orow['invoice_number']      . "</td>";
              echo "<td>" .  $orow['deliver_name']        . "</td>";
              echo "<td>" .  $orow['Stat'] . "</td>";
              echo "<td>" .  $account_sf                  . "</td>";
              echo "<td>" .  $region_sf                   . "</td>";

          }
          $docStore = $orow['invoice_number'] ;
}

echo "</table>";

echo "<br>"; 
echo "<br>"; 
echo "****EOS****"; 
echo "<br>"; 



/*-------------------------------------------------------------------------------------------------------------------------------------------------*/
<?php

/**
 * Description of SOAPClient
 *
 * @created : 2012/09/11
 * @author  : marek willman
 *
 * @description: ws export processor.
 * @note : this is a SEND process meaning it "initiates". Send does not mean we send stuff eg. orders to a 3rd party.
 *         For example, we can "send" a "request" to give us orders from a 3rd party system, meaning we initiate.
 *
 */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER.'functional/ws/client/CheckersClient.php');
require_once($ROOT.$PHPFOLDER.'functional/ws/client/CheckersClientREST.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/ServerConstants.php');
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostBIDAO.php");
include_once($ROOT.$PHPFOLDER."TO/SmartEventTO.php");


set_time_limit(30*60); // 30 mins - soap calls can take long depending on the soap server.
ini_set('default_socket_timeout', 600);

$statST = microtime(true);
$jobCount = 0;

echo "------- START: " . CommonUtils::getGMTime(0) . " -------<BR>";

$dbConn = new dbConnect();
$dbConn->dbConnection();

$principalDAO = new PrincipalDAO($dbConn);

  /***************************************************
   * Checkers Order Collection (PULL)
   ***************************************************/

  $checkersClient = new CheckersClient($dbConn);
  $checkersClientOverride = new CheckersClientREST($dbConn);
  $mfPV = $principalDAO->getPrincipalsForVendor(V_CHECKERS_VENDOR);
  // checkers have a separate username and pwd for each principal so need to connect individually
  
  foreach ($mfPV as $p) {
    $principalUId = $p["principal_uid"];
    $username = $p["username"];
    $password = $p["password"];
    $vendorAccount = $p["vendor_account"];
    $tradeDocumentType = $p["trade_document_type"];
    $vendorSystem = $p["vendor_system"];

    echo "\nDocuments for Principal : {$principalUId}\n, username : {$p["username"]}\n vendor_account: {$p["vendor_account"]}\n trade_document_type: {$p["trade_document_type"]}\n Vendor System {$p["vendor_system"]}\n";

    // Checkers have a maximum of 20 orders so you can control each "get" with a parameter value, to a max
    for ($i=0; $i<5; $i++) {
      $moreRowsToGet = 0;
      
      if ($tradeDocumentType=="CLAIMS") {

        if ($p["adaptor"]=="CheckersClientREST") $moreRowsToGet=$checkersClientOverride->runProcess_getClaims($principalUId, $vendorAccount, $username, $password);
        else $moreRowsToGet=$checkersClient->runProcess_getClaims($principalUId, $numOfOrders="5", $vendorAccount, $username, $password, $vendorSystem);

        $jobCount+=$checkersClientOverride->jobCount;

      } elseif ($tradeDocumentType<>"IGNOREORDERS") {

        if ($p["adaptor"]=="CheckersClientREST") $moreRowsToGet=$checkersClientOverride->runProcess_getOrders($principalUId, $vendorAccount, $username, $password, $vendorSystem);
        else $moreRowsToGet=$checkersClient->runProcess_getOrders($principalUId, $numOfOrders="20", $vendorAccount, $username, $password, $vendorSystem);

        $jobCount+=$checkersClient->jobCount;

      }

      if (!$moreRowsToGet) break;
    }
  }

  echo "[@>>>JOBS:" . $jobCount .";TT:" , (round(microtime(true) - $statST,4)) , "@]<BR>";
echo "------- END: " . CommonUtils::getGMTime(0) . " -------<BR>";
echo "<BR>";
echo "[***EOS***]";

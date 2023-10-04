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
 * NB !!!!
 * THIS PROCESS MUST BE RUN UNDER THE 2nd INSTANCE OF APACHE (on Port 8080) AS IT NEEDS THE SQLSVR DRIVERS !!!!
 *
 */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER.'functional/ws/client/ICTechnologyClient.php');  // directsql
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
   * ICTechnology Order Collection (SQL PULL)
  ***************************************************/

echo "***************************************************<br>
      * ICTechnology Order Collection (SQL PULL)<br>
      ***************************************************<br>";

  $ICTClient = new ICTechnologyClient($dbConn);
  $mfPV = $principalDAO->getPrincipalsForVendor(V_ICTECNOLOGY_VENDOR);
  // checkers have a separate username and pwd for each principal so need to connect individually
  foreach ($mfPV as $p) {
    $principalUId = $p["principal_uid"];
    $username = $p["username"];
    $password = $p["password"];
    $vendorAccount = $p["vendor_account"];

    echo "<p><u><b>Orders for Principal : {$principalUId}</b></u></p>";

    // All Orders are retrieved at once
    $moreRowsToGet=$ICTClient->runProcess_getOrders($principalUId, $vendorAccount, $username, $password);

    $jobCount+=$ICTClient->jobCount;

    if (!$moreRowsToGet) break;
  }

  echo "[@>>>JOBS:" . $jobCount .";TT:" , (round(microtime(true) - $statST,4)) , "@]<BR>";
echo "------- END: " . CommonUtils::getGMTime(0) . " -------<BR>";
echo "<BR>";


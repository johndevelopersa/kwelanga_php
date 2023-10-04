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
require_once($ROOT.$PHPFOLDER.'functional/ws/client/PnPClient.php');  //soap lib
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
   * PnP Invoice Upload
   ***************************************************/
  $pnpClient = new PnPClient($dbConn);
  $pnpClient->runProcess();
  $jobCount+=$pnpClient->jobCount;
  echo "<br>PnP Invoice Upload completed.<br><br>";


  /***************************************************
   * Checkers Invoice Upload
   ***************************************************/
  $checkersClient = new CheckersClientRest($dbConn);
  $checkersClient->runProcess_uploadInvoice();
  $jobCount+=$checkersClient->jobCount;
  echo "<br>Checkers Invoice Upload completed.<br><br>";

  echo "[@>>>JOBS:" . $jobCount .";TT:" , (round(microtime(true) - $statST,4)) , "@]<BR>";
echo "------- END: " . CommonUtils::getGMTime(0) . " -------<BR>";
echo "<BR>";
echo "[***EOS***]";

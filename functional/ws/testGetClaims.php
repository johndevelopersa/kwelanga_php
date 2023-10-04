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
require_once($ROOT.'ws/client/CheckersClient.php');  //soap lib
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/ServerConstants.php');
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostBIDAO.php");
include_once($ROOT.$PHPFOLDER."TO/SmartEventTO.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

$checkersClient = new CheckersClient($dbConn);
$principalUId = 4;
$username = "BBrandssa";
$password = "Birds61";
$vendorAccount = "864090";

echo "<p><u><b>Claims for Principal : {$principalUId}</b></u></p>";

$moreRowsToGet=$checkersClient->runProcess_getClaims($principalUId, $numOfOrders="1", $vendorAccount, $username, $password);



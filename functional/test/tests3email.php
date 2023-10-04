<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ImportDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/BIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostBIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
include_once($ROOT.$PHPFOLDER."TO/SequenceTO.php");
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER.'DAO/RemittanceDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/BillingDAO.php');
require_once $ROOT . $PHPFOLDER . "libs/smartqueue/SmartQueue.php";

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}


$postingDistributionTO = new PostingDistributionTO;
$postingDistributionTO->DMLType = "INSERT";
$postingDistributionTO->deliveryType = BT_EMAIL;
$postingDistributionTO->subject = 'LOCAL ATTACHED FILE!';
$postingDistributionTO->body = 'Please see attached file!';
$postingDistributionTO->attachmentFile = 'archives/extracts/357_rhino/bkup/2021/08/PAS357460.csv';
$postingDistributionTO->destinationAddr = 'onyx@gouws.co';

//$arr = json_encode($postingDistributionTO, JSON_FORCE_OBJECT);
//$d = json_decode($arr, true);


$dResult3 = (new PostDistributionDAO($dbConn))->postQueueDistribution($postingDistributionTO);

var_dump($dResult3);

if($dResult3->type == FLAG_ERRORTO_SUCCESS){
    $dbConn->dbinsQuery("commit");


    echo "SUCCESSFULLY QUEUED LOCAL FILE TEST!";
} else {
    var_dump($dResult3);
}

die("stoppp!");


$postingDistributionTO = new PostingDistributionTO;
$postingDistributionTO->DMLType = "INSERT";
$postingDistributionTO->deliveryType = BT_EMAIL;
$postingDistributionTO->subject = 'S3 ATTACHED FILE!';
$postingDistributionTO->body = 'Please see attached file!';
$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, '/archives/extracts/357_rhino/2021/08/PAS357460.csv');
$postingDistributionTO->destinationAddr = 'onyx@gouws.co';

$dResult1 = (new PostDistributionDAO($dbConn))->postQueueDistribution($postingDistributionTO);

var_dump($dResult1);

if($dResult1->type == FLAG_ERRORTO_SUCCESS){
    $dbConn->dbinsQuery("commit");
    echo "SUCCESSFULLY QUEUED SINGLE TEST!";
} else {
    var_dump($dResult1);
}


$postingDistributionTO = new PostingDistributionTO;
$postingDistributionTO->DMLType = "INSERT";
$postingDistributionTO->deliveryType = BT_EMAIL;
$postingDistributionTO->subject = 'S3 MULTI ATTACHED FILES!';
$postingDistributionTO->body = 'Please see attached files!';
$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, '/archives/extracts/357_rhino/2021/08/PAS357460.csv');
$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, '/archives/extracts/357_rhino/2021/08/PAS357461.csv');
$postingDistributionTO->destinationAddr = 'onyx@gouws.co';

$dResult = (new PostDistributionDAO($dbConn))->postQueueDistribution($postingDistributionTO);

var_dump($dResult);

if($dResult->type == FLAG_ERRORTO_SUCCESS){
    $dbConn->dbinsQuery("commit");
    echo "SUCCESSFULLY QUEUED MULTI TEST!";
} else {
    var_dump($dResult);
}

<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingDistributionTO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostDistributionDAO.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$postingDistributionTO = new PostingDistributionTO;
$postingDistributionTO->DMLType = "INSERT";
$postingDistributionTO->deliveryType = BT_EMAIL;
$postingDistributionTO->subject = "LEGACY MY SQS TEST EMAIL - " . time();
$postingDistributionTO->body = 'Report Test' ;
//$postingDistributionTO->attachmentFile = '/ftp/rvl/UpliftDocuments/R-'.trim($mDetails[0]['deliver_name']) .' - ' . ltrim($mDetails[0]['document_number'],'0') .'.pdf';
$postingDistributionTO->destinationAddr = $_GET['to'] ?? "onyx@gouws.co";
$postingDistributionTO->attachmentFile ='s3://kos.storage.cpt/archives/reports/2023/06/22/Stock_Movement_Report_.20230622041147.2.4.2259.csv,s3://kos.storage.cpt/archives/reports/2023/06/22/New_Daily_Sales_P216A.20230622041140.11.216.9945.csv';
//$postingDistributionTO->destinationAddr = "onyx@example.com";
$postDistributionDAO = new PostDistributionDAO($dbConn);

 $st = microtime(true);
$dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);

$dbConn->dbinsQuery("commit;");

if($dResult->isSuccess()) {

   // $postingDistributionTO->messageId = guid();
   // $arr = json_encode($postingDistributionTO, JSON_FORCE_OBJECT);
   // $postingDistributionTOArr = json_decode($arr, true);

    var_dump($dResult);

   // $result = $postDistributionDAO->postDecoupledSmartQueue($postingDistributionTOArr, 0, 30, 'EmailEvents.fifo');

   // echo "success!\n<Br>";
   // var_dump($result);
//} else {
//    var_dump($dResult);
}

  $et = microtime(true);
  echo $et-$st . "<br>\n";

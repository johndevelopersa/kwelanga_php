<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'properties/ServerConstants.php');
include_once($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
include_once($ROOT . $PHPFOLDER . "TO/SequenceTO.php");
include_once($ROOT . $PHPFOLDER . "DAO/SequenceDAO.php");

error_reporting(-1);
ini_set('display_errors', 1);

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}

$getSequenceResult = CommonUtils::getRandomInteger(); //preset incase of failure/error with actual key.
$sequenceTO = new SequenceTO();
$sequenceTO->sequenceKey = "SOURCEID";
$sequenceTO->sequenceStart = 0;
$sequenceTO->sequenceLen = 6;
$sequenceDAO = new SequenceDAO($dbConn);
$seqResult = $sequenceDAO->getSequence($sequenceTO, $getSequenceResult);

var_dump($seqResult);
var_dump($getSequenceResult);
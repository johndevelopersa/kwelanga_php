<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php"); // needed because of access_control commented out
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$userId      = ((isset($_GET["USERID"]))?$_GET["USERID"]:"sfdasdf");
$principalId = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:"sdfasdf");
$docmastId   = ((isset($_GET["DOCMASTID"]))?$_GET["DOCMASTID"]:"sdfsdfsa"); 
$outputTyp   = ((isset($_GET["OUTPUTTYP"]))?$_GET["OUTPUTTYP"]:"sdfsdfsa");
$templat     = ((isset($_GET["TEMPLATP"]))?$_GET["TEMPLATP"]:"sdfsdfgdfgdgfgdfsa"); 

$dbConn->dbClose(); // have to close and reopen because storeCard below closes it

// get the store card output;

ob_start(); //Turn on output buffering
include_once("C:/inetpub/wwwroot/systems/kwelanga_system/".$PHPFOLDER."functional/presentations/view/".$templat);
//copy current buffer contents into variable and delete current output buffer

$htmlBody = ob_get_clean();

file_put_contents($ROOT.$PHPFOLDER.'log/print.txt', $htmlBody); 


$htmlBody=BroadcastingUtils::sanitizePDFHTML($htmlBody);
$pdfBody=BroadcastingUtils::convertHTMLToPDF($htmlBody, "Document {$docmastId}", $docmastId, "Document, {$docmastId}, Invoiced"); // variables are from object called
BroadcastingUtils::outputPDFWithHeaders("Kwelangasolutions D{$docmastId}.pdf", $pdfBody);

// print(CommonUtils::getJavaScriptMsg($Returnmessages));


// $dbConn->dbClose();


?>
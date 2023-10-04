<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/ServerConstants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
//include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
//include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtilsPHPMailer.php');

include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

// print_r(BroadcastingUtils::sendEmailHTMLEmbedded("test Subj",array("mark@sasoft.co.za","willman.mark@gmail.com"), "Test Body", "Plain Body", $from = array()));

//print_r(BroadcastingUtils::sendEmailWithAttachment("test Subj", "test Body", "test plain body", array("willman.mark@gmail.com"), "kwelanga_php/libs/purposeProfit.pdf", $from = array()));


//BroadcastingUtils::sendTextEmail("PHPMailer Test: sendTextEmail", "test 'plain/text' email", [SUPPORT_EMERGENCY_EMAIL_CRITICAL, SUPPORT_EMERGENCY_EMAIL_BKUP]);
//BroadcastingUtils::sendAlert("PHPMailer Test: sendAlert -- body", false);


$dbConn = new dbConnect();  //Create new database object
$dbConn->dbConnection();  //Database connection method

#$e = BroadcastingUtils::sendEmailNewUser(1471, $dbConn);

#$e = BroadcastingUtils::emailResetPasswordLINK(1471, $dbConn);

$e = BroadcastingUtils::sendHTMLEmail("html email test", "html body here", "plain text here", ["onyx@gouws.co"]);

var_dump($e);

//email onyx!
//$e = BroadcastingUtils::sendEmailHTMLUser(1471, $dbConn, "body goes here...", "custom subject line", ["custom@kwelangasolutions.co.za"]);
//$e = BroadcastingUtils::sendEmailHTMLUser(1471, $dbConn, "body goes here...", "custom subject line #2", ["addr" => "custom2@kwelangasolutions.co.za", "alias" => "name here"]);

$e = BroadcastingUtils::sendEmailWithAttachment("test Subj", "test Body", "test plain body", array("onyx@gouws.co"), "kwelanga_php/libs/67717_2.jpg", $from = array());
var_dump($e);


//var_dump(BroadcastingUtils::sendEmailHTMLUserEmbedded(1471, $dbConn, 'test subject body <img src="images/KWELANGA-LARGE-dark-grey.png" WIDTH="200" HEIGHT="100" > <HR> <img src="https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelangaweb/images/KWELANGA-LARGE-dark-grey.png" WIDTH="200" HEIGHT="100">', "subject line!" ));



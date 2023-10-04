<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/ServerConstants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');


//print_r(BroadcastingUtils::sendEmailHTMLEmbedded("test Subj",array("onyx@gouws.co"), "Test Body", "Plain Body", $from = array()));


$st = microtime(true);

print_r(BroadcastingUtils::sendEmailWithAttachment("test Subj (Current)", "test Body", "test plain body", array("onyx@gouws.co"), "kwelanga_php/libs/67717_2.jpg", $from = array()));

$et = microtime(true);

echo "sendEmailWithAttachment: old smtp way" . ($et-$st);


$st = microtime(true);

print_r(BroadcastingUtils::sendEmailWithAttachmentWithAPI("test Subj (API)", "test Body", "test plain body", array("onyx@gouws.co"), "kwelanga_php/libs/67717_2.jpg", $from = array()));

$et = microtime(true);

echo "sendEmailWithAttachment: new api sendgrid way" . ($et-$st);
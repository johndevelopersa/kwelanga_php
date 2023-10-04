<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once ("BulkSMS_v2.php");

$rTO = BulkSMS_v2::sendSMS("0847675339", "test", 11); // number is formatted and sanitised to international format within this method class

print_r($rTO);

/*
 * returns :
 * 
 * ErrorTO Object ( [type] => S [description] => Successfully sent SMS [identifier] => [identifier2] => [object] => [sql] => )
 */
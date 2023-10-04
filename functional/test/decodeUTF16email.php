<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once $ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php";
require_once $ROOT . $PHPFOLDER . "properties/Constants.php";
require_once $ROOT . $PHPFOLDER . 'TO/ErrorTO.php';
require_once $ROOT . $PHPFOLDER . "libs/BroadcastingUtils.php";
require_once $ROOT . $PHPFOLDER . 'libs/pop3.php';
require_once $ROOT . $PHPFOLDER . 'libs/emailParser.php';
require_once $ROOT . $PHPFOLDER . 'DAO/ImportDAO.php';


$emlFile = '../../../archives/email/orders/junk/2021/10/Cognos.Reporting@clover.co.za_20211026145916_617817c494aa9.eml';

$data = file_get_contents($emlFile);

$parseProcess = new emailParser();
$result = $parseProcess->parseRawEmailString($data);  //returns TO and array of parsed email.

var_dump($result);

print_r($result->object['Type']);
print_r($result->object['Encoding']);
print_r($result->object['Data']);

if($result->object['Encoding'] != "UTF-8"){
	$conv = iconv($in_charset = $result->object['Encoding'], $out_charset = 'UTF-8' , $result->object['Data']);
	if ($conv !== false){
		print_r($conv);
	}


}

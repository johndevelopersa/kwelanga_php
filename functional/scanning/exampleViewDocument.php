<?php


//TODO: add security includes here


include('ROOT.php'); include($ROOT.'PHPINI.php');
require_once $ROOT.$PHPFOLDER.'properties/Constants.php' ;
require_once $ROOT.$PHPFOLDER.'libs/storage/Storage.php';
require_once $ROOT.$PHPFOLDER.'DAO/ScansDAO.php';
require_once $ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php';

/*------------------------------------------------*/

set_time_limit(15*60); // 15 mins
error_reporting(-1);
ini_set('display_errors', 1);

//DB conn
if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();  
}

$docsArr = (new ScansDAO($dbConn))->getDocumentScanForPrincipalDocumentNo($_GET['principal'], $_GET['docno']);


foreach($docsArr as $doc){		
	$publicUrl = Storage::getPublicURL(STORAGE_DOMAIN, $doc['storage_path']);	
	echo '<a href="'.$publicUrl.'">FILE: '.basename($doc['storage_path']) . ' ('.$doc['file_size_bytes'].' bytes)'.'</a><br>';
}


echo '<hr><pre>';
var_dump($docsArr);
echo '</pre>';

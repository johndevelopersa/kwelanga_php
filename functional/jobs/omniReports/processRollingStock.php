<?php

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php";
require_once $ROOT . $PHPFOLDER . 'properties/Constants.php';
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/OmniExtractDAO.php");

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    $errorTO = new ErrorTO;
}


foreach($reportArr as $sRow) {
     foreach($sRow as $prodrow) {
     	
     	   $whCode    = $prodrow['warehouse_code'];
     	   $stckCode  = $prodrow['stock_code'];
     	   $level     = $prodrow['level'];
     	   $avail     = $prodrow['available'];
     	
     	   $extractStock = new OmniExtractDAO($dbConn);
     	   $errorTO = $extractStock->updateGDStock($prinUid, $whCode, $stckCode, $level, $avail);
     	   
     	   if($errorTO->type == FLAG_ERRORTO_ERROR) {
     	   	     echo "<br>Failed to save Stock Record - " . $whCode . " - " . $stckCode . "<br>";
     	   } else {
     	         echo "<br>" . $errorTO->description ." - " . $whCode . " - " . $stckCode . "<br>"; 	
     	   }
     }
}

echo "<br>Completed ..<br>";
echo "[***EOS***]";

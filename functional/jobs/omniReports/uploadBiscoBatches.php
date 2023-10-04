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

$readyBatchList = new OmniExtractDAO($dbConn);
$errorTO = $readyBatchList->prepareBatchlist($prinUid, $depotUid);

if($errorTO->type!=FLAG_ERRORTO_SUCCESS) {
     echo "<br>";
     echo "Clear Old Batches Failed";
     echo "<br>";
     die();
 }

foreach($reportArr as $sRow) {
     foreach($sRow as $prodrow) {
      
         $startDash = strpos($prodrow['batch_no'],'-',0);
         $firstDash = strpos($prodrow['batch_no'],'-',3);
         $secondDash = strpos($prodrow['batch_no'],'-',$firstDash+1);

         $yy = str_pad(trim(substr($prodrow['batch_no'], $secondDash + 1 ,2)),4,"20",STR_PAD_LEFT);
         $mm = str_pad(trim(substr($prodrow['batch_no'], $firstDash + 1 ,$secondDash - $firstDash - 1)),2,"0",STR_PAD_LEFT);
         $dd = str_pad(trim(substr($prodrow['batch_no'], $startDash + 1 ,$firstDash - $startDash - 1)),2,"0",STR_PAD_LEFT);
	  	
         $insertBatches = new OmniExtractDAO($dbConn);
         $errorTO = $insertBatches->insertProductBatches($prinUid,
                                                         $depotUid,
                                                         $prodrow['stock_code'],
                                                         $prodrow['batch_no'],
                                                         $yy . $mm . $dd,
                                                         $prodrow['level']);
               
         if($errorTO->type!=FLAG_ERRORTO_SUCCESS) {
             echo "<br>";
             echo "Clear Old Batches Failed";
             echo "<br>";
            die();
          
	       }
	       echo "<br>";
	       echo "Inserted - " .$prodrow['stock_code'];
     }
}

echo "<br>Completed ..<br>";
echo "[***EOS***]";

<?php

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php";
require_once $ROOT . $PHPFOLDER . 'properties/Constants.php';
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/OmniExtractDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/MaintenanceDAO.php");

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    $errorTO = new ErrorTO;
}

foreach($reportArr as $sRow) {
    foreach($sRow as $iRow) {
        $checkUpdateInv = new OmniExtractDAO($dbConn);
        $invList = $checkUpdateInv->checkUpdateStatus($prinUid, $iRow['order_number']);
        
        if(count($invList) > 0) {
                   $readyUpdateInv = new OmniExtractDAO($dbConn);
                   $errorTO = $readyUpdateInv->updateOmniInvoiceStatus($prinUid,
                                                                       $iRow['document_date'],
                                                                       $iRow['order_number'],
                                                                       $iRow['reference'],
                                                                       $iRow['quantity'],
                                                                       $iRow['value_excl_after_discount'],
                                                                       $iRow['line_number'],
                                                                       $iRow['stock_code']);
                   if($errorTO->type == FLAG_ERRORTO_SUCCESS ) {
                   	
                   	   // Check Totals
                   	   $detAdj = new MaintenanceDAO($dbConn);
                   	   $errorTO = $detAdj->detailRecordAdjust($prinUid,$iRow['order_number']);
                   	
                   	   if($errorTO->type == FLAG_ERRORTO_SUCCESS ) {

                             $detAdj = new MaintenanceDAO($dbConn);
                             $errorTO = $detAdj->checkHeaderTotals($prinUid,'', $iRow['order_number'] );
                             
                             if($errorTO->type != FLAG_ERRORTO_SUCCESS ) {
                                  echo $iRow['order_number'] . " Header Update Bombed Out!!..<br>"; 
                             }
                   	   	
                   	   } else {
                   	   	     echo $iRow['order_number'] . " Detail Update Bombed Out!!..<br>";
                   	   }
                   	
                        echo $iRow['order_number'] . " Updated Successfully<br>";
                   } else {
                        echo $iRow['order_number'] . " Update Bombed Out!!..<br>";
                   }                                                    
        } else {
              echo "No order to update found!!..<br>";
        }
    }    
}
echo "<br>Completed ..<br>";
echo "[***EOS***]";

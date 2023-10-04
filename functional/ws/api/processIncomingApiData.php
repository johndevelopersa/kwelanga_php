<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "properties/Constants.php");
include_once($ROOT . $PHPFOLDER . "properties/ServerConstants.php");
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'DAO/ApiDAO.php');
include_once("processIncomingApiClass.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

$data = file_get_contents('php://input');

$JSON = json_decode($data, true);

$newApiDAO = new ApiDAO($dbConn);
$uCred = $newApiDAO->getVendorUser($JSON['username']);

$uCredStr = trim($uCred[0]['username']) . trim($uCred[0]['password']);
$upayLoadStr = trim($JSON['username']) . trim($JSON['password']);

if (strcmp($uCredStr, $upayLoadStr) !== 0) {  // make sure you setup a password specifically for each client individually
     echo json_encode( [
    "resultStatus"=>"E",
    "ResultCode"    =>'701' ,
    "resultMessage"=>"Sorry, incorrect API credentials supplied"
    ] );
    exit; // !! NB !!

} else {


        if(trim($JSON['requireddata']) == 'getProduct') {

              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->getProduct(trim($JSON['requireddata']),
                                                      trim($uCred[0]['pv_uid']),
                                                      trim($JSON['principalUid']),
                                                      trim($JSON['userEmail']));

              echo $returnResult;

              exit; // !! NB !!

        } elseif(trim($JSON['requireddata']) == 'getUserStore') {

              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->getUserStore(trim($JSON['requireddata']),
                                                       trim($uCred[0]['pv_uid']),
                                                       trim($JSON['principalUid']),
                                                       trim($JSON['userEmail']));

              echo $returnResult;

              exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'getPrincipalRepList') {
              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->getPrincipalReps(trim($JSON['requireddata']),
                                                            trim($uCred[0]['pv_uid']),
                                                            trim($JSON['principalUid']),
                                                            trim($JSON['userEmail']));

              echo $returnResult;
              exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'getAllPriceProducts') {

              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->getAllPriceProducts(trim($JSON['requireddata']),
                                                               trim($uCred[0]['pv_uid']),
                                                               trim($JSON['principalUid']),
                                                               trim($JSON['userEmail']));

              echo $returnResult;

              exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'getProductPrice') {

              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->getPriceProduct(trim($JSON['requireddata']),
                                                           trim($uCred[0]['pv_uid']),
                                                           trim($JSON['principalUid']),
                                                           trim($JSON['userEmail']),
                                                           trim($JSON['storeGroup']),
                                                           trim($JSON['groupName']),
                                                           trim($JSON['prodCode']),
                                                           trim($JSON['productDesc'])
                                                           );

              echo $returnResult;

              exit; // !! NB !!

        } elseif(trim($JSON['requireddata']) == 'postKosOrder') {
              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->postKosOrder(trim($uCred[0]['pv_uid']), $data);

              echo $returnResult;

              exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'postNewOrder') {
                $processApi   = new processIncomingApiClass();
                $returnResult = $processApi->postNewOrder($data);

                echo $returnResult;

                exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'postDocumentConfirm') {
              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->postDocumentConfirm($data);

              echo $returnResult;
              exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'postNewPrices') {
              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->postNewPrices($data);

              echo $returnResult;
              exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'postCreditLimits') {
        } elseif(trim($JSON['requireddata']) == 'updatePickStatus') {
        } elseif(trim($JSON['requireddata']) == 'postStockLevels') {
                $processApi   = new processIncomingApiClass();
                $returnResult = $processApi->postStockLevels($data);

                echo $returnResult;

                exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'getStockLevels') {

        	    if(!isset($JSON['wareHouseCode']) || $JSON['wareHouseCode'] == '' ) {
        	         $selWareHouse = '';
        	    } else {
        	         $selWareHouse = $JSON['wareHouseCode'];
        	    }

              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->getStockLevels(trim($JSON['requireddata']),
                                                          trim($uCred[0]['pv_uid']),
                                                          trim($JSON['principalUid']),
                                                          trim($JSON['userEmail']),
                                                          trim($selWareHouse));

              echo $returnResult;
              exit; // !! NB !!


        } elseif(trim($JSON['requireddata']) == 'getInvoiceImports') {
              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->getInvoiceImports(trim($JSON['requireddata']),
                                                             trim($uCred[0]['pv_uid']),
                                                             trim($JSON['principalUid']),
                                                             trim($JSON['userEmail']));

              echo $returnResult;
              exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'getCreditNoteImports') {
              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->getInvoiceImports(trim($JSON['requireddata']),
                                                             trim($uCred[0]['pv_uid']),
                                                             trim($JSON['principalUid']),
                                                             trim($JSON['userEmail']));

              echo $returnResult;
              exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'uploadCustomerfile') {
              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->updateCustomerfile($data);

              echo $returnResult;
              exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'getOrderStatusUpdate') {
              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->getInvoiceImports(trim($JSON['requireddata']),
                                                             trim($uCred[0]['pv_uid']),
                                                             trim($JSON['principalUid']),
                                                             trim($JSON['userEmail']));

              echo $returnResult;
              exit; // !! NB !!
        } elseif(trim($JSON['requireddata']) == 'getCompleteStoreList') {
              $processApi   = new processIncomingApiClass();
              $returnResult = $processApi->getCompleteStoreList(trim($JSON['requireddata']),
                                                                trim($uCred[0]['pv_uid']),
                                                                trim($JSON['principalUid']),
                                                                trim($JSON['userEmail']));

              echo $returnResult;
              exit; // !! NB !!
        } else {
        	
        	print_r($data);
             $newApiDAO = new APIDAO($dbConn);
                            $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']),
                                                                         'E',
                                                                         trim($JSON['requireddata']),
                                                                         '700');
        // ************************* End of Required Data ************************* 

                echo json_encode( [
                                   "resultStatus" =>'E',
                                   "ResultCode"    =>'700' ,
                                   "resultMessage"=>"Sorry, Request is not recognised - Oh Shit"
                                  ] );

                exit; // !! NB !!
           }
}

 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);

  return $data;
 }
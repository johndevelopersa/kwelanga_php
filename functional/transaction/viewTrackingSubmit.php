<?php
/*
 *
 * THIS LAYER PROVIDES BASIC SCREENING AND CONVERTS INPUT INTO PROPER TO BEFORE CALL IS MADE TO PROCESS
 *
 */
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostMiscellaneousDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingSpecialFieldTO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingDepotAmendDocumentDetailTO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingDocumentStatusTO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingDocumentDebriefTO.php");
include_once($ROOT.$PHPFOLDER.'TO/PostingOrderTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrderDetailTO.php');


if (!isset($_SESSION)) session_start;
$principalId = $_SESSION['principal_id'];
// $principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$principalType = $_SESSION['principal_type'];
$userId = $_SESSION['user_id'];
$skipInPickStage = ((isset($_SESSION['skip_inpick_stage']))?$_SESSION['skip_inpick_stage']:"N");

$errorTO = new ErrorTO; // used for ValidationCommonUtils for passback
$result = new ErrorTO;
$dbConn = new dbConnect();
$dbConn->dbConnection();


//preset POST vars BEFORE calling setPostVars
$postACTIONTYPE = "";
$postDOCMASTID = "";
$postCOMMENT = "";
$postREPCODE = 0;
$postTRACKINGNUMBER =  date('Y-m-d');
$postOVERIDEDATE = date('Y-m-d');
$postDDUID = "";
$postAMENDEDQTY = "";
$postALLOWDECIMAL = "";
$postBATCH = "";
$postACCEPTQTY =  "";
$postBULKACTION = false;
$postREASONCODE = "";
$postDOCMASTARR = array();
$postDELDATE = '';
$postGRVNO = '';
$postWAYBILLNO = '';
// $postPAYMENTTYPE = '';
// $postPAYMENTAMOUNT = '';
$postCLAIMNO = '';

// file_put_contents($ROOT.$PHPFOLDER.'log/debug1112.txt', print_r($_POST, TRUE), FILE_APPEND); 

CommonUtils::setPostVars(); /** NEW SIMPLE POST BUILDER **/

$postingDocumentStatusTO = new PostingDocumentStatusTO();
$postTransactionDAO = new PostTransactionDAO($dbConn);
$transactionDAO = new TransactionDAO($dbConn);
$postStockDAO = new PostStockDAO($dbConn);
$adminDAO = new AdministrationDAO($dbConn);

/************************************************************
 * WARNING !
 * This script handles both depot and principal requests.
 * Depot operations MUST be validated to only occur against
 * a depot WMS order (moved to validation - within the post method)
 ************************************************************/

if(CommonUtils::isDepotUser() && in_array($postACTIONTYPE,array('ACCEPT', 'INPICK', 'CANCEL', 'WAITING_DISPATCH', 'INVOICE', 'DELFULL', 'DELPART'))){ //accept type does not use the collected data here-within.

  if(!$postBULKACTION){//dont get this information if running in bulk action mode....

    $mfT = $transactionDAO->getUserDepotDocumentDetails($postDOCMASTID, $userId); // this also checks if user has access to principal
    if(count($mfT)==0){
      $errorTO->type=FLAG_ERRORTO_ERROR;
      $errorTO->description="You do not have access to this information, or order does not exist. (s)";
      echo CommonUtils::getJavaScriptMsg($errorTO);
      return;
    }
    $initialDST = ((in_array($mfT[0]["document_status_uid"],explode(",",$transactionDAO->getUnacceptedOrderStatuses())))?DST_UNACCEPTED:$mfT[0]["document_status_uid"]); // might get modified later so store initial value
  }

} else if(!CommonUtils::isDepotUser() && in_array($postACTIONTYPE,array('ACCEPT', 'AMEND', 'CANCEL', 'IN-PROGRESS', 'JOB-COMPLETE', 'INVOICE', 'DELFULL', 'DELPART' ))) { //accept type does not use the collected data here-within.

    // should ONLY enter in here for QUOTATION management by principal

    $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_MANAGE_QUOTATION);
    if (!$hasRole) {
      $errorTO->type=FLAG_ERRORTO_ERROR;
      $errorTO->description="You do not have permissions to manage quotations";
      echo CommonUtils::getJavaScriptMsg($errorTO);
      return;
    }

    $mfT = $transactionDAO->getDocumentWithDetailItem($userId, $principalId, $postDOCMASTID); // this also checks if user has access to principal
    if(count($mfT)==0){
      $errorTO->type=FLAG_ERRORTO_ERROR;
      $errorTO->description="You do not have access to this information, or document does not exist. (s)";
      echo CommonUtils::getJavaScriptMsg($errorTO);
      return;
    }
    $initialDST = ((in_array($mfT[0]["document_status_uid"],explode(",",$transactionDAO->getUnacceptedOrderStatuses())))?DST_UNACCEPTED:$mfT[0]["document_status_uid"]); // might get modified later so store initial value

    if (!in_array($mfT[0]["document_type_uid"],array(DT_QUOTATION,DT_PURCHASE_ORDER))) {
      $errorTO->type=FLAG_ERRORTO_ERROR;
      $errorTO->description="ERROR - Only Quotations can be managed by Principals";
      echo CommonUtils::getJavaScriptMsg($errorTO);
      return;
    }

}


// apply the action
switch ($postACTIONTYPE) {

    case "ACCEPT" : {

      if(!CommonUtils::isDepotUser()) {

        //SINGLE UPDATE
        $postingDocumentStatusTO->skipValidation = "Y"; // skip the depot stuff
        $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
        $postingDocumentStatusTO->documentStatusUId = DST_ACCEPTED;
        $postingDocumentStatusTO->comment = "";
        $postingDocumentStatusTO->repcode = 0;
        $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];
        $result = $postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
        $errorTO->identifier = $result->identifier;

      } else {


        if($postBULKACTION && count($postDOCMASTARR)==0){
          $result->type = FLAG_ERRORTO_ERROR;
          $result->description = 'Error - Invalid Bulk Action, no document list passed (' . $postACTIONTYPE . ')';
        } else if($postBULKACTION && count($postDOCMASTARR)>=1){

          //BULK UPDATE
          $result->type = FLAG_ERRORTO_SUCCESS;
          foreach($postDOCMASTARR as $postDOCMASTID){
            $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
            $postingDocumentStatusTO->documentStatusUId = DST_ACCEPTED;
            $postingDocumentStatusTO->comment = "";
            $postingDocumentStatusTO->repcode = 0;
//            $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];
            $resultItem=$postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
            $errorTO->identifier=$resultItem->identifier;
            if($resultItem->type!=FLAG_ERRORTO_SUCCESS){
              $result->type = $resultItem->type;
              $result->description = $resultItem->description;
              break; //STOP!!!
            }
          }
        } else {

          //SINGLE UPDATE
          $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
          $postingDocumentStatusTO->documentStatusUId = DST_ACCEPTED;
          $postingDocumentStatusTO->comment = "";
          $postingDocumentStatusTO->repcode = 0;
          $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];          
          $result = $postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
          $errorTO->identifier = $result->identifier;

          //IN-PICK TO ACCEPT, REVERSE STOCK.
          if($mfT[0]['document_status_uid'] == DST_INPICK && $mfT[0]['document_type_uid'] != DT_UPLIFTS){

            if ($skipInPickStage=="Y") {
              echo "WARNING: Should NEVER HAVE been on INPICK as this depot does not use inpick stage";
            }

            //var_dump($mfT);
            //return;
            foreach ($mfT as $r) {
              $result = $postStockDAO->updateStockInPick($r["principal_uid"],
                                            $r["depot_uid"],
                                            $r["product_uid"],
                                            $r["ordered_qty"], // !! NOT DOCUMENT QTY !!
                                            $reverseDirection=true);
              if ($result->type!=FLAG_ERRORTO_SUCCESS) break; // must exit both so dont put anything after the loop !
            }
          }


        }

      }

      break;
    }

// ******************************************
    case "UNACCEPTED" : {

        //SINGLE UPDATE
        $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
        $postingDocumentStatusTO->documentStatusUId = DST_UNACCEPTED;
        $postingDocumentStatusTO->comment = "";
        $postingDocumentStatusTO->repcode = 0;
        $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];          
        $result = $postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
        $errorTO->identifier = $result->identifier;

      break;
    }
// ******************************************

    case "INPICK" : {

      if ($skipInPickStage=="Y") {
        $errorTO->type = FLAG_ERRORTO_ERROR;
        $errorTO->description = 'Error - This depot does not use the inpick status';
        echo CommonUtils::getJavaScriptMsg($errorTO);
        return;
      }

      if($postBULKACTION && count($postDOCMASTARR)==0){
        $result->type = FLAG_ERRORTO_ERROR;
        $result->description = 'Error - Invalid Bulk Action, no document list passed (' . $postACTIONTYPE . ')';
      } else if($postBULKACTION && count($postDOCMASTARR)>=1){

        //BULK UPDATE
        $result->type = FLAG_ERRORTO_SUCCESS;
        foreach($postDOCMASTARR as $postDOCMASTID){

          //must be called before setDocumentStatus
          $mfT = $transactionDAO->getUserDepotDocumentDetails($postDOCMASTID, $userId);  // this also checks if user has access to principal

          if(count($mfT)==0){
            $errorTO->type=FLAG_ERRORTO_ERROR;
            $errorTO->description="You do not have access to this information, or order does not exist.";
            echo CommonUtils::getJavaScriptMsg($errorTO);
            return;
          }
          $initialDST = ((in_array($mfT[0]["document_status_uid"],explode(",",$transactionDAO->getUnacceptedOrderStatuses())))?DST_UNACCEPTED:$mfT[0]["document_status_uid"]); // might get modified later so store initial value

          $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
          $postingDocumentStatusTO->documentStatusUId = DST_INPICK;
          $postingDocumentStatusTO->comment = "";
          $postingDocumentStatusTO->repcode = 0 ;
          $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];
          $result = $postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
          $errorTO->identifier=$result->identifier;
          if($result->type!=FLAG_ERRORTO_SUCCESS){
            break; //STOP!!!
          }


        if($mfT[0]['document_type_uid'] != DT_UPLIFTS){

            // update stock
            foreach ($mfT as $r) {
            	
            	if ($initialDST==DST_ACCEPTED) {
                $result=$postStockDAO->updateStockInPick($r["principal_uid"],
                                                          $r["depot_uid"],
                                                          $r["product_uid"],
                                                          $r["ordered_qty"], // !! NOT DOCUMENT QTY !!
                                                          $reverseDirection=false);
              } else if ($initialDST==DST_INVOICED) {
                // calling of invoiced+reverse puts back into inpick
                $result=$postStockDAO->updateStockInvoiced($r["principal_uid"],
                                                            $r["depot_uid"],
                                                            $r["product_uid"],
                                                            $r["ordered_qty"],
                                                            $r["document_qty"],
                                                            $reverseDirection=true,
                                                            $skipInPickStage=$skipInPickStage,
                                                            $postDOCMASTID);

                if ($result->type!=FLAG_ERRORTO_SUCCESS) break;

                // reset dd & recalc header
                $result=$postTransactionDAO->setDocumentDetailAmendedReverse($postDOCMASTID);

              } else {
                $result->type = FLAG_ERRORTO_ERROR;
                $result->description = "Unknown originating Document Status - not catered for! (" . $postACTIONTYPE . ')';
                break;
              }

              if ($result->type!=FLAG_ERRORTO_SUCCESS) break; // must exit both so dont put anything after the loop !
            }

          }
        }


      } else {

        //SINGLE UPDATE
        $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
        $postingDocumentStatusTO->documentStatusUId = DST_INPICK;
        $postingDocumentStatusTO->comment = $postCOMMENT;
        $postingDocumentStatusTO->repcode =0;
        $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];
        $result=$postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
        $errorTO->identifier=$result->identifier;
        if ($result->type!=FLAG_ERRORTO_SUCCESS) break; //STOP!!!


        if($mfT[0]['document_type_uid'] != DT_UPLIFTS){

          // update stock
          foreach ($mfT as $r) { 
          	
            if ($initialDST==DST_ACCEPTED) {
              $result=$postStockDAO->updateStockInPick($r["principal_uid"],
                                                        $r["depot_uid"],
                                                        $r["product_uid"],
                                                        $r["ordered_qty"], // !! NOT DOCUMENT QTY !!
                                                        $reverseDirection=false);
            } else if ($initialDST==DST_INVOICED) {
              // calling of invoiced+reverse puts back into inpick
              $result=$postStockDAO->updateStockInvoiced($r["principal_uid"],
                                                          $r["depot_uid"],
                                                          $r["product_uid"],
                                                          $r["ordered_qty"],
                                                          $r["document_qty"],
                                                          $reverseDirection=true,
                                                          $skipInPickStage=$skipInPickStage,
                                                          $postDOCMASTID);

              if ($result->type!=FLAG_ERRORTO_SUCCESS) break;

              // reset dd & recalc header
              $result=$postTransactionDAO->setDocumentDetailAmendedReverse($postDOCMASTID);

            } else {
              $result->type = FLAG_ERRORTO_ERROR;
              $result->description = "Unknown originating Document Status - not catered for! (" . $postDOCMASTID . ' : ' . $initialDST . ' - ' . $postACTIONTYPE . ')';
              break;
            }

            if ($result->type!=FLAG_ERRORTO_SUCCESS) break; // must exit both so dont put anything after the loop !
          }

        }

      }

      break;
    }

    case "CANCEL": {

      if(!CommonUtils::isDepotUser()) {

        if (!in_array($initialDST,array(DST_ACCEPTED,DST_UNACCEPTED, DST_IN_PROGRESS, DST_JOB_COMPLETE))) {
          $result->type = FLAG_ERRORTO_ERROR;
          $result->description = "Current Status is not eligible for cancellation";
          break;
        }

        $postingDocumentStatusTO->skipValidation = "Y";
        $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];
        $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
        $postingDocumentStatusTO->documentStatusUId = DST_CANCELLED;
        $postingDocumentStatusTO->repcode =0;
        $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];  
        $result=$postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
        $errorTO->identifier=$result->identifier;
        if ($result->type!=FLAG_ERRORTO_SUCCESS) break;

      } else {

        if(empty($postREASONCODE)){
          $result->type = FLAG_ERRORTO_ERROR;
          $result->description = "Please specify a reason for cancellation!";
          break;
        }

        if($postBULKACTION && count($postDOCMASTARR)==0){
          $result->type = FLAG_ERRORTO_ERROR;
          $result->description = 'Error - Invalid Bulk Action, no document list passed (' . $postACTIONTYPE . ')';
        } else if($postBULKACTION && count($postDOCMASTARR)>=1){

          //BULK UPDATE
          $result->type = FLAG_ERRORTO_SUCCESS;
          foreach($postDOCMASTARR as $postDOCMASTID){

            //must be called before setDocumentStatus
            $mfT = $transactionDAO->getUserDepotDocumentDetails($postDOCMASTID, $userId);  // this also checks if user has access to principal
            if(count($mfT)==0){
              $errorTO->type=FLAG_ERRORTO_ERROR;
              $errorTO->description="You do not have access to this information, or order does not exist.";
              echo CommonUtils::getJavaScriptMsg($errorTO);
              return;
            }
            $initialDST = ((in_array($mfT[0]["document_status_uid"],explode(",",$transactionDAO->getUnacceptedOrderStatuses())))?DST_UNACCEPTED:$mfT[0]["document_status_uid"]); // might get modified later so store initial value

            $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
            $postingDocumentStatusTO->documentStatusUId = DST_CANCELLED;
            $postingDocumentStatusTO->comment = $postCOMMENT;
            $postingDocumentStatusTO->repcode =0; 
            $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no']; 
            $resultItem=$postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
            $errorTO->identifier=$resultItem->identifier;
            if($resultItem->type!=FLAG_ERRORTO_SUCCESS){
              $result->type = $resultItem->type;
              $result->description = $resultItem->description;
              break; //STOP!!!
            }

            // override document details
            $result = $postTransactionDAO->setDocumentDetailCancelled($postDOCMASTID);
            if ($result->type!=FLAG_ERRORTO_SUCCESS) break;

            $result = $postTransactionDAO->setDocumentReason($postDOCMASTID, $postREASONCODE);
            if ($result->type!=FLAG_ERRORTO_SUCCESS) break;


            if($mfT[0]['document_type_uid'] != DT_UPLIFTS){
              // update stock
              foreach ($mfT as $r) {
                $result=$postStockDAO->updateStockCancelled($r["principal_uid"],
                                                            $r["depot_uid"],
                                                            $r["product_uid"],
                                                            $r["ordered_qty"],
                                                            $r["document_qty"],
                                                            $initialDST,
                                                            $skipInPickStage=$skipInPickStage);

                if ($result->type!=FLAG_ERRORTO_SUCCESS) break; // must exit both so dont put anything after the loop !
              }
            }


          }
        } else {

          //SINGLE UPDATE
          $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
          $postingDocumentStatusTO->documentStatusUId = DST_CANCELLED;
          $postingDocumentStatusTO->comment = $postCOMMENT;
          $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];
          $result=$postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
          $errorTO->identifier=$result->identifier;
          if ($result->type!=FLAG_ERRORTO_SUCCESS) break;

          // override document details
          $result = $postTransactionDAO->setDocumentDetailCancelled($postDOCMASTID);
          if ($result->type!=FLAG_ERRORTO_SUCCESS) break;

          $result = $postTransactionDAO->setDocumentReason($postDOCMASTID, $postREASONCODE);
          if ($result->type!=FLAG_ERRORTO_SUCCESS) break;


          if($mfT[0]['document_type_uid'] != DT_UPLIFTS){
            // update stock
            foreach ($mfT as $r) {
                    $result=$postStockDAO->updateStockCancelled($r["principal_uid"],
                                                                $r["depot_uid"],
                                                                $r["product_uid"],
                                                                $r["ordered_qty"],
                                                                $r["document_qty"],
                                                                $initialDST,
                                                                $skipInPickStage = $skipInPickStage);

                    if ($result->type!=FLAG_ERRORTO_SUCCESS) break; // must exit both so dont put anything after the loop !
            }
          }


        }

      }

      break;
    }

    case "IN-PROGRESS": {

      if(!CommonUtils::isDepotUser()) {

        if (!in_array($initialDST,array(DST_ACCEPTED))) {
          $result->type = FLAG_ERRORTO_ERROR;
          $result->description = "Current Status is not eligible for In-Progress";
          break;
        }

        $postingDocumentStatusTO->skipValidation = "Y";
        $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
        $postingDocumentStatusTO->documentStatusUId = DST_IN_PROGRESS;
        $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];
        $result=$postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
        $errorTO->identifier=$result->identifier;

        if ($result->type!=FLAG_ERRORTO_SUCCESS) break;

      }

      break;

    }

    case "JOB-COMPLETE": {

      if(!CommonUtils::isDepotUser()) {

        if (!in_array($initialDST,array(DST_IN_PROGRESS))) {
          $result->type = FLAG_ERRORTO_ERROR;
          $result->description = "Current Status is not eligible for Job-Completion";
          break;
        }

        $postingDocumentStatusTO->skipValidation = "Y";
        $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
        $postingDocumentStatusTO->documentStatusUId = DST_JOB_COMPLETE;
        $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];
        $result=$postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
        $errorTO->identifier=$result->identifier;

        if ($result->type!=FLAG_ERRORTO_SUCCESS) break;

      }

      break;

    }

    case "INVOICE": {
    	      
            //change qty
            $postingDepotAmendDocumentDetailTO=new PostingDepotAmendDocumentDetailTO();
            $postingDepotAmendDocumentDetailTO->dmUId=$postDOCMASTID;
            $postingDepotAmendDocumentDetailTO->ddUIdArr=explode(",",$postDDUID);
            $postingDepotAmendDocumentDetailTO->amendedQtyArr=explode(",",$postAMENDEDQTY);
            $postingDepotAmendDocumentDetailTO->batchArr=explode(",",$postBATCH);
            $postingDepotAmendDocumentDetailTO->acceptQty=$postACCEPTQTY; // user accepts quantities
            $postingDepotAmendDocumentDetailTO->allowDecimal==explode(",",$postALLOWDECIMAL);
            $postingDepotAmendDocumentDetailTO->documentTypeUId = $mfT[0]['document_type_uid'];
            $postingDepotAmendDocumentDetailTO->allowDecimal=explode(",",$postALLOWDECIMAL);

    	      //file_put_contents('var3.txt', print_r($postingDepotAmendDocumentDetailTO->allowDecimal,TRUE), FILE_APPEND);
            $result = $postTransactionDAO->setDocumentDetailAmended($postingDepotAmendDocumentDetailTO);
            if ($result->type!=FLAG_ERRORTO_SUCCESS) break;



            if (CommonUtils::isDepotUser()) {
              //update stock
              $resultOK = true;
              if($mfT[0]['document_type_uid'] != DT_UPLIFTS){
                // update stock
                $i = 0;
                foreach ($mfT as $r) {
                    $result=$postStockDAO->updateStockInvoiced($r["principal_uid"],
                                                               $r["depot_uid"],
                                                               $r["product_uid"],
                                                               $r["ordered_qty"],
                                                               $postingDepotAmendDocumentDetailTO->amendedQtyArr[$i],
                                                               $reverseDirection=false,
                                                               $skipInPickStage=$skipInPickStage,
                                                               $mfT[0]['waiting_dispatch'],                                                               
                                                               $postDOCMASTID);
                    $i++;
                    if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                      $resultOK = false;
                      break; // must exit both so dont put anything after the loop !
                    }
                }
              }
              if (!$resultOK) break; // we have to in this section put this code after the loop so the break needs to be effected
            } else {
              $postingDocumentStatusTO->skipValidation = "Y";
            }
            
//            file_put_contents($ROOT.$PHPFOLDER.'log/viewtrsub.txt', print_r($mfT, TRUE), FILE_APPEND); 
            
            //update status and set invoice.
            //place this at the very last place as this increments the invoice number on each call... ??
            $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
            if($mfT[0]['waiting_dispatch'] == 'Y') {
                  $postingDocumentStatusTO->documentStatusUId = DST_WAITING_DISPATCH;
            } else {
                  $postingDocumentStatusTO->documentStatusUId = DST_INVOICED; 	
            }
            
            $postingDocumentStatusTO->comment = $postCOMMENT;
            $postingDocumentStatusTO->repcode = $postREPCODE ;
            $postingDocumentStatusTO->trackingnumber = $postTRACKINGNUMBER;
            $postingDocumentStatusTO->overideInvDate = $postTRACKINGNUMBER;
            $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];
            // next 4 fields only needed to use an invoice number sequence'
            $postingDocumentStatusTO->documentTypeUId = $mfT[0]['document_type_uid'];
            $postingDocumentStatusTO->principalUId  = $mfT[0]["principal_uid"];
            $postingDocumentStatusTO->depotUId = $mfT[0]["depot_uid"];
            $postingDocumentStatusTO->documentNumber = $mfT[0]["document_number"];
            $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];

            $result=$postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
            $errorTO->identifier=$result->identifier;
            if ($result->type!=FLAG_ERRORTO_SUCCESS) break;


            break;
    }

    case "DELFULL": {

            $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
            $postingDocumentStatusTO->documentStatusUId = DST_DELIVERED_POD_OK;
            $postingDocumentStatusTO->comment = $postCOMMENT;
            $postingDocumentStatusTO->repcode =0;
            $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no'];  
            $result = $postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
            $errorTO->identifier=$result->identifier;

            if ($result->type!=FLAG_ERRORTO_SUCCESS) break;

            //store waybill, grv and del date.
            $postingDocumentDebriefTO = new PostingDocumentDebriefTO();
            $postingDocumentDebriefTO->dmUId = $postDOCMASTID;
            $postingDocumentDebriefTO->deliveryDate = $postDELDATE;
            $postingDocumentDebriefTO->grvNumber = $postGRVNO;
            $postingDocumentDebriefTO->waybillNumber = $postWAYBILLNO;
//            $postingDocumentDebriefTO->paymentType = $postPAYMENTTYPE;
//            $postingDocumentDebriefTO->paymentAmount = $postPAYMENTAMOUNT;
            $postingDocumentDebriefTO->debriefComment = $postCOMMENT;

            $result = $postTransactionDAO->setDocumentDebrief($postingDocumentDebriefTO);
            
            if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                  $dbConn->dbQuery("rollback");
                  $errorTO->type=$result->type;
                  $errorTO->description="<Strong>Error updating document!</strong><BR><BR>".$result->description;
                  echo CommonUtils::getJavaScriptMsg($errorTO);
                  $dbConn->dbClose();
                  return;
            } else {
                  $dbConn->dbQuery("commit");
                  $errorTO->type=$result->type;
                  $errorTO->description=$result->description;
            }
            $dbConn->dbClose();

            $errorTO->description="Document Change(s) successfully saved";
//            if($postPAYMENTTYPE == '1'){
//                  $errorTO->description .= "<BR><BR><BR><a href=javascript:; onclick=window.open('functional/presentations/presentationManagement.php?TYPE=PAYMENT&DSTATUS=Processed&CSOURCE=P&FINDNUMBER=".$postDOCMASTID."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');>[VIEW/PRINT PAYMENT RECEIPT]</a>";
//            }
            echo CommonUtils::getJavaScriptMsg($errorTO);
            return;            
 
           break;
    }

    case "DELPART": {

            $postingDocumentStatusTO->documentMasterUId = $postDOCMASTID;
            $postingDocumentStatusTO->documentStatusUId = DST_DIRTY_POD;
            $postingDocumentStatusTO->comment = $postCOMMENT;
            $postingDocumentStatusTO->repcode =0; 
            $postingDocumentStatusTO->orderSequenceNo = $mfT[0]['order_sequence_no']; 
            $result = $postTransactionDAO->setDocumentStatus($postingDocumentStatusTO);
            $errorTO->identifier=$result->identifier;

            if ($result->type!=FLAG_ERRORTO_SUCCESS) break;
            
            if(empty($postREASONCODE)){
               $result->type = FLAG_ERRORTO_ERROR;
               $result->description = "Please specify a reason for cancellation!";
               break;
            }
            //store waybill, grv and del date.
            $postingDocumentDebriefTO = new PostingDocumentDebriefTO();
            $postingDocumentDebriefTO->dmUId = $postDOCMASTID;
            $postingDocumentDebriefTO->deliveryDate = $postDELDATE;
            $postingDocumentDebriefTO->grvNumber = $postGRVNO;
            $postingDocumentDebriefTO->waybillNumber = $postWAYBILLNO;
//            $postingDocumentDebriefTO->paymentType = $postPAYMENTTYPE;
//            $postingDocumentDebriefTO->paymentAmount = $postPAYMENTAMOUNT;
            $postingDocumentDebriefTO->debriefComment = $postCOMMENT;
            $postingDocumentDebriefTO->podreasonuid = $postREASONCODE;
            $postingDocumentDebriefTO->updateDeliveredQty = 'Y';
            $postingDocumentDebriefTO->ddUIdArr = explode(",",$postDDUID);
            $postingDocumentDebriefTO->amendedQtyArr = explode(",",$postAMENDEDQTY);
            $postingDocumentDebriefTO->acceptQty=$postACCEPTQTY;
            
            $result = $postTransactionDAO->setDocumentDebrief($postingDocumentDebriefTO); //ADJUST DEL QTY + UPDATE + validation that doc qty -> del qty differs.

            if ($result->type!=FLAG_ERRORTO_SUCCESS) 
             break;
            // update stock
            if (CommonUtils::isDepotUser()) {
                $i = 0;
                $resultOK = true;
                foreach ($mfT as $r) {
                   $result=$postStockDAO->updateStockReturnCancel($r["principal_uid"],
                                                                  $r["depot_uid"],
                                                                  $r["product_uid"],
                                                                  abs($r['document_qty']) - abs($postingDocumentDebriefTO->amendedQtyArr[$i]));
                    $i++;
                  if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                     $resultOK = false;
                     break; // must exit both so dont put anything after the loop !
                  }
                }

                if (!$resultOK) break; // the below had to come after the loop after all ... so do this
            }

            //CREATE CREDIT DOCUMENT - USING EXISTING DOCUMENT
            //FIELD USED FROM SCREEN
            // - CLAIM NO
            // - AMENDED QTY - DOCUMENT QTY
            $postingOrderTO = new PostingOrderTO;
            $postingOrderTO->DMLType = "INSERT";
            $postingOrderTO->storeChainUId = $mfT[0]['principal_store_uid'];
            $postingOrderTO->principalUId = $mfT[0]['principal_uid'];
            $postingOrderTO->orderNumber = $mfT[0]['customer_order_number'];
            $postingOrderTO->orderSequenceNo = ""; // is not assigned as part of posting
            $postingOrderTO->deliveryInstructions = "";
            $postingOrderTO->documentDate = gmdate('Y-m-d');  //curdate of debrief
            $postingOrderTO->deliveryDate = "";
            //$postingOrderTO->batchGUID = $myGUID;
            $postingOrderTO->captureUserUId = $userId;  //in-effect this user created this document...
            $postingOrderTO->deleted = 0;
            $postingOrderTO->ediCreated = "N";
            $postingOrderTO->ediFileName = "";
            if ($mfT[0]['document_type_uid'] == DT_UPLIFTS) {
               $postingOrderTO->documentType = DT_UPLIFT_CREDIT;
            }else{ 
               $postingOrderTO->documentType = DT_CREDITNOTE;
            }   
            $postingOrderTO->confirmOption = "N";
            $postingOrderTO->dataSource = DS_CAPTURE;
            $postingOrderTO->clientDocumentNumber  = ""; //auto generated.......
            $postingOrderTO->sourceDocumentNumber  = $mfT[0]['document_number'];
            $postingOrderTO->buyerAccountReference = $mfT[0]['buyer_account_reference'];
            $postingOrderTO->documentRepCodeUid    = $mfT[0]['overide_rep_code_uid'];
            $postingOrderTO->claimNumber = $postCLAIMNO;
            $postingOrderTO->podreasonuid = $postREASONCODE;
            $postingOrderTO->invoiceNumber = $mfT[0]['invoice_number'];
            $postingOrderTO->documentRepCodeUid = $mfT[0]['overide_rep_code_uid'];


            $ddUIdArr = explode(",",$postDDUID);
            $amendedQtyArr = explode(",",$postAMENDEDQTY);

            $detailArr = $transactionDAO->getDocumentDetails($postDOCMASTID);
            foreach($detailArr as $d){

              $key = array_search($d['uid'], $ddUIdArr);

              if($key===false){
                $errorTO->type=FLAG_ERRORTO_ERROR;
                $errorTO->description = "Erorr - Detail Uid List and Details Row Array failure!.";
                echo CommonUtils::getJavaScriptMsg($errorTO);
                return;
              } else {

                //build the credit note detail lines based on only where the doc qty and del qty differ.
                if($d['document_qty'] != $amendedQtyArr[$key]){

                  $postingOrderDetailTO = new PostingOrderDetailTO;
                  $postingOrderDetailTO->productUId = $d['product_uid'];
                  if($d['allow_decimal'] == 'Y') {
                       $postingOrderDetailTO->quantity = '-'.abs($d['document_qty'] - ($amendedQtyArr[$key] * 100)) /100;
                       $postingOrderDetailTO->pallets = "";  //?
                       $postingOrderDetailTO->priceOverride = "Y";
                       $postingOrderDetailTO->listPrice = $d['selling_price'] / 100;  // override price overwrites this value on processing
                       $postingOrderDetailTO->discountValue = $d['discount_value'] /100;
                       $postingOrderDetailTO->nettPrice = $d['net_price'];
                       $postingOrderDetailTO->extPrice = round($postingOrderDetailTO->quantity * $d['net_price'] * 100,2);
                       $postingOrderDetailTO->vatAmount = round((($postingOrderDetailTO->extPrice * $d['vat_rate']) / 100) ,2);
                       $postingOrderDetailTO->totPrice = round($postingOrderDetailTO->extPrice + $postingOrderDetailTO->vatAmount , 2);
                  } else {
                       $postingOrderDetailTO->quantity = '-'.abs($d['document_qty'] - $amendedQtyArr[$key]);
                       $postingOrderDetailTO->pallets = "";  //?
                       $postingOrderDetailTO->priceOverride = "Y";
                       $postingOrderDetailTO->listPrice = $d['selling_price'];  // override price overwrites this value on processing
                       $postingOrderDetailTO->discountValue = $d['discount_value'];
                       $postingOrderDetailTO->nettPrice = $d['net_price'];
                       $postingOrderDetailTO->extPrice = round($postingOrderDetailTO->quantity * $d['net_price'],2);
                       $postingOrderDetailTO->vatAmount = round((($postingOrderDetailTO->extPrice * $d['vat_rate']) / 100),2);
                       $postingOrderDetailTO->totPrice = round($postingOrderDetailTO->extPrice + $postingOrderDetailTO->vatAmount, 2);
                  }
                  $postingOrderTO->detailArr[] = $postingOrderDetailTO;

                }

              }
            }

            if(count($postingOrderTO->detailArr)==0){
                $errorTO->type=FLAG_ERRORTO_ERROR;
                $errorTO->description = "Invalid or empty quantites for partial delivery!";
                echo CommonUtils::getJavaScriptMsg($errorTO);
                return;
            }

            // Do the Actual Posting. Remember the pricing details are passed back.
            $result = $postTransactionDAO->postOrder($postingOrderTO);

            if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                  $dbConn->dbQuery("rollback");
                  $errorTO->type=$result->type;
                  $errorTO->description="<Strong>Error updating document!</strong><BR><BR>".$result->description;
                  echo CommonUtils::getJavaScriptMsg($errorTO);
                  $dbConn->dbClose();
                  return;
            } else {
                  $dbConn->dbQuery("commit");
                  $errorTO->type=$result->type;
                  $errorTO->description=$result->description;
            }
            $dbConn->dbClose();

            $errorTO->description="Document Change(s) successfully saved";

//            if($postPAYMENTTYPE == '1'){
//                  $errorTO->description .= "<BR><BR><BR><a href=javascript:; onclick=window.open('functional/presentations/presentationManagement.php?TYPE=PAYMENT&DSTATUS=Processed&CSOURCE=P&FINDNUMBER=".$postDOCMASTID."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');>[VIEW/PRINT PAYMENT RECEIPT]</a>";
//            }
            echo CommonUtils::getJavaScriptMsg($errorTO);
            return;            
            
            break;
    }

    // users can update special field values
    case "UPDATESF": {
            list($result, $msg)=updateSpecialFields();
            if ($result!==true) {
              $errorTO->type=FLAG_ERRORTO_ERROR;
              $errorTO->description=$msg;
            } else {
              $errorTO->type=FLAG_ERRORTO_SUCCESS;
            }
            $result=$errorTO;
            break;
    }

    default : {
    	
            $errorTO->type=FLAG_ERRORTO_ERROR;
            $errorTO->description="Invalid Action Type passed";
            echo CommonUtils::getJavaScriptMsg($errorTO);
            return;
    }
}

// return result
if ($result->type!=FLAG_ERRORTO_SUCCESS) {
  $dbConn->dbQuery("rollback");
  $errorTO->type=$result->type;
  $errorTO->description="<Strong>Error updating document!</strong><BR><BR>".$result->description;
  echo CommonUtils::getJavaScriptMsg($errorTO);
  $dbConn->dbClose();
  return;
} else {
  $dbConn->dbQuery("commit");
  $errorTO->type=$result->type;
  $errorTO->description=$result->description;
}

$errorTO->description="Document Change(s) successfully saved";

if($postTRACKINGNUMBER <> '' && in_array($principalId, array('207', '271'))) {
	
	  $seqVal = $postTransactionDAO->SetWaybillNumberFromTracking($principalId,$postDOCMASTID);
    $postTransactionDAO = new PostTransactionDAO($dbConn);
    $ruWB = $postTransactionDAO->updateWaybillNumberFromTracking($seqVal,$postDOCMASTID);
    
    if ($ruWB->type==FLAG_ERRORTO_SUCCESS) {
           $dbConn->dbinsQuery("commit");
    }  
    
            
    $errorTO->description .= "<BR><BR><BR><a href=javascript:; onclick=window.open('functional/presentations/presentationManagement.php?TYPE=WAYBILL&FINDNUMBER=" .$postDOCMASTID . "','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');>[VIEW/PRINT WAYBILL]</a>";
}
echo CommonUtils::getJavaScriptMsg($errorTO);


$dbConn->dbClose();

return;

function updateSpecialFields() {

    global $dbConn, $principalId, $userId, $postDOCMASTID, $postTransactionDAO, $transactionDAO;

    $postingSpecialFieldTO = new PostingSpecialFieldTO;
    $postingSpecialFieldTO->DMLType ="UPDATE";

    $mfT = $transactionDAO->getDocumentWithDetailItem($userId, $principalId, $postDOCMASTID);
    if (sizeof($mfT)==0) {
      echo "You do not have access to this information, or document master does not exist.";
      return;
    }

    $postMiscDAO = new PostMiscellaneousDAO($dbConn);
    $mfFlds=$transactionDAO->getDocumentSpecialFields($postDOCMASTID,$mfT[0]["document_type_uid"]);

    foreach ( $mfFlds as $smpfLine ) {
      if ($smpfLine['uid']==FLAG_STATUS_DELETED) continue; // dont update deleted values

      $postingSpecialFieldTO->principal = $principalId;
      $postingSpecialFieldTO->fielduid = $smpfLine['uid'];
      $postingSpecialFieldTO->entityUId = $postDOCMASTID;
      $postingSpecialFieldTO->editable = $smpfLine['editable'];

      if (isset($_POST["MDSF_".$smpfLine['uid']])) $postingSpecialFieldTO->value = $_POST["MDSF_".$smpfLine['uid']]; else $postingSpecialFieldTO->value = "";

      if($postingSpecialFieldTO->editable=='Y'){
        $Smpdresult = $postMiscDAO->postSpecialField($postingSpecialFieldTO);

        if ($Smpdresult->type!=FLAG_ERRORTO_SUCCESS) {
          $result3=mysql_query("rollback", $dbConn->connection);
          return array(false,"The Document Special Fields could not be added/updated because the Special Field update/insert failed.<BR><BR>".$Smpdresult->description);
        } else {
           // continue with next
        }
      }
    }

    return array(true,"");
}

?>
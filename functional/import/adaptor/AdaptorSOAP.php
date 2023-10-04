<?php
/* This is an ADAPTOR. As little as possible processing and lookups should happen in here. Leave that to the processing script.
 * Adaptors should be as lightweight as possible
 *
 * STORE CREDIT LIMITS IMPORT
 *
 * Updates the credit limit fields on principal_store_master by using store special fields
 * File Structure : XML
 * Sample :
 * see xsd
  */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostTransactionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


class AdaptorSOAP {


    private $dbConn;

    function __construct($dbConn) {

      $this->dbConn = $dbConn;
      // re-use above globals what we can from calling program to improve speed
    }





    public function EPODUpdateDeliveryNotice($requestArr){


      $postTransactionDAO = new PostTransactionDAO($this->dbConn);
      $transactionDAO = new TransactionDAO($this->dbConn);


      //is valid request
      if(!is_array($requestArr) ||
         !count($requestArr) > 0 ||
         !isset($requestArr[2]['name']) ||
         $requestArr[2]['name'] != 'UpdateDeliveryNotice' ||
         !isset($requestArr[2]['result']) ||
         count($requestArr[2]['result'])!=6 ||
         !isset($requestArr[2]['result']['DeliveryNoticeId']) ||
         !isset($requestArr[2]['result']['Status'])
         ){
        $this->EPODUpdateDeliveryNoticeResponse(1, 'Invalid Request or Structure');
      }

      //echo '<pre>';
      //var_dump($requestArr);


      if(isset($requestArr[2]['result']['UserName']) &&
         isset($requestArr[2]['result']['UserPassword']) &&
         $requestArr[2]['result']['UserName'] ==  EPOD_WS_USERNAME &&
         $requestArr[2]['result']['UserPassword'] == EPOD_WS_PASSWORD){

         //REQUEST SEEMS OK AT THIS POINT!

         //LOCAL EPOD REQUEST BY NOTICE ID
         $eArr = $transactionDAO->getDocumentEPODItemByNoticeID($requestArr[2]['result']['DeliveryNoticeId']);

         if(count($eArr)==0){ //no delivery notice found!
           $this->EPODUpdateDeliveryNoticeResponse(1, 'Invalid Delivery Notice Id');
         } else {

           //translate status code to message
           $statusMsg = '';
           switch ($requestArr[2]['result']['Status']){
             case 0:
               $statusMsg = 'Approval Pending';
               break;
             case 1:
               $statusMsg = 'Approved';
               break;
             case 2:
               $statusMsg = 'Declined';
               break;
             case 3:
               $statusMsg = 'Delivered';
               break;
             case 4:
               $statusMsg = 'Cancelled';
               break;
             case 5:
               $statusMsg = 'Expired';
               break;
             case 6:
               $statusMsg = 'Processed';
               break;
             case 7:
               $statusMsg = 'Approval Failed';
               break;
             case 8:
               $statusMsg = 'No Profile found at Bank';
               break;
             default:
               $statusMsg = 'Unknown Status - ' . $requestArr[2]['result']['Status'];
               break;
           }



          //update status of delivery id.
          $update = $postTransactionDAO->postEPODUpdate($eArr['uid'], $statusMsg, $requestArr[2]['result']['Status']);

          if($update->type == FLAG_ERRORTO_SUCCESS){

          } else {

          }

          if ($update->type == FLAG_ERRORTO_SUCCESS) {
            $result2 = mysql_query("commit", $this->dbConn->connection);
            $this->EPODUpdateDeliveryNoticeResponse(0, 'Delivery Notice Successfully Updated!');
          } else {
           $result2 = mysql_query("rollback", $this->dbConn->connection);
           $this->EPODUpdateDeliveryNoticeResponse(1, 'Update Error - ' .  $update->description);
          }

         }
      } else {
        $this->EPODUpdateDeliveryNoticeResponse(0, 'Invalid Username/Password');
      }


    }


    public function EPODUpdateDeliveryNoticeResponse($responseCode, $responseMsg){

      $responseXML = '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/"><SOAP:Header/>
        <SOAP:Body>
          <UpdateDeliveryNoticeResponse xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <UpdateDeliveryNoticeResult>
              <ResponseCode>'.$responseCode.'</ResponseCode>
              <ResponseMessage>'.$responseMsg.'</ResponseMessage>
            </UpdateDeliveryNoticeResult>
          </UpdateDeliveryNoticeResponse>
        </SOAP:Body>
        </SOAP:Envelope>';
      echo $responseXML;
      die();

    }



}
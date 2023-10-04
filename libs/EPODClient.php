<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER.'functional/ws/lib/nusoap.php');  //soap lib



class EPODClient {


  public $jobCount = 0; //counter for script output

  private $dbConn;
  private $epodClient;
  private $transactionDAO;
  private $postTransactionDAO;


  public function __construct() {

    $this->dbConn = new dbConnect();
    $this->dbConn->dbConnection();

    $this->transactionDAO = new TransactionDAO($this->dbConn);
    $this->postTransactionDAO = new PostTransactionDAO($this->dbConn);

  }

  public function runProcess(){

    //START PROCESS
    $eArr = $this->transactionDAO->getQueuedEPOD(FLAG_STATUS_QUEUED);

    $this->jobCount = count($eArr);

    if(count($eArr)==0){

      echo 'No Queued Items<BR>';

    } else {

      foreach($eArr as $e){

        $numberRequests = ($e['number_of_requests']+1);
        $callResult = $this->UploadInvoice(
                                            $e['amount'],
                                            $e['rsa_id'],
                                            $e['delivery_date'],
                                            $e['description'],
                                            $e['document_number'],
                                            $e['customer_order_number'],
                                            $e['cellphone_number'],
                                            $e['document_url'],
                                            $options = array(
                                                             'uid'=>$e['uid']
                                                            )
                                          );

        var_dump($callResult);

        if(isset($callResult->type) && ($callResult->type == FLAG_ERRORTO_SUCCESS) &&
           (isset($callResult->object['UploadInvoiceResult'])) &&
           (isset($callResult->object['UploadInvoiceResult']['ResponseCode'])) &&
           ($callResult->object['UploadInvoiceResult']['ResponseCode'] == 0)
          ) {

            $deliveryID = $callResult->object['UploadInvoiceResult']['DeliveryNoticeId'];
            $successResult = $this->postTransactionDAO->postEPODSuccess($e['uid'], $deliveryID, $numberRequests);
            if($successResult->type != FLAG_ERRORTO_SUCCESS){
              BroadcastingUtils::sendAlertEmail("Error in EPODProcess", "error running postEPODSuccess : " . $successResult->description, "Y", false);
              $this->dbConn->dbinsQuery("rollback");
            } else {
              $this->dbConn->dbinsQuery("commit");  //commit for process also.
            }

          } else {

            //HANDLE MINOR AND MAJOR ERRORS HERE.

            //nothing should fail...
            BroadcastingUtils::sendAlertEmail("EPOD Request Error", "error running EPODProcess.php -> UploadInvoice while sending request for epod notice id : ".$e['uid'], "Y", false);

            $successResult = $this->postTransactionDAO->postEPODError($e['uid'], $numberRequests, $errorLimit = 30);  //increments number of requests and disables at limit
            if($successResult->type != FLAG_ERRORTO_SUCCESS){
              $this->dbConn->dbinsQuery("rollback");
            } else {
              $this->dbConn->dbinsQuery("commit");  //commit for process also.
            }
          }

      } //eoloop
    }



    $polling = false;

    if($polling){

    //UPDATES
    $euArr = $this->transactionDAO->getQueuedEPOD(FLAG_ERRORTO_SUCCESS); //success => completed

    $this->jobCount += count($euArr);

    if(count($euArr)==0){

      echo '<br>No Active Items<BR>';

    } else {

      foreach($euArr as $e){

        $callResult = $this->QueryDeliveryNotice($e['delivery_notice_id'], $options = array('uid'=>$e['uid']));

        //echo '<pre>';
        //var_dump($callResult);

        if(isset($callResult->type) && ($callResult->type == FLAG_ERRORTO_SUCCESS) &&
           (isset($callResult->object['QueryDeliveryNoticeResult'])) &&
           (isset($callResult->object['QueryDeliveryNoticeResult']['ResponseCode'])) &&
           ($callResult->object['QueryDeliveryNoticeResult']['ResponseCode'] == 0)
          ) {

            $statusCode = $callResult->object['QueryDeliveryNoticeResult']['invoice']['StatusCode'];
            $statusMsg = $callResult->object['QueryDeliveryNoticeResult']['invoice']['Status'];

            $successResult = $this->postTransactionDAO->postEPODUpdate($e['uid'], $statusMsg, $statusCode);

            if($successResult->type != FLAG_ERRORTO_SUCCESS){
              BroadcastingUtils::sendAlertEmail("Error in EPODProcess", "error running postEPODUpdate : " . $successResult->description, "Y", false);
              $this->dbConn->dbinsQuery("rollback");
            } else {
              $this->dbConn->dbinsQuery("commit");  //commit for process also.
            }

          } else {

            //HANDLE MINOR AND MAJOR ERRORS HERE.

            //nothing should fail...
            //BroadcastingUtils::sendAlertEmail("EPOD Request Error", "error running EPODProcess.php -> UploadInvoice while sending request for epod notice id : ".$e['uid'], "Y", false);
            /*
            $successResult = $this->postTransactionDAO->postEPODError($e['uid'], $numberRequests, $errorLimit = 30);  //increments number of requests and disables at limit
            if($successResult->type != FLAG_ERRORTO_SUCCESS){
              $this->dbConn->dbinsQuery("rollback");
            } else {
              $this->dbConn->dbinsQuery("commit");  //commit for process also.
            }
            */
          }

      } //eoloop
    }

    } //end of polling.





  }


    public function QueryDeliveryNotice($id, $addOptions){

      $params = array(
          'UserName' => EPOD_WS_USERNAME,
          'UserPassword' => EPOD_WS_PASSWORD,
          'DeliveryNoticeId' => $id
      );

      return $this->WebServiceCall('QueryDeliveryNotice', $params, $addOptions);

    }


    public function UploadInvoice($amount, $customerId, $deliveryDate, $description, $invoiceNum, $orderNum, $customerCellNumber, $invoiceUrl, $addOptions = array()){

      $params = array(
          'UserName' => EPOD_WS_USERNAME,
          'UserPassword' => EPOD_WS_PASSWORD,
          'Amount' => $amount, // - decimial
          'CustomerId' => $customerId, // -> RSA lookup cell number (vslidation)
          'DeliveryDate' => $deliveryDate . 'T00:00:00',
          'Description' => $description, //store name either (NOT ON SMS)
          'InvoiceNum' => $invoiceNum, // - 8 char
          'OrderNum' => $orderNum, // - 20 char
          'CustomerCellNumber' => $customerCellNumber, //-> validation.
          'InvoiceUrl' => $invoiceUrl  // -

      );

      return $this->WebServiceCall('UploadInvoice', $params, $addOptions);

    }


    public function UpdateDeliveryNotice($id, $status, $remarks, $deliveryDate, $addOptions){

      $params = array(
          'UserName' => EPOD_WS_USERNAME, // -- beyond payment username also for incoming call
          'UserPassword' => EPOD_WS_PASSWORD,
          'DeliveryNoticeId' => $id,
          'Status' => $status,
          'Remarks' => $remarks,
          'DeliveryOrCancelDate' => $deliveryDate . 'T00:00:00'
      );

      return $this->WebServiceCall('UpdateDeliveryNotice', $params, $addOptions);

    }


    private function WebServiceCall($operation, $params, $addOptions){

      $errorTO = new ErrorTO();
      $client = new nusoap_client(EPOD_WS_URL, true);
      $client->soap_defencoding = 'utf-8';
      $client->setHTTPEncoding('deflate, gzip');

      $err = $client->getError();
      if ($err) {
        echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
        $errorTO->type = FLAG_ERRORTO_ERROR;
        $errorTO->description = htmlspecialchars($client->getDebug(), ENT_QUOTES);
        return $errorTO;
      }


      $result = $client->call($operation, $params, "http://poc.beyondpayments.com/pondservice", "http://poc.beyondpayments.com/pondservice/" . $operation, $headers=false, $rpcParams=null, $style = "document", $use='encoded');

      //var_dump($result)

      //DEBUG
      $debug = false;
      if($debug){
        if ($client->fault) {
                echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
        } else {
          $err = $client->getError();
          if ($err) {
            echo '<h2>Error</h2><pre>' . $err . '</pre>';
          } else {
            echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
          }
        }

        echo '<h2>Request</h2><pre>' . htmlspecialchars(str_replace("><",">\n<",$client->request), ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' , var_dump($client->return) , '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
      }


      //LOGGING
      $xmlLog = true;
      if($xmlLog){
        $logPath = SERVER_ROOT .'/'. FILE_ARCHIVE_LOGS_PATH . 'epod/bkup/' . date('Y') . '/' . date('m') . '/';
        //echo $logPath;
        //var_dump($addOptions);
        @mkdir($logPath, 0777, true);
        if(is_dir($logPath) && isset($addOptions['uid'])){
          $requestFilename = 'EPOD_ID-'.$addOptions['uid'].'_'.gmdate('YmdHis').'_REQUEST.xml';
          $reponseFilename = 'EPOD_ID-'.$addOptions['uid'].'_'.gmdate('YmdHis').'_RESPONSE.xml';
          file_put_contents($logPath . $requestFilename, substr($client->request, strpos($client->request, '<?'), strlen($client->request)));
          file_put_contents($logPath . $reponseFilename, $client->responseData);
          echo $logPath . $requestFilename . '<br>';
          echo $logPath . $reponseFilename . '<br>';
        }
      }



      if($client->fault){
        $errorTO->type = FLAG_ERRORTO_ERROR;
        $errorTO->description = '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>' . $result . '</pre>';
      } else {
          $err = $client->getError();
          if ($err) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>' . $result . '</pre>';
          } else {
            $errorTO->type = FLAG_ERRORTO_SUCCESS;
            $errorTO->object = $result;
          }
      }


      return $errorTO;
    }

    /*
    public function QueryDeliveryNoticeByDate(){

      $params = array(
          'UserName' => EPOD_WS_USERNAME,
          'UserPassword' => EPOD_WS_PASSWORD,
          'StartDate' => '2011-01-04T10:00:00',
          'EndDate' => '2012-08-30T00:00:00'
      );

      $result = $client->call('QueryDeliveryNoticeByDate', $params, "http://poc.beyondpayments.com/pondservice", "http://poc.beyondpayments.com/pondservice/QueryDeliveryNoticeByDate", false, null, $style = "document");

      return $result;

    }
    */





}







<?php
// need to load the Industria Depot = warehouse 03
// load vendor_principal
// need to load php sqlexpress drivers

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/DBConnect_3rdParty_SQLExpress.php");
include_once($ROOT.$PHPFOLDER.'TO/PostingStoreTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingDetailTO.php');
include_once ($ROOT.$PHPFOLDER.'functional/import/processor/ProcessorTOH.php');



class ICTechnologyClient {


  public $jobCount = 0; //counter for script output

  private $dbConn;
  private $dbConn_3rdParty;
  private $transactionDAO;
  private $postBIDAO;
  private $client = false;


  public function __construct($dbConn) {

    $this->dbConn = $dbConn;
    $this->transactionDAO = new TransactionDAO($this->dbConn);
    $this->postBIDAO = new PostBIDAO($this->dbConn);

  }

  // ICTechnology will ignore a max val > 20
  // This is a denormalised table with the detail redundant so you can't easily limit it to number of row returned
  public function runProcess_getOrders($principalUId, $company, $username, $password){

      // get orders
      $this->dbConn_3rdParty = new DBConnect_3rdParty_SQLServer();
      $this->dbConn_3rdParty->dbConnection_ICTechnology();

      $sql = "SELECT AutoID, Company, Warehouse, OrderNum, CONVERT(varchar, OrderDate, 120) OrderDate, Account,
                     Address1, Address2, Address3, Address4, Address5, Address6, AccountName, AccDescription,
                     PAddress1, PAddress2, PAddress3, PAddress4,
                     TaxRate, StockCode, StockName, Quantity, UnitPrice, LineID, LineDiscount, LineTotExcl,
                     LineTax, LineTotIncl, UnitPriceExclAfterDiscount, ExtOrderNum, TaxNumber
              FROM   JHBDB.JHBUser.Orders
              WHERE  Company = '{$company}'
              AND    Downloaded = 0
              AND    OrderDate >= CONVERT(datetime,'".date(GUI_PHP_DATE_FORMAT)."') - 21";

      $orders = $this->dbConn_3rdParty->dbGetAll($sql);

      if ($this->dbConn_3rdParty->dbQueryResult===false) {
        echo "<p><span style='color:red'>ERROR (#1) in ".get_class($this)." ICTechnologyClient.runProcess_getOrders(): </span>".var_dump(sqlsrv_errors())."</p>";
        return false;
      }

      if (count($orders)==0) {
        echo "<p>No Rows to Download</p>";
        return true;
      }

      echo "<p>Found : ".count($orders)." orders</p>";

      // Convert to OH TO
      $callResult = $this->setupTO($principalUId, $company, $orders);

      if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
        echo "<p><span style='color:red'>ERROR (#2) in ICTechnologyClient.setupTO(): </span>".$callResult->description."</p>";
        return false;
      }

      $ordersTOH = $callResult->object;

      // Store OH
      if (count($ordersTOH)>0) {

        $processorTOH = new ProcessorTOH($this->dbConn);
        $callResult=$processorTOH->postTOH($ordersTOH);

        if ($callResult->type != FLAG_ERRORTO_SUCCESS) {
          echo "<p><span style='color:red'>ERROR (#3) in ICTechnologyClient.setupTO(): </span>".$callResult->description."</p>";
          $this->dbConn->dbinsQuery("rollback");
          return false;
        }

        // send confirmation back
        $sendConfirmation = true;
        if ($sendConfirmation) {
          // get unique list of successfull
          $sqlValuesAutoId=array();
          foreach ($orders as $doc) {
            $sqlValuesAutoId[$doc["AutoID"]] = $doc["AutoID"];
          }

          if ( $this->dbConn_3rdParty->beginTransaction() === false ) {
            $this->dbConn->dbQuery("rollback");
            echo "<p><span style='color:red'>ERROR in ICTechnologyClient.sendOrderConfirmation, Could Not begin Transaction</p>";
            return false;
          }

          $orders = $this->dbConn_3rdParty->dbQuery("UPDATE JHBDB.JHBUser.Orders
                                                     SET Downloaded = 1,
                                                         DownloadedDate = CONVERT(date,GETDATE())
                                                     WHERE AutoID in (".implode(",",$sqlValuesAutoId).")");


          if ($this->dbConn_3rdParty->dbQueryResult===false) {
              echo "<p><span style='color:red'>ERROR in ICTechnologyClient.sendOrderConfirmation, no OrdersList Index in resultset found from response</p>";
              return false;
          }

          $rowsAffected = sqlsrv_rows_affected($this->dbConn_3rdParty->dbQueryResult);
          if ($rowsAffected!=count($sqlValuesAutoId)) {
              echo "<p><span style='color:green'>Confirmed rows (".count($sqlValuesAutoId).") did not equal updated ({$rowsAffected}) rows on 3rd Party DB in ".get_class($this)."</p>";
              // only treat as info for time being
          }

          $this->dbConn->dbQuery("commit");

          if ( $this->dbConn_3rdParty->commitTransaction() === false ) {
            echo "<p><span style='color:red'>ERROR in ICTechnologyClient.sendOrderConfirmation, Could Not COMMIT Transaction - Duplicates may occur</p>";
            return false;
          }

        }

      } // end > 0 array

      return true; // execute next loop for same principal, or exit controlled by that loop

  }




    function setupTO($principalUId, $company, $pOrders) {
      $eTO = new ErrorTO();

      $arrTO = array();

      // first put into Header-Detail format - assumes no duplicates
      $orders = array();
      foreach ($pOrders as $o) {
        $orders[$o["OrderNum"]]["Header"] = $o;
        $orders[$o["OrderNum"]]["Detail"][] = $o;
      }

      // NOTE:
      // Because these rows are returned in batch we just skip the current document if there is an error with it as
      // when we send back the confirmation then that document is therefore not confirmed and it will just keep resending
      foreach ($orders as $o) {
        $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
        $postingOrdersHoldingTO->updateProduct="Y";
        $postingOrdersHoldingTO->principalUid=$principalUId;
        $postingOrdersHoldingTO->orderDate = $o["Header"]["OrderDate"];
        $postingOrdersHoldingTO->deliveryDate = "";
        $postingOrdersHoldingTO->clientDocumentNo=$o["Header"]["OrderNum"];
        $postingOrdersHoldingTO->documentNo=preg_replace("/[^0-9]/","",$postingOrdersHoldingTO->clientDocumentNo);
        $postingOrdersHoldingTO->vendorUid = V_ICTECNOLOGY_VENDOR;
        $postingOrdersHoldingTO->vendorReference = $o["Header"]["Account"];  // used for extracts

        $postingOrdersHoldingTO->dataSource = DS_DIRECTSQL;
        $postingOrdersHoldingTO->capturedBy = 'ICTECHNOLOGY';
        $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
        $postingOrdersHoldingTO->chainLookupRef="GENERIC CHAIN";
        $postingOrdersHoldingTO->storeLookupRef=""; // ???
        $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;
        $postingOrdersHoldingTO->principalStoreUId=""; // let the processing script lookup the store
        $postingOrdersHoldingTO->reference=$o["Header"]["ExtOrderNum"];

        $postingOrdersHoldingTO->depotLookupRef = $o["Header"]["Warehouse"];
        $postingOrdersHoldingTO->depotSpecialFieldUId = "230"; // DIRECTSQL does not use onlineFileMapping
        $postingOrdersHoldingTO->deliverName = $o["Header"]["AccountName"];
        $postingOrdersHoldingTO->shipToName = $postingOrdersHoldingTO->deliverName;
        $postingOrdersHoldingTO->oldAccount = $o["Header"]["Account"]; // the lookup
        $postingOrdersHoldingTO->shipToGLN = "";

        /*******************
         *   CREATE STORE
        *******************/
        $postingStoreTO = new PostingStoreTO;
        $postingStoreTO->DMLType = "INSERT";
        $postingStoreTO->principalStoreUId = '';
        $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;

        $postingStoreTO->deliverName = $postingOrdersHoldingTO->deliverName;
        $postingStoreTO->deliverAdd1 = substr($o["Header"]["Address1"],0,60);
        $postingStoreTO->deliverAdd2 = substr($o["Header"]["Address2"],0,60).(($o["Header"]["Address3"]!="")?",":"").substr($o["Header"]["Address3"],0,60);
        $postingStoreTO->deliverAdd3 = substr($o["Header"]["Address4"],0,60).(($o["Header"]["Address5"]!="")?",":"").substr($o["Header"]["Address5"],0,60).(($o["Header"]["Address6"]!="")?",":"").substr($o["Header"]["Address6"],0,60);
        $postingStoreTO->vatNumber = $o["Header"]["TaxNumber"];

        $postingStoreTO->billName = trim(substr($o["Header"]["AccDescription"],0,60));
        $postingStoreTO->billAdd1 = $postingStoreTO->deliverAdd1;
        $postingStoreTO->billAdd2 = $postingStoreTO->deliverAdd2;
        $postingStoreTO->billAdd3 = $postingStoreTO->deliverAdd3;
        $postingStoreTO->depot = ''; // this will be set by the processing script
        $postingStoreTO->deliveryDay = "8";
        $postingStoreTO->noVAT = 0;
        $postingStoreTO->onHold = "0";
        $postingStoreTO->chain = ''; // should use the lookup
        $postingStoreTO->altPrincipalChainUId = ''; // let the posting allocate the generic chain
        $postingStoreTO->status = FLAG_STATUS_ACTIVE;
        $postingStoreTO->vendorCreatedByUId = $postingOrdersHoldingTO->vendorUid;
        $postingStoreTO->ownedBy = '';
        $postingStoreTO->oldAccount = $postingOrdersHoldingTO->oldAccount;
        $postingStoreTO->updatePrincipalStore="Y"; // updates fields if supplied and the store is already inserted
        $postingStoreTO->updateDeliveryDay="N";
        $postingStoreTO->updateNoVAT="N";
        $postingStoreTO->updateStoreStatus="N";
        $postingOrdersHoldingTO->postingStoreTO = $postingStoreTO;
        //End Create the StoreTO


        foreach ($o["Detail"] as $dtl) {
          $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();

          $postingOrdersHoldingDetailTO->clientLineNo = $dtl["LineID"];
          $postingOrdersHoldingDetailTO->productCode = $dtl["StockCode"];
          $postingOrdersHoldingDetailTO->productName = $dtl["StockName"];
          $postingOrdersHoldingDetailTO->quantity = $dtl["Quantity"];
          $postingOrdersHoldingDetailTO->listPrice = $dtl["UnitPrice"];
          $postingOrdersHoldingDetailTO->discountValue = round(($postingOrdersHoldingDetailTO->listPrice * $dtl["LineDiscount"])/100,2); // disc is a %
          $postingOrdersHoldingDetailTO->nettPrice = $dtl["UnitPriceExclAfterDiscount"];
          $postingOrdersHoldingDetailTO->extPrice = $dtl["LineTotExcl"];
          $postingOrdersHoldingDetailTO->vatRate = $dtl["TaxRate"];
          $postingOrdersHoldingDetailTO->vatAmount = $dtl["LineTax"];
          $postingOrdersHoldingDetailTO->totalPrice = $dtl["LineTotIncl"];
          $postingOrdersHoldingDetailTO->wsUniqueCreatorId = $dtl["AutoID"]; // used for extracts

          $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;

        }

        $arrTO[]=$postingOrdersHoldingTO;
      }

      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      $eTO->object = $arrTO;
      return $eTO;

    }


}







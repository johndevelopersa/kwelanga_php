<?php

/* * ********************************************************************************************
 * *
 * *  This job can run as many times per day as is necessary according to job scheduler.
 * *
 * *  It executes notifications that occur throughout the day by triggers
 * *
 * ******************************************************************************************** */

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "functional/extracts/daily/extractController.php");
include_once ($ROOT.$PHPFOLDER."DAO/DBConnect_3rdParty_SQLExpress.php");


//static method handler.
class ElvinInvoiced_DIRECTSQL {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}



class ElvinInvoiced_DIRECTSQLInit extends extractController {

  private $principalUid = 206; //uid of principal extract.

  public function generateOutput(){

    global $ROOT, $PHPFOLDER;

    $seTypeUId = 0;

    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];

    if (!$this->skipInsert) {
      // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $seTypeUId, $inclCancelled = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }

      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, $seTypeUId, array(DT_CREDITNOTE));  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
    }


    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($this->principalUid, $seTypeUId);

    /*  SUCCESS POINT - 1  */
    //nothing to do...
    if(count($seDocs)==0){
      echo "Successfully Completed Extract : ".get_class($this)." - No entries!<br>";
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
      return $this->errorTO;
    }

    //group array
    $grpDocs = array();
    $psms=array();
    foreach($seDocs as $k=>$r){

      $type = 'i';
      if ($r['document_type_uid'] == DT_CREDITNOTE){
        $type = 'c';
      }
      $grpDocs[$type][$r['dm_uid']][] = $r;
    }


    foreach($grpDocs as $type => $orders){

      $dataArr = array();
      $errorSEUIdArr = array();
      $successSEUIdArr = array();

      $sqlBulkInserts = array();
      $uploadedDateTime = date("Y-m-d H:i:s");

      $batch = 1; // to get around the SQL Express 100 Row limit
      // This prepares the statements and divides the inserts up into batches of 100
      foreach($orders as $ord){


        /*-------------------------------------------------*/
        /*            START BUILDING OUTPUT
        /*-------------------------------------------------*/

        $hasError = false;
        $tempDtl = array();
        foreach($ord as $d){ //detail rows.

          if (($type=='i') && (empty($d["ohd_ws_unique_creator_id"]))) {
            $hasError = true;
            break;
          }

          if ($type=='i') {
            $tempDtl[] = "SELECT
                           {$d['ohd_ws_unique_creator_id']},
                           '{$ord[0]['client_document_number']}',
                           'INV".abs($ord[0]['invoice_number'])."',
                           '{$ord[0]['invoice_date']}',
                           ".abs($d['document_qty']).",
                           ".number_format(abs(round($d['net_price'], 2)), 2, '.', '').",
                           {$d['extended_price']},
                           {$d['vat_amount']},
                           {$d['total']},
                           '{$uploadedDateTime}',
                           ".(empty($d['client_line_no'])?"NULL":$d['client_line_no']).",
                           '".$d['product_code']."',
                           'Q'";
          } else {
            $tempDtl[] = "SELECT
                            'ELVIN',
                            '04',
                            'INV".abs($ord[0]['invoice_number'])."',
                            '".abs($ord[0]['customer_order_number'])."',
                            '".$d['product_code']."',
                            ".abs($d['document_qty']).",
                            ".number_format(abs(round($d['net_price'], 2)), 2, '.', '').",
                            '".$ord[0]['reason_description']."',
                            '{$uploadedDateTime}',
                            '0'";
          }

        } // detail

        if ($hasError) {
          $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors
        } else {

          if (count($tempDtl)>100) {
            echo "<p><span style='color:red'>ERROR in ".get_class($this).", 100 Row SQLExpress limit exceeded</p>";
            return false;
          }

          if (!isset($sqlBulkInserts[$batch])) $sqlBulkInserts[$batch] = array();
          if ((count($sqlBulkInserts[$batch])+count($tempDtl))>100) {
            $batch++;
            $sqlBulkInserts[$batch] = array();
          }
          $sqlBulkInserts[$batch] = array_merge($sqlBulkInserts[$batch],$tempDtl);
          $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success
        }

      } // documents


      /********************************************************
       * START : DB 3rd Party Updates
       ********************************************************/

      if (count($sqlBulkInserts)>0) {

        $dbConn_3rdParty = new DBConnect_3rdParty_SQLServer();
        $dbConn_3rdParty->dbConnection_ICTechnology();

        if ( $dbConn_3rdParty->beginTransaction() === false ) {
          echo "<p><span style='color:red'>ERROR in ".get_class($this).", Could Not begin Transaction</p>";
          return false;
        }

        foreach ($sqlBulkInserts as $key=>$batch) {

          if ($type=='i') {
            // This multiple rows insert only works on SQL Express 2008+
            $sql = "INSERT INTO JHBDB.JHBUser.RTUpdates (AutoID, OrderNum, InvNumber, InvDateTime, InvQty,
                      InvUnitPrice, InvLineTotExcl, InvLineTax, InvTotIncl, InvUploadedDate, LineID, StockCode, Status) ".
                      implode(" UNION ALL ", $batch);
          } else {
            $sql = "INSERT INTO JHBDB.JHBUser.Returns (Company, Warehouse, InvNumber, ClientRef, StockCode, Quantity,
                      ValueExVat, Reason, UploadedDate, Processed) ".
                      implode(" UNION ALL ", $batch);
          }

          $dbConn_3rdParty->dbQuery($sql);

          if ($dbConn_3rdParty->dbQueryResult===false) {
            echo "<p><span style='color:red'>ERROR in ".get_class($this).", could not bulk insert to 3rd Party for type {$type}.</p>";
            return false;
          }

        } // each batch

        // Remember this is a log table so it's not important to rollback or anything
        if ( $dbConn_3rdParty->commitTransaction() === false ) {
          echo "<p><span style='color:red'>ERROR in ".get_class($this).", Could Not End/Commit Transaction</p>";
          return false;
        }

        // SQL Express does not support multiple table updates in same Statement
        // remember that if you use an alias in from clause, then you MUST also use an alias in the SET clause
        // except for the table in the UPDATE clause.

        if ($type=='i') {
          $dbConn_3rdParty->beginTransaction(); // For some reason I need to do this outside as "BEGIN TRANSACTION and COMMIT; does not work if I put it into the sql below directly!

          $sql = "DECLARE @ids TABLE (AutoID int);

                  UPDATE JHBDB.JHBUser.Orders
                  SET   JHBDB.JHBUser.Orders.InvNumber       = a.InvNumber,
                    	  JHBDB.JHBUser.Orders.InvDateTime     = a.InvDateTime,
                    	  JHBDB.JHBUser.Orders.InvQty          = a.InvQty,
                    	  JHBDB.JHBUser.Orders.InvUnitPrice    = a.InvUnitPrice,
                    	  JHBDB.JHBUser.Orders.InvLineTotExcl  = a.InvLineTotExcl,
                    	  JHBDB.JHBUser.Orders.InvLineTax      = a.InvLineTax,
                    	  JHBDB.JHBUser.Orders.InvTotIncl      = a.InvTotIncl,
                    	  JHBDB.JHBUser.Orders.InvUploadedDate = a.InvUploadedDate
                  OUTPUT INSERTED.AutoID INTO @ids
                  FROM  JHBDB.JHBUser.RTUpdates a,
                  	    JHBDB.JHBUser.Orders b
                  WHERE  a.AutoID = b.AutoID
                  AND    a.OrderNum = b.OrderNum
                  AND    a.StockCode = b.StockCode
                  AND    b.Downloaded = 1
                  AND    a.InvUploadedDate >= '{$uploadedDateTime}';

                  UPDATE JHBDB.JHBUser.RTUpdates
                  SET    RTUpdates.status = 'S'
                  FROM JHBDB.JHBUser.RTUpdates
                  		JOIN @ids i on i.AutoID = RTUpdates.AutoID
                  WHERE JHBDB.JHBUser.RTUpdates.InvUploadedDate >= '{$uploadedDateTime}';

                  ";

          $dbConn_3rdParty->dbQuery($sql);

          // Remember this is a log table so it's not important to rollback or anything
          if ( $dbConn_3rdParty->dbQueryResult=== false ) {
            echo "<p><span style='color:red'>ERROR in ".get_class($this).", Could Not Update Invoice Details on 3rdParty DB</p>";
            return false;
          }

          $dbConn_3rdParty->commitTransaction();

          // to undo
          /*
          update JHBDB.JHBUser.Orders
          set     Downloaded = 1,
                  DownloadedDate = '2013-08-29 14:21:00',
                  InvDateTime = null,
                  InvLineTax = null,
                  InvLineTotExcl = null,
                  InvNumber = null,
                  InvQty = null,
                  InvTotIncl = null,
                  InvUploadedDate = null,
                  InvUnitPrice = null
                  from   JHBDB.JHBUser.Orders a
          where  Company = 'ELVIN'
              */
        } // end type

      } // end count > 0

      /********************************************************
       * END : DB 3rd Party Updates
       ********************************************************/

      /*
       *  UPDATE SMART EVENT in BULK
       */
      //SUCCESSFUL ITEMS
      if (sizeof($successSEUIdArr) > 0) {
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), "DIRECTSQL", "");
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
      }
      //ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
      if (sizeof($errorSEUIdArr) > 0) {
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "AutoID", "DIRECTSQL", FLAG_ERRORTO_ERROR);
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
      }

    } // END TYPE
    /*-------------------------------------------------*/



    echo "Successfully Completed Extract : ".get_class($this)."<br>";

    /*  SUCCESS POINT - 2  */
    $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
    $this->errorTO->description = "Successful";
    return $this->errorTO;

  }

}


//direct run!
if ($runMe) {
  directRunExtract(__FILE__);
}

?>
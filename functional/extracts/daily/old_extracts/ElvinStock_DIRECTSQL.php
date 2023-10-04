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
class ElvinStock_DIRECTSQL {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}



class ElvinStock_DIRECTSQLInit extends extractController {

  private $principalUid = 206; //uid of principal extract.

  public function generateOutput(){

    global $ROOT, $PHPFOLDER;

    include_once($ROOT.$PHPFOLDER."DAO/StockDAO.php");
    $stockDAO = new StockDAO($this->dbConn);

    $seTypeUId = 0;

    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];

    $stock = $stockDAO->getPrincipalStock($this->principalUid);

    /*  SUCCESS POINT - 1  */
    //nothing to do...
    if(count($stock)==0){
      echo "Successfully Completed Extract : ".get_class($this)." - No entries!<br>";
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
      return $this->errorTO;
    }

    $dataArr = array();

    $sqlBulkInserts = array();
    $uploadedDateTime = date("Y-m-d H:i:s");

    $batch = 1; // to get around the SQL Express 100 Row limit
    // This prepares the statements and divides the inserts up into batches of 100

    /*-------------------------------------------------*/
    /*            START BUILDING OUTPUT
     /*-------------------------------------------------*/

    foreach($stock as $s){

      if (!isset($sqlBulkInserts[$batch])) $sqlBulkInserts[$batch] = array();
      // SQL express limit per statement is 100
      if (count($sqlBulkInserts[$batch])>=100) {
        $batch++;
        $sqlBulkInserts[$batch] = array();
      }

      $sqlBulkInserts[$batch][] = "SELECT
                     'ELVIN',
                     '{$s['stock_item']}',
                     '".str_replace("'","''",$s['stock_descrip'])."',
                     '{$s['opening']}',
                     '{$s['arrivals']}',
                     '{$s['uplifts']}',
                     '{$s['returns_cancel']}',
                     '{$s['returns_nc']}',
                     '{$s['delivered']}',
                     '{$s['adjustment']}',
                     '{$s['closing']}',
                     '{$s['allocations']}',
                     '{$s['in_pick']}',
                     '{$s['available']}',
                     '{$s['goods_in_transit']}',
                     '{$s['blocked_stock']}',
                     '{$s['lost_sales_cancel']}',
                     '{$s['lost_sales_oos']}',
                     '{$s['stock_count']}',
                     '{$uploadedDateTime}'";
    }

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

      $sql = "truncate table JHBDB.JHBUser.UllStock";
      $dbConn_3rdParty->dbQuery($sql);

      if ($dbConn_3rdParty->dbQueryResult===false) {
        echo "<p><span style='color:red'>ERROR in ".get_class($this).", could not truncate 3rd Party for Stock.</p>";
        return false;
      }

      foreach ($sqlBulkInserts as $key=>$batch) {
        // This multiple rows insert only works on SQL Express 2008+
        $sql = "INSERT INTO JHBDB.JHBUser.UllStock (Company, Product_Code, Product_Description, Opening,
                  Arrivals, Uplifts, Returns_Cancelled, Returns_No_Charge, Delivered, Stock_Adjustments, Closing_Stock, New_Orders,
                  In_Pick, Available, Goods_In_Transit, Blocked_Stock, Lost_Sales_Cancelled, Lost_Sales_OOS, Stock_Count, Uploaded_Date) ".
                  implode(" UNION ALL ", $batch);

        $dbConn_3rdParty->dbQuery($sql);

        if ($dbConn_3rdParty->dbQueryResult===false) {
          echo "<p><span style='color:red'>ERROR in ".get_class($this).", could not bulk insert to 3rd Party for Stock.</p>";
          return false;
        }

      } // each batch

      // Remember this is a log table so it's not important to rollback or anything
      if ( $dbConn_3rdParty->commitTransaction() === false ) {
        echo "<p><span style='color:red'>ERROR in ".get_class($this).", Could Not End/Commit Transaction</p>";
        return false;
      }


    /********************************************************
     * END : DB 3rd Party Updates
     ********************************************************/

    } // END has rows
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
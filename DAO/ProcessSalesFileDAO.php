<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class ProcessSalesFileDAO {
      private $dbConn;

      function __construct($dbConn) {
          $this->dbConn = $dbConn;
          $this->errorTO = new ErrorTO;
      }
// **************************************************************************************************************************
      public function dropTempSalesTable($userUId) {

           $bldsql = "DROP TABLE IF EXISTS honey_sales_temp_" .mysqli_real_escape_string($this->dbConn->connection, $userUId) ;
           $result = $this->dbConn->dbQuery($bldsql);
           $this->dbConn->dbQuery("commit");
           
      }
//******************************************************************************************************************************************************
      public function createTempSalesTable($userUId) {

             $bldsql = "CREATE TABLE honey_sales_temp_" .mysqli_real_escape_string($this->dbConn->connection, $userUId) . " (
                                          `Company`             VARCHAR(20)  NULL,
                                          `Business_Unit`       VARCHAR(20)  NULL,
                                          `GLN`                 VARCHAR(20)  NULL,
                                          `Day`                 VARCHAR(20)  NULL,
                                          `Barcode`             VARCHAR(20)  NULL,
                                          `PnP_Article_Number`  VARCHAR(20)  NULL,
                                          `Vendor_Product_Code` VARCHAR(20)  NULL,
                                          `Product_Description` VARCHAR(20)  NULL,
                                          `Store_ID`            VARCHAR(20)  NULL,
                                          `Store`               VARCHAR(20)  NULL,
                                          `Region`              VARCHAR(20)  NULL,
                                          `Major_Region`        VARCHAR(20)  NULL,
                                          `Units`               VARCHAR(20)  NULL,
                                          `Amount`              VARCHAR(20)  NULL,
                                          `Currency`            VARCHAR(20)  NULL,
                                          `Vendor_Account`      VARCHAR(20)  NULL,
                                          `Brand`               VARCHAR(20)  NULL,
                                          `Category`            VARCHAR(20)  NULL ,
                                          `Price`               VARCHAR(20)  NULL ,
                                          `Items_per_case`      VARCHAR(20)  NULL)";

               $dtresult = $this->dbConn->dbQuery($bldsql);
               
               $this->dbConn->dbQuery("commit");
               
      }
//*************************************************************************************************************************************************
      public function uploadDataToSales($fname, $userUId) {
      	
           global $ROOT;
      	
           $dirPath = $ROOT. "ftp/pnpsales/";
           
           $sql='LOAD DATA LOCAL INFILE "' . $dirPath . $fname . '" INTO TABLE honey_sales_temp_' .mysqli_real_escape_string($this->dbConn->connection, $userUId) . '   
                 FIELDS TERMINATED BY ","
                 OPTIONALLY ENCLOSED BY "\""
                 ESCAPED BY "\\\"
                 LINES TERMINATED BY "\\r\\n" 
                 IGNORE 1 LINES';
           $rTO = $this->dbConn->processPosting($sql,"");
           $this->dbConn->dbQuery("commit");
      	
      }	
//*************************************************************************************************************************************************
      public function updateTempFilePrice($userUId) {
              
           $sql = "UPDATE honey_sales_temp_11 hst 
                   LEFT JOIN principal_product pp ON pp.principal_uid = 305 
                                                  AND pp.product_code = hst.Vendor_Product_Code 
                   LEFT JOIN .pricing p ON p.principal_uid = 305
                                        AND pp.uid = p.principal_product_uid 
                                        AND hst.Day BETWEEN p.start_date 
                                        AND p.end_date 
                                        AND p.chain_store = 2515 SET hst.Price = round(p.list_price,2), hst.Items_per_case = pp.items_per_case 
                   WHERE 1;";  
           $rTO = $this->dbConn->processPosting($sql,"");
           $this->dbConn->dbQuery("commit");
      }	
//*************************************************************************************************************************************************
      public function extractRawData($userUId) {
              
           $sql = "SELECT h.Store_ID AS 'Store ID',
                          h.Store AS 'Store',
                          h.Region AS 'Region',
                          h.Major_Region AS 'Major Region',
                                                    h.Units AS 'Units',
                          h.Amount AS 'Amount',
                          round(h.Price / h.Items_per_case,2) AS 'Cost Per Unit',	
                          h.Price AS 'Cost per case',	
                          h.Items_per_case AS 'Items per case',
                          round(h.Units * (h.Price / h.Items_per_case),2) AS 'Comm Total'	
                   FROM .honey_sales_temp_" .mysqli_real_escape_string($this->dbConn->connection, $userUId) . " h ;";   

            $utresult = $this->dbConn->dbGetAll($sql);
            if (count($utresult) == 0) { ?>
                <script type='text/javascript' >parent.showMsgBoxError("No Rows Selected<BR><BR>")</script> 
            <?php
                return;	
            }
            
            $csv_export = '';
            
            $csv_export.= "Raw Data Report " . "\n";
            
            foreach (array_keys($utresult[0]) as $arow) {
                 $csv_export.= $arow . ',';
            }
            $csv_export.= "\n";
            foreach ($utresult as $brow) {
                $csv_export.= implode(',',$brow) . "\n";
            }
            $csv_export.= '';
            $csv_export.= 'End of Raw Report' . "\n";
            
            
            $fileName = "Raw Data.csv";
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=\"".$fileName."\"");
            header("Content-Type: text/csv");
            header("Content-Type: application/force-download");            
            echo $csv_export;	

      }	
//*************************************************************************************************************************************************
      public function extractComReport($userUId) {
              
           $sql = "SELECT h.Store_ID AS 'Store Code',
                          h.Store AS 'Store',
                          h.Major_Region AS 'Region',
                          ROUND(SUM(h.Units * (h.Price / h.Items_per_case)) ,2) AS 'Commissionable Total'
                   FROM .honey_sales_temp_" .mysqli_real_escape_string($this->dbConn->connection, $userUId) . " h
                   GROUP BY h.Store_ID
                   ORDER BY h.Major_Region, h.Store";
                   
            $utresult = $this->dbConn->dbGetAll($sql);
            if (count($utresult) == 0) { ?>
                <script type='text/javascript' >parent.showMsgBoxError("No Rows Selected<BR><BR>")</script> 
            <?php
                return;	
            }
            
            $csv_export = '';
            
            $csv_export.= "Commission Report " . "\n";
            
            foreach (array_keys($utresult[0]) as $arow) {
                 $csv_export.= $arow . ',';
            }
            
            $csv_export.= "\n";

            foreach ($utresult as $brow) {
                $csv_export.= implode(',',$brow) . "\n";
            }
            $csv_export.= '';
            $csv_export.= 'End of Commission Report' . "\n";
            
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=\"".$fileName."\"");
            header("Content-Type: text/csv");
            header("Content-Type: application/force-download");            
            echo $csv_export;	

      }	
//*************************************************************************************************************************************************

}

?>  
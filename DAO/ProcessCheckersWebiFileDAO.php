<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class ProcessCheckersWebiFileDAO {
      private $dbConn;

      function __construct($dbConn) {
          $this->dbConn = $dbConn;
          $this->errorTO = new ErrorTO;
      }
// **************************************************************************************************************************
      public function dropTempWebiTable($userUId) {

           $bldsql = "DROP TABLE IF EXISTS webi_temp_" .mysqli_real_escape_string($this->dbConn->connection, $userUId) ;
           $result = $this->dbConn->dbQuery($bldsql);
           $this->dbConn->dbQuery("commit");
           
      }
//******************************************************************************************************************************************************
      public function createTempWebiTable($userUId) {

             $bldsql = "CREATE TABLE webi_temp_" .mysqli_real_escape_string($this->dbConn->connection, $userUId) . " (
                                                `fld1`              VARCHAR(60)  NULL,
                                                `fld2`              VARCHAR(60)  NULL,
                                                `fld3`              VARCHAR(60)  NULL,
                                                `fld4`              VARCHAR(60)  NULL,
                                                `fld5`              VARCHAR(60)  NULL,
                                                `fld6`              VARCHAR(60)  NULL) ";

               $dtresult = $this->dbConn->dbQuery($bldsql);
               
               $this->dbConn->dbQuery("commit");
               
      }
//*************************************************************************************************************************************************
      public function uploadDataToWebi($fname, $userUId) {
      	
           global $ROOT;
      	
           $dirPath = $ROOT. "ftp/pnpsales/";
           
           $sql='LOAD DATA LOCAL INFILE "' . $dirPath . $fname . '" INTO TABLE webi_temp_' .mysqli_real_escape_string($this->dbConn->connection, $userUId) . '
                 FIELDS TERMINATED BY ","
                 OPTIONALLY ENCLOSED BY "\""
                 ESCAPED BY "\\\"
                 LINES TERMINATED BY "\\r\\n" 
                 IGNORE 1 LINES';	
           $rTO = $this->dbConn->processPosting($sql,"");
           $this->dbConn->dbQuery("commit");
      	
      }	
//*************************************************************************************************************************************************
      public function formatWebiData() {
      
          $sql = "SELECT *
                  FROM webi_temp_11 
                  WHERE 1;";

          $webiData = $this->dbConn->dbGetAll($sql);
        
          return $webiData;
     }          

//*************************************************************************************************************************************************
      public function insertWebiRecord($wDate, $wStore, $wArt, $wRos, $wSales, $wStock ) {

          $sql = "INSERT IGNORE INTO .checkers_webi (checkers_webi.date,
                                                     checkers_webi.branch,
                                                     checkers_webi.article,
                                                     checkers_webi.ros,
                                                     checkers_webi.sales,
                                                     checkers_webi.stock)
                  VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $wDate)  . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $wStore) . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $wArt)   . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $wRos)   . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $wSales) . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $wStock) . "')" ;
                  $this->errorTO = $this->dbConn->processPosting($sql,"");
             
                  if($this->errorTO->type == 'S') {
                         $this->dbConn->dbQuery("commit");
                         return $this->errorTO;     	
                  } else {
                        return $this->errorTO; 
                  }      	
      }     
//*************************************************************************************************************************************************
      public function updateWebiRecord($wDate, $wStore, $wArt, $wRos, $wSales, $wStock )  {

         $sql = "UPDATE .checkers_webi cw SET cw.ros   = '" . mysqli_real_escape_string($this->dbConn->connection, $wRos)   . "',
                                              cw.sales = '" . mysqli_real_escape_string($this->dbConn->connection, $wSales) . "',
                                              cw.stock = '" . mysqli_real_escape_string($this->dbConn->connection, $wstock) . "'
                 WHERE cw.date    = '" . mysqli_real_escape_string($this->dbConn->connection, $wDate)  . "'
                 AND   cw.branch  = '" . mysqli_real_escape_string($this->dbConn->connection, $wStore) . "'
                 AND   cw.article = '" . mysqli_real_escape_string($this->dbConn->connection, $wArt) . "';";
 
                 $this->errorTO = $this->dbConn->processPosting($sql,"");

                  if($this->errorTO->type == 'S') {
                         $this->dbConn->dbQuery("commit");
                         return $this->errorTO;     	
                  } else { 
                  	    echo $sql      ;
                        return $this->errorTO;  
                  }      	      	
      	
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
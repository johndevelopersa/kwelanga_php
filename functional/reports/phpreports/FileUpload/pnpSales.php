<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/reports/phpreports/pnpSales.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

$bldsql = "DROP TABLE IF EXISTS honey_sales_temp";
   
$result = $dbConn->dbQuery($bldsql);

//******************************************************************************************************************************************************

$bldsql = "CREATE TABLE honey_sales_temp (`Company`             VARCHAR(20)  NULL,
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

$dtresult = $dbConn->dbQuery($bldsql);
//*************************************************************************************************************************************************

$dirPath = 'C:/inetpub/wwwroot/systems/kwelanga_system/ftp/honeyfields/PNP/PNP12.csv';


$sql='LOAD DATA LOCAL INFILE "' . $dirPath . $row . '" INTO TABLE honey_sales_temp
      FIELDS TERMINATED BY ","
	    OPTIONALLY ENCLOSED BY "\""
	    ESCAPED BY "\\\"
	    LINES TERMINATED BY "\\r\\n" 
      IGNORE 1 LINES';	

      $rTO = $dbConn->processPosting($sql,"");

$dbConn->dbQuery("commit");

 echo "end";     
 
/*

SELECT h.Store_ID AS 'Store ID',
       h.Store AS 'Store',
		   h.Region AS 'Region',
		   h.Major_Region AS 'Major Region',
		   h.Units AS 'Units',
	  	 h.Amount AS 'Amount',
	  	 round(h.Price / h.Items_per_case,2) AS 'Cost Per Unit',	
	  	 h.Price AS 'Cost per case',	
	  	 h.Items_per_case AS 'Items per case',
	  	 round(h.Units * (h.Price / h.Items_per_case),2) AS 'Comm Total'	
FROM .honey_sales_temp h ;


SELECT h.Store_ID AS 'Store Code',
       h.Store AS 'Store',
		 h.Major_Region AS 'Region',
		 ROUND(SUM(h.Units * (h.Price / h.Items_per_case)) ,2) AS 'Commissionable Total'
FROM .honey_sales_temp h
GROUP BY h.Store_ID

update honey_sales_temp hst
LEFT JOIN .principal_product pp ON pp.principal_uid = 305 AND pp.product_code = hst.Vendor_Product_Code
LEFT JOIN .pricing p ON pp.uid = p.principal_product_uid AND hst.Day BETWEEN p.start_date AND p.end_date AND p.chain_store = 2515
SET hst.Price = round(p.list_price,2), hst.Items_per_case = pp.items_per_case
WHERE 1;   

*/

?>  
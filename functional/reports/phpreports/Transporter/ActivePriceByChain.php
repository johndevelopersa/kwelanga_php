<?php
// http://www.kwelangasolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/reports/phpreports/$prinrow['

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/ReportsDAO.php");

global $paramsArr, $principalId, $outputType;

if (!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();
//  0123456789012345678901234567890
//  2018022018-04-012018-04-30

$StartDate     = $paramsArr['p1'];
$grouplist     = $paramsArr['p2'];

// Create temporary table user specfic

$bldsql = "DROP TABLE IF EXISTS temp_price_report_" . $userId . ";   ";

$dtresult = $dbConn->dbQuery($bldsql);
$dbConn->dbQuery("commit");

$bldsql = "CREATE TABLE temp_price_report_" . $userId . " (`Field1` VARCHAR(40) NULL,
                                     `Field2` VARCHAR(40) NULL,
                                     `Field3` VARCHAR(40) NULL,
                                     `Field4` VARCHAR(40) NULL,
                                     `Field5` VARCHAR(40) NULL,
                                     `Field6` VARCHAR(40) NULL,
                                     `Field7` VARCHAR(40) NULL,
                                     `Field8` VARCHAR(40) NULL,
                                     `Field9` VARCHAR(40) NULL,
                                     `Field10` VARCHAR(40) NULL,
                                     `Field11` VARCHAR(40) NULL,
                                     `Field12` VARCHAR(40) NULL,
                                     `Field13` VARCHAR(40) NULL,
                                     `Field14` VARCHAR(40) NULL,
                                     `Field15` VARCHAR(40) NULL,
                                     `Sort`    INT(5) NULL);";

$dtresult = $dbConn->dbQuery($bldsql);
$dbConn->dbQuery("commit");

$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getPrincipalName($principalId);

$firstTime = 'T';
$catagoryStore = '';
$sortCount = 0;


foreach($gSBP as $row1) {
	   $sortCount = $sortCount+1;
	   $ReportsDAO = new ReportsDAO($dbConn);
     $iTP = $ReportsDAO->insertIntoTempPrice($userId, 'Field1', 'Field6', 'Field15', $row1['Principal'], date("Y-m-d H:i"),"100", $sortCount);
	
 }
     $sortCount = $sortCount+1;
     $ReportsDAO = new ReportsDAO($dbConn);
     $iTP = $ReportsDAO->insertIntoTempPrice($userId, "Field1", "Field2", 'Field15', '', '',"100", $sortCount);

     $sortCount = $sortCount+1;
     $ReportsDAO = new ReportsDAO($dbConn);
     $iTP = $ReportsDAO->insertIntoTempPrice($userId, "Field1", "Field2", 'Field15', 'Price report By Price Group ', 'as at ' . $StartDate,"100", $sortCount);

     $sortCount = $sortCount+1;
     $ReportsDAO = new ReportsDAO($dbConn);
     $iTP = $ReportsDAO->insertIntoTempPrice($userId, "Field1", "Field2", 'Field15', '', '',"100", $sortCount);

$ReportsDAO = new ReportsDAO($dbConn);
$gCBP = $ReportsDAO->getPriceGroups($principalId, $grouplist);

if (count($gCBP) > 105) {
     ?>
         <script type='text/javascript' >parent.showMsgBoxError("Report Cannot continue - Temp Table To Short - Contact Kwelanga Support<BR><BR>")</script> 
	   <?php
	 return;
}

$sortCount = $sortCount+1;
$ReportsDAO = new ReportsDAO($dbConn);
$iTP = $ReportsDAO->insertIntoTempPrice($userId, "Field1", "Field2", 'Field15', "Product/Group", '',"100", $sortCount);

$fldcount = 2;
$totalPrecords = 0;

foreach($gCBP as $row1) {

     $ReportsDAO = new ReportsDAO($dbConn);
     $uTP = $ReportsDAO->updateTempPrice($userId, "Field" . trim($fldcount), $row1['Chain'], "Product/Group");

     $ReportsDAO = new ReportsDAO($dbConn);
     $gPBProd = $ReportsDAO->getPriceRecords($principalId, $StartDate, $row1['uid']);

     $ReportsDAO = new ReportsDAO($dbConn);
     $gPBCat = $ReportsDAO->getCatagoryPriceRecords($principalId, $StartDate, $row1['uid']);

     
     $gPBP = array_merge($gPBProd, $gPBCat);
     
//     echo "<pre>";
     
//     print_r($gPBP);
     
     $totalPrecords = $totalPrecords + count($gPBP);
     
     if ($totalPrecords == 0) {     	
     		   ?>
                <script type='text/javascript' >parent.showMsgBoxError("Report Cannot continue - No active prices found for  on this Date<BR><BR>")</script> 
                
	         <?php
     	     return;
     }
     
 
    foreach($gPBP as $row2) {
    	  // Check file price exists
        $ReportsDAO = new ReportsDAO($dbConn);
        $gREC = $ReportsDAO->checkIfPriceRecordExists($row2['principal_product_uid'], $userId, "Field1" );

        $ReportsDAO = new ReportsDAO($dbConn);
        $catREC = $ReportsDAO->checkIfCatagoryRecordExists($row2['Catagory'], $userId, "Field1" );

        if ($row2['principal_product_uid'] == VAL_DEALTYPE_AMOUNT_OFF ) {
         	   $activeprice = $row2['list_price'] - $row2['discount_value'] ;        	 	
        } elseif ($row2['principal_product_uid'] == VAL_DEALTYPE_PERCENTAGE ) {
      	 	   $activeprice = $row2['list_price'] - ($row2['list_price'] * $row2['discount_value'] / 100);
        } else {
       	     $activeprice = $row2['list_price'];
        }
        if (count($gREC) == 0) {
            
            if ($catagoryStore <>  $row2['Catagory']) {
            	       $sortCount = $sortCount+1;
                     $catagoryStore = $row2['Catagory'];
                     $sortCount = $sortCount+1;
                     if (count($catREC) == 0) {
                         $ReportsDAO = new ReportsDAO($dbConn);
                         $iTP = $ReportsDAO->insertIntoTempPrice($userId, "Field1", "Field2", 'Field15', $row2['Catagory'] , '',$row2['CatagoryOrder'], "000");             	                     	
                     }
            }
            $sortCount = $sortCount+1;
         	  $ReportsDAO = new ReportsDAO($dbConn);
            $iTP = $ReportsDAO->insertIntoTempPrice($userId, "Field1", "Field" . trim($fldcount), 'Field15', $row2['principal_product_uid'], $activeprice , $row2['CatagoryOrder'], $sortCount);
        } else {
       	     $uTP = $ReportsDAO->updateTempPrice($userId, "Field" . trim($fldcount), $activeprice, $row2['principal_product_uid']);
       	}
     }
     $fldcount++;	
}
     $sortCount = $sortCount+1;
     $ReportsDAO = new ReportsDAO($dbConn);
     $iTP = $ReportsDAO->insertIntoTempPrice($userId, "Field1", "Field2", 'Field15', '', '',"900", $sortCount);
 
     $sortCount = $sortCount+1;
     $ReportsDAO = new ReportsDAO($dbConn);
     $iTP = $ReportsDAO->insertIntoTempPrice($userId, "Field1", "Field2", 'Field15', '---- End of Report ---- ', '',"900", $sortCount);

// Temp Table SQL

$csv_export = '';

$tesql =  "select if(`Field15` between 201 and 899 && `sort` <> 0 , concat(trim(pp.product_code), ' - ', trim(product_description)) ,`Field1`) as 'Field1',
                   `Field2`,
                   `Field3`,
                   `Field4`,
                   `Field5`,
                   `Field6`,
                   `Field7`
         from temp_price_report_" . $userId ." tpr
         left join principal_product pp on tpr.Field1 = pp.uid
         where 1
         order by `Field15`, `sort`" ;
 
$utresult = $dbConn->dbGetAll($tesql);

//print_r($utresult);

if (count($utresult) == 0) {
	   ?>
     <script type='text/javascript' >parent.showMsgBoxError("No Rows Selected<BR><BR>")</script> 
	   <?php
	   return;	
}


foreach (array_keys($utresult[0]) as $arow) {
  if (substr($arow,0,5) <> 'Field' && substr($arow,0,4) <> 'sort' ) {
     $csv_export.= $arow . ',';
  }

}
  $csv_export.= "\n";

foreach ($utresult as $brow) {
	$csv_export.= implode(',',$brow) . "\n";
}

$fileName = "Active Price Report.csv";

  header("Content-Description: File Transfer");
  header("Content-Disposition: attachment; filename=\"".$fileName."\"");
  header("Content-Type: application/force-download");
  echo $csv_export;

$bldsql = "DROP TABLE IF EXISTS temp_price_report_" . $userId . ";   ";

$dtresult = $dbConn->dbQuery($bldsql);
$dbConn->dbQuery("commit");

return;

?>
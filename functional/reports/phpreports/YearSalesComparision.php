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

print_r($paramsArr);

$YearDate     = $paramsArr['p1'];

// Create temporary table user specfic

$bldsql = "DROP TABLE IF EXISTS temp_sales_report_" . $userId . ";   ";

$dtresult = $dbConn->dbQuery($bldsql);
$dbConn->dbQuery("commit");

$bldsql = "CREATE TABLE temp_sales_report_" . $userId . " (`Field1` VARCHAR(40) NULL,
                                     `Field2`  VARCHAR(40) NULL,
                                     `Field3`  VARCHAR(40) NULL,
                                     `Field4`  VARCHAR(40) NULL,
                                     `Field5`  VARCHAR(40) NULL,
                                     `Field6`  VARCHAR(40) NULL,
                                     `Field7`  VARCHAR(40) NULL,
                                     `Field8`  VARCHAR(40) NULL,
                                     `Field9`  VARCHAR(40) NULL,
                                     `Field10` VARCHAR(40) NULL,
                                     `Field11` VARCHAR(40) NULL,
                                     `Field12` VARCHAR(40) NULL,
                                     `Field13` VARCHAR(40) NULL,
                                     `Field14` VARCHAR(40) NULL,
                                     `Field15` VARCHAR(40) NULL,
                                     `Field16` VARCHAR(40) NULL,
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
     $iTP = $ReportsDAO->insertIntoGeneralTemp($userId,
                                               $row1['Principal'],   // 1
                                               '',   // 2
                                               '',   // 3
                                               '',   // 4
                                               '',   // 5
                                               '',   // 6
                                               '',   // 7
                                               '',   // 8
                                               '',   // 9 
                                               '',   // 10
                                               '',   // 11
                                               '',   // 12
                                               '',   // 13
                                               date("Y-m-d H:i"),   // 14
                                               '',   // 15
                                               $sortCount);
}
     $sortCount = $sortCount+1;
     $ReportsDAO = new ReportsDAO($dbConn);
     $iTP = $ReportsDAO->insertIntoGeneralTemp($userId,
                                               'Year',  // 1    
                                               'Group', // 2    
                                               'Jan',   // 3    
                                               'Feb',   // 4    
                                               'Mar',   // 5    
                                               'Apr',   // 6    
                                               'May',   // 7    
                                               'Jun',   // 8    
                                               'Jul',   // 9    
                                               'Aug',   // 10   
                                               'Sep',   // 11   
                                               'Oct',   // 12   
                                               'Nov',   // 13   
                                               'Dec',   // 14   
                                               ''   ,   // 15   
                                               $sortCount);
     
     $sortCount = $sortCount+1;
     $ReportsDAO = new ReportsDAO($dbConn);
     $iTP = $ReportsDAO->insertIntoGeneralTemp($userId,
                                               '',   // 1
                                               '',   // 2
                                               '',   // 3
                                               '',   // 4
                                               '',   // 5
                                               '',   // 6
                                               '',   // 7
                                               '',   // 8
                                               '',   // 9 
                                               '',   // 10
                                               '',   // 11
                                               '',   // 12
                                               '',   // 13
                                               '',   // 14
                                               '',   // 15
                                               $sortCount);

      // Extract previous years monthly sales

      $ReportsDAO = new ReportsDAO($dbConn);
      $gSBP = $ReportsDAO->getMonthlyPrincipalSales($principalId, $YearDate-1);
      
      foreach($gSBP as $row1) {
            // Check for existing record
               $ReportsDAO = new ReportsDAO($dbConn);
               $ycKey = $row1['Year'] . $row1['ChainId'] . 'A';
               $eRec = $ReportsDAO->CheckForExistingRec($userId, $ycKey);
 
 
               
               if (count($eRec) == 0) {
                     if($row1['MM']==1)  {$janTotal = $row1['Total'];} else {$janTotal = '';}   // 3
                     if($row1['MM']==2)  {$febTotal = $row1['Total'];} else {$febTotal = '';}   // 4
                     if($row1['MM']==3)  {$marTotal = $row1['Total'];} else {$marTotal = '';}   // 5
                     if($row1['MM']==4)  {$aprTotal = $row1['Total'];} else {$aprTotal = '';}   // 6
                     if($row1['MM']==5)  {$mayTotal = $row1['Total'];} else {$mayTotal = '';}   // 7
                     if($row1['MM']==6)  {$junTotal = $row1['Total'];} else {$junTotal = '';}   // 8
                     if($row1['MM']==7)  {$julTotal = $row1['Total'];} else {$julTotal = '';}   // 9 
                     if($row1['MM']==8)  {$augTotal = $row1['Total'];} else {$augTotal = '';}   // 10
                     if($row1['MM']==9)  {$sepTotal = $row1['Total'];} else {$sepTotal = '';}   // 11
                     if($row1['MM']==10) {$octTotal = $row1['Total'];} else {$octTotal = '';}   // 12
                     if($row1['MM']==11) {$novTotal = $row1['Total'];} else {$novTotal = '';}   // 13
                     if($row1['MM']==12) {$decTotal = $row1['Total'];} else {$decTotal = '';}   // 14
                     $ReportsDAO = new ReportsDAO($dbConn);
                     $iTP = $ReportsDAO->insertIntoGeneralTemp($userId,
                                               $row1['Year'],                                    // 1
                                               $row1['Chain'],                                   // 2
                                               $janTotal,   // 3
                                               $febTotal,   // 4
                                               $marTotal,   // 5
                                               $aprTotal,   // 6
                                               $mayTotal,   // 7
                                               $junTotal,   // 8
                                               $julTotal,   // 9 
                                               $augTotal,   // 10
                                               $sepTotal,   // 11
                                               $octTotal,   // 12
                                               $novTotal,   // 13
                                               $decTotal,   // 14
                                               $row1['Year'] . $row1['ChainId'] . 'A',           // 15
                                               '5');                   
               } else {
                     if($row1['MM']    ==1) { $fld = '`Field3`'; }
                     elseif($row1['MM']==2) { $fld = '`Field4`'; }
                     elseif($row1['MM']==3) { $fld = '`Field5`'; }
                     elseif($row1['MM']==4) { $fld = '`Field6`'; }
                     elseif($row1['MM']==5) { $fld = '`Field7`'; }
                     elseif($row1['MM']==6) { $fld = '`Field8`'; }
                     elseif($row1['MM']==7) { $fld = '`Field9`'; }
                     elseif($row1['MM']==8) { $fld = '`Field10`'; }
                     elseif($row1['MM']==9) { $fld = '`Field11`'; }
                     elseif($row1['MM']==10) { $fld = '`Field12`'; }
                     elseif($row1['MM']==11) { $fld = '`Field13`'; }
                     elseif($row1['MM']==12) { $fld = '`Field14`'; }
                     $ReportsDAO = new ReportsDAO($dbConn);
                     $iTP = $ReportsDAO->UpdateGeneralTemp($userId, $fld, $row1['Total'], $ycKey);
               }
      }	      
      

      // Extract Current years monthly sales

      $ReportsDAO = new ReportsDAO($dbConn);
      $gSBP = $ReportsDAO->getMonthlyPrincipalSales($principalId, $YearDate-1);
      
      foreach($gSBP as $row1) {
            // Check for existing record
               $ReportsDAO = new ReportsDAO($dbConn);
               $ycKey = $row1['Year'] . $row1['ChainId'] . 'B';
               $eRec = $ReportsDAO->CheckForExistingRec($userId, $ycKey);
               
               if (count($eRec) == 0) {
                     if($row1['MM']==1)  {$janTotal = $row1['Total'];} else {$janTotal = '';}   // 3
                     if($row1['MM']==2)  {$febTotal = $row1['Total'];} else {$febTotal = '';}   // 4
                     if($row1['MM']==3)  {$marTotal = $row1['Total'];} else {$marTotal = '';}   // 5
                     if($row1['MM']==4)  {$aprTotal = $row1['Total'];} else {$aprTotal = '';}   // 6
                     if($row1['MM']==5)  {$mayTotal = $row1['Total'];} else {$mayTotal = '';}   // 7
                     if($row1['MM']==6)  {$junTotal = $row1['Total'];} else {$junTotal = '';}   // 8
                     if($row1['MM']==7)  {$julTotal = $row1['Total'];} else {$julTotal = '';}   // 9 
                     if($row1['MM']==8)  {$augTotal = $row1['Total'];} else {$augTotal = '';}   // 10
                     if($row1['MM']==9)  {$sepTotal = $row1['Total'];} else {$sepTotal = '';}   // 11
                     if($row1['MM']==10) {$octTotal = $row1['Total'];} else {$octTotal = '';}   // 12
                     if($row1['MM']==11) {$novTotal = $row1['Total'];} else {$novTotal = '';}   // 13
                     if($row1['MM']==12) {$decTotal = $row1['Total'];} else {$decTotal = '';}   // 14
                     
                     $ReportsDAO = new ReportsDAO($dbConn);
                     $iTP = $ReportsDAO->insertIntoGeneralTemp($userId,
                                               $row1['Year'],                                    // 1
                                               $row1['Chain'],                                   // 2
                                               $janTotal,   // 3
                                               $febTotal,   // 4
                                               $marTotal,   // 5
                                               $aprTotal,   // 6
                                               $mayTotal,   // 7
                                               $junTotal,   // 8
                                               $julTotal,   // 9 
                                               $augTotal,   // 10
                                               $sepTotal,   // 11
                                               $octTotal,   // 12
                                               $novTotal,   // 13
                                               $decTotal,   // 14
                                               $row1['Year'] . $row1['ChainId'] .'B',                 // 15
                                               '5') ;                  
               } else {
                     if($row1['MM']    ==1) { $fld = '`Field3`'; }
                     elseif($row1['MM']==2) { $fld = '`Field4`'; }
                     elseif($row1['MM']==2) { $fld = '`Field5`'; }
                     elseif($row1['MM']==2) { $fld = '`Field6`'; }
                     elseif($row1['MM']==2) { $fld = '`Field7`'; }
                     elseif($row1['MM']==2) { $fld = '`Field8`'; }
                     elseif($row1['MM']==2) { $fld = '`Field9`'; }
                     elseif($row1['MM']==2) { $fld = '`Field10`'; }
                     elseif($row1['MM']==2) { $fld = '`Field11`'; }
                     elseif($row1['MM']==2) { $fld = '`Field12`'; }
                     
                     $ReportsDAO = new ReportsDAO($dbConn);
                     $iTP = $ReportsDAO->UpdateGeneralTemp($userId, $fld, $row1['Total'], $ycKey);
               }
      }	      

// Calculate Totals and percentages



return;

?>
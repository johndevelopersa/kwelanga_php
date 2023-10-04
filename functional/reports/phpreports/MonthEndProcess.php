<?php
// http://www.kwelangasolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/reports/phpreports/MonthEndProcess.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/ReportsDAO.php");
include_once($ROOT.$PHPFOLDER.'properties/ServerConstants.php'); 

global $paramsArr, $principalId;

$dbConn = new dbConnect();
$dbConn->dbConnection();

$StartDate = $paramsArr['p1'];
$EndDate   = $paramsArr['p2'];
$principalArray   = $paramsArr['p3'];
$filestore = DIR_DATA_NON_FTP_FROM ;
$fcharge = $debtfee = $turnover = $ordertot = $printot = 0;

// Get principal details for processing


$ReportsDAO = new ReportsDAO($dbConn);
$tt = $ReportsDAO->CreateTempBillingTransactionTable();

$ReportsDAO = new ReportsDAO($dbConn);
$gSBP = $ReportsDAO->getBillingPrincipalDetailsArray($principalArray);

foreach($gSBP as $row1) {
	
	     // Report type 1 is the document Charge report 

        if($row1['report_type'] == 1) {
           $ReportsDAO = new ReportsDAO($dbConn);
           $docresult = $ReportsDAO->SetBillingDocumentCharge($row1['principal_uid'],
                                                              $row1['charge'],
                                                              trim($row1['document_types']),
                                                              $StartDate,
                                                              $EndDate,
                                                              $row1['warehouses_to_exclude'],
                                                              $row1['report_type'],
                                                              $filestore);

           $ReportsDAO = new ReportsDAO($dbConn);
           $tt = $ReportsDAO->InsertIntoBillingTransaction($row1['principal_uid'],
                                                           $row1['principal_product_uid'],
                                                           $row1['charge'],                                                             
                                                           $docresult[0]['total'],
                                                           $cost = $docresult[0]['total'] * $row1['charge'],
                                                           '',
                                                           '',
                                                           $StartDate, 
                                                           $EndDate);
              
        } elseif ($row1['report_type'] == 2) {
             $ReportsDAO = new ReportsDAO($dbConn);
             $toresult = $ReportsDAO->SetBillingTurnoverCharge($row1['principal_uid'],
                                                               $row1['charge'],
                                                               trim($row1['document_types']),
                                                               $StartDate,
                                                               $EndDate,
                                                               $row1['warehouses_to_exclude'],
                                                               $row1['report_type'],
                                                               $filestore,
                                                               $row1['document_status']);
          
             $ReportsDAO = new ReportsDAO($dbConn);
             $tt = $ReportsDAO->InsertIntoBillingTransaction($row1['principal_uid'],
                                                             $row1['principal_product_uid'],
                                                             $cost = $toresult[0]['total'] * $row1['charge'] / 100,    
                                                             '1',
                                                             $cost = $toresult[0]['total'] * $row1['charge'] / 100,    
                                                             $toresult[0]['total'],
                                                             $row1['charge'], 
                                                             $StartDate,
                                                             $EndDate);

        } elseif ($row1['report_type'] == 7) {
           $ReportsDAO = new ReportsDAO($dbConn);
           $tt = $ReportsDAO->InsertIntoBillingTransaction($row1['principal_uid'],
                                                           $row1['principal_product_uid'],
                                                           $row1['charge'],                                                             
                                                           '1',
                                                           $cost = 1 * $row1['charge'],    
                                                           '',
                                                           '',
                                                           $StartDate,    
                                                           $EndDate);     
        }	else {
                  
           $ReportsDAO = new ReportsDAO($dbConn);
           $tt = $ReportsDAO->InsertIntoBillingTransaction($row1['principal_uid'],
                                                           $row1['principal_product_uid'],
                                                           $row1['charge'],                                                             
                                                           '1',
                                                           $cost = 1 * $row1['charge'],
                                                           '',
                                                           '',
                                                           $StartDate,    
                                                           $EndDate);
        }
}

           $ReportsDAO = new ReportsDAO($dbConn);
           $tt = $ReportsDAO->CalculateMinimumCharge() ;
           
           $ReportsDAO = new ReportsDAO($dbConn);
           $tt = $ReportsDAO->ExtractSystemOrders($EndDate, $filestore);
           
           

            echo '<br>';
echo "End";
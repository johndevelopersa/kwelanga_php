<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/invoiceUpdate/uploadGlacialIncomingFile.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/glacialUpdateDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/ServerConstants.php');




//Create new database object
$dbConn  = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;

$glacialUpdateDAO = new glacialUpdateDAO($dbConn);
$fResult = $glacialUpdateDAO->getGlacialPrinInvoiceFiles();

$principalId = $fResult[0]['principal_uid'];

require_once($ROOT . $PHPFOLDER . "properties/" . "Omni_Constants_" . $fResult[0]['principal_uid'] . ".php");

$dirPath = "C:/inetpub/wwwroot/systems/kwelanga_system/"  . $fResult[0]['file_path'] . $fResult[0]['file_wildcard'];

echo $dirPath;
      
$files = glob($dirPath);


$aa = Array
(
    [username] => IDO
    [password] => yF!+KssJr-Ca8yM=NX
    [requireddata] => postOrder
    [principalId] => 216
    [reference_number] => 3922779766990
    [customer_account_code] => 5464371396814
    [customer_name] => Joel Van Zyl
    [physical_address_1] => 29 Musilis Avenue, northcligg 
    [physical_address_2] => Northcliff
    [physical_address_3] => Johannesburg 
    [physical_address_4] => 2196
    [physical_address_5] => 
    [region] => Gauteng
    [shipping] => 100.00
    [order_discount_type] => 
    [order_discount_amount] => 0.00
    [email_address] => joel1980@gmail.com
    [contact_number] => 1
    [purchase_order_number] => 1174
    [order_date] => 22-08-09
    [required_date] => 
    [delivery_instructions] => 
    [detail_lines] => Array
        (
            [0] => Array
                (
                    [product_code] => TC034
                    [product_description] => Herencia | Añejo Tequila  750ML
                    [order_quantity] => 1
                    [selling_price] => 589.00
                    [line_discount_type] => 
                    [line_discount_amount] => 0.00
                    [line_nett_price] => 589.00
                    [vat_rate] => 15.00
                )

        )

);

echo json_encode($aa);

die();


      
if(!empty($files)) {
      foreach($files as $frow) {
              if(file_exists($frow)) {
                     $content=file_get_contents($frow);
//                   echo "<br>";
//                   echo(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\xBD\x22\xA0\x96\x91\x92\x27\x99\xAE\x00]/', '', $content));
                     file_put_contents($frow, preg_replace('/[\xFF\xFE\xEF\xBB\xBF\xBD\x22\xA0\x96\x91\x92\x27\x99\xAE\x00]/', '', $content));

                     $glacialUpdateDAO = new glacialUpdateDAO($dbConn);
                     $errorTO = $glacialUpdateDAO->loadGlacialFile($frow);
/*  
                     $glacialUpdateDAO = new glacialUpdateDAO($dbConn);
                     $errorTO = $GdsStockDAO->clearStockBalances(354);
                   
                     if($errorTO->type == 'S') { 
              	          echo basename($frow);
                          $GdsStockDAO = new GdsStockDAO($dbConn);
                          
                          $fdate = substr(basename($frow),27,4) . '-' . substr(basename($frow),25,2) . '-' . substr(basename($frow),23,2) ;
                          
                          $errorTO = $GdsStockDAO->loadAvailableStock(354, $fdate);
                          
                          $runTime=CommonUtils::getGMTimeCompressed(0); // for filename

                          $bkupFolderSuccess=CommonUtils::createBkupDirs($dirPath,"1");
                          if ($bkupFolderSuccess===false) {
                               echo "Could not create bkup folders in {$dirPath}1";
                               BroadcastingUtils::sendAlertEmail("Could not here create bkup folders during onlineFileProcessing!","Could not create bkup folders in {$path}1","Y");
                               continue;
                          }
                          
                          $bkupFolderError=CommonUtils::createBkupDirs($dirPath,"2");
                          if ($bkupFolderError===false) {
                               echo "Could not create bkup folders in {$dirPath} 2";
                               BroadcastingUtils::sendAlertEmail("Could not create bkup folders during GDS Stock Import!","Could not create bkup folders in {$path}2","Y");
                               continue;
                          }
                         if($errorTO->type == 'S') {
                            
                               $dbConn->dbQuery("commit");
                                                   
                               echo "<br><br>";
                               echo "Load Stock file Successful";
                               echo "<br><br>";
                               echo "[***EOS***]";
                               echo "<br><br>"; 
                               
                               if (rename($dirPath.basename($frow), $bkupFolderSuccess.basename($frow).".".$runTime)) {
                                    return array(true,"");
                               } else {
                                    return array(false,"failed to move file");
                               }
                         } else {
                               echo "<br><br>";
                               echo "Load Stock file Failed";
                               echo "<br><br>";
                               echo "***End***";
                               echo "<br><br>";
                               if (rename($dirPath.basename($frow), $bkupFolderError.basename($frow).".".$runTime)) {
                                    return array(true,"");
                               } else {
                                    return array(false,"failed to move file");
                               }
                         }                    
                     } else { 
                         echo "<br><br>";
                         echo "Clear Stck Balances failed";
                         echo "<br><br>";
                         echo "***End***";
                         echo "<br><br>"; 

                         if (rename($dirPath.basename($frow), $bkupFolderError.basename($frow).".".$runTime)) {
                                    return array(true,"");
                         } else {
                                    return array(false,"failed to move file");
                         }
                     }
*/                     
              } 
      }       
} else {
         	    echo "<br><br>";
              echo "No Invoice Files to process";
              echo "<br><br>";
              echo "[***EOS***]";
              echo "<br><br>";        	
      }
 ?>

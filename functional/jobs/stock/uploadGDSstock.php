<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/stock/uploadGDSstock.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/GdsStockDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");

//Create new database object
$dbConn  = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;
      $dirPath = "C:/inetpub/wwwroot/systems/kwelanga_system/ftp/gdsreports/";
        
      $files = glob($dirPath . "STOCK MODEL*.csv");
      
      $GdsStockDAO = new GdsStockDAO($dbConn);
              
      $result = $GdsStockDAO->deleteGdsTemp();

      $GdsStockDAO = new GdsStockDAO($dbConn);
      $result = $GdsStockDAO->createGdsTemp();   	
      
      if(!empty($files)) {
          foreach($files as $frow) {
              if(file_exists($frow)) {
                     $content=file_get_contents($frow);
                     echo "<br>";
//                     echo(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\xBD\x22\xA0\x96\x91\x92\x27\x99\xAE\x00]/', '', $content));
                     file_put_contents($frow, preg_replace('/[\xFF\xFE\xEF\xBB\xBF\xBD\x22\xA0\x96\x91\x92\x27\x99\xAE\x00]/', '', $content));

                     $GdsStockDAO = new GdsStockDAO($dbConn);
                     $errorTO = $GdsStockDAO->loadGdsFile($frow);

                     $GdsStockDAO = new GdsStockDAO($dbConn);
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
              } 
          }       
      } else {
         	    echo "<br><br>";
              echo "No Stock Files to process";
              echo "<br><br>";
              echo "[***EOS***]";
              echo "<br><br>";        	
      }
 ?>

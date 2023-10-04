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

//static method handler.
class WilmarStock {
  public static function generateOutput(){
  	

  	
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class WilmarStockInit extends extractController {

  private $principalUid = 351; //uid of principal extract.
  private $filename = 'KOSXX[@FSEQ].dat';  //main controller will build full filename with seq. for us.
  
  public function generateOutput(){

      global $ROOT, $PHPFOLDER;

      //name in email and folder to place bkup files.
      $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
      if (count($pArr)==0) {
        BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
         return $this->errorTO;
      }
      $principalName = $pArr[0]['principal_name'];
      $folder = $this->principalUid . '_' . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.

      //use the receipients listed in the notification table instead of hard coding them!!!
      //expecting only one row loaded per principal extract
      $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_ALTCUSTOM2);
      if (count($reArr)==0) {
         BroadcastingUtils::sendAlertEmail("System Error", "Export failed load recipients in ".get_class($this)."!", "Y");
         return $this->errorTO;
      }

      $recipientUId = $reArr[0]['uid'];
    
      // Check stock balances for all warehouses
    
      $statusLoop        = Array("allocations", "in_pick");
      $interval          = 45;
      
      $principalsToCheck = $this->principalUid;	 
      
      $depotList         = '190,384';
      
      $depotTrans = array(195  => '10',  // Loginet
                          202  => 'N01', // TK
                          384  => '91',
                          190  => '90' );
    
      $updatedRow        = "";
      
      foreach($statusLoop as $loopRow) {	
	         if($loopRow =="allocations" ) {	    	
	     	        $statusList  = array(DST_UNACCEPTED, DST_ACCEPTED);
	     	        $docTypeList = array(DT_ORDINV,DT_DELIVERYNOTE,DT_ORDINV_ZERO_PRICE);
           } elseif ($loopRow == "in_pick") {
	        	     $statusList  = array(DST_INPICK);
	     	         $docTypeList = array(DT_ORDINV, DT_DELIVERYNOTE, DT_ORDINV_ZERO_PRICE);
	         }	
	         
	         include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
           include_once ($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php"); 
           include_once ($ROOT.$PHPFOLDER."DAO/ExtractDAO.php");   

  	       $dbConn = new dbConnect();
           $dbConn->dbConnection();
           
           $MaintenanceDAO = new MaintenanceDAO($dbConn);
           $aSr = $MaintenanceDAO->clearExistingBalances($principalsToCheck, $depotList, $loopRow);
     
          $MaintenanceDAO = new MaintenanceDAO($dbConn);
          $aSr = $MaintenanceDAO->getAllStockRecords($this->principalUid, $depotList, $interval, implode(",",$statusList), implode(",",$docTypeList) );
     
          $firstPrinDep = '';
          foreach($aSr as $row) {
      	       if($row['principal_uid'] . $row['depot_uid'] <> $firstPrinDep  )	{
      	          $firstPrinDep = $row['principal_uid'] . $row['depot_uid'];
      	          $updatedRow = $updatedRow . "Prin " . $row['principal_uid'] . "  Warehouse " . $row['depot_uid'] . "  Type - " . $loopRow . "<br>";
               }
          	
          	   $MaintenanceDAO = new MaintenanceDAO($dbConn);
               $aSr = $MaintenanceDAO->updateBalances($row['principal_uid'], $row['depot_uid'], $row['Quantity'], $row['product_uid'], $loopRow);
               if($aSr <> 'S') {
                	echo "Balance Update Failed" ;
               }
          }	
          $updatedRow = $updatedRow . "Prin " . $row['principal_uid'] . "  Warehouse " . $row['depot_uid'] . "  Type - " . $loopRow . "<br>"; 
         
      }

      $MaintenanceDAO = new MaintenanceDAO($dbConn);
      $aSr = $MaintenanceDAO->recalcalculateStockBalance();
      
      // Extract current stock
      
      $ExtractDAO = new ExtractDAO($dbConn);
      $stckRecs = $ExtractDAO->getCurrentStockRecords($this->principalUid, $depotList);
      
      $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid, false, 6);

      $filecount = 0; 

      $fileHeader = array();
      $fileHeader[] = '000';                                                        // RecordType
      $fileHeader[] = '1';                                                          // Version
      $fileHeader[] = '6001651048339';                                              // PartnerGUID x 13 m 
      $fileHeader[] = '6009668780006';                                              // CompanyGUID x 13 m 
      $fileHeader[] = date("Ymd") ;                                                 // DateOfPreparation d  m 
      $fileHeader[] = date("Hi");                                                   // TimeOfPreparation t  m 
      $fileHeader[] = substr($seqFilename,5,4);                                     // PartnerTransmissionNumber n  m sequential and unique per partner
      $fileHeader[] = substr($seqFilename,5,4);                                     // SwitchTransmissionNumber n  m sequential and unique per switch
      $fileHeader[] = date("d") ;   //FinancialDay#	n
      $fileHeader[] = date("m") ;   //FinancialMonth#	n
      $fileHeader[] = date("Y") ;   //FinancialYear#
      $fileHeader[] = '';                                                           // <do not delete>    
      $filecount++; 
      $dataArr[] = join('|', $fileHeader);
      $StockHeader = array();
      $StockHeader[] = '240';                                                        // RecordType
      $StockHeader[] = '1';                                            // <do not delete>    
      $filecount++;
      $ordCount++;
      $dataArr[] = join('|', $StockHeader);
      
      foreach ($stckRecs as $row) {
      	   $regionArr = (isset($depotTrans[$row['depot_id']])) ? $depotTrans[$row['depot_id']] : false; 
           $detArr = array();
           $detArr[] = '241'; 
           $detArr[] = $regionArr;   // DcCode	x
           $detArr[] = trim(str_replace(array('"'),array(''),$row['stock_item'])); // PrincipalStockCode	x	
           $detArr[] = $row['closing'];                                           // ClosingBalance	n	sale units - nb! may be negative
           $detArr[] = $row['Alloc'];                                              // Aloocated	n	sale units allocated to sales orders
           $detArr[] = trim($row['items_per_case']);                               // SinglesPerSaleUnit	n	
           $detArr[] = trim(str_replace(array('"'),array(''),$row['stock_item'])); // StockCode	x		
           $detArr[] = $row['sku_gtin'];                                             // SaleUnitGuid	x	
           $detArr[] = 'CASE';                                                       // SaleUnitLevel	x	“SINGLE”,”SHRINK”,”CASE”
           $detArr[] = '';  
           $fileType = 'C';
           $filecount++;
           $ordCount++;
           $dataArr[] = join('|', $detArr);
      }
      
      $stArr[] = '249';          //     RecordType	x	"249"
      $filecount++;
      $ordCount++;
      $stArr[] = $ordCount;    //	NrOfMessageRecords	n	
      $stArr[] = ''; 		 
      $dataArr[] = join('|', $stArr);
      
      $filecount++;
      $fileTrl = array();
      $fileTrl[] = '009';                                                        // RecordType
      $fileTrl[] = trim($filecount);
      $dataArr[] = join('|',$fileTrl); 
      
      $data = join("\r\n",$dataArr);
      //write physical file
             
      $filePath = $this->createFile($folder, str_replace('XX',$fileType,$seqFilename), $data);  //places file in correct folder
           
      if($filePath == false){
            BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
            return $this->errorTO;
      }
         // SETUP DISTRIBUTION
         $postingDistributionTO = new PostingDistributionTO;
         $postingDistributionTO->DMLType = "INSERT";
         $postingDistributionTO->deliveryType = BT_EMAIL;
         $postingDistributionTO->subject = $this->getTemplateStockBalSubject();
         $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
         if($filePath!=false){
			//$postingDistributionTO->attachmentFile = $filePath;
			$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, str_replace('/bkup/','/',$filePath));	
         }

         $recipientList = explode(",", $reArr[0]["user_uid_list"]); //if blank, sizeof() will still be 1
         $recipientsCheckCount = 0;  //check if atleast one distribution item was posted, incase of invalid recipients

         foreach($recipientList as $re){

            $mfC = $this->miscDAO->getContactItem($this->principalUid, "", $re);
            if (sizeof($mfC)==0) {
               BroadcastingUtils::sendAlertEmail("System Error",get_class($this)." Extract for nr.UID {$recipientUId} has an invalid Recipient/Contact: '{$re}'.","Y", true);
               continue;
            }

            $postingDistributionTO->destinationAddr = $mfC[0]["email_addr"];
            $dResult = $this->postDistributionDAO->postQueueDistribution($postingDistributionTO);
            if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->type=FLAG_ERRORTO_ERROR;
                $this->errorTO->description = get_class($this)." Extract distribution queue failed for for nr.UID {$recipientUId} and Recipient/Contact: '{$re}'.";
                BroadcastingUtils::sendAlertEmail("System Error", $this->errorTO->description, "Y", true);
                return $this->errorTO;
            } else {
                $recipientsCheckCount++;  //successful
            }
         }
         if ($recipientsCheckCount==0) {
             $this->errorTO->type = FLAG_ERRORTO_ERROR;
             $this->errorTO->description = "Failed in ".get_class($this)." extract no valid Recipient/Contact found, no outgoing mail generated!";
             BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
             return $this->errorTO;
         }




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
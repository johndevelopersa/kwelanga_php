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
class CreateStandingOrders207 {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class CreateStandingOrders207Init extends extractController {

  private $principalUid = 207; //uid of principal extract.
  private $filename = 'ORD207[@FSEQ].csv';  //main controller will build full filename with seq. for us.
  public function generateOutput(){

    global $ROOT, $PHPFOLDER ;
    
     //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();

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
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_CUSTOM);
    if (count($reArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];

    if (!$this->skipInsert) {
      // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
      $rTO = $this->postExtractDAO->queueStandingOrders($this->principalUid, $recipientUId, $inclCancelled = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueStandingOrders in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }

    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($this->principalUid, $recipientUId);

    /*  SUCCESS POINT - 1  */
    //nothing to do...
    if(count($seDocs)==0){
      echo "Successfully Completed Extract : ".get_class($this)." - No entries!<br>";
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
      return $this->errorTO;
    }

    //group array
    $grpDocs = array();
    $psms=array();
    foreach($seDocs as $k=>$r){
      $type = 'i';
      $grpDocs[$type][$r['dm_uid']][] = $r;
    }

    $linecount = 0;  
    foreach($grpDocs as $type => $orders){

         $dataArr = array();
         $errorSEUIdArr = array();
         $successSEUIdArr = array();
         $successCount = 0;
         include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");

         foreach($orders as $ord){
         	
         	print_r($ord)
         	
                /*-------------------------------------------------*/
                /*            START BUILDING OUTPUT
                /*-------------------------------------------------*/
                $successCount++;
                $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

//*********************************************
                $storeDAO = new StoreDAO($dbConn);
                $FsSt = $storeDAO->getFreeStockStoreDetails($ord[0]['principal_store_uid']);
 	              $dataH = array();                                                                                                                                                                                                                                                          
                $dataH[] = 'H';                                                                                                                                                                                                                                                                       
                $dataH[] = '1';                                                                                                                                                                                                                                                                     
                $dataH[] = $this->principalUid;                                                                                                                                                                                                                                                   
                $dataH[] = date("Ymd");
                if($ord[0]['order_required_month'] == '11') {
                     $dataH[] = 'November Order';	
                } elseif($ord[0]['order_required_month'] == '12') {
                     $dataH[] = 'December Order';	
                } elseif($ord[0]['order_required_month'] == '01') {
                     $dataH[] = 'January Order';	
                } elseif($ord[0]['order_required_month'] == '02') {
                     $dataH[] = 'February Order';	
                } elseif($ord[0]['order_required_month'] == '03') {
                     $dataH[] = 'March Order';	
                } elseif($ord[0]['order_required_month'] == '04') {
                     $dataH[] = 'April Order';	
                } elseif($ord[0]['order_required_month'] == '05') {
                     $dataH[] = 'May Order';	
                } elseif($ord[0]['order_required_month'] == '06') {
                     $dataH[] = 'June Order';	
                } elseif($ord[0]['order_required_month'] == '07') {
                     $dataH[] = 'July Order';	
                } elseif($ord[0]['order_required_month'] == '08') {
                     $dataH[] = 'Augus Order';	
                } elseif($ord[0]['order_required_month'] == '09') {
                     $dataH[] = 'September Order';	
                } elseif($ord[0]['order_required_month'] == '10') {
                     $dataH[] = 'October Order';	
                }
                $dataH[] = '';                                                                                                                                                                                                                                                             
                $dataH[] = str_replace(',',' ',$FsSt[0]['deliver_name'])  ;                                                                                                                                                                                                                                   
                $dataH[] = str_replace(',',' ',$FsSt[0]['deliver_add1']) ;                                                                                                                                                                                                                 
                $dataH[] = str_replace(',',' ',$FsSt[0]['deliver_add2']) ;                                                                                                                                                                                                                 
                $dataH[] = str_replace(',',' ',$FsSt[0]['deliver_add3']) ;                                                                                                                                                                                                                 
                $dataH[] = str_replace(',',' ',$FsSt[0]['bill_name']) ;                                                                                                                                                                                                                    
                $dataH[] = str_replace(',',' ',$FsSt[0]['bill_add1']) ;                                                                                                                                                                                                                    
                $dataH[] = str_replace(',',' ',$FsSt[0]['bill_add2']) ;                                                                                                                                                                                                                    
                $dataH[] = str_replace(',',' ',$FsSt[0]['bill_add3']) ;                                                                                                                                                                                                                    
                $dataH[] = $FsSt[0]['Warehouse_Uid'] ;                                                                                                                                                                                                                                     
                $dataH[] = $FsSt[0]['Warehouse_Name'] ;                                                                                                                                                                                                                                    
                $dataH[] = $FsSt[0]['Chain_Uid'] ;                                                                                                                                                                                                                                         
                $dataH[] = $FsSt[0]['Chain_Name'] ;                                                                                                                                                                                                                                        
                $dataH[] = '' ;                                                                                                                                                                                                                                                            
                $dataH[] = '';                                                                                                                                                                                                                                                             
                $dataH[] = $FsSt[0]['branch_code'] ;                                                                                                                                                                                                                                       
                $dataH[] = $FsSt[0]['old_account'] ;                                                                                                                                                                                                                                       
                $dataH[] = DT_QUOTATION ;                                                                                                                                                                                                                                                                                                                                                                                                   
 
                $linecount++;  
	
                $dataArr[] = join(",", $dataH);
         
                foreach($ord as $detailRow){ //detail rows.
                	
                  $dataD = array();                                                                                                                                                                                                                      
                  $dataD[] = 'D';                                                                                                                                                                                                                                                                                                                                                                           
//                  $dataD[] =  str_pad($detailRow->pageNo,2,"0",STR_PAD_LEFT) . str_pad($detailRow->lineNo,2,"0",STR_PAD_LEFT) ; 
                  $dataD[] =  $detailRow['product_code'];                                         
                  $dataD[] =  $detailRow['product_description'];                                  
                  $dataD[] =  abs($detailRow['ordered_qty']);                                              
                  $dataD[] =  number_format(round($detailRow['selling_price'], 2), 2, '.', '');             
                  $dataD[] =  round($detailRow['discount_value'],2);                                     
                                                                                                                                                                                                                                                                                                                                                                                                                                                            
                  $dataArr[] = join(',', $dataD);                                         $dataArr[] = join(',', $dataD);                                                                                                                                                                                                                                                                                                                                             
                  $linecount++;                                                           $linecount++;                                                                                                                                                                                                                                                                                                                                                               
                }       //eo detail     
                $linecount++;
       

         } //eo documents

         $data = join("\r\n",$dataArr);


         //create file only if there are successful items.
         $filePath = false;
         if(count($successSEUIdArr)>0){

         //determine seq.
         $seqFilename = $this->setFilenameFSEQ((($type=='i')?$this->filename:$this->crnFilename), $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
         if($seqFilename==false){
             BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
             return $this->errorTO;
          }

          //write physical file
          $filePath = $this->createFile($folder, $seqFilename, $data);  //places file in correct folder
          if($filePath == false){
             BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
             $this->errorTO->type = FLAG_ERRORTO_ERROR;
             $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
             return $this->errorTO;
          }
	          //create actual file to the network folder.
	                 $copy = copy($ROOT.$filePath, DIR_DATA_NON_FTP_FROM."ftp/standing_orders_207/".basename($this->filename));
 
	          
         }

         // SETUP DISTRIBUTION
         $postingDistributionTO = new PostingDistributionTO;
         $postingDistributionTO->DMLType = "INSERT";
         $postingDistributionTO->deliveryType = BT_EMAIL;
         $postingDistributionTO->subject = (($type=='i')?$this->getTemplateInvoiceSubject():$this->getTemplateCreditSubject()); //might have no filename if all errors therefore don't display on subject line...
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
                  print_r($dResult);

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
         /*
          *  UPDATE SMART EVENT in BULK
          */
         //SUCCESSFUL ITEMS
         if (sizeof($successSEUIdArr) > 0) {
            $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), basename($filePath), "");
            if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
              $this->errorTO->type = FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
              BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
              return $this->errorTO;
            }
         }
         //ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
          if (sizeof($errorSEUIdArr) > 0) {
             $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "231", "", FLAG_ERRORTO_ERROR);
             if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
               $this->errorTO->type = FLAG_ERRORTO_ERROR;
               $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
               BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
              return $this->errorTO;
            }
          }

    }
    /*-------------------------------------------------*/
    echo "Successfully Completed Extract : ".get_class($this)."<br>";

    /*  SUCCESS POINT - 2  */
    $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
    $this->errorTO->description = "Successful";
    return $this->errorTO;

    }

  }
}
//direct run!
if ($runMe) {
  directRunExtract(__FILE__);
}

?>
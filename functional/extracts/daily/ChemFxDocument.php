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
class ChemFxDocument {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class ChemFxDocumentInit extends extractController {

  private $principalUid = 451; //uid of principal extract.
  private $filename    = 'INV_451[@FSEQ].csv';  //main controller will build full filename with seq. for us.
  private $crnFilename = 'INV_451[@FSEQ].csv';  //credit note filename
  public function generateOutput() {

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
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_CUSTOM);
    if (count($reArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];

    if (!$this->skipInsert) {
      // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }


      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, $recipientUId, array(DT_CREDITNOTE));  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
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
      if ($r['document_type_uid'] == DT_CREDITNOTE){
        $type = 'c';
      }

      $grpDocs[$type][$r['dm_uid']][] = $r;
      $psms[$r["principal_store_uid"]] = $r["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
        $sfvals_pa   = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 622, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }

    foreach($grpDocs as $type => $orders){

       $dataArr = array();
       $errorSEUIdArr = array();
       $successSEUIdArr = array();
       $successCount = 0;
       $headerRow = "N";
       
       
       $headerArray = Array();                                           
       $headerArray[] = 'INVNUM.DOCTYPE';                     //1    4 = Invoice 1 = Credit Note                  
       $headerArray[] = 'INVNUM.ACCOUNTID';                   //2    Account Code                                
       $headerArray[] = 'INVNUM.DESCRIPTION';                 //3    Document Description                        
       $headerArray[] = 'INVNUM.ORDERDATE';                   //4    Order Date                                  
       $headerArray[] = 'INVNUM.INVDATE';                     //5    Invoice Date (Default to date of import)    
       $headerArray[] = 'INVNUM.TAXINCLUSIVE';                //6    Line Inclusive Total                        
       $headerArray[] = 'INVNUM.ORDERNUM';                    //7    Order Number                                
       $headerArray[] = '_BTBLINVOICELINES.CDESCRIPTION';     //8    Line Description (Should be Item Description
       $headerArray[] = '_BTBLINVOICELINES.FQUANTITY';        //9    Quantity Ordered                            
       $headerArray[] = '_BTBLINVOICELINES.FUNITPRICEEXCL';   //10   Unit Price Exclusive                        
       $headerArray[] = '_BTBLINVOICELINES.ISTOCKCODEID';     //1    Stock Code                                  
       $headerArray[] = '_BTBLINVOICELINES.ITAXTYPEID';       //2    Tax Type should always be 1 if taxable      
       $headerArray[] = '_BTBLINVOICELINES.IWAREHOUSEID';     //3    Warehouse Code                              
       $headerArray[] = '_BTBLINVOICELINES.IMODULE';          //4    0 = Stock and 1 = GL Account                
       $headerArray[] = '_BTBLINVOICELINES.ILINEID';          //5    Line ID - Sequential per Order              
       $headerArray[] = 'INVNUM.EXTORDERNUM';                 //6    External Order Number                       
       $headerArray[] = 'INVNUM.ADDRESS1';                    //7    Physical Address 1                          
       $headerArray[] = 'INVNUM.ADDRESS2';                    //8    Physical Address 2                          
       $headerArray[] = 'INVNUM.ADDRESS3';                    //9    Physical Address 3                          
       $headerArray[] = 'INVNUM.ADDRESS4';                    //20   Physical Address 4                         
       $headerArray[] = 'INVNUM.ADDRESS5';                    //1    Physical Address 5                          
       $headerArray[] = 'INVNUM.ADDRESS6';                    //2    Physical Address 6                          
       $headerArray[] = 'INVNUM.PADDRESS1';                   //3    Postal Address 1                            
       $headerArray[] = 'INVNUM.PADDRESS2';                   //4    Postal Address 2                            
       $headerArray[] = 'INVNUM.PADDRESS3';                   //5    Postal Address 3                            
       $headerArray[] = 'INVNUM.PADDRESS4';                   //6    Postal Address 4                            
       $headerArray[] = 'INVNUM.PADDRESS5';                   //7    Postal Address 5                            
       $headerArray[] = 'INVNUM.PADDRESS6';                   //8    Postal Address 6   
       
       $dataArr[] = join(',',$headerArray);
       
       // Mstr	Master Warehouse
       // RETURNS	RETURNS PENDING CREDIT
       // ABYX	STOCK SENT TO ABYX
       // ULTRA	ULTRA-CHEM
       // NEOLIFE	STOCK DELIVERED TO NEOLIFE
       // REJECTS	REJECTED STOCK
       // T3	STOCK DELIVERED TO T3

       $depotTrans = array(393   =>   'NELLWYN',
                           392   =>   'NELLWYN-CLAY',
                           400   =>   'NELLWYN-CLAY');      //  Master Warehouse                
    
       foreach($orders as $ord){
           /*-------------------------------------------------*/
           /*            START BUILDING OUTPUT
           /*-------------------------------------------------*/
           if(empty($sfvals_pa[$ord[0]["principal_store_uid"]]['value']))    {  // Missing Special Field
                $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors
           } else {
                $successCount++;
                $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success
                $storeAcc = trim($sfvals_pa[$ord[0]["principal_store_uid"]]['value']);
                
                $regionCde = (isset($depotTrans[$ord[0]['depot_uid']])) ? $depotTrans[$ord[0]['depot_uid']] : false;
                
                if($type == 'i'){
                    $docType = '4';
                    $documentNo = trim(ltrim($ord[0]["document_number"],'0')); 
                } else {
                    $docType = '1';
                    $documentNo = trim(ltrim($ord[0]["alternate_document_number"],0));  
                }
                
                foreach($ord as $d) { //detail rows => rows per order.
                      $rowArr = array();
                      $rowArr[] = $docType;                                                                             // 1   4 = Invoice 1 =Credit Note                                
                      $rowArr[] = $storeAcc;                                                                            // 2   Account Code                                              
                      $rowArr[] = "Sales Order";                                                                        // 3   Document Description                                      
                      $rowArr[] = date("Y/m/d", strtotime($ord[0]["order_date"]));                                      // 4   Order Date                                              
                      $rowArr[] = date("Y/m/d", strtotime($ord[0]["invoice_date"]));                                    // 5   Invoice Date (Default to date of import)                
                      $rowArr[] = "1";                                                                                  // 6   Line Inclusive Total                                    
                      $rowArr[] = $documentNo;      // 7   Order Number                                            
                      $rowArr[] = $d['product_description'];                                                         // 8   Line Description (Should be Item Description            
                      $rowArr[] = abs(round($d['document_qty'], 2));                                                    // 9   Quantity Ordered                                        
                      $rowArr[] = round($d['net_price'], 2);                                                            // 10  Unit Price Exclusive                                    
                      $rowArr[] = $d['product_code'];                                                                   // 11  Stock Code                                              
                      $rowArr[] = '1';                                                                                  // 12  Tax Type should always be 1 if taxable                  
                      $rowArr[] = $regionCde;                                                                              // 13  Warehouse Code                                          
                      $rowArr[] = '0';                                                                                  // 14  0 = Stock and 1 = GL Account                            
                      $rowArr[] =  $d['client_line_no'];                                                                // 15  Line ID - Sequential per Order                          
                      $rowArr[] =  trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"]));     // 16  External Order Number                                   
                      $rowArr[] =  trim(str_replace("'"," ",$ord[0]["deliver_name"]));                                  // 17  Physical Address 1                                      
                      $rowArr[] =  trim(str_replace("'"," ",$ord[0]["deliver_add1"]));                                  // 18  Physical Address 2                                      
                      $rowArr[] =  trim(str_replace("'"," ",$ord[0]["deliver_add2"]));                                  // 19  Physical Address 3                                      
                      $rowArr[] =  trim(str_replace("'"," ",$ord[0]["deliver_add3"]));                                  // 20  Physical Address 4                                      
                      $rowArr[] =  '';                                                                                  // 21  Physical Address 5                                      
                      $rowArr[] =  '';                                                                                  // 22  Physical Address 6                                      
                      $rowArr[] =  trim(str_replace("'"," ",$ord[0]["deliver_name"]));                                  // 23  Postal Address 1                                        
                      $rowArr[] =  trim(str_replace("'"," ",$ord[0]["deliver_add1"]));                                  // 24  Postal Address 2                                        
                      $rowArr[] =  trim(str_replace("'"," ",$ord[0]["deliver_add2"]));                                  // 25  Postal Address 3                                        
                      $rowArr[] =  trim(str_replace("'"," ",$ord[0]["deliver_add3"]));                                  // 26  Postal Address 4                                        
                      $rowArr[] =  '';                                                                                  // 27  Postal Address 5                                        
                      $rowArr[] =  '';                                                                                  // 28  Postal Address 6
             
                      $dataArr[] = join(',',$rowArr);
                }      
           } //eo special field check
       } //eo documents

      $data = join("\r\n",$dataArr);
      //create file only if there are successful items.
      $filePath = false;
      if(count($successSEUIdArr)>0){

        //determine seq.
        $seqFilename = $this->setFilenameFSEQ((($type=='i')?$this->filename:$this->crnFilename), $this->principalUid, false, 5, self::setFilenameFSEQ_LenType_PAD);
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
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "371", "", FLAG_ERRORTO_ERROR);
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
      }

    } // End ecah document Type
    /*-------------------------------------------------*/



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
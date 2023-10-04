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
class BraaiBQDocument {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class BraaiBQDocumentInit extends extractController {

  private $principalUid = 64; //uid of principal extract.
  private $filename    = 'INV_64[@FSEQ].csv';  //main controller will build full filename with seq. for us.
  private $crnFilename = 'INV_64[@FSEQ].csv';  //credit note filename
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
        $sfvals_pa   = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 137, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
        $sfvals_area = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 375, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
        $sfvals_rep  = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 374, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }

    foreach($grpDocs as $type => $orders){

       $dataArr = array();
       $errorSEUIdArr = array();
       $successSEUIdArr = array();
       $successCount = 0;
       $headerRow = "N";

       $headerArray = Array();                                           
       $headerArray[] = 'DocType';                                      // 1 DocType 1 is Credit note 4 is Sales Invoice
       $headerArray[] = 'InvNumber';                                     // 2 InvNumber
       $headerArray[] = 'OrderNum';                                      // 3 StoreNumber
       $headerArray[] = 'ExtOrderNum';                                   // 4 CustomerOrderNum
       $headerArray[] = 'AccountID';                                     // 5 Account Number
       $headerArray[] = 'ulIDSOrdArea';                                  // 6 Area
       $headerArray[] = 'Address1';                                      // 7 Address1
       $headerArray[] = 'InvDate';                                       // 8 InvDate
       $headerArray[] = 'iModule';                                       // 9 iModule
       $headerArray[] = 'iStockCodeID';                                  // 10 StockCode
       $headerArray[] = 'iWarehouseID';                                  // 11 Warehouse
       $headerArray[] = 'cDescription';                                  // 12 Stock Description
       $headerArray[] = 'fQuantity';                                     // 13 Quantity
       $headerArray[] = 'TaxInclusive';                                  // 14 TaxInclusive
       $headerArray[] = 'fUnitPriceExcl';                                // 15 UnitPriceExcl
       $headerArray[] = 'iTaxTypeID';                                    // 16 TaxType
       $headerArray[] = 'iPriceListNameID';                              // 17  PriceListName
       $headerArray[] = 'ulIDCrnReason';                                 // 18  Credit Note Reason
       $headerArray[] = 'Description';                                   // 19 Kwelanga Invoice No
       $headerArray[] = 'DocRepID';                                      // 20 Rep Code
       $headerArray[] = 'InvNum_iBranchID';                              // 21 InvNum_iBranchID 
       $headerArray[] = 'ucIDSordcustomerstoreNo';                       // 21 ucIDSordcustomerstoreNo 
            
       $dataArr[] = join(',',$headerArray);

       $depotTrans = array(190   =>   'BBQ-052',      //  DELIVER IT
                           104   =>   'BBQ-15-FIN',   //  L&B-Finished Goods
                           9     =>   'BBQ-40-FIN',   //  Megamor East London-Finished Goods
                           412   =>   'BBQ-47-FIN',   //  PSP Group KZN
                           149   =>   'BBQ-50-FIN',   //  TREND SALES
                           271   =>   'BBQ-51-FIN',   //  EMIT LOGISTICS
                           282   =>   'BBQ-42-FIN',   //  EMIT KZN LOGISTICS
                           195   =>   'BBQ-8-FIN',   //  Loginet
                           480   =>   'BBQ-72-FIN',   //  KP OPTIM TVL
                           483   =>   'BBQ-71-FIN',   //  KP OPTIM KZN
                           484   =>   'BBQ-70-FIN');   //  KP OPTIM TVL
    
       foreach($orders as $ord){
           /*-------------------------------------------------*/
           /*            START BUILDING OUTPUT
           /*-------------------------------------------------*/
           if(empty($sfvals_pa[$ord[0]["principal_store_uid"]]['value']) || 
              empty($sfvals_area[$ord[0]["principal_store_uid"]]['value']) ||
              empty($sfvals_rep[$ord[0]["principal_store_uid"]]['value'])
              ) {  // Missing Special Field
                $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors
           } else {
                $successCount++;
                $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success
                $storeAcc = trim($sfvals_pa[$ord[0]["principal_store_uid"]]['value']);
                $bqArea   = trim($sfvals_area[$ord[0]["principal_store_uid"]]['value']);
                $repCode  = trim($sfvals_rep[$ord[0]["principal_store_uid"]]['value']);
                
                $regionArr = (isset($depotTrans[$ord[0]['depot_uid']])) ? $depotTrans[$ord[0]['depot_uid']] : false;
                
                if($type == 'i'){
                    $docType = '4';
                    $credReason ='';
                    $documentNo = trim($ord[0]["document_number"]); 
                } else {
                    $docType = '1';
                    $credReason = trim($ord[0]["reason_description"]);
                    $documentNo = trim($ord[0]["alternate_document_number"]);  
                }
                
                foreach($ord as $d){ //detail rows => rows per order.
                	
//                	print_r($ord);

                      $rowArr = array();
                      $rowArr[] = $docType;                                                                           // 1 
                      $rowArr[] = $documentNo;;                                                                       // 2 
                      $rowArr[] = trim($ord[0]["branch_code"]);                                                       // 3 
                      $rowArr[] = trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"]));    // 4 
                      $rowArr[] = $storeAcc;                                                                          // 5 
                      $rowArr[] = $bqArea;                                                                            // 6 
                      $rowArr[] = trim(str_replace("'"," ",$ord[0]["deliver_name"]));                                 // 7 
                      $rowArr[] = date("Y/m/d", strtotime($ord[0]["invoice_date"]));                                  // 8 
                      $rowArr[] = '0';                                                                                // 9 
                      if(trim($d['product_code']) == 'L15718') {
                      	   $rowArr[] = $d['product_code'];                                                            // 10 
                      } elseif(trim($d['product_code']) == 'BBQ-L15718') {
                      	   $rowArr[] = $d['product_code'];                                                            // 10 
                      } elseif(trim($d['product_code']) == 'DEL001') {
                      	   $rowArr[] = '2000/MNF/DIS/I0059/GOUTGA0000/GA/CHA/BBQ';                                    // 10 
                      } else {
                      	   $rowArr[] = 'BBQ-' . $d['product_code'];                                                   // 10 
                      }                                                     
                      $rowArr[] = $regionArr;                                                                         // 11
                      $rowArr[] = $d[0]['product_description'];                                                       // 12
                      $rowArr[] = abs(round($d['document_qty'], 2));                                                  // 13
                      $rowArr[] = '0';                                                                                // 14
                      $rowArr[] = round($d['net_price'], 2);                                                          // 15
                      $rowArr[] = '1';                                                                                // 16
                      $rowArr[] = 'Price List1';                                                                      // 17
                      $rowArr[] = $credReason;                                                                        // 18
                      $rowArr[] = $documentNo;                                                                        // 19
                      $rowArr[] = $repCode;                                                                           // 20
                      $rowArr[] = '2';                                                                                // 21
                      $rowArr[] = '';                                                                                 // 22  
                                   
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
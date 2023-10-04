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
class WavesideSapInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class WavesideSapInvoicedInit extends extractController {

  private $principalUid = 81; //uid of principal extract.

  private $filename = 'SAPSAL[@FSEQ].txt';  //main controller will build full filename with seq. for us.
  private $custFilename = 'MMOUTLET[@FSEQ].txt';  //CUSTOMER filename
  private $crnFileName = 'CRN81[@FSEQ].txt';

  public function generateOutput(){

    global $ROOT, $PHPFOLDER;

    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];
    $contactPerson = $pArr[0]['contactperson'];
    $folder = $this->principalUid . '_' . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.


    //use the receipients listed in the notification table instead of hard coding them!!!
    //expecting only one row loaded per principal extract
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_ALTCUSTOM4);
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
    $psms=$dmUIds=array();
      foreach($seDocs as $k=>$r){

        $type = 'i';
        if ($r['document_type_uid'] == DT_CREDITNOTE){
          $type = 'c';
        }

        $grpDocs[$type][$r['dm_uid']][] = $r;
        $psms[$r["principal_store_uid"]] = $r["principal_store_uid"];
        $dmUIds[] = $r["dm_uid"];
      }
      
      // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals_PA = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 314, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");

    }
    
   
      if ($type == 'i'){
        foreach($grpDocs as $type => $orders){
      
        $salesArr=array();
        $custArr=array();
        $dataArr = array();
        $errorSEUIdArr = array();
        $successSEUIdArr = array();
        $successCount = 0;

          foreach($orders as $ord){
            
            if((empty($sfvals_PA[$ord[0]["principal_store_uid"]]['value'])) ) {  //has no special field and/or blank...
            $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors

        } else {
          
            $successCount++;
            $successSEUIdArr[] = $ord[0]['se_uid'];

            /*-------------------------------------------------*/
            /*            START BUILDING OUTPUT
            /*-------------------------------------------------*/
             $detArr = array();
                $detArr[] = 0;
                $detArr[] = str_pad($ord[0]['old_account'],6,0,STR_PAD_LEFT);
                $detArr[] = 'MMO0';
                if (trim($sfvals_PA[$ord[0]["principal_store_uid"]]['value']) !=""){
                  $detArr[] = trim($sfvals_PA[$ord[0]["principal_store_uid"]]['value']);
                }
                $detArr[] = str_pad($ord[0]['deliver_name'],30, ' ',STR_PAD_LEFT);
                $detArr[] = str_pad($ord[0]['deliver_add1'],30, ' ',STR_PAD_LEFT);
                $detArr[] = str_pad($ord[0]['deliver_add2'],30, ' ',STR_PAD_LEFT);
                $detArr[] = str_pad($ord[0]['deliver_add3'],30, ' ',STR_PAD_LEFT);
                $detArr[] = str_pad($ord[0]['tel_no1'],18, ' ',STR_PAD_LEFT);
                $detArr[] = str_pad($contactPerson,20, ' ',STR_PAD_LEFT);

                $custArr[] = join(',',$detArr);
              


              foreach($ord as $d){ //Customer Master

               
                $sellingPrice = round($d['selling_price'],2);
              
              /* SAP FILE HEADER */
              //array containing list of row values
              $rowArr = array();
              $rowArr[] = str_replace("-","",$d["invoice_date"]);
              $rowArr[] = 0;
              $rowArr[] = str_pad($d['old_account'],7,0,STR_PAD_LEFT);
              $rowArr[] = 0;//default for normal sales
              $rowArr[] = "000000000";
              $rowArr[] = str_pad($d['product_code'],6,0,STR_PAD_LEFT); 
              $rowArr[] = str_pad($d['document_qty'],7,0,STR_PAD_LEFT);
              $rowArr[] = "0000000";
              $rowArr[] = " ";
              $rowArr[] = str_pad(preg_replace("/[^1-9]/","", $sellingPrice),9,0,STR_PAD_LEFT);
              if($d['discount_value'] == 0) {
                $rowArr[] =" ";
                $rowArr[] = str_pad(preg_replace("/[^1-9]/","",$d['discount_value']),9,0,STR_PAD_LEFT);
              } else {
                $rowArr[]='-';
                $rowArr[]= str_pad(preg_replace("/[^1-9]/","",$d['discount_value']),9,0,STR_PAD_LEFT);
              }
              $rowArr[] = ' 000000000';
  
              $salesArr[] = join('',$rowArr);
                } //eo detail
            }
          } //eo documents

          $dataSales = join("\r\n",$salesArr);
          $cust=join("\r\n",$custArr);
          

          //create file only if there are successful items.
          $filePath = false;
          if(count($successSEUIdArr)>0){

            //determine seq.
            $seqFilenameSales = $this->setFilenameFSEQ((($type=='i')?$this->filename:$this->crnFileName), $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
            $seqFileNameCust = $this->setFilenameFSEQ($this->custFilename, $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
            if(($seqFilenameSales==false) || ( $seqFileNameCust==false)){
              BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
              return $this->errorTO;
            }
            
            //write sales file
            $filePathSales = $this->createFile($folder, $seqFilenameSales, $dataSales);  //places file in correct folder
            if($filePathSales == false){
              BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
              $this->errorTO->type = FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
              return $this->errorTO;
            }
            //cust file
            $filePathCust = $this->createFile($folder, $seqFileNameCust, $cust);  //places file in correct folder
            if($filePathCust == false){
              BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
              $this->errorTO->type = FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
              return $this->errorTO;
            }
        //create actual file to the network pastel folder.
            // $fp = file_put_contents(DIR_PHPBACKEND_PASTEL_DATAFILES  . $seqFilename, $data);
            $attachmentFiles = array($filePathSales,$filePathCust);
          


          // SETUP DISTRIBUTION
          
          for($i=0;$i<count($attachmentFiles);++$i){
            
            $postingDistributionTO = new PostingDistributionTO;
            $postingDistributionTO->DMLType = "INSERT";
            $postingDistributionTO->deliveryType = BT_EMAIL;
            $postingDistributionTO->subject = (($type=='i')?$this->getTemplateInvoiceSubject():$this->getTemplateCreditSubject()); //might have no filename if all errors therefore don't display on subject line...
            $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
            
            if($attachmentFiles[$i]!=false){
              $postingDistributionTO->attachmentFile = $attachmentFiles[$i];
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
              }else {
                $recipientsCheckCount++;  //successful
              }
            }
          
            if ($recipientsCheckCount==0) {
              $this->errorTO->type = FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Failed in ".get_class($this)." extract no valid Recipient/Contact found, no outgoing mail generated!";
              BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
              return $this->errorTO;
            }

          }
            /*
             *  UPDATE SMART EVENT in BULK
             */
            //SUCCESSFUL ITEMS
            if (sizeof($successSEUIdArr) > 0) {
              $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr),basename($filePathSales),basename($filePathCust));
              if ($bIResult->type != FLAG_ERRORTO_SUCCESS){
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
                BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
                return $this->errorTO;
              }
            }
            //ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
            if (sizeof($errorSEUIdArr) > 0) {
              $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "314", "", FLAG_ERRORTO_ERROR);
              if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
                BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
                return $this->errorTO;
              }
            }
          }
        }
      /*-------------------------------------------------*/
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
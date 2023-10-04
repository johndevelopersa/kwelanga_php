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
class GlenmorSoap {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class GlenmorSoapInit extends extractController {

  private $principalUid = 426; //uid of principal extract.
  private $filename = 'INV426[@FSEQ].txt';  //main controller will build full filename with seq. for us.
  private $crnFilename = 'CRN426[@FSEQ].txt';  //credit note filename

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
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, 
                                                     $recipientUId, 
                                                     $inclCancelled = false, 
                                                     $dtarr = array(DT_ORDINV, DT_ORDINV_ZERO_PRICE),
                                                     $p_wDSArr = false,
                                                     $fromInvDate='2021-11-01');  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
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

      $grpDocs[$type][$r['ddUid']][] = $r;
      $psms[$r["principal_store_uid"]] = $r["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals_PA = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 607, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }
      
    foreach($grpDocs as $type => $orders){

    
    $errorSEUIdArr = array();
    $successSEUIdArr = array();
    $successCount = 0;
    $orderStore = '';
    
    $C = 0;
//    	echo "<pre>";
//    	print_r($orders);

// Insert Headings

$rowArr[] = 'Document_type';
$rowArr[] = 'Document_Number' ;
$rowArr[] = 'Source_Invoice_Number' ;
$rowArr[] = 'Invoice_Date';
$rowArr[] = 'Customer_Code';
$rowArr[] = 'Product_Code';
$rowArr[] = 'Document_Qty';
$rowArr[] = 'Nett_Price';
$rowArr[] = 'Incl_Total';
$rowArr[] = 'Sales_Order_No' ;
$rowArr[] = 'Line_No' ;

$dataArr[] = join(',',$rowArr);

   
    foreach($orders as $ord) {

        if($type == 'i'){
            $filePrefix = 'KINV';
        } else {
            $filePrefix = 'KCRN';
        }
    	
    	
        /*-------------------------------------------------*/
        /*            START BUILDING OUTPUT
        /*-------------------------------------------------*/
        
        if(empty($sfvals_PA[$ord[0]["principal_store_uid"]]['value'])) {  //has no special field and/or blank...
            $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors
        } else {
        	
            $successCount++;
            $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

            $storeAcc = trim($sfvals_PA[$ord[0]["principal_store_uid"]]['value']);
            
            //array containing list of row values
            $rowArr = array();
            
            if(abs($ord[0]['document_qty']) <> 0) {            
            
                if($type == 'i'){
                    $rowArr[] = '4';
                    $rowArr[] = ltrim(trim(substr($ord[0]['invoice_number'],-6)),'0') ;
                    $rowArr[] = '' ;
                } else {
                   $rowArr[] = '1';
                   $rowArr[] = str_pad(substr($ord[0]['alternate_document_number'],-6), 6, 0, STR_PAD_LEFT)  ;
                   $rowArr[] = "SO".str_pad(substr($ord[0]['source_document_number'],-5), 5, 0, STR_PAD_LEFT)  ;
                }
                $rowArr[] = date("Y/m/d", strtotime($ord[0]["invoice_date"]));  //DATE (YYYY/MM/DD)
                $rowArr[] = $storeAcc;  //CUSTOMER CODE
                $rowArr[] = trim(str_replace(array('"'),array(''),$ord[0]['product_code']));
                $rowArr[] = abs($ord[0]['document_qty']) ;
                $rowArr[] = number_format(abs($ord[0]['net_price']), 2, '.', '');
                $rowArr[] = number_format(abs($ord[0]['total']), 2, '.', '');
                $rowArr[] = substr($ord[0]['client_document_number'],-8) ;
                $rowArr[] = substr($ord[0]['client_line_no'],-8) ;

            
                $dataArr[] = join(',',$rowArr);
            }
        } //eo special field check

    } //eo documents

    $data = join("\r\n",$dataArr);
        
    if(count($dataArr) > 0 ) {
          // determine seq.
          //determine seq.
          $seqFilename = $this->setFilenameFSEQ((($type=='i')?$this->filename:$this->crnFilename), $this->principalUid, false, 5, self::setFilenameFSEQ_LenType_PAD);
          if($seqFilename==false){
             BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
             return $this->errorTO;
          }
          
          //write physical file
          $filePath = $this->createFile($folder, $seqFilename, $data);  //places file in correct folder
          
          $toFolder = $ROOT . 'ftp/glenmor/';
          
          $fp = file_put_contents($toFolder . $seqFilename, $data);        
          
          if($fp != strlen($data)) {
               BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
               $this->errorTO->type = FLAG_ERRORTO_ERROR;
               $this->errorTO->description = "Failed in ".get_class($this)." on actual file creation";
               return $this->errorTO;
          }
          
          if($filePath == false) {
                BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
                return $this->errorTO;
          }
          
          $statusFlag = FLAG_STATUS_CLOSED;
          $general1 = $seqFilename;         
          
          // SETUP DISTRIBUTION
          $postingDistributionTO = new PostingDistributionTO;
          $postingDistributionTO->DMLType = "INSERT";
          $postingDistributionTO->deliveryType = BT_EMAIL;
          $postingDistributionTO->subject = (($type=='i')?$this->getTemplateInvoiceSubject():$this->getTemplateCreditSubject()); //might have no filename if all errors therefore don't display on subject line...
          $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));

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
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "253", "", FLAG_ERRORTO_ERROR);
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
      }

    }
    /*-------------------------------------------------*/

    echo "INV - Successfully Completed Extract : ".get_class($this)."<br>";

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
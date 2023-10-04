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
class StepInTimeInvoicedReverse {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class StepInTimeInvoicedReverseInit extends extractController {

  private $principalUid = 304; //uid of principal extract.
  private $filename =    'PAS304[@FSEQ].txt';  //main controller will build full filename with seq. for us.
  private $crnFilename = 'CRN304[@FSEQ].txt';  //credit note filename
  private $debFilename = 'DEB304[@FSEQ].txt';  //credit note filename

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
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = false, $dtarr = array(DT_ORDINV, DT_ORDINV_ZERO_PRICE));  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }

      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, $recipientUId, array(DT_CREDITNOTE, DT_MCREDIT_PRICING, DT_MCREDIT_OTHER, DT_MDEBIT_NOTE));  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
    }
    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrdersReverse($this->principalUid, $recipientUId);

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
    foreach($seDocs as $k=>$r) {

          $type = 'i';
          if (in_array($r['document_type_uid'], array(DT_CREDITNOTE, DT_MCREDIT_PRICING, DT_MCREDIT_OTHER))) {
                $type = 'c';
          }
          if ($r['document_type_uid'] == DT_MDEBIT_NOTE) {
                $type = 'm';
          }
          $grpDocs[$type][$r['dm_uid']][] = $r;
          $psms[$r["principal_store_uid"]] = $r["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals_PA = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 523, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }
      
    foreach($grpDocs as $type => $orders){

        $dataArr = array();
        $errorSEUIdArr = array();
        $successSEUIdArr = array();
        $successCount = 0;

        foreach($orders as $ord){

           /*-------------------------------------------------*/
           /*            START BUILDING OUTPUT
           /*-------------------------------------------------*/
        
           if(empty($sfvals_PA[$ord[0]["principal_store_uid"]]['value'])) {  //has no special field and/or blank...
               $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors
           } else {
               $successCount++;
               $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success
               $storeAcc = trim($sfvals_PA[$ord[0]["principal_store_uid"]]['value']);
               $salesAnalysis = "";


               //period
               $period = '00';
               switch (date("m", strtotime($ord[0]["invoice_date"]))) {
               case '01':
                  $period = '11';
                  break;
               case '02':
                  $period = '12';
                  break;
               case '03':
                  $period = '13';
                  break;
               case '04':
                  $period = '02';
                  break;
               case '05':
                  $period = '03';
                  break;
               case '06':
                  $period = '04';
                  break;
               case '07':
                  $period = '05';
                  break;
               case '08':
                  $period = '06';
                  break;
               case '09':
                  $period = '07';
                  break;
               case '10':
                  $period = '08';
                  break;
               case '11':
                  $period = '09';
                  break;
               case '12':
                  $period = '10';
                break;
               }
               $oldVATRate = "F";
               
               /* PASTEL HEADER */
               //array containing list of row values
               $rowArr = array();
               $rowArr[] = '"HEADER"';

               if($type == 'i') {
                   $rowArr[] = '"IN' . ltrim($ord[0]['document_number'],"0") . '"';
               } elseif($type == 'm') {
                   $rowArr[] = '"DB' . ltrim($ord[0]['document_number'],"0") . '"';
               } else {
                   $rowArr[] = '"CR' . str_pad(substr($ord[0]['document_number'],-6), 6, 0, STR_PAD_LEFT) . '"';
               }

               $rowArr[] = '" "';
               $rowArr[] = '"Y"';  //printed
               $rowArr[] = '"'.$storeAcc.'"';  //CUSTOMER CODE - Pastel Account.
               $rowArr[] = '"'.$period.'"';  //period number
               if($type == 'i'){
                  $rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["invoice_date"])).'"';  //DATE (DD/MM/YYYY)
                  $rowArr[] = '"'.trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"])).'"';
               } else {
                  $rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["order_date"])).'"';  //DATE (DD/MM/YYYY)
                  $rowArr[] = '"'.trim(str_replace(array('"',"'"),array('',''),$ord[0]["source_document_number"])).'"';
               }
               $rowArr[] = '"N"';  //IN / EX - CHAR
               $rowArr[] = $hDisc ;  //discount
               $rowArr[] = '" "';  //MESSAGE - CHAR
               $rowArr[] = '""';   //MESSAGE - CHAR
               $rowArr[] = '""';   //MESSAGE - CHAR
               $rowArr[] = '"'.trim($ord[0]["deliver_name"]).'"';
               $rowArr[] = '"'.trim($ord[0]["deliver_add1"]).'"';
               $rowArr[] = '"'.trim($ord[0]["deliver_add2"]).'"';
               $rowArr[] = '"'.trim($ord[0]["deliver_add3"]).'"';
               $rowArr[] = '""';
               $rowArr[] = '"'.$salesAnalysis.'"'; //SALES ANALYSIS – CHAR
               $rowArr[] = '""';
               if($type == 'i'){
                   $rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["invoice_date"])).'"';  //DATE (DD/MM/YYYY)
               } else {
                   $rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["order_date"])).'"';  //DATE (DD/MM/YYYY)
               }
               $rowArr[] = '""';
               $rowArr[] = '""';
               $rowArr[] = '""';
               $rowArr[] = '0';
               $rowArr[] = '"N"';
               $rowArr[] = '" "';
               $rowArr[] = '" "';
               $dataArr[] = join(',',$rowArr);
               foreach($ord as $d){ //detail rows.
             	
                  if(abs($d['document_qty'])<>0){
        
                      $detArr = array();
                      $detArr[] = '"DETAIL"';
                      $detArr[] = '0';
        
                      if($type == 'i') {
                          $detArr[] = abs($d['document_qty']);
                          $detArr[] = number_format(abs(round($d['selling_price'], 2)), 2, '.', ''); //SELLING PRICE - NUM
                          $detArr[] = number_format(abs(round(($d['selling_price']*VAL_VAT_RATE_ADD), 2)), 2, '.', '');  //INCLUSIVE PRICE - NUM                  
                          $detArr[] = '"' . trim(str_replace(array('"'),array(''),$d['items_per_case'])) . '"';  //UNIT - CHAR
                          $detArr[] = (substr($d['vat_rate'],0,2)=='15')?15:2;
                          $detArr[] = '3'; //DISCOUNT TYPE
                          $detArr[] = (abs($d['selling_price'])>0 && abs($d['discount_value'])>0) ?  number_format(round($d['discount_value'] / $d['selling_price'] * 100, 2), 2, '', '') : 0; //DISCOUNT %  //stored value : discount_value backwards calculation.
                      } else   {
                          $detArr[] = abs($d['document_qty']);
                          $detArr[] = number_format(abs(round($d['selling_price'], 2)), 2, '.', ''); //SELLING PRICE - NUM
                          if($oldVATRate == "T") {
                               $detArr[] = number_format(abs(round(($d['selling_price']*VAL_VAT_OLD_RATE_ADD), 2)), 2, '.', '');  //INCLUSIVE PRICE - NUM 	
                          } else {
                               $detArr[] = number_format(abs(round(($d['selling_price']*VAL_VAT_RATE_ADD), 2)), 2, '.', '');  //INCLUSIVE PRICE - NUM	
                          }                             $detArr[] = '" "';  //UNIT - CHAR
                          $detArr[] = (substr($d['vat_rate'],0,2)=='15')?15:2;
                          $detArr[] = '3'; //DISCOUNT TYPE
                          $detArr[] = (abs($d['selling_price'])>0 && abs($d['discount_value'])>0) ?  number_format(round($d['discount_value'] / $d['selling_price'] * 100, 2), 2, '', '') : 0; //DISCOUNT %  //stored value : discount_value backwards calculation.
                      }
                      if($d['non_stock_item'] == 'Y') {
                           $detArr[] = '"' . trim(str_replace(array('/'),array(''),$d['alt_code'])). '"';
                           $pastelType = '6';                	
                      } else {
                           $pastelType = '4';
                           $detArr[] = '"' . trim(str_replace(array('"'),array(''),$d['product_code'])) . '"';
                      }
                      $detArr[] = '"' . trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description'])) . '"';
                      $detArr[] = $pastelType ;  //Pastel Type.
                      $detArr[] = '""';
                      $detArr[] = '"'.$regionArr.'"';
 
                      $dataArr[] = join(',',$detArr);
                  } 
               } //eo detail
           } //eo special field check
        } //eo documents

        $data = join("\r\n",$dataArr);
        //create file only if there are successful items.
        $filePath = false;
        if(count($successSEUIdArr)>0) {
             //determine seq.
             if($type=='i') {
                   $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
             } elseif($type=='c') {
                   $seqFilename = $this->setFilenameFSEQ($this->crnFilename, $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
             } else {
                   $seqFilename = $this->setFilenameFSEQ($this->debFilename, $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
             }
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
           $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "465", "", FLAG_ERRORTO_ERROR);
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
//direct run!
if ($runMe) {
  directRunExtract(__FILE__);
}

?>
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
class CreightonInvoicedPnp {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class CreightonInvoicedPnpInit extends extractController {


  private $principalUid = 35; //uid of principal extract.
  private $filename = 'SAPB1[@FILETYPE][@FSEQ].txt';  //main controller will build full filename with seq. for us.
  private $crnFilename = 'SAPB1CRN[@FILETYPE][@FSEQ].txt';  //credit note filename
  //filename above gets hdr or dtl appended


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

    // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, 
                                                     $recipientUId, 
                                                     $inclCancelled = true,
                                                     $p_dtArr = false,
                                                     $p_wDSArr = false,
                                                     $fromInvDate=false,
                                                     $toInvDate=false,
                                                     $chainUIdIn=false,
                                                     $dataSource=false,
                                                     $capturedBy='PNP',
                                                     $depotUId = false);
       //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }

      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, 
                                                             $recipientUId, 
                                                             array(DT_CREDITNOTE),
                                                             $depotUId = false,
                                                             $fromInvDate=false,
                                                             $toInvDate=false,
                                                             $dataSource=false,
                                                             $capturedBy='PNP',
                                                             $depotUId = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***

      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
    }

    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($this->principalUid, $recipientUId);
print_r($seDocs);

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
      //place into groups
      $index = 'i'; //invoices
      if ($r['document_type_uid'] == DT_CREDITNOTE){
        $index = 'c';
      }
      if(in_array($r['document_status_uid'], array(DST_CANCELLED, DST_CANCELLED_NOT_OUR_AREA))){
        continue; // cancelled not outputted for time being.
        // $index = 'can'; //cancelled
      }
      $grpDocs[$index][$r['dm_uid']][] = $r;  //group by index.
      $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }


    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 240, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");  //Account - S/Sure
    }


    //BUILD FILE FOR EACH TYPE -> INVOICES AND CANCELLED.
    foreach($grpDocs as $type => $hr){

      //depot translate (clipper program)
      $depotTrans = array(
        202 =>   60, //TJ
        190 =>   61, // Deliver IT
        104 =>   62, //UD
          5 =>   52, //UC
          6 =>   54, //FP
          7 =>   55  //TE
      );

      /*-------------------------------------------------*/
      /* START BUILDING OUTPUT
       */

      $hdrArr = $dtlArr = array();
      $errorSEUIdArr = array();
      $successSEUIdArr = array();

      $hdrArr[] = "DocNum\tCardCode\tDocType\tDocDate\tNumAtCard\tDocCurrency\tU_REF\tU_CREDIT_REASON";
      $hdrArr[] = "DocNum\tCardCode\tDocType\tDocDate\tNumAtCard\tDocCur\tU_REF\tU_CREDIT_REASON";
      $dtlArr[] = "ParentKey\tLineNum\tItemCode\tItemDescription\tQuantity\tPrice\tPriceAfterVAT\tUnitPrice\tLineTotal\tCurrency\tRate\tWarehouseCode\tVatGroup\tTaxCode\tTaxLiable";
      $dtlArr[] = "DocNum\tLineNum\tItemCode\tDscription\tQuantity\tPrice\tPriceAfVAT\tPriceBefDi\tLineTotal\tCurrency\tRate\tWhsCode\tVatGroup\tTaxCode\tTaxStatus";

        foreach($hr as $ord){ //document loop

        $regionArr = (isset($depotTrans[$ord[0]['depot_uid']])) ? $depotTrans[$ord[0]['depot_uid']] : '  ' ;

        if($regionArr == 60) {
            $docNum = "TK" . substr($ord[0]['document_number'],-6);
        } elseif($regionArr == 61) {
            $docNum = "DI" . substr($ord[0]['document_number'],-6);
        } elseif($regionArr == 62) {
            $docNum = "LB" . substr($ord[0]['document_number'],-6);
        } else {
            $docNum = "  " . substr($ord[0]['document_number'],-6);
        }
        $docKey = substr($ord[0]['document_number'],-6);

        //which special field is based on what the product is, PP - or S/SURE.
        //$sfvals = (substr(trim(strtoupper($ord[0]["product_description"])),0,2) == 'PP') ? $sfvalsAQUE : $sfvalsSURE;

        if(empty($sfvals[$ord[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...

          $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors

        } else {

          $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

          $storeAcc = $sfvals[$ord[0]["principal_store_uid"]]['value'];

          //array containing list of row values
          $rowArr = array();
          $rowArr[] = $docKey;
          $rowArr[] = $storeAcc;
          $rowArr[] = "dDocument_Items";
          $rowArr[] = date("Ymd", strtotime($ord[0]["invoice_date"]));
          $rowArr[] = $docNum;
          $rowArr[] = "R";
          if ($type=='c') {
            $rowArr[] = trim($ord[0]["source_document_number"]); // U_REF
            // Credit Notes require the mapped Credit Note Reason
            /*
             * CP	CP Reason	UID	ULL Reason	 - we no longer do this as our codes map to multiple of theirs so we now just send them ours...
            1	Incorrect Customer Invoiced	1	NB	Returned Not Ordered
            2	Incorrect Stock Invoiced	1	NB	Returned Not Ordered
            3	Incorrect Price	Not Used By Ullmanns
            4	Invoice Duplicated	3	NC	Returned Order Duplicated
            5	Incorrect Quantity Invoiced	1	NB	Returned Not Ordered
            6	Price Credit Taken	Not Used By Ullmanns
            7	Cancelled Order	22	NA	Returned Cancelled Order	23	W0
            8	Damaged Goods Returned	6	NE	Returned Goods Damaged
            9	Late Delivery	76	NR	Returned Del. not on NDD
            10	Not Ordered	1	NB	Returned Not Ordered
            11	Oversupplied	4	ND	Returned Overstocked
            12	Short Delivered	10	NG	Returned Short Delivered
            13	Incorrectly Invoiced	104	R 1	Cancelled – Incorrect stock ordered
            14	Duplicate Order	3	NC	Returned Order Duplicated
            15	No Stock	24	W1	Cancelled - No Stock
            
            */
            $rowArr[] = "1";
          } else {
            $rowArr[] = trim($ord[0]["customer_order_number"]); // U_REF
            $rowArr[] = ""; // reason code only needed for credits
          }
          
          $hdrArr[] = join("\t",$rowArr);

          foreach($ord as $d){ //detail rows.
            if ($d['document_qty']==0) continue;

            $rowArr = array();

            $rowArr[] = $docKey; // the key
            $rowArr[] = ""; //$d['line_no']; // line no
            $rowArr[] = trim($d['product_code']);
            $rowArr[] = trim($d['product_description']);
            $rowArr[] = abs($d['document_qty']);
            $rowArr[] = number_format(round($d['selling_price'], 2), 2, '.', ''); // price
            $rowArr[] = ""; // prriceAfterVat
            $rowArr[] = number_format(round($d['selling_price'], 2), 2, '.', ''); // unitPrice
            $rowArr[] = ""; // LineTotal leave blank - self calculated
            $rowArr[] = "R";
            $rowArr[] = ""; // currency rate
            $rowArr[] = $regionArr; // warehouse code
            $rowArr[] = (($d['vat_rate'] > 0)?"O1":"O0"); // vatGroup ... the leading char is alpha O not zero ; O3=zero rated ; O0=exempt
            $rowArr[] = (($d['vat_rate'] > 0)?"O1":"O0"); // TaxCode ... the leading char is alpha O not zero ; O3=zero rated ; O0=exempt
            $rowArr[] = (($d['vat_rate'] > 0)?"tYES":"tNO"); // vatLiable
            
            $dtlArr[] = join("\t",$rowArr);

          } //eo detail
        } //eo special field check
      } //eo document

      //create file only if there are successful items.
      $filePath1 = $filePath2 = false;
      if(count($successSEUIdArr)>0){

        //determine seq.
        $seqFilename = $this->setFilenameFSEQ((($type=='i' || $type=='can')?$this->filename:$this->crnFilename), $this->principalUid, false, 3);
        if($seqFilename==false){
          BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
          return $this->errorTO;
        }

        $can = (($type=="can")?".CANCELLED":"");
        //write physical file - HDR
        $filePath1 = $this->createFile($folder, str_replace("[@FILETYPE]","HDR",$seqFilename).$can, join("\r\n",$hdrArr));  //places file in correct folder
        if($filePath1 == false){
          BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
          return $this->errorTO;
        }

        //write physical file - DTL
        $filePath2 = $this->createFile($folder, str_replace("[@FILETYPE]","DTL",$seqFilename).$can, join("\r\n",$dtlArr));  //places file in correct folder
        if($filePath2 == false){
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
      $postingDistributionTO->subject = (($type=='i' || $type=='can')?$this->getTemplateInvoiceSubject():$this->getTemplateCreditSubject()); //might have no filename if all errors therefore don't display on subject line...
      $postingDistributionTO->body = $this->getTemplateBody($principalName, count($successSEUIdArr), count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
      if($filePath1!=false){
        //$postingDistributionTO->attachmentFile = $filePath1.",".$filePath2;
		$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, str_replace('/bkup/','/',$filePath1));
		$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, str_replace('/bkup/','/',$filePath2));
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
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), basename($filePath1).",".basename($filePath2), "");
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
      }
      //ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
      if (sizeof($errorSEUIdArr) > 0) {
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "240", "", FLAG_ERRORTO_ERROR);
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
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
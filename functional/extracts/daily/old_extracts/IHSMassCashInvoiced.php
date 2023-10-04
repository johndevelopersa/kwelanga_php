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
class IHSMashCashInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}



class IHSMashCashInvoicedInit extends extractController {


  private $principalUid = 199; //uid of principal extract. (Shield / MashCash)
  private $filename = 'INV_299715900_[DATE]_[@FSEQ].dat';  //main controller will build full filename with seq. for us.

  public function generateOutput(){

    global $ROOT, $PHPFOLDER;

    $fSeq = false;

    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];
    $folder = $this->principalUid . '_' . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.
    $now = date("Ymd");


    //use the receipients listed in the notification table instead of hard coding them!!!
    //expecting only one row loaded per principal extract
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_ALTCUSTOM);
    if (count($reArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];

    // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid,
                                                     $recipientUId,
                                                     $inclCancelled = false,
                                                     false,
                                                     false,
                                                     false,
                                                     false,
                                                     $chainUIdIn="???");  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
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
    $psms=array();
    $productUIds = array();
    foreach($seDocs as $k=>$r){
      $grpDocs[$r['dm_uid']][] = $r; // remember that if you expand this to credit notes in future that
                                     // the processing below uses doc count as  a field so make sure it
                                     // doesnt count the cr/dr index as the cnt!
      $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];

      $productUIds[$r["product_uid"]] = $r["product_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals_PA = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 231, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }

    $productGTINs = $this->productDAO->getPrincipalProductGTINsByUIds($productUIds);

    $dataArr = array();
    $errorSEUIdArr = array();
    $successSEUIdArr = array();

    /*
     * Remember :
     * This extract has a header count so is different from other extracts in that it only joins together
     * the rows after the final checks are done so that it can go back and supply the header count
     */

    $rowArr = array();
    $rowArr[] = 'H'; // Header Row
    $rowArr[] = $pArr[0]["principal_gln"];
    $rowArr[] = $pArr[0]["principal_name"];
    $rowArr[] = $now;
    $rowArr[] = "?fseq";
    $rowArr[] = "?cnt"; // This is filled in at end, but to safeguard against later mods that upset col order we check the ??? value first
                        // make sure the above is not separated into cr/drs for this to work
    $dataArr[] = $rowArr;

    $docCnt = 0;
    $totExcl = 0;
    foreach($grpDocs as $ord){

      /*-------------------------------------------------*/
      /*            START BUILDING OUTPUT
      /*-------------------------------------------------*/

      if(
          (empty($sfvals_PA[$ord[0]["principal_store_uid"]]['value'])) ||
          (str_len(trim($ord[0]['ean_code']))!=13)
        ) {

        $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors

      } else {

        // do this here so as to not waste sequences
        if ($fSeq===false) {
          $fSeq = $this->getFileSequence($this->filename, $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
          if($fSeq==false){
            BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
            return $this->errorTO;
          }
        }

        $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

        //array containing list of row values
        $rowArr = array();
        $rowArr[] = 'I'; // Invoice Header Row
        $rowArr[] = $ord[0]['invoice_number'];
        $rowArr[] = $ord[0]['ean_code'];
        $rowArr[] = $ord[0]['deliver_name'];
        $rowArr[] = $ord[0]['document_number'];
        $rowArr[] = $ord[0]["exclusive_total"];
        $rowArr[] = $ord[0]["vat_total"];
        $rowArr[] = $ord[0]["invoice_total"];
        $rowArr[] = date("Ymd", strtotime($ord[0]["invoice_date"]));
        $rowArr[] = date("Ymd", strtotime($ord[0]["delivery_date"]));
        $rowArr[] = count($ord);
        $rowArr[] = $pArr[0]["physical_add1"];
        $rowArr[] = $pArr[0]["physical_add2"];
        $rowArr[] = $pArr[0]["physical_add3"]; // city
        $rowArr[] = $pArr[0]["physical_add4"]; // postal code
        $rowArr[] = $pArr[0]["vat_num"];
        $rowArr[] = "Shield Buying & Distribution";
        $rowArr[] = "19 Girton Road";
        $rowArr[] = "Parktown";
        $rowArr[] = "Johannesburg";
        $rowArr[] = "2193";
        $rowArr[] = "4630133744";
        $rowArr[] = "COPY TAX INVOICE"; // or ORIGINAL TAX INVOICE

        $tempDtlArr = array();
        $hasError = false;
        foreach($ord as $d){ //detail rows.

         if(abs($d['document_qty'])>0){  //include zero qty.

            if (!isset($productGTINs[$d["product_uid"]]) || (empty($productGTINs[$d["product_uid"]]["sku_gtin"]))) {
              $hasError = true;
              break;
            }

            $tempDtlArr[] = 'D'; // Detail Row
            $tempDtlArr[] = $ord[0]['invoice_number'];
            $tempDtlArr[] = $productGTINs[$d["product_uid"]];
            $tempDtlArr[] = trim(str_replace(array('"'),array(''),$d['product_code']));
            $tempDtlArr[] = trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description']));
            $tempDtlArr[] = abs($d['document_qty']);
            $tempDtlArr[] = $d['extended_price']; // Item Total
            $tempDtlArr[] = $d['vat_amount'];
            $tempDtlArr[] = $d['total'];

         }
        } //eo detail

        if ((count($tempDtlArr)>0) && (!$hasError)) {
          $dataArr[] = $rowArr; // add header
          $dataArr[] = $tempDtlArr; // add detail
          $totExcl += $ord[0]["exclusive_total"];
          $docCnt++;
        }

      } //eo special field check
    } //eo documents

    // This is just a safeguard check in case of mods later to this script that unknowingly move the col pos.
    if ($dataArr[0][4]=="?fseq") {
      $dataArr[0][4] = $fSeq;
    } else {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description = get_class($this)." failed to update header fseq row (not ?fseq) for nr.UID {$recipientUId}.";
      BroadcastingUtils::sendAlertEmail("System Error", $this->errorTO->description, "Y", true);
      return $this->errorTO;
    }
    // This is just a safeguard check in case of mods later to this script that unknowingly move the col pos.
    if ($dataArr[0][5]=="?cnt") {
      $dataArr[0][5] = $docCnt;
    } else {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description = get_class($this)." failed to update header doc count row (not ?cnt) for nr.UID {$recipientUId}.";
      BroadcastingUtils::sendAlertEmail("System Error", $this->errorTO->description, "Y", true);
      return $this->errorTO;
    }

    // add the trailer
    $dataArr[] = array("T",$totExcl);

    foreach ($dataArr as &$d) {
      $d = join("|",$d);
    }
    unset($d);

    $data = join("\r\n",$dataArr);


    //create file only if there are successful items.
    $filePath = false;
    if(count($successSEUIdArr)>0){

      //determine seq.
      $seqFilename = str_replace('[@FSEQ]', $fSeq, $filename);


      //write physical file
      $filePath = $this->createFile($folder, $seqFilename, $data);  //places file in correct folder
      if($filePath == false){
        BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
        return $this->errorTO;
      }
    }


    // copy to ftp folder
    $copy = copy($ROOT.$filePath, DIR_DATA_SURESERVER_NON_FTP_FROM."ihs/".basename($filePath));
    if(!$copy){
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description="failed copying file from archives {$filePath} to ftp dir in IHSMassCashInvoiced.php";
      BroadcastingUtils::sendAlertEmail("Error in Extract Adaptor IHSMassCashInvoiced", $this->errorTO->description, "Y", false);
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
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "231,EAN,SKU", "", FLAG_ERRORTO_ERROR);
      if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
        return $this->errorTO;
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
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
class CapeHerbInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class CapeHerbInvoicedInit extends extractController {


  private $principalUid = 74; //uid of principal extract.
  private $filename = 'INV[@FSEQ].XML';  //main controller will build full filename with seq. for us.


  public function generateOutput(){

    global $ROOT, $PHPFOLDER;


    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];
    $folder = $this->principalUid . '_capeherb'; // . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.


    //use the receipients listed in the notification table instead of hard coding them!!!
    //expecting only one row loaded per principal extract
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_CUSTOM);
    if (count($reArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Export failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }


    $recipientUId = $reArr[0]['uid'];

    // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
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
    $psms=array();
    foreach($seDocs as $k=>$r){
      $grpDocs[$r['dm_uid']][] = $r;
      $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    $newcodes = 'Y';
    if (sizeof($psms)>0) {          
          if ($newcodes == 'Y') {
          	$depotTrans = array(
            190 => "D6", // DC CHS - Imperial WC CHS
            202 => "D4",
            195 => "D5", // DC CHS - Loginet
            104 => "D7"); // L&B 
            $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 339, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
          } else {
          	$depotTrans = array(
            209 => "F2", // DC CHS - Imperial JHB CHS
          	210 => "F3", // DC CHS - Imperial WC CHS
            218 => "F6"); // L&B
            $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 35, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
          }
    } 
    /*
     * START BUILDING OUTPUT
     */
    $data = "<?xml version='1.0' encoding='Windows-1252'?>\n".
            "<SalesOrders xmlns:xsd='http://www.w3.org/2001/XMLSchema-instance' xsd:noNamespaceSchemaLocation='SORTOIDOC.XSD'>\n".
            " <Orders>\n";

    $errorSEUIdArr = array();
    $successSEUIdArr = array();
    foreach($grpDocs as $hr){
     if($hr[0]['depot_uid'] != 106) {
       if(empty($sfvals[$hr[0]["principal_store_uid"]]['value'])){  //has no special field...

         $errorSEUIdArr[] = $hr[0]['se_uid']; //list of smart event errors

       } else {

         $successSEUIdArr[] = $hr[0]['se_uid']; //list of smart event success
         $sval = $sfvals[$hr[0]["principal_store_uid"]]["value"]; //spf value.

         
        
         $region = (isset($depotTrans[$hr[0]["depot_uid"]])) ? $depotTrans[$hr[0]["depot_uid"]] : "  ";

         if($region == "F6"){
        $poNumPrepend = "L";
        } else {
        $poNumPrepend = "U";
        };

  //       $poNumPrepend = ($hr[0]["alt_code"] == "P") ? ("P") : ("C");
         $docNum = abs($hr[0]["document_number"]);
         $poLn = 1;

         //header rows
         $data .=   "   <OrderHeader>\n".
                    "     <CustomerPoNumber>{$poNumPrepend}{$docNum} - {$hr[0]['customer_order_number']}</CustomerPoNumber>\n".
                    "     <OrderActionType>A</OrderActionType>\n".
                    "     <Customer>{$sval}</Customer>\n".
                    "     <OrderDate>{$hr[0]['invoice_date']}</OrderDate>\n". //invoice date
                    "   </OrderHeader>\n".
                    "   <OrderDetails>\n";

          //detail rows.
          foreach($hr as $d){

            //invoice qty blank skip row.
            if(abs($d['document_qty'])==0){
              $poLn++;
            } else {

              $itemPerCase = ($d['items_per_case']!=1)?(str_pad($d['items_per_case'], 2, 0, STR_PAD_LEFT)):12;

              $data .=  "     <StockLine>\n".
                        "       <CustomerPoLine>" . str_pad($poLn, 4, 0, STR_PAD_LEFT) . "</CustomerPoLine>\n". //use numbering from 1, still incre if inv qty blank
                        "       <LineActionType>A</LineActionType>\n".
                        "       <StockCode>{$d['product_code']}</StockCode>\n". //product code
                        "       <Warehouse>{$region}</Warehouse>\n".
                        "       <OrderQty>{$d['document_qty']}</OrderQty>\n". //invoice qty
                        "       <OrderUom>EA</OrderUom>\n".
                        "       <Price>" . number_format(round($d['selling_price'], 2), 2, '.', '') . "</Price>\n". //selling
                        "       <PriceUom>C{$itemPerCase}</PriceUom>\n". //units per case C:product Items per Case
                        "       <PriceCode>A</PriceCode>\n".
                        "     </StockLine>\n";
               $poLn++;
            }
          }

          $data .= "    </OrderDetails>\n";

        }

      }
    }
      //close
      
      $data .= "  </Orders>\n".
              "</SalesOrders>";
      
    

    //create file only if there are successful items.
    $filePath = false;
    if(count($successSEUIdArr)>0){

      //determine seq.
      $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid);
      if($seqFilename==false){
        BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in CapeHerbInvoiced!", "Y");
        return $this->errorTO;
      }

      //write physical file
      $filePath = $this->createFile($folder, $seqFilename, $data);  //places file in correct folder
      if($filePath == false){
        BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in CapeHerbInvoiced on file creation";
        return $this->errorTO;
      }
    }


    // SETUP DISTRIBUTION
    $postingDistributionTO = new PostingDistributionTO;
    $postingDistributionTO->DMLType = "INSERT";
    $postingDistributionTO->deliveryType = BT_EMAIL;
    $postingDistributionTO->subject = $this->getTemplateInvoiceSubject(); //might have no filename if all errors therefore don't display on subject line...
    $postingDistributionTO->body = $this->getTemplateBody($principalName, count($successSEUIdArr), count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
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
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "35", "", FLAG_ERRORTO_ERROR);
      if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
        return $this->errorTO;
      }
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
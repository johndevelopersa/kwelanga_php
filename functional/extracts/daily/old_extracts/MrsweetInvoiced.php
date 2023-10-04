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
class MrsweetInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class MrsweetInvoicedInit extends extractController {


  private $principalUid = 104; //uid of principal extract.
  private $invFilename = 'INV[@FSEQ].csv';  //invoice filename
  private $canFilename = 'CAN[@FSEQ].csv';  //cancelled filename
  private $crnFilename = 'CRN[@SSEQ]_UL[@FSEQ].csv';  //credit note filename
  private $debFilename = 'DEB[@SSEQ]_UL[@FSEQ].csv';  //debit note filename


  public function generateOutput(){

    global $ROOT, $PHPFOLDER;


    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];
    $folder = $this->principalUid . '_mrsweet'; # . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.


    //use the receipients listed in the notification table instead of hard coding them!!!
    //expecting only one row loaded per principal extract
    //**************NB!!!!**************
    //we will be using the ftp details provided on the recipient.
    //**************NB!!!!**************
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_CUSTOM);
    if (count($reArr)==0){
      BroadcastingUtils::sendAlertEmail("System Error", "Export failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }


    $recipientUId = $reArr[0]['uid'];


    // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
      //invoices and cancelled items
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid,
                                                     $recipientUId,
                                                     $inclCancelled = true,
                                                     $p_dtArr = false,
                                                     $p_wDSArr = false,
                                                     $fromInvDate=false,
                                                     $toInvDate=false,
                                                     $chainUIdIn=false,
                                                     $dataSource="EDI",
                                                     $capturedBy="MSEVO");  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }

      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid,
                                                             $recipientUId,
                                                             $p_dtArr = false,
                                                             $fromInvDate=false,
                                                             $toInvDate=false,
                                                             $sourceDataSource="EDI",
                                                             $sourceCapturedBy="MSEVO");  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
    }

    //will return all queued items...
    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($this->principalUid, $recipientUId);


    //group array
    $grpDocs = array();
    $psms=array();
    foreach($seDocs as $k=>$r){
      if($r['depot_uid'] != 105){
        //place into which type...
        $index = $this->lookupExtractType($r);
        $grpDocs[$index][$r['dm_uid']][] = $r;
        $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];

      }
    }


    /*  SUCCESS POINT - 1  */
    //nothing to do...
    if(count($grpDocs)==0){
      echo "Successfully Completed Extract : ".get_class($this)." - No entries!<br>";
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
      return $this->errorTO;
    }


    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 92, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }

    $depotArr = array(
                        14 => 'JHBUll', //UB
                        2 => 'JHBUll',  //UB
                        5 => 'CTUll',   //UC
                        6 => 'PEUll',   //FP/PE
                        7 => 'ELUll',   //TE
                        3 => 'DBUll'    //UD
                     );


    $errorSEUIdArr = array(); //update errors at the end.

    //BUILD FILE FOR EACH DOCUMENT, treat each document as an extract.
    foreach($grpDocs as $type => $docArr){

      foreach($docArr as $doc){

        $dataArr = array();
        $successSEUIdArr = array();

        //echo '<pre>';
        //var_dump($doc[0]);

        if($type == 'can'){

          //can
          //build output
          $rowArr = array();
          $rowArr[] = 'RT' . abs($doc[0]['document_number']);
          $rowArr[] = date('d/m/Y', strtotime($doc[0]['invoice_date']));  //14/01/2013
          $rowArr[] = (!empty($doc[0]['reason_description'])) ? trim($doc[0]['reason_description']) : 'UNKNOWN REASON';  //reason
          $dataArr[] = join(',',$rowArr);

          $filename = str_replace('[@FSEQ]', abs($doc[0]['document_number']), $this->canFilename);

          $successSEUIdArr[] = $doc[0]['se_uid']; //list of smart event success

        } else {

          if(empty($sfvals[$doc[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...

            $errorSEUIdArr[] = $doc[0]['se_uid']; //list of smart event errors

          } else {

            $successSEUIdArr[] = $doc[0]['se_uid']; //list of smart event success

            //'inv','crn',deb
            switch ($type){
              case 'inv':
                $typeID = 4;
                $filename = str_replace('[@FSEQ]', abs($doc[0]['document_number']), $this->invFilename);
                break;
              case 'crn':
                $typeID = 1;
                $filename = str_replace('[@SSEQ]', abs($doc[0]['source_document_number']), $this->crnFilename);
                $filename = str_replace('[@FSEQ]', abs($doc[0]['alternate_document_number']), $filename);
                break;
              case 'deb':
                $typeID = 4;
                $filename = str_replace('[@SSEQ]', abs($doc[0]['source_document_number']), $this->debFilename);
                $filename = str_replace('[@FSEQ]', abs($doc[0]['alternate_document_number']), $filename);
                break;
            }


            foreach($doc as $i => $d){ //detail rows.

              //start building output
              $rowArr = array();
              $rowArr[] = $typeID;
              $rowArr[] = '0';
              $rowArr[] = '0';
              if($type == 'inv'){
                $rowArr[] = 'UL' . abs($doc[0]['invoice_number']);  //document_number
              } else {
                $rowArr[] = 'UL' . abs($doc[0]['alternate_document_number']);  //alternate document_number
              }
              $rowArr[] = $sfvals[$doc[0]["principal_store_uid"]]['value'];
              $rowArr[] = trim($doc[0]['deliver_name']);
              if($type != 'inv'){
                $rowArr[] = 'UL' . abs($doc[0]['invoice_number']);  //UL + invoice_number
              } else {
                $rowArr[] = '';
              }
              $rowArr[] = date('d/m/Y', strtotime($doc[0]['invoice_date']));  //14/01/2013
              $rowArr[] = '0';
              $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_name']));
              $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_add1']));
              $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_add2']));
              $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['deliver_add3']));
              $rowArr[] = '';
              $rowArr[] = '';
              $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_name']));
              $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_add1']));
              $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_add2']));
              $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[0]['bill_add3']));
              $rowArr[] = '';
              $rowArr[] = '';
              $rowArr[] = ''; //P_REPCD
              $rowArr[] = ''; //P_PROJC
              if($type == 'inv'){

                $rtPrefix = substr(str_replace(array('ORD','.CSV'), array('',''), strtoupper($doc[0]['incoming_file'])),0,2);
                if($rtPrefix == 'RT'){ //if document starts with a 5.
                  $rowArr[] = 'RT' . abs($doc[0]['document_number']);
                } else {
                  $rowArr[] = abs($doc[0]['document_number']);
                }
              } else {
                $rowArr[] = 'UL' . abs($doc[0]['invoice_number']);  //UL + invoice_number
              }
              $rowArr[] = str_replace(array('"',"'",','), array('','',''), trim($doc[0]['customer_order_number']));
              $rowArr[] = '0';
              $rowArr[] = '0';
              $rowArr[] = '0';
              $rowArr[] = '0';
              $rowArr[] = (isset($depotArr[$doc[0]['depot_uid']])) ? $depotArr[$doc[0]['depot_uid']] : '';
              $rowArr[] = trim($doc[$i]['detail_product_code']) . trim($doc[$i]['product_code']);
              $rowArr[] = str_replace(array('"',"'",','),array('','',''), trim($doc[$i]['product_description']));
              $rowArr[] = abs($doc[$i]['document_qty']); //use abs as we store credit notes negatively.
              $rowArr[] = number_format(round($doc[$i]['selling_price'], 2), 2, '.', ''); //SELLING PRICE - NUM
              $rowArr[] = ($doc[$i]['discount_value'] > 0) ? number_format(round($doc[$i]['discount_value'] / $doc[$i]['selling_price'] * 100, 2), 2, '.', '') : '0.00';
              $rowArr[] = '1';
              $rowArr[] = '';
              $rowArr[] = '';
              $rowArr[] = '0';
              $rowArr[] = '';
              $rowArr[] = '0';
              $rowArr[] = '';
              $rowArr[] = '';
              $rowArr[] = '';
              $rowArr[] = '1';
              $rowArr[] = '0';

              if(abs($doc[$i]['document_qty']) > 0){ //skip if qty is zero.
                $dataArr[] = join(',', $rowArr);
              }

            } //eof detail
          }
        }


        /*------------------ SUCCESS : START ---------------*/
        //success must be 1 for us to queue the ftp item.
        if(count($successSEUIdArr)>0){


          $data = join("\r\n", $dataArr);

          //write physical file - placed in extracts folder
          $filePath = $this->createFile($folder, $filename, $data);  //places file in correct folder
          if($filePath == false){
            BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." on backup file creation";
            return $this->errorTO;
          }

          //create actual file that gets picked up.
          $toFolder = DIR_DATA_SURESERVER_NON_FTP_FROM . 'mrsweet/tomrs/';
          $fp = file_put_contents($toFolder . $filename, $data);
          if($fp != strlen($data)){
            BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." on actual file creation";
            return $this->errorTO;
          }

          //create mister sweet backup...
          $bkupFolder = CommonUtils::createBkupDirs($toFolder);
          $fp = file_put_contents($bkupFolder . $filename, $data);


          $bIResult = $this->postBIDAO->setSmartEventStatus($successSEUIdArr[0], basename($filePath), "");
          if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatus with {$bIResult->description}";
            BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
            return $this->errorTO;
          }

        }
        /*------------------ SUCCESS : END ---------------*/

      } //eof single document

    } //eof all documents



    /*------------------ ERRORS : START ---------------*/
    //ERRORS - bulk action and email out...
    if (sizeof($errorSEUIdArr) > 0) {


        // SETUP DISTRIBUTION
        $postingDistributionTO = new PostingDistributionTO;
        $postingDistributionTO->DMLType = "INSERT";
        $postingDistributionTO->deliveryType = BT_EMAIL;
        $postingDistributionTO->subject = $this->getTemplateErrorSubject(); //might have no filename if all errors therefore don't display on subject line...
        $postingDistributionTO->body = $this->getTemplateBodyError($principalName, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));

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
          $this->errorTO->description = "Failed in ".get_class($this)." extract no valid Recipient/Contact found, no outgoing error mail generated!";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }

        //MARK SE AS "E", for extract errors display screen.
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "92", "", FLAG_ERRORTO_ERROR);
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
    }
    /*------------------ ERRORS : END ---------------*/


    echo "Successfully Completed Extract : ".get_class($this)."<br>";

    /*  SUCCESS POINT - 2  */
    $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
    $this->errorTO->description = "Successful";
    return $this->errorTO;

  }


  private function lookupExtractType($r){
    $index = 'inv';
    if(in_array($r['document_type_uid'], array(DT_ORDINV, DT_ORDINV_ZERO_PRICE, DT_DELIVERYNOTE))){
      $index = 'inv';
      if(in_array($r['document_status_uid'],array(DST_CANCELLED, DST_CANCELLED_NOT_OUR_AREA))){
        $index = 'can';
      }
    } elseif ($r['document_type_uid']==DT_CREDITNOTE){
      $index = 'crn';
    } elseif ($r['document_type_uid']==DT_DEBITNOTE){
      $index = 'deb';
    }
    return $index;
  }


}


//force run!
if ($runMe) {
  $obj = new MrsweetInvoiced();
  $obj->generateOutput();
  $dbConn->dbinsQuery("commit");
}


?>
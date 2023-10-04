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
class CreightonInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class CreightonInvoicedInit extends extractController {


  private $principalUid = 35; //uid of principal extract.
  private $filename = 'PAS10[@FSEQ].csv';  //main controller will build full filename with seq. for us.
  private $crnFilename = 'CRN191[@FSEQ].csv';  //credit note filename


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
      BroadcastingUtils::sendAlertEmail("System Error", "Export failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }

    $recipientUId = $reArr[0]['uid'];

    // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = true);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
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
      //place into groups
      $index = 'i'; //invoices
      if(in_array($r['document_status_uid'], array(DST_CANCELLED, DST_CANCELLED_NOT_OUR_AREA))){
        $index = 'can'; //cancelled
      }
      if ($r['document_type_uid'] == DT_CREDITNOTE){
        $index = 'c';
      }
      $grpDocs[$index][$r['dm_uid']][] = $r;  //group by index.
      $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }


    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 24, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");  //Account - S/Sure
    }



    //BUILD FILE FOR EACH TYPE -> INVOICES AND CANCELLED.
    foreach($grpDocs as $type => $hr){

      //depot translate (clipper program)
      $depotTrans = array(
          2 =>   50, //UJ
          3 =>   51, //UD
          5 =>   52, //UC
          6 =>   54, //FP
          7 =>   55 //TE
      );

      /*-------------------------------------------------*/
      /* START BUILDING OUTPUT
       */

      $dataArr = array();
      $errorSEUIdArr = array();
      $successSEUIdArr = array();


      foreach($hr as $ord){ //document loop

        //which special field is based on what the product is, PP - or S/SURE.
        //$sfvals = (substr(trim(strtoupper($ord[0]["product_description"])),0,2) == 'PP') ? $sfvalsAQUE : $sfvalsSURE;

        if(empty($sfvals[$ord[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...

          $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors

        } else {

          $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

          if($type == 'i'){
            $regionArr = (isset($depotTrans[$ord[0]['depot_uid']])) ? $depotTrans[$ord[0]['depot_uid']] : '  ';
          } else if($type == 'c'){
            $regionArr = (isset($depotTrans[$ord[0]['depot_uid']])) ? $depotTrans[$ord[0]['depot_uid']] : '  ';
          } else {
            $regionArr = ' ';  //region is blank for can
          }
          $storeAcc = $sfvals[$ord[0]["principal_store_uid"]]['value'];


          //period
          $period = '00';
          switch (date("m", strtotime($ord[0]["invoice_date"]))) {

            case '01':
              $period = 11;
              break;

            case '02':
              $period = 12;
              break;

            case '03':
              if(date("d", strtotime($ord[0]["invoice_date"])) < 8){  //01-03 to 01-07 gets period 13.
                $period = 13;
              } else {
                $period = 1;
              }
              break;

            case '04':
              $period = 2;
              break;

            case '05':
              $period = 3;
              break;

            case '06':
              $period = 4;
              break;
            case '07':
              $period = 5;
              break;

            case '08':
              $period = 6;
              break;

            case '09':
              $period = 7;
              break;

            case '10':
              $period = 8;
              break;

            case '11':
              $period = 9;
              break;

            case '12':
              $period = 10;
              break;
          }

          /* PASTEL HEADER */
          //array containing list of row values
          $rowArr = array();
          $rowArr[] = '"HEADER"';
          if($type == 'i' || $type == 'can'){
            $rowArr[] = '"UL' . substr($ord[0]['document_number'],-6).'"';
          } else {
            $rowArr[] = '"CUL' . substr($ord[0]['document_number'],-5).'"';
          }
          $rowArr[] = '" "';  //space(1);
          $rowArr[] = '"Y"';  //printed
          $rowArr[] = '"'.$storeAcc.'"';  //CUSTOMER CODE - Pastel Account.
          $rowArr[] = '"'.$period.'"';  //period number

          if($type == 'i' || $type == 'can'){
            $rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["invoice_date"])).'"';  //DATE (DD/MM/YYYY)
            $rowArr[] = '"'.trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"])).'"';
          } else {
            $rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["order_date"])).'"';  //DATE (DD/MM/YYYY)
            $rowArr[] = '"'.trim(str_replace(array('"',"'"),array('',''),'"UL' . substr($ord[0]['source_document_number'],-6))).'"';
          }

          $rowArr[] = '"N"';  //IN / EX - CHAR
          $rowArr[] = '"0"';  //discount
          $rowArr[] = '"NedBank"';  //MESSAGE - CHAR
          $rowArr[] = '"Branch Code: 164826"';   //MESSAGE - CHAR
          $rowArr[] = '"Account Number: 1020063092"';   //MESSAGE - CHAR
          $rowArr[] = '"'.trim($ord[0]["deliver_name"]).'"';
          $rowArr[] = '"'.trim($ord[0]["deliver_add1"]).'"';
          $rowArr[] = '"'.trim($ord[0]["deliver_add2"]).'"';
          $rowArr[] = '"'.trim($ord[0]["deliver_add3"]).'"';
          $rowArr[] = '"    "';
          $rowArr[] = '"DAYM"'; //SALES ANALYSIS - CHAR
          $rowArr[] = '""';

          if($type == 'i' || $type == 'can'){
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

            $detArr = array();
            $detArr[] = '"DETAIL"';
            $detArr[] = '0';
            $detArr[] = abs($d['document_qty']);
            $detArr[] = number_format(round($d['selling_price'], 2), 2, '.', ''); //SELLING PRICE - NUM
            $detArr[] = number_format(round(($d['selling_price']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
            $detArr[] = '" "';  //UNIT - CHAR
            $detArr[] = (substr($d['vat_rate'],0,2)=='14')?1:2;
            $detArr[] = '3'; //DISCOUNT TYPE
            $detArr[] = (abs($d['selling_price'])>0) ? round($d['discount_value'] / $d['selling_price'] * 100, 2) : 0; //DISCOUNT %  //stored value : discount_value backwards calculation.
            if($type == 'i'){
              $detArr[] = '"' . trim(str_replace(array('"'),array(''),$d['product_code'])) . '"';
              $detArr[] = '"' . trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description'])) . '"';
              $detArr[] = '4';  //unknown.
            } else if($type == 'c'){
              $detArr[] = '"' . trim(str_replace(array('"'),array(''),$d['product_code'])) . '"';
              $detArr[] = '"' . trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description'])) . '"';
              $detArr[] = '4';  //unknown.
            } else {
              $detArr[] = '"1002000"';
              $detArr[] = '"Sales - Ullmann"';
              $detArr[] = '6';  //for cancelled.
            }

            $detArr[] = '""';
            $detArr[] = '"'.$regionArr.'"';

            $dataArr[] = join(',',$detArr);

          } //eo detail
        } //eo special field check
      } //eo document

      $data = join("\r\n",$dataArr);


      //create file only if there are successful items.
      $filePath = false;
      if(count($successSEUIdArr)>0){

        //determine seq.
        $seqFilename = $this->setFilenameFSEQ((($type=='i' || $type=='can')?$this->filename:$this->crnFilename), $this->principalUid, false, 3);
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
      $postingDistributionTO->subject = (($type=='i' || $type=='can')?$this->getTemplateInvoiceSubject():$this->getTemplateCreditSubject()); //might have no filename if all errors therefore don't display on subject line...
      $postingDistributionTO->body = $this->getTemplateBody($principalName, count($successSEUIdArr), count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
      if($filePath!=false){
        $postingDistributionTO->attachmentFile = $filePath;
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
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "24", "", FLAG_ERRORTO_ERROR);
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
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
class PantrysInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class PantrysInvoicedInit extends extractController {


  private $principalUid = 30; //uid of principal extract.
  private $filename = 'PAS[@FSEQ].csv';  //main controller will build full filename with seq. for us.


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
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = false, $dtArr = array(DT_ORDINV));  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
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
      $grpDocs[$r['depot_uid']][$r['dm_uid']][] = $r;
      $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $sfvals = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 17, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }

    $periodArr = array( "01" => "11",
                        "02" => "12",
                        "03" => "13",
                        "04" => "2",
                        "05" => "3",
                        "06" => "4",
                        "07" => "5",
                        "08" => "6",
                        "09" => "7",
                        "10" => "8",
                        "11" => "9",
                        "12" => "10");

    //BUILD FILE FOR EACH DEPOT, treat each depot as an extract.
    foreach($grpDocs as $depotId => $hr){

      //depot translate (clipper program)
      $depotTrans = array(
          104   => array('doc'=>"NB", 'scde'=>"TRI"), //UJ
          202   => array('doc'=>"ND", 'scde'=>"TKLKZN"), //UD
          228   => array('doc'=>"NP",  'scde'=>"WOLFF"), //FP
          149   => array('doc'=>"NG",  'scde'=>"TREND"), //FP
          7     => array('doc'=>"INT",  'scde'=>"ULLEL") //TE
      );
      $regionLkArr = (isset($depotTrans[$depotId])) ? $depotTrans[$depotId] : array('doc'=>"IN",  'scde'=>"");

      /*-------------------------------------------------*/
      /* START BUILDING OUTPUT
       */

      $dataArr = array();
      $errorSEUIdArr = array();
      $successSEUIdArr = array();


      foreach($hr as $ord){ //document loop

        if(empty($sfvals[$ord[0]["principal_store_uid"]]['value'])){  //has no special field and/or blank...

          $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors

        } else {

          $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success
          
          $pnpchain = array(257, 261, 262, 2105);

          /* PASTEL HEADER */
          //array containing list of row values
          $rowArr = array();
          $rowArr[] = '"HEADER"';
          $rowArr[] = '"'.$regionLkArr['doc'] . substr($ord[0]['document_number'],-6).'"';
          $rowArr[] = '" "';  //space(1);
          $rowArr[] = '"Y"';  //printed
          $rowArr[] = '"'.$sfvals[$ord[0]["principal_store_uid"]]["value"].'"';  //CUSTOMER CODE - Pastel Account.
          $rowArr[] = '"'.$periodArr[date("m", strtotime($ord[0]["invoice_date"]))].'"';  //period number
          $rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["invoice_date"])).'"';  //DATE (DD/MM/YYYY)
          if(in_array(trim($ord[0]["principal_chain_uid"]),$pnpchain)) {
                $rowArr[] = '"'.trim($ord[0]["customer_order_number"]).'"'; //order number => branch code	
          } else {
                $rowArr[] = '"'.trim($ord[0]["branch_code"]).'"'; //order number => branch code	
          }
          $rowArr[] = '"N"';  //IN / EX - CHAR
          $rowArr[] = '"0"';  //discount
          $rowArr[] = '""';  //MESSAGE - CHAR
          $rowArr[] = '""';   //MESSAGE - CHAR
          $rowArr[] = '""';   //MESSAGE - CHAR
          $rowArr[] = '"'.trim($ord[0]["deliver_name"]).'"';
          $rowArr[] = '"'.trim($ord[0]["deliver_add1"]).'"';
          $rowArr[] = '"'.trim($ord[0]["deliver_add2"]).'"';
          $rowArr[] = '"'.trim($ord[0]["deliver_add3"]).'"';
          if(in_array(trim($ord[0]["principal_chain_uid"]),$pnpchain)) {
                $rowArr[] = '"'.trim($ord[0]["branch_code"]).'"'; //order number => branch code	
          } else {
                $rowArr[] = '"'.trim($ord[0]["customer_order_number"]).'"'; //order number => branch code	
          }
          $rowArr[] = '"'.$regionLkArr['scde'].'"'; //SALES ANALYSIS - CHAR
          $rowArr[] = '""';
          $rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["invoice_date"])).'"';  //CLOSING DATE (DD/MM/YYYY)
          $rowArr[] = '""';
          $rowArr[] = '""';
          $rowArr[] = '""';
          $rowArr[] = '0';
          $rowArr[] = '"N"';
          $rowArr[] = '" "';
          $rowArr[] = '" "';

          $dataArr[] = join(',',$rowArr);


          foreach($ord as $d){ //detail rows.

            if(abs($d['document_qty'])>0){  //invoice qty greater then zero.

              $detArr = array();
              $detArr[] = '"DETAIL"';
              $detArr[] = '0';
              $detArr[] = abs($d['document_qty']);
              $detArr[] = number_format(round($d['selling_price'], 2), 2, '.', ''); //SELLING PRICE - NUM
              $detArr[] = number_format(round(($d['selling_price']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
              $detArr[] = '" "';  //UNIT - CHAR <<<<<<<<<<<<<<<<<<<<<<<<<<<<<
              $detArr[] = (substr($d['vat_rate'],0,2)=='15')?11:2;
              $detArr[] = '3'; //DISCOUNT TYPE
              $detArr[] = round($d['discount_value'] / $d['selling_price'] * 100, 2); //DISCOUNT %  //stored value : discount_value backwards calculation.
              $detArr[] = '"'.trim($d['product_code']).'"';
              $detArr[] = '"'.trim($d['product_description']).'"';
              $detArr[] = '4';  //unknown.
              $detArr[] = '" "';
              $detArr[] = '"901"';

              $dataArr[] = join(',',$detArr);

            }
          } //eo detail
        } //eo special field check
      } //eo document

      $data = join("\r\n",$dataArr);


      //create file only if there are successful items.
      $filePath = false;
      if(count($successSEUIdArr)>0){

        //determine seq.
        $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid);
        if($seqFilename==false){
          BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
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
      $postingDistributionTO->subject = $this->getTemplateInvoiceSubject($regionLkArr['doc']); //might have no filename if all errors therefore don't display on subject line...
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
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "17", "", FLAG_ERRORTO_ERROR);
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
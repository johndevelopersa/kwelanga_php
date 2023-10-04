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
class MahewuInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


class MahewuInvoicedInit extends extractController {


  private $principalUid = 138; //uid of principal extract.
  private $fiscalMonthStart = "07"; // start of fiscal year = 01July


  public function generateOutput(){

    global $ROOT, $PHPFOLDER;


    $fiscalYear = ((date("m")>=$this->fiscalMonthStart)?(date("Y")+1):date("Y"));
    $depotMapping = array("2"=>"FG1","3"=>"DUR","5"=>"CAP","6"=>"PEL","7"=>"FG2","116"=>"NEL");


    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];
    $folder = $this->principalUid . '_mahewu'; // . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.


    //use the receipients listed in the notification table instead of hard coding them!!!
    //expecting only one row loaded per principal extract
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_CUSTOM);
    if (count($reArr)==0){
      BroadcastingUtils::sendAlertEmail("System Error", "Export failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];


     // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled=true);
      if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error","Export failed to call postExtractDAO->queueAllInvoiced in MahewuInvoiced.php ".$rTO->description,"Y");
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


    // group the doc result into its delimits
    $docs=array();
    $psms=array();
    foreach ($seDocs as $doc) {
      $docs[$doc["dm_uid"]][] = $doc;
      $psms[$doc["principal_store_uid"]]=$doc["principal_store_uid"];
    }

    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
      $ACCPAC_sfvals=$this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 171, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }


    //FILE CONTENTS
    $dataArr = array();
    $dataArr[] = array(
                  "RECTYPE","SHIUNIQ","SHINUMBER","CUSTOMER","CUSTGROUP",
                  "BILNAME","BILADDR1","BILADDR2","BILADDR3","BILADDR4",
                  "BILCITY","BILSTATE","BILZIP","BILCOUNTRY","BILPHONE",
                  "BILFAX","BILCONTACT","BILEMAIL","BILPHONEC","BILEMAILC",
                  "CUSTDISC","PRICELIST","PONUMBER","TERMS","REFERENCE",
                  "SHIDATE","EXPDATE","SHIFISCYR","SHIFISCPER","LOCATION",
                  "DESC","COMMENT","SALESPER1","RECALCTAX","TAXGROUP",
                  "TAUTH1","TCLASS1","TEAMOUNT1","TEXEMPT1","AUTOTAXCAL",
                  "ENTEREDBY","INVDATE","INHOMECURR","INRATETYPE","INSOURCURR",
                  "INRATEDATE","INRATE"
                );
    $dataArr[] = array(
                  "RECTYPE","SHIUNIQ","LINENUM","ITEM","DESC",
                  "PRICELIST","CATEGORY","LOCATION","PICKSEQ","EXPDATE",
                  "QTYSHIPPED","SHIUNIT","UNITCONV","UNITPRICE","PRICEOVER",
                  "UNITCOST","MOSTREC","AVGCOST","UNITPRCDEC","PRICEUNIT",
                  "PRIUNTPRC","PRIUNTCONV","PRIPERCENT","PRIAMOUNT","BASEUNIT",
                  "PRIBASPRC","PRIBASCONV","COSTUNIT","COSUNTCST","COSUNTCONV",
                  "EXTSHIMISC","SHIDISC","EXTSCOST","EXTOVER","UNITWEIGHT"
                );
    $dataArr[] = array(
                  "RECTYPE","SHIUNIQ","LINENUM","SERIALNUMF"
                );
    $dataArr[] = array(
                  "RECTYPE","SHIUNIQ","LINENUM","LOTNUMF"
                );
    $dataArr[] = array(
                  "RECTYPE","SHIUNIQ","UNIQUIFIER"
                );
    $dataArr[] = array(
                  "RECTYPE","SHIUNIQ"
                );
    $dataArr[] = array(
                  "RECTYPE","SHIUNIQ","LINENUM","OPTFIELD"
                );
    $seUIDs = array();
    $errorSEUIdArr = array();

    $currentFiscalMonth = ((date("m")>=$this->fiscalMonthStart)?(date("m")-$this->fiscalMonthStart+1):(12-$this->fiscalMonthStart+date("m")+1));

    foreach($docs as $doc){

        $accpac=((isset($ACCPAC_sfvals[$doc[0]["principal_store_uid"]]))?$ACCPAC_sfvals[$doc[0]["principal_store_uid"]]["value"]:"");
        $invNum=substr(str_pad(((trim($doc[0]["invoice_number"])=="")?$doc[0]["document_number"]:$doc[0]["invoice_number"]),8,"0",STR_PAD_LEFT),2); // always 6
        $dptCode=((isset($depotMapping[$doc[0]["depot_uid"]]))?$depotMapping[$doc[0]["depot_uid"]]:"?{$doc[0]["depot_uid"]}?"); // wrap depot in ?? if not found so that the process can continue

        if (empty($accpac)) {
          $errorSEUIdArr[]=$doc[0]["se_uid"];
          continue;
        }

        $seUIDs[]=$doc[0]["se_uid"];

        // RECTYPE 1
        $dataArr[] = array(
                        "1",$doc[0]["document_number"],"SH{$invNum}",$accpac,"01",
                        $doc[0]["bill_name"],$doc[0]["bill_add1"],$doc[0]["bill_add2"],$doc[0]["bill_add3"],"",
                        "","","","",$doc[0]["tel_no1"],
                        $doc[0]["tel_no2"],"",$doc[0]["email_add"],"","",
                        0,"RETAIL",$doc[0]["customer_order_number"],"14S",$doc[0]["customer_order_number"],
                        str_replace("-","",$doc[0]["order_date"]),str_replace("-","",$doc[0]["order_date"]),$fiscalYear,$currentFiscalMonth,$dptCode,
                        "","","",0,"SDVAT",
                        "SDVAT",3,"","",-1,
                        $doc[0]["data_source"],str_replace("-","",$doc[0]["invoice_date"]),"EMA","SP","EMA",
                        str_replace("-","",$doc[0]["order_date"]),1
                      );

        foreach ($doc as $dtl) {
          // RECTYPE 2
          $discPerc=(($dtl["selling_price"]>0)?(round($dtl["discount_value"]/$dtl["selling_price"]*100,2)):"0");
          $qty=((in_array($dtl["document_status_uid"],array(DST_CANCELLED,DST_CANCELLED_NOT_OUR_AREA)))?"0":$dtl["document_qty"]);
          $dataArr[] = array(
                          "2",$dtl["document_number"],$dtl["line_no"],$dtl["product_code"],$dtl["product_description"],
                          "RETAIL","MAH",$dptCode,"","",
                          $qty,"case",1,$dtl["selling_price"],0,
                          "","","","","case",
                          $dtl["selling_price"],1,0,0,"case",
                          "",1,"case","1",1,
                          $dtl["extended_price"],$discPerc,"",0,"1"
                        );
          /* deprecated - only used for optional fields which are not used
          // RECTYPE 7
          $dataArr[] = array(
                          "7",$dtl["document_number"],$dtl["line_no"],"BATCHNO"
                        );
          */
        }

    }


    //FILENAME
    $fP = $ROOT . FILE_ARCHIVE_EXTRACTS_PATH . $folder . "/";
    @mkdir($fP, 0777, true);
    $bkupFolder = CommonUtils::createBkupDirs($fP);
    $fileName = $bkupFolder.$this->bIDAO->getExtractFilename($this->principalUid, NT_DAILY_EXTRACT_CUSTOM);


    // FILE RESULT
    $fileResult = BroadcastingUtils::createCSVFile($dataArr, "", $fileName);
    if ($fileResult->type != FLAG_ERRORTO_SUCCESS){
      echo "Failed to create archive file from MahewuInvoiced.php!";
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed in MahewuInvoiced on filecreation with {$fileResult->description}";
      return $this->errorTO;
    }


    // SETUP DISTRIBUTION
    $postingDistributionTO = new PostingDistributionTO;
    $postingDistributionTO->DMLType = "INSERT";
    $postingDistributionTO->deliveryType = BT_EMAIL;
    $postingDistributionTO->subject = $this->getTemplateInvoiceSubject();
    $postingDistributionTO->body = $this->getTemplateBody($principalName, count($seUIDs), 0, $this->getManagementURL($this->principalUid));
    $postingDistributionTO->attachmentFile = str_replace("../","",$fileName);


    $recipientList = explode(",", $reArr[0]["user_uid_list"]); //if blank, sizeof() will still be 1
    $recipientsCheckCount = 0;  //check if atleast one distribution item was posted, incase of invalid recipients

    foreach($recipientList as &$re){

      $mfC = $this->miscDAO->getContactItem($this->principalUid, "", $re);
      if (sizeof($mfC)==0) {
        BroadcastingUtils::sendAlertEmail("System Error",get_class($this)." Extract for nr.UID {$recipientUId} has an invalid Recipient/Contact: '{$re}'.","Y", true);
        continue;
      }

      // save from having to lookup later again
      $re = array("user_uid"=>$re,"mfC_email"=>$mfC[0]["email_addr"]);

      $postingDistributionTO->destinationAddr = $mfC[0]["email_addr"];
      $dResult = $this->postDistributionDAO->postQueueDistribution($postingDistributionTO);
      if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = get_class($this)." Extract distribution queue failed for for nr.UID {$recipientUId} and Recipient/Contact: '{$re["user_uid"]}'.";
        BroadcastingUtils::sendAlertEmail("System Error", $this->errorTO->description, "Y", true);
        return $this->errorTO;
      } else {
        $recipientsCheckCount++;  //successful
      }

    }
    unset($re);


    if ($recipientsCheckCount==0) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed in ".get_class($this)." extract no valid Recipient/Contact found, no outgoing mail generated!";
      BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
      return $this->errorTO;
    }


    // UPDATE SMART EVENT in BULK
    if (sizeof($seUIDs)>0) {
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",",$seUIDs), basename($fileName), ""); //set smart event items where
      if ($bIResult->type != FLAG_ERRORTO_SUCCESS){
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in MahewuInvoiced on setting setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in MahewuInvoiced", "error setting setSmartEventStatusBulk " . $bIResult->description, "Y", false);
        return $this->errorTO;
      }
    }

    /*------------------ ERRORS : START ---------------*/
    //ERRORS - bulk action and email out...
    if (sizeof($errorSEUIdArr) > 0) {


      // SETUP DISTRIBUTION
      $postingDistributionTO = new PostingDistributionTO;
      $postingDistributionTO->DMLType = "INSERT";
      $postingDistributionTO->deliveryType = BT_EMAIL;
      $postingDistributionTO->subject = $this->getTemplateErrorSubject(); //might have no filename if all errors therefore don't display on subject line...
      $postingDistributionTO->body = $this->getTemplateBodyError($principalName, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));

      $recipientsCheckCount = 0;  //check if atleast one distribution item was posted, incase of invalid recipients

      foreach($recipientList as $re){
        if (!empty($re["mfC_email"])) {
          $postingDistributionTO->destinationAddr = $re["mfC_email"];
          $dResult = $this->postDistributionDAO->postQueueDistribution($postingDistributionTO);
          if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = get_class($this)." Extract distribution queue failed for for nr.UID {$recipientUId} and Recipient/Contact: '{$re["user_uid"]}'.";
            BroadcastingUtils::sendAlertEmail("System Error", $this->errorTO->description, "Y", true);
            return $this->errorTO;
          } else {
            $recipientsCheckCount++;  //successful
          }
        }

      }

      if ($recipientsCheckCount==0) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed in ".get_class($this)." extract no valid Recipient/Contact found, no outgoing error mail generated!";
        BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
        return $this->errorTO;
      }

      //MARK SE AS "E", for extract errors display screen.
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), 10, "", FLAG_ERRORTO_ERROR);
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

}


//direct run!
if ($runMe) {
  directRunExtract(__FILE__);
}


?>
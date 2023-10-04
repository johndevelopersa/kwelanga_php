<?php
/**********************************************************************************************
 **********************************************************************************************
 * *
 * * This job can run as many times per day as is necessary according to job scheduler.
 * *
 * * It executes notifications that occur throughout the day by triggers
 * *
 **********************************************************************************************
 **********************************************************************************************/

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/BIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostBIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExtractDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ImportDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'functional/extracts/daily/extractController.php');


//static method handler.
class SylkoCredits {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}


if (!isset($dbConn)) {
  $dbConn = new dbConnect();
  $dbConn->dbConnection();
}

class SylkoCreditsInit extends extractController {
  // calling program may already have set this in processExtractsBatch

  public static $principalSylko = "3";
  private $filename = 'CRD[@FSEQ].rfc';  //main controller will build full filename with seq. for us.

  public function generateOutput () {
    global $ROOT, $dbConn, $postDistributionDAO, $postBIDAO, $postExtractDAO, $extractDAO, $biDAO, $miscDAO, $importDAO;



    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem(self::$principalSylko);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];
    $folder = self::$principalSylko . '_' . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.


    $reArr = $this->bIDAO->getNotificationRecipients(self::$principalSylko, NT_DAILY_EXTRACT_CUSTOM);
    if (count($reArr)==0){
      BroadcastingUtils::sendAlertEmail("System Error", "Export failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];

    $eTO = new ErrorTO();

    $depotMap = $this->importDAO->getAllDepotPrincipalExportMapping($indexArray=ImportDAO::$PDP_EXP_MAP_INDEX_2); // directly accessible by [principal_uid][depot_uid]

     // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits(self::$principalSylko, $recipientUId, $dtArr = array(DT_CREDITNOTE));
      if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error","Export failed to call postExtractDAO->queueAllInvoiced in SylkoCredits.php ".$rTO->description,"Y");
      } else {
        $dbConn->dbinsQuery("commit;");
      }
    }

    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders(self::$principalSylko, $recipientUId);

    if (empty($seDocs)) {
      $eTO->type = FLAG_ERRORTO_SUCCESS;
      $eTO->description = "Successful";
      echo "<br>Successfully Completed Extract : SylkoCredits<br>";
      return $eTO;
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
      $ACCPAC_sfvals=$this->miscDAO->getPrincipalSpecialFieldValuesMultEntities(self::$principalSylko, 10, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
    }

    //FILENAME
    $seqFilename = $this->setFilenameFSEQ($this->filename, self::$principalSylko, $documentType=DT_CREDITNOTE, 5, $lenType = parent::setFilenameFSEQ_LenType_PAD);
    if($seqFilename==false){
      BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }


    //FILE CONTENTS
    $dataArr = array();
    $dataArr[] = "<RFC_File>";

    $seUIDs = $errorSEUIdArr = array();

    $exTotal=$total=0;

    foreach($docs as $doc){

        $seUIDs[]=$doc[0]["se_uid"];

        if ((!isset($depotMap[self::$principalSylko][$doc[0]["depot_uid"]])) || (trim($depotMap[self::$principalSylko][$doc[0]["depot_uid"]]["depot_code"])=="")) {
          BroadcastingUtils::sendAlertEmail("Extract Error : SylkoCredits", "Empty Principal-Depot Code! principalUid@ ".self::$principalSylko." depot@{$doc[0]["depot_uid"]}", "Y", $quietMode = false);
          $eTO->type = FLAG_ERRORTO_ERROR;
          return $eTO;
        }

        // store errors are handled differently
        $sylkoAccount=((isset($ACCPAC_sfvals[$doc[0]["principal_store_uid"]]))?$ACCPAC_sfvals[$doc[0]["principal_store_uid"]]["value"]:"");
        if (empty($sylkoAccount)) {
          $sylkoAccount = $doc[0]["old_account"];
          if (empty($sylkoAccount)) {
            $errorSEUIdArr[]=$doc[0]["se_uid"];
            continue;
          }
        }

        $originalInvoice=$doc[0]["source_document_number"];
        if (empty($originalInvoice)) {
          BroadcastingUtils::sendAlertEmail("Extract Error : SylkoCredits", "Original Source Document not known for SE_UID {$doc[0]["se_uid"]}! principalUid@ ".self::$principalSylko." depot@{$doc[0]["depot_uid"]}", "Y", $quietMode = false);
          $eTO->type = FLAG_ERRORTO_ERROR;
          return $eTO;
        }
        $originalInvoice=preg_replace("/^0+/","",trim($originalInvoice));

        $docDate=(((trim($doc[0]["invoice_date"])=="") || (trim($doc[0]["invoice_date"])=="0000-00-00"))?$doc[0]["order_date"]:$doc[0]["invoice_date"]);

        $dataArr[] = "<Order>";
        $dataArr[] = "<Header>";
        $dataArr[] = "<SysproCustomer>{$sylkoAccount}</SysproCustomer>";
        $dataArr[] = "<OrderDate>{$docDate}</OrderDate>";
        $dataArr[] = "<OriginalInvoice>{$originalInvoice}</OriginalInvoice>";
        $dataArr[] = "</Header>";

        foreach ($doc as $dtl) {

          $dataArr[] = "<OrderLine>";
          $dataArr[] = "<StockCode>{$dtl["product_code"]}</StockCode>";
          $dataArr[] = "<OrderQty>".abs($dtl["document_qty"])."</OrderQty>";
          $dataArr[] = "<CustPrice>".number_format($dtl["net_price"],2,".","")."</CustPrice>";
          $dataArr[] = "</OrderLine>";

        }

        $dataArr[] = "</Order>";

    }

    $dataArr[] = "</RFC_File>";

    //write physical file
    $filePath = $this->createFile($folder, $seqFilename, $data = join("\r\n",$dataArr));  //places file in correct folder
    if($filePath == false){
      BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Failed in SylkoCredits on file creation";
      return $this->errorTO;
    }

    // UPDATE SMART EVENT in BULK
    if (sizeof($seUIDs)>0) {
      $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",",$seUIDs), $seqFilename, ""); //set smart event items where
      if ($bIResult->type != FLAG_ERRORTO_SUCCESS){
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Failed in SylkoInvoiced on setting setSmartEventStatusBulk with {$bIResult->description}";
        BroadcastingUtils::sendAlertEmail("Error in SylkoInvoiced", "error setting setSmartEventStatusBulk " . $bIResult->description, "Y", false);
        return $eTO;
      }
    }

    // get array of user contacts
      $recipientList = explode(",", $reArr[0]["user_uid_list"]); //if blank, sizeof() will still be 1
      $destAddresses = array();
      foreach($recipientList as $re){
        $mfC = $this->miscDAO->getContactItem(self::$principalSylko, "", $re);
        if (sizeof($mfC)==0) {
          BroadcastingUtils::sendAlertEmail("System Error",get_class($this)." Extract for nr.UID {$recipientUId} has an invalid Recipient/Contact: '{$re}'.","Y", true);
          continue;
        }

        $destAddresses[] = array("email"=>$mfC[0]["email_addr"], "ftp"=>$mfC[0]["ftp_addr"]);

      }

     /*------------------ ERRORS : START ---------------*/
      //ERRORS - bulk action and email out...
      if (sizeof($errorSEUIdArr) > 0) {

          // SETUP DISTRIBUTION
          $postingDistributionTO = new PostingDistributionTO;
          $postingDistributionTO->DMLType = "INSERT";
          $postingDistributionTO->deliveryType = BT_EMAIL;
          $postingDistributionTO->subject = $this->getTemplateErrorSubject(); //might have no filename if all errors therefore don't display on subject line...
          $postingDistributionTO->body = $this->getTemplateBodyError($principalName, count($errorSEUIdArr), $this->getManagementURL(self::$principalSylko));

          $recipientsCheckCount = 0;  //check if atleast one distribution item was posted, incase of invalid recipients

          foreach($destAddresses as $re){
            if (!empty($re["email"])) {
              $postingDistributionTO->destinationAddr = $re["email"];
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
      /*
      // SETUP DISTRIBUTION.
      // NB !
      // Every distribution must be successfully stored for entire extract to be a success
      $postingDistributionTO = new PostingDistributionTO;
      $postingDistributionTO->DMLType = "INSERT";
      $postingDistributionTO->subject = "RetailTrading Extract : Daily Credit Notes ".date("Y-m-d");
      $postingDistributionTO->body = "Dear Sylko Representative,<br><br>
                                      Please find attached your daily Credit Notes extract.<br><br>
                                      Regards,<br>
                                      The RetailTrading Team<br><br>
                                      *** This is an automated email. Please do NOT reply to this email as the sending address mailbox is not monitored";
      $postingDistributionTO->attachmentFile = str_replace("../","",$filePath);
      foreach($destAddresses as $re){
        if (!empty($re["ftp"])) {
          $postingDistributionTO->deliveryType = BT_FTP;
          $postingDistributionTO->destinationAddr = $re["ftp"];
        } else {
          continue; // for time being ignore email contacts
          // $postingDistributionTO->deliveryType = BT_EMAIL;
          // $postingDistributionTO->destinationAddr = $re["email"];
        }

        $dResult = $this->postDistributionDAO->postQueueDistribution($postingDistributionTO);
        if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description = get_class($this)." Extract distribution queue failed for for nr.UID {$recipientUId} and Recipient/Contact: '{$re}'. (Attachment Queueing)";
          BroadcastingUtils::sendAlertEmail("System Error", $this->errorTO->description, "Y", true);
          return $this->errorTO;
        }
      }
*/
      // copy to ftp folder
      $copy = copy($ROOT.$filePath, DIR_DATA_SURESERVER_NON_FTP_FROM."sylko/XML/".basename($filePath));
      if(!$copy){
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description="failed copying file from archives {$filePath} to ftp dir in SylkoCredits.php";
        BroadcastingUtils::sendAlertEmail("Error in Extract Adaptor SylkoCredits", $this->errorTO->description, "Y", false);
        return $this->errorTO;
      }

    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";

    echo "<br>Successfully Completed Extract : SylkoCredits<br>";

    return $eTO;

  }

}

//direct run!
if ($runMe) {
  directRunExtract(__FILE__);
}

?>
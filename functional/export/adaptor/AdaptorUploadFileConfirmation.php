<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExportDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');

class UploadFileConfirmation{

    public $errorTO;
    private $dbConn;
    private $bIDAO;
    private $postBIDAO;
    private $exportDAO;
    private $importDAO;
    private $miscDAO;
    private $postDistributionDAO;
    private $sequenceDAO;


    function __construct($dbConn) {

    	$this->dbConn = $dbConn;
    	$this->errorTO = new ErrorTO;
      $this->bIDAO = new BIDAO($this->dbConn);
      $this->postBIDAO = new PostBIDAO($this->dbConn);
      $this->exportDAO = new ExportDAO($this->dbConn);
      $this->importDAO = new ImportDAO($this->dbConn);
      $this->depotDAO = new DepotDAO($this->dbConn);
      $this->miscDAO = new MiscellaneousDAO($this->dbConn);
      $this->postDistributionDAO = new PostDistributionDAO($this->dbConn);
      $this->sequenceDAO = new SequenceDAO(null);

    }


    //EXPORT FILE CONFIRMATION ITD - XML OUTPUT
    //
    // This ITD format only supports 1 output file per principal due to Vendor Number at header leve, but then multiple files can be within same output file.
    // This should be ok, because notifications (Export) are loaded at Principal Level anyway
    public function E_FC_ITDXML($notiArr, $seArr){

      global $ROOT;

      if((count($notiArr)>0) && (count($seArr)>0)){


      //RESULTSET
      $uidList = array();
      foreach($seArr as $se){$uidList[] = $se['data_uid'];}  //build list
      $flRows = $this->exportDAO->getExportFileLogList($notiArr["principal_uid"], join(',',$uidList));  //sql does grouping and line count.
      if(count($flRows)==0){
  		  $this->errorTO->type=FLAG_ERRORTO_ERROR;
  		  $this->errorTO->description = "Failed to get resultset from File Log from loaded Smart Events in ".get_class($this). " --> ".__FUNCTION__." for UIDs ".(join(',',$uidList)).".";
  		  return $this->errorTO;
      }

  		$principalUId = $notiArr["principal_uid"];
  		$principalCandyTops = "66";
  		$principalMrSweet = "104";
  		$principalDarsots = "234";
			$principalDarsotsChemicals = "235";
			$principalAllJoy = "236";

      //FILENAME - every recipient will get same file. l=local;  f=ftp
	    $lFilename = $this->bIDAO->getNotificationFilenameGeneric($notiArr['notification_uid'],"E",$notiArr["principal_uid"],true);
      $fileExt = '';
      $vendorCode="";
  		$vendorNo="";
  		if ($principalUId==$principalCandyTops) {
  			$vendorCode="ACT"; // may have to convert this to a table mapping in future
  			$vendorNo="6001914000005"; // this is a GLN - we might be able to store it in future, but the risk is that we dont want to populate orders_holding.gln with it as that will cause a lookup when processing store
  		} else if ($principalUId==$principalMrSweet) {
  			$vendorCode="MRS"; // may have to convert this to a table mapping in future
  			$vendorNo="6001704000000"; // this is a GLN - we might be able to store it in future, but the risk is that we dont want to populate orders_holding.gln with it as that will cause a lookup when processing store
  		} else if ($principalUId==$principalDarsots) {
  			$vendorCode="DFC"; // may have to convert this to a table mapping in future
  			$vendorNo="6001647000006"; // this is a GLN - we might be able to store it in future, but the risk is that we dont want to populate orders_holding.gln with it as that will cause a lookup when processing store
  		} else if ($principalUId==$principalDarsotsChemicals) {
  			$vendorCode="DMC"; // may have to convert this to a table mapping in future
  			$vendorNo="6009801812083"; // this is a GLN - we might be able to store it in future, but the risk is that we dont want to populate orders_holding.gln with it as that will cause a lookup when processing store
  		} else if ($principalUId==$principalAllJoy) {
  			$vendorCode="AJF"; // may have to convert this to a table mapping in future
  			$vendorNo="6003241000000"; // this is a GLN - we might be able to store it in future, but the risk is that we dont want to populate orders_holding.gln with it as that will cause a lookup when processing store
  		} else {
  			$this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description = "Vendor Code for Filenaming not referenced in AdaptorUploadFileConfirmation";
  			return $this->errorTO;
  		}


        //BUILD FILE WITHIN OUTPUT TYPE.
	    if($notiArr['output_type'] == OT_XML){

	      $fileExt = '.xml';

	      //build file contents.
	      //header
	      $fC = "<?xml version='1.0'?>\n";
	      $fC .= "<load_conf_file>\n";
	      $fC .= "<vendor_no>{$vendorNo}</vendor_no>\n";
	      $fC .= "<creation_date>".date('d/m/Y',strtotime(gmdate("d-m-Y H:i:s"))+(60*60*2))."</creation_date>\n";  //SA TIME. (GMT+2)
	      $fC .= "<creation_time>".date('H:i:s',strtotime(gmdate("H:i:s"))+(60*60*2))."</creation_time>\n";  //SA TIME. (GMT+2)

	      //rows
	      foreach($flRows as $fl){
	        $fC .= "<file_conf>\n";
	        $fC .= "<filename>".basename($fl['file_name'])."</filename>\n";

	        $fC .= "<loaded_date>".date('d/m/Y',strtotime($fl['processed_date'])+(60*60*2))."</loaded_date>\n";  //convert to SA time
	        $fC .= "<loaded_time>".date('H:i:s',strtotime($fl['processed_date'])+(60*60*2))."</loaded_time>\n";  //convert to SA time
	        $fC .= "<lines_loaded>".($fl["line_count"]+1)."</lines_loaded>\n"; // ITD require the line count to include a blank line which gets dropped by our XML wrapper parser
	        $fC .= "<status>".(($fl["status"]==FLAG_ERRORTO_SUCCESS)?"SUCCESSFUL":"UNSUCCESSFUL")."</status>\n";
	        $fC .= "<error_message>".$fl['error_msg']."</error_message>\n"; // ITD require a carriage return blank line

	        $fC .= "</file_conf>\n";
	      }

	      $fC .= "</load_conf_file>\n";  //footer

	    } else {
	      $this->errorTO->type=FLAG_ERRORTO_ERROR;
  		  $this->errorTO->description = "Export Adaptor ITDXML Only supports XML output.";
  		  return $this->errorTO;
	    }
	    //add more output types here...


	    $filename = $lFilename . $fileExt;  //add file ext.


	    //name change for delivery type
        if($notiArr['delivery_type'] == BT_FTP){

          $ftpFileSeq = $this->sequenceDAO->getFTPFileExportSequence();
          if ($ftpFileSeq=="") {
          	$this->errorTO->type=FLAG_ERRORTO_ERROR;
      			$this->errorTO->description = "Could not get File Sequence in AdaptorUploadFileConfirmation";
      			return $this->errorTO;
          }

          $ftpFilename = 'RTT.' . $vendorCode . '.' . (gmdate("Ymd")) . '.' . $ftpFileSeq . ".FILECONF" . $fileExt;  // ITD has an exacting filename, uppercase

	    } else {
	      $this->errorTO->type=FLAG_ERRORTO_ERROR;
  		  $this->errorTO->description = "Export Adaptor ITDXML Only supports FTP Delivery.";
  		  return $this->errorTO;
	    }


	    //CREATE FILE.
	    $bkupFolder = CommonUtils::createBkupDirs($ROOT.FILE_ARCHIVE_EXPORTS_PATH."itdynamics/");
	    $fSize = file_put_contents($bkupFolder.$filename, $fC);
	    if($fSize != strlen($fC)){
	      $this->errorTO->type=FLAG_ERRORTO_ERROR;
  		  $this->errorTO->description = "Export Adaptor ITDXML could not write export file: " . $bkupFolder.$filename;
  		  return $this->errorTO;
	    }


	    //recipients
        if($notiArr["user_uid_list"]==""){
  	      $this->errorTO->type=FLAG_ERRORTO_ERROR;
    		  $this->errorTO->description = "The File Confirmation Export has no recipients loaded for Uid: " . $notiArr['uid'];
    		  return $this->errorTO;
        }
  	    $recipients = explode(",",$notiArr["user_uid_list"]); // if blank, sizeof() will still be 1
  	    $this->errorTO->identifier = $distributionSourceIdentifier = CommonUtils::getRandomInteger(); //will be used later in export to set notify recip. table


  	    //Distribution
  	    $postingDistributionTO = new PostingDistributionTO;
        $postingDistributionTO->DMLType="INSERT";
        $postingDistributionTO->deliveryType = $notiArr['delivery_type'];
        $postingDistributionTO->sourceIdentifier = $distributionSourceIdentifier;  //id
        $postingDistributionTO->attachmentFile = str_replace("../", "", $bkupFolder.$filename); //same file for all recipients.
        $postingDistributionTO->ftpFilename = $ftpFilename;
        $postingDistributionTO->subject=""; //blank for ftp.
        $postingDistributionTO->body=""; //blank for ftp.


	    foreach($recipients as $re){
          $mfC = $this->miscDAO->getContactItem($notiArr["principal_uid"], "", $re);
          if (sizeof($mfC)==0) {
            BroadcastingUtils::sendAlertEmail("System Error","ITD File Confirmation Export Adaptor for nr.UID {$notiArr["uid"]} has an invalid Recipient/Contact: '{$re}'.","Y", true);
            continue;
          }

          if($notiArr['delivery_type'] == BT_FTP){

            $postingDistributionTO->destinationAddr = $mfC[0]["ftp_addr"];  //distribution will handle empty ftp strings etc.

      	    $dResult = $this->postDistributionDAO->postQueueDistribution($postingDistributionTO);
      			if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
      				BroadcastingUtils::sendAlertEmail("System Error","Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$notiArr["uid"]}.","Y", true);

      				$this->errorTO->type=FLAG_ERRORTO_ERROR;
              		$this->errorTO->description = "Distribution queue failed for for UID {$notiArr["uid"]} and Contact: '{$re}'.";
              		return $this->errorTO;
      			}

          }

	    }

	    //*** POINT OF SUCCESS ***
  		$this->errorTO->type = FLAG_ERRORTO_SUCCESS;
  		$this->errorTO->description = "Successful";
  		return $this->errorTO;


    } else {
    		$this->errorTO->type=FLAG_ERRORTO_ERROR;
    		$this->errorTO->description = "Passed parameter arrays are invalid/empty.";
    		return $this->errorTO;
      }

      return $this->errorTO;

    }


    //EXPORT FILE CONFIRMATION RIESES - CSV PIPE OUTPUT, EMAIL ONLY
    public function E_FC_RIESES($notiArr, $seArr){

      global $ROOT;

      if((count($notiArr)>0) && (count($seArr)>0)){


        //RESULTSET
        $uidList = array();
        foreach($seArr as $se){$uidList[] = $se['data_uid'];}  //build list
        $flRows = $this->exportDAO->getExportRiesesFileOrders($notiArr["principal_uid"], join(',',$uidList));
        if(count($flRows)==0){
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed to get resultset from File Log from loaded Smart Events in ".get_class($this). " --> ".__FUNCTION__.".";
          return $this->errorTO;
        }

        foreach($flRows as $row){

          $recipientsCheckCount = 0;
          if($row['status'] != FLAG_ERRORTO_SUCCESS){
            $recipientsCheckCount++;  //fake a recipient for single loops
            continue;
          }

          $lines = explode(',', $row['document_list']);

          //make sure the document list is real.
          if(empty($row['document_list'])){
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed to get Document List of File.";
            return $this->errorTO;
          }
          if(count($lines)==0){
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed to get Document List of File.";
            return $this->errorTO;
          }
          //recipients
          if($notiArr["user_uid_list"]==""){
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "The File Confirmation Export has no recipients loaded for Uid: " . $notiArr['uid'];
            return $this->errorTO;
          }


          //filename.
          $fileSeq = $this->sequenceDAO->getFTPFileExportSequence();
          $filename = 'CNF'.$fileSeq.'.edi'; //don't care about output type.

          //file data.
          $dataArr = array();
          foreach($lines as $docNo){
            $dataArr[] = date('Ymd|H:i:s|', strtotime($row['processed_date'])+7200) . trim($docNo); //ADD 2 HOURS TO MAKE GMT+2:00
          }
          $dataStr = join("\r\n",$dataArr);


          //CREATE FILE.
          $bkupFolder = CommonUtils::createBkupDirs($ROOT.FILE_ARCHIVE_EXPORTS_PATH.'rieses/');
          $fSize = file_put_contents($bkupFolder.$filename, $dataStr);
          if($fSize != strlen($dataStr)){
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Export Adaptor RIESES could not write export file: " . $bkupFolder.$filename;
            return $this->errorTO;
          }

	  $recipients = explode(",",$notiArr["user_uid_list"]); // if blank, sizeof() will still be 1
	  $this->errorTO->identifier = $distributionSourceIdentifier = CommonUtils::getRandomInteger(); //will be used later in export to set notify recip. table

          //Distribution
          $postingDistributionTO = new PostingDistributionTO;
          $postingDistributionTO->DMLType="INSERT";
          $postingDistributionTO->deliveryType = BT_EMAIL;  //hard value
          $postingDistributionTO->sourceIdentifier = $distributionSourceIdentifier;  //id
          $postingDistributionTO->attachmentFile = str_replace("../", "", $bkupFolder.$filename); //same file for all recipients.
          $postingDistributionTO->subject="Retail Trading: Confirmation (". basename($row['file_name']) .' - ' . $filename . ")"; //blank for ftp.
          $postingDistributionTO->body=""; //add rtt email file attached template here.


          //send to each loaded contact.
          foreach($recipients as $re){

            $mfC = $this->miscDAO->getContactItem($notiArr["principal_uid"], "", $re);
            if (sizeof($mfC)==0) {
              BroadcastingUtils::sendAlertEmail("System Error","Rieses File Confirmation Export Adaptor for nr.UID {$notiArr["uid"]} has an invalid Recipient/Contact: '{$re}'.","Y", true);
              continue;
            }

            $postingDistributionTO->destinationAddr = $mfC[0]["email_addr"];
            $dResult = $this->postDistributionDAO->postQueueDistribution($postingDistributionTO);
            if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
              BroadcastingUtils::sendAlertEmail("System Error","Could not queue for UID {$notiArr["uid"]}.","Y", true);
              $this->errorTO->type=FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Distribution queue failed for for UID {$notiArr["uid"]} and Contact: '{$re}'.";
              return $this->errorTO;
            } else {
              $recipientsCheckCount++;
            }
          }

        } //EOL: file loop

        if($recipientsCheckCount>0){
          //*** POINT OF SUCCESS ***
          $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
          $this->errorTO->description = "Success.";
          return $this->errorTO;
        } else {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "No Recipients were find for loaded confirmation.";
          return $this->errorTO;
        }
      } else {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Passed parameter arrays are invalid/empty.";
        return $this->errorTO;
      }

      return $this->errorTO;

    }


    //EXPORT FILE CONFIRMATION SMOLLAN - CSV SPACE OUTPUT, FTP ONLY
    public function E_FC_SMOLLAN($notiArr, $seArr){

      global $ROOT;

      if((count($notiArr)>0) && (count($seArr)>0)){


        //RESULTSET
        $uidList = array();
        foreach($seArr as $se){$uidList[] = $se['data_uid'];}  //build list
        $flRows = $this->exportDAO->getExportSmollanFileOrders($notiArr["principal_uid"], join(',',$uidList));
        if(count($flRows)==0){
          echo "<p>".join(',',$uidList)."</p>";
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed to get resultset from File Log from loaded Smart Events in ".get_class($this). " --> ".__FUNCTION__." for Principal {$notiArr["principal_uid"]}.";
          return $this->errorTO;
        }

        $fileArr = array();
        foreach($flRows as $order){
          $fileArr[basename($order['file_name'])][] = $order;
          $onlineFPUid = $order['online_file_processing_uid'];
        }

        $importMapArr = $this->importDAO->getOnlineImportMappings($onlineFPUid);

        /* ----------------------------
         * OUTPUT EXAMPLE AT 2012.07.12
         * ----------------------------
         * CHDR1VCK029.TXT  2012070310:16:45
         * CDTL1043674PicknPay Fam Pine Ridge   KF19                    00000020716UDV4906
         * CDTL1043675PicknPay Liberty Mall     KC19                    00000154130UDV4906
         * CDTL1043676Makro Pietermaritzburg      08                    00000841275UDV4906
         * CDTL1043677SUPERSPAR Camperdown     10670                    00000116476UDV4906
         * CDTL1043678SUPERSPAR Bluff          10048                    00000201559UDV4906
         * TRL0100012
         * ----------------------------
        */

        $generalRefArr = array();
        foreach($fileArr as $file){

          $onlineMapArr = array();
          foreach($importMapArr as $importMap){
            if($importMap['principal_uid'] == $file[0]["principal_uid"]){
              $onlineMapArr = $importMap;
              break;
            }
          }

          if(count($onlineMapArr)==0){
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed to get Online File Processing Mapping.";
            return $this->errorTO;
          }


          $depot = $this->depotDAO->getPrincipalDepotBySF($file[0]["principal_uid"], $onlineMapArr['pd_special_field_uid'], $file[0]['depot_lookup_ref']);
          if(count($depot)==0 || !isset($depot[0]['code'])){
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed to get Depot Uid from lookup code: " . $file[0]['depot_lookup_ref'];
            return $this->errorTO;
          }

          $depotCode = $depot[0]['code'];

          switch ($depotCode){
            case "UJ":
              $depotSmollanCode = '04';
              break;
            case "UC":
              $depotSmollanCode = '05';
              break;
            case "UD":
              $depotSmollanCode = '06';
              break;
            case "TE":
              $depotSmollanCode = '07';
              break;
            case "FP":
              $depotSmollanCode = '08';
              break;
            case "SR":
              $depotSmollanCode = '10';
              break;
            case "UB":
              $depotSmollanCode = '19';
              break;
            default:
              $depotSmollanCode = '  ';
              break;
          }

          if(!isset($depotSmollanCode) || trim($depotSmollanCode)==''){
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Depot Code to Smollan Code tranlation failure!";
            return $this->errorTO;
          }

          if(count($file)==0){
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed to get Orders of File.";
            return $this->errorTO;
          }

          //filename.
          $fileSeq = $this->sequenceDAO->getFTPFileExportSequence();
          $filename = 'C'. str_pad($file[0]['principal_code'], 2, "0", STR_PAD_LEFT) . $depotCode . substr($fileSeq,-3) . '.TXT'; //don't care about output type.

          //file data
          $fileDat = '';
          $fileDat .= "CHDR1" . str_pad(strtoupper(basename($file[0]['file_name'])), 12, " ", STR_PAD_RIGHT) .  date('YmdH:i:s',strtotime($file[0]['processed_date'])) . "\r\n";
          $lineCount = 1;

          foreach($file as $orders){

            $fileDat .= "CDTL1" .
                  substr($orders['client_document_number'], -6) .
                  str_pad($orders['deliver_name'], 50, " ", STR_PAD_RIGHT) .
                  str_pad(str_replace('.','',$orders['extended_price']), 11, "0", STR_PAD_LEFT) .
                  $depotCode .
                  $orders['document_type'] .
                  str_pad($orders['principal_code'], 2, "0", STR_PAD_LEFT) .
                  $depotSmollanCode .
                  "\r\n";

            $lineCount++;
          }
          $fileDat .= 'TRL01' . str_pad($lineCount, 5, "0", STR_PAD_LEFT) . "\r\n";


          //CREATE FILE.
          //backup file first.
          $bkupFolder = CommonUtils::createBkupDirs(DIR_DATA_SURESERVER_NON_FTP_FROM . 'smollan/in/');
          $fSize = file_put_contents($bkupFolder.$filename . '.' . CommonUtils::getGMTimeCompressed(0), $fileDat);
          if($fSize != strlen($fileDat)){
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Export Adaptor Smollan could not write backup export file: " . $bkupFolder.$filename;
            return $this->errorTO;
          } else {

            //provide file to smollan
            $fSizeA = file_put_contents(DIR_DATA_SURESERVER_NON_FTP_FROM . 'smollan/in/' . $filename, $fileDat);
            if($fSizeA != strlen($fileDat)){
              $this->errorTO->type=FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Export Adaptor Smollan could not write export file: " . $filename;
              return $this->errorTO;
            } else {
              $generalRefArr[][0] = $filename; //zerro = general 1
            }

          }
        } //end of loop.

        //*** POINT OF SUCCESS ***
        $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        $this->errorTO->description = "Success.";
        $this->errorTO->object = $generalRefArr;
        return $this->errorTO;

      } else {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Passed parameter arrays are invalid/empty.";
        return $this->errorTO;
      }

      return $this->errorTO;

    }

    //EXPORT FILE CONFIRMATION MrSweet - CSV OUTPUT
    // This format only supports 1 output file per principal due to Vendor Number at header leve, but then multiple files can be within same output file.
    // This should be ok, because notifications (Export) are loaded at Principal Level anyway
    public function E_FC_MRS($notiArr, $seArr){

      global $ROOT;

      if((count($notiArr)>0) && (count($seArr)>0)){


      //RESULTSET
      $uidList = array();
      foreach($seArr as $se){$uidList[] = $se['data_uid'];}  //build list

      $flRows = $this->exportDAO->getExportFileLogList($notiArr["principal_uid"], join(',',$uidList));  //sql does grouping and line count.
      if(count($flRows)==0){
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Failed to get resultset from File Log from loaded Smart Events in ".get_class($this). " --> ".__FUNCTION__.".";
        return $this->errorTO;
      }

      $combineFiles=CommonUtils::getParamValuesFromString($notiArr["additional_parameter_string"],"p2",$paramSeparator="&",$paramValueAsignment="=");

      if ($combineFiles!="N") {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Export Adaptor E_FC_MRS Only supports uncombined files for output.";
        return $this->errorTO;
      }

      $principalUId = $notiArr["principal_uid"];
      $principalMrSweet = "104";

      if (!in_array($principalUId,array($principalMrSweet))) {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Vendor Code for Filenaming not referenced in AdaptorUploadFileConfirmation";
        return $this->errorTO;
      }

      //BUILD FILE WITHIN OUTPUT TYPE.
      if($notiArr['output_type'] == OT_CSV){

        $fileExt = '.csv';

        $now=date(GUI_PHP_DATETIME_FORMAT);

        //build file contents.
        $fCArr=array();
        foreach($flRows as $fl){
          if ($fl["status"]!=FLAG_ERRORTO_SUCCESS) continue; // only send confirmation if successful

          if (trim($fl["document_number"]=="")) {
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Export Adaptor E_FC_MRS encountered empty document number.";
            return $this->errorTO;
          }

          $dN = str_pad($fl["document_number"],8,"0",STR_PAD_LEFT);
          if (substr($dN,0,2)!="00") {
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Document number ({$dN}) too long for adpator E_FC_MRS in AdaptorUploadFileConfirmation";
            return $this->errorTO;
          }

          if (substr($fl["client_document_number"],0,2)=="00") {
            $dN = substr($dN,2); // return 6 chars
          } else {
            $dN = $fl["client_document_number"]; // return the original with prefix that it came in on
          }

          $fCArr[] = array("document_number"=>trim($fl["document_number"]),"content"=>"{$dN},{$now}\n");
        }

        if (sizeof($fCArr)==0) {
          // exit with success - no confirmations to process
          $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
          $this->errorTO->description = "Successful - no file log (success status) found";
          return $this->errorTO;
        }

      } else {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Export Adaptor E_FC_MRS Only supports CSV output.";
        return $this->errorTO;
      }


      //name change for delivery type
      if($notiArr['delivery_type'] == BT_FTP_LOCALDIR){

        foreach ($fCArr as $f) {
          $lFilename = $this->bIDAO->getNotificationFilenameGeneric($notiArr['notification_uid'],"E",$notiArr["principal_uid"],true);
          $ftpFilename = 'CON' . $f["document_number"] . $fileExt;  // ITD has an exacting filename, uppercase

          //PATH AND LOCATION OF FILE
          $localPath = $ROOT . FILE_ARCHIVE_EXPORTS_PATH . "mrsweet/confirmations/";
          if(!is_dir($localPath)){
            mkdir($localPath, 0777, TRUE);  //create recursive directory based on path.
          }

          $archivesBkUpFolder = CommonUtils::createLocalBackup($localPath); // create bkup folders under local path

          // put it directly into bkup folder, dont wait for ftp as that is not setup for local
          $bytesWrit = @file_put_contents($archivesBkUpFolder . $lFilename, $f["content"]);
          if($bytesWrit != strlen($f["content"])){
            BroadcastingUtils::sendAlertEmail("Error in Export Adaptor E_FC_MRS", "The DEPOT Export file could not be created Export Adaptor E_FC_MRS @path:".$archivesBkUpFolder . $lFilename, "Y", $quietMode = false);
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            return $this->errorTO;
          }

          // copy to ftp folder
          $copy = copy($archivesBkUpFolder . $lFilename, DIR_DATA_SURESERVER_NON_FTP_FROM."mrsweet/tomrs/".$ftpFilename);
          if(!$copy){
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description="failed copying file from archives {$filename} to ftp dir in E_FC_MRS Adaptor";
            BroadcastingUtils::sendAlertEmail("Error in Export Adaptor E_FC_MRS", $this->errorTO->description, "Y", false);
            return $this->errorTO;
          }


        }


      } else {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Export Adaptor E_FC_MRS Only supports FTP LOCAL Delivery.";
        return $this->errorTO;
      }

      //*** POINT OF SUCCESS ***
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
      return $this->errorTO;


    } else {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Passed parameter arrays are invalid/empty.";
        return $this->errorTO;
      }

      return $this->errorTO;

    }


}
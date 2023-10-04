<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/ImportDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostImportDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostProductDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostDistributionDAO.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."libs/FileParser.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/BroadcastingUtils.php");
include_once($ROOT.$PHPFOLDER."TO/PostingDistributionTO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."TO/PostingFileLogTO.php");


#error_reporting(-1);
#ini_set('display_errors', 1);
set_time_limit(15 * 60); // 15 mins

$dbConn = new dbConnect();
$dbConn->dbConnection();

$runTime=CommonUtils::getGMTimeCompressed(0); // for filename
$jobType=str_replace(".php","",basename(__FILE__));

// temporarily fudge the session for validation purposes
if (!isset($_SESSION)) session_start();
$_SESSION['user_id'] = SESSION_ADMIN_USERID;

// get list of files
// check what files are waiting to be processed, and route accordingly
$importDAO = new ImportDAO($dbConn);
$mfLocs=$importDAO->getAllOnlineImportLocations();
$miscellaneousDAO = new MiscellaneousDAO($dbConn);
$postDistributionDAO = new PostDistributionDAO($dbConn);
$postImportDAO = new PostImportDAO($dbConn);
$postProductDAO = new PostProductDAO($dbConn);

function sendRemovalNotificationToEDIContact($recipients, $subject, $body, $stopOnError, $contactName, $fileLogItem) {
	global $postDistributionDAO; global $PHPFOLDER;
	$dTO = new PostingDistributionTO;
	$eTO = new ErrorTO;

	if ((($stopOnError=="Y") && ($fileLogItem->errorCount>50)) ||
		($fileLogItem->errorCount>150)) {
		BroadcastingUtils::sendAlertEmail("System Error","Client has not responded to removal request of file ({$fileLogItem->fileName}) holding up online processing, and stop_on_error is set to {$stopOnError} with error_count now {$fileLogItem->errorCount}.","Y");
	}

	$dTO->DMLType = "INSERT";
	$dTO->subject = $subject;
	$dTO->deliveryType = BT_EMAIL;
	foreach ($recipients as $r) {
		$dTO->destinationAddr=$r;
		$guid=md5($fileLogItem->uid.$fileLogItem->vendorUId.$fileLogItem->fileName.$fileLogItem->principalUId);
		$link=HOST_SURESERVER_AS_USER."{$PHPFOLDER}functional/import/fileActionFromUser.php?MASTID={$fileLogItem->uid}&KEYFROMLINK={$guid}";
		$dTO->body = "{$contactName},<br><br>You are setup in the Kwelanga Solutions System as the contact for data files that get sent from yourselves to Kwelanga Solutions. There has been a problem with one of the files and we need your action to continue!<br><br>".$body;
		if ($stopOnError=="Y") $dTO->body.="<br><br>Your location is configured to <b><u>** STOP **</u></b> all further processing until you action this request.";
		$dTO->body.="<br><br>Please click on the link below to remove this file from the server, or alternatively, please upload the replacement file soonest, to prevent processing holdups." .
				"	 <br><a href='{$link}'>{$link}</a>";

		$dTO->body.="<br><br>*** Please do NOT reply to this message as this email box is not monitored.<br>{$fileLogItem->principalUId},{$fileLogItem->vendorUId},{$fileLogItem->onlineFileProcessingUId}";
		$dResult=$postDistributionDAO->postQueueDistribution($dTO);
		if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
			$eTO->type = FLAG_ERRORTO_ERROR;
			$eTO->description = $dResult->description;
			BroadcastingUtils::sendAlertEmail("System Error","Sending Import File error to EDI Contact failed in onlineFileProcessing : ".$eTO->description,"Y");
			return $eTO;
		}
	}

	$eTO->type = FLAG_ERRORTO_SUCCESS;
	$eTO->description = "Successfully queued email in sendRemovalNotificationToEDIContact.";
	return $eTO;
}


function moveFile($fromPath,$file,$type) {
  global $runTime; global $ROOT; global $bkupFolderSuccess; global $bkupFolderError;

  if (!file_exists ($fromPath.$file)) { return array(false,"Could not move file (file does not exist): ".$file); }

  switch ($type) {
    case "S":
      if (rename($fromPath.$file, $bkupFolderSuccess.$file.".".$runTime)) {
        return array(true,"");
      } else {
        return array(false,"failed to move file");
      }
      break;
    case "E":
      if (rename($fromPath.$file, $bkupFolderError.$file.".".$runTime)) {
        return array(true,"");
      } else {
        return array(false,"failed to move file");
      }
      break;
    case "ZIP":
      if (rename($fromPath.$file, $bkupFolderError.$file.".".$runTime)) {
        return array(true,"");
      } else {
        return array(false,"failed to move file");
      }
      break;
    default:
      return array(false,"Could not move file (invalid type ".$type." passed): ".$file);
  }

}



/*
 * HOUSEKEEPING : move unnecessary files to temp folder for SGX to prevent oversized directory
 */
$path = DIR_DATA_SURESERVER_NON_FTP_FROM."sgx/fromsgx/";
//$handle = opendir($path);
$files=glob($path."LOADCONF*");
foreach ($files as $f) {
	//$result=rename($f,$path."temp/".basename($f));
	$result=unlink($f);
	if ($result===false) BroadcastingUtils::sendAlertEmail("System Error","Could not perform housekeeping on file {$f}.","Y");
}
// if (sizeof($files)>0) BroadcastingUtils::sendAlertEmail("System Housekeeping Notification",sizeof($files)." file(s) moved from {$path} to temp.","Y");

$path = DIR_DATA_FTP_FROM."unversalpaper/in/";

//$handle = opendir($path);
$files=glob($path."ELondon*");
foreach ($files as $f) {
	// Check if file is processed 

	$uppf = $importDAO->getUppFilesToDelete($f);

	if(count($uppf) > 0) {
	    echo "Deleting - <br>";
	    echo $f;
	    echo "<br>";
      
      $result=unlink($f);
      if ($result===false) BroadcastingUtils::sendAlertEmail("System Error","Could not perform housekeeping on file {$f}.","Y");
	}	
	//$result=rename($f,$path."temp/".basename($f));
	//$result=unlink($f);
	//if ($result===false) BroadcastingUtils::sendAlertEmail("System Error","Could not perform housekeeping on file {$f}.","Y");
}
/*
 * END HOUSEKEEPING
 */


$classObjects=array();
$vendorEDIContacts=array();
foreach ($mfLocs as $onlineImportLocation) {
$path=$onlineImportLocation["root_dir_constant"].$onlineImportLocation["file_path"];
//	$path="C:/inetpub/wwwroot/systems/kwelanga_system/ftp/".$onlineImportLocation["file_path"];

	echo "<br>Processing path: {$path}<br>";

	//establish backup processing folders. Errors folder is used only for when user themselves remove a file, then it moves the file there. But we still create it here
	$bkupFolderSuccess=CommonUtils::createBkupDirs($path,"1");
	if ($bkupFolderSuccess===false) {
		echo "Could not create bkup folders in {$path}1";
		BroadcastingUtils::sendAlertEmail("Could not create bkup folders during onlineFileProcessing!","Could not create bkup folders in {$path}1","Y");
		continue;
	}
	$bkupFolderError=CommonUtils::createBkupDirs($path,"2");
	if ($bkupFolderError===false) {
		echo "Could not create bkup folders in {$path}2";
		BroadcastingUtils::sendAlertEmail("Could not create bkup folders during onlineFileProcessing!","Could not create bkup folders in {$path}2","Y");
		continue;
	}

  // START : first unzip any zip files if present
  $filesAll=glob($path."*.ZIP");
  foreach($filesAll as $file) {
    if (CommonUtils::unzipFiles($path.$file,$path,"",true)===false) {
      $msg="Failed to extract ARJ archive in onlineFileProcessing {$path}{$file}. Import terminated.";
      echo $msg;
      BroadcastingUtils::sendAlertEmail("Could not extract archive in onlineFileProcessing!",$msg,"Y");
      continue;
    } else {
      // was successfully unzipped, so move file to archive
      moveFile($path,$file,"ZIP");
    }
  }
  // END : UnZip


	// get file list to process
	// $files=glob($path.$onlineImportLocation["file_wildcard"]); // cant use glob anymore as it is case sensitive and ITD are non-conformists
	$filesAll=glob($path."*");

	//$wildcardRegEx=str_replace(".","[.]",$onlineImportLocation["file_wildcard"]);
	//$wildcardRegEx=str_replace("*",".*?",$wildcardRegEx);
	$files=array();
	foreach($filesAll as $f) {
		//if (preg_match("/^{$wildcardRegEx}/i",basename($f))) {
		if (fnmatch($onlineImportLocation["file_wildcard"],basename($f),FNM_CASEFOLD)) {
			$files[]=$f; // has full path info
		}
	}

	sort($files);

	// get the contact(s) information for this vendor, only if not already retrieved to same db time
	if (!isset($vendorEDIContacts[$onlineImportLocation["vendor_uid"]])) {
		$tempArr=array();
		$mfC=$miscellaneousDAO->getVendorContactItem($onlineImportLocation["vendor_uid"], CTD_EDI);
		foreach ($mfC as $c) {
			if (preg_match(GUI_PHP_EMAIL_REGEX,$c["email_addr"])) {
				$tempArr[]=$c["email_addr"];
			}
		}
		if (sizeof($tempArr)==0) {
			BroadcastingUtils::sendAlertEmail("System Error","There are no (0, or invalid) EDI Contact(s) registered for vendor for online import UID {$onlineImportLocation["uid"]}. Cannot process this file as no messages can therefore be sent for file Management!","Y");
			continue;
		}
		$vendorEDIContacts[$onlineImportLocation["vendor_uid"]]=$tempArr;
	}

	// get the processing mappings and affix to object so adapter can use it
	$onlineImportLocation["onlineFileProcessingMapping"]=$importDAO->getOnlineImportMappings($onlineImportLocation["uid"]);

	foreach ($files as $f) {
		$dbConn->dbinsQuery("commit;"); // incase of something bombing after continue when a result update was needing to be committed;
		if (is_dir($f)) continue;
		$fn=basename($f);

		$onlineImportLocation["file_being_processed"] = $f; // add it to object so that adaptor can use it
		$mfFL=$importDAO->getFileLogItem($f, $filterToPastMonths = 3); // get history of this file, only those vendor_removed=N
		$fLTO = new PostingFileLogTO;
		$fLTO->DMLType="INSERT";

		// the file was received and successfully processed before or is still waiting on exception
		$fLUId="";
		if (sizeof($mfFL)>0) {
			$fLTO->uid = $fLUId = $mfFL[0]["uid"];
			$fLTO->fileName = $f;
  		$fLTO->status = $mfFL[0]["status"];
  		$fLTO->vendorUId = $mfFL[0]["vendor_uid"];
  		$fLTO->principalUId = $mfFL[0]["principal_uid"];
  		$fLTO->errorCount = $mfFL[0]["error_count"];
  		$fLTO->errorType = $mfFL[0]["error_type"];
  		$fLTO->seconds_elapsed = $mfFL[0]["seconds_elapsed"]; // this is NOT part of the TO, but just used here temporarily
  		$fLTO->onlineFileProcessingUId=$onlineImportLocation["uid"]; // dont use the uid from the fileLog as it might have changed now. Use the latest which is this loop.

			if ($fLTO->status==FLAG_ERRORTO_SUCCESS) {
        // if first row returned is "S" then that will be only row thus far due to ordering clause in sql
				$msg="Duplicated File {$fn} received. Previously processed on {$mfFL[0]["processed_date"]} (GMT).";
				$eTO=sendRemovalNotificationToEDIContact($vendorEDIContacts[$onlineImportLocation["vendor_uid"]], "Action Required from Kwelanga Solutions: Duplicate File", $msg, $onlineImportLocation["stop_on_error"], "EDI Contact",$fLTO);

        // duplicate file log entries get reused / overwritten every time as the "else" leg will get iterated on next run - it is necessary to store this as the fileConf notification needs it
        $fLTO->errorMsg=$msg;
        $fLTO->status = "N"; // rest of TO fields are the same
        $eTO=$postImportDAO->postFileLog($fLTO);
        if ($eTO->type!=FLAG_ERRORTO_SUCCESS) {
          BroadcastingUtils::sendAlertEmail("System Error","Unable to create file_log entry for file ({$f}).","Y");
          if ($onlineImportLocation["stop_on_error"]=="Y") continue 2; else {moveFile($path,$fn,FLAG_ERRORTO_ERROR); continue;}
        } else {
          // commit the file log so that if error later, it will update it and find it
          $dbConn->dbinsQuery("commit;");
        }
        $fLTO->uid = $fLUId = $eTO->identifier;
        $fLTO->errorCount = 0;
        $fLTO->errorType = "";
        $fLTO->seconds_elapsed = 0;

				// either skip all remaining files of this type, or continue with next
				if ($onlineImportLocation["stop_on_error"]=="Y") continue 2; else {moveFile($path,$fn,FLAG_ERRORTO_ERROR); continue;}

			} else {
			  // only other possibilities are "E"rror or "N" ~ duplicate ; "D"eleted rows are excluded from query
        if ($fLTO->status=="N") {

          $msg="Duplicated File {$fn} received. Previously processed on {$mfFL[0]["processed_date"]} (GMT).";
          $eTO=sendRemovalNotificationToEDIContact($vendorEDIContacts[$onlineImportLocation["vendor_uid"]], "Action Required from Kwelanga Solutions: Duplicate File (sent {$mfFL[0]["error_count"]}  times)", $msg, $onlineImportLocation["stop_on_error"], "EDI Contact",$fLTO);

          $eTO=$postImportDAO->setFileLogResult($fLTO->uid,$fLTO->status,$mfFL[0]["error_msg"],ET_CUSTOMER,$onlineImportLocation["uid"]); // system errors do not incr the error_count
          if ($eTO->type!=FLAG_ERRORTO_SUCCESS) BroadcastingUtils::sendAlertEmail("System Error","1Could not setFileLogResult in onlineFileProcessing of file {$f} : ".$eTO->description,"Y");
          if ($onlineImportLocation["stop_on_error"]=="Y") continue 2; else {moveFile($path,$fn,FLAG_ERRORTO_ERROR); continue;}
        } else {
          // processing is not stopped if filelog status is ERROR as this file could simply be the replacement to fix it
        }

			}
		} else {
			// create the file_log entry

			if(strstr($f,"mtd_im") || strstr($f,"avail") || strstr($f,"SCANN") ) {
				$fnStored =  $f . date("YmdHis");
			} else {
				$fnStored =  $f;
			}		
      echo $fnStored;
      echo "<br>";

  		$fLTO->fileName = $fnStored;
  		$fLTO->status = FLAG_STATUS_QUEUED;
  		$fLTO->vendorUId = $onlineImportLocation["vendor_uid"];
  		$fLTO->principalUId = $onlineImportLocation["principal_uid"];
  		$fLTO->onlineFileProcessingUId=$onlineImportLocation["uid"];
			$eTO=$postImportDAO->postFileLog($fLTO);
			if ($eTO->type!=FLAG_ERRORTO_SUCCESS) {
                          BroadcastingUtils::sendAlertEmail("System Error","Unable to create file_log entry for file ({$f}).","Y");
                          if ($onlineImportLocation["stop_on_error"]=="Y") continue 2; else {MoveFile($path,$fn,FLAG_ERRORTO_ERROR); continue;}
			} else {
                          // commit the file log so that if error later, it will update it and find it
                          $dbConn->dbinsQuery("commit;");
			}
			$fLTO->uid = $fLUId = $eTO->identifier;
			$fLTO->errorCount = 0;
			$fLTO->errorType = "";
			$fLTO->seconds_elapsed = 0;
		}

		$onlineImportLocation["fileLogUId"] = $fLUId; // add this to the object so the processor can use it
		
		echo "<br>";
		echo $f;
		echo "<br>";
		
		if(strpos($f,'xml') == TRUE || strpos($f,'XML') == TRUE) {
      $importDAO->uploadSmollanXMLfile($f);
		}
		
		$content = trim(file_get_contents($f)); // this automatically leaves off trailing blank lines
		if ($content===false) {
			$eTO=$postImportDAO->setFileLogResult($fLTO->uid,FLAG_STATUS_ERROR,"Could not read file during onlineFileProcessing",ET_SYSTEM,$onlineImportLocation["uid"]); // system errors do not incr the error_count
      if ($eTO->type!=FLAG_ERRORTO_SUCCESS) BroadcastingUtils::sendAlertEmail("System Error","2Could not setFileLogResult in onlineFileProcessing of file {$f} : ".$eTO->description,"Y");
			BroadcastingUtils::sendAlertEmail("Could not read file during onlineFileProcessing","Could not read file during onlineFileProcessing: \n\n{$f}","Y");
			if ($onlineImportLocation["stop_on_error"]=="Y") continue 2; else {moveFile($path,$fn,FLAG_ERRORTO_ERROR); continue;}
		}

		// check filesize zero
		if (strlen($content)==0) {
			// make it an error after duration / send notification
			if ((($fLTO->status==FLAG_STATUS_QUEUED) && ($fLTO->seconds_elapsed>(1*60))) ||
				($fLTO->status==FLAG_STATUS_ERROR)) {
				$msg = "Zero byte file received - " . $fn;
				$eTO=sendRemovalNotificationToEDIContact($vendorEDIContacts[$onlineImportLocation["vendor_uid"]], "Action Required from Kwelanga Solutions: Empty File Received", $msg, $onlineImportLocation["stop_on_error"], "EDI Contact",$fLTO);
				$eTO=$postImportDAO->setFileLogResult($fLTO->uid,FLAG_STATUS_ERROR,$msg,ET_CUSTOMER,$onlineImportLocation["uid"]);
				if ($eTO->type!=FLAG_ERRORTO_SUCCESS) BroadcastingUtils::sendAlertEmail("System Error","3Could not setFileLogResult in onlineFileProcessing of file {$f} : ".$eTO->description,"Y");
			}
			echo "Empty File {$f}, possibly still downloading from client.";
			if ($onlineImportLocation["stop_on_error"]=="Y") continue 2; else {moveFile($path,$fn,FLAG_ERRORTO_ERROR); continue;} // just skip until sufficient time elapsed
		}

		// check the trailer
		if ($onlineImportLocation["file_end_delimiter"]!="") {
			$failedEOFD=false;
			if ($onlineImportLocation["file_end_delimiter_is_regex"]=="Y") {
				$lastLineArr=explode("\n",substr($content,-50)); // trailing blank lines are removed in the file_get_contents func above
				if (!preg_match("/{$onlineImportLocation["file_end_delimiter"]}/i",$lastLineArr[sizeof($lastLineArr)-1])) {
					$failedEOFD=true; // if it is a total line, this only checks to see if it has a total line at end - it is up to the adaptor to validate the line count
				}
			} else {
				if (strtoupper(substr($content,strlen($onlineImportLocation["file_end_delimiter"])*-1))!=strtoupper($onlineImportLocation["file_end_delimiter"])) {
					$failedEOFD=true;
				}
			}
			if ($failedEOFD) {
				// some files are large 200KB+ so only send message if sufficient time elapsed (5 mins)
				$fileUnixTIme=filemtime($f); // MUST use modified time, as on windows the created time is different (only on create) to UNIX (whenever modified)
				if ((time()-$fileUnixTIme)>(60*10)) {
					$eTO=sendRemovalNotificationToEDIContact($vendorEDIContacts[$onlineImportLocation["vendor_uid"]], "Action Required from Kwelanga Solutions: Invalid File Structure", "File End Delimiter not found for file ".basename($f).". This file was received with a required end-delimiter of ".htmlspecialchars($onlineImportLocation["file_end_delimiter"]).".", $onlineImportLocation["stop_on_error"], "EDI Contact",$fLTO);
				}
				$eTO=$postImportDAO->setFileLogResult($fLTO->uid,FLAG_ERRORTO_ERROR,"File End Delimiter not found for file {$f}, end-delimiter of {$onlineImportLocation["file_end_delimiter"]}.",ET_CUSTOMER,$onlineImportLocation["uid"]);
				if ($eTO->type!=FLAG_ERRORTO_SUCCESS) BroadcastingUtils::sendAlertEmail("System Error","4Could not setFileLogResult in onlineFileProcessing of file {$f} : ".$eTO->description,"Y");
				if ($onlineImportLocation["stop_on_error"]=="Y") continue 2; else {moveFile($path,$fn,FLAG_ERRORTO_ERROR); continue;}

			}
		}

		// update file particulars - must not do this if error occurred above
		$lineCount=sizeof(explode("\n",$content));
		$eTO=$postImportDAO->setFileLogParticulars($fLTO->uid,$lineCount);
		if ($eTO->type!=FLAG_ERRORTO_SUCCESS) {
			BroadcastingUtils::sendAlertEmail("System Error","Could not setFileLogParticulars in onlineFileProcessing of file {$f} : ".$eTO->description,"Y");
			if ($onlineImportLocation["stop_on_error"]=="Y") continue 2; else {moveFile($path,$fn,FLAG_ERRORTO_ERROR); continue;}
		}

		// include the adaptor and processor classes
		$adaptorClassName="Adaptor{$onlineImportLocation["transaction_type"]}";
		$processorClassName="Processor{$onlineImportLocation["process_name"]}";
		include_once($ROOT.$PHPFOLDER."functional/import/adaptor/{$adaptorClassName}.php");
		include_once($ROOT.$PHPFOLDER."functional/import/processor/{$processorClassName}.php");
		// call the specific adaptor (which may vary). we dont instantiate class from new each time to improve overhead
		if (!isset($classObjects[$adaptorClassName])) $classObjects[$adaptorClassName] = new $adaptorClassName($dbConn);

		if(!method_exists($classObjects[$adaptorClassName], "adaptor".$onlineImportLocation["adaptor_name"])){
			echo "error missing/invalid method: (new {$adaptorClassName}())->adaptor{$onlineImportLocation["adaptor_name"]}()";
		}

		$aTO=call_user_func(array($classObjects[$adaptorClassName], "adaptor".$onlineImportLocation["adaptor_name"]),$content, $onlineImportLocation);
		#echo "<br>";
		#echo $adaptorClassName;
		#echo "<br>";
		#echo $onlineImportLocation["adaptor_name"];
		#echo "<br>";
		#echo $onlineImportLocation;
		#echo "<br>";
		#echo $content;	
		
		#print_r($aTO);
		
		
		if ($aTO->type!=FLAG_ERRORTO_SUCCESS) {
			if ($aTO->identifier==ET_SYSTEM){
                          BroadcastingUtils::sendAlertEmail("System Error","Adaptor processing failed in onlineFileProcessing : ".$aTO->description,"Y");
                        } else if($aTO->identifier2==1000 && $onlineImportLocation["stop_on_error"]!="Y"){
                          // if caller identifer2 is quiet mode and ONLY if the stop
                          // on error is disabled can we quietly fail
                          // as potentially we don't want a file to fail quitely and halt processing
                        } else {
                          $eTO=sendRemovalNotificationToEDIContact($vendorEDIContacts[$onlineImportLocation["vendor_uid"]], "Action Required from Kwelanga Solutions: Invalid File Structure", "<br>{$fn}<br>".$aTO->description, $onlineImportLocation["stop_on_error"], "EDI Contact",$fLTO);
                        }

			// update the FileLog using the type of error contained in identifier
			$eTO=$postImportDAO->setFileLogResult($fLTO->uid,FLAG_ERRORTO_ERROR,$aTO->description,$aTO->identifier,$onlineImportLocation["uid"]);
			if ($eTO->type!=FLAG_ERRORTO_SUCCESS) BroadcastingUtils::sendAlertEmail("System Error","Could not setFileLogResult in onlineFileProcessing of file {$f} : ".$eTO->description,"Y");
			if ($onlineImportLocation["stop_on_error"]=="Y") continue 2; else {moveFile($path,$fn,FLAG_ERRORTO_ERROR); continue;}

		} else {
			// call the processing script for the object
			
			if (!isset($classObjects[$processorClassName])) $classObjects[$processorClassName] = new $processorClassName($dbConn);
			$pTO=call_user_func(array($classObjects[$processorClassName], "post".$onlineImportLocation["process_name"]),$aTO->object, $onlineImportLocation);
			// be careful that rollback doesnt undo stuff you did before the processing that you want to keep !! I just presume here that there is none. If there is no continue above, then you need to commit yourself
			if ($pTO->type!=FLAG_ERRORTO_SUCCESS) {
				$dbConn->dbinsQuery("rollback;");
				if ($pTO->identifier==ET_SYSTEM) BroadcastingUtils::sendAlertEmail("System Error","Processor processing failed in onlineFileProcessing : ".$pTO->description,"Y");
				else $eTO=sendRemovalNotificationToEDIContact($vendorEDIContacts[$onlineImportLocation["vendor_uid"]], "Action Required from Kwelanga Solutions: Processing Error", "<br>{$fn}<br>".$pTO->description."<br><br>*** This error may also be a masterfiles error. Instead of removing the file, also consider updating the masterfiles correctly.", $onlineImportLocation["stop_on_error"], "EDI Contact",$fLTO);
				// update the FileLog using the type of error contained in identifier
				$eTO=$postImportDAO->setFileLogResult($fLTO->uid,FLAG_ERRORTO_ERROR,$pTO->description,$pTO->identifier,$onlineImportLocation["uid"]);
				if ($eTO->type!=FLAG_ERRORTO_SUCCESS) BroadcastingUtils::sendAlertEmail("System Error","6Could not setFileLogResult in onlineFileProcessing of file {$f} : ".$eTO->description,"Y");
				if ($onlineImportLocation["stop_on_error"]=="Y") continue 2; else {moveFile($path,$fn,FLAG_ERRORTO_ERROR); continue;}
			} else {
				$dbConn->dbinsQuery("commit;");
        // store the document number for file log update - used for confirmations
        if ((isset($aTO->object[0]->documentNo)) /*&& ($aTO->object[0] instanceof PostingOrdersHoldingTO)*/ && (sizeof($aTO->object)==1)) {
          $fLTO->documentNumber = $aTO->object[0]->documentNo;
          $fLTO->clientDocumentNumber = $aTO->object[0]->clientDocumentNo;
        }

				$eTO=$postImportDAO->setFileLogResult($fLTO->uid,FLAG_ERRORTO_SUCCESS,"","",$onlineImportLocation["uid"],$fLTO->documentNumber,$fLTO->clientDocumentNumber);
				if ($eTO->type!=FLAG_ERRORTO_SUCCESS) BroadcastingUtils::sendAlertEmail("System Error","7Could not setFileLogResult in onlineFileProcessing of file {$f} : ".$eTO->description,"Y");

				// move file. We only move it out if successful. All other situations the client must take explicit action
				$mResult=moveFile($path,$fn,FLAG_ERRORTO_SUCCESS);
				if ($mResult[0]===false) BroadcastingUtils::sendAlertEmail("System Error",$mResult[1],"Y");
			}
		}


	} // end files loop

	$dbConn->dbinsQuery("commit;");
}

$dbConn->dbinsQuery("commit;");

echo "[***EOS***]";

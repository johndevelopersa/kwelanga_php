<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/SchedulerDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ReportDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostSchedulerDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingSchedulerJobTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/xmlClass.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/FileParser.php');

set_time_limit(60*45); // the 3am slot that runs 5 reports per principal (ML,RP,RI,RU,RO) may need this much time in future
error_reporting(-1);
ini_set('display_errors', 1);

$statST = microtime(true);
$statJOBS = 0;

// calling program may already have set this in JobExecution
if (!isset($dbConn)){
  $dbConn = new dbConnect();
  $dbConn->dbConnection();
}
$postingSchedulerJobTO = new PostingSchedulerJobTO;
$postDistributionDAO = new PostDistributionDAO($dbConn);
$adminDAO = new AdministrationDAO($dbConn);
$reportDAO = new ReportDAO($dbConn);
$postSchedulerDAO = new PostSchedulerDAO($dbConn);
$schedulerDAO = new SchedulerDAO($dbConn);
$principalDAO = new PrincipalDAO($dbConn);
$mfS = $schedulerDAO->getActiveSchedules();


echo "START: ".CommonUtils::getGMTime(0)."<BR>";


if (sizeof($mfS) > 0) {


  $bkupFolder=CommonUtils::createBkupDirs($ROOT.FILE_ARCHIVE_REPORTS_PATH);
  if ($bkupFolder===false) {
    BroadcastingUtils::sendAlertEmail("System Error","Could not create bkup dirs in location : ".$ROOT.FILE_ARCHIVE_REPORTS_PATH,"Y");
    return;
  }


  $nowCompressed = CommonUtils::getGMTimeCompressed(0);
  $nowJustDate = gmdate(GUI_PHP_DATE_FORMAT);
  $now = CommonUtils::getGMTime();
  $hrNow = gmdate("G"); // no leading zeros
  $mthNow = gmdate("m");
  $yrNow = gmdate("Y");
  $dayNow = gmdate("j"); // no leading zeros
  $weekDayNow = gmdate("w");


  foreach ($mfS as $s) {

    if ($s["run_day"]!="") $day = explode(",",$s["run_day"]); else $day=array(); sort($day);
    if ($s["run_week"]!="") $week = explode(",",$s["run_week"]); else $week=array(); rsort($week);
    $time = explode(",",$s["run_time"]); sort($time); // always assumed to be in 24hr format

          $dueNow = false;
          $lastMaxTime = 0;

          if ($s["regenerate"]=="Y") $dueNow = true;

          // include those schedules that MISSED their last interval, or interval is now due
          if ($dueNow===false) {
                  // get the last schedule run date
                  foreach($time as $t) {

                    //move time backwards to GMT timezone
                    //IE: 6 AM (GMT+2) = 4 AM (GMT+0)
                    $t -= 2;

                          if (sizeof($day)>0) {
                                  foreach($day as $d) {
                                          // assume all days are in past to get the last time it should have run.
                                          // same month - cant do it same as for weeks because of differing days per month and leap year
                                          if ($d<=$dayNow) {
                                                  if (checkdate(intval($mthNow),intval($d),intval($yrNow))) $calcTime=strtotime($yrNow."-".$mthNow."-".$d." ".$t.":00:00"); // check month has that many days
                                                  else $calcTime=0;
                                          }
                                          // previous month
                                          else {
                                                  if (intval($mthNow)==1) {
                                                          if (checkdate(intval("12"),intval($d),intval($yrNow))) $calcTime=strtotime((intval($yrNow)-1)."-12-".$d." ".$t.":00:00"); // rollover
                                                          else $calcTime=0;
                                                  } else {
                                                          if (checkdate((intval($mthNow)-1),$d,$yrNow)) $calcTime=strtotime($yrNow."-".(intval($mthNow)-1)."-".$d." ".$t.":00:00");
                                                          else $calcTime=0;
                                                  }
                                          }
                                          if (($calcTime>$lastMaxTime) && ($calcTime<=strtotime($now))) $lastMaxTime = $calcTime; // remember it only if in past
                                  }
                          } else if (sizeof($week)>0) {
                                  foreach($week as $w) {
                                          // assume all weeks are in past to get the last time it should have run.
                                          if (intval($w)<=intval($weekDayNow)) $calcTime=strtotime($nowJustDate." ".$t.":00:00")-(($weekDayNow-$w)*60*60*24);
                                          else $calcTime=strtotime($nowJustDate." ".$t.":00:00")-((7-($w-$weekDayNow))*60*60*24);
                                          if (($calcTime>$lastMaxTime) && ($calcTime<=strtotime($now))) $lastMaxTime = $calcTime; // remember it only if in past
                                  }
                          }

                  }
                  if ($s["last_run_date"]=="") {
                          if (strtotime($s["created_date"])<$lastMaxTime) $dueNow=true;
                  } else {
                          if (strtotime($s["last_run_date"])<$lastMaxTime) $dueNow=true;
                  }
          }

          if ($dueNow) {

                $postSchedulerDAO->setScheduleResult($s["uid"],$now);
                $postingSchedulerJobTO->DMLType = "INSERT";
                $postingSchedulerJobTO->schedulerUId = $s["uid"];
                $postingSchedulerJobTO->runDate = $now;
                $postingSchedulerJobTO->queuedDate = $now;
                $postingSchedulerJobTO->runResult = FLAG_STATUS_QUEUED;
                $postingSchedulerJobTO->runMsg = "Queued for Processing";
                $postingSchedulerJobTO->attachmentFile = "";
                $resultTO=$postSchedulerDAO->postScheduleJob($postingSchedulerJobTO); // create the job entry for this run
                $postingSchedulerJobTO->UId = $resultTO->identifier;

                // only process standard user reports for now, and System Reports
                if (($s["job_type"]==SCD_JT_REPORT) || ($s["job_type"]==SCD_JT_SYSTEM_REPORT)) {


                    //output type
                    $outputType = $s['output_type'];

                    //USER SETTINGS
                    $userReportOutputSetting = false;
                    $mfUP = $adminDAO->getUserPreferences($s["user_uid"]);
                    if (!sizeof($mfUP)==0) {
                      $userReportOutputSetting = $mfUP[0]["user_report_output_setting"];
                    }

                        // convert params to array
                        $paramArr=array();
                        $emailList="";

                        if($s["parameter_list"] != ''){
                                $plArr=explode("&",$s["parameter_list"]);
                                foreach($plArr as $param) {
                                        list($name,$value)=explode("=",$param);
                                        $paramArr[$name] = $value;
                                        $emailList.="\n".$name."=" . GUICommonUtils::translateDateRangeValue($value);
                                }
                        }

                    // set up session var(s) which may be used inside the sql
                    // dont do this anymore as it sometimes causes sql's to hand on the "sending data" phase $dbConn->dbQuery("SET @SCHEDULERUID:={$s["uid"]}");
                    $GLOBALS["SCHEDULERUID"]=$s["uid"];
                    $GLOBALS["SCRIPTORIGIN"]="SCHEDULER"; // might not be needed, but couldnt fully understand how downloadBase/parameterBase was called as it had scheduler checks inside
                    $GLOBALS["SE_RUNONCE_TYPE"]="S".$s["uid"]; // might not be needed, but couldnt fully understand how downloadBase/parameterBase was called as it had scheduler checks inside
                    $GLOBALS["SE_RUNONCE_TYPE_UID"]=$s["object_id"]; // might not be needed, but couldnt fully understand how downloadBase/parameterBase was called as it had scheduler checks inside

                    $resultTO = $reportDAO->reportSQL_getReportSQL($s["object_id"],$s["user_uid"],$s["principal_uid"],$s["principal_code"],$paramArr);

                    if ($resultTO->type!=FLAG_ERRORTO_SUCCESS) {

                                BroadcastingUtils::sendAlertEmail("System Error","<BR>".$resultTO->description,"Y");
                                $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, $resultTO->description);

                        } else {

                            $reportSQLTO = $resultTO->object;

                            $sql = $resultTO->object->sql;
                            $fileName = $resultTO->object->fileName;
                            $xmlSchema = $resultTO->object->xmlSchema;

                            $Cformater = (!empty($resultTO->object->columnFormat)) ? $resultTO->object->columnFormat : false;

                            // returns only the result set.

                            $runOnceParams=array("runOnceFieldName"=>$resultTO->object->runOnceFieldName, "type"=>$GLOBALS["SE_RUNONCE_TYPE"], "typeUId"=>$GLOBALS["SE_RUNONCE_TYPE_UID"]);

                            // this not may also do an update (run once code) which we will need to rollback if error so commit now !
                            // There must NOT be any other rollback in this script besides the one directly after this call due to its rollback
                            $dbConn->dbinsQuery("commit");

                            $resultTO = $reportDAO->reportSQL_runReportSQL($sql,$resultTO->object->database,$runOnceParams,$hiddenColList=$resultTO->object->hiddenColList);

                            if ($resultTO->type!=FLAG_ERRORTO_SUCCESS) {

                                    $dbConn->dbinsQuery("rollback"); // rollback only any run-once updates to smart event. There is no other rollback in this script, so ok to rollback here and commit earlier
                                    BroadcastingUtils::sendAlertEmail("System Error","<BR>".$resultTO->description,"Y");
                                    $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, $resultTO->description);

                            } else {

                                // Add Control Break Total Lines if necessary
                                $rTO = $reportDAO->reportSQL_addBreakTotals ($reportSQLTO, $resultTO->object->data);
                                if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
                                  $dbConn->dbinsQuery("rollback");
                                  BroadcastingUtils::sendAlertEmail("System Error","<BR>".$rTO->description,"Y");
                                  $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, $rTO->description);
                                }
                                unset($resultTO->object->data);
                                $resultTO->object->data = $rTO->object;


                                //store output as file

                                    //replace spaces for URL friendly link - for future upgrades.
                                    //make name unique as same information can occur within same exec.
                                    //substr last 4 char as based on microtime - seconds not safe enough.
                                    $fileName = str_replace(" ", "_", $fileName) . '.' . $nowCompressed . "." . $s["user_uid"] . "." . $s["principal_uid"] . "." . substr(uniqid(),9,4);

                                    if($outputType == SCD_OT_CSV){

                                      //CSV = 1 FILE
                                          $fh = @fopen($bkupFolder . $fileName . ".csv", 'w');
                                          $fp = true;  //preset

                                    } else if ($outputType == SCD_OT_XML) {

                                      //XML = 1 FILE
                                          $fh = @fopen($bkupFolder . $fileName . ".xml", 'w');
                                          $fp = true;  //preset

                                    } else {

                                      //HTML = 2 FILES: 1 HTML, 2 PLAIN TXT
                                          $fh = @fopen($bkupFolder . $fileName . ".html", 'w');
                                          $fp = @fopen($bkupFolder . $fileName . ".txt", 'w');

                                    }

                                        if ($fh===false || $fp === false) {
                                        	
                                        	echo $bkupFolder;
                                        	echo $fileName;

                                                BroadcastingUtils::sendAlertEmail("System Error","Failed to create archive file from scheduler!","Y");
                                                $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, "Failed to create archive file from scheduler!");

                                        } else {

                                          $mfU=$adminDAO->getUserItem($s["user_uid"]);
                                          if (isset($mfU[0]["full_name"])) $userName=$mfU[0]["full_name"]; else $userName="SYSTEM";

                                          # use the user who loaded the scheduled item to link to the system
                                          # and grab the settings from the class via tokenizer...
                                          $systemId = (isset($mfU[0]['system_uid']))?$mfU[0]['system_uid']:SYS_RETAIL;
                                          $sysArr = $adminDAO->getSystemByUid($systemId);
                                          $fparse = new FileParser();
                                          $conArr = $fparse->classConstTokenizer($ROOT . $PHPFOLDER . 'properties/conventions/'.$systemId .'_'. strtolower($sysArr[0]['name']) . '.php');
                                          if(count($conArr)==0){
                                            BroadcastingUtils::sendAlertEmail("System Error","<BR>Error in loadding const from system SNC file!","Y");
                                            return;
                                          }

                                          $systemTitle = $conArr['title'];
                                          $systemFromEmail = $conArr['admin_email_addr'];
                                          $systemLogo = $conArr['logo_path'];

                                          if($s['principal_uid'] != ''){
                                                $pdA = $principalDAO->getPrincipalItem($s['principal_uid']);
                                                $principalName = $pdA[0]['principal_name'];
                                          } else {
                                                $principalName = 'SYSTEM REPORT';
                                          }
                                          $placement = ($outputType == SCD_OT_CSV || $outputType == SCD_OT_XML) ? 'attached' : 'below';

                                          $logoHTML = '<div align="right"><img src="'.HOST_SURESERVER_AS_USER.$PHPFOLDER.$systemLogo.'" alt="" ></div><hr><br><br>';
                                          $bodyHeader = $logoHTML . addSlashes("Dear User,<br><br>\n\nPlease find {$placement} the <B>{$s["report_name"]}</B> that was scheduled by {$userName} for you to receive.<br>\n\nRegards,<br>\n{$systemTitle}<br><br>\n\n");
                                          $bodyFooter = addSlashes("Parameters specified:<br>\n====================<br>\n<small>{$emailList}</small><br><br>\n\nPlease do not reply to this message. This is an automated emailbox and contents are not checked for communications.");

                                            //FORMAT RESULT SET DETERMINED BY OUTPUT TYPE
                                            if($outputType == SCD_OT_CSV){

                                              if ($resultTO->object->data=="" || !count($resultTO->object->data)>0) {
                                                $writeResult=fwrite($fh, "No Rows Found.");
                                              } else {

                                                    //WRITE CSV
                                                    $reportOutput = $reportDAO->reportSQL_arrayToCSV($resultTO->object->data, TRUE, $userReportOutputSetting);
                                                    $writeResult=fwrite($fh, $reportOutput);
                                                    fclose($fh);
                                              }

                                            } else if ($outputType == SCD_OT_XML) {

                                              if ($resultTO->object->data=="" || !count($resultTO->object->data)>0) {
                                                $writeResult=fwrite($fh, "No Rows Found.");
                                              } else {

                                              //WRITE XML
                                              $xmlResult = new arrayToXMLschema($xmlSchema, $resultTO->object->data);

                                                  if ($xmlResult->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                                                    $writeResult=fwrite($fh, "No Rows Found." . $xmlResult->errorTO->description);
                                                  } else {
                                                    $writeResult=fwrite($fh, $xmlResult->resultXML);
                                                                        fclose($fh);
                                                  }
                                               }

                                            } else {

                                              if ($resultTO->object->data=="" || !count($resultTO->object->data)>0) {

                                                $writeResult=fwrite($fh, str_replace("\n",'<br>',$bodyHeader) . "No Rows Found.<br><br>" . str_replace("\n",'<br>',$bodyFooter));
                                                fclose($fh);

                                                $writeResult=fwrite($fp, $bodyHeader . "No Rows Found." . "\n\n" . $bodyFooter);
                                                fclose($fp);
                                              } else {

                                                //WRITE HTML
                                                $reportOutput = $reportDAO->reportSQL_arrayToHTML($resultTO->object->data, true, $Cformater);
                                                $writeResult = fwrite($fh, str_replace("\n",'<br>',$bodyHeader) . $reportOutput . "<br><br>" . str_replace("\n",'<br>',$bodyFooter));
                                                fclose($fh);

                                                //WRITE PLAIN TEXT
                                                $reportOutput = $reportDAO->reportSQL_arrayToPLAINTEXT($resultTO->object->data);
                                                $writeResult = fwrite($fp, $bodyHeader . $reportOutput . "\n\n" . $bodyFooter);
                                                fclose($fp);
                                              }

                                            }


                                                if ($writeResult===false) {

                                                        BroadcastingUtils::sendAlertEmail("System Error","Failed to write to archive file from scheduler!","Y");
                                                        $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, "Failed to write to archive file from scheduler!");

                                                } else {

                                                        $postingSchedulerJobTO->DMLType = "UPDATE";
                                                        $postingSchedulerJobTO->runResult = FLAG_ERRORTO_SUCCESS;
                                                        $postingSchedulerJobTO->runMsg = "Successfully Created Report Attachment";
                                              $postingSchedulerJobTO->runDate = $now = CommonUtils::getGMTime();

                                                        if($outputType == SCD_OT_CSV){
                                                          $attachFile = $bkupFolder.$fileName.'.csv';
                                                        } else if($outputType == SCD_OT_XML){
                                                          $attachFile = $bkupFolder.$fileName.'.xml';
                                                        } else {
                                                          $attachFile = $bkupFolder.$fileName.'.html';
                                                        }
                                                        $postingSchedulerJobTO->attachmentFile = $attachFile;

                                                        $postingSchedulerJobTO->distributionStatus = FLAG_STATUS_QUEUED;
                                                        $resultTO=$postSchedulerDAO->postScheduleJob($postingSchedulerJobTO);  // update the job created at start
                                                // queue delivery for each recipient
                                                $distributionSourceIdentifier=CommonUtils::getRandomInteger(); // linked to created distributions for retrieval on query screen

                                                        //IF EMAIL DESTIN.
                                                        if($s["destination_type"]==SCD_DT_EMAIL){

                                                        // process each output type for each recipient. for time being only cater for emails from scheduler
                                                        $recipientList=array();
                                                        if ($s["alt_recipient_list"]!="")  $recipientList=explode(",",$s["alt_recipient_list"]);
                                                        if ($s["send_to_self"]=="Y") {
                                                                $mfU = $adminDAO->getUserItem($s["user_uid"]);
                                                                // we validate it here as well because it is more helpful to get a specific message for send-to-self
                                                                if (isset($mfU[0]["user_email"])) {
                                                                        if (preg_match(GUI_PHP_EMAIL_REGEX,$mfU[0]["user_email"])) $recipientList[]=$mfU[0]["user_email"];
                                                                        else {
                                                                                $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, "Incorrect email address format for send-to-self.");
                                                                                continue;
                                                                        }
                                                                }
                                                        }

                                                        if (sizeof($recipientList)==0) {
                                                                $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, "No recipients found for distribution type email");
                                                                continue;
                                                        }

                                                        // validate each email
                                                        foreach ($recipientList as $r) {
                                                                if (!preg_match(GUI_PHP_EMAIL_REGEX,$r)) {
                                                                        $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, "Incorrect email address format for alternate recipient :".$r);
                                                                        continue 2; // dont send to any of users if any single one has a problem
                                                                }
                                                        }

                                                        foreach ($recipientList as $r) {


                                                                if ($s["destination_type"]==SCD_DT_EMAIL) {


                                                                        // queue for distribution
                                                                        $postingDistributionTO = new PostingDistributionTO;
                                                                        $postingDistributionTO->DMLType="INSERT";
                                                                        $postingDistributionTO->deliveryType=BT_EMAIL;
                                                                        $postingDistributionTO->sourceIdentifier=$distributionSourceIdentifier;
                                                                        //$postingDistributionTO->destinationUserUId=$s["user_uid"]; // distributor uses destinationAddr instead if filled in
                                                                        $postingDistributionTO->destinationAddr = $r;
                                                                        if($systemId != SYS_KWELANGA){
                                                                          $postingDistributionTO->fromAddr = $systemFromEmail;
                                                                          $postingDistributionTO->fromAlias = $systemTitle;
                                                                        }
                                                                        $postingDistributionTO->subject = addSlashes( $s["report_name"] . " for {$principalName} is attached ({$now} GMT) - Scheduled ");
                                                                        $postingDistributionTO->attachmentFile=$postingSchedulerJobTO->attachmentFile;
                                                                        $postingDistributionTO->body = ($outputType == SCD_OT_CSV || $outputType == SCD_OT_XML) ? ($bodyHeader . $bodyFooter) : ('');

                                                                        $dResult=$postDistributionDAO->postQueueDistribution($postingDistributionTO);

                                                                        if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
                                                                                BroadcastingUtils::sendAlertEmail("System Error","Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$postingSchedulerJobTO->UId}.","Y");
                                                                                $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, addSlashes($dResult->description));
                                                                        }
                                                                        // reset the distribution uid back - it is important to update it regardless of successfully queueing distribution as you don't want it to point to old entry
                                                                        $sjdResult=$postSchedulerDAO->setScheduleJobDistribution($postingSchedulerJobTO->UId,$postingDistributionTO->sourceIdentifier);
                                                                        if ($sjdResult->type!=FLAG_ERRORTO_SUCCESS) {
                                                                                BroadcastingUtils::sendAlertEmail("System Error","Could not set the distribution id passback.","Y");
                                                                        }
                                                                }

                                                        }
                                                         echo "<BR>Successfully completed: ".preg_replace("/error/i","@error",$fileName) . ' (EMAIL)';
                                                        }  //END OF EMAIL DESTIN.


                                                        //IF DESTIN IS FTP
                                                        if($s["destination_type"]==SCD_DT_FTP){

                              $postingDistributionTO = new PostingDistributionTO;
                              $postingDistributionTO->DMLType = 'INSERT';
                              $postingDistributionTO->deliveryType = BT_FTP;
                              $postingDistributionTO->sourceIdentifier = $distributionSourceIdentifier;
                              $postingDistributionTO->destinationAddr = $s["destination_address"];  //USE THE SCHEDULE SERIAL STRING
                              $postingDistributionTO->subject = '';  //EMPTY FOR FTP :)
                              $postingDistributionTO->body = '';  //EMPTY FOR FTP :)
                              $postingDistributionTO->attachmentFile = $postingSchedulerJobTO->attachmentFile;

                              $dResult=$postDistributionDAO->postQueueDistribution($postingDistributionTO);

                              if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
                                BroadcastingUtils::sendAlertEmail("System Error","Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$postingSchedulerJobTO->UId}.","Y");
                                $postSchedulerDAO->setScheduleJobResult($postingSchedulerJobTO->UId, FLAG_ERRORTO_ERROR, addSlashes($dResult->description));
                              }
                              // reset the distribution uid back - it is important to update it regardless of successfully queueing distribution as you don't want it to point to old entry
                              $sjdResult=$postSchedulerDAO->setScheduleJobDistribution($postingSchedulerJobTO->UId,$postingDistributionTO->sourceIdentifier);
                              if ($sjdResult->type!=FLAG_ERRORTO_SUCCESS) {
                                BroadcastingUtils::sendAlertEmail("System Error","Could not set the distribution id passback.","Y");
                              }
                              echo "<BR>Successfully completed: ".preg_replace("/error/i","@error",$fileName) . ' (FTP)';
                                                        }  //END OF DESTIN FTP.

                                                }
                                        }
                                }
                        }
                }
                $dbConn->dbinsQuery("commit");
                $statJOBS++;

        } // end due now

  } //END OF LOOP

}

$statET = microtime(true);
$statTT = round($statET - $statST,4);
echo "[@>>>JOBS:".$statJOBS.";TT:".$statTT."@]<BR>";  //stat line.
echo "END: ".CommonUtils::getGMTime(0)."<BR>[***EOS***]";
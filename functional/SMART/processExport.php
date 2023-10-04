<?php

/**********************************************************************************************
 **********************************************************************************************
 * *
 * * PROCESS EXPORTS
 * *
 * * executes exports loaded and passes to relative export adaptor.
 * *
 * * WILL HANDLE POSTING TO NOTIFICATION.
 * *
 **********************************************************************************************
 **********************************************************************************************/

// time limit is set by calling JobExecution

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/ServerConstants.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/BIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostBIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostTransactionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ReportDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'functional/export/adaptor/AdaptorUploadFileConfirmation.php');


$statST = microtime(true);
$statJOBS = 0;
echo "START: ".CommonUtils::getGMTime(0)."<BR>";


// calling program may already have set this in JobExecution
if (!isset($dbConn)) {
  $dbConn = new dbConnect();
  $dbConn->dbConnection();
}


$postDistributionDAO = new PostDistributionDAO($dbConn);
$administrationDAO = new AdministrationDAO($dbConn);
$miscellaneousDAO = new MiscellaneousDAO($dbConn);
$postTransactionDAO = new PostTransactionDAO($dbConn);
$reportDAO = new ReportDAO($dbConn);
$postBIDAO = new PostBIDAO($dbConn);
$bIDAO = new BIDAO($dbConn);
$fileConfirm = new UploadFileConfirmation($dbConn);  //file confirm exportor.


/*******************************************************************************************************************************************
 * PROCESS NOTIFICATION : EDI File Upload Confirmation (Orders)
 *
 * Be careful when making changes. It is written like this to maximise all contacts getting the attachment, as rows can only be sent once.
 *******************************************************************************************************************************************/


echo "<hr>EXPORT NOTIFICATION : EDI File Upload Confirmation";

// Create new log entries into SMART_EVENT
$biTO = $postBIDAO->queueAllExportFileLog();
if ($biTO->type!=FLAG_ERRORTO_SUCCESS) {
	BroadcastingUtils::sendAlertEmail("System Error","Export failed to call postBIDAO->queueAllExportFileLog in processExport.php","Y");
} else {
	$dbConn->dbinsQuery("commit;");
}

// get all notifications of this type across all principals
$mfNR = $bIDAO->getEDIFileDefNotificationExport("");

if (sizeof($mfNR)==0) {
	echo "<br>No active 'EDI File Upload Confirmation (Orders)' exports found";
} else {

  foreach($mfNR as $n) :

    //any smart events loaded?
    $seRows = $bIDAO->getQueuedSmartEvents("E", $n['uid']);
    if(sizeof($seRows)>0){

      $paramExportAdaptor = CommonUtils::getParamValuesFromString($n["additional_parameter_string"],"p1");  //which adaptor am i using?

      //update runtime.
      $rtResult = $postBIDAO->postNotificationRecipientStart($n["uid"]);  // set the run_date for this processing run
      if ($rtResult->type==FLAG_ERRORTO_SUCCESS) {
        $dbConn->dbinsQuery("commit;");  //commit only start time.
      } else {
        BroadcastingUtils::sendAlertEmail("System Error","Export failed to update runtime for Notification Uid:{$n['uid']}.","Y");
        $dbConn->dbinsQuery("rollback;");
      }


      if(method_exists($fileConfirm,$paramExportAdaptor)==true){  //check if method exists.

        //Adaptor handles distribution posting - success for posting to notification.
        $cResult = $fileConfirm->{$paramExportAdaptor}($n, $seRows);  //exec.

        if($cResult->type==FLAG_ERRORTO_SUCCESS){

    	    // update the distribution uid to link any distributions created later for screen lookups
    	    $bIResult = $postBIDAO->postNotificationRecipientDistribution($n['uid'],$cResult->identifier); // identifier set at start. important to update it regardless of successfully queueing distribution as you don't want it to point to old entry

            if ($bIResult->type!=FLAG_ERRORTO_SUCCESS) {
          	  $dbConn->dbinsQuery("rollback;");  //roll back everything.

    		  $bIResult = $postBIDAO->postNotificationRecipientResult($n['uid'],FLAG_ERRORTO_ERROR,$bIResult->description); // this also automatically updates error count and concats the status
    		  $dbConn->dbinsQuery("commit;");  //commit result.

    	    } else {
    	    	$dbConn->dbinsQuery("commit;");
    	      /*
    	       * UPDATE SMART_EVENT here only for success, leave errors as Queued
               * update each row smart event at a time for general ref 1 and 2
    	       */
    	      //$seList=array();
              $n = 0;
    	      foreach ($seRows as $se) {

                //$seList[]=$se["uid"];

                $generalRef1 = '';  //present for other adaptors
                $generalRef2 = '';  //introduce at later stage.
                if(is_array($cResult->object) && count($seRows)==count($cResult->object)){
                  if(isset($cResult->object[$n][0])){
                    $generalRef1 = $cResult->object[$n][0];
                  }
                  if(isset($cResult->object[$n][1])){
                    $generalRef2 = $cResult->object[$n][1];
                  }
                }
                $n++;

                $biTO = $postBIDAO->setSmartEventStatus($se["uid"], $generalRef1, $generalRef2);
                if ($biTO->type!=FLAG_ERRORTO_SUCCESS) {
                  BroadcastingUtils::sendAlertEmail("System Error","Export failed to update smart_event status in processExport.php, setSmartEventStatus","Y");
                  // no need to rollback, as it will just keep on trying to run next time
                  break;
                }
    	      }


    	      $dbConn->dbinsQuery("commit;");  //commit for adaptor and Notification Recipient identifier 'TO' distribution table.
    	    }

        } else {

          //adaptor failure...
          $dbConn->dbinsQuery("rollback;");  //rollback adaptor.
          
          BroadcastingUtils::sendAlertEmail("System Error","Error occurred in processExport.php : ".$cResult->description,"Y", true);

          //update failure to noti
    	    $bIResult = $postBIDAO->postNotificationRecipientResult($n['uid'],FLAG_ERRORTO_ERROR,$cResult->description); // this also automatically updates error count and concats the status
          $dbConn->dbinsQuery("commit;");  //commit result.

        }


      } else {
        BroadcastingUtils::sendAlertEmail("System Error","File Confirmation Export Adaptor type {$paramExportAdaptor} for nr.UID {$n["uid"]} was not found.","Y");
      }

      $statJOBS++;
    }

  endforeach;

}

/*******************************************************************************************************************************************/


$statET = microtime(true);
$statTT = round($statET - $statST,4);
echo "<hR>[@>>>JOBS:".$statJOBS.";TT:".$statTT."@]<BR>";  //stat line.
echo "END: ".CommonUtils::getGMTime(0)."<BR>[***EOS***]";



<?php
/**********************************************************************************************
 ********************************************************************************************** 
 * *
 * * This job must only run once a day !
 * *
 ********************************************************************************************** 
 **********************************************************************************************/
 
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
//require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/BIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostBIDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

set_time_limit(300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// time limit is set by calling JobExecution
echo "<BR>Job Started :".CommonUtils::getGMTime(0)."<BR>";

// calling program may already have set this in JobExecution
if (!isset($dbConn)) {
	$dbConn = new dbConnect();
	$dbConn->dbConnection();
}

$postDistributionDAO = new PostDistributionDAO($dbConn);
$administrationDAO = new AdministrationDAO($dbConn);
$postBIDAO = new PostBIDAO($dbConn);
$bIDAO = new BIDAO($dbConn);
$mfNR = $bIDAO->getActiveNotificationRecipients();

if (sizeof($mfNR)==0) {
	echo "No active notifications found";
	return;
}


function queueEmail($recipientUId, $subject, $body) {
	global $postDistributionDAO;
	
	$dTO = new PostingDistributionTO;
	$eTO = new ErrorTO;
	
	$dTO->DMLType = "INSERT";
	$dTO->subject = $subject;
	$dTO->body = $body;
	$dTO->deliveryType = BT_EMAIL;
	$dTO->destinationUserUId=$recipientUId;
	$dTO->body.="<br><br>*** Please do NOT reply to this message as this email box is not monitored.";
	$dResult=$postDistributionDAO->postQueueDistribution($dTO);
	if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
		$eTO->type = FLAG_ERRORTO_ERROR;
		$eTO->description = $dResult->description;
		return $eTO;
	}
	$eTO->type = FLAG_ERRORTO_SUCCESS;
	$eTO->description = "Successfully queued email in queueEmail.";
	return $eTO;
}

function problemNotification($notification, $errDesc, $suList) {
	global $postBIDAO;
	// PROBLEM with recipient, so alert hierarchy
	$bIResult=$postBIDAO->postNotificationRecipientResult($notification["uid"], FLAG_ERRORTO_ERROR,addSlashes($errDesc)); // this also automatically updates error count and status
	if ($bIResult->type != FLAG_ERRORTO_SUCCESS) BroadcastingUtils::sendAlertEmail("System Error","A critical Error occurred calling postNotificationRecipientResult() for notification {$notification["uid"]}","Y");
	// send error email to SUs
	$adminEmailNeeded=true;
	foreach ($suList as $su) {
		if (preg_match(GUI_PHP_EMAIL_REGEX,$su["user_email"])) {
			$eResult=queueEmail($su["uid"],
					  			"Request for Action : Notifications error",
					  			"Dear Super User,<BR>".
							    "An error occurred sending a Notification ($errDesc) for one of your principal-users.<BR><BR>".
							    "Please check the notifications screen under Reports to see the error Message and correct the mistake.<BR><BR>".
					            "The notification service will be disabled after 10 attempts at sending. <B><U>The error count currently is ".($notification["error_count"]+1)."</U></B><BR><BR>");
			if ($eResult->type!=FLAG_ERRORTO_SUCCESS)  BroadcastingUtils::sendAlertEmail("System Error","Could not send email to SU ({$su["user_email"]})","Y");
			$adminEmailNeeded=false;
		} else {
			$adminEmailNeeded=true;
		}
	}
	// if blank email or no SU's, send to RTT staff
	if ($adminEmailNeeded) {
		BroadcastingUtils::sendAlertEmail("System Error","There are no SuperUsers set up for Principal {$notification["principal_uid"]}, and an error occurred during the notifications processing and could therefore not be sent.","Y");
	}
}



/******************************************************
 *  Process Notification : PRICING DEALS EXPIRY
 * 						   STOCK THRESHOLD LIMIT
 ******************************************************/

foreach ($mfNR as $n) {
	if (($n["notification_uid"]==NT_PRICE_DEAL_EXPIRY) || ($n["notification_uid"]==NT_STOCK_THRESHOLD)) {
		$mfSU=$administrationDAO->getSuperUsersForPrincipal($n["principal_uid"]); // get list of SUs to send errors to
		
		$recipients=explode(",",$n["user_uid_list"]); // if blank, sizeof() will still be 1
		$distributionSourceIdentifier=CommonUtils::getRandomInteger(); // linked to created distributions for retrieval on query screen
		// update the distribution uid to link any distributions created later for screen lookups
		$bIResult=$postBIDAO->postNotificationRecipientDistribution($n["uid"],$distributionSourceIdentifier); // identifier set at start. important to update it regardless of successfully queueing distribution as you don't want it to point to old entry
		if ($bIResult->type!=FLAG_ERRORTO_SUCCESS) {
			$bIResult=$postBIDAO->postNotificationRecipientResult($n["uid"],FLAG_ERRORTO_ERROR,"Could not set the distribution id passback."); // this also automatically updates error count and concats the status
			$dbConn->dbinsQuery("commit;");
			continue;
		}
		foreach ($recipients as $r) {
			// validate the recipients
			$errDesc="";
			$valid=true;
			$mfU=$administrationDAO->getUserItem($r); // dont worry about checking whether user within priviledges
			if (sizeof($mfU)==0) {
				$errDesc.=" Invalid Recipient UId ({$r})";
				$valid=false;
			}
			if (($n["delivery_type"]==BT_EMAIL) && (!preg_match(GUI_PHP_EMAIL_REGEX,$mfU[0]["user_email"]))) {
				$errDesc.=" Chosen User's ({$mfU[0]["full_name"]}) email address is not a valid format for delivery.";
				$valid=false;
			}
			if (($n["delivery_type"]==BT_SMS) && (!preg_match(GUI_PHP_MOBILE_REGEX,$mfU[0]["user_cell"]))) {
				$errDesc.=" Chosen User's ({$mfU[0]["full_name"]}) cell/mobile number is not a valid format for delivery.";
				$valid=false;
			}
			if (($mfU[0]["suspended"]=="1") || ($mfU[0]["deleted"]=="1")) {
				$errDesc.=" Chosen User ({$mfU[0]["full_name"]}) has been suspended or deleted.";
				$valid=false;
			}
			$dayDiff=floor((strtotime(date(GUI_PHP_DATETIME_FORMAT))-strtotime($mfU[0]["lastlogin"]))/24/60/60);
			if ($dayDiff>30) {
				$errDesc.=" Chosen User ({$mfU[0]["full_name"]}) has not logged in within the last 30 days, and therefore is disallowed.";
				$valid=false;
			}
			
			$postingDistributionTO = new PostingDistributionTO;
			$postingDistributionTO->DMLType="INSERT";
			$postingDistributionTO->deliveryType=$n["delivery_type"];
			$postingDistributionTO->sourceIdentifier=$distributionSourceIdentifier;
			$postBIDAO->postNotificationRecipientStart($n["uid"]);  // set the run_date, status_msg for this processing run
			
			// ALL GOOD, so process the recipients 
			if ($valid===true) {
				$text=""; $subjectText="";
				switch (intval($n["notification_uid"])) {
					case NT_PRICE_DEAL_EXPIRY:
						  $biResult=$bIDAO->getBIPriceDealExpiries($r, $n["principal_uid"], $n["value"]);
						  if ((isset($biResult[0])) && ($biResult[0]["cnt"]>0)) {
							if ($n["delivery_type"]==BT_SMS) {
								$text="You have {$biResult[0]["cnt"]} pricing deal(s) set to expire over next {$n["value"]} days."; // footer is added to text inside call sendSMS
								$postingDistributionTO->destinationAddr=$mfU[0]["user_cell"];
							} else if ($n["delivery_type"]==BT_EMAIL) {
								$subjectText="Kwelanga Solutions: Pricing Deals Expiry";
								$text="Dear {$mfU[0]["full_name"]},<BR>You have {$biResult[0]["cnt"]} pricing deal(s) set to expire over next {$n["value"]} days.<BR><BR>*** You have been signed up to receive these business intelligence Notifications from the Retailtrading System";
							}
						  }
						  break;
					case NT_STOCK_THRESHOLD:
						  $productList=""; $depotList="";
						  $params=explode("&",$n["additional_parameter_string"]); // split up the supplied params
						  foreach ($params as $p) {
							$valArr=explode("=",$p);
							if ((isset($valArr[0])) && ($valArr[0]=="p1") && (isset($valArr[1])) && ($valArr[1]!="*")) $productList=$valArr[1];
							if ((isset($valArr[0])) && ($valArr[0]=="p2") && (isset($valArr[1])) && ($valArr[1]!="*")) $depotList=$valArr[1];	
						  }
						  $biResult=$bIDAO->getBIAvailStockThreshold($r, $n["principal_uid"], $n["value"], $productList, $depotList);
						  if ((isset($biResult[0])) && ($biResult[0]["cnt"]>0)) {
							if ($n["delivery_type"]==BT_SMS) {
								$depotCodes=$biResult[0]["depot_codes"];
								if (strlen($depotCodes)>90) $depotCodes="(".(substr($depotCodes,0,90))."...)"; else $depotCodes="({$depotCodes})";
								$text="{$biResult[0]["cnt"]} stock item(s) have fallen below {$n["value"]} for dpts {$depotCodes}"; // footer is added to text inside call sendSMS
								$postingDistributionTO->destinationAddr=$mfU[0]["user_cell"];
							} else if ($n["delivery_type"]==BT_EMAIL) {
								$subjectText="Kwelanga Solutions Notification: Available Stock Threshold";
								$text="Dear {$mfU[0]["full_name"]},<BR>{$biResult[0]["cnt"]} stock item(s) have fallen below {$n["value"]} for dpts {$biResult[0]["depot_codes"]}.<BR><BR>*** You have been signed up to receive these business intelligence Notifications from the Retailtrading System";
							}
						  }
						  break;
					default;
				}
				
				if ((isset($biResult[0])) && ($biResult[0]["cnt"]>0)) {
					// queue the distribution
					$postingDistributionTO->subject=$subjectText;
					$postingDistributionTO->body=$text;
					$postingDistributionTO->destinationUserUId=$r; // not necessary, only to output error msg using useruid
					$dResult=$postDistributionDAO->postQueueDistribution($postingDistributionTO);
					if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
						BroadcastingUtils::sendAlertEmail("System Error","Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$n["uid"]}.","Y");
						$bIResult=$postBIDAO->postNotificationRecipientResult($n["uid"],FLAG_ERRORTO_ERROR,addSlashes($dResult->description)); // this also automatically updates error count and status
					} 
				}
			} else {
				problemNotification($n, $errDesc, $mfSU);
			}
			
			$dbConn->dbinsQuery("commit;");
			
		} // end recipients loop
		$dbConn->dbinsQuery("commit;"); // in case of continue
	} // end if pricing notification
}  // end notification loop for pricing

echo "Successfully Generated Notifications for Distribution";

echo "<BR>Job Ended :".CommonUtils::getGMTime(0)."\n";
echo "[***EOS***]";

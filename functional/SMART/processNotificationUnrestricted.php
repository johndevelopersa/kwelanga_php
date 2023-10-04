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

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
//require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'properties/ServerConstants.php');
include_once($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'DAO/BIDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostBIDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/MiscellaneousDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostTransactionDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostDistributionDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ReportDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingDistributionTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . 'functional/export/adaptor/AdaptorCustomDocumentConfirmation.php');
include_once($ROOT . $PHPFOLDER . "libs/EncryptionClass.php");
include_once $ROOT . $PHPFOLDER . 'libs/storage/Storage.php';

//setup S3 storage class.
new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);

set_time_limit(60 * 15);
error_reporting(-1);
ini_set('display_errors', 1);

// time limit is set by calling JobExecution
echo "<BR>Job Started :" . CommonUtils::getGMTime(0) . "<BR>";

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
$aCustomDocumentConfirmation = new AdaptorCustomDocumentConfirmation($dbConn, $bIDAO);
$encryption = new EncryptionClass();

$mfSU = null; // list of Super Users for Principal


//this notification generates to a file because the file could be larger than body field size or the recipient does not have HTML enabled to read body links
$bkUpFolder = FILE_ARCHIVE_NOTIFICATIONS_PATH . date("Y") . "/" . date("m") . "/" . date("d") . "/";

function validateDestinationAddr($notificationObject, $contactObject)
{
    global $mfSU;

    // validate destination - we do it here (as well as in distribution) because we set an error count in this logic
    if (($notificationObject["delivery_type"] == BT_EMAIL) && (!preg_match(GUI_PHP_EMAIL_REGEX, $contactObject["email_addr"]))) {
        $errDesc = " Chosen User's ({$contactObject["uid"]} -{$contactObject["name"]}) email address is not a valid format for delivery.";
        echo $errDesc;
        problemNotification($notificationObject, $errDesc, $mfSU);
        return false;
    } else if (($notificationObject["delivery_type"] == BT_SMS) && (!preg_match(GUI_PHP_MOBILE_REGEX, $contactObject["mobile_number"]))) {
        $errDesc = " Chosen Contact's ({$contactObject["uid"]} - {$contactObject["name"]}) cell/mobile number is not a valid format for delivery (nruid:{$notificationObject["uid"]}).";
        echo $errDesc;
        problemNotification($notificationObject, $errDesc, $mfSU);
        return false;
    }

    return true;
}

function queueEmail($recipientUId, $subject, $body)
{
    global $postDistributionDAO;

    $dTO = new PostingDistributionTO;
    $eTO = new ErrorTO;

    $dTO->DMLType = "INSERT";
    $dTO->subject = $subject;
    $dTO->body = $body;
    $dTO->deliveryType = BT_EMAIL;
    $dTO->destinationUserUId = $recipientUId;
    $dTO->body .= "<br><br>*** Please do NOT reply to this message as this email box is not monitored.";
    $dResult = $postDistributionDAO->postQueueDistribution($dTO);
    if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = $dResult->description;
        return $eTO;
    }
    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successfully queued email in queueEmail.";
    return $eTO;
}

function problemNotification($notification, $errDesc, $suList)
{
    global $postBIDAO;
    // PROBLEM with recipient, so alert hierarchy
    $bIResult = $postBIDAO->postNotificationRecipientResult($notification["uid"], FLAG_ERRORTO_ERROR, addSlashes($errDesc)); // this also automatically updates error count and status
    if ($bIResult->type != FLAG_ERRORTO_SUCCESS) BroadcastingUtils::sendAlertEmail("System Error", "A critical Error occurred calling postNotificationRecipientResult() for notification {$notification["uid"]}", "Y");
    // send error email to SUs
    $adminEmailNeeded = true;
    foreach ($suList as $su) {
        if (preg_match(GUI_PHP_EMAIL_REGEX, $su["user_email"])) {
            $eResult = queueEmail($su["uid"],
                "Request for Action : Notifications error",
                "Dear Super User,<BR>" .
                "An error occurred sending a Notification ({$notification["description"]}) for one of your principal-users.<BR><BR>" .
                "Please check the notifications screen under Reports to see the error Message and correct the mistake.<BR><BR>" .
                $errDesc . "<br><br>" .
                "The notification service will be disabled after 10 attempts at sending. <B><U>The error count currently is " . ($notification["error_count"] + 1) . "</U></B><BR><BR>");
            if ($eResult->type != FLAG_ERRORTO_SUCCESS) BroadcastingUtils::sendAlertEmail("System Error", "Could not send email to SU ({$su["user_email"]})", "Y");
            $adminEmailNeeded = false;
        } else {
            $adminEmailNeeded = true;
        }
    }
    // if blank email or no SU's, send to RTT staff
    if ($adminEmailNeeded) {
        BroadcastingUtils::sendAlertEmail("System Error", "There are no SuperUsers set up for Principal {$notification["principal_uid"]}, and an error occurred during the notifications processing and could therefore not be sent.", "Y");
    }
}


/*******************************************************************************************************************************************
 * PROCESS NOTIFICATION : Document Confirmations
 *
 * Be careful when making changes. It is written like this to maximise all contacts getting the attachment, as rows can only be sent once.
 *******************************************************************************************************************************************/

echo "<br>1. starting PROCESS NOTIFICATION : Document Confirmations";


//get queued document confirmations
$seNR = $bIDAO->getQueuedSmartEvents(SE_NOTIFICATION, false);


if (sizeof($seNR) == 0) {
    echo "<br>No queued 'Documents Confirmations' smart events found!";
} else {


    //GROUP EVENTS BY TYPE UID (Notification Recipient uid)
    $grpNotifications = array();
    foreach ($seNR as $se) {
        $grpNotifications[$se['type_uid']][] = $se;
    }

    foreach ($grpNotifications as $nrUId => $notiArr) {

        //set vars
        $atleast1RecipientPassed = false;
        $html = "";
        $seGeneral1 = "";
        $seGeneral2 = "";
        $dataArr = array();
        $docFilter = array(); //builds a custom list of filtered docs to passed to the adaptor script
        $docMList = array();


        //get data
        $mfNR = $bIDAO->getNotificationRecipientItem($nrUId);
        // smart events might have been loaded already and someone removed the notification
        if (count($mfNR) == 0) {
            continue;
        }
        $mfSU = $administrationDAO->getSuperUsersForPrincipal($mfNR[0]['principal_uid']); // get list of SUs to send errors to

        if ($mfNR[0]["output_type"] != OT_SMS_STANDARD_TEXT) {
            $dataArr[] = array("Principal", "Depot", "Store Name", "Document Number", "Document Type", "Status", "Order Date", "Reference", "Incoming File", "Processed Date & Time", "View Online Link");
        }

        //get document data
        foreach ($notiArr as $seRow) {
            $docMList[] = $seRow['data_uid'];
        } //build doc uid list
        $docArr = $bIDAO->getBIDocumentsByUIdList(join(',', $docMList));


        //output area
        foreach ($docArr as $doc) {

            $docFilter[] = $doc;
            $guid = md5($doc["uid"] . $doc["document_number"] . NT_DOCUMENT_CONFIRMATION);
            if ($mfNR[0]["output_type"] == OT_SMS_STANDARD_TEXT) {
                // at the moment - to safeguard against too many sms being sent - sms only available for capture. One sms per doc.
                if ($doc["data_source"] == DS_CAPTURE) {
                    $dataArr[] = array("Principal" => $doc["principal_name"],
                        "Store Name" => $doc["deliver_name"],
                        "Document Number" => $doc["document_number"],
                        "Client Document Number" => $doc["client_document_number"],
                        "Document Type" => $doc["document_type"],
                        "Reference" => $doc["customer_order_number"]
                    );
                }
            } else {

                if (substr($doc["principal_name"], 0, 3) == "ITE") {

                    $dataArr[] = array("Principal" => $doc["principal_name"],
                        "Depot" => $doc["depot_name"],
                        "Store Name" => $doc["deliver_name"],
                        "Document Number" => $doc["document_number"],
                        "Client Document Number" => $doc["client_document_number"],
                        //"Order Capture Seq"=>$doc["order_sequence_no"],  //no longer required FOR CLIENT DISPLAYS - NB!! For Developers
                        "Document Type" => $doc["document_type"],
                        "Status" => $doc["status"],
                        "Order Date" => $doc["order_date"],
                        "Reference" => $doc["customer_order_number"],
                        "Incoming File" => $doc["incoming_file"],
                        "Processed Date" => $doc["processed_date"] . " " . $doc["processed_time"],
                        "No Link Available"
                    );
                } else {
                    $dataArr[] = array("Principal" => $doc["principal_name"],
                        "Depot" => $doc["depot_name"],
                        "Store Name" => $doc["deliver_name"],
                        "Document Number" => $doc["document_number"],
                        "Client Document Number" => $doc["client_document_number"],
                        //"Order Capture Seq"=>$doc["order_sequence_no"],  //no longer required FOR CLIENT DISPLAYS - NB!! For Developers
                        "Document Type" => $doc["document_type"],
                        "Status" => $doc["status"],
                        "Order Date" => $doc["order_date"],
                        "Reference" => $doc["customer_order_number"],
                        "Incoming File" => $doc["incoming_file"],
                        "Processed Date" => $doc["processed_date"] . " " . $doc["processed_time"],
                        "View Online Link" => HOST_SURESERVER_AS_USER . "systems/kwelanga_system/r/?di={$encryption->encryptUIDValue($doc["uid"],0,6)}"
                    );
                }

            }

        } //end of document loop -

        $recipients = explode(",", $mfNR[0]["user_uid_list"]); // if blank, sizeof() will still be 1

        /*--------------------------------------------*/
        /*
         *  change : onyx
         *  dated : 2013/05/14
         *  description : distribution source identifier was a rand int,
         *                this is not safe for linking, a sequence key has therefore
         *                been created for across the system for linking purposes.
         */

        include_once($ROOT . $PHPFOLDER . "TO/SequenceTO.php");
        include_once($ROOT . $PHPFOLDER . "DAO/SequenceDAO.php");
        $getSequenceResult = CommonUtils::getRandomInteger(); //preset incase of failure/error with actual key.
        $sequenceTO = new SequenceTO();
        $sequenceTO->sequenceKey = "SOURCEID";
        $sequenceTO->sequenceStart = 0;
        $sequenceTO->sequenceLen = 6;
        $sequenceDAO = new SequenceDAO($dbConn);
        $seqResult = $sequenceDAO->getSequence($sequenceTO, $getSequenceResult);

        /*--------------------------------------------*/


        // linked to created distributions for retrieval on query screen
        $seGeneral1 = $distributionSourceIdentifier = $getSequenceResult;


        // update the distribution uid to link any distributions created later for screen lookups
        $bIResult = $postBIDAO->postNotificationRecipientDistribution($mfNR[0]["uid"], $distributionSourceIdentifier); // identifier set at start. important to update it regardless of successfully queueing distribution as you don't want it to point to old entry
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
            $bIResult = $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, "Could not set the distribution id passback."); // this also automatically updates error count and concats the status
            continue;
        }


        if (!in_array($mfNR[0]["delivery_type"], array(BT_EMAIL, BT_SMS, BT_FTP, BT_FTP_LOCALDIR))) {
            $bIResult = $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, "Only EMAIL,SMS and FTP/Local FTP output type is supported!"); // this also automatically updates error count and concats the status
            continue;
        }


        //distribution.
        $postingDistributionTO = new PostingDistributionTO;
        $postingDistributionTO->DMLType = "INSERT";
        $postingDistributionTO->deliveryType = $mfNR[0]["delivery_type"];
        $postingDistributionTO->sourceIdentifier = $distributionSourceIdentifier;
        $subject = ($mfNR[0]["delivery_type"] == BT_EMAIL) ? ("Order Confirmation - " . (sizeof($dataArr) - 1) . " Document(s)") : ("");
        $postingDistributionTO->subject = $subject;


        // set the output type
        //custom format.
        //sets output and output content.
        $adaptorMethod = CommonUtils::getParamValuesFromString($mfNR[0]["additional_parameter_string"], "p6");

        if ((trim($adaptorMethod) != '') && (method_exists($aCustomDocumentConfirmation, $adaptorMethod) == true)) {

            $reTO = $aCustomDocumentConfirmation->$adaptorMethod($docFilter);

            if (in_array($reTO->identifier, array(OT_CSV, OT_EXPORT_FILE))) {
                if ($reTO->type != FLAG_ERRORTO_SUCCESS) {
                    echo "Failed to create file in Document Confirmation Custom Adaptor for notification {$mfNR[0]["uid"]}.";
                    $bIResult = $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, "Failed to create file in Document Confirmation Custom Adaptor!"); // this also automatically updates error count and concats the status
                    continue;
                }
                $postingDistributionTO->attachmentFile = str_replace("../", "", $reTO->object);
                $postingDistributionTO->subject .= " (" . basename($reTO->object) . ")";
                $html = $reTO->description;
                $seGeneral2 = basename($reTO->object);
            } else {
                $html = $reTO->object;
            }

        } else {

            if ($mfNR[0]["output_type"] == OT_CSV) {

                // get filename. This notification does not generate a separate file per user so do this outside recipient loop
                $fileName = $bkUpFolder . $bIDAO->getNotificationFilename(NT_DOCUMENT_CONFIRMATION, "C", $mfNR[0]["principal_uid"]);
                $fileResult = BroadcastingUtils::getCSVData($dataArr, "");

				$s3Result = Storage::putObject(S3_BUCKET_NAME, $fileName, $fileResult);
				if (!$s3Result) {
					echo "storage error: " . $s3Result . "\n";
                    problemNotification($mfNR[0], "processNotificationUnrestricted.php storage error: " . $s3Result . ' --- #' . $mfNR[0]["uid"], $mfSU);
                    continue;
				}

				$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, $fileName);

				$seGeneral2 = basename($fileName);

            } elseif ($mfNR[0]["output_type"] == OT_HTML) {
                $html = $reportDAO->reportSQL_arrayToHTML(array_slice($dataArr, 1), $displayHeaderRow = true, "View Online Link='<a href=#>View Document Online</a>'"); // remove the header put there for CSV
            } elseif ($mfNR[0]["output_type"] == OT_SMS_STANDARD_TEXT) {
                // let the processing below set the format
            } else {
                $bIResult = $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, "Unknown Output type specify!"); // this also automatically updates error count and concats the status
                continue;
            }

        }

        $postBIDAO->postNotificationRecipientStart($mfNR[0]["uid"]);  // set the run_date for this processing run

        //loop through setup recipients
        foreach ($recipients as $r) {

            // OT_EXPORT_FILE with BT_FTP_LOCAL_DIR does not use distribution, as once the file is put there then that is the end of the process
            if (($mfNR[0]["output_type"] == OT_EXPORT_FILE) && ($mfNR[0]["delivery_type"] == BT_FTP_LOCALDIR)) {
                $atleast1RecipientPassed = true;
                continue;
            }

            // validate the recipients (contacts) - there should only be one for time being !
            $mfC = $miscellaneousDAO->getContactItem($mfNR[0]["principal_uid"], "", $r);
            if (sizeof($mfC) == 0) {
                problemNotification($mfNR[0], "Invalid Recipient/Contact UId ({$r})", $mfSU);
                continue;
            }

            // can have multiple of same contact
            foreach ($mfC as $c) {

                if (in_array($mfNR[0]["delivery_type"], array(BT_EMAIL, BT_SMS))) {
                    if (validateDestinationAddr($mfNR[0], $c) !== true) continue; // validate the destination address of the recipient and email SU's if problem
                }

                // ALL GOOD, so process the recipients
                // NOTE : We use CONTACTS because this SKIPS the user permissions for viewing a document !!!
                // UNUSED IF FTP
                if (trim($adaptorMethod) != '') {

                    $text = $html;

                } else {

                    if ($mfNR[0]["output_type"] == OT_CSV) {
                        $text = "Dear {$c["name"]},<BR><BR>" . (sizeof($dataArr) - 1) . " new document(s) have been processed.<BR><BR>Please find attached a CSV file containing the details of these.<br><br>" .
                            "<U>Instructions</U><br>" .
                            "1.Open the attachment by double clicking it<br>" .
                            "2.The first column contains the link to view the document properly formatted online. If the link is not clickable directly, then simply click on the cell with the link, press F2 and then ENTER. The link will be converted to a clickable link. Clicking on it will bring the online document up in your browser.<br>" .
                            "3.When the document appears, click on the view-as-invoice-PDF at top to convert the document into a proper encrypted invoice which can then be saved on your computer and emailed accordingly." .
                            "<BR><BR>*** You have been signed up to receive these business intelligence Notifications from the Kwelanga System";
                    } else if ($mfNR[0]["output_type"] == OT_SMS_STANDARD_TEXT) {
                        // handle below
                    } else {
                        $text = "Dear {$c["name"]},<BR><BR>" . (sizeof($dataArr) - 1) . " new document(s) have been processed.<BR><BR>Please find below a list of these.<br><br>" .
                            $html .
                            "<BR><BR>*** You have been signed up to receive these business intelligence Notifications from the Kwelanga System";
                    }

                }
                // queue the distribution

                $batchProcess = (($mfNR[0]["delivery_type"] == BT_SMS) ? false : true); // group all docs together on one distribution entry ?

                if ($mfNR[0]["delivery_type"] == BT_EMAIL) {
                    $postingDistributionTO->body = $text;
                    $postingDistributionTO->destinationAddr = $c["email_addr"];
                } else if ($mfNR[0]["delivery_type"] == BT_SMS) {
                    $postingDistributionTO->destinationAddr = $c["mobile_number"];
                } else if ($mfNR[0]["delivery_type"] == BT_FTP) {
                    $postingDistributionTO->body = '';  //empty for ftp as we only need the ftp array and file.
                    $postingDistributionTO->destinationAddr = $c["ftp_addr"];  //serialized ftp array
                }

                if ($batchProcess) {

                    $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);

                    if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
                        BroadcastingUtils::sendAlertEmail("System Error", "Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$mfNR[0]["uid"]}.", "Y");
                        $bIResult = $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, addSlashes($dResult->description)); // this also automatically updates error count and concats the status
                        // (we have to continue processing as all users MUST get the list, and because we then update the status so can never return)
                    } else {
                        $atleast1RecipientPassed = true;
                    }

                } else {

                    // one dTO per doc
                    foreach ($dataArr as $doc) {

                        $postingDistributionTO->body = substr("{$doc["Document Type"]} {$doc["Document Number"]} Submitted for {$doc["Store Name"]} Ref:{$doc["Reference"]}", 0, 145);

                        $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);

                        if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
                            BroadcastingUtils::sendAlertEmail("System Error", "Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$mfNR[0]["uid"]}.", "Y");
                            $bIResult = $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, addSlashes($dResult->description)); // this also automatically updates error count and concats the status
                            // (we have to continue processing as all users MUST get the list, and because we then update the status so can never return)
                        } else {
                            $atleast1RecipientPassed = true;
                        }

                    }

                }


            } // end multiple contacts loop

        } // end recipients loop


        if ($atleast1RecipientPassed) {

            //update smart events
            foreach ($notiArr as $se) {

                $bIResult = $postBIDAO->setSmartEventStatus($se['uid'], $seGeneral1, $seGeneral2); //set smart event items where
                if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("Error in processNotificationUnrestricted", "error setting setSmartEventStatus " . $bIResult->description, "Y", false);
                    $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, addSlashes($bIResult->description)); // this also automatically updates error count and concats the status
                    echo $bIResult->description . "<BR>";
                    break;
                }

            }

        }

        //commit all => smart_event, distribution & notification
        $dbConn->dbinsQuery("commit;"); // commit all recipients (or until 1st error), including any error Results backupdated


    }

    $dbConn->dbinsQuery("commit;"); //catch notifications results

}


/*******************************************************************************************************************************************
 * PROCESS NOTIFICATION : Electronic Import Exceptions
 *
 * Be careful when making changes. It is written like this to maximise all contacts getting the attachment, as rows can only be sent once.
 *******************************************************************************************************************************************/

echo "<br>2. starting PROCESS NOTIFICATION : Electronic Import Exceptions";
$mfNR = $bIDAO->getNotificationElectronicException(""); // get all notifications of this type across all principals

if (sizeof($mfNR) == 0) {
    echo "<br>No active 'Electronic Import Exceptions' notifications found";
}

function updateElectronicExceptionsSent($n, $force = false)
{
    global $uidArr, $postTransactionDAO, $dbConn;

    // process if final call, or the notification uid has changed
    if (($force === true) || ((!isset($uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]])) && (sizeof($uidArr) > 0))) {
        $dbConn->dbinsQuery("commit;"); // this commmit is for distTO's that got inserted by general exceptions. Must be done first because of rollbacks below
        foreach ($uidArr as $u) {
            if ($u["exception"]["sent_count"] > 0) {
                $bIResult = $postTransactionDAO->setOrdersHoldingExceptionNotified(implode(",", array_unique($u["exception"]["uids"])));
                if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                    echo $bIResult->description . "<BR>";
                    BroadcastingUtils::sendAlertEmail("System Error", "Failed to setOrdersHoldingExceptionNotified.", "Y");
                    $dbConn->dbinsQuery("rollback;");
                    return false;
                } else {
                    $dbConn->dbinsQuery("commit;");
                }
            }
            if ($u["co"]["sent_count"] > 0) {
                $bIResult = $postTransactionDAO->setOrdersHoldingCancelledOrdersNotified(implode(",", array_unique($u["co"]["uids"])));
                if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                    echo $bIResult->description . "<BR>";
                    BroadcastingUtils::sendAlertEmail("System Error", "Failed to setOrdersHoldingCancelledOrdersNotified.", "Y");
                    $dbConn->dbinsQuery("rollback;");
                    return false;
                } else {
                    $dbConn->dbinsQuery("commit;");
                }
            }
        }

        $uidArr = array();
    }

    return true;
}

$uidArr = array();

// print_r($mfNR);
foreach ($mfNR as $n) {
    // update whenever the notification changes - could have had multiple of same notification types, one for SMS and another for EMAIL etc.
    if (!updateElectronicExceptionsSent($n)) return false;

    $mfSU = $administrationDAO->getSuperUsersForPrincipal($n["principal_uid"]); // get list of SUs to send errors to

    $recipients = explode(",", $n["user_uid_list"]); // if blank, sizeof() will still be 1
    $distributionSourceIdentifier = CommonUtils::getRandomInteger(); // linked to created distributions for retrieval on query screen

    // update the distribution uid to link any distributions created later for screen lookups
    $bIResult = $postBIDAO->postNotificationRecipientDistribution($n["uid"], $distributionSourceIdentifier); // identifier set at start. important to update it regardless of successfully queueing distribution as you don't want it to point to old entry
    if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $bIResult = $postBIDAO->postNotificationRecipientResult($n["uid"], FLAG_ERRORTO_ERROR, "Could not set the distribution id passback."); // this also automatically updates error count and concats the status
        $dbConn->dbinsQuery("rollback;");
        return false;
    }

    $bIResult = $bIDAO->getBIElectronicExceptions($n["principal_uid"]); // only run once for whole batch of users

    if (sizeof($bIResult) == 0) {
        continue;
    }

    $postBIDAO->postNotificationRecipientStart($n["uid"]);  // set the run_date for this processing run

    $postingDistributionTO = new PostingDistributionTO;
    $postingDistributionTO->DMLType = "INSERT";
    $postingDistributionTO->deliveryType = $n["delivery_type"];
    $postingDistributionTO->sourceIdentifier = $distributionSourceIdentifier;

    // setup the general exceptions
    $GELines = array();
    foreach ($bIResult as $doc) {
        if (!isset($uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["exception"]["sent_count"])) {
            $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["exception"]["sent_count"] = 0;
        }
        // only send notifications once for each
        if ((($doc["exception_notified_hdr"] == FLAG_STATUS_QUEUED) || ($doc["exception_notified_dtl"] == FLAG_STATUS_QUEUED)) &&
            ($doc["status_hdr"] != "S") &&
            (($doc["status_hdr"] != "") || ($doc["status_dtl"] != ""))
        ) {
            $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["exception"]["uids"][] = $doc["oh_uid"]; // update is done in batch mode at end for (1)speed, (2)must only be done once all of possible multiple of same notification have run.
            // MUST join string multilinks like this and not continuous open quote because the tabs cause table to be moved right down page on email
            $GELines[$doc["oh_uid"]]["header"] = "<td>{$doc["reference"]}</td>" .
                "<td>{$doc["client_document_number"]}</td>" .
                "<td>{$doc["principal_name"]}</td>" .
                "<td>{$doc["deliver_name"]}</td>" .
                "<td>{$doc["document_type"]}</td>" .
                "<td>{$doc["created_date"]}</td>" .
                "<td>{$doc["order_date"]}</td>";
            if (isset($GELines[$doc["oh_uid"]]["errors"])) {
                $GELines[$doc["oh_uid"]]["errors"] .= "," . $doc["status_hdr"] . "," . $doc["status_dtl"]; // dups removed later
            } else {
                $GELines[$doc["oh_uid"]]["errors"] = $doc["status_hdr"] . "," . $doc["status_dtl"]; // dups removed later
            }
            $GELines[$doc["oh_uid"]]["status_msg"] = $doc["status_msg"];

        }
    }

    // setup the cancelled orders notification
    $COLines = array();
    foreach ($bIResult as $doc) {
        if (!isset($uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["co"]["sent_count"])) {
            $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["co"]["sent_count"] = 0;
        }
        if ($doc["cancelled_order_notified"] == FLAG_STATUS_QUEUED) {
            $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["co"]["uids"][] = $doc["oh_uid"]; // update is done in batch mode at end for (1)speed, (2)must only be done once all of possible multiple of same notification have run.
            // MUST join string multilinks like this and not continuous open quote because the tabs cause table to be moved right down page on email
            $COLines[$doc["oh_uid"]]["header"] = "<td>{$doc["reference"]}</td>" .
                "<td>{$doc["client_document_number"]}</td>" .
                "<td>{$doc["principal_name"]}</td>" .
                "<td>{$doc["deliver_name"]}</td>" .
                "<td>{$doc["document_type"]}</td>" .
                "<td>{$doc["created_date"]}</td>" .
                "<td>{$doc["order_date"]}</td>";
        }
    }

    foreach ($recipients as $r) {
        // validate the recipients (contacts) - there should only be one for time being !
        $mfC = $miscellaneousDAO->getContactItem($n["principal_uid"], "", $r);
        if (sizeof($mfC) == 0) {
            problemNotification($n, "Invalid Recipient/Contact UId ({$r})", $mfSU);
            continue;
        }
        // can have multiple of same contact type, but at moment is accessed by uid so is only 1
        foreach ($mfC as $c) {
            if (validateDestinationAddr($n, $c) !== true) continue; // validate the destination address of the recipient and email SU's if problem

            // processess the general exceptions
            if (!empty($GELines)) {
                $postingDistributionTO->subject = "Kwelanga Online Notification: " . $n["principal_uid"] . "  " . (sizeof($GELines)) . " Electronic Exception(s)";

                if ($n["delivery_type"] == BT_EMAIL) {
                    $text = "<html><head><style>table,td " . chr(123) . " font-family:courier; font-size:8px; border:1; border-style:solid; border-width:1px;" . chr(125) . "</style></head><body>Dear {$c["name"]},<BR><BR>" . (sizeof($GELines)) . " exceptions have occurred during order imports in the RT system.<BR><BR>Listed below are summary details of these, please visit our Exceptions Management screen in the RT System to manage these or to view further details.<br><br>" .
                        "<table cellspacing=0><tr><th>Reference</th><th>Client Document Number</th><th>Principal</th><th>Store Name</th><th>Document Type</th><th>Created Date</th><th>Order Date</th><th>Error(s)</th></tr>";
                    foreach ($GELines as $line) {
                        $errMsg = $line["status_msg"];
                        $errorArr = array_unique(explode(",", $line["errors"]));
                        foreach ($errorArr as $e) {
                            $errMsg .= GUICommonUtils::translateOHExceptionStatus($e) . "<br>";
                        }
                        $text .= "<tr>{$line["header"]}<td>{$errMsg}</td></tr>";
                    }

                    $text .= "</table><BR><BR>*** You have been signed up to receive these business intelligence Notifications from the Kwelanga System</body></html>";
                    $postingDistributionTO->destinationAddr = $c["email_addr"]; // not necessary, only to output error msg using useruid
                } else if ($n["delivery_type"] == BT_SMS) {
                    $text = "You have " . sizeof($GELines) . " Electronic Exception(s). Please action asap!"; // footer is added to text inside call sendSMS
                    $postingDistributionTO->destinationAddr = $c["mobile_number"];
                }

                // queue the distribution
                $postingDistributionTO->body = $text;
                $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
                if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("System Error", "Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$n["uid"]}.", "Y");
                    $bIResult = $postBIDAO->postNotificationRecipientResult($n["uid"], FLAG_ERRORTO_ERROR, addSlashes($dResult->description)); // this also automatically updates error count and concats the status
                    // (we have to continue processing as all users MUST get the list, and because we then update the status so can never return)
                } else {
                    $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["exception"]["sent_count"]++;
                }
            }

            // process the cancelled orders notification
            if (!empty($COLines)) {
                $postingDistributionTO->subject = "Kwelanga Online Notification: " . (sizeof($COLines)) . " Cancelled Order(s)";
                if ($n["delivery_type"] == BT_EMAIL) {
                    $text = "<html><head><style>table,td " . chr(123) . " font-family:courier; font-size:8px; border:1; border-style:solid; border-width:1px;" . chr(153) . "</style></head><body>Dear {$c["name"]},<BR><BR>" . (sizeof($COLines)) . " Cancelled Orders have been processed during imports in the RT system.<BR><BR>Listed below are summary details of these.<br><br>" .
                        "<table cellspacing=0><tr><th>Reference</th><th>Client Document Number</th><th>Principal</th><th>Store Name</th><th>Document Type</th><th>Created Date</th><th>Order Date</th></tr>";
                    foreach ($COLines as $line) {
                        $text .= "<tr>{$line["header"]}</tr>";
                    }

                    $text .= "</table><BR><BR>*** You have been signed up to receive these business intelligence Notifications from the Kwelanga System</body></html>";
                    $postingDistributionTO->destinationAddr = $c["email_addr"]; // not necessary, only to output error msg using useruid
                } else if ($n["delivery_type"] == BT_SMS) {
                    $text = "You have " . sizeof($COLines) . " Cancelled Orders."; // footer is added to text inside call sendSMS
                    $postingDistributionTO->destinationAddr = $c["mobile_number"];
                }

                // queue the distribution
                $postingDistributionTO->body = $text;
                $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
                if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("System Error", "Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$n["uid"]}.", "Y");
                    $bIResult = $postBIDAO->postNotificationRecipientResult($n["uid"], FLAG_ERRORTO_ERROR, addSlashes($dResult->description)); // this also automatically updates error count and concats the status
                    // (we have to continue processing as all users MUST get the list, and because we then update the status so can never return)
                } else {
                    $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["co"]["sent_count"]++;
                }
            }

        } // end multiple contacts loop

        // prevent buildup for when 1st contact is added - you dont want to get 1000's of PVs
        if (sizeof($mfC) == 0) {
            $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["co"]["sent_count"]++;
        }

    } // end recipients loop

}  // end notification loop for electronic exceptions, $n will still have the last iteration
if (!updateElectronicExceptionsSent($n, true)) return false; // update final iteration, including those with no contacts


/*******************************************************************************************************************************************
 * PROCESS NOTIFICATION : Price Variances
 *
 * Be careful when making changes. It is written like this to maximise all contacts getting the attachment, as rows can only be sent once.
 *******************************************************************************************************************************************/

echo "<br>3. starting PROCESS NOTIFICATION : Price Variances";
$mfNR = $bIDAO->getNotificationPriceVariance(""); // get all notifications of this type across all principals

if (sizeof($mfNR) == 0) {
    echo "<br>No active 'EDI PRICE VARIANCE' notifications found";
}

function updateEPVSent($n, $force = false)
{
    global $uidArr, $postTransactionDAO, $dbConn;

    // process if final call, or the notification uid has changed
    if (($force === true) || ((!isset($uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]])) && (sizeof($uidArr) > 0))) {
        $dbConn->dbinsQuery("commit;"); // Must be done first because of rollbacks below
        foreach ($uidArr as $u) {
            if ($u["pv"]["sent_count"] > 0) {
                $bIResult = $postTransactionDAO->setOrdersHoldingDetailPriceDiffNotified(implode(",", array_unique($u["pv"]["uids"])));
                if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                    echo $bIResult->description . "<BR>";
                    BroadcastingUtils::sendAlertEmail("System Error", "Failed to setOrdersHoldingDetailPriceDiffNotified.", "Y");
                    $dbConn->dbinsQuery("rollback;");
                    return false;
                } else {
                    $dbConn->dbinsQuery("commit;");
                }
            }
        }

        $uidArr = array();
    }

    return true;
}

$uidArr = array();
foreach ($mfNR as $n) {
    // update whenever the notification changes - could have had multiple of same notification types, one for SMS and another for EMAIL etc.
    if (!updateEPVSent($n)) return false;

    $mfSU = $administrationDAO->getSuperUsersForPrincipal($n["principal_uid"]); // get list of SUs to send errors to

    $recipients = explode(",", $n["user_uid_list"]); // if blank, sizeof() will still be 1
    $distributionSourceIdentifier = CommonUtils::getRandomInteger(); // linked to created distributions for retrieval on query screen

    // update the distribution uid to link any distributions created later for screen lookups
    $bIResult = $postBIDAO->postNotificationRecipientDistribution($n["uid"], $distributionSourceIdentifier); // identifier set at start. important to update it regardless of successfully queueing distribution as you don't want it to point to old entry
    if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $bIResult = $postBIDAO->postNotificationRecipientResult($n["uid"], FLAG_ERRORTO_ERROR, "Could not set the distribution id passback."); // this also automatically updates error count and concats the status
        $dbConn->dbinsQuery("rollback;");
        return false;
    }

    $bIResult = $bIDAO->getBIPriceVariances($n["principal_uid"]); // only run once for whole batch of users

    if (sizeof($bIResult) == 0) {
        continue;
    }

    $postBIDAO->postNotificationRecipientStart($n["uid"]);  // set the run_date for this processing run

    $postingDistributionTO = new PostingDistributionTO;
    $postingDistributionTO->DMLType = "INSERT";
    $postingDistributionTO->deliveryType = $n["delivery_type"];
    $postingDistributionTO->sourceIdentifier = $distributionSourceIdentifier;

    // setup the price variance notification
    $PVLines = array();
    foreach ($bIResult as $doc) {
        if (!isset($uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["pv"]["sent_count"])) {
            $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["pv"]["sent_count"] = 0;
        }
        if ($doc["price_diff_notified"] == FLAG_STATUS_QUEUED) {
            $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["pv"]["uids"][] = $doc["ohd_uid"]; // update is done in batch mode at end for (1)speed, (2)must only be done once all of possible multiple of same notification have run.
            // we use oh_uid and not ohd_uid for key because we only want headers
            // MUST join string multilinks like this and not continuous open quote because the tabs cause table to be moved right down page on email
            $PVLines[$doc["oh_uid"]]["header"] = "<td>{$doc["reference"]}</td>" .
                "<td>{$doc["client_document_number"]}</td>" .
                "<td>{$doc["principal_name"]}</td>" .
                "<td>{$doc["deliver_name"]}</td>" .
                "<td>{$doc["document_type"]}</td>" .
                "<td>{$doc["created_date"]}</td>" .
                "<td>{$doc["order_date"]}</td>";
        }
    }

    foreach ($recipients as $r) {
        // validate the recipients (contacts) - there should only be one for time being !
        $mfC = $miscellaneousDAO->getContactItem($n["principal_uid"], "", $r);
        if (sizeof($mfC) == 0) {
            problemNotification($n, "Invalid Recipient/Contact UId ({$r})", $mfSU);
            continue;
        }
        // can have multiple of same contact type, but at moment is accessed by uid so is only 1
        foreach ($mfC as $c) {
            if (validateDestinationAddr($n, $c) !== true) continue; // validate the destination address of the recipient and email SU's if problem

            // process the price variance notification
            if (!empty($PVLines)) {
                $postingDistributionTO->subject = "Kwelanga Notification: " . (sizeof($PVLines)) . " Price Variance(s)";

                if ($n["delivery_type"] == BT_EMAIL) {
                    $text = "<html><head><style>table,td " . chr(123) . " font-family:courier; font-size:8px; border:1; border-style:solid; border-width:1px;" . chr(125) . "</style></head><body>Dear {$c["name"]},<BR><BR>" . (sizeof($PVLines)) . " PRICE VARIANCES have occurred during order imports in the RT system.<BR><BR>Listed below are summary details of these. If you have elected to suspend these, then please visit the Exceptions Management screen in the RT System to manage these or to view further details.<br><br>" .
                        "<table cellspacing=0><tr><th>Reference</th><th>Client Document Number</th><th>Principal</th><th>Store Name</th><th>Document Type</th><th>Created Date</th><th>Order Date</th></tr>";
                    foreach ($PVLines as $line) {
                        $text .= "<tr>{$line["header"]}</tr>";
                    }

                    $text .= "</table><BR><BR>*** You have been signed up to receive these business intelligence Notifications from the Kwelanga System</body></html>";
                    $postingDistributionTO->destinationAddr = $c["email_addr"]; // not necessary, only to output error msg using useruid
                } else if ($n["delivery_type"] == BT_SMS) {
                    $text = "You have " . sizeof($PVLines) . " Price Variance(s)."; // footer is added to text inside call sendSMS
                    $postingDistributionTO->destinationAddr = $c["mobile_number"];
                }

                // queue the distribution
                $postingDistributionTO->body = $text;
                $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
                if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("System Error", "Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$n["uid"]}.", "Y");
                    $bIResult = $postBIDAO->postNotificationRecipientResult($n["uid"], FLAG_ERRORTO_ERROR, addSlashes($dResult->description)); // this also automatically updates error count and concats the status
                    // (we have to continue processing as all users MUST get the list, and because we then update the status so can never return)
                } else {
                    $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["pv"]["sent_count"]++;
                }
            }

        } // end multiple contacts loop

        // prevent buildup for when 1st contact is added - you dont want to get 1000's of PVs
        if (sizeof($mfC) == 0) {
            $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["pv"]["sent_count"]++;
        }

    } // end recipients loop

}  // end notification loop for electronic exceptions, $n will still have the last iteration
if (!updateEPVSent($n, true)) return false; // update final iteration, including those with no contacts


/*******************************************************************************************************************************************
 * PROCESS NOTIFICATION : EDI File Upload Confirmation (Orders)
 *
 * Be careful when making changes. It is written like this to maximise all contacts getting the attachment, as rows can only be sent once.
 *******************************************************************************************************************************************/

echo "<br>4. starting PROCESS NOTIFICATION : EDI File Upload Confirmation (Orders)";
$mfNR = $bIDAO->getAllEDIFileDefNotificationOFD(""); // get all notifications of this type across all principals

if (sizeof($mfNR) == 0) {
    echo "<br>No active 'EDI File Upload Confirmation (Orders)' notifications found";
}

function updateEDIFDSent($n, $force = false)
{
    global $uidArr, $postTransactionDAO, $dbConn;

    // process if final call, or the notification uid has changed
    if (($force === true) || ((!isset($uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]])) && (sizeof($uidArr) > 0))) {
        $dbConn->dbinsQuery("commit;"); // Must be done first because of rollbacks below
        foreach ($uidArr as $u) {
            if ($u["EDIFD"]["sent_count"] > 0) {
                $bIResult = $postTransactionDAO->setOrdersHoldingEDIFileDefNotified(implode(",", array_unique($u["EDIFD"]["uids"])));
                if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                    echo $bIResult->description . "<BR>";
                    BroadcastingUtils::sendAlertEmail("System Error", "Failed to setOrdersHoldingEDIFileDefNotified.", "Y");
                    $dbConn->dbinsQuery("rollback;");
                    return false;
                } else {
                    $dbConn->dbinsQuery("commit;");
                }
            }
        }

        $uidArr = array();
    }

    return true;
}

$bIRows = $uidArr = array();
$currentPrincipal = "";
foreach ($mfNR as $n) {
    // get the BI Queued Rows only on first iteration for same principal and notification (allows for multiple of same notification to be loaded per principal)
    if ($currentPrincipal != $n["principal_uid"]) {
        $bIRows = $bIDAO->getBIEDIFileDefn($n["principal_uid"]); // only run once for all notifications under same principal
        $currentPrincipal = $n["principal_uid"];
    }

    if (sizeof($bIRows) == 0) {
        continue;
    }

    $paramOFPUId = CommonUtils::getParamValuesFromString($n["additional_parameter_string"], "p1", $paramSeparator = "&", $paramValueAsignment = "=");

    // update whenever the notification changes - could have had multiple of same notification types, one for SMS and another for EMAIL etc.
    if (!updateEDIFDSent($n)) return false;

    $mfSU = $administrationDAO->getSuperUsersForPrincipal($n["principal_uid"]); // get list of SUs to send errors to

    $recipients = explode(",", $n["user_uid_list"]); // if blank, sizeof() will still be 1
    $distributionSourceIdentifier = CommonUtils::getRandomInteger(); // linked to created distributions for retrieval on query screen

    // update the distribution uid to link any distributions created later for screen lookups
    $bIResult = $postBIDAO->postNotificationRecipientDistribution($n["uid"], $distributionSourceIdentifier); // identifier set at start. important to update it regardless of successfully queueing distribution as you don't want it to point to old entry
    if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
        $bIResult = $postBIDAO->postNotificationRecipientResult($n["uid"], FLAG_ERRORTO_ERROR, "Could not set the distribution id passback."); // this also automatically updates error count and concats the status
        $dbConn->dbinsQuery("rollback;");
        return false;
    }

    $postBIDAO->postNotificationRecipientStart($n["uid"]);  // set the run_date for this processing run

    $postingDistributionTO = new PostingDistributionTO;
    $postingDistributionTO->DMLType = "INSERT";
    $postingDistributionTO->deliveryType = $n["delivery_type"];
    $postingDistributionTO->sourceIdentifier = $distributionSourceIdentifier;
    $postingDistributionTO->attachmentFile = ""; // reset if was used on last iteration
    $postingDistributionTO->body = ""; // reset if was used on last iteration

    // START : Content
    $lines = array();
    foreach ($bIRows as $doc) {
        // only consider those rows for the selected incoming_file type
        if (($doc["online_file_processing_uid"] == $paramOFPUId) || ($paramOFPUId == "")) {
            if (!isset($uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["EDIFD"]["sent_count"])) {
                $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["EDIFD"]["sent_count"] = 0;
            }
            // unfortunately there if multiple notifications for same principal exist, then the same UID will be inserted more than once
            $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["EDIFD"]["uids"][] = $doc["uid"]; // update is done in batch mode at end for (1)speed, (2)must only be done once all of possible multiple of same notification have run.

            // assign the user friendly status message. They need not be precise or disclose too much.
            if ($doc["status"] == FLAG_ERRORTO_SUCCESS) $status = "Document passed validation";
            else if ($doc["status"] == "") $status = "Document imported, awaiting validation";
            else $status = "Document imported, but failed validation - awaiting principal attention";

            // MUST join string multilinks like this and not continuous open quote because the tabs cause table to be moved right down page on email
            $lines[$doc["incoming_file"]][] = array("Incoming FileName" => $doc["incoming_file"],
                "Created Date" => $doc["created_date"],
                "Processed Date" => $doc["processed_date"],
                "Client Document Number" => $doc["client_document_number"],
                "Reference" => $doc["reference"],
                "Deliver Name" => $doc["deliver_name"],
                "Status" => $status);
        }
    }
    // END : Content

    foreach ($recipients as $r) {
        // validate the recipients (contacts) - there should only be one for time being !
        $mfC = $miscellaneousDAO->getContactItem($n["principal_uid"], "", $r);
        if (sizeof($mfC) == 0) {
            problemNotification($n, "Invalid Recipient/Contact UId ({$r})", $mfSU);
            continue;
        }
        // can have multiple of same contact type, but at moment is accessed by uid so is only 1
        foreach ($mfC as $c) {
            if (validateDestinationAddr($n, $c) !== true) continue; // validate the destination address of the recipient and email SU's if problem

            // process the price variance notification
            if (!empty($lines)) {

                // output one content per file name (at this stage there should really be only one interation of this loop as only one file was selected per notification)
                foreach ($lines as $key => $l) {
                    $postingDistributionTO->subject = "Kwelanga Notification: [EDI File] {$key} has been uploaded";

                    if ($n["delivery_type"] == BT_EMAIL) {

                        // NB: The principal name is used at notification level, not at transactional level, as altho a file can have many principals in it, the notification
                        //     Will only select those for the principal at the level which the notification is loaded
                        $text = "<html><head><style>table,td " . chr(123) . " font-family:courier; font-size:8px; border:1; border-style:solid; border-width:1px;" . chr(125) . "</style></head>" .
                            "<body>Dear {$c["name"]},<BR><BR>[EDI File] {$key} has been uploaded containing " . (sizeof($l)) . " Documents into the RT system for principal : <b>{$n["principal_name"]}</b>.<BR><BR>" .
                            "Listed below (or as an attachment) are summary details of these.<br><br>";

                        // Now convert to output type chosen. Remember there must be one output per file name !
                        if ($n["output_type"] == OT_CSV) {
                            // get filename. This notification does not generate a separate file per user so do this outside recipient loop
                            $fileName = $bkUpFolder . $bIDAO->getNotificationFilename(NT_EDIFILEDEF, "C", $n["principal_uid"], $enforceMS = true);

                            $myDataArr = array_merge(array(array("Incoming FileName" => "Incoming FileName",
                                    "Created Date" => "Created Date",
                                    "Processed Date" => "Processed Date",
                                    "Client Document Number" => "Client Document Number",
                                    "Reference" => "Reference",
                                    "Deliver Name" => "Deliver Name",
                                    "Status" => "Status")) , $l);
                            $fileResult = BroadcastingUtils::getCSVData($myDataArr, "");

                            $s3Result = Storage::putObject(S3_BUCKET_NAME, $fileName, $fileResult);
                            if (!$s3Result) {
                                echo "storage error: " . $s3Result . "\n";
                                problemNotification($mfNR[0], "processNotificationUnrestricted.php storage error #2: " . $s3Result . ' --- #' . $mfNR[0]["uid"], $mfSU);
                                continue;
                            }

                            $postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, $fileName);

                        } else {
                            $text .= $reportDAO->reportSQL_arrayToHTML($l, $displayHeaderRow = true, $columnFormatString = ""); // remove the header put there for CSV
                        }

                        $text .= "<BR><BR>*** You have been signed up to receive these business intelligence Notifications from the Kwelanga System</body></html>";
                        $postingDistributionTO->destinationAddr = $c["email_addr"];
                    }

                    // queue the distribution
                    $postingDistributionTO->body = $text;
                    $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
                    if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
                        BroadcastingUtils::sendAlertEmail("System Error", "Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$n["uid"]}.", "Y");
                        $bIResult = $postBIDAO->postNotificationRecipientResult($n["uid"], FLAG_ERRORTO_ERROR, addSlashes($dResult->description)); // this also automatically updates error count and concats the status
                        // (we have to continue processing as all users MUST get the list, and because we then update the status so can never return)
                    } else {
                        $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["EDIFD"]["sent_count"]++;
                    }
                } // end loop per file name

            }

        } // end multiple contacts loop

        // prevent buildup for when 1st contact is added - you dont want to get 1000's of PVs
        if (sizeof($mfC) == 0) {
            $uidArr[$n["notification_uid"] . "-" . $n["principal_uid"]]["EDIFD"]["sent_count"]++;
        }

    } // end recipients loop

}  // end notification loop for electronic exceptions, $n will still have the last iteration
if (!updateEDIFDSent($n, true)) return false; // update final iteration, including those with no contacts


/*******************************************************************************************************************************************
 * PROCESS NOTIFICATION : Delivery Exception
 *
 *
 *******************************************************************************************************************************************/

echo "<br>5. starting PROCESS NOTIFICATION : Delivery Day Exceptions";

//DISABLED AS NO DELIVERY CONFIRMATIONS EXIST...
//get queued document confirmations
//$seNR = $bIDAO->getQueuedSmartEvents(SE_DELIVERY_EXCEPTION, false);
$seNR = [];

if (sizeof($seNR) == 0) {
    echo "<br>No queued 'Delivery Day Exceptions' smart events found!";
} else {


    //GROUP EVENTS BY TYPE UID (Notification Recipient uid)
    $grpNotifications = array();
    foreach ($seNR as $se) {
        $grpNotifications[$se['type_uid']][] = $se;
    }


    foreach ($grpNotifications as $nrUId => $notiArr) {

        //set vars
        $atleast1RecipientPassed = false;
        $html = "";
        $seGeneral1 = "";
        $seGeneral2 = "";
        $dataArr = array();
        $docMList = array();


        //get data
        $mfNR = $bIDAO->getNotificationRecipientItem($nrUId);
        $mfSU = $administrationDAO->getSuperUsersForPrincipal($mfNR[0]['principal_uid']); // get list of SUs to send errors to


        //get document data
        foreach ($notiArr as $seRow) {
            $docMList[] = $seRow['data_uid'];
        } //build doc uid list
        $docArr = $bIDAO->getBIDeliveryExceptionsByUIdList(join(',', $docMList));


        //output area
        $dataArr[] = array("Depot", "Store Name", "Document No", "Document Type", "Reference", "Order Date", "Requested Delivery Date", "Due Delivery Date", "Reason");
        $principalName = '';

        foreach ($docArr as $doc) {

            $dataArr[] = array("Depot" => $doc["depot_name"],
                "Store Name" => $doc["deliver_name"],
                "Document No" => $doc["document_number"],
                "Document Type" => $doc["document_type"],
                "Reference" => $doc["customer_order_number"],
                "Order Date" => $doc["order_date"],
                "Requested Delivery Date" => $doc["requested_delivery_date"],
                "Due Delivery Date" => $doc["due_delivery_date"],
                "Reason" => $doc["comment"],
                "View Online Link" => HOST_SURESERVER_AS_USER . "system/kwelanga_system/r/?di={$encryption->encryptUIDValue($doc["uid"],0,6)}"
            );

            $principalName = $doc['principal_name'];

        } //end of document loop -

        $recipients = explode(",", $mfNR[0]["user_uid_list"]); // if blank, sizeof() will still be 1

        /*--------------------------------------------*/
        /*
         *  change : onyx
         *  dated : 2013/05/14
         *  description : distribution source identifier was a rand int,
         *                this is not safe for linking, a sequence key has therefore
         *                been created for across the system for linking purposes.
         */

        include_once($ROOT . $PHPFOLDER . "TO/SequenceTO.php");
        include_once($ROOT . $PHPFOLDER . "DAO/SequenceDAO.php");
        $getSequenceResult = CommonUtils::getRandomInteger(); //preset incase of failure/error with actual key.
        $sequenceTO = new SequenceTO();
        $sequenceTO->sequenceKey = "SOURCEID";
        $sequenceTO->sequenceStart = 0;
        $sequenceTO->sequenceLen = 6;
        $sequenceDAO = new SequenceDAO($dbConn);
        $seqResult = $sequenceDAO->getSequence($sequenceTO, $getSequenceResult);

        /*--------------------------------------------*/


        // linked to created distributions for retrieval on query screen
        $seGeneral2 = $distributionSourceIdentifier = $getSequenceResult;


        // update the distribution uid to link any distributions created later for screen lookups
        $bIResult = $postBIDAO->postNotificationRecipientDistribution($mfNR[0]["uid"], $distributionSourceIdentifier); // identifier set at start. important to update it regardless of successfully queueing distribution as you don't want it to point to old entry
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
            $bIResult = $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, "Could not set the distribution id passback."); // this also automatically updates error count and concats the status
            continue;
        }

        if (!in_array($mfNR[0]["delivery_type"], array(BT_EMAIL))) {
            $bIResult = $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, "Only EMAIL output type is supported!"); // this also automatically updates error count and concats the status
            continue;
        }

        //distribution.
        $postingDistributionTO = new PostingDistributionTO;
        $postingDistributionTO->DMLType = "INSERT";
        $postingDistributionTO->deliveryType = $mfNR[0]["delivery_type"];
        $postingDistributionTO->sourceIdentifier = $distributionSourceIdentifier;
        $subject = ($mfNR[0]["delivery_type"] == BT_EMAIL) ? ("Delivery Day Exception(s) - {$principalName}") : ("");
        $postingDistributionTO->subject = $subject;


        // set the output type
        if ($mfNR[0]["output_type"] == OT_CSV) {

            // get filename. This notification does not generate a separate file per user so do this outside recipient loop
            $fileName = $bkUpFolder . $bIDAO->getNotificationFilename(NT_DELIVERY_EXCEPTION, "C", $mfNR[0]["principal_uid"]);
            $fileResult = BroadcastingUtils::getCSVData($dataArr, "");

            $s3Result = Storage::putObject(S3_BUCKET_NAME, $fileName, $fileResult);
            if (!$s3Result) {
                echo "storage error: " . $s3Result . "\n";
                problemNotification($mfNR[0], "processNotificationUnrestricted.php storage error #3: " . $s3Result . ' --- #' . $mfNR[0]["uid"], $mfSU);
                continue;
            }

            $postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, $fileName);

        } elseif ($mfNR[0]["output_type"] == OT_HTML) {

            $html = $reportDAO->reportSQL_arrayToHTML(array_slice($dataArr, 1), $displayHeaderRow = true); // remove the header put there for CSV

        } else {
            $bIResult = $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, "Unknown Output type specify!"); // this also automatically updates error count and concats the status
            continue;
        }


        $postBIDAO->postNotificationRecipientStart($mfNR[0]["uid"]);  // set the run_date for this processing run


        //loop through setup recipients
        foreach ($recipients as $r) {


            // validate the recipients (contacts) - there should only be one for time being !
            $mfC = $miscellaneousDAO->getContactItem($mfNR[0]["principal_uid"], "", $r);
            if (sizeof($mfC) == 0) {
                problemNotification($mfNR[0], "Invalid Recipient/Contact UId ({$r})", $mfSU);
                continue;
            }

            // can have multiple of same contact
            foreach ($mfC as $c) {

                if (in_array($mfNR[0]["delivery_type"], array(BT_EMAIL))) {
                    if (validateDestinationAddr($mfNR[0], $c) !== true) continue; // validate the destination address of the recipient and email SU's if problem
                }

                // ALL GOOD, so process the recipients

                if ($mfNR[0]["output_type"] == OT_CSV) {

                    $text = "Dear {$c["name"]},<BR><BR>There are several delivery exceptions for {$principalName}, <br>these are orders where the due delivery date is on a non-delivery day at the Depot/Warehouse.<BR><BR>Please find attached a CSV file containing the details of these.<br><br>" .
                        "<U>Instructions</U><br>" .
                        "1.Open the attachment by double clicking it<br>" .
                        "2.The last column contains the link to view the document properly formatted online. If the link is not clickable directly, then simply click on the cell with the link, press F2 and then ENTER. The link will be converted to a clickable link. Clicking on it will bring the online document up in your browser.<br>" .
                        "3.When the document appears, click on the view-as-invoice-PDF at top to convert the document into a proper encrypted invoice which can then be saved on your computer and emailed accordingly." .
                        "<BR><BR>*** You have been signed up to receive these business intelligence Notifications from the Kwelanga System";

                } else {

                    $text = "Dear {$c["name"]},<BR><BR>There are several delivery exceptions for {$principalName}, <br>these are orders where the due delivery date is on a non-delivery day at the Depot/Warehouse.<BR>Please advise the Depot/Warehouse what to do with these orders.<BR><BR>Please find below a list of these.<br><br>" .
                        $html .
                        "<BR><BR>*** You have been signed up to receive these business intelligence Notifications from the Kwelanga System";

                }


                // queue the distribution
                if ($mfNR[0]["delivery_type"] == BT_EMAIL) {
                    $postingDistributionTO->body = $text;
                    $postingDistributionTO->destinationAddr = $c["email_addr"];
                }

                $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);

                if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("System Error", "Could not queue Distribution type {$postingDistributionTO->deliveryType} for UID {$mfNR[0]["uid"]}.", "Y");
                    $bIResult = $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, addSlashes($dResult->description)); // this also automatically updates error count and concats the status
                    // (we have to continue processing as all users MUST get the list, and because we then update the status so can never return)
                } else {
                    $atleast1RecipientPassed = true;
                }

            } // end multiple contacts loop

        } // end recipients loop


        if ($atleast1RecipientPassed) {

            //update smart events
            foreach ($notiArr as $se) {

                $bIResult = $postBIDAO->setSmartEventStatus($se['uid'], $se['general_reference_1'], $seGeneral2); //set smart event items where
                if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("Error in processNotificationUnrestricted", "error setting setSmartEventStatus " . $bIResult->description, "Y", false);
                    $postBIDAO->postNotificationRecipientResult($mfNR[0]["uid"], FLAG_ERRORTO_ERROR, addSlashes($bIResult->description)); // this also automatically updates error count and concats the status
                    echo $bIResult->description . "<BR>";
                    break;
                }

            }

        }

        //commit all => smart_event, distribution & notification
        $dbConn->dbinsQuery("commit;"); // commit all recipients (or until 1st error), including any error Results backupdated


    }

    $dbConn->dbinsQuery("commit;"); //catch notifications results

}


echo "<br>Successfully Generated Unrestricted Notifications for Distribution";

echo "<br>Job Ended :" . CommonUtils::getGMTime(0) . "<BR>[***EOS***]";
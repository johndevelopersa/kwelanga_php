<?php

/*------------------------------------------------------------------------------
 * EMAIL LOG (table: email_log) importer and matcher for email_file_mappings
 *------------------------------------------------------------------------------*/

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . "properties/Constants.php");
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . "libs/BroadcastingUtils.php");
include_once($ROOT . $PHPFOLDER . 'DAO/ImportDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostImportDAO.php');
require_once($ROOT . $PHPFOLDER . 'libs/storage/Storage.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingDistributionTO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostDistributionDAO.php');

set_time_limit(5 * 60);

$processRecordLimit = $_GET["limit"] ?? 10;

/*---------------------------------------------------------------
 * OUTPUT STARTS
 *---------------------------------------------------------------*/

echo "fetching queued entries from 'email_log'....\n";
echo "\tlimited to {$processRecordLimit}\n";

$storage = new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_MAIL_ENDPOINT, S3_MAIL_REGION);

$dbConn = new dbConnect();
$dbConn->dbConnection();
$emailArr = (new ImportDAO($dbConn))->getEmailLogByStatus(FLAG_STATUS_QUEUED, $processRecordLimit);

if (count($emailArr)) {

    echo 'Processing ' . count($emailArr) . " queued emails\n";

    // get mappings
    $emailFileMappingArr = (new ImportDAO($dbConn))->getAllEmailFileMappings(); // set to supercede onlineImportArr in time ...

    //PARSE EML FILES
    foreach ($emailArr as $email) {

        $emailUID = $email['uid'];
        $toAddress = strtolower($email['to']);
        $fromAddress = $email['from'];
        $subject = $email['subject'];
        $body = $email['body'];

        echo "Email: #{$emailUID} --- TO: {$toAddress} --- FROM: {$fromAddress} --- {$subject}\n";

        if (empty($email['attachments']) || !count($email['attachments'])) {

            echo "\t contains no attachments\n";

            //email client has sent an email with no attachments
            if (isOrderEmail($toAddress)) {
                $body = file_get_contents($ROOT . $PHPFOLDER . 'email_templates/inbound_email_no_attachment.html');
                $body = str_replace('{{REFERENCE}}', $emailUID, $body);

                $emailTO = new PostingDistributionTO();
                $emailTO->DMLType = "INSERT";
                $emailTO->deliveryType = BT_EMAIL;
                $emailTO->subject = 'RE: ' . $subject . ' *** Automated Reply ***';
                $emailTO->body = $body;
                $emailTO->setAttachmentFileAsS3Uri(S3_MAIL_BUCKET_NAME, $email['uri']);
                $emailTO->destinationAddr = $fromAddress;
                $result = (new PostDistributionDAO($dbConn))->postQueueDistribution($emailTO);
                if ($result->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail("System Error: " . __FILE__, 'Could not queue Distribution: ' . $result->description, "Y");
                }
            }

            //mark as completed
            (new PostImportDAO($dbConn))->updateEmailLogEntry($emailUID, FLAG_STATUS_CLOSED, 0);

            //next email
            continue;
        }

        //MATCH ANY ATTACHMENTS WITHIN EMAIL.
        $matchOnlineArr = false;
        $saveAttachArr = [];
        $filenames = [];

        foreach ($email['attachments'] as $attach) {

            if (!isset($attach['filename']) || !isset($attach['uri'])) {
                echo "error invalid attachment\n";
                continue;
            }

            $attachUID = $attach['uid'];
            $filename = $attach['filename'];
            $attachURI = $attach['uri'];
            $filenames[] = $filename;

            echo "\t attachment: #{$attachUID} \"{$filename}\"<br>";

            foreach ($emailFileMappingArr as $mapArr) {

                //we are trusting that the file match is the same UID if multiple files are sent - ie:cross matching masks within single email.
                if (!empty($mapArr['file_wildcard']) && fnmatch($mapArr['file_wildcard'], $filename, FNM_CASEFOLD)) {

                    // to address matches
                    if ($toAddress == trim($mapArr['to_address'])

                        && fromEmailMatchesList($fromAddress, $mapArr['email_domain_list'])
                    ) {

                        $matchOnlineArr = $mapArr;  //set for email matching
                        $saveAttachArr[$attach['uid']] = $attach;  //build multiple file saving array - removes any JUNK attachments.

                    } else {
                        echo "[WARN] Email to address mismatch, expected: {$mapArr['to_address']} got: {$toAddress}\n";
                    }
                }
            }
        }

        if (!count($saveAttachArr)) {

            //Has attachments BUT - 0 FILE matches found (SKIPS EMAIL) - FORWARD TO SUPPORT
            echo "No attachments matched\n";

            if (isOrderEmail($toAddress)) {

                //respond as unknown email to ADMIN
                $body = file_get_contents($ROOT . $PHPFOLDER . 'email_templates/inbound_email_nomatch_admin.html');
                $body = str_replace('{{UID}}', $emailUID, $body);
                $body = str_replace('{{FROM_ADDRESS}}', htmlentities($fromAddress), $body);
                $body = str_replace('{{TO_ADDRESS}}', htmlentities($toAddress), $body);
                $body = str_replace('{{SUBJECT}}', htmlentities($subject), $body);

                $fileArr = [];
                foreach ($email['attachments'] as $attach) {
                    $fileArr[] = $attach['filename'];
                }
                $body = str_replace('{{FILES}}', htmlentities(join(', ', $fileArr)), $body);

                $emailTO = new PostingDistributionTO();
                $emailTO->DMLType = 'INSERT';
                $emailTO->deliveryType = BT_EMAIL;
                $emailTO->subject = '[INBOUND_MAIL] Unknown Mail:' . $subject;
                $emailTO->body = $body;
                $emailTO->setAttachmentFileAsS3Uri(S3_MAIL_BUCKET_NAME, $email['uri']);
                $emailTO->destinationAddr = SUPPORT_EMERGENCY_EMAIL_CRITICAL;
                $result = (new PostDistributionDAO($dbConn))->postQueueDistribution($emailTO);
                if ($result->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail('System Error: ' . __FILE__, 'Could not queue Distribution: ' . $result->description, "Y");
                }

                $emailTO->destinationAddr = SUPPORT_EMERGENCY_EMAIL_BKUP;
                $result = (new PostDistributionDAO($dbConn))->postQueueDistribution($emailTO);
                if ($result->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail('System Error: ' . __FILE__, 'Could not queue Distribution: ' . $result->description, "Y");
                }

            } else {

                //TODO: add a metric here and alert when it escalates (user is retrying)

                //respond as unknown email to SENDER (pods only)
                $body = file_get_contents($ROOT . $PHPFOLDER . 'email_templates/inbound_email_nomatch_client.html');
                $body = str_replace('{{REFERENCE}}', $emailUID, $body);

                $emailTO = new PostingDistributionTO();
                $emailTO->DMLType = 'INSERT';
                $emailTO->deliveryType = BT_EMAIL;
                $emailTO->subject = 'RE: ' . $subject . ' *** Automated Reply ***';
                $emailTO->body = $body;
                $emailTO->destinationAddr = $fromAddress;
                $emailTO->setAttachmentFileAsS3Uri(S3_MAIL_BUCKET_NAME, $email['uri']);
                $result = (new PostDistributionDAO($dbConn))->postQueueDistribution($emailTO);
                if ($result->type != FLAG_ERRORTO_SUCCESS) {
                    BroadcastingUtils::sendAlertEmail('System Error: ' . __FILE__, 'Could not queue Distribution: ' . $result->description, "Y");
                }
            }

            //mark email as completed
            (new PostImportDAO($dbConn))->updateEmailLogEntry($emailUID, FLAG_STATUS_CLOSED, 0);

            //next email
            continue;
        }

        // [X] VALID EMAIL
        // [X] VALID ATTACHMENT

        // process attachments.
        echo "\t matched email and attachment(s)... \t\t\t OK\n";

        // if the file_path is blank then that means we just want to discard the file and bypass generating the error email
        if (trim($matchOnlineArr['file_path']) == "") {
            echo "Bypassed attachment (file marked to be discarded) {$fromAddress}";
        }

        $saveFolder = $matchOnlineArr['root_dir_constant'] . $matchOnlineArr['file_path'];

        //check if saving folder exists.
        if (!is_dir($saveFolder)) {
            $e = BroadcastingUtils::sendAlertEmail('ERROR: Inbound Email!', "\n" . 'Attachment folder does not exist: ' . $saveFolder . "\n", 'N', false);
            die('error attachment folder does not exist: ' . $saveFolder);
        }

        //send valid attachments to adaptor folder.
        foreach ($saveAttachArr as $attfile) {

            // add sequence to filename if needed, so add it before the extension if an extension exists
            $fn = $attfile['filename'];

            $fileObj = $storage::getObject(S3_MAIL_BUCKET_NAME, $attfile['uri']);
            if (!$fileObj) {
                die("ERROR downloading attachment: {$attfile['uri']}\n");
            }

            $fileSize = $file->headers['size'] ?? strlen($fileObj->body);
            $filePath = $saveFolder . $fn;
            if (is_file($filePath)) {
                echo "[WARN] File already exists '{$filePath}', appending random suffix\n";
                $posExt = strrpos($filePath, ".");
                $filePath = substr_replace($filePath, "___uid___" . rand(100, 1000000), $posExt, 0);
            }

            $f = file_put_contents($filePath, $fileObj->body);
            if ($f == $fileSize) {
                echo "\t wrote: \"{$filePath}\"\n";
            } else {
                $e = BroadcastingUtils::sendAlertEmail('ERROR: Unknown Inbound Email', "\n" . 'Attachment file could not be written - ' . $filePath . "\n", 'N', false);
                die("[ERROR] Written size mismatch when creating {$filePath}, wrote: {$f}  expected: {$fileSize}");
            }
        }
        (new PostImportDAO($dbConn))->updateEmailLogEntry($emailUID, FLAG_ERRORTO_SUCCESS, $matchOnlineArr['uid']);
    }
} else {
    echo "no records found\n";
}

$dbConn->dbinsQuery("commit;");

echo '[***EOS***]';

function isOrderEmail($toAddress): bool
{
    return $toAddress == 'orderslive@kwelangaonlinesolutions.co.za';
}

function fromEmailMatchesList($fromAddress, $domainList): bool
{

    //email list for matched file attachment
    if ($domainList == '') {  //top level catch any incoming email address
        return true;  //if email list is blank we turn email flag ON - nothing to validate against, acts as a catch all.
    }

    //parse email list into array
    $emailArr = explode(',', $domainList);
    $emailArr[] = "kwelangasolutions.co.za";    //by default always include this domain
    $fromDomain = explode('@', $fromAddress);
    $fromDomain = $fromDomain[1];

    foreach ($emailArr as $email) {  //email in online_file_pro. can be domain.co.za or info@domain.co.za both are checked from the email.
        $email = trim($email);

        if ((strtolower($fromAddress) == strtolower($email)) || (strtolower($fromDomain) == strtolower($email))) {
            //check on full email and domain
            return true;
        }
    }

    return false;
}
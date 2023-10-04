<?php


include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/FTPClass.php');
include_once($ROOT . $PHPFOLDER . 'DAO/DistributionDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostDistributionDAO.php');
include_once $ROOT . $PHPFOLDER . 'libs/storage/Storage.php';

//setup S3 storage class.
new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);


set_time_limit(240); //new dashboard does not stack jobs if a previous one hasn't finished.

//totals
$statST = microtime(true);
$statEMAILcnt = 0;
$statSMScnt = 0;
$statFTPcnt = 0;

$processType = (isset($_GET['TYPE']) && in_array($_GET['TYPE'], array('EMAIL', 'SMSFTP'))) ? $_GET['TYPE'] : false;


echo "START: " . CommonUtils::getGMTime(0) . "<BR>";


/********************************/
/*    ALLOWED RUNNING TIMES     */
/********************************/

//GMT 0 - times
$now = strtotime(gmdate("H:i"));
$notBefore = '04:00';
$notAfter = '20:00';

/********************************/


// calling program may already have set this in JobExecution
if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}

$distributionDAO = new DistributionDAO($dbConn);
$postDistributionDAO = new PostDistributionDAO($dbConn);


/********************************/
/*    PROCESS THREADING         */
/********************************/
$mfD = array();

if ($processType == false) {
    echo "ERROR - Unknown Process type!";
    return;
} elseif ($processType == 'EMAIL') {

    $deleteResult = $postDistributionDAO->deleteDistributionErrors(30);
    if ($deleteResult->type != FLAG_ERRORTO_SUCCESS) {
        echo "Error deleteDistributionErrors: {$deleteResult->description}<BR>";
    } else {
        echo "cleared distribution items with issues<BR>";
    }

    $mfD = $distributionDAO->getQueuedDistributionsByType(array(BT_EMAIL), 20);

} elseif ($processType == 'SMSFTP') {

    $mfD = $distributionDAO->getQueuedDistributionsByType(array(BT_SMS, BT_FTP), 100);
}
/********************************/


foreach ($mfD as $d) {

    $sendResult = new ErrorTO();
    $dResultTO = $postDistributionDAO->setDistributionStart($d["uid"]);
    if ($dResultTO->type != FLAG_ERRORTO_SUCCESS) {
        if (intval($d["error_cnt"]) >= 3) {
            BroadcastingUtils::sendAlertEmail("System Error", "Error occurred setting Distribution start.\n{$dResultTO->description}\n{$dResultTO->sql}", "Y");
        }
        return;
    }

    // convert sms's outside of working hours (8-4 mon-fri) to emails. Will have to make this user specific TIMEZONE in future
    $convertedToEmail = false;
    if ((
            (in_array(gmdate("w"), array(0, 6))) ||
            (gmdate("H") < (6 + 2)) ||
            (gmdate("H") > (14 + 2))
        ) &&
        ($d["delivery_type"] == BT_SMS)
    ) {
        $d["delivery_type"] = BT_EMAIL;
        $convertedToEmail = true;
    }

    $type = "Unknown";

    if ($d["delivery_type"] == BT_SMS) {


        /*--------------------
         *        SMS
         *--------------------*/

        if (($now > strtotime($notAfter)) || ($now < strtotime($notBefore))) {
            echo 'runtime out of bounds!';
            continue;
        }

        $statSMScnt++;
        $type = "SMS";

        if (!preg_match(GUI_PHP_MOBILE_REGEX, $d["destination_addr"])) {
            $dResult = $postDistributionDAO->setDistributionResult($d["uid"], addSlashes("Invalid cellphone number for uid {$d["uid"]}"), FLAG_ERRORTO_ERROR);
            continue;
        }
        $sendResult = BroadcastingUtils::sendSMS($d["body"], $d["destination_addr"]);


    } else if ($d["delivery_type"] == BT_EMAIL) {

        /*--------------------
         *        E-MAIL
         *--------------------*/
        $statEMAILcnt++;
        $type = "EMAIL";

        $fromArr = (!empty($d['from_addr'])) ? array('alias' => trim($d['from_alias']), 'addr' => trim($d['from_addr'])) : array();


        //online version linking
        include_once($ROOT . $PHPFOLDER . 'libs/EncryptionClass.php');
        $eC = new EncryptionClass();
        $dUId = $eC->encryptUIDValue($d["uid"], 2, 8);
        $online = '<center><div style="font-family:Arial,verdana,tahoma;font-size:10px;" align="center">Is this email not displaying correctly? <a href="http://' . $_SERVER['SERVER_NAME'] . '/systems/kwelanga_system/r/?e=' . $dUId . '" target="_blank">View it in your browser</a></div></center><br>';

        if (($d["destination_user_uid"] != "") && ($d["attachment_file"] == "")) {

            // use the user id instead for certain email outputs
            $sendResult = $eResult = BroadcastingUtils::sendEmailHTMLUser($d["destination_user_uid"], $dbConn, $d["body"], (($convertedToEmail) ? "(sms-->email)" : "") . $d["subject"], $fromArr, dechex($d["uid"]));

        } else if ($d["destination_addr"] != "") {

            // use the supplied raw email address directly as an array
            if (!preg_match(GUI_PHP_EMAIL_REGEX, $d["destination_addr"])) {
                $dResult = $postDistributionDAO->setDistributionResult($d["uid"], addSlashes("Invalid email address for {$d["destination_addr"]}"), FLAG_ERRORTO_ERROR);
                continue;
            }

            //EMAIL OUTPUT TYPE : HANDLING
            //csv OR html (look for a txt file for plain text)
            if (strpos(basename($d["attachment_file"]), '.html') === false) {

                echo "Sending with SMTP Server: {$d["destination_addr"]}\n";
                $fromArr = [
                    'addr' => "noreply@kwelangaonlinesolutions.co.za",
                    'alias' => "Kwelanga Solutions",
                ];

                $sourceId = dechex($d["uid"]);
                $sendResult = BroadcastingUtils::sendEmailWithAttachment($d["subject"], $online . $d["body"], $d["plain_body"], array($d["destination_addr"]), $d["attachment_file"], $fromArr, $useAltSMTP, $sourceId);

            } else {

                //HTML SENDING.
                //read data from files.

                $textFile = str_replace('.html', '.txt', $d["attachment_file"]);

                $s3FilePartsArr = parse_url($d["attachment_file"]);
                if (isset($s3FilePartsArr['scheme']) && $s3FilePartsArr['scheme'] === "s3") {

                    $bodyHtml = Storage::getObject($s3FilePartsArr['host'], $s3FilePartsArr['path']);
                    $bodyPlain = Storage::getObject($s3FilePartsArr['host'], str_replace('.html', '.txt', $s3FilePartsArr['path']));

                    if ($bodyHtml) {
                        $sendResult = BroadcastingUtils::sendEmailHTMLEmbedded($d["subject"], array($d["destination_addr"]), $online . $bodyHtml->body, $bodyPlain->body, $fromArr, dechex($d["uid"]));
                    } else {
                        echo "error: invalid/blank body html file - {$d}";
                    }

                } else if (is_file($ROOT . $d["attachment_file"]) && is_file($ROOT . $textFile)) {

                    $htmlBody = file_get_contents($ROOT . $d["attachment_file"]);
                    $plainTxt = file_get_contents($ROOT . $textFile);

                    $sendResult = BroadcastingUtils::sendEmailHTMLEmbedded($d["subject"], array($d["destination_addr"]), $online . $htmlBody, $plainTxt, $fromArr, dechex($d["uid"]));

                } else {
                    $dResult = $postDistributionDAO->setDistributionResult($d["uid"], addSlashes("Could not find all HTML/TXT files required from embedded HTML email."), FLAG_ERRORTO_ERROR);
                    continue;
                }

            }

        } else {
            $dResult = $postDistributionDAO->setDistributionResult($d["uid"], addSlashes("Uncatered for email type for uid {$d["uid"]}. Please contact Kwelanga Solutions Support"), FLAG_ERRORTO_ERROR);
            continue;
        }


    } else if ($d["delivery_type"] == BT_FTP) {


        /*--------------------
         *        FTP
         *--------------------*/
        $statFTPcnt++;
        $type = "FTP";

        if (($d["destination_addr"] == "") || ($d["attachment_file"] == "")) {

            $sendResult->description = "Invalid Attachment/FTP Server Settings. Please contact Kwelanga Solutions support";

        } else {

            //GET FTP SETTINGS - CHECK IF SET IN ARRAY.
            $FTPArr = unserialize($d["destination_addr"]);
            if (!isset($FTPArr['HOST']) || !isset($FTPArr['USR']) || !isset($FTPArr['PWD']) || !isset($FTPArr['FOLDER']) || !isset($FTPArr['PORT']) || !isset($FTPArr['MODE'])) {
                $sendResult->description = "Invalid FTP Settings in Database. Please contact Kwelanga Solutions support";
            } else {
                $ftpObj = new FTP();

                //handling of ftp name of file.
                $ftpfilename = (($d["ftp_filename"] == "") ? basename($d["attachment_file"]) : $d["ftp_filename"]);
                $sendResult = $ftpObj->sendFile($FTPArr['HOST'], $FTPArr['USR'], $FTPArr['PWD'], $FTPArr['FOLDER'], $d["attachment_file"], $ftpfilename, $FTPArr['PORT'], $FTPArr['MODE']);

            }

        }
    } else continue;


    if ($sendResult->type != FLAG_ERRORTO_SUCCESS) {
        // only send error message to RT if it is not a once-off error
        if ($d["error_cnt"] > 0) {
            BroadcastingUtils::sendAlertEmail("System Error", "Could not send {$type} to User {$d["destination_user_uid"]}/{$d["destination_addr"]}.<BR>UID: " . $d["uid"] . "<BR>ERROR CNT:" . ($d["error_cnt"] + 1) . "<BR>", "Y");
        }
        $dResult = $postDistributionDAO->setDistributionResult($d["uid"], addSlashes($sendResult->description), FLAG_STATUS_ERROR);
        if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
            echo "Error occurred setting Distribution Result.";
            BroadcastingUtils::sendAlertEmail("System Error", "Error occurred setting Distribution Result.<BR>UID: " . $d["uid"], "Y");
            return;
        }
    } else {
        $dResult = $postDistributionDAO->setDistributionResult($d["uid"], "", FLAG_STATUS_CLOSED);
        if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
            BroadcastingUtils::sendAlertEmail("System Error", "Error occurred setting Distribution Result.<BR>UID: " . $d["uid"], "Y");
        }
    }
    $dbConn->dbinsQuery("commit;");


} //eoloop


$statET = microtime(true);
$statTT = round($statET - $statST, 4);
echo "[@>>>EMAIL:" . $statEMAILcnt . ";SMS:" . $statSMScnt . ";FTP:" . $statFTPcnt . ";JOBS:" . ($statEMAILcnt + $statSMScnt + $statFTPcnt) . ";TT:" . $statTT . "@]<BR>";  //stat line.
echo "END: " . CommonUtils::getGMTime(0) . "<BR>[***EOS***]";


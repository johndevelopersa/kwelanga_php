<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'properties/ServerConstants.php');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . 'libs/SMTPClass.php');
include_once $ROOT . $PHPFOLDER . 'libs/storage/Storage.php';

//setup S3 storage class.
$S3Storage = new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);


class BroadcastingUtils
{
    // this function ONLY acceps dates without time.
    // NOTE : there seems to be a problem when inserting into MYSQL -> the date is a day behind, so don't use this until tested
    public static function sendEmailNewUser($userId, $dbConn)
    {
        global $ROOT;
        global $PHPFOLDER;
        include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');

        $errorTO = new ErrorTO;
        $administrationDAO = new AdministrationDAO($dbConn);
        $userDet = $administrationDAO->getUserItem($userId);

        if (sizeof($userDet) == 0) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "User could not be found.";
            return $errorTO;
        }

        if (!preg_match(GUI_PHP_EMAIL_REGEX, $userDet[0]['user_email'])) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Email must be of recognisable structure.";
            return $errorTO;
        };
        CommonUtils::getSystemConventions();

        //presets
        $newUserPWD = NEW_USER_PWD;

        $message = '<html>
                            <head>
                              <title>New User Created / Password has been Reset</title>
                            </head>
                            <body style="font-family:Arial,sans-serif,verdana;font-size:12px;">
                              <p>Good day ' . $userDet[0]['full_name'] . ',</p>
                              <p>
                                You have now been provided with access to the <strong>' . SNC::title . ' System</strong>, or your password has been reset.<BR>
                                <BR>
                                Please keep this email in a safe and confidential place as it contains your personal logon credentials.
                              </p>
                               <BR>
                              <p>
                                Website Address: ' . SNC::homepage . '<BR>
                                Username: <strong>' . $userDet[0]['username'] . '</strong><BR>
                                Password: <strong>' . $newUserPWD . '</strong> <i>(case sensitive)</i>
                              </p>
                               <BR>
                              <p>
                                When Logging on for the first time, you will be prompted to change your password.<BR>
                                Should you have any queries or problem logging on, please contact your SuperUser.
                              </p>
                              <p>
                                Regards,<BR>
                                The ' . SNC::title . ' Team
                              </p>
                            </body>
                            </html>';

        try {

            $mail = (new SMTPClass())->getPHPMailerSES();
            $mail->setFrom(DEFAULT_FROM_EMAIL, DEFAULT_FROM_ALIAS);
            $mail->addBCC(DEFAULT_FROM_EMAIL, DEFAULT_FROM_ALIAS);  //send copy to system!
            $mail->addAddress($userDet[0]['user_email'], $userDet[0]['username']);
            $mail->isHTML(true);
            $mail->Subject = 'New User Created / Password has been Reset';
            $mail->Body = $message;

            if ($mail->send()) {
                $errorTO->type = FLAG_ERRORTO_SUCCESS;
                $errorTO->description = "Email successfully sent to User confirming logon credentials.";
                return $errorTO;
            } else {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management";
                return $errorTO;
            }

        } catch (Exception $e) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management: {$mail->ErrorInfo} ({$e->getMessage()})";
            return $errorTO;
        }

    }


    public static function getFromAddress($arr)
    {
        $defaultAlias = DEFAULT_FROM_EMAIL;
        $defaultAddr = DEFAULT_FROM_ALIAS;
        $fromStr = $defaultAlias . ' <' . $defaultAddr . '>'; //preset to default value.

        if (is_array($arr) && count($arr) > 0) {   //alias is not required so can pass on 1 / 2 count.
            if (!empty($arr['alias']) && !empty($arr['addr'])) {  //if both set and not blank build proper alias string
                $fromStr = $arr['alias'] . ' <' . $arr['addr'] . '>';
            } else if (!empty($arr['addr'])) {  //if addr provided and not blank use only that.
                $fromStr = $arr['addr'];
            }
        }

        return $fromStr;
    }

    public static function sendEmailHTMLUser($userId, $dbConn, $htmlBody, $subjectLine, $from = [], $mailTraceId = false)
    {
        global $ROOT;
        global $PHPFOLDER;
        include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');

        $errorTO = new ErrorTO;

        $administrationDAO = new AdministrationDAO($dbConn);

        $userDet = $administrationDAO->getUserItem($userId);

        if (sizeof($userDet) == 0) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "User could not be found.";
            return $errorTO;
        }

        if (!preg_match(GUI_PHP_EMAIL_REGEX, $userDet[0]['user_email'])) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Email must be of recognisable structure.";
            return $errorTO;
        };

        $to = $userDet[0]['user_email'];  // the actual person who gets emailed. this IS seen by recipient
        $message = '
					<html>
					<head>
					  <title>' . $subjectLine . '</title>
					</head>
					<body>
					  ' . self::splitLineAfterTag($htmlBody, 500, $isHTML = true) . '
					</body>
					</html>
				';

        try {

            $mail = (new SMTPClass())->getPHPMailerSES();
            $mail->setFrom(DEFAULT_FROM_EMAIL, DEFAULT_FROM_ALIAS);
            if($mailTraceId) {
                $mail->addCustomHeader('X-Source-ID', $mailTraceId);
            }
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subjectLine;
            $mail->Body = $message;

            if ($mail->send()) {
                $errorTO->type = FLAG_ERRORTO_SUCCESS;
                $errorTO->description = "Email successfully sent to User.";
                return $errorTO;
            } else {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management";
                return $errorTO;
            }

        } catch (Exception $e) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management: {$mail->ErrorInfo} ({$e->getMessage()})";
            return $errorTO;
        }
    }

    // coverts all http:// to embedded images
    public static function sendEmailHTMLUserEmbedded($userId, $dbConn, $htmlBody, $subjectLine)
    {
        global $ROOT;
        global $PHPFOLDER;
        include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');

        $errorTO = new ErrorTO;

        $administrationDAO = new AdministrationDAO($dbConn);

        $userDet = $administrationDAO->getUserItem($userId);

        if (sizeof($userDet) == 0) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "User could not be found.";
            return $errorTO;
        }

        if (!preg_match(GUI_PHP_EMAIL_REGEX, $userDet[0]['user_email'])) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Email must be of recognisable structure.";
            return $errorTO;
        };

        $to = $userDet[0]['user_email'];  // the actual person who gets emailed. this IS seen by recipient

        try {

            $mail = (new SMTPClass())->getPHPMailerSES();

            $mail->setFrom(DEFAULT_FROM_EMAIL, DEFAULT_FROM_ALIAS);
            $mail->addAddress($to);


            //embed images...
            //this doesn't work very well!!!
            $sep = sha1(date('r', time()));

            preg_match_all("/http:\/\/.*?[.](gif|jpeg|jpg|png)/", $htmlBody, $arr); // get array of images, dont put the www in as it sometimes does not exist

            $imageTypes = [];
            $imagePaths = [];
            foreach ($arr[0] as $key => $link) {
                $link = str_replace(["src=", "'", '"'], ["", "", ""], $link);
                $htmlBody = preg_replace("/" . str_replace("/", "\/", $link) . "/", "cid:PHP-CID-" . $sep . "-" . $key, $htmlBody); // replace each link with the cid
                $tempArr = explode(".", $link);
                $imageTypes[] = $tempArr[sizeof($tempArr) - 1];
                $imagePaths[] = preg_replace("/http:\/\/.*?\//", $ROOT, $link);
            }

            // multipart/related for images

            // $body .= "--PHP-alt-{$sep}{$emailNewLine}Content-Type: multipart/related; boundary=\"PHP-related-{$sep}\"{$emailNewLine}{$emailNewLine}";
            // now dump the image data into body
            foreach ($arr[0] as $key => $img) {
                $data = file_get_contents($imagePaths[$key]);
                // $body .= "--PHP-related-{$sep}{$emailNewLine}Content-Type: image/{$imageTypes[$key]}; name=\"inline_image-{$key}.{$imageTypes[$key]}\"{$emailNewLine}Content-Transfer-Encoding: base64{$emailNewLine}Content-ID: <PHP-CID-{$sep}-{$key}>{$emailNewLine}Content-Disposition: inline; filename=\"inline_image{$key}.{$imageTypes[$key]}\"{$emailNewLine}{$emailNewLine}{$inline}{$emailNewLine}{$emailNewLine}";
                $mail->addStringAttachment($data, "PHP-CID-" . $sep . "-" . $key);
            }

            $mail->isHTML(true);
            $mail->Subject = $subjectLine;
            $mail->Body = $htmlBody;


            if ($mail->send()) {
                $errorTO->type = FLAG_ERRORTO_SUCCESS;
                $errorTO->description = "Email successfully sent to User.";
                return $errorTO;
            } else {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management";
                return $errorTO;
            }

        } catch (Exception $e) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management: {$mail->ErrorInfo} ({$e->getMessage()})";
            return $errorTO;
        }

    }

    // coverts all http:// to embedded images
    public static function sendEmailHTMLEmbedded($subjectLine, $toRecipientListArr, $htmlBody, $plainBody, $from = [], $mailTraceId = false)
    {

        global $ROOT;
        global $PHPFOLDER;

        $errorTO = new ErrorTO;

        try {

            $mail = (new SMTPClass())->getPHPMailerSES();

            $mail->setFrom(DEFAULT_FROM_EMAIL, DEFAULT_FROM_ALIAS);
            if (is_array($toRecipientListArr) && count($toRecipientListArr)) {
                foreach ($toRecipientListArr as $address) {
                    $mail->addAddress($address);
                }
            }

            if($mailTraceId) {
                $mail->addCustomHeader('X-Source-ID', $mailTraceId);
            }

            //embed images...
            //this doesn't work very well!!!
            $sep = sha1(date('r', time()));

            preg_match_all("/src=([\"'])http:\/\/.*?[.](gif|jpeg|jpg|png)/", $htmlBody, $arr); // get array of images, dont put the www in as it sometimes does not exist

            $imageTypes = [];
            $imagePaths = [];
            foreach ($arr[0] as $key => $link) {
                $link = str_replace(["src=", "'", '"'], ["", "", ""], $link);
                $htmlBody = preg_replace("/" . str_replace("/", "\/", $link) . "/", "cid:PHP-CID-" . $sep . "-" . $key, $htmlBody); // replace each link with the cid
                $tempArr = explode(".", $link);
                $imageTypes[] = $tempArr[sizeof($tempArr) - 1];
                $imagePaths[] = preg_replace("/http:\/\/.*?\//", $ROOT, $link);
            }

            // multipart/related for images

            // $body .= "--PHP-alt-{$sep}{$emailNewLine}Content-Type: multipart/related; boundary=\"PHP-related-{$sep}\"{$emailNewLine}{$emailNewLine}";
            // now dump the image data into body
            foreach ($arr[0] as $key => $img) {
                $data = file_get_contents($imagePaths[$key]);
                // $body .= "--PHP-related-{$sep}{$emailNewLine}Content-Type: image/{$imageTypes[$key]}; name=\"inline_image-{$key}.{$imageTypes[$key]}\"{$emailNewLine}Content-Transfer-Encoding: base64{$emailNewLine}Content-ID: <PHP-CID-{$sep}-{$key}>{$emailNewLine}Content-Disposition: inline; filename=\"inline_image{$key}.{$imageTypes[$key]}\"{$emailNewLine}{$emailNewLine}{$inline}{$emailNewLine}{$emailNewLine}";
                $mail->addStringAttachment($data, "PHP-CID-" . $sep . "-" . $key);
            }

            $mail->isHTML(true);
            $mail->Subject = $subjectLine;
            $mail->Body = $htmlBody;
            $mail->AltBody = $plainBody;


            if ($mail->send()) {
                $errorTO->type = FLAG_ERRORTO_SUCCESS;
                $errorTO->description = "Email successfully sent to User.";
                return $errorTO;
            } else {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management";
                return $errorTO;
            }

        } catch (Exception $e) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management: {$mail->ErrorInfo} ({$e->getMessage()})";
            return $errorTO;
        }
    }

    public static function sendEmailWithAttachment($emailSubject, $emailBody, $plainBody, $BCrecipientListArr, $fileAttachmentPath, $from = [], $useAltSMTP = false, $mailTraceId = false)
    {
        global $ROOT, $PHPFOLDER, $S3Storage;
        include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');

        $errorTO = new ErrorTO;

        try {


            $mail = (new SMTPClass())->getPHPMailerSES();
            if($useAltSMTP){
                $mail = (new SMTPClass())->getPHPMailerSES();
            }
            $mail->setFrom(DEFAULT_FROM_EMAIL, DEFAULT_FROM_ALIAS);
            if (is_array($BCrecipientListArr) && count($BCrecipientListArr)) {
                foreach ($BCrecipientListArr as $BCAddress) {
                    $mail->addBCC($BCAddress);
                }
            }
            $mail->isHTML(true);
            $mail->Subject = $emailSubject;
            $mail->Body = $emailBody;
            $mail->AltBody = $plainBody;

            //trace id: a day based, random 24 char hash
            if(!$mailTraceId){
                $mailTraceId = dechex(date("ymd")) . substr(sha1(uniqid() . rand(0,10000)),0,19);
            }
            $mail->addCustomHeader('X-Source-ID', $mailTraceId);

            //append attachments
            if ($fileAttachmentPath != "") {
                $filesArr = explode(",", $fileAttachmentPath);
                foreach ($filesArr as $f) {

                    //handle s3 stored files, eg: s3://{$bucketName}/{$fileLocation}
                    $s3FilePartsArr = parse_url($f);
                    if (isset($s3FilePartsArr['scheme']) && $s3FilePartsArr['scheme'] === "s3") {

                        //toggle region endpoint
                        if($s3FilePartsArr['host'] == S3_MAIL_BUCKET_NAME){
                            $currentRegion = Storage::getRegion();
                            $currentEndpoint = Storage::$endpoint;
                            $S3Storage->setRegion(S3_MAIL_REGION);  //set region for mail bucket
                            $S3Storage->setEndpoint(S3_MAIL_ENDPOINT);  //set endpoint for mail bucket
                            $storageFile = $S3Storage::getObject($s3FilePartsArr['host'], $s3FilePartsArr['path']);
                            $S3Storage->setRegion($currentRegion);  //set region back
                            $S3Storage->setEndpoint($currentEndpoint);  //set endpoint back
                        } else {
                            $storageFile = Storage::getObject($s3FilePartsArr['host'], $s3FilePartsArr['path']);
                        }

                        if(!$storageFile || !isset($storageFile->body)){
                            echo "error fetching s3 attachment: '{$f}'\n";;
                            continue;
                        }

                        if(!$mail->addStringAttachment($storageFile->body, basename($s3FilePartsArr['path']))){
                            echo "error adding attachment: '{$f}' from s3 storage.\n";
                        }

                    } else {

                        //file must be local right?
                        $filePath = $ROOT . $f;
                        if (!file_exists($filePath)) {
                            $filePath = $ROOT . "../" . $f;
                        }
                        if (file_exists($filePath)) {
                            $mail->addAttachment($filePath, basename($filePath));
                        }

                    }
                }
            }

            if ($mail->send()) {

                //echo "<br>trace-id: " . $mailTraceId . "<br>";

                $errorTO->identifier = $mailTraceId;
                $errorTO->type = FLAG_ERRORTO_SUCCESS;
                $errorTO->description = "Successful";
                return $errorTO;
            } else {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management";
                return $errorTO;
            }

        } catch (Exception $e) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management: {$mail->ErrorInfo} ({$e->getMessage()})";
            return $errorTO;
        }

    }

    public static function sendTextEmail($emailSubject, $emailBody, $BCrecipientListArr)
    {
        $errorTO = new ErrorTO;

        try {

            $mail = (new SMTPClass())->getPHPMailerSES();
            $mail->setFrom(DEFAULT_FROM_EMAIL, DEFAULT_FROM_ALIAS);
            if (is_array($BCrecipientListArr) && count($BCrecipientListArr)) {
                foreach ($BCrecipientListArr as $BCAddress) {
                    $mail->addBCC($BCAddress);
                }
            }
            $mail->isHTML(false);   //set to text.
            $mail->Subject = $emailSubject;
            $mail->Body = $emailBody;

            if ($mail->send()) {
                $errorTO->type = FLAG_ERRORTO_SUCCESS;
                $errorTO->description = "Email successfully sent.";
                return $errorTO;
            } else {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "A problem occurred sending the text email.";
                return $errorTO;
            }

        } catch (Exception $e) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Message could not be sent. Mailer Error: {$mail->ErrorInfo} ({$e->getMessage()})";
            return $errorTO;
        }

    }

    public static function sendHTMLEmail($emailSubject, $emailHTMLBody, $emailPlainBody, $BCrecipientListArr)
    {
        $errorTO = new ErrorTO;
        try {
            $mail = (new SMTPClass())->getPHPMailerSES();
            $mail->setFrom(DEFAULT_FROM_EMAIL, DEFAULT_FROM_ALIAS);
            $mail->addReplyTo(DEFAULT_FROM_EMAIL);
            if (is_array($BCrecipientListArr) && count($BCrecipientListArr)) {
                foreach ($BCrecipientListArr as $BCAddress) {
                    $mail->addBCC($BCAddress);
                }
            }
            $mail->isHTML(true);
            $mail->Subject = $emailSubject;
            $mail->AltBody = $emailPlainBody;
            $mail->Body = $emailHTMLBody;

            if ($mail->send()) {
                $errorTO->type = FLAG_ERRORTO_SUCCESS;
                $errorTO->description = "Email successfully sent to User.";
                return $errorTO;
            } else {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "A problem occurred sending the text email.";
                return $errorTO;
            }

        } catch (Exception $e) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "A problem occurred sending the new user email. {$mail->ErrorInfo} ({$e->getMessage()})";
            return $errorTO;
        }
    }


    public static function emailResetPasswordLINK($userId, $dbConn)
    {

        global $ROOT;
        global $PHPFOLDER;

        include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
        include_once($ROOT . $PHPFOLDER . 'libs/EncryptionClass.php');

        $errorTO = new ErrorTO;

        $administrationDAO = new AdministrationDAO($dbConn);
        $userArr = $administrationDAO->getUserItem($userId);

        if (sizeof($userArr) == 0) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "User could not be found.";
            return $errorTO;
        }

        if (!preg_match(GUI_PHP_EMAIL_REGEX, $userArr[0]['user_email'])) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Email must be of recognisable structure.";
            return $errorTO;
        };


        //--------------------------------------------------------
        //
        //  BASICALLY WE BUILD A STRING CONTAINING : USERID, TIMESTAMP AND IP ADDRESS
        //  PROCEED TO ENCODE THE STRING USING THE USERNAME AS THE KEY - THIS ENCRYPTED STRING FORMS PART OF THE RESET URL
        //
        //  WHEN THE LINK IS ACCESSED THE USER WILL BE REQUESTED TO INPUT THEIR USERNAME A 2ND TIME TO DECODE THE RESET URL - REMOVES ANY EMAIL TAMPERING
        //  THE CODE WILL CHECK IF THE USERID STRING IS FOUND - = SUCCESSFUL DECRYPT AND PROCEED TO USE THE USERID VALUE AND RESET THE PASSWORD.
        //
        //--------------------------------------------------------

        $pwdResetString = 'USERID:' . $userId . ':' . strtotime(date('Y-m-d H:i:s')) . ':' . getenv('REMOTE_ADDR');
        $username = $userArr[0]['username'];
        $pwdResetStringCrypt = openssl_encrypt($pwdResetString, 'rc4', $username, false);

        $resetURL = 'https://www.kwelangasolutions.co.za/index.php?pg=password&pstr=' . urlencode($pwdResetStringCrypt);

        $to = $userArr[0]['user_email'];
        $toAlias = $userArr[0]['full_name'];
        $subject = 'Password Reset Link';
        $message = '<html>
					<head>
					  <title>Password Reset Link</title>
					</head>
					<body>
					  <p>Good day ' . $userArr[0]['full_name'] . ',</p>
					  <p>You have requested for your password to be reset.</p><BR>
					  <p>Please follow the below link in your browser to proceed to the next Step:</p><BR>
					  <a href="' . $resetURL . '">' . $resetURL . '</a>
					  <BR>
					  <p>If the above link does not work, copy it and paste into your address bar in your browser.</p>
					  <BR>
					  <p>Regards,</p>
					  <p>The Kwelanga Solutions Team</p>
					</body>
					</html>
				';

        try {

            $mail = (new SMTPClass())->getPHPMailerSES();
            $mail->setFrom(DEFAULT_FROM_EMAIL, DEFAULT_FROM_ALIAS);
            $mail->addAddress($to, $toAlias);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            if ($mail->send()) {
                $errorTO->type = FLAG_ERRORTO_SUCCESS;
                $errorTO->description = "Email successfully sent.";
                return $errorTO;
            } else {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management";
                return $errorTO;
            }

        } catch (Exception $e) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "A problem occurred sending the new user email. Please contact Kwelanga Solutions management: {$mail->ErrorInfo} ({$e->getMessage()})";
            return $errorTO;
        }

    }

    public static function sendSMS($text, $cellphone)
    {
        $errorTO = new ErrorTO;
        $msgLenLimit = 160;

        if (!preg_match(GUI_PHP_MOBILE_REGEX, $cellphone)) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Invalid Cellphone format specified.";
            return $errorTO;
        }
        $msg = "{$text}\n>> Ret.Trad.Tech <<";
        $msgLen = strlen($msg);
        if (strlen("{$text}\n>> Ret.Trad.Tech <<") > $msgLenLimit) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Message length ({$msgLen}) exceeds {$msgLenLimit} chars after linefeed(s) and footer have been added.";
            return $errorTO;
        }
        $content = "cellphone: {$cellphone}\n{$msg}";

        // do it this way so that you can send text literal as file and not have to create file first locally
        $host = "ftpsms.itouchnet.co.za"; // used to be ftpsms.co.za
        $usr = "retailtr";
        $pwd = "RHDMMVER";
        $ftpToPath = "/rttsms_" . (CommonUtils::getRandomInteger()) . ".TXT";
        $hostname = "ftp://{$usr}:{$pwd}@{$host}{$ftpToPath}";

        $opts = [
            'ftp' => [
                'overwrite' => true,
            ],
        ];
        $context = stream_context_create($opts);
        $upload = file_put_contents($hostname, $content, false, $context);

        if (!$upload) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Failed to transfer file for SMS send";
            return $errorTO;
        }

        $errorTO->type = FLAG_ERRORTO_SUCCESS;
        $errorTO->description = "Message successfully sent.";
        return $errorTO;
    }

    /* header is a text string that takes up whole of 1st row */
    public static function createEDIFile($arrValues, $header, $type, $dbConn)
    {
        global $ROOT;
        global $PHPFOLDER;
        include_once($ROOT . $PHPFOLDER . 'TO/SequenceTO.php');
        include_once($ROOT . $PHPFOLDER . 'DAO/SequenceDAO.php');
        $sequenceDAO = new SequenceDAO($dbConn);
        $sequenceTO = new SequenceTO;

        $errorTO = new ErrorTO;

        $seqVal = "000000";
        switch ($type) {
            case FILE_FTP_DOPS_FILETYPE_STORE:
            {
                $sequenceTO->sequenceKey = FILE_FTP_DOPS_FILENAME_STORE_SEQ;
                $sequenceTO->sequenceLen = FILE_FTP_DOPS_FILENAME_STORE_SEQ_LEN;
                $result = $sequenceDAO->getSequence($sequenceTO, $seqVal);
                if ($result->type != FLAG_ERRORTO_SUCCESS) {
                    return $result;
                }
                $fileName = $ROOT . FILE_FTP_DOPS_PATH . FILE_FTP_DOPS_FILENAME_FRONT_STORE . $seqVal . "." . FILE_FTP_DOPS_FILENAME_EXT_STORE;
                break;
            }
            case FILE_FTP_DOPS_FILETYPE_PRICE:
            {
                $sequenceTO->sequenceKey = FILE_FTP_DOPS_FILENAME_PRICE_SEQ;
                $sequenceTO->sequenceLen = FILE_FTP_DOPS_FILENAME_PRICE_SEQ_LEN;
                $result = $sequenceDAO->getSequence($sequenceTO, $seqVal);
                if ($result->type != FLAG_ERRORTO_SUCCESS) {
                    return $result;
                }
                $fileName = $ROOT . FILE_FTP_DOPS_PATH . FILE_FTP_DOPS_FILENAME_FRONT_PRICE . $seqVal . "." . FILE_FTP_DOPS_FILENAME_EXT_PRICE;
                break;
            }
            /* deprecated - no more WORs
                  case FILE_FTP_DOPS_FILETYPE_ORDER: {
                      $sequenceTO->sequenceKey=FILE_FTP_DOPS_FILENAME_ORDER_SEQ;
                      $sequenceTO->sequenceLen=FILE_FTP_DOPS_FILENAME_ORDER_SEQ_LEN;
                      $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
                      if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                          return $result;
                      }
                      // get the orderSequence... should be the same for each row in passed array
                      $orderSeq=$arrValues[1][4]; // it is assumed row 0 is always the headers
                      $fileName=$ROOT.FILE_FTP_DOPS_PATH.FILE_FTP_DOPS_FILENAME_FRONT_ORDER.$seqVal.".OS".$orderSeq.".".FILE_FTP_DOPS_FILENAME_EXT_ORDER;
                      break;
                  }
            */
            case FILE_FTP_DOPS_FILETYPE_PRODUCT:
            {
                $sequenceTO->sequenceKey = FILE_FTP_DOPS_FILENAME_PRODUCT_SEQ;
                $sequenceTO->sequenceLen = FILE_FTP_DOPS_FILENAME_PRODUCT_SEQ_LEN;
                $result = $sequenceDAO->getSequence($sequenceTO, $seqVal);
                if ($result->type != FLAG_ERRORTO_SUCCESS) {
                    return $result;
                }
                $fileName = $ROOT . FILE_FTP_DOPS_PATH . FILE_FTP_DOPS_FILENAME_FRONT_PRODUCT . $seqVal . "." . FILE_FTP_DOPS_FILENAME_EXT_PRODUCT;
                break;
            }
            default:
            {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "Unknown file type in create EDI file.";
                return $errorTO;
            }
        }
        $fh = @fopen($fileName, 'w');
        if ($fh) {
            foreach ($arrValues as $row) {
                $i = 0;
                if ($header != "") fwrite($fh, DELIMITER_EDI_FIELD . str_replace(DELIMITER_EDI_FIELD, "", $header) . DELIMITER_EDI_FIELD . DELIMITER_EDI_COLUMN);

                foreach ($row as $field) {
                    $writeField = DELIMITER_EDI_FIELD . str_replace(DELIMITER_EDI_FIELD, "", $field) . DELIMITER_EDI_FIELD;
                    if ($i == 0) fwrite($fh, $writeField);
                    else fwrite($fh, DELIMITER_EDI_COLUMN . $writeField);
                    $i++;
                }
                $result = fwrite($fh, "\n");
                if (!$result) {
                    $errorTO->type = FLAG_ERRORTO_ERROR;
                    $errorTO->description = "Error on attempted write of contents to EDI file.";
                    return $errorTO;
                }
            }
            // write the Line Count
            $result = fwrite($fh, "LC" . sizeof($arrValues));
            if (!$result) {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "Error on attempted write of LC contents to EDI file.";
                return $errorTO;
            }
        } else {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Could not create EDI file." . $fileName;
            return $errorTO;
        }

        $errorTO->type = FLAG_ERRORTO_SUCCESS;
        $errorTO->description = "EDI file successfully created.";
        $errorTO->identifier = $fileName;

        fclose($fh);

        return $errorTO;
    }

    // header is the text report heading, not column headers
    // if you want column headers, then add the headers in as first row of array

    public static function getCSVData($arrValues, $header): string
    {
        $fh = tmpfile();
        if (!$fh) {
            throw new Exception("Could not create temporary file");
        }

        $tmpPath = stream_get_meta_data($fh)['uri'];
        $fileResult = BroadcastingUtils::writeCSVFile($fh, $arrValues, $header);
        $fileData = file_get_contents($tmpPath);

        fclose($fh);

        if ($fileResult->type != FLAG_ERRORTO_SUCCESS) {
            throw new Exception('can not convert null string to date');
        }
        return $fileData;
    }

    public static function createCSVFile($arrValues, $header, $fullPathFN)
    {
        $errorTO = new ErrorTO;

        $fh = @fopen($fullPathFN, 'w');
        if (!$fh) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = "Could not create CSV file." . basename($fullPathFN);
            return $errorTO;
        }

        $fileResult = BroadcastingUtils::writeCSVFile($fh, $arrValues, $header);

        fclose($fh);

        if ($fileResult->type != FLAG_ERRORTO_SUCCESS) {
            return $fileResult;
        }

        $errorTO->type = FLAG_ERRORTO_SUCCESS;
        $errorTO->description = "CSV file successfully created.";

        return $errorTO;
    }

    public static function writeCSVFile($fileHandle, $arrValues, $header): ErrorTO
    {
        $errorTO = new ErrorTO;

        foreach ($arrValues as $row) {
            $i = 0;
            if ($header != ""){
                fwrite($fileHandle, DELIMITER_EDI_FIELD . str_replace(DELIMITER_EDI_FIELD, "", $header) . DELIMITER_EDI_FIELD . DELIMITER_EDI_COLUMN);
            }

            foreach ($row as $field) {
                $writeField = DELIMITER_EDI_FIELD . str_replace(DELIMITER_EDI_FIELD, "", $field) . DELIMITER_EDI_FIELD;
                if ($i == 0) {
                    fwrite($fileHandle, $writeField);
                } else {
                    fwrite($fileHandle, DELIMITER_EDI_COLUMN . $writeField);
                }
                $i++;
            }
            $result = fwrite($fileHandle, "\n");
            if (!$result) {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = "Error on attempted write of contents to EDI file.";
                return $errorTO;
            }
        }

        $errorTO->type = FLAG_ERRORTO_SUCCESS;
        $errorTO->description = "CSV file successfully created.";

        return $errorTO;
    }

    // this sends immediately, no queueing due to possibility of distribution being down
    // do not allow sms between 7pm GMT and 04am GMT
    public static function sendAlert($bodyMsg, $outputEcho)
    {
        $hr = gmdate("H");
        if (($hr >= 19) || ($hr < 4)) {
            // convert to email
            $sResultTO = self::sendAlertEmail("System Error", $bodyMsg, "Y", false);
            if ($sResultTO->type != FLAG_ERRORTO_SUCCESS) echo "Failed to send FAILURE Sms - Error Converting to Email";
        } else {
            $sResultTO = self::sendSMS($bodyMsg, SUPPORT_EMERGENCY_CELLPHONE_CRITICAL);
            if ($sResultTO->type != FLAG_ERRORTO_SUCCESS) echo "Failed to send FAILURE Sms";
            $sResultTO = self::sendSMS($bodyMsg, SUPPORT_EMERGENCY_CELLPHONE_BKUP);
            if ($sResultTO->type != FLAG_ERRORTO_SUCCESS) echo "Failed to send FAILURE Sms";
            if ($outputEcho == "Y") echo $bodyMsg;
        }
    }

    // this sends immediately, no queueing due to possibility of distribution being down
    public static function sendAlertEmail($subject, $body, $outputEcho, $quietMode = false)
    {
        global $ROOT;
        global $PHPFOLDER;

        $resultTO = self::sendTextEmail($subject, $body, [SUPPORT_EMERGENCY_EMAIL_CRITICAL, SUPPORT_EMERGENCY_EMAIL_BKUP]);
        if ($quietMode == false) {
            if ($resultTO->type != FLAG_ERRORTO_SUCCESS) {
                echo "Failed to send FAILURE Email - " . $resultTO->description;
                echo "<p>Subject: {$subject}</p>";
                echo "<p>Body: {$body}</p>";
                echo "<p>outputEcho: {$outputEcho}</p>";
            }
            if ($outputEcho == "Y") echo "\n<BR>" . $body . "\n<BR>";
        }
        return $resultTO;
    }

    // this sends immediately, no queueing due to possibility of distribution being down
    public static function sendSupportEmail($subject, $body, $outputEcho, $quietMode = false)
    {
        global $ROOT;
        global $PHPFOLDER;

        $resultTO = self::sendTextEmail($subject, $body, [SUPPORT_EMAIL]);
        if ($quietMode == false) {
            if ($resultTO->type != FLAG_ERRORTO_SUCCESS) {
                echo "Failed to send FAILURE Email - " . $resultTO->description;
                echo "<p>Subject: {$subject}</p>";
                echo "<p>Body: {$body}</p>";
                echo "<p>outputEcho: {$outputEcho}</p>";
            }
            if ($outputEcho == "Y") echo "\n<BR>" . $body . "\n<BR>";
        }
        return $resultTO;
    }


    // the TCPDF needs very tidy conformant HTML otherwise strange results occur
    public static function sanitizePDFHTML($html)
    {
        // make it pdf compatible

        file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/printh.txt', "I am HERE");
        file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/printa.txt', $html);

        $tHTML = str_replace("'", '"', $html);


        $tHTML = preg_replace("/<A.*?>.*?<\/A>/si", "", $tHTML); // strip out links
        file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/printb.txt', $tHTML);

        $tHTML = preg_replace("/<p.*?>[ ]*?<\/p>/si", "", $tHTML); // take out empty p tags as a result of removing links above
        $tHTML = preg_replace("/border-style:[\"]{0,1}none[\"]{0,1}[;]{0,1}/i", "", $tHTML); // style none puts a line at top so remove it
        $tHTML = preg_replace("/<TABLE/i", "<BR><TABLE", $tHTML); // add a break before any tables


        $tHTML = preg_replace("/<script.*?<\/script>/si", "", $tHTML); // strip out javascript, ignore case, span multilines if there is a line break between start and end tag
        file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/printmi1.txt', $tHTML);


//		$tHTML=preg_replace("/<[\/]{0,1}[a-zA-Z]+?([ ]|[>])/sie","strtolower('$0')",$tHTML); // convert all tags to lowercase otherwise each row becomes indented in tables
        file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/printmi.txt', $tHTML);


        $tHTML = preg_replace("/<fieldset.*?>/si", "<div style=\"padding:10; background-color:#F4F5F5;\">", $tHTML); // Fieldset tags are not compatible. Convert them to Divs. Can be ignored too, but i want to shade them.
        $tHTML = preg_replace("/<\/fieldset>/si", "</div>", $tHTML); // Fieldset tags are not compatible. Convert them to Divs. Can be ignored too, but i want to shade them.

        file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/printbe.txt', $tHTML);

        return $tHTML;
    }

    public static function convertHTMLToPDF($html, $title, $subject, $keywords = "")
    {
        global $ROOT;
        global $PHPFOLDER;

        require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/config/lang/eng.php');
        require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/tcpdf.php');

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetProtection($permissions = ['modify', 'copy'], $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('KwelangaSolutions c/o Alan');
        $pdf->SetTitle($title);
        $pdf->SetSubject($subject);
        $pdf->SetKeywords($keywords);

        // set JPEG quality
        $pdf->setJPEGQuality(75);
        $pdf->setPrintHeader(false);
        //$pdf->SetHeaderData("retailtrading_logo_g.jpg", 13, "Kwelanga Solutions", "End to end supply chain solutions");

        // set header and footer fonts
        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', 12]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf->SetMargins(8, 5, 8);
        $pdf->SetHeaderMargin(3);
        $pdf->SetFooterMargin(8);

        //set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $pdf->setImageScale(1.5);

        //set some language-dependent strings
        $pdf->setLanguageArray($l);

        // CONTENT -------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // $sHTML=self::sanitizePDFHTML($html);

        // Image example with resizing
        //$pdf->setJPEGQuality(75);

//  $pdf->Image($ROOT.$PHPFOLDER.'libs/pdf/tcpdf/images/signature_logo.png', '', 3, 15, 15, 'PNG', 'https://kwelangasonlinesolutions.co.za', '', true, 300, 'R', false, false, 0, false, false, false);

        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = false);

        // page end is normally about 265
        $posY = $pdf->getY();
        // If there is a security error like "You do not have access to this information, or document master does not exist" then an error occurs if you execute this block
        // You should really check that the content is tagged html instead
        if (strlen($html) > 90) {
            if ($posY < 15) {
                $pagePos = $pdf->getPage();
                $pdf->setPage($pagePos - 1);
                $posY = 259;
            } else {
                $posY -= 8;
            }
        }

//		$pdf->Image($ROOT.$PHPFOLDER.'libs/pdf/tcpdf/images/poweredByLogo.png', '', $posY, 40, '', 'PNG', 'http://www.kwelangasolutions.co.za', '', true, 300, 'R', false, false, 0, false, false, false);

        return $pdf->Output('D.pdf', 'S');
    }

    public static function outputPDFWithHeaders($filename, $pdfString)
    {
        header('Content-Type: application/pdf');
        if (headers_sent()) {
            echo "Some data has already been output to browser, can\'t send PDF file";
            return false;
        }
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: inline; filename="' . $filename . '";');
        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) or empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            // the content length may vary if the server is using compression
            header('Content-Length: ' . strlen($pdfString));
        }
        echo $pdfString;

        return true;
    }

    // Splits a line, but if html tags are present, then make sure only split after ">" for opening / closing tag
    // Makes each line end in \r\n to a set length for emailer MIME format
    public static function splitLineAfterTag($str, $lineLen, $isHTML = true)
    {

        $strLen = strlen($str);
        $retStr = "";
        $hasOpener = false;
        $pos = 0;
        $rememberPos = false;
        for ($i = 0; $i < $strLen; $i++) {
            // skip unsplittable chars, they act as splitters anyway - can cause problems if this is an actual value within a tag
            if (in_array($str[$i], ["\x0D", "\x0A"])) {
                $retStr .= $str[$i];
                $hasOpener = false;
                $rememberPos = false;
                $pos = 0;
                continue;
            }

            // assumes there won't be nested tages within other braces eg <xxx<y>>'
            if ($str[$i] == "<") {
                $hasOpener = true;
            }
            // remember the last position of the brekable position (before line limit) for breaking html tags later
            if (($hasOpener) &&
                (in_array($str[$i], [" ", "'", '"', ";", ">"])) &&
                ($isHTML) &&
                (($rememberPos === false) || ($pos < ($lineLen - 1)))
            ) {
                $rememberPos = $i;
            }

            // if encountered closing html tag before line limit, then reset
            if (($hasOpener) &&
                ($str[$i] == ">") &&
                ($pos <= ($lineLen - 1))
            ) {
                $hasOpener = false;
                $rememberPos = false;
            }

            if ($pos >= ($lineLen - 1)) {
                if (
                    (($hasOpener) && ($str[$i] == ">")) ||
                    (!$isHTML) ||
                    (!$hasOpener)
                ) {
                    $retStr .= $str[$i] . "\r\n";
                    $hasOpener = false;
                    $rememberPos = false;
                    $pos = 0;
                    continue;
                } else if (($hasOpener) && ($rememberPos !== false)) {
                    // restore to remembered position
                    $retStr .= $str[$i];
                    $retStr = substr($retStr, 0, strlen($retStr) - ($i - $rememberPos)) . "\r\n"; // take off the stored values back to the remembered position
                    //$hasOpener=false; // continue to split this tag
                    $i = $rememberPos;
                    $rememberPos = false;
                    $pos = 0;
                    continue;
                }

            }

            $retStr .= $str[$i];
            $pos++;

        }

        return $retStr;

    }

}

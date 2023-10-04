<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once 'ROOT.php';
require_once $ROOT . $PHPFOLDER . 'libs/phpmailer/Exception.php';
require_once $ROOT . $PHPFOLDER . 'libs/phpmailer/PHPMailer.php';
require_once $ROOT . $PHPFOLDER . 'libs/phpmailer/SMTP.php';
require_once $ROOT . $PHPFOLDER . 'properties/Constants.php';


class SMTPClass
{

    const DEBUG = false;

    public function getPHPMailer(): PHPMailer
    {
        try {
            $mail = new PHPMailer(true);

            //constants are from ServerConstants.php
            $mail->isSMTP();                                            // Send using SMTP
            $mail->SMTPKeepAlive = true;
            $mail->Host = SMTP_HOST;                    // Set the SMTP server to send through
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;                                   // Enable SMTP authentication
            $mail->Username = SMTP_USERNAME;                     // SMTP username
            $mail->Password = SMTP_PASSWORD;                               // SMTP password
            //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $mail->Port = SMTP_PORT;                                    // TCP port to connect to
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => SMTP_SSL_VERIFY,
                    'verify_peer_name' => SMTP_SSL_VERIFY,
                    'allow_self_signed' => true,
                ],
            ];

			$mail->XMailer = ' ';
			$mail->SMTPDebug = false;
            if (SMTPClass::DEBUG) {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            }
            //$mail->SMTPDebug = 4;

            return $mail;

        } catch (Exception $e) {
            echo "SMTPClass: mailer Error: {$e->errorMessage()}";
            die();
        }
    }

    public function getPHPMailerSES(): PHPMailer
    {
        try {
            $mail = new PHPMailer(true);

            //constants are from ServerConstants.php
            $mail->isSMTP();                                            // Send using SMTP
            $mail->SMTPKeepAlive = true;
            $mail->Host = SES_SMTP_HOST;                    // Set the SMTP server to send through
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;                                   // Enable SMTP authentication
            $mail->Username = SES_SMTP_USERNAME;                     // SMTP username
            $mail->Password = SES_SMTP_PASSWORD;                               // SMTP password
            $mail->Port = SES_SMTP_PORT;                                     // TCP port to connect to
			# $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => SES_SMTP_SSL_VERIFY,
                    'verify_peer_name' => SES_SMTP_SSL_VERIFY,
                    'allow_self_signed' => true,
                ],
            ];

			$mail->XMailer = ' ';
			$mail->SMTPDebug = false;
            if (SMTPClass::DEBUG) {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            }
            //$mail->SMTPDebug = 4;

            return $mail;

        } catch (Exception $e) {
            echo "SMTPClass: mailer Error: {$e->errorMessage()}";
            die();
        }
    }
}

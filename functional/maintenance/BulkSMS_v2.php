<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class BulkSMS_v2
{

  private static $basicAuth = "Njc4RDFGNTVBNzJGNEM5OEFGMkQ2MkE2ODM2MUY4RUEtMDEtMTpIZzhIS3RwbmxZTm1ZT0I0Tmg4MG9NSVhnc0E2cA==";
  private static $endPoint = "https://api.bulksms.com/v1/messages?auto-unicode=true&longMessageMaxParts=30";

  public static function sendSMS ( $recipientNo, $msg, $userUId) {
    global $ROOT, $PHPFOLDER;

    $rTO = new ErrorTO();
    $mobileNo = preg_replace("/[^0-9]/", "", $recipientNo);
    $mobileNo = "+27" . substr($mobileNo, -9);
    if (!preg_match("/^[+]27[0-9]{9}/", $mobileNo)) {
      $rTO->type = FLAG_ERRORTO_ERROR;
      $rTO->description = "Incorrect resultant format of mobile number being : " . $mobileNo;
      return $rTO;
    }

    $messages = [
      ['to'=>$mobileNo, 'body'=>$msg]
    ];

    $ch = curl_init( );
    $headers = array(
      'Content-Type:application/json',
      'Authorization:Basic '. self::$basicAuth
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt ( $ch, CURLOPT_URL, self::$endPoint );
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode($messages) );
    // Allow cUrl functions 20 seconds to execute
    curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
    // Wait 10 seconds while trying to connect
    curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );

    $response = curl_exec( $ch );
    $curlInfo = curl_getinfo( $ch );
    $errors = curl_error($ch);
    curl_close( $ch );

    // local transaction
    $dbConn = new dbConnect();
    $dbConn->dbConnection();

    if ($curlInfo[ 'http_code' ] != 201) {
      $rTO->type = FLAG_ERRORTO_ERROR;
      $rTO->description = "Error sending: " . ($errors ? $errors : "HTTP status {$curlInfo['http_code']}; Response was {$response}");
    }
    else {
      $rTO->type = FLAG_ERRORTO_SUCCESS;
      $rTO->description = "Successfully sent SMS";
    }

    $sql = "INSERT INTO third_party_service(service_name, created_datetime, status, response, user_uid)
            VALUES ('BULKSMS', NOW(), '{$rTO->type}', '".mysqli_real_escape_string($dbConn->connection, substr($response,0, 10000))."', {$userUId})";
    $pTO = $dbConn->processPosting($sql, false);

    $dbConn->dbQuery("commit");

    return $rTO;
  }

}
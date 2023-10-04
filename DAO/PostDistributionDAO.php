<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'properties/dbSettings.inc');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');

class PostDistributionDAO
{

    /**
     * @var ErrorTO
     */
    public $errorTO;

    /**
     * @var dbConnect
     */
    private $dbConn;

    function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
        $this->errorTO = new ErrorTO;
    }

    public function postQueueDistribution($postingDistributionTO, $useSmartQueue = true)
    {
        $this->dbConn->dbQuery("SET time_zone='+0:00'");

        if ($postingDistributionTO->destinationUserUId == "") $destUId = " NULL "; else $destUId = $postingDistributionTO->destinationUserUId;

        $now = gmdate(GUI_PHP_DATETIME_FORMAT);
        $attachmentFile = str_replace("../", "", $postingDistributionTO->attachmentFile); // strip off the root so that emailer can use its own root
        $attachmentFile = (trim($attachmentFile) != "") ? ("'" . mysqli_real_escape_string($this->dbConn->connection, $attachmentFile) . "'") : ("NULL");
        $ftpFilename = (trim($postingDistributionTO->ftpFilename) != "") ? ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingDistributionTO->ftpFilename) . "'") : ("NULL");
        $body = (trim($postingDistributionTO->body) != "") ? ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingDistributionTO->body) . "'") : ("NULL");
        $plainBody = (trim($postingDistributionTO->plainBody) != "") ? ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingDistributionTO->plainBody) . "'") : ("NULL");
        $postingDistributionTO->messageId = $this->guid();

        if ($postingDistributionTO->DMLType == "INSERT") {
            $sql = "INSERT INTO distribution
             (
               run_date,
               queued_date,
               attachment_file,
               ftp_filename,
               status,
               subject,
               body,
               plain_body,
               delivery_type,
               destination_addr,
               destination_user_uid,
               source_identifier,
               message_id
             )
             VALUES (
               NULL,
               '{$now}',
               {$attachmentFile},
               {$ftpFilename},
               '" . ($useSmartQueue ? FLAG_STATUS_ACTIVE : FLAG_STATUS_QUEUED) . "',
               '{$postingDistributionTO->subject}',
               {$body},
               {$plainBody},
               {$postingDistributionTO->deliveryType},
               '{$postingDistributionTO->destinationAddr}',
               {$destUId},
               '{$postingDistributionTO->sourceIdentifier}',
               '{$postingDistributionTO->messageId}'
             )";
        }

        $this->errorTO = $this->dbConn->processPosting($sql, $postingDistributionTO->UId);
        $this->errorTO->identifier2 = $postingDistributionTO->messageId;

        if ($this->errorTO->isSuccess()) {

            if ($postingDistributionTO->DMLType == "INSERT") {
                $this->errorTO->description = "Distribution Successfully queued.";
                $this->errorTO->identifier = $this->dbConn->dbGetLastInsertId(); // get the UID just created
            }

            if ($useSmartQueue) {
                //Publish event to SQS...
                //stop using the database as a queuing mechanism

                $attachmentsArr = null;
                if($attachmentFile != "" && $attachmentFile != "NULL"){
                    $attachmentsArr = explode(",", trim($attachmentFile,"'"));
                }

                $emailRequest = [
                    "message_id" => $postingDistributionTO->messageId ?? guid(),
                    "to" => $postingDistributionTO->destinationAddr,
                    "attachments" => $attachmentsArr,
                    "subject" => $postingDistributionTO->subject,
                    "body" => $postingDistributionTO->body??'',
                    "plain_body" => $postingDistributionTO->plainBody??'',
                    "source_id" => (string)($postingDistributionTO->sourceIdentifier??''),
                    "created_timestamp" => $now,
                ];

                $publishResult = self::postDecoupledSmartQueue($emailRequest, $this->errorTO->identifier);
                if ($publishResult->isError()) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "SmartQueue failed: {$publishResult->getDescription()}";
                }
            }
        }

        return $this->errorTO;
    }

    public function postDecoupledSmartQueue($data = [], $id = null, $timeout = 30, $queueName = 'EmailEvents.fifo')
    {

        //TODO: move to constants...
        $typeName = 'EMAIL_TYPE';
        $url = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/distribution/smartQ.php";

        $params = [
            'queueName' => $queueName,
            "type" => $typeName,
        ];
        if ($id && $id > 0) {
            $params["id"] = $id;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $rawResponse = curl_exec($ch);
        $curlDebug = curl_getinfo($ch);
        $statusCode = $curlDebug['http_code'] ?? -1;

        if ($statusCode != 200) {
            return ErrorTO::NewError($rawResponse . " " . print_r($curlDebug, true));
        }

        return ErrorTO::NewSuccess("SUCCESS");
    }

    //guid returns a random guid, eg: 7347aa21-9097-4630-93bd-c5914493113f
    private function guid()
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function setDistributionStart($dUId)
    {
        $this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA)

        $sql = "UPDATE distribution
 				SET   run_date=now()
				WHERE uid='{$dUId}'";

        $this->errorTO = $this->dbConn->Processposting($sql, $dUId);

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Distribution Successfully updated.";
            return $this->errorTO;
        } else {
            print_r($this->errorTO);
        }

        return $this->errorTO;
    }

    public function deleteDistributionErrors($errorCount = 30)
    {
        $sql = "UPDATE distribution d 
                  SET d.`status` = '" . FLAG_STATUS_DELETED . "'
                WHERE d.`status` = '" . FLAG_STATUS_ERROR . "' 
                    AND d.error_cnt > {$errorCount}";

        $this->errorTO = $this->dbConn->processPosting($sql, $dUId);


        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Distribution Successfully updated.";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setDistributionResult($dUId, $runMsg, $status)
    {
        $this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA)

        $sql = "UPDATE distribution
 				SET  run_msg='{$runMsg}',
 				     run_date=now(),
             		 status='{$status}',
             		 error_cnt=if('{$status}'='" . FLAG_STATUS_ERROR . "',error_cnt+1,error_cnt)
				WHERE uid='{$dUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, $dUId);

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Distribution Successfully updated.";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

    public function setDistributionStatus($dUId, $status)
    {
        $this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA)

        $sql = "UPDATE distribution
 				    SET  status='{$status}'
				WHERE uid='{$dUId}'";

        $this->errorTO = $this->dbConn->processPosting($sql, $dUId);

        if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Distribution Successfully updated.";
            return $this->errorTO;
        }

        return $this->errorTO;
    }

}
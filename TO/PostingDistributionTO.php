<?php

class PostingDistributionTO
{
    public $DMLType;
    public $UId; // returned if insert
    public $runDate;
    public $queuedDate;
    public $runMsg;
    public $attachmentFile;
    public $ftpFilename;
    public $status;
    public $subject;
    public $body;
    public $plainBody;
    public $deliveryType;
    public $destinationAddr;
    public $destinationUserUId;
    public $sourceIdentifier;
    public $fromAddr;
    public $fromAlias;
    public $messageId;

    /**
     * @param string $bucketName
     * @param string $fileLocation
     * @return void
     */
    public function setAttachmentFileAsS3Uri(string $bucketName, string $fileLocation)
    {
        //cater for multiple attachments
        if ($this->attachmentFile != '') {
            $this->attachmentFile .= ',';
        }

        //remove all front slashes
        $fileLocation = ltrim($fileLocation, '/');

        $this->attachmentFile .= "s3://{$bucketName}/{$fileLocation}";
    }

}
<?php

class PostingFileLogTO
{
    public $DMLType;
    public $uid;
    public $fileName;
    public $processedDate;
    public $status;
    public $vendorUId;
    public $vendorRemovalDate;
    public $vendorRemoved;
    public $errorCount;
    public $errorMsg;
    public $errorType;
    public $principalUId;
    public $onlineFileProcessingUId; // the file filter that processed this file
    public $documentNumber; // only used for confirmations - if the file has only one document inside it !
    public $clientDocumentNumber; // only used for confirmations - if the file has only one document inside it !
}
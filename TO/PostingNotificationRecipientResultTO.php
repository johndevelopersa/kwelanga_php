<?php

class PostingNotificationRecipientResultTO
{
    public $UId;
    public $statusMsg;
    public $serviceStatus; // do not pass. set automatically by error count
    public $errorCount; // do not pass. set automatically
    public $distributionSourceIdentifier; // liked to generated distributions
}
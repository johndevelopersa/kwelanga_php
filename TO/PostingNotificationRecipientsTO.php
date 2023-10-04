<?php

class PostingNotificationRecipientsTO
{
    public $DMLType; // INSERT, DELETE, UPDATE
    public $UId;
    public $notificationUId;
    public $principalUId;
    public $runDate;
    public $userUIdList;
    public $value;
    public $outputType;
    public $deliveryType;
    public $distributionSourceIdentifier; // liked to generated distributions
    public $additionalParameterString;
}
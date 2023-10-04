<?php

class PostingSchedulerJobTO
{
    public $DMLType;
    public $UId;
    public $schedulerUId;
    public $runDate;
    public $queuedDate;
    public $runResult;
    public $runMsg;
    public $attachmentFile;
    public $distributionSourceIdentifier; // liked to generated distributions
}
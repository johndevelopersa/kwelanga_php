<?php

class PostingTSCLTO
{
    // filename headers
    public $principalId;
    public $vendorId;
    public $sourceAdaptorName;

    // data in file
    public $vendorNo;
    public $processedDate;
    public $specialStoreFieldValue; // eg. the value in account_no of file
    public $creditBalance;
    public $creditLimit;
    public $principalStoreUId;
    public $principalStoreUIdList;
    public $specialStoreFieldIdForLookup; // lookup on this ssfn to get the psm uid eg. "SGX Bill to Acc" (uid val of it)

}
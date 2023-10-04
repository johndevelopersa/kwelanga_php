<?php

class PostingPricingDealTO
{
    public $DMLType;
    public $pduid; // current deal uid being modified if UPDATE
    public $customerTypeUid;
    public $chainOrStoreUid;  // can be a list string for Inserts
    public $principalProdUid;  // can be a list string for Inserts
    public $priceTypeUId;
    public $principalUid;
    public $listPrice;
    public $dealTypeID;
    public $discountValue;
    public $startDate;
    public $endDate;
    public $status_uid;
    public $exclInclFlag;
    public $user_uid;
    public $captureDate;
    public $activated;
    public $guid;
    public $reference;
    public $deleted;
}

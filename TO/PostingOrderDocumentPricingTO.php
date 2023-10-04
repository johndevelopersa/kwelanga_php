<?php

class PostingOrderDocumentPricingTO
{
    public $DMLType = "INSERT"; // only used for inserts

    public $uid;
    public $description;
    public $chosenPricingDocumentUId;
    public $quantity;
    public $dealTypeUId;
    public $value;
    public $applyLevel;
    public $applyPerUnit;
    public $cumulativeType;
    public $principalProductUId;
    public $discountValue;

    // pass back set on insert
    public $ordersUId;
    public $unitPriceTypeUId;
}
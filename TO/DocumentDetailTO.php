<?php

class DocumentDetailTO
{
    public $lineNo;
    public $productUId;
    public $orderedQty;
    public $documentQty;
    public $deliveredQty;
    public $sellingPrice;
    public $discountValue;
    public $discountReference;
    public $netPrice;
    public $extendedPrice;
    public $vatAmount;
    public $vatRate;
    public $total;
    public $pallets;
    public $podReasonUId;

    // unsynched table section : hold vars until can be derived
    public $holdProductCode;
}

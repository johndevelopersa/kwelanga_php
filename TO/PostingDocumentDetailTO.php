<?php

class PostingDocumentDetailTO
{
    public $ddUId;
    public $lineNo;
    public $clientLineNo;
    public $productUId;
    // should only be used if product code not found in m/f to be stored with document detail
    public $productCode;
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
    public $wsUniqueCreatorId;
    public $additionalType;
    public $userModified = "N"; // user modified the electronic source in orders_holding
    public $comment;

    // unsynched table section : hold vars until can be derived
    public $holdProductCode;
    public $oldQuantity = null;
    public $oldPrice = null;
}
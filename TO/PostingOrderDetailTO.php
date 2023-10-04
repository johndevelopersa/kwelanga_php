<?php

class PostingOrderDetailTO
{
    public $UId; // set after insert, not passed
    public $ordersUId; // set after insert, not passed
    public $productUId;
    public $quantity;
    public $comment;
    public $pallets; // overwritten with calculated value on posting
    public $itemspercase;
    public $priceOverrideValue; // to override the looked up price
    public $priceOverrideUseSuppliedVals = false; // if true, then the supplied discount value is subtracted from supplied list price to give override price, with a check done against supplied overridePrice. Also only cater for DV as an amount off, and vat rate is used as overridden
    public $lineNo; // Depot Numbering Scheme
    public $pageNo; // Depot Numbering Scheme
    public $clientLineNo; // Client Numbering Scheme
    public $clientPageNo; // Client Numbering Scheme
    public $originalProductCode; // if modified differs from unmodified
    public $wsUniqueCreatorId;
    public $additionalType;
    public $userModified = "N"; // user modified the electronic source in orders_holding

    // pricing fields - set by TransactionDAO and returned
    public $chosenPricingUId;
    public $priceType;
    public $dealTypeUId;
    public $listPrice;  // override price overwrites this value on processing
    public $dealTypeValue;
    public $discountValue;
    public $nettPrice;
    public $priceOverride; // Y/N flag
    public $discountReference;
    public $vatAmount;
    public $extPrice;
    public $totPrice;

    // used for pricing, leave blank if you want to calling program to calculate it(extra processing to instantiate DAO).
    public $qtyConvertedToCases = ""; // Only really needed for applying bulk discounts.
    public $productVatRate = ""; // if overriding, make sure $priceOverrideUseSuppliedVals is set. This is the calculated Vat Rate, not the specific m/f setting
    public $majorCategory = false; // used for bulk discount calculation

    // passbacks
    public $productNoVATAuthorisedBy;
    public $mfProductVatRate = ""; // this is the actual specific masterfile vat rate setting
    public $productCode;
    public $productDescription;

    // not stored - used only for depot export file
    public $mass;
    public $volume;

    public $oldQuantity = null;
    public $oldPrice = null;
}

<?php

class PostingOrdersHoldingDetailTO
{
    public $principalProductUid;
    public $quantity;
    public $rejectedQuantity = 0;
    public $pallets;
    public $listPrice;
    public $discountValue;
    public $discountReference;
    public $totalPrice;
    public $nettPrice;
    public $extPrice;
    public $vatAmount;
    public $vatRate;
    public $insertVatRate;
    public $productName;
    public $productSKUGTIN; // innercasing
    public $productGTIN;    // outercasing
    public $productCode;
    public $originalProductCode; // if modified differs to unmodified
    public $clientPageNo;
    public $clientLineNo;
    public $updateProductStatus = "N";
    public $updateProductVATRate = "Y"; // only applies for updates, not inserts
    public $overridePriceType = "";
    public $mass = null;
    public $volume = null;
    public $wsUniqueCreatorId;
    public $additionalType;
}
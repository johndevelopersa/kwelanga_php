<?php

class PostingProductTO
{
    public $DMLType; // INSERT, DELETE, UPDATE
    public $principal;
    public $productCode;
    /*
     * DO NOT USE : just for legacy to stop stuff falling over until can be removed
     * @deprecated
     */
    public $eanCode;
    public $productDescription;
    public $packing;
    public $skuGTINList;
    public $outerCasingGTINList;
    public $weight;
    public $majorCategory;
    public $minorCategory;
    public $productVATRate;
    public $productString;
    public $status;
    public $enforcePalletConsignment;
    public $nonStockItem;
    public $webCapture = "N";
    public $loadToShopify = "N";
    public $noDiscounts = "N";
    public $allowDecimal = "N";
    public $gtinDepotUidList;
    public $unitsPerPallet;
    public $altCode;
    public $itemsPerCase = "1";
    public $unitValue;
    public $sizeType;
    public $sizeWidth;
    public $sizeLength;
    public $sizeHeight;
    public $principalProductMinorCategoryTOArr = [];
    // if UPDATE
    public $UId;
    // only for synching, format Y-M-d H:i:s.
    // If supplied, update will only happen if this date is later
    // than date in product table
    public $lastUpdated;
    // only used for synching - the UID in the unsynch table
    // used for updating after WS returns.
    public $synchUId;
    // only set this if coming from storeForm and user manually set this !!
    public $vatExclAuthorisedByFlag = "N";
}
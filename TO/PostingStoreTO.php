<?php

class PostingStoreTO
{
    public $DMLType;
    public $principal;
    public $storeString;
    public $deliverName;
    public $deliverAdd1;
    public $deliverAdd2;
    public $deliverAdd3;
    public $billName;
    public $billAdd1;
    public $billAdd2;
    public $billAdd3;
    public $eanCode;
    public $vatNumber;
    public $vatNumber2 = "";
    public $telNo1;  //NULL for blank - has better db storage
    public $telNo2;  //NULL for blank - has better db storage
    public $emailAdd;  //NULL for blank - has better db storage
    public $depot;
    public $deliveryDay;
    public $noVAT;
    public $onHold;
    public $chain;
    public $altPrincipalChainUId;
    public $branchCode;
    public $oldAccount;
    public $ledgerBalance;
    public $ledgerCreditLimit;
    public $status;
    public $areaUId;
    public $vendorCreatedByUId; // who created the store, null/blank is principal
    public $ownedBy; // null/blank is principal, else vendorUId
    public $principalStoreUId; // if UPDATE, then this is the uid being edited
    public $lastUpdated; // only for synching, format Y-M-d H:i:s. If supplied, update will only happen if this date is later than date in product table
    public $synchUId; // only used for synching - the UID in the unsynch table used for updating after WS returns.
    public $strippedDeliverName; // dont regenerate, pass it because we could have updated sureserver stripped_deliver_name manually to force a row in.
    public $vatExclAuthorisedByFlag = "N"; // only set this if coming from storeForm and user manually set this !!
    public $retailer = "";
    public $baccount = 1;
    public $qrcode = "";
    public $principalSalesRepresentativeUId = 0;
    public $exportNumberEnabled = "N";
    public $disval = 0;
    public $wlink;
    public $noPricesOnInvoice;
    public $localCountry = "Y";
    public $autoMailInvoice = "N";

    // extra processing requirements, not part of table object
    public $allocatePermissionsUserList; // comma separated user_uid

    // orders holding fields
    public $updatePrincipalStore = "N"; // updates fields if supplied and the store is already inserted
    public $updateDeliveryDay = "N";
    public $updateNoVAT = "N";
    public $updateStoreStatus = "N"; // change psm.status

    // passback fields
    public $areaDescription;
    public $deliveryDayDescription;

    //EPOD fields
    public $epodStoreFlag = 'N';
    public $epodRsaId;
    public $epodCellphoneNumber;
}

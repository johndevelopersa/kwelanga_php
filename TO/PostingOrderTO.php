<?php

class PostingOrderTO
{
    public $DMLType; // INSERT, UPDATE, DELETE
    public $UId;
    public $storeChainUId;
    public $principalUId;
    public $orderNumber;
    public $orderSequenceNo;
    public $deliveryInstructions;
    public $documentDate;
    public $batchGUID;
    public $captureUserUId; // RT user
    public $capturedBy; // non RT user
    public $deleted;
    public $ediCreated = "N";
    public $ediFileName; // the WOR file
    public $incomingFileName; // the EDI file
    public $documentType;
    public $additionalType;
    public $deliveryDate;
    public $deliveryDueDate;
    public $requestedDeliveryDate;
    public $additionalDetails;
    public $documentServiceTypeUId = 0;
    public $documentRepCodeUid = 0;
    public $documentTrackingNumber;
    public $discountReference;
    public $processedDepotUId; // Passed Back. Because depot can change for the store, store the depot the store was linked to at time of capture
    public $dataSource;
    public $clientDocumentNumber;
    public $documentNumber; // set according to principal Preferences, but may be passed if satisfies these checks
    public $generalReference1;
    public $generalReference2;
    public $vendorBuyingGroupCode; // used to store the chain code the vendor supplied for import
    public $sourceDocumentNumber;
    public $claimNumber;
    public $podreasonuid;
    public $forceDepotUId = "";
    public $expiryDate;
    public $offInvoiceDiscount = 0;
    public $offInvoiceDiscountType;

    public $detailArr = []; // should be array of PostingOrderDetailTO

    // passbacks
    public $pricingDocumentArr = []; // passed back, array of PostingOrderDocumentPricingTO
    public $captureDate; // GMT
    public $captureTime; // GMT
    public $WMS = "";
    public $orderStartStatus;
    public $storeNoVATAuthorisedBy;
    public $dMUId; // from document master when inserted

    // used to control processing only
    public $confirmOption; // passback from capture form to confirm a warning
    public $vendorUId;

    // used for pricing, leave blank if you want to calling program to calculate it(extra processing to instantiate DAO).
    public $storeNoVat = "";

    // not stored, only used for generation of WOR file
    public $salesAgentStoreIdentifier;
    public $debtorsStoreIdentifier;
    public $principalStoreIdentifier;
    public $useRTDocNum; // clipper import to use RT generated docnum
    public $uniqueCreatorId;

    // not stored in orders, but stored in document_master/header
    public $buyerAccountReference;
    public $invoiceNumber;
    public $fileLogUId;
    public $invoiceDate;
    public $documentMasterVersion; // used for quotations only

    //flag to skip unique customer order no check
    public $skipUniqueOrderNoFlag = 'N';
    public $skipInvoiceComputationCheck = "N"; // only applicable if using Vendor Pricing (EDI) and priceOverrideUseSuppliedVals is set on detail level
}
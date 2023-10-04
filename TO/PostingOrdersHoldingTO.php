<?php

class PostingOrdersHoldingTO
{
    //header
    public $principalUid;
    public $vendorUid;

    //processing fields
    public $status;
    public $statusMsg;
    public $createdDate;
    public $dataSource;
    public $incomingFile;
    public $userActionStatus;
    public $lastChangeByUserid = 0;
    public $wsUniqueCreatorId;

    //order
    public $documentNo; // their original number / their modified number / our RT seq
    public $clientDocumentNo; // their original doc no ref
    public $sourceDocumentNo;
    public $captureDate;
    public $orderDate;
    public $deliveryDate;
    public $expiryDate;
    public $requestedDeliveryDate;
    public $invoiceDate = null;
    public $invoiceNumber = null;
    public $capturedBy;
    public $reference; // this gets processed into the orders.order_number (the purchase order number) when processed
    public $deliveryInstructions;
    public $documentType;
    public $additionalType;
    public $additionalDetails;
    public $documentTypeUId;
    public $principalStoreUId;
    public $generalReference1;
    public $generalReference2;
    public $detailArr = []; // of type PostingOrdersHoldingDetailTO
    public $shipToName;
    public $shipToGLN;
    public $buyerGLN;
    public $deliverName;
    public $vendorReference; // buyer's store reference
    public $salesAgentStoreIdentifier; // 3rd party agent's (eg.Harding) store id. Will be put in WOR file
    public $debtorsStoreIdentifier; // debtors (eg. SGX) store id. Will be put in WOR file
    public $offInvoiceDiscount = 0;
    public $offInvoiceDiscountType;

    // lookup fields. Fill these in if you want to lookup a store/depot/chain on any of these
    public $oldAccount;
    public $depotUId = "";
    public $depotLookupRef;
    public $chainLookupRef;
    public $storeLookupRef;
    public $depotSpecialFieldUId; // some data sources (DIRECTSQL) don't use onlineFIleMapping as EDI do
    public $depotLookupRefEnforced = "N"; // If the depot lookup ref was supplied,must it always find a row. If set to N then the psm depot is used if not found
    public $flagStrippedDeliverNameLookupRef;
    public $documentStatusUId = ""; // starting status
    public $onlineFileProcessingUId;
    public $cancelledOrderNotified;
    public $checkPriceVariance;
    public $pricingConflictAction;
    public $EDIFileDefNotified;
    public $fileLogUId;
    public $enforceSameDepot = "Y"; // compare edi depot to masterfiles store
    public $updateStoreDepot = "N"; // update the store depot if supplied and valid
    public $updateProduct = "N";
    public $insertProduct = "Y"; // also controlled by PCA_USE_VENDOR in processing
    public $skipInvoiceComputationCheck = "N";
    public $documentOriginQueried = "N"; // to control whether the document origin has already been looked up
    public $exceptionNotified = "Q"; // We set this to Q for every row to make them eligible in processNotificationUnrestricted to be considered. Otherwise postImportDAO->setOrdersHoldingStatus will handle it - which is not called for R.A (Requires Approval)
    public $postingStoreTO; // only allocate if you want the processor to create the store
    public $postingSpecialFieldTOArr = []; // only allocate if you want the processor to create the store special fields

    // staging fields, not stored
    public $principalGLN = '';
    public $principalCode = '';

    // control fields
    public $skipDtlLineCountCheck = "N"; // this only controls up until the insert into OH, not further (deliberately)!
}
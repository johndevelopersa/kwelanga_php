<?php

class PostingDocumentTO
{
	public $DMLType; // INSERT or UPDATE
	public $dmUId;
	public $principalUId;
	public $depotUId;
	public $documentNumber;
	public $clientDocumentNumber;
	public $alternateDocumentNumber;
	public $documentTypeUId;
	public $additionalType;
	public $processedDate;
	public $processedTime;
	public $mergedDate;
	public $mergedTime;
	public $validationDate;
	public $validationTime;
	public $validationStatus;
	public $incomingFile;
	public $confirmationFile;
	public $dopFile;
	public $rwrFile;
	public $invoiceFile;
	public $creditNoteFile;
	public $TransmissionFlag1;
	public $TransmissionFlag2;
	public $TransmissionFlag3;
	public $TransmissionFlag4;
	public $orderSequenceNo;
	public $notificationArray = []; //internal flag
	public $fileLogUId = "";
	public $version = "";
	public $apiReference;

	// document header
	public $dhUId;
	public $orderDate;
	public $invoiceDate;
	public $deliveryDate;
	public $deliveryDueDate;
	public $requestedDeliveryDate;
	public $podReturnedDate;
	public $documentStatusUId;
	public $principalStoreUId;
	public $depotPrincipalStoreUId;
	public $customerOrderNumber;
	public $invoiceNumber;
	public $cases;
	public $sellingPrice;
	public $exclusiveTotal;
	public $vatTotal;
	public $invoiceTotal;
	public $discountReference;
	public $podReasonUId;
	public $sourceDocumentNumber;
	public $grvNumber;
	public $claimNumber;
	public $buyerAccountReference;
	public $additionalDetails = "";
	public $documentServiceTypeUId = 0;
	public $documentRepCodeUid = 0;
	public $offInvoiceDiscount = 0;
	public $offInvoiceDiscountType;
	public $detailArr = [];

	// hold vars for synching functionality
	public $holdDeliverName;
	public $holdOldAccount;
	public $lastUpdated; // only for synching, format Y-M-d H:i:s. If supplied, update will only happen if this date is later than date in product table
	public $synchUId; // only used for synching - the UID in the unsynch table used for updating after WS returns.

	// informational - used for processing
	public $dataSource;
	public $capturedBy; // if RT user, then uid, else capture by descriptor
}

<?php

class DocumentTO
{

	// document master
	public $principalUId;
	public $depotUId;
	public $documentNumber;
	public $documentTypeUId;
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
	public $notificationOnCreateStatus;
	// document header
	public $orderDate;
	public $invoiceDate;
	public $deliveryDate;
	public $podReturnedDate;
	public $documentStatusUId;
	public $principalStoreUId;
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
	public $detailArr = []; // array of DocumentDetailTO

	// hold vars for synching functionality
	public $deliverName;
	public $oldAccount;

}

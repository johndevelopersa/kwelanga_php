<?php

class PostingDocumentConfirmationTO
{
    public $DMLType;
    // document master
    public $dmUId;
    public $principalUId;
    public $depotUId;
    public $documentNumber;
    public $documentTypeUId;
    public $mergedDate;
    public $mergedTime;
    public $validationDate;
    public $validationTime;
    public $validationStatus;
    public $dopFile;
    public $rwrFile;
    public $invoiceFile;
    public $creditNoteFile;

    // document header
    public $dhUId;
    public $invoiceDate;
    public $deliveryDate;
    public $podReturnedDate;
    public $documentStatusUId;
    public $invoiceNumber;
    public $cases;
    public $exclusiveTotal;
    public $vatTotal;
    public $invoiceTotal;
    public $podReasonUId;
    public $sourceDocumentNumber;
    public $grvNumber;
    public $claimNumber;
    public $detailArr = [];
}

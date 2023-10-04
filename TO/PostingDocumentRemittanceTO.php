<?php

class PostingDocumentRemittanceTO
{
    public $DMLType; // INSERT, UPDATE, DELETE
    public $uId;
    public $wsUniqueCreatorId;
    public $dataSource;
    public $capturedBy;
    public $captureDate;
    public $paymentEffectiveDate;
    public $principalUId;
    public $principalGLN;
    public $vendorUId;
    public $vendorReference;
    public $documentNumber;
    public $reference;
    public $totalAmount;
    public $documentType;
    public $documentTypeUId;
    public $buyerGLN;
    public $detailArr = [];
}
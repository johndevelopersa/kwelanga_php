<?php

class PostingDocumentStatusTO
{
    public $documentMasterUId;
    public $documentStatusUId = false;
    public $buyerDocumentStatusUId = false; // only used for buyer pods (GI's)
    public $comment = "";
    public $repcode = NULL;
    public $trackingnumber = NULL;
    public $overideInvDate = NULL;
    public $orderSequenceNo = NULL;
    public $userId = ""; // overwritten by validation
    public $skipValidation = 'N'; // bypass validation.
    public $podDocumentMasterUId = false;

    // extra fields required for processing quotations or DTs that need to get an invoice number
    public $documentTypeUId;
    public $principalUId;
    public $depotUId;
    public $documentNumber;
}
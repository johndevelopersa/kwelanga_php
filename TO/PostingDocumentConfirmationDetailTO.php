<?php

class PostingDocumentConfirmationDetailTO
{
    public $ddUId;
    public $productUId;
    public $lineNo;
    public $documentQty;
    public $deliveredQty;
    public $extendedPrice;
    public $vatAmount;
    public $total;
    public $podReasonUId;
    // unsynched table section : hold vars until can be derived
    // used for update in sql.
    public $holdProductCode;
}

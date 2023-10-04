<?php

class PostingDocumentUpdateInvoiceDetailTO
{
    public $ddUId;
    public $productUId;
    public $lineNo;
    public $documentQty = false;
    public $deliveredQty = false; // leave this blank if you only want to change the buyerDeliveredQty
    public $buyerDeliveredQty = false; // only fill in if you want to change this value
    public $extendedPrice = false;
    public $vatAmount = false;
    public $total = false;
    public $podReasonUId = false;
}

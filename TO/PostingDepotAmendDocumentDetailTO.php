<?php

class PostingDepotAmendDocumentDetailTO
{
    public $dmUId;
    public $ddUIdArr;
    public $amendedQtyArr;
    public $batchArr;
    public $acceptQty = "N";
    public $allowDecimal;
    public $news;

    // returned vars
    public $principalProductUIdArr = [];
    public $processedDepotUId;
    public $principalUId;
    public $documentTypeUId;
}
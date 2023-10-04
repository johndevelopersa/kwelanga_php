<?php

class PostingStockTO
{
    public $stkUid;
    public $principalId;
    public $depotId;
    public $stockCode;
    public $stockDescription;
    public $goodsInTransit = 0;
    public $opening = 0;
    public $arrivals = 0;
    public $uplifts = 0;
    public $returnsCancel = 0;
    public $returnsNC = 0;
    public $delivered = 0;
    public $adjustment = 0;
    public $closing = 0;
    public $allocations = 0;
    public $inPick = 0;
    public $available = 0;
    public $blockedStock = 0;
    public $lostSalesCancel = 0;
    public $lostSalesOOS = 0;
    public $stockCount = 0;
    public $stockCountDate;
    public $sguid;

    // as part of synching
    public $dataGeneratedDate;

}